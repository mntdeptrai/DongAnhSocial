@extends('layouts.hkd')

@section('title', 'Trang Tổng Quan & Chỉ Số HT10 — ' . $eatery->name)
@section('title_header', 'Kênh Điều Hành HKD & Doanh Nghiệp — Tổng Quan')

@section('content')

<style>
    .hkd-hero {
        background: linear-gradient(135deg, #065f46 0%, #047857 50%, #0284c7 100%);
        border-radius: 20px;
        padding: 28px 32px;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(4, 120, 87, 0.3);
        margin-bottom: 28px;
    }
    .hkd-hero-tag {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px; background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 20px; font-size: 0.75rem; font-weight: 700; color: #a7f3d0;
        text-transform: uppercase; margin-bottom: 10px;
    }
    .hkd-hero-title { font-size: 1.8rem; font-weight: 900; margin-bottom: 8px; }
    .hkd-hero-sub { font-size: 0.95rem; color: #e2e8f0; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }

    .hkd-stat-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px;
    }
    .hkd-stat-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.2s, box-shadow 0.2s;
    }
    .hkd-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
    .hkd-stat-label { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; }
    .hkd-stat-value { font-size: 1.6rem; font-weight: 900; color: #0f172a; }

    .hkd-progress-bar {
        height: 10px; background: #e2e8f0; border-radius: 6px; overflow: hidden; margin-top: 10px;
    }
    .hkd-progress-fill {
        height: 100%; background: linear-gradient(90deg, #10b981 0%, #0284c7 100%); border-radius: 6px;
    }

    .hkd-section-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 28px;
    }
    .hkd-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .hkd-section-title { font-size: 1.1rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }

    .hkd-table { width: 100%; border-collapse: collapse; }
    .hkd-table th { background: #f8fafc; text-align: left; padding: 12px 16px; font-size: 0.8rem; font-weight: 800; color: #475569; border-bottom: 1px solid #e2e8f0; }
    .hkd-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; font-weight: 600; color: #334155; }

    .hkd-btn-action {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px; border-radius: 10px; font-size: 0.85rem; font-weight: 700;
        text-decoration: none; border: none; cursor: pointer; transition: all 0.2s;
    }
    .hkd-btn-emerald { background: #059669; color: #ffffff; }
    .hkd-btn-emerald:hover { background: #047857; }
    .hkd-btn-sky { background: #0284c7; color: #ffffff; }
    .hkd-btn-sky:hover { background: #0369a1; }
</style>

<!-- HERO HEADER BANNER -->
<div class="hkd-hero">
    <div class="hkd-hero-tag">
        <i class="fa-solid fa-square-poll-vertical"></i> Cổng Chuyển Đổi Số Cấp Cơ Sở (HT10)
    </div>
    <div class="hkd-hero-title">{{ $eatery->name }}</div>
    <div class="hkd-hero-sub">
        <span><i class="fa-solid fa-id-card"></i> MST: {{ $storyData['mst'] ?? 'Đang cập nhật' }}</span>
        <span><i class="fa-solid fa-location-dot"></i> {{ $eatery->address }}</span>
        <span><i class="fa-solid fa-phone"></i> {{ $eatery->phone }}</span>
    </div>
    <div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('hkd.profile') }}" class="hkd-btn-action hkd-btn-emerald">
            <i class="fa-solid fa-pen-to-square"></i> Hồ Sơ Cơ Sở & Bản Đồ
        </a>
        <a href="{{ route('hkd.products.index') }}" class="hkd-btn-action hkd-btn-sky">
            <i class="fa-solid fa-plus"></i> Đăng Mặt Hàng Kinh Doanh
        </a>
        <a href="{{ route('hkd.qr') }}" class="hkd-btn-action" style="background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.4);">
            <i class="fa-solid fa-qrcode"></i> Cấu Hình VietQR Ngân Hàng
        </a>
    </div>
</div>

<!-- STATS CARDS -->
<div class="hkd-stat-grid">
    <div class="hkd-stat-card">
        <div class="hkd-stat-label"><i class="fa-solid fa-coins" style="color: #059669;"></i> Doanh Thu Thực Tế</div>
        <div class="hkd-stat-value" style="color: #059669;">{{ number_format($totalRevenue) }} đ</div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Tích lũy qua đơn hàng trực tuyến</div>
    </div>
    <div class="hkd-stat-card">
        <div class="hkd-stat-label"><i class="fa-solid fa-box" style="color: #0284c7;"></i> Sản Phẩm Kinh Doanh</div>
        <div class="hkd-stat-value">{{ $productsCount }} <span style="font-size: 0.9rem; font-weight: 600;">mặt hàng</span></div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Đang bày bán công khai</div>
    </div>
    <div class="hkd-stat-card">
        <div class="hkd-stat-label"><i class="fa-solid fa-cart-shopping" style="color: #d97706;"></i> Đơn Hàng Đã Nhận</div>
        <div class="hkd-stat-value">{{ $ordersCount }} <span style="font-size: 0.9rem; font-weight: 600;">đơn</span></div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Giao dịch phát sinh</div>
    </div>
    <div class="hkd-stat-card">
        <div class="hkd-stat-label"><i class="fa-solid fa-circle-check" style="color: #6366f1;"></i> Chỉ Số Số Hóa HT10</div>
        <div class="hkd-stat-value" style="color: #6366f1;">{{ $ht10Score }}%</div>
        <div class="hkd-progress-bar">
            <div class="hkd-progress-fill" style="width: {{ $ht10Score }}%;"></div>
        </div>
    </div>
</div>

<!-- RECENT ORDERS SECTION -->
<div class="hkd-section-card">
    <div class="hkd-section-header">
        <div class="hkd-section-title">
            <i class="fa-solid fa-clock-rotate-left" style="color: #059669;"></i>
            <span>Đơn Hàng Trực Tuyến Mới Nhận</span>
        </div>
        <a href="{{ route('hkd.orders.index') }}" style="color: #0284c7; text-decoration: none; font-weight: 700; font-size: 0.85rem;">
            Xem tất cả đơn hàng →
        </a>
    </div>

    @if($recentOrders->isEmpty())
        <div style="text-align: center; padding: 40px; color: #94a3b8;">
            <i class="fa-solid fa-basket-shopping" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
            <p style="font-weight: 600;">Chưa có đơn hàng trực tuyến nào phát sinh.</p>
        </div>
    @else
        <table class="hkd-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $ord)
                <tr>
                    <td>#{{ $ord->id }}</td>
                    <td>{{ $ord->customer_name ?? 'Khách hàng' }}</td>
                    <td>{{ $ord->customer_phone ?? 'N/A' }}</td>
                    <td style="font-weight: 800; color: #059669;">{{ number_format($ord->total_amount ?? 0) }} đ</td>
                    <td>
                        @if($ord->status === 'pending')
                            <span style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;">Chờ duyệt</span>
                        @elseif(in_array($ord->status, ['completed', 'delivered']))
                            <span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;">Đã hoàn thành</span>
                        @else
                            <span style="background: #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;">{{ $ord->status }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('hkd.orders.show', $ord->id) }}" style="color: #0284c7; font-weight: 700; text-decoration: none;">Chi tiết</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- PRODUCTS OVERVIEW SECTION -->
<div class="hkd-section-card">
    <div class="hkd-section-header">
        <div class="hkd-section-title">
            <i class="fa-solid fa-boxes-stacked" style="color: #0284c7;"></i>
            <span>Sản Phẩm & Hàng Hóa Đang Bày Bán</span>
        </div>
        <a href="{{ route('hkd.products.index') }}" class="hkd-btn-action hkd-btn-sky" style="font-size: 0.8rem; padding: 6px 14px;">
            <i class="fa-solid fa-plus"></i> Quản Lý Danh Mục
        </a>
    </div>

    @if($products->isEmpty())
        <div style="text-align: center; padding: 40px; color: #94a3b8;">
            <i class="fa-solid fa-box-open" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
            <p style="font-weight: 600;">Chưa có sản phẩm / hàng hóa nào được niêm yết.</p>
            <p style="font-size: 0.85rem; margin-top: 4px;">Hãy bấm nút <strong>+ Quản Lý Danh Mục</strong> để đăng mặt hàng mới lên trang công khai.</p>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
            @foreach($products as $p)
            <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; background: #ffffff;">
                <div style="height: 120px; background: #f8fafc; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 10px;">
                    @if($p->image)
                        <img src="{{ $p->image }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="fa-solid fa-box" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                    @endif
                </div>
                <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $p->name }}</div>
                <div style="color: #059669; font-weight: 900; font-size: 0.95rem;">{{ number_format($p->price) }} đ</div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
