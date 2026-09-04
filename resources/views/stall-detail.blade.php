@extends('layouts.app')

@section('title', 'Gian Hàng ' . $stallName . ' — ' . $eatery->name . ' | Chợ Số Đông Anh')
@section('meta_description', 'Xem chi tiết gian hàng ' . $stallName . ' tại ' . $eatery->name . ', Đông Anh — thông tin tiểu thương ' . ($sellerName ?: '') . ', danh mục mặt hàng niêm yết, bản đồ vị trí, thanh toán VietQR và đánh giá khách hàng.')
@section('meta_keywords', \App\Helpers\VietnameseSeoHelper::generateStallKeywords($stallName, $eatery->name, $category ?? null, $eatery->commune?->name ?? 'Đông Anh'))
@section('og_image', $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80')
@section('og_type', 'business.business')
@section('canonical_url', route('market.stall.show', ['marketSlug' => $marketSlug, 'stallSlug' => $stallSlug]))

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Store",
  "name": "{{ addslashes($stallName) }}",
  "description": "Gian hàng {{ addslashes($stallName) }} tại {{ addslashes($eatery->name) }}, Đông Anh, Hà Nội",
  "image": "{{ $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80' }}",
  "telephone": "{{ $sellerPhone ?: 'Chưa cập nhật' }}",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "{{ addslashes($eatery->address ?: 'Đông Anh') }}",
    "addressLocality": "{{ addslashes($eatery->commune?->name ?: 'Đông Anh') }}",
    "addressRegion": "Hà Nội",
    "addressCountry": "VN"
  },
  "geo": {
    "@@type": "GeoCoordinates",
    "latitude": {{ (float)($lat ?? 21.1571) }},
    "longitude": {{ (float)($lng ?? 105.8448) }}
  },
  "url": "{{ route('market.stall.show', ['marketSlug' => $marketSlug, 'stallSlug' => $stallSlug]) }}"
  @if(isset($avgRating) && $avgRating > 0)
  ,
  "aggregateRating": {
    "@@type": "AggregateRating",
    "ratingValue": "{{ $avgRating }}",
    "reviewCount": "{{ isset($reviews) && $reviews->count() > 0 ? $reviews->count() : 5 }}",
    "bestRating": "5",
    "worstRating": "1"
  }
  @endif
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
    "name": "{{ addslashes($eatery->name) }}",
    "item": "{{ route('eatery.show', $eatery->slug) }}"
  },{
    "@@type": "ListItem",
    "position": 3,
    "name": "Gian hàng {{ addslashes($stallName) }}"
  }]
}
</script>
@endpush

