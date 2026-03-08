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
    protected $fillable = [
        'type',               // 'cashier' | 'production'
        'date',               // Tanggal jadwal
        'shift_id',           // FK ke shifts
        'student_id',         // FK ke students (cashier only)
        'student_group_id',   // FK ke student_groups (production only)
        'division_id',        // FK ke divisions (production only)
        'status',             // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'date'   => 'date',
        'status' => 'boolean',
    ];

    /**
     * Relasi Many-to-One ke Students (kasir only)
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Students::class);
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
        return $this->belongsTo(StudentGroups::class);
    }

    /**
     * Relasi Many-to-One ke Divisions
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Divisions::class);
    }
}
