@extends('layouts.app')

@section('title', 'Đăng ký tài khoản - Bản đồ số Khám phá Đông Anh')

@section('content')
<div style="position: relative; overflow: hidden; background: var(--bg-base); min-height: calc(100vh - var(--header-height) - 160px); display: flex; align-items: center; justify-content: center; width: 100%;">
    <!-- Glowing atmosphere orbs -->
    <div class="cinematic-glow-orb-1" style="top: 10%; left: 20%; width: 350px; height: 350px;"></div>
    <div class="cinematic-glow-orb-2" style="bottom: 10%; right: 20%; width: 400px; height: 400px;"></div>
    
    <!-- Sparkles particles -->
    <div class="particles-container">
        <div class="particle p-1"></div>
        <div class="particle p-2" style="animation-delay: 2s;"></div>
        <div class="particle p-3" style="animation-delay: 4s;"></div>
        <div class="particle p-4" style="animation-delay: 1.5s;"></div>
    </div>
    
    <!-- Shooting stars meteor shower background -->
    <div class="shooting-stars-container">
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
    </div>
    
    <div class="container" style="padding: 60px 0; display: flex; justify-content: center; align-items: center; position: relative; z-index: 2; width: 100%;">
        <div class="glass-panel hover-lift" 
             x-data="{
                email: '{{ old('email') }}',
                otpCooldown: 0,
                otpFeedback: '',
                otpFeedbackType: '',
                sendOtp() {
                    if (!this.email) {
                        this.otpFeedback = 'Vui lòng nhập địa chỉ email trước!';
                        this.otpFeedbackType = 'error';
                        return;
                    }
                    if (this.otpCooldown > 0) return;
                    
                    this.otpFeedback = 'Đang gửi mã OTP...';
                    this.otpFeedbackType = 'info';
                    
                    fetch('/auth/register/send-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ email: this.email })
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
             }"
             style="width: 100%; max-width: 680px; padding: 40px; box-shadow: var(--shadow-overlay); border: 1px solid var(--border-glow); background: var(--bg-card); backdrop-filter: blur(20px);">
            
            <div style="text-align: center; margin-bottom: 24px;">
                <span style="font-size: 3rem; display: block; margin-bottom: 10px;">🌾</span>
                <h2 style="font-size: 1.8rem; font-family: var(--font-heading); background: var(--primary-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; padding: 6px 0; line-height: 1.3; font-weight: 800;">
                    Đăng ký tài khoản mới
                </h2>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">
                    Đăng ký thành viên để nhận xét quán ngon hoặc quản trị cửa hàng
                </p>
            </div>
            
            <!-- Hiển thị các lỗi validation -->
            @if ($errors->any())
                <div class="glass-panel" style="background: rgba(240, 78, 35, 0.1); border-color: var(--primary-hover); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; color: var(--primary); font-size: 0.85rem;">
                    <ul style="list-style: none;">
                        @foreach ($errors->all() as $error)
                            <li>⚠️ {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="/auth/register" method="POST">
                @csrf
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px 20px; margin-bottom: 24px;">
                    <!-- Cột 1 -->
                    <div>
                        <div class="review-form-group" style="margin-bottom: 16px;">
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Họ và Tên</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-input" required placeholder="Nguyễn Văn A" style="padding: 11px 16px; width: 100%;">
                        </div>
                        
                        <div class="review-form-group" style="margin-bottom: 16px;">
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Địa chỉ Email</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="email" name="email" x-model="email" class="form-input" required placeholder="name@example.com" style="padding: 11px 16px; flex: 1;">
                                <button type="button" @click="sendOtp()" :disabled="otpCooldown > 0" class="btn-secondary" style="padding: 11px 16px; border-radius: 8px; font-size: 0.85rem; white-space: nowrap; font-weight: 600; cursor: pointer; transition: all 0.2s; background: rgba(255,255,255,0.05); border: 1px solid var(--border-glow); color: var(--text-main);" :style="otpCooldown > 0 ? 'opacity: 0.6; cursor: not-allowed; background: rgba(255,255,255,0.1);' : ''" x-text="cooldownText"></button>
                            </div>
                            <div x-show="otpFeedback" style="margin-top: 6px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                <span x-show="otpFeedbackType === 'success'">✅</span>
                                <span x-show="otpFeedbackType === 'error'">⚠️</span>
                                <span x-show="otpFeedbackType === 'info'">⏳</span>
                                <span x-text="otpFeedback" :style="otpFeedbackType === 'success' ? 'color: #10b981;' : (otpFeedbackType === 'error' ? 'color: #ef4444;' : 'color: var(--text-muted);')"></span>
                            </div>
                        </div>

                        <div class="review-form-group" style="margin-bottom: 16px;">
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Mã xác thực OTP</label>
                            <input type="text" name="otp" required class="form-input" placeholder="Nhập mã 6 số" maxlength="6" pattern="\d{6}" style="padding: 11px 16px; width: 100%;">
                        </div>
    
                        <div class="review-form-group" style="margin-bottom: 0;">
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Mật khẩu (Tối thiểu 6 ký tự)</label>
                            <input type="password" name="password" class="form-input" required placeholder="••••••••" style="padding: 11px 16px; width: 100%;">
                        </div>
                    </div>
    
                    <!-- Cột 2 -->
                    <div>
                        <div class="review-form-group" style="margin-bottom: 16px;">
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Số điện thoại liên hệ</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" required placeholder="Ví dụ: 0901234567" style="padding: 11px 16px; width: 100%;">
                        </div>
    
                        <div class="review-form-group" style="margin-bottom: 16px;">
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Vai trò tài khoản</label>
                            <select name="role" class="form-input" style="padding: 11px 16px; width: 100%; height: 42px; cursor: pointer; background: var(--bg-input, rgba(255,255,255,0.05)); border: 1px solid var(--border-glow); border-radius: 8px; color: var(--text-main);">
                                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Người dùng</option>
                                <option value="seller" {{ old('role') === 'seller' ? 'selected' : '' }}>Chủ cơ sở, cửa hàng</option>
                            </select>
                        </div>
                        
                        <div class="review-form-group" style="margin-bottom: 0;">
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Xác nhận Mật khẩu</label>
                            <input type="password" name="password_confirmation" class="form-input" required placeholder="••••••••" style="padding: 11px 16px; width: 100%;">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 12px 0; font-size: 1rem; margin-bottom: 20px;">
                    Đăng ký tài khoản
                </button>
            </form>
            
            <div style="text-align: center; border-top: 1px solid var(--border-glow); padding-top: 20px; font-size: 0.85rem; color: var(--text-muted);">
                Đã có tài khoản? 
                <a href="/auth/login" style="color: var(--primary); font-weight: 600;">Đăng nhập ngay</a>
            </div>
            
        </div>
    </div>
</div>
@endsection

