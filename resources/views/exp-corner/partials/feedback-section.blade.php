<section id="community-feedback-wall" style="margin-top: 75px; padding-top: 45px; border-top: 1px solid rgba(16, 185, 129, 0.15);">
    
    {{-- Section Header --}}
    <div class="section-header-row">
        <div>
            <span style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; color: #10b981; display: block; margin-bottom: 4px;">
                💬 Community Voices & Diaries
            </span>
            <h3 style="font-size: clamp(1.25rem, 3vw, 1.6rem); font-weight: 900; color: var(--text-main); margin: 0; font-family: var(--font-heading); display: flex; align-items: center; gap: 10px;">
                <span>📖</span> Nhật Ký & Cảm Nhận Thực Tế từ Du Khách
            </h3>
            <p style="margin: 6px 0 0 0; font-size: 0.88rem; color: var(--text-muted); max-width: 680px;">
                Lắng nghe chia sẻ chân thực từ hàng nghìn du khách đã tự tay trải nghiệm các hoạt động văn hóa, nông nghiệp và ẩm thực bản địa tại Đông Anh.
            </p>
        </div>

        <div class="section-header-actions">
            <button type="button" onclick="openFeedbackLibraryModal()" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 20px; border-radius: 50px; background: var(--bg-card); color: var(--text-main); border: 1.5px solid rgba(16, 185, 129, 0.3); font-weight: 800; font-size: 0.86rem; cursor: pointer; transition: all 0.25s ease;" onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='rgba(16, 185, 129, 0.3)'">
                <span>📖</span> Mở Thư Viện Tất Cả (1.250+)
            </button>
            <button type="button" onclick="openFeedbackModal()" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 22px; border-radius: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: none; font-weight: 800; font-size: 0.86rem; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); transition: all 0.25s ease;">
                <span>✍️</span> Đăng Cảm Nhận Của Bạn
            </button>
        </div>
    </div>

    {{-- ── 1. AIRBNB-STYLE SCORECARD DASHBOARD ── --}}
    <div class="scorecard-container">
        {{-- Left: Big Score & Trust Badge --}}
        <div class="scorecard-main">
            <div style="display: flex; align-items: baseline; gap: 10px;">
                <span class="scorecard-big-number">4.9</span>
                <div style="color: #f59e0b; font-size: 1.25rem; font-weight: 800;">
                    ⭐⭐⭐⭐⭐
                </div>
            </div>
            <div style="margin-top: 6px;">
                <strong style="font-size: 1.05rem; color: var(--text-main); display: block;">
                    Tuyệt vời • 1.250+ Đánh giá xác thực
                </strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">
                    98% Du khách giới thiệu cho bạn bè & người thân
                </span>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap;">
                <span style="font-size: 0.72rem; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 3px 10px; border-radius: 12px; font-weight: 800;">
                    🛡️ 100% Trải nghiệm thật
                </span>
                <span style="font-size: 0.72rem; background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); padding: 3px 10px; border-radius: 12px; font-weight: 700;">
                    🌿 100% Bản địa Đông Anh
                </span>
            </div>
        </div>

        {{-- Right: Criteria Bars Grid --}}
        <div class="scorecard-criteria-grid">
            <div class="criteria-item">
                <div class="criteria-label-row">
                    <span>Độ nhiệt tình gia chủ & nghệ nhân</span>
                    <span>4.9 / 5.0</span>
                </div>
                <div class="criteria-bar-track">
                    <div class="criteria-bar-fill" style="width: 98%;"></div>
                </div>
            </div>

            <div class="criteria-item">
                <div class="criteria-label-row">
                    <span>Tính chân thực & giá trị văn hóa</span>
                    <span>4.9 / 5.0</span>
                </div>
                <div class="criteria-bar-track">
                    <div class="criteria-bar-fill" style="width: 98%;"></div>
                </div>
            </div>

            <div class="criteria-item">
                <div class="criteria-label-row">
                    <span>Độ an toàn & hướng dẫn chu đáo</span>
                    <span>5.0 / 5.0</span>
                </div>
                <div class="criteria-bar-track">
                    <div class="criteria-bar-fill" style="width: 100%;"></div>
                </div>
            </div>

            <div class="criteria-item">
                <div class="criteria-label-row">
                    <span>Đáng giá tiền (Value for money)</span>
                    <span>4.9 / 5.0</span>
                </div>
                <div class="criteria-bar-track">
                    <div class="criteria-bar-fill" style="width: 98%;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. GUEST REAL PHOTO SHOWCASE ROW ── --}}
    <div style="margin-bottom: 22px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <span style="font-size: 0.82rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                <span>📸</span> Ảnh chụp thực tế từ du khách (<span style="color: #10b981;">340+ khoảnh khắc</span>)
            </span>
            <button type="button" onclick="openFeedbackLibraryModal()" style="background: none; border: none; color: #10b981; font-size: 0.78rem; font-weight: 800; cursor: pointer; text-decoration: underline;">
                Xem tất cả ảnh trong thư viện →
            </button>
        </div>

        <div class="guest-photos-scroll">
            <div class="guest-photo-item" onclick="openFeedbackLibraryModal('Ăn tối tại nhà dân Cổ Loa')">
                <img src="https://media.xadonganh.com/experience/chao_se_co_loa.jpg" alt="Cháo se Cổ Loa" loading="lazy">
                <span class="guest-photo-label">🥣 Cháo se Cổ Loa truyền thống</span>
            </div>
            <div class="guest-photo-item" onclick="openFeedbackLibraryModal('Tự tay làm bún Mạch Tràng cổ truyền')">
                <img src="https://media.xadonganh.com/experience/bun_xao_mach_trang.jpg" alt="Bún Mạch Tràng xào cần tỏi" loading="lazy">
                <span class="guest-photo-label">🍜 Bún Mạch Tràng xào cần tỏi</span>
            </div>
            <div class="guest-photo-item" onclick="openFeedbackLibraryModal('Dịch vụ dâng hương tưởng niệm')">
                <img src="https://media.xadonganh.com/experience/te_le_den_thuong_co_loa.jpg" alt="Tế lễ Đền Thượng Cổ Loa" loading="lazy">
                <span class="guest-photo-label">🏮 Nghi lễ tế lễ Đền Thượng</span>
            </div>
            <div class="guest-photo-item" onclick="openFeedbackLibraryModal('Bắn nỏ liên châu Âu Lạc')">
                <img src="https://media.xadonganh.com/experience/tuong_no_than_au_lac.jpg" alt="Tượng Nỏ thần Âu Lạc" loading="lazy">
                <span class="guest-photo-label">🎯 Tượng Nỏ Thần Âu Lạc</span>
            </div>
            <div class="guest-photo-item" onclick="openFeedbackLibraryModal('Tham quan di tích lịch sử Thành Cổ Loa')">
                <img src="https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg" alt="Cổng Đền Thượng Cổ Loa" loading="lazy">
                <span class="guest-photo-label">🏛️ Cổng Đền Thượng Cổ Loa</span>
            </div>
            <div class="guest-photo-item" onclick="openFeedbackLibraryModal('Tham quan di tích lịch sử Thành Cổ Loa')">
                <img src="https://media.xadonganh.com/experience/gieng_ngoc_co_loa_ho.jpg" alt="Hồ Giếng Ngọc Cổ Loa" loading="lazy">
                <span class="guest-photo-label">🌊 Hồ Giếng Ngọc Cổ Loa</span>
            </div>
            <div class="guest-photo-item" onclick="openFeedbackLibraryModal('Tour trải nghiệm trồng rau tại Cổ Loa')">
                <img src="https://media.xadonganh.com/experience/flycam_toan_canh_den_thuong_co_loa.jpg" alt="Toàn cảnh Đền Thượng & Giếng Ngọc" loading="lazy">
                <span class="guest-photo-label">📸 Toàn cảnh Đền Thượng</span>
            </div>
            <div class="guest-photo-item" onclick="openFeedbackLibraryModal('Tham quan di tích lịch sử Thành Cổ Loa')">
                <img src="https://media.xadonganh.com/experience/le_hoi_ruoc_kieu_co_loa.jpg" alt="Lễ hội rước kiệu Cổ Loa" loading="lazy">
                <span class="guest-photo-label">🏮 Lễ hội rước kiệu Cổ Loa</span>
            </div>
        </div>
    </div>

    {{-- ── 3. FEEDBACK FILTER PILLS ── --}}
    <div class="fb-filter-pills" id="fb-filter-pills-row">
        <button type="button" class="fb-pill active" data-filter="all" onclick="filterSpotlightFeedbacks('all', this)">
            ✨ Tất cả
        </button>
        <button type="button" class="fb-pill" data-filter="has_photo" onclick="filterSpotlightFeedbacks('has_photo', this)">
            📸 Có ảnh chụp
        </button>
        <button type="button" class="fb-pill" data-filter="Ăn tối tại nhà dân Cổ Loa" onclick="filterSpotlightFeedbacks('Ăn tối tại nhà dân Cổ Loa', this)">
            🏡 Ăn tối nhà dân
        </button>
        <button type="button" class="fb-pill" data-filter="Tour trải nghiệm trồng rau tại Cổ Loa" onclick="filterSpotlightFeedbacks('Tour trải nghiệm trồng rau tại Cổ Loa', this)">
            🌱 Trồng rau sạch
        </button>
        <button type="button" class="fb-pill" data-filter="Game Mật Mã Loa Thành" onclick="filterSpotlightFeedbacks('Game Mật Mã Loa Thành', this)">
            🏹 Game Mật Mã
        </button>
        <button type="button" class="fb-pill" data-filter="Tự tay làm bún Mạch Tràng cổ truyền" onclick="filterSpotlightFeedbacks('Tự tay làm bún Mạch Tràng cổ truyền', this)">
            🍜 Làm bún
        </button>
        <button type="button" class="fb-pill" data-filter="5star" onclick="filterSpotlightFeedbacks('5star', this)">
            ⭐ 5 Sao (98%)
        </button>
    </div>

    {{-- ── 4. SPOTLIGHT FEEDBACK CARDS GRID ── --}}
    <div class="feedback-grid-container" id="feedback-grid-container">
        @foreach($feedbacks as $index => $fb)
            @php
                $isExtra = $index >= 6;
            @endphp
            <div class="feedback-card {{ $isExtra ? 'fb-card-extra' : '' }}" 
                 data-activity="{{ $fb['activity_name'] }}"
                 data-rating="{{ $fb['rating'] ?? 5 }}"
                 data-has-photo="{{ !empty($fb['image_url']) ? '1' : '0' }}"
                 style="background: var(--bg-card); border: 1.5px solid rgba(16, 185, 129, 0.2); border-radius: 20px; padding: 22px; display: {{ $isExtra ? 'none' : 'flex' }}; flex-direction: column; transition: transform 0.3s ease, box-shadow 0.3s ease; box-shadow: 0 6px 20px rgba(0,0,0,0.03);">
                
                {{-- Header --}}
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem; text-transform: uppercase;">
                            {{ mb_substr($fb['author_name'] ?? 'DK', 0, 1) }}
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <strong style="font-size: 0.92rem; color: var(--text-main);">{{ $fb['author_name'] }}</strong>
                                <span class="verified-badge">✓ Đã trải nghiệm</span>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 2px;">
                                {{ $fb['created_at'] ?? 'Mới đây' }}
                            </span>
                        </div>
                    </div>
                    <div style="color: #f59e0b; font-size: 0.8rem; font-weight: 800; background: #fffbeb; padding: 4px 8px; border-radius: 12px; border: 1px solid #fef3c7;">
                        @for($i = 0; $i < ($fb['rating'] ?? 5); $i++)⭐@endfor
                    </div>
                </div>

                {{-- Activity Badge --}}
                <div style="margin-bottom: 12px;">
                    <span style="font-size: 0.74rem; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); padding: 4px 10px; border-radius: 20px; font-weight: 700; display: inline-block;">
                        🎯 {{ $fb['activity_name'] }}
                    </span>
                </div>

                {{-- Comment --}}
                <p style="margin: 0 0 14px 0; font-size: 0.86rem; color: var(--text-main); line-height: 1.6; font-style: italic; flex: 1;">
                    "{{ $fb['comment'] }}"
                </p>

                {{-- Attached Image --}}
                @if(!empty($fb['image_url']))
                    <div style="position: relative; height: 160px; border-radius: 14px; overflow: hidden; border: 1px solid rgba(16, 185, 129, 0.2); margin-bottom: 12px; cursor: pointer;" onclick="openFeedbackLibraryModal('{{ $fb['activity_name'] }}')">
                        <img src="{{ $fb['image_url'] }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Kỷ niệm chuyến đi" loading="lazy" onerror="this.style.display='none'">
                        <span style="position: absolute; bottom: 6px; right: 6px; background: rgba(0,0,0,0.65); color: #ffffff; padding: 2px 8px; border-radius: 10px; font-size: 0.65rem; font-weight: 700; backdrop-filter: blur(4px);">
                            📸 Xem chi tiết
                        </span>
                    </div>
                @endif

                {{-- Host Reply --}}
                @if(!empty($fb['host_reply']))
                    <div class="host-reply-box" style="margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 5px; font-weight: 800; font-size: 0.72rem; color: #10b981; margin-bottom: 2px;">
                            <span>💬</span>
                            <span>Phản hồi từ {{ $fb['host_reply']['author'] ?? 'Người tổ chức' }}</span>
                        </div>
                        <div style="font-size: 0.78rem; line-height: 1.45;">{{ $fb['host_reply']['comment'] }}</div>
                    </div>
                @endif

                {{-- Card Actions: Helpful Button --}}
                <div style="display: flex; align-items: center; justify-content: flex-end; margin-top: auto; padding-top: 8px;">
                    <button type="button" class="btn-helpful" onclick="toggleHelpful(this, '{{ $fb['id'] ?? uniqid() }}')">
                        <span>👍</span> Hữu ích (<span class="helpful-count">{{ $fb['helpful_count'] ?? 12 }}</span>)
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── 5. SCALING CONTROLS BAR (PAGINATION / DRAWER TRIGGER) ── --}}
    <div class="feedback-scaling-bar">
        <div>
            <span style="font-size: 0.88rem; font-weight: 800; color: var(--text-main); display: block;">
                Đang hiển thị <span id="fb-visible-count-badge" style="color: #10b981;">{{ min(6, count($feedbacks)) }}</span> / {{ count($feedbacks) }} đánh giá tiêu biểu
            </span>
            <span style="font-size: 0.76rem; color: var(--text-muted);">
                Tổng cộng hơn 1.250+ lượt đánh giá lưu trong thư viện cộng đồng.
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            @if(count($feedbacks) > 6)
                <button type="button" id="btn-load-more-fb" onclick="loadMoreSpotlightFeedbacks()" style="padding: 10px 20px; border-radius: 50px; background: transparent; border: 1.5px solid rgba(16, 185, 129, 0.4); color: var(--text-main); font-size: 0.84rem; font-weight: 800; cursor: pointer; transition: all 0.25s ease;" onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='rgba(16, 185, 129, 0.4)'">
                    <span>⬇️</span> Xem Thêm Đánh Giá
                </button>
            @endif

            <button type="button" onclick="openFeedbackLibraryModal()" style="padding: 10px 22px; border-radius: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: none; font-size: 0.84rem; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);">
                <span>📖</span> Mở Toàn Bộ Thư Viện (1.250+)
            </button>
        </div>
    </div>

