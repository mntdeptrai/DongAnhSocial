<?php

namespace App\Contracts;

interface NongSanCatalogInterface
{
    /**
     * Get the list of curated agricultural produce & heritage specialties.
     *
     * @param string|null $category
     * @return array
     */
    public function getProducts(?string $category = 'all'): array;

    /**
     * Get the spotlight heritage product (e.g. Mâm cỗ Cổ Loa xưa).
     *
     * @return array
     */
    public function getSpotlightProduct(): array;

    /**
     * Get the curated gift combos & hampers.
     *
     * @return array
     */
    public function getCombos(): array;

    /**
     * Get the 4-season agricultural harvest calendar.
     *
     * @return array
     */
    public function getSeasonalCalendar(): array;

    /**
     * Get total count of official heritage products.
     *
     * @return int
     */
    public function getTotalCount(): int;
}
