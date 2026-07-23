<?php $__env->startSection('title', 'Đặt hàng thành công - Đông Anh Map'); ?>

<?php $__env->startSection('content'); ?>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container" style="max-width: 800px; padding: 40px 20px; font-family: 'Be Vietnam Pro', sans-serif;">
    
    <?php if(session('success')): ?>
        <div class="glass-panel" style="background: rgba(46, 204, 113, 0.1); border-color: #2ecc71; padding: 14px 20px; border-radius: 16px; color: #2ecc71; margin-bottom: 30px; text-align: center; font-size: 0.95rem; font-weight: 600; border: 1.5px solid rgba(46, 204, 113, 0.25);">
            🎉 <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Top Success Header -->
    <div style="text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px; margin-bottom: 35px;" data-aos="fade-down">
        <div class="success-checkmark-circle">
            <i class="bi bi-check2-circle"></i>
        </div>
        <h2 style="font-size: 1.85rem; font-weight: 900; color: var(--text-main); margin: 0; font-family: var(--font-heading); letter-spacing: -0.5px;">
            Đặt Hàng Thành Công!
        </h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0; max-width: 580px; line-height: 1.6;">
            Hệ thống đã tiếp nhận yêu cầu của bạn. Đơn hàng đã được tách riêng theo từng **Gian hàng/Sạp tiểu thương** để phục vụ việc chuẩn bị và thanh toán chính xác nhất.
        </p>
    </div>

    <!-- Orders List Loop -->
    <div style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 40px;">
        <?php
            $displayOrders = isset($orders) && $orders->count() > 0 ? $orders : collect([$order]);
        ?>

        <?php $__currentLoopData = $displayOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $isMarketOrd = ($ord->category_slug === 'dong-anh-market');
                $eateryName = $ord->eatery 
                    ? ($ord->stall_name ? $ord->eatery->name . ' - ' . $ord->stall_name : $ord->eatery->name) 
                    : 'Cửa hàng';
                $orderCode = 'ORD' . str_pad($ord->id, 6, '0', STR_PAD_LEFT);
            ?>
            
            <div class="order-success-card" id="order-card-<?php echo e($ord->id); ?>" data-aos="fade-up" style="background: var(--bg-card); border: 1px solid var(--border-glow); border-radius: 24px; padding: 24px; box-shadow: 0 10px 25px -10px rgba(0,0,0,0.05); transition: all 0.3s; position: relative; overflow: hidden;">
                <!-- Left Accent Line -->
                <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: <?php echo e($ord->payment_method === 'Online' ? '#0ea5e9' : '#f59e0b'); ?>;"></div>

                <!-- Card Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; border-bottom: 1px dashed var(--border-glow); padding-bottom: 14px; margin-bottom: 16px;">
                    <div>
                        <div style="font-size: 0.78rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                            Mã đơn: <span style="font-family: monospace; color: var(--text-main); font-size: 0.85rem;">#<?php echo e($orderCode); ?></span>
                        </div>
                        <h4 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 6px;">
                            🏪 <?php echo e($eateryName); ?>

                        </h4>
                    </div>
                    <!-- Status Badge -->
                    <span class="status-badge-pill status-<?php echo e($ord->status); ?>" id="status-badge-<?php echo e($ord->id); ?>">
                        <?php echo e($ord->status === 'paid' ? 'Đã thanh toán' : ($ord->status === 'cancelled' ? 'Đã hủy' : 'Chờ xác nhận')); ?>

                    </span>
                </div>

                <!-- Items list -->
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                    <?php $__currentLoopData = $ord->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem;">
                            <div style="display: flex; flex-direction: column;">
                                <strong style="color: var(--text-main); font-weight: 600;"><?php echo e($item->name); ?></strong>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">Số lượng: <?php echo e($item->quantity); ?> x <?php echo e(number_format($item->price)); ?>đ</span>
                            </div>
                            <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-main);"><?php echo e(number_format($item->price * $item->quantity)); ?>đ</span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Shipping / Pickup Info -->
                <div style="background: rgba(0,0,0,0.015); border: 1px solid var(--border-glow); border-radius: 14px; padding: 14px; font-size: 0.82rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px;">
                    <div>👤 <strong>Người nhận:</strong> <?php echo e($ord->customer_name); ?> (<?php echo e($ord->customer_phone); ?>)</div>
                    <div>📍 <strong><?php echo e($isMarketOrd ? 'Nơi nhận đồ:' : 'Địa chỉ giao hàng:'); ?></strong> <?php echo e($ord->shipping_address); ?></div>
                    <div>💳 <strong>Phương thức:</strong> <?php echo e($ord->payment_method === 'COD' ? 'Tiền mặt khi nhận đồ (COD)' : 'Chuyển khoản VietQR sạp tiểu thương'); ?></div>
                    <?php if($ord->notes): ?>
                        <div>💬 <strong>Ghi chú:</strong> <span style="font-style: italic;">"<?php echo e($ord->notes); ?>"</span></div>
                    <?php endif; ?>
                </div>

                <!-- Pickup Code for Markets -->
                <?php if($isMarketOrd): ?>
                    <div class="pickup-container-success" id="pickup-container-<?php echo e($ord->id); ?>" style="margin-bottom: 20px; display: <?php echo e($ord->status === 'cancelled' ? 'none' : 'block'); ?>;">
                        <div style="background: rgba(46, 204, 113, 0.04); border: 2.2px dashed #2ecc71; border-radius: 16px; padding: 14px; text-align: center;">
                            <span style="font-size: 0.78rem; color: var(--text-muted); display: block; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Mã hẹn lấy đồ (Pickup Code)</span>
                            <strong style="font-size: 1.85rem; color: #2ecc71; font-family: monospace; display: block; margin: 4px 0; letter-spacing: 1px;">
                                MCP-<?php echo e(str_pad($ord->id, 5, '0', STR_PAD_LEFT)); ?>

                            </strong>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Đưa mã này cho chủ sạp khi bạn đến lấy đồ để nhận dạng nhanh đơn hàng.</span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Footer Summary & Actions -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; border-top: 1px solid var(--border-glow); padding-top: 16px;">
                    <div>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Tổng số tiền:</span>
                        <strong style="color: var(--primary); font-size: 1.25rem; font-weight: 800; font-family: var(--font-heading); display: block; line-height: 1.1;">
                            <?php echo e(number_format($ord->total_amount, 0, ',', '.')); ?>đ
                        </strong>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;" class="order-action-buttons" id="actions-<?php echo e($ord->id); ?>">
                        <?php if($ord->status === 'cancelled'): ?>
                            <span style="font-size: 0.85rem; color: #ef4444; font-weight: 700;"><i class="bi bi-x-circle-fill"></i> Đã hủy đơn hàng</span>
                        <?php else: ?>
                            <?php if($ord->payment_method === 'Online' && $ord->status === 'pending'): ?>
                                <a href="<?php echo e(route('checkout.payment', $ord->id)); ?>" class="btn-pay-stall">
                                    <i class="bi bi-qr-code"></i> Quét QR Thanh toán
                                </a>
                            <?php endif; ?>
                            <button type="button" onclick="cancelSuccessOrder(<?php echo e($ord->id); ?>)" class="btn-cancel-stall">
                                <i class="bi bi-trash3"></i> Hủy đơn
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Global Page Actions -->
    <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; border-top: 1px solid var(--border-glow); padding-top: 30px;" data-aos="fade-up">
        <a href="/" class="btn-global-secondary">
            <i class="bi bi-house"></i> Quay lại Trang chủ
        </a>
        <a href="/orders" class="btn-global-secondary" style="border-color: var(--primary); color: var(--primary);">
            <i class="bi bi-clock-history"></i> Lịch sử đơn hàng
        </a>
        <a href="/tim-kiem" class="btn-global-primary">
            <i class="bi bi-compass"></i> Tiếp tục khám phá
        </a>
    </div>

