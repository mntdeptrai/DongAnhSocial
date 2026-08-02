<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('ocop_products')) {
            return;
        }

        // 1. Sao lưu bảng ocop_products cũ nếu chưa có
        if (!DB::getSchemaBuilder()->hasTable('ocop_products_backup')) {
            DB::statement("CREATE TABLE ocop_products_backup AS SELECT * FROM ocop_products");
        }

        // 2. Làm sạch bảng ocop_products để cập nhật 28 sản phẩm chuẩn nhất
        DB::table('ocop_products')->truncate();

        // 3. Danh sách 28 Sản phẩm OCOP chuẩn hóa từ bảng tài liệu
        $products = [
            // --- NĂM 2022 ---
            [
                'eatery_id' => 34,
                'stall_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
                'seller_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
                'name' => 'Đông trùng hạ thảo tươi',
                'star_rating' => '4 sao',
                'heritage_year' => '2022',
                'description' => 'Thôn Lộc Hà, xã Đông Anh; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'QĐ số 2008/QĐ-UBND ngày 7/4/2023',
            ],
            [
                'eatery_id' => 34,
                'stall_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
                'seller_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
                'name' => 'Đông trùng hạ thảo khô',
                'star_rating' => '4 sao',
                'heritage_year' => '2022',
                'description' => 'Thôn Lộc Hà, xã Đông Anh; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'QĐ số 2008/QĐ-UBND ngày 7/4/2023',
            ],
            [
                'eatery_id' => 34,
                'stall_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
                'seller_name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
                'name' => 'Đông trùng hạ thảo ký chủ nhộng tằm',
                'star_rating' => '4 sao',
                'heritage_year' => '2022',
                'description' => 'Thôn Lộc Hà, xã Đông Anh; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'QĐ số 2008/QĐ-UBND ngày 7/4/2023',
            ],
            [
                'eatery_id' => 35,
                'stall_name' => 'Hộ kinh doanh Trần Văn Tần',
                'seller_name' => 'Hộ kinh doanh Trần Văn Tần',
                'name' => 'Tượng Phật Đại Thế Chí Bồ Tát',
                'star_rating' => '4 sao',
                'heritage_year' => '2022',
                'description' => 'Thôn Thạc Quả, xã Đông Anh; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'QĐ số 2008/QĐ-UBND ngày 7/4/2023',
            ],
            [
                'eatery_id' => 35,
                'stall_name' => 'Hộ kinh doanh Trần Văn Tần',
                'seller_name' => 'Hộ kinh doanh Trần Văn Tần',
                'name' => 'Song ngư sinh tài',
                'star_rating' => '4 sao',
                'heritage_year' => '2022',
                'description' => 'Thôn Thạc Quả, xã Đông Anh; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'QĐ số 2008/QĐ-UBND ngày 7/4/2023',
            ],
            [
                'eatery_id' => 36,
                'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'seller_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'name' => 'Bánh gạo lứt',
                'star_rating' => '4 sao',
                'price' => 30000.00,
                'unit' => 'gói',
                'heritage_year' => '2022',
                'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'Số tài khoản: 125349089 tại ngân hàng ACB. Bán các siêu thị, cửa hàng tiện ích, cửa hàng tạp hóa.',
            ],
            [
                'eatery_id' => 36,
                'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'seller_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'name' => 'Bánh vừng vòng',
                'star_rating' => '3 sao',
                'price' => 30000.00,
                'unit' => 'gói',
                'heritage_year' => '2022',
                'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'Số tài khoản: 125349089 tại ngân hàng ACB. Bán các siêu thị, cửa hàng tiện ích, cửa hàng tạp hóa.',
            ],
            [
                'eatery_id' => 37,
                'stall_name' => 'Hợp tác xã dịch vụ nông nghiệp thôn Đoài',
                'seller_name' => 'Hợp tác xã dịch vụ nông nghiệp thôn Đoài',
                'name' => 'Tương Việt Hùng',
                'star_rating' => '3 sao',
                'heritage_year' => '2022',
                'description' => 'Thôn Đoài, xã Đông Anh, TP. Hà Nội; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'QĐ số 2008/QĐ-UBND ngày 7/4/2023',
            ],

            // --- NĂM 2023 ---
            [
                'eatery_id' => 38,
                'stall_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
                'seller_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
                'name' => 'Bánh xốp vừng',
                'star_rating' => '3 sao',
                'heritage_year' => '2023',
                'description' => 'xóm Đông Ngàn, xã Đông Anh; QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],
            [
                'eatery_id' => 38,
                'stall_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
                'seller_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
                'name' => 'Bánh Sampa (3 sao)',
                'star_rating' => '3 sao',
                'heritage_year' => '2023',
                'description' => 'xóm Đông Ngàn, xã Đông Anh; QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],
            [
                'eatery_id' => 38,
                'stall_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
                'seller_name' => 'HKD sản xuất và kinh doanh bánh ngọt Thùy Quyên',
                'name' => 'Bánh trứng nhện',
                'star_rating' => '3 sao',
                'heritage_year' => '2023',
                'description' => 'xóm Đông Ngàn, xã Đông Anh; QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],
            [
                'eatery_id' => 39,
                'stall_name' => 'Hộ kinh doanh Thạo Loan',
                'seller_name' => 'Hộ kinh doanh Thạo Loan',
                'name' => 'Rượu gạo nếp Long Tửu (2023)',
                'star_rating' => '3 sao',
                'heritage_year' => '2023',
                'description' => 'Thôn Xuân Canh, xã Đông Anh; QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],
            [
                'eatery_id' => 39,
                'stall_name' => 'Hộ kinh doanh Thạo Loan',
                'seller_name' => 'Hộ kinh doanh Thạo Loan',
                'name' => 'Rượu dâu Long Tửu',
                'star_rating' => '3 sao',
                'heritage_year' => '2023',
                'description' => 'Thôn Xuân Canh, xã Đông Anh; QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],

            // --- NĂM 2024 ---
            [
                'eatery_id' => 40,
                'stall_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
                'seller_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
                'name' => 'Hành lá',
                'star_rating' => '3 sao',
                'heritage_year' => '2024',
                'description' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],
            [
                'eatery_id' => 40,
                'stall_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
                'seller_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
                'name' => 'Khoai tây',
                'star_rating' => '3 sao',
                'heritage_year' => '2024',
                'description' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],
            [
                'eatery_id' => 42,
                'stall_name' => 'Hợp tác xã dịch vụ nông nghiệp kinh doanh tổng hợp Dục Tú',
                'seller_name' => 'Hợp tác xã dịch vụ nông nghiệp kinh doanh tổng hợp Dục Tú',
                'name' => 'Gạo nếp cái hoa vàng',
                'star_rating' => '3 sao',
                'heritage_year' => '2024',
                'description' => 'Thôn Dục Tú, xã Đông Anh; QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],
            [
                'eatery_id' => 36,
                'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'seller_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'name' => 'Bánh Sampa (4 sao)',
                'star_rating' => '4 sao',
                'price' => 30000.00,
                'unit' => 'gói',
                'heritage_year' => '2024',
                'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'Số tài khoản: 125349089 tại ngân hàng ACB. Bán các siêu thị, cửa hàng tiện ích, cửa hàng tạp hóa.',
            ],
            [
                'eatery_id' => 36,
                'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'seller_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'name' => 'Bánh nhện vừng',
                'star_rating' => '4 sao',
                'price' => 30000.00,
                'unit' => 'gói',
                'heritage_year' => '2024',
                'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'Số tài khoản: 125349089 tại ngân hàng ACB. Bán các siêu thị, cửa hàng tiện ích, cửa hàng tạp hóa.',
            ],
            [
                'eatery_id' => 36,
                'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'seller_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'name' => 'Bánh Vừng Dừa (Coconut Biscuit)',
                'star_rating' => '4 sao',
                'price' => 30000.00,
                'unit' => 'gói',
                'heritage_year' => '2024',
                'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'Số tài khoản: 125349089 tại ngân hàng ACB. Bán các siêu thị, cửa hàng tiện ích, cửa hàng tạp hóa.',
            ],
            [
                'eatery_id' => 39,
                'stall_name' => 'Hộ kinh doanh Thạo Loan',
                'seller_name' => 'Hộ kinh doanh Thạo Loan',
                'name' => 'Rượu mơ Long Tửu',
                'star_rating' => '3 sao',
                'heritage_year' => '2024',
                'description' => 'Thôn Xuân Canh, xã Đông Anh; QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],
            [
                'eatery_id' => 39,
                'stall_name' => 'Hộ kinh doanh Thạo Loan',
                'seller_name' => 'Hộ kinh doanh Thạo Loan',
                'name' => 'Rượu Bạch cúc Long Tửu',
                'star_rating' => '3 sao',
                'heritage_year' => '2024',
                'description' => 'Thôn Xuân Canh, xã Đông Anh; QĐ số 560/QĐ-UBND ngày 27/1/2021',
                'story' => 'QĐ số 560/QĐ-UBND ngày 27/1/2021',
            ],

            // --- NĂM 2025 ---
            [
                'eatery_id' => 41,
                'stall_name' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
                'seller_name' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
                'name' => 'Giò lụa',
                'star_rating' => '3 sao',
                'heritage_year' => '2025',
                'description' => 'Xóm Thượng, xã Đông Anh',
                'story' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
            ],
            [
                'eatery_id' => 41,
                'stall_name' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
                'seller_name' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
                'name' => 'Chả lụa',
                'star_rating' => '3 sao',
                'heritage_year' => '2025',
                'description' => 'Xóm Thượng, xã Đông Anh',
                'story' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
            ],
            [
                'eatery_id' => 36,
                'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'seller_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'name' => 'Bánh vừng Cookies',
                'star_rating' => '3 sao',
                'price' => 30000.00,
                'unit' => 'gói',
                'heritage_year' => '2025',
                'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'Số tài khoản: 125349089 tại ngân hàng ACB. Bán các siêu thị, cửa hàng tiện ích, cửa hàng tạp hóa.',
            ],
            [
                'eatery_id' => 36,
                'stall_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'seller_name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'name' => 'Bánh gạo thơm',
                'star_rating' => '3 sao',
                'price' => 30000.00,
                'unit' => 'gói',
                'heritage_year' => '2025',
                'description' => 'Thôn Đông Ngàn, xã Đông Anh, TP. Hà Nội; QĐ số 2008/QĐ-UBND ngày 7/4/2023',
                'story' => 'Số tài khoản: 125349089 tại ngân hàng ACB. Bán các siêu thị, cửa hàng tiện ích, cửa hàng tạp hóa.',
            ],
            [
                'eatery_id' => 39,
                'stall_name' => 'Hộ kinh doanh Thạo Loan',
                'seller_name' => 'Hộ kinh doanh Thạo Loan',
                'name' => 'Rượu gạo nếp Long Tửu (2025)',
                'star_rating' => '3 sao',
                'heritage_year' => '2025',
                'description' => 'Thôn Xuân Canh, xã Đông Anh; QĐ số 7059/QĐ-UBND ngày 13/12/2019',
                'story' => 'QĐ số 7059/QĐ-UBND ngày 13/12/2019',
            ],
            [
                'eatery_id' => 40,
                'stall_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
                'seller_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
                'name' => 'Bí đỏ',
                'star_rating' => '3 sao',
                'heritage_year' => '2025',
                'description' => 'Trung tâm Cổ Loa, xã Đông Anh; QĐ số 1441/QĐ-UBND ngày 28/4/2022',
                'story' => 'QĐ số 1441/QĐ-UBND ngày 28/4/2022',
            ],
            [
                'eatery_id' => 40,
                'stall_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
                'seller_name' => 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa',
                'name' => 'Lạc nhân',
                'star_rating' => '3 sao',
                'heritage_year' => '2025',
                'description' => 'Trung tâm Cổ Loa, xã Đông Anh; QĐ số 1441/QĐ-UBND ngày 28/4/2022',
                'story' => 'QĐ số 1441/QĐ-UBND ngày 28/4/2022',
            ],
        ];

        foreach ($products as $item) {
            DB::table('ocop_products')->insert(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        if (DB::getSchemaBuilder()->hasTable('ocop_products_backup')) {
            DB::statement("DELETE FROM ocop_products");
            DB::statement("INSERT INTO ocop_products SELECT * FROM ocop_products_backup");
            DB::statement("DROP TABLE ocop_products_backup");
        }
    }
};
