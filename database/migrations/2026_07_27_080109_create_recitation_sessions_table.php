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
        Schema::create('recitation_sessions', function (Blueprint $table) {
            $table->id();
             $table->foreignId('student_id')->constrained();
    $table->foreignId('teacher_id')->nullable()->constrained('users'); // أو حسب جدول المعلمات عندكم

    $table->unsignedInteger('from_page');
    $table->unsignedInteger('to_page');

    $table->enum('status', ['upcoming', 'accepted', 'rejected', 'excused'])
          ->default('upcoming');

    $table->date('scheduled_date')->nullable();   
    $table->timestamp('reviewed_at')->nullable(); 
    $table->text('notes')->nullable();             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recitation_sessions');
    }
};
