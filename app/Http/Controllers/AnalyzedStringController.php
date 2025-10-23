<?php

namespace App\Http\Controllers;

use App\Models\AnalyzedString;
use Illuminate\Http\Request;

class AnalyzedStringController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalyzedString::query();
        $filtersApplied = [];


        if ($request->has('is_palindrome')) {
            $isPalindrome = filter_var($request->get('is_palindrome'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_palindrome', $isPalindrome);
            $filtersApplied['is_palindrome'] = $isPalindrome;
        }

        if ($request->has('min_length')) {
            $minLength = (int)$request->get('min_length');
            $query->where('length', '>=', $minLength);
            $filtersApplied['min_length'] = $minLength;
        }

        if ($request->has('max_length')) {
            $maxLength = (int)$request->get('max_length');
            $query->where('length', '<=', $maxLength);
            $filtersApplied['max_length'] = $maxLength;
        }

        if ($request->has('word_count')) {
            $wordCount = (int)$request->get('word_count');
            $query->where('word_count', $wordCount);
            $filtersApplied['word_count'] = $wordCount;
        }

        if ($request->has('contains_character')) {
            $char = strtolower($request->get('contains_character'));
            $query->whereRaw("JSON_EXTRACT(character_frequency_map, '$.\"$char\"') IS NOT NULL");
            $filtersApplied['contains_character'] = $char;
        }

        $results = $query->get();

        return response()->json([
            'data' => $results,
            'count' => $results->count(),
            'filters_applied' => $filtersApplied
        ]);
    }
    public function destroy($stringValue)
    {
        $string = AnalyzedString::where('value', $stringValue)->first();

        if (!$string) {
            return response()->json(['error' => 'String not found'], 404);
        }

        $string->delete();

        return response()->noContent(); // 204 No Content
    }


    public function filterByNaturalLanguage(Request $request)
    {
        $queryText = strtolower($request->get('query'));

        if (!$queryText) {
            return response()->json(['error' => 'Missing query parameter'], 400);
        }

        $filters = [];

        if (str_contains($queryText, 'palindromic')) {
            $filters['is_palindrome'] = true;
        }

        if (preg_match('/longer than (\d+)/', $queryText, $matches)) {
            $filters['min_length'] = (int)$matches[1] + 1;
        }

        if (preg_match('/shorter than (\d+)/', $queryText, $matches)) {
            $filters['max_length'] = (int)$matches[1] - 1;
        }

        if (preg_match('/containing the letter ([a-z])/', $queryText, $matches)) {
            $filters['contains_character'] = $matches[1];
        }

        if (str_contains($queryText, 'single word')) {
            $filters['word_count'] = 1;
        }

        if (empty($filters)) {
            return response()->json(['error' => 'Unable to parse natural language query'], 400);
        }

        $query = AnalyzedString::query();

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'is_palindrome':
                    $query->where('is_palindrome', $value);
                    break;
                case 'min_length':
                    $query->where('length', '>=', $value);
                    break;
                case 'max_length':
                    $query->where('length', '<=', $value);
                    break;
                case 'word_count':
                    $query->where('word_count', $value);
                    break;
                case 'contains_character':
                    $query->whereRaw("JSON_EXTRACT(character_frequency_map, '$.\"$value\"') IS NOT NULL");
                    break;
            }
        }

        $results = $query->get();

        return response()->json([
            'data' => $results,
            'count' => $results->count(),
            'interpreted_query' => [
                'original' => $queryText,
                'parsed_filters' => $filters
            ]
        ]);
    }

}
