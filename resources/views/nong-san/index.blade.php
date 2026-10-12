@extends('layouts.food-tour')

@section('title', 'Nông Sản Đông Anh - Tinh Hoa Nông Nghiệp & Đặc Sản Đất Cổ')

@section('meta_description', 'Khám phá và đặt mua nông sản sạch, đặc sản làng nghề truyền thống Đông Anh: Bỏng chủ tiến Vua, Bún Mạch Tràng, Dưa lưới Kim Sơn, Chuối nấu giả cầy, Mâm cỗ xứ Cổ.')

@section('styles')
    @include('nong-san.partials.styles')
@endsection

@section('content')
<div style="background: var(--ns-cream); min-height: 100vh; overflow-x: hidden;">

    {{-- 1. CINEMATIC LUXURY HERO --}}
    @include('nong-san.partials.hero')

    {{-- 2. SPOTLIGHT HERITAGE GRAND BOX (MÂM CỖ CỔ LOA XƯA) --}}
    <section class="ns-spotlight-section">
        <div class="ns-mamco-card">
            {{-- Left column: Story, 4-Bento Menu Categories & Actions --}}
            <div>
                <span class="ns-spotlight-tag" style="background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);">
                    <span>👑</span> Di Sản Ẩm Thực Cố Đô • Mâm Đồng Tròn Cổ Truyền
                </span>
                
                <h2 class="ns-spotlight-title" style="margin-bottom: 6px;">
                    MÂM CỖ CỔ LOA XƯA
                </h2>
                
                <span class="ns-mamco-slogan">
                    “Hương vị từ đất cổ – Nếp quê còn mãi • Một mâm cỗ – Một miền ký ức – Một Cổ Loa nghìn năm văn hiến”
                </span>

                <p class="ns-spotlight-desc" style="margin-bottom: 16px;">
                    Mâm cỗ chính được bài trí cầu kỳ, trang trọng trên <strong>mâm đồng tròn truyền thống</strong>, tái hiện trọn vẹn văn hóa yến tiệc cung đình thời An Dương Vương và nếp nhà quê xứ Cổ, kết thúc bằng tuần <strong>trà mạn truyền thống</strong> thưởng thức cùng <strong>Bỏng chủ</strong> và <strong>Mít Cổ Loa</strong>.
                </p>

                {{-- 4-Bento Categories Grid matching user's exact menu --}}
                <div class="ns-mamco-categories-grid">
                    <div class="ns-mamco-cat-box">
                        <div class="ns-mamco-cat-head">
                            <span>🥩</span> I. MÓN MẶN
                        </div>
                        <p class="ns-mamco-cat-items">
                            • Gà luộc lá chanh • Giò Mo<br>
                            • Thịt lợn trộn thính • Thịt lợn đông<br>
                            • Chuối giả cầy • Bún xào cần • Trám đen om
                        </p>
                    </div>

                    <div class="ns-mamco-cat-box">
                        <div class="ns-mamco-cat-head">
                            <span>🍚</span> II. BÁNH & XÔI
                        </div>
                        <p class="ns-mamco-cat-items">
                            • Xôi trám đen • Bánh Dày<br>
                            • Bánh đúc lạc • Bánh chưng
                        </p>
                    </div>

                    <div class="ns-mamco-cat-box">
                        <div class="ns-mamco-cat-head">
                            <span>🥗</span> III. RAU & CANH
                        </div>
                        <p class="ns-mamco-cat-items">
                            • Rau theo mùa tươi ngọt<br>
                            • Canh khoai tây hầm xương
                        </p>
                    </div>

                    <div class="ns-mamco-cat-box">
                        <div class="ns-mamco-cat-head">
                            <span>🍵</span> IV. TRÁNG MIỆNG & TRÀ
                        </div>
                        <p class="ns-mamco-cat-items">
                            • Bỏng chủ Cổ Loa ngào gừng<br>
                            • Mít Cổ Loa • Trà mạn truyền thống
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @php
                    $spotlightJson = json_encode($spotlightProduct, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <button type="button" class="ns-btn-quick-inquire" style="padding: 13px 24px; font-size: 0.92rem;" onclick="openNsDetailModal({{ $spotlightJson }})">
                        <span>🏮</span> Đặt Trước Mâm Cỗ (Nhận Báo Giá)
                    </button>
                    <button type="button" class="ns-btn-see-detail" style="padding: 12px 20px; font-size: 0.9rem;" onclick="openMamCoLightbox(currentMamCoImage, currentMamCoCaption)">
                        <span>🔍</span> Xem Bản Thực Đơn Cực Nét
                    </button>
                    <a href="tel:0983882119" style="display: inline-flex; align-items: center; gap: 6px; color: #059669; font-weight: 800; font-size: 0.88rem; text-decoration: none; padding: 10px 14px;">
                        <span>📞</span> Hotline: 0983.882.119
                    </a>
                </div>
            </div>

            {{-- Right column: Interactive Image Switcher (Bản Thực Đơn Cổ Ưu Tiên Số 1) --}}
            <div class="ns-mamco-media-card">
                <div class="ns-mamco-tabs-bar">
                    <button type="button" id="tab-btn-thucdon" class="ns-mamco-tab-btn active" onclick="switchMamCoMedia('thucdon')">
                        <span>📜</span> Bản Thực Đơn Cổ
                    </button>
                    <button type="button" id="tab-btn-sodo" class="ns-mamco-tab-btn" onclick="switchMamCoMedia('sodo')">
                        <span>🔘</span> Sơ Đồ Mâm Đồng
                    </button>
                </div>

                <div class="ns-mamco-img-frame" onclick="openMamCoLightbox(currentMamCoImage, currentMamCoCaption)" title="Bấm để phóng to xem chi tiết">
                    <img id="mam-co-main-img" src="https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg" alt="Bản thực đơn cổ truyền Cổ Loa – Nền trống đồng Đông Sơn" loading="lazy">
                    <div class="ns-mamco-img-floating-tag">
                        <span id="mam-co-tag-lbl">📜 Bản thực đơn cổ truyền Cổ Loa</span>
                        <span style="font-size: 0.82rem;">🔍 Phóng to</span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.76rem; color: var(--ns-text-muted);">
                        💡 Bấm vào ảnh hoặc đổi tab để xem chi tiết từng món.
                    </span>
                    <button type="button" onclick="openNsDetailModal({{ $spotlightJson }})" style="background: none; border: none; color: #059669; font-size: 0.78rem; font-weight: 800; cursor: pointer; text-decoration: underline;">
                        Xem điển tích văn hóa ➔
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- 2.5. TRUST PILLARS STRIP (3 KHÔNG & MINH BẠCH BẢN ĐỊA) --}}
    <section class="ns-trust-strip">
        <div class="ns-trust-grid">
            <div class="ns-trust-card">
                <div class="ns-trust-icon-box">🌾</div>
                <div>
                    <h4 class="ns-trust-title">100% Nguồn Gốc Bản Địa</h4>
                    <p class="ns-trust-desc">Gạo nếp hoa vàng giống cổ, men gạo ủ chua truyền thống từ làng nghề Đông Anh.</p>
                </div>
            </div>

            <div class="ns-trust-card">
                <div class="ns-trust-icon-box">🛡️</div>
                <div>
                    <h4 class="ns-trust-title">Tiêu Chuẩn 3 KHÔNG</h4>
                    <p class="ns-trust-desc">Không chất bảo quản - Không phụ gia tạo màu - Không tẩy trắng hóa chất.</p>
                </div>
            </div>

            <div class="ns-trust-card">
                <div class="ns-trust-icon-box">🌿</div>
                <div>
                    <h4 class="ns-trust-title">VietGAP & Làng Nghề</h4>
                    <p class="ns-trust-desc">Nông trại sinh thái công nghệ cao kết hợp tinh hoa nghệ nhân làng cổ trăm năm.</p>
                </div>
            </div>

            <div class="ns-trust-card">
                <div class="ns-trust-icon-box">⚡</div>
                <div>
                    <h4 class="ns-trust-title">Giao Nhanh Tươi Ngon</h4>
                    <p class="ns-trust-desc">Đóng gói chân không giữ trọn hương vị, giao hỏa tốc Hà Nội và gửi hàng toàn quốc.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. STICKY FILTER BAR --}}
    <div class="ns-filter-sticky">
        <div class="ns-filter-container">
            <div class="ns-pills-row">
                <button type="button" class="ns-filter-pill active" data-category="all" onclick="filterProducts('all', this)">
                    <span>✨</span> Tất cả sản vật ({{ $totalCount }})
                </button>
                <button type="button" class="ns-filter-pill" data-category="farm" onclick="filterProducts('farm', this)">
                    <span>🍈</span> Nông sản tươi & Trang trại
                </button>
                <button type="button" class="ns-filter-pill" data-category="noodle" onclick="filterProducts('noodle', this)">
                    <span>🍜</span> Bún Mạch Tràng & Món ngon
                </button>
                <button type="button" class="ns-filter-pill" data-category="cuisine" onclick="filterProducts('cuisine', this)">
                    <span>🍲</span> Ẩm thực & Mâm cơm xứ Cổ
                </button>
                <button type="button" class="ns-filter-pill" data-category="snack" onclick="filterProducts('snack', this)">
                    <span>🎁</span> Quà quê & Đặc sản khô
                </button>
            </div>

            <div class="ns-search-box">
                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--ns-text-muted); font-size: 0.95rem; pointer-events: none;">🔍</span>
                <input type="text" id="nsSearchInput" class="ns-search-field" placeholder="Tìm kiếm nông sản, món ăn, đặc sản..." oninput="searchProducts()">
            </div>
        </div>
    </div>

    {{-- 4. BENTO PRODUCT CARDS GRID --}}
    <main class="ns-catalog-container">
        <div class="ns-cards-grid" id="nsProductGrid">
            @foreach($products as $product)
                @php
                    $productJson = json_encode($product, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp
                <article class="ns-product-card" 
                     data-category="{{ $product['category'] }}"
                     data-name="{{ $product['name'] }}"
                     data-origin="{{ $product['origin'] }}"
                     data-summary="{{ $product['summary'] }}"
                     onclick="openNsDetailModal({{ $productJson }})">
                    
                    {{-- Thumbnail & Badges --}}
                    <div class="ns-thumb-container">
                        <img src="{{ $product['image'] }}" class="ns-thumb-img" alt="{{ $product['name'] }}" loading="lazy" onerror="this.src='{{ asset('images/thanhcoloa.webp') }}'">
                        <div class="ns-thumb-gradient"></div>
                        
                        <span class="ns-badge-floating">
                            {{ $product['badge'] }}
                        </span>

                        <div class="ns-origin-floating">
                            <span>📍</span> {{ $product['origin'] }}
                        </div>
                    </div>

                    {{-- Card Content --}}
                    <div class="ns-card-content">
                        <h3 class="ns-item-name">
                            {{ $product['name'] }}
                        </h3>

                        {{-- Tasting Notes Tags --}}
                        <div class="ns-feature-row">
                            @if(!empty($product['tasting_notes']))
                                @foreach($product['tasting_notes'] as $tag)
                                    <span class="ns-feature-tag">• {{ $tag }}</span>
                                @endforeach
                            @endif
                        </div>

                        <p class="ns-item-summary">
                            {{ $product['summary'] }}
                        </p>

                        {{-- Price Bar (Strictly "Liên hệ", 2-row layout with zero line wrap) --}}
                        <div class="ns-price-display-box">
                            <div class="ns-price-row-main">
                                <span class="ns-price-meta-tag">
                                    <span>🏷️</span> Giá tham khảo
                                </span>
                                <span class="ns-price-val">
                                    {{ $product['price_label'] }}
                                </span>
                            </div>
                            <div class="ns-unit-row">
                                <span>📦</span>
                                <span class="ns-unit-val">{{ $product['unit'] }}</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="ns-card-btns-row" onclick="event.stopPropagation()">
                            <button type="button" class="ns-btn-see-detail" onclick="openNsDetailModal({{ $productJson }})">
                                <span>👁️</span> Chi tiết
                            </button>
                            <button type="button" class="ns-btn-quick-inquire" onclick="openNsDetailModal({{ $productJson }})">
                                <span>🛒</span> Đặt mua
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Empty Search State --}}
        <div id="ns-empty-state" style="display: none; text-align: center; padding: 70px 20px; background: var(--ns-surface); border-radius: 26px; border: 1.5px dashed var(--ns-border-light); margin-bottom: 60px;">
            <span style="font-size: 3.5rem; display: block; margin-bottom: 14px;">🌾</span>
            <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--ns-text-dark); margin-bottom: 8px;">Không tìm thấy sản vật phù hợp</h3>
            <p style="font-size: 0.92rem; color: var(--ns-text-muted); margin-bottom: 22px;">Hãy thử tìm kiếm với các từ khóa như "bún", "bỏng", "dưa lưới", "mâm cỗ", "chuối"...</p>
            <button type="button" class="ns-filter-pill active" onclick="document.getElementById('nsSearchInput').value=''; filterProducts('all', document.querySelector('.ns-filter-pill[data-category=all]'));">
                Xem toàn bộ 10 sản vật Đông Anh
            </button>
        </div>
    </main>

    {{-- 4.2. PROPOSAL 1: CURATED GIFT COMBOS --}}
    @if(!empty($combos))
    <section class="ns-combos-container">
        <div class="ns-section-header">
            <span class="ns-section-tag">
                <span>🎁</span> Quà Quê Tri Ân & Biếu Tặng
            </span>
            <h2 class="ns-section-title">
                Bộ Sưu Tập Giỏ Quà & Combo Tiến Vua
            </h2>
            <p class="ns-section-desc">
                Đóng gói trang trọng trong hộp mây tre đan hoặc hộp quà mỹ thuật cao cấp, kết hợp trọn vẹn những sản vật tinh hoa nhất của đất cố đô Cổ Loa và nông trại sinh thái Đông Anh.
            </p>
        </div>

        <div class="ns-combos-grid">
            @foreach($combos as $combo)
                @php
                    $comboJson = json_encode([
                        'name'          => $combo['name'],
                        'price_label'   => $combo['price_label'],
                        'unit'          => 'Set hộp quà biếu cao cấp',
                        'badge'         => $combo['tag'],
                        'origin'        => 'Đông Anh, Hà Nội',
                        'image'         => $combo['image'],
                        'summary'       => $combo['description'],
                        'story'         => $combo['description'] . ' Lý tưởng cho: ' . $combo['ideal_for'],
                        'highlights'    => $combo['items'],
                        'contact_phone' => $combo['contact_phone'],
                        'zalo'          => $combo['zalo'],
                        'experience_tip'=> $combo['ideal_for'],
                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp
                <div class="ns-combo-card">
                    <div class="ns-combo-media">
                        <img src="{{ $combo['image'] }}" alt="{{ $combo['name'] }}" loading="lazy">
                        <span class="ns-combo-badge-top">{{ $combo['badge'] }}</span>
                    </div>

                    <div class="ns-combo-body">
                        <span style="font-size: 0.75rem; font-weight: 800; color: #d97706; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">
                            {{ $combo['tag'] }}
                        </span>
                        <h3 class="ns-combo-name">{{ $combo['name'] }}</h3>
                        <p class="ns-combo-desc">{{ $combo['description'] }}</p>

                        <div class="ns-combo-items-box">
                            <span class="ns-combo-items-box-title">
                                <span>🧺</span> Thành phần trong set:
                            </span>
                            <ul class="ns-combo-items-list">
                                @foreach($combo['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Combo Price Bar (Strictly "Liên hệ") --}}
                        <div class="ns-price-display-box" style="margin-top: auto;">
                            <div class="ns-price-row-main">
                                <span class="ns-price-meta-tag">
                                    <span>🏷️</span> Giá tham khảo
                                </span>
                                <span class="ns-price-val">
                                    {{ $combo['price_label'] }}
                                </span>
                            </div>
                            <div class="ns-unit-row">
                                <span>💡</span>
                                <span class="ns-unit-val" style="font-size: 0.76rem; color: #047857;">{{ $combo['ideal_for'] }}</span>
                            </div>
                        </div>

                        <div class="ns-card-btns-row" style="margin-top: 10px;">
                            <button type="button" class="ns-btn-see-detail" onclick="openNsDetailModal({{ $comboJson }})">
                                <span>👁️</span> Chi tiết set
                            </button>
                            <button type="button" class="ns-btn-quick-inquire" onclick="openNsDetailModal({{ $comboJson }})">
                                <span>🛒</span> Báo giá set quà
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 4.3. PROPOSAL 2: SEASONAL HARVEST CALENDAR --}}
    @if(!empty($seasons))
    <section class="ns-calendar-container">
        <div class="ns-section-header">
            <span class="ns-section-tag" style="background: rgba(16, 185, 129, 0.12); color: #059669; border-color: rgba(16, 185, 129, 0.35);">
                <span>📅</span> Mùa Nào Thức Nấy
            </span>
            <h2 class="ns-section-title">
                Lịch Mùa Vụ & Ẩm Thực 4 Mùa Đông Anh
            </h2>
            <p class="ns-section-desc">
                Đất bãi sông Hồng màu mỡ cùng khí hậu 4 mùa mang đến những sản vật đặc trưng, ngon nhất khi đúng vào mùa thu hoạch rộ.
            </p>
        </div>

        <div class="ns-calendar-grid">
            @foreach($seasons as $season)
                <div class="ns-season-card">
                    <div class="ns-season-header-box">
                        <div class="ns-season-icon">{{ $season['icon'] }}</div>
                        <div>
                            <span class="ns-season-lbl">{{ $season['season'] }}</span>
                            <h3 class="ns-season-title">{{ $season['title'] }}</h3>
                        </div>
                    </div>

                    <p class="ns-season-desc">{{ $season['description'] }}</p>

                    <ul class="ns-season-bullets">
                        @foreach($season['highlights'] as $highlight)
                            <li>{{ $highlight }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 4.4. PROPOSAL 3: FARM-TO-TABLE EXPERIENCE CTA --}}
    <section class="ns-experience-cta-wrap">
        <div class="ns-experience-cta-box">
            <div>
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 14px; border-radius: 50px; background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: #fef08a; margin-bottom: 12px;">
                    <span>🚜</span> Du Lịch Canh Tác Trải Nghiệm
                </span>
                <h3 class="ns-experience-cta-title">
                    Từ Nông Trại & Làng Nghề Đến Bàn Ăn
                </h3>
                <p class="ns-experience-cta-desc">
                    Không chỉ đặt mua mang về, hãy đến tận nơi để cùng bà con tự tay thu hoạch dưa lưới tại <strong>Kim Sơn Farm</strong>, trải nghiệm kéo sợi bún chua thủ công ở <strong>Làng nghề Mạch Tràng</strong>, và ngào mẻ bỏng nếp ấm nồng trong nếp nhà cổ Cổ Loa.
                </p>
            </div>

            <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 10px; min-width: 210px;">
                <a href="/exp-corner" class="ns-btn-quick-inquire" style="text-decoration: none; padding: 15px 24px; font-size: 0.95rem; text-align: center;">
                    <span>🗺️</span> Khám Phá Tour Trải Nghiệm
                </a>
                <a href="tel:0983882119" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #a7f3d0; font-weight: 700; font-size: 0.88rem; padding: 8px;">
                    <span>📞</span> Đặt lịch tour: 0983.882.119
                </a>
            </div>
        </div>
    </section>

    {{-- 5. EDITORIAL SHOWCASE MODAL --}}
    @include('nong-san.partials.modal')

    {{-- 6. LIGHTBOX MODAL FOR FULL RESOLUTION MENU IMAGES --}}
    <div id="mamCoLightbox" class="ns-lightbox-modal" style="display: none;" onclick="closeMamCoLightbox()">
        <button type="button" class="ns-close-x-btn" onclick="closeMamCoLightbox()" style="top: 24px; right: 24px;" aria-label="Đóng">✕</button>
        <div class="ns-lightbox-content" onclick="event.stopPropagation()">
            <img id="lightbox-img" src="" alt="Ảnh thực đơn phóng to">
            <div id="lightbox-caption" class="ns-lightbox-caption"></div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
    @include('nong-san.partials.scripts')
@endsection
