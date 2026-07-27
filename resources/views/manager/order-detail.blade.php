@extends('layouts.admin')

@section('title', 'BQL - Chi tiết đơn hàng #ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT))

@section('content')

@php
    $st = strtolower($order->status ?? 'pending');
    $isConfirmed = in_array($st, ['confirmed', 'preparing', 'đang chuẩn bị']);
    $isReady     = in_array($st, ['ready', 'sẵn sàng', 'chờ lấy']);
    $isDone      = in_array($st, ['completed', 'delivered', 'hoàn thành', 'hoàn tất']);
    $isCancelled = in_array($st, ['cancelled', 'rejected', 'đã từ chối', 'đã hủy']);
    $isPending   = !$isConfirmed && !$isReady && !$isDone && !$isCancelled;

    $badgeStyle  = 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;';
    $badgeLabel  = '🟡 Chờ xác nhận';
    if ($isConfirmed) {
        $badgeStyle = 'background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;';
        $badgeLabel = '🟢 Đã nhận & Đang chuẩn bị';
    } elseif ($isReady) {
        $badgeStyle = 'background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd;';
        $badgeLabel = '🏪 Sẵn sàng tại sạp';
    } elseif ($isDone) {
        $badgeStyle = 'background:#ecfdf5; color:#047857; border:1px solid #6ee7b7;';
        $badgeLabel = '🎉 Hoàn thành';
    } elseif ($isCancelled) {
        $badgeStyle = 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;';
        $badgeLabel = '✕ Đã từ chối';
    }
@endphp

@if(session('success'))
    <div style="padding: 14px 20px; background: #ecfdf5; border: 1.5px solid #10b981; color: #065f46; border-radius: 12px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <span>🎉</span> {{ session('success') }}
    </div>
@endif

<!-- TOP ACTION BAR -->
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.orders.index') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 12px; background: #ffffff; color: #0f172a; font-weight: 700; text-decoration: none; border: 1.5px solid #e2e8f0; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        ⬅️ Quay lại danh sách đơn hàng toàn chợ
    </a>
</div>

<!-- HEADER SECTION -->
<div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 24px 28px; margin-bottom: 24px; box-shadow: 0 8px 24px -6px rgba(0,0,0,0.04); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
            <h1 style="font-size: 1.65rem; font-weight: 900; color: #0f172a; margin: 0;">
                Chi tiết đơn hàng #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
            </h1>
            <span style="display: inline-block; font-size: 0.8rem; font-weight: 800; padding: 6px 14px; border-radius: 20px; {{ $badgeStyle }}">
                {{ $badgeLabel }}
            </span>
        </div>
        <div style="font-size: 0.88rem; color: #64748b;">
            🏪 Gian hàng: <strong style="color: #0d9488;">{{ $order->stall_name ?? 'N/A' }}</strong> | Thời gian đặt: <strong>{{ \Carbon\Carbon::parse($order->created_at)->format('H:i - d/m/Y') }}</strong>
        </div>
    </div>
</div>

<!-- 2 COLUMN LAYOUT -->
<div style="display: grid; grid-template-columns: minmax(320px, 1fr) minmax(400px, 1.4fr); gap: 24px; align-items: start;">

    <!-- LEFT COLUMN -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- CARD 1: THÔNG TIN KHÁCH HÀNG -->
        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">
                👤 Thông tin khách hàng
            </h3>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <div>
                    <span style="color: #64748b; font-size: 0.76rem; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Tên khách hàng</span>
                    <strong style="color: #0f172a; font-size: 1rem;">{{ $order->customer_name ?? 'Khách lẻ' }}</strong>
                </div>
                <div style="border-top: 1px dashed #e2e8f0; padding-top: 10px;">
                    <span style="color: #64748b; font-size: 0.76rem; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Số điện thoại</span>
                    <a href="tel:{{ $order->customer_phone }}" style="color: #0d9488; font-weight: 800; font-size: 1.05rem; text-decoration: none;">
                        📞 {{ $order->customer_phone ?? 'N/A' }}
                    </a>
                </div>
                <div style="border-top: 1px dashed #e2e8f0; padding-top: 10px;">
                    <span style="color: #64748b; font-size: 0.76rem; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">📍 Hẹn lấy / Địa chỉ</span>
                    <span style="color: #0f172a; font-weight: 600;">{{ $order->shipping_address ?? 'Tự lấy tại chợ' }}</span>
                </div>
            </div>
        </div>

        <!-- CARD 2: CẬP NHẬT TRẠNG THÁI (DÀNH CHO BQL CHỢ / ADMIN) -->
        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #86efac; border-radius: 18px; padding: 24px; box-shadow: 0 8px 24px -6px rgba(16, 185, 129, 0.15);">
            <h3 style="font-size: 1.1rem; font-weight: 900; color: #166534; margin: 0 0 12px 0;">
                🛡️ BQL Chợ Cập Nhật Trạng Thái
            </h3>

            @if($isPending)
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                          onsubmit="return confirm('BQL xác nhận hỗ trợ sạp nhận đơn hàng #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}?')">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 14px; border: none; background: #0d9488; color: #ffffff; font-weight: 900; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            ✅ Nhận đơn & Tiến hành chuẩn bị
                        </button>
                    </form>

                    <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                          onsubmit="return confirm('Hủy đơn hàng #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}?')">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" style="width: 100%; padding: 12px 20px; border-radius: 14px; border: 1.5px solid #fca5a5; background: #fef2f2; color: #dc2626; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            ✕ Hủy đơn hàng này
                        </button>
                    </form>
                </div>
            @elseif($isConfirmed)
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 14px; border: none; background: #0284c7; color: #ffffff; font-weight: 900; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            🏪 Xác nhận chuẩn bị xong (Sẵn sàng tại sạp)
                        </button>
                    </form>

                    <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                          onsubmit="return confirm('Hủy đơn hàng #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}?')">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" style="width: 100%; padding: 12px 20px; border-radius: 14px; border: 1.5px solid #fca5a5; background: #fef2f2; color: #dc2626; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            ✕ Hủy đơn hàng này
                        </button>
                    </form>
                </div>
            @elseif($isReady)
                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                      onsubmit="return confirm('Xác nhận khách đã lấy đồ và hoàn thành đơn?')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 14px; border: none; background: #059669; color: #ffffff; font-weight: 900; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        🎉 Khách đã lấy (Hoàn thành đơn hàng)
                    </button>
                </form>
            @elseif($isDone)
                <div style="padding: 14px; background: #ecfdf5; border: 1.5px solid #10b981; color: #047857; border-radius: 12px; font-weight: 800; text-align: center;">
                    🎉 Đơn hàng đã hoàn thành!
                </div>
            @elseif($isCancelled)
                <div style="padding: 14px; background: #fef2f2; border: 1.5px solid #ef4444; color: #b91c1c; border-radius: 12px; font-weight: 800; text-align: center;">
                    ✕ Đơn hàng đã bị hủy.
                </div>
            @endif
        </div>

    </div>

    <!-- RIGHT COLUMN -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- CARD 1: DANH SÁCH MÓN ĐẶT MUA -->
        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">
                🍱 Chi tiết sản phẩm đặt mua
            </h3>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9; text-align: left; color: #64748b; font-size: 0.78rem; text-transform: uppercase;">
                        <th style="padding: 8px 0;">Sản phẩm</th>
                        <th style="padding: 8px; text-align: center;">Số lượng</th>
                        <th style="padding: 8px; text-align: right;">Đơn giá</th>
                        <th style="padding: 8px 0; text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($order->items) && count($order->items) > 0)
                        @foreach($order->items as $item)
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 14px 0;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                                        {{ $item->dish_name ?? $item->product_name ?? $item->name ?? 'Sản phẩm' }}
                                    </div>
                                </td>
                                <td style="padding: 14px; text-align: center; font-weight: 800; color: #0d9488;">
                                    {{ $item->quantity }}
                                </td>
                                <td style="padding: 14px; text-align: right; color: #64748b;">
                                    {{ number_format($item->price ?? 0, 0, ',', '.') }}đ
                                </td>
                                <td style="padding: 14px 0; text-align: right; font-weight: 800; color: #0f172a;">
                                    {{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

            <div style="border-top: 2px dashed #e2e8f0; margin-top: 16px; padding-top: 16px; display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 900;">
                    <span style="color: #0f172a;">Tổng cộng thanh toán:</span>
                    <span style="color: #10b981; font-size: 1.35rem;">{{ number_format($order->total_amount ?? 0, 0, ',', '.') }}đ</span>
                </div>
            </div>
        </div>

        <!-- CARD 2: TIMELINE LỊCH SỬ -->
        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 20px 0;">
                🔄 Lịch sử đơn hàng (Timeline)
            </h3>

            <div style="display: flex; flex-direction: column; gap: 16px; position: relative; padding-left: 28px;">
                <div style="position: absolute; top: 10px; bottom: 10px; left: 10px; width: 3px; background: #e2e8f0; border-radius: 2px;"></div>

                <div style="position: relative;">
                    <div style="position: absolute; left: -28px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: #10b981; border: 3px solid #ffffff; box-shadow: 0 0 0 2px #10b981;"></div>
                    <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem;">
                        📝 Đơn hàng được khởi tạo
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('H:i - d/m/Y') }}
                    </div>
                </div>

                <div style="position: relative;">
                    @php $m2Active = $isConfirmed || $isReady || $isDone; @endphp
                    <div style="position: absolute; left: -28px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: {{ $m2Active ? '#10b981' : '#cbd5e1' }}; border: 3px solid #ffffff; box-shadow: 0 0 0 2px {{ $m2Active ? '#10b981' : '#cbd5e1' }};"></div>
                    <div style="font-weight: 800; color: {{ $m2Active ? '#0f172a' : '#94a3b8' }}; font-size: 0.92rem;">
                        📋 Sạp đã nhận đơn & đang chuẩn bị
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        {{ $m2Active ? \Carbon\Carbon::parse($order->updated_at)->format('H:i - d/m/Y') : 'Chờ xử lý...' }}
                    </div>
                </div>

                <div style="position: relative;">
                    @php $m3Active = $isReady || $isDone; @endphp
                    <div style="position: absolute; left: -28px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: {{ $m3Active ? '#0284c7' : '#cbd5e1' }}; border: 3px solid #ffffff; box-shadow: 0 0 0 2px {{ $m3Active ? '#0284c7' : '#cbd5e1' }};"></div>
                    <div style="font-weight: 800; color: {{ $m3Active ? '#0f172a' : '#94a3b8' }}; font-size: 0.92rem;">
                        🏪 Sẵn sàng tại sạp
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        {{ $m3Active ? \Carbon\Carbon::parse($order->updated_at)->format('H:i - d/m/Y') : 'Chờ cập nhật...' }}
                    </div>
                </div>

                <div style="position: relative;">
                    <div style="position: absolute; left: -28px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: {{ $isDone ? '#059669' : ($isCancelled ? '#ef4444' : '#cbd5e1') }}; border: 3px solid #ffffff; box-shadow: 0 0 0 2px {{ $isDone ? '#059669' : ($isCancelled ? '#ef4444' : '#cbd5e1') }};"></div>
                    <div style="font-weight: 800; color: {{ $isDone ? '#059669' : ($isCancelled ? '#ef4444' : '#94a3b8') }}; font-size: 0.92rem;">
                        @if($isCancelled)
                            ✕ Đơn hàng bị hủy
                        @else
                            🎉 Hoàn thành đơn hàng
                        @endif
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        {{ ($isDone || $isCancelled) ? \Carbon\Carbon::parse($order->updated_at)->format('H:i - d/m/Y') : 'Chờ hoàn tất...' }}
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

@endsection
