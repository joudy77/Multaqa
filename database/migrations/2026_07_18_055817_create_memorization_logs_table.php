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
        Schema::create('memorization_logs', function (Blueprint $table) {
            $table->id();
             $table->foreignId('student_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('teacher_id')
          ->constrained('users')
          ->cascadeOnDelete();

    $table->decimal('parts', 3, 1);

    $table->enum('status', [
        'accepted',
        'rejected',
        'absent'
    ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorization_logs');
    }
};
