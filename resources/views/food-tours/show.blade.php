@extends('layouts.food-tour')

@section('styles')
<style>
    /* Styling Leaflet custom premium popup to match design perfectly */
    .premium-leaflet-popup .leaflet-popup-content-wrapper {
        background: rgba(255, 255, 255, 0.98) !important;
        border-radius: 20px !important;
        border: 1.5px solid rgba(255, 255, 255, 0.9) !important;
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.18) !important;
        padding: 0 !important;
        overflow: hidden !important;
        color: #0f172a !important;
        animation: springPopIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
    }
    
    @if($tour->mood === 'cooking')
    .tour-layout,
    .premium-leaflet-popup,
    .leaflet-container,
    .modal,
    .modal-content {
        --primary: #10b981 !important;
        --primary-grad: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        --border-glow: rgba(16, 185, 129, 0.25) !important;
        --accent: #34d399 !important;
    }
    .checkin-action-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
    }
    .timeline-trail-glow {
        background: linear-gradient(to bottom, transparent, #10b981, #059669) !important;
        box-shadow: 0 0 12px rgba(16, 185, 129, 0.8) !important;
    }
    @endif
    
    .premium-leaflet-popup .leaflet-popup-content {
        margin: 10px !important;
        width: 220px !important;
        font-family: inherit !important;
    }
    
    .premium-leaflet-popup .leaflet-popup-tip-container {
        display: none !important; /* Float popup directly above to not block map path/marker */
    }
    
    .premium-leaflet-popup .leaflet-popup-close-button {
        color: #64748b !important;
        font-size: 14px !important;
        top: 8px !important;
        right: 8px !important;
        font-weight: 800 !important;
        z-index: 100 !important;
        background: rgba(255, 255, 255, 0.85) !important;
        border-radius: 50% !important;
        width: 22px !important;
        height: 22px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12) !important;
        border: none !important;
        transition: all 0.2s !important;
    }
    
    .premium-leaflet-popup .leaflet-popup-close-button:hover {
        color: #0ea5e9 !important;
        transform: scale(1.08) !important;
    }
    
    .timeline-card {
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        will-change: transform, box-shadow;
    }
    .timeline-card:hover {
        transform: translateY(-4px) scale(1.01) !important;
        border-color: #0ea5e9 !important;
        box-shadow: 0 12px 28px rgba(14, 165, 233, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
    }

    /* Spring Pop-In Animations & Glassmorphism Modals */
    @keyframes springPopIn {
        0% { transform: scale(0.65) translateY(20px); opacity: 0; }
        70% { transform: scale(1.04) translateY(-4px); opacity: 1; }
        100% { transform: scale(1) translateY(0); opacity: 1; }
    }
    
    @keyframes pulseRing {
        0% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.5); }
        70% { box-shadow: 0 0 0 14px rgba(14, 165, 233, 0); }
        100% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0); }
    }

    /* Animated Map Route Line Glow */
    .route-glow {
        stroke-dasharray: 12, 12;
        animation: routeLineDashFlow 1.4s linear infinite;
        filter: drop-shadow(0 0 8px rgba(14, 165, 233, 0.6));
    }
    
    @keyframes routeLineDashFlow {
        from { stroke-dashoffset: 24; }
        to { stroke-dashoffset: 0; }
    }

    @keyframes confettiFall {
        0% { transform: translateY(0) rotate(0deg); opacity: 1; }
        100% { transform: translateY(105vh) rotate(720deg); opacity: 0; }
    }
    
    @if($tour->mood === 'cooking')
    .timeline-card:hover {
        border-color: #10b981 !important;
        box-shadow: 0 12px 28px rgba(16, 185, 129, 0.18) !important;
    }
    @endif
</style>
@endsection

@section('title', $tour->name . ' - Hành trình Ẩm thực Đông Anh')

@section('content')
<!-- CSS Canvas for Confetti -->
<div id="confettiContainer" class="confetti-container"></div>

