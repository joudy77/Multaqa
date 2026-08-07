<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tafsirs', function (Blueprint $table) {
            $table->id();
            $table->string('ayah_key');           // مثال: "5:84"
            $table->string('ayah_keys')->nullable(); // كل مفاتيح المجموعة لو متوفرة، مثال: "5:84,5:85,5:86"
            $table->unsignedInteger('surah');
            $table->unsignedInteger('from_ayah');
            $table->unsignedInteger('to_ayah');
            $table->string('group_ayah_key')->nullable();
            $table->longText('text');
            $table->timestamps();

            $table->index(['surah', 'from_ayah', 'to_ayah']); // يسرّع البحث لاحقًا
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tafsirs');
    }
};