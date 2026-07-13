<?php

namespace App\Services;

use App\Domain\Checkin\CheckinData;
use App\Domain\Checkin\Actions\CreateCheckinAction;
use App\Helpers\R2Helper;
use App\Models\Checkin;
use Illuminate\Support\Facades\DB;

class CheckinService
{
    public function __construct(
        protected CreateCheckinAction $createAction
    ) {}

    public function createCheckin(CheckinData $data): Checkin
    {
        return DB::transaction(function() use ($data) {
            $imagePath = null;
            if ($data->image) {
                $imagePath = R2Helper::upload($data->image, 'checkins');
            } elseif (!empty($data->image_base64)) {
                try {
                    if (preg_match('/^data:image\/(\w+);base64,/', $data->image_base64, $matches)) {
                        $extension = $matches[1];
                        $base64Data = substr($data->image_base64, strpos($data->image_base64, ',') + 1);
                        $binaryContent = base64_decode($base64Data);
                        
                        if ($binaryContent !== false) {
                            $imagePath = R2Helper::uploadRaw($binaryContent, $extension, 'checkins');
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('[CheckinService] Decode base64 image failed: ' . $e->getMessage());
                }
            }

            return $this->createAction->execute($data, $imagePath);
        });
    }
}
