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
        Schema::create('quran_words', function (Blueprint $table) {
           $table->id(); // نفس الـ id الأصلي من QUL
    $table->string('location');
    $table->unsignedInteger('surah');
    $table->unsignedInteger('ayah');
    $table->unsignedInteger('word');
    $table->text('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quran_words');
    }
};
