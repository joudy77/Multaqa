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
    //     Schema::create('segments', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }
public function up(): void
{
    Schema::create('segments', function (Blueprint $table) {
        $table->id('segment_id');
        $table->unsignedBigInteger('mawdi_id')->nullable();
        $table->integer('page_number')->nullable();
        $table->string('reference_text')->nullable();
        $table->string('reference_surah')->nullable();
        $table->integer('ayah_start')->nullable();
        $table->integer('ayah_end')->nullable();
        $table->longText('plain_text')->nullable();
        $table->longText('html_text')->nullable();
        $table->longText('red_parts_json')->nullable();
        $table->index(['reference_surah', 'ayah_start', 'ayah_end']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
