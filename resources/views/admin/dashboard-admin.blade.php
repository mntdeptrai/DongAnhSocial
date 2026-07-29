@extends('layouts.admin')

@section('title', 'Kênh Điều Hành Tối Cao System Admin')

@section('content')

@php
    $categories = $categories ?? \App\Models\Category::all();
    $communes = $communes ?? \App\Models\Commune::all();
@endphp

<style>
/* ═══════════════════════════════════════════════════════════════
   🔥 ADMIN — CYBER NEON COMMAND CENTER THEME
   Palette: Deep Space Slate + Electric Cyan + Indigo Plasma
   ═══════════════════════════════════════════════════════════════ */

/* === Animated Orb Background === */
@keyframes admin-orb-float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(30px, -20px) scale(1.08); }
    50% { transform: translate(-20px, 15px) scale(0.95); }
    75% { transform: translate(15px, 25px) scale(1.05); }
}
@keyframes admin-glow-pulse {
    0%, 100% { opacity: 0.4; box-shadow: 0 0 40px rgba(14,165,233,0.3); }
    50% { opacity: 0.8; box-shadow: 0 0 80px rgba(99,102,241,0.5); }
}
@keyframes admin-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
@keyframes admin-counter-glow {
    0% { text-shadow: 0 0 10px rgba(14,165,233,0.3); }
    50% { text-shadow: 0 0 25px rgba(14,165,233,0.6), 0 0 50px rgba(99,102,241,0.3); }
    100% { text-shadow: 0 0 10px rgba(14,165,233,0.3); }
}
@keyframes admin-border-sweep {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}
@keyframes admin-badge-glow {
    0%, 100% { box-shadow: 0 0 8px rgba(14,165,233,0.2); }
    50% { box-shadow: 0 0 20px rgba(14,165,233,0.6), 0 0 40px rgba(99,102,241,0.2); }
}

