<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$conns = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
foreach ($conns as $c) {
    try {
        echo "=== Connection: $c ===\n";
        $cats = \App\Models\Category::on($c)->get(['id', 'name', 'slug']);
        foreach ($cats as $cat) {
            $eateries = \App\Models\Eatery::on($c)->where('category_id', $cat->id)->get(['id', 'name', 'slug']);
            echo "Category: {$cat->name} (slug: {$cat->slug}, id: {$cat->id}) -> Count: " . count($eateries) . "\n";
            foreach ($eateries as $e) {
                echo "   - [{$e->id}] {$e->name} ({$e->slug})\n";
            }
        }
    } catch (\Exception $ex) {
        echo "Error on $c: " . $ex->getMessage() . "\n";
    }
}
