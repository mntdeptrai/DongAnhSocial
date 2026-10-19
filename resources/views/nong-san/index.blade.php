@extends('layouts.food-tour')

@section('title', 'Nông Sản Đông Anh - Tinh Hoa Nông Nghiệp & Đặc Sản Đất Cổ')

@section('meta_description', 'Khám phá và đặt mua nông sản sạch, đặc sản làng nghề truyền thống Đông Anh: Bỏng chủ tiến Vua, Bún Mạch Tràng, Dưa lưới Kim Sơn, Chuối nấu giả cầy, Mâm cỗ Cổ Loa xưa.')

@section('styles')
    @include('nong-san.partials.styles')
@endsection

@section('content')
<div class="ns-page-wrapper">

    {{-- 1. CINEMATIC LUXURY HERO --}}
    @include('nong-san.partials.hero')

    {{-- 2. SPOTLIGHT HERITAGE GRAND BOX (MÂM CỖ CỔ LOA XƯA) --}}
    <section id="ns-spotlight-anchor" class="ns-spotlight-section">
        <div class="ns-mamco-card">
            {{-- Left column: Story, 4-Course Royal Bento Categories & Actions --}}
            <div class="ns-mamco-left">
                <div class="ns-spotlight-badge-wrap">
                    <span class="ns-spotlight-tag">
                        <span class="ns-tag-crown" aria-hidden="true">👑</span> Di Sản Ẩm Thực Cố Đô • Mâm Đồng Tròn Cổ Truyền
                    </span>
                </div>
                
                <h2 class="ns-spotlight-title">
                    MÂM CỖ CỔ LOA XƯA
                </h2>
                
                <p class="ns-mamco-slogan">
                    “Hương vị từ đất cổ – Nếp quê còn mãi • Một mâm cỗ – Một miền ký ức – Một Cổ Loa nghìn năm văn hiến”
                </p>

                <p class="ns-spotlight-desc">
                    Mâm cỗ chính được bài trí trang trọng, đủ đầy trên <strong>mâm đồng tròn truyền thống</strong>, tái hiện trọn vẹn văn hóa yến tiệc cung đình thời An Dương Vương và nếp nhà quê xứ Cổ, kết thúc viên mãn bằng tuần <strong>trà mạn cổ truyền</strong> thưởng thức cùng <strong>Bỏng chủ ngào gừng</strong> và <strong>Mít Cổ Loa</strong>.
                </p>

                {{-- 4-Course Royal Bento Grid --}}
                <div class="ns-mamco-categories-grid">
                    <div class="ns-mamco-cat-box ns-cat-man">
                        <div class="ns-mamco-cat-head">
                            <span class="ns-cat-icon">🥩</span> I. MÓN MẶN
                        </div>
                        <div class="ns-mamco-cat-chips">
                            <span class="ns-cat-chip">Gà luộc lá chanh</span>
                            <span class="ns-cat-chip">Giò Mo</span>
                            <span class="ns-cat-chip">Thịt lợn trộn thính</span>
                            <span class="ns-cat-chip">Thịt lợn đông</span>
                            <span class="ns-cat-chip">Chuối giả cầy</span>
                            <span class="ns-cat-chip">Bún xào cần</span>
                            <span class="ns-cat-chip">Trám đen om</span>
                        </div>
                    </div>

                    <div class="ns-mamco-cat-box ns-cat-banh">
                        <div class="ns-mamco-cat-head">
                            <span class="ns-cat-icon">🍚</span> II. BÁNH & XÔI
                        </div>
                        <div class="ns-mamco-cat-chips">
                            <span class="ns-cat-chip">Xôi trám đen</span>
                            <span class="ns-cat-chip">Bánh Dày</span>
                            <span class="ns-cat-chip">Bánh đúc lạc</span>
                            <span class="ns-cat-chip">Bánh chưng</span>
                        </div>
                    </div>

                    <div class="ns-mamco-cat-box ns-cat-rau">
                        <div class="ns-mamco-cat-head">
                            <span class="ns-cat-icon">🥗</span> III. RAU & CANH
                        </div>
                        <div class="ns-mamco-cat-chips">
                            <span class="ns-cat-chip">Rau theo mùa tươi ngọt</span>
                            <span class="ns-cat-chip">Canh khoai tây hầm xương</span>
                        </div>
                    </div>

                    <div class="ns-mamco-cat-box ns-cat-tra">
                        <div class="ns-mamco-cat-head">
                            <span class="ns-cat-icon">🍵</span> IV. TRÁNG MIỆNG & TRÀ
                        </div>
                        <div class="ns-mamco-cat-chips">
                            <span class="ns-cat-chip">Bỏng chủ ngào gừng</span>
                            <span class="ns-cat-chip">Mít Cổ Loa</span>
                            <span class="ns-cat-chip">Trà mạn truyền thống</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @php
                    $spotlightJson = json_encode($spotlightProduct, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp
                <div class="ns-mamco-actions-row">
                    <button type="button" class="ns-btn-primary" onclick="openNsDetailModal({{ $spotlightJson }})">
                        <span>🏮</span> Đặt Trước Mâm Cỗ (Nhận Báo Giá)
                    </button>
                    <button type="button" class="ns-btn-secondary" onclick="openMamCoLightbox(currentMamCoImage, currentMamCoCaption)">
                        <span>🔍</span> Xem Bản Thực Đơn Cực Nét
                    </button>
                    <a href="tel:0983882119" class="ns-hotline-pill">
                        <span class="ns-hotline-icon">📞</span>
                        <span>Hotline: <strong>0983.882.119</strong></span>
                    </a>
                </div>
            </div>

            {{-- Right column: Interactive Dual-Media Showcase --}}
            <div class="ns-mamco-media-card">
                <div class="ns-mamco-tabs-bar" role="tablist">
                    <button type="button" id="tab-btn-thucdon" class="ns-mamco-tab-btn active" onclick="switchMamCoMedia('thucdon')" role="tab" aria-selected="true">
                        <span>📜</span> Bản Thực Đơn Cổ
                    </button>
                    <button type="button" id="tab-btn-sodo" class="ns-mamco-tab-btn" onclick="switchMamCoMedia('sodo')" role="tab" aria-selected="false">
                        <span>🔘</span> Sơ Đồ Mâm Đồng
                    </button>
                </div>

                <div class="ns-mamco-img-frame" onclick="openMamCoLightbox(currentMamCoImage, currentMamCoCaption)" title="Bấm để phóng to xem cực nét">
                    <img id="mam-co-main-img" src="https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg" alt="Bản thực đơn cổ truyền Cổ Loa – Nền trống đồng Đông Sơn" loading="lazy">
                    <div class="ns-mamco-img-floating-tag">
                        <span id="mam-co-tag-lbl">📜 Bản thực đơn cổ truyền Cổ Loa</span>
                        <span class="ns-zoom-badge">🔍 Chạm phóng to</span>
                    </div>
                </div>
                
                <div class="ns-mamco-caption-bar">
                    <span class="ns-caption-hint">
                        💡 Bấm vào ảnh hoặc đổi tab để khám phá sơ đồ bài trí mâm đồng.
                    </span>
                    <button type="button" class="ns-story-link-btn" onclick="openNsDetailModal({{ $spotlightJson }})">
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
                <div class="ns-trust-content">
                    <h4 class="ns-trust-title">100% Nguồn Gốc Bản Địa</h4>
                    <p class="ns-trust-desc">Gạo nếp cái hoa vàng thuần giống cổ, men gạo ủ chua truyền thống từ làng nghề Đông Anh.</p>
                </div>
            </div>

            <div class="ns-trust-card">
                <div class="ns-trust-icon-box">🛡️</div>
                <div class="ns-trust-content">
                    <h4 class="ns-trust-title">Tiêu Chuẩn 3 KHÔNG</h4>
                    <p class="ns-trust-desc">Không chất bảo quản - Không phụ gia tạo màu - Không tẩy trắng bằng hóa chất công nghiệp.</p>
                </div>
            </div>

            <div class="ns-trust-card">
                <div class="ns-trust-icon-box">🌿</div>
                <div class="ns-trust-content">
                    <h4 class="ns-trust-title">VietGAP & Làng Nghề</h4>
                    <p class="ns-trust-desc">Nông trại sinh thái công nghệ cao kết hợp bàn tay tài hoa của nghệ nhân làng cổ trăm năm.</p>
                </div>
            </div>

            <div class="ns-trust-card">
                <div class="ns-trust-icon-box">⚡</div>
                <div class="ns-trust-content">
                    <h4 class="ns-trust-title">Giao Nhanh Tươi Ngon</h4>
                    <p class="ns-trust-desc">Đóng gói chân không giữ trọn hương vị tươi mới, giao hỏa tốc Hà Nội và gửi hàng toàn quốc.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. STICKY FILTER & SEARCH BAR --}}
    <div id="ns-catalog-anchor" class="ns-filter-sticky">
        <div class="ns-filter-container">
            <div class="ns-pills-row" role="tablist">
                <button type="button" class="ns-filter-pill active" data-category="all" onclick="filterProducts('all', this)">
                    <span class="ns-pill-icon">✨</span> Tất cả sản vật ({{ $totalCount }})
                </button>
                <button type="button" class="ns-filter-pill" data-category="farm" onclick="filterProducts('farm', this)">
                    <span class="ns-pill-icon">🍈</span> Nông sản tươi & Trang trại
                </button>
                <button type="button" class="ns-filter-pill" data-category="noodle" onclick="filterProducts('noodle', this)">
                    <span class="ns-pill-icon">🍜</span> Bún Mạch Tràng & Món ngon
                </button>
                <button type="button" class="ns-filter-pill" data-category="cuisine" onclick="filterProducts('cuisine', this)">
                    <span class="ns-pill-icon">🍲</span> Ẩm thực & Mâm cỗ xứ Cổ
                </button>
                <button type="button" class="ns-filter-pill" data-category="snack" onclick="filterProducts('snack', this)">
                    <span class="ns-pill-icon">🎁</span> Quà quê & Đặc sản khô
                </button>
            </div>

            <div class="ns-search-box">
                <span class="ns-search-icon" aria-hidden="true">🔍</span>
                <input type="text" id="nsSearchInput" class="ns-search-field" placeholder="Tìm sản vật, món ăn, đặc sản..." oninput="searchProducts()" aria-label="Tìm kiếm sản phẩm">
                <button type="button" id="nsSearchClear" class="ns-search-clear-btn" onclick="clearSearch()" aria-label="Xóa tìm kiếm" style="display: none;">✕</button>
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
                    
                    {{-- Thumbnail & Floating Badges --}}
                    <div class="ns-thumb-container">
                        <img src="{{ $product['image'] }}" class="ns-thumb-img" alt="{{ $product['name'] }}" loading="lazy" onerror="this.src='{{ asset('images/thanhcoloa.webp') }}'">
                        <div class="ns-thumb-gradient" aria-hidden="true"></div>
                        
                        <span class="ns-badge-floating">
                            {{ $product['badge'] }}
                        </span>

                        <div class="ns-origin-floating">
                            <span class="ns-origin-icon" aria-hidden="true">📍</span> {{ $product['origin'] }}
                        </div>
                    </div>

                    {{-- Card Content Body --}}
                    <div class="ns-card-content">
                        <h3 class="ns-item-name" title="{{ $product['name'] }}">
                            {{ $product['name'] }}
                        </h3>

                        {{-- Tasting Notes Chips --}}
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

                        {{-- Price Bar (Strictly "Liên hệ" with Non-Breaking Text) --}}
                        <div class="ns-price-display-box">
                            <div class="ns-price-row-main">
                                <span class="ns-price-meta-tag">
                                    <span aria-hidden="true">🏷️</span> Giá tham khảo
                                </span>
                                <span class="ns-price-val">
                                    {{ $product['price_label'] }}
                                </span>
                            </div>
                            <div class="ns-unit-row">
                                <span class="ns-unit-icon" aria-hidden="true">📦</span>
                                <span class="ns-unit-val">{{ $product['unit'] }}</span>
                            </div>
                        </div>

                        {{-- Card Action Buttons --}}
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
        <div id="ns-empty-state" class="ns-empty-state-box" style="display: none;">
            <span class="ns-empty-icon" aria-hidden="true">🌾</span>
            <h3 class="ns-empty-title">Không tìm thấy sản vật phù hợp</h3>
            <p class="ns-empty-desc">Hãy thử tìm với các từ khóa phổ biến: "bún", "bỏng", "dưa lưới", "mâm cỗ", "chuối", "bánh đúc"...</p>
            <button type="button" class="ns-btn-primary" onclick="clearSearchAndShowAll()">
                Xem toàn bộ {{ $totalCount }} sản vật Đông Anh
            </button>
        </div>
    </main>

    {{-- 4.2. PROPOSAL 1: CURATED GIFT COMBOS --}}
    @if(!empty($combos))
    <section id="ns-combos-anchor" class="ns-combos-container">
        <div class="ns-section-header">
            <span class="ns-section-tag">
                <span aria-hidden="true">🎁</span> Quà Quê Tri Ân & Biếu Tặng
            </span>
            <h2 class="ns-section-title">
                Bộ Sưu Tập Giỏ Quà & Combo Tiến Vua
            </h2>
            <p class="ns-section-desc">
                Đóng gói trang trọng trong giỏ mây tre đan thủ công hoặc hộp quà mỹ thuật cao cấp, kết hợp trọn vẹn những sản vật tinh hoa nhất của đất cố đô Cổ Loa và nông trại sinh thái Đông Anh.
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
                <div class="ns-combo-card" onclick="openNsDetailModal({{ $comboJson }})">
                    <div class="ns-combo-media">
                        <img src="{{ $combo['image'] }}" alt="{{ $combo['name'] }}" loading="lazy">
                        <span class="ns-combo-badge-top">{{ $combo['badge'] }}</span>
                    </div>

                    <div class="ns-combo-body">
                        <span class="ns-combo-tag-lbl">
                            {{ $combo['tag'] }}
                        </span>
                        <h3 class="ns-combo-name">{{ $combo['name'] }}</h3>
                        <p class="ns-combo-desc">{{ $combo['description'] }}</p>

                        <div class="ns-combo-items-box">
                            <span class="ns-combo-items-box-title">
                                <span aria-hidden="true">🧺</span> Thành phần trong set quà:
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
                                    <span aria-hidden="true">🏷️</span> Giá tham khảo
                                </span>
                                <span class="ns-price-val">
                                    {{ $combo['price_label'] }}
                                </span>
                            </div>
                            <div class="ns-unit-row">
                                <span aria-hidden="true">💡</span>
                                <span class="ns-unit-val" style="color: #047857;">{{ $combo['ideal_for'] }}</span>
                            </div>
                        </div>

                        <div class="ns-card-btns-row" style="margin-top: 12px;" onclick="event.stopPropagation()">
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
            <span class="ns-section-tag ns-tag-calendar">
                <span aria-hidden="true">📅</span> Mùa Nào Thức Nấy
            </span>
            <h2 class="ns-section-title">
                Lịch Mùa Vụ & Ẩm Thực 4 Mùa Đông Anh
            </h2>
            <p class="ns-section-desc">
                Đất bãi sông Hồng màu mỡ cùng khí hậu bốn mùa đặc trưng mang đến những sản vật tươi ngon nhất khi vào mùa thu hoạch rộ.
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
            <div class="ns-experience-cta-text">
                <span class="ns-experience-badge">
                    <span aria-hidden="true">🚜</span> Du Lịch Canh Tác Trải Nghiệm
                </span>
                <h3 class="ns-experience-cta-title">
                    Từ Nông Trại & Làng Nghề Đến Bàn Ăn
                </h3>
                <p class="ns-experience-cta-desc">
                    Không chỉ đặt mua mang về, hãy đến tận nơi để cùng bà con tự tay thu hoạch dưa lưới tại <strong>Kim Sơn Farm</strong>, trải nghiệm kéo sợi bún chua thủ công ở <strong>Làng nghề Mạch Tràng</strong>, và ngào mẻ bỏng nếp ấm nồng trong nếp nhà cổ Cổ Loa.
                </p>
            </div>

            <div class="ns-experience-cta-actions">
                <a href="/exp-corner" class="ns-btn-experience-primary">
                    <span>🗺️</span> Khám Phá Tour Trải Nghiệm
                </a>
                <a href="tel:0983882119" class="ns-experience-hotline-link">
                    <span>📞</span> Đặt lịch tour: <strong>0983.882.119</strong>
                </a>
            </div>
        </div>
    </section>

    {{-- 5. EDITORIAL SHOWCASE MODAL --}}
    @include('nong-san.partials.modal')

    {{-- 6. LIGHTBOX MODAL FOR FULL RESOLUTION MENU IMAGES --}}
    <div id="mamCoLightbox" class="ns-lightbox-modal" style="display: none;" onclick="closeMamCoLightbox()" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
        <button type="button" class="ns-close-x-btn" onclick="closeMamCoLightbox()" aria-label="Đóng phóng to">✕</button>
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