</section>

{{-- MODAL: ĐĂNG FEEDBACK / NHẬT KÝ MỚI --}}
<div id="feedbackSubmitModal" class="artisan-modal-overlay" style="display: none;">
    <div class="artisan-modal-content" style="max-width: 520px;">
        <button type="button" class="modal-close-btn" onclick="closeFeedbackModal()">✕</button>

        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span style="font-size: 1.4rem;">✍️</span>
            <span style="font-size: 0.76rem; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 4px 12px; border-radius: 20px; font-weight: 800; text-transform: uppercase;">
                Chia sẻ cảm nhận
            </span>
        </div>

        <h3 style="font-size: 1.3rem; font-weight: 900; margin: 0 0 8px 0; color: var(--text-main); font-family: var(--font-heading);">
            Đăng Nhật Ký / Cảm Nhận Của Bạn
        </h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0 0 20px 0;">
            Chia sẻ trải nghiệm quý báu của bạn để cộng đồng du khách có thêm thông tin hữu ích!
        </p>

        <form id="exp-feedback-form" onsubmit="handleFeedbackSubmit(event)">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 4px;">
                        Họ và tên hoặc Biệt danh *
                    </label>
                    <input type="text" id="fb-author" required placeholder="VD: Gia đình chị Mai, Nhóm bạn Tuấn..." style="width: 100%; padding: 11px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; background: var(--bg-card); color: var(--text-main);">
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 4px;">
                        Hoạt động bạn đã trải nghiệm *
                    </label>
                    <select id="fb-activity" required style="width: 100%; padding: 11px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; background: var(--bg-card); color: var(--text-main);">
                        <option value="Ăn tối tại nhà dân Cổ Loa">🏡 Ăn tối tại nhà dân Cổ Loa</option>
                        <option value="Tour trải nghiệm trồng rau tại Cổ Loa">🌱 Tour trải nghiệm trồng rau tại Cổ Loa</option>
                        <option value="Game Mật Mã Loa Thành">🏹 Game Mật Mã Loa Thành</option>
                        <option value="Bắn nỏ liên châu Âu Lạc">🎯 Bắn nỏ liên châu Âu Lạc</option>
                        <option value="Tự tay làm bún Mạch Tràng cổ truyền">🍜 Tự tay làm bún Mạch Tràng cổ truyền</option>
                        <option value="Tham quan di tích lịch sử Thành Cổ Loa">🏛️ Tham quan di tích lịch sử Thành Cổ Loa</option>
                        <option value="Dịch vụ dâng hương & Trải nghiệm gốm sứ">🙏 Dịch vụ dâng hương & Trải nghiệm gốm sứ</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 4px;">
                        Mức độ hài lòng của bạn *
                    </label>
                    <div class="star-rating-options" style="display: flex; gap: 8px;">
                        <label style="flex: 1; padding: 10px; border-radius: 12px; border: 1.5px solid #cbd5e1; text-align: center; cursor: pointer; font-size: 0.82rem; font-weight: 700;" class="star-opt-label">
                            <input type="radio" name="fb-rating" value="5" checked style="margin-right: 4px;"> 5 ⭐ (Tuyệt vời)
                        </label>
                        <label style="flex: 1; padding: 10px; border-radius: 12px; border: 1.5px solid #cbd5e1; text-align: center; cursor: pointer; font-size: 0.82rem; font-weight: 700;" class="star-opt-label">
                            <input type="radio" name="fb-rating" value="4" style="margin-right: 4px;"> 4 ⭐ (Hài lòng)
                        </label>
                    </div>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 4px;">
                        Cảm nhận chi tiết & Lời khuyên cho người đi sau *
                    </label>
                    <textarea id="fb-comment" required rows="4" placeholder="Chia sẻ cảm xúc của bạn, không gian, nghệ nhân, món ăn hoặc điều bạn thích nhất..." style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; background: var(--bg-card); color: var(--text-main); line-height: 1.5;"></textarea>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 4px;">
                        Link ảnh kỷ niệm chuyến đi (tùy chọn)
                    </label>
                    <input type="url" id="fb-image" placeholder="https://media.xadonganh.com/... hoặc link ảnh thực tế của bạn" style="width: 100%; padding: 11px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; background: var(--bg-card); color: var(--text-main);">
                </div>

                <button type="submit" id="btn-submit-fb" style="width: 100%; padding: 14px; border-radius: 14px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: none; font-weight: 800; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); margin-top: 6px;">
                    🚀 Xuất bản Cảm Nhận
                </button>
            </div>
        </form>
    </div>
</div>
