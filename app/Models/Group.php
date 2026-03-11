<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'name',
        'description',
        'year',
        'level',
        'max_capacity',
        'is_active',
    ];

    protected $casts = [
        'year' => 'integer',
        'max_capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'group_student')
            ->withPivot(['from_date', 'to_date', 'is_current'])
            ->withTimestamps();
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}

