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
        Schema::create('recitation_errors', function (Blueprint $table) {
            $table->id();
             $table->foreignId('session_id')->constrained('recitation_sessions')->cascadeOnDelete();
    $table->foreignId('student_id')->constrained(); 

    $table->unsignedInteger('word_id');   
    $table->unsignedInteger('surah_number');
    $table->unsignedInteger('ayah_number');

    $table->string('error_type');   

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recitation_errors');
    }
};
