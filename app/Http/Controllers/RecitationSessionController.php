<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
// use App\Http\Requests\ShowRecitationErrorsRequest;
// use App\Http\Requests\StoreRecitationSessionRequest;
// use App\Http\Requests\StoreRecitationErrorsRequest;
// use App\Http\Requests\UpdateRecitationStatusRequest;
// use App\Models\RecitationSession;
// use App\Models\RecitationError;
// use App\Models\Student;
// use App\Services\QuranPageService;
// use GuzzleHttp\Psr7\Request;
// use Illuminate\Http\JsonResponse;
// use App\Models\WordColor;
// use Illuminate\Support\Facades\DB;
// use App\Models\Mawdi;

// class RecitationSessionController extends Controller
// {
//     public function __construct(
//         protected QuranPageService $quranPageService
//     ) {}

//     // POST /recitation-sessions
//     public function store(StoreRecitationSessionRequest $request): JsonResponse
//     {
//         $user = $request->user();
//         $student = $user->student;
//         $session = RecitationSession::create([
//             ...$request->validated(),
//             'student_id' => $student->id,
//             'teacher_id' => $student->teacher_id,
//             'status' => 'upcoming',
//         ]);

//         return response()->json($session, 201);
//     }

//     // GET /students/{student}/next-session
//     public function nextSession(Student $student): JsonResponse
//     {
//         $session = $student->recitationSessions()
//             ->where('status', 'upcoming')
//             ->orderBy('scheduled_date')
//             ->first();

//         if (!$session) {
//             return response()->json(['message' => 'لا يوجد تسميع قادم'], 404);
//         }

//         return response()->json([
//             'session' => $session,
//             'pages' => $this->quranPageService->getPages($session->from_page, $session->to_page),
//         ]);
//     }

//     // POST /recitation-sessions/{session}/errors
//     // public function storeErrors(StoreRecitationErrorsRequest $request, RecitationSession $session): JsonResponse
//     // {
//     //     $session->errors()->delete();

//     //     $rows = collect($request->validated('errors'))->map(fn ($e) => [
//     //         'session_id' => $session->id,
//     //         'student_id' => $session->student_id,
//     //         'word_id' => $e['word_id'],
//     //         'surah_number' => $e['surah_number'],
//     //         'ayah_number' => $e['ayah_number'],
//     //         'error_type' => $e['error_type'],
//     //         'created_at' => now(),
//     //         'updated_at' => now(),
//     //     ])->toArray();

//     //     RecitationError::insert($rows);

//     //     return response()->json(['message' => 'تم حفظ الأخطاء', 'count' => count($rows)]);
//     // }

//     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// public function storeErrors(StoreRecitationErrorsRequest $request, RecitationSession $session): JsonResponse
// {
//     $session->errors()->delete();

//     $errorsInput = collect($request->validated('errors'));

//     // بس الكلمات الحمرا هي يلي بتدخل بالبحث عن الموضوع
//     $redWordIds = $errorsInput->where('error_type', 'red')->pluck('word_id');

//     $mawdiByWordId = WordColor::whereIn('word_id', $redWordIds)
//         ->where('is_red', true)
//         ->pluck('mawdi_id', 'word_id');

//     $rows = $errorsInput->map(fn ($e) => [
//         'session_id' => $session->id,
//         'student_id' => $session->student_id,
//         'word_id' => $e['word_id'],
//         'surah_number' => $e['surah_number'],
//         'ayah_number' => $e['ayah_number'],
//         'error_type' => $e['error_type'],
//         'mawdi_id' => $e['error_type'] === 'red' ? $mawdiByWordId->get($e['word_id']) : null,
//         'created_at' => now(),
//         'updated_at' => now(),
//     ])->toArray();

//     RecitationError::insert($rows);

