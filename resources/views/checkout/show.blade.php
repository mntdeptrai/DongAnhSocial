@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . ' - Đông Anh Map')

@section('content')
<!-- Load premium styling for orders -->
<link rel="stylesheet" href="{{ asset('css/orders.css') }}?v={{ time() }}">

<div class="orders-container">
    
    <!-- Top Bar: Breadcrumbs & Back Button -->
    <div class="orders-top-bar">
        <div class="orders-breadcrumbs">
            <a href="/">Trang chủ</a>
            <span>/</span>
            <a href="/orders">Lịch sử đơn hàng</a>
            <span>/</span>
            <span>Chi tiết đơn hàng</span>
        </div>
        <a href="/orders" class="btn-back-home">
            <span class="back-arrow">←</span> Quay lại
        </a>
    </div>

    <!-- Header Section -->
    <div class="orders-header-section">
        <h1 class="orders-title">
            📦 Chi tiết đơn hàng #ORD{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
        </h1>
        <p class="orders-subtitle">
            Chi tiết các sản phẩm đặt mua, tiến trình giao nhận và lịch sử thanh toán của đơn hàng.
        </p>
    </div>

    <!-- Dynamic Detail Container (Rendered by orders.js) -->
    <div id="order-detail-container" data-order-id="{{ $order->id }}">
        <div style="text-align: center; padding: 80px 40px; color: #64748b;">
            <span style="display: inline-block; animation: spin 1s linear infinite; font-size: 2rem; margin-bottom: 12px;">⏳</span>
            <div style="font-weight: 600;">Đang kết nối tải chi tiết đơn hàng...</div>
        </div>
    </div>

</div>

<!-- Core interactive logic -->
<script src="{{ asset('js/orders.js') }}?v={{ time() }}"></script>
@endsection
