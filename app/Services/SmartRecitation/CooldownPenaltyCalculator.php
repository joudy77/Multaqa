<?php

namespace App\Services\SmartRecitation;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * يحسب "معامل تبريد" لكل كلمة: كل ما اقترحناها بمقاطع سابقة أكتر
 * وبوقت أقرب، معاملها بينزل (بدون ما يوصل صفر - المشكلة الحقيقية
 * لازم تضل قابلة للظهور لو لسا موجودة فعلاً).
 *
 * factor = 1   → ما في تبريد، الـ score الأصلي كما هو
 * factor = 0.15 → أقصى تخفيض ممكن (85%) لو اتكرر اقتراحها أكتر من مرة بوقت قريب
 *
 * بالإضافة: hasHardBlock() بترجع true لو الكلمة اتقترحت خلال آخر
 * HARD_BLOCK_HOURS ساعة (افتراضياً 6 ساعات) - تُستخدم بـ
 * SmartRecitationSelector لاستبعادها كلياً من سبر لسبر بنفس اليوم/
 * بعد فترة قصيرة، مش بس تخفيض جزئي.
 */
class CooldownPenaltyCalculator
{
    private const COOLDOWN_HALF_LIFE_DAYS = 5.0; // أسرع خبو من أهمية الخطأ نفسها (14 يوم)
    private const MAX_REDUCTION = 0.85;
    private const HARD_BLOCK_HOURS = 6;

    /** @param  Collection  $history  خرجة ExcerptHistoryRepository::recentFor() */
    public function factorFor(int $wordId, Collection $history): float
    {
        $accumulatedPenalty = 0.0;

        foreach ($history as $entry) {
            if (! $this->covers($wordId, $entry)) {
                continue;
            }

            $daysAgo = max(0, Carbon::parse($entry->suggested_at)->diffInDays(now()));
            $accumulatedPenalty += 0.5 ** ($daysAgo / self::COOLDOWN_HALF_LIFE_DAYS);
        }

        $reduction = min(self::MAX_REDUCTION, $accumulatedPenalty / (1 + $accumulatedPenalty));

        return 1 - $reduction;
    }

    /**
     * true لو الكلمة اتقترحت خلال آخر HARD_BLOCK_HOURS ساعة - يعني
     * لازم تُستبعد كلياً (مش بس تخفيض score) من سبر لسبر قريب.
     */
    public function hasHardBlock(int $wordId, Collection $history): bool
    {
        foreach ($history as $entry) {
            if (! $this->covers($wordId, $entry)) {
                continue;
            }

            $hoursAgo = Carbon::parse($entry->suggested_at)->diffInHours(now());
            if ($hoursAgo < self::HARD_BLOCK_HOURS) {
                return true;
            }
        }

        return false;
    }

    private function covers(int $wordId, object $entry): bool
    {
        return $wordId >= $entry->from_word_id && $wordId <= $entry->to_word_id;
    }
}
