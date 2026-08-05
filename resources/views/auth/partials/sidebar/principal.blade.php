<!-- SIDEBAR SUB-VIEW: PRINCIPAL / SCHOOL -->
<div class="pro-card">
    <div class="pro-card-title">
        <span>Thông tin địa điểm</span>
    </div>
    <ul class="pro-info-list">
        <li class="pro-info-row">
            <span class="pro-info-icon">🏫</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Loại hình</span>
                <span class="pro-info-val">{{ optional(optional($school)->category)->name ?: 'Trường học / Cơ sở' }}</span>
            </div>
        </li>

        <li class="pro-info-row">
            <span class="pro-info-icon">📍</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Địa chỉ</span>
                <span class="pro-info-val">{{ optional($school)->address ?: 'Xã Đông Anh, Hà Nội' }}</span>
            </div>
        </li>
        @if(optional($school)->phone || $user->phone)
        <li class="pro-info-row">
            <span class="pro-info-icon">📞</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Điện thoại</span>
                <span class="pro-info-val">{{ optional($school)->phone ?: ($user->phone ?: 'Chưa cập nhật') }}</span>
            </div>
        </li>
        @endif
        @if(optional($school)->website)
        <li class="pro-info-row">
            <span class="pro-info-icon">🌐</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Website</span>
                <span class="pro-info-val" style="color: #2563eb;">{{ optional($school)->website }}</span>
            </div>
        </li>
        @endif
        @if(optional($school)->opening_hours)
        <li class="pro-info-row">
            <span class="pro-info-icon">🕒</span>
            <div class="pro-info-text">
                <span class="pro-info-lbl">Giờ mở cửa</span>
                <span class="pro-info-val">{{ optional($school)->opening_hours }}</span>
            </div>
        </li>
        @endif
    </ul>

    @if($isOwner)
        <button type="button" @click="showEditModal = true" class="pro-btn-orange" style="width: 100%; border: none; cursor: pointer;">
            ✏️ Cập nhật thông tin
        </button>
    @endif
</div>

<!-- Card Đánh giá Score Breakdown -->
<div class="pro-card">
    <div class="pro-card-title">
        <span>Đánh giá</span>
    </div>
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 16px;">
        <div>
            <div class="pro-rating-big">{{ number_format($avgScore, 1) }}</div>
            <div class="pro-stars">★★★★★</div>
            <div style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">{{ $totalRev }} đánh giá</div>
        </div>
    </div>
</div>
