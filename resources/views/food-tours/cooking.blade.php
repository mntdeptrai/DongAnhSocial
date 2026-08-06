@extends('layouts.food-tour')

@section('title', 'Góc trải nghiệm thực tế - Làng nghề & Vui chơi Đông Anh')

@section('styles')
<style>
    /* Cinematic Hero Section */
    .cooking-hero {
        position: relative;
        padding: 90px 0 60px;
        background: radial-gradient(circle at 50% 30%, rgba(16, 185, 129, 0.12) 0%, rgba(5, 150, 105, 0.04) 40%, rgba(9, 9, 11, 0) 70%), var(--bg-base);
        overflow: hidden;
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        text-align: center;
    }
    [data-theme="light"] .cooking-hero {
        background: radial-gradient(circle at 50% 30%, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.02) 40%, rgba(248, 250, 252, 0) 70%), var(--bg-base);
    }

    /* Cinematic orbs */
    .cooking-orb-1 {
        position: absolute;
        top: -150px;
        left: 5%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.16) 0%, rgba(16, 185, 129, 0) 70%);
        filter: blur(90px);
        pointer-events: none;
        animation: floatOrb1 18s ease-in-out infinite alternate;
        z-index: 0;
    }
    .cooking-orb-2 {
        position: absolute;
        bottom: -120px;
        right: 5%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(5, 150, 105, 0.12) 0%, rgba(5, 150, 105, 0) 70%);
        filter: blur(80px);
        pointer-events: none;
        animation: floatOrb2 22s ease-in-out infinite alternate;
        z-index: 0;
    }
    .cooking-orb-accent {
        position: absolute;
        top: 30%;
        right: 15%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(20, 184, 166, 0.1) 0%, rgba(20, 184, 166, 0) 70%);
        filter: blur(70px);
        pointer-events: none;
        animation: floatOrb1 14s ease-in-out infinite alternate;
        z-index: 0;
    }

    /* Slogan badge */
    .cooking-slogan {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }
    .cooking-slogan-badge {
        background: rgba(16, 185, 129, 0.12);
        border: 1.5px solid rgba(16, 185, 129, 0.3);
        color: #10b981;
        padding: 7px 18px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-family: var(--font-heading);
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
        animation: badgePulse 3s ease-in-out infinite alternate;
    }
    [data-theme="light"] .cooking-slogan-badge {
        background: rgba(16, 185, 129, 0.08);
        border-color: rgba(16, 185, 129, 0.25);
    }
    @keyframes badgePulse {
        from { box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1); }
        to { box-shadow: 0 4px 25px rgba(16, 185, 129, 0.25); border-color: rgba(16, 185, 129, 0.5); }
    }
    .cooking-slogan-sub {
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 600;
        border-left: 1px solid rgba(16, 185, 129, 0.25);
        padding-left: 12px;
        font-family: var(--font-body);
        font-style: italic;
    }

    /* Cinematic title */
    .cooking-title {
        font-family: var(--font-heading);
        font-size: 3.4rem;
        font-weight: 900;
        letter-spacing: -1px;
        line-height: 1.15;
        position: relative;
        z-index: 2;
        margin: 0 0 20px 0;
    }
    /* Dòng 1: gradient mát lạnh DongAnh Discovery */
    .cooking-title-line1 {
        display: block;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 4px 12px rgba(14,165,233,0.2)) drop-shadow(0 0 8px rgba(6,182,212,0.1));
        font-weight: 900;
    }
    /* Dòng 2: gradient xanh lá đặc trưng cooking */
    .cooking-title-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 45%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 2px 12px rgba(16, 185, 129, 0.25));
        display: block;
        margin-top: 6px;
    }
    .cooking-subtitle {
        font-size: 1.05rem;
        font-family: 'Be Vietnam Pro', 'Inter', sans-serif;
        color: var(--text-muted);
        line-height: 1.7;
        max-width: 720px;
        margin: 0 auto 36px;
        position: relative;
        z-index: 2;
        font-weight: 500;
    }

    /* CTA Button */
    .btn-cooking-cta {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #34d399 100%);
        color: #fff;
        border: none;
        padding: 16px 36px;
        border-radius: 50px;
        font-size: 1.05rem;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4), 0 0 20px rgba(16, 185, 129, 0.15);
        letter-spacing: 0.3px;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        font-family: var(--font-heading);
        position: relative;
        z-index: 10;
        animation: ctaPulseGreen 2.5s ease-in-out infinite;
    }
    .btn-cooking-cta:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.55), 0 0 30px rgba(52, 211, 153, 0.3);
        color: #fff;
    }
    @keyframes ctaPulseGreen {
        0%, 100% { box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4), 0 0 0 0 rgba(16, 185, 129, 0.3); }
        50% { box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5), 0 0 0 10px rgba(16, 185, 129, 0); }
    }

    /* Stats row */
    .cooking-stats {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 32px;
        margin-top: 40px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }
    .cooking-stat-item {
        text-align: center;
    }
    .cooking-stat-value {
        font-family: var(--font-heading);
        font-size: 1.8rem;
        font-weight: 900;
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1;
        display: block;
    }
    .cooking-stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .cooking-stat-divider {
        width: 1px;
        height: 36px;
        background: rgba(16, 185, 129, 0.2);
    }

    /* Tour card green hover */
    .cooking-tour-card {
        border-radius: 20px;
        overflow: hidden;
        border: 1.5px solid rgba(16, 185, 129, 0.2);
        background: var(--bg-card);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: transform, box-shadow;
    }
    .cooking-tour-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(16, 185, 129, 0.2), 0 0 0 1px rgba(16, 185, 129, 0.3);
        border-color: rgba(16, 185, 129, 0.4);
    }

    /* Shooting stars - green tint */
    .cooking-shooting-star {
        position: absolute;
        width: 100px;
        height: 2px;
        background: linear-gradient(90deg, rgba(255,255,255,0.9) 0%, rgba(16, 185, 129, 0.5) 30%, rgba(16, 185, 129, 0) 100%);
        filter: drop-shadow(0 0 5px rgba(16, 185, 129, 0.7));
        opacity: 0;
        transform: rotate(-40deg) translate3d(200px, -200px, 0);
        animation: shootStar 7s linear infinite;
        will-change: transform, opacity;
    }
    .cooking-shooting-star:nth-child(1) { top: -20px; right: 15%; animation-delay: 0.5s; animation-duration: 6s; }
    .cooking-shooting-star:nth-child(2) { top: 0px; right: 40%; animation-delay: 2.2s; animation-duration: 7.5s; }
    .cooking-shooting-star:nth-child(3) { top: -40px; right: 60%; animation-delay: 3.8s; animation-duration: 6.8s; }
    .cooking-shooting-star:nth-child(4) { top: 20px; right: 25%; animation-delay: 1.2s; animation-duration: 8.2s; }
    .cooking-shooting-star:nth-child(5) { top: -10px; right: 50%; animation-delay: 4.8s; animation-duration: 7s; }

    /* Green particles */
    .cooking-particle {
        position: absolute;
        background: radial-gradient(circle, rgba(255,255,255,0.9) 0%, rgba(16,185,129,0.5) 50%, rgba(16,185,129,0) 100%);
        border-radius: 50%;
        pointer-events: none;
        opacity: 0;
        animation: floatParticle 10s infinite ease-in-out;
    }
    .cp-1 { left: 10%; top: 25%; width: 5px; height: 5px; animation-duration: 10s; animation-delay: 0s; }
    .cp-2 { left: 82%; top: 18%; width: 7px; height: 7px; animation-duration: 13s; animation-delay: 1.8s; }
    .cp-3 { left: 45%; top: 75%; width: 4px; height: 4px; animation-duration: 9s; animation-delay: 3.5s; }
    .cp-4 { left: 28%; top: 60%; width: 6px; height: 6px; animation-duration: 12s; animation-delay: 0.5s; }
    .cp-5 { left: 68%; top: 40%; width: 5px; height: 5px; animation-duration: 8s; animation-delay: 2.5s; }
    .cp-6 { left: 90%; top: 55%; width: 6px; height: 6px; animation-duration: 14s; animation-delay: 4.5s; }

    @keyframes rotateEmoji {
        0%, 100% { transform: rotate(0deg); }
        50% { transform: rotate(15deg); }
    }
    .spotlight-image-wrapper:hover img, .spotlight-image-wrapper:hover video {
        transform: scale(1.06);
        transition: transform 0.6s ease;
    }
    .spotlight-image-wrapper img, .spotlight-image-wrapper video {
        transition: transform 0.6s ease;
    }
    [data-theme="light"] .cooking-stat-divider {
        background: rgba(16, 185, 129, 0.15);
    }
