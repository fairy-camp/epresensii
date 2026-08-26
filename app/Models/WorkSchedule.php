<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'check_in_time',
        'start_check_in_time',
        'end_check_in_time',
        'check_out_time',
        'start_check_out_time',
        'end_check_out_time',
        'late_tolerance_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'late_tolerance_minutes' => 'integer',
    ];

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'work_schedule_id');
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class, 'work_schedule_id');
    }
}