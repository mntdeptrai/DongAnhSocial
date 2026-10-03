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
        max-height: 55vh; /* Giới hạn chiều cao bằng 55% màn hình */
        overflow-y: auto; /* Có thanh cuộn bên trong nếu quá dài */
    }
    
    /* Scrollbar nhỏ gọn cho danh sách bên trong */
    .category-items::-webkit-scrollbar {
        width: 4px;
    }
    .category-items::-webkit-scrollbar-track {
        background: #transparent; 
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
                <input type="text" id="searchInput" placeholder="Tìm kiếm địa danh, trường học...">
            </div>
        </div>
        
        <div class="map-sidebar-content" id="sidebarContent">
            <!-- Dynamic grouped content will be rendered here via JS -->
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
    const eateries = @json($eateries);
    let searchMap;
    // markers[slug] = leaflet marker object (chỉ tạo khi cần - lazy)
    let markers = {};
    // markerData[slug] = raw eatery data để tạo marker on demand
    let markerData = {};
    let currentCategorySlug = null;

    document.addEventListener("DOMContentLoaded", function() {
        // Init Map với Canvas renderer (nhanh hơn SVG khi nhiều marker)
        searchMap = L.map('searchMap', {
            zoomControl: false,
            preferCanvas: true,
        }).setView([21.1352, 105.8458], 12);
        
        L.control.zoom({ position: 'bottomright' }).addTo(searchMap);
        
        // Sử dụng Google Maps Tiles
        L.tileLayer('https://mt1.google.com/vt/lyrs=m&hl=vi&x={x}&y={y}&z={z}', {
            attribution: '&copy; Google Maps',
            maxZoom: 20
        }).addTo(searchMap);

        // Index tất cả eatery vào markerData (không tạo DOM)
        eateries.forEach(eat => {
            if (eat.slug) markerData[eat.slug] = eat;
        });

        renderSidebar(eateries);
        
        // Search filter - debounce 250ms, chỉ re-render sidebar
        const searchInput = document.getElementById('searchInput');
        let searchTimer = null;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const val = e.target.value.toLowerCase().trim();
                const filtered = val ? eateries.filter(eat => 
                    (eat.name && eat.name.toLowerCase().includes(val)) || 
                    (eat.address && eat.address.toLowerCase().includes(val)) ||
                    (eat.category && eat.category.name.toLowerCase().includes(val))
                ) : eateries;
                clearAllMarkers();
                currentCategorySlug = null;
                renderSidebar(filtered);
            }, 250);
        });
    });

    function getCategoryColor(slug) {
        switch(slug) {
            case 'hanh-trinh-di-san': return '#8B4513'; // Nâu đất di tích
            case 'smart-education-map': return '#1a73e8'; // Xanh blue trường học
            case 'wellness-care': return '#34a853'; // Xanh lá y tế
            case 'stay-in-dong-anh': return '#9334e6'; // Tím khách sạn
            case 'dong-anh-market': return '#f29900'; // Vàng chợ
            case 'dong-anh-food-map': return '#ea4335'; // Đỏ ẩm thực
            case 'discover-dong-anh-community-culture-hub': return '#e81e63'; // Hồng văn hóa
            case 'co-so-kinh-doanh': return '#0284c7'; // Xanh cơ sở kinh doanh
            default: return '#70757a';
        }
    }
    
    function getCategoryIcon(slug) {
        switch(slug) {
            case 'hanh-trinh-di-san': return '⛩️'; 
            case 'smart-education-map': return '🎓'; 
            case 'wellness-care': return '🏥'; 
            case 'stay-in-dong-anh': return '🏨'; 
            case 'dong-anh-market': return '🛍️'; 
            case 'dong-anh-food-map': return '🍜'; 
            case 'discover-dong-anh-community-culture-hub': return '🏛️'; 
            case 'co-so-kinh-doanh': return '🏪';
            default: return '📍';
        }
    }
    
    function getCategoryLabel(slug, originalName) {
        switch(slug) {
            case 'hanh-trinh-di-san': return 'DI TÍCH QUỐC GIA & DI SẢN';
            case 'smart-education-map': return 'HỆ THỐNG TRƯỜNG HỌC';
            case 'wellness-care': return 'BỆNH VIỆN & CƠ SỞ Y TẾ';
            case 'stay-in-dong-anh': return 'KHÁCH SẠN & LƯU TRÚ';
            case 'dong-anh-market': return 'CHỢ TRUYỀN THỐNG & SIÊU THỊ';
            case 'dong-anh-food-map': return 'ĐỊA ĐIỂM ẨM THỰC';
            case 'discover-dong-anh-community-culture-hub': return 'NHÀ VĂN HÓA & THỂ THAO';
            case 'co-so-kinh-doanh': return 'CƠ SỞ KINH DOANH, DOANH NGHIỆP';
            default: return originalName.toUpperCase();
        }
    }

    // Tạo marker Leaflet cho 1 eatery theo yêu cầu (lazy)
    function createMarker(eat) {
        const slug = eat.slug;
        if (markers[slug]) return markers[slug]; // reuse nếu đã tạo
        if (!eat.latitude || !eat.longitude) return null;

        const catSlug = eat.category ? eat.category.slug : '';
        const color = getCategoryColor(catSlug);
        const catIcon = getCategoryIcon(catSlug);

        const iconHtml = `<div style="background-color:${color};width:40px;height:40px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;border:3px solid #fff;box-shadow:2px 6px 12px rgba(0,0,0,0.4);">
            <div style="transform:rotate(45deg);font-size:18px;line-height:1;">${catIcon}</div>
        </div>`;

        const customIcon = L.divIcon({
            html: iconHtml,
            className: 'gm-marker-custom',
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });

        // Rating thực từ DB, không hardcode
        const rating = (eat.rating !== null && eat.rating !== undefined && eat.rating !== '')
            ? parseFloat(eat.rating).toFixed(1)
            : '—';
        const imgUrl = eat.image_path || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=600&q=80';
        const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${eat.latitude},${eat.longitude}`;

        const popupContent = `<div class="gm-popup-wrapper">
            <img src="${imgUrl}" class="gm-popup-cover" alt="Image" loading="lazy">
            <div class="gm-popup-body">
                <div class="gm-popup-title">${eat.name}</div>
                <div class="gm-popup-rating"><span>⭐</span> ${rating} / 5.0</div>
                <div class="gm-popup-address">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#1a73e8" style="flex-shrink:0"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    <span>${eat.address || 'Đang cập nhật...'}</span>
                </div>
                <div class="gm-popup-actions">
                    <a href="${directionsUrl}" target="_blank" class="gm-btn-direction">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M21.71 11.29l-9-9c-.39-.39-1.02-.39-1.41 0l-9 9c-.39.39-.39 1.02 0 1.41l9 9c.39.39 1.02.39 1.41 0l9-9c.39-.38.39-1.01 0-1.41zM14 14.5V12h-4v3H8v-4c0-.55.45-1 1-1h5V7.5l3.5 3.5-3.5 3.5z"/></svg>
                        Đường đi
                    </a>
                    <a href="/dia-diem/${slug}" class="gm-btn-detail">Xem chi tiết</a>
                </div>
            </div>
        </div>`;

        const marker = L.marker([eat.latitude, eat.longitude], { icon: customIcon })
            .bindPopup(popupContent, { maxWidth: 300, minWidth: 300 });
        marker.categorySlug = catSlug;
        markers[slug] = marker;
        return marker;
    }

    // Xóa marker khỏi bản đồ nhưng giữ lại object để reuse
    function clearAllMarkers() {
        for (let key in markers) {
            if (searchMap.hasLayer(markers[key])) {
                searchMap.removeLayer(markers[key]);
            }
        }
    }

    // Hiển thị marker cho 1 danh sách (lazy create + fitBounds thông minh)
    function showMarkersForList(list) {
        clearAllMarkers();
        const bounds = [];
        list.forEach(eat => {
            if (!eat.latitude || !eat.longitude) return;
            const marker = createMarker(eat);
            if (marker) {
                marker.addTo(searchMap);
                bounds.push([eat.latitude, eat.longitude]);
            }
        });

        if (bounds.length === 1) {
            searchMap.setView(bounds[0], 16);
        } else if (bounds.length > 1) {
            // maxZoom 14 để không zoom ra quá xa - tránh trông như 1 điểm
            searchMap.fitBounds(bounds, { padding: [60, 60], maxZoom: 14 });
        }
    }

    // Render sidebar (không đụng đến markers - tách biệt hoàn toàn)
    function renderSidebar(dataList) {
        const grouped = {};
        dataList.forEach(eat => {
            if (!eat.category) return;
            const catLabel = getCategoryLabel(eat.category.slug, eat.category.name);
            if (!grouped[catLabel]) {
                grouped[catLabel] = { slug: eat.category.slug, items: [] };
            }
            grouped[catLabel].items.push(eat);
        });

        let sidebarHtml = '';
        let index = 0;
        let firstCatItems = [];

        for (let catName in grouped) {
            const catInfo = grouped[catName];
            const color = getCategoryColor(catInfo.slug);
            const isOpen = index === 0;
            if (index === 0) firstCatItems = catInfo.items;

            sidebarHtml += `
                <div class="category-group">
                    <div class="category-header ${isOpen ? 'active' : ''}" id="header-${catInfo.slug}" onclick="toggleAccordion('${catInfo.slug}')">
                        <div class="category-icon-wrapper" style="background-color:${color}">${getCategoryIcon(catInfo.slug)}</div>
                        <div style="flex:1">${catName} <span style="font-weight:normal;font-size:0.85rem;opacity:0.8">(${catInfo.items.length})</span></div>
                        <svg class="category-chevron" width="20" height="20" viewBox="0 0 24 24" style="${isOpen ? 'transform:rotate(180deg)' : ''}"><path d="M7 10l5 5 5-5z"/></svg>
                    </div>
                    <div class="category-items" id="cat-items-${catInfo.slug}" style="display:${isOpen ? 'block' : 'none'}">
            `;

            catInfo.items.forEach(eat => {
                const imgUrl = eat.image_path || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=150&q=80';
                sidebarHtml += `
                    <div class="map-list-item" onclick="focusEatery('${eat.slug}', ${eat.latitude || 'null'}, ${eat.longitude || 'null'})">
                        <img src="${imgUrl}" class="map-list-item-img" alt="${eat.name}" loading="lazy">
                        <div class="map-list-item-info">
                            <div class="map-list-item-name">${eat.name}</div>
                            <div class="map-list-item-addr">${eat.address || 'Đang cập nhật địa chỉ...'}</div>
                        </div>
                    </div>
                `;
            });

            sidebarHtml += `</div></div>`;
            index++;
        }

        if (!sidebarHtml) {
            sidebarHtml = `<div style="padding:30px 20px;text-align:center;color:#70757a">Không tìm thấy kết quả nào phù hợp.</div>`;
        }

        document.getElementById('sidebarContent').innerHTML = sidebarHtml;

        // Lazy: chỉ hiện marker category đầu tiên
        if (firstCatItems.length > 0) {
            showMarkersForList(firstCatItems);
        }
    }

    window.toggleAccordion = function(clickedSlug) {
        const el = document.getElementById('cat-items-' + clickedSlug);
        const header = document.getElementById('header-' + clickedSlug);
        const isCurrentlyOpen = el && el.style.display === 'block';

        // Thu gọn tất cả
        document.querySelectorAll('.category-items').forEach(item => item.style.display = 'none');
        document.querySelectorAll('.category-header').forEach(h => {
            h.classList.remove('active');
            const ch = h.querySelector('.category-chevron');
            if (ch) ch.style.transform = '';
        });

        if (isCurrentlyOpen) {
            // Click lại category đang mở → đóng, show tất cả marker có sẵn
            clearAllMarkers();
            currentCategorySlug = null;
            for (let key in markers) {
                markers[key].addTo(searchMap);
            }
            return;
        }

        // Mở category được click
        if (el && header) {
            el.style.display = 'block';
            header.classList.add('active');
            const ch = header.querySelector('.category-chevron');
            if (ch) ch.style.transform = 'rotate(180deg)';
        }

        currentCategorySlug = clickedSlug;

        // Lazy: filter và hiển thị marker cho category này
        const items = eateries.filter(e => e.category && e.category.slug === clickedSlug);
        showMarkersForList(items);
    }

    window.focusEatery = function(slug, lat, lng) {
        if (!lat || !lng) return;

        const eat = markerData[slug];
        if (eat) {
            const marker = createMarker(eat);
            if (marker && !searchMap.hasLayer(marker)) {
                marker.addTo(searchMap);
            }
            searchMap.flyTo([lat, lng], 17, { animate: true, duration: 1.5 });
            setTimeout(() => {
                if (markers[slug]) markers[slug].openPopup();
            }, 1500);
        }

        // Mobile: collapse sidebar
        if (window.innerWidth <= 768) {
            document.querySelector('.map-sidebar').style.height = '20vh';
            document.querySelector('.map-area').style.height = '80vh';
        }
    }
</script>
@endsection
