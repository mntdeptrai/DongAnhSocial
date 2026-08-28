@extends('layouts.app')

@section('title', 'Trang Cá Nhân & Quản Lý Trường - ' . (optional($school)->standardized_name ?: $user->name))
@section('og_image', optional($school)->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=1200&q=80')

@section('content')
<link rel="stylesheet" href="{{ asset('css/facebook-feed.css') }}?v={{ file_exists(public_path('css/facebook-feed.css')) ? filemtime(public_path('css/facebook-feed.css')) : '1.0.0' }}">
<script defer src="{{ asset('js/facebook-feed.js') }}?v={{ file_exists(public_path('js/facebook-feed.js')) ? filemtime(public_path('js/facebook-feed.js')) : '1.0.0' }}"></script>
<script>
    function profileData() {
        return {
            showEditModal: false,
            showPasswordModal: false,
            activeTab: 'overview',
            otpCooldown: 0,
            cooldownText: 'Gửi mã OTP',
            otpFeedback: '',
            otpFeedbackType: '',
            sendOtp() {
                if (this.otpCooldown > 0) return;
                this.otpFeedback = 'Đang gửi mã OTP đến email...';
                this.otpFeedbackType = 'info';
                
                fetch('/profile/password/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.otpFeedback = data.message || 'Mã OTP đã được gửi đến email của bạn!';
                        this.otpFeedbackType = 'success';
                        this.startCooldown(60);
                    } else {
                        this.otpFeedback = data.message || 'Không thể gửi mã OTP. Vui lòng thử lại!';
                        this.otpFeedbackType = 'error';
                    }
                })
                .catch(() => {
                    this.otpFeedback = 'Có lỗi xảy ra khi kết nối máy chủ!';
                    this.otpFeedbackType = 'error';
                });
            },
            startCooldown(seconds) {
                this.otpCooldown = seconds;
                const timer = setInterval(() => {
                    this.otpCooldown--;
                    if (this.otpCooldown > 0) {
                        this.cooldownText = `Thử lại (${this.otpCooldown}s)`;
                    } else {
                        this.cooldownText = 'Gửi lại mã OTP';
                        clearInterval(timer);
                    }
                }, 1000);
            }
        }
    }
</script>

