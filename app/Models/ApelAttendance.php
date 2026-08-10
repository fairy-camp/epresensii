<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApelAttendance extends Model
{
    use HasFactory;

    protected $table = 'apel_attendances';

    protected $fillable = [
        'teacher_id',
        'date',
        'scan_time',
        'status',
        'notes',
    ];

    /**
     * Relasi ke model Teacher
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}