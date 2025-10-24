<?php

namespace App\Http\Controllers;

use App\Models\AnalyzedString;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StringController extends Controller
{
    /**
     * POST /api/strings
     * Analyze and store a new string.
     */
    public function analyzeString(Request $request)
    {
        // 1) Missing field => 400 Bad Request
        if (!$request->has('value')) {
            return response()->json(['error' => 'Missing "value" field'], 400);
        }

        $value = $request->input('value');

        // 2) Invalid type => 422 Unprocessable Entity
        if (!is_string($value)) {
            return response()->json(['error' => '"value" must be a string'], 422);
        }

        $value = trim($value);
        $sha256Hash = hash('sha256', $value);

        // 3) Duplicate check by sha256 => 409 Conflict
        if (AnalyzedString::where('sha256_hash', $sha256Hash)->exists()) {
            return response()->json(['error' => 'String already exists'], 409);
        }

        // Compute properties (case-insensitive palindrome check)
        $lower = mb_strtolower($value);
        $length = mb_strlen($value);
        $isPalindrome = $lower === Str::reverse($lower);
        $uniqueCharacters = count(array_unique(preg_split('//u', $lower, -1, PREG_SPLIT_NO_EMPTY)));
        $wordCount = str_word_count($value);

        // Character frequency map (case-insensitive keys)
        $chars = preg_split('//u', $lower, -1, PREG_SPLIT_NO_EMPTY);
        $frequency = [];
        foreach ($chars as $c) {
            $frequency[$c] = ($frequency[$c] ?? 0) + 1;
        }

        // Save record (sha256_hash is stored on model)
        $record = AnalyzedString::create([
            'value' => $value,
            'sha256_hash' => $sha256Hash,
            'length' => $length,
            'is_palindrome' => (bool) $isPalindrome,
            'unique_characters' => $uniqueCharacters,
            'word_count' => $wordCount,
            'character_frequency_map' => $frequency,
        ]);

        // Response must use sha256_hash as id
        return response()->json([
            'id' => $record->sha256_hash,
            'value' => $record->value,
            'properties' => [
                'length' => $record->length,
                'is_palindrome' => (bool) $record->is_palindrome,
                'unique_characters' => $record->unique_characters,
                'word_count' => $record->word_count,
                'sha256_hash' => $record->sha256_hash,
                'character_frequency_map' => $record->character_frequency_map,
            ],
            'created_at' => $record->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * GET /api/strings/{value}
     * Return analyzed string or 404 if not found.
     */
    public function getString($value)
    {
        $record = AnalyzedString::where('value', $value)->first();

        if (!$record) {
            return response()->json(['error' => 'String not found'], 404);
        }

        return response()->json([
            'id' => $record->sha256_hash,
            'value' => $record->value,
            'properties' => [
                'length' => $record->length,
                'is_palindrome' => (bool) $record->is_palindrome,
                'unique_characters' => $record->unique_characters,
                'word_count' => $record->word_count,
                'sha256_hash' => $record->sha256_hash,
                'character_frequency_map' => $record->character_frequency_map,
            ],
            'created_at' => $record->created_at->toIso8601String(),
        ], 200);
    }
}
