@extends('layouts.app')

@section('title', 'Khám phá Đông Anh - Bản đồ Địa điểm, Trường học, Dịch vụ Xã Đông Anh')

@section('content')
<style>
    /* Ẩn navbar và footer mặc định để layout full screen map giống Google Maps */
    body { overflow: hidden; }
    header.navbar { display: none !important; }
    footer { display: none !important; }
    main { padding: 0 !important; margin: 0 !important; }
    
    .map-app-container {
        display: flex;
        height: 100vh;
        width: 100vw;
        margin: 0;
        padding: 0;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999;
        background: #fff;
    }
    
    /* Left Sidebar */
    .map-sidebar {
        width: 380px;
        height: 100%;
        background: #fff;
        box-shadow: 2px 0 15px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        z-index: 10000;
        transition: transform 0.3s ease;
        overflow-y: hidden;
    }
    
    .map-sidebar-header {
        background: #db4437; /* Đỏ Google My Maps */
        color: white;
        padding: 16px 20px;
        position: relative;
    }
    .map-sidebar-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0 0 4px 0;
        font-family: 'Inter', sans-serif;
    }
    .map-sidebar-subtitle {
        font-size: 0.85rem;
        opacity: 0.9;
        font-family: 'Inter', sans-serif;
    }
    .btn-close-map {
        position: absolute;
        top: 18px;
        right: 20px;
        background: transparent;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0.8;
        transition: 0.2s;
        text-decoration: none;
    }
    .btn-close-map:hover {
        opacity: 1;
    }
    
    .map-sidebar-search {
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
        background: #fff;
    }
    .search-input-wrapper {
        position: relative;
    }
    .search-input-wrapper input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        border: 1px solid #dadce0;
        border-radius: 24px;
        font-size: 0.95rem;
        outline: none;
        transition: 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .search-input-wrapper input:focus {
        border-color: #1a73e8;
        box-shadow: 0 1px 4px rgba(26,115,232,0.2);
    }
    .search-input-wrapper svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        fill: #5f6368;
    }
    
    .map-sidebar-content {
        flex: 1;
        overflow-y: auto;
    }
    
    /* Custom Scrollbar for Sidebar */
    .map-sidebar-content::-webkit-scrollbar {
        width: 6px;
    }
    .map-sidebar-content::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .map-sidebar-content::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 10px;
    }
    .map-sidebar-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }

    /* Accordion Category Group */
    .category-group {
        border-bottom: 1px solid #e8eaed;
    }
    .category-header {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        cursor: pointer;
        background: #fff;
        font-weight: 600;
        font-size: 0.95rem;
        color: #3c4043;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    .category-header:hover {
        background: #f8f9fa;
    }
    .category-header.active {
        background: #f4f8ff;
        border-left: 4px solid #1a73e8;
        color: #1a73e8;
    }
    .category-icon-wrapper {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        color: white;
        font-size: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .category-chevron {
        transition: transform 0.3s ease;
        fill: #70757a;
    }
    .category-header.active .category-chevron {
        transform: rotate(180deg);
        fill: #1a73e8;
    }
    
    .category-items {
        display: none;
        background: #fff;
        max-height: 55vh;
        overflow-y: auto;
    }
    
    /* Scrollbar nhỏ gọn cho danh sách bên trong */
    .category-items::-webkit-scrollbar {
        width: 4px;
    }
    .category-items::-webkit-scrollbar-track {
        background: transparent; 
    }
    .category-items::-webkit-scrollbar-thumb {
        background: #dadce0; 
        border-radius: 4px;
    }
    .category-items::-webkit-scrollbar-thumb:hover {
        background: #bdc1c6; 
    }
    
    .map-list-item {
        padding: 12px 16px 12px 30px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        cursor: pointer;
        transition: background 0.2s;
        border-bottom: 1px solid #f1f3f4;
    }
    .map-list-item:hover {
        background: #f8f9fa;
    }
    .map-list-item:last-child {
        border-bottom: none;
    }
    .map-list-item-img {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        object-fit: cover;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }
    .map-list-item-info {
        flex: 1;
        min-width: 0;
    }
    .map-list-item-name {
        font-size: 0.95rem;
        color: #202124;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .map-list-item-addr {
        font-size: 0.8rem;
        color: #70757a;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    /* Right Map Area */
    .map-area {
        flex: 1;
        position: relative;
        height: 100%;
    }
    
    #searchMap {
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    /* Premium Popup like Google Maps */
    .leaflet-popup-content-wrapper {
        padding: 0 !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15) !important;
        border: none !important;
    }
    .leaflet-popup-content {
        margin: 0 !important;
        width: 300px !important;
    }
    .gm-popup-cover {
        width: 100%;
        height: 160px;
        object-fit: cover;
    }
    .gm-popup-body {
        padding: 16px;
        background: #fff;
    }
    .gm-popup-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #202124;
        margin-bottom: 4px;
        font-family: 'Inter', sans-serif;
    }
    .gm-popup-rating {
        font-size: 0.85rem;
        color: #70757a;
        margin-bottom: 12px;
    }
    .gm-popup-rating span {
        color: #fbbc04;
    }
    .gm-popup-address {
        font-size: 0.85rem;
        color: #5f6368;
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        line-height: 1.4;
    }
    .gm-popup-actions {
        display: flex;
        gap: 10px;
    }
    .gm-btn-direction {
        flex: 1;
        background: #1a73e8;
        color: #fff !important;
        border: none;
        padding: 10px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: 0.2s;
    }
    .gm-btn-direction:hover {
        background: #1557b0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .gm-btn-detail {
        flex: 1;
        background: #fff;
        color: #1a73e8 !important;
        border: 1px solid #dadce0;
        padding: 10px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }
    .gm-btn-detail:hover {
        background: #f8f9fa;
        border-color: #1a73e8;
    }

    /* Loading spinner */
    .sidebar-loading, .sidebar-load-more {
        text-align: center;
        padding: 20px;
        color: #70757a;
        font-size: 0.9rem;
    }
    .sidebar-load-more {
        cursor: pointer;
        color: #1a73e8;
        font-weight: 600;
    }
    .sidebar-load-more:hover {
        text-decoration: underline;
    }
    
    @media(max-width: 768px) {
        .map-app-container {
            flex-direction: column-reverse;
        }
        .map-sidebar {
            width: 100%;
            height: 45vh; /* Sidebar 45% bottom */
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
            z-index: 10001;
        }
        .map-sidebar-header {
            padding: 12px 16px;
        }
        .map-area {
            height: 55vh;
        }
    }
