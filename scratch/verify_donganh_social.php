<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LARAVEL CONNECTED TO DATABASE: " . DB::connection()->getDatabaseName() . " ===\n";
echo "Total Users: " . \App\Models\User::count() . "\n";
echo "Total Eateries/Places: " . \App\Models\Eatery::count() . "\n";
echo "Total Categories: " . \App\Models\Category::count() . "\n";
echo "Total OCOP Products: " . DB::table('ocop_products')->count() . "\n";
echo "Total Cultural Activities: " . DB::table('cultural_activities')->count() . "\n";
echo "Total Food Tours: " . \App\Models\FoodTour::count() . "\n";
echo "Total Checkins: " . \App\Models\Checkin::count() . "\n";
