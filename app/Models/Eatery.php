<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Eatery extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'category_id',
        'commune_id',
        'description',
        'address',
        'phone',
        'opening_hours',
        'latitude',
        'longitude',
        'price_range',
        'image_path',
        'is_featured',
        'rating',
        'status'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'rating' => 'decimal:2',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    /**
     * Scope only active eateries
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope only featured eateries
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get average rating dynamically from reviews or fallback to stored rating.
     * Prevents N+1 query issue by checking if 'reviews' relation is loaded.
     */
    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->isEmpty() ? (float) $this->rating : round($this->reviews->avg('rating'), 1);
        }
        return (float) $this->rating;
    }

    /**
     * Get rich heritage storytelling and cultural data for digital museum showcase
     */
    public function getHeritageDossierAttribute()
    {
        $dossiers = [
            'bun-mach-trang-co-loa' => [
                'ocop_stars' => 4,
                'heritage_year' => 'Từ thời Hùng Vương / An Dương Vương dựng nước',
                'story' => 'Bún Mạch Tràng có lịch sử hào hùng gắn liền với truyền thuyết Cổ Loa thành. Tương truyền, trong quá trình xây dựng thành Cổ Loa, người dân làng Mạch Tràng đã làm ra những sợi bún có màu ngà tự nhiên làm lương thực dâng lên vua An Dương Vương và các tướng sĩ. Trải qua hàng ngàn năm, sợi bún đặc trưng không dùng chất tẩy trắng này vẫn gìn giữ nguyên vẹn vị ngọt thanh mát, dẻo dai từ gạo nguyên bản, trở thành một di sản sống của vùng đất kinh đô cổ.',
                'artisans' => 'Nghệ nhân Nguyễn Văn Cường (Đời thứ 4 làm bún truyền thống làng Mạch Tràng) chia sẻ: "Làm bún Mạch Tràng khó nhất là khâu ủ bột. Gạo được chọn kỹ lưỡng, ngâm ủ đủ từ 2-3 ngày đêm để lên men tự nhiên trước khi ép khuôn. Bún xịn phải có màu trắng ngà mộc mạc, ăn vào thấy dai dẻo tự nhiên của gạo nếp cũ."',
                'ingredients' => [
                    'Gạo tẻ ngon vụ mùa cũ (để bún dai và không nát)',
                    'Nguồn nước ngầm vùng đất cổ Cổ Loa tinh khiết',
                    'Nghệ tươi, thịt heo phi thơm hành hành củ',
                    'Rau thơm trồng hữu cơ ven đê sông Đuống'
                ],
                'fun_fact' => 'Khác với bún thương phẩm ngoài thị trường, bún Mạch Tràng chuẩn có màu hơi ngà sẫm (không trắng tinh). Đó là do quy trình ủ lọc bột thủ công tự nhiên hoàn toàn không sử dụng bất kỳ hóa chất hay chất tẩy trắng nào. Đây chính là điểm tự hào giúp bún Mạch Tràng giữ trọn danh tiếng ngàn năm qua.',
                'audio_narrative' => 'Chào mừng bạn đến với Không gian số di sản Khám phá Đông Anh. Sau đây là câu chuyện về Bún Mạch Tràng Cổ Loa. Tương truyền, khi vua An Dương Vương xây thành Cổ Loa, người dân làng Mạch Tràng đã chế biến ra những sợi bún màu trắng ngà mộc mạc, dẻo thơm từ hạt gạo ruộng đồng làm lương thực cho quân lính. Sợi bún Mạch Tràng trải qua quy trình ngâm gạo, ủ men tự nhiên suốt 3 ngày đêm, giã bột và ép khuôn thủ công. Vị bún dai dai, ngọt thanh dịu mát đã đi vào thơ ca và tâm thức người dân Việt Nam như một biểu tượng của lòng yêu nước và tài hoa ẩm thực Việt.',
                'nearby_attractions' => [
                    ['name' => 'Khu di tích lịch sử Đền Cổ Loa', 'distance' => '800m', 'url' => 'https://vi.wikipedia.org/wiki/C%E1%BB%95_Loa'],
                    ['name' => 'Đình làng Mạch Tràng cổ kính', 'distance' => '200m', 'url' => '#'],
                    ['name' => 'Am Mỵ Châu - Giếng Ngọc Cổ Loa', 'distance' => '900m', 'url' => '#'],
                ],
                'timeline' => [
                    ['year' => 'Thời An Dương Vương', 'event' => 'Xuất hiện đầu tiên làm lương thực cho quân lính xây thành Cổ Loa.'],
                    ['year' => 'Thế kỷ 19', 'event' => 'Trở thành sản vật dâng tiến cúng tiến hàng năm dịp lễ hội đền Cổ Loa.'],
                    ['year' => 'Năm 2018', 'event' => 'Đạt chứng nhận OCOP 4 sao và được vinh danh sản phẩm nông nghiệp tiêu biểu Hà Nội.'],
                    ['year' => 'Hiện nay', 'event' => 'Được số hóa trên Bản đồ Di sản Khám phá Đông Anh để quảng bá ra toàn thế giới.']
                ]
            ],
            'chao-se-gia-truyen-lien-ha' => [
                'ocop_stars' => 3,
                'heritage_year' => 'Hơn 100 năm gìn giữ nét quê Đại Vĩ',
                'story' => 'Món cháo se trứ danh của làng Đại Vĩ, xã Liên Hà được nấu vô cùng công phu và tỉ mỉ. Tên gọi "cháo se" bắt nguồn từ kỹ thuật se bột bằng tay rất độc đáo. Bột gạo sau khi ngâm và lọc khô sẽ được người thợ khéo léo dùng lòng bàn tay se thành những sợi nhỏ, tròn dài đều đặn như sợi bánh canh rồi thả trực tiếp vào nồi nước xương hầm nóng hổi đang sôi sùng sục. Cháo chín sánh đặc, thơm phức, mang đậm hương vị đồng quê mộc mạc.',
                'artisans' => 'Cụ bà Nguyễn Thị Nhị (78 tuổi, truyền nhân đời thứ 3 của quán cháo se lâu đời nhất Liên Hà): "Để nồi cháo se thơm ngon, nước hầm phải là xương ống heo tươi nguyên chất ninh liên tục trong 8 tiếng. Khi se bột phải đều tay, sợi bột không được quá to hay quá nhỏ để cháo chín đều và giữ được độ sánh mịn."',
                'ingredients' => [
                    'Bột gạo tẻ ngon xay ướt, ép ráo nước',
                    'Nguồn nước xương ống heo ninh nhừ ngọt lịm',
                    'Thịt heo băm nhuyễn xào hành củ phi thơm',
                    'Hạt tiêu sọ xay cay nồng, hành hoa tươi'
                ],
                'fun_fact' => 'Ăn cháo se Liên Hà chuẩn vị là không dùng thìa múc ăn liền mà phải thong thả nhâm nhi từng thìa nhỏ nóng hổi, để cảm nhận sợi bột se dai mềm dẻo quyện trong nước cháo ngọt lịm từ tủy xương và mùi thơm nồng nàn của tiêu sọ.',
                'audio_narrative' => 'Chào mừng bạn đến với Không gian số di sản Khám phá Đông Anh. Sau đây là câu chuyện về Cháo se Liên Hà. Bát cháo se nóng hổi, sánh mịn là linh hồn ẩm thực của làng Đại Vĩ. Nét độc đáo làm nên thương hiệu chính là những sợi bột được người thợ dùng tay se tỉ mỉ trực tiếp vào nồi nước dùng hầm xương ngọt lịm. Mỗi sợi bột thấm đẫm vị béo ngậy, ngọt thơm của tủy heo, quyện cùng thịt băm xào hành thơm nức lòng. Thưởng thức cháo se vào buổi chiều se lạnh là một trải nghiệm văn hóa dân dã khó quên, đong đầy tình người và hơi thở làng quê Kinh Bắc.',
                'nearby_attractions' => [
                    ['name' => 'Làng nghề gỗ mỹ nghệ Liên Hà', 'distance' => '500m', 'url' => '#'],
                    ['name' => 'Đình làng Đại Vĩ cổ kính', 'distance' => '300m', 'url' => '#'],
                    ['name' => 'Chùa Diên Phúc tôn nghiêm', 'distance' => '1.2km', 'url' => '#'],
                ],
                'timeline' => [
                    ['year' => 'Đầu thế kỷ 20', 'event' => 'Món cháo dân dã xuất hiện trong các buổi chợ quê Liên Hà để phục vụ người dân lao động.'],
                    ['year' => 'Kháng chiến chống Pháp', 'event' => 'Là món ăn tiếp tế ấm lòng quân dân du kích địa phương.'],
                    ['year' => 'Năm 2021', 'event' => 'Được chứng nhận OCOP 3 sao và đưa vào danh sách ẩm thực truyền thống cần được bảo tồn của huyện Đông Anh.'],
                    ['year' => 'Hiện tại', 'event' => 'Chính thức số hóa trên nền tảng Bản đồ di sản Khám phá Đông Anh để gìn giữ muôn đời sau.']
                ]
            ],
            'htx-nong-nghiep-duoc-lieu-cong-nghe-cao-kovi' => [
                'ocop_stars' => 4,
                'heritage_year' => 'Năm 2022',
                'story' => 'Đông trùng hạ thảo KOVI được ứng dụng công nghệ sinh học tiên tiến, lưu giữ trọn vẹn tinh chất quý giá. Đây là niềm tự hào của ngành nông nghiệp công nghệ cao Đông Anh.',
                'artisans' => 'Tập thể kỹ sư nông nghiệp HTX KOVI: "Chúng tôi luôn kiểm soát nghiêm ngặt từ khâu cấy giống đến thu hoạch để đảm bảo chất lượng."',
                'ingredients' => ['Đông trùng hạ thảo tươi', 'Đông trùng hạ thảo khô', 'Ký chủ nhộng tằm'],
                'fun_fact' => 'Sản phẩm đạt OCOP 4 sao và là món quà sức khỏe được ưa chuộng.',
                'audio_narrative' => 'Chào mừng bạn đến với Không gian số di sản Khám phá Đông Anh. HTX KOVI là đơn vị tiên phong trong việc nuôi cấy Đông trùng hạ thảo chất lượng cao.',
                'nearby_attractions' => [],
                'timeline' => [
                    ['year' => 'Năm 2022', 'event' => 'Đạt chứng nhận OCOP 4 sao Cấp Quốc Gia.']
                ]
            ],
            'cong-ty-tnhh-hoang-chien-thang' => [
                'ocop_stars' => 4,
                'heritage_year' => 'Truyền thống nhiều đời',
                'story' => 'Hoàng Chiến Thắng lưu giữ hương vị bánh kẹo Đông Ngàn truyền thống với các loại bánh gạo lứt, bánh sampa, bánh nhện vừng... thơm ngon, giòn rụm.',
                'artisans' => 'Nghệ nhân Đông Ngàn: "Bánh được làm từ tâm huyết, giữ nguyên hương vị quê hương."',
                'ingredients' => ['Gạo lứt', 'Vừng', 'Đường kính', 'Bột mì'],
                'fun_fact' => 'Thương hiệu liên tục đạt chuẩn OCOP từ năm 2022 đến 2025.',
                'audio_narrative' => 'Bánh kẹo Đông Ngàn từ lâu đã nức tiếng gần xa, mang đậm hương vị ngọt ngào của quê hương Kinh Bắc.',
                'nearby_attractions' => [],
                'timeline' => [
                    ['year' => 'Năm 2022 - 2025', 'event' => 'Liên tục đạt chứng nhận OCOP cho các dòng sản phẩm bánh kẹo.']
                ]
            ],
            'tuong-viet-hung-htx-dich-vu-nong-nghiep-thon-doai' => [
                'ocop_stars' => 3,
                'heritage_year' => 'Di sản ẩm thực lâu đời',
                'story' => 'Tương Việt Hùng mang hương vị mộc mạc, đậm đà bản sắc. Được làm từ gạo nếp cái hoa vàng, đỗ tương và ủ chum sành theo phương pháp thủ công.',
                'artisans' => 'Người dân thôn Đoài: "Chum tương là linh hồn của bữa cơm quê."',
                'ingredients' => ['Gạo nếp cái hoa vàng', 'Đỗ tương', 'Muối tinh'],
                'fun_fact' => 'Tương được ủ nắng tự nhiên, càng để lâu càng thơm ngon.',
                'audio_narrative' => 'Hương vị tương Việt Hùng gắn liền với những bữa cơm sum vầy, là nét đẹp văn hóa ẩm thực không thể thiếu.',
                'nearby_attractions' => [],
                'timeline' => [
                    ['year' => 'Năm 2022', 'event' => 'Đạt chứng nhận OCOP 3 sao.']
                ]
            ],
            'banh-ngot-thuy-quyen' => [
                'ocop_stars' => 3,
                'heritage_year' => 'Năm 2023',
                'story' => 'Bánh ngọt Thúy Quyên nổi tiếng với bánh xốp vừng, bánh sampa, giữ trọn vẹn hương vị tuổi thơ của người dân Đông Anh.',
                'artisans' => 'Chủ cơ sở Thúy Quyên: "Mỗi chiếc bánh là một niềm vui mang đến cho khách hàng."',
                'ingredients' => ['Bột mì', 'Trứng', 'Đường', 'Vừng'],
                'fun_fact' => 'Bánh trứng nhện là món ăn vặt không thể thiếu trong các dịp lễ tết tại địa phương.',
                'audio_narrative' => 'Đến với Đông Anh, đừng quên thưởng thức bánh ngọt Thúy Quyên - hương vị ngọt ngào của quê hương.',
                'nearby_attractions' => [],
                'timeline' => [
                    ['year' => 'Năm 2023', 'event' => 'Đạt chứng nhận OCOP.']
                ]
            ],
            'ruou-long-tuu-hkd-thao-loan' => [
                'ocop_stars' => 4,
                'heritage_year' => 'Hương vị gia truyền',
                'story' => 'Rượu Long Tửu (rượu gạo nếp, rượu dâu, rượu mơ) được chưng cất thủ công với men lá tự nhiên, êm dịu và đậm đà.',
                'artisans' => 'Gia đình Thạo Loan: "Nấu rượu là nghệ thuật, đòi hỏi sự tỉ mỉ từ khâu chọn gạo đến ủ men."',
                'ingredients' => ['Gạo nếp', 'Men lá truyền thống', 'Quả dâu, mơ tươi'],
                'fun_fact' => 'Rượu ủ chum sành hạ thổ giúp loại bỏ andehit, uống không đau đầu.',
                'audio_narrative' => 'Nhấp một ngụm Rượu Long Tửu, ta như cảm nhận được tinh hoa của đất trời và sự cần mẫn của người thợ nấu rượu.',
                'nearby_attractions' => [],
                'timeline' => [
                    ['year' => 'Năm 2023 - 2025', 'event' => 'Liên tiếp các sản phẩm rượu được công nhận OCOP.']
                ]
            ],
            'htx-co-loa' => [
                'ocop_stars' => 3,
                'heritage_year' => 'Nông nghiệp Cổ Loa',
                'story' => 'HTX Cổ Loa cung cấp các nông sản sạch như hành lá, khoai tây, bí đỏ... nuôi dưỡng từ mảnh đất lịch sử giàu phù sa.',
                'artisans' => 'Xã viên HTX Cổ Loa: "Sản xuất nông nghiệp sạch là trách nhiệm của chúng tôi."',
                'ingredients' => ['Nông sản sạch Cổ Loa'],
                'fun_fact' => 'Sản phẩm được trồng theo tiêu chuẩn VietGAP.',
                'audio_narrative' => 'Nông sản Cổ Loa mang đến những bữa ăn an toàn và dinh dưỡng cho mọi gia đình.',
                'nearby_attractions' => [],
                'timeline' => [
                    ['year' => 'Năm 2024', 'event' => 'Đạt chứng nhận OCOP.']
                ]
            ],
            'gao-nep-cai-hoa-vang-duc-tu' => [
                'ocop_stars' => 4,
                'heritage_year' => 'Năm 2024',
                'story' => 'Gạo nếp cái hoa vàng Dục Tú nổi tiếng hạt tròn, dẻo thơm, là nguyên liệu tuyệt hảo cho các món xôi, bánh chưng truyền thống.',
                'artisans' => 'Nông dân Dục Tú: "Giống lúa nếp được lưu giữ qua nhiều thế hệ."',
                'ingredients' => ['Gạo nếp cái hoa vàng thuần chủng'],
                'fun_fact' => 'Hương thơm của lúa nếp Dục Tú lan tỏa khắp xóm làng vào mùa gặt.',
                'audio_narrative' => 'Gạo nếp cái hoa vàng Dục Tú - hạt ngọc của trời, kết tinh từ giọt mồ hôi của người nông dân.',
                'nearby_attractions' => [],
                'timeline' => [
                    ['year' => 'Năm 2024', 'event' => 'Đạt chứng nhận OCOP.']
                ]
            ],
            'co-so-san-xuat-thuc-pham-liem-hiep' => [
                'ocop_stars' => 3,
                'heritage_year' => 'Năm 2025',
                'story' => 'Giò lụa, chả lụa Liêm Hiệp giữ nguyên công thức truyền thống, thịt heo tươi ngon giã tay cho độ giòn dai hoàn hảo.',
                'artisans' => 'Nghệ nhân Liêm Hiệp: "Chất lượng làm nên thương hiệu."',
                'ingredients' => ['Thịt heo tươi', 'Nước mắm ngon', 'Lá chuối'],
                'fun_fact' => 'Sản phẩm hoàn toàn không sử dụng hàn the.',
                'audio_narrative' => 'Khoanh giò lụa Liêm Hiệp thơm mùi thịt và lá chuối, mang đậm nét văn hóa ẩm thực Việt Nam.',
                'nearby_attractions' => [],
                'timeline' => [
                    ['year' => 'Năm 2025', 'event' => 'Sản phẩm Giò lụa, Chả lụa đạt chứng nhận OCOP.']
                ]
            ]
        ];

        return $dossiers[$this->slug] ?? null;
    }

    /**
     * Category Relationship
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Commune Relationship
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Dishes Menu Relationship
     */
    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    /**
     * Rooms Relationship
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Wellness Services Relationship
     */
    public function wellnessServices(): HasMany
    {
        return $this->hasMany(WellnessService::class);
    }

    /**
     * OCOP Products Relationship
     */
    public function ocopProducts(): HasMany
    {
        return $this->hasMany(OcopProduct::class);
    }

    /**
     * Education Programs Relationship
     */
    public function educationPrograms(): HasMany
    {
        return $this->hasMany(EducationProgram::class);
    }

    /**
     * Reviews Relationship
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Video Reviews Relationship
     */
    public function reviewVideos(): HasMany
    {
        return $this->hasMany(ReviewVideo::class);
    }

    /**
     * Food Safety Certificate Relationship (1-1)
     */
    public function foodSafetyCertificate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FoodSafetyCertificate::class);
    }

    /**
     * Food Supply Contracts Relationship (1-N)
     */
    public function foodSupplyContracts(): HasMany
    {
        return $this->hasMany(FoodSupplyContract::class)->orderBy('signed_at', 'desc');
    }

    /**
     * Purchase Invoices Relationship (1-N)
     */
    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class)->orderBy('invoice_date', 'desc');
    }

    /**
     * Daily Food Logs Relationship (1-N)
     */
    public function dailyFoodLogs(): HasMany
    {
        return $this->hasMany(DailyFoodLog::class)->orderBy('log_date', 'desc');
    }

    /**
     * Cultural Activities Relationship (1-N)
     */
    public function culturalActivities(): HasMany
    {
        return $this->hasMany(CulturalActivity::class);
    }

    /**
     * Eatery Photos Gallery Relationship (1-N)
     */
    public function photos(): HasMany
    {
        return $this->hasMany(EateryPhoto::class)->orderBy('sort_order');
    }
}
