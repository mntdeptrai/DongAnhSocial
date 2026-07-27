@extends('layouts.admin')

@section('title', '➕ Thêm Gian Hàng Số Mới')

@section('content')
<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem; color: #ffffff;">🛒 Thêm Gian Hàng Số Mới</h1>
            <p style="color: rgba(255,255,255,0.85); margin-top: 4px;">Khai báo thông tin hộ kinh doanh, gian hàng và sản phẩm nông sản Chợ 4.0</p>
        </div>
        <a href="/admin/stalls" class="btn-admin btn-admin-accent" style="padding: 8px 18px; border-radius: 8px; font-weight: 700; text-decoration: none;">
            ⬅ Quay lại
        </a>
    </div>
</div>

<!-- Errors Notification Banner -->
@if ($errors->any())
    <div class="admin-alert admin-alert-warning" style="background-color: #fee2e2; border-color: #fecaca; color: #b91c1c; margin-bottom: 24px;">
        <div>
            <strong style="display: block; margin-bottom: 6px;">⚠️ Vui lòng kiểm tra lại thông tin nhập vào:</strong>
            <ul style="padding-left: 20px; font-size: 0.85rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="admin-card" style="max-width: 900px; margin: 0 auto; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); border-radius: 20px; padding: 28px;">
    <form action="/admin/stalls" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- SECTION 1: THÔNG TIN CHỢ & HỘ KINH DOANH -->
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-bottom: 16px; border-bottom: 1.5px solid #e0f2fe; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <span>🏪</span> Thông Tin Chợ & Hộ Kinh Doanh
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Địa Điểm Chợ -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Thuộc Chợ Truyền Thống <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏢</span>
                    @if(session('user_role') === 'manager' && $managerEatery)
                        <input type="hidden" name="eatery_id" value="{{ $managerEatery->id }}">
                        <div class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem; display: flex; align-items: center; background-color: #f1f5f9; color: #0284c7; font-weight: 700; border: 1.5px solid #cbd5e1; cursor: not-allowed;">
                            {{ $managerEatery->name }} ({{ $managerEatery->address }}) 🔒 [Cố định theo tài khoản BQL]
                        </div>
                    @else
                        <select name="eatery_id" required class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem;">
                            @foreach($markets as $m)
                                <option value="{{ $m->id }}" {{ old('eatery_id', $managerEatery ? $managerEatery->id : null) == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }} ({{ $m->address }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <!-- Tên Gian Hàng -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tên Gian Hàng Số <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🛒</span>
                    <input type="text" name="stall_name" required value="{{ old('stall_name') }}" class="admin-form-input" placeholder="Ví dụ: Gian hàng Ăn uống Cô Sinh" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- Họ Tên Chủ Hộ -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Họ và Tên Chủ Hộ Kinh Doanh <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">👤</span>
                    <input type="text" name="seller_name" required value="{{ old('seller_name') }}" class="admin-form-input" placeholder="Ví dụ: Nguyễn Thị Sinh" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Số Điện Thoại -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Số Điện Thoại Chủ Hộ</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">📞</span>
                    <input type="text" name="seller_phone" value="{{ old('seller_phone') }}" class="admin-form-input" placeholder="Ví dụ: 0987654321" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <!-- SECTION 2: THÔNG TIN TÀI KHOẢN NGÂN HÀNG & MÃ VIETQR THANH TOÁN SỐ -->
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-top: 10px; margin-bottom: 16px; border-bottom: 1.5px solid #e0f2fe; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <span>💳</span> Thông Tin Tài Khoản Ngân Hàng & Mã VietQR Thanh Toán Số
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Ngân hàng -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Ngân Hàng Thụ Hưởng</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏦</span>
                    <select name="bank_name" class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem;">
                        <option value="">-- Chọn Ngân Hàng --</option>
                        <option value="MBBank" selected>MBBank (Ngân hàng Quân Đội)</option>
                        <option value="Vietcombank">Vietcombank (VCB)</option>
                        <option value="Agribank">Agribank (Nông nghiệp & PTNT)</option>
                        <option value="Techcombank">Techcombank (TCB)</option>
                        <option value="BIDV">BIDV (Đầu tư & Phát triển)</option>
                        <option value="VPBank">VPBank (Thịnh Vượng)</option>
                        <option value="VietinBank">VietinBank (Công Thương)</option>
                        <option value="TPBank">TPBank (Tiên Phong)</option>
                        <option value="Sacombank">Sacombank</option>
                    </select>
                </div>
            </div>

            <!-- Số tài khoản -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Số Tài Khoản Ngân Hàng</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🔢</span>
                    <input type="text" name="bank_account" value="{{ old('bank_account') }}" class="admin-form-input" placeholder="Ví dụ: 0987654321 / 1900123456" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Tên chủ tài khoản -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tên Chủ Tài Khoản (Viết Hoa)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">💳</span>
                    <input type="text" name="bank_holder" value="{{ old('bank_holder') }}" class="admin-form-input" placeholder="Ví dụ: NGUYEN THI SINH" style="padding-left: 38px; border-radius: 10px; height: 42px; text-transform: uppercase;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- Upload Ảnh Mã VietQR -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tải Ảnh Mã VietQR (File)</label>
                <input type="file" name="qr_code" accept="image/*" class="admin-form-input" style="padding: 8px; border-radius: 10px; height: 42px;">
            </div>

            <!-- Link URL Mã VietQR -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Hoặc URL Ảnh Mã VietQR Online</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🔲</span>
                    <input type="url" name="qr_code_url" value="{{ old('qr_code_url') }}" class="admin-form-input" placeholder="https://api.vietqr.io/..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <!-- SECTION 3: THÔNG TIN SẢN PHẨM & GIÁ BÁN -->
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-top: 10px; margin-bottom: 16px; border-bottom: 1.5px solid #e0f2fe; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <span>📦</span> Thông Tin Sản Phẩm / Nông Sản Nổi Bật
        </h3>

        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Tên Sản Phẩm -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tên Mặt Hàng / Sản Phẩm <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🍜</span>
                    <input type="text" name="name" required value="{{ old('name') }}" class="admin-form-input" placeholder="Ví dụ: Bún Riêu Cua / Cà chua VietGAP" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Giá Bán -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Giá Bán (VNĐ)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏷️</span>
                    <input type="text" name="price" value="{{ old('price') }}" class="admin-form-input" placeholder="Ví dụ: 20.000đ" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Đơn Vị Tính -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Đơn Vị Tính</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">⚖️</span>
                    <input type="text" name="unit" value="{{ old('unit') }}" class="admin-form-input" placeholder="Ví dụ: bát, kg, túi 500g" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Upload Ảnh -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tải Ảnh Gian Hàng / Sản Phẩm (File)</label>
                <input type="file" name="image" accept="image/*" class="admin-form-input" style="padding: 8px; border-radius: 10px; height: 42px;">
            </div>

            <!-- URL Ảnh thay thế -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Hoặc URL Hình Ảnh (Link online)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🌐</span>
                    <input type="url" name="image_url" value="{{ old('image_url') }}" class="admin-form-input" placeholder="https://images.unsplash.com/..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <!-- Mô Tả Gian Hàng -->
        <div class="admin-form-group" style="margin-bottom: 24px;">
            <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Mô Tả Chi Tiết Gian Hàng & Nông Sản</label>
            <textarea name="description" rows="3" class="admin-form-input" style="border-radius: 10px; padding: 12px;" placeholder="Nhập giới thiệu gian hàng, điểm nổi bật về vệ sinh an toàn thực phẩm..."></textarea>
        </div>

        <!-- SECTION 3: TRUY XUẤT NGUỒN GỐC & HẠ TẦNG CHỢ SỐ 4.0 -->
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-top: 10px; margin-bottom: 16px; border-bottom: 1.5px solid #e0f2fe; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <span>🌱</span> Truy Xuất Nguồn Gốc & Tiện Ích Chợ Số 4.0
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Nguồn Gốc Nhập Hàng / Xuất Xứ Nông Sản -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Nguồn Gốc Nhập Hàng / Xuất Xứ Nông Sản <span style="color: var(--admin-danger);">*</span></label>
                <select name="origin" class="admin-form-input" style="border-radius: 10px; height: 42px; font-size: 0.86rem;">
                    <option value="Mua trong làng">🌱 Mua trong làng</option>
                    <option value="Mua trong làng, chợ Đầu">🌾 Mua trong làng, chợ Đầu</option>
                    <option value="Mua chợ Tó">🏪 Mua chợ Tó</option>
                    <option value="Chợ Long Biên">🚚 Chợ Long Biên</option>
                    <option value="Chợ Văn Trì">🛵 Chợ Văn Trì</option>
                    <option value="Chợ đầu mối Bắc Thăng Long">🚛 Chợ đầu mối Bắc Thăng Long</option>
                    <option value="Tự sản xuất" selected>🧑‍🌾 Tự sản xuất (Nông hộ địa phương)</option>
                    <option value="Mua trong làng, tự sản xuất">🏡 Mua trong làng, tự sản xuất</option>
                    <option value="Cơ sở sản xuất gạo sạch Hải Tiến">🏭 Cơ sở sản xuất gạo sạch Hải Tiến</option>
                    <option value="Khác">🔹 Nhập khẩu / Nguồn gốc khác</option>
                </select>
            </div>

            <!-- Xếp Hạng Chất Lượng / Đạt Chuẩn -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Xếp Hạng Chất Lượng / Chứng Nhận</label>
                <select name="star_rating" class="admin-form-input" style="border-radius: 10px; height: 42px; font-size: 0.86rem;">
                    <option value="5 sao">⭐ Đạt chuẩn VietGAP / 5 sao</option>
                    <option value="4 sao" selected>⭐ Hàng tươi nông sản đạt chuẩn ATTP</option>
                    <option value="3 sao">⭐ Gian hàng đạt chuẩn Chợ 4.0</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px;">
            <!-- Chứng nhận Vệ sinh ATTP -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Trạng Thái An Toàn Thực Phẩm (ATTP)</label>
                <select name="attp_status" class="admin-form-input" style="border-radius: 10px; height: 42px; font-size: 0.86rem;">
                    <option value="ĐẠT TIÊU CHUẨN" selected>✅ Đạt chuẩn ATTP (BQL Chợ Kiểm Định)</option>
                    <option value="ĐANG KIỂM NGHIỆM">🧪 Đang xét nghiệm mẫu sản phẩm</option>
                    <option value="CẦN BỔ SUNG">⚠️ Đang bổ sung chứng nhận nguồn gốc</option>
                </select>
            </div>

            <!-- Liên kết VietQR & Thanh Toán Số -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Liên Kết Thanh Toán Số & VietQR</label>
                <select name="has_qr" class="admin-form-input" style="border-radius: 10px; height: 42px; font-size: 0.86rem;">
                    <option value="1" selected>💳 Đã liên kết Mã VietQR & TK Ngân Hàng</option>
                    <option value="0">❌ Chưa liên kết thanh toán số</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1.5px solid var(--admin-border); padding-top: 20px;">
            <a href="/admin/stalls" class="btn-admin btn-admin-accent" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; text-decoration: none;">
                Hủy
            </a>
            <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 32px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border: none; background-color: #0284c7;">
                ✓ Lưu Gian Hàng Số
            </button>
        </div>
    </form>
</div>
@endsection
