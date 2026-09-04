@extends('layouts.admin')

@section('title', '🛒 Quản Lý Sản Phẩm Trưng Bày — Chợ Du Lịch Cổ Loa')

@section('content')
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); padding: 24px; border-radius: 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem; color: #ffffff; display: flex; align-items: center; gap: 8px; margin: 0;">
                <span>🏛️</span> Quản Lý Sản Phẩm Trưng Bày — Chợ văn hoá Du lịch Cổ Loa
            </h1>
            <p style="color: rgba(255,255,255,0.85); margin-top: 6px; margin-bottom: 0;">
                Cổng điều hành & Quảng bá danh mục sản phẩm văn hóa, OCOP & Quà lưu niệm Cổ Loa
            </p>
        </div>
        <a href="/admin/stalls/create" class="btn-admin btn-admin-accent" style="padding: 10px 20px; border-radius: 10px; background-color: #ffffff; color: #0284c7; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: none; text-decoration: none;">
            ➕ Thêm Sản Phẩm Mới
        </a>
    </div>
</div>

<!-- ==========================================================================
     QUICK STATISTICS METRICS (Khu vực chỉ số Sản phẩm Chợ Du Lịch Cổ Loa)
     ========================================================================== -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Stat Card 1 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #eff6ff; width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #3b82f6;">📦</div>
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">TỔNG SỐ SẢN PHẨM TRƯNG BÀY</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $totalMatHang ?: $totalHoKinhDoanh }} Sản Phẩm</strong>
            <small style="display: block; font-size: 0.72rem; color: #10b981; margin-top: 4px;">✓ Đã xuất bản lên hệ thống</small>
        </div>
    </div>

    <!-- Stat Card 2 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #f0fdf4; width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #10b981;">🌾</div>
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">SẢN PHẨM OCOP & ĐẶC SẢN</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $totalMatHang }} Sản Phẩm</strong>
            <small style="display: block; font-size: 0.72rem; color: #10b981; margin-top: 4px;">🔹 Bánh chưng, Bún Mạch Tràng...</small>
        </div>
    </div>

    <!-- Stat Card 3 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #fefce8; width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #eab308;">🎨</div>
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">SẢN PHẨM LÀNG NGHỀ</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $hoSmartphone ?: 5 }} Sản Phẩm</strong>
            <small style="display: block; font-size: 0.72rem; color: #0284c7; margin-top: 4px;">⚡ Chạm khắc gỗ, quà lưu niệm</small>
        </div>
    </div>

    <!-- Stat Card 4 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #ecfdf5; width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #059669;">📱</div>
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">MÃ QR TRA CỨU DU LỊCH</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">100%</strong>
            <small style="display: block; font-size: 0.72rem; color: #059669; margin-top: 4px;">📲 Quét tra cứu thông tin di sản</small>
        </div>
    </div>
</div>

<!-- ==========================================================================
     TRUNG TÂM PHÂN TÍCH DANH MỤC SẢN PHẨM DU LỊCH
     ========================================================================== -->
<div class="admin-card" style="margin-bottom: 28px; padding: 24px; background: #ffffff; border-radius: 16px;">
    <div style="margin-bottom: 20px; border-bottom: 1.5px solid var(--admin-border); padding-bottom: 12px;">
        <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--admin-text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
            <span>📊</span> Trung Tâm Phân Tích Danh Mục Sản Phẩm Du Lịch
        </h2>
        <p style="font-size: 0.83rem; color: var(--admin-text-muted); margin-top: 4px; margin-bottom: 0;">Phân tích cơ cấu nhóm hàng, phân hạng OCOP & nguồn gốc sản phẩm trưng bày</p>
    </div>

    <!-- 3 Donut Charts Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 28px;">
        <!-- Chart 1: Nhóm sản phẩm trưng bày -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; text-align: center;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 14px;">Phân loại Nhóm Sản phẩm</h4>
            <div style="height: 200px; position: relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Phân hạng OCOP / Đặc sản -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; text-align: center;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 14px;">Phân hạng OCOP & Đặc sản</h4>
            <div style="height: 200px; position: relative;">
                <canvas id="qrChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Trạng thái Trưng bày -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; text-align: center;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 14px;">Trạng thái Hiển thị Trưng bày</h4>
            <div style="height: 200px; position: relative;">
                <canvas id="bankChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Horizontal Bar Chart: Nguồn gốc / Làng nghề -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
        <h4 style="font-size: 0.92rem; font-weight: 700; color: #334155; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
            <span>🌱</span> Nguồn gốc / Xuất xứ Sản phẩm Trưng bày (%)
        </h4>
        <div style="height: 220px; position: relative;">
            <canvas id="originChart"></canvas>
        </div>
    </div>
</div>

<!-- ==========================================================================
     DANH SÁCH SẢN PHẨM TRƯNG BÀY (TABLE DATA & SEARCH FILTER)
     ========================================================================== -->
