<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'dni',
        'birth_date',
        'gender',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function tutors()
    {
        return $this->belongsToMany(Tutor::class, 'tutor_student')
            ->withPivot(['relationship_type', 'is_primary'])
            ->withTimestamps();
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_student')
            ->withPivot(['from_date', 'to_date', 'is_current'])
            ->withTimestamps();
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }
}

