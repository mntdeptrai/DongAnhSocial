<?php

namespace App\Services;

use App\Domain\Wellness\WellnessServiceData;
use App\Domain\Wellness\Actions\CreateWellnessServiceAction;
use App\Domain\Wellness\Actions\UpdateWellnessServiceAction;
use App\Helpers\R2Helper;
use App\Models\WellnessService;
use App\Services\EateryApiService;

class WellnessMapService
{
    public function __construct(
        protected CreateWellnessServiceAction $createAction,
        protected UpdateWellnessServiceAction $updateAction
    ) {}

    public function create(WellnessServiceData $data, ?string $connName = null): WellnessService
    {
        $imagePath = $this->resolveImagePath($data->image, $data->image_url);
        
        $action = $this->createAction;
        if ($connName) {
            \App\Models\WellnessService::setConnectionResolver(app('db'));
        }
        
        $service = $action->execute($data, $imagePath);
        if ($connName) {
            $service->setConnection($connName);
            $service->save();
        }
        return $service;
    }

    public function update($id, WellnessServiceData $data, ?string $connName = null): WellnessService
    {
        $connections = ['mysql'];
        $service = null;
        $activeConn = $connName;

        if ($connName) {
            $service = WellnessService::on($connName)->find($id);
        } else {
            foreach ($connections as $conn) {
                $ws = WellnessService::on($conn)->find($id);
                if ($ws) {
                    $service = $ws;
                    $activeConn = $conn;
                    break;
                }
            }
        }

        if (!$service) {
            throw new \Exception('Dịch vụ sức khỏe không tồn tại!');
        }

        $imagePath = $service->image_path;
        if ($data->image) {
            $imagePath = R2Helper::upload($data->image, 'wellness');
        } elseif ($data->image_url) {
            $imagePath = $this->resolveImagePath(null, $data->image_url);
        }

        return $this->updateAction->execute($service, $data, $imagePath);
    }

    public function delete($id): bool
    {
        return EateryApiService::deleteWellnessService($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'wellness');
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
