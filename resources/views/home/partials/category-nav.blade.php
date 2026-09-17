
<!-- Categories Slider -->
<div class="categories-container-wrap">
    <div class="categories-slider">
        <a href="/" class="category-card glass-panel {{ !$selectedCatSlug ? 'active' : '' }}">
            <span class="cat-icon">🗺️</span>
            <span class="cat-name">
                <span class="cat-title-en">TẤT CẢ ĐỊA ĐIỂM</span>
                <span class="cat-title-vi">Toàn bộ bản đồ</span>
            </span>
        </a>
        <a href="/tuyen-duong-40" class="category-card glass-panel tuyen-duong-highlight-card" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important; color: #ffffff !important; border: 1.5px solid #34d399 !important; position: relative;">
            <span class="badge-tag" style="position: absolute; top: 6px; right: 6px; background: #fbbf24; color: #78350f; font-size: 0.65rem; font-weight: 900; padding: 2px 7px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.25); letter-spacing: 0.5px; z-index: 2;">LIVE 4.0</span>
            <span class="cat-icon">🛣️</span>
            <span class="cat-name">
                <span class="cat-title-en" style="color: #ffffff !important; font-weight: 800; text-shadow: 0 1px 2px rgba(0,0,0,0.3); background: none !important; -webkit-text-fill-color: #ffffff !important;">TUYẾN ĐƯỜNG 4.0</span>
                <span class="cat-title-vi" style="color: rgba(255,255,255,0.95) !important; font-weight: 600;">Bản đồ tuyến đường số</span>
            </span>
        </a>
        <a href="https://sapxepthon.xadonganh.com/" target="_blank" rel="noopener noreferrer" class="category-card glass-panel digital-map-highlight-card" id="digital-map-external-tab" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important; color: #ffffff !important; border: 1.5px solid #60a5fa !important; position: relative;" title="Bấm để mở Bản đồ số Xã Đông Anh">
            <span class="badge-tag" style="position: absolute; top: 6px; right: 6px; background: #38bdf8; color: #0c4a6e; font-size: 0.65rem; font-weight: 900; padding: 2px 7px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.25); letter-spacing: 0.5px; z-index: 2;">3D LINK</span>
            <span class="cat-icon">🌐</span>
            <span class="cat-name">
                <span class="cat-title-en" style="color: #ffffff !important; font-weight: 800; text-shadow: 0 1px 2px rgba(0,0,0,0.3); background: none !important; -webkit-text-fill-color: #ffffff !important;">BẢN ĐỒ SỐ</span>
                <span class="cat-title-vi" style="color: rgba(255,255,255,0.95) !important; font-weight: 600;">Xã Đông Anh</span>
            </span>
        </a>
        @foreach($categories as $cat)
            @if($cat->slug === 'checkin-dong-anh')
                @continue
            @endif
            @php
                $displayIcon = $cat->icon;
                $displayNameEn = $cat->name;
                $displayNameVi = $cat->name;
                if ($cat->slug === 'dong-anh-food-map') {
                    $displayIcon = '🍜';
                    $displayNameEn = 'ẨM THỰC & ĐIỂM ĐẾN';
                    $displayNameVi = 'Quán ăn & nhà hàng';
                } elseif ($cat->slug === 'stay-in-dong-anh') {
                    $displayIcon = '🛌';
                    $displayNameEn = 'LƯU TRÚ & KHÁCH SẠN';
                    $displayNameVi = 'Khách sạn & resort';
                } elseif ($cat->slug === 'wellness-care') {
                    $displayIcon = '🩺';
                    $displayNameEn = 'Y TẾ & CHĂM SÓC';
                    $displayNameVi = 'Sức khỏe & spa';
                } elseif ($cat->slug === 'dong-anh-market') {
                    $displayIcon = '🌾';
                    $displayNameEn = 'NÔNG SẢN & OCOP';
                    $displayNameVi = 'Đặc sản OCOP';
                } elseif ($cat->slug === 'traditional-market') {
                    $displayIcon = '🏪';
                    $displayNameEn = 'CHỢ TRUYỀN THỐNG';
                    $displayNameVi = '17 Chợ số Đông Anh';
                } elseif ($cat->slug === 'smart-education-map') {
                    $displayIcon = '🎓';
                    $displayNameEn = 'TRƯỜNG HỌC SỐ';
                    $displayNameVi = 'Giáo dục số';
                } elseif ($cat->slug === 'hanh-trinh-di-san') {
                    $displayIcon = '⛩️';
                    $displayNameEn = 'HÀNH TRÌNH DI SẢN';
                    $displayNameVi = 'Di tích lịch sử 360';
                } elseif ($cat->slug === 'discover-dong-anh-community-culture-hub') {
                    $displayIcon = '🏛️';
                    $displayNameEn = 'THIẾT CHẾ VĂN HÓA';
                    $displayNameVi = 'Văn hóa & thể thao';
                } elseif ($cat->slug === 'co-so-kinh-doanh') {
                    $displayIcon = '🏢';
                    $displayNameEn = 'CƠ SỞ KINH DOANH';
                    $displayNameVi = 'HKD & doanh nghiệp';
                }
            @endphp
            @if($cat->slug === 'hanh-trinh-di-san')
                <a href="https://donganh360.vn" target="_blank" rel="noopener noreferrer" class="category-card glass-panel">
                    <img src="{{ asset('images/den_tho_kinh_duong_vuong_thanh_co_luy_lau_.webp') }}" alt="Hành trình di sản" class="cat-icon-img" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover;">
                    <span class="cat-name">
                        <span class="cat-title-en">{{ $displayNameEn }}</span>
                        <span class="cat-title-vi">{{ $displayNameVi }}</span>
                    </span>
                </a>
            @elseif($cat->slug === 'discover-dong-anh-community-culture-hub')
                <a href="/?cat={{ $cat->slug }}" class="category-card glass-panel {{ $selectedCatSlug === $cat->slug ? 'active' : '' }}">
                    <img src="{{ asset('images/nha_van_hoa_dong_anh.webp') }}" alt="Community & Culture Hub" class="cat-icon-img" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover;">
                    <span class="cat-name">
                        <span class="cat-title-en">{{ $displayNameEn }}</span>
                        <span class="cat-title-vi">{{ $displayNameVi }}</span>
                    </span>
                </a>
            @else
                <a href="/?cat={{ $cat->slug }}" class="category-card glass-panel {{ $selectedCatSlug === $cat->slug ? 'active' : '' }} {{ $cat->slug === 'dong-anh-market' ? 'specialty-highlight-card' : '' }}">
                    <span class="cat-icon">{{ $displayIcon }}</span>
                    <span class="cat-name">
                        <span class="cat-title-en">{{ $displayNameEn }}</span>
                        <span class="cat-title-vi">{{ $displayNameVi }}</span>
                    </span>
                </a>
            @endif
        @endforeach
    </div>
</div>

