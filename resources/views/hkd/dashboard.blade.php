@extends('layouts.hkd')

@section('title', 'Tổng Quan Quản Trị Kinh Doanh — ' . $eatery->name)
@section('title_header', 'Tổng Quan Quản Trị Kinh Doanh')

@section('content')

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .tendoo-dash-container {
        max-width: 1280px;
        margin: 0 auto;
        padding-bottom: 40px;
        font-family: var(--font-body, system-ui, -apple-system, sans-serif);
    }

    /* TOP GREETING & FILTER BAR */
    .tendoo-greeting-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }

    .tendoo-greeting-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .tendoo-greeting-sub {
        font-size: 0.88rem;
        color: #64748b;
        font-weight: 600;
    }

    .tendoo-filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
    }

    .tendoo-date-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tendoo-date-pill:hover {
        border-color: #dc2626;
        background: #ffffff;
    }

    /* DATE PICKER POPOVER STYLES */
    .tendoo-date-popover {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 100;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 16px;
        box-shadow: 0 12px 36px rgba(0,0,0,0.15);
        display: flex;
        overflow: hidden;
        min-width: 580px;
        animation: popoverFadeIn 0.2s ease forwards;
    }

    @keyframes popoverFadeIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .tendoo-popover-sidebar {
        width: 140px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        padding: 10px 0;
        display: flex;
        flex-direction: column;
    }

    .tendoo-popover-opt {
        padding: 9px 16px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .tendoo-popover-opt:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .tendoo-popover-opt.active {
        background: #ffffff;
        color: #dc2626;
        border-left: 3px solid #dc2626;
    }

    .tendoo-popover-calendar-body {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .tendoo-calendar-grid-wrap {
        display: flex;
        gap: 20px;
    }

    .tendoo-cal-month-title {
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
        text-align: center;
        margin-bottom: 10px;
    }

    .tendoo-cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        text-align: center;
        font-size: 0.78rem;
    }

    .tendoo-cal-head {
        font-weight: 800;
        color: #94a3b8;
        padding-bottom: 4px;
    }

    .tendoo-cal-day {
        padding: 6px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        color: #334155;
    }
    .tendoo-cal-day:hover {
        background: #fee2e2;
        color: #dc2626;
    }
    .tendoo-cal-day.selected {
        background: #dc2626;
        color: #ffffff;
        font-weight: 800;
    }
    .tendoo-cal-day.in-range {
        background: #fef2f2;
        color: #dc2626;
    }
    .tendoo-cal-day.muted {
        color: #cbd5e1;
        cursor: default;
    }

    .tendoo-compare-hint {
        font-size: 0.82rem;
        color: #64748b;
        font-weight: 600;
    }

    .tendoo-btn-red {
        background: #dc2626;
        color: #ffffff;
        border: none;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
        text-decoration: none;
    }
    .tendoo-btn-red:hover { background: #b91c1c; color: #fff; }

    .tendoo-btn-light {
        background: #ffffff;
        color: #334155;
        border: 1.5px solid #cbd5e1;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    .tendoo-btn-light:hover { background: #f8fafc; border-color: #94a3b8; }

    /* 4 KEY KPI CARDS GRID */
    .tendoo-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    @media (max-width: 1024px) {
        .tendoo-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .tendoo-kpi-grid { grid-template-columns: 1fr; }
    }

    .tendoo-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        position: relative;
    }

    .tendoo-kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .tendoo-kpi-title {
        font-size: 0.76rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .tendoo-kpi-val {
        font-size: 1.5rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.2;
    }

    .tendoo-kpi-sub {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 6px;
    }

    .tendoo-trend-arrow {
        color: #dc2626;
        font-weight: 900;
        font-size: 0.95rem;
    }

    /* GRID LAYOUT FOR CHARTS & TABLES */
    .tendoo-row-2col {
        display: grid;
        grid-template-columns: 2.2fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .tendoo-row-60-40 {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .tendoo-row-equal {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (max-width: 1024px) {
        .tendoo-row-2col, .tendoo-row-60-40, .tendoo-row-equal {
            grid-template-columns: 1fr;
        }
    }

    /* CARD PANELS */
    .tendoo-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px 22px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .tendoo-card-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tendoo-card-h3 {
        font-size: 0.98rem;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* SUB-TAB PILLS */
    .tendoo-tab-group {
        display: inline-flex;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 8px;
    }

    .tendoo-tab-btn {
        border: none;
        background: transparent;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tendoo-tab-btn.active {
        background: #dc2626;
        color: #ffffff;
    }

    /* INVENTORY ALERT WIDGET */
    .tendoo-alert-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s;
        text-decoration: none;
    }
    .tendoo-alert-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        transform: translateY(-1px);
    }

    .tendoo-alert-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    /* DAILY REVENUE TABLE */
    .tendoo-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    .tendoo-table th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 10px 14px;
        border-bottom: 1.5px solid #e2e8f0;
        text-align: left;
    }
    .tendoo-table td {
        padding: 11px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        font-weight: 600;
    }
    .tendoo-table tr:hover td {
        background: #f8fafc;
    }

    /* TOP PRODUCTS LIST */
    .top-prod-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .top-prod-rank {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .top-prod-rank.top-1 { background: #fef3c7; color: #d97706; }
    .top-prod-rank.top-2 { background: #e0f2fe; color: #0284c7; }
    .top-prod-rank.top-3 { background: #ecfdf5; color: #059669; }
</style>

<div class="tendoo-dash-container">

    <!-- 1. GREETING & DATE FILTER HEADER BAR -->
    <div class="tendoo-greeting-bar">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div class="tendoo-greeting-sub">Xin chào, <strong>{{ $eatery->name }}</strong></div>
                <h2 class="tendoo-greeting-title">Chúc bạn một ngày kinh doanh hiệu quả!</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="tendoo-btn-light">
                    <i class="fa-solid fa-pen-to-square"></i> Tùy chỉnh
                </button>
                <a href="{{ route('hkd.products.create') }}" class="tendoo-btn-red">
                    <i class="fa-solid fa-plus"></i> Thêm tiện ích
                </a>
            </div>
        </div>

        <div class="tendoo-filter-row">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <div class="tendoo-date-picker-wrap" style="position: relative;">
                    <button type="button" class="tendoo-date-pill" id="datePickerBtn" onclick="toggleDatePickerPopover(event)">
                        <i class="fa-regular fa-calendar-days" style="color: #64748b;"></i>
                        <span id="dateRangeText">{{ now()->subDays(6)->format('d/m/Y') }} — {{ now()->format('d/m/Y') }}</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.72rem; color: #94a3b8; margin-left: 4px;"></i>
                    </button>

                    <!-- POPOVER MODAL DROPDOWN (MATCHING VIETTEL POS) -->
                    <div id="datePickerPopover" class="tendoo-date-popover" style="display: none;" onclick="event.stopPropagation();">
                        <div class="tendoo-popover-sidebar">
                            <div class="tendoo-popover-opt" onclick="selectDateRangeOption('today', 'Hôm nay', this)">Hôm nay</div>
                            <div class="tendoo-popover-opt" onclick="selectDateRangeOption('yesterday', 'Hôm qua', this)">Hôm qua</div>
                            <div class="tendoo-popover-opt" onclick="selectDateRangeOption('last7', '7 ngày qua', this)">7 ngày qua</div>
                            <div class="tendoo-popover-opt active" onclick="selectDateRangeOption('this_week', 'Tuần này', this)">Tuần này</div>
                            <div class="tendoo-popover-opt" onclick="selectDateRangeOption('last_week', 'Tuần trước', this)">Tuần trước</div>
                            <div class="tendoo-popover-opt" onclick="selectDateRangeOption('last30', '30 ngày qua', this)">30 ngày qua</div>
                            <div class="tendoo-popover-opt" onclick="selectDateRangeOption('this_month', 'Tháng này', this)">Tháng này</div>
                            <div class="tendoo-popover-opt" onclick="selectDateRangeOption('last_month', 'Tháng trước', this)">Tháng trước</div>
                            <div class="tendoo-popover-opt" onclick="selectDateRangeOption('this_year', 'Năm nay', this)">Năm nay</div>
                        </div>

                        <div class="tendoo-popover-calendar-body">
                            <div class="tendoo-calendar-grid-wrap">
                                <!-- MONTH 1 -->
                                <div style="flex: 1;">
                                    <div class="tendoo-cal-month-title">Th09 2026</div>
                                    <div class="tendoo-cal-grid">
                                        <div class="tendoo-cal-head">T2</div>
                                        <div class="tendoo-cal-head">T3</div>
                                        <div class="tendoo-cal-head">T4</div>
                                        <div class="tendoo-cal-head">T5</div>
                                        <div class="tendoo-cal-head">T6</div>
                                        <div class="tendoo-cal-head">T7</div>
                                        <div class="tendoo-cal-head">CN</div>

                                        <div class="tendoo-cal-day muted">31</div>
                                        <div class="tendoo-cal-day">1</div>
                                        <div class="tendoo-cal-day">2</div>
                                        <div class="tendoo-cal-day">3</div>
                                        <div class="tendoo-cal-day">4</div>
                                        <div class="tendoo-cal-day">5</div>
                                        <div class="tendoo-cal-day">6</div>

                                        <div class="tendoo-cal-day">7</div>
                                        <div class="tendoo-cal-day">8</div>
                                        <div class="tendoo-cal-day">9</div>
                                        <div class="tendoo-cal-day">10</div>
                                        <div class="tendoo-cal-day">11</div>
                                        <div class="tendoo-cal-day">12</div>
                                        <div class="tendoo-cal-day">13</div>

                                        <div class="tendoo-cal-day">14</div>
                                        <div class="tendoo-cal-day">15</div>
                                        <div class="tendoo-cal-day">16</div>
                                        <div class="tendoo-cal-day">17</div>
                                        <div class="tendoo-cal-day">18</div>
                                        <div class="tendoo-cal-day">19</div>
                                        <div class="tendoo-cal-day">20</div>

                                        <div class="tendoo-cal-day">21</div>
                                        <div class="tendoo-cal-day">22</div>
                                        <div class="tendoo-cal-day">23</div>
                                        <div class="tendoo-cal-day">24</div>
                                        <div class="tendoo-cal-day">25</div>
                                        <div class="tendoo-cal-day">26</div>
                                        <div class="tendoo-cal-day">27</div>

                                        <div class="tendoo-cal-day selected">28</div>
                                        <div class="tendoo-cal-day in-range">29</div>
                                        <div class="tendoo-cal-day in-range">30</div>
                                        <div class="tendoo-cal-day muted">1</div>
                                        <div class="tendoo-cal-day muted">2</div>
                                        <div class="tendoo-cal-day muted">3</div>
                                        <div class="tendoo-cal-day muted">4</div>
                                    </div>
                                </div>

                                <!-- MONTH 2 -->
                                <div style="flex: 1;">
                                    <div class="tendoo-cal-month-title">Th10 2026</div>
                                    <div class="tendoo-cal-grid">
                                        <div class="tendoo-cal-head">T2</div>
                                        <div class="tendoo-cal-head">T3</div>
                                        <div class="tendoo-cal-head">T4</div>
                                        <div class="tendoo-cal-head">T5</div>
                                        <div class="tendoo-cal-head">T6</div>
                                        <div class="tendoo-cal-head">T7</div>
                                        <div class="tendoo-cal-head">CN</div>

                                        <div class="tendoo-cal-day muted">28</div>
                                        <div class="tendoo-cal-day muted">29</div>
                                        <div class="tendoo-cal-day muted">30</div>
                                        <div class="tendoo-cal-day in-range">1</div>
                                        <div class="tendoo-cal-day in-range">2</div>
                                        <div class="tendoo-cal-day in-range">3</div>
                                        <div class="tendoo-cal-day selected">4</div>

                                        <div class="tendoo-cal-day">5</div>
                                        <div class="tendoo-cal-day">6</div>
                                        <div class="tendoo-cal-day">7</div>
                                        <div class="tendoo-cal-day">8</div>
                                        <div class="tendoo-cal-day">9</div>
                                        <div class="tendoo-cal-day">10</div>
                                        <div class="tendoo-cal-day">11</div>

                                        <div class="tendoo-cal-day">12</div>
                                        <div class="tendoo-cal-day">13</div>
                                        <div class="tendoo-cal-day">14</div>
                                        <div class="tendoo-cal-day">15</div>
                                        <div class="tendoo-cal-day">16</div>
                                        <div class="tendoo-cal-day">17</div>
                                        <div class="tendoo-cal-day">18</div>

                                        <div class="tendoo-cal-day">19</div>
                                        <div class="tendoo-cal-day">20</div>
                                        <div class="tendoo-cal-day">21</div>
                                        <div class="tendoo-cal-day">22</div>
                                        <div class="tendoo-cal-day">23</div>
                                        <div class="tendoo-cal-day">24</div>
                                        <div class="tendoo-cal-day">25</div>

                                        <div class="tendoo-cal-day">26</div>
                                        <div class="tendoo-cal-day">27</div>
                                        <div class="tendoo-cal-day">28</div>
                                        <div class="tendoo-cal-day">29</div>
                                        <div class="tendoo-cal-day">30</div>
                                        <div class="tendoo-cal-day">31</div>
                                        <div class="tendoo-cal-day muted">1</div>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                                <button type="button" class="tendoo-btn-light" onclick="toggleDatePickerPopover(event)" style="padding: 6px 14px; font-size: 0.8rem;">Hủy</button>
                                <button type="button" class="tendoo-btn-red" onclick="toggleDatePickerPopover(event)" style="padding: 6px 16px; font-size: 0.8rem;">Áp dụng</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tendoo-compare-hint" id="compareTextHint">
                    So sánh với Tuần trước ({{ now()->subDays(13)->format('d/m') }} - {{ now()->subDays(7)->format('d/m/Y') }})
                </div>
            </div>
        </div>
    </div>

    <!-- 2. TOP METRICS - 4 KEY KPI CARDS GRID -->
    <div class="tendoo-kpi-grid">
        <!-- Card 1: TỔNG TIỀN ĐƠN HÀNG -->
        <div class="tendoo-kpi-card">
            <div class="tendoo-kpi-header">
                <span class="tendoo-kpi-title">TỔNG TIỀN ĐƠN HÀNG</span>
                <i class="fa-regular fa-circle-question" style="color: #cbd5e1; font-size: 0.85rem;" title="Tổng giá trị các đơn hàng hoàn thành trong tuần"></i>
            </div>
            <div class="tendoo-kpi-val">{{ number_format($totalRevenue) }} đ</div>
            <div class="tendoo-kpi-sub">— Giữ nguyên</div>
        </div>

        <!-- Card 2: TỔNG ĐƠN HÀNG -->
        <div class="tendoo-kpi-card">
            <div class="tendoo-kpi-header">
                <span class="tendoo-kpi-title">TỔNG ĐƠN HÀNG</span>
                <i class="fa-solid fa-cart-shopping" style="color: #cbd5e1; font-size: 0.85rem;"></i>
            </div>
            <div class="tendoo-kpi-val">{{ $ordersCount }}</div>
            <div class="tendoo-kpi-sub">— Giữ nguyên</div>
        </div>

        <!-- Card 3: CHỜ XÁC NHẬN -->
        <div class="tendoo-kpi-card">
            <div class="tendoo-kpi-header">
                <span class="tendoo-kpi-title">CHỜ XÁC NHẬN</span>
                <span class="tendoo-trend-arrow">↗</span>
            </div>
            <div class="tendoo-kpi-val" style="font-size: 1.3rem;">
                {{ $pendingOrdersCount }} <span style="font-size: 0.88rem; font-weight: 700; color: #64748b;">Đơn</span>
            </div>
            <div style="font-size: 0.92rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
                {{ number_format($pendingOrdersAmount) }}đ
            </div>
        </div>

        <!-- Card 4: ĐANG XỬ LÝ -->
        <div class="tendoo-kpi-card">
            <div class="tendoo-kpi-header">
                <span class="tendoo-kpi-title">ĐANG XỬ LÝ</span>
                <span class="tendoo-trend-arrow">↗</span>
            </div>
            <div class="tendoo-kpi-val" style="font-size: 1.3rem;">
                {{ $processingOrdersCount }} <span style="font-size: 0.88rem; font-weight: 700; color: #64748b;">Đơn</span>
            </div>
            <div style="font-size: 0.92rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
                {{ number_format($processingOrdersAmount) }}đ
            </div>
        </div>
    </div>

    <!-- 3. ROW 2: CHART (70%) + TOP 5 SELLING PRODUCTS (30%) -->
    <div class="tendoo-row-2col">
        <!-- LEFT: BIỂU ĐỒ XU HƯỚNG -->
        <div class="tendoo-card">
            <div class="tendoo-card-title-row">
                <div class="tendoo-card-h3">
                    <span>BIỂU ĐỒ XU HƯỚNG</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <div class="tendoo-tab-group" id="trendTabGroup">
                        <button type="button" class="tendoo-tab-btn active" id="btnTabRevenue" onclick="switchTrendTab('revenue', this)">Doanh thu</button>
                        <button type="button" class="tendoo-tab-btn" id="btnTabOrders" onclick="switchTrendTab('orders', this)">Đơn hàng</button>
                        <button type="button" class="tendoo-tab-btn" id="btnTabCustomers" onclick="switchTrendTab('customers', this)">Khách hàng</button>
                    </div>

                    <div style="display: inline-flex; background: #f1f5f9; padding: 3px; border-radius: 8px;">
                        <button type="button" class="tendoo-type-btn active" id="btnTypeBar" onclick="switchChartType('bar', this)" title="Biểu đồ cột" style="border: none; background: #dc2626; color: #fff; padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;">
                            <i class="fa-solid fa-chart-column"></i>
                        </button>
                        <button type="button" class="tendoo-type-btn" id="btnTypeLine" onclick="switchChartType('line', this)" title="Biểu đồ đường" style="border: none; background: transparent; color: #64748b; padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;">
                            <i class="fa-solid fa-chart-line"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 16px; margin-bottom: 12px; font-size: 0.78rem; font-weight: 700; color: #64748b;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; background: #f59e0b; border-radius: 50%;"></span>
                    <span>Tuần trước</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; background: #0284c7; border-radius: 50%;"></span>
                    <span>Tuần này</span>
                </div>
            </div>

            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- RIGHT: TOP 5 SẢN PHẨM BÁN CHẠY NHẤT -->
        <div class="tendoo-card">
            <div class="tendoo-card-title-row">
                <div class="tendoo-card-h3" style="font-size: 0.88rem;">
                    <span>TOP 5 SẢN PHẨM BÁN CHẠY</span>
                    <a href="{{ route('hkd.products.index') }}" style="color: #64748b; text-decoration: none;">➔</a>
                </div>
                <div class="tendoo-tab-group" id="topTabGroup">
                    <button type="button" class="tendoo-tab-btn active" id="btnTopQty" onclick="switchTopTab('qty', this)">Số lượng</button>
                    <button type="button" class="tendoo-tab-btn" id="btnTopRevenue" onclick="switchTopTab('revenue', this)">Doanh thu</button>
                </div>
            </div>

            <!-- TAB 1: SỐ LƯỢNG -->
            <div id="topListQty">
                @if(!isset($topProductsByQty) || $topProductsByQty->isEmpty())
                    <div style="text-align: center; padding: 40px 10px; color: #cbd5e1;">
                        <i class="fa-solid fa-box-open" style="font-size: 2.2rem; margin-bottom: 6px;"></i>
                        <div style="font-size: 0.82rem; font-weight: 600;">Chưa có dữ liệu bán hàng</div>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column;">
                        @foreach($topProductsByQty as $idx => $tp)
                        <div class="top-prod-item">
                            <div class="top-prod-rank top-{{ $idx + 1 }}">{{ $idx + 1 }}</div>
                            <div style="width: 36px; height: 36px; border-radius: 8px; overflow: hidden; background: #f1f5f9; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                @if($tp->image)
                                    <img src="{{ $tp->image }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <i class="fa-solid fa-box" style="color: #cbd5e1; font-size: 0.9rem;"></i>
                                @endif
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tp->name }}
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    {{ number_format($tp->price) }}đ
                                </div>
                            </div>
                            <div style="text-align: right; font-size: 0.82rem; font-weight: 800; color: #059669;">
                                {{ number_format($tp->sold_qty) }} <span style="font-size: 0.72rem; color: #64748b; font-weight: 600;">đã bán</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- TAB 2: DOANH THU -->
            <div id="topListRevenue" style="display: none;">
                @if(!isset($topProductsByRevenue) || $topProductsByRevenue->isEmpty())
                    <div style="text-align: center; padding: 40px 10px; color: #cbd5e1;">
                        <i class="fa-solid fa-box-open" style="font-size: 2.2rem; margin-bottom: 6px;"></i>
                        <div style="font-size: 0.82rem; font-weight: 600;">Chưa có dữ liệu bán hàng</div>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column;">
                        @foreach($topProductsByRevenue as $idx => $tp)
                        <div class="top-prod-item">
                            <div class="top-prod-rank top-{{ $idx + 1 }}">{{ $idx + 1 }}</div>
                            <div style="width: 36px; height: 36px; border-radius: 8px; overflow: hidden; background: #f1f5f9; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                @if($tp->image)
                                    <img src="{{ $tp->image }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <i class="fa-solid fa-box" style="color: #cbd5e1; font-size: 0.9rem;"></i>
                                @endif
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tp->name }}
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    {{ number_format($tp->price) }}đ
                                </div>
                            </div>
                            <div style="text-align: right; font-size: 0.82rem; font-weight: 800; color: #2563eb;">
                                {{ number_format($tp->total_sales) }}đ
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 4. ROW 3: DOANH THU THEO NGÀY (60%) + CẢNH BÁO HÀNG HOÁ (40%) -->
    <div class="tendoo-row-60-40">
        <!-- LEFT: DOANH THU THEO NGÀY TABLE -->
        <div class="tendoo-card">
            <div class="tendoo-card-title-row">
                <div class="tendoo-card-h3">
                    <span>DOANH THU THEO NGÀY</span>
                    <span class="tendoo-trend-arrow">↗</span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="tendoo-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th style="text-align: center;">SL đặt hàng</th>
                            <th style="text-align: right;">Tiền hàng trả lại</th>
                            <th style="text-align: right;">Doanh thu thuần</th>
                            <th style="text-align: right;">Tổng tiền đơn hàng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyTableData as $row)
                        <tr>
                            <td style="font-weight: 700; color: #334155;">{{ $row['date'] }}</td>
                            <td style="text-align: center;">{{ $row['sl_dat'] }}</td>
                            <td style="text-align: right; color: #94a3b8;">{{ number_format($row['tra_lai']) }}</td>
                            <td style="text-align: right; font-weight: 800; color: #059669;">{{ number_format($row['doanh_thu_thuan']) }}</td>
                            <td style="text-align: right; font-weight: 800; color: #0f172a;">{{ number_format($row['tong_tien']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT: CẢNH BÁO HÀNG HOÁ -->
        <div class="tendoo-card">
            <div class="tendoo-card-title-row">
                <div class="tendoo-card-h3">
                    <span>CẢNH BÁO HÀNG HOÁ</span>
                </div>
            </div>

            <div>
                <!-- Item 1: Sản phẩm đã hết hàng -->
                <a href="{{ route('hkd.products.index') }}?stock=out_of_stock" class="tendoo-alert-item" style="background: #fff5f5; border-color: #fed7d7;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="tendoo-alert-icon" style="background: #fee2e2; color: #dc2626;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.88rem; font-weight: 800; color: #991b1b;">Sản phẩm đã hết hàng</div>
                            <div style="font-size: 0.75rem; color: #791919;">Cần nhập thêm tồn kho ngay</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.95rem; font-weight: 900; color: #dc2626;">{{ $outOfStockCount }}</span>
                        <span style="color: #94a3b8;">➔</span>
                    </div>
                </a>

                <!-- Item 2: Sản phẩm sắp hết hàng -->
                <a href="{{ route('hkd.products.index') }}" class="tendoo-alert-item" style="background: #fffbeb; border-color: #fef3c7;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="tendoo-alert-icon" style="background: #fef3c7; color: #d97706;">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.88rem; font-weight: 800; color: #92400e;">Sản phẩm sắp hết hàng</div>
                            <div style="font-size: 0.75rem; color: #b45309;">Số lượng kho dưới ngưỡng an toàn</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.95rem; font-weight: 900; color: #d97706;">{{ $lowStockCount }}</span>
                        <span style="color: #94a3b8;">➔</span>
                    </div>
                </a>

                <!-- Item 3: Sản phẩm sắp hết hạn -->
                <div class="tendoo-alert-item" style="background: #fafafa; opacity: 0.85;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="tendoo-alert-icon" style="background: #f1f5f9; color: #64748b;">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.88rem; font-weight: 800; color: #475569;">Sản phẩm sắp hết hạn</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Hạn dùng dưới 30 ngày</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.95rem; font-weight: 900; color: #64748b;">0</span>
                        <span style="color: #cbd5e1;">➔</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. ROW 4: TỶ LỆ ĐƠN THEO TRẠNG THÁI (50%) + VIETQR & HOÀN THIỆN GIAN HÀNG (50%) -->
    <div class="tendoo-row-equal">
        <!-- LEFT: TỶ LỆ ĐƠN THEO TRẠNG THÁI -->
        <div class="tendoo-card">
            <div class="tendoo-card-title-row">
                <div class="tendoo-card-h3">
                    <span>TỶ LỆ ĐƠN THEO TRẠNG THÁI</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; justify-content: center; padding: 10px 0;">
                <div style="position: relative; width: 170px; height: 170px;">
                    <canvas id="statusDonutChart"></canvas>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.84rem; font-weight: 700;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 10px; height: 10px; background: #0284c7; border-radius: 50%;"></span>
                        <span style="color: #64748b;">Chờ xác nhận:</span>
                        <strong style="color: #0f172a;">{{ $pendingOrdersCount }} đơn</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 10px; height: 10px; background: #f59e0b; border-radius: 50%;"></span>
                        <span style="color: #64748b;">Đang xử lý:</span>
                        <strong style="color: #0f172a;">{{ $processingOrdersCount }} đơn</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 10px; height: 10px; background: #10b981; border-radius: 50%;"></span>
                        <span style="color: #64748b;">Hoàn thành:</span>
                        <strong style="color: #0f172a;">{{ $completedOrdersCount }} đơn</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 10px; height: 10px; background: #94a3b8; border-radius: 50%;"></span>
                        <span style="color: #64748b;">Trả hàng:</span>
                        <strong style="color: #0f172a;">0 đơn</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 10px; height: 10px; background: #ef4444; border-radius: 50%;"></span>
                        <span style="color: #64748b;">Hủy bỏ:</span>
                        <strong style="color: #0f172a;">{{ $cancelledOrdersCount }} đơn</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: HOÀN THIỆN GIAN HÀNG -->
        <div class="tendoo-card">
            <div class="tendoo-card-title-row">
                <div class="tendoo-card-h3">
                    <span>Nhiệm Vụ Hoàn Thiện Gian Hàng</span>
                </div>
                <span style="font-weight: 900; color: #059669; font-size: 0.95rem;">{{ $profileScore }}%</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($profileChecklist as $key => $task)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 12px; background: {{ $task['done'] ? '#ecfdf5' : '#f8fafc' }}; border: 1px solid {{ $task['done'] ? '#a7f3d0' : '#e2e8f0' }};">
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 0.84rem; font-weight: 700; color: {{ $task['done'] ? '#065f46' : '#334155' }};">
                        <i class="fa-solid {{ $task['done'] ? 'fa-circle-check' : 'fa-circle' }}" style="color: {{ $task['done'] ? '#10b981' : '#cbd5e1' }};"></i>
                        <span>{{ $task['title'] }}</span>
                    </div>
                    @if(!$task['done'])
                        <a href="{{ $task['route'] }}" class="tendoo-btn-light" style="padding: 4px 10px; font-size: 0.75rem;">
                            {{ $task['label'] }}
                        </a>
                    @else
                        <span style="font-size: 0.75rem; font-weight: 800; color: #059669;">Đã xong</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

<!-- CHART.JS LOGIC -->
<script>
    // DYNAMIC TREND CHART SCRIPT
    const trendLabels = @json($sevenDaysLabels);
    const sevenDaysFullDates = @json($sevenDaysFullDates);
    const prevWeekFullDates = @json($prevWeekFullDates);

    const datasetsMap = {
        revenue: {
            unit: 'đ',
            thisWeek: @json($sevenDaysRevenueData),
            prevWeek: @json($prevWeekRevenueData),
            formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + 'đ'
        },
        orders: {
            unit: 'đơn',
            thisWeek: @json($sevenDaysOrdersData),
            prevWeek: @json($prevWeekOrdersData),
            formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + ' đơn'
        },
        customers: {
            unit: 'khách',
            thisWeek: @json($sevenDaysCustomersData),
            prevWeek: @json($prevWeekCustomersData),
            formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + ' khách'
        }
    };

    let currentTab = 'revenue';
    let currentChartType = 'bar';
    let chartInstance = null;

    function renderTrendChart() {
        const ctx = document.getElementById('trendChart').getContext('2d');
        const dataObj = datasetsMap[currentTab];

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: currentChartType,
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'Tuần này',
                        data: dataObj.thisWeek,
                        backgroundColor: currentChartType === 'bar' ? '#0284c7' : 'rgba(2, 132, 199, 0.08)',
                        borderColor: '#0284c7',
                        borderWidth: 2,
                        borderRadius: currentChartType === 'bar' ? 4 : 0,
                        fill: currentChartType === 'line',
                        tension: 0.3,
                        pointBackgroundColor: '#0284c7',
                        pointRadius: currentChartType === 'line' ? 5 : 0
                    },
                    {
                        label: 'Tuần trước',
                        data: dataObj.prevWeek,
                        backgroundColor: currentChartType === 'bar' ? '#f59e0b' : 'transparent',
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        borderRadius: currentChartType === 'bar' ? 4 : 0,
                        fill: false,
                        tension: 0.3,
                        pointBackgroundColor: '#f59e0b',
                        pointRadius: currentChartType === 'line' ? 4 : 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        titleColor: '#0f172a',
                        bodyColor: '#334155',
                        borderColor: '#cbd5e1',
                        borderWidth: 1,
                        padding: 10,
                        boxPadding: 4,
                        callbacks: {
                            title: function() { return ''; },
                            label: function(ctx) {
                                const idx = ctx.dataIndex;
                                const isThisWeek = ctx.datasetIndex === 0;
                                const fullDate = isThisWeek ? sevenDaysFullDates[idx] : prevWeekFullDates[idx];
                                const label = isThisWeek ? 'Tuần này' : 'Tuần trước';
                                const valFormatted = dataObj.formatter(ctx.raw);
                                return `• ${label} (${fullDate}): ${valFormatted}`;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        border: { dash: [4, 4] },
                        ticks: {
                            callback: function(val) {
                                if (currentTab === 'revenue') {
                                    if (val >= 1000000) return (val / 1000000) + 'M';
                                    if (val >= 1000) return (val / 1000) + 'k';
                                }
                                return val;
                            }
                        }
                    }
                }
            }
        });
    }

    function switchTrendTab(tabKey, btn) {
        currentTab = tabKey;
        document.querySelectorAll('#trendTabGroup .tendoo-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderTrendChart();
    }

    function switchTopTab(type, btn) {
        document.querySelectorAll('#topTabGroup .tendoo-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if (type === 'qty') {
            document.getElementById('topListQty').style.display = 'block';
            document.getElementById('topListRevenue').style.display = 'none';
        } else {
            document.getElementById('topListQty').style.display = 'none';
            document.getElementById('topListRevenue').style.display = 'block';
        }
    }

    function switchChartType(type, btn) {
        currentChartType = type;
        document.querySelectorAll('.tendoo-type-btn').forEach(b => {
            b.style.background = 'transparent';
            b.style.color = '#64748b';
        });
        btn.style.background = '#dc2626';
        btn.style.color = '#ffffff';
        renderTrendChart();
    }

    function toggleDatePickerPopover(e) {
        if (e) e.stopPropagation();
        const popover = document.getElementById('datePickerPopover');
        if (popover) {
            popover.style.display = popover.style.display === 'none' ? 'flex' : 'none';
        }
    }

    document.addEventListener('click', function(e) {
        const popover = document.getElementById('datePickerPopover');
        const btn = document.getElementById('datePickerBtn');
        if (popover && popover.style.display === 'flex' && !popover.contains(e.target) && (!btn || !btn.contains(e.target))) {
            popover.style.display = 'none';
        }
    });

    function selectDateRangeOption(key, label, element) {
        document.querySelectorAll('.tendoo-popover-opt').forEach(el => el.classList.remove('active'));
        if (element) element.classList.add('active');

        const dateRangeText = document.getElementById('dateRangeText');
        const compareHint = document.getElementById('compareTextHint');

        const todayStr = '28/09/2026';
        if (key === 'today') {
            dateRangeText.innerText = todayStr;
            compareHint.innerText = 'So sánh với Hôm qua (27/09/2026)';
        } else if (key === 'yesterday') {
            dateRangeText.innerText = '27/09/2026';
            compareHint.innerText = 'So sánh với Ngày 26/09/2026';
        } else if (key === 'last7') {
            dateRangeText.innerText = '22/09/2026 — 28/09/2026';
            compareHint.innerText = 'So sánh với 7 ngày trước đó (15/09 - 21/09/2026)';
        } else if (key === 'this_week') {
            dateRangeText.innerText = '28/09/2026 — 04/10/2026';
            compareHint.innerText = 'So sánh với Tuần trước (21/09 - 27/09/2026)';
        } else if (key === 'last_week') {
            dateRangeText.innerText = '21/09/2026 — 27/09/2026';
            compareHint.innerText = 'So sánh với Tuần 14/09 - 20/09/2026';
        } else if (key === 'this_month') {
            dateRangeText.innerText = '01/09/2026 — 30/09/2026';
            compareHint.innerText = 'So sánh với Tháng 08/2026';
        } else {
            dateRangeText.innerText = '28/09/2026 — 04/10/2026';
            compareHint.innerText = 'So sánh với Tuần trước (21/09 - 27/09/2026)';
        }

        const popover = document.getElementById('datePickerPopover');
        if (popover) popover.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderTrendChart();

        // 2. STATUS DONUT CHART
        const ctxDonut = document.getElementById('statusDonutChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Chờ xác nhận', 'Đang xử lý', 'Hoàn thành', 'Trả hàng', 'Hủy bỏ'],
                datasets: [{
                    data: [
                        {{ $pendingOrdersCount }},
                        {{ $processingOrdersCount }},
                        {{ $completedOrdersCount }},
                        0,
                        {{ $cancelledOrdersCount }}
                    ],
                    backgroundColor: ['#0284c7', '#f59e0b', '#10b981', '#94a3b8', '#ef4444'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>

@endsection
