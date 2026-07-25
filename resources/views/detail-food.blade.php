@extends('layouts.app')

<!-- Tối ưu hóa SEO: Tiêu đề động chính xác cho Ẩm thực & Món ngon Đông Anh -->
@section('title', $eatery->name . ' - Thực đơn món ngon & Nhà hàng tại ' . $eatery->commune->name . ', Đông Anh')

<!-- Tối ưu hóa SEO: Thẻ mô tả Meta tự sinh chân thực -->
@section('meta_description', 'Khám phá ẩm thực đặc sắc tại ' . $eatery->name . ' ở ' . $eatery->address . ', ' . $eatery->commune->name . ', Đông Anh. Số điện thoại: ' . $eatery->phone . '. Thực đơn món ngon: ' . $eatery->dishes->take(3)->pluck('name')->implode(', ') . '. Xem đánh giá thực khách và bản đồ dẫn đường.')

@section('og_image', $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80')

<!-- Tối ưu hóa SEO Google: Nhúng mã JSON-LD Schema.org động sinh từ Controller -->
@section('seo_schema')
    {!! $jsonLd !!}
@endsection

@section('content')
<style>
    .detail-header-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
    }
    .detail-header-left {
        flex: 1;
        min-width: 300px;
    }
    .detail-header-badges {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .detail-title-main {
        font-size: 2.2rem;
        font-weight: 800;
        margin-top: 0;
        margin-bottom: 8px;
        font-family: var(--font-heading);
        color: var(--text-main);
        line-height: 1.25;
    }
    .detail-address-sub {
        font-size: 1.05rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-weight: 500;
    }
    .detail-header-actions {
        display: flex;
        gap: 10px;
    }
    .detail-hero {
        position: relative;
        background-size: cover;
        background-position: center;
        padding: 100px 0 80px;
        overflow: hidden;
        border-bottom: 1px solid var(--border-glow);
    }
    .detail-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(9, 9, 11, 0.4) 0%, rgba(9, 9, 11, 0.85) 100%);
        z-index: 1;
    }
    .detail-hero-content {
        position: relative;
        z-index: 2;
    }
    /* Ambient glow */
    .detail-hero::after {
        content: '';
        position: absolute;
        bottom: -150px;
        right: 10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 126, 41, 0.15) 0%, rgba(255, 126, 41, 0) 70%);
        filter: blur(80px);
        pointer-events: none;
        animation: floatOrb2 20s ease-in-out infinite alternate;
        z-index: 1;
    }
    /* Custom spacing for cards */
    .detail-section {
        background: var(--bg-card);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border-glow);
        border-radius: 20px;
        padding: 30px !important;
        box-shadow: var(--shadow-main);
        transition: all 0.3s var(--ease-premium);
    }
    .detail-section:hover {
        border-color: var(--border-glow-hover);
        box-shadow: var(--shadow-hover);
        transform: translateY(-2px);
    }
    .dish-card, .review-card, .trust-card {
        transition: all 0.3s var(--ease-premium);
    }
    .dish-card:hover, .review-card:hover, .trust-card:hover {
        transform: translateY(-4px);
        border-color: var(--border-glow-hover);
        box-shadow: var(--shadow-hover);
    }
    @keyframes pulse-shield {
        0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
        100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }

    /* Floating Contact Button on Mobile */
    .mobile-call-btn {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white !important;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(46, 204, 113, 0.4);
        z-index: 9999;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: float-pulse-call 2s infinite;
    }
    .mobile-call-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 25px rgba(46, 204, 113, 0.6);
    }
    .mobile-call-btn svg {
        width: 26px;
        height: 26px;
        fill: currentColor;
    }
    @keyframes float-pulse-call {
        0% {
            box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(46, 204, 113, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(46, 204, 113, 0);
        }
    }

    /* =====================================================================
       📱 DETAIL PAGE MOBILE RESPONSIVE FIX
       Mục tiêu: ngăn chữ bị cắt cụt ở rìa phải trên màn hình dọc (portrait)
       ===================================================================== */
    @media (max-width: 768px) {
        .detail-header-wrapper {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 16px !important;
        }
        .detail-header-left {
            min-width: 100% !important;
            width: 100% !important;
        }
        .detail-header-badges {
            gap: 6px !important;
            margin-bottom: 8px !important;
        }
        .detail-title-main {
            font-size: clamp(1.5rem, 6vw, 2.2rem) !important;
            margin-bottom: 6px !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            white-space: normal !important;
        }
        .detail-address-sub {
            font-size: 0.95rem !important;
            line-height: 1.4 !important;
        }
        .detail-header-actions {
            width: 100% !important;
            justify-content: flex-start !important;
        }
        .mobile-call-btn {
            display: flex !important;
        }
        /* 1. Hero section: đảm bảo chiều cao và text không tràn */
        .detail-hero {
            height: auto !important;
            min-height: 160px !important;
            padding-top: 80px !important;
            padding-bottom: 20px !important;
        }
        .detail-hero-content {
            padding: 16px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .detail-hero-content h1 {
            font-size: clamp(1.3rem, 5vw, 2rem) !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            white-space: normal !important;
        }

        /* 2. Trivia widget: không để bị tràn */
        .trivia-widget {
            flex-wrap: wrap !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }

        /* 3. Trust shield banner: wrap trên mobile */
        .trust-shield-banner {
            flex-wrap: wrap !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }

        /* 4. Cert image preview: không để chiếm quá nhiều chỗ */
        .cert-image-preview {
            width: 100px !important;
            height: 140px !important;
        }

        /* 5. Table trong trust hub: không bị tràn */
        table {
            width: 100% !important;
            table-layout: fixed !important;
            word-break: break-word !important;
        }
        table td {
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 6. Flex rows: cho phép wrap để tránh tràn ngang */
        .detail-section [style*="display: flex"] {
            flex-wrap: wrap !important;
        }

        /* 7. Any element with fixed pixel min-width: reset on mobile */
        [style*="min-width: 250px"],
        [style*="min-width: 260px"],
        [style*="min-width: 280px"] {
            min-width: 0 !important;
            width: 100% !important;
        }

        /* 8. Dossier header: sắp xếp theo hàng dọc */
        .detail-section [style*="justify-content: space-between"][style*="align-items: flex-start"],
        .detail-section [style*="justify-content: space-between"][style*="align-items: center"] {
            flex-wrap: wrap !important;
        }
    }
</style>

@php
    $categorySlug = $eatery->category->slug;
@endphp
<!-- Detail Header Info -->
<div class="container" style="padding-top: 40px; margin-bottom: 24px;">
    <div class="detail-header-wrapper">
        <div class="detail-header-left">
            <div class="detail-header-badges">
                <span class="tag-badge-accent" style="display: inline-block; font-size: 0.85rem; color: var(--text-main); background: var(--bg-card); border-color: var(--border-glow); backdrop-filter: blur(8px); font-weight: 600;">
                    {{ $eatery->category->icon }} {{ $eatery->category->name }}
                </span>
                @if($eatery->foodSafetyCertificate)
                    <a href="#trust-hub-section" onclick="scrollToTrustHub(event)" class="tag-badge-accent" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #ffffff; background: #2ecc71; border: 1px solid #27ae60; border-radius: 30px; padding: 6px 14px; cursor: pointer; text-decoration: none; font-weight: 800; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.4); transition: all 0.3s; animation: pulse-shield 2.5s infinite;" onmouseover="this.style.background='#27ae60'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='#2ecc71'; this.style.transform='none';">
                        🛡️ <span style="color: #ffffff;">Xác minh An Toàn</span>
                    </a>
                @endif
                @if($eatery->phone)
                    <a href="tel:{{ $eatery->phone }}" class="tag-badge-accent" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #ffffff; background: linear-gradient(135deg, #ff7e29 0%, #ff4f18 100%); border: 1px solid #ff4f18; border-radius: 30px; padding: 6px 14px; cursor: pointer; text-decoration: none; font-weight: 800; box-shadow: 0 4px 15px rgba(255, 126, 41, 0.4); transition: all 0.3s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='none';">
                        📞 <span style="color: #ffffff;">Gọi ngay: {{ $eatery->phone }}</span>
                    </a>
                @endif
            </div>
            
            <h1 class="detail-title-main">
                {{ $eatery->name }}
            </h1>
            <p class="detail-address-sub">
                <span>📍</span> {{ $eatery->address }}
            </p>
        </div>
        
        <!-- Action Buttons -->
        <div class="detail-header-actions">
            <button class="btn-secondary" style="border-radius: 30px; padding: 8px 16px; font-size: 0.9rem; border-color: var(--border-glow); display: flex; align-items: center; gap: 6px; background: var(--bg-card); color: var(--text-main); font-weight: 600;" onmouseover="this.style.background='var(--bg-btn-secondary)'" onmouseout="this.style.background='var(--bg-card)'">
                <span style="font-size: 1.1rem;">📤</span> Chia sẻ
            </button>
            <button class="btn-secondary" style="border-radius: 30px; padding: 8px 16px; font-size: 0.9rem; border-color: var(--border-glow); display: flex; align-items: center; gap: 6px; background: var(--bg-card); color: var(--text-main); font-weight: 600;" onmouseover="this.style.background='var(--bg-btn-secondary)'" onmouseout="this.style.background='var(--bg-card)'">
                <span style="font-size: 1.1rem;">❤️</span> Lưu lại
            </button>
        </div>
    </div>
</div>

@php
    $allMedia = [];

    // 1. Ảnh đại diện chính của cơ sở
    if ($eatery->image_path) {
        $allMedia[] = ['type' => 'image', 'url' => $eatery->image_path];
    }

    // 2. Ảnh gallery đã upload bởi admin/seller
    $eateryPhotos = $eatery->relationLoaded('photos') ? $eatery->photos : collect();
    foreach ($eateryPhotos as $photo) {
        $allMedia[] = ['type' => 'image', 'url' => $photo->image_path, 'caption' => $photo->caption];
    }

    // 3. Ảnh từ các bài đánh giá của khách hàng
    foreach ($eatery->reviews as $rev) {
        if ($rev->media) {
            foreach ($rev->media as $m) {
                $allMedia[] = ['type' => $m->file_type, 'url' => $m->file_path];
            }
        }
    }

    // 4. Nếu không có ảnh nào: dùng ảnh placeholder trung lập
    if (empty($allMedia)) {
        $allMedia[] = ['type' => 'image', 'url' => asset('images/ocop-placeholder.png')];
    }

    // 5. Pad đủ 5 ô cho grid (lặp lại ảnh đầu tiên nếu thiếu)
    $firstMedia = $allMedia[0];
    while (count($allMedia) < 5) {
        $allMedia[] = $firstMedia;
    }
@endphp

<!-- Modern Airbnb-style Gallery Grid -->
<div class="container" style="margin-bottom: 40px;">
    <div class="hero-gallery-grid" style="display: grid; grid-template-columns: 2fr 1fr 1fr; grid-template-rows: 220px 220px; gap: 12px; border-radius: 24px; overflow: hidden; position: relative;">
        <!-- Image 1: Main Large -->
        <div onclick="openHeroGalleryModal(0)" style="grid-column: 1; grid-row: 1 / 3; cursor: pointer; position: relative; overflow: hidden;" class="gallery-item">
            @if($allMedia[0]['type'] === 'video')
                <video src="{{ $allMedia[0]['url'] }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;" autoplay muted loop playsinline></video>
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.2);">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #ff4f18;">▶</div>
                </div>
            @else
                <img src="{{ $allMedia[0]['url'] }}" alt="{{ $eatery->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
            @endif
            <div class="gallery-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.1); opacity: 0; transition: opacity 0.3s;"></div>
        </div>
        
        <!-- Image 2 -->
        <div onclick="openHeroGalleryModal(1)" style="cursor: pointer; position: relative; overflow: hidden;" class="gallery-item">
            <img src="{{ $allMedia[1]['url'] }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
            <div class="gallery-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.1); opacity: 0; transition: opacity 0.3s;"></div>
        </div>
        
        <!-- Image 3 -->
        <div onclick="openHeroGalleryModal(2)" style="cursor: pointer; position: relative; overflow: hidden;" class="gallery-item">
            <img src="{{ $allMedia[2]['url'] }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
            <div class="gallery-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.1); opacity: 0; transition: opacity 0.3s;"></div>
        </div>
        
        <!-- Image 4 -->
        <div onclick="openHeroGalleryModal(3)" style="cursor: pointer; position: relative; overflow: hidden;" class="gallery-item">
            <img src="{{ $allMedia[3]['url'] }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
            <div class="gallery-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.1); opacity: 0; transition: opacity 0.3s;"></div>
        </div>
        
        <!-- Image 5: Show all photos overlay -->
        <div onclick="openHeroGalleryModal(4)" style="cursor: pointer; position: relative; overflow: hidden;" class="gallery-item">
            @if($allMedia[4]['type'] === 'video')
                <video src="{{ $allMedia[4]['url'] }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;" muted playsinline></video>
            @else
                <img src="{{ $allMedia[4]['url'] }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
            @endif
            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; transition: background 0.3s;" onmouseover="this.style.background='rgba(0,0,0,0.3)'" onmouseout="this.style.background='rgba(0,0,0,0.4)'">
                <div style="text-align: center;">
                    <div style="font-size: 1.8rem; margin-bottom: 4px;">🖼️</div>
                    <span style="color: white; font-weight: 700; font-size: 1.05rem; display: block;">
                        Xem tất cả {{ count($allMedia) }} Ảnh
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full Screen Hero Gallery Modal -->
<div id="heroGalleryModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.95); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <!-- Close Button -->
    <button onclick="closeHeroGalleryModal()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); width: 44px; height: 44px; border-radius: 50%; color: white; font-size: 1.5rem; cursor: pointer; z-index: 2; transition: all 0.3s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">✕</button>

    <!-- Navigation Buttons -->
    <button onclick="navigateHeroGallery(-1)" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 50%; color: white; font-size: 1.5rem; cursor: pointer; z-index: 2; transition: all 0.3s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">❮</button>
    
    <button onclick="navigateHeroGallery(1)" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 50%; color: white; font-size: 1.5rem; cursor: pointer; z-index: 2; transition: all 0.3s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">❯</button>

    <!-- Main Content Area -->
    <div style="width: 90vw; height: 80vh; max-width: 1200px; display: flex; align-items: center; justify-content: center; position: relative;">
        <div id="heroGalleryContent" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; transform: scale(0.9); transition: transform 0.3s ease;">
            <!-- Content injected via JS -->
        </div>
    </div>
    
    <!-- Counter Indicator -->
    <div style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); color: white; font-size: 1.1rem; font-weight: 600; background: rgba(0,0,0,0.5); padding: 8px 16px; border-radius: 30px; backdrop-filter: blur(5px);">
        <span id="heroGalleryCounter">1</span> / {{ count($allMedia) }}
    </div>
</div>

<script>
    const allGalleryMedia = {!! json_encode($allMedia) !!};
    let currentGalleryIndex = 0;

    function openHeroGalleryModal(index = 0) {
        currentGalleryIndex = index;
        const modal = document.getElementById("heroGalleryModal");
        if (modal) {
            updateHeroGalleryContent();
            modal.style.display = "flex";
            setTimeout(() => {
                modal.style.opacity = "1";
                const contentWrapper = document.getElementById("heroGalleryContent");
                if (contentWrapper) contentWrapper.style.transform = "scale(1)";
            }, 10);
            document.body.style.overflow = "hidden"; // Prevent scrolling behind modal
        }
    }

    function closeHeroGalleryModal() {
        const modal = document.getElementById("heroGalleryModal");
        if (modal) {
            modal.style.opacity = "0";
            const contentWrapper = document.getElementById("heroGalleryContent");
            if (contentWrapper) contentWrapper.style.transform = "scale(0.9)";
            
            // Stop video if playing by clearing HTML
            setTimeout(() => {
                if(contentWrapper) contentWrapper.innerHTML = '';
                modal.style.display = "none";
                document.body.style.overflow = "auto";
            }, 300);
        }
    }

    function navigateHeroGallery(direction) {
        // Clear current content immediately to stop any playing videos
        const contentWrapper = document.getElementById("heroGalleryContent");
        if(contentWrapper) contentWrapper.innerHTML = '';
        
        currentGalleryIndex += direction;
        if (currentGalleryIndex < 0) {
            currentGalleryIndex = allGalleryMedia.length - 1;
        } else if (currentGalleryIndex >= allGalleryMedia.length) {
            currentGalleryIndex = 0;
        }
        updateHeroGalleryContent();
    }

    function updateHeroGalleryContent() {
        const item = allGalleryMedia[currentGalleryIndex];
        const contentWrapper = document.getElementById("heroGalleryContent");
        const counterText = document.getElementById("heroGalleryCounter");
        
        if (contentWrapper && item) {
            if (item.type === 'video') {
                contentWrapper.innerHTML = `<video src="${item.url}" controls autoplay style="max-width: 100%; max-height: 100%; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);"></video>`;
            } else {
                contentWrapper.innerHTML = `<img src="${item.url}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">`;
            }
        }
        
        if (counterText) {
            counterText.textContent = currentGalleryIndex + 1;
        }
    }
    
    // Add Keyboard Navigation support
    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById("heroGalleryModal");
        if (modal && modal.style.display === "flex") {
            if (event.key === "ArrowLeft") {
                navigateHeroGallery(-1);
            } else if (event.key === "ArrowRight") {
                navigateHeroGallery(1);
            } else if (event.key === "Escape") {
                closeHeroGalleryModal();
            }
        }
    });
</script>

<style>
    .gallery-item:hover img, .gallery-item:hover video {
        transform: scale(1.05);
    }
    .gallery-item:hover .gallery-overlay {
        opacity: 1 !important;
    }
    
    @media (max-width: 768px) {
        .hero-gallery-grid {
            display: flex !important;
            flex-direction: row;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            grid-template-columns: none !important;
            grid-template-rows: none !important;
            height: 300px !important;
            border-radius: 0 !important;
            margin-left: -15px;
            margin-right: -15px;
            width: calc(100% + 30px);
            scrollbar-width: none; /* Firefox */
        }
        .hero-gallery-grid::-webkit-scrollbar {
            display: none; /* Safari and Chrome */
        }
        .gallery-item {
            flex: 0 0 100%;
            height: 100%;
            scroll-snap-align: start;
            border-radius: 0;
        }
    }
</style>

