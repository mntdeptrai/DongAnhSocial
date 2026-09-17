<!-- Interactive Split Screen Layout -->
<section class="split-screen" style="border-top: 1px solid var(--border-glow);">
    
    <!-- Left column: Scrollable feed of eateries -->
    <div class="split-list">
        <div id="listHeaderContainer">
            @if($selectedCatSlug === 'dong-anh-market')
                <div style="margin-bottom: 20px; border-bottom: 1.5px dashed rgba(212, 175, 55, 0.3); padding-bottom: 16px;">
                    <span class="heritage-badge" style="margin-bottom: 8px; font-size: 0.7rem; font-weight: 800; letter-spacing: 1.5px; border: 1px solid rgba(212, 175, 55, 0.4); background: rgba(212, 175, 55, 0.1); color: #ffb300; padding: 4px 10px; border-radius: 20px; display: inline-block;">🌾 NÔNG SẢN SỐ & ĐẶC SẢN OCOP / DIGITAL AGRICULTURAL & OCOP SPECIALTIES</span>
                    <h2 style="font-size: 1.6rem; font-family: var(--font-heading); font-weight: 800; margin: 4px 0 6px 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <span style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Không Gian Nông Sản Số Đông Anh <span style="font-size: 1.1rem; color: var(--text-muted); font-weight: 600; display: block; margin-top: 4px;">(Nông Sản Số & Local Specialties)</span></span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">
                            ({{ (isset($ocopProducts) && $ocopProducts->count() > 0) ? $ocopProducts->count() . ' sản phẩm OCOP' : $eateries->count() . ' địa điểm / places' }})
                        </span>
                    </h2>
                    <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                        Khám phá các sản phẩm OCOP đặc trưng, quà lưu niệm độc đáo, nông sản sạch mang đậm hồn quê Đông Anh. <span style="display: block; font-style: italic; margin-top: 4px; font-size: 0.8rem; opacity: 0.8;">Discover signature OCOP products, unique souvenirs, organic agriculture filled with Đông Anh's cultural soul.</span>
                    </p>

                    <!-- Banner Trình Diễn Story Liên Hoàn Tất Cả Sản Phẩm OCOP -->
                    <div style="margin-top: 14px; background: linear-gradient(135deg, rgba(217, 119, 6, 0.15) 0%, rgba(5, 150, 105, 0.2) 100%); border: 1.5px solid rgba(251, 191, 36, 0.5); border-radius: 14px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; box-shadow: 0 8px 20px rgba(217, 119, 6, 0.15);">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.8rem;">🎬</span>
                            <div>
                                <h4 style="margin: 0; color: #d97706; font-size: 0.98rem; font-weight: 800; font-family: var(--font-heading);">HÀNH TRÌNH TỔNG THỂ DI SẢN OCOP ĐÔNG ANH</h4>
                                <p style="margin: 2px 0 0 0; color: var(--text-muted); font-size: 0.8rem;">Xem trình diễn liên hoàn tất cả các vùng nguyên liệu & sản phẩm OCOP đạt sao trên bản đồ</p>
                            </div>
                        </div>
                        <button type="button" onclick="window.openOcopFullHeritageStory()" style="background: linear-gradient(135deg, #d97706 0%, #059669 100%); border: none; color: #ffffff; padding: 9px 18px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4); transition: all 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                            <span>🌾 Xem Story Tất Cả OCOP</span> ➔
                        </button>
                    </div>
                </div>
            @elseif($selectedCatSlug === 'traditional-market')
                <div class="traditional-market-hero-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span class="market-badge-chip">
                            <span>🏪</span> CHỢ SỐ
                        </span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #0284c7; background: rgba(14, 165, 233, 0.1); padding: 4px 12px; border-radius: 20px;">
                            📍 {{ $totalCount ?? $eateries->count() }} Chợ Quê & Trung Tâm Thương Mại
                        </span>
                    </div>
                    
                    <h2 class="market-hero-title">
                        Hệ Thống Chợ Số Đông Anh
                    </h2>
                    
                    <p style="font-size: 0.92rem; color: #475569; line-height: 1.6; margin: 0;">
                        Khám phá nét đẹp văn hóa Chợ Quê Đông Anh kết hợp công nghệ Chuyển Đổi Số. Tra cứu sơ đồ gian hàng, bảng giá nông sản sạch, thanh toán quét mã VietQR không dùng tiền mặt và giao hàng tận nơi.
                    </p>

                    <div class="market-stats-pills">
                        <div class="market-stat-pill">
                            <span class="icon">✨</span> 100% Gian hàng chuẩn hóa
                        </div>
                        <div class="market-stat-pill">
                            <span class="icon">📲</span> Thanh toán VietQR / Chuyển khoản
                        </div>
                        <div class="market-stat-pill">
                            <span class="icon">🌱</span> Nông sản ATTP
                        </div>
                        <div class="market-stat-pill">
                            <span class="icon">🗺️</span> Sơ đồ gian hàng 2D
                        </div>
                    </div>
                </div>
            @elseif($selectedCatSlug === 'dong-anh-food-map')
                <div class="food-hero-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span class="food-badge-chip">
                            <span>🍜</span> ẨM THỰC ĐÔNG ANH
                        </span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #ea580c; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #fdba74; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.1);">
                            📍 {{ $totalCount ?? $eateries->count() }} Quán Ngon & Nhà Hàng Nổi Tiếng
                        </span>
                    </div>
                    
                    <h2 class="food-hero-title">
                        Bản Đồ Khám Phá Ẩm Thực Đông Anh
                    </h2>
                    
                    <p style="font-size: 0.92rem; color: #431407; line-height: 1.65; margin: 0;">
                        Thưởng thức hương vị đậm đà đặc sản Đông Anh: Lẩu ếch măng cay, Quán nướng rặng tre, Bún chả làng quê... Đã được xác minh vệ sinh ATTP và đánh giá chất lượng thực tế.
                    </p>

                    <div class="food-stats-pills">
                        <div class="food-stat-pill">
                            <span class="icon">🔥</span> Quán ngon tuyển chọn
                        </div>
                        <div class="food-stat-pill">
                            <span class="icon">⭐</span> Đánh giá thực tế
                        </div>
                        <div class="food-stat-pill">
                            <span class="icon">🛡️</span> Chuẩn VSTP
                        </div>
                        <div class="food-stat-pill">
                            <span class="icon">🛵</span> Đặt món & Chỉ đường
                        </div>
                    </div>
                </div>
            @elseif($selectedCatSlug === 'stay-in-dong-anh')
                <div class="stay-hero-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span class="stay-badge-chip">
                            <span>🏨</span> STAY IN ĐÔNG ANH
                        </span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #be185d; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #fbcfe8; box-shadow: 0 2px 8px rgba(219, 39, 119, 0.1);">
                            📍 {{ $totalCount ?? $eateries->count() }} Địa Điểm Lưu Trú & Khách Sạn
                        </span>
                    </div>
                    <h2 class="stay-hero-title">
                        Không Gian Lưu Trú & Nghỉ Dưỡng Đông Anh
                    </h2>
                    <p style="font-size: 0.92rem; color: #831843; line-height: 1.65; margin: 0;">
                        Trải nghiệm dịch vụ nghỉ dưỡng cao cấp, khách sạn đạt chuẩn, homestay ấm cúng ngợp tràn không gian xanh cho chuyến du lịch Đông Anh hoàn hảo.
                    </p>
                    <div class="stay-stats-pills">
                        <div class="stay-stat-pill"><span class="icon">✨</span> Khách sạn & Homestay</div>
                        <div class="stay-stat-pill"><span class="icon">⭐</span> Đạt chuẩn dịch vụ</div>
                        <div class="stay-stat-pill"><span class="icon">🏊</span> Tiện ích hiện đại</div>
                        <div class="stay-stat-pill"><span class="icon">🛎️</span> Đặt phòng nhanh</div>
                    </div>
                </div>
            @elseif($selectedCatSlug === 'wellness-care')
                <div class="wellness-hero-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span class="wellness-badge-chip">
                            <span>🩺</span> WELLNESS & CARE
                        </span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #047857; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #a7f3d0; box-shadow: 0 2px 8px rgba(5, 150, 105, 0.1);">
                            📍 {{ $totalCount ?? $eateries->count() }} Cơ Sở Y Tế & Spa Chăm Sóc Sức Khỏe
                        </span>
                    </div>
                    <h2 class="wellness-hero-title">
                        Hệ Thống Y Tế & Chăm Sóc Sức Khỏe Đông Anh
                    </h2>
                    <p style="font-size: 0.92rem; color: #064e3b; line-height: 1.65; margin: 0;">
                        Tra cứu các bệnh viện uy tín, phòng khám đa khoa chất lượng cao, trung tâm spa & phục hồi sức khỏe được cấp phép và đánh giá hàng đầu.
                    </p>
                    <div class="wellness-stats-pills">
                        <div class="wellness-stat-pill"><span class="icon">🏥</span> Bệnh viện & Phòng khám</div>
                        <div class="wellness-stat-pill"><span class="icon">🌿</span> Spa & Phục hồi sức khỏe</div>
                        <div class="wellness-stat-pill"><span class="icon">👨‍⚕️</span> Bác sĩ chuyên khoa</div>
                        <div class="wellness-stat-pill"><span class="icon">🚑</span> Hỗ trợ Y tế 24/7</div>
                    </div>
                </div>
            @elseif($selectedCatSlug === 'discover-dong-anh-community-culture-hub')
                <div class="culture-hero-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span class="culture-badge-chip">
                            <span>🏛️</span> COMMUNITY & CULTURE HUB
                        </span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #b45309; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #fde047; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.1);">
                            📍 {{ $totalCount ?? $eateries->count() }} Thiết Chế Văn Hóa - Thể Thao
                        </span>
                    </div>
                    <h2 class="culture-hero-title">
                        Trung Tâm Văn Hóa, Thể Thao & Sinh Hoạt Cộng Đồng
                    </h2>
                    <p style="font-size: 0.92rem; color: #78350f; line-height: 1.65; margin: 0;">
                        Không gian giao lưu văn hóa, nhà văn hóa huyện, sân vận động, trung tâm thể dục thể thao và các điểm sinh hoạt cộng đồng năng động Đông Anh.
                    </p>
                    <div class="culture-stats-pills">
                        <div class="culture-stat-pill"><span class="icon">🏛️</span> Nhà văn hóa & Sân vận động</div>
                        <div class="culture-stat-pill"><span class="icon">🎨</span> Triển lãm & Sự kiện</div>
                        <div class="culture-stat-pill"><span class="icon">⚽</span> Khu vui chơi thể thao</div>
                        <div class="culture-stat-pill"><span class="icon">🤝</span> Kết nối cộng đồng</div>
                    </div>
                </div>
            @elseif($selectedCatSlug === 'smart-education-map')
                <div class="edu-hero-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span class="edu-badge-chip">
                            <span>🎓</span> SMART EDUCATION MAP
                        </span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #4338ca; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #a5b4fc; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1);">
                            📍 {{ $totalCount ?? $eateries->count() }} Trường Học & Cơ Sở Giáo Dục
                        </span>
                    </div>
                    <h2 class="edu-hero-title">
                        Hệ Thống Mạng Lưới Giáo Dục & Trường Học Đông Anh
                    </h2>
                    <p style="font-size: 0.92rem; color: #1e1b4b; line-height: 1.65; margin: 0;">
                        Bản đồ thông minh tra cứu hệ thống các trường mầm non, tiểu học, THCS, THPT và trung tâm giáo dục chất lượng cao trên địa bàn xã Đông Anh.
                    </p>
                    <div class="edu-stats-pills">
                        <div class="edu-stat-pill"><span class="icon">🏫</span> Trường đạt chuẩn Quốc gia</div>
                        <div class="edu-stat-pill"><span class="icon">📚</span> Cơ sở vật chất hiện đại</div>
                        <div class="edu-stat-pill"><span class="icon">👩‍🏫</span> Đội ngũ giáo viên giỏi</div>
                        <div class="edu-stat-pill"><span class="icon">🗺️</span> Chỉ đường trường học</div>
                    </div>
                </div>
            @elseif($selectedCatSlug === 'co-so-kinh-doanh')
                <div class="business-hero-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span class="business-badge-chip">
                            <span>🏪</span> CƠ SỞ KINH DOANH & DOANH NGHIỆP
                        </span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #0284c7; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #7dd3fc; box-shadow: 0 2px 8px rgba(14, 165, 233, 0.1);">
                            📍 {{ $totalCount ?? $eateries->count() }} Hộ Kinh Doanh & Doanh Nghiệp Trên Địa Bàn
                        </span>
                    </div>
                    
                    <h2 class="business-hero-title">
                        Hệ Thống Cơ Sở Kinh Doanh & Doanh Nghiệp Đông Anh
                    </h2>
                    
                    <p style="font-size: 0.92rem; color: #334155; line-height: 1.65; margin: 0;">
                        Tra cứu danh bạ Hộ kinh doanh cá thể, Cửa hàng dịch vụ, Siêu thị mini và Doanh nghiệp trên địa bàn xã Đông Anh. Kết nối giao thương số, công khai minh bạch mã số thuế và hỗ trợ chuyển đổi số bán lẻ toàn diện.
                    </p>

                    <div class="business-stats-pills">
                        <div class="business-stat-pill">
                            <span class="icon">🏢</span> 100% Xác thực MST & Ngành nghề
                        </div>
                        <div class="business-stat-pill">
                            <span class="icon">📞</span> Hotline liên hệ trực tiếp
                        </div>
                        <div class="business-stat-pill">
                            <span class="icon">💳</span> Thanh toán VietQR số
                        </div>
                        <div class="business-stat-pill">
                            <span class="icon">🗺️</span> Bản đồ số & Chỉ đường
                        </div>
                    </div>
                </div>
            @else
                <h2 style="font-size: 1.25rem; margin: 6px 0 0 0; font-family: var(--font-heading); font-weight: 700; line-height: 1.4; color: var(--text-main);">
                    <span style="margin-right: 4px;">📍</span> 
                    @if($selectedCatSlug)
                        @php
                            $selectedCat = $categories->where('slug', $selectedCatSlug)->first();
                            $selEn = $selectedCat->name;
                            $selVi = $selectedCat->name;
                            if ($selectedCatSlug === 'dong-anh-food-map') {
                                $selEn = 'DongAnh Discovery';
                                $selVi = 'Bản đồ khám phá đông anh';
                            } elseif ($selectedCatSlug === 'stay-in-dong-anh') {
                                $selEn = 'Stay in Đông Anh';
                                $selVi = 'Nhà nghỉ, khách sạn, khu nghỉ dưỡng';
                            } elseif ($selectedCatSlug === 'wellness-care') {
                                $selEn = 'Wellness & Care';
                                $selVi = 'Y tế – chăm sóc sức khỏe – spa';
                            } elseif ($selectedCatSlug === 'dong-anh-market') {
                                $selEn = 'Nông sản số';
                                $selVi = 'OCOP – quà tặng – đặc sản';
                            } elseif ($selectedCatSlug === 'traditional-market') {
                                $selEn = 'Traditional Market';
                                $selVi = 'Chợ truyền thống';
                            } elseif ($selectedCatSlug === 'smart-education-map') {
                                $selEn = 'Smart Education Map';
                                $selVi = 'Trường học';
                            } elseif ($selectedCatSlug === 'hanh-trinh-di-san') {
                                $selEn = 'Heritage Journey';
                                $selVi = 'Hành trình di sản';
                            } elseif ($selectedCatSlug === 'discover-dong-anh-community-culture-hub') {
                                $selEn = 'Discover Dong Anh Community & Culture Hub';
                                $selVi = 'Khám phá thiết chế văn hóa - thể thao Đông Anh';
                            } elseif ($selectedCatSlug === 'co-so-kinh-doanh') {
                                $selEn = 'Business & Enterprise';
                                $selVi = 'Cơ sở kinh doanh, Doanh nghiệp';
                            }
                        @endphp
                        {{ $selVi }} <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; font-style: italic;">({{ $selEn }})</span>
                    @else
                        Địa điểm nổi bật <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; font-style: italic;">(Featured Places)</span>
                    @endif
                    <span id="resultsCountSpan" style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-left: 6px; display: inline-block; white-space: nowrap;">
                        ({{ $totalCount ?? $eateries->count() }} địa điểm / places)
                    </span>
                </h2>
            @endif
        </div>
        
        <!-- Thanh Tìm Kiếm Trực Tiếp Tại Khu Vực Danh Mục -->
        <div class="inline-section-search-wrapper" style="margin-bottom: 20px; width: 100%;">
            <form action="/tim-kiem" method="GET" class="inline-search-form" onsubmit="return handleInlineSearchSubmit(event)">
                <div class="inline-search-box" style="position: relative; display: flex; align-items: center; background: #ffffff; border: 2px solid #0284c7; border-radius: 16px; padding: 4px 6px 4px 16px; box-shadow: 0 4px 20px rgba(2, 132, 199, 0.12); transition: all 0.25s ease;">
                    <span style="font-size: 1.2rem; margin-right: 10px; color: #0284c7;">🔍</span>
                    <input type="text" name="q" id="inlineSectionSearchInput" class="inline-search-input" placeholder="Nhập để tìm kiếm..." autocomplete="off" style="flex: 1; border: none; outline: none; font-size: 0.95rem; font-weight: 600; color: #0f172a; background: transparent; padding: 10px 0;">
                    <button type="button" id="inlineSearchClearBtn" onclick="clearInlineSearch()" style="display: none; background: #f1f5f9; border: none; color: #64748b; font-weight: 700; font-size: 0.85rem; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; margin-right: 8px; align-items: center; justify-content: center;" title="Xóa từ khóa">✕</button>
                    <button type="submit" class="inline-search-submit-btn" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border: none; font-weight: 800; font-size: 0.88rem; padding: 10px 22px; border-radius: 12px; cursor: pointer; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25); transition: transform 0.2s ease;">Tìm kiếm</button>
                </div>
            </form>
        </div>

        <div id="eateriesListContainer" style="display: flex; flex-direction: column; gap: 24px; width: 100%;">
            @if($eateries->count() > 0)
                @php
                    if (!function_exists('getSmartBusinessImgHelper')) {
                        function getSmartBusinessImgHelper($name, $desc = '') {
                            $text = mb_strtolower($name . ' ' . $desc);
                            if (preg_match('/(thuốc|y tế|phòng khám|bác sĩ|nha khoa|pharmacy|clinic|medical|dược)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=600&q=80';
                            }
                            if (preg_match('/(spa|cắt tóc|làm đầu|gội đầu|nail|beauty|salon|barber|massage|thẩm mỹ)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=600&q=80';
                            }
                            if (preg_match('/(cơ khí|sửa chữa|ô tô|mô tô|xe máy|phụ tùng|kim loại|hàn|nhôm kính|đúc|sắt)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=600&q=80';
                            }
                            if (preg_match('/(may mặc|quần áo|thời trang|giày|dép|vải|boutique|clothing|fashion)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80';
                            }
                            if (preg_match('/(điện tử|máy tính|điện thoại|laptop|mobile|viễn thông|điện máy|điện gia dụng|camera)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=600&q=80';
                            }
                            if (preg_match('/(cà phê|cafe|coffee|trà|đồ uống|bánh|bakery|quán ăn|ẩm thực|nhà hàng|bún|phở|cơm|lẩu|nướng)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=600&q=80';
                            }
                            if (preg_match('/(xây dựng|vật liệu|xi măng|gạch|sơn|nội thất|gỗ|kính)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=600&q=80';
                            }
                            if (preg_match('/(nhà đất|bất động sản|cho thuê|quản lý nhà|mặt bằng|văn phòng|land|real estate)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=600&q=80';
                            }
                            if (preg_match('/(công ty|tnhh|cổ phần|doanh nghiệp|tập đoàn|enterprise)/ui', $text)) {
                                return 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&q=80';
                            }
                            return 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80';
                        }
                    }
                @endphp
                @foreach($eateries as $eat)
                    @php
                        $isMarket = ($eat->category->slug === 'traditional-market');
                        $displayCards = [];

                        if ($eat->category->slug === 'dong-anh-market') {
                            // 1. Kiểm tra nếu có sản phẩm OCOP trong DB
                            if ($eat->ocopProducts && $eat->ocopProducts->count() > 0) {
                                foreach ($eat->ocopProducts as $p) {
                                    $displayCards[] = [
                                        'title' => $p->name,
                                        'product_id' => $p->id,
                                        'subtitle' => 'Chủ thể sản xuất: ' . ($p->seller_name ?: $eat->name),
                                        'desc' => $p->description ?: $eat->description,
                                        'image' => $p->image_path ?: ($eat->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80'),
                                        'stars' => $p->star_rating ? (str_contains($p->star_rating, 'sao') ? $p->star_rating : $p->star_rating . ' sao') : ($eat->average_rating ?: '5.0'),
                                        'price' => $p->price ? (is_numeric($p->price) ? number_format($p->price, 0, ',', '.') . 'đ' : $p->price) : $eat->price_range,
                                        'badgeText' => $p->star_rating ? '⭐ ' . $p->star_rating : 'Đặc sản OCOP',
                                        'badgeIcon' => '🌾',
                                    ];
                                }
                            } 
                            // 2. Tách tên sản phẩm từ mô tả có định dạng "tên sản phẩm OCOP: sản phẩm 1, sản phẩm 2..."
                            elseif (preg_match('/tên\s+sản\s+phẩm\s+OCOP:\s*([^;]+)/ui', $eat->description, $matches)) {
                                $rawProducts = array_filter(array_map('trim', explode(',', $matches[1])));
                                $cleanDesc = preg_replace('/tên\s+sản\s+phẩm\s+OCOP:\s*[^;]+;?\s*/ui', '', $eat->description);
                                $cleanDesc = preg_replace('/^[^;]+;\s*địa chỉ[^;]+;\s*/ui', '', $cleanDesc);
                                if (empty(trim($cleanDesc))) $cleanDesc = $eat->description;

                                foreach ($rawProducts as $pName) {
                                    $displayCards[] = [
                                        'title' => $pName,
                                        'subtitle' => 'Chủ thể sản xuất: ' . $eat->name,
                                        'desc' => $cleanDesc,
                                        'image' => $eat->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80',
                                        'stars' => $eat->average_rating ?: '5.0',
                                        'price' => $eat->price_range,
                                        'badgeText' => 'Đặc sản OCOP',
                                        'badgeIcon' => '🌾',
                                    ];
                                }
                            } 
                            // 3. Fallback: Loại bỏ chữ HKD/HTX tiền tố để tạo tên sản phẩm
                            else {
                                $cleanName = preg_replace('/^(HKD|HTX|Hộ kinh doanh|Cơ sở|Công ty)\s+/ui', '', $eat->name);
                                $displayCards[] = [
                                    'title' => 'Sản phẩm OCOP - ' . $cleanName,
                                    'subtitle' => 'Chủ thể sản xuất: ' . $eat->name,
                                    'desc' => $eat->description,
                                    'image' => $eat->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80',
                                    'stars' => $eat->average_rating ?: '5.0',
                                    'price' => $eat->price_range,
                                    'badgeText' => 'Đặc sản OCOP',
                                    'badgeIcon' => '🌾',
                                ];
                            }
                        } else {
                            $cardImg = $eat->image_path;
                            if (!$cardImg) {
                                if ($eat->category->slug === 'co-so-kinh-doanh') {
                                    $cardImg = getSmartBusinessImgHelper($eat->name, $eat->description);
                                } else {
                                    $cardImg = 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80';
                                }
                            }
                            $displayCards[] = [
                                'title' => $eat->name,
                                'subtitle' => null,
                                'desc' => $eat->description,
                                'image' => $cardImg,
                                'stars' => $eat->average_rating ?: '5.0',
                                'price' => $eat->price_range,
                                'badgeText' => $eat->category->name,
                                'badgeIcon' => $eat->category->icon,
                            ];
                        }
                    @endphp

                    @foreach($displayCards as $card)
                        @php 
                            $isOcopItem = ($eat->category->slug === 'dong-anh-market'); 
                            $isFoodItem = ($eat->category->slug === 'dong-anh-food-map');
                            $isStayItem = ($eat->category->slug === 'stay-in-dong-anh');
                            $isWellnessItem = ($eat->category->slug === 'wellness-care');
                            $isCultureItem = ($eat->category->slug === 'discover-dong-anh-community-culture-hub');
                            $isEduItem = ($eat->category->slug === 'smart-education-map');
                            $isBusinessItem = ($eat->category->slug === 'co-so-kinh-doanh');
                            $isCustomStyled = ($isMarket || $isOcopItem || $isFoodItem || $isStayItem || $isWellnessItem || $isCultureItem || $isEduItem || $isBusinessItem);
                        @endphp
                        <div class="eatery-card glass-panel reveal reveal-fade-up hover-lift {{ $isOcopItem ? 'ocop-card-highlight' : '' }} {{ $isMarket ? 'market-card-highlight' : '' }} {{ $isFoodItem ? 'food-card-highlight' : '' }} {{ $isStayItem ? 'stay-card-highlight' : '' }} {{ $isWellnessItem ? 'wellness-card-highlight' : '' }} {{ $isCultureItem ? 'culture-card-highlight' : '' }} {{ $isEduItem ? 'edu-card-highlight' : '' }} {{ $isBusinessItem ? 'business-card-highlight' : '' }}" 
                             data-slug="{{ $eat->slug }}"
                             data-name="{{ $card['title'] }}"
                             data-address="{{ $eat->address }}"
                             data-desc="{{ $card['desc'] }}"
                             data-commune="{{ $eat->commune?->name ?? 'Đông Anh' }}"
                             data-taxcode="{{ $eat->storytelling_data['tax_code'] ?? '' }}"
                             data-owner="{{ $eat->storytelling_data['owner_name'] ?? '' }}"
                             data-phone="{{ $eat->phone ?? '' }}"
                             data-category="{{ $eat->category->slug }}"
                             onclick="focusOnEatery({{ number_format($eat->latitude, 6, '.', '') }}, {{ number_format($eat->longitude, 6, '.', '') }}, '{{ $eat->slug }}', '{{ addslashes($card['title']) }}', '{{ $card['image'] }}', '{{ $card['price'] }}', '{{ $card['stars'] }}', '{{ addslashes($card['subtitle'] ?? '') }}')">
                            <div class="eatery-img-wrapper hover-zoom-container">
                                <img src="{{ $card['image'] }}" class="eatery-img hover-zoom-img" alt="{{ $card['title'] }}" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80';">
                                @if(!$isCustomStyled)
                                    <div style="position: absolute; top: 8px; left: 8px; max-width: calc(100% - 16px); display: flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 700; color: #ffffff; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); padding: 4px 8px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);">
                                        <span>{{ $card['badgeIcon'] }}</span>
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $card['badgeText'] }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="eatery-info">
                                @if($isOcopItem)
                                    <div style="margin-bottom: 4px;">
                                        <span class="ocop-title-badge">🌾 ĐẶC SẢN OCOP</span>
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title ocop-product-title">{{ $card['title'] }}</h3>
                                        <div class="ocop-star-tag">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @elseif($isMarket)
                                    <div style="margin-bottom: 4px;">
                                        <span class="market-title-badge">🏪 CHỢ SỐ</span>
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title">{{ $card['title'] }}</h3>
                                        <div class="rating-stars">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @elseif($isFoodItem)
                                    <div style="margin-bottom: 4px;">
                                        <span class="food-title-badge">🍜 QUÁN NGON NỔI BẬT</span>
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title">{{ $card['title'] }}</h3>
                                        <div class="rating-stars" style="color: #f59e0b; font-weight: 800;">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @elseif($isStayItem)
                                    <div style="margin-bottom: 4px;">
                                        <span class="stay-title-badge">🏨 LƯU TRÚ DỊCH VỤ</span>
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title">{{ $card['title'] }}</h3>
                                        <div class="rating-stars" style="color: #db2777; font-weight: 800;">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @elseif($isWellnessItem)
                                    <div style="margin-bottom: 4px;">
                                        <span class="wellness-title-badge">🩺 CHĂM SÓC SỨC KHỎE</span>
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title">{{ $card['title'] }}</h3>
                                        <div class="rating-stars" style="color: #059669; font-weight: 800;">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @elseif($isCultureItem)
                                    <div style="margin-bottom: 4px;">
                                        <span class="culture-title-badge">🏛️ THIẾT CHẾ VĂN HÓA</span>
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title">{{ $card['title'] }}</h3>
                                        <div class="rating-stars" style="color: #d97706; font-weight: 800;">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @elseif($isEduItem)
                                    <div style="margin-bottom: 4px;">
                                        <span class="edu-title-badge">🎓 CƠ SỞ GIÁO DỤC</span>
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title">{{ $card['title'] }}</h3>
                                        <div class="rating-stars" style="color: #4f46e5; font-weight: 800;">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @elseif($isBusinessItem)
                                    @php
                                        $taxCode = $eat->storytelling_data['tax_code'] ?? null;
                                        $isEnterprise = str_contains(mb_strtoupper($eat->name), 'CÔNG TY') || ($eat->storytelling_data['business_type'] ?? '') === 'Doanh nghiệp';
                                    @endphp
                                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; margin-bottom: 4px;">
                                        <span class="business-title-badge" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; font-size: 0.68rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            {{ $isEnterprise ? '🏢 DOANH NGHIỆP' : '🏪 HỘ KINH DOANH' }}
                                        </span>
                                        @if($taxCode)
                                            <span class="business-mst-tag" style="font-size: 0.68rem; color: #0284c7; background: rgba(2, 132, 199, 0.1); padding: 2px 8px; border-radius: 6px;">
                                                🆔 MST: {{ $taxCode }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title" style="color: #0f172a; font-size: 1.18rem; font-weight: 900; letter-spacing: -0.2px;">{{ $card['title'] }}</h3>
                                        <div class="rating-stars" style="color: #0284c7; font-weight: 800;">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @else
                                    <div class="eatery-header">
                                        <h3 class="eatery-title">{{ $card['title'] }}</h3>
                                        <div class="rating-stars">
                                            <span>⭐</span> {{ $card['stars'] }}
                                        </div>
                                    </div>
                                @endif

                                @if($card['subtitle'])
                                <div class="{{ $isOcopItem ? 'ocop-seller-badge' : '' }}" style="{{ !$isOcopItem ? 'font-size: 0.75rem; font-weight: 700; color: var(--primary); margin-top: -2px; margin-bottom: 6px;' : '' }}">
                                    🏛️ {{ $card['subtitle'] }}
                                </div>
                                @endif

                                @if(!empty($card['desc']) && $card['desc'] !== 'null')
                                <p class="eatery-desc">{{ $card['desc'] }}</p>
                                @endif

                                <div class="eatery-footer">
                                    <div class="eatery-meta-item">
                                        <span>📍</span> {{ $eat->commune?->name ?? 'Đông Anh' }}
                                    </div>
                                    @if($isBusinessItem && $eat->phone)
                                        <a href="tel:{{ $eat->phone }}" class="business-phone-tag" onclick="event.stopPropagation();" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.8rem;">
                                            <span>📞</span> {{ $eat->phone }}
                                        </a>
                                    @elseif(!in_array($eat->category->slug, ['smart-education-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']))
                                    <div class="eatery-meta-item {{ $isOcopItem ? 'ocop-price-tag' : '' }}" style="{{ !$isOcopItem ? 'color: var(--primary); font-weight: 700;' : '' }}">
                                        {{ $card['price'] }}
                                    </div>
                                    @endif
                                </div>
                                @if($isOcopItem)
                                    <a href="{{ isset($card['product_id']) ? route('ocop.product.show', $card['product_id']) : route('eatery.show', $eat->slug) }}" class="ocop-explore-btn" onclick="event.stopPropagation();">
                                        <span>🌾 Xem Chi Tiết Sản Phẩm OCOP</span> ➔
                                    </a>
                                @elseif($isMarket)
                                    <a href="{{ route('eatery.show', $eat->slug) }}" class="market-explore-btn" onclick="event.stopPropagation();">
                                        <span>🛒 Xem Gian Hàng Số & Sơ Đồ Chợ</span> ➔
                                    </a>
                                @elseif($isFoodItem)
                                    <a href="{{ route('eatery.show', $eat->slug) }}" class="food-explore-btn" onclick="event.stopPropagation();">
                                        <span>🍽️ Xem Thực Đơn & Chỉ Đường</span> ➔
                                    </a>
                                @elseif($isStayItem)
                                    <a href="{{ route('eatery.show', $eat->slug) }}" class="stay-explore-btn" onclick="event.stopPropagation();">
                                        <span>🏨 Xem Chi Tiết & Đặt Phòng</span> ➔
                                    </a>
                                @elseif($isWellnessItem)
                                    <a href="{{ route('eatery.show', $eat->slug) }}" class="wellness-explore-btn" onclick="event.stopPropagation();">
                                        <span>🩺 Xem Dịch Vụ & Đặt Lịch</span> ➔
                                    </a>
                                @elseif($isCultureItem)
                                    <a href="{{ route('eatery.show', $eat->slug) }}" class="culture-explore-btn" onclick="event.stopPropagation();">
                                        <span>🏛️ Khám Phá Hoạt Động & Sự Kiện</span> ➔
                                    </a>
                                @elseif($isEduItem)
                                    <div class="edu-card-actions">
                                        <button type="button" class="edu-story-btn" onclick="event.stopPropagation(); window.openSchoolStoryteller('{{ $eat->slug }}', '{{ route('eatery.show', $eat->slug) }}');">
                                            <span>📖 Xem Story</span>
                                        </button>
                                        <a href="{{ route('eatery.show', $eat->slug) }}" class="edu-explore-btn" onclick="event.stopPropagation();">
                                            <span>🎓 Tra Cứu Thông Tin Trường</span> ➔
                                        </a>
                                    </div>
                                @elseif($isBusinessItem)
                                    <a href="{{ route('eatery.show', $eat->slug) }}" class="business-explore-btn" onclick="event.stopPropagation();" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border-radius: 8px; font-size: 0.78rem; font-weight: 700; text-decoration: none; margin-top: 10px; transition: all 0.2s;">
                                        <span>🏪 Xem Chi Tiết Cơ Sở & Dịch Vụ</span> ➔
                                    </a>
                                @else
                                    <a href="{{ route('eatery.show', $eat->slug) }}" class="btn-primary" onclick="event.stopPropagation();" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; text-decoration: none; margin-top: 10px;">
                                        <span>Xem Chi Tiết</span> ➔
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            @else
                <div class="glass-panel" style="padding: 40px; text-align: center; color: var(--text-muted); width: 100%;">
                    <p style="font-size: 1.2rem; margin-bottom: 8px;">😔 Không tìm thấy địa điểm nào phù hợp</p>
                    <p style="font-size: 0.9rem;">Hãy thử lọc danh mục khác hoặc xóa bộ lọc để khám phá lại toàn bộ Đông Anh!</p>
                    <a href="/" class="btn-primary" style="margin-top: 16px; padding: 8px 16px; text-decoration: none; display: inline-block;">Xem tất cả</a>
                </div>
            @endif
        </div>

        <!-- Infinite Scroll Sentinel & Loading Indicator -->
        <div id="infiniteScrollSentinel" style="width: 100%; height: 20px; margin-top: 10px;"></div>
        <div id="infiniteScrollLoader" style="display: none; padding: 18px 16px; text-align: center; color: var(--primary); font-weight: 700; font-size: 0.9rem; background: rgba(var(--primary-rgb), 0.05); border-radius: 12px; margin-top: 10px; border: 1px dashed rgba(var(--primary-rgb), 0.3);">
            <span style="display: inline-block; animation: spin 1s linear infinite; margin-right: 8px;">⏳</span> Đang tải thêm địa điểm tiếp theo...
        </div>
        <div id="allLoadedIndicator" style="{{ (isset($totalCount) && count($eateries) >= $totalCount) ? 'display: block;' : 'display: none;' }} padding: 16px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; background: rgba(0,0,0,0.03); border-radius: 12px; margin-top: 10px;">
            ✨ Bạn đã xem toàn bộ <span id="totalLoadedSpan">{{ $totalCount ?? count($eateries) }}</span> địa điểm tại Đông Anh!
        </div>
    </div>
    
    <!-- Right column: Premium Leaflet map -->
    <div class="split-map-container" style="position: relative;">
        <div id="map"></div>
        
        <!-- Floating Tóp Tóp Reels FAB removed -->
    </div>
<!-- Dedicated OCOP Product Detail Modal -->
<div id="homeOcopProductModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.82); backdrop-filter: blur(14px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <div style="background: #ffffff; color: #1e293b; width: 92%; max-width: 820px; max-height: 90vh; border-radius: 20px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5); overflow: hidden; transform: scale(0.92); transition: transform 0.3s ease; display: flex; flex-direction: column; position: relative; border: 2px solid #059669;">
        
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: linear-gradient(135deg, #064e3b 0%, #047857 100%); color: #ffffff;">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span style="background: #fbbf24; color: #78350f; font-weight: 800; font-size: 0.7rem; padding: 3px 10px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">🌾 SẢN PHẨM OCOP CHỨNG NHẬN</span>
                <h3 id="hpmName" style="margin: 0; font-size: 1.25rem; font-weight: 800; font-family: var(--font-heading); color: #ffffff;"></h3>
            </div>
            <button onclick="closeHomeOcopModal()" style="background: rgba(255,255,255,0.2); border: none; font-size: 1.2rem; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">✕</button>
        </div>

        <!-- Scrollable Modal Content -->
        <div style="overflow-y: auto; padding: 24px; flex: 1;">
            
            <!-- Hero Top Row: Image + Main Card Info -->
            <div class="ocop-popup-hero">
                <!-- Product Image -->
                <div class="ocop-popup-img-col">
                    <img id="hpmImg" src="" style="width: 100%; height: 100%; object-fit: cover;" alt="">
                </div>

                <!-- Product Quick Specs & Seller -->
                <div class="ocop-popup-info-col">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px;">
                            <span id="hpmStars" style="background: #fef3c7; color: #d97706; border: 1px solid #fde68a; font-weight: 800; font-size: 0.85rem; padding: 4px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px;"></span>
                            <span id="hpmPrice" style="font-size: 1.2rem; font-weight: 800; color: #059669;"></span>
                        </div>

                        <!-- Seller Info Box -->
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px 16px; border-radius: 12px; margin-bottom: 12px;">
                            <strong style="color: #166534; font-size: 0.85rem; display: block; margin-bottom: 2px;">🏛️ Chủ thể sản xuất / Hộ kinh doanh:</strong>
                            <span id="hpmSeller" style="font-weight: 700; color: #0f172a; font-size: 0.95rem;"></span>
                            <div id="hpmAddress" style="font-size: 0.82rem; color: #475569; margin-top: 4px;"></div>
                        </div>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a id="hpmCallBtn" href="tel:" style="flex: 1; min-width: 140px; background: #059669; color: #ffffff; text-align: center; padding: 10px 16px; border-radius: 10px; font-weight: 700; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);">
                            📞 Liên hệ chủ sạp
                        </a>
                        <a id="hpmEateryLink" href="#" style="background: #f1f5f9; color: #334155; padding: 10px 16px; border-radius: 10px; font-weight: 700; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1;">
                            🏪 Trang gian hàng
                        </a>
                    </div>
                </div>
            </div>

            <!-- Detailed Content Sections -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Description Section -->
                <div>
                    <h4 style="font-size: 1rem; font-weight: 800; color: #064e3b; margin-bottom: 8px; border-left: 4px solid #059669; padding-left: 10px; font-family: var(--font-heading);">
                        📝 Thông Tin Chi Tiết & Nguồn Gốc Sản Phẩm
                    </h4>
                    <div id="hpmDesc" style="font-size: 0.92rem; color: #334155; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; white-space: pre-line;">
                    </div>
                </div>

                <!-- Ingredients & Secret Section (If available) -->
                <div id="hpmIngSection" style="display: none;">
                    <h4 style="font-size: 1rem; font-weight: 800; color: #064e3b; margin-bottom: 8px; border-left: 4px solid #d97706; padding-left: 10px; font-family: var(--font-heading);">
                        🌾 Thành Phần & Bí Quyết Sản Xuất
                    </h4>
                    <div id="hpmIngGrid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Fullscreen Reels Modal -->
<div id="reelsModal" class="reels-overlay" style="display: none;">

    <div class="reels-container glass-panel">
        <div class="reels-video-wrapper">
            <!-- Dynamic video / iframe player container -->
            <div id="reelPlayerWrapper" style="width: 100%; height: 100%; position: absolute; inset: 0; z-index: 1;"></div>
            
            <!-- Instagram-style top progress bars -->
            <div id="reelsProgressBars" style="position: absolute; top: 12px; left: 16px; right: 16px; display: flex; gap: 4px; z-index: 15;"></div>
            
            <!-- Interactive inner next/prev navigation buttons -->
            <button id="modalPrevReelBtn" onclick="searchPrevReel()" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.25); width: 40px; height: 40px; border-radius: 50%; color: #fff; cursor: pointer; display: none; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.2s; z-index: 12; backdrop-filter: blur(8px); pointer-events: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.4);" onmouseover="this.style.background='rgba(0,0,0,0.75)'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(0,0,0,0.55)'; this.style.transform='translateY(-50%) scale(1)';">◀</button>
            <button id="modalNextReelBtn" onclick="searchNextReel()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.25); width: 40px; height: 40px; border-radius: 50%; color: #fff; cursor: pointer; display: none; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.2s; z-index: 12; backdrop-filter: blur(8px); pointer-events: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.4);" onmouseover="this.style.background='rgba(0,0,0,0.75)'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(0,0,0,0.55)'; this.style.transform='translateY(-50%) scale(1)';">▶</button>
            
            <div class="reels-header-controls" style="z-index: 10; margin-top: 6px;">
                <span class="reels-badge-live">🎥 REVIEW THỰC TẾ</span>
                <button class="reels-close-btn" style="pointer-events: auto;" onclick="closeReelsModal()">✕</button>
            </div>
            
            <div class="reels-overlay-info" style="z-index: 10; bottom: 20px; left: 16px; right: 80px;">
                <h3 class="reels-eatery-name" id="reelsEateryName" style="font-size: 1.05rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.95); margin: 0; font-family: var(--font-heading); color: #ffffff;"></h3>
                <p class="reels-desc" id="reelsVideoDesc" style="display: none !important;"></p>
                <span class="reels-signature-tag" style="display: none !important;"></span>
            </div>
            
            <!-- Double click/Tap overlay to fly hearts -->
            <div id="reelTapOverlay" style="position: absolute; inset: 0; z-index: 5; pointer-events: auto;" onclick="triggerDoubleTapHeart(event)"></div>
        </div>
        
        <!-- Right Action sidebar (Premium Glassmorphic Cinema style) -->
        <div class="reels-side-actions" style="right: 14px; bottom: 50px; gap: 14px;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <button type="button" class="reels-action-btn" id="reelsLikeBtn" onclick="toggleReelsLike()" style="background: rgba(255, 255, 255, 0.12); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-2px)'; this.style.backgroundColor='rgba(255, 255, 255, 0.25)';" onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='rgba(255, 255, 255, 0.12)';">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#ff3366" stroke="#ff3366" stroke-width="2" style="filter: drop-shadow(0 0 4px rgba(255, 51, 102, 0.6));"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </button>
                <span class="reels-action-label" id="reelsLikeCount" style="margin-top: 6px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.8); font-size: 0.72rem;">3.8K</span>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center;">
                <button type="button" class="reels-action-btn" onclick="alert('Đã thêm quán ăn này vào Danh sách Yêu thích của bạn!')" style="background: rgba(255, 255, 255, 0.12); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-2px)'; this.style.backgroundColor='rgba(255, 255, 255, 0.25)';" onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='rgba(255, 255, 255, 0.12)';">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#ffb800" stroke="#ffb800" stroke-width="2" style="filter: drop-shadow(0 0 4px rgba(255, 184, 0, 0.6));"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </button>
                <span class="reels-action-label" style="margin-top: 6px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.8); font-size: 0.72rem;">4.8</span>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center;">
                <button type="button" class="reels-action-btn" onclick="navigator.clipboard.writeText(window.location.href); alert('Đã sao chép liên kết chia sẻ review của quán!');" style="background: rgba(255, 255, 255, 0.12); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-2px)'; this.style.backgroundColor='rgba(255, 255, 255, 0.25)';" onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='rgba(255, 255, 255, 0.12)';">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 0 3px rgba(255,255,255,0.4));"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                </button>
                <span class="reels-action-label" style="margin-top: 6px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.8); font-size: 0.72rem;">Chia sẻ</span>
            </div>
            
            <div class="reels-music-disc" style="border: 2px solid var(--primary); background: radial-gradient(circle, var(--primary) 30%, #000 70%); font-size: 0.95rem; box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.5);">🍜</div>
        </div>
    </div>
</div>