<div class="tour-layout" id="tourLayout">
    
    <!-- LEFT PANEL: Timeline, Storytelling, and Progress Controls -->
    <div class="tour-sidebar-panel" id="sidebarPanel">
        
        <!-- Focus Journey Header (Appears in Start Journey Mode) -->
        <div class="journey-focus-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <button onclick="exitJourneyMode()" style="background: rgba(255,255,255,0.08); border: 1px solid var(--border-glow); padding: 6px 12px; border-radius: 8px; color: var(--text-main); font-size: 0.8rem; cursor: pointer; font-weight: 700; transition: all 0.3s ease;">
                    ⬅️ Thoát Focus
                </button>
                <div>
                    <h4 style="font-size: 0.88rem; font-weight: 800; color: var(--text-main); margin: 0;">Đang đi Tour</h4>
                    <span style="font-size: 0.72rem; color: var(--primary); font-weight: 700;" id="journeyProgressText">Tiến trình: 0%</span>
                </div>
            </div>
            
            <div style="text-align: right;">
                <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">
                    🚀 Hướng dẫn trực quan
                </span>
            </div>

            <!-- Absolute progress indicator -->
            <div class="journey-progress-bar-wrapper">
                <div class="journey-progress-bar-fill" id="progressBarFill"></div>
            </div>
        </div>

        <!-- Static Hero Banner -->
        <div class="tour-sidebar-hero">
            <img src="{{ $tour->thumbnail ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $tour->name }}">
            <div class="tour-sidebar-hero-overlay">
                <span style="background: var(--primary-grad, var(--primary)); color: #ffffff; padding: 4px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; width: fit-content; margin-bottom: 8px;">
                    {{ $tour->difficulty }}
                </span>
                <h1 class="tour-sidebar-title">{{ $tour->name }}</h1>
                <p style="font-size: 0.78rem; color: rgba(255,255,255,0.7); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin: 0;">
                    {{ $tour->description }}
                </p>
            </div>
        </div>

        <!-- Dashboard Content Area -->
        <div class="tour-sidebar-content">

            @if(session('success'))
                <div class="glass-card" style="background: rgba(16, 185, 129, 0.1); border: 1.5px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 12px; border-radius: 12px; margin-bottom: 15px; font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            <!-- Owner & Sharing Admin Panel -->
            @if($tour->user_id !== null)
                <div class="glass-card" style="padding: 16px; border-radius: 16px; border-color: rgba(255,126,41,0.2); margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 0.78rem; color: var(--text-muted);">
                            👤 Tạo bởi: <strong style="color: var(--primary);">{{ $tour->user ? $tour->user->name : 'Thành viên cộng đồng' }}</strong>
                        </span>
                        
                        @if($tour->shared_at)
                            <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 2px 8px; border-radius: 20px; font-size: 0.65rem; font-weight: 800;">🌐 CÔNG KHAI</span>
                        @else
                            <span style="background: rgba(107, 114, 128, 0.15); color: #9ca3af; padding: 2px 8px; border-radius: 20px; font-size: 0.65rem; font-weight: 800;">🔒 RIÊNG TƯ</span>
                        @endif
                    </div>

                    @php
                        $canManageTour = auth()->check() && (
                            ($tour->user_id !== null && $tour->user_id === auth()->id()) ||
                            ($tour->user_id === null && (optional(auth()->user())->role === 'admin' || session('user_role') === 'admin'))
                        );
                    @endphp

                    @if($canManageTour)
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                            <a href="/food-tour/{{ $tour->slug }}/edit" class="btn-primary" style="flex: 1; text-align: center; text-decoration: none; padding: 8px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                ✏️ Chỉnh sửa
                            </a>
                            
                            <form action="/food-tour/{{ $tour->slug }}/share" method="POST" style="flex: 1.2; display: flex; margin: 0;">
                                @csrf
                                <button type="submit" class="btn-secondary" style="width: 100%; padding: 8px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--border-glow); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                    @if($tour->shared_at)
                                        🔒 Huỷ chia sẻ
                                    @else
                                        🌐 Chia sẻ
                                    @endif
                                </button>
                            </form>

                            <form action="/food-tour/{{ $tour->slug }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xoá lộ trình này không?')" style="display: inline-flex; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary" style="padding: 8px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); cursor: pointer;" onmouseover="this.style.background='rgba(239,68,68,0.2)';" onmouseout="this.style.background='rgba(239,68,68,0.1)';">
                                    🗑️ Xoá
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
            
            <!-- Quick Trip Info Box Grid -->
            <div class="glass-card" style="padding: 16px; border-radius: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; border-color: rgba(255,126,41,0.15);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">⏱️</span>
                    <div>
                        <span style="display:block; font-size:0.68rem; color:var(--text-muted); text-transform:uppercase;">Thời gian</span>
                        <strong style="font-size:0.85rem; color:var(--text-main);">{{ $tour->duration }}</strong>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">🚶</span>
                    <div>
                        <span style="display:block; font-size:0.68rem; color:var(--text-muted); text-transform:uppercase;">Khoảng cách</span>
                        <strong style="font-size:0.85rem; color:var(--text-main);">{{ $tour->distance }}</strong>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">💰</span>
                    <div>
                        <span style="display:block; font-size:0.68rem; color:var(--text-muted); text-transform:uppercase;">Ngân sách</span>
                        <strong style="font-size:0.85rem; color:var(--text-main);">{{ $tour->budget }}</strong>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">🕒</span>
                    <div>
                        <span style="display:block; font-size:0.68rem; color:var(--text-muted); text-transform:uppercase;">Best Time</span>
                        <strong style="font-size:0.85rem; color:var(--text-main);">{{ $tour->best_time }}</strong>
                    </div>
                </div>
            </div>

            <!-- Storytelling Lore Paragraph -->
            <div class="glass-card" style="padding: 16px; border-radius: 16px; font-size: 0.82rem; line-height: 1.6; color: var(--text-muted);">
                <h4 style="font-weight: 800; color: var(--primary); margin-bottom: 8px; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                    <span>📜</span> Câu chuyện hành trình
                </h4>
                <p style="margin: 0;">{{ $tour->story }}</p>
            </div>

            <!-- TIMELINE STOPS -->
            <div>
                <h4 style="font-weight: 800; color: var(--text-main); font-size: 0.95rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <span>🗺️</span> @if($tour->mood === 'cooking') Lộ trình Trải nghiệm thực tế @else Các Chặng Dừng Chân @endif
                </h4>
                
                <div class="timeline-wrapper">
                    <!-- Dynamic timeline lines -->
                    <div class="timeline-trail"></div>
                    <div class="timeline-trail-glow" id="trailGlow"></div>

                    <!-- RPG-style Starter Location timeline item -->
                    <div class="timeline-item active" id="start-timeline-item" style="transition: all 0.4s ease;">
                        <div class="timeline-badge" style="background: @if($tour->mood === 'cooking') linear-gradient(135deg, #10b981 0%, #059669 100%) @else linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) @endif; color: #ffffff; box-shadow: @if($tour->mood === 'cooking') 0 0 10px rgba(16, 185, 129, 0.4) @else 0 0 10px rgba(59, 130, 246, 0.4) @endif; font-size: 0.75rem;">📍</div>
                        <div class="timeline-card" onclick="focusStartLocation()" style="background: @if($tour->mood === 'cooking') rgba(16, 185, 129, 0.05) @else rgba(59, 130, 246, 0.05) @endif; border: 1.5px solid @if($tour->mood === 'cooking') rgba(16, 185, 129, 0.25) @else rgba(59, 130, 246, 0.25) @endif; box-shadow: 0 0 15px rgba(59, 130, 246, 0.05); cursor: pointer;">
                            <div class="timeline-card-header">
                                <h5 class="timeline-card-title" style="color: @if($tour->mood === 'cooking') #10b981 @else #3b82f6 @endif; font-weight: 800; font-size: 0.78rem;">🚩 Điểm Xuất Phát Của Bạn</h5>
                                <span class="timeline-card-meta" style="background: @if($tour->mood === 'cooking') rgba(16, 185, 129, 0.15) @else rgba(59, 130, 246, 0.15) @endif; color: @if($tour->mood === 'cooking') #10b981 @else #3b82f6 @endif; padding: 2px 8px; border-radius: 10px; font-size: 0.65rem; font-weight: 800;">SẴN SÀNG</span>
                            </div>
                            <p style="font-size: 0.72rem; color: var(--text-muted); margin: 6px 0 0 0; line-height: 1.4;">
                                Hệ thống định vị GPS đã sẵn sàng. Hãy bấm nút <strong style="color: var(--primary);">@if($tour->mood === 'cooking') Bắt đầu Trải nghiệm @else Bắt đầu Food Tour @endif</strong> bên dưới để chính thức mở khóa chặng khám phá đầu tiên nhé!
                            </p>
                        </div>
                    </div>

                    @foreach($tour->stops as $index => $stop)
                        <div class="timeline-item" id="stop-item-{{ $index }}" data-index="{{ $index }}" data-lat="{{ number_format($stop->eatery->latitude, 6, '.', '') }}" data-lng="{{ number_format($stop->eatery->longitude, 6, '.', '') }}" data-name="{{ $stop->eatery->name }}">
                            <div class="timeline-badge">{{ $index + 1 }}</div>
                            <div class="timeline-card" onclick="selectStop({{ $index }})">
                                <div class="timeline-card-header" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                        @if($tour->mood === 'cooking')
                                            @if($index === 0)
                                                <span style="font-size: 0.62rem; color: #10b981; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 4px;">🥦 Bước 1: Ghé Chợ Chọn Nguyên Liệu</span>
                                            @elseif($index === 1)
                                                <span style="font-size: 0.62rem; color: #10b981; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 4px;">👩‍🍳 Bước 2: Học làm cùng Nghệ nhân</span>
                                            @elseif($index === 2)
                                                <span style="font-size: 0.62rem; color: #10b981; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 4px;">🔥 Bước 3: Tự nấu & Thưởng thức</span>
                                            @else
                                                <span style="font-size: 0.62rem; color: #10b981; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 4px;">✨ Bước {{ $index + 1 }}: Trải nghiệm bản địa</span>
                                            @endif
                                        @endif
                                    </div>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-top: 4px;">
                                        <h5 class="timeline-card-title" style="margin: 0; font-size: 0.8rem; font-weight: 800; color: var(--text-main);">
                                            {{ $stop->eatery->category->icon ?: '🍜' }} {{ $stop->eatery->name }}
                                        </h5>
                                        <span class="timeline-card-meta">⏱️ {{ $stop->estimated_time ?: '45 phút' }}</span>
                                    </div>
                                </div>
                                <p style="font-size: 0.72rem; color: var(--text-muted); margin: 6px 0 0 0; display: flex; align-items: center; gap: 4px;">
                                    <span>📍</span> {{ $stop->eatery->address }}
                                </p>
                                
                                <!-- Detailed story toggled when active -->
                                <div class="timeline-card-story">
                                    <div style="position: relative; height: 120px; border-radius: 8px; overflow: hidden; margin-bottom: 10px;">
                                        <img src="{{ $stop->eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80' }}" style="width:100%; height:100%; object-fit:cover;">
                                    </div>
                                    <p style="margin: 0 0 12px 0;">{{ $stop->stop_story }}</p>

                                    @if($tour->mood === 'cooking')
                                        <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; padding: 12px; margin-bottom: 12px; font-size: 0.72rem; line-height: 1.45; animation: fadeIn 0.4s ease;">
                                            @if($tour->slug === 'tu-tay-lam-dac-san-co-loa')
                                                @if($index === 0)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🛍️</span>
                                                        <div><strong style="color: #10b981;">Cần mua:</strong> Gạo nếp cái hoa vàng ngon & rau sống tươi ven sông Đuống.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Mua ở đâu:</strong> Sạp rau sạch cô Năm & Đại lý gạo cô Tám ở giữa chợ Mạch Tràng.</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Lựa nông sản quê tươi rói và tập mặc cả duyên dáng chuẩn chợ quê.</div>
                                                    </div>
                                                @elseif($index === 1)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🛠️</span>
                                                        <div><strong style="color: #10b981;">Chuẩn bị:</strong> Bột gạo tẻ ngâm ủ chua, khuôn gỗ ép & cối xay đá cổ truyền.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Học tại đâu:</strong> Xưởng bún nghệ nhân Mạch Tràng cổ truyền (gần giếng Trọng Thủy).</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Tự tay xoay cối xay bột, ép bún vào nồi nước sôi sùng sục vớt ăn liền.</div>
                                                    </div>
                                                @elseif($index === 2)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🍳</span>
                                                        <div><strong style="color: #10b981;">Tự nấu gì:</strong> Bún thịt nướng ngói mộc mạc thơm lừng chấm mắm chanh ớt.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Nấu tại đâu:</strong> Sân vườn thoáng đãng của Tiệm Lẩu Nướng Cổ Loa Hội Quán.</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Quạt bếp lò than hoa, nướng thịt trên ngói đất và thưởng thức đĩa bún tự tay làm 100%.</div>
                                                    </div>
                                                @endif
                                            @elseif($tour->slug === 'goi-banh-chung-xanh-tranh-khuc')
                                                @if($index === 0)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🛍️</span>
                                                        <div><strong style="color: #10b981;">Cần mua:</strong> Lá dong rừng bản to xanh mướt & bó lạt giang dẻo dai.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Mua ở đâu:</strong> Khu bán lá dong gia truyền ngay sảnh chính Chợ Tó.</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Đo kích thước lá dong tươi bằng gang tay và học cách tước cuống lá dong nghệ thuật.</div>
                                                    </div>
                                                @elseif($index === 1)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🛠️</span>
                                                        <div><strong style="color: #10b981;">Chuẩn bị:</strong> Gạo nếp nhung đã vo sạch, đậu xanh đồ nhuyễn, thịt ba chỉ ướp tiêu đen.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Học tại đâu:</strong> Xưởng bánh chưng nghệ nhân làng nghề cổ truyền Tranh Khúc.</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Học gói bánh vuông vức không cần khuôn, xếp bánh đều vào nồi củi đỏ rực.</div>
                                                    </div>
                                                @elseif($index === 2)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🍳</span>
                                                        <div><strong style="color: #10b981;">Tự nấu gì:</strong> Bánh chưng nóng hổi vớt lò ăn kèm hành muối chua ngọt giòn rụm.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Nấu tại đâu:</strong> Khuôn viên rặng lộc vừng rủ bóng mát của Nhà hàng Sinh Thái Lộc Vừng.</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Thưởng thức đĩa bánh chưng nóng nổi khói nghi ngút bên đầm nước lộng gió Vân Trì.</div>
                                                    </div>
                                                @endif
                                            @elseif($tour->slug === 'u-tuong-nep-dat-nung-van-ha')
                                                @if($index === 0)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🛍️</span>
                                                        <div><strong style="color: #10b981;">Cần mua:</strong> Vại sành đất nung Hương Canh cỡ nhỏ & nia tre đan thủ công.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Mua ở đâu:</strong> Sạp đồ gốm dân gian góc phía Tây Chợ TT Đông Anh.</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Gõ nhẹ vại sành nghe tiếng kêu đanh giòn để chọn chiếc kín kẽ nhất.</div>
                                                    </div>
                                                @elseif($index === 1)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🛠️</span>
                                                        <div><strong style="color: #10b981;">Chuẩn bị:</strong> Gạo nếp đồ xôi vàng óng, lá nhãn tơ tươi rói, nước giếng đá ong cổ.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Học tại đâu:</strong> Xưởng ủ tương nghệ nhân làng cổ Vân Hà (gần sân đền cổ kính).</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Đồ xôi nếp vàng, rải đều lên nia, phủ lá nhãn tơ ủ mốc tương tự nhiên.</div>
                                                    </div>
                                                @elseif($index === 2)
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">🍳</span>
                                                        <div><strong style="color: #10b981;">Tự nấu gì:</strong> Nước chấm chao tương nếp béo ngậy ăn kèm rau muống luộc giòn.</div>
                                                    </div>
                                                    <div style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">📍</span>
                                                        <div><strong style="color: #10b981;">Nấu tại đâu:</strong> Triền đê lộng gió mát rượi của Cà Phê Gió Vĩnh Ngọc.</div>
                                                    </div>
                                                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                                                        <span style="font-size: 0.95rem; flex-shrink: 0; margin-top: 1px;">✨</span>
                                                        <div><strong style="color: #10b981;">Trải nghiệm:</strong> Vừa ngắm trọn vẹn cầu Nhật Tân tráng lệ vừa thưởng thức bát nước tương bùi ngọt tự làm.</div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <div style="display: flex; gap: 8px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px; flex-wrap: wrap;">
                                        <!-- Check-in button -->
                                        <button class="checkin-action-btn" onclick="triggerCheckIn(event, {{ $index }})" style="flex: 1 1 100%; padding: 10px 12px; border-radius: 8px; background: @if($tour->mood === 'cooking') linear-gradient(135deg, #10b981 0%, #059669 100%) @else #0ea5e9 @endif; border: none; color: #ffffff; font-weight: 700; font-size: 0.78rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.3s ease; margin-bottom: 4px;">
                                            <span class="check-icon-{{ $index }}">✍️</span> <span class="check-text-{{ $index }}">Đánh giá & Check-in</span>
                                        </button>
                                        
                                        <!-- Directions link -->
                                        <a id="tourStopDirectionsLink-{{ $index }}" href="https://www.google.com/maps/dir/?api=1{{ $index === 0 ? '' : '&origin='.number_format($tour->stops[$index - 1]->eatery->latitude, 6, '.', '').','.number_format($tour->stops[$index - 1]->eatery->longitude, 6, '.', '') }}&destination={{ number_format($stop->eatery->latitude, 6, '.', '') }},{{ number_format($stop->eatery->longitude, 6, '.', '') }}" target="_blank" rel="noopener noreferrer" class="btn-secondary" style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 4px; border-color: @if($tour->mood === 'cooking') rgba(16, 185, 129, 0.4) @else rgba(14, 165, 233, 0.4) @endif; color: var(--primary);">
                                            🗺️ Chỉ đường
                                        </a>

                                        <!-- Quick show details link -->
                                        <a href="/dia-diem/{{ $stop->eatery->slug }}" class="btn-secondary" style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center;">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Start Journey Mode Activation Panel -->
            <div id="setupControlsPanel" style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
                <button class="btn-primary start-journey-btn" onclick="enterJourneyMode()" style="width: 100%; background: @if($tour->mood === 'cooking') linear-gradient(135deg, #10b981 0%, #059669 100%) @else var(--primary-grad) @endif; box-shadow: @if($tour->mood === 'cooking') 0 8px 24px rgba(16, 185, 129, 0.3) @else 0 8px 24px rgba(255, 126, 41, 0.3) @endif; padding: 12px; border-radius: 12px; font-weight: 800; border: none; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    🚀 @if($tour->mood === 'cooking') Bắt đầu Trải nghiệm thực tế @else Bắt đầu Food Tour @endif
                </button>
                
                <button class="btn-secondary" onclick="startCinematicShowcase()" style="width: 100%; padding: 12px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid var(--border-glow); background: rgba(255,255,255,0.02); color: var(--text-main); cursor: pointer;">
                    🎬 Cinematic Tour Showcase
                </button>
            </div>

            <!-- Journey Mode Focus Control Bar -->
            <div class="journey-control-box" id="focusControlPanel" style="display: none; gap: 12px; margin-top: 16px;">
                <button class="btn-secondary" onclick="navigateStop(-1)" style="flex: 1; padding: 12px; font-weight: 700; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Điểm trước
                </button>
                <button class="btn-primary" onclick="navigateStop(1)" style="flex: 1; padding: 12px; font-weight: 700; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    Điểm tiếp theo
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>

        </div>
    </div>

    <!-- RIGHT PANEL: Fullscreen Interactive Map -->
    <div class="tour-map-panel">
        <div id="tourMap" class="tour-map-element"></div>
        
        <!-- Cinematic overlay darken mask -->
        <div class="map-cinematic-mask" id="cinematicMask"></div>
        
        <!-- Cinematic Showcase Info Overlay -->
        <div id="cinematicOverlayText" style="display: none; position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); border: 1.5px solid var(--primary); padding: 16px 28px; border-radius: 20px; color: #ffffff; text-align: center; z-index: 500; font-size: 1.1rem; font-weight: 800; box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 20px rgba(255,126,41,0.25); animation: pulse 2s infinite; pointer-events: none; max-width: 90%;">
            🍿 Đang chạy Cinematic Showcase. Vui lòng chiêm ngưỡng...
        </div>
    </div>

</div>
@endsection

@php
    $mappedStops = $tour->stops->map(function($stop) {
        $eatery = $stop->eatery;
        if (!$eatery) return null;

        $catName = 'Ẩm thực Đông Anh';
        $catIcon = '🍜';

        if (is_object($eatery) && isset($eatery->category)) {
            $catName = is_object($eatery->category) ? ($eatery->category->name ?? 'Ẩm thực Đông Anh') : ($eatery->category['name'] ?? 'Ẩm thực Đông Anh');
            $catIcon = is_object($eatery->category) ? ($eatery->category->icon ?? '🍜') : ($eatery->category['icon'] ?? '🍜');
        } elseif (is_array($eatery) && isset($eatery['category'])) {
            $catName = is_array($eatery['category']) ? ($eatery['category']['name'] ?? 'Ẩm thực Đông Anh') : 'Ẩm thực Đông Anh';
            $catIcon = is_array($eatery['category']) ? ($eatery['category']['icon'] ?? '🍜') : '🍜';
        }

        $lat = is_object($eatery) ? ($eatery->latitude ?? 21.1408) : ($eatery['latitude'] ?? 21.1408);
        $lng = is_object($eatery) ? ($eatery->longitude ?? 105.8450) : ($eatery['longitude'] ?? 105.8450);

        return [
            'id' => is_object($eatery) ? $eatery->id : ($eatery['id'] ?? 0),
            'name' => is_object($eatery) ? ($eatery->name ?? 'Địa điểm Đông Anh') : ($eatery['name'] ?? 'Địa điểm Đông Anh'),
            'address' => is_object($eatery) ? ($eatery->address ?? 'Đông Anh, Hà Nội') : ($eatery['address'] ?? 'Đông Anh, Hà Nội'),
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
            'category_icon' => $catIcon ?: '🍜',
            'category_name' => $catName ?: 'Ẩm thực Đông Anh',
            'image' => (is_object($eatery) ? ($eatery->image_path ?? null) : ($eatery['image_path'] ?? null)) ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80',
            'slug' => is_object($eatery) ? ($eatery->slug ?? '') : ($eatery['slug'] ?? '')
        ];
    })->filter()->values();
@endphp

