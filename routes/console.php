<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động dọn rác AI Tour bản nháp (draft) mỗi ngày lúc 2 giờ sáng
Schedule::command('ai:cleanup-tours')->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
