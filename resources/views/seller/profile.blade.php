@extends('layouts.seller')

@section('title', 'Cấu Hình Gian Hàng & VietQR — ' . $stallName)

@section('content')

@php
    $p = $primaryProduct ?? null;
    $currentBankName = $p->bank_name ?? '';
    $currentBankAccount = $p->bank_account ?? '';
    $currentBankHolder = $p->bank_holder ?? '';
    $currentQrUrl = $p->qr_code_path ?? '';
    $currentSellerName = $p->seller_name ?? $sellerName;
    $currentSellerPhone = $p->seller_phone ?? $sellerPhone;
    $currentStallName = $p->stall_name ?? $stallName;
    
    // Parse description for origin & attp
    $rawDesc = $p->description ?? '';
    $originVal = '';
    $attpVal = 'Đã cam kết ATTP';
    
    if (preg_match('/Nguồn gốc:\s*([^.]+)/u', $rawDesc, $m)) {
        $originVal = trim($m[1]);
    }
    if (preg_match('/Cam kết ATTP:\s*([^.]+)/u', $rawDesc, $m)) {
        $attpVal = trim($m[1]);
    }
@endphp

@if(session('success'))
    <div style="padding: 16px 24px; background: #ecfdf5; border: 1.5px solid #10b981; color: #065f46; border-radius: 16px; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(16,185,129,0.12);">
        <span style="font-size: 1.4rem;">🎉</span> {{ session('success') }}
    </div>
@endif

<style>
.slr-profile-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #0f172a 100%);
    border-radius: 22px;
    padding: 28px 32px;
    color: #ffffff;
    box-shadow: 0 16px 32px -8px rgba(30, 41, 59, 0.25);
    margin-bottom: 28px;
}
.slr-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1.5px solid #e2e8f0;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.04);
}
.slr-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.slr-card-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.slr-form-group {
    margin-bottom: 20px;
}
.slr-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 800;
    color: #334155;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.slr-input, .slr-select, .slr-textarea {
    width: 100%;
    padding: 12px 16px;
    border-radius: 12px;
    border: 1.5px solid #cbd5e1;
    font-size: 0.95rem;
    color: #0f172a;
    background: #f8fafc;
    transition: all 0.2s ease;
    box-sizing: border-box;
}
.slr-input:focus, .slr-select:focus, .slr-textarea:focus {
    border-color: #ea580c;
    background: #ffffff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
}
.slr-grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}
.qr-preview-box {
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border: 1.5px solid #a7f3d0;
    border-radius: 18px;
    padding: 20px;
    text-align: center;
}
.btn-save-profile {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    color: #ffffff;
    font-weight: 900;
    font-size: 1.05rem;
    padding: 16px 36px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 24px rgba(234, 88, 12, 0.3);
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
.btn-save-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(234, 88, 12, 0.4);
}
</style>

<!-- PROFILE HERO BANNER -->
<div class="slr-profile-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 4px 12px; background: rgba(255,255,255,0.15); border-radius: 20px; font-size: 0.78rem; font-weight: 800; color: #fef08a; margin-bottom: 8px;">
                <span>⚙️</span> HỒ SƠ TỰ QUẢN LÝ
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 900; margin: 0 0 6px 0; color: #ffffff;">
                Cấu Hình Gian Hàng & VietQR 4.0
            </h1>
            <p style="margin: 0; color: #cbd5e1; font-size: 0.92rem;">
                Cập nhật thông tin tài khoản ngân hàng, mã VietQR, SĐT Zalo & Giấy cam kết An toàn thực phẩm
            </p>
        </div>
        <div>
            <a href="{{ route('seller.dashboard') }}" style="background: rgba(255,255,255,0.15); color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 12px; font-weight: 800; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,0.25);">
                ⬅️ Quay lại Dashboard
            </a>
        </div>
    </div>
</div>

