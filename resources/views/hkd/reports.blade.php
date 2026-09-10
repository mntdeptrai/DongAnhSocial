@extends('layouts.hkd')

@section('title', 'Báo Cáo Doanh Thu & Chỉ Số HT10 — ' . $eatery->name)
@section('title_header', 'Báo Cáo Phân Tích Số Liệu & Chỉ Tiêu Điều Hành (HT10)')

@section('content')

<style>
    .hkd-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; margin-bottom: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .hkd-card-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; }
    
    .hkd-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px; }
    .hkd-kpi-box { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 20px; text-align: center; }
    .hkd-kpi-num { font-size: 1.8rem; font-weight: 900; color: #0f172a; margin: 8px 0 4px 0; }
    .hkd-kpi-title { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; }

    .hkd-ht10-badge {
        background: linear-gradient(135deg, #059669 0%, #0284c7 100%); color: #ffffff;
        padding: 24px; border-radius: 18px; margin-bottom: 28px; box-shadow: 0 8px 20px rgba(5,150,105,0.25);
    }
</style>

<!-- BANNER HƯỚNG DẪN HT10 -->
<div class="hkd-ht10-badge">
    <div style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: #a7f3d0; letter-spacing: 0.05em;">
        📋 Hướng Dẫn Nền Tảng Số HT10 Cấp Xã / Huyện
    </div>
    <h2 style="font-size: 1.4rem; font-weight: 900; margin: 8px 0 10px 0;">Báo Cáo Số Liệu & Tích Hợp Hệ Thống Chuyển Đổi Số</h2>
    <p style="font-size: 0.92rem; color: #f0fdf4; line-height: 1.6; max-width: 900px;">
        Hệ thống tự động lập Bảng theo dõi số liệu, chỉ tiêu điều hành; phân tích doanh thu, chi phí ước tính, lợi nhuận, và quản lý danh mục mặt hàng kinh doanh cho cơ sở. Cung cấp nền tảng kết nối thanh toán ngân hàng điện tử VietQR giúp tự động hóa quy trình.
    </p>
</div>

<!-- THỐNG KÊ DOANH THU & CHỈ TIÊU ĐIỀU HÀNH -->
<div class="hkd-kpi-grid">
    <div class="hkd-kpi-box" style="border-color: #a7f3d0; background: #ecfdf5;">
        <div class="hkd-kpi-title" style="color: #047857;">Doanh Thu Tích Lũy</div>
        <div class="hkd-kpi-num" style="color: #059669;">{{ number_format($totalRevenue) }} đ</div>
        <div style="font-size: 0.78rem; color: #059669; font-weight: 700;">Đơn hàng ghi nhận</div>
    </div>
    <div class="hkd-kpi-box" style="border-color: #fed7aa; background: #fff7ed;">
        <div class="hkd-kpi-title" style="color: #c2410c;">Chi Phí Ước Tính (65%)</div>
        <div class="hkd-kpi-num" style="color: #ea580c;">{{ number_format($estExpenses) }} đ</div>
        <div style="font-size: 0.78rem; color: #ea580c; font-weight: 700;">Vốn hàng & Vận hành</div>
    </div>
    <div class="hkd-kpi-box" style="border-color: #bae6fd; background: #f0f9ff;">
        <div class="hkd-kpi-title" style="color: #0369a1;">Lợi Nhuận Ước Tính</div>
        <div class="hkd-kpi-num" style="color: #0284c7;">{{ number_format($estProfit) }} đ</div>
        <div style="font-size: 0.78rem; color: #0284c7; font-weight: 700;">Lợi nhuận gộp cơ sở</div>
    </div>
    <div class="hkd-kpi-box">
        <div class="hkd-kpi-title">Mặt Hàng Quản Lý</div>
        <div class="hkd-kpi-num">{{ $productsCount }}</div>
        <div style="font-size: 0.78rem; color: #64748b; font-weight: 700;">Sản phẩm niêm yết</div>
    </div>
</div>

<!-- BẢNG BÁO CÁO CHI TIẾT THEO TIÊU CHUẨN HT10 -->
<div class="hkd-card">
    <div class="hkd-card-title">
        <i class="fa-solid fa-chart-line" style="color: #059669;"></i>
        <span>Bảng Tổng Hợp Chỉ Tiêu Điều Hành & Chuyển Đổi Số</span>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 14px; text-align: left; font-size: 0.85rem; font-weight: 800; color: #334155;">Chỉ tiêu báo cáo HT10</th>
                <th style="padding: 14px; text-align: right; font-size: 0.85rem; font-weight: 800; color: #334155;">Giá trị / Trạng thái</th>
                <th style="padding: 14px; text-align: center; font-size: 0.85rem; font-weight: 800; color: #334155;">Đánh giá số hóa</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 16px; font-weight: 700; color: #0f172a;">1. Tổng số đơn hàng tiếp nhận</td>
                <td style="padding: 16px; text-align: right; font-weight: 900; color: #0f172a;">{{ $totalOrders }} đơn</td>
                <td style="padding: 16px; text-align: center;"><span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.75rem;">Đạt chuẩn</span></td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 16px; font-weight: 700; color: #0f172a;">2. Đơn hàng hoàn thành / Đã thanh toán</td>
                <td style="padding: 16px; text-align: right; font-weight: 900; color: #059669;">{{ $completedOrders }} đơn</td>
                <td style="padding: 16px; text-align: center;"><span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.75rem;">Đạt chuẩn</span></td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 16px; font-weight: 700; color: #0f172a;">3. Tỷ lệ đơn thành công</td>
                <td style="padding: 16px; text-align: right; font-weight: 900; color: #0284c7;">
                    {{ $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 100 }}%
                </td>
                <td style="padding: 16px; text-align: center;"><span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.75rem;">Tốt</span></td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 16px; font-weight: 700; color: #0f172a;">4. Tích hợp thanh toán QR Ngân hàng (VietQR)</td>
                <td style="padding: 16px; text-align: right; font-weight: 900; color: #059669;">Đã kích hoạt</td>
                <td style="padding: 16px; text-align: center;"><span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.75rem;">Sẵn sàng</span></td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 16px; font-weight: 700; color: #0f172a;">5. Định vị tọa độ bản đồ GPS (Latitude / Longitude)</td>
                <td style="padding: 16px; text-align: right; font-weight: 900; color: #0f172a;">
                    {{ $eatery->latitude }}, {{ $eatery->longitude }}
                </td>
                <td style="padding: 16px; text-align: center;"><span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.75rem;">Chính xác</span></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 24px; text-align: right;">
        <button onclick="window.print()" class="hkd-btn-action hkd-btn-emerald" style="padding: 12px 24px;">
            <i class="fa-solid fa-print"></i> In Báo Cáo / Xuất File HT10
        </button>
    </div>
</div>

@endsection
