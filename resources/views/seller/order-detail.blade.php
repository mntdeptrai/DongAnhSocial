@extends('layouts.seller')

@section('title', 'Chi tiết đơn hàng #ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT))

@section('content')

@php
    $st = strtolower($order->status ?? 'pending');
    $isPickup    = str_contains($order->shipping_address ?? '', '[Ghé sạp lấy đồ]');
    $isConfirmed = in_array($st, ['confirmed', 'đã xác nhận']);
    $isPreparing = in_array($st, ['preparing', 'processing', 'đang chuẩn bị']);
    $isReady     = in_array($st, ['ready', 'sẵn sàng', 'chờ lấy']);
    $isShipping  = in_array($st, ['shipping', 'delivering', 'đang giao']);
    $isDone      = in_array($st, ['completed', 'delivered', 'hoàn thành']);
    $isCancelled = in_array($st, ['cancelled', 'rejected', 'đã từ chối', 'đã hủy']);
    $isPending   = !$isConfirmed && !$isPreparing && !$isReady && !$isShipping && !$isDone && !$isCancelled;

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
    .pill-ready { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
    .pill-shipping { background: #e0f2fe; color: #0284c7; border: 1px solid #7dd3fc; }
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
        @elseif($isDone)
            <span class="status-pill pill-completed">🟢 Hoàn thành</span>
        @elseif($isShipping)
            <span class="status-pill pill-shipping">🚚 Đang giao hàng</span>
        @elseif($isReady)
            <span class="status-pill pill-ready">🏪 Đã chuẩn bị xong (Đợi lấy)</span>
        @elseif($isPreparing)
            <span class="status-pill pill-preparing">🍽️ Đang chuẩn bị</span>
        @elseif($isConfirmed)
            <span class="status-pill pill-confirmed">🔵 Đã xác nhận</span>
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
                    <div class="info-item-label">Hình thức nhận hàng</div>
                    <div class="info-item-val" style="font-size: 0.88rem; line-height: 1.4; color: {{ $isPickup ? '#059669' : '#0284c7' }}; font-weight: 800;">
                        {{ $isPickup ? '🏪 Ghé sạp lấy đồ (Chợ số)' : '🛵 Giao hàng tận nơi (Ship)' }}
                    </div>
                </div>

                <div>
                    <div class="info-item-label">Địa chỉ giao nhận</div>
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
                        ✅ Xác nhận đơn hàng
                    </button>
                </form>
            @elseif($isConfirmed)
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" style="margin-bottom: 10px;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="preparing">
                    <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 12px; border: none; background: #7e22ce; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(126, 34, 206, 0.25);">
                        👨‍🍳 Xác nhận bắt đầu chuẩn bị hàng
                    </button>
                </form>
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" style="margin-bottom: 10px;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" style="width: 100%; padding: 10px; border-radius: 10px; border: 1.5px solid #bbf7d0; background: #f0fdf4; color: #166534; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        🎉 Hoàn thành đơn ngay
                    </button>
                </form>
            @elseif($isPreparing)
                @if($isPickup)
                    <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" style="margin-bottom: 10px;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 12px; border: none; background: #0284c7; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3);">
                            🏪 Hàng đã chuẩn bị xong (Đợi khách lấy)
                        </button>
                    </form>
                @else
                    <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" style="margin-bottom: 10px;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="shipping">
                        <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 12px; border: none; background: #0284c7; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3);">
                            🚚 Đã bàn giao cho Shipper (Đang giao hàng)
                        </button>
                    </form>
                @endif
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" style="margin-bottom: 10px;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" style="width: 100%; padding: 10px; border-radius: 10px; border: 1.5px solid #bbf7d0; background: #f0fdf4; color: #166534; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        🎉 Xác nhận hoàn thành đơn hàng
                    </button>
                </form>
            @elseif($isReady)
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" style="margin-bottom: 10px;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 12px; border: none; background: #15803d; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(21, 128, 61, 0.3);">
                        🎉 Xác nhận khách đã đến sạp nhận đồ (Hoàn thành)
                    </button>
                </form>
            @elseif($isShipping)
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" style="margin-bottom: 10px;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 12px; border: none; background: #15803d; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(21, 128, 61, 0.3);">
                        🎉 Xác nhận đã giao hàng thành công (Hoàn thành)
                    </button>
                </form>
            @elseif($isDone)
                <div style="padding: 12px; background: #dcfce7; color: #15803d; border-radius: 10px; font-weight: 800; text-align: center; font-size: 0.9rem;">
                    ✅ Đơn hàng đã hoàn thành
                </div>
            @elseif($isCancelled)
                <div style="padding: 14px; background: #fef2f2; color: #dc2626; border: 1.5px solid #fca5a5; border-radius: 12px; font-weight: 800; text-align: center; font-size: 0.9rem;">
                    🔴 Đơn hàng đã bị hủy ({{ ($order->cancelled_by ?? '') === 'seller' ? 'Chủ gian hàng từ chối' : (( $order->cancelled_by ?? '') === 'customer' ? 'Khách hàng hủy' : 'Đã hủy') }})
                    @if(!empty($order->cancel_reason))
                        <div style="font-size: 0.8rem; font-weight: 600; color: #991b1b; margin-top: 4px;">
                            Lý do: "{{ $order->cancel_reason }}"
                        </div>
                    @endif
                </div>
            @endif

            @if(!$isDone && !$isCancelled)
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST" id="form-cancel-order" style="margin-top: 10px;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <input type="hidden" name="cancel_reason" id="seller-cancel-reason" value="">
                    <button type="button" onclick="handleSellerCancelOrder()" style="width: 100%; padding: 10px; border-radius: 10px; border: 1.5px solid #fca5a5; background: #fef2f2; color: #dc2626; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        ❌ Từ chối / Hủy đơn hàng
                    </button>
                </form>
            @endif
        </div>

    </div>

    <script>
    function handleSellerCancelOrder() {
        const defaultReason = 'Sạp hết mặt hàng này / Tạm ngưng phục vụ';
        const reason = prompt('Vui lòng nhập lý do từ chối / hủy đơn hàng này:', defaultReason);
        if (reason === null) return;
        document.getElementById('seller-cancel-reason').value = reason.trim() || defaultReason;
        document.getElementById('form-cancel-order').submit();
    }
    </script>

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
                @if($isCancelled)
                    <div class="timeline-item active">
                        <div class="timeline-dot" style="background: #dc2626; border-color: #fca5a5; box-shadow: 0 0 10px rgba(220,38,38,0.4);"></div>
                        <div class="timeline-title" style="color: #dc2626; font-weight: 800;">
                            Đơn hàng đã bị hủy ({{ ($order->cancelled_by ?? '') === 'seller' ? 'Chủ gian hàng từ chối' : (($order->cancelled_by ?? '') === 'customer' ? 'Khách hàng hủy' : 'Hệ thống') }})
                        </div>
                        <div class="timeline-time" style="color: #b91c1c; font-weight: 700;">
                            {{ $order->cancelled_at ? \Carbon\Carbon::parse($order->cancelled_at)->format('d/m/Y - h:i A') : \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y - h:i A') }}
                        </div>
                    </div>
                @endif

                <!-- Step 1: Đơn hàng đã được tạo -->
                <div class="timeline-item active">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Đơn hàng đã được tạo</div>
                    <div class="timeline-time">{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y - h:i A') : '--' }}</div>
                </div>

                <!-- Step 2: Đơn hàng đã được xác nhận -->
                @php
                    $hasConfirmed = $isConfirmed || $isPreparing || $isReady || $isShipping || $isDone;
                    $timeConfirmed = $order->confirmed_at ? \Carbon\Carbon::parse($order->confirmed_at)->format('d/m/Y - h:i A') : ($hasConfirmed ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y - h:i A') : '--');
                @endphp
                <div class="timeline-item {{ $hasConfirmed ? 'active' : '' }}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Đơn hàng đã được xác nhận</div>
                    <div class="timeline-time">{{ $timeConfirmed }}</div>
                </div>

                <!-- Step 3: Đơn hàng đang chuẩn bị -->
                @php
                    $hasPreparing = $isPreparing || $isReady || $isShipping || $isDone;
                    $timePreparing = $order->preparing_at ? \Carbon\Carbon::parse($order->preparing_at)->format('d/m/Y - h:i A') : ($hasPreparing ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y - h:i A') : '--');
                @endphp
                <div class="timeline-item {{ $hasPreparing ? 'active' : '' }}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Đơn hàng đang chuẩn bị món</div>
                    <div class="timeline-time">{{ $timePreparing }}</div>
                </div>

                <!-- Step 4: Sẵn sàng tại sạp / Đang giao -->
                @php
                    $hasReady = $isReady || $isShipping || $isDone;
                    $targetReadyDate = $isPickup ? ($order->ready_at ?? null) : ($order->shipping_at ?? null);
                    $timeReady = $targetReadyDate 
                        ? \Carbon\Carbon::parse($targetReadyDate)->format('d/m/Y - h:i A') 
                        : ($hasReady ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y - h:i A') : '--');
                @endphp
                <div class="timeline-item {{ $hasReady ? 'active' : '' }}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">{{ $isPickup ? 'Đã chuẩn bị xong (Đợi lấy)' : 'Đã bàn giao (Đang giao hàng)' }}</div>
                    <div class="timeline-time">{{ $timeReady }}</div>
                </div>

                <!-- Step 5: Đơn hàng đã hoàn thành -->
                @php
                    $timeCompleted = $order->completed_at ? \Carbon\Carbon::parse($order->completed_at)->format('d/m/Y - h:i A') : ($isDone ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y - h:i A') : '--');
                @endphp
                <div class="timeline-item {{ $isDone ? 'active' : '' }}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Đơn hàng đã hoàn thành</div>
                    <div class="timeline-time">{{ $timeCompleted }}</div>
                </div>
            </div>
        </div>

    </div>

<!-- SELLER CUSTOM CANCEL MODAL -->
<div id="sellerCancelModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.8); overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 22px; border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5rem; background: #fef2f2; width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid #fecaca; flex-shrink: 0;">🚫</span>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #0f172a;">Từ chối / Hủy đơn hàng</h3>
                    <div style="font-size: 0.78rem; color: #64748b; font-weight: 500;">Chọn lý do gian hàng từ chối phục vụ đơn này</div>
                </div>
            </div>
            <button type="button" onclick="closeSellerCancelModal()" style="background: none; border: none; font-size: 1.8rem; color: #94a3b8; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
        </div>

        <div style="padding: 18px 22px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label class="slr-cancel-chip">
                    <input type="radio" name="seller_cancel_preset" value="🍱 Tạm hết món / Hết nguyên liệu chế biến" checked>
                    <span>🍱 Tạm hết món / Hết nguyên liệu chế biến</span>
                </label>
                <label class="slr-cancel-chip">
                    <input type="radio" name="seller_cancel_preset" value="🛑 Gian hàng quá tải / Tạm ngưng nhận đơn">
                    <span>🛑 Gian hàng quá tải / Tạm ngưng nhận đơn</span>
                </label>
                <label class="slr-cancel-chip">
                    <input type="radio" name="seller_cancel_preset" value="📞 Không liên hệ được với khách hàng">
                    <span>📞 Không liên hệ được với khách hàng</span>
                </label>
                <label class="slr-cancel-chip">
                    <input type="radio" name="seller_cancel_preset" value="📍 Địa chỉ xa / Không hỗ trợ giao tới đây">
                    <span>📍 Địa chỉ xa / Không hỗ trợ giao tới đây</span>
                </label>
                <label class="slr-cancel-chip">
                    <input type="radio" name="seller_cancel_preset" value="other">
                    <span>✏️ Lý do khác (Nhập chi tiết)</span>
                </label>
            </div>

            <div id="sellerCancelOtherWrapper" style="margin-top: 14px; display: none;">
                <textarea id="sellerCancelOtherText" style="width: 100%; height: 80px; border-radius: 12px; border: 1.5px solid #cbd5e1; padding: 12px; font-family: inherit; font-size: 0.85rem; outline: none; box-sizing: border-box; resize: none;" placeholder="Vui lòng ghi rõ lý do từ chối..."></textarea>
            </div>
        </div>

        <div style="padding: 14px 22px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" onclick="submitSellerCancelOrder()" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; border: none; font-weight: 800; font-size: 0.85rem; padding: 11px 18px; border-radius: 12px; cursor: pointer; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);">
                🚫 Xác nhận hủy đơn
            </button>
            <button type="button" onclick="closeSellerCancelModal()" style="background: #ffffff; color: #64748b; border: 1.5px solid #cbd5e1; font-weight: 700; font-size: 0.85rem; padding: 11px 16px; border-radius: 12px; cursor: pointer;">
                Quay lại
            </button>
        </div>
    </div>
</div>

<style>
.slr-cancel-chip {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px; border: 1.5px solid #e2e8f0;
    border-radius: 12px; cursor: pointer; font-size: 0.85rem;
    font-weight: 600; color: #334155; transition: all 0.2s ease;
    background: #f8fafc; user-select: none;
}
.slr-cancel-chip:hover {
    border-color: #fca5a5; background: #fef2f2; color: #dc2626;
}
.slr-cancel-chip input[type="radio"] {
    accent-color: #dc2626; width: 17px; height: 17px; margin: 0; cursor: pointer; flex-shrink: 0;
}
</style>

<script>
function handleSellerCancelOrder() {
    const modal = document.getElementById('sellerCancelModal');
    if (modal) modal.style.display = 'flex';
}

function closeSellerCancelModal() {
    const modal = document.getElementById('sellerCancelModal');
    if (modal) modal.style.display = 'none';
}

function submitSellerCancelOrder() {
    const modal = document.getElementById('sellerCancelModal');
    const selected = modal.querySelector('input[name="seller_cancel_preset"]:checked');
    const otherText = document.getElementById('sellerCancelOtherText').value.trim();

    let finalReason = selected ? selected.value : '';
    if (finalReason === 'other') {
        finalReason = otherText ? `Lý do khác: ${otherText}` : 'Lý do khác';
    }

    document.getElementById('seller-cancel-reason').value = finalReason;
    document.getElementById('form-cancel-order').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="seller_cancel_preset"]');
    const wrapper = document.getElementById('sellerCancelOtherWrapper');
    if (radios && wrapper) {
        radios.forEach(r => {
            r.addEventListener('change', function() {
                wrapper.style.display = this.value === 'other' ? 'block' : 'none';
            });
        });
    }

    // Realtime polling 2s on seller view so if customer cancels, seller view automatically syncs
    const orderId = "{{ $order->id }}";
    let lastStatus = "{{ $order->status }}";
    setInterval(() => {
        fetch(`/api/orders/${orderId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                if (data.data.status !== lastStatus) {
                    window.location.reload();
                }
            }
        })
        .catch(() => {});
    }, 2000);
});
</script>

@endsection