@section('content')
@php
    $isCultureMarket = str_contains(strtolower($eatery->slug ?? ''), 'van-hoa-du-lich') 
        || str_contains(strtolower($eatery->name ?? ''), 'văn hóa du lịch');
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* ====================== STALL DETAIL DYNAMIC DESIGN SYSTEM ====================== */
    :root {
        --stall-primary: var(--primary, #0ea5e9);
        --stall-primary-grad: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        --stall-primary-soft: rgba(14, 165, 233, 0.08);
        --stall-gold: #F59E0B;
        --stall-green: #10B981;
        --stall-green-soft: rgba(16, 185, 129, 0.1);
        --stall-radius: 20px;
        --stall-radius-sm: 12px;
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
        animation: fadeInScale 0.4s ease-out forwards;
    }

    /* ---- Animations ---- */
    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        100% { background-position: 200% 50%; }
    }
    @keyframes floatAvatar {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-5px) scale(1.03); }
    }
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 8px 24px rgba(14, 165, 233, 0.25); }
        50% { box-shadow: 0 14px 36px rgba(14, 165, 233, 0.45); }
    }
    @keyframes fadeInScale {
        from { opacity: 0; transform: translateY(12px) scale(0.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ---- Breadcrumb ---- */
    .stall-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.84rem;
        color: var(--text-muted);
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .stall-breadcrumb a {
        color: var(--primary);
        font-weight: 600;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .stall-breadcrumb a:hover {
        color: #0284c7;
        transform: translateX(1px);
    }

    /* ---- Hero Header ---- */
    .stall-hero {
        background: linear-gradient(135deg, rgba(14,165,233,0.06) 0%, rgba(6,182,212,0.02) 50%, rgba(16,185,129,0.04) 100%), var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: 24px;
        padding: 30px 34px;
        margin-bottom: 28px;
        display: flex;
        gap: 28px;
        align-items: flex-start;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        backdrop-filter: blur(16px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stall-hero:hover {
        box-shadow: 0 16px 40px rgba(14, 165, 233, 0.1);
    }
    .stall-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: linear-gradient(90deg, #0ea5e9, #06b6d4, #10b981, #f59e0b, #0ea5e9);
        background-size: 200% 100%;
        animation: gradientMove 6s linear infinite;
        border-radius: 24px 24px 0 0;
    }
    .stall-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        flex-shrink: 0;
        animation: pulseGlow 4s ease-in-out infinite, floatAvatar 5s ease-in-out infinite;
        border: 3px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 10px 30px rgba(14, 165, 233, 0.3);
    }
    .stall-hero-info { flex: 1; }
    .stall-hero-name {
        font-size: 1.75rem;
        font-weight: 900;
        color: var(--text-main);
        margin: 0 0 6px;
        line-height: 1.2;
        letter-spacing: -0.3px;
        background: linear-gradient(135deg, var(--text-main) 30%, var(--primary) 100%);
        -webkit-background-clip: text;
    }
    .stall-hero-market {
        font-size: 0.9rem;
        color: var(--primary);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 14px;
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
        gap: 6px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        border: 1px solid;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stall-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .badge-green { background: var(--stall-green-soft); color: var(--stall-green); border-color: rgba(16,185,129,0.3); }
    .badge-sky { background: rgba(14, 165, 233, 0.08); color: var(--primary); border-color: rgba(14, 165, 233, 0.3); }
    .badge-gold { background: rgba(245,158,11,0.12); color: #B45309; border-color: rgba(245,158,11,0.3); }
    .badge-blue { background: rgba(59,130,246,0.08); color: #2563EB; border-color: rgba(59,130,246,0.25); }

    .stall-avg-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
    }
    .stall-stars-large {
        color: var(--stall-gold);
        font-size: 1.25rem;
        letter-spacing: 1px;
        filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.3));
    }
    .stall-rating-num {
        font-size: 1.45rem;
        font-weight: 900;
        color: var(--text-main);
    }
    .stall-rating-count {
        font-size: 0.82rem;
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
        .stall-hero { flex-direction: column; padding: 22px; }
        .stall-hero-name { font-size: 1.4rem; }
    }

    /* ---- Panels ---- */
    .stall-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: var(--stall-radius);
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
    }
    .stall-panel:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(14, 165, 233, 0.08), 0 4px 12px rgba(0, 0, 0, 0.03);
        border-color: rgba(14, 165, 233, 0.3);
    }
    .stall-panel-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border-glow);
        display: flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(90deg, rgba(14, 165, 233, 0.03) 0%, transparent 100%);
    }
    .stall-panel-header h3 {
        font-size: 0.98rem;
        font-weight: 800;
        margin: 0;
        color: var(--text-main);
        letter-spacing: -0.2px;
    }
    .panel-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    .panel-body { padding: 22px; }

    /* ---- Info rows ---- */
    .info-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 12px 14px;
        margin: 0 -14px;
        border-radius: 12px;
        border-bottom: 1px solid var(--border-glow);
        font-size: 0.88rem;
        transition: background 0.2s ease, transform 0.2s ease;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row:hover {
        background: rgba(14, 165, 233, 0.04);
        transform: translateX(3px);
    }
    .info-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: rgba(14, 165, 233, 0.09);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .info-label {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 2px;
    }
    .info-value {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* ---- Products List ---- */
    .product-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        margin: 0 -16px;
        border-radius: 16px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border-bottom: 1px solid var(--border-glow);
        position: relative;
    }
    .product-card:last-child { border-bottom: none; }
    .product-card:hover {
        background: linear-gradient(90deg, rgba(14, 165, 233, 0.06) 0%, rgba(6, 182, 212, 0.02) 100%);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.1);
        transform: scale(1.01);
        border-bottom-color: transparent;
    }
    #stallProductsContainer::-webkit-scrollbar {
        width: 6px;
    }
    #stallProductsContainer::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.03);
        border-radius: 10px;
    }
    #stallProductsContainer::-webkit-scrollbar-thumb {
        background: rgba(14, 165, 233, 0.3);
        border-radius: 10px;
    }
    #stallProductsContainer::-webkit-scrollbar-thumb:hover {
        background: var(--primary);
    }

    /* ---- Category Filter Tabs ---- */
    .category-tabs-bar {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 12px 18px;
        border-bottom: 1px solid var(--border-glow);
        background: rgba(14, 165, 233, 0.02);
        scrollbar-width: none;
    }
    .category-tabs-bar::-webkit-scrollbar { display: none; }
    .cat-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        border: 1px solid var(--border-glow);
        background: var(--bg-card);
        color: var(--text-muted);
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .cat-tab-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-1px);
    }
    .cat-tab-btn.active {
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
    }
    .cat-count-badge {
        font-size: 0.7rem;
        padding: 2px 7px;
        border-radius: 12px;
        background: rgba(0, 0, 0, 0.06);
    }
    .cat-tab-btn.active .cat-count-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }
    .product-img {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        object-fit: cover;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .product-card:hover .product-img {
        transform: scale(1.08) rotate(1deg);
    }
    .product-img-placeholder {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
        box-shadow: 0 6px 16px rgba(14, 165, 233, 0.3);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .product-card:hover .product-img-placeholder {
        transform: scale(1.08);
    }
    .product-name {
        font-weight: 800;
        font-size: 0.96rem;
        color: var(--text-main);
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .product-origin {
        font-size: 0.74rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .product-price-chip {
        display: inline-flex;
        align-items: baseline;
        gap: 3px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
        border: 1px solid rgba(14, 165, 233, 0.25);
        border-radius: 10px;
        padding: 4px 12px;
        margin-top: 6px;
    }
    .product-price-num {
        font-weight: 900;
        font-size: 0.98rem;
        color: var(--primary);
    }
    .product-price-unit {
        font-size: 0.7rem;
        color: var(--text-muted);
    }
    .btn-order {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
        flex-shrink: 0;
    }
    .btn-order:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 22px rgba(14, 165, 233, 0.45);
        background: linear-gradient(135deg, #0284c7, #0891b2);
    }
    .btn-order:active { transform: scale(0.95); }
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
        padding: 14px 22px;
        background: rgba(14, 165, 233, 0.08);
        color: var(--primary);
        font-weight: 700;
        font-size: 0.88rem;
        transition: all 0.25s ease;
        cursor: pointer;
        border: none;
        width: 100%;
        text-align: left;
    }
    .map-direction-btn:hover {
        background: rgba(14, 165, 233, 0.16);
        color: #0284c7;
    }
    .map-direction-btn i.bi-box-arrow-up-right {
        transition: transform 0.25s ease;
    }
    .map-direction-btn:hover i.bi-box-arrow-up-right {
        transform: translate(3px, -3px);
    }

    /* ---- QR Code Panel ---- */
    .qr-panel-body {
        padding: 22px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
    }
    .qr-box {
        background: #fff;
        border-radius: 18px;
        padding: 18px;
        border: 2px solid rgba(14, 165, 233, 0.2);
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.12);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .qr-box:hover {
        transform: scale(1.03);
        box-shadow: 0 12px 32px rgba(14, 165, 233, 0.2);
    }
    .qr-box img { width: 160px; height: 160px; display: block; }

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
        padding: 13px 24px;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        color: #fff;
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none !important;
        box-shadow: 0 6px 18px rgba(14, 165, 233, 0.3);
    }
    .btn-stall-primary:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.45);
        color: #fff !important;
    }
    .btn-stall-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 24px;
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.9rem;
        border: 1px solid var(--border-glow);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    }
    .btn-stall-secondary:hover {
        border-color: var(--primary);
        color: var(--primary) !important;
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.15);
    }

    /* ---- Reviews Section ---- */
    .reviews-section {
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: var(--stall-radius);
        overflow: hidden;
        margin-bottom: 28px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
    }
    .review-form-area {
        padding: 28px;
        background: linear-gradient(180deg, rgba(14, 165, 233, 0.05) 0%, transparent 100%);
        border-bottom: 1px solid var(--border-glow);
    }
    .review-form-title {
        font-size: 1rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .star-input-row {
        display: flex;
        gap: 8px;
        margin-bottom: 14px;
    }
    .star-btn {
        font-size: 1.75rem;
        cursor: pointer;
        color: #D1D5DB;
        transition: all 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
        border: none;
        background: none;
        padding: 0;
        line-height: 1;
    }
    .star-btn.active, .star-btn:hover {
        color: var(--stall-gold);
        transform: scale(1.25);
        filter: drop-shadow(0 0 6px rgba(245,158,11,0.5));
    }
    .review-textarea {
        width: 100%;
        border-radius: 14px;
        border: 1.5px solid var(--border-glow);
        background: var(--bg-base);
        color: var(--text-main);
        padding: 14px 18px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: 0.9rem;
        resize: none;
        outline: none;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
        box-sizing: border-box;
    }
    .review-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
    }

    .review-list { padding: 8px 0; }
    .review-item {
        padding: 20px 28px;
        border-bottom: 1px solid var(--border-glow);
        border-left: 3px solid transparent;
        transition: all 0.25s ease;
    }
    .review-item:last-child { border-bottom: none; }
    .review-item:hover {
        background: rgba(14, 165, 233, 0.02);
        border-left-color: var(--primary);
    }
    .review-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }
    .reviewer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 800;
        font-size: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
    }
    .reviewer-name {
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--text-main);
    }
    .reviewer-time {
        font-size: 0.74rem;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .reviewer-stars {
        margin-left: auto;
        color: var(--stall-gold);
        font-size: 0.9rem;
        letter-spacing: 1px;
    }
    .review-comment {
        font-size: 0.9rem;
        color: var(--text-main);
        line-height: 1.65;
        padding-left: 52px;
    }

    .no-reviews-state {
        text-align: center;
        padding: 44px 24px;
        color: var(--text-muted);
    }
    .no-reviews-state .icon { font-size: 3.2rem; margin-bottom: 12px; }
    .no-reviews-state p { font-size: 0.9rem; font-weight: 600; }

    /* ---- Flash success ---- */
    .flash-success {
        background: var(--stall-green-soft);
        border: 1px solid rgba(16,185,129,0.3);
        color: var(--stall-green);
        border-radius: 14px;
        padding: 14px 20px;
        font-weight: 700;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        transition: opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease;
        max-height: 100px;
        opacity: 1;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.15);
    }
    .flash-success.fade-out {
        opacity: 0;
        transform: translateY(-10px);
        max-height: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
        border-width: 0;
    }
</style>

