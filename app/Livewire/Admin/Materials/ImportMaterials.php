<?php

namespace App\Livewire\Admin\Materials;

use App\Models\Categories;
use App\Models\Materials;
use App\Models\Suppliers;
use App\Models\Unit;
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
#[Title('Import Data Material')]
class ImportMaterials extends Component
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
            $sheet->setTitle('Template Material');

            $sheet->setCellValue('A1', 'Nama Material');
            $sheet->setCellValue('B1', 'Kategori');
            $sheet->setCellValue('C1', 'Satuan');
            $sheet->setCellValue('D1', 'Supplier');
            $sheet->setCellValue('E1', 'Harga');
            $sheet->setCellValue('F1', 'Minimum Stok');
            $sheet->setCellValue('G1', 'Status');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

            $sheet->setCellValue('A2', 'Tepung Terigu');
            $sheet->setCellValue('B2', 'Bahan Kering');
            $sheet->setCellValue('C2', 'Kg');
            $sheet->setCellValue('D2', 'Supplier A');
            $sheet->setCellValue('E2', '12000');
            $sheet->setCellValue('F2', '10');
            $sheet->setCellValue('G2', 'active');

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer   = new Xlsx($spreadsheet);
            $fileName = 'template_import_material.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'import_material_');
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('ImportMaterials downloadTemplate error: ' . $e->getMessage());
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

            $categories = Categories::where('type', 'material')->pluck('id', 'name')->toArray();
            $units = Unit::pluck('id', 'name')->toArray();
            $suppliers = Suppliers::pluck('id', 'name')->toArray();
            $existingNames = Materials::pluck('name')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();

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
                    $categoryName = isset($row[1]) ? trim((string) $row[1]) : '';
                    $unitName = isset($row[2]) ? trim((string) $row[2]) : '';
                    $supplierName = isset($row[3]) ? trim((string) $row[3]) : '';
                    $priceRaw = isset($row[4]) ? trim((string) $row[4]) : '0';
                    $minimumStockRaw = isset($row[5]) ? trim((string) $row[5]) : '0';
                    $statusRaw = isset($row[6]) ? strtolower(trim((string) $row[6])) : 'active';

                    $price = is_numeric($priceRaw) ? (float) $priceRaw : null;
                    $minimumStock = is_numeric($minimumStockRaw) ? (float) $minimumStockRaw : null;

                    $rowData = [
                        'sheet_name'      => $sheetName,
                        'row_number'      => $rowIndex + 2,
                        'name'            => $name,
                        'category_name'   => $categoryName,
                        'unit_name'       => $unitName,
                        'supplier_name'   => $supplierName,
                        'price'           => $price,
                        'minimum_stock'   => $minimumStock,
                        'status'          => in_array($statusRaw, ['active', 'inactive'], true) ? $statusRaw : 'active',
                        'category_id'     => null,
                        'unit_id'         => null,
                        'supplier_id'     => null,
                        'errors'          => [],
                        'has_error'       => false,
                    ];

                    if ($rowData['name'] === '') {
                        $rowData['errors'][] = 'Nama material wajib diisi';
                    } elseif (in_array(mb_strtolower($rowData['name']), $existingNames, true)) {
                        $rowData['errors'][] = 'Nama material sudah terdaftar';
                    }

                    if ($rowData['category_name'] === '') {
                        $rowData['errors'][] = 'Kategori wajib diisi';
                    } elseif (!isset($categories[$rowData['category_name']])) {
                        $rowData['errors'][] = 'Kategori tidak ditemukan';
                    } else {
                        $rowData['category_id'] = $categories[$rowData['category_name']];
                    }

                    if ($rowData['unit_name'] === '') {
                        $rowData['errors'][] = 'Satuan wajib diisi';
                    } elseif (!isset($units[$rowData['unit_name']])) {
                        $rowData['errors'][] = 'Satuan tidak ditemukan';
                    } else {
                        $rowData['unit_id'] = $units[$rowData['unit_name']];
                    }

                    if ($rowData['supplier_name'] !== '') {
                        if (!isset($suppliers[$rowData['supplier_name']])) {
                            $rowData['errors'][] = 'Supplier tidak ditemukan';
                        } else {
                            $rowData['supplier_id'] = $suppliers[$rowData['supplier_name']];
                        }
                    }

                    if ($rowData['price'] === null || $rowData['price'] < 0) {
                        $rowData['errors'][] = 'Harga harus angka >= 0';
                    }

                    if ($rowData['minimum_stock'] === null || $rowData['minimum_stock'] < 0) {
                        $rowData['errors'][] = 'Minimum stok harus angka >= 0';
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
            Log::error('ImportMaterials preview error: ' . $e->getMessage());
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
            $existingNames = Materials::pluck('name')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();

            foreach ($this->previewData as $row) {
                if ($row['has_error']) {
                    continue;
                }

                if (in_array(mb_strtolower($row['name']), $existingNames, true)) {
                    continue;
                }

                try {
                    $material = Materials::create([
                        'name'          => $row['name'],
                        'category_id'   => $row['category_id'],
                        'unit_id'       => $row['unit_id'],
                        'supplier_id'   => $row['supplier_id'] ?: null,
                        'price'         => $row['price'],
                        'minimum_stock' => $row['minimum_stock'],
                        'status'        => $row['status'] === 'active',
                    ]);

                    $material->stock()->create(['qty_available' => 0]);

                    $imported++;
                    $existingNames[] = mb_strtolower($row['name']);
                } catch (\Exception $e) {
                    $errors[] = 'Sheet ' . $row['sheet_name'] . ', Baris ' . $row['row_number'] . ': ' . $e->getMessage();
                }
            }

            if ($imported > 0) {
                DB::commit();
                $this->importedCount = $imported;

                $msg = 'Berhasil import ' . $imported . ' data material!';
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
            Log::error('ImportMaterials importData error: ' . $e->getMessage());
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

        if (in_array($field, ['name', 'category_name', 'unit_name', 'supplier_name', 'status'], true)) {
            $this->previewData[$rowIndex][$field] = trim((string) $this->previewData[$rowIndex][$field]);
        }

        if (in_array($field, ['price', 'minimum_stock'], true)) {
            $raw = (string) $this->previewData[$rowIndex][$field];
            $raw = str_replace(',', '.', trim($raw));
            $this->previewData[$rowIndex][$field] = is_numeric($raw) ? (float) $raw : null;
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

        $categories = Categories::where('type', 'material')->pluck('id', 'name')->toArray();
        $units = Unit::pluck('id', 'name')->toArray();
        $suppliers = Suppliers::pluck('id', 'name')->toArray();
        $existingNames = Materials::pluck('name')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();

        $row = $this->previewData[$index];
        $errors = [];
        $categoryId = null;
        $unitId = null;
        $supplierId = null;

        $name = trim((string) ($row['name'] ?? ''));
        $categoryName = trim((string) ($row['category_name'] ?? ''));
        $unitName = trim((string) ($row['unit_name'] ?? ''));
        $supplierName = trim((string) ($row['supplier_name'] ?? ''));
        $price = $row['price'];
        $minimumStock = $row['minimum_stock'];

        if ($name === '') {
            $errors[] = 'Nama material wajib diisi';
        } elseif (in_array(mb_strtolower($name), $existingNames, true)) {
            $errors[] = 'Nama material sudah terdaftar';
        }

        if ($categoryName === '') {
            $errors[] = 'Kategori wajib diisi';
        } elseif (!isset($categories[$categoryName])) {
            $errors[] = 'Kategori tidak ditemukan';
        } else {
            $categoryId = $categories[$categoryName];
        }

        if ($unitName === '') {
            $errors[] = 'Satuan wajib diisi';
        } elseif (!isset($units[$unitName])) {
            $errors[] = 'Satuan tidak ditemukan';
        } else {
            $unitId = $units[$unitName];
        }

        if ($supplierName !== '') {
            if (!isset($suppliers[$supplierName])) {
                $errors[] = 'Supplier tidak ditemukan';
            } else {
                $supplierId = $suppliers[$supplierName];
            }
        }

        if ($price === null || !is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Harga harus angka >= 0';
        }

        if ($minimumStock === null || !is_numeric($minimumStock) || (float) $minimumStock < 0) {
            $errors[] = 'Minimum stok harus angka >= 0';
        }

        $this->previewData[$index]['name'] = $name;
        $this->previewData[$index]['category_name'] = $categoryName;
        $this->previewData[$index]['unit_name'] = $unitName;
        $this->previewData[$index]['supplier_name'] = $supplierName;
        $this->previewData[$index]['category_id'] = $categoryId;
        $this->previewData[$index]['unit_id'] = $unitId;
        $this->previewData[$index]['supplier_id'] = $supplierId;
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

    public function render()
    {
        return view('livewire.admin.materials.import-materials', [
            'filteredPreviewData' => $this->filteredPreviewData,
            'availableSheets' => $this->availableSheets,
            'availableCategories' => $this->availableCategories,
        ]);
    }
}
