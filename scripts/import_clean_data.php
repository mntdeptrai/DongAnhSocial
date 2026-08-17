<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$startTime = microtime(true);

echo "=== DONG BO DATABASE CHUAN 100% TU LOCAL LEN SERVER ===\n";

$sqlFile = __DIR__ . '/../database/clean_data_sync.sql';
if (!file_exists($sqlFile)) {
    echo "ERROR: File $sqlFile khong ton tai!\n";
    exit(1);
}

echo "Dang doc file SQL: " . number_format(filesize($sqlFile)) . " bytes...\n";
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "ERROR: Khong the doc file $sqlFile!\n";
    exit(1);
}

echo "Dang thuc thi import vao Database...\n";
try {
    DB::unprepared($sql);
} catch (\Throwable $e) {
    echo "ERROR khi import database: " . $e->getMessage() . "\n";
    exit(1);
}

$catId = DB::table('categories')->where('slug', 'co-so-kinh-doanh')->value('id');
$elapsed = round(microtime(true) - $startTime, 2);

echo "=== KET QUA DONG BO ===\n";
echo "Categories: " . DB::table('categories')->count() . "\n";
echo "Eateries: " . DB::table('eateries')->count() . "\n";
echo "Users: " . DB::table('users')->count() . "\n";
echo "OCOP Products / Gian hang: " . DB::table('ocop_products')->count() . "\n";
echo "HKD/DN trong co-so-kinh-doanh: " . ($catId ? DB::table('eateries')->where('category_id', $catId)->count() : 0) . "\n";
echo "Thoi gian thuc thi: {$elapsed}s\n";
echo "HOAN TAT DONG BO DU LIEU CHUAN XAC 100%!\n";

