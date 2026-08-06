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
            <p style="color: rgba(255,255,255,0.85); margin-top: 4px;">Quản lý và cấp quyền tất cả tài khoản người dùng trên hệ thống</p>
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
            <span style="font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Tổng User</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $totalUsers }}</strong>
        </div>
    </div>

    <!-- Stat Card 2 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #faf5ff; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #a855f7;">👨‍💼</div>
        <div>
            <span style="font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Admin</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $adminCount }}</strong>
        </div>
    </div>

    <!-- Stat Card 3 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #ecfdf5; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #10b981;">👨‍🍳</div>
        <div>
            <span style="font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Seller</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $sellerCount }}</strong>
        </div>
    </div>

    <!-- Stat Card 4 -->
    <div style="padding: 20px; background-color: #ffffff; border: 1.5px solid var(--admin-border); border-radius: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #fff7ed; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #f97316;">🧑</div>
        <div>
            <span style="font-size: 0.76rem; font-weight: 700; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Customer</span>
            <strong style="font-size: 1.5rem; color: var(--admin-text-main); line-height: 1;">{{ $userCount }}</strong>
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

    <!-- Filter Control Panel -->
    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1; min-width: 280px; max-width: 600px;">
            <!-- Instant Search Input -->
            <div style="position: relative; flex: 1; min-width: 200px;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted); font-size: 0.95rem;">🔍</span>
                <input type="text" id="user-search-input" value="{{ request('search') }}" class="admin-form-input" placeholder="Tìm kiếm theo Tên, Email, SĐT..." style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
            </div>

            <!-- Status Filter Dropdown -->
            <select id="user-status-filter" class="admin-form-input" style="width: 180px; border-radius: 10px; height: 42px; font-size: 0.88rem;">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Vô hiệu hóa</option>
            </select>
        </div>

        <!-- Buttons Panel -->
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
     ZERO-RELOAD AJAX INSTANT FILTERING & AUTO FOCUS
     ========================================================================== -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('user-search-input');
        const statusFilter = document.getElementById('user-status-filter');
        const tableWrapper = document.getElementById('users-table-wrapper');

        let debounceTimeout = null;

        function fetchUsers() {
            const searchVal = searchInput.value.trim();
            const statusVal = statusFilter.value;

            // Xây dựng query URL
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchVal);
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
                
                // Khôi phục auto focus vào ô nhập liệu để nhập liên tục không bị mất dấu nháy
                searchInput.focus();
                
                // Đưa con trỏ chuột về cuối input text
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
            })
            .catch(err => console.error("Lỗi AJAX: ", err));
        }

        // Bắt sự kiện gõ tìm kiếm kèm Debounce tránh gửi request dồn dập
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(fetchUsers, 300); // Đợi 300ms sau khi dừng gõ
        });

        // Bắt sự kiện chọn bộ lọc trạng thái lập tức
        statusFilter.addEventListener('change', fetchUsers);

        // Hỗ trợ bắt sự kiện click phân trang AJAX
        tableWrapper.addEventListener('click', function(e) {
            const paginatorLink = e.target.closest('.pagination a');
            if (paginatorLink) {
                e.preventDefault();
                const targetUrl = new URL(paginatorLink.href);

                // Đồng bộ từ input hiện tại
                targetUrl.searchParams.set('search', searchInput.value.trim());
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
                    // Tự động cuộn mượt lên đầu bảng
                    tableWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                })
                .catch(err => console.error(err));
            }
        });
    });
</script>
@endsection
