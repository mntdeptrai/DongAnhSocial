@extends('layouts.admin')

@section('title', '🛒 Quản Lý Gian Hàng Số 4.0')

@section('content')
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                <span>🛒</span> Quản Lý Gian Hàng Số 4.0 {{ $managerEatery ? '— ' . $managerEatery->name : '' }}
            </h1>
            <p style="color: rgba(255,255,255,0.85); margin-top: 4px;">
                Cổng điều hành & Giám sát chỉ số chuyển đổi số gian hàng, hộ kinh doanh Chợ 4.0
            </p>
        </div>
        <a href="/admin/stalls/create" class="btn-admin btn-admin-accent" style="padding: 10px 20px; border-radius: 10px; background-color: #ffffff; color: #0284c7; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: none;">
            ➕ Thêm Gian Hàng Mới
        </a>
    </div>
</div>

<!-- ==========================================================================
     QUICK STATISTICS METRICS (Khu vực chỉ số số hóa Chợ 4.0)
     ========================================================================== -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Stat Card 1 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #eff6ff; width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #3b82f6;">🏪</div>
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Tổng Hộ Kinh Doanh</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $totalHoKinhDoanh }} Hộ</strong>
            <small style="display: block; font-size: 0.72rem; color: #10b981; margin-top: 4px;">✓ Đã xác minh gian hàng số</small>
        </div>
    </div>

    <!-- Stat Card 2 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #f0fdf4; width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #10b981;">📦</div>
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Tổng Số Mặt Hàng</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $totalMatHang }} Sản Phẩm</strong>
            <small style="display: block; font-size: 0.72rem; color: #10b981; margin-top: 4px;">🔹 Nông sản & Thực phẩm sạch</small>
        </div>
    </div>

    <!-- Stat Card 3 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #fefce8; width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #eab308;">📱</div>
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Hộ Dùng Smartphone</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $hoSmartphone }} Hộ</strong>
            <small style="display: block; font-size: 0.72rem; color: #0284c7; margin-top: 4px;">⚡ Tỷ lệ phủ 88%</small>
        </div>
    </div>

    <!-- Stat Card 4 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #ecfdf5; width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #059669;">💳</div>
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Tỷ Lệ Thanh Toán Số</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $hoThanhToanSoPct }}%</strong>
            <small style="display: block; font-size: 0.72rem; color: #059669; margin-top: 4px;">💳 Giao dịch không tiền mặt</small>
        </div>
    </div>
</div>

<!-- ==========================================================================
     TRUNG TÂM PHÂN TÍCH DỮ LIỆU CHỢ SỐ 4.0 (BIỂU ĐỒ THỐNG KÊ MATCHING SCREENSHOTS)
     ========================================================================== -->
<div class="admin-card" style="margin-bottom: 28px; padding: 24px;">
    <div style="margin-bottom: 20px; border-bottom: 1.5px solid var(--admin-border); padding-bottom: 12px;">
        <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--admin-text-main); display: flex; align-items: center; gap: 8px;">
            <span>📊</span> Trung Tâm Phân Tích Dữ Liệu Chợ Số 4.0
        </h2>
        <p style="font-size: 0.83rem; color: var(--admin-text-muted); margin-top: 4px;">Phân tích cơ cấu ngành hàng, tỷ lệ liên kết ngân hàng & truy xuất nguồn gốc nông sản</p>
    </div>

    <!-- 3 Donut Charts Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 28px;">
        <!-- Chart 1: Ngành hàng kinh doanh -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; text-align: center;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 14px;">Ngành hàng kinh doanh</h4>
            <div style="height: 200px; position: relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Đăng ký mã VietQR -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; text-align: center;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 14px;">Đăng ký mã VietQR</h4>
            <div style="height: 200px; position: relative;">
                <canvas id="qrChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Thiết bị & Liên kết Ngân hàng -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; text-align: center;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 14px;">Thiết bị & Liên kết Ngân hàng</h4>
            <div style="height: 200px; position: relative;">
                <canvas id="bankChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Horizontal Bar Chart: Truy xuất nguồn gốc nông sản -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
        <h4 style="font-size: 0.92rem; font-weight: 700; color: #334155; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
            <span>🌱</span> Truy xuất Nguồn gốc Hàng hóa nông sản (%)
        </h4>
        <div style="height: 260px; position: relative;">
            <canvas id="originChart"></canvas>
        </div>
    </div>
</div>

