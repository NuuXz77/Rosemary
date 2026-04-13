<?php

namespace App\Livewire\Admin\Master\Units;

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
#[Title('Import Data Satuan')]
class ImportUnits extends Component
{
    use WithFileUploads;

    public $file;
    public array $previewData = [];
    public int $validCount = 0;
    public int $errorCount = 0;
    public int $importedCount = 0;
    public bool $isLoading = false;

    public string $filterSheet = 'all';
    public string $filterStatus = 'all';

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
            $sheet->setTitle('Template Units');

            $sheet->setCellValue('A1', 'Nama Satuan');
            $sheet->setCellValue('B1', 'Status');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ];
            $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

            $sheet->setCellValue('A2', 'kg');
            $sheet->setCellValue('B2', 'active');

            $sheet->setCellValue('A3', 'pcs');
            $sheet->setCellValue('B3', 'active');

            foreach (range('A', 'B') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'template_import_units.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'import_units_');
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('ImportUnits downloadTemplate error: ' . $e->getMessage());
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
        $this->filterStatus = 'all';

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
            $existingNames = Unit::pluck('name')->map(fn($name) => mb_strtolower(trim((string) $name)))->toArray();

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
                    $statusRaw = isset($row[1]) ? strtolower(trim((string) $row[1])) : 'active';

                    $rowData = [
                        'sheet_name' => $sheetName,
                        'row_number' => $rowIndex + 2,
                        'name'       => $name,
                        'status'     => in_array($statusRaw, ['active', 'inactive'], true) ? $statusRaw : 'active',
                        'errors'     => [],
                        'has_error'  => false,
                    ];

                    if ($rowData['name'] === '') {
                        $rowData['errors'][] = 'Nama satuan wajib diisi';
                    } elseif (in_array(mb_strtolower($rowData['name']), $existingNames, true)) {
                        $rowData['errors'][] = 'Nama satuan sudah terdaftar';
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
            Log::error('ImportUnits preview error: ' . $e->getMessage());
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
            $existingNames = Unit::pluck('name')->map(fn($name) => mb_strtolower(trim((string) $name)))->toArray();

            foreach ($this->previewData as $row) {
                if ($row['has_error']) {
                    continue;
                }

                $normalizedName = mb_strtolower(trim((string) $row['name']));
                if (in_array($normalizedName, $existingNames, true)) {
                    continue;
                }

                Unit::create([
                    'name' => $row['name'],
                    'status' => $row['status'] === 'active',
                ]);

                $existingNames[] = $normalizedName;
                $imported++;
            }

            if ($imported > 0) {
                DB::commit();
                $this->importedCount = $imported;
                $this->dispatch('show-toast', type: 'success', message: 'Berhasil import ' . $imported . ' data satuan!');
                $this->reset(['file', 'previewData', 'validCount', 'errorCount']);
                $this->dispatch('redirect-after-import');
            } else {
                DB::rollBack();
                $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data yang berhasil diimport');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportUnits importData error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearPreview(): void
    {
        $this->reset(['file', 'previewData', 'validCount', 'errorCount', 'importedCount']);
        $this->filterSheet = 'all';
        $this->filterStatus = 'all';
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

        if (in_array($field, ['name', 'status'], true)) {
            $this->previewData[$rowIndex][$field] = trim((string) $this->previewData[$rowIndex][$field]);
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

        $existingNames = Unit::pluck('name')->map(fn($name) => mb_strtolower(trim((string) $name)))->toArray();

        $name = trim((string) ($this->previewData[$index]['name'] ?? ''));
        $errors = [];

        if ($name === '') {
            $errors[] = 'Nama satuan wajib diisi';
        } elseif (in_array(mb_strtolower($name), $existingNames, true)) {
            $errors[] = 'Nama satuan sudah terdaftar';
        }

        $this->previewData[$index]['name'] = $name;
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
            ->when($this->filterStatus !== 'all', fn($c) => $c->where('status', $this->filterStatus))
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
        return view('livewire.admin.master.units.import-units', [
            'filteredPreviewData' => $this->filteredPreviewData,
            'availableSheets' => $this->availableSheets,
        ]);
    }
}
