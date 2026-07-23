<?php
    $isMarketOrder = ($order->category_slug === 'dong-anh-market');
    $stallsPaymentData = [];
    
    if ($isMarketOrder) {
        $orderItems = $order->items;
        foreach ($orderItems as $item) {
            $product = \App\Models\OcopProduct::on('mysql_market')->find($item->ocop_product_id);
            if ($product) {
                $stallName = $product->stall_name ?: 'Gian hàng';
                if (!isset($stallsPaymentData[$stallName])) {
                    $sellerName = 'Tiểu thương';
                    if (preg_match('/Chủ hộ: (.*?)\./', $product->description, $matches)) {
                        $sellerName = $matches[1];
                    }
                    
                    $bankInfo = '';
                    if (preg_match('/ngân hàng (.*?)\./', $product->description, $matches)) {
                        $bankInfo = $matches[1];
                    }
                    
                    $stallsPaymentData[$stallName] = [
                        'seller_name' => $sellerName,
                        'bank_info' => $bankInfo,
                        'items' => [],
                        'total' => 0
                    ];
                }
                $stallsPaymentData[$stallName]['items'][] = $item;
                $stallsPaymentData[$stallName]['total'] += ($item->price * $item->quantity);
            }
        }
    }
?>

<?php $__env->startSection('title', 'Cổng thanh toán giả lập - Đông Anh Map'); ?>