<!-- ==========================================================================
     DANH SÁCH GIAN HÀNG SỐ (TABLE DATA & SEARCH FILTER)
     ========================================================================== -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">
            <span>📋</span> Danh Sách Gian Hàng Số & Sản Phẩm
        </h2>
    </div>

    <!-- Filter Control Panel -->
    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1; min-width: 280px; max-width: 650px;">
            <!-- Instant Search Input -->
            <div style="position: relative; flex: 1; min-width: 200px;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted); font-size: 0.95rem;">🔍</span>
                <input type="text" id="stall-search-input" value="{{ request('search') }}" class="admin-form-input" placeholder="Tìm kiếm tên Gian hàng, Chủ hộ, SĐT, Sản phẩm..." style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
            </div>

            <!-- Market Filter Dropdown -->
            @if(isset($markets) && count($markets) > 0)
                <select id="stall-market-filter" class="admin-form-input" style="width: 200px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
                    <option value="">🏛️ Tất cả Chợ</option>
                    @foreach($markets as $m)
                        <option value="{{ $m->id }}" {{ request('market_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Category Filter Dropdown -->
            <select id="stall-category-filter" class="admin-form-input" style="width: 180px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
                <option value="">Tất cả Ngành hàng</option>
                <option value="Ăn uống" {{ request('category') === 'Ăn uống' ? 'selected' : '' }}>Ăn uống / Bún phở</option>
                <option value="Rau củ" {{ request('category') === 'Rau củ' ? 'selected' : '' }}>Rau củ sạch / Nông sản</option>
                <option value="Thực phẩm khô" {{ request('category') === 'Thực phẩm khô' ? 'selected' : '' }}>Thực phẩm khô / Gạo</option>
                <option value="Thịt tươi" {{ request('category') === 'Thịt tươi' ? 'selected' : '' }}>Thịt tươi / Hải sản</option>
                <option value="Hoa quả" {{ request('category') === 'Hoa quả' ? 'selected' : '' }}>Hoa quả tươi</option>
            </select>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 8px;">
            <a href="/admin/stalls/create" class="btn-admin btn-admin-primary" style="padding: 10px 18px; border-radius: 10px; display: flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; height: 42px; border: none;">
                ➕ Thêm Gian Hàng Mới
            </a>
            <button type="button" onclick="alert('Tính năng xuất báo cáo Chợ 4.0 đang hoàn thiện!')" class="btn-admin btn-admin-accent" style="padding: 10px 18px; border-radius: 10px; display: flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; height: 42px; background-color: #10b981; border: none;">
                📥 Xuất Báo Cáo
            </button>
        </div>
    </div>

    <!-- Table Container -->
    <div id="stalls-table-wrapper">
        @include('admin.stalls.partial-table')
    </div>
</div>

<!-- Render Chart Scripts -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Chart Ngành Hàng
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: ['Ăn uống', 'Rau củ', 'Thực phẩm khô', 'Thịt tươi', 'Khác'],
            datasets: [{
                data: [{{ $catStats['Ăn uống'] }}, {{ $catStats['Rau củ'] }}, {{ $catStats['Thực phẩm khô'] }}, {{ $catStats['Thịt tươi'] }}, {{ $catStats['Khác'] }}],
                backgroundColor: ['#ef4444', '#10b981', '#f59e0b', '#ec4899', '#64748b']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // 2. Chart QR Code
    const ctxQr = document.getElementById('qrChart').getContext('2d');
    new Chart(ctxQr, {
        type: 'doughnut',
        data: {
            labels: ['Đã có mã QR (88%)', 'Chưa có QR (12%)'],
            datasets: [{
                data: [88, 12],
                backgroundColor: ['#10b981', '#e2e8f0']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // 3. Chart Ngân hàng & Smartphone
    const ctxBank = document.getElementById('bankChart').getContext('2d');
    new Chart(ctxBank, {
        type: 'doughnut',
        data: {
            labels: ['Có Smartphone (88%)', 'Có tài khoản NH (88%)'],
            datasets: [{
                data: [88, 88],
                backgroundColor: ['#3b82f6', '#0284c7']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // 4. Horizontal Bar Chart: Truy xuất nguồn gốc
    const ctxOrigin = document.getElementById('originChart').getContext('2d');
    new Chart(ctxOrigin, {
        type: 'bar',
        data: {
            labels: [
                'Mua trong làng',
                'Mua trong làng, chợ Đầu',
                'Mua chợ Tó',
                'Chợ Long Biên',
                'Chợ Văn Trì',
                'Chợ đầu mối Bắc Thăng Long',
                'Tự sản xuất',
                'Mua trong làng, tự sản xuất',
                'Cơ sở sản xuất gạo sạch Hải Tiến'
            ],
            datasets: [{
                label: 'Tỷ lệ % nguồn gốc nông sản nhập khẩu/tự sản xuất',
                data: [4, 2, 2, 4, 2, 2, 6, 8, 2],
                backgroundColor: '#0284c7',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, max: 10 } }
        }
    });

    // AJAX Instant Filtering
    const searchInput = document.getElementById('stall-search-input');
    const marketFilter = document.getElementById('stall-market-filter');
    const categoryFilter = document.getElementById('stall-category-filter');
    const tableWrapper = document.getElementById('stalls-table-wrapper');
    let debounceTimeout = null;

    function fetchStalls() {
        const searchVal = searchInput.value.trim();
        const marketVal = marketFilter ? marketFilter.value : '';
        const categoryVal = categoryFilter.value;

        const url = new URL(window.location.href);
        url.searchParams.set('search', searchVal);
        url.searchParams.set('market_id', marketVal);
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

    if (marketFilter) marketFilter.addEventListener('change', fetchStalls);
    categoryFilter.addEventListener('change', fetchStalls);
});
</script>
@endsection
