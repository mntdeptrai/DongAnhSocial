@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng - Đông Anh Map')

@section('content')
@php
    $hasMarketItems = collect($cartItems)->contains('category_slug', 'dong-anh-market');
@endphp
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
                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                    📍 Hình thức & Thông tin nhận hàng
                </h3>

                <!-- Fulfillment Mode Switcher Tabs -->
                <div style="display: flex; gap: 10px; margin-bottom: 22px; background: rgba(255,255,255,0.03); padding: 5px; border-radius: 14px; border: 1.5px solid var(--border-glow);">
                    <button type="button" id="tabPickupBtn" class="fulfillment-tab active" onclick="switchFulfillment('pickup')" style="flex: 1; padding: 12px; border-radius: 10px; font-size: 0.88rem; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.25s; background: var(--primary, #ff7e29); color: #ffffff; box-shadow: 0 4px 14px rgba(255,126,41,0.25);">
                        🏪 Ghé sạp lấy / Ăn tại chợ
                    </button>
                    <button type="button" id="tabDeliveryBtn" class="fulfillment-tab" onclick="switchFulfillment('delivery')" style="flex: 1; padding: 12px; border-radius: 10px; font-size: 0.88rem; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.25s; background: transparent; color: var(--text-muted);">
                        🛵 Giao tận nơi (Ship)
                    </button>
                </div>
                <input type="hidden" name="fulfillment_mode" id="fulfillmentModeInput" value="pickup">

                <div style="display: flex; flex-direction: column; gap: 18px; margin-bottom: 30px;">
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Họ và tên người nhận <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" required class="form-input" placeholder="Nhập họ và tên..." value="{{ old('name', Auth::user()->name ?? '') }}" style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Số điện thoại <span style="color:#ef4444;">*</span></label>
                        <input type="tel" name="phone" required class="form-input" placeholder="Nhập số điện thoại liên hệ..." value="{{ old('phone', Auth::user()->phone ?? '') }}" style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                    </div>

                    <!-- Panel 1: Pick up at stall (Ghé sạp lấy) -->
                    <div id="pickupFieldsSection" style="display: flex; flex-direction: column; gap: 18px;">
                        <div style="background: rgba(14, 165, 233, 0.05); border: 1.5px dashed rgba(14, 165, 233, 0.3); border-radius: 12px; padding: 14px 16px;">
                            <div style="font-size: 0.85rem; font-weight: 700; color: #0284c7; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                🏛️ Địa điểm nhận hàng:
                            </div>
                            <div style="font-size: 0.92rem; font-weight: 700; color: var(--text-main);">
                                {{ !empty($distinctMarkets) ? implode(', ', $distinctMarkets) : 'Chợ truyền thống Đông Anh' }}
                            </div>
                            @if(!empty($distinctStalls))
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">
                                    Gian hàng phụ trách: <span style="color: var(--primary); font-weight: 600;">{{ implode(' • ', $distinctStalls) }}</span>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                                ⏰ Giờ hẹn qua lấy đồ <span style="color:#ef4444;">*</span>
                            </label>
                            <select id="pickupTimeSelect" onchange="toggleCustomPickupTime(this.value)" style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                                <option value="Lấy ngay (sau 15 - 30 phút)">⚡ Lấy ngay (sau 15 - 30 phút)</option>
                                <option value="06:30 - 08:00 (Ăn sáng / Chợ sớm)">🌅 06:30 - 08:00 (Ăn sáng / Chợ sớm)</option>
                                <option value="11:00 - 12:30 (Bữa trưa)" selected>☀️ 11:00 - 12:30 (Bữa trưa)</option>
                                <option value="16:30 - 18:30 (Chợ chiều / Bữa tối)">🌇 16:30 - 18:30 (Chợ chiều / Bữa tối)</option>
                                <option value="custom">⏰ Khung giờ khác (Tự chọn)...</option>
                            </select>
                            <input type="text" id="customPickupTimeInput" class="form-input" placeholder="Nhập giờ bạn muốn ghé lấy (Ví dụ: 14h00 chiều nay)..." style="display: none; margin-top: 8px; width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                                🚗 Thông tin phương tiện / Biển số xe <small style="font-weight: normal; color: var(--text-muted);">(Tùy chọn)</small>
                            </label>
                            <input type="text" id="pickupVehicleInput" class="form-input" placeholder="Ví dụ: Xe SH đỏ 29-X1 123.45 (nhờ chủ sạp mang ra cổng chợ)..." style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                        </div>
                    </div>

                    <!-- Panel 2: Delivery to address (Giao tận nơi) -->
                    <div id="deliveryFieldsSection" style="display: none; flex-direction: column; gap: 18px;">
                        <div>
                            <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                                📍 Địa chỉ nhận hàng chi tiết <span style="color:#ef4444;">*</span>
                            </label>
                            <textarea id="deliveryAddressInput" rows="3" class="form-input" placeholder="Số nhà, tên ngõ/xóm/thôn, xã/thị trấn, huyện Đông Anh, Hà Nội..." style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none; resize: vertical;">{{ old('address') }}</textarea>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                                🚚 Thời gian mong muốn giao đến
                            </label>
                            <select id="deliveryTimeSelect" style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                                <option value="Giao càng sớm càng tốt (30-45 phút)">⚡ Giao càng sớm càng tốt (30 - 45 phút)</option>
                                <option value="Giao trong sáng nay">🌅 Giao trong sáng nay</option>
                                <option value="Giao trước giờ ăn trưa (11:30)">☀️ Giao trước giờ ăn trưa (11:30)</option>
                                <option value="Giao buổi chiều / tối (17:00 - 18:30)">🌇 Giao buổi chiều / tối (17:00 - 18:30)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Hidden address input for backend compatibility -->
                    <input type="hidden" name="address" id="hiddenAddressInput" value="">

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Ghi chú đơn hàng <small style="font-weight: normal; color: var(--text-muted);">(Tùy chọn)</small></label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Ghi chú thêm về khẩu vị (ít cay, không hành), chỉ dẫn tìm đường..." style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none; resize: vertical;">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                    💳 Phương thức thanh toán
                </h3>

                <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 30px;">
                    <label style="position: relative; border: 2px solid var(--primary); border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; background: rgba(255, 126, 41, 0.03);" class="payment-method-card active">
                        <input type="radio" name="payment_method" value="COD" checked style="accent-color: var(--primary);" onchange="updatePaymentCardStyles(this)">
                        <div>
                            <strong style="display: block; font-size: 0.9rem; color: var(--text-main);">Thanh toán khi nhận hàng (COD / Tiền mặt)</strong>
                            <span style="font-size: 0.72rem; color: var(--text-muted);">Trả tiền mặt khi nhận đồ tại sạp hoặc khi shipper giao tới</span>
                        </div>
                    </label>

                    <label style="position: relative; border: 1.5px solid var(--border-glow); border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all 0.2s;" class="payment-method-card">
                        <input type="radio" name="payment_method" value="Online" style="accent-color: var(--primary);" onchange="updatePaymentCardStyles(this)">
                        <div>
                            <strong style="display: block; font-size: 0.9rem; color: var(--text-main);">Chuyển khoản VietQR của Tiểu thương (Khuyên dùng)</strong>
                            <span style="font-size: 0.72rem; color: var(--text-muted);">Quét mã QR chuyển khoản trực tiếp cho từng chủ quầy</span>
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

    function switchFulfillment(mode) {
        const modeInput = document.getElementById('fulfillmentModeInput');
        const tabPickup = document.getElementById('tabPickupBtn');
        const tabDelivery = document.getElementById('tabDeliveryBtn');
        const pickupSection = document.getElementById('pickupFieldsSection');
        const deliverySection = document.getElementById('deliveryFieldsSection');
        const deliveryAddr = document.getElementById('deliveryAddressInput');

        modeInput.value = mode;

        if (mode === 'pickup') {
            tabPickup.style.background = 'var(--primary, #ff7e29)';
            tabPickup.style.color = '#ffffff';
            tabPickup.style.boxShadow = '0 4px 14px rgba(255,126,41,0.25)';

            tabDelivery.style.background = 'transparent';
            tabDelivery.style.color = 'var(--text-muted)';
            tabDelivery.style.boxShadow = 'none';

            pickupSection.style.display = 'flex';
            deliverySection.style.display = 'none';
            if (deliveryAddr) deliveryAddr.removeAttribute('required');
        } else {
            tabDelivery.style.background = 'var(--primary, #ff7e29)';
            tabDelivery.style.color = '#ffffff';
            tabDelivery.style.boxShadow = '0 4px 14px rgba(255,126,41,0.25)';

            tabPickup.style.background = 'transparent';
            tabPickup.style.color = 'var(--text-muted)';
            tabPickup.style.boxShadow = 'none';

            deliverySection.style.display = 'flex';
            pickupSection.style.display = 'none';
            if (deliveryAddr) deliveryAddr.setAttribute('required', 'required');
        }
    }

    function toggleCustomPickupTime(val) {
        const customInput = document.getElementById('customPickupTimeInput');
        if (customInput) {
            if (val === 'custom') {
                customInput.style.display = 'block';
                customInput.focus();
            } else {
                customInput.style.display = 'none';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
        if (checkedRadio) {
            updatePaymentCardStyles(checkedRadio);
        }

        const checkoutForm = document.getElementById('checkoutForm');
        const hiddenAddress = document.getElementById('hiddenAddressInput');
        const marketNames = "{{ !empty($distinctMarkets) ? implode(', ', $distinctMarkets) : 'Chợ Đông Anh' }}";
        
        if (checkoutForm && hiddenAddress) {
            checkoutForm.addEventListener('submit', function(e) {
                const mode = document.getElementById('fulfillmentModeInput').value;

                if (mode === 'pickup') {
                    const timeSelect = document.getElementById('pickupTimeSelect').value;
                    let finalTime = timeSelect;
                    if (timeSelect === 'custom') {
                        const customVal = document.getElementById('customPickupTimeInput').value.trim();
                        finalTime = customVal ? customVal : 'Lấy trong ngày';
                    }
                    const vehicle = document.getElementById('pickupVehicleInput').value.trim();
                    let addr = `[Ghé sạp lấy đồ] Tại ${marketNames} (Hẹn: ${finalTime}`;
                    if (vehicle) {
                        addr += `, Xe: ${vehicle}`;
                    }
                    addr += `)`;
                    hiddenAddress.value = addr;
                } else {
                    const deliveryAddr = document.getElementById('deliveryAddressInput').value.trim();
                    if (!deliveryAddr) {
                        e.preventDefault();
                        alert('Vui lòng nhập địa chỉ nhận hàng chi tiết!');
                        document.getElementById('deliveryAddressInput').focus();
                        return;
                    }
                    const deliveryTime = document.getElementById('deliveryTimeSelect').value;
                    hiddenAddress.value = `[Giao tận nơi] ${deliveryAddr} (Thời gian: ${deliveryTime})`;
                }
            });
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