<div class="stall-page-wrapper">

    <!-- Harmonious Modern Breadcrumb Navigation -->
    <nav class="integrated-breadcrumb-nav" aria-label="Breadcrumb">
        <a href="/" class="breadcrumb-item-link">
            <span style="font-size: 0.95rem;">🏠</span>
            <span>Trang chủ</span>
        </a>
        <span class="breadcrumb-arrow">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </span>
        <a href="{{ route('eatery.show', $eatery->slug) }}" class="breadcrumb-item-link">
            <span style="font-size: 0.95rem;">🏛️</span>
            <span>{{ $eatery->name }}</span>
        </a>
        <span class="breadcrumb-arrow">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </span>
        <span class="breadcrumb-item-active">
            <span style="font-size: 0.95rem;">🏪</span>
            <span>{{ $stallName }}</span>
        </span>
    </nav>

    {{-- Flash messages --}}
    @if(session('review_success'))
        <div class="flash-success" id="flashSuccessAlert">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('review_success') }}
        </div>
    @endif

    {{-- ========================= HERO HEADER ========================= --}}
    <div class="stall-hero">
        @php
            $stallCustomImg = $stallProducts->pluck('stall_image')->filter()->first();
            $stallImgUrl = null;
            if (!empty($stallCustomImg)) {
                $trimmedCover = trim($stallCustomImg);
                if (str_starts_with($trimmedCover, 'http://') || str_starts_with($trimmedCover, 'https://')) {
                    $stallImgUrl = $trimmedCover;
                } else {
                    $stallImgUrl = asset(ltrim($trimmedCover, '/'));
                }
            }

            $ownerAvatarUrl = null;
            if (!empty($sellerUser?->avatar)) {
                $trimmedAvt = trim($sellerUser->avatar);
                if (str_starts_with($trimmedAvt, 'http://') || str_starts_with($trimmedAvt, 'https://')) {
                    $ownerAvatarUrl = $trimmedAvt;
                } else {
                    $ownerAvatarUrl = asset(ltrim($trimmedAvt, '/'));
                }
            }

            $emojiMap = [
                'Ăn uống' => '🍜', 'Rau củ' => '🥦', 'Thịt tươi' => '🥩',
                'Thực phẩm khô' => '🌾', 'Khác' => '🏪'
            ];
            $stallEmoji = $emojiMap[$category] ?? '🏪';
        @endphp

        <div class="stall-avatar" style="{{ $stallImgUrl ? 'padding: 0; overflow: hidden; cursor: pointer;' : '' }}"
             @if($stallImgUrl) onclick="openCustomImageLightbox('{{ $stallImgUrl }}')" title="Bấm để xem ảnh thực tế của Gian hàng" @endif>
            @if($stallImgUrl)
                <img src="{{ $stallImgUrl }}" alt="Ảnh Gian Hàng {{ $stallName }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                {{ $stallEmoji }}
            @endif
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
                @if($isCultureMarket)
                    <span class="stall-badge badge-sky" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd; font-weight: 700;">
                        <i class="bi bi-bank"></i> Khu trưng bày di sản Cổ Loa
                    </span>
                    <span class="stall-badge badge-green" style="background: #dcfce7; color: #15803d; border-color: #86efac; font-weight: 700;">
                        <i class="bi bi-shield-check"></i> Không gian văn hóa & ATTP
                    </span>
                @else
                    <span class="stall-badge badge-sky">
                        <i class="bi bi-tag-fill"></i> {{ $category }}
                    </span>
                    <span class="stall-badge badge-green" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 700;">
                        <i class="bi bi-shield-check"></i> Đạt chuẩn ATTP
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
            @if(!$isCultureMarket && $sellerPhone)
                <a href="tel:{{ $sellerPhone }}" class="btn-stall-primary">
                    <i class="bi bi-telephone-fill"></i> Gọi ngay
                </a>
                <a href="https://zalo.me/{{ $sellerPhone }}" target="_blank" class="btn-stall-secondary">
                    <i class="bi bi-chat-dots-fill"></i> Zalo
                </a>
            @elseif($isCultureMarket)
                <div style="padding: 10px 16px; border-radius: 14px; background: rgba(14,165,233,0.08); border: 1px solid rgba(14,165,233,0.25); color: #0284c7; font-size: 0.8rem; font-weight: 800; text-align: center; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-building"></i> Ban Quản lý Chợ Cổ Loa
                </div>
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

            {{-- Thông tin đơn vị quản lý & Triển lãm --}}
            <div class="stall-panel">
                <div class="stall-panel-header">
                    <div class="panel-icon" style="background: rgba(14, 165, 233, 0.08); color: var(--primary);">
                        <i class="bi bi-building"></i>
                    </div>
                    <h3>{{ $isCultureMarket ? 'Thông tin đơn vị quản lý & Triển lãm' : 'Thông tin tiểu thương' }}</h3>
                </div>
                <div class="panel-body">
                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-bank"></i></div>
                        <div>
                            <span class="info-label">{{ $isCultureMarket ? 'Đơn vị trưng bày / Thực hiện' : 'Chủ hộ kinh doanh' }}</span>
                            <span class="info-value">{{ $isCultureMarket ? 'Ban Quản lý Chợ Văn hóa Du lịch Cổ Loa' : ($sellerName ?: 'Chưa cập nhật') }}</span>
                        </div>
                    </div>
                    @if(!$isCultureMarket && $sellerPhone)
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
                    @if(!$isCultureMarket && $bankInfo)
                    <div class="info-row">
                        <div class="info-icon" style="background: rgba(99,102,241,0.1); color: #6366F1;"><i class="bi bi-bank2"></i></div>
                        <div>
                            <span class="info-label">Số tài khoản (STK)</span>
                            <span class="info-value" style="font-family: monospace; letter-spacing: 0.5px;">{{ $bankInfo }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="info-row">
                        <div class="info-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <span class="info-label">Tiêu chuẩn không gian & ATTP</span>
                            <span class="info-value" style="color: #059669; font-weight: 700;">
                                {{ $isCultureMarket ? '✓ Đạt chuẩn không gian văn hóa & ATTP di sản (BQL Chợ Kiểm Định)' : '✓ Đạt chuẩn ATTP (BQL Chợ Kiểm Định)' }}
                            </span>
                        </div>
                    </div>

                    @if($originText && $originText !== 'Tự sản xuất')
                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-geo-fill"></i></div>
                        <div>
                            <span class="info-label">Nguồn gốc xuất xứ</span>
                            <span class="info-value">{{ $originText }}</span>
                        </div>
                    </div>
                    @endif
                    {{-- Hình ảnh đại diện Gian hàng & Chủ hộ --}}
                    <div class="info-row" style="align-items: flex-start;">
                        <div class="info-icon" style="background: rgba(14, 165, 233, 0.1); color: var(--primary);">
                            <i class="bi bi-images"></i>
                        </div>
                        <div style="flex: 1;">
                            <span class="info-label">Hình ảnh đại diện Gian hàng & Chủ hộ</span>
                            <div style="display: flex; gap: 12px; margin-top: 10px; flex-wrap: wrap;">
                                {{-- Ảnh đại diện Gian hàng (Sạp) --}}
                                <div style="flex: 1; min-width: 130px; background: var(--bg-card); border: 1px solid var(--border-glow); border-radius: 12px; padding: 10px; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                        <span>🏪</span> Ảnh Sạp Gian Hàng
                                    </div>
                                    @if($stallImgUrl)
                                        <div onclick="openCustomImageLightbox('{{ $stallImgUrl }}')" 
                                             style="width: 100%; height: 110px; border-radius: 8px; overflow: hidden; position: relative; cursor: pointer; border: 1px solid rgba(0,0,0,0.1);"
                                             title="Bấm để xem ảnh phóng to">
                                            <img src="{{ $stallImgUrl }}" alt="Ảnh sạp gian hàng" style="width: 100%; height: 100%; object-fit: cover;">
                                            <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.65); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; backdrop-filter: blur(4px);">
                                                <i class="bi bi-zoom-in"></i> Phóng to
                                            </div>
                                        </div>
                                    @else
                                        <div style="width: 100%; height: 110px; border-radius: 8px; background: rgba(0,0,0,0.03); border: 1px dashed var(--border-glow); display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); padding: 6px;">
                                            <span style="font-size: 1.8rem;">{{ $stallEmoji }}</span>
                                            <span style="font-size: 0.68rem; margin-top: 4px; color: var(--text-muted);">Chưa tải ảnh sạp thực tế</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Ảnh đại diện Chủ hộ / Tiểu thương --}}
                                @if(!$isCultureMarket)
                                <div style="flex: 1; min-width: 130px; background: var(--bg-card); border: 1px solid var(--border-glow); border-radius: 12px; padding: 10px; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                        <span>👤</span> Avatar Chủ Hộ
                                    </div>
                                    @if($ownerAvatarUrl)
                                        <div onclick="openCustomImageLightbox('{{ $ownerAvatarUrl }}')" 
                                             style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; position: relative; cursor: pointer; border: 2.5px solid var(--primary); box-shadow: 0 4px 12px rgba(14,165,233,0.25); margin: 12px auto;"
                                             title="Bấm để xem ảnh phóng to">
                                            <img src="{{ $ownerAvatarUrl }}" alt="Avatar chủ hộ" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    @else
                                        @php
                                            $firstChar = mb_substr(trim($sellerName ?: 'T'), 0, 1);
                                        @endphp
                                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #06b6d4); color: #fff; font-size: 1.8rem; font-weight: 900; display: flex; align-items: center; justify-content: center; margin: 12px auto; box-shadow: 0 4px 12px rgba(14,165,233,0.25);">
                                            {{ mb_strtoupper($firstChar) }}
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

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
            @php
                $categoriesMap = [
                    'food'   => ['name' => '🍜 Món ăn & Đồ uống', 'keywords' => ['bún', 'phở', 'xôi', 'bánh', 'cơm', 'cháo', 'miến', 'nước', 'chè', 'trà', 'sữa', 'cà phê']],
                    'veggie' => ['name' => '🥦 Rau củ & Trái cây', 'keywords' => ['rau', 'củ', 'quả', 'nấm', 'cam', 'táo', 'chuối', 'dưa', 'cà', 'hành', 'tỏi', 'ớt']],
                    'meat'   => ['name' => '🥩 Thịt & Hải sản', 'keywords' => ['thịt', 'giò', 'chả', 'cá', 'tôm', 'mực', 'gà', 'vịt', 'bò', 'heo', 'lợn', 'trứng']],
                    'dry'    => ['name' => '🌾 Món khô & Gia vị', 'keywords' => ['gạo', 'đỗ', 'hạt', 'khô', 'mắm', 'muối', 'đường', 'tiêu', 'dầu', 'mì', 'tương']],
                ];

                $categoryCounts = ['all' => $stallProducts->count()];
                $prodCatAssoc = [];

                foreach ($stallProducts as $prod) {
                    $pNameLower = mb_strtolower($prod->name);
                    $foundCat = 'other';
                    foreach ($categoriesMap as $catKey => $catInfo) {
                        foreach ($catInfo['keywords'] as $kw) {
                            if (str_contains($pNameLower, $kw)) {
                                $foundCat = $catKey;
                                break 2;
                            }
                        }
                    }
                    $prodCatAssoc[$prod->id] = $foundCat;
                    $categoryCounts[$foundCat] = ($categoryCounts[$foundCat] ?? 0) + 1;
                }

                $activeCategoryCount = count(array_filter($categoryCounts, fn($cnt, $k) => $k !== 'all' && $cnt > 0, ARRAY_FILTER_USE_BOTH));
            @endphp

            <div class="stall-panel">
                <div class="stall-panel-header" style="flex-wrap: wrap; gap: 10px;">
                    <div class="panel-icon" style="background: rgba(14, 165, 233, 0.08); color: var(--primary);">
                        <i class="bi bi-basket2-fill"></i>
                    </div>
                    <h3>Sản phẩm niêm yết (<span id="productCountBadge">{{ $stallProducts->count() }}</span>)</h3>
                    
                    @if($stallProducts->count() > 3)
                        <div style="margin-left: auto; width: 100%; max-width: 220px; position: relative;">
                            <input type="text" id="productSearchInput" placeholder="Tìm sản phẩm..." onkeyup="filterStallProducts()"
                                   style="width: 100%; padding: 6px 14px 6px 30px; border-radius: 20px; border: 1px solid var(--border-glow); background: var(--bg-base); color: var(--text-main); font-size: 0.8rem; outline: none; box-sizing: border-box;">
                            <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.75rem;"></i>
                        </div>
                    @else
                        <span style="margin-left: auto; font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">Giá niêm yết</span>
                    @endif
                </div>

                {{-- Category Filter Tabs Bar --}}
                @if($stallProducts->count() > 0)
                    <div class="category-tabs-bar">
                        <button type="button" class="cat-tab-btn active" data-cat="all" onclick="selectProductCategory('all', this)">
                            <span>Tất cả</span>
                            <span class="cat-count-badge">{{ $stallProducts->count() }}</span>
                        </button>
                        @foreach($categoriesMap as $catKey => $catInfo)
                            @if(!empty($categoryCounts[$catKey]))
                                <button type="button" class="cat-tab-btn" data-cat="{{ $catKey }}" onclick="selectProductCategory('{{ $catKey }}', this)">
                                    <span>{{ $catInfo['name'] }}</span>
                                    <span class="cat-count-badge">{{ $categoryCounts[$catKey] }}</span>
                                </button>
                            @endif
                        @endforeach
                        @if(!empty($categoryCounts['other']))
                            <button type="button" class="cat-tab-btn" data-cat="other" onclick="selectProductCategory('other', this)">
                                <span>📦 Khác</span>
                                <span class="cat-count-badge">{{ $categoryCounts['other'] }}</span>
                            </button>
                        @endif
                    </div>
                @endif

                <div class="panel-body" id="stallProductsContainer" style="{{ $stallProducts->count() > 5 ? 'max-height: 540px; overflow-y: auto; padding-right: 14px;' : '' }}">
                    @forelse($stallProducts as $product)
                        @php
                            $isCultureMarket = str_contains(strtolower($eatery->slug ?? ''), 'van-hoa-du-lich') || str_contains(strtolower($eatery->name ?? ''), 'văn hóa du lịch');
                            $hasCustomOrigin = false;
                            $prodOrigin = '';
                            $cleanDesc = '';
                            if (!empty($product->description)) {
                                if (preg_match('/^Nguồn gốc[:\s]+(.*?)(?:\.|\n|$)(.*)/us', $product->description, $pm)) {
                                    $hasCustomOrigin = true;
                                    $prodOrigin = trim($pm[1]);
                                    $cleanDesc = trim($pm[2], " .\t\n\r");
                                } else {
                                    $cleanDesc = trim($product->description);
                                }
                            }
                            $prodEmoji = '🌾';
                            $pCat = $prodCatAssoc[$product->id] ?? 'other';
                            $starRating = $product->star_rating;
                        @endphp
                        @php
                            $prodImgUrl = $product->image_url ?: ($product->image_path ?: '/images/stalls/food.png');
                            if (!empty($prodImgUrl)) {
                                if (!str_starts_with($prodImgUrl, 'http://') && !str_starts_with($prodImgUrl, 'https://')) {
                                    $prodImgUrl = asset(ltrim($prodImgUrl, '/'));
                                }
                            } else {
                                $prodImgUrl = asset('/images/stalls/food.png');
                            }
                        @endphp
                        <div class="product-card" data-name="{{ mb_strtolower($product->name) }}" data-category="{{ $pCat }}" style="padding: 14px 16px; align-items: flex-start;">
                            {{-- Thumbnail --}}
                            <img src="{{ $prodImgUrl }}" alt="{{ $product->name }}" class="product-img" style="width: 76px; height: 76px; border-radius: 12px; object-fit: cover; border: 1px solid #cbd5e1; cursor: pointer;" onclick="openPublicDetailModal({{ json_encode($product->name) }}, {{ intval($product->price) }}, {{ json_encode($product->unit ?: '') }}, {{ json_encode($product->description ?: '') }}, {{ json_encode($product->all_images) }}, {{ json_encode($starRating ?: '') }}, {{ $product->id }})">

                            {{-- Info --}}
                            <div style="flex: 1; min-width: 0; padding-right: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 2px;">
                                    <div class="product-name" style="cursor: pointer;" onclick="openPublicDetailModal({{ json_encode($product->name) }}, {{ intval($product->price) }}, {{ json_encode($product->unit ?: '') }}, {{ json_encode($product->description ?: '') }}, {{ json_encode($product->all_images) }}, {{ json_encode($starRating ?: '') }}, {{ $product->id }})">{{ $product->name }}</div>
                                    @if(!$isCultureMarket && !empty($starRating) && $starRating !== 'OCOP / Đặc sản' && $starRating !== '4 sao')
                                        <span style="background: #fef3c7; color: #92400e; font-size: 0.68rem; font-weight: 700; padding: 2px 7px; border-radius: 8px; border: 1px solid #fde68a;">⭐ {{ $starRating }}</span>
                                    @endif
                                </div>

                                @if(!$isCultureMarket && $hasCustomOrigin && !empty($prodOrigin))
                                    <div class="product-origin" style="margin-top: 2px;">
                                        <i class="bi bi-geo-alt-fill" style="color: var(--primary);"></i>
                                        <strong>Nguồn gốc:</strong> {{ $prodOrigin }}
                                    </div>
                                @endif

                                @if(!empty($cleanDesc))
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $cleanDesc }}
                                    </div>
                                @endif

                                <div class="product-price-chip" style="margin-top: 6px;">
                                    @if($product->price)
                                        <span class="product-price-num">{{ is_numeric($product->price) ? number_format($product->price, 0, ',', '.') . '₫' : $product->price }}</span>
                                        @if($product->unit)<span class="product-price-unit">&nbsp;/ {{ $product->unit }}</span>@endif
                                    @else
                                        <span class="product-price-num" style="color: #0284c7; font-size: 0.85rem;">Sản phẩm trưng bày</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Action buttons --}}
                            <div style="display: flex; gap: 8px; align-items: center; flex-shrink: 0; margin-top: 4px;">
                                <button type="button"
                                        style="height: 38px; padding: 0 16px; border-radius: 20px; border: 1.5px solid #0284c7; background: #0284c7; color: #ffffff; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; transition: all 0.2s;"
                                        onmouseover="this.style.background='#0369a1'"
                                        onmouseout="this.style.background='#0284c7'"
                                        onclick="openPublicDetailModal({{ json_encode($product->name) }}, {{ intval($product->price) }}, {{ json_encode($product->unit ?: '') }}, {{ json_encode($product->description ?: '') }}, {{ json_encode($product->all_images) }}, {{ json_encode($starRating ?: '') }}, {{ $product->id }})">
                                    <i class="bi bi-eye-fill"></i> Xem chi tiết
                                </button>
                                @if(!$isCultureMarket)
                                    <button class="btn-order add-to-cart-btn"
                                            data-id="{{ $product->id }}"
                                            data-type="ocop_product"
                                            style="height: 38px; padding: 0 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;"
                                            onclick="addToCart(event, this); animateFlyToCart(this);">
                                        <i class="bi bi-cart-plus-fill"></i> Đặt hàng
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding: 32px 0; color: var(--text-muted);">
                            <div style="font-size: 2.5rem; margin-bottom: 10px;">📦</div>
                            <p style="font-size: 0.9rem; font-weight: 600;">Chưa có sản phẩm được niêm yết.</p>
                        </div>
                    @endforelse
                    <div id="noProductMatch" style="display: none; text-align: center; padding: 28px 0; color: var(--text-muted);">
                        <div style="font-size: 2rem; margin-bottom: 6px;">🔍</div>
                        <p style="font-size: 0.85rem; font-weight: 600;">Không tìm thấy sản phẩm phù hợp.</p>
                    </div>
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

            {{-- QR Thanh toán VietQR 4.0 --}}
            @if(!empty($qrCodeUrl))
            <div class="stall-panel">
                <div class="stall-panel-header" style="background: linear-gradient(90deg, rgba(16, 185, 129, 0.08) 0%, transparent 100%); border-bottom: 1px solid rgba(16, 185, 129, 0.2);">
                    <div class="panel-icon" style="background: #e6f4ea; color: #10b981;">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h3 style="color: #047857;">MÃ VIETQR THANH TOÁN SỐ 4.0</h3>
                </div>
                <div class="qr-panel-body" style="padding: 20px; background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); text-align: center;">
                    <div style="background: #ffffff; padding: 12px; border-radius: 16px; display: inline-block; box-shadow: 0 6px 18px rgba(0,0,0,0.06); border: 1.5px solid #d1fae5; margin-bottom: 12px;">
                        <img src="{{ $qrCodeUrl }}" style="width: 200px; height: auto; max-width: 100%; display: block; border-radius: 8px;" alt="VietQR Thanh toán Chợ số">
                    </div>
                    <div style="font-size: 0.9rem; font-weight: 800; color: #065f46; margin-bottom: 2px;">
                        {{ $bankName ?? 'MBBank' }} · {{ $bankAcct }}
                    </div>
                    <div style="font-size: 0.76rem; color: #047857; text-transform: uppercase; font-weight: 700; margin-bottom: 12px;">
                        CHỦ TK: {{ $bankHolder ?? $sellerName }}
                    </div>
                    <a href="{{ $qrCodeUrl }}" target="_blank" download="VietQR_{{ Str::slug($stallName) }}.png" class="btn-stall-primary" style="background: #10b981; border: none; justify-content: center; width: 100%; text-decoration: none;">
                        <i class="bi bi-download"></i> Tải Mã QR Thanh Toán
                    </a>
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
            <form method="POST" action="{{ route('market.stall.review.store', ['marketSlug' => $marketSlug, 'stallSlug' => $stallSlug]) }}" enctype="multipart/form-data">
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

                @if(Auth::check())
                    <div style="margin-bottom: 12px; font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px; background: rgba(14, 165, 233, 0.05); padding: 8px 14px; border-radius: 10px; border: 1px solid rgba(14, 165, 233, 0.15);">
                        <i class="bi bi-person-check-fill" style="color: var(--primary); font-size: 1.1rem;"></i>
                        <span>Đang đánh giá với tài khoản: <strong style="color: var(--text-main);">{{ Auth::user()->name }}</strong></span>
                    </div>
                @else
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

                {{-- Media Attachment --}}
                <div style="margin-top: 12px;">
                    <label for="stallReviewMedia" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px; border: 1px dashed var(--border-glow); background: var(--bg-base); color: var(--text-muted); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                        <i class="bi bi-camera-fill" style="color: var(--primary); font-size: 1rem;"></i>
                        <span>Thêm ảnh / video (tùy chọn)</span>
                    </label>
                    <input type="file" name="media[]" id="stallReviewMedia" accept="image/*,video/*" multiple style="display: none;" onchange="previewStallReviewMedia(this)">
                    <div id="stallMediaPreview" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;"></div>
                </div>

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
                    @if($rev->media && $rev->media->count() > 0)
                        @php
                            $galleryItems = $rev->media->map(function($m) {
                                return [
                                    'url'  => asset($m->file_path),
                                    'type' => $m->file_type ?? 'image'
                                ];
                            })->values()->toArray();
                        @endphp
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                            @foreach($rev->media as $mIndex => $mediaItem)
                                @if($mediaItem->file_type === 'image')
                                    <div style="position: relative; width: 90px; height: 90px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-glow); cursor: pointer; transition: transform 0.2s ease;"
                                         onmouseover="this.style.transform='scale(1.05)'"
                                         onmouseout="this.style.transform='none'"
                                         onclick="openReviewGallery({{ json_encode($galleryItems) }}, {{ $mIndex }})">
                                        <img src="{{ asset($mediaItem->file_path) }}" alt="Review Image" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                @else
                                    <div style="position: relative; width: 140px; height: 90px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-glow); cursor: pointer;"
                                         onclick="openReviewGallery({{ json_encode($galleryItems) }}, {{ $mIndex }})">
                                        <video src="{{ asset($mediaItem->file_path) }}" style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;"></video>
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #fff; font-size: 1.2rem; text-shadow: 0 0 8px #000;">▶</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
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

