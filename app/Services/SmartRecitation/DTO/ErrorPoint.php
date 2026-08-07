<?php

namespace App\Services\SmartRecitation\DTO;

use App\Enums\ErrorCategory;

/**
 * تجميع كل الأخطاء المسجّلة على كلمة واحدة (word_id) لطالبة معيّنة،
 * مع score إجمالي وعدد مرات كل نوع خطأ (يُستخدم لكسر التعادل).
 */
final class ErrorPoint
{
    /**
     * @param  array<string,int>  $categoryCounts  مثال: ['tashkeel' => 2, 'forgetting' => 1]
     */
    public function __construct(
        public readonly int $wordId,
        public readonly int $surahNumber,
        public readonly int $ayahNumber,
        public readonly float $score,
        public readonly array $categoryCounts,
    ) {
    }

    /**
     * نوع الخطأ الأكثر تكراراً على هاي الكلمة.
     * عند تعادل بعدد التكرار، يُرجَّح حسب أولوية النوع نفسه
     * (تشكيل > نسيان > حفظ > تجويد) - مطابق للمتطلب الرابع.
     */
    public function dominantCategory(): ErrorCategory
    {
        $best = null;
        $bestCount = -1;

        foreach ($this->categoryCounts as $type => $count) {
            $category = ErrorCategory::from($type);

            $isBetter = $count > $bestCount
                || ($count === $bestCount && $best !== null && $category->priority() < $best->priority());

            if ($isBetter) {
                $best = $category;
                $bestCount = $count;
            }
        }

        return $best ?? ErrorCategory::Tajweed;
    }

    public function dominantCategoryPriority(): int
    {
        return $this->dominantCategory()->priority();
    }
}
