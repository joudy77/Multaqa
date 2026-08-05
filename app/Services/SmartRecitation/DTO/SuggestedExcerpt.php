<?php

namespace App\Services\SmartRecitation\DTO;

use App\Enums\ErrorCategory;
use Illuminate\Support\Collection;

/**
 * سؤال سبر مقترح جاهز للإرسال بصيغة JSON للفرونت - يشمل النص الفعلي.
 *
 * dominantCategory قابلة تكون null: يعني هاد السؤال "موضع عشوائي"
 * (ما في أخطاء مسجّلة كافية عليه) بدل ما يكون مبني على أخطاء فعلية.
 */
final class SuggestedExcerpt
{
    /**
     * @param  array<string,int>  $categoryBreakdown
     * @param  Collection  $lines  الأسطر والكلمات الفعلية (من ExcerptTextRenderer)
     */
    public function __construct(
        public readonly int $fromWordId,
        public readonly int $toWordId,
        public readonly int $fromPage,
        public readonly int $toPage,
        public readonly int $fromLine,
        public readonly int $toLine,
        public readonly float $score,
        public readonly ?ErrorCategory $dominantCategory,
        public readonly array $categoryBreakdown,
        public readonly Collection $lines,
        public readonly bool $isRandom = false,
    ) {
    }

    public function toArray(): array
    {
        return [
            'from_word_id' => $this->fromWordId,
            'to_word_id' => $this->toWordId,
            'from_page' => $this->fromPage,
            'to_page' => $this->toPage,
            'from_line' => $this->fromLine,
            'to_line' => $this->toLine,
            'score' => round($this->score, 3),
            'dominant_category' => $this->dominantCategory?->value,
            'dominant_category_label' => $this->dominantCategory?->label() ?? 'موضع عشوائي',
            'category_breakdown' => $this->categoryBreakdown,
            'is_random' => $this->isRandom,
            'lines' => $this->lines->values(), // النص الفعلي: كل سطر بكلماته الحقيقية
        ];
    }
}
