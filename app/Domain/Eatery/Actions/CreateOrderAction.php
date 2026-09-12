<?php

namespace App\Domain\Eatery\Actions;

class CreateOrderAction
{
    public function execute(array $orderData): array
    {
        $orderId = 'ORD-' . time();
        
        return [
            'success'  => true,
            'order_id' => $orderId,
            'message'  => 'Tạo đơn hàng thành công',
        ];
    }
}
