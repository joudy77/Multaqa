<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول جديد بالكامل (إضافة فقط). يخزّن الأسئلة "المجمّدة" لكل جلسة
 * سبر ذكي وقت إنشائها - حتى لو الأنسة سكرت الشاشة ورجعت فتحت نفس
 * الجلسة (upcoming) لاحقاً، بترجع تشوف نفس الأسئلة بالضبط، مش
 * مجموعة جديدة. الحذف يصير تلقائياً (cascade) لما تُحذف الجلسة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_recitation_session_excerpts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('recitation_sessions')->cascadeOnDelete();
            $table->unsignedInteger('order_index');
            $table->unsignedInteger('from_word_id');
            $table->unsignedInteger('to_word_id');
            $table->unsignedInteger('from_page');
            $table->unsignedInteger('to_page');
            $table->unsignedInteger('from_line');
            $table->unsignedInteger('to_line');
            $table->float('score')->default(0);
            $table->string('dominant_category')->nullable(); // null = موضع عشوائي (ما إله أخطاء مسجّلة)
            $table->json('category_breakdown')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'order_index'], 'smart_session_excerpts_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_recitation_session_excerpts');
    }
};