@section('scripts')
<script>
    // 1. Parse stops coordinates from Eloquent collection
    const stopsData = @json($mappedStops);
    const isUserLoggedIn = @json(auth()->check());
    const tourMood = @json($tour->mood);

    let map = null;
    let markersList = [];
    let routeLine = null;
    let fallbackCurveLines = [];
    let routeSegmentsList = [];
    let activeStopIndex = 0;
    let isJourneyMode = false;
    let completedStops = new Set();
    let cinematicTimeout = null;
    let userMarker = null;
    let userLatitude = null;
    let userLongitude = null;
    let currentReviewIndex = null;
    let selectedModalStarRating = null;
    let checkInReviews = {};
    let currentRouteDrawId = 0;

    // Helper to render beautiful review badge inside timeline cards
    function renderStopReviewBadge(index) {
        const review = checkInReviews[index];
        if (!review) return;

        const storyDiv = document.querySelector(`#stop-item-${index} .timeline-card-story`);
        if (storyDiv) {
            const prevBadge = document.getElementById(`review-badge-${index}`);
            if (prevBadge) prevBadge.remove();
            
            const starsText = review.rating ? '⭐'.repeat(review.rating) : 'Chưa đánh giá sao';
            let imageHtml = '';
            if (review.image) {
                imageHtml = `
                    <div style="position: relative; height: 140px; border-radius: 12px; overflow: hidden; margin-top: 8px; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                        <img src="${review.image}" style="width: 100%; height: 100%; object-fit: cover;">
                        <span style="position: absolute; bottom: 6px; right: 6px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); font-size: 0.65rem; color: #ffffff; padding: 2px 8px; border-radius: 20px; font-weight: 700;">📸 Ảnh kỷ niệm</span>
                    </div>
                `;
            }

            const commentText = review.comment ? `"${review.comment}"` : "Không có bình luận.";

            const badgeBgColor = tourMood === 'cooking' ? 'rgba(16, 185, 129, 0.06)' : 'rgba(255, 126, 41, 0.06)';
            const badgeBorderColor = tourMood === 'cooking' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(255, 126, 41, 0.2)';
            const badgeHtml = `
                <div id="review-badge-${index}" style="margin-top: 12px; padding: 10px; border-radius: 10px; background: ${badgeBgColor}; border: 1.5px solid ${badgeBorderColor}; font-size: 0.75rem; animation: fadeIn 0.4s ease;">
                    <div style="color: #ffb03a; font-weight: 800; display: flex; align-items: center; gap: 4px; margin-bottom: 4px;">
                        <span>${starsText}</span> <span style="color: var(--text-main); font-size: 0.7rem; font-weight: 700;">Đánh giá của bạn</span>
                    </div>
                    <p style="margin: 0; color: var(--text-muted); font-style: italic; line-height: 1.45;">${commentText}</p>
                    ${imageHtml}
                </div>
            `;
            const buttonGroup = storyDiv.querySelector('div[style*="display: flex; gap: 8px"]');
            if (buttonGroup) {
                buttonGroup.insertAdjacentHTML('beforebegin', badgeHtml);
            } else {
                storyDiv.appendChild(badgeHtml);
            }
        }
    }

    // Save current active food tour progress to LocalStorage
    function saveTourStateToLocalStorage() {
        const state = {
            isJourneyMode: isJourneyMode,
            activeStopIndex: activeStopIndex,
            completedStops: Array.from(completedStops),
            checkInReviews: checkInReviews
        };
        localStorage.setItem(`food_tour_state_{{ $tour->slug }}`, JSON.stringify(state));
    }

    // Load active food tour progress from LocalStorage
    function loadTourStateFromLocalStorage() {
        const saved = localStorage.getItem(`food_tour_state_{{ $tour->slug }}`);
        if (!saved) return false;
        try {
            const state = JSON.parse(saved);
            if (state && state.isJourneyMode) {
                isJourneyMode = state.isJourneyMode;
                activeStopIndex = state.activeStopIndex !== undefined ? state.activeStopIndex : 0;
                completedStops = new Set(state.completedStops || []);
                checkInReviews = state.checkInReviews || {};
                
                // Restore timeline card statuses and badges immediately
                completedStops.forEach(idx => {
                    const stopItem = document.getElementById(`stop-item-${idx}`);
                    if (stopItem) {
                        stopItem.classList.add('completed');
                        const btn = document.querySelector(`#stop-item-${idx} .checkin-action-btn`);
                        if (btn) {
                            btn.style.background = '#047857';
                        }
                        const checkIcon = document.querySelector(`.check-icon-${idx}`);
                        if (checkIcon) checkIcon.innerText = '✅';
                        const checkText = document.querySelector(`.check-text-${idx}`);
                        if (checkText) checkText.innerText = 'Đã Check-in!';
                    }
                    renderStopReviewBadge(idx);
                });

                // Trigger UI panel changes and map restoration
                enterJourneyMode(activeStopIndex, true);
                return true;
            }
        } catch (e) {
            console.error("Lỗi phục hồi trạng thái Food Tour từ LocalStorage:", e);
        }
        return false;
    }

    document.addEventListener("DOMContentLoaded", function() {
        initTourMap();
        
        // Mặc định luôn khởi chạy ở Chế độ Xem Tổng quan Lộ trình (Overview Mode)
        focusStartLocation();

        // Force Leaflet to recalculate container size after render
        setTimeout(() => {
            if (map) map.invalidateSize();
        }, 300);
    });

    // 2. Initialize Leaflet Map
    function initTourMap() {
        let centerLat = 21.1408;
        let centerLng = 105.8450;

        if (stopsData && stopsData.length > 0) {
            let sumLat = 0, sumLng = 0, validCount = 0;
            stopsData.forEach(s => {
                if (s.latitude && s.longitude) {
                    sumLat += parseFloat(s.latitude);
                    sumLng += parseFloat(s.longitude);
                    validCount++;
                }
            });
            if (validCount > 0) {
                centerLat = sumLat / validCount;
                centerLng = sumLng / validCount;
            }
        }

        // Leaflet Init
        map = L.map('tourMap', {
            zoomControl: false,
            zoomSnap: 0.5,       // Bước zoom 0.5 giúp phản hồi nhanh nhạy
            zoomDelta: 0.5,      // Độ nhảy zoom mỗi lần cuộn
            wheelPxPerZoomLevel: 60, // Tốc độ zoom tiêu chuẩn nhanh & mượt
            zoomAnimation: true,
            fadeAnimation: true,
            markerZoomAnimation: true
        }).setView([centerLat, centerLng], 14);

        // Add standard zoom control at top right
        L.control.zoom({ position: 'topright' }).addTo(map);

        // Dark/Light theme tiles mapper (CARTO Voyager cho Sáng & CARTO Dark Matter cho Tối)
        const getTileUrl = (theme) => {
            return theme === 'light'
                ? 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        };

        const currentTheme = document.documentElement.getAttribute('data-theme') || (localStorage.getItem('theme') || 'light');
        let tileLayer = L.tileLayer(getTileUrl(currentTheme), {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // Listen for global theme changes to hot-swap map styling
        document.addEventListener('theme-changed', function(e) {
            if (map && tileLayer) {
                map.removeLayer(tileLayer);
                tileLayer = L.tileLayer(getTileUrl(e.detail.theme), {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    subdomains: 'abcd',
                    maxZoom: 20
                }).addTo(map);
            }
        });

        // 3. Add Custom Markers
        stopsData.forEach((stop, idx) => {
            // Create a premium glowing HTML pulse icon
            const isCooking = tourMood === 'cooking';
            const pulseBg = isCooking ? 'rgba(16, 185, 129, 0.2)' : 'rgba(255, 126, 41, 0.2)';
            const pulseBorder = isCooking ? '#10b981' : 'var(--primary)';
            const badgeBg = isCooking ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'var(--primary-grad, var(--primary))';
            
            const customIcon = L.divIcon({
                className: 'custom-stop-marker-wrapper',
                html: `
                    <div class="custom-stop-pulse-marker" style="display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; background: ${pulseBg}; border: 2px solid ${pulseBorder}; position: relative; box-shadow: 0 0 10px ${isCooking ? 'rgba(16, 185, 129, 0.4)' : 'rgba(255, 126, 41, 0.4)'};">
                        <div style="width: 22px; height: 22px; border-radius: 50%; background: ${badgeBg}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">
                            ${idx + 1}
                        </div>
                    </div>
                `,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            // Create Marker
            const marker = L.marker([stop.latitude, stop.longitude], { icon: customIcon }).addTo(map);
            
            // Marker dynamic popups (Styled exactly like the user's template card)
            const popupContent = `
                <div style="width: 100%; font-family: inherit; position: relative;">
                    <!-- 1. Top rounded image banner -->
                    <div style="position: relative; height: 110px; border-radius: 12px; overflow: hidden; margin-bottom: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.06);">
                        <img src="${stop.image}" style="width: 100%; height: 100%; object-fit: cover;" />
                    </div>
                    
                    <!-- 2. Restaurant Name -->
                    <h4 style="margin: 4px 0; font-size: 0.88rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                        ${stop.name}
                    </h4>
                    
                    <!-- 3. Address -->
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 0.68rem; color: #64748b; margin-bottom: 8px;">
                        <span>📍</span>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 190px; font-weight: 600;" title="${stop.address}">
                            ${stop.address}
                        </span>
                    </div>
                    
                    <!-- 4. Directions Link (Cyan gradient matching design) -->
                    <a href="https://www.google.com/maps/dir/?api=1${idx > 0 ? '&origin=' + stopsData[idx - 1].latitude + ',' + stopsData[idx - 1].longitude : ''}&destination=${stop.latitude},${stop.longitude}" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 10px; border-radius: 10px; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; font-weight: 800; font-size: 0.68rem; box-shadow: 0 3px 8px rgba(13, 148, 136, 0.2); margin-bottom: 8px; transition: all 0.2s;">
                        🗺️ Chỉ đường chặng này
                    </a>
                    
                    <!-- 5. Bottom Row: Rating and Action Button -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; padding-top: 6px; border-top: 1px solid rgba(226, 232, 240, 0.5);">
                        <span style="font-size: 0.72rem; font-weight: 800; color: #eab308; display: flex; align-items: center; gap: 2px;">
                            ⭐ ${(4.4 + (stop.id % 6) / 10).toFixed(1)}
                        </span>
                        
                        <a href="/dia-diem/${stop.slug}" style="padding: 5px 12px; border-radius: 20px; background: ${isCooking ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)'}; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 0.68rem; box-shadow: 0 3px 8px ${isCooking ? 'rgba(16, 185, 129, 0.2)' : 'rgba(14, 165, 233, 0.2)'}; transition: all 0.2s;">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            `;
            
            marker.bindPopup(popupContent, {
                maxWidth: 240,
                minWidth: 230,
                className: 'premium-leaflet-popup',
                offset: L.point(0, -18),
                closeButton: true
            });
            
            // Map click selection
            marker.on('click', () => {
                selectStop(idx);
            });

            markersList.push(marker);
        });

        // 4. Generate Animated Glowing Line Route
        drawJourneyRoute();
    }

    // 5. Draw Journey Route (OSRM street route + Parabolic curved line fallback)
    async function drawJourneyRoute(userLat = null, userLng = null) {
        const drawId = ++currentRouteDrawId;
        // Clear existing routes and curves
        clearExistingRouteLines();

        if (stopsData.length < 1) return;

        // Compile points array: if user GPS exists, prepend it!
        let routingPoints = [];
        const hasUserGps = (userLat !== null && userLng !== null);
        if (hasUserGps) {
            routingPoints.push({ latitude: userLat, longitude: userLng });
        }
        routingPoints = routingPoints.concat(stopsData);

        if (routingPoints.length < 2) return;

        // Draw each segment individually to enable gorgeous dynamic styling (solid vs dashed)
        const segmentPromises = [];
        for (let i = 0; i < routingPoints.length - 1; i++) {
            const start = routingPoints[i];
            const end = routingPoints[i + 1];
            const segmentCoordsStr = `${start.longitude},${start.latitude};${end.longitude},${end.latitude}`;
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${segmentCoordsStr}?overview=full&geometries=geojson`;
            
            const fetchWithTimeout = new Promise((resolve) => {
                const timeoutId = setTimeout(() => resolve(null), 1200);
                fetch(osrmUrl)
                    .then(res => res.json())
                    .then(data => {
                        clearTimeout(timeoutId);
                        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                            const coordinates = data.routes[0].geometry.coordinates;
                            resolve(coordinates.map(coord => [coord[1], coord[0]]));
                        } else {
                            resolve(null);
                        }
                    })
                    .catch(() => {
                        clearTimeout(timeoutId);
                        resolve(null);
                    });
            });

            segmentPromises.push(fetchWithTimeout);
        }

        const segmentsData = await Promise.all(segmentPromises);
        if (drawId !== currentRouteDrawId) return; // Cancel stale draws to prevent overlapping race conditions!

        const routePrimaryColor = tourMood === 'cooking' ? '#10b981' : '#0ea5e9';

        segmentsData.forEach((latLngs, i) => {
            const start = routingPoints[i];
            const end = routingPoints[i + 1];

            // Segment i is visited if we have traveled up to activeStopIndex:
            // - If hasUserGps: i <= activeStopIndex (User GPS -> Stop 0 is segment 0)
            // - If no userGps: i < activeStopIndex (Stop 0 -> Stop 1 is segment 0)
            const isVisited = hasUserGps ? (i <= activeStopIndex) : (i < activeStopIndex);

            let segmentLine;

            if (latLngs) {
                // Street routing polyline
                segmentLine = L.polyline(latLngs, {
                    color: isVisited ? routePrimaryColor : (tourMood === 'cooking' ? 'rgba(16, 185, 129, 0.45)' : 'rgba(14, 165, 233, 0.45)'),
                    weight: isVisited ? 6 : 4,
                    opacity: isVisited ? 0.95 : 0.75,
                    lineJoin: 'round'
                }).addTo(map);

                // Apply dynamic class
                setTimeout(() => {
                    const el = segmentLine.getElement();
                    if (el) {
                        if (isVisited) {
                            el.classList.add('route-glow');
                        } else {
                            el.classList.add('route-dashed-glow');
                        }
                    }
                }, 100);
            } else {
                // Fallback parabolic curve
                const midLat = (start.latitude + end.latitude) / 2;
                const midLng = (start.longitude + end.longitude) / 2;
                const latDiff = end.latitude - start.latitude;
                const lngDiff = end.longitude - start.longitude;
                const midLatOffset = midLat + (lngDiff * 0.15);
                const midLngOffset = midLng - (latDiff * 0.15);

                const curvePoints = [];
                const segments = 30;
                for (let t = 0; t <= segments; t++) {
                    const ratio = t / segments;
                    const lat = Math.pow(1 - ratio, 2) * start.latitude + 
                                2 * (1 - ratio) * ratio * midLatOffset + 
                                Math.pow(ratio, 2) * end.latitude;
                    const lng = Math.pow(1 - ratio, 2) * start.longitude + 
                                2 * (1 - ratio) * ratio * midLngOffset + 
                                Math.pow(ratio, 2) * end.longitude;
                    curvePoints.push([lat, lng]);
                }

                segmentLine = L.polyline(curvePoints, {
                    color: isVisited ? routePrimaryColor : (tourMood === 'cooking' ? 'rgba(16, 185, 129, 0.45)' : 'rgba(14, 165, 233, 0.45)'),
                    weight: isVisited ? 6 : 4,
                    opacity: isVisited ? 0.95 : 0.75,
                    lineJoin: 'round'
                }).addTo(map);

                setTimeout(() => {
                    const el = segmentLine.getElement();
                    if (el) {
                        if (isVisited) {
                            el.classList.add('route-glow');
                        } else {
                            el.classList.add('route-dashed-glow');
                        }
                    }
                }, 100);
            }

            routeSegmentsList.push(segmentLine);
        });
    }

    function clearExistingRouteLines() {
        routeSegmentsList.forEach(line => {
            if (map && line) {
                map.removeLayer(line);
            }
        });
        routeSegmentsList = [];
    }

    // 6. Select Stop handler (Autofocus & Highlight)
    function selectStop(index) {
        if (index < 0 || index >= stopsData.length) return;
        
        activeStopIndex = index;
        
        // Automatically mark stops up to current index as visited/completed in journey mode
        if (isJourneyMode) {
            // Loop through all stops: strictly previous stops (i < index) are auto-completed.
            // Active stop (index) and future stops (i >= index) are only completed if the user actually clicked & reviewed them!
            for (let i = 0; i < stopsData.length; i++) {
                if (i < index) {
                    completedStops.add(i);
                    const stopItem = document.getElementById(`stop-item-${i}`);
                    if (stopItem) {
                        stopItem.classList.add('completed');
                        const btn = document.querySelector(`#stop-item-${i} .checkin-action-btn`);
                        if (btn) {
                            btn.style.background = '#047857';
                        }
                        const checkIcon = document.querySelector(`.check-icon-${i}`);
                        if (checkIcon) checkIcon.innerText = '✅';
                        const checkText = document.querySelector(`.check-text-${i}`);
                        if (checkText) checkText.innerText = 'Đã Check-in!';
                    }
                } else {
                    if (!completedStops.has(i)) {
                        const stopItem = document.getElementById(`stop-item-${i}`);
                        if (stopItem) {
                            stopItem.classList.remove('completed');
                            const btn = document.querySelector(`#stop-item-${i} .checkin-action-btn`);
                            if (btn) {
                                btn.style.background = '#0ea5e9';
                            }
                            const checkIcon = document.querySelector(`.check-icon-${i}`);
                            if (checkIcon) checkIcon.innerText = '✍️';
                            const checkText = document.querySelector(`.check-text-${i}`);
                            if (checkText) checkText.innerText = 'Đánh giá & Check-in';
                        }
                    }
                }
            }
            updateJourneyProgress();
            
            // Dynamically redraw route to update solid/dashed segment styles!
            drawJourneyRoute(userLatitude, userLongitude);
        }

        // Remove previous highlights
        document.querySelectorAll('.timeline-item').forEach(item => {
            item.classList.remove('active');
        });

        // Set active class
        const currentItem = document.getElementById(`stop-item-${index}`);
        if (currentItem) {
            currentItem.classList.add('active');
            
            // Scroll sidebar timeline into view smoothly
            currentItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        
        // Dynamic Glow height on sidebar timeline
        updateTimelineGlowHeight(index);

        // Map pan focus camera with elegant glide flyTo
        const stop = stopsData[index];
        if (map) {
            // Close previous popups, then fly and display popup
            map.closePopup();
            map.flyTo([stop.latitude, stop.longitude], 16, {
                animate: true,
                duration: 1.2
            });

            // Auto-open map popup to display stop details, address info, and Menu buttons
            setTimeout(() => {
                if (markersList[index]) {
                    markersList[index].openPopup();
                }
            }, 1300);
        }

        if (isJourneyMode) {
            saveTourStateToLocalStorage();
        }
    }

    // Sets the glowing sidebar timeline line height matching current stops completed
    function updateTimelineGlowHeight(index) {
        const glow = document.getElementById('trailGlow');
        if (!glow) return;
        
        const total = stopsData.length;
        if (total <= 1) {
            glow.style.height = '0%';
            return;
        }

        const percentage = (index / (total - 1)) * 95;
        glow.style.height = `${percentage}%`;
    }

    function focusStartLocation() {
        activeStopIndex = -1;
        
        // Highlight start-timeline-item in sidebar
        document.querySelectorAll('.timeline-item').forEach(item => {
            item.classList.remove('active');
        });
        const startItem = document.getElementById('start-timeline-item');
        if (startItem) {
            startItem.classList.add('active');
            startItem.style.opacity = '1';
            startItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        // Reset glow height to 0%
        const glow = document.getElementById('trailGlow');
        if (glow) glow.style.height = '0%';
        
        // Center map on the overview route
        setTimeout(() => {
            fitMapToRoute();
        }, 300);
    }

    function updateMarkerPopups() {
        stopsData.forEach((stop, idx) => {
            const isCooking = tourMood === 'cooking';
            const popupContent = `
                <div style="width: 100%; font-family: inherit; position: relative;">
                    <!-- 1. Top rounded image banner -->
                    <div style="position: relative; height: 110px; border-radius: 12px; overflow: hidden; margin-bottom: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.06);">
                        <img src="${stop.image}" style="width: 100%; height: 100%; object-fit: cover;" />
                    </div>
                    
                    <!-- 2. Restaurant Name -->
                    <h4 style="margin: 4px 0; font-size: 0.88rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                        ${stop.name}
                    </h4>
                    
                    <!-- 3. Address -->
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 0.68rem; color: #64748b; margin-bottom: 8px;">
                        <span>📍</span>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 190px; font-weight: 600;" title="${stop.address}">
                            ${stop.address}
                        </span>
                    </div>
                    
                    <!-- 4. Directions Link (Cyan gradient matching design) -->
                    <a href="https://www.google.com/maps/dir/?api=1${idx > 0 ? '&origin=' + stopsData[idx - 1].latitude + ',' + stopsData[idx - 1].longitude : (userLatitude && userLongitude ? '&origin=' + userLatitude + ',' + userLongitude : '')}&destination=${stop.latitude},${stop.longitude}" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 10px; border-radius: 10px; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; font-weight: 800; font-size: 0.68rem; box-shadow: 0 3px 8px rgba(13, 148, 136, 0.2); margin-bottom: 8px; transition: all 0.2s;">
                        🗺️ Chỉ đường chặng này
                    </a>
                    
                    <!-- 5. Bottom Row: Rating and Action Button -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; padding-top: 6px; border-top: 1px solid rgba(226, 232, 240, 0.5);">
                        <span style="font-size: 0.72rem; font-weight: 800; color: #eab308; display: flex; align-items: center; gap: 2px;">
                            ⭐ ${(4.4 + (stop.id % 6) / 10).toFixed(1)}
                        </span>
                        
                        <a href="/dia-diem/${stop.slug}" style="padding: 5px 12px; border-radius: 20px; background: ${isCooking ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)'}; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 0.68rem; box-shadow: 0 3px 8px ${isCooking ? 'rgba(16, 185, 129, 0.2)' : 'rgba(14, 165, 233, 0.2)'}; transition: all 0.2s;">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            `;
            
            if (markersList[idx]) {
                markersList[idx].bindPopup(popupContent, {
                    maxWidth: 240,
                    minWidth: 230,
                    className: 'premium-leaflet-popup',
                    offset: L.point(0, -18),
                    closeButton: true
                });
            }
        });
    }

    // 7. Focus / Fullscreen Start Journey Mode
    function enterJourneyMode(targetStopIndex = 0, isRestore = false) {
        if (!isUserLoggedIn) {
            openAuthGateModal();
            return;
        }

        isJourneyMode = true;
        saveTourStateToLocalStorage();
        
        // Automatically dim and complete starting timeline item visually
        const startItem = document.getElementById('start-timeline-item');
        if (startItem) {
            startItem.classList.remove('active');
            startItem.style.opacity = '0.5';
        }
        
        if (!isRestore) {
            // Reset journey memory state for a fresh new experience
            completedStops.clear();
            checkInReviews = {};
            
            // Clear all previously rendered stop badge checkins in UI
            document.querySelectorAll('[id^="review-badge-"]').forEach(badge => badge.remove());
            document.querySelectorAll('.timeline-item').forEach(item => {
                item.classList.remove('completed');
                const btn = item.querySelector('.checkin-action-btn');
                if (btn) {
                    btn.style.background = '#0ea5e9';
                }
                const checkIcon = item.querySelector('[class^="check-icon-"]');
                if (checkIcon) {
                    checkIcon.innerText = '✍️';
                }
                const checkText = item.querySelector('[class^="check-text-"]');
                if (checkText) {
                    checkText.innerText = 'Đánh giá & Check-in';
                }
            });
        }

        document.getElementById('tourLayout').classList.add('start-journey-active');
        document.getElementById('setupControlsPanel').style.display = 'none';
        document.getElementById('focusControlPanel').style.display = 'flex';
        
        const commSection = document.getElementById('communityDiariesSection');
        if (commSection) commSection.style.display = 'none';
        
        // Reset progress
        updateJourneyProgress();

        // 1. Request GPS Geolocation
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userLatitude = position.coords.latitude;
                    userLongitude = position.coords.longitude;

                    // Update popups and directions links dynamically
                    updateMarkerPopups();
                    const startDirectionsLink = document.getElementById("tourStopDirectionsLink-0");
                    if (startDirectionsLink) {
                        startDirectionsLink.href = `https://www.google.com/maps/dir/?api=1&origin=${userLatitude},${userLongitude}&destination=${stopsData[0].latitude},${stopsData[0].longitude}`;
                    }

                    // Remove previous marker
                    if (userMarker) {
                        map.removeLayer(userMarker);
                    }

                    // Create blue pulsating GPS dot marker
                    const userIcon = L.divIcon({
                        className: 'user-gps-marker-wrapper',
                        html: `
                            <div class="user-gps-pulse-marker" style="display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; background: rgba(59, 130, 246, 0.25); border: 2px solid #3b82f6; position: relative; box-shadow: 0 0 12px rgba(59, 130, 246, 0.5);">
                                <div style="width: 14px; height: 14px; border-radius: 50%; background: #3b82f6; box-shadow: 0 0 8px #3b82f6; border: 2px solid #ffffff;"></div>
                            </div>
                        `,
                        iconSize: [34, 34],
                        iconAnchor: [17, 17]
                    });

                    userMarker = L.marker([userLatitude, userLongitude], { icon: userIcon }).addTo(map);
                    userMarker.bindPopup("<b>📍 Vị trí hiện tại của bạn</b><br>Hành trình bắt đầu từ đây!");

                    // Select the target stop and center camera smoothly
                    selectStop(targetStopIndex);
                    setTimeout(() => {
                        fitMapToRoute();
                    }, 500);
                },
                function(error) {
                    console.info("GPS Location unavailable or denied, seamless fallback to Stop 1:", error);
                    drawJourneyRoute().then(() => {
                        fitMapToRoute();
                    });
                    selectStop(targetStopIndex);
                },
                { enableHighAccuracy: false, timeout: 2500 }
            );
        } else {
            drawJourneyRoute().then(() => {
                fitMapToRoute();
            });
            selectStop(targetStopIndex);
        }
    }

    function exitJourneyMode() {
        isJourneyMode = false;
        localStorage.removeItem(`food_tour_state_{{ $tour->slug }}`);
        
        // Restore start timeline item state
        const startItem = document.getElementById('start-timeline-item');
        if (startItem) {
            startItem.style.opacity = '1';
        }
        focusStartLocation();
        
        document.getElementById('tourLayout').classList.remove('start-journey-active');
        document.getElementById('setupControlsPanel').style.display = 'flex';
        document.getElementById('focusControlPanel').style.display = 'none';
        
        const commSection = document.getElementById('communityDiariesSection');
        if (commSection) commSection.style.display = 'block';
        
        // Reset sidebar state and clear cinematic mask
        document.getElementById('cinematicMask').classList.remove('active');
        document.getElementById('cinematicOverlayText').style.display = 'none';
        
        if (cinematicTimeout) {
            clearTimeout(cinematicTimeout);
            cinematicTimeout = null;
        }

        // Clean up user marker
        if (userMarker) {
            map.removeLayer(userMarker);
            userMarker = null;
        }
        userLatitude = null;
        userLongitude = null;

        // Restore standalone route lines
        drawJourneyRoute().then(() => {
            fitMapToRoute();
        });
    }

    function fitMapToRoute() {
        if (!map) return;
        try {
            if (routeSegmentsList.length > 0) {
                const group = new L.featureGroup(routeSegmentsList);
                map.fitBounds(group.getBounds(), { padding: [50, 50] });
            } else if (markersList.length > 0) {
                const group = new L.featureGroup(markersList);
                map.fitBounds(group.getBounds(), { padding: [50, 50] });
            } else if (stopsData && stopsData.length > 0) {
                const bounds = L.latLngBounds(stopsData.map(s => [parseFloat(s.latitude), parseFloat(s.longitude)]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        } catch (e) {
            console.warn("fitMapToRoute exception handled:", e);
        }
    }

    // 8. Prev / Next Stop navigator
    function navigateStop(direction) {
        let target = activeStopIndex + direction;
        if (target < 0) {
            target = 0;
        } else if (target >= stopsData.length) {
            // User clicked "Next" on the last stop of the tour
            if (completedStops.size === stopsData.length) {
                // Open completion certificate modal directly!
                openCompletionModal();
            } else {
                // Prompt them if they want to complete the tour anyway
                const endMsg = tourMood === 'cooking'
                    ? "🏁 Bạn đã đến điểm cuối của cuộc hành trình! Bạn có muốn kết thúc Trải nghiệm thực tế và xem Chứng Nhận Tổng Kết Nhật Ký chuyến đi ngay không?"
                    : "🏁 Bạn đã đến điểm cuối của cuộc hành trình! Bạn có muốn kết thúc Food Tour và xem Chứng Nhận Tổng Kết Nhật Ký chuyến đi ngay không?";
                if (confirm(endMsg)) {
                    openCompletionModal();
                }
            }
            return;
        }
        selectStop(target);
    }

    // 9. Interactive Stop Check-In & Custom Review Workflow
    function triggerCheckIn(event, index) {
        event.stopPropagation(); // Avoid triggering card selection again
        
        if (!isUserLoggedIn) {
            openAuthGateModal();
            return;
        }

        // Logical safety gate: require active journey mode to check-in!
        if (!isJourneyMode) {
            const confirmMsg = tourMood === 'cooking'
                ? "🚀 Bạn cần kích hoạt hành trình 'Bắt đầu Trải nghiệm thực tế' để ghi nhận GPS đi đường và thực hiện Check-in chặng này. Bắt đầu ngay nhé?"
                : "🚀 Bạn cần kích hoạt hành trình 'Bắt đầu Food Tour' để ghi nhận GPS đi đường và thực hiện Check-in chặng này. Bắt đầu ngay nhé?";
            if (confirm(confirmMsg)) {
                enterJourneyMode(index);
                // Open the review modal after a small timeout to let geolocation start smoothly
                setTimeout(() => {
                    openReviewModal(index);
                }, 800);
            }
            return;
        }
        
        const stopItem = document.getElementById(`stop-item-${index}`);
        
        if (completedStops.has(index)) {
            // Confirm delete memory if already checked in
            if (confirm("Bạn có muốn hủy trạng thái Check-in và xóa đánh giá chặng dừng này không?")) {
                completedStops.delete(index);
                delete checkInReviews[index];
                stopItem.classList.remove('completed');
                
                const btn = document.querySelector(`#stop-item-${index} .checkin-action-btn`);
                if (btn) {
                    btn.style.background = '#10b981';
                    document.querySelector(`#stop-item-${index} .check-text-${index}`).innerText = 'Đã khám phá';
                }
                
                // Clear star badge
                const badge = document.getElementById(`review-badge-${index}`);
                if (badge) {
                    badge.remove();
                }

                updateJourneyProgress();
                saveTourStateToLocalStorage();
            }
        } else {
            // Open Review Modal to get stars and feedback!
            openReviewModal(index);
        }
    }

    function openReviewModal(index) {
        currentReviewIndex = index;
        const stop = stopsData[index];
        
        document.getElementById('modalStopName').innerText = stop.name;
        document.getElementById('modalStopIcon').innerText = stop.category_icon || '🍜';
        document.getElementById('modalReviewText').value = '';
        
        // Reset uploaded photo fields
        currentUploadedPhotoBase64 = null;
        document.getElementById('photoFileInput').value = '';
        document.getElementById('photoUploadPreview').src = '';
        document.getElementById('photoUploadPreview').style.display = 'none';
        document.getElementById('photoUploadPlaceholder').style.display = 'block';
        document.getElementById('deletePhotoBtn').style.display = 'none';
        document.getElementById('photoUploadContainer').style.borderColor = 'var(--border-glow)';
        document.getElementById('photoUploadContainer').style.borderStyle = 'dashed';
        
        // Reset stars
        setModalStarRating(null);
        
        const modal = document.getElementById('reviewModal');
        modal.style.display = 'flex';
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
        currentReviewIndex = null;
    }

    function setModalStarRating(rating) {
        selectedModalStarRating = rating;
        highlightStars(rating);
        
        if (rating === null) {
            document.getElementById('starRatingLabel').innerText = "Chưa đánh giá";
            return;
        }

        const labels = {
            1: "Tệ (1/5)",
            2: "Bình thường (2/5)",
            3: "Khá ngon (3/5)",
            4: "Rất ngon! (4/5)",
            5: "Tuyệt vời! (5/5)"
        };
        document.getElementById('starRatingLabel').innerText = labels[rating] || `${rating}/5`;
    }

    function highlightStars(rating) {
        const stars = document.querySelectorAll('.review-star');
        stars.forEach((star, idx) => {
            if (rating !== null && idx < rating) {
                star.style.opacity = '1';
                star.style.transform = 'scale(1.15)';
            } else {
                star.style.opacity = '0.3';
                star.style.transform = 'scale(1)';
            }
        });
    }

    function resetStarsHighlight() {
        highlightStars(selectedModalStarRating);
    }

    function saveStopReview() {
        if (currentReviewIndex === null) return;
        
        const index = currentReviewIndex;
        const comment = document.getElementById('modalReviewText').value.trim();
        
        // Store review
        checkInReviews[index] = {
            eatery_id: stopsData[index].id,
            rating: selectedModalStarRating,
            comment: comment || "",
            image: currentUploadedPhotoBase64
        };
        
        // Mark completed
        completedStops.add(index);
        
        const stopItem = document.getElementById(`stop-item-${index}`);
        stopItem.classList.add('completed');
        
        const btn = document.querySelector(`#stop-item-${index} .checkin-action-btn`);
        if (btn) {
            btn.style.background = '#047857';
            document.querySelector(`#stop-item-${index} .check-text-${index}`).innerText = 'Đã Check-in!';
        }
        
        // Render beautiful badge inside card story
        renderStopReviewBadge(index);

        // Save state to LocalStorage
        saveTourStateToLocalStorage();
        
        // Close modal
        closeReviewModal();
        
        // Launch dynamic confetti celebrate!
        shootConfetti();
        
        // Update progress bar
        updateJourneyProgress();
        
        // Auto navigate to next stop
        if (index < stopsData.length - 1) {
            setTimeout(() => {
                selectStop(index + 1);
            }, 1200);
        } else {
            // Trigger completion certificate when 100% finished
            if (completedStops.size === stopsData.length) {
                setTimeout(() => {
                    openCompletionModal();
                }, 2000);
            }
        }
    }

    // 10. Journey Completion Modal functions
    let overallTourRating = null;
    let overallTourComment = "";
    let tourCoverPhotoBase64 = null;

    function setOverallTourRating(rating) {
        overallTourRating = rating;
        highlightOverallStars(rating);
        
        if (rating === null) {
            document.getElementById('overallRatingLabel').innerText = "Chưa đánh giá";
            return;
        }

        const labels = {
            1: 'Tệ (1/5) 😞',
            2: 'Tạm ổn (2/5) 🙂',
            3: 'Bình thường (3/5) 😐',
            4: 'Rất tốt (4/5) 😊',
            5: 'Tuyệt vời! (5/5) 😍'
        };
        document.getElementById('overallRatingLabel').innerText = labels[rating] || `${rating}/5`;
    }

    function highlightOverallStars(rating) {
        const stars = document.querySelectorAll('.overall-star');
        stars.forEach((star, idx) => {
            if (rating !== null && idx < rating) {
                star.style.opacity = '1';
                star.style.transform = 'scale(1.15)';
                star.style.color = '#ffb03a';
                star.style.textShadow = '0 0 10px rgba(255, 176, 58, 0.6)';
            } else {
                star.style.opacity = '0.35';
                star.style.transform = 'scale(1)';
                star.style.color = '#ccc';
                star.style.textShadow = 'none';
            }
        });
    }

    function resetOverallStarsHighlight() {
        highlightOverallStars(overallTourRating);
    }

    function openCompletionModal() {
        // Automatically mark all stops as completed when finishing the tour
        stopsData.forEach((_, idx) => {
            completedStops.add(idx);
            
            // Also update sidebar timeline UI just in case
            const stopItem = document.getElementById(`stop-item-${idx}`);
            if (stopItem) {
                stopItem.classList.add('completed');
                const btn = document.querySelector(`#stop-item-${idx} .checkin-action-btn`);
                if (btn) {
                    btn.style.background = '#047857';
                    const checkText = document.querySelector(`#stop-item-${idx} .check-text-${idx}`);
                    if (checkText) checkText.innerText = 'Đã Check-in!';
                }
            }
        });
        updateJourneyProgress();

        const listContainer = document.getElementById('completionSummaryList');
        listContainer.innerHTML = '';
        
        stopsData.forEach((stop, index) => {
            const isCompleted = completedStops.has(index);
            const badge = isCompleted ? '✅ Đã đi' : '⚪ Chưa đi';
            const badgeColor = isCompleted ? '#10b981' : '#64748b';
            
            const stopHtml = `
                <div style="display: inline-flex; align-items: center; gap: 4px; background: #ffffff; padding: 3px 8px; border-radius: 12px; border: 1px solid rgba(226, 232, 240, 0.8); font-size: 0.65rem; font-weight: 700; color: #334155; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <span>${stop.category_icon || '🍜'}</span>
                    <span style="max-width: 90px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${stop.name}</span>
                    <span style="font-size: 0.55rem; font-weight: 800; color: ${badgeColor}; margin-left: 2px;">${isCompleted ? '✓' : '○'}</span>
                </div>
            `;
            listContainer.insertAdjacentHTML('beforeend', stopHtml);
        });
        
        // Reset Tour Cover Upload Preview
        tourCoverPhotoBase64 = null;
        overallTourComment = "";
        document.getElementById('overallTourCommentInput').value = '';
        document.getElementById('tourCoverInput').value = '';
        document.getElementById('tourCoverPreview').src = '';
        document.getElementById('tourCoverPreview').style.display = 'none';
        document.getElementById('tourCoverPlaceholder').style.display = 'block';
        document.getElementById('deleteTourCoverBtn').style.display = 'none';
        document.getElementById('tourCoverPhotoContainer').style.borderColor = 'rgba(203, 213, 225, 0.8)';
        document.getElementById('tourCoverPhotoContainer').style.borderStyle = 'dashed';

        // Set overall rating to null by default
        setOverallTourRating(null);

        // Format Completion Date
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('completionDateText').innerText = new Date().toLocaleDateString('vi-VN', options);
        
        document.getElementById('completionModal').style.display = 'flex';
        
        // Big Confetti burst!
        shootConfetti();
        setTimeout(shootConfetti, 400);
        setTimeout(shootConfetti, 800);
    }

    function closeCompletionModal() {
        document.getElementById('completionModal').style.display = 'none';
        
        // Restore active stop focus, highlighting, and map panning
        if (isJourneyMode && activeStopIndex >= 0) {
            selectStop(activeStopIndex);
        }
    }

    function exitAndGoHome() {
        closeCompletionModal();
        exitJourneyMode();
        const targetUrl = '{{ $tour->mood }}' === 'cooking' ? '/exp-corner' : '/food-tours';
        window.location.href = targetUrl;
    }

    function shareCompletion() {
        const tourName = "{{ $tour->name }}";
        const shareText = `🏆 Tôi đã hoàn thành xuất sắc chuyến hành trình Food Tour "${tourName}" trên Bản đồ số Ẩm thực Đông Anh! Khám phá ngay chặng đường ẩm thực tuyệt vời tại đây nhé:`;
        const shareUrl = window.location.href;

        if (navigator.share) {
            navigator.share({
                title: `Hành trình ẩm thực Đông Anh - ${tourName}`,
                text: shareText,
                url: shareUrl
            }).catch(err => console.log('Lỗi chia sẻ:', err));
        } else {
            // Desktop Clipboard copy fallback
            navigator.clipboard.writeText(`${shareText}\n${shareUrl}`).then(() => {
                showToast("📋 Đã sao chép liên kết chia sẻ hành trình ẩm thực của bạn!");
            }).catch(err => {
                showToast("❌ Không thể sao chép liên kết!");
            });
        }
    }

    // Modern glassmorphic Toast notification creator
    function showToast(message) {
        let toast = document.getElementById('premium-custom-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'premium-custom-toast';
            toast.style.cssText = `
                position: fixed;
                top: 40px;
                left: 50%;
                transform: translateX(-50%) translateY(-100px);
                background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95));
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 2px solid rgba(255, 126, 41, 0.6);
                color: #ffffff;
                padding: 16px 32px;
                border-radius: 16px;
                font-size: 1rem;
                font-weight: 800;
                line-height: 1.5;
                box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 25px rgba(255, 126, 41, 0.35);
                z-index: 100005;
                transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                pointer-events: none;
                text-align: center;
                max-width: 90%;
                width: max-content;
            `;
            document.body.appendChild(toast);
        }
        
        toast.innerText = message;
        toast.style.transform = 'translateX(-50%) translateY(0)';
        toast.style.opacity = '1';
        
        setTimeout(() => {
            toast.style.transform = 'translateX(-50%) translateY(-100px)';
            toast.style.opacity = '0';
        }, 4000); // Increased duration to 4 seconds for longer text
    }

    // 11. Custom Camera & Image Upload Handlers
    function triggerPhotoUploadInput() {
        document.getElementById('photoFileInput').click();
    }

    function handlePhotoSelection(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            currentUploadedPhotoBase64 = e.target.result;
            
            const previewImg = document.getElementById('photoUploadPreview');
            const placeholder = document.getElementById('photoUploadPlaceholder');
            const deleteBtn = document.getElementById('deletePhotoBtn');
            const container = document.getElementById('photoUploadContainer');
            
            previewImg.src = currentUploadedPhotoBase64;
            previewImg.style.display = 'block';
            placeholder.style.display = 'none';
            deleteBtn.style.display = 'flex';
            container.style.borderStyle = 'solid';
            container.style.borderColor = 'var(--primary)';
        };
        reader.readAsDataURL(file);
    }

    function removeUploadedPhoto(event) {
        event.stopPropagation();
        
        currentUploadedPhotoBase64 = null;
        document.getElementById('photoFileInput').value = '';
        
        const previewImg = document.getElementById('photoUploadPreview');
        const placeholder = document.getElementById('photoUploadPlaceholder');
        const deleteBtn = document.getElementById('deletePhotoBtn');
        const container = document.getElementById('photoUploadContainer');
        
        previewImg.src = '';
        previewImg.style.display = 'none';
        placeholder.style.display = 'block';
        deleteBtn.style.display = 'none';
        container.style.borderStyle = 'dashed';
        container.style.borderColor = 'var(--border-glow)';
    }

    function triggerTourCoverUpload() {
        document.getElementById('tourCoverInput').click();
    }

    function handleTourCoverSelection(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            tourCoverPhotoBase64 = e.target.result;
            
            const previewImg = document.getElementById('tourCoverPreview');
            const placeholder = document.getElementById('tourCoverPlaceholder');
            const deleteBtn = document.getElementById('deleteTourCoverBtn');
            const container = document.getElementById('tourCoverPhotoContainer');
            
            previewImg.src = tourCoverPhotoBase64;
            previewImg.style.display = 'block';
            placeholder.style.display = 'none';
            deleteBtn.style.display = 'flex';
            container.style.borderStyle = 'solid';
            container.style.borderColor = 'var(--primary)';
        };
        reader.readAsDataURL(file);
    }

    function removeTourCoverPhoto(event) {
        event.stopPropagation();
        
        tourCoverPhotoBase64 = null;
        document.getElementById('tourCoverInput').value = '';
        
        const previewImg = document.getElementById('tourCoverPreview');
        const placeholder = document.getElementById('tourCoverPlaceholder');
        const deleteBtn = document.getElementById('deleteTourCoverBtn');
        const container = document.getElementById('tourCoverPhotoContainer');
        
        previewImg.src = '';
        previewImg.style.display = 'none';
        placeholder.style.display = 'block';
        deleteBtn.style.display = 'none';
        container.style.borderStyle = 'dashed';
        container.style.borderColor = 'rgba(255,255,255,0.15)';
    }

    // Updates progress indicators
    function updateJourneyProgress() {
        const fill = document.getElementById('progressBarFill');
        const text = document.getElementById('journeyProgressText');
        if (!fill || !text) return;

        const total = stopsData.length;
        const completed = completedStops.size;
        const percentage = Math.round((completed / total) * 100);

        fill.style.width = `${percentage}%`;
        text.innerText = `Tiến trình: ${percentage}% (${completed}/${total} stops)`;
    }

    // 10. Cinematic Showcase Autoplay Mode
    function startCinematicShowcase() {
        // Exit journey if open
        exitJourneyMode();

        const mask = document.getElementById('cinematicMask');
        const textOverlay = document.getElementById('cinematicOverlayText');
        
        mask.classList.add('active');
        textOverlay.style.display = 'block';

        let currentShowcaseIdx = 0;
        
        function runStep() {
            if (currentShowcaseIdx >= stopsData.length) {
                // Tour showcase ends
                setTimeout(() => {
                    mask.classList.remove('active');
                    textOverlay.style.display = 'none';
                    alert("🎬 Trình diễn Showcase kết thúc! Bạn hãy nhấn '🚀 Bắt đầu Food Tour' để bắt đầu hành trình thực tế nhé!");
                }, 2000);
                return;
            }

            selectStop(currentShowcaseIdx);
            currentShowcaseIdx++;
            
            // Schedule next camera zoom in 5 seconds
            cinematicTimeout = setTimeout(runStep, 5000);
        }

        // Run first showcase stop immediately
        runStep();
    }

    // 11. Custom CSS/JS Confetti Launcher
    function shootConfetti() {
        const container = document.getElementById('confettiContainer');
        if (!container) return;

        // Generate 100 colorful paper flakes
        const colors = ['#0ea5e9', '#ffb03a', '#10b981', '#3b82f6', '#ec4899', '#a855f7'];
        const numConfetti = 80;

        for (let i = 0; i < numConfetti; i++) {
            const flake = document.createElement('div');
            const randomColor = colors[Math.floor(Math.random() * colors.length)];
            
            flake.style.position = 'absolute';
            flake.style.width = `${Math.random() * 8 + 6}px`;
            flake.style.height = `${Math.random() * 10 + 6}px`;
            flake.style.background = randomColor;
            flake.style.borderRadius = '2px';
            
            // Random horizontal launch position
            flake.style.left = `${Math.random() * 100}vw`;
            // Launch from top of screen
            flake.style.top = `-20px`;
            flake.style.opacity = Math.random().toString();
            flake.style.zIndex = '9999';
            
            container.appendChild(flake);

            // Animate fall
            const duration = Math.random() * 3 + 2; // 2-5 seconds
            const delay = Math.random() * 0.5; // up to 500ms delay

            flake.animate([
                { transform: 'translate3d(0, 0, 0) rotate(0deg)', opacity: 1 },
                { transform: `translate3d(${(Math.random() - 0.5) * 200}px, 105vh, 0) rotate(${Math.random() * 720}deg)`, opacity: 0 }
            ], {
                duration: duration * 1000,
                delay: delay * 1000,
                easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                fill: 'forwards'
            });

            // Cleanup DOM after animation completes
            setTimeout(() => {
                flake.remove();
            }, (duration + delay) * 1000);
        }
    }

    // 12. Interactive Stop Rating & Comment Syncing inside the Completion Ticket
    let ticketImageTargetIndex = null;

    function triggerTicketStopImage(index) {
        ticketImageTargetIndex = index;
        document.getElementById('ticketStopFileInput').click();
    }

    function handleTicketStopPhotoSelection(event) {
        if (ticketImageTargetIndex === null) return;
        const index = ticketImageTargetIndex;
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const base64 = e.target.result;
            
            // Ensure review object exists
            if (!checkInReviews[index]) {
                checkInReviews[index] = { rating: 5, comment: "", image: null };
            }
            checkInReviews[index].image = base64;
            completedStops.add(index);

            // Sync timeline card completed state
            const stopItem = document.getElementById(`stop-item-${index}`);
            if (stopItem) {
                stopItem.classList.add('completed');
                const btn = document.querySelector(`#stop-item-${index} .checkin-action-btn`);
                if (btn) {
                    btn.style.background = '#047857';
                    document.querySelector(`#stop-item-${index} .check-text-${index}`).innerText = 'Đã Check-in!';
                }
            }

            // Sync timeline card review badge
            updateTimelineBadgeReview(index);

            // Re-render completion modal and update overall journey progress
            openCompletionModal();
            updateJourneyProgress();
        };
        reader.readAsDataURL(file);
        ticketImageTargetIndex = null;
    }

    function setTicketStopRating(index, rating) {
        if (!checkInReviews[index]) {
            checkInReviews[index] = { rating: rating, comment: "", image: null };
        } else {
            checkInReviews[index].rating = rating;
        }
        completedStops.add(index);

        // Sync timeline card completed state
        const stopItem = document.getElementById(`stop-item-${index}`);
        if (stopItem) {
            stopItem.classList.add('completed');
            const btn = document.querySelector(`#stop-item-${index} .checkin-action-btn`);
            if (btn) {
                btn.style.background = '#047857';
                document.querySelector(`#stop-item-${index} .check-text-${index}`).innerText = 'Đã Check-in!';
            }
        }

        updateTimelineBadgeReview(index);

        // Redraw stars instantly inside the ticket list
        const stars = document.querySelectorAll(`.ticket-star-${index}`);
        stars.forEach((star, idx) => {
            if (idx < rating) {
                star.style.opacity = '1';
            } else {
                star.style.opacity = '0.25';
            }
        });

        updateJourneyProgress();
    }

    function updateTicketStopComment(index, val) {
        if (!checkInReviews[index]) {
            checkInReviews[index] = { rating: 5, comment: val, image: null };
        } else {
            checkInReviews[index].comment = val;
        }
        completedStops.add(index);

        // Sync timeline card completed state
        const stopItem = document.getElementById(`stop-item-${index}`);
        if (stopItem) {
            stopItem.classList.add('completed');
            const btn = document.querySelector(`#stop-item-${index} .checkin-action-btn`);
            if (btn) {
                btn.style.background = '#047857';
                document.querySelector(`#stop-item-${index} .check-text-${index}`).innerText = 'Đã Check-in!';
            }
        }

        updateTimelineBadgeReview(index);
        updateJourneyProgress();
    }

    function saveJourneyDiary() {
        if (!isUserLoggedIn) {
            closeCompletionModal();
            openAuthGateModal();
            return;
        }

        // Require overall rating with a beautiful prompt
        if (overallTourRating === null) {
            showCustomAlert(
                "Bạn quên chấm điểm kìa! ✨", 
                "Hãy dành 1 giây chọn số sao để chia sẻ cảm nhận của bạn về chuyến hành trình ẩm thực Đông Anh tuyệt vời này nhé!",
                "Tuyệt vời, để mình chọn!",
                null,
                "⭐"
            );
            
            // Add a gentle pulsing glow to the star row to draw attention
            const starRow = document.querySelector('.overall-star-rating-row');
            if (starRow) {
                starRow.style.animation = 'pulseAttention 1.5s ease-in-out 2';
                setTimeout(() => { starRow.style.animation = 'none'; }, 3000);
            }
            return;
        }
        
        // Note: Cover photo selection is fully optional now and has no prompts.

        // Show a loading button state to prevent double submits
        const saveBtn = document.querySelector('button[onclick="saveJourneyDiary()"]');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '⏳ Đang lưu nhật ký hành trình...';

        // Prepare request body payload
        overallTourComment = document.getElementById('overallTourCommentInput').value.trim();
        const payload = {
            rating: overallTourRating,
            comment: overallTourComment,
            image: tourCoverPhotoBase64,
            completed_stops: Array.from(completedStops),
            stop_reviews: checkInReviews,
            share_to_community: document.getElementById('shareToCommunityToggle')
                ? document.getElementById('shareToCommunityToggle').checked
                : false
        };

        fetch('/api/food-tours/{{ $tour->id }}/diary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeCompletionModal();
                exitJourneyMode();
                triggerCelebrationConfetti();
                
                const targetUrl = '{{ $tour->mood }}' === 'cooking' ? '/exp-corner?saved=1' : '/food-tours?saved=1';
                
                showCustomAlert(
                    "Lưu nhật ký thành công! 🎉",
                    "⭐ Đánh giá chung: " + (overallTourRating ? overallTourRating + "/5 Sao" : "Không có") + "\n✍️ Bình luận: " + (overallTourComment || "Chưa có bình luận."),
                    "Xem danh sách Food Tour 🚀",
                    function() {
                        window.location.href = targetUrl;
                    },
                    "🎉"
                );
            } else {
                showCustomAlert(
                    "❌ Lưu nhật ký thất bại",
                    data.message || "Vui lòng thử lại sau.",
                    "Đã hiểu",
                    null,
                    "⚠️"
                );
            }
        })
        .catch(err => {
            console.error("Lưu nhật ký hành trình thất bại:", err);
            showCustomAlert(
                "❌ Lỗi kết nối hệ thống",
                "Lỗi kết nối máy chủ khi lưu nhật ký. Vui lòng thử lại!",
                "Đã hiểu",
                null,
                "🌐"
            );
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    }

    // 13. Smart Auth Gate modal controllers
    function openAuthGateModal() {
        const modal = document.getElementById('authGateModal');
        if (!modal) return;
        
        const currentPath = window.location.pathname;
        const registerBtn = document.getElementById('authGateRegisterBtn');
        const loginBtn = document.getElementById('authGateLoginBtn');
        
        if (registerBtn) {
            registerBtn.href = `/auth/register?redirect=${encodeURIComponent(currentPath)}`;
        }
        if (loginBtn) {
            loginBtn.href = `/auth/login?redirect=${encodeURIComponent(currentPath)}`;
        }
        
        modal.style.display = 'flex';
    }

    function closeAuthGateModal() {
        const modal = document.getElementById('authGateModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // 14. Celebration Confetti Engine
    function triggerCelebrationConfetti() {
        const container = document.getElementById('confettiContainer');
        if (!container) return;
        container.innerHTML = '';
        
        const colors = ['#0ea5e9', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#3b82f6', '#f43f5e'];
        const particleCount = 90;

        for (let i = 0; i < particleCount; i++) {
            const confetti = document.createElement('div');
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size = Math.random() * 10 + 6;
            const left = Math.random() * 100;
            const animDuration = Math.random() * 2.5 + 2.5;
            const animDelay = Math.random() * 0.4;
            const shape = Math.random() > 0.5 ? '50%' : '3px';

            confetti.style.cssText = `
                position: fixed;
                top: -20px;
                left: ${left}vw;
                width: ${size}px;
                height: ${size}px;
                background: ${color};
                border-radius: ${shape};
                z-index: 100100;
                pointer-events: none;
                opacity: ${Math.random() * 0.85 + 0.15};
                box-shadow: 0 0 10px ${color};
                transform: rotate(${Math.random() * 360}deg);
                animation: confettiFall ${animDuration}s ease-out ${animDelay}s forwards;
            `;
            container.appendChild(confetti);
        }

        setTimeout(() => {
            if (container) container.innerHTML = '';
        }, 5500);
    }

    // 15. Community Diaries Modal Controllers
    function openCommunityDiariesModal() {
        const modal = document.getElementById('communityDiariesModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeCommunityDiariesModal() {
        const modal = document.getElementById('communityDiariesModal');
        if (modal) modal.style.display = 'none';
    }
</script>

<style>
    /* Float animation for auth gate modal icon */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    /* Marching ants dashed path animation for unvisited future routes */
    @keyframes dashedMarch {
        to {
            stroke-dashoffset: -24;
        }
    }
    .route-dashed-glow {
        stroke-dasharray: 10 12;
        animation: dashedMarch 1.2s linear infinite !important;
        stroke-linecap: round;
        filter: drop-shadow(0 0 3px rgba(255, 126, 41, 0.4));
    }


    /* Lock body overflow to keep everything perfectly inside the viewport */
    body {
        overflow: hidden !important;
    }

    /* User GPS Marker pulse effect */
    .user-gps-pulse-marker::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.45);
        animation: user-pulse 2s infinite ease-out;
        z-index: -1;
    }
    
    @keyframes user-pulse {
        0% {
            transform: scale(0.6);
            opacity: 1;
        }
        100% {
            transform: scale(2.4);
            opacity: 0;
        }
    }

    /* Styling adjustments specifically for Leaflet dynamic popups */
    .leaflet-popup-content-wrapper {
        background: var(--bg-card) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-glow) !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2) !important;
    }
    .leaflet-popup-tip {
        background: var(--bg-card) !important;
        border-color: var(--border-glow) !important;
    }
    .leaflet-popup-content h5 {
        color: var(--text-main) !important;
    }
    .leaflet-popup-content p {
        color: var(--text-muted) !important;
    }

    /* Review & Overall Star transitions */
    .review-star, .overall-star {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .review-star:hover, .overall-star:hover {
        transform: scale(1.3) !important;
        text-shadow: 0 0 10px rgba(255, 176, 58, 0.8);
    }
    
    /* Completion certificate keyframe animation */
    @keyframes bounce {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-8px) scale(1.05); }
    }
    
    /* Attention pulsing for missing stars */
    @keyframes pulseAttention {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 126, 41, 0.7); background: transparent; border-radius: 10px; }
        50% { transform: scale(1.05); box-shadow: 0 0 15px 5px rgba(255, 126, 41, 0); background: rgba(255, 126, 41, 0.1); border-radius: 10px; }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 126, 41, 0); background: transparent; border-radius: 10px; }
    }
    
    /* Interactive Stop Image Change Hover Effect */
    .hover-upload-overlay:hover {
        opacity: 1 !important;
    }
