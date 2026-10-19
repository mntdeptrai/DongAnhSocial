<?php

namespace App\Services;

use App\Contracts\NongSanCatalogInterface;

class NongSanCatalogService implements NongSanCatalogInterface
{
    /**
     * Get the list of curated agricultural produce & heritage specialties.
     */
    public function getProducts(?string $category = 'all'): array
    {
        $all = $this->loadCuratedCatalog();

        if ($category && $category !== 'all') {
            $filtered = array_filter($all, fn($p) => ($p['category'] ?? '') === $category);
            return array_values($filtered);
        }

        return $all;
    }

    /**
     * Get the spotlight heritage product (Mâm Cỗ Cổ Loa Xưa).
     */
    public function getSpotlightProduct(): array
    {
        $products = $this->loadCuratedCatalog();
        // Mâm Cỗ Cổ Loa Xưa is index 7
        return $products[7] ?? $products[0];
    }

    /**
     * Get total count of official heritage products.
     */
    public function getTotalCount(): int
    {
        return count($this->loadCuratedCatalog());
    }

    /**
     * Get the curated gift combos & hampers.
     */
    public function getCombos(): array
    {
        return [
            [
                'id'            => 'combo-tien-vua',
                'name'          => 'Set Quà Biếu Tiến Vua Cổ Loa',
                'tag'           => 'Thượng phẩm Cung đình',
                'badge'         => '👑 Bán chạy nhất',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg',
                'description'   => 'Bộ quà biếu thượng hạng kết hợp những đặc sản dâng Vua nức tiếng đất Cổ Loa, bài trí trang trọng trong hộp quà mỹ thuật dập nổi hoa văn Trống đồng Đông Sơn.',
                'items'         => [
                    '02 Hộp Bỏng chủ Cổ Loa ngào gừng già (hộp quà 350g)',
                    '02 Gói Bún Mạch Tràng cổ truyền hút chân không (500g)',
                    '01 Hũ trám đen ngâm mắm gừng Cổ Loa gia truyền',
                    '01 Hộp trà sen Tây Hồ ướp bông thượng hạng'
                ],
                'ideal_for'     => 'Quà biếu Tết, biếu đối tác ngoại giao, lễ tri ân thượng khách',
                'contact_phone' => '0243.965.2973',
                'zalo'          => '02439652973',
            ],
            [
                'id'            => 'combo-que-huong',
                'name'          => 'Set Giỏ Quà Quê Đông Anh Mộc Mạc',
                'tag'           => 'Hồn quê đất Bắc',
                'badge'         => '🌿 Đậm chất dân dã',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/chao_se_co_loa.jpg',
                'description'   => 'Gói trọn nếp nhà quê trong chiếc giỏ mây tre đan thủ công Đông Anh, mang trọn vẹn phong vị mộc mạc, lành sạch của đồng bằng sông Hồng.',
                'items'         => [
                    '01 Mẹt Bánh đúc lạc chấm tương bần truyền thống',
                    '02 Túi Bánh đa vừng Đỗ Xá giòn rụm thơm bùi',
                    '01 Gói Bún cháy Mạch Tràng ăn vặt giòn tan',
                    '01 Chiếc Nón lá làng Đan Loan đan tay thanh tao'
                ],
                'ideal_for'     => 'Quà tặng người thân đi xa, tặng bà, tặng mẹ, quà lưu niệm du lịch',
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'combo-nong-trai-xanh',
                'name'          => 'Set Xanh Nông Nghiệp VietGAP Kim Sơn',
                'tag'           => 'Sinh thái Công nghệ cao',
                'badge'         => '🍈 Tươi mới tại vườn',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/chao_se_dong_anh_1.jpg',
                'description'   => 'Thùng quà nông sản sạch thu hoạch ngay tại vườn nông trại công nghệ cao Kim Sơn Farm Đông Anh, chuẩn kiểm định VietGAP an toàn tuyệt đối.',
                'items'         => [
                    '02 Trái Dưa lưới Huỳnh Long / Kim Cô Nương tuyển chọn (loại 1)',
                    '01 Set rau củ hữu cơ theo mùa thu hoạch sáng sớm',
                    '01 Hộp nấm ăn sinh thái giàu dinh dưỡng',
                    'Tem truy xuất nguồn gốc QR-Code từng trái'
                ],
                'ideal_for'     => 'Quà tặng sức khỏe gia đình, thăm hỏi đối tác, quà biếu xanh thanh khiết',
                'contact_phone' => '0988.123.456',
                'zalo'          => '0988123456',
            ]
        ];
    }

