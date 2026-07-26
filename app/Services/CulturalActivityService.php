<?php

namespace App\Services;

use App\Domain\CulturalActivity\CulturalActivityData;
use App\Domain\CulturalActivity\Actions\CreateCulturalActivityAction;
use App\Domain\CulturalActivity\Actions\UpdateCulturalActivityAction;
use App\Helpers\R2Helper;
use App\Models\CulturalActivity;
use App\Services\EateryApiService;

class CulturalActivityService
{
    public function __construct(
        protected CreateCulturalActivityAction $createAction,
        protected UpdateCulturalActivityAction $updateAction
    ) {}

    public function create(CulturalActivityData $data, ?string $connName = null): CulturalActivity
    {
        $imagePath = $this->resolveImagePath($data->image, $data->image_url);
        
        $action = $this->createAction;
        if ($connName) {
            \App\Models\CulturalActivity::setConnectionResolver(app('db'));
        }
        
        $activity = $action->execute($data, $imagePath);
        if ($connName) {
            $activity->setConnection($connName);
            $activity->save();
        }
        return $activity;
    }

    public function update($id, CulturalActivityData $data, ?string $connName = null): CulturalActivity
    {
        $connections = ['mysql'];
        $activity = null;
        $activeConn = $connName;

        if ($connName) {
            $activity = CulturalActivity::on($connName)->find($id);
        } else {
            foreach ($connections as $conn) {
                $act = CulturalActivity::on($conn)->find($id);
                if ($act) {
                    $activity = $act;
                    $activeConn = $conn;
                    break;
                }
            }
        }

        if (!$activity) {
            throw new \Exception('Hoạt động văn hóa không tồn tại!');
        }

        $imagePath = $activity->image_path;
        if ($data->image) {
            $imagePath = R2Helper::upload($data->image, 'cultural_activities');
        } elseif ($data->image_url) {
            $imagePath = $this->resolveImagePath(null, $data->image_url);
        }

        return $this->updateAction->execute($activity, $data, $imagePath);
    }

    public function delete($id): bool
    {
        return EateryApiService::deleteCulturalActivity($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'cultural_activities');
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
