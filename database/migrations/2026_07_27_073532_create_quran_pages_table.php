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
        Schema::create('quran_pages', function (Blueprint $table) {
            // $table->id();
            // $table->timestamps();
            $table->unsignedInteger('page_number');
    $table->unsignedInteger('line_number');
    $table->string('line_type');
    $table->boolean('is_centered')->default(false);
    $table->unsignedInteger('first_word_id')->nullable();
    $table->unsignedInteger('last_word_id')->nullable();
    $table->unsignedInteger('surah_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quran_pages');
    }
};
