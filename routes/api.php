<?php

use App\Http\Controllers\AnalyzedStringController;
use App\Http\Controllers\StringController;
use Illuminate\Support\Facades\Route;

// Natural language filtering
Route::get('/strings/filter-by-natural-language', [AnalyzedStringController::class, 'filterByNaturalLanguage']);

// Filtered listing
Route::get('/strings', [AnalyzedStringController::class, 'index']);

// Delete string
Route::delete('/strings/{string_value}', [AnalyzedStringController::class, 'destroy']);

// Analyze and store new string
Route::post('/strings', [StringController::class, 'analyzeString']);

// Retrieve a specific analyzed string
Route::get('/strings/{value}', [StringController::class, 'getString']);
