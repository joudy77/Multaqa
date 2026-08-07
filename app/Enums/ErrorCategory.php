<?php

namespace App\Enums;

/**
 * أنواع الأخطاء الأربعة المعتمدة بالتسميع، مرتبة حسب الأولوية بالسبر الذكي:
 * تشكيل (أعلى أولوية) > نسيان > حفظ > تجويد (أقل أولوية).
 *
 * ⚠️ هاد enum داخلي (Domain Layer) فقط لميزة السبر الذكي - ما إله
 * أي علاقة بعمود error_type المخزّن فعلياً بجدول recitation_errors،
 * وهاد العمود ظل بالضبط متل ما هو (red/blue/yellow/green) بدون أي
 * تعديل بالباك اند. الربط بين اللون المخزّن وهاد الـ enum يصير حصراً
 * عبر ErrorColorMapper (انظر نفس المجلد).
 */
enum ErrorCategory: string
{
    case Tashkeel = 'tashkeel';         // تشكيل
    case Forgetting = 'forgetting';     // نسيان
    case Memorization = 'memorization'; // حفظ
    case Tajweed = 'tajweed';           // تجويد

    /** أولوية الخطورة: رقم أصغر = أهم عند التعادل بالسبر الذكي */
    public function priority(): int
    {
        return match ($this) {
            self::Tashkeel => 1,
            self::Forgetting => 2,
            self::Memorization => 3,
            self::Tajweed => 4,
        };
    }

    /** الوزن الرقمي المستخدم بحساب score كل كلمة (أعلى = أهم) */
    public function weight(): float
    {
        return match ($this) {
            self::Tashkeel => 4.0,
            self::Forgetting => 3.0,
            self::Memorization => 2.0,
            self::Tajweed => 1.0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Tashkeel => 'خطأ تشكيل',
            self::Forgetting => 'خطأ نسيان',
            self::Memorization => 'خطأ حفظ',
            self::Tajweed => 'خطأ تجويد',
        };
    }

}
