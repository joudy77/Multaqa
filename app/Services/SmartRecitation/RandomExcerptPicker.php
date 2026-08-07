<?php

namespace App\Services\SmartRecitation;

use App\Services\SmartRecitation\DTO\SuggestedExcerpt;
use Illuminate\Support\Collection;

/**
 * لما عدد الأخطاء المسجّلة مش كافي لتغطية عدد الأسئلة المطلوب (أو
 * حتى لو صفر أخطاء إطلاقاً)، هاي الخدمة بتكمّل الباقي بمواضع عشوائية
 * ضمن مجال الصفحات نفسه - بنفس آلية التوسيع لـ15 سطر (ExcerptRangeBuilder)
 * ونفس عرض النص الفعلي (ExcerptTextRenderer)، بس نقطة البداية عشوائية
 * بدل ما تكون مبنية على score.
 */
class RandomExcerptPicker
{
    private const MAX_ATTEMPTS_PER_EXCERPT = 30;

    public function __construct(
        private readonly ExcerptRangeBuilder $rangeBuilder,
        private readonly ExcerptTextRenderer $textRenderer,
    ) {
    }

    /**
     * @param  Collection  $coveredRanges  كل [from_word_id, to_word_id] المُختارة أصلاً (تفادي التكرار جوا نفس الدفعة)
     * @return Collection<int,SuggestedExcerpt>
     */
    public function pick(
        int $fromWordId,
        int $toWordId,
        int $teacherFromPage,
        int $teacherToPage,
        int $count,
        Collection $allLines,
        Collection $coveredRanges,
    ): Collection {
        $picked = collect();

        for ($i = 0; $i < $count; $i++) {
            $range = $this->pickOneNonOverlapping($fromWordId, $toWordId, $teacherFromPage, $teacherToPage, $allLines, $coveredRanges);

            if ($range === null) {
                break; // ما بقي مكان كافي للمزيد (مجال الصفحات صغير جداً)
            }

            $lines = $this->textRenderer->render($range->fromWordId, $range->toWordId);

            $picked->push(new SuggestedExcerpt(
                fromWordId: $range->fromWordId,
                toWordId: $range->toWordId,
                fromPage: $range->fromPage,
                toPage: $range->toPage,
                fromLine: $range->fromLine,
                toLine: $range->toLine,
                score: 0,
                dominantCategory: null,
                categoryBreakdown: [],
                lines: $lines,
                isRandom: true,
            ));

            $coveredRanges->push([$range->fromWordId, $range->toWordId]);
        }

        return $picked;
    }

    private function pickOneNonOverlapping(
        int $fromWordId,
        int $toWordId,
        int $teacherFromPage,
        int $teacherToPage,
        Collection $allLines,
        Collection $coveredRanges,
    ): ?\App\Services\SmartRecitation\DTO\SuggestedExcerptRange {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS_PER_EXCERPT; $attempt++) {
            $randomWordId = random_int($fromWordId, $toWordId);

            $overlaps = $coveredRanges->contains(
                fn (array $r) => $randomWordId >= $r[0] && $randomWordId <= $r[1]
            );
            if ($overlaps) {
                continue;
            }

            try {
                $range = $this->rangeBuilder->build($randomWordId, $teacherFromPage, $teacherToPage, $allLines);
            } catch (\RuntimeException) {
                continue; // نادراً ما يصير (كلمة على حافة مجال غريبة) - نجرب رقم تاني
            }

            // نتأكد المقطع الناتج (بعد التوسيع) نفسه ما يتقاطع مع مقطع مُختار سابقاً
            $rangeOverlaps = $coveredRanges->contains(
                fn (array $r) => $range->fromWordId <= $r[1] && $range->toWordId >= $r[0]
            );
            if ($rangeOverlaps) {
                continue;
            }

            return $range;
        }

        return null;
    }
}
