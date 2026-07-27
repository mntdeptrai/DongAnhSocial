@extends('layouts.admin')

@section('title', 'Kênh Điều Hành Ban Quản Lý Chợ Truyền Thống Số')

@section('content')

@php
    $managedMarket = $marketStats['managed_market'] ?? null;
    $stallsCount = $marketStats['stalls_count'] ?? 0;
    $totalOrders = $marketStats['total_orders'] ?? 0;
    $totalRevenue = $marketStats['total_revenue'] ?? 0;
    $stallBreakdown = $marketStats['stall_breakdown'] ?? collect();

    $announcements = [];
    if ($managedMarket && !empty($managedMarket->announcements)) {
        $announcements = json_decode($managedMarket->announcements, true) ?: [];
    }
@endphp

<style>
/* Dedicated Manager Market Control Tower Styling */
.mgr-market-header {
    background: linear-gradient(135deg, #064e3b 0%, #047857 60%, #059669 100%);
    border: 1px solid rgba(52, 211, 153, 0.3);
    border-radius: 22px;
    padding: 30px 34px;
    color: #ffffff;
    box-shadow: 0 20px 40px -15px rgba(4, 120, 87, 0.4);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.mgr-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 30px;
    color: #a7f3d0;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.mgr-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.mgr-stat-card {
    background: #ffffff;
    border: 1.5px solid #d1fae5;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.08);
    transition: all 0.25s ease;
}

.mgr-stat-card:hover {
    transform: translateY(-4px);
    border-color: #10b981;
    box-shadow: 0 15px 30px -8px rgba(5, 150, 105, 0.2);
}

.mgr-announcement-box {
    background: #ffffff;
    border: 1.5px solid #a7f3d0;
    border-radius: 20px;
    padding: 26px;
    margin-bottom: 28px;
    box-shadow: 0 10px 30px rgba(5, 150, 105, 0.06);
}

.mgr-announcement-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    margin-bottom: 10px;
}
</style>

<!-- MANAGER HERO HEADER -->
<div class="mgr-market-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <div class="mgr-badge">
                <span>🏛️</span> Ban Quản Lý Chợ Truyền Thống Số
            </div>
            <h1 style="font-size: 1.9rem; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -0.02em; color: #ffffff;">
                {{ $managedMarket ? $managedMarket->name : 'Chợ Truyền Thống Đông Anh' }}
            </h1>
            <p style="margin: 0; color: #a7f3d0; font-size: 0.95rem;">
                📍 {{ $managedMarket ? $managedMarket->address : 'Trung tâm điều hành bản tin số, gian hàng & tiểu thương Chợ' }}
            </p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="/admin/users" class="btn-admin" style="background: #f59e0b; color: #fff; border: none; padding: 12px 22px; border-radius: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);">
                👥 Quản Lý Tiểu Thương Chợ
            </a>
            @if($managedMarket)
            <a href="/admin/eateries/{{ $managedMarket->slug }}/edit" class="btn-admin" style="background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.3); padding: 12px 22px; border-radius: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                ⚙️ Hồ Sơ Chợ & Bản Đồ
            </a>
            @endif
        </div>
    </div>
</div>

<!-- MANAGER KEY METRICS -->
<div class="mgr-stats-grid">
    <div class="mgr-stat-card">
        <div style="font-size: 0.75rem; font-weight: 800; color: #047857; text-transform: uppercase;">GIAN HÀNG TRONG CHỢ</div>
        <div style="font-size: 2.3rem; font-weight: 900; color: #064e3b; margin: 4px 0;">{{ $stallsCount }}</div>
        <div style="font-size: 0.78rem; color: #059669; font-weight: 700;">🏪 Tiểu thương đang kinh doanh</div>
    </div>

    <div class="mgr-stat-card">
        <div style="font-size: 0.75rem; font-weight: 800; color: #047857; text-transform: uppercase;">ĐƠN HÀNG PHÁT SINH</div>
        <div style="font-size: 2.3rem; font-weight: 900; color: #0284c7; margin: 4px 0;">{{ $totalOrders }}</div>
        <div style="font-size: 0.78rem; color: #0284c7; font-weight: 700;">📦 Tổng giao dịch giao dịch tại Chợ</div>
    </div>

    <div class="mgr-stat-card">
        <div style="font-size: 0.75rem; font-weight: 800; color: #047857; text-transform: uppercase;">TỔNG DOANH THU CHỢ</div>
        <div style="font-size: 2.3rem; font-weight: 900; color: #d97706; margin: 4px 0;">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
        <div style="font-size: 0.78rem; color: #d97706; font-weight: 700;">💰 Doanh thu các gian hàng hội tụ</div>
    </div>

    <div class="mgr-stat-card">
        <div style="font-size: 0.75rem; font-weight: 800; color: #047857; text-transform: uppercase;">BẢN TIN SỐ ĐÃ PHÁT</div>
        <div style="font-size: 2.3rem; font-weight: 900; color: #7c3aed; margin: 4px 0;">{{ count($announcements) }}</div>
        <div style="font-size: 0.78rem; color: #7c3aed; font-weight: 700;">📢 Loa Chợ Số & Bảng tin BQL</div>
    </div>
