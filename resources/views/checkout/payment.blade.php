@extends('layouts.app')

@section('title', 'Cổng thanh toán giả lập - Đông Anh Map')

@section('content')
<div class="container" style="max-width: 600px; padding: 50px 20px; font-family: 'Be Vietnam Pro', sans-serif;">
    <div class="glass-panel" style="padding: 35px; border-radius: 24px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.015); box-shadow: 0 15px 40px rgba(0,0,0,0.15); text-align: center;">
        
        <!-- Payment Header -->
        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 24px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(14, 165, 233, 0.1); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #0ea5e9;">
                💳
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: var(--font-heading);">
                Cổng thanh toán an toàn Đông Anh Pay
            </h2>
            <span style="font-size: 0.8rem; background: rgba(241, 196, 15, 0.15); color: #f1c40f; padding: 4px 12px; border-radius: 20px; font-weight: 700; border: 1px solid rgba(241, 196, 15, 0.3);">
                ⚠️ Môi trường giả lập - Không dùng tiền thật
            </span>
        </div>

        <!-- Timer Countdown -->
        <div style="background: rgba(239, 68, 68, 0.04); border: 1px dashed rgba(239, 68, 68, 0.2); border-radius: 12px; padding: 10px; font-size: 0.88rem; color: #ef4444; font-weight: 700; margin-bottom: 24px;">
            ⏳ Giao dịch sẽ hết hạn sau: <span id="countdownTimer">05:00</span>
        </div>

        <!-- QR Code and Payment Info Card -->
        <div style="background: rgba(255, 255, 255, 0.02); border: 1.5px solid var(--border-glow); border-radius: 18px; padding: 24px; margin-bottom: 30px; display: flex; flex-direction: column; align-items: center; gap: 16px;">
            <!-- QR code generated dynamically via API -->
            <div style="background: #ffffff; padding: 14px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 12px rgba(0,0,0,0.06); cursor: pointer;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://donganh.hanoi.gov.vn/payment-simulated-order-{{ $order->id }}" alt="QR Code Payment" style="width: 170px; height: 170px; display: block; mix-blend-mode: multiply;">
            </div>
            
            <div style="width: 100%; text-align: left; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; border-top: 1px dashed var(--border-glow); padding-top: 16px; margin-top: 8px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Mã đơn hàng:</span>
                    <strong style="color: var(--text-main);">#DA-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Đơn vị thụ hưởng:</span>
                    <strong style="color: var(--text-main);">Đông Anh Map Discovery System</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.1rem; border-top: 1px solid var(--border-glow); padding-top: 10px; margin-top: 4px;">
                    <span style="color: var(--text-muted); font-weight: 700;">Số tiền cần chuyển:</span>
                    <strong style="color: var(--primary); font-size: 1.25rem; font-weight: 800; font-family: var(--font-heading);">
                        {{ number_format($order->total_amount, 0, ',', '.') }}đ
                    </strong>
                </div>
            </div>
        </div>

        <!-- Simulation Actions Form -->
        <form action="{{ route('checkout.payment.process', $order->id) }}" method="POST" id="paymentProcessForm">
            @csrf
            <input type="hidden" name="simulate_success" id="simulateSuccessInput" value="1">
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <button type="button" onclick="submitPayment(true)" class="btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; font-weight: 700; border-radius: 12px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(255, 126, 41, 0.2);">
                    ✅ Xác nhận Đã chuyển khoản (Thành công)
                </button>
                
                <button type="button" onclick="submitPayment(false)" class="btn-secondary" style="width: 100%; padding: 14px; font-size: 0.95rem; font-weight: 700; border-radius: 12px; cursor: pointer; border-color: rgba(239, 68, 68, 0.2); color: #ef4444; background: rgba(239, 68, 68, 0.04); transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.08)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.04)'">
                    ❌ Hủy giao dịch thanh toán
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Countdown Timer logic
    document.addEventListener('DOMContentLoaded', () => {
        let timeRemaining = 300; // 5 minutes in seconds
        const timerElement = document.getElementById('countdownTimer');

        const interval = setInterval(() => {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;

            timerElement.innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (timeRemaining <= 0) {
                clearInterval(interval);
                alert('Thời gian thanh toán đã hết hạn. Đơn hàng đã bị hủy.');
                submitPayment(false);
            }

            timeRemaining--;
        }, 1000);
    });

    function submitPayment(success) {
        document.getElementById('simulateSuccessInput').value = success ? '1' : '0';
        document.getElementById('paymentProcessForm').submit();
    }
</script>
@endsection
