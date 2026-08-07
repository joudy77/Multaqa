<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('word_colors', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }
    public function up(): void
{
    Schema::create('word_colors', function (Blueprint $table) {
        $table->unsignedBigInteger('word_id')->primary();
        $table->integer('surah');
        $table->integer('ayah');
        $table->integer('position');
        $table->string('word_text')->nullable();
        $table->boolean('is_red')->default(false);
        $table->unsignedBigInteger('mawdi_id')->nullable();
        $table->unsignedBigInteger('segment_id')->nullable();
        $table->index(['surah', 'ayah', 'position']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_colors');
    }
};
