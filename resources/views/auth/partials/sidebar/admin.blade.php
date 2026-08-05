<!-- SIDEBAR SUB-VIEW: ADMIN -->
<div class="pro-card">
    <div class="pro-card-title">
        <span>Thông tin Quản trị viên</span>
    </div>
    <ul class="pro-info-list">
        <li class="pro-info-row">
            <span class="pro-info-icon">✉️</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Email</span>
                <span class="pro-info-val">{{ $user->email }}</span>
            </div>
        </li>
        <li class="pro-info-row">
            <span class="pro-info-icon">📞</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Điện thoại</span>
                <span class="pro-info-val">{{ $user->phone ?: 'Chưa cập nhật' }}</span>
            </div>
        </li>
        <li class="pro-info-row">
            <span class="pro-info-icon">📅</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Ngày gia nhập</span>
                <span class="pro-info-val">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Mới tham gia' }}</span>
            </div>
        </li>
    </ul>

    @if($isOwner)
        <a href="{{ route('admin.dashboard') }}" class="pro-btn-primary" style="width: 100%; text-decoration: none; text-align: center; justify-content: center; display: inline-flex; border-radius: 12px; margin-top: 10px;">
            ⚙️ Vào Trang Quản Trị Admin
        </a>
    @endif
</div>
