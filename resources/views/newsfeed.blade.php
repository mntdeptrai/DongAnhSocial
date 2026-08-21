@extends('layouts.app')

@section('title', 'Bản tin - DongAnh Map Discovery')
@section('meta_description', 'Bản tin cập nhật các bài viết mới nhất từ các Trường học, Profile cá nhân và Gian hàng Đông Anh.')

@section('content')
<!-- Ambient Glowing Orbs -->
<div style="position: fixed; top: 10%; left: -10%; width: 550px; height: 550px; background: radial-gradient(circle, rgba(14, 165, 233, 0.07) 0%, rgba(14, 165, 233, 0) 70%); filter: blur(120px); pointer-events: none; z-index: 1;"></div>
<div style="position: fixed; bottom: 10%; right: -10%; width: 550px; height: 550px; background: radial-gradient(circle, rgba(16, 185, 129, 0.07) 0%, rgba(16, 185, 129, 0) 70%); filter: blur(120px); pointer-events: none; z-index: 1;"></div>

<style>
/* Modern 2-Column Responsive Grid System */
.newsfeed-wrapper {
    max-width: 980px;
    margin: 24px auto 48px auto;
    padding: 0 16px;
    display: grid;
    grid-template-columns: 250px 680px;
    gap: 24px;
    justify-content: center;
    align-items: start;
    position: relative;
    z-index: 2;
    box-sizing: border-box;
}

@media (max-width: 768px) {
    body {
        overflow-x: hidden !important;
    }
    .newsfeed-wrapper {
        grid-template-columns: 1fr !important;
        padding: 0 8px !important;
        margin-top: 8px !important;
        margin-bottom: 24px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }
    .comment-reply-item {
        margin-left: 16px !important;
        padding: 8px 10px !important;
    }
    .nf-main-stream {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        min-width: 0 !important;
    }
    .nf-left-sidebar {
        display: none !important;
    }
    .nf-stories-container {
        gap: 8px;
        margin-bottom: 14px;
        padding-bottom: 4px;
        width: 100%;
        box-sizing: border-box;
    }
    .nf-story-card {
        min-width: 96px;
        height: 145px;
        border-radius: 14px;
    }
    .nf-story-avatar {
        width: 32px;
        height: 32px;
    }
    .nf-story-name {
        font-size: 0.72rem;
    }
    .nf-filter-pills {
        gap: 6px;
        margin-bottom: 14px;
        width: 100%;
        box-sizing: border-box;
    }
    .nf-filter-pill {
        padding: 6px 14px;
        font-size: 0.78rem;
    }
    .nf-post-card {
        border-radius: 14px !important;
        margin-bottom: 14px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .nf-author-box {
        padding: 12px 14px 10px 14px;
    }
}

/* Mobile Explore Menu Bar (Only visible on Mobile) */
.mobile-explore-menu-bar {
    display: none;
}

@media (max-width: 768px) {
    .mobile-explore-menu-bar {
        display: block;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 16px;
        padding: 12px 14px;
        margin-bottom: 14px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        width: 100%;
        box-sizing: border-box;
    }
    .mobile-explore-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }
    .mobile-explore-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 10px 4px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        color: #475569;
        text-decoration: none;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
        font-family: inherit;
    }
    .mobile-explore-item:active, .mobile-explore-item.active {
        background: #e0f2fe;
        border-color: #0ea5e9;
        color: #0284c7;
    }
    .mobile-explore-icon {
        font-size: 1.25rem;
        margin-bottom: 4px;
    }
    .mobile-explore-label {
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .mobile-explore-badge {
        position: absolute;
        top: -6px;
        right: -2px;
        font-size: 0.55rem;
        background: #fef3c7;
        color: #d97706;
        padding: 1px 4px;
        border-radius: 8px;
        font-weight: 800;
        border: 1px solid #fde68a;
    }
}

/* Glassmorphic Widget Cards */
.nf-widget {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
    padding: 18px;
    margin-bottom: 20px;
}
.nf-widget-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 14px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Stories / Highlights Carousel */
.nf-stories-container {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 20px;
    scrollbar-width: none;
}
.nf-stories-container::-webkit-scrollbar {
    display: none;
}
.nf-story-card {
    min-width: 110px;
    height: 165px;
    border-radius: 18px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    background-size: cover;
    background-position: center;
}
.nf-story-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 10px 25px rgba(14,165,233,0.3);
}
.nf-story-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.75) 100%);
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.nf-story-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2.5px solid #00000000;
    background-image: linear-gradient(#0f172a, #0f172a), linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
    background-origin: border-box;
    background-clip: content-box, border-box;
    object-fit: cover;
    box-shadow: 0 2px 10px rgba(0,0,0,0.4);
}
.nf-story-name {
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 800;
    margin: 0;
    line-height: 1.25;
    text-shadow: 0 1px 4px rgba(0,0,0,0.8);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Post Cards */
.nf-post-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    margin-bottom: 22px;
    transition: all 0.35s ease;
}
.nf-post-card:hover {
    box-shadow: 0 12px 32px -4px rgba(14, 165, 233, 0.12);
    border-color: #cbd5e1;
}

