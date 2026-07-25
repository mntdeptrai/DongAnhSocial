@extends('layouts.admin')

@section('title', 'Kênh Điều Hành Tối Cao System Admin')

@section('content')

@php
    $categories = $categories ?? \App\Models\Category::all();
    $communes = $communes ?? \App\Models\Commune::all();
@endphp

<style>
/* Dedicated Admin Cyber Command Palette */
.admin-cyber-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    border: 1px solid rgba(14, 165, 233, 0.3);
    border-radius: 20px;
    padding: 28px 32px;
    color: #ffffff;
    box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.5), 0 0 30px rgba(14, 165, 233, 0.15);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.admin-cyber-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.08) 0%, transparent 60%);
    animation: promax-pulse-aura 8s infinite alternate ease-in-out;
}

.admin-cyber-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: rgba(14, 165, 233, 0.15);
    border: 1px solid rgba(14, 165, 233, 0.4);
    border-radius: 30px;
    color: #38bdf8;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.admin-stat-cyber-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.admin-stat-cyber-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.admin-stat-cyber-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 35px -10px rgba(14, 165, 233, 0.25);
    border-color: #0ea5e9;
}

.admin-stat-cyber-card::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 4px; height: 100%;
    background: linear-gradient(180deg, #0ea5e9, #6366f1);
}

.admin-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 28px;
}

.admin-action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    color: #1e293b;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.admin-action-btn:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
}
</style>

<!-- SUCCESS / ERROR TOAST MESSAGES ARE HANDLED BY LAYOUT -->

<!-- EXECUTIVE HERO BANNER -->
<div class="admin-cyber-header">
    <div style="position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <div class="admin-cyber-badge">
                <span>🛡️</span> System Executive Command Center
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -0.02em; color: #ffffff;">
                Kênh Điều Hành Tối Cao System Admin
            </h1>
            <p style="margin: 0; color: #94a3b8; font-size: 0.92rem; max-width: 680px;">
                Trung tâm giám sát toàn bộ 52+ địa điểm số hóa, 30 sản phẩm OCOP chính thức, phân quyền Manager/Seller và vận hành hệ thống thông tin địa bàn Đông Anh.
            </p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="/admin/users" class="btn-admin" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; border: none; padding: 12px 22px; border-radius: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);">
                👥 Quản Lý Người Dùng
            </a>
            <a href="/admin/eateries/create" class="btn-admin" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 12px 22px; border-radius: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                ➕ Thêm Cơ Sở Mới
            </a>
        </div>
    </div>
</div>

<!-- SYSTEM METRICS GRID -->
<div class="admin-stat-cyber-grid">
    <div class="admin-stat-cyber-card">
        <div style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em;">ĐỊA ĐIỂM SỐ HÓA</div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #0f172a; margin: 4px 0;">{{ $stats['total_eateries'] }}</div>
        <div style="font-size: 0.78rem; color: #0ea5e9; font-weight: 700;">📍 Phân bổ khắp 24 Xã / Thị trấn</div>
    </div>

    <div class="admin-stat-cyber-card" style="border-right-color: #f59e0b;">
        <div style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em;">SẢN PHẨM OCOP BẢN ĐỒ</div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #d97706; margin: 4px 0;">30</div>
        <div style="font-size: 0.78rem; color: #f59e0b; font-weight: 700;">⭐ Đạt tiêu chuẩn OCOP chính thức</div>
    </div>

    <div class="admin-stat-cyber-card" style="border-right-color: #10b981;">
        <div style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em;">DANH MỤC DỊCH VỤ</div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #059669; margin: 4px 0;">{{ $stats['total_categories'] }}</div>
        <div style="font-size: 0.78rem; color: #10b981; font-weight: 700;">🍱 Đa dạng từ Ẩm thực đến Di sản</div>
    </div>

    <div class="admin-stat-cyber-card" style="border-right-color: #8b5cf6;">
        <div style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em;">TỔNG SỐ ĐÁNH GIÁ</div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #7c3aed; margin: 4px 0;">{{ $stats['total_reviews'] }}</div>
        <div style="font-size: 0.78rem; color: #8b5cf6; font-weight: 700;">💬 Phản hồi thực tế từ người dùng</div>
    </div>
