<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\OcopCertifiedProduct;
use App\Models\Eatery;
use App\Models\User;

echo "========================================================\n";
echo "   CHUYỂN TOÀN BỘ DỮ LIỆU SẢN PHẨM OCOP CHUẨN ĐÔNG ANH   \n";
echo "========================================================\n\n";

$connections = ['mysql'];
if (config('database.connections.mysql_market')) {
    $connections[] = 'mysql_market';
}

$rawOfficialProducts = [
    // --- NĂM 2022 ---
    [
        'eatery_id' => 34,
        'stall_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
        'name' => 'Đông trùng hạ thảo tươi',
        'star_rating' => '4 sao',
        'price' => 250000.00,
        'unit' => 'hộp',
        'heritage_year' => '2022',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Lộc Hà, xã Đông Anh; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
        'story' => 'Đông trùng hạ thảo nuôi cấy theo tiêu chuẩn công nghệ cao KOVI, giàu dược chất Cordycepin và Adenosine hỗ trợ tăng cường sức khỏe.',
        'artisans' => 'HTX Nông nghiệp Dược liệu Công nghệ cao KOVI',
    ],
    [
        'eatery_id' => 34,
        'stall_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
        'name' => 'Đông trùng hạ thảo khô',
        'star_rating' => '4 sao',
        'price' => 450000.00,
        'unit' => 'lọ 20g',
        'heritage_year' => '2022',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Lộc Hà, xã Đông Anh; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
        'story' => 'Sấy thăng hoa nhiệt độ âm sâu giữ trọn 99% dược chất tự nhiên quý giá.',
        'artisans' => 'HTX Nông nghiệp Dược liệu Công nghệ cao KOVI',
    ],
    [
        'eatery_id' => 34,
        'stall_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
        'name' => 'Đông trùng hạ thảo ký chủ nhộng tằm',
        'star_rating' => '4 sao',
        'price' => 650000.00,
        'unit' => 'hộp 30 con',
        'heritage_year' => '2022',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Lộc Hà, xã Đông Anh; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
        'story' => 'Nuôi cấy trực tiếp trên cá thể nhộng tằm sống cho hàm lượng dược tính cao vượt trội.',
        'artisans' => 'HTX Nông nghiệp Dược liệu Công nghệ cao KOVI',
    ],
    [
        'eatery_id' => 35,
        'stall_name' => 'Hộ kinh doanh Trần Văn Tần',
        'name' => 'Tượng Phật Đại Thế Chí Bồ Tát',
        'star_rating' => '4 sao',
        'price' => 12000000.00,
        'unit' => 'pho',
        'heritage_year' => '2022',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Thạc Quả, xã Đông Anh; Điêu khắc gỗ thủ công tinh xảo',
        'story' => 'Nghệ thuật điêu khắc gỗ truyền thống Thạc Quả, tạo tác từ gỗ nguyên khối với đường nét từ bi, thanh tịnh.',
        'artisans' => 'Nghệ nhân Trần Văn Tần',
    ],
    [
        'eatery_id' => 35,
        'stall_name' => 'Hộ kinh doanh Trần Văn Tần',
        'name' => 'Song ngư sinh tài',
        'star_rating' => '4 sao',
        'price' => 8500000.00,
        'unit' => 'tác phẩm',
        'heritage_year' => '2022',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Thạc Quả, xã Đông Anh; Mỹ nghệ phong thủy gỗ quý',
        'story' => 'Biểu tượng phong thủy may mắn, sung túc và trường tồn qua bàn tay đục đẽo tinh tế của nghệ nhân Đông Anh.',
        'artisans' => 'Nghệ nhân Trần Văn Tần',
    ],
    [
        'eatery_id' => 36,
        'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
        'name' => 'Bánh gạo lứt',
        'star_rating' => '4 sao',
        'price' => 30000.00,
        'unit' => 'gói 200g',
        'heritage_year' => '2022',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội',
        'story' => 'Bánh làm từ hạt gạo lứt hữu cơ nguyên cám, giòn rụm, thanh ngọt tự nhiên, giàu chất xơ và khoáng chất.',
        'artisans' => 'Công ty TNHH Hoàng Chiến Thắng',
    ],
    [
        'eatery_id' => 36,
        'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
        'name' => 'Bánh vừng vòng',
        'star_rating' => '3 sao',
        'price' => 30000.00,
        'unit' => 'gói 250g',
        'heritage_year' => '2022',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội',
        'story' => 'Thức quà truyền thống gắn liền với tuổi thơ, vừng rang thơm phức quyện trong từng chiếc bánh tròn xoe giòn tan.',
        'artisans' => 'Công ty TNHH Hoàng Chiến Thắng',
    ],
    [
        'eatery_id' => 37,
        'stall_name' => 'Hợp tác xã dịch vụ nông nghiệp thôn Đoài',
        'name' => 'Tương Việt Hùng',
        'star_rating' => '3 sao',
        'price' => 45000.00,
        'unit' => 'chai 500ml',
        'heritage_year' => '2022',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Đoài, xã Đông Anh, TP. Hà Nội',
        'story' => 'Tương ủ tự nhiên từ gạo nếp và đỗ tương tuyển chọn, dậy mùi thơm ngào ngạt đậm đà hương vị đồng quê Bắc Bộ.',
        'artisans' => 'HTX Dịch vụ Nông nghiệp Thôn Đoài',
    ],

    // --- NĂM 2023 ---
    [
        'eatery_id' => 38,
        'stall_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
        'name' => 'Bánh xốp vừng',
        'star_rating' => '3 sao',
        'price' => 35000.00,
        'unit' => 'gói 200g',
        'heritage_year' => '2023',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'xóm Đông Ngàn, xã Đông Anh',
        'story' => 'Bánh xốp mềm mịn phủ lớp vừng vàng óng, thơm bùi vị bơ trứng thượng hạng.',
        'artisans' => 'Cơ sở bánh ngọt Thùy Quyên',
    ],
    [
        'eatery_id' => 38,
        'stall_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
        'name' => 'Bánh Sampa',
        'star_rating' => '4 sao',
        'price' => 35000.00,
        'unit' => 'gói 250g',
        'heritage_year' => '2023',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'xóm Đông Ngàn, xã Đông Anh',
        'story' => 'Bánh quy sampa xốp nhẹ, hương vani tự nhiên hòa quyện thích hợp thưởng thức cùng trà nóng.',
        'artisans' => 'Cơ sở bánh ngọt Thùy Quyên',
    ],
    [
        'eatery_id' => 38,
        'stall_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
        'name' => 'Bánh trứng nhện',
        'star_rating' => '3 sao',
        'price' => 30000.00,
        'unit' => 'gói 200g',
        'heritage_year' => '2023',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'xóm Đông Ngàn, xã Đông Anh',
        'story' => 'Từng sợi bánh mảnh giòn tan đan xen như mạng nhện, rắc mè rang thơm lừng hấp dẫn.',
        'artisans' => 'Cơ sở bánh ngọt Thùy Quyên',
    ],
    [
        'eatery_id' => 39,
        'stall_name' => 'Hộ kinh doanh Thạo Loan',
        'name' => 'Rượu gạo nếp Long Tửu',
        'star_rating' => '3 sao',
        'price' => 120000.00,
        'unit' => 'chai 500ml',
        'heritage_year' => '2023',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Xuân Canh, xã Đông Anh; Men thuốc bắc cổ truyền',
        'story' => 'Nấu hoàn toàn thủ công từ nếp cái hoa vàng và men lá 36 vị thuốc bắc trứ danh làng Xuân Canh.',
        'artisans' => 'Hộ kinh doanh Thạo Loan',
    ],
    [
        'eatery_id' => 39,
        'stall_name' => 'Hộ kinh doanh Thạo Loan',
        'name' => 'Rượu dâu Long Tửu',
        'star_rating' => '3 sao',
        'price' => 150000.00,
        'unit' => 'chai 500ml',
        'heritage_year' => '2023',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Xuân Canh, xã Đông Anh',
        'story' => 'Ngâm ủ từ trái dâu tằm tươi chín mọng với rượu nếp hạ thổ, màu đỏ ruby sóng sánh, vị chua ngọt êm dịu.',
        'artisans' => 'Hộ kinh doanh Thạo Loan',
    ],

    // --- NĂM 2024 ---
    [
        'eatery_id' => 40,
        'stall_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
        'name' => 'Hành lá Cổ Loa',
        'star_rating' => '3 sao',
        'price' => 25000.00,
        'unit' => 'kg',
        'heritage_year' => '2024',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Vùng trồng rau an toàn Cổ Loa, xã Đông Anh',
        'story' => 'Trồng theo tiêu chuẩn VietGAP trên đất phù sa cổ thành Cổ Loa, cọng hành xanh tươi, thơm nồng đặc trưng.',
        'artisans' => 'HTX Nông nghiệp Cổ Loa',
    ],
    [
        'eatery_id' => 40,
        'stall_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
        'name' => 'Khoai tây Cổ Loa',
        'star_rating' => '3 sao',
        'price' => 30000.00,
        'unit' => 'kg',
        'heritage_year' => '2024',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Vùng trồng nông sản Cổ Loa, xã Đông Anh',
        'story' => 'Củ khoai vàng ruộm, bùi bở, giàu dinh dưỡng trồng chuẩn an toàn sinh học.',
        'artisans' => 'HTX Nông nghiệp Cổ Loa',
    ],
    [
        'eatery_id' => 42,
        'stall_name' => 'Hợp tác xã dịch vụ nông nghiệp kinh doanh tổng hợp Dục Tú',
        'name' => 'Gạo nếp cái hoa vàng Dục Tú',
        'star_rating' => '4 sao',
        'price' => 45000.00,
        'unit' => 'kg',
        'heritage_year' => '2024',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Dục Tú, xã Đông Anh; Giống lúa nếp đặc sản cổ truyền',
        'story' => 'Đặc sản trứ danh đất Dục Tú, hạt gạo tròn mẩy, khi nấu xôi dẻo thơm nức mũi dù để nguội nhiều giờ.',
        'artisans' => 'HTX Nông nghiệp Dục Tú',
    ],
    [
        'eatery_id' => 36,
        'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
        'name' => 'Bánh nhện vừng',
        'star_rating' => '4 sao',
        'price' => 30000.00,
        'unit' => 'gói 200g',
        'heritage_year' => '2024',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội',
        'story' => 'Hương vị giòn rụm kết hợp giữa đường kính trắng, bột nếp và vừng thơm hảo hạng.',
        'artisans' => 'Công ty TNHH Hoàng Chiến Thắng',
    ],
    [
        'eatery_id' => 36,
        'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
        'name' => 'Bánh Vừng Dừa (Coconut Biscuit)',
        'star_rating' => '4 sao',
        'price' => 35000.00,
        'unit' => 'gói 250g',
        'heritage_year' => '2024',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội',
        'story' => 'Sự kết hợp hoàn hảo giữa cùi dừa Bến Tre béo ngậy và vừng rang thơm bùi xứ Bắc.',
        'artisans' => 'Công ty TNHH Hoàng Chiến Thắng',
    ],
    [
        'eatery_id' => 39,
        'stall_name' => 'Hộ kinh doanh Thạo Loan',
        'name' => 'Rượu mơ Long Tửu',
        'star_rating' => '3 sao',
        'price' => 160000.00,
        'unit' => 'chai 500ml',
        'heritage_year' => '2024',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Xuân Canh, xã Đông Anh',
        'story' => 'Trái mơ rừng Hương Tích lên men tự nhiên hòa quyện với rượu gạo nếp thơm ngon.',
        'artisans' => 'Hộ kinh doanh Thạo Loan',
    ],
    [
        'eatery_id' => 39,
        'stall_name' => 'Hộ kinh doanh Thạo Loan',
        'name' => 'Rượu Bạch cúc Long Tửu',
        'star_rating' => '3 sao',
        'price' => 180000.00,
        'unit' => 'chai 500ml',
        'heritage_year' => '2024',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Xuân Canh, xã Đông Anh',
        'story' => 'Rượu ngâm hoa cúc trắng tiến vua, thanh nhiệt, hương thơm tao nhã quý phái.',
        'artisans' => 'Hộ kinh doanh Thạo Loan',
    ],

    // --- NĂM 2025 ---
    [
        'eatery_id' => 41,
        'stall_name' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
        'name' => 'Giò lụa truyền thống',
        'star_rating' => '4 sao',
        'price' => 200000.00,
        'unit' => 'kg',
        'heritage_year' => '2025',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Xóm Thượng, xã Đông Anh; Thịt nạc mông tươi ngon',
        'story' => 'Giã tay bí truyền, thịt lợn tươi nóng dẻo quánh, nước mắm cốt cá cơm nguyên chất gói lá chuối tiêu.',
        'artisans' => 'Cơ sở Thực phẩm Liêm Hiệp',
    ],
    [
        'eatery_id' => 41,
        'stall_name' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
        'name' => 'Chả lụa chiên',
        'star_rating' => '3 sao',
        'price' => 200000.00,
        'unit' => 'kg',
        'heritage_year' => '2025',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Xóm Thượng, xã Đông Anh',
        'story' => 'Vỏ ngoài vàng ruộm thơm lừng, ruột chả dai giòn sần sật tự nhiên không hàn the.',
        'artisans' => 'Cơ sở Thực phẩm Liêm Hiệp',
    ],
    [
        'eatery_id' => 36,
        'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
        'name' => 'Bánh vừng Cookies',
        'star_rating' => '3 sao',
        'price' => 35000.00,
        'unit' => 'gói 200g',
        'heritage_year' => '2025',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội',
        'story' => 'Phong cách cookies châu Âu hiện đại kết hợp hạt mè rang truyền thống Đông Anh.',
        'artisans' => 'Công ty TNHH Hoàng Chiến Thắng',
    ],
    [
        'eatery_id' => 36,
        'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
        'name' => 'Bánh gạo thơm',
        'star_rating' => '3 sao',
        'price' => 30000.00,
        'unit' => 'gói 200g',
        'heritage_year' => '2025',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội',
        'story' => 'Nướng giòn xốp từ bột gạo tẻ hương thơm đặc sản Bắc Bộ.',
        'artisans' => 'Công ty TNHH Hoàng Chiến Thắng',
    ],
    [
        'eatery_id' => 40,
        'stall_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
        'name' => 'Bí đỏ Cổ Loa',
        'star_rating' => '3 sao',
        'price' => 20000.00,
        'unit' => 'kg',
        'heritage_year' => '2025',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Trung tâm Cổ Loa, xã Đông Anh',
        'story' => 'Bí đỏ dày cùi, dẻo ngọt, giàu vitamin A bồi bổ trí não.',
        'artisans' => 'HTX Nông nghiệp Cổ Loa',
    ],
    [
        'eatery_id' => 40,
        'stall_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
        'name' => 'Lạc nhân Cổ Loa',
        'star_rating' => '3 sao',
        'price' => 65000.00,
        'unit' => 'kg',
        'heritage_year' => '2025',
        'image_path' => '/assets/icon/default_food.png',
        'description' => 'Trung tâm Cổ Loa, xã Đông Anh',
        'story' => 'Hạt lạc đỏ mẩy đều, vỏ lụa căng bóng, bùi béo ngọt đậm đà.',
        'artisans' => 'HTX Nông nghiệp Cổ Loa',
    ],
];