</style>
@endsection

@section('content')
<div style="background: var(--bg-base); min-height: calc(100vh - 70px); position: relative; overflow-x: hidden;">

    {{-- ════════════════════════════════════════════════════════
         CINEMATIC HERO SECTION
    ════════════════════════════════════════════════════════ --}}
    <section class="cooking-hero">
        {{-- Glow Orbs --}}
        <div class="cooking-orb-1"></div>
        <div class="cooking-orb-2"></div>
        <div class="cooking-orb-accent"></div>

        {{-- Particles --}}
        <div style="position: absolute; inset: 0; pointer-events: none; z-index: 1; overflow: hidden;">
            <div class="cooking-particle cp-1"></div>
            <div class="cooking-particle cp-2"></div>
            <div class="cooking-particle cp-3"></div>
            <div class="cooking-particle cp-4"></div>
            <div class="cooking-particle cp-5"></div>
            <div class="cooking-particle cp-6"></div>
            {{-- Green shooting stars --}}
            <div class="cooking-shooting-star"></div>
            <div class="cooking-shooting-star"></div>
            <div class="cooking-shooting-star"></div>
            <div class="cooking-shooting-star"></div>
            <div class="cooking-shooting-star"></div>
        </div>

        <div class="container" style="max-width: 960px; position: relative; z-index: 2; text-align: center;">
            {{-- Slogan Badge --}}
            <div class="cooking-slogan">
                <span class="cooking-slogan-badge">🌾 Dong Anh Discovery</span>
                <span class="cooking-slogan-sub">Hành trình văn hóa & Làng nghề</span>
            </div>

            {{-- Animated Title --}}
            <h1 class="cooking-title">
                <span class="text-reveal" style="display: block;">
                    <span class="cooking-title-line1">Góc trải nghiệm thực tế</span>
                </span>
                <span class="text-reveal cooking-title-green" style="animation-delay: 200ms;">
                    Làng Nghề & Vui Chơi Bản Địa Đông Anh
                </span>
            </h1>

            <p class="cooking-subtitle">
                Không chỉ là ăn uống, đây là hành trình nhập vai thực tế! Bạn đồng hành cùng người bản xứ, tự tay học các nghề truyền thống (làm bún, đan lát, gốm sứ), tham gia các trò chơi dân gian và vui chơi giải trí sống động tại vùng đất cổ Đông Anh.
            </p>

            {{-- Stats Row --}}
            <div class="cooking-stats">
                <div class="cooking-stat-item">
                    <span class="cooking-stat-value">12+</span>
                    <span class="cooking-stat-label">Làng nghề truyền thống</span>
                </div>
                <div class="cooking-stat-divider"></div>
                <div class="cooking-stat-item">
                    <span class="cooking-stat-value">500+</span>
                    <span class="cooking-stat-label">Khách trải nghiệm</span>
                </div>
                <div class="cooking-stat-divider"></div>
                <div class="cooking-stat-item">
                    <span class="cooking-stat-value">4.9⭐</span>
                    <span class="cooking-stat-label">Đánh giá trung bình</span>
                </div>
                <div class="cooking-stat-divider"></div>
                <div class="cooking-stat-item">
                    <span class="cooking-stat-value">100%</span>
                    <span class="cooking-stat-label">Trải nghiệm thực tế</span>
                </div>
            </div>
        </div>
    </section>

    <div class="container reveal reveal-fade-in delay-100" style="position: relative; z-index: 2; padding: 50px 0;">
        <div style="display: grid; grid-template-columns: 1fr; gap: 40px;">
            
            <!-- UNIFIED TOURS & ACTIVITIES GRID SECTION -->
            <div>
                <h3 style="font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                    <span>🗺️</span> Các Lộ trình & Hoạt động Trải nghiệm thực tế
                </h3>

                <div class="tours-grid" id="main-tours-grid">
                    {{-- Loop through Tours --}}
                    @foreach($tours as $tour)
                        <div class="tour-card hover-lift reveal reveal-scale-in" data-mood="{{ $tour->mood }}" style="border: 1.5px solid rgba(16, 185, 129, 0.2);">
                            <div class="tour-popularity-badge" style="background: rgba(16, 185, 129, 0.9); color: white;">
                                <span>🗺️ Lộ trình</span> • {{ $tour->popularity }}
                            </div>
                            <div class="tour-thumbnail-wrapper">
                                <img src="{{ $tour->thumbnail ?: 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80' }}" class="tour-thumbnail" alt="{{ $tour->name }}">
                                <div class="tour-card-overlay">
                                    <span class="tour-difficulty-tag" style="background: #10b981;">{{ $tour->difficulty }}</span>
                                </div>
                            </div>
                            <div class="tour-card-body">
                                <h3 class="tour-card-title">{{ $tour->name }}</h3>
                                <p class="tour-card-desc">{{ $tour->description }}</p>
                                
                                <div class="tour-meta-grid">
                                    <div class="tour-meta-item">
                                        <span class="tour-meta-label">⏱️ Thời gian</span>
                                        <span class="tour-meta-value">{{ $tour->duration }}</span>
                                    </div>
                                    <div class="tour-meta-item">
                                        <span class="tour-meta-label">📏 Khoảng cách</span>
                                        <span class="tour-meta-value">{{ $tour->distance }}</span>
                                    </div>
                                    <div class="tour-meta-item">
                                        <span class="tour-meta-label">🕒 Thời điểm</span>
                                        <span class="tour-meta-value">{{ $tour->best_time }}</span>
                                    </div>
                                </div>

                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; justify-content: center; background: rgba(255,255,255,0.02); padding: 8px; border-radius: 10px; border: 1px solid rgba(16, 185, 129, 0.15);">
                                    <span>💰</span> Dự chi: <strong style="color: #10b981;">{{ $tour->budget }}</strong>
                                </div>
                                
                                @if($tour->diaries_count > 0)
                                <div style="display: flex; gap: 8px;">
                                    <a href="/food-tour/{{ $tour->slug }}" class="btn-primary" style="flex: 1.5; text-align: center; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 700; display: block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25); display: flex; align-items: center; justify-content: center; color: white;">
                                        🚀 Trải nghiệm
                                    </a>
                                    <button type="button" onclick="openDiariesModal('{{ $tour->id }}')" class="btn-secondary" style="flex: 1; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.3); display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.85rem; cursor: pointer;">
                                        📖 Nhật ký ({{ $tour->diaries_count }})
                                    </button>
                                </div>
                                @else
                                <a href="/food-tour/{{ $tour->slug }}" class="btn-primary" style="text-align: center; text-decoration: none; width: 100%; padding: 12px; border-radius: 12px; font-weight: 700; display: block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25); color: white;">
                                    🚀 Bắt đầu Hành trình Trải nghiệm
                                </a>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Loop through Cultural Activities --}}
                    @foreach($activities as $activity)
                        <div class="tour-card hover-lift reveal reveal-scale-in" style="border: 1.5px solid rgba(16, 185, 129, 0.2);">
                            <div class="tour-popularity-badge" style="background: rgba(3, 105, 161, 0.9); color: white;">
                                @if($activity->type === 'experience')
                                    <span>🏛️ Trải nghiệm</span>
                                @elseif($activity->type === 'ticket')
                                    <span>🎫 Vé tham quan</span>
                                @elseif($activity->type === 'service')
                                    <span>🙏 Dịch vụ di tích</span>
                                @else
                                    <span>🏛️ Khác</span>
                                @endif
                            </div>
                            <div class="tour-thumbnail-wrapper">
                                <img src="{{ $activity->image_path ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80' }}" class="tour-thumbnail" alt="{{ $activity->name }}">
                                <div class="tour-card-overlay">
                                    <span class="tour-difficulty-tag" style="background: #0284c7; box-shadow: 0 2px 8px rgba(3, 105, 161, 0.3);">📍 {{ $activity->eatery->name }}</span>
                                </div>
                            </div>
                            <div class="tour-card-body">
                                <h3 class="tour-card-title">{{ $activity->name }}</h3>
                                <p class="tour-card-desc">{{ $activity->description }}</p>
                                
                                <div class="tour-meta-grid">
                                    <div class="tour-meta-item">
                                        <span class="tour-meta-label">💰 Giá vé/Phí</span>
                                        <span class="tour-meta-value" style="font-size: 0.8rem; color: #10b981;">
                                            {{ $activity->price > 0 ? number_format($activity->price, 0, ',', '.') . 'đ' : 'Theo yêu cầu' }}
                                        </span>
                                    </div>
                                    <div class="tour-meta-item">
                                        <span class="tour-meta-label">📦 Đơn vị</span>
                                        <span class="tour-meta-value">{{ $activity->unit ?: 'lượt' }}</span>
                                    </div>
                                    <div class="tour-meta-item">
                                        <span class="tour-meta-label">🏷️ Ưu đãi</span>
                                        <span class="tour-meta-value" style="font-size: 0.7rem; line-height: 1.2;">
                                            {{ $activity->discount_note ?: 'Không có' }}
                                        </span>
                                    </div>
                                </div>

                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; justify-content: center; background: rgba(255,255,255,0.02); padding: 8px; border-radius: 10px; border: 1px solid rgba(16, 185, 129, 0.15);">
                                    <span>🏛️ Địa điểm:</span> <strong style="color: var(--text-main); font-size: 0.85rem;">{{ $activity->eatery->name }}</strong>
                                </div>
                                
                                <a href="/dia-diem/{{ $activity->eatery->slug }}" class="btn-primary" style="text-align: center; text-decoration: none; width: 100%; padding: 12px; border-radius: 12px; font-weight: 700; display: block; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none; box-shadow: 0 4px 15px rgba(3, 105, 161, 0.25); color: white;">
                                    🔍 Xem Điểm Đến chi tiết
                                </a>
                            </div>
                        </div>
                    @endforeach

                    @if($tours->isEmpty() && $activities->isEmpty())
                        <div class="glass-card" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; border-radius: 20px;">
                            <span style="font-size: 3rem;">🔍</span>
                            <h3 style="margin-top: 16px; font-weight: 700; color: var(--text-main);">Đang phát triển lộ trình mới</h3>
                            <p style="color: var(--text-muted); margin-top: 8px;">Hệ thống đang thiết kế thêm các tour làm Bánh Chưng bánh giầy, làm Tương hữu cơ Đông Anh. Vui lòng quay lại sau!</p>
                        </div>
                    @endif
                </div>
            </div>



    </div>

    <!-- 📖 Diaries Modals for all tours -->
    @foreach($tours as $tourData)
        @if($tourData->diaries_count > 0)
        <div id="communityDiariesModal-{{ $tourData->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 10005; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
            <div style="width: 90%; max-width: 650px; max-height: 85vh; display: flex; flex-direction: column; padding: 30px; border-radius: 24px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); color: #0f172a; position: relative;">
                
                <button onclick="closeDiariesModal('{{ $tourData->id }}')" style="position: absolute; top: 20px; right: 20px; background: #f1f5f9; border: 1px solid #e2e8f0; width: 36px; height: 36px; border-radius: 50%; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; color: #475569; cursor: pointer; transition: all 0.2s; z-index: 10;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#475569';">✕</button>
                
                <h3 style="font-weight: 800; color: #0f172a; font-size: 1.4rem; margin: 0 0 20px 0; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px;">
                    📖 Nhật ký Cộng đồng
                    <span style="font-size: 0.8rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; padding: 4px 12px; border-radius: 20px; font-weight: 800;">{{ $tourData->diaries_count }} đánh giá</span>
                </h3>
                
                <div style="flex: 1; overflow-y: auto; padding-right: 12px; display: flex; flex-direction: column; gap: 24px;">
                    @foreach($tourData->diaries as $diary)
                        <div style="padding: 22px; border-radius: 20px; background: #ffffff; border: 1.5px solid #cbd5e1; border-top: 4px solid #10b981; box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06); position: relative;">
                            
                            <!-- Header Banner for each separate diary -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 0.72rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 4px 10px; border-radius: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                                        📌 Nhật ký #{{ $loop->iteration }}
                                    </span>
                                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                                        📅 {{ $diary->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                
                                @if($diary->rating)
                                <div style="color: #d97706; font-size: 0.75rem; font-weight: 800; display: flex; align-items: center; gap: 4px; background: #fffbeb; padding: 5px 12px; border-radius: 20px; border: 1px solid #fef3c7;">
                                    <span>⭐</span><strong>{{ $diary->rating }} / 5 Sao</strong>
                                </div>
                                @else
                                <div style="color: #059669; font-size: 0.75rem; font-weight: 800; display: flex; align-items: center; gap: 4px; background: #ecfdf5; padding: 5px 12px; border-radius: 20px; border: 1px solid #a7f3d0;">
                                    <span>✅</span><strong>Đã hoàn thành</strong>
                                </div>
                                @endif
                            </div>

                            <!-- User info block -->
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px; background: #f8fafc; padding: 10px 14px; border-radius: 14px; border: 1px solid #f1f5f9;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.88rem; font-weight: 800; text-transform: uppercase; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3); flex-shrink: 0;">
                                    {{ substr($diary->user ? $diary->user->name : 'TK', 0, 2) }}
                                </div>
                                <div>
                                    <strong style="font-size: 0.92rem; color: #0f172a; display: block;">
                                        {{ $diary->user ? $diary->user->name : 'Thực khách Food Tour' }}
                                    </strong>
                                    <span style="font-size: 0.72rem; color: #64748b;">Tác giả nhật ký chuyến đi</span>
                                </div>
                            </div>
                            
                            <!-- Overall Tour Comment -->
                            @if($diary->comment)
                            <div style="margin: 0 0 16px 0; font-size: 0.88rem; color: #1e293b; line-height: 1.55; background: #f8fafc; padding: 12px 16px; border-radius: 12px; border-left: 4px solid #10b981; border-top: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
                                <span style="font-size: 1.1rem; color: #10b981; margin-right: 4px;">💬</span> <em>"{{ $diary->comment }}"</em>
                            </div>
                            @endif
                            
                            <!-- Cover/Selfie Photo -->
                            @if($diary->image_path)
                                <div style="position: relative; height: 220px; border-radius: 14px; overflow: hidden; border: 1px solid #cbd5e1; margin-bottom: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                                    <img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" src="{{ $diary->image_path }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    <span style="position: absolute; bottom: 12px; right: 12px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); font-size: 0.7rem; color: #ffffff; padding: 6px 12px; border-radius: 20px; font-weight: 800; text-transform: uppercase;">📸 Kỷ niệm Selfie</span>
                                </div>
                            @endif

                            <!-- Stop-by-stop check-in list preview -->
                            @if(!empty($diary->stop_reviews))
                                <div style="margin-top: 16px; background: #f8fafc; border-radius: 14px; padding: 14px; border: 1px solid #e2e8f0;">
                                    <span style="font-size: 0.75rem; color: #334155; display: flex; align-items: center; gap: 6px; margin-bottom: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                        📍 Đánh giá từng chặng dừng của {{ $diary->user ? $diary->user->name : 'thực khách' }}:
                                    </span>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        @foreach($diary->stop_reviews as $stopIdx => $stopRev)
                                            @php
                                                $stopEatery = $tourData->stops[$stopIdx]->eatery ?? null;
                                            @endphp
                                            @if($stopEatery)
                                                <div style="display: flex; gap: 12px; background: #ffffff; border-radius: 12px; padding: 12px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.02); align-items: flex-start;">
                                                    <div style="flex: 1;">
                                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                                            <span style="font-size: 0.84rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                                                <span style="font-size: 1rem;">{{ $stopEatery->category->icon ?: '🍜' }}</span>
                                                                {{ $stopEatery->name }}
                                                            </span>
                                                            <span style="color: #d97706; font-size: 0.7rem; font-weight: 700; background: #fffbeb; padding: 3px 8px; border-radius: 6px; border: 1px solid #fef3c7; display: flex; align-items: center; gap: 2px;">
                                                                @if(!empty($stopRev['rating']))
                                                                    ⭐ {{ $stopRev['rating'] }}
                                                                @else
                                                                    ✅ Đã đến
                                                                @endif
                                                            </span>
                                                        </div>
                                                        @if(!empty($stopRev['comment']))
                                                        <p style="margin: 0; font-size: 0.8rem; color: #475569; font-style: italic; line-height: 1.5;">
                                                            "{{ $stopRev['comment'] }}"
                                                        </p>
                                                        @endif
                                                    </div>
                                                    
                                                    @if(!empty($stopRev['image_path']))
                                                        <div style="width: 70px; height: 70px; border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1; flex-shrink: 0;">
                                                            <img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" src="{{ $stopRev['image_path'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>

<script>
    function openDiariesModal(tourId) {
        const modal = document.getElementById('communityDiariesModal-' + tourId);
        if (modal) modal.style.display = 'flex';
    }

    function closeDiariesModal(tourId) {
        const modal = document.getElementById('communityDiariesModal-' + tourId);
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('saved') === '1') {
            if (typeof window.showToast === 'function') {
                window.showToast('🎉 Nhật ký chuyến đi Food Tour của bạn đã được lưu trữ thành công vào cơ sở dữ liệu!', 'success', 'Lưu nhật ký thành công');
            }
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });
</script>


@endsection

@section('footer')
<footer style="background: #09090b; border-top: 1px solid rgba(16, 185, 129, 0.15); padding: 50px 0 30px 0; color: var(--text-muted); font-size: 0.88rem; font-family: var(--font-body); margin-top: 60px;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div class="footer-grid" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; margin-bottom: 30px;">
            <div>
                <h3 class="logo" style="margin-bottom: 16px; font-size: 1.3rem; display: flex; align-items: center; gap: 8px; color: var(--text-main); font-family: var(--font-heading);">
                    <span>📖</span> <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">DongAnh Discovery</span>
                </h3>
                <p style="line-height: 1.6; max-width: 480px;">
                    Bản đồ số Đông Anh (DongAnh Discovery) là giải pháp công nghệ số hóa toàn bộ trường học, bệnh viện, cơ sở y tế, khách sạn, nhà nghỉ, nhà hàng, quán cafe và quảng bá các sản phẩm OCOP đặc sản truyền thống của xã Đông Anh, Hà Nội. Hỗ trợ chuyển đổi số và nâng tầm văn hóa du lịch địa phương.
                </p>
            </div>
            <div>
                <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem; font-weight: 700; font-family: var(--font-heading);">Liên kết nhanh</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; padding: 0; margin: 0;">
                    <li><a href="/" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='var(--text-muted)'">Trang chủ</a></li>
                    <li><a href="/tim-kiem" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='var(--text-muted)'">Bản đồ số</a></li>
                    <li><a href="/food-tours" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='var(--text-muted)'">Food Tour</a></li>
                    <li><a href="/exp-corner" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='var(--text-muted)'">Góc trải nghiệm thực tế</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem; font-weight: 700; font-family: var(--font-heading);">Thông tin hành chính</h4>
                <p style="line-height: 1.6;">
                    <strong>Cơ quan chủ quản:</strong> Uỷ ban nhân dân xã Đông Anh<br>
                    <strong>Chịu trách nhiệm chính:</strong> Phòng Văn hóa - Xã hội xã Đông Anh<br>
                    📍 Địa chỉ: Số 66 đường Cao Lỗ, xã Đông Anh, thành phố Hà Nội<br>
                    📞 Điện thoại: 0243.965.2973<br>
                    🌐 Website: <a href="https://donganh.hanoi.gov.vn" target="_blank" style="color: inherit; text-decoration: none;">donganh.hanoi.gov.vn</a>
                </p>
            </div>
        </div>
        <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.3);">
            &copy; 2026 DongAnh Discovery (Bản đồ số Đông Anh). Tất cả quyền được bảo lưu. Phát triển bởi Phòng Văn hóa - Xã hội xã Đông Anh
        </div>
    </div>
</footer>
@endsection