</div>

<style>
    /* Styling variables and specific styles for success page */
    .success-checkmark-circle {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: rgba(46, 204, 113, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.8rem;
        color: #2ecc71;
        box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4);
        animation: pulse-trust-ring 2s infinite ease-in-out;
    }

    @keyframes pulse-trust-ring {
        0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
        70% { box-shadow: 0 0 0 12px rgba(46, 204, 113, 0); }
        100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }

    /* Status Badges */
    .status-badge-pill {
        font-size: 0.72rem;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1.5px solid;
    }
    .status-pending {
        background: rgba(245, 158, 11, 0.06);
        color: #f59e0b;
        border-color: rgba(245, 158, 11, 0.2);
    }
    .status-paid {
        background: rgba(16, 185, 129, 0.06);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.2);
    }
    .status-cancelled {
        background: rgba(239, 68, 68, 0.06);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.2);
    }

    /* Buttons stylings */
    .btn-pay-stall {
        background: var(--primary, #ff7e29);
        border: 1.5px solid var(--primary, #ff7e29);
        color: #ffffff !important;
        padding: 8px 16px;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(255, 126, 41, 0.18);
        transition: all 0.2s;
    }
    .btn-pay-stall:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-cancel-stall {
        background: rgba(239, 68, 68, 0.04);
        border: 1.5px solid rgba(239, 68, 68, 0.2);
        color: #ef4444;
        padding: 8px 16px;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-cancel-stall:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    .btn-global-secondary {
        border: 1.5px solid var(--border-glow);
        background: transparent;
        color: var(--text-main);
        padding: 12px 24px;
        font-size: 0.88rem;
        font-weight: 700;
        border-radius: 14px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-global-secondary:hover {
        background: rgba(0, 0, 0, 0.02);
        transform: translateY(-1px);
    }

    .btn-global-primary {
        background: var(--primary, #ff7e29);
        color: #ffffff !important;
        padding: 12px 24px;
        font-size: 0.88rem;
        font-weight: 700;
        border-radius: 14px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 15px rgba(255, 126, 41, 0.2);
        transition: all 0.2s;
    }
    .btn-global-primary:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function cancelSuccessOrder(orderId) {
        if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
            return;
        }

        const buttonsDiv = document.getElementById(`actions-${orderId}`);
        const statusBadge = document.getElementById(`status-badge-${orderId}`);
        const pickupContainer = document.getElementById(`pickup-container-${orderId}`);

        // Disable buttons during request
        buttonsDiv.querySelectorAll('button, a').forEach(el => {
            el.style.pointerEvents = 'none';
            el.style.opacity = '0.5';
        });

        fetch(`/api/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update Badge UI
                statusBadge.className = 'status-badge-pill status-cancelled';
                statusBadge.innerText = 'ĐÃ HỦY';

                // Hide pickup code if visible
                if (pickupContainer) {
                    pickupContainer.style.display = 'none';
                }

                // Replace buttons with Cancelled label
                buttonsDiv.innerHTML = `<span style="font-size: 0.85rem; color: #ef4444; font-weight: 700;"><i class="bi bi-x-circle-fill"></i> Đã hủy đơn hàng</span>`;
                
                // Add minor nice visual feedback
                const card = document.getElementById(`order-card-${orderId}`);
                card.style.opacity = '0.85';
                card.style.borderColor = 'rgba(239, 68, 68, 0.15)';
            } else {
                alert('Không thể hủy đơn: ' + data.message);
                // Reset state
                buttonsDiv.querySelectorAll('button, a').forEach(el => {
                    el.style.pointerEvents = 'auto';
                    el.style.opacity = '1';
                });
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Có lỗi xảy ra, vui lòng thử lại.');
            buttonsDiv.querySelectorAll('button, a').forEach(el => {
                el.style.pointerEvents = 'auto';
                el.style.opacity = '1';
            });
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DA_DISCOVERY\resources\views/checkout/success.blade.php ENDPATH**/ ?>