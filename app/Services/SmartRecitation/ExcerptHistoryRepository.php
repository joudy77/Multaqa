<?php

namespace App\Services\SmartRecitation;

use App\Models\SmartRecitationExcerptHistory;
use Illuminate\Support\Collection;

/**
 * طبقة وصول لجدول smart_recitation_excerpt_history:
 * - جلب المقاطع اللي اقترحناها سابقاً لهاي الطالبة (لحساب التبريد).
 * - تسجيل كل مقطع جديد بعد ما يُقترح، حتى ما يتكرر أول شي بالمرة الجاية.
 */
class ExcerptHistoryRepository
{
    private const HISTORY_WINDOW_DAYS = 30; // نتجاهل تاريخ أقدم من هيك (تأثيره أصلاً صفر تقريباً)

    /** @return Collection كل سجل: from_word_id / to_word_id / suggested_at */
    public function recentFor(int $studentId): Collection
    {
        return SmartRecitationExcerptHistory::query()
            ->where('student_id', $studentId)
            ->where('suggested_at', '>=', now()->subDays(self::HISTORY_WINDOW_DAYS))
            ->get(['from_word_id', 'to_word_id', 'suggested_at']);
    }

    public function log(int $studentId, int $fromWordId, int $toWordId): void
    {
        SmartRecitationExcerptHistory::query()->create([
            'student_id' => $studentId,
            'from_word_id' => $fromWordId,
            'to_word_id' => $toWordId,
            'suggested_at' => now(),
        ]);
    }
}
