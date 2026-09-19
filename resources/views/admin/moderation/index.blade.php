@extends('layouts.admin')

@section('title', 'Trung Tâm Kiểm Duyệt & Xử Lý Vi Phạm')

@section('content')

@php
    $filter = request('status', 'all');
@endphp

<style>
/* ═══════════════════════════════════════════════════════════════
   🛡️ ADMIN MODERATION COMMAND CENTER THEME
   Palette: Cyber Neon Slate + Crimson Alert + Emerald Safe
   ═══════════════════════════════════════════════════════════════ */
.mod-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #450a0a 100%);
    border: 1.5px solid rgba(239, 68, 68, 0.25);
    border-radius: 24px;
    padding: 36px 40px;
    color: #ffffff;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(2, 6, 23, 0.7);
}

.mod-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(248, 113, 113, 0.4);
    border-radius: 100px;
    color: #f87171;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 12px;
}

.mod-hero h1 {
    font-size: 1.95rem;
    font-weight: 900;
    margin: 0 0 8px 0;
    letter-spacing: -0.02em;
}

.mod-hero p {
    font-size: 0.95rem;
    color: #cbd5e1;
    line-height: 1.6;
    margin: 0;
    max-width: 780px;
}

.mod-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 28px;
}

@media (max-width: 900px) {
    .mod-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.mod-stat-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 22px 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03);
    position: relative;
    overflow: hidden;
}

.mod-stat-card.alert {
    border-left: 5px solid #ef4444;
}

.mod-stat-card.success {
    border-left: 5px solid #10b981;
}

.mod-stat-card.warning {
    border-left: 5px solid #f59e0b;
}

.mod-stat-card.info {
    border-left: 5px solid #0ea5e9;
}

.mod-stat-val {
    font-size: 2.1rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.1;
    margin-bottom: 4px;
}

.mod-stat-lbl {
    font-size: 0.84rem;
    font-weight: 700;
    color: #64748b;
}

.mod-stat-icon {
    position: absolute;
    right: 16px;
    top: 18px;
    font-size: 2rem;
    opacity: 0.7;
}

.mod-filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    overflow-x: auto;
    padding-bottom: 4px;
}

.mod-tab-item {
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 0.86rem;
    font-weight: 700;
    text-decoration: none;
    background: #ffffff;
    color: #475569;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: all 0.15s ease;
}

.mod-tab-item:hover {
    background: #f8fafc;
    color: #0f172a;
}

.mod-tab-item.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
}

