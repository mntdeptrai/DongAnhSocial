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
            <span x-text="`📋 Danh sách (${filteredLocations().length} hộ)`">📋 Danh sách</span>
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
                        <p style="font-size: 0.72rem; color: #64748b; margin: 0;">Bản đồ số <span x-text="locations.length">72</span> hộ kinh doanh & tuyến đường thông minh</p>
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

                <!-- Dual Dropdown Selectors Section -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
                    <!-- Dropdown 1: Ngành hàng / Danh mục -->
                    <div>
                        <label style="font-size: 0.68rem; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 4px;">Ngành hàng</label>
                        <select x-model="activeCategory" @change="updateMapMarkers()" 
                                style="width: 100%; padding: 8px 10px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; font-size: 0.78rem; font-weight: 700; color: #0f172a; outline: none; cursor: pointer; transition: all 0.2s;"
                                onfocus="this.style.borderColor='#10b981'; this.style.background='#ffffff';"
                                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <option value="all">🗺️ Tất cả ngành hàng</option>
                            <option value="quan-an">🍜 Quán ăn & Phở</option>
                            <option value="nha-hang">🍽️ Nhà hàng & Hải sản</option>
                            <option value="tap-hoa">🛒 Tạp hóa & Bán lẻ</option>
                            <option value="thoi-trang">👕 Thời trang & Đồ dùng</option>
                            <option value="y-te">💊 Y tế & Thẩm mỹ</option>
                            <option value="dich-vu">🔧 Dịch vụ & Kỹ thuật</option>
                        </select>
                    </div>

                    <!-- Dropdown 2: Tuyến đường 4.0 -->
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                            <label style="font-size: 0.68rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Tuyến đường</label>
                            <button type="button" @click="resetFilters()" style="font-size: 0.65rem; color: #059669; background: none; border: none; font-weight: 800; cursor: pointer; padding: 0;">Đặt lại</button>
                        </div>
                        <select x-model="activeVillage" @change="setVillage($event.target.value)" 
                                style="width: 100%; padding: 8px 10px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; font-size: 0.78rem; font-weight: 700; color: #0f172a; outline: none; cursor: pointer; transition: all 0.2s;"
                                onfocus="this.style.borderColor='#10b981'; this.style.background='#ffffff';"
                                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <option value="all">🛣️ Tất cả tuyến đường</option>
                            <option value="phu-loc">📍 Đường Phúc Lộc</option>
                            <option value="dong-anh-cum-3">📍 Quốc Lộ 3 (Cụm 3)</option>
                            <option value="duc-noi">📍 Đường Cổ Vân</option>
                            <option value="viet-hung">📍 Tuyến Việt Hùng</option>
                            <option value="cao-lo">📍 Đường Cao Lỗ (Hùng Sơn)</option>
                            <option value="xuan-canh">📍 Tuyến 4.0 Xuân Canh</option>
                            <option value="dan-di">📍 Tuyến 4.0 Đản Dị</option>
                            <option value="mai-lam">📍 Tuyến 4.0 Dốc Vân (Mai Lâm)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Summary inside Sidebar -->
            <div style="padding: 8px 16px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                <div style="background: #ffffff; padding: 6px 4px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 0.92rem; font-weight: 900; color: #059669; display: block; font-family: 'Be Vietnam Pro', sans-serif;" x-text="villages.length">5</span>
                    <span style="font-size: 0.68rem; color: #64748b; font-weight: 700;">🛣️ Tuyến 4.0</span>
                </div>
                <div style="background: #ffffff; padding: 6px 4px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 0.92rem; font-weight: 900; color: #2563eb; display: block; font-family: 'Be Vietnam Pro', sans-serif;" x-text="locations.length">72</span>
                    <span style="font-size: 0.68rem; color: #64748b; font-weight: 700;">🏪 Hộ kinh doanh</span>
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
                { id: 'viet-hung', name: 'Việt Hùng', routeName: 'Tuyến Việt Hùng', color: '#3B82F6' },
                { id: 'cao-lo', name: 'Đường Cao Lỗ', routeName: 'Đường Cao Lỗ (Hùng Sơn)', color: '#EC4899' },
                { id: 'xuan-canh', name: 'Xuân Canh', routeName: 'Tuyến Đường 4.0 Xuân Canh', color: '#8B5CF6' },
                { id: 'dan-di', name: 'Đản Dị', routeName: 'Tuyến Đường 4.0 Đản Dị', color: '#06B6D4' },
                { id: 'mai-lam', name: 'Dốc Vân (Mai Lâm)', routeName: 'Tuyến Đường 4.0 Dốc Vân', color: '#10B981' }
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
                        [21.1335, 105.8460],
                        [21.1365, 105.8462],
                        [21.1397, 105.8465]
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
                        [21.1460, 105.8430],
                        [21.1396, 105.8435],
                        [21.1330, 105.8440]
                    ]
                },
                {
                    id: 'route-co-van',
                    name: 'Tuyến 3: Đường Cổ Vân (Dục Nội)',
                    length: '0.8km',
                    color: '#EAB308',
                    villages: ['duc-noi'],
                    animClass: 'route-path-animated-3',
                    pathCoords: [
                        [21.1424, 105.8742],
                        [21.1418, 105.8758],
                        [21.1413, 105.8770],
                        [21.1407, 105.8782],
                        [21.1400, 105.8793]
                    ]
                },
                {
                    id: 'route-viet-hung',
                    name: 'Tuyến 4: Tuyến Việt Hùng (Việt Hùng)',
                    length: '1.2km',
                    color: '#3B82F6',
                    villages: ['viet-hung'],
                    animClass: 'route-path-animated-1',
                    pathCoords: [
                        [21.1435, 105.8790],
                        [21.1390, 105.8790],
                        [21.1345, 105.8790]
                    ]
                },
                {
                    id: 'route-cao-lo',
                    name: 'Tuyến 5: Đường Cao Lỗ (Ngã tư QL3 - Hùng Sơn)',
                    length: '1.1km',
                    color: '#EC4899',
                    villages: ['cao-lo'],
                    animClass: 'route-path-animated-2',
                    pathCoords: [
                        [21.1408, 105.8435],
                        [21.14085, 105.8460],
                        [21.1409, 105.8485],
                        [21.14095, 105.8510],
                        [21.1410, 105.8530]
                    ]
                },
                {
                    id: 'route-xuan-canh',
                    name: 'Tuyến 6: Tuyến Đường 4.0 Xuân Canh',
                    length: '1.5km',
                    color: '#8B5CF6',
                    villages: ['xuan-canh'],
                    animClass: 'route-path-animated-3',
                    pathCoords: [
                        [21.0850, 105.8600],
                        [21.0862, 105.8615],
                        [21.0871, 105.8628],
                        [21.0880, 105.8640]
                    ]
                },
                {
                    id: 'route-dan-di',
                    name: 'Tuyến 7: Tuyến Đường 4.0 Đản Dị (Khối 4)',
                    length: '1.2km',
                    color: '#06B6D4',
                    villages: ['dan-di'],
                    animClass: 'route-path-animated-1',
                    pathCoords: [
                        [21.1440, 105.8500],
                        [21.1455, 105.8520],
                        [21.1470, 105.8540]
                    ]
                },
                {
                    id: 'route-doc-van',
                    name: 'Tuyến 8: Tuyến Đường 4.0 Dốc Vân (Mai Lâm)',
                    length: '2.1km',
                    color: '#10B981',
                    villages: ['mai-lam'],
                    animClass: 'route-path-animated-2',
                    pathCoords: [
                        [21.0720, 105.8750],
                        [21.0735, 105.8770],
                        [21.0750, 105.8790]
                    ]
                }
            ],

            locations: @json($dbLocations ?? []).length > 0 ? @json($dbLocations) : [],

            initMap() {
                this.$nextTick(() => {
                    if (typeof L === 'undefined') return;

                    // Automatically calculate exact lat/lng for every store directly on top of its assigned route line
                    const routeMap = {};
                    this.routes.forEach(r => { routeMap[r.id] = r.pathCoords; });

                    const locsByVillage = { 'phu-loc': [], 'dong-anh-cum-3': [], 'duc-noi': [], 'viet-hung': [], 'cao-lo': [], 'xuan-canh': [], 'dan-di': [], 'mai-lam': [] };
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
                        'viet-hung': 'route-viet-hung',
                        'cao-lo': 'route-cao-lo',
                        'xuan-canh': 'route-xuan-canh',
                        'dan-di': 'route-dan-di',
                        'mai-lam': 'route-doc-van'
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

                    // Add Official High-Definition Google Maps Tiles
                    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Maps'
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

                    // Helper to snap store markers smoothly along the polyline
                    const snapRouteStores = (routeId, lineCoords) => {
                        if (!lineCoords || lineCoords.length < 2) return;

                        const routeVillageMap = {
                            'route-phu-loc': 'phu-loc',
                            'route-ql3': 'dong-anh-cum-3',
                            'route-co-van': 'duc-noi',
                            'route-viet-hung': 'viet-hung',
                            'route-cao-lo': 'cao-lo',
                            'route-xuan-canh': 'xuan-canh',
                            'route-dan-di': 'dan-di',
                            'route-doc-van': 'mai-lam'
                        };

                        const targetVillage = routeVillageMap[routeId];
                        if (!targetVillage) return;

                        const routeLocs = this.locations.filter(loc => loc.village === targetVillage);
                        if (routeLocs.length === 0) return;

                        const dists = [0];
                        for (let i = 1; i < lineCoords.length; i++) {
                            const dLat = lineCoords[i][0] - lineCoords[i-1][0];
                            const dLng = lineCoords[i][1] - lineCoords[i-1][1];
                            dists.push(dists[i-1] + Math.sqrt(dLat * dLat + dLng * dLng));
                        }
                        const totalDist = dists[dists.length - 1];
                        if (totalDist <= 0) return;

                        const n = routeLocs.length;
                        routeLocs.forEach((loc, idx) => {
                            const frac = n > 1 ? (0.04 + (idx / (n - 1)) * 0.92) : 0.50;
                            const targetD = frac * totalDist;

                            let seg = 0;
                            while (seg < dists.length - 2 && dists[seg + 1] < targetD) {
                                seg++;
                            }

                            const segStartD = dists[seg];
                            const segEndD = dists[seg + 1];
                            const segLen = segEndD - segStartD;
                            const segT = segLen > 0 ? (targetD - segStartD) / segLen : 0;

                            const p1 = lineCoords[seg];
                            const p2 = lineCoords[seg + 1];

                            const snappedLat = p1[0] + (p2[0] - p1[0]) * segT;
                            const snappedLng = p1[1] + (p2[1] - p1[1]) * segT;

                            loc.lat = snappedLat;
                            loc.lng = snappedLng;

                            const m = this.markersMap[loc.id];
                            if (m) {
                                m.setLatLng([snappedLat, snappedLng]);
                            }
                        });
                    };

                    // Render initial polyline & fetch Google-Maps-style street routing curves
                    if (r.pathCoords && r.pathCoords.length >= 2) {
                        glowLine.setLatLngs(r.pathCoords);
                        mainLine.setLatLngs(r.pathCoords);
                        snapRouteStores(r.id, r.pathCoords);

                        // Fetch detailed street geometry like Google Maps directions
                        const waypointsStr = r.pathCoords.map(c => `${c[1]},${c[0]}`).join(';');
                        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${waypointsStr}?overview=full&geometries=geojson`;

                        fetch(osrmUrl)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.routes && data.routes.length > 0 && data.routes[0].geometry) {
                                    const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                                    if (coords && coords.length > 0) {
                                        glowLine.setLatLngs(coords);
                                        mainLine.setLatLngs(coords);
                                        snapRouteStores(r.id, coords);
                                    }
                                }
                            })
                            .catch(() => {
                                // Keep fallback
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
                this.activeVillage = vId;
                this.updateMapState();

                if (this.activeVillage !== 'all') {
                    const villageToRouteKey = {
                        'phu-loc': 'route-phu-loc',
                        'dong-anh-cum-3': 'route-ql3',
                        'duc-noi': 'route-co-van',
                        'viet-hung': 'route-viet-hung',
                        'cao-lo': 'route-cao-lo',
                        'xuan-canh': 'route-xuan-canh',
                        'dan-di': 'route-dan-di',
                        'mai-lam': 'route-doc-van'
                    };
                    const rKey = villageToRouteKey[this.activeVillage];
                    const line = this.polylinesMap[rKey];
                    if (line && this.map) {
                        try {
                            this.map.fitBounds(line.getBounds(), { padding: [80, 80], maxZoom: 16, animate: true, duration: 1.2 });
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
                // Update Route Visibility - Highlight selected route and dim/hide others
                this.routes.forEach(r => {
                    const glow = this.glowPolylinesMap[r.id];
                    const line = this.polylinesMap[r.id];
                    if (!glow || !line) return;

                    let show = (this.activeRouteId === 'all' || this.activeRouteId === r.id);
                    if (this.activeVillage !== 'all' && !r.villages.includes(this.activeVillage)) {
                        show = false;
                    }

                    if (show) {
                        const isFocused = (this.activeVillage !== 'all' && r.villages.includes(this.activeVillage)) || (this.activeRouteId === r.id);
                        if (isFocused) {
                            glow.setStyle({ opacity: 0.5, weight: 18 });
                            line.setStyle({ opacity: 1.0, weight: 8 });
                            glow.bringToFront();
                            line.bringToFront();
                        } else {
                            glow.setStyle({ opacity: 0.25, weight: 12 });
                            line.setStyle({ opacity: 0.7, weight: 5 });
                        }
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