<div class="admin-card" style="background: #ffffff; border-radius: 16px; padding: 24px;">
    <div class="admin-card-header" style="margin-bottom: 16px;">
        <h2 class="admin-card-title" style="font-size: 1.15rem; font-weight: 800; color: var(--admin-text-main); margin: 0;">
            <span>📋</span> Danh Sách Sản Phẩm Trưng Bày Chợ Du Lịch Cổ Loa
        </h2>
    </div>

    <!-- Filter Control Panel -->
    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1; min-width: 280px; max-width: 650px;">
            <!-- Instant Search Input -->
            <div style="position: relative; flex: 1; min-width: 200px;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted); font-size: 0.95rem;">🔍</span>
                <input type="text" id="stall-search-input" value="{{ request('search') }}" class="admin-form-input" placeholder="Tìm kiếm tên sản phẩm trưng bày..." style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.88rem; width: 100%;">
            </div>

            <!-- Category Filter Dropdown -->
            <select id="stall-category-filter" class="admin-form-input" style="width: 180px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
                <option value="">📌 Tất cả loại sản phẩm</option>
                <option value="Ăn uống" {{ request('category') == 'Ăn uống' ? 'selected' : '' }}>🍜 Ẩm thực / Ăn uống</option>
                <option value="Rau củ" {{ request('category') == 'Rau củ' ? 'selected' : '' }}>🥬 Đặc sản Nông sản</option>
                <option value="Thực phẩm khô" {{ request('category') == 'Thực phẩm khô' ? 'selected' : '' }}>📦 Bánh chưng / Đồ khô</option>
                <option value="Thịt tươi" {{ request('category') == 'Thịt tươi' ? 'selected' : '' }}>🎨 Thủ công / Lưu niệm</option>
            </select>
        </div>
    </div>

    <!-- Dynamic Stalls Table Wrapper -->
    <div id="stalls-table-wrapper">
        @include('admin.stalls.partial-table')
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart Nhóm Sản Phẩm
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: ['OCOP & Đặc sản', 'Quà lưu niệm', 'Di sản & Hiện vật', 'Nông sản Cổ Loa', 'Khác'],
            datasets: [{
                data: [{{ $catStats['Ăn uống'] ?: 40 }}, {{ $catStats['Rau củ'] ?: 25 }}, {{ $catStats['Thực phẩm khô'] ?: 15 }}, {{ $catStats['Thịt tươi'] ?: 10 }}, {{ $catStats['Khác'] ?: 10 }}],
                backgroundColor: ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#64748b']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // 2. Chart Phân hạng OCOP
    const ctxQr = document.getElementById('qrChart').getContext('2d');
    new Chart(ctxQr, {
        type: 'doughnut',
        data: {
            labels: ['Đạt OCOP 4-5 sao (45%)', 'Đạt OCOP 3 sao (35%)', 'Đặc sản làng nghề (20%)'],
            datasets: [{
                data: [45, 35, 20],
                backgroundColor: ['#10b981', '#f59e0b', '#0284c7']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // 3. Chart Trạng thái Hiển thị
    const ctxBank = document.getElementById('bankChart').getContext('2d');
    new Chart(ctxBank, {
        type: 'doughnut',
        data: {
            labels: ['Đang hiển thị Trang chủ (90%)', 'Chờ cập nhật (10%)'],
            datasets: [{
                data: [90, 10],
                backgroundColor: ['#0284c7', '#cbd5e1']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // 4. Horizontal Bar Chart: Xuất xứ
    const ctxOrigin = document.getElementById('originChart').getContext('2d');
    new Chart(ctxOrigin, {
        type: 'bar',
        data: {
            labels: [
                'Bánh chưng & Bún Mạch Tràng Cổ Loa',
                'Chạm khắc gỗ Làng Vân Hà',
                'Nông sản sạch Cổ Loa',
                'Sản phẩm OCOP Huyện Đông Anh',
                'Địa phương lân cận'
            ],
            datasets: [{
                label: 'Tỷ lệ % xuất xứ sản phẩm trưng bày',
                data: [40, 25, 20, 10, 5],
                backgroundColor: '#0284c7',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, max: 50 } }
        }
    });

    // AJAX Filtering
    const searchInput = document.getElementById('stall-search-input');
    const categoryFilter = document.getElementById('stall-category-filter');
    const tableWrapper = document.getElementById('stalls-table-wrapper');
    let debounceTimeout = null;

    function fetchStalls() {
        const searchVal = searchInput.value.trim();
        const categoryVal = categoryFilter.value;

        const url = new URL(window.location.href);
        url.searchParams.set('search', searchVal);
        url.searchParams.set('category', categoryVal);
        url.searchParams.set('page', 1);

        window.history.pushState({}, '', url);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                tableWrapper.innerHTML = html;
                searchInput.focus();
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
            });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(fetchStalls, 300);
    });

    categoryFilter.addEventListener('change', fetchStalls);
});
</script>
@endsection
