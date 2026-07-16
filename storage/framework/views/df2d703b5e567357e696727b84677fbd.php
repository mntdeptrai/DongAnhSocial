<?php $__env->startSection('title', 'Quản lý đơn hàng - Đông Anh Map'); ?>

<?php $__env->startSection('content'); ?>
<div class="container" style="max-width: 900px; padding: 40px 20px; font-family: 'Be Vietnam Pro', sans-serif;">
    
    <div style="margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 850; color: var(--text-main); margin: 0; font-family: var(--font-heading); display: flex; align-items: center; gap: 10px;">
                📦 Lịch sử đơn hàng
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin: 4px 0 0 0;">
                Xem danh sách và trạng thái các đơn hàng bạn đã đặt trên hệ thống Đông Anh Food Map & Market
            </p>
        </div>
        <a href="/tim-kiem" class="btn-secondary" style="padding: 10px 18px; font-size: 0.85rem; border-radius: 10px; font-weight: 700; text-decoration: none;">
            🗺️ Khám phá Bản đồ
        </a>
    </div>

    <?php if($orders->isEmpty()): ?>
        <div class="glass-panel" style="padding: 60px 40px; text-align: center; border-radius: 24px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.015); display: flex; flex-direction: column; align-items: center; gap: 16px; color: var(--text-muted);">
            <span style="font-size: 4rem; filter: grayscale(1); opacity: 0.6;">📦</span>
            <h3 style="margin: 0; color: var(--text-main); font-weight: 800; font-size: 1.25rem; font-family: var(--font-heading);">
                Bạn chưa có đơn hàng nào
            </h3>
            <p style="margin: 0; font-size: 0.9rem; max-width: 360px; line-height: 1.5;">
                Hãy chọn các món ăn ngon từ thực đơn nhà hàng hoặc đặc sản địa phương từ Chợ Đông Anh OCOP để đặt hàng nhé!
            </p>
            <a href="/tim-kiem" class="btn-primary" style="padding: 12px 28px; font-size: 0.9rem; border-radius: 12px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 15px rgba(255, 126, 41, 0.3); margin-top: 8px;">
                Bắt đầu mua sắm ➔
            </a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="glass-panel" style="border-radius: 20px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.015); box-shadow: 0 8px 32px rgba(0,0,0,0.04); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 40px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 8px 32px rgba(0,0,0,0.04)'">
                    
                    <!-- Order Card Header -->
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-glow); background: rgba(0,0,0,0.015); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading);">
                                #DA-<?php echo e(str_pad($order->id, 6, '0', STR_PAD_LEFT)); ?>

                            </span>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                📅 <?php echo e($order->created_at->format('d/m/Y H:i')); ?>

                            </span>
                        </div>
                        
                        <!-- Status Badges -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <?php if($order->status === 'paid'): ?>
                                <span style="background: rgba(46, 204, 113, 0.15); color: #27ae60; font-size: 0.78rem; font-weight: 800; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;">
                                    Đã thanh toán (Online)
                                </span>
                            <?php elseif($order->status === 'pending'): ?>
                                <span style="background: rgba(241, 196, 15, 0.15); color: #d4ac0d; font-size: 0.78rem; font-weight: 800; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;">
                                    Chờ xác nhận (COD)
                                </span>
                            <?php elseif($order->status === 'completed'): ?>
                                <span style="background: rgba(52, 152, 219, 0.15); color: #2980b9; font-size: 0.78rem; font-weight: 800; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;">
                                    Đã hoàn thành
                                </span>
                            <?php else: ?>
                                <span style="background: rgba(149, 165, 166, 0.15); color: #7f8c8d; font-size: 0.78rem; font-weight: 800; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;">
                                    <?php echo e($order->status); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Card Body -->
                    <div style="padding: 24px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px;">
                        
                        <!-- Items list -->
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <h4 style="margin: 0 0 6px 0; font-size: 0.92rem; font-weight: 800; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">Sản phẩm mua</h4>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; border-bottom: 1px solid rgba(0,0,0,0.02); padding-bottom: 8px;">
                                        <div style="display: flex; flex-direction: column;">
                                            <strong style="color: var(--text-main);"><?php echo e($item->name); ?></strong>
                                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                                Số lượng: <?php echo e($item->quantity); ?> x <?php echo e(number_format($item->price)); ?>đ
                                            </span>
                                        </div>
                                        <strong style="color: var(--text-main); font-size: 0.88rem; flex-shrink: 0;">
                                            <?php echo e(number_format($item->price * $item->quantity)); ?>đ
                                        </strong>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <!-- Delivery Info & Total -->
                        <div style="border-left: 1px solid var(--border-glow); padding-left: 24px; display: flex; flex-direction: column; justify-content: space-between; gap: 16px;">
                            <div>
                                <h4 style="margin: 0 0 10px 0; font-size: 0.92rem; font-weight: 800; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">Thông tin nhận hàng</h4>
                                <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem;">
                                    <div>
                                        <span style="color: var(--text-muted);">Người nhận:</span>
                                        <strong style="color: var(--text-main);"><?php echo e($order->customer_name); ?></strong>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-muted);">Số điện thoại:</span>
                                        <strong style="color: var(--text-main);"><?php echo e($order->customer_phone); ?></strong>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-muted);">Địa chỉ:</span>
                                        <span style="color: var(--text-main); word-break: break-all;"><?php echo e($order->shipping_address); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div style="border-top: 1px dashed var(--border-glow); padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted);">Tổng cộng:</span>
                                <strong style="color: var(--primary); font-size: 1.2rem; font-weight: 850; font-family: var(--font-heading);">
                                    <?php echo e(number_format($order->total_amount, 0, ',', '.')); ?>đ
                                </strong>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DA_DISCOVERY\resources\views/checkout/orders.blade.php ENDPATH**/ ?>