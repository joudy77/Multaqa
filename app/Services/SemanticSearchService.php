<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SemanticSearchService
{
    protected string $baseUrl = 'http://localhost:8001';

    public function search(string $query, int $topK = 5): array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/search", [
            'q' => $query,
            'top_k' => $topK,
        ]);

        if (!$response->successful()) {
            return [];
        }

        return $response->json('results', []);
    }
}