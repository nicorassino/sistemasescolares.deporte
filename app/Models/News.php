<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'body',
        'image_path',
        'external_link',
        'is_published',
        'published_at',
        'expires_at',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'sort_order' => 'integer',
    ];
}

