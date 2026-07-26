@extends('layouts.app')

@section('title', 'Lịch sử đơn hàng - Đông Anh Map')

@section('content')
<!-- Load premium styling for orders -->
<link rel="stylesheet" href="{{ asset('css/orders.css') }}?v={{ time() }}">

<div class="orders-container">
    
    <!-- Top Bar: Breadcrumbs & Back Button -->
    <div class="orders-top-bar">
        <div class="orders-breadcrumbs">
            <a href="/">Trang chủ</a>
            <span>/</span>
            <span>Lịch sử đơn hàng</span>
        </div>
        <a href="/" class="btn-back-home">
            <span class="back-arrow">←</span> Quay lại
        </a>
    </div>

    <!-- Header Section -->
    <div class="orders-header-section">
        <h1 class="orders-title">
            📦 Lịch sử đơn hàng
        </h1>
        <p class="orders-subtitle">
            Theo dõi trạng thái đơn hàng, chi tiết thanh toán và tiến trình giao hàng.
        </p>

        <!-- Stats Dashboard (Updated dynamically by JS) -->
        <div class="stats-dashboard" id="orders-stats-dashboard">
            <!-- Loading skeletal cards initially -->
            <div class="stat-card" style="opacity: 0.6;">
                <div class="stat-icon">📦</div>
                <span class="stat-value">...</span>
                <span class="stat-label">Tổng đơn hàng</span>
            </div>
            <div class="stat-card" style="opacity: 0.6;">
                <div class="stat-icon">⏳</div>
                <span class="stat-value">...</span>
                <span class="stat-label">Đang xử lý</span>
            </div>
            <div class="stat-card" style="opacity: 0.6;">
                <div class="stat-icon">✅</div>
                <span class="stat-value">...</span>
                <span class="stat-label">Hoàn thành</span>
            </div>
            <div class="stat-card" style="opacity: 0.6;">
                <div class="stat-icon">💳</div>
                <span class="stat-value">...</span>
                <span class="stat-label">Tổng chi tiêu</span>
            </div>
        </div>
    </div>

    <!-- Control Panel (Search & Date Range Filters) -->
    <div class="control-panel">
        <!-- Search bar -->
        <div class="search-box">
            <input type="text" id="search-order-input" placeholder="Tìm theo mã đơn #ORD001 hoặc tên món ăn...">
            <span class="search-icon">🔍</span>
        </div>

        <!-- Date Range Filter -->
        <div class="date-filters">
            <div class="date-picker-wrapper">
                <label for="filter-start-date">Từ ngày</label>
                <input type="date" id="filter-start-date">
            </div>
            <div class="date-picker-wrapper">
                <label for="filter-end-date">Đến ngày</label>
                <input type="date" id="filter-end-date">
            </div>
        </div>
    </div>

    <!-- Status Tabs Selector -->
    <div class="pills-container">
        <button class="pill-tab active" data-status="all">
            🌐 Tất cả <span class="pill-count" id="pill-count-all" style="display: none;">0</span>
        </button>
        <button class="pill-tab" data-status="pending">
            📋 Chờ xác nhận
        </button>
        <button class="pill-tab" data-status="paid">
            💳 Đã thanh toán
        </button>
        <button class="pill-tab" data-status="processing">
            🍳 Đang chuẩn bị
        </button>
        <button class="pill-tab" data-status="shipping">
            🚴 Đang giao
        </button>
        <button class="pill-tab" data-status="completed">
            ✅ Đã nhận <span class="pill-count" id="pill-count-completed" style="display: none;">0</span>
        </button>
        <button class="pill-tab" data-status="cancelled">
            🚫 Đã hủy
        </button>
        <button class="pill-tab" data-status="returned">
            🔄 Hoàn hàng
        </button>
    </div>

    <!-- Dynamic Orders List Container -->
    <div id="orders-list-container">
        <!-- Rendered dynamically by orders.js -->
    </div>

</div>

<!-- Core interactive logic -->
<script src="{{ asset('js/orders.js') }}?v={{ time() }}"></script>
@endsection
