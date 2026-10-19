<?php

namespace App\Contracts;

use App\Models\Order;

interface OrderInquiryServiceInterface
{
    /**
     * Create and persist a new customer inquiry / pre-order.
     *
     * @param array $data Validated input data
     * @return Order
     */
    public function createInquiry(array $data): Order;
}
