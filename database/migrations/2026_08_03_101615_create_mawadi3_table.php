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
    //     Schema::create('mawadi3', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }
public function up(): void
{
    Schema::create('mawadi3', function (Blueprint $table) {
        $table->id('mawdi_id');
        $table->integer('mawdi_number')->nullable();
        $table->unsignedBigInteger('surah_id')->nullable();
        $table->string('reference_text')->nullable();
        $table->integer('start_page')->nullable();
        $table->integer('end_page')->nullable();
        $table->longText('plain_text')->nullable();
        $table->longText('html_text')->nullable();
        $table->longText('normalized_text')->nullable();
        $table->boolean('is_closed')->default(false);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mawadi3');
    }
};
