<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role_type',
        'description',
        'is_management',
    ];

    protected $casts = [
        'is_management' => 'boolean',
    ];

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'position_id');
    }

    public function wakaTeachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'waka_id');
    }
}