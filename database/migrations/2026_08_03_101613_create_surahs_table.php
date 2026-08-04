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
    //     Schema::create('surahs', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }
public function up(): void
{
    Schema::create('surahs', function (Blueprint $table) {
        $table->id('surah_id');
        $table->string('surah_name');
        $table->integer('order_in_book')->nullable();
        $table->integer('first_page')->nullable();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surahs');
    }
};
