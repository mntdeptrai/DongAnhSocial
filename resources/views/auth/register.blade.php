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
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Tên đăng nhập (Username)</label>
                            <input type="text" name="username" value="{{ old('username') }}" class="form-input" required pattern="^[a-zA-Z0-9_.-]+$" title="Tên đăng nhập chỉ gồm chữ, số, dấu gạch nối, gạch dưới hoặc dấu chấm (không dấu, không khoảng trắng)" placeholder="Ví dụ: nguyenvana" style="padding: 11px 16px; width: 100%;">
                        </div>

                        <div class="review-form-group" style="margin-bottom: 16px;">
                            <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Số điện thoại liên hệ</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" required pattern="0[0-9]{9}" title="Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0" placeholder="Ví dụ: 0901234567" style="padding: 11px 16px; width: 100%;">
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
                
                <!-- Checkbox đồng ý Điều khoản & Thu thập thông tin -->
                <div style="margin-bottom: 20px;" x-data="{ openTermsModal: false }">
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.83rem; color: var(--text-muted); cursor: pointer; user-select: none; line-height: 1.5;">
                        <input type="checkbox" name="agree_terms" required style="width: 17px; height: 17px; margin-top: 2px; accent-color: var(--primary); cursor: pointer; flex-shrink: 0;">
                        <span>
                            Tôi đã đọc, hiểu rõ và đồng ý với 
                            <a href="javascript:void(0)" @click.prevent="openTermsModal = true" style="color: var(--primary); font-weight: 700; text-decoration: underline;">
                                Điều khoản dịch vụ
                            </a> 
                            và 
                            <a href="javascript:void(0)" @click.prevent="openTermsModal = true" style="color: var(--primary); font-weight: 700; text-decoration: underline;">
                                Chính sách thu thập thông tin
                            </a> 
                            của Bản đồ số Khám phá Đông Anh.
                        </span>
                    </label>

                    <!-- Modal Chi Tiết Điều Khoản & Thu Thập Thông Tin -->
                    <div x-show="openTermsModal" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(8px); padding: 20px;"
                         @keydown.escape.window="openTermsModal = false"
                         x-cloak>
                         
                        <div @click.outside="openTermsModal = false"
                             style="background: var(--bg-surface, #ffffff); border: 1.5px solid var(--border-glow); width: 100%; max-width: 650px; max-height: 85vh; border-radius: 20px; box-shadow: var(--shadow-overlay, 0 25px 50px -12px rgba(0,0,0,0.5)); display: flex; flex-direction: column; overflow: hidden; color: var(--text-main);">
                             
                            <!-- Header Modal -->
                            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-glow); display: flex; align-items: center; justify-content: space-between; background: rgba(14, 165, 233, 0.06);">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 1.3rem;">📜</span>
                                    <h3 style="font-size: 1.1rem; font-weight: 800; font-family: var(--font-heading); margin: 0; color: var(--text-main);">
                                        Điều Khoản Dịch Vụ & Bảo Mật Thông Tin
                                    </h3>
                                </div>
                                <button type="button" @click="openTermsModal = false" style="background: transparent; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted); padding: 0 4px; line-height: 1;">&times;</button>
                            </div>
                            
                            <!-- Nội dung chi tiết Điều khoản -->
                            <div style="padding: 24px; overflow-y: auto; font-size: 0.86rem; line-height: 1.65; color: var(--text-muted); flex: 1;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">1. Mục Đích Thu Thập Thông Tin Cá Nhân</h4>
                                <p style="margin-bottom: 14px;">Bản đồ số Khám phá Đông Anh thu thập các thông tin cá nhân cơ bản (Họ tên, Email, Số điện thoại, Vai trò tài khoản) phục vụ các mục đích sau:</p>
                                <ul style="margin: 0 0 16px 20px; padding: 0; list-style-type: disc;">
                                    <li style="margin-bottom: 6px;">Xác thực danh tính và gửi mã bảo mật OTP qua Email nhằm bảo vệ tài khoản chính chủ.</li>
                                    <li style="margin-bottom: 6px;">Cho phép thành viên gửi đánh giá, tải ảnh check-in trải nghiệm các địa điểm văn hóa, ẩm thực, Food Tour trên địa bàn Xã Đông Anh.</li>
                                    <li style="margin-bottom: 6px;">Hỗ trợ các chủ gian hàng/cơ sở kinh doanh tiếp nhận mã thanh toán VietQR và cập nhật danh mục sản phẩm OCOP chính thức.</li>
                                </ul>

                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">2. Phạm Vi Thu Thập & Lưu Trữ Dữ Liệu</h4>
                                <ul style="margin: 0 0 16px 20px; padding: 0; list-style-type: disc;">
                                    <li style="margin-bottom: 6px;"><strong>Thông tin tài khoản:</strong> Họ và tên, Email, Số điện thoại, Tên đăng nhập và Mật khẩu (đã mã hóa bảo mật 1 chiều Bcrypt/Argon2).</li>
                                    <li style="margin-bottom: 6px;"><strong>Dữ liệu định vị & Tương tác:</strong> Vị trí GPS (khi người dùng cho phép trên trình duyệt để tìm kiếm địa điểm xung quanh) và nội dung bình luận công khai.</li>
                                </ul>

                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">3. Cam Kết Bảo Mật Thông Tin Người Dùng</h4>
                                <p style="margin-bottom: 14px;">Hệ thống cam kết tuyệt đối không bán, chia sẻ hoặc tiết lộ thông tin cá nhân của bạn cho bên thứ ba vì mục đích thương mại. Mọi thông tin được bảo mật trên máy chủ và tuân thủ các quy định hiện hành về bảo vệ dữ liệu cá nhân.</p>

                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">4. Quy Định Sử Dụng & Trách Nhiệm Thành Viên</h4>
                                <ul style="margin: 0 0 16px 20px; padding: 0; list-style-type: disc;">
                                    <li style="margin-bottom: 6px;">Không đăng tải văn hóa phẩm độc hại, nội dung vi phạm pháp luật, thông tin sai sự thật về các cơ sở kinh doanh, điểm đến trên địa bàn.</li>
                                    <li style="margin-bottom: 6px;">Tự quản lý và bảo mật thông tin đăng nhập cá nhân. Nối lại với ban quản trị nếu phát hiện nghi vấn truy cập trái phép.</li>
                                </ul>

                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">5. Quyền Của Người Dùng Đối Với Dữ Liệu</h4>
                                <p style="margin-bottom: 0;">Bạn có quyền truy cập, cập nhật thông tin cá nhân hoặc gửi yêu cầu xóa tài khoản/dữ liệu khỏi hệ thống bất kỳ lúc nào thông qua trang Quản lý tài khoản hoặc liên hệ Ban quản trị.</p>
                            </div>
                            
                            <!-- Footer Modal -->
                            <div style="padding: 14px 24px; border-top: 1px solid var(--border-glow); display: flex; justify-content: flex-end; background: rgba(14, 165, 233, 0.03);">
                                <button type="button" @click="openTermsModal = false" class="btn-primary" style="padding: 8px 24px; font-size: 0.85rem; border-radius: 10px; font-weight: 700;">
                                    Đã hiểu & Đóng
                                </button>
                            </div>
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

