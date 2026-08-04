<?php
namespace App\Services; 

// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Collection;

// class QuranPageService
// {
//     // التابع القديم يضل موجود متل ما هو (للتسميع القادم مثلاً، لما ما في أخطاء بعد)
//     public function getPages(int $fromPage, int $toPage): Collection
//     {
//         return $this->buildPages($fromPage, $toPage, collect());
//     }

//     /**
//      * نفس الصفحات، بس كل كلمة إلها خطأ بتترافق بـ error_type
//      * $errorsByWordId: Collection مفهرسة بـ word_id => error_type
//      */
//     public function getPagesWithErrors(int $fromPage, int $toPage, Collection $errorsByWordId): Collection
//     {
//         return $this->buildPages($fromPage, $toPage, $errorsByWordId);
//     }

//     private function buildPages(int $fromPage, int $toPage, Collection $errorsByWordId): Collection
//     {
//         $lines = DB::table('quran_pages')
//             ->whereBetween('page_number', [$fromPage, $toPage])
//             ->orderBy('page_number')
//             ->orderBy('line_number')
//             ->get();

//         $wordIds = $lines
//             ->filter(fn ($line) => $line->first_word_id && $line->last_word_id)
//             ->flatMap(fn ($line) => range($line->first_word_id, $line->last_word_id));

//         $words = DB::table('quran_words')
//             ->whereIn('id', $wordIds)
//             ->get()
//             ->keyBy('id');

//         return $lines
//             ->groupBy('page_number')
//             ->map(function (Collection $pageLines) use ($words, $errorsByWordId) {
//                 return $pageLines->map(function ($line) use ($words, $errorsByWordId) {
//                     $lineWords = [];

//                     if ($line->first_word_id && $line->last_word_id) {
//                         $lineWords = collect(range($line->first_word_id, $line->last_word_id))
//                             ->map(fn ($id) => $words->get($id))
//                             ->filter()
//                             ->map(fn ($w) => [
//                                 'word_id' => $w->id,
//                                 'surah' => $w->surah,
//                                 'ayah' => $w->ayah,
//                                 'position' => $w->word,
//                                 'text' => $w->text,
//                                 // 'error_type' => $errorsByWordId->get($w->id), // null لو ما في خطأ
//                                 'error_type' => $errorsByWordId->get($w->id)?->error_type,
//                                 'mawdi_id' => $errorsByWordId->get($w->id)?->mawdi_id,
//                             ])
//                             ->values();
//                     }

//                     return [
//                         'line_number' => $line->line_number,
//                         'line_type' => $line->line_type,
//                         'is_centered' => (bool) $line->is_centered,
//                         'surah_number' => $line->surah_number,
//                         'words' => $lineWords,
//                     ];
//                 });
//             });
//     }
// }


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class QuranPageService
{
    // التابع القديم يضل موجود متل ما هو (للتسميع القادم مثلاً، لما ما في أخطاء بعد)
    public function getPages(int $fromPage, int $toPage): Collection
    {
        return $this->buildPages($fromPage, $toPage, collect());
    }

    /**
     * نفس الصفحات، بس كل كلمة إلها خطأ بتترافق بـ error_type
     * $errorsByWordId: Collection مفهرسة بـ word_id => error_type
     */
    public function getPagesWithErrors(int $fromPage, int $toPage, Collection $errorsByWordId): Collection
    {
        return $this->buildPages($fromPage, $toPage, $errorsByWordId);
    }

    /**
     * يبني Map<word_id, page_number> ضمن نطاق from/to Page — يُستخدم
     * لمعرفة تحت أي صفحة تُعرض بطاقة موضع "التبيان" (mawadi_by_page)،
     * بلا حاجة نكرر بناء الصفحات كامل لأجل هالمعلومة بس.
     */
    public function pageNumberForWords(int $fromPage, int $toPage, Collection $wordIds): Collection
    {
        if ($wordIds->isEmpty()) {
            return collect();
        }

        $lines = DB::table('quran_pages')
            ->whereBetween('page_number', [$fromPage, $toPage])
            ->whereNotNull('first_word_id')
            ->whereNotNull('last_word_id')
            ->get(['page_number', 'first_word_id', 'last_word_id']);

        return $wordIds->mapWithKeys(function ($wordId) use ($lines) {
            $line = $lines->first(
                fn ($l) => $wordId >= $l->first_word_id && $wordId <= $l->last_word_id
            );
            return $line ? [$wordId => $line->page_number] : [];
        });
    }

    private function buildPages(int $fromPage, int $toPage, Collection $errorsByWordId): Collection
    {
        $lines = DB::table('quran_pages')
            ->whereBetween('page_number', [$fromPage, $toPage])
            ->orderBy('page_number')
            ->orderBy('line_number')
            ->get();

        $wordIds = $lines
            ->filter(fn ($line) => $line->first_word_id && $line->last_word_id)
            ->flatMap(fn ($line) => range($line->first_word_id, $line->last_word_id));

        $words = DB::table('quran_words')
            ->whereIn('id', $wordIds)
            ->get()
            ->keyBy('id');

        return $lines
            ->groupBy('page_number')
            ->map(function (Collection $pageLines) use ($words, $errorsByWordId) {
                return $pageLines->map(function ($line) use ($words, $errorsByWordId) {
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
                                'error_type' => $errorsByWordId->get($w->id), // null لو ما في خطأ
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