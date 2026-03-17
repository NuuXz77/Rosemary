<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// type: 'cashier' | 'production'

/**
 * Schedule Model
 * 
 * Jadwal penugasan yang menentukan kelompok siswa bertugas di shift dan divisi tertentu pada tanggal tertentu.
 */
class Schedules extends Model
{
    public const ABSENCE_NONE = 'none';
    public const ABSENCE_SICK = 'sick';
    public const ABSENCE_PERMIT = 'permit';
    public const ABSENCE_LEAVE = 'leave';
    public const ABSENCE_OTHER = 'other';
    public const ABSENCE_RESCHEDULED = 'rescheduled';

    protected $fillable = [
        'type',               // 'cashier' | 'production'
        'date',               // Tanggal jadwal
        'shift_id',           // FK ke shifts
        'student_id',         // FK ke students (cashier only)
        'student_group_id',   // FK ke student_groups (production only)
        'division_id',        // FK ke divisions (production only)
        'status',             // Boolean: aktif/nonaktif
        'absence_type',       // none|sick|permit|leave|other|rescheduled
        'absence_note',       // catatan ketidakhadiran/perubahan jadwal
        'replaced_from_schedule_id', // jadwal asal (untuk jadwal pengganti)
        'replaced_by_schedule_id',   // jadwal pengganti yang menggantikan jadwal ini
    ];

    protected $casts = [
        'date'   => 'date',
        'status' => 'boolean',
    ];

    public function replacedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_from_schedule_id');
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_schedule_id');
    }

    public function isUnavailable(): bool
    {
        return $this->absence_type !== self::ABSENCE_NONE;
    }

    /**
     * Relasi Many-to-One ke Students (kasir only)
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    /**
     * Relasi Many-to-One ke Shifts
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relasi Many-to-One ke StudentGroups
     */
    public function studentGroup(): BelongsTo
    {
        return $this->belongsTo(StudentGroups::class, 'student_group_id');
    }

    /**
     * Relasi Many-to-One ke Divisions
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Divisions::class, 'division_id');
    }
}
