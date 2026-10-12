<div id="activityDetailModal" class="artisan-modal-overlay" style="display: none;">
    <div class="artisan-modal-content modal-detail-content" style="max-width: 680px; padding: 0; overflow: hidden; border-radius: 26px;">
        <button type="button" class="modal-close-btn" onclick="closeActivityDetailModal()" style="top: 16px; right: 16px; z-index: 10;">✕</button>
        
        {{-- Header Image & Badges --}}
        <div class="modal-detail-cover" style="position: relative; height: 260px; overflow: hidden; background: #0f172a;">
            <img id="detail-cover-img" src="https://media.xadonganh.com/experience/flycam_toan_canh_den_thuong_co_loa.jpg" onerror="this.src='{{ asset('images/experience/flycam_toan_canh_den_thuong_co_loa.jpg') }}'" style="width: 100%; height: 100%; object-fit: cover;" alt="Hoạt động">
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(15,23,42,0.85) 100%);"></div>
            
            <div style="position: absolute; top: 16px; left: 16px; display: flex; gap: 8px;">
                <span id="detail-badge-type" style="padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: #ffffff; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    Trải nghiệm
                </span>
                <span id="detail-badge-audience" style="padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; color: #38bdf8; background: rgba(15,23,42,0.75); border: 1px solid rgba(56,189,248,0.4);">
                    Mọi lứa tuổi
                </span>
            </div>

            <div style="position: absolute; bottom: 16px; left: 20px; right: 20px;">
                <h3 id="detail-title" style="color: #ffffff; font-family: var(--font-heading); font-size: 1.35rem; font-weight: 900; margin: 0 0 6px 0; text-shadow: 0 2px 4px rgba(0,0,0,0.8); line-height: 1.3;">
                    Tên hoạt động
                </h3>
                <div style="display: flex; align-items: center; gap: 6px; color: rgba(255,255,255,0.9); font-size: 0.85rem; font-weight: 600;">
                    <span>📍</span> <span id="detail-location">Địa điểm</span>
                </div>
            </div>
        </div>

        {{-- Body Content --}}
        <div class="modal-detail-body" style="padding: 24px 28px; max-height: calc(85vh - 260px); overflow-y: auto;">
            
            {{-- Quick Stats Grid --}}
            <div class="modal-detail-stats" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 16px; padding: 14px; margin-bottom: 22px; text-align: center;">
                <div>
                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 2px;">💰 Chi phí</span>
                    <strong id="detail-price" style="font-size: 0.95rem; color: #10b981;">Đang cập nhật</strong>
                </div>
                <div>
                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 2px;">⏱️ Thời lượng</span>
                    <strong id="detail-duration" style="font-size: 0.85rem; color: var(--text-main);">2 - 3 giờ</strong>
                </div>
                <div>
                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 2px;">🕒 Khung giờ</span>
                    <strong id="detail-best-time" style="font-size: 0.82rem; color: var(--text-main);">Hằng ngày</strong>
                </div>
            </div>

            {{-- Description --}}
            <div style="margin-bottom: 22px;">
                <h4 style="font-size: 0.92rem; font-weight: 800; color: var(--text-main); margin: 0 0 8px 0; font-family: var(--font-heading); display: flex; align-items: center; gap: 6px;">
                    <span>📖</span> Giới thiệu trải nghiệm
                </h4>
                <p id="detail-description" style="margin: 0; font-size: 0.88rem; color: var(--text-muted); line-height: 1.65;">
                    Mô tả hoạt động
                </p>
            </div>

            {{-- Highlights --}}
            <div style="margin-bottom: 22px;">
                <h4 style="font-size: 0.92rem; font-weight: 800; color: #10b981; margin: 0 0 10px 0; font-family: var(--font-heading); display: flex; align-items: center; gap: 6px;">
                    <span>✨</span> Những điều thú vị đang chờ đón bạn
                </h4>
                <div id="detail-highlights-list" style="display: flex; flex-direction: column; gap: 8px;">
                    {{-- Render dynamically --}}
                </div>
            </div>

            {{-- Timeline / Steps --}}
            <div style="margin-bottom: 22px;">
                <h4 style="font-size: 0.92rem; font-weight: 800; color: var(--text-main); margin: 0 0 12px 0; font-family: var(--font-heading); display: flex; align-items: center; gap: 6px;">
                    <span>🗺️</span> Lịch trình trải nghiệm từng bước
                </h4>
                <div id="detail-timeline-list" style="display: flex; flex-direction: column; gap: 10px; border-left: 2px solid rgba(16, 185, 129, 0.3); padding-left: 14px; margin-left: 6px;">
                    {{-- Render dynamically --}}
                </div>
            </div>

            {{-- Included & Notice --}}
            <div style="background: rgba(16, 185, 129, 0.04); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 14px; padding: 14px 18px; margin-bottom: 24px; font-size: 0.82rem; line-height: 1.6;">
                <div style="margin-bottom: 6px;">
                    <strong style="color: #10b981;">🎁 Chi phí đã bao gồm:</strong>
                    <span id="detail-included" style="color: var(--text-muted);">Đang cập nhật</span>
                </div>
                <div>
                    <strong style="color: #f59e0b;">💡 Lưu ý:</strong>
                    <span id="detail-notice" style="color: var(--text-muted);">Liên hệ trước để được chuẩn bị chu đáo nhất.</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="modal-detail-actions" style="display: flex; gap: 12px; border-top: 1px solid rgba(16, 185, 129, 0.15); padding-top: 20px;">
                <button type="button" id="btn-detail-book" onclick="triggerBookFromDetail()" style="flex: 1.5; padding: 14px; border-radius: 14px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: none; font-weight: 800; font-size: 0.92rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                    <span>📞</span> Đặt lịch / Liên hệ nghệ nhân
                </button>
                <a id="detail-zalo-link" href="#" target="_blank" style="flex: 1; padding: 14px; border-radius: 14px; background: #0068ff; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(0, 104, 255, 0.25);">
                    <span>💬</span> Chat Zalo
                </a>
            </div>

        </div>
    </div>
</div>
