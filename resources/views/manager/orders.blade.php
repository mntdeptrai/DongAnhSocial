@extends('layouts.manager')

@section('title', 'Quản Lý Đơn Hàng — ' . ($marketName ?? 'Chợ'))

@section('content')

<!-- Workspace Header -->
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
    <div>
        <h1 style="font-family: var(--mgr-font, 'Inter', sans-serif); font-size: 1.6rem; font-weight: 800; margin: 0; color: #0f172a;">
            🛍️ Quản Lý Đơn Hàng Toàn Chợ
        </h1>
        <p style="font-size: 0.9rem; color: #64748b; margin-top: 4px;">
            Tiếp nhận, xử lý và giám sát tất cả đơn hàng chợ truyền thống tại <strong>{{ $marketName ?? 'Chợ' }}</strong>.
        </p>
    </div>
</div>

@if(session('success'))
    <div style="display: flex; align-items: center; gap: 10px; padding: 14px 18px; background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 12px; margin-bottom: 20px; font-size: 0.88rem; font-weight: 600; color: #065f46;">
        <span>✅</span>
        <div><strong>Thành công!</strong> {{ session('success') }}</div>
    </div>
@endif

<!-- Orders List Card -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid #e2e8f0;">
        <h2 style="font-size: 1rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; margin: 0;">
            <span>📋</span> Danh Sách Đơn Hàng
            <span style="font-size: 0.82rem; font-weight: 600; color: #64748b; margin-left: 8px;">
                ({{ $orders->total() ?? $orders->count() }} đơn)
            </span>
        </h2>
        <!-- Live polling indicator -->
        <span id="live-indicator" style="font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; padding: 4px 12px; background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 20px; color: #0d9488;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#d1d5db;margin-right:6px;"></span>
            Đang kết nối...
        </span>
    </div>

    @if($orders->isEmpty())
        <div style="text-align: center; padding: 56px 20px;">
            <div style="font-size: 3rem; margin-bottom: 14px;">🛍️</div>
            <div style="font-weight: 700; font-size: 1.05rem; color: #0f172a; margin-bottom: 8px;">Chưa có đơn hàng nào</div>
            <div style="font-size: 0.88rem; color: #64748b;">Đơn hàng đặt trước tại <strong>{{ $marketName ?? 'Chợ' }}</strong> sẽ hiện ở đây.</div>
        </div>
    @else

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px 14px; text-align: left; font-weight: 800; color: #475569; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em; width: 70px;">Mã Đơn</th>
                    <th style="padding: 12px 14px; text-align: left; font-weight: 800; color: #475569; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em; width: 115px;">Thời Gian</th>
                    <th style="padding: 12px 14px; text-align: left; font-weight: 800; color: #475569; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em;">Gian Hàng</th>
                    <th style="padding: 12px 14px; text-align: left; font-weight: 800; color: #475569; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em;">Khách Hàng</th>
                    <th style="padding: 12px 14px; text-align: left; font-weight: 800; color: #475569; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em; width: 120px;">SĐT</th>
                    <th style="padding: 12px 14px; text-align: left; font-weight: 800; color: #475569; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em; width: 105px;">Tổng Tiền</th>
                    <th style="padding: 12px 14px; text-align: left; font-weight: 800; color: #475569; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em; width: 115px;">Trạng Thái</th>
                    <th style="padding: 12px 14px; text-align: center; font-weight: 800; color: #475569; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em; width: 195px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="orders-tbody">
                @foreach($orders as $ord)
                @php
                    $st = strtolower($ord->status ?? 'pending');
                    $isConfirmed = in_array($st, ['confirmed', 'preparing', 'đang chuẩn bị']);
                    $isReady     = in_array($st, ['ready', 'sẵn sàng', 'chờ lấy']);
                    $isDone      = in_array($st, ['completed', 'delivered', 'hoàn thành', 'hoàn tất']);
                    $isCancelled = in_array($st, ['cancelled', 'rejected', 'đã từ chối', 'đã hủy']);
                    $isPending   = !$isConfirmed && !$isReady && !$isDone && !$isCancelled;

                    $badgeStyle  = 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;';
                    $badgeLabel  = 'Chờ xác nhận';
                    if ($isConfirmed) {
                        $badgeStyle = 'background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;';
                        $badgeLabel = '✅ Sạp đã nhận đơn';
                    } elseif ($isReady) {
                        $badgeStyle = 'background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd;';
                        $badgeLabel = '🏪 Sẵn sàng tại sạp';
                    } elseif ($isDone) {
                        $badgeStyle = 'background:#ecfdf5; color:#047857; border:1px solid #6ee7b7;';
                        $badgeLabel = '🎉 Hoàn thành';
                    } elseif ($isCancelled) {
                        $badgeStyle = 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;';
                        $badgeLabel = '✕ Đã hủy';
                    }
                @endphp
                <tr style="cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''" onclick="location.href='{{ route('admin.orders.show', $ord->id) }}'" title="Nhấn để xem chi tiết đơn hàng">
                    <td style="padding: 12px 14px; font-weight: 800; color: #0d9488;">#ORD-{{ str_pad($ord->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td style="padding: 12px 14px; font-size: 0.78rem; color: #64748b; line-height: 1.5;">
                        {{ \Carbon\Carbon::parse($ord->created_at)->format('d/m/Y') }}<br>
                        <strong>{{ \Carbon\Carbon::parse($ord->created_at)->format('H:i') }}</strong>
                    </td>
                    <td style="padding: 12px 14px;">
                        <div style="font-weight: 700; font-size: 0.84rem; color: #0f172a;">
                            🏪 {{ $ord->stall_name ?? 'N/A' }}
                        </div>
                    </td>
                    <td style="padding: 12px 14px;">
                        <div style="font-weight: 700; font-size: 0.9rem; color: #0f172a;">
                            {{ $ord->customer_name ?? 'Khách lẻ' }}
                        </div>
                    </td>
                    <td style="padding: 12px 14px;" onclick="event.stopPropagation()">
                        <a href="tel:{{ $ord->customer_phone }}"
                           style="color: #0d9488; font-weight: 700; text-decoration: none; font-size: 0.88rem;">
                            📞 {{ $ord->customer_phone ?? 'N/A' }}
                        </a>
                    </td>
                    <td style="padding: 12px 14px; font-weight: 800; color: #10b981; font-size: 1rem;">
                        {{ number_format($ord->total_amount ?? 0, 0, ',', '.') }}đ
                    </td>
                    <td style="padding: 12px 14px;" onclick="event.stopPropagation()">
                        <span id="badge-{{ $ord->id }}" style="display: inline-block; font-size: 0.73rem; font-weight: 800; padding: 5px 10px; border-radius: 20px; {{ $badgeStyle }}">
                            {{ $badgeLabel }}
                        </span>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;" onclick="event.stopPropagation()">
                        <a href="{{ route('admin.orders.show', $ord->id) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: #f3f4f6; color: #374151; font-weight: 700; font-size: 0.78rem; text-decoration: none; border: 1px solid #e5e7eb; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'; this.style.color='#111827';" onmouseout="this.style.background='#f3f4f6'; this.style.color='#374151';">
                            👁️ Chi tiết
                        </a>
                    </td>
                </tr>

                <!-- Chi tiết sản phẩm trong đơn (ẩn theo mặc định) -->
                <tr id="detail-{{ $ord->id }}" style="display: none; background: #f0fdfa;">
                    <td colspan="9" style="padding: 0;">
                        <div style="padding: 16px 28px 20px; border-top: 2px dashed #99f6e4;">

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                                <!-- Cột trái: Chi tiết sản phẩm -->
                                <div>
                                    <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0d9488; margin-bottom: 10px;">
                                        🧾 Sản Phẩm Trong Đơn #ORD-{{ str_pad($ord->id, 5, '0', STR_PAD_LEFT) }}
                                    </div>

                                    @if(isset($ord->items) && $ord->items->isNotEmpty())
                                        @foreach($ord->items as $item)
                                        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #ffffff; border: 1px solid #99f6e4; border-radius: 10px; margin-bottom: 8px;">
                                            <div style="width: 36px; height: 36px; background: #ccfbf1; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">🥘</div>
                                            <div style="flex: 1;">
                                                <div style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">{{ $item->name }}</div>
                                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                                    {{ number_format($item->price, 0, ',', '.') }}đ × {{ $item->quantity }}
                                                </div>
                                            </div>
                                            <div style="font-weight: 800; color: #0d9488; font-size: 0.92rem;">
                                                {{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ
                                            </div>
                                        </div>
                                        @endforeach

                                        <!-- Tổng -->
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #0d9488; color: #fff; border-radius: 10px; margin-top: 4px;">
                                            <span style="font-weight: 700; font-size: 0.88rem;">Tổng thanh toán</span>
                                            <span style="font-weight: 800; font-size: 1.05rem;">{{ number_format($ord->total_amount, 0, ',', '.') }}đ</span>
                                        </div>
                                    @else
                                        <div style="color: #64748b; font-size: 0.85rem; font-style: italic;">
                                            Không có dữ liệu sản phẩm chi tiết.
                                        </div>
                                    @endif
                                </div>

                                <!-- Cột phải: Thông tin giao nhận -->
                                <div>
                                    <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0d9488; margin-bottom: 10px;">
                                        📦 Thông Tin Đặt Hàng
                                    </div>
                                    <div style="background: #fff; border: 1px solid #99f6e4; border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 9px;">
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0;">Mã đơn hàng</span>
                                            <span style="font-weight: 800; color: #0d9488;">#ORD{{ str_pad($ord->id, 6, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0;">Gian hàng</span>
                                            <span style="font-weight: 700;">🏪 {{ $ord->stall_name ?? 'N/A' }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0;">Thời gian đặt</span>
                                            <span style="font-weight: 700;">{{ \Carbon\Carbon::parse($ord->created_at)->format('H:i, d/m/Y') }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0;">Khách hàng</span>
                                            <span style="font-weight: 700;">{{ $ord->customer_name ?? 'Khách lẻ' }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0;">Số điện thoại</span>
                                            <a href="tel:{{ $ord->customer_phone }}" style="font-weight: 700; color: #0d9488; text-decoration: none;">{{ $ord->customer_phone }}</a>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0;">Địa điểm nhận</span>
                                            <span style="font-weight: 700;">{{ $ord->shipping_address }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0;">Thanh toán</span>
                                            <span style="font-weight: 700;">
                                                @if($ord->payment_method === 'Online' || $ord->payment_method === 'online')
                                                    💳 Chuyển khoản Online
                                                @elseif($ord->payment_method === 'cod' || $ord->payment_method === 'COD')
                                                    💵 COD (Trả tiền mặt)
                                                @else
                                                    {{ $ord->payment_method }}
                                                @endif
                                            </span>
                                        </div>
                                        @if($ord->notes)
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0;">Ghi chú</span>
                                            <span style="font-style: italic; color: #92400e;">{{ $ord->notes }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div style="padding: 20px 24px; border-top: 1px solid #e2e8f0;">
            {{ $orders->links() }}
        </div>
    @endif

    @endif
</div>

@endsection

@section('scripts')
<script>
/* ============================================================
   MANAGER ORDERS — REAL-TIME POLLING
   Fetch /admin/api/orders mỗi 15 giây, tự update UI
   ============================================================ */

const POLL_INTERVAL_MS = 15000;
const API_URL          = '/admin/api/orders';
let   knownOrderIds    = new Set();
let   pollTimer        = null;
let   pollCount        = 0;

function statusBadge(st) {
    const map = {
        pending:    { label: 'Chờ xác nhận', style: 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;' },
        confirmed:  { label: 'Đã xác nhận',  style: 'background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;' },
        processing: { label: 'Đang xử lý',   style: 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;' },
        cancelled:  { label: 'Đã hủy',       style: 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;' },
        completed:  { label: 'Hoàn tất',     style: 'background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;' },
        delivered:  { label: 'Hoàn tất',     style: 'background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;' },
    };
    return map[st] || { label: st, style: 'background:#f3f4f6; color:#6b7280;' };
}

function updateRowBadge(orderId, newStatus) {
    const badgeEl = document.getElementById('badge-' + orderId);
    if (!badgeEl) return;
    const { label, style } = statusBadge(newStatus);
    badgeEl.setAttribute('style', 'display:inline-block; font-size:0.73rem; font-weight:800; padding:5px 10px; border-radius:20px; ' + style);
    badgeEl.textContent = label;
}

function showToast(msg, type = 'info') {
    const colors = {
        info:    { bg: '#042f2e', border: '#0d9488', icon: '🔔' },
        success: { bg: '#052e16', border: '#10b981', icon: '✅' },
        warning: { bg: '#1c1007', border: '#f59e0b', icon: '⚠️' },
    };
    const c = colors[type] || colors.info;
    const toast = document.createElement('div');
    toast.innerHTML = `<span style="font-size:1.1rem;">${c.icon}</span><span>${msg}</span>`;
    Object.assign(toast.style, {
        position: 'fixed', bottom: '28px', right: '28px', zIndex: '99999',
        background: c.bg, color: '#fff', border: `1.5px solid ${c.border}`,
        borderRadius: '12px', padding: '12px 20px', fontSize: '0.88rem',
        fontWeight: '700', display: 'flex', alignItems: 'center', gap: '10px',
        boxShadow: '0 8px 32px rgba(0,0,0,0.4)',
        transition: 'opacity 0.4s', opacity: '1', maxWidth: '360px',
    });
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 4000);
}

async function pollOrders() {
    try {
        const res = await fetch(API_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();

        pollCount++;
        updateLiveIndicator(data.polled_at);

        if (pollCount === 1) {
            data.orders.forEach(o => knownOrderIds.add(o.id));
            return;
        }

        let newCount = 0;
        data.orders.forEach(ord => {
            if (!knownOrderIds.has(ord.id)) {
                knownOrderIds.add(ord.id);
                newCount++;
            } else {
                updateRowBadge(ord.id, ord.status);
            }
        });

        if (newCount > 0) {
            showToast(`🛍️ Có ${newCount} đơn hàng mới vừa đến!`, 'warning');
            // Reload page to show new orders
            setTimeout(() => location.reload(), 2000);
        }
    } catch (e) {
        console.warn('[Manager Poll] Lỗi fetch:', e.message);
    }
}

function updateLiveIndicator(polledAt) {
    const el = document.getElementById('live-indicator');
    if (!el) return;
    const t = polledAt ? polledAt.slice(11, 16) : '--:--';
    el.innerHTML = `<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#0d9488;animation:pulse-dot 1.5s infinite;margin-right:6px;"></span> Live · cập nhật lúc ${t}`;
}

function toggleOrderDetail(id) {
    const row = document.getElementById(id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    pollOrders();
    pollTimer = setInterval(pollOrders, POLL_INTERVAL_MS);
});

document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        clearInterval(pollTimer);
    } else {
        pollOrders();
        pollTimer = setInterval(pollOrders, POLL_INTERVAL_MS);
    }
});
</script>

<style>
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.5; transform: scale(1.4); }
}
</style>
@endsection
