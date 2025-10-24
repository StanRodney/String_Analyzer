<?php

namespace App\Http\Controllers;

use App\Models\AnalyzedString;
use Illuminate\Http\Request;

class AnalyzedStringController extends Controller
{
    /**
     * GET /api/strings
     * Return filtered listing with filters_applied.
     */
    public function index(Request $request)
    {
        $query = AnalyzedString::query();
        $filtersApplied = [];

        // is_palindrome (boolean)
        if ($request->has('is_palindrome')) {
            $val = $request->query('is_palindrome');
            $bool = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($bool === null) {
                return response()->json(['error' => 'Invalid value for is_palindrome'], 400);
            }
            $query->where('is_palindrome', $bool);
            $filtersApplied['is_palindrome'] = $bool;
        }

        // min_length (integer)
        if ($request->has('min_length')) {
            if (!is_numeric($request->query('min_length'))) {
                return response()->json(['error' => 'min_length must be an integer'], 400);
            }
            $min = (int) $request->query('min_length');
            $query->where('length', '>=', $min);
            $filtersApplied['min_length'] = $min;
        }

        // max_length (integer)
        if ($request->has('max_length')) {
            if (!is_numeric($request->query('max_length'))) {
                return response()->json(['error' => 'max_length must be an integer'], 400);
            }
            $max = (int) $request->query('max_length');
            $query->where('length', '<=', $max);
            $filtersApplied['max_length'] = $max;
        }

        // word_count (integer)
        if ($request->has('word_count')) {
            if (!is_numeric($request->query('word_count'))) {
                return response()->json(['error' => 'word_count must be an integer'], 400);
            }
            $wc = (int) $request->query('word_count');
            $query->where('word_count', $wc);
            $filtersApplied['word_count'] = $wc;
        }

        // contains_character (single letter)
        if ($request->has('contains_character')) {
            $char = strtolower($request->query('contains_character'));
            if (mb_strlen($char) !== 1) {
                return response()->json(['error' => 'contains_character must be a single character'], 400);
            }

            // Use JSON_EXTRACT presence check (works if DB supports JSON functions)
            $query->whereRaw("JSON_EXTRACT(character_frequency_map, '$.\"$char\"') IS NOT NULL");
            $filtersApplied['contains_character'] = $char;
        }

        $results = $query->get();

        // map to expected output format
        $data = $results->map(function ($item) {
            return [
                'id' => $item->sha256_hash,
                'value' => $item->value,
                'properties' => [
                    'length' => $item->length,
                    'is_palindrome' => (bool) $item->is_palindrome,
                    'unique_characters' => $item->unique_characters,
                    'word_count' => $item->word_count,
                    'sha256_hash' => $item->sha256_hash,
                    'character_frequency_map' => $item->character_frequency_map,
                ],
                'created_at' => $item->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'count' => $data->count(),
            'filters_applied' => $filtersApplied,
        ], 200);
    }

    /**
     * DELETE /api/strings/{string_value}
     */
    public function destroy($stringValue)
    {
        $record = AnalyzedString::where('value', $stringValue)->first();

        if (!$record) {
            return response()->json(['error' => 'String not found'], 404);
        }

        $record->delete();

        // 204 No Content
        return response()->noContent();
    }

    /**
     * GET /api/strings/filter-by-natural-language?query=...
     */
    public function filterByNaturalLanguage(Request $request)
    {
        $queryText = strtolower((string) $request->query('query', ''));

        if (trim($queryText) === '') {
            return response()->json(['error' => 'Missing query parameter'], 400);
        }

        $filters = [];

        if (str_contains($queryText, 'palindromic') || str_contains($queryText, 'palindrome')) {
            $filters['is_palindrome'] = true;
        }

        if (str_contains($queryText, 'single word')) {
            $filters['word_count'] = 1;
        }

        if (preg_match('/longer than (\d+)/', $queryText, $m)) {
            $filters['min_length'] = (int)$m[1] + 1;
        }

        if (preg_match('/shorter than (\d+)/', $queryText, $m)) {
            $filters['max_length'] = (int)$m[1] - 1;
        }

        if (preg_match('/containing the letter ([a-z])/', $queryText, $m)) {
            $filters['contains_character'] = strtolower($m[1]);
        }

        // special heuristic: "first vowel" -> 'a'
        if (str_contains($queryText, 'first vowel')) {
            $filters['contains_character'] = 'a';
        }

        if (empty($filters)) {
            return response()->json(['error' => 'Unable to parse natural language query'], 400);
        }

        // detect conflicting filters (min > max)
        if (isset($filters['min_length'], $filters['max_length']) && $filters['min_length'] > $filters['max_length']) {
            return response()->json(['error' => 'Query parsed but resulted in conflicting filters'], 422);
        }

        $q = AnalyzedString::query();

        if (isset($filters['is_palindrome'])) {
            $q->where('is_palindrome', $filters['is_palindrome']);
        }
        if (isset($filters['word_count'])) {
            $q->where('word_count', $filters['word_count']);
        }
        if (isset($filters['min_length'])) {
            $q->where('length', '>=', $filters['min_length']);
        }
        if (isset($filters['max_length'])) {
            $q->where('length', '<=', $filters['max_length']);
        }
        if (isset($filters['contains_character'])) {
            $char = $filters['contains_character'];
            $q->whereRaw("JSON_EXTRACT(character_frequency_map, '$.\"$char\"') IS NOT NULL");
        }

        $results = $q->get();

        $data = $results->map(function ($item) {
            return [
                'id' => $item->sha256_hash,
                'value' => $item->value,
                'properties' => [
                    'length' => $item->length,
                    'is_palindrome' => (bool) $item->is_palindrome,
                    'unique_characters' => $item->unique_characters,
                    'word_count' => $item->word_count,
                    'sha256_hash' => $item->sha256_hash,
                    'character_frequency_map' => $item->character_frequency_map,
                ],
                'created_at' => $item->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'count' => $data->count(),
            'interpreted_query' => [
                'original' => $queryText,
                'parsed_filters' => $filters,
            ],
        ], 200);
    }
}
