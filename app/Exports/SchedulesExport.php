<?php

namespace App\Exports;

use App\Models\Schedules;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SchedulesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate, $endDate, $filterShift, $filterGroup, $filterDivision, $search;

    public function __construct($startDate, $endDate, $filterShift, $filterGroup, $filterDivision, $search)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterShift = $filterShift;
        $this->filterGroup = $filterGroup;
        $this->filterDivision = $filterDivision;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Schedules::query()
            ->with(['shift', 'studentGroup', 'division'])
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterGroup, fn($q) => $q->where('student_group_id', $this->filterGroup))
            ->when($this->filterDivision, fn($q) => $q->where('division_id', $this->filterDivision))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('studentGroup', fn($g) => $g->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('division', fn($d) => $d->where('name', 'like', '%' . $this->search . '%'));
                });
            });

        return $query->orderBy('date', 'desc')->get();
    }

    public function map($schedule): array
    {
        return [
            $schedule->date,
            $schedule->studentGroup->name ?? '-',
            $schedule->division->name ?? '-',
            $schedule->shift->name ?? '-',
            $schedule->status ? 'Aktif' : 'Nonaktif',
            $schedule->notes,
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kelompok Siswa',
            'Divisi',
            'Shift',
            'Status',
            'Catatan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
