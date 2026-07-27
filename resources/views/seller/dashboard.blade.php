@extends('layouts.seller')

@section('title', 'Kênh Điều Hành Gian Hàng — ' . $stallName)

@section('content')

@php
    $totalRevenue = $totalRevenue ?? 0;
    $ordersCount = $ordersCount ?? 0;
    $productsCount = $productsCount ?? 0;
@endphp

@if(session('success'))
    <div style="padding: 14px 20px; background: #ecfdf5; border: 1.5px solid #10b981; color: #065f46; border-radius: 12px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <span>🎉</span> {{ session('success') }}
    </div>
@endif

<style>
.slr-merchant-header {
    background: linear-gradient(135deg, #c2410c 0%, #ea580c 50%, #d97706 100%);
    border-radius: 22px;
    padding: 28px 32px;
    color: #ffffff;
    box-shadow: 0 18px 36px -10px rgba(234, 88, 12, 0.35);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.slr-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 30px;
    color: #fef08a;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.slr-pos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.slr-pos-card {
    background: #ffffff;
    border: 1.5px solid #ffedd5;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 8px 20px -4px rgba(234, 88, 12, 0.08);
    transition: all 0.25s ease;
}

.slr-action-bar {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 28px;
}

.slr-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 22px;
    border-radius: 14px;
    font-weight: 800;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.25s ease;
}
</style>

<!-- SELLER HERO BANNER -->
<div class="slr-merchant-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <div class="slr-badge">
                <span>🏪</span> Kênh Gian Hàng & Smart POS
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -0.02em; color: #ffffff;">
                {{ $stallName }}
            </h1>
            <p style="margin: 0; color: #ffedd5; font-size: 0.94rem;">
                📍 Trực thuộc: <strong>{{ $market ? $market->name : 'Chợ Truyền Thống Số' }}</strong> | Chủ gian: <strong>{{ $sellerName }}</strong> (📞 {{ $sellerPhone }})
            </p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('seller.products.index') }}" class="btn-admin" style="background: #ffffff; color: #c2410c; border: none; padding: 12px 22px; border-radius: 12px; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                ➕ Đổi Giá & Đăng Món
            </a>
            <a href="{{ route('seller.orders.index') }}" class="btn-admin" style="background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.35); padding: 12px 22px; border-radius: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                📦 Xử Lý Đơn Hàng
            </a>
        </div>
    </div>
</div>

<!-- SELLER REAL-TIME POS STATS -->
<div class="slr-pos-grid">
    <div class="slr-pos-card">
        <div style="font-size: 0.75rem; font-weight: 800; color: #9a3412; text-transform: uppercase;">DOANH THU GIAN HÀNG</div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #c2410c; margin: 4px 0;">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
        <div style="font-size: 0.78rem; color: #ea580c; font-weight: 700;">💰 Doanh thu thực tế tích lũy</div>
    </div>

    <div class="slr-pos-card">
        <div style="font-size: 0.75rem; font-weight: 800; color: #9a3412; text-transform: uppercase;">MẶT HÀNG ĐANG BÀY BÁN</div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #0284c7; margin: 4px 0;">{{ $productsCount }} món</div>
        <div style="font-size: 0.78rem; color: #0284c7; font-weight: 700;">📦 Sản phẩm / Thực đơn sẵn sàng</div>
    </div>

    <div class="slr-pos-card">
        <div style="font-size: 0.75rem; font-weight: 800; color: #9a3412; text-transform: uppercase;">ĐƠN HÀNG ĐÃ NHẬN</div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #059669; margin: 4px 0;">{{ $ordersCount }} đơn</div>
        <div style="font-size: 0.78rem; color: #059669; font-weight: 700;">🧾 Giao dịch phát sinh</div>
    </div>

    <div class="slr-pos-card">
        <div style="font-size: 0.75rem; font-weight: 800; color: #9a3412; text-transform: uppercase;">THANH TOÁN VIETQR</div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #7c3aed; margin: 4px 0;">100%</div>
        <div style="font-size: 0.78rem; color: #7c3aed; font-weight: 700;">📲 Chuyển khoản QR ngân hàng</div>
    </div>
</div>

<!-- PRODUCTS TABLE PREVIEW -->
<div class="admin-card">
    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="admin-card-title"><span>📦</span> Danh Sách Sản Phẩm / Món Ăn Bày Bán</h2>
        <a href="{{ route('seller.products.index') }}" class="btn-admin" style="background: #ea580c; color: #fff; font-size: 0.82rem; padding: 8px 16px; border-radius: 10px; text-decoration: none; font-weight: 800;">
            + Thêm Món Mới
        </a>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">STT</th>
                    <th>Hình Ảnh</th>
                    <th>Tên Sản Phẩm / Món Ăn</th>
                    <th>Giá Niêm Yết</th>
                    <th>Mô Tả / Nguồn Gốc</th>
                    <th style="text-align: center;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $idx => $p)
                <tr>
                    <td><strong>#{{ $idx + 1 }}</strong></td>
                    <td>
                        <div style="width: 48px; height: 48px; border-radius: 10px; overflow: hidden; background: #fff7ed; border: 1px solid #ffedd5;">
                            <img src="{{ $p->image_path ?: '/images/stalls/food.png' }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $p->name }}">
                        </div>
                    </td>
                    <td style="font-weight: 800; color: #1e293b; font-size: 0.94rem;">{{ $p->name }}</td>
                    <td style="color: #ea580c; font-weight: 900; font-size: 1rem;">
                        {{ number_format($p->price, 0, ',', '.') }}đ / {{ $p->unit ?? 'kg' }}
                    </td>
                    <td style="font-size: 0.82rem; color: #64748b;">{{ Str::limit($p->description, 50) }}</td>
                    <td style="text-align: center;">
                        <a href="{{ route('seller.products.index') }}" class="btn-admin" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; font-weight: 800;">
                            ⚙️ Chỉnh sửa / Đổi giá
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 36px;">
                        Chưa có sản phẩm nào trong gian hàng. Hãy bấm <strong>+ Thêm món mới</strong> để bắt đầu đăng bán!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