</style>

<!-- 12. Check-in & Review Glassmorphic Modal (Bright Frosted Theme) -->
<div id="reviewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.2); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 10000; align-items: center; justify-content: center;">
    <div style="width: 90%; max-width: 420px; padding: 26px; border-radius: 24px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1.5px solid rgba(255,255,255,0.6); box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); color: #0f172a; position: relative;">
        <button onclick="closeReviewModal()" style="position: absolute; top: 18px; right: 18px; background: none; border: none; font-size: 1.2rem; color: #64748b; cursor: pointer;">✕</button>
        
        <div style="text-align: center; margin-bottom: 16px;">
            <span id="modalStopIcon" style="font-size: 2.2rem; display: block; margin-bottom: 6px;">🍜</span>
            <h3 id="modalStopName" style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin: 0 0 6px 0;"></h3>
            <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Lưu giữ kỷ niệm & chia sẻ đánh giá của bạn</p>
        </div>
        
        <!-- Star Rating Selector -->
        <div style="margin-bottom: 16px; text-align: center;">
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Đánh giá chất lượng của bạn</label>
            <div class="star-rating-row" style="display: flex; justify-content: center; gap: 8px; font-size: 2rem; cursor: pointer; user-select: none;">
                <span class="review-star" data-value="1" onclick="setModalStarRating(1)" onmouseover="highlightStars(1)" onmouseout="resetStarsHighlight()">⭐</span>
                <span class="review-star" data-value="2" onclick="setModalStarRating(2)" onmouseover="highlightStars(2)" onmouseout="resetStarsHighlight()">⭐</span>
                <span class="review-star" data-value="3" onclick="setModalStarRating(3)" onmouseover="highlightStars(3)" onmouseout="resetStarsHighlight()">⭐</span>
                <span class="review-star" data-value="4" onclick="setModalStarRating(4)" onmouseover="highlightStars(4)" onmouseout="resetStarsHighlight()">⭐</span>
                <span class="review-star" data-value="5" onclick="setModalStarRating(5)" onmouseover="highlightStars(5)" onmouseout="resetStarsHighlight()">⭐</span>
            </div>
            <span id="starRatingLabel" style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--primary); margin-top: 4px;">Tuyệt vời! (5/5)</span>
        </div>

        <!-- Photo Upload Box (Polaroid style) -->
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Đăng ảnh kỷ niệm thực tế</label>
            <div id="photoUploadContainer" onclick="triggerPhotoUploadInput()" style="width: 100%; height: 110px; border: 1.5px dashed rgba(203, 213, 225, 0.8); border-radius: 12px; background: rgba(248, 250, 252, 0.6); display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; overflow: hidden; position: relative;">
                <!-- Label when empty -->
                <div id="photoUploadPlaceholder" style="text-align: center; padding: 10px;">
                    <span style="font-size: 1.5rem; display: block; margin-bottom: 4px;">📸</span>
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Chụp ảnh ngay hoặc chọn từ thư viện</span>
                </div>
                <img id="photoUploadPreview" style="display: none; width: 100%; height: 100%; object-fit: cover;" />
                <button id="deletePhotoBtn" onclick="removeUploadedPhoto(event)" style="display: none; position: absolute; top: 6px; right: 6px; background: rgba(239, 68, 68, 0.85); border: none; width: 22px; height: 22px; border-radius: 50%; color: #ffffff; font-size: 0.75rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">✕</button>
            </div>
            <input type="file" id="photoFileInput" accept="image/*" onchange="handlePhotoSelection(event)" style="display: none;" />
        </div>
        
        <!-- Review Comment Textarea -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px;">Cảm nhận của bạn về món ăn</label>
            <textarea id="modalReviewText" rows="2" placeholder="Món ăn có hợp khẩu vị không? Không gian quán ra sao?..." style="width: 100%; padding: 10px; border-radius: 10px; background: #ffffff; border: 1.5px solid rgba(226, 232, 240, 0.8); color: #0f172a; outline: none; font-size: 0.82rem; resize: none; line-height: 1.4; font-family: inherit; transition: border-color 0.3s;"></textarea>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 12px;">
            <button onclick="closeReviewModal()" style="flex: 1; padding: 10px; border-radius: 12px; font-weight: 700; font-size: 0.88rem; background: rgba(241, 245, 249, 0.8); border: 1px solid rgba(226, 232, 240, 0.8); color: #475569; cursor: pointer;">Hủy</button>
            <button onclick="saveStopReview()" class="btn-primary" style="flex: 1.5; padding: 10px; border-radius: 12px; font-weight: 800; font-size: 0.88rem; border: none; cursor: pointer; box-shadow: var(--shadow-glow);">🚀 Lưu Check-in</button>
        </div>
    </div>
