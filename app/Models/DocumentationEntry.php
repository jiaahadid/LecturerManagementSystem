<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentationEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_name',
        'source_path',
        'metadata',
        'documentation'
    ];

    protected $casts = [
        'metadata' => 'array', // Penting untuk menukar JSON ke array dan sebaliknya
    ];
}