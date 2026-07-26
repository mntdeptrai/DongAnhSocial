@extends('layouts.app')

@section('title', 'Trang cá nhân - Bản đồ số Khám phá Đông Anh')

@section('content')
<div x-data="{ 
    showPasswordModal: {{ ($errors->has('password') || $errors->has('otp')) ? 'true' : 'false' }},
    otpCooldown: 0,
    otpFeedback: '',
    otpFeedbackType: '',
    sendOtp() {
        if (this.otpCooldown > 0) return;
        
        this.otpFeedback = 'Đang gửi mã OTP...';
        this.otpFeedbackType = 'info';
        
        fetch('/profile/password/send-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.otpFeedback = data.message;
                this.otpFeedbackType = 'success';
                this.otpCooldown = 240;
                let interval = setInterval(() => {
                    if (this.otpCooldown <= 0) {
                        clearInterval(interval);
                    } else {
                        this.otpCooldown--;
                    }
                }, 1000);
            } else {
                this.otpFeedback = data.message || 'Gửi mã OTP thất bại!';
                this.otpFeedbackType = 'error';
            }
        })
        .catch(error => {
            this.otpFeedback = 'Lỗi kết nối mạng!';
            this.otpFeedbackType = 'error';
        });
    },
    get cooldownText() {
        if (this.otpCooldown <= 0) return 'Gửi mã';
        let mins = Math.floor(this.otpCooldown / 60);
        let secs = this.otpCooldown % 60;
        return `Gửi lại (${mins}:${secs.toString().padStart(2, '0')})`;
    }
}" style="position: relative; overflow: hidden; background: var(--bg-base); min-height: calc(100vh - var(--header-height) - 160px); width: 100%;">
    <!-- Glowing atmosphere orbs -->
    <div class="cinematic-glow-orb-1" style="top: 15%; left: 5%; width: 450px; height: 450px;"></div>
    <div class="cinematic-glow-orb-2" style="bottom: 20%; right: 5%; width: 500px; height: 500px;"></div>
    
    <div class="container" style="padding: 40px 0; position: relative; z-index: 2; width: 100%;">
        <!-- Header Section -->
        <div style="margin-bottom: 30px; border-bottom: 1px solid var(--border-glow); padding-bottom: 20px;">
            <h1 style="font-size: 2.2rem; font-family: var(--font-heading); font-weight: 800; background: var(--primary-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0;">
                Hồ sơ cá nhân
            </h1>
            <p style="color: var(--text-muted); margin-top: 6px; font-size: 0.95rem;">
                Quản lý thông tin tài khoản cá nhân và các lộ trình food tour do chính bạn thiết kế.
            </p>
        </div>

        <!-- Success & Error Alerts -->
        @if (session('success'))
            <div class="glass-panel" style="background: rgba(16, 185, 129, 0.08); border-color: #10b981; padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; color: #065f46; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="glass-panel" style="background: rgba(239, 68, 68, 0.08); border-color: #ef4444; padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; color: #991b1b; font-size: 0.9rem; font-weight: 600;">
                <ul style="list-style: none; margin: 0; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li style="display: flex; align-items: center; gap: 8px;"><span>⚠️</span> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Profile Dashboard Grid -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 30px; align-items: start;" class="profile-grid-desktop">
            
            <!-- Avatar Upload Card -->
            <div class="glass-panel" style="background: var(--bg-card); padding: 30px; border: 1px solid var(--border-glow); border-radius: 16px; box-shadow: var(--shadow-overlay); margin-bottom: 0;" id="avatarCard">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-glow); padding-bottom: 14px;">
                    <span style="font-size: 1.5rem;">🖼️</span>
                    <h3 style="font-size: 1.25rem; font-family: var(--font-heading); font-weight: 700; margin: 0; color: var(--text-main);">
                        Ảnh đại diện
                    </h3>
                </div>

                <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                    <!-- Current Avatar Preview -->
                    <div style="position: relative; flex-shrink: 0;">
                        <div id="avatarPreviewWrapper" style="width: 100px; height: 100px; border-radius: 50%; background: rgba(var(--primary-rgb),0.08); border: 3px solid var(--border-glow); display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; transition: border-color 0.2s;">
                            @if($user->avatar && str_starts_with($user->avatar, 'avatars/'))
                                <img id="avatarPreviewImg" src="{{ rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $user->avatar }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <span id="avatarPreviewEmoji" style="font-size: 3rem; line-height: 1;">{{ $user->avatar ?? '👤' }}</span>
                                <img id="avatarPreviewImg" src="" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            @endif
                        </div>
                        <!-- Edit overlay -->
                        <label for="avatarFileInput" style="position: absolute; bottom: 2px; right: 2px; width: 28px; height: 28px; background: var(--primary-grad); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(var(--primary-rgb),0.4); border: 2px solid white; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <span style="font-size: 0.7rem; color: white; font-weight: bold;">✏️</span>
                        </label>
                    </div>

                    <!-- Upload Controls -->
                    <div style="flex: 1; min-width: 200px;">
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0 0 12px 0; line-height: 1.5;">
                            Chọn ảnh đại diện mới (JPG, PNG, WebP — tối đa 3MB)
                        </p>
                        <label for="avatarFileInput" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: 1px solid var(--border-glow);">
                            <span>📂</span> Chọn ảnh
                        </label>
                        <input type="file" id="avatarFileInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" style="display: none;">
                        <button id="avatarUploadBtn" onclick="uploadAvatar()" style="display: none; margin-left: 8px; padding: 8px 18px; background: var(--primary-grad); border: none; color: white; border-radius: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                            ✅ Lưu ảnh
                        </button>
                        <div id="avatarFeedback" style="margin-top: 10px; font-size: 0.82rem; font-weight: 600; display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- Orders Shortcut Card -->
            <div class="glass-panel" style="background: var(--bg-card); padding: 24px; border: 1.5px solid rgba(234, 88, 12, 0.25); border-radius: 16px; box-shadow: var(--shadow-overlay); margin-bottom: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.8rem;">📦</span>
                        <div>
                            <h3 style="font-size: 1.15rem; font-family: var(--font-heading); font-weight: 800; margin: 0; color: #ea580c;">
                                Lịch sử đơn hàng của tôi
                            </h3>
                            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0 0;">
                                Theo dõi trạng thái giao hàng, xác nhận nhận hàng, hủy đơn hoặc yêu cầu hoàn hàng
                            </p>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 14px;">
                    <a href="/orders" class="btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 10px; font-size: 0.88rem; font-weight: 700; text-decoration: none; background: linear-gradient(135deg, #ea580c 0%, #f97316 100%); color: white; border: none; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25);">
                        <span>🛒</span> Xem tất cả đơn hàng ➔
                    </a>
                </div>
            </div>

            <!-- Left Side: Profile CRUD Form -->
            <div class="glass-panel" style="background: var(--bg-card); padding: 30px; border: 1px solid var(--border-glow); border-radius: 16px; box-shadow: var(--shadow-overlay);">