/* Author Header Line Fixes */
.nf-author-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    min-width: 0;
}
.nf-author-info-link {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
    min-width: 0;
    flex: 1;
}
.nf-author-avatar-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid #e0f2fe;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
.nf-author-name-text {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 800;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Filter Pills Bar */
.nf-filter-pills {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    margin-bottom: 18px;
    padding-bottom: 4px;
}
.nf-filter-pill {
    padding: 8px 18px;
    border-radius: 100px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.nf-filter-pill:hover, .nf-filter-pill.active {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 4px 14px rgba(14, 165, 233, 0.35);
}

/* Post Action Buttons */
.nf-action-btn {
    border: none;
    background: transparent;
    padding: 9px 16px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    border-radius: 10px;
    transition: all 0.2s ease;
}
.nf-action-btn:hover {
    background: #f1f5f9;
    color: #0284c7;
}
.nf-action-btn.active {
    color: #0284c7;
    background: #e0f2fe;
}
</style>

<div class="newsfeed-wrapper">

    <!-- LEFT SIDEBAR -->
    <aside class="nf-left-sidebar" style="position: sticky; top: 90px;">
        
        <!-- User Profile Card Widget -->
        @if(auth()->check())
            @php $authUser = auth()->user(); @endphp
            <div class="nf-widget" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    @if($authUser->avatar_url)
                        <img src="{{ $authUser->avatar_url }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2.5px solid #0ea5e9; flex-shrink: 0;" alt="Avatar">
                    @else
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
                            {{ mb_substr($authUser->name, 0, 1, 'UTF-8') }}
                        </div>
                    @endif
                    <div style="min-width: 0; flex: 1;">
                        <h4 style="margin: 0; font-size: 0.96rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $authUser->name }}</h4>
                        <span style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 12px; font-weight: 700; display: inline-block; margin-top: 2px;">
                            {{ $authUser->role === 'admin' ? '⭐ Quản trị viên' : ($authUser->role === 'principal' ? '🏫 Nhà trường' : 'Thành viên') }}
                        </span>
                    </div>
                </div>
                <a href="/profile/{{ \Illuminate\Support\Str::slug($authUser->name) }}" style="display: block; width: 100%; text-align: center; background: #f1f5f9; color: #334155; font-size: 0.82rem; font-weight: 700; padding: 8px 0; border-radius: 10px; text-decoration: none; transition: background 0.2s ease;">
                    Trang cá nhân của tôi 👤
                </a>
            </div>
        @endif

        <!-- Quick Navigation Menu -->
        <div class="nf-widget">
            <div class="nf-widget-title">🧭 Menu Khám Phá</div>
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <button type="button" class="nf-sidebar-nav-item active" data-type="all" onclick="filterNewsfeedPosts('all', document.getElementById('pill-all'))" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 12px; background: #e0f2fe; color: #0284c7; font-weight: 800; font-size: 0.88rem; border: none; cursor: pointer; text-align: left; transition: all 0.2s ease;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <span>📰</span> Bản tin cộng đồng
                    </span>
                </button>

                <button type="button" class="nf-sidebar-nav-item" data-type="food_tour" onclick="filterNewsfeedPosts('food_tour', document.getElementById('pill-food-tour'))" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 12px; background: transparent; color: #475569; font-weight: 700; font-size: 0.88rem; border: none; cursor: pointer; text-align: left; transition: all 0.2s ease;" onmouseover="if(!this.classList.contains('active')) this.style.background='#f8fafc'" onmouseout="if(!this.classList.contains('active')) this.style.background='transparent'">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <span>🍲</span> Food Tour Đông Anh
                    </span>
                    <span style="font-size: 0.7rem; background: #fef3c7; color: #d97706; padding: 2px 7px; border-radius: 10px; font-weight: 800;">Nhật ký</span>
                </button>

                <a href="/tim-kiem" class="nf-sidebar-nav-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; color: #475569; font-weight: 700; font-size: 0.88rem; text-decoration: none; transition: background 0.2s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <span>🗺️</span> Bản đồ Địa điểm
                </a>

                @if(auth()->check())
                    <a href="/social" class="nf-sidebar-nav-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; color: #475569; font-weight: 700; font-size: 0.88rem; text-decoration: none; transition: background 0.2s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <span>👥</span> Kết nối Bạn bè
                    </a>
                @else
                    <button type="button" onclick="openAuthLoginModal('Kết nối Bạn bè & Nhắn tin Messenger')" class="nf-sidebar-nav-item" style="width: 100%; border: none; font-family: inherit; cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; color: #475569; font-weight: 700; font-size: 0.88rem; background: transparent; transition: background 0.2s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <span>👥</span> Kết nối Bạn bè
                    </button>
                @endif
            </div>
        </div>

    </aside>

    <!-- CENTER MAIN STREAM -->
    <main class="nf-main-stream">
        
        <!-- Story Reels Bar (Facebook / Instagram Style) -->
        <div class="nf-stories-container">
            <!-- Add Story Card -->
            <div class="nf-story-card" style="background-image: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=300&q=80');" onclick="@if(auth()->check()) openCreateStoryModal() @else openAuthLoginModal('đăng tin Story mới') @endif">
                <div class="nf-story-overlay" style="justify-content: flex-end; align-items: center; text-align: center;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #0ea5e9; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 900; margin-bottom: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">+</div>
                    <span class="nf-story-name">Tạo tin mới</span>
                </div>
            </div>

            @php $groupCardIdx = 0; @endphp

            <!-- User-Submitted Active Stories (Grouped per author) -->
            @if(isset($stories) && $stories->isNotEmpty())
                @php
                    $uniqueUserStories = $stories->groupBy(function($item) {
                        return $item->author_name ?? ($item->user ? $item->user->name : 'Thành viên');
                    });
                @endphp
                @foreach($uniqueUserStories as $authorName => $authorStories)
                    @php
                        $latestStory = $authorStories->last();
                        $stBg = $latestStory->media_url ?: null;
                        $stAvatar = $latestStory->author_avatar ?? ($latestStory->user ? $latestStory->user->avatar_url : null);
                        $clickGroupIdx = $groupCardIdx++;
                    @endphp
                    <div class="nf-story-card story-item-card" style="{{ $stBg ? "background-image: url('{$stBg}');" : "background: {$latestStory->bg_gradient};" }}" onclick="openStoryViewer({{ $clickGroupIdx }})">
                        <div class="nf-story-overlay">
                            @if($stAvatar)
                                <img src="{{ $stAvatar }}" class="nf-story-avatar" alt="{{ $authorName }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($authorName) }}&background=0ea5e9&color=fff'">
                            @else
                                <div class="nf-story-avatar" style="background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                    {{ mb_substr($authorName, 0, 1, 'UTF-8') }}
                                </div>
                            @endif
                            <span class="nf-story-name">{{ \Illuminate\Support\Str::limit($authorName, 18) }}</span>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Featured Story 1: Co Loa -->
            <div class="nf-story-card story-item-card" style="background-image: url('https://images.unsplash.com/photo-1596402184320-417e7178b2cd?auto=format&fit=crop&w=300&q=80');" onclick="openStoryViewer({{ $groupCardIdx++ }})">
                <div class="nf-story-overlay">
                    <img src="https://media.xadonganh.com/eateries/1780392421_hfPQH7HB.png" class="nf-story-avatar" alt="Cổ Loa" onerror="this.src='https://ui-avatars.com/api/?name=Co+Loa&background=0ea5e9&color=fff'">
                    <span class="nf-story-name">Lễ hội Cổ Loa ⛩️</span>
                </div>
            </div>

            <!-- Featured Story 2: Truong Mua Mua -->
            <div class="nf-story-card story-item-card" style="background-image: url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=300&q=80');" onclick="openStoryViewer({{ $groupCardIdx++ }})">
                <div class="nf-story-overlay">
                    <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80" class="nf-story-avatar" alt="Trường học" onerror="this.src='https://ui-avatars.com/api/?name=School&background=10b981&color=fff'">
                    <span class="nf-story-name">Chào năm học mới 🏫</span>
                </div>
            </div>

            <!-- Featured Story 3: Food Tour -->
            <div class="nf-story-card story-item-card" style="background-image: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=300&q=80');" onclick="openStoryViewer({{ $groupCardIdx++ }})">
                <div class="nf-story-overlay">
                    <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=150&q=80" class="nf-story-avatar" alt="Food Tour" onerror="this.src='https://ui-avatars.com/api/?name=Food&background=f59e0b&color=fff'">
                    <span class="nf-story-name">Ẩm thực Đông Anh 🍲</span>
                </div>
            </div>

            <!-- Featured Story 4: Checkin -->
            <div class="nf-story-card story-item-card" style="background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=300&q=80');" onclick="openStoryViewer({{ $groupCardIdx++ }})">
                <div class="nf-story-overlay">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=150&q=80" class="nf-story-avatar" alt="Checkin" onerror="this.src='https://ui-avatars.com/api/?name=Checkin&background=ec4899&color=fff'">
                    <span class="nf-story-name">Góc Check-in Hot 🎈</span>
                </div>
            </div>
        </div>

        <!-- Mobile Explore Menu Bar (Displayed only on Mobile Screens) -->
        <div class="mobile-explore-menu-bar">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; padding: 0 2px;">
                <span style="font-size: 0.85rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 1rem;">🧭</span> Menu Khám Phá
                </span>
            </div>
            <div class="mobile-explore-grid">
                <button type="button" class="mobile-explore-item active" onclick="filterNewsfeedPosts('all', this)">
                    <span class="mobile-explore-icon">📰</span>
                    <span class="mobile-explore-label">Bản tin</span>
                </button>
                <button type="button" class="mobile-explore-item" onclick="filterNewsfeedPosts('food_tour', this)">
                    <span class="mobile-explore-icon">🍲</span>
                    <span class="mobile-explore-label">Food Tour</span>
                    <span class="mobile-explore-badge">Nhật ký</span>
                </button>
                <a href="/tim-kiem" class="mobile-explore-item">
                    <span class="mobile-explore-icon">🗺️</span>
                    <span class="mobile-explore-label">Bản đồ</span>
                </a>
                @if(auth()->check())
                    <a href="/social" class="mobile-explore-item">
                        <span class="mobile-explore-icon">👥</span>
                        <span class="mobile-explore-label">Kết nối</span>
                    </a>
                @else
                    <button type="button" onclick="openAuthLoginModal('Kết nối Bạn bè & Nhắn tin Messenger')" class="mobile-explore-item">
                        <span class="mobile-explore-icon">👥</span>
                        <span class="mobile-explore-label">Kết nối</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Filter Pills Bar -->
        <div class="nf-filter-pills">
            <button class="nf-filter-pill active" id="pill-all" onclick="filterNewsfeedPosts('all', this)">🔥 Tất cả bài viết</button>
            <button class="nf-filter-pill" id="pill-food-tour" onclick="filterNewsfeedPosts('food_tour', this)">🍲 Nhật ký Food Tour</button>
            <button class="nf-filter-pill" onclick="filterNewsfeedPosts('school', this)">🏫 Trường học</button>
            <button class="nf-filter-pill" onclick="filterNewsfeedPosts('media', this)">📸 Ảnh & Video</button>
            <button class="nf-filter-pill" onclick="filterNewsfeedPosts('checkin', this)">🎈 Check-in</button>
        </div>

        <!-- Post Creator Box -->
        @if(auth()->check())
            @php $authUser = auth()->user(); @endphp
            <div class="nf-widget" style="padding: 16px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    @if($authUser->avatar_url)
                        <img src="{{ $authUser->avatar_url }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #e0f2fe; flex-shrink: 0;" alt="Avatar">
                    @else
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">
                            {{ mb_substr($authUser->name, 0, 1, 'UTF-8') }}
                        </div>
                    @endif
                    <button type="button" onclick="openNewsfeedPostModal()" style="flex: 1; min-width: 0; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 100px; padding: 12px 18px; color: #64748b; font-size: 0.88rem; font-weight: 500; cursor: pointer; text-align: left; font-family: inherit; transition: all 0.2s ease; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" onmouseover="this.style.borderColor='#0ea5e9'; this.style.background='#ffffff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#f1f5f9';">
                        {{ $authUser->name }} ơi, bạn đang nghĩ gì thế?
                    </button>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-around; border-top: 1px solid #f1f5f9; padding-top: 12px; flex-wrap: wrap; gap: 6px;">
                    <button type="button" onclick="openNewsfeedPostModal()" style="background: transparent; border: none; color: #475569; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-family: inherit; transition: background 0.2s ease;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='transparent'">
                        <span style="font-size: 1.1rem;">🖼️</span> Ảnh & Video
                    </button>
                    <button type="button" onclick="openNewsfeedPostModal()" style="background: transparent; border: none; color: #475569; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-family: inherit; transition: background 0.2s ease;" onmouseover="this.style.background='#fef3c7'" onmouseout="this.style.background='transparent'">
                        <span style="font-size: 1.1rem;">😊</span> Cảm xúc
                    </button>
                    <button type="button" onclick="openNewsfeedPostModal()" style="background: transparent; border: none; color: #475569; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-family: inherit; transition: background 0.2s ease;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">
                        <span style="font-size: 1.1rem;">📍</span> Check-in
                    </button>
                    <button type="button" onclick="openNewsfeedPostModal()" style="background: transparent; border: none; color: #475569; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-family: inherit; transition: background 0.2s ease;" onmouseover="this.style.background='#f3e8ff'" onmouseout="this.style.background='transparent'">
                        <span style="font-size: 1.1rem;">📅</span> Sự kiện
                    </button>
                </div>
            </div>
        @else
            <div class="nf-widget" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; min-width: 0; overflow: hidden;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">👤</div>
                <a href="/auth/login" style="flex: 1; min-width: 0; display: block; background: #f1f5f9; border-radius: 100px; padding: 12px 18px; color: #64748b; font-size: 0.88rem; text-decoration: none; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Đăng nhập để chia sẻ thông tin lên Bản tin...</a>
            </div>
        @endif

        <!-- Flash Success Message -->
        @if(session('success'))
            <div style="background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(6,182,212,0.08)); border: 1.5px solid rgba(16,185,129,0.35); border-radius: 16px; padding: 14px 20px; display: flex; align-items: center; gap: 12px; font-weight: 600; color: #059669; font-size: 0.92rem; margin-bottom: 20px;">
                <span style="font-size: 1.3rem;">✅</span>
                {{ session('success') }}
            </div>
        @endif

        <!-- STREAM OF POST CARDS -->
        @if(isset($posts) && $posts->isNotEmpty())
            @foreach($posts as $p)
                @php
                    $authorName = 'Thành viên Đông Anh';
                    $profileUrl = '#';
                    $authorAvatar = null;
                    $isSchoolPost = false;

                    if ($p instanceof \App\Models\EducationProgram) {
                        $authorName = $p->eatery ? ($p->eatery->standardized_name ?: $p->eatery->name) : 'Trường học Đông Anh';
                        $authorSlug = $p->eatery ? $p->eatery->slug : '';
                        $profileUrl = $authorSlug ? "/dia-diem/{$authorSlug}" : '#';
                        $authorAvatar = $p->eatery ? $p->eatery->image_path : null;
                        $isSchoolPost = true;
                    } elseif ($p->user) {
                        $authorName = $p->user->name;
                        $authorSlug = \Illuminate\Support\Str::slug($p->user->name);
                        $profileUrl = "/profile/{$authorSlug}";
                        $authorAvatar = $p->user->avatar ?? null;
                    } elseif ($p->eatery) {
                        $authorName = $p->eatery->name;
                        $authorSlug = $p->eatery->slug;
                        $profileUrl = "/dia-diem/{$authorSlug}";
                        $authorAvatar = $p->eatery->image_path;
                        $isSchoolPost = true;
                    } elseif (!empty($p->display_name)) {
                        $authorName = $p->display_name;
                    }

                    $postUser = $p->user ?? ($p->eatery && $p->eatery->user_id ? \App\Models\User::find($p->eatery->user_id) : ($p->eatery_id ? \App\Models\User::where('eatery_id', $p->eatery_id)->first() : null));
                    $isVerifiedAuthor = ($postUser && ($postUser->is_verified || in_array($postUser->role, ['admin', 'principal', 'seller']))) || ($p instanceof \App\Models\EducationProgram);
                    $isAdminAuthor = $postUser && ($postUser->role === 'admin');

                    $imgs = method_exists($p, 'getAllImagesAttribute') ? $p->all_images : ($p->image_path ? [$p->image_path] : []);
                    $imgCount = count($imgs);
                    $isFoodTour = $p->is_food_tour ?? false;
                    $isCheckin = $p->is_checkin ?? false;
                    $isEdu = $p instanceof \App\Models\EducationProgram;
                    $postTypeAttr = $isFoodTour ? 'food_tour' : ($isCheckin ? 'checkin' : ($isSchoolPost ? 'school' : ($imgCount > 0 ? 'media' : 'all')));
                    $postDomKey = $isFoodTour ? 'foodtour-' . $p->id : ($isCheckin ? 'checkin-' . $p->id : ($isEdu ? 'edu-' . $p->id : 'post-' . $p->id));
                    $commentableClass = get_class($p);
                @endphp

                <article class="nf-post-card post-item-card" data-post-type="{{ $postTypeAttr }}" id="post-card-{{ $postDomKey }}">
                    
                    <!-- Post Author Header (Strict Flex & Truncation - No Broken Lines) -->
                    <div class="nf-author-box">
                        <a href="{{ $profileUrl }}" class="nf-author-info-link">
                            @if(!empty($authorAvatar))
                                <img src="{{ $authorAvatar }}" class="nf-author-avatar-img" alt="{{ $authorName }}" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                <div class="nf-author-avatar-img" style="display: none; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                                    {{ mb_substr($authorName, 0, 1, 'UTF-8') }}
                                </div>
                            @else
                                <div class="nf-author-avatar-img" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                                    {{ mb_substr($authorName, 0, 1, 'UTF-8') }}
                                </div>
                            @endif
                            <div style="min-width: 0; flex: 1;">
                                <h4 class="nf-author-name-text">
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $authorName }}</span>
                                    @if($isAdminAuthor)
                                        <span title="Tài khoản Quản trị viên (Admin)" style="color: #ef4444; font-size: 0.9rem; flex-shrink: 0;">⭐</span>
                                    @elseif($isVerifiedAuthor)
                                        <span title="Tài khoản chính thức đã xác minh ⭐" style="color: #f59e0b; font-size: 0.9rem; flex-shrink: 0;">⭐</span>
                                    @endif
                                </h4>
                                <div style="font-size: 0.76rem; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 6px;">
                                    <span>{{ $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong' }}</span>
                                    <span>•</span>
                                    <span>🌐 Công khai</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Post Content Body -->
                    <div style="padding: 0 16px 14px 16px; word-break: break-word; overflow-wrap: anywhere;">
                        @if($p->name)
                            <strong style="font-size: 1.06rem; line-height: 1.45; color: #0f172a; display: block; margin-bottom: 8px;">🌸 {{ $p->name }}</strong>
                        @endif
                        @if($p->description)
                            @php
                                $rawDesc = $p->description;
                                $isLongDesc = mb_strlen($rawDesc, 'UTF-8') > 220 || substr_count($rawDesc, "\n") > 4;
                            @endphp

                            @if($isLongDesc)
                                @php
                                    $truncatedText = mb_substr($rawDesc, 0, 200, 'UTF-8') . '...';
                                @endphp
                                <div class="post-description-box" style="font-size: 0.92rem; line-height: 1.6; color: #334155;">
                                    <span class="desc-truncated-text" style="white-space: pre-line;">{!! \App\Helpers\TextHelper::linkify($truncatedText) !!}</span>
                                    <span class="desc-full-text" style="display: none; white-space: pre-line;">{!! \App\Helpers\TextHelper::linkify($rawDesc) !!}</span>
                                    <button type="button" class="desc-toggle-btn" onclick="togglePostDescription(this)" style="background: transparent; border: none; color: #0ea5e9; font-weight: 800; font-size: 0.86rem; cursor: pointer; padding: 0; margin-left: 4px; display: inline-block; font-family: inherit; vertical-align: baseline;">
                                        Xem thêm 🔽
                                    </button>
                                </div>
                            @else
                                <div style="font-size: 0.92rem; line-height: 1.65; color: #334155; white-space: pre-line;">{!! \App\Helpers\TextHelper::linkify($rawDesc) !!}</div>
                            @endif
                        @endif
                    </div>

                    <!-- Multi-Photo Grid Gallery (Facebook Full-Bleed Edge-to-Edge) -->
                    @if($imgCount === 1)
                        <div class="fb-photo-grid fb-grid-1" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)">
                            <img src="{{ $imgs[0] }}" alt="{{ $p->name ?? 'Ảnh' }}" style="width: 100%; max-height: 520px; object-fit: cover; cursor: pointer;">
                        </div>
                    @elseif($imgCount === 2)
                        <div class="fb-photo-grid fb-grid-2">
                            <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name ?? 'Ảnh' }}">
                            <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name ?? 'Ảnh' }}">
                        </div>
                    @elseif($imgCount === 3)
                        <div class="fb-photo-grid fb-grid-3">
                            <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name ?? 'Ảnh' }}">
                            <div class="fb-grid-3-col-right">
                                <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name ?? 'Ảnh' }}">
                            </div>
                        </div>
                    @elseif($imgCount === 4)
                        <div class="fb-photo-grid fb-grid-4">
                            <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name ?? 'Ảnh' }}">
                            <div class="fb-grid-4-col-right">
                                <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[3] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 3)" alt="{{ $p->name ?? 'Ảnh' }}">
                            </div>
                        </div>
                    @elseif($imgCount >= 5)
                        <div class="fb-photo-grid fb-grid-5">
                            <div class="fb-grid-5-row-top">
                                <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name ?? 'Ảnh' }}">
                            </div>
                            <div class="fb-grid-5-row-bottom">
                                <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[3] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 3)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <div class="fb-photo-thumb-box" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 4)" style="position: relative !important; width: 100% !important; height: 100% !important; overflow: hidden !important; display: block !important;">
                                    <img src="{{ $imgs[4] }}" alt="{{ $p->name ?? 'Ảnh' }}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                    @if($imgCount > 5)
                                        <div class="fb-photo-more-overlay" style="position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; display: flex !important; align-items: center !important; justify-content: center !important; background: rgba(0, 0, 0, 0.52) !important; backdrop-filter: blur(4px) !important; -webkit-backdrop-filter: blur(4px) !important; z-index: 10 !important; pointer-events: none !important; margin: 0 !important; padding: 0 !important; color: #ffffff !important; font-size: 2.8rem !important; font-weight: 900 !important;">
                                            <span class="fb-photo-more-count" style="color: #ffffff !important; font-size: 2.8rem !important; font-weight: 900 !important; line-height: 1 !important; text-shadow: 0 3px 12px rgba(0,0,0,0.8) !important; display: inline-block !important; margin: 0 !important; padding: 0 !important; text-align: center !important; transform: none !important;">+{{ $imgCount - 5 }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Video Player Section -->
                    @php
                        $vids = method_exists($p, 'getAllVideosAttribute') ? $p->all_videos : ($p->video_path ? [$p->video_path] : []);
                    @endphp
                    @if(!empty($vids))
                        <div style="width: calc(100% - 24px); margin: 0 12px 12px 12px; border-radius: 14px; overflow: hidden; background: #000;">
                            @foreach($vids as $vidUrl)
                                <video src="{{ $vidUrl }}" controls preload="metadata" style="width: 100%; max-height: 480px; display: block; border-radius: 12px; background: #0f172a;"></video>
                            @endforeach
                        </div>
                    @endif

                    <!-- Post Stats Bar -->
                    <div style="padding: 10px 18px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; font-size: 0.84rem; color: #64748b; font-weight: 600; background: #fafafa;">
                        <div id="post-likes-count-{{ $postDomKey }}" onclick="showPostLikers({{ $p->id }}, '{{ $isFoodTour ? 'food_tour' : ($isCheckin ? 'checkin' : 'post') }}')" style="cursor:pointer; display: inline-flex; align-items: center; gap: 6px;" title="Xem danh sách người đã thích">
                            <span style="width: 22px; height: 22px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 900; box-shadow: 0 2px 6px rgba(14, 165, 233, 0.3);">👍</span>
                            <span style="color: #334155; font-weight: 700;">{{ $p->reaction_total ?? $p->likes_count ?? 0 }} lượt thích</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span onclick="toggleComments('{{ $postDomKey }}', this)" style="cursor: pointer; font-weight: 700; color: #475569; padding: 4px 10px; border-radius: 8px; background: rgba(14, 165, 233, 0.08); transition: all 0.2s;" onmouseover="this.style.background='#e0f2fe'; this.style.color='#0284c7';" onmouseout="this.style.background='rgba(14, 165, 233, 0.08)'; this.style.color='#475569';" title="Bấm để xem/bình luận">💬 {{ $p->comments ? $p->comments->count() : 0 }} bình luận</span>
                            <span style="color: #cbd5e1;">•</span>
                            <span style="color: #64748b;"><span id="post-shares-count-{{ $postDomKey }}">{{ $p->shares_count ?? 0 }}</span> chia sẻ</span>
                        </div>
                    </div>

                    <!-- Reaction Actions Bar (Facebook Desktop Style) -->
                    <div style="display: flex; justify-content: space-between; gap: 4px; padding: 4px 12px; background: #ffffff;">
                        <button class="nf-action-btn {{ ($p->is_liked ?? false) ? 'active' : '' }}" 
                                id="post-like-btn-{{ $postDomKey }}" 
                                onclick="togglePostLike(this, {{ $p->id }})"
                                style="flex: 1; justify-content: center;">
                            👍 {{ ($p->is_liked ?? false) ? 'Đã thích' : 'Thích' }}
                        </button>
                        <button class="nf-action-btn" onclick="toggleComments('{{ $postDomKey }}', this)" style="flex: 1; justify-content: center;">
                            💬 Bình luận
                        </button>
                        <button class="nf-action-btn" onclick="shareFbPost({{ $p->id }}, {{ json_encode($p->name ?? '') }}, {{ json_encode($imgs) }}, '{{ $p->hashid ?? $p->id }}')" style="flex: 1; justify-content: center;">
                            🔄 Chia sẻ
                        </button>
                    </div>

                    <!-- Expandable Comments Drawer -->
                    <div class="comments-section" id="comments-section-{{ $postDomKey }}" style="display: none; padding: 14px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <div class="comments-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                            @if($p->comments && $p->comments->isNotEmpty())
                                @foreach($p->comments as $comment)
                                    @php
                                        $rawContent = $comment->content;
                                        $isReply = str_starts_with(trim($rawContent), '@');
                                        
                                        // Format @Mention thành tag pill màu xanh nổi bật
                                        $formattedContent = e($rawContent);
                                        if ($isReply) {
                                            $formattedContent = preg_replace('/^@([^:]+?)(:|\s|$)/u', '<span style="color: #0284c7; font-weight: 800; background: rgba(2, 132, 199, 0.12); padding: 2px 8px; border-radius: 6px; margin-right: 4px; display: inline-block; font-size: 0.84rem;">@$1</span>', $formattedContent);
                                        }
                                    @endphp

                                    <div class="comment-item {{ $isReply ? 'comment-reply-item' : '' }}" 
                                         style="display: flex; gap: 10px; align-items: flex-start; 
                                                background: {{ $isReply ? '#f0f9ff' : '#ffffff' }}; 
                                                border-radius: 14px; padding: 10px 14px; 
                                                border: 1px solid {{ $isReply ? '#bae6fd' : '#e2e8f0' }}; 
                                                {{ $isReply ? 'margin-left: 32px; border-left: 3.5px solid #0284c7; box-shadow: 0 2px 8px rgba(2,132,199,0.06);' : '' }}">
                                        <div class="comment-avatar" style="width: {{ $isReply ? '28px' : '32px' }}; height: {{ $isReply ? '28px' : '32px' }}; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: {{ $isReply ? '0.75rem' : '0.8rem' }}; font-weight: 800; flex-shrink: 0;">
                                            {{ $comment->user ? mb_substr($comment->user->name, 0, 1, 'UTF-8') : '👤' }}
                                        </div>
                                        <div style="flex: 1; display: flex; flex-direction: column; gap: 3px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                                    @if($isReply)
                                                        <span style="color: #0284c7; font-size: 0.85rem; font-weight: 900;">↳</span>
                                                    @endif
                                                    {{ $comment->display_name }}
                                                    @if($comment->user && $comment->user->role === 'admin')
                                                        <span style="font-size: 0.65rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-radius: 4px; padding: 1px 5px;">Admin</span>
                                                    @endif
                                                    @if($isReply)
                                                        <span style="font-size: 0.68rem; font-weight: 700; background: #e0f2fe; color: #0284c7; border-radius: 4px; padding: 1px 6px;">Phản hồi</span>
                                                    @endif
                                                </span>
                                                <span style="font-size: 0.7rem; color: #64748b;">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p style="margin: 0; font-size: 0.86rem; color: #334155; line-height: 1.45;">{!! $formattedContent !!}</p>
                                            
                                            <!-- Action Bar (Trả lời bình luận) -->
                                            <div style="display: flex; align-items: center; gap: 12px; margin-top: 4px; font-size: 0.75rem;">
                                                <button type="button" onclick="replyToComment('{{ $postDomKey }}', '{{ e($comment->display_name) }}')" style="background: none; border: none; padding: 0; color: #0ea5e9; font-weight: 700; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 4px;">
                                                    ↩️ Trả lời
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Comment Input Form (Cho phép cả khách vãng lai & thành viên bình luận - AJAX Không Reload) -->
                        <form action="{{ route('comments.store') }}" method="POST" onsubmit="submitCommentAjax(event, '{{ $postDomKey }}')" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                            @csrf
                            <input type="hidden" name="commentable_id" value="{{ $p->id }}">
                            <input type="hidden" name="commentable_type" value="{{ $commentableClass }}">
                            <div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none; height: 0; width: 0; overflow: hidden;" aria-hidden="true">
                                <input type="text" name="_hp_author_url" tabindex="-1" autocomplete="off" value="">
                            </div>

                            @if(!auth()->check() && !session('user_id'))
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #64748b; margin-bottom: 2px;">
                                    <span style="font-weight: 700; color: #0284c7;">👤 Bạn đang bình luận dưới danh nghĩa:</span>
                                    <input type="text" name="guest_name" placeholder="Tên của bạn (Tùy chọn)" value="" style="padding: 4px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.8rem; outline: none; background: #fff; max-width: 200px;">
                                </div>
                            @endif

                            <div style="display: flex; gap: 8px; align-items: center; position: relative;">
                                <input type="text" name="content" placeholder="Viết bình luận của bạn... (Nhấn Enter để gửi)" required autocomplete="off"
                                    onkeydown="handleCommentKeydown(event, '{{ $postDomKey }}')"
                                    style="flex: 1; padding: 10px 16px; border-radius: 20px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #0f172a; font-size: 0.88rem; outline: none; transition: all 0.2s;"
                                    onfocus="this.style.borderColor='#0284c7'; this.style.boxShadow='0 0 0 3px rgba(2, 132, 199, 0.15)';"
                                    onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                                <button type="submit" style="padding: 10px 20px; border-radius: 20px; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; border: none; font-size: 0.88rem; font-weight: 800; cursor: pointer; white-space: nowrap; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); transition: transform 0.15s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                                    Gửi 💬
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            @endforeach
        @else
            <div class="nf-widget" style="padding: 40px 20px; text-align: center;">
                <div style="font-size: 3.2rem; margin-bottom: 12px;">📰</div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Chưa có bài viết nào trên Bản tin</h3>
                <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 20px;">Hãy là người đầu tiên chia sẻ thông tin hoặc bài viết mới nhất lên cộng đồng!</p>
                <button onclick="openNewsfeedPostModal()" style="display: inline-block; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; padding: 12px 24px; border-radius: 100px; font-weight: 800; border: none; cursor: pointer; box-shadow: 0 4px 15px rgba(14,165,233,0.35);">✍️ Đăng bài ngay</button>
            </div>
        @endif

    </main>

</div>

<!-- Modal 1: Đăng Tin Story Mới (Instagram Story Creator Studio) -->
<div id="createStoryModal" style="display: none; position: fixed; inset: 0; background: #09090b; z-index: 999999; flex-direction: column; align-items: center; justify-content: space-between; overflow: hidden; font-family: inherit;">
    
    <!-- Top IG Header Tools Bar -->
    <div style="width: 100%; max-width: 480px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; z-index: 10;">
        <button type="button" onclick="closeCreateStoryModal()" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.15); color: #fff; border: none; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>
        
        <div style="display: flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.12); padding: 6px 14px; border-radius: 100px;">
            <span style="font-size: 0.82rem; font-weight: 800; color: #fff;">Story Studio</span>
            <span style="font-size: 0.7rem; background: linear-gradient(45deg, #f09433, #dc2743); color: #fff; padding: 1px 6px; border-radius: 6px; font-weight: 800;">IG</span>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Toggle Text Input Modal / Overlay -->
            <button type="button" title="Thêm văn bản trên Story" onclick="document.getElementById('igCaptionTextInput').focus()" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.15); color: #fff; border: none; font-size: 1.1rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center;">Aa</button>
            
            <!-- Cycle IG Gradient Palette -->
            <button type="button" title="Đổi phông màu Instagram" onclick="cycleIgGradientColor()" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #f09433, #dc2743, #bc1888); border: 2px solid #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;"></button>
        </div>
    </div>

    <!-- Main Instagram Story 9:16 Canvas Frame -->
    <div id="igStoryCanvas" style="width: 100%; max-width: 380px; height: 74vh; max-height: 680px; border-radius: 28px; position: relative; overflow: hidden; background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); box-shadow: 0 20px 50px rgba(0,0,0,0.7); display: flex; flex-direction: column; justify-content: space-between; padding: 16px;">
        
        <!-- Author Top Tag inside Canvas -->
        <div style="display: flex; align-items: center; gap: 8px; z-index: 10;">
            @if(auth()->check())
                @php $igUser = auth()->user(); @endphp
                <img src="{{ $igUser->avatar_url ?? 'https://ui-avatars.com/api/?name=User' }}" style="width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid #fff; object-fit: cover;">
                <span style="font-size: 0.82rem; font-weight: 800; color: #fff; text-shadow: 0 1px 4px rgba(0,0,0,0.6);">{{ $igUser->name }}</span>
            @else
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #fff; color: #000; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">👤</div>
                <span style="font-size: 0.82rem; font-weight: 800; color: #fff;">Bạn</span>
            @endif
            <span style="font-size: 0.72rem; color: rgba(255,255,255,0.75);">Vừa xong</span>
        </div>

        <!-- Center Canvas Content (Media Image/Video OR Text Overlay) -->
        <div id="igCanvasMediaLayer" style="position: absolute; inset: 0; z-index: 1; display: none;">
            <img id="igCanvasImg" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
            <video id="igCanvasVid" src="" autoplay playsinline loop style="width: 100%; height: 100%; object-fit: contain; display: none; background: #000;"></video>
        </div>

        <!-- Live IG Text Badge Overlay -->
        <div id="igCanvasTextLayer" style="z-index: 10; margin: auto; padding: 16px; text-align: center; width: 100%;">
            <div id="igCanvasTextPreview" style="color: #ffffff; font-size: 1.35rem; font-weight: 900; line-height: 1.4; text-shadow: 0 2px 10px rgba(0,0,0,0.8); word-break: break-word;">
                Chạm để nhập văn bản Story ✨
            </div>
        </div>

        <!-- Sticker Mood Badge -->
        <div id="igStickerBadge" style="z-index: 10; align-self: center; margin-bottom: 20px; background: rgba(255,255,255,0.25); backdrop-filter: blur(10px); color: #fff; font-size: 0.8rem; font-weight: 800; padding: 6px 14px; border-radius: 100px; border: 1px solid rgba(255,255,255,0.4); display: none;">
            📍 Đông Anh Vibe 🌾
        </div>
    </div>

    <!-- Hidden Form Fields -->
    <form id="createStoryForm" onsubmit="submitNewStory(event)" enctype="multipart/form-data" style="display: none;">
        @csrf
        <input type="file" name="media_file" id="igStoryMediaFileInput" accept="image/*,video/*" onchange="previewIgStoryMedia(this)">
        <input type="hidden" name="caption" id="igStoryCaptionHiddenInput">
        <input type="hidden" name="bg_gradient" id="igStoryBgGradientHiddenInput" value="linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%)">
        <input type="hidden" name="type" id="igStoryTypeHiddenInput" value="image">
    </form>

    <!-- Bottom IG Studio Control Toolbar -->
    <div style="width: 100%; max-width: 480px; padding: 14px 20px 24px 20px; z-index: 10; display: flex; flex-direction: column; gap: 14px;">
        
        <!-- Text Caption Quick Input Row -->
        <div style="display: flex; gap: 8px;">
            <input type="text" id="igCaptionTextInput" placeholder="Viết tin nhắn / caption cho Story..." oninput="updateIgCanvasText(this.value)" style="flex: 1; padding: 12px 16px; border-radius: 100px; border: 1.5px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.15); color: #fff; font-size: 0.88rem; outline: none; font-family: inherit;">
            
            <button type="button" onclick="toggleIgStickerBadge()" style="padding: 12px 16px; border-radius: 100px; border: 1.5px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.15); color: #fff; font-size: 0.85rem; font-weight: 800; cursor: pointer;">
                🏷️ Nhãn
            </button>
        </div>

        <!-- Action Buttons: Select Photo & Post Your Story -->
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            
            <!-- Gallery / Camera Picker -->
            <button type="button" onclick="document.getElementById('igStoryMediaFileInput').click()" style="padding: 12px 18px; border-radius: 100px; background: rgba(255,255,255,0.18); color: #fff; border: 1px solid rgba(255,255,255,0.3); font-weight: 800; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                🖼️ Chọn Ảnh/Video
            </button>

            <!-- Instagram "Your Story" Post Button -->
            <button type="button" id="submitStoryBtn" onclick="document.getElementById('createStoryForm').requestSubmit()" style="flex: 1; padding: 12px 20px; border-radius: 100px; background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); color: #fff; border: none; font-weight: 900; font-size: 0.92rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 15px rgba(220,39,67,0.4);">
                @if(auth()->check())
                    <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=User' }}" style="width: 26px; height: 26px; border-radius: 50%; border: 2px solid #fff; object-fit: cover;">
                @endif
                Tin của bạn ✨
            </button>
        </div>
    </div>
