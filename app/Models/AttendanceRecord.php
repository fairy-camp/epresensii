<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'shift_assignment_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'latitude',
        'longitude',
        'check_out_latitude',
        'check_out_longitude',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function shiftAssignment(): BelongsTo
    {
        return $this->belongsTo(ShiftAssignment::class, 'shift_assignment_id');
    }

    /**
     * Relasi ke WorkSchedule secara tidak langsung via ShiftAssignment
     */
    public function workSchedule(): HasOneThrough
    {
        return $this->hasOneThrough(
            WorkSchedule::class,
            ShiftAssignment::class,
            'id',                  // Key di shift_assignments (relasi ke attendance_records.shift_assignment_id)
            'id',                  // Key di work_schedules (relasi ke shift_assignments.work_schedule_id)
            'shift_assignment_id', // Local key di attendance_records
            'work_schedule_id'     // Local key di shift_assignments
        );
    }

    public function geolocationLog(): HasOne
    {
        return $this->hasOne(GeolocationLog::class, 'attendance_id');
    }
}