//     return response()->json(['message' => 'تم حفظ الأخطاء', 'count' => count($rows)]);
// }
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//     // PATCH /recitation-sessions/{session}/status
//     public function updateStatus(UpdateRecitationStatusRequest $request, RecitationSession $session): JsonResponse
//     {
//         $session->update([
//             'status' => $request->validated('status'),
//             'reviewed_at' => now(),
//         ]);

//         return response()->json($session);
//     }

//     // GET /students/{student}/recitation-history
//     public function history(Student $student): JsonResponse
//     {
//         return response()->json(
//             $student->recitationSessions()
//                 ->orderByDesc('scheduled_date')
//                 ->get(['id', 'from_page', 'to_page', 'status', 'scheduled_date', 'reviewed_at'])
//         );
//     }

//     /////////////////////////////////////////////////////////////////////////////////////////////////
//     // GET /recitation-sessions/{session}
// // public function show(ShowRecitationErrorsRequest $request): JsonResponse
// // {
// //     $recitationSessionId = $request->validated('recitation_session_id');
// //     $session = RecitationSession::find($recitationSessionId);
// //     $errorsByWordId = $session->errors()
// //         ->pluck('error_type', 'word_id');

// //     return response()->json([
// //         'session' => $session,
// //         'pages' => $this->quranPageService->getPagesWithErrors(
// //             $session->from_page,
// //             $session->to_page,
// //             $errorsByWordId
// //         ),
// //     ]);
// // }
// public function show(ShowRecitationErrorsRequest $request): JsonResponse
// {
//     $recitationSessionId = $request->validated('recitation_session_id');
//     $session = RecitationSession::find($recitationSessionId);

//     $errorsByWordId = $session->errors()
//         ->get(['word_id', 'error_type', 'mawdi_id'])
//         ->keyBy('word_id');

//     return response()->json([
//         'session' => $session,
//         'pages' => $this->quranPageService->getPagesWithErrors(
//             $session->from_page,
//             $session->to_page,
//             $errorsByWordId
//         ),
//         'mawadi_by_page' => $this->buildMawadiByPage($session->from_page, $session->to_page, $errorsByWordId),
//     ]);
// }

// private function buildMawadiByPage(int $fromPage, int $toPage, \Illuminate\Support\Collection $errorsByWordId): array
// {
//     $mawdiErrors = $errorsByWordId->filter(fn ($e) => $e->mawdi_id !== null);
//     if ($mawdiErrors->isEmpty()) {
//         return [];
//     }

//     $lines = DB::table('quran_pages')
//         ->whereBetween('page_number', [$fromPage, $toPage])
//         ->whereNotNull('first_word_id')
//         ->get(['page_number', 'first_word_id', 'last_word_id']);

//     $wordTextById = DB::table('quran_words')
//         ->whereIn('id', $mawdiErrors->keys())
//         ->pluck('text', 'id');

//     $grouped = []; // [page_number][mawdi_id] => [...]

//     foreach ($mawdiErrors as $wordId => $error) {
//         $pageNumber = optional($lines->first(fn ($l) => $wordId >= $l->first_word_id && $wordId <= $l->last_word_id))->page_number;
//         if (!$pageNumber) continue;

//         $mawdi = Mawdi::find($error->mawdi_id);
//         if (!$mawdi) continue;

//         $grouped[$pageNumber][$mawdi->mawdi_id] ??= [
//             'mawdi_id' => $mawdi->mawdi_id,
//             'mawdi_number' => $mawdi->mawdi_number,
//             'html' => $mawdi->html_text,
//             'matched_words' => [],
//         ];
//         $grouped[$pageNumber][$mawdi->mawdi_id]['matched_words'][] = $wordTextById->get($wordId);
//     }

//     return collect($grouped)->map(fn ($mawadiForPage) =>
//         collect($mawadiForPage)->map(function ($m) {
//             $m['matched_words'] = array_values(array_unique($m['matched_words']));
//             return $m;
//         })->values()
//     )->toArray();
// }

