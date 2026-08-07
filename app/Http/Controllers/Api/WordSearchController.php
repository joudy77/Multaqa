<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuranWord;
use App\Models\WordColor;
use App\Models\Mawdi;
use Illuminate\Http\Request;

class WordSearchController extends Controller
{
    // خريطة أسماء السور لأرقامها - نفس الخريطة يلي عندك بـ Python
    private array $surahMap = [
        "الفاتحة" => 1, "البقرة" => 2, "آل عمران" => 3, "النساء" => 4, "المائدة" => 5,
        "الأنعام" => 6, "الأعراف" => 7, "الأنفال" => 8, "التوبة" => 9, "يونس" => 10,
        // ... كمّلي باقي 114 سورة نفس القاموس يلي عندك بايثون
    ];

    public function search(Request $request)
    {
        $words = $request->input('words', []);
        // شكل متوقع: [{"surah": "البقرة", "ayah": 60, "position": 3}, ...]

        $matchedMawdiIds = [];   // نجمع mawdi_id الفريدة يلي طلعت حمراء
        $matchedWordsPerMawdi = []; // نتتبع أي كلمات طلبها المستخدم أدت لكل موضع

        foreach ($words as $w) {
            $surahName = trim($w['surah'] ?? '');
            $ayah = $w['ayah'] ?? null;
            $position = $w['position'] ?? null;

            if (!$surahName || !$ayah) continue;

            $surahNum = $this->surahMap[$surahName] ?? null;
            if (!$surahNum) continue;

            $query = QuranWord::where('surah_number', $surahNum)
                               ->where('ayah_number', $ayah);
            if ($position) {
                $query->where('word_position', $position);
            }
            $wordRows = $query->get();

            foreach ($wordRows as $wordRow) {
                $colorRow = WordColor::where('word_id', $wordRow->id)->first();

                if (!$colorRow || !$colorRow->is_red) {
                    continue; // مو أحمر، أو مش مربوطة أصلاً - تجاهليها تمامًا
                }

                $mawdiId = $colorRow->mawdi_id;
                if (!in_array($mawdiId, $matchedMawdiIds)) {
                    $matchedMawdiIds[] = $mawdiId;
                }
                $matchedWordsPerMawdi[$mawdiId][] = $wordRow->word_text;
            }
        }

        // نرجع كل موضع فريد مرة وحدة بس، مع الكلمات يلي طابقته
        $results = [];
        foreach ($matchedMawdiIds as $mawdiId) {
            $mawdi = Mawdi::find($mawdiId);
            if (!$mawdi) continue;

            $results[] = [
                'mawdi_id' => $mawdi->mawdi_id,
                'mawdi_number' => $mawdi->mawdi_number,
                'html' => $mawdi->html_text,
                'matched_words' => array_unique($matchedWordsPerMawdi[$mawdiId]),
            ];
        }

        return response()->json([
            'success' => true,
            'count' => count($results),
            'results' => $results,
        ]);
    }
}
