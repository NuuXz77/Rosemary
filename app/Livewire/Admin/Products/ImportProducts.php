<?php

namespace App\Livewire\Admin\Products;

use App\Models\Categories;
use App\Models\Divisions;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Layout('components.layouts.app')]
#[Title('Import Data Produk')]
class ImportProducts extends Component
{
    use WithFileUploads;

    public $file;
    public array $previewData = [];
    public int $validCount = 0;
    public int $errorCount = 0;
    public int $importedCount = 0;
    public bool $isLoading = false;

    public string $filterSheet = 'all';
    public string $filterCategory = 'all';
    public string $filterDivision = 'all';

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls|max:5120',
    ];

    protected $messages = [
        'file.required' => 'File Excel wajib dipilih',
        'file.mimes'    => 'File harus berformat .xlsx atau .xls',
        'file.max'      => 'Ukuran file maksimal 5MB',
    ];

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Products');

            $sheet->setCellValue('A1', 'Nama Produk');
            $sheet->setCellValue('B1', 'Barcode');
            $sheet->setCellValue('C1', 'Kategori');
            $sheet->setCellValue('D1', 'Divisi');
            $sheet->setCellValue('E1', 'Harga');
            $sheet->setCellValue('F1', 'Status');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            $sheet->setCellValue('A2', 'Roti Coklat');
            $sheet->setCellValue('B2', 'PRD0001');
            $sheet->setCellValue('C2', 'Snack');
            $sheet->setCellValue('D2', 'Pastry Bakery');
            $sheet->setCellValue('E2', '15000');
            $sheet->setCellValue('F2', 'active');

            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'template_import_products.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'import_products_');
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('ImportProducts downloadTemplate error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal download template');
            return null;
        }
    }

    public function updatedFile(): void
    {
        $this->validateOnly('file');
        $this->isLoading = true;

        $this->reset(['previewData', 'validCount', 'errorCount', 'importedCount']);
        $this->filterSheet = 'all';
        $this->filterCategory = 'all';
        $this->filterDivision = 'all';

        $this->previewAllSheets();
    }

    public function previewAllSheets(): void
    {
        try {
            if (!$this->file) {
                $this->dispatch('show-toast', type: 'error', message: 'Pilih file terlebih dahulu');
                $this->isLoading = false;
                return;
            }

            $path = $this->file->getRealPath();
            $spreadsheet = IOFactory::load($path);

            $categories = Categories::where('type', 'product')->pluck('id', 'name')->toArray();
            $divisions = Divisions::pluck('id', 'name')->toArray();
            $existingNames = Products::pluck('name')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();
            $existingBarcodes = Products::whereNotNull('barcode')->pluck('barcode')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();

            $this->previewData = [];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $sheetName = $worksheet->getTitle();
                $rows = $worksheet->toArray();
                array_shift($rows);

                foreach ($rows as $rowIndex => $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $name = isset($row[0]) ? trim((string) $row[0]) : '';
                    $barcode = isset($row[1]) ? trim((string) $row[1]) : '';
                    $categoryName = isset($row[2]) ? trim((string) $row[2]) : '';
                    $divisionName = isset($row[3]) ? trim((string) $row[3]) : '';
                    $priceRaw = isset($row[4]) ? trim((string) $row[4]) : '0';
                    $statusRaw = isset($row[5]) ? strtolower(trim((string) $row[5])) : 'active';
                    $price = is_numeric($priceRaw) ? (float) $priceRaw : null;

                    $rowData = [
                        'sheet_name'    => $sheetName,
                        'row_number'    => $rowIndex + 2,
                        'name'          => $name,
                        'barcode'       => $barcode,
                        'category_name' => $categoryName,
                        'division_name' => $divisionName,
                        'price'         => $price,
                        'status'        => in_array($statusRaw, ['active', 'inactive'], true) ? $statusRaw : 'active',
                        'category_id'   => null,
                        'division_id'   => null,
                        'errors'        => [],
                        'has_error'     => false,
                    ];

                    if ($rowData['name'] === '') {
                        $rowData['errors'][] = 'Nama produk wajib diisi';
                    } elseif (in_array(mb_strtolower($rowData['name']), $existingNames, true)) {
                        $rowData['errors'][] = 'Nama produk sudah terdaftar';
                    }

                    if ($rowData['barcode'] !== '' && in_array(mb_strtolower($rowData['barcode']), $existingBarcodes, true)) {
                        $rowData['errors'][] = 'Barcode sudah terdaftar';
                    }

                    if ($rowData['category_name'] === '') {
                        $rowData['errors'][] = 'Kategori wajib diisi';
                    } elseif (!isset($categories[$rowData['category_name']])) {
                        $rowData['errors'][] = 'Kategori produk tidak ditemukan';
                    } else {
                        $rowData['category_id'] = $categories[$rowData['category_name']];
                    }

                    if ($rowData['division_name'] === '') {
                        $rowData['errors'][] = 'Divisi wajib diisi';
                    } elseif (!isset($divisions[$rowData['division_name']])) {
                        $rowData['errors'][] = 'Divisi tidak ditemukan';
                    } else {
                        $rowData['division_id'] = $divisions[$rowData['division_name']];
                    }

                    if ($rowData['price'] === null || $rowData['price'] < 0) {
                        $rowData['errors'][] = 'Harga harus angka >= 0';
                    }

                    $rowData['has_error'] = !empty($rowData['errors']);
                    if ($rowData['has_error']) {
                        $this->errorCount++;
                    } else {
                        $this->validCount++;
                    }

                    $this->previewData[] = $rowData;
                }
            }

            if (count($this->previewData) === 0) {
                $this->dispatch('show-toast', type: 'warning', message: 'Tidak ada data ditemukan di file ini');
            } else {
                $msg = 'File berhasil diupload: ' . count($this->previewData) . ' data';
                if ($this->errorCount > 0) {
                    $msg .= ' (' . $this->errorCount . ' error)';
                }
                $this->dispatch('show-toast', type: 'success', message: $msg);
            }
        } catch (\Exception $e) {
            Log::error('ImportProducts preview error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Error membaca file: ' . $e->getMessage());
            $this->reset(['previewData', 'validCount', 'errorCount']);
        } finally {
            $this->isLoading = false;
        }
    }

    public function importData(): void
    {
        if ($this->errorCount > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Ada ' . $this->errorCount . ' data error. Perbaiki sebelum import.');
            return;
        }

        if (empty($this->previewData)) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data untuk diimport');
            return;
        }

        $this->isLoading = true;

        try {
            DB::beginTransaction();

            $imported = 0;
            $errors = [];
            $existingNames = Products::pluck('name')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();
            $existingBarcodes = Products::whereNotNull('barcode')->pluck('barcode')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();

            foreach ($this->previewData as $row) {
                if ($row['has_error']) {
                    continue;
                }

                $nameKey = mb_strtolower(trim((string) $row['name']));
                $barcodeKey = mb_strtolower(trim((string) ($row['barcode'] ?? '')));

                if (in_array($nameKey, $existingNames, true)) {
                    continue;
                }

                if ($barcodeKey !== '' && in_array($barcodeKey, $existingBarcodes, true)) {
                    continue;
                }

                try {
                    $product = Products::create([
                        'name'        => $row['name'],
                        'barcode'     => $row['barcode'] !== '' ? $row['barcode'] : null,
                        'category_id' => $row['category_id'],
                        'division_id' => $row['division_id'],
                        'price'       => $row['price'],
                        'status'      => $row['status'] === 'active',
                    ]);

                    $product->stock()->create(['qty_available' => 0]);

                    $imported++;
                    $existingNames[] = $nameKey;
                    if ($barcodeKey !== '') {
                        $existingBarcodes[] = $barcodeKey;
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Sheet ' . $row['sheet_name'] . ', Baris ' . $row['row_number'] . ': ' . $e->getMessage();
                }
            }

            if ($imported > 0) {
                DB::commit();
                $this->importedCount = $imported;

                $msg = 'Berhasil import ' . $imported . ' data produk!';
                if (!empty($errors)) {
                    $msg .= ' (' . count($errors) . ' gagal)';
                }
                $this->dispatch('show-toast', type: 'success', message: $msg);
                $this->reset(['file', 'previewData', 'validCount', 'errorCount']);
                $this->dispatch('redirect-after-import');
            } else {
                DB::rollBack();
                $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data yang berhasil diimport');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportProducts importData error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearPreview(): void
    {
        $this->reset(['file', 'previewData', 'validCount', 'errorCount', 'importedCount']);
        $this->filterSheet = 'all';
        $this->filterCategory = 'all';
        $this->filterDivision = 'all';
        $this->dispatch('show-toast', type: 'info', message: 'Data dibersihkan');
    }

    public function setFilterSheet(string $sheet): void
    {
        $this->filterSheet = $sheet;
    }

    public function updatedPreviewData($value, $key): void
    {
        [$index, $field] = array_pad(explode('.', (string) $key, 2), 2, null);
        if ($field === null || !isset($this->previewData[(int) $index])) {
            return;
        }

        $rowIndex = (int) $index;

        if (in_array($field, ['name', 'barcode', 'category_name', 'division_name', 'status'], true)) {
            $this->previewData[$rowIndex][$field] = trim((string) $this->previewData[$rowIndex][$field]);
        }

        if ($field === 'price') {
            $raw = (string) $this->previewData[$rowIndex]['price'];
            $raw = str_replace(',', '.', trim($raw));
            $this->previewData[$rowIndex]['price'] = is_numeric($raw) ? (float) $raw : null;
        }

        if ($field === 'status') {
            $status = strtolower((string) $this->previewData[$rowIndex]['status']);
            $this->previewData[$rowIndex]['status'] = in_array($status, ['active', 'inactive'], true) ? $status : 'active';
        }

        $this->revalidatePreviewRow($rowIndex);
        $this->recalculatePreviewCounts();
    }

    private function revalidatePreviewRow(int $index): void
    {
        if (!isset($this->previewData[$index])) {
            return;
        }

        $categories = Categories::where('type', 'product')->pluck('id', 'name')->toArray();
        $divisions = Divisions::pluck('id', 'name')->toArray();
        $existingNames = Products::pluck('name')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();
        $existingBarcodes = Products::whereNotNull('barcode')->pluck('barcode')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();

        $row = $this->previewData[$index];
        $errors = [];
        $categoryId = null;
        $divisionId = null;

        $name = trim((string) ($row['name'] ?? ''));
        $barcode = trim((string) ($row['barcode'] ?? ''));
        $categoryName = trim((string) ($row['category_name'] ?? ''));
        $divisionName = trim((string) ($row['division_name'] ?? ''));
        $price = $row['price'];

        if ($name === '') {
            $errors[] = 'Nama produk wajib diisi';
        } elseif (in_array(mb_strtolower($name), $existingNames, true)) {
            $errors[] = 'Nama produk sudah terdaftar';
        }

        if ($barcode !== '' && in_array(mb_strtolower($barcode), $existingBarcodes, true)) {
            $errors[] = 'Barcode sudah terdaftar';
        }

        if ($categoryName === '') {
            $errors[] = 'Kategori wajib diisi';
        } elseif (!isset($categories[$categoryName])) {
            $errors[] = 'Kategori produk tidak ditemukan';
        } else {
            $categoryId = $categories[$categoryName];
        }

        if ($divisionName === '') {
            $errors[] = 'Divisi wajib diisi';
        } elseif (!isset($divisions[$divisionName])) {
            $errors[] = 'Divisi tidak ditemukan';
        } else {
            $divisionId = $divisions[$divisionName];
        }

        if ($price === null || !is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Harga harus angka >= 0';
        }

        $this->previewData[$index]['name'] = $name;
        $this->previewData[$index]['barcode'] = $barcode;
        $this->previewData[$index]['category_name'] = $categoryName;
        $this->previewData[$index]['division_name'] = $divisionName;
        $this->previewData[$index]['category_id'] = $categoryId;
        $this->previewData[$index]['division_id'] = $divisionId;
        $this->previewData[$index]['errors'] = $errors;
        $this->previewData[$index]['has_error'] = !empty($errors);
    }

    private function recalculatePreviewCounts(): void
    {
        $this->errorCount = collect($this->previewData)->where('has_error', true)->count();
        $this->validCount = collect($this->previewData)->where('has_error', false)->count();
    }

    public function getFilteredPreviewDataProperty(): array
    {
        return collect($this->previewData)
            ->map(function ($row, $index) {
                $row['_index'] = $index;
                return $row;
            })
            ->when($this->filterSheet !== 'all', fn($c) => $c->where('sheet_name', $this->filterSheet))
            ->when($this->filterCategory !== 'all', fn($c) => $c->where('category_name', $this->filterCategory))
            ->when($this->filterDivision !== 'all', fn($c) => $c->where('division_name', $this->filterDivision))
            ->values()
            ->all();
    }

    public function getAvailableSheetsProperty(): array
    {
        return collect($this->previewData)
            ->pluck('sheet_name')
            ->unique()
            ->filter()
            ->values()
            ->all();
    }

    public function getAvailableCategoriesProperty(): array
    {
        return collect($this->previewData)
            ->pluck('category_name')
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->all();
    }

    public function getAvailableDivisionsProperty(): array
    {
        return collect($this->previewData)
            ->pluck('division_name')
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.products.import-products', [
            'filteredPreviewData' => $this->filteredPreviewData,
            'availableSheets' => $this->availableSheets,
            'availableCategories' => $this->availableCategories,
            'availableDivisions' => $this->availableDivisions,
        ]);
    }
}
