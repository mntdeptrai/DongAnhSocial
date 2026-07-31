<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\OcopProduct::first();
echo "OCOP PRODUCT COLUMNS:\n";
print_r(array_keys($p->getAttributes()));

$e = App\Models\Eatery::first();
echo "\nEATERY COLUMNS:\n";
print_r(array_keys($e->getAttributes()));
