@extends('layouts.app')

@section('title', 'Cộng đồng Check-in & Nhật ký hành trình - DongAnh Map Discovery')
@section('meta_description', 'Khám phá các hình ảnh check-in thực tế, đánh giá chi tiết và trải nghiệm du lịch ẩm thực Đông Anh từ cộng đồng.')

@section('content')
<!-- Glowing background orbs for premium cinematic atmosphere -->
<div style="position: fixed; top: 10%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(14, 165, 233, 0.05) 0%, rgba(14, 165, 233, 0) 70%); filter: blur(100px); pointer-events: none; z-index: 1;"></div>
<div style="position: fixed; bottom: 10%; right: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0) 70%); filter: blur(100px); pointer-events: none; z-index: 1;"></div>

<div class="checkin-feed-container">
    <div style="width: 100%; display: flex; flex-direction: column; z-index: 2;">
        
        <!-- Harmonious Modern Breadcrumb Navigation -->
        <nav class="integrated-breadcrumb-nav" aria-label="Breadcrumb">
            <a href="/" class="breadcrumb-item-link">
                <span style="font-size: 0.95rem;">🏠</span>
                <span>Trang chủ</span>
            </a>
            <span class="breadcrumb-arrow">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </span>
            <span class="breadcrumb-item-active">
                <span style="font-size: 0.95rem;">📸</span>
                <span>Góc Check-in & Nhật Ký</span>
            </span>
        </nav>

        <!-- Page Header -->
        <div class="feed-header" style="margin-bottom: 20px;">
            <h1>📸 <span>Cộng đồng Check-in Đông Anh</span>
                <span id="liveBadge" style="display:inline-flex;align-items:center;font-size:0.72rem;font-weight:700;letter-spacing:0.5px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.4);color:#10b981;border-radius:20px;padding:3px 10px;margin-left:10px;vertical-align:middle;transition:all 0.3s;">⏳ Kết nối...</span>
            </h1>
            <p>Trải nghiệm thực tế, khoảnh khắc lưu niệm và đánh giá hành trình từ những bước chân khám phá mảnh đất Đông Anh.</p>
        </div>

        <!-- LOCKET CAMERA FIRST INTEGRATED WIDGET -->
        <style>
            .locket-widget-wrapper {
                width: 100%;
                max-width: 440px;
                margin: 0 auto 32px auto;
                box-sizing: border-box;
            }
            .locket-widget-card {
                background: var(--bg-card);
                border: 2px solid rgba(var(--primary-rgb), 0.25);
                border-radius: 32px;
                padding: 20px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 16px;
                box-sizing: border-box;
                transition: all 0.3s ease;
            }
            .locket-widget-card:hover {
                border-color: rgba(var(--primary-rgb), 0.5);
                box-shadow: 0 16px 40px rgba(var(--primary-rgb), 0.15);
            }
            .locket-header {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 4px;
            }
            .locket-live-indicator {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 0.72rem;
                font-weight: 800;
                color: #10b981;
                letter-spacing: 0.5px;
            }
            .locket-live-indicator .live-dot {
                width: 8px;
                height: 8px;
                background: #10b981;
                border-radius: 50%;
                display: inline-block;
                animation: livePulse 1.5s infinite;
            }
            .locket-title {
                font-size: 0.85rem;
                font-weight: 800;
                color: var(--text-main);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .locket-viewfinder-container {
                width: 100%;
                aspect-ratio: 1 / 1;
                background: #000;
                border-radius: 28px;
                overflow: hidden;
                position: relative;
                border: 2px solid var(--border-glow);
                box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .locket-viewfinder-container video, 
            .locket-viewfinder-container img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            .locket-fallback-view {
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
                color: var(--text-muted);
                cursor: pointer;
                padding: 20px;
            }
            .locket-fallback-view span.icon {
                font-size: 3rem;
            }
            #locketFlashOverlay {
                position: absolute;
                inset: 0;
                background: #fff;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.1s ease;
                z-index: 5;
            }
            .viewfinder-overlay {
                position: absolute;
                inset: 0;
                pointer-events: none;
                z-index: 2;
            }
            .overlay-corner {
                position: absolute;
                width: 24px;
                height: 24px;
                border-color: rgba(255, 255, 255, 0.4);
                border-style: solid;
                border-width: 0;
            }
            .overlay-corner.top-left { top: 16px; left: 16px; border-top-width: 3px; border-left-width: 3px; border-top-left-radius: 6px; }
            .overlay-corner.top-right { top: 16px; right: 16px; border-top-width: 3px; border-right-width: 3px; border-top-right-radius: 6px; }
            .overlay-corner.bottom-left { bottom: 16px; left: 16px; border-bottom-width: 3px; border-left-width: 3px; border-bottom-left-radius: 6px; }
            .overlay-corner.bottom-right { bottom: 16px; right: 16px; border-bottom-width: 3px; border-right-width: 3px; border-bottom-right-radius: 6px; }

            .locket-icon-btn {
                position: absolute;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.1rem;
                cursor: pointer;
                z-index: 3;
                transition: all 0.2s;
            }
            .locket-icon-btn:hover {
                background: rgba(0, 0, 0, 0.7);
                transform: scale(1.08);
            }
            .locket-icon-btn.flip-btn {
                top: 16px;
                right: 16px;
            }
            .locket-controls-row {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 12px;
            }
            .locket-control-btn {
                background: rgba(var(--primary-rgb), 0.08);
                border: 1.5px solid rgba(var(--primary-rgb), 0.2);
                width: 48px;
                height: 48px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.3rem;
                cursor: pointer;
                transition: all 0.2s;
            }
            .locket-control-btn:hover {
                background: rgba(var(--primary-rgb), 0.15);
                border-color: var(--primary);
                transform: scale(1.08);
            }
            .locket-shutter-btn {
                background: #fff;
                border: 5px solid rgba(var(--primary-rgb), 0.2);
                width: 76px;
                height: 76px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            }
            .locket-shutter-btn:hover {
                transform: scale(1.06);
                border-color: rgba(var(--primary-rgb), 0.45);
            }
            .locket-shutter-btn:active {
                transform: scale(0.92);
            }
            .shutter-inner {
                width: 54px;
                height: 54px;
                background: #fff;
                border: 2px solid #000;
                border-radius: 50%;
                display: block;
                transition: background 0.15s;
            }
            .locket-shutter-btn:active .shutter-inner {
                background: #e2e8f0;
            }
            .locket-details-form {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 16px;
                border-top: 1px dashed var(--border-glow);
                padding-top: 16px;
                box-sizing: border-box;
            }
            .locket-details-form .form-group {
                display: flex;
                flex-direction: column;
                gap: 6px;
                width: 100%;
            }
            .locket-details-form label {
                font-size: 0.82rem;
                font-weight: 700;
                color: var(--text-main);
            }
            .locket-details-form input[type="text"],
            .locket-details-form textarea {
                width: 100%;
                padding: 10px 14px;
                border-radius: 12px;
                border: 1.5px solid var(--border-glow);
                background: var(--bg-card);
                color: var(--text-main);
                font-size: 0.88rem;
                outline: none;
                font-family: inherit;
                box-sizing: border-box;
                transition: border-color 0.2s;
            }
            .locket-details-form input:focus,
            .locket-details-form textarea:focus {
                border-color: var(--primary);
            }
            .locket-stars-row {
                display: flex;
                gap: 8px;
                font-size: 1.6rem;
                cursor: pointer;
                user-select: none;
            }
            .locket-star {
                transition: transform 0.15s;
                display: inline-block;
            }
            .locket-details-form .rating-label {
                font-size: 0.75rem;
                font-weight: 700;
                color: var(--primary);
            }
            .locket-details-form .form-actions {
                display: flex;
                gap: 10px;
                margin-top: 4px;
                width: 100%;
            }
            .locket-details-form .form-actions button {
                padding: 11px;
                border-radius: 12px;
                font-weight: 700;
                font-size: 0.88rem;
                cursor: pointer;
                font-family: inherit;
                transition: all 0.2s;
                border: none;
            }
            .locket-details-form .form-actions .btn-cancel {
                flex: 1;
                background: transparent;
                border: 1.5px solid var(--border-glow);
                color: var(--text-muted);
            }
            .locket-details-form .form-actions .btn-cancel:hover {
                background: rgba(0, 0, 0, 0.04);
            }
            .locket-details-form .form-actions .btn-submit {
                flex: 2;
                background: var(--primary-grad);
                color: #fff;
                box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.25);
            }
            .locket-details-form .form-actions .btn-submit:hover {
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(var(--primary-rgb), 0.4);
            }
            .locket-autocomplete-dropdown {
                position: absolute;
                z-index: 10;
                width: 100%;
                max-height: 200px;
                overflow-y: auto;
                background: var(--bg-card);
                border: 1.5px solid var(--border-glow);
                border-radius: 12px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.25);
                margin-top: 4px;
            }
            .locket-eatery-preview-card {
                padding: 10px 12px;
                border-radius: 12px;
                background: rgba(var(--primary-rgb), 0.06);
                border: 1.5px solid rgba(var(--primary-rgb), 0.2);
                display: flex;
                align-items: center;
                gap: 10px;
                margin-top: 8px;
            }
            .locket-eatery-preview-card img {
                width: 38px;
                height: 38px;
                border-radius: 6px;
                object-fit: cover;
                border: 1px solid var(--border-glow);
            }
            .locket-eatery-preview-card .preview-details {
                flex: 1;
                min-width: 0;
            }
            .locket-eatery-preview-card .preview-details .name {
                font-weight: 700;
                color: var(--text-main);
                font-size: 0.88rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .locket-eatery-preview-card .preview-details .meta {
                font-size: 0.72rem;
                color: var(--text-muted);
                margin-top: 1px;
            }
            .locket-eatery-preview-card .clear-btn {
                background: transparent;
                border: none;
                color: var(--text-muted);
                cursor: pointer;
                font-size: 1rem;
                padding: 2px;
            }
            .locket-eatery-preview-card .clear-btn:hover {
                color: #ef4444;
            }

            /* Reactions and Floating Emojis styling (Facebook style counts) */
            .reactions-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 12px;
                padding: 8px 12px;
                background: rgba(255, 255, 255, 0.03);
                border-radius: 16px;
                border: 1px dashed var(--border-glow);
            }
            .reactions-list {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }
            .react-btn {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 4px 10px;
                background: var(--bg-card, #ffffff);
                border: 1px solid var(--border-glow, rgba(0, 0, 0, 0.08));
                border-radius: 20px;
                font-size: 0.95rem;
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
                user-select: none;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            }
            .react-btn:hover {
                transform: scale(1.15) translateY(-2px);
                border-color: var(--primary, #0ea5e9);
                box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
            }
            .react-btn:active {
                transform: scale(0.95);
            }
            .react-count {
                font-size: 0.78rem;
                font-weight: 800;
                color: var(--text-muted, #64748b);
                line-height: 1;
            }
            .react-count.active-count {
                color: #ea580c;
            }
            .reaction-total-badge {
                font-size: 0.78rem;
                font-weight: 700;
                color: var(--text-muted, #64748b);
                background: rgba(0, 0, 0, 0.04);
                padding: 4px 10px;
                border-radius: 12px;
            }
            .checkin-post-card {
                position: relative;
                overflow: visible !important;
                transform: translate3d(0, 0, 0);
                will-change: transform;
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .emoji-floater {
                position: absolute;
                pointer-events: none;
                z-index: 100;
                font-size: 2rem;
                transform: translate3d(0, 0, 0);
                will-change: transform, opacity;
                animation: floatUpAndFade 1.5s cubic-bezier(0.16, 1, 0.3, 1) both;
            }

            @keyframes floatUpAndFade {
                0% {
                    transform: translateY(0) translateX(0) scale(0.5);
                    opacity: 0;
                }
                15% {
                    opacity: 1;
                    transform: translateY(-20px) translateX(var(--drift-1)) scale(1.1);
                }
                50% {
                    transform: translateY(-100px) translateX(var(--drift-2)) scale(1);
                }
                100% {
                    transform: translateY(-220px) translateX(var(--drift-3)) scale(0.8);
                    opacity: 0;
                }
            }
        </style>

        @if(session()->has('user_id'))
        <div class="locket-widget-wrapper">
            <div class="locket-widget-card glass-panel">
                <div class="locket-header">
                    <div class="locket-live-indicator">
                        <span class="live-dot"></span> LIVE CAMERA ACTIVE
                    </div>
                    <div class="locket-title">📸 DAD Check-in</div>
                </div>
                
                <div class="locket-viewfinder-container">
                    <video id="locketVideo" autoplay playsinline muted></video>
                    <img id="locketPreview" src="" alt="Captured Preview" style="display: none;">
                    <canvas id="locketCanvas" style="display: none;"></canvas>
                    
                    <!-- Flash overlay -->
                    <div id="locketFlashOverlay"></div>
                    
                    <!-- Viewfinder overlay corners -->
                    <div class="viewfinder-overlay">
                        <span class="overlay-corner top-left"></span>
                        <span class="overlay-corner top-right"></span>
                        <span class="overlay-corner bottom-left"></span>
                        <span class="overlay-corner bottom-right"></span>
                    </div>
                    
                    <!-- Flip Camera -->
                    <button type="button" class="locket-icon-btn flip-btn" onclick="locketFlipCamera()" title="Đổi camera">
                        🔄
                    </button>
                </div>
                
                <!-- Capture Controls -->
                <div id="locketCaptureControls" class="locket-controls-row">
                    <button type="button" class="locket-control-btn gallery-btn" onclick="triggerLocketGallery()" title="Chọn ảnh từ máy">
                        🖼️
                    </button>
                    
                    <button type="button" class="locket-shutter-btn" onclick="locketCapturePhoto()" title="Chụp ảnh">
                        <span class="shutter-inner"></span>
                    </button>
                    
                    <button type="button" class="locket-control-btn gps-btn" onclick="locketAutoSelectLocation()" title="Tìm địa điểm gần đây">
                        📍
                    </button>
                </div>
                
                <!-- Hidden File Input -->
                <input type="file" id="locketFileInput" accept="image/*,video/*,.mp4,.mov,.avi,.mkv,.webm" style="display: none;" onchange="handleLocketGallerySelect(event)">
                
                <!-- Details Form (Initially Hidden) -->
                <div id="locketDetailsForm" style="display: none;" class="locket-details-form">
                    <form action="{{ route('checkin.store') }}" method="POST" enctype="multipart/form-data" id="locketCheckinForm">
                        @csrf
                        <input type="file" name="image" id="locketFormFileInput" accept="image/*,video/*,.mp4,.mov,.avi,.mkv,.webm" style="display: none;">
                        <input type="hidden" name="image_base64" id="locketFormBase64Input">
                        
                        <div class="form-group">
                            <label>📍 Địa điểm check-in <span style="color:#ef4444;">*</span></label>
                            <div style="position: relative;">
                                <input type="text" id="locketSearchInput" placeholder="Tìm tên địa điểm, quán ăn..." autocomplete="off" oninput="searchLocketEateries(this.value)">
                                <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;">🔍</span>
                            </div>
                            <div id="locketEateryDropdown" class="locket-autocomplete-dropdown" style="display: none;"></div>
                            
                            <input type="hidden" name="eatery_id" id="locketSelectedEateryId">
                            <input type="hidden" name="eatery_slug" id="locketSelectedEaterySlug">
                            
                            <!-- Selected eatery preview inside widget -->
                            <div id="locketEateryPreview" class="locket-eatery-preview-card" style="display: none;">
                                <img id="locketEateryImg" src="" alt="">
                                <div class="preview-details">
                                    <div id="locketEateryName" class="name"></div>
                                    <div id="locketEateryMeta" class="meta"></div>
                                </div>
                                <button type="button" class="clear-btn" onclick="clearLocketEatery()">✕</button>
                            </div>
                        </div>
                        
                        @if(!session('user_id'))
                            <div class="form-group">
                                <label>👤 Tên của bạn</label>
                                <input type="text" name="guest_name" placeholder="Tên hiển thị (nếu có)...">
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <label>⭐ Đánh giá</label>
                            <div class="locket-stars-row">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="locket-star" data-val="{{ $i }}" onclick="setLocketRating({{ $i }})">⭐</span>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="locketRatingInput" value="5">
                            <div id="locketRatingLabel" class="rating-label">Tuyệt vời (5/5)</div>
                        </div>
                        
                        <div class="form-group">
                            <label>💬 Cảm nhận</label>
                            <textarea name="comment" rows="2" placeholder="Ngon không? Ghi chú..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="locketResetCamera()">Chụp lại</button>
                            <button type="submit" class="btn-submit">🚀 Đăng Check-in</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @else
        {{-- Guest: show login nudge --}}
        <div class="locket-widget-wrapper">
            <div class="locket-widget-card glass-panel" style="gap: 20px; text-align: center; padding: 32px 24px;">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(var(--primary-rgb), 0.1); border: 2px solid rgba(var(--primary-rgb), 0.25); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto;">📸</div>
                <div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">Đăng nhập để Check-in</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;">Chia sẻ khoảnh khắc khám phá Đông Anh của bạn cùng cộng đồng. Chụp ảnh, đánh giá địa điểm và lưu nhật ký hành trình.</div>
                </div>
                <div style="display: flex; gap: 10px; width: 100%;">
                    <a href="/auth/login" class="btn-primary" style="flex: 1; text-align: center; padding: 11px 16px; font-size: 0.9rem; font-weight: 700; border-radius: 12px; text-decoration: none;">🔑 Đăng nhập</a>
                    <a href="/auth/register" style="flex: 1; text-align: center; padding: 11px 16px; font-size: 0.9rem; font-weight: 700; border-radius: 12px; text-decoration: none; background: transparent; border: 1.5px solid var(--border-glow); color: var(--text-main);">✨ Đăng ký</a>
                </div>
            </div>
        </div>
        @endif

        <!-- Success Alert -->
        @if(session('success'))
            <div style="background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(6,182,212,0.08)); border: 1.5px solid rgba(16,185,129,0.35); border-radius: 14px; padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 600; color: #059669; font-size: 0.95rem; animation: fadeInDown 0.5s ease;">
                <span style="font-size: 1.4rem;">✅</span>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: rgba(239,68,68,0.08); border: 1.5px solid rgba(239,68,68,0.3); border-radius: 14px; padding: 14px 20px; margin-bottom: 24px; color: #dc2626; font-size: 0.9rem;">
                <strong>⚠️ Vui lòng kiểm tra lại:</strong>
                <ul style="margin: 8px 0 0 20px; display: flex; flex-direction: column; gap: 4px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ── Hot Places strip (inline, full-width) ── -->
        @if($standaloneCheckins->isNotEmpty())
            @php
                $hotPlaces = $standaloneCheckins
                    ->groupBy('eatery_id')
                    ->sortByDesc(fn($g) => $g->count())
                    ->take(5);
            @endphp
            <div style="margin-bottom: 24px;">
                <p style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px;">🔥 Địa điểm được check-in nhiều nhất</p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    @foreach($hotPlaces as $eateryId => $group)
                        @php $hotEatery = $group->first()->eatery; @endphp
                        @if($hotEatery)
                            <a href="/dia-diem/{{ $hotEatery->slug }}"
                                style="display: flex; align-items: center; gap: 9px; background: var(--bg-card); border: 1.5px solid var(--border-glow); border-radius: 40px; padding: 6px 14px 6px 7px; text-decoration: none; transition: all 0.2s; white-space: nowrap;"
                                onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.borderColor='var(--border-glow)'; this.style.transform=''">
                                <img src="{{ $hotEatery->image_path ?? 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=60&q=60' }}"
                                    alt="{{ $hotEatery->name }}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--border-glow);">
                                <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">{{ $hotEatery->name }}</span>
                                <span style="font-size: 0.72rem; font-weight: 600; color: var(--primary); background: rgba(var(--primary-rgb), 0.1); padding: 2px 7px; border-radius: 20px;">{{ $group->count() }} 📸</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <div style="width: 100%;">

                @auth
                <div class="feed-tabs" style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 1.5px solid var(--border-glow); padding-bottom: 12px;">
                    <button class="feed-tab-btn active" onclick="switchFeedTab('all', this)" style="background: rgba(14, 165, 233, 0.1); border: none; font-size: 0.95rem; font-weight: 800; color: var(--primary); cursor: pointer; padding: 6px 14px; border-radius: 20px; transition: all 0.3s; display: flex; align-items: center; gap: 8px;">
                        🌍 Cộng đồng Check-in <span class="tab-badge" style="background: rgba(14, 165, 233, 0.15); color: var(--primary); font-size: 0.72rem; padding: 2px 8px; border-radius: 20px;">{{ $standaloneCheckins->count() + $diaries->count() }}</span>
                    </button>
                    <button class="feed-tab-btn" onclick="switchFeedTab('my', this)" style="background: none; border: none; font-size: 0.95rem; font-weight: 800; color: var(--text-muted); cursor: pointer; padding: 6px 14px; border-radius: 20px; transition: all 0.3s; display: flex; align-items: center; gap: 8px;">
                        👤 Bài viết của tôi <span class="tab-badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); font-size: 0.72rem; padding: 2px 8px; border-radius: 20px;">{{ $standaloneCheckins->where('user_id', auth()->id())->count() + $diaries->where('user_id', auth()->id())->count() }}</span>
                    </button>
                </div>
                @endauth

                <!-- ========================================================
                     SECTION: Standalone Check-ins
                     ======================================================== -->
                @if($standaloneCheckins->isNotEmpty())
                    <div id="standaloneCheckinsSection" style="margin-bottom: 8px;">
                        <h2 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading); display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <span style="background: var(--primary-grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">📍 Check-in địa điểm</span>
                        </h2>
                        @foreach($standaloneCheckins as $checkin)
                            <article class="checkin-post-card glass-panel" data-checkin-id="{{ $checkin->id }}" data-user-id="{{ $checkin->user_id ?? '' }}" style="margin-bottom: 20px;">
                                <!-- Post Author Header -->
                                <div class="post-header">
                                    <div class="post-author">
                                        <div class="author-avatar">
                                            {{ $checkin->user ? mb_substr($checkin->user->name, 0, 1, 'UTF-8') : '👤' }}
                                        </div>
                                        <div class="author-meta">
                                            <span class="author-name" style="display: inline-flex; align-items: center; gap: 4px;">
                                                {{ $checkin->display_name }}
                                                @if($checkin->user && $checkin->user->role === 'admin')
                                                    <span title="Tài khoản Quản trị viên (Admin)" style="color: #ef4444; font-size: 0.95rem;">⭐</span>
                                                @endif
                                            </span>
                                            <span class="author-role-badge {{ ($checkin->user && $checkin->user->role === 'admin') ? 'role-admin' : 'role-user' }}">
                                                {{ ($checkin->user && $checkin->user->role === 'admin') ? 'Admin' : 'Thực khách' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                                        <div class="rating-stars-container">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="star-icon {{ $i <= $checkin->rating ? 'filled' : 'empty' }}">★</span>
                                            @endfor
                                        </div>
                                        <span class="post-time">📅 {{ $checkin->created_at->diffForHumans() }} ({{ $checkin->created_at->format('d/m/Y H:i') }})</span>
                                    </div>
                                </div>

                                <!-- Eatery badge -->
                                @if($checkin->eatery)
                                    <a href="/dia-diem/{{ $checkin->eatery->slug }}" class="post-tour-link">
                                        📍 {{ $checkin->eatery->name }}
                                        @if($checkin->eatery->category)
                                            <span style="font-weight: 500; opacity: 0.75;">• {{ $checkin->eatery->category->name }}</span>
                                        @endif
                                        @if($checkin->eatery->commune)
                                            <span style="font-weight: 500; opacity: 0.75;">• {{ $checkin->eatery->commune->name }}</span>
                                        @endif
                                    </a>
                                @endif

                                <!-- Comment -->
                                @if($checkin->comment)
                                    <div class="post-comment">
                                        "{{ $checkin->comment }}"
                                    </div>
                                @endif

                                <!-- Photo -->
                                @if($checkin->image_path)
                                    <div class="post-photo-wrapper">
                                        <img src="{{ $checkin->image_path }}" alt="Ảnh check-in" class="post-photo-img" loading="lazy"
                                            onclick="openStopImageModal('{{ $checkin->image_path }}')">
                                    </div>
                                @endif

                                <!-- Reactions Bar -->
                                <div class="reactions-bar">
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span style="font-size:0.8rem; font-weight:800; color:var(--text-muted);">Thả cảm xúc:</span>
                                        <div class="reactions-list" data-post-id="{{ $checkin->id }}" data-post-type="checkin">
                                            @php
                                                $rCounts = $checkin->reaction_counts ?? ['❤️'=>0, '🔥'=>0, '👍'=>0, '😂'=>0, '😍'=>0, '🤤'=>0];
                                            @endphp
                                            <button type="button" class="react-btn" data-emoji="❤️" onclick="sendReaction({{ $checkin->id }}, 'checkin', '❤️', event)">
                                                <span>❤️</span> <span class="react-count {{ ($rCounts['❤️'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['❤️'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="🔥" onclick="sendReaction({{ $checkin->id }}, 'checkin', '🔥', event)">
                                                <span>🔥</span> <span class="react-count {{ ($rCounts['🔥'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['🔥'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="👍" onclick="sendReaction({{ $checkin->id }}, 'checkin', '👍', event)">
                                                <span>👍</span> <span class="react-count {{ ($rCounts['👍'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['👍'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="😂" onclick="sendReaction({{ $checkin->id }}, 'checkin', '😂', event)">
                                                <span>😂</span> <span class="react-count {{ ($rCounts['😂'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['😂'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="😍" onclick="sendReaction({{ $checkin->id }}, 'checkin', '😍', event)">
                                                <span>😍</span> <span class="react-count {{ ($rCounts['😍'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['😍'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="🤤" onclick="sendReaction({{ $checkin->id }}, 'checkin', '🤤', event)">
                                                <span>🤤</span> <span class="react-count {{ ($rCounts['🤤'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['🤤'] ?? 0 }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="reaction-total-badge" style="{{ ($checkin->reaction_total ?? 0) > 0 ? '' : 'display:none;' }}">
                                        <span class="total-num">{{ $checkin->reaction_total ?? 0 }}</span> lượt bày tỏ
                                    </div>
                                </div>

                                <!-- Comments Section -->
                                <div class="comments-section" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border-glow); display: flex; flex-direction: column; gap: 10px;">
                                    <h5 class="comments-count" style="margin: 0; font-size: 0.85rem; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">💬 Bình luận ({{ $checkin->comments->count() }})</h5>
                                    <div class="comments-list" style="display: flex; flex-direction: column; gap: 8px;">
                                        @foreach($checkin->comments as $comment)
                                            <div class="comment-item" data-comment-id="{{ $comment->id }}" style="display: flex; gap: 10px; align-items: flex-start; background: rgba(255, 255, 255, 0.02); border-radius: 12px; padding: 10px 14px; border: 1px solid rgba(255,255,255,0.03);">
                                                <div class="comment-avatar" style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, rgba(var(--primary-rgb),0.1), rgba(16,185,129,0.08)); border: 1px solid rgba(var(--primary-rgb),0.2); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: var(--text-main); flex-shrink: 0;">
                                                    {{ $comment->user ? mb_substr($comment->user->name, 0, 1, 'UTF-8') : '👤' }}
                                                </div>
                                                <div style="flex: 1; display: flex; flex-direction: column; gap: 3px;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-main);">
                                                            {{ $comment->display_name }}
                                                            @if($comment->user && $comment->user->role === 'admin')
                                                                <span style="font-size: 0.65rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-radius: 4px; padding: 1px 4px; margin-left: 4px;">Admin</span>
                                                            @endif
                                                        </span>
                                                        <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $comment->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">{{ $comment->content }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Form bình luận -->
                                    <form action="{{ route('comments.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 8px; margin-top: 5px;">
                                        @csrf
                                        <input type="hidden" name="commentable_id" value="{{ $checkin->id }}">
                                        <input type="hidden" name="commentable_type" value="App\Models\Checkin">

                                        @if(!auth()->check())
                                            <div style="width: 100%; display: flex; gap: 10px; align-items: center;">
                                                <input type="text" name="guest_name" placeholder="Tên của bạn (Khách vãng lai)..." 
                                                    style="flex: 1; padding: 8px 12px; border-radius: 8px; border: 1.5px solid var(--border-glow); background: rgba(0,0,0,0.15); color: var(--text-main); font-size: 0.8rem; outline: none; transition: border-color 0.2s;"
                                                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-glow)'">
                                            </div>
                                        @endif

                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <input type="text" name="content" placeholder="Viết bình luận của bạn..." required
                                                style="flex: 1; padding: 8px 16px; border-radius: 30px; border: 1.5px solid var(--border-glow); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 0.88rem; outline: none; transition: border-color 0.2s;"
                                                onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-glow)'">
                                            <button type="submit" 
                                                style="padding: 8px 18px; border-radius: 30px; border: none; background: var(--primary-grad); color: #fff; font-weight: 700; font-size: 0.82rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);"
                                                onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(var(--primary-rgb), 0.35)'"
                                                onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(var(--primary-rgb), 0.2)'">
                                                💬 Gửi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <!-- ========================================================
                     SECTION: Food Tour Diaries
                     ======================================================== -->
                @if($diaries->isNotEmpty())
                    <div id="diariesSection">
                        <h2 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading); display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <span style="background: var(--primary-grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">🗺️ Nhật ký Food Tour</span>
                        </h2>
                        @foreach($diaries as $diary)
                            <article class="checkin-post-card glass-panel" data-diary-id="{{ $diary->id }}" data-user-id="{{ $diary->user_id ?? '' }}" style="margin-bottom: 20px;">
                                <!-- Post Author Header -->
                                <div class="post-header">
                                    <div class="post-author">
                                        <div class="author-avatar">
                                            {{ $diary->user->avatar ?? '👤' }}
                                        </div>
                                        <div class="author-meta">
                                            <span class="author-name">{{ $diary->user->name ?? 'Thực khách Đông Anh' }}</span>
                                            <span class="author-role-badge {{ ($diary->user && $diary->user->role === 'admin') ? 'role-admin' : 'role-user' }}">
                                                {{ ($diary->user && $diary->user->role === 'admin') ? 'Admin' : 'Thực khách' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                                        <div class="rating-stars-container">
                                            @if($diary->rating)
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="star-icon {{ $i <= $diary->rating ? 'filled' : 'empty' }}">★</span>
                                                @endfor
                                            @else
                                                <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Đã hoàn thành</span>
                                            @endif
                                        </div>
                                        <span class="post-time">📅 {{ $diary->created_at->diffForHumans() }} ({{ $diary->created_at->format('d/m/Y H:i') }})</span>
                                    </div>
                                </div>

                                @if($diary->foodTour)
                                    <a href="/food-tour/{{ $diary->foodTour->slug }}" class="post-tour-link">
                                        🎯 Hành trình: {{ $diary->foodTour->name }}
                                    </a>
                                @endif

                                @if($diary->comment)
                                    <div class="post-comment">
                                        "{{ $diary->comment }}"
                                    </div>
                                @endif

                                @if($diary->image_path)
                                    <div class="post-photo-wrapper">
                                        <img src="{{ $diary->image_path }}" alt="Ảnh checkin của {{ $diary->user->name ?? 'Thực khách' }}" class="post-photo-img" loading="lazy">
                                    </div>
                                @endif

                                @if(!empty($diary->stop_reviews) && $diary->foodTour)
                                    <div style="margin-top: 20px; padding-top: 18px; border-top: 1px dashed var(--border-glow);">
                                        <h4 class="stops-summary-title">📍 Chi tiết hành trình chặng đi:</h4>
                                        <div class="stops-timeline">
                                            @foreach($diary->stop_reviews as $stopIdx => $stopRev)
                                                @php
                                                    $eateryId = $stopRev['eatery_id'] ?? null;
                                                    if (!$eateryId) {
                                                        $tourStop = $diary->foodTour->stops->firstWhere('stop_order', $stopIdx + 1);
                                                        $eateryId = $tourStop?->eatery_id;
                                                    }
                                                    $eatery = $eateryId ? $eateriesMap->get($eateryId) : null;
                                                @endphp
                                                <div class="timeline-node">
                                                    <div class="stop-detail-card">
                                                        <div class="stop-header">
                                                            <div class="stop-info">
                                                                @if($eatery)
                                                                    <a href="/dia-diem/{{ $eatery->slug }}" class="stop-name">
                                                                        {{ $stopIdx + 1 }}. {{ $eatery->name }}
                                                                    </a>
                                                                    <span class="stop-category">
                                                                        🔖 {{ $eatery->category->name ?? 'Ẩm thực' }} • 📍 {{ $eatery->commune->name ?? 'Đông Anh' }}
                                                                    </span>
                                                                @else
                                                                    <span class="stop-name" style="color: var(--text-muted);">
                                                                        {{ $stopIdx + 1 }}. Điểm dừng chưa đồng bộ
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            @if(!empty($stopRev['rating']))
                                                                <div class="rating-stars-container" style="scale: 0.9;">
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                        <span class="star-icon {{ $i <= $stopRev['rating'] ? 'filled' : 'empty' }}">★</span>
                                                                    @endfor
                                                                </div>
                                                            @endif
                                                        </div>
                                                        @if(!empty($stopRev['comment']))
                                                            <p class="stop-comment">💭 {{ $stopRev['comment'] }}</p>
                                                        @endif
                                                        @if(!empty($stopRev['image_path']))
                                                            <div class="stop-media-grid">
                                                                <img src="{{ $stopRev['image_path'] }}" alt="Ảnh chặng {{ $stopIdx + 1 }}" class="stop-media-thumb" onclick="openStopImageModal('{{ $stopRev['image_path'] }}')" loading="lazy">
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Reactions Bar -->
                                <div class="reactions-bar">
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span style="font-size:0.8rem; font-weight:800; color:var(--text-muted);">Thả cảm xúc:</span>
                                        <div class="reactions-list" data-post-id="{{ $diary->id }}" data-post-type="diary">
                                            @php
                                                $rCounts = $diary->reaction_counts ?? ['❤️'=>0, '🔥'=>0, '👍'=>0, '😂'=>0, '😍'=>0, '🤤'=>0];
                                            @endphp
                                            <button type="button" class="react-btn" data-emoji="❤️" onclick="sendReaction({{ $diary->id }}, 'diary', '❤️', event)">
                                                <span>❤️</span> <span class="react-count {{ ($rCounts['❤️'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['❤️'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="🔥" onclick="sendReaction({{ $diary->id }}, 'diary', '🔥', event)">
                                                <span>🔥</span> <span class="react-count {{ ($rCounts['🔥'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['🔥'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="👍" onclick="sendReaction({{ $diary->id }}, 'diary', '👍', event)">
                                                <span>👍</span> <span class="react-count {{ ($rCounts['👍'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['👍'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="😂" onclick="sendReaction({{ $diary->id }}, 'diary', '😂', event)">
                                                <span>😂</span> <span class="react-count {{ ($rCounts['😂'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['😂'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="😍" onclick="sendReaction({{ $diary->id }}, 'diary', '😍', event)">
                                                <span>😍</span> <span class="react-count {{ ($rCounts['😍'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['😍'] ?? 0 }}</span>
                                            </button>
                                            <button type="button" class="react-btn" data-emoji="🤤" onclick="sendReaction({{ $diary->id }}, 'diary', '🤤', event)">
                                                <span>🤤</span> <span class="react-count {{ ($rCounts['🤤'] ?? 0) > 0 ? 'active-count' : '' }}">{{ $rCounts['🤤'] ?? 0 }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="reaction-total-badge" style="{{ ($diary->reaction_total ?? 0) > 0 ? '' : 'display:none;' }}">
                                        <span class="total-num">{{ $diary->reaction_total ?? 0 }}</span> lượt bày tỏ
                                    </div>
                                </div>

                                <!-- Comments Section -->
                                <div class="comments-section" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border-glow); display: flex; flex-direction: column; gap: 10px;">
                                    <h5 class="comments-count" style="margin: 0; font-size: 0.85rem; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">💬 Bình luận ({{ $diary->comments->count() }})</h5>
                                    <div class="comments-list" style="display: flex; flex-direction: column; gap: 8px;">
                                        @foreach($diary->comments as $comment)
                                            <div class="comment-item" data-comment-id="{{ $comment->id }}" style="display: flex; gap: 10px; align-items: flex-start; background: rgba(255, 255, 255, 0.02); border-radius: 12px; padding: 10px 14px; border: 1px solid rgba(255,255,255,0.03);">
                                                <div class="comment-avatar" style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, rgba(var(--primary-rgb),0.1), rgba(16,185,129,0.08)); border: 1px solid rgba(var(--primary-rgb),0.2); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: var(--text-main); flex-shrink: 0;">
                                                    {{ $comment->user ? mb_substr($comment->user->name, 0, 1, 'UTF-8') : '👤' }}
                                                </div>
                                                <div style="flex: 1; display: flex; flex-direction: column; gap: 3px;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-main);">
                                                            {{ $comment->display_name }}
                                                            @if($comment->user && $comment->user->role === 'admin')
                                                                <span style="font-size: 0.65rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-radius: 4px; padding: 1px 4px; margin-left: 4px;">Admin</span>
                                                            @endif
                                                        </span>
                                                        <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $comment->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">{{ $comment->content }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Form bình luận -->
                                    <form action="{{ route('comments.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 8px; margin-top: 5px;">
                                        @csrf
                                        <input type="hidden" name="commentable_id" value="{{ $diary->id }}">
                                        <input type="hidden" name="commentable_type" value="App\Models\FoodTourDiary">

                                        @if(!auth()->check())
                                            <div style="width: 100%; display: flex; gap: 10px; align-items: center;">
                                                <input type="text" name="guest_name" placeholder="Tên của bạn (Khách vãng lai)..." 
                                                    style="flex: 1; padding: 8px 12px; border-radius: 8px; border: 1.5px solid var(--border-glow); background: rgba(0,0,0,0.15); color: var(--text-main); font-size: 0.8rem; outline: none; transition: border-color 0.2s;"
                                                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-glow)'">
                                            </div>
                                        @endif

                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <input type="text" name="content" placeholder="Viết bình luận của bạn..." required
                                                style="flex: 1; padding: 8px 16px; border-radius: 30px; border: 1.5px solid var(--border-glow); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 0.88rem; outline: none; transition: border-color 0.2s;"
                                                onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-glow)'">
                                            <button type="submit" 
                                                style="padding: 8px 18px; border-radius: 30px; border: none; background: var(--primary-grad); color: #fff; font-weight: 700; font-size: 0.82rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);"
                                                onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(var(--primary-rgb), 0.35)'"
                                                onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(var(--primary-rgb), 0.2)'">
                                                💬 Gửi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if($standaloneCheckins->isEmpty() && $diaries->isEmpty())
                    <div class="feed-empty-state glass-panel">
                        <span style="font-size: 3.5rem; display: block; margin-bottom: 16px;">📸</span>
                        <h3 style="font-size: 1.3rem; font-family: var(--font-heading); font-weight: 700; margin-bottom: 8px; color: var(--text-main);">Chưa có check-in nào</h3>
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 24px; max-width: 480px; margin-left: auto; margin-right: auto;">Hãy là người đầu tiên chia sẻ khoảnh khắc tại các địa điểm ẩm thực Đông Anh!</p>
                        <button onclick="openCheckinModal()" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 30px; border: none; cursor: pointer;">
                            📍 Đăng check-in đầu tiên
                        </button>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- Simple Lightbox Modal -->
