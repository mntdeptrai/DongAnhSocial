<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface SearchServiceInterface
{
    /**
     * Get autocomplete search suggestions.
     *
     * @param string $keyword
     * @param int $limit
     * @return Collection
     */
    public function getSuggestions(string $keyword, int $limit = 6): Collection;

    /**
     * Perform quick categorized search.
     *
     * @param string $query
     * @param string $category
     * @param int $limit
     * @return Collection
     */
    public function quickSearch(string $query, string $category = 'all', int $limit = 8): Collection;
}
