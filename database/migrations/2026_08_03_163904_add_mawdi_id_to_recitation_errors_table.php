<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_errors', function (Blueprint $table) {
            $table->unsignedBigInteger('mawdi_id')->nullable()->after('error_type');
            $table->foreign('mawdi_id')->references('mawdi_id')->on('mawadi3')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recitation_errors', function (Blueprint $table) {
            $table->dropForeign(['mawdi_id']);
            $table->dropColumn('mawdi_id');
        });
    }
};