<div id="imageLightbox" style="position: fixed; inset: 0; background: rgba(9, 9, 11, 0.9); z-index: 99999; display: none; align-items: center; justify-content: center; backdrop-filter: blur(10px);" onclick="closeStopImageModal()">
    <button style="position: absolute; top: 20px; right: 20px; background: transparent; border: none; color: #fff; font-size: 2.5rem; cursor: pointer;" onclick="closeStopImageModal()">&times;</button>
    <img id="lightboxImg" src="" alt="Zoomed checkin view" style="max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
</div>

<script>
    /* ========================================================
       Lightbox
       ======================================================== */
    function openStopImageModal(imgSrc) {
        const lightbox = document.getElementById('imageLightbox');
        document.getElementById('lightboxImg').src = imgSrc;
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeStopImageModal() {
        document.getElementById('imageLightbox').style.display = 'none';
        document.body.style.overflow = '';
    }

    /* ========================================================
       Locket Camera First logic
       ======================================================== */
    let locketStream = null;
    let locketFacingMode = 'environment'; // 'environment' or 'user'
    let selectedLocketRating = 5;
    let locketSearchTimeout = null;

    // Start camera stream automatically on page load or when returning to page
    async function startLocketCamera(force = false) {
        const video = document.getElementById('locketVideo');
        if (!video) return;

        // Check if stream is active and video is currently playing
        if (!force && locketStream) {
            const tracks = locketStream.getVideoTracks();
            if (tracks.length > 0 && tracks[0].readyState === 'live' && video.srcObject === locketStream && !video.paused) {
                return; // Stream is already active and running
            }
            tracks.forEach(t => t.stop());
            locketStream = null;
        } else if (locketStream) {
            locketStream.getTracks().forEach(t => t.stop());
            locketStream = null;
        }

        try {
            locketStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: locketFacingMode, width: { ideal: 640 }, height: { ideal: 640 } },
                audio: false
            });
            video.srcObject = locketStream;
            video.play().catch(e => console.warn('Video play warning:', e));
            video.style.display = 'block';
            const preview = document.getElementById('locketPreview');
            if (preview) preview.style.display = 'none';
        } catch (err) {
            console.warn('Camera access denied or unavailable. Showing fallback UI.', err);
            // Replace viewfinder with fallback UI for uploading
            const container = document.querySelector('.locket-viewfinder-container');
            if (container) {
                container.innerHTML = `
                    <div class="locket-fallback-view" onclick="triggerLocketGallery()">
                        <span class="icon">📁</span>
                        <span style="font-weight: 700; font-size: 0.95rem;">Không thể truy cập camera</span>
                        <span style="font-size: 0.78rem;">Nhấn vào đây để tải ảnh lên từ thiết bị</span>
                    </div>
                    <!-- Keep flash & corner elements -->
                    <div id="locketFlashOverlay"></div>
                    <div class="viewfinder-overlay">
                        <span class="overlay-corner top-left"></span>
                        <span class="overlay-corner top-right"></span>
                        <span class="overlay-corner bottom-left"></span>
                        <span class="overlay-corner bottom-right"></span>
                    </div>
                `;
            }
        }
    }

    async function locketFlipCamera() {
        locketFacingMode = locketFacingMode === 'environment' ? 'user' : 'environment';
        await startLocketCamera();
    }

    function locketCapturePhoto() {
        const video = document.getElementById('locketVideo');
        const canvas = document.getElementById('locketCanvas');
        const preview = document.getElementById('locketPreview');
        const flash = document.getElementById('locketFlashOverlay');

        if (!video || !canvas || !preview) return;

        // Flash effect
        if (flash) {
            flash.style.opacity = '1';
            setTimeout(() => { flash.style.opacity = '0'; }, 100);
        }

        // Draw to square canvas
        const size = Math.min(video.videoWidth, video.videoHeight) || 640;
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');

        // Center crop to square
        const sx = (video.videoWidth - size) / 2;
        const sy = (video.videoHeight - size) / 2;

        if (locketFacingMode === 'user') {
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
        }

        ctx.drawImage(video, sx, sy, size, size, 0, 0, size, size);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        preview.src = dataUrl;
        preview.style.display = 'block';
        video.style.display = 'none';

        // Save base64
        document.getElementById('locketFormBase64Input').value = dataUrl;

        // Convert dataUrl to Blob and put in form file input
        canvas.toBlob(function(blob) {
            const file = new File([blob], 'locket_snap.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            try {
                document.getElementById('locketFormFileInput').files = dt.files;
            } catch(e) {
                console.warn(e);
            }
        }, 'image/jpeg', 0.9);

        // Hide capture controls, show form details
        document.getElementById('locketCaptureControls').style.display = 'none';
        document.getElementById('locketDetailsForm').style.display = 'flex';
    }

    function triggerLocketGallery() {
        document.getElementById('locketFileInput').click();
    }

    function handleLocketGallerySelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('locketPreview');
            const video = document.getElementById('locketVideo');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            if (video) video.style.display = 'none';

            // Save base64
            document.getElementById('locketFormBase64Input').value = e.target.result;

            // Also put file in form file input
            const dt = new DataTransfer();
            dt.items.add(file);
            try {
                document.getElementById('locketFormFileInput').files = dt.files;
            } catch(err) {
                console.warn(err);
            }

            // Expand form
            document.getElementById('locketCaptureControls').style.display = 'none';
            document.getElementById('locketDetailsForm').style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }

    function locketResetCamera() {
        document.getElementById('locketPreview').style.display = 'none';
        document.getElementById('locketDetailsForm').style.display = 'none';
        document.getElementById('locketCaptureControls').style.display = 'flex';
        document.getElementById('locketFormFileInput').value = '';
        document.getElementById('locketFormBase64Input').value = '';
        startLocketCamera();
    }

    // Geolocation autocomplete for locket
    function searchLocketEateries(query) {
        clearTimeout(locketSearchTimeout);
        const dropdown = document.getElementById('locketEateryDropdown');
        if (!query || query.length < 1) {
            dropdown.style.display = 'none';
            return;
        }
        locketSearchTimeout = setTimeout(() => {
            fetch(`/api/eateries/search?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(results => renderLocketEateryDropdown(results));
        }, 300);
    }

    function renderLocketEateryDropdown(results) {
        const dropdown = document.getElementById('locketEateryDropdown');
        if (!results.length) {
            dropdown.innerHTML = `<div style="padding: 10px 12px; color: var(--text-muted); font-size: 0.8rem; text-align: center;">Không tìm thấy địa điểm</div>`;
            dropdown.style.display = 'block';
            return;
        }
        dropdown.innerHTML = results.map(e => `
            <div onclick="selectLocketEatery(${e.id}, '${e.name.replace(/'/g, "\\'").replace(/"/g, '&quot;')}', '${(e.category||'').replace(/'/g,"\\'")}', '${(e.commune||'').replace(/'/g,"\\'")}', '${e.image ?? ''}', '${e.slug}')"
                style="display: flex; align-items: center; gap: 10px; padding: 8px 10px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid var(--border-glow);"
                onmouseover="this.style.background='rgba(var(--primary-rgb), 0.05)'" onmouseout="this.style.background='transparent'">
                <img src="${e.image || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=40&q=40'}" alt="${e.name}" style="width: 32px; height: 32px; border-radius: 6px; object-fit: cover; flex-shrink: 0;">
                <div style="flex:1; min-width: 0;">
                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${e.name}</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">${e.category} • ${e.commune}</div>
                </div>
            </div>
        `).join('');
        dropdown.style.display = 'block';
    }

    function selectLocketEatery(id, name, category, commune, image, slug) {
        document.getElementById('locketSelectedEateryId').value = id;
        document.getElementById('locketSelectedEaterySlug').value = slug;
        document.getElementById('locketSearchInput').value = name;
        document.getElementById('locketEateryDropdown').style.display = 'none';

        const preview = document.getElementById('locketEateryPreview');
        document.getElementById('locketEateryImg').src = image || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=40&q=40';
        document.getElementById('locketEateryName').textContent = name;
        document.getElementById('locketEateryMeta').textContent = `${category} • ${commune}`;
        preview.style.display = 'flex';
    }

    function clearLocketEatery() {
        document.getElementById('locketSelectedEateryId').value = '';
        document.getElementById('locketSelectedEaterySlug').value = '';
        document.getElementById('locketSearchInput').value = '';
        document.getElementById('locketEateryPreview').style.display = 'none';
    }

    // Auto location lookup using GPS
    function locketAutoSelectLocation() {
        if (!navigator.geolocation) {
            alert('Trình duyệt không hỗ trợ GPS.');
            return;
        }
        const gpsBtn = document.querySelector('.locket-controls-row .gps-btn');
        const origText = gpsBtn.innerHTML;
        gpsBtn.innerHTML = '⏳';
        gpsBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                fetch(`/api/eateries/nearby?lat=${lat}&lng=${lng}&radius=5`)
                    .then(r => r.json())
                    .then(results => {
                        gpsBtn.innerHTML = origText;
                        gpsBtn.disabled = false;
                        if (results && results.length > 0) {
                            const nearest = results[0];
                            selectLocketEatery(nearest.id, nearest.name, nearest.category, nearest.commune, nearest.image, nearest.slug);
                        } else {
                            alert('Không tìm thấy địa điểm nào gần bạn trong bán kính 5km.');
                        }
                    })
                    .catch(() => {
                        gpsBtn.innerHTML = origText;
                        gpsBtn.disabled = false;
                        alert('Lỗi định vị.');
                    });
            },
            () => {
                gpsBtn.innerHTML = origText;
                gpsBtn.disabled = false;
                alert('Không thể xác định vị trí. Vui lòng cấp quyền.');
            },
            { timeout: 8000, enableHighAccuracy: true }
        );
    }

    // Locket rating stars
    const ratingLabels = ['', 'Tệ (1/5)', 'Tạm được (2/5)', 'Bình thường (3/5)', 'Tốt (4/5)', 'Tuyệt vời (5/5)'];
    function setLocketRating(val) {
        selectedLocketRating = val;
        document.getElementById('locketRatingInput').value = val;
        document.getElementById('locketRatingLabel').textContent = ratingLabels[val];
        document.querySelectorAll('.locket-star').forEach((star, idx) => {
            star.textContent = idx < val ? '⭐' : '☆';
            star.style.transform = idx < val ? 'scale(1.15)' : 'scale(1)';
        });
    }

    // Submit validation
    document.getElementById('locketCheckinForm').addEventListener('submit', function(e) {
        const eateryId = document.getElementById('locketSelectedEateryId').value;
        if (!eateryId) {
            e.preventDefault();
            document.getElementById('locketSearchInput').focus();
            document.getElementById('locketSearchInput').style.borderColor = '#ef4444';
            setTimeout(() => { document.getElementById('locketSearchInput').style.borderColor = 'var(--border-glow)'; }, 2000);
            alert('⚠️ Vui lòng chọn địa điểm check-in!');
            return;
        }
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.textContent = '⏳ Đang đăng...';
        submitBtn.disabled = true;
    });

    // Auto open camera on load & auto refresh camera when returning to page/tab
    document.addEventListener('DOMContentLoaded', () => {
        startLocketCamera();
        setLocketRating(5);
    });

    window.addEventListener('pageshow', () => {
        startLocketCamera();
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            startLocketCamera();
        }
    });

    window.addEventListener('focus', () => {
        startLocketCamera();
    });

    // Auto expand/fill form if there are validation errors on redirect
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('locketCaptureControls').style.display = 'none';
            document.getElementById('locketDetailsForm').style.display = 'flex';
        });
    @endif

    /* ========================================================
       Floating Emoji Reactions & Counts Logic
       ======================================================== */
    function sendReaction(id, type, emoji, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        // Spawn floating animation locally
        spawnFloatingEmojis(id, type, emoji, event);

        // Optimistically update local counter UI
        const list = document.querySelector(`article.checkin-post-card[data-${type}-id="${id}"] .reactions-list`);
        if (list) {
            const btn = list.querySelector(`button[data-emoji="${emoji}"]`);
            if (btn) {
                const countSpan = btn.querySelector('.react-count');
                if (countSpan) {
                    let val = parseInt(countSpan.textContent) || 0;
                    countSpan.textContent = val + 1;
                    countSpan.classList.add('active-count');
                }
            }
        }

        // POST request to backend
        fetch(`/api/checkins/${id}/react`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || ''
            },
            body: JSON.stringify({ type: type, emoji: emoji })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.counts) {
                updateReactionCounts(id, type, data.counts, data.total);
            }
        })
        .catch(err => console.error('Failed to send reaction:', err));
    }

    function updateReactionCounts(id, type, counts, total) {
        const list = document.querySelector(`article.checkin-post-card[data-${type}-id="${id}"] .reactions-list`);
        if (!list) return;

        for (const [emoji, cnt] of Object.entries(counts)) {
            const btn = list.querySelector(`button[data-emoji="${emoji}"]`);
            if (btn) {
                const countSpan = btn.querySelector('.react-count');
                if (countSpan) {
                    countSpan.textContent = cnt;
                    if (cnt > 0) {
                        countSpan.classList.add('active-count');
                    } else {
                        countSpan.classList.remove('active-count');
                    }
                }
            }
        }

        const card = list.closest('article.checkin-post-card');
        if (card) {
            const totalBadge = card.querySelector('.reaction-total-badge');
            if (totalBadge) {
                const totalNum = totalBadge.querySelector('.total-num');
                if (totalNum) totalNum.textContent = total;
                totalBadge.style.display = total > 0 ? 'inline-block' : 'none';
            }
        }
    }

    function spawnFloatingEmojis(id, type, emoji, event) {
        let card = null;
        if (type === 'checkin') {
            card = document.querySelector(`article.checkin-post-card[data-checkin-id="${id}"]`);
        } else {
            card = document.querySelector(`article.checkin-post-card[data-diary-id="${id}"]`);
        }
        if (!card) return;

        // Calculate spawn point
        let startX, startY;
        if (event) {
            // Spawn around click coordinates
            const rect = card.getBoundingClientRect();
            startX = event.clientX - rect.left;
            startY = event.clientY - rect.top;
        } else {
            // Spawn from the bottom center of the card (for broadcasted reactions)
            startX = card.clientWidth / 2;
            startY = card.clientHeight - 40;
        }

        // Spawn a burst of 8-12 emojis
        const burstCount = 8 + Math.floor(Math.random() * 5);
        for (let i = 0; i < burstCount; i++) {
            const floater = document.createElement('span');
            floater.className = 'emoji-floater';
            floater.textContent = emoji;

            // Randomize drift animation parameters
            floater.style.setProperty('--drift-1', (Math.random() * 60 - 30) + 'px');
            floater.style.setProperty('--drift-2', (Math.random() * 120 - 60) + 'px');
            floater.style.setProperty('--drift-3', (Math.random() * 180 - 90) + 'px');

            // Apply randomized positions
            floater.style.left = (startX + Math.random() * 50 - 25) + 'px';
            floater.style.top = (startY + Math.random() * 20 - 10) + 'px';

            // Randomize slight animation delays for natural burst effect
            floater.style.animationDelay = (Math.random() * 0.25) + 's';

            card.appendChild(floater);

            // Cleanup after animation ends
            setTimeout(() => {
                floater.remove();
            }, 1800);
        }
    }

    function switchFeedTab(tab, btn) {
        // 1. Update active tab styles
        document.querySelectorAll('.feed-tab-btn').forEach(b => {
            b.style.color = 'var(--text-muted)';
            b.style.background = 'none';
            const badge = b.querySelector('.tab-badge');
            if (badge) {
                badge.style.background = 'rgba(255, 255, 255, 0.05)';
                badge.style.color = 'var(--text-muted)';
            }
        });
        
        btn.style.color = 'var(--primary)';
        btn.style.background = 'rgba(14, 165, 233, 0.1)';
        const activeBadge = btn.querySelector('.tab-badge');
        if (activeBadge) {
            activeBadge.style.background = 'rgba(14, 165, 233, 0.15)';
            activeBadge.style.color = 'var(--primary)';
        }

        // 2. Filter posts in DOM
        const currentUserId = @json(auth()->id());
        
        document.querySelectorAll('.checkin-post-card').forEach(card => {
            const cardUserId = card.getAttribute('data-user-id');
            if (tab === 'all') {
                card.style.display = 'block';
            } else if (tab === 'my') {
                if (cardUserId && parseInt(cardUserId) === parseInt(currentUserId)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        });
        
        // 3. Show empty state if no posts visible in current tab
        const hasVisibleCheckins = Array.from(document.querySelectorAll('[data-checkin-id]')).some(c => c.style.display !== 'none');
        const hasVisibleDiaries = Array.from(document.querySelectorAll('[data-diary-id]')).some(d => d.style.display !== 'none');
        
        const checkinsSection = document.getElementById('standaloneCheckinsSection');
        const diariesSection = document.getElementById('diariesSection');
        
        if (checkinsSection) {
            checkinsSection.style.display = hasVisibleCheckins ? 'block' : 'none';
        }
        if (diariesSection) {
            diariesSection.style.display = hasVisibleDiaries ? 'block' : 'none';
        }
        
        let emptyState = document.getElementById('myFeedEmptyState');
        if (tab === 'my' && !hasVisibleCheckins && !hasVisibleDiaries) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.id = 'myFeedEmptyState';
                emptyState.className = 'glass-panel';
                emptyState.style.padding = '40px';
                emptyState.style.textAlign = 'center';
                emptyState.style.borderRadius = '16px';
                emptyState.style.marginTop = '20px';
                emptyState.innerHTML = `
                    <div style="font-size: 3rem; margin-bottom: 16px;">📸</div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">Bạn chưa đăng check-in nào</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 400px; margin: 0 auto;">Hãy chia sẻ những hình ảnh check-in tại các địa điểm ẩm thực để lưu giữ hành trình của riêng bạn!</p>
                `;
                const container = checkinsSection ? checkinsSection.parentElement : diariesSection.parentElement;
                container.appendChild(emptyState);
            } else {
                emptyState.style.display = 'block';
            }
        } else {
            if (emptyState) {
                emptyState.style.display = 'none';
            }
        }
    }
</script>

{{-- ============================================================
     REAL-TIME: Checkin Feed Live via Laravel Reverb WebSocket
     ============================================================ --}}
<script>
(function () {
    // Lưu timestamp check-in mới nhất đã render (dùng cho polling fallback)
    let latestCheckinTs = Math.floor(Date.now() / 1000);
    // Set để tránh render trùng
    const renderedIds = new Set();
    let echoConnected = false;
    let pollingTimer  = null;

    /* ── Hàm tạo HTML card check-in ─────────────────────── */
    function buildCheckinCard(data) {
        const stars = Array.from({length: 5}, (_, i) =>
            `<span class="star-icon ${i < data.rating ? 'filled' : 'empty'}">★</span>`
        ).join('');

        const eateryBadge = data.eatery
            ? `<a href="/dia-diem/${data.eatery.slug}" class="post-tour-link">
                   📍 ${data.eatery.name}
                   ${data.eatery.category ? `<span style="font-weight:500;opacity:.75">• ${data.eatery.category}</span>` : ''}
                   ${data.eatery.commune  ? `<span style="font-weight:500;opacity:.75">• ${data.eatery.commune}</span>`  : ''}
               </a>` : '';

        const photoHtml = data.image_path
            ? `<div class="post-photo-wrapper">
                   <img src="${data.image_path}" alt="Ảnh check-in" class="post-photo-img" loading="lazy"
                        onclick="openStopImageModal('${data.image_path}')">
               </div>` : '';

        const commentHtml = data.comment
            ? `<div class="post-comment">"${data.comment}"</div>` : '';

        const roleBadge = data.role === 'admin'
            ? `<span class="author-role-badge role-admin">Admin</span>`
            : `<span class="author-role-badge role-user">Thực khách</span>`;

        return `
        <article class="checkin-post-card glass-panel" data-checkin-id="${data.id}" data-user-id="${data.user_id || ''}"
            style="margin-bottom:20px; animation: slideInNew 0.5s cubic-bezier(.22,.68,0,1.2) both;">
            <div class="post-header">
                <div class="post-author">
                    <div class="author-avatar">${data.avatar_char}</div>
                    <div class="author-meta">
                        <span class="author-name">${data.display_name}</span>
                        ${roleBadge}
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                    <div class="rating-stars-container">${stars}</div>
                    <span class="post-time">📅 ${data.created_at_human} (${data.created_at_format})</span>
                    <span style="font-size:0.7rem;font-weight:700;color:#10b981;background:rgba(16,185,129,0.12);border-radius:20px;padding:2px 8px;">🔴 Vừa đăng</span>
                </div>
            </div>
            ${eateryBadge}
            ${commentHtml}
            ${photoHtml}
            <!-- Reactions Bar -->
            <div class="reactions-bar">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:0.8rem; font-weight:800; color:var(--text-muted);">Thả cảm xúc:</span>
                    <div class="reactions-list" data-post-id="${data.id}" data-post-type="checkin">
                        <button type="button" class="react-btn" data-emoji="❤️" onclick="sendReaction(${data.id}, 'checkin', '❤️', event)">
                            <span>❤️</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="🔥" onclick="sendReaction(${data.id}, 'checkin', '🔥', event)">
                            <span>🔥</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="👍" onclick="sendReaction(${data.id}, 'checkin', '👍', event)">
                            <span>👍</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="😂" onclick="sendReaction(${data.id}, 'checkin', '😂', event)">
                            <span>😂</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="😍" onclick="sendReaction(${data.id}, 'checkin', '😍', event)">
                            <span>😍</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="🤤" onclick="sendReaction(${data.id}, 'checkin', '🤤', event)">
                            <span>🤤</span> <span class="react-count">0</span>
                        </button>
                    </div>
                </div>
                <div class="reaction-total-badge" style="display:none;">
                    <span class="total-num">0</span> lượt bày tỏ
                </div>
            </div>

            <div class="comments-section" style="margin-top:15px;padding-top:15px;border-top:1px dashed var(--border-glow); display: flex; flex-direction: column; gap: 10px;">
                <h5 class="comments-count" style="margin:0;font-size:.85rem;font-weight:800;color:var(--text-muted);">💬 Bình luận (0)</h5>
                <div class="comments-list" style="display: flex; flex-direction: column; gap: 8px;"></div>
                <form action="/binh-luan" method="POST" style="display: flex; flex-direction: column; gap: 8px; margin-top: 5px;">
                    <input type="hidden" name="_token" value="${document.querySelector('input[name="_token"]')?.value || ''}">
                    <input type="hidden" name="commentable_id" value="${data.id}">
                    <input type="hidden" name="commentable_type" value="App\\Models\\Checkin">
                    ${!document.body.classList.contains('logged-in') && !window.Laravel?.user ? `
                    <div style="width: 100%; display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="guest_name" placeholder="Tên của bạn (Khách vãng lai)..." 
                            style="flex: 1; padding: 8px 12px; border-radius: 8px; border: 1.5px solid var(--border-glow); background: rgba(0,0,0,0.15); color: var(--text-main); font-size: 0.8rem; outline: none; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-glow)'">
                    </div>` : ''}
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="content" placeholder="Viết bình luận của bạn..." required
                            style="flex: 1; padding: 8px 16px; border-radius: 30px; border: 1.5px solid var(--border-glow); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 0.88rem; outline: none; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-glow)'">
                        <button type="submit" 
                            style="padding: 8px 18px; border-radius: 30px; border: none; background: var(--primary-grad); color: #fff; font-weight: 700; font-size: 0.82rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);"
                            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(var(--primary-rgb), 0.35)'"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(var(--primary-rgb), 0.2)'">💬 Gửi</button>
                    </div>
                </form>
            </div>
        </article>`;
    }

    /* ── Hàm tạo HTML card nhật ký Food Tour ────────────────── */
    function buildDiaryCard(data) {
        const rating = data.rating 
            ? Array.from({length: 5}, (_, i) =>
                `<span class="star-icon ${i < data.rating ? 'filled' : 'empty'}">★</span>`
              ).join('')
            : '<span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Đã hoàn thành</span>';

        const tourLink = data.foodTour
            ? `<a href="/food-tour/${data.foodTour.slug}" class="post-tour-link">
                   🎯 Hành trình: ${data.foodTour.name}
               </a>`
            : '';

        const commentHtml = data.comment
            ? `<div class="post-comment">"${data.comment}"</div>`
            : '';

        const photoHtml = data.image_path
            ? `<div class="post-photo-wrapper">
                   <img src="${data.image_path}" alt="Ảnh checkin" class="post-photo-img" loading="lazy">
               </div>`
            : '';

        const roleBadge = data.role === 'admin'
            ? `<span class="author-role-badge role-admin">Admin</span>`
            : `<span class="author-role-badge role-user">Thực khách</span>`;

        let stopsHtml = '';
        if (data.stop_reviews && data.stop_reviews.length > 0) {
            const nodesHtml = data.stop_reviews.map(stop => {
                const eateryHtml = stop.eatery
                    ? `<a href="/dia-diem/${stop.eatery.slug}" class="stop-name">
                           ${stop.stop_index}. ${stop.eatery.name}
                       </a>
                       <span class="stop-category">
                           🔖 ${stop.eatery.category || 'Ẩm thực'} • 📍 ${stop.eatery.commune || 'Đông Anh'}
                       </span>`
                    : `<span class="stop-name" style="color: var(--text-muted);">
                           ${stop.stop_index}. Điểm dừng chưa đồng bộ
                       </span>`;

                const stopStars = stop.rating
                    ? `<div class="rating-stars-container" style="scale: 0.9;">
                           ${Array.from({length: 5}, (_, i) =>
                               `<span class="star-icon ${i < stop.rating ? 'filled' : 'empty'}">★</span>`
                           ).join('')}
                       </div>`
                    : '';

                const stopComment = stop.comment ? `<p class="stop-comment">💭 ${stop.comment}</p>` : '';
                const stopPhoto = stop.image_path 
                    ? `<div class="stop-media-grid">
                           <img src="${stop.image_path}" alt="Ảnh chặng ${stop.stop_index}" class="stop-media-thumb" onclick="openStopImageModal('${stop.image_path}')" loading="lazy">
                       </div>`
                    : '';

                return `
                <div class="timeline-node">
                    <div class="stop-detail-card">
                        <div class="stop-header">
                            <div class="stop-info">${eateryHtml}</div>
                            ${stopStars}
                        </div>
                        ${stopComment}
                        ${stopPhoto}
                    </div>
                </div>`;
            }).join('');

            stopsHtml = `
            <div style="margin-top: 20px; padding-top: 18px; border-top: 1px dashed var(--border-glow);">
                <h4 class="stops-summary-title">📍 Chi tiết hành trình chặng đi:</h4>
                <div class="stops-timeline">${nodesHtml}</div>
            </div>`;
        }

        return `
        <article class="checkin-post-card glass-panel" data-diary-id="${data.id}" data-user-id="${data.user_id || ''}"
            style="margin-bottom:20px; animation: slideInNew 0.5s cubic-bezier(.22,.68,0,1.2) both;">
            <div class="post-header">
                <div class="post-author">
                    <div class="author-avatar">${data.avatar_char}</div>
                    <div class="author-meta">
                        <span class="author-name">${data.display_name}</span>
                        ${roleBadge}
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                    <div class="rating-stars-container">${rating}</div>
                    <span class="post-time">📅 ${data.created_at_human} (${data.created_at_format})</span>
                    <span style="font-size:0.7rem;font-weight:700;color:#10b981;background:rgba(16,185,129,0.12);border-radius:20px;padding:2px 8px;">🔴 Vừa đăng</span>
                </div>
            </div>
            ${tourLink}
            ${commentHtml}
            ${photoHtml}
            ${stopsHtml}
            <!-- Reactions Bar -->
            <div class="reactions-bar">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:0.8rem; font-weight:800; color:var(--text-muted);">Thả cảm xúc:</span>
                    <div class="reactions-list" data-post-id="${data.id}" data-post-type="diary">
                        <button type="button" class="react-btn" data-emoji="❤️" onclick="sendReaction(${data.id}, 'diary', '❤️', event)">
                            <span>❤️</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="🔥" onclick="sendReaction(${data.id}, 'diary', '🔥', event)">
                            <span>🔥</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="👍" onclick="sendReaction(${data.id}, 'diary', '👍', event)">
                            <span>👍</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="😂" onclick="sendReaction(${data.id}, 'diary', '😂', event)">
                            <span>😂</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="😍" onclick="sendReaction(${data.id}, 'diary', '😍', event)">
                            <span>😍</span> <span class="react-count">0</span>
                        </button>
                        <button type="button" class="react-btn" data-emoji="🤤" onclick="sendReaction(${data.id}, 'diary', '🤤', event)">
                            <span>🤤</span> <span class="react-count">0</span>
                        </button>
                    </div>
                </div>
                <div class="reaction-total-badge" style="display:none;">
                    <span class="total-num">0</span> lượt bày tỏ
                </div>
            </div>

            <div class="comments-section" style="margin-top:15px;padding-top:15px;border-top:1px dashed var(--border-glow); display: flex; flex-direction: column; gap: 10px;">
                <h5 class="comments-count" style="margin:0;font-size:.85rem;font-weight:800;color:var(--text-muted);">💬 Bình luận (0)</h5>
                <div class="comments-list" style="display: flex; flex-direction: column; gap: 8px;"></div>
                <form action="/binh-luan" method="POST" style="display: flex; flex-direction: column; gap: 8px; margin-top: 5px;">
                    <input type="hidden" name="_token" value="${document.querySelector('input[name="_token"]')?.value || ''}">
                    <input type="hidden" name="commentable_id" value="${data.id}">
                    <input type="hidden" name="commentable_type" value="App\\Models\\FoodTourDiary">
                    ${!document.body.classList.contains('logged-in') && !window.Laravel?.user ? `
                    <div style="width: 100%; display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="guest_name" placeholder="Tên của bạn (Khách vãng lai)..." 
                            style="flex: 1; padding: 8px 12px; border-radius: 8px; border: 1.5px solid var(--border-glow); background: rgba(0,0,0,0.15); color: var(--text-main); font-size: 0.8rem; outline: none; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-glow)'">
                    </div>` : ''}
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="content" placeholder="Viết bình luận của bạn..." required
                            style="flex: 1; padding: 8px 16px; border-radius: 30px; border: 1.5px solid var(--border-glow); background: rgba(0,0,0,0.2); color: var(--text-main); font-size: 0.88rem; outline: none; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-glow)'">
                        <button type="submit" 
                            style="padding: 8px 18px; border-radius: 30px; border: none; background: var(--primary-grad); color: #fff; font-weight: 700; font-size: 0.82rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);"
                            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(var(--primary-rgb), 0.35)'"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(var(--primary-rgb), 0.2)'">💬 Gửi</button>
                    </div>
                </form>
            </div>
        </article>`;
    }

    /* ── Prepend card check-in vào đầu feed ────────────────── */
    function prependCard(data) {
        const key = 'checkin-' + data.id;
        if (renderedIds.has(key)) return;
        renderedIds.add(key);

        const section = document.querySelector('article.checkin-post-card[data-checkin-id]')?.parentElement;
        if (!section) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = buildCheckinCard(data);
        const card = wrapper.firstElementChild;
        section.insertBefore(card, section.firstChild);

        // Toast notification
        showLiveToast(data.display_name, data.eatery?.name);
    }

    /* ── Prepend card nhật ký vào đầu feed ─────────────────── */
    function prependDiaryCard(data) {
        const key = 'diary-' + data.id;
        if (renderedIds.has(key)) return;
        renderedIds.add(key);

        const section = document.querySelector('article.checkin-post-card[data-diary-id]')?.parentElement 
                     || document.querySelector('h2[style*="🗺️ Nhật ký Food Tour"]')?.nextElementSibling;
        if (!section) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = buildDiaryCard(data);
        const card = wrapper.firstElementChild;
        section.insertBefore(card, section.firstChild);

        // Toast notification
        showLiveToast(data.display_name, data.foodTour ? `hành trình "${data.foodTour.name}"` : 'nhật ký');
    }

    /* ── Append comment vào card tương ứng ─────────────────── */
    function appendCommentToCard(data) {
        let selector = '';
        if (data.commentable_type === 'App\\Models\\Checkin') {
            selector = `article.checkin-post-card[data-checkin-id="${data.commentable_id}"]`;
        } else if (data.commentable_type === 'App\\Models\\FoodTourDiary') {
            selector = `article.checkin-post-card[data-diary-id="${data.commentable_id}"]`;
        }

        const card = document.querySelector(selector);
        if (!card) return;

        const list = card.querySelector('.comments-list');
        if (!list) return;

        // Tránh trùng lặp bình luận đã hiển thị
        if (list.querySelector(`[data-comment-id="${data.id}"]`)) return;

        const adminBadge = data.role === 'admin' 
            ? `<span style="font-size: 0.65rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-radius: 4px; padding: 1px 4px; margin-left: 4px;">Admin</span>`
            : '';

        const commentHtml = `
            <div class="comment-item" data-comment-id="${data.id}" style="display: flex; gap: 10px; align-items: flex-start; background: rgba(255, 255, 255, 0.02); border-radius: 12px; padding: 10px 14px; border: 1px solid rgba(255,255,255,0.03); animation: slideInNew 0.3s ease both;">
                <div class="comment-avatar" style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, rgba(var(--primary-rgb),0.1), rgba(16,185,129,0.08)); border: 1px solid rgba(var(--primary-rgb),0.2); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: var(--text-main); flex-shrink: 0;">
                    ${data.avatar_char}
                </div>
                <div style="flex: 1; display: flex; flex-direction: column; gap: 3px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-main);">
                            ${data.display_name}
                            ${adminBadge}
                        </span>
                        <span style="font-size: 0.7rem; color: var(--text-muted);">${data.created_at_human}</span>
                    </div>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">${data.content}</p>
                </div>
            </div>`;

        list.insertAdjacentHTML('beforeend', commentHtml);

        // Cập nhật lại số đếm bình luận hiển thị
        const countHeader = card.querySelector('.comments-count');
        if (countHeader) {
            const currentCount = list.children.length;
            countHeader.innerHTML = `💬 Bình luận (${currentCount})`;
        }
    }

    /* ── Toast notification ───────────────────────────────── */
    function showLiveToast(name, targetName) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(100px);
            background:linear-gradient(135deg,rgba(14,165,233,0.95),rgba(16,185,129,0.9));
            color:#fff; padding:12px 22px; border-radius:40px; font-weight:700; font-size:0.88rem;
            box-shadow:0 8px 30px rgba(14,165,233,0.4); z-index:99999; transition:transform 0.4s cubic-bezier(.22,.68,0,1.2);
            display:flex; align-items:center; gap:10px; white-space:nowrap; max-width:90vw;
        `;
        toast.innerHTML = `<span style="font-size:1.1rem;">⚡ Real-time</span>
            <strong>${name}</strong> vừa chia sẻ ${targetName}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(-50%) translateY(0)', 50);
        setTimeout(() => {
            toast.style.transform = 'translateX(-50%) translateY(100px)';
            setTimeout(() => toast.remove(), 400);
        }, 4500);
    }

    /* ── Cập nhật Live badge ──────────────────────────────── */
    function setLiveBadge(connected) {
        const badge = document.getElementById('liveBadge');
        if (!badge) return;
        if (connected) {
            badge.innerHTML = `<span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;margin-right:5px;animation:livePulse 1.5s infinite;"></span>LIVE`;
            badge.style.color = '#10b981';
            badge.style.borderColor = 'rgba(16,185,129,0.4)';
        } else {
            badge.innerHTML = `<span style="display:inline-block;width:8px;height:8px;background:#f59e0b;border-radius:50%;margin-right:5px;"></span>Polling`;
            badge.style.color = '#f59e0b';
            badge.style.borderColor = 'rgba(245,158,11,0.4)';
        }
    }

    /* ── Polling fallback (gọi API mỗi 15 giây) ─────────── */
    function startPolling() {
        if (pollingTimer) return;
        pollingTimer = setInterval(async () => {
            try {
                const res = await fetch(`/api/checkins/latest?after=${latestCheckinTs}`);
                const list = await res.json();
                list.reverse().forEach(item => {
                    prependCard(item);
                    if (item.created_at_ts > latestCheckinTs) {
                        latestCheckinTs = item.created_at_ts;
                    }
                });
            } catch(e) { /* silent */ }
        }, 15000);
    }

    function stopPolling() {
        if (pollingTimer) { clearInterval(pollingTimer); pollingTimer = null; }
    }

    /* ── Kết nối Laravel Echo + Reverb ───────────────────── */
    function connectEcho() {
        if (typeof window.Echo === 'undefined') {
            // Echo chưa load — dùng polling
            setLiveBadge(false);
            startPolling();
            return;
        }

        try {
            window.Echo.channel('checkin-feed')
                .listen('.NewCheckinPosted', (data) => {
                    prependCard(data);
                    if (data.created_at_ts > latestCheckinTs) {
                        latestCheckinTs = data.created_at_ts;
                    }
                })
                .listen('.NewFoodTourDiaryPosted', (data) => {
                    prependDiaryCard(data);
                    if (data.created_at_ts > latestCheckinTs) {
                        latestCheckinTs = data.created_at_ts;
                    }
                })
                .listen('.NewCommentPosted', (data) => {
                    appendCommentToCard(data);
                })
                .listen('.CheckinReacted', (data) => {
                    if (typeof spawnFloatingEmojis === 'function') {
                        spawnFloatingEmojis(data.id, data.type, data.emoji, null);
                    }
                    if (typeof updateReactionCounts === 'function' && data.counts) {
                        updateReactionCounts(data.id, data.type, data.counts, data.total);
                    }
                });

            // Kiểm tra kết nối thực sự
            if (window.Echo.connector?.pusher) {
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    echoConnected = true;
                    setLiveBadge(true);
                    stopPolling();
                });
                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                    echoConnected = false;
                    setLiveBadge(false);
                    startPolling();
                });
                window.Echo.connector.pusher.connection.bind('failed', () => {
                    echoConnected = false;
                    setLiveBadge(false);
                    startPolling();
                });
            }
        } catch(e) {
            setLiveBadge(false);
            startPolling();
        }
    }

    // Khởi động sau khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', () => {
        // Đánh dấu các card hiện có để tránh duplicate
        document.querySelectorAll('article.checkin-post-card[data-checkin-id]').forEach(el => {
            renderedIds.add('checkin-' + el.dataset.checkinId);
        });
        document.querySelectorAll('article.checkin-post-card[data-diary-id]').forEach(el => {
            renderedIds.add('diary-' + el.dataset.diaryId);
        });
        connectEcho();
    });
})();
</script>