</div>

<!-- Modal 2: Viewer Xem Story Toàn Màn Hình (FB / IG Fullscreen Story Viewer) -->
<div id="storyViewerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.95); backdrop-filter: blur(16px); z-index: 999999; align-items: center; justify-content: center; overflow: hidden;">
    
    <!-- Top Close Button -->
    <button type="button" onclick="closeStoryViewer()" style="position: absolute; top: 20px; right: 20px; width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.2); color: #fff; border: none; font-size: 1.4rem; cursor: pointer; z-index: 100000; display: flex; align-items: center; justify-content: center; transition: background 0.2s ease;" title="Đóng Story">✕</button>

    <!-- Visible Previous Navigation Arrow Button -->
    <button type="button" id="prevStoryBtn" onclick="prevStorySlide()" style="position: absolute; left: 24px; top: 50%; transform: translateY(-50%); width: 52px; height: 52px; border-radius: 50%; background: rgba(255,255,255,0.2); color: #ffffff; border: 1.5px solid rgba(255,255,255,0.35); font-size: 1.5rem; font-weight: 900; cursor: pointer; z-index: 100000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(12px); box-shadow: 0 4px 20px rgba(0,0,0,0.5); transition: all 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.35)'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-50%) scale(1)';" title="Story trước">❮</button>

    <!-- Visible Next Navigation Arrow Button -->
    <button type="button" id="nextStoryBtn" onclick="nextStorySlide()" style="position: absolute; right: 24px; top: 50%; transform: translateY(-50%); width: 52px; height: 52px; border-radius: 50%; background: rgba(255,255,255,0.2); color: #ffffff; border: 1.5px solid rgba(255,255,255,0.35); font-size: 1.5rem; font-weight: 900; cursor: pointer; z-index: 100000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(12px); box-shadow: 0 4px 20px rgba(0,0,0,0.5); transition: all 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.35)'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-50%) scale(1)';" title="Story tiếp theo">❯</button>

    <!-- Main Mobile Frame Container (Aspect Ratio 9:16) -->
    <div style="width: 100%; max-width: 420px; height: 90vh; max-height: 840px; position: relative; border-radius: 24px; overflow: hidden; background: #0f172a; box-shadow: 0 25px 60px rgba(0,0,0,0.8); display: flex; flex-direction: column;">
        
        <!-- Multi-segment Progress Bar Lines Top Container -->
        <div id="storyProgressContainer" style="display: flex; gap: 4px; padding: 12px 14px 4px 14px; position: absolute; top: 0; left: 0; right: 0; z-index: 100;"></div>

        <!-- Story Author Header Row -->
        <div style="display: flex; align-items: center; gap: 10px; padding: 24px 14px 12px 14px; position: absolute; top: 0; left: 0; right: 0; z-index: 95; background: linear-gradient(180deg, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0) 100%); pointer-events: none;">
            <img id="viewerAuthorAvatar" src="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #0ea5e9; flex-shrink: 0;">
            <div style="flex: 1; min-width: 0;">
                <h5 id="viewerAuthorName" style="margin: 0; font-size: 0.92rem; font-weight: 800; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></h5>
                <span id="viewerTimestamp" style="font-size: 0.74rem; color: rgba(255,255,255,0.8);">Vừa xong</span>
            </div>
        </div>

        <!-- Story Media / Background Box -->
        <div id="viewerContentBox" style="width: 100%; height: 100%; position: absolute; inset: 0; z-index: 1; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center;">
            <img id="viewerMediaImage" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
            <video id="viewerMediaVideo" src="" autoplay playsinline loop style="width: 100%; height: 100%; object-fit: contain; display: none; background: #000;"></video>
            
            <!-- Story Caption Text Overlay -->
            <div id="viewerCaptionText" style="position: absolute; bottom: 75px; left: 0; right: 0; padding: 20px 16px 16px 16px; color: #ffffff; font-size: 1.1rem; font-weight: 800; text-align: center; line-height: 1.5; text-shadow: 0 2px 10px rgba(0,0,0,0.9); z-index: 10; background: linear-gradient(0deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%); word-break: break-word; pointer-events: none; display: none;"></div>
        </div>

        <!-- Left & Right Tap Navigation Zones -->
        <div onclick="prevStorySlide()" style="position: absolute; top: 60px; bottom: 75px; left: 0; width: 40%; z-index: 80; cursor: pointer;"></div>
        <div onclick="nextStorySlide()" style="position: absolute; top: 60px; bottom: 75px; right: 0; width: 60%; z-index: 80; cursor: pointer;"></div>

        <!-- Bottom Quick Reaction Bar -->
        <div style="padding: 12px 14px 16px 14px; position: absolute; bottom: 0; left: 0; right: 0; z-index: 95; background: linear-gradient(0deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%); display: flex; align-items: center; gap: 8px;">
            <input type="text" id="viewerQuickReplyInput" placeholder="Gửi phản hồi..." style="flex: 1; padding: 10px 16px; border-radius: 100px; border: 1.5px solid rgba(255,255,255,0.35); background: rgba(255,255,255,0.2); color: #fff; font-size: 0.85rem; outline: none;" onkeydown="if(event.key==='Enter') sendStoryReply()">
            <button type="button" onclick="sendStoryReaction('❤️')" style="background: transparent; border: none; font-size: 1.4rem; cursor: pointer;">❤️</button>
            <button type="button" onclick="sendStoryReaction('🔥')" style="background: transparent; border: none; font-size: 1.4rem; cursor: pointer;">🔥</button>
            <button type="button" onclick="sendStoryReaction('👍')" style="background: transparent; border: none; font-size: 1.4rem; cursor: pointer;">👍</button>
        </div>
    </div>
