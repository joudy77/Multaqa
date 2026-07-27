<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class QuranPageService
{
    /**
     * يرجّع بيانات صفحات القرآن من from_page إلى to_page
     * مقسّمة حسب الصفحة، وكل صفحة فيها أسطرها، وكل سطر فيه كلماته.
     */
    public function getPages(int $fromPage, int $toPage): Collection
    {
        $lines = DB::table('quran_pages')
            ->whereBetween('page_number', [$fromPage, $toPage])
            ->orderBy('page_number')
            ->orderBy('line_number')
            ->get();

        // نجمع كل معرفات الكلمات المطلوبة بضربة وحدة (تفادي N+1 queries)
        $wordIds = $lines
            ->filter(fn ($line) => $line->first_word_id && $line->last_word_id)
            ->flatMap(fn ($line) => range($line->first_word_id, $line->last_word_id));

        $words = DB::table('quran_words')
            ->whereIn('id', $wordIds)
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        return $lines
            ->groupBy('page_number')
            ->map(function (Collection $pageLines) use ($words) {
                return $pageLines->map(function ($line) use ($words) {
                    $lineWords = [];

                    if ($line->first_word_id && $line->last_word_id) {
                        $lineWords = collect(range($line->first_word_id, $line->last_word_id))
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
                    }

                    return [
                        'line_number' => $line->line_number,
                        'line_type' => $line->line_type,
                        'is_centered' => (bool) $line->is_centered,
                        'surah_number' => $line->surah_number,
                        'words' => $lineWords,
                    ];
                });
            });
    }
}