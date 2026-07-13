<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

class TrustHubObserver
{
    public function created($model): void
    {
        $className = class_basename($model);
        Log::info("Hồ sơ Trust Hub mới thuộc loại [{$className}] (ID: {$model->id}) đã được thêm cho địa điểm ID [{$model->eatery_id}].");
    }

    public function deleted($model): void
    {
        $className = class_basename($model);
        Log::info("Hồ sơ Trust Hub loại [{$className}] (ID: {$model->id}) đã được gỡ bỏ.");
    }
}