<script>
// Preview ảnh trước khi upload
document.getElementById('avatarFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 3 * 1024 * 1024) {
        showAvatarFeedback('❌ Ảnh quá lớn! Vui lòng chọn ảnh dưới 3MB.', 'error');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(ev) {
        const img = document.getElementById('avatarPreviewImg');
        const emoji = document.getElementById('avatarPreviewEmoji');
        img.src = ev.target.result;
        img.style.display = 'block';
        if (emoji) emoji.style.display = 'none';
        document.getElementById('avatarUploadBtn').style.display = 'inline-flex';
        document.getElementById('avatarPreviewWrapper').style.borderColor = 'var(--primary)';
    };
    reader.readAsDataURL(file);
});

function uploadAvatar() {
    const fileInput = document.getElementById('avatarFileInput');
    const file = fileInput.files[0];
    if (!file) return;

    const btn = document.getElementById('avatarUploadBtn');
    btn.textContent = '⏳ Đang tải...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('/profile/avatar', {
        method: 'POST',
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAvatarFeedback('✅ ' + data.message, 'success');
            btn.style.display = 'none';
            document.getElementById('avatarPreviewWrapper').style.borderColor = '#10b981';
            fileInput.value = '';
        } else {
            showAvatarFeedback('❌ ' + (data.message || 'Lỗi tải ảnh!'), 'error');
            btn.textContent = '✅ Lưu ảnh';
            btn.disabled = false;
        }
    })
    .catch(() => {
        showAvatarFeedback('❌ Lỗi kết nối mạng!', 'error');
        btn.textContent = '✅ Lưu ảnh';
        btn.disabled = false;
    });
}

