<?php $__env->startSection('title', 'Thanh toán đơn hàng - Đông Anh Map'); ?>

<?php $__env->startSection('content'); ?>
<div class="container" style="padding: 40px 20px; font-family: 'Be Vietnam Pro', sans-serif;">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px; font-family: var(--font-heading);">
            🛒 Thanh toán đặt hàng
        </h2>
        <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
            Vui lòng điền thông tin giao hàng để hoàn tất đơn hàng của bạn.
        </p>
    </div>

    <?php if($errors->any()): ?>
        <div class="glass-panel" style="background: rgba(231, 76, 60, 0.1); border-color: #e74c3c; padding: 16px; border-radius: 12px; color: #e74c3c; margin-bottom: 24px;">
            <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; align-items: start;">
        <!-- Left Column: Shipping Info & Payment -->
        <div class="glass-panel" style="padding: 30px; border-radius: 20px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.015);">
            <form action="<?php echo e(route('checkout.store')); ?>" method="POST" id="checkoutForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="items" value="<?php echo e(request()->query('items')); ?>">
                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                    📍 Thông tin giao nhận
                </h3>

                <div style="display: flex; flex-direction: column; gap: 18px; margin-bottom: 30px;">
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Họ và tên người nhận <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" required class="form-input" placeholder="Nhập tên người nhận..." value="<?php echo e(old('name', Auth::user()->name ?? '')); ?>" style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Số điện thoại <span style="color:#ef4444;">*</span></label>
                        <input type="tel" name="phone" required class="form-input" placeholder="Nhập số điện thoại nhận hàng..." value="<?php echo e(old('phone', Auth::user()->phone ?? '')); ?>" style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Địa chỉ giao hàng <span style="color:#ef4444;">*</span></label>
                        <textarea name="address" required rows="3" class="form-input" placeholder="Số nhà, tên đường, xã/thị trấn, Đông Anh, Hà Nội..." style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none; resize: vertical;"><?php echo e(old('address')); ?></textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">Ghi chú đơn hàng (Tùy chọn)</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Ghi chú về thời gian giao hàng, chỉ dẫn tìm đường..." style="width: 100%; padding: 12px 16px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none; resize: vertical;"><?php echo e(old('notes')); ?></textarea>
                    </div>
                </div>

                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                    💳 Phương thức thanh toán
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 30px;">
                    <label style="position: relative; border: 2px solid var(--border-glow); border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all 0.25s;" class="payment-method-card active">
                        <input type="radio" name="payment_method" value="COD" checked style="accent-color: var(--primary);" onchange="updatePaymentCardStyles(this)">
                        <div>
                            <strong style="display: block; font-size: 0.9rem; color: var(--text-main);">Thanh toán khi nhận hàng (COD)</strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Trả tiền mặt khi Shipper giao tới</span>
                        </div>
                    </label>

                    <label style="position: relative; border: 2px solid var(--border-glow); border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all 0.25s;" class="payment-method-card">
                        <input type="radio" name="payment_method" value="Online" style="accent-color: var(--primary);" onchange="updatePaymentCardStyles(this)">
                        <div>
                            <strong style="display: block; font-size: 0.9rem; color: var(--text-main);">Thanh toán trực tuyến (Simulated)</strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Cổng thanh toán giả lập bảo mật</span>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 16px; font-size: 1.05rem; font-weight: 800; border-radius: 12px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 8px 24px rgba(255,126,41,0.25);">
                    🚀 Xác nhận Đặt hàng
                </button>
            </form>
        </div>

        <!-- Right Column: Order Summary & Vouchers -->
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
                <?php
                    $groupedCartItems = collect($cartItems)->groupBy('eatery_id');
                ?>

                <div style="display: flex; flex-direction: column; gap: 20px; max-height: 380px; overflow-y: auto; padding-right: 4px; margin-bottom: 24px;">
                    <?php $__currentLoopData = $groupedCartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eateryId => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $firstItem = $items->first();
                            $eatery = null;
                            if ($firstItem['category_slug'] === 'dong-anh-market') {
                                $eatery = \App\Models\OcopProduct::on('mysql_market')->find($firstItem['ocop_product_id'])?->eatery;
                            } else {
                                $eatery = \App\Models\Dish::on('mysql')->find($firstItem['dish_id'])?->eatery;
                            }
                        ?>
                        <div style="border: 1px solid var(--border-glow); border-radius: 14px; padding: 14px; background: rgba(255,255,255,0.015);">
                            <div style="font-size: 0.82rem; font-weight: 700; color: var(--accent); margin-bottom: 10px; display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-glow); padding-bottom: 6px;">
                                <span>🏪 <?php echo e($eatery ? $eatery->name : 'Cửa hàng Đông Anh'); ?></span>
                                <span style="background: rgba(255, 126, 41, 0.1); padding: 1px 6px; border-radius: 4px; font-size: 0.72rem;">
                                    <?php echo e($firstItem['category_slug'] === 'dong-anh-market' ? 'Chợ & OCOP' : 'Ẩm thực'); ?>

                                </span>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <img src="<?php echo e($item['image']); ?>" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;" alt="">
                                        <div style="flex: 1; min-width: 0;">
                                            <h5 style="margin: 0; font-size: 0.85rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e($item['name']); ?></h5>
                                            <span style="font-size: 0.78rem; color: var(--text-muted);">SL: <?php echo e($item['quantity']); ?> x <?php echo e(number_format($item['price'])); ?>đ</span>
                                        </div>
                                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);"><?php echo e(number_format($item['total'])); ?>đ</span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div style="border-top: 1px dashed var(--border-glow); padding-top: 16px; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">Tạm tính:</span>
                        <span style="font-weight: 600; color: var(--text-main);" id="checkoutSubtotal"><?php echo e(number_format($subtotal, 0, ',', '.')); ?>đ</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; color: #2ecc71;">
                        <span>Giảm giá Voucher:</span>
                        <strong id="checkoutDiscount">
                            <?php if($bestVoucherApplied): ?>
                                -<?php echo e($bestVoucherApplied['discount']); ?>

                            <?php else: ?>
                                0đ
                            <?php endif; ?>
                        </strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.15rem; border-top: 1px solid var(--border-glow); padding-top: 12px; margin-top: 4px;">
                        <span style="font-weight: 700; color: var(--text-main);">Tổng thanh toán:</span>
                        <strong id="checkoutTotal" style="color: var(--primary); font-size: 1.3rem; font-weight: 800; font-family: var(--font-heading);">
                            <?php if($bestVoucherApplied): ?>
                                <?php echo e($bestVoucherApplied['total']); ?>

                            <?php else: ?>
                                <?php echo e(number_format($subtotal, 0, ',', '.')); ?>đ
                            <?php endif; ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Voucher Section -->
            <div class="glass-panel" style="padding: 24px; border-radius: 20px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.015);">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                    🏷️ Mã giảm giá Voucher
                </h3>

                <!-- Active Voucher Notification -->
                <div id="voucherStatusBanner" style="<?php echo e($bestVoucherApplied ? 'display: flex;' : 'display: none;'); ?> justify-content: space-between; align-items: center; background: rgba(46, 204, 113, 0.1); border: 1.5px solid rgba(46, 204, 113, 0.25); border-radius: 12px; padding: 12px 16px; margin-bottom: 18px; font-size: 0.85rem;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #27ae60; font-weight: 600;">
                        <span style="font-size: 1rem;">✅</span>
                        <span id="bannerTextSpan">
                            <?php if($bestVoucherApplied): ?>
                                Đã tự động áp dụng mã <strong><?php echo e($bestVoucherApplied['code']); ?></strong> (giảm <?php echo e((int)$bestVoucherApplied['percent']); ?>%)
                            <?php endif; ?>
                        </span>
                    </div>
                    <button type="button" onclick="removeVoucher()" style="background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.2); color: #e74c3c; padding: 4px 10px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s;">
                        ✕ Bỏ
                    </button>
                </div>

                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="text" id="voucherCodeInput" placeholder="Nhập mã ưu đãi..." value="<?php echo e($bestVoucherApplied ? $bestVoucherApplied['code'] : ''); ?>" style="flex: 1; padding: 10px 14px; border-radius: 10px; border: 1.5px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); outline: none; font-size: 0.88rem;">
                    <button type="button" onclick="applyVoucher()" class="btn-primary" style="padding: 10px 18px; font-size: 0.85rem; border-radius: 10px; cursor: pointer; font-weight: 700;">
                        Áp dụng
                    </button>
                </div>

                <?php if($vouchers->count() > 0): ?>
                    <div style="display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h4 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                🎁 Mã giảm giá khả dụng
                            </h4>
                            <button type="button" onclick="toggleVouchersList()" id="btnToggleVouchers" style="background: transparent; border: 1px solid var(--border-glow); padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 4px;">
                                <span id="toggleVouchersText">Thu gọn</span>
                                <span id="toggleVouchersArrow">▲</span>
                            </button>
                        </div>

                        <div id="vouchersListWrapper" style="display: flex; flex-direction: column; gap: 10px; max-height: 250px; overflow-y: auto; padding-right: 4px;">
                            <?php $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $isEligible = ($subtotal >= $voucher->min_order_amount);
                                    $isApplied = ($bestVoucherApplied && $bestVoucherApplied['code'] === $voucher->code);
                                ?>
                                <div class="voucher-card-item <?php echo e($isApplied ? 'applied' : ($isEligible ? 'eligible' : 'ineligible')); ?>" 
                                     data-code="<?php echo e($voucher->code); ?>" 
                                     data-eligible="<?php echo e($isEligible ? 'true' : 'false'); ?>"
                                     style="display: flex; align-items: center; border: 1.5px solid <?php echo e($isApplied ? '#2ecc71' : 'var(--border-glow)'); ?>; border-radius: 12px; background: <?php echo e($isApplied ? 'rgba(46, 204, 113, 0.03)' : 'rgba(255,255,255,0.01)'); ?>; padding: 12px; position: relative; overflow: hidden; gap: 12px; transition: all 0.25s; opacity: <?php echo e($isEligible ? '1' : '0.65'); ?>;">
                                    
                                    <!-- Left dashed border separator to simulate coupon ticket -->
                                    <div style="position: absolute; left: 74px; top: 0; bottom: 0; width: 1px; border-left: 2px dashed var(--border-glow); z-index: 1;"></div>
                                    
                                    <!-- Left graphic (orange icon representation) -->
                                    <div style="width: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; flex-shrink: 0; z-index: 2;">
                                        <span style="font-size: 1.3rem;">🏷️</span>
                                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--primary);"><?php echo e((int)$voucher->percentage); ?>%</span>
                                    </div>
                                    
                                    <!-- Middle content (coupon details) -->
                                    <div style="flex: 1; padding-left: 12px; min-width: 0; z-index: 2; display: flex; flex-direction: column; gap: 3px;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <strong style="font-size: 0.9rem; color: var(--text-main); font-family: var(--font-heading);"><?php echo e($voucher->code); ?></strong>
                                            <span class="badge-applied" style="background: rgba(46, 204, 113, 0.15); color: #2ecc71; font-size: 0.65rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: <?php echo e($isApplied ? 'inline-block' : 'none'); ?>;">Đang dùng</span>
                                        </div>
                                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; font-weight: 500;">
                                            Giảm <?php echo e((int)$voucher->percentage); ?>% cho đơn từ <?php echo e(number_format($voucher->min_order_amount)); ?>đ
                                        </span>
                                        <?php if(!$isEligible): ?>
                                            <span style="font-size: 0.7rem; color: #e74c3c; font-weight: 600; display: flex; align-items: center; gap: 3px;">
                                                ⚠️ Chưa đủ điều kiện (Thiếu <?php echo e(number_format($voucher->min_order_amount - $subtotal)); ?>đ)
                                            </span>
                                        <?php endif; ?>
                                        <span style="font-size: 0.68rem; color: var(--text-muted); display: block; opacity: 0.8;">
                                            HSD: 31/12/2026
                                        </span>
                                    </div>
                                    
                                    <!-- Right action button -->
                                    <div style="z-index: 2; flex-shrink: 0;">
                                        <?php if($isApplied): ?>
                                            <button type="button" onclick="removeVoucher()" class="btn-voucher-action" style="border: 1.5px solid #2ecc71; background: transparent; color: #2ecc71; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                                Bỏ
                                            </button>
                                        <?php elseif($isEligible): ?>
                                            <button type="button" onclick="selectVoucher('<?php echo e($voucher->code); ?>')" class="btn-voucher-action" style="border: none; background: var(--primary-grad); color: white; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(255,126,41,0.2);">
                                                Áp dụng
                                            </button>
                                        <?php else: ?>
                                            <button type="button" disabled class="btn-voucher-action" style="border: 1.5px solid #e2e8f0; background: #f1f5f9; color: #94a3b8; padding: 6px 10px; border-radius: 8px; font-size: 0.72rem; font-weight: 600; cursor: not-allowed;">
                                                Không đủ ĐK
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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

    function selectVoucher(code) {
        const input = document.getElementById('voucherCodeInput');
        if (input) {
            input.value = code;
            applyVoucher();
        }
    }

    function toggleVouchersList() {
        const list = document.getElementById('vouchersListWrapper');
        const text = document.getElementById('toggleVouchersText');
        const arrow = document.getElementById('toggleVouchersArrow');
        if (list && text && arrow) {
            if (list.style.display === 'none') {
                list.style.display = 'flex';
                text.textContent = 'Thu gọn';
                arrow.textContent = '▲';
            } else {
                list.style.display = 'none';
                text.textContent = 'Xem thêm';
                arrow.textContent = '▼';
            }
        }
    }

    function applyVoucher() {
        const codeInput = document.getElementById('voucherCodeInput');
        if (!codeInput) return;
        const code = codeInput.value.trim();
        if (!code) {
            alert('Vui lòng nhập mã voucher');
            return;
        }

        const items = document.querySelector('input[name="items"]')?.value || '';

        fetch('<?php echo e(route("checkout.apply-voucher")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ voucher_code: code, items: items })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => { throw new Error(data.message || 'Lỗi áp dụng voucher'); });
            }
            return res.json();
        })
        .then(res => {
            if (res.success) {
                // Update pricing summary
                document.getElementById('checkoutDiscount').innerText = '-' + res.voucher.discount;
                document.getElementById('checkoutTotal').innerText = res.voucher.total;
                
                // Show banner
                const banner = document.getElementById('voucherStatusBanner');
                const bannerText = document.getElementById('bannerTextSpan');
                if (banner && bannerText) {
                    banner.style.display = 'flex';
                    bannerText.innerHTML = `Đã áp dụng mã <strong>${res.voucher.code}</strong> (giảm ${parseInt(res.voucher.percent)}%)`;
                }

                // Update input
                codeInput.value = res.voucher.code;

                // Update cards status dynamically
                updateVoucherCardsUI(res.voucher.code);

                alert(res.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message || 'Mã giảm giá không hợp lệ hoặc không đủ điều kiện đơn hàng.');
        });
    }

    function removeVoucher() {
        const items = document.querySelector('input[name="items"]')?.value || '';

        fetch('<?php echo e(route("checkout.remove-voucher")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: items })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => { throw new Error(data.message || 'Lỗi hủy voucher'); });
            }
            return res.json();
        })
        .then(res => {
            if (res.success) {
                // Update pricing summary
                document.getElementById('checkoutDiscount').innerText = '0đ';
                document.getElementById('checkoutTotal').innerText = res.voucher.total;
                
                // Hide banner
                const banner = document.getElementById('voucherStatusBanner');
                if (banner) {
                    banner.style.display = 'none';
                }

                // Clear input
                const codeInput = document.getElementById('voucherCodeInput');
                if (codeInput) {
                    codeInput.value = '';
                }

                // Reset cards status dynamically
                updateVoucherCardsUI(null);

                alert(res.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message || 'Có lỗi xảy ra khi hủy mã giảm giá.');
        });
    }

    function updateVoucherCardsUI(appliedCode) {
        document.querySelectorAll('.voucher-card-item').forEach(card => {
            const cardCode = card.getAttribute('data-code');
            const isEligible = card.getAttribute('data-eligible') === 'true';
            const actionBtnWrapper = card.querySelector('div:last-child');
            const badge = card.querySelector('.badge-applied');

            if (cardCode === appliedCode) {
                card.style.borderColor = '#2ecc71';
                card.style.background = 'rgba(46, 204, 113, 0.03)';
                if (badge) badge.style.display = 'inline-block';
                
                if (actionBtnWrapper) {
                    actionBtnWrapper.innerHTML = `<button type="button" onclick="removeVoucher()" class="btn-voucher-action" style="border: 1.5px solid #2ecc71; background: transparent; color: #2ecc71; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">Bỏ</button>`;
                }
            } else {
                card.style.borderColor = 'var(--border-glow)';
                card.style.background = 'rgba(255, 255, 255, 0.01)';
                if (badge) badge.style.display = 'none';

                if (actionBtnWrapper) {
                    if (isEligible) {
                        actionBtnWrapper.innerHTML = `<button type="button" onclick="selectVoucher('${cardCode}')" class="btn-voucher-action" style="border: none; background: var(--primary-grad); color: white; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(255,126,41,0.2);">Áp dụng</button>`;
                    } else {
                        actionBtnWrapper.innerHTML = `<button type="button" disabled class="btn-voucher-action" style="border: 1.5px solid #e2e8f0; background: #f1f5f9; color: #94a3b8; padding: 6px 10px; border-radius: 8px; font-size: 0.72rem; font-weight: 600; cursor: not-allowed;">Không đủ ĐK</button>`;
                    }
                }
            }
        });
    }
</script>
<style>
    .payment-method-card {
        border-color: var(--border-glow);
    }
    .payment-method-card.active {
        border-color: var(--primary) !important;
        background: rgba(255, 126, 41, 0.03) !important;
    }
    .voucher-card-item {
        transition: all 0.25s ease;
    }
    .voucher-card-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .btn-voucher-action {
        transition: all 0.2s ease;
    }
    .btn-voucher-action:hover:not(:disabled) {
        opacity: 0.9;
        transform: scale(1.02);
    }
</style>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DA_DISCOVERY\resources\views/checkout/index.blade.php ENDPATH**/ ?>