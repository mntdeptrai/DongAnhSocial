<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NongSanDongAnhController extends Controller
{
    /**
     * Display the official catalog of Dong Anh agricultural produce and local specialties.
     */
    public function index(Request $request)
    {
        $products = $this->getProducts();

        // Optional filter by category
        $currentCat = $request->get('cat', 'all');
        if ($currentCat !== 'all') {
            $products = array_filter($products, function ($p) use ($currentCat) {
                return $p['category'] === $currentCat;
            });
        }

        // Spotlight featured product
        $spotlightProduct = $this->getProducts()[7] ?? $this->getProducts()[0]; // Mâm Cỗ Cổ Loa Xưa

        return view('nong-san.index', [
            'products'         => array_values($products),
            'spotlightProduct' => $spotlightProduct,
            'currentCat'       => $currentCat,
            'totalCount'       => count($this->getProducts()),
            'combos'           => $this->getCombos(),
            'seasons'          => $this->getSeasonalCalendar(),
        ]);
    }

    /**
     * Handle quick order / callback inquiry from visitor.
     */
    public function submitOrder(Request $request)
    {
        $validated = $request->validate([
            'product_name'  => 'required|string|max:255',
            'customer_name' => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'quantity'      => 'nullable|string|max:50',
            'note'          => 'nullable|string|max:500',
        ]);

        \Log::info('Nong San Inquiry Received', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn quý khách! Yêu cầu đặt mua "' . $validated['product_name'] . '" đã được tiếp nhận. Đơn vị phụ trách sẽ liên hệ lại qua SĐT ' . $validated['phone'] . ' trong thời gian sớm nhất.'
        ]);
    }

    /**
     * Returns curated list of 10 iconic Dong Anh agricultural and local culinary products.
     */
    private function getProducts(): array
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
                'name'          => 'BÁNH ĐÚC',
                'category'      => 'cuisine',
                'category_label'=> 'Bún & Ẩm thực xứ Cổ',
                'badge'         => '🌿 Quà quê truyền thống',
                'origin'        => 'Xứ Cổ Loa, Đông Anh',
                'unit'          => 'Đĩa / Mẹt lá chuối kèm chén tương bần',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/gieng_ngoc_co_loa_ho.jpg',
                'summary'       => 'Bánh đúc dẻo mềm giòn hạt lạc béo ngậy, chấm tương nếp truyền thống đậm đà khó quên.',
                'story'         => 'Chiếc bánh đúc dẻo mịn làm từ gạo tẻ ngâm nước vôi trong, nấu quấy đều tay trên bếp củi, rắc lạc béo bùi. Ăn kèm bát tương nếp cổ truyền thơm phức mang lại trọn vẹn phong vị làng quê nghìn năm.',
                'tasting_notes' => ['Dẻo mát mịn màng', 'Lạc béo bùi thơm', 'Tương nếp dậy mùi'],
                'experience_tip'=> 'Chấm đẫm bát tương nếp cổ truyền Cổ Loa',
                'highlights'    => [
                    'Nấu quấy từ gạo tẻ mới và nước vôi trong theo bí quyết xưa',
                    'Hạt lạc luộc bở bùi điểm xuyết trong thớ bánh mịn màng',
                    'Chén tương nếp thơm lừng dậy mùi đậu tương lên men',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'banh-da',
                'name'          => 'BÁNH ĐA',
                'category'      => 'snack',
                'category_label'=> 'Đặc sản khô & Quà quê',
                'badge'         => '🍘 Giòn rụm béo bùi',
                'origin'        => 'Đông Anh, Hà Nội',
                'unit'          => 'Chục chiếc / Túi bảo quản kín',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg',
                'summary'       => 'Bánh đa quạt than hoa phồng rộp, vừng đen béo ngậy, giòn tan đậm đà hương gạo quê.',
                'story'         => 'Bánh đa vừng nướng than hoa là món quà ăn vặt dân dã gắn liền với ký ức bao thế hệ. Bánh tráng dày dặn, rắc kín vừng đen, nướng trên than hoa đỏ rực cho lớp vỏ phồng xốp giòn rụm.',
                'tasting_notes' => ['Quạt than hoa giòn tan', 'Vừng đen bùi béo', 'Đượm hương gạo quê'],
                'experience_tip'=> 'Ăn vặt hoặc kèm các món nộm, gỏi xứ Cổ',
                'highlights'    => [
                    'Tráng từ bột gạo xay nhuyễn kết hợp vừng đen nguyên chất',
                    'Nướng than hoa thủ công tạo độ phồng xốp vàng ruộm',
                    'Món ăn kèm hoàn hảo cho các bữa tiệc và lai rai đàm đạo',
                ],
                'contact_phone' => '0243.965.2973',
                'zalo'          => '02439652973',
            ],
            [
                'id'            => 'bun-chay',
                'name'          => 'BÚN CHÁY',
                'category'      => 'noodle',
                'category_label'=> 'Bún & Ẩm thực xứ Cổ',
                'badge'         => '⭐ Đặc sản độc lạ',
                'origin'        => 'Xưởng bún Mạch Tràng, Cổ Loa',
                'unit'          => 'Đĩa / Túi thành phẩm khô',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/bun_xao_mach_trang.jpg',
                'summary'       => 'Phần bún cháy vàng ươm dưới đáy chảo khi chế biến bún tươi, giòn rụm thơm phức vị gạo nướng.',
                'story'         => 'Bún cháy là "món ngon độc lạ" gắn liền với những người thợ làng bún Mạch Tràng. Lớp sợi bún tiếp xúc nhiệt đáy chảo tạo thành tảng cháy vàng giòn, chấm mắm chanh ớt hay ăn cùng nước canh đều cuốn hút kỳ lạ.',
                'tasting_notes' => ['Vàng ruộm giòn tan', 'Hương khói xém độc lạ', 'Bí truyền làng nghề'],
                'experience_tip'=> 'Chấm mắm chanh ớt tỏi hoặc ăn cùng nước dùng',
                'highlights'    => [
                    'Giòn rụm, vàng ươm tự nhiên từ lửa chảo làng nghề',
                    'Mùi thơm cháy xém đặc trưng từ bột gạo lên men',
                    'Số lượng có hạn, thường phải đặt trước khi mẻ bún ra lò',
                ],
                'contact_phone' => '0988.123.456',
                'zalo'          => '0988123456',
            ],
            [
                'id'            => 'mam-co-co-loa',
                'name'          => 'MÂM CỖ CỔ LOA XƯA',
                'category'      => 'cuisine',
                'category_label'=> 'Bún & Ẩm thực xứ Cổ',
                'badge'         => '👑 Di sản ẩm thực Cố Đô',
                'origin'        => 'Không gian nhà cổ Cổ Loa, Đông Anh',
                'unit'          => 'Mâm tiệc 4 - 6 người (Đặt trước)',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg',
                'menu_image'    => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg',
                'diagram_image' => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_so_do.png',
                'summary'       => 'Bài trí chuẩn mực: Gà luộc lá chanh, Giò Mo, Thịt lợn trộn thính, Thịt lợn đông, Chuối giả cầy, Bún xào cần, Trám đen om, Xôi trám đen, Bánh Dày, Bánh đúc lạc, Bánh chưng, Canh khoai tây & Thưởng trà Bỏng chủ, Mít Cổ Loa.',
                'story'         => '“Một mâm cỗ – Một miền ký ức – Một Cổ Loa nghìn năm văn hiến”. Mâm cỗ chính được bài trí trang trọng trên mâm đồng tròn truyền thống, tái hiện văn hóa ẩm thực yến tiệc cung đình thời An Dương Vương và nếp nhà quê nghìn năm, kết thúc bằng tuần trà mạn thưởng thức cùng Bỏng chủ ngào gừng và Mít Cổ Loa ngọt đậm.',
                'tasting_notes' => ['Bản thực đơn cổ', 'Trống đồng Đông Sơn', 'Trà mạn & Bỏng chủ'],
                'experience_tip'=> 'Thưởng thức tại không gian sân vườn nhà cổ Cổ Loa thanh tịnh',
                'menu_sections' => [
                    'I. MÓN MẶN' => ['Gà luộc lá chanh', 'Giò Mo', 'Thịt lợn trộn thính', 'Thịt lợn đông', 'Chuối giả cầy', 'Bún xào cần', 'Trám đen om'],
                    'II. BÁNH & XÔI' => ['Xôi trám đen', 'Bánh Dày', 'Bánh đúc lạc', 'Bánh chưng'],
                    'III. RAU & CANH' => ['Rau theo mùa', 'Canh khoai tây'],
                    'IV. TRÁNG MIỆNG & TRÀ' => ['Bỏng chủ Cổ Loa', 'Mít Cổ Loa', 'Trà mạn truyền thống'],
                ],
                'highlights'    => [
                    'I. MÓN MẶN: Gà luộc lá chanh, Giò Mo, Thịt lợn trộn thính, Thịt lợn đông, Chuối giả cầy, Bún xào cần, Trám đen om',
                    'II. BÁNH & XÔI: Xôi trám đen dẻo bùi, Bánh Dày, Bánh đúc lạc, Bánh chưng truyền thống',
                    'III. RAU & CANH: Rau theo mùa thanh mát, Canh khoai tây ngọt nước hầm',
                    'IV. TRÁNG MIỆNG & TRÀ: Bỏng chủ Cổ Loa ngào gừng cay, Mít Cổ Loa ngọt đậm, Trà mạn truyền thống',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'kim-son-farm',
                'name'          => 'CÁC SẢN PHẨM CỦA KIM SƠN FARM',
                'category'      => 'farm',
                'category_label'=> 'Nông sản tươi & Trang trại',
                'badge'         => '🌿 Canh tác hữu cơ VietGAP',
                'origin'        => 'Nông trại sinh thái Kim Sơn, Thụy Lâm, Đông Anh',
                'unit'          => 'Quả dưa lưới (1.4 - 1.8 kg) / Rau củ VietGAP',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/flycam_toan_canh_den_thuong_co_loa.jpg',
                'summary'       => 'Dưa lưới giống Nhật Fuji & Huỳnh Long và rau củ trồng nhà màng công nghệ cao, giòn ngọt thanh mát.',
                'story'         => 'Được chăm sóc theo quy trình tưới nhỏ giọt Israel và thụ phấn sinh học tự nhiên, các nông sản Kim Sơn Farm đạt độ ngọt chuẩn BRIX cao, canh tác an toàn sinh học, là niềm tự hào nông nghiệp công nghệ cao Đông Anh.',
                'tasting_notes' => ['Độ ngọt 14° Brix', 'Nhà màng công nghệ cao', 'Giòn tan mọng nước'],
                'experience_tip'=> 'Bổ ướp lạnh 15 phút trước khi thưởng thức',
                'highlights'    => [
                    '100% Trồng trong nhà màng cách ly sâu bệnh, chuẩn VietGAP',
                    'Độ ngọt đậm đà, thịt dưa giòn thơm mọng nước',
                    'Khách có thể đặt hái tươi tại vườn hoặc ship tận nhà',
                ],
                'contact_phone' => '0912.456.789',
                'zalo'          => '0912456789',
            ],
            [
                'id'            => 'non-la',
                'name'          => 'NÓN LÁ VẼ TAY',
                'category'      => 'snack',
                'category_label'=> 'Đặc sản khô & Quà quê',
                'badge'         => '🎋 Thủ công mỹ nghệ',
                'origin'        => 'Làng nghề đan lát Cổ Loa, Đông Anh',
                'unit'          => 'Chiếc',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/tuong_no_than_au_lac.jpg',
                'summary'       => 'Nón lá vẽ tay chằm tỉ mỉ, mỗi chiếc là một tác phẩm thủ công vẽ tranh phong cảnh làng quê Cổ Loa độc bản.',
                'story'         => 'Từng vành nón uốn cong thanh thoát, lớp lá nón trắng muốt được nghệ nhân khéo léo vẽ tranh phong cảnh làng nghề, đền Cổ Loa, cánh đồng lúa vàng. Nón lá vẽ tay vừa che mưa che nắng khi đi tour đồng quê, vừa là món quà lưu niệm nghệ thuật đậm chất Việt Nam.',
                'tasting_notes' => ['Vẽ tay nghệ nhân', 'Tranh phong cảnh độc bản', 'Quà lưu niệm di sản'],
                'experience_tip'=> 'Đạo cụ chụp ảnh check-in Cổ Loa cực đẹp và độc đáo',
                'highlights'    => [
                    'Chằm tay thủ công từ lá nón tuyển chọn, mỗi chiếc vẽ tranh độc bản',
                    'Vành nón dẻo dai bền chắc, hình vẽ sắc nét bền màu',
                    'Quà lưu niệm nghệ thuật ý nghĩa, lý tưởng cho du khách',
                ],
                'contact_phone' => '0243.965.2973',
                'zalo'          => '02439652973',
            ],
            [
                'id'            => 'tram-den',
                'name'          => 'TRÁM ĐEN CỔ LOA',
                'category'      => 'farm',
                'category_label'=> 'Nông sản tươi & Trang trại',
                'badge'         => '🫒 Đặc sản rừng Cổ Loa',
                'origin'        => 'Vườn cổ thụ Cổ Loa, Đông Anh',
                'unit'          => 'Túi 500g / Kg tươi theo mùa',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg',
                'summary'       => 'Trám đen hái từ cây cổ thụ trăm năm, om với mắm muối thơm bùi ngậy, nguyên liệu đặc trưng của mâm cỗ Cổ Loa xưa.',
                'story'         => 'Những cây trám đen cổ thụ hàng trăm năm tuổi gắn bó với mảnh đất Cổ Loa. Quả trám om nước sôi rồi trộn muối vừng, hoặc om mắm, tạo vị béo bùi đặc trưng không loại quả nào thay thế được. Trám đen cũng là nguyên liệu chính nấu xôi trám đen – món xôi khai vị truyền thống trong mâm cỗ Cổ Loa xưa.',
                'tasting_notes' => ['Béo bùi cổ thụ', 'Om mắm truyền thống', 'Đặc sản mùa thu'],
                'experience_tip'=> 'Om chín tới, ăn kèm xôi nếp hoa vàng hoặc cơm nóng',
                'highlights'    => [
                    'Hái từ cây trám cổ thụ trăm năm tuổi tại vườn Cổ Loa',
                    'Om nước sôi hoặc om mắm muối vừng thơm bùi ngậy',
                    'Nguyên liệu chính nấu xôi trám đen mâm cỗ cổ truyền',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'mit-co-loa',
                'name'          => 'MÍT CỔ LOA',
                'category'      => 'farm',
                'category_label'=> 'Nông sản tươi & Trang trại',
                'badge'         => '🍈 Trái cây di sản',
                'origin'        => 'Vườn cổ Cổ Loa, Đông Anh',
                'unit'          => 'Quả / Múi tươi theo kg',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/gieng_ngoc_co_loa_ho.jpg',
                'summary'       => 'Mít Cổ Loa giống bản địa, múi dày vàng ươm mật ngọt đậm, thơm nức mùi trái cây nhiệt đới đặc trưng xứ đất cổ.',
                'story'         => 'Vườn mít Cổ Loa giống cổ thuần chủng được giữ gìn qua nhiều thế hệ. Múi mít dày căng, vàng ươm mật ngọt, không hắc như giống mít nơi khác. Mít Cổ Loa thường được dùng tráng miệng trong mâm cỗ cổ truyền kết hợp cùng Bỏng chủ và trà mạn sau bữa tiệc.',
                'tasting_notes' => ['Múi dày vàng ươm', 'Mật ngọt thơm đậm', 'Giống bản địa cổ'],
                'experience_tip'=> 'Thưởng thức tráng miệng cùng Bỏng chủ sau mâm cỗ',
                'highlights'    => [
                    'Giống mít cổ thuần chủng, trồng lâu năm tại vườn Cổ Loa',
                    'Múi dày căng, ít xơ, mật ngọt đậm đà đặc trưng bản địa',
                    'Món tráng miệng truyền thống không thể thiếu trong mâm cỗ',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'thit-lon-dong',
                'name'          => 'THỊT LỢN ĐÔNG',
                'category'      => 'cuisine',
                'category_label'=> 'Ẩm thực & Mâm cơm xứ Cổ',
                'badge'         => '❄️ Món tết truyền thống',
                'origin'        => 'Nếp nhà cổ Cổ Loa, Đông Anh',
                'unit'          => 'Bát / Khay đông lạnh sẵn theo kg',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/chao_se_co_loa.jpg',
                'summary'       => 'Thịt lợn ba chỉ luộc chín, ướp nước mắm ngon thái mỏng đông lạnh, giữ nguyên vị béo giòn sần sật đặc trưng ngày đông.',
                'story'         => 'Thịt lợn đông là món ăn truyền thống của người đồng bằng Bắc Bộ mỗi dịp Tết đến xuân về. Thịt ba chỉ hay thịt mông luộc chín tới, xếp khay rồi để đông tự nhiên trong tiết trời lạnh giá. Lát thịt trong suốt, chấm mắm gừng hay dưa hành cay nồng – đây là hương vị Tết không thể thiếu của đất Cổ Loa.',
                'tasting_notes' => ['Béo giòn sần sật', 'Đông tự nhiên giữ vị', 'Chấm mắm gừng cay'],
                'experience_tip'=> 'Ăn kèm dưa hành chua ngọt và cơm nếp ngày Tết',
                'highlights'    => [
                    'Thịt ba chỉ / thịt mông lợn bản địa luộc chín đông tự nhiên',
                    'Lát thịt trong mịn, giữ vị béo giòn đặc trưng tiết đông',
                    'Món không thể thiếu trong mâm cỗ Tết Cổ Loa truyền thống',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'thit-lon-tron-thinh',
                'name'          => 'THỊT LỢN TRỘN THÍNH',
                'category'      => 'cuisine',
                'category_label'=> 'Ẩm thực & Mâm cơm xứ Cổ',
                'badge'         => '🌾 Món mặn gia truyền',
                'origin'        => 'Làng cổ Cổ Loa, Đông Anh',
                'unit'          => 'Đĩa / Phần ăn chế biến sẵn',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg',
                'summary'       => 'Thịt lợn thái lát mỏng trộn thính gạo rang xay, ngậy thơm cay nồng vị tiêu bắc, đậm đà hương gia vị truyền thống xứ Cổ.',
                'story'         => 'Thính gạo được rang vàng ươm giã nhuyễn, trộn cùng lát thịt lợn chín mềm, thêm chút tiêu bắc, ớt và gừng giã. Món ăn mang vị ngậy bùi đặc trưng của thính gạo rang, cay nhẹ tiêu ớt, là món mặn không thể thiếu trong mâm cỗ truyền thống Cổ Loa.',
                'tasting_notes' => ['Thính gạo rang thơm', 'Ngậy bùi tiêu bắc', 'Đậm đà gia truyền'],
                'experience_tip'=> 'Ăn kèm rau thơm và cơm nóng hay bún tươi Mạch Tràng',
                'highlights'    => [
                    'Thính gạo rang vàng giã nhuyễn ướp cùng thịt lợn tươi',
                    'Vị ngậy bùi ấm nồng hương tiêu bắc và ớt tươi',
                    'Món mặn gia truyền đặc trưng mâm cỗ làng Cổ Loa xưa',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'xoi-tram-den',
                'name'          => 'XÔI TRÁM ĐEN',
                'category'      => 'cuisine',
                'category_label'=> 'Ẩm thực & Mâm cơm xứ Cổ',
                'badge'         => '🍚 Xôi khai vị mâm cỗ',
                'origin'        => 'Làng cổ Cổ Loa, Đông Anh',
                'unit'          => 'Đĩa xôi / Phần ăn nóng hổi',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/bun_xao_mach_trang.jpg',
                'summary'       => 'Xôi nếp hoa vàng đồ cùng trám đen om mắm, hạt nếp dẻo thơm quyện vị bùi béo của trám – món khai vị mâm cỗ Cổ Loa nghìn năm.',
                'story'         => 'Xôi trám đen là món khai vị truyền thống mở đầu mâm cỗ Cổ Loa từ bao đời. Gạo nếp cái hoa vàng đồ chín dẻo quyện, trộn cùng trám đen om mắm bùi béo thơm nức. Màu tím đen óng ánh của trám in trên nền xôi trắng ngà tạo nên vẻ đẹp đặc trưng, hương vị đậm đà không thể trộn lẫn.',
                'tasting_notes' => ['Nếp hoa vàng dẻo thơm', 'Trám om mắm bùi béo', 'Khai vị mâm cỗ'],
                'experience_tip'=> 'Thưởng thức nóng hổi ngay khi vừa đồ ra, ăn kèm trám om mắm',
                'highlights'    => [
                    'Nếp cái hoa vàng thuần chủng đồ chín dẻo thơm đặc trưng',
                    'Trám đen cổ thụ Cổ Loa om mắm bùi béo ánh tím hấp dẫn',
                    'Món khai vị truyền thống không thể thiếu trong mâm cỗ cổ',
                ],
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
        ];
    }

    /**
     * Curated gift sets and signature specialty combos.
     */
    private function getCombos(): array
    {
        return [
            [
                'id'            => 'combo-tien-vua',
                'name'          => 'GIỎ QUÀ TIẾN VUA XỨ CỔ',
                'tag'           => '👑 Quà Biếu Cao Cấp',
                'badge'         => 'Bán chạy nhất',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg',
                'description'   => 'Bộ quà biếu thượng hạng mang đậm phong vị hoàng gia Âu Lạc, đóng gói hộp mây tre đan hoặc hộp quà trang trọng gắn nơ đỏ truyền thống.',
                'items'         => [
                    '02 Hộp Bỏng chủ nếp hoa vàng gừng cay (350g/hộp)',
                    '02 Gói Bún Mạch Tràng ủ chua hút chân không (500g/gói)',
                    '01 Túi Bánh đa vừng nướng than hoa (10 chiếc)',
                    '01 Nón lá lưu niệm thủ công',
                ],
                'ideal_for'     => 'Biếu tặng đối tác ngoại giao, kiều bào & tri ân người thân',
                'contact_phone' => '0243.965.2973',
                'zalo'          => '02439652973',
            ],
            [
                'id'            => 'combo-mam-co',
                'name'          => 'SET MÂM CỖ ĐOÀN VIÊN 6 MÓN CỔ TRUYỀN',
                'tag'           => '🏮 Đầy Đủ Hương Vị',
                'badge'         => 'Sum họp gia đình',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_so_do.png',
                'menu_image'    => 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg',
                'description'   => 'Trọn bộ nguyên liệu sơ chế và chế biến sẵn các món cổ truyền đất Cổ Loa bài trí mâm đồng tròn dành cho tiệc gia đình 4 - 6 người.',
                'items'         => [
                    'Cháo se Cổ Loa quấy nước hầm xương ngọt thanh',
                    'Gà đồi hấp lá chanh da vàng giòn ngọt thịt',
                    'Bún xào cần tỏi phi thơm lừng',
                    'Chuối giả cầy riềng mẻ sánh mịn',
                    'Bánh đúc dẻo mềm chấm tương nếp cổ truyền',
                    'Bỏng chủ tráng miệng thơm ấm nồng vị gừng',
                ],
                'ideal_for'     => 'Bữa cơm sum họp cuối tuần, giỗ chạp, tiệc đãi khách quý',
                'contact_phone' => '0983.882.119',
                'zalo'          => '0983882119',
            ],
            [
                'id'            => 'combo-kim-son-xanh',
                'name'          => 'HỘP QUÀ NÔNG SẢN XANH KIM SƠN FARM',
                'tag'           => '🌿 Canh Tác VietGAP',
                'badge'         => 'Sống Khỏe Mỗi Ngày',
                'price_label'   => 'Liên hệ',
                'image'         => 'https://media.xadonganh.com/experience/flycam_toan_canh_den_thuong_co_loa.jpg',
                'description'   => 'Tuyển chọn dưa lưới Fuji / Huỳnh Long ngọt thanh chuẩn BRIX 14+ kèm rau củ quả canh tác công nghệ cao nhà màng.',
                'items'         => [
                    '02 Quả Dưa lưới Fuji / Huỳnh Long tuyển chọn (1.5 - 1.8kg/quả)',
                    'Set rau xanh thủy canh & rau mầm hữu cơ thu hoạch trong ngày',
                    'Hộp quà giấy Kraft thân thiện môi trường, quai xách sang trọng',
                ],
                'ideal_for'     => 'Món quà sức khỏe thanh mát, chăm sóc người thân và bạn bè',
                'contact_phone' => '0912.456.789',
                'zalo'          => '0912456789',
            ],
        ];
    }

    /**
     * Seasonal harvest calendar for local agriculture and specialties.
     */
    private function getSeasonalCalendar(): array
    {
        return [
            [
                'key'         => 'xuan',
                'season'      => 'MÙA XUÂN (Tháng 1 - 3 ÂL)',
                'title'       => 'Vào Hội Đất Cổ & Khởi Đầu Năm Mới',
                'icon'        => '🌸',
                'badge'       => 'Mùa trẩy hội Đền Thượng',
                'description' => 'Tiết trời se lạnh đầu xuân, mùa lễ hội Đền Thượng Cổ Loa rộn rã cờ hoa. Thời điểm tuyệt vời nhất để thưởng thức mâm cỗ dâng Vua và các món bún xào nóng sốt.',
                'highlights'  => [
                    'Mâm cỗ truyền thống 6 món Cổ Loa dâng tiệc đầu xuân',
                    'Bún Mạch Tràng xào cần tỏi nóng hổi bên bếp củi',
                    'Bánh đúc dẻo mềm chấm tương nếp làng xưa',
                ],
            ],
            [
                'key'         => 'ha',
                'season'      => 'MÙA HẠ (Tháng 4 - 7 ÂL)',
                'title'       => 'Mùa Thu Hoạch Nông Sản & Dưa Lưới',
                'icon'        => '☀️',
                'badge'       => 'Vụ thu hoạch rộ Kim Sơn Farm',
                'description' => 'Những ngày nắng rực rỡ là lúc các giàn dưa lưới Kim Sơn Farm đạt độ ngọt đậm đà nhất (14-15° Brix), mọng nước giải nhiệt ngày hè oi ả.',
                'highlights'  => [
                    'Dưa lưới Fuji & Huỳnh Long hái tươi tại vườn',
                    'Rau củ hữu cơ công nghệ cao tươi mát trong ngày',
                    'Trải nghiệm nông trại sinh thái Thụy Lâm cho trẻ nhỏ',
                ],
            ],
            [
                'key'         => 'thu',
                'season'      => 'MÙA THU (Tháng 8 - 10 ÂL)',
                'title'       => 'Hương Cốm & Nếp Mới Dâng Vua',
                'icon'        => '🌾',
                'badge'       => 'Vụ gặt nếp cái hoa vàng',
                'description' => 'Gió heo may về trên cánh đồng nếp cái hoa vàng. Nghệ nhân rộn ràng ngào mẻ bỏng chủ thơm nức vị gừng cay dâng Vua, quạt bánh đa vừng giòn tan bên chén trà sen ấm.',
                'highlights'  => [
                    'Bỏng chủ Cổ Loa ngào gừng già đón heo may',
                    'Bánh đa vừng nướng than hoa phồng rộp giòn tan',
                    'Thưởng thức cùng trà ướp hoa sen Tây Hồ & Cổ Loa',
                ],
            ],
            [
                'key'         => 'dong',
                'season'      => 'MÙA ĐÔNG (Tháng 11 - 12 ÂL)',
                'title'       => 'Món Nóng Bếp Củi & Lửa Than Hồng',
                'icon'        => '❄️',
                'badge'       => 'Món ấm nồng sum họp',
                'description' => 'Mùa gió bấc tràn về, cả gia đình quây quần bên nồi chuối om giả cầy sôi lục bục vàng óng, ăn cùng bún tươi hay đĩa bún cháy giòn rụm bên bếp than hồng.',
                'highlights'  => [
                    'Chuối nấu giả cầy om nồi đất riềng mẻ nghi ngút khói',
                    'Bún cháy Mạch Tràng giòn rụm chấm mắm ớt cay tê',
                    'Gà đồi hấp lá chanh nóng hổi ngọt thịt',
                ],
            ],
        ];
    }
}

