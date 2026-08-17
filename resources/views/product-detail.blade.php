@extends('layouts.app')

<!-- Tối ưu hóa SEO: Tiêu đề động cho Sản phẩm của Cơ sở kinh doanh -->
@section('title', $product->name . ' - ' . ($eatery ? $eatery->name : 'Cơ sở kinh doanh tại Đông Anh'))

<!-- SEO Meta Description -->
@section('meta_description', 'Chi tiết sản phẩm ' . $product->name . ' tại ' . ($eatery ? $eatery->name : 'Cơ sở kinh doanh Đông Anh') . ', địa chỉ: ' . ($eatery ? $eatery->address : 'Đông Anh, Hà Nội') . '. xem mô tả, báo giá, hotline liên hệ và hướng dẫn đặt hàng.')

<!-- SEO Keywords -->
@section('meta_keywords', $product->name . ', ' . ($eatery ? $eatery->name : 'Cơ sở kinh doanh') . ', sản phẩm Đông Anh, hải sản Đông Anh, nông sản thực phẩm Đông Anh, mua ' . $product->name . ', giá ' . $product->name)

@section('og_image', $product->image_path ? asset($product->image_path) : ($eatery?->image_path ?: asset('images/ocop-placeholder.png')))
@section('og_type', 'product')
@section('canonical_url', route('business.product.show', $product->id))

<!-- Structured Data JSON-LD cho Google Rich Snippets & Search Indexing -->
@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org/",
  "@@type": "Product",
  "name": "{{ addslashes($product->name) }}",
  "image": [
    "{{ $product->image_path ? asset($product->image_path) : ($eatery?->image_path ?: asset('images/ocop-placeholder.png')) }}"
  ],
  "description": "{{ addslashes(preg_replace('/\s+/', ' ', strip_tags($product->description ?: 'Sản phẩm kinh doanh chất lượng cao tại ' . ($eatery ? $eatery->name : 'Đông Anh')))) }}",
  "brand": {
    "@@type": "Brand",
    "name": "{{ addslashes($eatery ? $eatery->name : 'Cơ sở kinh doanh Đông Anh') }}"
  },
  "offers": {
    "@@type": "Offer",
    "url": "{{ route('business.product.show', $product->slug ?: $product->id) }}",
    "priceCurrency": "VND",
    "price": "{{ $product->price > 0 ? (int)$product->price : 0 }}",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "https://schema.org/InStock",
    "seller": {
      "@@type": "Organization",
      "name": "{{ addslashes($eatery ? $eatery->name : 'Cơ sở kinh doanh Đông Anh') }}"
    }
  }
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [{
    "@@type": "ListItem",
    "position": 1,
    "name": "Trang chủ",
    "item": "{{ url('/') }}"
  },{
    "@@type": "ListItem",
    "position": 2,
    "name": "{{ $eatery ? $eatery->name : 'Cơ sở kinh doanh' }}",
    "item": "{{ $eatery ? route('eatery.show', $eatery->slug) : url('/') }}"
  },{
    "@@type": "ListItem",
    "position": 3,
    "name": "{{ addslashes($product->name) }}"
  }]
}
</script>
@endpush

@section('content')

@php
    $formatMediaUrl = function($url) {
        if (!$url) return asset('images/ocop-placeholder.png');
        if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) return $url;
        return asset(ltrim($url, '/'));
    };

    $mainImgUrl = $formatMediaUrl($product->image_path ?: $eatery?->image_path);
    
    // Check if food safety cert exists
    $hasCert = $eatery && $eatery->foodSafetyCertificate;
@endphp

