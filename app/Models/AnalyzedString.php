<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyzedString extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'sha256_hash',
        'length',
        'is_palindrome',
        'unique_characters',
        'word_count',
        'character_frequency_map',
    ];

    protected $casts = [
        'character_frequency_map' => 'array',
    ];
}