    /**
     * Get the 4-season agricultural harvest calendar.
     */
    public function getSeasonalCalendar(): array
    {
        return [
            [
                'season'      => 'MÙA XUÂN (Tháng 1 - 3)',
                'icon'        => '🌸',
                'title'       => 'Mùa Hội Làng & Đĩa Bún Cần Non',
                'description' => 'Mùa lễ hội đền Cổ Loa đầu năm, rau cần cọng xanh non giòn ngọt xào cùng bún Mạch Tràng nức lòng du khách thập phương.',
                'highlights'  => [
                    'Bún Mạch Tràng xào rau cần tỏi phi nóng hổi',
                    'Bỏng chủ dâng lễ Vua An Dương Vương ngày mùng 6 tháng Giêng',
                    'Rau cần, rau rút đồng nước bãi bồi sông Hồng'
                ]
            ],
            [
                'season'      => 'MÙA HẠ (Tháng 4 - 6)',
                'icon'        => '☀️',
                'title'       => 'Dưa Lưới Trĩu Quả & Bún Mát Lành',
                'description' => 'Mùa hè rực nắng trên cánh đồng Kim Sơn Farm, những trái dưa lưới ngọt mát đậm đà giải nhiệt tối đa.',
                'highlights'  => [
                    'Dưa lưới Kim Sơn Farm thu hoạch rộ vụ 1 ngọt lịm',
                    'Bún Mạch Tràng tươi mát ăn kèm canh cua rau đay cà pháo',
                    'Mít Cổ Loa chín cây thơm nức các nếp vườn xưa'
                ]
            ],
            [
                'season'      => 'MÙA THU (Tháng 7 - 9)',
                'icon'        => '🍂',
                'title'       => 'Vụ Nếp Mới & Chuối Nấu Heo May',
                'description' => 'Gió heo may chớm lạnh, nồi chuối tiêu xanh om giả cầy sôi lục bục sánh vàng trên bếp củi quê ấm áp.',
                'highlights'  => [
                    'Chuối tiêu xanh om giả cầy riềng mẻ bùi ngậy quyến rũ',
                    'Gạo nếp cái hoa vàng thuần chủng vào vụ gặt rộ',
                    'Bánh đúc lạc chấm tương bần sánh mịn ấm lòng'
                ]
            ],
            [
                'season'      => 'MÙA ĐÔNG (Tháng 10 - 12)',
                'icon'        => '❄️',
                'title'       => 'Bỏng Gừng Cay Ấm & Cỗ Tất Niên',
                'description' => 'Mùa đông giá lạnh, cối bỏng nếp ngào đường phèn thơm nồng gừng cay ấm, các gia đình quây quần bên Mâm cỗ tất niên.',
                'highlights'  => [
                    'Bỏng chủ ngào gừng già thưởng thức cùng trà mạn nóng',
                    'Mâm cỗ Cổ Loa xưa trên mâm đồng tròn sum vầy đại gia đình',
                    'Bánh chưng nếp hoa vàng Cổ Loa dẻo thơm ngày Tết'
                ]
            ]
        ];
    }

