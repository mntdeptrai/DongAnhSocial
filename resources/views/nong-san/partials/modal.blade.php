<div id="nsDetailModal" class="ns-modal-backdrop" style="display: none;" onclick="handleModalBackdropClick(event)" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="ns-modal-sheet" onclick="event.stopPropagation()">
        <button type="button" class="ns-close-x-btn" onclick="closeNsDetailModal()" aria-label="Đóng cửa sổ">✕</button>

        {{-- 2-Column Split Grid --}}
        <div class="ns-modal-split-grid">

            {{-- Left Column: Visual Showcase & Direct Producer Contact --}}
            <div class="ns-modal-col-left">
                <div class="ns-modal-media-wrapper" onclick="handleModalCoverClick()" title="Bấm để phóng to xem chi tiết">
                    <img id="modal-cover-img" src="" alt="Ảnh sản phẩm">
                    <span id="modal-badge" class="ns-modal-badge-float">Đặc sản</span>
                    <span id="modal-zoom-hint" class="ns-modal-zoom-hint">
                        <span>🔍</span> Phóng to ảnh
                    </span>
                </div>

                {{-- Interactive Media Switcher for Products with Dual Images (e.g., Mâm Cỗ) --}}
                <div id="modal-gallery-strip" class="ns-modal-gallery-strip" style="display: none;">
                    <button type="button" id="modal-thumb-btn-1" class="ns-mamco-tab-btn active" onclick="switchModalMedia(1)">
                        <span>📜</span> Bản Thực Đơn Cổ
                    </button>
                    <button type="button" id="modal-thumb-btn-2" class="ns-mamco-tab-btn" onclick="switchModalMedia(2)">
                        <span>🔘</span> Sơ Đồ Mâm Đồng
                    </button>
                </div>

                <div class="ns-modal-left-details">
                    <div class="ns-modal-origin-box">
                        <span class="ns-origin-pin" aria-hidden="true">📍</span>
                        <span id="modal-origin">Đông Anh, Hà Nội</span>
                    </div>

                    <div id="modal-experience-tip" class="ns-modal-tip-box">
                        💡 Thưởng thức đúng điệu để cảm nhận trọn vẹn hương vị bản địa.
                    </div>

                    <div class="ns-modal-guarantee-box">
                        <span class="ns-guarantee-icon" aria-hidden="true">🏛️</span>
                        <div>
                            <strong>Cam kết chuẩn gốc:</strong> Sản vật thuộc danh mục Nông sản & Đặc sản truyền thống Đông Anh. Kết nối trực tiếp với nghệ nhân và cơ sở sản xuất bản địa.
                        </div>
                    </div>

                    <div class="ns-modal-direct-actions">
                        <a id="modal-call-link" href="tel:0983882119" class="ns-btn-direct-phone">
                            <span>📞</span> Gọi hotline tư vấn
                        </a>
                        <a id="modal-zalo-link" href="#" target="_blank" rel="noopener noreferrer" class="ns-btn-direct-zalo">
                            <span>💬</span> Chat Zalo nhận báo giá
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column: Editorial Details & Fast Inquiry Callback Form --}}
            <div class="ns-modal-col-right">
                
                {{-- Title & Category Heading --}}
                <div class="ns-modal-heading-group">
                    <span class="ns-modal-cat-tag">
                        <span>🌾</span> Tinh Hoa Nông Sản & Ẩm Thực Đông Anh
                    </span>
                    <h3 id="modal-title" class="ns-modal-product-title">Tên sản phẩm</h3>
                </div>

                {{-- Price & Unit Highlight Banner --}}
                <div class="ns-price-highlight-banner">
                    <div class="ns-price-meta-col">
                        <span class="ns-banner-price-lbl">🏷️ Mức giá tham khảo</span>
                        <strong id="modal-price" class="ns-banner-price-val">Liên hệ</strong>
                        <span class="ns-banner-price-sub">(Báo giá theo số lượng sỉ/lẻ & mùa vụ)</span>
                    </div>
                    <div class="ns-unit-meta-col">
                        <span class="ns-banner-unit-lbl">📦 Quy cách đóng gói</span>
                        <span id="modal-unit" class="ns-banner-unit-val">Đang cập nhật</span>
                    </div>
                </div>

                {{-- Royal Parchment Menu Breakdown for Mâm Cỗ --}}
                <div id="modal-menu-sections-wrap" style="display: none;">
                    <div id="modal-menu-sections-body"></div>
                </div>

                {{-- Heritage Story Quote & Narrative --}}
                <div class="ns-modal-section-story">
                    <h4 class="ns-modal-story-title">
                        <span>📖</span> Điển tích & Câu chuyện văn hóa
                    </h4>
                    <p id="modal-story" class="ns-modal-story-p">
                        Nội dung câu chuyện sản phẩm
                    </p>
                </div>

                {{-- Highlights & Quality Commitments --}}
                <div id="modal-highlights-wrap" class="ns-modal-section-highlights">
                    <h4 class="ns-modal-story-title">
                        <span>✨</span> Tinh hoa & Cam kết chất lượng
                    </h4>
                    <ul id="modal-highlights" class="ns-modal-points">
                        {{-- Populated dynamically via JS --}}
                    </ul>
                </div>

                {{-- Fast Inquiry & Callback Form --}}
                <div class="ns-order-glass-box">
                    <div class="ns-order-form-header">
                        <h4 class="ns-order-form-title">
                            <span>📝</span> Đặt mua & Nhận báo giá trực tiếp
                        </h4>
                        <p class="ns-order-form-sub">
                            Để lại thông tin bên dưới, cơ sở sản xuất sẽ liên hệ lại ngay trong 15 phút để tư vấn ưu đãi và xác nhận đơn hàng!
                        </p>
                    </div>

                    <form id="ns-inquiry-form" onsubmit="handleInquirySubmit(event)">
                        <input type="hidden" id="form-product-name" name="product_name">

                        <div class="ns-form-grid-2">
                            <div class="ns-form-group">
                                <label for="form-customer-name" class="ns-form-label">Họ và tên của bạn <span class="ns-req">*</span></label>
                                <input type="text" id="form-customer-name" name="customer_name" required placeholder="Ví dụ: Nguyễn Văn A" class="ns-form-input">
                            </div>
                            <div class="ns-form-group">
                                <label for="form-phone" class="ns-form-label">Số điện thoại liên hệ <span class="ns-req">*</span></label>
                                <input type="tel" id="form-phone" name="phone" required placeholder="Ví dụ: 0912.345.678" class="ns-form-input">
                            </div>
                        </div>

                        <div class="ns-form-grid-2" style="margin-bottom: 16px;">
                            <div class="ns-form-group">
                                <label for="form-quantity" class="ns-form-label">Số lượng dự kiến</label>
                                <input type="text" id="form-quantity" name="quantity" placeholder="VD: 2 hộp, 5kg, 1 mâm cỗ..." class="ns-form-input">
                            </div>
                            <div class="ns-form-group">
                                <label for="form-note" class="ns-form-label">Ghi chú (Địa chỉ, ngày nhận...)</label>
                                <input type="text" id="form-note" name="note" placeholder="VD: Giao tại Hà Nội chiều nay..." class="ns-form-input">
                            </div>
                        </div>

                        <button type="submit" id="btn-submit-inquiry" class="ns-btn-submit-order">
                            <span>🚀</span> Gửi Yêu Cầu Đặt Mua & Báo Giá
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