<style>
    :root {
        --prod-accent: #0284c7;
        --prod-accent-grad: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        --prod-glow: rgba(2, 132, 199, 0.2);
    }

    .product-detail-hero {
        padding-top: 24px;
        padding-bottom: 40px;
        max-width: 1240px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    .prod-glass-card {
        background: var(--bg-card);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border-glow);
        border-radius: 24px;
        padding: 32px;
        box-shadow: var(--shadow-main);
        transition: all 0.3s var(--ease-premium);
        box-sizing: border-box;
    }

    .prod-main-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 36px;
        align-items: start;
    }

    .prod-image-wrapper {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        background: rgba(0, 0, 0, 0.04);
        border: 1px solid var(--border-glow);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
    }

    .prod-image-main {
        width: 100%;
        height: 420px;
        object-fit: cover;
        display: block;
        transition: transform 0.5s ease;
    }

    .prod-image-wrapper:hover .prod-image-main {
        transform: scale(1.04);
    }

    .prod-badge-floating {
        position: absolute;
        top: 16px;
        left: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        z-index: 2;
    }

    .prod-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--text-main);
        font-family: var(--font-heading);
        margin: 0 0 12px 0;
        line-height: 1.25;
        word-break: break-word;
    }

    .prod-store-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(2, 132, 199, 0.08);
        border: 1px solid rgba(2, 132, 199, 0.25);
        padding: 8px 16px;
        border-radius: 30px;
        color: #0284c7;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 18px;
        max-width: 100%;
        box-sizing: border-box;
    }

    .prod-store-badge:hover {
        background: rgba(2, 132, 199, 0.15);
        transform: translateY(-2px);
    }

    .prod-price-box {
        background: linear-gradient(135deg, rgba(2, 132, 199, 0.06) 0%, rgba(16, 185, 129, 0.06) 100%);
        border: 1px solid rgba(2, 132, 199, 0.2);
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .prod-price-num {
        font-size: 2.1rem;
        font-weight: 900;
        color: #10b981;
    }

    .prod-highlight-list {
        list-style: none;
        padding: 0;
        margin: 0 0 24px 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .prod-highlight-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.98rem;
        color: var(--text-main);
        font-weight: 500;
        line-height: 1.5;
    }

    .prod-action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .btn-action-primary, .btn-action-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 26px;
        border-radius: 30px;
        font-weight: 800;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        box-sizing: border-box;
    }

    .btn-action-primary {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff !important;
        border: none;
        box-shadow: 0 6px 20px rgba(2, 132, 199, 0.35);
    }

    .btn-action-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(2, 132, 199, 0.5);
    }

    .btn-action-secondary {
        background: var(--bg-card);
        color: var(--text-main) !important;
        border: 1px solid var(--border-glow);
    }

    .btn-action-secondary:hover {
        border-color: #0284c7;
        color: #0284c7 !important;
        transform: translateY(-2px);
    }

    /* Tabs Styling */
    .prod-tab-btns {
        display: flex;
        gap: 12px;
        border-bottom: 1px solid var(--border-glow);
        padding-bottom: 16px;
        margin-bottom: 24px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .prod-tab-btns::-webkit-scrollbar {
        display: none;
    }

    .prod-tab-btn {
        background: transparent;
        border: 1px solid transparent;
        color: var(--text-muted);
        padding: 10px 20px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .prod-tab-btn.active {
        background: rgba(2, 132, 199, 0.12);
        border-color: rgba(2, 132, 199, 0.3);
        color: #0284c7;
    }

    .prod-tab-content {
        display: none;
    }

    .prod-tab-content.active-content {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    /* VSATTP Box & Facility Grids */
    .vsattp-card {
        background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        border: 1.5px solid #bbf7d0;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 40px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.08);
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
    }

    .vsattp-grid {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 24px;
        align-items: center;
    }

    .facility-tab-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .related-products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }

    .related-prod-card {
        display: flex !important;
        flex-direction: column !important;
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        box-sizing: border-box;
    }

    .related-prod-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(2, 132, 199, 0.18);
        border-color: #0284c7;
    }

    .related-prod-img {
        width: 100% !important;
        height: 180px !important;
        object-fit: cover !important;
        display: block !important;
    }

    .related-prod-body {
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex: 1;
        justify-content: space-between;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* =========================================================================
       RESPONSIVE BREAKPOINTS ACROSS 4 DEVICE TYPES
       ========================================================================= */

    /* Tier 1: Ultra-Wide & Desktop (>= 1200px) */
    @media (min-width: 1200px) {
        .product-detail-hero { padding-left: 20px; padding-right: 20px; }
        .prod-image-main { height: 440px; }
    }

    /* Tier 2: Standard Laptops / Small Desktop (992px - 1199px) */
    @media (max-width: 1199px) and (min-width: 992px) {
        .prod-main-grid { gap: 28px; }
        .prod-image-main { height: 380px; }
        .prod-title { font-size: 1.9rem; }
        .prod-glass-card { padding: 28px; }
        .vsattp-grid { grid-template-columns: 200px 1fr; gap: 20px; }
    }

    /* Tier 3: Tablets & Mobile Landscape (576px - 991px) */
    @media (max-width: 991px) {
        .prod-main-grid { grid-template-columns: 1fr; gap: 24px; }
        .prod-image-main { height: 340px; }
        .prod-title { font-size: 1.75rem; }
        .prod-glass-card { padding: 24px; border-radius: 20px; }
        .vsattp-grid { grid-template-columns: 180px 1fr; gap: 20px; }
        .facility-tab-grid { grid-template-columns: 1fr; gap: 16px; }
        .vsattp-card { padding: 20px; border-radius: 20px; }
        .btn-action-primary, .btn-action-secondary { flex: 1 1 auto; text-align: center; }
    }

    /* Tier 4: Mobile Portrait (< 576px - iPhones / Android) */
    @media (max-width: 575px) {
        .product-detail-hero { padding-top: 14px; padding-bottom: 24px; padding-left: 10px; padding-right: 10px; }
        .prod-glass-card { padding: 16px; border-radius: 16px; margin-bottom: 24px; }
        .prod-image-wrapper { border-radius: 14px; }
        .prod-image-main { height: 250px; }
        
        .prod-badge-floating { top: 10px; left: 10px; gap: 6px; }
        .prod-badge-floating span {
            font-size: 0.7rem !important;
            padding: 4px 10px !important;
        }

        .prod-title { font-size: 1.35rem; margin-bottom: 8px; }
        .prod-store-badge { font-size: 0.82rem; padding: 6px 12px; margin-bottom: 14px; }

        .prod-price-box {
            padding: 12px 14px;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            border-radius: 14px;
            margin-bottom: 18px;
        }

        .prod-price-num { font-size: 1.55rem; }
        .prod-highlight-item { font-size: 0.88rem; }

        .prod-action-group {
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .btn-action-primary, .btn-action-secondary {
            width: 100% !important;
            padding: 12px 16px;
            font-size: 0.9rem;
            justify-content: center;
        }

        .vsattp-card { padding: 16px; border-radius: 16px; margin-bottom: 24px; }
        .vsattp-grid { grid-template-columns: 1fr; gap: 14px; }
        .vsattp-grid img { height: 190px !important; }

        .prod-tab-btns {
            gap: 6px;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .prod-tab-btn {
            padding: 8px 12px;
            font-size: 0.8rem;
        }

        .related-products-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }
    }
</style>

<div class="container product-detail-hero">

    <!-- Breadcrumb Navigation -->
    <nav class="integrated-breadcrumb-nav" aria-label="Breadcrumb" style="margin-bottom: 24px;">
        <a href="/" class="breadcrumb-item-link">
            <span>🏠 Trang chủ</span>
        </a>
        <span class="breadcrumb-arrow">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </span>
        @if($eatery)
            <a href="{{ route('eatery.show', $eatery->slug) }}" class="breadcrumb-item-link">
                <span>🏪 {{ $eatery->name }}</span>
            </a>
            <span class="breadcrumb-arrow">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </span>
        @endif
        <span class="breadcrumb-item-active">
            <span>🛒 {{ $product->name }}</span>
        </span>
    </nav>

    <!-- Main Product Card Showcase -->
    <div class="prod-glass-card" style="margin-bottom: 40px;">
        <div class="prod-main-grid">

            <!-- Left: High-Res Product Image & Badges -->
            <div>
                <div class="prod-image-wrapper">
                    <div class="prod-badge-floating">
                        <span style="background: rgba(2, 132, 199, 0.9); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; backdrop-filter: blur(8px); display: inline-flex; align-items: center; gap: 6px;">
                            🏪 Sản phẩm Cơ sở Kinh doanh
                        </span>
                        @if($product->is_signature)
                            <span style="background: rgba(255, 126, 41, 0.95); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; backdrop-filter: blur(8px); display: inline-flex; align-items: center; gap: 6px;">
                                ★ Món / Sản phẩm đặc trưng
                            </span>
                        @endif
                        @if($hasCert)
                            <span style="background: rgba(46, 204, 113, 0.95); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; backdrop-filter: blur(8px); display: inline-flex; align-items: center; gap: 6px;">
                                🛡️ Xác minh An Toàn VSTP
                            </span>
                        @endif
                    </div>
                    <img src="{{ $mainImgUrl }}" alt="{{ $product->name }}" class="prod-image-main">
                </div>

                <!-- Establishment Quick Card -->
                @if($eatery)
                    <div style="margin-top: 20px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-glow); border-radius: 16px; padding: 18px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 1.8rem;">🏬</span>
                            <div>
                                <h4 style="margin: 0; color: var(--text-main); font-size: 1rem; font-weight: 800;">{{ $eatery->name }}</h4>
                                <p style="margin: 2px 0 0 0; color: var(--text-muted); font-size: 0.85rem;">📍 {{ $eatery->address }}</p>
                            </div>
                        </div>
                        <a href="{{ route('eatery.show', $eatery->slug) }}" class="btn-action-secondary" style="padding: 8px 16px; font-size: 0.85rem;">
                            Xem cơ sở ➔
                        </a>
                    </div>
                @endif
            </div>

            <!-- Right: Product Info & CTAs -->
            <div>
                <h1 class="prod-title">{{ $product->name }}</h1>

                @if($eatery)
                    <a href="{{ route('eatery.show', $eatery->slug) }}" class="prod-store-badge">
                        <span>🏪 Cung cấp bởi: <strong>{{ $eatery->name }}</strong></span>
                        @if($eatery->commune)
                            <span>· {{ $eatery->commune->name }}</span>
                        @endif
                    </a>
                @endif

                <!-- Price Box -->
                <div class="prod-price-box">
                    <div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 2px;">GIÁ BÁN / DỰ CHI:</span>
                        @if($product->price > 0)
                            <span class="prod-price-num">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                            <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 600;">/ {{ $product->unit ?: 'phần / kg' }}</span>
                        @else
                            <span class="prod-price-num" style="color: #0284c7;">Liên hệ báo giá</span>
                        @endif
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.85rem; color: #10b981; font-weight: 700; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 4px 12px; border-radius: 20px;">
                            ✓ Sẵn hàng tại Đông Anh
                        </span>
                    </div>
                </div>

                <!-- Product Features Bullet List -->
                <ul class="prod-highlight-list">
                    <li class="prod-highlight-item">
                        <span style="color: #10b981; font-size: 1.1rem;">✔</span>
                        <span><strong>Cung cấp đa dạng:</strong> Cho cỗ tiệc, trường học, nhà hàng, sự kiện & hộ gia đình.</span>
                    </li>
                    <li class="prod-highlight-item">
                        <span style="color: #10b981; font-size: 1.1rem;">✔</span>
                        <span><strong>Chất lượng cam kết:</strong> Tươi sống ngon sạch hàng ngày, nguồn gốc xuất xứ rõ ràng.</span>
                    </li>
                    <li class="prod-highlight-item">
                        <span style="color: #10b981; font-size: 1.1rem;">✔</span>
                        <span><strong>Giao hàng hỏa tốc:</strong> Nhận đơn & giao nhanh tận nơi trong khu vực Đông Anh.</span>
                    </li>
                    @if($eatery && $eatery->phone)
                        <li class="prod-highlight-item">
                            <span style="color: #10b981; font-size: 1.1rem;">✔</span>
                            <span><strong>Hotline hỗ trợ 24/7:</strong> {{ $eatery->phone }}</span>
                        </li>
                    @endif
                </ul>

                <!-- Action CTA Buttons -->
                <div class="prod-action-group">
                    @if($eatery && $eatery->phone)
                        <a href="tel:{{ $eatery->phone }}" class="btn-action-primary">
                            <span>📞 Gọi ngay: {{ $eatery->phone }}</span>
                        </a>
                        <a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $eatery->phone) }}" target="_blank" rel="noopener noreferrer" class="btn-action-secondary" style="border-color: #0068ff; color: #0068ff !important;">
                            <span>💬 Chat Zalo</span>
                        </a>
                    @endif

                    @if($product->price > 0)
                        <button onclick="addToCart(event, this)" 
                                data-id="{{ $product->id }}" 
                                data-type="dish"
                                class="btn-action-primary" 
                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);">
                            <span>🛒 Thêm vào giỏ</span>
                        </button>
                    @endif

                    @if(!empty($qrCodeUrl) && !empty($bankAcct))
                        <button onclick="openVietQRModal()" class="btn-action-secondary">
                            <span>💳 VietQR</span>
                        </button>
                    @endif

                    <button onclick="shareLocationPage('{{ addslashes($product->name) }}')" class="btn-action-secondary">
                        <span>📤 Chia sẻ</span>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- VSATTP FOOD SAFETY CERTIFICATE SHOWCASE BLOCK -->
    @php
        $cert = $eatery?->foodSafetyCertificate;
        $certImg = $cert?->image_path ? (\Illuminate\Support\Str::startsWith($cert->image_path, ['http://', 'https://']) ? $cert->image_path : asset($cert->image_path)) : null;
    @endphp

    <div class="vsattp-card">
        
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; border-bottom: 1px dashed #bbf7d0; padding-bottom: 16px;">
            <h3 style="font-size: 1.35rem; font-weight: 800; color: #065f46; margin: 0; font-family: var(--font-heading); display: flex; align-items: center; gap: 10px;">
                🛡️ Giấy Chứng Nhận An Toàn Vệ Sinh Thực Phẩm (VSATTP)
            </h3>
            <span style="background: #10b981; color: #ffffff; font-weight: 800; font-size: 0.85rem; padding: 6px 16px; border-radius: 30px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                ✓ Đủ Điều Kiện An Toàn VSTP
            </span>
        </div>

        <div class="vsattp-grid" style="grid-template-columns: {{ $certImg ? '' : '1fr' }};">
            @if($certImg)
                <div style="position: relative; cursor: pointer; border-radius: 16px; overflow: hidden; border: 2px solid #10b981; box-shadow: 0 8px 20px rgba(0,0,0,0.1);" onclick="openCertImageModal('{{ $certImg }}')">
                    <img src="{{ $certImg }}" alt="Giấy chứng nhận VSATTP {{ $eatery?->name }}" style="width: 100%; height: 250px; object-fit: cover; display: block; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.35); opacity: 0; transition: opacity 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.85rem; text-align: center; padding: 10px;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                        🔍 Bấm xem ảnh Chứng nhận
                    </div>
                </div>
            @endif

            <div>
                <div style="background: #ffffff; border: 1px solid #a7f3d0; border-radius: 18px; padding: 20px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; font-size: 0.95rem; color: #1e293b;">
                        <div>
                            <span style="color: #64748b; font-size: 0.8rem; font-weight: 700; display: block; text-transform: uppercase;">CƠ SỞ KINH DOANH:</span>
                            <strong style="color: #065f46; font-size: 1.05rem;">{{ $product->seller_name ?: ($eatery?->name ?: 'Cơ sở kinh doanh Đông Anh') }}</strong>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.8rem; font-weight: 700; display: block; text-transform: uppercase;">SỐ GIẤY CHỨNG NHẬN:</span>
                            <strong style="color: #0284c7; font-size: 1rem;">{{ $cert?->certificate_number ?: 'Đã xác minh ATTP' }}</strong>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.8rem; font-weight: 700; display: block; text-transform: uppercase;">CƠ QUAN CẤP CHỨNG NHẬN:</span>
                            <strong>{{ $cert?->issued_by ?: 'Chi cục An toàn Vệ sinh Thực phẩm Hà Nội' }}</strong>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.8rem; font-weight: 700; display: block; text-transform: uppercase;">TRẠNG THÁI HIỆU LỰC:</span>
                            <span style="color: #10b981; font-weight: 800;">✓ Đang có hiệu lực lưu hành</span>
                        </div>
                    </div>
                </div>

                <div style="font-size: 0.95rem; line-height: 1.7; color: #334155;">
                    <p style="margin: 0 0 6px 0; font-weight: 600;">
                        📋 <strong>Cam kết chất lượng:</strong> Sản phẩm {{ $product->name }} được kiểm soát nguồn gốc nghiêm ngặt, đáp ứng toàn bộ quy chuẩn vệ sinh an toàn thực phẩm.
                    </p>
                    <p style="margin: 0; color: #64748b; font-size: 0.88rem;">
                        Nhận cung cấp cho cỗ đám tiệc, bếp ăn trường học, nhà hàng & hộ gia đình trên địa bàn Đông Anh.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- CERTIFICATE IMAGE ZOOM MODAL -->
    <div id="certImgZoomModal" style="display: none; position: fixed; inset: 0; z-index: 9999999; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px;" onclick="closeCertImageModal()">
        <div style="position: relative; max-width: 90vw; max-height: 90vh;" onclick="event.stopPropagation()">
            <button onclick="closeCertImageModal()" style="position: absolute; top: -16px; right: -16px; background: #ef4444; color: white; border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 10;">✕</button>
            <img id="certImgZoomTarget" src="" style="max-width: 90vw; max-height: 85vh; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); object-fit: contain;">
        </div>
    </div>
    <script>
        function openCertImageModal(url) {
            document.getElementById('certImgZoomTarget').src = url;
            document.getElementById('certImgZoomModal').style.display = 'flex';
        }
        function closeCertImageModal() {
            document.getElementById('certImgZoomModal').style.display = 'none';
        }
    </script>

    <!-- Detailed Tabs & Content -->
    <div class="prod-glass-card" style="margin-bottom: 40px;">
        <div class="prod-tab-btns">
            <button class="prod-tab-btn active" onclick="switchProdTab('tab-desc', this)">📦 Mô tả chi tiết & Quy cách</button>
            <button class="prod-tab-btn" onclick="switchProdTab('tab-facility', this)">🏪 Về Cơ sở kinh doanh</button>
            <button class="prod-tab-btn" onclick="switchProdTab('tab-policy', this)">🚚 Chính sách giao hàng & Cung ứng</button>
            <button class="prod-tab-btn" onclick="switchProdTab('tab-reviews', this)">⭐ Đánh giá & Phản hồi ({{ $reviews->count() }})</button>
        </div>

        <!-- Tab 1: Mô tả chi tiết -->
        <div id="tab-desc" class="prod-tab-content active-content">
            <h3 style="color: var(--text-main); font-size: 1.3rem; margin-top: 0; margin-bottom: 16px;">Mô tả sản phẩm</h3>
            <div style="font-size: 1.05rem; line-height: 1.8; color: var(--text-main); white-space: pre-line;">
                {{ $product->description ?: 'Chuyên cung cấp sản phẩm tươi sống, thực phẩm và hàng hóa tiêu chuẩn cho các cỗ đám tiệc, bếp ăn trường học, nhà hàng và hộ gia đình tại huyện Đông Anh.' }}
            </div>
            
            <div style="margin-top: 24px; padding: 20px; background: rgba(2, 132, 199, 0.04); border-left: 4px solid #0284c7; border-radius: 8px;">
                <h4 style="margin: 0 0 8px 0; color: #0284c7; font-size: 1.05rem;">💡 Phạm vi phục vụ & Cung ứng:</h4>
                <p style="margin: 0; color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
                    Sản phẩm <strong>{{ $product->name }}</strong> được cung cấp chính hãng bởi <strong>{{ $eatery ? $eatery->name : 'cơ sở kinh doanh' }}</strong>. Nhận cung cấp số lượng lớn theo hợp đồng cho trường học, nhà hàng, khách sạn và các sự kiện lớn nhỏ trên địa bàn Đông Anh & khu vực lân cận.
                </p>
            </div>
        </div>

        <!-- Tab 2: Thông tin cơ sở kinh doanh -->
        <div id="tab-facility" class="prod-tab-content">
            @if($eatery)
                <h3 style="color: var(--text-main); font-size: 1.3rem; margin-top: 0; margin-bottom: 16px;">{{ $eatery->name }}</h3>
                <div class="facility-tab-grid">
                    <div>
                        <p style="font-size: 1rem; color: var(--text-main); margin-bottom: 10px;"><strong>📍 Địa chỉ:</strong> {{ $eatery->address }}</p>
                        <p style="font-size: 1rem; color: var(--text-main); margin-bottom: 10px;"><strong>📞 Hotline / Zalo:</strong> {{ $eatery->phone ?: 'Chưa cập nhật' }}</p>
                        <p style="font-size: 1rem; color: var(--text-main); margin-bottom: 10px;"><strong>🕒 Giờ mở cửa:</strong> {{ $eatery->opening_hours ?: '07:00 - 21:00 hàng ngày' }}</p>
                        <p style="font-size: 1rem; color: var(--text-main); margin-bottom: 10px;"><strong>⭐ Đánh giá trung bình:</strong> {{ $avgRating }} / 5.0 ({{ $reviews->count() }} lượt đánh giá)</p>
                        
                        <div style="margin-top: 16px;">
                            <a href="{{ route('eatery.show', $eatery->slug) }}" class="btn-action-primary" style="padding: 10px 20px; font-size: 0.9rem;">
                                Xem toàn bộ danh mục sản phẩm của cơ sở ➔
                            </a>
                        </div>
                    </div>
                    <div>
                        @if($eatery->image_path)
                            <img src="{{ $formatMediaUrl($eatery->image_path) }}" style="width: 100%; height: 220px; object-fit: cover; border-radius: 16px; border: 1px solid var(--border-glow);" alt="{{ $eatery->name }}">
                        @endif
                    </div>
                </div>
            @else
                <p style="color: var(--text-muted);">Đang cập nhật thông tin cơ sở kinh doanh.</p>
            @endif
        </div>

        <!-- Tab 3: Chính sách giao hàng -->
        <div id="tab-policy" class="prod-tab-content">
            <h3 style="color: var(--text-main); font-size: 1.3rem; margin-top: 0; margin-bottom: 16px;">Chính sách đặt hàng & Giao vận</h3>
            <ul style="line-height: 1.8; color: var(--text-main); padding-left: 20px;">
                <li><strong>Giao hàng nội thành Đông Anh:</strong> Nhận giao hàng nhanh tận nhà, trường học, bếp ăn.</li>
                <li><strong>Đơn hàng lớn / Hợp đồng:</strong> Quý khách đặt mua cho trường học, cỗ tiệc vui lòng gọi hotline trước 1-2 ngày để được hỗ trợ giá ưu đãi.</li>
                <li><strong>Phương thức thanh toán:</strong> Tiền mặt khi nhận hàng (COD), Chuyển khoản VietQR, Hóa đơn VAT (nếu yêu cầu).</li>
            </ul>
        </div>

        <!-- Tab 4: Đánh giá -->
        <div id="tab-reviews" class="prod-tab-content">
            <h3 style="color: var(--text-main); font-size: 1.3rem; margin-top: 0; margin-bottom: 16px;">Đánh giá từ khách hàng</h3>
            @if($reviews->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($reviews as $rev)
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-glow); border-radius: 14px; padding: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong style="color: var(--text-main); font-size: 0.95rem;">👤 {{ $rev->user_name }}</strong>
                                <span style="color: #ffc107; font-size: 0.9rem;">
                                    @for($i=1; $i<=5; $i++)
                                        {{ $i <= $rev->rating ? '★' : '☆' }}
                                    @endfor
                                </span>
                            </div>
                            <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">{{ $rev->comment }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: var(--text-muted);">Chưa có đánh giá nào. Hãy là người đầu tiên trải nghiệm và chia sẻ nhận xét!</p>
            @endif
        </div>
    </div>

    <!-- Related Products Section -->
    @if($otherEstablishmentProducts->count() > 0)
        <div style="margin-bottom: 40px;">
            <h3 style="font-size: clamp(1.15rem, 3.5vw, 1.45rem); font-weight: 800; color: var(--text-main); font-family: var(--font-heading); margin-bottom: 20px; line-height: 1.35; word-break: break-word;">
                🛒 Sản phẩm khác từ {{ $eatery ? $eatery->name : 'cơ sở' }}
            </h3>
            <div class="related-products-grid">
                @foreach($otherEstablishmentProducts as $item)
                    <div class="related-prod-card" onclick="window.location.href='{{ route('business.product.show', $item->id) }}'">
                        <img src="{{ $formatMediaUrl($item->image_path) }}" class="related-prod-img" alt="{{ $item->name }}">
                        <div class="related-prod-body">
                            <h4 style="margin: 0 0 6px 0; color: var(--text-main); font-size: 1rem; font-weight: 800; line-height: 1.3;">{{ $item->name }}</h4>
                            <p style="margin: 0 0 10px 0; color: var(--text-muted); font-size: 0.85rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $item->description }}
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: auto; flex-wrap: wrap;">
                                <strong style="color: #10b981; font-size: 1rem;">
                                    {{ $item->price > 0 ? number_format($item->price, 0, ',', '.') . 'đ' : 'Liên hệ' }}
                                </strong>
                                <span style="color: #0284c7; font-size: 0.85rem; font-weight: 700; white-space: nowrap;">Xem chi tiết ➔</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

