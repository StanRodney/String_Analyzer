<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnalyzedString;

class StringController extends Controller
{
    public function analyzeString(Request $request)
    {
        $request->validate([
            'value' => 'required|string'
        ]);

        $value = $request->input('value');
        $lowercaseValue = strtolower($value);

        $length = strlen($value);
        $is_palindrome = $lowercaseValue === strrev($lowercaseValue);
        $unique_characters = count(array_unique(str_split($lowercaseValue)));
        $word_count = str_word_count($value);
        $sha256_hash = hash('sha256', $value);

        $character_frequency_map = [];
        foreach (str_split($lowercaseValue) as $char) {
            $character_frequency_map[$char] = ($character_frequency_map[$char] ?? 0) + 1;
        }

        $analyzedString = AnalyzedString::updateOrCreate(
            ['sha256_hash' => $sha256_hash],
            [
                'value' => $value,
                'length' => $length,
                'is_palindrome' => $is_palindrome,
                'unique_characters' => $unique_characters,
                'word_count' => $word_count,
                'character_frequency_map' => json_encode($character_frequency_map),
            ]
        );

        return response()->json([
            'id' => $analyzedString->id,
            'value' => $analyzedString->value,
            'properties' => [
                'length' => $length,
                'is_palindrome' => $is_palindrome,
                'unique_characters' => $unique_characters,
                'word_count' => $word_count,
                'sha256_hash' => $sha256_hash,
                'character_frequency_map' => $character_frequency_map,
            ],
            'created_at' => now()->toISOString(),
        ], 201);
    }

    public function getString($value)
    {
        $analyzedString = AnalyzedString::where('value', $value)->first();

        if (!$analyzedString) {
            return response()->json([
                'error' => 'String not found in the system.'
            ], 404);
        }

        $characterFrequencyMap = json_decode($analyzedString->character_frequency_map, true);

        return response()->json([
            'id' => $analyzedString->id,
            'value' => $analyzedString->value,
            'properties' => [
                'length' => $analyzedString->length,
                'is_palindrome' => $analyzedString->is_palindrome,
                'unique_characters' => $analyzedString->unique_characters,
                'word_count' => $analyzedString->word_count,
                'sha256_hash' => $analyzedString->sha256_hash,
                'character_frequency_map' => $characterFrequencyMap,
            ],
            'created_at' => $analyzedString->created_at->toISOString(),
        ], 200);
    }

    public function analyze($string)
    {
        $lowercaseValue = strtolower($string);

        $length = strlen($string);
        $is_palindrome = $lowercaseValue === strrev($lowercaseValue);
        $unique_characters = count(array_unique(str_split($lowercaseValue)));
        $word_count = str_word_count($string);
        $sha256_hash = hash('sha256', $string);

        $character_frequency_map = [];
        foreach (str_split($lowercaseValue) as $char) {
            $character_frequency_map[$char] = ($character_frequency_map[$char] ?? 0) + 1;
        }

        // ✅ Save or update in the database
        $analyzedString = AnalyzedString::updateOrCreate(
            ['sha256_hash' => $sha256_hash],
            [
                'value' => $string,
                'length' => $length,
                'is_palindrome' => $is_palindrome,
                'unique_characters' => $unique_characters,
                'word_count' => $word_count,
                'character_frequency_map' => json_encode($character_frequency_map),
            ]
        );

        $response = [
            'id' => $sha256_hash,
            'value' => $string,
            'properties' => [
                'length' => $length,
                'is_palindrome' => $is_palindrome,
                'unique_characters' => $unique_characters,
                'word_count' => $word_count,
                'sha256_hash' => $sha256_hash,
                'character_frequency_map' => $character_frequency_map
            ],
            'created_at' => $analyzedString->created_at->toISOString(),
        ];

        return response()->json($response, 200);
    }

}
