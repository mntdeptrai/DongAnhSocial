@extends('layouts.hkd')

@section('title', 'Sửa Sản Phẩm — ' . $product->name)
@section('title_header', 'Cập Nhật Hàng Hóa & Dịch Vụ Kinh Doanh')

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
    .img-drop img { max-width: 100%; max-height: 180px; object-fit: contain; border-radius: 12px; }

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
                ✏️ Cập Nhật Thông Tin Hàng Hóa: {{ $product->name }}
            </h2>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.88rem;">Chỉnh sửa thông số kỹ thuật, mô tả & chính sách cam kết cho mặt hàng này</p>
        </div>
    </div>

    <!-- MAIN FORM CARD -->
    <div class="form-main-card">
        <form action="{{ route('hkd.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @php
                $pType = $specData['product_type'] ?? '';
                if ($pType === 'dich_vu') $pType = 'Dịch vụ & Cho thuê (Sửa chữa, sự kiện, thiết bị...)';
                elseif ($pType === 'hang_hoa') $pType = 'Thời trang, May mặc & Giày dép';
                elseif ($pType === 'dien_tu') $pType = 'Công nghệ, Máy tính & Điện tử';
                elseif ($pType === 'am_thuc') $pType = 'Ẩm thực, Quán ăn & Nhà hàng';
                elseif ($pType === 'nong_san') $pType = 'Nông sản, Thực phẩm & OCOP';

                $isSigChecked = !empty($product->is_signature) || (!empty($specData['is_signature']) && (int)$specData['is_signature'] === 1);
            @endphp

            <!-- FORM 2 CỘT RỘNG RÃI -->
            <div class="prod-form-grid">
                
                <!-- Cột Trái: Thông tin cơ bản & Ảnh -->
                <div>
                    <div class="field-group">
                        <label class="field-label">Tên sản phẩm / hàng hóa <span style="color: #dc2626;">*</span></label>
                        <input id="edit-name" type="text" name="name" class="hkd-form-input" required 
                               value="{{ $product->name }}" placeholder="Ví dụ: Rạp cưới trọn gói 10 khẩu độ, Áo sơ mi nam, Sửa điều hòa...">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Giá bán (VNĐ) <span style="color: #dc2626;">*</span></label>
                            <input id="edit-price" type="number" name="price" class="hkd-form-input" required 
                                   value="{{ (int)$product->price }}"
                                   oninput="showPrice('editPriceHint', this.value)">
                            <div id="editPriceHint" class="price-preview" style="font-size: 0.8rem; font-weight: 700; color: #059669; margin-top: 4px;">
                                {{ number_format($product->price, 0, ',', '.') }}đ
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Đơn vị tính</label>
                            <input id="edit-unit" type="text" name="unit" list="unitOptions" 
                                   class="hkd-form-input" value="{{ $product->unit ?? '' }}" placeholder="Ví dụ: Cái, Kg, Bộ, Lần, Suất, Gói..." 
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Mô tả sản phẩm / dịch vụ chi tiết</label>
                        <textarea id="edit-desc" name="description" rows="4" class="hkd-form-textarea"
                                  placeholder="Thông số kỹ thuật, quy cách đóng gói, phạm vi phục vụ, thông điệp giới thiệu...">{{ $product->description }}</textarea>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Hình ảnh sản phẩm đại diện <span class="field-hint">(Bỏ trống nếu giữ ảnh cũ)</span></label>
                        <div class="img-drop" onclick="document.getElementById('editImgInput').click()">
                            <input type="file" id="editImgInput" name="image" accept="image/*" 
                                   style="display:none;" onchange="previewImg(this, 'editImgPrev', 'editImgPlaceholder')">
                            
                            @if($product->image_path)
                                <img id="editImgPrev" src="{{ \Illuminate\Support\Str::startsWith($product->image_path, ['http://', 'https://']) ? $product->image_path : asset($product->image_path) }}" style="display: block;">
                                <div id="editImgPlaceholder" style="text-align: center; display: none;">
                            @else
                                <img id="editImgPrev" style="display: none;">
                                <div id="editImgPlaceholder" style="text-align: center;">
                            @endif
                                <i class="fa-solid fa-image" style="font-size: 2.2rem; color: #0284c7; margin-bottom: 8px;"></i>
                                <div style="font-size: 0.9rem; font-weight: 700; color: #334155;">Bấm để chọn ảnh thay thế</div>
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
                            <input id="edit-product-type" type="text" name="product_type" list="businessCategoryList" 
                                   class="hkd-form-input" value="{{ $pType }}" 
                                   placeholder="Gõ danh mục tự chọn hoặc chọn từ danh sách đề xuất..." 
                                   autocomplete="off">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Cam kết chất lượng / Điểm nổi bật</label>
                            <input id="edit-commitment" type="text" name="commitment_text" class="hkd-form-input" 
                                   value="{{ $specData['commitment_text'] ?? '' }}"
                                   placeholder="Ví dụ: Đảm bảo mới 100%, đúng quy cách hợp đồng, uy tín cao...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Chính sách bàn giao & phục vụ</label>
                            <input id="edit-delivery" type="text" name="delivery_text" class="hkd-form-input" 
                                   value="{{ $specData['delivery_text'] ?? '' }}"
                                   placeholder="Ví dụ: Hỗ trợ thi công, vận chuyển & bàn giao tận nơi tại Đông Anh...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Chính sách Đơn hàng lớn / Hợp đồng</label>
                            <input id="edit-order-policy" type="text" name="order_policy" class="hkd-form-input" 
                                   value="{{ $specData['order_policy'] ?? '' }}"
                                   placeholder="Ví dụ: Đặt cọc 30%, gọi hotline trước 1-2 ngày để nhận ưu đãi chiết khấu...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Phương thức Thanh toán & Hóa đơn</label>
                            <input id="edit-payment-policy" type="text" name="payment_policy" class="hkd-form-input" 
                                   value="{{ $specData['payment_policy'] ?? '' }}"
                                   placeholder="Ví dụ: Tiền mặt COD, VietQR chuyển khoản, xuất hóa đơn VAT điện tử...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Giấy phép / Tiêu chuẩn xác minh</label>
                            <input id="edit-cert" type="text" name="certificate_info" class="hkd-form-input" 
                                   value="{{ $specData['certificate_info'] ?? '' }}"
                                   placeholder="Ví dụ: Có hóa đơn VAT & Giấy phép ĐKKD chính thức...">
                        </div>

                        <div class="field-group" style="margin-top: 16px; background: #ffffff; padding: 12px 16px; border-radius: 14px; border: 1.5px solid #bae6fd;">
                            <label style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 0.9rem; color: #0369a1; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="is_signature" value="1" {{ $isSigChecked ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #0284c7; cursor: pointer;">
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
                <button type="submit" class="hkd-btn-action hkd-btn-sky" style="padding: 12px 32px; font-size: 1rem;">
                    <i class="fa-solid fa-floppy-disk"></i> 💾 Lưu Thay Đổi Cập Nhật
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
                const holder = document.getElementById(holderId);
                if (holder) holder.style.display = 'none';
            };
            r.readAsDataURL(input.files[0]);
        }
    }
</script>

@endsection
