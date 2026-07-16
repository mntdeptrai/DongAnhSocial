@extends('layouts.app')

@section('title', 'Đặt hàng thành công - Đông Anh Map')

@section('content')
<div class="container" style="max-width: 700px; padding: 50px 20px; font-family: 'Be Vietnam Pro', sans-serif;">
    
    @if(session('success'))
        <div class="glass-panel" style="background: rgba(46, 204, 113, 0.1); border-color: #2ecc71; padding: 14px 20px; border-radius: 12px; color: #2ecc71; margin-bottom: 24px; text-align: center; font-size: 0.95rem; font-weight: 600;">
            🎉 {{ session('success') }}
        </div>
    @endif

    <div class="glass-panel" style="padding: 40px; border-radius: 24px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.015); box-shadow: 0 15px 40px rgba(0,0,0,0.12); display: flex; flex-direction: column; gap: 28px;">
        
        <!-- Success Icon Header -->
        <div style="text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px;">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(46, 204, 113, 0.1); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #2ecc71; animation: pulse-trust 2s infinite;">
                ✓
            </div>
            <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: var(--font-heading);">
                Đặt Hàng Thành Công!
            </h2>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0; max-width: 480px; line-height: 1.5;">
                Cảm ơn bạn đã lựa chọn mua sắm. Đơn hàng của bạn đã được tiếp nhận và đang được chủ cửa hàng chuẩn bị.
            </p>
        </div>

        <!-- Order Information Receipt -->
        <div style="border: 1px solid var(--border-glow); border-radius: 18px; padding: 24px; background: rgba(255,255,255,0.01);">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-top: 0; margin-bottom: 16px; border-bottom: 1px dashed var(--border-glow); padding-bottom: 10px; font-family: var(--font-heading);">
                🧾 Thông tin đơn hàng #DA-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
            </h3>

            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Trạng thái đơn hàng:</span>
                    <strong style="color: #2ecc71; text-transform: uppercase;">
                        @if($order->status === 'paid')
                            Đã thanh toán (Online)
                        @elseif($order->status === 'pending')
                            Chờ xác nhận (COD)
                        @else
                            {{ $order->status }}
                        @endif
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Phương thức thanh toán:</span>
                    <strong style="color: var(--text-main);">
                        {{ $order->payment_method === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : 'Thanh toán trực tuyến' }}
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Thời gian đặt hàng:</span>
                    <strong style="color: var(--text-main);">{{ $order->created_at->format('d/m/Y H:i') }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-glow); padding-top: 12px; margin-top: 4px;">
                    <span style="color: var(--text-muted);">Họ tên người nhận:</span>
                    <strong style="color: var(--text-main);">{{ $order->customer_name }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Số điện thoại:</span>
                    <strong style="color: var(--text-main);">{{ $order->customer_phone }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Địa chỉ giao hàng:</span>
                    <strong style="color: var(--text-main); text-align: right; max-width: 300px; word-break: break-word;">{{ $order->shipping_address }}</strong>
                </div>

                @if($order->notes)
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Ghi chú của bạn:</span>
                        <span style="color: var(--text-main); font-style: italic;">"{{ $order->notes }}"</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Breakdown List -->
        <div style="border: 1px solid var(--border-glow); border-radius: 18px; padding: 24px; background: rgba(255,255,255,0.01);">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-top: 0; margin-bottom: 16px; border-bottom: 1px dashed var(--border-glow); padding-bottom: 10px; font-family: var(--font-heading);">
                📦 Danh sách sản phẩm mua
            </h3>

            <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px;">
                @foreach($order->items as $item)
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem;">
                        <div style="display: flex; flex-direction: column;">
                            <strong style="color: var(--text-main);">{{ $item->name }}</strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Số lượng: {{ $item->quantity }} x {{ number_format($item->price) }}đ</span>
                        </div>
                        <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-main);">{{ number_format($item->price * $item->quantity) }}đ</span>
                    </div>
                @endforeach
            </div>

            <div style="border-top: 1px solid var(--border-glow); padding-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);">Tổng thanh toán thực tế:</span>
                <strong style="color: var(--primary); font-size: 1.25rem; font-weight: 800; font-family: var(--font-heading);">
                    {{ number_format($order->total_amount, 0, ',', '.') }}đ
                </strong>
            </div>
        </div>

        <!-- Return Actions -->
        <div style="display: flex; gap: 14px; justify-content: center; border-top: 1px solid var(--border-glow); padding-top: 24px;">
            <a href="/" class="btn-secondary" style="padding: 12px 24px; font-size: 0.9rem; border-radius: 10px; font-weight: 700; text-decoration: none;">
                🏠 Quay lại Trang chủ
            </a>
            <a href="/tim-kiem" class="btn-primary" style="padding: 12px 24px; font-size: 0.9rem; border-radius: 10px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 15px rgba(255, 126, 41, 0.25);">
                🗺️ Tiếp tục khám phá Bản đồ
            </a>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<style>
    @keyframes pulse-trust {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }
</style>
@endsection