{{-- CSS animation cho card mới --}}
<style>
    @keyframes slideInNew {
        from { opacity: 0; transform: translateY(-20px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0)     scale(1); }
    }
    @keyframes livePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.6); }
        50%       { box-shadow: 0 0 0 5px rgba(16,185,129,0); }
    }
</style>
@endsection

@push('realtime-scripts')
{{--
    Laravel Echo + Pusher-js client (Reverb dùng Pusher protocol)
    Chỉ load trên trang /checkin để tránh ảnh hưởng các trang khác
--}}
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script>
    // Khởi tạo Laravel Echo kết nối đến Reverb WebSocket server
    // Các giá trị lấy từ .env thông qua Blade (server-side render)
    window.Echo = new (class {
        constructor() {
            this._pusher = new Pusher('{{ env('REVERB_APP_KEY') }}', {
                wsHost:           '{{ env('REVERB_HOST', 'localhost') }}',
                wsPort:           {{ env('REVERB_PORT', 8080) }},
                wssPort:          {{ env('REVERB_PORT', 8080) }},
                forceTLS:         {{ env('REVERB_SCHEME', 'http') === 'https' ? 'true' : 'false' }},
                enabledTransports: ['ws', 'wss'],
                cluster:          'mt1', // Không dùng cluster của Pusher — giá trị bất kỳ
                disableStats:     true,
            });
            this.connector = { pusher: this._pusher };
        }
        channel(name) {
            const ch = this._pusher.subscribe(name);
            return {
                listen: (event, cb) => {
                    // Reverb broadcast event name có prefix "."
                    const evtName = event.startsWith('.') ? event.slice(1) : event;
                    ch.bind(evtName, cb);
                    return this;
                }
            };
        }
    })();

    console.log('[Reverb] Echo initialized — connecting to ws://{{ env('REVERB_HOST', 'localhost') }}:{{ env('REVERB_PORT', 8080) }}');
</script>
@endpush
