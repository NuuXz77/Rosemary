<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * StudentGroup Model
 * 
 * Kelompok siswa untuk penugasan dan jadwal kerja.
 */
class StudentGroups extends Model
{
    protected $fillable = [
        'name',      // Nama kelompok
        'class_id',  // FK ke classes
        'division_id', // FK ke divisions
        'start_date',
        'end_date',
        'status',    // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'status'     => 'boolean',
    ];

    /**
     * Relasi Many-to-One ke Classes
     * Banyak kelompok dalam satu kelas
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Relasi Many-to-One ke Divisions
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Divisions::class, 'division_id');
    }

    /**
     * Relasi Many-to-Many ke Students (via student_group_members)
     * Banyak siswa dalam satu kelompok
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Students::class, 'student_group_members', 'student_group_id', 'student_id');
    }

    /**
     * Relasi One-to-Many ke StudentGroupMembers
     * Data relasi langsung ke table junction
     */
    public function members(): HasMany
    {
        return $this->hasMany(StudentGroupMembers::class, 'student_group_id');
    }

    /**
     * Relasi One-to-Many ke Schedules
     * Satu kelompok bisa punya banyak jadwal
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedules::class);
    }

    /**
     * Relasi One-to-Many ke Productions
     * Satu kelompok bisa produce banyak kali
     */
    public function productions(): HasMany
    {
        return $this->hasMany(Productions::class);
    }
}
