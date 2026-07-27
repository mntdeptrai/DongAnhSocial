@extends('layouts.food-tour')

@section('title', 'Hành trình Ẩm thực Đông Anh - Khám phá Cinematic Du lịch Số')

@section('styles')
<style>
    /* Cinematic design token additions */
    .food-tours-hero {
        position: relative;
        padding: 90px 0 60px;
        background: radial-gradient(circle at 50% 30%, rgba(255, 126, 41, 0.12) 0%, rgba(192, 38, 211, 0.04) 40%, rgba(9, 9, 11, 0) 70%), var(--bg-base);
        overflow: hidden;
        border-bottom: 1px solid rgba(255, 126, 41, 0.08);
        text-align: center;
    }
    [data-theme="light"] .food-tours-hero {
        background: radial-gradient(circle at 50% 30%, rgba(255, 126, 41, 0.08) 0%, rgba(192, 38, 211, 0.02) 40%, rgba(248, 250, 252, 0) 70%), var(--bg-base);
        border-bottom-color: rgba(255, 126, 41, 0.06);
    }
    
    .cinematic-glow-orb-1 {
        position: absolute;
        top: -150px;
        left: 10%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(255, 126, 41, 0.15) 0%, rgba(255, 126, 41, 0) 70%);
        filter: blur(80px);
        pointer-events: none;
        animation: floatOrb1 15s ease-in-out infinite alternate;
        z-index: 0;
    }
    .cinematic-glow-orb-2 {
        position: absolute;
        bottom: -150px;
        right: 5%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(192, 38, 211, 0.1) 0%, rgba(192, 38, 211, 0) 70%);
        filter: blur(90px);
        pointer-events: none;
        animation: floatOrb2 20s ease-in-out infinite alternate;
        z-index: 0;
    }
    
    @keyframes floatOrb1 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(60px, 40px) scale(1.15); }
    }
    @keyframes floatOrb2 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(-50px, -60px) scale(0.95); }
    }
    
    .particles-container {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 1;
    }
    .particle {
        position: absolute;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.8) 0%, rgba(255, 126, 41, 0.4) 60%, rgba(255, 126, 41, 0) 100%);
        border-radius: 50%;
        pointer-events: none;
        opacity: 0.6;
        animation: floatParticle 8s infinite ease-in-out;
    }
    .p-1 { left: 15%; top: 20%; width: 5px; height: 5px; animation-duration: 9s; }
    .p-2 { left: 85%; top: 15%; width: 7px; height: 7px; animation-duration: 14s; animation-delay: 1.5s; }
    .p-3 { left: 50%; top: 80%; width: 4px; height: 4px; animation-duration: 10s; animation-delay: 3s; }
    .p-4 { left: 30%; top: 55%; width: 6px; height: 6px; animation-duration: 11s; animation-delay: 0.8s; }
    .p-5 { left: 72%; top: 72%; width: 5px; height: 5px; animation-duration: 8s; animation-delay: 2.2s; }
    .p-6 { left: 92%; top: 45%; width: 6px; height: 6px; animation-duration: 13s; animation-delay: 4.5s; }
    
    @keyframes floatParticle {
        0%, 100% { transform: translateY(0) translateX(0) scale(1); opacity: 0.15; }
        50% { transform: translateY(-50px) translateX(25px) scale(1.3); opacity: 0.7; }
    }
    
    .hero-slogan {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 126, 41, 0.06);
        border: 1px solid rgba(255, 126, 41, 0.25);
        padding: 8px 20px;
        border-radius: 30px;
        margin-bottom: 24px;
        box-shadow: 0 0 25px rgba(14, 165, 233, 0.04);
        position: relative;
        z-index: 2;
    }
    [data-theme="light"] .hero-slogan {
        background: rgba(14, 165, 233, 0.04);
        border-color: rgba(14, 165, 233, 0.2);
    }
    .slogan-badge {
        color: #0ea5e9;
        font-weight: 800;
        font-size: 0.88rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-family: var(--font-heading);
    }
    .slogan-sub {
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 600;
        border-left: 1px solid rgba(255, 255, 255, 0.15);
        padding-left: 10px;
    }
    [data-theme="light"] .slogan-sub {
        border-left-color: rgba(15, 23, 42, 0.12);
        color: #475569;
    }
    
    .cinematic-title {
        font-family: var(--font-heading);
        font-size: 3.4rem;
        font-weight: 900;
        letter-spacing: -1px;
        line-height: 1.15;
        position: relative;
        z-index: 2;
    }
    .cinematic-title-line1 {
        display: block;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 4px 12px rgba(14,165,233,0.2)) drop-shadow(0 0 8px rgba(6,182,212,0.1));
        font-weight: 900;
    }
    .gradient-text {
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 45%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 2px 12px rgba(14,165,233,0.15));
        margin-top: 4px;
    }
    
    .btn-cinematic-ai {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #10b981 100%);
        color: #fff;
        border: none;
        padding: 16px 36px;
        border-radius: 50px;
        font-size: 1.05rem;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 8px 30px rgba(14, 165, 233, 0.4), 0 0 20px rgba(6, 182, 212, 0.2);
        letter-spacing: 0.3px;
        transition: all 0.4s var(--ease-premium);
        font-family: var(--font-heading);
        position: relative;
        z-index: 10;
        outline: none;
    }
    .btn-cinematic-ai:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 35px rgba(255, 126, 41, 0.5), 0 0 30px rgba(236, 72, 153, 0.35);
    }
    .btn-cinematic-ai:active {
        transform: translateY(-1px) scale(0.99);
    }
    .btn-cinematic-ai.active {
        background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%);
        border: 1.5px solid rgba(255, 126, 41, 0.4);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4), inset 0 0 15px rgba(255, 126, 41, 0.15);
    }
    
    .mood-selector-wrapper {
        background: rgba(255, 255, 255, 0.02) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        padding: 6px !important;
        border-radius: 40px !important;
        display: inline-flex !important;
        gap: 6px !important;
        margin-bottom: 40px !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
    }
    [data-theme="light"] .mood-selector-wrapper {
        background: rgba(255, 255, 255, 0.6) !important;
        border-color: rgba(15, 23, 42, 0.06) !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04) !important;
    }
    .mood-pill {
        background: transparent !important;
        border: 1px solid transparent !important;
        padding: 10px 24px !important;
        font-family: var(--font-heading) !important;
        font-size: 0.9rem !important;
        font-weight: 700 !important;
        transition: all 0.3s var(--ease-premium) !important;
    }
    .mood-pill:hover {
        background: rgba(255, 126, 41, 0.06) !important;
        border-color: rgba(255, 126, 41, 0.15) !important;
        color: var(--primary) !important;
        transform: translateY(-1px) !important;
    }
    .mood-pill.active {
        background: var(--primary-grad, var(--primary)) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: var(--shadow-btn) !important;
    }
    
    .ai-planner-card {
        background: rgba(18, 18, 24, 0.8) !important;
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1.5px solid rgba(255, 126, 41, 0.3) !important;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5), 0 0 30px rgba(255, 126, 41, 0.12) !important;
    }
    [data-theme="light"] .ai-planner-card {
        background: rgba(255, 255, 255, 0.85) !important;
        border: 1.5px solid rgba(255, 126, 41, 0.2) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06), 0 0 20px rgba(255, 126, 41, 0.04) !important;
    }
    
    .tour-card {
        background: rgba(18, 18, 24, 0.5) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
    }
    [data-theme="light"] .tour-card {
        background: rgba(255, 255, 255, 0.7) !important;
        border-color: rgba(15, 23, 42, 0.06) !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03) !important;
    }
    .tour-card:hover {
        border-color: rgba(255, 126, 41, 0.35) !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 25px rgba(255, 126, 41, 0.15) !important;
    }
    [data-theme="light"] .tour-card:hover {
        border-color: rgba(255, 126, 41, 0.35) !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.08), 0 0 20px rgba(255, 126, 41, 0.1) !important;
    }
