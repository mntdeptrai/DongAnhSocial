<!-- ==========================================================
     1. TOP HERO HEADER & COVER PHOTO CARD
     ========================================================== -->
<div class="pro-header-card">
    <!-- Cover Image -->
    <div class="pro-cover-box" style="background: #ffffff; min-height: 220px;">
        @php
            $coverSrc = $user->cover_url ?: (optional($school)->image_path ?: null);
        @endphp
        @if($coverSrc)
            <img src="{{ $coverSrc }}" class="pro-cover-img" id="pro-cover-img-el" alt="Cover" style="cursor: pointer;" onclick="openSingleImageLightbox(this.src, '🖼️ Ảnh bìa - {{ addslashes(optional($school)->standardized_name ?: $user->name) }}')">
        @else
            <div style="width: 100%; height: 100%; min-height: 220px; background: #ffffff;"></div>
        @endif
        @if($isOwner)
            <label class="pro-cover-btn" style="cursor: pointer;" onclick="event.stopPropagation();">
                📷 Đổi ảnh bìa
                <input type="file" id="cover-file-input" accept="image/*" style="display: none;" onchange="handleCoverSelect(this)">
            </label>
        @endif
    </div>

    <!-- Profile Info Bar -->
    <div class="pro-info-bar">
        <div class="pro-info-main">
            <!-- Left: Avatar & Text Details -->
            <div class="pro-left-details">
                <div class="pro-avatar-box">
                    @php
                        $avatarSrc = $user->avatar_url ?: optional($school)->image_path;
                    @endphp
                    @if($avatarSrc)
                        <img src="{{ $avatarSrc }}" class="pro-avatar-img" id="pro-avatar-img-el" alt="{{ $user->name }}" style="cursor: pointer;" onclick="openSingleImageLightbox(this.src, '🧑 Ảnh đại diện - {{ addslashes(optional($school)->standardized_name ?: $user->name) }}')" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'User') }}&background=0ea5e9&color=fff';">
                    @else
                        <div style="width:100%;height:100%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:3rem;border-radius:20px;">👤</div>
                    @endif
                    @if($isOwner)
                        <label class="pro-avatar-badge" title="Đổi ảnh đại diện" style="cursor: pointer;" onclick="event.stopPropagation();">
                            📷
                            <input type="file" id="avatar-file-input" accept="image/*" style="display: none;" onchange="handleAvatarSelect(this)">
                        </label>
                    @endif
                </div>

                <div>
                    <h1 class="pro-name-title" style="display: inline-flex; align-items: center; gap: 6px;">
                        {{ optional($school)->standardized_name ?: $user->name }}
                        @if($user->isAdmin() || $user->role === 'admin')
                            <span class="pro-admin-star" title="Tài khoản Quản trị viên (Admin)" style="color: #ef4444; font-size: 1.3rem; line-height: 1; filter: drop-shadow(0 0 6px rgba(239, 68, 68, 0.4));">⭐</span>
                        @elseif($user->is_verified)
                            <span class="pro-verify-badge" title="Tài khoản xịn đã xác minh bởi Admin ⭐">⭐</span>
                        @endif
                    </h1>
                    <div class="pro-subtitle">
                        @if($user->isAdmin() || $user->role === 'admin')
                            🏛️ Quản trị viên hệ thống DongAnh Social ⭐
                        @elseif($user->isSeller() || $user->role === 'seller')
                            🛍️ {{ $stall ? 'Chủ gian hàng ' . $stall->stall_name : 'Kinh doanh & Tuyến 4.0 Đông Anh' }}
                        @elseif($user->role === 'manager')
                            🏛️ Ban Quản lý Chợ / Cơ sở
                        @elseif($user->isPrincipal() || $user->role === 'principal')
                            🏫 {{ optional(optional($school)->category)->name ?: 'Cơ sở giáo dục' }}{{ optional($school)->commune ? ' · ' . optional($school)->commune->name : '' }}
                        @else
                            👤 Thành viên cộng đồng · Khám phá & Trải nghiệm Đông Anh
                        @endif
                    </div>

                    <!-- Counter Stats (Direct Database Values) -->
                    <div class="pro-stats-row">
                        <div class="pro-stat-item">
                            <span class="pro-stat-num">{{ $followersCount }}</span>
                            <span class="pro-stat-label">Người theo dõi</span>
                        </div>
                        <div class="pro-stat-item">
                            <span class="pro-stat-num">{{ $followingCount }}</span>
                            <span class="pro-stat-label">Đang theo dõi</span>
                        </div>
                        @if($school || (($user->isSeller() || $user->role === 'seller') && !empty($stall)))
                        <div class="pro-stat-item">
                            <span class="pro-stat-num">{{ number_format($avgScore, 1) }}</span>
                            <span class="pro-stat-label">Đánh giá</span>
                        </div>
                        @endif
                        <div class="pro-stat-item">
                            <span class="pro-stat-num">{{ $posts->count() }}</span>
                            <span class="pro-stat-label">Bài viết</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons Group (Phân biệt rõ Chủ sở hữu & Người xem) -->
            <div class="pro-actions-group">
                @if($isOwner)
                    <!-- Dành riêng cho Chính Chủ Tài Khoản (Owner View) -->
                    <button type="button" @click="showEditModal = true" class="pro-btn-primary">
                        ✏️ Chỉnh sửa hồ sơ
                    </button>

                    <button type="button" @click="showPasswordModal = true" class="pro-btn-outline">
                        🔑 Đổi mật khẩu
                    </button>

                    <button type="button" onclick="shareProfilePage()" class="pro-btn-outline">
                        📤 Chia sẻ
                    </button>
                @else
                    <!-- Dành cho Khách & Người xem khác ghé thăm (Viewer / Guest View) -->
                    <!-- 1. Nút Thêm bạn bè (Friendship Button) -->
                    <div id="friend-btn-wrapper" style="display: inline-flex;">
                        @if($friendshipStatus === 'none')
                            <button type="button" class="pro-btn-primary" id="friend-btn" onclick="sendFriendRequest({{ $user->id }})">
                                ➕ Thêm bạn bè
                            </button>
                        @elseif($friendshipStatus === 'pending_sent')
                            <button type="button" class="pro-btn-outline" id="friend-btn" onclick="cancelFriendRequest({{ $friendshipId }}, {{ $user->id }})">
                                ⏳ Đã gửi lời mời
                            </button>
                        @elseif($friendshipStatus === 'pending_received')
                            <button type="button" class="pro-btn-primary" id="friend-btn" style="background: #059669; color: #ffffff;" onclick="acceptFriendRequest({{ $friendshipId }})">
                                ✅ Chấp nhận lời mời
                            </button>
                        @elseif($friendshipStatus === 'accepted')
                            <button type="button" class="pro-btn-outline" id="friend-btn" style="background: #ecfdf5; border-color: #10b981; color: #047857;" onclick="unfriendUser({{ $friendshipId }}, {{ $user->id }})">
                                👥 Bạn bè ✓
                            </button>
                        @endif
                    </div>

                    <!-- 2. Nút Theo dõi (Follow Button) -->
                    <button type="button" 
                            class="{{ $isFollowing ? 'pro-btn-outline' : 'pro-btn-primary' }}" 
                            id="follow-btn" 
                            data-following="{{ $isFollowing ? 'true' : 'false' }}"
                            style="{{ $isFollowing ? 'background: #f1f5f9; color: #475569;' : '' }}"
                            onclick="toggleFollowUser(this, {{ $user->id }})">
                        <span id="follow-icon">{{ $isFollowing ? '✓' : '🔔' }}</span> 
                        <span id="follow-text">{{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}</span>
                    </button>

                    <!-- 3. Nút Thả tim (nếu là trang Trường học / Cơ sở) -->
                    @if($school)
                        @php
                            $uId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
                            $sId = session()->getId();
                            $eateryLiked = \App\Models\CheckinReaction::where('reactionable_type', 'eatery')
                                ->where('reactionable_id', $school->id)
                                ->where(function($q) use ($uId, $sId) {
                                    if ($uId) { $q->where('user_id', $uId); } else { $q->where('session_id', $sId); }
                                })->exists();

                            $eateryLikesCount = \App\Models\CheckinReaction::where('reactionable_type', 'eatery')
                                ->where('reactionable_id', $school->id)
                                ->count();
                        @endphp
                        <button type="button" class="pro-btn-primary" 
                                id="eatery-heart-btn-{{ $school->id }}" 
                                onclick="togglePlaceHeart(this, {{ $school->id }})" 
                                style="{{ $eateryLiked ? 'background: #ef4444 !important; color: #ffffff !important;' : 'background: #f1f5f9; color: #0f172a;' }}">
                            ❤️ <span id="eatery-heart-text-{{ $school->id }}">{{ $eateryLiked ? 'Đã thả tim' : 'Thả tim' }} ({{ $eateryLikesCount }})</span>
                        </button>
                    @endif

                    <!-- 4. Nút Nhắn tin -->
                    <button type="button" class="pro-btn-outline" 
                            onclick="openDirectMessage({{ $user->id }}, '{{ addslashes(optional($school)->standardized_name ?: $user->name) }}', '{{ optional($school)->image_path ?: '' }}')">
                        💬 Nhắn tin
                    </button>

                    <!-- 5. Nút Gọi điện WebRTC P2P -->
                    <button type="button" class="pro-btn-outline" 
                            onclick="DongAnhWebRTC.startCall({{ $user->id }}, '{{ addslashes(optional($school)->standardized_name ?: $user->name) }}', '{{ optional($school)->image_path ?: '' }}', 'audio')">
                        📞 Gọi điện
                    </button>

                    <!-- 6. Nút Gọi Video WebRTC P2P -->
                    <button type="button" class="pro-btn-outline" 
                            onclick="DongAnhWebRTC.startCall({{ $user->id }}, '{{ addslashes(optional($school)->standardized_name ?: $user->name) }}', '{{ optional($school)->image_path ?: '' }}', 'video')">
                        📹 Video Call
                    </button>

                    <!-- 7. Nút Chia sẻ -->
                    <button type="button" class="pro-btn-outline" 
                            onclick="shareProfilePage()">
                        📤 Chia sẻ
                    </button>
                @endif
            </div>
        </div>

        <!-- Tab Navigation Bar -->
        <div class="pro-tabs-bar">
            <button type="button" class="pro-tab-item" :class="{ 'active': activeTab === 'overview' }" @click="activeTab = 'overview'">Tổng quan</button>
            <button type="button" class="pro-tab-item" :class="{ 'active': activeTab === 'posts' }" @click="activeTab = 'posts'">Bài viết ({{ $posts->count() }})</button>
            <button type="button" class="pro-tab-item" :class="{ 'active': activeTab === 'photos' }" @click="activeTab = 'photos'">Thư viện ảnh ({{ $allPhotoUrls->count() }})</button>
            <button type="button" class="pro-tab-item" :class="{ 'active': activeTab === 'about' }" @click="activeTab = 'about'">Giới thiệu</button>
            @if(($user->isPrincipal() || $user->role === 'principal') || (($user->isSeller() || $user->role === 'seller') && !empty($stall)))
            <button type="button" class="pro-tab-item" :class="{ 'active': activeTab === 'reviews' }" @click="activeTab = 'reviews'">Đánh giá ({{ $totalRev }})</button>
            @endif
        </div>
    </div>
</div>
