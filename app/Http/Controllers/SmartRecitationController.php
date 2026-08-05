<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSmartRecitationSessionRequest;
use App\Http\Requests\SuggestSmartRecitationRequest;
use App\Models\RecitationSession;
use App\Models\SmartRecitationSessionExcerpt;
use App\Models\Teacher;
use App\Services\SmartRecitation\SmartRecitationSelector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartRecitationController extends Controller
{
    public function __construct(
        private readonly SmartRecitationSelector $selector,
    ) {
    }

    /**
     * POST /teacher/smart-recitation/suggest
     * (لسا موجودة لأغراض اختبار/توافق - الفرونت الحالي ما عاد يستخدمها،
     * صارت POST /teacher/smart-recitation/sessions تعمل الاثنين معاً).
     */
    public function suggest(SuggestSmartRecitationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $suggestions = $this->selector->suggest(
            studentId: $data['student_id'],
            fromPage: $data['from_page'],
            toPage: $data['to_page'],
            count: $data['count'],
        );

        return response()->json([
            'count' => $suggestions->count(),
            'suggestions' => $suggestions->map(fn ($s) => $s->toArray())->values(),
        ]);
    }

    /**
     * POST /teacher/smart-recitation/sessions
     * body: { student_id, from_page, to_page, count }
     *
     * تنشئ recitation_session حقيقية (is_smart_review = true)، وبنفس
     * الوقت تحسب الأسئلة المقترحة وتخزّنها "مجمّدة" بجدول
     * smart_recitation_session_excerpts مربوطة بهاي الجلسة بالضبط -
     * حتى لو الأنسة سكرت وفتحت هاي الجلسة تاني (upcoming) بترجع تشوف
     * نفس الأسئلة، مش مجموعة جديدة.
     */
    public function createSession(CreateSmartRecitationSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $teacher = Teacher::where('user_id', $request->user()->id)->firstOrFail();

        $session = RecitationSession::create([
            'student_id' => $data['student_id'],
            'teacher_id' => $teacher->id,
            'from_page' => $data['from_page'],
            'to_page' => $data['to_page'],
            'status' => 'upcoming',
            'is_smart_review' => true,
        ]);

        $excerpts = $this->selector->suggest(
            studentId: $data['student_id'],
            fromPage: $data['from_page'],
            toPage: $data['to_page'],
            count: $data['count'],
        );

        $this->persistExcerpts($session->id, $excerpts);

        return response()->json([
            'session' => $session,
            'excerpts' => $excerpts->map(fn ($e) => $e->toArray())->values(),
        ], 201);
    }

    /**
     * GET /teacher/smart-recitation/students/{student}/upcoming
     *
     * لو في جلسة سبر ذكي "upcoming" (لسا ما تقرر مصيرها) لهاي الطالبة،
     * بترجعها مع أسئلتها المجمّدة - حتى الفرونت يستأنفها بدل ما يفتح
     * شاشة إعداد جديدة من الصفر. لو ما في، بترجع { session: null }.
     */
    public function upcomingForStudent(Request $request, int $studentId): JsonResponse
    {
        $session = RecitationSession::query()
            ->where('student_id', $studentId)
            ->where('is_smart_review', true)
            ->where('status', 'upcoming')
            ->latest('id')
            ->first();

        if ($session === null) {
            return response()->json(['session' => null, 'excerpts' => []]);
        }

        $excerpts = SmartRecitationSessionExcerpt::query()
            ->where('session_id', $session->id)
            ->orderBy('order_index')
            ->get();

        return response()->json([
            'session' => $session,
            'excerpts' => $excerpts->map(fn ($e) => $this->excerptRowToArray($e))->values(),
        ]);
    }

    private function persistExcerpts(int $sessionId, $excerpts): void
    {
        foreach ($excerpts->values() as $index => $excerpt) {
            SmartRecitationSessionExcerpt::create([
                'session_id' => $sessionId,
                'order_index' => $index,
                'from_word_id' => $excerpt->fromWordId,
                'to_word_id' => $excerpt->toWordId,
                'from_page' => $excerpt->fromPage,
                'to_page' => $excerpt->toPage,
                'from_line' => $excerpt->fromLine,
                'to_line' => $excerpt->toLine,
                'score' => $excerpt->score,
                'dominant_category' => $excerpt->dominantCategory?->value,
                'category_breakdown' => $excerpt->categoryBreakdown,
            ]);
        }
    }

    /**
     * الأسئلة المجمّدة مخزّنة بدون النص الفعلي (lines) - نص القرآن
     * ثابت أصلاً وموجود بجدولي quran_pages/quran_words، فلا داعي
     * تكراره بكل صف. نجيبه هون وقت القراءة فقط عبر ExcerptTextRenderer.
     */
    private function excerptRowToArray(SmartRecitationSessionExcerpt $row): array
    {
        $renderer = app(\App\Services\SmartRecitation\ExcerptTextRenderer::class);
        $lines = $renderer->render($row->from_word_id, $row->to_word_id);

        return [
            'from_word_id' => $row->from_word_id,
            'to_word_id' => $row->to_word_id,
            'from_page' => $row->from_page,
            'to_page' => $row->to_page,
            'from_line' => $row->from_line,
            'to_line' => $row->to_line,
            'score' => $row->score,
            'dominant_category' => $row->dominant_category,
            'dominant_category_label' => $row->dominant_category
                ? \App\Enums\ErrorCategory::from($row->dominant_category)->label()
                : 'موضع عشوائي',
            'category_breakdown' => $row->category_breakdown ?? [],
            'is_random' => $row->dominant_category === null,
            'lines' => $lines->values(),
        ];
    }
}