</div>

<!-- EXECUTIVE QUICK CONTROL SHORTCUTS -->
<div class="admin-quick-actions">
    <a href="/admin/users" class="admin-action-btn">
        <span style="font-size: 1.5rem;">👑</span>
        <div>
            <div>Quản Lý Tài Khoản</div>
            <div style="font-size: 0.72rem; font-weight: 500; color: #64748b;">Phân quyền Admin, Manager, Seller</div>
        </div>
    </a>
    <a href="/admin/eateries/create" class="admin-action-btn">
        <span style="font-size: 1.5rem;">📍</span>
        <div>
            <div>Đăng Ký Cơ Sở Mới</div>
            <div style="font-size: 0.72rem; font-weight: 500; color: #64748b;">Thêm địa điểm vào bản đồ số</div>
        </div>
    </a>
    <a href="/" target="_blank" class="admin-action-btn">
        <span style="font-size: 1.5rem;">🗺️</span>
        <div>
            <div>Xem Bản Đồ Số</div>
            <div style="font-size: 0.72rem; font-weight: 500; color: #64748b;">Kiểm tra hiển thị trực tuyến</div>
        </div>
    </a>
</div>

<!-- MAIN LOCATIONS TABLE WORKSPACE -->
<div class="admin-card">
    <div class="admin-card-header" style="flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <h2 class="admin-card-title" style="margin-bottom: 0;">
            <span>📋</span> Danh Sách Địa Điểm Bản Đồ Số
        </h2>
    </div>

    <!-- FILTER BAR -->
    <form method="GET" action="/admin/dashboard" id="filterForm" style="width: 100%; display: flex; gap: 12px; margin-bottom: 24px; align-items: center; flex-wrap: wrap; background-color: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid var(--admin-border);">
        <div style="flex: 2; min-width: 260px;">
            <input type="text" name="q" value="{{ request('q') }}" class="admin-form-input" placeholder="🔍 Tìm theo tên cơ sở, địa chỉ hoặc SĐT..." style="width: 100%;">
        </div>
        <div style="flex: 1; min-width: 160px;">
            <select name="category" class="admin-form-input" onchange="this.form.submit()">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->name }}" {{ request('category') == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex: 1; min-width: 160px;">
            <select name="commune" class="admin-form-input" onchange="this.form.submit()">
                <option value="">Tất cả khu vực xã</option>
                @foreach($communes as $com)
                    <option value="{{ $com->name }}" {{ request('commune') == $com->name ? 'selected' : '' }}>{{ $com->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 18px;">Lọc</button>
        <a href="/admin/dashboard" class="btn-admin btn-admin-secondary" style="padding: 10px 16px; text-decoration: none;">🔄 Reset</a>
    </form>

    <!-- LOCATIONS TABLE -->
    @if($eateries->count() > 0)
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Tên Cơ Sở / Địa Điểm</th>
                        <th>Danh Mục</th>
                        <th>Xã / Thị Trấn</th>
                        <th>Số Điện Thoại</th>
                        <th>Trạng Thái</th>
                        <th style="text-align: center;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eateries as $e)
                    <tr>
                        <td><strong>#{{ $e->id }}</strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 8px; overflow: hidden; background: #e2e8f0; flex-shrink: 0;">
                                    <img src="{{ $e->image_url ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100' }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $e->name }}">
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $e->name }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">{{ Str::limit($e->address, 35) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 12px;">
                                {{ $e->category->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ $e->commune->name ?? 'N/A' }}</td>
                        <td>{{ $e->phone ?: '—' }}</td>
                        <td><span style="color: #10b981; font-weight: 700; font-size: 0.8rem;">🟢 Hoạt động</span></td>
                        <td style="text-align: center;">
                            <a href="/admin/eateries/{{ $e->slug }}/edit" class="btn-admin btn-admin-primary" style="padding: 6px 14px; font-size: 0.78rem; text-decoration: none;">
                                ⚙️ Quản lý
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 20px;">
            {{ $eateries->links() }}
        </div>
    @else
        <div style="text-align: center; padding: 40px; color: #64748b;">
            Không tìm thấy địa điểm nào phù hợp với bộ lọc.
        </div>
    @endif
</div>

@endsection