{{-- ========================= REVIEW GALLERY LIGHTBOX MODAL ========================= --}}
<div id="reviewGalleryModal" onclick="closeReviewGallery()" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.93); backdrop-filter: blur(10px); z-index: 999999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <!-- Close button -->
    <button type="button" onclick="closeReviewGallery()" style="position: absolute; top: 20px; right: 24px; background: rgba(255,255,255,0.15); border: none; color: #fff; width: 44px; height: 44px; border-radius: 50%; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1000001; transition: background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.8)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
        &times;
    </button>

    <!-- Counter badge -->
    <div id="galleryCounter" style="position: absolute; top: 24px; left: 24px; color: #fff; background: rgba(255,255,255,0.15); padding: 6px 16px; border-radius: 20px; font-weight: 700; font-size: 0.88rem; z-index: 1000001; letter-spacing: 0.5px;">
        1 / 1
    </div>

    <!-- Prev button -->
    <button type="button" id="galleryPrevBtn" onclick="event.stopPropagation(); navigateReviewGallery(-1);" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1000001; transition: background 0.2s;" onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
        <i class="bi bi-chevron-left"></i>
    </button>

    <!-- Media display container -->
    <div style="max-width: 90vw; max-height: 85vh; display: flex; align-items: center; justify-content: center; position: relative;" onclick="event.stopPropagation()">
        <img id="galleryImg" src="" alt="Zoomed Review Image" style="max-width: 90vw; max-height: 85vh; border-radius: 14px; object-fit: contain; box-shadow: 0 10px 40px rgba(0,0,0,0.8); display: none;">
        <video id="galleryVid" src="" controls style="max-width: 90vw; max-height: 85vh; border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.8); display: none;"></video>
    </div>

    <!-- Next button -->
    <button type="button" id="galleryNextBtn" onclick="event.stopPropagation(); navigateReviewGallery(1);" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1000001; transition: background 0.2s;" onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
        <i class="bi bi-chevron-right"></i>
    </button>
