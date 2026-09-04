@extends('layouts.app')

@section('title', 'Cổng Chợ Số ' . $eatery->name . ' - DongAnh Map Discovery')

@section('meta_description', 'Nền tảng quản lý Chợ số hiện đại tại ' . $eatery->name . ', Đông Anh. Tích hợp thanh toán số VietQR, sơ đồ phân khu tương tác, thông tin nông sản sạch và ATTP.')

<!-- SEO Keywords Đa Chiều -->
@section('meta_keywords', \App\Helpers\VietnameseSeoHelper::generateKeywords($eatery->name, 'traditional-market', $eatery->commune?->name ?? 'Đông Anh'))
@section('canonical_url', route('eatery.show', $eatery->slug))

@section('og_image', $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80')

@section('seo_schema')
    {!! $jsonLd !!}
@endsection

@section('content')
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- AOS Library CDN for Scroll Animations -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<!-- Leaflet MarkerCluster CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

@php
    $products = $eatery->ocopProducts;
    $groupedStalls = $products->groupBy('stall_name');
    $sellerUsersMap = \App\Models\User::whereIn('id', $products->pluck('user_id')->filter())->get()->keyBy('id');
    $sellerPhoneUsersMap = \App\Models\User::whereIn('phone', $products->pluck('seller_phone')->filter())->get()->keyBy('phone');
    
    $stallReviews = \App\Models\Review::on($eatery->getConnectionName())->where('eatery_id', $eatery->id)
        ->whereNotNull('stall_name')
        ->latest()
        ->get()
        ->groupBy('stall_name');
    
    $totalStalls = $groupedStalls->count();
    $totalProducts = $products->count();
    
    $stallsWithQr = 0;
    $stallsWithBank = 0;
    $stallsWithSmartphone = 0;
    
    $categoriesCount = [
        'Ăn uống' => 0,
        'Rau củ' => 0,
        'Thực phẩm khô' => 0,
        'Thịt tươi' => 0,
        'Khác' => 0
    ];
    
    $originsCount = [];

    foreach ($groupedStalls as $name => $stallProducts) {
        $first = $stallProducts->first();
        $desc = $first->description ?? '';
        $sUser = $first->user_id ? ($sellerUsersMap[$first->user_id] ?? null) : null;
        
        $hasBank = (!empty($first->bank_account) || !empty($first->bank_name) || ($sUser && !empty($sUser->bank_account)) || str_contains($desc, 'ngân hàng')) && !str_contains($desc, 'ngân hàng tiền mặt');
        $hasQr = (!empty($first->qr_code_path) || !empty($first->bank_account) || ($sUser && (!empty($sUser->bank_account) || !empty($sUser->qr_code))) || str_contains($desc, 'VietQR') || str_contains($desc, 'mã QR') || $hasBank);
        $hasSmartphone = (!empty($first->seller_phone) || ($sUser && !empty($sUser->phone)) || str_contains($desc, 'Có sử dụng smartphone') || str_contains($desc, 'Có sử dụng điện thoại thông minh'));
        
        if ($hasQr) $stallsWithQr++;
        if ($hasBank) $stallsWithBank++;
        if ($hasSmartphone) $stallsWithSmartphone++;
        
        $cat = 'Khác';
        if (str_contains($name, 'Ăn uống') || str_contains($name, 'Ăn sáng') || str_contains($name, 'Ẩm thực')) {
            $cat = 'Ăn uống';
        } elseif (str_contains($name, 'Rau củ') || str_contains($name, 'Rau')) {
            $cat = 'Rau củ';
        } elseif (str_contains($name, 'Thực phẩm khô') || str_contains($name, 'Hoa quả')) {
            $cat = 'Thực phẩm khô';
        } elseif (str_contains($name, 'Thịt') || str_contains($name, 'Giò chả')) {
            $cat = 'Thịt tươi';
        }
        $categoriesCount[$cat]++;
        
        foreach ($stallProducts as $prod) {
            $origin = 'Khác';
            if (preg_match('/Nguồn gốc: (.*?)\./', $prod->description, $match)) {
                $origin = trim($match[1]);
            }
            if (!isset($originsCount[$origin])) {
                $originsCount[$origin] = 0;
            }
            $originsCount[$origin]++;
        }
    }
    
    $qrPercentage       = $totalStalls > 0 ? round(($stallsWithQr / $totalStalls) * 100) : 0;
    $bankPercentage     = $totalStalls > 0 ? round(($stallsWithBank / $totalStalls) * 100) : 0;
    $phonePercentage    = $totalStalls > 0 ? round(($stallsWithSmartphone / $totalStalls) * 100) : 0;
    // Cashless rate = % stalls that have any digital payment (QR or bank link)
    $stallsWithCashless = max($stallsWithQr, $stallsWithBank);
    $cashlessPercentage = $totalStalls > 0 ? round(($stallsWithCashless / $totalStalls) * 100) : 0;

    // Helper function for media URL formatting
    $formatMediaUrl = function($url) {
        if (!$url) return null;
        if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) return $url;
        return asset(ltrim($url, '/'));
    };

    // Build media gallery list
    $allMedia = [];

    // 1. Ảnh đại diện chính của cơ sở
    if ($eatery->image_path) {
        $url = $formatMediaUrl($eatery->image_path);
        if ($url) $allMedia[] = ['type' => 'image', 'url' => $url];
    }

    // 2. Ảnh gallery do Admin/Chủ sạp tải lên trong Quản trị cơ sở (EateryPhoto)
    $eateryPhotos = $eatery->relationLoaded('photos') ? $eatery->photos : collect();
    foreach ($eateryPhotos as $photo) {
        if ($photo->image_path) {
            $url = $formatMediaUrl($photo->image_path);
            if ($url) $allMedia[] = ['type' => 'image', 'url' => $url, 'caption' => $photo->caption];
        }
    }

    // 3. Ảnh check-in từ người dùng
    if (isset($checkinPhotos)) {
        foreach ($checkinPhotos as $photo) {
            if ($photo->image_path) {
                $url = $formatMediaUrl($photo->image_path);
                if ($url) $allMedia[] = ['type' => 'image', 'url' => $url];
            }
        }
    }

    // 4. Ảnh từ các bài đánh giá của khách hàng
    if ($eatery->reviews) {
        foreach ($eatery->reviews as $rev) {
            if ($rev->media) {
                foreach ($rev->media as $m) {
                    if ($m->file_path) {
                        $url = $formatMediaUrl($m->file_path);
                        if ($url) $allMedia[] = ['type' => $m->file_type, 'url' => $url];
                    }
                }
            }
        }
    }

    // 5. Fallback nếu chưa đủ 5 ảnh
    $fallbacks = [
        ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80'],
        ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80'],
        ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1488459718432-01055e67e1f5?auto=format&fit=crop&w=800&q=80'],
        ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1506484381205-f7945653044d?auto=format&fit=crop&w=800&q=80'],
        ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=800&q=80']
    ];
    $fallbackIndex = 0;
    while (count($allMedia) < 5) {
        $allMedia[] = $fallbacks[$fallbackIndex % count($fallbacks)];
        $fallbackIndex++;
    }
@endphp

<style>
    /* Premium Smart City Style Sheet - Integrated with App Design Language */
    @keyframes highlightStallGlow {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.7); outline: 2.5px solid #0ea5e9; }
        50% { transform: scale(1.02); box-shadow: 0 0 30px 8px rgba(14, 165, 233, 0.4); outline: 3px solid #0ea5e9; }
        100% { transform: scale(1); box-shadow: 0 4px 20px rgba(0,0,0,0.05); outline: 2.5px solid transparent; }
    }
    .highlight-stall-card {
        animation: highlightStallGlow 2.5s ease-in-out forwards;
        border-color: #0ea5e9 !important;
    }

    html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
        width: 100% !important;
    }

    body {
        font-family: 'Be Vietnam Pro', sans-serif !important;
        background-color: var(--bg-base) !important;
        color: var(--text-main) !important;
    }
    
    a {
        text-decoration: none !important;
        color: inherit;
    }

    /* Core Layout & Structure */
    .market-container {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 24px;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .market-container {
            padding: 0 12px;
        }
    }

    /* Modern Airbnb-style Gallery Grid styles */
    .hero-gallery-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        grid-template-rows: 220px 220px;
        gap: 12px;
        border-radius: 24px;
        overflow: hidden;
        position: relative;
    }
    .gallery-item {
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .gallery-item img, .gallery-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .gallery-item:hover img, .gallery-item:hover video {
        transform: scale(1.04);
    }
    .gallery-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.1);
        opacity: 0;
        transition: opacity 0.3s;
    }
    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }
    
    /* Premium Gallery Focus Hover effect */
    .hero-gallery-grid:hover .gallery-item:not(:hover) {
        filter: brightness(0.85) grayscale(0.1);
        opacity: 0.9;
    }

    @media (max-width: 768px) {
        .hero-gallery-grid {
            grid-template-columns: 1fr;
            grid-template-rows: 280px;
        }
        .gallery-item:not(:first-child) {
            display: none;
        }
    }

    /* Cohesive Glassmorphic Containers extending detail-section */
    .premium-panel {
        background: var(--bg-card) !important;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border-glow) !important;
        border-radius: 24px !important;
        padding: 30px !important;
        box-shadow: var(--shadow-main) !important;
        transition: all 0.4s var(--ease-premium) !important;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .premium-panel:hover {
        border-color: var(--border-glow-hover) !important;
        box-shadow: var(--shadow-hover) !important;
        transform: translateY(-2px);
    }

    /* Subtle Smart City Blueprint Grid dot overlay background */
    .blueprint-grid {
        background-image: radial-gradient(rgba(14, 165, 233, 0.07) 1.5px, transparent 1.5px);
        background-size: 16px 16px;
        border: 1px dashed rgba(14, 165, 233, 0.15) !important;
    }

    /* Metric panel corner highlights */
    .premium-panel-metric::after {
        content: '';
        position: absolute;
        top: -40%;
        right: -40%;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, rgba(14, 165, 233, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .premium-panel-success::after {
        background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0) 70%);
    }
    .premium-panel-danger::after {
        background: radial-gradient(circle, rgba(239, 68, 68, 0.08) 0%, rgba(239, 68, 68, 0) 70%);
    }
    .premium-panel-warning::after {
        background: radial-gradient(circle, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0) 70%);
    }

    /* Small stat badges */
    .hero-stat-pill {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-glow) !important;
        border-radius: 16px !important;
        padding: 12px 20px !important;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02) !important;
        transition: all 0.3s var(--ease-premium) !important;
    }
    .hero-stat-pill:hover {
        border-color: var(--primary) !important;
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.12) !important;
        transform: translateY(-3px) !important;
    }
    .hero-stat-num {
        font-family: var(--font-heading);
        font-size: 1.45rem;
        font-weight: 900;
        color: var(--primary);
    }
    .hero-stat-lbl {
        font-size: 0.72rem;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Premium Smart City - Metrics & Dashboard cards styles */
    .metric-card-premium {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-glow) !important;
        border-radius: 24px !important;
        padding: 24px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02), 0 8px 16px -6px rgba(0, 0, 0, 0.02) !important;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 180px;
    }

    .metric-card-premium:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.08) !important;
    }

    .theme-blue:hover {
        border-color: rgba(59, 130, 246, 0.4) !important;
        box-shadow: 0 20px 35px -10px rgba(59, 130, 246, 0.15) !important;
    }

    .theme-emerald:hover {
        border-color: rgba(16, 185, 129, 0.4) !important;
        box-shadow: 0 20px 35px -10px rgba(16, 185, 129, 0.15) !important;
    }

    .theme-orange {
        border: 1px dashed rgba(245, 158, 11, 0.4) !important;
        background: linear-gradient(135deg, var(--bg-card) 0%, rgba(245, 158, 11, 0.02) 100%) !important;
    }

    .theme-orange:hover {
        border-color: rgba(245, 158, 11, 0.6) !important;
        box-shadow: 0 20px 35px -10px rgba(245, 158, 11, 0.12) !important;
        transform: translateY(-4px);
    }

    .metric-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.35rem;
        box-shadow: 0 6px 15px -3px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }

    .metric-card-premium:hover .metric-icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    .grad-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
    }

    .grad-emerald {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
    }

    .grad-orange {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    }

    .grad-indigo {
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%) !important;
    }

    .grad-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important;
    }

    .metric-progress-wrapper {
        margin-top: auto;
        padding-top: 14px;
    }

    /* Custom Responsive Grids */
    .top-db-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        position: relative;
        z-index: 10;
        padding-bottom: 40px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    .db-card-metric {
        font-family: var(--font-heading);
        font-size: 2.2rem;
        font-weight: 900;
        line-height: 1.1;
        color: var(--text-main);
        background: linear-gradient(135deg, var(--primary) 0%, #2563EB 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Charts Layout Grid */
    .charts-grid-custom {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    .chart-full-width {
        grid-column: 1 / -1;
    }
    @media (max-width: 992px) {
        .charts-grid-custom {
            grid-template-columns: 1fr;
        }
    }

    /* Interactive 2D Stall blocks */
    .interactive-grid-2d {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-top: 20px;
    }
    @media (max-width: 768px) {
        .interactive-grid-2d {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .interactive-block-2d {
        border-radius: 16px;
        padding: 24px 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.35s var(--ease-premium);
        border: 2px solid var(--border-glow);
        position: relative;
        background: var(--bg-base);
    }
    .interactive-block-2d.zone-a { border-color: rgba(244, 63, 94, 0.25); color: #f43f5e; }
    .interactive-block-2d.zone-b { border-color: rgba(16, 185, 129, 0.25); color: #10b981; }
    .interactive-block-2d.zone-c { border-color: rgba(245, 158, 11, 0.25); color: #f59e0b; }
    .interactive-block-2d.zone-d { border-color: rgba(59, 130, 246, 0.25); color: #3b82f6; }

    .interactive-block-2d:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary);
    }
    
    .interactive-block-2d.active {
        box-shadow: 0 0 20px rgba(14, 165, 233, 0.25);
        border-color: var(--primary);
        border-width: 3px;
        transform: translateY(-4px);
    }

    /* Filters Layout Grid */
    .filters-grid-custom {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .filter-item-wrapper {
        display: flex;
        flex-direction: column;
    }
    .custom-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 6px;
    }
    .custom-input, .custom-select {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-glow) !important;
        color: var(--text-main) !important;
        border-radius: 12px !important;
        padding: 10px 14px !important;
        outline: none;
        transition: all 0.3s;
        width: 100%;
        font-size: 0.9rem;
    }
    .custom-input:focus, .custom-select:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
    }
    .custom-input-group {
        display: flex;
        position: relative;
        align-items: center;
    }
    .custom-input-icon {
        position: absolute;
        left: 14px;
        color: var(--text-muted);
        font-size: 0.95rem;
    }
    .custom-input-with-icon {
        padding-left: 38px !important;
    }

    /* Stalls Grid Layout */
    .stalls-grid-custom {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    @media (max-width: 1200px) {
        .stalls-grid-custom {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }
    @media (max-width: 768px) {
        .stalls-grid-custom {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }

    /* Stalls cards custom styles */
    .stall-card-wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }
    .stall-card-gov {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-glow) !important;
        border-radius: 24px !important;
        overflow: hidden !important;
        box-shadow: 0 10px 30px -15px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }
    
    /* Apple glass card edge styling */
    .stall-card-gov::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 24px;
        padding: 1.5px;
        background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }

    .stall-card-gov:hover {
        transform: translateY(-8px) scale(1.01) !important;
        box-shadow: 0 20px 40px -15px rgba(14, 165, 233, 0.15) !important;
        border-color: rgba(14, 165, 233, 0.3) !important;
    }

    .stall-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-heading);
        font-size: 1rem;
        font-weight: 900;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        border: 2px solid rgba(255,255,255,0.4);
        flex-shrink: 0;
    }

    /* Badges pills - uniform single row */
    .gov-badge {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 5px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    .badge-qr-green { background: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.15) !important; }
    .badge-attp-blue { background: rgba(37, 99, 235, 0.08) !important; color: #2563eb !important; border: 1px solid rgba(37, 99, 235, 0.15) !important; }
    .badge-ocop-orange { background: rgba(245, 158, 11, 0.08) !important; color: #f59e0b !important; border: 1px solid rgba(245, 158, 11, 0.15) !important; }
    .badge-verify-sky { background: rgba(14, 165, 233, 0.08) !important; color: #0ea5e9 !important; border: 1px solid rgba(14, 165, 233, 0.15) !important; }
    .badge-home-purple { background: rgba(139, 92, 246, 0.08) !important; color: #8b5cf6 !important; border: 1px solid rgba(139, 92, 246, 0.15) !important; }

    /* Badges container - single row with overflow hidden */
    .stall-badges-row {
        padding: 12px 16px 0 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-height: 38px;
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
    }

    /* Product items list */
    .product-item-gov {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(14, 165, 233, 0.03);
        border: 1px solid rgba(14, 165, 233, 0.08);
        padding: 8px 12px;
        border-radius: 14px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
        min-height: 44px;
        gap: 6px;
        min-width: 0;
        width: 100%;
        box-sizing: border-box;
    }
    .product-item-gov:hover {
        background: rgba(14, 165, 233, 0.06);
        border-color: var(--primary);
    }
    .product-name-txt {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        min-width: 0;
    }
    .product-price-txt {
        font-family: var(--font-heading);
        font-size: 0.78rem;
        font-weight: 800;
        color: var(--primary);
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Products section - fixed height for uniform cards */
    .stall-products-section {
        padding: 14px 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 200px;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    /* Actions buttons - Floating Modern Capsules */
    .btn-stall-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
        padding: 0 16px 16px 16px;
        background: none;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }
    .btn-stall-action {
        background: rgba(14, 165, 233, 0.04);
        border: 1px solid var(--border-glow);
        color: var(--text-main);
        padding: 9px 2px;
        border-radius: 12px;
        text-align: center;
        font-weight: 700;
        font-size: 0.72rem;
        cursor: pointer;
        transition: all 0.2s var(--ease-premium);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        text-transform: uppercase;
        letter-spacing: 0;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        box-sizing: border-box;
    }
    .btn-stall-action:hover {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }

    @media (max-width: 576px) {
        .btn-stall-grid {
            gap: 4px;
            padding: 0 10px 14px 10px;
        }
        .btn-stall-action {
            padding: 8px 1px;
            font-size: 0.68rem;
        }
        .stall-products-section {
            padding: 12px 10px;
        }
        .stall-badges-row {
            padding: 10px 10px 0 10px;
        }
    }

    /* Pulsing dots indicators */
    .pulse-indicator-success {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
        animation: pulse-dot-simple 1.8s infinite;
        margin-right: 6px;
    }
    .pulse-indicator-pending {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #F59E0B;
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.5);
        animation: pulse-dot-pending-simple 1.8s infinite;
        margin-right: 6px;
    }
    
    @keyframes pulse-dot-simple {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
        70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    @keyframes pulse-dot-pending-simple {
        0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.5); }
        70% { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
        100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }

    /* Satellite Mini Map Wrapper */
    #miniMap {
        width: 100%;
        height: 380px;
        border-radius: 20px;
        border: 1px solid var(--border-glow);
        box-shadow: var(--shadow-main);
    }

    /* Bottom double cards info */
    .bottom-double-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 40px;
    }
    @media (max-width: 992px) {
        .bottom-double-grid {
            grid-template-columns: 1fr;
        }
    }

    .bottom-info-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    @media (max-width: 480px) {
        .bottom-info-list {
            grid-template-columns: 1fr;
        }
    }

    /* Timeline style */
    .gov-timeline {
        position: relative;
        border-left: 2px solid var(--border-glow);
        padding-left: 24px;
        margin-left: 12px;
    }
    .gov-timeline-item {
        position: relative;
        margin-bottom: 24px;
    }
    .gov-timeline-dot {
        position: absolute;
        left: -33px;
        top: 3px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 4px solid var(--primary);
        z-index: 2;
        box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.4);
        animation: pulse-dot 2s infinite;
    }
    .gov-timeline-dot.warning {
        border-color: var(--warning-brand);
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
        animation: pulse-dot-warning 2s infinite;
    }
    
    @keyframes pulse-dot {
        0% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(14, 165, 233, 0); }
        100% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0); }
    }
    @keyframes pulse-dot-warning {
        0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
        100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }

    /* Flowchart process flow */
    .flow-process {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 15px;
        position: relative;
    }
    .flow-step {
        text-align: center;
        z-index: 2;
        flex: 1;
    }
    .flow-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 2px solid var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin: 0 auto 10px;
        box-shadow: var(--shadow-main);
    }
    .flow-process::before {
        content: '';
        position: absolute;
        top: 26px;
        left: 12%;
        right: 12%;
        height: 2px;
        background: dashed var(--border-glow);
        z-index: 1;
    }

    /* Stall Detail Modal Lightbox overlay styles */
    .qr-lightbox {
        position: fixed !important;
        inset: 0 !important;
        background: rgba(15, 23, 42, 0.6) !important; /* Translucent dark backdrop overlay */
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
        z-index: 99999 !important;
        opacity: 0;
        transition: opacity 0.3s ease !important;
    }

    .qr-lightbox-content {
        background: var(--bg-card) !important;
        backdrop-filter: blur(30px) saturate(180%);
        -webkit-backdrop-filter: blur(30px) saturate(180%);
        border: 1px solid var(--border-glow) !important;
        box-shadow: 0 30px 70px rgba(0, 0, 0, 0.3) !important;
        transform: scale(0.92) translateY(30px);
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease !important;
        width: 90% !important;
        max-width: 720px !important;
        border-radius: 32px !important;
        overflow: hidden !important;
        position: relative;
    }

    /* Premium Merchant details card */
    .merchant-info-card {
        background: rgba(14, 165, 233, 0.015);
        border: 1px solid var(--border-glow);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 24px;
        transition: all 0.3s;
    }
    .merchant-info-card:hover {
        border-color: rgba(14, 165, 233, 0.25);
        background: rgba(14, 165, 233, 0.03);
    }
    .merchant-info-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        font-size: 0.85rem;
    }
    .merchant-info-row:last-child {
        margin-bottom: 0;
    }
    .merchant-info-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(14, 165, 233, 0.08);
        color: var(--primary);
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    /* Modern modal product rows */
    .modal-product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(14, 165, 233, 0.02);
        border: 1px solid var(--border-glow);
        padding: 12px 18px;
        border-radius: 16px;
        transition: all 0.3s;
    }
    .modal-product-item:hover {
        transform: translateX(4px);
        border-color: var(--primary);
        background: rgba(14, 165, 233, 0.05);
    }

    /* High-tech flowchart */
    .flow-process-step {
        text-align: center;
        flex-shrink: 0;
    }
    .flow-process-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 2px solid var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin: 0 auto 8px;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.12);
        transition: all 0.3s var(--ease-premium);
    }
    .flow-process-icon-wrap:hover {
        transform: translateY(-4px) scale(1.06);
        border-color: #2563EB;
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.25);
    }
    .flow-process-line {
        flex: 1;
        height: 2px;
        background: dashed var(--border-glow);
        margin-bottom: 24px;
        position: relative;
    }
    .flow-process-line::after {
        content: '➔';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: var(--text-muted);
        font-size: 0.8rem;
    }

    /* Bank info badge */
    .bank-info-badge {
        background: rgba(14, 165, 233, 0.05);
        border: 1px solid rgba(14, 165, 233, 0.15);
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 12px;
        text-align: center;
        display: inline-block;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    /* Custom Scrollbar for Modal content */
    .qr-lightbox-content > div[style*="overflow-y: auto"]::-webkit-scrollbar {
        width: 6px;
    }
    .qr-lightbox-content > div[style*="overflow-y: auto"]::-webkit-scrollbar-track {
        background: transparent;
    }
    .qr-lightbox-content > div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb {
        background: rgba(14, 165, 233, 0.2);
        border-radius: 10px;
    }
    .qr-lightbox-content > div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb:hover {
        background: rgba(14, 165, 233, 0.4);
    }
    /* High-tech QR scanning scanner glow laser line */
    .qr-scanner-box {
        position: relative;
        overflow: hidden;
        background: #ffffff;
        border: 2px solid var(--border-glow);
        border-radius: 24px;
        padding: 14px;
        width: 190px;
        height: 190px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.08);
        transition: border-color 0.3s;
        margin-bottom: 14px;
    }
    .qr-scanner-box:hover {
        border-color: var(--primary);
    }
    .qr-scanner-line {
        position: absolute;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(to right, transparent, var(--primary), transparent);
        box-shadow: 0 0 8px var(--primary);
        z-index: 10;
        animation: scanline 2.5s infinite linear;
        pointer-events: none;
    }
    @keyframes scanline {
        0% { top: 5%; }
        50% { top: 95%; }
        100% { top: 5%; }
    }
    
    /* Leaflet map markers classes */
    .stall-map-marker {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        color: white;
        transition: all 0.2s var(--ease-premium);
        cursor: pointer;
    }
    .stall-map-marker:hover {
        transform: scale(1.2) translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        z-index: 1000 !important;
    }
    @keyframes pulse-stall {
        0% { box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.6); transform: scale(1); }
        70% { box-shadow: 0 0 0 12px rgba(225, 29, 72, 0); transform: scale(1.08); }
        100% { box-shadow: 0 0 0 0 rgba(225, 29, 72, 0); transform: scale(1); }
    }
    .pulse-marker {
        animation: pulse-stall 1.6s infinite ease-in-out;
    }
    
    /* Fly to cart animations */
    .flying-cart-item {
        position: fixed;
        z-index: 999999;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
        pointer-events: none;
        transition: all 0.9s cubic-bezier(0.16, 1, 0.3, 1);
        border: 2px solid white;
    }
    @keyframes cart-bounce-wiggle {
        0% { transform: scale(1); }
        20% { transform: scale(1.3) rotate(-15deg); }
        40% { transform: scale(1.3) rotate(15deg); }
        60% { transform: scale(1.3) rotate(-10deg); }
        80% { transform: scale(1.3) rotate(10deg); }
        100% { transform: scale(1); }
    }
    .cart-wiggle {
        animation: cart-bounce-wiggle 0.7s ease-in-out !important;
        transform-origin: center;
    }
