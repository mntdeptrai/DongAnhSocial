@extends('layouts.seller')

@section('title', 'Kênh Chủ Gian Hàng — ' . $stallName)

@section('content')

<!-- Welcome Banner -->
<div class="admin-welcome-banner" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 1.5rem; color: #ffffff;">🛒 Kênh Điều Hành {{ $stallName }}</h1>
        <p style="color: rgba(255,255,255,0.9);">Quản lý danh mục món ăn/sản phẩm bày bán, cập nhật giá niêm yết, tải ảnh sản phẩm và tiếp nhận đơn hàng tại {{ $market ? $market->name : 'Chợ Số' }}.</p>
    </div>
    <div style="font-size: 2.8rem;">🛍️</div>
</div>

<!-- Quick Stats Grid -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div>
            <div class="admin-stat-val">{{ $productsCount }}</div>
            <div class="admin-stat-lbl">MẶT HÀNG BÀY BÁN</div>
        </div>
        <span class="admin-stat-icon">📦</span>
    </div>

    <div class="admin-stat-card">
        <div>
            <div class="admin-stat-val">{{ $ordersCount }}</div>
            <div class="admin-stat-lbl">ĐƠN HÀNG PHÁT SINH</div>
        </div>
        <span class="admin-stat-icon">🛍️</span>
    </div>

    <div class="admin-stat-card">
        <div>
            <div class="admin-stat-val">100%</div>
            <div class="admin-stat-lbl">THANH TOÁN VIETQR</div>
        </div>
        <span class="admin-stat-icon">📲</span>
    </div>

    <div class="admin-stat-card">
        <div>
            <div class="admin-stat-val">⭐ 5.0</div>
            <div class="admin-stat-lbl">ĐÁNH GIÁ TRUNG BÌNH</div>
        </div>
        <span class="admin-stat-icon">✨</span>
    </div>
</div>

<!-- Quick Action Panel -->
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="admin-card-header">
        <h2 class="admin-card-title"><span>⚡</span> Thao Tác Nhanh Cho Chủ Gian Hàng</h2>
    </div>
    <div style="display: flex; gap: 16px; flex-wrap: wrap; padding: 10px 0;">
        <a href="{{ route('seller.products.index') }}" class="btn-admin btn-admin-primary" style="padding: 12px 20px; text-decoration: none; border-radius: 12px; display: inline-flex; align-items: center; gap: 8px;">
            <span>➕</span> Quản Lý & Đổi Giá Sản Phẩm
        </a>
        <a href="{{ route('seller.orders.index') }}" class="btn-admin" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9; padding: 12px 20px; text-decoration: none; border-radius: 12px; display: inline-flex; align-items: center; gap: 8px;">
            <span>📦</span> Xem Danh Sách Đơn Hàng
        </a>
        <a href="/dia-diem/{{ $market ? $market->slug : 'cho-to' }}" target="_blank" class="btn-admin" style="background: rgba(0,0,0,0.05); color: var(--text-main); padding: 12px 20px; text-decoration: none; border-radius: 12px; display: inline-flex; align-items: center; gap: 8px;">
            <span>👁️</span> Xem Gian Hàng Trên Bản Đồ
        </a>
    </div>
</div>

<!-- Products Table Preview -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title"><span>📦</span> Danh Sách Mặt Hàng Đang Bày Bán</h2>
        <a href="{{ route('seller.products.index') }}" class="btn-admin btn-admin-primary" style="font-size: 0.82rem; padding: 6px 14px; text-decoration: none;">+ Thêm món mới</a>
    </div>
    
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Hình Ảnh</th>
                    <th>Tên Sản Phẩm / Món Ăn</th>
                    <th>Giá Niêm Yết</th>
                    <th>Mô Tả / Nguồn Gốc</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $idx => $p)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <img src="{{ $p->image_path ?: '/images/stalls/food.png' }}" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                    </td>
                    <td style="font-weight: 700; color: var(--text-main);">{{ $p->name }}</td>
                    <td style="color: #0ea5e9; font-weight: 800;">{{ number_format($p->price, 0, ',', '.') }}đ</td>
                    <td style="font-size: 0.82rem; color: var(--text-muted);">{{ Str::limit($p->description, 50) }}</td>
                    <td>
                        <a href="{{ route('seller.products.index') }}" class="btn-admin" style="padding: 4px 8px; font-size: 0.75rem; text-decoration: none; background: rgba(14,165,233,0.1); color: #0ea5e9;">Sửa / Đổi Giá</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Chưa có sản phẩm nào. Hãy bấm <strong>+ Thêm món mới</strong> để đăng bán sản phẩm đầu tiên!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
