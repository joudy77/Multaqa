<?php

namespace App\Services\SmartRecitation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * يرجّع نص المقطع الفعلي (كل الأسطر والكلمات بين from_word_id و
 * to_word_id) - نفس شكل بيانات QuranPageService::getPages() تماماً
 * (سطر فيه كلماته الحقيقية بالنص)، بس مقصوص بالضبط على حدود مقطع
 * السؤال المقترح، مش صفحة كاملة.
 *
 * بيستخدم نفس جدولي quran_pages و quran_words الموجودين أصلاً -
 * ما بيضيف ولا جدول جديد.
 */
class ExcerptTextRenderer
{
    /** @return Collection كل سطر: page_number/line_number/surah_number/words[] */
    public function render(int $fromWordId, int $toWordId): Collection
    {
        $lines = DB::table('quran_pages')
            ->whereNotNull('first_word_id')
            ->where('last_word_id', '>=', $fromWordId)
            ->where('first_word_id', '<=', $toWordId)
            ->orderBy('page_number')
            ->orderBy('line_number')
            ->get();

        $wordIds = $lines->flatMap(
            fn ($line) => range(max($line->first_word_id, $fromWordId), min($line->last_word_id, $toWordId))
        );

        $words = DB::table('quran_words')
            ->whereIn('id', $wordIds)
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        return $lines->map(function ($line) use ($words, $fromWordId, $toWordId) {
            $start = max($line->first_word_id, $fromWordId);
            $end = min($line->last_word_id, $toWordId);

            $lineWords = collect(range($start, $end))
                ->map(fn ($id) => $words->get($id))
                ->filter()
                ->map(fn ($w) => [
                    'word_id' => $w->id,
                    'surah' => $w->surah,
                    'ayah' => $w->ayah,
                    'position' => $w->word,
                    'text' => $w->text,
                ])
                ->values();

            return [
                'page_number' => (int) $line->page_number,
                'line_number' => (int) $line->line_number,
                'line_type' => $line->line_type,
                'is_centered' => (bool) $line->is_centered,
                'surah_number' => $line->surah_number,
                'words' => $lineWords,
            ];
        })->values();
    }
}
