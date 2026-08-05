<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة فقط (عمود جديد nullable بقيمة افتراضية false) - ما بيلمس
 * ولا صف موجود بالجدول. الغرض: تمييز جلسات "السبر الذكي" (تبدأ من
 * الأنسة عبر SmartRecitationController@createSession) عن جلسات
 * التسميع العادية (تبدأ من الطالبة عبر RecitationSessionController@store)
 * بنفس جدول recitation_sessions وبنفس شاشة السجل/التاريخ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->boolean('is_smart_review')->nullable()->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropColumn('is_smart_review');
        });
    }
};