</div>

<!-- DIGITAL LOUDSPEAKER & ANNOUNCEMENT BROADCASTING WIDGET -->
@if($managedMarket)
<div class="mgr-announcement-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 1.3rem; font-weight: 800; color: #064e3b; margin: 0;">
                📢 Phát Thông Báo Qua Loa Chợ Số & Bảng Tin BQL
            </h2>
            <div style="font-size: 0.85rem; color: #059669; margin-top: 4px;">
                Phát tin nhắn thông báo kiểm định an toàn, giờ đóng/mở cửa chợ hoặc sự kiện cho bà con tiểu thương.
            </div>
        </div>
    </div>

    <!-- FORM DĂNG BẢN TIN SỐ -->
    <form action="{{ route('admin.announcements.store', $managedMarket->id) }}" method="POST" style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 20px; border-radius: 16px; margin-bottom: 24px;">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 12px; margin-bottom: 12px;">
            <div>
                <label style="font-size: 0.78rem; font-weight: 800; color: #064e3b;">Thẻ Phân Loại</label>
                <input type="text" name="tag" class="admin-form-input" placeholder="VD: THÔNG BÁO BQL" required style="background: #fff;">
            </div>
            <div>
                <label style="font-size: 0.78rem; font-weight: 800; color: #064e3b;">Tiêu Đề Bản Tin</label>
                <input type="text" name="title" class="admin-form-input" placeholder="VD: Kiểm tra ATTP định kỳ Chợ truyền thống" required style="background: #fff;">
            </div>
            <div>
                <label style="font-size: 0.78rem; font-weight: 800; color: #064e3b;">Thời Gian Hiển Thị</label>
                <input type="text" name="time" class="admin-form-input" placeholder="VD: 07:00 Sáng nay" style="background: #fff;">
            </div>
        </div>
        <div style="margin-bottom: 14px;">
            <label style="font-size: 0.78rem; font-weight: 800; color: #064e3b;">Nội Dung Chi Tiết</label>
            <textarea name="content" class="admin-form-input" rows="2" placeholder="Nhập nội dung phát thanh cho bà con tiểu thương..." required style="background: #fff;"></textarea>
        </div>
        <button type="submit" class="btn-admin" style="background: #059669; color: #fff; border: none; padding: 10px 24px; border-radius: 10px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
            🎙️ Phát Bản Tin Loa Chợ Số
        </button>
    </form>

    <!-- LIST ANNOUNCEMENTS -->
    <h3 style="font-size: 1rem; font-weight: 800; color: #064e3b; margin-bottom: 14px;">
        📋 Danh Sách Bản Tin Đã Phát Trực Tuyến
    </h3>
    @if(count($announcements) > 0)
        @foreach($announcements as $anc)
        <div class="mgr-announcement-item">
            <div>
                <span style="font-size: 0.72rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; background: #059669; color: #fff;">
                    {{ $anc['tag'] ?? 'BẢN TIN' }}
                </span>
                <strong style="margin-left: 8px; color: #064e3b; font-size: 0.95rem;">{{ $anc['title'] ?? '' }}</strong>
                <div style="font-size: 0.85rem; color: #374151; margin-top: 4px;">{{ $anc['content'] ?? '' }}</div>
                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">⏱️ {{ $anc['time'] ?? '' }} ({{ $anc['created_at'] ?? '' }})</div>
            </div>
            <form action="{{ route('admin.announcements.destroy', [$managedMarket->id, $anc['id'] ?? 0]) }}" method="POST" onsubmit="return confirm('Bạn muốn xóa bản tin này?')">
                @csrf
                @method('DELETE')
                <button type="submit" style="background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.78rem; cursor: pointer;">
                    🗑️ Xóa
                </button>
            </form>
        </div>
        @endforeach
    @else
        <div style="color: #6b7280; font-style: italic; font-size: 0.88rem;">Chưa có bản tin số nào được phát.</div>
    @endif
</div>
@endif

<!-- STALL REVENUE LEADERBOARD -->
<div class="admin-card">
    <h2 class="admin-card-title" style="margin-bottom: 20px;">
        <span>🏪</span> Báo Cáo Doanh Thu & Đơn Hàng Theo Gian Hàng
    </h2>

    @if($stallBreakdown->count() > 0)
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tên Gian Hàng / Tiểu Thương</th>
                    <th style="text-align: center;">Số Đơn Hàng</th>
                    <th style="text-align: right;">Tổng Doanh Thu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stallBreakdown as $stall)
                <tr>
                    <td><strong>🏪 {{ $stall['stall_name'] }}</strong></td>
                    <td style="text-align: center;">
                        <span style="font-weight: 800; color: #0284c7; padding: 4px 10px; background: #e0f2fe; border-radius: 12px; font-size: 0.8rem;">
                            {{ $stall['orders_count'] }} đơn
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: 900; color: #d97706; font-size: 1.05rem;">
                        {{ number_format($stall['revenue'], 0, ',', '.') }}đ
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 30px; color: #6b7280;">
        Chưa phát sinh dữ liệu đơn hàng cho từng gian hàng.
    </div>
    @endif
</div>

@endsection
