<?php

namespace App\Services\SmartRecitation;

use App\Enums\ErrorCategory;

/**
 * ملف جديد بالكامل - ما بيلمس ولا ملف موجود بالباك اند.
 *
 * جدول recitation_errors ظل بالضبط متل ما هو (error_type = red/blue/yellow/green)
 * ولا الـ StoreRecitationErrorsRequest تغيّر ولا شي. هاد الكلاس بس طبقة تفسير
 * (Mapping Layer) داخلية تُستخدم فقط جوا ميزة السبر الذكي، لتحويل اللون
 * المخزّن أصلاً لمعناه الدلالي (نوع الخطأ) حسب تحديدك:
 *
 *   red    → حفظ         (Memorization)
 *   yellow → تشكيل        (Tashkeel)   ← الأولوية الأعلى
 *   blue   → نسيان        (Forgetting)
 *   green  → تجويد        (Tajweed)    ← الأولوية الأدنى
 */
final class ErrorColorMapper
{
    private const MAP = [
        'yellow' => ErrorCategory::Tashkeel,
        'blue' => ErrorCategory::Forgetting,
        'red' => ErrorCategory::Memorization,
        'green' => ErrorCategory::Tajweed,
    ];

    public function categoryFor(string $storedColor): ErrorCategory
    {
        return self::MAP[$storedColor]
            ?? throw new \InvalidArgumentException("لون خطأ غير معروف بجدول recitation_errors: {$storedColor}");
    }
}
