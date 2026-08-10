<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeolocationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'teacher_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'distance_from_school',
        'is_within_radius',
        'permission_status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_meters' => 'float',
        'distance_from_school' => 'float',
        'is_within_radius' => 'boolean',
    ];

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}