<?php $__env->startSection('title', 'Đặt hàng thành công - Đông Anh Map'); ?>

<?php $__env->startSection('content'); ?>
<div class="container" style="max-width: 700px; padding: 50px 20px; font-family: 'Be Vietnam Pro', sans-serif;">
    
    <?php if(session('success')): ?>
        <div class="glass-panel" style="background: rgba(46, 204, 113, 0.1); border-color: #2ecc71; padding: 14px 20px; border-radius: 12px; color: #2ecc71; margin-bottom: 24px; text-align: center; font-size: 0.95rem; font-weight: 600;">
            🎉 <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

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

        <?php
            $isMarketOrder = ($order->category_slug === 'dong-anh-market');
        ?>

        <!-- Pickup code for Market orders -->
        <?php if($isMarketOrder): ?>
            <div style="background: rgba(46, 204, 113, 0.05); border: 2px dashed #2ecc71; border-radius: 16px; padding: 20px; text-align: center;">
                <span style="font-size: 0.85rem; color: var(--text-muted); display: block; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Mã hẹn lấy đồ (Pickup Code)</span>
                <strong style="font-size: 2.2rem; color: #2ecc71; font-family: monospace; display: block; margin: 6px 0; letter-spacing: 2px;">
                    MCP-<?php echo e(str_pad($order->id, 5, '0', STR_PAD_LEFT)); ?>

                </strong>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0; line-height: 1.4;">
                    Vui lòng chụp lại màn hình hoặc lưu mã **MCP-<?php echo e(str_pad($order->id, 5, '0', STR_PAD_LEFT)); ?>** này. Khi qua chợ, hãy xuất trình mã này cho các tiểu thương tương ứng để nhận đồ đã chuẩn bị sẵn!
                </p>
            </div>
        <?php endif; ?>

        <!-- Order Information Receipt -->
        <div style="border: 1px solid var(--border-glow); border-radius: 18px; padding: 24px; background: rgba(255,255,255,0.01);">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-top: 0; margin-bottom: 16px; border-bottom: 1px dashed var(--border-glow); padding-bottom: 10px; font-family: var(--font-heading);">
                🧾 Thông tin đơn hàng #DA-<?php echo e(str_pad($order->id, 6, '0', STR_PAD_LEFT)); ?>

            </h3>
 
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Trạng thái đơn hàng:</span>
                    <strong style="color: #2ecc71; text-transform: uppercase;">
                        <?php if($order->status === 'paid'): ?>
                            Đã thanh toán (Online)
                        <?php elseif($order->status === 'pending'): ?>
                            Chờ xác nhận (COD)
                        <?php else: ?>
                            <?php echo e($order->status); ?>

                        <?php endif; ?>
                    </strong>
                </div>
 
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Phương thức thanh toán:</span>
                    <strong style="color: var(--text-main);">
                        <?php if($isMarketOrder): ?>
                            <?php echo e($order->payment_method === 'COD' ? 'Tiền mặt khi nhận đồ (COD)' : 'Chuyển khoản VietQR của Tiểu thương'); ?>

                        <?php else: ?>
                            <?php echo e($order->payment_method === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : 'Thanh toán trực tuyến'); ?>

                        <?php endif; ?>
                    </strong>
                </div>
 
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Thời gian đặt hàng:</span>
                    <strong style="color: var(--text-main);"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></strong>
                </div>
 
                <div style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-glow); padding-top: 12px; margin-top: 4px;">
                    <span style="color: var(--text-muted);">Họ tên người nhận:</span>
                    <strong style="color: var(--text-main);"><?php echo e($order->customer_name); ?></strong>
                </div>
 
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Số điện thoại:</span>
                    <strong style="color: var(--text-main);"><?php echo e($order->customer_phone); ?></strong>
                </div>
 
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);"><?php echo e($isMarketOrder ? 'Địa điểm lấy đồ:' : 'Địa chỉ giao hàng:'); ?></span>
                    <strong style="color: var(--text-main); text-align: right; max-width: 300px; word-break: break-word;"><?php echo e($order->shipping_address); ?></strong>
                </div>
 
                <?php if($order->notes): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Ghi chú của bạn:</span>
                        <span style="color: var(--text-main); font-style: italic;">"<?php echo e($order->notes); ?>"</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Breakdown List -->
        <div style="border: 1px solid var(--border-glow); border-radius: 18px; padding: 24px; background: rgba(255,255,255,0.01);">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-top: 0; margin-bottom: 16px; border-bottom: 1px dashed var(--border-glow); padding-bottom: 10px; font-family: var(--font-heading);">
                📦 Danh sách sản phẩm mua
            </h3>

            <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px;">
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem;">
                        <div style="display: flex; flex-direction: column;">
                            <strong style="color: var(--text-main);"><?php echo e($item->name); ?></strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Số lượng: <?php echo e($item->quantity); ?> x <?php echo e(number_format($item->price)); ?>đ</span>
                        </div>
                        <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-main);"><?php echo e(number_format($item->price * $item->quantity)); ?>đ</span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div style="border-top: 1px solid var(--border-glow); padding-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);">Tổng thanh toán thực tế:</span>
                <strong style="color: var(--primary); font-size: 1.25rem; font-weight: 800; font-family: var(--font-heading);">
                    <?php echo e(number_format($order->total_amount, 0, ',', '.')); ?>đ
                </strong>
            </div>
        </div>

        <!-- Return Actions -->
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; border-top: 1px solid var(--border-glow); padding-top: 24px;">
            <a href="/" class="btn-secondary" style="padding: 12px 20px; font-size: 0.88rem; border-radius: 10px; font-weight: 700; text-decoration: none; border: 1.5px solid rgba(0,0,0,0.1); color: var(--text-main); background: transparent;">
                🏠 Trang chủ
            </a>
            <a href="/orders/<?php echo e($order->id); ?>" class="btn-secondary" style="padding: 12px 20px; font-size: 0.88rem; border-radius: 10px; font-weight: 700; text-decoration: none; border: 1.5px solid var(--primary, #ff7e29); color: var(--primary, #ff7e29); background: transparent;">
                🔍 Chi tiết đơn hàng
            </a>
            <a href="/tim-kiem" class="btn-primary" style="padding: 12px 20px; font-size: 0.88rem; border-radius: 10px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 15px rgba(255, 126, 41, 0.25);">
                🗺️ Tiếp tục khám phá
            </a>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
    @keyframes pulse-trust {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DA_DISCOVERY\resources\views/checkout/success.blade.php ENDPATH**/ ?>