    /**
     * Internal curated catalog repository data.
     */
    private function loadCuratedCatalog(): array
    {
        return [
            [
                'id'            => 'bong-chu',
                'name'          => 'BỎNG CHỦ',
                'category'      => 'snack',
                'category_label'=> 'Đặc sản khô & Quà quê',
                'badge'         => '👑 Đặc sản tiến Vua',
                'origin'        => 'Làng cổ Cổ Loa, Đông Anh',
                'unit'          => 'Hộp quà / Túi zip 350g',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg',
                'summary'       => 'Bỏng nếp cái hoa vàng ngào gừng già thơm nức, món quà tiến Vua An Dương Vương nghìn năm lịch sử.',
                'story'         => 'Tương truyền từ thời An Dương Vương định đô xây thành Ốc, người dân Cổ Loa đã làm bỏng nếp cái hoa vàng trộn đường phèn và gừng già dâng lên Vua. Bỏng giòn xốp, thơm nồng vị gừng, ngọt thanh dịu mát đượm hồn quê xứ Cổ.',
                'tasting_notes' => ['Nếp nương hoa vàng', 'Gừng già cay ấm', 'Đặc sản dâng Vua'],
                'experience_tip'=> 'Thưởng thức kèm chén trà sen Tây Hồ nóng',
                'highlights'    => [
                    'Làm từ gạo nếp cái hoa vàng thuần chủng xứ Cổ',
                    'Ngào đường phèn và gừng cay ấm nồng tự nhiên',
                    'Đóng hộp quà biếu trang trọng, thích hợp làm quà tặng',
                ],
                'contact_phone' => '0243.965.2973',
                'zalo'          => '02439652973',
            ],
            [
                'id'            => 'bun-mach-trang',
                'name'          => 'BÚN MẠCH TRÀNG',
                'category'      => 'noodle',
                'category_label'=> 'Bún & Ẩm thực xứ Cổ',
                'badge'         => '🏺 Làng nghề di sản',
                'origin'        => 'Làng nghề truyền thống Mạch Tràng, Cổ Loa',
                'unit'          => 'Gói 500g (hút chân không) / Bún tươi theo kg',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/bun_xao_mach_trang.jpg',
                'summary'       => 'Sợi bún màu sẫm ngà tự nhiên từ gạo ủ chua dâng vua, dai thơm đặc trưng không hóa chất tẩy trắng.',
                'story'         => 'Gắn với sự tích nàng Mỵ Châu và món bún xào dâng Vua. Bún Mạch Tràng không trắng tinh như bún nơi khác mà mang sắc ngà sẫm mộc mạc do lên men gạo cổ truyền, sợi bún dẻo dai bùi béo tự nhiên.',
                'tasting_notes' => ['Lên men tự nhiên', 'Sợi sẫm dẻo bùi', 'Ủ chua cổ truyền'],
                'experience_tip'=> 'Ngon nhất khi luộc ăn trong ngày hoặc xào cần',
                'highlights'    => [
                    'Ủ chua lên men tự nhiên theo bí quyết làng nghề hàng trăm năm',
                    'Không chất bảo quản, không chất huỳnh quang tẩy trắng',
                    'Đóng gói hút chân không tiện bảo quản và gửi đi xa',
                ],
                'contact_phone' => '0988.123.456',
                'zalo'          => '0988123456',
            ],
            [
                'id'            => 'bun-xao-can',
                'name'          => 'BÚN XÀO CẦN',
                'category'      => 'noodle',
                'category_label'=> 'Bún & Ẩm thực xứ Cổ',
                'badge'         => '🔥 Món ngon nóng sốt',
                'origin'        => 'Làng Mạch Tràng, Cổ Loa, Đông Anh',
                'unit'          => 'Đĩa / Suất chế biến nóng sốt',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/bun_xao_mach_trang.jpg',
                'summary'       => 'Bún Mạch Tràng trứ danh xào rau cần xanh mướt, tỏi phi thơm lừng rắc hạt tiêu cay nồng.',
                'story'         => 'Đĩa bún xào cần tỏi là món ăn truyền thống không thể thiếu trong các dịp hội làng và bữa cơm đãi khách quý của người Mạch Tràng. Sợi bún thấm đều gia vị, rau cần giòn ngọt, dậy mùi tiêu bắc ấm nồng.',
                'tasting_notes' => ['Cần giòn ngọt', 'Tiêu bắc cay nồng', 'Tỏi phi thơm nức'],
                'experience_tip'=> 'Thưởng thức nóng hổi vừa bưng ra khỏi chảo',
                'highlights'    => [
                    'Rau cần cọng xanh non giòn ngọt xào lửa lớn',
                    'Sợi bún dai săn đượm hương tỏi phi thơm phức',
                    'Món ăn gia truyền được yêu thích hàng đầu đất Cổ Loa',
                ],
                'contact_phone' => '0988.123.456',
                'zalo'          => '0988123456',
            ],
            [
                'id'            => 'chuoi-gia-cay',
                'name'          => 'CHUỐI GIẢ CẦY',
                'category'      => 'cuisine',
                'category_label'=> 'Bún & Ẩm thực xứ Cổ',
                'badge'         => '🍲 Món ngon dân dã',
                'origin'        => 'Làng cổ Cổ Loa, Đông Anh',
                'unit'          => 'Tô / Nồi đất nóng hổi',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/chao_se_co_loa.jpg',
                'summary'       => 'Món ăn đậm đà vị riềng mẻ, chuối xanh dẻo bùi hòa quyện nước dùng vàng óng sánh mịn.',
                'story'         => 'Món chuối nấu giả cầy là nét ẩm thực dân gian độc đáo của đồng bằng Bắc Bộ được lưu giữ trọn vẹn tại các nếp nhà cổ Cổ Loa. Chuối tiêu xanh tước vỏ, om cùng riềng, mẻ, mắm tôm tạo nên hương vị bùi ngậy quyến rũ.',
                'tasting_notes' => ['Riềng mẻ đậm đà', 'Chuối bùi dẻo thơm', 'Vàng óng sánh mịn'],
                'experience_tip'=> 'Ăn kèm bún tươi hoặc cơm nóng mùa se lạnh',
                'highlights'    => [
                    'Chuối tiêu bánh tẻ om nhừ bở tơi, vị bùi ngọt đậm sâu',
                    'Nước dùng sánh vàng rực rỡ, thoang thoảng hương riềng mẻ',
                    'Ăn kèm bún tươi Mạch Tràng hoặc cơm nóng đều tuyệt hảo',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'banh-duc',
                'name'          => 'BÁNH ĐÚC LẠC',
                'category'      => 'cuisine',
                'category_label'=> 'Ẩm thực & Món ngon xứ Cổ',
                'badge'         => '🌿 Quà quê mộc mạc',
                'origin'        => 'Xã Cổ Loa, Đông Anh',
                'unit'          => 'Mẹt / Suất kèm tương bần',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/chao_se_dong_anh_1.jpg',
                'summary'       => 'Bánh đúc trắng ngần dẻo quánh, hạt lạc bùi ngậy chấm ngập bát tương bần sánh đậm.',
                'story'         => 'Bánh đúc Cổ Loa được quấy từ bột gạo tẻ ngâm nước vôi trong theo tỷ lệ vàng gia truyền, thêm lạc hoa rang giòn béo ngậy. Miếng bánh mát lịm, mềm mướt nơi đầu lưỡi là thức quà quê gợi nhớ tuổi thơ bao thế hệ.',
                'tasting_notes' => ['Bột gạo dẻo quánh', 'Lạc hoa bùi béo', 'Tương bần ngọt thơm'],
                'experience_tip'=> 'Chấm tương bần pha chút ớt tươi cay nồng',
                'highlights'    => [
                    'Bánh quấy tay truyền thống, dẻo mềm không hàn the',
                    'Lạc hoa tuyển chọn hạt mẩy, bùi béo tự nhiên',
                    'Thức quà dân dã thanh mát cho mọi lứa tuổi',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'banh-da',
                'name'          => 'BÁNH ĐA ĐỖ XÁ',
                'category'      => 'snack',
                'category_label'=> 'Đặc sản khô & Quà quê',
                'badge'         => '🌾 Giòn thơm đượm vị',
                'origin'        => 'Làng Đỗ Xá, Cổ Loa, Đông Anh',
                'unit'          => 'Tệp 5 - 10 chiếc nướng giòn',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg',
                'summary'       => 'Chiếc bánh đa quạt than hoa phồng rộp, rắc kín vừng đen vừng trắng thơm ngậy đặc trưng.',
                'story'         => 'Làng Đỗ Xá từ lâu nức tiếng với nghề tráng bánh đa thủ công. Bột gạo xay nhuyễn trộn đều vừng mè, tráng mỏng hai lần trên nồi hơi rồi phơi nắng tự nhiên. Khi quạt trên than hồng, bánh phồng xốp giòn rụm, thơm nức mũi.',
                'tasting_notes' => ['Vừng rang bùi béo', 'Giòn tan thơm lừng', 'Nướng than hoa cổ truyền'],
                'experience_tip'=> 'Ăn vặt hoặc ăn kèm các món gỏi, nộm',
                'highlights'    => [
                    'Nhiều vừng đen trắng hảo hạng, thơm ngậy tự nhiên',
                    'Nướng than hoa phồng đều, giòn tan đậm đà',
                    'Đóng túi kín gió, để lâu vẫn giữ trọn độ giòn thơm',
                ],
                'contact_phone' => '0243.965.2973',
                'zalo'          => '02439652973',
            ],
            [
                'id'            => 'bun-chay',
                'name'          => 'BÚN CHÁY MẠCH TRÀNG',
                'category'      => 'snack',
                'category_label'=> 'Đặc sản khô & Quà quê',
                'badge'         => '✨ Sản vật độc đáo',
                'origin'        => 'Làng Mạch Tràng, Cổ Loa, Đông Anh',
                'unit'          => 'Túi zip 250g - 500g',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/bun_xao_mach_trang.jpg',
                'summary'       => 'Sợi bún chua Mạch Tràng ép cháy giòn tan, tẩm ướp hành hoa, ớt cay và ruốc thịt đậm đà.',
                'story'         => 'Sáng tạo độc đáo từ sợi bún chua tiến Vua làng Mạch Tràng. Thay vì cơm cháy thông thường, bún Mạch Tràng được sấy giòn xốp rồi chiên vàng rộm, phủ lớp sốt mắm ớt hành hoa cay ngọt và ruốc thịt thượng hạng.',
                'tasting_notes' => ['Giòn rụm đậm vị', 'Sốt mắm ớt cay ngọt', 'Chà bông đậm đà'],
                'experience_tip'=> 'Món ăn vặt tuyệt vời khi xem phim, tụ họp',
                'highlights'    => [
                    'Làm từ bún gạo chua Mạch Tràng trứ danh xứ Cổ',
                    'Sốt mắm hành cay ngọt kích thích vị giác',
                    'Món quà vặt sáng tạo được giới trẻ đặc biệt yêu thích',
                ],
                'contact_phone' => '0988.123.456',
                'zalo'          => '0988123456',
            ],
            [
                'id'            => 'mam-co-co-loa',
                'name'          => 'MÂM CỖ CỔ LOA XƯA',
                'category'      => 'cuisine',
                'category_label'=> 'Đại tiệc ẩm thực Cố Đô',
                'badge'         => '👑 Di sản ẩm thực cung đình',
                'origin'        => 'Khu di tích Quốc gia đặc biệt Cổ Loa',
                'unit'          => 'Mâm đồng tròn 6 - 8 người',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg',
                'menu_image'    => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg',
                'diagram_image' => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_so_do.png',
                'summary'       => 'Mâm cỗ mâm đồng tròn truyền thống tái hiện văn hóa yến tiệc cung đình thời An Dương Vương và nếp nhà quê xứ Cổ.',
                'story'         => 'Mâm cỗ chính được bài trí cầu kỳ, trang trọng trên mâm đồng tròn truyền thống, tái hiện trọn vẹn văn hóa yến tiệc cung đình thời An Dương Vương và nếp nhà quê xứ Cổ, kết thúc bằng tuần trà mạn truyền thống thưởng thức cùng Bỏng chủ và Mít Cổ Loa.',
                'tasting_notes' => ['Mâm đồng tròn cổ truyền', 'Món ăn dâng Vua', 'Trọn vị 4 mùa'],
                'experience_tip'=> 'Đặt trước 24h để nghệ nhân chuẩn bị nguyên liệu tươi ngon nhất',
                'highlights'    => [
                    'Tái hiện đầy đủ 4 nhóm món truyền thống đất Cố đô',
                    'Trang trọng trên mâm đồng chạm trổ hoa văn cổ',
                    'Kèm theo tuần trà mạn, tráng miệng bỏng nếp ngào gừng và mít vườn',
                ],
                'menu_sections' => [
                    'I. MÓN MẶN'            => ['Gà luộc lá chanh', 'Giò Mo', 'Thịt lợn trộn thính', 'Thịt lợn đông', 'Chuối giả cầy', 'Bún xào cần', 'Trám đen om'],
                    'II. BÁNH & XÔI'        => ['Xôi trám đen', 'Bánh Dày', 'Bánh đúc lạc', 'Bánh chưng'],
                    'III. RAU & CANH'       => ['Rau theo mùa tươi ngọt', 'Canh khoai tây hầm xương'],
                    'IV. TRÁNG MIỆNG & TRÀ' => ['Bỏng chủ Cổ Loa ngào gừng', 'Mít Cổ Loa', 'Trà mạn truyền thống'],
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'kim-son-farm',
                'name'          => 'CÁC SẢN PHẨM CỦA KIM SƠN FARM',
                'category'      => 'farm',
                'category_label'=> 'Nông sản sạch VietGAP',
                'badge'         => '🍈 Nông trại công nghệ cao',
                'origin'        => 'Kim Sơn Farm, Kim Sơn, Đông Anh',
                'unit'          => 'Trái tuyển chọn (1.3 - 2kg) / Thùng quà',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/chao_se_dong_anh_1.jpg',
                'summary'       => 'Dưa lưới Huỳnh Long, dưa lê Bạch Ngọc và nông sản hữu cơ trồng trong nhà màng công nghệ cao Israel.',
                'story'         => 'Kim Sơn Farm là điểm sáng nông nghiệp sinh thái tuần hoàn công nghệ cao tại Đông Anh. Dưa lưới và rau quả được thụ phấn tự nhiên bằng ong, bón phân hữu cơ sinh học và tưới nhỏ giọt Israel, mang đến vị ngọt thanh khiết chuẩn VietGAP.',
                'tasting_notes' => ['Ngọt đậm giòn mát', 'Hương thơm nồng nàn', 'Chuẩn VietGAP quốc tế'],
                'experience_tip'=> 'Ăn trực tiếp ướp lạnh hoặc làm nước ép trái cây',
                'highlights'    => [
                    'Trồng trong nhà màng cách ly sâu bệnh, chuẩn VietGAP',
                    'Độ ngọt Brix đạt 14 - 16, giòn ngọt thơm mát tự nhiên',
                    'Đóng hộp quà biếu cao cấp hoặc gửi hàng hỏa tốc',
                ],
                'contact_phone' => '0988.123.456',
                'zalo'          => '0988123456',
            ],
            [
                'id'            => 'non-la',
                'name'          => 'NÓN LÁ ĐAN LOAN',
                'category'      => 'snack',
                'category_label'=> 'Thủ công mỹ nghệ truyền thống',
                'badge'         => '🏺 Tinh hoa làng nghề',
                'origin'        => 'Làng nón Đan Loan, Đông Anh',
                'unit'          => 'Chiếc nón lá truyền thống / Nón quai thao',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg',
                'summary'       => 'Chiếc nón lá trắng ngà đan tay 16 vành tỉ mỉ, nét đẹp duyên dáng của người phụ nữ đất kinh kỳ xưa.',
                'story'         => 'Nghề chằm nón Đan Loan có lịch sử hàng trăm năm. Mỗi chiếc nón là sự kết tinh của sự kiên nhẫn, lá lụa được ủi phẳng phiu, vành tre chuốt tròn nhẵn, từng mũi kim sợi cước đều tăm tắp tạo nên chiếc nón lá bền đẹp che mưa che nắng.',
                'tasting_notes' => ['Lá lụa trắng ngà', '16 vành tre chuốt đều', 'Đan tay thủ công tinh xảo'],
                'experience_tip'=> 'Món quà lưu niệm độc đáo kết hợp du lịch Cổ Loa',
                'highlights'    => [
                    'Lá nón xử lý thủ công, màu ngà tự nhiên bền đẹp',
                    'Đan 16 vành chuẩn mực, đường chỉ may tăm tắp',
                    'Món quà mang đậm hồn cốt văn hóa truyền thống Việt Nam',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
        ];
    }
}
