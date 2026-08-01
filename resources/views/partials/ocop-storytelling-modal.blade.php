{{-- ==========================================================================
     CONCEPT "OCOP PREMIUM" OVERLAY MODAL
     Official Government & High-End Exhibition Style for Dong Anh OCOP Heritage
     Primary: #0F5E4A (Forest Green) | Secondary: #D4A017 (Gold) | Background: #F7F4EC
     ========================================================================== --}}

<div id="ocopStorytellingModal" class="story-overlay-modal ocop-theme" role="dialog" aria-hidden="true">
    <!-- Sparkle Canvas Overlay -->
    <canvas id="ocopSparkleCanvas"></canvas>

    <!-- 1. CINEMATIC FULLSCREEN INTRO OVERLAY -->
    <div id="ocopCinematicIntroOverlay" class="ocop-cinematic-intro-overlay">
        <div class="ocop-intro-content-box">
            <div class="ocop-intro-logo-wrap">
                <span class="ocop-logo-icon">🌾</span>
                <div class="ocop-intro-tag">🌾 HÀNH TRÌNH ĐIỆN ẢNH OCOP CAO CẤP</div>
            </div>
            <h1 id="ocopIntroMainTitle" class="ocop-intro-main-title">
                HÀNH TRÌNH DI SẢN<br>OCOP ĐÔNG ANH
            </h1>
            <div style="width: 140px; height: 3px; background: linear-gradient(90deg, transparent, #D4A017, transparent); margin: 18px auto;"></div>
            <p id="ocopIntroCountText" class="ocop-intro-sub-title">
                Khám phá 33 sản phẩm OCOP tiêu biểu Xã Đông Anh
            </p>
        </div>
    </div>

    <!-- 2. TOP HEADER BRANDING & SKIP BUTTON -->
    <div class="story-top-bar" style="z-index: 99990;">
        <div class="story-header-branding">
            <div class="story-header-logo" style="background: rgba(212, 160, 23, 0.2); border: 1.5px solid #D4A017; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">🌾</div>
            <div class="story-header-text">
                <h1>HÀNH TRÌNH DI SẢN OCOP ĐÔNG ANH</h1>
                <p id="ocopPhaseLabel">CHẶNG PHIM OCOP CAO CẤP</p>
            </div>
        </div>

        <div class="story-action-buttons">
            <button type="button" class="story-btn-skip" onclick="window.ocopStoryController ? window.ocopStoryController.closeAndReset() : null">
                <span>⏭️</span> Bỏ qua phim
            </button>
        </div>
    </div>

    <!-- 3. LEFT TIMELINE SIDEBAR (LIST OF PRODUCTS & INTERACTIVE CONTROLS) -->
    <div class="story-timeline-sidebar">
        <!-- Interactive Controls Bar -->
        <div class="ocop-sidebar-controls">
            <button type="button" id="ocopPrevBtn" class="ocop-ctrl-btn" onclick="window.ocopStoryController ? window.ocopStoryController.prevProduct() : null" title="Chặng trước">
                ⏮️
            </button>
            <button type="button" id="ocopPauseBtn" class="ocop-ctrl-btn" onclick="window.ocopStoryController ? window.ocopStoryController.togglePause() : null" style="flex: 1; justify-content: center;">
                ⏸️ Tạm dừng
            </button>
            <button type="button" id="ocopNextBtn" class="ocop-ctrl-btn" onclick="window.ocopStoryController ? window.ocopStoryController.nextProduct() : null" title="Chặng tiếp">
                ⏭️
            </button>
        </div>

        <!-- Progress Counter Display Pill -->
        <div id="ocopProgressCounterWrap" class="ocop-progress-counter-badge" style="margin-bottom: 12px; justify-content: center;">
            <span class="counter-label">TIẾN TRÌNH:</span>
            <div id="ocopProgressCounterText" class="counter-val">
                <span style="color: #F0C24B; font-weight: 900;">01</span> / <span style="color: #D6EED8;">33</span>
            </div>
        </div>

        <!-- Section Title -->
        <div style="font-weight: 900; font-size: 0.82rem; color: #0F5E4A; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span>📜 DANH SÁCH SẢN PHẨM</span>
        </div>

        <!-- Scrollable Product List -->
        <div id="ocopTimelineList" class="story-timeline-list">
            <!-- Dynamic 25 Items -->
        </div>
    </div>

    <!-- 4. RIGHT GLASSMORPHISM SLIDE-IN CARD (PURE WHITE GLASS, GOLD BORDER #E6D8A8) -->
    <div class="story-card-container" style="z-index: 99990;">
        <div id="ocopCinematicGlassCard" class="story-glass-card ocop-cinematic-card">
            <!-- Image Frame with 3px Gold border & Shine effect -->
            <div class="ocop-card-img-frame">
                <img id="ocopCinematicImg" src="" alt="OCOP Product" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80';">
                <span id="ocopCinematicBadge" class="ocop-card-badge-gold" style="position: absolute; top: 14px; left: 14px; z-index: 5;">⭐ OCOP 4 SAO</span>
                <div class="image-shine-overlay"></div>
            </div>

            <!-- Product Title (Forest Green #0F5E4A) -->
            <h3 id="ocopCinematicTitle" class="ocop-card-main-title">
                Tên Sản Phẩm OCOP
            </h3>
            
            <!-- Producer Name -->
            <div id="ocopCinematicProducer" class="ocop-card-producer-text">
                🏢 Đơn vị: HTX Nông nghiệp...
            </div>
            
            <!-- Address -->
            <div id="ocopCinematicAddress" class="ocop-card-address-text">
                📍 Địa chỉ: Xã Đông Anh...
            </div>

            <!-- Price & QR Box -->
            <div class="ocop-card-price-box">
                <div>
                    <span style="font-size: 0.72rem; color: #0F5E4A; text-transform: uppercase; font-weight: 800; display: block; letter-spacing: 0.5px;">Mức Giá Niêm Yết</span>
                    <strong id="ocopCinematicPrice" class="price-val">390.000đ</strong>
                </div>
                <div class="ocop-qr-frame">
                    <img id="ocopCinematicQr" src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://donganh.hanoi.gov.vn" style="width: 44px; height: 44px; display: block; border-radius: 6px;" alt="QR Code">
                </div>
            </div>

            <!-- Description Box -->
            <div id="ocopCinematicDesc" class="ocop-card-desc-box">
                Mô tả chi tiết sản phẩm OCOP Đông Anh...
            </div>

            <!-- Action Button -->
            <button type="button" class="ocop-card-btn-action" onclick="window.ocopStoryController ? window.ocopStoryController.closeAndReset() : null; if(window.location.pathname !== '/') { window.location.href='/?cat=dong-anh-market'; }">
                <span>🛍️ Đặt Mua & Xem Chi Tiết</span> ➔
            </button>
        </div>
    </div>

    <!-- 5. SLOGAN CHAIN & GRAND FINALE OVERLAY CARD -->
    <div id="ocopSloganOverlay" class="ocop-slogan-overlay-screen" style="display: none;">
        <div id="ocopSloganText" class="ocop-slogan-text-display">
            OCOP ĐÔNG ANH
        </div>

        <!-- Memory Flashback Container (Tua ký ức) -->
        <div id="ocopFlashbackContainer" class="ocop-flashback-container" style="display: none; flex-direction: column; align-items: center; justify-content: center; gap: 16px; transform: scale(0.9); opacity: 0; transition: all 0.5s ease; position: absolute; z-index: 10;">
            <div class="flashback-frame" style="width: 280px; height: 280px; border-radius: 20px; border: 4px solid #D4A017; box-shadow: 0 0 50px rgba(212, 160, 23, 0.7); overflow: hidden; position: relative;">
                <img id="ocopFlashbackImage" src="" style="width: 100%; height: 100%; object-fit: cover;" />
                <div class="flashback-glow" style="position: absolute; inset: 0; box-shadow: inset 0 0 40px rgba(15, 94, 74, 0.8);"></div>
            </div>
            <h2 id="ocopFlashbackTitle" style="color: #ffffff; font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 1.6rem; margin: 10px 0 0 0; text-align: center; text-shadow: 0 2px 10px rgba(0,0,0,0.5); max-width: 500px; line-height: 1.3;">
                Tên Sản Phẩm
            </h2>
            <div id="ocopFlashbackBadge" style="background: #D4A017; color: #173B32; font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 0.82rem; padding: 4px 14px; border-radius: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); letter-spacing: 0.5px;">
                ⭐ OCOP 4 SAO
            </div>
        </div>

        <!-- Grand Finale Stats Card -->
        <div id="ocopFinaleStatsCard" class="ocop-finale-stats-card" style="display: none;">
            <div style="font-size: 3.5rem; margin-bottom: 10px; filter: drop-shadow(0 0 15px rgba(212, 160, 23, 0.6));">✨🌾✨</div>
            <h2 class="ocop-finale-title">
                ĐÔNG ANH<br><span style="color: #D4A017;">TINH HOA NÔNG SẢN SỐ & DI SẢN OCOP</span>
            </h2>
            <div style="width: 140px; height: 3px; background: linear-gradient(90deg, transparent, #0F5E4A, transparent); margin: 16px auto 20px auto;"></div>
            <p style="font-size: 1.08rem; color: #173B32; margin-bottom: 24px; font-weight: 700; line-height: 1.6;">
                Hệ thống 100% Sản phẩm & Cơ sở OCOP Xã Đông Anh đã được số hóa trên Bản đồ Di sản số.
            </p>

            <div class="ocop-stats-row">
                <div class="stat-pill">
                    <span class="num">33</span>
                    <span class="lbl">Sản Phẩm OCOP</span>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <button type="button" class="ocop-btn-gold" style="font-size: 1.02rem; padding: 14px 28px; border-radius: 16px;" onclick="window.ocopStoryController ? window.ocopStoryController.closeAndReset() : null; if(window.location.pathname !== '/') { window.location.href='/?cat=dong-anh-market'; }">
                    🛍️ Khám Phá & Đặt Mua Ngay
                </button>
                <button type="button" class="ocop-btn-outline" style="font-size: 1.02rem; padding: 14px 28px; border-radius: 16px;" onclick="window.ocopStoryController ? window.ocopStoryController.startMovie() : null">
                    🔄 Xem Lại Trình Diễn
                </button>
            </div>
        </div>
    </div>

    <!-- 6. MAP CONTAINER -->
    <div class="story-map-wrapper">
        <div class="story-map-dark-overlay" style="background: rgba(247, 244, 236, 0.08);"></div>
        <div id="ocopStoryMap" style="width: 100%; height: 100%;"></div>
    </div>
</div>
