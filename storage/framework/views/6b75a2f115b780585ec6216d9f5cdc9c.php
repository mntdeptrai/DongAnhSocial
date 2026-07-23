<?php $__env->startSection('title', 'Cổng Chợ Số ' . $eatery->name . ' - DongAnh Map Discovery'); ?>

<?php $__env->startSection('meta_description', 'Nền tảng quản lý Chợ số hiện đại tại ' . $eatery->name . ', Đông Anh. Tích hợp thanh toán số VietQR, sơ đồ phân khu tương tác, thông tin nông sản sạch và ATTP.'); ?>

<?php $__env->startSection('og_image', $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80'); ?>

<?php $__env->startSection('seo_schema'); ?>
    <?php echo $jsonLd; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- AOS Library CDN for Scroll Animations -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<?php
    $products = $eatery->ocopProducts;
    $groupedStalls = $products->groupBy('stall_name');
    
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
        $desc = $first->description;
        
        $hasQr = str_contains($desc, 'Hỗ trợ thanh toán VietQR') && !str_contains($desc, 'ngân hàng tiền mặt');
        $hasBank = str_contains($desc, 'ngân hàng') && !str_contains($desc, 'ngân hàng tiền mặt');
        $hasSmartphone = str_contains($desc, 'Có sử dụng smartphone');
        
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
    
    $qrPercentage = $totalStalls > 0 ? round(($stallsWithQr / $totalStalls) * 100) : 88;
    $bankPercentage = $totalStalls > 0 ? round(($stallsWithBank / $totalStalls) * 100) : 88;
    $phonePercentage = $totalStalls > 0 ? round(($stallsWithSmartphone / $totalStalls) * 100) : 88;

    // Build media gallery list
    $allMedia = [];
    if ($eatery->image_path) {
        $allMedia[] = ['type' => 'image', 'url' => $eatery->image_path];
    }
    if (isset($checkinPhotos)) {
        foreach ($checkinPhotos as $photo) {
            $allMedia[] = ['type' => 'image', 'url' => $photo->image_path];
        }
    }
    if ($eatery->reviews) {
        foreach ($eatery->reviews as $rev) {
            if ($rev->media) {
                foreach ($rev->media as $m) {
                    $allMedia[] = ['type' => $m->file_type, 'url' => $m->file_path];
                }
            }
        }
    }
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
?>

<style>
    /* Premium Smart City Style Sheet - Integrated with App Design Language */
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
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: 14px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s;
    }
    .hero-stat-pill:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
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

    /* Custom Responsive Grids */
    .top-db-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
        position: relative;
        z-index: 10;
        padding-bottom: 30px;
    }
    
    .db-card-metric {
        font-family: var(--font-heading);
        font-size: 2.2rem;
        font-weight: 900;
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
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
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
    }
    @media (max-width: 1200px) {
        .stalls-grid-custom {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 768px) {
        .stalls-grid-custom {
            grid-template-columns: 1fr;
        }
    }

    /* Stalls cards custom styles */
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
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-heading);
        font-size: 1.15rem;
        font-weight: 900;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        border: 2px solid rgba(255,255,255,0.4);
    }

    /* Badges pills */
    .gov-badge {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-qr-green { background: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.15) !important; }
    .badge-attp-blue { background: rgba(37, 99, 235, 0.08) !important; color: #2563eb !important; border: 1px solid rgba(37, 99, 235, 0.15) !important; }
    .badge-ocop-orange { background: rgba(245, 158, 11, 0.08) !important; color: #f59e0b !important; border: 1px solid rgba(245, 158, 11, 0.15) !important; }
    .badge-verify-sky { background: rgba(14, 165, 233, 0.08) !important; color: #0ea5e9 !important; border: 1px solid rgba(14, 165, 233, 0.15) !important; }
    .badge-home-purple { background: rgba(139, 92, 246, 0.08) !important; color: #8b5cf6 !important; border: 1px solid rgba(139, 92, 246, 0.15) !important; }

    /* Product items list */
    .product-item-gov {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(14, 165, 233, 0.03);
        border: 1px solid rgba(14, 165, 233, 0.08);
        padding: 10px 14px;
        border-radius: 14px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    .product-item-gov:hover {
        background: rgba(14, 165, 233, 0.06);
        border-color: var(--primary);
        transform: scale(1.02);
    }
    .product-name-txt {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
    }
    .product-price-txt {
        font-family: var(--font-heading);
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--primary);
    }

    /* Actions buttons */
    /* Actions buttons - Floating Modern Capsules */
    .btn-stall-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        padding: 0 20px 20px 20px;
        background: none;
    }
    .btn-stall-action {
        background: rgba(14, 165, 233, 0.04);
        border: 1px solid var(--border-glow);
        color: var(--text-main);
        padding: 10px 4px;
        border-radius: 12px;
        text-align: center;
        font-weight: 700;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.2s var(--ease-premium);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .btn-stall-action:hover {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
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
<div class="market-container" style="padding-top: 40px; margin-bottom: 24px;" data-aos="fade-down">
    <div class="hero-gallery-grid">
        <!-- Image 1: Main Large -->
        <div onclick="openHeroGalleryModal(0)" class="gallery-item" style="grid-column: 1; grid-row: 1 / 3;">
            <?php if($allMedia[0]['type'] === 'video'): ?>
                <video src="<?php echo e($allMedia[0]['url']); ?>" autoplay muted loop playsinline></video>
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.2);">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--primary);">▶</div>
                </div>
            <?php else: ?>
                <img src="<?php echo e($allMedia[0]['url']); ?>" alt="<?php echo e($eatery->name); ?>">
            <?php endif; ?>
            <div class="gallery-overlay"></div>
        </div>
        
        <!-- Image 2 -->
        <div onclick="openHeroGalleryModal(1)" class="gallery-item">
            <img src="<?php echo e($allMedia[1]['url']); ?>">
            <div class="gallery-overlay"></div>
        </div>
        
        <!-- Image 3 -->
        <div onclick="openHeroGalleryModal(2)" class="gallery-item">
            <img src="<?php echo e($allMedia[2]['url']); ?>">
            <div class="gallery-overlay"></div>
        </div>
        
        <!-- Image 4 -->
        <div onclick="openHeroGalleryModal(3)" class="gallery-item">
            <img src="<?php echo e($allMedia[3]['url']); ?>">
            <div class="gallery-overlay"></div>
        </div>
        
        <!-- Image 5: Show all photos overlay -->
        <div onclick="openHeroGalleryModal(4)" class="gallery-item">
            <?php if($allMedia[4]['type'] === 'video'): ?>
                <video src="<?php echo e($allMedia[4]['url']); ?>" muted playsinline></video>
            <?php else: ?>
                <img src="<?php echo e($allMedia[4]['url']); ?>">
            <?php endif; ?>
            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; transition: background 0.3s;" onmouseover="this.style.background='rgba(0,0,0,0.35)'" onmouseout="this.style.background='rgba(0,0,0,0.45)'">
                <div style="text-align: center;">
                    <div style="font-size: 1.8rem; margin-bottom: 4px;">📸</div>
                    <span style="color: white; font-weight: 700; font-size: 1.05rem; display: block;">
                        Xem tất cả <?php echo e(count($allMedia)); ?> Ảnh/Video
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
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                <span class="gov-badge badge-verify-sky" style="font-size: 0.78rem; padding: 6px 14px;">🏪 CHỢ SỐ 4.0 ĐÔNG ANH</span>
                <span class="gov-badge badge-attp-blue" style="font-size: 0.78rem; padding: 6px 14px;">🛡️ Đạt chuẩn ATTP</span>
            </div>
            <h1 style="font-family: var(--font-heading); font-size: 2.6rem; font-weight: 900; color: var(--text-main); margin: 0 0 8px 0;">
                <?php echo e($eatery->name); ?>

            </h1>
            <p style="font-size: 1.05rem; color: var(--text-muted); margin: 0; font-weight: 500;">
                📍 <?php echo e($eatery->address); ?>

            </p>
        </div>
        
        <!-- Info stat widgets row -->
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div class="hero-stat-pill">
                <div class="hero-stat-num"><?php echo e($totalStalls); ?></div>
                <div class="hero-stat-lbl">Hộ kinh doanh</div>
            </div>
            <div class="hero-stat-pill">
                <div class="hero-stat-num"><?php echo e($totalProducts); ?></div>
                <div class="hero-stat-lbl">Mặt hàng</div>
            </div>
            <div class="hero-stat-pill">
                <div class="hero-stat-num">95%</div>
                <div class="hero-stat-lbl">Thanh toán số</div>
            </div>
            <div class="hero-stat-pill">
                <div class="hero-stat-num"><?php echo e($qrPercentage); ?>%</div>
                <div class="hero-stat-lbl">Có mã QR</div>
            </div>
        </div>
    </div>
</div>

<!-- Content Container -->
<div class="market-container">
    
    <!-- II. DASHBOARD THỐNG KÊ CHI TIẾT -->
    <div class="top-db-row">
        <!-- 1. Total Stalls -->
        <div class="premium-panel premium-panel-metric" data-aos="fade-up" data-aos-delay="50" style="margin-bottom:0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span class="db-card-metric"><?php echo e($totalStalls); ?></span>
                <span style="font-size: 1.8rem;">🏪</span>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Tổng số Hộ kinh doanh</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Số lượng hộ đăng ký gian hàng số chính thức.</p>
        </div>

        <!-- 2. Total Products -->
        <div class="premium-panel premium-panel-metric" data-aos="fade-up" data-aos-delay="100" style="margin-bottom:0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span class="db-card-metric"><?php echo e($totalProducts); ?></span>
                <span style="font-size: 1.8rem;">📦</span>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Tổng số Mặt hàng</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Nông sản sạch, ẩm thực đặc sắc và hàng tiêu dùng thiết yếu.</p>
        </div>

        <!-- 3. Stalls with QR -->
        <div class="premium-panel premium-panel-metric premium-panel-success" data-aos="fade-up" data-aos-delay="150" style="margin-bottom:0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span class="db-card-metric"><?php echo e($stallsWithQr); ?></span>
                <span style="font-size: 1.8rem; color: #10B981;">💳</span>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">
                <span class="pulse-indicator-success"></span>Số hộ có mã QR
            </h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Tỷ lệ phủ mã VietQR đạt <strong><?php echo e($qrPercentage); ?>%</strong>.</p>
        </div>

        <!-- 4. Cashless Payment rate -->
        <div class="premium-panel premium-panel-metric premium-panel-success" data-aos="fade-up" data-aos-delay="200" style="margin-bottom:0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span class="db-card-metric">95%</span>
                <span style="font-size: 1.8rem; color: var(--primary);">📈</span>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Tỷ lệ thanh toán số</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Tỷ trọng giao dịch không tiền mặt trên hệ thống.</p>
        </div>

        <!-- 5. Smartphone count -->
        <div class="premium-panel premium-panel-metric" data-aos="fade-up" data-aos-delay="250" style="margin-bottom:0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span class="db-card-metric"><?php echo e($stallsWithSmartphone); ?></span>
                <span style="font-size: 1.8rem;">📱</span>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Hộ dùng Smartphone</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Tỷ lệ sử dụng thiết bị thông minh đạt <strong><?php echo e($phonePercentage); ?>%</strong>.</p>
        </div>

        <!-- 6. Bank account count -->
        <div class="premium-panel premium-panel-metric" data-aos="fade-up" data-aos-delay="300" style="margin-bottom:0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span class="db-card-metric"><?php echo e($stallsWithBank); ?></span>
                <span style="font-size: 1.8rem;">🏛️</span>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Hộ có tài khoản ngân hàng</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Tài khoản ngân hàng số liên kết trực tiếp.</p>
        </div>

        <!-- 7. Wifi Status -->
        <div class="premium-panel premium-panel-metric premium-panel-warning" data-aos="fade-up" data-aos-delay="350" style="margin-bottom:0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 800; color: #F59E0B;">
                    <span class="pulse-indicator-pending"></span>Chưa triển khai
                </span>
                <span style="font-size: 1.8rem;">📶</span>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Wifi công cộng miễn phí</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Đang lập dự án hạ tầng mạng công cộng.</p>
        </div>

        <!-- 8. Camera Status -->
        <div class="premium-panel premium-panel-metric premium-panel-warning" data-aos="fade-up" data-aos-delay="400" style="margin-bottom:0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 800; color: #F59E0B;">
                    <span class="pulse-indicator-pending"></span>Chưa triển khai
                </span>
                <span style="font-size: 1.8rem;">📹</span>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Camera AI giám sát an ninh</h4>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Lắp đặt hệ thống giám sát an ninh trung tâm.</p>
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

    <!-- IV. BẢN ĐỒ SỐ VỊ TRÍ KHÔNG GIAN -->
    <div class="premium-panel" data-aos="fade-up">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <span class="gov-badge badge-verify-sky">📍 ĐỊA ĐIỂM SỐ</span>
                <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; margin-top: 6px; margin-bottom: 0; color: var(--text-main);">Bản đồ Số Vị trí Không gian</h3>
            </div>
            <button onclick="toggleMapFullscreen()" class="btn-stall-action" style="padding: 8px 16px; border-radius: 12px; font-weight: 700; font-size: 0.85rem; width: auto; gap: 8px; border: 1px solid var(--border-glow); background: var(--bg-base);">
                <i class="bi bi-arrows-fullscreen"></i> Xem toàn màn hình
            </button>
        </div>
        
        <div id="miniMap"></div>
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
                <span style="font-size: 0.82rem; font-weight: 700; display: block; margin-top: 4px;">Ẩm thực: <?php echo e($categoriesCount['Ăn uống']); ?> gian</span>
            </div>
            <div class="interactive-block-2d zone-b" id="block-B" onclick="filterByBlock('Rau củ', 'B')">
                <span style="font-size: 1.8rem; display: block; margin-bottom: 6px;">🥦</span>
                <strong style="font-family: var(--font-heading); font-size: 1.1rem; display: block;">Khối B</strong>
                <span style="font-size: 0.82rem; font-weight: 700; display: block; margin-top: 4px;">Rau củ: <?php echo e($categoriesCount['Rau củ']); ?> gian</span>
            </div>
            <div class="interactive-block-2d zone-c" id="block-C" onclick="filterByBlock('Thực phẩm khô', 'C')">
                <span style="font-size: 1.8rem; display: block; margin-bottom: 6px;">🥜</span>
                <strong style="font-family: var(--font-heading); font-size: 1.1rem; display: block;">Khối C</strong>
                <span style="font-size: 0.82rem; font-weight: 700; display: block; margin-top: 4px;">Đồ khô: <?php echo e($categoriesCount['Thực phẩm khô']); ?> gian</span>
            </div>
            <div class="interactive-block-2d zone-d" id="block-D" onclick="filterByBlock('Thịt tươi', 'D')">
                <span style="font-size: 1.8rem; display: block; margin-bottom: 6px;">🥩</span>
                <strong style="font-family: var(--font-heading); font-size: 1.1rem; display: block;">Khối D</strong>
                <span style="font-size: 0.82rem; font-weight: 700; display: block; margin-top: 4px;">Thịt tươi: <?php echo e($categoriesCount['Thịt tươi']); ?> gian</span>
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

    <!-- VI. DANH SÁCH GIAN HÀNG TIỂU THƯƠNG -->
    <div style="margin-bottom: 40px;">
        <h2 style="font-family: var(--font-heading); font-weight: 900; font-size: 1.8rem; margin-bottom: 24px; color: var(--text-main);">
            🏪 Hệ thống Gian Hàng Số Chợ Mạch Tràng
        </h2>
        
        <div class="stalls-grid-custom" id="stallsContainer">
            <?php $__currentLoopData = $groupedStalls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stallName => $stallProducts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $first = $stallProducts->first();
                    $sellerName = $first->seller_name;
                    $sellerPhone = $first->seller_phone;
                    
                    $hasQr = str_contains($first->description, 'Hỗ trợ thanh toán VietQR') && !str_contains($first->description, 'ngân hàng tiền mặt');
                    $hasBank = str_contains($first->description, 'ngân hàng') && !str_contains($first->description, 'ngân hàng tiền mặt');
                    $hasSmartphone = str_contains($first->description, 'Có sử dụng smartphone');
                    
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
                    if (preg_match('/ngân hàng (.*?)\./', $first->description, $matches)) {
                        $bankInfo = $matches[1];
                    }
                    
                    $originText = 'Tự sản xuất';
                    if (preg_match('/Nguồn gốc: (.*?)\./', $first->description, $match)) {
                        $originText = trim($match[1]);
                    }
                ?>
                
                <div class="stall-card-wrapper" 
                     data-name="<?php echo e(strtolower($stallName)); ?>" 
                     data-seller="<?php echo e(strtolower($sellerName)); ?>"
                     data-category="<?php echo e($category); ?>"
                     data-qr="<?php echo e($hasQr ? 'yes' : 'no'); ?>"
                     data-phone="<?php echo e($hasSmartphone ? 'yes' : 'no'); ?>"
                     data-origin="<?php echo e(strtolower($first->description)); ?>"
                     data-products="<?php echo e(strtolower($stallProducts->pluck('name')->implode(' '))); ?>"
                     data-lat="<?php echo e($first->latitude ?? ''); ?>"
                     data-lng="<?php echo e($first->longitude ?? ''); ?>">
                     
                    <div class="stall-card-gov">
                        <!-- Card Header -->
                        <div style="padding: 20px; border-bottom: 1px solid var(--border-glow); display: flex; align-items: center; gap: 16px; background: rgba(14, 165, 233, 0.015);">
                            <?php
                                $gradients = [
                                    'linear-gradient(135deg, #0EA5E9 0%, #2563EB 100%)',
                                    'linear-gradient(135deg, #10B981 0%, #059669 100%)',
                                    'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)',
                                    'linear-gradient(135deg, #EC4899 0%, #DB2777 100%)'
                                ];
                                $grad = $gradients[abs(crc32($sellerName)) % count($gradients)];
                            ?>
                            <div class="stall-avatar" style="background: <?php echo $grad; ?>">
                                <?php echo e(mb_substr($sellerName, 0, 1)); ?>

                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <h4 style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 800; margin: 0; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">
                                        <?php echo e($stallName); ?>

                                    </h4>
                                    <button onclick="showStallOnMap('<?php echo e($stallName); ?>', '<?php echo e($sellerName); ?>', '<?php echo e($category); ?>', '<?php echo e($first->latitude ?? ''); ?>', '<?php echo e($first->longitude ?? ''); ?>')" class="btn-map-pin" title="Định vị trên bản đồ số" style="background: rgba(14, 165, 233, 0.06); border: 1px solid var(--border-glow); color: #E11D48; border-radius: 8px; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; padding: 4px 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" onmouseover="this.style.background='#E11D48'; this.style.color='#ffffff';" onmouseout="this.style.background='rgba(14, 165, 233, 0.06)'; this.style.color='#E11D48';">
                                        📍
                                    </button>
                                </div>
                                <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">👤 Chủ hộ: <?php echo e($sellerName); ?></span>
                            </div>
                        </div>
                        
                        <!-- Badges area -->
                        <div style="padding: 12px 20px 0 20px; display: flex; flex-wrap: wrap; gap: 6px;">
                            <span class="gov-badge badge-verify-sky" 
                                  style="cursor: pointer; transition: all 0.2s;" 
                                  onmouseover="this.style.background='rgba(14, 165, 233, 0.15)'" 
                                  onmouseout="this.style.background='rgba(14, 165, 233, 0.08)'"
                                  onclick="openStallDetailAndScrollToReviews('<?php echo e($stallName); ?>', '<?php echo e($sellerName); ?>', '<?php echo e($sellerPhone); ?>', '<?php echo e($bankInfo); ?>', '<?php echo e($category); ?>', '<?php echo e($originText); ?>', '<?php echo e($hasSmartphone ? 'yes' : 'no'); ?>', <?php echo e(json_encode($stallProducts)); ?>, '<?php echo e($first->latitude ?? ''); ?>', '<?php echo e($first->longitude ?? ''); ?>')">
                                ⭐ 5.0 (1 Đánh giá)
                            </span>
                            <span class="gov-badge badge-attp-blue">✓ ATTP</span>
                            <?php if($hasQr): ?>
                                <span class="gov-badge badge-qr-green">✓ Có QR</span>
                            <?php endif; ?>
                            <?php if(str_contains(strtolower($originText), 'tự sản xuất')): ?>
                                <span class="gov-badge badge-home-purple">✓ Tự sản xuất</span>
                            <?php else: ?>
                                <span class="gov-badge badge-verify-sky">✓ Đã xác minh</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Products listed & latest review -->
                        <div style="padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h5 style="font-size: 0.78rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Sản phẩm nổi bật</h5>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <?php $__currentLoopData = $stallProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="product-item-gov" style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                                <span style="font-size: 1rem;">🍎</span>
                                                <span class="product-name-txt" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e($prod->name); ?></span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                <span class="product-price-txt">
                                                    <?php echo e(number_format($prod->price, 0, ',', '.')); ?>đ
                                                </span>
                                                <button class="add-to-cart-btn" data-id="<?php echo e($prod->id); ?>" data-type="ocop_product" onclick="addToCart(event, this); animateFlyToCart(this);" style="background: rgba(14, 165, 233, 0.08); border: 1.5px solid rgba(14, 165, 233, 0.25); color: var(--primary); border-radius: 20px; padding: 4px 10px; font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all 0.2s; white-space: nowrap; display: flex; align-items: center; gap: 3px;" onmouseover="this.style.background='var(--primary)'; this.style.color='#ffffff'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='rgba(14, 165, 233, 0.08)'; this.style.color='var(--primary)'; this.style.borderColor='rgba(14, 165, 233, 0.25)';">
                                                    🛒 Đặt trước
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>

                            <!-- Comment snippet preview -->
                            <div style="margin-top: 16px; border-top: 1px dashed var(--border-glow); padding-top: 14px; cursor: pointer;" 
                                 onclick="openStallDetailAndScrollToReviews('<?php echo e($stallName); ?>', '<?php echo e($sellerName); ?>', '<?php echo e($sellerPhone); ?>', '<?php echo e($bankInfo); ?>', '<?php echo e($category); ?>', '<?php echo e($originText); ?>', '<?php echo e($hasSmartphone ? 'yes' : 'no'); ?>', <?php echo e(json_encode($stallProducts)); ?>, '<?php echo e($first->latitude ?? ''); ?>', '<?php echo e($first->longitude ?? ''); ?>')">
                                <span style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Đánh giá mới nhất</span>
                                <div style="font-size: 0.8rem; color: var(--text-main); font-style: italic; display: flex; gap: 8px; align-items: flex-start; font-weight: 500;">
                                    <span style="color: #F59E0B; font-size: 0.95rem;">💬</span>
                                    <span style="line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        "Sản phẩm rất tươi ngon, chủ quán thân thiện, thanh toán QR siêu nhanh chóng!"
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions footer -->
                        <div class="btn-stall-grid">
                            <a href="tel:<?php echo e($sellerPhone); ?>" class="btn-stall-action">
                                <i class="bi bi-telephone-fill"></i> Gọi điện
                            </a>
                            <a href="https://zalo.me/<?php echo e($sellerPhone); ?>" target="_blank" class="btn-stall-action">
                                <i class="bi bi-chat-text-fill"></i> Zalo
                            </a>
                            <button onclick="openStallDetail('<?php echo e($stallName); ?>', '<?php echo e($sellerName); ?>', '<?php echo e($sellerPhone); ?>', '<?php echo e($bankInfo); ?>', '<?php echo e($category); ?>', '<?php echo e($originText); ?>', '<?php echo e($hasSmartphone ? 'yes' : 'no'); ?>', <?php echo e(json_encode($stallProducts)); ?>, '<?php echo e($first->latitude ?? ''); ?>', '<?php echo e($first->longitude ?? ''); ?>')" class="btn-stall-action">
                                <i class="bi bi-info-circle-fill"></i> Chi tiết
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    <!-- XIII. CARD CHUYỂN ĐỔI SỐ & XIV. CARD HẠ TẦNG -->
    <div class="bottom-double-grid">
        <!-- Chuyển đổi số checklist -->
        <div class="premium-panel blueprint-grid" data-aos="fade-right" style="margin-bottom:0; background: var(--bg-card);">
            <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.25rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
                <i class="bi bi-cpu" style="color: var(--primary);"></i> Bản đồ Chuyển đổi số Chợ 4.0
            </h3>
            <div class="bottom-info-list">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #10B981; font-size: 1.1rem;"><i class="bi bi-check-circle-fill"></i></span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Thanh toán số (95%)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #10B981; font-size: 1.1rem;"><i class="bi bi-check-circle-fill"></i></span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Liên kết mã QR (88%)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #10B981; font-size: 1.1rem;"><i class="bi bi-check-circle-fill"></i></span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Sử dụng Smartphone</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #10B981; font-size: 1.1rem;"><i class="bi bi-check-circle-fill"></i></span>
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">Liên kết Ngân hàng số</span>
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

    <!-- Bún Mạch Tràng Cổ Loa Heritage (Special for ID 16) -->
    <?php if($eatery->id === 16): ?>
        <div class="premium-panel" style="border-color: rgba(212,175,55,0.4) !important; background: linear-gradient(135deg, var(--bg-card) 0%, rgba(212,175,55,0.03) 100%) !important;" data-aos="fade-up">
            <span class="premium-badge" style="background: rgba(212,175,55,0.1); border-color: rgba(212,175,55,0.4); color: #ffb300; font-weight: 800; padding: 6px 14px; border-radius: 20px; display: inline-block;">🏛️ DI SẢN ẨM THỰC TRUYỀN THUYẾT CỔ LOA</span>
            
            <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 16px;">
                <div style="flex: 2; min-width: 300px; display: flex; flex-direction: column; gap: 14px;">
                    <h3 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 900; color: var(--text-main); margin: 0;">
                        Bún Mạch Tràng Cổ Loa - Hương Vị Ngàn Năm
                    </h3>
                    <p style="font-size: 0.9rem; line-height: 1.6; color: var(--text-muted); margin: 0;">
                        Tương truyền, trong quá trình xây dựng thành Cổ Loa, người dân làng Mạch Tràng đã làm ra những sợi bún có màu ngà tự nhiên làm lương thực dâng lên vua An Dương Vương và các tướng sĩ. Trải qua hàng ngàn năm, sợi bún đặc trưng không dùng chất tẩy trắng này vẫn gìn giữ nguyên vẹn vị ngọt thanh mát, dẻo dai từ gạo nguyên bản, trở thành một di sản sống của vùng đất kinh đô cổ.
                    </p>
                    
                    <div style="background: var(--bg-base); border: 1px solid var(--border-glow); border-radius: 16px; padding: 14px 18px; display: flex; align-items: center; gap: 16px;">
                        <button onclick="toggleAudio()" id="audioBtn" style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary); border: none; color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;">
                            ▶️
                        </button>
                        <div>
                            <strong style="display: block; font-size: 0.85rem; color: var(--text-main);">Nghe Thuyết Minh Di Sản Bún Mạch Tràng</strong>
                            <span style="font-size: 0.75rem; color: var(--text-muted);" id="audioStatus">Bấm để bắt đầu nghe thuyết minh tự động</span>
                        </div>
                        <audio id="heritageAudio" src="https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&q=Bún%20Mạch%20Tràng%20có%20lịch%20sử%20hào%20hùng%20gắn%20liền%20với%20truyền%20thuyết%20Cổ%20Loa%20thành.%20Tương%20truyền,%20trong%20quá%20trình%20xây%20dựng%20thành%20Cổ%20Loa,%20người%20dân%20làng%20Mạch%20Tràng%20đã%20làm%20ra%20những%20sợi%20bún%20có%20màu%20ngà%20tự%20nhiên%20làm%20lương%20thực%20dâng%20lên%20vua%20An%20Dương%20Vương%20và%20các%20tướng%20sĩ.&tl=vi"></audio>
                    </div>
                </div>
                
                <div style="flex: 1; min-width: 250px; background: rgba(0,0,0,0.015); border: 1px solid var(--border-glow); border-radius: 20px; padding: 20px;">
                    <strong style="display: block; color: var(--text-main); font-size: 0.95rem; margin-bottom: 12px;">Đạt Chuẩn OCOP 4 Sao</strong>
                    <div style="font-size: 0.82rem; display: flex; flex-direction: column; gap: 10px; color: var(--text-muted);">
                        <div>🏆 <strong>Sản phẩm tiêu biểu:</strong> Đạt chứng nhận sản phẩm nông nghiệp tiêu biểu Hà Nội.</div>
                        <div>🌱 <strong>Cam kết:</strong> Ngâm gạo, lên men tự nhiên 3 ngày đêm, hoàn toàn không chất tẩy trắng.</div>
                        <div>👵 <strong>Nghệ nhân tiêu biểu:</strong> Nguyễn Văn Cường (Đời thứ 4 làng Mạch Tràng).</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
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
                    <div id="mdProductsList" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px;">
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
                <div style="display: flex; flex-direction: column; gap: 14px;">
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
        <span id="heroGalleryCounter">1</span> / <?php echo e(count($allMedia)); ?>

    </div>
</div>

<?php $__env->startSection('scripts'); ?>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-quad',
        once: true
    });

    const categoriesData = <?php echo json_encode($categoriesCount, 15, 512) ?>;
    const originsData = <?php echo json_encode($originsCount, 15, 512) ?>;
    const totalStallsCount = <?php echo e($totalStalls); ?>;
    const stallsWithQrCount = <?php echo e($stallsWithQr); ?>;
    const stallsWithSmartphoneCount = <?php echo e($stallsWithSmartphone); ?>;
    const stallsWithBankCount = <?php echo e($stallsWithBank); ?>;

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
    });

    let mainMap;
    let blockPolygons = {};
    let tempStallMarker = null;
    const eateryLat = <?php echo e($eatery->latitude); ?>;
    const eateryLng = <?php echo e($eatery->longitude); ?>;

    const allStallsData = [
        <?php $__currentLoopData = $groupedStalls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stallName => $stallProducts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
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
            ?>
            {
                name: <?php echo json_encode($stallName); ?>,
                seller: <?php echo json_encode($sellerName); ?>,
                phone: <?php echo json_encode($sellerPhone); ?>,
                bank: <?php echo json_encode($bankInfo); ?>,
                category: "<?php echo e($category); ?>",
                origin: <?php echo json_encode($originText); ?>,
                smartphone: "<?php echo e($hasSmartphone ? 'yes' : 'no'); ?>",
                products: <?php echo json_encode($stallProducts); ?>,
                lat: "<?php echo e($first->latitude ?? ''); ?>",
                lng: "<?php echo e($first->longitude ?? ''); ?>"
            },
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    ];

    function openStallFromMap(idx) {
        const s = allStallsData[idx];
        openStallDetail(s.name, s.seller, s.phone, s.bank, s.category, s.origin, s.smartphone, s.products, s.lat, s.lng);
    }

    function getStallCoords(category, sellerName) {
        // Generate deterministic offset based on category and hash of name
        let baseLat = eateryLat;
        let baseLng = eateryLng;
        if (category === 'Ăn uống') {
            baseLat += 0.000175; baseLng -= 0.000175;
        } else if (category === 'Rau củ') {
            baseLat += 0.000175; baseLng += 0.000175;
        } else if (category === 'Thực phẩm khô') {
            baseLat -= 0.000175; baseLng -= 0.000175;
        } else if (category === 'Thịt tươi') {
            baseLat -= 0.000175; baseLng += 0.000175;
        }
        
        // Small jitter based on name string sum
        let sum = 0;
        for (let i = 0; i < sellerName.length; i++) {
            sum += sellerName.charCodeAt(i);
        }
        let latJitter = ((sum % 17) - 8) * 0.000015;
        let lngJitter = ((sum % 13) - 6) * 0.000015;
        return [baseLat + latJitter, baseLng + lngJitter];
    }

    function initSatelliteMap() {
        mainMap = L.map('miniMap').setView([eateryLat, eateryLng], 17);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mainMap);
        
        const customIcon = L.divIcon({
            html: `<div style="background-color: var(--primary); width: 36px; height: 36px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">🏪</div>`,
            className: 'custom-leaflet-marker',
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });
        
        L.marker([eateryLat, eateryLng], { icon: customIcon })
            .addTo(mainMap)
            .bindPopup(`<strong style="font-family: var(--font-heading); font-size:1rem;"><?php echo e($eatery->name); ?></strong><br>📍 ${eateryLat}, ${eateryLng}<br>🏪 Quy mô: <?php echo e($totalStalls); ?> hộ kinh doanh`)
            .openPopup();

        // Draw Block Polygons
        // Block A (Blue)
        const blockACoords = [
            [eateryLat + 0.00028, eateryLng - 0.00028],
            [eateryLat + 0.00028, eateryLng - 0.00005],
            [eateryLat + 0.00005, eateryLng - 0.00005],
            [eateryLat + 0.00005, eateryLng - 0.00028]
        ];
        blockPolygons['A'] = L.polygon(blockACoords, {
            color: '#0EA5E9',
            fillColor: '#0EA5E9',
            fillOpacity: 0.12,
            weight: 2,
            dashArray: '4'
        }).addTo(mainMap).bindPopup("🍲 <strong>Khối A - Khu Ẩm thực</strong>");

        // Block B (Green)
        const blockBCoords = [
            [eateryLat + 0.00028, eateryLng + 0.00005],
            [eateryLat + 0.00028, eateryLng + 0.00028],
            [eateryLat + 0.00005, eateryLng + 0.00028],
            [eateryLat + 0.00005, eateryLng + 0.00005]
        ];
        blockPolygons['B'] = L.polygon(blockBCoords, {
            color: '#10B981',
            fillColor: '#10B981',
            fillOpacity: 0.12,
            weight: 2,
            dashArray: '4'
        }).addTo(mainMap).bindPopup("🥦 <strong>Khối B - Khu Rau củ sạch</strong>");

        // Block C (Orange)
        const blockCCoords = [
            [eateryLat - 0.00005, eateryLng - 0.00028],
            [eateryLat - 0.00005, eateryLng - 0.00005],
            [eateryLat - 0.00028, eateryLng - 0.00005],
            [eateryLat - 0.00028, eateryLng - 0.00028]
        ];
        blockPolygons['C'] = L.polygon(blockCCoords, {
            color: '#F59E0B',
            fillColor: '#F59E0B',
            fillOpacity: 0.12,
            weight: 2,
            dashArray: '4'
        }).addTo(mainMap).bindPopup("🥜 <strong>Khối C - Đồ khô & Gia vị</strong>");

        // Block D (Pink)
        const blockDCoords = [
            [eateryLat - 0.00005, eateryLng + 0.00005],
            [eateryLat - 0.00005, eateryLng + 0.00028],
            [eateryLat - 0.00028, eateryLng + 0.00028],
            [eateryLat - 0.00028, eateryLng + 0.00005]
        ];
        blockPolygons['D'] = L.polygon(blockDCoords, {
            color: '#EC4899',
            fillColor: '#EC4899',
            fillOpacity: 0.12,
            weight: 2,
            dashArray: '4'
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
            let bgColor = '#64748B';
            if (s.category === 'Ăn uống') { emoji = '🍲'; bgColor = '#0EA5E9'; }
            else if (s.category === 'Rau củ') { emoji = '🥦'; bgColor = '#10B981'; }
            else if (s.category === 'Thực phẩm khô') { emoji = '🥜'; bgColor = '#F59E0B'; }
            else if (s.category === 'Thịt tươi') { emoji = '🥩'; bgColor = '#EC4899'; }

            const stallIcon = L.divIcon({
                html: `<div class="stall-map-marker" style="background: ${bgColor};" title="${s.name}">${emoji}</div>`,
                className: 'stall-leaflet-marker',
                iconSize: [28, 28],
                iconAnchor: [14, 28]
            });

            const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${sLat},${sLng}`;

            L.marker([sLat, sLng], { icon: stallIcon })
                .addTo(mainMap)
                .bindPopup(`
                    <div style="font-family: var(--font-heading); padding: 5px; min-width: 170px;">
                        <strong style="font-size: 0.95rem; color: var(--primary); display:block; margin-bottom: 4px;">${s.name}</strong>
                        <span style="font-size: 0.8rem; color:#64748B; display:block; margin-bottom: 10px;">👤 Chủ hộ: ${s.seller}<br>📍 Ngành: ${s.category}</span>
                        <div style="display:flex; flex-direction:column; gap:6px;">
                            <button onclick="openStallFromMap(${idx})" style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.2); color: var(--primary); border-radius: 8px; font-weight:700; font-size: 0.72rem; padding: 6px; cursor:pointer; width:100%; display:flex; align-items:center; justify-content:center; gap:4px;" onmouseover="this.style.background='var(--primary)'; this.style.color='#ffffff';" onmouseout="this.style.background='rgba(14, 165, 233, 0.08)'; this.style.color='var(--primary)';">
                                🔍 Xem Chi Tiết
                            </button>
                            <a href="${googleMapsUrl}" target="_blank" style="display:inline-flex; align-items:center; gap:4px; background: #E11D48; color: white; padding: 6px; border-radius: 8px; font-weight:700; font-size: 0.72rem; text-decoration:none; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.15); width: 100%; justify-content: center;">
                                🗺️ Google Maps chỉ đường
                            </a>
                        </div>
                    </div>
                `);
        });
    }

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

        const prodList = document.getElementById('mdProductsList');
        prodList.innerHTML = '';
        products.forEach(p => {
            prodList.innerHTML += `
                <div class="modal-product-item" style="border-left: 4px solid var(--primary); display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                    <div style="flex: 1;">
                        <span style="font-weight: 700; color: var(--text-main); display: block; font-size: 0.85rem;">${p.name}</span>
                        <span style="font-size: 0.72rem; color: var(--text-muted);">🌾 Nguồn gốc: ${originText} | Cam kết tươi sạch</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
                        <span style="font-family: var(--font-heading); font-size: 0.9rem; font-weight: 900; color: var(--primary);">
                            ${new Intl.NumberFormat('vi-VN').format(p.price)}đ
                        </span>
                        <button class="add-to-cart-btn" data-id="${p.id}" data-type="ocop_product" onclick="addToCart(event, this); animateFlyToCart(this);" style="background: var(--primary); border: none; color: white; border-radius: 8px; padding: 6px 12px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='none';">
                            + Chọn mua
                        </button>
                    </div>
                </div>
            `;
        });

        const qrImage = document.getElementById('mdQrImage');
        const qrLoader = document.getElementById('mdQrLoader');
        const qrBankText = document.getElementById('mdBankInfo');

        if (bankInfo) {
            qrBankText.textContent = `Ngân hàng: ${bankInfo}`;
            const parts = bankInfo.split(':');
            const bank = parts[0].trim().toLowerCase();
            const account = parts[1].trim();
            
            const bankMap = {
                'mb': 'MB',
                'techcombank': 'TCB',
                'tpbank': 'TPB',
                'vietinbank': 'ICB'
            };
            const bankId = bankMap[bank] || 'MB';
            
            const memo = encodeURIComponent(`Thanh toan quay ${stallName}`);
            const qrUrl = `https://img.vietqr.io/image/${bankId}-${account}-compact2.jpg?amount=${products[0].price}&addInfo=${memo}`;
            
            qrImage.src = qrUrl;
            qrLoader.style.display = 'flex';
            qrImage.style.display = 'none';
            
            qrImage.onload = function() {
                qrLoader.style.display = 'none';
                qrImage.style.display = 'block';
            };
        } else {
            qrBankText.textContent = 'Thanh toán tiền mặt';
            qrImage.src = '';
            qrLoader.style.display = 'flex';
            qrLoader.textContent = '✕ Tiền mặt';
            qrImage.style.display = 'none';
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
        alert('Cảm ơn bạn đã gửi đánh giá! Bình luận sẽ được kiểm duyệt trước khi hiển thị công khai.');
        e.target.reset();
        setReviewRating(0);
    }

    let isPlayingAudio = false;
    function toggleAudio() {
        const audio = document.getElementById('heritageAudio');
        const btn = document.getElementById('audioBtn');
        const status = document.getElementById('audioStatus');
        
        if (isPlayingAudio) {
            audio.pause();
            btn.textContent = '▶️';
            status.textContent = 'Tạm dừng thuyết minh di sản';
            isPlayingAudio = false;
        } else {
            audio.play();
            btn.textContent = '⏸️';
            status.textContent = 'Đang phát thuyết minh di sản...';
            isPlayingAudio = true;
        }
    }

    /* Lightbox Gallery Modal Scripts */
    const allGalleryMedia = <?php echo json_encode($allMedia); ?>;
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
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DA_DISCOVERY\resources\views/detail-market.blade.php ENDPATH**/ ?>