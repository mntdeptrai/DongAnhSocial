<?php

namespace App\Observers;

use App\Models\Eatery;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EateryObserver
{
    public function creating(Eatery $eatery): void
    {
        if (empty($eatery->slug)) {
            $eatery->slug = Str::slug($eatery->name) . '-' . substr(md5(uniqid()), 0, 5);
        }
    }

    public function created(Eatery $eatery): void
    {
        Log::info("Địa điểm ẩm thực mới '{$eatery->name}' (ID: {$eatery->id}) đã được đăng ký thành công trên connection [{$eatery->getConnectionName()}].");
    }

    public function updated(Eatery $eatery): void
    {
        Log::info("Thông tin địa điểm '{$eatery->name}' (ID: {$eatery->id}) đã được cập nhật thành công.");
    }

    public function deleted(Eatery $eatery): void
    {
        Log::info("Địa điểm '{$eatery->name}' (ID: {$eatery->id}) đã được xóa khỏi hệ thống.");
    }
}
