@extends('layouts.admin')

@section('title', '👥 Quản lý tài khoản người dùng')

@section('content')
<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                <span>👥</span> Quản Lý User
            </h1>
            <p style="color: rgba(255,255,255,0.85); margin-top: 4px;">Quản lý và phân quyền thông minh cho tất cả tài khoản người dùng trên hệ thống</p>
        </div>
        <a href="/admin/users/create" class="btn-admin btn-admin-accent" style="padding: 10px 20px; border-radius: 10px; background-color: #ffffff; color: var(--admin-primary); font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: none;">
            ➕ Thêm User Mới
        </a>
    </div>
</div>

<!-- Quick Statistics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <!-- Stat Card 1 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #eff6ff; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #3b82f6;">👥</div>
        <div>
            <span style="font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Tổng Người Dùng</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $totalUsers }}</strong>
        </div>
    </div>

    <!-- Stat Card 2 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #fef3c7; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #d97706;">🏛️</div>
        <div>
            <span style="font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Tiểu Thương Chợ</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $marketSellerCount ?? 0 }}</strong>
        </div>
    </div>

    <!-- Stat Card 3 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #eef2ff; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #4f46e5;">🏢</div>
        <div>
            <span style="font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Hộ Kinh Doanh / DN</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $cskdSellerCount ?? 0 }}</strong>
        </div>
    </div>

    <!-- Stat Card 4 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #faf5ff; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #a855f7;">🛡️</div>
        <div>
            <span style="font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Ban Quản Trị / BQL</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $adminCount }}</strong>
        </div>
    </div>
</div>