</div>

{{-- ========================= LEAFLET INIT ========================= --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WPaU=" crossorigin=""></script>
<script>
let currentSelectedCategory = 'all';

function selectProductCategory(catKey, btnEl) {
    currentSelectedCategory = catKey;
    document.querySelectorAll('.cat-tab-btn').forEach(btn => btn.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    filterStallProducts();
}

function filterStallProducts() {
    const input = document.getElementById('productSearchInput');
    const filter = input ? input.value.toLowerCase().trim() : '';
    const cards = document.querySelectorAll('#stallProductsContainer .product-card');
    const noMatch = document.getElementById('noProductMatch');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const cat = card.getAttribute('data-category') || 'other';

        const matchSearch = name.includes(filter);
        const matchCategory = (currentSelectedCategory === 'all' || cat === currentSelectedCategory);

        if (matchSearch && matchCategory) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    if (noMatch) {
        noMatch.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}
window.selectProductCategory = selectProductCategory;
window.filterStallProducts = filterStallProducts;
let currentGalleryItems = [];
let currentGalleryIndex = 0;

function openReviewGallery(items, index) {
    if (!items || items.length === 0) return;
    currentGalleryItems = items;
    currentGalleryIndex = index;
    
    const modal = document.getElementById('reviewGalleryModal');
    if (!modal) return;
    
    updateGalleryView();
    
    modal.style.display = 'flex';
    setTimeout(() => { modal.style.opacity = '1'; }, 10);
    document.body.style.overflow = 'hidden';
}

function updateGalleryView() {
    const item = currentGalleryItems[currentGalleryIndex];
    const imgEl = document.getElementById('galleryImg');
    const vidEl = document.getElementById('galleryVid');
    const counterEl = document.getElementById('galleryCounter');
    const prevBtn = document.getElementById('galleryPrevBtn');
    const nextBtn = document.getElementById('galleryNextBtn');

    if (!item) return;

    if (item.type === 'video') {
        imgEl.style.display = 'none';
        vidEl.style.display = 'block';
        vidEl.src = item.url;
    } else {
        vidEl.pause();
        vidEl.style.display = 'none';
        imgEl.style.display = 'block';
        imgEl.src = item.url;
    }

    if (counterEl) {
        counterEl.innerText = (currentGalleryIndex + 1) + ' / ' + currentGalleryItems.length;
    }

    if (currentGalleryItems.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
    } else {
        if (prevBtn) prevBtn.style.display = 'flex';
        if (nextBtn) nextBtn.style.display = 'flex';
    }
}

function navigateReviewGallery(direction) {
    if (currentGalleryItems.length <= 1) return;
    currentGalleryIndex = (currentGalleryIndex + direction + currentGalleryItems.length) % currentGalleryItems.length;
    updateGalleryView();
}

function closeReviewGallery() {
    const modal = document.getElementById('reviewGalleryModal');
    const vidEl = document.getElementById('galleryVid');
    if (!modal) return;
    
    if (vidEl) vidEl.pause();
    
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
}

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('reviewGalleryModal');
    if (modal && modal.style.display === 'flex') {
        if (e.key === 'Escape') {
            closeReviewGallery();
        } else if (e.key === 'ArrowLeft') {
            navigateReviewGallery(-1);
        } else if (e.key === 'ArrowRight') {
            navigateReviewGallery(1);
        }
    }
});

window.openReviewGallery = openReviewGallery;
window.closeReviewGallery = closeReviewGallery;
window.navigateReviewGallery = navigateReviewGallery;

let selectedReviewFiles = [];

function previewStallReviewMedia(input) {
    if (input.files && input.files.length > 0) {
        const newFiles = Array.from(input.files);
        selectedReviewFiles = selectedReviewFiles.concat(newFiles);
        updateFileInputAndPreview();
    }
}

function removeStallReviewMedia(index) {
    selectedReviewFiles.splice(index, 1);
    updateFileInputAndPreview();
}

function updateFileInputAndPreview() {
    const input = document.getElementById('stallReviewMedia');
    const preview = document.getElementById('stallMediaPreview');
    if (!preview) return;

    // Sync HTML input files list using DataTransfer
    if (input && window.DataTransfer) {
        const dt = new DataTransfer();
        selectedReviewFiles.forEach(file => dt.items.add(file));
        input.files = dt.files;
    }

    preview.innerHTML = '';
    selectedReviewFiles.forEach((file, index) => {
        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'position: relative; width: 80px; height: 80px; border-radius: 10px; overflow: hidden; border: 1.5px solid var(--border-glow); background: #000; box-shadow: 0 4px 10px rgba(0,0,0,0.15);';
        
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
            wrapper.appendChild(img);
        } else if (file.type.startsWith('video/')) {
            const vid = document.createElement('video');
            vid.src = URL.createObjectURL(file);
            vid.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
            wrapper.appendChild(vid);
            
            const playIcon = document.createElement('div');
            playIcon.innerHTML = '▶';
            playIcon.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #fff; font-size: 1.1rem; pointer-events: none; text-shadow: 0 0 6px #000;';
            wrapper.appendChild(playIcon);
        }

        // Delete "X" button at top-right
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.innerHTML = '&times;';
        removeBtn.title = 'Xóa tệp này';
        removeBtn.style.cssText = 'position: absolute; top: 3px; right: 3px; width: 22px; height: 22px; border-radius: 50%; background: rgba(239, 68, 68, 0.95); color: #fff; border: 1.5px solid #fff; font-size: 15px; font-weight: bold; line-height: 1; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.4); z-index: 10; transition: transform 0.15s ease, background 0.15s ease;';
        removeBtn.onmouseover = function() { this.style.transform = 'scale(1.15)'; this.style.background = '#dc2626'; };
        removeBtn.onmouseout = function() { this.style.transform = 'scale(1)'; this.style.background = 'rgba(239, 68, 68, 0.95)'; };
        removeBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            removeStallReviewMedia(index);
        };
        wrapper.appendChild(removeBtn);

        preview.appendChild(wrapper);
    });
}

