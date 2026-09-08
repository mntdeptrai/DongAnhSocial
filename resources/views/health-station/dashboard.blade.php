@extends('layouts.health-station')

@section('title', 'Tổng quan Trạm Y tế — ' . $eatery->name)
@section('header_title', 'Bảng Điều Hành Trạm Y Tế & Cơ Sở Sức Khỏe')

@section('content')
<style>
    .hs-banner {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 60%, #0284c7 100%);
        border-radius: 16px;
        padding: 28px 32px;
        color: #ffffff;
        margin-bottom: 28px;
        box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .hs-banner-info h2 {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 8px;
    }

    .hs-banner-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin-top: 12px;
    }

    .hs-tag {
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(8px);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(255,255,255,0.25);
    }

    .hs-banner-actions {
        display: flex;
        gap: 12px;
    }

    .btn-hs-banner {
        background-color: #ffffff;
        color: #0f766e;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.88rem;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }

    .btn-hs-banner:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        background-color: #f0fdf4;
    }

    /* Grid stats */
    .hs-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .hs-stat-card {
        background-color: #ffffff;
        border-radius: 14px;
        padding: 22px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .hs-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .hs-stat-info h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }

    .hs-stat-info p {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        margin-top: 2px;
    }

    /* Section Card */
    .hs-section-card {
        background-color: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        margin-bottom: 28px;
        overflow: hidden;
    }

    .hs-section-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #fafafa;
    }

    .hs-section-header h3 {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .hs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .hs-table th {
        background-color: #f8fafc;
        padding: 12px 20px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .hs-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        color: #334155;
    }

    .hs-table tr:last-child td {
        border-bottom: none;
    }

    .doc-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #2dd4bf;
    }

    .badge-bhyt {
        background-color: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-block;
    }
</style>

<!-- Banner Top -->
<div class="hs-banner">
    <div class="hs-banner-info">
        <h2>🏥 {{ $eatery->name }}</h2>
        <p><i class="fa-solid fa-location-dot"></i> {{ $eatery->address ?? 'Đông Anh, Hà Nội' }}</p>
        <div class="hs-banner-tags">
            <span class="hs-tag"><i class="fa-solid fa-phone-volume" style="color: #4ade80;"></i> Hotline Cấp cứu: {{ $eatery->phone ?? '0389 928 304' }}</span>
            <span class="hs-tag"><i class="fa-solid fa-clock"></i> {{ $eatery->opening_hours ?? 'Trực Cấp cứu 24/7' }}</span>
            <span class="hs-tag"><i class="fa-solid fa-shield-halved" style="color: #67e8f9;"></i> {{ $eatery->price_range ?? 'Khám BHYT / Miễn phí' }}</span>
        </div>
    </div>
    <div class="hs-banner-actions">
        <a href="{{ route('health-station.profile') }}" class="btn-hs-banner">
            <i class="fa-solid fa-pen-to-square"></i> Cập nhật Hotline & Hồ sơ
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="hs-stats-grid">
    <div class="hs-stat-card">
        <div class="hs-stat-icon" style="background-color: #ccfbf1; color: #0d9488;">
            <i class="fa-solid fa-users-gear"></i>
        </div>
        <div class="hs-stat-info">
            <h3>{{ $storytelling['staff_count'] ?? $storytelling['staff_total'] ?? 9 }} nhân viên</h3>
            <p>Đội ngũ Nhân sự Y tế</p>
        </div>
    </div>

    <div class="hs-stat-card">
        <div class="hs-stat-icon" style="background-color: #e0f2fe; color: #0284c7;">
            <i class="fa-solid fa-maximize"></i>
        </div>
        <div class="hs-stat-info">
            <h3>{{ $storytelling['area'] ?? '3.793 m²' }}</h3>
            <p>Tổng Diện tích Cơ sở</p>
        </div>
    </div>

    <div class="hs-stat-card">
        <div class="hs-stat-icon" style="background-color: #dcfce7; color: #15803d;">
            <i class="fa-solid fa-file-contract"></i>
        </div>
        <div class="hs-stat-info">
            <h3>{{ $storytelling['approved_services'] ?? 214 }}</h3>
            <p>Kỹ thuật SYT Phê duyệt</p>
        </div>
    </div>

    <div class="hs-stat-card">
        <div class="hs-stat-icon" style="background-color: #fef3c7; color: #d97706;">
            <i class="fa-solid fa-check-double"></i>
        </div>
        <div class="hs-stat-info">
            <h3>BHYT 100%</h3>
            <p>Đã đồng bộ Bản đồ Sức khỏe</p>
        </div>
    </div>
