@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng - Đông Anh Map')

@section('content')
<div class="container" style="padding: 40px 20px; font-family: 'Be Vietnam Pro', sans-serif;">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px; font-family: var(--font-heading);">
            🛒 Thanh toán đặt hàng
        </h2>
        <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
            Vui lòng điền thông tin giao hàng để hoàn tất đơn hàng của bạn.
        </p>
    </div>

    @if($errors->any())
        <div class="glass-panel" style="background: rgba(231, 76, 60, 0.1); border-color: #e74c3c; padding: 16px; border-radius: 12px; color: #e74c3c; margin-bottom: 24px;">
            <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; align-items: start;">
        <!-- Left Column: Shipping Info & Payment -->
        <div class="glass-panel" style="padding: 30px; border-radius: 20px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.015);">
            <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
                @csrf
                <input type="hidden" name="items" value="{{ request()->query('items') }}">
                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                    📍 Thông tin giao nhận
                </h3>

                <div style="display: flex; flex-direction: column; gap: 18px; margin-bottom: 30px;">
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Họ và tên người nhận <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" required class="form-input" placeholder="Nhập tên người nhận..." value="{{ old('name', Auth::user()->name ?? '') }}" style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Số điện thoại <span style="color:#ef4444;">*</span></label>
                        <input type="tel" name="phone" required class="form-input" placeholder="Nhập số điện thoại nhận hàng..." value="{{ old('phone', Auth::user()->phone ?? '') }}" style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Địa chỉ giao hàng <span style="color:#ef4444;">*</span></label>
                        <textarea name="address" required rows="3" class="form-input" placeholder="Số nhà, tên đường, xã/thị trấn, Đông Anh, Hà Nội..." style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none; resize: vertical;">{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Ghi chú đơn hàng (Tùy chọn)</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Ghi chú về thời gian giao hàng, chỉ dẫn tìm đường..." style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none; resize: vertical;">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                    💳 Phương thức thanh toán
                </h3>

                <div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 30px;">
                    <label style="position: relative; border: 2px solid var(--primary); border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; background: rgba(255, 126, 41, 0.03);" class="payment-method-card active">
                        <input type="radio" name="payment_method" value="COD" checked style="accent-color: var(--primary);" onchange="updatePaymentCardStyles(this)">
                        <div>
                            <strong style="display: block; font-size: 0.9rem; color: var(--text-main);">Thanh toán khi nhận hàng (COD)</strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Trả tiền mặt khi Shipper giao tới</span>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 16px; font-size: 1.05rem; font-weight: 800; border-radius: 12px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 8px 24px rgba(255,126,41,0.25);">
                    🚀 Xác nhận Đặt hàng
                </button>
            </form>
        </div>

        <!-- Right Column: Order Summary -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Order Summary Grouped by Subdomain/Eatery -->
            <div class="glass-panel" style="padding: 24px; border-radius: 20px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.015);">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin-top: 0; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                    📝 Tóm tắt đơn hàng
                </h3>

                <!-- Info message explaining Split Order -->
                <div style="background: rgba(32, 178, 170, 0.05); border: 1px dashed rgba(32, 178, 170, 0.3); border-radius: 12px; padding: 12px 16px; font-size: 0.8rem; line-height: 1.5; color: var(--text-muted); margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-start;">
                    <span>🛡️</span>
                    <span><strong>Lưu ý:</strong> Do các mặt hàng được cung cấp bởi các cửa hàng/kho gian khác nhau, hệ thống sẽ tự động tách thành các đơn hàng riêng biệt để shipper giao nhanh nhất.</span>
                </div>

                <!-- Group items by eatery/merchant -->
                @php
                    $groupedCartItems = collect($cartItems)->groupBy('eatery_id');
                @endphp

                <div style="display: flex; flex-direction: column; gap: 20px; max-height: 380px; overflow-y: auto; padding-right: 4px; margin-bottom: 24px;">
                    @foreach($groupedCartItems as $eateryId => $items)
                        @php
                            $firstItem = $items->first();
                            $eatery = null;
                            if ($firstItem['category_slug'] === 'dong-anh-market') {
                                $eatery = \App\Models\OcopProduct::on('mysql_market')->find($firstItem['ocop_product_id'])?->eatery;
                            } else {
                                $eatery = \App\Models\Dish::on('mysql')->find($firstItem['dish_id'])?->eatery;
                            }
                        @endphp
                        <div style="border: 1px solid var(--border-glow); border-radius: 14px; padding: 14px; background: rgba(255,255,255,0.015);">
                            <div style="font-size: 0.82rem; font-weight: 700; color: var(--accent); margin-bottom: 10px; display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-glow); padding-bottom: 6px;">
                                <span>🏪 {{ $eatery ? $eatery->name : 'Cửa hàng Đông Anh' }}</span>
                                <span style="background: rgba(255, 126, 41, 0.1); padding: 1px 6px; border-radius: 4px; font-size: 0.72rem;">
                                    {{ $firstItem['category_slug'] === 'dong-anh-market' ? 'Chợ & OCOP' : 'Ẩm thực' }}
                                </span>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                @foreach($items as $item)
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <img src="{{ $item['image'] }}" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;" alt="">
                                        <div style="flex: 1; min-width: 0;">
                                            <h5 style="margin: 0; font-size: 0.85rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item['name'] }}</h5>
                                            <span style="font-size: 0.78rem; color: var(--text-muted);">SL: {{ $item['quantity'] }} x {{ number_format($item['price']) }}đ</span>
                                        </div>
                                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">{{ number_format($item['total']) }}đ</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="border-top: 1px dashed var(--border-glow); padding-top: 16px; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">Tạm tính:</span>
                        <span style="font-weight: 600; color: var(--text-main);" id="checkoutSubtotal">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.15rem; border-top: 1px solid var(--border-glow); padding-top: 12px; margin-top: 4px;">
                        <span style="font-weight: 700; color: var(--text-main);">Tổng thanh toán:</span>
                        <strong id="checkoutTotal" style="color: var(--primary); font-size: 1.3rem; font-weight: 800; font-family: var(--font-heading);">
                            {{ number_format($subtotal, 0, ',', '.') }}đ
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updatePaymentCardStyles(radio) {
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.classList.remove('active');
            card.style.borderColor = 'var(--border-glow)';
        });
        const card = radio.closest('.payment-method-card');
        if (card) {
            card.classList.add('active');
            card.style.borderColor = 'var(--primary)';
        }
    }

    // Set initial border for active payment method
    document.addEventListener('DOMContentLoaded', () => {
        const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
        if (checkedRadio) {
            updatePaymentCardStyles(checkedRadio);
        }
    });
</script>
<style>
    .payment-method-card {
        border-color: var(--border-glow);
    }
    .payment-method-card.active {
        border-color: var(--primary) !important;
        background: rgba(255, 126, 41, 0.03) !important;
    }
</style>
@endsection


