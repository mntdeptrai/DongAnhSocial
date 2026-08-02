@extends('layouts.app')

@section('title', 'Trang cá nhân - ' . $user->name)

@section('content')
<div x-data="{ 
    showPasswordModal: {{ ($errors->has('password') || $errors->has('otp')) ? 'true' : 'false' }},
    showEditModal: false,
    otpCooldown: 0,
    otpFeedback: '',
    otpFeedbackType: '',
    sendOtp() {
        if (this.otpCooldown > 0) return;
        this.otpFeedback = 'Đang gửi mã OTP...';
        this.otpFeedbackType = 'info';
        fetch('/profile/password/send-otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.otpFeedback = data.message;
                this.otpFeedbackType = 'success';
                this.otpCooldown = 240;
                let iv = setInterval(() => { if (this.otpCooldown <= 0) clearInterval(iv); else this.otpCooldown--; }, 1000);
            } else { this.otpFeedback = data.message || 'Gửi mã OTP thất bại!'; this.otpFeedbackType = 'error'; }
        })
        .catch(() => { this.otpFeedback = 'Lỗi kết nối mạng!'; this.otpFeedbackType = 'error'; });
    },
    get cooldownText() {
        if (this.otpCooldown <= 0) return 'Gửi mã';
        let m = Math.floor(this.otpCooldown / 60), s = this.otpCooldown % 60;
        return `Gửi lại (${m}:${s.toString().padStart(2, '0')})`;
    }
}">

<!-- ===================== SCOPED PROFILE CSS ===================== -->
<style>
/* ---------- Profile-only CSS: no Bootstrap needed ---------- */
.pf-root {
    background: #f0f2f5;
    min-height: 100vh;
    font-family: 'Be Vietnam Pro', 'Segoe UI', system-ui, sans-serif;
    color: #1c1e21;
    padding-bottom: 60px;
}
.pf-header-wrap {
    background: #ffffff;
    border-bottom: 1px solid #dddfe2;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 20px;
}
.pf-inner {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 16px;
}