</div>

<!-- Services Grid -->
<div class="hs-section-card">
    <div class="hs-section-header">
        <h3><i class="fa-solid fa-notes-medical" style="color: #0d9488;"></i> Danh Mục Dịch Vụ Y Tế & Kỹ Thuật Phê Duyệt</h3>
        <a href="{{ route('health-station.services') }}" style="color: #0d9488; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
            + Quản lý Dịch vụ <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>
    <table class="hs-table">
        <thead>
            <tr>
                <th style="width: 60px;">STT</th>
                <th>Tên Dịch Vụ / Kỹ Thuật Y Tế</th>
                <th>Hình Thức Khám & Chi Phí</th>
                <th>Mô Tả Nhiệm Vụ & Phụ Trách</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $index => $srv)
            <tr>
                <td style="font-weight: 800; color: #64748b;">#{{ $index + 1 }}</td>
                <td>
                    <strong style="color: #0f172a;">{{ $srv->name }}</strong>
                </td>
                <td>
                    <span class="badge-bhyt">{{ $srv->price }}</span>
                </td>
                <td style="color: #64748b; font-size: 0.84rem;">
                    {{ $srv->description ?? 'Phục vụ người dân và bệnh nhân theo quy chuẩn Bộ Y tế' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #64748b; padding: 24px;">
                    Chưa có dịch vụ y tế nào. Bấm vào Quản lý Dịch vụ để thêm mới!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Doctors Grid -->
<div class="hs-section-card">
    <div class="hs-section-header">
        <h3><i class="fa-solid fa-user-doctor" style="color: #0284c7;"></i> Đội Ngũ Bác Sĩ & Cán Bộ Trực Khám Trong Tuần</h3>
        <a href="{{ route('health-station.doctors') }}" style="color: #0284c7; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
            + Quản lý Bác sĩ <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>
    <table class="hs-table">
        <thead>
            <tr>
                <th style="width: 70px;">Chân dung</th>
                <th>Họ & Tên Cán Bộ</th>
                <th>Chức Vụ / Chuyên Khoa</th>
                <th>Lịch Trực Khám</th>
                <th>Số Điện Thoại Cán Bộ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($doctors as $doc)
            <tr>
                <td>
                    <img src="{{ $doc->avatar }}" class="doc-avatar" alt="{{ $doc->name }}">
                </td>
                <td>
                    <strong style="color: #0f172a; font-size: 0.92rem;">{{ $doc->name }}</strong>
                </td>
                <td>
                    <div style="font-weight: 700; color: #0f766e;">{{ $doc->title }}</div>
                    <div style="font-size: 0.78rem; color: #64748b;">{{ $doc->specialty }}</div>
                </td>
                <td>
                    <span style="background-color: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; color: #334155;">
                        <i class="fa-solid fa-calendar-check" style="color: #0d9488;"></i> {{ $doc->duty_schedule }}
                    </span>
                </td>
                <td style="font-weight: 700; color: #0f172a;">
                    <i class="fa-solid fa-phone" style="color: #10b981;"></i> {{ $doc->phone }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b; padding: 24px;">
                    Chưa có danh sách bác sĩ trực. Bấm vào Quản lý Bác sĩ để thêm mới!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