<form action="{{ route('seller.profile.update') }}" method="POST">
    @csrf

    <!-- SECTION 1: THÔNG TIN TIỂU THƯƠNG & GIAN HÀNG -->
    <div class="slr-card">
        <div class="slr-card-header">
            <span style="font-size: 1.4rem;">🏪</span>
            <h2 class="slr-card-title">Thông Tin Gian Hàng & Liên Hệ Tiểu Thương</h2>
        </div>
        
        <div class="slr-grid-2">
            <div class="slr-form-group">
                <label class="slr-label">Tên Gian Hàng / Quầy Hàng *</label>
                <input type="text" name="stall_name" class="slr-input" value="{{ old('stall_name', $currentStallName) }}" required placeholder="Ví dụ: Gian hàng Thịt Lợn - Cô Dung">
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Hiển thị nổi bật trên bản đồ & danh sách sạp chợ</div>
            </div>

            <div class="slr-form-group">
                <label class="slr-label">Tên Chủ Hộ Kinh Doanh *</label>
                <input type="text" name="seller_name" class="slr-input" value="{{ old('seller_name', $currentSellerName) }}" required placeholder="Ví dụ: Nguyễn Thị Phương Dung">
            </div>

            <div class="slr-form-group">
                <label class="slr-label">Số Điện Thoại / Zalo Liên Hệ *</label>
                <input type="text" name="seller_phone" class="slr-input" value="{{ old('seller_phone', $currentSellerPhone) }}" required placeholder="Ví dụ: 0985815760">
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Dùng cho nút "Gọi ngay" & nút "Nhắn Zalo" của khách mua hàng</div>
            </div>

            <div class="slr-form-group">
                <label class="slr-label">Chợ Trực Thuộc</label>
                <input type="text" class="slr-input" value="{{ $market ? $market->name : 'Chợ Truyền Thống Số' }}" readonly style="background: #f1f5f9; color: #64748b; cursor: not-allowed;">
            </div>
        </div>
    </div>

    <!-- SECTION 2: CẤU HÌNH TÀI KHOẢN NGÂN HÀNG & MÃ VIETQR 4.0 -->
    <div class="slr-card">
        <div class="slr-card-header">
            <span style="font-size: 1.4rem;">💳</span>
            <h2 class="slr-card-title">Cấu Hình Ngân Hàng & Mã VietQR Thanh Toán 4.0</h2>
        </div>

        <div style="padding: 14px 18px; background: #fff7ed; border-left: 4px solid #ea580c; border-radius: 8px; font-size: 0.88rem; color: #9a3412; margin-bottom: 20px; line-height: 1.5;">
            💡 <strong>Hướng dẫn</strong>: Điền Tên Ngân Hàng & Số Tài Khoản bên dưới, hệ thống sẽ <strong>tự động sinh mã VietQR Napas247</strong> hiển thị trực tiếp lên gian hàng số của bạn để khách mua hàng quét mã thanh toán chuyển khoản nhanh chóng.
        </div>

        <div class="slr-grid-2">
            <div>
                <div class="slr-form-group">
                    <label class="slr-label">Tên Ngân Hàng</label>
                    @php
                        $banks = [
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
                        $selectedBankCode = old('bank_name', $currentBankName);
                        $selectedBankLabel = isset($banks[$selectedBankCode]) ? $banks[$selectedBankCode] : $selectedBankCode;
                    @endphp

                    <!-- SEARCHABLE BANK SELECT WIDGET -->
                    <div class="bank-select-wrapper" style="position: relative;">
                        <input type="hidden" name="bank_name" id="bank_name_hidden" value="{{ $selectedBankCode }}">
                        <div style="position: relative;">
                            <input type="text" id="bank_search_input" class="slr-input" style="padding-right: 36px;" 
                                   placeholder="🔍 Gõ tên hoặc chọn ngân hàng (VD: MBBank, VCB...)" 
                                   value="{{ $selectedBankLabel }}" 
                                   autocomplete="off">
                            <span id="clear_bank_btn" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; font-weight: bold; font-size: 1.1rem; display: {{ !empty($selectedBankCode) ? 'inline' : 'none' }};">✕</span>
                        </div>
                        
                        <div id="bank_dropdown_list" style="position: absolute; top: calc(100% + 4px); left: 0; right: 0; max-height: 250px; overflow-y: auto; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 14px; box-shadow: 0 12px 28px -6px rgba(0,0,0,0.18); z-index: 9999; display: none;">
                            <div class="bank-opt-item" data-value="" data-label="" style="padding: 11px 16px; cursor: pointer; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.88rem; font-weight: 700;">
                                -- Chưa sử dụng ngân hàng / Tiền mặt --
                            </div>
                            @foreach($banks as $code => $label)
                                <div class="bank-opt-item" data-value="{{ $code }}" data-label="{{ $label }}" style="padding: 11px 16px; cursor: pointer; border-bottom: 1px solid #f8fafc; font-size: 0.88rem; color: #0f172a; transition: background 0.15s ease;">
                                    🏦 <strong>{{ $code }}</strong> — <span style="color: #475569;">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const input = document.getElementById('bank_search_input');
                            const hidden = document.getElementById('bank_name_hidden');
                            const dropdown = document.getElementById('bank_dropdown_list');
                            const clearBtn = document.getElementById('clear_bank_btn');
                            const items = dropdown.querySelectorAll('.bank-opt-item');

                            function filterItems() {
                                const q = input.value.trim().toLowerCase();
                                let matchCount = 0;
                                items.forEach(item => {
                                    const val = (item.getAttribute('data-value') || '').toLowerCase();
                                    const lbl = (item.getAttribute('data-label') || '').toLowerCase();
                                    if (!q || val.includes(q) || lbl.includes(q)) {
                                        item.style.display = 'block';
                                        matchCount++;
                                    } else {
                                        item.style.display = 'none';
                                    }
                                });
                                dropdown.style.display = 'block';
                                clearBtn.style.display = input.value ? 'inline' : 'none';
                            }

                            input.addEventListener('focus', function() {
                                filterItems();
                            });

                            input.addEventListener('input', function() {
                                filterItems();
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

                                item.addEventListener('mouseenter', function() {
                                    this.style.background = '#fff7ed';
                                });
                                item.addEventListener('mouseleave', function() {
                                    this.style.background = '#ffffff';
                                });
                            });

                            clearBtn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                input.value = '';
                                hidden.value = '';
                                clearBtn.style.display = 'none';
                                filterItems();
                            });

                            document.addEventListener('click', function(e) {
                                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                                    dropdown.style.display = 'none';
                                }
                            });
                        });
                    </script>
                </div>

                <div class="slr-form-group">
                    <label class="slr-label">Số Tài Khoản Ngân Hàng (STK)</label>
                    <input type="text" name="bank_account" class="slr-input" value="{{ old('bank_account', $currentBankAccount) }}" placeholder="Ví dụ: 0985815760 (hoặc STK cá nhân)">
                </div>

                <div class="slr-form-group">
                    <label class="slr-label">Tên Chủ Tài Khoản Ngân Hàng</label>
                    <input type="text" name="bank_holder" class="slr-input" value="{{ old('bank_holder', $currentBankHolder) }}" placeholder="Ví dụ: NGUYEN THI PHUONG DUNG">
                </div>
            </div>

            <!-- PREVIEW MÃ QR -->
            <div>
                <div class="qr-preview-box">
                    <div style="font-size: 0.8rem; font-weight: 800; color: #047857; text-transform: uppercase; margin-bottom: 10px;">
                        📲 XEM TRƯỚC MÃ VIETQR SẼ HIỂN THỊ
                    </div>
                    @if(!empty($currentQrUrl))
                        <div style="background: #ffffff; padding: 12px; border-radius: 14px; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #a7f3d0; margin-bottom: 10px;">
                            <img src="{{ $currentQrUrl }}" alt="VietQR Code Preview" style="width: 160px; height: 160px; display: block;">
                        </div>
                        <div style="font-size: 0.88rem; font-weight: 800; color: #065f46;">
                            {{ $currentBankName }} · {{ $currentBankAccount }}
                        </div>
                        <div style="font-size: 0.78rem; color: #047857; text-transform: uppercase; font-weight: 700;">
                            CHỦ TK: {{ $currentBankHolder ?: $currentSellerName }}
                        </div>
                    @else
                        <div style="padding: 30px 15px; color: #64748b; font-size: 0.88rem; border: 2px dashed #cbd5e1; border-radius: 12px; background: #ffffff;">
                            <span style="font-size: 2.2rem; display: block; margin-bottom: 8px;">💳</span>
                            Chưa có mã VietQR.<br>Hãy chọn <strong>Tên Ngân Hàng</strong> và nhập <strong>Số Tài Khoản</strong> để kích hoạt tự động!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: NGUỒN GỐC XUẤT XỨ & ATTP -->
    <div class="slr-card">
        <div class="slr-card-header">
            <span style="font-size: 1.4rem;">🛡️</span>
            <h2 class="slr-card-title">Nguồn Gốc Xuất Xứ & Chứng Nhận An Toàn Thực Phẩm</h2>
        </div>

        <div class="slr-grid-2">
            <div class="slr-form-group">
                <label class="slr-label">Nguồn Gốc Xuất Xứ Hàng Hóa</label>
                <input type="text" name="origin" class="slr-input" value="{{ old('origin', $originVal) }}" placeholder="Ví dụ: Mua trong làng, Chợ đầu mối, Tự sản xuất trang trại...">
            </div>

            <div class="slr-form-group">
                <label class="slr-label">Chứng Nhận / Cam Kết ATTP</label>
                <input type="text" name="attp" class="slr-input" value="{{ old('attp', $attpVal) }}" placeholder="Ví dụ: Đã cam kết ATTP, 100% Đạt chuẩn BQL kiểm định">
            </div>
        </div>

        <div class="slr-form-group">
            <label class="slr-label">Mô Tả Thêm Về Gian Hàng (Nếu có)</label>
            <textarea name="description" rows="3" class="slr-textarea" placeholder="Giới thiệu thêm về quầy hàng, dịch vụ giao hàng tận nhà, giờ bán..."></textarea>
        </div>
    </div>

    <!-- SUBMIT BUTTON -->
    <div style="text-align: right; margin-bottom: 40px;">
        <button type="submit" class="btn-save-profile">
            💾 LƯU CẤU HÌNH GIAN HÀNG
        </button>
    </div>
</form>

@endsection