</style>
@endsection

@section('content')
<div x-data="{ 
    aiPlannerOpen: false, 
    activeMood: '{{ $mood ?: '' }}',
    aiBudget: '350000',
    aiMood: 'chill',
    aiGroupSize: 'couple',
    aiTimeOfDay: 'afternoon',
    aiLoading: false,
    submitPlanner() {
        this.aiLoading = true;
        fetch('/api/food-tours/generate-ai', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                budget: this.aiBudget, 
                mood: this.aiMood, 
                group_size: this.aiGroupSize, 
                time_of_day: this.aiTimeOfDay 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `/food-tour/${data.slug}`;
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                    this.aiLoading = false;
                }
            })
            .catch(err => {
                alert('Lỗi kết nối máy chủ!');
                this.aiLoading = false;
            });
        }
    }">
    
    <!-- Cinematic Hero Banner Section -->
    <section class="food-tours-hero">
        <!-- Glowing atmosphere orbs -->
        <div class="cinematic-glow-orb-1"></div>
        <div class="cinematic-glow-orb-2"></div>
        
        <!-- Magical sparkles particles -->
        <div class="particles-container">
            <div class="particle p-1"></div>
            <div class="particle p-2"></div>
            <div class="particle p-3"></div>
            <div class="particle p-4"></div>
            <div class="particle p-5"></div>
            <div class="particle p-6"></div>
        </div>
        
        <div class="container" style="max-width: 960px; position: relative; z-index: 2;">
            <!-- Immersive Page Header with reveal animations -->
            <div class="reveal reveal-fade-up revealed" style="text-align: center;">
                <div class="hero-slogan">
                    <span class="slogan-badge">✨ DongAnh Discovery</span>
                    <span class="slogan-sub">Đi là mê, chạm là thích</span>
                </div>
                
                <h1 class="cinematic-title">
                    <span class="text-reveal" style="display: block;">
                        <span class="cinematic-title-line1">Hành trình Trải nghiệm</span>
                    </span>
                    <span class="text-reveal gradient-text" style="display: block; animation-delay: 200ms;">Ẩm thực Đông Anh</span>
                </h1>
                
                <p style="font-size: 1.05rem; font-family: 'Be Vietnam Pro', 'Inter', sans-serif; color: var(--text-muted); line-height: 1.7; max-width: 720px; margin: 20px auto 32px; font-weight: 500;">
                    Không chỉ là ăn uống, đây là hành trình văn hóa, lịch sử và khám phá thực tế được thiết kế tinh tế. Chọn lộ trình phù hợp với tâm trạng hoặc sử dụng trí tuệ nhân tạo AI để tự thiết kế riêng cho bạn!
                </p>

                <!-- PRIMARY CTA BUTTONS -->
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-bottom: 20px;">
                    <button @click="aiPlannerOpen = !aiPlannerOpen" 
                            class="btn-cinematic-ai"
                            :class="aiPlannerOpen ? 'active' : ''"
                            :style="{ animation: aiPlannerOpen ? 'none' : 'ctaPulse 2.5s ease-in-out infinite' }">
                        <span style="font-size: 1.4rem;">🪄</span>
                        <span>Thiết kế Lộ trình bằng AI ngay!</span>
                        <span style="font-size: 0.9rem; transition: transform 0.4s var(--ease-premium);" 
                              :style="{ transform: aiPlannerOpen ? 'rotate(180deg)' : 'rotate(0deg)' }">&#9660;</span>
                    </button>

                    <a href="/food-tours/create" 
                       class="btn-cinematic-ai" 
                       style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); box-shadow: 0 8px 30px rgba(249, 115, 22, 0.4); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; animation: none;">
                        <span style="font-size: 1.4rem;">🗺️</span>
                        <span>Tự xây dựng Lộ trình</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Page Content Section -->
    <div style="background: var(--bg-base); min-height: 50vh; padding: 60px 0;">
        <div class="container">
            
            @if(session('success'))
                <div class="glass-card" style="background: rgba(16, 185, 129, 0.1); border: 1.5px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 16px; border-radius: 16px; margin-bottom: 25px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

        <!-- COLLAPSIBLE AI PLANNER PANEL (Alpine.js transition) -->
        <div x-show="aiPlannerOpen" 
             x-transition:enter="transition ease-out duration-350 transform"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             style="display: none; margin-bottom: 30px;">
            <div class="glass-card ai-planner-card" style="border-radius: 24px; padding: 40px; position: relative; overflow: hidden; border: 1.5px solid rgba(255,126,41,0.3);">
                <div style="position: absolute; right: -30px; top: -30px; font-size: 9rem; opacity: 0.05; pointer-events: none; transform: rotate(-15deg);">🪄</div>
                <div style="position: absolute; left: -20px; bottom: -20px; font-size: 7rem; opacity: 0.04; pointer-events: none; transform: rotate(20deg);">&#10024;</div>

                <form @submit.prevent="submitPlanner()" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; max-width: 640px; margin: 0 auto;" :style="aiLoading ? 'opacity: 0.3; pointer-events: none;' : ''">
                    <div class="ai-field-group">
                        <label class="ai-field-label">💰 Ngân sách dự chi</label>
                        <select x-model="aiBudget" class="ai-select">
                            <option value="100000">Dưới 100k — Siêu tiết kiệm</option>
                            <option value="200000">100k – 200k — Bình dân</option>
                            <option value="350000">200k – 350k — Phổ thông</option>
                            <option value="600000">350k – 600k — Thoải mái</option>
                            <option value="1000000">Trên 600k — Sang chảnh</option>
                        </select>
                    </div>
                    <div class="ai-field-group">
                        <label class="ai-field-label">🎭 Tâm trạng hôm nay</label>
                        <select x-model="aiMood" class="ai-select">
                            <option value="chill">☕ Chill nhẹ nhàng</option>
                            <option value="night">🌙 Ăn đêm sôi động</option>
                            <option value="cheap">💰 Ngon rẻ vỉa hè</option>
                            <option value="specialty">🌾 Khám phá đặc sản</option>
                            <option value="romantic">💕 Hẹn hò lãng mạn</option>
                            <option value="family">👨‍👩‍👧 Gia đình sum vầy</option>
                        </select>
                    </div>
                    <div class="ai-field-group">
                        <label class="ai-field-label">👥 Bạn đi cùng ai?</label>
                        <select x-model="aiGroupSize" class="ai-select">
                            <option value="solo">🧍 Một mình (Solo trip)</option>
                            <option value="couple">👫 2 người (Cặp đôi)</option>
                            <option value="small_group">👥 3 – 4 người (Nhóm nhỏ)</option>
                            <option value="large_group">🎉 5+ người (Nhóm lớn)</option>
                        </select>
                    </div>
                    <div class="ai-field-group">
                        <label class="ai-field-label">⏰ Thời điểm khởi hành</label>
                        <select x-model="aiTimeOfDay" class="ai-select">
                            <option value="morning">🌅 Buổi sáng (7h – 11h)</option>
                            <option value="noon">☀️ Buổi trưa (11h – 14h)</option>
                            <option value="afternoon">🌤️ Buổi chiều (14h – 18h)</option>
                            <option value="evening">🌆 Tối sớm (18h – 21h)</option>
                            <option value="late_night">🌙 Đêm khuya (21h+)</option>
                        </select>
                    </div>
                    <div style="grid-column: span 2; margin-top: 4px;">
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 16px; border-radius: 16px; font-weight: 800; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 1rem; box-shadow: var(--shadow-glow); transition: all 0.3s ease;">
                            <span style="font-size: 1.2rem;">🪄</span>
                            <span>Thiết kế Lộ trình AI ngay cho tôi!</span>
                        </button>
                    </div>
                </form>

                <div x-show="aiLoading" style="display: none; margin-top: 30px; text-align: center;">
                    <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 16px;">
                        <div style="width: 52px; height: 52px; border: 4px solid rgba(255, 126, 41, 0.15); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.9s linear infinite;"></div>
                        <div>
                            <p style="font-size: 0.9rem; color: var(--primary); font-weight: 800; margin: 0;">Gemini AI đang sáng tác lộ trình riêng cho bạn...</p>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 4px 0 0;">Phân tích dữ liệu · Tối ưu tuyến đường · Viết câu chuyện ẩm thực</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- MOOD FILTER SELECTOR -->
        <div style="text-align: center; margin-bottom: 40px;">
            <div class="mood-selector-wrapper" style="margin-bottom: 0 !important;">
                <button class="mood-pill" :class="activeMood === '' ? 'active' : ''" @click="activeMood = ''; window.history.pushState(null, '', '/food-tours')">
                    <span>🌐</span> Tất cả Lộ trình
                </button>
                <button class="mood-pill" :class="activeMood === 'specialty' ? 'active' : ''" @click="activeMood = 'specialty'; window.history.pushState(null, '', '/food-tours?mood=specialty')">
                    <span>🌾</span> Đặc sản Đông Anh
                </button>
                <button class="mood-pill" :class="activeMood === 'chill' ? 'active' : ''" @click="activeMood = 'chill'; window.history.pushState(null, '', '/food-tours?mood=chill')">
                    <span>☕</span> Chill cuối tuần
                </button>
                <button class="mood-pill" :class="activeMood === 'night' ? 'active' : ''" @click="activeMood = 'night'; window.history.pushState(null, '', '/food-tours?mood=night')">
                    <span>🌙</span> Ăn đêm Cao Lỗ
                </button>
                <button class="mood-pill" :class="activeMood === 'cheap' ? 'active' : ''" @click="activeMood = 'cheap'; window.history.pushState(null, '', '/food-tours?mood=cheap')">
                    <span>💰</span> Sinh viên giá rẻ
                </button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 40px;">
            
            <!-- SECTION 1: Mood Filters and Pre-designed Tours -->
            <div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
                    <span style="background: rgba(14,165,233,0.15); color: #0ea5e9; padding: 6px 16px; border-radius: 30px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: 1px solid rgba(14,165,233,0.25); white-space: nowrap;">
                        🌐 Lộ trình khám phá từ cộng đồng
                    </span>
                    <div style="flex: 1; height: 1px; background: linear-gradient(to right, rgba(14,165,233,0.4), transparent);"></div>
                </div>

                <!-- Tours Listing Grid Empty State -->
                <div id="no-tours-message" class="glass-card" x-show="activeMood !== '' && !document.querySelectorAll('.tour-card[data-mood=\''+activeMood+'\']').length" style="display: none; text-align: center; padding: 60px 20px; border-radius: 20px;">
                    <span style="font-size: 3rem;">🔍</span>
                    <h3 style="margin-top: 16px; font-weight: 700; color: var(--text-main);">Chưa tìm thấy lộ trình phù hợp</h3>
                    <p style="color: var(--text-muted); margin-top: 8px;">Bạn hãy thử chuyển đổi bộ lọc tâm trạng khác hoặc tự tạo tour bằng AI phía bên dưới!</p>
                </div>

                <div class="tours-grid" id="main-tours-grid">
                    @foreach($tours as $tour)
                        <div class="tour-card hover-lift reveal reveal-scale-in" x-show="activeMood === '' || activeMood === '{{ $tour->mood }}'" data-mood="{{ $tour->mood }}" style="position: relative;">
                                <!-- Tour Origin/Visibility Badges -->
                                <div style="position: absolute; top: 10px; left: 10px; z-index: 10; display: flex; flex-direction: column; gap: 4px;">
                                    @if(!$tour->user_id || ($tour->user && $tour->user->role === 'admin'))
                                    <span style="background: rgba(14, 165, 233, 0.95); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; letter-spacing: 0.5px; backdrop-filter: blur(4px); box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: inline-flex; align-items: center; gap: 4px;">
                                        🏛️ Chính thức
                                    </span>
                                    @elseif(auth()->check() && $tour->user_id == auth()->id())
                                    <span style="background: rgba(249, 115, 22, 0.95); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; letter-spacing: 0.5px; backdrop-filter: blur(4px); box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: inline-flex; align-items: center; gap: 4px;">
                                        👤 Của bạn @if(!$tour->shared_at)(🔒 Riêng tư)@else(🌐 Công khai)@endif
                                    </span>
                                    @else
                                    <span style="background: rgba(16, 185, 129, 0.95); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; letter-spacing: 0.5px; backdrop-filter: blur(4px); box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: inline-flex; align-items: center; gap: 4px;">
                                        👥 Cộng đồng ({{ $tour->user->name ?? 'Thành viên' }})
                                    </span>
                                    @endif
                                </div>

                                <div class="tour-popularity-badge">
                                    <span>⭐</span> {{ $tour->popularity }}
                                </div>
                                <div class="tour-thumbnail-wrapper hover-zoom-container">
                                    <img src="{{ $tour->thumbnail ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80' }}" class="tour-thumbnail hover-zoom-img" alt="{{ $tour->name }}">
                                    <div class="tour-card-overlay">
                                        <span class="tour-difficulty-tag">{{ $tour->difficulty }}</span>
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

                                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; justify-content: center; background: rgba(255,255,255,0.02); padding: 8px; border-radius: 10px;">
                                        <span>💰</span> Dự chi: <strong style="color: var(--primary);">{{ $tour->budget }}</strong>
                                    </div>
                                    
                                    @php
                                        $canEdit = auth()->check() && ($tour->user_id == auth()->id() || auth()->user()->role === 'admin');
                                    @endphp

                                    <div style="display: flex; gap: 8px;">
                                        <a href="/food-tour/{{ $tour->slug }}" class="btn-primary" style="flex: 1.5; text-align: center; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-glow); font-size: 0.85rem;">
                                            🚀 Trải nghiệm
                                        </a>
                                        @if($canEdit)
                                        <a href="/food-tour/{{ $tour->slug }}/edit" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 700; background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--border-glow); display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.85rem;">
                                            ✏️ Sửa
                                        </a>
                                        @elseif($tour->diaries_count > 0)
                                        <button type="button" onclick="openDiariesModal('{{ $tour->id }}')" class="btn-secondary" style="flex: 1; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--border-glow); display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.85rem; cursor: pointer;">
                                            📖 Nhật ký ({{ $tour->diaries_count }})
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
            </div>

            <!-- SECTION 2: Community AI Tours (Lộ trình AI từ cộng đồng) -->
            @if($communityTours->isNotEmpty())
            <div style="margin-top: 40px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                    <div style="flex: 1; height: 1px; background: linear-gradient(to right, rgba(255,126,41,0.4), transparent);"></div>
                    <span style="background: rgba(255,126,41,0.15); color: var(--primary); padding: 6px 16px; border-radius: 30px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: 1px solid rgba(255,126,41,0.25); white-space: nowrap;">
                        🔥 Lộ trình AI từ cộng đồng
                    </span>
                    <div style="flex: 1; height: 1px; background: linear-gradient(to left, rgba(255,126,41,0.4), transparent);"></div>
                </div>

                <div class="tours-grid">
                    @foreach($communityTours as $cTour)
                    @php
                        $hoursLeft = now()->diffInHours($cTour->expires_at, false);
                        $isUrgent = $hoursLeft <= 12;
                    @endphp
                    <div class="tour-card hover-lift reveal reveal-scale-in" x-show="activeMood === '' || activeMood === '{{ $cTour->mood }}'" data-mood="{{ $cTour->mood }}" style="position: relative; border: 1.5px solid rgba(255,126,41,0.2);">
                        <!-- Community badge + Countdown -->
                        <div style="position: absolute; top: 10px; left: 10px; z-index: 10; display: flex; flex-direction: column; gap: 4px;">
                            <span style="background: rgba(255,126,41,0.9); color: #fff; font-size: 0.62rem; font-weight: 800; padding: 3px 8px; border-radius: 20px; letter-spacing: 0.5px; backdrop-filter: blur(4px);">
                                🤖 AI Cộng đồng
                            </span>
                            <span style="background: {{ $isUrgent ? 'rgba(239,68,68,0.9)' : 'rgba(15,23,42,0.75)' }}; color: #fff; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; backdrop-filter: blur(4px);">
                                ⏳ Còn {{ $hoursLeft }}h
                            </span>
                        </div>

                        <div class="tour-thumbnail-wrapper hover-zoom-container">
                            <img src="{{ $cTour->thumbnail ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80' }}" class="tour-thumbnail hover-zoom-img" alt="{{ $cTour->name }}">
                            <div class="tour-card-overlay">
                                <span class="tour-difficulty-tag">{{ $cTour->difficulty }}</span>
                            </div>
                        </div>
                        <div class="tour-card-body">
                            <h3 class="tour-card-title">{{ $cTour->name }}</h3>
                            <p class="tour-card-desc">{{ $cTour->description }}</p>

                            <div class="tour-meta-grid">
                                <div class="tour-meta-item">
                                    <span class="tour-meta-label">⏱️ Thời gian</span>
                                    <span class="tour-meta-value">{{ $cTour->duration }}</span>
                                </div>
                                <div class="tour-meta-item">
                                    <span class="tour-meta-label">📍 Điểm dừng</span>
                                    <span class="tour-meta-value">{{ $cTour->stops->count() }} địa điểm</span>
                                </div>
                                <div class="tour-meta-item">
                                    <span class="tour-meta-label">💰 Ngân sách</span>
                                    <span class="tour-meta-value">{{ $cTour->budget }}</span>
                                </div>
                            </div>

                            @if($cTour->diaries_count > 0)
                            <div style="display: flex; gap: 8px;">
                                <a href="/food-tour/{{ $cTour->slug }}" class="btn-primary" style="flex: 1.5; text-align: center; text-decoration: none; padding: 11px; border-radius: 12px; font-weight: 700; display: block; box-shadow: var(--shadow-glow); font-size: 0.88rem; display: flex; align-items: center; justify-content: center;">
                                    🚀 Thử lộ trình này
                                </a>
                                <button type="button" onclick="openDiariesModal('{{ $cTour->id }}')" class="btn-secondary" style="flex: 1; text-align: center; padding: 11px; border-radius: 12px; font-weight: 700; background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--border-glow); display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.8rem; cursor: pointer;">
                                    📖 Nhật ký ({{ $cTour->diaries_count }})
                                </button>
                            </div>
                            @else
                            <a href="/food-tour/{{ $cTour->slug }}" class="btn-primary" style="text-align: center; text-decoration: none; width: 100%; padding: 11px; border-radius: 12px; font-weight: 700; display: block; box-shadow: var(--shadow-glow); font-size: 0.88rem;">
                                🚀 Thử lộ trình AI này!
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- 📖 Diaries Modals for all tours -->
    @foreach($tours->merge($communityTours) as $tourData)
        @if($tourData->diaries_count > 0)
        <div id="communityDiariesModal-{{ $tourData->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 10005; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
            <div style="width: 90%; max-width: 650px; max-height: 85vh; display: flex; flex-direction: column; padding: 30px; border-radius: 24px; background: rgba(26, 26, 38, 0.85); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1.5px solid rgba(255, 255, 255, 0.1); box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4); color: #ffffff; position: relative;">
                
                <button onclick="closeDiariesModal('{{ $tourData->id }}')" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); width: 36px; height: 36px; border-radius: 50%; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.8); cursor: pointer; transition: all 0.2s; z-index: 10;" onmouseover="this.style.background='rgba(255,255,255,0.15)'; this.style.color='#fff';" onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.color='rgba(255,255,255,0.8)';">✕</button>
                
                <h3 style="font-weight: 800; color: #ffffff; font-size: 1.4rem; margin: 0 0 20px 0; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 16px;">
                    📖 Nhật ký Cộng đồng
                    <span style="font-size: 0.8rem; background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.3); color: #0ea5e9; padding: 4px 12px; border-radius: 20px; font-weight: 800;">{{ $tourData->diaries_count }} đánh giá</span>
                </h3>
                
                <div style="flex: 1; overflow-y: auto; padding-right: 12px; display: flex; flex-direction: column; gap: 20px;">
                    @foreach($tourData->diaries as $diary)
                        <div style="padding: 20px; border-radius: 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); transition: transform 0.2s;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-grad); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; box-shadow: 0 2px 8px rgba(14, 165, 233, 0.4);">
                                        {{ substr($diary->user ? $diary->user->name : 'TK', 0, 2) }}
                                    </div>
                                    <div>
                                        <strong style="font-size: 0.85rem; color: #ffffff; display: block;">
                                            {{ $diary->user ? $diary->user->name : 'Thực khách Food Tour' }}
                                        </strong>
                                        <span style="font-size: 0.65rem; color: rgba(255,255,255,0.5); display: block; margin-top: 1px;">
                                            📅 {{ $diary->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                                @if($diary->rating)
                                <div style="color: #ffb03a; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; gap: 3px; background: rgba(255,176,58,0.15); padding: 4px 8px; border-radius: 8px; border: 1px solid rgba(255,176,58,0.2);">
                                    <span>⭐</span><strong>{{ $diary->rating }}</strong>
                                </div>
                                @else
                                <div style="color: #10b981; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; gap: 3px; background: rgba(16, 185, 129, 0.15); padding: 4px 8px; border-radius: 8px; border: 1px solid rgba(16,185,129,0.2);">
                                    <span>✅</span><strong>Hoàn thành</strong>
                                </div>
                                @endif
                            </div>
                            
                            @if($diary->comment)
                            <p style="margin: 0 0 12px 0; font-size: 0.85rem; color: rgba(255,255,255,0.8); font-style: italic; line-height: 1.5;">
                                "{{ $diary->comment }}"
                            </p>
                            @endif
                            
                            @if($diary->image_path)
                                <div style="position: relative; height: 220px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); margin-bottom: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.25);">
                                    <img src="{{ $diary->image_path }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    <span style="position: absolute; bottom: 12px; right: 12px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); font-size: 0.7rem; color: #ffffff; padding: 6px 12px; border-radius: 20px; font-weight: 800; text-transform: uppercase;">📸 Kỷ niệm Selfie</span>
                                </div>
                            @endif

                            @if(!empty($diary->stop_reviews))
                                <div style="margin-top: 16px; border-top: 1px dashed rgba(255,255,255,0.15); padding-top: 16px;">
                                    <span style="font-size: 0.7rem; color: rgba(255,255,255,0.6); display: block; margin-bottom: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">📍 Check-in tại các chặng dừng:</span>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        @foreach($diary->stop_reviews as $stopIdx => $stopRev)
                                            @php
                                                $stopEatery = $tourData->stops[$stopIdx]->eatery ?? null;
                                            @endphp
                                            @if($stopEatery)
                                                <div style="display: flex; gap: 12px; background: rgba(255,255,255,0.02); border-radius: 12px; padding: 12px; border: 1px solid rgba(255,255,255,0.05); align-items: flex-start;">
                                                    <div style="flex: 1;">
                                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                                            <span style="font-size: 0.8rem; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 6px;">
                                                                <span style="font-size: 1rem;">{{ $stopEatery->category->icon ?: '🍜' }}</span>
                                                                {{ $stopEatery->name }}
                                                            </span>
                                                            <span style="color: #ffb03a; font-size: 0.7rem; font-weight: 700; background: rgba(255,176,58,0.1); padding: 2px 6px; border-radius: 6px; display: flex; align-items: center; gap: 2px;">
                                                                @if(!empty($stopRev['rating']))
                                                                    ⭐ {{ $stopRev['rating'] }}
                                                                @else
                                                                    ✅ Đã đến
                                                                @endif
                                                            </span>
                                                        </div>
                                                        @if(!empty($stopRev['comment']))
                                                        <p style="margin: 0; font-size: 0.8rem; color: rgba(255,255,255,0.7); font-style: italic; line-height: 1.5;">
                                                            "{{ $stopRev['comment'] }}"
                                                        </p>
                                                        @endif
                                                    </div>
                                                    
                                                    @if(!empty($stopRev['image_path']))
                                                        <div style="width: 70px; height: 70px; border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); flex-shrink: 0;">
                                                            <img src="{{ $stopRev['image_path'] }}" style="width: 100%; height: 100%; object-fit: cover;">
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
</div> <!-- Closes background div -->
</div> <!-- Closes outer x-data scope div -->
@endsection

@section('scripts')
<script>
    function openDiariesModal(tourId) {
        const modal = document.getElementById('communityDiariesModal-' + tourId);
        if (modal) modal.style.display = 'flex';
    }

    function closeDiariesModal(tourId) {
        const modal = document.getElementById('communityDiariesModal-' + tourId);
        if (modal) modal.style.display = 'none';
    }
</script>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    @keyframes pulse {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
    }
    @keyframes ctaPulse {
        0%   { box-shadow: 0 8px 32px rgba(255,126,41,0.45), 0 0 0 0 rgba(255,126,41,0.4); transform: scale(1); }
        50%  { box-shadow: 0 8px 32px rgba(255,126,41,0.6), 0 0 0 10px rgba(255,126,41,0); transform: scale(1.02); }
        100% { box-shadow: 0 8px 32px rgba(255,126,41,0.45), 0 0 0 0 rgba(255,126,41,0); transform: scale(1); }
    }

    /* AI Planner Form Styles */
    .ai-field-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .ai-field-label {
        font-size: 0.78rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .ai-select {
        width: 100%;
        padding: 13px 14px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.04);
        border: 1.5px solid rgba(255, 126, 41, 0.2);
        color: var(--text-main);
        font-weight: 600;
        font-size: 0.88rem;
        outline: none;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23ff7e29' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 38px;
        transition: border-color 0.25s, box-shadow 0.25s;
    }
    .ai-select:hover, .ai-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 126, 41, 0.12);
    }
    .ai-select option {
        background: #1a1a1a;
        color: #fff;
    }
