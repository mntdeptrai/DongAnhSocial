@extends('layouts.app')

@section('title', 'Lịch sử đơn hàng - Đông Anh Map')

@section('content')
<!-- Load premium styling for orders -->
<link rel="stylesheet" href="{{ asset('css/orders.css') }}?v={{ file_exists(public_path('css/orders.css')) ? filemtime(public_path('css/orders.css')) : '1.0.0' }}">

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
        <div style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #047857; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; border: 1px solid #a7f3d0; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);">
            <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3); animation: pulse-dot 1.5s infinite;"></span>
            Đồng bộ thời gian thực 10s • Hệ thống Chợ & Đồ Ăn Đông Anh
        </div>
        <h1 class="orders-title">
            📦 Lịch sử đơn hàng
        </h1>
        <p class="orders-subtitle">
            Theo dõi trạng thái đơn hàng, tiến trình đóng gói tại sạp và lịch sử giao nhận nhanh chóng.
        </p>

        <!-- Stats Dashboard (Updated dynamically by JS) -->
        <div class="stats-dashboard" id="orders-stats-dashboard">
            <!-- Loading skeletal cards initially -->
            <div class="stat-card stat-card-total">
                <div class="stat-icon-wrapper stat-icon-emerald">
                    <span class="stat-icon">📦</span>
                </div>
                <div class="stat-content">
                    <span class="stat-value text-emerald">...</span>
                    <span class="stat-label">Tổng đơn hàng</span>
                </div>
            </div>
            <div class="stat-card stat-card-processing">
                <div class="stat-icon-wrapper stat-icon-blue">
                    <span class="stat-icon">⏳</span>
                </div>
                <div class="stat-content">
                    <span class="stat-value text-blue">...</span>
                    <span class="stat-label">Đang xử lý</span>
                </div>
            </div>
            <div class="stat-card stat-card-completed">
                <div class="stat-icon-wrapper stat-icon-green">
                    <span class="stat-icon">✅</span>
                </div>
                <div class="stat-content">
                    <span class="stat-value text-green">...</span>
                    <span class="stat-label">Hoàn thành</span>
                </div>
            </div>
            <div class="stat-card stat-card-spent">
                <div class="stat-icon-wrapper stat-icon-orange">
                    <span class="stat-icon">💳</span>
                </div>
                <div class="stat-content">
                    <span class="stat-value text-orange">...</span>
                    <span class="stat-label">Tổng chi tiêu</span>
                </div>
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
<script defer src="{{ asset('js/orders.js') }}?v={{ file_exists(public_path('js/orders.js')) ? filemtime(public_path('js/orders.js')) : '1.0.0' }}"></script>
@endsection