/* === Hero Header === */
.adm-hero {
    background: linear-gradient(135deg, #020617 0%, #0f172a 40%, #1e1b4b 100%);
    border: 1.5px solid rgba(99,102,241,0.25);
    border-radius: 24px;
    padding: 36px 40px;
    color: #ffffff;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(2,6,23,0.7), inset 0 1px 0 rgba(255,255,255,0.05);
}
.adm-hero::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(14,165,233,0.15) 0%, transparent 70%);
    border-radius: 50%;
    animation: admin-orb-float 12s infinite ease-in-out;
    pointer-events: none;
}
.adm-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; left: 20%;
    width: 220px; height: 220px;
    background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 70%);
    border-radius: 50%;
    animation: admin-orb-float 15s infinite ease-in-out reverse;
    pointer-events: none;
}
.adm-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 16px;
    background: rgba(14,165,233,0.12);
    border: 1px solid rgba(14,165,233,0.45);
    border-radius: 30px;
    color: #38bdf8;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 14px;
    animation: admin-badge-glow 3s infinite ease-in-out;
}
.adm-hero h1 {
    font-size: 2rem;
    font-weight: 900;
    margin: 0 0 10px 0;
    letter-spacing: -0.025em;
    background: linear-gradient(90deg, #ffffff, #38bdf8, #a78bfa, #ffffff);
    background-size: 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: admin-shimmer 6s linear infinite;
}
.adm-hero p {
    margin: 0;
    color: #94a3b8;
    font-size: 0.92rem;
    line-height: 1.6;
    max-width: 640px;
}
.adm-hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.adm-btn-neon {
    padding: 13px 24px;
    border-radius: 14px;
    font-weight: 800;
    font-size: 0.88rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    border: none;
    cursor: pointer;
}
.adm-btn-neon.primary {
    background: linear-gradient(135deg, #0ea5e9, #6366f1);
    color: #fff;
    box-shadow: 0 10px 30px rgba(14,165,233,0.4), 0 0 15px rgba(99,102,241,0.2);
}
.adm-btn-neon.primary:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 15px 40px rgba(14,165,233,0.5), 0 0 30px rgba(99,102,241,0.3);
}
.adm-btn-neon.ghost {
    background: rgba(255,255,255,0.06);
    color: #e2e8f0;
    border: 1.5px solid rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
}
.adm-btn-neon.ghost:hover {
    background: rgba(255,255,255,0.12);
    border-color: rgba(14,165,233,0.5);
    transform: translateY(-2px);
}

/* === Stats Grid Cards === */
.adm-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}
.adm-stat-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 26px 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    box-shadow: 0 8px 25px -8px rgba(15,23,42,0.06);
}
.adm-stat-card:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 20px 40px -10px rgba(14,165,233,0.2);
    border-color: transparent;
}
.adm-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 20px 20px 0 0;
}
.adm-stat-card:nth-child(1)::before { background: linear-gradient(90deg, #0ea5e9, #38bdf8); }
.adm-stat-card:nth-child(2)::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.adm-stat-card:nth-child(3)::before { background: linear-gradient(90deg, #10b981, #34d399); }
.adm-stat-card:nth-child(4)::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
.adm-stat-card::after {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 120px; height: 120px;
    border-radius: 50%;
    opacity: 0.06;
    transition: all 0.3s ease;
}
.adm-stat-card:nth-child(1)::after { background: #0ea5e9; }
.adm-stat-card:nth-child(2)::after { background: #f59e0b; }
.adm-stat-card:nth-child(3)::after { background: #10b981; }
.adm-stat-card:nth-child(4)::after { background: #8b5cf6; }
.adm-stat-card:hover::after { opacity: 0.12; transform: scale(1.3); }

.adm-stat-label {
    font-size: 0.7rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 4px;
}
.adm-stat-value {
    font-size: 2.5rem;
    font-weight: 900;
    line-height: 1.1;
    margin: 6px 0;
}
.adm-stat-card:nth-child(1) .adm-stat-value { color: #0284c7; animation: admin-counter-glow 4s infinite; }
.adm-stat-card:nth-child(2) .adm-stat-value { color: #d97706; }
.adm-stat-card:nth-child(3) .adm-stat-value { color: #059669; }
.adm-stat-card:nth-child(4) .adm-stat-value { color: #7c3aed; }
.adm-stat-desc {
    font-size: 0.78rem;
    font-weight: 700;
}
.adm-stat-card:nth-child(1) .adm-stat-desc { color: #0ea5e9; }
.adm-stat-card:nth-child(2) .adm-stat-desc { color: #f59e0b; }
.adm-stat-card:nth-child(3) .adm-stat-desc { color: #10b981; }
.adm-stat-card:nth-child(4) .adm-stat-desc { color: #8b5cf6; }
.adm-stat-icon {
    position: absolute;
    bottom: 14px; right: 18px;
    font-size: 2.5rem;
    opacity: 0.12;
    transition: all 0.3s;
}
.adm-stat-card:hover .adm-stat-icon { opacity: 0.25; transform: scale(1.15) rotate(-5deg); }

/* === Quick Action Panel === */
.adm-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.adm-action-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 18px;
    text-decoration: none;
    color: #1e293b;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.adm-action-card:hover {
    border-color: transparent;
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(15,23,42,0.12);
}
.adm-action-card:nth-child(1):hover { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #3b82f6; }
.adm-action-card:nth-child(2):hover { background: linear-gradient(135deg, #fdf4ff, #f3e8ff); border-color: #a855f7; }
.adm-action-card:nth-child(3):hover { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-color: #22c55e; }
.adm-action-icon {
    width: 52px; height: 52px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
    transition: all 0.3s;
}
.adm-action-card:nth-child(1) .adm-action-icon { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
.adm-action-card:nth-child(2) .adm-action-icon { background: linear-gradient(135deg, #f3e8ff, #e9d5ff); }
.adm-action-card:nth-child(3) .adm-action-icon { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
.adm-action-card:hover .adm-action-icon { transform: scale(1.1) rotate(-5deg); }

/* === Premium Table Styling === */
.adm-table-wrap {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 22px;
    padding: 28px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.04);
    overflow: hidden;
}
.adm-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    flex-wrap: wrap;
    gap: 12px;
}
.adm-table-title {
    font-size: 1.25rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.adm-filter-bar {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
    background: #f8fafc;
    padding: 14px 18px;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    margin-bottom: 20px;
}
.adm-filter-bar input, .adm-filter-bar select {
    padding: 10px 14px;
    border-radius: 12px;
    border: 1.5px solid #cbd5e1;
    font-size: 0.88rem;
    font-weight: 600;
    background: #ffffff;
    color: #334155;
    transition: all 0.2s;
}
.adm-filter-bar input:focus, .adm-filter-bar select:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14,165,233,0.15);
    outline: none;
}
.adm-premium-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 6px;
}
.adm-premium-table thead th {
    padding: 14px 16px;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #64748b;
    background: #f1f5f9;
    border: none;
}
.adm-premium-table thead th:first-child { border-radius: 12px 0 0 12px; }
.adm-premium-table thead th:last-child { border-radius: 0 12px 12px 0; }
.adm-premium-table tbody tr {
    background: #ffffff;
    transition: all 0.25s ease;
    border-radius: 12px;
}
.adm-premium-table tbody tr:hover {
    background: linear-gradient(135deg, #f0f9ff, #eff6ff);
    transform: scale(1.005);
    box-shadow: 0 4px 20px rgba(14,165,233,0.1);
}
.adm-premium-table tbody td {
    padding: 16px;
    font-size: 0.88rem;
    color: #334155;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.adm-premium-table tbody td:first-child { border-left: 1px solid #f1f5f9; border-radius: 12px 0 0 12px; }
.adm-premium-table tbody td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 12px 12px 0; }
.adm-premium-table tbody tr:hover td { border-color: rgba(14,165,233,0.15); }

.adm-eatery-thumb {
    width: 44px; height: 44px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #e0f2fe, #dbeafe);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 2px solid #e0f2fe;
    flex-shrink: 0;
}
.adm-eatery-thumb img { width: 100%; height: 100%; object-fit: cover; }

.adm-cat-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    background: linear-gradient(135deg, #e0f2fe, #dbeafe);
    color: #0369a1;
}
.adm-manage-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 800;
    text-decoration: none;
    color: #ffffff;
    background: linear-gradient(135deg, #0ea5e9, #6366f1);
    box-shadow: 0 4px 15px rgba(14,165,233,0.3);
    transition: all 0.25s;
}
.adm-manage-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14,165,233,0.4);
}
</style>

<!-- ═══════════════════════ HERO BANNER ═══════════════════════ -->
<div class="adm-hero">
    <div style="position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px;">
        <div>
            <div class="adm-hero-badge">
                <span>🛡️</span> System Executive Command Center
            </div>
            <h1>Kênh Điều Hành Tối Cao System Admin</h1>
            <p>Trung tâm giám sát toàn bộ 52+ địa điểm số hóa, 30 sản phẩm OCOP chính thức, phân quyền Manager / Seller và vận hành hệ thống thông tin địa bàn Đông Anh.</p>
        </div>
        <div class="adm-hero-actions">
            <a href="/admin/users" class="adm-btn-neon primary">👥 Quản Lý Người Dùng</a>
            <a href="/admin/eateries/create" class="adm-btn-neon ghost">➕ Thêm Cơ Sở Mới</a>
        </div>
    </div>
</div>

<!-- ═══════════════════════ STATS GRID ═══════════════════════ -->
<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <div class="adm-stat-label">ĐỊA ĐIỂM SỐ HÓA</div>
        <div class="adm-stat-value">{{ $stats['total_eateries'] }}</div>
        <div class="adm-stat-desc">📍 Phân bổ khắp 24 Xã / Thị trấn</div>
        <div class="adm-stat-icon">📍</div>
    </div>
    <div class="adm-stat-card">
        <div class="adm-stat-label">SẢN PHẨM OCOP BẢN ĐỒ</div>
        <div class="adm-stat-value">30</div>
        <div class="adm-stat-desc">⭐ Đạt tiêu chuẩn OCOP chính thức</div>
        <div class="adm-stat-icon">⭐</div>
    </div>
    <div class="adm-stat-card">
        <div class="adm-stat-label">DANH MỤC DỊCH VỤ</div>
        <div class="adm-stat-value">{{ $stats['total_categories'] }}</div>
        <div class="adm-stat-desc">🍱 Đa dạng từ Ẩm thực đến Di sản</div>
        <div class="adm-stat-icon">🍱</div>
    </div>
    <div class="adm-stat-card">
        <div class="adm-stat-label">TỔNG SỐ ĐÁNH GIÁ</div>
        <div class="adm-stat-value">{{ $stats['total_reviews'] }}</div>
        <div class="adm-stat-desc">💬 Phản hồi thực tế từ người dùng</div>
        <div class="adm-stat-icon">💬</div>
    </div>
</div>

<!-- ═══════════════════════ QUICK ACTIONS ═══════════════════════ -->
<div class="adm-actions">
    <a href="/admin/users" class="adm-action-card">
        <div class="adm-action-icon">👑</div>
        <div>
            <div style="font-weight: 800; font-size: 0.95rem;">Quản Lý Tài Khoản</div>
            <div style="font-size: 0.78rem; font-weight: 500; color: #64748b; margin-top: 2px;">Phân quyền Admin, Manager, Seller</div>
        </div>
    </a>
    <a href="/admin/eateries/create" class="adm-action-card">
        <div class="adm-action-icon">📍</div>
        <div>
            <div style="font-weight: 800; font-size: 0.95rem;">Đăng Ký Cơ Sở Mới</div>
            <div style="font-size: 0.78rem; font-weight: 500; color: #64748b; margin-top: 2px;">Thêm địa điểm vào bản đồ số</div>
        </div>
    </a>
    <a href="/" target="_blank" class="adm-action-card">
        <div class="adm-action-icon">🗺️</div>
        <div>
            <div style="font-weight: 800; font-size: 0.95rem;">Xem Bản Đồ Số</div>
            <div style="font-size: 0.78rem; font-weight: 500; color: #64748b; margin-top: 2px;">Kiểm tra hiển thị trực tuyến</div>
        </div>
    </a>
</div>

<!-- ═══════════════════════ LOCATIONS TABLE ═══════════════════════ -->
<div class="adm-table-wrap">
    <div class="adm-table-header">
        <div class="adm-table-title">
            <span>📋</span> Danh Sách Cơ Sở Bản Đồ Số
        </div>
    </div>

    <form method="GET" action="/admin/dashboard" class="adm-filter-bar">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 Tìm theo tên, địa chỉ, SĐT..." style="flex: 2; min-width: 240px;">
        <select name="category" onchange="this.form.submit()" style="flex: 1; min-width: 150px;">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->name }}" {{ request('category') == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="commune" onchange="this.form.submit()" style="flex: 1; min-width: 150px;">
            <option value="">Tất cả khu vực</option>
            @foreach($communes as $com)
                <option value="{{ $com->name }}" {{ request('commune') == $com->name ? 'selected' : '' }}>{{ $com->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="adm-btn-neon primary" style="padding: 10px 20px; font-size: 0.82rem;">🔍 Lọc</button>
        <a href="/admin/dashboard" style="padding: 10px 16px; border-radius: 12px; background: #f1f5f9; color: #64748b; font-weight: 700; font-size: 0.82rem; text-decoration: none;">🔄 Reset</a>
    </form>

    @if($eateries->count() > 0)
    <div style="overflow-x: auto;">
        <table class="adm-premium-table">
            <thead>
                <tr>
                    <th>Tên Cơ Sở / Địa Điểm</th>
                    <th>Danh Mục</th>
                    <th>Xã / Thị Trấn</th>
                    <th>SĐT</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: center;">Điều Khiển</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eateries as $e)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="adm-eatery-thumb">
                                <img src="{{ $e->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=100&q=80' }}" alt="{{ $e->name }}">
                            </div>
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem;">{{ $e->name }}</div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">{{ Str::limit($e->address, 40) }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="adm-cat-badge">{{ $e->category->name ?? 'N/A' }}</span></td>
                    <td style="font-weight: 600; color: #475569;">{{ $e->commune?->name ?? 'N/A' }}</td>
                    <td style="font-weight: 600; color: #475569;">{{ $e->phone ?: '—' }}</td>
                    <td><span style="display: inline-flex; align-items: center; gap: 5px; font-weight: 700; font-size: 0.8rem; color: #059669; background: #dcfce7; padding: 5px 12px; border-radius: 20px;">🟢 Hoạt động</span></td>
                    <td style="text-align: center;">
                        <a href="/admin/eateries/{{ $e->slug }}/edit" class="adm-manage-btn">⚙️ Quản lý</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top: 20px;">{{ $eateries->links() }}</div>
    @else
    <div style="text-align: center; padding: 50px; color: #94a3b8;">
        <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
        <div style="font-weight: 700; font-size: 1.1rem;">Không tìm thấy kết quả phù hợp</div>
    </div>
    @endif
</div>

@endsection
