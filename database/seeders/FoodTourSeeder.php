<?php

namespace Database\Seeders;

use App\Models\Eatery;
use App\Models\FoodTour;
use App\Models\FoodTourStop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FoodTourSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate tables to avoid duplicates when seeding multiple times
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\FoodTourDiary::truncate();
        FoodTourStop::truncate();
        FoodTour::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Query existing eateries
        $eateries = Eatery::all()->keyBy('slug');

        if ($eateries->isEmpty()) {
            return;
        }

        // Tour 1: Hành trình Di sản Cổ Loa (Cổ Loa Linh Thiêng)
        $tour1 = FoodTour::create([
            'name' => 'Hành trình Di sản Cổ Loa',
            'slug' => 'hanh-trinh-di-san-co-loa',
            'description' => 'Khám phá trọn vẹn tinh hoa ẩm thực Cổ Loa cổ kính kết hợp cùng các món ăn đặc sản lâu đời và những góc cafe bình yên ngắm hoàng hôn rực rỡ.',
            'duration' => '3.0 giờ',
            'distance' => '5.2 km',
            'budget' => '250.000đ - 300.000đ/người',
            'difficulty' => '🏛 Văn hóa & Đặc sản',
            'best_time' => '16:00 - 20:00',
            'popularity' => 'Rất cao (9.8/10)',
            'mood' => 'specialty',
            'thumbnail' => 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80',
            'story' => 'Cổ Loa không chỉ nổi tiếng với Loa thành cổ kính nghìn năm lịch sử, mà còn mang trong mình linh hồn ẩm thực bình dị mà kiêu sa. Hành trình này sẽ đưa bạn qua những con ngõ cổ xưa của Thôn Mạch Tràng để ngắm nhìn nghề làm bún tiến vua độc đáo, nếm thử lẩu nướng ngói thơm nồng bên bờ thành đền và kết thúc bằng một buổi chiều lộng gió ngắm cầu Nhật Tân.'
        ]);

        if (isset($eateries['bun-mach-trang-co-loa'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour1->id,
                'eatery_id' => $eateries['bun-mach-trang-co-loa']->id,
                'stop_order' => 1,
                'stop_story' => 'Bún Mạch Tràng nổi tiếng khắp vùng Kinh Bắc xưa. Sợi bún ở đây mang sắc ngà đục tự nhiên (do không dùng chất tẩy đường và được làm bằng công thức gia truyền đặc biệt ép bột chắt lọc). Khi ăn kèm với lòng xào nghệ vàng ươm, bạn sẽ cảm nhận vị dai giòn sần sật cực kỳ đặc trưng.',
                'estimated_time' => '45 phút'
            ]);
        }

        if (isset($eateries['tiem-lau-nuong-co-loa-hoi-quan'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour1->id,
                'eatery_id' => $eateries['tiem-lau-nuong-co-loa-hoi-quan']->id,
                'stop_order' => 2,
                'stop_story' => 'Nằm ngay sát thềm đền Cổ Loa cổ kính, Hội Quán mang lại trải nghiệm ẩm thực độc nhất vô nhị. Món ba chỉ bò sốt trứng muối nướng trên tấm ngói nung đỏ hồng giữ trọn độ mọng của thịt, hòa cùng tiếng xèo xèo reo vang giữa hoàng hôn đền đài tịch mịch.',
                'estimated_time' => '60 phút'
            ]);
        }

        if (isset($eateries['ca-phe-gio-vinh-ngoc'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour1->id,
                'eatery_id' => $eateries['ca-phe-gio-vinh-ngoc']->id,
                'stop_order' => 3,
                'stop_story' => 'Điểm kết chặng tuyệt đẹp chính là Gió Cafe bên triền đê sông Hồng thơ mộng. Hãy order một tách Cà phê Trứng Nướng béo ngậy được khò lửa thơm phức, vừa đón ngọn gió mát lạnh vừa chiêm ngưỡng cầu Nhật Tân lộng lẫy lên đèn.',
                'estimated_time' => '45 phút'
            ]);
        }

        // Tour 2: Đông Anh Đêm Lên Đèn (Ăn đêm Cao Lỗ)
        $tour2 = FoodTour::create([
            'name' => 'Đông Anh Đêm Lên Đèn',
            'slug' => 'dong-anh-dem-len-den',
            'description' => 'Trải nghiệm cuộc sống ban đêm nhộn nhịp tại trung tâm thị trấn Đông Anh với các quán ăn khuya gia truyền ấm áp cực kỳ thu hút giới trẻ.',
            'duration' => '2.5 giờ',
            'distance' => '4.2 km',
            'budget' => '150.000đ - 220.000đ/người',
            'difficulty' => '🌙 Ẩm thực ăn đêm',
            'best_time' => '19:00 - 22:30',
            'popularity' => 'Được yêu thích (9.2/10)',
            'mood' => 'night',
            'thumbnail' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
            'story' => 'Khi ánh đèn thành thị thắp sáng dọc tuyến phố Cao Lỗ sầm uất, Đông Anh lột xác thành một trung tâm vui chơi, ẩm thực đầy năng lượng. Tour đêm sẽ dẫn bạn đi thưởng thức tô phở bò tái lăn trứ danh nghi ngút khói, check-in phòng nghỉ khách sạn sang trọng đẳng cấp ngắm toàn cảnh thị trấn và hội họp sum vầy bên lẩu nướng ngói nóng hổi.'
        ]);

        if (isset($eateries['pho-bo-gia-truyen-cao-lo'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour2->id,
                'eatery_id' => $eateries['pho-bo-gia-truyen-cao-lo']->id,
                'stop_order' => 1,
                'stop_story' => 'Nước phở hầm xương bò 18 tiếng liên tục, thơm nức thảo quả và quế hồi. Thịt bò tái lăn được xào nhanh trên lửa lớn rực cháy với rất nhiều tỏi phi thơm, miếng thịt mềm ngọt tan ngay đầu lưỡi.',
                'estimated_time' => '30 phút'
            ]);
        }

        if (isset($eateries['khach-san-dong-anh-luxury-hotel'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour2->id,
                'eatery_id' => $eateries['khach-san-dong-anh-luxury-hotel']->id,
                'stop_order' => 2,
                'stop_story' => 'Trải nghiệm không gian nghỉ ngơi thư giãn 3 sao ngay trung tâm. Bạn có thể đứng từ ban công kính phòng Double ngắm nhìn ngã tư Cao Lỗ tấp nập xe cộ và lung linh ánh đèn cao áp.',
                'estimated_time' => '30 phút'
            ]);
        }

        if (isset($eateries['tiem-lau-nuong-co-loa-hoi-quan'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour2->id,
                'eatery_id' => $eateries['tiem-lau-nuong-co-loa-hoi-quan']->id,
                'stop_order' => 3,
                'stop_story' => 'Đêm muộn Cổ Loa, còn gì sướng hơn khi quây quần bên nồi lẩu Thái chua cay nóng bỏng. Đồ nhúng phong phú cùng nước lẩu thanh chua nguyên chất giấm bỗng sẽ làm ấm lòng mọi thực khách.',
                'estimated_time' => '60 phút'
            ]);
        }

        // Tour 3: Cafe Chill Ven Hồ Sông Hồng (Sống ảo cuối tuần)
        $tour3 = FoodTour::create([
            'name' => 'Cafe Chill Ven Hồ Sông Hồng',
            'slug' => 'cafe-chill-ven-ho-song-hong',
            'description' => 'Tìm kiếm sự yên bình thư thái tuyệt đối với view ôm trọn sông Hồng cuộn sóng và đầm Vân Trì thơ mộng kết hợp ngắm cảnh thiên nhiên.',
            'duration' => '3.5 giờ',
            'distance' => '9.5 km',
            'budget' => '200.000đ - 350.000đ/người',
            'difficulty' => '☕ Chill & Sống ảo',
            'best_time' => '07:30 - 11:30',
            'popularity' => 'Mới nổi (9.0/10)',
            'mood' => 'chill',
            'thumbnail' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=800&q=80',
            'story' => 'Cuối tuần trốn xa khói bụi thủ đô chật hẹp, hãy cùng vác máy ảnh vi vu qua những đồi cỏ xanh ngắt ven sông Hồng Đông Anh. Tour trọn vẹn cảnh sắc lãng mạn lộng gió cầu Nhật Tân, nhâm nhi tách kem trứng thơm ngậy và kết thúc bằng bữa tiệc cá lăng nướng muối ớt bên hồ sinh thái lãng mạn Vân Trì.'
        ]);

        if (isset($eateries['ca-phe-gio-vinh-ngoc'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour3->id,
                'eatery_id' => $eateries['ca-phe-gio-vinh-ngoc']->id,
                'stop_order' => 1,
                'stop_story' => 'Đón bình minh trong trẻo tràn gió sông Hồng, nhấm nháp ngụm Trà Đào Cam Sả thơm thảo mộc mát rượi. View cầu Nhật Tân buổi sáng sương mờ phủ trắng mang lại sự tĩnh tâm hiếm có.',
                'estimated_time' => '60 phút'
            ]);
        }

        if (isset($eateries['bun-mach-trang-co-loa'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour3->id,
                'eatery_id' => $eateries['bun-mach-trang-co-loa']->id,
                'stop_order' => 2,
                'stop_story' => 'Bữa trưa tiếp năng lượng bằng bát bún mọc dọc mùng nước dùng xương trong vắt thanh dịu đậm chất làng quê cổ kính Cổ Loa.',
                'estimated_time' => '40 phút'
            ]);
        }

        if (isset($eateries['nha-hang-sinh-thai-loc-vung'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour3->id,
                'eatery_id' => $eateries['nha-hang-sinh-thai-loc-vung']->id,
                'stop_order' => 3,
                'stop_story' => 'Thư giãn trong khuôn viên rặng lộc vừng rủ bóng mát mẻ sát mép đầm nước Vân Trì. Nếm thử món cá lăng sông nướng than hoa vàng giòn cuộn kèm rau sống chuối xanh dứa chua chấm mắm nêm đậm vị.',
                'estimated_time' => '90 phút'
            ]);
        }

        // Tour 4: Ẩm thực học sinh sinh viên Liên Hà
        $tour4 = FoodTour::create([
            'name' => 'Ẩm thực Học sinh Sinh viên',
            'slug' => 'am-thuc-hoc-sinh-sinh-vien',
            'description' => 'Tour khám phá các món ăn rẻ - ngon - lạ mang tính chất vỉa hè truyền thống gắn liền với tuổi học sinh tinh nghịch tại Đông Anh.',
            'duration' => '2.0 giờ',
            'distance' => '8.5 km',
            'budget' => '60.000đ - 100.000đ/người',
            'difficulty' => '💰 Rẻ & Trải nghiệm',
            'best_time' => '14:30 - 18:30',
            'popularity' => 'Thịnh hành (9.4/10)',
            'mood' => 'cheap',
            'thumbnail' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80',
            'story' => 'Tuổi học trò Đông Anh chắc chắn không ai xa lạ gì với những buổi chiều tan học cùng rủ nhau đá bóng, lượn lách các quán ăn vặt cổng trường. Chặng tour siêu tiết kiệm này sẽ đưa bạn khám phá đặc sản cháo se Đại Vĩ se tay độc lạ bậc nhất Hà Nội và tô phở tái lăn bình dị Cao Lỗ.'
        ]);

        if (isset($eateries['chao-se-gia-truyen-lien-ha'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour4->id,
                'eatery_id' => $eateries['chao-se-gia-truyen-lien-ha']->id,
                'stop_order' => 1,
                'stop_story' => 'Bát cháo se nóng rẫy nấu bằng nước ninh xương ống ngọt đậm đà, bột nếp se thành những sợi nhỏ dai dai ngập thịt nạc xào phi thơm và ớt bột cay cay nồng ấm.',
                'estimated_time' => '40 phút'
            ]);
        }

        if (isset($eateries['pho-bo-gia-truyen-cao-lo'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour4->id,
                'eatery_id' => $eateries['pho-bo-gia-truyen-cao-lo']->id,
                'stop_order' => 2,
                'stop_story' => 'Kết thúc bằng bát phở tái nạm giòn thơm mùi gừng, hành hoa với mức giá cực kỳ dễ chịu phù hợp túi tiền sinh viên.',
                'estimated_time' => '30 phút'
            ]);
        }

        // Tour 5: Hành trình Tự tay làm Đặc sản Cổ Loa (Ingredient & Cooking Tour)
        $tour5 = FoodTour::create([
            'name' => 'Tự tay làm Đặc sản Cổ Loa',
            'slug' => 'tu-tay-lam-dac-san-co-loa',
            'description' => 'Hành trình nhập vai thực tế: Tự tay lựa gạo chọn nông sản tại Chợ Mạch Tràng, tham gia làm bún tiến vua cùng nghệ nhân và vui chơi trải nghiệm văn hóa.',
            'duration' => '4.0 giờ',
            'distance' => '3.8 km',
            'budget' => '180.000đ - 250.000đ/người',
            'difficulty' => '🌾 Góc trải nghiệm thực tế',
            'best_time' => '07:30 - 11:30',
            'popularity' => 'Mới lạ (9.6/10)',
            'mood' => 'cooking',
            'thumbnail' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80',
            'story' => 'Thay vì chỉ đi thưởng thức các món ăn chín sẵn, hãy đồng hành cùng chúng tôi trong hành trình tìm về cội nguồn các làng nghề Kinh Bắc. Bạn sẽ sắm vai một người dân địa phương thực thụ, ghé chợ mua sắm, tự tay tham gia vào quy trình giã bột ép khuôn bún Mạch Tràng truyền thống dưới sự hướng dẫn của truyền nhân đời thứ 4, và kết thúc bằng một chặng nghỉ ngơi ăn nướng ngói vui chơi thư thả.'
        ]);

        if (isset($eateries['cho-mach-trang'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour5->id,
                'eatery_id' => $eateries['cho-mach-trang']->id,
                'stop_order' => 1,
                'stop_story' => 'Chặng khởi đầu tại Chợ Mạch Tràng nhộn nhịp lúc bình minh. Bạn sẽ ghé qua các sạp nông sản sạch, tự mình lựa chọn những hạt gạo nếp cái hoa vàng chuẩn mùi và bó rau sống trồng ven sông Đuống để chuẩn bị nguyên liệu cho bữa trưa tự tay chế biến.',
                'estimated_time' => '45 phút'
            ]);
        }

        if (isset($eateries['bun-mach-trang-co-loa'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour5->id,
                'eatery_id' => $eateries['bun-mach-trang-co-loa']->id,
                'stop_order' => 2,
                'stop_story' => 'Đến xưởng bún của nghệ nhân Nguyễn Văn Cường. Bạn sẽ trực tiếp quan sát công đoạn ngâm ủ bột tự nhiên, tự tay nâng chiếc chày giã bột và ép khuôn những sợi bún màu ngà đục nguyên bản. Sau đó, trực tiếp thưởng thức đĩa bún lòng xào nghệ thơm nồng vàng óng do chính tay mình góp công hoàn thiện.',
                'estimated_time' => '90 phút'
            ]);
        }

        if (isset($eateries['tiem-lau-nuong-co-loa-hoi-quan'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour5->id,
                'eatery_id' => $eateries['tiem-lau-nuong-co-loa-hoi-quan']->id,
                'stop_order' => 3,
                'stop_story' => 'Nghỉ ngơi thư giãn chặng cuối tại Hội Quán Cổ Loa ngay thềm đền. Tự tay lật những miếng thịt ba chỉ bò mọng nước nướng trên phiến ngói hồng rực, uống một ly nước mía mát lạnh để khép lại nửa ngày làm "người nông dân Kinh Bắc" trọn vẹn.',
                'estimated_time' => '60 phút'
            ]);
        }

        // Tour 6: Gói Bánh Chưng Xanh Tranh Khúc (Ingredient & Cooking Tour)
        $tour6 = FoodTour::create([
            'name' => 'Gói Bánh Chưng Xanh Tranh Khúc',
            'slug' => 'goi-banh-chung-xanh-tranh-khuc',
            'description' => 'Hành trình trải nghiệm: Ghé Chợ Tó chọn lá dong rừng, tự tay đong nếp nhung nặn bánh chưng vuông vức không cần khuôn và nhóm lửa luộc bánh.',
            'duration' => '1 Ngày (8h)',
            'distance' => '1.2 km',
            'budget' => '350.000đ/người',
            'difficulty' => '✨ Làng nghề cổ truyền',
            'best_time' => '08:00 - 16:00',
            'popularity' => 'Đăng ký sớm',
            'mood' => 'cooking',
            'thumbnail' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
            'story' => 'Tìm về làng Tranh Khúc nổi tiếng với nghề làm bánh chưng xanh. Bạn sẽ đi chợ chọn những lá dong bánh tẻ xanh mướt, mua lạt giang dẻo dai, tự tay đãi gạo nếp nhung, gói bánh vuông vắn và cùng nhóm bếp củi canh nồi bánh chưng nghi ngút khói.'
        ]);

        if (isset($eateries['cho-to'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour6->id,
                'eatery_id' => $eateries['cho-to']->id,
                'stop_order' => 1,
                'stop_story' => 'Ghé qua Chợ Tó sầm uất để tự tay chọn những xấp lá dong rừng xanh ngắt, bản to và những bó lạt giang dẻo dai từ tay các tiểu thương bản xứ.',
                'estimated_time' => '45 phút'
            ]);
        }

        if (isset($eateries['chao-se-gia-truyen-lien-ha'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour6->id,
                'eatery_id' => $eateries['chao-se-gia-truyen-lien-ha']->id,
                'stop_order' => 2,
                'stop_story' => 'Đến nhà xưởng làm bánh chưng cổ truyền Tranh Khúc. Học cách đãi gạo nếp nhung tròn mẩy, thái ba chỉ ướp tiêu thơm phức và gói chiếc bánh chưng vuông tăm tắp không cần dùng khuôn.',
                'estimated_time' => '90 phút'
            ]);
        }

        if (isset($eateries['nha-hang-sinh-thai-loc-vung'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour6->id,
                'eatery_id' => $eateries['nha-hang-sinh-thai-loc-vung']->id,
                'stop_order' => 3,
                'stop_story' => 'Nghỉ ngơi và nhóm củi luộc bánh bên hồ nước mát lành. Trong lúc chờ bánh chín, cùng gia đình thưởng thức các món ăn đồng quê thơm nức lòng.',
                'estimated_time' => '120 phút'
            ]);
        }

        // Tour 7: Ủ Tương Nếp Đất Nung Vân Hà (Ingredient & Cooking Tour)
        $tour7 = FoodTour::create([
            'name' => 'Ủ Tương Nếp Đất Nung Vân Hà',
            'slug' => 'u-tuong-nep-dat-nung-van-ha',
            'description' => 'Hành trình trải nghiệm: Ghé Chợ TT Đông Anh chọn vại sành đất nung, học bí quyết ủ mốc tương nếp bằng lá nhãn và ép khuôn đậu phụ sạch.',
            'duration' => '4.0 giờ',
            'distance' => '2.0 km',
            'budget' => '180.000đ/người',
            'difficulty' => '✨ Bí quyết gia truyền',
            'best_time' => '09:00 - 13:00',
            'popularity' => 'Sắp ra mắt',
            'mood' => 'cooking',
            'thumbnail' => 'https://images.unsplash.com/photo-1596797038530-2c107229654b?auto=format&fit=crop&w=800&q=80',
            'story' => 'Về làng cổ Vân Hà khám phá nghệ thuật ủ tương nếp trong vại sành đất nung. Trải nghiệm vo gạo đồ xôi, ủ mốc bằng lá nhãn tươi và khơi dòng nước giếng cổ đá ong ngọt mát để đúc nên dòng tương sánh mịn thơm bùi.'
        ]);

        if (isset($eateries['cho-tt-dong-anh'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour7->id,
                'eatery_id' => $eateries['cho-tt-dong-anh']->id,
                'stop_order' => 1,
                'stop_story' => 'Tìm mua những chiếc vại sành đất nung Hương Canh đỏ hồng và nia tre nhỏ xinh để chuẩn bị cho công đoạn ủ mốc tương gia truyền.',
                'estimated_time' => '45 phút'
            ]);
        }

        if (isset($eateries['bun-mach-trang-co-loa'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour7->id,
                'eatery_id' => $eateries['bun-mach-trang-co-loa']->id,
                'stop_order' => 2,
                'stop_story' => 'Thực hành đồ xôi nếp cái hoa vàng, trải đều ra nia tre để ủ mốc tương tự nhiên bằng lá nhãn dưới nắng ấm hiền hòa.',
                'estimated_time' => '90 phút'
            ]);
        }

        if (isset($eateries['ca-phe-gio-vinh-ngoc'])) {
            FoodTourStop::create([
                'food_tour_id' => $tour7->id,
                'eatery_id' => $eateries['ca-phe-gio-vinh-ngoc']->id,
                'stop_order' => 3,
                'stop_story' => 'Nghỉ chân bên triền đê lộng gió, nhâm nhi tách cafe trứng nướng và thưởng thức nước chấm tương nếp chao béo ngậy chấm kèm rau luộc mát lành.',
                'estimated_time' => '60 phút'
            ]);
        }
    }
}
