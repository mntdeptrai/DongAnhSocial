<?php

namespace App\Services;

use App\Contracts\OrderInquiryServiceInterface;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderInquiryService implements OrderInquiryServiceInterface
{
    /**
     * Create and persist a new customer inquiry / pre-order.
     */
    public function createInquiry(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $productName = trim($data['product_name'] ?? 'Đặc sản Đông Anh');
            $customerName = trim($data['customer_name'] ?? 'Khách hàng');
            $phone = trim($data['phone'] ?? '');
            $quantityText = trim($data['quantity'] ?? '1');
            $note = trim($data['note'] ?? '');

            // Parse integer quantity if possible
            $parsedQty = (int) filter_var($quantityText, FILTER_SANITIZE_NUMBER_INT);
            if ($parsedQty <= 0) {
                $parsedQty = 1;
            }

            // Create Order record in Database
            $order = Order::create([
                'user_id'          => Auth::id(),
                'eatery_id'        => 0, // General Agricultural & Craft producer
                'category_slug'    => 'nong-san-dong-anh',
                'stall_name'       => 'Nông Sản & Đặc Sản Đông Anh',
                'customer_name'    => $customerName,
                'customer_phone'   => $phone,
                'seller_phone'     => '0983882119',
                'shipping_address' => !empty($note) ? $note : 'Liên hệ xác nhận qua điện thoại',
                'total_amount'     => 0.00, // Quote on callback
                'payment_method'   => 'Liên hệ báo giá',
                'status'           => 'pending',
                'is_reviewed'      => 0,
                'notes'            => "Yêu cầu đặt mua: {$productName} (SL: {$quantityText}). Ghi chú: {$note}",
            ]);

            // Create OrderItem line item
            OrderItem::create([
                'order_id'         => $order->id,
                'dish_id'          => null,
                'ocop_product_id'  => null,
                'name'             => $productName,
                'price'            => 0.00,
                'quantity'         => $parsedQty,
            ]);

            Log::info("Nong San Order Inquiry #{$order->id} created successfully for {$customerName} ({$phone}) - Product: {$productName}");

            return $order;
        });
    }
}
