<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\OcopProduct::with(['eatery', 'eatery.commune'])->get();

$data = [];
foreach ($products as $p) {
    $eatery = $p->eatery;
    if (!$eatery) continue;
    $data[] = [
        'id' => $p->id,
        'name' => $p->name,
        'price' => $p->price ? number_format($p->price, 0, ',', '.') . 'đ' : 'Liên hệ',
        'star_rating' => $p->star_rating ?: '3 sao OCOP',
        'image' => $p->image_path ?: ($eatery->image_path ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80'),
        'eatery_name' => $eatery->name,
        'eatery_address' => $eatery->address . ($eatery->commune ? ', ' . $eatery->commune->name : ''),
        'lat' => (float)$eatery->latitude,
        'lng' => (float)$eatery->longitude,
        'description' => $p->description ?: ($eatery->description ?: 'Sản phẩm OCOP đặc trưng của Xã Đông Anh, đạt tiêu chuẩn chất lượng cao.')
    ];
}

echo "PARSED " . count($data) . " PRODUCTS:\n";
echo json_encode(array_slice($data, 0, 3), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