</div>

<script>
// Toggle Comments Drawer for Post
function toggleComments(postId, btn) {
    const section = document.getElementById('comments-section-' + postId);
    if (!section) return;
    
    if (section.style.display === 'none' || !section.style.display) {
        section.style.display = 'block';
        section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        section.style.display = 'none';
    }
}

// Tự động cuộn, mở bình luận và phát sáng bài viết khi chuyển đến từ thông báo
window.scrollToNotifTarget = function() {
    const params = new URLSearchParams(window.location.search);
    const targetPost = params.get('post') || params.get('post_id') || params.get('id');
    const openComments = params.get('open_comments') || params.get('comments');

    if (targetPost) {
        let targetEl = document.getElementById('post-card-' + targetPost) ||
                       document.getElementById('post-card-post-' + targetPost) ||
                       document.getElementById('post-card-checkin-' + targetPost) ||
                       document.getElementById('post-card-foodtour-' + targetPost) ||
                       document.querySelector(`[data-post-id="${targetPost}"]`) ||
                       document.querySelector(`[data-hashid="${targetPost}"]`);

        if (targetEl) {
            document.querySelectorAll('.notif-target-highlight').forEach(el => el.classList.remove('notif-target-highlight'));

            setTimeout(function() {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetEl.classList.add('notif-target-highlight');

                if (openComments) {
                    const domKey = targetEl.id.replace('post-card-', '');
                    const section = document.getElementById('comments-section-' + domKey);
                    if (section) {
                        section.style.display = 'block';
                        const input = section.querySelector('input[name="content"]');
                        if (input) {
                            setTimeout(() => {
                                input.focus();
                                input.style.boxShadow = '0 0 0 4px rgba(14, 165, 233, 0.4)';
                                setTimeout(() => { input.style.boxShadow = 'none'; }, 2500);
                            }, 350);
                        }
                    }
                }
            }, 250);
        }
    }
};

