<?php

namespace App\Livewire\Admin\Settings\Logs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\File;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $search = '';
    public string $filterLevel = '';
    public string $selectedFile = '';
    public int $linesPerPage = 100;

    #[Title('Sistem Logs')]
    public function render()
    {
        $logPath = storage_path('logs');
        $logFiles = $this->getLogFiles($logPath);
        $logEntries = $this->parseLogEntries();

        return view('livewire.admin.settings.logs.index', [
            'logFiles' => $logFiles,
            'logEntries' => $logEntries,
        ]);
    }

    /**
     * Get all log files from storage/logs directory
     */
    private function getLogFiles(string $logPath): array
    {
        if (!File::exists($logPath)) {
            return [];
        }

        $files = File::files($logPath);
        $logFiles = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'log') {
                $logFiles[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getRealPath(),
                    'size' => $this->formatFileSize($file->getSize()),
                    'sizeBytes' => $file->getSize(),
                    'lastModified' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        // Sort by last modified (newest first)
        usort($logFiles, function ($a, $b) {
            return strcmp($b['lastModified'], $a['lastModified']);
        });

        return $logFiles;
    }

    /**
     * Parse log entries from selected file
     */
    private function parseLogEntries(): array
    {
        if (empty($this->selectedFile)) {
            // Default to latest log file
            $logPath = storage_path('logs/laravel.log');
            if (!File::exists($logPath)) {
                return [];
            }
        } else {
            $logPath = storage_path('logs/' . basename($this->selectedFile));
            if (!File::exists($logPath)) {
                return [];
            }
        }

        $content = File::get($logPath);
        $lines = explode("\n", $content);

        // Parse log entries (each entry starts with a date pattern)
        $entries = [];
        $currentEntry = null;

        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}\.?\d*[\+\-]?\d{0,4})\]\s+(\w+)\.(\w+):\s*(.*)/', $line, $matches)) {
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => strtolower($matches[3]),
                    'message' => $matches[4],
                    'stackTrace' => '',
                ];
            } elseif ($currentEntry && !empty(trim($line))) {
                $currentEntry['stackTrace'] .= $line . "\n";
            }
        }

        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        // Sort newest first
        $entries = array_reverse($entries);

        // Filter by level
        if (!empty($this->filterLevel)) {
            $entries = array_filter($entries, function ($entry) {
                return $entry['level'] === $this->filterLevel;
            });
            $entries = array_values($entries);
        }

        // Filter by search
        if (!empty($this->search)) {
            $searchLower = strtolower($this->search);
            $entries = array_filter($entries, function ($entry) use ($searchLower) {
                return str_contains(strtolower($entry['message']), $searchLower) ||
                       str_contains(strtolower($entry['stackTrace']), $searchLower);
            });
            $entries = array_values($entries);
        }

        // Limit entries
        return array_slice($entries, 0, $this->linesPerPage);
    }

    /**
     * Select a log file to view
     */
    public function selectFile(string $filename): void
    {
        $this->selectedFile = basename($filename);
        $this->search = '';
        $this->filterLevel = '';
        $this->linesPerPage = 100;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterLevel = '';
        $this->linesPerPage = 100;
    }

    /**
     * Clear the selected log file
     */
    public function clearLogFile(): void
    {
        $filename = $this->selectedFile ?: 'laravel.log';
        $logPath = storage_path('logs/' . basename($filename));

        if (File::exists($logPath)) {
            File::put($logPath, '');
            session()->flash('success', "Log file '{$filename}' berhasil dikosongkan.");
        }
    }

    /**
     * Download the selected log file
     */
    public function downloadLogFile(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $this->selectedFile ?: 'laravel.log';
        $logPath = storage_path('logs/' . basename($filename));

        return response()->streamDownload(function () use ($logPath) {
            echo File::get($logPath);
        }, $filename, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Delete a specific log file
     */
    public function deleteLogFile(string $filename): void
    {
        $logPath = storage_path('logs/' . basename($filename));

        if (File::exists($logPath) && basename($filename) !== 'laravel.log') {
            File::delete($logPath);
            if ($this->selectedFile === basename($filename)) {
                $this->selectedFile = '';
            }
            session()->flash('success', "Log file '{$filename}' berhasil dihapus.");
        } else {
            session()->flash('error', "Tidak dapat menghapus file log utama 'laravel.log'. Gunakan 'Kosongkan' sebagai gantinya.");
        }
    }

    /**
     * Format file size to human readable
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
