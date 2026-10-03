<?php

namespace App\Services;

use App\Domain\CulturalActivity\CulturalActivityData;
use App\Helpers\R2Helper;
use App\Models\CulturalActivity;
use App\Models\Eatery;
use Illuminate\Support\Facades\Log;

class CulturalActivityService
{
    /**
     * Lấy toàn bộ hoạt động văn hóa và di sản kèm thông tin di tích.
     */
    public function getAllCulturalActivities()
    {
        $activities = collect();
        try {
            $connActivities = CulturalActivity::select('id', 'eatery_id', 'name', 'type', 'price', 'unit', 'discount_note', 'description', 'image_path')
                ->with(['eatery:id,name,slug,category_id,address,phone,latitude,longitude,rating', 'eatery.category:id,name,slug,icon'])
                ->get();

            foreach ($connActivities as $activity) {
                if ($activity->eatery) {
                    $activity->eatery->category_slug = $activity->eatery->category->slug ?? 'hanh-trinh-di-san';
                    $activities->push($activity);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Lỗi khi lấy danh sách cultural activities: " . $e->getMessage());
        }

        return $activities->unique('id')->values();
    }

    public function create(CulturalActivityData|array $data): ?CulturalActivity
    {
        if ($data instanceof CulturalActivityData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url);
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'type' => $data->type,
                'price' => $data->price,
                'unit' => $data->unit,
                'discount_note' => $data->discount_note,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        return $this->storeCulturalActivity($attributes);
    }

    public function update($id, CulturalActivityData|array $data): ?CulturalActivity
    {
        $activity = CulturalActivity::find($id);
        if (!$activity) return null;

        if ($data instanceof CulturalActivityData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url) ?? $activity->image_path;
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'type' => $data->type,
                'price' => $data->price,
                'unit' => $data->unit,
                'discount_note' => $data->discount_note,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        $activity->update($attributes);
        return $activity;
    }

    public function storeCulturalActivity(array $data): ?CulturalActivity
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return CulturalActivity::create($data);
    }

    public function updateCulturalActivity($id, array $data): ?CulturalActivity
    {
        $activity = CulturalActivity::find($id);
        if (!$activity) return null;

        $activity->update($data);
        return $activity;
    }

    public function delete($id): bool
    {
        $activity = CulturalActivity::find($id);
        if (!$activity) return false;

        return (bool) $activity->delete();
    }

    public function deleteCulturalActivity($id): bool
    {
        return $this->delete($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'culture');
        }

        if ($imageUrl) {
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $imageUrl, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            return $imageUrl;
        }

        return null;
    }
}