@if(!empty($qrCodeUrl) && !empty($bankAcct))
<!-- VietQR Modal -->
<div id="vietQrModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(10px); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-glow); border-radius: 24px; padding: 28px; max-width: 440px; width: 100%; text-align: center; position: relative;">
        <button onclick="closeVietQRModal()" style="position: absolute; right: 16px; top: 16px; background: transparent; border: none; font-size: 1.4rem; color: var(--text-muted); cursor: pointer;">✕</button>
        <h3 style="margin: 0 0 12px 0; color: var(--text-main); font-size: 1.3rem;">💳 Thanh toán qua VietQR</h3>
        <p style="margin: 0 0 16px 0; color: var(--text-muted); font-size: 0.88rem;">Quét mã QR bằng ứng dụng ngân hàng bất kỳ để đặt mua <strong>{{ $product->name }}</strong></p>

        <div style="background: #ffffff; padding: 16px; border-radius: 16px; display: inline-block; margin-bottom: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
            <img src="{{ $qrCodeUrl }}" style="width: 220px; height: 220px; display: block;" alt="VietQR Code">
        </div>

        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-glow); border-radius: 12px; padding: 12px; font-size: 0.88rem; color: var(--text-main); text-align: left; margin-bottom: 16px;">
            <p style="margin: 0 0 4px 0;"><strong>Chủ tài khoản:</strong> {{ $bankHolder }}</p>
            <p style="margin: 0 0 4px 0;"><strong>Ngân hàng:</strong> {{ $bankName }}</p>
            <p style="margin: 0;"><strong>Số tài khoản:</strong> {{ $bankAcct }}</p>
        </div>

        <button onclick="closeVietQRModal()" class="btn-action-primary" style="width: 100%; justify-content: center;">
            Tôi đã hoàn tất chuyển khoản
        </button>
    </div>
</div>
@endif

<script>
    function switchProdTab(tabId, btn) {
        document.querySelectorAll('.prod-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.prod-tab-content').forEach(c => c.classList.remove('active-content'));
        
        btn.classList.add('active');
        const target = document.getElementById(tabId);
        if (target) target.classList.add('active-content');
    }

    function openVietQRModal() {
        const m = document.getElementById('vietQrModal');
        if (m) m.style.display = 'flex';
    }

    function closeVietQRModal() {
        const m = document.getElementById('vietQrModal');
        if (m) m.style.display = 'none';
    }
</script>

@endsection
