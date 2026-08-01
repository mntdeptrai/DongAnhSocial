@extends('layouts.admin')

@section('title', '➕ Thêm User mới')

@section('content')
<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem;">👥 Thêm Tài Khoản Mới</h1>
            <p>Khai báo và cấp quyền cho thành viên mới của hệ thống</p>
        </div>
        <a href="/admin/users" class="btn-admin btn-admin-accent" style="padding: 8px 18px; border-radius: 8px; font-weight: 700; text-decoration: none;">
            ⬅ Quay lại
        </a>
    </div>
</div>

<!-- Errors Notification Banner -->
@if ($errors->any())
    <div class="admin-alert admin-alert-warning" style="background-color: #fee2e2; border-color: #fecaca; color: #b91c1c; margin-bottom: 24px;">
        <div>
            <strong style="display: block; margin-bottom: 6px;">⚠️ Vui lòng hoàn thiện các thông tin hợp lệ:</strong>
            <ul style="padding-left: 20px; font-size: 0.85rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="admin-card" style="max-width: 800px; margin: 0 auto; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); border-radius: 20px;">
    <div class="admin-card-header" style="border-bottom: 1.5px solid var(--admin-border); padding-bottom: 16px;">
        <h2 class="admin-card-title" style="font-size: 1.1rem; font-weight: 700; color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <span>➕</span> Thêm User Mới
        </h2>
    </div>

    <form action="/admin/users" method="POST" style="padding: 24px 0 8px 0;">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Họ và tên -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Họ và tên <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">👤</span>
                    <input type="text" name="name" required value="{{ old('name') }}" class="admin-form-input" placeholder="Nhập họ và tên..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Tên đăng nhập (username) -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tên đăng nhập (Username) <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🔑</span>
                    <input type="text" name="username" required value="{{ old('username') }}" class="admin-form-input" pattern="^[a-zA-Z0-9_.-]+$" title="Tên đăng nhập chỉ gồm chữ, số, dấu gạch nối, gạch dưới hoặc dấu chấm (không dấu, không khoảng trắng)" placeholder="Nhập tên đăng nhập (Ví dụ: nguyenvana)..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Email -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Email <span style="color: var(--admin-text-muted); font-weight: normal;">(Không bắt buộc)</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">✉️</span>
                    <input type="email" name="email" value="{{ old('email') }}" class="admin-form-input" placeholder="email@example.com" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Số điện thoại -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Số điện thoại <span style="color: var(--admin-text-muted); font-weight: normal;">(Không bắt buộc)</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">📞</span>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="admin-form-input" pattern="0[0-9]{9}" title="Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0" placeholder="0901234567" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Vai trò -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Vai trò <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    @if(session('user_role') === 'manager')
                        <div style="background: #f0fdf4; border: 1.5px solid #86efac; padding: 8px 14px; border-radius: 10px; color: #166534; font-weight: 700; font-size: 0.86rem; display: flex; align-items: center; gap: 8px; height: 42px; box-sizing: border-box;">
                            <span>🛡️</span> Seller (Chủ gian hàng / Tiểu thương) 🔒 <span style="font-weight: 400; font-size: 0.76rem; color: #15803d;">(Cố định theo BQL Chợ)</span>
                        </div>
                        <input type="hidden" name="role" value="seller">
                    @else
                        <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🛡️</span>
                        <select name="role" required class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem;">
                            <option value="seller" {{ old('role') === 'seller' ? 'selected' : '' }}>Seller (Chủ cơ sở / Tiểu thương)</option>
                            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager (Ban Quản Lý Chợ)</option>
                            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Customer (Khách hàng)</option>
                            <option value="principal" {{ old('role') === 'principal' ? 'selected' : '' }}>Principal (Hiệu trưởng / Quản lý giáo dục)</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Quản trị viên hệ thống)</option>
                        </select>
                    @endif
                </div>
            </div>

            <!-- Avatar Emoji -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Avatar (Emoji) <span style="color: var(--admin-text-muted); font-weight: normal;">(Mặc định 🧑)</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">👤</span>
                    <input type="text" name="avatar" value="{{ old('avatar', '🧑') }}" class="admin-form-input" placeholder="Nhập Emoji đại diện (Ví dụ: 👨‍🍳, 👨‍💼...)" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px;">
            <!-- Mật khẩu -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Mật khẩu khởi tạo <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🔒</span>
                    <input type="password" name="password" required class="admin-form-input" placeholder="Nhập mật khẩu..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
            
            <div></div>
        </div>

        @if(session('user_role') === 'manager' || (isset($stalls) && count($stalls) > 0))
            <!-- Gian Hàng Chợ Số liên kết (Dành cho Manager quản lý tiểu thương) -->
            <div id="stall_selection_group" class="admin-form-group" style="margin-bottom: 28px;">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.84rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">🛒 Gian Hàng Số Trong Chợ Liên Kết <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🛒</span>
                    <select name="stall_id" id="stall_id_select" class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem;" onchange="autoFillStallInfo(this)">
                        <option value="">-- Chọn Gian Hàng Quản Lý trong Chợ --</option>
                        @foreach($stalls as $st)
                            <option value="{{ $st->id }}" data-seller="{{ $st->seller_name }}" data-phone="{{ $st->seller_phone }}" {{ old('stall_id') == $st->id ? 'selected' : '' }}>
                                [{{ $st->stall_name }}] - Hộ: {{ $st->seller_name }} (SĐT: {{ $st->seller_phone ?: 'Chưa có' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <small style="color: var(--admin-text-muted); display: block; margin-top: 6px;">Tài khoản tiểu thương sẽ trực tiếp làm chủ và quản lý dữ liệu gian hàng số này.</small>
            </div>
        @else
            <!-- Cơ sở kinh doanh (Dành cho Seller, Principal, Manager) -->
            <div id="eatery_selection_group" class="admin-form-group" style="display: none; margin-bottom: 28px;">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Cơ sở kinh doanh liên kết (Dành cho Seller)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏢</span>
                    <select name="eatery_id" id="eatery_id_select" class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem;">
                        <option value="">-- Chọn cơ sở quản lý (Hoặc để trống tạo sau) --</option>
                        @foreach($eateries as $eat)
                            <option value="{{ $eat->id }}" data-category="{{ $eat->category->slug }}" {{ old('eatery_id') == $eat->id ? 'selected' : '' }}>
                                [{{ $eat->category->name }}] {{ $eat->name }} ({{ $eat->address }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1.5px solid var(--admin-border); padding-top: 20px;">
            <a href="/admin/users" class="btn-admin btn-admin-accent" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; text-decoration: none;">
                Hủy
            </a>
            <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 32px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border: none;">
                ✓ Lưu Lại
            </button>
        </div>
    </form>
</div>

<script>
function autoFillStallInfo(select) {
    const option = select.options[select.selectedIndex];
    if (option && option.value) {
        const sellerName = option.getAttribute('data-seller');
        const sellerPhone = option.getAttribute('data-phone');
        const nameInput = document.querySelector('input[name="name"]');
        const phoneInput = document.querySelector('input[name="phone"]');

        if (sellerName && sellerName !== 'Cần cập nhật thông tin' && (!nameInput.value || nameInput.dataset.autofilled)) {
            nameInput.value = sellerName;
            nameInput.dataset.autofilled = "true";
        }
        if (sellerPhone && sellerPhone !== 'Cần cập nhật thông tin' && (!phoneInput.value || phoneInput.dataset.autofilled)) {
            phoneInput.value = sellerPhone;
            phoneInput.dataset.autofilled = "true";
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.querySelector('select[name="role"]');
    const eateryGroup = document.getElementById('eatery_selection_group');
    const eaterySelect = document.getElementById('eatery_id_select');

    if (roleSelect && eateryGroup && eaterySelect) {
        // Lưu danh sách options ban đầu
        const originalOptions = Array.from(eaterySelect.options).map(option => ({
            value: option.value,
            text: option.text,
            category: option.getAttribute('data-category') || ''
        }));

        const originalLabel = eateryGroup.querySelector('label');

        function toggleEaterySelection() {
            const role = roleSelect.value;
            const currentSelectedValue = eaterySelect.value;

            if (role === 'seller' || role === 'principal' || role === 'manager') {
                eateryGroup.style.display = 'block';

                // Cập nhật tiêu đề nhãn tùy theo vai trò
                if (role === 'manager') {
                    originalLabel.innerHTML = '🏪 Chợ truyền thống liên kết (Dành cho Manager) <span style="color: var(--admin-danger);">*</span>';
                } else if (role === 'principal') {
                    originalLabel.innerHTML = '🏫 Trường học liên kết (Dành cho Principal) <span style="color: var(--admin-danger);">*</span>';
                } else {
                    originalLabel.innerHTML = '🏢 Cơ sở kinh doanh liên kết (Dành cho Seller)';
                }

                // Xóa các options cũ
                eaterySelect.innerHTML = '';

                // Lọc danh sách theo vai trò
                const filtered = originalOptions.filter(opt => {
                    if (opt.value === '') return true; // Luôn giữ placeholder
                    if (role === 'manager') {
                        return opt.category === 'traditional-market';
                    }
                    if (role === 'principal') {
                        return opt.category === 'smart-education-map';
                    }
                    if (role === 'seller') {
                        // Seller: hiển thị tất cả cơ sở (bao gồm OCOP), trừ chợ truyền thống và trường học
                        return opt.category !== 'traditional-market' && opt.category !== 'smart-education-map';
                    }
                    return false;
                });

                // Thêm các option đã lọc vào select
                filtered.forEach(opt => {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.text = opt.text;
                    newOpt.setAttribute('data-category', opt.category);
                    if (opt.value === currentSelectedValue) {
                        newOpt.selected = true;
                    }
                    eaterySelect.appendChild(newOpt);
                });
            } else {
                eateryGroup.style.display = 'none';
            }
        }
        roleSelect.addEventListener('change', toggleEaterySelection);
        toggleEaterySelection();
    }
});
</script>
@endsection