<style>
    /* ==========================================================
       REAL-DATA PURE DYNAMIC PROFILE SYSTEM (NO MOCKDATA)
       ========================================================== */
    .pro-root {
        background-color: #f0f4f9;
        min-height: 100vh;
        font-family: 'Plus Jakarta Sans', 'Be Vietnam Pro', sans-serif;
        color: #1e293b;
        padding-bottom: 60px;
    }

    .pro-wrap {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Header Card */
    .pro-header-card {
        background: #ffffff;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        border: 1px solid #e2e8f0;
        margin-top: 16px;
        margin-bottom: 24px;
    }

    .pro-cover-box {
        position: relative;
        height: 300px;
        width: 100%;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }

    .pro-cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pro-cover-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(10px);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 12px;
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .pro-cover-btn:hover {
        background: rgba(15, 23, 42, 0.75);
    }

    .pro-info-bar {
        padding: 0 32px 24px 32px;
        position: relative;
    }

    .pro-info-main {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 24px;
    }

    .pro-left-details {
        display: flex;
        align-items: flex-end;
        gap: 24px;
        flex-wrap: wrap;
    }

    .pro-avatar-box {
        position: relative;
        width: 140px;
        height: 140px;
        border-radius: 24px;
        margin-top: -65px;
        border: 4px solid #ffffff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        background: #ffffff;
        flex-shrink: 0;
    }

    .pro-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 20px;
    }

    .pro-avatar-badge {
        position: absolute;
        bottom: 6px;
        right: 6px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #2563eb;
        color: #ffffff;
        border: 2.5px solid #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        cursor: pointer;
    }

    .pro-name-title {
        font-size: 1.85rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
        letter-spacing: -0.02em;
    }

    .pro-verify-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #b45309 100%);
        color: #ffffff;
        border-radius: 50%;
        font-size: 0.8rem;
        font-weight: 800;
        box-shadow: 0 3px 8px rgba(245, 158, 11, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.8);
        border: 1.5px solid #ffffff;
        transition: transform 0.2s ease;
    }
    .pro-verify-badge:hover {
        transform: scale(1.15) rotate(10deg);
    }

    .pro-subtitle {
        font-size: 0.92rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .pro-stats-row {
        display: flex;
        align-items: center;
        gap: 28px;
        flex-wrap: wrap;
    }

    .pro-stat-item {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }

    .pro-stat-num {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
    }

    .pro-stat-label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .pro-actions-group {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pro-btn-primary {
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 10px 22px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        transition: all 0.2s ease;
    }

    .pro-btn-primary:hover {
        background: #1d4ed8;
        color: #ffffff;
    }

    .pro-btn-outline {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pro-btn-outline:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    .pro-btn-icon-only {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        cursor: pointer;
    }

    .pro-tabs-bar {
        display: flex;
        align-items: center;
        gap: 32px;
        border-top: 1px solid #f1f5f9;
        padding-top: 16px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .pro-tab-item {
        font-size: 0.95rem;
        font-weight: 700;
        color: #64748b;
        background: transparent;
        border: none;
        padding: 8px 4px 14px 4px;
        cursor: pointer;
        position: relative;
        white-space: nowrap;
    }

    .pro-tab-item.active {
        color: #1e3a8a;
        font-weight: 800;
    }

    .pro-tab-item.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #f59e0b;
        border-radius: 3px;
    }

    /* Body Grid */
    .pro-body-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 24px;
    }

    @media (max-width: 991px) {
        .pro-body-grid {
            grid-template-columns: 1fr;
        }
        .pro-info-bar {
            padding: 0 16px 20px 16px;
        }
    }

    .pro-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03);
        margin-bottom: 24px;
    }

    .pro-card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #2563eb;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pro-info-list {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .pro-info-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .pro-info-icon {
        font-size: 1.1rem;
        width: 24px;
        flex-shrink: 0;
    }

    .pro-info-text {
        display: flex;
        flex-direction: column;
    }

    .pro-info-lbl {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 600;
    }

    .pro-info-val {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
    }

    .pro-btn-orange {
        background: #f59e0b;
        color: #ffffff;
        border: none;
        border-radius: 14px;
        padding: 12px 20px;
        font-weight: 700;
        font-size: 0.92rem;
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        text-decoration: none;
    }

    .pro-btn-orange:hover {
        background: #d97706;
        color: #ffffff;
    }

    .pro-mini-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 768px) {
        .pro-mini-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .pro-mini-stat-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 18px 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        text-align: center;
    }

    .pro-mini-stat-icon {
        font-size: 1.8rem;
        margin-bottom: 6px;
    }

    .pro-mini-stat-val {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .pro-mini-stat-lbl {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }

    .pro-creator-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03);
        margin-bottom: 24px;
    }

    .pro-creator-top {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .pro-creator-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
    }

    .pro-creator-input {
        flex: 1;
        background: #f1f5f9;
        border: none;
        border-radius: 100px;
        padding: 12px 20px;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        text-align: left;
    }

    .pro-creator-actions {
        display: flex;
        align-items: center;
        justify-content: space-around;
        border-top: 1px solid #f1f5f9;
        padding-top: 14px;
    }

    .pro-creator-btn {
        background: transparent;
        border: none;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .pro-post-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03);
        margin-bottom: 24px;
    }

    .pro-post-img-box {
        position: relative;
        width: 100%;
        max-height: 420px;
        overflow: hidden;
        background: #f1f5f9;
    }

    .post-emoji-chip {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        padding: 5px 12px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .post-emoji-chip:hover {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #2563eb;
    }

    .post-loc-cat-btn {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .post-loc-cat-btn:hover, .post-loc-cat-btn.active {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    .pro-post-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pro-post-tag {
        position: absolute;
        top: 16px;
        left: 16px;
        background: rgba(37, 99, 235, 0.9);
        backdrop-filter: blur(8px);
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .pro-post-body {
        padding: 24px;
    }

    .pro-post-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .pro-post-time {
        font-size: 0.8rem;
        color: #94a3b8;
        margin-bottom: 16px;
    }

    .pro-post-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 20px;
        border-top: 1px solid #f1f5f9;
        padding-top: 14px;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
    }

    .pro-rating-big {
        font-size: 2.8rem;
        font-weight: 900;
        color: #d97706;
        line-height: 1;
        margin-bottom: 4px;
    }

    .pro-stars {
        color: #f59e0b;
        font-size: 1.1rem;
        letter-spacing: 2px;
        margin-bottom: 4px;
    }

    .pro-tags-flex {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pro-tag-pill {
        background: #eff6ff;
        color: #2563eb;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.82rem;
        font-weight: 600;
    }

    /* Empty state card */
    .pro-empty-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 40px 20px;
        text-align: center;
        border: 1px dashed #cbd5e1;
        margin-bottom: 24px;
    }

    /* Modals Overlay */
    .pf-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(6px);
        z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px;
    }
    .pf-modal-box {
        background: #ffffff; border-radius: 24px; padding: 28px; width: 100%; max-width: 480px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative;
    }
    .pf-modal-close {
        position: absolute; top: 16px; right: 16px; background: #f1f5f9; border: none;
        width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-weight: 800; color: #64748b; cursor: pointer;
    }
    .pf-form-group { margin-bottom: 16px; text-align: left; }
    .pf-form-label { font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: block; }
    .pf-form-input {
        width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 12px; font-size: 0.9rem;
    }
</style>

<div class="pro-root" x-data="profileData()">
    <div class="pro-wrap">

        @php
            $avgScore = optional($school)->rating ?: ($reviews->avg('rating') ?: 5.0);
            $star5Count = $reviews->where('rating', 5)->count();
            $star4Count = $reviews->where('rating', 4)->count();
            $star3Count = $reviews->where('rating', 3)->count();
            $totalRev = $reviews->count();
            
            $mergedComp = optional($school)->merged_components ?? [];
            $storyData = optional($school)->storytelling_data ?? [];
            $mergedSchool = $storyData['mergedSchool'] ?? [];

            $staffCount = optional($school)->total_teachers ?: ($mergedSchool['total_staff'] ?? (count($mergedComp) > 0 ? array_sum(array_column($mergedComp, 'staff')) : null));
            $studentsCount = optional($school)->total_students ?: ($mergedSchool['total_students'] ?? (count($mergedComp) > 0 ? array_sum(array_column($mergedComp, 'students')) : null));
            $foundedYr = optional($school)->founded_year ?: ($mergedSchool['founded_year'] ?? null);
            $awardsCount = optional($school)->awards_count ?: ($mergedSchool['awards_count'] ?? null);

            $allPhotoUrls = collect();
            if (isset($photos) && $photos->count() > 0) {
                foreach ($photos as $ph) {
                    if (!empty($ph->photo_url)) $allPhotoUrls->push($ph->photo_url);
                    elseif (!empty($ph->image_path)) $allPhotoUrls->push($ph->image_path);
                }
            }
            if (isset($posts) && $posts->count() > 0) {
                foreach ($posts as $p) {
                    if (!empty($p->all_images)) {
                        foreach ($p->all_images as $img) {
                            $allPhotoUrls->push($img);
                        }
                    }
                }
            }
            $allPhotoUrls = $allPhotoUrls->unique()->filter()->values();

            $currentUserId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
            $isOwner = $currentUserId && ($currentUserId == $user->id);

            $friendshipStatus = 'none';
            $friendshipId = null;
            $isFollowing = false;

            if ($currentUserId && !$isOwner) {
                $friendship = \App\Models\Friendship::where(function($q) use ($currentUserId, $user) {
                    $q->where('user_id', $currentUserId)->where('friend_id', $user->id);
                })->orWhere(function($q) use ($currentUserId, $user) {
                    $q->where('user_id', $user->id)->where('friend_id', $currentUserId);
                })->first();

                if ($friendship) {
                    $friendshipId = $friendship->id;
                    if ($friendship->status === 'accepted') {
                        $friendshipStatus = 'accepted';
                        $isFollowing = true;
                    } elseif ($friendship->status === 'pending') {
                        $friendshipStatus = ($friendship->user_id == $currentUserId) ? 'pending_sent' : 'pending_received';
                        if ($friendship->user_id == $currentUserId) {
                            $isFollowing = true;
                        }
                    }
                }
            }
        @endphp

        <!-- 1. TOP HERO HEADER & COVER PHOTO CARD -->
        @include('auth.partials.profile-header')

        <!-- 2. TWO-COLUMN MAIN CONTENT BODY -->
        <div class="pro-body-grid">

            <!-- LEFT SIDEBAR COLUMN (340px) -->
            @include('auth.partials.profile-sidebar')

            <!-- RIGHT MAIN COLUMN -->
            <div>
                <!-- ================= TAB 1: OVERVIEW & TAB 2: POSTS ================= -->
                <div x-show="activeTab === 'overview' || activeTab === 'posts'">
                    <!-- Mini Top Key Stat Cards (4 Cards Grid - Tailored by Role) -->
                    @include('auth.partials.profile-mini-stats')

                    <!-- 2. Post Creator Card Trigger -->
                    <div class="pro-creator-card">
                        <div class="pro-creator-top">
                            <img src="{{ optional($school)->image_path ?: ($user->avatar ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80') }}" class="pro-creator-avatar" alt="Avatar">
                            <button type="button" onclick="openModal('addPostModal')" class="pro-creator-input" style="text-align: left; background: #f1f5f9; border: none; outline: none; cursor: pointer; width: 100%;">
                                Chia sẻ điều gì đó...
                            </button>
                        </div>
                        <div class="pro-creator-actions">
                            <button type="button" onclick="openModal('addPostModal')" class="pro-creator-btn">🖼️ Ảnh & Video</button>
                            <button type="button" onclick="openModal('addPostModal')" class="pro-creator-btn">😃 Cảm xúc</button>
                            <button type="button" onclick="openModal('addPostModal')" class="pro-creator-btn">🎈 Check-in</button>
                            <button type="button" onclick="openModal('addPostModal')" class="pro-creator-btn">📅 Sự kiện</button>
                        </div>
                    </div>

                    <!-- 3. Posts Stream (Pure Dynamic DB Items with Facebook Grid & Lightbox) -->
                    @if($posts->isEmpty())
                        <div class="pro-empty-card">
                            <div style="font-size: 3rem; margin-bottom: 12px;">📰</div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Chưa có bài viết nào</h4>
                            <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 20px;">Đăng thông báo & hình ảnh hoạt động lên bảng tin!</p>
                            @if(Auth::check())
                                <button type="button" onclick="openModal('addPostModal')" class="pro-btn-primary" style="border-radius: 100px; cursor: pointer;">
                                    + Đăng bài viết mới
                                </button>
                            @endif
                        </div>
                    @else
                        @foreach($posts as $p)
                            @php
                                $imgs = $p->all_images;
                                $imgCount = count($imgs);
                            @endphp
                            <article class="fb-post-card mb-4" style="background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box;">
                                <!-- Facebook Post Header -->
                                <div class="fb-post-header" style="box-sizing: border-box; width: 100%; display: flex; align-items: center; justify-content: space-between; position: relative;">
                                    <div class="fb-post-author-box">
                                        <img src="{{ optional($school)->image_path ?: ($user->avatar ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80') }}" class="fb-user-avatar" alt="{{ optional($school)->standardized_name }}">
                                        <div>
                                            <h4 class="fb-post-author-name" style="word-break: break-word; overflow-wrap: anywhere; display: inline-flex; align-items: center; gap: 4px;">
                                                {{ optional($school)->standardized_name ?: $user->name }}
                                                @if($user->isAdmin() || $user->role === 'admin')
                                                    <span title="Tài khoản Quản trị viên (Admin)" style="color: #ef4444; font-size: 0.95rem; filter: drop-shadow(0 0 4px rgba(239, 68, 68, 0.4));">⭐</span>
                                                @elseif($user->is_verified)
                                                    <span class="pro-verify-badge" style="font-size: 0.85rem;" title="Tài khoản xịn đã xác minh bởi Admin ⭐">⭐</span>
                                                @endif
                                            </h4>
                                            <div class="fb-post-subtext">
                                                <span>{{ $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong' }}</span>
                                                <span>•</span>
                                                <span>🌐 Công khai</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nút 3 chấm cho chủ bài viết hoặc Admin -->
                                    @if($isOwner || (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->role === 'admin')))
                                        <div style="position: relative;" x-data="{ openOptions: false }">
                                            <button type="button" @click="openOptions = !openOptions" @click.away="openOptions = false" style="background: none; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; padding: 4px 10px; border-radius: 50%; transition: background 0.2s; line-height: 1;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'" title="Tùy chọn bài viết">
                                                •••
                                            </button>
                                            <div x-show="openOptions" x-cloak style="position: absolute; right: 0; top: 100%; margin-top: 4px; background: #ffffff; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; width: 180px; z-index: 50; overflow: hidden; padding: 6px 0;">
                                                <button type="button" onclick="editPostItem({{ json_encode($p) }})" style="width: 100%; text-align: left; background: none; border: none; padding: 10px 16px; font-size: 0.9rem; font-weight: 600; color: #1e293b; cursor: pointer; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='none'">
                                                    ✏️ Chỉnh sửa bài
                                                </button>
                                                <form action="/posts/{{ $p->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');" style="margin: 0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="width: 100%; text-align: left; background: none; border: none; padding: 10px 16px; font-size: 0.9rem; font-weight: 600; color: #ef4444; cursor: pointer; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
                                                        🗑️ Xóa bài viết
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Post Content Text -->
                                <div class="fb-post-text" style="word-break: break-word; overflow-wrap: anywhere; max-width: 100%; box-sizing: border-box;">
                                    <strong class="d-block mb-1 text-dark" style="font-size: 1.05rem; word-break: break-word; overflow-wrap: anywhere; line-height: 1.45;">🌸 {{ $p->name }}</strong>
                                    <div style="word-break: break-word; overflow-wrap: anywhere; line-height: 1.6;">{!! \App\Helpers\TextHelper::linkify($p->description) !!}</div>
                                </div>

                                <!-- Facebook Multi-Photo Grid System (1, 2, 3, 4, 5+ photos) -->
                                @if($imgCount === 1)
                                    <div class="fb-photo-grid fb-grid-1" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)">
                                        <img src="{{ $imgs[0] }}" alt="{{ $p->name }}">
                                    </div>
                                @elseif($imgCount === 2)
                                    <div class="fb-photo-grid fb-grid-2">
                                        <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name }}">
                                    </div>
                                @elseif($imgCount === 3)
                                    <div class="fb-photo-grid fb-grid-3">
                                        <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name }}">
                                        <div class="fb-grid-3-col-right">
                                            <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name }}">
                                            <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name }}">
                                        </div>
                                    </div>
                                @elseif($imgCount === 4)
                                    <div class="fb-photo-grid fb-grid-4">
                                        <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name }}">
                                        <div class="fb-grid-4-col-right">
                                            <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name }}">
                                            <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name }}">
                                            <img src="{{ $imgs[3] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 3)" alt="{{ $p->name }}">
                                        </div>
                                    </div>
                                @elseif($imgCount >= 5)
                                    <div class="fb-photo-grid fb-grid-5">
                                        <div class="fb-grid-5-row-top">
                                            <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name }}">
                                            <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name }}">
                                        </div>
                                        <div class="fb-grid-5-row-bottom">
                                            <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name }}">
                                            <img src="{{ $imgs[3] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 3)" alt="{{ $p->name }}">
                                            <div class="fb-photo-thumb-box" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 4)">
                                                <img src="{{ $imgs[4] }}" alt="{{ $p->name }}">
                                                @if($imgCount > 5)
                                                    <div class="fb-photo-more-overlay">+{{ $imgCount - 5 }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Video Player Section if post contains videos -->
                                @php
                                    $vids = $p->all_videos;
                                @endphp
                                @if(!empty($vids))
                                    <div class="fb-post-video-container" style="width: 100%; border-radius: 16px; overflow: hidden; margin-top: 10px; background: #000; display: flex; flex-direction: column; gap: 8px;">
                                        @foreach($vids as $vidUrl)
                                            <video src="{{ $vidUrl }}" controls preload="metadata" style="width: 100%; max-height: 480px; display: block; border-radius: 12px; background: #0f172a;"></video>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Facebook Post Stats Bar -->
                                <div class="fb-post-stats">
                                    <div id="post-likes-count-{{ $p->id }}" onclick="showPostLikers({{ $p->id }}, 'post')" style="cursor:pointer;" title="Xem ai đã thích">👍 {{ $p->real_likes_count ?? $p->likes_count ?? 0 }} lượt thích</div>
                                    <div>💬 {{ $p->real_comments_count ?? 0 }} bình luận • <span id="post-shares-count-{{ $p->id }}">{{ $p->shares_count ?? $p->real_shares_count ?? 0 }}</span> chia sẻ</div>
                                </div>

                                <!-- Facebook Footer Actions Bar -->
                                <div class="fb-post-actions">
                                    <button class="fb-action-btn {{ ($p->is_liked ?? false) ? 'active' : '' }}" 
                                            id="post-like-btn-{{ $p->id }}" 
                                            onclick="togglePostLike(this, {{ $p->id }})"
                                            style="{{ ($p->is_liked ?? false) ? 'color: #2563eb; font-weight: 700;' : '' }}">
                                        👍 {{ ($p->is_liked ?? false) ? 'Đã thích' : 'Thích' }}
                                    </button>
                                    <button class="fb-action-btn" onclick="toggleComments({{ $p->id }}, this)">💬 Bình luận</button>
                                    <button class="fb-action-btn" onclick="shareFbPost({{ $p->id }}, {{ json_encode($p->name) }}, {{ json_encode($imgs) }}, '{{ $p->hashid ?? $p->id }}')">🔄 Chia sẻ</button>
                                </div>
                            </article>
                        @endforeach
                    @endif

                </div>

                <!-- ================= TAB 3: PHOTOS GALLERY ================= -->
                <div x-show="activeTab === 'photos'" x-cloak style="display: none;">
                    <div class="pro-card">
                        <div class="pro-card-title" style="color: #0f172a; margin-bottom: 20px;">
                            <span>📷 Thư viện ảnh ({{ $allPhotoUrls->count() }} hình ảnh)</span>
                        </div>
                        @if($allPhotoUrls->isEmpty())
                            <div style="text-align: center; color: #64748b; font-size: 0.95rem; padding: 40px 20px;">
                                <div style="font-size: 3rem; margin-bottom: 12px;">🖼️</div>
                                <strong>Chưa có hình ảnh nào trong thư viện</strong>
                                <p style="margin-top: 6px; font-size: 0.85rem;">Hình ảnh từ các bài viết hoặc ảnh tải lên sẽ tự động hiển thị tại đây.</p>
                            </div>
                        @else
                            <div id="photos-gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px;">
                                @foreach($allPhotoUrls as $index => $imgUrl)
                                    <div style="position: relative; border-radius: 14px; overflow: hidden; aspect-ratio: 1; background: #f1f5f9; cursor: pointer; border: 1px solid #e2e8f0; transition: transform 0.2s, box-shadow 0.2s;"
                                         onclick="openPostLightboxGallery({{ json_encode($allPhotoUrls->toArray()) }}, {{ $index }})"
                                         onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';"
                                         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                        <img src="{{ $imgUrl }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Photo {{ $index + 1 }}">
                                        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 1.5rem; opacity: 0; transition: opacity 0.2s;"
                                             onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                                            🔍
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ================= TAB 4: ABOUT / GIỚI THIỆU ================= -->
                <div x-show="activeTab === 'about'" x-cloak style="display: none;">
                    @if($user->isPrincipal() || $user->role === 'principal')
                        <div class="pro-card" style="margin-bottom: 20px;">
                            <div class="pro-card-title" style="color: #0f172a; margin-bottom: 16px;">
                                <span>🏫 Thông tin chi tiết địa điểm</span>
                            </div>
                            <ul class="pro-info-list" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">🏫</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Loại hình</span>
                                        <span class="pro-info-val">{{ optional(optional($school)->category)->name ?: 'Chưa cập nhật' }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">📍</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Địa chỉ</span>
                                        <span class="pro-info-val">{{ optional($school)->address ?: 'Chưa cập nhật' }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">📞</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Điện thoại</span>
                                        <span class="pro-info-val">{{ optional($school)->phone ?: ($user->phone ?: 'Chưa cập nhật') }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">🌐</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Website</span>
                                        @if(optional($school)->website)
                                            <a href="{{ str_starts_with(optional($school)->website, 'http') ? optional($school)->website : 'https://' . optional($school)->website }}" target="_blank" class="pro-info-val" style="color: #2563eb;">{{ optional($school)->website }}</a>
                                        @else
                                            <span class="pro-info-val">Chưa cập nhật</span>
                                        @endif
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">🕒</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Giờ mở cửa</span>
                                        <span class="pro-info-val">{{ optional($school)->opening_hours ?: 'Chưa cập nhật' }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">📍</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Khu vực</span>
                                        <span class="pro-info-val">{{ optional($school)->commune ? optional($school)->commune->name : 'Chưa cập nhật' }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="pro-card" style="margin-bottom: 20px;">
                            <div class="pro-card-title" style="color: #0f172a; margin-bottom: 16px;">
                                <span>📊 Quy mô & Thành tựu</span>
                            </div>
                            <div class="pro-mini-stats-grid">
                                <div class="pro-mini-stat-card">
                                    <div class="pro-mini-stat-icon">📅</div>
                                    <div class="pro-mini-stat-val">{{ $foundedYr ?: 'Chưa cập nhật' }}</div>
                                    <div class="pro-mini-stat-lbl">Năm thành lập</div>
                                </div>
                                <div class="pro-mini-stat-card">
                                    <div class="pro-mini-stat-icon">👩‍🏫</div>
                                    <div class="pro-mini-stat-val">{{ $staffCount !== null ? $staffCount . ' người' : 'Chưa cập nhật' }}</div>
                                    <div class="pro-mini-stat-lbl">Giáo viên</div>
                                </div>
                                <div class="pro-mini-stat-card">
                                    <div class="pro-mini-stat-icon">🎒</div>
                                    <div class="pro-mini-stat-val">{{ $studentsCount !== null ? $studentsCount . ' bé' : 'Chưa cập nhật' }}</div>
                                    <div class="pro-mini-stat-lbl">Học sinh</div>
                                </div>
                                <div class="pro-mini-stat-card">
                                    <div class="pro-mini-stat-icon">🏆</div>
                                    <div class="pro-mini-stat-val">{{ $awardsCount !== null ? $awardsCount . ' danh hiệu' : 'Chưa cập nhật' }}</div>
                                    <div class="pro-mini-stat-lbl">Giải thưởng</div>
                                </div>
                            </div>
                        </div>

                        @if(optional($school)->description)
                        <div class="pro-card">
                            <div class="pro-card-title" style="color: #0f172a; margin-bottom: 12px;">
                                <span>📝 Giới thiệu chi tiết</span>
                            </div>
                            <p style="font-size: 0.95rem; color: #334155; line-height: 1.7; margin: 0; white-space: pre-line;">
                                {{ $school->description }}
                            </p>
                        </div>
                        @endif
                    @elseif($user->isSeller() || $user->role === 'seller')
                        <div class="pro-card" style="margin-bottom: 20px;">
                            <div class="pro-card-title" style="color: #0f172a; margin-bottom: 16px;">
                                <span>🛒 Thông tin gian hàng & Kinh doanh</span>
                            </div>
                            <ul class="pro-info-list" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">🛒</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Tên gian hàng</span>
                                        <span class="pro-info-val">{{ $stall ? $stall->stall_name : 'Cơ sở kinh doanh' }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">📞</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Hotline kinh doanh</span>
                                        <span class="pro-info-val">{{ $user->phone ?: 'Chưa cập nhật' }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">✉️</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Email liên hệ</span>
                                        <span class="pro-info-val">{{ $user->email ?: 'Chưa cập nhật' }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">📅</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Ngày tham gia</span>
                                        <span class="pro-info-val">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Mới tham gia' }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="pro-card" style="margin-bottom: 20px;">
                            <div class="pro-card-title" style="color: #0f172a; margin-bottom: 16px;">
                                <span>👤 Thông tin cá nhân</span>
                            </div>
                            <ul class="pro-info-list" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">👤</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Họ và tên</span>
                                        <span class="pro-info-val">{{ $user->name }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">✉️</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Email</span>
                                        <span class="pro-info-val">{{ $user->email ?: 'Chưa cập nhật' }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">📞</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Điện thoại</span>
                                        <span class="pro-info-val">{{ $user->phone ?: 'Chưa cập nhật' }}</span>
                                    </div>
                                </li>
                                <li class="pro-info-row">
                                    <span class="pro-info-icon">📅</span>
                                    <div class="pro-info-text">
                                        <span class="pro-info-lbl">Ngày tham gia</span>
                                        <span class="pro-info-val">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Mới tham gia' }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- ================= TAB 5: REVIEWS / ĐÁNH GIÁ ================= -->
                @if(($user->isPrincipal() || $user->role === 'principal') || (($user->isSeller() || $user->role === 'seller') && !empty($stall)))
                <div x-show="activeTab === 'reviews'" x-cloak style="display: none;">
                    <div class="pro-card">
                        <div class="pro-card-title" style="color: #0f172a; margin-bottom: 20px;">
                            <span>⭐ Đánh giá từ phụ huynh & cộng đồng ({{ $totalRev }})</span>
                        </div>

                        <!-- Rating summary header -->
                        <div style="display: flex; align-items: center; gap: 24px; padding: 20px; background: #f8fafc; border-radius: 16px; margin-bottom: 24px;">
                            <div style="text-align: center; padding-right: 24px; border-right: 1px solid #e2e8f0;">
                                <div style="font-size: 3rem; font-weight: 900; color: #0f172a; line-height: 1;">{{ number_format($avgScore, 1) }}</div>
                                <div style="color: #f59e0b; font-size: 1.2rem; margin: 4px 0;">
                                    @for($i=1; $i<=5; $i++)
                                        {{ $i <= round($avgScore) ? '★' : '☆' }}
                                    @endfor
                                </div>
                                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">{{ $totalRev }} nhận xét</div>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                    <span style="font-size: 0.82rem; font-weight: 700; color: #475569; width: 40px;">5 sao</span>
                                    <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 100px; overflow: hidden;">
                                        <div style="height: 100%; background: #f59e0b; width: {{ $totalRev > 0 ? ($star5Count / $totalRev * 100) : 0 }}%;"></div>
                                    </div>
                                    <span style="font-size: 0.82rem; color: #64748b; font-weight: 600; width: 30px; text-align: right;">{{ $star5Count }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                    <span style="font-size: 0.82rem; font-weight: 700; color: #475569; width: 40px;">4 sao</span>
                                    <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 100px; overflow: hidden;">
                                        <div style="height: 100%; background: #f59e0b; width: {{ $totalRev > 0 ? ($star4Count / $totalRev * 100) : 0 }}%;"></div>
                                    </div>
                                    <span style="font-size: 0.82rem; color: #64748b; font-weight: 600; width: 30px; text-align: right;">{{ $star4Count }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 0.82rem; font-weight: 700; color: #475569; width: 40px;">3 sao</span>
                                    <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 100px; overflow: hidden;">
                                        <div style="height: 100%; background: #f59e0b; width: {{ $totalRev > 0 ? ($star3Count / $totalRev * 100) : 0 }}%;"></div>
                                    </div>
                                    <span style="font-size: 0.82rem; color: #64748b; font-weight: 600; width: 30px; text-align: right;">{{ $star3Count }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews List -->
                        @if($reviews->isEmpty())
                            <div style="text-align: center; color: #64748b; font-size: 0.95rem; padding: 30px 20px;">
                                <div style="font-size: 2.5rem; margin-bottom: 10px;">💬</div>
                                <strong>Chưa có nhận xét nào cho địa điểm này</strong>
                                <p style="margin-top: 4px; font-size: 0.85rem;">Các đánh giá từ phụ huynh và cộng đồng sẽ xuất hiện tại đây.</p>
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                @foreach($reviews as $rev)
                                    <div style="display: flex; align-items: flex-start; gap: 14px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37,99,235,0.2);">
                                            {{ mb_substr(optional($rev->user)->name ?: 'U', 0, 1) }}
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                                <div style="font-size: 0.98rem; font-weight: 800; color: #0f172a;">
                                                    {{ optional($rev->user)->name ?: 'Người dùng ẩn danh' }}
                                                </div>
                                                <span style="font-size: 0.78rem; color: #94a3b8;">
                                                    {{ $rev->created_at ? $rev->created_at->diffForHumans() : '' }}
                                                </span>
                                            </div>
                                            <div style="color: #f59e0b; font-size: 0.88rem; margin-bottom: 6px;">
                                                @for($s=1; $s<=5; $s++)
                                                    {{ $s <= $rev->rating ? '★' : '☆' }}
                                                @endfor
                                            </div>
                                            <p style="font-size: 0.92rem; color: #334155; margin: 0; line-height: 1.6;">
                                                {{ $rev->comment }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>

        </div>

    </div>

    <!-- ====== IMAGE PREVIEW & CONFIRMATION MODAL ====== -->
    <div id="imagePreviewModal" style="display: none;" class="pf-modal-overlay">
        <div class="pf-modal-box" style="max-width: 520px; text-align: center;">
            <button type="button" onclick="closeImagePreviewModal()" class="pf-modal-close">✕</button>
            <div style="margin-bottom: 16px;">
                <h3 id="previewModalTitle" style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">🖼️ Xem trước hình ảnh</h3>
                <p id="previewModalSub" style="font-size: 0.85rem; color: #64748b; margin: 0;">Xem trước hình ảnh trước khi cập nhật chính thức lên trang cá nhân</p>
            </div>

            <!-- Image Preview Box -->
            <div id="previewFrameWrap" style="width: 100%; border-radius: 16px; overflow: hidden; background: #f1f5f9; border: 2px dashed #cbd5e1; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; position: relative;">
                <img id="previewModalImg" src="" style="width: 100%; height: 100%; object-fit: cover; display: block;">
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeImagePreviewModal()" class="pro-btn-outline" style="border-radius: 12px; padding: 10px 22px; cursor: pointer;">
                    ✕ Hủy bỏ
                </button>
                <button type="button" id="confirmUploadBtn" onclick="confirmImageUpload()" class="pro-btn-primary" style="border-radius: 12px; padding: 10px 24px; cursor: pointer;">
                    💾 Cập nhật ảnh
                </button>
            </div>
        </div>
    </div>

    <!-- ====== EDIT PROFILE & PLACE MODAL ====== -->
    <div x-show="showEditModal" x-transition.opacity style="display:none;" class="pf-modal-overlay">
        <div @click.outside="showEditModal = false" x-show="showEditModal" x-transition.scale class="pf-modal-box" style="max-width: 520px; max-height: 90vh; overflow-y: auto;">
            <button type="button" @click="showEditModal = false" class="pf-modal-close">✕</button>
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">📝 Cập nhật thông tin địa điểm</h3>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Chỉnh sửa trực tiếp các thông tin hiển thị tại bảng Thông tin địa điểm.</p>
            </div>
            <form action="/profile" method="POST">
                @csrf
                @method('PUT')

                <div class="pf-form-group">
                    <label class="pf-form-label">🏫 Tên cơ sở / Họ tên</label>
                    <input type="text" name="name" required class="pf-form-input" value="{{ $user->name }}" placeholder="Ví dụ: Trường Mầm non Phúc Lộc">
                </div>

                <div class="pf-form-group">
                    <label class="pf-form-label">📍 Địa chỉ</label>
                    <input type="text" name="address" class="pf-form-input" value="{{ optional($school)->address }}" placeholder="Ví dụ: Thôn Hùng Sơn, Xã Đông Anh, Hà Nội">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="pf-form-group">
                        <label class="pf-form-label">📞 Điện thoại</label>
                        <input type="text" name="phone" class="pf-form-input" value="{{ optional($school)->phone ?: ($user->phone ?: '') }}" placeholder="02438830001">
                    </div>
                    <div class="pf-form-group">
                        <label class="pf-form-label">🌐 Website</label>
                        <input type="text" name="website" class="pf-form-input" value="{{ optional($school)->website }}" placeholder="phucloc.edu.vn">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="pf-form-group">
                        <label class="pf-form-label">🕒 Giờ mở cửa</label>
                        <input type="text" name="opening_hours" class="pf-form-input" value="{{ optional($school)->opening_hours }}" placeholder="07:00 – 17:30">
                    </div>
                    <div class="pf-form-group">
                        <label class="pf-form-label">✉️ Email liên hệ</label>
                        <input type="email" name="email" required class="pf-form-input" value="{{ $user->email }}" placeholder="email@example.com">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" @click="showEditModal = false" class="pro-btn-outline" style="border-radius: 12px; padding: 10px 22px;">Hủy</button>
                    <button type="submit" class="pro-btn-primary" style="border-radius: 12px; padding: 10px 22px;">💾 Lưu cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====== CHANGE PASSWORD MODAL ====== -->
    <div x-show="showPasswordModal" x-transition.opacity style="display:none;" class="pf-modal-overlay">
        <div @click.outside="showPasswordModal = false" x-show="showPasswordModal" x-transition.scale class="pf-modal-box" style="max-width: 480px;">
            <button type="button" @click="showPasswordModal = false" class="pf-modal-close">✕</button>
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">🔑 Thay đổi mật khẩu</h3>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Mã OTP xác thực sẽ được gửi tới email tài khoản: <strong>{{ $user->email }}</strong>.</p>
            </div>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Gửi OTP & Nhập OTP -->
                <div class="pf-form-group">
                    <label class="pf-form-label">Mã xác thực OTP (6 chữ số) <span style="color: #ef4444;">*</span></label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="otp" required maxlength="6" class="pf-form-input" placeholder="Nhập mã OTP 6 chữ số..." style="flex: 1; letter-spacing: 2px; font-weight: 700;">
                        <button type="button" @click="sendOtp()" :disabled="otpCooldown > 0" class="pro-btn-outline" style="border-radius: 12px; padding: 10px 14px; font-size: 0.84rem; white-space: nowrap; font-weight: 600;" :style="otpCooldown > 0 ? 'opacity: 0.6; cursor: not-allowed;' : ''" x-text="cooldownText"></button>
                    </div>
                    <template x-if="otpFeedback">
                        <div style="margin-top: 6px; font-size: 0.8rem; font-weight: 600;" :style="otpFeedbackType === 'success' ? 'color: #10b981;' : (otpFeedbackType === 'error' ? 'color: #ef4444;' : 'color: #0ea5e9;')" x-text="otpFeedback"></div>
                    </template>
                </div>

                <div class="pf-form-group">
                    <label class="pf-form-label">Mật khẩu mới <span style="color: #ef4444;">*</span></label>
                    <input type="password" name="password" required minlength="6" class="pf-form-input" placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)...">
                </div>

                <div class="pf-form-group">
                    <label class="pf-form-label">Xác nhận mật khẩu mới <span style="color: #ef4444;">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="6" class="pf-form-input" placeholder="Nhập lại mật khẩu mới...">
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" @click="showPasswordModal = false" class="pro-btn-outline" style="border-radius: 12px; padding: 10px 22px;">Hủy</button>
                    <button type="submit" class="pro-btn-primary" style="border-radius: 12px; padding: 10px 22px;">🔒 Đổi mật khẩu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====== EDIT POST MODAL ====== -->
    <div class="sch-modal" id="editPostModal" onclick="if(event.target === this) closeModal('editPostModal')">
        <div class="fb-modal-box">
            <button type="button" onclick="closeModal('editPostModal')" class="sch-close-modal" style="top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; border: none; font-size: 1.1rem; color: #475569; position: absolute; z-index: 10; cursor: pointer;">✕</button>
            <div class="fb-modal-header">
                <h4 class="fb-modal-title">✏️ Chỉnh sửa bài viết</h4>
            </div>
            
            <form id="editPostForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="fb-modal-user-row">
                    <img src="{{ optional($school)->image_path ?: ($user->avatar ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80') }}" class="fb-modal-user-avatar">
                    <div>
                        <div class="fb-modal-user-name">{{ optional($school)->standardized_name ?: $user->name }}</div>
                        <div class="fb-modal-privacy">🌐 Công khai</div>
                    </div>
                </div>

                <div style="padding: 0 16px 12px 16px;">
                    <input type="text" name="name" id="editPostNameInput" required class="fb-modal-title-input" placeholder="Tiêu đề bài viết...">
                </div>

                <div style="padding: 0 16px 16px 16px;">
                    <textarea name="description" id="editPostDescInput" required class="fb-modal-textarea" rows="4" placeholder="Nội dung bài viết..."></textarea>
                </div>

                <div style="padding: 0 16px 16px 16px;">
                    <label style="font-weight: 700; font-size: 0.88rem; color: #475569; display: block; margin-bottom: 6px;">📷 Thêm hình ảnh bổ sung:</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="pf-form-input" style="width: 100%;">
                </div>

                <div class="fb-modal-footer">
                    <button type="submit" class="fb-post-submit-btn">💾 Lưu cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====== CREATE POST MODAL ====== -->
    @if(Auth::check())
    <div class="sch-modal" id="addPostModal" onclick="if(event.target === this) closeModal('addPostModal')">
        <div class="fb-modal-box">
            <button type="button" onclick="closeModal('addPostModal')" class="sch-close-modal" style="top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; border: none; font-size: 1.1rem; color: #475569; position: absolute; z-index: 10; cursor: pointer;">✕</button>
            <div class="fb-modal-header">
                <h4 class="fb-modal-title">Tạo bài viết</h4>
            </div>
            
            <form action="{{ route('principal.posts.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleRealtimePostSubmit(event, this)">
                @csrf
                <input type="hidden" name="eatery_id" value="{{ optional($school)->id }}">

                <!-- User Header Row -->
                <div class="fb-modal-user-row">
                    <img src="{{ optional($school)->image_path ?: ($user->avatar ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80') }}" class="fb-modal-user-avatar">
                    <div class="fb-modal-user-info">
                        <h5 class="fb-modal-user-name" style="display: inline-flex; align-items: center; gap: 4px;">
                            {{ optional($school)->standardized_name ?: $user->name }}
                            @if($user->isAdmin() || $user->role === 'admin')
                                <span title="Tài khoản Quản trị viên (Admin)" style="color: #ef4444; font-size: 0.95rem;">⭐</span>
                            @endif
                        </h5>
                        <div class="fb-modal-badges" style="position: relative; display: flex; gap: 8px;">
                            <!-- Hidden Inputs -->
                            <input type="hidden" name="privacy" id="postPrivacyInput" value="public">

                            <!-- Privacy Dropdown Badge -->
                            <div style="position: relative;">
                                <button type="button" class="fb-modal-badge" id="privacyDropdownBtn" onclick="togglePostDropdown('privacyDropdownMenu')" style="cursor: pointer; border: none; background: #e2e8f0; font-family: inherit; font-size: 0.78rem; padding: 4px 10px; border-radius: 6px; font-weight: 700; color: #334155; display: inline-flex; align-items: center; gap: 4px;">
                                    <span id="privacyDropdownLabel">🌐 Công khai</span> ▾
                                </button>
                                <div id="privacyDropdownMenu" class="fb-post-dropdown-menu" style="display: none; position: absolute; top: 110%; left: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); width: 220px; z-index: 100; padding: 6px;">
                                    <div onclick="selectPostPrivacy('public', '🌐 Công khai')" class="fb-dropdown-item" style="padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                        <span>🌐</span>
                                        <div>
                                            <div>Công khai</div>
                                            <div style="font-size: 0.72rem; font-weight: 500; color: #64748b;">Bất kỳ ai trên Đông Anh Social</div>
                                        </div>
                                    </div>
                                    <div onclick="selectPostPrivacy('friends', '👥 Bạn bè')" class="fb-dropdown-item" style="padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                        <span>👥</span>
                                        <div>
                                            <div>Bạn bè</div>
                                            <div style="font-size: 0.72rem; font-weight: 500; color: #64748b;">Chỉ bạn bè của bạn</div>
                                        </div>
                                    </div>
                                    <div onclick="selectPostPrivacy('private', '🔒 Chỉ mình tôi')" class="fb-dropdown-item" style="padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                        <span>🔒</span>
                                        <div>
                                            <div>Chỉ mình tôi</div>
                                            <div style="font-size: 0.72rem; font-weight: 500; color: #64748b;">Chỉ riêng bạn nhìn thấy</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="fb-modal-body">
                    <input type="text" name="name" required class="fb-modal-title-input" placeholder="Tiêu đề bài viết...">
                    <textarea name="description" id="postModalTextarea" required class="fb-modal-textarea" rows="4" placeholder="{{ optional($school)->standardized_name ?: $user->name }} ơi, bạn đang nghĩ gì thế?"></textarea>

                    <!-- Interactive Helpers Containers -->
                    <div id="postHelperTagContainer" style="display: none; position: relative; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 12px; align-items: center; gap: 8px;">
                        <span style="font-size: 1rem;">🏷️</span>
                        <span style="font-size: 0.85rem; font-weight: 700; color: #475569;">Gắn thẻ:</span>
                        <input type="text" id="postHelperTagInput" placeholder="Nhập tên người bạn..." style="flex: 1; border: none; background: transparent; outline: none; font-size: 0.9rem; font-weight: 600; color: #0f172a;" oninput="onTagFriendsInput(this.value)">
                        <button type="button" onclick="closePostHelper('postHelperTagContainer')" style="border: none; background: none; color: #94a3b8; font-size: 1.1rem; cursor: pointer;">✕</button>

                        <!-- Tag Friends Auto-complete Dropdown Menu -->
                        <div id="tagFriendsDropdown" style="display: none; position: absolute; top: 110%; left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); max-height: 220px; overflow-y: auto; z-index: 200; padding: 6px;"></div>
                    </div>

                    <div id="postHelperEmojiContainer" style="display: none; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a;">😊 Bạn đang cảm thấy thế nào?</span>
                            <button type="button" onclick="closePostHelper('postHelperEmojiContainer')" style="border: none; background: none; color: #94a3b8; font-size: 1rem; cursor: pointer;">✕</button>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <button type="button" class="post-emoji-chip" onclick="selectPostEmoji('😊 Hạnh phúc')">😊 Hạnh phúc</button>
                            <button type="button" class="post-emoji-chip" onclick="selectPostEmoji('😍 Yêu đời')">😍 Yêu đời</button>
                            <button type="button" class="post-emoji-chip" onclick="selectPostEmoji('🎉 Hào hứng')">🎉 Hào hứng</button>
                            <button type="button" class="post-emoji-chip" onclick="selectPostEmoji('🥳 Vui vẻ')">🥳 Vui vẻ</button>
                            <button type="button" class="post-emoji-chip" onclick="selectPostEmoji('😎 Tự tin')">😎 Tự tin</button>
                            <button type="button" class="post-emoji-chip" onclick="selectPostEmoji('😋 Thèm ăn')">😋 Thèm ăn</button>
                            <button type="button" class="post-emoji-chip" onclick="selectPostEmoji('✈️ Du lịch')">✈️ Du lịch</button>
                            <button type="button" class="post-emoji-chip" onclick="selectPostEmoji('✨ Tuyệt vời')">✨ Tuyệt vời</button>
                        </div>
                    </div>

                    <div id="postHelperLocationContainer" style="display: none; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 14px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 0.88rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                📍 Tìm & chọn địa điểm Check-in tại Đông Anh
                            </span>
                            <button type="button" onclick="closePostHelper('postHelperLocationContainer')" style="border: none; background: none; color: #94a3b8; font-size: 1rem; cursor: pointer;">✕</button>
                        </div>
                        
                        <!-- Search Box -->
                        <div style="position: relative; margin-bottom: 10px;">
                            <input type="text" id="postHelperLocInput" placeholder="🔍 Tìm tên trường học, doanh nghiệp, di tích, quán ăn..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.88rem; outline: none; box-sizing: border-box; background: #ffffff;" oninput="onSearchLocationInput(this.value)">
                        </div>

                        <!-- Category Filters -->
                        <div style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 6px; margin-bottom: 10px; scrollbar-width: none;">
                            <button type="button" class="post-loc-cat-btn active" onclick="filterLocationsByCategory('all', this)">Tất cả</button>
                            <button type="button" class="post-loc-cat-btn" onclick="filterLocationsByCategory('Trường học', this)">🏫 Trường học</button>
                            <button type="button" class="post-loc-cat-btn" onclick="filterLocationsByCategory('Ẩm thực', this)">🍜 Ẩm thực</button>
                            <button type="button" class="post-loc-cat-btn" onclick="filterLocationsByCategory('Văn hóa / Di sản', this)">🏛️ Văn hóa</button>
                            <button type="button" class="post-loc-cat-btn" onclick="filterLocationsByCategory('Chợ / Gian hàng', this)">🛍️ Chợ / OCOP</button>
                            <button type="button" class="post-loc-cat-btn" onclick="filterLocationsByCategory('Lưu trú', this)">🏨 Lưu trú</button>
                            <button type="button" class="post-loc-cat-btn" onclick="filterLocationsByCategory('Y tế', this)">🏥 Y tế</button>
                        </div>

                        <!-- Locations List Stream -->
                        <div id="locationsListStream" style="max-height: 220px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; padding-right: 4px;"></div>
                    </div>
                </div>

                <!-- Multi Image Preview Box -->
                <div id="add-post-multi-preview" style="display: none; border-radius: 14px; overflow: hidden; margin-bottom: 16px; position: relative;">
                    <div id="preview-grid" style="width: 100%;"></div>
                </div>

                <!-- Facebook Bottom Action Bar -->
                <div class="fb-modal-action-bar">
                    <span class="fb-modal-action-label">Thêm vào bài viết của bạn</span>
                    <div class="fb-modal-action-buttons">
                        <label class="fb-modal-action-btn" title="Thêm ảnh/video">
                            🖼️
                            <input type="file" name="images[]" multiple accept="image/*,video/*,.mp4,.mov,.avi,.mkv,.webm" class="fb-modal-file-input" onchange="previewMultiPostImages(this, 'add-post-multi-preview', 'preview-grid')">
                        </label>
                        <button type="button" class="fb-modal-action-btn" title="Gắn thẻ" onclick="togglePostHelper('postHelperTagContainer')">🏷️</button>
                        <button type="button" class="fb-modal-action-btn" title="Cảm xúc" onclick="togglePostHelper('postHelperEmojiContainer')">😊</button>
                        <button type="button" class="fb-modal-action-btn" title="Vị trí" onclick="togglePostHelper('postHelperLocationContainer')">📍</button>
                    </div>
                </div>

                <button type="submit" class="fb-modal-submit-btn">
                    Đăng
                </button>
            </form>
        </div>
    </div>
    @endif

</div>

<script>
    function openModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.add('show');
            m.style.display = 'flex';
        }
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
    }

    function togglePostDropdown(menuId) {
        const menus = ['privacyDropdownMenu', 'categoryDropdownMenu'];
        menus.forEach(id => {
            const m = document.getElementById(id);
            if (m) {
                if (id === menuId) {
                    m.style.display = (m.style.display === 'none' || m.style.display === '') ? 'block' : 'none';
                } else {
                    m.style.display = 'none';
                }
            }
        });
    }

    function selectPostPrivacy(value, label) {
        const inp = document.getElementById('postPrivacyInput');
        const lbl = document.getElementById('privacyDropdownLabel');
        if (inp) inp.value = value;
        if (lbl) lbl.innerText = label;
        const menu = document.getElementById('privacyDropdownMenu');
        if (menu) menu.style.display = 'none';
    }

    function selectPostCategory(value, label) {
        const inp = document.getElementById('postCategoryInput');
        const lbl = document.getElementById('categoryDropdownLabel');
        if (inp) inp.value = value;
        if (lbl) lbl.innerText = label;
        const menu = document.getElementById('categoryDropdownMenu');
        if (menu) menu.style.display = 'none';
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#privacyDropdownBtn') && !e.target.closest('#privacyDropdownMenu') &&
            !e.target.closest('#categoryDropdownBtn') && !e.target.closest('#categoryDropdownMenu')) {
            const pMenu = document.getElementById('privacyDropdownMenu');
            const cMenu = document.getElementById('categoryDropdownMenu');
            if (pMenu) pMenu.style.display = 'none';
            if (cMenu) cMenu.style.display = 'none';
        }
    });

    function togglePostHelper(containerId) {
        const el = document.getElementById(containerId);
        if (!el) return;
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = containerId === 'postHelperTagContainer' ? 'flex' : 'block';
            if (containerId === 'postHelperTagContainer') {
                const inp = document.getElementById('postHelperTagInput');
                if (inp) inp.focus();
            } else if (containerId === 'postHelperLocationContainer') {
                const inp = document.getElementById('postHelperLocInput');
                if (inp) inp.focus();
                renderLocationList(allLocationsData);
            }
        } else {
            el.style.display = 'none';
        }
    }

    function closePostHelper(containerId) {
        const el = document.getElementById(containerId);
        if (el) el.style.display = 'none';
        const dropdown = document.getElementById('tagFriendsDropdown');
        if (dropdown) dropdown.style.display = 'none';
    }

    const friendsData = @json($friendsList ?? []);

    function onTagFriendsInput(query) {
        const dropdown = document.getElementById('tagFriendsDropdown');
        if (!dropdown) return;

        const q = query.trim().toLowerCase();
        if (!q) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            applyPostHelperText();
            return;
        }

        const matches = friendsData.filter(u => u.name && u.name.toLowerCase().includes(q));
        if (matches.length === 0) {
            dropdown.innerHTML = '<div style="padding: 10px; font-size: 0.85rem; color: #94a3b8; text-align: center;">Không tìm thấy người dùng phù hợp</div>';
            dropdown.style.display = 'block';
            applyPostHelperText();
            return;
        }

        dropdown.innerHTML = matches.map(u => `
            <div onclick="selectTaggedFriend('${u.name.replace(/'/g, "\\'")}')" class="fb-dropdown-item" style="padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px; transition: background 0.15s ease;">
                <img src="${u.avatar || 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=80&q=80'}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                <div>
                    <div>${u.name}</div>
                    <div style="font-size: 0.72rem; font-weight: 500; color: #64748b;">${u.role === 'admin' ? '⭐ Admin' : (u.role === 'principal' ? '🏫 Hiệu trưởng' : 'Thành viên')}</div>
                </div>
            </div>
        `).join('');
        dropdown.style.display = 'block';
        applyPostHelperText();
    }

    function selectTaggedFriend(friendName) {
        const inp = document.getElementById('postHelperTagInput');
        const dropdown = document.getElementById('tagFriendsDropdown');
        if (inp) inp.value = friendName;
        if (dropdown) dropdown.style.display = 'none';
        applyPostHelperText();
    }

    let selectedPostEmoji = '';
    let selectedPostLocation = '';

    const allLocationsData = @json($allLocations ?? []);
    let activeLocationCategory = 'all';

    function filterLocationsByCategory(category, btn) {
        activeLocationCategory = category;
        const btns = document.querySelectorAll('.post-loc-cat-btn');
        btns.forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const q = (document.getElementById('postHelperLocInput')?.value || '').trim().toLowerCase();
        let filtered = allLocationsData;
        if (category !== 'all') {
            filtered = filtered.filter(l => l.category === category);
        }
        if (q) {
            filtered = filtered.filter(l => (l.name && l.name.toLowerCase().includes(q)) || (l.address && l.address.toLowerCase().includes(q)));
        }
        renderLocationList(filtered);
    }

    function onSearchLocationInput(query) {
        const q = query.trim().toLowerCase();
        let filtered = allLocationsData;
        if (activeLocationCategory !== 'all') {
            filtered = filtered.filter(l => l.category === activeLocationCategory);
        }
        if (q) {
            filtered = filtered.filter(l => (l.name && l.name.toLowerCase().includes(q)) || (l.address && l.address.toLowerCase().includes(q)));
        }
        renderLocationList(filtered);
    }

    function renderLocationList(locations) {
        const stream = document.getElementById('locationsListStream');
        if (!stream) return;

        if (!locations || locations.length === 0) {
            stream.innerHTML = '<div style="padding: 14px; text-align: center; color: #94a3b8; font-size: 0.85rem;">Không tìm thấy địa điểm phù hợp</div>';
            return;
        }

        stream.innerHTML = locations.map(loc => `
            <div onclick="selectPostLocation('${loc.name.replace(/'/g, "\\'")}')" class="fb-dropdown-item" style="padding: 8px 12px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: #ffffff; border: 1px solid #e2e8f0; transition: all 0.15s ease;">
                <div style="width: 36px; height: 36px; border-radius: 8px; overflow: hidden; background: #f1f5f9; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    ${loc.image ? `<img src="${loc.image}" style="width: 100%; height: 100%; object-fit: cover;">` : '📍'}
                </div>
                <div style="flex: 1; overflow: hidden; text-align: left;">
                    <div style="font-size: 0.88rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${loc.name}</div>
                    <div style="font-size: 0.72rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <span style="font-weight: 700; color: #2563eb;">[${loc.category}]</span> ${loc.address}
                    </div>
                </div>
            </div>
        `).join('');
    }

    function selectPostEmoji(emojiStr) {
        selectedPostEmoji = emojiStr;
        applyPostHelperText();
        closePostHelper('postHelperEmojiContainer');
    }

    function selectPostLocation(locStr) {
        selectedPostLocation = locStr;
        const locInp = document.getElementById('postHelperLocInput');
        if (locInp) locInp.value = locStr;
        applyPostHelperText();
        closePostHelper('postHelperLocationContainer');
    }

    function applyPostHelperText() {
        const tagInp = document.getElementById('postHelperTagInput');
        const locInp = document.getElementById('postHelperLocInput');
        
        let suffixParts = [];
        if (selectedPostEmoji) {
            suffixParts.push('— đang cảm thấy ' + selectedPostEmoji);
        }
        if (tagInp && tagInp.value.trim()) {
            suffixParts.push('cùng với ' + tagInp.value.trim());
        }
        const currentLoc = (locInp && locInp.value.trim()) ? locInp.value.trim() : selectedPostLocation;
        if (currentLoc) {
            suffixParts.push('tại ' + (currentLoc.startsWith('📍') ? currentLoc : '📍 ' + currentLoc));
        }

        let badgeEl = document.getElementById('postModalMetaBadge');
        if (!badgeEl) {
            const userRow = document.querySelector('.fb-modal-user-info');
            if (userRow) {
                badgeEl = document.createElement('div');
                badgeEl.id = 'postModalMetaBadge';
                badgeEl.style.cssText = 'font-size: 0.85rem; font-weight: 700; color: #2563eb; margin-top: 4px;';
                userRow.appendChild(badgeEl);
            }
        }
        if (badgeEl) {
            badgeEl.innerHTML = suffixParts.join(' ');
        }
    }

    function clearMultiPostPreview(containerId, gridId) {
        const container = document.getElementById(containerId);
        const grid = document.getElementById(gridId);
        if (grid) grid.innerHTML = '';
        if (container) container.style.display = 'none';
        const fileInputs = document.querySelectorAll('input[type="file"][name="images[]"]');
        fileInputs.forEach(input => { input.value = ''; });
    }

    // Facebook multi-photo collage previewer for creation modal
    function previewMultiPostImages(input, containerId, gridId) {
        const container = document.getElementById(containerId);
        const grid = document.getElementById(gridId);
        if (!container || !grid) return;

        if (!input.files || input.files.length === 0) {
            container.style.display = 'none';
            grid.innerHTML = '';
            return;
        }

        container.style.display = 'block';
        grid.innerHTML = '';

        const files = Array.from(input.files);
        const total = files.length;
        let loadedCount = 0;
        const imageUrls = new Array(total);

        files.forEach((file, index) => {
            const isVideo = file.type.startsWith('video/') || ['mp4', 'mov', 'avi', 'mkv', 'webm'].some(ext => file.name.toLowerCase().endsWith('.' + ext));
            const reader = new FileReader();
            reader.onload = function(e) {
                imageUrls[index] = { type: isVideo ? 'video' : 'image', url: e.target.result };
                loadedCount++;
                if (loadedCount === total) {
                    renderFbCollagePreview(grid, imageUrls, containerId);
                }
            };
            reader.readAsDataURL(file);
        });
    }

    function renderFbCollagePreview(grid, imageUrls, containerId) {
        const total = imageUrls.length;
        grid.innerHTML = '';

        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'position: relative; width: 100%; border-radius: 14px; overflow: hidden; background: #0f172a; font-family: "Be Vietnam Pro", sans-serif; border: 1px solid #cbd5e1;';

        const topBar = document.createElement('div');
        topBar.style.cssText = 'position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: center; z-index: 30; pointer-events: none;';
        topBar.innerHTML = `
            <button type="button" style="pointer-events: auto; background: #ffffff; color: #0f172a; border: none; padding: 7px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); cursor: pointer;">
                ✏️ Chỉnh sửa tất cả
            </button>
            <button type="button" onclick="clearMultiPostPreview('${containerId}', '${grid.id}')" style="pointer-events: auto; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 900; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                ✕
            </button>
        `;
        wrapper.appendChild(topBar);

        const getMediaHtml = (item) => {
            if (!item) return '';
            if (typeof item === 'string') {
                return `<img src="${item}" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
            if (item.type === 'video') {
                return `<video src="${item.url}" controls muted style="width: 100%; height: 100%; object-fit: cover; background: #000;"></video>`;
            }
            return `<img src="${item.url}" style="width: 100%; height: 100%; object-fit: cover;">`;
        };

        const flexGrid = document.createElement('div');
        flexGrid.style.cssText = 'display: flex; gap: 2px; width: 100%; height: 380px; background: #0f172a;';

        if (total === 1) {
            flexGrid.style.height = '320px';
            flexGrid.innerHTML = `<div style="width: 100%; height: 100%; overflow: hidden;">${getMediaHtml(imageUrls[0])}</div>`;
        } else if (total === 2) {
            flexGrid.style.height = '340px';
            flexGrid.innerHTML = `
                <div style="flex: 1; height: 100%; overflow: hidden;">${getMediaHtml(imageUrls[0])}</div>
                <div style="flex: 1; height: 100%; overflow: hidden;">${getMediaHtml(imageUrls[1])}</div>
            `;
        } else if (total === 3) {
            flexGrid.style.height = '360px';
            flexGrid.innerHTML = `
                <div style="flex: 1; height: 100%; overflow: hidden;">${getMediaHtml(imageUrls[0])}</div>
                <div style="flex: 1; height: 100%; display: flex; flex-direction: column; gap: 2px;">
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[1])}</div>
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[2])}</div>
                </div>
            `;
        } else if (total === 4) {
            flexGrid.style.height = '380px';
            flexGrid.innerHTML = `
                <div style="flex: 1.1; height: 100%; overflow: hidden;">${getMediaHtml(imageUrls[0])}</div>
                <div style="flex: 1; height: 100%; display: flex; flex-direction: column; gap: 2px;">
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[1])}</div>
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[2])}</div>
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[3])}</div>
                </div>
            `;
        } else {
            flexGrid.style.height = '390px';
            const extraCount = total - 4;

            flexGrid.innerHTML = `
                <div style="flex: 1; height: 100%; display: flex; flex-direction: column; gap: 2px;">
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[0])}</div>
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[1])}</div>
                </div>
                <div style="flex: 1; height: 100%; display: flex; flex-direction: column; gap: 2px;">
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[2])}</div>
                    <div style="flex: 1; overflow: hidden;">${getMediaHtml(imageUrls[3])}</div>
                    <div style="flex: 1; overflow: hidden; position: relative;">
                        ${getMediaHtml(imageUrls[4])}
                        <div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.45); backdrop-filter: blur(1px); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 1.8rem; font-weight: 900; font-family: 'Be Vietnam Pro', sans-serif; letter-spacing: -0.5px;">+${extraCount}</div>
                    </div>
                </div>
            `;
        }

        wrapper.appendChild(flexGrid);
        grid.appendChild(wrapper);
    }

    let currentGalleryImages = [];
    let currentGalleryIndex = 0;

    function openSingleImageLightbox(imgUrl, titleText) {
        if (!imgUrl) return;
        openPostLightboxGallery([imgUrl], 0);
        const counterEl = document.getElementById('postLightboxCounter');
        if (counterEl && titleText) {
            counterEl.innerHTML = titleText;
        }
    }

    function openPostLightboxGallery(images, startIndex = 0) {
        if (!Array.isArray(images) || images.length === 0) return;
        currentGalleryImages = images;
        currentGalleryIndex = startIndex;

        let modal = document.getElementById('postLightboxModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'postLightboxModal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.94); z-index: 10000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); flex-direction: column;';
            
            modal.innerHTML = `
                <button onclick="closePostLightbox()" style="position: absolute; top: 20px; right: 25px; color: #ffffff; font-size: 2.2rem; cursor: pointer; background: none; border: none; font-weight: bold; z-index: 10001; line-height: 1;">✕</button>
                <div id="postLightboxCounter" style="position: absolute; top: 24px; left: 30px; color: #ffffff; font-size: 0.95rem; font-weight: 700; font-family: 'Be Vietnam Pro', sans-serif; background: rgba(255,255,255,0.18); padding: 6px 16px; border-radius: 20px; backdrop-filter: blur(4px);"></div>
                
                <button id="postLightboxPrevBtn" onclick="navigateLightboxGallery(-1)" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; width: 48px; height: 48px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); transition: all 0.2s;">❮</button>

                <img id="postLightboxImg" src="" style="max-width: 88vw; max-height: 82vh; border-radius: 12px; object-fit: contain; box-shadow: 0 25px 50px rgba(0,0,0,0.6); transition: opacity 0.15s ease-in-out;">

                <button id="postLightboxNextBtn" onclick="navigateLightboxGallery(1)" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; width: 48px; height: 48px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); transition: all 0.2s;">❯</button>
            `;
            document.body.appendChild(modal);

            document.addEventListener('keydown', function(e) {
                const m = document.getElementById('postLightboxModal');
                if (!m || m.style.display === 'none') return;
                if (e.key === 'ArrowLeft') navigateLightboxGallery(-1);
                else if (e.key === 'ArrowRight') navigateLightboxGallery(1);
                else if (e.key === 'Escape') closePostLightbox();
            });
        }

        updateLightboxView();
        modal.style.display = 'flex';
    }

    function updateLightboxView() {
        const imgEl = document.getElementById('postLightboxImg');
        const counterEl = document.getElementById('postLightboxCounter');
        const prevBtn = document.getElementById('postLightboxPrevBtn');
        const nextBtn = document.getElementById('postLightboxNextBtn');

        if (!imgEl) return;

        imgEl.src = currentGalleryImages[currentGalleryIndex];
        if (counterEl) {
            counterEl.textContent = `Ảnh ${currentGalleryIndex + 1} / ${currentGalleryImages.length}`;
        }

        if (prevBtn) prevBtn.style.display = currentGalleryImages.length > 1 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = currentGalleryImages.length > 1 ? 'flex' : 'none';
    }

    function navigateLightboxGallery(direction) {
        if (!currentGalleryImages.length) return;
        currentGalleryIndex = (currentGalleryIndex + direction + currentGalleryImages.length) % currentGalleryImages.length;
        updateLightboxView();
    }

    function closePostLightbox() {
        const modal = document.getElementById('postLightboxModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function togglePostLike(btn, postId) {
        if (typeof checkAuthGuard === 'function' && !checkAuthGuard('thích bài viết')) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/api/reactions/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
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
                    btn.style.color = '';
                    btn.style.fontWeight = '';
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

    function togglePlaceHeart(btn, eateryId) {
        if (typeof checkAuthGuard === 'function' && !checkAuthGuard('thả tim địa điểm')) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/api/reactions/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: eateryId,
                type: 'eatery',
                emoji: '❤️'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const textEl = document.getElementById(`eatery-heart-text-${eateryId}`);
                if (data.liked) {
                    btn.style.background = '#ef4444';
                    btn.style.color = '#ffffff';
                    if (textEl) textEl.textContent = `Đã thả tim (${data.likes_count})`;
                } else {
                    btn.style.background = '#f1f5f9';
                    btn.style.color = '#0f172a';
                    if (textEl) textEl.textContent = `Thả tim (${data.likes_count})`;
                }
            }
        })
        .catch(err => {
            console.error('Lỗi thả tim địa điểm:', err);
        });
    }

    // Toggle & Fetch Realtime Facebook Post Comments
    function toggleComments(postId, btnEl) {
        let commentBox = document.getElementById(`fb-comments-box-${postId}`);
        
        if (!commentBox) {
            let postCard = btnEl ? btnEl.closest('.fb-post-card') : null;
            if (!postCard) {
                postCard = document.getElementById(`post-like-btn-${postId}`)?.closest('.fb-post-card');
            }
            if (!postCard) return;

            commentBox = document.createElement('div');
            commentBox.id = `fb-comments-box-${postId}`;
            commentBox.className = 'fb-comments-section show';
            
            const currentUserAvatar = document.querySelector('.fb-modal-user-avatar')?.src || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80';

            commentBox.innerHTML = `
                <div class="fb-comments-list" id="fb-comments-list-${postId}">
                    <div style="font-size: 0.85rem; color: #94a3b8; text-align: center; padding: 10px;">⏳ Đang tải bình luận...</div>
                </div>
                <form class="fb-comment-input-box" onsubmit="submitPostComment(event, ${postId})">
                    <img src="${currentUserAvatar}" class="fb-comment-avatar">
                    <input type="text" id="fb-comment-input-${postId}" required class="fb-comment-input" placeholder="Viết bình luận công khai...">
                    <button type="submit" class="fb-comment-send-btn" title="Gửi bình luận">➤</button>
                </form>
            `;

            postCard.appendChild(commentBox);

            fetch(`/api/comments?id=${postId}&type=post`)
                .then(res => res.json())
                .then(data => {
                    const listEl = document.getElementById(`fb-comments-list-${postId}`);
                    if (!listEl) return;
                    
                    if (data.success && data.comments.length > 0) {
                        listEl.innerHTML = '';
                        data.comments.forEach(c => {
                            listEl.appendChild(createCommentItemElement(c));
                        });
                    } else {
                        listEl.innerHTML = `<div class="text-center py-2 text-muted" style="font-size: 0.85rem;" id="no-comments-msg-${postId}">Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</div>`;
                    }
                })
                .catch(err => console.error('Lỗi tải bình luận:', err));
        } else {
            commentBox.classList.toggle('show');
        }
    }

    function submitPostComment(e, postId) {
        e.preventDefault();
        if (typeof checkAuthGuard === 'function' && !checkAuthGuard('bình luận')) return;
        const inputEl = document.getElementById(`fb-comment-input-${postId}`);
        if (!inputEl || !inputEl.value.trim()) return;

        const content = inputEl.value.trim();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        inputEl.disabled = true;

        fetch('/api/comments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: postId,
                type: 'post',
                content: content
            })
        })
        .then(res => res.json())
        .then(data => {
            inputEl.disabled = false;
            inputEl.value = '';

            if (data.success) {
                const listEl = document.getElementById(`fb-comments-list-${postId}`);
                const noMsg = document.getElementById(`no-comments-msg-${postId}`);
                if (noMsg) noMsg.remove();

                if (listEl && data.comment) {
                    listEl.appendChild(createCommentItemElement(data.comment));
                    listEl.scrollTop = listEl.scrollHeight;
                }

                const postCard = document.getElementById(`post-like-btn-${postId}`)?.closest('.fb-post-card');
                const statsDivs = postCard?.querySelectorAll('.fb-post-stats div');
                if (statsDivs && statsDivs.length >= 2 && data.total_comments !== undefined) {
                    const rightStat = statsDivs[1];
                    const parts = rightStat.textContent.split('•');
                    const sharesPart = parts.length > 1 ? parts[1] : ' 0 chia sẻ';
                    rightStat.innerHTML = `💬 ${data.total_comments} bình luận •${sharesPart}`;
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Không thể gửi bình luận',
                        text: data.message || 'Bình luận không hợp lệ hoặc bạn đang gửi quá nhanh.',
                        confirmButtonColor: '#0284c7'
                    });
                } else {
                    alert(data.message || 'Bình luận không hợp lệ hoặc bạn đang gửi quá nhanh.');
                }
            }
        })
        .catch(err => {
            inputEl.disabled = false;
            console.error('Lỗi gửi bình luận:', err);
        });
    }

    function createCommentItemElement(c) {
        const item = document.createElement('div');
        item.className = 'fb-comment-item animate__animated animate__fadeIn';
        item.innerHTML = `
            <img src="${c.author_avatar}" class="fb-comment-avatar" alt="${c.author_name}">
            <div>
                <div class="fb-comment-bubble">
                    <div class="fb-comment-author">${c.author_name}</div>
                    <div class="fb-comment-text">${escapeHtml(c.content)}</div>
                </div>
                <div class="fb-comment-meta">
                    <span>${c.created_at_human}</span>
                </div>
            </div>
        `;
        return item;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Realtime Post Submission without page refresh
    function handleRealtimePostSubmit(e, form) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('.fb-modal-submit-btn');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Đăng';
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '🚀 Đang đăng bài...';
        }

        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }

            if (data.success) {
                // Close modal
                closeModal('addPostModal');
                
                // Reset form inputs & image preview
                form.reset();
                const previewBox = document.getElementById('add-post-multi-preview');
                const previewGrid = document.getElementById('preview-grid');
                if (previewBox) previewBox.style.display = 'none';
                if (previewGrid) previewGrid.innerHTML = '';

                // Insert new post realtime into feed
                if (data.post) {
                    renderNewPostRealtime(data.post, data.school);
                }

                // Show Toast Alert
                showToastNotification('✅ Đăng bài viết mới thành công!');
            } else {
                alert(data.message || 'Có lỗi xảy ra khi đăng bài viết!');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
            console.error('Lỗi realtime post:', err);
            form.submit();
        });
    }

    function renderNewPostRealtime(post, school) {
        let feedContainer = document.querySelector('.fb-posts-feed') || document.querySelector('.pro-posts-stream');
        const emptyState = document.querySelector('.pro-empty-card');
        
        if (!feedContainer && emptyState) {
            const parent = emptyState.parentElement;
            emptyState.remove();
            feedContainer = document.createElement('div');
            feedContainer.className = 'fb-posts-feed';
            parent.appendChild(feedContainer);
        }

        if (!feedContainer) return;

        const imgs = post.all_images || (post.images ? post.images : (post.image_path ? [post.image_path] : []));
        const imgCount = imgs.length;

        let photoGridHtml = '';
        if (imgCount === 1) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-1" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)">
                <img src="${imgs[0]}" alt="${post.name}">
            </div>`;
        } else if (imgCount === 2) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-2">
                <img src="${imgs[0]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)" alt="${post.name}">
                <img src="${imgs[1]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 1)" alt="${post.name}">
            </div>`;
        } else if (imgCount === 3) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-3">
                <img src="${imgs[0]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)" alt="${post.name}">
                <div class="fb-grid-3-col-right">
                    <img src="${imgs[1]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 1)" alt="${post.name}">
                    <img src="${imgs[2]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 2)" alt="${post.name}">
                </div>
            </div>`;
        } else if (imgCount === 4) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-4">
                <img src="${imgs[0]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)" alt="${post.name}">
                <div class="fb-grid-4-col-right">
                    <img src="${imgs[1]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 1)" alt="${post.name}">
                    <img src="${imgs[2]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 2)" alt="${post.name}">
                    <img src="${imgs[3]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 3)" alt="${post.name}">
                </div>
            </div>`;
        } else if (imgCount >= 5) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-5">
                <div class="fb-grid-5-row-top">
                    <img src="${imgs[0]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)" alt="${post.name}">
                    <img src="${imgs[1]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 1)" alt="${post.name}">
                </div>
                <div class="fb-grid-5-row-bottom">
                    <img src="${imgs[2]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 2)" alt="${post.name}">
                    <img src="${imgs[3]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 3)" alt="${post.name}">
                    <div class="fb-photo-thumb-box" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 4)">
                        <img src="${imgs[4]}" alt="${post.name}">
                        ${imgCount > 5 ? `<div class="fb-photo-more-overlay">+${imgCount - 5}</div>` : ''}
                    </div>
                </div>
            </div>`;
        }

        const postCard = document.createElement('article');
        postCard.className = 'fb-post-card mb-4 animate__animated animate__fadeInDown';
        postCard.style.cssText = 'background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); animation-duration: 0.4s;';
        postCard.innerHTML = `
            <div class="fb-post-header">
                <div class="fb-post-author-box">
                    <img src="${school ? school.image_path : ''}" class="fb-user-avatar" alt="${school ? school.name : ''}">
                    <div>
                        <h4 class="fb-post-author-name">${school ? school.name : ''}</h4>
                        <div class="fb-post-subtext">
                            <span>Vừa xong</span>
                            <span>•</span>
                            <span>🌐 Công khai</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fb-post-text">
                <strong class="d-block mb-1 text-dark" style="font-size: 1.05rem;">🌸 ${post.name}</strong>
                ${post.description ? post.description.replace(/\n/g, '<br>') : ''}
            </div>
            ${photoGridHtml}
            <div class="fb-post-stats">
                <div id="post-likes-count-${post.id}" onclick="showPostLikers(${post.id}, 'post')" style="cursor:pointer;" title="Xem ai đã thích">👍 0 lượt thích</div>
                <div>💬 0 bình luận • <span id="post-shares-count-${post.id}">${post.shares_count || 0}</span> chia sẻ</div>
            </div>
            <div class="fb-post-actions">
                <button class="fb-action-btn" id="post-like-btn-${post.id}" onclick="togglePostLike(this, ${post.id})">👍 Thích</button>
                <button class="fb-action-btn" onclick="alert('Tính năng bình luận bài viết đang được kết nối dữ liệu thực!')">💬 Bình luận</button>
                <button class="fb-action-btn" onclick="shareFbPost(${post.id}, ${JSON.stringify(post.name)}, ${JSON.stringify(imgs)}, '${post.hashid || post.id}')">🔄 Chia sẻ</button>
            </div>
        `;

        feedContainer.prepend(postCard);
        if (typeof initPostTextExpanders === 'function') {
            initPostTextExpanders(postCard);
        }
    }

    const IS_USER_LOGGED_IN = @json(!!$currentUserId);

    function checkAuthGuard(actionName) {
        var allowGuestActions = ['thích bài viết', 'bình luận', 'thả tim địa điểm', 'tương tác bài viết', 'thích'];
        if (actionName && allowGuestActions.indexOf(actionName.toLowerCase()) !== -1) {
            return true;
        }
        if (!IS_USER_LOGGED_IN) {
            openAuthLoginModal(actionName);
            return false;
        }
        return true;
    }

    function sendFriendRequest(targetUserId) {
        if (!checkAuthGuard('gửi lời mời kết bạn')) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('/social/friends', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ friend_id: targetUserId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const wrapper = document.getElementById('friend-btn-wrapper');
                if (wrapper) {
                    wrapper.innerHTML = `<button type="button" class="pro-btn-outline" id="friend-btn" onclick="cancelFriendRequest(${data.friendship_id}, ${targetUserId})">⏳ Đã gửi lời mời</button>`;
                }
                const followBtn = document.getElementById('follow-btn');
                if (followBtn && followBtn.getAttribute('data-following') !== 'true') {
                    followBtn.setAttribute('data-following', 'true');
                    followBtn.className = 'pro-btn-outline';
                    followBtn.style.background = '#f1f5f9';
                    followBtn.style.color = '#475569';
                    const iconEl = document.getElementById('follow-icon');
                    const textEl = document.getElementById('follow-text');
                    if (iconEl) iconEl.textContent = '✓';
                    if (textEl) textEl.textContent = 'Đang theo dõi';
                }
                showToastNotification('➕ Đã gửi lời mời kết bạn!');
            } else {
                showToastNotification(data.message || 'Không thể gửi lời mời kết bạn.');
            }
        })
        .catch(err => {
            console.error('Friend request error:', err);
        });
    }

    function acceptFriendRequest(friendshipId) {
        if (!checkAuthGuard('chấp nhận lời mời kết bạn')) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(`/social/friends/${friendshipId}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const wrapper = document.getElementById('friend-btn-wrapper');
                if (wrapper) {
                    wrapper.innerHTML = `<button type="button" class="pro-btn-outline" id="friend-btn" style="background: #ecfdf5; border-color: #10b981; color: #047857;" onclick="unfriendUser(${friendshipId})">👥 Bạn bè ✓</button>`;
                }
                showToastNotification('✅ Đã chấp nhận lời mời kết bạn!');
            }
        });
    }

    function cancelFriendRequest(friendshipId, targetUserId) {
        if (!checkAuthGuard('hủy kết bạn')) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(`/social/friends/${friendshipId}/decline`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const wrapper = document.getElementById('friend-btn-wrapper');
                if (wrapper) {
                    wrapper.innerHTML = `<button type="button" class="pro-btn-primary" id="friend-btn" onclick="sendFriendRequest(${targetUserId})">➕ Thêm bạn bè</button>`;
                }
                showToastNotification('Đã hủy lời mời kết bạn.');
            }
        });
    }

    function unfriendUser(friendshipId, targetUserId) {
        if (!checkAuthGuard('hủy kết bạn')) return;
        showConfirmModal({
            title: 'Hủy kết bạn',
            message: 'Bạn có chắc chắn muốn hủy kết bạn với người dùng này không?',
            isDanger: true,
            onConfirm: () => cancelFriendRequest(friendshipId, targetUserId)
        });
    }

    function toggleFollowUser(btn, targetUserId) {
        if (!checkAuthGuard('theo dõi người dùng')) return;
        const isFollowing = btn.getAttribute('data-following') === 'true';
        const iconEl = document.getElementById('follow-icon');
        const textEl = document.getElementById('follow-text');

        if (isFollowing) {
            btn.setAttribute('data-following', 'false');
            btn.className = 'pro-btn-primary';
            btn.style.background = '#2563eb';
            btn.style.color = '#ffffff';
            if (iconEl) iconEl.textContent = '🔔';
            if (textEl) textEl.textContent = 'Theo dõi';
            showToastNotification('Đã bỏ theo dõi trang cá nhân này.');
        } else {
            btn.setAttribute('data-following', 'true');
            btn.className = 'pro-btn-outline';
            btn.style.background = '#f1f5f9';
            btn.style.color = '#475569';
            if (iconEl) iconEl.textContent = '✓';
            if (textEl) textEl.textContent = 'Đang theo dõi';
            showToastNotification('🔔 Bạn đã theo dõi trang cá nhân này!');
        }
    }

    function openDirectMessage(userId, userName, userAvatar) {
        if (!checkAuthGuard('gửi tin nhắn')) return;
        if (window.Alpine && Alpine.store && Alpine.store('chatStore')) {
            Alpine.store('chatStore').openChat(userId, userName, userAvatar, userAvatar, true);
        } else {
            showToastNotification('💬 Đang kết nối kênh tin nhắn với ' + userName + '...');
        }
    }

    function shareProfilePage() {
        const profileSlug = @json(optional($school)->slug ?: ($user->username ?: \Illuminate\Support\Str::slug($user->name)));
        const profileUrl = window.location.origin + '/profile/' + profileSlug;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(profileUrl).then(() => {
                showToastNotification('🔗 Đã sao chép liên kết trang cá nhân vào khay nhớ tạm!');
            }).catch(() => {
                fallbackCopyUrl(profileUrl);
            });
        } else {
            fallbackCopyUrl(profileUrl);
        }
    }

    function fallbackCopyUrl(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showToastNotification('🔗 Đã sao chép liên kết trang cá nhân!');
        } catch (err) {
            alert('Liên kết trang: ' + text);
        }
        document.body.removeChild(textArea);
    }

    async function shareFbPost(postId, postTitle, postImages) {
        const shareUrl = window.location.href;
        const titleText = postTitle ? ('Bài viết: ' + postTitle) : 'Chia sẻ bài viết';
        
        if (navigator.share) {
            const shareData = {
                title: titleText,
                text: postTitle ? (postTitle + ' — DongAnh Social') : 'Xem bài viết này trên DongAnh Social',
                url: shareUrl
            };

            let imagesArray = [];
            if (Array.isArray(postImages)) {
                imagesArray = postImages;
            } else if (typeof postImages === 'string' && postImages.startsWith('[')) {
                try { imagesArray = JSON.parse(postImages); } catch(e) {}
            }

            if (imagesArray.length > 0 && imagesArray[0]) {
                try {
                    const firstImgUrl = imagesArray[0];
                    const res = await fetch(firstImgUrl);
                    const blob = await res.blob();
                    const file = new File([blob], 'post-image.jpg', { type: blob.type || 'image/jpeg' });
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        shareData.files = [file];
                    }
                } catch (err) {
                    console.log('Non-critical share image fetch:', err);
                }
            }

            navigator.share(shareData).catch(() => {});
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(shareUrl).then(() => {
                showToastNotification('🔄 Đã sao chép liên kết bài viết vào khay nhớ tạm!');
            }).catch(() => {
                fallbackCopyUrl(shareUrl);
            });
        } else {
            fallbackCopyUrl(shareUrl);
        }
    }

    let currentPreviewType = null;
    let selectedImageFile = null;
    let activeFileInputEl = null;

    function handleAvatarSelect(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        selectedImageFile = file;
        activeFileInputEl = input;
        currentPreviewType = 'avatar';

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('previewModalImg');
            const wrap = document.getElementById('previewFrameWrap');
            const title = document.getElementById('previewModalTitle');
            const sub = document.getElementById('previewModalSub');

            if (title) title.innerHTML = '🧑 Xem trước ảnh đại diện';
            if (sub) sub.innerHTML = 'Kiểm tra ảnh đại diện mới trước khi cập nhật chính thức.';
            if (previewImg) previewImg.src = e.target.result;
            if (wrap) {
                wrap.style.width = '180px';
                wrap.style.height = '180px';
                wrap.style.borderRadius = '50%';
                wrap.style.margin = '0 auto 20px auto';
                wrap.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
            }

            const modal = document.getElementById('imagePreviewModal');
            if (modal) modal.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }

    function handleCoverSelect(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        selectedImageFile = file;
        activeFileInputEl = input;
        currentPreviewType = 'cover';

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('previewModalImg');
            const wrap = document.getElementById('previewFrameWrap');
            const title = document.getElementById('previewModalTitle');
            const sub = document.getElementById('previewModalSub');

            if (title) title.innerHTML = '🖼️ Xem trước ảnh bìa';
            if (sub) sub.innerHTML = 'Kiểm tra ảnh bìa mới trước khi đăng tải lên địa điểm / trang cá nhân.';
            if (previewImg) previewImg.src = e.target.result;
            if (wrap) {
                wrap.style.width = '100%';
                wrap.style.height = '230px';
                wrap.style.borderRadius = '16px';
                wrap.style.margin = '0 0 20px 0';
                wrap.style.boxShadow = 'none';
            }

            const modal = document.getElementById('imagePreviewModal');
            if (modal) modal.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }

    function closeImagePreviewModal() {
        const modal = document.getElementById('imagePreviewModal');
        if (modal) modal.style.display = 'none';
        if (activeFileInputEl) {
            activeFileInputEl.value = '';
        }
        selectedImageFile = null;
        currentPreviewType = null;
        activeFileInputEl = null;
    }

    function confirmImageUpload() {
        if (!selectedImageFile || !currentPreviewType) {
            closeImagePreviewModal();
            return;
        }

        const formData = new FormData();
        const endpoint = currentPreviewType === 'avatar' ? '/profile/avatar' : '/profile/cover';
        const fileFieldName = currentPreviewType === 'avatar' ? 'avatar' : 'cover';

        formData.append(fileFieldName, selectedImageFile);
        formData.append('_token', '{{ csrf_token() }}');

        const btn = document.getElementById('confirmUploadBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '⏳ Đang lưu...';
        }

        showToastNotification(currentPreviewType === 'avatar' ? '⌛ Đang cập nhật ảnh đại diện...' : '⌛ Đang cập nhật ảnh bìa...');

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '💾 Cập nhật ảnh';
            }
            if (data.success) {
                showToastNotification('✅ ' + data.message);
                closeImagePreviewModal();
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                showToastNotification('❌ ' + (data.message || 'Không thể cập nhật hình ảnh.'));
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '💾 Cập nhật ảnh';
            }
            console.error('Image upload error:', err);
            showToastNotification('❌ Có lỗi xảy ra khi cập nhật hình ảnh!');
        });
    }

    function editPostItem(post) {
        const form = document.getElementById('editPostForm');
        form.action = '/posts/' + post.id + '/update';
        document.getElementById('editPostNameInput').value = post.name || '';
        document.getElementById('editPostDescInput').value = post.description || '';
        openModal('editPostModal');
    }

    function showToastNotification(msg) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position: fixed; bottom: 25px; right: 25px; background: #065f46; color: #ffffff; padding: 14px 22px; border-radius: 14px; font-weight: 700; font-size: 0.95rem; font-family: "Be Vietnam Pro", sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 99999; animation: modalFadeIn 0.3s ease; display: flex; align-items: center; gap: 8px;';
        toast.innerHTML = msg;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
</script>
@endsection
