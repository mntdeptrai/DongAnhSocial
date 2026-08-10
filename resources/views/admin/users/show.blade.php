@extends('layouts.admin')

@section('title', '👁️ Chi tiết User: ' . $user->name)

@section('content')
<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem;">👥 Chi Tiết Người Dùng</h1>
            <p>Xem thông tin chi tiết hồ sơ tài khoản {{ $user->name }}</p>
        </div>
        <a href="/admin/users" class="btn-admin btn-admin-accent" style="padding: 8px 18px; border-radius: 8px; font-weight: 700; text-decoration: none;">
            ⬅ Quay lại
        </a>
    </div>
</div>

<div class="admin-card" style="max-width: 850px; margin: 0 auto; box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.08); border-radius: 20px; overflow: hidden; padding: 0; border: none;">
    <!-- Large Blue/Purple Profile Header Banner -->
    <div style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); padding: 32px 32px 100px 32px; position: relative;">
        <div style="display: flex; gap: 20px; align-items: center;">
            @php
                $isImg = !empty($user->avatar) && (str_starts_with($user->avatar, 'http') || str_contains($user->avatar, '/') || preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $user->avatar));
                $avatarSrc = $isImg ? ($user->avatar_url ?: (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')))) : null;
            @endphp
            <div style="width: 72px; height: 72px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; border: 2.5px solid #ffffff; backdrop-filter: blur(4px); box-shadow: 0 4px 10px rgba(0,0,0,0.15); overflow: hidden; flex-shrink: 0;">
                @if($isImg && $avatarSrc)
                    <img src="{{ $avatarSrc }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='🧑'">
                @else
                    {{ $user->avatar ?: '🧑' }}
                @endif
            </div>
            <div>
                <h2 style="margin: 0; color: #ffffff; font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                    {{ $user->name }}
                    @if($user->role === 'admin')
                        <span class="admin-badge" style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border-color: rgba(255,255,255,0.4); font-size: 0.72rem; font-weight: 700; padding: 3px 10px;">Admin</span>
                    @elseif($user->role === 'principal')
                        <span class="admin-badge" style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border-color: rgba(255,255,255,0.4); font-size: 0.72rem; font-weight: 700; padding: 3px 10px;">Principal 🏫</span>
                    @elseif($user->role === 'manager')
                        <span class="admin-badge" style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border-color: rgba(255,255,255,0.4); font-size: 0.72rem; font-weight: 700; padding: 3px 10px;">Manager 🏛️</span>
                    @elseif($user->role === 'seller')
                        <span class="admin-badge" style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border-color: rgba(255,255,255,0.4); font-size: 0.72rem; font-weight: 700; padding: 3px 10px;">Seller</span>
                    @else
                        <span class="admin-badge" style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border-color: rgba(255,255,255,0.4); font-size: 0.72rem; font-weight: 700; padding: 3px 10px;">Customer</span>
                    @endif
                </h2>
                <span style="color: rgba(255, 255, 255, 0.85); font-size: 0.86rem; margin-top: 4px; display: block;">✉️ {{ $user->email }}</span>
            </div>
        </div>
    </div>

    <!-- Floating Overview Stats Cards -->
    <div style="padding: 0 32px; margin-top: -60px; position: relative; z-index: 10; display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 32px; max-width: 300px;">
        <!-- Card 1 -->
        <div style="padding: 16px 20px; background-color: #ffffff; border-radius: 12px; border: 1px solid rgba(124, 58, 237, 0.15); box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.05); display: flex; flex-direction: column;">
            <span style="font-size: 0.72rem; font-weight: 700; color: #a78bfa; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">📅 Ngày tham gia hệ thống</span>
            <strong style="font-size: 1.1rem; color: var(--admin-text-main);">{{ $user->created_at->format('d/m/Y') }}</strong>
        </div>
    </div>

    <!-- Detailed Info Grid -->
    <div style="padding: 0 32px 32px 32px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
            <!-- Block: Phone -->
            <div style="padding: 14px 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc;">
                <span style="font-size: 0.78rem; color: var(--admin-text-muted); display: block; margin-bottom: 4px; font-weight: 700; text-transform: uppercase;">📞 Số điện thoại</span>
                <strong style="font-size: 0.95rem; color: var(--admin-text-main);">{{ $user->phone ?: 'Chưa cập nhật' }}</strong>
            </div>

            <!-- Block: Email -->
            <div style="padding: 14px 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc;">
                <span style="font-size: 0.78rem; color: var(--admin-text-muted); display: block; margin-bottom: 4px; font-weight: 700; text-transform: uppercase;">✉️ Email liên hệ</span>
                <strong style="font-size: 0.95rem; color: var(--admin-text-main);">{{ $user->email }}</strong>
            </div>

            <!-- Block: Last Login -->
            <div style="padding: 14px 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc;">
                <span style="font-size: 0.78rem; color: var(--admin-text-muted); display: block; margin-bottom: 4px; font-weight: 700; text-transform: uppercase;">📅 Lần đăng nhập cuối</span>
                <strong style="font-size: 0.95rem; color: var(--admin-text-main);">{{ $user->updated_at->format('H:i d/m/Y') }}</strong>
            </div>

            <!-- Block: Status -->
            <div style="padding: 14px 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc; display: flex; flex-direction: column; justify-content: center;">
                <span style="font-size: 0.78rem; color: var(--admin-text-muted); display: block; margin-bottom: 6px; font-weight: 700; text-transform: uppercase;">🔌 Trạng thái</span>
                <div>
                    @if($user->status === 'active')
                        <span class="admin-badge admin-badge-success" style="font-size: 0.8rem; font-weight: 700; padding: 4px 14px;">Hoạt động</span>
                    @else
                        <span class="admin-badge" style="font-size: 0.8rem; font-weight: 700; background-color: #f1f5f9; color: #64748b; border: 1px solid rgba(100, 116, 139, 0.15); padding: 4px 14px;">Vô hiệu hóa</span>
                    @endif
                </div>
            </div>
        </div>

        @if($user->role === 'seller')
            <div style="margin-top: 24px; margin-bottom: 32px; padding: 22px; border: 1.5px solid rgba(124, 58, 237, 0.2); border-radius: 16px; background-color: rgba(124, 58, 237, 0.02);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px dashed rgba(124, 58, 237, 0.2); padding-bottom: 10px;">
                    <span style="font-size: 0.88rem; color: #6d28d9; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                        🛒 THÔNG TIN GIAN HÀNG SỐ & CHỢ QUẢN LÝ
                    </span>
                    <a href="/admin/stalls" class="btn-admin" style="font-size: 0.76rem; padding: 4px 12px; background: #e0e7ff; color: #3730a3; font-weight: 700; border-radius: 6px; text-decoration: none;">
                        🏬 Đến quản lý gian hàng ➔
                    </a>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <!-- Gian Hàng Số -->
                    <div style="padding: 12px 16px; background: #ffffff; border: 1px solid var(--admin-border); border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; font-weight: 700; text-transform: uppercase;">🛒 Gian hàng số</span>
                        <strong style="font-size: 0.95rem; color: #2563eb; display: block; margin-top: 2px;">
                            {{ $stall ? ($stall->stall_name ?: $stall->seller_name) : 'Gian Hàng Chưa Gán' }}
                        </strong>
                        @if($stall && $stall->seller_name)
                            <span style="font-size: 0.78rem; color: var(--admin-text-muted); display: block; margin-top: 2px;">
                                👤 Chủ sạp: {{ $stall->seller_name }}
                            </span>
                        @endif
                    </div>

                    <!-- Chợ Quản Lý -->
                    <div style="padding: 12px 16px; background: #ffffff; border: 1px solid var(--admin-border); border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; font-weight: 700; text-transform: uppercase;">🏢 Thuộc Chợ / Cơ sở</span>
                        <strong style="font-size: 0.95rem; color: #166534; display: block; margin-top: 2px;">
                            {{ $market ? $market->name : 'Chợ Mạch Tràng' }}
                        </strong>
                        <span style="font-size: 0.78rem; color: var(--admin-text-muted); display: block; margin-top: 2px;">
                            📍 Khu vực Đông Anh, Hà Nội
                        </span>
                    </div>
                </div>

                @if($stall && !empty($stall->bank_account) && $stall->bank_account !== '0987654321')
                    <div style="padding: 10px 14px; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 10px; margin-bottom: 16px; color: #0369a1; font-size: 0.82rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        💳 Thông tin thanh toán QR: <span>🏦 {{ !empty($stall->bank_name) ? $stall->bank_name : 'Ngân hàng' }}: {{ $stall->bank_account }}</span>
                    </div>
                @endif

                <!-- Danh sách sản phẩm của gian hàng -->
                <div>
                    <span style="font-size: 0.8rem; color: var(--admin-text-main); font-weight: 700; display: block; margin-bottom: 8px;">
                        📦 Danh sách sản phẩm kinh doanh ({{ count($products) }} mặt hàng):
                    </span>
                    @if(count($products) > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            @foreach($products as $prod)
                                <div style="padding: 6px 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.8rem; font-weight: 600; color: #334155; display: flex; align-items: center; gap: 6px;">
                                    <span>📦 {{ $prod->name }}</span>
                                    <span style="color: #059669; font-weight: 700;">{{ number_format($prod->price) }}đ{{ $prod->unit ? '/'.$prod->unit : '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-style: italic;">Chưa có sản phẩm nào được tải lên.</span>
                    @endif
                </div>
            </div>
        @elseif($user->role === 'manager')
            <div style="margin-top: 24px; margin-bottom: 32px; padding: 20px; border: 1.5px solid rgba(16, 185, 129, 0.3); border-radius: 16px; background-color: rgba(16, 185, 129, 0.03);">
                <span style="font-size: 0.85rem; color: #047857; display: block; margin-bottom: 8px; font-weight: 800; text-transform: uppercase;">🏢 Ban Quản Lý Chợ</span>
                <strong style="font-size: 1rem; color: var(--admin-text-main); display: block;">
                    {{ $market ? $market->name : 'Chợ Mạch Tràng' }}
                </strong>
                <span style="font-size: 0.8rem; color: var(--admin-text-muted); display: block; margin-top: 4px;">
                    Chịu trách nhiệm quản lý toàn bộ các gian hàng số & tiểu thương thuộc Chợ.
                </span>
            </div>
        @endif

        <!-- Quick Profile Controls -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1.5px solid var(--admin-border); padding-top: 24px;">
            @if($user->id !== session('user_id'))
                <!-- Toggle status form -->
                <form action="/admin/users/{{ $user->id }}/toggle-status" method="POST" onsubmit="showCustomConfirm(event, this, '{{ $user->status === 'active' ? 'Vô hiệu hóa tài khoản' : 'Kích hoạt tài khoản' }}', 'Bạn có chắc chắn muốn {{ $user->status === 'active' ? 'vô hiệu hóa' : 'kích hoạt lại' }} tài khoản của người dùng {{ $user->name }}?', false)" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" class="btn-admin" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 0.82rem; background-color: #d97706; color: #ffffff; border: none; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        🔌 {{ $user->status === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                    </button>
                </form>
            @endif

            <!-- Edit redirect button -->
            <a href="/admin/users/{{ $user->id }}/edit" class="btn-admin btn-admin-primary" style="padding: 10px 28px; border-radius: 10px; font-weight: 700; font-size: 0.82rem; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                ✏️ Chỉnh sửa
            </a>

            @if($user->id !== session('user_id'))
                <!-- Delete user form -->
                <form action="/admin/users/{{ $user->id }}" method="POST" onsubmit="showCustomConfirm(event, this, 'Xóa tài khoản', 'Bạn có chắc chắn muốn xóa vĩnh viễn người dùng {{ $user->name }} khỏi hệ thống? Hành động này không thể hoàn tác!', true)" style="display: inline; margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-admin btn-admin-danger" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 0.82rem; border: none; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        🗑️ Xóa
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Custom Premium Confirmation Modal -->
<div id="confirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); z-index: 9999; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease;">
    <div class="glass-panel" style="width: 100%; max-width: 420px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); text-align: center; border: 1px solid rgba(255,255,255,0.15); background: rgba(30, 41, 59, 0.95); color: #ffffff; border-radius: 16px; transform: scale(0.9); transition: transform 0.3s ease;">
        <span id="modalIcon" style="font-size: 3rem; display: block; margin-bottom: 16px;">⚠️</span>
        <h3 id="modalTitle" style="font-size: 1.25rem; font-family: var(--font-heading); margin-bottom: 12px; color: #ffffff; font-weight: 700;">
            Xác nhận yêu cầu
        </h3>
        <p id="modalMessage" style="color: rgba(255,255,255,0.7); font-size: 0.9rem; line-height: 1.5; margin-bottom: 24px; padding: 0 10px;">
            Bạn có chắc chắn muốn thay đổi trạng thái của tài khoản này?
        </p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button id="cancelModalBtn" style="padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); background: transparent; color: #ffffff; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                Hủy bỏ
            </button>
            <button id="confirmModalBtn" style="padding: 10px 24px; border-radius: 8px; border: none; color: #ffffff; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                Đồng ý
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('confirmModal');
    const modalContent = modal.querySelector('.glass-panel');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalIcon = document.getElementById('modalIcon');
    const confirmBtn = document.getElementById('confirmModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');
    
    let currentForm = null;

    window.showCustomConfirm = function(event, form, title, message, isDanger = false) {
        event.preventDefault(); // Chặn gửi form ngay
        currentForm = form;
        
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        
        // Cấu hình màu sắc nút Đồng ý theo loại hành động
        if (isDanger) {
            modalIcon.textContent = '🗑️';
            confirmBtn.style.background = '#ef4444';
            confirmBtn.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.3)';
            confirmBtn.onmouseover = () => confirmBtn.style.background = '#dc2626';
            confirmBtn.onmouseout = () => confirmBtn.style.background = '#ef4444';
        } else {
            modalIcon.textContent = '🔌';
            confirmBtn.style.background = '#d97706'; 
            confirmBtn.style.boxShadow = '0 4px 12px rgba(217, 119, 6, 0.3)';
            confirmBtn.onmouseover = () => confirmBtn.style.background = '#b45309';
            confirmBtn.onmouseout = () => confirmBtn.style.background = '#d97706';
        }
        
        // Hiển thị modal với hiệu ứng mượt mà
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modalContent.style.transform = 'scale(1)';
        }, 10);
    };

    function closeModal() {
        modal.style.opacity = '0';
        modalContent.style.transform = 'scale(0.9)';
        setTimeout(() => {
            modal.style.display = 'none';
            currentForm = null;
        }, 300);
    }

    cancelBtn.addEventListener('click', closeModal);
    
    // Đóng khi click ra ngoài vùng modal
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    confirmBtn.addEventListener('click', function() {
        if (currentForm) {
            currentForm.submit();
        }
    });
});
</script>
@endsection