.mod-table-box {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.mod-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.mod-table th {
    background: #f8fafc;
    padding: 14px 18px;
    text-align: left;
    font-weight: 800;
    color: #0f172a;
    border-bottom: 1.5px solid #e2e8f0;
}

.mod-table td {
    padding: 16px 18px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.mod-sla-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 100px;
    font-size: 0.76rem;
    font-weight: 800;
}

.mod-sla-badge.safe {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.mod-sla-badge.urgent {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}

.mod-sla-badge.expired {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
    animation: pulse 1.5s infinite;
}

.mod-sla-badge.resolved {
    background: #f1f5f9;
    color: #64748b;
}

.mod-btn {
    padding: 7px 14px;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.15s ease;
}

.mod-btn-remove {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.mod-btn-remove:hover {
    background: #dc2626;
    color: #ffffff;
}

.mod-btn-ban {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
}

.mod-btn-ban:hover {
    background: #ea580c;
    color: #ffffff;
}

.mod-btn-dismiss {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.mod-btn-dismiss:hover {
    background: #e2e8f0;
    color: #0f172a;
}
</style>

<!-- Hero Header -->
<div class="mod-hero">
    <div class="mod-hero-badge">⚡ SLA Cam Kết: Xử lý vi phạm dưới 24h</div>
    <h1>Trung Tâm Kiểm Duyệt & Xử Lý Vi Phạm</h1>
    <p>Giám sát toàn diện nội dung người dùng tạo (UGC), giải quyết báo cáo vi phạm, thực thi gỡ bài viết độc hại và áp dụng chế tài tạm khóa tác giả theo tiêu chuẩn App Store Guideline 1.2.</p>
</div>

<!-- KPI Banner Grid -->
<div class="mod-stats-grid">
    <div class="mod-stat-card info">
        <div class="mod-stat-val">{{ $stats['total'] }}</div>
        <div class="mod-stat-lbl">TỔNG SỐ VÉ BÁO CÁO</div>
        <div class="mod-stat-icon">📋</div>
    </div>
    <div class="mod-stat-card alert">
        <div class="mod-stat-val" style="color: #dc2626;">{{ $stats['pending'] }}</div>
        <div class="mod-stat-lbl">CHỜ XỬ LÝ (SLA &lt; 24H)</div>
        <div class="mod-stat-icon">⏱️</div>
    </div>
    <div class="mod-stat-card warning">
        <div class="mod-stat-val" style="color: #ea580c;">{{ $stats['resolved_removed'] }}</div>
        <div class="mod-stat-lbl">ĐÃ GỠ BỎ NỘI DUNG</div>
        <div class="mod-stat-icon">🗑️</div>
    </div>
    <div class="mod-stat-card success">
        <div class="mod-stat-val" style="color: #059669;">{{ $stats['resolved_banned'] }}</div>
        <div class="mod-stat-lbl">TÀI KHOẢN BỊ KHÓA</div>
        <div class="mod-stat-icon">🚫</div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mod-filter-tabs">
    <a href="/admin/moderation?status=all" class="mod-tab-item {{ $filter === 'all' ? 'active' : '' }}">Tất cả ({{ $stats['total'] }})</a>
    <a href="/admin/moderation?status=pending" class="mod-tab-item {{ $filter === 'pending' ? 'active' : '' }}">⏳ Chờ duyệt ({{ $stats['pending'] }})</a>
    <a href="/admin/moderation?status=resolved_removed" class="mod-tab-item {{ $filter === 'resolved_removed' ? 'active' : '' }}">🗑️ Đã gỡ bài ({{ $stats['resolved_removed'] }})</a>
    <a href="/admin/moderation?status=resolved_banned" class="mod-tab-item {{ $filter === 'resolved_banned' ? 'active' : '' }}">🚫 Đã khóa tác giả ({{ $stats['resolved_banned'] }})</a>
    <a href="/admin/moderation?status=dismissed" class="mod-tab-item {{ $filter === 'dismissed' ? 'active' : '' }}">✅ Đã bác bỏ ({{ $stats['dismissed'] }})</a>
</div>

<!-- Moderation Table Box -->
<div class="mod-table-box">
    @if(count($reports) > 0)
    <div style="overflow-x: auto;">
        <table class="mod-table">
            <thead>
                <tr>
                    <th style="width: 130px;">Hạn SLA 24h</th>
                    <th style="width: 170px;">Tác Giả Bị Báo Cáo</th>
                    <th style="width: 160px;">Lý Do Vi Phạm</th>
                    <th>Nội Dung Bị Báo Cáo</th>
                    <th style="width: 140px;">Người Báo Cáo</th>
                    <th style="width: 120px;">Trạng Thái</th>
                    <th style="width: 220px; text-align: center;">Hành Động Xử Lý</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $r)
                <tr>
                    <td>
                        <span class="mod-sla-badge {{ $r['sla_status'] ?? 'safe' }}">
                            {{ $r['sla_text'] ?? '24h' }}
                        </span>
                        <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">
                            {{ \Carbon\Carbon::parse($r['created_at'])->diffForHumans() }}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: #0f172a;">{{ $r['author_name'] ?? 'Ẩn danh' }}</div>
                        @if(!empty($r['author_id']))
                            <div style="font-size: 0.75rem; color: #64748b;">ID: #{{ $r['author_id'] }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight: 700; color: #dc2626; font-size: 0.82rem; background: #fef2f2; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                            {{ $r['reason'] }}
                        </span>
                        @if(!empty($r['details']))
                            <div style="font-size: 0.75rem; color: #475569; margin-top: 4px; font-style: italic;">
                                "{{ Str::limit($r['details'], 50) }}"
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #1e293b;">{{ $r['target_title'] ?: 'Bài viết Bảng tin' }}</div>
                        <div style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">
                            "{{ Str::limit($r['target_summary'] ?? 'Nội dung bài viết', 80) }}"
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #334155;">{{ $r['reporter_name'] ?? 'Thành viên' }}</div>
                        @if(!empty($r['reporter_id']))
                            <div style="font-size: 0.75rem; color: #94a3b8;">UID: #{{ $r['reporter_id'] }}</div>
                        @endif
                    </td>
                    <td>
                        @if(($r['status'] ?? '') === 'pending')
                            <span style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 100px; font-size: 0.76rem; font-weight: 800;">Chờ xử lý</span>
                        @elseif(($r['status'] ?? '') === 'resolved_removed')
                            <span style="background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 100px; font-size: 0.76rem; font-weight: 800;">Đã gỡ bài</span>
                        @elseif(($r['status'] ?? '') === 'resolved_banned')
                            <span style="background: #ffedd5; color: #c2410c; padding: 4px 10px; border-radius: 100px; font-size: 0.76rem; font-weight: 800;">Khóa ({{ $r['ban_duration'] ?? '24h' }})</span>
                        @elseif(($r['status'] ?? '') === 'dismissed')
                            <span style="background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 100px; font-size: 0.76rem; font-weight: 800;">Bác bỏ</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if(($r['status'] ?? '') === 'pending')
                            <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                <form action="/admin/moderation/resolve/{{ $r['id'] }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn gỡ bỏ nội dung này khỏi hệ thống ngay lập tức?')">
                                    @csrf
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="mod-btn mod-btn-remove" title="Xóa nội dung khỏi hệ thống">
                                        🗑️ Gỡ bài
                                    </button>
                                </form>

                                <button type="button" class="mod-btn mod-btn-ban" onclick="openBanModal('{{ $r['id'] }}', '{{ addslashes($r['author_name'] ?? '') }}', '{{ $r['author_id'] ?? '' }}')" title="Khóa tài khoản tác giả có thời hạn">
                                    🚫 Khóa
                                </button>

                                <form action="/admin/moderation/resolve/{{ $r['id'] }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="dismiss">
                                    <button type="submit" class="mod-btn mod-btn-dismiss" title="Bác bỏ báo cáo nếu nội dung an toàn">
                                        ✓ Bác bỏ
                                    </button>
                                </form>
                            </div>
                        @else
                            <div style="font-size: 0.78rem; color: #64748b; font-weight: 600;">
                                {{ $r['resolution_action'] ?? 'Đã giải quyết' }}
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
        <div style="font-size: 3rem; margin-bottom: 10px;">🎉</div>
        <div style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Không có vé vi phạm nào trong mục này</div>
        <p style="font-size: 0.88rem; color: #64748b; margin-top: 4px;">Toàn bộ nội dung Bảng tin đang tuân thủ tốt tiêu chuẩn cộng đồng.</p>
    </div>
    @endif
</div>

<!-- ============================================================
     BAN DURATION MODAL DIALOG
     ============================================================ -->
<div id="adminBanModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 24px; max-width: 480px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; position: relative;">
        <div style="padding: 20px 24px 14px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.3rem;">🚫</span>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #991b1b;">Khóa Tài Khoản Có Thời Hạn</h3>
            </div>
            <button type="button" onclick="closeBanModal()" style="background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; font-weight: 800; color: #64748b; cursor: pointer;">✕</button>
        </div>

        <form id="adminBanForm" action="" method="POST" style="padding: 20px 24px;">
            @csrf
            <input type="hidden" name="action" value="ban">

            <div style="background: #f8fafc; border-radius: 12px; padding: 12px 16px; margin-bottom: 18px; border: 1px solid #e2e8f0; font-size: 0.88rem;">
                Tác giả bị áp dụng chế tài: <strong id="banModalAuthorName" style="color: #0f172a;">...</strong>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="font-size: 0.88rem; font-weight: 800; color: #334155; display: block; margin-bottom: 8px;">Chọn thời hạn khóa tài khoản:</label>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem;">
                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer;">
                        <input type="radio" name="ban_duration" value="24h" checked style="accent-color: #ea580c;">
                        <span>⏱️ <strong>24 giờ</strong> (Cảnh cáo vi phạm lần đầu)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer;">
                        <input type="radio" name="ban_duration" value="3d" style="accent-color: #ea580c;">
                        <span>📅 <strong>3 ngày</strong> (Vi phạm mức độ nhẹ/spam)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer;">
                        <input type="radio" name="ban_duration" value="7d" style="accent-color: #ea580c;">
                        <span>🗓️ <strong>7 ngày</strong> (Tái phạm nhiều lần)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer;">
                        <input type="radio" name="ban_duration" value="30d" style="accent-color: #ea580c;">
                        <span>🛑 <strong>30 ngày</strong> (Vi phạm nghiêm trọng)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid #fecaca; background: #fff5f5; border-radius: 12px; cursor: pointer;">
                        <input type="radio" name="ban_duration" value="permanent" style="accent-color: #dc2626;">
                        <span style="color: #991b1b;">⛔ <strong>Vĩnh viễn</strong> (Phản động, đồi trụy 18+, lừa đảo)</span>
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 0.88rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Ghi chú lý do xử lý của Admin:</label>
                <textarea name="resolution_note" rows="2" placeholder="Ví dụ: Khóa 7 ngày do cố tình chia sẻ đường link cờ bạc..." style="width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 12px; font-size: 0.88rem; font-family: inherit; resize: vertical; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeBanModal()" style="padding: 10px 20px; border-radius: 12px; background: #f1f5f9; color: #475569; border: none; font-weight: 700; font-size: 0.9rem; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 10px 22px; border-radius: 12px; background: linear-gradient(135deg, #ea580c, #c2410c); color: #ffffff; border: none; font-weight: 800; font-size: 0.9rem; cursor: pointer; box-shadow: 0 4px 14px rgba(234, 88, 12, 0.35);">🚫 Thực thi khóa tài khoản</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBanModal(ticketId, authorName, authorId) {
    const modal = document.getElementById('adminBanModal');
    const form = document.getElementById('adminBanForm');
    const nameEl = document.getElementById('banModalAuthorName');
    
    if (form) {
        form.action = '/admin/moderation/resolve/' + ticketId;
    }
    if (nameEl) {
        nameEl.innerText = authorName + (authorId ? ' (ID: #' + authorId + ')' : '');
    }
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeBanModal() {
    const modal = document.getElementById('adminBanModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
</script>

@endsection
