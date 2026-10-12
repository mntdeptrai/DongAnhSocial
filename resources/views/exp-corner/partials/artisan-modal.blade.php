<div id="artisanModal" class="artisan-modal-overlay" style="display: none;">
    <div class="artisan-modal-content">
        <button type="button" class="modal-close-btn" onclick="closeArtisanModal()">✕</button>
        
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span style="font-size: 1.4rem;">🏛️</span>
            <span id="modal-tag-badge" style="font-size: 0.75rem; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 4px 12px; border-radius: 20px; font-weight: 800; text-transform: uppercase;">
                Trải nghiệm thực tế
            </span>
        </div>

        <h3 id="modal-act-name" style="font-size: 1.3rem; font-weight: 900; margin: 0 0 6px 0; color: var(--text-main); font-family: var(--font-heading); line-height: 1.3;">
            Tên hoạt động trải nghiệm
        </h3>
        <p id="modal-location-name" style="font-size: 0.86rem; color: #64748b; margin: 0 0 20px 0; font-weight: 600;">
            📍 Địa điểm tổ chức
        </p>

        {{-- Thẻ gọi trực tiếp & Chat Zalo --}}
        <div class="modal-contact-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;">
            <a id="modal-tel-link" href="tel:02439652973" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px; border-radius: 14px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; text-decoration: none; font-weight: 800; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                <span>📞</span> Gọi Hotline
            </a>
            <a id="modal-zalo-link" href="https://zalo.me/02439652973" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px; border-radius: 14px; background: #0068ff; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(0, 104, 255, 0.25);">
                <span>💬</span> Chat Zalo
            </a>
        </div>

        {{-- Thông tin lưu ý bản xứ --}}
        <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 16px; padding: 16px; margin-bottom: 24px; font-size: 0.82rem; line-height: 1.6; color: var(--text-muted);">
            <div style="font-weight: 800; color: #10b981; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                <span>📌</span> Lời khuyên trước khi đến:
            </div>
            <div>• Khung giờ đón khách lý tưởng: <strong>08:30 - 11:00</strong> & <strong>14:00 - 17:00</strong> hằng ngày.</div>
            <div>• Nên liên hệ trước ít nhất 2 giờ đối với đoàn đông để nghệ nhân chuẩn bị nguyên liệu tươi.</div>
            <div>• Trang phục: Gọn gàng, thoáng mát, nên mang theo nón lá hoặc mũ che nắng.</div>
        </div>

        {{-- Form gửi yêu cầu đặt lịch hẹn trước --}}
        <div>
            <h4 style="font-size: 0.95rem; font-weight: 800; margin: 0 0 12px 0; color: var(--text-main); font-family: var(--font-heading);">
                📝 Đăng ký Giữ chỗ / Hẹn lịch trước
            </h4>
            <form id="booking-form" onsubmit="handleBookingSubmit(event)">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <input type="text" id="book-name" required placeholder="Họ và tên của bạn *" style="width: 100%; padding: 11px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; background: var(--bg-card); color: var(--text-main);">
                    <div class="modal-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <input type="tel" id="book-phone" required placeholder="Số điện thoại *" style="width: 100%; padding: 11px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; background: var(--bg-card); color: var(--text-main);">
                        <input type="number" id="book-count" min="1" value="2" placeholder="Số lượng khách" style="width: 100%; padding: 11px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; background: var(--bg-card); color: var(--text-main);">
                    </div>
                    <input type="date" id="book-date" style="width: 100%; padding: 11px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; background: var(--bg-card); color: var(--text-main);">
                    <button type="submit" style="width: 100%; padding: 13px; border-radius: 14px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: none; font-weight: 800; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
                        🚀 Gửi yêu cầu đặt lịch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