// ////////////////////////////////
// // GET /recitation-sessions/{session}/mawdi-review
// public function mawdiReview(RecitationSession $session): JsonResponse
// {
//     $errors = $session->errors()
//         ->whereNotNull('mawdi_id')
//         ->get(['word_id', 'mawdi_id']);

//     if ($errors->isEmpty()) {
//         return response()->json(['success' => true, 'count' => 0, 'results' => []]);
//     }

//     $wordTextById = DB::table('quran_words')
//         ->whereIn('id', $errors->pluck('word_id'))
//         ->pluck('text', 'id');

//     $results = $errors->groupBy('mawdi_id')->map(function ($group, $mawdiId) use ($wordTextById) {
//         $mawdi = Mawdi::find($mawdiId);
//         if (!$mawdi) return null;

//         return [
//             'mawdi_id' => $mawdi->mawdi_id,
//             'mawdi_number' => $mawdi->mawdi_number,
//             'html' => $mawdi->html_text,
//             'matched_words' => $group->pluck('word_id')
//                 ->map(fn ($id) => $wordTextById->get($id))
//                 ->filter()
//                 ->unique()
//                 ->values(),
//         ];
//     })->filter()->values();

//     return response()->json([
//         'success' => true,
//         'count' => $results->count(),
//         'results' => $results,
//     ]);
// }
// }



