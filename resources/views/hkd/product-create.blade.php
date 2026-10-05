@extends('layouts.hkd')

@section('title', 'Thêm Sản Phẩm / Hàng Hóa Mới — ' . $eatery->name)
@section('title_header', 'Tạo Sản Phẩm & Hàng Hóa Kinh Doanh')

@section('content')
<style>
    .create-prod-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* Top Navigation Header */
    .create-prod-header {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    .back-btn-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none;
        background: #f1f5f9;
        padding: 8px 16px;
        border-radius: 30px;
        transition: all 0.2s ease;
    }

    .back-btn-pill:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* AI Copilot Card */
    .ai-copilot-card {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        border: 1.5px solid #a7f3d0;
        border-radius: 20px;
        padding: 18px 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.08);
    }

    .ai-copilot-title {
        font-weight: 800;
        font-size: 0.98rem;
        color: #065f46;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ai-copilot-desc {
        font-size: 0.82rem;
        color: #047857;
        margin-top: 4px;
    }

    .ai-gen-btn {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 9px 18px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .ai-gen-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
    }

    /* Main Grid Layout */
    .prod-create-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
    }

    @media (max-width: 960px) {
        .prod-create-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Cards & Panels */
    .form-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    .panel-card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 12px;
        border-bottom: 1.5px solid #f1f5f9;
    }

    /* Form Fields */
    .form-group-item {
        margin-bottom: 18px;
    }

    .form-group-item:last-child {
        margin-bottom: 0;
    }

    .form-field-label {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
        display: block;
    }

    .form-field-hint {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 500;
        margin-top: 4px;
    }

    .form-row-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* Modern Tabs Nav */
    .prod-tabs-header {
        display: flex;
        gap: 6px;
        border-bottom: 2px solid #f1f5f9;
        margin-bottom: 20px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .prod-tab-btn {
        background: transparent;
        border: none;
        padding: 10px 16px;
        font-weight: 700;
        font-size: 0.85rem;
        color: #64748b;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .prod-tab-btn.active {
        color: #059669;
        border-bottom-color: #059669;
        background: rgba(16, 185, 129, 0.05);
        border-radius: 8px 8px 0 0;
    }

    .prod-tab-btn:hover:not(.active) {
        color: #0f172a;
        background: #f8fafc;
    }

    .tab-content-panel {
        display: none;
    }

    .tab-content-panel.active {
        display: block;
    }

    /* Image Dropzone */
    .img-upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        background: #f8fafc;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 220px;
        padding: 20px;
        transition: all 0.2s ease;
        overflow: hidden;
        position: relative;
        text-align: center;
    }

    .img-upload-box:hover {
        border-color: #059669;
        background: #f0fdf4;
    }

    .img-upload-box img {
        max-width: 100%;
        max-height: 220px;
        object-fit: contain;
        display: none;
        border-radius: 12px;
    }

    /* Preset Category Chips */
    .preset-chip-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 10px;
    }

    .preset-chip-item {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .preset-chip-item:hover {
        border-color: #10b981;
        background: #f0fdf4;
        transform: translateY(-2px);
    }

    /* Price tag badge */
    .price-live-tag {
        font-size: 0.85rem;
        font-weight: 800;
        color: #059669;
        background: #d1fae5;
        padding: 2px 10px;
        border-radius: 12px;
        display: inline-block;
        margin-top: 6px;
    }

    /* Action Bar */
    .action-submit-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
</style>

<div class="create-prod-container">

    <!-- 1. HEADER NAV -->
    <div class="create-prod-header">
        <div>
            <a href="{{ route('hkd.products.index') }}" class="back-btn-pill">
                <i class="fa-solid fa-arrow-left"></i> Danh sách sản phẩm
            </a>
            <h2 style="font-size: 1.35rem; font-weight: 900; color: #0f172a; margin: 10px 0 0 0; font-family: var(--font-heading);">
                ➕ Thêm Sản Phẩm / Hàng Hóa Mới
            </h2>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.85rem;">Niêm yết thông tin hàng hóa & dịch vụ số trên Nền tảng Xã Đông Anh Digital</p>
        </div>
    </div>

    <!-- 2. SMART AI ASSISTANT BANNER -->
    <div class="ai-copilot-card">
        <div>
            <div class="ai-copilot-title">
                <span>🤖</span>
                <span>Trợ Lý Đăng Hàng Thông Minh AI</span>
            </div>
            <div class="ai-copilot-desc">
                Nhập tên sản phẩm rồi bấm nút AI để tự động tạo mô tả chuẩn, quy cách, quy đổi đơn vị và gợi ý đơn vị tính nhanh chóng!
            </div>
        </div>
        <button type="button" class="ai-gen-btn" onclick="generateAiContent()">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>Tự Động Tạo Bằng AI</span>
        </button>
    </div>

    <!-- 3. MAIN FORM -->
    <form action="{{ route('hkd.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf

        <div class="prod-create-grid">
            
            <!-- LEFT COLUMN: MAIN INPUTS & TABS -->
            <div>

                <!-- PANEL 1: CỐT LÕI SẢN PHẨM -->
                <div class="form-panel-card">
                    <div class="panel-card-title">
                        <i class="fa-solid fa-box-archive" style="color: #059669;"></i>
                        <span>1. Thông Tin Nhận Diện Sản Phẩm</span>
                    </div>

                    <div class="form-group-item">
                        <label class="form-field-label">Tên sản phẩm / hàng hóa <span style="color: #dc2626;">*</span></label>
                        <input id="prod-name" type="text" name="name" class="hkd-form-input" required 
                               placeholder="Nhập tên sản phẩm (Ví dụ: Đông trùng hạ thảo tươi KOVI, Áo sơ mi nam, Rạp cưới trọn gói...)" 
                               style="font-size: 1rem; font-weight: 600;">
                    </div>

                    <div class="form-row-2col">
                        <div class="form-group-item">
                            <label class="form-field-label">Mã sản phẩm (SKU)</label>
                            <input id="prod-sku" type="text" name="sku" class="hkd-form-input" 
                                   placeholder="Ví dụ: SP-001, KOVI-FRESH">
                            <div class="form-field-hint">Mã định danh quản lý nội bộ</div>
                        </div>

                        <div class="form-group-item">
                            <label class="form-field-label">Đơn vị tính cơ bản</label>
                            <input id="prod-unit" type="text" name="unit" list="unitOptions" 
                                   class="hkd-form-input" placeholder="Hộp, Chai, Kg, Bộ, Cái, Lần..." 
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group-item">
                        <label class="form-field-label">Phân hạng / Chứng nhận OCOP</label>
                        <select name="star_rating" class="hkd-form-input" style="cursor: pointer;">
                            <option value="">Không xếp hạng (Sản phẩm kinh doanh thông thường)</option>
                            <option value="3 sao">⭐ OCOP 3 Sao Cấp Thành Phố</option>
                            <option value="4 sao">⭐⭐ OCOP 4 Sao Cấp Thành Phố</option>
                            <option value="5 sao">⭐⭐⭐ OCOP 5 Sao Cấp Quốc Gia</option>
                        </select>
                    </div>
                </div>

                <!-- PANEL 2: TABBED INFORMATION -->
                <div class="form-panel-card">
                    <div class="prod-tabs-header">
                        <button type="button" class="prod-tab-btn active" onclick="switchTab('tab-pricing', this)">
                            <i class="fa-solid fa-tag"></i> 1. Giá & Giá Sỉ
                        </button>
                        <button type="button" class="prod-tab-btn" onclick="switchTab('tab-conversions', this)">
                            <i class="fa-solid fa-right-left"></i> 2. Quy Đổi & Thuộc Tính
                        </button>
                        <button type="button" class="prod-tab-btn" onclick="switchTab('tab-inventory', this)">
                            <i class="fa-solid fa-warehouse"></i> 3. Quản Lý Kho
                        </button>
                        <button type="button" class="prod-tab-btn" onclick="switchTab('tab-description', this)">
                            <i class="fa-solid fa-file-pen"></i> 4. Mô Tả Chi Tiết (*)
                        </button>
                        <button type="button" class="prod-tab-btn" onclick="switchTab('tab-policies', this)">
                            <i class="fa-solid fa-shield-halved"></i> 5. Cam Kết
                        </button>
                        <button type="button" class="prod-tab-btn" onclick="switchTab('tab-presets', this)">
                            <i class="fa-solid fa-layer-group"></i> 6. Mẫu Nhanh
                        </button>
                    </div>

                    <!-- TAB 1: GIÁ BÁN & GIÁ SỈ -->
                    <div id="tab-pricing" class="tab-content-panel active">
                        <div class="form-group-item">
                            <label class="form-field-label">Giá bán lẻ niêm yết (VNĐ) <span style="color: #dc2626;">*</span></label>
                            <input id="prod-price" type="number" name="price" class="hkd-form-input" required value="0"
                                   oninput="updatePriceTag('priceTagLive', this.value)" style="font-size: 1.05rem; font-weight: 700; color: #059669;">
                            <div id="priceTagLive" class="price-live-tag">0đ</div>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; margin-top: 16px;">
                            <div style="font-weight: 700; font-size: 0.9rem; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-boxes-packing" style="color: #0284c7;"></i>
                                <span>Bảng Giá Sỉ (Khi Mua Số Lượng Lớn)</span>
                            </div>
                            <div class="form-row-2col">
                                <div class="form-group-item">
                                    <label class="form-field-label">Giá sỉ theo lô (VNĐ)</label>
                                    <input type="number" name="wholesale_price" class="hkd-form-input" placeholder="0">
                                </div>
                                <div class="form-group-item">
                                    <label class="form-field-label">Số lượng tối thiểu mua sỉ</label>
                                    <input type="number" name="wholesale_qty" class="hkd-form-input" placeholder="Ví dụ: 10, 50, 100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: QUY ĐỔI & THUỘC TÍNH (NEW ACCORDING TO SCREENSHOTS) -->
                    <div id="tab-conversions" class="tab-content-panel">
                        <div class="form-group-item">
                            <label class="form-field-label">Quy đổi đơn vị (Ví dụ: 1 Thùng = 24 Chai, 1 Lốc = 6 Lon...)</label>
                            <input id="prod-conversion" type="text" name="unit_conversion" class="hkd-form-input" 
                                   placeholder="Gõ quy đổi đơn vị (Ví dụ: 1 Thùng = 20 Lốc, 1 Hộp = 10 Gói...)">
                            <div class="form-field-hint">Giúp khách hàng dễ dàng mua theo các hình thức đóng gói lớn hơn.</div>
                        </div>
                        <div class="form-group-item">
                            <label class="form-field-label">Loại hình / Danh mục kinh doanh cụ thể</label>
                            <input id="prod-type" type="text" name="product_type" list="businessCategoryList" 
                                   class="hkd-form-input" placeholder="Gõ chọn danh mục gợi ý..." autocomplete="off">
                        </div>
                    </div>

                    <!-- TAB 3: QUẢN LÝ KHO (NEW ACCORDING TO SCREENSHOTS) -->
                    <div id="tab-inventory" class="tab-content-panel">
                        <div class="form-row-2col">
                            <div class="form-group-item">
                                <label class="form-field-label">Trạng thái tồn kho</label>
                                <select name="stock_status" class="hkd-form-input" style="cursor: pointer;">
                                    <option value="in_stock">🟢 Còn hàng (Sẵn sàng bán)</option>
                                    <option value="out_of_stock">🔴 Hết hàng (Tạm ngưng nhận đơn)</option>
                                    <option value="pre_order">🟡 Đặt hàng trước (Pre-order)</option>
                                </select>
                            </div>
                            <div class="form-group-item">
                                <label class="form-field-label">Số lượng sẵn có trong kho</label>
                                <input type="number" name="stock_qty" class="hkd-form-input" placeholder="Ví dụ: 100, 500...">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: MÔ TẢ SẢN PHẨM CHI TIẾT (YÊU CẦU BẮT BUỘC) -->
                    <div id="tab-description" class="tab-content-panel">
                        <div class="form-group-item">
                            <label class="form-field-label">Nội dung mô tả sản phẩm / dịch vụ chi tiết</label>
                            <textarea id="prod-desc" name="description" rows="7" class="hkd-form-textarea"
                                      placeholder="Hãy nhập mô tả đầy đủ:&#10;1. Giới thiệu nguồn gốc & xuất xứ sản phẩm&#10;2. Thông số kỹ thuật, thành phần hoặc quy cách đóng gói&#10;3. Hướng dẫn sử dụng & bảo quản&#10;4. Phạm vi phục vụ & chính sách ưu đãi..." style="line-height: 1.6;"></textarea>
                            <div class="form-field-hint">Mô tả càng chi tiết sẽ càng giúp khách hàng tin tưởng và chốt đơn nhanh chóng!</div>
                        </div>
                    </div>

                    <!-- TAB 5: CAM KẾT & CHÍNH SÁCH -->
                    <div id="tab-policies" class="tab-content-panel">
                        <div class="form-row-2col">
                            <div class="form-group-item">
                                <label class="form-field-label">Cam kết chất lượng / Điểm nổi bật</label>
                                <input type="text" name="commitment_text" class="hkd-form-input" 
                                       placeholder="Ví dụ: Hàng chuẩn VietGAP 100%, Đổi trả trong 7 ngày...">
                            </div>
                            <div class="form-group-item">
                                <label class="form-field-label">Chính sách bàn giao & Phục vụ</label>
                                <input type="text" name="delivery_text" class="hkd-form-input" 
                                       placeholder="Ví dụ: Giao tận nơi tại Đông Anh trong 24h...">
                            </div>
                        </div>

                        <div class="form-row-2col">
                            <div class="form-group-item">
                                <label class="form-field-label">Phương thức thanh toán & Hóa đơn</label>
                                <input type="text" name="payment_policy" class="hkd-form-input" 
                                       placeholder="Ví dụ: Tiền mặt COD, VietQR, Hóa đơn VAT...">
                            </div>
                            <div class="form-group-item">
                                <label class="form-field-label">Giấy phép / Tiêu chuẩn xác minh</label>
                                <input type="text" name="certificate_info" class="hkd-form-input" 
                                       placeholder="Ví dụ: Đạt chuẩn HACCP, ISO 22000, ĐKKD chính thức...">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 6: CHỌN NHÓM MẪU NHANH -->
                    <div id="tab-presets" class="tab-content-panel">
                        <div style="font-size: 0.85rem; color: #475569; margin-bottom: 12px;">Bấm chọn nhóm hàng bên dưới để tự động điền mẫu thông tin chuẩn:</div>
                        <div class="preset-chip-list">
                            <div class="preset-chip-item" onclick="applyPreset('Nông sản tươi & Thực phẩm sạch', 'Kg', 'Được gieo trồng và thu hoạch tươi sạch tại vùng nông nghiệp sạch Xã Đông Anh. 100% tự nhiên, không chất bảo quản.', 'Nông sản, Thực phẩm & OCOP')">
                                <div style="font-size: 1.5rem;">🥦</div>
                                <div style="font-size: 0.78rem; font-weight: 700; margin-top: 4px;">Nông sản & OCOP</div>
                            </div>
                            <div class="preset-chip-item" onclick="applyPreset('Món ăn chế biến tươi nóng', 'Phần', 'Chế biến sạch sẽ hàng ngày từ nguồn thực phẩm tươi sống, phục vụ ăn tại chỗ hoặc ship nhanh tận nơi.', 'Ẩm thực, Quán ăn & Nhà hàng')">
                                <div style="font-size: 1.5rem;">🍲</div>
                                <div style="font-size: 0.78rem; font-weight: 700; margin-top: 4px;">Ẩm thực & Món ăn</div>
                            </div>
                            <div class="preset-chip-item" onclick="applyPreset('Đồ uống & Trà giải khát', 'Cốc', 'Pha chế tươi mới từ nguyên liệu tự nhiên, mang lại hương vị thơm ngon mát lành.', 'Ẩm thực, Quán ăn & Nhà hàng')">
                                <div style="font-size: 1.5rem;">🧋</div>
                                <div style="font-size: 0.78rem; font-weight: 700; margin-top: 4px;">Đồ uống & Trà</div>
                            </div>
                            <div class="preset-chip-item" onclick="applyPreset('Gói rạp cưới & Sự kiện trọn gói', 'Gói', 'Cung cấp hệ thống phông rạp, bàn ghế, sân khấu, âm thanh ánh sáng chuyên nghiệp trọn gói.', 'Sự kiện & Rạp cưới')">
                                <div style="font-size: 1.5rem;">🎪</div>
                                <div style="font-size: 0.78rem; font-weight: 700; margin-top: 4px;">Rạp cưới & Sự kiện</div>
                            </div>
                            <div class="preset-chip-item" onclick="applyPreset('Dịch vụ bảo dưỡng & Sửa chữa', 'Lần', 'Nhận kiểm tra sự cố, sửa chữa chuyên nghiệp tận nơi, bảo hành linh kiện chính hãng uy tín.', 'Dịch vụ Sửa chữa & Kỹ thuật')">
                                <div style="font-size: 1.5rem;">🔧</div>
                                <div style="font-size: 0.78rem; font-weight: 700; margin-top: 4px;">Sửa chữa & Kỹ thuật</div>
                            </div>
                            <div class="preset-chip-item" onclick="applyPreset('Vật liệu xây dựng & Vật tư', 'Bộ', 'Cung cấp nguồn hàng quy chuẩn chất lượng cao, đầy đủ chứng từ hóa đơn cho công trình.', 'Vật liệu Xây dựng & Nội thất')">
                                <div style="font-size: 1.5rem;">🧱</div>
                                <div style="font-size: 0.78rem; font-weight: 700; margin-top: 4px;">Vật liệu & Xây dựng</div>
                            </div>
                            <div class="preset-chip-item" onclick="applyPreset('Trang phục thời trang cao cấp', 'Bộ', 'Thiết kế đẹp mắt, đường may tỉ mỉ, chất liệu vải bền đẹp mang lại sự thoải mái tối đa.', 'Thời trang, May mặc & Giày dép')">
                                <div style="font-size: 1.5rem;">👗</div>
                                <div style="font-size: 0.78rem; font-weight: 700; margin-top: 4px;">Thời trang & Phụ kiện</div>
                            </div>
                            <div class="preset-chip-item" onclick="applyPreset('Thiết bị điện tử & Đồ gia dụng', 'Cái', 'Sản phẩm chính hãng fullbox, bảo hành đầy đủ tiêu chuẩn nhà sản xuất.', 'Công nghệ & Điện tử')">
                                <div style="font-size: 1.5rem;">📻</div>
                                <div style="font-size: 0.78rem; font-weight: 700; margin-top: 4px;">Điện tử & Gia dụng</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: MEDIA & ACTION BAR -->
            <div>

                <!-- UPLOAD HÌNH ẢNH SẢN PHẨM -->
                <div class="form-panel-card">
                    <div class="panel-card-title">
                        <i class="fa-solid fa-image" style="color: #0284c7;"></i>
                        <span>2. Hình Ảnh Đại Diện</span>
                    </div>

                    <div class="img-upload-box" onclick="document.getElementById('imgFileInput').click()">
                        <input type="file" id="imgFileInput" name="image" accept="image/*" style="display:none;" 
                               onchange="handleImgPreview(this)">
                        <img id="imgPreviewElem" alt="Preview">
                        <div id="imgPlaceholderBox">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: #059669; margin-bottom: 10px;"></i>
                            <div style="font-size: 0.92rem; font-weight: 700; color: #1e293b;">Tải ảnh sản phẩm</div>
                            <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">Hỗ trợ PNG, JPG, WEBP (Tối đa 5MB)</div>
                        </div>
                    </div>
                </div>

                <!-- ĐẶT LÀM SẢN PHẨM ĐẶC TRƯNG -->
                <div class="form-panel-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <label style="display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 0.9rem; color: #065f46; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="is_signature" value="1" style="width: 20px; height: 20px; accent-color: #059669; cursor: pointer;">
                        <span>★ Đặt làm Món / Sản phẩm đặc trưng của cơ sở</span>
                    </label>
                </div>

                <!-- CỐ ĐỊNH NÚT LƯU -->
                <div class="action-submit-card">
                    <button type="submit" class="hkd-btn-action hkd-btn-emerald" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: 12px; margin-bottom: 10px;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> 🚀 Lưu & Đăng Sản Phẩm
                    </button>
                    <a href="{{ route('hkd.products.index') }}" class="hkd-btn-action hkd-btn-slate" style="display: block; text-align: center; width: 100%; padding: 12px; text-decoration: none; border-radius: 12px; box-sizing: border-box;">
                        Hủy bỏ
                    </a>
                </div>

            </div>

        </div>
    </form>

</div>

{{-- Datatlists --}}
<datalist id="businessCategoryList">
    <option value="Sự kiện & Rạp cưới (Rạp phông, bàn ghế, âm thanh, ánh sáng)">
    <option value="Dịch vụ Sửa chữa & Kỹ thuật (Sửa điện, nước, điện lạnh, máy tính, xe)">
    <option value="Vật liệu Xây dựng & Nội thất (Sơn, xi măng, sắt thép, gạch, thiết bị bếp)">
    <option value="Thời trang, May mặc & Giày dép (Quần áo, phụ kiện, đồng phục)">
    <option value="Công nghệ, Máy tính & Điện tử (Điện thoại, camera, đồ gia dụng điện)">
    <option value="Ẩm thực, Quán ăn & Nhà hàng (Đồ ăn, thức uống, tiệc lưu động, ship đồ ăn)">
    <option value="Nông sản, Thực phẩm & OCOP (Rau củ quả tươi, thịt cá, đặc sản địa phương)">
    <option value="Mỹ phẩm, Làm đẹp & Spa (Cắt tóc, làm nail, spa thẩm mỹ, mỹ phẩm)">
</datalist>

<datalist id="unitOptions">
    <option value="Cái">
    <option value="Hộp">
    <option value="Kg">
    <option value="Bộ">
    <option value="Lần">
    <option value="Suất">
    <option value="Phần">
    <option value="Chai">
    <option value="Cốc">
</datalist>

<script>
    function updatePriceTag(tagId, val) {
        const tag = document.getElementById(tagId);
        if (!tag) return;
        const num = parseInt(val) || 0;
        tag.textContent = num.toLocaleString('vi-VN') + 'đ';
    }

    function switchTab(tabId, btnElem) {
        document.querySelectorAll('.prod-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));
        
        btnElem.classList.add('active');
        const target = document.getElementById(tabId);
        if (target) target.classList.add('active');
    }

    function handleImgPreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imgPreviewElem');
                img.src = e.target.result;
                img.style.display = 'block';
                document.getElementById('imgPlaceholderBox').style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function applyPreset(name, unit, desc, productType) {
        document.getElementById('prod-name').value = name;
        document.getElementById('prod-unit').value = unit;
        document.getElementById('prod-desc').value = desc;
        if (productType) {
            document.getElementById('prod-type').value = productType;
        }
        // Switch to description tab so user sees the description
        const descTabBtn = document.querySelectorAll('.prod-tab-btn')[3];
        if (descTabBtn) switchTab('tab-description', descTabBtn);
    }

    function generateAiContent() {
        const nameInput = document.getElementById('prod-name').value.trim();
        if (!nameInput) {
            alert('Vui lòng nhập Tên sản phẩm trước để AI hỗ trợ sinh mô tả!');
            document.getElementById('prod-name').focus();
            return;
        }

        let descText = `Sản phẩm ${nameInput} cao cấp, được sản xuất và phân phối chính hãng tại Xã Đông Anh, TP Hà Nội.\n\n` +
                       `📌 1. Nguồn gốc & Đặc điểm:\n- Đảm bảo quy trình sản xuất sạch, vệ sinh an toàn thực phẩm & đạt quy chuẩn chất lượng.\n- Nguồn nguyên liệu được chọn lọc kỹ lưỡng mang lại hiệu quả và hương vị tốt nhất.\n\n` +
                       `📌 2. Hướng dẫn sử dụng & Bảo quản:\n- Bảo quản nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp.\n- Sử dụng theo hướng dẫn đi kèm sản phẩm.\n\n` +
                       `📌 3. Cam kết dịch vụ:\n- Giao hàng nhanh chóng tận nơi tại Đông Anh và các vùng lân cận.\n- Đổi trả linh hoạt nếu phát hiện lỗi từ nhà sản xuất.`;

        document.getElementById('prod-desc').value = descText;
        if (!document.getElementById('prod-unit').value) {
            document.getElementById('prod-unit').value = 'Hộp';
        }

        // Auto switch to description tab
        const descTabBtn = document.querySelectorAll('.prod-tab-btn')[3];
        if (descTabBtn) switchTab('tab-description', descTabBtn);
    }
</script>
@endsection