</style>

<div class="map-app-container">
    <!-- Bảng điều khiển bên trái -->
    <aside class="map-sidebar">
        <div class="map-sidebar-header">
            <a href="/" class="btn-close-map">✕</a>
            <h1 class="map-sidebar-title">Bản đồ số Đông Anh</h1>
            <div class="map-sidebar-subtitle">100+ Di tích, Trường học, Y tế & Đặc sản</div>
        </div>
        
        <div class="map-sidebar-search">
            <div class="search-input-wrapper">
                <svg width="20" height="20" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="text" id="searchInput" placeholder="Tìm kiếm địa danh, trường học..." value="{{ $keyword ?? '' }}">
            </div>
        </div>
        
        <div class="map-sidebar-content" id="sidebarContent">
            <div class="sidebar-loading" id="initialLoading">⏳ Đang tải danh mục...</div>
        </div>
    </aside>

    <!-- Bản đồ chính -->
    <div class="map-area">
        <div id="searchMap"></div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    'use strict';

    // ==================== STATE ====================
    let searchMap;
    let clusterGroup;           // MarkerCluster group
    let markers = {};           // slug -> L.marker
    let categories = [];        // Category list from API
    let activeCategory = null;  // Currently open category slug
    let sidebarPage = 1;        // Current sidebar pagination page
    let sidebarLoading = false;
    let sidebarHasMore = true;
    let searchDebounceTimer = null;

    // ==================== CONFIG ====================
    const CATEGORY_COLORS = {
        'hanh-trinh-di-san': '#8B4513',
        'smart-education-map': '#1a73e8',
        'wellness-care': '#34a853',
        'stay-in-dong-anh': '#9334e6',
        'dong-anh-market': '#f29900',
        'dong-anh-food-map': '#ea4335',
        'discover-dong-anh-community-culture-hub': '#e81e63',
        'co-so-kinh-doanh': '#0284c7',
        'traditional-market': '#f29900'
    };
    const CATEGORY_ICONS = {
        'hanh-trinh-di-san': '⛩️',
        'smart-education-map': '🎓',
        'wellness-care': '🏥',
        'stay-in-dong-anh': '🏨',
        'dong-anh-market': '🛍️',
        'dong-anh-food-map': '🍜',
        'discover-dong-anh-community-culture-hub': '🏛️',
        'co-so-kinh-doanh': '🏪',
        'traditional-market': '🛒'
    };
    const CATEGORY_LABELS = {
        'hanh-trinh-di-san': 'DI TÍCH QUỐC GIA & DI SẢN',
        'smart-education-map': 'HỆ THỐNG TRƯỜNG HỌC',
        'wellness-care': 'BỆNH VIỆN & CƠ SỞ Y TẾ',
        'stay-in-dong-anh': 'KHÁCH SẠN & LƯU TRÚ',
        'dong-anh-market': 'ĐẶC SẢN OCOP',
        'dong-anh-food-map': 'ĐỊA ĐIỂM ẨM THỰC',
        'discover-dong-anh-community-culture-hub': 'NHÀ VĂN HÓA & THỂ THAO',
        'co-so-kinh-doanh': 'CƠ SỞ KINH DOANH, DOANH NGHIỆP',
        'traditional-market': 'CHỢ TRUYỀN THỐNG'
    };

    // Preferred display order (nhỏ trước, cơ sở kinh doanh cuối)
    const CATEGORY_ORDER = [
        'dong-anh-food-map', 'discover-dong-anh-community-culture-hub',
        'wellness-care', 'dong-anh-market', 'traditional-market',
        'smart-education-map', 'stay-in-dong-anh', 'hanh-trinh-di-san',
        'co-so-kinh-doanh'
    ];

    function getColor(slug) { return CATEGORY_COLORS[slug] || '#70757a'; }
    function getIcon(slug)  { return CATEGORY_ICONS[slug] || '📍'; }
    function getLabel(slug, fallback) { return CATEGORY_LABELS[slug] || (fallback || '').toUpperCase(); }

    // ==================== INIT ====================
    document.addEventListener("DOMContentLoaded", async function() {
        initMap();
        await loadCategories();
        setupSearch();
    });

    function initMap() {
        searchMap = L.map('searchMap', { zoomControl: false })
            .setView([21.1352, 105.8458], 12);
        L.control.zoom({ position: 'bottomright' }).addTo(searchMap);
        L.tileLayer('https://mt1.google.com/vt/lyrs=m&hl=vi&x={x}&y={y}&z={z}', {
            attribution: '&copy; Google Maps',
            maxZoom: 20
        }).addTo(searchMap);

        // Init MarkerCluster
        clusterGroup = L.markerClusterGroup({
            maxClusterRadius: 50,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            disableClusteringAtZoom: 16
        });
        searchMap.addLayer(clusterGroup);
    }

    // ==================== CATEGORIES ====================
    async function loadCategories() {
        try {
            const res = await fetch('/api/map/categories');
            categories = await res.json();

            // Sort by preferred order
            categories.sort((a, b) => {
                const ai = CATEGORY_ORDER.indexOf(a.slug);
                const bi = CATEGORY_ORDER.indexOf(b.slug);
                return (ai === -1 ? 999 : ai) - (bi === -1 ? 999 : bi);
            });

            renderCategorySidebar();

            // Auto-open first category
            if (categories.length > 0) {
                toggleCategory(categories[0].slug);
            }
        } catch (err) {
            document.getElementById('sidebarContent').innerHTML = 
                '<div class="sidebar-loading">❌ Không thể tải dữ liệu</div>';
        }
    }

    function renderCategorySidebar() {
        let html = '';
        categories.forEach(cat => {
            const color = getColor(cat.slug);
            const icon = getIcon(cat.slug);
            const label = getLabel(cat.slug, cat.name);

            html += `
                <div class="category-group" id="group-${cat.slug}">
                    <div class="category-header" id="header-${cat.slug}" onclick="window._mapToggleCategory('${cat.slug}')">
                        <div class="category-icon-wrapper" style="background-color: ${color}">
                            ${icon}
                        </div>
                        <div style="flex:1;">
                            ${label} <span style="color:inherit; font-weight:normal; font-size:0.85rem; opacity:0.8">(${cat.eateries_count})</span>
                        </div>
                        <svg class="category-chevron" width="20" height="20" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
                    </div>
                    <div class="category-items" id="cat-items-${cat.slug}"></div>
                </div>
            `;
        });
        document.getElementById('sidebarContent').innerHTML = html;
    }

    // ==================== TOGGLE CATEGORY (LAZY LOAD) ====================
    window._mapToggleCategory = function(slug) {
        const el = document.getElementById('cat-items-' + slug);
        const header = document.getElementById('header-' + slug);

        // If clicking the currently open category → collapse & show all markers
        if (slug === activeCategory) {
            el.style.display = 'none';
            header.classList.remove('active');
            activeCategory = null;
            // Show all loaded markers
            clusterGroup.clearLayers();
            for (let key in markers) {
                clusterGroup.addLayer(markers[key]);
            }
            if (Object.keys(markers).length > 0) {
                searchMap.fitBounds(clusterGroup.getBounds(), { padding: [60, 60] });
            }
            return;
        }

        // Close all categories
        document.querySelectorAll('.category-items').forEach(item => item.style.display = 'none');
        document.querySelectorAll('.category-header').forEach(h => h.classList.remove('active'));

        // Open clicked category
        el.style.display = 'block';
        header.classList.add('active');
        activeCategory = slug;
        sidebarPage = 1;
        sidebarHasMore = true;

        // Clear old markers for this category view
        clusterGroup.clearLayers();

        // Load markers + sidebar items for this category
        el.innerHTML = '<div class="sidebar-loading">⏳ Đang tải...</div>';
        loadCategoryData(slug, 1, true);
    };

    // ==================== LOAD DATA FOR CATEGORY ====================
    async function loadCategoryData(slug, page, isFirstLoad) {
        if (sidebarLoading) return;
        sidebarLoading = true;

        try {
            // Fetch markers + sidebar in parallel
            const [markersRes, sidebarRes] = await Promise.all([
                fetch(`/api/map/markers?category_slug=${slug}&page=${page}`),
                fetch(`/api/map/sidebar?category_slug=${slug}&page=${page}`)
            ]);

            const markersData = await markersRes.json();
            const sidebarData = await sidebarRes.json();

            // Add markers to map
            addMarkersToMap(markersData.data, slug);

            // Render sidebar items
            renderSidebarItems(slug, sidebarData.data, isFirstLoad);

            // Update pagination state
            sidebarHasMore = markersData.meta.page < markersData.meta.last_page;
            sidebarPage = page;

            // Add "Load more" button if needed
            if (sidebarHasMore) {
                appendLoadMoreButton(slug);
            }

            // Fit bounds to visible markers
            if (clusterGroup.getLayers().length > 0) {
                searchMap.fitBounds(clusterGroup.getBounds(), { padding: [60, 60] });
            }

            // If category has many pages, auto-load remaining markers in background (for map completeness)
            if (isFirstLoad && markersData.meta.last_page > 1) {
                loadRemainingMarkers(slug, 2, markersData.meta.last_page);
            }
        } catch (err) {
            const el = document.getElementById('cat-items-' + slug);
            if (el && isFirstLoad) {
                el.innerHTML = '<div class="sidebar-loading">❌ Lỗi tải dữ liệu</div>';
            }
        }

        sidebarLoading = false;
    }

    // Background-load remaining marker pages (for map dots only, no sidebar)
    async function loadRemainingMarkers(slug, fromPage, lastPage) {
        for (let p = fromPage; p <= lastPage; p++) {
            if (activeCategory !== slug) break; // User switched category
            try {
                const res = await fetch(`/api/map/markers?category_slug=${slug}&page=${p}`);
                const data = await res.json();
                addMarkersToMap(data.data, slug);
            } catch (_) {}
        }
    }

    // ==================== MARKERS ====================
    function addMarkersToMap(markerList, categorySlug) {
        const color = getColor(categorySlug);
        const catIcon = getIcon(categorySlug);

        markerList.forEach(m => {
            if (!m.lat || !m.lng || markers[m.slug]) return;

            const icon = L.divIcon({
                html: `<div style="background:${color};width:36px;height:36px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:1px 4px 8px rgba(0,0,0,0.3);cursor:pointer;">
                    <div style="transform:rotate(45deg);font-size:16px;">${catIcon}</div>
                </div>`,
                className: '',
                iconSize: [36, 36],
                iconAnchor: [18, 36]
            });

            const marker = L.marker([m.lat, m.lng], { icon });
            marker._eaterySlug = m.slug;
            marker._eateryName = m.name;
            marker._categorySlug = categorySlug;

            // Lazy popup: fetch detail on click
            marker.on('click', () => loadPopup(marker));

            markers[m.slug] = marker;
            clusterGroup.addLayer(marker);
        });
    }

    async function loadPopup(marker) {
        const slug = marker._eaterySlug;
        marker.bindPopup('<div style="padding:20px;text-align:center;color:#70757a;">Đang tải...</div>', { maxWidth: 300, minWidth: 300 }).openPopup();

        try {
            const res = await fetch(`/api/map/detail/${slug}`);
            const eat = await res.json();

            const imgUrl = eat.image_path || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=600&q=80';
            const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${eat.latitude},${eat.longitude}`;

            const popupContent = `
                <div class="gm-popup-wrapper">
                    <img src="${imgUrl}" class="gm-popup-cover" alt="${eat.name}" loading="lazy">
                    <div class="gm-popup-body">
                        <div class="gm-popup-title">${eat.name}</div>
                        <div class="gm-popup-rating"><span>⭐</span> ${parseFloat(eat.rating || 5.0).toFixed(1)} / 5.0</div>
                        <div class="gm-popup-address">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#1a73e8" style="flex-shrink:0"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            <span>${eat.address || 'Đang cập nhật'}</span>
                        </div>
                        <div class="gm-popup-actions">
                            <a href="${directionsUrl}" target="_blank" class="gm-btn-direction">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M21.71 11.29l-9-9c-.39-.39-1.02-.39-1.41 0l-9 9c-.39.39-.39 1.02 0 1.41l9 9c.39.39 1.02.39 1.41 0l9-9c.39-.38.39-1.01 0-1.41zM14 14.5V12h-4v3H8v-4c0-.55.45-1 1-1h5V7.5l3.5 3.5-3.5 3.5z"/></svg>
                                Đường đi
                            </a>
                            <a href="/dia-diem/${eat.slug}" class="gm-btn-detail">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            `;

            marker.setPopupContent(popupContent);
        } catch (_) {
            marker.setPopupContent('<div style="padding:20px;text-align:center;color:#ea4335;">Lỗi tải dữ liệu</div>');
        }
    }

    // ==================== SIDEBAR ITEMS ====================
    function renderSidebarItems(slug, items, isFirstLoad) {
        const container = document.getElementById('cat-items-' + slug);
        if (!container) return;

        let html = '';
        items.forEach(eat => {
            const imgUrl = eat.image_path || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=150&q=80';
            html += `
                <div class="map-list-item" onclick="window._mapFocusEatery('${eat.slug}', ${eat.latitude}, ${eat.longitude})">
                    <img src="${imgUrl}" class="map-list-item-img" alt="${eat.name}" loading="lazy">
                    <div class="map-list-item-info">
                        <div class="map-list-item-name">${eat.name}</div>
                        <div class="map-list-item-addr">${eat.address || 'Đang cập nhật địa chỉ...'}</div>
                    </div>
                </div>
            `;
        });

        if (isFirstLoad) {
            container.innerHTML = html;
        } else {
            // Remove old "load more" button before appending
            const oldBtn = container.querySelector('.sidebar-load-more');
            if (oldBtn) oldBtn.remove();
            container.insertAdjacentHTML('beforeend', html);
        }
    }

    function appendLoadMoreButton(slug) {
        const container = document.getElementById('cat-items-' + slug);
        if (!container) return;
        container.insertAdjacentHTML('beforeend', `
            <div class="sidebar-load-more" onclick="window._mapLoadMore('${slug}')">
                📥 Tải thêm...
            </div>
        `);
    }

    window._mapLoadMore = function(slug) {
        loadCategoryData(slug, sidebarPage + 1, false);
    };

    // ==================== FOCUS EATERY ====================
    window._mapFocusEatery = function(slug, lat, lng) {
        if (!lat || !lng) return;
        searchMap.flyTo([lat, lng], 17, { animate: true, duration: 1.2 });
        setTimeout(() => {
            if (markers[slug]) {
                loadPopup(markers[slug]);
            }
        }, 1200);

        // Mobile behavior
        if (window.innerWidth <= 768) {
            document.querySelector('.map-sidebar').style.height = '20vh';
            document.querySelector('.map-area').style.height = '80vh';
        }
    };

    // ==================== SEARCH ====================
    function setupSearch() {
        const input = document.getElementById('searchInput');
        input.addEventListener('input', function() {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => performSearch(this.value.trim()), 300);
        });

        // If URL has keyword, auto-search
        const urlKeyword = '{{ $keyword ?? '' }}';
        if (urlKeyword) {
            performSearch(urlKeyword);
        }
    }

    async function performSearch(keyword) {
        if (!keyword || keyword.length < 2) {
            // Reset to category view
            renderCategorySidebar();
            clusterGroup.clearLayers();
            markers = {};
            activeCategory = null;
            if (categories.length > 0) {
                toggleCategory(categories[0].slug);
            }
            return;
        }

        // Show loading
        document.getElementById('sidebarContent').innerHTML = '<div class="sidebar-loading">🔍 Đang tìm kiếm...</div>';
        clusterGroup.clearLayers();
        markers = {};
        activeCategory = null;

        try {
            const [markersRes, sidebarRes] = await Promise.all([
                fetch(`/api/map/markers?q=${encodeURIComponent(keyword)}`),
                fetch(`/api/map/sidebar?q=${encodeURIComponent(keyword)}`)
            ]);

            const markersData = await markersRes.json();
            const sidebarData = await sidebarRes.json();

            if (markersData.data.length === 0) {
                document.getElementById('sidebarContent').innerHTML = 
                    '<div class="sidebar-loading">Không tìm thấy kết quả cho "' + keyword + '"</div>';
                return;
            }

            // Group results by category for display
            const grouped = {};
            sidebarData.data.forEach(eat => {
                const catSlug = eat.category?.slug || 'other';
                if (!grouped[catSlug]) grouped[catSlug] = [];
                grouped[catSlug].push(eat);
            });

            // Render search results
            let html = `<div style="padding:12px 20px;font-size:0.85rem;color:#70757a;border-bottom:1px solid #e8eaed;">
                Tìm thấy ${markersData.meta.total} kết quả
            </div>`;

            for (let catSlug in grouped) {
                const color = getColor(catSlug);
                const icon = getIcon(catSlug);
                const label = getLabel(catSlug, catSlug);

                html += `<div class="category-group">
                    <div class="category-header active" style="border-left-color:${color};background:#f4f8ff;">
                        <div class="category-icon-wrapper" style="background-color:${color}">${icon}</div>
                        <div style="flex:1;">${label} <span style="font-weight:normal;opacity:0.8">(${grouped[catSlug].length})</span></div>
                    </div>
                    <div class="category-items" style="display:block;">`;

                grouped[catSlug].forEach(eat => {
                    const imgUrl = eat.image_path || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=150&q=80';
                    html += `
                        <div class="map-list-item" onclick="window._mapFocusEatery('${eat.slug}', ${eat.latitude}, ${eat.longitude})">
                            <img src="${imgUrl}" class="map-list-item-img" alt="${eat.name}" loading="lazy">
                            <div class="map-list-item-info">
                                <div class="map-list-item-name">${eat.name}</div>
                                <div class="map-list-item-addr">${eat.address || 'Đang cập nhật'}</div>
                            </div>
                        </div>`;
                });
                html += '</div></div>';
            }

            document.getElementById('sidebarContent').innerHTML = html;

            // Add markers
            markersData.data.forEach(m => {
                addMarkersToMap([m], m.cat || 'other');
            });

            if (clusterGroup.getLayers().length > 0) {
                searchMap.fitBounds(clusterGroup.getBounds(), { padding: [60, 60] });
            }
        } catch (_) {
            document.getElementById('sidebarContent').innerHTML = 
                '<div class="sidebar-loading">❌ Lỗi tìm kiếm</div>';
        }
    }

    function toggleCategory(slug) {
        window._mapToggleCategory(slug);
    }
})();
</script>
@endsection
