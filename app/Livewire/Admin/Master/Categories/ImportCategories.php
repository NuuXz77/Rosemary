<?php

namespace App\Livewire\Admin\Master\Categories;

use App\Models\Categories;
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
#[Title('Import Data Kategori')]
class ImportCategories extends Component
{
    use WithFileUploads;

    public $file;
    public array $previewData = [];
    public int $validCount = 0;
    public int $errorCount = 0;
    public int $importedCount = 0;
    public bool $isLoading = false;

    public string $filterSheet = 'all';
    public string $filterType = 'all';

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
            $sheet->setTitle('Template Categories');

            $sheet->setCellValue('A1', 'Nama Kategori');
            $sheet->setCellValue('B1', 'Tipe');
            $sheet->setCellValue('C1', 'Status');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ];
            $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

            $sheet->setCellValue('A2', 'Snack');
            $sheet->setCellValue('B2', 'product');
            $sheet->setCellValue('C2', 'active');

            $sheet->setCellValue('A3', 'Bahan Kering');
            $sheet->setCellValue('B3', 'material');
            $sheet->setCellValue('C3', 'active');

            foreach (range('A', 'C') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'template_import_categories.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'import_categories_');
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('ImportCategories downloadTemplate error: ' . $e->getMessage());
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
        $this->filterType = 'all';

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

            $existingPairs = Categories::query()
                ->get(['name', 'type'])
                ->map(fn($c) => mb_strtolower(trim($c->name)) . '|' . $c->type)
                ->toArray();

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
                    $type = isset($row[1]) ? strtolower(trim((string) $row[1])) : '';
                    $statusRaw = isset($row[2]) ? strtolower(trim((string) $row[2])) : 'active';

                    $rowData = [
                        'sheet_name' => $sheetName,
                        'row_number' => $rowIndex + 2,
                        'name'       => $name,
                        'type'       => $type,
                        'status'     => in_array($statusRaw, ['active', 'inactive'], true) ? $statusRaw : 'active',
                        'errors'     => [],
                        'has_error'  => false,
                    ];

                    if ($rowData['name'] === '') {
                        $rowData['errors'][] = 'Nama kategori wajib diisi';
                    }

                    if (!in_array($rowData['type'], ['product', 'material'], true)) {
                        $rowData['errors'][] = 'Tipe harus product atau material';
                    }

                    if ($rowData['name'] !== '' && in_array(mb_strtolower($rowData['name']) . '|' . $rowData['type'], $existingPairs, true)) {
                        $rowData['errors'][] = 'Kategori dengan nama & tipe yang sama sudah ada';
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
            Log::error('ImportCategories preview error: ' . $e->getMessage());
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
            $existingPairs = Categories::query()
                ->get(['name', 'type'])
                ->map(fn($c) => mb_strtolower(trim($c->name)) . '|' . $c->type)
                ->toArray();

            foreach ($this->previewData as $row) {
                if ($row['has_error']) {
                    continue;
                }

                $pair = mb_strtolower(trim((string) $row['name'])) . '|' . $row['type'];
                if (in_array($pair, $existingPairs, true)) {
                    continue;
                }

                Categories::create([
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'status' => $row['status'] === 'active',
                ]);

                $existingPairs[] = $pair;
                $imported++;
            }

            if ($imported > 0) {
                DB::commit();
                $this->importedCount = $imported;
                $this->dispatch('show-toast', type: 'success', message: 'Berhasil import ' . $imported . ' data kategori!');
                $this->reset(['file', 'previewData', 'validCount', 'errorCount']);
                $this->dispatch('redirect-after-import');
            } else {
                DB::rollBack();
                $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data yang berhasil diimport');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportCategories importData error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearPreview(): void
    {
        $this->reset(['file', 'previewData', 'validCount', 'errorCount', 'importedCount']);
        $this->filterSheet = 'all';
        $this->filterType = 'all';
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

        if (in_array($field, ['name', 'type', 'status'], true)) {
            $this->previewData[$rowIndex][$field] = trim((string) $this->previewData[$rowIndex][$field]);
        }

        if ($field === 'type') {
            $type = strtolower((string) $this->previewData[$rowIndex]['type']);
            $this->previewData[$rowIndex]['type'] = in_array($type, ['product', 'material'], true) ? $type : '';
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

        $existingPairs = Categories::query()
            ->get(['name', 'type'])
            ->map(fn($c) => mb_strtolower(trim($c->name)) . '|' . $c->type)
            ->toArray();

        $row = $this->previewData[$index];
        $errors = [];

        $name = trim((string) ($row['name'] ?? ''));
        $type = strtolower(trim((string) ($row['type'] ?? '')));

        if ($name === '') {
            $errors[] = 'Nama kategori wajib diisi';
        }

        if (!in_array($type, ['product', 'material'], true)) {
            $errors[] = 'Tipe harus product atau material';
        }

        if ($name !== '' && in_array(mb_strtolower($name) . '|' . $type, $existingPairs, true)) {
            $errors[] = 'Kategori dengan nama & tipe yang sama sudah ada';
        }

        $this->previewData[$index]['name'] = $name;
        $this->previewData[$index]['type'] = $type;
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
            ->when($this->filterType !== 'all', fn($c) => $c->where('type', $this->filterType))
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

    public function render()
    {
        return view('livewire.admin.master.categories.import-categories', [
            'filteredPreviewData' => $this->filteredPreviewData,
            'availableSheets' => $this->availableSheets,
        ]);
    }
}
