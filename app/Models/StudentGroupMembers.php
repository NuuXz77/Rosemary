<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StudentGroupMember Model (Junction Table)
 * 
 * Tabel penghubung many-to-many antara students dan student_groups.
 * Satu siswa bisa masuk satu atau lebih kelompok.
 */
class StudentGroupMembers extends Model
{
    protected $fillable = [
        'student_group_id', // FK ke student_groups
        'student_id',       // FK ke students
    ];

    // Nonaktifkan timestamp jika hanya perlu created_at, atau aktifkan jika perlu both
    public $timestamps = true;

    /**
     * Relasi Many-to-One ke StudentGroups
     */
    public function studentGroup(): BelongsTo
    {
        return $this->belongsTo(StudentGroups::class, 'student_group_id');
    }

    /**
     * Relasi Many-to-One ke Students
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Students::class, 'student_id');
    }
}