</style>

<!-- Top Media Slider/Gallery Grid (Replacing background image banner) -->
<div class="market-container" style="padding-top: 20px; margin-bottom: 24px;" data-aos="fade-down">
    <!-- Harmonious Modern Breadcrumb Navigation -->
    <nav class="integrated-breadcrumb-nav" aria-label="Breadcrumb">
        <a href="/" class="breadcrumb-item-link">
            <span style="font-size: 0.95rem;">🏠</span>
            <span>Trang chủ</span>
        </a>
        <span class="breadcrumb-arrow">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </span>
        <a href="{{ url('/?cat=' . $eatery->category->slug) }}" class="breadcrumb-item-link">
            <span style="font-size: 0.95rem;">{{ $eatery->category->icon ?: '🏪' }}</span>
            <span>{{ $eatery->category->name }}</span>
        </a>
        <span class="breadcrumb-arrow">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </span>
        <span class="breadcrumb-item-active">
            <span style="font-size: 0.95rem;">✨</span>
            <span>{{ $eatery->name }}</span>
        </span>
    </nav>
    <div class="hero-gallery-grid">
        <!-- Image 1: Main Large -->
        <div onclick="openHeroGalleryModal(0)" class="gallery-item" style="grid-column: 1; grid-row: 1 / 3;">
            @if($allMedia[0]['type'] === 'video')
                <video src="{{ $allMedia[0]['url'] }}" autoplay muted loop playsinline></video>
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.2);">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--primary);">▶</div>
                </div>
            @else
                <img src="{{ $allMedia[0]['url'] }}" alt="{{ $eatery->name }}">
            @endif
            <div class="gallery-overlay"></div>
        </div>
        
        <!-- Image 2 -->
        <div onclick="openHeroGalleryModal(1)" class="gallery-item">
            <img src="{{ $allMedia[1]['url'] }}">
            <div class="gallery-overlay"></div>
        </div>
        
        <!-- Image 3 -->
        <div onclick="openHeroGalleryModal(2)" class="gallery-item">
            <img src="{{ $allMedia[2]['url'] }}">
            <div class="gallery-overlay"></div>
        </div>
        
        <!-- Image 4 -->
        <div onclick="openHeroGalleryModal(3)" class="gallery-item">
            <img src="{{ $allMedia[3]['url'] }}">
            <div class="gallery-overlay"></div>
        </div>
        
        <!-- Image 5: Show all photos overlay -->
        <div onclick="openHeroGalleryModal(4)" class="gallery-item">
            @if($allMedia[4]['type'] === 'video')
                <video src="{{ $allMedia[4]['url'] }}" muted playsinline></video>
            @else
                <img src="{{ $allMedia[4]['url'] }}">
            @endif
            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; transition: background 0.3s;" onmouseover="this.style.background='rgba(0,0,0,0.35)'" onmouseout="this.style.background='rgba(0,0,0,0.45)'">
                <div style="text-align: center;">
                    <div style="font-size: 1.8rem; margin-bottom: 4px;">📸</div>
                    <span style="color: white; font-weight: 700; font-size: 1.05rem; display: block;">
                        Xem tất cả {{ count($allMedia) }} Ảnh/Video
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header Information Section (Below Media Gallery) -->
<div class="market-container" style="margin-bottom: 40px;" data-aos="fade-up">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 24px; border-bottom: 1px solid var(--border-glow); padding-bottom: 30px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap;">
                <span class="gov-badge" style="font-size: 0.78rem; padding: 6px 14px; background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(37, 99, 235, 0.08) 100%); color: #0284c7; border: 1px solid rgba(14, 165, 233, 0.2); font-weight: 700; border-radius: 20px;">
                    <i class="bi bi-shop me-1"></i> CHỢ SỐ 4.0 ĐÔNG ANH
                </span>
                <span class="gov-badge" style="font-size: 0.78rem; padding: 6px 14px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.08) 100%); color: #059669; border: 1px solid rgba(16, 185, 129, 0.2); font-weight: 700; border-radius: 20px;">
                    <i class="bi bi-shield-fill-check me-1"></i> ĐẠT CHUẨN ATTP
                </span>
            </div>
            <h1 style="font-family: var(--font-heading); font-size: 2.8rem; font-weight: 900; color: var(--text-main); margin: 0 0 8px 0; letter-spacing: -0.5px;">
                {{ $eatery->name }}
            </h1>
            <p style="font-size: 1.05rem; color: var(--text-muted); margin: 0; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                <i class="bi bi-geo-alt-fill text-danger"></i> {{ $eatery->address }}
            </p>
        </div>
        
        <!-- Info stat widgets row -->
        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <div class="hero-stat-pill">
                <i class="bi bi-shop text-primary" style="font-size: 1.25rem;"></i>
                <div>
                    <div class="hero-stat-num">{{ $totalStalls }}</div>
                    <div class="hero-stat-lbl">Hộ kinh doanh</div>
                </div>
            </div>
            <div class="hero-stat-pill">
                <i class="bi bi-box-seam" style="color: #6366f1; font-size: 1.25rem;"></i>
                <div>
                    <div class="hero-stat-num">{{ $totalProducts }}</div>
                    <div class="hero-stat-lbl">Mặt hàng</div>
                </div>
            </div>
            <div class="hero-stat-pill">
                <i class="bi bi-currency-exchange text-success" style="font-size: 1.25rem;"></i>
                <div>
                    <div class="hero-stat-num">95%</div>
                    <div class="hero-stat-lbl">Thanh toán số</div>
                </div>
            </div>
            <div class="hero-stat-pill">
                <i class="bi bi-qr-code-scan text-success" style="font-size: 1.25rem;"></i>
                <div>
                    <div class="hero-stat-num">{{ $qrPercentage }}%</div>
                    <div class="hero-stat-lbl">Có mã QR</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Container -->
