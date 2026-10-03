@extends('layouts.hkd')

@section('title', 'Thêm Sản Phẩm / Hàng Hóa Mới — ' . $eatery->name)
@section('title_header', 'Thêm Hàng Hóa & Dịch Vụ Kinh Doanh')

@section('content')
<style>
    .form-page-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .form-header-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    }

    .back-btn-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        background: #f1f5f9;
        padding: 8px 16px;
        border-radius: 30px;
        transition: all 0.2s ease;
    }

    .back-btn-link:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .form-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.04);
        margin-bottom: 40px;
    }

    /* PRESET CHIPS */
    .preset-chips-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 10px;
        margin-bottom: 24px;
    }

    .cat-chip {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 10px 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }

    .cat-chip:hover {
        border-color: #10b981;
        background: #f0fdf4;
        transform: translateY(-2px);
    }

    .cat-chip-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        margin-top: 4px;
    }

    /* FORM GRID */
    .prod-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
    }

    @media (max-width: 850px) {
        .prod-form-grid { grid-template-columns: 1fr; gap: 20px; }
        .form-main-card { padding: 20px; }
    }

    .field-group { margin-bottom: 18px; }

    .field-label {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
        display: block;
    }

    .field-hint { font-size: 0.78rem; color: #94a3b8; font-weight: 500; margin-left: 4px; }

    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    /* IMAGE DROP */
    .img-drop {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        background: #f8fafc;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 160px;
        transition: all 0.2s ease;
        overflow: hidden;
        position: relative;
        padding: 16px;
    }

    .img-drop:hover { border-color: #0284c7; background: #f0f9ff; }
    .img-drop img { max-width: 100%; max-height: 180px; object-fit: contain; display: none; border-radius: 12px; }

    .form-foot-actions {
        display: flex;
        justify-content: flex-end;
        gap: 14px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e2e8f0;
    }
</style>

<div class="form-page-container">

    <!-- HEADER NAVIGATION -->
    <div class="form-header-card">
        <div>
            <a href="{{ route('hkd.products.index') }}" class="back-btn-link">
                <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
            </a>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 12px 0 0 0; font-family: var(--font-heading);">
                ➕ Thêm Hàng Hóa / Dịch Vụ Mới
            </h2>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.88rem;">Tạo thông tin mặt hàng niêm yết công khai trên bản đồ Đông Anh Digital</p>
        </div>
    </div>

    <!-- MAIN FORM CARD -->
    <div class="form-main-card">
        <form action="{{ route('hkd.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- PRESET SUGGESTION CHIPS -->
            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border: 1.5px solid #bbf7d0; border-radius: 18px; padding: 20px; margin-bottom: 28px;">
                <div style="font-weight: 800; font-size: 0.95rem; color: #065f46; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-bolt" style="color: #10b981;"></i>
                    <span>Chọn Nhanh Nhóm Mặt Hàng Mẫu (Tự động điền thông số chuẩn)</span>
                </div>
                <div class="preset-chips-grid">
                    <div class="cat-chip" onclick="pickCat('Gói rạp cưới & Phông rạp sự kiện trọn gói', 'Gói', 'Bao gồm hệ thống rạp phông khép kín, bàn ghế cao cấp, hệ thống đèn led, quạt mát & thảm sàn.', 'Sự kiện & Rạp cưới (Rạp phông, bàn ghế, âm thanh, ánh sáng)')">
                        <div style="font-size: 1.6rem;">🎪</div>
                        <div class="cat-chip-title">Rạp cưới & Sự kiện</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('Dịch vụ bảo dưỡng & Sửa chữa thiết bị', 'Lần', 'Nhận kiểm tra, chẩn đoán sự cố, thay thế linh kiện chính hãng và bảo hành uy tín tận nơi.', 'Dịch vụ Sửa chữa & Kỹ thuật (Sửa điện, nước, điện lạnh, máy tính, xe)')">
                        <div style="font-size: 1.6rem;">🔧</div>
                        <div class="cat-chip-title">Sửa chữa & Kỹ thuật</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('Vật liệu xây dựng & Vật tư công trình', 'Bộ', 'Cung cấp nguồn hàng đạt chuẩn quy chuẩn chất lượng, có đầy đủ hóa đơn chứng từ.', 'Vật liệu Xây dựng & Nội thất (Sơn, xi măng, sắt thép, gạch, thiết bị bếp)')">
                        <div style="font-size: 1.6rem;">🧱</div>
                        <div class="cat-chip-title">Xây dựng & Vật liệu</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('Trang phục & Phụ kiện thời trang', 'Bộ', 'Thiết kế đẹp mắt, chất liệu thoáng mát, bền đẹp theo thời gian.', 'Thời trang, May mặc & Giày dép (Quần áo, phụ kiện, đồng phục)')">
                        <div style="font-size: 1.6rem;">👗</div>
                        <div class="cat-chip-title">Thời trang & Giày dép</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('Thiết bị điện tử & Gia dụng thông minh', 'Cái', 'Hàng chính hãng fullbox, bảo hành theo tiêu chuẩn nhà sản xuất.', 'Công nghệ, Máy tính & Điện tử (Điện thoại, camera, đồ gia dụng điện)')">
                        <div style="font-size: 1.6rem;">📻</div>
                        <div class="cat-chip-title">Điện tử & Gia dụng</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('Món ăn / Đồ ăn chế biến sẵn', 'Phần', 'Chế biến tươi mới hàng ngày từ nguyên liệu sạch, đảm bảo an toàn vệ sinh thực phẩm.', 'Ẩm thực, Quán ăn & Nhà hàng (Đồ ăn, thức uống, tiệc lưu động, ship đồ ăn)')">
                        <div style="font-size: 1.6rem;">🍲</div>
                        <div class="cat-chip-title">Ẩm thực & Món ăn</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('Nước uống & Đồ giải khát', 'Cốc', 'Pha chế tươi ngon hàng ngày từ trái cây tự nhiên, đồ uống mát lành.', 'Ẩm thực, Quán ăn & Nhà hàng (Đồ ăn, thức uống, tiệc lưu động, ship đồ ăn)')">
                        <div style="font-size: 1.6rem;">🧋</div>
                        <div class="cat-chip-title">Đồ uống & Trà sữa</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('Nông sản tươi & Thực phẩm sạch', 'Kg', 'Nguồn gốc rõ ràng tại vùng trồng Đông Anh, không chất bảo quản.', 'Nông sản, Thực phẩm & OCOP (Rau củ quả tươi, thịt cá, đặc sản địa phương)')">
                        <div style="font-size: 1.6rem;">🥦</div>
                        <div class="cat-chip-title">Nông sản & OCOP</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('Liệu trình chăm sóc & Dịch vụ làm đẹp', 'Buổi', 'Thực hiện bởi kỹ thuật viên giàu kinh nghiệm, sản phẩm an toàn lành tính.', 'Mỹ phẩm, Làm đẹp & Spa (Cắt tóc, làm nail, spa thẩm mỹ, mỹ phẩm)')">
                        <div style="font-size: 1.6rem;">💄</div>
                        <div class="cat-chip-title">Mỹ phẩm & Spa</div>
                    </div>
                    <div class="cat-chip" onclick="pickCat('In ấn & Biển bảng quảng cáo', 'Bộ', 'Thiết kế theo yêu cầu, in sắc nét trên công nghệ cao, giao hàng nhanh.', 'Văn phòng phẩm, In ấn & Quảng cáo (Photocopy, in biển bạt, quà tặng)')">
                        <div style="font-size: 1.6rem;">🖨️</div>
                        <div class="cat-chip-title">In ấn & Quảng cáo</div>
                    </div>
                </div>
            </div>

            <!-- FORM 2 CỘT RỘNG RÃI -->
            <div class="prod-form-grid">
                
                <!-- Cột Trái: Thông tin cơ bản & Ảnh -->
                <div>
                    <div class="field-group">
                        <label class="field-label">Tên sản phẩm / hàng hóa <span style="color: #dc2626;">*</span></label>
                        <input id="add-name" type="text" name="name" class="hkd-form-input" required 
                               placeholder="Ví dụ: Rạp cưới trọn gói 10 khẩu độ, Áo sơ mi nam, Sửa điều hòa...">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Giá bán (VNĐ) <span style="color: #dc2626;">*</span></label>
                            <input id="add-price" type="number" name="price" class="hkd-form-input" required value="0"
                                   oninput="showPrice('addPriceHint', this.value)">
                            <div id="addPriceHint" class="price-preview">0đ</div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Đơn vị tính</label>
                            <input id="add-unit" type="text" name="unit" list="unitOptions" 
                                   class="hkd-form-input" placeholder="Ví dụ: Cái, Kg, Bộ, Lần, Suất, Gói..." 
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Mô tả sản phẩm / dịch vụ chi tiết</label>
                        <textarea id="add-desc" name="description" rows="4" class="hkd-form-textarea"
                                  placeholder="Thông số kỹ thuật, quy cách đóng gói, phạm vi phục vụ, thông điệp giới thiệu..."></textarea>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Hình ảnh sản phẩm đại diện</label>
                        <div class="img-drop" onclick="document.getElementById('addImgInput').click()">
                            <input type="file" id="addImgInput" name="image" accept="image/*" 
                                   style="display:none;" onchange="previewImg(this, 'addImgPrev', 'addImgPlaceholder')">
                            <img id="addImgPrev">
                            <div id="addImgPlaceholder" style="text-align: center;">
                                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.2rem; color: #0284c7; margin-bottom: 8px;"></i>
                                <div style="font-size: 0.9rem; font-weight: 700; color: #334155;">Bấm để chọn ảnh tải lên từ máy tính</div>
                                <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">PNG, JPG, WEBP — Tối đa 5MB</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột Phải: Phân loại & Cam kết -->
                <div>
                    <div style="background: #f0f9ff; border: 1.5px dashed #bae6fd; border-radius: 20px; padding: 24px; height: 100%; box-sizing: border-box;">
                        <div style="font-weight: 800; font-size: 1rem; color: #0369a1; margin-bottom: 18px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-sliders" style="font-size: 1.2rem;"></i>
                            <span>Tùy Chỉnh Phân Loại & Cam Kết Cung Ứng</span>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Loại hình / Danh mục kinh doanh (Gõ tự do hoặc chọn gợi ý)</label>
                            <input id="add-product-type" type="text" name="product_type" list="businessCategoryList" 
                                   class="hkd-form-input" placeholder="Gõ danh mục tự chọn hoặc chọn từ danh sách đề xuất..." 
                                   autocomplete="off">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Cam kết chất lượng / Điểm nổi bật</label>
                            <input id="add-commitment" type="text" name="commitment_text" class="hkd-form-input" 
                                   placeholder="Ví dụ: Đảm bảo mới 100%, đúng quy cách hợp đồng, uy tín cao...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Chính sách bàn giao & phục vụ</label>
                            <input id="add-delivery" type="text" name="delivery_text" class="hkd-form-input" 
                                   placeholder="Ví dụ: Hỗ trợ thi công, vận chuyển & bàn giao tận nơi tại Đông Anh...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Chính sách Đơn hàng lớn / Hợp đồng</label>
                            <input id="add-order-policy" type="text" name="order_policy" class="hkd-form-input" 
                                   placeholder="Ví dụ: Đặt cọc 30%, gọi hotline trước 1-2 ngày để nhận ưu đãi chiết khấu...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Phương thức Thanh toán & Hóa đơn</label>
                            <input id="add-payment-policy" type="text" name="payment_policy" class="hkd-form-input" 
                                   placeholder="Ví dụ: Tiền mặt COD, VietQR chuyển khoản, xuất hóa đơn VAT điện tử...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Giấy phép / Tiêu chuẩn xác minh</label>
                            <input id="add-cert" type="text" name="certificate_info" class="hkd-form-input" 
                                   placeholder="Ví dụ: Có hóa đơn VAT & Giấy phép ĐKKD chính thức...">
                        </div>

                        <div class="field-group" style="margin-top: 16px; background: #ffffff; padding: 12px 16px; border-radius: 14px; border: 1.5px solid #bae6fd;">
                            <label style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 0.9rem; color: #0369a1; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="is_signature" value="1" style="width: 20px; height: 20px; accent-color: #0284c7; cursor: pointer;">
                                <span>★ Đặt làm Món / Sản phẩm đặc trưng của cơ sở</span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- FOOTER ACTIONS -->
            <div class="form-foot-actions">
                <a href="{{ route('hkd.products.index') }}" class="hkd-btn-action hkd-btn-slate" style="padding: 12px 28px; text-decoration: none;">
                    Hủy bỏ
                </a>
                <button type="submit" class="hkd-btn-action hkd-btn-emerald" style="padding: 12px 32px; font-size: 1rem;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> 🚀 Lưu & Đăng Hàng Mới
                </button>
            </div>
        </form>
    </div>

</div>

{{-- Shared datalist for business categories --}}
<datalist id="businessCategoryList">
    <option value="Sự kiện & Rạp cưới (Rạp phông, bàn ghế, âm thanh, ánh sáng)">
    <option value="Dịch vụ Sửa chữa & Kỹ thuật (Sửa điện, nước, điện lạnh, máy tính, xe)">
    <option value="Vật liệu Xây dựng & Nội thất (Sơn, xi măng, sắt thép, gạch, thiết bị bếp)">
    <option value="Vận tải, Lô-gi-stíc & Chở hàng (Taxi, xe tải, cho thuê xe, cứu hộ)">
    <option value="Thời trang, May mặc & Giày dép (Quần áo, phụ kiện, đồng phục)">
    <option value="Công nghệ, Máy tính & Điện tử (Điện thoại, camera, đồ gia dụng điện)">
    <option value="Y tế, Dược phẩm & Thiết bị Y tế (Nhà thuốc, phòng khám, vật tư y tế)">
    <option value="Mỹ phẩm, Làm đẹp & Spa (Cắt tóc, làm nail, spa thẩm mỹ, mỹ phẩm)">
    <option value="Ẩm thực, Quán ăn & Nhà hàng (Đồ ăn, thức uống, tiệc lưu động, ship đồ ăn)">
    <option value="Nông sản, Thực phẩm & OCOP (Rau củ quả tươi, thịt cá, đặc sản địa phương)">
    <option value="Bách hóa, Tạp hóa & Hàng tiêu dùng (Siêu thị mini, tạp hóa gia đình)">
    <option value="Văn phòng phẩm, In ấn & Quảng cáo (Photocopy, in biển bạt, quà tặng)">
    <option value="Bất động sản & Nhà đất (Cho thuê nhà, mặt bằng kinh doanh, kho bãi)">
    <option value="Giáo dục, Dạy học & Trung tâm (Dạy thêm, ngoại ngữ, kỹ năng sống)">
    <option value="Nông nghiệp, Chăn nuôi & Vật tư (Phân bón, thức ăn gia súc, cây giống)">
    <option value="Thủ công mỹ nghệ & Đồ gỗ (Nội thất gỗ, mây tre đan, đồ thờ cúng)">
    <option value="Dịch vụ Cho thuê & Phục vụ (Thuê ô tô, thiết bị công trình, dụng cụ)">
    <option value="Tài chính, Kế toán & Legal (Kế toán thuế, tư vấn pháp lý, bảo hiểm)">
</datalist>

{{-- Shared datalist for unit suggestions --}}
<datalist id="unitOptions">
    <option value="Cái">
    <option value="Chiếc">
    <option value="Bộ">
    <option value="Set">
    <option value="Lần">
    <option value="Buổi">
    <option value="Ngày">
    <option value="Tháng">
    <option value="Suất">
    <option value="Phần">
    <option value="Ly">
    <option value="Cốc">
    <option value="Kg">
    <option value="Gam">
    <option value="Gói">
    <option value="Hộp">
    <option value="Chai">
    <option value="Lon">
    <option value="Mét">
    <option value="Mét vuông">
    <option value="Mét khối">
    <option value="Tấn">
    <option value="Cuộn">
    <option value="Tờ">
    <option value="Quyển">
    <option value="Viên">
    <option value="Cây">
    <option value="Con">
</datalist>

<script>
    function showPrice(elId, val) {
        const el = document.getElementById(elId);
        if (!el) return;
        const n = parseInt(val) || 0;
        el.textContent = n.toLocaleString('vi-VN') + 'đ';
    }

    function previewImg(input, imgId, holderId) {
        if (input.files && input.files[0]) {
            const r = new FileReader();
            r.onload = e => {
                const img = document.getElementById(imgId);
                img.src = e.target.result;
                img.style.display = 'block';
                document.getElementById(holderId).style.display = 'none';
            };
            r.readAsDataURL(input.files[0]);
        }
    }

    function pickCat(name, unit, desc, productType = '') {
        document.getElementById('add-name').value = name;
        document.getElementById('add-desc').value = desc;
        document.getElementById('add-unit').value = unit;
        if (productType) {
            document.getElementById('add-product-type').value = productType;
        }
    }
</script>

@endsection