document.addEventListener("DOMContentLoaded", window.scrollToNotifTarget);

// Trả lời bình luận (Reply to comment)
function replyToComment(postDomKey, authorName) {
    const section = document.getElementById('comments-section-' + postDomKey);
    if (!section) return;
    
    if (section.style.display === 'none' || !section.style.display) {
        section.style.display = 'block';
    }
    
    const input = section.querySelector('input[name="content"]');
    if (input) {
        input.value = '@' + authorName + ' ';
        input.focus();
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Bấm phím Enter để gửi bình luận
function handleCommentKeydown(event, postDomKey) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        const form = event.target.closest('form');
        if (form) {
            form.requestSubmit();
        }
    }
}

// Gửi bình luận không làm reload trang (AJAX Submit)
function submitCommentAjax(event, postDomKey) {
    event.preventDefault();
    const form = event.target;
    const input = form.querySelector('input[name="content"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (!input || !input.value.trim()) return;
    
    const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Gửi 💬';
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳...';
    }
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(({ status, body }) => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
        
        if (body && body.success) {
            input.value = '';
            
            const section = document.getElementById('comments-section-' + postDomKey);
            if (section) {
                let list = section.querySelector('.comments-list');
                if (!list) {
                    list = document.createElement('div');
                    list.className = 'comments-list';
                    list.style.cssText = 'display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;';
                    section.insertBefore(list, form);
                }
                
                const c = body.comment;
                const rawContent = c.content || '';
                const isReply = rawContent.trim().startsWith('@');
                let formattedContent = rawContent.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

                if (isReply) {
                    formattedContent = formattedContent.replace(/^@([^:]+?)(:|\s|$)/, '<span style="color: #0284c7; font-weight: 800; background: rgba(2, 132, 199, 0.12); padding: 2px 8px; border-radius: 6px; margin-right: 4px; display: inline-block; font-size: 0.84rem;">@$1</span>');
                }

                const adminBadge = c.is_admin ? '<span style="font-size: 0.65rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-radius: 4px; padding: 1px 5px;">Admin</span>' : '';
                const replyBadge = isReply ? '<span style="font-size: 0.68rem; font-weight: 700; background: #e0f2fe; color: #0284c7; border-radius: 4px; padding: 1px 6px;">Phản hồi</span>' : '';
                const replyArrow = isReply ? '<span style="color: #0284c7; font-size: 0.85rem; font-weight: 900;">↳</span>' : '';
                const marginLeft = isReply ? 'margin-left: 32px; border-left: 3.5px solid #0284c7; box-shadow: 0 2px 8px rgba(2,132,199,0.06);' : '';
                const bg = isReply ? '#f0f9ff' : '#ffffff';
                const borderColor = isReply ? '#bae6fd' : '#e2e8f0';
                const avatarSize = isReply ? '28px' : '32px';
                const avatarFont = isReply ? '0.75rem' : '0.8rem';
                const safeName = c.display_name.replace(/'/g, "\\'");
                
                const commentEl = document.createElement('div');
                commentEl.className = 'comment-item' + (isReply ? ' comment-reply-item' : '');
                commentEl.style.cssText = `display: flex; gap: 10px; align-items: flex-start; background: ${bg}; border-radius: 14px; padding: 10px 14px; border: 1px solid ${borderColor}; animation: fadeIn 0.3s ease; ${marginLeft}`;
                commentEl.innerHTML = `
                    <div class="comment-avatar" style="width: ${avatarSize}; height: ${avatarSize}; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: ${avatarFont}; font-weight: 800; flex-shrink: 0;">
                        ${c.avatar_letter}
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 3px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                ${replyArrow} ${c.display_name} ${adminBadge} ${replyBadge}
                            </span>
                            <span style="font-size: 0.7rem; color: #64748b;">${c.created_at}</span>
                        </div>
                        <p style="margin: 0; font-size: 0.86rem; color: #334155; line-height: 1.45;">${formattedContent}</p>
                        <div style="display: flex; align-items: center; gap: 12px; margin-top: 4px; font-size: 0.75rem;">
                            <button type="button" onclick="replyToComment('${postDomKey}', '${safeName}')" style="background: none; border: none; padding: 0; color: #0ea5e9; font-weight: 700; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 4px;">
                                ↩️ Trả lời
                            </button>
                        </div>
                    </div>
                `;
                
                list.appendChild(commentEl);
                commentEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            
            // Cập nhật số lượng bình luận ở thanh thống kê
            const statsBtn = document.querySelector(`#post-card-${postDomKey} [onclick*="toggleComments"]`);
            if (statsBtn) {
                const currentText = statsBtn.innerText;
                const match = currentText.match(/(\d+)/);
                if (match) {
                    const newCount = parseInt(match[1]) + 1;
                    statsBtn.innerText = `💬 ${newCount} bình luận`;
                }
            }
            
            if (typeof window.showToast === 'function') {
                window.showToast('✅ Đăng bình luận thành công!', 'success');
            }
        } else {
            const err = (body && body.message) ? body.message : 'Không thể gửi bình luận, vui lòng thử lại.';
            if (typeof window.showToast === 'function') {
                window.showToast('❌ ' + err, 'error');
            } else {
                alert(err);
            }
        }
    })
    .catch(err => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
        console.error('Comment AJAX error:', err);
        if (typeof window.showToast === 'function') {
            window.showToast('❌ Lỗi kết nối mạng, vui lòng thử lại.', 'error');
        }
    });
}

// Toggle Expand / Collapse for Long Post Descriptions
function togglePostDescription(btn) {
    const box = btn.closest('.post-description-box');
    if (!box) return;
    const truncatedSpan = box.querySelector('.desc-truncated-text');
    const fullSpan = box.querySelector('.desc-full-text');

    if (fullSpan.style.display === 'none') {
        fullSpan.style.display = 'inline';
        truncatedSpan.style.display = 'none';
        btn.innerHTML = 'Thu gọn 🔼';
    } else {
        fullSpan.style.display = 'none';
        truncatedSpan.style.display = 'inline';
        btn.innerHTML = 'Xem thêm 🔽';
    }
}

// Filter Newsfeed Posts
function filterNewsfeedPosts(type, btnEl) {
    const pills = document.querySelectorAll('.nf-filter-pill');
    pills.forEach(p => p.classList.remove('active'));
    if (btnEl) {
        btnEl.classList.add('active');
    } else {
        const matchingPill = document.getElementById(`pill-${type}`);
        if (matchingPill) matchingPill.classList.add('active');
    }

    const sidebarNavItems = document.querySelectorAll('.nf-sidebar-nav-item');
    sidebarNavItems.forEach(item => {
        item.classList.remove('active');
        if (item.tagName === 'BUTTON') {
            item.style.background = 'transparent';
            item.style.color = '#475569';
            item.style.fontWeight = '700';
        }
    });

    const activeNavItem = document.querySelector(`.nf-sidebar-nav-item[data-type="${type}"]`);
    if (activeNavItem) {
        activeNavItem.classList.add('active');
        activeNavItem.style.background = '#e0f2fe';
        activeNavItem.style.color = '#0284c7';
        activeNavItem.style.fontWeight = '800';
    }

    const posts = document.querySelectorAll('.post-item-card');
    posts.forEach(post => {
        const postType = post.getAttribute('data-post-type');
        if (type === 'all' || postType === type) {
            post.style.display = 'block';
        } else {
            post.style.display = 'none';
        }
    });
}