</div>

<!-- 13. Journey Completion Certificate Summary Modal (Bright Frosted Theme) -->
<div id="completionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.2); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 10001; align-items: center; justify-content: center; padding: 15px 20px;">
    <div class="completion-scroll-card" style="width: 100%; max-width: 510px; padding: 22px 26px; border-radius: 24px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 2px solid rgba(255, 255, 255, 0.9); position: relative; box-shadow: 0 25px 60px rgba(15, 23, 42, 0.15); color: #0f172a; overflow: hidden;">
        <button onclick="closeCompletionModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.2rem; color: #64748b; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">✕</button>
        
        <div style="text-align: center; margin-bottom: 10px;">
            <span style="font-size: 2rem; display: block; animation: bounce 2s infinite; margin-bottom: 2px;">🏆</span>
            <span style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); padding: 2px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.6px; display: inline-block; margin-bottom: 3px;">CHỨNG NHẬN HOÀN THÀNH</span>
            <h2 style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin: 0 0 2px 0; line-height: 1.2;">{{ $tour->name }}</h2>
            <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Chúc mừng bạn đã chinh phục 100% chặng đường!</p>
        </div>
        
        <!-- Certificate Input & Ticket Box -->
        <div id="certificateTicket" style="background: rgba(248, 250, 252, 0.7); border: 1.5px dashed var(--primary); border-radius: 14px; padding: 12px; margin-bottom: 12px;">
            
            <!-- 1. Star Rating Selector for the entire Journey -->
            <div style="margin-bottom: 8px; text-align: center;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 3px;">Đánh giá chất lượng chuyến đi của bạn</label>
                <div class="overall-star-rating-row" style="display: flex; justify-content: center; gap: 6px; font-size: 1.55rem; cursor: pointer; user-select: none;">
                    <span class="overall-star" data-value="1" onclick="setOverallTourRating(1)" onmouseover="highlightOverallStars(1)" onmouseout="resetOverallStarsHighlight()" style="transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: inline-block; cursor: pointer;">⭐</span>
                    <span class="overall-star" data-value="2" onclick="setOverallTourRating(2)" onmouseover="highlightOverallStars(2)" onmouseout="resetOverallStarsHighlight()" style="transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: inline-block; cursor: pointer;">⭐</span>
                    <span class="overall-star" data-value="3" onclick="setOverallTourRating(3)" onmouseover="highlightOverallStars(3)" onmouseout="resetOverallStarsHighlight()" style="transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: inline-block; cursor: pointer;">⭐</span>
                    <span class="overall-star" data-value="4" onclick="setOverallTourRating(4)" onmouseover="highlightOverallStars(4)" onmouseout="resetOverallStarsHighlight()" style="transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: inline-block; cursor: pointer;">⭐</span>
                    <span class="overall-star" data-value="5" onclick="setOverallTourRating(5)" onmouseover="highlightOverallStars(5)" onmouseout="resetOverallStarsHighlight()" style="transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: inline-block; cursor: pointer;">⭐</span>
                </div>
                <span id="overallRatingLabel" style="display: block; font-size: 0.72rem; font-weight: 700; color: #0ea5e9; margin-top: 1px;">Tuyệt vời! (5/5)</span>
            </div>
 
            <!-- 2. Trip Cover Selfie Photo Frame -->
            <div style="margin-bottom: 8px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 3px;">Đăng ảnh Selfie kỷ niệm chuyến đi</label>
                <div id="tourCoverPhotoContainer" onclick="triggerTourCoverUpload()" style="border: 1.5px dashed rgba(203, 213, 225, 0.8); border-radius: 8px; height: 66px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; background: rgba(255, 255, 255, 0.85); position: relative; transition: all 0.3s;">
                    <div id="tourCoverPlaceholder" style="text-align: center; padding: 2px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                        <span style="font-size: 1.1rem;">🤳</span>
                        <span style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Chụp ảnh hoặc chọn từ thiết bị (Tùy chọn)</span>
                    </div>
                    <img id="tourCoverPreview" style="display: none; width: 100%; height: 100%; object-fit: cover;" />
                    <button id="deleteTourCoverBtn" onclick="removeTourCoverPhoto(event)" style="display: none; position: absolute; top: 4px; right: 4px; background: rgba(239, 68, 68, 0.85); border: none; width: 16px; height: 16px; border-radius: 50%; color: #ffffff; font-size: 0.6rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;">✕</button>
                </div>
                <input type="file" id="tourCoverInput" accept="image/*" onchange="handleTourCoverSelection(event)" style="display: none;" />
            </div>
 
            @if($tour->is_ai_generated && $tour->status === 'draft')
            <!-- 🌍 Chia sẻ lên Cộng đồng AI Tour (chỉ hiện với AI Tour) -->
            <div id="shareCommunityBox" style="margin-bottom: 8px; padding: 10px 12px; border-radius: 10px; background: @if($tour->mood === 'cooking') linear-gradient(135deg, rgba(16,185,129,0.08), rgba(52,211,153,0.05)) @else linear-gradient(135deg, rgba(var(--primary-rgb), 0.08), rgba(6,182,212,0.05)) @endif; border: 1.5px solid @if($tour->mood === 'cooking') rgba(16,185,129,0.25) @else rgba(var(--primary-rgb), 0.25) @endif;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <div style="position: relative; flex-shrink: 0; margin-top: 1px;">
                        <input type="checkbox" id="shareToCommunityToggle" style="width: 16px; height: 16px; accent-color: @if($tour->mood === 'cooking') #10b981 @else var(--primary) @endif; cursor: pointer;">
                    </div>
                    <div>
                        <span style="font-size: 0.78rem; font-weight: 800; color: #334155; display: block; line-height: 1.3;">
                            🌍 Chia sẻ lộ trình này lên cộng đồng @if($tour->mood === 'cooking') Hành trình @else Food Tour @endif
                        </span>
                        <span style="font-size: 0.7rem; color: #64748b; display: block; margin-top: 2px; line-height: 1.4;">
                            Lộ trình sẽ hiển thị công khai trong <strong style="color: @if($tour->mood === 'cooking') #10b981 @else var(--primary) @endif;">72 giờ</strong> để người dùng khác có thể khám phá và trải nghiệm.
                        </span>
                    </div>
                </label>
            </div>
            @endif

            <!-- 3. Overall Comment Input -->
            <div style="margin-bottom: 8px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 3px;">Nhập bình luận đánh giá</label>
                <textarea id="overallTourCommentInput" oninput="overallTourComment = this.value" rows="2" placeholder="Cảm nhận tổng quan của bạn về Food Tour..." style="width: 100%; padding: 8px 12px; border-radius: 8px; background: #ffffff; border: 1.5px solid rgba(226, 232, 240, 0.8); color: #0f172a; outline: none; font-size: 0.78rem; resize: none; line-height: 1.35; font-family: inherit; transition: all 0.3s; height: 46px;" onfocus="this.style.borderColor='var(--primary)';"></textarea>
            </div>
 
            <!-- Clean, static list of stops visited (names & categories only in badge style) -->
            <div style="background: rgba(248, 250, 252, 0.8); border: 1.5px solid rgba(226, 232, 240, 0.8); border-radius: 10px; padding: 8px;">
                <h5 style="color: #475569; font-weight: 800; text-align: center; border-bottom: 1px solid rgba(226, 232, 240, 0.6); padding-bottom: 3px; margin: 0 0 5px 0; font-size: 0.68rem; letter-spacing: 0.3px; text-transform: uppercase;">Chặng dừng đã chinh phục</h5>
                <div id="completionSummaryList" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; max-height: 58px; overflow-y: auto;">
                    <!-- Dynamically populated stops badges -->
                </div>
            </div>
            
            <div style="border-top: 1px dashed rgba(226, 232, 240, 0.8); margin-top: 10px; padding-top: 8px; text-align: center;">
                <span style="font-size: 0.6rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">Thời gian lưu trữ: </span>
                <strong style="font-size: 0.75rem; color: var(--primary); margin-left: 2px;" id="completionDateText"></strong>
            </div>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <!-- Save Journey Diary: primary completion success action! -->
            <button onclick="saveJourneyDiary()" class="btn-primary" style="width: 100%; padding: 8px; border-radius: 8px; font-weight: 800; font-size: 0.8rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px; box-shadow: @if($tour->mood === 'cooking') 0 0 10px rgba(16, 185, 129, 0.25) @else 0 0 10px rgba(14, 165, 233, 0.25) @endif; background: @if($tour->mood === 'cooking') linear-gradient(135deg, #10b981 0%, #059669 100%) @else var(--primary-grad) @endif;">
                💾 Lưu Nhật Ký Chuyến Đi (Hoàn Thành)
            </button>
            <div style="display: flex; gap: 6px;">
                <button onclick="shareCompletion()" style="flex: 1.2; padding: 6px; border-radius: 8px; font-weight: 700; font-size: 0.72rem; display: flex; align-items: center; justify-content: center; gap: 3px; border: 1px solid rgba(226, 232, 240, 0.8); background: rgba(241, 245, 249, 0.8); color: #475569; cursor: pointer;">
                    <span>📸</span> Chia sẻ
                </button>
                <button onclick="exitAndGoHome()" style="flex: 1.5; padding: 6px; border-radius: 8px; font-weight: 700; font-size: 0.72rem; border: 1.5px solid rgba(226, 232, 240, 0.8); background: rgba(239, 68, 68, 0.08); color: #ef4444; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 3px;">
                    🚪 Về trang Tour
                </button>
                <button onclick="closeCompletionModal()" style="flex: 1; padding: 6px; border-radius: 8px; font-weight: 700; font-size: 0.72rem; border: 1px solid rgba(226, 232, 240, 0.8); background: rgba(241, 245, 249, 0.3); color: #64748b; cursor: pointer;">
                    Quay lại
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 14. Smart Auth Gate Glassmorphic Modal (Frosted Neon theme) -->
<div id="authGateModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 10002; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
    <div style="width: 90%; max-width: 440px; padding: 32px 28px; border-radius: 28px; background: rgba(26, 26, 38, 0.7); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1.5px solid rgba(14, 165, 233, 0.25); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3), 0 0 30px rgba(14, 165, 233, 0.15); color: #ffffff; position: relative; text-align: center;">
        <button onclick="closeAuthGateModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.3rem; color: rgba(255,255,255,0.4); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">✕</button>
        
        <!-- Animated icon header -->
        <div style="margin-bottom: 24px; position: relative; display: inline-block;">
            <div style="width: 76px; height: 76px; border-radius: 50%; background: rgba(14, 165, 233, 0.15); border: 2.5px solid var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 0 20px rgba(14, 165, 233, 0.3); animation: float 3s ease-in-out infinite;">
                <span style="font-size: 2.5rem; filter: drop-shadow(0 0 8px rgba(14, 165, 233, 0.6));">✨</span>
            </div>
        </div>
        
        <span style="background: rgba(14,165,233,0.15); color: var(--primary); padding: 4px 14px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; display: inline-block; margin-bottom: 12px; border: 1px solid rgba(14,165,233,0.2);">
            Trải nghiệm trọn vẹn hơn
        </span>
        
        <h3 style="font-size: 1.35rem; font-weight: 900; color: #ffffff; margin: 0 0 10px 0; letter-spacing: -0.3px;">Lưu giữ khoảnh khắc của bạn</h3>
        
        <p style="font-size: 0.82rem; color: rgba(255, 255, 255, 0.7); line-height: 1.55; margin: 0 0 26px 0; padding: 0 10px;">
            Bạn ơi! Hãy <strong style="color: var(--primary);">Đăng nhập hoặc Đăng ký nhanh</strong> trong vài giây để Check-in các chặng dừng chân, tải ảnh Selfie cực chất và lưu lại Chứng nhận Hoàn thành Food Tour nhé!
        </p>
        
        <!-- Login and Register Actions -->
        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px;">
            <!-- Direct Register Button (Higher visibility for guest conversion) -->
            <a id="authGateRegisterBtn" href="#" style="padding: 13px; border-radius: 14px; font-weight: 800; font-size: 0.88rem; text-decoration: none; color: #ffffff; background: var(--primary-grad); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 0 20px rgba(14, 165, 233, 0.35); transition: all 0.3s;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 0 25px rgba(14, 165, 233, 0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 0 20px rgba(14, 165, 233, 0.35)';">
                🔥 Đăng ký thành viên mới (3s)
            </a>
            
            <!-- Login link -->
            <a id="authGateLoginBtn" href="#" style="padding: 12px; border-radius: 14px; font-weight: 700; font-size: 0.85rem; text-decoration: none; color: #ffffff; background: rgba(255, 255, 255, 0.08); border: 1.5px solid rgba(255, 255, 255, 0.15); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'; this.style.borderColor='rgba(255,255,255,0.3)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.borderColor='rgba(255,255,255,0.15)';">
                🔑 Bạn đã có tài khoản? Đăng nhập ngay
            </a>
        </div>
        
        <button onclick="closeAuthGateModal()" style="background: none; border: none; color: rgba(255,255,255,0.4); font-size: 0.75rem; cursor: pointer; font-weight: 600; text-decoration: underline; transition: color 0.2s;" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">
            Để sau, tiếp tục đi tour ẩn danh
        </button>
    </div>
