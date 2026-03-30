<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGroupAttendance extends Model
{
    protected $table = 'student_group_attendances';

    protected $fillable = [
        'student_group_id',
        'schedule_id',
        'shift_id',
        'date',
        'login_time',
        'shift_start',
        'status',
        'late_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'login_time' => 'datetime',
        'late_minutes' => 'integer',
    ];

    public function studentGroup(): BelongsTo
    {
        return $this->belongsTo(StudentGroups::class, 'student_group_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedules::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