// INSTAGRAM STORY CREATOR STUDIO LOGIC
const igGradientsList = [
    'linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%)',
    'linear-gradient(135deg, #8b5cf6, #ec4899)',
    'linear-gradient(135deg, #0ea5e9, #0284c7)',
    'linear-gradient(135deg, #10b981, #059669)',
    'linear-gradient(135deg, #1e293b, #0f172a)'
];
let currentIgGradientIdx = 0;

function openCreateStoryModal() {
    const modal = document.getElementById('createStoryModal');
    if (modal) modal.style.display = 'flex';
}
function closeCreateStoryModal() {
    const modal = document.getElementById('createStoryModal');
    if (modal) modal.style.display = 'none';
}

function cycleIgGradientColor() {
    currentIgGradientIdx = (currentIgGradientIdx + 1) % igGradientsList.length;
    const newGrad = igGradientsList[currentIgGradientIdx];
    document.getElementById('igStoryCanvas').style.background = newGrad;
    document.getElementById('igStoryBgGradientHiddenInput').value = newGrad;
}

function updateIgCanvasText(val) {
    const textPreview = document.getElementById('igCanvasTextPreview');
    document.getElementById('igStoryCaptionHiddenInput').value = val;
    if (val && val.trim() !== '') {
        textPreview.innerText = val;
    } else {
        textPreview.innerText = 'Chạm để nhập văn bản Story ✨';
    }
}

function previewIgStoryMedia(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const layer = document.getElementById('igCanvasMediaLayer');
            const img = document.getElementById('igCanvasImg');
            const vid = document.getElementById('igCanvasVid');
            layer.style.display = 'block';
            if (file.type.startsWith('image/')) {
                img.src = e.target.result;
                img.style.display = 'block';
                vid.style.display = 'none';
            } else if (file.type.startsWith('video/')) {
                vid.src = e.target.result;
                vid.style.display = 'block';
                img.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }
}

const igStickers = ['📍 Đông Anh Vibe 🌾', '🍲 Foodie Đông Anh', '🎈 Check-in Hot', '☕ Chill Out'];
let currentStickerIdx = -1;
function toggleIgStickerBadge() {
    const badge = document.getElementById('igStickerBadge');
    currentStickerIdx = (currentStickerIdx + 1) % (igStickers.length + 1);
    if (currentStickerIdx === igStickers.length) {
        badge.style.display = 'none';
    } else {
        badge.innerText = igStickers[currentStickerIdx];
        badge.style.display = 'inline-block';
    }
}

function submitNewStory(e) {
    e.preventDefault();
    const btn = document.getElementById('submitStoryBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Đang đăng Story...';

    const formData = new FormData(document.getElementById('createStoryForm'));

    fetch('/stories', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '🚀 Chia sẻ Story ngay';
        if (data.success) {
            if (window.showToast) window.showToast('✅ ' + data.message, 'success');
            closeCreateStoryModal();
            location.reload();
        } else {
            alert(data.message || 'Đăng Story thất bại!');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '🚀 Chia sẻ Story ngay';
        alert('Có lỗi xảy ra khi đăng Story!');
    });
}

// STORY VIEWER MODAL LOGIC (FULLSCREEN FB/IG STYLE)
let storyGroups = [];
let currentGroupIndex = 0;
let currentGroupStoryIndex = 0;
let storyTimer = null;

const presetStoriesList = [
    {
        author_name: 'Lễ hội Cổ Loa ⛩️',
        author_avatar: 'https://media.xadonganh.com/eateries/1780392421_hfPQH7HB.png',
        media_url: 'https://images.unsplash.com/photo-1596402184320-417e7178b2cd?auto=format&fit=crop&w=1200&q=80',
        caption: 'Hòa mình vào không khí Lễ hội di tích quốc gia Cổ Loa năm 2026! ⛩️',
        created_at: '2 giờ trước'
    },
    {
        author_name: 'Chào năm học mới 🏫',
        author_avatar: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80',
        media_url: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=1200&q=80',
        caption: 'Chúc các bé Trường Mầm Nhỏ có một năm học mới tràn đầy niềm vui 🎒',
        created_at: '4 giờ trước'
    },
    {
        author_name: 'Ẩm thực Đông Anh 🍲',
        author_avatar: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=150&q=80',
        media_url: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
        caption: 'Food tour bún chả & đặc sản Đông Anh hấp dẫn không thể bỏ qua! 🍲',
        created_at: '5 giờ trước'
    },
    {
        author_name: 'Góc Check-in Hot 🎈',
        author_avatar: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=150&q=80',
        media_url: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80',
        caption: 'Góc chụp ảnh chill cực đẹp tại Đông Anh 🎈',
        created_at: '6 giờ trước'
    }
];

const userStoriesDb = @json(isset($stories) ? $stories : []);

function formatStoryTimeAgo(dateStr) {
    if (!dateStr) return 'Vừa xong';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return dateStr;

    const seconds = Math.floor((new Date() - date) / 1000);
    if (seconds < 60) return 'Vừa xong';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + ' phút trước';
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + ' giờ trước';
    const days = Math.floor(hours / 24);
    if (days < 30) return days + ' ngày trước';
    return date.toLocaleDateString('vi-VN');
}

function buildStoryGroups() {
    storyGroups = [];
    const groupedUsers = {};

    // 1. Group DB User Stories by author name
    if (userStoriesDb && userStoriesDb.length > 0) {
        userStoriesDb.forEach(s => {
            const authorName = s.author_name || (s.user ? s.user.name : 'Thành viên');
            const authorAvatar = s.author_avatar || (s.user ? s.user.avatar_url : 'https://ui-avatars.com/api/?name=User');
            const key = authorName;

            if (!groupedUsers[key]) {
                groupedUsers[key] = {
                    author_name: authorName,
                    author_avatar: authorAvatar,
                    stories: []
                };
            }
            groupedUsers[key].stories.push({
                id: s.id,
                user_id: s.user_id || (s.user ? s.user.id : null),
                author_name: authorName,
                author_avatar: authorAvatar,
                media_url: s.media_url,
                caption: s.caption || '',
                bg_gradient: s.bg_gradient || 'linear-gradient(135deg, #0ea5e9, #0284c7)',
                created_at: s.time_ago || formatStoryTimeAgo(s.created_at)
            });
        });

        Object.values(groupedUsers).forEach(g => {
            storyGroups.push(g);
        });
    }

    // 2. Add Preset Featured Stories (each preset story is its own group)
    presetStoriesList.forEach(p => {
        storyGroups.push({
            author_name: p.author_name,
            author_avatar: p.author_avatar,
            stories: [p]
        });
    });
}

function openStoryViewer(groupIdx) {
    buildStoryGroups();

    currentGroupIndex = groupIdx;
    if (currentGroupIndex >= storyGroups.length) currentGroupIndex = 0;
    currentGroupStoryIndex = 0;

    const modal = document.getElementById('storyViewerModal');
    if (modal) modal.style.display = 'flex';
    renderCurrentStory();
}

function renderCurrentStory() {
    if (!storyGroups[currentGroupIndex]) return;
    const group = storyGroups[currentGroupIndex];
    if (!group.stories[currentGroupStoryIndex]) return;

    const st = group.stories[currentGroupStoryIndex];

    document.getElementById('viewerAuthorName').innerText = st.author_name;
    document.getElementById('viewerAuthorAvatar').src = st.author_avatar || 'https://ui-avatars.com/api/?name=Story';
    document.getElementById('viewerTimestamp').innerText = st.created_at || 'Vừa xong';

    const img = document.getElementById('viewerMediaImage');
    const vid = document.getElementById('viewerMediaVideo');
    const txt = document.getElementById('viewerCaptionText');
    const box = document.getElementById('viewerContentBox');

    if (st.media_url) {
        box.style.background = '#0f172a';
        if (st.media_url.match(/\.(mp4|webm|mov)$/i)) {
            vid.src = st.media_url;
            vid.style.display = 'block';
            img.style.display = 'none';
        } else {
            img.src = st.media_url;
            img.style.display = 'block';
            vid.style.display = 'none';
        }
    } else {
        img.style.display = 'none';
        vid.style.display = 'none';
        box.style.background = st.bg_gradient || 'linear-gradient(135deg, #0ea5e9, #0284c7)';
    }

    if (st.caption && st.caption.trim() !== '') {
        txt.innerText = st.caption;
        txt.style.display = 'block';
    } else {
        txt.innerText = '';
        txt.style.display = 'none';
    }

    startStoryProgress();
}

function renderStoryProgressBars() {
    const container = document.getElementById('storyProgressContainer');
    if (!container) return;
    container.innerHTML = '';

    const group = storyGroups[currentGroupIndex];
    if (!group || !group.stories) return;

    group.stories.forEach((_, idx) => {
        const seg = document.createElement('div');
        seg.style.cssText = 'flex: 1; height: 3px; background: rgba(255,255,255,0.3); border-radius: 4px; overflow: hidden;';
        const inner = document.createElement('div');
        inner.id = `storySegInner_${idx}`;
        if (idx < currentGroupStoryIndex) {
            inner.style.cssText = 'width: 100%; height: 100%; background: #ffffff;';
        } else {
            inner.style.cssText = 'width: 0%; height: 100%; background: #ffffff; transition: width 0.1s linear;';
        }
        seg.appendChild(inner);
        container.appendChild(seg);
    });
}

function startStoryProgress() {
    clearInterval(storyTimer);
    renderStoryProgressBars();

    const inner = document.getElementById(`storySegInner_${currentGroupStoryIndex}`);
    if (!inner) return;

    let pct = 0;
    storyTimer = setInterval(() => {
        pct += 2;
        inner.style.width = pct + '%';
        if (pct >= 100) {
            clearInterval(storyTimer);
            nextStorySlide();
        }
    }, 100);
}

function nextStorySlide() {
    const group = storyGroups[currentGroupIndex];
    if (!group) {
        closeStoryViewer();
        return;
    }

    if (currentGroupStoryIndex < group.stories.length - 1) {
        currentGroupStoryIndex++;
        renderCurrentStory();
    } else if (currentGroupIndex < storyGroups.length - 1) {
        currentGroupIndex++;
        currentGroupStoryIndex = 0;
        renderCurrentStory();
    } else {
        closeStoryViewer();
    }
}

function prevStorySlide() {
    const group = storyGroups[currentGroupIndex];
    if (!group) return;

    if (currentGroupStoryIndex > 0) {
        currentGroupStoryIndex--;
        renderCurrentStory();
    } else if (currentGroupIndex > 0) {
        currentGroupIndex--;
        const prevGroup = storyGroups[currentGroupIndex];
        currentGroupStoryIndex = prevGroup.stories.length - 1;
        renderCurrentStory();
    }
}

function closeStoryViewer() {
    clearInterval(storyTimer);
    const modal = document.getElementById('storyViewerModal');
    if (modal) modal.style.display = 'none';
}

function sendStoryReaction(emoji) {
    if (window.showToast) window.showToast('Đã gửi ' + emoji + ' đến Story!', 'success');

    const group = storyGroups[currentGroupIndex];
    if (!group || !group.stories || !group.stories[currentGroupStoryIndex]) return;
    const st = group.stories[currentGroupStoryIndex];

    if (!st.user_id) return;

    const msgContent = `Đã bày tỏ cảm xúc ${emoji} với Story của bạn`;

    fetch('/social/messages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            receiver_id: st.user_id,
            message: msgContent
        })
    }).catch(err => {});
}

function sendStoryReply() {
    const input = document.getElementById('viewerQuickReplyInput');
    const text = input ? input.value.trim() : '';
    if (!text) return;

    const group = storyGroups[currentGroupIndex];
    if (!group || !group.stories || !group.stories[currentGroupStoryIndex]) return;
    const st = group.stories[currentGroupStoryIndex];

    if (window.showToast) window.showToast('💬 Đã gửi phản hồi tin nhắn!', 'success');
    if (input) input.value = '';

    if (!st.user_id) return;

    const msgContent = `📲 Đã trả lời Story của bạn:\n"${text}"`;

    fetch('/social/messages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            receiver_id: st.user_id,
            message: msgContent
        })
    }).catch(err => {});
}
</script>

