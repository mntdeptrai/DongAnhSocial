<?php

namespace App\Contracts;

use App\Domain\OcopProduct\OcopProductData;
use App\Models\OcopProduct;
use Illuminate\Support\Collection;

interface OcopProductServiceInterface
{
    /**
     * Get filtered list of OCOP products.
     */
    public function getOcopProducts(array $filters = []): Collection;

    /**
     * Resolve product by slug or numeric ID (handles OcopProduct or Dish fallback).
     */
    public function resolveProduct(string|int $slugOrId): ?object;

    /**
     * Get related products in the same commune / area.
     */
    public function getRelatedProducts(object $product, int $limit = 6): Collection;

    /**
     * Get other products from the same eatery / establishment.
     */
    public function getEstablishmentProducts(object $product, int $limit = 6): Collection;

    /**
     * Create a new OCOP product record.
     */
    public function create(OcopProductData|array $data): ?OcopProduct;

    /**
     * Update an existing OCOP product record.
     */
    public function update($id, OcopProductData|array $data): ?OcopProduct;

    /**
     * Delete an OCOP product record.
     */
    public function delete($id): bool;
}