<!-- Detail Main Layout Grid -->
<div class="container">
    <div class="detail-grid">
        
        <!-- Left Side: Core Info, Menu, and Reviews -->
        <div>
            <!-- Giới thiệu hoặc Không gian di sản văn hóa ẩm thực -->
            @php
                $dossier = $eatery->heritage_dossier;
            @endphp

            @if($dossier)
                <!-- Premium Digital Museum Showcase for Heritage Specialties -->
                <div class="detail-section glass-panel heritage-museum-card" style="padding: 28px; margin-bottom: 40px;">
                    
                    <!-- Traditional Vietnamese Decorative Pattern Overlay -->
                    <div class="heritage-pattern-overlay"></div>

                    <div style="position: relative; z-index: 2;">
                        <!-- Header of Heritage Dossier -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px; border-bottom: 1px dashed rgba(212, 175, 55, 0.25); padding-bottom: 20px;">
                            <div style="flex: 1; min-width: 0;">

                                <h2 style="font-size: 2rem; font-family: var(--font-heading); color: var(--text-main); font-weight: 800; margin-top: 8px; margin-bottom: 4px;">
                                    Hồ Sơ Di Sản: {{ $eatery->name }}
                                </h2>
                                <p style="font-style: italic; color: var(--primary); font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                    <span>🌾</span> {{ $dossier['heritage_year'] }}
                                </p>
                            </div>
                            
                            <!-- OCOP Star Badge Badge -->
                            <div class="ocop-star-badge" style="flex-shrink: 0;">
                                <span style="font-weight: 900; font-size: 0.75rem; color: var(--primary); display: block; letter-spacing: 1px;">CHỨNG NHẬN OCOP</span>
                                <div style="color: #ffc107; font-size: 1.2rem; margin-top: 4px; display: flex; gap: 2px; justify-content: flex-end;">
                                    @for($i=1; $i<=5; $i++)
                                        <span style="{{ $i <= $dossier['ocop_stars'] ? 'color: #ffb300; text-shadow: 0 0 10px rgba(255, 179, 0, 0.5);' : 'color: var(--border-glow);' }}">★</span>
                                    @endfor
                                </div>
                                <span style="font-size: 0.7rem; color: var(--text-muted); display: block; margin-top: 4px;">{{ $dossier['ocop_stars'] }} Sao Cấp Quốc Gia</span>
                            </div>
                        </div>

                        <!-- Smart AI Voice Storytelling Component -->
                        <div class="audio-storyteller-widget glass-panel" style="background: rgba(212, 175, 55, 0.04); border: 1px solid rgba(212, 175, 55, 0.2); padding: 16px 20px; border-radius: 16px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; max-width: 100%; box-sizing: border-box;">
                            <div style="display: flex; align-items: center; gap: 16px; flex: 1; min-width: 0;">
                                <button id="playAudioBtn" class="audio-play-btn" aria-label="Play narrative audio" title="Nghe kể câu chuyện di sản" style="outline: none;">
                                    <span class="play-icon" id="playBtnIcon">🔊</span>
                                </button>
                                <div>
                                    <strong style="color: #ffb300; display: block; font-size: 1rem;">🎧 Nghe kể chuyện di sản</strong>
                                    <span style="font-size: 0.85rem; color: var(--text-muted);" id="audioStatusText">Bấm để lắng nghe giọng đọc AI thuyết minh văn hóa món ăn</span>
                                </div>
                            </div>
                            
                            <!-- Equalizer Visualizer -->
                            <div class="equalizer-container" id="audioEqualizer">
                                <div class="eq-bar"></div>
                                <div class="eq-bar"></div>
                                <div class="eq-bar"></div>
                                <div class="eq-bar"></div>
                                <div class="eq-bar"></div>
                                <div class="eq-bar"></div>
                            </div>
                        </div>

                        <!-- Heritage Tab Content -->
                        <div class="heritage-tabs-container">
                            <div class="heritage-tab-buttons">
                                <button class="heritage-tab-btn active" data-tab="tab-story">🏛️ Nguồn Gốc & Câu Chuyện</button>
                                <button class="heritage-tab-btn" data-tab="tab-artisans">👨‍🍳 Nghệ Nhân Truyền Nghề</button>
                                <button class="heritage-tab-btn" data-tab="tab-ingredients">🌾 Bí Quyết & Nguyên Liệu</button>
                                <button class="heritage-tab-btn" data-tab="tab-timeline">📜 Hành Trình Di Sản</button>
                            </div>

                            <!-- Tab 1: Nguồn gốc -->
                            <div id="tab-story" class="heritage-tab-content active-content">
                                <p style="font-size: 1.05rem; line-height: 1.8; color: var(--text-main); margin-bottom: 20px;">
                                    {{ $dossier['story'] }}
                                </p>
                                @if(!empty($eatery->description) && $eatery->description !== 'null')
                                <div style="background: rgba(255,255,255,0.01); border-left: 3px solid #ffb300; padding: 16px; border-radius: 4px; font-size: 0.95rem; line-height: 1.6;">
                                    {{ $eatery->description }}
                                </div>
                                @endif
                            </div>

                            <!-- Tab 2: Nghệ nhân -->
                            <div id="tab-artisans" class="heritage-tab-content">
                                <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                    <div style="flex: 1; min-width: 260px;">
                                        <h4 style="color: var(--primary); font-size: 1.15rem; margin-bottom: 12px; font-weight: 700;">Gặp Gỡ Những Người Giữ Lửa Di Sản</h4>
                                        <p style="font-size: 1.02rem; line-height: 1.8; color: var(--text-main); font-style: italic; background: var(--bg-btn-secondary); padding: 20px; border-radius: 12px; border: 1px dashed var(--border-glow-hover); margin: 0;">
                                            {{ $dossier['artisans'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
 
                            <!-- Tab 3: Nguyên liệu -->
                            <div id="tab-ingredients" class="heritage-tab-content">
                                <h4 style="color: var(--primary); font-size: 1.15rem; margin-bottom: 16px; font-weight: 700;">Bảng Thành Phần Bản Địa Thuần Khiết</h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                                    @foreach($dossier['ingredients'] as $ingredient)
                                        <div class="glass-panel" style="padding: 14px 20px; background: rgba(212, 175, 55, 0.03); border: 1px solid rgba(212, 175, 55, 0.15); display: flex; align-items: center; gap: 12px;">
                                            <span style="font-size: 1.3rem;">✨</span>
                                            <span style="font-size: 0.95rem; font-weight: 600; color: var(--text-main);">{{ $ingredient }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
 
                            <!-- Tab 4: Timeline -->
                            <div id="tab-timeline" class="heritage-tab-content">
                                <div class="heritage-timeline">
                                    @foreach($dossier['timeline'] as $item)
                                        <div class="heritage-timeline-item">
                                            <div class="heritage-timeline-badge">{{ $item['year'] }}</div>
                                            <div class="heritage-timeline-content glass-panel" style="margin-left: 20px;">
                                                <p style="font-size: 0.95rem; margin: 0; line-height: 1.6; color: var(--text-main);">{{ $item['event'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
 
                        <!-- "Bạn có biết?" Trivia Widget -->
                        <div class="trivia-widget glass-panel" style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.08) 0%, rgba(255, 111, 0, 0.03) 100%); border: 1px solid rgba(212, 175, 55, 0.25); padding: 24px; border-radius: 16px; margin-top: 32px; display: flex; gap: 16px; align-items: flex-start;">
                            <span style="font-size: 2.2rem; filter: drop-shadow(0 0 10px rgba(255,179,0,0.6));">💡</span>
                            <div>
                                <h4 style="font-size: 1.1rem; color: var(--primary); font-weight: 700; margin-bottom: 6px;">BẠN CÓ BIẾT?</h4>
                                <p style="font-size: 0.95rem; line-height: 1.7; color: var(--text-main); margin: 0;">
                                    {{ $dossier['fun_fact'] }}
                                </p>
                            </div>
                        </div>
                        

                    </div>
                </div>
            @else
                @if(!empty($eatery->description) && $eatery->description !== 'null')
                <div class="detail-section glass-panel" style="padding: 28px; margin-bottom: 40px;">
                    <h2 class="section-title">
                        <span>📝</span> 
                        @if($categorySlug === 'dong-anh-food-map')
                            Giới thiệu về quán
                        @elseif($categorySlug === 'stay-in-dong-anh')
                            Giới thiệu địa điểm lưu trú
                        @elseif($categorySlug === 'wellness-care')
                            Giới thiệu về cơ sở chăm sóc
                        @elseif($categorySlug === 'dong-anh-market')
                            Giới thiệu về cơ sở
                        @elseif($categorySlug === 'smart-education-map')
                            Giới thiệu về nhà trường
                        @else
                            Giới thiệu chi tiết
                        @endif
                    </h2>
                    <p style="font-size: 1rem; color: var(--text-main); line-height: 1.8;">
                        {{ $eatery->description }}
                    </p>
                </div>
                @endif
            @endif

            <!-- CSS đặc thù cho Hệ thống Minh bạch Thực phẩm sạch (Trust Hub) -->
            <style>
                .trust-tab-btn {
                    background: transparent;
                    border: none;
                    outline: none;
                    color: var(--text-muted);
                    font-weight: 600;
                    font-size: 0.9rem;
                    padding: 8px 16px;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                .trust-tab-btn.active {
                    background: var(--bg-btn-secondary);
                    color: var(--accent);
                    box-shadow: 0 0 15px rgba(32, 178, 170, 0.15), inset 0 0 0 1px rgba(32, 178, 170, 0.3);
                }
                .trust-tab-btn:hover:not(.active) {
                    color: var(--text-main);
                    background: rgba(255, 255, 255, 0.02);
                }
                .trust-tab-content {
                    display: none;
                    animation: fadeInTrust 0.4s ease forwards;
                }
                .trust-tab-content.active-content {
                    display: block;
                }
                @keyframes fadeInTrust {
                    from { opacity: 0; transform: translateY(8px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes pulse-trust {
                    0% { transform: scale(1); filter: drop-shadow(0 0 4px rgba(32, 178, 170, 0.4)); }
                    50% { transform: scale(1.05); filter: drop-shadow(0 0 12px rgba(32, 178, 170, 0.7)); }
                    100% { transform: scale(1); filter: drop-shadow(0 0 4px rgba(32, 178, 170, 0.4)); }
                }
                @keyframes pulse-shield {
                    0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
                    70% { box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
                }
            </style>



            <!-- Premium Pop-up Lightbox for Document Scan Image Viewer -->
            <div id="trustLightbox" class="lightbox-overlay" onclick="closeTrustLightbox()" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.9); z-index: 99999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
                <div style="position: absolute; top: 20px; right: 20px; font-size: 2rem; color: white; cursor: pointer; font-weight: bold; text-shadow: 0 0 10px rgba(0,0,0,0.8);" onclick="closeTrustLightbox()">&times;</div>
                <div class="lightbox-content" style="max-width: 90%; max-height: 85%; transform: scale(0.9); transition: transform 0.3s ease; text-align: center;" onclick="event.stopPropagation()">
                    <img id="trustLightboxImg" src="" style="max-width: 100%; max-height: 75vh; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.1);">
                    <h4 id="trustLightboxCaption" style="color: white; margin-top: 16px; font-family: var(--font-heading); font-size: 1.1rem; text-shadow: 0 2px 4px rgba(0,0,0,0.8);"></h4>
                    <div style="font-size: 0.8rem; color: #aaa; margin-top: 8px; letter-spacing: 0.5px;">✓ Bản quét bảo mật số hóa Đông Anh (Đã xác thực)</div>
                </div>
            </div>

            <!-- Dynamic Category Details Section -->
            @php
                $items = $eatery->dishes;
                $title = 'Thực đơn & Món ăn đặc trưng';
                $icon = '🍲';
                $emptyText = 'Chưa cập nhật thực đơn chi tiết cho nhà hàng này.';
                $btnText = 'Xem toàn bộ thực đơn';
                $modalTitle = 'Thực đơn & Món ăn đặc trưng - ' . $eatery->name;
                $itemUnit = 'món';
                $placeholderSearch = 'Tìm món ăn ngon theo tên hoặc mô tả...';
            @endphp

            <div class="detail-section glass-panel" style="padding: 28px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                    <h2 class="section-title" style="margin-bottom: 0;"><span>{!! $icon !!}</span> {{ $title }}</h2>
                    @if($items->count() > 0)
                        <button onclick="openFullMenuModal()" class="btn-secondary" style="font-size: 0.85rem; padding: 8px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; background: rgba(255, 126, 41, 0.05); border-color: rgba(255, 126, 41, 0.2); color: var(--primary);">
                            {!! $icon !!} {{ $btnText }}
                        </button>
                    @endif
                </div>
                @if($items->count() > 0)
                    <div style="position: relative; width: 100%;">
                        
                        <div class="menu-slider-wrapper" id="menuSliderWrapper">
                            <div class="menu-slider-content" id="menuSliderContent">
                                @foreach($items as $item)
                                    @if(in_array($categorySlug, ['hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']))
                                        <div class="dish-card glass-panel" style="background: rgba(255, 255, 255, 0.02); flex: 0 0 350px; min-width: 320px; display: flex; flex-direction: column; gap: 0; padding: 0; overflow: hidden; border: 1.5px solid rgba(16, 185, 129, 0.2); transition: all 0.3s ease; border-radius: 16px;">
                                            <div style="position: relative; height: 160px; overflow: hidden; width: 100%;">
                                                <img src="{{ $item->image_path ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80' }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;" alt="{{ $item->name }}">
                                                <div style="position: absolute; top: 12px; left: 12px; display: flex; flex-direction: column; gap: 6px; z-index: 2;">
                                                    @if($item->type === 'experience')
                                                        <span style="background: rgba(3, 105, 161, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">🏛️ Trải nghiệm</span>
                                                    @elseif($item->type === 'ticket')
                                                        <span style="background: rgba(21, 128, 61, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">🎫 Vé tham quan</span>
                                                    @elseif($item->type === 'service')
                                                        <span style="background: rgba(161, 98, 7, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">🙏 Dịch vụ di tích</span>
                                                    @else
                                                        <span style="background: rgba(71, 85, 105, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">🏛️ Khác</span>
                                                    @endif
                                                    @if($item->discount_note && $item->discount_note !== 'null')
                                                        <span style="background: rgba(221, 107, 32, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">🏷️ {{ $item->discount_note }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div style="padding: 20px; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between; gap: 12px;">
                                                <div>
                                                    <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); line-height: 1.4; margin: 0 0 8px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.8em;" title="{{ $item->name }}">{{ $item->name }}</h3>
                                                    @if($item->description && $item->description !== 'null' && $item->description !== 'NULL')
                                                        <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; min-height: 6em;" title="{{ $item->description }}">{{ $item->description }}</p>
                                                    @else
                                                        <div style="min-height: 6em;"></div>
                                                    @endif
                                                </div>
                                                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.06); margin-top: auto;">
                                                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">💰 Dự chi:</span>
                                                    <strong style="color: #10b981; font-size: 1.05rem; font-weight: 800;">
                                                        @if($item->price !== null && $item->price > 0)
                                                            {{ number_format($item->price, 0, ',', '.') }}đ <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-muted);">/ {{ $item->unit ?: 'lượt' }}</span>
                                                        @else
                                                            Theo yêu cầu <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-muted);">/ {{ $item->unit ?: 'lượt' }}</span>
                                                        @endif
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $hasProductDossier = !empty($item->story) || !empty($item->artisans) || !empty($item->heritage_year) || !empty($item->fun_fact) || !empty($item->ingredients) || !empty($item->timeline);
                                        @endphp
                                        <div class="dish-card glass-panel" 
                                             style="background: rgba(255,255,255,0.02); flex: 0 0 calc(50% - 10px); min-width: 290px; transition: all 0.3s ease; {{ $hasProductDossier ? 'cursor: pointer;' : '' }}"
                                             @if($hasProductDossier)
                                                 onclick="const el = document.getElementById('heritage-dossier-{{ $item->id }}'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });"
                                                 onmouseover="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 15px rgba(255, 126, 41, 0.15)'"
                                                 onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.boxShadow='none'"
                                             @endif>
                                            <img src="{{ $item->image_path ?: ($categorySlug === 'smart-education-map' ? 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=150&q=80' : ($categorySlug === 'wellness-care' ? 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=150&q=80' : ($categorySlug === 'stay-in-dong-anh' ? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=150&q=80' : ($categorySlug === 'dong-anh-market' ? 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=150&q=80' : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=150&q=80')))) }}" class="dish-img" alt="{{ $item->name }}">
                                            <div class="dish-info" style="flex: 1;">
                                                <div>
                                                    @if($categorySlug === 'dong-anh-market' && $hasProductDossier)
                                                        <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(212, 175, 55, 0.1); border-color: rgba(212, 175, 55, 0.3); color: #ffb300; animation: pulse-trust 2s infinite;">🏺 Xem Chi Tiết Di Sản</span>
                                                    @endif
                                                    @if($categorySlug === 'smart-education-map')
                                                        <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(52, 152, 219, 0.1); border-color: rgba(52, 152, 219, 0.2); color: #3498db;">⏱️ {{ $item->duration }}</span>
                                                    @elseif($categorySlug === 'wellness-care' && $item->duration)
                                                        <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(46, 204, 113, 0.1); border-color: rgba(46, 204, 113, 0.2); color: #2ecc71;">⏱️ {{ $item->duration }}</span>
                                                    @elseif($categorySlug === 'stay-in-dong-anh')
                                                        <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(155, 89, 182, 0.1); border-color: rgba(155, 89, 182, 0.2); color: #9b59b6;">🛏️ {{ $item->bed_type }} | 👤 Sức chứa: {{ $item->capacity }}</span>
                                                    @elseif($categorySlug === 'dong-anh-market' && $item->star_rating)
                                                        <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(241, 196, 15, 0.1); border-color: rgba(241, 196, 15, 0.2); color: #f1c40f;">⭐ OCOP: {{ $item->star_rating }}</span>
                                                    @elseif(isset($item->is_signature) && $item->is_signature)
                                                        <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block;">★ Món đặc trưng</span>
                                                    @endif
                                                    <h3 class="dish-name">{{ $item->name }}</h3>
                                                    @if($item->description && $item->description !== 'null' && $item->description !== 'NULL')
                                                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; line-height: 1.4;">{{ $item->description }}</p>
                                                    @endif
                                                </div>
                                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; width: 100%;">
                                                    @if($categorySlug === 'smart-education-map')
                                                        <span class="dish-price" style="font-size: 0.95rem;">{{ $item->tuition_fee ?: 'Liên hệ trường' }}</span>
                                                    @elseif(isset($item->price) && $item->price > 0)
                                                        <span class="dish-price">{{ number_format($item->price, 0, ',', '.') }}đ{{ $categorySlug === 'stay-in-dong-anh' ? ' / đêm' : '' }}</span>
                                                    @else
                                                        <span class="dish-price" style="font-size: 0.95rem; color: var(--text-muted);">Liên hệ</span>
                                                    @endif

                                                    @if(!in_array($categorySlug, ['stay-in-dong-anh', 'wellness-care', 'smart-education-map', 'discover-dong-anh-community-culture-hub']) && isset($item->price) && $item->price > 0)
                                                        <button class="btn-add-to-cart-mini" 
                                                                data-id="{{ $item->id }}" 
                                                                data-type="{{ $categorySlug === 'dong-anh-market' ? 'ocop_product' : 'dish' }}"
                                                                style="background: var(--primary-grad); border: none; color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; box-shadow: 0 4px 10px rgba(255, 126, 41, 0.2);"
                                                                onclick="addToCart(event, this)">
                                                            Thêm 🛒
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Flat Heritage Dossiers Section -->
                    @php
                        $heritageProducts = $items->filter(function($prod) {
                            return !empty($prod->story) || !empty($prod->artisans) || !empty($prod->heritage_year) || !empty($prod->fun_fact) || !empty($prod->ingredients) || !empty($prod->timeline);
                        });
                    @endphp

                    @if($heritageProducts->count() > 0)
                        <div class="heritage-flat-section" style="margin-top: 35px; border-top: 1.5px dashed rgba(255,255,255,0.08); padding-top: 30px;">
                            <h3 style="font-size: 1.2rem; font-weight: 800; color: #ffb300; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; text-shadow: 0 0 10px rgba(255,179,0,0.15);">
                                🏺 Hồ Sơ Di Sản & Câu Chuyện Đặc Sản Làng Nghề
                            </h3>
                            <div style="display: flex; flex-direction: column; gap: 30px;">
                                @foreach($heritageProducts as $prod)
                                    <div id="heritage-dossier-{{ $prod->id }}" class="glass-panel" style="background: rgba(255, 255, 255, 0.02); border: 1.5px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 24px; position: relative; overflow: hidden;">
                                        <!-- Decorative background patterns -->
                                        <div class="heritage-pattern-overlay" style="opacity: 0.08;"></div>
                                        
                                        <div style="position: relative; z-index: 2; display: flex; flex-direction: column; gap: 20px;">
                                            <!-- Header: Name, OCOP stars, heritage year -->
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 16px;">
                                                <div>
                                                    <h4 style="font-size: 1.35rem; font-weight: 800; color: #ffb300; margin: 0 0 6px 0; text-shadow: 0 0 10px rgba(255,179,0,0.15);">
                                                        Hồ Sơ Di Sản: {{ $prod->name }}
                                                    </h4>
                                                    @if($prod->heritage_year)
                                                        <p style="font-style: italic; color: var(--primary); font-size: 0.95rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 6px;">
                                                            🌾 {{ $prod->heritage_year }}
                                                        </p>
                                                    @endif
                                                </div>
                                                @if($prod->star_rating)
                                                    @php
                                                        $starsMatch = [];
                                                        preg_match('/\d+/', $prod->star_rating, $starsMatch);
                                                        $starsCount = !empty($starsMatch) ? (int)$starsMatch[0] : 0;
                                                    @endphp
                                                    @if($starsCount > 0)
                                                        <div class="ocop-star-badge" style="flex-shrink: 0; text-align: center; background: rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.15); padding: 8px 16px; border-radius: 12px; min-width: 140px;">
                                                            <span style="font-weight: 900; font-size: 0.65rem; color: #0ea5e9; display: block; letter-spacing: 1px; margin-bottom: 4px;">CHỨNG NHẬN OCOP</span>
                                                            <div style="color: #ffb300; font-size: 1.1rem; display: flex; gap: 2px; justify-content: center; margin-bottom: 4px;">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <span style="{{ $i <= $starsCount ? 'color: #ffb300; text-shadow: 0 0 10px rgba(255, 179, 0, 0.5);' : 'color: rgba(255,255,255,0.15);' }}">★</span>
                                                                @endfor
                                                            </div>
                                                            <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">{{ $prod->star_rating }} Cấp Quốc Gia</span>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>

                                            <!-- TTS Audio Widget (Flat, standalone!) -->
                                            @if($prod->audio_narrative)
                                                <div class="audio-storyteller-widget glass-panel" style="background: rgba(212, 175, 55, 0.04); border: 1px solid rgba(212, 175, 55, 0.15); padding: 16px 20px; border-radius: 16px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                                                    <div style="display: flex; align-items: center; gap: 16px; flex: 1; min-width: 0;">
                                                        <button class="audio-play-btn flat-audio-play-btn" data-narrative="{{ $prod->audio_narrative }}" onclick="toggleFlatAudio(this)" aria-label="Play narrative audio" style="outline: none; background: linear-gradient(135deg, #f79d00, #f87a00); border: none; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; transition: transform 0.2s ease; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(248, 122, 0, 0.3);">
                                                            <span class="play-icon" style="color: #ffffff; font-size: 1.1rem; line-height: 1;">🔊</span>
                                                        </button>
                                                        <div>
                                                            <strong style="color: #ffb300; display: block; font-size: 0.95rem; margin-bottom: 2px;">🎧 Nghe kể chuyện di sản</strong>
                                                            <span style="font-size: 0.8rem; color: var(--text-muted);" class="flat-audio-status-text">Bấm để lắng nghe giọng đọc AI thuyết minh văn hóa món ăn</span>
                                                        </div>
                                                    </div>
                                                    <!-- Equalizer Visualizer / Six dots -->
                                                    <div class="equalizer-container flat-audio-equalizer" style="display: none; height: 20px; align-items: flex-end; gap: 3px;">
                                                        <div class="eq-bar" style="width: 3px; height: 100%; background: #ffb300; animation: eq 0.8s infinite alternate;"></div>
                                                        <div class="eq-bar" style="width: 3px; height: 60%; background: #ffb300; animation: eq 1.2s infinite alternate;"></div>
                                                        <div class="eq-bar" style="width: 3px; height: 80%; background: #ffb300; animation: eq 0.9s infinite alternate;"></div>
                                                        <div class="eq-bar" style="width: 3px; height: 40%; background: #ffb300; animation: eq 1.1s infinite alternate;"></div>
                                                    </div>
                                                    <div class="dots-placeholder" style="color: #ffb300; letter-spacing: 2px; font-weight: 900; font-size: 1.2rem; opacity: 0.7;">......</div>
                                                </div>
                                            @endif

                                            <!-- Tab Buttons -->
                                            <div class="heritage-tab-buttons" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 12px;">
                                                <button class="flat-heritage-tab-btn active" onclick="switchFlatTab(this, 'flat-tab-story-{{ $prod->id }}')">🏛️ Nguồn Gốc & Câu Chuyện</button>
                                                @if($prod->artisans)
                                                    <button class="flat-heritage-tab-btn" onclick="switchFlatTab(this, 'flat-tab-artisans-{{ $prod->id }}')">🧑‍🍳 Nghệ Nhân Truyền Nghề</button>
                                                @endif
                                                @if(is_array($prod->ingredients) && count($prod->ingredients) > 0)
                                                    <button class="flat-heritage-tab-btn" onclick="switchFlatTab(this, 'flat-tab-ingredients-{{ $prod->id }}')">🌾 Bí Quyết & Nguyên Liệu</button>
                                                @endif
                                                @if(is_array($prod->timeline) && count($prod->timeline) > 0)
                                                    <button class="flat-heritage-tab-btn" onclick="switchFlatTab(this, 'flat-tab-timeline-{{ $prod->id }}')">📜 Hành Trình Di Sản</button>
                                                @endif
                                            </div>

                                            <!-- Tab Contents -->
                                            <div class="flat-tab-content-container" style="min-height: 120px; margin-top: 10px;">
                                                <!-- Story Tab -->
                                                <div id="flat-tab-story-{{ $prod->id }}" class="flat-tab-panel" style="display: block;">
                                                    <p style="font-size: 0.92rem; color: var(--text-main); line-height: 1.7; margin: 0; text-align: justify; white-space: pre-line;">
                                                        {{ $prod->story }}
                                                    </p>
                                                </div>

                                                <!-- Artisans Tab -->
                                                @if($prod->artisans)
                                                    <div id="flat-tab-artisans-{{ $prod->id }}" class="flat-tab-panel" style="display: none;">
                                                        <div class="glass-panel" style="background: rgba(52, 152, 219, 0.02); border: 1px solid rgba(52, 152, 219, 0.15); padding: 18px; border-radius: 12px;">
                                                            <strong style="color: #3498db; display: block; font-size: 1.05rem; margin-bottom: 8px;">Gặp Gỡ Những Người Giữ Lửa Di Sản</strong>
                                                            <p style="font-size: 0.9rem; color: var(--text-main); line-height: 1.6; margin: 0; white-space: pre-line;">
                                                                {{ $prod->artisans }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Ingredients Tab -->
                                                @if(is_array($prod->ingredients) && count($prod->ingredients) > 0)
                                                    <div id="flat-tab-ingredients-{{ $prod->id }}" class="flat-tab-panel" style="display: none;">
                                                        <strong style="color: #0ea5e9; display: block; font-size: 1.05rem; margin-bottom: 12px;">Bảng Thành Phần Bản Địa Thuần Khiết</strong>
                                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                                                            @foreach($prod->ingredients as $ing)
                                                                <div class="glass-panel" style="padding: 14px 18px; background: rgba(212, 175, 55, 0.03); border: 1px solid rgba(212, 175, 55, 0.15); display: flex; align-items: center; gap: 12px; border-radius: 12px; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                                                    <span style="font-size: 1.25rem; color: #ffb300; filter: drop-shadow(0 0 5px rgba(255,179,0,0.4)); flex-shrink: 0;">✨</span>
                                                                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); line-height: 1.4;">{{ $ing }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Timeline Tab -->
                                                @if(is_array($prod->timeline) && count($prod->timeline) > 0)
                                                    <div id="flat-tab-timeline-{{ $prod->id }}" class="flat-tab-panel" style="display: none;">
                                                        <div style="position: relative; border-left: 2px solid rgba(255,255,255,0.08); margin-left: 15px; padding-left: 20px; display: flex; flex-direction: column; gap: 20px;">
                                                            @foreach($prod->timeline as $t)
                                                                <div style="position: relative;">
                                                                    <div style="position: absolute; left: -27px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #f87a00; border: 2.5px solid #1a202c;"></div>
                                                                    <span style="background: linear-gradient(135deg, #f79d00, #f87a00); color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; display: inline-block; margin-bottom: 8px; box-shadow: 0 2px 6px rgba(248,122,0,0.2);">{{ $t['year'] ?? 'Mốc thời gian' }}</span>
                                                                    <div class="glass-panel" style="padding: 14px 18px; border: 1px solid var(--border-glow); border-radius: 12px; background: rgba(255,255,255,0.015);">
                                                                        <p style="font-size: 0.88rem; margin: 0; line-height: 1.5; color: var(--text-main);">{{ $t['event'] ?? '' }}</p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Fun Fact -->
                                            @if($prod->fun_fact)
                                                <div class="glass-panel" style="background: rgba(14, 165, 233, 0.03); border: 1.5px solid rgba(14, 165, 233, 0.15); padding: 18px 20px; border-radius: 16px; display: flex; align-items: flex-start; gap: 14px; margin-top: 15px;">
                                                    <div style="font-size: 1.6rem; line-height: 1; filter: drop-shadow(0 0 8px rgba(255,179,0,0.3));">💡</div>
                                                    <div>
                                                        <strong style="color: #0ea5e9; font-size: 0.85rem; display: block; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 800;">BẠN CÓ BIẾT?</strong>
                                                        <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0; line-height: 1.5; text-align: justify;">{{ $prod->fun_fact }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div style="text-align: center; padding: 50px 20px; background: rgba(255,255,255,0.015); border-radius: 20px; border: 1px dashed rgba(255,255,255,0.1); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="font-size: 3.5rem; opacity: 0.4; margin-bottom: 16px; filter: grayscale(1);">{!! $icon !!}</div>
                        <h4 style="color: var(--text-main); font-weight: 700; font-size: 1.15rem; margin: 0 0 8px 0;">Dữ liệu đang cập nhật</h4>
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">{{ $emptyText }}</p>
                    </div>
                @endif
            </div>

            <!-- Styles cho Slider thực đơn -->
            <style>
                .flat-heritage-tab-btn {
                    background: rgba(255, 255, 255, 0.03);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    color: var(--text-muted);
                    padding: 8px 18px;
                    border-radius: 30px;
                    font-size: 0.82rem;
                    cursor: pointer;
                    font-weight: 600;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    transition: all 0.3s ease;
                }
                .flat-heritage-tab-btn:hover {
                    background: rgba(255, 255, 255, 0.08);
                    color: var(--text-main);
                }
                .flat-heritage-tab-btn.active {
                    background: linear-gradient(to right, #f79d00, #f87a00) !important;
                    border-color: transparent !important;
                    color: #ffffff !important;
                    box-shadow: 0 4px 12px rgba(255, 111, 0, 0.25);
                }
                .menu-slider-wrapper {
                    overflow-x: auto;
                    scroll-behavior: smooth;
                    width: 100%;
                    padding: 10px 0;
                    scrollbar-width: none; /* Firefox */
                }
                .menu-slider-wrapper::-webkit-scrollbar {
                    display: none; /* Safari and Chrome */
                }
                .menu-slider-content {
                    display: flex;
                    gap: 20px;
                    transition: all 0.4s ease;
                }
                .menu-slider-btn {
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 48px;
                    height: 48px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.08);
                    border: 1px solid rgba(255, 255, 255, 0.15);
                    color: var(--text-main);
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    backdrop-filter: blur(12px);
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
                }
                .menu-slider-btn:hover {
                    background: var(--primary-grad);
                    border-color: rgba(255, 255, 255, 0.25);
                    color: #ffffff;
                    box-shadow: 0 10px 25px rgba(255, 111, 0, 0.45);
                    transform: translateY(-50%) scale(1.08);
                }
                .menu-slider-btn:active {
                    transform: translateY(-50%) scale(0.95);
                }
                .menu-slider-btn.prev-btn {
                    left: -24px;
                }
                .menu-slider-btn.next-btn {
                    right: -24px;
                }
                
                /* Modal tab buttons */
                .modal-tab-btn {
                    background: rgba(255, 255, 255, 0.03);
                    border: 1px solid var(--border-glow);
                    color: var(--text-muted);
                    padding: 8px 18px;
                    border-radius: 30px;
                    font-size: 0.82rem;
                    font-weight: 700;
                    cursor: pointer;
                    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                    white-space: nowrap;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                }
                .modal-tab-btn:hover {
                    background: rgba(255, 255, 255, 0.08);
                    color: var(--text-main);
                    transform: translateY(-1px);
                }
                .modal-tab-btn.active {
                    background: var(--primary-grad);
                    border-color: rgba(255, 255, 255, 0.15);
                    color: #ffffff;
                    box-shadow: 0 4px 15px rgba(255, 111, 0, 0.3);
                }

                @media (max-width: 768px) {
                    .menu-slider-btn {
                        display: none;
                    }
                    .menu-slider-content .dish-card {
                        flex: 0 0 85% !important;
                    }
                }
            </style>

            <!-- Full Menu Detail Modal -->
            <div id="fullMenuModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(12px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
                <div class="lightbox-content" style="background: var(--bg-card); border: 1px solid var(--border-glow); width: 90%; max-width: 780px; max-height: 85vh; border-radius: 24px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5); overflow: hidden; transform: scale(0.9); transition: transform 0.3s ease; display: flex; flex-direction: column; position: relative;">
                    <!-- Modal Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border-glow); padding: 20px 24px; background: rgba(255,255,255,0.01);">
                        <h3 style="margin: 0; font-size: 1.4rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px; font-family: var(--font-heading);">
                            {!! $icon !!} {{ $modalTitle }}
                        </h3>
                        <button onclick="closeFullMenuModal()" style="background: transparent; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">✕</button>
                    </div>
                    
                    <!-- Modal Content (Scrollable List of Dishes/Services) -->
                    <div style="overflow-y: auto; padding: 24px; flex: 1; display: flex; flex-direction: column; gap: 16px;">
                        <!-- Thanh tìm kiếm & Lọc nhanh -->
                        <div style="position: relative; margin-bottom: 4px;">
                            <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem; pointer-events: none;">🔍</span>
                            <input type="text" id="dishSearchInput" oninput="filterModalDishes()" placeholder="{{ $placeholderSearch }}" style="width: 100%; padding: 12px 16px 12px 46px; background: rgba(255,255,255,0.03); border: 1.5px solid var(--border-glow); border-radius: 14px; color: var(--text-main); font-size: 0.88rem; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 12px rgba(255, 126, 41, 0.15)'" onblur="this.style.borderColor='var(--border-glow)'; this.style.boxShadow='none'">
                        </div>

                        <!-- Bộ lọc nhóm món ăn -->
                        @if($categorySlug === 'dong-anh-food-map')
                        <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none; -ms-overflow-style: none;">
                            <button class="modal-tab-btn active" onclick="filterModalTab('all')" data-tab="all">📋 Tất cả ({{ $items->count() }})</button>
                            @if($items->where('is_signature', true)->count() > 0)
                                <button class="modal-tab-btn" onclick="filterModalTab('signature')" data-tab="signature">★ Món đặc trưng ({{ $items->where('is_signature', true)->count() }})</button>
                            @endif
                            <button class="modal-tab-btn" onclick="filterModalTab('best-price')" data-tab="best-price">💰 Giá tiết kiệm (≤ 200k)</button>
                        </div>
                        @else
                        <div style="display: none;">
                            <!-- Hidden tab selector to keep JS from breaking -->
                            <button class="modal-tab-btn active" onclick="filterModalTab('all')" data-tab="all"></button>
                        </div>
                        @endif

                        <!-- Grid hiển thị món ăn/khóa học/dịch vụ -->
                        <div id="modalMenuGrid" style="display: grid; grid-template-columns: {{ in_array($categorySlug, ['hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']) ? 'repeat(auto-fill, minmax(300px, 1fr))' : '1fr 1fr' }}; gap: 16px;">
                            @foreach($items as $item)
                                @if(in_array($categorySlug, ['hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']))
                                    <div class="dish-card glass-panel" 
                                         data-name="{{ strtolower($item->name) }}" 
                                         data-desc="{{ strtolower($item->description) }}" 
                                         data-signature="false" 
                                         data-price="{{ $item->price ?? 0 }}"
                                         style="background: rgba(255,255,255,0.02); display: flex; flex-direction: column; gap: 0; padding: 0; border-radius: 16px; border: 1.5px solid rgba(16, 185, 129, 0.2); transition: opacity 0.25s ease, transform 0.25s ease, border-color 0.3s ease; opacity: 1; transform: translateY(0); overflow: hidden;">
                                        <div style="position: relative; height: 160px; overflow: hidden; width: 100%;">
                                            <img src="{{ $item->image_path ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80' }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $item->name }}">
                                            <div style="position: absolute; top: 12px; left: 12px; display: flex; flex-direction: column; gap: 6px; z-index: 2;">
                                                @if($item->type === 'experience')
                                                    <span style="background: rgba(3, 105, 161, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">🏛️ Trải nghiệm</span>
                                                @elseif($item->type === 'ticket')
                                                    <span style="background: rgba(21, 128, 61, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">🎫 Vé tham quan</span>
                                                @elseif($item->type === 'service')
                                                    <span style="background: rgba(161, 98, 7, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">🙏 Dịch vụ di tích</span>
                                                @else
                                                    <span style="background: rgba(71, 85, 105, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">🏛️ Khác</span>
                                                @endif
                                                @if($item->discount_note && $item->discount_note !== 'null')
                                                    <span style="background: rgba(221, 107, 32, 0.95); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">🏷️ {{ $item->discount_note }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div style="padding: 20px; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between; gap: 12px;">
                                            <div>
                                                <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); line-height: 1.4; margin: 0 0 8px 0;" title="{{ $item->name }}">{{ $item->name }}</h3>
                                                @if($item->description && $item->description !== 'null' && $item->description !== 'NULL')
                                                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0;">{{ $item->description }}</p>
                                                @endif
                                            </div>
                                            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.06); margin-top: auto;">
                                                <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">💰 Dự chi:</span>
                                                <strong style="color: #10b981; font-size: 1.05rem; font-weight: 800;">
                                                    @if($item->price !== null && $item->price > 0)
                                                        {{ number_format($item->price, 0, ',', '.') }}đ <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-muted);">/ {{ $item->unit ?: 'lượt' }}</span>
                                                    @else
                                                        Theo yêu cầu <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-muted);">/ {{ $item->unit ?: 'lượt' }}</span>
                                                    @endif
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $hasProductDossier = !empty($item->story) || !empty($item->artisans) || !empty($item->heritage_year) || !empty($item->fun_fact) || !empty($item->ingredients) || !empty($item->timeline);
                                    @endphp
                                    <div class="dish-card glass-panel" 
                                         data-name="{{ strtolower($item->name) }}" 
                                         data-desc="{{ strtolower($item->description) }}" 
                                         data-signature="{{ isset($item->is_signature) && $item->is_signature ? 'true' : 'false' }}" 
                                         data-price="{{ $item->price ?? 0 }}"
                                         style="background: rgba(255,255,255,0.02); display: flex; gap: 16px; padding: 16px; border-radius: 12px; border: 1px solid var(--border-glow); transition: all 0.3s ease; opacity: 1; transform: translateY(0); {{ $hasProductDossier ? 'cursor: pointer;' : '' }}"
                                         @if($hasProductDossier)
                                             onclick="closeFullMenuModal(); const el = document.getElementById('heritage-dossier-{{ $item->id }}'); if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'center' }), 350);"
                                             data-product="{{ json_encode($item) }}"
                                             onmouseover="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 15px rgba(255, 126, 41, 0.15)'"
                                             onmouseout="this.style.borderColor='var(--border-glow)'; this.style.boxShadow='none'"
                                         @endif>
                                        <img src="{{ $item->image_path ?: ($categorySlug === 'smart-education-map' ? 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=150&q=80' : ($categorySlug === 'wellness-care' ? 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=150&q=80' : ($categorySlug === 'stay-in-dong-anh' ? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=150&q=80' : ($categorySlug === 'dong-anh-market' ? 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=150&q=80' : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=150&q=80')))) }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; flex-shrink: 0;" alt="{{ $item->name }}">
                                        <div style="display: flex; flex-direction: column; justify-content: space-between; flex: 1;">
                                            <div>
                                                @if($categorySlug === 'dong-anh-market' && $hasProductDossier)
                                                    <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(212, 175, 55, 0.1); border-color: rgba(212, 175, 55, 0.3); color: #ffb300; animation: pulse-trust 2s infinite;">🏺 Xem Chi Tiết Di Sản</span>
                                                @endif
                                                @if($categorySlug === 'smart-education-map')
                                                    <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(52, 152, 219, 0.1); border-color: rgba(52, 152, 219, 0.2); color: #3498db;">⏱️ {{ $item->duration }}</span>
                                                @elseif($categorySlug === 'wellness-care' && $item->duration)
                                                    <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(46, 204, 113, 0.1); border-color: rgba(46, 204, 113, 0.2); color: #2ecc71;">⏱️ {{ $item->duration }}</span>
                                                @elseif($categorySlug === 'stay-in-dong-anh')
                                                    <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(155, 89, 182, 0.1); border-color: rgba(155, 89, 182, 0.2); color: #9b59b6;">🛏️ {{ $item->bed_type }} | 👤 Sức chứa: {{ $item->capacity }}</span>
                                                @elseif($categorySlug === 'dong-anh-market' && $item->star_rating)
                                                    <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block; background: rgba(241, 196, 15, 0.1); border-color: rgba(241, 196, 15, 0.2); color: #f1c40f;">⭐ OCOP: {{ $item->star_rating }}</span>
                                                @elseif(isset($item->is_signature) && $item->is_signature)
                                                    <span class="tag-badge" style="padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-bottom: 4px; display: inline-block;">★ Món đặc trưng</span>
                                                @endif
                                                <h4 style="font-weight: 600; font-size: 0.95rem; color: var(--text-main); margin: 0;">{{ $item->name }}</h4>
                                                @if($item->description && $item->description !== 'null' && $item->description !== 'NULL')
                                                    <p style="font-size: 0.78rem; color: var(--text-muted); margin: 4px 0 0 0; line-height: 1.4;">{{ $item->description }}</p>
                                                @endif
                                            </div>
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px; width: 100%;">
                                                @if($categorySlug === 'smart-education-map')
                                                    <span style="color: var(--primary); font-weight: 700; font-size: 0.95rem; font-family: var(--font-heading); margin-top: 4px;">{{ $item->tuition_fee ?: 'Liên hệ trường' }}</span>
                                                @elseif(isset($item->price) && $item->price > 0)
                                                    <span style="color: var(--primary); font-weight: 700; font-size: 0.95rem; font-family: var(--font-heading); margin-top: 4px;">{{ number_format($item->price, 0, ',', '.') }}đ{{ $categorySlug === 'stay-in-dong-anh' ? ' / đêm' : '' }}</span>
                                                @else
                                                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem; margin-top: 4px;">Liên hệ</span>
                                                @endif

                                                @if(!in_array($categorySlug, ['stay-in-dong-anh', 'wellness-care', 'smart-education-map', 'discover-dong-anh-community-culture-hub']) && isset($item->price) && $item->price > 0)
                                                    <button class="btn-add-to-cart-mini" 
                                                            data-id="{{ $item->id }}" 
                                                            data-type="{{ $categorySlug === 'dong-anh-market' ? 'ocop_product' : 'dish' }}"
                                                            style="background: var(--primary-grad); border: none; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;"
                                                            onclick="addToCart(event, this)">
                                                        Thêm 🛒
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div style="border-top: 1px dashed var(--border-glow); padding: 16px 24px; background: rgba(255,255,255,0.01); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Đang hiển thị: <strong style="color: var(--accent);" id="modalVisibleCount">{{ $items->count() }} {{ $itemUnit }}</strong></span>
                        <button onclick="closeFullMenuModal()" class="btn-primary" style="font-size: 0.85rem; padding: 8px 20px; border-radius: 8px; cursor: pointer;">Đóng</button>
                    </div>
                </div>
            </div>
            
            <!-- Đánh giá bình luận -->
            <div class="detail-section glass-panel" style="padding: 28px;">
                <h2 class="section-title" id="reviewsSection">
                    <span>💬</span> 
                    @if($categorySlug === 'dong-anh-food-map')
                        Đánh giá từ thực khách
                    @elseif($categorySlug === 'stay-in-dong-anh')
                        Đánh giá từ khách lưu trú
                    @elseif($categorySlug === 'wellness-care')
                        Đánh giá từ khách hàng
                    @elseif($categorySlug === 'dong-anh-market')
                        Đánh giá từ người mua
                    @elseif($categorySlug === 'smart-education-map')
                        Đánh giá từ phụ huynh & học sinh
                    @else
                        Đánh giá từ cộng đồng
                    @endif
                    <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: normal;">
                        ({{ $eatery->reviews->count() }} nhận xét)
                    </span>
                </h2>
                
                <!-- Hiển thị thông báo thành công khi gửi đánh giá -->
                @if(session('success'))
                    <div class="glass-panel" style="background: rgba(32, 178, 170, 0.1); border-color: var(--accent); padding: 14px 20px; border-radius: 8px; color: var(--accent); margin-bottom: 20px; font-size: 0.95rem;">
                        {{ session('success') }}
                    </div>
                @endif
                
                <!-- Form Đánh giá mới -->
                <div class="review-submit glass-panel" style="background: rgba(255,255,255,0.02);">
                    <h3 style="font-size: 1.1rem; margin-bottom: 16px;">✍️ Gửi nhận xét của bạn</h3>
                    <form action="/dia-diem/reviews/{{ $eatery->id }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="review-form-group">
                            <label class="review-form-label">Tên của bạn</label>
                            <input type="text" name="user_name" class="form-input" required placeholder="Nhập tên..." value="{{ session('user_name') ?: '' }}">
                        </div>
                        
                        <div class="review-form-group">
                            <label class="review-form-label">Chấm điểm sao (1 - 5 sao)</label>
                            <div class="stars-rating-select" id="starsSelector">
                                <span data-value="1">☆</span>
                                <span data-value="2">☆</span>
                                <span data-value="3">☆</span>
                                <span data-value="4">☆</span>
                                <span data-value="5">☆</span>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="">
                        </div>
                        
                        <div class="review-form-group">
                            <label class="review-form-label">Nhận xét của bạn</label>
                            <textarea name="comment" class="form-input" rows="4" 
                                @if($categorySlug === 'dong-anh-food-map')
                                    placeholder="Nhập cảm nhận của bạn về món ăn, không gian, dịch vụ quán..."
                                @elseif($categorySlug === 'stay-in-dong-anh')
                                    placeholder="Nhập cảm nhận của bạn về phòng ốc, dịch vụ, tiện nghi nơi lưu trú..."
                                @elseif($categorySlug === 'wellness-care')
                                    placeholder="Nhập cảm nhận của bạn về chất lượng khám chữa bệnh, chăm sóc, phục vụ..."
                                @elseif($categorySlug === 'dong-anh-market')
                                    placeholder="Nhập cảm nhận của bạn về chất lượng sản phẩm, giá cả, thái độ phục vụ..."
                                @elseif($categorySlug === 'smart-education-map')
                                    placeholder="Nhập cảm nhận của bạn về chất lượng đào tạo, cơ sở vật chất, giáo viên..."
                                @else
                                    placeholder="Nhập nhận xét của bạn về địa điểm này..."
                                @endif
                                style="resize: vertical;"></textarea>
                        </div>
                        
                        <div class="review-form-group">
                            <label class="review-form-label">Thêm Ảnh / Video ngắn (Tùy chọn)</label>
                            <input type="file" name="media[]" class="form-input" multiple accept="image/*,video/*">
                        </div>
                        
                        <button type="submit" class="btn-primary" style="margin-top: 8px;">Gửi đánh giá</button>
                    </form>
                </div>
                
                <!-- Danh sách bình luận -->
                @if($eatery->reviews->count() > 0)
                    <div class="review-list" style="display: flex; flex-direction: column; gap: 24px; margin-top: 24px;">
                        @foreach($eatery->reviews->take(5) as $rev)
                            <div class="review-card glass-panel" style="padding: 24px; border-radius: 20px; border: 1px solid var(--border-glow); background: rgba(255, 255, 255, 0.015); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.015); transition: all 0.3s ease; display: flex; flex-direction: column; gap: 16px;">
                                <!-- User Info Header -->
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 14px;">
                                        <!-- Avatar with gradient -->
                                        <div style="width: 46px; height: 46px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #ff8b3d 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(255, 126, 41, 0.2); text-transform: uppercase;">
                                            {{ substr($rev->user_name, 0, 2) }}
                                        </div>
                                        
                                        <!-- Name & Stars -->
                                        <div>
                                            <span style="font-weight: 700; color: var(--text-main); font-size: 1.05rem; display: block;">{{ $rev->user_name }}</span>
                                            <div style="color: #ffb03a; font-size: 0.95rem; margin-top: 2px; display: flex; gap: 3px;">
                                                @for($i=1; $i<=5; $i++)
                                                    @if($i <= $rev->rating)
                                                        <span style="color: #ffb03a; text-shadow: 0 0 8px rgba(255, 176, 58, 0.4);">★</span>
                                                    @else
                                                        <span style="color: var(--border-glow);">★</span>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Date Badge -->
                                    <span style="font-size: 0.8rem; color: var(--text-muted); background: rgba(255,255,255,0.04); padding: 4px 12px; border-radius: 30px; border: 1px solid var(--border-glow); display: inline-flex; align-items: center; gap: 4px;">
                                        📅 {{ $rev->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                <!-- Review Comment Text -->
                                <p style="font-size: 0.98rem; color: var(--text-main); line-height: 1.7; margin: 0; white-space: pre-line; font-weight: 450;">{{ $rev->comment }}</p>
                                
                                <!-- Attached Media (Photos/Videos) -->
                                @if($rev->media && $rev->media->count() > 0)
                                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 4px;">
                                        @foreach($rev->media as $mediaItem)
                                            @if($mediaItem->file_type === 'image')
                                                <div style="position: relative; width: 100px; height: 100px; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-glow); box-shadow: 0 4px 12px rgba(0,0,0,0.12); cursor: pointer; transition: all 0.25s ease;" onmouseover="this.style.transform='scale(1.05)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-glow)'">
                                                    <img src="{{ $mediaItem->file_path }}" alt="Review Image" style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                            @else
                                                <div style="position: relative; width: 140px; height: 100px; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-glow); box-shadow: 0 4px 12px rgba(0,0,0,0.12);">
                                                    <video src="{{ $mediaItem->file_path }}" style="width: 100%; height: 100%; object-fit: cover;" controls></video>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Official Seller Reply Box -->
                                @if($rev->seller_reply)
                                    <div class="seller-reply-bubble" style="margin-top: 8px; padding: 18px 22px; background: rgba(255, 126, 41, 0.04); border: 1.5px solid rgba(255, 126, 41, 0.15); border-radius: 18px; font-size: 0.9rem; box-shadow: 0 8px 24px rgba(255, 126, 41, 0.02); position: relative;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                                            <strong style="color: var(--primary); display: flex; align-items: center; gap: 8px; font-size: 0.92rem; font-weight: 800;">
                                                <span style="font-size: 1.2rem;">🏪</span> 
                                                @if($categorySlug === 'dong-anh-food-map')
                                                    Phản hồi từ chủ quán
                                                @elseif($categorySlug === 'stay-in-dong-anh')
                                                    Phản hồi từ quản lý
                                                @elseif($categorySlug === 'wellness-care')
                                                    Phản hồi từ cơ sở
                                                @elseif($categorySlug === 'dong-anh-market')
                                                    Phản hồi từ chủ cửa hàng
                                                @elseif($categorySlug === 'smart-education-map')
                                                    Phản hồi từ nhà trường
                                                @else
                                                     Phản hồi từ địa điểm
                                                @endif
                                            </strong>
                                            <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">
                                                @if($categorySlug === 'smart-education-map')
                                                    Ban giám hiệu
                                                @elseif($categorySlug === 'stay-in-dong-anh' || $categorySlug === 'wellness-care')
                                                    Ban quản lý
                                                @else
                                                    Chủ cơ sở
                                                @endif
                                            </span>
                                        </div>
                                        <p style="margin: 0; color: var(--text-main); line-height: 1.65; font-style: italic; font-weight: 500;">
                                            "{{ $rev->seller_reply }}"
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($eatery->reviews->count() > 5)
                        <div style="display: flex; justify-content: center; margin-top: 24px;">
                            <button onclick="openAllReviewsModal()" class="btn-secondary" style="font-size: 0.95rem; padding: 12px 28px; border-radius: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 126, 41, 0.05); border: 1.5px solid rgba(255, 126, 41, 0.2); color: var(--primary); transition: all 0.3s ease; outline: none;" onmouseover="this.style.background='rgba(255, 126, 41, 0.1)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='rgba(255, 126, 41, 0.05)'; this.style.transform='none'">
                                💬 Xem tất cả đánh giá & phản hồi ({{ $eatery->reviews->count() }} nhận xét)
                            </button>
                        </div>
                    @endif
                @else
                    <div style="text-align: center; padding: 40px 20px; background: rgba(255,255,255,0.015); border-radius: 20px; border: 1px dashed rgba(255,255,255,0.1); margin-top: 24px;">
                        <div style="font-size: 3rem; margin-bottom: 12px; filter: grayscale(1); opacity: 0.5;">💬</div>
                        <h4 style="color: var(--text-main); font-weight: 700; font-size: 1.1rem; margin: 0 0 8px 0;">Chưa có nhận xét</h4>
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">Hãy là người đầu tiên chia sẻ cảm nhận thực tế của bạn về địa điểm này!</p>
                    </div>
                @endif
            </div>

            @if(in_array($categorySlug, ['dong-anh-food-map', 'dong-anh-market']))
            <!-- Bridge Text and upgraded Trust Hub Card -->
            <div style="margin-top: 40px; margin-bottom: 20px; padding: 0 10px; display: flex; align-items: flex-start; gap: 12px; background: rgba(32, 178, 170, 0.03); border: 1px dashed rgba(32, 178, 170, 0.2); padding: 16px; border-radius: 12px;">
                <span style="font-size: 1.5rem; filter: drop-shadow(0 0 5px rgba(32, 178, 170, 0.5));">🛡️</span>
                <span style="font-size: 0.9rem; line-height: 1.6; color: var(--text-muted); font-style: italic;">
                    Nhằm đảm bảo sức khỏe cộng đồng và bảo tồn tinh hoa ẩm thực địa phương, nhà hàng tự nguyện công khai toàn bộ hồ sơ nguồn gốc thực phẩm dưới sự giám sát chặt chẽ của các cơ quan chức năng xã Đông Anh.
                </span>
            </div>

            <!-- Báo cáo Minh bạch An toàn & Truy xuất nguồn gốc thực phẩm sạch -->
            <div id="trust-hub-section" class="detail-section glass-panel trust-hub-card" style="padding: 28px; margin-bottom: 40px; position: relative;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; border-bottom: 1px dashed rgba(32, 178, 170, 0.25); padding-bottom: 16px;">
                    <h2 class="section-title" style="margin: 0; border: none; padding: 0;">
                        <span style="display: inline-block; filter: drop-shadow(0 0 8px rgba(32, 178, 170, 0.5));">🛡️</span> Minh Bạch An Toàn & Truy Xuất Số
                    </h2>
                    @if($eatery->foodSafetyCertificate)
                        <span class="trust-badge" style="background: rgba(32, 178, 170, 0.1); border: 1px solid var(--accent); color: var(--accent); font-weight: 700; font-size: 0.8rem; padding: 4px 12px; border-radius: 20px;">
                            ✓ ĐÃ XÁC MINH CSDL
                        </span>
                    @else
                        <span class="trust-badge" style="background: rgba(255, 193, 7, 0.1); border: 1px solid #ffc107; color: #ffc107; font-weight: 700; font-size: 0.8rem; padding: 4px 12px; border-radius: 20px;">
                            ⚠ ĐANG CHỜ CẬP NHẬT
                        </span>
                    @endif
                </div>

                @if($eatery->foodSafetyCertificate || $eatery->foodSupplyContracts->count() > 0 || $eatery->purchaseInvoices->count() > 0 || $eatery->dailyFoodLogs->count() > 0)
                    <!-- Khiên An toàn Vàng kim / Xanh ngọc (Trust Shield Banner) -->
                    <div class="trust-shield-banner glass-panel" style="background: linear-gradient(135deg, rgba(32, 178, 170, 0.08) 0%, rgba(0, 150, 136, 0.02) 100%); border: 1px solid rgba(32, 178, 170, 0.25); padding: 18px 24px; border-radius: 16px; margin-bottom: 24px; display: flex; gap: 16px; align-items: center;">
                        <div style="font-size: 2.2rem; animation: pulse-trust 2s infinite;">🛡️</div>
                        <div>
                            <h4 style="font-size: 1.05rem; color: var(--accent); font-weight: 700; margin-bottom: 4px; text-transform: uppercase;">Cơ sở Đủ Điều Kiện An Toàn Thực Phẩm</h4>
                            <p style="font-size: 0.88rem; line-height: 1.6; color: var(--text-main); margin: 0;">
                                Nhà hàng đã công khai toàn bộ hồ sơ pháp lý, hợp đồng cung cấp thực phẩm sạch và nhật ký kiểm tra hàng ngày trên hệ thống dữ liệu số của xã Đông Anh.
                            </p>
                        </div>
                    </div>

                    <div class="trust-tabs-container">
                        <div class="trust-tab-buttons" style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 8px; border-bottom: 1px solid var(--border-glow);">
                            <button class="trust-tab-btn active" data-trust-tab="trust-cert" style="white-space: nowrap;">🛡️ Chứng Nhận ATTP</button>
                            <button class="trust-tab-btn" data-trust-tab="trust-contracts" style="white-space: nowrap;">📜 Hợp Đồng Cung Ứng</button>
                            <button class="trust-tab-btn" data-trust-tab="trust-invoices" style="white-space: nowrap;">🧾 Hóa Đơn Mua Hàng</button>
                            <button class="trust-tab-btn" data-trust-tab="trust-logs" style="white-space: nowrap;">📅 Nhật Ký An Toàn</button>
                        </div>

                        <!-- Tab 1: Giấy chứng nhận VSATTP -->
                        <div id="trust-cert" class="trust-tab-content active-content">
                            @if($eatery->foodSafetyCertificate)
                                <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start;">
                                    <div>
                                        <div class="cert-image-preview" style="position: relative; width: 140px; height: 190px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-glow); cursor: pointer;" onclick="openTrustLightbox('{{ $eatery->foodSafetyCertificate->image_path }}', 'Giấy chứng nhận VSATTP số {{ $eatery->foodSafetyCertificate->certificate_number }}')">
                                            <img src="{{ $eatery->foodSafetyCertificate->image_path }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                                <span style="font-size: 1.5rem;">🔍</span>
                                            </div>
                                        </div>
                                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 8px; text-align: center; max-width: 140px; line-height: 1.3;">
                                            Nhấp vào ảnh để xem chi tiết hoặc tải về
                                        </div>
                                    </div>
                                    <div style="flex: 1; min-width: 250px;">
                                        <h4 style="color: var(--accent); font-size: 1.15rem; margin-bottom: 12px; font-weight: 700;">Giấy Chứng Nhận Đủ Điều Kiện ATTP</h4>
                                        <table style="width: 100%; font-size: 0.9rem; border-collapse: collapse;">
                                            <tr style="border-bottom: 1px dashed var(--border-glow);">
                                                <td style="padding: 8px 0; color: var(--text-muted); width: 140px;">Số chứng chỉ:</td>
                                                <td style="padding: 8px 0; font-weight: 600; color: var(--text-main);">{{ $eatery->foodSafetyCertificate->certificate_number }}</td>
                                            </tr>
                                            <tr style="border-bottom: 1px dashed var(--border-glow);">
                                                <td style="padding: 8px 0; color: var(--text-muted);">Cơ quan cấp:</td>
                                                <td style="padding: 8px 0; font-weight: 600; color: var(--text-main);">{{ $eatery->foodSafetyCertificate->issued_by }}</td>
                                            </tr>
                                            <tr style="border-bottom: 1px dashed var(--border-glow);">
                                                <td style="padding: 8px 0; color: var(--text-muted);">Ngày cấp:</td>
                                                <td style="padding: 8px 0; font-weight: 600; color: var(--text-main);">{{ $eatery->foodSafetyCertificate->issued_at->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr style="border-bottom: 1px dashed var(--border-glow);">
                                                <td style="padding: 8px 0; color: var(--text-muted);">Hạn dùng đến:</td>
                                                <td style="padding: 8px 0; font-weight: 600; color: var(--text-main);">{{ $eatery->foodSafetyCertificate->expired_at->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr style="border-bottom: 1px dashed var(--border-glow);">
                                                <td style="padding: 8px 0; color: var(--text-muted);">Thời hạn giám sát:</td>
                                                <td style="padding: 8px 0; font-weight: 700;">
                                                    @php
                                                        $daysLeft = $eatery->foodSafetyCertificate->days_left;
                                                        $expiryStatus = $eatery->foodSafetyCertificate->expiry_status;
                                                    @endphp
                                                    @if($expiryStatus === 'valid')
                                                        <span style="color: #2ecc71; display: inline-flex; align-items: center; gap: 6px;">
                                                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #2ecc71; box-shadow: 0 0 10px #2ecc71; animation: pulse-trust 2s infinite;"></span> Còn {{ $daysLeft }} ngày (An toàn hoạt động)
                                                        </span>
                                                    @elseif($expiryStatus === 'warning')
                                                        <span style="color: #ff9f43; display: inline-flex; align-items: center; gap: 6px; animation: pulse-text 2s infinite;">
                                                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ff9f43; box-shadow: 0 0 10px #ff9f43;"></span> Sắp hết hạn (Còn {{ $daysLeft }} ngày) - Hệ thống đang chuẩn bị hồ sơ gia hạn tự động
                                                        </span>
                                                    @else
                                                        <span style="color: #e74c3c; display: inline-flex; align-items: center; gap: 6px;">
                                                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #e74c3c; box-shadow: 0 0 10px #e74c3c;"></span> Đã quá hạn {{ abs($daysLeft) }} ngày - Yêu cầu gia hạn khẩn cấp
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: var(--text-muted);">Trạng thái pháp lý:</td>
                                                <td style="padding: 8px 0;">
                                                    @if($expiryStatus === 'expired')
                                                        <span style="color: #e74c3c; font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">🔴 Hết hiệu lực / Tạm đình chỉ</span>
                                                    @else
                                                        <span style="color: #2ecc71; font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">🟢 Đang hoạt động / Được bảo hộ</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Con Dấu Số Thẩm Định & QR Thẩm Định -->
                                        <div style="margin-top: 20px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap; background: linear-gradient(135deg, rgba(39, 174, 96, 0.05) 0%, rgba(46, 204, 113, 0.01) 100%); border: 1px solid rgba(39, 174, 96, 0.2); padding: 16px; border-radius: 16px; box-shadow: inset 0 0 12px rgba(39, 174, 96, 0.02);">
                                            <div style="font-size: 2.2rem; filter: drop-shadow(0 4px 8px rgba(46, 204, 113, 0.3)); flex-shrink: 0; animation: pulse-trust 2s infinite;">🛡️</div>
                                            <div style="flex: 1; min-width: 200px;">
                                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 4px;">
                                                    <h5 style="margin: 0; font-size: 0.85rem; font-weight: 800; color: #27ae60; text-transform: uppercase; letter-spacing: 0.5px;">CON DẤU SỐ ĐÔNG ANH</h5>
                                                    <span style="background: rgba(39, 174, 96, 0.15); color: #27ae60; border: 1px solid #2ecc71; font-size: 0.6rem; padding: 1px 6px; border-radius: 4px; font-weight: 800;">ĐÃ ĐỐI CHIẾU CƠ SỞ DỮ LIỆU</span>
                                                </div>
                                                <p style="margin: 0; font-size: 0.8rem; color: var(--text-main); line-height: 1.4;">
                                                    Hệ thống xác thực liên kết trực tiếp với Phòng Y tế Xã Đông Anh. Chứng thực 100% tài liệu thật, còn hiệu lực và được phê duyệt chính thức bởi UBND Xã Đông Anh.
                                                </p>
                                            </div>
                                            <div style="flex-shrink: 0; background: #ffffff; padding: 8px; border-radius: 12px; border: 1px solid rgba(39, 174, 96, 0.2); box-shadow: 0 4px 10px rgba(0,0,0,0.08); text-align: center; cursor: pointer;" onclick="openTrustLightbox('https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=https://donganh.hanoi.gov.vn/phong-ban-y-te-xac-minh-id-{{ $eatery->foodSafetyCertificate->id }}', 'Mã QR Xác Thực Công Hành của UBND Xã Đông Anh')">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=https://donganh.hanoi.gov.vn/phong-ban-y-te-xac-minh-id-{{ $eatery->foodSafetyCertificate->id }}" style="width: 60px; height: 60px; display: block; mix-blend-mode: multiply;">
                                                <span style="display: block; font-size: 0.55rem; color: var(--text-muted); margin-top: 4px; font-weight: 700;">QUÉT THẨM ĐỊNH</span>
                                            </div>
                                        </div>

                                        <!-- Nút Báo Cáo Phản Ánh ATTP Của Khách Hàng -->
                                        <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: rgba(231, 76, 60, 0.02); border: 1px dashed rgba(231, 76, 60, 0.2); padding: 12px 16px; border-radius: 12px;">
                                            <div style="display: flex; align-items: center; gap: 8px; min-width: 250px; flex: 1;">
                                                <span style="font-size: 1.1rem; animation: pulse-trust 2s infinite;">📢</span>
                                                <span style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                                                    Bạn ăn thấy quán không đảm bảo vệ sinh như cam kết? Phản hồi ẩn danh ngay để bảo vệ sức khỏe cộng đồng.
                                                </span>
                                            </div>
                                            <button onclick="openFeedbackModal('{{ $eatery->name }}')" class="btn-secondary" style="font-size: 0.75rem; padding: 6px 12px; border-radius: 8px; font-weight: 700; color: #e74c3c; border-color: rgba(231, 76, 60, 0.2); background: rgba(231, 76, 60, 0.04); display: inline-flex; align-items: center; gap: 4px; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.background='rgba(231, 76, 60, 0.08)'; this.style.color='#c0392b';" onmouseout="this.style.background='rgba(231, 76, 60, 0.04)'; this.style.color='#e74c3c';">
                                                🚨 Gửi Phản Ánh ATTP
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p style="color: var(--text-muted); font-style: italic; text-align: center; padding: 20px 0;">Chưa cập nhật Giấy chứng nhận ATTP của cơ sở.</p>
                            @endif
                        </div>

                        <!-- Tab 2: Hợp đồng cung ứng thực phẩm sạch -->
                        <div id="trust-contracts" class="trust-tab-content">
                            @if($eatery->foodSupplyContracts->count() > 0)
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                                    @foreach($eatery->foodSupplyContracts as $contract)
                                        <div class="glass-panel" style="padding: 16px; background: rgba(255,255,255,0.01); display: flex; gap: 16px; align-items: center; border: 1px solid var(--border-glow); border-radius: 12px;">
                                            <div style="position: relative; width: 60px; height: 85px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border-glow); cursor: pointer; flex-shrink: 0;" onclick="openTrustLightbox('{{ $contract->image_path }}', 'Bản quét hợp đồng cung cấp sạch từ {{ $contract->supplier_name }}')">
                                                <img src="{{ $contract->image_path }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                                    <span style="font-size: 1rem;">🔍</span>
                                                </div>
                                            </div>
                                            <div style="min-width: 0; flex: 1;">
                                                <h5 style="margin: 0 0 4px 0; font-size: 0.95rem; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $contract->supplier_name }}</h5>
                                                <p style="margin: 0 0 6px 0; font-size: 0.8rem; color: var(--accent); font-weight: 600; line-height: 1.3;">🌾 {{ $contract->items_supplied }}</p>
                                                <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">Hiệu lực: {{ $contract->signed_at->format('d/m/Y') }} - {{ $contract->expired_at->format('d/m/Y') }}</p>
                                                <button class="btn-secondary" style="font-size: 0.7rem; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;" onclick="openTrustLightbox('{{ $contract->image_path }}', 'Bản quét hợp đồng cung cấp sạch (đã ẩn chi tiết thương mại)')">
                                                    📄 Xem hợp đồng mẫu
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p style="color: var(--text-muted); font-style: italic; text-align: center; padding: 20px 0;">Chưa cập nhật thông tin hợp đồng cung cấp thực phẩm sạch.</p>
                            @endif
                        </div>

                        <!-- Tab 3: Hóa đơn mua bán thực tế -->
                        <div id="trust-invoices" class="trust-tab-content">
                            @if($eatery->purchaseInvoices->count() > 0)
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                                    @foreach($eatery->purchaseInvoices as $invoice)
                                        @php
                                            $itemLower = mb_strtolower($invoice->items_summary);
                                            $icon = '🧾';
                                            $iconBg = 'rgba(32, 178, 170, 0.1)';
                                            $iconBorder = 'rgba(32, 178, 170, 0.3)';
                                            if (str_contains($itemLower, 'thịt') || str_contains($itemLower, 'heo') || str_contains($itemLower, 'bò') || str_contains($itemLower, 'xương')) {
                                                $icon = '🥩';
                                                $iconBg = 'rgba(231, 76, 60, 0.1)';
                                                $iconBorder = 'rgba(231, 76, 60, 0.3)';
                                            } elseif (str_contains($itemLower, 'gạo') || str_contains($itemLower, 'bột') || str_contains($itemLower, 'nếp')) {
                                                $icon = '🌾';
                                                $iconBg = 'rgba(255, 179, 0, 0.1)';
                                                $iconBorder = 'rgba(255, 179, 0, 0.3)';
                                            } elseif (str_contains($itemLower, 'rau') || str_contains($itemLower, 'quả') || str_contains($itemLower, 'hành') || str_contains($itemLower, 'củ')) {
                                                $icon = '🥬';
                                                $iconBg = 'rgba(46, 204, 113, 0.1)';
                                                $iconBorder = 'rgba(46, 204, 113, 0.3)';
                                            } elseif (str_contains($itemLower, 'cá') || str_contains($itemLower, 'hải sản')) {
                                                $icon = '🐟';
                                                $iconBg = 'rgba(52, 152, 219, 0.1)';
                                                $iconBorder = 'rgba(52, 152, 219, 0.3)';
                                            }
                                        @endphp
                                        <div class="glass-panel" style="padding: 16px; background: rgba(255,255,255,0.01); display: flex; gap: 16px; align-items: center; border: 1px solid var(--border-glow); border-radius: 12px;">
                                            <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $iconBg }}; border: 1px solid {{ $iconBorder }}; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                                                {{ $icon }}
                                            </div>
                                            <div style="min-width: 0; flex: 1;">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 2px;">
                                                    <div style="font-size: 0.75rem; color: var(--accent); font-weight: 700; text-transform: uppercase;">
                                                        📅 NHẬP HÀNG: {{ $invoice->invoice_date->format('d/m/Y') }}
                                                    </div>
                                                    <span style="font-size: 0.65rem; background: rgba(32, 178, 170, 0.1); border: 1px solid var(--accent); color: var(--accent); padding: 1px 6px; border-radius: 4px; font-weight: bold; white-space: nowrap;">ĐÃ ĐỐI CHIẾU</span>
                                                </div>
                                                <h5 style="margin: 0 0 4px 0; font-size: 0.92rem; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $invoice->supplier_name }}</h5>
                                                <p style="margin: 0 0 6px 0; font-size: 0.8rem; color: var(--text-muted); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $invoice->items_summary }}</p>
                                                <button class="btn-secondary" style="font-size: 0.7rem; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;" onclick="openTrustLightbox('{{ $invoice->image_path }}', 'Bản quét hóa đơn nhập hàng sạch (dữ liệu giá trị thương mại đã được che bảo mật)')">
                                                    🔍 Xem hóa đơn
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p style="color: var(--text-muted); font-style: italic; text-align: center; padding: 20px 0;">Chưa cập nhật hóa đơn mua bán gần đây.</p>
                            @endif
                        </div>

                        <!-- Tab 4: Nhật ký hàng ngày -->
                        <div id="trust-logs" class="trust-tab-content">
                            @if($eatery->dailyFoodLogs->count() > 0)
                                <div style="display: flex; flex-direction: column; gap: 14px;">
                                    @foreach($eatery->dailyFoodLogs->take(7) as $log)
                                        @php
                                            $isOfficial = $log->checker_role === 'official';
                                        @endphp
                                        <div class="glass-panel" style="padding: 18px; border-radius: 16px; transition: all 0.3s ease;
                                            @if($isOfficial)
                                                border: 1px solid rgba(231, 76, 60, 0.3); background: rgba(231, 76, 60, 0.02); box-shadow: 0 4px 15px rgba(231, 76, 60, 0.05);
                                            @else
                                                border: 1px solid rgba(46, 204, 113, 0.2); background: rgba(46, 204, 113, 0.01); box-shadow: 0 4px 15px rgba(46, 204, 113, 0.02);
                                            @endif
                                        " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                            
                                            <!-- Header with logo and badge -->
                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; border-bottom: 1px dashed var(--border-glow); padding-bottom: 10px; margin-bottom: 12px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    @if($isOfficial)
                                                        <span style="font-size: 1.1rem;">🏢</span>
                                                        <span style="font-weight: 700; color: #e74c3c; font-size: 0.92rem;">CƠ QUAN KIỂM TRA CHỨC NĂNG</span>
                                                    @else
                                                        <span style="font-size: 1.1rem;">👨‍🍳</span>
                                                        <span style="font-weight: 700; color: #2ecc71; font-size: 0.92rem;">TỰ KIỂM TRA HÀNG NGÀY</span>
                                                    @endif
                                                </div>
                                                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">📅 Ngày {{ $log->log_date->format('d/m/Y') }}</span>
                                            </div>

                                            <!-- Checklist columns -->
                                            <div style="font-size: 0.88rem; display: flex; flex-direction: column; gap: 8px; line-height: 1.6;">
                                                <div style="display: flex; gap: 8px; align-items: flex-start;">
                                                    <span style="color: #2ecc71; font-weight: bold;">✓</span>
                                                    <div>
                                                        <strong style="color: var(--primary);">Nguồn gốc nguyên liệu:</strong> 
                                                        <span style="color: var(--text-main);">{{ $log->ingredients_origin }}</span>
                                                    </div>
                                                </div>
                                                <div style="display: flex; gap: 8px; align-items: flex-start;">
                                                    <span style="color: #2ecc71; font-weight: bold;">✓</span>
                                                    <div>
                                                        <strong style="color: var(--primary);">Bảo quản & Điều kiện:</strong> 
                                                        <span style="color: var(--text-main);">{{ $log->storage_condition }}</span>
                                                    </div>
                                                </div>
                                                <div style="display: flex; gap: 8px; align-items: flex-start;">
                                                    <span style="color: #2ecc71; font-weight: bold;">✓</span>
                                                    <div>
                                                        <strong style="color: var(--primary);">Kết quả kiểm nghiệm:</strong> 
                                                        <span style="color: #2ecc71; font-weight: 700;">ĐẠT TIÊU CHUẨN VỆ SINH</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Footer of card with badge inspector logo -->
                                            <div style="margin-top: 12px; border-top: 1px dashed var(--border-glow); padding-top: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.8rem;">
                                                <span style="color: var(--text-muted);">
                                                    Người phê duyệt: <strong style="color: var(--text-main);">{{ $log->checker_name }}</strong>
                                                </span>
                                                @if($isOfficial)
                                                    <span style="background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; color: #e74c3c; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 0.7rem; display: inline-flex; align-items: center; gap: 4px;">
                                                        🛡️ ĐÃ THẨM ĐỊNH
                                                    </span>
                                                @else
                                                    <span style="color: var(--text-muted); font-size: 0.72rem; font-style: italic; opacity: 0.7;">
                                                        Ghi nhận tự động
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p style="color: var(--text-muted); font-style: italic; text-align: center; padding: 20px 0;">Chưa ghi nhận nhật ký kiểm tra vệ sinh hàng ngày.</p>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Phân hệ thông tin chưa cập nhật -->
                    <div style="text-align: center; padding: 30px 20px;">
                        <div style="font-size: 3rem; margin-bottom: 16px; filter: drop-shadow(0 0 10px rgba(255,193,7,0.5));">🛡️</div>
                        <h4 style="font-size: 1.1rem; color: var(--text-main); font-weight: 700; margin-bottom: 8px;">HỆ THỐNG TRUY XUẤT CHƯA KÍCH HOẠT</h4>
                        <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 500px; margin: 0 auto 20px auto; line-height: 1.6;">
                            Cơ sở kinh doanh này đang chuẩn bị hồ sơ minh bạch nguồn gốc thực phẩm đầu vào. Vui lòng quay lại sau khi hồ sơ được ban quản lý phê duyệt.
                        </p>
                        <div style="font-size: 0.85rem; color: var(--primary); font-weight: 600; background: var(--bg-btn-secondary); display: inline-block; padding: 8px 20px; border-radius: 30px; border: 1px dashed var(--border-glow);">
                            📞 Hotline Ban Quản Lý ATTP Đông Anh: 024.3883.2241
                        </div>
                    </div>
                @endif
            </div>
            @endif

            <!-- Section: Hình ảnh thực tế từ thực khách Check-in -->
            @if(isset($checkinPhotos) && $checkinPhotos->count() > 0)
                <div class="detail-section glass-panel" style="padding: 28px; margin-bottom: 40px;">
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        📸 Hình Ảnh Thực Tế Từ Thực Khách Check-in ({{ $checkinPhotos->count() }})
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 14px;">
                        @foreach($checkinPhotos as $photo)
                            @php
                                $imgPath = $photo->image_path;
                                $fullImg = str_starts_with($imgPath, 'http') ? $imgPath : asset($imgPath);
                            @endphp
                            <div style="position: relative; height: 130px; border-radius: 14px; overflow: hidden; border: 1px solid var(--border-glow); cursor: pointer; transition: transform 0.25s ease;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='none'" onclick="openTrustLightbox('{{ $fullImg }}', 'Ảnh check-in bởi {{ $photo->display_name }}')">
                                <img src="{{ $fullImg }}" alt="Check-in Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                <div style="position: absolute; bottom: 0; inset-x: 0; background: linear-gradient(0deg, rgba(0,0,0,0.85) 0%, transparent 100%); padding: 6px 8px; color: #fff; font-size: 0.75rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    👤 {{ $photo->display_name }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Right Side: Sidebar Information and Coordinates Map -->
        <aside>
            <!-- Thông tin liên hệ -->
            <div class="sidebar-widget glass-panel">
                <h3 style="font-size: 1.2rem; margin-bottom: 24px; border-bottom: 2px solid rgba(255,126,41,0.3); padding-bottom: 12px; color: var(--text-main); font-weight: 800;">
                    📌 Thông tin chi tiết
                </h3>
                <ul class="widget-info-list">
                    <li class="widget-info-item" style="padding: 14px 16px; margin-bottom: 12px; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 14px;" onmouseover="this.style.background='var(--bg-btn-secondary)'; this.style.borderColor='var(--border-glow-hover)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(255,255,255,0.02)'; this.style.borderColor='rgba(255,255,255,0.06)'; this.style.transform='none';">
                        <span class="widget-info-icon" style="background: rgba(255, 126, 41, 0.1); border-radius: 12px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: 1px solid rgba(255, 126, 41, 0.2); flex-shrink: 0; box-shadow: inset 0 0 10px rgba(255, 126, 41, 0.05);">📞</span>
                        <div>
                            <strong style="display: block; font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Điện thoại liên hệ</strong>
                            <a href="tel:{{ $eatery->phone }}" style="color: var(--text-main); font-weight: 700; font-size: 1.1rem; display: inline-block; margin-top: 2px;">{{ $eatery->phone ?: 'Chưa cập nhật' }}</a>
                        </div>
                    </li>
                    <li class="widget-info-item" style="padding: 14px 16px; margin-bottom: 12px; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 14px;" onmouseover="this.style.background='var(--bg-btn-secondary)'; this.style.borderColor='var(--border-glow-hover)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(255,255,255,0.02)'; this.style.borderColor='rgba(255,255,255,0.06)'; this.style.transform='none';">
                        <span class="widget-info-icon" style="background: rgba(32, 178, 170, 0.1); border-radius: 12px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: 1px solid rgba(32, 178, 170, 0.2); flex-shrink: 0; box-shadow: inset 0 0 10px rgba(32, 178, 170, 0.05);">🕒</span>
                        <div>
                            <strong style="display: block; font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Giờ mở cửa</strong>
                            <span style="color: var(--text-main); font-weight: 600; display: inline-block; margin-top: 2px; font-size: 0.95rem;">{{ $eatery->opening_hours ?: 'Đang cập nhật' }}</span>
                        </div>
                    </li>
                    @if(!in_array($eatery->category->slug, ['smart-education-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']))
                    <li class="widget-info-item" style="padding: 14px 16px; margin-bottom: 12px; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 14px;" onmouseover="this.style.background='var(--bg-btn-secondary)'; this.style.borderColor='var(--border-glow-hover)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(255,255,255,0.02)'; this.style.borderColor='rgba(255,255,255,0.06)'; this.style.transform='none';">
                        <span class="widget-info-icon" style="background: rgba(255, 179, 0, 0.1); border-radius: 12px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: 1px solid rgba(255, 179, 0, 0.2); flex-shrink: 0; box-shadow: inset 0 0 10px rgba(255, 179, 0, 0.05);">💰</span>
                        <div>
                            <strong style="display: block; font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Mức giá tham khảo</strong>
                            <span style="color: #ffb300; font-weight: 800; font-size: 1.1rem; display: inline-block; margin-top: 2px; white-space: nowrap; text-shadow: 0 0 10px rgba(255,179,0,0.2);">{{ $eatery->price_range ?: 'Đang cập nhật' }}</span>
                        </div>
                    </li>
                    @endif
                    <li class="widget-info-item" style="padding: 14px 16px; margin-bottom: 0; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 14px;" onmouseover="this.style.background='var(--bg-btn-secondary)'; this.style.borderColor='var(--border-glow-hover)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(255,255,255,0.02)'; this.style.borderColor='rgba(255,255,255,0.06)'; this.style.transform='none';">
                        <span class="widget-info-icon" style="background: rgba(255, 193, 7, 0.1); border-radius: 12px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: 1px solid rgba(255, 193, 7, 0.2); flex-shrink: 0; box-shadow: inset 0 0 10px rgba(255, 193, 7, 0.05);">⭐</span>
                        <div>
                            <strong style="display: block; font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Đánh giá trung bình</strong>
                            <span style="font-weight: 900; color: #ffc107; font-size: 1.15rem; display: inline-block; margin-top: 2px; text-shadow: 0 0 10px rgba(255,193,7,0.4);">★ {{ number_format($eatery->average_rating, 1) }} <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500; text-shadow: none;">/ 5.0</span></span>
                        </div>
                    </li>
                </ul>
                @if($eatery->phone)
                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed var(--border-glow);">
                    <a href="tel:{{ $eatery->phone }}" class="btn-primary" style="width: 100%; justify-content: center; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-weight: 700; background: linear-gradient(135deg, #ff7e29 0%, #ff4f18 100%); box-shadow: 0 4px 15px rgba(255, 126, 41, 0.3); border: none;">
                        📞 Gọi điện liên hệ ngay
                    </a>
                </div>
                @endif
            </div>
            
            <!-- Mã QR Code Thông Minh -->
            <div class="sidebar-widget glass-panel" style="text-align: center; margin-bottom: 24px; padding-top: 32px;">
                <h3 style="font-size: 1.2rem; margin-bottom: 12px; color: var(--text-main); font-weight: 800;">
                    @if($categorySlug === 'dong-anh-food-map')
                        📲 Mã QR Nhà hàng
                    @elseif($categorySlug === 'stay-in-dong-anh')
                        📲 Mã QR Nơi lưu trú
                    @elseif($categorySlug === 'wellness-care')
                        📲 Mã QR Cơ sở chăm sóc
                    @elseif($categorySlug === 'dong-anh-market')
                        📲 Mã QR Cửa hàng
                    @elseif($categorySlug === 'smart-education-map')
                        📲 Mã QR Nhà trường
                    @else
                        📲 Mã QR Địa điểm
                    @endif
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; padding: 0 10px;">
                    @if($categorySlug === 'dong-anh-food-map')
                        Lưu mã QR hoặc đưa cho bạn bè quét để mở nhanh thực đơn quán này!
                    @elseif($categorySlug === 'stay-in-dong-anh')
                        Lưu mã QR hoặc đưa cho bạn bè quét để mở nhanh thông tin và đặt phòng nơi lưu trú này!
                    @elseif($categorySlug === 'wellness-care')
                        Lưu mã QR hoặc đưa cho bạn bè quét để mở nhanh thông tin dịch vụ chăm sóc sức khỏe này!
                    @elseif($categorySlug === 'dong-anh-market')
                        Lưu mã QR hoặc đưa cho bạn bè quét để mở nhanh danh sách sản phẩm OCOP & đặc sản này!
                    @elseif($categorySlug === 'smart-education-map')
                        Lưu mã QR hoặc đưa cho bạn bè quét để mở nhanh trang giới thiệu nhà trường này!
                    @else
                        Lưu mã QR hoặc đưa cho bạn bè quét để mở nhanh trang giới thiệu địa điểm này!
                    @endif
                </p>
                <div style="background: #ffffff; padding: 16px; border-radius: 20px; display: inline-block; box-shadow: 0 8px 24px rgba(0,0,0,0.15), inset 0 0 0 1px rgba(0,0,0,0.05); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='none'">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(url()->current()) }}" alt="QR Code {{ $eatery->name }}" style="width: 180px; height: 180px; display: block; mix-blend-mode: multiply;">
                </div>
            </div>
            
            <!-- Định vị vị trí & chỉ đường -->
            <div class="sidebar-widget glass-panel">
                <h3 style="font-size: 1.2rem; margin-bottom: 16px; color: var(--text-main); font-weight: 800; border-bottom: 2px solid rgba(32, 178, 170, 0.3); padding-bottom: 12px;">
                    🗺️ Vị trí & Chỉ đường
                </h3>
                <p style="font-size: 0.8rem; color: var(--text-muted);">
                    📍 Vĩ độ: <strong>{{ $eatery->latitude }}</strong> | Kinh độ: <strong>{{ $eatery->longitude }}</strong>
                </p>
                
                <div class="mini-map-container">
                    <div id="miniMap" style="width: 100%; height: 100%;"></div>
                </div>
                
                <!-- Google Geolocation Distance Widget -->
                <div id="distanceWidget" class="glass-panel" style="padding: 12px; margin-top: 14px; background: rgba(32,178,170,0.05); border-color: rgba(32,178,170,0.1); display: block;">
                    <p style="font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                        <span>🚗</span> Khoảng cách đến vị trí của bạn: <strong id="distanceKm" style="color: var(--accent);">Đang tính...</strong>
                    </p>
                </div>
                
                <a id="directionsLink" href="https://www.google.com/maps/dir/?api=1&destination={{ number_format($eatery->latitude, 6, '.', '') }},{{ number_format($eatery->longitude, 6, '.', '') }}" target="_blank" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px; font-size: 0.9rem;">
                    🗺️ Hướng dẫn đường đi (Google Maps)
                </a>
            </div>
        </aside>
        
    </div>
</div>

<!-- Feedback Modal for Food Safety -->
<div id="feedbackModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(8px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <div class="lightbox-content" style="background: var(--bg-card); border: 1px solid var(--border-glow); width: 90%; max-width: 500px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4); overflow: hidden; transform: scale(0.9); transition: transform 0.3s ease; padding: 24px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border-glow); padding-bottom: 14px; margin-bottom: 16px;">
            <h4 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                📢 Báo Cáo Phản Ánh ATTP
            </h4>
            <button onclick="closeFeedbackModal()" style="background: transparent; border: none; font-size: 1.3rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#e74c3c'" onmouseout="this.style.color='var(--text-muted)'">✕</button>
        </div>
        
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0; margin-bottom: 16px; line-height: 1.5;">
            Mọi thông tin phản hồi của bạn về <strong id="feedbackEateryName" style="color: var(--accent);"></strong> đều được mã hóa ẩn danh hoàn toàn để đảm bảo an toàn riêng tư, đồng thời gửi trực tiếp tới Ban Quản Lý xã Đông Anh.
        </p>
        
        <!-- Cảnh báo nếu ở xa quán -->
        <div id="feedbackFarWarning" style="display: none; background: rgba(243, 156, 18, 0.06); border: 1px solid rgba(243, 156, 18, 0.25); padding: 12px; border-radius: 10px; font-size: 0.8rem; color: #d35400; margin-bottom: 16px; line-height: 1.45;">
            ⚠️ <strong>Xác thực từ xa:</strong> Để tránh phản ánh giả mạo dìm hàng từ đối thủ, do bạn đang ở ngoài phạm vi của quán, vui lòng đính kèm <strong>ảnh chụp hóa đơn mua hàng</strong> hoặc <strong>ảnh món ăn tại cơ sở</strong> để làm minh chứng bắt buộc.
        </div>
        
        <form id="feedbackForm" onsubmit="submitFeedback(event)">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">1. Chọn nội dung phản ánh</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--text-main); background: rgba(255,255,255,0.02); border: 1px solid var(--border-glow); padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='rgba(231,76,60,0.3)';" onmouseout="this.style.borderColor='var(--border-glow)';">
                        <input type="radio" name="feedback_type" value="dirty_utensils" required style="accent-color: #e74c3c; cursor: pointer;">
                        <span>🍽️ Bát đũa bẩn</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--text-main); background: rgba(255,255,255,0.02); border: 1px solid var(--border-glow); padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='rgba(231,76,60,0.3)';" onmouseout="this.style.borderColor='var(--border-glow)';">
                        <input type="radio" name="feedback_type" value="bad_ingredients" style="accent-color: #e74c3c; cursor: pointer;">
                        <span>🥩 Thực phẩm lạ</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--text-main); background: rgba(255,255,255,0.02); border: 1px solid var(--border-glow); padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='rgba(231,76,60,0.3)';" onmouseout="this.style.borderColor='var(--border-glow)';">
                        <input type="radio" name="feedback_type" value="no_gloves" style="accent-color: #e74c3c; cursor: pointer;">
                        <span>👨‍🍳 Không găng tay</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--text-main); background: rgba(255,255,255,0.02); border: 1px solid var(--border-glow); padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='rgba(231,76,60,0.3)';" onmouseout="this.style.borderColor='var(--border-glow)';">
                        <input type="radio" name="feedback_type" value="dirty_space" style="accent-color: #e74c3c; cursor: pointer;">
                        <span>🧹 Rác, mất vệ sinh</span>
                    </label>
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">2. Chi tiết phản ánh</label>
                <textarea required name="description" rows="3" class="form-input" placeholder="Mô tả cụ thể sự việc bạn quan sát được để giúp ban quản lý nhanh chóng xác minh..." style="width: 100%; border-radius: 8px; font-size: 0.85rem; padding: 10px; resize: vertical;"></textarea>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">3. Đính kèm ảnh thực tế (Tùy chọn)</label>
                <input type="file" name="feedback_image" accept="image/*" class="form-input" style="width: 100%; font-size: 0.8rem; padding: 6px;">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeFeedbackModal()" class="btn-secondary" style="font-size: 0.85rem; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer;">Hủy</button>
                <button type="submit" class="btn-primary" style="font-size: 0.85rem; padding: 8px 16px; border-radius: 8px; font-weight: 700; background: #e74c3c; border-color: #c0392b; cursor: pointer; color: #fff;">Gửi Báo Cáo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal hiển thị toàn bộ đánh giá & Phân loại sao -->