<!-- Newsfeed Post Creation Modal -->
@if(auth()->check())
@php $authUser = auth()->user(); @endphp
<div class="sch-modal" id="addNewsfeedPostModal" onclick="if(event.target === this) closeNewsfeedPostModal()">
    <div class="fb-modal-box" style="max-width: 620px; width: 100%; max-height: 90vh; overflow-y: auto;">
        <button type="button" onclick="closeNewsfeedPostModal()" class="sch-close-modal" style="top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; border: none; font-size: 1.1rem; color: #475569; position: absolute; z-index: 10; cursor: pointer;">✕</button>
        <div class="fb-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding-right: 44px;">
            <h4 class="fb-modal-title" style="margin: 0;">Tạo bài viết</h4>
            <button type="button" id="toggleLivePreviewBtn" onclick="toggleNewsfeedLivePreview()" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; border-radius: 20px; padding: 4px 12px; font-size: 0.78rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                👁️ Live Preview: <span id="previewStatusText">Bật</span>
            </button>
        </div>
        
        <form action="{{ route('principal.posts.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleNewsfeedPostSubmit(event, this)">
            @csrf

            <!-- User Header Row -->
            <div class="fb-modal-user-row">
                @if($authUser->avatar_url)
                    <img src="{{ $authUser->avatar_url }}" class="fb-modal-user-avatar" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($authUser->name ?? 'User') }}&background=0ea5e9&color=fff';">
                @else
                    <div class="fb-modal-user-avatar" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                        {{ mb_substr($authUser->name, 0, 1, 'UTF-8') }}
                    </div>
                @endif
                <div class="fb-modal-user-info">
                    <h5 class="fb-modal-user-name">{{ $authUser->name }}</h5>
                    <div class="fb-modal-badges">
                        <span class="fb-modal-badge" style="background: #e2e8f0; font-size: 0.78rem; padding: 4px 10px; border-radius: 6px; font-weight: 700; color: #334155;">🌐 Công khai</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="fb-modal-body">
                <input type="text" name="name" id="newsfeed-input-title" required class="fb-modal-title-input" placeholder="Tiêu đề bài viết..." oninput="updateLivePostPreview()">
                <textarea name="description" id="newsfeed-input-content" required class="fb-modal-textarea" rows="3" placeholder="{{ $authUser->name }} ơi, bạn đang nghĩ gì thế?" oninput="updateLivePostPreview()"></textarea>
            </div>

            <!-- Multi Image Preview Box -->
            <div id="newsfeed-post-preview" style="display: none; border-radius: 14px; overflow: hidden; margin-bottom: 16px; position: relative;">
                <div id="newsfeed-preview-grid" style="width: 100%;"></div>
            </div>

            <!-- Action Bar với Kiểu dáng Độc lập (Tránh xung đột CSS fb-modal-action-btn) -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding: 12px 14px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; margin-bottom: 16px;">
                <span style="font-weight: 800; color: #1e293b; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                    ✨ Thêm vào bài viết
                </span>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <!-- Chọn tệp từ Máy tính / Thư viện -->
                    <label title="Chọn ảnh/video từ thư viện" style="width: auto !important; height: auto !important; min-height: 38px; border-radius: 10px !important; padding: 8px 14px !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; background: #eff6ff !important; color: #2563eb !important; border: 1px solid #bfdbfe !important; font-size: 0.85rem !important; font-weight: 700 !important; cursor: pointer !important; margin: 0 !important;">
                        🖼️ Thư viện / Tệp
                        <input type="file" name="images[]" id="newsfeed-input-files" multiple accept="image/*,video/*,.mp4,.mov,.avi,.mkv,.webm" style="display: none !important;" onchange="handleMediaFilesSelected(this)">
                    </label>

                    <!-- Chụp ảnh / Quay video trực tiếp (Camera điện thoại) -->
                    <label title="Chụp ảnh hoặc quay video trực tiếp bằng Camera" style="width: auto !important; height: auto !important; min-height: 38px; border-radius: 10px !important; padding: 8px 14px !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; background: #f0fdf4 !important; color: #16a34a !important; border: 1px solid #bbf7d0 !important; font-size: 0.85rem !important; font-weight: 700 !important; cursor: pointer !important; margin: 0 !important;">
                        📸 Camera
                        <input type="file" id="newsfeed-camera-input" accept="image/*,video/*" capture="environment" style="display: none !important;" onchange="handleMediaFilesSelected(this)">
                    </label>

                    <!-- Xóa tất cả tệp đã chọn -->
                    <button type="button" id="clearAllMediaBtn" onclick="clearAllSelectedMedia()" style="display: none; width: auto !important; height: auto !important; min-height: 38px; border-radius: 10px !important; padding: 8px 14px !important; white-space: nowrap !important; background: #fef2f2 !important; color: #ef4444 !important; border: 1px solid #fecaca !important; font-size: 0.85rem !important; font-weight: 700 !important; cursor: pointer !important; margin: 0 !important;">
                        🗑️ Xóa hết
                    </button>
                </div>
            </div>

            <!-- REAL-TIME LIVE POST PREVIEW CARD -->
            <div id="newsfeed-live-preview-container" style="margin-top: 14px; border-top: 1px dashed #cbd5e1; padding-top: 14px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 0.8rem; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                        👁️ Xem trước bài đăng trên Bảng tin
                    </span>
                    <span style="font-size: 0.72rem; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 12px; font-weight: 700;">Xem trước thời gian thực</span>
                </div>

                <div class="fb-post-card" style="background: #ffffff; border-radius: 16px; border: 1.5px solid #cbd5e1; overflow: hidden; box-shadow: 0 4px 16px rgba(15,23,42,0.06); width: 100%; box-sizing: border-box;">
                    <!-- Header -->
                    <div style="padding: 12px 14px; display: flex; align-items: center; gap: 10px; background: #fafafa; border-bottom: 1px solid #f1f5f9;">
                        @if($authUser->avatar_url)
                            <img src="{{ $authUser->avatar_url }}" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1;">
                        @else
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; flex-shrink: 0;">
                                {{ mb_substr($authUser->name, 0, 1, 'UTF-8') }}
                            </div>
                        @endif
                        <div>
                            <h4 style="margin: 0; font-size: 0.92rem; font-weight: 800; color: #0f172a;">{{ $authUser->name }}</h4>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 1px;">Vừa xong • 🌐 Công khai</div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div style="padding: 12px 14px; word-break: break-word; overflow-wrap: anywhere;">
                        <strong id="live-preview-title" style="font-size: 1rem; color: #0f172a; display: block; margin-bottom: 6px; line-height: 1.4; opacity: 0.5;">🌸 Tiêu đề bài viết...</strong>
                        <div id="live-preview-body" style="font-size: 0.88rem; line-height: 1.55; color: #334155; white-space: pre-wrap; opacity: 0.5;">Nội dung bài viết của bạn sẽ hiển thị trực tiếp ở đây khi bạn gõ...</div>
                    </div>

                    <!-- Media Grid Preview in Live Card -->
                    <div id="live-preview-media-box" style="display: none; width: 100%; border-top: 1px solid #e2e8f0; background: #0f172a;">
                        <div id="live-preview-media-grid" style="width: 100%;"></div>
                    </div>

                    <!-- Footer Bar Preview -->
                    <div style="padding: 10px 14px; border-top: 1px solid #f1f5f9; background: #fafafa; display: flex; align-items: center; justify-content: space-between; color: #64748b; font-size: 0.82rem; font-weight: 600;">
                        <span style="display: flex; align-items: center; gap: 4px;">👍 0 lượt thích</span>
                        <span>0 bình luận • 0 chia sẻ</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="fb-modal-submit-btn" style="margin-top: 16px;">
                Đăng bài viết
            </button>
        </form>
    </div>
</div>
@endif

<!-- Facebook Photo Lightbox Modal System -->
<div id="postLightboxModal" class="modal" style="display:none; position:fixed; z-index:99999; left:0; top:0; width:100%; height:100%; overflow:hidden; background-color:rgba(0,0,0,0.92); backdrop-filter:blur(10px); justify-content:center; align-items:center;">
    <span style="position:absolute; top:20px; right:25px; color:#ffffff; font-size:36px; font-weight:bold; cursor:pointer; z-index:100000; text-shadow: 0 2px 8px rgba(0,0,0,0.5);" onclick="closePostLightbox()">&times;</span>
    
    <button type="button" onclick="navigateLightbox(-1)" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); color:#fff; border:none; border-radius:50%; width:48px; height:48px; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:100000; backdrop-filter:blur(5px);">‹</button>
    <button type="button" onclick="navigateLightbox(1)" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); color:#fff; border:none; border-radius:50%; width:48px; height:48px; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:100000; backdrop-filter:blur(5px);">›</button>

    <div style="position:relative; max-width:90vw; max-height:90vh; display:flex; align-items:center; justify-content:center;">
        <img id="lightboxCurrentImg" style="max-width:90vw; max-height:85vh; border-radius:12px; object-fit:contain; box-shadow:0 12px 40px rgba(0,0,0,0.8);">
        <div id="lightboxCounter" style="position:absolute; bottom:-35px; color:rgba(255,255,255,0.85); font-size:0.85rem; font-weight:700; background:rgba(0,0,0,0.5); padding:4px 12px; border-radius:20px;"></div>
    </div>
</div>

<script>
let currentLightboxImages = [];
let currentLightboxIndex = 0;

function openPostLightboxGallery(images, startIndex = 0) {
    if (!images || !images.length) return;
    currentLightboxImages = images;
    currentLightboxIndex = startIndex;
    updateLightboxView();
    document.getElementById('postLightboxModal').style.display = 'flex';
}

function updateLightboxView() {
    if (!currentLightboxImages.length) return;
    const imgEl = document.getElementById('lightboxCurrentImg');
    const counterEl = document.getElementById('lightboxCounter');
    imgEl.src = currentLightboxImages[currentLightboxIndex];
    counterEl.textContent = `${currentLightboxIndex + 1} / ${currentLightboxImages.length}`;
}

function navigateLightbox(dir) {
    if (!currentLightboxImages.length) return;
    currentLightboxIndex = (currentLightboxIndex + dir + currentLightboxImages.length) % currentLightboxImages.length;
    updateLightboxView();
}

function closePostLightbox() {
    document.getElementById('postLightboxModal').style.display = 'none';
}

function toggleComments(postId, btnEl) {
    const sec = document.getElementById('comments-section-' + postId);
    if (sec) {
        sec.style.display = (sec.style.display === 'none' || sec.style.display === '') ? 'block' : 'none';
    }
}

function sendReaction(postId, type, emoji, event) {
    if (event) event.preventDefault();
    if (typeof checkAuthGuard === 'function' && !checkAuthGuard('tương tác bài viết')) return;
    fetch('/api/reactions/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            post_id: postId,
            type: type,
            emoji: emoji
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.reload();
        }
    })
    .catch(err => console.error('Reaction error:', err));
}

function togglePostLike(btn, postId) {
    if (typeof checkAuthGuard === 'function' && !checkAuthGuard('thích bài viết')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/api/reactions/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            id: postId,
            type: 'post',
            emoji: '👍'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.liked) {
                btn.classList.add('active');
                btn.style.color = '#2563eb';
                btn.style.fontWeight = '700';
                btn.innerHTML = '👍 Đã thích';
            } else {
                btn.classList.remove('active');
                btn.style.color = '#64748b';
                btn.style.fontWeight = '700';
                btn.innerHTML = '👍 Thích';
            }
            
            const statsEl = document.getElementById(`post-likes-count-${postId}`);
            if (statsEl) {
                statsEl.innerHTML = `👍 ${data.likes_count} lượt thích`;
            }
        }
    })
    .catch(err => {
        console.error('Lỗi tương tác bài viết:', err);
    });
}

