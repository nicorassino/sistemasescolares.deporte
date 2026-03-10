<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_id',
        'payment_id',
        'pdf_path',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}