use App\Http\Controllers\Controller;
use App\Http\Requests\ShowRecitationErrorsRequest;
use App\Http\Requests\StoreRecitationSessionRequest;
use App\Http\Requests\StoreRecitationErrorsRequest;
use App\Http\Requests\UpdateRecitationStatusRequest;
use App\Models\Mawdi;
use App\Models\RecitationSession;
use App\Models\RecitationError;
use App\Models\Student;
use App\Models\WordColor;
use App\Services\QuranPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RecitationSessionController extends Controller
{
    public function __construct(
        protected QuranPageService $quranPageService
    ) {}

    // POST /recitation-sessions
    public function store(StoreRecitationSessionRequest $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;
        $session = RecitationSession::create([
            ...$request->validated(),
            'student_id' => $student->id,
            'teacher_id' => $student->teacher_id,
            'status' => 'upcoming',
        ]);

        return response()->json($session, 201);
    }

    // GET /students/{student}/next-session
    public function nextSession(Student $student): JsonResponse
    {
        $session = $student->recitationSessions()
            ->where('status', 'upcoming')
            ->orderBy('scheduled_date')
            ->first();

        if (!$session) {
            return response()->json(['message' => 'لا يوجد تسميع قادم'], 404);
        }

        return response()->json([
            'session' => $session,
            'pages' => $this->quranPageService->getPages($session->from_page, $session->to_page),
        ]);
    }

    // POST /recitation-sessions/{session}/errors
    public function storeErrors(StoreRecitationErrorsRequest $request, RecitationSession $session): JsonResponse
    {
        $session->errors()->delete();

        $errorsInput = collect($request->validated('errors'));

        // بس الكلمات الحمرا هي يلي بتدخل بالبحث عن موضع "التبيان
        // المفصل" — باقي الألوان (أخضر/أزرق/أصفر) تتحفظ عادي بدون
        // أي بحث إضافي.
        $redWordIds = $errorsInput->where('error_type', 'red')->pluck('word_id');

        $mawdiIdByWordId = WordColor::whereIn('word_id', $redWordIds)
            ->where('is_red', true)
            ->pluck('mawdi_id', 'word_id');

        $rows = $errorsInput->map(fn ($e) => [
            'session_id' => $session->id,
            'student_id' => $session->student_id,
            'word_id' => $e['word_id'],
            'surah_number' => $e['surah_number'],
            'ayah_number' => $e['ayah_number'],
            'error_type' => $e['error_type'],
            'mawdi_id' => $e['error_type'] === 'red' ? $mawdiIdByWordId->get($e['word_id']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        RecitationError::insert($rows);

        // مواضع "التبيان" المرتبطة بأخطاء هالجلسة بالذات، جاهزة تُعرض
        // فوراً بنفس رد الحفظ (بلا ما تحتاج فتح شاشة تانية بعدين).
        $mawadi = Mawdi::whereIn('mawdi_id', $mawdiIdByWordId->filter()->unique()->values())
            ->get()
            ->map(fn ($m) => [
                'mawdi_id' => $m->mawdi_id,
                'mawdi_number' => $m->mawdi_number,
                'html' => $m->html_text,
            ])
            ->values();

        return response()->json([
            'message' => 'تم حفظ الأخطاء',
            'count' => count($rows),
            'mawadi' => $mawadi,
        ]);
    }

    // PATCH /recitation-sessions/{session}/status
    public function updateStatus(UpdateRecitationStatusRequest $request, RecitationSession $session): JsonResponse
    {
        $session->update([
            'status' => $request->validated('status'),
            'reviewed_at' => now(),
        ]);

        return response()->json($session);
    }

    // GET /students/{student}/recitation-history
    public function history(Student $student): JsonResponse
    {
        return response()->json(
            $student->recitationSessions()
                ->orderByDesc('scheduled_date')
                ->get(['id', 'from_page', 'to_page', 'status', 'scheduled_date', 'reviewed_at'])
        );
    }

    // POST /recitation-sessions/show
    public function show(ShowRecitationErrorsRequest $request): JsonResponse
    {
        $recitationSessionId = $request->validated('recitation_session_id');
        $session = RecitationSession::find($recitationSessionId);

        $errorsByWordId = $session->errors()->pluck('error_type', 'word_id');
        $mawdiIdByWordId = $session->errors()->whereNotNull('mawdi_id')->pluck('mawdi_id', 'word_id');

        return response()->json([
            'session' => $session,
            'pages' => $this->quranPageService->getPagesWithErrors(
                $session->from_page,
                $session->to_page,
                $errorsByWordId
            ),
            'mawadi_by_page' => $this->buildMawadiByPage($session, $mawdiIdByWordId),
        ]);
    }

    /**
     * يبني Map<رقم الصفحة, [مواضع التبيان]> اعتماداً على أرقام
     * الصفحات الفعلية لكل كلمة (عبر QuranPageService) + بيانات
     * المواضع نفسها من جدول mawadi3.
     */
    private function buildMawadiByPage(RecitationSession $session, $mawdiIdByWordId): array
    {
        if ($mawdiIdByWordId->isEmpty()) {
            return [];
        }

        $pageNumberByWordId = $this->quranPageService->pageNumberForWords(
            $session->from_page,
            $session->to_page,
            $mawdiIdByWordId->keys()
        );

        $mawadiById = Mawdi::whereIn('mawdi_id', $mawdiIdByWordId->unique()->values())
            ->get()
            ->keyBy('mawdi_id');

        $wordTextById = DB::table('quran_words')
            ->whereIn('id', $mawdiIdByWordId->keys())
            ->pluck('text', 'id');

        $grouped = []; // [page_number][mawdi_id] => [...]

        foreach ($mawdiIdByWordId as $wordId => $mawdiId) {
            $pageNumber = $pageNumberByWordId->get($wordId);
            $mawdi = $mawadiById->get($mawdiId);
            if (!$pageNumber || !$mawdi) {
                continue;
            }

            $grouped[$pageNumber][$mawdiId] ??= [
                'mawdi_id' => $mawdi->mawdi_id,
                'mawdi_number' => $mawdi->mawdi_number,
                'html' => $mawdi->html_text,
                'matched_words' => [],
            ];
            $grouped[$pageNumber][$mawdiId]['matched_words'][] = $wordTextById->get($wordId);
        }

        return collect($grouped)->map(
            fn ($mawadiForPage) => collect($mawadiForPage)->map(function ($m) {
                $m['matched_words'] = array_values(array_unique(array_filter($m['matched_words'])));
                return $m;
            })->values()
        )->toArray();
    }
}
