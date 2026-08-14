<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'shift_assignment_id',
        'work_schedule_id',        // DITAMBAHKAN
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'latitude',                // DITAMBAHKAN
        'longitude',               // DITAMBAHKAN
        'check_out_latitude',      // DITAMBAHKAN
        'check_out_longitude',     // DITAMBAHKAN
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

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }

    public function geolocationLog(): HasOne
    {
        return $this->hasOne(GeolocationLog::class, 'attendance_id');
    }
}