@extends('layouts.hkd')

@section('title', 'Chi Tiết Đơn Hàng #' . $order->id . ' — ' . $eatery->name)
@section('title_header', 'Chi Tiết & Duyệt Đơn Hàng #' . $order->id)

@section('content')

<style>
    .hkd-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; margin-bottom: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .hkd-card-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; }
    .hkd-detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px; }
    .hkd-detail-item { font-size: 0.9rem; }
    .hkd-detail-label { font-weight: 700; color: #64748b; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 4px; }
    .hkd-detail-val { font-weight: 800; color: #0f172a; }

    .hkd-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    .hkd-table th { background: #f8fafc; text-align: left; padding: 12px 16px; font-size: 0.8rem; font-weight: 800; color: #475569; border-bottom: 1px solid #e2e8f0; }
    .hkd-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; font-weight: 600; color: #334155; }
</style>

<div style="margin-bottom: 20px;">
    <a href="{{ route('hkd.orders.index') }}" style="color: #0284c7; text-decoration: none; font-weight: 700; font-size: 0.9rem;">
        ← Quay lại danh sách đơn hàng
    </a>
</div>

<!-- ĐỔI TRẠNG THÁI VÀ DUYỆT ĐƠN -->
<div class="hkd-card" style="border: 2px solid #059669; background: #ecfdf5;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 800; color: #047857; text-transform: uppercase;">Trạng Thái Đơn Hàng Hiện Tại</div>
            <div style="font-size: 1.4rem; font-weight: 900; color: #065f46; margin-top: 2px;">
                @if($order->status === 'pending') ⏳ Chờ Duyệt Trực Tuyến
                @elseif($order->status === 'processing') 🔄 Đang Xử Lý & Chuẩn Bị
                @elseif(in_array($order->status, ['completed', 'delivered'])) ✅ Đã Hoàn Thành
                @elseif($order->status === 'cancelled') ❌ Đã Hủy
                @else {{ $order->status }}
                @endif
            </div>
        </div>

        <form action="{{ route('hkd.orders.update-status', $order->id) }}" method="POST" style="display: flex; gap: 10px; align-items: center;">
            @csrf
            @method('PUT')
            <select name="status" class="hkd-form-select" style="padding: 10px 16px; background: #ffffff;">
                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Chờ duyệt</option>
                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>🔄 Đang xử lý</option>
                <option value="completed" {{ in_array($order->status, ['completed', 'delivered']) ? 'selected' : '' }}>✅ Hoàn thành</option>
                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Hủy đơn</option>
            </select>
            <button type="submit" class="hkd-btn-action hkd-btn-emerald" style="padding: 10px 20px;">
                Cập Nhật Trạng Thái
            </button>
        </form>
    </div>
</div>

<!-- THÔNG TIN KHÁCH HÀNG & GIAO DỊCH -->
<div class="hkd-card">
    <div class="hkd-card-title">
        <i class="fa-solid fa-user-tag" style="color: #0284c7;"></i>
        <span>Thông Tin Người Đặt Hàng</span>
    </div>
    <div class="hkd-detail-grid">
        <div class="hkd-detail-item">
            <div class="hkd-detail-label">Tên Khách Hàng</div>
            <div class="hkd-detail-val">{{ $order->customer_name ?? 'Khách hàng Vãng lai' }}</div>
        </div>
        <div class="hkd-detail-item">
            <div class="hkd-detail-label">Số Điện Thoại</div>
            <div class="hkd-detail-val" style="color: #0284c7;">{{ $order->customer_phone ?? 'N/A' }}</div>
        </div>
        <div class="hkd-detail-item">
            <div class="hkd-detail-label">Địa Chỉ Giao Hàng / Nhận</div>
            <div class="hkd-detail-val">{{ $order->shipping_address ?? 'Tại cơ sở' }}</div>
        </div>
        <div class="hkd-detail-item">
            <div class="hkd-detail-label">Ghi Chú Đơn Hàng</div>
            <div class="hkd-detail-val">{{ $order->notes ?? 'Không có ghi chú' }}</div>
        </div>
    </div>
</div>

<!-- DANH SÁCH MẶT HÀNG TRONG ĐƠN -->
<div class="hkd-card">
    <div class="hkd-card-title">
        <i class="fa-solid fa-cart-flatbed" style="color: #d97706;"></i>
        <span>Chi Tiết Sản Phẩm & Hàng Hóa</span>
    </div>

    @if($orderItems->isEmpty())
        <div style="padding: 20px; color: #64748b;">Tổng giá trị đơn hàng: <strong style="color: #059669; font-size: 1.2rem;">{{ number_format($order->total_amount ?? 0) }} đ</strong></div>
    @else
        <table class="hkd-table">
            <thead>
                <tr>
                    <th>Sản phẩm / Mặt hàng</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderItems as $item)
                <tr>
                    <td style="font-weight: 800; color: #0f172a;">{{ $item->product_name ?? 'Sản phẩm' }}</td>
                    <td>x{{ $item->quantity ?? 1 }}</td>
                    <td>{{ number_format($item->price ?? 0) }} đ</td>
                    <td style="font-weight: 900; color: #059669;">{{ number_format(($item->quantity ?? 1) * ($item->price ?? 0)) }} đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top: 20px; text-align: right; font-size: 1.2rem; font-weight: 900; color: #0f172a;">
            Tổng thanh toán: <span style="color: #059669;">{{ number_format($order->total_amount ?? 0) }} đ</span>
        </div>
    @endif
</div>

@endsection
