@extends('layouts.app')

@section('title', 'Tuyến đường 4.0 - Bản đồ Số Tuyến đường Xã Đông Anh')
@section('meta_description', 'Bản đồ tuyến đường số 4.0 Xã Đông Anh - Tra cứu 57 hộ kinh doanh thực tế, tuyến đường số hóa và điểm thanh toán VietQR thông minh.')

@push('head')
<!-- Leaflet JS & CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- Leaflet MarkerCluster Plugin -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<style>
    /* Reset window scrollbar & main padding from layout */
    html, body {
        height: 100vh !important;
        overflow: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    main {
        padding-bottom: 0 !important;
        min-height: auto !important;
    }
    footer {
        display: none !important;
    }

    /* Full height app container */
    .tuyen-duong-app-root {
        position: relative;
        width: 100%;
        height: calc(100vh - 64px);
        min-height: 600px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Keyframe Animations */
    @keyframes leafletDashFlow {
        from { stroke-dashoffset: 0; }
        to { stroke-dashoffset: -44px; }
    }
    @keyframes pulsePing {
        0% { transform: scale(0.95); opacity: 0.85; }
        50% { transform: scale(1.4); opacity: 0.15; }
        100% { transform: scale(0.95); opacity: 0.85; }
    }
    @keyframes modalSlideUp {
        from { opacity: 0; transform: translateY(24px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes overlayFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Route Polyline CSS Animations */
    .route-path-animated-1 path {
        stroke-dasharray: 12, 10;
        animation: leafletDashFlow 1.2s linear infinite;
    }
    .route-path-animated-2 path {
        stroke-dasharray: 12, 10;
        animation: leafletDashFlow 1.8s linear infinite;
    }
    .route-path-animated-3 path {
        stroke-dasharray: 12, 10;
        animation: leafletDashFlow 0.9s linear infinite;
    }

    .modal-animate-enter {
        animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .overlay-animate-enter {
        animation: overlayFadeIn 0.2s ease forwards;
    }

    /* Scrollbar Utility */
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Category Filter Chip Buttons */
    .filter-chip-btn {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.76rem;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    .filter-chip-btn:hover {
        border-color: #10b981;
        color: #059669;
        background: #ecfdf5;
    }
    .filter-chip-btn.active {
        background: #059669 !important;
        color: #ffffff !important;
        border-color: #059669 !important;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    /* Modern Village Filter Pill Buttons */
    .village-pill-btn {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.76rem;
        font-weight: 700;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .village-pill-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .village-pill-btn.v-all.active {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #0f172a !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);
    }
    .village-pill-btn.v-phu-loc { color: #ea580c; border-color: #ffedd5; background: #fff7ed; }
    .village-pill-btn.v-phu-loc.active { color: #ffffff; background: #f97316; border-color: #f97316; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }

    .village-pill-btn.v-dong-anh-cum-3 { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
    .village-pill-btn.v-dong-anh-cum-3.active { color: #ffffff; background: #10b981; border-color: #10b981; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }

    .village-pill-btn.v-duc-noi { color: #a16207; border-color: #fef08a; background: #fefce8; }
    .village-pill-btn.v-duc-noi.active { color: #ffffff; background: #eab308; border-color: #eab308; box-shadow: 0 4px 12px rgba(234,179,8,0.3); }

    .village-pill-btn.v-viet-hung { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; }
    .village-pill-btn.v-viet-hung.active { color: #ffffff; background: #3b82f6; border-color: #3b82f6; box-shadow: 0 4px 12px rgba(59,130,246,0.3); }

    /* Modern Card Tag Badges */
    .card-tag-badge {
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        display: inline-flex;
        align-items: center;
    }
    .card-tag-quan-an { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .card-tag-nha-hang { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .card-tag-tap-hoa { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
    .card-tag-thoi-trang { background: #faf5ff; color: #7e22ce; border: 1px solid #e9d5ff; }
    .card-tag-y-te { background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; }
    .card-tag-dich-vu { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

    /* Card Village Badge */
    .card-village-badge {
        padding: 3px 8px;
        border-radius: 8px;
        font-size: 0.68rem;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        display: inline-flex;
        align-items: center;
    }
    .card-village-badge.v-phu-loc { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .card-village-badge.v-dong-anh-cum-3 { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .card-village-badge.v-duc-noi { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
    .card-village-badge.v-viet-hung { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

    /* Location Card Item */
    .location-list-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 10px;
        display: flex;
        gap: 12px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-align: left;
        position: relative;
    }
    .location-list-card:hover {
        border-color: #10b981;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.12);
        transform: translateY(-2px);
    }
    .location-list-card.selected {
        border-color: #059669;
        background: #f0fdf4;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
    }

    /* Scalable Compact Circular Leaflet Markers */
    .compact-leaflet-pin {
        position: relative;
        width: 40px;
        height: 40px;
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .compact-leaflet-pin:hover {
        transform: scale(1.22) translateY(-4px);
        z-index: 9999 !important;
    }
    .pin-circle-body {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff;
        border: 2.5px solid;
        box-shadow: 0 6px 18px rgba(0,0,0,0.22);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        position: relative;
        z-index: 2;
    }
    .pin-rating-tag {
        position: absolute;
        top: -6px;
        right: -10px;
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fef3c7;
        padding: 0 5px;
        border-radius: 8px;
        font-size: 0.64rem;
        font-weight: 900;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        z-index: 3;
        font-family: 'Be Vietnam Pro', sans-serif;
    }
    .pin-pulse-ring {
        position: absolute;
        width: 52px;
        height: 52px;
        top: -6px;
        left: -6px;
        border-radius: 50%;
        animation: pulsePing 2s ease-in-out infinite;
        pointer-events: none;
        z-index: 1;
    }

    /* MarkerCluster Custom Theme */
    .marker-cluster-small, .marker-cluster-medium, .marker-cluster-large {
        background-color: rgba(5, 150, 105, 0.25) !important;
        border-radius: 50%;
    }
    .marker-cluster-small div, .marker-cluster-medium div, .marker-cluster-large div {
        background-color: #059669 !important;
        color: #ffffff !important;
        font-family: 'Be Vietnam Pro', sans-serif !important;
        font-weight: 900 !important;
        font-size: 0.85rem !important;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.4) !important;
    }

    /* Custom Leaflet Popup Card */
    .leaflet-popup-content-wrapper {
        border-radius: 18px !important;
        padding: 4px !important;
        box-shadow: 0 15px 35px rgba(0,0,0,0.18) !important;
        border: 1px solid #e2e8f0 !important;
    }
    .leaflet-popup-tip {
        background: #ffffff !important;
    }

    /* ==========================================================================
       RESPONSIVE DESIGN SYSTEM FOR 4 SCREEN BREAKPOINTS
       ========================================================================== */

    /* 1. Ultra & Large Desktop Screens (>= 1400px) */
    @media (min-width: 1400px) {
        .tuyen-duong-sidebar {
            width: 410px !important;
        }
        .card-img-container {
            width: 115px !important;
            height: 98px !important;
        }
    }

    /* 2. Standard Desktop & Laptop Screens (1024px - 1399px) */
    @media (min-width: 1024px) and (max-width: 1399px) {
        .tuyen-duong-sidebar {
            width: 350px !important;
        }
        .card-img-container {
            width: 96px !important;
            height: 88px !important;
        }
    }

    /* 3. Tablet / iPad Screens (768px - 1023px) */
    @media (min-width: 768px) and (max-width: 1023px) {
        .tuyen-duong-app-root {
            height: calc(100vh - 64px) !important;
            overflow: hidden !important;
        }
        .tuyen-duong-layout {
            position: relative !important;
            flex-direction: row !important;
        }
        .tuyen-duong-sidebar {
            position: absolute !important;
            top: 0; left: 0;
            width: 340px !important;
            height: 100% !important;
            z-index: 1000 !important;
            box-shadow: 10px 0 30px rgba(0,0,0,0.18) !important;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .tuyen-duong-sidebar.tablet-collapsed {
            transform: translateX(-100%) !important;
        }
        .tuyen-duong-map {
            width: 100% !important;
            height: 100% !important;
        }
        .tablet-toggle-sidebar-btn {
            display: flex !important;
        }
    }

    /* 4. Mobile Smartphone Screens (< 768px) */
    @media (max-width: 767px) {
        html, body {
            height: auto !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }
        .tuyen-duong-app-root {
            height: auto !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: visible !important;
        }
        .mobile-switcher-bar {
            display: flex !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
        }
        .tuyen-duong-layout {
            flex-direction: column !important;
            flex: none !important;
            height: auto !important;
            overflow: visible !important;
            display: block !important;
        }
        .tuyen-duong-sidebar {
            width: 100% !important;
            height: auto !important;
            border-right: none !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: visible !important;
        }
        .custom-scrollbar {
            overflow: visible !important;
            max-height: none !important;
            height: auto !important;
        }
        .tuyen-duong-map {
            width: 100% !important;
            height: calc(100vh - 120px) !important;
            min-height: 480px !important;
        }
        .mobile-view-map .tuyen-duong-sidebar {
            display: none !important;
        }
        .mobile-view-map .tuyen-duong-map {
            display: block !important;
        }
        .mobile-view-list .tuyen-duong-sidebar {
            display: flex !important;
        }
        .mobile-view-list .tuyen-duong-map {
            display: none !important;
        }
        .filter-scroll-container {
            overflow-x: auto !important;
            white-space: nowrap !important;
            -webkit-overflow-scrolling: touch !important;
            padding-bottom: 2px !important;
        }
        .filter-scroll-container::-webkit-scrollbar {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="tuyen-duong-app-root" x-data="tuyenDuongApp()" x-init="initMap()" :class="`mobile-view-${mobileView}`">

    <!-- Mobile Screen View Switcher Bar (Visible on Smartphone screens < 768px) -->
    <div class="mobile-switcher-bar" style="display: none; background: #0f172a; padding: 6px; border-bottom: 1px solid #1e293b; gap: 6px; z-index: 99;">
        <button type="button" @click="toggleMobileView('map')" 
                :style="mobileView === 'map' ? 'background: #059669; color: #ffffff;' : 'background: rgba(255,255,255,0.08); color: #94a3b8;'"
                style="flex: 1; padding: 8px 12px; border: none; border-radius: 10px; font-size: 0.8rem; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; transition: all 0.2s;">
            <span>🗺️ Bản đồ số 4.0</span>
        </button>
        <button type="button" @click="toggleMobileView('list')" 
                :style="mobileView === 'list' ? 'background: #059669; color: #ffffff;' : 'background: rgba(255,255,255,0.08); color: #94a3b8;'"
                style="flex: 1; padding: 8px 12px; border: none; border-radius: 10px; font-size: 0.8rem; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; transition: all 0.2s;">
            <span>📋 Danh sách (57 hộ)</span>
        </button>
    </div>

    <!-- Main Layout Container (2 Columns) -->
    <div class="tuyen-duong-layout" style="display: flex; flex: 1; overflow: hidden; position: relative;">
        
        <!-- Left Sidebar: Control Panel & Location Cards -->
        <aside class="tuyen-duong-sidebar" :class="{ 'tablet-collapsed': !tabletSidebarOpen }" style="width: 380px; background: #ffffff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; height: 100%; z-index: 10; flex-shrink: 0;">
            
            <!-- Sidebar Header & Search -->
            <div style="padding: 16px 16px 12px 16px; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, #059669, #10b981); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(5,150,105,0.3); flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M 3 17 L 10 3 L 17 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M 6.5 12 L 13.5 12" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="2 2"/>
                        </svg>
                    </div>
                    <div>
                        <h1 style="font-size: 1.05rem; font-weight: 900; color: #0f172a; margin: 0; font-family: 'Be Vietnam Pro', sans-serif; letter-spacing: -0.2px;">
                            TUYẾN ĐƯỜNG 4.0 <span style="font-size: 0.75rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 10px; margin-left: 4px;">ĐÔNG ANH</span>
                        </h1>
                        <p style="font-size: 0.72rem; color: #64748b; margin: 0;">Bản đồ số 57 hộ kinh doanh & tuyến đường thông minh</p>
                    </div>
                </div>

                <!-- Search Input -->
                <div style="position: relative; margin-bottom: 12px;">
                    <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%);" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <circle cx="7" cy="7" r="5.5" stroke="#94a3b8" stroke-width="1.8"/>
                        <path d="M 11 11 L 15 15" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input type="text" x-model="search" @input="updateMapMarkers()" placeholder="Tìm tên chủ hộ, tên quán, mặt hàng, thôn..." 
                           style="width: 100%; padding: 10px 14px 10px 38px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; font-size: 0.85rem; color: #0f172a; outline: none; transition: all 0.2s;"
                           onfocus="this.style.borderColor='#10b981'; this.style.background='#ffffff'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)';" 
                           onblur="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'; this.style.boxShadow='none';">
                </div>

                <!-- Category Chips -->
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button" class="filter-chip-btn" :class="{ 'active': activeCategory === cat.id }" @click="activeCategory = cat.id; updateMapMarkers();">
                            <span x-text="cat.icon"></span>
                            <span x-text="cat.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Route Filter Chips Section -->
            <div style="padding: 10px 16px; background: #fafafa; border-bottom: 1px solid #f1f5f9;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Lọc theo tuyến đường 4.0</span>
                    <button type="button" @click="resetFilters()" style="font-size: 0.72rem; color: #059669; background: none; border: none; font-weight: 700; cursor: pointer;">Đặt lại tất cả</button>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    <button type="button" @click="setVillage('all')" class="village-pill-btn v-all" :class="{ 'active': activeVillage === 'all' }">
                        Tất cả tuyến đường
                    </button>
                    <template x-for="v in villages" :key="v.id">
                        <button type="button" @click="setVillage(v.id)" class="village-pill-btn" :class="[`v-${v.id}`, activeVillage === v.id ? 'active' : '']">
                            <span x-text="v.routeName || v.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Quick Stats Summary inside Sidebar -->
            <div style="padding: 8px 16px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                <div style="background: #ffffff; padding: 6px 4px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 0.92rem; font-weight: 900; color: #059669; display: block; font-family: 'Be Vietnam Pro', sans-serif;">4</span>
                    <span style="font-size: 0.65rem; color: #64748b; font-weight: 700;">🛣️ Tuyến 4.0</span>
                </div>
                <div style="background: #ffffff; padding: 6px 4px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 0.92rem; font-weight: 900; color: #14b8a6; display: block; font-family: 'Be Vietnam Pro', sans-serif;">4</span>
                    <span style="font-size: 0.65rem; color: #64748b; font-weight: 700;">🏡 Thôn kết nối</span>
                </div>
                <div style="background: #ffffff; padding: 6px 4px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 0.92rem; font-weight: 900; color: #2563eb; display: block; font-family: 'Be Vietnam Pro', sans-serif;">57</span>
                    <span style="font-size: 0.65rem; color: #64748b; font-weight: 700;">🏪 Hộ kinh doanh</span>
                </div>
            </div>

            <!-- List Results Header -->
            <div style="padding: 10px 16px 6px 16px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.78rem; color: #64748b;">Hiển thị <strong style="color: #0f172a;" x-text="filteredLocations().length"></strong> hộ kinh doanh</span>
                <span style="font-size: 0.72rem; color: #94a3b8;">Chạm để xem chi tiết</span>
            </div>

            <!-- Location Cards Scrollable List -->
            <div class="custom-scrollbar" style="flex: 1; overflow-y: auto; padding: 6px 16px 16px 16px; display: flex; flex-direction: column; gap: 10px;">
                <template x-for="loc in filteredLocations()" :key="loc.id">
                    <div class="location-list-card" :class="{ 'selected': selectedLoc && selectedLoc.id === loc.id }" @click="selectLocation(loc)">
                        <!-- Card Photo -->
                        <div style="width: 105px; height: 95px; border-radius: 12px; overflow: hidden; position: relative; flex-shrink: 0; background: #e2e8f0;">
                            <img :src="loc.image" :alt="loc.name" style="width: 100%; height: 100%; object-fit: cover;">
                            <span :style="loc.open ? 'background: #10b981;' : 'background: #64748b;'" 
                                  style="position: absolute; top: 6px; left: 6px; color: #ffffff; padding: 2px 6px; border-radius: 8px; font-size: 0.62rem; font-weight: 800;" 
                                  x-text="loc.open ? '● Mở' : '● Đóng'"></span>
                        </div>

                        <!-- Card Info -->
                        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 6px; margin-bottom: 3px;">
                                    <h3 style="font-size: 0.88rem; font-weight: 700; color: #0f172a; margin: 0; font-family: 'Be Vietnam Pro', sans-serif; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="loc.name"></h3>
                                    <div style="display: flex; align-items: center; gap: 2px; background: #fffbeb; border: 1px solid #fef3c7; padding: 1px 6px; border-radius: 8px; flex-shrink: 0;">
                                        <span style="color: #f59e0b; font-size: 0.7rem;">★</span>
                                        <span style="font-size: 0.72rem; font-weight: 800; color: #b45309; font-family: 'Be Vietnam Pro', sans-serif;" x-text="loc.rating"></span>
                                    </div>
                                </div>

                                <p style="font-size: 0.72rem; color: #64748b; margin: 0 0 6px 0; font-family: 'Be Vietnam Pro', sans-serif; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="loc.address"></p>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; flex-wrap: nowrap;">
                                <div style="display: flex; gap: 4px; align-items: center; min-width: 0;">
                                    <span class="card-tag-badge" :class="`card-tag-${loc.type}`"
                                          x-text="getCategoryLabel(loc.type)"></span>
                                </div>

                                <span class="card-village-badge" :class="`v-${loc.village}`"
                                      x-text="loc.villageName"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="filteredLocations().length === 0">
                    <div style="text-align: center; padding: 40px 16px; color: #94a3b8;">
                        <div style="font-size: 2.5rem; margin-bottom: 8px;">🗺️</div>
                        <p style="font-size: 0.88rem; font-weight: 800; color: #334155; margin: 0;">Không tìm thấy hộ kinh doanh</p>
                        <p style="font-size: 0.75rem; margin: 4px 0 0 0;">Hãy thử chọn từ khóa khác hoặc bấm "Đặt lại tất cả"</p>
                    </div>
                </template>
            </div>
        </aside>

        <!-- Right Main Interactive Leaflet Map Canvas -->
        <main class="tuyen-duong-map" style="flex: 1; height: 100%; position: relative; background: #e5e7eb; overflow: hidden;">
            
            <!-- Real Leaflet Map Container -->
            <div id="tuyenDuongMap" style="width: 100%; height: 100%; z-index: 1;"></div>

            <!-- Floating Tablet Sidebar Toggle Button (Visible on Tablet screens 768px-1023px) -->
            <button type="button" class="tablet-toggle-sidebar-btn" @click="toggleTabletSidebar()" 
                    style="display: none; position: absolute; top: 16px; left: 16px; z-index: 1001; background: #0f172a; color: #ffffff; border: none; padding: 9px 16px; border-radius: 24px; font-size: 0.8rem; font-weight: 800; cursor: pointer; align-items: center; gap: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.25);">
                <span x-text="tabletSidebarOpen ? '◀ Ẩn bảng 57 hộ' : '📋 Hiện 57 hộ'"></span>
            </button>

            <!-- Floating GPS Live Location Button -->
            <button type="button" @click="getUserLocation()" 
                    style="position: absolute; top: 16px; right: 16px; z-index: 500; background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); border: 1px solid #cbd5e1; padding: 9px 16px; border-radius: 24px; font-size: 0.8rem; font-weight: 800; color: #0f172a; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.14); transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);"
                    onmouseover="this.style.borderColor='#10b981'; this.style.color='#059669'; this.style.transform='translateY(-2px)';"
                    onmouseout="this.style.borderColor='#cbd5e1'; this.style.color='#0f172a'; this.style.transform='translateY(0)';">
                <span style="font-size: 1.05rem; display: inline-block; animation: pulsePing 2s infinite;">🎯</span>
                <span x-text="gpsLoading ? 'Đang định vị GPS...' : 'Vị trí của tôi (GPS)'"></span>
            </button>

        </main>
    </div>

    <!-- Location Detail Popup Modal -->
    <template x-if="selectedLoc">
        <div style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px;" @click="selectedLoc = null">
            <div class="overlay-animate-enter" style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(5px);"></div>

            <div class="modal-animate-enter" style="position: relative; background: #ffffff; border-radius: 24px; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.3); width: 100%; max-width: 440px; overflow: hidden; z-index: 10;" @click.stop>
                <!-- Hero Image Header -->
                <div style="position: relative; height: 190px; background: #e2e8f0; overflow: hidden;">
                    <img :src="selectedLoc.image" :alt="selectedLoc.name" style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);"></div>

                    <!-- Close Button -->
                    <button type="button" @click="selectedLoc = null" style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.92); border: none; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #1e293b; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">✕</button>

                    <!-- Open Status Badge -->
                    <span :style="selectedLoc.open ? 'background: #10b981;' : 'background: #ef4444;'" 
                          style="position: absolute; top: 12px; left: 12px; color: #ffffff; padding: 4px 12px; border-radius: 14px; font-size: 0.75rem; font-weight: 800; box-shadow: 0 2px 8px rgba(0,0,0,0.2);" 
                          x-text="selectedLoc.open ? '● Đang mở cửa' : '● Đã đóng cửa'"></span>
                </div>

                <!-- Scrollable Body Content -->
                <div class="custom-scrollbar" style="padding: 20px; max-height: calc(85vh - 190px); overflow-y: auto; text-align: left;">
                    
                    <!-- Title & Rating Badge -->
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 8px;">
                        <h2 style="font-size: 1.15rem; font-weight: 900; color: #0f172a; margin: 0; font-family: 'Be Vietnam Pro', sans-serif; line-height: 1.25;" x-text="selectedLoc.name"></h2>
                        <div style="display: flex; align-items: center; gap: 4px; background: #fffbeb; border: 1px solid #fef3c7; padding: 4px 8px; border-radius: 12px; flex-shrink: 0;">
                            <span style="color: #f59e0b; font-size: 0.9rem;">★</span>
                            <span style="font-size: 0.88rem; font-weight: 800; color: #b45309;" x-text="selectedLoc.rating"></span>
                        </div>
                    </div>

                    <p style="font-size: 0.82rem; color: #64748b; margin: 0 0 6px 0; display: flex; align-items: center; gap: 6px;">
                        📍 <span x-text="selectedLoc.address"></span>
                    </p>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0 0 6px 0; display: flex; align-items: center; gap: 6px;">
                        👤 Chủ hộ: <strong style="color: #0f172a;" x-text="selectedLoc.owner || selectedLoc.name"></strong>
                    </p>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0 0 16px 0; display: flex; align-items: center; gap: 6px;">
                        📞 Hotline: <strong style="color: #059669;" x-text="selectedLoc.phone || 'Đang cập nhật'"></strong>
                    </p>

                    <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 16px 0;">

                    <!-- Business Items & Price List -->
                    <div style="margin-bottom: 16px;">
                        <p style="font-size: 0.72rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 8px 0;">Mặt hàng kinh doanh & Niêm yết giá</p>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <template x-for="item in selectedLoc.menu" :key="item">
                                <span style="padding: 4px 10px; background: #f0fdf4; color: #047857; font-size: 0.78rem; font-weight: 700; border-radius: 8px; border: 1px solid #a7f3d0;" x-text="item"></span>
                            </template>
                        </div>
                    </div>

                    <!-- VietQR Payment Card -->
                    <div style="margin-bottom: 16px;">
                        <p style="font-size: 0.72rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 8px 0;">Tài khoản VietQR Tuyến đường 4.0</p>
                        <template x-if="selectedLoc.bankAccount">
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                <div>
                                    <p style="font-size: 0.7rem; color: #64748b; margin: 0 0 2px 0; font-weight: 600;">Số tài khoản thanh toán</p>
                                    <p style="font-family: monospace; font-size: 0.98rem; font-weight: 900; color: #0f172a; margin: 0;" x-text="selectedLoc.bankAccount"></p>
                                    <p style="font-size: 0.75rem; color: #059669; margin: 2px 0 0 0; font-weight: 700;" x-text="selectedLoc.bank"></p>
                                </div>
                                <div style="background: #ffffff; padding: 6px; border-radius: 12px; border: 1px solid #cbd5e1; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                                    <img :src="getVietQrUrl(selectedLoc.bank, selectedLoc.bankAccount, selectedLoc.owner)" 
                                         alt="Mã VietQR" 
                                         style="width: 80px; height: 80px; object-fit: contain; display: block; border-radius: 6px;"
                                         loading="lazy">
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedLoc.bankAccount">
                            <p style="font-size: 0.78rem; color: #94a3b8; margin: 0; font-style: italic;">Đang cập nhật mã VietQR tại cửa hàng</p>
                        </template>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 10px; padding-top: 4px;">
                        <a :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(selectedLoc.name + ' ' + selectedLoc.address)}`" target="_blank" 
                           style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; background: #059669; color: #ffffff; font-weight: 800; padding: 12px; border-radius: 14px; text-decoration: none; font-size: 0.88rem; transition: background 0.2s; box-shadow: 0 4px 12px rgba(5,150,105,0.25);" 
                           onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                            ➔ Chỉ đường Maps
                        </a>
                        <a :href="`tel:${selectedLoc.phone}`" 
                           style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; background: #f97316; color: #ffffff; font-weight: 800; padding: 12px; border-radius: 14px; text-decoration: none; font-size: 0.88rem; transition: background 0.2s; box-shadow: 0 4px 12px rgba(249,115,22,0.25);" 
                           onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                            📞 Gọi hotline
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function tuyenDuongApp() {
        return {
            map: null,
            clusterGroup: null,
            search: '',
            activeCategory: 'all',
            activeVillage: 'all',
            activeRouteId: 'all',
            selectedLoc: null,
            markersMap: {},
            polylinesMap: {},
            glowPolylinesMap: {},
            gpsLoading: false,
            userGpsMarker: null,
            userGpsCircle: null,

            categories: [
                { id: 'all', label: 'Tất cả', icon: '🗺️' },
                { id: 'quan-an', label: 'Quán ăn & Phở', icon: '🍜' },
                { id: 'nha-hang', label: 'Nhà hàng & Hải sản', icon: '🍽️' },
                { id: 'tap-hoa', label: 'Tạp hóa & Bán lẻ', icon: '🛒' },
                { id: 'thoi-trang', label: 'Thời trang & Đồ dùng', icon: '👕' },
                { id: 'y-te', label: 'Y tế & Thẩm mỹ', icon: '💊' },
                { id: 'dich-vu', label: 'Dịch vụ & Kỹ thuật', icon: '🔧' }
            ],

            villages: [
                { id: 'phu-loc', name: 'Phúc Lộc', routeName: 'Đường Phúc Lộc', color: '#F97316' },
                { id: 'dong-anh-cum-3', name: 'Đông Anh (Cụm 3)', routeName: 'Quốc Lộ 3 (Cụm 3)', color: '#10B981' },
                { id: 'duc-noi', name: 'Dục Nội', routeName: 'Đường Cổ Vân', color: '#EAB308' },
                { id: 'viet-hung', name: 'Việt Hùng', routeName: 'Tuyến Việt Hùng', color: '#3B82F6' }
            ],

            routes: @json($dbRoutes ?? []).length > 0 ? @json($dbRoutes) : [
                {
                    id: 'route-phu-loc',
                    name: 'Tuyến 1: Đường Phúc Lộc (Phúc Lộc)',
                    length: '1.2km',
                    color: '#F97316',
                    villages: ['phu-loc'],
                    animClass: 'route-path-animated-1',
                    pathCoords: [
                        [21.1370, 105.8360],
                        [21.1390, 105.8390],
                        [21.1415, 105.8430]
                    ]
                },
                {
                    id: 'route-ql3',
                    name: 'Tuyến 2: Quốc Lộ 3 (Cụm 3)',
                    length: '1.8km',
                    color: '#10B981',
                    villages: ['dong-anh-cum-3'],
                    animClass: 'route-path-animated-2',
                    pathCoords: [
                        [21.1420, 105.8440],
                        [21.1360, 105.8470],
                        [21.1300, 105.8495]
                    ]
                },
                {
                    id: 'route-co-van',
                    name: 'Tuyến 3: Đường Cổ Vân (Dục Nội)',
                    length: '0.6km',
                    color: '#EAB308',
                    villages: ['duc-noi'],
                    animClass: 'route-path-animated-3',
                    pathCoords: [
                        [21.1448, 105.8640],
                        [21.1455, 105.8670],
                        [21.1462, 105.8700]
                    ]
                },
                {
                    id: 'route-viet-hung',
                    name: 'Tuyến 4: Tuyến Việt Hùng (Việt Hùng)',
                    length: '0.8km',
                    color: '#3B82F6',
                    villages: ['viet-hung'],
                    animClass: 'route-path-animated-1',
                    pathCoords: [
                        [21.1465, 105.8705],
                        [21.1490, 105.8720],
                        [21.1515, 105.8735]
                    ]
                }
            ],

            locations: @json($dbLocations ?? []).length > 0 ? @json($dbLocations) : [
                // CSV 3.1: Đường Phúc Lộc (Thôn Phúc Lộc - 24 hộ)
                { id: 1, name: 'Kính thuốc Hồng Thái', owner: 'Hồng Thái', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'y-te', rating: 4.8, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0988364444', bankAccount: '8877923955', bank: 'BIDV', open: true, menu: ['Kính mắt các loại'], image: 'https://images.unsplash.com/photo-1591076482161-42ce6da69f67?w=420&h=230&fit=crop&auto=format', lat: 21.1350, lng: 105.8320 },
                { id: 2, name: 'Thẩm mỹ Karena', owner: 'Nguyễn Văn Phòng', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'y-te', rating: 4.7, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0988364445', bankAccount: '2170034293', bank: 'BIDV', open: true, menu: ['Chăm sóc da các loại'], image: 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=420&h=230&fit=crop&auto=format', lat: 21.1355, lng: 105.8330 },
                { id: 3, name: 'Quán ăn sáng Nguyễn Thị Huệ', owner: 'Nguyễn Thị Huệ', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.9, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0988364446', bankAccount: '22993399999', bank: 'MB Bank', open: true, menu: ['Bún ngan', 'Miến gà', 'Bún gà ta'], image: 'https://images.unsplash.com/photo-1597345637412-9fd611e758f3?w=420&h=230&fit=crop&auto=format', lat: 21.1360, lng: 105.8340 },
                { id: 4, name: 'CT TNHH ĐTXD TM&DV ĐA', owner: 'Trương Văn Lân', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'dich-vu', rating: 4.5, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0988364447', bankAccount: '23656971', bank: 'ACB', open: true, menu: ['In ấn quảng cáo', 'Photocopy dịch thuật'], image: 'https://images.unsplash.com/photo-1562654501-a0ccc0fc3fb1?w=420&h=230&fit=crop&auto=format', lat: 21.1365, lng: 105.8350 },
                { id: 5, name: 'Cửa hàng Đông Đô', owner: 'Trần Thị Thảo', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'tap-hoa', rating: 4.6, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0988364448', bankAccount: '0972511711', bank: 'BIDV', open: true, menu: ['Tạp hóa', 'Văn phòng phẩm'], image: 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=420&h=230&fit=crop&auto=format', lat: 21.1370, lng: 105.8360 },
                { id: 6, name: 'Quán Cà Phê Howse', owner: 'Nguyễn Tuấn Anh', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.9, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0988364449', bankAccount: '0880105359999', bank: 'MB Bank', open: true, menu: ['Cà phê muối', 'Trà hoa quả', 'Nước giải khát'], image: 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=420&h=230&fit=crop&auto=format', lat: 21.1375, lng: 105.8370 },
                { id: 7, name: 'HC Mobile', owner: 'Dương Quý Hợi', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'dich-vu', rating: 4.8, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0911571983', bankAccount: '19033822650021', bank: 'Techcombank', open: true, menu: ['Mua bán điện thoại', 'Sửa chữa ép kính'], image: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=420&h=230&fit=crop&auto=format', lat: 21.1380, lng: 105.8380 },
                { id: 8, name: 'Thanh Mai HSK', owner: 'Đỗ Thị Hường', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'dich-vu', rating: 4.9, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0965522035', bankAccount: '020110732008', bank: 'Sacombank', open: true, menu: ['Dạy tiếng Trung HSK', 'Luyện thi chứng chỉ'], image: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=420&h=230&fit=crop&auto=format', lat: 21.1385, lng: 105.8390 },
                { id: 9, name: 'Cửa hàng Xuân Nguyễn', owner: 'Ngô Thanh Xuân', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'thoi-trang', rating: 4.6, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0949906689', bankAccount: '0949906689', bank: 'MB Bank', open: true, menu: ['Quần áo trẻ em', 'Thời trang bé trai bé gái'], image: 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=420&h=230&fit=crop&auto=format', lat: 21.1390, lng: 105.8400 },
                { id: 10, name: 'Dâu Tây Shop', owner: 'Nguyễn Thanh Thùy', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'thoi-trang', rating: 4.7, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0986068246', bankAccount: '0986068246', bank: 'BIDV', open: true, menu: ['Quần áo thời trang nữ', 'Váy đầm hot trend'], image: 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=420&h=230&fit=crop&auto=format', lat: 21.1395, lng: 105.8410 },
                { id: 11, name: 'Trung Tâm Viettel Đông Anh', owner: 'Nguyễn Chí Hiếu', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'dich-vu', rating: 4.8, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0987783898', bankAccount: '0001232184569', bank: 'MB Bank', open: true, menu: ['Dịch vụ viễn thông', 'Đăng ký SIM 4G/5G', 'Internet cáp quang'], image: 'https://images.unsplash.com/photo-1556742049-0a670f4a4587?w=420&h=230&fit=crop&auto=format', lat: 21.1400, lng: 105.8420 },
                { id: 12, name: 'Cửa hàng Đồ Điện Duyên', owner: 'Nguyễn Thị Duyên', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'tap-hoa', rating: 4.5, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0986460490', bankAccount: '8818039982', bank: 'BIDV', open: true, menu: ['Đồ điện gia dụng', 'Thiết bị chiếu sáng', 'Tạp hóa điện'], image: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=420&h=230&fit=crop&auto=format', lat: 21.1405, lng: 105.8430 },
                { id: 13, name: 'Công ty Luật Minh Nghiêm', owner: 'Đinh Đức Duy', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'dich-vu', rating: 4.9, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0917768512', bankAccount: '451106868', bank: 'MB Bank', open: true, menu: ['Tư vấn pháp luật', 'Hồ sơ doanh nghiệp', 'Tranh tụng đất đai'], image: 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=420&h=230&fit=crop&auto=format', lat: 21.1410, lng: 105.8440 },
                { id: 14, name: 'Quán Phở Gà Đạt', owner: 'Lê Quý Đạt', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.8, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '09694444186', bankAccount: '8836868686', bank: 'BIDV', open: true, menu: ['Phở gà ta đùi chặt', 'Bún gà đùi', 'Miến gà lá chanh'], image: 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?w=420&h=230&fit=crop&auto=format', lat: 21.1415, lng: 105.8450 },
                { id: 15, name: 'Đồ Ăn Vặt Oanh Oanh', owner: 'Dương T Hồng Oanh', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.7, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0968645654', bankAccount: '9886838888', bank: 'MB Bank', open: true, menu: ['Đồ ăn vặt học sinh', 'Trà sữa trâu châu', 'Nem chua rán'], image: 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=420&h=230&fit=crop&auto=format', lat: 21.1420, lng: 105.8460 },
                { id: 16, name: 'Cửa hàng Đồ Xe Máy Thắng', owner: 'Lê Trung Thắng', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'dich-vu', rating: 4.6, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0364100366', bankAccount: '1064232172', bank: 'Vietcombank', open: true, menu: ['Phụ tùng xe máy chính hãng', 'Sửa chữa bảo dưỡng xe'], image: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=420&h=230&fit=crop&auto=format', lat: 21.1425, lng: 105.8470 },
                { id: 17, name: 'Tu Mimi Beauty', owner: 'Dương Minh Tú', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'y-te', rating: 4.8, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0975320130', bankAccount: '2142654818', bank: 'BIDV', open: true, menu: ['Làm móng Nail Art', 'Nối mi tự nhiên'], image: 'https://images.unsplash.com/photo-1604654894610-df63bc536371?w=420&h=230&fit=crop&auto=format', lat: 21.1430, lng: 105.8480 },
                { id: 18, name: 'Quán Giải Khát Huyện', owner: 'Phạm Quang Minh', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.5, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0862874945', bankAccount: '1053307896', bank: 'Vietcombank', open: true, menu: ['Nước giải khát đóng chai', 'Nước dừa tươi', 'Sinh tố dầm'], image: 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?w=420&h=230&fit=crop&auto=format', lat: 21.1435, lng: 105.8490 },
                { id: 19, name: 'Gia Khánh AUTO', owner: 'Hoàng Ngọc Bình', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'dich-vu', rating: 4.9, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0898620898', bankAccount: '8845821677', bank: 'BIDV', open: true, menu: ['Rửa xe bọt tuyết', 'Chăm sóc nội thất ô tô'], image: 'https://images.unsplash.com/photo-1520340356584-f9917d1eea6f?w=420&h=230&fit=crop&auto=format', lat: 21.1440, lng: 105.8500 },
                { id: 20, name: 'Bún Chả Bà Mai', owner: 'Nguyễn Văn Bình', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.9, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0975525645', bankAccount: '2143888999', bank: 'BIDV', open: true, menu: ['Bún chả nướng than hoa', 'Nem hải sản rán'], image: 'https://images.unsplash.com/photo-1541544741938-0af808871cc0?w=420&h=230&fit=crop&auto=format', lat: 21.1445, lng: 105.8510 },
                { id: 21, name: 'Sugy Tea & Cà Phê', owner: 'Mai Xuân Hải', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.8, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0989653387', bankAccount: '36708888', bank: 'Vietinbank', open: true, menu: ['Trà San Tuyết cổ thụ', 'Cà phê phin truyền thống'], image: 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=420&h=230&fit=crop&auto=format', lat: 21.1450, lng: 105.8520 },
                { id: 22, name: 'Phở Tuấn Phố Cổ', owner: 'Hoàng Văn Lâm', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.8, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0373237704', bankAccount: '26016868888', bank: 'Techcombank', open: true, menu: ['Phở bò tái chín', 'Phở sốt vang đặc biệt'], image: 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f6?w=420&h=230&fit=crop&auto=format', lat: 21.1455, lng: 105.8530 },
                { id: 23, name: 'Trà Trái Cây 9', owner: 'Lý Văn Vượng', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'quan-an', rating: 4.7, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0987738683', bankAccount: '103871251181', bank: 'Vietinbank', open: true, menu: ['Trà trái cây nhiệt đới', 'Trà xoài mắng cầu tươi'], image: 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=420&h=230&fit=crop&auto=format', lat: 21.1460, lng: 105.8540 },
                { id: 24, name: 'NH Hải Sản Hoàng Quân', owner: 'Vũ Hoàng Giang', village: 'phu-loc', villageName: 'Thôn Phúc Lộc', type: 'nha-hang', rating: 4.9, address: 'Đường Phúc Lộc, Thôn Phúc Lộc, Đông Anh', phone: '0914470285', bankAccount: '3330914470285', bank: 'PG Bank', open: true, menu: ['Hải sản tươi sống bể cá', 'Cua Cà Mau hấp', 'Tôm hùm nướng'], image: 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=420&h=230&fit=crop&auto=format', lat: 21.1465, lng: 105.8550 },

                // CSV 3.2: Quốc Lộ 3 (Thôn Đông Anh Cụm 3 - 19 hộ)
                { id: 25, name: 'Điện Tử Đỗ Minh Tuấn', owner: 'Đỗ Minh Tuấn', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'dich-vu', rating: 4.7, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0987987272', bankAccount: '2812195899', bank: 'MB Bank', open: true, menu: ['Sửa chữa điện tử', 'Bảo dưỡng điều hòa máy giặt'], image: 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=420&h=230&fit=crop&auto=format', lat: 21.1445, lng: 105.8405 },
                { id: 26, name: 'Dán Đề Can Chu Văn Châu', owner: 'Chu Văn Châu', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'dich-vu', rating: 4.6, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0983731243', bankAccount: '8844532936', bank: 'BIDV', open: true, menu: ['Dán đề can xe máy ô tô', 'Dán kính chống nóng'], image: 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=420&h=230&fit=crop&auto=format', lat: 21.1435, lng: 105.8415 },
                { id: 27, name: 'Nội Thất Hoàng Thu Hà', owner: 'Hoàng Thị Thu Hà', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.8, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0982395197', bankAccount: '19033095542014', bank: 'Techcombank', open: true, menu: ['Nội thất gỗ cao cấp', 'Bàn ghế sofa phòng khách'], image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=420&h=230&fit=crop&auto=format', lat: 21.1425, lng: 105.8425 },
                { id: 28, name: 'Nội Thất Thu Trang', owner: 'Phan Thị Thu Trang', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.7, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0915344122', bankAccount: '19036732871018', bank: 'Techcombank', open: true, menu: ['Giường tủ hiện đại', 'Tủ bếp nhựa picomat'], image: 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=420&h=230&fit=crop&auto=format', lat: 21.1415, lng: 105.8435 },
                { id: 29, name: 'Quán Ăn Nguyễn Thị Thu', owner: 'Nguyễn Thị Thu', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'quan-an', rating: 4.5, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0900000000', bankAccount: '', bank: '', open: true, menu: ['Cơm bình dân', 'Món ăn gia đình'], image: 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=420&h=230&fit=crop&auto=format', lat: 21.1405, lng: 105.8445 },
                { id: 30, name: 'Chăn Ga Gối Đệm Hoàng Xuyên', owner: 'Hoàng Thị Xuyên', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.8, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0915384100', bankAccount: '8600580999', bank: 'BIDV', open: true, menu: ['Chăn ga gối đệm sông Hồng', 'Đệm cao su thiên nhiên'], image: 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=420&h=230&fit=crop&auto=format', lat: 21.1395, lng: 105.8455 },
                { id: 31, name: 'Giầy Dép Túi Xách Xuân Lâm', owner: 'Nguyễn Xuân Lâm', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.7, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0986676805', bankAccount: '0986676805', bank: 'MB Bank', open: true, menu: ['Giày da nam nữ', 'Túi xách công sở Việt Nam'], image: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=420&h=230&fit=crop&auto=format', lat: 21.1385, lng: 105.8465 },
                { id: 32, name: 'Nội Thất Bùi Thị Châm', owner: 'Bùi Thị Châm', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.6, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0976149380', bankAccount: '53040117', bank: 'VPBank', open: true, menu: ['Nội thất gỗ công nghiệp', 'Kệ tivi phòng ngủ'], image: 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=420&h=230&fit=crop&auto=format', lat: 21.1375, lng: 105.8475 },
                { id: 33, name: 'Nội Thất Ô Tô Nguyễn Doãn Đạt', owner: 'Nguyễn Doãn Đạt', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'dich-vu', rating: 4.9, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0976262543', bankAccount: '225588599', bank: 'VPBank', open: true, menu: ['Bọc ghế da ô tô', 'Dán phim cách nhiệt 3M'], image: 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=420&h=230&fit=crop&auto=format', lat: 21.1365, lng: 105.8485 },
                { id: 34, name: 'Quán Phở Nguyễn Văn Đức', owner: 'Nguyễn Văn Đức', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'quan-an', rating: 4.8, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0902982326', bankAccount: '19034876799013', bank: 'Techcombank', open: true, menu: ['Phở bò tái gầu', 'Quẩy giòn tan'], image: 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?w=420&h=230&fit=crop&auto=format', lat: 21.1355, lng: 105.8495 },
                { id: 35, name: 'Công Ty Thiết Bị Điện Đức Cường', owner: 'Đức Cường', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'dich-vu', rating: 4.8, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0974330955', bankAccount: '0311100998888', bank: 'MB Bank', open: true, menu: ['Thiết bị điện công nghiệp', 'Dây cáp điện Cadivi'], image: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=420&h=230&fit=crop&auto=format', lat: 21.1345, lng: 105.8505 },
                { id: 36, name: 'Giầy Dép Thể Thao Phương Thảo', owner: 'Lê Phương Thảo', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.7, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0397947165', bankAccount: '2027131012', bank: 'Vietcombank', open: true, menu: ['Giày thể thao Sneaker', 'Giày chạy bộ nam nữ'], image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=420&h=230&fit=crop&auto=format', lat: 21.1335, lng: 105.8515 },
                { id: 37, name: 'Nội Thất Công Ty Nam Hải', owner: 'Nam Hải', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.8, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0989059699', bankAccount: '2140496962', bank: 'BIDV', open: true, menu: ['Nội thất văn phòng', 'Bàn làm việc giám đốc'], image: 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=420&h=230&fit=crop&auto=format', lat: 21.1325, lng: 105.8525 },
                { id: 38, name: 'Nội Thất Trương Văn Thành', owner: 'Trương Văn Thành', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.6, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0853287888', bankAccount: '060316153429', bank: 'VPBank', open: true, menu: ['Sofa góc chữ L', 'Bàn ăn thông minh'], image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=420&h=230&fit=crop&auto=format', lat: 21.1315, lng: 105.8535 },
                { id: 39, name: 'Nội Thất Đỗ Bá Phương', owner: 'Đỗ Bá Phương', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.7, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0983346126', bankAccount: '8606882666', bank: 'BIDV', open: true, menu: ['Tủ quần áo cánh kính', 'Kệ trang trí'], image: 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=420&h=230&fit=crop&auto=format', lat: 21.1305, lng: 105.8545 },
                { id: 40, name: 'Nội Thất Minh Thư', owner: 'Đào Thị Minh Thư', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.8, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0987805118', bankAccount: '571349752', bank: 'VPBank', open: true, menu: ['Nội thất chung cư trọn gói', 'Bàn học trẻ em'], image: 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=420&h=230&fit=crop&auto=format', lat: 21.1295, lng: 105.8555 },
                { id: 41, name: 'Quán Phở Đặng Bá Huy', owner: 'Đặng Bá Huy', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'quan-an', rating: 4.9, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0948022005', bankAccount: '19037131028018', bank: 'Techcombank', open: true, menu: ['Phở gia truyền Đông Anh', 'Quẩy giòn nóng'], image: 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?w=420&h=230&fit=crop&auto=format', lat: 21.1285, lng: 105.8565 },
                { id: 42, name: 'Chăn Ga Gối Đệm Lê Quốc Quân', owner: 'Lê Quốc Quân', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'thoi-trang', rating: 4.7, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0357274664', bankAccount: '0961000016673', bank: 'Vietcombank', open: true, menu: ['Chăn ga Hàn Quốc', 'Đệm bông ép Everon'], image: 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=420&h=230&fit=crop&auto=format', lat: 21.1275, lng: 105.8575 },
                { id: 43, name: 'Công Ty Điện Dân Dụng Tràng Thị', owner: 'Tràng Thị', village: 'dong-anh-cum-3', villageName: 'Thôn Đông Anh (Cụm 3)', type: 'dich-vu', rating: 4.8, address: 'Tây QL3, Thôn Đông Anh (Cụm 3), Đông Anh', phone: '0976173907', bankAccount: '50530100454', bank: 'Sacombank', open: true, menu: ['Điện gia dụng Pana', 'Công tắc ổ cắm Sino'], image: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=420&h=230&fit=crop&auto=format', lat: 21.1265, lng: 105.8585 },

                // CSV 2: Đường Cổ Vân (Thôn Dục Nội - 11 hộ)
                { id: 44, name: 'Quầy Thuốc Trung Hiếu 2', owner: 'Trung Hiếu', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'y-te', rating: 4.9, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0975624998', bankAccount: '8807196124', bank: 'BIDV', open: true, menu: ['Thuốc tân dược', 'Dụng cụ y tế gia đình'], image: 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=420&h=230&fit=crop&auto=format', lat: 21.1250, lng: 105.8600 },
                { id: 45, name: 'Nhà Thuốc Hoàng Long', owner: 'Hoàng Long', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'y-te', rating: 4.9, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0981872297', bankAccount: 'V3SM2024005860', bank: 'BIDV VietQR', open: true, menu: ['Bán lẻ thuốc chuẩn GPP', 'Mỹ phẩm chăm sóc sức khỏe'], image: 'https://images.unsplash.com/photo-1631549916768-4119b2e5f926?w=420&h=230&fit=crop&auto=format', lat: 21.1260, lng: 105.8610 },
                { id: 46, name: 'Cửa Hàng Tạp Hóa Ngô Văn Linh', owner: 'Ngô Văn Linh', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'tap-hoa', rating: 4.6, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0974915596', bankAccount: '19072079042015', bank: 'Techcombank', open: true, menu: ['Tạp hóa gia đình', 'Bánh kẹo sữa tươi'], image: 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=420&h=230&fit=crop&auto=format', lat: 21.1270, lng: 105.8620 },
                { id: 47, name: 'Cửa Hàng Hàng Đa Dụng Nguyễn Công Lục', owner: 'Nguyễn Công Lục', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'tap-hoa', rating: 4.7, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0989892424', bankAccount: '3156495', bank: 'MOMO', open: true, menu: ['Đồ dùng đa năng', 'Vật dụng tiện ích nhà bếp'], image: 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=420&h=230&fit=crop&auto=format', lat: 21.1280, lng: 105.8630 },
                { id: 48, name: 'Giày Dép Linh Trang', owner: 'Linh Trang', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'thoi-trang', rating: 4.8, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0966813762', bankAccount: '19032666997026', bank: 'Techcombank', open: true, menu: ['Giày dép thời trang', 'Sandal học sinh'], image: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=420&h=230&fit=crop&auto=format', lat: 21.1290, lng: 105.8640 },
                { id: 49, name: 'Cửa Hàng May Mặc Công Thị Mẫn', owner: 'Công Thị Mẫn', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'thoi-trang', rating: 4.6, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0396882886', bankAccount: '8858480785', bank: 'BIDV', open: true, menu: ['Quần áo may mặc', 'Đồ da & giả da cao cấp'], image: 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=420&h=230&fit=crop&auto=format', lat: 21.1300, lng: 105.8650 },
                { id: 50, name: 'Tạp Hóa Nguyễn Công Đa', owner: 'Nguyễn Công Đa', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'tap-hoa', rating: 4.5, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0972233572', bankAccount: '', bank: '', open: true, menu: ['Tạp hóa bánh kẹo', 'Nước giải khát'], image: 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=420&h=230&fit=crop&auto=format', lat: 21.1310, lng: 105.8660 },
                { id: 51, name: 'Tạp Hóa Lân Tịnh', owner: 'Lân Tịnh', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'tap-hoa', rating: 4.7, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0392031313', bankAccount: '2142773634', bank: 'BIDV', open: true, menu: ['Bánh kẹo nhập khẩu', 'Đồ khô gia đình'], image: 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=420&h=230&fit=crop&auto=format', lat: 21.1320, lng: 105.8670 },
                { id: 52, name: 'Cửa Hàng Hanoximex Dục Nội', owner: 'Hanoximex', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'thoi-trang', rating: 4.8, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0988158056', bankAccount: '', bank: '', open: true, menu: ['Thời trang dệt kim Hanoximex', 'Phụ kiện quần áo'], image: 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=420&h=230&fit=crop&auto=format', lat: 21.1330, lng: 105.8680 },
                { id: 53, name: 'Đồ Điện Nước Nguyễn Công Mạc', owner: 'Nguyễn Công Mạc', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'dich-vu', rating: 4.7, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0346036574', bankAccount: '0385055346', bank: 'MB Bank', open: true, menu: ['Đồ điện nước gia đình', 'Ống nhựa Tiền Phong'], image: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=420&h=230&fit=crop&auto=format', lat: 21.1340, lng: 105.8690 },
                { id: 54, name: 'Cửa Hàng Đồ Khô Vân Khoa', owner: 'Vân Khoa', village: 'duc-noi', villageName: 'Thôn Dục Nội', type: 'tap-hoa', rating: 4.8, address: 'Đường Cổ Vân, Thôn Dục Nội, Đông Anh', phone: '0358908488', bankAccount: '3099669', bank: 'MOMO', open: true, menu: ['Nấm hương mộc nhĩ khô', 'Miến riềng măng khô'], image: 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=420&h=230&fit=crop&auto=format', lat: 21.1350, lng: 105.8700 },

                // CSV 1: Tuyến Đường Thôn Việt Hùng (Thôn Việt Hùng - 3 hộ)
                { id: 55, name: 'Cửa Hàng Nông Sản Ngô Thế Long', owner: 'Ngô Thế Long', village: 'viet-hung', villageName: 'Thôn Việt Hùng', type: 'tap-hoa', rating: 4.9, address: 'Tuyến Việt Hùng, Thôn Việt Hùng, Đông Anh', phone: '0368794411', bankAccount: '0368794411', bank: 'Vietinbank', open: true, menu: ['Dưa cà muối sẵn (5k/túi)', 'Gia vị nấu ăn', 'Bánh kẹo & Sữa tươi'], image: 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=420&h=230&fit=crop&auto=format', lat: 21.1480, lng: 105.8620 },
                { id: 56, name: 'Đại Lý Bia Nước Ngọt Đào Xuân Hải', owner: 'Đào Xuân Hải', village: 'viet-hung', villageName: 'Thôn Việt Hùng', type: 'tap-hoa', rating: 4.9, address: 'Tuyến Việt Hùng, Thôn Việt Hùng, Đông Anh', phone: '0983164384', bankAccount: '8821490906', bank: 'BIDV', open: true, menu: ['Bia hơi Hà Nội (26k/lít)', 'Bia lon Hà Nội & Sài Gòn', 'Nước ngọt & Bánh kẹo Thành Trung'], image: 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=420&h=230&fit=crop&auto=format', lat: 21.1500, lng: 105.8650 },
                { id: 57, name: 'Cửa Hàng Tổng Hợp Nguyễn Văn Nguyên', owner: 'Nguyễn Văn Nguyên', village: 'viet-hung', villageName: 'Thôn Việt Hùng', type: 'tap-hoa', rating: 4.9, address: 'Tuyến Việt Hùng, Thôn Việt Hùng, Đông Anh', phone: '0372314727', bankAccount: 'PMC.2609116300000024', bank: 'MOMO VietQR', open: true, menu: ['Bia hơi Hà Nội (26k/lít)', 'Dầu ăn (50k/lít)', 'Mỳ tôm & Bánh kẹo Hải Hà'], image: 'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=420&h=230&fit=crop&auto=format', lat: 21.1520, lng: 105.8680 }
            ],

            initMap() {
                this.$nextTick(() => {
                    if (typeof L === 'undefined') return;

                    // Automatically calculate exact lat/lng for every store directly on top of its assigned route line
                    const routeMap = {};
                    this.routes.forEach(r => { routeMap[r.id] = r.pathCoords; });

                    const locsByVillage = { 'phu-loc': [], 'dong-anh-cum-3': [], 'duc-noi': [], 'viet-hung': [] };
                    this.locations.forEach(loc => { if (locsByVillage[loc.village]) locsByVillage[loc.village].push(loc); });

                    function getPointOnPath(coords, t) {
                        if (!coords || coords.length === 0) return [21.1375, 105.8480];
                        if (coords.length === 1 || t <= 0) return coords[0];
                        if (t >= 1) return coords[coords.length - 1];
                        const totalSegs = coords.length - 1;
                        const scaledT = t * totalSegs;
                        const segIdx = Math.floor(scaledT);
                        const segFrac = scaledT - segIdx;
                        const p1 = coords[Math.min(segIdx, totalSegs)];
                        const p2 = coords[Math.min(segIdx + 1, totalSegs)];
                        return [p1[0] + (p2[0] - p1[0]) * segFrac, p1[1] + (p2[1] - p1[1]) * segFrac];
                    }

                    const villageToRouteKey = {
                        'phu-loc': 'route-phu-loc',
                        'dong-anh-cum-3': 'route-ql3',
                        'duc-noi': 'route-co-van',
                        'viet-hung': 'route-viet-hung'
                    };

                    Object.keys(locsByVillage).forEach(vKey => {
                        const list = locsByVillage[vKey];
                        const rKey = villageToRouteKey[vKey];
                        const path = routeMap[rKey];
                        if (!path) return;
                        list.forEach((loc, idx) => {
                            const t = list.length > 1 ? (idx / (list.length - 1)) : 0.5;
                            const pt = getPointOnPath(path, t);
                            loc.lat = pt[0];
                            loc.lng = pt[1];
                        });
                    });

                    // Initialize Leaflet Map centered on Đông Anh
                    this.map = L.map('tuyenDuongMap', {
                        center: [21.1375, 105.8480],
                        zoom: 13,
                        zoomControl: false
                    });

                    // Add Custom Zoom Control to top-left
                    L.control.zoom({ position: 'topleft' }).addTo(this.map);

                    // Add High-Quality CartoDB Voyager Map Tiles
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://carto.com/">CARTO</a> &copy; OpenStreetMap',
                        subdomains: 'abcd',
                        maxZoom: 19
                    }).addTo(this.map);

                    // Initialize Marker Cluster Group for high-density scalability
                    if (typeof L.markerClusterGroup !== 'undefined') {
                        this.clusterGroup = L.markerClusterGroup({
                            disableClusteringAtZoom: 16,
                            maxClusterRadius: 35,
                            spiderfyOnMaxZoom: true,
                            showCoverageOnHover: false
                        });
                        this.map.addLayer(this.clusterGroup);
                    }

                    // Render Animated Polyline Routes
                    this.renderRoutes();

                    // Render Scalable Compact Pins & Cluster Group
                    this.renderMarkers();
                });
            },

            getUserLocation() {
                if (!navigator.geolocation) {
                    alert('Trình duyệt của bạn không hỗ trợ định vị GPS.');
                    return;
                }

                this.gpsLoading = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.gpsLoading = false;
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        if (this.map) {
                            // Fly map camera to user position
                            this.map.flyTo([lat, lng], 16, { duration: 1.5 });

                            // Remove existing user GPS markers if any
                            if (this.userGpsMarker) this.map.removeLayer(this.userGpsMarker);
                            if (this.userGpsCircle) this.map.removeLayer(this.userGpsCircle);

                            // Add pulsing blue user marker
                            const userGpsIcon = L.divIcon({
                                html: `
                                    <div style="position: relative; width: 24px; height: 24px;">
                                        <div style="position: absolute; width: 36px; height: 36px; top: -6px; left: -6px; background: rgba(37, 99, 235, 0.35); border-radius: 50%; animation: pulsePing 1.8s infinite;"></div>
                                        <div style="width: 24px; height: 24px; background: #2563eb; border: 3px solid #ffffff; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.3);"></div>
                                    </div>
                                `,
                                className: 'custom-gps-user-icon',
                                iconSize: [24, 24],
                                iconAnchor: [12, 12]
                            });

                            this.userGpsMarker = L.marker([lat, lng], { icon: userGpsIcon }).addTo(this.map);
                            this.userGpsMarker.bindPopup('<strong style="color: #2563eb;">📍 Vị trí hiện tại của bạn (GPS)</strong>').openPopup();

                            // Add accuracy circle
                            this.userGpsCircle = L.circle([lat, lng], {
                                radius: accuracy || 50,
                                color: '#2563eb',
                                fillColor: '#3b82f6',
                                fillOpacity: 0.15,
                                weight: 1.5
                            }).addTo(this.map);
                        }
                    },
                    (error) => {
                        this.gpsLoading = false;
                        alert('Không thể định vị được vị trí GPS của bạn. Vui lòng cho phép quyền vị trí trên trình duyệt.');
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            },

            renderRoutes() {
                this.routes.forEach(r => {
                    // Outer Polyline Glow
                    const glowLine = L.polyline(r.pathCoords, {
                        color: r.color,
                        weight: 12,
                        opacity: 0.25,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(this.map);
                    this.glowPolylinesMap[r.id] = glowLine;

                    // Inner Animated Polyline
                    const mainLine = L.polyline(r.pathCoords, {
                        color: r.color,
                        weight: 5,
                        opacity: 0.95,
                        lineCap: 'round',
                        lineJoin: 'round',
                        className: r.animClass
                    }).addTo(this.map);
                    this.polylinesMap[r.id] = mainLine;

                    // Fetch OSRM Real-world Street Routing Geometry
                    if (r.pathCoords && r.pathCoords.length >= 2) {
                        const start = r.pathCoords[0];
                        const end = r.pathCoords[r.pathCoords.length - 1];
                        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full&geometries=geojson`;

                        fetch(osrmUrl)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.routes && data.routes.length > 0 && data.routes[0].geometry) {
                                    const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                                    if (coords.length > 0) {
                                        glowLine.setLatLngs(coords);
                                        mainLine.setLatLngs(coords);

                                        // Re-snap markers on this route directly onto OSRM street curves
                                        const routeVillageMap = {
                                            'route-phu-loc': 'phu-loc',
                                            'route-ql3': 'dong-anh-cum-3',
                                            'route-co-van': 'duc-noi',
                                            'route-viet-hung': 'viet-hung'
                                        };

                                        const targetVillage = routeVillageMap[r.id];
                                        const routeLocs = this.locations.filter(loc => loc.village === targetVillage);

                                        if (routeLocs.length > 0) {
                                            routeLocs.forEach((loc, idx) => {
                                                const t = routeLocs.length > 1 ? (idx / (routeLocs.length - 1)) : 0.5;
                                                const totalSegs = coords.length - 1;
                                                const scaledT = t * totalSegs;
                                                const segIdx = Math.floor(scaledT);
                                                const segFrac = scaledT - segIdx;
                                                const p1 = coords[Math.min(segIdx, totalSegs)];
                                                const p2 = coords[Math.min(segIdx + 1, totalSegs)];
                                                const newLat = p1[0] + (p2[0] - p1[0]) * segFrac;
                                                const newLng = p1[1] + (p2[1] - p1[1]) * segFrac;

                                                loc.lat = newLat;
                                                loc.lng = newLng;

                                                const m = this.markersMap[loc.id];
                                                if (m) {
                                                    m.setLatLng([newLat, newLng]);
                                                }
                                            });
                                        }
                                    }
                                }
                            })
                            .catch(() => {
                                // Keep accurate street waypoints fallback
                            });
                    }
                });
            },

            renderMarkers() {
                this.locations.forEach(loc => {
                    const villageColor = this.getVillageColor(loc.village);
                    const iconEmoji = this.getCategoryEmoji(loc.type);

                    // Compact Circular Pin Badge (38x38px)
                    const htmlContent = `
                        <div class="compact-leaflet-pin" id="marker-pin-${loc.id}">
                            <div class="pin-pulse-ring" style="background: ${villageColor}35;"></div>
                            <div class="pin-circle-body" style="border-color: ${villageColor};">
                                <span>${iconEmoji}</span>
                            </div>
                            <div class="pin-rating-tag">★ ${loc.rating}</div>
                        </div>
                    `;

                    const customIcon = L.divIcon({
                        html: htmlContent,
                        className: 'custom-compact-icon',
                        iconSize: [40, 40],
                        iconAnchor: [20, 20],
                        popupAnchor: [0, -22]
                    });

                    const marker = L.marker([loc.lat, loc.lng], { icon: customIcon });

                    // Popup Tooltip Card on Marker Hover / Click
                    const popupHtml = `
                        <div style="font-family: 'Be Vietnam Pro', sans-serif; padding: 4px; width: 220px; text-align: left;">
                            <div style="position: relative; height: 95px; border-radius: 12px; overflow: hidden; margin-bottom: 8px; background: #e2e8f0;">
                                <img src="${loc.image}" style="width: 100%; height: 100%; object-fit: cover;">
                                <span style="position: absolute; top: 4px; left: 4px; ${loc.open ? 'background: #10b981;' : 'background: #64748b;'} color: #ffffff; padding: 2px 6px; border-radius: 6px; font-size: 0.6rem; font-weight: 800;">
                                    ${loc.open ? '● Mở' : '● Đóng'}
                                </span>
                            </div>
                            <div style="font-size: 0.88rem; font-weight: 900; color: #0f172a; margin-bottom: 2px; line-height: 1.3;">${loc.name}</div>
                            <div style="font-size: 0.72rem; color: #64748b; margin-bottom: 8px;">📍 ${loc.address}</div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.7rem; font-weight: 800; color: ${villageColor}; background: ${villageColor}15; padding: 2px 8px; border-radius: 6px;">${loc.villageName}</span>
                                <span style="font-size: 0.75rem; font-weight: 900; color: #b45309; background: #fffbeb; padding: 2px 6px; border-radius: 6px; border: 1px solid #fef3c7;">★ ${loc.rating}</span>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupHtml);

                    marker.on('click', () => {
                        this.selectLocation(loc);
                    });

                    this.markersMap[loc.id] = marker;

                    if (this.clusterGroup) {
                        this.clusterGroup.addLayer(marker);
                    } else {
                        marker.addTo(this.map);
                    }
                });
            },

            selectLocation(loc) {
                if (!loc) return;
                this.selectedLoc = loc;
                this.activeVillage = loc.village;

                if (this.map) {
                    this.map.flyTo([loc.lat, loc.lng], 16, {
                        duration: 1.2,
                        easeLinearity: 0.25
                    });

                    const m = this.markersMap[loc.id];
                    if (m) {
                        setTimeout(() => {
                            m.openPopup();
                        }, 400);
                    }
                }
            },

            setVillage(vId) {
                this.activeVillage = (this.activeVillage === vId ? 'all' : vId);
                this.updateMapState();

                if (this.activeVillage !== 'all') {
                    const villageToRouteKey = {
                        'phu-loc': 'route-phu-loc',
                        'dong-anh-cum-3': 'route-ql3',
                        'duc-noi': 'route-co-van',
                        'viet-hung': 'route-viet-hung'
                    };
                    const rKey = villageToRouteKey[this.activeVillage];
                    const line = this.polylinesMap[rKey];
                    if (line && this.map) {
                        try {
                            this.map.fitBounds(line.getBounds(), { padding: [60, 60], maxZoom: 16, animate: true, duration: 1.2 });
                        } catch (e) {}
                    }
                } else {
                    if (this.map) {
                        this.map.flyTo([21.1375, 105.8480], 13, { duration: 1 });
                    }
                }
            },

            setRoute(rId) {
                this.activeRouteId = (this.activeRouteId === rId ? 'all' : rId);
                this.updateMapState();
            },

            resetFilters() {
                this.search = '';
                this.activeCategory = 'all';
                this.activeVillage = 'all';
                this.activeRouteId = 'all';
                this.updateMapState();

                if (this.map) {
                    this.map.flyTo([21.1375, 105.8480], 13, { duration: 1 });
                }
            },

            updateMapState() {
                // Update Route Visibility - HIDE non-selected routes completely
                this.routes.forEach(r => {
                    const glow = this.glowPolylinesMap[r.id];
                    const line = this.polylinesMap[r.id];
                    if (!glow || !line) return;

                    let show = (this.activeRouteId === 'all' || this.activeRouteId === r.id);
                    if (this.activeVillage !== 'all' && !r.villages.includes(this.activeVillage)) {
                        show = false;
                    }

                    if (show) {
                        glow.setStyle({ opacity: 0.3 });
                        line.setStyle({ opacity: 0.95 });
                        if (!this.map.hasLayer(glow)) this.map.addLayer(glow);
                        if (!this.map.hasLayer(line)) this.map.addLayer(line);
                    } else {
                        glow.setStyle({ opacity: 0 });
                        line.setStyle({ opacity: 0 });
                        if (this.map.hasLayer(glow)) this.map.removeLayer(glow);
                        if (this.map.hasLayer(line)) this.map.removeLayer(line);
                    }
                });

                // Filter Cluster Markers - HIDE non-matching markers completely
                this.locations.forEach(loc => {
                    const m = this.markersMap[loc.id];
                    if (!m) return;

                    const matchSearch = !this.search || loc.name.toLowerCase().includes(this.search.toLowerCase()) || (loc.owner && loc.owner.toLowerCase().includes(this.search.toLowerCase())) || loc.menu.some(t => t.toLowerCase().includes(this.search.toLowerCase())) || loc.villageName.toLowerCase().includes(this.search.toLowerCase());
                    const matchCat = this.activeCategory === 'all' || loc.type === this.activeCategory;
                    const matchVillage = this.activeVillage === 'all' || loc.village === this.activeVillage;
                    
                    let matchRoute = true;
                    if (this.activeRouteId !== 'all') {
                        const r = this.routes.find(item => item.id === this.activeRouteId);
                        if (r) matchRoute = r.villages.includes(loc.village);
                    }

                    if (this.clusterGroup) {
                        if (matchSearch && matchCat && matchVillage && matchRoute) {
                            if (!this.clusterGroup.hasLayer(m)) {
                                this.clusterGroup.addLayer(m);
                            }
                        } else {
                            if (this.clusterGroup.hasLayer(m)) {
                                this.clusterGroup.removeLayer(m);
                            }
                        }
                    } else {
                        if (matchSearch && matchCat && matchVillage && matchRoute) {
                            if (!this.map.hasLayer(m)) this.map.addLayer(m);
                        } else {
                            if (this.map.hasLayer(m)) this.map.removeLayer(m);
                        }
                    }
                });
            },

            updateMapMarkers() {
                this.updateMapState();
            },

            filteredLocations() {
                const q = this.search.toLowerCase().trim();
                return this.locations.filter(loc => {
                    const matchSearch = !q || loc.name.toLowerCase().includes(q) || (loc.owner && loc.owner.toLowerCase().includes(q)) || loc.menu.some(t => t.toLowerCase().includes(q)) || loc.villageName.toLowerCase().includes(q);
                    const matchCat = this.activeCategory === 'all' || loc.type === this.activeCategory;
                    const matchVillage = this.activeVillage === 'all' || loc.village === this.activeVillage;
                    
                    let matchRoute = true;
                    if (this.activeRouteId !== 'all') {
                        const r = this.routes.find(item => item.id === this.activeRouteId);
                        if (r) matchRoute = r.villages.includes(loc.village);
                    }

                    return matchSearch && matchCat && matchVillage && matchRoute;
                });
            },

            getVillageColor(villageId) {
                const v = this.villages.find(item => item.id === villageId);
                return v ? v.color : '#059669';
            },

            getCategoryLabel(type) {
                const catMap = {
                    'quan-an': 'Quán ăn & Phở',
                    'nha-hang': 'Nhà hàng',
                    'tap-hoa': 'Tạp hóa',
                    'thoi-trang': 'Thời trang',
                    'y-te': 'Y tế & Spa',
                    'dich-vu': 'Dịch vụ'
                };
                return catMap[type] || 'Hộ kinh doanh';
            },

            getCategoryEmoji(type) {
                const emojiMap = {
                    'quan-an': '🍜',
                    'nha-hang': '🍽️',
                    'tap-hoa': '🛒',
                    'thoi-trang': '👕',
                    'y-te': '💊',
                    'dich-vu': '🔧'
                };
                return emojiMap[type] || '🏪';
            },

            getVietQrUrl(bankName, bankAccount, ownerName) {
                if (!bankAccount) return '';
                let cleanAcc = String(bankAccount).trim();
                if (cleanAcc.includes('-')) {
                    cleanAcc = cleanAcc.split('-')[0].trim();
                }
                cleanAcc = cleanAcc.replace(/[^0-9a-zA-Z]/g, '');
                if (!cleanAcc) return '';

                let bankCode = 'ICB';
                const b = (bankName || '').toUpperCase();
                if (b.includes('BIDV')) bankCode = 'BIDV';
                else if (b.includes('MB')) bankCode = 'MB';
                else if (b.includes('TECHCOM')) bankCode = 'TCB';
                else if (b.includes('VIETCOM')) bankCode = 'VCB';
                else if (b.includes('VIETIN')) bankCode = 'CTG';
                else if (b.includes('ACB')) bankCode = 'ACB';
                else if (b.includes('VPBANK') || b.includes('VP')) bankCode = 'VPB';
                else if (b.includes('SACOMBANK') || b.includes('SACOM')) bankCode = 'STB';
                else if (b.includes('PG')) bankCode = 'PGB';
                else if (b.includes('MOMO')) bankCode = 'MOMO';

                const accountNameParam = encodeURIComponent(ownerName || 'HKD DONG ANH');
                return `https://img.vietqr.io/image/${bankCode}-${cleanAcc}-compact2.jpg?accountName=${accountNameParam}`;
            }
        };
    }
</script>
@endsection
