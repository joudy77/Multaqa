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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('last_name');
            $table->string('mother_name');
            $table->string('father_name');
            $table->string('home_address');
            $table->integer('goal');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('achievement')->default(0);
            $table->string('college');
            $table->string('path')->in(['زاد','أترجة']);
            $table->integer('start_page');
            $table->integer('end_page');
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
