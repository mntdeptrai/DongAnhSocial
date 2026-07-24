@extends('layouts.seller')

@section('title', 'Quản Lý Đơn Hàng — ' . $stallName)

@section('content')

<!-- Workspace Header -->
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
    <div>
        <h1 style="font-family: var(--slr-font); font-size: 1.6rem; font-weight: 800; margin: 0; color: var(--slr-text-main);">
            🛍️ Đơn Hàng Tại {{ $stallName }}
        </h1>
        <p style="font-size: 0.9rem; color: var(--slr-text-muted); margin-top: 4px;">
            Tiếp nhận và xử lý đơn hàng chợ truyền thống của khách đặt trước tại gian hàng.
        </p>
    </div>
</div>

@if(session('success'))
    <div class="admin-alert admin-alert-success" style="margin-bottom: 20px;">
        <span>✅</span>
        <div><strong>Thành công!</strong> {{ session('success') }}</div>
    </div>
@endif

<!-- Orders List Card -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">
            <span>📋</span> Danh Sách Đơn Hàng
            <span style="font-size: 0.82rem; font-weight: 600; color: var(--slr-text-muted); margin-left: 8px;">
                ({{ $orders->total() ?? $orders->count() }} đơn)
            </span>
        </h2>
        <!-- Live polling indicator -->
        <span id="live-indicator" style="font-size: 0.75rem; font-weight: 700; color: var(--slr-text-muted); display: inline-flex; align-items: center; margin-left: auto; padding: 4px 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 20px; color: #065f46;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#d1d5db;margin-right:6px;"></span>
            Đang kết nối...
        </span>
    </div>

    @if($orders->isEmpty())
        <div style="text-align: center; padding: 56px 20px;">
            <div style="font-size: 3rem; margin-bottom: 14px;">🛍️</div>
            <div style="font-weight: 700; font-size: 1.05rem; color: var(--slr-text-main); margin-bottom: 8px;">Chưa có đơn hàng nào</div>
            <div style="font-size: 0.88rem; color: var(--slr-text-muted);">Đơn hàng đặt trước tại gian hàng <strong>{{ $stallName }}</strong> sẽ hiện ở đây.</div>
        </div>
    @else

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 70px;">Mã Đơn</th>
                    <th style="width: 115px;">Thời Gian</th>
                    <th>Khách Hàng</th>
                    <th style="width: 120px;">SĐT</th>
                    <th>Địa Điểm / Ghi Chú</th>
                    <th style="width: 105px;">Tổng Tiền</th>
                    <th style="width: 115px;">Trạng Thái</th>
                    <th style="width: 195px; text-align: center;">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="orders-tbody">
                @foreach($orders as $ord)
                @php
                    $st = $ord->status ?? 'pending';
                    $isPending   = $st === 'pending';
                    $isConfirmed = $st === 'confirmed';
                    $isCancelled = $st === 'cancelled';
                    $isDone      = in_array($st, ['completed', 'delivered']);

                    $badgeStyle  = 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;';
                    $badgeLabel  = 'Chờ xác nhận';
                    if ($isConfirmed) {
                        $badgeStyle = 'background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;';
                        $badgeLabel = 'Đã xác nhận';
                    } elseif ($isDone) {
                        $badgeStyle = 'background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;';
                        $badgeLabel = 'Hoàn tất';
                    } elseif ($isCancelled) {
                        $badgeStyle = 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;';
                        $badgeLabel = 'Đã từ chối';
                    }
                @endphp
                <tr style="cursor: pointer;" onclick="toggleOrderDetail('detail-{{ $ord->id }}')" title="Nhấn để xem chi tiết sản phẩm">
                    <td style="font-weight: 800; color: var(--slr-primary);">#{{ $ord->id }}</td>
                    <td style="font-size: 0.78rem; color: var(--slr-text-muted); line-height: 1.5;">
                        {{ \Carbon\Carbon::parse($ord->created_at)->format('d/m/Y') }}<br>
                        <strong>{{ \Carbon\Carbon::parse($ord->created_at)->format('H:i') }}</strong>
                    </td>
                    <td>
                        <div style="font-weight: 700; font-size: 0.9rem; color: var(--slr-text-main);">
                            {{ $ord->customer_name ?? 'Khách lẻ' }}
                        </div>
                    </td>
                    <td>
                        <a href="tel:{{ $ord->customer_phone }}"
                           style="color: var(--slr-primary); font-weight: 700; text-decoration: none; font-size: 0.88rem;"
                           onclick="event.stopPropagation()">
                            📞 {{ $ord->customer_phone ?? 'N/A' }}
                        </a>
                    </td>
                    <td style="font-size: 0.82rem; color: var(--slr-text-muted); max-width: 200px; line-height: 1.5;">
                        {{ $ord->shipping_address ?? 'N/A' }}
                        @if($ord->notes)
                            <div style="color: #92400e; font-style: italic; margin-top: 3px;">📝 {{ $ord->notes }}</div>
                        @endif
                    </td>
                    <td style="font-weight: 800; color: #10b981; font-size: 1rem;">
                        {{ number_format($ord->total_amount ?? 0, 0, ',', '.') }}đ
                    </td>
                    <td onclick="event.stopPropagation()">
                        <span style="display: inline-block; font-size: 0.73rem; font-weight: 800; padding: 5px 10px; border-radius: 20px; {{ $badgeStyle }}">
                            {{ $badgeLabel }}
                        </span>
                    </td>
                    <td style="text-align: center;" onclick="event.stopPropagation()">
                        @if($isPending)
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <form action="{{ route('seller.orders.update-status', $ord->id) }}" method="POST"
                                      onsubmit="return confirm('Xác nhận đơn hàng #{{ $ord->id }}?')">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" style="display:inline-flex; align-items:center; gap:4px; padding:7px 12px; border-radius:8px; border:none; background:#10b981; color:#fff; font-weight:700; font-size:0.76rem; cursor:pointer; box-shadow:0 2px 8px rgba(16,185,129,0.3); transition:all 0.2s;"
                                        onmouseover="this.style.background='#059669'"
                                        onmouseout="this.style.background='#10b981'">
                                        ✅ Xác nhận
                                    </button>
                                </form>
                                <form action="{{ route('seller.orders.update-status', $ord->id) }}" method="POST"
                                      onsubmit="return confirm('Từ chối đơn hàng #{{ $ord->id }}?')">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" style="display:inline-flex; align-items:center; gap:4px; padding:7px 10px; border-radius:8px; border:1.5px solid rgba(239,68,68,0.25); background:#fef2f2; color:#ef4444; font-weight:700; font-size:0.76rem; cursor:pointer; transition:all 0.2s;"
                                        onmouseover="this.style.background='#fee2e2'"
                                        onmouseout="this.style.background='#fef2f2'">
                                        ✕ Từ chối
                                    </button>
                                </form>
                            </div>
                        @elseif($isConfirmed)
                            <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">✅ Đã xác nhận</span>
                        @elseif($isCancelled)
                            <span style="font-size: 0.75rem; color: #ef4444; font-weight: 700;">✕ Đã từ chối</span>
                        @elseif($isDone)
                            <span style="font-size: 0.75rem; color: #0ea5e9; font-weight: 700;">🎉 Hoàn tất</span>
                        @else
                            <span style="font-size: 0.75rem; color: var(--slr-text-muted);">—</span>
                        @endif
                    </td>
                </tr>

                <!-- Chi tiết sản phẩm trong đơn (ẩn theo mặc định) -->
                <tr id="detail-{{ $ord->id }}" style="display: none; background: #fffbeb;">
                    <td colspan="8" style="padding: 0;">
                        <div style="padding: 16px 28px 20px; border-top: 2px dashed #fde68a;">

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                                <!-- Cột trái: Chi tiết sản phẩm -->
                                <div>
                                    <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--slr-primary); margin-bottom: 10px;">
                                        🧾 Sản Phẩm Trong Đơn #{{ $ord->id }}
                                    </div>

                                    @if(isset($ord->items) && $ord->items->isNotEmpty())
                                        @foreach($ord->items as $item)
                                        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #ffffff; border: 1px solid #fde68a; border-radius: 10px; margin-bottom: 8px;">
                                            <div style="width: 36px; height: 36px; background: #fef3c7; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">🥘</div>
                                            <div style="flex: 1;">
                                                <div style="font-weight: 700; font-size: 0.88rem; color: var(--slr-text-main);">{{ $item->name }}</div>
                                                <div style="font-size: 0.78rem; color: var(--slr-text-muted); margin-top: 2px;">
                                                    {{ number_format($item->price, 0, ',', '.') }}đ × {{ $item->quantity }}
                                                </div>
                                            </div>
                                            <div style="font-weight: 800; color: var(--slr-primary); font-size: 0.92rem;">
                                                {{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ
                                            </div>
                                        </div>
                                        @endforeach

                                        <!-- Tổng -->
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: var(--slr-primary); color: #fff; border-radius: 10px; margin-top: 4px;">
                                            <span style="font-weight: 700; font-size: 0.88rem;">Tổng thanh toán</span>
                                            <span style="font-weight: 800; font-size: 1.05rem;">{{ number_format($ord->total_amount, 0, ',', '.') }}đ</span>
                                        </div>
                                    @else
                                        <div style="color: var(--slr-text-muted); font-size: 0.85rem; font-style: italic;">
                                            Không có dữ liệu sản phẩm chi tiết.
                                        </div>
                                    @endif
                                </div>

                                <!-- Cột phải: Thông tin giao nhận -->
                                <div>
                                    <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--slr-primary); margin-bottom: 10px;">
                                        📦 Thông Tin Đặt Hàng
                                    </div>
                                    <div style="background: #fff; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 9px;">
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: var(--slr-text-muted); font-weight: 600; flex-shrink: 0;">Mã đơn hàng</span>
                                            <span style="font-weight: 800; color: var(--slr-primary);">#ORD{{ str_pad($ord->id, 6, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: var(--slr-text-muted); font-weight: 600; flex-shrink: 0;">Thời gian đặt</span>
                                            <span style="font-weight: 700;">{{ \Carbon\Carbon::parse($ord->created_at)->format('H:i, d/m/Y') }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: var(--slr-text-muted); font-weight: 600; flex-shrink: 0;">Khách hàng</span>
                                            <span style="font-weight: 700;">{{ $ord->customer_name ?? 'Khách lẻ' }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: var(--slr-text-muted); font-weight: 600; flex-shrink: 0;">Số điện thoại</span>
                                            <a href="tel:{{ $ord->customer_phone }}" style="font-weight: 700; color: var(--slr-primary); text-decoration: none;">{{ $ord->customer_phone }}</a>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: var(--slr-text-muted); font-weight: 600; flex-shrink: 0;">Địa điểm nhận</span>
                                            <span style="font-weight: 700;">{{ $ord->shipping_address }}</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; font-size: 0.83rem;">
                                            <span style="width: 130px; color: var(--slr-text-muted); font-weight: 600; flex-shrink: 0;">Thanh toán</span>
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
                                            <span style="width: 130px; color: var(--slr-text-muted); font-weight: 600; flex-shrink: 0;">Ghi chú</span>
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
        <div style="padding: 20px 24px; border-top: 1px solid var(--slr-border-std);">
            {{ $orders->links() }}
        </div>
    @endif

    @endif
</div>

@endsection

@section('scripts')
<script>
/* ============================================================
   SELLER ORDERS — REAL-TIME POLLING
   Fetch /seller/api/orders mỗi 15 giây, tự update UI
   ============================================================ */

const POLL_INTERVAL_MS = 15000; // 15 giây
const API_URL          = '/seller/api/orders';
let   knownOrderIds    = new Set();
let   pollTimer        = null;
let   pollCount        = 0;

/* ------ Trạng thái badge ------ */
function statusBadge(st) {
    const map = {
        pending:   { label: 'Chờ xác nhận', style: 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;' },
        confirmed: { label: 'Đã xác nhận',  style: 'background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;' },
        cancelled: { label: 'Đã từ chối',   style: 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;' },
        completed: { label: 'Hoàn tất',     style: 'background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;' },
        delivered: { label: 'Hoàn tất',     style: 'background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;' },
    };
    return map[st] || { label: st, style: 'background:#f3f4f6; color:#6b7280;' };
}

/* ------ Cập nhật badge của một hàng ------ */
function updateRowBadge(orderId, newStatus) {
    const badgeEl = document.getElementById('badge-' + orderId);
    const actEl   = document.getElementById('actions-' + orderId);
    if (!badgeEl) return;

    const { label, style } = statusBadge(newStatus);
    badgeEl.setAttribute('style', 'display:inline-block; font-size:0.73rem; font-weight:800; padding:5px 10px; border-radius:20px; ' + style);
    badgeEl.textContent = label;

    if (!actEl) return;
    if (newStatus === 'confirmed') {
        actEl.innerHTML = '<span style="font-size:0.75rem; color:#10b981; font-weight:700;">✅ Đã xác nhận</span>';
    } else if (newStatus === 'cancelled') {
        actEl.innerHTML = '<span style="font-size:0.75rem; color:#ef4444; font-weight:700;">✕ Đã từ chối</span>';
    } else if (['completed','delivered'].includes(newStatus)) {
        actEl.innerHTML = '<span style="font-size:0.75rem; color:#0ea5e9; font-weight:700;">🎉 Hoàn tất</span>';
    }
}

/* ------ Toast notification ------ */
function showToast(msg, type = 'info') {
    const colors = {
        info:    { bg: '#1c1007', border: '#d97706', icon: '🔔' },
        success: { bg: '#052e16', border: '#10b981', icon: '✅' },
        warning: { bg: '#451a03', border: '#f59e0b', icon: '⚠️' },
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
        transition: 'opacity 0.4s', opacity: '1',
        maxWidth: '360px',
    });
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 4000);
}

/* ------ Thêm hàng đơn mới vào đầu bảng ------ */
function prependNewOrder(ord) {
    const tbody = document.getElementById('orders-tbody');
    if (!tbody) return;

    const { label, style } = statusBadge(ord.status || 'pending');
    const createdAt = new Date(ord.created_at.replace(' ', 'T'));
    const dateStr   = createdAt.toLocaleDateString('vi-VN');
    const timeStr   = createdAt.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

    const total = parseInt(ord.total_amount || 0).toLocaleString('vi-VN');

    // Sản phẩm
    let itemsHtml = '';
    if (ord.items && ord.items.length > 0) {
        ord.items.forEach(it => {
            const itTotal = (parseFloat(it.price) * parseInt(it.quantity)).toLocaleString('vi-VN');
            const itPrice = parseFloat(it.price).toLocaleString('vi-VN');
            itemsHtml += `
            <div style="display:flex; align-items:center; gap:10px; padding:10px 14px; background:#fff; border:1px solid #fde68a; border-radius:10px; margin-bottom:8px;">
                <div style="width:36px;height:36px;background:#fef3c7;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">🥘</div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:0.88rem;">${it.name}</div>
                    <div style="font-size:0.78rem;color:#78716c;margin-top:2px;">${itPrice}đ × ${it.quantity}</div>
                </div>
                <div style="font-weight:800;color:var(--slr-primary);font-size:0.92rem;">${itTotal}đ</div>
            </div>`;
        });
    }

    const ordNum = String(ord.id).padStart(6, '0');

    const mainRow = document.createElement('tr');
    mainRow.id = 'order-row-' + ord.id;
    mainRow.className = 'new-order-flash';
    mainRow.style.cursor = 'pointer';
    mainRow.onclick = () => toggleOrderDetail('detail-' + ord.id);
    mainRow.innerHTML = `
        <td style="font-weight:800; color:var(--slr-primary);">#${ord.id}</td>
        <td style="font-size:0.78rem;color:var(--slr-text-muted);line-height:1.5;">${dateStr}<br><strong>${timeStr}</strong></td>
        <td><div style="font-weight:700;font-size:0.9rem;color:var(--slr-text-main);">${ord.customer_name || 'Khách lẻ'}</div></td>
        <td><a href="tel:${ord.customer_phone}" style="color:var(--slr-primary);font-weight:700;text-decoration:none;font-size:0.88rem;" onclick="event.stopPropagation()">📞 ${ord.customer_phone || 'N/A'}</a></td>
        <td style="font-size:0.82rem;color:var(--slr-text-muted);max-width:200px;line-height:1.5;">${ord.shipping_address || 'N/A'}</td>
        <td style="font-weight:800;color:#10b981;font-size:1rem;">${total}đ</td>
        <td onclick="event.stopPropagation()">
            <span id="badge-${ord.id}" style="display:inline-block;font-size:0.73rem;font-weight:800;padding:5px 10px;border-radius:20px; ${style}">${label}</span>
        </td>
        <td style="text-align:center;" onclick="event.stopPropagation()" id="actions-${ord.id}">
            <div style="display:flex;gap:6px;justify-content:center;">
                <form action="/seller/orders/${ord.id}/status" method="POST" onsubmit="return confirm('Xác nhận đơn #${ord.id}?')">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]')?.content || ''}">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" style="display:inline-flex;align-items:center;gap:4px;padding:7px 12px;border-radius:8px;border:none;background:#10b981;color:#fff;font-weight:700;font-size:0.76rem;cursor:pointer;">✅ Xác nhận</button>
                </form>
                <form action="/seller/orders/${ord.id}/status" method="POST" onsubmit="return confirm('Từ chối đơn #${ord.id}?')">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]')?.content || ''}">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" style="display:inline-flex;align-items:center;gap:4px;padding:7px 10px;border-radius:8px;border:1.5px solid rgba(239,68,68,0.25);background:#fef2f2;color:#ef4444;font-weight:700;font-size:0.76rem;cursor:pointer;">✕ Từ chối</button>
                </form>
            </div>
        </td>`;

    const detailRow = document.createElement('tr');
    detailRow.id = 'detail-' + ord.id;
    detailRow.style.display = 'none';
    detailRow.style.background = '#fffbeb';
    detailRow.innerHTML = `
        <td colspan="8" style="padding:0;">
            <div style="padding:16px 28px 20px;border-top:2px dashed #fde68a;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div>
                        <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:var(--slr-primary);margin-bottom:10px;">🧾 Sản Phẩm Trong Đơn #${ord.id}</div>
                        ${itemsHtml || '<div style="color:var(--slr-text-muted);font-style:italic;font-size:0.85rem;">Không có chi tiết.</div>'}
                        ${ord.items && ord.items.length > 0 ? `<div style="display:flex;justify-content:space-between;padding:10px 14px;background:var(--slr-primary);color:#fff;border-radius:10px;margin-top:4px;"><span style="font-weight:700;font-size:.88rem;">Tổng</span><span style="font-weight:800;font-size:1.05rem;">${total}đ</span></div>` : ''}
                    </div>
                    <div>
                        <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:var(--slr-primary);margin-bottom:10px;">📦 Thông Tin Đặt Hàng</div>
                        <div style="background:#fff;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;display:flex;flex-direction:column;gap:9px;font-size:0.83rem;">
                            <div style="display:flex;gap:10px;"><span style="width:130px;color:var(--slr-text-muted);font-weight:600;">Mã đơn hàng</span><span style="font-weight:800;color:var(--slr-primary);">#ORD${ordNum}</span></div>
                            <div style="display:flex;gap:10px;"><span style="width:130px;color:var(--slr-text-muted);font-weight:600;">Khách hàng</span><span style="font-weight:700;">${ord.customer_name || 'Khách lẻ'}</span></div>
                            <div style="display:flex;gap:10px;"><span style="width:130px;color:var(--slr-text-muted);font-weight:600;">Số điện thoại</span><a href="tel:${ord.customer_phone}" style="font-weight:700;color:var(--slr-primary);text-decoration:none;">${ord.customer_phone}</a></div>
                            <div style="display:flex;gap:10px;"><span style="width:130px;color:var(--slr-text-muted);font-weight:600;">Địa điểm nhận</span><span style="font-weight:700;">${ord.shipping_address}</span></div>
                            <div style="display:flex;gap:10px;"><span style="width:130px;color:var(--slr-text-muted);font-weight:600;">Thanh toán</span><span style="font-weight:700;">${ord.payment_method || 'N/A'}</span></div>
                            ${ord.notes ? `<div style="display:flex;gap:10px;"><span style="width:130px;color:var(--slr-text-muted);font-weight:600;">Ghi chú</span><span style="font-style:italic;color:#92400e;">${ord.notes}</span></div>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        </td>`;

    tbody.prepend(detailRow);
    tbody.prepend(mainRow);
    setTimeout(() => mainRow.classList.remove('new-order-flash'), 3000);
}

/* ------ Polling chính ------ */
async function pollOrders() {
    try {
        const res = await fetch(API_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();

        pollCount++;
        updateLiveIndicator(data.polled_at);

        // Lần đầu: khởi tạo set ID đã biết
        if (pollCount === 1) {
            data.orders.forEach(o => knownOrderIds.add(o.id));
            return;
        }

        let newCount = 0;
        data.orders.forEach(ord => {
            if (!knownOrderIds.has(ord.id)) {
                // Đơn MỚI hoàn toàn — thêm vào đầu bảng
                knownOrderIds.add(ord.id);
                prependNewOrder(ord);
                newCount++;
            } else {
                // Đơn cũ — cập nhật badge & nút nếu trạng thái thay đổi
                const badgeEl = document.getElementById('badge-' + ord.id);
                if (badgeEl) {
                    const curLabel = badgeEl.textContent.trim();
                    const { label } = statusBadge(ord.status);
                    if (curLabel !== label) updateRowBadge(ord.id, ord.status);
                }
            }
        });

        if (newCount > 0) {
            showToast(`🛍️ Có ${newCount} đơn hàng mới vừa đến!`, 'warning');
            // Rung chuông tab
            const origTitle = document.title;
            let blink = setInterval(() => {
                document.title = document.title === origTitle ? `🔔 (${newCount}) Đơn mới!` : origTitle;
            }, 800);
            setTimeout(() => { clearInterval(blink); document.title = origTitle; }, 8000);
        }

    } catch (e) {
        console.warn('[Seller Poll] Lỗi fetch:', e.message);
    }
}

/* ------ Live indicator ------ */
function updateLiveIndicator(polledAt) {
    const el = document.getElementById('live-indicator');
    if (!el) return;
    const t = polledAt ? polledAt.slice(11, 16) : '--:--';
    el.innerHTML = `<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10b981;animation:pulse-dot 1.5s infinite;margin-right:6px;"></span> Live · cập nhật lúc ${t}`;
}

/* ------ Toggle chi tiết ------ */
function toggleOrderDetail(id) {
    const row = document.getElementById(id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}

/* ------ Khởi động ------ */
document.addEventListener('DOMContentLoaded', function () {
    pollOrders();
    pollTimer = setInterval(pollOrders, POLL_INTERVAL_MS);
});

/* Dừng polling khi rời trang */
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
@keyframes flash-new {
    0%   { background: #fef3c7; }
    50%  { background: #fde68a; }
    100% { background: transparent; }
}
.new-order-flash { animation: flash-new 1.5s ease 3; }
</style>
@endsection
