<?php

namespace App\Http\Controllers;

use App\Models\FoodTour;
use App\Models\ExperienceFeedback;
use App\Services\EateryApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExperienceCornerController extends Controller
{
    /**
     * Display a listing of cultural craft experiences, folk activities & monument services.
     */
    public function index(Request $request)
    {
        $tours = FoodTour::public()
            ->where('mood', 'cooking')
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries')
            ->get();
            
        $activities = $this->getFeaturedActivities();
        $feedbacks = $this->getCommunityFeedbacks();
            
        return view('exp-corner.index', compact('tours', 'activities', 'feedbacks'));
    }

    /**
     * Store feedback / experience diary from visitor directly into MySQL database.
     */
    public function storeFeedback(Request $request)
    {
        $validated = $request->validate([
            'author_name'   => 'required|string|max:100',
            'activity_name' => 'required|string|max:255',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'required|string|min:10|max:1500',
            'image_url'     => 'nullable|string|max:500',
        ]);

        $feedback = ExperienceFeedback::create([
            'user_id'       => Auth::id(),
            'author_name'   => strip_tags($validated['author_name']),
            'activity_name' => strip_tags($validated['activity_name']),
            'rating'        => (int) $validated['rating'],
            'comment'       => strip_tags($validated['comment']),
            'image_url'     => $validated['image_url'] ?? null,
            'is_verified'   => true,
            'helpful_count' => 1,
            'status'        => 'approved',
        ]);

        $feedbackData = [
            'id'            => 'fb_' . $feedback->id,
            'author_name'   => $feedback->author_name,
            'activity_name' => $feedback->activity_name,
            'rating'        => (int) $feedback->rating,
            'verified'      => (bool) $feedback->is_verified,
            'helpful_count' => (int) $feedback->helpful_count,
            'comment'       => $feedback->comment,
            'image_url'     => $feedback->image_url,
            'created_at'    => 'Vừa đăng xong',
        ];

        return response()->json([
            'success'  => true,
            'message'  => 'Nhật ký trải nghiệm của bạn đã được lưu vào cơ sở dữ liệu và hiển thị lên bảng tin cộng đồng!',
            'feedback' => $feedbackData,
        ]);
    }

    /**
     * Get all activities including official and newly featured experiences.
     */
    protected function getFeaturedActivities()
    {
        $dbActivities = collect();
        try {
            $dbActivities = EateryApiService::getAllCulturalActivities();
        } catch (\Throwable $e) {}

        // Curated featured experiences for Dong Anh (including the 3 newly requested activities)
        $curated = [
            // ── HOẠT ĐỘNG MỚI 1: ĂN TỐI NHÀ DÂN CỔ LOA ──
            (object)[
                'id'            => 101,
                'name'          => 'Ăn tối tại nhà dân Cổ Loa - Trải nghiệm mâm cơm xứ Cổ & Sống chậm cùng người bản xứ',
                'type'          => 'homestay',
                'price'         => 180000,
                'unit'          => 'người / suất',
                'discount_note' => 'Trẻ em dưới 6 tuổi miễn phí',
                'description'   => 'Cùng ngồi ăn mâm cơm gia đình ấm cúng tại gian nhà cổ Cổ Loa. Thưởng thức đặc sản Cháo se Cổ Loa ngàn năm, gà đồi hấp lá chanh, bún Mạch Tràng xào cần tỏi, canh cua đồng mùng tơi cà pháo và nghe các cụ bô lão kể tích xưa Nỏ Thần.',
                'image_path'    => 'https://media.xadonganh.com/experience/chao_se_co_loa.jpg',
                'duration'      => '2.5 - 3 giờ (17:30 - 20:30)',
                'best_time'     => 'Buổi tối hằng ngày (17:30 - 20:30)',
                'highlights'    => [
                    'Thưởng thức món Cháo se Cổ Loa độc nhất vô nhị & mâm cơm gia đình xứ Cổ',
                    'Không gian nhà cổ & sân vườn thoáng đãng, đậm hồn quê Bắc Bộ',
                    'Giao lưu văn hóa chân tình cùng chủ nhà, nghe kể chuyện lịch sử làng xưa',
                ],
                'timeline'      => [
                    'Chặng 1 (17:30): Đón khách tại nhà dân, thưởng trà hoa nhài và dạo quanh vườn cây ăn trái.',
                    'Chặng 2 (18:15): Cùng gia chủ vào bếp chuẩn bị món ăn dân dã, học cách pha nước mắm gừng ớt truyền thống.',
                    'Chặng 3 (19:00 - 20:30): Thưởng thức mâm cơm ấm cúng, giao lưu văn hóa và nghe kể chuyện truyền thuyết Cổ Loa.',
                ],
                'included'      => 'Bữa tối thịnh soạn, Trà đón tiếp & Tráng miệng hoa quả vườn, Chỗ đỗ xe ô tô.',
                'notice'        => 'Nên đặt trước ít nhất 3 giờ để gia đình kịp đi chợ chọn thực phẩm tươi ngon nhất.',
                'eatery'        => (object)[
                    'name'  => 'Nhà Cổ Dân Gian Cổ Loa',
                    'slug'  => 'khu-di-tich-co-loa',
                    'phone' => '0983.882.119',
                ],
            ],

            // ── HOẠT ĐỘNG MỚI 2: TRỒNG RAU CỔ LOA ──
            (object)[
                'id'            => 102,
                'name'          => 'Tour trải nghiệm trồng rau tại Cổ Loa - Nông nghiệp sinh thái ven hào thành',
                'type'          => 'craft',
                'price'         => 60000,
                'unit'          => 'người / lượt',
                'discount_note' => 'Tặng giỏ rau sạch tự thu hoạch mang về',
                'description'   => 'Khoác lên mình bộ áo bà ba, nón lá; tự tay xới đất phù sa ven hào thành Cổ Loa, gieo mầm hạt giống, tưới nước bằng gáo dừa và thu hoạch rau xanh hữu cơ mang về nhà.',
                'image_path'    => 'https://media.xadonganh.com/experience/flycam_toan_canh_den_thuong_co_loa.jpg',
                'duration'      => '2 giờ (Sáng 08:00 hoặc Chiều 15:30)',
                'best_time'     => '08:00 - 10:00 & 15:30 - 17:30',
                'highlights'    => [
                    '100% Thực hành làm nông dân thực thụ giữa thiên nhiên ven hào thành',
                    'Học kỹ thuật bón phân hữu cơ và phân biệt các giống rau cổ truyền',
                    'Mỗi khách được tự tay hái một giỏ rau sạch tươi rói mang về gia đình',
                ],
                'timeline'      => [
                    'Chặng 1 (08:00): Tập kết, nhận trang phục áo bà ba, nón lá và nghe giới thiệu về các loại rau bản địa.',
                    'Chặng 2 (08:30): Tự tay xới luống đất phù sa, ủ mùn vi sinh và gieo hạt giống cải mầm, xà lách.',
                    'Chặng 3 (09:30): Gánh nước tưới rau và tự tay thu hoạch giỏ rau sạch mang về nhà nấu cơm.',
                ],
                'included'      => 'Mượn trang phục áo bà ba & nón lá, Dụng cụ làm vườn, Giỏ rau sạch thu hoạch mang về.',
                'notice'        => 'Nên mang theo giày thể thao hoặc dép quai hậu thuận tiện đi lại ngoài vườn đất mềm.',
                'eatery'        => (object)[
                    'name'  => 'Vườn Nông Nghiệp Sinh Thái Cổ Loa',
                    'slug'  => 'khu-di-tich-co-loa',
                    'phone' => '0912.456.789',
                ],
            ],

            // ── HOẠT ĐỘNG MỚI 3: GAME MẬT MÃ LOA THÀNH ──
            (object)[
                'id'            => 103,
                'name'          => 'Game Mật Mã Loa Thành - Nhập vai giải mật thư vượt 3 vòng hào thành Cổ Loa',
                'type'          => 'game',
                'price'         => 80000,
                'unit'          => 'người / lượt',
                'discount_note' => 'Đoàn từ 5 người giảm 15% + Tặng huy hiệu',
                'description'   => 'Trò chơi nhập vai giải mật thư (Scavenger Hunt) khám phá bí ẩn quân sự Loa Thành. Lập đội giải 5 câu đố lịch sử qua Đền Thượng, Am Mỵ Châu, Giếng Ngọc để truy tìm cơ quan Nỏ Thần.',
                'image_path'    => 'https://media.xadonganh.com/experience/tuong_no_than_au_lac.jpg',
                'duration'      => '2 - 2.5 giờ',
                'best_time'     => 'Ban ngày (08:30 - 16:30)',
                'highlights'    => [
                    'Game vận động trí tuệ độc đáo dựa trên truyền thuyết lịch sử có thật',
                    'Bản đồ cuộn giấy da thời Âu Lạc và túi đạo cụ giải mã mật thư cực ngầu',
                    'Phần thưởng vinh danh: Huy hiệu "Chiến binh Âu Lạc" & Quà lưu niệm thủ công',
                ],
                'timeline'      => [
                    'Chặng 1: Tập kết trước Đền Thượng, nhận túi mật thư da bò, la bàn cổ và bản đồ xoắn ốc Loa Thành.',
                    'Chặng 2: Vượt chướng ngại vật giải mã văn bia cổ, định vị điểm mốc tại Am Mỵ Châu và hào thành thứ 2.',
                    'Chặng 3: Giải cơ quan Nỏ Thần tại Giếng Ngọc, tìm mảnh ghép cổ vật và nhận huy hiệu Chiến binh Âu Lạc.',
                ],
                'included'      => 'Bản đồ da bò Loa Thành, Túi mật thư đạo cụ, Trọng tài hướng dẫn, Huy hiệu lưu niệm.',
                'notice'        => 'Thích hợp cho nhóm bạn từ 3 - 15 người. Khuyến khích mang trang phục năng động để dễ di chuyển.',
                'eatery'        => (object)[
                    'name'  => 'Ban Quản Lý Di Tích & Trải Nghiệm Cổ Loa',
                    'slug'  => 'khu-di-tich-co-loa',
                    'phone' => '0243.965.2973',
                ],
            ],

            // ── CÁC HOẠT ĐỘNG TRUYỀN THỐNG TIÊU BIỂU ──
            (object)[
                'id'            => 104,
                'name'          => 'Bắn nỏ liên châu, đúc hiện vật thủ công Âu Lạc & làm oản xôi lá mít dâng vua',
                'type'          => 'game',
                'price'         => 30000,
                'unit'          => 'người',
                'discount_note' => 'Học sinh & Người cao tuổi giảm 50%',
                'description'   => 'Trải nghiệm bắn nỏ thần mô phỏng công nghệ quân sự thời An Dương Vương, tự tay đúc hiện vật lưu niệm và chuẩn bị lễ vật dâng vua.',
                'image_path'    => 'https://media.xadonganh.com/experience/tuong_no_than_au_lac.jpg',
                'duration'      => '1.5 giờ',
                'best_time'     => '08:00 - 17:00 hằng ngày',
                'highlights'    => [
                    'Trực tiếp ngắm bắn cung nỏ thần di sản trên bia bia cỏ',
                    'Tự tay đúc khuôn tiền đồng mô phỏng thời kỳ Âu Lạc',
                    'Được nghệ nhân hướng dẫn tỉ mỉ, đảm bảo an toàn tuyệt đối',
                ],
                'timeline'      => [
                    'Chặng 1: Tìm hiểu cấu tạo lẫy nỏ thần thời An Dương Vương và kỹ thuật ngắm bia.',
                    'Chặng 2: Thực hành bắn 5 lượt tên nỏ trúng đích nhận điểm thưởng.',
                    'Chặng 3: Tự tay làm bánh oản xôi lá mít và đúc hiện vật lưu niệm đem về.',
                ],
                'included'      => 'Cung nỏ, 10 mũi tên tập, Vật liệu đúc quà lưu niệm.',
                'notice'        => 'Trẻ em dưới 8 tuổi cần có phụ huynh đi kèm.',
                'eatery'        => (object)[
                    'name'  => 'Khu Di Tích Quốc Gia Đặc Biệt Cổ Loa',
                    'slug'  => 'khu-di-tich-co-loa',
                    'phone' => '0243.965.2973',
                ],
            ],

            (object)[
                'id'            => 105,
                'name'          => 'Tự tay làm bún Mạch Tràng cổ truyền & Workshop đan lát mây tre',
                'type'          => 'craft',
                'price'         => 50000,
                'unit'          => 'người',
                'discount_note' => 'Được mang thành phẩm bún tươi về',
                'description'   => 'Xoay cối đá xay bột gạo ủ chua, tự tay ép từng sợi bún Mạch Tràng trứ danh, thưởng thức đĩa bún Mạch Tràng xào cần tỏi thơm lừng và học kỹ thuật đan lát mây tre cùng nghệ nhân.',
                'image_path'    => 'https://media.xadonganh.com/experience/bun_xao_mach_trang.jpg',
                'duration'      => '2 giờ',
                'best_time'     => '08:30 - 11:30',
                'highlights'    => [
                    'Tự tay xoay cối đá cổ truyền xay bột gạo ngâm',
                    'Tự ép sợi bún vào nồi nước sôi sùng sục và thưởng thức đĩa bún xào cần tỏi đặc sản',
                    'Học đan rổ rá mây tre đan cùng nghệ nhân bản địa',
                ],
                'timeline'      => [
                    'Chặng 1: Tìm hiểu bí quyết làm bún sẫm màu tự nhiên dâng vua An Dương Vương.',
                    'Chặng 2: Trải nghiệm xoay cối đá và ép sợi bún tươi.',
                    'Chặng 3: Thưởng thức đĩa bún Mạch Tràng xào cần tỏi nóng hổi tại xưởng.',
                ],
                'included'      => 'Nguyên liệu bột gạo làm bún, Hướng dẫn viên làng nghề, Suất bún xào cần tỏi ăn thử.',
                'notice'        => 'Trang phục gọn gàng, tránh mặc quần áo sáng màu dễ dính bột.',
                'eatery'        => (object)[
                    'name'  => 'Làng Nghề Truyền Thống Mạch Tràng',
                    'slug'  => 'lang-nghe-mach-trang',
                    'phone' => '0988.123.456',
                ],
            ],

            (object)[
                'id'            => 106,
                'name'          => 'Tham quan quần thể di tích lịch sử đặc biệt Thành Cổ Loa',
                'type'          => 'ticket',
                'price'         => 10000,
                'unit'          => 'vé / lượt',
                'discount_note' => 'Miễn phí trẻ em dưới 1m2',
                'description'   => 'Khám phá kiến trúc thành Cổ Loa cổ kính với 3 vòng hào thành độc đáo, Đền Thượng, Am Mỵ Châu, Điếm làng và Giếng Ngọc.',
                'image_path'    => 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg',
                'duration'      => 'Tự do',
                'best_time'     => '07:30 - 17:30 hằng ngày',
                'highlights'    => [
                    'Quần thể di tích quốc gia đặc biệt quy mô nhất miền Bắc',
                    'Chiêm ngưỡng giếng Trọng Thủy - Mỵ Châu huyền thoại',
                    'Cảnh quan thiên nhiên cổ kính và bóng cây cổ thụ ngàn năm',
                ],
                'timeline'      => [
                    'Chặng 1: Tham quan cổng thành, Điếm làng và Đền Thượng thờ Vua.',
                    'Chặng 2: Thăm Am Mỵ Châu, tượng bà chúa Cổ Loa không đầu.',
                    'Chặng 3: Dạo quanh Giếng Ngọc và bờ hào thành xanh mát.',
                ],
                'included'      => 'Vé tham quan toàn bộ các điểm di tích trong quần thể.',
                'notice'        => 'Trang phục lịch sự, kín đáo khi vào dâng hương trong đền thờ.',
                'eatery'        => (object)[
                    'name'  => 'Khu Di Tích Quốc Gia Đặc Biệt Cổ Loa',
                    'slug'  => 'khu-di-tich-co-loa',
                    'phone' => '0243.965.2973',
                ],
            ],

            (object)[
                'id'            => 107,
                'name'          => 'Dịch vụ dâng hương tưởng niệm & Thuyết minh viên di tích Cổ Loa',
                'type'          => 'service',
                'price'         => 150000,
                'unit'          => 'đoàn',
                'discount_note' => 'Kèm thuyết minh viên chuyên nghiệp',
                'description'   => 'Hành trình tế lễ dâng hương tưởng niệm vua An Dương Vương trang nghiêm cùng các cụ bô lão áo thụng và được thuyết minh viên bản địa thuyết minh chi tiết về lịch sử xây thành Loa.',
                'image_path'    => 'https://media.xadonganh.com/experience/te_le_den_thuong_co_loa.jpg',
                'duration'      => '1.5 giờ',
                'best_time'     => 'Sáng 08:30 hoặc Chiều 14:00',
                'highlights'    => [
                    'Nghi thức dâng hương trang trọng tại Đền Thượng',
                    'Thuyết minh viên truyền cảm hứng tái hiện ngàn năm lịch sử',
                    'Phục vụ chu đáo cho đoàn gia đình, cơ quan, học sinh',
                ],
                'timeline'      => [
                    'Chặng 1: Đón đoàn tại nhà trưng bày di tích Cổ Loa.',
                    'Chặng 2: Hướng dẫn nghi thức dâng hương tưởng niệm Vua.',
                    'Chặng 3: Thuyết minh chi tiết từng vòng hào thành và các cổ vật khai quật.',
                ],
                'included'      => 'Lễ vật dâng hương cơ bản, Thuyết minh viên theo đoàn suốt tuyến.',
                'notice'        => 'Vui lòng liên hệ trước ít nhất 1 ngày đối với đoàn lớn.',
                'eatery'        => (object)[
                    'name'  => 'Khu Di Tích Quốc Gia Đặc Biệt Cổ Loa',
                    'slug'  => 'khu-di-tich-co-loa',
                    'phone' => '0243.965.2973',
                ],
            ],
        ];

        // Combine DB activities if any, with curated list
        if ($dbActivities->isNotEmpty()) {
            return collect($curated)->merge($dbActivities)->unique('name')->values();
        }

        return collect($curated);
    }

    /**
     * Get community feedbacks directly from MySQL database.
     */
    protected function getCommunityFeedbacks()
    {
        try {
            $feedbacks = ExperienceFeedback::approved()
                ->latest()
                ->get()
                ->map(function ($item) {
                    return [
                        'id'            => 'fb_' . $item->id,
                        'author_name'   => $item->author_name,
                        'activity_name' => $item->activity_name,
                        'rating'        => (int) $item->rating,
                        'verified'      => (bool) $item->is_verified,
                        'helpful_count' => (int) $item->helpful_count,
                        'comment'       => $item->comment,
                        'image_url'     => $item->image_url,
                        'host_reply'    => $item->host_reply,
                        'created_at'    => $item->created_at ? $item->created_at->diffForHumans() : 'Mới đây',
                    ];
                });

            if ($feedbacks->isNotEmpty()) {
                return $feedbacks->toArray();
            }
        } catch (\Throwable $e) {
            \Log::warning('Could not load experience feedbacks from DB: ' . $e->getMessage());
        }

        return [];
    }
}