function showAvatarFeedback(msg, type) {
    const el = document.getElementById('avatarFeedback');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.color = type === 'success' ? '#10b981' : '#ef4444';
}
</script>

                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-glow); padding-bottom: 14px;">
                    <span style="font-size: 1.5rem;">⚙️</span>
                    <h3 style="font-size: 1.25rem; font-family: var(--font-heading); font-weight: 700; margin: 0; color: var(--text-main);">
                        Thông tin tài khoản
                    </h3>
                </div>

                <form action="/profile" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="review-form-group" style="margin-bottom: 16px;">
                        <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 6px;">Họ và tên</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required style="padding: 10px 14px; width: 100%; border-radius: 8px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.7);" placeholder="Nguyễn Văn A">
                    </div>

                    <div class="review-form-group" style="margin-bottom: 16px;">
                        <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 6px;">Địa chỉ Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required style="padding: 10px 14px; width: 100%; border-radius: 8px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.7);" placeholder="email@domain.com">
                    </div>

                    <div class="review-form-group" style="margin-bottom: 16px;">
                        <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 6px;">Số điện thoại</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" required style="padding: 10px 14px; width: 100%; border-radius: 8px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.7);" placeholder="09xxxxxx">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <button type="button" @click="showPasswordModal = true" class="btn-secondary" style="width: 100%; justify-content: center; padding: 11px 0; border-radius: 8px; font-weight: 600; border: 1px solid var(--primary); color: var(--primary); display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white'" onmouseout="this.style.background='transparent'; this.style.color='var(--primary)'">
                            <span>🔑</span> Thay đổi mật khẩu
                        </button>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 12px 0; border-radius: 8px; font-weight: 700;">
                        Lưu thay đổi
                    </button>
                </form>
            </div>

            <!-- Right Side: Custom Food Tours Management List -->
            <div class="glass-panel" style="background: var(--bg-card); padding: 30px; border: 1px solid var(--border-glow); border-radius: 16px; box-shadow: var(--shadow-overlay);">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-glow); padding-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 1.5rem;">🗺️</span>
                        <h3 style="font-size: 1.25rem; font-family: var(--font-heading); font-weight: 700; margin: 0; color: var(--text-main);">
                            Lộ trình của bạn
                        </h3>
                    </div>
                    <a href="/food-tours/create" class="btn-primary" style="text-decoration: none; padding: 6px 14px; font-size: 0.82rem; border-radius: 20px; display: flex; align-items: center; gap: 6px;">
                        <span>➕</span> Tạo lộ trình mới
                    </a>
                </div>

                @if($tours->isEmpty())
                    <!-- Blank state container -->
                    <div style="text-align: center; padding: 45px 20px; border: 2px dashed var(--border-glow); border-radius: 12px; background: rgba(255,255,255,0.25);">
                        <span style="font-size: 3rem; display: block; margin-bottom: 12px;">🧭</span>
                        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0 0 6px;">Bạn chưa tự xây dựng lộ trình nào cả</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0 0 20px; max-width: 320px; margin-left: auto; margin-right: auto; line-height: 1.4;">
                            Tự tay kết nối các địa điểm ăn uống yêu thích để tạo nên bản đồ hành trình ẩm thực của riêng mình.
                        </p>
                        <a href="/food-tours/create" class="btn-primary" style="text-decoration: none; display: inline-flex; padding: 10px 20px; font-size: 0.88rem; font-weight: 600; border-radius: 24px;">
                            Bắt đầu tạo ngay
                        </a>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @foreach($tours as $tour)
                            <div class="glass-panel" style="background: rgba(255,255,255,0.6); border: 1px solid var(--border-glow); border-radius: 12px; padding: 18px; transition: all 0.2s;" onmouseover="this.style.borderColor='#0ea5e9'" onmouseout="this.style.borderColor='var(--border-glow)'">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 8px;">
                                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; font-family: var(--font-heading);">
                                        <a href="/food-tour/{{ $tour->slug }}" style="color: var(--text-main); text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#0ea5e9'" onmouseout="this.style.color='var(--text-main)'">
                                            {{ $tour->title }}
                                        </a>
                                    </h4>
                                    
                                    <!-- Public / Private Status Badge -->
                                    <div>
                                        @if($tour->status === 'saved' && $tour->is_public)
                                            <span style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 12px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                                🌐 Công khai
                                            </span>
                                        @else
                                            <span style="background: rgba(100, 116, 139, 0.1); color: #64748b; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 12px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                                🔒 Riêng tư
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <p style="margin: 0 0 12px; color: var(--text-muted); font-size: 0.85rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $tour->description ?: 'Không có mô tả.' }}
                                </p>

                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-top: 1px solid rgba(0,0,0,0.04); padding-top: 12px; margin-top: 6px;">
                                    <div style="font-size: 0.82rem; color: #475569; display: flex; align-items: center; gap: 4px; font-weight: 550;">
                                        <span>📍</span> {{ $tour->stops->count() }} địa điểm dừng chân
                                    </div>

                                    <!-- Quick actions CRUD tools -->
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <!-- View Detail -->
                                        <a href="/food-tour/{{ $tour->slug }}" class="btn-secondary" style="text-decoration: none; padding: 6px 12px; font-size: 0.78rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" title="Xem chi tiết lộ trình">
                                            👁️ Xem
                                        </a>

                                        <!-- Edit Custom Tour -->
                                        <a href="/food-tour/{{ $tour->slug }}/edit" class="btn-secondary" style="text-decoration: none; padding: 6px 12px; font-size: 0.78rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; color: #4f46e5 !important;" title="Chỉnh sửa hành trình">
                                            ✏️ Sửa
                                        </a>

                                        <!-- Toggle Public Status -->
                                        <form action="/food-tour/{{ $tour->slug }}/share" method="POST" style="margin: 0; display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 0.78rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;" title="{{ $tour->is_public ? 'Hủy chia sẻ lên cộng đồng' : 'Chia sẻ công khai lên cộng đồng' }}">
                                                @if($tour->is_public)
                                                    🔌 Khóa
                                                @else
                                                    🌐 Chia sẻ
                                                @endif
                                            </button>
                                        </form>

                                        <!-- Delete Tour -->
                                        <form action="/food-tour/{{ $tour->slug }}" method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lộ trình này không? Hành động này không thể hoàn tác!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 0.78rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; color: #ef4444 !important; cursor: pointer;" title="Xóa lộ trình">
                                                🗑️ Xóa
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