function updateLivePostPreview() {
    const titleInput = document.getElementById('newsfeed-input-title');
    const contentInput = document.getElementById('newsfeed-input-content');
    const previewTitle = document.getElementById('live-preview-title');
    const previewBody = document.getElementById('live-preview-body');

    if (previewTitle) {
        const val = titleInput ? titleInput.value.trim() : '';
        previewTitle.textContent = val ? ('🌸 ' + val) : '🌸 Tiêu đề bài viết...';
        previewTitle.style.opacity = val ? '1' : '0.5';
    }

    if (previewBody) {
        const val = contentInput ? contentInput.value : '';
        previewBody.textContent = val ? val : 'Nội dung bài viết của bạn sẽ hiển thị trực tiếp ở đây khi bạn gõ...';
        previewBody.style.opacity = val ? '1' : '0.5';
    }
}

function toggleNewsfeedLivePreview() {
    const container = document.getElementById('newsfeed-live-preview-container');
    const statusText = document.getElementById('previewStatusText');
    const btn = document.getElementById('toggleLivePreviewBtn');
    if (!container || !btn) return;

    if (container.style.display === 'none') {
        container.style.display = 'block';
        if (statusText) statusText.textContent = 'Bật';
        btn.style.background = '#e0f2fe';
        btn.style.color = '#0284c7';
    } else {
        container.style.display = 'none';
        if (statusText) statusText.textContent = 'Tắt';
        btn.style.background = '#f1f5f9';
        btn.style.color = '#64748b';
    }
}

let selectedMediaFiles = [];

async function compressImageFile(file, maxDim = 2048, quality = 0.85) {
    if (!file || !file.type || !file.type.startsWith('image/') || file.size < 1200000) {
        return file;
    }

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;

                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    if (blob && blob.size < file.size) {
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        resolve(compressedFile);
                    } else {
                        resolve(file);
                    }
                }, 'image/jpeg', quality);
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

async function handleMediaFilesSelected(input) {
    if (!input || !input.files || input.files.length === 0) return;

    const newFiles = Array.from(input.files);
    input.value = '';

    for (const file of newFiles) {
        const processedFile = await compressImageFile(file);
        const exists = selectedMediaFiles.some(f => f.name === processedFile.name && f.size === processedFile.size);
        if (!exists) {
            selectedMediaFiles.push(processedFile);
        }
    }

    syncMediaFilesToForm();
}

function removeMediaFileItem(index) {
    if (index >= 0 && index < selectedMediaFiles.length) {
        selectedMediaFiles.splice(index, 1);
        syncMediaFilesToForm();
    }
}

function clearAllSelectedMedia() {
    selectedMediaFiles = [];
    syncMediaFilesToForm();
}

function syncMediaFilesToForm() {
    const mainFileInput = document.getElementById('newsfeed-input-files');
    const clearBtn = document.getElementById('clearAllMediaBtn');
    const previewContainer = document.getElementById('newsfeed-post-preview');
    const previewGrid = document.getElementById('newsfeed-preview-grid');
    const liveBox = document.getElementById('live-preview-media-box');
    const liveGrid = document.getElementById('live-preview-media-grid');

    if (mainFileInput) {
        const dt = new DataTransfer();
        selectedMediaFiles.forEach(file => dt.items.add(file));
        mainFileInput.files = dt.files;
    }

    if (clearBtn) {
        clearBtn.style.display = selectedMediaFiles.length > 0 ? 'inline-flex' : 'none';
    }

    if (selectedMediaFiles.length === 0) {
        if (previewContainer) previewContainer.style.display = 'none';
        if (previewGrid) previewGrid.innerHTML = '';
        if (liveBox) liveBox.style.display = 'none';
        if (liveGrid) liveGrid.innerHTML = '';
        return;
    }

    if (previewContainer) previewContainer.style.display = 'block';

    const total = selectedMediaFiles.length;
    let loadedCount = 0;
    const items = new Array(total);

    selectedMediaFiles.forEach((file, index) => {
        const isVideo = file.type.startsWith('video/') || ['mp4', 'mov', 'avi', 'mkv', 'webm'].some(ext => file.name.toLowerCase().endsWith('.' + ext));
        const reader = new FileReader();
        reader.onload = function(e) {
            items[index] = { type: isVideo ? 'video' : 'image', url: e.target.result, fileName: file.name };
            loadedCount++;
            if (loadedCount === total) {
                renderInteractiveMediaPreview(previewGrid, items, true);
                if (liveBox && liveGrid) {
                    liveBox.style.display = 'block';
                    renderInteractiveMediaPreview(liveGrid, items, false);
                }
            }
        };
        reader.readAsDataURL(file);
    });
}

function renderInteractiveMediaPreview(grid, items, showDeleteButtons = true) {
    if (!grid) return;
    grid.innerHTML = '';
    if (!items || !items.length) return;

    const wrapper = document.createElement('div');
    wrapper.style.cssText = 'width: 100%; border-radius: 12px; overflow: hidden; background: #0f172a; border: 1px solid #cbd5e1;';

    const count = items.length;
    if (count === 1) {
        const itemWrapper = document.createElement('div');
        itemWrapper.style.cssText = 'position: relative; width: 100%;';
        itemWrapper.appendChild(createMediaPreviewEl(items[0], '100%', '320px'));
        if (showDeleteButtons) {
            itemWrapper.appendChild(createDeleteButton(0));
        }
        wrapper.appendChild(itemWrapper);
    } else {
        const row = document.createElement('div');
        row.style.cssText = 'display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 6px; padding: 6px; max-height: 360px; overflow-y: auto;';

        items.forEach((item, idx) => {
            const itemWrapper = document.createElement('div');
            itemWrapper.style.cssText = 'position: relative; width: 100%; height: 140px; border-radius: 8px; overflow: hidden; background: #1e293b;';
            itemWrapper.appendChild(createMediaPreviewEl(item, '100%', '100%'));
            if (showDeleteButtons) {
                itemWrapper.appendChild(createDeleteButton(idx));
            }
            row.appendChild(itemWrapper);
        });
        wrapper.appendChild(row);
    }

    grid.appendChild(wrapper);
}

function createDeleteButton(index) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        removeMediaFileItem(index);
    };
    btn.style.cssText = 'position: absolute; top: 6px; right: 6px; width: 28px; height: 28px; border-radius: 50%; background: rgba(239, 68, 68, 0.95); color: #ffffff; border: 2px solid #ffffff; font-weight: 900; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 50; box-shadow: 0 4px 10px rgba(0,0,0,0.4); font-family: sans-serif; transition: transform 0.15s ease;';
    btn.title = 'Xóa tệp này';
    btn.innerHTML = '✕';
    btn.onmouseover = function() { btn.style.transform = 'scale(1.15)'; };
    btn.onmouseout = function() { btn.style.transform = 'scale(1)'; };
    return btn;
}

function createMediaPreviewEl(item, width, height) {
    if (item.type === 'video') {
        const video = document.createElement('video');
        video.src = item.url;
        video.controls = true;
        video.style.cssText = `width: ${width}; height: ${height}; object-fit: cover; display: block; border-radius: 4px; background: #000;`;
        return video;
    } else {
        const img = document.createElement('img');
        img.src = item.url;
        img.style.cssText = `width: ${width}; height: ${height}; object-fit: cover; display: block; border-radius: 4px;`;
        return img;
    }
}

function openNewsfeedPostModal() {
    const m = document.getElementById('addNewsfeedPostModal');
    if (m) {
        m.classList.add('show');
        m.style.display = 'flex';
        updateLivePostPreview();
    }
}

function closeNewsfeedPostModal() {
    const m = document.getElementById('addNewsfeedPostModal');
    if (m) {
        m.classList.remove('show');
        m.style.display = 'none';
    }
}

async function handleNewsfeedPostSubmit(e, form) {
    e.preventDefault();
    
    const submitBtn = form.querySelector('.fb-modal-submit-btn');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Đăng bài viết';
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '🚀 Đang tải media lên...';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const uploadedImageUrls = [];

    // Nâng cấp: Tự động chia đợt tải ảnh (Batch Uploading 5 tệp/lần) để vượt qua giới hạn max_file_uploads = 20 của PHP server
    if (typeof selectedMediaFiles !== 'undefined' && selectedMediaFiles.length > 0) {
        const batchSize = 5;
        const totalFiles = selectedMediaFiles.length;
        
        for (let i = 0; i < totalFiles; i += batchSize) {
            const chunk = selectedMediaFiles.slice(i, i + batchSize);
            if (submitBtn) {
                submitBtn.innerHTML = `🚀 Đang tải lên ${Math.min(i + batchSize, totalFiles)}/${totalFiles} tệp...`;
            }
            
            const batchFormData = new FormData();
            chunk.forEach(file => batchFormData.append('files[]', file));
            batchFormData.append('folder', 'education');

            try {
                const uploadRes = await fetch('/api/upload-media', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json'
                    },
                    body: batchFormData
                });
                
                const uploadData = await uploadRes.json();
                if (uploadData && uploadData.success && Array.isArray(uploadData.files)) {
                    uploadData.files.forEach(f => {
                        if (f && f.url) uploadedImageUrls.push(f.url);
                    });
                }
            } catch (err) {
                console.error('Batch upload error:', err);
            }
        }
    }

    if (submitBtn) {
        submitBtn.innerHTML = '🚀 Đang tạo bài viết...';
    }

    const formData = new FormData(form);
    formData.delete('images[]');
    formData.delete('images');

    // Nạp toàn bộ danh sách URL đã upload thành công (bảo toàn đủ 100% 33, 50 hay 100 ảnh)
    if (uploadedImageUrls.length > 0) {
        uploadedImageUrls.forEach(url => {
            formData.append('image_urls[]', url);
        });
    }

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async res => {
        let data = null;
        try {
            data = await res.json();
        } catch (e) {
            data = null;
        }

        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }

        if (res.ok && data && (data.success || data.post)) {
            closeNewsfeedPostModal();
            form.reset();
            clearAllSelectedMedia();
            updateLivePostPreview();

            if (typeof window.showToast === 'function') {
                window.showToast('✅ Đăng bài viết mới thành công!', 'success');
            } else if (typeof showToastNotification === 'function') {
                showToastNotification('✅ Đăng bài viết mới thành công!');
            }

            setTimeout(() => window.location.reload(), 800);
        } else {
            let errorMsg = 'Có lỗi xảy ra, vui lòng thử lại.';
            if (data) {
                if (data.message) errorMsg = data.message;
                else if (data.error) errorMsg = data.error;
                else if (data.errors) {
                    const errList = Object.values(data.errors).flat().join('\n');
                    if (errList) errorMsg = errList;
                }
            } else if (res.status === 413) {
                errorMsg = 'Dung lượng tổng của ảnh/video vượt quá giới hạn của máy chủ!';
            } else if (res.status === 422) {
                errorMsg = 'Vui lòng nhập đầy đủ tiêu đề và nội dung bài viết.';
            }

            if (typeof window.showToast === 'function') {
                window.showToast(errorMsg, 'error');
            } else {
                alert(errorMsg);
            }
        }
    })
    .catch(err => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
        console.error('Submit post error:', err);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('post');
    if (postId) {
        const targetPost = document.getElementById('post-' + postId);
        if (targetPost) {
            targetPost.style.borderColor = '#0284c7';
            targetPost.style.boxShadow = '0 0 0 4px rgba(2, 132, 199, 0.25), 0 8px 30px rgba(0,0,0,0.12)';
            setTimeout(() => {
                targetPost.style.borderColor = '#e2e8f0';
                targetPost.style.boxShadow = '0 4px 20px rgba(0,0,0,0.05)';
            }, 3500);
        }
    }
});
</script>
@endsection
