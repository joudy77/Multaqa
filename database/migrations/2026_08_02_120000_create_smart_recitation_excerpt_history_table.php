<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول جديد بالكامل (إضافة فقط - ما بيلمس ولا جدول موجود).
 *
 * الغرض: تسجيل كل مقطع تم اقتراحه فعلياً للطالبة عبر السبر الذكي،
 * حتى نقدر "نبرّد" (Cooldown) نفس المقطع بالمرة الجاية بدل ما يتكرر
 * أول شي كل مرة، مهما كان score الأخطاء فيه عالي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_recitation_excerpt_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->unsignedInteger('from_word_id');
            $table->unsignedInteger('to_word_id');
            $table->timestamp('suggested_at')->useCurrent();

            $table->index(['student_id', 'from_word_id', 'to_word_id'], 'smart_recitation_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_recitation_excerpt_history');
    }
};
