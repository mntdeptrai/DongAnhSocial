<?php

namespace App\Http\Controllers;

use App\Contracts\SearchServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected SearchServiceInterface $searchService
    ) {}

    public function search(Request $request): View|JsonResponse
    {
        $keyword = $request->query('q');

        // Autocomplete Suggestions when typing
        if ($request->query('ajax') === 'suggest' && $keyword) {
            $suggestions = $this->searchService->getSuggestions($keyword, 6);
            return response()->json($suggestions);
        }

        // Lightweight render: async fetch from API
        return view('search', [
            'keyword' => $keyword,
        ]);
    }

    public function quickSearch(Request $request): JsonResponse
    {
        $q   = trim((string) $request->query('q', ''));
        $cat = (string) $request->query('cat', 'all');

        $results = $this->searchService->quickSearch($q, $cat, 8);

        return response()->json($results);
    }
}