</style>
@endsection

@section('footer')
<footer style="background: #09090b; border-top: 1px solid rgba(255, 126, 41, 0.15); padding: 50px 0 30px 0; color: var(--text-muted); font-size: 0.88rem; font-family: var(--font-body); margin-top: 60px;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div class="footer-grid" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; margin-bottom: 30px;">
            <div>
                <h3 class="logo" style="margin-bottom: 16px; font-size: 1.3rem; display: flex; align-items: center; gap: 8px; color: var(--text-main); font-family: var(--font-heading);">
                    <span>📖</span> <span style="background: var(--primary-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">DongAnh Discovery</span>
                </h3>
                <p style="line-height: 1.6; max-width: 480px;">
                    Bản đồ số Đông Anh (DongAnh Discovery) là giải pháp công nghệ số hóa toàn bộ trường học, bệnh viện, cơ sở y tế, khách sạn, nhà nghỉ, nhà hàng, quán cafe và quảng bá các sản phẩm OCOP đặc sản truyền thống của xã Đông Anh, Hà Nội. Hỗ trợ chuyển đổi số và nâng tầm văn hóa du lịch địa phương.
                </p>
            </div>
            <div>
                <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem; font-weight: 700; font-family: var(--font-heading);">Liên kết nhanh</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; padding: 0; margin: 0;">
                    <li><a href="/" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">Trang chủ</a></li>
                    <li><a href="/tim-kiem" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">Bản đồ số</a></li>
                    <li><a href="/?cat=dong-anh-market" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">Chợ & Đặc sản Đông Anh</a></li>
                    <li><a href="/auth/login" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">Đăng nhập quản trị viên</a></li>
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

