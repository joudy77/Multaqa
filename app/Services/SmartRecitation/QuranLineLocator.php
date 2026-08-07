<?php

namespace App\Services\SmartRecitation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * طبقة وصول لجدول quran_pages (نفس الجدول المستخدم أصلاً بـ QuranPageService)
 * لتحديد مجال الكلمات (word_id) لصفحات معيّنة، وأسطر الكتاب كاملة مرتبة،
 * تُستخدم لتوسيع مقطع السؤال قبل/بعد نقطة الخطأ الساخنة.
 */
class QuranLineLocator
{
    /** أول/آخر word_id ضمن مجال صفحات [fromPage..toPage] */
    public function wordRangeForPages(int $fromPage, int $toPage): array
    {
        $first = DB::table('quran_pages')
            ->where('page_number', $fromPage)
            ->whereNotNull('first_word_id')
            ->orderBy('line_number')
            ->value('first_word_id');

        $last = DB::table('quran_pages')
            ->where('page_number', $toPage)
            ->whereNotNull('last_word_id')
            ->orderByDesc('line_number')
            ->value('last_word_id');

        return [(int) $first, (int) $last];
    }

    /** كل أسطر الكتاب (اللي فيها كلمات فعلاً) مرتبة صفحة ثم سطر - تُستخدم للتوسيع */
    public function allOrderedLines(): Collection
    {
        return DB::table('quran_pages')
            ->whereNotNull('first_word_id')
            ->orderBy('page_number')
            ->orderBy('line_number')
            ->get();
    }
}
