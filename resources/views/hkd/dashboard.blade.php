@extends('layouts.hkd')

@section('title', 'Tổng Quan Quản Trị Kinh Doanh — ' . $eatery->name)
@section('title_header', 'Kênh Điều Hành Hộ Kinh Doanh & Doanh Nghiệp')

@section('content')

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Executive HKD Dashboard Theme */
    .hkd-hero {
        background: linear-gradient(135deg, #044e3b 0%, #059669 50%, #0284c7 100%);
        border-radius: 20px;
        padding: 30px;
        color: #ffffff;
        box-shadow: 0 12px 30px -8px rgba(5, 150, 105, 0.35);
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    .hkd-hero::after {
        content: '';
        position: absolute;
        right: -30px; top: -30px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
        pointer-events: none;
    }
    .hkd-hero-tag {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 5px 14px; background: rgba(255,255,255,0.18);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 20px; font-size: 0.76rem; font-weight: 800; color: #a7f3d0;
        text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 12px;
    }
    .hkd-hero-title { font-size: 1.9rem; font-weight: 900; margin-bottom: 10px; line-height: 1.2; letter-spacing: -0.01em; }
    .hkd-hero-sub { font-size: 0.92rem; color: #e2e8f0; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .hkd-hero-sub span { display: flex; align-items: center; gap: 6px; }

    /* STAT CARDS */
    .hkd-stat-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px; margin-bottom: 28px;
    }
    .hkd-stat-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 22px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03); transition: all 0.25s ease;
        display: flex; flex-direction: column; justify-content: space-between;
    }
    .hkd-stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,0.07); border-color: #cbd5e1; }
    .hkd-stat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .hkd-stat-label { font-size: 0.78rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; }
    .hkd-stat-icon {
        width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }
    .hkd-stat-value { font-size: 1.7rem; font-weight: 900; color: #0f172a; line-height: 1.1; }
    .hkd-stat-sub { font-size: 0.78rem; color: #64748b; margin-top: 8px; font-weight: 600; display: flex; align-items: center; gap: 6px; }

    /* PROGRESS BAR */
    .hkd-progress-bar { height: 8px; background: #e2e8f0; border-radius: 6px; overflow: hidden; margin-top: 10px; }
    .hkd-progress-fill { height: 100%; background: linear-gradient(90deg, #10b981 0%, #0284c7 100%); border-radius: 6px; }

    /* GRID & CARDS SYSTEM */
    .hkd-grid-2col { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 28px; }
    .hkd-grid-equal { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; }

    @media (max-width: 1024px) {
        .hkd-grid-2col, .hkd-grid-equal { grid-template-columns: 1fr; }
    }

    .hkd-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 24px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; flex-direction: column;
    }
    .hkd-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .hkd-card-title { font-size: 1.08rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }

    /* TABLES */
    .hkd-table { width: 100%; border-collapse: collapse; }
    .hkd-table th { background: #f8fafc; text-align: left; padding: 12px 16px; font-size: 0.78rem; font-weight: 800; color: #475569; text-transform: uppercase; border-bottom: 1.5px solid #e2e8f0; }
    .hkd-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.88rem; font-weight: 600; color: #334155; }
    .hkd-table tr:hover td { background: #fafafa; }

    /* CHECKLIST LIST ITEM */
    .hkd-task-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 16px; border-radius: 14px; background: #f8fafc; border: 1px solid #f1f5f9;
        margin-bottom: 10px; transition: all 0.2s;
    }
    .hkd-task-item.is-done { background: #f0fdf4; border-color: #dcfce7; }
    .hkd-task-left { display: flex; align-items: center; gap: 12px; }
    .hkd-task-check {
        width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; font-weight: 800;
    }
    .hkd-task-check.done { background: #10b981; color: #fff; }
    .hkd-task-check.todo { background: #cbd5e1; color: #fff; }
</style>

<!-- HERO HEADER BANNER -->
<div class="hkd-hero">
    <div class="hkd-hero-tag">
        <i class="fa-solid fa-chart-line"></i> Kênh Quản Trị Hộ Kinh Doanh & Doanh Nghiệp
    </div>
    <div class="hkd-hero-title">{{ $eatery->name }}</div>
    <div class="hkd-hero-sub">
        <span><i class="fa-solid fa-id-card" style="color: #6ee7b7;"></i> MST: {{ $storyData['mst'] ?? 'Đang cập nhật' }}</span>
        <span><i class="fa-solid fa-location-dot" style="color: #6ee7b7;"></i> {{ $eatery->address }}</span>
        <span><i class="fa-solid fa-phone" style="color: #6ee7b7;"></i> {{ $eatery->phone }}</span>
    </div>
    <div style="margin-top: 22px; display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('hkd.profile') }}" class="hkd-btn-action hkd-btn-emerald">
            <i class="fa-solid fa-store"></i> Hồ Sơ Cơ Sở & Định Vị
        </a>
        <a href="{{ route('hkd.products.index') }}" class="hkd-btn-action hkd-btn-sky">
            <i class="fa-solid fa-plus-circle"></i> Đăng Mặt Hàng Mới
        </a>
        <a href="{{ route('hkd.qr') }}" class="hkd-btn-action" style="background: rgba(255,255,255,0.18); color: #fff; border: 1px solid rgba(255,255,255,0.35); backdrop-filter: blur(6px);">
            <i class="fa-solid fa-qrcode"></i> VietQR Ngân Hàng
        </a>
    </div>
</div>

<!-- 4 STAT CARDS -->
<div class="hkd-stat-grid">
    <!-- Doanh Thu -->
    <div class="hkd-stat-card">
        <div>
            <div class="hkd-stat-header">
                <span class="hkd-stat-label">Doanh Thu Thực Tế</span>
                <div class="hkd-stat-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="hkd-stat-value" style="color: #059669;">{{ number_format($totalRevenue) }} đ</div>
        </div>
        <div class="hkd-stat-sub">
            <i class="fa-solid fa-calendar-day" style="color: #10b981;"></i>
            <span>Hôm nay: <strong style="color: #059669;">{{ number_format($todayRevenue) }} đ</strong></span>
        </div>
    </div>

    <!-- Đơn Hàng -->
    <div class="hkd-stat-card">
        <div>
            <div class="hkd-stat-header">
                <span class="hkd-stat-label">Đơn Hàng Đã Nhận</span>
                <div class="hkd-stat-icon" style="background: #eff6ff; color: #0284c7;">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
            <div class="hkd-stat-value">{{ $ordersCount }} <span style="font-size: 0.9rem; font-weight: 700; color: #64748b;">đơn</span></div>
        </div>
        <div class="hkd-stat-sub">
            @if($pendingOrdersCount > 0)
                <span style="color: #d97706; font-weight: 800;"><i class="fa-solid fa-bell"></i> {{ $pendingOrdersCount }} đơn chờ duyệt</span>
            @else
                <span style="color: #10b981;"><i class="fa-solid fa-circle-check"></i> Hôm nay: {{ $todayOrdersCount }} đơn</span>
            @endif
        </div>
    </div>

    <!-- Sản Phẩm -->
    <div class="hkd-stat-card">
        <div>
            <div class="hkd-stat-header">
                <span class="hkd-stat-label">Sản Phẩm Kinh Doanh</span>
                <div class="hkd-stat-icon" style="background: #faf5ff; color: #9333ea;">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="hkd-stat-value">{{ $productsCount }} <span style="font-size: 0.9rem; font-weight: 700; color: #64748b;">mặt hàng</span></div>
        </div>
        <div class="hkd-stat-sub">
            <i class="fa-solid fa-eye" style="color: #9333ea;"></i>
            <span>Đang bày bán công khai</span>
        </div>
    </div>

    <!-- Hoàn Thiện Gian Hàng -->
    <div class="hkd-stat-card">
        <div>
            <div class="hkd-stat-header">
                <span class="hkd-stat-label">Hoàn Thiện Gian Hàng</span>
                <div class="hkd-stat-icon" style="background: #f0fdf4; color: #16a34a;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="hkd-stat-value" style="color: #16a34a;">{{ $profileScore }}%</div>
            <div class="hkd-progress-bar">
                <div class="hkd-progress-fill" style="width: {{ $profileScore }}%;"></div>
            </div>
        </div>
        <div class="hkd-stat-sub">
            @if($profileScore >= 80)
                <span style="color: #16a34a; font-weight: 800;"><i class="fa-solid fa-shield-check"></i> Hồ sơ hoàn hảo</span>
            @else
                <span style="color: #ea580c; font-weight: 700;"><i class="fa-solid fa-circle-exclamation"></i> Cần bổ sung thông tin</span>
            @endif
        </div>
    </div>
</div>

<!-- ANALYTICS CHARTS SECTION -->
<div class="hkd-grid-2col">
    <!-- 7-Day Revenue Line Chart -->
    <div class="hkd-card">
        <div class="hkd-card-header">
            <div class="hkd-card-title">
                <i class="fa-solid fa-chart-area" style="color: #059669;"></i>
                <span>Xu Hướng Doanh Thu 7 Ngày Gần Nhất</span>
            </div>
            <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 8px;">
                Đơn hàng hoàn thành
            </span>
        </div>
        <div style="position: relative; height: 260px; width: 100%;">
            <canvas id="revenueTrendChart"></canvas>
        </div>
    </div>

    <!-- Order Status Doughnut Chart -->
    <div class="hkd-card">
        <div class="hkd-card-header">
            <div class="hkd-card-title">
                <i class="fa-solid fa-chart-pie" style="color: #0284c7;"></i>
                <span>Trạng Thái Đơn Hàng</span>
            </div>
        </div>
        <div style="position: relative; height: 200px; display: flex; justify-content: center;">
            <canvas id="orderStatusChart"></canvas>
        </div>
        <div style="display: flex; justify-content: space-around; margin-top: 16px; font-size: 0.78rem; font-weight: 700; text-align: center;">
            <div>
                <span style="display: inline-block; width: 10px; height: 10px; background: #f59e0b; border-radius: 50%;"></span>
                <div>Chờ duyệt ({{ $pendingOrdersCount }})</div>
            </div>
            <div>
                <span style="display: inline-block; width: 10px; height: 10px; background: #0284c7; border-radius: 50%;"></span>
                <div>Đang xử lý ({{ $processingOrdersCount }})</div>
            </div>
            <div>
                <span style="display: inline-block; width: 10px; height: 10px; background: #10b981; border-radius: 50%;"></span>
                <div>Hoàn thành ({{ $completedOrdersCount }})</div>
            </div>
            <div>
                <span style="display: inline-block; width: 10px; height: 10px; background: #ef4444; border-radius: 50%;"></span>
                <div>Đã hủy ({{ $cancelledOrdersCount }})</div>
            </div>
        </div>
    </div>
</div>

<!-- RECENT ORDERS & STORE CHECKLIST SECTION -->
<div class="hkd-grid-2col">
    <!-- Recent Orders Table -->
    <div class="hkd-card">
        <div class="hkd-card-header">
            <div class="hkd-card-title">
                <i class="fa-solid fa-clock-rotate-left" style="color: #059669;"></i>
                <span>Đơn Hàng Trực Tuyến Mới Nhận</span>
            </div>
            <a href="{{ route('hkd.orders.index') }}" style="color: #0284c7; text-decoration: none; font-weight: 700; font-size: 0.84rem;">
                Xem tất cả đơn hàng →
            </a>
        </div>

        @if($recentOrders->isEmpty())
            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                <i class="fa-solid fa-basket-shopping" style="font-size: 2.8rem; margin-bottom: 12px; opacity: 0.5;"></i>
                <p style="font-weight: 700; font-size: 0.95rem;">Chưa có đơn hàng trực tuyến nào phát sinh.</p>
                <p style="font-size: 0.82rem; margin-top: 4px;">Đơn hàng khi khách đặt mua từ trang công khai sẽ hiển thị ngay tại đây.</p>
            </div>
        @else
            <table class="hkd-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $ord)
                    <tr>
                        <td style="font-weight: 800; color: #0f172a;">#{{ $ord->id }}</td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">{{ $ord->customer_name ?? 'Khách vãng lai' }}</div>
                            <div style="font-size: 0.76rem; color: #64748b;">{{ $ord->customer_phone ?? 'N/A' }}</div>
                        </td>
                        <td style="font-weight: 900; color: #059669;">{{ number_format($ord->total_amount ?? 0) }} đ</td>
                        <td>
                            @if($ord->status === 'pending')
                                <span style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800;">Chờ duyệt</span>
                            @elseif(in_array($ord->status, ['completed', 'delivered', 'paid']))
                                <span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800;">Hoàn thành</span>
                            @elseif($ord->status === 'cancelled')
                                <span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800;">Đã hủy</span>
                            @else
                                <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800;">{{ $ord->status }}</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('hkd.orders.show', $ord->id) }}" style="color: #0284c7; font-weight: 800; text-decoration: none; font-size: 0.84rem;">
                                Chi tiết →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Store Completion Checklist -->
    <div class="hkd-card">
        <div class="hkd-card-header">
            <div class="hkd-card-title">
                <i class="fa-solid fa-list-check" style="color: #10b981;"></i>
                <span>Nhiệm Vụ Hoàn Thiện Gian Hàng</span>
            </div>
            <span style="font-weight: 800; color: #059669; font-size: 0.85rem;">{{ $profileScore }}%</span>
        </div>

        <div>
            @foreach($profileChecklist as $key => $task)
            <div class="hkd-task-item {{ $task['done'] ? 'is-done' : '' }}">
                <div class="hkd-task-left">
                    <div class="hkd-task-check {{ $task['done'] ? 'done' : 'todo' }}">
                        <i class="fa-solid {{ $task['done'] ? 'fa-check' : 'fa-minus' }}"></i>
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: {{ $task['done'] ? '#065f46' : '#334155' }};">
                        {{ $task['title'] }}
                    </div>
                </div>
                @if(!$task['done'])
                    <a href="{{ $task['route'] }}" class="hkd-btn-action hkd-btn-slate" style="padding: 5px 12px; font-size: 0.75rem;">
                        {{ $task['label'] }}
                    </a>
                @else
                    <span style="font-size: 0.75rem; font-weight: 800; color: #10b981;"><i class="fa-solid fa-circle-check"></i> Đã xong</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- PRODUCTS & VIETQR ROW -->
<div class="hkd-grid-equal">
    <!-- Active Products -->
    <div class="hkd-card">
        <div class="hkd-card-header">
            <div class="hkd-card-title">
                <i class="fa-solid fa-boxes-stacked" style="color: #0284c7;"></i>
                <span>Sản Phẩm & Hàng Hóa Bày Bán</span>
            </div>
            <a href="{{ route('hkd.products.index') }}" class="hkd-btn-action hkd-btn-sky" style="font-size: 0.78rem; padding: 6px 14px;">
                <i class="fa-solid fa-plus"></i> Quản Lý Danh Mục
            </a>
        </div>

        @if($products->isEmpty())
            <div style="text-align: center; padding: 30px; color: #94a3b8;">
                <i class="fa-solid fa-box-open" style="font-size: 2.5rem; margin-bottom: 10px; opacity: 0.5;"></i>
                <p style="font-weight: 700;">Chưa có sản phẩm / hàng hóa nào được niêm yết.</p>
                <p style="font-size: 0.82rem; margin-top: 4px;">Hãy bấm nút <strong>+ Quản Lý Danh Mục</strong> để thêm mặt hàng mới.</p>
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px;">
                @foreach($products as $p)
                <div style="border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px; background: #ffffff; transition: transform 0.2s;">
                    <div style="height: 110px; background: #f8fafc; border-radius: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 8px;">
                        @if($p->image)
                            <img src="{{ $p->image }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="fa-solid fa-box" style="font-size: 2.2rem; color: #cbd5e1;"></i>
                        @endif
                    </div>
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $p->name }}</div>
                    <div style="color: #059669; font-weight: 900; font-size: 0.9rem;">{{ number_format($p->price) }} đ</div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- VietQR Integration Card -->
    <div class="hkd-card" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-color: #a7f3d0;">
        <div class="hkd-card-header">
            <div class="hkd-card-title">
                <i class="fa-solid fa-qrcode" style="color: #059669;"></i>
                <span>Thanh Toán Không Tiền Mặt VietQR</span>
            </div>
            <a href="{{ route('hkd.qr') }}" class="hkd-btn-action hkd-btn-emerald" style="font-size: 0.78rem; padding: 6px 14px;">
                <i class="fa-solid fa-gear"></i> Cấu Hình QR
            </a>
        </div>

        @if($hasVietQr)
            <div style="display: flex; align-items: center; gap: 20px; padding: 10px;">
                <div style="width: 110px; height: 110px; background: #ffffff; padding: 8px; border-radius: 14px; border: 1.5px solid #10b981; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode('VietQR Pay: ' . $bankName . ' - ' . $bankAccount) }}" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div>
                    <div style="font-size: 0.78rem; font-weight: 800; color: #059669; text-transform: uppercase;">Ngân hàng liên kết</div>
                    <div style="font-size: 1.1rem; font-weight: 900; color: #0f172a; margin: 2px 0;">{{ $bankName }}</div>
                    <div style="font-size: 0.9rem; font-weight: 800; color: #334155;"><i class="fa-solid fa-credit-card" style="color: #64748b;"></i> STK: {{ $bankAccount }}</div>
                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px;">Chủ TK: <strong>{{ $bankOwner }}</strong></div>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 24px; color: #64748b;">
                <i class="fa-solid fa-qrcode" style="font-size: 2.8rem; color: #94a3b8; margin-bottom: 12px;"></i>
                <p style="font-weight: 700; color: #0f172a;">Chưa cấu hình nhận tiền VietQR Ngân Hàng</p>
                <p style="font-size: 0.82rem; margin-top: 4px; color: #64748b;">Vui lòng thêm Tên Ngân hàng và Số tài khoản để tự động tạo mã QR cho khách hàng khi đặt mua.</p>
                <div style="margin-top: 16px;">
                    <a href="{{ route('hkd.qr') }}" class="hkd-btn-action hkd-btn-emerald" style="padding: 10px 20px; font-size: 0.85rem;">
                        <i class="fa-solid fa-plus-circle"></i> Thêm Tài Khoản Ngân Hàng
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- CHART JS INITIALIZATION SCRIPT -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Revenue Trend Line Chart
        const ctxRevenue = document.getElementById('revenueTrendChart').getContext('2d');
        
        const gradientRevenue = ctxRevenue.createLinearGradient(0, 0, 0, 250);
        gradientRevenue.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
        gradientRevenue.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: @json($sevenDaysLabels),
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: @json($sevenDaysRevenueData),
                    borderColor: '#10b981',
                    borderWidth: 3,
                    backgroundColor: gradientRevenue,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#059669',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                return ' Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.raw) + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        border: { dash: [4, 4] },
                        ticks: {
                            callback: function(val) {
                                if (val >= 1000000) return (val / 1000000) + 'M';
                                if (val >= 1000) return (val / 1000) + 'k';
                                return val;
                            }
                        }
                    }
                }
            }
        });

        // 2. Order Status Doughnut Chart
        const ctxStatus = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Chờ duyệt', 'Đang xử lý', 'Hoàn thành', 'Đã hủy'],
                datasets: [{
                    data: [
                        {{ $pendingOrdersCount }},
                        {{ $processingOrdersCount }},
                        {{ $completedOrdersCount }},
                        {{ $cancelledOrdersCount }}
                    ],
                    backgroundColor: ['#f59e0b', '#0284c7', '#10b981', '#ef4444'],
                    borderWidth: 2,
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
