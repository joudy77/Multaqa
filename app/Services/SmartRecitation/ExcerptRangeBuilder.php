<?php

namespace App\Services\SmartRecitation;

use App\Services\SmartRecitation\DTO\SuggestedExcerptRange;
use Illuminate\Support\Collection;

/**
 * يبني مقطع السؤال حول كلمة "ساخنة" (أعلى نقطة أخطاء):
 * - الهدف ~15 سطر (طول الصفحة القياسي بالمصحف المدني).
 * - التوسيع بالتناوب قبل/بعد نقطة الخطأ (مو بالضرورة من بداية الصفحة).
 * - ممكن يعبر لصفحة تالية أو سابقة، بحد أقصى صفحة وحدة زيادة عن
 *   مجال الانسة (from_page - 1 .. to_page + 1) عشان يوصل لعدد الأسطر
 *   المطلوب حتى لو نقطة الخطأ كانت بأول أو آخر المجال المختار.
 */
class ExcerptRangeBuilder
{
    private const TARGET_LINES = 15;

    public function build(int $hotWordId, int $teacherFromPage, int $teacherToPage, Collection $allLines): SuggestedExcerptRange
    {
        $centerIndex = $allLines->search(
            fn ($line) => $line->first_word_id <= $hotWordId && $line->last_word_id >= $hotWordId
        );

        if ($centerIndex === false) {
            throw new \RuntimeException("لم يتم إيجاد السطر الذي يحتوي على الكلمة رقم {$hotWordId} بجدول quran_pages.");
        }

        $minPage = $teacherFromPage - 1;
        $maxPage = $teacherToPage + 1;
        $lastIndex = $allLines->count() - 1;

        $startIndex = $centerIndex;
        $endIndex = $centerIndex;
        $lineCount = 1;
        $expandBackNext = true; // نبدأ بالتوسيع للخلف أولاً، وبعدين نتناوب

        while ($lineCount < self::TARGET_LINES) {
            $canExpandBack = $startIndex > 0 && $allLines[$startIndex - 1]->page_number >= $minPage;
            $canExpandForward = $endIndex < $lastIndex && $allLines[$endIndex + 1]->page_number <= $maxPage;

            if (! $canExpandBack && ! $canExpandForward) {
                break; // ما في متسع أكتر بأي اتجاه
            }

            if ($expandBackNext && $canExpandBack) {
                $startIndex--;
                $lineCount++;
            } elseif (! $expandBackNext && $canExpandForward) {
                $endIndex++;
                $lineCount++;
            } elseif ($canExpandBack) {
                $startIndex--;
                $lineCount++;
            } elseif ($canExpandForward) {
                $endIndex++;
                $lineCount++;
            }

            $expandBackNext = ! $expandBackNext;
        }

        $startLine = $allLines[$startIndex];
        $endLine = $allLines[$endIndex];

        return new SuggestedExcerptRange(
            fromWordId: (int) $startLine->first_word_id,
            toWordId: (int) $endLine->last_word_id,
            fromPage: (int) $startLine->page_number,
            toPage: (int) $endLine->page_number,
            fromLine: (int) $startLine->line_number,
            toLine: (int) $endLine->line_number,
            lineCount: $lineCount,
        );
    }
}