<div id="allReviewsModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(8px); align-items: center; justify-content: center; opacity: 0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
    <div class="lightbox-content" style="background: var(--bg-card); border: 1px solid var(--border-glow); width: 90%; max-width: 780px; height: 85vh; border-radius: 24px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45); overflow: hidden; transform: scale(0.9); transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); display: flex; flex-direction: column; position: relative;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glow); padding: 20px 28px; background: rgba(255, 255, 255, 0.015);">
            <div>
                <h4 style="margin: 0; font-size: 1.35rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                    💬 Toàn bộ Đánh giá & Phản hồi
                </h4>
                <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: var(--text-muted);">
                    {{ $eatery->name }} • {{ $eatery->reviews->count() }} lượt nhận xét
                </p>
            </div>
            <button onclick="closeAllReviewsModal()" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-glow); width: 36px; height: 36px; border-radius: 50%; font-size: 1.1rem; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.color='var(--primary)'; this.style.borderColor='rgba(var(--primary-rgb), 0.3)'; this.style.background='rgba(var(--primary-rgb), 0.05)'" onmouseout="this.style.color='var(--text-muted)'; this.style.borderColor='var(--border-glow)'; this.style.background='rgba(255,255,255,0.04)'">✕</button>
        </div>
        
        <!-- Star Filter Tabs Segmented Control -->
        <div style="padding: 16px 28px; border-bottom: 1px solid var(--border-glow); background: rgba(255, 255, 255, 0.005); overflow-x: auto; scrollbar-width: none;">
            <div style="display: flex; gap: 8px; align-items: center;">
                <button class="review-modal-tab active" onclick="filterReviewStars('all')" data-star="all" style="white-space: nowrap; font-size: 0.85rem; font-weight: 700; padding: 10px 18px; border-radius: 30px; border: 1.5px solid rgba(var(--primary-rgb), 0.2); background: rgba(var(--primary-rgb), 0.06); color: var(--primary); cursor: pointer; transition: all 0.25s ease;">
                    🌟 Tất cả ({{ $eatery->reviews->count() }})
                </button>
                @for($s = 5; $s >= 1; $s--)
                    @php
                        $countForStar = $eatery->reviews->where('rating', $s)->count();
                    @endphp
                    <button class="review-modal-tab" onclick="filterReviewStars({{ $s }})" data-star="{{ $s }}" style="white-space: nowrap; font-size: 0.85rem; font-weight: 600; padding: 8px 16px; border-radius: 30px; border: 1.5px solid var(--border-glow); background: transparent; color: var(--text-muted); cursor: pointer; transition: all 0.25s ease;" onmouseover="if(!this.classList.contains('active')){ this.style.borderColor='rgba(var(--primary-rgb), 0.2)'; this.style.color='var(--text-main)'; }" onmouseout="if(!this.classList.contains('active')){ this.style.borderColor='var(--border-glow)'; this.style.color='var(--text-muted)'; }">
                        {{ $s }} ★ ({{ $countForStar }})
                    </button>
                @endfor
            </div>
        </div>

        <!-- Scrollable Reviews List Container -->
        <div id="modalReviewListScroll" style="flex: 1; overflow-y: auto; padding: 28px; display: flex; flex-direction: column; gap: 20px; background: rgba(0,0,0,0.02);">
            @foreach($eatery->reviews as $rev)
                <div class="modal-review-card-item" data-rating="{{ $rev->rating }}" style="padding: 24px; border-radius: 20px; border: 1px solid var(--border-glow); background: var(--bg-card); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01); display: flex; flex-direction: column; gap: 14px; transition: all 0.3s ease;">
                    <!-- Header -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <!-- Avatar -->
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary-grad); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2); text-transform: uppercase;">
                                {{ substr($rev->user_name, 0, 2) }}
                            </div>
                            <!-- Name & Stars -->
                            <div>
                                <span style="font-weight: 700; color: var(--text-main); font-size: 1rem; display: block;">{{ $rev->user_name }}</span>
                                <div style="color: #ffb03a; font-size: 0.9rem; margin-top: 2px; display: flex; gap: 2px;">
                                    @for($i=1; $i<=5; $i++)
                                        @if($i <= $rev->rating)
                                            <span style="color: #ffb03a; text-shadow: 0 0 6px rgba(255, 176, 58, 0.4);">★</span>
                                        @else
                                            <span style="color: var(--border-glow);">★</span>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <span style="font-size: 0.78rem; color: var(--text-muted); background: rgba(255,255,255,0.03); padding: 4px 12px; border-radius: 30px; border: 1px solid var(--border-glow);">
                            📅 {{ $rev->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <!-- Text -->
                    <p style="font-size: 0.95rem; color: var(--text-main); line-height: 1.65; margin: 0; white-space: pre-line; font-weight: 450;">{{ $rev->comment }}</p>

                    <!-- Media -->
                    @if($rev->media && $rev->media->count() > 0)
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 2px;">
                            @foreach($rev->media as $mediaItem)
                                @if($mediaItem->file_type === 'image')
                                    <div style="position: relative; width: 90px; height: 90px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-glow); box-shadow: 0 4px 10px rgba(0,0,0,0.1); cursor: pointer; transition: all 0.25s ease;" onmouseover="this.style.transform='scale(1.05)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-glow)'">
                                        <img src="{{ $mediaItem->file_path }}" alt="Review Media" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                @else
                                    <div style="position: relative; width: 130px; height: 90px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-glow); box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                        <video src="{{ $mediaItem->file_path }}" style="width: 100%; height: 100%; object-fit: cover;" controls></video>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Reply -->
                    @if($rev->seller_reply)
                        <div class="seller-reply-bubble" style="margin-top: 4px; padding: 16px 20px; background: rgba(var(--primary-rgb), 0.04); border: 1px solid rgba(var(--primary-rgb), 0.12); border-radius: 16px; font-size: 0.88rem; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.01);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; flex-wrap: wrap; gap: 8px;">
                                <strong style="color: var(--primary); display: flex; align-items: center; gap: 6px; font-size: 0.9rem; font-weight: 800;">
                                    <span style="font-size: 1.15rem;">🏪</span> 
                                    @if($categorySlug === 'dong-anh-food-map')
                                        Phản hồi từ chủ quán
                                    @elseif($categorySlug === 'stay-in-dong-anh')
                                        Phản hồi từ quản lý
                                    @elseif($categorySlug === 'wellness-care')
                                        Phản hồi từ cơ sở
                                    @elseif($categorySlug === 'dong-anh-market')
                                        Phản hồi từ chủ cửa hàng
                                    @elseif($categorySlug === 'smart-education-map')
                                        Phản hồi từ nhà trường
                                    @else
                                        Phản hồi từ địa điểm
                                    @endif
                                </strong>
                                <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;">
                                    @if($categorySlug === 'smart-education-map')
                                        Ban giám hiệu
                                    @elseif($categorySlug === 'stay-in-dong-anh' || $categorySlug === 'wellness-care')
                                        Ban quản lý
                                    @else
                                        Chủ cơ sở
                                    @endif
                                </span>
                            </div>
                            <p style="margin: 0; color: var(--text-main); line-height: 1.6; font-style: italic; font-weight: 500;">
                                "{{ $rev->seller_reply }}"
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach

            @if(isset($checkinReviews) && $checkinReviews->count() > 0)
                @foreach($checkinReviews as $cRev)
                    <div class="modal-review-card-item" data-rating="{{ $cRev->rating }}" style="padding: 24px; border-radius: 20px; border: 1px solid var(--border-glow); background: var(--bg-card); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01); display: flex; flex-direction: column; gap: 14px; transition: all 0.3s ease;">
                        <!-- Header -->
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); text-transform: uppercase;">
                                    {{ substr($cRev->display_name, 0, 2) }}
                                </div>
                                <div>
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 1rem; display: block;">{{ $cRev->display_name }}</span>
                                    <span style="font-size: 0.72rem; color: #0ea5e9; font-weight: 600;">📍 Đã check-in tại quán</span>
                                    <div style="color: #ffb03a; font-size: 0.9rem; margin-top: 2px; display: flex; gap: 2px;">
                                        @for($i=1; $i<=5; $i++)
                                            @if($i <= $cRev->rating)
                                                <span style="color: #ffb03a; text-shadow: 0 0 6px rgba(255, 176, 58, 0.4);">★</span>
                                            @else
                                                <span style="color: var(--border-glow);">★</span>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            <span style="font-size: 0.78rem; color: var(--text-muted); background: rgba(255,255,255,0.03); padding: 4px 12px; border-radius: 30px; border: 1px solid var(--border-glow);">
                                📅 {{ $cRev->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <!-- Text -->
                        @if($cRev->comment)
                            <p style="font-size: 0.95rem; color: var(--text-main); line-height: 1.65; margin: 0; white-space: pre-line; font-weight: 450;">{{ $cRev->comment }}</p>
                        @endif

                        <!-- Photo -->
                        @if($cRev->image_path)
                            @php
                                $cImg = str_starts_with($cRev->image_path, 'http') ? $cRev->image_path : asset($cRev->image_path);
                            @endphp
                            <div style="position: relative; width: 140px; height: 110px; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-glow); box-shadow: 0 4px 10px rgba(0,0,0,0.1); cursor: pointer; transition: all 0.25s ease;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='none'" onclick="openTrustLightbox('{{ $cImg }}', 'Ảnh check-in từ {{ $cRev->display_name }}')">
                                <img src="{{ $cImg }}" alt="Check-in Photo" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif

            <!-- Empty State for filtered stars -->
            <div id="modalReviewsEmptyState" style="display: none; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; text-align: center;">
                <div style="font-size: 3.5rem; margin-bottom: 16px; filter: drop-shadow(0 0 10px rgba(255,126,41,0.25));">💬</div>
                <h5 style="margin: 0 0 8px 0; font-size: 1.15rem; color: var(--text-main); font-weight: 700;">Chưa có nhận xét nào!</h5>
                <p style="margin: 0; font-size: 0.88rem; color: var(--text-muted); max-width: 320px; line-height: 1.5;">
                    @if($categorySlug === 'dong-anh-food-map')
                        Không tìm thấy đánh giá nào có mức xếp hạng sao này cho quán ăn.
                    @elseif($categorySlug === 'stay-in-dong-anh')
                        Không tìm thấy đánh giá nào có mức xếp hạng sao này cho nơi lưu trú.
                    @elseif($categorySlug === 'wellness-care')
                        Không tìm thấy đánh giá nào có mức xếp hạng sao này cho cơ sở.
                    @elseif($categorySlug === 'dong-anh-market')
                        Không tìm thấy đánh giá nào có mức xếp hạng sao này cho cửa hàng.
                    @elseif($categorySlug === 'smart-education-map')
                        Không tìm thấy đánh giá nào có mức xếp hạng sao này cho nhà trường.
                    @else
                        Không tìm thấy đánh giá nào có mức xếp hạng sao này cho địa điểm.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let miniMap;
    const eateryLat = {{ number_format($eatery->latitude, 6, '.', '') }};
    const eateryLng = {{ number_format($eatery->longitude, 6, '.', '') }};
    const eateryName = "{{ $eatery->name }}";
    const categoryIcon = "{{ $eatery->category->icon }}";
    let userCurrentDistanceKm = null;

    document.addEventListener("DOMContentLoaded", function() {
        // 1. Khởi tạo mini map
        miniMap = L.map('miniMap', {
            zoomControl: false,
            scrollWheelZoom: false
        }).setView([eateryLat, eateryLng], 15);

        // Lớp nền phù hợp chế độ Sáng/Tối (Sử dụng Google Maps chính thức cho bản đồ sáng)
        let currentTheme = localStorage.getItem('theme') || 'dark';
        let tileUrl = currentTheme === 'light' 
            ? 'https://mt1.google.com/vt/lyrs=m&hl=vi&x={x}&y={y}&z={z}'
            : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            
        let activeTileLayer = L.tileLayer(tileUrl, {
            attribution: currentTheme === 'light' ? '&copy; Google Maps' : '&copy; OpenStreetMap &copy; CARTO'
        }).addTo(miniMap);

        // Lắng nghe sự kiện đổi chế độ Sáng/Tối để đổi lớp nền bản đồ tức thì
        document.addEventListener('theme-changed', function(e) {
            const nextTheme = e.detail.theme;
            const nextTileUrl = nextTheme === 'light'
                ? 'https://mt1.google.com/vt/lyrs=m&hl=vi&x={x}&y={y}&z={z}'
                : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            
            miniMap.removeLayer(activeTileLayer);
            activeTileLayer = L.tileLayer(nextTileUrl, {
                attribution: nextTheme === 'light' ? '&copy; Google Maps' : '&copy; OpenStreetMap &copy; CARTO'
            }).addTo(miniMap);
        });

        // Custom Marker
        const customIcon = L.divIcon({
            html: `<div style="background-color: var(--primary); width: 28px; height: 28px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">${categoryIcon}</div>`,
            className: 'custom-leaflet-marker',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });

        L.marker([eateryLat, eateryLng], { icon: customIcon })
            .bindPopup(`<strong style="font-family: var(--font-heading);">${eateryName}</strong><br>📍 ${eateryLat}, ${eateryLng}`)
            .addTo(miniMap)
            .openPopup();

        // 2. Logic Chọn Sao Đánh giá (Interactive Stars Picker)
        const stars = document.querySelectorAll("#starsSelector span");
        const ratingInput = document.getElementById("ratingInput");

        stars.forEach(star => {
            star.addEventListener("click", function() {
                const val = this.getAttribute("data-value");
                ratingInput.value = val;
                
                stars.forEach(s => {
                    const sVal = s.getAttribute("data-value");
                    if (parseInt(sVal) <= parseInt(val)) {
                        s.classList.add("selected");
                        s.textContent = "★";
                    } else {
                        s.classList.remove("selected");
                        s.textContent = "☆";
                    }
                });
            });
        });

        // 4. Heritage tabs click logic
        const tabBtns = document.querySelectorAll(".heritage-tab-btn");
        const tabContents = document.querySelectorAll(".heritage-tab-content");

        tabBtns.forEach(btn => {
            btn.addEventListener("click", function() {
                tabBtns.forEach(b => b.classList.remove("active"));
                this.classList.add("active");

                tabContents.forEach(c => c.classList.remove("active-content"));
                
                const targetId = this.getAttribute("data-tab");
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add("active-content");
                }
            });
        });

        // 5. AI Speech Synthesis Narrator Play Logic
        const playBtn = document.getElementById("playAudioBtn");
        const eq = document.getElementById("audioEqualizer");
        const statusText = document.getElementById("audioStatusText");
        const playBtnIcon = document.getElementById("playBtnIcon");

        if (playBtn) {
            let synth = window.speechSynthesis;
            let utterance = null;
            let isSpeaking = false;

            const audioText = `{!! isset($dossier) ? addslashes($dossier['audio_narrative']) : '' !!}`;

            playBtn.addEventListener("click", function() {
                if (!synth) {
                    alert("Trình duyệt của bạn không hỗ trợ công nghệ đọc giọng nói AI.");
                    return;
                }

                if (isSpeaking) {
                    synth.cancel();
                    isSpeaking = false;
                    playBtn.classList.remove("playing");
                    eq.classList.remove("playing-audio");
                    playBtnIcon.textContent = "🔊";
                    statusText.textContent = "Bấm để lắng nghe giọng đọc AI thuyết minh văn hóa món ăn";
                } else {
                    synth.cancel();
                    
                    utterance = new SpeechSynthesisUtterance(audioText);
                    utterance.lang = "vi-VN";
                    utterance.rate = 0.92; // Majestic, calm storytelling pace

                    // Set voice if available
                    const voices = synth.getVoices();
                    const viVoice = voices.find(voice => voice.lang.includes("VI") || voice.lang.includes("vi"));
                    if (viVoice) {
                        utterance.voice = viVoice;
                    }

                    utterance.onend = function() {
                        isSpeaking = false;
                        playBtn.classList.remove("playing");
                        eq.classList.remove("playing-audio");
                        playBtnIcon.textContent = "🔊";
                        statusText.textContent = "Thuyết minh hoàn thành. Bấm để nghe lại!";
                    };

                    utterance.onerror = function(event) {
                        console.error("SpeechSynthesis error:", event);
                        isSpeaking = false;
                        playBtn.classList.remove("playing");
                        eq.classList.remove("playing-audio");
                        playBtnIcon.textContent = "🔊";
                        statusText.textContent = "Đã xảy ra lỗi khi phát âm thanh thuyết minh.";
                    };

                    synth.speak(utterance);
                    isSpeaking = true;
                    playBtn.classList.add("playing");
                    eq.classList.add("playing-audio");
                    playBtnIcon.textContent = "⏸️";
                    statusText.textContent = "Đang thuyết minh về di sản ẩm thực Đông Anh... Lắng nghe văn hóa!";
                }
            });

            // Ensure voice synthesis stops if user leaves or navigates away
            window.addEventListener("beforeunload", function() {
                if (synth) {
                    synth.cancel();
                }
            });
        }

        // 6. Logic chọn Tab của phần Minh bạch thực phẩm (Trust Hub Tabs)
        const trustTabBtns = document.querySelectorAll(".trust-tab-btn");
        const trustTabContents = document.querySelectorAll(".trust-tab-content");

        trustTabBtns.forEach(btn => {
            btn.addEventListener("click", function() {
                trustTabBtns.forEach(b => b.classList.remove("active"));
                this.classList.add("active");

                trustTabContents.forEach(c => c.classList.remove("active-content"));
                
                const targetId = this.getAttribute("data-trust-tab");
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add("active-content");
                }
            });
        });
    });

    // Lightbox Popup logic cho ảnh giấy tờ VSATTP, hợp đồng, hóa đơn
    function openTrustLightbox(imgSrc, captionText) {
        const lightbox = document.getElementById("trustLightbox");
        const img = document.getElementById("trustLightboxImg");
        const cap = document.getElementById("trustLightboxCaption");
        
        if (lightbox && img && cap) {
            img.src = imgSrc;
            cap.textContent = captionText;
            lightbox.style.display = "flex";
            setTimeout(() => {
                lightbox.style.opacity = "1";
                lightbox.querySelector(".lightbox-content").style.transform = "scale(1)";
            }, 50);
        }
    }

    function closeTrustLightbox() {
        const lightbox = document.getElementById("trustLightbox");
        if (lightbox) {
            lightbox.style.opacity = "0";
            lightbox.querySelector(".lightbox-content").style.transform = "scale(0.9)";
            setTimeout(() => {
                lightbox.style.display = "none";
            }, 300);
        }
    }

    function scrollToTrustHub(event) {
        event.preventDefault();
        const trustSection = document.getElementById("trust-hub-section");
        if (trustSection) {
            trustSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            // Tạo hiệu ứng phát sáng nhẹ thu hút sự chú ý
            setTimeout(() => {
                trustSection.style.transition = 'all 0.5s ease-in-out';
                trustSection.style.boxShadow = '0 0 25px rgba(32, 178, 170, 0.5)';
                trustSection.style.borderColor = 'rgba(32, 178, 170, 0.8)';
                setTimeout(() => {
                    trustSection.style.boxShadow = 'none';
                    trustSection.style.borderColor = 'var(--border-glow)';
                }, 1500);
            }, 800);
        }
    }
    // 7. Logic Popup Báo Cáo Phản Ánh ATTP Dành Cho Thực Khách
    function openFeedbackModal(eateryName) {
        const modal = document.getElementById("feedbackModal");
        const nameSpan = document.getElementById("feedbackEateryName");
        const farWarning = document.getElementById("feedbackFarWarning");
        const fileInput = document.querySelector("input[name='feedback_image']");

        // Xác định xem người dùng có ở xa quán hay không (hoặc chặn GPS)
        let isFarAway = false;
        if (userCurrentDistanceKm === null || userCurrentDistanceKm === 'denied' || (typeof userCurrentDistanceKm === 'number' && userCurrentDistanceKm > 0.15)) {
            isFarAway = true;
        }

        if (farWarning) {
            if (isFarAway) {
                farWarning.style.display = "block";
                if (fileInput) fileInput.required = true; // Bắt buộc đính kèm ảnh minh chứng
            } else {
                farWarning.style.display = "none";
                if (fileInput) fileInput.required = false;
            }
        }

        if (modal && nameSpan) {
            nameSpan.textContent = eateryName;
            modal.style.display = "flex";
            setTimeout(() => {
                modal.style.opacity = "1";
                modal.querySelector(".lightbox-content").style.transform = "scale(1)";
            }, 50);
        }
    }

    function closeFeedbackModal() {
        const modal = document.getElementById("feedbackModal");
        if (modal) {
            modal.style.opacity = "0";
            modal.querySelector(".lightbox-content").style.transform = "scale(0.9)";
            setTimeout(() => {
                modal.style.display = "none";
                document.getElementById("feedbackForm").reset();
            }, 300);
        }
    }

    function submitFeedback(event) {
        event.preventDefault();
        
        const fileInput = document.querySelector("input[name='feedback_image']");
        let isFarAway = false;
        if (userCurrentDistanceKm === null || userCurrentDistanceKm === 'denied' || (typeof userCurrentDistanceKm === 'number' && userCurrentDistanceKm > 0.15)) {
            isFarAway = true;
        }

        // Kiểm tra bằng chứng bắt buộc nếu ở xa quán
        if (isFarAway && (!fileInput || !fileInput.files || fileInput.files.length === 0)) {
            showPremiumToast("Thiếu Minh Chứng!", "Do bạn đang gửi từ xa, vui lòng đính kèm ảnh hóa đơn hoặc món ăn để xác thực.", "error");
            return;
        }

        showPremiumToast("Báo Cáo Thành Công!", "Phản hồi đã được gửi ẩn danh về Ban Quản Lý Đông Anh để xem xét thực tế.", "success");
        closeFeedbackModal();
    }

    // Hàm hiển thị Toast thông báo đa dạng kiểu dáng cực kỳ cao cấp
    function showPremiumToast(title, message, type = "success") {
        const toast = document.createElement("div");
        toast.style.position = "fixed";
        toast.style.top = "25px";
        toast.style.right = "25px";
        toast.style.zIndex = "999999";
        toast.style.padding = "16px 24px";
        toast.style.borderRadius = "14px";
        toast.style.color = "#ffffff";
        toast.style.display = "flex";
        toast.style.alignItems = "center";
        toast.style.gap = "14px";
        toast.style.opacity = "0";
        toast.style.transform = "translateY(-20px) scale(0.9)";
        toast.style.transition = "all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)";
        toast.style.border = "1px solid rgba(255,255,255,0.12)";
        toast.style.backdropFilter = "blur(10px)";
        toast.style.maxWidth = "350px";

        let icon = "🔔";
        if (type === "success") {
            toast.style.background = "linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)";
            toast.style.boxShadow = "0 12px 30px rgba(231, 76, 60, 0.4)";
            icon = "🚨";
        } else if (type === "warning") {
            toast.style.background = "linear-gradient(135deg, #ff9f43 0%, #ff9f43 100%)";
            toast.style.boxShadow = "0 12px 30px rgba(255, 159, 67, 0.4)";
            icon = "⚠️";
        } else {
            toast.style.background = "linear-gradient(135deg, #ee5253 0%, #ff2222 100%)";
            toast.style.boxShadow = "0 12px 30px rgba(238, 82, 83, 0.4)";
            icon = "❌";
        }

        toast.innerHTML = `
            <span style="font-size: 1.6rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));">${icon}</span>
            <div>
                <strong style="display: block; font-size: 0.92rem; margin-bottom: 2px; font-weight: 800;">${title}</strong>
                <span style="font-size: 0.8rem; opacity: 0.95; line-height: 1.4; display: block;">${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = "1";
            toast.style.transform = "translateY(0) scale(1)";
        }, 100);

        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(-20px) scale(0.9)";
            setTimeout(() => {
                toast.remove();
            }, 400);
        }, 5000);
    }

    // 3. Tích hợp định vị trình duyệt tính khoảng cách Km (Browser Geolocation API)
    function getUserDistance() {
        if (!navigator.geolocation) {
            document.getElementById("distanceKm").textContent = "Không hỗ trợ GPS";
            userCurrentDistanceKm = 'denied';
            return;
        }

        navigator.geolocation.getCurrentPosition(function(position) {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            
            // Công thức Haversine tính khoảng cách đường thẳng giữa 2 tọa độ GPS
            const R = 6371; // Bán kính Trái Đất (km)
            const dLat = (eateryLat - userLat) * Math.PI / 180;
            const dLng = (eateryLng - userLng) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(userLat * Math.PI / 180) * Math.cos(eateryLat * Math.PI / 180) * 
                Math.sin(dLng/2) * Math.sin(dLng/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c; // Khoảng cách (km)

            userCurrentDistanceKm = distance;
            document.getElementById("distanceKm").textContent = distance.toFixed(2) + " km";

            // Cập nhật link Hướng dẫn đường đi có cả điểm xuất phát thực tế (userLat, userLng)
            const directionsLink = document.getElementById("directionsLink");
            if (directionsLink) {
                directionsLink.href = `https://www.google.com/maps/dir/?api=1&origin=${userLat},${userLng}&destination=${eateryLat},${eateryLng}`;
            }

            // Vẽ vị trí người dùng lên bản đồ Leaflet
            if (typeof miniMap !== 'undefined' && miniMap) {
                const userIcon = L.divIcon({
                    html: `<div style="background-color: #3b82f6; width: 22px; height: 22px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 0 10px rgba(59, 130, 246, 0.8); display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">🔵</div>`,
                    className: 'user-leaflet-marker',
                    iconSize: [22, 22],
                    iconAnchor: [11, 11]
                });
                
                L.marker([userLat, userLng], { icon: userIcon })
                    .bindPopup(`<strong style="font-family: var(--font-heading);">Vị trí của bạn</strong><br>📍 ${userLat.toFixed(4)}, ${userLng.toFixed(4)}`)
                    .addTo(miniMap);

                // Zoom fit hiển thị cả vị trí người dùng và cửa hàng
                const group = new L.featureGroup([
                    L.marker([eateryLat, eateryLng]),
                    L.marker([userLat, userLng])
                ]);
                miniMap.fitBounds(group.getBounds().pad(0.15));
            }
        }, function(error) {
            document.getElementById("distanceKm").textContent = "Bị từ chối GPS";
            userCurrentDistanceKm = 'denied';
        });
    }

    // 8. Slider Thực đơn & Popup Modal Xem toàn bộ thực đơn
    window.scrollMenuSlider = function(direction) {
        const slider = document.getElementById('menuSliderWrapper');
        if (slider) {
            const card = slider.querySelector('.dish-card');
            if (card) {
                const cardWidth = card.offsetWidth + 20; // card + gap
                slider.scrollBy({
                    left: direction * cardWidth * 1.5,
                    behavior: 'smooth'
                });
            }
        }
    };

    window.openFullMenuModal = function() {
        const modal = document.getElementById('fullMenuModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.querySelector('.lightbox-content').style.transform = 'scale(1)';
            }, 10);
        }
    };

    window.closeFullMenuModal = function() {
        const modal = document.getElementById('fullMenuModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('.lightbox-content').style.transform = 'scale(0.9)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    };

    let currentFilterTab = 'all';

    window.filterModalTab = function(tab) {
        currentFilterTab = tab;
        
        // Update active class on tab buttons
        document.querySelectorAll('.modal-tab-btn').forEach(btn => {
            if (btn.getAttribute('data-tab') === tab) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        filterModalDishes();
    };

    window.filterModalDishes = function() {
        const query = document.getElementById('dishSearchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('#modalMenuGrid .dish-card');
        let visibleCount = 0;
        const itemUnit = "{{ $itemUnit }}";
        
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const desc = card.getAttribute('data-desc');
            const isSignature = card.getAttribute('data-signature') === 'true';
            const price = parseFloat(card.getAttribute('data-price') || '0');
            
            let matchesTab = false;
            if (currentFilterTab === 'all') {
                matchesTab = true;
            } else if (currentFilterTab === 'signature') {
                matchesTab = isSignature;
            } else if (currentFilterTab === 'best-price') {
                matchesTab = price <= 200000;
            }
            
            const matchesQuery = name.includes(query) || desc.includes(query);
            
            if (matchesTab && matchesQuery) {
                card.style.display = 'flex';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 10);
                visibleCount++;
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    if (card.style.opacity === '0') {
                        card.style.display = 'none';
                    }
                }, 250);
            }
        });
        
        const countSpan = document.getElementById('modalVisibleCount');
        if (countSpan) {
            countSpan.textContent = visibleCount + ' ' + itemUnit;
        }
    };

    // 9. Điều khiển Modal Xem Toàn Bộ Đánh giá & Phân loại sao
    window.openAllReviewsModal = function() {
        const modal = document.getElementById('allReviewsModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.querySelector('.lightbox-content').style.transform = 'scale(1)';
            }, 10);
            document.body.style.overflow = 'hidden'; // Khóa cuộn trang nền
        }
    };

    window.closeAllReviewsModal = function() {
        const modal = document.getElementById('allReviewsModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('.lightbox-content').style.transform = 'scale(0.9)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
            document.body.style.overflow = ''; // Khôi phục cuộn trang nền
        }
    };

    window.filterReviewStars = function(star) {
        // Cập nhật trạng thái Tab hoạt động
        document.querySelectorAll('.review-modal-tab').forEach(tab => {
            const tabStar = tab.getAttribute('data-star');
            if (tabStar === star.toString()) {
                tab.classList.add('active');
                tab.style.border = '1.5px solid rgba(255, 126, 41, 0.2)';
                tab.style.background = 'rgba(255, 126, 41, 0.06)';
                tab.style.color = 'var(--primary)';
                tab.style.fontWeight = '700';
            } else {
                tab.classList.remove('active');
                tab.style.border = '1.5px solid var(--border-glow)';
                tab.style.background = 'transparent';
                tab.style.color = 'var(--text-muted)';
                tab.style.fontWeight = '600';
            }
        });

        // Lọc danh sách thẻ đánh giá trong Modal
        const cards = document.querySelectorAll('#modalReviewListScroll .modal-review-card-item');
        let matchedCount = 0;

        cards.forEach(card => {
            const cardRating = card.getAttribute('data-rating');
            
            if (star === 'all' || cardRating === star.toString()) {
                card.style.display = 'flex';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 10);
                matchedCount++;
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
                card.style.display = 'none';
            }
        });

        // Hiển thị giao diện Trạng thái Rỗng nếu không có nhận xét phù hợp
        const emptyState = document.getElementById('modalReviewsEmptyState');
        if (matchedCount === 0) {
            emptyState.style.display = 'flex';
        } else {
            emptyState.style.display = 'none';
        }
    };

    // Product Heritage Modal JS Logic
    let pmSynth = window.speechSynthesis;
    let pmUtterance = null;
    let pmIsSpeaking = false;

    window.switchPmTab = function(btn, tabId) {
        btn.parentNode.querySelectorAll('.heritage-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const parent = btn.closest('.heritage-tabs-container');
        parent.querySelectorAll('.heritage-tab-content').forEach(c => {
            c.style.display = 'none';
            c.classList.remove('active-content');
        });

        const activeContent = document.getElementById(tabId);
        if (activeContent) {
            activeContent.style.display = 'block';
            activeContent.classList.add('active-content');
        }
    };

    window.openProductHeritageModal = function(element) {
        const product = JSON.parse(element.getAttribute('data-product'));
        
        document.getElementById('pmName').textContent = product.name;
        document.getElementById('pmHeritageYear').textContent = product.heritage_year || 'Đặc sản truyền thống Đông Anh';
        
        // OCOP stars
        const starsContainer = document.getElementById('pmOcopStarsContainer');
        const starsDiv = document.getElementById('pmOcopStars');
        starsDiv.innerHTML = '';
        if (product.star_rating) {
            const starsMatch = product.star_rating.match(/\d+/);
            const starsCount = starsMatch ? parseInt(starsMatch[0]) : 0;
            if (starsCount > 0) {
                for (let i = 1; i <= 5; i++) {
                    const starSpan = document.createElement('span');
                    starSpan.textContent = '★';
                    if (i <= starsCount) {
                        starSpan.style.cssText = 'color: #ffb300; text-shadow: 0 0 10px rgba(255, 179, 0, 0.5);';
                    } else {
                        starSpan.style.color = 'var(--border-glow)';
                    }
                    starsDiv.appendChild(starSpan);
                }
                starsContainer.style.display = 'block';
            } else {
                starsContainer.style.display = 'none';
            }
        } else {
            starsContainer.style.display = 'none';
        }

        // Story
        document.getElementById('pmStoryText').textContent = product.story || product.description || 'Chưa cập nhật lịch sử hình thành.';
        
        // Artisans
        const artisansTab = document.getElementById('pmTabArtisansBtn');
        if (product.artisans) {
            document.getElementById('pmArtisansText').textContent = product.artisans;
            artisansTab.style.display = 'inline-block';
        } else {
            artisansTab.style.display = 'none';
        }

        // Ingredients
        const ingredientsTab = document.getElementById('pmTabIngredientsBtn');
        const ingredientsGrid = document.getElementById('pmIngredientsGrid');
        ingredientsGrid.innerHTML = '';
        
        let ingredientsArray = [];
        if (Array.isArray(product.ingredients)) {
            ingredientsArray = product.ingredients;
        } else if (typeof product.ingredients === 'string') {
            try { ingredientsArray = JSON.parse(product.ingredients) || []; } catch(e) {}
        }
        
        if (ingredientsArray && ingredientsArray.length > 0) {
            ingredientsArray.forEach(ing => {
                const div = document.createElement('div');
                div.className = 'glass-panel';
                div.style.cssText = 'padding: 12px 16px; background: rgba(212, 175, 55, 0.03); border: 1px solid rgba(212, 175, 55, 0.15); display: flex; align-items: center; gap: 10px; border-radius: 8px;';
                div.innerHTML = `<span style="font-size: 1.1rem;">✨</span><span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">${ing}</span>`;
                ingredientsGrid.appendChild(div);
            });
            ingredientsTab.style.display = 'inline-block';
        } else {
            ingredientsTab.style.display = 'none';
        }

        // Timeline
        const timelineTab = document.getElementById('pmTabTimelineBtn');
        const timelineContainer = document.getElementById('pmTimelineContainer');
        timelineContainer.innerHTML = '';
        
        let timelineArray = [];
        if (Array.isArray(product.timeline)) {
            timelineArray = product.timeline;
        } else if (typeof product.timeline === 'string') {
            try { timelineArray = JSON.parse(product.timeline) || []; } catch(e) {}
        }
        
        if (timelineArray && timelineArray.length > 0) {
            timelineArray.forEach(t => {
                const div = document.createElement('div');
                div.className = 'heritage-timeline-item';
                div.style.display = 'flex';
                div.style.marginBottom = '20px';
                div.innerHTML = `
                    <div class="heritage-timeline-badge" style="background: var(--primary); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; height: fit-content;">${t.year || 'Mốc'}</div>
                    <div class="heritage-timeline-content glass-panel" style="margin-left: 15px; padding: 12px 16px; flex: 1; border: 1px solid var(--border-glow); border-radius: 8px; background: rgba(255,255,255,0.01);">
                        <p style="font-size: 0.88rem; margin: 0; line-height: 1.5; color: var(--text-main);">${t.event}</p>
                    </div>
                `;
                timelineContainer.appendChild(div);
            });
            timelineTab.style.display = 'inline-block';
        } else {
            timelineTab.style.display = 'none';
        }

        // Trivia Fun Fact
        const triviaWidget = document.getElementById('pmTriviaWidget');
        if (product.fun_fact) {
            document.getElementById('pmTriviaText').textContent = product.fun_fact;
            triviaWidget.style.display = 'flex';
        } else {
            triviaWidget.style.display = 'none';
        }

        // Speech Audio Narrator setup
        const audioWidget = document.getElementById('pmAudioWidget');
        const playBtn = document.getElementById('pmPlayAudioBtn');
        const playBtnIcon = document.getElementById('pmPlayBtnIcon');
        const statusText = document.getElementById('pmAudioStatusText');
        const eq = document.getElementById('pmAudioEqualizer');
        
        if (pmIsSpeaking) {
            pmSynth.cancel();
            pmIsSpeaking = false;
        }
        
        playBtnIcon.textContent = "🔊";
        statusText.textContent = "Bấm để lắng nghe giọng đọc AI giới thiệu đặc sản";
        eq.classList.remove("playing-audio");

        if (product.audio_narrative) {
            audioWidget.style.display = 'flex';
            
            // Clean up existing listener if any
            const newPlayBtn = playBtn.cloneNode(true);
            playBtn.parentNode.replaceChild(newPlayBtn, playBtn);
            
            newPlayBtn.addEventListener('click', function() {
                if (!pmSynth) {
                    alert("Trình duyệt của bạn không hỗ trợ giọng đọc AI.");
                    return;
                }
                
                if (pmIsSpeaking) {
                    pmSynth.cancel();
                    pmIsSpeaking = false;
                    newPlayBtn.classList.remove("playing");
                    eq.classList.remove("playing-audio");
                    newPlayBtn.querySelector('.play-icon').textContent = "🔊";
                    statusText.textContent = "Bấm để lắng nghe giọng đọc AI giới thiệu đặc sản";
                } else {
                    pmSynth.cancel();
                    pmIsSpeaking = true;
                    newPlayBtn.classList.add("playing");
                    eq.classList.add("playing-audio");
                    newPlayBtn.querySelector('.play-icon').textContent = "⏸️";
                    statusText.textContent = "AI đang thuyết minh...";
                    
                    pmUtterance = new SpeechSynthesisUtterance(product.audio_narrative);
                    pmUtterance.lang = "vi-VN";
                    pmUtterance.rate = 0.92;
                    
                    const voices = pmSynth.getVoices();
                    const viVoice = voices.find(v => v.lang.includes("VI") || v.lang.includes("vi"));
                    if (viVoice) pmUtterance.voice = viVoice;
                    
                    pmUtterance.onend = function() {
                        pmIsSpeaking = false;
                        newPlayBtn.classList.remove("playing");
                        eq.classList.remove("playing-audio");
                        newPlayBtn.querySelector('.play-icon').textContent = "🔊";
                        statusText.textContent = "Thuyết minh hoàn thành. Bấm để nghe lại!";
                    };
                    
                    pmUtterance.onerror = function() {
                        pmIsSpeaking = false;
                        newPlayBtn.classList.remove("playing");
                        eq.classList.remove("playing-audio");
                        newPlayBtn.querySelector('.play-icon').textContent = "🔊";
                        statusText.textContent = "Đã xảy ra lỗi khi phát thuyết minh.";
                    };
                    
                    pmSynth.speak(pmUtterance);
                }
            });
        } else {
            audioWidget.style.display = 'none';
        }

        // Show tab story by default
        const storyBtn = document.getElementById('pmTabStoryBtn');
        switchPmTab(storyBtn, 'pm-tab-story');

        const modal = document.getElementById('productHeritageModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.querySelector('.lightbox-content').style.transform = 'scale(1)';
        }, 10);
        document.body.style.overflow = 'hidden';
    };

    window.closeProductHeritageModal = function() {
        const modal = document.getElementById('productHeritageModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('.lightbox-content').style.transform = 'scale(0.9)';
            
            if (pmSynth && pmIsSpeaking) {
                pmSynth.cancel();
                pmIsSpeaking = false;
            }
            
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        document.body.style.overflow = '';
    };

    let flatAudioSynth = window.speechSynthesis;
    let currentFlatUtterance = null;
    let currentFlatBtn = null;

    window.toggleFlatAudio = function(btn) {
        if (!flatAudioSynth) {
            alert("Trình duyệt của bạn không hỗ trợ giọng đọc AI.");
            return;
        }

        const narrative = btn.getAttribute('data-narrative');
        const statusText = btn.parentElement.querySelector('.flat-audio-status-text');
        const equalizer = btn.closest('.audio-storyteller-widget').querySelector('.flat-audio-equalizer');
        const playIcon = btn.querySelector('.play-icon');
        const dots = btn.closest('.audio-storyteller-widget').querySelector('.dots-placeholder');

        if (flatAudioSynth.speaking) {
            flatAudioSynth.cancel();
            
            if (currentFlatBtn) {
                const prevStatus = currentFlatBtn.parentElement.querySelector('.flat-audio-status-text');
                const prevEqualizer = currentFlatBtn.closest('.audio-storyteller-widget').querySelector('.flat-audio-equalizer');
                const prevPlayIcon = currentFlatBtn.querySelector('.play-icon');
                const prevDots = currentFlatBtn.closest('.audio-storyteller-widget').querySelector('.dots-placeholder');
                if (prevStatus) prevStatus.textContent = 'Bấm để lắng nghe giọng đọc AI thuyết minh văn hóa món ăn';
                if (prevEqualizer) prevEqualizer.style.display = 'none';
                if (prevDots) prevDots.style.display = 'block';
                if (prevPlayIcon) prevPlayIcon.textContent = '🔊';
            }

            if (currentFlatBtn === btn) {
                currentFlatBtn = null;
                return;
            }
        }

        currentFlatBtn = btn;
        currentFlatUtterance = new SpeechSynthesisUtterance(narrative);
        currentFlatUtterance.lang = 'vi-VN';
        currentFlatUtterance.rate = 0.92;

        const voices = flatAudioSynth.getVoices();
        const viVoice = voices.find(v => v.lang.includes("VI") || v.lang.includes("vi"));
        if (viVoice) currentFlatUtterance.voice = viVoice;
        
        currentFlatUtterance.onstart = function() {
            statusText.textContent = 'AI đang thuyết minh câu chuyện di sản...';
            equalizer.style.display = 'flex';
            if (dots) dots.style.display = 'none';
            playIcon.textContent = '⏸️';
        };

        currentFlatUtterance.onend = function() {
            statusText.textContent = 'Bấm để lắng nghe giọng đọc AI thuyết minh văn hóa món ăn';
            equalizer.style.display = 'none';
            if (dots) dots.style.display = 'block';
            playIcon.textContent = '🔊';
            currentFlatBtn = null;
        };

        currentFlatUtterance.onerror = function() {
            statusText.textContent = 'Có lỗi xảy ra khi phát âm thanh.';
            equalizer.style.display = 'none';
            if (dots) dots.style.display = 'block';
            playIcon.textContent = '🔊';
            currentFlatBtn = null;
        };

        flatAudioSynth.speak(currentFlatUtterance);
    };

    window.switchFlatTab = function(btn, panelId) {
        const container = btn.closest('.glass-panel');
        
        // Deactivate all tab buttons in this container
        container.querySelectorAll('.flat-heritage-tab-btn').forEach(b => {
            b.classList.remove('active');
        });
        
        // Hide all tab panels in this container
        container.querySelectorAll('.flat-tab-panel').forEach(p => {
            p.style.display = 'none';
        });
        
        // Activate clicked button
        btn.classList.add('active');
        
        // Show active panel
        const panel = document.getElementById(panelId);
        if (panel) {
            panel.style.display = 'block';
        }
    };

    // Tự động gọi tính khoảng cách ngay khi trang vừa tải xong
    window.addEventListener('load', getUserDistance);
</script>

<!-- Product Heritage Dossier Modal -->
<div id="productHeritageModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(16px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <div class="lightbox-content" style="background: var(--bg-card); border: 1.5px solid var(--primary); width: 92%; max-width: 800px; max-height: 90vh; border-radius: 24px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6); overflow: hidden; transform: scale(0.9); transition: transform 0.3s ease; display: flex; flex-direction: column; position: relative;">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed rgba(212, 175, 55, 0.25); padding: 20px 24px; background: rgba(255,255,255,0.01); z-index: 10;">
            <h3 style="margin: 0; font-size: 1.3rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                🏺 Hồ Sơ Di Sản Đặc Sản: <span id="pmName" style="color: var(--primary);"></span>
            </h3>
            <button onclick="closeProductHeritageModal()" style="background: transparent; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">✕</button>
        </div>
        
        <!-- Modal Content (Scrollable) -->
        <div class="heritage-museum-card" style="overflow-y: auto; padding: 24px; flex: 1; position: relative; border-radius: 0; border: none; box-shadow: none; background: transparent; margin-bottom: 0;">
            <!-- Decorative overlay -->
            <div class="heritage-pattern-overlay" style="opacity: 0.15;"></div>

            <div style="position: relative; z-index: 2; display: flex; flex-direction: column; gap: 24px;">
                
                <!-- Subheader: Heritage Year & OCOP Stars -->
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 16px;">
                    <div>
                        <p style="font-style: italic; color: var(--primary); font-size: 0.95rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 6px;">
                            🌾 <span id="pmHeritageYear"></span>
                        </p>
                    </div>
                    <div class="ocop-star-badge" id="pmOcopStarsContainer" style="flex-shrink: 0; display: none;">
                        <span style="font-weight: 900; font-size: 0.65rem; color: var(--primary); display: block; letter-spacing: 1px;">CHỨNG NHẬN OCOP</span>
                        <div id="pmOcopStars" style="color: #ffc107; font-size: 1.1rem; margin-top: 2px; display: flex; gap: 2px; justify-content: flex-end;">
                        </div>
                    </div>
                </div>

                <!-- AI Speech Voice widget -->
                <div id="pmAudioWidget" class="audio-storyteller-widget glass-panel" style="background: rgba(212, 175, 55, 0.04); border: 1px solid rgba(212, 175, 55, 0.2); padding: 14px 18px; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; max-width: 100%; box-sizing: border-box;">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                        <button id="pmPlayAudioBtn" class="audio-play-btn" aria-label="Play narrative audio" title="Nghe kể câu chuyện di sản" style="outline: none;">
                            <span class="play-icon" id="pmPlayBtnIcon">🔊</span>
                        </button>
                        <div>
                            <strong style="color: #ffb300; display: block; font-size: 0.95rem;">🎧 Nghe thuyết minh di sản đặc sản</strong>
                            <span style="font-size: 0.8rem; color: var(--text-muted);" id="pmAudioStatusText">Bấm để lắng nghe giọng đọc AI giới thiệu đặc sản</span>
                        </div>
                    </div>
                    <!-- Equalizer Visualizer -->
                    <div class="equalizer-container" id="pmAudioEqualizer">
                        <div class="eq-bar"></div>
                        <div class="eq-bar"></div>
                        <div class="eq-bar"></div>
                        <div class="eq-bar"></div>
                        <div class="eq-bar"></div>
                        <div class="eq-bar"></div>
                    </div>
                </div>

                <!-- Heritage Tabs Container -->
                <div class="heritage-tabs-container">
                    <div class="heritage-tab-buttons" style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <button class="heritage-tab-btn active" id="pmTabStoryBtn" onclick="switchPmTab(this, 'pm-tab-story')">🏛️ Câu Chuyện</button>
                        <button class="heritage-tab-btn" id="pmTabArtisansBtn" onclick="switchPmTab(this, 'pm-tab-artisans')">👨‍🍳 Nghệ Nhân</button>
                        <button class="heritage-tab-btn" id="pmTabIngredientsBtn" onclick="switchPmTab(this, 'pm-tab-ingredients')">🌾 Thành Phần</button>
                        <button class="heritage-tab-btn" id="pmTabTimelineBtn" onclick="switchPmTab(this, 'pm-tab-timeline')">📜 Hành Trình</button>
                    </div>

                    <!-- Tab Story -->
                    <div id="pm-tab-story" class="heritage-tab-content active-content" style="margin-top: 20px;">
                        <p id="pmStoryText" style="font-size: 1rem; line-height: 1.7; color: var(--text-main); margin: 0;"></p>
                    </div>

                    <!-- Tab Artisans -->
                    <div id="pm-tab-artisans" class="heritage-tab-content" style="margin-top: 20px; display: none;">
                        <h4 style="color: var(--primary); font-size: 1.05rem; margin-bottom: 12px; font-weight: 700;">Nghệ Nhân Gìn Giữ & Truyền Nghề</h4>
                        <p id="pmArtisansText" style="font-size: 1rem; line-height: 1.7; color: var(--text-main); font-style: italic; background: var(--bg-btn-secondary); padding: 18px; border-radius: 12px; border: 1px dashed var(--border-glow-hover); margin: 0;"></p>
                    </div>

                    <!-- Tab Ingredients -->
                    <div id="pm-tab-ingredients" class="heritage-tab-content" style="margin-top: 20px; display: none;">
                        <h4 style="color: var(--primary); font-size: 1.05rem; margin-bottom: 14px; font-weight: 700;">Nguyên Liệu & Bí Quyết Truyền Thống</h4>
                        <div id="pmIngredientsGrid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;"></div>
                    </div>

                    <!-- Tab Timeline -->
                    <div id="pm-tab-timeline" class="heritage-tab-content" style="margin-top: 20px; display: none;">
                        <div class="heritage-timeline" id="pmTimelineContainer"></div>
                    </div>
                </div>

                <!-- Trivia Widget -->
                <div id="pmTriviaWidget" class="trivia-widget glass-panel" style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.08) 0%, rgba(255, 111, 0, 0.03) 100%); border: 1px solid rgba(212, 175, 55, 0.2); padding: 20px; border-radius: 14px; display: flex; gap: 14px; align-items: flex-start;">
                    <span style="font-size: 2rem; filter: drop-shadow(0 0 10px rgba(255,179,0,0.6));">💡</span>
                    <div>
                        <h4 style="font-size: 1rem; color: var(--primary); font-weight: 700; margin-bottom: 4px;">BẠN CÓ BIẾT?</h4>
                        <p id="pmTriviaText" style="font-size: 0.9rem; line-height: 1.6; color: var(--text-main); margin: 0;"></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@if($eatery->phone)
<!-- Floating Contact Button on Mobile -->
<a href="tel:{{ $eatery->phone }}" class="mobile-call-btn" title="Gọi điện thoại liên hệ" aria-label="Gọi điện thoại liên hệ">
    <svg viewBox="0 0 24 24">
        <path d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z" />
    </svg>
</a>
@endif
@endsection
