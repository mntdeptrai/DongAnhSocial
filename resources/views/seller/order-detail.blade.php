@extends('layouts.seller')

@section('title', 'Chi tiết đơn hàng #ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT))

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

@if(session('error'))
    <div style="padding: 14px 20px; background: #fef2f2; border: 1.5px solid #ef4444; color: #b91c1c; border-radius: 12px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <span>⚠️</span> {{ session('error') }}
    </div>
@endif

<!-- TOP ACTION BAR & BREADCRUMB -->
<div style="margin-bottom: 20px;">
    <a href="{{ route('seller.orders.index') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 12px; background: #ffffff; color: var(--slr-text-main); font-weight: 700; text-decoration: none; border: 1.5px solid var(--slr-border); transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.04);" onmouseover="this.style.borderColor='var(--slr-primary)'" onmouseout="this.style.borderColor='var(--slr-border)'">
        ⬅️ Quay lại danh sách đơn hàng
    </a>
</div>

<!-- HEADER SECTION -->
<div style="background: #ffffff; border: 1.5px solid var(--slr-border); border-radius: 20px; padding: 24px 28px; margin-bottom: 24px; box-shadow: 0 8px 24px -6px rgba(0,0,0,0.04); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
            <h1 style="font-size: 1.65rem; font-weight: 900; color: var(--slr-text-main); margin: 0;">
                Chi tiết đơn hàng #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
            </h1>
            <span style="display: inline-block; font-size: 0.8rem; font-weight: 800; padding: 6px 14px; border-radius: 20px; {{ $badgeStyle }}">
                {{ $badgeLabel }}
            </span>
        </div>
        <div style="font-size: 0.88rem; color: var(--slr-text-muted);">
            🕒 Thời gian đặt: <strong>{{ \Carbon\Carbon::parse($order->created_at)->format('H:i - d/m/Y') }}</strong>
        </div>
    </div>
</div>

<!-- 2 COLUMN LAYOUT (LEFT 40% - RIGHT 60%) -->
<div style="display: grid; grid-template-columns: minmax(320px, 1fr) minmax(400px, 1.4fr); gap: 24px; align-items: start;">

    <!-- LEFT COLUMN: THÔNG TIN KHÁCH HÀNG & THAO TÁC CẬP NHẬT TRẠNG THÁI -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- CARD 1: THÔNG TIN KHÁCH HÀNG -->
        <div style="background: #ffffff; border: 1.5px solid var(--slr-border); border-radius: 18px; padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--slr-text-main); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                👤 Thông tin khách hàng
            </h3>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <div>
                    <span style="color: var(--slr-text-muted); font-size: 0.76rem; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Tên khách hàng</span>
                    <strong style="color: var(--slr-text-main); font-size: 1rem;">{{ $order->customer_name ?? 'Khách lẻ' }}</strong>
                </div>
                <div style="border-top: 1px dashed var(--slr-border); padding-top: 10px;">
                    <span style="color: var(--slr-text-muted); font-size: 0.76rem; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Số điện thoại liên hệ</span>
                    <a href="tel:{{ $order->customer_phone }}" style="color: var(--slr-primary); font-weight: 800; font-size: 1.05rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        📞 {{ $order->customer_phone ?? 'N/A' }}
                    </a>
                </div>
                <div style="border-top: 1px dashed var(--slr-border); padding-top: 10px;">
                    <span style="color: var(--slr-text-muted); font-size: 0.76rem; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">📍 Hẹn lấy / Địa chỉ</span>
                    <span style="color: var(--slr-text-main); font-weight: 600; line-height: 1.4;">{{ $order->shipping_address ?? 'Tự lấy tại chợ' }}</span>
                </div>
            </div>
        </div>

        <!-- CARD 2: PHƯƠNG THỨC THANH TOÁN & GHI CHÚ -->
        <div style="background: #ffffff; border: 1.5px solid var(--slr-border); border-radius: 18px; padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--slr-text-main); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                💳 Thanh toán & Ghi chú
            </h3>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <div>
                    <span style="color: var(--slr-text-muted); font-size: 0.76rem; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Phương thức thanh toán</span>
                    <span style="font-weight: 800; color: var(--slr-text-main);">
                        {{ $order->payment_method ?? 'COD (Tiền mặt tại sạp)' }}
                    </span>
                </div>
                <div style="border-top: 1px dashed var(--slr-border); padding-top: 10px;">
                    <span style="color: var(--slr-text-muted); font-size: 0.76rem; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Ghi chú từ khách hàng</span>
                    <span style="color: #92400e; font-style: italic; font-weight: 600;">
                        "{{ $order->notes ?? 'Không có ghi chú' }}"
                    </span>
                </div>
            </div>
        </div>

        <!-- CARD 3: NÚT XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG (LUỒNG A CHUẨN) -->
        <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 2px solid #fdba74; border-radius: 18px; padding: 24px; box-shadow: 0 8px 24px -6px rgba(234, 88, 12, 0.15);">
            <h3 style="font-size: 1.1rem; font-weight: 900; color: #c2410c; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
                ⚡ Cập nhật trạng thái đơn
            </h3>
            <p style="font-size: 0.84rem; color: #9a3412; margin: 0 0 18px 0; line-height: 1.4;">
                Tiểu thương thao tác cập nhật tiến trình đơn hàng để thông báo cho khách hàng biết:
            </p>

            @if($isPending)
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST"
                          onsubmit="return confirm('Xác nhận nhận đơn hàng #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }} và tiến hành đóng gói chuẩn bị?')">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 14px; border: none; background: #10b981; color: #ffffff; font-weight: 900; font-size: 0.95rem; cursor: pointer; box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35); display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                            ✅ Nhận đơn hàng & Chuẩn bị đồ
                        </button>
                    </form>

                    <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST"
                          onsubmit="return confirm('Từ chối đơn hàng #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}?')">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" style="width: 100%; padding: 12px 20px; border-radius: 14px; border: 1.5px solid #fca5a5; background: #fef2f2; color: #dc2626; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                            ✕ Từ chối đơn hàng này
                        </button>
                    </form>
                </div>

            @elseif($isConfirmed)
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 14px; border: none; background: #0284c7; color: #ffffff; font-weight: 900; font-size: 0.95rem; cursor: pointer; box-shadow: 0 6px 18px rgba(2, 132, 199, 0.35); display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='#0284c7'">
                            🏪 Xác nhận chuẩn bị xong (Sẵn sàng tại sạp)
                        </button>
                    </form>

                    <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST"
                          onsubmit="return confirm('Từ chối / Hủy đơn hàng #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}?')">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" style="width: 100%; padding: 12px 20px; border-radius: 14px; border: 1.5px solid #fca5a5; background: #fef2f2; color: #dc2626; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                            ✕ Từ chối / Hủy đơn hàng
                        </button>
                    </form>
                </div>

            @elseif($isReady)
                <form action="{{ route('seller.orders.update-status', $order->id) }}" method="POST"
                      onsubmit="return confirm('Xác nhận khách đã đến sạp nhận túi đồ và hoàn tất đơn?')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" style="width: 100%; padding: 14px 20px; border-radius: 14px; border: none; background: #059669; color: #ffffff; font-weight: 900; font-size: 0.95rem; cursor: pointer; box-shadow: 0 6px 18px rgba(5, 150, 105, 0.35); display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                        🎉 Khách đã đến lấy đồ (Hoàn thành đơn hàng)
                    </button>
                </form>

            @elseif($isDone)
                <div style="padding: 14px; background: #ecfdf5; border: 1.5px solid #10b981; color: #047857; border-radius: 12px; font-weight: 800; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    🎉 Đơn hàng đã giao thành công và hoàn tất!
                </div>

            @elseif($isCancelled)
                <div style="padding: 14px; background: #fef2f2; border: 1.5px solid #ef4444; color: #b91c1c; border-radius: 12px; font-weight: 800; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    ✕ Đơn hàng này đã bị từ chối / hủy.
                </div>
            @endif
        </div>

    </div>

    <!-- RIGHT COLUMN: DANH SÁCH MÓN ĐẶT MUA & TIMELINE LỊCH SỬ -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- CARD 1: CHI TIẾT SẢN PHẨM / MÓN ĂN ĐẶT MUA -->
        <div style="background: #ffffff; border: 1.5px solid var(--slr-border); border-radius: 18px; padding: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--slr-text-main); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                🍱 Chi tiết sản phẩm đặt mua
            </h3>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9; text-align: left; color: var(--slr-text-muted); font-size: 0.78rem; text-transform: uppercase;">
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
                                    <div style="font-weight: 700; color: var(--slr-text-main); font-size: 0.95rem;">
                                        {{ $item->dish_name ?? $item->product_name ?? $item->name ?? 'Sản phẩm' }}
                                    </div>
                                </td>
                                <td style="padding: 14px; text-align: center; font-weight: 800; color: var(--slr-primary);">
                                    {{ $item->quantity }}
                                </td>
                                <td style="padding: 14px; text-align: right; color: var(--slr-text-muted);">
                                    {{ number_format($item->price ?? 0, 0, ',', '.') }}đ
                                </td>
                                <td style="padding: 14px 0; text-align: right; font-weight: 800; color: var(--slr-text-main);">
                                    {{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" style="padding: 16px 0; color: var(--slr-text-muted); text-align: center;">
                                Không có thông tin món ăn chi tiết.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- BẢNG TỔNG CỘNG TIỀN -->
            <div style="border-top: 2px dashed var(--slr-border); margin-top: 16px; padding-top: 16px; display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; color: var(--slr-text-muted);">
                    <span>Tạm tính</span>
                    <span>{{ number_format($order->subtotal ?? $order->total_amount ?? 0, 0, ',', '.') }}đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; color: var(--slr-text-muted);">
                    <span>Phí vận chuyển / Hẹn lấy</span>
                    <span style="color: #10b981; font-weight: 700;">Miễn phí (Lấy tại chợ)</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 900; border-top: 1px solid #f1f5f9; padding-top: 12px; margin-top: 6px;">
                    <span style="color: var(--slr-text-main);">Tổng cộng thanh toán:</span>
                    <span style="color: #10b981; font-size: 1.35rem;">{{ number_format($order->total_amount ?? 0, 0, ',', '.') }}đ</span>
                </div>
            </div>
        </div>

        <!-- CARD 2: TIMELINE LỊCH SỬ ĐƠN HÀNG (LUỒNG A 4 BƯỚC) -->
        <div style="background: #ffffff; border: 1.5px solid var(--slr-border); border-radius: 18px; padding: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--slr-text-main); margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                🔄 Lịch sử & Tiến trình đơn hàng
            </h3>

            <div style="display: flex; flex-direction: column; gap: 16px; position: relative; padding-left: 28px;">
                <!-- Dải đường kẻ dọc -->
                <div style="position: absolute; top: 10px; bottom: 10px; left: 10px; width: 3px; background: #e2e8f0; border-radius: 2px;"></div>

                <!-- Mốc 1: Đặt đơn -->
                <div style="position: relative;">
                    <div style="position: absolute; left: -28px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: #10b981; border: 3px solid #ffffff; box-shadow: 0 0 0 2px #10b981;"></div>
                    <div style="font-weight: 800; color: var(--slr-text-main); font-size: 0.92rem;">
                        📝 Khách hàng gửi đơn hàng
                    </div>
                    <div style="font-size: 0.78rem; color: var(--slr-text-muted);">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('H:i - d/m/Y') }}
                    </div>
                </div>

                <!-- Mốc 2: Nhận đơn -->
                <div style="position: relative;">
                    @php $m2Active = $isConfirmed || $isReady || $isDone; @endphp
                    <div style="position: absolute; left: -28px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: {{ $m2Active ? '#10b981' : '#cbd5e1' }}; border: 3px solid #ffffff; box-shadow: 0 0 0 2px {{ $m2Active ? '#10b981' : '#cbd5e1' }};"></div>
                    <div style="font-weight: 800; color: {{ $m2Active ? 'var(--slr-text-main)' : '#94a3b8' }}; font-size: 0.92rem;">
                        📋 Sạp nhận đơn & tiến hành chuẩn bị đồ
                    </div>
                    <div style="font-size: 0.78rem; color: var(--slr-text-muted);">
                        {{ $m2Active ? \Carbon\Carbon::parse($order->updated_at)->format('H:i - d/m/Y') : 'Chờ xử lý...' }}
                    </div>
                </div>

                <!-- Mốc 3: Sẵn sàng tại sạp -->
                <div style="position: relative;">
                    @php $m3Active = $isReady || $isDone; @endphp
                    <div style="position: absolute; left: -28px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: {{ $m3Active ? '#0284c7' : '#cbd5e1' }}; border: 3px solid #ffffff; box-shadow: 0 0 0 2px {{ $m3Active ? '#0284c7' : '#cbd5e1' }};"></div>
                    <div style="font-weight: 800; color: {{ $m3Active ? 'var(--slr-text-main)' : '#94a3b8' }}; font-size: 0.92rem;">
                        🏪 Đồ đã đóng gói sẵn sàng tại sạp (Chờ khách lấy)
                    </div>
                    <div style="font-size: 0.78rem; color: var(--slr-text-muted);">
                        {{ $m3Active ? \Carbon\Carbon::parse($order->updated_at)->format('H:i - d/m/Y') : 'Chờ cập nhật...' }}
                    </div>
                </div>

                <!-- Mốc 4: Hoàn thành -->
                <div style="position: relative;">
                    <div style="position: absolute; left: -28px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: {{ $isDone ? '#059669' : ($isCancelled ? '#ef4444' : '#cbd5e1') }}; border: 3px solid #ffffff; box-shadow: 0 0 0 2px {{ $isDone ? '#059669' : ($isCancelled ? '#ef4444' : '#cbd5e1') }};"></div>
                    <div style="font-weight: 800; color: {{ $isDone ? '#059669' : ($isCancelled ? '#ef4444' : '#94a3b8') }}; font-size: 0.92rem;">
                        @if($isCancelled)
                            ✕ Đơn hàng đã bị từ chối / hủy
                        @else
                            🎉 Khách đã nhận đồ & Đơn hàng hoàn thành
                        @endif
                    </div>
                    <div style="font-size: 0.78rem; color: var(--slr-text-muted);">
                        {{ ($isDone || $isCancelled) ? \Carbon\Carbon::parse($order->updated_at)->format('H:i - d/m/Y') : 'Chờ hoàn tất...' }}
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

@endsection