<div class="market-container">

    <!-- IV. BẢN ĐỒ SỐ VỊ TRÍ KHÔNG GIAN -->
    <div class="premium-panel" data-aos="fade-up">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <span class="gov-badge badge-verify-sky">📍 ĐỊA ĐIỂM SỐ</span>
                <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; margin-top: 6px; margin-bottom: 0; color: var(--text-main);">Bản đồ Số Vị trí Không gian</h3>
            </div>
        </div>
        
        <div style="position: relative; border-radius: 20px; overflow: hidden; border: 1px solid var(--border-glow); box-shadow: var(--shadow-sm);">
            <!-- Overlay Button: Fullscreen -->
            <button onclick="toggleMapFullscreen()" style="position: absolute; top: 14px; right: 14px; z-index: 1000; background: rgba(255,255,255,0.92); backdrop-filter: blur(10px); border: 1px solid var(--border-glow); color: var(--text-main); padding: 8px 16px; border-radius: 12px; font-weight: 800; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='none';">
                <i class="bi bi-arrows-fullscreen" style="color: var(--primary);"></i> Xem toàn màn hình
            </button>

            <!-- Map Legend (Bảng Chú Giải) -->
            <div class="map-legend-overlay" style="position: absolute; bottom: 16px; left: 16px; z-index: 1000; background: rgba(255,255,255,0.92); backdrop-filter: blur(10px); border: 1px solid var(--border-glow); border-radius: 14px; padding: 10px 14px; box-shadow: 0 6px 18px rgba(0,0,0,0.12); font-family: 'Be Vietnam Pro', sans-serif;">
                <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">🗺️ Chú giải phân khu:</span>
                <div style="display: flex; gap: 12px; flex-wrap: wrap; font-size: 0.76rem; font-weight: 800; color: var(--text-main);">
                    <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; border-radius: 50%; background: #0EA5E9; display: inline-block;"></span> Khối A (Ẩm thực)</span>
                    <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; border-radius: 50%; background: #10B981; display: inline-block;"></span> Khối B (Rau củ)</span>
                    <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; border-radius: 50%; background: #F59E0B; display: inline-block;"></span> Khối C (Đồ khô)</span>
                    <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; border-radius: 50%; background: #EC4899; display: inline-block;"></span> Khối D (Thịt tươi)</span>
                </div>
            </div>

            <div id="miniMap" style="height: 480px; width: 100%; z-index: 1;"></div>
        </div>
    </div>

    <!-- V. BẢNG TIN SỐ BAN QUẢN LÝ CHỢ (LOA CHỢ SỐ) -->
    <div class="premium-panel" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.06) 0%, rgba(6, 182, 212, 0.03) 100%) !important; border: 1.5px solid rgba(14, 165, 233, 0.25) !important; position: relative;" data-aos="fade-up">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--primary-grad); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 8px 20px rgba(14, 165, 233, 0.35);">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <div>
                    <h3 style="font-family: var(--font-heading); font-weight: 900; font-size: 1.35rem; margin: 0 0 2px 0; color: var(--text-main); letter-spacing: -0.3px;">
                        📢 Bảng Tin Số Ban Quản Lý Chợ
                    </h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Thông báo &amp; Thông tin điều hành chính thức từ BQL {{ $eatery->name }}</span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); padding: 6px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);"></span>
                    LOA PHÁT TIN SỐ
                </span>
                @if($eatery->phone)
                <a href="tel:{{ $eatery->phone }}" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; padding: 8px 18px; border-radius: 12px; font-size: 0.82rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
                    <i class="bi bi-telephone-fill"></i> Hotline BQL: {{ $eatery->phone }}
                </a>
                @endif
            </div>
        </div>

        @php
            $announcements = json_decode($eatery->announcements ?? '[]', true);
            if (empty($announcements)) {
                $announcements = [
                    ['id' => 1, 'tag' => '🛡️ KIỂM ĐỊNH ATTP', 'time' => 'Mới cập nhật', 'title' => '100% sạp đạt chuẩn ATTP Tháng 7/2026', 'content' => 'Đoàn kiểm tra liên ngành đã nghiệm thu chất lượng nguồn gốc nông sản & vệ sinh quầy hàng.', 'color' => '#10B981'],
                    ['id' => 2, 'tag' => '🧼 VỆ SINH ĐỊNH KỲ', 'time' => '18h00 Chủ Nhật', 'title' => 'Lịch phun khử khuẩn toàn chợ', 'content' => 'BQL tiến hành dọn vệ sinh tổng thể & phun tiêu độc khử khuẩn định kỳ vào cuối tuần.', 'color' => '#0ea5e9'],
                    ['id' => 3, 'tag' => '🎪 SỰ KIỆN NÔNG SẢN', 'time' => 'Sáng Thứ 7', 'title' => 'Phiên Chợ Nông Sản Sạch Đông Anh', 'content' => 'Quy tụ các hợp tác xã nông sản sạch, rau VietGAP & OCOP giá ưu đãi tại Khối B.', 'color' => '#f59e0b']
                ];
            }
        @endphp

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
            @foreach($announcements as $ann)
            @php $itemColor = $ann['color'] ?? '#10B981'; @endphp
            <div onclick="openAnnouncementDetailModal('{{ addslashes($ann['tag'] ?? '📢 THÔNG BÁO') }}', '{{ addslashes($ann['time'] ?? 'Mới cập nhật') }}', '{{ addslashes($ann['title']) }}', '{{ addslashes($ann['content']) }}', '{{ $itemColor }}')"
                 style="background: var(--bg-card); border: 1px solid var(--border-glow); border-left: 4px solid {{ $itemColor }}; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 10px; transition: all 0.25s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.05); cursor: pointer; min-width: 0;"
                 onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 28px -6px {{ $itemColor }}40';"
                 onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 14px rgba(0,0,0,0.05)';">

                {{-- Header: icon + tag --}}
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 38px; height: 38px; min-width: 38px; border-radius: 10px; background: {{ $itemColor }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; box-shadow: 0 3px 8px {{ $itemColor }}55;">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <span style="display: inline-block; background: {{ $itemColor }}1f; color: {{ $itemColor }}; font-size: 0.68rem; font-weight: 800; padding: 2px 9px; border-radius: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ $ann['tag'] ?? '📢 THÔNG BÁO' }}</span>
                        <div style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">⏰ {{ $ann['time'] ?? 'Mới cập nhật' }}</div>
                    </div>
                </div>

                {{-- Title --}}
                <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); margin: 0; line-height: 1.35; word-break: break-word;">{{ $ann['title'] }}</h4>

                {{-- Content preview --}}
                <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word;">{{ $ann['content'] }}</p>

                {{-- CTA --}}
                <span style="font-size: 0.73rem; color: #0284c7; font-weight: 700; display: flex; align-items: center; gap: 4px;">
                    <i class="bi bi-zoom-in"></i> Nhấn để xem chi tiết...
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- V. SƠ ĐỒ PHÂN KHU TƯƠNG TÁC -->
    <div class="premium-panel blueprint-grid" data-aos="fade-up" style="background: var(--bg-card);">
        <span class="gov-badge badge-ocop-orange">🗺️ KHU VỰC CHỢ</span>
        <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; margin-top: 6px; margin-bottom: 8px; color: var(--text-main);">Sơ đồ Phân Khu Hộ Kinh Doanh</h3>
        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 24px;">Nhấp vào phân khu để di chuyển nhanh và làm nổi bật các quầy hàng thuộc phân khu tương ứng.</p>
        
        <div class="interactive-grid-2d">
            <div class="interactive-block-2d zone-a" id="block-A" onclick="filterByBlock('Ăn uống', 'A')">
                <span style="font-size: 1.8rem; display: block; margin-bottom: 6px;">🍲</span>
                <strong style="font-family: var(--font-heading); font-size: 1.1rem; display: block;">Khối A</strong>
                <span style="font-size: 0.82rem; font-weight: 700; display: block; margin-top: 4px;">Ẩm thực: {{ $categoriesCount['Ăn uống'] }} gian</span>
            </div>
            <div class="interactive-block-2d zone-b" id="block-B" onclick="filterByBlock('Rau củ', 'B')">
                <span style="font-size: 1.8rem; display: block; margin-bottom: 6px;">🥦</span>
                <strong style="font-family: var(--font-heading); font-size: 1.1rem; display: block;">Khối B</strong>
                <span style="font-size: 0.82rem; font-weight: 700; display: block; margin-top: 4px;">Rau củ: {{ $categoriesCount['Rau củ'] }} gian</span>
            </div>
            <div class="interactive-block-2d zone-c" id="block-C" onclick="filterByBlock('Thực phẩm khô', 'C')">
                <span style="font-size: 1.8rem; display: block; margin-bottom: 6px;">🥜</span>
                <strong style="font-family: var(--font-heading); font-size: 1.1rem; display: block;">Khối C</strong>
                <span style="font-size: 0.82rem; font-weight: 700; display: block; margin-top: 4px;">Đồ khô: {{ $categoriesCount['Thực phẩm khô'] }} gian</span>
            </div>
            <div class="interactive-block-2d zone-d" id="block-D" onclick="filterByBlock('Thịt tươi', 'D')">
                <span style="font-size: 1.8rem; display: block; margin-bottom: 6px;">🥩</span>
                <strong style="font-family: var(--font-heading); font-size: 1.1rem; display: block;">Khối D</strong>
                <span style="font-size: 0.82rem; font-weight: 700; display: block; margin-top: 4px;">Thịt tươi: {{ $categoriesCount['Thịt tươi'] }} gian</span>
            </div>
        </div>
    </div>

    <!-- IX. THANH TÌM KIẾM NÂNG CAO & BỘ LỌC REALTIME -->
    <div class="premium-panel" data-aos="fade-up">
        <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.25rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i class="bi bi-sliders" style="color: var(--primary);"></i> Bộ Lọc & Tìm Kiếm Nâng Cao
        </h3>
        
        <div class="filters-grid-custom">
            <div class="filter-item-wrapper">
                <label class="custom-label">Tên chủ hộ / quầy hàng</label>
                <div class="custom-input-group">
                    <span class="custom-input-icon"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchName" oninput="applyFilters()" class="custom-input custom-input-with-icon" placeholder="Nhập tên hộ hoặc quầy...">
                </div>
            </div>
            
            <div class="filter-item-wrapper">
                <label class="custom-label">Tên mặt hàng/sản phẩm</label>
                <div class="custom-input-group">
                    <span class="custom-input-icon"><i class="bi bi-bag"></i></span>
                    <input type="text" id="searchProduct" oninput="applyFilters()" class="custom-input custom-input-with-icon" placeholder="Ví dụ: bún riêu, cà chua...">
                </div>
            </div>
            
            <div class="filter-item-wrapper">
                <label class="custom-label">Ngành hàng kinh doanh</label>
                <select id="filterCategory" onchange="applyFilters()" class="custom-select">
                    <option value="">Tất cả ngành hàng</option>
                    <option value="Ăn uống">🍲 Khối A - Ẩm thực</option>
                    <option value="Rau củ">🥦 Khối B - Rau củ quả</option>
                    <option value="Thực phẩm khô">🥜 Khối C - Đồ khô & Gia vị</option>
                    <option value="Thịt tươi">🥩 Khối D - Thịt tươi sống</option>
                </select>
            </div>
            
            <div class="filter-item-wrapper">
                <label class="custom-label">Thanh toán VietQR</label>
                <select id="filterQr" onchange="applyFilters()" class="custom-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="yes">✓ Hỗ trợ VietQR</option>
                    <option value="no">✕ Chưa triển khai QR</option>
                </select>
            </div>
            
                    <div class="filter-item-wrapper">
                <label class="custom-label">Nguồn gốc hàng hóa</label>
                <select id="filterOrigin" onchange="applyFilters()" class="custom-select">
                    <option value="">Tất cả nguồn gốc</option>
                    <option value="Tự sản xuất">🌾 Hộ gia đình tự sản xuất</option>
                    <option value="trong làng">🏡 Mua nông sản trong làng</option>
                    <option value="Chợ Tó">🏛️ Chợ Tó Cổ Loa</option>
                    <option value="Long Biên">🍏 Chợ đầu mối Long Biên</option>
                </select>
            </div>
            
            <div class="filter-item-wrapper">
                <label class="custom-label">Sử dụng Smartphone</label>
                <select id="filterPhone" onchange="applyFilters()" class="custom-select">
                    <option value="">Tất cả</option>
                    <option value="yes">✓ Có sử dụng</option>
                    <option value="no">✕ Không sử dụng</option>
                </select>
            </div>

            <div class="filter-item-wrapper" style="justify-content: flex-end;">
                <button onclick="resetAdvancedFilters()" style="background: rgba(0,0,0,0.05); border: 1px solid var(--border-glow); color: var(--text-main); font-weight: 700; border-radius: 12px; padding: 10px 14px; cursor: pointer; transition: all 0.2s;">
                    ✕ Xóa bộ lọc
                </button>
            </div>
        </div>
    </div>

    <!-- VI. DANH SÁCH SẢN PHẨM TRƯNG BÀY -->
    @php
        $isCultureMarket = str_contains(strtolower($eatery->slug ?? ''), 'van-hoa-du-lich') || str_contains(strtolower($eatery->name ?? ''), 'văn hóa du lịch');
    @endphp

    <div style="margin-top: 40px; margin-bottom: 40px;">
        @if($isCultureMarket)
            <h2 style="font-family: var(--font-heading); font-weight: 900; font-size: clamp(1.3rem, 5vw, 1.8rem); margin-bottom: 6px; color: var(--text-main); word-break: break-word;">
                🏛️ Danh Mục Sản Phẩm & Đặc Sản Trưng Bày — {{ $eatery->name }}
            </h2>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 24px;">
                Không gian triển lãm, giới thiệu sản phẩm đặc sản, quà lưu niệm & tinh hoa di sản vùng đất Cổ Loa.
            </p>
        @else
            <h2 style="font-family: var(--font-heading); font-weight: 900; font-size: clamp(1.3rem, 5vw, 1.8rem); margin-bottom: 24px; color: var(--text-main); word-break: break-word;">
                🏪 Hệ thống Gian Hàng Số {{ $eatery->name }}
            </h2>
        @endif
        
        <div class="stalls-grid-custom" id="stallsContainer">
            @foreach($groupedStalls as $stallName => $stallProducts)
                @php
                    $first = $stallProducts->first();
                    $displayStallName = !empty(trim($stallName)) ? $stallName : ($first->name ?: 'Gian hàng trưng bày');
                    $safeStallSlug = \Illuminate\Support\Str::slug($displayStallName) ?: ('gian-hang-' . ($first->id ?? '1'));
                    $sellerUser = $first->user_id ? ($sellerUsersMap[$first->user_id] ?? null) : null;
                    $sellerName = $first->seller_name ?: ($sellerUser?->name ?: 'Ban Quản lý Chợ Cổ Loa');
                    $sellerPhone = $first->seller_phone ?: ($sellerUser?->phone ?: '');
                    $desc = $first->description ?? '';
                    
                    $hasBank = (!empty($first->bank_account) || !empty($first->bank_name) || ($sellerUser && !empty($sellerUser->bank_account)) || str_contains($desc, 'ngân hàng')) && !str_contains($desc, 'ngân hàng tiền mặt');
                    $hasQr = (!empty($first->qr_code_path) || !empty($first->bank_account) || ($sellerUser && (!empty($sellerUser->bank_account) || !empty($sellerUser->qr_code))) || str_contains($desc, 'VietQR') || str_contains($desc, 'mã QR') || $hasBank);
                    $hasSmartphone = (!empty($sellerPhone) || ($sellerUser && !empty($sellerUser->phone)) || str_contains($desc, 'Có sử dụng smartphone') || str_contains($desc, 'Có sử dụng điện thoại thông minh'));
                    
                    $category = 'Khác';
                    if (str_contains($stallName, 'Ăn uống') || str_contains($stallName, 'Ăn sáng') || str_contains($stallName, 'Ẩm thực')) {
                        $category = 'Ăn uống';
                    } elseif (str_contains($stallName, 'Rau củ') || str_contains($stallName, 'Rau')) {
                        $category = 'Rau củ';
                    } elseif (str_contains($stallName, 'Thực phẩm khô') || str_contains($stallName, 'Hoa quả')) {
                        $category = 'Thực phẩm khô';
                    } elseif (str_contains($stallName, 'Thịt') || str_contains($stallName, 'Giò chả')) {
                        $category = 'Thịt tươi';
                    }
                    
                    $bankInfo = '';
                    if ($sellerUser && !empty($sellerUser->bank_account)) {
                        $bankInfo = $sellerUser->bank_account . ($sellerUser->bank_name ? " ({$sellerUser->bank_name})" : '');
                    } elseif (preg_match('/ngân hàng (.*?)\./i', $desc, $matches)) {
                        $bankInfo = $matches[1];
                    } elseif (!empty($first->bank_account)) {
                        $bankInfo = $first->bank_account . ($first->bank_name ? " ({$first->bank_name})" : '');
                    }
                    
                    $originText = 'Tự sản xuất';
                    if (preg_match('/Nguồn gốc: (.*?)\./i', $desc, $match)) {
                        $originText = trim($match[1]);
                    }

                    $thisStallReviews = $stallReviews->get($stallName) ?? collect();
                    $reviewCount = $thisStallReviews->count();
                    $avgRating = $reviewCount > 0 ? round($thisStallReviews->avg('rating'), 1) : 5.0;
                    $latestReview = $reviewCount > 0 ? $thisStallReviews->first()->comment : 'Sản phẩm trưng bày tuyệt đẹp, mang đậm bản sắc di sản Cổ Loa!';
                @endphp
                
                <div class="stall-card-wrapper" 
                     id="stall-card-{{ $safeStallSlug }}"
                     data-name="{{ strtolower($stallName) }}" 
                     data-seller="{{ strtolower($sellerName) }}"
                     data-category="{{ $category }}"
                     data-qr="{{ $hasQr ? 'yes' : 'no' }}"
                     data-phone="{{ $hasSmartphone ? 'yes' : 'no' }}"
                     data-origin="{{ strtolower($first->description) }}"
                     data-products="{{ strtolower($stallProducts->pluck('name')->implode(' ')) }}"
                     data-lat="{{ $first->latitude ?? '' }}"
                     data-lng="{{ $first->longitude ?? '' }}">
                     
                    <div class="stall-card-gov">
                        @php
                            // Ảnh 1: Ảnh đại diện cho sạp (Gian hàng cover banner)
                            $stallCustomImg = $first->image_url ?: $first->image_path;
                            if (!empty($stallCustomImg)) {
                                $trimmedCover = trim($stallCustomImg);
                                if (str_starts_with($trimmedCover, '[')) {
                                    $decodedCover = json_decode($trimmedCover, true);
                                    if (is_array($decodedCover) && count($decodedCover) > 0) {
                                        $trimmedCover = $decodedCover[0];
                                    }
                                }
                                if (str_starts_with($trimmedCover, 'http://') || str_starts_with($trimmedCover, 'https://')) {
                                    $coverImg = $trimmedCover;
                                } else {
                                    $coverImg = asset(ltrim($trimmedCover, '/'));
                                }
                            } else {
                                $coverImg = asset('images/stalls/food.png');
                                if ($category === 'Rau củ') {
                                    $coverImg = asset('images/stalls/veggies.png');
                                } elseif ($category === 'Thực phẩm khô' || str_contains($stallName, 'Hoa quả')) {
                                    $coverImg = asset('images/stalls/fruits.png');
                                } elseif ($category === 'Thịt tươi') {
                                    $coverImg = asset('images/stalls/meat.png');
                                }
                            }

                            // Ảnh 2: Ảnh đại diện của chủ hộ (Profile photo hoặc Badge chữ cái đầu)
                            $sellerUserObj = null;
                            if (!empty($first->user_id) && isset($sellerUsersMap[$first->user_id])) {
                                $sellerUserObj = $sellerUsersMap[$first->user_id];
                            } elseif (!empty($sellerPhone) && isset($sellerPhoneUsersMap[$sellerPhone])) {
                                $sellerUserObj = $sellerPhoneUsersMap[$sellerPhone];
                            }

                            $sellerAvatar = null;
                            if ($sellerUserObj && !empty($sellerUserObj->avatar)) {
                                $uAv = $sellerUserObj->avatar;
                                $userAv = (str_starts_with($uAv, 'http://') || str_starts_with($uAv, 'https://')) ? $uAv : asset(ltrim($uAv, '/'));
                                if ($userAv !== $coverImg) {
                                    $sellerAvatar = $userAv;
                                }
                            }
                        @endphp
                        
                        <!-- AI Cover Header Banner (Ảnh 1: Đại diện sạp) -->
                        <div style="position: relative; height: 125px; overflow: hidden; border-top-left-radius: 24px; border-top-right-radius: 24px;">
                            <img src="{{ $coverImg }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease;" class="stall-cover-img" alt="{{ $displayStallName }}">
                            <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(15, 23, 42, 0.15) 0%, rgba(15, 23, 42, 0.8) 100%);"></div>
                            
                            <div style="position: absolute; top: 10px; right: 10px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #ffffff; font-size: 0.7rem; font-weight: 800; padding: 4px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 4px 10px rgba(0,0,0,0.25);">
                                @if($isCultureMarket)
                                    🌾 Sản Phẩm Trưng Bày
                                @elseif($category === 'Ăn uống') 🍲 Khối Ẩm Thực
                                @elseif($category === 'Rau củ') 🥦 Nông Sản Sạch
                                @elseif($category === 'Thực phẩm khô') 🥜 Đồ Khô & Gia Vị
                                @elseif($category === 'Thịt tươi') 🥩 Thực Phẩm Tươi
                                @else 🛍️ Gian Hàng Số
                                @endif
                            </div>

                            <div style="position: absolute; bottom: 10px; left: 16px; right: 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <h4 style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 900; margin: 0; color: #ffffff; text-shadow: 0 2px 8px rgba(0,0,0,0.8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">
                                    {{ $displayStallName }}
                                </h4>
                                <button onclick="showStallOnMap('{{ $displayStallName }}', '{{ $sellerName }}', '{{ $category }}', '{{ $first->latitude ?? '' }}', '{{ $first->longitude ?? '' }}')" class="btn-map-pin" title="Định vị trên bản đồ số" style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(6px); border: 1px solid rgba(255,255,255,0.4); color: #ffffff; border-radius: 8px; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; padding: 4px 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" onmouseover="this.style.background='#E11D48'; this.style.color='#ffffff';" onmouseout="this.style.background='rgba(255, 255, 255, 0.25)'; this.style.color='#ffffff';">
                                    📍 Map
                                </button>
                            </div>
                        </div>

                        <!-- Card Header Info (Ảnh 2: Đại diện chủ hộ) -->
                        <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-glow); display: flex; align-items: center; gap: 14px; background: rgba(14, 165, 233, 0.015);">
                            @if($isCultureMarket)
                                <div class="stall-avatar" style="width: 42px; height: 42px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.12); border: 2px solid #ffffff; background: linear-gradient(135deg, #0EA5E9 0%, #0284c7 100%); color: #fff; font-size: 1.2rem;">
                                    🏛️
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <span style="font-size: 0.85rem; color: var(--text-main); font-weight: 700; display: block;">🏛️ Đơn vị trưng bày: Chợ Văn hóa Du lịch Cổ Loa</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">📍 Điểm tham quan & Quảng bá sản phẩm di sản</span>
                                </div>
                            @else
                                @php
                                    $gradients = [
                                        'linear-gradient(135deg, #0EA5E9 0%, #2563EB 100%)',
                                        'linear-gradient(135deg, #10B981 0%, #059669 100%)',
                                        'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)',
                                        'linear-gradient(135deg, #EC4899 0%, #DB2777 100%)'
                                    ];
                                    $grad = $gradients[abs(crc32($sellerName)) % count($gradients)];
                                @endphp
                                <div class="stall-avatar" style="width: 42px; height: 42px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.12); border: 2px solid #ffffff; background: {!! $grad !!};">
                                    @if(!empty($sellerAvatar))
                                        <img src="{{ $sellerAvatar }}" alt="{{ $sellerName }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 1rem;">
                                            {{ mb_substr($sellerName, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <span style="font-size: 0.85rem; color: var(--text-main); font-weight: 700; display: block;">👤 Chủ hộ: {{ $sellerName }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">📞 {{ $sellerPhone ?: 'Đã xác minh thông tin' }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Badges area -->
                        <div class="stall-badges-row">
                            <span class="gov-badge badge-verify-sky" 
                                  style="cursor: pointer; transition: all 0.2s;" 
                                  onmouseover="this.style.background='rgba(14, 165, 233, 0.15)'" 
                                  onmouseout="this.style.background='rgba(14, 165, 233, 0.08)'"
                                  onclick="openStallDetailAndScrollToReviews('{{ $displayStallName }}', '{{ $sellerName }}', '{{ $sellerPhone }}', '{{ $bankInfo }}', '{{ $category }}', '{{ $originText }}', '{{ $hasSmartphone ? 'yes' : 'no' }}', {{ json_encode($stallProducts) }}, '{{ $first->latitude ?? '' }}', '{{ $first->longitude ?? '' }}')">
                                ⭐ {{ number_format($avgRating, 1) }} ({{ $reviewCount }} Đánh giá)
                            </span>
                            @if($isCultureMarket)
                                <span class="gov-badge badge-attp-blue">✓ Sản phẩm OCOP / Di sản</span>
                            @else
                                <span class="gov-badge badge-attp-blue">✓ ATTP</span>
                                @if($hasQr)
                                    <span class="gov-badge badge-qr-green">✓ Có QR</span>
                                @endif
                            @endif
                        </div>
                        
                        <!-- Products listed & latest review -->
                        <div class="stall-products-section">
                            <div>
                                <h5 style="font-size: 0.78rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Sản phẩm nổi bật</h5>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    @foreach($stallProducts as $prod)
                                        @php
                                            $itemImg = $prod->image_url ?: ($prod->image_path ?: '/images/stalls/food.png');
                                            if (!empty($itemImg)) {
                                                $trimmedItemImg = trim($itemImg);
                                                if (str_starts_with($trimmedItemImg, '[')) {
                                                    $decodedItem = json_decode($trimmedItemImg, true);
                                                    if (is_array($decodedItem) && count($decodedItem) > 0) {
                                                        $trimmedItemImg = $decodedItem[0];
                                                    }
                                                }
                                                if (str_starts_with($trimmedItemImg, 'http://') || str_starts_with($trimmedItemImg, 'https://')) {
                                                    $itemImgUrl = $trimmedItemImg;
                                                } else {
                                                    $itemImgUrl = asset(ltrim($trimmedItemImg, '/'));
                                                }
                                            } else {
                                                $itemImgUrl = asset('/images/stalls/food.png');
                                            }
                                        @endphp
                                        <div class="product-item-gov" style="cursor: pointer; transition: all 0.2s; border-radius: 10px; padding: 6px 10px; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between; gap: 10px;" onmouseover="this.style.background='rgba(14, 165, 233, 0.08)';" onmouseout="this.style.background='transparent';" onclick="window.location.href='{{ route('market.stall.show', ['marketSlug' => $eatery->slug, 'stallSlug' => $safeStallSlug]) }}'">
                                            <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;">
                                                <img src="{{ $itemImgUrl }}" alt="{{ $prod->name }}" style="width: 38px; height: 38px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; flex-shrink: 0;">
                                                <span class="product-name-txt" style="font-weight: 700; color: var(--text-main); font-size: 0.88rem;">{{ $prod->name }}</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                @if($prod->price)
                                                    <span class="product-price-txt" style="font-weight: 700; color: #ea580c; font-size: 0.85rem;">
                                                        {{ is_numeric($prod->price) ? number_format($prod->price, 0, ',', '.') . 'đ' : $prod->price }}{{ $prod->unit ? '/' . $prod->unit : '' }} ➔
                                                    </span>
                                                @else
                                                    <span class="product-price-txt" style="color: #0284c7; font-weight: 700; font-size: 0.82rem; background: #e0f2fe; padding: 4px 10px; border-radius: 6px;">Trưng bày ➔</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions footer -->
                        <div class="btn-stall-grid">
                            @if($isCultureMarket)
                                <a href="{{ route('market.stall.show', ['marketSlug' => $eatery->slug, 'stallSlug' => $safeStallSlug]) }}" class="btn-stall-action" style="grid-column: span 2; background: var(--primary-grad); color: #fff; border-color: transparent; text-align: center; justify-content: center; font-weight: 700;">
                                    <i class="bi bi-search"></i> Xem chi tiết danh mục sản phẩm
                                </a>
                            @else
                                <a href="tel:{{ $sellerPhone }}" class="btn-stall-action">
                                    <i class="bi bi-telephone-fill"></i> Gọi điện
                                </a>
                                <a href="https://zalo.me/{{ $sellerPhone }}" target="_blank" class="btn-stall-action">
                                    <i class="bi bi-chat-text-fill"></i> Zalo
                                </a>
                                <a href="{{ route('market.stall.show', ['marketSlug' => $eatery->slug, 'stallSlug' => $safeStallSlug]) }}" class="btn-stall-action" style="background: var(--primary-grad); color: #fff; border-color: transparent;">
                                    <i class="bi bi-info-circle-fill"></i> Chi tiết
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- XII. TIMELINE AN TOÀN VỆ SINH THỰC PHẨM -->
    <div class="premium-panel" data-aos="fade-up">
        <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; margin-bottom: 24px; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-shield-check-fill" style="color: #10B981;"></i> Lịch trình Giám sát An toàn Thực phẩm (ATTP)
        </h3>
        
        <div class="gov-timeline">
            <div class="gov-timeline-item">
                <div class="gov-timeline-dot success"></div>
                <strong style="font-size: 0.88rem; color: var(--text-main); display: block;">Ngày 23/07/2026</strong>
                <span style="font-size: 0.82rem; color: #10B981; font-weight: 700;">🛡️ ĐẠT TIÊU CHUẨN</span>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">Kiểm nghiệm mẫu bún làng Mạch Tràng, chỉ số formol và hàn the âm tính, đạt chuẩn vệ sinh.</p>
            </div>
            
            <div class="gov-timeline-item">
                <div class="gov-timeline-dot success"></div>
                <strong style="font-size: 0.88rem; color: var(--text-main); display: block;">Ngày 22/07/2026</strong>
                <span style="font-size: 0.82rem; color: #10B981; font-weight: 700;">🛡️ ĐẠT TIÊU CHUẨN</span>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">Kiểm tra nguồn hàng nông sản tươi sống nhập khẩu, các quầy rau củ đạt chứng nhận VietGAP.</p>
            </div>

            <div class="gov-timeline-item">
                <div class="gov-timeline-dot warning"></div>
                <strong style="font-size: 0.88rem; color: var(--text-main); display: block;">Ngày 21/07/2026</strong>
                <span style="font-size: 0.82rem; color: var(--warning-brand); font-weight: 700;">🧪 ĐANG KIỂM NGHIỆM</span>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">Lấy mẫu định kỳ sản phẩm cá bống vàng khô và giò chả hộ bà Nguyễn Thị Hòa.</p>
            </div>

            <div class="gov-timeline-item">
                <div class="gov-timeline-dot success"></div>
                <strong style="font-size: 0.88rem; color: var(--text-main); display: block;">Ngày 20/07/2026</strong>
                <span style="font-size: 0.82rem; color: #10B981; font-weight: 700;">🛡️ ĐẠT TIÊU CHUẨN</span>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">Tổng kiểm tra chứng chỉ tập huấn vệ sinh an toàn thực phẩm toàn bộ 17 hộ kinh doanh.</p>
            </div>
        </div>
    </div>

    <!-- VII. DỮ LIỆU BÁO CÁO & TRUNG TÂM PHÂN TÍCH QUẢN TRỊ CHỢ SỐ 4.0 -->
    <div style="margin-top: 50px; margin-bottom: 20px;">
        <h2 style="font-family: var(--font-heading); font-weight: 900; font-size: 1.6rem; margin-bottom: 8px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
            📊 Trung Tâm Dữ Liệu &amp; Báo Cáo Hạ Tầng Chợ Số 4.0
        </h2>
        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 24px;">Báo cáo chỉ số số hóa, tỷ lệ liên kết ngân hàng và phân tích xu hướng nông sản chính thức từ BQL {{ $eatery->name }}.</p>
    </div>

    <!-- II. DASHBOARD THỐNG KÊ CHI TIẾT -->
    <div class="top-db-row">
        <!-- 1. Total Stalls -->
        <div class="metric-card-premium theme-blue" data-aos="fade-up" data-aos-delay="50">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span class="db-card-metric">{{ $totalStalls }}</span>
                <div class="metric-icon-box grad-blue">
                    <i class="bi bi-shop"></i>
                </div>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Tổng Hộ kinh doanh</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0 0 12px 0; line-height: 1.4;">Số lượng hộ đăng ký gian hàng số chính thức.</p>
            <div style="margin-top: auto;">
                <span style="font-size: 0.7rem; font-weight: 800; background: rgba(59, 130, 246, 0.08); color: #2563eb; padding: 4px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #2563eb; display: inline-block;"></span> Đã xác minh
                </span>
            </div>
        </div>

        <!-- 2. Total Products -->
        <div class="metric-card-premium theme-blue" data-aos="fade-up" data-aos-delay="100">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span class="db-card-metric">{{ $totalProducts }}</span>
                <div class="metric-icon-box grad-indigo">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Tổng số Mặt hàng</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0 0 12px 0; line-height: 1.4;">Nông sản sạch, ẩm thực đặc sắc và tiêu dùng thiết yếu.</p>
            <div style="margin-top: auto;">
                <span style="font-size: 0.7rem; font-weight: 800; background: rgba(99, 102, 241, 0.08); color: #4f46e5; padding: 4px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #4f46e5; display: inline-block;"></span> Sản phẩm sạch
                </span>
            </div>
        </div>

        <!-- 3. Stalls with QR -->
        <div class="metric-card-premium theme-emerald" data-aos="fade-up" data-aos-delay="150">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span class="db-card-metric">{{ $stallsWithQr }}</span>
                <div class="metric-icon-box grad-emerald">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main); display: flex; align-items: center;">
                <span class="pulse-indicator-success" style="margin-right: 6px;"></span>Số hộ có mã QR
            </h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0; line-height: 1.4;">Tỷ lệ phủ mã VietQR hỗ trợ thanh toán số cực kỳ nhanh chóng.</p>
            <div class="metric-progress-wrapper">
                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                    <span>Tỷ lệ phủ</span>
                    <span style="color: #10B981;">{{ $qrPercentage }}%</span>
                </div>
                <div style="height: 6px; background: rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $qrPercentage }}%; background: linear-gradient(90deg, #10B981, #34D399); border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <!-- 4. Cashless Payment rate -->
        <div class="metric-card-premium theme-emerald" data-aos="fade-up" data-aos-delay="200">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span class="db-card-metric">{{ $cashlessPercentage }}%</span>
                <div class="metric-icon-box grad-emerald">
                    <i class="bi bi-currency-exchange"></i>
                </div>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Tỷ lệ thanh toán số</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0; line-height: 1.4;">Tỷ trọng giao dịch không tiền mặt của tiểu thương trên hệ thống.</p>
            <div class="metric-progress-wrapper">
                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                    <span>Thanh toán số</span>
                    <span style="color: #10B981;">{{ $cashlessPercentage }}%</span>
                </div>
                <div style="height: 6px; background: rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $cashlessPercentage }}%; background: linear-gradient(90deg, #10B981, #059669); border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <!-- 5. Smartphone count -->
        <div class="metric-card-premium theme-blue" data-aos="fade-up" data-aos-delay="250">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span class="db-card-metric">{{ $stallsWithSmartphone }}</span>
                <div class="metric-icon-box grad-blue">
                    <i class="bi bi-phone"></i>
                </div>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Hộ dùng Smartphone</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0; line-height: 1.4;">Tỷ lệ sử dụng điện thoại thông minh để quản lý kinh doanh.</p>
            <div class="metric-progress-wrapper">
                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                    <span>Sử dụng</span>
                    <span style="color: #3b82f6;">{{ $phonePercentage }}%</span>
                </div>
                <div style="height: 6px; background: rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $phonePercentage }}%; background: linear-gradient(90deg, #3b82f6, #60a5fa); border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <!-- 6. Bank account count -->
        <div class="metric-card-premium theme-blue" data-aos="fade-up" data-aos-delay="300">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span class="db-card-metric">{{ $stallsWithBank }}</span>
                <div class="metric-icon-box grad-purple">
                    <i class="bi bi-bank"></i>
                </div>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Hộ có TK ngân hàng</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0; line-height: 1.4;">Tài khoản ngân hàng số liên kết trực tiếp để nhận tiền chuyển khoản.</p>
            <div class="metric-progress-wrapper">
                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                    <span>Tỷ lệ liên kết</span>
                    <span style="color: #8b5cf6;">{{ $bankPercentage }}%</span>
                </div>
                <div style="height: 6px; background: rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $bankPercentage }}%; background: linear-gradient(90deg, #8b5cf6, #a78bfa); border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <!-- 7. Wifi Status -->
        <div class="metric-card-premium theme-orange" data-aos="fade-up" data-aos-delay="350">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 900; color: #F59E0B; display: flex; align-items: center; gap: 4px; line-height: 1.2;">
                    <span class="pulse-indicator-pending" style="margin-right: 0;"></span>Đang quy hoạch
                </span>
                <div class="metric-icon-box grad-orange" style="opacity: 0.85;">
                    <i class="bi bi-wifi"></i>
                </div>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Wifi công cộng miễn phí</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0; line-height: 1.4;">Đang khảo sát lập phương án thiết kế hạ tầng mạng diện rộng.</p>
            <div class="metric-progress-wrapper">
                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                    <span>Khảo sát dự án</span>
                    <span style="color: #F59E0B;">35%</span>
                </div>
                <div style="height: 6px; background: rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden;">
                    <div style="height: 100%; width: 35%; background: linear-gradient(90deg, #F59E0B, #fbbf24); border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <!-- 8. Camera Status -->
        <div class="metric-card-premium theme-orange" data-aos="fade-up" data-aos-delay="400">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 900; color: #F59E0B; display: flex; align-items: center; gap: 4px; line-height: 1.2;">
                    <span class="pulse-indicator-pending" style="margin-right: 0;"></span>Đang lập dự án
                </span>
                <div class="metric-icon-box grad-orange" style="opacity: 0.85;">
                    <i class="bi bi-camera-video"></i>
                </div>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Camera AI giám sát an ninh</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0; line-height: 1.4;">Thiết kế hệ thống giám sát an ninh tự động trung tâm.</p>
            <div class="metric-progress-wrapper">
                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                    <span>Lập dự án</span>
                    <span style="color: #F59E0B;">20%</span>
                </div>
                <div style="height: 6px; background: rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden;">
                    <div style="height: 100%; width: 20%; background: linear-gradient(90deg, #F59E0B, #fbbf24); border-radius: 10px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- III. BIỂU ĐỒ PHÂN TÍCH (CHART.JS) -->
    <div class="premium-panel" data-aos="fade-up">
        <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; margin-bottom: 24px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
            📊 Trung tâm Phân Tích Dữ Liệu Chợ Số 4.0
        </h3>
        
        <div class="charts-grid-custom">
            <!-- 1. Pie Phân bố ngành hàng -->
            <div style="background: var(--bg-base); border-radius: 16px; padding: 20px; border: 1px solid var(--border-glow); height: 100%;">
                <h5 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 15px; text-align: center; color: var(--text-main);">Ngành hàng kinh doanh</h5>
                <div style="position: relative; height: 220px;">
                    <canvas id="categoryPieChart"></canvas>
                </div>
            </div>
            <!-- 2. Doughnut Tỷ lệ QR -->
            <div style="background: var(--bg-base); border-radius: 16px; padding: 20px; border: 1px solid var(--border-glow); height: 100%;">
                <h5 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 15px; text-align: center; color: var(--text-main);">Đăng ký mã VietQR</h5>
                <div style="position: relative; height: 220px;">
                    <canvas id="qrDoughnutChart"></canvas>
                </div>
            </div>
            <!-- 3. Doughnut Smartphone & Ngân hàng -->
            <div style="background: var(--bg-base); border-radius: 16px; padding: 20px; border: 1px solid var(--border-glow); height: 100%;">
                <h5 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 15px; text-align: center; color: var(--text-main);">Thiết bị & Liên kết Ngân hàng</h5>
                <div style="position: relative; height: 220px;">
                    <canvas id="smartDoughnutChart"></canvas>
                </div>
            </div>
            <!-- 4. Bar Nguồn gốc hàng hóa -->
            <div class="chart-full-width" style="background: var(--bg-base); border-radius: 16px; padding: 20px; border: 1px solid var(--border-glow); margin-top: 24px;">
                <h5 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 15px; color: var(--text-main);">Truy xuất Nguồn gốc Hàng hóa nông sản (%)</h5>
                <div style="position: relative; height: 260px;">
                    <canvas id="originBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- DYNAMIC CHART.JS SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') return;

            // 1. Category Pie Chart
            const catCanvas = document.getElementById('categoryPieChart');
            if (catCanvas) {
                new Chart(catCanvas.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: {!! json_encode(array_keys($categoriesCount)) !!},
                        datasets: [{
                            data: {!! json_encode(array_values($categoriesCount)) !!},
                            backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 2. VietQR Doughnut Chart
            const qrCanvas = document.getElementById('qrDoughnutChart');
            if (qrCanvas) {
                new Chart(qrCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Có VietQR', 'Chưa tạo QR'],
                        datasets: [{
                            data: [{{ $stallsWithQr }}, {{ max(0, $totalStalls - $stallsWithQr) }}],
                            backgroundColor: ['#10B981', '#E2E8F0']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 3. Smart & Bank Doughnut Chart
            const smartCanvas = document.getElementById('smartDoughnutChart');
            if (smartCanvas) {
                new Chart(smartCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Có TK Ngân Hàng', 'Có Smartphone', 'Tiền mặt'],
                        datasets: [{
                            data: [{{ $stallsWithBank }}, {{ $stallsWithSmartphone }}, {{ max(0, $totalStalls - $stallsWithBank) }}],
                            backgroundColor: ['#0EA5E9', '#3B82F6', '#CBD5E1']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 4. Origin Bar Chart
            const originCanvas = document.getElementById('originBarChart');
            if (originCanvas) {
                new Chart(originCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_keys($originsCount)) !!},
                        datasets: [{
                            label: 'Số lượng sản phẩm theo nguồn gốc',
                            data: {!! json_encode(array_values($originsCount)) !!},
                            backgroundColor: '#10B981',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        });
    </script>

    <!-- XIII. CARD CHUYỂN ĐỔI SỐ & XIV. CARD HẠ TẦNG -->
    <div class="bottom-double-grid">
        <!-- Chuyển đổi số checklist -->
        <div class="premium-panel blueprint-grid" data-aos="fade-right" style="margin-bottom:0; background: var(--bg-card);">
            <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.25rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
                <i class="bi bi-cpu" style="color: var(--primary);"></i> Bản đồ Chuyển đổi số Chợ 4.0
            </h3>
            <div class="bottom-info-list">
                @php
                    $checkIcon = '<span style="color: #10B981; font-size: 1.1rem;"><i class="bi bi-check-circle-fill"></i></span>';
                    $crossIcon = '<span style="color: #EF4444; font-size: 1.1rem;"><i class="bi bi-x-circle-fill"></i></span>';
                    $warnIcon  = '<span style="color: #F59E0B; font-size: 1.1rem;"><i class="bi bi-dash-circle-fill"></i></span>';
                @endphp

                {{-- Thanh toán số: check if > 0% --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    {!! $cashlessPercentage > 0 ? $checkIcon : $crossIcon !!}
                    <span style="font-size: 0.88rem; font-weight: 600; color: {{ $cashlessPercentage > 0 ? 'var(--text-main)' : 'var(--text-muted)' }};">
                        Thanh toán số ({{ $cashlessPercentage }}%)
                    </span>
                </div>

                {{-- Liên kết mã QR --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    {!! $qrPercentage > 0 ? $checkIcon : $crossIcon !!}
                    <span style="font-size: 0.88rem; font-weight: 600; color: {{ $qrPercentage > 0 ? 'var(--text-main)' : 'var(--text-muted)' }};">
                        Liên kết mã QR ({{ $qrPercentage }}%)
                    </span>
                </div>

                {{-- Smartphone --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    {!! $phonePercentage > 0 ? $checkIcon : $crossIcon !!}
                    <span style="font-size: 0.88rem; font-weight: 600; color: {{ $phonePercentage > 0 ? 'var(--text-main)' : 'var(--text-muted)' }};">
                        Sử dụng Smartphone ({{ $phonePercentage }}%)
                    </span>
                </div>

                {{-- Liên kết Ngân hàng --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    {!! $bankPercentage > 0 ? $checkIcon : $crossIcon !!}
                    <span style="font-size: 0.88rem; font-weight: 600; color: {{ $bankPercentage > 0 ? 'var(--text-main)' : 'var(--text-muted)' }};">
                        Liên kết Ngân hàng số ({{ $bankPercentage }}%)
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #EF4444; font-size: 1.1rem;"><i class="bi bi-x-circle-fill"></i></span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-muted);">Camera giám sát AI</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #EF4444; font-size: 1.1rem;"><i class="bi bi-x-circle-fill"></i></span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-muted);">Hệ thống Wifi Free</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #EF4444; font-size: 1.1rem;"><i class="bi bi-x-circle-fill"></i></span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-muted);">Ban Quản Lý Số hóa</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #10B981; font-size: 1.1rem;"><i class="bi bi-check-circle-fill"></i></span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Bản đồ số phân khu</span>
                </div>
            </div>
        </div>

        <!-- Hạ tầng kỹ thuật -->
        <div class="premium-panel blueprint-grid" data-aos="fade-left" style="margin-bottom:0; background: var(--bg-card);">
            <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.25rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
                <i class="bi bi-building-gear" style="color: var(--primary);"></i> Hạ tầng kỹ thuật số
            </h3>
            <div class="bottom-info-list">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">📶</span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Wifi công cộng</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">📹</span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Hệ thống Camera</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">🚗</span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Bãi đỗ xe thông minh</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">🚾</span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Nhà vệ sinh sạch sẽ</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">🗑️</span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Thùng rác phân loại</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">🚨</span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Lối thoát hiểm khẩn cấp</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">🏧</span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Trạm rút tiền ATM</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">🚌</span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Điểm xe buýt kết nối</span>
                </div>
            </div>
        </div>
    </div>


    
</div>

<!-- X. TRANG CHI TIẾT HỘ KINH DOANH (MODAL DRAWER) -->
<div id="stallDetailModal" class="qr-lightbox">
    <div class="qr-lightbox-content">
        
        <!-- Modal Banner -->
        <div style="padding: 24px 30px; display: flex; justify-content: space-between; align-items: center; position: relative; border-bottom: 1px solid var(--border-glow); background: rgba(0,0,0,0.015);">
            <div>
                <h3 id="mdStallName" style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 900; color: var(--text-main); margin: 0;">Gian hàng Cô Sinh</h3>
                <span id="mdCategoryBadge" class="gov-badge badge-verify-sky" style="margin-top: 6px; border: 1px solid var(--border-glow);">ĂN UỐNG</span>
            </div>
            <button onclick="closeStallDetail()" style="background: rgba(0, 0, 0, 0.04); border: 1px solid var(--border-glow); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: var(--text-main); font-size: 0.95rem; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='rgba(0,0,0,0.08)'; this.style.transform='rotate(90deg)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.04)'; this.style.transform='none';">✕</button>
        </div>
        
        <!-- Modal Content Body -->
        <div style="padding: 30px; max-height: 70vh; overflow-y: auto; background: var(--bg-card);">
            <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 24px;">
                <!-- Left panel: Info & products -->
                <div>
                    <h5 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin-bottom: 12px; color: var(--text-main);">Thông tin tiểu thương</h5>
                    <div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 10px; color: var(--text-muted); margin-bottom: 24px; background: rgba(14, 165, 233, 0.015); border: 1px solid var(--border-glow); border-radius: 16px; padding: 18px;">
                        <div><i class="bi bi-person-fill" style="color: var(--primary); margin-right: 6px;"></i> Chủ hộ: <strong id="mdSellerName" style="color: var(--text-main);">Nguyễn Thị Sinh</strong></div>
                        <div><i class="bi bi-telephone-fill" style="color: #10B981; margin-right: 6px;"></i> Số điện thoại: <strong id="mdSellerPhone" style="color: var(--text-main);">0965194462</strong></div>
                        <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                            <i class="bi bi-geo-alt-fill" style="color: #EF4444; margin-right: 6px;"></i> Phân khu: 
                            <strong id="mdZone" style="color: var(--text-main);">Khối A - Gian 102</strong>
                            <span onclick="showStallOnMapFromModal()" style="color: var(--primary); font-weight: 800; font-size: 0.75rem; cursor: pointer; text-decoration: underline; margin-left: 8px; display: inline-flex; align-items: center; gap: 2px;" title="Xem vị trí & Chỉ đường"><i class="bi bi-geo-alt-fill"></i> Chỉ đường</span>
                        </div>
                        <div><i class="bi bi-phone-fill" style="color: var(--primary); margin-right: 6px;"></i> Sử dụng Smartphone: <strong id="mdSmartphone" style="color: var(--text-main);">Có</strong></div>
                    </div>
                    
                    <h5 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin-bottom: 12px; color: var(--text-main);">Danh sách sản phẩm niêm yết</h5>
                    <div id="mdProductsList" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px; max-height: 270px; overflow-y: auto; padding-right: 6px; scrollbar-width: thin;">
                        <!-- JS generated items -->
                    </div>

                    <!-- XV. NGUỒN GỐC HÀNG HÓA -->
                    <h5 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin-bottom: 16px; color: var(--text-main);">Nguồn gốc & Truy xuất xuất xứ</h5>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; margin-bottom: 10px; max-width: 300px;">
                        <div class="flow-process-step">
                            <div class="flow-process-icon-wrap">🌾</div>
                            <span id="mdOriginText" style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); display: block; max-width: 80px; margin: 0 auto; line-height: 1.2;">Tự sản xuất</span>
                        </div>
                        <div class="flow-process-line" style="margin-bottom: 16px;"></div>
                        <div class="flow-process-step">
                            <div class="flow-process-icon-wrap">🚛</div>
                            <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); display: block;">Vận chuyển</span>
                        </div>
                        <div class="flow-process-line" style="margin-bottom: 16px;"></div>
                        <div class="flow-process-step">
                            <div class="flow-process-icon-wrap">🏪</div>
                            <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); display: block;">Gian hàng số</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right panel: QR Payment & Checkout -->
                <div style="border-left: 1px solid var(--border-glow); padding-left: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <h5 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin-bottom: 16px; width: 100%; text-align: center; color: var(--text-main);">Mã QR Thanh Toán</h5>
                    
                    <div class="qr-scanner-box">
                        <div class="qr-scanner-line"></div>
                        <img id="mdQrImage" src="" alt="VietQR" style="width: 100%; height: 100%; object-fit: contain;">
                        <div id="mdQrLoader" style="position: absolute; inset: 0; background: #ffffff; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">⏳</div>
                    </div>
                    
                    <div style="text-align: center; width: 100%;">
                        <div id="mdBankInfo" class="bank-info-badge">Ngân hàng MB: 0965194462</div>
                        <p style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4; margin: 0; max-width: 185px; margin: 0 auto;">
                            Chuyển khoản trực tiếp Napas247 bảo mật, an toàn 100%.
                        </p>
                    </div>
                </div>
            </div>

            <!-- XI. ĐÁNH GIÁ VÀ PHẢN HỒI GIAN HÀNG -->
            <div style="border-top: 1px solid var(--border-glow); margin-top: 30px; padding-top: 24px;">
                <h5 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin-bottom: 16px; color: var(--text-main);">Đánh giá & Bình luận quầy hàng</h5>
                
                <!-- Rating Form -->
                <form id="mdReviewForm" onsubmit="submitStallReview(event)" style="background: var(--bg-base); padding: 16px; border-radius: 16px; border: 1px solid var(--border-glow); margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">Đánh giá quầy:</span>
                        <div class="stars-selector" style="display: flex; gap: 4px; font-size: 1.25rem; color: #CBD5E1; cursor: pointer;">
                            <span onclick="setReviewRating(1)">★</span>
                            <span onclick="setReviewRating(2)">★</span>
                            <span onclick="setReviewRating(3)">★</span>
                            <span onclick="setReviewRating(4)">★</span>
                            <span onclick="setReviewRating(5)">★</span>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <textarea class="custom-input" rows="2" style="font-size: 0.85rem; background: var(--bg-card); border-color: var(--border-glow); color: var(--text-main);" placeholder="Viết nhận xét của bạn..." required></textarea>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.72rem; color: var(--text-muted);"><i class="bi bi-camera-fill"></i> Đính kèm ảnh/video</span>
                        <button type="submit" style="background: var(--primary); border: none; color: white; font-weight: 700; font-size: 0.82rem; padding: 8px 16px; border-radius: 10px; cursor: pointer;">Gửi đánh giá</button>
                    </div>
                </form>

                <!-- Reviews list mock -->
                <div id="mdReviewsList" style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; gap: 12px; background: var(--bg-base); padding: 12px; border-radius: 12px; border: 1px solid var(--border-glow);">
                        <span style="font-size: 1.5rem;">👨</span>
                        <div>
                            <strong style="font-size: 0.82rem; color: var(--text-main); display: block;">Nguyễn Hoàng Minh <span style="color: #F59E0B; margin-left: 6px;">★★★★★</span></strong>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 4px 0 0 0;">Sản phẩm rất tươi ngon, chủ quán thân thiện, thanh toán QR siêu nhanh chóng!</p>
                        </div>
                    </div>
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

<!-- FLOATING CHAT BUBBLE & PANEL FOR CHỢ SỐ 4.0 -->
<!-- Floating Trigger Bubble -->
<div id="marketChatBubbleTrigger" onclick="toggleFloatingChat()" style="position: fixed; bottom: 95px; right: 24px; width: 62px; height: 62px; border-radius: 50%; background: linear-gradient(135deg, #FF9F43, #FF7A00); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.7rem; cursor: pointer; box-shadow: 0 6px 20px rgba(255, 122, 0, 0.35); z-index: 99999; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='scale(1.08) translateY(-2px)'" onmouseout="this.style.transform='none'">
    💬
    <!-- Unread indicator count -->
    <span id="chatBubbleUnreadBadge" style="display: none; position: absolute; top: -2px; right: -2px; background: #ef4444; color: white; font-size: 0.68rem; font-weight: 800; padding: 2px 6px; border-radius: 10px; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">0</span>
</div>

<!-- Floating Chat Panel -->
<div id="marketFloatingChatPanel" style="display: none; position: fixed; bottom: 175px; right: 24px; width: 850px; height: 580px; background: var(--bg-card); border: 1.5px solid var(--border-glow); border-radius: 24px; box-shadow: 0 12px 40px rgba(0,0,0,0.18); z-index: 99999; flex-direction: column; overflow: hidden; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; transform: translateY(20px);">
    <!-- Panel Header -->
    <div style="background: linear-gradient(135deg, #FF9F43, #FF7A00); color: white; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; z-index: 10; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.4rem;">💬</span>
            <div style="text-align: left;">
                <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 0.95rem; margin: 0; color: white; line-height: 1.2;">
                    Cổng Chợ Số 4.0 {{ $eatery->name }}
                </h3>
                <p style="font-size: 0.72rem; opacity: 0.9; margin: 2px 0 0 0; color: white; line-height: 1.1;">
                    Giao lưu cộng đồng & Nhắn riêng tiểu thương trực tuyến
                </p>
            </div>
        </div>
        <button onclick="toggleFloatingChat()" style="background: rgba(255,255,255,0.18); border: none; width: 28px; height: 28px; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: bold; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.18)'">✕</button>
    </div>

    <!-- Inner Chat Wrapper -->
    <div class="market-chat-container" style="height: calc(100% - 58px); margin-top: 0; border: none; border-radius: 0; box-shadow: none;">
            <!-- Left panel: Rooms & Grouped Stalls Directory -->
            <div class="market-chat-sidebar" style="overflow-y: auto; scrollbar-width: thin; padding-right: 12px;">
                <!-- Room Switcher Tabs -->
                <div class="active-merchants-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                    <div class="merchant-active-card chat-room-tab active" id="tabPublicRoom" onclick="switchChatRoom('public')" style="cursor: pointer; font-weight: 700; border-color: var(--primary);">
                        <span style="font-size: 1.35rem;">💬</span>
                        <div style="flex: 1; min-width: 0;">
                            <strong style="font-size: 0.85rem; color: var(--text-main); display: block;">Phòng Chat Chung</strong>
                            <span style="font-size: 0.72rem; color: var(--text-muted); display: block;">Mọi người thảo luận</span>
                        </div>
                    </div>
                </div>

                <!-- Search Bar -->
                <div style="margin-bottom: 12px; margin-top: 4px;">
                    <div class="custom-input-group" style="position: relative;">
                        <span class="custom-input-icon" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); display: flex; align-items: center; justify-content: center; pointer-events: none;"><i class="bi bi-search" style="font-size: 0.85rem;"></i></span>
                        <input type="text" id="chatStallSearch" oninput="filterChatStalls()" class="custom-input" style="font-size: 0.78rem; border-radius: 12px; padding: 8px 12px 8px 36px !important; width: 100%;" placeholder="Tìm tên sạp / chủ hộ...">
                    </div>
                </div>

                <!-- Dynamic Grouped Merchant List for Private Chat -->
                <div id="stallsGroupedAccordion">
                    @php
                        $groupedByCategory = [
                            'Ăn uống' => [],
                            'Rau củ' => [],
                            'Thực phẩm khô' => [],
                            'Thịt tươi' => [],
                            'Khác' => []
                        ];

                        foreach($groupedStalls as $stallName => $stallProducts) {
                            $first = $stallProducts->first();
                            $category = 'Khác';
                            if (str_contains($stallName, 'Ăn uống') || str_contains($stallName, 'Ăn sáng') || str_contains($stallName, 'Ẩm thực')) {
                                $category = 'Ăn uống';
                            } elseif (str_contains($stallName, 'Rau củ') || str_contains($stallName, 'Rau')) {
                                $category = 'Rau củ';
                            } elseif (str_contains($stallName, 'Thực phẩm khô') || str_contains($stallName, 'Hoa quả')) {
                                $category = 'Thực phẩm khô';
                            } elseif (str_contains($stallName, 'Thịt') || str_contains($stallName, 'Giò chả')) {
                                $category = 'Thịt tươi';
                            }
                            $groupedByCategory[$category][$stallName] = $stallProducts;
                        }
                        
                        $categoryEmojis = [
                            'Ăn uống' => '🍲 Ẩm thực',
                            'Rau củ' => '🥦 Rau củ quả',
                            'Thực phẩm khô' => '🥜 Đồ khô & Đồ khô',
                            'Thịt tươi' => '🥩 Thịt tươi sống',
                            'Khác' => '🏪 Ngành hàng khác'
                        ];

                        $merchantCount = 0;
                    @endphp

                    @foreach($groupedByCategory as $catName => $stallsList)
                        @if(count($stallsList) > 0)
                            <div class="chat-category-group" id="catGroup_{{ preg_replace('/[^a-zA-Z0-9]/', '', $catName) }}" style="margin-bottom: 14px;">
                                <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-glow); padding-bottom: 4px;">
                                    <span>{{ $categoryEmojis[$catName] }}</span>
                                    <span style="background: rgba(0,0,0,0.05); padding: 1px 5px; border-radius: 6px; font-size: 0.65rem;">{{ count($stallsList) }}</span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    @foreach($stallsList as $stallName => $stallProducts)
                                        @php
                                            $first = $stallProducts->first();
                                            $sellerName = $first->seller_name;
                                            $merchantCount++;
                                            $avatars = ['👵','👩','👨','👩‍🍳','👨‍🌾','👵','👨‍🍳','👩‍🌾'];
                                            $avatar = $avatars[$merchantCount % count($avatars)];
                                            
                                            $safeStallId = preg_replace('/[^a-zA-Z0-9]/', '', $stallName);

                                            // Realistic simulated last active time
                                            $activityLogs = ['Vừa truy cập', 'Đang trực tuyến', 'Hoạt động 2 phút trước', 'Hoạt động 5 phút trước'];
                                            $activityText = $activityLogs[$merchantCount % count($activityLogs)];
                                        @endphp
                                        <div class="merchant-active-card chat-room-tab chat-stall-card-item" id="tabStall_{{ $safeStallId }}" data-stall-raw-name="{{ $stallName }}" data-stall-name="{{ strtolower($stallName) }}" data-seller-name="{{ strtolower($sellerName) }}" onclick="switchChatRoom('private', '{{ $stallName }}')" style="cursor: pointer; position: relative;">
                                            <span style="font-size: 1.35rem;">{{ $avatar }}</span>
                                            <div style="flex: 1; min-width: 0;">
                                                <strong style="font-size: 0.82rem; color: var(--text-main); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ $stallName }}
                                                </strong>
                                                <span style="font-size: 0.72rem; color: var(--text-muted); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    Chủ sạp: {{ $sellerName }}
                                                </span>
                                                <span style="font-size: 0.64rem; color: #2ecc71; font-weight: 700; display: flex; align-items: center; gap: 3px; margin-top: 2px;">
                                                    <span class="active-dot-pulsing" style="margin: 0; width: 6px; height: 6px;"></span> {{ $activityText }}
                                                </span>
                                            </div>
                                            <!-- Red dot indicator for unread messages -->
                                            <span class="unread-badge-dot" id="unreadStall_{{ $safeStallId }}" style="display: none; position: absolute; top: 10px; right: 10px; width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- If current user is a merchant, show customer list who sent PMs to them -->
                @if(Auth::check())
                    @php
                        $user = Auth::user();
                        $userPhone = $user->phone ?? '';
                        $merchantStall = \App\Models\OcopProduct::on('mysql_market')
                            ->where('eatery_id', $eatery->id)
                            ->where('seller_phone', $userPhone)
                            ->whereNotNull('stall_name')
                            ->first();
                    @endphp
                    @if($merchantStall)
                        <div id="merchantPrivateChatsSection" style="border-top: 1px solid var(--border-glow); margin-top: 18px; padding-top: 14px;">
                            <h4 style="font-family: var(--font-heading); font-size: 0.85rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                📩 Khách hàng nhắn riêng
                            </h4>
                            <div id="merchantCustomersList" style="display: flex; flex-direction: column; gap: 8px;">
                                <div style="font-size: 0.72rem; color: var(--text-muted); text-align: center; padding: 10px 0;">Chưa có khách hàng nhắn riêng</div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Right panel: Chatbox -->
            <div class="market-chat-main">
                <!-- Pinned message banner / Recipient header -->
                <div class="pinned-message-banner" id="chatRoomHeader" style="background: rgba(14, 165, 233, 0.04); border-color: rgba(14, 165, 233, 0.25); color: var(--text-main);">
                    <span style="font-size: 1.1rem; flex-shrink: 0;" id="chatHeaderIcon">📢</span>
                    <div style="flex: 1; min-width: 0; font-size: 0.78rem; font-weight: 600; line-height: 1.4;">
                        <span id="chatHeaderSubtitle" style="color: var(--primary); text-transform: uppercase; font-size: 0.72rem; font-weight: 800; display: block; margin-bottom: 2px;">Thông báo từ Ban Quản Lý</span>
                        <span id="chatHeaderContent">Chào mừng bà con đến với Cổng Chợ Số 4.0 {{ $eatery->name }}. Hãy quét QR VietQR để thanh toán không tiền mặt và tích lũy điểm tiêu dùng xanh!</span>
                    </div>
                </div>

                <!-- Chat history window -->
                <div class="chat-messages-window" id="marketChatMessages">
                    <div style="display: flex; justify-content: center; align-items: center; height: 100%; color: var(--text-muted);">
                        <div style="width: 24px; height: 24px; border: 2px solid rgba(0,0,0,0.05); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 8px;"></div>
                        <span style="font-size: 0.82rem; font-weight: 600;">Đang tải hội thoại...</span>
                    </div>
                </div>

                <!-- Product attachment preview -->
                <div class="product-attachment-preview" id="chatProductPreview" style="display: none;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: #FF7A00; display: flex; align-items: center; gap: 4px; margin-bottom: 6px;">
                        <span>🏷️</span> Đang gắn kèm sản phẩm:
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center; background: var(--bg-base); padding: 8px 12px; border-radius: 12px; border: 1px solid var(--border-glow); position: relative;">
                        <img id="chatAttachedImg" src="" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;" onerror="this.src='https://placehold.co/44x44/00A86B/ffffff?text=Product'">
                        <div style="flex: 1; min-width: 0;">
                            <strong id="chatAttachedName" style="font-size: 0.82rem; color: var(--text-main); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Tên</strong>
                            <span id="chatAttachedPrice" style="font-size: 0.78rem; color: #FF7A00; font-weight: 800;">0đ</span>
                        </div>
                        <button type="button" onclick="removeAttachedProduct()" style="background: rgba(0,0,0,0.05); border: none; width: 24px; height: 24px; border-radius: 50%; color: var(--text-muted); font-size: 0.75rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444'" onmouseout="this.style.background='rgba(0,0,0,0.05)'; this.style.color='var(--text-muted)'">✕</button>
                    </div>
                    <input type="hidden" id="chatAttachedProductId" value="">
                </div>

                <!-- Image attachment preview -->
                <div class="product-attachment-preview" id="chatImagePreview" style="display: none; background: rgba(16, 185, 129, 0.03); border-color: rgba(16, 185, 129, 0.25);">
                    <div style="font-size: 0.78rem; font-weight: 700; color: #10B981; display: flex; align-items: center; gap: 4px; margin-bottom: 6px;">
                        <span>📷</span> Đang đính kèm hình ảnh:
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center; background: var(--bg-base); padding: 8px 12px; border-radius: 12px; border: 1px solid var(--border-glow); position: relative; width: fit-content;">
                        <img id="chatAttachedImgFile" src="" style="max-width: 80px; max-height: 80px; border-radius: 8px; object-fit: contain;">
                        <button type="button" onclick="removeAttachedImage()" style="position: absolute; top: -8px; right: -8px; background: #ef4444; border: none; width: 20px; height: 20px; border-radius: 50%; color: white; font-size: 0.7rem; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">✕</button>
                    </div>
                </div>

                <!-- Input area -->
                <div id="chatLoginPrompt" style="display: none; background: rgba(0,0,0,0.015); border: 1.5px dashed var(--border-glow); border-radius: 16px; padding: 14px; text-align: center; margin-top: 10px;">
                    <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                        🔑 Vui lòng <a href="/login" style="color: var(--primary); text-decoration: underline !important; font-weight: 750;">đăng nhập</a> để bắt đầu cuộc trò chuyện riêng tư với chủ sạp.
                    </span>
                </div>

                <form id="marketChatForm" onsubmit="sendChatMessage(event)" style="border-top: 1px solid var(--border-glow); padding-top: 12px; display: flex; flex-direction: column; gap: 10px;">
                    
                    <!-- Quick Replies Row -->
                    <div class="quick-replies-wrap">
                        <span class="quick-reply-pill" onclick="sendQuickReply('Sản phẩm này còn hàng không ạ?')">Sản phẩm này còn không?</span>
                        <span class="quick-reply-pill" onclick="sendQuickReply('Giá bao nhiêu vậy ạ?')">Giá bao nhiêu vậy ạ?</span>
                        <span class="quick-reply-pill" onclick="sendQuickReply('Hàng có sẵn để qua lấy luôn không ạ?')">Có sẵn không ạ?</span>
                        <span class="quick-reply-pill" onclick="sendQuickReply('Nhớ bọc kỹ giúp mình nhé!')">Bọc kỹ giúp mình nhé!</span>
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        @if(Auth::check() && $merchantStall)
                            <!-- Share product button for merchants -->
                            <div style="position: relative;">
                                <button type="button" onclick="toggleMerchantProductSelector()" class="btn-attach-product" style="padding: 5px 10px; font-size: 0.72rem; font-weight: 700; border-radius: 8px; background: rgba(0, 168, 107, 0.08); border: 1px solid rgba(0, 168, 107, 0.25); color: #00A86B; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s;">
                                    🏷️ Gắn sản phẩm sạp của tôi
                                </button>
                                <div class="merchant-product-dropdown" id="merchantProductDropdown" style="display: none; position: absolute; bottom: 32px; left: 0; width: 240px; max-height: 200px; overflow-y: auto; background: var(--bg-card); border: 1px solid var(--border-glow); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10; padding: 6px 0;">
                                    @php
                                        $stallsProds = \App\Models\OcopProduct::on('mysql_market')
                                            ->where('eatery_id', $eatery->id)
                                            ->where('stall_name', $merchantStall->stall_name)
                                            ->get();
                                    @endphp
                                    @if($stallsProds->count() === 0)
                                        <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center; padding: 10px 0;">Sạp của bạn chưa có sản phẩm</div>
                                    @else
                                        @foreach($stallsProds as $sp)
                                            <div class="dropdown-product-item" onclick="attachProduct({{ $sp->id }}, '{{ $sp->name }}', {{ (float)$sp->price }}, '{{ $sp->image_path ? asset($sp->image_path) : '' }}')">
                                                <strong>{{ $sp->name }}</strong>
                                                <span>{{ number_format($sp->price) }}đ</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center;">
                        <!-- Image Upload Button (Camera icon) -->
                        <button type="button" onclick="document.getElementById('chatImageInputFile').click()" style="padding: 10px 12px; background: rgba(0,0,0,0.03); border: 1px solid var(--border-glow); border-radius: 12px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" title="Đính kèm hình ảnh" onmouseover="this.style.background='rgba(0,168,107,0.08)'; this.style.color='#00A86B'" onmouseout="this.style.background='rgba(0,0,0,0.03)'; this.style.color='var(--text-muted)'">
                            <i class="bi bi-camera" style="font-size: 1.15rem;"></i>
                        </button>
                        <input type="file" id="chatImageInputFile" style="display: none;" accept="image/*" onchange="handleChatImageSelect(this)">

                        @if(!Auth::check())
                            <div class="custom-input-group" style="width: 140px; flex-shrink: 0;">
                                <span class="custom-input-icon"><i class="bi bi-person"></i></span>
                                <input type="text" id="chatGuestName" class="custom-input custom-input-with-icon" style="font-size: 0.78rem; padding: 6px 6px 6px 28px; border-radius: 10px;" placeholder="Tên của bạn" required>
                            </div>
                        @endif

                        <input type="text" id="chatMessageInput" class="custom-input" style="flex: 1; border-radius: 14px; font-size: 0.85rem;" placeholder="Nhập tin nhắn với tư cách {{ Auth::check() ? Auth::user()->name : 'Khách vãng lai' }}..." required>
                        <button type="submit" class="btn-send-chat" style="padding: 10px 20px; font-size: 0.85rem; font-weight: 800; border-radius: 14px; border: none; background: linear-gradient(135deg, #FF9F43, #FF7A00); color: white; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(255, 122, 0, 0.2); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                            Gửi <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<!-- Additional styles for Market Group Chat -->
<style>
    .quick-replies-wrap {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        white-space: nowrap;
        padding: 4px 2px 6px 2px;
        margin-bottom: 2px;
        scrollbar-width: none;
    }

    .quick-replies-wrap::-webkit-scrollbar {
        display: none;
    }

    .quick-reply-pill {
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(255, 122, 0, 0.06);
        border: 1px solid rgba(255, 122, 0, 0.2);
        color: #e66000;
        padding: 6px 14px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-block;
        user-select: none;
        box-shadow: 0 1px 3px rgba(255, 122, 0, 0.04);
        flex-shrink: 0;
    }
    .quick-reply-pill:hover {
        background: linear-gradient(135deg, #FF9F43, #FF7A00);
        color: white;
        border-color: #FF7A00;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 12px rgba(255, 122, 0, 0.25);
    }

    .market-chat-container {
        display: flex;
        gap: 0;
        height: 100%;
        background: var(--bg-card);
        border: none;
        border-radius: 0;
        overflow: hidden;
        margin-top: 0;
        box-shadow: none;
    }

    @media (max-width: 900px) {
        #marketFloatingChatPanel {
            width: calc(100% - 32px) !important;
            height: 520px !important;
            right: 16px !important;
            bottom: 160px !important;
        }
    }

    @media (max-width: 600px) {
        #marketFloatingChatPanel {
            width: 100% !important;
            height: 100% !important;
            right: 0 !important;
            bottom: 0 !important;
            border-radius: 0 !important;
        }
        #marketChatBubbleTrigger {
            bottom: 16px !important;
            right: 16px !important;
            width: 54px !important;
            height: 54px !important;
            font-size: 1.5rem !important;
        }
    }

    .market-chat-sidebar {
        width: 32%;
        background: rgba(0, 0, 0, 0.005);
        border-right: 1px solid var(--border-glow);
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        height: 100%;
    }

    .market-chat-main {
        width: 68%;
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
        padding: 16px;
        background: rgba(0, 0, 0, 0.002);
        height: 100%;
    }

    .merchant-active-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        margin-right: 4px;
    }

    .merchant-active-card:hover {
        border-color: var(--primary);
        background: rgba(255, 122, 0, 0.02);
        transform: translateY(-1.5px);
        box-shadow: 0 4px 12px rgba(255, 122, 0, 0.05);
    }

    .merchant-active-card.active {
        border-color: var(--primary) !important;
        background: rgba(255, 122, 0, 0.04) !important;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(255, 122, 0, 0.08);
    }

    .active-dot-pulsing {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2ecc71;
        box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4);
        animation: pulse-active-dot 1.8s infinite ease-in-out;
        flex-shrink: 0;
        margin-left: auto;
    }

    @keyframes pulse-active-dot {
        0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(46, 204, 113, 0); }
        100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }

    .pinned-message-banner {
        display: flex;
        gap: 10px;
        align-items: center;
        background: rgba(245, 158, 11, 0.04);
        border: 1px dashed rgba(245, 158, 11, 0.25);
        border-radius: 14px;
        padding: 10px 14px;
        margin-bottom: 12px;
        color: var(--text-main);
    }

    .chat-messages-window {
        flex: 1;
        overflow-y: auto;
        border: 1px solid var(--border-glow);
        border-radius: 18px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        background: rgba(248, 250, 252, 0.65);
        margin-bottom: 12px;
        scrollbar-width: thin;
    }

    .chat-bubble-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        max-width: 80%;
        margin-bottom: 2px;
        align-self: flex-start;
    }

    .chat-bubble-row.own-message {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .chat-avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.06);
    }

    .chat-avatar-merchant {
        background: linear-gradient(135deg, #FEF3C7, #FDE68A);
        border: 1px solid #FCD34D;
        color: #D97706;
    }

    .chat-avatar-user {
        background: linear-gradient(135deg, #E0F2FE, #BAE6FD);
        border: 1px solid #7DD3FC;
        color: #0284C7;
    }

    .chat-avatar-admin {
        background: linear-gradient(135deg, #FEE2E2, #FCA5A5);
        border: 1px solid #F87171;
        color: #DC2626;
    }

    .chat-bubble-content-wrap {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .own-message .chat-bubble-content-wrap {
        align-items: flex-end;
    }

    .chat-bubble-header {
        font-size: 0.73rem;
        color: var(--text-muted);
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .own-message .chat-bubble-header {
        justify-content: flex-end;
    }

    .chat-badge {
        font-size: 0.64rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .badge-admin {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .badge-merchant {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .badge-user {
        background: rgba(14, 165, 233, 0.1);
        color: #0284c7;
        border: 1px solid rgba(14, 165, 233, 0.2);
    }

    .chat-bubble-text {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        color: #1e293b;
        padding: 10px 15px;
        border-radius: 18px;
        border-top-left-radius: 4px;
        font-size: 0.86rem;
        line-height: 1.45;
        word-break: break-word;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .own-message .chat-bubble-text {
        background: linear-gradient(135deg, #FF9F43, #FF7A00);
        border: 1px solid #FF7A00;
        color: #ffffff;
        border-radius: 18px;
        border-top-right-radius: 4px;
        border-top-left-radius: 18px;
        box-shadow: 0 4px 14px rgba(255, 122, 0, 0.22);
    }

    .chat-product-card-msg {
        display: flex;
        gap: 10px;
        align-items: center;
        background: var(--bg-base);
        border: 1.5px solid var(--border-glow);
        border-radius: 12px;
        padding: 10px;
        margin-top: 6px;
        max-width: 280px;
    }

    .own-message .chat-product-card-msg {
        border-color: rgba(255,255,255,0.25);
        background: rgba(255,255,255,0.1);
        color: white;
    }

    .dropdown-product-item {
        padding: 8px 14px;
        cursor: pointer;
        font-size: 0.78rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s;
    }

    .dropdown-product-item:hover {
        background: rgba(0,0,0,0.03);
        color: var(--primary);
    }

    .product-attachment-preview {
        background: rgba(255, 122, 0, 0.03);
        border: 1.5px dashed rgba(255, 122, 0, 0.25);
        border-radius: 14px;
        padding: 10px 14px;
        margin-bottom: 12px;
    }

    @media (max-width: 768px) {
        .market-chat-container {
            flex-direction: column;
            height: auto;
        }
        .market-chat-sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid var(--border-glow);
            padding-right: 0;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }
        .market-chat-main {
            width: 100%;
            padding-left: 0;
        }
        .chat-messages-window {
            height: 300px;
        }
    }
</style>

@section('scripts')
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-quad',
        once: true
    });

    const categoriesData = @json($categoriesCount);
    const originsData = @json($originsCount);
    const totalStallsCount = {{ $totalStalls }};
    const stallsWithQrCount = {{ $stallsWithQr }};
    const stallsWithSmartphoneCount = {{ $stallsWithSmartphone }};
    const stallsWithBankCount = {{ $stallsWithBank }};

    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const labelColor = isDark ? '#94A3B8' : '#64748B';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        // 1. Pie Chart: Phân bố ngành hàng
        new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(categoriesData),
                datasets: [{
                    data: Object.values(categoriesData),
                    backgroundColor: ['#F43F5E', '#10B981', '#F59E0B', '#EF4444', '#94A3B8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, color: labelColor, font: { size: 10 } } }
                }
            }
        });

        // 2. Doughnut Chart: Tỷ lệ QR
        new Chart(document.getElementById('qrDoughnutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Đã có mã QR', 'Chưa có QR'],
                datasets: [{
                    data: [stallsWithQrCount, totalStallsCount - stallsWithQrCount],
                    backgroundColor: ['#10B981', '#E2E8F0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, color: labelColor, font: { size: 10 } } }
                }
            }
        });

        // 3. Doughnut Chart: Thiết bị & Ngân hàng
        new Chart(document.getElementById('smartDoughnutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Có Smartphone', 'Có tài khoản NH'],
                datasets: [{
                    data: [stallsWithSmartphoneCount, stallsWithBankCount],
                    backgroundColor: ['#0EA5E9', '#2563EB'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, color: labelColor, font: { size: 10 } } }
                }
            }
        });

        // 4. Bar Chart: Nguồn gốc hàng hóa
        new Chart(document.getElementById('originBarChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(originsData),
                datasets: [{
                    label: 'Số lượng sản phẩm',
                    data: Object.values(originsData),
                    backgroundColor: '#0EA5E9',
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y', // Convert to Horizontal Bar Chart
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: labelColor },
                        beginAtZero: true
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: labelColor }
                    }
                }
            }
        });

        initSatelliteMap();

        // Click-outside listener for Stall Detail Modal
        const stallModal = document.getElementById('stallDetailModal');
        if (stallModal) {
            stallModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeStallDetail();
                }
            });
        }

        // Click-outside listener for Hero Gallery Modal
        const galleryModal = document.getElementById('heroGalleryModal');
        if (galleryModal) {
            galleryModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeHeroGalleryModal();
                }
            });
        }

        // Start Chat Polling
        if (document.getElementById('marketChatMessages')) {
            loadChatMessages();
            startChatPolling(10000);
        }
    });

    // --- CHỢ SỐ 4.0: PHÒNG CHAT CỘNG ĐỒNG ---
    const marketId = {{ $eatery->id }};
    const marketName = {!! json_encode($eatery->name) !!};
    const messagesWindow = document.getElementById('marketChatMessages');
    let lastLoadedMsgId = 0;
    
    // Private chat states
    let activeRoomType = 'public'; // 'public' or 'private'
    let activeStallName = null;
    let activeCustomerId = null;
    let activeCustomerName = null;
    
    const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
    const isMerchant = {{ (isset($merchantStall) && $merchantStall) ? 'true' : 'false' }};
    const merchantStallName = {!! (isset($merchantStall) && $merchantStall) ? json_encode($merchantStall->stall_name) : 'null' !!};

    // Keep track of last read message IDs for unread badges
    let lastReadMsgIds = JSON.parse(localStorage.getItem(`market_${marketId}_last_read_msgs`) || '{}');

    let chatInterval = null;
    function startChatPolling(ms = 3000) {
        if (chatInterval) clearInterval(chatInterval);
        chatInterval = setInterval(loadChatMessages, ms);
    }

    window.toggleFloatingChat = function() {
        const panel = document.getElementById('marketFloatingChatPanel');
        if (!panel) return;
        
        if (panel.style.display === 'none') {
            panel.style.display = 'flex';
            panel.offsetHeight; // force reflow
            panel.style.opacity = '1';
            panel.style.transform = 'translateY(0)';
            
            startChatPolling(3000);
            
            const msgInput = document.getElementById('chatMessageInput');
            if (msgInput) msgInput.focus();
            
            if (messagesWindow) {
                setTimeout(() => {
                    messagesWindow.scrollTop = messagesWindow.scrollHeight;
                }, 50);
            }
        } else {
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(20px)';
            saveReadProgress();
            
            setTimeout(() => {
                panel.style.display = 'none';
            }, 300);
            
            startChatPolling(10000);
        }
    };

    function switchChatRoom(roomType, stallName, customerId, customerName) {
        // Update read pointer for the room we are leaving
        saveReadProgress();

        activeRoomType = roomType;
        activeStallName = stallName || null;
        activeCustomerId = customerId || null;
        activeCustomerName = customerName || null;
        lastLoadedMsgId = 0; // force reload

        // Remove active class from all tabs
        document.querySelectorAll('.chat-room-tab').forEach(el => {
            el.classList.remove('active');
            el.style.borderColor = 'var(--border-glow)';
        });

        // Set active class on selected tab & clear its badge immediately
        if (roomType === 'public') {
            const tab = document.getElementById('tabPublicRoom');
            if (tab) {
                tab.classList.add('active');
                tab.style.borderColor = 'var(--primary)';
            }
            
            // Restore default header
            document.getElementById('chatRoomHeader').style.background = 'rgba(14, 165, 233, 0.04)';
            document.getElementById('chatRoomHeader').style.borderColor = 'rgba(14, 165, 233, 0.25)';
            document.getElementById('chatHeaderIcon').textContent = '📢';
            document.getElementById('chatHeaderSubtitle').textContent = 'Thông báo từ Ban Quản Lý';
            document.getElementById('chatHeaderContent').textContent = 'Chào mừng bà con đến với Cổng Chợ Số 4.0 ' + marketName + '. Hãy quét QR VietQR để thanh toán không tiền mặt và tích lũy điểm tiêu dùng xanh!';
        } else {
            if (isMerchant && customerId) {
                // Stall owner replying to customer
                const tab = document.getElementById(`tabCustomer_${customerId}`);
                if (tab) {
                    tab.classList.add('active');
                    tab.style.borderColor = 'var(--primary)';
                }
                
                // Hide unread badge dot immediately
                const badge = document.getElementById(`badgeCustomer_${customerId}`);
                if (badge) badge.style.display = 'none';

                document.getElementById('chatRoomHeader').style.background = 'rgba(16, 185, 129, 0.04)';
                document.getElementById('chatRoomHeader').style.borderColor = 'rgba(16, 185, 129, 0.25)';
                document.getElementById('chatHeaderIcon').textContent = '✉️';
                document.getElementById('chatHeaderSubtitle').textContent = 'Trả lời khách hàng';
                document.getElementById('chatHeaderContent').innerHTML = `Đang phản hồi riêng tư cho Khách hàng: <strong>${customerName}</strong>.`;
            } else {
                // Customer chatting with stall
                const safeId = stallName.replace(/[^a-zA-Z0-9]/g, '');
                const tab = document.getElementById(`tabStall_${safeId}`);
                if (tab) {
                    tab.classList.add('active');
                    tab.style.borderColor = 'var(--primary)';
                }
                
                // Hide unread badge dot immediately
                const badge = document.getElementById(`unreadStall_${safeId}`);
                if (badge) badge.style.display = 'none';

                document.getElementById('chatRoomHeader').style.background = 'rgba(245, 158, 11, 0.04)';
                document.getElementById('chatRoomHeader').style.borderColor = 'rgba(245, 158, 11, 0.25)';
                document.getElementById('chatHeaderIcon').textContent = '💬';
                document.getElementById('chatHeaderSubtitle').textContent = 'Hội thoại riêng';
                document.getElementById('chatHeaderContent').innerHTML = `Đang trò chuyện riêng tư với sạp: <strong>${stallName}</strong>. Tin nhắn chỉ có bạn và chủ sạp nhìn thấy.`;
            }
        }

        // Toggle input form / login prompt for private chat
        const chatForm = document.getElementById('marketChatForm');
        const loginPrompt = document.getElementById('chatLoginPrompt');
        
        if (roomType === 'private' && !isLoggedIn) {
            if (chatForm) chatForm.style.display = 'none';
            if (loginPrompt) loginPrompt.style.display = 'block';
        } else {
            if (chatForm) chatForm.style.display = 'flex';
            if (loginPrompt) loginPrompt.style.display = 'none';
        }

        // Trigger load
        loadChatMessages();
    }

    function saveReadProgress() {
        if (lastLoadedMsgId > 0) {
            let roomKey = 'public';
            if (activeRoomType === 'private') {
                roomKey = isMerchant ? 'customer_' + activeCustomerId : 'private_' + activeStallName;
            }
            lastReadMsgIds[roomKey] = lastLoadedMsgId;
            localStorage.setItem(`market_${marketId}_last_read_msgs`, JSON.stringify(lastReadMsgIds));
        }
    }

    function loadChatMessages() {
        if (!messagesWindow) return;
        
        // Build query string
        const url = new URL(`/api/market-chat/${marketId}/messages`, window.location.origin);
        url.searchParams.append('room', activeRoomType);
        if (activeStallName) url.searchParams.append('private_stall_name', activeStallName);
        if (activeCustomerId) url.searchParams.append('private_user_id', activeCustomerId);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const messages = data.messages;
                    if (messages.length === 0) {
                        if (activeRoomType === 'private' && !isLoggedIn) {
                            messagesWindow.innerHTML = `
                                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; color: var(--text-muted); text-align: center; padding: 20px;">
                                    <span style="font-size: 2.2rem; margin-bottom: 6px;">🔒</span>
                                    <strong style="font-size: 0.9rem; color: var(--text-main);">Hội thoại riêng tư</strong>
                                    <p style="font-size: 0.78rem; margin: 4px 0 0 0; max-width: 240px;">Vui lòng đăng nhập để bắt đầu hội thoại và xem tin nhắn riêng tư với sạp này.</p>
                                </div>
                            `;
                        } else {
                            messagesWindow.innerHTML = `
                                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; color: var(--text-muted); text-align: center; padding: 20px;">
                                    <span style="font-size: 2.2rem; margin-bottom: 6px;">💬</span>
                                    <strong style="font-size: 0.9rem; color: var(--text-main);">Chưa có tin nhắn nào</strong>
                                    <p style="font-size: 0.78rem; margin: 4px 0 0 0; max-width: 240px;">Hãy là người đầu tiên mở đầu cuộc trò chuyện!</p>
                                </div>
                            `;
                        }
                    } else {
                        let html = '';
                        let hasNew = false;
                        let lastDateGroup = '';
                        
                        messages.forEach(msg => {
                            if (msg.id > lastLoadedMsgId) {
                                hasNew = true;
                            }
                            
                            // Render Time Divider
                            if (msg.date_group !== lastDateGroup) {
                                html += `
                                    <div style="text-align: center; margin: 16px 0 8px 0; position: relative;">
                                        <div style="position: absolute; left: 0; right: 0; top: 50%; height: 1px; background: var(--border-glow); z-index: 1;"></div>
                                        <span style="position: relative; z-index: 2; background: var(--bg-card); border: 1px solid var(--border-glow); padding: 4px 12px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">
                                            ${msg.date_group}
                                        </span>
                                    </div>
                                `;
                                lastDateGroup = msg.date_group;
                            }
                            
                            const isOwn = msg.is_own;
                            
                            // Clean up sender name
                            let rawName = msg.sender_name || 'Khách vãng lai';
                            let cleanName = rawName.replace(/^Chủ sạp\s+/i, '').trim();

                            let avatarEmoji = '👤';
                            let avatarClass = 'chat-avatar-user';
                            let badgeHtml = '';
                            let displayName = cleanName;

                            if (msg.sender_role === 'merchant') {
                                avatarEmoji = '🏪';
                                avatarClass = 'chat-avatar-merchant';
                                displayName = msg.stall_name || cleanName;
                                const subName = (msg.stall_name && cleanName && msg.stall_name !== cleanName) ? ` (${cleanName})` : '';
                                badgeHtml = `<span class="chat-badge badge-merchant">🏪 Chủ sạp</span>`;
                                if (subName) {
                                    displayName += `<span style="font-weight: 500; opacity: 0.85; font-size: 0.7rem;">${subName}</span>`;
                                }
                            } else if (msg.sender_role === 'admin') {
                                avatarEmoji = '🛡️';
                                avatarClass = 'chat-avatar-admin';
                                displayName = msg.sender_name || 'BQL Chợ';
                                badgeHtml = `<span class="chat-badge badge-admin">🛡️ BQL</span>`;
                            } else {
                                avatarEmoji = '👤';
                                avatarClass = 'chat-avatar-user';
                                badgeHtml = `<span class="chat-badge badge-user">👤 Khách</span>`;
                            }

                            // Image attachment element
                            let imageAttachHtml = '';
                            if (msg.image_url) {
                                imageAttachHtml = `
                                    <div style="margin-top: 6px; max-width: 240px; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-glow);">
                                        <a href="${msg.image_url}" target="_blank">
                                            <img src="${msg.image_url}" style="width: 100%; max-height: 180px; object-fit: cover; cursor: zoom-in;" onerror="this.style.display='none'">
                                        </a>
                                    </div>
                                `;
                            }

                            let productCardHtml = '';
                            if (msg.product) {
                                productCardHtml = `
                                    <div class="chat-product-card-msg">
                                        <img src="${msg.product.image}" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;" onerror="this.src='https://placehold.co/44x44/00A86B/ffffff?text=Product'">
                                        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px;">
                                            <strong style="font-size: 0.78rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; color: inherit;">${msg.product.name}</strong>
                                            <span style="font-size: 0.75rem; font-weight: 850; color: #FF7A00;">${new Intl.NumberFormat('vi-VN').format(msg.product.price)}đ</span>
                                            <button type="button" class="btn-pay-stall" onclick="event.preventDefault(); event.stopPropagation(); addProductToCart(${msg.product.id}, this)" style="padding: 4px 8px; font-size: 0.68rem; font-weight: 700; border-radius: 6px; margin-top: 2px; width: fit-content; border: none; cursor: pointer; display: flex; align-items: center; gap: 3px;">
                                                🛒 Thêm vào giỏ
                                            </button>
                                        </div>
                                    </div>
                                `;
                            }

                            const headerHtml = isOwn ? `
                                <div class="chat-bubble-header">
                                    <span style="font-size: 0.68rem; color: var(--text-muted); margin-right: 2px;">${msg.time_formatted}</span>
                                    <strong style="color: var(--text-main); font-weight: 700;">${cleanName}</strong>
                                    ${badgeHtml}
                                </div>
                            ` : `
                                <div class="chat-bubble-header">
                                    ${badgeHtml}
                                    <strong style="color: var(--text-main); font-weight: 700;">${displayName}</strong>
                                    <span style="font-size: 0.68rem; color: var(--text-muted); margin-left: 2px;">${msg.time_formatted}</span>
                                </div>
                            `;

                            html += `
                                <div class="chat-bubble-row ${isOwn ? 'own-message' : ''}">
                                    <div class="chat-avatar-circle ${avatarClass}">${avatarEmoji}</div>
                                    <div class="chat-bubble-content-wrap">
                                        ${headerHtml}
                                        <div class="chat-bubble-text">
                                            ${msg.message_text ? `<div>${msg.message_text}</div>` : ''}
                                            ${imageAttachHtml}
                                            ${productCardHtml}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        messagesWindow.innerHTML = html;
                        
                        if (messages.length > 0) {
                            const latestId = messages[messages.length - 1].id;
                            lastLoadedMsgId = latestId;
                            
                            // Save current room progress immediately
                            let currentRoomKey = 'public';
                            if (activeRoomType === 'private') {
                                currentRoomKey = isMerchant ? 'customer_' + activeCustomerId : 'private_' + activeStallName;
                            }
                            lastReadMsgIds[currentRoomKey] = latestId;
                            localStorage.setItem(`market_${marketId}_last_read_msgs`, JSON.stringify(lastReadMsgIds));
                        }

                        if (hasNew) {
                            setTimeout(() => {
                                messagesWindow.scrollTop = messagesWindow.scrollHeight;
                            }, 50);
                        }
                    }

                    // 1. Process Unread Badge indicators for Stalls in sidebar
                    if (data.latest_message_ids) {
                        document.querySelectorAll('.chat-stall-card-item').forEach(card => {
                            const rawStall = card.getAttribute('data-stall-raw-name');
                            if (rawStall) {
                                const key = 'private_' + rawStall;
                                const latestId = data.latest_message_ids[key] || 0;
                                const readId = lastReadMsgIds[key] || 0;
                                const dot = card.querySelector('.unread-badge-dot');
                                
                                if (dot) {
                                    if (activeStallName !== rawStall && latestId > readId) {
                                        dot.style.display = 'block';
                                        card.style.fontWeight = '700';
                                    } else {
                                        dot.style.display = 'none';
                                        card.style.fontWeight = 'normal';
                                    }
                                }
                            }
                        });
                    }

                    // 2. Dynamically update active chat list for merchants
                    if (isMerchant && data.active_chats) {
                        const listEl = document.getElementById('merchantCustomersList');
                        if (listEl) {
                            if (data.active_chats.length === 0) {
                                listEl.innerHTML = '<div style="font-size: 0.72rem; color: var(--text-muted); text-align: center; padding: 10px 0;">Chưa có khách hàng nhắn riêng</div>';
                            } else {
                                let listHtml = '';
                                data.active_chats.forEach(chat => {
                                    const isActive = activeCustomerId === chat.id;
                                    
                                    // Check unread count for this customer
                                    const key = 'customer_' + chat.id;
                                    const latestId = (data.latest_message_ids && data.latest_message_ids[key]) ? data.latest_message_ids[key] : 0;
                                    const readId = lastReadMsgIds[key] || 0;
                                    const isUnread = !isActive && latestId > readId;

                                    listHtml += `
                                        <div class="merchant-active-card chat-room-tab ${isActive ? 'active' : ''}" id="tabCustomer_${chat.id}" onclick="switchChatRoom('private', merchantStallName, ${chat.id}, '${chat.name}')" style="cursor: pointer; position: relative; ${isActive ? 'border-color: var(--primary);' : ''}">
                                            <span style="font-size: 1.35rem;">👤</span>
                                            <div style="flex: 1; min-width: 0;">
                                                <strong style="font-size: 0.82rem; color: var(--text-main); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; ${isUnread ? 'font-weight: 800;' : ''}">
                                                    ${chat.name}
                                                </strong>
                                                <span style="font-size: 0.72rem; color: var(--text-muted); display: block;">Khách nhắn riêng</span>
                                            </div>
                                            ${isUnread ? `<span class="unread-badge-dot" id="badgeCustomer_${chat.id}" style="position: absolute; top: 10px; right: 10px; width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>` : ''}
                                        </div>
                                    `;
                                });
                                listEl.innerHTML = listHtml;
                            }
                        }
                    }

                    // 3. Calculate and show total unread messages count on the floating bubble trigger badge
                    if (data.latest_message_ids) {
                        let totalUnread = 0;
                        
                        // Check public room
                        const publicLatest = data.latest_message_ids['public'] || 0;
                        const publicRead = lastReadMsgIds['public'] || 0;
                        if (activeRoomType !== 'public' && publicLatest > publicRead) {
                            totalUnread += 1;
                        }
                        
                        // Check private channels
                        for (const key in data.latest_message_ids) {
                            if (key !== 'public') {
                                const latest = data.latest_message_ids[key] || 0;
                                const read = lastReadMsgIds[key] || 0;
                                
                                let isCurrentChannel = false;
                                if (activeRoomType === 'private') {
                                    if (key.startsWith('private_') && activeStallName && key === 'private_' + activeStallName) {
                                        isCurrentChannel = true;
                                    } else if (key.startsWith('customer_') && activeCustomerId && key === 'customer_' + activeCustomerId) {
                                        isCurrentChannel = true;
                                    }
                                }
                                
                                if (!isCurrentChannel && latest > read) {
                                    totalUnread += 1;
                                }
                            }
                        }
                        
                        const bubbleBadge = document.getElementById('chatBubbleUnreadBadge');
                        if (bubbleBadge) {
                            if (totalUnread > 0) {
                                bubbleBadge.textContent = totalUnread;
                                bubbleBadge.style.display = 'flex';
                                bubbleBadge.style.alignItems = 'center';
                                bubbleBadge.style.justifyContent = 'center';
                            } else {
                                bubbleBadge.style.display = 'none';
                            }
                        }
                    }
                }
            })
            .catch(err => console.error('Error loading chat messages:', err));
    }

    // Add to cart helper within chat
    window.addProductToCart = function(productId, btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳...';
        
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ocop_product_id: productId,
                quantity: 1
            })
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '🛒 Thêm vào giỏ';
            if (res.success) {
                showCartToast('Đã thêm sản phẩm vào giỏ hàng');
                updateCartBadge(res.count);
                
                if (typeof animateFlyToCart === 'function') {
                    animateFlyToCart(btn, '🍲');
                }
            } else {
                showCartToast(res.message || 'Lỗi thêm vào giỏ hàng', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '🛒 Thêm vào giỏ';
            console.error(err);
            showCartToast('Lỗi kết nối mạng', 'error');
        });
    };

    // Attach product from Merchant stall dropdown
    window.attachProduct = function(id, name, price, img) {
        document.getElementById('chatAttachedProductId').value = id;
        document.getElementById('chatAttachedName').textContent = name;
        document.getElementById('chatAttachedPrice').textContent = new Intl.NumberFormat('vi-VN').format(price) + 'đ';
        if (img) {
            document.getElementById('chatAttachedImg').src = img;
        } else {
            document.getElementById('chatAttachedImg').src = 'https://placehold.co/44x44/00A86B/ffffff?text=OCOP';
        }
        document.getElementById('chatProductPreview').style.display = 'block';
        
        const dropdown = document.getElementById('merchantProductDropdown');
        if (dropdown) dropdown.style.display = 'none';
    };

    // Attach product by clicking on any Merchant Active Card in the sidebar
    window.attachProductFromStall = function(stallName) {
        if (typeof allStallsData !== 'undefined') {
            const stall = allStallsData.find(s => s.name === stallName);
            if (stall && stall.products && stall.products.length > 0) {
                const prod = stall.products[0];
                const imgUrl = prod.image_path ? `/${prod.image_path}` : '';
                attachProduct(prod.id, prod.name, parseFloat(prod.price), imgUrl);
                showCartToast(`Đã gắn kèm "${prod.name}" từ ${stallName}!`);
            } else {
                showCartToast('Sạp này chưa cập nhật danh sách sản phẩm OCOP', 'error');
            }
        }
    };

    window.removeAttachedProduct = function() {
        document.getElementById('chatAttachedProductId').value = '';
        document.getElementById('chatProductPreview').style.display = 'none';
    };

    window.toggleMerchantProductSelector = function() {
        const dropdown = document.getElementById('merchantProductDropdown');
        if (dropdown) {
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }
    };

    // Search bar filtering
    window.filterChatStalls = function() {
        const query = document.getElementById('chatStallSearch').value.toLowerCase().trim();
        
        document.querySelectorAll('.chat-stall-card-item').forEach(card => {
            const name = card.getAttribute('data-stall-name');
            const seller = card.getAttribute('data-seller-name');
            
            if (name.includes(query) || seller.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // Hide category groups with zero visible items
        document.querySelectorAll('.chat-category-group').forEach(group => {
            const stalls = group.querySelectorAll('.chat-stall-card-item');
            let hasVisible = false;
            stalls.forEach(s => {
                if (s.style.display !== 'none') hasVisible = true;
            });
            
            if (hasVisible) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    };

    // Image upload handler
    window.handleChatImageSelect = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 5 * 1024 * 1024) {
                showCartToast('Ảnh tải lên không được vượt quá 5MB', 'error');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('chatAttachedImgFile').src = e.target.result;
                document.getElementById('chatImagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    };

    window.removeAttachedImage = function() {
        document.getElementById('chatImageInputFile').value = '';
        document.getElementById('chatImagePreview').style.display = 'none';
    };

    // Quick replies helper
    window.sendQuickReply = function(text) {
        const msgInput = document.getElementById('chatMessageInput');
        if (msgInput) {
            msgInput.value = text;
            sendChatMessage();
        }
    };

    // Advanced FormData submission
    window.sendChatMessage = function(e) {
        if (e) e.preventDefault();
        
        const msgInput = document.getElementById('chatMessageInput');
        const guestNameInput = document.getElementById('chatGuestName');
        const productIdInput = document.getElementById('chatAttachedProductId');
        const fileInput = document.getElementById('chatImageInputFile');

        if (!msgInput.value && !productIdInput.value && (!fileInput || !fileInput.files[0])) {
            return;
        }

        const formData = new FormData();
        formData.append('message_text', msgInput.value || '');
        if (productIdInput.value) {
            formData.append('product_id', productIdInput.value);
        }
        if (fileInput && fileInput.files[0]) {
            formData.append('image', fileInput.files[0]);
        }
        if (guestNameInput) {
            formData.append('sender_name', guestNameInput.value);
        }

        // Include private chat context
        if (activeRoomType === 'private') {
            if (isMerchant) {
                formData.append('private_user_id', activeCustomerId);
                formData.append('private_stall_name', merchantStallName);
            } else {
                formData.append('private_stall_name', activeStallName);
            }
        }

        // Button sending state
        const sendBtn = document.querySelector('.btn-send-chat');
        const originalText = sendBtn ? sendBtn.innerHTML : 'Gửi';
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '⏳ Gửi...';
        }

        fetch(`/api/market-chat/${marketId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalText;
            }
            if (data.success) {
                msgInput.value = '';
                removeAttachedProduct();
                removeAttachedImage();
                loadChatMessages();
            } else {
                alert('Lỗi gửi tin nhắn: ' + (data.message || 'vui lòng thử lại'));
            }
        })
        .catch(err => {
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalText;
            }
            console.error('Error sending message:', err);
            alert('Không thể kết nối máy chủ để gửi tin nhắn.');
        });
    };

    let mainMap;
    let blockPolygons = {};
    let tempStallMarker = null;
    const eateryLat = {{ $eatery->latitude }};
    const eateryLng = {{ $eatery->longitude }};

    const allStallsData = [
        @foreach($groupedStalls as $stallName => $stallProducts)
            @php
                $first = $stallProducts->first();
                $sellerName = '';
                if (preg_match('/Chủ hộ: (.*?)\./', $first->description, $matches)) {
                    $sellerName = $matches[1];
                }
                
                $category = 'Khác';
                if (str_contains($stallName, 'Ăn uống') || str_contains($stallName, 'Ăn sáng') || str_contains($stallName, 'Ẩm thực')) {
                    $category = 'Ăn uống';
                } elseif (str_contains($stallName, 'Rau củ') || str_contains($stallName, 'Rau')) {
                    $category = 'Rau củ';
                } elseif (str_contains($stallName, 'Thực phẩm khô') || str_contains($stallName, 'Hoa quả')) {
                    $category = 'Thực phẩm khô';
                } elseif (str_contains($stallName, 'Thịt') || str_contains($stallName, 'Giò chả')) {
                    $category = 'Thịt tươi';
                }
                
                $hasSmartphone = str_contains($first->description, 'Có sử dụng smartphone');
                
                $bankInfo = '';
                if (preg_match('/ngân hàng (.*?)\./', $first->description, $matches)) {
                    $bankInfo = $matches[1];
                }
                
                $originText = 'Tự sản xuất';
                if (preg_match('/Nguồn gốc: (.*?)\./', $first->description, $match)) {
                    $originText = trim($match[1]);
                }
                $sellerPhone = $first->phone ?? '';
                $safeStallSlugMap = \Illuminate\Support\Str::slug(!empty(trim($stallName)) ? $stallName : ($first->name ?: 'Gian hàng')) ?: ('gian-hang-' . ($first->id ?? '1'));
            @endphp
            {
                name: {!! json_encode($stallName) !!},
                seller: {!! json_encode($sellerName) !!},
                phone: {!! json_encode($sellerPhone) !!},
                bank: {!! json_encode($bankInfo) !!},
                category: "{{ $category }}",
                origin: {!! json_encode($originText) !!},
                smartphone: "{{ $hasSmartphone ? 'yes' : 'no' }}",
                products: {!! json_encode($stallProducts) !!},
                lat: "{{ $first->latitude ?? '' }}",
                lng: "{{ $first->longitude ?? '' }}",
                stallSlug: {!! json_encode($safeStallSlugMap) !!},
                marketSlug: {!! json_encode($eatery->slug) !!}
            },
        @endforeach
    ];

    let markerClusterGroup;

    function openStallFromMap(idx) {
        const s = allStallsData[idx];
        openStallDetail(s.name, s.seller, s.phone, s.bank, s.category, s.origin, s.smartphone, s.products, s.lat, s.lng);
    }

    function highlightStallCard(stallSlug) {
        const card = document.getElementById(`stall-card-${stallSlug}`);
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.remove('highlight-stall-card');
            void card.offsetWidth; // trigger reflow
            card.classList.add('highlight-stall-card');
        }
    }

    const categoryCounts = {};
    function getStallCoords(category, sellerName) {
        categoryCounts[category] = (categoryCounts[category] || 0) + 1;
        const catIdx = categoryCounts[category];

        let baseLat = eateryLat;
        let baseLng = eateryLng;
        if (category === 'Ăn uống') {
            baseLat += 0.00045; baseLng -= 0.00045;
        } else if (category === 'Rau củ') {
            baseLat += 0.00045; baseLng += 0.00045;
        } else if (category === 'Thực phẩm khô') {
            baseLat -= 0.00045; baseLng -= 0.00045;
        } else if (category === 'Thịt tươi') {
            baseLat -= 0.00045; baseLng += 0.00045;
        }
        
        // Spread out stalls in a 2-column grid inside their block with generous spacing
        const row = Math.floor((catIdx - 1) / 2);
        const col = (catIdx - 1) % 2;

        const offsetLat = (row - 0.5) * 0.00025;
        const offsetLng = (col - 0.5) * 0.00025;

        return [baseLat + offsetLat, baseLng + offsetLng];
    }

    function initSatelliteMap() {
        mainMap = L.map('miniMap').setView([eateryLat, eateryLng], 17);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mainMap);

        // Initialize MarkerClusterGroup safely if available
        if (typeof L.markerClusterGroup === 'function') {
            markerClusterGroup = L.markerClusterGroup({
                showCoverageOnHover: false,
                maxClusterRadius: 45,
                spiderfyOnMaxZoom: true,
                iconCreateFunction: function(cluster) {
                    const count = cluster.getChildCount();
                    return L.divIcon({
                        html: `<div style="background: linear-gradient(135deg, #0ea5e9, #06b6d4); color: white; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem; border: 3px solid white; box-shadow: 0 4px 18px rgba(14,165,233,0.45); font-family: 'Be Vietnam Pro', sans-serif;">
                            ${count}
                        </div>`,
                        className: 'custom-cluster-marker',
                        iconSize: [44, 44],
                        iconAnchor: [22, 22]
                    });
                }
            });
            mainMap.addLayer(markerClusterGroup);
        }

        // Market center pin
        const customIcon = L.divIcon({
            html: `<div style="background: linear-gradient(135deg,#0ea5e9,#06b6d4); width: 44px; height: 44px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 20px rgba(14,165,233,0.5); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; animation: pulse 2s infinite;">🏪</div>`,
            className: 'custom-leaflet-marker',
            iconSize: [44, 44],
            iconAnchor: [22, 44]
        });
        
        L.marker([eateryLat, eateryLng], { icon: customIcon })
            .addTo(mainMap)
            .bindPopup(`<strong style="font-family: var(--font-heading); font-size:1.05rem; color: var(--primary);">{{ $eatery->name }}</strong><br><span style="font-size:0.8rem; color:#64748B;">📍 Trung tâm Chợ · {{ $totalStalls }} hộ kinh doanh</span>`);

        // Draw Block Polygons with modern smooth fill and clean stroke
        // Block A (Blue)
        const blockACoords = [
            [eateryLat + 0.00075, eateryLng - 0.00075],
            [eateryLat + 0.00075, eateryLng - 0.00010],
            [eateryLat + 0.00010, eateryLng - 0.00010],
            [eateryLat + 0.00010, eateryLng - 0.00075]
        ];
        blockPolygons['A'] = L.polygon(blockACoords, {
            color: '#0EA5E9',
            fillColor: '#0EA5E9',
            fillOpacity: 0.12,
            weight: 2
        }).addTo(mainMap).bindPopup("🍲 <strong>Khối A - Khu Ẩm thực</strong>");

        // Block B (Green)
        const blockBCoords = [
            [eateryLat + 0.00075, eateryLng + 0.00010],
            [eateryLat + 0.00075, eateryLng + 0.00075],
            [eateryLat + 0.00010, eateryLng + 0.00075],
            [eateryLat + 0.00010, eateryLng + 0.00010]
        ];
        blockPolygons['B'] = L.polygon(blockBCoords, {
            color: '#10B981',
            fillColor: '#10B981',
            fillOpacity: 0.12,
            weight: 2
        }).addTo(mainMap).bindPopup("🥦 <strong>Khối B - Khu Rau củ sạch</strong>");

        // Block C (Orange)
        const blockCCoords = [
            [eateryLat - 0.00010, eateryLng - 0.00075],
            [eateryLat - 0.00010, eateryLng - 0.00010],
            [eateryLat - 0.00075, eateryLng - 0.00010],
            [eateryLat - 0.00075, eateryLng - 0.00075]
        ];
        blockPolygons['C'] = L.polygon(blockCCoords, {
            color: '#F59E0B',
            fillColor: '#F59E0B',
            fillOpacity: 0.12,
            weight: 2
        }).addTo(mainMap).bindPopup("🥜 <strong>Khối C - Đồ khô & Gia vị</strong>");

        // Block D (Pink)
        const blockDCoords = [
            [eateryLat - 0.00010, eateryLng + 0.00010],
            [eateryLat - 0.00010, eateryLng + 0.00075],
            [eateryLat - 0.00075, eateryLng + 0.00075],
            [eateryLat - 0.00075, eateryLng + 0.00010]
        ];
        blockPolygons['D'] = L.polygon(blockDCoords, {
            color: '#EC4899',
            fillColor: '#EC4899',
            fillOpacity: 0.12,
            weight: 2
        }).addTo(mainMap).bindPopup("🥩 <strong>Khối D - Thực phẩm tươi sống</strong>");

        // Draw Stall Markers
        allStallsData.forEach((s, idx) => {
            let sLat, sLng;
            if (s.lat && s.lng && !isNaN(parseFloat(s.lat)) && !isNaN(parseFloat(s.lng))) {
                sLat = parseFloat(s.lat);
                sLng = parseFloat(s.lng);
            } else {
                [sLat, sLng] = getStallCoords(s.category, s.seller);
            }
            
            let emoji = '🏪';
            let bgColor = '#0ea5e9';
            if (s.category === 'Ăn uống') { emoji = '🍲'; bgColor = '#0EA5E9'; }
            else if (s.category === 'Rau củ') { emoji = '🥦'; bgColor = '#10B981'; }
            else if (s.category === 'Thực phẩm khô') { emoji = '🥜'; bgColor = '#F59E0B'; }
            else if (s.category === 'Thịt tươi') { emoji = '🥩'; bgColor = '#EC4899'; }

            // Compact 36px circular pin badge (Zero text overlap on map)
            const stallIcon = L.divIcon({
                html: `
                    <div style="background: ${bgColor}; width: 36px; height: 36px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; border: 2.5px solid #ffffff; box-shadow: 0 4px 14px rgba(0,0,0,0.3); transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='scale(1.25)';" onmouseout="this.style.transform='none';">
                        ${emoji}
                    </div>
                `,
                className: 'custom-stall-circle-pin',
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${sLat},${sLng}`;
            const stallUrl = `/cho/${s.marketSlug}/gian-hang/${s.stallSlug}`;

            const marker = L.marker([sLat, sLng], { icon: stallIcon });
            
            // Hover Tooltip: Text only pops up on hover
            marker.bindTooltip(`
                <div style="font-family: 'Be Vietnam Pro', sans-serif; font-size: 0.8rem; padding: 2px 4px;">
                    <strong style="color: var(--text-main); font-weight:800;">${emoji} ${s.name}</strong><br>
                    <span style="color:#64748B; font-size: 0.75rem;">👤 Chủ hộ: ${s.seller}</span>
                </div>
            `, { direction: 'top', offset: [0, -18] });

            // Click Popup: Full options & 2-way sync button
            marker.bindPopup(`
                <div style="font-family: 'Be Vietnam Pro', sans-serif; padding: 6px; min-width: 220px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom: 8px;">
                        <span style="font-size: 1.5rem;">${emoji}</span>
                        <div>
                            <strong style="font-size: 0.95rem; color: var(--text-main); display:block; line-height: 1.2;">${s.name}</strong>
                            <span style="font-size: 0.75rem; color:#64748B; font-weight: 600;">👤 Chủ hộ: ${s.seller}</span>
                        </div>
                    </div>
                    <div style="border-top: 1px dashed var(--border-glow); padding-top: 8px; margin-top: 4px; display:flex; flex-direction:column; gap:6px;">
                        <button onclick="highlightStallCard('${s.stallSlug}')" style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.2); color: var(--primary); border-radius: 8px; font-weight:800; font-size: 0.78rem; padding: 7px; cursor:pointer; width:100%; display:flex; align-items:center; justify-content:center; gap:5px;" onmouseover="this.style.background='var(--primary)'; this.style.color='#ffffff';" onmouseout="this.style.background='rgba(14, 165, 233, 0.08)'; this.style.color='var(--primary)';">
                            🎯 Định vị thẻ sạp bên dưới
                        </button>
                        <a href="${stallUrl}" style="background: var(--primary-grad); color: white; border-radius: 8px; font-weight:800; font-size: 0.78rem; padding: 8px; text-decoration:none !important; text-align:center; display:block; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);">
                            🚀 Vào gian hàng chi tiết ➔
                        </a>
                        <a href="${googleMapsUrl}" target="_blank" style="display:flex; align-items:center; justify-content:center; gap:4px; background: rgba(0, 0, 0, 0.03); color: #64748B; padding: 6px; border-radius: 8px; font-weight:700; font-size: 0.72rem; text-decoration:none; border: 1px solid rgba(0,0,0,0.08);">
                            🗺️ Google Maps chỉ đường
                        </a>
                    </div>
                </div>
            `);

            if (markerClusterGroup) {
                markerClusterGroup.addLayer(marker);
            } else {
                marker.addTo(mainMap);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initSatelliteMap();
        setTimeout(function() {
            if (mainMap) {
                mainMap.invalidateSize();
            }
        }, 500);
    });

    function highlightBlockOnMap(blockLetter) {
        let lat, lng;
        if (blockLetter === 'A') {
            lat = eateryLat + 0.000175; lng = eateryLng - 0.000175;
        } else if (blockLetter === 'B') {
            lat = eateryLat + 0.000175; lng = eateryLng + 0.000175;
        } else if (blockLetter === 'C') {
            lat = eateryLat - 0.000175; lng = eateryLng - 0.000175;
        } else if (blockLetter === 'D') {
            lat = eateryLat - 0.000175; lng = eateryLng + 0.000175;
        }
        
        if (mainMap && blockPolygons[blockLetter]) {
            mainMap.setView([lat, lng], 18, { animate: true, duration: 1 });
            setTimeout(() => {
                blockPolygons[blockLetter].openPopup();
            }, 600);
            document.getElementById('miniMap').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function showStallOnMap(stallName, sellerName, category) {
        const [sLat, sLng] = getStallCoords(category, sellerName);
        const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${sLat},${sLng}`;
        
        if (tempStallMarker) {
            mainMap.removeLayer(tempStallMarker);
        }
        
        const tempStallIcon = L.divIcon({
            html: `
                <div class="pulse-marker" style="background: #E11D48; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 15px #E11D48; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: white;">
                    🏪
                </div>
            `,
            className: 'temp-stall-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 32]
        });
        
        tempStallMarker = L.marker([sLat, sLng], { icon: tempStallIcon })
            .addTo(mainMap)
            .bindPopup(`
                <div style="font-family: var(--font-heading); padding: 5px; min-width: 170px;">
                    <strong style="font-size: 0.95rem; color: var(--primary); display:block; margin-bottom: 4px;">${stallName}</strong>
                    <span style="font-size: 0.8rem; color:#64748B; display:block; margin-bottom: 8px;">👤 Chủ hộ: ${sellerName}<br>📍 Ngành: ${category}</span>
                    <a href="${googleMapsUrl}" target="_blank" style="display:inline-flex; align-items:center; gap:6px; background: var(--primary); color: white; padding: 6px 12px; border-radius: 8px; font-weight:700; font-size: 0.75rem; text-decoration:none; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.2); width: 100%; justify-content: center;">
                        🗺️ Chỉ đường Google Maps
                    </a>
                </div>
            `)
            .addTo(mainMap);
            
        document.getElementById('miniMap').scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            mainMap.setView([sLat, sLng], 19, { animate: true, duration: 1.2 });
            setTimeout(() => {
                tempStallMarker.openPopup();
            }, 600);
        }, 300);
    }

    function showStallOnMapFromModal() {
        const stallName = document.getElementById('mdStallName').textContent;
        const sellerName = document.getElementById('mdSellerName').textContent;
        let category = document.getElementById('mdCategoryBadge').textContent;
        // Normalize category mapping
        if (category === 'ĂN UỐNG') category = 'Ăn uống';
        else if (category === 'RAU CỦ') category = 'Rau củ';
        else if (category === 'THỰC PHẨM KHÔ') category = 'Thực phẩm khô';
        else if (category === 'THỊT TƯƠI') category = 'Thịt tươi';

        closeStallDetail();
        setTimeout(() => {
            showStallOnMap(stallName, sellerName, category);
        }, 300);
    }

    function toggleMapFullscreen() {
        const mapContainer = document.getElementById('miniMap');
        if (!document.fullscreenElement) {
            mapContainer.requestFullscreen().catch(err => {
                alert(`Không thể bật chế độ toàn màn hình: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    }

    let activeBlock = null;

    function filterByBlock(category, blockLetter) {
        const blocks = document.querySelectorAll('.interactive-block-2d');
        blocks.forEach(b => b.classList.remove('active'));

        const clickedBlock = document.getElementById(`block-${blockLetter}`);
        
        if (activeBlock === category) {
            activeBlock = null;
            document.getElementById('filterCategory').value = '';
        } else {
            activeBlock = category;
            clickedBlock.classList.add('active');
            document.getElementById('filterCategory').value = category;
            highlightBlockOnMap(blockLetter); // Pan and highlight block on OSM Leaflet map!
        }

        applyFilters();
    }

    function applyFilters() {
        const nameVal = document.getElementById('searchName').value.toLowerCase().trim();
        const prodVal = document.getElementById('searchProduct').value.toLowerCase().trim();
        const catVal = document.getElementById('filterCategory').value;
        const qrVal = document.getElementById('filterQr').value;
        const phoneVal = document.getElementById('filterPhone').value;
        const originVal = document.getElementById('filterOrigin').value.toLowerCase();

        const stallCards = document.querySelectorAll('.stall-card-wrapper');

        stallCards.forEach(card => {
            const name = card.dataset.name;
            const seller = card.dataset.seller;
            const category = card.dataset.category;
            const qr = card.dataset.qr;
            const phone = card.dataset.phone;
            const origin = card.dataset.origin;
            const products = card.dataset.products;

            let matches = true;

            if (nameVal && !name.includes(nameVal) && !seller.includes(nameVal)) matches = false;
            if (prodVal && !products.includes(prodVal)) matches = false;
            if (catVal && category !== catVal) matches = false;
            if (qrVal && qr !== qrVal) matches = false;
            if (phoneVal && phone !== phoneVal) matches = false;
            if (originVal && !origin.includes(originVal)) matches = false;

            if (matches) {
                card.style.display = 'block';
                card.style.opacity = '1';
            } else {
                card.style.display = 'none';
                card.style.opacity = '0';
            }
        });
    }

    function resetAdvancedFilters() {
        document.getElementById('searchName').value = '';
        document.getElementById('searchProduct').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterQr').value = '';
        document.getElementById('filterPhone').value = '';
        document.getElementById('filterOrigin').value = '';
        
        const blocks = document.querySelectorAll('.interactive-block-2d');
        blocks.forEach(b => b.classList.remove('active'));
        activeBlock = null;

        applyFilters();
    }

    function openStallDetail(stallName, sellerName, sellerPhone, bankInfo, category, originText, hasSmartphone, products, lat, lng) {
        const modal = document.getElementById('stallDetailModal');
        modal.dataset.lat = lat || '';
        modal.dataset.lng = lng || '';
        document.getElementById('mdStallName').textContent = stallName;
        document.getElementById('mdCategoryBadge').textContent = category;
        document.getElementById('mdSellerName').textContent = sellerName;
        document.getElementById('mdSellerPhone').textContent = sellerPhone;
        document.getElementById('mdSmartphone').textContent = hasSmartphone === 'yes' ? 'Có sử dụng' : 'Không sử dụng';
        document.getElementById('mdOriginText').textContent = originText;
        
        let blockStr = 'Khác';
        if (category === 'Ăn uống') blockStr = 'Khối A - Gian hàng Ẩm thực';
        else if (category === 'Rau củ') blockStr = 'Khối B - Nông sản sạch';
        else if (category === 'Thực phẩm khô') blockStr = 'Khối C - Đồ khô & Gia vị';
        else if (category === 'Thịt tươi') blockStr = 'Khối D - Thực phẩm tươi sống';
        document.getElementById('mdZone').textContent = blockStr;

        const isCultureMarketJs = {{ $isCultureMarket ? 'true' : 'false' }};
        const prodList = document.getElementById('mdProductsList');
        prodList.innerHTML = '';
        products.forEach(p => {
            const priceFormatted = p.price ? `${new Intl.NumberFormat('vi-VN').format(p.price)}đ` : 'Trưng bày';
            const originSpan = isCultureMarketJs ? '' : `<span style="font-size: 0.72rem; color: var(--text-muted);">🌾 Nguồn gốc: ${originText}</span>`;
            const buyBtn = isCultureMarketJs ? '' : `<button class="add-to-cart-btn" data-id="${p.id}" data-type="ocop_product" onclick="addToCart(event, this); animateFlyToCart(this);" style="background: var(--primary); border: none; color: white; border-radius: 8px; padding: 6px 12px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='none';">+ Chọn mua</button>`;
            
            prodList.innerHTML += `
                <div class="modal-product-item" style="border-left: 4px solid var(--primary); display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                    <div style="flex: 1;">
                        <span style="font-weight: 700; color: var(--text-main); display: block; font-size: 0.85rem;">${p.name}</span>
                        ${originSpan}
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
                        <span style="font-family: var(--font-heading); font-size: 0.9rem; font-weight: 900; color: var(--primary);">
                            ${priceFormatted}
                        </span>
                        ${buyBtn}
                    </div>
                </div>
            `;
        });

        const qrImage = document.getElementById('mdQrImage');
        const qrLoader = document.getElementById('mdQrLoader');
        const qrBankText = document.getElementById('mdBankInfo');

        if (bankInfo && bankInfo.trim()) {
            qrBankText.textContent = `Ngân hàng: ${bankInfo}`;
            
            // Smart extraction of bank name and account number
            let account = '';
            const accMatch = bankInfo.match(/(\d{6,22})/);
            if (accMatch) {
                account = accMatch[1];
            }
            
            const bankLower = bankInfo.toLowerCase();
            let bankId = 'MB';
            if (bankLower.includes('vietcom') || bankLower.includes('vcb')) bankId = 'VCB';
            else if (bankLower.includes('vietin') || bankLower.includes('icb') || bankLower.includes('ctg')) bankId = 'CTG';
            else if (bankLower.includes('techcom') || bankLower.includes('tcb')) bankId = 'TCB';
            else if (bankLower.includes('bidv')) bankId = 'BIDV';
            else if (bankLower.includes('agri') || bankLower.includes('vba')) bankId = 'VBA';
            else if (bankLower.includes('sacom') || bankLower.includes('stb')) bankId = 'STB';
            else if (bankLower.includes('momo')) bankId = 'MOMO';
            else if (bankLower.includes('msb')) bankId = 'MSB';
            else if (bankLower.includes('vp')) bankId = 'VPB';
            else if (bankLower.includes('tp')) bankId = 'TPB';
            else bankId = 'MB';
            
            if (!account && bankInfo.includes(':')) {
                const parts = bankInfo.split(':');
                account = parts[1].trim();
            }
            
            if (account) {
                const memo = encodeURIComponent(`Thanh toan quay ${stallName}`);
                const price = (products && products[0] && products[0].price) ? products[0].price : '';
                const qrUrl = `https://img.vietqr.io/image/${bankId}-${account}-compact2.jpg?amount=${price}&addInfo=${memo}`;
                
                qrImage.src = qrUrl;
                qrLoader.style.display = 'flex';
                qrImage.style.display = 'none';
                
                qrImage.onload = function() {
                    qrLoader.style.display = 'none';
                    qrImage.style.display = 'block';
                };
                qrImage.onerror = function() {
                    qrLoader.style.display = 'flex';
                    qrLoader.textContent = '💳 Chuyển khoản: ' + account;
                    qrImage.style.display = 'none';
                };
            } else {
                qrBankText.textContent = `Thanh toán: ${bankInfo}`;
                qrImage.src = '';
                qrLoader.style.display = 'flex';
                qrLoader.textContent = '💳 ' + bankInfo;
                qrImage.style.display = 'none';
            }
        } else {
            qrBankText.textContent = 'Thanh toán tiền mặt';
            qrImage.src = '';
            qrLoader.style.display = 'flex';
            qrLoader.textContent = '✕ Tiền mặt';
            qrImage.style.display = 'none';
        }

        // Fetch Stall Reviews from database
        const reviewsList = document.getElementById('mdReviewsList');
        if (reviewsList) {
            reviewsList.innerHTML = '<div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 10px 0;"><div style="width: 16px; height: 16px; border: 1.5px solid rgba(0,0,0,0.05); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; margin-right: 6px; vertical-align: middle;"></div> Đang tải đánh giá...</div>';
            
            fetch(`/api/market-stalls/${marketId}/reviews?stall_name=${encodeURIComponent(stallName)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.reviews.length === 0) {
                            reviewsList.innerHTML = '<div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 15px 0;">Chưa có đánh giá nào cho quầy hàng này. Hãy là người đầu tiên đánh giá!</div>';
                        } else {
                            let html = '';
                            data.reviews.forEach(rev => {
                                let starStr = '';
                                for (let i = 0; i < 5; i++) {
                                    if (i < rev.rating) starStr += '★';
                                    else starStr += '☆';
                                }
                                html += `
                                    <div style="display: flex; gap: 12px; background: var(--bg-base); padding: 12px; border-radius: 12px; border: 1px solid var(--border-glow);">
                                        <span style="font-size: 1.5rem;">👤</span>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 4px;">
                                                <strong style="font-size: 0.82rem; color: var(--text-main); font-weight: 700;">
                                                    ${rev.user_name} <span style="color: #F59E0B; margin-left: 6px;">${starStr}</span>
                                                </strong>
                                                <span style="font-size: 0.68rem; color: var(--text-muted);">${rev.time_formatted}</span>
                                            </div>
                                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 4px 0 0 0; word-break: break-word;">${rev.comment}</p>
                                        </div>
                                    </div>
                                `;
                            });
                            reviewsList.innerHTML = html;
                        }
                    } else {
                        reviewsList.innerHTML = '<div style="text-align: center; color: #ef4444; font-size: 0.8rem; padding: 10px 0;">Không thể tải đánh giá.</div>';
                    }
                })
                .catch(err => {
                    console.error('Error fetching reviews:', err);
                    reviewsList.innerHTML = '<div style="text-align: center; color: #ef4444; font-size: 0.8rem; padding: 10px 0;">Lỗi kết nối máy chủ.</div>';
                });
        }

        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.querySelector('.qr-lightbox-content').style.transform = 'scale(1) translateY(0)';
        }, 50);
    }

    function closeStallDetail() {
        const modal = document.getElementById('stallDetailModal');
        modal.style.opacity = '0';
        modal.querySelector('.qr-lightbox-content').style.transform = 'scale(0.92) translateY(30px)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    function openStallDetailAndScrollToReviews(stallName, sellerName, sellerPhone, bankInfo, category, originText, hasSmartphone, products, lat, lng) {
        openStallDetail(stallName, sellerName, sellerPhone, bankInfo, category, originText, hasSmartphone, products, lat, lng);
        setTimeout(() => {
            const scrollableDiv = document.querySelector('#stallDetailModal .qr-lightbox-content > div[style*="overflow-y: auto"]');
            if (scrollableDiv) {
                const reviewForm = document.getElementById('mdReviewForm');
                if (reviewForm) {
                    scrollableDiv.scrollTo({
                        top: reviewForm.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            }
        }, 400);
    }

    let selectedRating = 0;
    function setReviewRating(rating) {
        selectedRating = rating;
        const stars = document.querySelectorAll('.stars-selector span');
        stars.forEach((s, idx) => {
            if (idx < rating) {
                s.style.color = '#F59E0B';
            } else {
                s.style.color = '#CBD5E1';
            }
        });
    }

    function submitStallReview(e) {
        e.preventDefault();
        
        const textarea = e.target.querySelector('textarea');
        if (!textarea) return;
        
        const comment = textarea.value.trim();
        if (!comment) return;
        
        const rating = selectedRating || 5; 
        const stallName = document.getElementById('mdStallName').textContent;
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang gửi...';
        }

        fetch(`/api/market-stalls/${marketId}/reviews`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                stall_name: stallName,
                comment: comment,
                rating: rating
            })
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Gửi đánh giá';
            }

            if (data.success) {
                const reviewsList = document.getElementById('mdReviewsList');
                if (reviewsList) {
                    if (reviewsList.querySelector('[style*="padding: 15px 0"]') || reviewsList.querySelector('[style*="padding: 10px 0"]')) {
                        reviewsList.innerHTML = '';
                    }

                    let starStr = '';
                    for (let i = 0; i < 5; i++) {
                        if (i < data.review.rating) starStr += '★';
                        else starStr += '☆';
                    }

                    const newItem = document.createElement('div');
                    newItem.style.cssText = "display: flex; gap: 12px; background: var(--bg-base); padding: 12px; border-radius: 12px; border: 1px solid var(--border-glow); animation: fadeIn 0.3s ease-out;";
                    newItem.innerHTML = `
                        <span style="font-size: 1.5rem;">👤</span>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 4px;">
                                <strong style="font-size: 0.82rem; color: var(--text-main); font-weight: 700;">
                                    ${data.review.user_name} <span style="color: #F59E0B; margin-left: 6px;">${starStr}</span>
                                </strong>
                                <span style="font-size: 0.68rem; color: var(--text-muted);">${data.review.time_formatted}</span>
                            </div>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 4px 0 0 0; word-break: break-word;">${data.review.comment}</p>
                        </div>
                    `;
                    reviewsList.insertBefore(newItem, reviewsList.firstChild);
                }

                showCartToast('Đăng đánh giá thành công!');
                textarea.value = '';
                setReviewRating(0);
            } else {
                alert(data.message || 'Lỗi khi lưu đánh giá.');
            }
        })
        .catch(err => {
            console.error('Error posting review:', err);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Gửi đánh giá';
            }
            alert('Lỗi kết nối khi gửi đánh giá.');
        });
    }



    /* Lightbox Gallery Modal Scripts */
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
            document.body.style.overflow = "hidden";
        }
    }

    function closeHeroGalleryModal() {
        const modal = document.getElementById("heroGalleryModal");
        if (modal) {
            modal.style.opacity = "0";
            const contentWrapper = document.getElementById("heroGalleryContent");
            if (contentWrapper) contentWrapper.style.transform = "scale(0.9)";
            
            setTimeout(() => {
                modal.style.display = "none";
                const contentWrapper = document.getElementById("heroGalleryContent");
                if (contentWrapper) contentWrapper.innerHTML = "";
            }, 300);
            document.body.style.overflow = "auto";
        }
    }

    function navigateHeroGallery(direction) {
        currentGalleryIndex += direction;
        if (currentGalleryIndex >= allGalleryMedia.length) {
            currentGalleryIndex = 0;
        } else if (currentGalleryIndex < 0) {
            currentGalleryIndex = allGalleryMedia.length - 1;
        }
        updateHeroGalleryContent();
    }

    function updateHeroGalleryContent() {
        const contentWrapper = document.getElementById("heroGalleryContent");
        const counter = document.getElementById("heroGalleryCounter");
        if (!contentWrapper || !counter) return;

        const media = allGalleryMedia[currentGalleryIndex];
        counter.textContent = currentGalleryIndex + 1;

        if (media.type === 'video') {
            contentWrapper.innerHTML = `
                <video src="${media.url}" controls autoplay class="img-fluid" style="max-width: 100%; max-height: 100%; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);"></video>
            `;
        } else {
            contentWrapper.innerHTML = `
                <img src="${media.url}" class="img-fluid" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            `;
        }
    }

    function animateFlyToCart(button) {
        if (!button) return;

        // Try to find the cart icon in the layout header
        const cartIcon = document.querySelector('.header-action-btn[title="Giỏ hàng"]') || 
                          document.querySelector('.header-cart-btn') || 
                          document.querySelector('[href*="/cart"]') || 
                          document.getElementById('cart-badge') ||
                          document.querySelector('.bi-cart3')?.parentElement ||
                          document.querySelector('.bi-cart3');

        if (!cartIcon) return;

        const btnRect = button.getBoundingClientRect();
        const cartRect = cartIcon.getBoundingClientRect();

        // Create a flying clone element
        const flyingEl = document.createElement('div');
        flyingEl.className = 'flying-cart-item';
        
        // Find product card wrapper to get dynamic emojis or defaults
        let emoji = '🍎';
        const cardWrapper = button.closest('.stall-card-wrapper');
        if (cardWrapper) {
            const cat = cardWrapper.dataset.category;
            if (cat === 'Ăn uống') emoji = '🍲';
            else if (cat === 'Rau củ') emoji = '🥦';
            else if (cat === 'Thực phẩm khô') emoji = '🥜';
            else if (cat === 'Thịt tươi') emoji = '🥩';
        } else {
            // Check modal active category
            const modalBadge = document.getElementById('mdCategoryBadge');
            if (modalBadge) {
                const cat = modalBadge.textContent.toUpperCase();
                if (cat.includes('ĂN UỐNG')) emoji = '🍲';
                else if (cat.includes('RAU CỦ')) emoji = '🥦';
                else if (cat.includes('THỰC PHẨM KHÔ')) emoji = '🥜';
                else if (cat.includes('THỊT TƯƠI')) emoji = '🥩';
            }
        }
        flyingEl.textContent = emoji;

        // Set initial position
        flyingEl.style.left = `${btnRect.left + btnRect.width / 2 - 19}px`;
        flyingEl.style.top = `${btnRect.top + btnRect.height / 2 - 19}px`;
        document.body.appendChild(flyingEl);

        // Force browser layout repaint
        const trigger = flyingEl.offsetWidth;

        // Animate flying
        flyingEl.style.left = `${cartRect.left + cartRect.width / 2 - 19}px`;
        flyingEl.style.top = `${cartRect.top + cartRect.height / 2 - 19}px`;
        flyingEl.style.transform = 'scale(0.3) rotate(360deg)';
        flyingEl.style.opacity = '0.2';

        setTimeout(() => {
            flyingEl.remove();
            
            // Trigger wiggle animation on header cart icon
            cartIcon.classList.add('cart-wiggle');
            setTimeout(() => {
                cartIcon.classList.remove('cart-wiggle');
            }, 700);
        }, 900);
    }

    // =========================================================================
    // MODAL XEM CHI TIẾT BẢNG TIN SỐ BAN QUẢN LÝ CHỢ
    // =========================================================================
    function openAnnouncementDetailModal(tag, time, title, content, color) {
        document.getElementById('modalAnnTag').innerText = tag;
        document.getElementById('modalAnnTag').style.background = color + '1f';
        document.getElementById('modalAnnTag').style.color = color;

        document.getElementById('modalAnnTime').innerText = '⏰ ' + time;
        document.getElementById('modalAnnTitle').innerText = title;
        document.getElementById('modalAnnContent').innerText = content;

        const iconDiv = document.getElementById('modalAnnIcon');
        iconDiv.style.background = color;

        document.getElementById('announcementDetailModal').style.display = 'flex';
    }

    function closeAnnouncementDetailModal() {
        document.getElementById('announcementDetailModal').style.display = 'none';
    }
</script>

<!-- Modal Xem Chi Tiết Bản Tin Số BQL Chợ -->
<div id="announcementDetailModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 580px; border-radius: 20px; padding: 28px; position: relative; box-shadow: 0 20px 50px rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.2);">
        <button type="button" onclick="closeAnnouncementDetailModal()" style="position: absolute; top: 18px; right: 18px; background: #f1f5f9; border: 1px solid #cbd5e1; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #475569; font-size: 1.1rem; cursor: pointer; font-weight: 800; z-index: 10;">✕</button>

        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 18px;">
            <div id="modalAnnIcon" style="width: 50px; height: 50px; border-radius: 14px; background: #10B981; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 6px 16px rgba(0,0,0,0.15);">
                📢
            </div>
            <div>
                <span id="modalAnnTag" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 0.78rem; font-weight: 800; padding: 4px 12px; border-radius: 14px; display: inline-block;">🛡️ KIỂM ĐỊNH ATTP</span>
                <span id="modalAnnTime" style="font-size: 0.78rem; color: #64748b; font-weight: 700; margin-left: 8px;">⏰ Mới cập nhật</span>
            </div>
        </div>

        <h3 id="modalAnnTitle" style="font-size: 1.3rem; font-weight: 900; color: #0f172a; margin-bottom: 14px; line-height: 1.4; font-family: var(--font-heading);">
            Tiêu đề bản tin
        </h3>

        <div style="background: #f8fafc; border-left: 4px solid #0284c7; padding: 18px; border-radius: 12px; margin-bottom: 22px; font-size: 0.94rem; color: #334155; line-height: 1.6; white-space: pre-line;" id="modalAnnContent">
            Nội dung chi tiết bản tin...
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 16px;">
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                📢 Bản tin chính thức từ Ban Quản Lý Chợ
            </span>
            <button type="button" onclick="closeAnnouncementDetailModal()" style="background: #0284c7; color: #fff; border: none; padding: 9px 24px; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.88rem; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
                Đóng
            </button>
        </div>
    </div>
</div>
@endsection
@endsection
