<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery360 extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'hotspots',
        'is_active',
    ];

    // Dato extra: Esto le dice a Laravel que 'hotspots' es un JSON y no un simple texto
    protected $casts = [
        'hotspots' => 'array',
    ];
}