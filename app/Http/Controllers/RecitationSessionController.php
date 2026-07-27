<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecitationSessionRequest;
use App\Http\Requests\StoreRecitationErrorsRequest;
use App\Http\Requests\UpdateRecitationStatusRequest;
use App\Models\RecitationSession;
use App\Models\RecitationError;
use App\Models\Student;
use App\Services\QuranPageService;
use Illuminate\Http\JsonResponse;

class RecitationSessionController extends Controller
{
    public function __construct(
        protected QuranPageService $quranPageService
    ) {}

    // POST /recitation-sessions
    public function store(StoreRecitationSessionRequest $request): JsonResponse
    {
        $session = RecitationSession::create([
            ...$request->validated(),
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

        $rows = collect($request->validated('errors'))->map(fn ($e) => [
            'session_id' => $session->id,
            'student_id' => $session->student_id,
            'word_id' => $e['word_id'],
            'surah_number' => $e['surah_number'],
            'ayah_number' => $e['ayah_number'],
            'error_type' => $e['error_type'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        RecitationError::insert($rows);

        return response()->json(['message' => 'تم حفظ الأخطاء', 'count' => count($rows)]);
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
}