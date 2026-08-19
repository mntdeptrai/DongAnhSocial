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
                            @php
                                $isImg = !empty($user->avatar) && (str_starts_with($user->avatar, 'http') || str_contains($user->avatar, '/') || preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $user->avatar));
                                $avatarSrc = $isImg ? ($user->avatar_url ?: (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')))) : null;
                            @endphp
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; border: 1.5px solid var(--admin-border); overflow: hidden; margin: 0 auto;">
                                @if($isImg && $avatarSrc)
                                    <img src="{{ $avatarSrc }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='🧑'">
                                @else
                                    {{ $user->avatar ?: '🧑' }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--admin-text-main); font-size: 0.9rem; display: inline-flex; align-items: center; gap: 4px;">
                                {{ $user->name }}
                                @if($user->role === 'admin')
                                    <span title="Tài khoản Quản trị viên (Admin)" style="color: #ef4444; font-size: 0.95rem;">⭐</span>
                                @endif
                            </strong>
                            @if($user->role === 'seller')
                                @php 
                                    $stall = $user->getStall(); 
                                    $marketId = ($stall && !empty($stall->eatery_id)) ? $stall->eatery_id : $user->eatery_id;
                                    $marketName = (isset($marketsMap) && $marketId && isset($marketsMap[$marketId])) ? $marketsMap[$marketId]->name : null;
                                    $rawOwnedEateries = $user->getOwnedEateries();
                                    $ownedEateries = collect($rawOwnedEateries)->reject(function($oe) use ($marketId, $marketName) {
                                        return !empty($marketName) && (($marketId && isset($oe['id']) && $oe['id'] == $marketId) || (isset($oe['name']) && $oe['name'] === $marketName));
                                    })->values();
                                    $routeBusinesses = $user->getRouteBusinesses();
                                @endphp
                                <div style="margin-top: 4px; display: flex; flex-direction: column; gap: 4px;">
                                    {{-- 1. Chợ trực thuộc & Gian hàng số --}}
                                    @if(!empty($marketName) || $stall)
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">
                                            @if(!empty($marketName))
                                                <span style="font-size: 0.74rem; color: #92400e; font-weight: 700; background: #fef3c7; padding: 2px 8px; border-radius: 6px; border: 1px solid #fde68a;" title="Chợ trực thuộc">
                                                    🏛️ {{ $marketName }}
                                                </span>
                                            @endif
                                            @if($stall)
                                                <span style="font-size: 0.74rem; color: #0284c7; font-weight: 700; background: #e0f2fe; padding: 2px 8px; border-radius: 6px; border: 1px solid #bae6fd;" title="Gian hàng trong chợ">
                                                    🛒 {{ $stall->stall_name }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- 2. Tuyến đường 4.0 trực thuộc --}}
                                    @if($routeBusinesses && $routeBusinesses->count() > 0)
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">
                                            @foreach($routeBusinesses as $rb)
                                                <span style="font-size: 0.74rem; color: #065f46; font-weight: 700; background: #ecfdf5; padding: 2px 8px; border-radius: 6px; border: 1px solid #a7f3d0;" title="Thuộc Tuyến đường 4.0 {{ $rb->village_name }}">
                                                    🛣️ Tuyến 4.0 ({{ $rb->village_name }}): {{ $rb->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- 3. Cơ sở kinh doanh / Doanh nghiệp độc lập --}}
                                    @if(count($ownedEateries) > 0)
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">
                                            @foreach($ownedEateries as $oe)
                                                <span style="font-size: 0.74rem; color: #4f46e5; font-weight: 700; background: #eef2ff; padding: 2px 8px; border-radius: 6px; border: 1px solid #c7d2fe;" title="Phân loại: {{ $oe['type'] }}">
                                                    🏢 {{ $oe['name'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- 4. Nếu chưa gán bất kỳ đâu --}}
                                    @if(empty($marketName) && !$stall && (!$routeBusinesses || $routeBusinesses->count() === 0) && count($ownedEateries) === 0)
                                        <span style="font-size: 0.72rem; color: #ef4444; font-style: italic;">
                                            ⚠️ Chưa gán gian hàng
                                        </span>
                                    @endif
                                </div>
                            @elseif($user->role === 'manager')
                                @php
                                    $marketName = (isset($marketsMap) && $user->eatery_id && isset($marketsMap[$user->eatery_id])) ? $marketsMap[$user->eatery_id]->name : null;
                                @endphp
                                @if($marketName)
                                    <div style="margin-top: 4px;">
                                        <span style="font-size: 0.74rem; color: #92400e; font-weight: 700; background: #fef3c7; padding: 2px 8px; border-radius: 6px; border: 1px solid #fde68a;" title="Ban Quản Lý Chợ">
                                            🏛️ Phụ trách quản lý: {{ $marketName }}
                                        </span>
                                    </div>
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
                            @elseif($user->role === 'principal')
                                <span class="admin-badge admin-badge-primary" style="font-size: 0.72rem; font-weight: 700; background-color: #e0e7ff; color: #4338ca; border-color: rgba(67, 56, 202, 0.15);">Principal 🏫</span>
                            @elseif($user->role === 'manager')
                                <span class="admin-badge admin-badge-primary" style="font-size: 0.72rem; font-weight: 700; background-color: #fef3c7; color: #b45309; border-color: rgba(180, 83, 9, 0.15);">Manager 🏛️</span>
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
