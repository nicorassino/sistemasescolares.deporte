<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_id',
        'tutor_id',
        'teacher_id',
        'amount_reported',
        'paid_on_date',
        'status',
        'evidence_file_path',
        'evidence_file_size',
        'evidence_mime_type',
        'transfer_sender_name',
        'bank_reference',
        'admin_comment',
        'reviewed_by',
        'reviewed_at',
        'archived_at',
    ];

    protected $casts = [
        'amount_reported' => 'decimal:2',
        'paid_on_date' => 'date',
        'reviewed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}

