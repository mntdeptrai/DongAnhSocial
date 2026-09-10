@extends('layouts.hkd')

@section('title', 'Quản Lý Đơn Hàng & Duyệt Đơn — ' . $eatery->name)
@section('title_header', 'Tiếp Nhận & Duyệt Đơn Hàng Trực Tuyến')

@section('content')

<style>
    .hkd-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 24px; margin-bottom: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .hkd-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .hkd-card-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }

    .hkd-filter-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .hkd-filter-btn {
        padding: 8px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; text-decoration: none;
        border: 1px solid #cbd5e1; background: #ffffff; color: #475569; transition: all 0.2s;
    }
    .hkd-filter-btn.active, .hkd-filter-btn:hover { background: #059669; color: #ffffff; border-color: #059669; }

    .hkd-table { width: 100%; border-collapse: collapse; }
    .hkd-table th { background: #f8fafc; text-align: left; padding: 12px 16px; font-size: 0.8rem; font-weight: 800; color: #475569; border-bottom: 1px solid #e2e8f0; }
    .hkd-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; font-weight: 600; color: #334155; }
</style>

<div class="hkd-card">
    <div class="hkd-card-header">
        <div class="hkd-card-title">
            <i class="fa-solid fa-receipt" style="color: #059669;"></i>
            <span>Danh Sách Đơn Hàng Giao Dịch ({{ $orders->total() }})</span>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="hkd-filter-bar">
        <a href="{{ route('hkd.orders.index') }}" class="hkd-filter-btn {{ empty($activeStatus) ? 'active' : '' }}">Tất cả đơn</a>
        <a href="{{ route('hkd.orders.index', ['status' => 'pending']) }}" class="hkd-filter-btn {{ $activeStatus === 'pending' ? 'active' : '' }}">⏳ Chờ duyệt</a>
        <a href="{{ route('hkd.orders.index', ['status' => 'processing']) }}" class="hkd-filter-btn {{ $activeStatus === 'processing' ? 'active' : '' }}">🔄 Đang xử lý</a>
        <a href="{{ route('hkd.orders.index', ['status' => 'completed']) }}" class="hkd-filter-btn {{ $activeStatus === 'completed' ? 'active' : '' }}">✅ Đã hoàn thành</a>
        <a href="{{ route('hkd.orders.index', ['status' => 'cancelled']) }}" class="hkd-filter-btn {{ $activeStatus === 'cancelled' ? 'active' : '' }}">❌ Đã hủy</a>
    </div>

    @if($orders->isEmpty())
        <div style="text-align: center; padding: 60px; color: #94a3b8;">
            <i class="fa-solid fa-basket-shopping" style="font-size: 3rem; margin-bottom: 14px;"></i>
            <p style="font-weight: 700; font-size: 1.05rem;">Không tìm thấy đơn hàng nào.</p>
        </div>
    @else
        <table class="hkd-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $ord)
                <tr>
                    <td style="font-weight: 800; color: #0f172a;">#{{ $ord->id }}</td>
                    <td>{{ date('H:i d/m/Y', strtotime($ord->created_at)) }}</td>
                    <td>{{ $ord->customer_name ?? 'Khách hàng' }}</td>
                    <td>{{ $ord->customer_phone ?? 'N/A' }}</td>
                    <td style="font-weight: 900; color: #059669;">{{ number_format($ord->total_amount ?? 0) }} đ</td>
                    <td>
                        @if($ord->status === 'pending')
                            <span style="background: #fef3c7; color: #b45309; padding: 4px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;">⏳ Chờ duyệt</span>
                        @elseif($ord->status === 'processing')
                            <span style="background: #e0f2fe; color: #0369a1; padding: 4px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;">🔄 Đang xử lý</span>
                        @elseif(in_array($ord->status, ['completed', 'delivered']))
                            <span style="background: #dcfce7; color: #15803d; padding: 4px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;">✅ Hoàn thành</span>
                        @elseif($ord->status === 'cancelled')
                            <span style="background: #fee2e2; color: #b91c1c; padding: 4px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;">❌ Đã hủy</span>
                        @else
                            <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;">{{ $ord->status }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('hkd.orders.show', $ord->id) }}" class="hkd-btn-action hkd-btn-sky" style="padding: 6px 14px; font-size: 0.8rem;">
                            <i class="fa-solid fa-eye"></i> Xem & Duyệt
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 24px;">
            {{ $orders->links() }}
        </div>
    @endif
</div>

@endsection
