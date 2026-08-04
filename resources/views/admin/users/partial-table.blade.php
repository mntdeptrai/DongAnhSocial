<div class="admin-table-container">
    <table class="admin-data-table">
        <thead>
            <tr>
                <th style="width: 70px; text-align: center;">Avatar</th>
                <th>Họ và Tên</th>
                <th>Email</th>
                <th>Số Điện Thoại</th>
                <th>Vai Trò</th>
                <th style="text-align: center;">Trạng Thái</th>
                <th style="text-align: center; width: 160px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @if($users->count() > 0)
                @foreach($users as $user)
                    <tr>
                        <td style="text-align: center;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; border: 1.5px solid var(--admin-border);">
                                {{ $user->avatar ?: '🧑' }}
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--admin-text-main); font-size: 0.9rem; display: block;">{{ $user->name }}</strong>
                            @if($user->role === 'seller')
                                @php 
                                    $stall = $user->getStall(); 
                                    $ownedEateries = $user->getOwnedEateries();
                                    $routeBusinesses = $user->getRouteBusinesses();
                                @endphp
                                @if($stall)
                                    <div style="margin-top: 4px;">
                                        <span style="font-size: 0.78rem; color: #0284c7; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; background: #e0f2fe; padding: 2px 8px; border-radius: 6px; border: 1px solid #bae6fd;">
                                            🛒 {{ $stall->stall_name }}
                                        </span>
                                    </div>
                                @elseif(count($ownedEateries) > 0)
                                    <div style="margin-top: 6px; display: flex; flex-direction: column; gap: 4px;">
                                        @foreach($ownedEateries as $oe)
                                            <span style="font-size: 0.76rem; color: #4f46e5; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;" title="Phân loại: {{ $oe['type'] }}">
                                                🏢 {{ $oe['name'] }}
                                                <small style="color: #6b7280; font-weight: normal; background: #f3f4f6; padding: 1px 6px; border-radius: 10px; font-size: 0.65rem;">
                                                    {{ $oe['type'] }}
                                                </small>
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif($routeBusinesses && $routeBusinesses->count() > 0)
                                    <div style="margin-top: 6px; display: flex; flex-direction: column; gap: 4px;">
                                        @foreach($routeBusinesses as $rb)
                                            <span style="font-size: 0.76rem; color: #059669; display: inline-flex; align-items: center; gap: 4px; font-weight: 700;" title="Tuyến: {{ $rb->village_name }}">
                                                🛣️ {{ $rb->name }}
                                                <small style="color: #047857; font-weight: 700; background: #ecfdf5; padding: 1px 6px; border-radius: 10px; font-size: 0.65rem; border: 1px solid #a7f3d0;">
                                                    Tuyến 4.0 ({{ $rb->village_name }})
                                                </small>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="font-size: 0.72rem; color: #ef4444; display: block; margin-top: 4px; font-style: italic;">
                                        ⚠️ Chưa gán gian hàng
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 0.84rem; color: var(--admin-text-main);">{{ $user->email }}</span>
                        </td>
                        <td>
                            <span style="font-size: 0.84rem; color: var(--admin-text-main);">{{ $user->phone ?: 'Chưa cập nhật' }}</span>
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="admin-badge admin-badge-primary" style="font-size: 0.72rem; font-weight: 700; background-color: #f3e8ff; color: #7e22ce; border-color: rgba(126, 34, 206, 0.15);">Admin</span>
                            @elseif($user->role === 'seller')
                                <span class="admin-badge admin-badge-primary" style="font-size: 0.72rem; font-weight: 700; background-color: #ecfdf5; color: #047857; border-color: rgba(4, 120, 87, 0.15);">Seller</span>
                            @else
                                <span class="admin-badge admin-badge-primary" style="font-size: 0.72rem; font-weight: 700; background-color: #eff6ff; color: #1d4ed8; border-color: rgba(29, 78, 216, 0.15);">Customer</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($user->status === 'active')
                                <span class="admin-badge admin-badge-success" style="font-size: 0.72rem; font-weight: 700;">Hoạt động</span>
                            @else
                                <span class="admin-badge" style="font-size: 0.72rem; font-weight: 700; background-color: #f1f5f9; color: #64748b; border: 1px solid rgba(100, 116, 139, 0.15);">Vô hiệu hóa</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                <!-- View details button -->
                                <a href="/admin/users/{{ $user->id }}" class="btn-admin btn-admin-accent" style="padding: 6px 10px; font-size: 0.72rem; font-weight: 700; border-radius: 6px;" title="Xem chi tiết">
                                    👁️
                                </a>

                                <!-- Edit button -->
                                <a href="/admin/users/{{ $user->id }}/edit" class="btn-admin btn-admin-primary" style="padding: 6px 10px; font-size: 0.72rem; font-weight: 700; border-radius: 6px;" title="Chỉnh sửa">
                                    ✏️
                                </a>

                                <!-- Delete button -->
                                @if($user->id !== session('user_id'))
                                    <form action="/admin/users/{{ $user->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn người dùng này khỏi hệ thống?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.72rem; font-weight: 700; border-radius: 6px;" title="Xóa tài khoản">
                                            🗑️
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px 0; color: var(--admin-text-muted); font-style: italic;">
                        🔍 Không tìm thấy tài khoản người dùng nào khớp với bộ lọc.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Premium Pagination links -->
@if($users->hasPages())
    <div style="margin-top: 20px; display: flex; justify-content: center;">
        <div class="pagination">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endif
