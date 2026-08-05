<!-- SIDEBAR SUB-VIEW: MANAGER -->
<div class="pro-card">
    <div class="pro-card-title">
        <span>Thông tin Ban quản lý</span>
    </div>
    <ul class="pro-info-list">

        @if($user->email)
        <li class="pro-info-row">
            <span class="pro-info-icon">✉️</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Email</span>
                <span class="pro-info-val">{{ $user->email }}</span>
            </div>
        </li>
        @endif
        @if($user->phone)
        <li class="pro-info-row">
            <span class="pro-info-icon">📞</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Điện thoại</span>
                <span class="pro-info-val">{{ $user->phone }}</span>
            </div>
        </li>
        @endif
        <li class="pro-info-row">
            <span class="pro-info-icon">📅</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Ngày gia nhập</span>
                <span class="pro-info-val">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Mới tham gia' }}</span>
            </div>
        </li>
    </ul>

    @if($isOwner)
        <button type="button" @click="showEditModal = true" class="pro-btn-primary" style="width: 100%; border: none; cursor: pointer; border-radius: 12px; margin-top: 10px;">
            ✏️ Chỉnh sửa thông tin
        </button>
    @endif
</div>
