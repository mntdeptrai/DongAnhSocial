@extends('layouts.admin')

@section('title', '⚙️ Chỉnh sửa User: ' . $user->name)

@section('content')
<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem;">👥 Chỉnh Sửa Tài Khoản</h1>
            <p>Thay đổi thông tin và quyền hạn của thành viên {{ $user->name }}</p>
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
            <span>✏️</span> Chỉnh Sửa User: {{ $user->name }}
        </h2>
    </div>

    <form action="/admin/users/{{ $user->id }}" method="POST" style="padding: 24px 0 8px 0;">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Họ và tên -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Họ và tên <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">👤</span>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="admin-form-input" placeholder="Nhập họ và tên..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Avatar Emoji -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Avatar (Emoji) <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">👤</span>
                    <input type="text" name="avatar" required value="{{ old('avatar', $user->avatar ?: '🧑') }}" class="admin-form-input" placeholder="Nhập Emoji đại diện..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Email -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Email <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">✉️</span>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}" class="admin-form-input" placeholder="email@example.com" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Số điện thoại -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Số điện thoại <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">📞</span>
                    <input type="text" name="phone" required value="{{ old('phone', $user->phone) }}" class="admin-form-input" placeholder="0901234567" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Vai trò -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Vai trò <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🛡️</span>
                    <select name="role" required class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem;">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Customer (Khách hàng)</option>
                        <option value="seller" {{ old('role', $user->role) === 'seller' ? 'selected' : '' }}>Seller (Chủ cơ sở ẩm thực)</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin (Quản trị viên hệ thống)</option>
                    </select>
                </div>
            </div>

            <!-- Trạng thái -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Trạng thái tài khoản <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🔌</span>
                    <select name="status" required class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem;" {{ $user->id === session('user_id') ? 'disabled' : '' }}>
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Hoạt động (Active)</option>
                        <option value="disabled" {{ old('status', $user->status) === 'disabled' ? 'selected' : '' }}>Vô hiệu hóa (Disabled)</option>
                    </select>
                    @if($user->id === session('user_id'))
                        <input type="hidden" name="status" value="active">
                    @endif
                </div>
            </div>
        </div>

        <!-- Đổi mật khẩu -->
        <div class="admin-form-group" style="margin-bottom: 24px;">
            <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Thay đổi mật khẩu <span style="color: var(--admin-text-muted); font-weight: normal;">(Để trống nếu không muốn đổi mật khẩu)</span></label>
            <div style="position: relative;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🔒</span>
                <input type="password" name="password" class="admin-form-input" placeholder="Nhập mật khẩu mới..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
            </div>
        </div>

        <!-- Cơ sở kinh doanh (Chỉ hiện khi chọn Seller) -->
        <div id="eatery_selection_group" class="admin-form-group" style="display: none; margin-bottom: 28px;">
            <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Cơ sở kinh doanh liên kết (Dành cho Seller)</label>
            <div style="position: relative;">
                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏢</span>
                <select name="eatery_id" class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem;">
                    <option value="">-- Chọn cơ sở quản lý (Hoặc để trống tạo sau) --</option>
                    @foreach($eateries as $eat)
                        <option value="{{ $eat->id }}" {{ old('eatery_id', $currentEateryId) == $eat->id ? 'selected' : '' }}>
                            [{{ $eat->category->name }}] {{ $eat->name }} ({{ $eat->address }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1.5px solid var(--admin-border); padding-top: 20px;">
            <a href="/admin/users" class="btn-admin btn-admin-accent" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; text-decoration: none;">
                Hủy
            </a>
            <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 32px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border: none;">
                ✓ Cập Nhật
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.querySelector('select[name="role"]');
    const eateryGroup = document.getElementById('eatery_selection_group');

    function toggleEaterySelection() {
        if (roleSelect.value === 'seller') {
            eateryGroup.style.display = 'block';
        } else {
            eateryGroup.style.display = 'none';
        }
    }

    roleSelect.addEventListener('change', toggleEaterySelection);
    toggleEaterySelection();
});
</script>
@endsection
