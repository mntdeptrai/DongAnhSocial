<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Eatery;
use App\Models\OcopProduct;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cập nhật thông tin Tổng quan Chợ Lý Nhân (Eatery ID: 29)
        $lyNhan = Eatery::find(29) ?: Eatery::where('slug', 'like', 'cho-ly-nhan%')->first();
        
        $announcements = [
            [
                'id' => 1,
                'tag' => '🛡️ KIỂM ĐỊNH ATTP',
                'time' => 'Mới cập nhật',
                'title' => '100% sạp đạt chuẩn ATTP Tháng 8/2026',
                'content' => 'Đoàn kiểm tra liên ngành đã nghiệm thu chất lượng nguồn gốc nông sản & vệ sinh quầy hàng tại Chợ Lý Nhân.',
                'color' => '#10B981'
            ],
            [
                'id' => 2,
                'tag' => '🧼 VỆ SINH ĐỊNH KỲ',
                'time' => '18h00 Chủ Nhật',
                'title' => 'Lịch phun khử khuẩn toàn chợ',
                'content' => 'BQL tiến hành dọn vệ sinh tổng thể & phun tiêu độc khử khuẩn định kỳ vào cuối tuần.',
                'color' => '#0ea5e9'
            ],
            [
                'id' => 3,
                'tag' => '🎪 NÔNG SẢN DÂN SINH',
                'time' => 'Mỗi sáng hàng ngày',
                'title' => 'Phiên Chợ Dân Sinh Số 4.0 Thôn Lý Nhân',
                'content' => 'Đầy đủ thực phẩm tươi sống, rau củ quả, nông sản sạch & đồ ăn sáng thanh toán quét mã QR.',
                'color' => '#f59e0b'
            ]
        ];

        if ($lyNhan) {
            $lyNhan->update([
                'address' => 'Lý Nhân, Dục Tú, Đông Anh, Hà Nội, Việt Nam',
                'price_range' => '10.000đ - 260.000đ',
                'announcements' => $announcements,
                'status' => 'active'
            ]);
            $eateryId = $lyNhan->id;
        } else {
            $eateryId = DB::table('eateries')->insertGetId([
                'name' => 'Chợ Lý Nhân',
                'slug' => 'cho-ly-nhan-tgo5z',
                'category_id' => 8,
                'commune_id' => 6,
                'address' => 'Lý Nhân, Dục Tú, Đông Anh, Hà Nội, Việt Nam',
                'price_range' => '10.000đ - 260.000đ',
                'announcements' => json_encode($announcements, JSON_UNESCAPED_UNICODE),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Danh sách 26 Hộ Kinh Doanh / Sạp Tiểu Thương Chợ Lý Nhân từ file thống kê
        $stallsData = [
            [
                'seller_name' => 'Lý Thị La',
                'seller_phone' => '0392486364',
                'stall_name' => 'Gian hàng Bánh mỳ & Rau quả Cô La',
                'bank_name' => 'Vietcombank',
                'bank_account' => '0351000932466',
                'bank_holder' => 'LY THI LA',
                'origin' => 'Nhập lại',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Bánh mỳ kẹp pate thịt', 'price' => 20000, 'unit' => 'cái', 'desc' => 'Bánh mỳ kẹp pate thịt, chả nóng giòn rụm mỗi sáng.'],
                    ['name' => 'Rau củ quả tươi sạch', 'price' => 15000, 'unit' => 'kg', 'desc' => 'Rau củ quả sạch lấy tươi mới mỗi ngày.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Thành',
                'seller_phone' => '0349325973',
                'stall_name' => 'Gian hàng Tôm cá tươi Cô Thành',
                'bank_name' => 'BacABank',
                'bank_account' => '190001060013322',
                'bank_holder' => 'NGUYEN THI THANH',
                'origin' => 'Chợ Long Biên',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Tôm sông tươi sống', 'price' => 180000, 'unit' => 'kg', 'desc' => 'Tôm sông tươi sống nhập Chợ Long Biên hàng ngày.'],
                    ['name' => 'Cá tươi sống các loại', 'price' => 65000, 'unit' => 'kg', 'desc' => 'Cá tươi sống bơi khỏe, sơ chế sạch sẽ tại chỗ.']
                ]
            ],
            [
                'seller_name' => 'Trần Anh Tú',
                'seller_phone' => '0963652280',
                'stall_name' => 'Gian hàng Thịt gà tươi Anh Tú',
                'bank_name' => 'Techcombank',
                'bank_account' => '4067888888',
                'bank_holder' => 'TRAN ANH TU',
                'origin' => 'Chợ Từ Sơn',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Gà ta thả vườn nguyên con', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Gà ta thả vườn thịt săn chắc thơm ngon nhập Chợ Từ Sơn.'],
                    ['name' => 'Thịt gà làm sẵn sạch sẽ', 'price' => 130000, 'unit' => 'kg', 'desc' => 'Gà thịt mổ sẵn sạch sẽ giao tận tay.']
                ]
            ],
            [
                'seller_name' => 'Hoàng Thị Xuân',
                'seller_phone' => '0965591311',
                'stall_name' => 'Gian hàng Hoa quả tươi Cô Xuân',
                'bank_name' => 'MBBank',
                'bank_account' => '0965591311',
                'bank_holder' => 'HOANG THI XUAN',
                'origin' => 'Chợ Từ Sơn',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Hoa quả tươi theo mùa', 'price' => 35000, 'unit' => 'kg', 'desc' => 'Hoa quả sạch nhập chợ Từ Sơn tươi ngon ngọt mát.'],
                    ['name' => 'Dưa hấu / Cam sành ngọt', 'price' => 25000, 'unit' => 'kg', 'desc' => 'Dưa hấu ngọt lịm mọng nước, cam sành vắt nước.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Lệ',
                'seller_phone' => '0983660277',
                'stall_name' => 'Gian hàng Bánh kẹo Cô Lệ',
                'bank_name' => 'Vietcombank',
                'bank_account' => '10447857321',
                'bank_holder' => 'NGUYEN THI LE',
                'origin' => 'Các nhà phân phối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Bánh kẹo đặc sản các loại', 'price' => 45000, 'unit' => 'gói', 'desc' => 'Bánh kẹo phân phối chính hãng thơm ngon.'],
                    ['name' => 'Bánh quy & Kẹo tổng hợp', 'price' => 30000, 'unit' => 'gói', 'desc' => 'Bánh quy giòn thơm, kẹo hoa quả các loại.']
                ]
            ],
            [
                'seller_name' => 'Trần Thị Thoan',
                'seller_phone' => '0369219145',
                'stall_name' => 'Gian hàng Thịt lợn tươi Cô Thoan',
                'bank_name' => 'MBBank',
                'bank_account' => '8832140693',
                'bank_holder' => 'TRAN THI THOAN',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt ba chỉ lợn tươi sạch', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Thịt lợn tươi mổ trong ngày từ lò mổ đạt chuẩn ATTP.'],
                    ['name' => 'Sườn non / Thịt nạc vai', 'price' => 130000, 'unit' => 'kg', 'desc' => 'Sườn non mềm ngon, nạc vai xào nấu.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Tân',
                'seller_phone' => '0326487793',
                'stall_name' => 'Gian hàng Thịt lợn sạch Cô Tân',
                'bank_name' => 'Agribank',
                'bank_account' => '3140205167201',
                'bank_holder' => 'NGUYEN THI TAN',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt lợn nạc tươi ngon', 'price' => 115000, 'unit' => 'kg', 'desc' => 'Thịt lợn tươi từ lò mổ đạt chuẩn ATTP.'],
                    ['name' => 'Thịt chân giò lợn sạch', 'price' => 110000, 'unit' => 'kg', 'desc' => 'Chân giò lợn béo ngậy nấu canh, giả cầy.']
                ]
            ],
            [
                'seller_name' => 'Hoàng Thị Trang',
                'seller_phone' => '0978449378',
                'stall_name' => 'Gian hàng Thịt lợn sạch Cô Trang',
                'bank_name' => 'MBBank',
                'bank_account' => '0978449378',
                'bank_holder' => 'HOANG THI TRANG',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt mông / Ba chỉ tươi', 'price' => 115000, 'unit' => 'kg', 'desc' => 'Thịt tươi lò mổ kiểm dịch an toàn ATTP.'],
                    ['name' => 'Xương ống & Móng giò lợn', 'price' => 90000, 'unit' => 'kg', 'desc' => 'Xương ống hầm nước dùng ngọt đậm đà.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Hưng',
                'seller_phone' => '0342627517',
                'stall_name' => 'Gian hàng Hàng xén Cô Hưng',
                'bank_name' => 'MBBank',
                'bank_account' => '0342627517',
                'bank_holder' => 'NGUYEN THI HUNG',
                'origin' => 'Chợ Đồng Xuân',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Hàng xén & Đồ dùng tiện ích', 'price' => 20000, 'unit' => 'cái', 'desc' => 'Đồ xén gia đình nhập Chợ Đồng Xuân đa dạng mẫu mã.'],
                    ['name' => 'Đồ kim chỉ & Vật dụng sinh hoạt', 'price' => 15000, 'unit' => 'bộ', 'desc' => 'Kim chỉ, kéo, chun buộc và đồ gia dụng hàng ngày.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Thu',
                'seller_phone' => '0971996296',
                'stall_name' => 'Gian hàng Rau xanh tươi Cô Thu',
                'bank_name' => 'Techcombank',
                'bank_account' => '2251989999',
                'bank_holder' => 'NGUYEN THI THU',
                'origin' => 'Chợ đầu mối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Rau muống / Rau ngót xanh sạch', 'price' => 7000, 'unit' => 'mớ', 'desc' => 'Rau xanh sạch nhập chợ đầu mối tươi non mỗi sáng.'],
                    ['name' => 'Củ quả các loại tươi ngon', 'price' => 15000, 'unit' => 'kg', 'desc' => 'Bí đỏ, su su, cà rốt tươi ngon.']
                ]
            ],
            [
                'seller_name' => 'Đỗ Thị Linh',
                'seller_phone' => '0974100925',
                'stall_name' => 'Gian hàng Tạp hóa Cô Linh',
                'bank_name' => 'Techcombank',
                'bank_account' => '787519719999',
                'bank_holder' => 'DO THI LINH',
                'origin' => 'Các nhà phân phối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Gia vị, Mắm muối các loại', 'price' => 18000, 'unit' => 'chai', 'desc' => 'Nước mắm, hạt nêm, dầu hào nhà phân phối chính hãng.'],
                    ['name' => 'Dầu ăn & Tạp phẩm bách hóa', 'price' => 45000, 'unit' => 'chai', 'desc' => 'Dầu ăn thực vật nguyên chất và đồ dùng gia đình.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Thu',
                'seller_phone' => '0984475860',
                'stall_name' => 'Gian hàng Tạp hóa Bách hóa Cô Thu',
                'bank_name' => 'Techcombank',
                'bank_account' => '19076106268026',
                'bank_holder' => 'NGUYEN THI THU',
                'origin' => 'Các nhà phân phối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Mì tôm, Miến khô các loại', 'price' => 25000, 'unit' => 'gói', 'desc' => 'Mì tôm, miến dong, bánh đa khô chất lượng.'],
                    ['name' => 'Bách hóa & Hàng tiêu dùng nhanh', 'price' => 20000, 'unit' => 'món', 'desc' => 'Bách hóa tổng hợp sinh hoạt gia đình.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Hòa',
                'seller_phone' => '0977611282',
                'stall_name' => 'Gian hàng Thịt bò tươi Cô Hòa',
                'bank_name' => 'Vietinbank',
                'bank_account' => '102006343861',
                'bank_holder' => 'NGUYEN THI HOA',
                'origin' => 'Nhập lại',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt bò thăn tươi mềm', 'price' => 260000, 'unit' => 'kg', 'desc' => 'Bò thăn tươi dẻo đạt chuẩn ATTP.'],
                    ['name' => 'Bắp bò / Gầu bò tươi ngon', 'price' => 240000, 'unit' => 'kg', 'desc' => 'Bắp bò hoa giòn sần sật, gầu bò nấu phở lẩu.']
                ]
            ],
            [
                'seller_name' => 'Đặng Thị Dung',
                'seller_phone' => '0374373319',
                'stall_name' => 'Gian hàng Thịt bò sạch Cô Dung',
                'bank_name' => 'Techcombank',
                'bank_account' => '0374373319',
                'bank_holder' => 'DANG THI DUNG',
                'origin' => 'Nhập lại',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt bò phi lê tươi sạch', 'price' => 250000, 'unit' => 'kg', 'desc' => 'Bò tươi sạch chọn lọc đạt chuẩn ATTP.'],
                    ['name' => 'Nạm bò / Gân bò hầm sốt vang', 'price' => 220000, 'unit' => 'kg', 'desc' => 'Nạm bò, gân bò tươi giòn hầm sốt vang thơm lừng.']
                ]
            ],
            [
                'seller_name' => 'Đỗ Thị Tạo',
                'seller_phone' => '0966561592',
                'stall_name' => 'Gian hàng Cháo nóng Cô Tạo',
                'bank_name' => 'MBBank',
                'bank_account' => '0966561592',
                'bank_holder' => 'DO THI TAO',
                'origin' => 'Nhà tự nấu',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Cháo sườn nóng hổi', 'price' => 20000, 'unit' => 'bát', 'desc' => 'Cháo sườn nấu nhuyễn sánh mịn, sườn non thơm béo.'],
                    ['name' => 'Cháo trai / Cháo thịt băm', 'price' => 20000, 'unit' => 'bát', 'desc' => 'Cháo trai đồng thơm mùi rau răm, hành khô.']
                ]
            ],
            [
                'seller_name' => 'Hoàng Thị Nga',
                'seller_phone' => '0988671375',
                'stall_name' => 'Gian hàng Xôi & Cháo nóng Cô Nga',
                'bank_name' => 'Vietinbank',
                'bank_account' => '0988671375',
                'bank_holder' => 'HOANG THI NGA',
                'origin' => 'Nhà tự nấu',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Xôi xéo / Xôi ngô chả ruốc', 'price' => 15000, 'unit' => 'gói', 'desc' => 'Xôi nếp cái hoa vàng dẻo thơm ngào ngạt hành phi ruốc thịt.'],
                    ['name' => 'Cháo sườn quẩy nóng', 'price' => 20000, 'unit' => 'bát', 'desc' => 'Cháo sườn nóng hổi kèm quẩy giòn tan.']
                ]
            ],
            [
                'seller_name' => 'Hoàng Thị Khánh',
                'seller_phone' => '0396531026',
                'stall_name' => 'Gian hàng Xôi sáng Cô Khánh',
                'bank_name' => 'MBBank',
                'bank_account' => '0396531026',
                'bank_holder' => 'HOANG THI KHANH',
                'origin' => 'Nhà tự nấu',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Xôi đỗ xanh chả ruốc thơm ngon', 'price' => 15000, 'unit' => 'gói', 'desc' => 'Xôi nếp đỗ xanh dẻo thơm gia truyền.'],
                    ['name' => 'Xôi vò chè đường ngọt thanh', 'price' => 15000, 'unit' => 'gói', 'desc' => 'Xôi vò tơi xốp ăn kèm chè hoa cau chè đường thanh mát.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Thúy',
                'seller_phone' => '0373775715',
                'stall_name' => 'Gian hàng Bánh ngon Cô Thúy',
                'bank_name' => 'MBBank',
                'bank_account' => '0373775715',
                'bank_holder' => 'NGUYEN THI THUY',
                'origin' => 'Tự nấu',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Bánh rán mặn / ngọt nóng hổi', 'price' => 5000, 'unit' => 'chiếc', 'desc' => 'Bánh rán vỏ giòn rụm, nhân thịt mộc nhĩ thơm lừng.'],
                    ['name' => 'Bánh giò nóng / Bánh đúc lạc', 'price' => 15000, 'unit' => 'chiếc', 'desc' => 'Bánh giò nóng mềm béo, bánh đúc chấm tương bần.']
                ]
            ],
            [
                'seller_name' => 'Đàm Thị Hòa',
                'seller_phone' => '0358922392',
                'stall_name' => 'Gian hàng Thời trang Quần áo Cô Hòa',
                'bank_name' => 'Vietinbank',
                'bank_account' => '109001047307',
                'bank_holder' => 'DAM THI HOA',
                'origin' => 'Nhập thanh lý',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Bộ quần áo mặc nhà nam nữ', 'price' => 75000, 'unit' => 'bộ', 'desc' => 'Quần áo chất liệu cotton thoáng mát, bền đẹp.'],
                    ['name' => 'Áo phông / Quần đùi thể thao', 'price' => 50000, 'unit' => 'chiếc', 'desc' => 'Áo phông co giãn, thấm hút mồ hôi tốt.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Hằng',
                'seller_phone' => '0971931389',
                'stall_name' => 'Gian hàng Thời trang Quần áo Cô Hằng',
                'bank_name' => 'Vietcombank',
                'bank_account' => '0961000028139',
                'bank_holder' => 'NGUYEN THI HANG',
                'origin' => 'Chợ Ninh Hiệp',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Quần áo thời trang Ninh Hiệp', 'price' => 85000, 'unit' => 'bộ', 'desc' => 'Quần áo mẫu mới nhập Chợ Ninh Hiệp hợp xu hướng.'],
                    ['name' => 'Váy đầm & Đồ mặc nhà nữ', 'price' => 90000, 'unit' => 'chiếc', 'desc' => 'Váy đầm kiểu dáng trang nhã, vải mát.']
                ]
            ],
            [
                'seller_name' => 'Hoàng Thị Thu Hà',
                'seller_phone' => '0986891123',
                'stall_name' => 'Gian hàng Hàng xén Cô Thu Hà',
                'bank_name' => 'Techcombank',
                'bank_account' => '19037105006014',
                'bank_holder' => 'HOANG THI THU HA',
                'origin' => 'Chợ Từ Sơn',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Hàng xén & Đồ tạp dụng sinh hoạt', 'price' => 15000, 'unit' => 'cái', 'desc' => 'Đồ dùng tiện ích gia đình nhập Chợ Từ Sơn.'],
                    ['name' => 'Phụ kiện kẹp tóc & Đồ sinh hoạt nhỏ', 'price' => 10000, 'unit' => 'chiếc', 'desc' => 'Kẹp nơ, dây buộc tóc, phụ kiện xinh xắn.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Hà',
                'seller_phone' => '0982331422',
                'stall_name' => 'Gian hàng Giò chả nóng Cô Hà',
                'bank_name' => 'MBBank',
                'bank_account' => '0982331422',
                'bank_holder' => 'NGUYEN THI HA',
                'origin' => 'Tự làm',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Giò lụa truyền thống thơm ngon', 'price' => 150000, 'unit' => 'kg', 'desc' => 'Giò lụa tự làm từ thịt lợn tươi nóng dẻo, không hàn the.'],
                    ['name' => 'Chả quế nướng vàng ươm', 'price' => 150000, 'unit' => 'kg', 'desc' => 'Chả quế thơm phức mùi quế chi cay nhẹ giòn vỏ.']
                ]
            ],
            [
                'seller_name' => 'Trần Thị Tuyết',
                'seller_phone' => '0987785821',
                'stall_name' => 'Gian hàng Hoa quả tươi Cô Tuyết',
                'bank_name' => 'MBBank',
                'bank_account' => '0987785821',
                'bank_holder' => 'TRAN THI TUYET',
                'origin' => 'Chợ đầu mối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Táo đỏ / Nho tươi giòn ngọt', 'price' => 60000, 'unit' => 'kg', 'desc' => 'Hoa quả sạch nhập khẩu & nội địa chọn lọc tươi ngon.'],
                    ['name' => 'Cam sành / Xoài cát chín thơm', 'price' => 35000, 'unit' => 'kg', 'desc' => 'Cam mọng nước, xoài ngọt đậm vị.']
                ]
            ],
            [
                'seller_name' => 'Hoàng Thị Hòa',
                'seller_phone' => '0338291962',
                'stall_name' => 'Gian hàng Hoa quả sạch Cô Hòa',
                'bank_name' => 'MBBank',
                'bank_account' => '0338291962',
                'bank_holder' => 'HOANG THI HOA',
                'origin' => 'Chợ Từ Sơn',
                'has_smart_phone' => false,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Thanh long / Chuối tiêu quả', 'price' => 25000, 'unit' => 'kg', 'desc' => 'Hoa quả tươi ngon nhập Chợ Từ Sơn hàng ngày.'],
                    ['name' => 'Ổi găng / Cóc chín giòn ngọt', 'price' => 20000, 'unit' => 'kg', 'desc' => 'Ổi giòn ngọt, cóc bao tử dầm chấm muối ớt.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Chất',
                'seller_phone' => '0975381284',
                'stall_name' => 'Gian hàng Thịt gà tươi Cô Chất',
                'bank_name' => 'MBBank',
                'bank_account' => '2906061983',
                'bank_holder' => 'NGUYEN THI CHAT',
                'origin' => 'Chợ Từ Sơn',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Gà ri ta thả vườn nguyên con', 'price' => 125000, 'unit' => 'kg', 'desc' => 'Gà ri ta thịt vàng ươm dai ngọt đạt chuẩn ATTP.'],
                    ['name' => 'Thịt gà làm sạch nguyên con', 'price' => 135000, 'unit' => 'kg', 'desc' => 'Gà làm sạch mổ moi giao ngay tại quầy.']
                ]
            ],
            [
                'seller_name' => 'Trần Thị Tình',
                'seller_phone' => '0357162025',
                'stall_name' => 'Gian hàng Thịt lợn & Giò chả Cô Tình',
                'bank_name' => 'MBBank',
                'bank_account' => '789034456',
                'bank_holder' => 'TRAN THI TINH',
                'origin' => 'Chợ Long / Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt lợn tươi sạch mổ trong ngày', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Thịt lợn tươi từ lò mổ đạt chuẩn ATTP.'],
                    ['name' => 'Giò lụa nóng thơm giòn', 'price' => 150000, 'unit' => 'kg', 'desc' => 'Giò lụa làm từ thịt nóng tươi mới mỗi sáng.']
                ]
            ],
        ];

        // 3. Xóa các sản phẩm cũ của Chợ Lý Nhân nếu có (để tránh trùng lặp)
        DB::table('ocop_products')->where('eatery_id', $eateryId)->delete();
        try {
            DB::connection('mysql_market')->table('ocop_products')->where('eatery_id', $eateryId)->delete();
        } catch (\Exception $e) {}

        // 4. Tạo tài khoản Seller và thêm sản phẩm cho từng gian hàng
        foreach ($stallsData as $stall) {
            $rawPhone = preg_replace('/[^0-9]/', '', $stall['seller_phone']);
            $username = $rawPhone ?: ('seller_lynhan_' . Str::slug($stall['seller_name'], '_'));

            // Tìm hoặc tạo User Seller
            $user = User::where('phone', $rawPhone)->first() ?: User::where('username', $username)->first();

            if (!$user) {
                $user = User::create([
                    'name'         => $stall['seller_name'],
                    'username'     => $username,
                    'email'        => null,
                    'phone'        => $rawPhone,
                    'password'     => Hash::make('12345678'),
                    'role'         => 'seller',
                    'status'       => 'active',
                    'is_verified'  => 1,
                    'bank_account' => $stall['bank_account'],
                    'bank_name'    => $stall['bank_name'],
                    'eatery_id'    => $eateryId,
                ]);
            } else {
                $user->update([
                    'name'         => $stall['seller_name'],
                    'phone'        => $rawPhone,
                    'bank_account' => $stall['bank_account'] ?: $user->bank_account,
                    'bank_name'    => $stall['bank_name'] ?: $user->bank_name,
                    'eatery_id'    => $eateryId,
                    'role'         => 'seller',
                    'status'       => 'active',
                    'is_verified'  => 1,
                ]);
            }

            $firstInsertedProdId = null;

            // Thêm từng sản phẩm của gian hàng
            foreach ($stall['products'] as $p) {
                $addInfo = 'TT+' . Str::slug($stall['stall_name'], '+');
                $qrUrl = "https://img.vietqr.io/image/{$stall['bank_name']}-{$stall['bank_account']}-compact.png?accountName=" . urlencode($stall['bank_holder']) . "&addInfo={$addInfo}";

                $descFull = "Nguồn gốc: {$stall['origin']}. " . $p['desc'] . ($stall['bank_account'] ? " Hỗ trợ thanh toán VietQR ngân hàng {$stall['bank_name']}: {$stall['bank_account']}." : "") . ($stall['has_smart_phone'] ? " Có sử dụng smartphone." : "");

                $insertData = [
                    'eatery_id'     => $eateryId,
                    'user_id'       => $user->id,
                    'stall_name'    => $stall['stall_name'],
                    'seller_name'   => $stall['seller_name'],
                    'seller_phone'  => $stall['seller_phone'],
                    'bank_name'     => $stall['bank_name'],
                    'bank_account'  => $stall['bank_account'],
                    'bank_holder'   => $stall['bank_holder'],
                    'qr_code_path'  => $qrUrl,
                    'name'          => $p['name'],
                    'slug'          => Str::slug($p['name']) . '-' . Str::random(5),
                    'price'         => $p['price'],
                    'unit'          => $p['unit'],
                    'description'   => $descFull,
                    'image_path'    => null,
                    'star_rating'   => '4 sao',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];

                $prodId = DB::table('ocop_products')->insertGetId($insertData);
                try {
                    DB::connection('mysql_market')->table('ocop_products')->insert(array_merge($insertData, ['id' => $prodId]));
                } catch (\Exception $e) {}

                if (!$firstInsertedProdId) {
                    $firstInsertedProdId = $prodId;
                }
            }

            // Gắn stall_id vào user để định danh chính xác gian hàng
            if ($firstInsertedProdId) {
                $user->update(['stall_id' => $firstInsertedProdId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $lyNhan = Eatery::find(29) ?: Eatery::where('slug', 'like', 'cho-ly-nhan%')->first();
        if ($lyNhan) {
            DB::table('ocop_products')->where('eatery_id', $lyNhan->id)->delete();
            try {
                DB::connection('mysql_market')->table('ocop_products')->where('eatery_id', $lyNhan->id)->delete();
            } catch (\Exception $e) {}
        }
    }
};