// Tiến hành đồng bộ sang ocop_certified_products
foreach ($connections as $conn) {
    echo "▶ Đồng bộ trên kết nối [{$conn}]...\n";
    if (!Schema::connection($conn)->hasTable('ocop_certified_products')) {
        continue;
    }

    $count = 0;
    foreach ($rawOfficialProducts as $idx => $item) {
        $slug = Str::slug($item['name']) . '-' . ($idx + 1);

        // Tìm user chủ thể nếu có
        $ownerUser = User::where('eatery_id', $item['eatery_id'])->first();

        // Kiểm tra hình ảnh từ eatery nếu có
        $eatery = Eatery::find($item['eatery_id']);
        $img = $eatery?->avatar_path ?? $item['image_path'];

        $data = [
            'eatery_id'       => $item['eatery_id'],
            'user_id'         => $ownerUser ? $ownerUser->id : null,
            'name'            => $item['name'],
            'slug'            => $slug,
            'price'           => $item['price'] ?? 50000,
            'unit'            => $item['unit'] ?? 'sản phẩm',
            'star_rating'     => $item['star_rating'],
            'description'     => $item['description'],
            'story'           => $item['story'],
            'artisans'        => $item['artisans'],
            'heritage_year'   => $item['heritage_year'],
            'fun_fact'        => 'Sản phẩm OCOP tiêu biểu được xếp hạng sao cấp Huyện & Thành phố Hà Nội.',
            'image_path'      => $img,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];

        DB::connection($conn)->table('ocop_certified_products')->updateOrInsert(
            ['name' => $item['name']],
            $data
        );
        $count++;
    }

    echo "  -> Đã cập nhật thành công {$count} sản phẩm OCOP chính thức vào ocop_certified_products.\n";
}

echo "\n========================================================\n";
echo "           DANH SÁCH SẢN PHẨM OCOP TRONG BẢNG MỚI        \n";
echo "========================================================\n";

$all = OcopCertifiedProduct::orderBy('heritage_year', 'desc')->orderBy('name')->get();
foreach ($all as $i => $p) {
    $price = $p->price > 0 ? number_format($p->price, 0, ',', '.') . 'đ/' . $p->unit : 'Liên hệ';
    echo sprintf(
        " %2d. %-35s | ⭐ %-6s | 📅 %-4s | 💰 %-18s\n",
        $i + 1,
        $p->name,
        $p->star_rating,
        $p->heritage_year,
        $price
    );
}

echo "\nTổng cộng: " . $all->count() . " sản phẩm OCOP chuẩn Đông Anh đã sẵn sàng!\n";
