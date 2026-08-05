<!-- SIDEBAR SUB-VIEW: SELLER -->
<div class="pro-card">
    <div class="pro-card-title">
        <span>Thông tin Gian hàng</span>
    </div>
    <ul class="pro-info-list">
        <li class="pro-info-row">
            <span class="pro-info-icon">🛒</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Gian hàng / Cơ sở</span>
                <span class="pro-info-val" style="color: #0284c7; font-weight: 700;">{{ $stall ? $stall->stall_name : 'Cơ sở kinh doanh' }}</span>
            </div>
        </li>

        <li class="pro-info-row">
            <span class="pro-info-icon">👤</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Chủ gian hàng</span>
                <span class="pro-info-val">{{ $user->name }}</span>
            </div>
        </li>
        <li class="pro-info-row">
            <span class="pro-info-icon">📞</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Hotline kinh doanh</span>
                <span class="pro-info-val">{{ $user->phone ?: 'Chưa cập nhật' }}</span>
            </div>
        </li>
        @if($user->email)
        <li class="pro-info-row">
            <span class="pro-info-icon">✉️</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Email</span>
                <span class="pro-info-val">{{ $user->email }}</span>
            </div>
        </li>
        @endif
        <li class="pro-info-row">
            <span class="pro-info-icon">📅</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Ngày mở gian hàng</span>
                <span class="pro-info-val">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Mới tham gia' }}</span>
            </div>
        </li>
    </ul>

    @if($isOwner)
        <button type="button" @click="showEditModal = true" class="pro-btn-primary" style="width: 100%; border: none; cursor: pointer; border-radius: 12px; margin-top: 10px;">
            ✏️ Chỉnh sửa gian hàng
        </button>
    @endif
</div>
