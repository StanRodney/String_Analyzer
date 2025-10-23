<?php

use App\Http\Controllers\AnalyzedStringController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StringController;

// For browser testing
Route::get('/strings/filter-by-natural-language', [\App\Http\Controllers\AnalyzedStringController::class, 'filterByNaturalLanguage']);

Route::get('/strings', [AnalyzedStringController::class, 'index']);

Route::delete('/strings/{string_value}', [AnalyzedStringController::class, 'destroy']);

Route::get('/strings/{string}', [StringController::class, 'analyze']);

Route::post('/strings', [StringController::class, 'analyzeString']);

Route::get('/strings/{value}', [StringController::class, 'getString']);