<style>
/* CSS Grid Responsive Layout for Profile Page */
@media (min-width: 992px) {
    .profile-grid-desktop {
        grid-template-columns: 380px 1fr !important;
    }
}
.modal-backdrop {
    display: flex;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(8px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
</style>

<!-- Password Change Modal Overlay -->
<div x-show="showPasswordModal" 
     x-transition.opacity
     style="display: none;"
     class="modal-backdrop">
    
    <!-- Modal Box -->
    <div @click.outside="showPasswordModal = false" 
         x-show="showPasswordModal"
         x-transition.scale
         class="glass-panel" 
         style="background: var(--bg-card); padding: 30px; border: 1px solid var(--border-glow); border-radius: 16px; box-shadow: var(--shadow-overlay); width: 100%; max-width: 440px; position: relative;">
        
        <button type="button" @click="showPasswordModal = false" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--text-muted); padding: 4px; transition: color 0.15s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--text-muted)'">✕</button>
        
        <div style="text-align: center; margin-bottom: 24px;">
            <span style="font-size: 2.5rem; display: block; margin-bottom: 8px;">🔑</span>
            <h3 style="font-size: 1.4rem; font-family: var(--font-heading); font-weight: 800; background: var(--primary-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; padding: 4px 0;">
                Thay đổi mật khẩu
            </h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">
                Nhập mật khẩu mới của bạn bên dưới để đổi mật khẩu.
            </p>
        </div>

        <form action="/profile/password" method="POST">
            @csrf
            @method('PUT')

            <!-- Mã xác thực OTP -->
            <div class="review-form-group" style="margin-bottom: 16px;">
                <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 6px;">Mã xác thực OTP</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="otp" required class="form-input" style="padding: 10px 14px; flex: 1; border-radius: 8px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.7);" placeholder="Nhập mã 6 số" maxlength="6" pattern="\d{6}">
                    <button type="button" @click="sendOtp()" :disabled="otpCooldown > 0" class="btn-secondary" style="padding: 10px 16px; border-radius: 8px; font-size: 0.85rem; white-space: nowrap; font-weight: 600; cursor: pointer; transition: all 0.2s;" :style="otpCooldown > 0 ? 'opacity: 0.6; cursor: not-allowed; background: rgba(255,255,255,0.1);' : ''" x-text="cooldownText"></button>
                </div>
                <div x-show="otpFeedback" style="margin-top: 6px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <span x-show="otpFeedbackType === 'success'">✅</span>
                    <span x-show="otpFeedbackType === 'error'">⚠️</span>
                    <span x-show="otpFeedbackType === 'info'">⏳</span>
                    <span x-text="otpFeedback" :style="otpFeedbackType === 'success' ? 'color: #10b981;' : (otpFeedbackType === 'error' ? 'color: #ef4444;' : 'color: var(--text-muted);')"></span>
                </div>
            </div>

            <div class="review-form-group" style="margin-bottom: 16px;">
                <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 6px;">Mật khẩu mới</label>
                <input type="password" name="password" required class="form-input" style="padding: 10px 14px; width: 100%; border-radius: 8px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.7);" placeholder="Tối thiểu 6 ký tự">
            </div>

            <div class="review-form-group" style="margin-bottom: 24px;">
                <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); display: block; margin-bottom: 6px;">Xác nhận mật khẩu mới</label>
                <input type="password" name="password_confirmation" required class="form-input" style="padding: 10px 14px; width: 100%; border-radius: 8px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.7);" placeholder="••••••••">
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" @click="showPasswordModal = false" class="btn-secondary" style="padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Hủy
                </button>
                <button type="submit" class="btn-primary" style="padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
