@extends('layouts.hkd')

@section('title', 'Bộ Giải Pháp Mã QR & VietQR — ' . $eatery->name)
@section('title_header', 'Bộ Giải Pháp Số & Mã QR Địa Điểm / VietQR Ngân Hàng')

@section('content')

<style>
    .hkd-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; margin-bottom: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .hkd-card-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; }

    .hkd-qr-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 28px; }
    .hkd-qr-box { border: 2px solid #e2e8f0; border-radius: 20px; padding: 24px; text-align: center; background: #ffffff; }
    .hkd-qr-img { width: 220px; height: 220px; object-fit: contain; margin: 16px auto; border-radius: 12px; border: 1px solid #cbd5e1; padding: 8px; background: #fff; }
</style>

<div class="hkd-qr-grid">
    
    <!-- MÃ QR TRANG ĐỊA ĐIỂM CÔNG KHAI -->
    <div class="hkd-qr-box">
        <div style="font-size: 0.8rem; font-weight: 800; color: #0284c7; text-transform: uppercase;">📲 Mã QR Giới Thiệu Địa Điểm</div>
        <h3 style="font-size: 1.15rem; font-weight: 900; color: #0f172a; margin-top: 4px;">{{ $eatery->name }}</h3>

        <img src="{{ $qrLocationUrl }}" class="hkd-qr-img" alt="QR Location Code">

        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 16px;">
            Dùng dán tại cửa hàng hoặc in ấn để khách hàng quét trực tiếp xem thông tin, bản đồ & sản phẩm.
        </p>

        <a href="{{ $qrLocationUrl }}" download="QR_DiaDiem_{{ $eatery->slug }}.png" target="_blank" class="hkd-btn-action hkd-btn-sky" style="width: 100%; justify-content: center;">
            <i class="fa-solid fa-download"></i> Tải Mã QR Địa Điểm (300x300)
        </a>
    </div>

    <!-- MÃ VIETQR CHUYỂN KHOẢN NGÂN HÀNG -->
    <div class="hkd-qr-box" style="border-color: #a7f3d0; background: #fafdfb;">
        <div style="font-size: 0.8rem; font-weight: 800; color: #059669; text-transform: uppercase;">💳 Mã VietQR Thanh Toán Ngân Hàng</div>
        <h3 style="font-size: 1.15rem; font-weight: 900; color: #065f46; margin-top: 4px;">Chuyển Khoản Tự Động</h3>

        @if(!empty($vietQrUrl))
            <img src="{{ $vietQrUrl }}" class="hkd-qr-img" style="border-color: #10b981;" alt="VietQR Code">
            <div style="font-size: 0.88rem; font-weight: 700; color: #0f172a; margin-bottom: 4px;">{{ $bankName }} - {{ $bankAccount }}</div>
            <div style="font-size: 0.8rem; color: #059669; font-weight: 800; margin-bottom: 16px;">Chủ TK: {{ strtoupper($bankHolder) }}</div>

            <a href="{{ $vietQrUrl }}" download="VietQR_{{ $eatery->slug }}.png" target="_blank" class="hkd-btn-action hkd-btn-emerald" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-download"></i> Tải Mã VietQR Ngân Hàng
            </a>
        @else
            <div style="padding: 40px 20px; color: #64748b;">
                <i class="fa-solid fa-building-columns" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 12px;"></i>
                <p style="font-weight: 700;">Chưa cấu hình tài khoản ngân hàng VietQR.</p>
                <p style="font-size: 0.85rem; margin-top: 4px;">Vui lòng vào trang <strong>Hồ Sơ Cơ Sở</strong> để điền Số tài khoản & Tên ngân hàng.</p>
                <a href="{{ route('hkd.profile') }}" class="hkd-btn-action hkd-btn-emerald" style="margin-top: 14px;">
                    Cấu Hình Ngân Hàng
                </a>
            </div>
        @endif
    </div>

</div>

@endsection
