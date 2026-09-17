@extends('layouts.hkd')

@section('title', 'Quản Lý Đơn Hàng & Duyệt Đơn — ' . $eatery->name)
@section('title_header', 'Quản lý đơn hàng trực tuyến')

@section('content')

<style>
    .hkd-order-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }
    .hkd-order-title {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0;
        color: #0f172a;
    }
    .filter-btn {
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .filter-btn:hover {
        border-color: #059669;
        color: #059669;
        background: #f0fdf4;
    }
    .filter-btn.active {
        background: #059669;
        color: #ffffff;
        border-color: #059669;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .hkd-orders-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .hkd-orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .hkd-orders-table thead tr {
        background: #1e293b;
        color: #ffffff;
    }

    .hkd-orders-table th {
        padding: 14px 18px;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: left;
        border-bottom: 2px solid #0f172a;
    }

    .hkd-orders-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s ease;
    }

    .hkd-orders-table tbody tr:hover {
        background: #f8fafc;
    }

    .hkd-orders-table td {
        padding: 16px 18px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #334155;
        vertical-align: middle;
    }

    /* Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .badge-pending { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    .badge-preparing { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
    .badge-confirmed { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-completed { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-cancelled { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }

    .btn-detail-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        border-radius: 10px;
        background: #ffffff;
        color: #475569;
        font-weight: 700;
        font-size: 0.82rem;
        text-decoration: none;
        border: 1.5px solid #cbd5e1;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        transition: all 0.2s ease;
    }

    .btn-detail-action:hover {
        background: #059669;
        color: #ffffff;
        border-color: #059669;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }
</style>

<!-- Header & Filter -->
<div class="hkd-order-header">
    <div>
        <h1 class="hkd-order-title">
            Đơn hàng
        </h1>
        <div style="font-size: 0.88rem; color: #64748b; margin-top: 4px;">
            Tiếp nhận và xử lý đơn hàng kinh doanh tại cơ sở <strong>{{ $eatery->name }}</strong>
        </div>
    </div>
</div>

<!-- Horizontal Filter Tab Bar -->
<div class="order-filter-bar" style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
    <a href="{{ route('hkd.orders.index') }}" class="filter-btn {{ empty($activeStatus) ? 'active' : '' }}">
        Tất cả đơn
    </a>
    <a href="{{ route('hkd.orders.index', ['status' => 'pending']) }}" class="filter-btn {{ $activeStatus === 'pending' ? 'active' : '' }}">
        ⏳ Chờ xác nhận
    </a>
    <a href="{{ route('hkd.orders.index', ['status' => 'processing']) }}" class="filter-btn {{ in_array($activeStatus, ['processing', 'preparing']) ? 'active' : '' }}">
        🔄 Đang chuẩn bị
    </a>
    <a href="{{ route('hkd.orders.index', ['status' => 'completed']) }}" class="filter-btn {{ $activeStatus === 'completed' ? 'active' : '' }}">
        ✅ Hoàn thành
    </a>
    <a href="{{ route('hkd.orders.index', ['status' => 'cancelled']) }}" class="filter-btn {{ $activeStatus === 'cancelled' ? 'active' : '' }}">
        ❌ Đã hủy
    </a>
</div>

@if(session('success'))
    <div style="margin-bottom: 20px; padding: 14px 20px; background: #ecfdf5; border: 1.5px solid #10b981; color: #065f46; border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 1.2rem;">✅</span>
        <div><strong>Thành công!</strong> {{ session('success') }}</div>
    </div>
@endif

<!-- Table Card -->
<div class="hkd-orders-card">
    @if($orders->isEmpty())
        <div style="text-align: center; padding: 64px 20px;">
            <div style="font-size: 3.5rem; margin-bottom: 16px; opacity: 0.7;">📋</div>
            <div style="font-weight: 800; font-size: 1.15rem; color: #0f172a; margin-bottom: 6px;">Không tìm thấy đơn hàng nào</div>
            <div style="font-size: 0.9rem; color: #64748b;">Các đơn hàng trực tuyến của cơ sở sẽ xuất hiện tại đây.</div>
        </div>
    @else

    <div style="overflow-x: auto;">
        <table class="hkd-orders-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Mã đơn</th>
                    <th style="width: 20%;">Khách hàng</th>
                    <th style="width: 24%;">Sản phẩm</th>
                    <th style="width: 15%;">Tổng tiền</th>
                    <th style="width: 12%; text-align: center;">Trạng thái</th>
                    <th style="width: 11%;">Thanh toán</th>
                    <th style="width: 10%;">Thời gian</th>
                    <th style="width: 10%; text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $ord)
                @php
                    $st = strtolower($ord->status ?? 'pending');
                    $isConfirmed = in_array($st, ['confirmed', 'đã xác nhận']);
                    $isPreparing = in_array($st, ['preparing', 'processing', 'đang xử lý', 'đang chuẩn bị']);
                    $isDone      = in_array($st, ['completed', 'delivered', 'hoàn thành']);
                    $isCancelled = in_array($st, ['cancelled', 'rejected', 'đã hủy']);

                    // Items summary
                    $itemsList = isset($ord->items) ? $ord->items : collect();
                    $itemNames = $itemsList->pluck('name')->implode(', ');
                    if (empty($itemNames)) {
                        $itemNames = 'Sản phẩm trực tuyến';
                    }
                    $itemsSum = $itemsList->sum(function($i) { return ($i->price ?? 0) * ($i->quantity ?? 1); });
                @endphp
                <tr onclick="location.href='{{ route('hkd.orders.show', $ord->id) }}'" style="cursor: pointer;">
                    <!-- Mã đơn -->
                    <td style="font-weight: 800; color: #059669; font-size: 0.95rem;">
                        #{{ $ord->id }}
                    </td>

                    <!-- Khách hàng -->
                    <td>
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.92rem;">
                            {{ $ord->customer_name ?? 'Khách hàng' }}
                        </div>
                    </td>

                    <!-- Sản phẩm -->
                    <td>
                        <div style="font-weight: 600; color: #334155; font-size: 0.88rem; max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $itemNames }}">
                            {{ $itemNames }}
                        </div>
                    </td>

                    <!-- Tổng tiền -->
                    <td>
                        <div style="font-weight: 800; color: #059669; font-size: 0.95rem;">
                            {{ number_format($ord->total_amount ?? 0, 0, ',', '.') }} đ
                        </div>
                        @if($itemsSum > 0)
                            <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; margin-top: 2px;">
                                [Sản phẩm: {{ number_format($itemsSum, 0, ',', '.') }} đ]
                            </div>
                        @endif
                    </td>

                    <!-- Trạng thái -->
                    <td style="text-align: center;">
                        @if($isCancelled)
                            <span class="status-badge badge-cancelled">
                                🔴 Đã hủy
                            </span>
                        @elseif($isPreparing)
                            <span class="status-badge badge-preparing">
                                🍽️ Đang chuẩn bị
                            </span>
                        @elseif($isConfirmed)
                            <span class="status-badge badge-confirmed">
                                🔵 Đã xác nhận
                            </span>
                        @elseif($isDone)
                            <span class="status-badge badge-completed">
                                🟢 Hoàn thành
                            </span>
                        @else
                            <span class="status-badge badge-pending">
                                🟠 Chờ xác nhận
                            </span>
                        @endif
                    </td>

                    <!-- Thanh toán -->
                    <td>
                        @php
                            $pm = strtolower($ord->payment_method ?? 'cod');
                            $isOnline = str_contains($pm, 'online') || str_contains($pm, 'bank') || str_contains($pm, 'vnpay');
                        @endphp
                        <div style="font-size: 0.84rem; font-weight: 700; color: #334155;">
                            {{ $isOnline ? 'Online' : 'COD' }}
                        </div>
                        <div style="font-size: 0.76rem; color: #64748b; font-weight: 600;">
                            @if($isCancelled)
                                Thất bại
                            @elseif($isDone || $isOnline)
                                Đã thanh toán
                            @else
                                Chưa thanh toán
                            @endif
                        </div>
                    </td>

                    <!-- Thời gian -->
                    <td style="font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                        <div>{{ \Carbon\Carbon::parse($ord->created_at)->format('H:i A') }}</div>
                        <div style="font-weight: 600;">{{ \Carbon\Carbon::parse($ord->created_at)->format('d/m/Y') }}</div>
                    </td>

                    <!-- Thao tác -->
                    <td style="text-align: center;" onclick="event.stopPropagation();">
                        <a href="{{ route('hkd.orders.show', $ord->id) }}" class="btn-detail-action">
                            👁️ Chi tiết
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Table Footer & Pagination -->
    <div style="padding: 16px 24px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 0.85rem; font-weight: 600; color: #64748b;">
            Đang hiển thị dòng {{ $orders->firstItem() ?? 1 }} đến {{ $orders->lastItem() ?? $orders->count() }} của {{ $orders->total() }} đơn hàng
        </div>

        <div>
            {{ $orders->links() }}
        </div>
    </div>

    @endif
</div>

@endsection

@section('scripts')
<script>
function filterHkdOrders(val) {
    if (val === 'all') {
        window.location.href = "{{ route('hkd.orders.index') }}";
    } else {
        window.location.href = "{{ route('hkd.orders.index') }}?status=" + val;
    }
}
</script>
@endsection
