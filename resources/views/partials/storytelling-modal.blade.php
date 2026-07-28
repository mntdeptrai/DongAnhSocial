{{-- ==========================================================================
     MAP-BASED STORYTELLING OVERLAY MODAL
     Government Digital Modern | Apple Glassmorphism | Interactive Presentation
     ========================================================================== --}}

<div id="storytellingModal" class="story-overlay-modal" role="dialog" aria-hidden="true">
    <!-- Sparkle Canvas Overlay -->
    <canvas id="storySparkleCanvas"></canvas>
    
    <!-- 1. PHASE 0: INTRO SCREEN -->
    <div id="storyIntroScreen" class="story-intro-screen hidden">
        <div class="story-intro-card">
            <div class="story-intro-badge">
                <span>🎓</span> BẢN ĐỒ SỐ GIÁO DỤC XÃ ĐÔNG ANH
            </div>
            <div class="story-intro-divider"></div>
            <h1 id="storyIntroTitle" class="story-intro-title">
                <span style="white-space: nowrap; display: inline-block;">HÀNH TRÌNH HÌNH THÀNH</span><br><span class="story-intro-highlight">TRƯỜNG MẦM NON PHÚC LỘC</span>
            </h1>
            <p id="storyIntroSubtitle" class="story-intro-subtitle">
                Hội nghị công bố việc sắp xếp, tổ chức lại các cơ sở giáo dục công lập xã Đông Anh
            </p>
        </div>
    </div>

    <!-- 2. TOP CONTROLS BAR -->
    <div class="story-top-bar">
        <div class="story-header-branding">
            <div class="story-header-logo">🗺️</div>
            <div class="story-header-text">
                <h1>ĐÔNG ANH MAP STORYTELLING</h1>
                <p id="storyPhaseLabel">GIAI ĐOẠN 1: KHÁI QUÁT KHU VỰC</p>
            </div>
        </div>

        <div class="story-action-buttons">
            <button class="story-btn-skip" onclick="window.storyteller.skipStory()">
                <span>⏭️</span> Bỏ qua hiệu ứng
            </button>
        </div>
    </div>

    <!-- 3. LEFT TIMELINE SIDEBAR -->
    <div class="story-timeline-sidebar">
        <div class="story-timeline-title">
            <span>📋 Tiến trình sắp xếp</span>
        </div>
        <div id="storyTimelineList" class="story-timeline-list">
            <!-- Timeline items rendered dynamically -->
        </div>
    </div>

    <!-- 4. BOTTOM NARRATIVE TYPING BOX -->
    <div class="story-narrative-box">
        <div class="story-narrative-header">
            <div class="story-narrative-dot"></div>
            <div class="story-narrative-phase">Thông điệp thuyết minh</div>
        </div>
        <div class="story-narrative-text">
            <span id="storyNarrativeText">Đang tải bản đồ số...</span>
            <span class="story-typing-cursor"></span>
        </div>
    </div>

    <!-- 5. RIGHT GLASS CARD UI -->
    <div class="story-card-container">
        <div id="storyGlassCard" class="story-glass-card">
            <div class="story-card-image-wrap">
                <img id="storyCardImage" src="" class="story-card-image" alt="School" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80';">
                <span id="storyCardBadge" class="story-card-badge">Đơn vị sáp nhập</span>
            </div>

            <h3 id="storyCardTitle" class="story-card-title">Tên Trường</h3>
            <div id="storyCardAddress" class="story-card-address">📍 Địa chỉ trường...</div>

            <div class="story-stats-grid">
                <div class="story-stat-box">
                    <div id="storyStatClasses" class="story-stat-val">0 Lớp</div>
                    <div class="story-stat-lbl">Quy mô phòng học</div>
                </div>
                <div class="story-stat-box">
                    <div id="storyStatStudents" class="story-stat-val">0 HS</div>
                    <div class="story-stat-lbl">Tổng số học sinh</div>
                </div>
            </div>

            <!-- Ban Giám Hiệu Trường Mới (Sau Sáp Nhập) -->
            <div id="storyBoardSection" class="story-board-section" style="display: none;">
                <div class="story-board-title">
                    <span>👑</span> BAN GIÁM HIỆU NHÀ TRƯỜNG
                </div>
                <div id="storyBoardGrid" class="story-board-grid">
                    <!-- Dynamic rendering by engine -->
                </div>
            </div>

            <button id="storyCardActionBtn" class="story-card-action-btn" style="display: none;" onclick="window.storyteller.skipStory()">
                <span>🔍 Tra cứu chi tiết trường mới</span> ➔
            </button>
        </div>
    </div>

    <!-- 6. FLOATING DISTANCE CARD (PHASE 4) -->
    <div id="storyDistanceBox" class="story-distance-box">
        <div class="story-distance-item">
            <h4>Khoảng cách kết nối</h4>
            <p id="storyDistVal">2.3 km</p>
        </div>
        <div class="story-distance-divider"></div>
        <div class="story-distance-item">
            <h4>Thời gian di chuyển</h4>
            <p id="storyDurationVal">6 phút</p>
        </div>
    </div>

    <!-- 7. FLASH ANNOUNCEMENT OVERLAY (PHASE 5) -->
    <div id="storyAnnouncementCard" class="story-announcement-card">
        <div class="story-announcement-icon">✨</div>
        <div class="story-announcement-date">TỪ NGÀY 01 THÁNG 08 NĂM 2026</div>
        <div class="story-announcement-title">TỔ CHỨC LẠI & THÀNH LẬP</div>
        <h2 id="storyAnnounceTitle" class="story-announcement-school-name">
            TRƯỜNG MẦM NON PHÚC LỘC
        </h2>
    </div>

    <!-- 7.5. STAGE 7 CELEBRATION FIREWORKS BANNER OVERLAY -->
    <div id="storyCelebrationBanner" class="story-celebration-banner">
        <div class="story-celebration-burst">🎆 🎇 🎉 🏫 🍾 ⭐️ 🎆</div>
        <h2 class="story-celebration-title">CHÀO MỪNG ĐƠN VỊ MỚI THÀNH LẬP!</h2>
        <p class="story-celebration-sub">Hoàn tất quy hoạch sáp nhập & đang chuyển hướng sang trang chi tiết...</p>
    </div>

    <!-- 8. BOTTOM PROGRESS INDICATOR DOTS -->
    <div class="story-progress-bar-wrap">
        <div class="story-progress-dot active"></div>
        <div class="story-progress-dot"></div>
        <div class="story-progress-dot"></div>
        <div class="story-progress-dot"></div>
        <div class="story-progress-dot"></div>
        <div class="story-progress-dot"></div>
        <div class="story-progress-dot"></div>
    </div>

    <!-- 9. MAP CONTAINER -->
    <div class="story-map-wrapper">
        <div class="story-map-dark-overlay"></div>
        <div id="storyMap"></div>
    </div>
</div>