</div>

<!-- 14. Auth Gate Modal -->
<div id="authGateModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 100050; align-items: center; justify-content: center;">
    <div style="width: 90%; max-width: 400px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 24px; padding: 28px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 12px;">🔑</div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Đăng nhập để trải nghiệm Food Tour</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 20px; line-height: 1.5;">Bạn cần đăng nhập tài khoản để bật chế độ dẫn đường GPS và lưu nhật ký check-in hành trình nhé!</p>
        <div style="display: flex; gap: 10px;">
            <a id="authGateLoginBtn" href="/login" class="btn-primary" style="flex: 1; padding: 10px; border-radius: 12px; font-weight: 700; text-decoration: none; display: inline-block; text-align: center;">Đăng nhập</a>
            <button onclick="closeAuthGateModal()" class="btn-secondary" style="flex: 1; padding: 10px; border-radius: 12px; font-weight: 700; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; cursor: pointer;">Để sau</button>
        </div>
    </div>
</div>

<!-- 15. Custom Alert Pop-up Modal (Glassmorphic Spring Card) -->
<div id="customAlertModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 100010; align-items: center; justify-content: center;">
    <div class="spring-modal-card" style="width: 85%; max-width: 380px; padding: 30px 26px; border-radius: 28px; text-align: center; position: relative;">
        
        <div style="width: 68px; height: 68px; border-radius: 50%; background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%); border: 2px solid rgba(14, 165, 233, 0.3); display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto; box-shadow: 0 0 20px rgba(14, 165, 233, 0.25); animation: pulseRing 2s infinite;">
            <span id="customAlertIcon" style="font-size: 2.2rem; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.15));">🎉</span>
        </div>
        
        <h3 id="customAlertTitle" style="font-size: 1.3rem; font-weight: 900; color: #0f172a; margin: 0 0 10px 0; letter-spacing: -0.3px;">Thông báo</h3>
        
        <p id="customAlertMessage" style="font-size: 0.88rem; color: #475569; line-height: 1.6; margin: 0 0 26px 0; white-space: pre-line; font-weight: 500;">
            Nội dung thông báo ở đây.
        </p>
        
        <button id="customAlertBtn" onclick="closeCustomAlert()" class="btn-primary" style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 32px; border-radius: 50px; font-weight: 800; font-size: 0.92rem; border: none; cursor: pointer; background: var(--primary-grad); color: #ffffff; box-shadow: 0 8px 24px rgba(14, 165, 233, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px) scale(1.03)'; this.style.boxShadow='0 12px 28px rgba(14, 165, 233, 0.55)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 8px 24px rgba(14, 165, 233, 0.4)';">
            Đồng ý
        </button>
    </div>