window.previewStallReviewMedia = previewStallReviewMedia;
window.removeStallReviewMedia = removeStallReviewMedia;

document.addEventListener('DOMContentLoaded', function() {
    // ---- Auto-hide Flash Message ----
    const flashAlert = document.getElementById('flashSuccessAlert');
    if (flashAlert) {
        setTimeout(function() {
            flashAlert.classList.add('fade-out');
            setTimeout(function() {
                if (flashAlert.parentNode) {
                    flashAlert.parentNode.removeChild(flashAlert);
                }
            }, 500);
        }, 3500);
    }

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

/* ==========================================================
   Public Product Detail Modal (Đồng bộ 100% dữ liệu)
   ========================================================== */
let activePublicProdId = null;
let modalImages = [];
let currentImgIndex = 0;

function openPublicDetailModal(name, price, unit, description, images, starRating, productId) {
    activePublicProdId = productId;
    document.getElementById('pub-detail-name').textContent = name;
    document.getElementById('pub-detail-unit').textContent = unit ? ('/ ' + unit) : '';
    document.getElementById('pub-detail-price').textContent = (price && parseInt(price) > 0) ? (parseInt(price).toLocaleString('vi-VN') + '₫') : 'Trưng bày';
    
    // Parse images array
    if (Array.isArray(images) && images.length > 0) {
        modalImages = images.filter(Boolean);
    } else if (typeof images === 'string' && images.trim() !== '') {
        try {
            const parsed = JSON.parse(images);
            if (Array.isArray(parsed) && parsed.length > 0) {
                modalImages = parsed.filter(Boolean);
            } else {
                modalImages = [images];
            }
        } catch(e) {
            modalImages = [images];
        }
    } else {
        modalImages = ['/images/stalls/food.png'];
    }
    if (modalImages.length === 0) modalImages = ['/images/stalls/food.png'];

    currentImgIndex = 0;
    renderModalImage();

    let originVal = '';
    let descVal = description || 'Chưa có mô tả chi tiết sản phẩm.';
    if (descVal.startsWith('Nguồn gốc:')) {
        const parts = descVal.split('.');
        const firstPart = parts.shift();
        originVal = firstPart.replace(/^Nguồn gốc:\s*/, '').trim();
        descVal = parts.join('.').trim() || 'Chưa có mô tả thêm.';
    }

    const originBox = document.getElementById('pub-detail-origin-box');
    if (originBox) {
        if (originVal && originVal.toLowerCase() !== 'tự sản xuất') {
            document.getElementById('pub-detail-origin').textContent = originVal;
            originBox.style.display = 'block';
        } else {
            originBox.style.display = 'none';
        }
    }
    
    const starElem = document.getElementById('pub-detail-star');
    if (starElem) {
        if (starRating && starRating !== 'OCOP / Đặc sản' && starRating !== '4 sao') {
            starElem.textContent = '⭐ ' + starRating;
            starElem.style.display = 'inline-block';
        } else {
            starElem.style.display = 'none';
        }
    }

    document.getElementById('pub-detail-description').textContent = descVal;

    const modal = document.getElementById('pub-detail-product-modal');
    const box = document.getElementById('pub-detail-modal-box');
    modal.style.display = 'flex';
    setTimeout(function() { box.style.transform = 'scale(1)'; }, 10);
    document.body.style.overflow = 'hidden';
}

function renderModalImage() {
    const mainImg = document.getElementById('pub-detail-img');
    const counter = document.getElementById('pub-detail-img-counter');
    const navPrev = document.getElementById('pub-detail-img-prev');
    const navNext = document.getElementById('pub-detail-img-next');
    const thumbContainer = document.getElementById('pub-detail-thumbs');
    
    const curImg = modalImages[currentImgIndex];
    if (mainImg) mainImg.src = curImg;

    if (modalImages.length > 1) {
        if (counter) { counter.style.display = 'inline-block'; counter.textContent = `${currentImgIndex + 1} / ${modalImages.length}`; }
        if (navPrev) navPrev.style.display = 'flex';
        if (navNext) navNext.style.display = 'flex';
        
        if (thumbContainer) {
            thumbContainer.style.display = 'flex';
            thumbContainer.innerHTML = '';
            modalImages.forEach((img, idx) => {
                const thumb = document.createElement('img');
                thumb.src = img;
                thumb.style.width = '34px';
                thumb.style.height = '34px';
                thumb.style.objectFit = 'cover';
                thumb.style.borderRadius = '6px';
                thumb.style.cursor = 'pointer';
                thumb.style.border = (idx === currentImgIndex) ? '2px solid #0284c7' : '1px solid #cbd5e1';
                thumb.style.opacity = (idx === currentImgIndex) ? '1' : '0.55';
                thumb.onclick = function() {
                    currentImgIndex = idx;
                    renderModalImage();
                };
                thumbContainer.appendChild(thumb);
            });
        }
    } else {
        if (counter) counter.style.display = 'none';
        if (navPrev) navPrev.style.display = 'none';
        if (navNext) navNext.style.display = 'none';
        if (thumbContainer) thumbContainer.style.display = 'none';
    }
}

function changeModalImage(step) {
    if (modalImages.length <= 1) return;
    currentImgIndex = (currentImgIndex + step + modalImages.length) % modalImages.length;
    renderModalImage();
}

/* Lightbox Image Viewer Modal */
let lightboxIndex = 0;

function openImageLightbox(index) {
    if (!modalImages || modalImages.length === 0) return;
    lightboxIndex = typeof index === 'number' ? index : currentImgIndex;
    renderLightboxContent();
    const modal = document.getElementById('pub-img-lightbox-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function openCustomImageLightbox(url) {
    if (!url) return;
    modalImages = [url];
    lightboxIndex = 0;
    renderLightboxContent();
    const modal = document.getElementById('pub-img-lightbox-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function renderLightboxContent() {
    const lbImg = document.getElementById('pub-lb-img');
    const lbCounter = document.getElementById('pub-lb-counter');
    const lbPrev = document.getElementById('pub-lb-prev');
    const lbNext = document.getElementById('pub-lb-next');
    
    if (lbImg) lbImg.src = modalImages[lightboxIndex];
    if (lbCounter) lbCounter.textContent = `Ảnh ${lightboxIndex + 1} / ${modalImages.length}`;
    
    if (modalImages.length > 1) {
        if (lbPrev) lbPrev.style.display = 'flex';
        if (lbNext) lbNext.style.display = 'flex';
    } else {
        if (lbPrev) lbPrev.style.display = 'none';
        if (lbNext) lbNext.style.display = 'none';
    }
}

function changeLightboxImage(step) {
    if (!modalImages || modalImages.length <= 1) return;
    lightboxIndex = (lightboxIndex + step + modalImages.length) % modalImages.length;
    currentImgIndex = lightboxIndex;
    renderModalImage();
    renderLightboxContent();
}

function closeImageLightbox(e) {
    if (e && e.target !== document.getElementById('pub-img-lightbox-modal') && !e.target.classList.contains('lb-close')) return;
    const modal = document.getElementById('pub-img-lightbox-modal');
    if (modal) {
        modal.style.display = 'none';
        if (document.getElementById('pub-detail-product-modal').style.display !== 'flex') {
            document.body.style.overflow = '';
        }
    }
}

function closePublicDetailModal(e) {
    if (e && e.target !== document.getElementById('pub-detail-product-modal')) return;
    const modal = document.getElementById('pub-detail-product-modal');
    const box = document.getElementById('pub-detail-modal-box');
    box.style.transform = 'scale(0.92)';
    setTimeout(function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 250);
}

function orderFromPublicModal() {
    if (!activePublicProdId) return;
    const btn = document.querySelector(`.add-to-cart-btn[data-id="${activePublicProdId}"]`);
    if (btn) {
        closePublicDetailModal();
        addToCart(new Event('click'), btn);
        animateFlyToCart(btn);
    }
}

document.addEventListener('keydown', function(e) {
    const lbModal = document.getElementById('pub-img-lightbox-modal');
    if (lbModal && lbModal.style.display === 'flex') {
        if (e.key === 'Escape') closeImageLightbox();
        if (e.key === 'ArrowLeft') changeLightboxImage(-1);
        if (e.key === 'ArrowRight') changeLightboxImage(1);
        return;
    }
    const modal = document.getElementById('pub-detail-product-modal');
    if (modal && modal.style.display === 'flex') {
        if (e.key === 'Escape') closePublicDetailModal();
        if (e.key === 'ArrowLeft') changeModalImage(-1);
        if (e.key === 'ArrowRight') changeModalImage(1);
    }
});
</script>

<!-- ============================================================
     MODAL XEM CHI TIẾT SẢN PHẨM (Multi-Image Carousel + Clean Layout)
     ============================================================ -->
<div id="pub-detail-product-modal" style="
    display: none;
    position: fixed; inset: 0; z-index: 10000;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(10px);
    align-items: center; justify-content: center;
" onclick="closePublicDetailModal(event)">

    <div style="
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 30px 70px rgba(0,0,0,0.3);
        width: 100%; max-width: 620px;
        margin: 24px;
        overflow: hidden;
        transform: scale(0.92);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    " id="pub-detail-modal-box" onclick="event.stopPropagation()">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; padding: 22px 28px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">👁️</div>
                <div>
                    <div style="font-size: 1.15rem; font-weight: 800; letter-spacing: -0.01em;">Chi Tiết Sản Phẩm</div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.85); margin-top: 2px;">🏪 Gian hàng: <strong>{{ $stallName }}</strong></div>
                </div>
            </div>
            <button onclick="closePublicDetailModal()" style="background: rgba(255,255,255,0.18); border: none; color: #fff; width: 38px; height: 38px; border-radius: 50%; font-size: 1.3rem; cursor: pointer; transition: background 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.18)'">×</button>
        </div>

        <div style="padding: 26px;">
            <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                <!-- Product Image Viewer with Multi-Image Carousel -->
                <div style="position: relative; flex-shrink: 0; width: 170px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="position: relative; width: 160px; height: 160px; border-radius: 18px; overflow: hidden; border: 2.5px solid #e0f2fe; box-shadow: 0 8px 20px rgba(2,132,199,0.15); background: #f8fafc; cursor: pointer;" onclick="openImageLightbox()">
                        <img id="pub-detail-img" src="" alt="Ảnh sản phẩm" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='none'">
                        
                        <!-- Hover Zoom Hint Badge -->
                        <span style="position: absolute; top: 6px; left: 6px; background: rgba(15,23,42,0.65); color: #ffffff; font-size: 0.65rem; font-weight: 700; padding: 2px 7px; border-radius: 10px; backdrop-filter: blur(4px); display: flex; align-items: center; gap: 3px; pointer-events: none;">🔍 Phóng to</span>
                        
                        <!-- Nav Prev Arrow -->
                        <button id="pub-detail-img-prev" onclick="event.stopPropagation(); changeModalImage(-1)" style="display: none; position: absolute; left: 4px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.65); color: #ffffff; border: none; width: 26px; height: 26px; border-radius: 50%; font-size: 0.8rem; cursor: pointer; align-items: center; justify-content: center; z-index: 2;">❮</button>
                        
                        <!-- Nav Next Arrow -->
                        <button id="pub-detail-img-next" onclick="event.stopPropagation(); changeModalImage(1)" style="display: none; position: absolute; right: 4px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.65); color: #ffffff; border: none; width: 26px; height: 26px; border-radius: 50%; font-size: 0.8rem; cursor: pointer; align-items: center; justify-content: center; z-index: 2;">❯</button>
                        
                        <!-- Image Counter Badge -->
                        <span id="pub-detail-img-counter" style="display: none; position: absolute; bottom: 6px; right: 6px; background: rgba(0,0,0,0.65); color: #ffffff; font-size: 0.68rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; backdrop-filter: blur(4px); z-index: 2;">1 / 1</span>
                    </div>

                    <!-- Thumbnails row -->
                    <div id="pub-detail-thumbs" style="display: none; gap: 6px; flex-wrap: wrap; justify-content: center; max-width: 160px; max-height: 80px; overflow-y: auto;"></div>
                </div>

                <!-- Main Meta Info -->
                <div style="flex: 1; min-width: 240px;">
                    <div id="pub-detail-badges" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                        @if($isCultureMarket)
                            <span style="background: #f0f9ff; color: #0284c7; font-size: 0.73rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; border: 1px solid #bae6fd;">🌾 Sản phẩm trưng bày di sản</span>
                        @else
                            <span id="pub-detail-star" style="background: #fef3c7; color: #92400e; font-size: 0.73rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; border: 1px solid #fde68a;">⭐ 4 sao</span>
                            <span style="background: #ecfdf5; color: #065f46; font-size: 0.73rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; border: 1px solid #a7f3d0;">🛡️ Đạt chuẩn ATTP</span>
                        @endif
                    </div>

                    <h3 id="pub-detail-name" style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0 0 8px 0; line-height: 1.35; word-break: break-word; overflow-wrap: anywhere;"></h3>

                    <div style="font-size: 1.5rem; font-weight: 900; color: #0284c7; margin-bottom: 14px; display: flex; align-items: baseline; gap: 4px;">
                        <span id="pub-detail-price"></span>
                        <span id="pub-detail-unit" style="font-size: 0.88rem; font-weight: 700; color: #64748b;"></span>
                    </div>

                    <div id="pub-detail-origin-box" style="display: none; font-size: 0.83rem; color: #475569; background: #f0f9ff; padding: 12px 16px; border-radius: 12px; border: 1px solid #bae6fd; word-break: break-word; overflow-wrap: anywhere;">
                        <div style="font-weight: 800; color: #0369a1; margin-bottom: 3px; display: flex; align-items: center; gap: 6px;">
                            <span>📍</span> Nguồn gốc / Xuất xứ:
                        </div>
                        <div id="pub-detail-origin" style="color: #0f172a; font-weight: 700; font-size: 0.88rem;"></div>
                    </div>
                </div>
            </div>

            <!-- Description Block -->
            <div style="margin-top: 22px; border-top: 1px solid #f1f5f9; padding-top: 18px;">
                <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0369a1; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <span>📝</span> Mô tả chi tiết sản phẩm
                </div>
                <div id="pub-detail-description" style="font-size: 0.92rem; color: #334155; line-height: 1.65; background: #f8fafc; padding: 16px 18px; border-radius: 14px; border: 1px solid #e2e8f0; max-height: 180px; overflow-y: auto; word-break: break-word; overflow-wrap: anywhere; white-space: pre-line;"></div>
            </div>

            <!-- Actions -->
            <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end; align-items: center;">
                <button type="button" onclick="closePublicDetailModal()" style="height: 42px; padding: 0 24px; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; font-weight: 700; font-size: 0.88rem; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background='#ffffff'">
                    Đóng
                </button>
                @if(!$isCultureMarket)
                    <button type="button" onclick="orderFromPublicModal()" style="height: 42px; padding: 0 24px; border-radius: 12px; border: none; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; font-weight: 800; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 16px rgba(2,132,199,0.35); display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                        <i class="bi bi-cart-plus-fill" style="font-size: 1.05rem;"></i> Thêm vào giỏ hàng
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     LIGHTBOX POPUP PHÓNG TO ẢNH TRÊN CÙNG TRANG
     ============================================================ -->
<div id="pub-img-lightbox-modal" style="display: none; position: fixed; inset: 0; z-index: 20000; background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(14px); align-items: center; justify-content: center; flex-direction: column;" onclick="closeImageLightbox(event)">
    <button class="lb-close" onclick="closeImageLightbox(event)" style="position: absolute; top: 20px; right: 24px; background: rgba(255,255,255,0.15); border: none; color: #fff; width: 44px; height: 44px; border-radius: 50%; font-size: 1.6rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 20005; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>

    <div style="position: relative; max-width: 90vw; max-height: 82vh; display: flex; align-items: center; justify-content: center;" onclick="event.stopPropagation()">
        <img id="pub-lb-img" src="" alt="Ảnh sản phẩm phóng to" style="max-width: 90vw; max-height: 80vh; border-radius: 16px; object-fit: contain; box-shadow: 0 25px 50px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.2);">

        <!-- Lightbox Prev -->
        <button id="pub-lb-prev" onclick="changeLightboxImage(-1)" style="display: none; position: absolute; left: -20px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.7); color: #fff; border: 1px solid rgba(255,255,255,0.3); width: 44px; height: 44px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; align-items: center; justify-content: center; z-index: 20005;" onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='rgba(0,0,0,0.7)'">❮</button>

        <!-- Lightbox Next -->
        <button id="pub-lb-next" onclick="changeLightboxImage(1)" style="display: none; position: absolute; right: -20px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.7); color: #fff; border: 1px solid rgba(255,255,255,0.3); width: 44px; height: 44px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; align-items: center; justify-content: center; z-index: 20005;" onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='rgba(0,0,0,0.7)'">❯</button>
    </div>

    <!-- Lightbox Counter -->
    <div id="pub-lb-counter" style="margin-top: 16px; color: rgba(255,255,255,0.9); font-size: 0.9rem; font-weight: 700; background: rgba(0,0,0,0.5); padding: 4px 16px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.15);"></div>
</div>

@endsection
