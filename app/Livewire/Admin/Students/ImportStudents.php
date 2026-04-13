<?php

namespace App\Livewire\Admin\Students;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Students;
use App\Models\Classes;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Import Data Siswa')]
class ImportStudents extends Component
{
    use WithFileUploads;

    public $file;
    public array $previewData = [];
    public int $validCount = 0;
    public int $errorCount = 0;
    public int $importedCount = 0;
    public bool $isLoading = false;

    // Multiple sheets
    public array $sheets = [];
    public array $selectedSheets = [];
    public bool $showSheetSelection = false;
    public int $totalRowsAllSheets = 0;

    // Preview filters
    public string $filterSheet = 'all';
    public string $filterKelas = 'all';

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls|max:5120',
    ];

    protected $messages = [
        'file.required' => 'File Excel wajib dipilih',
        'file.mimes'    => 'File harus berformat .xlsx atau .xls',
        'file.max'      => 'Ukuran file maksimal 5MB',
    ];

    // ─────────────────────────────────────────
    // DOWNLOAD TEMPLATE
    // ─────────────────────────────────────────

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Siswa');

            // Header
            $sheet->setCellValue('A1', 'PIN');
            $sheet->setCellValue('B1', 'Nama Lengkap');
            $sheet->setCellValue('C1', 'Nama Kelas');
            $sheet->setCellValue('D1', 'Status');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ];
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

            // Sample data
            $sheet->setCellValue('A2', '1001');
            $sheet->setCellValue('B2', 'Budi Santoso');
            $sheet->setCellValue('C2', '10 KULINER 1');
            $sheet->setCellValue('D2', 'active');

            $sheet->setCellValue('A3', '1002');
            $sheet->setCellValue('B3', 'Siti Rahayu');
            $sheet->setCellValue('C3', '10 KULINER 2');
            $sheet->setCellValue('D3', 'active');

            // Add note sheet with available classes
            // $noteSheet = $spreadsheet->createSheet();
            // $noteSheet->setTitle('Daftar Kelas');
            // $noteSheet->setCellValue('A1', 'Nama Kelas (gunakan persis seperti ini)');
            // $noteSheet->getStyle('A1')->applyFromArray($headerStyle);

            // $classes = Classes::where('status', true)->orderBy('name')->get();
            // foreach ($classes as $i => $class) {
            //     $noteSheet->setCellValue('A' . ($i + 2), $class->name);
            // }

            // foreach ($spreadsheet->getAllSheets() as $s) {
            //     foreach (range('A', 'D') as $col) {
            //         $s->getColumnDimension($col)->setAutoSize(true);
            //     }
            // }

            $writer   = new Xlsx($spreadsheet);
            $fileName = 'template_import_siswa.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'import_siswa_');
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('ImportStudents downloadTemplate error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal download template');
            return null;
        }
    }

    // ─────────────────────────────────────────
    // FILE UPLOAD & PREVIEW
    // ─────────────────────────────────────────

    public function updatedFile(): void
    {
        $this->validateOnly('file');
        $this->isLoading = true;

        $this->reset(['previewData', 'validCount', 'errorCount', 'sheets', 'selectedSheets', 'importedCount']);
        $this->showSheetSelection = false;
        $this->filterSheet = 'all';
        $this->filterKelas = 'all';

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

            $path        = $this->file->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheetNames  = $spreadsheet->getSheetNames();

            $this->sheets = [];

            // Build lookup: class name → id
            $classList    = Classes::pluck('id', 'name')->toArray();
            $existingPins = Students::pluck('pin')->toArray();

            $this->totalRowsAllSheets = 0;

            foreach ($sheetNames as $index => $sheetName) {
                $spreadsheet->setActiveSheetIndex($index);
                $rows = $spreadsheet->getActiveSheet()->toArray();
                array_shift($rows); // skip header

                $sheetData = [
                    'name'        => $sheetName,
                    'index'       => $index,
                    'rows'        => [],
                    'valid_count' => 0,
                    'error_count' => 0,
                    'total_rows'  => 0,
                    'selected'    => true,
                ];

                foreach ($rows as $rowIndex => $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $pin      = isset($row[0]) ? trim((string) $row[0]) : '';
                    $nama     = isset($row[1]) ? trim((string) $row[1]) : '';
                    $namaKelas = isset($row[2]) ? trim((string) $row[2]) : '';
                    $status   = isset($row[3]) ? strtolower(trim((string) $row[3])) : 'active';

                    $rowData = [
                        'sheet_name'  => $sheetName,
                        'row_number'  => $rowIndex + 2,
                        'pin'         => $pin,
                        'nama_lengkap' => $nama,
                        'nama_kelas'  => $namaKelas,
                        'status'      => in_array($status, ['active', 'inactive']) ? $status : 'active',
                        'errors'      => [],
                        'has_error'   => false,
                        'kelas_id'    => null,
                    ];

                    // Validate PIN
                    if (empty($rowData['pin'])) {
                        $rowData['errors'][] = 'PIN wajib diisi';
                    } elseif (in_array($rowData['pin'], $existingPins)) {
                        $rowData['errors'][] = 'PIN "' . $rowData['pin'] . '" sudah terdaftar';
                    }

                    // Validate nama
                    if (empty($rowData['nama_lengkap'])) {
                        $rowData['errors'][] = 'Nama wajib diisi';
                    }

                    // Validate kelas
                    if (empty($rowData['nama_kelas'])) {
                        $rowData['errors'][] = 'Nama Kelas wajib diisi';
                    } elseif (!isset($classList[$rowData['nama_kelas']])) {
                        $rowData['errors'][] = 'Kelas "' . $rowData['nama_kelas'] . '" tidak ditemukan';
                    } else {
                        $rowData['kelas_id'] = $classList[$rowData['nama_kelas']];
                    }

                    $rowData['has_error'] = !empty($rowData['errors']);

                    if ($rowData['has_error']) {
                        $sheetData['error_count']++;
                    } else {
                        $sheetData['valid_count']++;
                    }

                    $sheetData['rows'][]   = $rowData;
                    $sheetData['total_rows']++;
                    $this->totalRowsAllSheets++;
                    $this->previewData[] = $rowData;
                }

                $this->sheets[] = $sheetData;
            }

            $this->validCount = collect($this->sheets)->sum('valid_count');
            $this->errorCount = collect($this->sheets)->sum('error_count');
            $this->showSheetSelection = count($this->sheets) > 1;

            if ($this->totalRowsAllSheets === 0) {
                $this->dispatch('show-toast', type: 'warning', message: 'Tidak ada data ditemukan di file ini');
            } else {
                $msg = "File berhasil diupload: {$this->totalRowsAllSheets} data dari " . count($this->sheets) . " sheet";
                if ($this->errorCount > 0) {
                    $msg .= " ({$this->errorCount} error)";
                }
                $this->dispatch('show-toast', type: 'success', message: $msg);
            }

        } catch (\Exception $e) {
            Log::error('ImportStudents preview error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Error membaca file: ' . $e->getMessage());
            $this->reset(['previewData', 'validCount', 'errorCount', 'sheets', 'selectedSheets']);
            $this->showSheetSelection = false;
        } finally {
            $this->isLoading = false;
        }
    }

    // ─────────────────────────────────────────
    // SHEET SELECTION
    // ─────────────────────────────────────────

    public function toggleAllSheets(bool $selected): void
    {
        foreach ($this->sheets as $i => $sheet) {
            $this->sheets[$i]['selected'] = $selected;
        }
        $this->updatePreviewDataFromSelectedSheets();
    }

    public function toggleSheet(int $index): void
    {
        if (isset($this->sheets[$index])) {
            $this->sheets[$index]['selected'] = !$this->sheets[$index]['selected'];
            $this->updatePreviewDataFromSelectedSheets();
        }
    }

    private function updatePreviewDataFromSelectedSheets(): void
    {
        $this->previewData  = [];
        $this->validCount   = 0;
        $this->errorCount   = 0;
        $this->filterSheet  = 'all';
        $this->filterKelas  = 'all';

        foreach ($this->sheets as $sheet) {
            if (!$sheet['selected']) {
                continue;
            }
            foreach ($sheet['rows'] as $row) {
                $this->previewData[] = $row;
                $row['has_error'] ? $this->errorCount++ : $this->validCount++;
            }
        }

        $this->dispatch('show-toast', type: 'info', message: count($this->previewData) . ' data ditampilkan dari sheet terpilih');
    }

    public function updatedPreviewData($value, $key): void
    {
        [$index, $field] = array_pad(explode('.', (string) $key, 2), 2, null);
        if ($field === null || !isset($this->previewData[(int) $index])) {
            return;
        }

        $rowIndex = (int) $index;

        if (in_array($field, ['pin', 'nama_lengkap', 'nama_kelas', 'status'], true)) {
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

        $classList = Classes::pluck('id', 'name')->toArray();
        $existingPins = Students::pluck('pin')->toArray();

        $row = $this->previewData[$index];
        $errors = [];
        $kelasId = null;

        $pin = trim((string) ($row['pin'] ?? ''));
        $nama = trim((string) ($row['nama_lengkap'] ?? ''));
        $namaKelas = trim((string) ($row['nama_kelas'] ?? ''));

        if ($pin === '') {
            $errors[] = 'PIN wajib diisi';
        } elseif (in_array($pin, $existingPins, true)) {
            $errors[] = 'PIN "' . $pin . '" sudah terdaftar';
        }

        if ($nama === '') {
            $errors[] = 'Nama wajib diisi';
        }

        if ($namaKelas === '') {
            $errors[] = 'Nama Kelas wajib diisi';
        } elseif (!isset($classList[$namaKelas])) {
            $errors[] = 'Kelas "' . $namaKelas . '" tidak ditemukan';
        } else {
            $kelasId = $classList[$namaKelas];
        }

        $this->previewData[$index]['pin'] = $pin;
        $this->previewData[$index]['nama_lengkap'] = $nama;
        $this->previewData[$index]['nama_kelas'] = $namaKelas;
        $this->previewData[$index]['kelas_id'] = $kelasId;
        $this->previewData[$index]['errors'] = $errors;
        $this->previewData[$index]['has_error'] = !empty($errors);
    }

    private function recalculatePreviewCounts(): void
    {
        $this->errorCount = collect($this->previewData)->where('has_error', true)->count();
        $this->validCount = collect($this->previewData)->where('has_error', false)->count();
    }

    // ─────────────────────────────────────────
    // IMPORT
    // ─────────────────────────────────────────

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

            $imported     = 0;
            $errors       = [];
            $existingPins = Students::pluck('pin')->toArray();

            foreach ($this->previewData as $row) {
                if ($row['has_error'] || in_array($row['pin'], $existingPins)) {
                    continue;
                }

                try {
                    Students::create([
                        'pin'      => $row['pin'],
                        'name'     => $row['nama_lengkap'],
                        'class_id' => $row['kelas_id'],
                        'status'   => $row['status'] === 'active',
                    ]);
                    $imported++;
                    $existingPins[] = $row['pin'];
                } catch (\Exception $e) {
                    $errors[] = "Sheet {$row['sheet_name']}, Baris {$row['row_number']}: " . $e->getMessage();
                }
            }

            if ($imported > 0) {
                DB::commit();
                $this->importedCount = $imported;

                $msg = "Berhasil import {$imported} data siswa!";
                if (!empty($errors)) {
                    $msg .= ' (' . count($errors) . ' gagal)';
                }
                $this->dispatch('show-toast', type: 'success', message: $msg);
                $this->reset(['file', 'previewData', 'validCount', 'errorCount', 'sheets', 'selectedSheets']);
                $this->showSheetSelection = false;
                $this->dispatch('redirect-after-import');
            } else {
                DB::rollBack();
                $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data yang berhasil diimport');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportStudents importData error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    // ─────────────────────────────────────────
    // HELPERS / FILTERS
    // ─────────────────────────────────────────

    public function clearPreview(): void
    {
        $this->reset(['file', 'previewData', 'validCount', 'errorCount', 'sheets', 'selectedSheets', 'importedCount']);
        $this->showSheetSelection = false;
        $this->filterSheet = 'all';
        $this->filterKelas = 'all';
        $this->dispatch('show-toast', type: 'info', message: 'Data dibersihkan');
    }

    public function setFilterSheet(string $sheet): void
    {
        $this->filterSheet = $sheet;
    }

    public function setFilterKelas(string $kelas): void
    {
        $this->filterKelas = $kelas;
    }

    // Computed property — accessible as $this->filteredPreviewData
    public function getFilteredPreviewDataProperty(): array
    {
        return collect($this->previewData)
            ->map(function ($row, $index) {
                $row['_index'] = $index;
                return $row;
            })
            ->when($this->filterSheet !== 'all', fn ($c) => $c->where('sheet_name', $this->filterSheet))
            ->when($this->filterKelas !== 'all', fn ($c) => $c->where('nama_kelas', $this->filterKelas))
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

    public function getAvailableKelasProperty(): array
    {
        return collect($this->previewData)
            ->pluck('nama_kelas')
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.students.import-students', [
            'filteredPreviewData' => $this->filteredPreviewData,
            'availableSheets'     => $this->availableSheets,
            'availableKelas'      => $this->availableKelas,
        ]);
    }
}
