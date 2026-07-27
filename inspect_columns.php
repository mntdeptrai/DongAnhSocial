<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Columns in mysql_market.ocop_products:\n";
print_r(Schema::connection('mysql_market')->getColumnListing('ocop_products'));

$sample = \Illuminate\Support\Facades\DB::connection('mysql_market')->table('ocop_products')->first();
echo "\nSample row:\n";
print_r($sample);
