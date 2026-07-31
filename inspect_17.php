<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\OcopProduct::find(17);

if ($p) {
    echo "ID: " . $p->id . "\n";
    echo "Name: " . $p->name . "\n";
    echo "Seller: " . $p->seller_name . "\n";
    echo "Description: " . $p->description . "\n";
    echo "Story: " . $p->story . "\n";

    // Clean up Product 17's story & description
    $p->story = "Bánh Sampa 4 sao là dòng sản phẩm cao cấp của Công ty TNHH Hoàng Chiến Thắng. Bánh được chọn lọc từ nguồn nguyên liệu thượng hạng, bột mỳ đạt chuẩn 95% kết hợp cùng trứng tươi và bơ cao cấp. Với công thức nướng giòn xốp đặc biệt và chứng nhận OCOP 4 sao Cấp Quốc Gia, bánh mang lại vị thơm béo đậm đà, phù hợp làm quà tặng di sản sang trọng.";

    $p->description = "Công ty TNHH Hoàng Chiến Thắng xin kính chào quý khách, cảm ơn quý khách đã sử dụng sản phẩm Bánh Sampa 4 sao!\n\n1. Hướng dẫn sử dụng & Bảo quản:\n- Sản phẩm ăn ngay không cần chế biến\n- Bảo quản ở nơi thoáng mát, sạch sẽ\n- Đậy kín miệng túi sau mỗi lần sử dụng\n\n2. Thành phần: Bột mỳ (95%), đường kính, nước sạch, dầu ăn, muối ăn, bột khai Ammonium Bicarbonate, trứng tươi.\n\n3. Thời hạn sử dụng: 12 tháng kể từ ngày sản xuất.\n\n4. Phân phối: Bán tại các siêu thị, cửa hàng tiện ích, cửa hàng tạp hóa.";

    $p->save();
    echo "CLEANED PRODUCT 17 SUCCESSFULLY!\n";
} else {
    echo "PRODUCT 17 NOT FOUND\n";
}