</div>

<!-- 16. Community Diaries Modal -->
@if(isset($diaries) && count($diaries) > 0)
<div id="communityDiariesModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 10005; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
    <div style="width: 90%; max-width: 520px; max-height: 85vh; display: flex; flex-direction: column; padding: 26px 22px; border-radius: 24px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); color: #0f172a; position: relative;">
        
        <button onclick="closeCommunityDiariesModal()" style="position: absolute; top: 18px; right: 18px; background: #f1f5f9; border: 1px solid #e2e8f0; width: 34px; height: 34px; border-radius: 50%; font-size: 1rem; display: flex; align-items: center; justify-content: center; color: #475569; cursor: pointer; transition: all 0.2s; z-index: 10;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#475569';">✕</button>
        
        <h3 style="font-weight: 800; color: #0f172a; font-size: 1.3rem; margin: 0 0 16px 0; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
            📖 Nhật ký Cộng đồng
            <span style="font-size: 0.78rem; background: #e0f2fe; border: 1px solid #bae6fd; color: #0284c7; padding: 3px 12px; border-radius: 20px; font-weight: 800;">{{ count($diaries) }} đánh giá</span>
        </h3>
        
        <!-- scrollable content -->
        <div style="flex: 1; overflow-y: auto; padding-right: 8px; display: flex; flex-direction: column; gap: 24px;">
            @foreach($diaries as $diary)
                <div style="padding: 22px; border-radius: 20px; background: #ffffff; border: 1.5px solid #cbd5e1; border-top: 4px solid #0ea5e9; box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06); position: relative;">
                    
                    <!-- Header Banner for each separate diary -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 0.72rem; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #ffffff; padding: 4px 10px; border-radius: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 6px rgba(14, 165, 233, 0.25);">
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
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.88rem; font-weight: 800; text-transform: uppercase; box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3); flex-shrink: 0;">
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
                    <div style="margin: 0 0 16px 0; font-size: 0.88rem; color: #1e293b; line-height: 1.55; background: #f8fafc; padding: 12px 16px; border-radius: 12px; border-left: 4px solid #0ea5e9; border-top: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
                        <span style="font-size: 1.1rem; color: #0ea5e9; margin-right: 4px;">💬</span> <em>"{{ $diary->comment }}"</em>
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
                                        $stopEatery = $tour->stops[$stopIdx]->eatery ?? null;
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
@endsection