<?php $__env->startSection('content'); ?>
<div class="container" style="max-width: 600px; padding: 50px 20px; font-family: 'Be Vietnam Pro', sans-serif;">
    <?php if(session('error')): ?>
        <div class="glass-panel" style="background: rgba(239, 68, 68, 0.1); border-color: #ef4444; padding: 14px 20px; border-radius: 12px; color: #ef4444; margin-bottom: 24px; text-align: center; font-size: 0.95rem; font-weight: 600; border: 1.5px solid #ef4444;">
            ❌ <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
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
        <?php if($isMarketOrder): ?>
            <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">
                <p style="font-size: 0.88rem; color: var(--text-muted); text-align: left; margin: 0 0 4px 0; line-height: 1.4; background: rgba(14, 165, 233, 0.05); border: 1px dashed rgba(14, 165, 233, 0.2); border-radius: 10px; padding: 10px;">
                    💡 Đơn đặt hàng của bạn gồm các sản phẩm từ nhiều chủ quầy khác nhau. Vui lòng quét mã chuyển khoản cho **từng chủ quầy tương ứng** để thanh toán:
                </p>
                
                <?php $__currentLoopData = $stallsPaymentData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stallName => $stallData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $bankId = '';
                        $accountNo = '';
                        if ($stallData['bank_info'] && strpos($stallData['bank_info'], ':') !== false) {
                            list($bName, $bAcc) = explode(':', $stallData['bank_info']);
                            $bankId = trim($bName);
                            $accountNo = trim($bAcc);
                        }
                    ?>
                    
                    <div style="background: rgba(255, 255, 255, 0.02); border: 1.5px solid var(--border-glow); border-radius: 18px; padding: 20px; text-align: left; display: flex; flex-direction: column; gap: 14px;">
                        <!-- Stall Header -->
                        <div style="border-bottom: 1px dashed var(--border-glow); padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="font-size: 0.95rem; color: var(--primary); display: block;">🏪 <?php echo e($stallName); ?></strong>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">👤 Chủ hộ: <?php echo e($stallData['seller_name']); ?></span>
                            </div>
                            <span style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 800; color: var(--primary);">
                                <?php echo e(number_format($stallData['total'], 0, ',', '.')); ?>đ
                            </span>
                        </div>
                        
                        <!-- Stall Items list -->
                        <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
                            <?php $__currentLoopData = $stallData['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>• <?php echo e($item->name); ?> (SL: <?php echo e($item->quantity); ?>)</div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <!-- QR Code / Cash payment -->
                        <?php if($bankId && $accountNo): ?>
                            <div style="display: flex; gap: 16px; align-items: center; background: rgba(14, 165, 233, 0.02); border: 1px solid var(--border-glow); border-radius: 12px; padding: 12px; flex-wrap: wrap;">
                                <div style="background: #ffffff; padding: 8px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); flex-shrink: 0; margin: 0 auto;">
                                    <img src="https://img.vietqr.io/image/<?php echo e($bankId); ?>-<?php echo e($accountNo); ?>-compact2.png?amount=<?php echo e($stallData['total']); ?>&addInfo=DH%20DA%20<?php echo e($order->id); ?>%20<?php echo e(urlencode($stallName)); ?>&accountName=<?php echo e(urlencode($stallData['seller_name'])); ?>" alt="VietQR" style="width: 130px; height: 130px; display: block; mix-blend-mode: multiply;">
                                </div>
                                <div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 180px;">
                                    <div>🏦 <strong>Ngân hàng:</strong> <?php echo e($bankId); ?></div>
                                    <div>💳 <strong>Số tài khoản:</strong> <code style="font-family: monospace; font-size: 0.95rem; color: var(--text-main); font-weight: 700;"><?php echo e($accountNo); ?></code></div>
                                    <div>👤 <strong>Tên TK:</strong> <?php echo e($stallData['seller_name']); ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="background: rgba(241, 196, 15, 0.05); border: 1px dashed rgba(241, 196, 15, 0.3); border-radius: 12px; padding: 12px; font-size: 0.82rem; color: var(--text-muted); text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                💵 <span>Gian hàng này nhận tiền mặt trực tiếp tại quầy khi qua lấy đồ</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div style="background: rgba(255, 255, 255, 0.02); border: 1.5px solid var(--border-glow); border-radius: 18px; padding: 24px; margin-bottom: 30px; display: flex; flex-direction: column; align-items: center; gap: 16px;">
                <!-- QR code generated dynamically via API -->
                <div style="background: #ffffff; padding: 14px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 12px rgba(0,0,0,0.06); cursor: pointer;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://donganh.hanoi.gov.vn/payment-simulated-order-<?php echo e($order->id); ?>" alt="QR Code Payment" style="width: 170px; height: 170px; display: block; mix-blend-mode: multiply;">
                </div>
                
                <div style="width: 100%; text-align: left; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; border-top: 1px dashed var(--border-glow); padding-top: 16px; margin-top: 8px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Mã đơn hàng:</span>
                        <strong style="color: var(--text-main);">#DA-<?php echo e(str_pad($order->id, 6, '0', STR_PAD_LEFT)); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Đơn vị thụ hưởng:</span>
                        <strong style="color: var(--text-main);">Đông Anh Map Discovery System</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.1rem; border-top: 1px solid var(--border-glow); padding-top: 10px; margin-top: 4px;">
                        <span style="color: var(--text-muted); font-weight: 700;">Số tiền cần chuyển:</span>
                        <strong style="color: var(--primary); font-size: 1.25rem; font-weight: 800; font-family: var(--font-heading);">
                            <?php echo e(number_format($order->total_amount, 0, ',', '.')); ?>đ
                        </strong>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Simulation Actions Form -->
        <form action="<?php echo e(route('checkout.process-payment', $order->id)); ?>" method="POST" id="paymentProcessForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="simulate_success" id="simulateSuccessInput" value="1">
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <button type="button" onclick="submitPayment(true)" class="btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; font-weight: 700; border-radius: 12px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(255, 126, 41, 0.2);">
                    ✅ Xác nhận Đã chuyển khoản (Thành công)
                </button>
                
                <button type="button" onclick="submitPayment(false)" class="btn-global-secondary" style="width: 100%; padding: 12px; font-size: 0.9rem; font-weight: 700; border-radius: 12px; cursor: pointer; border-color: rgba(0,0,0,0.1); color: var(--text-muted); background: transparent; transition: all 0.2s; justify-content: center;">
                    ⚠️ Báo lỗi thanh toán (Không hủy đơn)
                </button>

                <button type="button" onclick="cancelOrderEntirely(<?php echo e($order->id); ?>)" class="btn-secondary" style="width: 100%; padding: 12px; font-size: 0.9rem; font-weight: 700; border-radius: 12px; cursor: pointer; border-color: rgba(239, 68, 68, 0.2); color: #ef4444; background: rgba(239, 68, 68, 0.04); transition: all 0.2s; display: flex; justify-content: center; align-items: center;" onmouseover="this.style.background='rgba(239, 68, 68, 0.08)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.04)'">
                    ❌ Hủy đơn hàng này
                </button>
            </div>
        </form>
        
        <div style="margin-top: 20px; display: flex; justify-content: center; gap: 16px;">
            <a href="/" style="font-size: 0.85rem; color: var(--text-muted); text-decoration: underline;">
                🏠 Quay lại Trang chủ
            </a>
            <span style="color: var(--border-glow);">|</span>
            <a href="/orders" style="font-size: 0.85rem; color: var(--text-muted); text-decoration: underline;">
                📦 Danh sách đơn hàng
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
                cancelOrderEntirely(<?php echo e($order->id); ?>, false);
            }

            timeRemaining--;
        }, 1000);
    });

    function submitPayment(success) {
        document.getElementById('simulateSuccessInput').value = success ? '1' : '0';
        document.getElementById('paymentProcessForm').submit();
    }

    function cancelOrderEntirely(orderId, prompt = true) {
        if (prompt && !confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
            return;
        }
        
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
                if (prompt) alert('Đơn hàng đã được hủy thành công.');
                window.location.href = '/orders';
            } else {
                alert('Không thể hủy đơn: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Lỗi khi hủy đơn:', err);
            alert('Có lỗi xảy ra, vui lòng thử lại.');
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DA_DISCOVERY\resources\views/checkout/payment.blade.php ENDPATH**/ ?>