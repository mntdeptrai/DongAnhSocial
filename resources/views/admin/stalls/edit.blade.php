@extends('layouts.admin')

@section('title', '✏️ Chỉnh Sửa Gian Hàng: ' . $stall->stall_name)

@section('content')
<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem; color: #ffffff;">✏️ Chỉnh Sửa Gian Hàng Số: {{ $stall->stall_name }}</h1>
            <p style="color: rgba(255,255,255,0.85); margin-top: 4px;">Cập nhật thông tin chủ hộ, sản phẩm và hồ sơ chứng nhận Chợ 4.0</p>
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
    <form action="/admin/stalls/{{ $stall->id }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                                <option value="{{ $m->id }}" {{ old('eatery_id', $stall->eatery_id) == $m->id ? 'selected' : '' }}>
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
                    <input type="text" name="stall_name" required value="{{ old('stall_name', $stall->stall_name) }}" class="admin-form-input" placeholder="Ví dụ: Gian hàng Ăn uống Cô Sinh" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- Họ Tên Chủ Hộ -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Họ và Tên Chủ Hộ Kinh Doanh <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">👤</span>
                    <input type="text" name="seller_name" required value="{{ old('seller_name', $stall->seller_name) }}" class="admin-form-input" placeholder="Ví dụ: Nguyễn Thị Sinh" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Số Điện Thoại -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Số Điện Thoại Chủ Hộ</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">📞</span>
                    <input type="text" name="seller_phone" value="{{ old('seller_phone', $stall->seller_phone) }}" class="admin-form-input" placeholder="Ví dụ: 0987654321" style="padding-left: 38px; border-radius: 10px; height: 42px;">
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
                @php
                    $adminBanks = [
                        'MBBank'         => 'MBBank (NH Quân Đội)',
                        'VietinBank'     => 'VietinBank (NH Công Thương)',
                        'Vietcombank'    => 'Vietcombank (NH Ngoại Thương - VCB)',
                        'Agribank'       => 'Agribank (NH Nông Nghiệp & PTNT)',
                        'BIDV'           => 'BIDV (NH Đầu Tư & Phát Triển)',
                        'Techcombank'    => 'Techcombank (NH Kỹ Thương - TCB)',
                        'VPBank'         => 'VPBank (NH Việt Nam Thịnh Vượng)',
                        'TPBank'         => 'TPBank (NH Tiên Phong)',
                        'ACB'            => 'ACB (NH Á Châu)',
                        'Sacombank'      => 'Sacombank (NH Sài Gòn Thương Tín)',
                        'MSB'            => 'MSB (NH Hàng Hải)',
                        'VIB'            => 'VIB (NH Quốc Tế)',
                        'HDBank'         => 'HDBank (NH Phát Triển TP.HCM)',
                        'LPBank'         => 'LPBank (LienVietPostBank)',
                        'SHB'            => 'SHB (NH Sài Gòn - Hà Nội)',
                        'SeABank'        => 'SeABank (NH Đông Nam Á)',
                        'ABBANK'         => 'ABBANK (NH An Bình)',
                        'PVcomBank'      => 'PVcomBank (NH Đại Chúng)',
                        'Eximbank'       => 'Eximbank (NH Xuất Nhập Khẩu)',
                        'OCB'            => 'OCB (NH Phương Đông)',
                        'SCB'            => 'SCB (NH Sài Gòn)',
                        'BacABank'       => 'BacABank (NH Bắc Á)',
                        'DongABank'      => 'DongABank (NH Đông Á)',
                        'BaoVietBank'    => 'BaoVietBank (NH Bảo Việt)',
                        'NCB'            => 'NCB (NH Quốc Dân)',
                        'Oceanbank'      => 'Oceanbank (NH Đại Dương)',
                        'GPBank'         => 'GPBank (NH Dầu Khí Toàn Cầu)',
                        'KienLongBank'   => 'KienLongBank (NH Kiên Long)',
                        'VietBank'       => 'VietBank (NH Việt Nam Thương Tín)',
                        'NamABank'       => 'NamABank (NH Nam Á)',
                        'SaigonBank'     => 'SaigonBank (NH Sài Gòn Công Thương)',
                        'PGBank'         => 'PGBank (NH Thịnh Vượng & Phát Triển)',
                        'BVBank'         => 'BVBank (NH Bản Việt)',
                        'PublicBank'     => 'PublicBank (Public Bank Việt Nam)',
                        'ShinhanBank'    => 'ShinhanBank (NH Shinhan Việt Nam)',
                        'WooriBank'      => 'WooriBank (NH Woori Việt Nam)',
                        'UOB'            => 'UOB (NH United Overseas Bank)',
                        'HSBC'           => 'HSBC (NH HSBC Việt Nam)',
                        'Cake'           => 'Cake by VPBank (NH Số Cake)',
                        'Timo'           => 'Timo (NH Số Timo)',
                        'ViettelMoney'   => 'Viettel Money',
                        'VNPTMoney'      => 'VNPT Money'
                    ];
                    $selectedAdminBank = old('bank_name', $stall->bank_name ?? '');
                    $selectedAdminLabel = isset($adminBanks[$selectedAdminBank]) ? $adminBanks[$selectedAdminBank] : $selectedAdminBank;
                @endphp

                <div class="admin-bank-wrapper" style="position: relative;">
                    <input type="hidden" name="bank_name" id="admin_bank_hidden_edit" value="{{ $selectedAdminBank }}">
                    <div style="position: relative;">
                        <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted); z-index: 2;">🏦</span>
                        <input type="text" id="admin_bank_input_edit" class="admin-form-input" style="padding-left: 38px; padding-right: 36px; border-radius: 10px; height: 42px; font-size: 0.86rem;" 
                               placeholder="🔍 Gõ tìm kiếm hoặc chọn ngân hàng..." 
                               value="{{ $selectedAdminLabel }}" 
                               autocomplete="off">
                        <span id="admin_clear_bank_btn_edit" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; font-weight: bold; z-index: 2; display: {{ !empty($selectedAdminBank) ? 'inline' : 'none' }};">✕</span>
                    </div>

                    <div id="admin_bank_dropdown_edit" style="position: absolute; top: calc(100% + 4px); left: 0; right: 0; max-height: 240px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); z-index: 9999; display: none;">
                        <div class="admin-bank-opt-edit" data-value="" data-label="" style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.84rem; font-weight: 700;">
                            -- Chưa sử dụng ngân hàng / Tiền mặt --
                        </div>
                        @foreach($adminBanks as $code => $label)
                            <div class="admin-bank-opt-edit" data-value="{{ $code }}" data-label="{{ $label }}" style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f8fafc; font-size: 0.84rem; color: #0f172a;">
                                🏦 <strong>{{ $code }}</strong> — {{ $label }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const input = document.getElementById('admin_bank_input_edit');
                        const hidden = document.getElementById('admin_bank_hidden_edit');
                        const dropdown = document.getElementById('admin_bank_dropdown_edit');
                        const clearBtn = document.getElementById('admin_clear_bank_btn_edit');
                        const items = dropdown.querySelectorAll('.admin-bank-opt-edit');

                        function filterAdminEditBanks() {
                            const q = input.value.trim().toLowerCase();
                            items.forEach(item => {
                                const val = (item.getAttribute('data-value') || '').toLowerCase();
                                const lbl = (item.getAttribute('data-label') || '').toLowerCase();
                                if (!q || val.includes(q) || lbl.includes(q)) {
                                    item.style.display = 'block';
                                } else {
                                    item.style.display = 'none';
                                }
                            });
                            dropdown.style.display = 'block';
                            clearBtn.style.display = input.value ? 'inline' : 'none';
                        }

                        input.addEventListener('focus', filterAdminEditBanks);
                        input.addEventListener('input', function() {
                            filterAdminEditBanks();
                            hidden.value = input.value.trim();
                        });

                        items.forEach(item => {
                            item.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const val = item.getAttribute('data-value');
                                const lbl = item.getAttribute('data-label');
                                hidden.value = val;
                                input.value = lbl || val;
                                dropdown.style.display = 'none';
                                clearBtn.style.display = input.value ? 'inline' : 'none';
                            });
                            item.addEventListener('mouseenter', function() { this.style.background = '#f1f5f9'; });
                            item.addEventListener('mouseleave', function() { this.style.background = '#ffffff'; });
                        });

                        clearBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            input.value = '';
                            hidden.value = '';
                            clearBtn.style.display = 'none';
                            filterAdminEditBanks();
                        });

                        document.addEventListener('click', function(e) {
                            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                                dropdown.style.display = 'none';
                            }
                        });
                    });
                </script>
            </div>

            <!-- Số tài khoản -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Số Tài Khoản Ngân Hàng</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🔢</span>
                    <input type="text" name="bank_account" value="{{ old('bank_account', $stall->bank_account ?? '') }}" class="admin-form-input" placeholder="Ví dụ: 0987654321 / 1900123456" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Tên chủ tài khoản -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tên Chủ Tài Khoản (Viết Hoa)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">💳</span>
                    <input type="text" name="bank_holder" value="{{ old('bank_holder', $stall->bank_holder ?? '') }}" class="admin-form-input" placeholder="Ví dụ: NGUYEN THI SINH" style="padding-left: 38px; border-radius: 10px; height: 42px; text-transform: uppercase;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- Upload Ảnh Mã VietQR -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tải Ảnh Mã VietQR Mới (File)</label>
                <input type="file" name="qr_code" accept="image/*" class="admin-form-input" style="padding: 8px; border-radius: 10px; height: 42px;">
            </div>

            <!-- Link URL Mã VietQR -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Hoặc URL Ảnh Mã VietQR Online</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🔲</span>
                    <input type="url" name="qr_code_url" value="{{ old('qr_code_url', $stall->qr_code_path ?? '') }}" class="admin-form-input" placeholder="https://api.vietqr.io/..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <!-- Xem trước QR -->
        @if(!empty($stall->qr_code_path))
            <div style="margin-bottom: 20px;">
                <label style="font-size: 0.78rem; font-weight: 700; color: var(--admin-text-muted); display: block; margin-bottom: 6px;">Ảnh VietQR hiện tại:</label>
                <img src="{{ $stall->qr_code_path }}" style="width: 90px; height: 90px; object-fit: contain; border-radius: 10px; border: 1.5px solid var(--admin-border); background: #ffffff; padding: 4px;" alt="VietQR Code">
            </div>
        @endif

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
                    <input type="text" name="name" required value="{{ old('name', $stall->name) }}" class="admin-form-input" placeholder="Ví dụ: Bún Riêu Cua / Cà chua VietGAP" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Giá Bán -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Giá Bán (VNĐ)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏷️</span>
                    <input type="text" name="price" value="{{ old('price', $stall->price) }}" class="admin-form-input" placeholder="Ví dụ: 20.000đ" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>

            <!-- Đơn Vị Tính -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Đơn Vị Tính</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">⚖️</span>
                    <input type="text" name="unit" value="{{ old('unit', $stall->unit) }}" class="admin-form-input" placeholder="Ví dụ: bát, kg, túi 500g" style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Upload Ảnh -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tải Ảnh Mới (File)</label>
                <input type="file" name="image" accept="image/*" class="admin-form-input" style="padding: 8px; border-radius: 10px; height: 42px;">
            </div>

            <!-- URL Ảnh thay thế -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Hoặc URL Hình Ảnh (Link online)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🌐</span>
                    <input type="url" name="image_url" value="{{ old('image_url', $stall->image_path) }}" class="admin-form-input" placeholder="https://images.unsplash.com/..." style="padding-left: 38px; border-radius: 10px; height: 42px;">
                </div>
            </div>
        </div>

        <!-- Xem trước ảnh -->
        @if($stall->image_path)
            <div style="margin-bottom: 20px;">
                <label style="font-size: 0.78rem; font-weight: 700; color: var(--admin-text-muted); display: block; margin-bottom: 6px;">Ảnh hiện tại:</label>
                <img src="{{ $stall->image_path }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px; border: 1.5px solid var(--admin-border);" alt="Current Image">
            </div>
        @endif

        <!-- Mô Tả Gian Hàng -->
        <div class="admin-form-group" style="margin-bottom: 24px;">
            <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Mô Tả Chi Tiết Gian Hàng & Nông Sản</label>
            <textarea name="description" rows="3" class="admin-form-input" style="border-radius: 10px; padding: 12px;" placeholder="Nhập giới thiệu gian hàng, điểm nổi bật về vệ sinh an toàn thực phẩm...">{{ old('description', $stall->description) }}</textarea>
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
                    <option value="Mua trong làng" {{ old('origin', $stall->origin ?? '') === 'Mua trong làng' ? 'selected' : '' }}>🌱 Mua trong làng</option>
                    <option value="Mua trong làng, chợ Đầu" {{ old('origin', $stall->origin ?? '') === 'Mua trong làng, chợ Đầu' ? 'selected' : '' }}>🌾 Mua trong làng, chợ Đầu</option>
                    <option value="Mua chợ Tó" {{ old('origin', $stall->origin ?? '') === 'Mua chợ Tó' ? 'selected' : '' }}>🏪 Mua chợ Tó</option>
                    <option value="Chợ Long Biên" {{ old('origin', $stall->origin ?? '') === 'Chợ Long Biên' ? 'selected' : '' }}>🚚 Chợ Long Biên</option>
                    <option value="Chợ Văn Trì" {{ old('origin', $stall->origin ?? '') === 'Chợ Văn Trì' ? 'selected' : '' }}>🛵 Chợ Văn Trì</option>
                    <option value="Chợ đầu mối Bắc Thăng Long" {{ old('origin', $stall->origin ?? '') === 'Chợ đầu mối Bắc Thăng Long' ? 'selected' : '' }}>🚛 Chợ đầu mối Bắc Thăng Long</option>
                    <option value="Tự sản xuất" {{ old('origin', $stall->origin ?? 'Tự sản xuất') === 'Tự sản xuất' ? 'selected' : '' }}>🧑‍🌾 Tự sản xuất (Nông hộ địa phương)</option>
                    <option value="Mua trong làng, tự sản xuất" {{ old('origin', $stall->origin ?? '') === 'Mua trong làng, tự sản xuất' ? 'selected' : '' }}>🏡 Mua trong làng, tự sản xuất</option>
                    <option value="Cơ sở sản xuất gạo sạch Hải Tiến" {{ old('origin', $stall->origin ?? '') === 'Cơ sở sản xuất gạo sạch Hải Tiến' ? 'selected' : '' }}>🏭 Cơ sở sản xuất gạo sạch Hải Tiến</option>
                    <option value="Khác" {{ old('origin', $stall->origin ?? '') === 'Khác' ? 'selected' : '' }}>🔹 Nhập khẩu / Nguồn gốc khác</option>
                </select>
            </div>

            <!-- Xếp Hạng Chất Lượng / Đạt Chuẩn -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Xếp Hạng Chất Lượng / Chứng Nhận</label>
                <select name="star_rating" class="admin-form-input" style="border-radius: 10px; height: 42px; font-size: 0.86rem;">
                    <option value="5 sao" {{ old('star_rating', $stall->star_rating) === '5 sao' ? 'selected' : '' }}>⭐ Đạt chuẩn VietGAP / 5 sao</option>
                    <option value="4 sao" {{ old('star_rating', $stall->star_rating) === '4 sao' ? 'selected' : '' }}>⭐ Hàng tươi nông sản đạt chuẩn ATTP</option>
                    <option value="3 sao" {{ old('star_rating', $stall->star_rating) === '3 sao' ? 'selected' : '' }}>⭐ Gian hàng đạt chuẩn Chợ 4.0</option>
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
                ✓ Cập Nhật Gian Hàng Số
            </button>
        </div>
    </form>
</div>
@endsection
