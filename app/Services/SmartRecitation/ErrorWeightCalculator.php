<?php

namespace App\Services\SmartRecitation;

use App\Models\RecitationError;
use Carbon\Carbon;

/**
 * يحسب وزن خطأ واحد = وزن نوع الخطأ الدلالي (بعد تفسير اللون المخزّن
 * عبر ErrorColorMapper) × معامل الحداثة (الخطأ الحديث أهم من خطأ قديم
 * اتصلحت غالباً - decay أسّي بنصف عمر 14 يوم).
 */
class ErrorWeightCalculator
{
    private const RECENCY_HALF_LIFE_DAYS = 14.0;

    public function __construct(
        private readonly ErrorColorMapper $colorMapper,
    ) {
    }

    public function weightFor(RecitationError $error): float
    {
        $category = $this->colorMapper->categoryFor($error->error_type);
        $daysAgo = max(0, Carbon::parse($error->created_at)->diffInDays(now()));
        $recencyFactor = 0.5 ** ($daysAgo / self::RECENCY_HALF_LIFE_DAYS);

        return $category->weight() * $recencyFactor;
    }
}