<!-- Search & Data Grid Block -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">
            <span>📋</span> Danh Sách Tài Khoản Người Dùng
        </h2>
    </div>

    <!-- Smart Multi-Tier Filter Control Panel -->
    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap; flex: 1; min-width: 300px;">
            <!-- 1. Instant Search Input -->
            <div style="position: relative; flex: 1.5; min-width: 220px;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted); font-size: 0.95rem;">🔍</span>
                <input type="text" id="user-search-input" value="{{ request('search') }}" class="admin-form-input" placeholder="Tìm theo Tên, Email, SĐT, Tên HKD..." style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
            </div>

            <!-- 2. User Group / Type Filter Dropdown -->
            <select id="user-type-filter" class="admin-form-input" style="flex: 1; min-width: 180px; border-radius: 10px; height: 42px; font-size: 0.88rem; font-weight: 600; color: #1e293b;">
                <option value="">🏷️ Tất cả Nhóm Tài Khoản</option>
                <option value="market_seller" {{ request('user_type') === 'market_seller' ? 'selected' : '' }}>🏛️ Tiểu thương Chợ truyền thống</option>
                <option value="cskd_seller" {{ request('user_type') === 'cskd_seller' ? 'selected' : '' }}>🏢 Hộ kinh doanh & Doanh nghiệp</option>
                <option value="principal" {{ request('user_type') === 'principal' ? 'selected' : '' }}>🏫 Ban giám hiệu / Trường học</option>
                <option value="admin_group" {{ request('user_type') === 'admin_group' ? 'selected' : '' }}>🛡️ Ban quản trị (Admin & BQL)</option>
                <option value="customer" {{ request('user_type') === 'customer' ? 'selected' : '' }}>👤 Khách hàng / Người dân</option>
            </select>

            <!-- 3. Contextual Filter: Market Dropdown (when Market selected or default) -->
            <div id="market-filter-wrapper" style="{{ request('user_type') === 'cskd_seller' ? 'display: none;' : 'display: block;' }}">
                <select id="user-market-filter" class="admin-form-input" style="min-width: 180px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
                    <option value="">🏛️ Tất cả Chợ truyền thống</option>
                    @if(isset($markets) && count($markets) > 0)
                        @foreach($markets as $m)
                            <option value="{{ $m->id }}" {{ request('market_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- 4. Contextual Filter: Commune Dropdown (when CSKD selected) -->
            <div id="commune-filter-wrapper" style="{{ request('user_type') === 'cskd_seller' ? 'display: block;' : 'display: none;' }}">
                <select id="user-commune-filter" class="admin-form-input" style="min-width: 180px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
                    <option value="">📍 Tất cả Xã / Địa bàn</option>
                    @if(isset($communes) && count($communes) > 0)
                        @foreach($communes as $c)
                            <option value="{{ $c->id }}" {{ request('commune_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- 5. Status Filter Dropdown -->
            <select id="user-status-filter" class="admin-form-input" style="width: 150px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
                <option value="">⚡ Tất cả trạng thái</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Vô hiệu hóa</option>
            </select>

            <!-- Reset Filter Button -->
            <button id="btn-reset-filters" type="button" class="btn-admin" style="padding: 0 14px; height: 42px; border-radius: 10px; background-color: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px;" title="Xóa toàn bộ bộ lọc về mặc định">
                🔄 Đặt lại
            </button>
        </div>

        <!-- Action Buttons Panel -->
        <div style="display: flex; gap: 8px;">
            <a href="/admin/users/create" class="btn-admin btn-admin-primary" style="padding: 10px 18px; border-radius: 10px; display: flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; height: 42px; border: none;">
                ➕ Thêm mới
            </a>
            <a href="{{ route('admin.users.export', request()->query()) }}" class="btn-admin btn-admin-accent" style="padding: 10px 18px; border-radius: 10px; display: flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; height: 42px; background-color: #10b981; border: none; color: #ffffff; text-decoration: none;">
                📥 Xuất File
            </a>
        </div>
    </div>

    <!-- Table Container -->
    <div id="users-table-wrapper">
        @include('admin.users.partial-table')
    </div>
</div>

<!-- ==========================================================================
     ZERO-RELOAD AJAX INSTANT MULTI-TIER FILTERING & AUTO FOCUS
     ========================================================================== -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('user-search-input');
        const userTypeFilter = document.getElementById('user-type-filter');
        const marketFilterWrapper = document.getElementById('market-filter-wrapper');
        const marketFilter = document.getElementById('user-market-filter');
        const communeFilterWrapper = document.getElementById('commune-filter-wrapper');
        const communeFilter = document.getElementById('user-commune-filter');
        const statusFilter = document.getElementById('user-status-filter');
        const btnReset = document.getElementById('btn-reset-filters');
        const tableWrapper = document.getElementById('users-table-wrapper');

        let debounceTimeout = null;

        // Xử lý chuyển đổi giao diện linh hoạt giữa Chợ và Xã khi chọn Nhóm tài khoản
        function handleUserTypeChange() {
            const selectedType = userTypeFilter.value;
            if (selectedType === 'cskd_seller') {
                marketFilterWrapper.style.display = 'none';
                if (marketFilter) marketFilter.value = '';
                communeFilterWrapper.style.display = 'block';
            } else if (selectedType === 'market_seller') {
                communeFilterWrapper.style.display = 'none';
                if (communeFilter) communeFilter.value = '';
                marketFilterWrapper.style.display = 'block';
            } else {
                // Mặc định hiện Chợ, ẩn Xã
                communeFilterWrapper.style.display = 'none';
                if (communeFilter) communeFilter.value = '';
                marketFilterWrapper.style.display = 'block';
            }
        }

        function fetchUsers() {
            const searchVal = searchInput.value.trim();
            const userTypeVal = userTypeFilter.value;
            const marketVal = marketFilter ? marketFilter.value : '';
            const communeVal = communeFilter ? communeFilter.value : '';
            const statusVal = statusFilter.value;

            // Xây dựng query URL
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchVal);
            url.searchParams.set('user_type', userTypeVal);
            url.searchParams.set('market_id', marketVal);
            url.searchParams.set('commune_id', communeVal);
            url.searchParams.set('status', statusVal);
            url.searchParams.set('page', 1); // Reset to page 1 on filter

            // Update URL bar without reloading
            window.history.pushState({}, '', url);

            // Gửi request AJAX lấy dữ liệu partial table
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                tableWrapper.innerHTML = html;
                
                // Khôi phục auto focus vào ô nhập liệu để nhập liên tục
                if (document.activeElement === searchInput) {
                    searchInput.focus();
                    const len = searchInput.value.length;
                    searchInput.setSelectionRange(len, len);
                }
            })
            .catch(err => console.error("Lỗi AJAX: ", err));
        }

        // Bắt sự kiện thay đổi nhóm tài khoản
        userTypeFilter.addEventListener('change', function() {
            handleUserTypeChange();
            fetchUsers();
        });

        // Bắt sự kiện gõ tìm kiếm kèm Debounce
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(fetchUsers, 300);
        });

        // Bắt sự kiện chọn bộ lọc
        if (marketFilter) marketFilter.addEventListener('change', fetchUsers);
        if (communeFilter) communeFilter.addEventListener('change', fetchUsers);
        statusFilter.addEventListener('change', fetchUsers);

        // Nút đặt lại bộ lọc
        btnReset.addEventListener('click', function() {
            searchInput.value = '';
            userTypeFilter.value = '';
            if (marketFilter) marketFilter.value = '';
            if (communeFilter) communeFilter.value = '';
            statusFilter.value = '';
            handleUserTypeChange();
            fetchUsers();
        });

        // Hỗ trợ bắt sự kiện click phân trang AJAX
        tableWrapper.addEventListener('click', function(e) {
            const paginatorLink = e.target.closest('.pagination a');
            if (paginatorLink) {
                e.preventDefault();
                const targetUrl = new URL(paginatorLink.href);

                // Đồng bộ từ input hiện tại
                targetUrl.searchParams.set('search', searchInput.value.trim());
                targetUrl.searchParams.set('user_type', userTypeFilter.value);
                if (marketFilter) targetUrl.searchParams.set('market_id', marketFilter.value);
                if (communeFilter) targetUrl.searchParams.set('commune_id', communeFilter.value);
                targetUrl.searchParams.set('status', statusFilter.value);

                window.history.pushState({}, '', targetUrl);

                fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    tableWrapper.innerHTML = html;
                    tableWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                })
                .catch(err => console.error(err));
            }
        });
    });
</script>
@endsection
