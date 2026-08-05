<?php

namespace App\Services\SmartRecitation;

use App\Services\SmartRecitation\DTO\ErrorPoint;
use App\Services\SmartRecitation\DTO\SuggestedExcerpt;
use Illuminate\Support\Collection;

/**
 * نقطة الدخول الوحيدة لميزة "السبر الذكي".
 *
 * خطوات الخوارزمية:
 * 1) تحديد مجال الكلمات المطابق للصفحات اللي حددتها الانسة (from_page..to_page).
 * 2) تجميع كل أخطاء الطالبة بهاد المجال لكل كلمة (WordErrorAggregator) مع score خام.
 * 3) تعديل الـ score بمعامل تبريد (CooldownPenaltyCalculator) حسب تاريخ
 *    الاقتراحات السابقة لهاي الطالبة (ExcerptHistoryRepository)، مع
 *    استبعاد صلب لأي كلمة اتقترحت خلال آخر 6 ساعات (لو بقيت بدائل).
 * 4) ترتيب النقاط تنازلياً حسب الـ score المعدّل، وعند التعادل: نوع
 *    الخطأ الأعلى أولوية (تشكيل > نسيان > حفظ > تجويد) يفوز.
 * 5) اختيار أعلى $count نقطة بشكل جشع (greedy)، مع تخطي أي نقطة تقع
 *    ضمن مقطع سبق اختياره بنفس الدفعة (تنويع داخل نفس الطلب).
 * 6) لكل نقطة مُختارة: توسيعها لمقطع ~15 سطر، جلب نصها الفعلي، وتسجيلها
 *    بتاريخ الاقتراحات حتى تُحتسب بالتبريد بالمرة الجاية.
 * 7) ⭐ لو عدد الأسئلة المبنية على أخطاء فعلية أقل من $count (أو حتى
 *    صفر - ما في أي خطأ مسجّل بهاد المجال إطلاقاً)، نكمّل الباقي
 *    بمواضع عشوائية (RandomExcerptPicker) ضمن نفس مجال الصفحات، حتى
 *    الانسة دايماً تاخذ عدد الأسئلة يلي طلبتها بالضبط (إذا مساحة
 *    الصفحات كافية أصلاً).
 */
class SmartRecitationSelector
{
    public function __construct(
        private readonly QuranLineLocator $locator,
        private readonly WordErrorAggregator $aggregator,
        private readonly ExcerptRangeBuilder $rangeBuilder,
        private readonly ExcerptTextRenderer $textRenderer,
        private readonly CooldownPenaltyCalculator $cooldown,
        private readonly ExcerptHistoryRepository $history,
        private readonly RandomExcerptPicker $randomPicker,
    ) {
    }

    /** @return Collection<int,SuggestedExcerpt> */
    public function suggest(int $studentId, int $fromPage, int $toPage, int $count): Collection
    {
        [$fromWordId, $toWordId] = $this->locator->wordRangeForPages($fromPage, $toPage);
        $allLines = $this->locator->allOrderedLines();
        $recentHistory = $this->history->recentFor($studentId);

        $selected = collect();
        $coveredRanges = collect(); // كل [from_word_id, to_word_id] تم اختيارها لغاية هلأ

        $errorPoints = $this->aggregator->aggregate($studentId, $fromWordId, $toWordId);

        if ($errorPoints->isNotEmpty()) {
            // استبعاد صلب: أي كلمة اتقترحت خلال آخر 6 ساعات تُشال كلياً
            // من المرشحين - إلا إذا هيك رح يفضّي القائمة بالكامل.
            $hardFiltered = $errorPoints->filter(
                fn (ErrorPoint $p) => ! $this->cooldown->hasHardBlock($p->wordId, $recentHistory)
            );
            $candidatePoints = $hardFiltered->isNotEmpty() ? $hardFiltered : $errorPoints;

            $adjustedScores = $this->applyCooldown($candidatePoints, $recentHistory);
            $ranked = $this->rankByScoreThenErrorPriority($candidatePoints, $adjustedScores);

            foreach ($ranked as $point) {
                if ($selected->count() >= $count) {
                    break;
                }

                if ($this->overlapsAnySelectedRange($point, $coveredRanges)) {
                    continue; // تنويع: هاد المكان مغطّى ضمن سؤال سابق بنفس الدفعة
                }

                $range = $this->rangeBuilder->build($point->wordId, $fromPage, $toPage, $allLines);
                $lines = $this->textRenderer->render($range->fromWordId, $range->toWordId);

                $selected->push(new SuggestedExcerpt(
                    fromWordId: $range->fromWordId,
                    toWordId: $range->toWordId,
                    fromPage: $range->fromPage,
                    toPage: $range->toPage,
                    fromLine: $range->fromLine,
                    toLine: $range->toLine,
                    score: $adjustedScores[$point->wordId],
                    dominantCategory: $point->dominantCategory(),
                    categoryBreakdown: $point->categoryCounts,
                    lines: $lines,
                    isRandom: false,
                ));

                $coveredRanges->push([$range->fromWordId, $range->toWordId]);
                $this->history->log($studentId, $range->fromWordId, $range->toWordId);
            }
        }

        // ⭐ تكملة عشوائية: لو ما وصلنا للعدد المطلوب (أو ما في أخطاء
        // إطلاقاً)، نملأ الباقي بمواضع عشوائية ضمن نفس مجال الصفحات.
        if ($selected->count() < $count) {
            $remaining = $count - $selected->count();
            $randomExcerpts = $this->randomPicker->pick(
                $fromWordId,
                $toWordId,
                $fromPage,
                $toPage,
                $remaining,
                $allLines,
                $coveredRanges,
            );

            foreach ($randomExcerpts as $excerpt) {
                $selected->push($excerpt);
                $this->history->log($studentId, $excerpt->fromWordId, $excerpt->toWordId);
            }
        }

        return $selected->values();
    }

    /** @param  Collection<int,ErrorPoint>  $points */
    private function rankByScoreThenErrorPriority(Collection $points, array $adjustedScores): Collection
    {
        return $points->sort(function (ErrorPoint $a, ErrorPoint $b) use ($adjustedScores) {
            $scoreCompare = $adjustedScores[$b->wordId] <=> $adjustedScores[$a->wordId];

            return $scoreCompare !== 0
                ? $scoreCompare
                : $a->dominantCategoryPriority() <=> $b->dominantCategoryPriority();
        })->values();
    }

    /**
     * @param  Collection<int,ErrorPoint>  $points
     * @return array<int,float> score معدّل لكل word_id
     */
    private function applyCooldown(Collection $points, Collection $recentHistory): array
    {
        return $points
            ->mapWithKeys(fn (ErrorPoint $p) => [
                $p->wordId => $p->score * $this->cooldown->factorFor($p->wordId, $recentHistory),
            ])
            ->all();
    }

    private function overlapsAnySelectedRange(ErrorPoint $point, Collection $coveredRanges): bool
    {
        return $coveredRanges->contains(
            fn (array $range) => $point->wordId >= $range[0] && $point->wordId <= $range[1]
        );
    }
}
