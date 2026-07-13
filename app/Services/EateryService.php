<?php

namespace App\Services;

use App\Domain\Eatery\EateryData;
use App\Domain\Eatery\Actions\CreateEateryAction;
use App\Domain\Eatery\Actions\UpdateEateryAction;
use App\Helpers\R2Helper;
use App\Models\Eatery;
use App\Services\EateryApiService;

class EateryService
{
    public function __construct(
        protected CreateEateryAction $createAction,
        protected UpdateEateryAction $updateAction
    ) {}

    public function create(EateryData $data, string $categorySlug): Eatery
    {
        $imagePath = $this->resolveImagePath($data->image, $data->image_url);
        return $this->createAction->execute($data, $categorySlug, $imagePath);
    }

    public function update($id, EateryData $data, string $categorySlug, ?string $currentImagePath): Eatery
    {
        $imagePath = $currentImagePath;
        
        if ($data->image) {
            if ($currentImagePath && \Str::startsWith($currentImagePath, '/uploads/eateries/')) {
                $oldPath = public_path($currentImagePath);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $imagePath = R2Helper::upload($data->image, 'eateries');
        } elseif ($data->image_url) {
            $imagePath = $this->parseImageUrl($data->image_url);
        }

        return $this->updateAction->execute($id, $data, $categorySlug, $imagePath);
    }

    public function delete(string $categorySlug, $id): bool
    {
        return EateryApiService::deleteEatery($categorySlug, $id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'eateries');
        }

        if ($imageUrl) {
            return $this->parseImageUrl($imageUrl);
        }

        return null;
    }

    protected function parseImageUrl(string $url): string
    {
        if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }
        return $url;
    }
}
