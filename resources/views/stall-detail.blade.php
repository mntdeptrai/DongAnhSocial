@extends('layouts.app')

@section('title', $stallName . ' — ' . $eatery->name . ' | DongAnh Map')
@section('meta_description', 'Xem chi tiết gian hàng ' . $stallName . ' tại ' . $eatery->name . ', Đông Anh — thông tin tiểu thương, sản phẩm niêm yết, bản đồ vị trí và đánh giá từ khách hàng.')
@section('og_image', $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80')
@section('canonical_url', route('market.stall.show', ['marketSlug' => $marketSlug, 'stallSlug' => $stallSlug]))

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* ====================== STALL DETAIL PAGE DESIGN SYSTEM (Sky Lagoon Theme) ====================== */
    :root {
        --stall-primary: var(--primary, #0ea5e9);
        --stall-primary-grad: linear-gradient(135deg, #0ea5e9, #06b6d4);
        --stall-primary-soft: rgba(14, 165, 233, 0.08);
        --stall-gold: #F59E0B;
        --stall-green: #10B981;
        --stall-green-soft: rgba(16, 185, 129, 0.1);
        --stall-radius: 16px;
        --stall-radius-sm: 10px;
    }

    body {
        font-family: 'Be Vietnam Pro', sans-serif !important;
        background: var(--bg-base) !important;
        color: var(--text-main) !important;
    }

    a { text-decoration: none !important; }

    .stall-page-wrapper {
        max-width: 1300px;
        margin: 0 auto;
        padding: 24px 20px 80px;
    }

    /* ---- Breadcrumb ---- */
    .stall-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .stall-breadcrumb a {
        color: var(--primary);
        font-weight: 600;
        transition: opacity 0.2s;
    }
    .stall-breadcrumb a:hover { opacity: 0.75; }

    /* ---- Hero Header ---- */
    .stall-hero {
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 24px;
        display: flex;
        gap: 24px;
        align-items: flex-start;
        position: relative;
        overflow: hidden;
    }
    .stall-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #0ea5e9, #06b6d4, #10b981);
        border-radius: 20px 20px 0 0;
    }
    .stall-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.25);
    }
    .stall-hero-info { flex: 1; }
    .stall-hero-name {
        font-size: 1.6rem;
        font-weight: 900;
        color: var(--text-main);
        margin: 0 0 6px;
        line-height: 1.2;
    }
    .stall-hero-market {
        font-size: 0.88rem;
        color: var(--primary);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
    }
    .stall-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    .stall-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        border: 1px solid;
    }
    .badge-green { background: var(--stall-green-soft); color: var(--stall-green); border-color: rgba(16,185,129,0.25); }
    .badge-sky { background: rgba(14, 165, 233, 0.08); color: var(--primary); border-color: rgba(14, 165, 233, 0.25); }
    .badge-gold { background: rgba(245,158,11,0.1); color: #B45309; border-color: rgba(245,158,11,0.25); }
    .badge-blue { background: rgba(59,130,246,0.08); color: #2563EB; border-color: rgba(59,130,246,0.2); }

    .stall-avg-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
    }
    .stall-stars-large {
        color: var(--stall-gold);
        font-size: 1.15rem;
        letter-spacing: 1px;
    }
    .stall-rating-num {
        font-size: 1.4rem;
        font-weight: 900;
        color: var(--text-main);
    }
    .stall-rating-count {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    /* ---- Main 2-column grid ---- */
    .stall-main-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }
    @media (max-width: 900px) {
        .stall-main-grid { grid-template-columns: 1fr; }
        .stall-hero { flex-direction: column; }
        .stall-hero-name { font-size: 1.3rem; }
    }

    /* ---- Panels ---- */
    .stall-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: var(--stall-radius);
        overflow: hidden;
    }
    .stall-panel-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-glow);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .stall-panel-header h3 {
        font-size: 0.95rem;
        font-weight: 800;
        margin: 0;
        color: var(--text-main);
    }
    .panel-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .panel-body { padding: 20px; }

    /* ---- Info rows ---- */
    .info-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-glow);
        font-size: 0.88rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(14, 165, 233, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 0.85rem;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .info-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: block;
        margin-bottom: 2px;
    }
    .info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* ---- Products List ---- */
    .product-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        margin: 0 -16px;
        border-radius: 14px;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        border-bottom: 1px solid var(--border-glow);
        position: relative;
    }
    .product-card:last-child { border-bottom: none; }
    .product-card:hover {
        background: rgba(14, 165, 233, 0.04);
        box-shadow: 0 4px 18px rgba(14, 165, 233, 0.08);
        border-bottom-color: transparent;
    }
    .product-img {
        width: 68px;
        height: 68px;
        border-radius: 14px;
        object-fit: cover;
        flex-shrink: 0;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    .product-img-placeholder {
        width: 68px;
        height: 68px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
    }
    .product-name {
        font-weight: 800;
        font-size: 0.93rem;
        color: var(--text-main);
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .product-origin {
        font-size: 0.73rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .product-price-chip {
        display: inline-flex;
        align-items: baseline;
        gap: 2px;
        background: rgba(14, 165, 233, 0.08);
        border: 1px solid rgba(14, 165, 233, 0.2);
        border-radius: 8px;
        padding: 3px 10px;
        margin-top: 6px;
    }
    .product-price-num {
        font-weight: 900;
        font-size: 0.95rem;
        color: var(--primary);
    }
    .product-price-unit {
        font-size: 0.68rem;
        color: var(--text-muted);
    }
    .btn-order {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        box-shadow: 0 3px 10px rgba(14, 165, 233, 0.28);
        flex-shrink: 0;
    }
    .btn-order:hover {
        transform: translateY(-2px) scale(1.04);
        box-shadow: 0 6px 18px rgba(14, 165, 233, 0.4);
    }
    .btn-order:active { transform: scale(0.96); }
    .btn-order:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* ---- Leaflet Map Panel ---- */
    #stallMap {
        height: 320px;
        width: 100%;
        border-radius: 0 0 var(--stall-radius) var(--stall-radius);
    }
    .map-direction-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: rgba(14, 165, 233, 0.08);
        color: var(--primary);
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
        width: 100%;
        text-align: left;
    }
    .map-direction-btn:hover { background: rgba(14, 165, 233, 0.15); }

    /* ---- QR Code Panel ---- */
    .qr-panel-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }
    .qr-box {
        background: #fff;
        border-radius: 14px;
        padding: 16px;
        border: 2px solid rgba(14, 165, 233, 0.15);
    }
    .qr-box img { width: 150px; height: 150px; display: block; }

    /* ---- Action Buttons ---- */
    .stall-action-btns {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-stall-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        color: #fff;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.88rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none !important;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.25);
    }
    .btn-stall-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.35);
        color: #fff !important;
    }
    .btn-stall-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.88rem;
        border: 1px solid var(--border-glow);
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none !important;
    }
    .btn-stall-secondary:hover {
        border-color: var(--primary);
        color: var(--primary) !important;
        transform: translateY(-2px);
    }

    /* ---- Reviews Section ---- */
    .reviews-section {
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: var(--stall-radius);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .review-form-area {
        padding: 24px;
        background: rgba(14, 165, 233, 0.04);
        border-bottom: 1px solid var(--border-glow);
    }
    .review-form-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 14px;
    }
    .star-input-row {
        display: flex;
        gap: 8px;
        margin-bottom: 14px;
    }
    .star-btn {
        font-size: 1.6rem;
        cursor: pointer;
        color: #D1D5DB;
        transition: all 0.15s;
        border: none;
        background: none;
        padding: 0;
        line-height: 1;
    }
    .star-btn.active, .star-btn:hover { color: var(--stall-gold); transform: scale(1.15); }
    .review-textarea {
        width: 100%;
        border-radius: 12px;
        border: 1px solid var(--border-glow);
        background: var(--bg-base);
        color: var(--text-main);
        padding: 12px 16px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: 0.88rem;
        resize: none;
        outline: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }
    .review-textarea:focus { border-color: var(--primary); }

    .review-list { padding: 8px 0; }
    .review-item {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border-glow);
        transition: background 0.2s;
    }
    .review-item:last-child { border-bottom: none; }
    .review-item:hover { background: rgba(14, 165, 233, 0.02); }
    .review-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }
    .reviewer-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 800;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    .reviewer-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-main);
    }
    .reviewer-time {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .reviewer-stars {
        margin-left: auto;
        color: var(--stall-gold);
        font-size: 0.85rem;
        letter-spacing: 1px;
    }
    .review-comment {
        font-size: 0.88rem;
        color: var(--text-main);
        line-height: 1.6;
        padding-left: 50px;
    }

    .no-reviews-state {
        text-align: center;
        padding: 40px 24px;
        color: var(--text-muted);
    }
    .no-reviews-state .icon { font-size: 3rem; margin-bottom: 12px; }
    .no-reviews-state p { font-size: 0.88rem; font-weight: 500; }

    /* ---- Flash success ---- */
    .flash-success {
        background: var(--stall-green-soft);
        border: 1px solid rgba(16,185,129,0.3);
        color: var(--stall-green);
        border-radius: 12px;
        padding: 12px 18px;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
</style>

<div class="stall-page-wrapper">

    {{-- Breadcrumb --}}
    <nav class="stall-breadcrumb">
        <a href="{{ route('home') }}"><i class="bi bi-house-fill"></i> Trang chủ</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('eatery.show', $eatery->slug) }}">{{ $eatery->name }}</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ $stallName }}</span>
    </nav>

    {{-- Flash messages --}}
    @if(session('review_success'))
        <div class="flash-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('review_success') }}
        </div>
    @endif

    {{-- ========================= HERO HEADER ========================= --}}
    <div class="stall-hero">
        <div class="stall-avatar">
            @php
                $emojiMap = [
                    'Ăn uống' => '🍜', 'Rau củ' => '🥦', 'Thịt tươi' => '🥩',
                    'Thực phẩm khô' => '🌾', 'Khác' => '🏪'
                ];
                $stallEmoji = $emojiMap[$category] ?? '🏪';
            @endphp
            {{ $stallEmoji }}
        </div>

        <div class="stall-hero-info">
            <h1 class="stall-hero-name">{{ $stallName }}</h1>

            <div class="stall-hero-market">
                <i class="bi bi-shop"></i>
                <a href="{{ route('eatery.show', $eatery->slug) }}" style="color: var(--primary);">
                    {{ $eatery->name }}
                </a>
                <span style="color: var(--text-muted); font-weight: 400;">· Đông Anh, Hà Nội</span>
            </div>

            <div class="stall-badges">
                <span class="stall-badge badge-sky">
                    <i class="bi bi-tag-fill"></i> {{ $category }}
                </span>
                @if($hasQr)
                    <span class="stall-badge badge-green">
                        <i class="bi bi-qr-code-scan"></i> VietQR
                    </span>
                @endif
                @if($hasSmartphone)
                    <span class="stall-badge badge-blue">
                        <i class="bi bi-phone-fill"></i> Smartphone
                    </span>
                @endif
                @if($reviews->count() > 0)
                    <span class="stall-badge badge-gold">
                        <i class="bi bi-star-fill"></i> {{ $avgRating }}/5 · {{ $reviews->count() }} đánh giá
                    </span>
                @endif
            </div>

            @if($reviews->count() > 0)
                <div class="stall-avg-rating">
                    <span class="stall-stars-large">
                        @for($i = 1; $i <= 5; $i++)
                            {{ $i <= round($avgRating) ? '★' : '☆' }}
                        @endfor
                    </span>
                    <span class="stall-rating-num">{{ $avgRating }}</span>
                    <span class="stall-rating-count">/ 5 · {{ $reviews->count() }} lượt đánh giá</span>
                </div>
            @endif
        </div>

        {{-- Action buttons --}}
        <div style="display: flex; flex-direction: column; gap: 10px; flex-shrink: 0;">
            @if($sellerPhone)
                <a href="tel:{{ $sellerPhone }}" class="btn-stall-primary">
                    <i class="bi bi-telephone-fill"></i> Gọi ngay
                </a>
                <a href="https://zalo.me/{{ $sellerPhone }}" target="_blank" class="btn-stall-secondary">
                    <i class="bi bi-chat-dots-fill"></i> Zalo
                </a>
            @endif
            <button onclick="scrollToReviews()" class="btn-stall-secondary" style="border-color: var(--stall-gold); color: #B45309;">
                <i class="bi bi-star-fill"></i> Đánh giá
            </button>
        </div>
    </div>

    {{-- ========================= MAIN 2-COLUMN GRID ========================= --}}
    <div class="stall-main-grid">

        {{-- ---- LEFT: Info + Products ---- --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">

            {{-- Thông tin tiểu thương --}}
            <div class="stall-panel">
                <div class="stall-panel-header">
                    <div class="panel-icon" style="background: rgba(14, 165, 233, 0.08); color: var(--primary);">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h3>Thông tin tiểu thương</h3>
                </div>
                <div class="panel-body">
                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-person-fill"></i></div>
                        <div>
                            <span class="info-label">Chủ hộ kinh doanh</span>
                            <span class="info-value">{{ $sellerName ?: 'Chưa cập nhật' }}</span>
                        </div>
                    </div>
                    @if($sellerPhone)
                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <span class="info-label">Số điện thoại</span>
                            <span class="info-value">
                                <a href="tel:{{ $sellerPhone }}" style="color: var(--primary);">{{ $sellerPhone }}</a>
                            </span>
                        </div>
                    </div>
                    @endif
                    @if($bankInfo)
                    <div class="info-row">
                        <div class="info-icon" style="background: rgba(99,102,241,0.1); color: #6366F1;"><i class="bi bi-bank2"></i></div>
                        <div>
                            <span class="info-label">Số tài khoản (STK)</span>
                            <span class="info-value" style="font-family: monospace; letter-spacing: 0.5px;">{{ $bankInfo }}</span>
                        </div>
                    </div>
                    @endif
                    @if($originText && $originText !== 'Tự sản xuất')
                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-geo-fill"></i></div>
                        <div>
                            <span class="info-label">Nguồn gốc xuất xứ</span>
                            <span class="info-value">{{ $originText }}</span>
                        </div>
                    </div>
                    @endif

                    {{-- Chia sẻ gian hàng --}}
                    <div style="margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--border-glow);">
                        <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-share-fill" style="color: var(--primary);"></i> Chia sẻ gian hàng
                        </div>
                        <div style="display: flex; gap: 8px; align-items: stretch; margin-bottom: 8px;">
                            <input type="text" id="shareUrlInput" value="{{ url()->current() }}"
                                   style="flex: 1; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-glow); background: var(--bg-base); color: var(--text-main); font-size: 0.8rem; outline: none; min-width: 0;"
                                   readonly>
                            <button onclick="copyShareUrl()" id="copyBtn"
                                    style="padding: 8px 14px; border-radius: 8px; background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.25); color: var(--primary); font-weight: 700; font-size: 0.8rem; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                                <i class="bi bi-clipboard"></i> Sao chép
                            </button>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                               style="flex:1; text-align:center; padding: 8px; border-radius: 8px; background: rgba(24,119,242,0.08); color: #1877F2; font-weight: 700; font-size: 0.78rem; border: 1px solid rgba(24,119,242,0.15);">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                            <a href="https://zalo.me/share/url?url={{ urlencode(url()->current()) }}&title={{ urlencode($stallName) }}" target="_blank"
                               style="flex:1; text-align:center; padding: 8px; border-radius: 8px; background: rgba(0,107,255,0.08); color: #006BFF; font-weight: 700; font-size: 0.78rem; border: 1px solid rgba(0,107,255,0.15);">
                                <i class="bi bi-chat-fill"></i> Zalo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danh sách sản phẩm --}}
            <div class="stall-panel">
                <div class="stall-panel-header">
                    <div class="panel-icon" style="background: rgba(14, 165, 233, 0.08); color: var(--primary);">
                        <i class="bi bi-basket2-fill"></i>
                    </div>
                    <h3>Sản phẩm niêm yết ({{ $stallProducts->count() }})</h3>
                    <span style="margin-left: auto; font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">Giá niêm yết</span>
                </div>
                <div class="panel-body">
                    @forelse($stallProducts as $product)
                        @php
                            $prodOrigin = 'Tự sản xuất';
                            if ($product->description && preg_match('/Nguồn gốc[:\s]+(.*?)[\.\n]/u', $product->description, $pm)) {
                                $prodOrigin = trim($pm[1]);
                            }
                            $prodEmoji = '🛒';
                            if (str_contains($product->name, 'Bún') || str_contains($product->name, 'Phở')) $prodEmoji = '🍜';
                            elseif (str_contains($product->name, 'Rau')) $prodEmoji = '🥦';
                            elseif (str_contains($product->name, 'Thịt') || str_contains($product->name, 'Giò')) $prodEmoji = '🥩';
                        @endphp
                        <div class="product-card">
                            {{-- Thumbnail --}}
                            @if($product->image_path)
                                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="product-img">
                            @else
                                <div class="product-img-placeholder">{{ $prodEmoji }}</div>
                            @endif

                            {{-- Info --}}
                            <div style="flex: 1; min-width: 0;">
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="product-origin">
                                    <i class="bi bi-geo-alt-fill" style="color: var(--primary);"></i>
                                    {{ $prodOrigin }} &middot; Cam kết tươi sạch
                                </div>
                                <div class="product-price-chip">
                                    <span class="product-price-num">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                    @php
                                        $displayUnit = $product->unit ?: 'kg';
                                    @endphp
                                    <span class="product-price-unit">&nbsp;/ {{ $displayUnit }}</span>
                                </div>
                            </div>

                            {{-- Order button --}}
                            <button class="btn-order add-to-cart-btn"
                                    data-id="{{ $product->id }}"
                                    data-type="ocop_product"
                                    onclick="addToCart(event, this); animateFlyToCart(this);">
                                <i class="bi bi-cart-plus-fill"></i> Đặt hàng
                            </button>
                        </div>
                    @empty
                        <div style="text-align:center; padding: 32px 0; color: var(--text-muted);">
                            <div style="font-size: 2.5rem; margin-bottom: 10px;">📦</div>
                            <p style="font-size: 0.9rem; font-weight: 600;">Chưa có sản phẩm được niêm yết.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ---- RIGHT: Map + QR ---- --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">

            {{-- Bản đồ Leaflet --}}
            <div class="stall-panel">
                <div class="stall-panel-header">
                    <div class="panel-icon" style="background: var(--stall-green-soft); color: var(--stall-green);">
                        <i class="bi bi-map-fill"></i>
                    </div>
                    <h3>Vị trí gian hàng</h3>
                </div>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $lat }},{{ $lng }}" target="_blank" class="map-direction-btn">
                    <i class="bi bi-navigation-fill"></i>
                    Chỉ đường tới gian hàng · Google Maps
                    <i class="bi bi-box-arrow-up-right" style="margin-left: auto;"></i>
                </a>
                <div id="stallMap"></div>
            </div>

            {{-- QR Thanh toán --}}
            @if($bankName && $bankAcct)
            <div class="stall-panel">
                <div class="stall-panel-header">
                    <div class="panel-icon" style="background: rgba(99,102,241,0.1); color: #6366F1;">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h3>Thanh toán VietQR</h3>
                </div>
                <div class="qr-panel-body">
                    <div class="qr-box">
                        @php
                            $qrDesc = urlencode('Thanh toan cho ' . $stallName);
                            $qrUrl  = "https://img.vietqr.io/image/{$bankName}-{$bankAcct}-compact2.png?accountName=" . urlencode($sellerName) . "&addInfo={$qrDesc}";
                        @endphp
                        <img src="{{ $qrUrl }}" alt="VietQR {{ $bankName }}" style="width:160px;height:auto;display:block;">
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                            Số tài khoản
                        </div>
                        <div style="font-family: monospace; font-weight: 900; font-size: 1.05rem; color: var(--text-main); letter-spacing: 1px; margin-bottom: 2px;">
                            {{ $bankAcct }}
                        </div>
                        <div style="font-size: 0.78rem; font-weight: 700; color: #6366F1; margin-bottom: 6px;">
                            {{ $bankName }} Bank
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            Quét mã QR để chuyển khoản · 100% bảo mật
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- ========================= REVIEWS SECTION (full width) ========================= --}}
    <div class="reviews-section" id="reviewsAnchor">

        {{-- Review Form --}}
        <div class="review-form-area">
            <div class="review-form-title">
                <i class="bi bi-star-fill" style="color: var(--stall-gold);"></i>
                Viết đánh giá cho gian hàng
            </div>
            <form method="POST" action="{{ route('market.stall.review.store', ['marketSlug' => $marketSlug, 'stallSlug' => $stallSlug]) }}">
                @csrf

                {{-- Star selector --}}
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 8px;">Đánh giá của bạn</div>
                    <div class="star-input-row" id="starRow">
                        @for($s = 1; $s <= 5; $s++)
                            <button type="button" class="star-btn" data-val="{{ $s }}" onclick="setStar({{ $s }})">★</button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="5">
                </div>

                @if(!Auth::check())
                    <div style="margin-bottom: 12px;">
                        <input type="text" name="user_name" placeholder="Tên của bạn (tùy chọn)"
                               style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-glow); background: var(--bg-base); color: var(--text-main); font-family: 'Be Vietnam Pro', sans-serif; font-size: 0.88rem; outline: none; box-sizing: border-box;"
                               maxlength="50">
                    </div>
                @endif

                <textarea name="comment" class="review-textarea" rows="4"
                          placeholder="Chia sẻ trải nghiệm mua sắm của bạn tại gian hàng này..." required minlength="5" maxlength="500"></textarea>

                @error('comment')
                    <div style="color: #EF4444; font-size: 0.8rem; margin-top: 6px;">{{ $message }}</div>
                @enderror

                <div style="display: flex; justify-content: flex-end; margin-top: 14px;">
                    <button type="submit" class="btn-stall-primary">
                        <i class="bi bi-send-fill"></i> Gửi đánh giá
                    </button>
                </div>
            </form>
        </div>

        {{-- Reviews Header --}}
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-glow); display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-main);">
                <i class="bi bi-chat-square-quote-fill" style="color: var(--primary);"></i>
                Đánh giá từ khách hàng
            </div>
            @if($reviews->count() > 0)
                <span style="background: rgba(14, 165, 233, 0.08); color: var(--primary); font-size: 0.78rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(14, 165, 233, 0.2);">
                    {{ $reviews->count() }} nhận xét
                </span>
                <span style="margin-left: auto; color: var(--stall-gold); font-weight: 800; font-size: 0.95rem;">
                    ★ {{ $avgRating }}/5
                </span>
            @endif
        </div>

        {{-- Reviews List --}}
        <div class="review-list">
            @forelse($reviews as $rev)
                <div class="review-item">
                    <div class="review-top">
                        <div class="reviewer-avatar">
                            {{ mb_strtoupper(mb_substr($rev->user_name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="reviewer-name">{{ $rev->user_name }}</div>
                            <div class="reviewer-time">
                                <i class="bi bi-clock"></i>
                                {{ $rev->created_at ? $rev->created_at->diffForHumans() : 'Vừa xong' }}
                            </div>
                        </div>
                        <div class="reviewer-stars">
                            {{ str_repeat('★', $rev->rating) }}{{ str_repeat('☆', 5 - $rev->rating) }}
                        </div>
                    </div>
                    <div class="review-comment">{{ $rev->comment }}</div>
                </div>
            @empty
                <div class="no-reviews-state">
                    <div class="icon">💬</div>
                    <p>Chưa có đánh giá nào. Hãy là người đầu tiên nhận xét!</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Back link --}}
    <div style="text-align: center; padding: 12px 0;">
        <a href="{{ route('eatery.show', $eatery->slug) }}" class="btn-stall-secondary" style="display: inline-flex;">
            <i class="bi bi-arrow-left-circle-fill"></i> Quay lại {{ $eatery->name }}
        </a>
    </div>

</div>

{{-- ========================= LEAFLET INIT ========================= --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WPaU=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ---- Star picker ----
    const stars  = document.querySelectorAll('.star-btn');
    let selected = 5;

    function setStar(val) {
        selected = val;
        document.getElementById('ratingInput').value = val;
        stars.forEach((s, idx) => {
            s.classList.toggle('active', idx < val);
        });
    }

    window.setStar = setStar;
    setStar(5); // default 5 stars

    stars.forEach((btn, idx) => {
        btn.addEventListener('mouseenter', () => {
            stars.forEach((s, i) => s.classList.toggle('active', i <= idx));
        });
        btn.addEventListener('mouseleave', () => setStar(selected));
    });

    // ---- Leaflet Map ----
    const lat = {{ $lat ?? 21.1571 }};
    const lng = {{ $lng ?? 105.8448 }};

    const map = L.map('stallMap', { zoomControl: true, scrollWheelZoom: false }).setView([lat, lng], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    // Custom Cyan / Sky Blue marker pin (matching DongAnh Discovery brand system)
    const cyanIcon = L.divIcon({
        html: `<div style="width:42px;height:42px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(14,165,233,0.45);">
                 <span style="transform:rotate(45deg);font-size:1.2rem;">{{ $stallEmoji }}</span>
               </div>`,
        className: '',
        iconSize: [42, 42],
        iconAnchor: [21, 42],
        popupAnchor: [0, -44]
    });

    L.marker([lat, lng], { icon: cyanIcon })
     .addTo(map)
     .bindPopup(`
        <div style="font-family:'Be Vietnam Pro',sans-serif;min-width:180px;">
            <strong style="font-size:0.95rem;color:#0ea5e9;">{{ $stallName }}</strong><br>
            <span style="font-size:0.8rem;color:#666;">{{ $eatery->name }}</span>
            @if($sellerPhone)
            <br><a href="tel:{{ $sellerPhone }}" style="font-size:0.8rem;color:#0ea5e9;">📞 {{ $sellerPhone }}</a>
            @endif
        </div>
     `, { maxWidth: 240 })
     .openPopup();

    // ---- Copy URL ----
    window.copyShareUrl = function() {
        const input = document.getElementById('shareUrlInput');
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = document.getElementById('copyBtn');
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Đã sao chép!';
            btn.style.background = 'rgba(16,185,129,0.1)';
            btn.style.color = '#10B981';
            btn.style.borderColor = 'rgba(16,185,129,0.25)';
            setTimeout(() => {
                btn.innerHTML = '<i class="bi bi-clipboard"></i> Sao chép';
                btn.style.background = '';
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2500);
        });
    };

    // ---- Scroll to reviews ----
    window.scrollToReviews = function() {
        document.getElementById('reviewsAnchor')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
});
</script>

@endsection
