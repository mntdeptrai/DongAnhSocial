<div id="nsDetailModal" class="ns-modal-backdrop" style="display: none;" onclick="handleModalBackdropClick(event)">
    <div class="ns-modal-sheet">
        <button type="button" class="ns-close-x-btn" onclick="closeNsDetailModal()" aria-label="Đóng">✕</button>

        {{-- 2-Column Split Grid --}}
        <div class="ns-modal-split-grid">

            {{-- Left Column: Visual Showcase & Direct Contact --}}
            <div class="ns-modal-col-left">
                <div class="ns-modal-media-wrapper" onclick="handleModalCoverClick()" style="cursor: pointer;" title="Bấm để phóng to xem chi tiết">
                    <img id="modal-cover-img" src="" alt="Ảnh sản phẩm">
                    <div class="ns-modal-media-shade"></div>
                    <span id="modal-badge" class="ns-modal-badge-float">Đặc sản</span>
                    <span id="modal-zoom-hint" style="position: absolute; bottom: 12px; right: 14px; background: rgba(6,40,30,0.85); padding: 4px 10px; border-radius: 50px; color: #fef08a; font-size: 0.72rem; font-weight: 800; border: 1px solid rgba(254,240,138,0.25);">🔍 Phóng to</span>
                </div>

                {{-- Image Switcher Strip for Products with multiple menu images (like Mâm cỗ) --}}
                <div id="modal-gallery-strip" style="display: none; padding: 12px 18px 0; gap: 8px; align-items: center;">
                    <button type="button" id="modal-thumb-btn-1" class="ns-mamco-tab-btn active" style="padding: 7px 14px; font-size: 0.78rem;" onclick="switchModalMedia(1)">
                        <span>📜</span> Bản Thực Đơn Cổ
                    </button>
                    <button type="button" id="modal-thumb-btn-2" class="ns-mamco-tab-btn" style="padding: 7px 14px; font-size: 0.78rem;" onclick="switchModalMedia(2)">
                        <span>🔘</span> Sơ Đồ Mâm Đồng
                    </button>
                </div>

                <div class="ns-modal-left-details">
                    <div class="ns-modal-origin-box">
                        <span>📍</span>
                        <span id="modal-origin">Đông Anh, Hà Nội</span>
                    </div>

                    <div id="modal-experience-tip" class="ns-modal-tip-box">
                        💡 Thưởng thức đúng điệu để cảm nhận trọn vẹn hương vị bản địa.
                    </div>

                    <div style="font-size: 0.78rem; color: rgba(255, 255, 255, 0.75); line-height: 1.5; background: rgba(0,0,0,0.25); padding: 10px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
                        🏛️ <strong>Cam kết chuẩn gốc:</strong> Sản phẩm thuộc danh mục Nông sản & Đặc sản truyền thống Đông Anh. Được kết nối trực tiếp với nghệ nhân và cơ sở sản xuất bản địa.
                    </div>

                    <div class="ns-modal-direct-actions">
                        <a id="modal-call-link" href="tel:0983882119" class="ns-btn-direct-phone">
                            <span>📞</span> Gọi hotline tư vấn
                        </a>
                        <a id="modal-zalo-link" href="#" target="_blank" class="ns-btn-direct-zalo">
                            <span>💬</span> Chat Zalo nhận báo giá
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column: Editorial Details & Fast Pre-Order Form --}}
            <div class="ns-modal-col-right">
                
                {{-- Title & Category Heading --}}
                <div class="ns-modal-heading-group">
                    <span style="font-size: 0.75rem; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.6px; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 4px;">
                        <span>🌾</span> Tinh Hoa Nông Sản & Ẩm Thực Đông Anh
                    </span>
                    <h3 id="modal-title" class="ns-modal-product-title">Tên sản phẩm</h3>
                </div>

                {{-- Price & Unit Highlight Banner --}}
                <div class="ns-price-highlight-banner">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.5px; display: block;">🏷️ Mức giá tham khảo</span>
                        <strong id="modal-price" style="font-size: 1.45rem; color: #047857; font-weight: 900; letter-spacing: -0.3px; white-space: nowrap;">Liên hệ</strong>
                        <span style="font-size: 0.76rem; color: var(--ns-text-muted); display: block; margin-top: 2px;">(Báo giá theo số lượng sỉ/lẻ & mùa vụ)</span>
                    </div>
                    <div style="text-align: right; max-width: 50%;">
                        <span style="font-size: 0.72rem; font-weight: 800; color: var(--ns-text-muted); text-transform: uppercase; display: block;">📦 Quy cách</span>
                        <span id="modal-unit" style="font-size: 0.92rem; font-weight: 800; color: var(--ns-text-dark); display: block; word-break: break-word;">Đang cập nhật</span>
                    </div>
                </div>

                {{-- 4-Group Menu Sections for Mâm Cỗ (Dynamic Royal Parchment) --}}
                <div id="modal-menu-sections-wrap" style="display: none;">
                    <div id="modal-menu-sections-body">
                    </div>
                </div>

                {{-- Heritage Story (Fixed typo: Điển tích & Câu chuyện văn hóa) --}}
                <div>
                    <h4 class="ns-modal-story-title">
                        <span>📖</span> Điển tích & Câu chuyện văn hóa
                    </h4>
                    <p id="modal-story" class="ns-modal-story-p">
                        Nội dung câu chuyện sản phẩm
                    </p>
                </div>

                {{-- Highlights & Quality Commitments --}}
                <div id="modal-highlights-wrap">
                    <h4 class="ns-modal-story-title">
                        <span>✨</span> Tinh hoa & Cam kết chất lượng
                    </h4>
                    <ul id="modal-highlights" class="ns-modal-points">
                        {{-- Dynamically populated via JS --}}
                    </ul>
                </div>

                {{-- Fast Inquiry & Callback Form --}}
                <div class="ns-order-glass-box">
                    <h4 style="font-size: 0.95rem; font-weight: 900; color: #059669; margin: 0 0 6px; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading, 'Be Vietnam Pro', sans-serif);">
                        <span>📝</span> Đặt mua & Nhận báo giá trực tiếp
                    </h4>
                    <p style="font-size: 0.82rem; color: var(--ns-text-muted); margin: 0 0 14px; line-height: 1.5;">
                        Để lại thông tin bên dưới, cơ sở sản xuất sẽ liên hệ lại ngay để báo giá ưu đãi và chuẩn bị giao hàng cho bạn!
                    </p>

                    <form id="ns-inquiry-form" onsubmit="handleInquirySubmit(event)">
                        <input type="hidden" id="form-product-name" name="product_name">

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                            <div>
                                <label class="ns-form-label">Họ và tên của bạn *</label>
                                <input type="text" id="form-customer-name" name="customer_name" required placeholder="Nguyễn Văn A" class="ns-form-input">
                            </div>
                            <div>
                                <label class="ns-form-label">Số điện thoại *</label>
                                <input type="tel" id="form-phone" name="phone" required placeholder="0912.345.678" class="ns-form-input">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1.4fr; gap: 10px; margin-bottom: 14px;">
                            <div>
                                <label class="ns-form-label">Số lượng dự kiến</label>
                                <input type="text" id="form-quantity" name="quantity" placeholder="VD: 2 hộp, 5kg..." class="ns-form-input">
                            </div>
                            <div>
                                <label class="ns-form-label">Ghi chú (Địa chỉ, ngày nhận...)</label>
                                <input type="text" id="form-note" name="note" placeholder="VD: Nhận tại Đông Anh chiều nay..." class="ns-form-input">
                            </div>
                        </div>

                        <button type="submit" id="btn-submit-inquiry" class="ns-btn-quick-inquire" style="width: 100%; padding: 13px; font-size: 0.92rem;">
                            <span>🚀</span> Gửi Yêu Cầu Đặt Mua & Báo Giá
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
