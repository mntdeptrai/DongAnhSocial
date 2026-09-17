@extends('layouts.seller')

@section('title', 'Chi tiết đơn hàng #ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT))

@section('content')

@php
    $st = strtolower($order->status ?? 'pending');
    $isConfirmed = in_array($st, ['confirmed', 'đã xác nhận']);
    $isPreparing = in_array($st, ['preparing', 'processing', 'đang chuẩn bị']);
    $isDone      = in_array($st, ['completed', 'delivered', 'hoàn thành']);
    $isCancelled = in_array($st, ['cancelled', 'rejected', 'đã từ chối', 'đã hủy']);
    $isPending   = !$isConfirmed && !$isPreparing && !$isDone && !$isCancelled;

    $itemsList = isset($order->items) ? $order->items : collect();
    $subtotal  = $itemsList->sum(function($i) { return ($i->price ?? 0) * ($i->quantity ?? 1); });
    $total = $order->total_amount ?? $subtotal;
@endphp

<style>
    .slr-detail-header {
        margin-bottom: 24px;
    }
    .slr-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #475569;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none;
        margin-bottom: 12px;
        transition: color 0.2s;
    }
    .slr-back-link:hover {
        color: #0284c7;
    }

    .slr-detail-title-row {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .slr-detail-title {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 800;
    }
    .pill-pending { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    .pill-preparing { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
    .pill-confirmed { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .pill-completed { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .pill-cancelled { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }

    .slr-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    }

    .slr-card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-group {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .info-item-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 3px;
    }
    .info-item-val {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }

    /* Timeline Stepper */
    .timeline-list {
        position: relative;
        padding-left: 28px;
        margin-top: 10px;
    }
    .timeline-list::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 22px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-dot {
        position: absolute;
        left: -28px;
        top: 2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .timeline-item.active .timeline-dot {
        border-color: #10b981;
        background: #10b981;
    }
    .timeline-item.active .timeline-dot::after {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #ffffff;
    }
    .timeline-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #334155;
    }
    .timeline-item.active .timeline-title {
        color: #0f172a;
    }
    .timeline-time {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 2px;
    }
</style>

<!-- TOP BREADCRUMB & HEADER -->
<div class="slr-detail-header">
    <a href="{{ route('seller.orders.index') }}" class="slr-back-link">
        ← Quay lại danh sách
    </a>
    <div class="slr-detail-title-row">
        <h1 class="slr-detail-title">
            Chi tiết đơn hàng #ORD{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
        </h1>

        @if($isCancelled)
            <span class="status-pill pill-cancelled">🔴 Đã hủy</span>
        @elseif($isPreparing)
            <span class="status-pill pill-preparing">🍽️ Đang chuẩn bị</span>
        @elseif($isConfirmed)
            <span class="status-pill pill-confirmed">🔵 Đã xác nhận</span>
        @elseif($isDone)
            <span class="status-pill pill-completed">🟢 Hoàn thành</span>
        @else
            <span class="status-pill pill-pending">🟠 Chờ xác nhận</span>
        @endif

        <span style="font-size: 0.88rem; color: #64748b; font-weight: 600; margin-left: auto;">
            🕒 {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y - h:i A') }}
        </span>
    </div>
</div>

@if(session('success'))
    <div style="margin-bottom: 20px; padding: 14px 20px; background: #ecfdf5; border: 1.5px solid #10b981; color: #065f46; border-radius: 12px; font-weight: 700;">
        🎉 {{ session('success') }}
    </div>
@endif

<!-- 2 COLUMN GRID LAYOUT -->
<div style="display: grid; grid-template-columns: minmax(300px, 38%) minmax(360px, 62%); gap: 24px; align-items: start;">

    <!-- LEFT COLUMN -->
    <div>
        <!-- CARD 1: THÔNG TIN KHÁCH HÀNG -->
        <div class="slr-card">
            <h3 class="slr-card-title">
                👤 Thông tin khách hàng
            </h3>
            <div class="info-group">
                <div>
                    <div class="info-item-label">Tên khách hàng</div>
                    <div class="info-item-val">{{ $order->customer_name ?? 'Khách lẻ' }}</div>
                </div>

                <div>
                    <div class="info-item-label">Số điện thoại</div>
                    <div class="info-item-val">
                        <a href="tel:{{ $order->customer_phone }}" style="color: #0284c7; text-decoration: none; font-weight: 800;">
                            📞 {{ $order->customer_phone ?? 'N/A' }}
                        </a>
                    </div>
                </div>

                <div>
                    <div class="info-item-label">Địa chỉ giao hàng</div>
                    <div class="info-item-val" style="font-size: 0.88rem; line-height: 1.4; color: #475569;">
                        📍 {{ $order->shipping_address ?? 'Tại gian hàng' }}
                    </div>
                </div>

                <div>
                    <div class="info-item-label">Đối tác vận chuyển</div>
                    <select style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-weight: 700; font-size: 0.88rem; color: #334155; outline: none; background: #ffffff;">
                        <option value="">-- Chưa chọn đối tác --</option>
                        <option value="grab">GrabExpress</option>
                        <option value="ahamove">Ahamove</option>
                        <option value="self">Tự giao hàng / Shipper sạp</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- CARD 2: THÔNG TIN THANH TOÁN -->
        <div class="slr-card">
            <h3 class="slr-card-title">
                💳 Thông tin thanh toán
            </h3>
            <div class="info-group">
                <div>
                    <div class="info-item-label">Phương thức thanh toán</div>
                    <div class="info-item-val">{{ $order->payment_method ?? 'COD' }}</div>
                </div>

                <div>
                    <div class="info-item-label">Ghi chú</div>
                    <div style="font-size: 0.88rem; color: #64748b; font-style: italic;">
                        {{ $order->notes ?? '--' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: CẬP NHẬT TRẠNG THÁI (PRIMARY ACTIONS) -->
        <div style="background: #ffffff; border-radius: 16px; border: 1.5px solid #e2e8f0; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            @if($isPending)
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" style="margin-bottom: 10px;" onsubmit="return confirm('Xác nhận nhận đơn hàng này?')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 12px; border: none; background: #0284c7; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3);">
                        Xác nhận đơn hàng
                    </button>
                </form>

                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" onsubmit="return confirm('Từ chối đơn hàng này?')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" style="width: 100%; padding: 10px; border-radius: 10px; border: 1.5px solid #fca5a5; background: #fef2f2; color: #dc2626; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        Từ chối đơn hàng
                    </button>
                </form>
            @elseif($isConfirmed)
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="preparing">
                    <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 12px; border: none; background: #7e22ce; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s;">
                        Xác nhận bắt đầu chuẩn bị hàng
                    </button>
                </form>
            @elseif($isPreparing)
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 12px; border: none; background: #15803d; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s;">
                        Xác nhận đã hoàn thành đơn hàng
                    </button>
                </form>
            @elseif($isDone)
                <div style="padding: 12px; background: #dcfce7; color: #15803d; border-radius: 10px; font-weight: 800; text-align: center; font-size: 0.9rem;">
                    ✅ Đơn hàng đã hoàn thành
                </div>
            @elseif($isCancelled)
                <div style="padding: 12px; background: #fee2e2; color: #dc2626; border-radius: 10px; font-weight: 800; text-align: center; font-size: 0.9rem;">
                    🔴 Đơn hàng đã bị hủy
                </div>
            @endif
        </div>

    </div>

    <!-- RIGHT COLUMN -->
    <div>
        <!-- CARD 1: CHI TIẾT SẢN PHẨM -->
        <div class="slr-card">
            <h3 class="slr-card-title">
                📦 Chi tiết sản phẩm
            </h3>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; margin-bottom: 20px;">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9; text-align: left; color: #64748b; font-size: 0.8rem; text-transform: uppercase;">
                        <th style="padding: 10px 0;">Tên sản phẩm</th>
                        <th style="padding: 10px; text-align: center;">Số lượng</th>
                        <th style="padding: 10px; text-align: right;">Đơn giá</th>
                        <th style="padding: 10px 0; text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($order->items) && count($order->items) > 0)
                        @foreach($order->items as $item)
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 14px 0; font-weight: 700; color: #0f172a;">
                                    {{ $item->name }}
                                </td>
                                <td style="padding: 14px; text-align: center; font-weight: 800; color: #334155;">
                                    {{ $item->quantity }}
                                </td>
                                <td style="padding: 14px; text-align: right; color: #64748b; font-weight: 600;">
                                    {{ number_format($item->price, 0, ',', '.') }} đ
                                </td>
                                <td style="padding: 14px 0; text-align: right; font-weight: 800; color: #0f172a;">
                                    {{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic;">
                                Không có chi tiết mặt hàng
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- SUMMARY TOTALS -->
            <div style="border-top: 2px solid #f1f5f9; padding-top: 14px; display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; color: #64748b;">
                    <span>Tạm tính</span>
                    <span style="font-weight: 700; color: #334155;">{{ number_format($subtotal, 0, ',', '.') }} đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; color: #64748b;">
                    <span>Phí giao hàng</span>
                    <span style="font-weight: 700; color: #334155;">0 đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; color: #64748b;">
                    <span>Giảm giá</span>
                    <span style="font-weight: 700; color: #334155;">0 đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 1.15rem; font-weight: 900; color: #0f172a;">
                    <span>Tổng cộng</span>
                    <span style="color: #0284c7;">{{ number_format($total, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>

        <!-- CARD 2: LỊCH SỬ ĐƠN HÀNG -->
        <div class="slr-card">
            <h3 class="slr-card-title">
                ⏳ Lịch sử đơn hàng
            </h3>

            <div class="timeline-list">
                <!-- Step 1: Đơn hàng đã được tạo -->
                <div class="timeline-item active">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Đơn hàng đã được tạo</div>
                    <div class="timeline-time">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y - h:i A') }}</div>
                </div>

                <!-- Step 2: Đơn hàng đã được xác nhận -->
                <div class="timeline-item {{ ($isConfirmed || $isPreparing || $isDone) ? 'active' : '' }}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Đơn hàng đã được xác nhận</div>
                    <div class="timeline-time">{{ ($isConfirmed || $isPreparing || $isDone) ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y - h:i A') : '--' }}</div>
                </div>

                <!-- Step 3: Đơn hàng đang được giao -->
                <div class="timeline-item {{ ($isPreparing || $isDone) ? 'active' : '' }}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Đơn hàng đang được chuẩn bị / giao</div>
                    <div class="timeline-time">{{ ($isPreparing || $isDone) ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y - h:i A') : '--' }}</div>
                </div>

                <!-- Step 4: Đơn hàng đã hoàn thành -->
                <div class="timeline-item {{ $isDone ? 'active' : '' }}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Đơn hàng đã hoàn thành</div>
                    <div class="timeline-time">{{ $isDone ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y - h:i A') : '--' }}</div>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection
