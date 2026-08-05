<?php

namespace App\Services\SmartRecitation\DTO;

/** حدود المقطع (بالكلمات/الصفحات/الأسطر) قبل إضافة معلومات الـ score */
final class SuggestedExcerptRange
{
    public function __construct(
        public readonly int $fromWordId,
        public readonly int $toWordId,
        public readonly int $fromPage,
        public readonly int $toPage,
        public readonly int $fromLine,
        public readonly int $toLine,
        public readonly int $lineCount,
    ) {
    }
}
