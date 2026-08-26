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
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (\Throwable $e) {}

        if (\Illuminate\Support\Facades\Schema::hasTable('ocop_products')) {
            \Illuminate\Support\Facades\Schema::table('ocop_products', function ($table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('ocop_products', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->index()->after('eatery_id');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('ocop_products', 'stall_name')) {
                    $table->string('stall_name')->nullable()->after('user_id');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('ocop_products', 'seller_name')) {
                    $table->string('seller_name')->nullable()->after('stall_name');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('ocop_products', 'seller_phone')) {
                    $table->string('seller_phone')->nullable()->after('seller_name');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('ocop_products', 'bank_name')) {
                    $table->string('bank_name')->nullable()->after('seller_phone');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('ocop_products', 'bank_account')) {
                    $table->string('bank_account')->nullable()->after('bank_name');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('ocop_products', 'bank_holder')) {
                    $table->string('bank_holder')->nullable()->after('bank_account');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('ocop_products', 'qr_code_path')) {
                    $table->string('qr_code_path', 500)->nullable()->after('bank_holder');
                }
            });
        }

        // 1. Cập nhật thông tin Tổng quan Chợ Dục Tú (Eatery ID: 22)
        $ducTu = Eatery::find(22) ?: Eatery::where('slug', 'like', 'cho-duc-tu%')->orWhere('name', 'like', '%Dục Tú%')->first();

        $announcements = [
            [
                'id' => 1,
                'tag' => '🛡️ KIỂM ĐỊNH ATTP',
                'time' => 'Mới cập nhật',
                'title' => '100% sạp đạt chuẩn ATTP Tháng 8/2026',
                'content' => 'Đoàn kiểm tra liên ngành đã nghiệm thu chất lượng nguồn gốc nông sản & vệ sinh quầy hàng tại Chợ Dục Tú.',
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
                'title' => 'Phiên Chợ Dân Sinh Số 4.0 Thôn Dục Tú',
                'content' => 'Đầy đủ thực phẩm tươi sống, rau củ quả, nông sản sạch & đồ ăn sáng thanh toán quét mã QR.',
                'color' => '#f59e0b'
            ]
        ];

        if ($ducTu) {
            DB::table('eateries')->where('id', $ducTu->id)->update([
                'name' => 'Chợ Dục Tú',
                'address' => 'Thôn Dục Tú, Xã Dục Tú, Đông Anh, Hà Nội, Việt Nam',
                'price_range' => '10.000đ - 250.000đ',
                'announcements' => json_encode($announcements, JSON_UNESCAPED_UNICODE),
                'status' => 'active'
            ]);
            $eateryId = $ducTu->id;
        } else {
            $eateryId = DB::table('eateries')->insertGetId([
                'name' => 'Chợ Dục Tú',
                'slug' => 'cho-duc-tu-3-TD0C7',
                'category_id' => 8,
                'commune_id' => 35,
                'address' => 'Thôn Dục Tú, Xã Dục Tú, Đông Anh, Hà Nội, Việt Nam',
                'price_range' => '10.000đ - 250.000đ',
                'announcements' => json_encode($announcements, JSON_UNESCAPED_UNICODE),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Danh sách 26 Hộ Kinh Doanh / Sạp Tiểu Thương Chợ Dục Tú từ file Excel thống kê
        $stallsData = [
            [
                'seller_name' => 'Chu Văn Đức',
                'seller_phone' => '0832994678',
                'stall_name' => 'Gian hàng Thịt lợn sạch Chú Đức',
                'bank_name' => 'MBBank',
                'bank_account' => '0384297357',
                'bank_holder' => 'CHU VAN DUC',
                'origin' => 'Nhập lại',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt ba chỉ lợn tươi sạch', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Thịt ba chỉ tươi mổ trong ngày, nguồn gốc an toàn kiểm dịch.'],
                    ['name' => 'Sườn non & Nạc vai lợn tươi', 'price' => 130000, 'unit' => 'kg', 'desc' => 'Sườn non mềm mọng, nạc vai xào nấu đậm vị.']
                ]
            ],
            [
                'seller_name' => 'Đào Thị Hoa',
                'seller_phone' => '0386979179',
                'stall_name' => 'Gian hàng Thịt lợn tươi Cô Hoa',
                'bank_name' => 'VietinBank',
                'bank_account' => '0379126028',
                'bank_holder' => 'DAO THI HOA',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt lợn thăn sấn tươi', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Thịt thăn tươi mổ sáng sớm tại lò mổ đạt chuẩn ATTP.'],
                    ['name' => 'Thịt chân giò & Xương ống lợn', 'price' => 110000, 'unit' => 'kg', 'desc' => 'Chân giò béo ngậy nấu canh, xương ống hầm nước ngọt.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Duyên',
                'seller_phone' => '0987884782',
                'stall_name' => 'Gian hàng Thịt lợn sạch Cô Duyên',
                'bank_name' => 'BIDV',
                'bank_account' => '21243149195',
                'bank_holder' => 'NGUYEN THI DUYEN',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt ba chỉ quế tươi ngon', 'price' => 125000, 'unit' => 'kg', 'desc' => 'Ba chỉ quế nạc mỡ đan xen đều, thịt dẻo thơm.'],
                    ['name' => 'Thịt nạc mông / Nạc dăm lợn', 'price' => 115000, 'unit' => 'kg', 'desc' => 'Nạc mông tươi mềm làm ruốc hoặc luộc, xào.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Ninh',
                'seller_phone' => '0358102710',
                'stall_name' => 'Gian hàng Thịt lợn tươi Cô Ninh',
                'bank_name' => 'VietinBank',
                'bank_account' => '377993897',
                'bank_holder' => 'NGUYEN THI NINH',
                'origin' => 'Nhập lại lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt lợn mông sấn tươi ngon', 'price' => 115000, 'unit' => 'kg', 'desc' => 'Thịt tươi mới mỗi ngày, bảo đảm an toàn vệ sinh thực phẩm.'],
                    ['name' => 'Sườn thăn & Thịt vai giòn', 'price' => 130000, 'unit' => 'kg', 'desc' => 'Sườn thăn tươi rim chua ngọt, nạc vai giòn sần sật.']
                ]
            ],
            [
                'seller_name' => 'Phạm Thị Chung',
                'seller_phone' => '0370407997',
                'stall_name' => 'Gian hàng Thịt lợn sạch Cô Chung',
                'bank_name' => 'PGBank',
                'bank_account' => '1037040799786',
                'bank_holder' => 'PHAM THI CHUNG',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt lợn tươi mổ nóng trong ngày', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Thịt lợn nóng vừa mổ sáng, thớ thịt đỏ tươi thơm ngon.'],
                    ['name' => 'Thịt nạc vai / Ba chỉ ngon', 'price' => 125000, 'unit' => 'kg', 'desc' => 'Nạc vai mềm béo, ba chỉ luộc chấm mắm tỏi thơm lừng.']
                ]
            ],
            [
                'seller_name' => 'Phạm Ngọc Ánh',
                'seller_phone' => '0987982035',
                'stall_name' => 'Gian hàng Giò chả nóng Cô Ánh',
                'bank_name' => 'VietinBank',
                'bank_account' => '101883909141',
                'bank_holder' => 'PHAM NGOC ANH',
                'origin' => 'Lấy thịt tươi tại chợ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Giò lụa nóng thơm ngon', 'price' => 150000, 'unit' => 'kg', 'desc' => 'Giò lụa giã từ thịt nóng tươi mới, không hàn the.'],
                    ['name' => 'Chả quế nướng giòn rụm', 'price' => 140000, 'unit' => 'kg', 'desc' => 'Chả quế nướng thơm phức hương quế truyền thống.']
                ]
            ],
            [
                'seller_name' => 'Đào Thị Bích',
                'seller_phone' => '0379114184',
                'stall_name' => 'Gian hàng Giò chả truyền thống Cô Bích',
                'bank_name' => 'MBBank',
                'bank_account' => '06569868',
                'bank_holder' => 'DAO THI BICH',
                'origin' => 'Lấy thịt tươi tại chợ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Giò lụa truyền thống gia truyền', 'price' => 150000, 'unit' => 'kg', 'desc' => 'Giò lụa thơm nức hương lá chuối, vị ngọt tự nhiên.'],
                    ['name' => 'Chả mỡ nướng thơm ngon', 'price' => 140000, 'unit' => 'kg', 'desc' => 'Chả mỡ nướng vàng ruộm, béo bùi giòn rụm.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Hậu',
                'seller_phone' => '0379899175',
                'stall_name' => 'Gian hàng Thịt bò tươi Cô Hậu',
                'bank_name' => 'MBBank',
                'bank_account' => '2379899175',
                'bank_holder' => 'NGUYEN THI HAU',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt bắp bò / Thăn bò tươi sạch', 'price' => 250000, 'unit' => 'kg', 'desc' => 'Bắp hoa, bắp rùa, thăn bò tươi đỏ mọng mổ trong ngày.'],
                    ['name' => 'Gầu bò & Thịt bò xào mềm ngon', 'price' => 220000, 'unit' => 'kg', 'desc' => 'Gầu giòn ngọt béo ngậy, thịt bò xào thơm mềm.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Hưng',
                'seller_phone' => '0396782916',
                'stall_name' => 'Gian hàng Thịt chó & Ẩm thực Cô Hưng',
                'bank_name' => 'MBBank',
                'bank_account' => '',
                'bank_holder' => 'NGUYEN THI HUNG',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt chó hấp & Dồi nướng', 'price' => 120000, 'unit' => 'đĩa', 'desc' => 'Thịt chó hấp thơm phức mùi sả riềng, dồi nướng béo bùi.'],
                    ['name' => 'Rựa mận & Chân chó nấu thơm ngon', 'price' => 100000, 'unit' => 'phần', 'desc' => 'Rựa mận sền sệt đậm đà hương mắm tôm bánh đa.']
                ]
            ],
            [
                'seller_name' => 'Phạm Thị Chung (Sạp 2)',
                'seller_phone' => '0393451781',
                'stall_name' => 'Gian hàng Thịt lợn tươi ngon Cô Chung',
                'bank_name' => 'MBBank',
                'bank_account' => '0393451781',
                'bank_holder' => 'PHAM THI CHUNG',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt ba chỉ sấn giòn', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Ba chỉ sấn tươi sạch, nguồn gốc xuất xứ rõ ràng.'],
                    ['name' => 'Sườn sụn & Thịt nạc dăm lợn', 'price' => 130000, 'unit' => 'kg', 'desc' => 'Sườn sụn giòn sần sật nấu canh chua, nạc dăm nướng.']
                ]
            ],
            [
                'seller_name' => 'Đỗ Thị Linh',
                'seller_phone' => '0974100925',
                'stall_name' => 'Gian hàng Tạp hóa Bách hóa Cô Linh',
                'bank_name' => 'Techcombank',
                'bank_account' => '787519719999',
                'bank_holder' => 'DO THI LINH',
                'origin' => 'Các nhà phân phối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Nước mắm, hạt nêm, gia vị các loại', 'price' => 25000, 'unit' => 'chai', 'desc' => 'Gia vị nước chấm, mì chính, bột canh chính hãng.'],
                    ['name' => 'Dầu ăn & Đồ dùng thiết yếu gia đình', 'price' => 45000, 'unit' => 'chai', 'desc' => 'Dầu ăn tinh luyện thực vật và tạp hóa gia đình.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Thu',
                'seller_phone' => '0984475860',
                'stall_name' => 'Gian hàng Tạp hóa Bách hóa Cô Thu',
                'bank_name' => 'Techcombank',
                'bank_account' => '',
                'bank_holder' => 'NGUYEN THI THU',
                'origin' => 'Các nhà phân phối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Mì tôm, miến khô, bánh đa', 'price' => 25000, 'unit' => 'gói', 'desc' => 'Miến dong sạch, mì các loại phân phối chính hãng.'],
                    ['name' => 'Bánh kẹo & Nước ngọt giải khát', 'price' => 20000, 'unit' => 'lốc', 'desc' => 'Bánh ngọt, sữa tươi và nước giải khát tiện ích.']
                ]
            ],
            [
                'seller_name' => 'Cửa hàng Điện tử Dục Tú',
                'seller_phone' => '0944315310',
                'stall_name' => 'Gian hàng Điện tử & Sửa chữa Dục Tú',
                'bank_name' => 'VietinBank',
                'bank_account' => '108006429943',
                'bank_holder' => 'CU HANG DIEN TU',
                'origin' => 'Mua bán đồ điện tử',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Dịch vụ sửa chữa đồ điện tử gia dụng', 'price' => 50000, 'unit' => 'lần', 'desc' => 'Sửa chữa quạt điện, nồi cơm, ấm siêu tốc, thiết bị điện.'],
                    ['name' => 'Phụ kiện & Thiết bị điện tử dân dụng', 'price' => 60000, 'unit' => 'chiếc', 'desc' => 'Dây sạc, củ sạc, pin và phụ kiện điện gia đình.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Thanh',
                'seller_phone' => '0348916748',
                'stall_name' => 'Gian hàng Thịt lợn tươi sạch Cô Thanh',
                'bank_name' => 'MBBank',
                'bank_account' => '0348916748',
                'bank_holder' => 'NGUYEN THI THANH',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt ba chỉ & Nạc vai tươi sạch', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Thịt ba chỉ tươi mổ sớm trong ngày từ lò mổ uy tín.'],
                    ['name' => 'Xương cục hầm canh & Chân giò', 'price' => 90000, 'unit' => 'kg', 'desc' => 'Xương hầm ngọt nước, chân giò luộc chấm mắm tỏi.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Miên',
                'seller_phone' => '0399732974',
                'stall_name' => 'Gian hàng Xôi & Bánh quê Cô Miên',
                'bank_name' => 'MBBank',
                'bank_account' => '',
                'bank_holder' => 'NGUYEN THI MIEN',
                'origin' => 'Nhà tự nấu',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Xôi xéo, Xôi đỗ, Xôi khúc nóng hổi', 'price' => 15000, 'unit' => 'gói', 'desc' => 'Xôi nếp dẻo thơm béo ngậy mỡ hành, ruốc thịt.'],
                    ['name' => 'Bánh giò, Bánh dày giò truyền thống', 'price' => 15000, 'unit' => 'cái', 'desc' => 'Bánh giò nóng hổi nhân thịt mộc nhĩ thơm ngon.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Tân',
                'seller_phone' => '0794048313',
                'stall_name' => 'Gian hàng Bánh quê & Quà sáng Cô Tân',
                'bank_name' => 'BIDV',
                'bank_account' => '2141420539',
                'bank_holder' => 'NGUYEN THI TAN',
                'origin' => 'Nhà tự nấu',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Bánh tẻ, Bánh nếp dẻo thơm', 'price' => 10000, 'unit' => 'cái', 'desc' => 'Bánh quê truyền thống tự làm thơm ngon nóng hổi mỗi sáng.'],
                    ['name' => 'Xôi vò, Xôi lạc ăn sáng ấm nóng', 'price' => 15000, 'unit' => 'gói', 'desc' => 'Xôi hạt dẻo vàng óng, ăn kèm chả lụa hoặc vừng dừa.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Hoa (Thịt tươi)',
                'seller_phone' => '0338996018',
                'stall_name' => 'Gian hàng Thịt tươi dân sinh Cô Hoa',
                'bank_name' => 'BIDV',
                'bank_account' => '8842260542',
                'bank_holder' => 'NGUYEN THI HOA',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt nạc mông / Thịt thăn lợn tươi', 'price' => 115000, 'unit' => 'kg', 'desc' => 'Thịt lợn nạc sạch tươi ngon, mổ nóng trong ngày.'],
                    ['name' => 'Thịt dọi nướng & Sườn que', 'price' => 125000, 'unit' => 'kg', 'desc' => 'Thịt dọi mềm thơm ướp nướng, sườn que rim mặn ngọt.']
                ]
            ],
            [
                'seller_name' => 'Lê Văn Minh',
                'seller_phone' => '0989402092',
                'stall_name' => 'Gian hàng Bún tươi & Ẩm thực Bác Minh',
                'bank_name' => 'ABBANK',
                'bank_account' => '1051014113023',
                'bank_holder' => 'LE VAN MINH',
                'origin' => 'Tự nấu',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Bún riêu cua đồng đậm đà', 'price' => 25000, 'unit' => 'bát', 'desc' => 'Bún riêu cua đồng truyền thống nước dùng thanh ngọt chua dịu.'],
                    ['name' => 'Bún sợi tươi truyền thống', 'price' => 15000, 'unit' => 'kg', 'desc' => 'Bún tươi dẻo dai nguyên chất làm mới mỗi ngày.']
                ]
            ],
            [
                'seller_name' => 'Cửa hàng Tạp hóa Dục Tú',
                'seller_phone' => '0982719907',
                'stall_name' => 'Gian hàng Tạp hóa & Bách hóa Tiện ích',
                'bank_name' => 'BIDV',
                'bank_account' => '21110002053195',
                'bank_holder' => 'CU HANG TAP HOA DUC TU',
                'origin' => 'Các nhà phân phối',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Bột giặt, nước xả & Đồ tẩy rửa', 'price' => 35000, 'unit' => 'chai', 'desc' => 'Bột giặt, nước rửa chén, xà phòng chính hãng giá bình ổn.'],
                    ['name' => 'Bánh kẹo & Hàng tiêu dùng nhanh', 'price' => 20000, 'unit' => 'gói', 'desc' => 'Bánh quy, kẹo hoa quả và đồ bách hóa tổng hợp.']
                ]
            ],
            [
                'seller_name' => 'Cửa hàng Điện nước Sướng Cầm',
                'seller_phone' => '0988665544',
                'stall_name' => 'Gian hàng Điện nước Sướng Cầm',
                'bank_name' => 'MBBank',
                'bank_account' => '',
                'bank_holder' => 'DIEN NUOC SUONG CAM',
                'origin' => 'Các nhà phân phối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Ống nước & Phụ kiện nối PVC', 'price' => 20000, 'unit' => 'món', 'desc' => 'Ống nước Tiền Phong, cút nối, tê ren đầy đủ kích thước.'],
                    ['name' => 'Ổ cắm, công tắc, bóng đèn LED', 'price' => 35000, 'unit' => 'cái', 'desc' => 'Thiết bị điện chiếu sáng gia đình bền đẹp tiết kiệm điện.']
                ]
            ],
            [
                'seller_name' => 'Cửa hàng Hàng xén Dục Tú',
                'seller_phone' => '0977665544',
                'stall_name' => 'Gian hàng Hàng xén & Tạp phẩm Dục Tú',
                'bank_name' => 'MBBank',
                'bank_account' => '',
                'bank_holder' => 'HANG XEN DUC TU',
                'origin' => 'Chợ Từ Sơn',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Kim chỉ, kẹp tóc, phụ kiện gia dụng nhỏ', 'price' => 10000, 'unit' => 'món', 'desc' => 'Đồ dùng may vá gia đình, phụ kiện làm đẹp tiện dụng.'],
                    ['name' => 'Đồ nhựa gia dụng & Túi bóng các loại', 'price' => 20000, 'unit' => 'kg', 'desc' => 'Rổ rá nhựa, thau chậu và túi bao gói sinh hoạt.']
                ]
            ],
            [
                'seller_name' => 'Đỗ Xuân Quý',
                'seller_phone' => '0983328983',
                'stall_name' => 'Gian hàng Giò chả truyền thống Chú Quý',
                'bank_name' => 'BIDV',
                'bank_account' => '8801351367',
                'bank_holder' => 'DO XUAN QUY',
                'origin' => 'Tự làm',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Giò lụa nóng giòn nguyên chất', 'price' => 150000, 'unit' => 'kg', 'desc' => 'Giò lụa gia truyền tự làm từ thịt heo nóng tươi mỗi sáng.'],
                    ['name' => 'Giò tai & Chả quế thơm lừng', 'price' => 140000, 'unit' => 'kg', 'desc' => 'Giò tai sần sật, chả nướng vàng ươm chuẩn vị.']
                ]
            ],
            [
                'seller_name' => 'Trần Văn Trung',
                'seller_phone' => '0987785821',
                'stall_name' => 'Gian hàng Hoa quả tươi Anh Trung',
                'bank_name' => 'MBBank',
                'bank_account' => '',
                'bank_holder' => 'TRAN VAN TRUNG',
                'origin' => 'Chợ đầu mối',
                'has_smart_phone' => true,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Dưa hấu ngọt lịm mọng nước', 'price' => 25000, 'unit' => 'kg', 'desc' => 'Dưa hấu Long An ngọt mát tươi ngon mỗi ngày.'],
                    ['name' => 'Thanh long ruột đỏ & Cam sành', 'price' => 30000, 'unit' => 'kg', 'desc' => 'Hoa quả tuyển chọn tươi mới nhập chợ đầu mối.']
                ]
            ],
            [
                'seller_name' => 'Phạm Thị Nhàn',
                'seller_phone' => '0338291962',
                'stall_name' => 'Gian hàng Hoa quả bốn mùa Cô Nhàn',
                'bank_name' => 'BIDV',
                'bank_account' => '21110002053195',
                'bank_holder' => 'PHAM THI NHAN',
                'origin' => 'Các nhà phân phối',
                'has_smart_phone' => false,
                'has_attp' => false,
                'products' => [
                    ['name' => 'Xoài cát / Cam sành vắt nước', 'price' => 35000, 'unit' => 'kg', 'desc' => 'Cam sành ngọt mọng nước, xoài cát chín thơm ngon.'],
                    ['name' => 'Chuối tiêu hồng, Táo ta giòn ngọt', 'price' => 20000, 'unit' => 'kg', 'desc' => 'Chuối tiêu chín tự nhiên, táo giòn ngọt giải nhiệt.']
                ]
            ],
            [
                'seller_name' => 'Đỗ Thị Dung',
                'seller_phone' => '0384166360',
                'stall_name' => 'Gian hàng Thịt gà ta sạch Cô Dung',
                'bank_name' => 'MBBank',
                'bank_account' => '0384166360',
                'bank_holder' => 'DO THI DUNG',
                'origin' => 'Chợ Từ Sơn',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Gà ta thả vườn nguyên con', 'price' => 120000, 'unit' => 'kg', 'desc' => 'Gà ta thịt săn chắc da vàng giòn nhập Chợ Từ Sơn.'],
                    ['name' => 'Gà mổ sẵn làm sạch & Lòng mề', 'price' => 130000, 'unit' => 'kg', 'desc' => 'Gà làm sạch sẽ tại chỗ, đầy đủ lòng mề mổ tươi.']
                ]
            ],
            [
                'seller_name' => 'Nguyễn Thị Thanh (Sạp 2)',
                'seller_phone' => '0348916749',
                'stall_name' => 'Gian hàng Thịt lợn tươi ngon Cô Thanh',
                'bank_name' => 'MBBank',
                'bank_account' => '0348916748',
                'bank_holder' => 'NGUYEN THI THANH',
                'origin' => 'Lò mổ',
                'has_smart_phone' => true,
                'has_attp' => true,
                'products' => [
                    ['name' => 'Thịt ba chỉ rút sườn tươi ngon', 'price' => 130000, 'unit' => 'kg', 'desc' => 'Thịt ba chỉ rút sườn loại 1 mổ tươi lò mổ đạt chuẩn.'],
                    ['name' => 'Móng giò & Xương sườn hầm canh', 'price' => 95000, 'unit' => 'kg', 'desc' => 'Móng giò giả cầy thơm béo, sườn hầm nước ngọt lịm.']
                ]
            ],
        ];

        // 3. Xóa các sản phẩm cũ của Chợ Dục Tú nếu có (để tránh trùng lặp)
        DB::table('ocop_products')->where('eatery_id', $eateryId)->delete();
        try {
            DB::connection('mysql_market')->table('ocop_products')->where('eatery_id', $eateryId)->delete();
        } catch (\Exception $e) {}
        
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (\Throwable $e) {}

        // 4. Tạo tài khoản Seller và thêm sản phẩm cho từng gian hàng
        foreach ($stallsData as $index => $stall) {
            $rawPhone = preg_replace('/[^0-9]/', '', $stall['seller_phone']);
            $username = $rawPhone ?: ('seller_ductu_' . Str::slug($stall['seller_name'], '_') . '_' . ($index + 1));

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
                ]);
            } else {
                $user->update([
                    'name'         => $stall['seller_name'],
                    'email'        => null,
                    'phone'        => $rawPhone,
                    'bank_account' => $stall['bank_account'] ?: $user->bank_account,
                    'bank_name'    => $stall['bank_name'] ?: $user->bank_name,
                    'role'         => 'seller',
                    'status'       => 'active',
                    'is_verified'  => 1,
                ]);
            }

            $firstInsertedProdId = null;

            // Thêm từng sản phẩm của gian hàng
            foreach ($stall['products'] as $p) {
                $addInfo = 'TT+' . Str::slug($stall['stall_name'], '+');
                $qrUrl = $stall['bank_account']
                    ? "https://img.vietqr.io/image/{$stall['bank_name']}-{$stall['bank_account']}-compact.png?accountName=" . urlencode($stall['bank_holder']) . "&addInfo={$addInfo}"
                    : null;

                $descFull = "Nguồn gốc: {$stall['origin']}. " . $p['desc'] . ($stall['bank_account'] ? " Hỗ trợ thanh toán VietQR ngân hàng {$stall['bank_name']}: {$stall['bank_account']}." : " Thanh toán tiền mặt tại sạp.") . ($stall['has_smart_phone'] ? " Có sử dụng smartphone." : "");

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
                    'star_rating'   => null,
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
            if ($firstInsertedProdId && \Illuminate\Support\Facades\Schema::hasColumn('users', 'stall_id')) {
                $user->update(['stall_id' => $firstInsertedProdId]);
            }
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $ducTu = Eatery::find(22) ?: Eatery::where('slug', 'like', 'cho-duc-tu%')->orWhere('name', 'like', '%Dục Tú%')->first();
        if ($ducTu) {
            DB::table('ocop_products')->where('eatery_id', $ducTu->id)->delete();
            try {
                DB::connection('mysql_market')->table('ocop_products')->where('eatery_id', $ducTu->id)->delete();
            } catch (\Exception $e) {}
        }
    }
};
