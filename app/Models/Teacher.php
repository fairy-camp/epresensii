<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'nik',
        'nuptk',
        'npy',
        'full_name',
        'gender',
        'photo',
        'department',
        'position_id',
        'waka_id',
        'tmt',
        'phone',
        'work_schedule_id',
        'is_active',
    ];

    protected $casts = [
        'tmt' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function wakaPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'waka_id');
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'teacher_id');
    }

    // Mengambil QR Code yang sedang aktif saja
    public function activeQrCode(): HasOne
    {
        return $this->hasOne(QrCode::class, 'teacher_id')->where('is_active', true);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class, 'teacher_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'teacher_id');
    }
    
    /**
     * Relasi ke Riwayat Apel Pagi
     */
    public function apelAttendances()
    {
        return $this->hasMany(ApelAttendance::class, 'teacher_id');
    }
}