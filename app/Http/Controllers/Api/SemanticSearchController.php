<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemanticSearchRequest;
use App\Services\SemanticSearchService;
use Illuminate\Http\JsonResponse;

class SemanticSearchController extends Controller
{
    public function __construct(
        protected SemanticSearchService $searchService
    ) {}

    public function search(SemanticSearchRequest $request): JsonResponse
    {
        $results = $this->searchService->search(
            $request->validated('q'),
            $request->validated('top_k') ?? 5
        );

        return response()->json([
            'query' => $request->validated('q'),
            'results' => $results,
        ]);
    }
}