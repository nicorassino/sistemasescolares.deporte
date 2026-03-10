<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'group_id',
        'type',
        'period',
        'amount',
        'due_date',
        'status',
        'issued_at',
        'paid_at',
        'receipt_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}

