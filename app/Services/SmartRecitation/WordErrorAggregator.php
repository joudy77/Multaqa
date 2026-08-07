<?php

namespace App\Services\SmartRecitation;

use App\Models\RecitationError;
use App\Services\SmartRecitation\DTO\ErrorPoint;
use Illuminate\Support\Collection;

/** يجمّع أخطاء طالبة ضمن مجال كلمات معيّن، ويحوّلها لـ ErrorPoint واحد لكل كلمة */
class WordErrorAggregator
{
    public function __construct(
        private readonly ErrorWeightCalculator $weightCalculator,
        private readonly ErrorColorMapper $colorMapper,
    ) {
    }

    /** @return Collection<int,ErrorPoint> مفهرسة بـ word_id */
    public function aggregate(int $studentId, int $fromWordId, int $toWordId): Collection
    {
        $errors = RecitationError::query()
            ->where('student_id', $studentId)
            ->whereBetween('word_id', [$fromWordId, $toWordId])
            ->get();

        return $errors
            ->groupBy('word_id')
            ->map(function (Collection $wordErrors) {
                $first = $wordErrors->first();
                $score = 0.0;
                $categoryCounts = [];

                foreach ($wordErrors as $error) {
                    $score += $this->weightCalculator->weightFor($error);

                    // نجمّع حسب نوع الخطأ الدلالي (بعد تفسير اللون المخزّن)
                    // مش حسب اللون الخام، حتى تشتغل dominantCategory() صح.
                    $category = $this->colorMapper->categoryFor($error->error_type);
                    $categoryCounts[$category->value] = ($categoryCounts[$category->value] ?? 0) + 1;
                }

                return new ErrorPoint(
                    wordId: (int) $first->word_id,
                    surahNumber: (int) $first->surah_number,
                    ayahNumber: (int) $first->ayah_number,
                    score: $score,
                    categoryCounts: $categoryCounts,
                );
            })
            ->values()
            ->keyBy(fn (ErrorPoint $p) => $p->wordId);
    }
}