/* Cover */
.pf-cover {
    position: relative;
    width: 100%;
    height: 320px;
    border-radius: 0 0 12px 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.pf-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pf-cover-btn {
    position: absolute; bottom: 14px; right: 16px;
    background: #ffffff; color: #1c1e21;
    border: none; border-radius: 20px;
    padding: 8px 20px; font-size: 0.85rem; font-weight: 700;
    cursor: pointer; box-shadow: 0 2px 12px rgba(0,0,0,0.12);
    display: inline-flex; align-items: center; gap: 6px;
    transition: all 0.2s ease;
}
.pf-cover-btn:hover { background: #f0f0f0; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }

/* Profile bar */
.pf-bar {
    display: flex; align-items: flex-end; justify-content: space-between;
    gap: 16px; flex-wrap: wrap;
    padding: 0 8px; margin-top: -40px;
}
.pf-bar-left { display: flex; align-items: flex-end; gap: 16px; }
.pf-avatar-wrap { position: relative; flex-shrink: 0; }
.pf-bubble {
    position: absolute; top: -30px; left: 16px;
    background: #fff; color: #65676b; font-size: 0.72rem;
    padding: 3px 10px; border-radius: 12px;
    border: 1px solid #dddfe2; font-weight: 600;
    white-space: nowrap; box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.pf-avatar {
    width: 150px; height: 150px; border-radius: 50%;
    border: 5px solid #fff; object-fit: cover;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    background: #e4e6eb;
}
.pf-avatar-placeholder {
    width: 150px; height: 150px; border-radius: 50%;
    border: 5px solid #fff; background: #e4e6eb;
    display: flex; align-items: center; justify-content: center;
    font-size: 3.5rem; box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}
.pf-name-block { padding-bottom: 8px; }
.pf-name {
    font-size: 1.85rem; font-weight: 800; color: #1c1e21;
    margin: 0 0 2px; line-height: 1.15;
    display: flex; align-items: center; gap: 8px;
}
.pf-verified { color: #1877f2; font-size: 1.1rem; }
.pf-followers {
    font-size: 0.9rem; color: #65676b;
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.pf-followers strong { color: #1c1e21; }
.pf-bio {
    font-size: 0.85rem; color: #65676b; margin-top: 4px;
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}

.pf-bar-right {
    display: flex; gap: 8px; padding-bottom: 12px; flex-wrap: wrap;
}
.pf-btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    background: linear-gradient(135deg, #1877f2 0%, #0d65d9 100%); color: #fff; border: none;
    padding: 9px 22px; border-radius: 9999px;
    font-size: 0.9rem; font-weight: 700; cursor: pointer;
    text-decoration: none; transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(24,119,242,0.25);
}
.pf-btn-primary:hover { background: linear-gradient(135deg, #166fe5 0%, #0b5bca 100%); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(24,119,242,0.35); }
.pf-btn-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    background: #e4e6eb; color: #050505; border: none;
    padding: 9px 22px; border-radius: 9999px;
    font-size: 0.9rem; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: all 0.2s ease;
}
.pf-btn-secondary:hover { background: #d8dadf; color: #050505; transform: translateY(-1px); }

/* Tabs */
.pf-tabs {
    display: flex; align-items: center; justify-content: space-between;
    border-top: 1px solid #dddfe2; padding: 0 8px;
    overflow-x: auto; gap: 4px;
}
.pf-tabs-left { display: flex; gap: 0; }
.pf-tab {
    padding: 14px 18px; font-size: 0.92rem; font-weight: 600;
    color: #65676b; background: none; border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer; transition: all 0.15s; white-space: nowrap;
}
.pf-tab:hover { background: #f0f2f5; border-radius: 8px 8px 0 0; }
.pf-tab.active {
    color: #1877f2; border-bottom-color: #1877f2;
    font-weight: 700;
}
.pf-tab-more {
    background: none; border: none; color: #65676b;
    font-size: 1.3rem; cursor: pointer; padding: 8px;
    border-radius: 50%; transition: background 0.15s;
}
.pf-tab-more:hover { background: #f0f2f5; }

/* Two-column body */
.pf-body {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 16px;
    align-items: start;
}
@media (max-width: 900px) {
    .pf-body { grid-template-columns: 1fr; }
    .pf-bar { flex-direction: column; align-items: flex-start; }
    .pf-cover { height: 200px; }
    .pf-avatar, .pf-avatar-placeholder { width: 120px; height: 120px; }
    .pf-name { font-size: 1.5rem; }
}

/* Cards */
.pf-card {
    background: #ffffff;
    border: 1px solid #dddfe2;
    border-radius: 16px;
    padding: 18px 22px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
    margin-bottom: 16px;
}
.pf-card-title {
    font-size: 1.15rem; font-weight: 800; color: #1c1e21;
    margin: 0 0 4px; line-height: 1.3;
}
.pf-card-head {
    display: flex; justify-content: space-between; align-items: center;
    padding-bottom: 12px; margin-bottom: 14px;
    border-bottom: 1px solid #ebedf0;
}
.pf-card-edit {
    background: none; border: none; font-size: 1rem;
    cursor: pointer; color: #65676b; padding: 4px;
    border-radius: 50%; transition: background 0.15s;
}
.pf-card-edit:hover { background: #f0f2f5; }

/* Info list */
.pf-info-list { list-style: none; padding: 0; margin: 0; }
.pf-info-item {
    display: flex; align-items: center; gap: 12px;
    padding: 8px 0; font-size: 0.92rem; color: #3a3b3c;
    white-space: nowrap; overflow: hidden;
}
.pf-info-item .icon { font-size: 1.2rem; flex-shrink: 0; width: 24px; text-align: center; }
.pf-info-item > span:last-child { overflow: hidden; text-overflow: ellipsis; }
.pf-info-item strong { color: #1c1e21; }

.pf-btn-block {
    display: block; width: 100%; text-align: center;
    background: #e4e6eb; color: #050505; border: none;
    padding: 10px 0; border-radius: 9999px;
    font-size: 0.9rem; font-weight: 700; cursor: pointer;
    margin-top: 14px; transition: all 0.2s ease;
}
.pf-btn-block:hover { background: #d8dadf; transform: translateY(-1px); }

/* School card */
.pf-school-row {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 14px;
}
.pf-school-thumb {
    width: 52px; height: 52px; border-radius: 10px;
    object-fit: cover; border: 1px solid #dddfe2;
    flex-shrink: 0;
}
.pf-school-name { font-size: 0.96rem; font-weight: 700; color: #1c1e21; margin: 0 0 2px; }
.pf-school-addr { font-size: 0.82rem; color: #65676b; margin: 0; }

/* Create-post card */
.pf-create-row {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 12px;
}
.pf-create-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    object-fit: cover; border: 2px solid #e4e6eb; flex-shrink: 0;
}
.pf-create-input {
    flex: 1; background: #f0f2f5; border: none;
    padding: 11px 20px; border-radius: 9999px;
    font-size: 0.95rem; color: #65676b;
    cursor: pointer; transition: all 0.2s ease;
    text-decoration: none; display: block;
}
.pf-create-input:hover { background: #e4e6eb; color: #3a3b3c; }
.pf-create-actions {
    display: flex; justify-content: space-around;
    border-top: 1px solid #ebedf0; padding-top: 10px;
}
.pf-create-btn {
    background: none; border: none;
    font-size: 0.85rem; font-weight: 700;
    cursor: pointer; padding: 7px 14px;
    border-radius: 9999px; transition: all 0.2s ease;
    display: inline-flex; align-items: center; gap: 6px;
}
.pf-create-btn:hover { background: #f0f2f5; }
.pf-create-btn.red { color: #e53e3e; }
.pf-create-btn.green { color: #38a169; }
.pf-create-btn.orange { color: #dd6b20; }

/* Empty state */
.pf-empty {
    text-align: center; padding: 48px 24px;
    background: #fff; border: 1px solid #dddfe2;
    border-radius: 12px;
}
.pf-empty-icon { font-size: 3.5rem; margin-bottom: 12px; }
.pf-empty h5 { font-size: 1.1rem; font-weight: 700; color: #1c1e21; margin: 0 0 6px; }
.pf-empty p { font-size: 0.88rem; color: #65676b; margin: 0 0 16px; }

/* Modal */
.pf-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(255,255,255,0.65);
    backdrop-filter: blur(6px);
    z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.pf-modal-box {
    background: #fff; border: 1px solid #dddfe2;
    border-radius: 12px; padding: 28px;
    width: 100%; max-width: 440px;
    box-shadow: 0 12px 48px rgba(0,0,0,0.15);
    position: relative;
}
.pf-modal-close {
    position: absolute; top: 14px; right: 14px;
    width: 32px; height: 32px; border-radius: 50%;
    background: #e4e6eb; border: none;
    font-size: 1rem; color: #3a3b3c;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.pf-modal-close:hover { background: #d8dadf; }
.pf-modal-head {
    text-align: center; margin-bottom: 22px;
}
.pf-modal-head .emoji { font-size: 2.2rem; display: block; margin-bottom: 6px; }
.pf-modal-head h3 {
    font-size: 1.3rem; font-weight: 800; color: #1c1e21; margin: 0;
}
.pf-modal-head p { font-size: 0.85rem; color: #65676b; margin-top: 4px; }
.pf-form-group { margin-bottom: 14px; }
.pf-form-label {
    display: block; font-size: 0.82rem; font-weight: 700;
    color: #1c1e21; margin-bottom: 6px;
}
.pf-form-input {
    width: 100%; padding: 10px 16px;
    border: 1px solid #dddfe2; border-radius: 12px;
    font-size: 0.92rem; color: #1c1e21;
    background: #f5f6f7; transition: all 0.2s ease;
    outline: none; box-sizing: border-box;
}
.pf-form-input:focus { border-color: #1877f2; background: #fff; }
.pf-form-row { display: flex; gap: 8px; }
.pf-form-row .pf-form-input { flex: 1; }
.pf-otp-btn {
    padding: 10px 18px; background: #e4e6eb;
    border: none; border-radius: 9999px;
    font-size: 0.82rem; font-weight: 700;
    cursor: pointer; white-space: nowrap; color: #1c1e21;
    transition: all 0.2s ease;
}
.pf-otp-btn:hover { background: #d8dadf; }
.pf-otp-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pf-modal-actions {
    display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;
}
</style>
<!-- ===================== END SCOPED CSS ===================== -->


<div class="pf-root">
    <!-- ====== HEADER SECTION ====== -->
    <div class="pf-header-wrap">
        <div class="pf-inner">

            <!-- 1. Cover Photo -->
            <div class="pf-cover">
                @if(optional($school)->image_path)
                    <img src="{{ optional($school)->image_path }}" alt="Ảnh bìa">
                @else
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.15);font-size:6rem;">🏫</div>
                @endif
                <button type="button" class="pf-cover-btn">📷 Thêm ảnh bìa</button>
            </div>

            <!-- 2. Profile Info Bar -->
            <div class="pf-bar">
                <div class="pf-bar-left">
                    <!-- Avatar -->
                    <div class="pf-avatar-wrap">
                        <div class="pf-bubble">Chia sẻ suy nghĩ...</div>
                        @if($user->avatar && str_starts_with($user->avatar, 'avatars/'))
                            <img src="{{ rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $user->avatar }}" class="pf-avatar" alt="{{ $user->name }}">
                        @elseif(optional($school)->image_path)
                            <img src="{{ optional($school)->image_path }}" class="pf-avatar" alt="{{ $user->name }}">
                        @else
                            <div class="pf-avatar-placeholder">👤</div>
                        @endif
                    </div>
                    <!-- Name block -->
                    <div class="pf-name-block">
                        <h2 class="pf-name">
                            {{ $user->name }}
                            <span class="pf-verified" title="Xác minh Hiệu trưởng">✅</span>
                        </h2>
                        <div class="pf-followers">
                            <strong>863 người theo dõi</strong> <span>•</span> <span>113 đang theo dõi</span>
                        </div>
                        <div class="pf-bio">
                            <span>💼 {{ $user->isPrincipal() ? 'Hiệu trưởng nhà trường' : 'Thành viên' }}</span>
                            <span>📍 Đông Anh, Hà Nội</span>
                            @if($school)
                                <span>🏫 {{ $school->standardized_name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="pf-bar-right">
                    @if($school)
                        <a href="{{ route('principal.schools.dashboard', $school->id) }}" class="pf-btn-primary">📊 Bảng điều khiển</a>
                    @endif
                    <button type="button" @click="showPasswordModal = true" class="pf-btn-secondary">🔒 Đổi mật khẩu</button>
                    <button type="button" class="pf-btn-secondary" style="padding:9px 14px;border-radius:50%;width:40px;height:40px;justify-content:center;">•••</button>
                </div>
            </div>

            <!-- 3. Tab Bar -->
            <div class="pf-tabs">
                <div class="pf-tabs-left">
                    <button class="pf-tab active">Tất cả</button>
                    <button class="pf-tab">Giới thiệu</button>
                    <button class="pf-tab">Reels</button>
                    <button class="pf-tab">Ảnh</button>
                    <button class="pf-tab">Bạn bè</button>
                    <button class="pf-tab">Xem thêm ▾</button>
                </div>
                <button class="pf-tab-more">•••</button>
            </div>
        </div>
    </div>

    <!-- ====== BODY TWO-COLUMN ====== -->
    <div class="pf-inner">
        <div class="pf-body">

            <!-- LEFT COLUMN -->
            <div>
                <!-- Info Card -->
                <div class="pf-card">
                    <div class="pf-card-head">
                        <h4 class="pf-card-title">Thông tin cá nhân</h4>
                        <button type="button" class="pf-card-edit" @click="showEditModal = true">✏️</button>
                    </div>
                    <ul class="pf-info-list">
                        <li class="pf-info-item"><span class="icon">📍</span><span>Sống ở <strong>Xã Đông Anh, Hà Nội</strong></span></li>
                        <li class="pf-info-item"><span class="icon">🏠</span><span>Từ <strong>Hà Nội, Việt Nam</strong></span></li>
                        @if($school)
                            <li class="pf-info-item"><span class="icon">🏫</span><span>Hiệu trưởng tại <strong>{{ $school->standardized_name }}</strong></span></li>
                        @endif
                        @if($user->email)
                            <li class="pf-info-item"><span class="icon">📧</span><span>{{ $user->email }}</span></li>
                        @endif
                        @if($user->phone)
                            <li class="pf-info-item"><span class="icon">📞</span><span>{{ $user->phone }}</span></li>
                        @endif
                    </ul>
                    <button type="button" class="pf-btn-block" @click="showEditModal = true">Chỉnh sửa chi tiết</button>
                </div>

                @if($school)
                    <!-- School Card -->
                    <div class="pf-card">
                        <h4 class="pf-card-title" style="margin-bottom:14px;">Trường học điều hành</h4>
                        <div class="pf-school-row">
                            <img src="{{ $school->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80' }}" class="pf-school-thumb" alt="{{ $school->standardized_name }}">
                            <div>
                                <p class="pf-school-name">{{ $school->standardized_name }}</p>
                                <p class="pf-school-addr">📍 {{ $school->address ?: 'Đông Anh, Hà Nội' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('principal.schools.dashboard', $school->id) }}" class="pf-btn-primary" style="width:100%;justify-content:center;">
                            📊 Vào Kênh Điều Hành Trường ➔
                        </a>
                    </div>
                @endif
            </div>

            <!-- RIGHT COLUMN -->
            <div>
                <!-- Create Post Trigger -->
                <div class="pf-card">
                    <div class="pf-create-row">
                        <img src="{{ $user->avatar && str_starts_with($user->avatar, 'avatars/') ? rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $user->avatar : (optional($school)->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80') }}" class="pf-create-avatar" alt="">
                        @if($school)
                            <a href="{{ route('principal.schools.dashboard', $school->id) }}" class="pf-create-input">
                                {{ $user->name }} ơi, bạn đang nghĩ gì?
                            </a>
                        @else
                            <div class="pf-create-input">Bạn đang nghĩ gì?</div>
                        @endif
                    </div>
                    <div class="pf-create-actions">
                        <button type="button" class="pf-create-btn red">📹 Video trực tiếp</button>
                        <button type="button" class="pf-create-btn green">🖼️ Ảnh/video</button>
                        <button type="button" class="pf-create-btn orange">🎬 Thước phim</button>
                    </div>
                </div>

                <!-- Posts Feed -->
                @if($posts->isEmpty())
                    <div class="pf-empty">
                        <div class="pf-empty-icon">📰</div>
                        <h5>Chưa có bài viết nào</h5>
                        <p>Đăng thông báo & hình ảnh hoạt động giáo dục nhà trường lên bảng tin!</p>
                        @if($school)
                            <a href="{{ route('principal.schools.dashboard', $school->id) }}" class="pf-btn-primary" style="border-radius:9999px;padding:10px 24px;">
                                + Đăng bài ngay
                            </a>
                        @endif
                    </div>
                @else
                    @foreach($posts as $p)
                        @php
                            $imgs = $p->all_images;
                            $imgCount = count($imgs);
                        @endphp
                        <article class="fb-post-card" style="margin-bottom:16px;">
                            <!-- Post Header -->
                            <div class="fb-post-header">
                                <div class="fb-post-author-box">
                                    <img src="{{ optional($school)->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80' }}" class="fb-user-avatar" alt="{{ optional($school)->standardized_name }}">
                                    <div>
                                        <h4 class="fb-post-author-name">{{ optional($school)->standardized_name }}</h4>
                                        <div class="fb-post-subtext">
                                            <span>{{ $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong' }}</span>
                                            <span>•</span>
                                            <span>🌐 Công khai</span>
                                        </div>
                                    </div>
                                </div>
                                @if($school)
                                    <a href="{{ route('principal.schools.dashboard', $school->id) }}" style="background:#e4e6eb;color:#050505;border:none;padding:6px 14px;border-radius:8px;font-size:0.78rem;font-weight:700;text-decoration:none;white-space:nowrap;">
                                        ✏️ Quản lý
                                    </a>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="fb-post-text">
                                <strong style="display:block;margin-bottom:4px;font-size:1.05rem;">🌸 {{ $p->name }}</strong>
                                {{ $p->description }}
                            </div>

                            <!-- Photo Grid -->
                            @if($imgCount === 1)
                                <div class="fb-photo-grid fb-grid-1"><img src="{{ $imgs[0] }}" alt="{{ $p->name }}"></div>
                            @elseif($imgCount === 2)
                                <div class="fb-photo-grid fb-grid-2">
                                    <img src="{{ $imgs[0] }}" alt="{{ $p->name }}">
                                    <img src="{{ $imgs[1] }}" alt="{{ $p->name }}">
                                </div>
                            @elseif($imgCount === 3)
                                <div class="fb-photo-grid fb-grid-3">
                                    <img src="{{ $imgs[0] }}" alt="{{ $p->name }}">
                                    <div class="fb-grid-3-col-right">
                                        <img src="{{ $imgs[1] }}" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[2] }}" alt="{{ $p->name }}">
                                    </div>
                                </div>
                            @elseif($imgCount >= 4)
                                <div class="fb-photo-grid fb-grid-4">
                                    <img src="{{ $imgs[0] }}" alt="{{ $p->name }}">
                                    <div class="fb-grid-4-col-right">
                                        <img src="{{ $imgs[1] }}" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[2] }}" alt="{{ $p->name }}">
                                        <div class="fb-photo-thumb-box">
                                            <img src="{{ $imgs[3] }}" alt="{{ $p->name }}">
                                            @if($imgCount > 4)
                                                <div class="fb-photo-more-overlay">+{{ $imgCount - 3 }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Stats -->
                            <div class="fb-post-stats">
                                <div>👍 {{ $p->likes_count ?: rand(18, 96) }} lượt thích</div>
                                <div>💬 {{ rand(4, 25) }} bình luận • {{ $p->shares_count ?: rand(2, 14) }} chia sẻ</div>
                            </div>

                            <!-- Actions -->
                            <div class="fb-post-actions">
                                <button class="fb-action-btn" onclick="toggleFbLike(this)">👍 Thích</button>
                                <button class="fb-action-btn">💬 Bình luận</button>
                                <button class="fb-action-btn">🔄 Chia sẻ</button>
                            </div>
                        </article>
                    @endforeach
                @endif
            </div>

        </div>
    </div>
</div>

<!-- ====== EDIT PROFILE MODAL ====== -->
<div x-show="showEditModal" x-transition.opacity style="display:none;" class="pf-modal-overlay">
    <div @click.outside="showEditModal = false" x-show="showEditModal" x-transition.scale class="pf-modal-box">
        <button type="button" @click="showEditModal = false" class="pf-modal-close">✕</button>
        <div class="pf-modal-head">
            <span class="emoji">📝</span>
            <h3>Chỉnh sửa thông tin cá nhân</h3>
            <p>Cập nhật họ tên, email và số điện thoại của bạn.</p>
        </div>
        <form action="/profile" method="POST">
            @csrf
            @method('PUT')
            <div class="pf-form-group">
                <label class="pf-form-label">Họ và tên</label>
                <input type="text" name="name" required class="pf-form-input" value="{{ $user->name }}" placeholder="Nguyễn Văn A">
            </div>
            <div class="pf-form-group">
                <label class="pf-form-label">Email</label>
                <input type="email" name="email" required class="pf-form-input" value="{{ $user->email }}" placeholder="email@example.com">
            </div>
            <div class="pf-form-group">
                <label class="pf-form-label">Số điện thoại</label>
                <input type="text" name="phone" required class="pf-form-input" value="{{ $user->phone }}" placeholder="0912 345 678">
            </div>
            <div class="pf-modal-actions">
                <button type="button" @click="showEditModal = false" class="pf-btn-secondary" style="border-radius:9999px;padding:8px 22px;">Hủy</button>
                <button type="submit" class="pf-btn-primary" style="border-radius:9999px;padding:8px 22px;">💾 Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== PASSWORD MODAL ====== -->
<div x-show="showPasswordModal" x-transition.opacity style="display:none;" class="pf-modal-overlay">
    <div @click.outside="showPasswordModal = false" x-show="showPasswordModal" x-transition.scale class="pf-modal-box">
        <button type="button" @click="showPasswordModal = false" class="pf-modal-close">✕</button>
        <div class="pf-modal-head">
            <span class="emoji">🔑</span>
            <h3>Thay đổi mật khẩu</h3>
            <p>Nhập thông tin bên dưới để đổi mật khẩu bảo mật.</p>
        </div>
        <form action="/profile/password" method="POST">
            @csrf
            @method('PUT')
            <div class="pf-form-group">
                <label class="pf-form-label">Mã xác thực OTP</label>
                <div class="pf-form-row">
                    <input type="text" name="otp" required class="pf-form-input" placeholder="Nhập mã 6 số" maxlength="6">
                    <button type="button" @click="sendOtp()" :disabled="otpCooldown > 0" class="pf-otp-btn" x-text="cooldownText"></button>
                </div>
                <div x-show="otpFeedback" style="margin-top:6px;font-size:0.8rem;font-weight:600;">
                    <span x-text="otpFeedback" :style="otpFeedbackType === 'success' ? 'color:#38a169' : (otpFeedbackType === 'error' ? 'color:#e53e3e' : 'color:#65676b')"></span>
                </div>
            </div>
            <div class="pf-form-group">
                <label class="pf-form-label">Mật khẩu mới</label>
                <input type="password" name="password" required class="pf-form-input" placeholder="Tối thiểu 6 ký tự">
            </div>
            <div class="pf-form-group">
                <label class="pf-form-label">Xác nhận mật khẩu mới</label>
                <input type="password" name="password_confirmation" required class="pf-form-input" placeholder="••••••••">
            </div>
            <div class="pf-modal-actions">
                <button type="button" @click="showPasswordModal = false" class="pf-btn-secondary" style="border-radius:9999px;padding:8px 22px;">Hủy</button>
                <button type="submit" class="pf-btn-primary" style="border-radius:9999px;padding:8px 22px;">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

</div>
@endsection
