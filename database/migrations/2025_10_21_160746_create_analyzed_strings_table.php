<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analyzed_strings', function (Blueprint $table) {
            $table->id();
            $table->string('value')->unique();
            $table->string('sha256_hash')->unique();
            $table->integer('length');
            $table->boolean('is_palindrome');
            $table->integer('unique_characters');
            $table->integer('word_count');
            $table->json('character_frequency_map');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyzed_strings');
    }
};
