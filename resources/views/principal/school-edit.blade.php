@extends(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.app')

@section('title', 'Chỉnh sửa Trường học - ' . $school->standardized_name)

@section('content')
@php
    $isAdmin = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager());
@endphp
<style>
    /* ==========================================================
       HIGH-END AGENCY GRADE SCHOOL EDIT DASHBOARD SYSTEM
       ========================================================== */
    .sch-edit-root {
        max-width: 1240px;
        margin: 0 auto;
        padding: 24px 16px 60px 16px;
        font-family: 'Plus Jakarta Sans', 'Be Vietnam Pro', sans-serif;
    }

    /* Top Hero Banner */
    .sch-edit-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);
        border-radius: 24px;
        padding: 32px 36px;
        color: #ffffff;
        box-shadow: 0 15px 35px rgba(30, 27, 75, 0.25);
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }

    .sch-edit-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 260px;
        height: 260px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .sch-edit-back-link {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        color: #ffffff;
        padding: 6px 16px;
        border-radius: 100px;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        margin-bottom: 12px;
    }

    .sch-edit-back-link:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
        transform: translateX(-2px);
    }

    .sch-edit-hero-title {
        font-size: 1.8rem;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.02em;
        margin-bottom: 8px;
    }

    .sch-edit-hero-desc {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    .sch-edit-hero-badge {
        background: #f59e0b;
        color: #ffffff;
        padding: 4px 12px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .sch-edit-preview-btn {
        background: #ffffff;
        color: #3730a3;
        padding: 12px 24px;
        border-radius: 100px;
        font-weight: 800;
        font-size: 0.88rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        transition: all 0.2s ease;
    }

    .sch-edit-preview-btn:hover {
        background: #f8fafc;
        color: #1e1b4b;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    /* Cards */
    .sch-edit-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        margin-bottom: 28px;
    }

    .sch-edit-card-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1e1b4b;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 2px solid #eef2ff;
        padding-bottom: 14px;
    }

    .sch-edit-title-icon {
        background: #e0e7ff;
        color: #4f46e5;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Form Controls Micro-styling */
    .form-label-custom {
        font-weight: 800;
        font-size: 0.88rem;
        color: #1e293b;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-custom {
        width: 100%;
        padding: 12px 16px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        transition: all 0.2s ease;
    }

    .form-control-custom:focus {
        border-color: #6366f1;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        outline: none;
    }

    .form-control-custom[readonly] {
        background-color: #f1f5f9 !important;
        color: #64748b !important;
        cursor: not-allowed;
    }

    /* Highlight Stats Section Box */
    .sch-edit-stats-box {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        margin-top: 24px;
    }

    .sch-edit-stats-title {
        font-size: 1rem;
        font-weight: 800;
        color: #4f46e5;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Drag & Drop Upload Zone */
    .r2-upload-zone {
        border: 2px dashed #a5b4fc;
        border-radius: 20px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        background: #f5f3ff;
        position: relative;
    }

    .r2-upload-zone:hover,
    .r2-upload-zone.dragover {
        border-color: #4f46e5;
        background: #eef2ff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .r2-upload-zone .upload-icon {
        font-size: 2.2rem;
        margin-bottom: 8px;
        display: block;
    }

    .r2-upload-zone .upload-text {
        font-size: 0.88rem;
        color: #475569;
        line-height: 1.5;
    }

    .r2-upload-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .r2-upload-preview {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 14px;
        padding: 12px 16px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .r2-upload-preview img {
        width: 76px;
        height: 54px;
        object-fit: cover;
        border-radius: 10px;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
    }

    /* Merged Component Box */
    .component-box {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 20px;
        position: relative;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
        transition: all 0.2s ease;
    }

    .component-box:hover {
        border-color: #a5b4fc;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.06);
    }

    .btn-remove-component {
        position: absolute;
        top: 20px;
        right: 20px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 10px;
        padding: 6px 14px;
        font-size: 0.82rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-remove-component:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* Aggregated Totals Banner */
    .sch-edit-totals-banner {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border-radius: 20px;
        padding: 24px;
        color: #1e1b4b;
        margin-bottom: 24px;
        border: 1px solid #c7d2fe;
    }

    .sch-edit-totals-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 768px) {
        .sch-edit-totals-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .sch-edit-total-item {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px 12px;
        text-align: center;
        border: 1px solid #c7d2fe;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.04);
        min-width: 120px;
    }

    .sch-edit-total-lbl {
        font-size: 0.82rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sch-edit-total-val {
        font-size: 1.2rem;
        font-weight: 900;
        line-height: 1.2;
        white-space: nowrap;
    }

    .sch-edit-locations-box {
        font-size: 0.88rem;
        color: #334155;
        font-weight: 600;
        padding-top: 14px;
        border-top: 1px solid rgba(199, 210, 254, 0.8);
        margin-top: 12px;
        line-height: 1.6;
    }

    /* Submit Button */
    .sch-edit-submit-btn {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: #ffffff;
        border: none;
        border-radius: 100px;
        padding: 14px 42px;
        font-size: 1.05rem;
        font-weight: 800;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.35);
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .sch-edit-submit-btn:hover {
        background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(79, 70, 229, 0.45);
    }
</style>

<div class="sch-edit-root">

    <!-- Welcome Hero Banner -->
    <div class="sch-edit-hero">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div>
                    <a href="{{ request()->is('admin*') ? route('admin.schools.index') : route('principal.schools.index') }}" class="sch-edit-back-link">
                        ⬅ Quay lại danh sách trường
                    </a>
                </div>
                <h1 class="sch-edit-hero-title">
                    🏫 Quản Lý & Chỉnh Sửa Trường Học
                </h1>
                <p class="sch-edit-hero-desc">
                    Cập nhật thông tin trường sáp nhập mới & các chỉ số nổi bật cho: <span class="sch-edit-hero-badge">{{ $school->standardized_name }}</span>
                </p>
            </div>
            <div>
                <a href="/dia-diem/{{ $school->slug }}" target="_blank" class="sch-edit-preview-btn">
                    👁️ Xem giao diện thực tế
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            ✓ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form id="schoolEditForm" action="{{ request()->is('admin*') ? route('admin.schools.update', $school->id) : route('principal.schools.update', $school->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- SECTION 1: THÔNG TIN TRƯỜNG CHÍNH -->
        <div class="sch-edit-card">
            <div class="sch-edit-card-title">
                <span class="sch-edit-title-icon">📍</span>
                <span>1. Thông tin Trường Sáp Nhập Mới</span>
            </div>

            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label-custom">Tên Trường Học (Tự động chuẩn hóa MN/TH): <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control-custom" value="{{ old('name', $school->name) }}" required placeholder="Ví dụ: Trường Mầm non Phúc Lộc">
                    <div class="form-text small text-muted mt-1">Các từ viết tắt như MN, TH sẽ tự động được chuẩn hóa thành Mầm non, Tiểu học.</div>
                </div>

                <div class="col-md-5">
                    <label class="form-label-custom">Số Điện Thoại Liên Hệ:</label>
                    <input type="text" name="phone" class="form-control-custom" value="{{ old('phone', $school->phone) }}" placeholder="024 3883 xxxx">
                </div>

                <div class="col-md-7">
                    <label class="form-label-custom">Địa Chỉ Trụ Sở Chính:</label>
                    <input type="text" name="address" class="form-control-custom" value="{{ old('address', $school->address) }}" placeholder="Ví dụ: Thôn Phúc Lộc, Xã Đông Anh, Hà Nội">
                </div>

                <div class="col-md-5">
                    <label class="form-label-custom">Hiệu Trưởng Đại Diện:</label>
                    <input type="text" name="principal_name" class="form-control-custom" value="{{ old('principal_name', $storyData['mergedSchool']['principal'] ?? '') }}" placeholder="Ví dụ: Cô Đỗ Thị Hậu">
                </div>

                <!-- Upload Ảnh Đại Diện Trường -->
                <div class="col-12 mt-3">
                    <label class="form-label-custom">Hình Ảnh Đại Diện Trường:</label>
                    <div class="r2-upload-zone" id="mainUploadZone">
                        <input type="file" name="image" accept="image/*" id="mainFileInput">
                        <span class="upload-icon">📷</span>
                        <div class="upload-text">
                            <strong>Bấm chọn</strong> hoặc <strong>kéo thả ảnh</strong> vào đây<br>
                            <span style="font-size: 0.78rem; color: #64748b;">Tự động nén tối ưu trước khi gửi • Chỉ tải lên khi bạn bấm "Lưu thay đổi"</span>
                        </div>
                    </div>
                    <div id="mainUploadPreview">
                        @if($school->image_path)
                            <div class="r2-upload-preview" id="mainExistingPreview">
                                <img src="{{ $school->image_path }}" alt="Ảnh hiện tại">
                                <div class="preview-info">
                                    <div class="preview-name" style="font-weight: 700;">Ảnh đại diện hiện tại</div>
                                    <div class="preview-meta">
                                        <span class="preview-status success" style="color: #16a34a; font-weight: 700;">✓ Đã lưu trên hệ thống Cloud</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- CÁC CHỈ SỐ THỐNG KÊ TRƯỜNG HỌC -->
                <div class="col-12">
                    <div class="sch-edit-stats-box">
                        <div class="sch-edit-stats-title">
                            <span>📊</span> <span>Chỉ Số Thống Kê Nổi Bật (Hiển thị trên Trang Profile & Dashboard):</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <label class="form-label-custom">📅 Năm Thành Lập:</label>
                                <input type="number" name="founded_year" class="form-control-custom" value="{{ old('founded_year', $storyData['mergedSchool']['founded_year'] ?? 2008) }}" placeholder="2008">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label-custom">👩‍🏫 Số Lượng Giáo Viên:</label>
                                <input type="number" name="total_teachers" class="form-control-custom" value="{{ old('total_teachers', $storyData['mergedSchool']['total_staff'] ?? 63) }}" placeholder="63">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label-custom">🎒 Số Lượng Học Sinh:</label>
                                <input type="number" name="total_students" class="form-control-custom" value="{{ old('total_students', $storyData['mergedSchool']['total_students'] ?? 759) }}" placeholder="759">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label-custom">🏆 Số Danh Hiệu / Giải Thưởng:</label>
                                <input type="number" name="awards_count" class="form-control-custom" value="{{ old('awards_count', $storyData['mergedSchool']['awards_count'] ?? 12) }}" placeholder="12">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">🌐 Website Trường Học:</label>
                                <input type="text" name="website" class="form-control-custom" value="{{ old('website', $storyData['mergedSchool']['website'] ?? 'phucloc.edu.vn') }}" placeholder="phucloc.edu.vn">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">🕒 Giờ Mở Cửa:</label>
                                <input type="text" name="opening_hours" class="form-control-custom" value="{{ old('opening_hours', $school->opening_hours ?: '7:00 – 17:30') }}" placeholder="7:00 – 17:30">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: DANH SÁCH CÁC TRƯỜNG THÀNH PHẦN SÁP NHẬP VÀO -->
        <div class="sch-edit-card">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom">
                <div>
                    <div class="sch-edit-card-title mb-1 border-0 pb-0">
                        <span class="sch-edit-title-icon" style="background: #ecfdf5; color: #10b981;">🏫</span>
                        <span>2. Danh sách các trường học thành phần sáp nhập vào</span>
                    </div>
                    <p class="text-muted small mb-0 ms-5">
                        @if($isAdmin)
                            Quản lý tên, địa chỉ, đại diện, quy mô lớp/học sinh, CBGVNV, diện tích & tọa độ bản đồ của các điểm trường cũ
                        @else
                            🔒 Quy mô lớp, học sinh, CBGVNV & diện tích của từng điểm trường sáp nhập
                        @endif
                    </p>
                </div>

                @if($isAdmin)
                    <button type="button" id="addNewComponentBtn" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" style="background: #10b981; border: none; font-size: 0.88rem; padding: 10px 20px;">
                        + Thêm trường sáp nhập
                    </button>
                @else
                    <span class="badge bg-light text-primary border px-3 py-2 rounded-pill font-weight-bold">
                        🔒 Admin quản lý tên & địa chỉ điểm trường • Hiệu trưởng cập nhật quy mô
                    </span>
                @endif
            </div>

            <!-- Real-time Aggregated Summary Banner -->
            <div class="sch-edit-totals-banner" id="aggregatedTotalsBanner">
                <div class="fw-extrabold mb-3 fs-6 d-flex align-items-center gap-2">
                    <span>📊</span> <span>Tổng hợp quy mô Trường Sáp Nhập Mới (Tự động cộng từ các điểm trường thành phần):</span>
                </div>
                <div class="sch-edit-totals-grid">
                    <div class="sch-edit-total-item">
                        <div class="sch-edit-total-lbl">🏫 Tổng số lớp</div>
                        <div class="sch-edit-total-val" style="color: #4f46e5;" id="sumClasses">0 lớp</div>
                    </div>
                    <div class="sch-edit-total-item">
                        <div class="sch-edit-total-lbl">👨‍🎓 Tổng học sinh</div>
                        <div class="sch-edit-total-val" style="color: #10b981;" id="sumStudents">0 học sinh</div>
                    </div>
                    <div class="sch-edit-total-item">
                        <div class="sch-edit-total-lbl">👩‍🏫 Tổng CBGVNV</div>
                        <div class="sch-edit-total-val" style="color: #2563eb;" id="sumStaff">0 CBGVNV</div>
                    </div>
                    <div class="sch-edit-total-item">
                        <div class="sch-edit-total-lbl">📐 Tổng diện tích</div>
                        <div class="sch-edit-total-val" style="color: #ef4444;" id="sumArea">0 m²</div>
                    </div>
                </div>
                <div id="sumLocationsList" class="sch-edit-locations-box">
                    <!-- Location list rendered dynamically -->
                </div>
            </div>

            <div id="componentsContainer">
                @forelse($components as $index => $comp)
                    <div class="component-box">
                        @if($isAdmin)
                            <button type="button" class="btn-remove-component remove-component-btn">✕ Xóa điểm trường này</button>
                        @endif
                        
                            <div class="col-md-6">
                                <label class="form-label-custom">Tên trường thành phần cũ: <span class="text-danger">*</span></label>
                                <input type="text" name="components[{{ $index }}][name]" class="form-control-custom comp-name" value="{{ $comp['name'] ?? '' }}" required placeholder="Trường Mầm non / Tiểu học cũ..." oninput="recalculateTotals()" {{ !$isAdmin ? 'readonly' : '' }}>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">Địa chỉ điểm trường cũ:</label>
                                <input type="text" name="components[{{ $index }}][address]" class="form-control-custom comp-address" value="{{ $comp['address'] ?? '' }}" placeholder="Khu A, Thôn Phúc Lộc..." oninput="recalculateTotals()" {{ !$isAdmin ? 'readonly' : '' }}>
                            </div>

                            <!-- CÁC TRƯỜNG HIỆU TRƯỞNG ĐƯỢC PHÉP CHỈNH SỬA -->
                            <div class="col-md-3 col-6">
                                <label class="form-label-custom" style="color: #2563eb;">🏫 Số lớp (Cho phép sửa):</label>
                                <input type="number" name="components[{{ $index }}][classes]" class="form-control-custom comp-classes" value="{{ $comp['classes'] ?? 0 }}" oninput="recalculateTotals()" style="border-color: #3b82f6; background: #ffffff;">
                            </div>

                            <div class="col-md-3 col-6">
                                <label class="form-label-custom" style="color: #10b981;">🎒 Số học sinh (Cho phép sửa):</label>
                                <input type="number" name="components[{{ $index }}][students]" class="form-control-custom comp-students" value="{{ $comp['students'] ?? 0 }}" oninput="recalculateTotals()" style="border-color: #10b981; background: #ffffff;">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom" style="color: #2563eb;">👩‍🏫 Số CBGVNV (Cán bộ, Giáo viên, Nhân viên):</label>
                                <input type="number" name="components[{{ $index }}][staff]" class="form-control-custom comp-staff" value="{{ $comp['staff'] ?? 0 }}" placeholder="Ví dụ: 35" oninput="recalculateTotals()" style="border-color: #3b82f6; background: #ffffff;">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">📐 Tổng diện tích đất / cơ sở (m²):</label>
                                <input type="number" step="0.1" name="components[{{ $index }}][area]" class="form-control-custom comp-area" value="{{ $comp['area'] ?? 0 }}" placeholder="Ví dụ: 6500" oninput="recalculateTotals()" {{ !$isAdmin ? 'readonly' : '' }}>
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">Vị trí Google Maps (Link chia sẻ vị trí Google Maps):</label>
                                @php
                                    $latVal = $comp['lat'] ?? '';
                                    $lngVal = $comp['lng'] ?? '';
                                    $gmapUrl = !empty($comp['gmap_link']) ? $comp['gmap_link'] : (($latVal && $lngVal) ? "https://www.google.com/maps?q={$latVal},{$lngVal}" : "https://www.google.com/maps/search/" . urlencode(($comp['name'] ?? '') . ' ' . ($comp['address'] ?? '')));
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-danger border-end-0" style="border-radius: 14px 0 0 14px; border-color: #cbd5e1;">📍</span>
                                    <input type="url" name="components[{{ $index }}][gmap_link]" class="form-control-custom comp-gmap-link border-start-0" value="{{ $gmapUrl }}" placeholder="Ví dụ: https://maps.app.goo.gl/..." oninput="updateGmapBtn(this)" style="border-radius: 0;" {{ !$isAdmin ? 'readonly' : '' }}>
                                    <a href="{{ $gmapUrl }}" target="_blank" class="btn btn-danger px-4 fw-bold comp-gmap-btn d-flex align-items-center gap-1 text-white text-decoration-none" style="background: #ea4335; border-color: #ea4335; border-radius: 0 14px 14px 0;">
                                        <span>🗺️ Mở Bản Đồ</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Upload Hình ảnh cơ sở cũ (Chỉ Admin mới upload ảnh điểm trường) -->
                            @if($isAdmin)
                                <div class="col-12">
                                    <label class="form-label-custom">Hình ảnh cơ sở cũ:</label>
                                    <input type="hidden" name="components[{{ $index }}][existing_photo]" class="comp-photo-url" value="{{ $comp['photo'] ?? '' }}">
                                    <div class="r2-upload-zone comp-upload-zone">
                                        <input type="file" name="components[{{ $index }}][photo_file]" accept="image/*" class="comp-file-input">
                                        <span class="upload-icon">🏫</span>
                                        <div class="upload-text">
                                            <strong>Bấm chọn</strong> hoặc kéo thả ảnh vào đây
                                        </div>
                                    </div>
                                    <div class="comp-upload-preview-container">
                                        @if(!empty($comp['photo']))
                                            <div class="r2-upload-preview">
                                                <img src="{{ $comp['photo'] }}" alt="Ảnh cơ sở">
                                                <div class="preview-info">
                                                    <div class="preview-name" style="font-weight: 700;">Ảnh điểm trường hiện tại</div>
                                                    <div class="preview-meta">
                                                        <span class="preview-status success" style="color: #16a34a; font-weight: 700;">✓ Đã lưu trên hệ thống</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif(!empty($comp['photo']))
                                <div class="col-12">
                                    <div class="r2-upload-preview">
                                        <img src="{{ $comp['photo'] }}" alt="Ảnh cơ sở">
                                        <div class="preview-info">
                                            <div class="preview-name" style="font-weight: 700;">Hình ảnh điểm trường</div>
                                            <div class="preview-meta">
                                                <span class="preview-status success" style="color: #16a34a; font-weight: 700;">✓ Đã được phê duyệt bởi Quản trị viên</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted bg-light border rounded-4" id="emptyComponentsAlert">
                        <p class="mb-2 fs-5 font-weight-bold">📭 Chưa có thông tin điểm trường thành phần</p>
                        <p class="small text-secondary mb-0">Bấm nút <strong>"+ Thêm trường sáp nhập"</strong> ở trên để khởi tạo điểm trường sáp nhập đầu tiên.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ request()->is('admin*') ? route('admin.schools.index') : route('principal.schools.dashboard', $school->slug ?: $school->id) }}" class="btn btn-light border rounded-pill px-4 fw-bold" style="padding-top: 12px; padding-bottom: 12px; border-radius: 100px;">Hủy bỏ</a>
            <button type="submit" id="submitBtn" class="sch-edit-submit-btn">
                💾 Lưu tất cả thay đổi
            </button>
        </div>
    </form>
</div>

<script>
// Format bytes to human readable string
function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Recalculate Totals across all Component Schools dynamically
function recalculateTotals() {
    let totalClasses = 0;
    let totalStudents = 0;
    let totalStaff = 0;
    let totalArea = 0;
    let locations = [];

    document.querySelectorAll('.component-box').forEach((box, idx) => {
        let classes = parseInt(box.querySelector('.comp-classes')?.value) || 0;
        let students = parseInt(box.querySelector('.comp-students')?.value) || 0;
        let staff = parseInt(box.querySelector('.comp-staff')?.value) || 0;
        let area = parseFloat(box.querySelector('.comp-area')?.value) || 0;
        let name = box.querySelector('.comp-name')?.value || `Điểm trường ${idx + 1}`;
        let address = box.querySelector('.comp-address')?.value || '';

        totalClasses += classes;
        totalStudents += students;
        totalStaff += staff;
        totalArea += area;

        if (name || address) {
            locations.push(`📍 <strong>Địa điểm ${idx + 1}:</strong> ${name} ${address ? '(' + address + ')' : ''}`);
        }
    });

    const sumClassesEl = document.getElementById('sumClasses');
    const sumStudentsEl = document.getElementById('sumStudents');
    const sumStaffEl = document.getElementById('sumStaff');
    const sumAreaEl = document.getElementById('sumArea');
    const sumLocationsListEl = document.getElementById('sumLocationsList');

    if (sumClassesEl) sumClassesEl.textContent = totalClasses.toLocaleString('en-US') + ' lớp';
    if (sumStudentsEl) sumStudentsEl.textContent = totalStudents.toLocaleString('en-US') + ' học sinh';
    if (sumStaffEl) sumStaffEl.textContent = totalStaff.toLocaleString('en-US') + ' CBGVNV';
    if (sumAreaEl) sumAreaEl.textContent = totalArea.toLocaleString('en-US') + ' m²';

    if (sumLocationsListEl) {
        if (locations.length > 0) {
            sumLocationsListEl.innerHTML = locations.join(' • ');
        } else {
            sumLocationsListEl.innerHTML = '<em>Chưa có thông tin điểm trường sáp nhập.</em>';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    recalculateTotals();

    // Dynamically Add Component Box (Only Admin)
    const addNewBtn = document.getElementById('addNewComponentBtn');
    const container = document.getElementById('componentsContainer');
    const emptyAlert = document.getElementById('emptyComponentsAlert');

    if (addNewBtn) {
        addNewBtn.addEventListener('click', function() {
            if (emptyAlert) emptyAlert.remove();
            
            const index = container.querySelectorAll('.component-box').length;
            const box = document.createElement('div');
            box.className = 'component-box';
            box.innerHTML = `
                <button type="button" class="btn-remove-component remove-component-btn">✕ Xóa điểm trường này</button>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">Tên trường thành phần cũ: <span class="text-danger">*</span></label>
                        <input type="text" name="components[${index}][name]" class="form-control-custom comp-name" required placeholder="Trường Mầm non / Tiểu học cũ..." oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Địa chỉ điểm trường cũ:</label>
                        <input type="text" name="components[${index}][address]" class="form-control-custom comp-address" placeholder="Khu A, Thôn Phúc Lộc..." oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Số lớp:</label>
                        <input type="number" name="components[${index}][classes]" class="form-control-custom comp-classes" value="0" oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Số học sinh:</label>
                        <input type="number" name="components[${index}][students]" class="form-control-custom comp-students" value="0" oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Số CBGVNV (Cán bộ, Giáo viên, Nhân viên):</label>
                        <input type="number" name="components[${index}][staff]" class="form-control-custom comp-staff" value="0" placeholder="Ví dụ: 35" oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Tổng diện tích đất / cơ sở (m²):</label>
                        <input type="number" step="0.1" name="components[${index}][area]" class="form-control-custom comp-area" value="0" placeholder="Ví dụ: 6500" oninput="recalculateTotals()">
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom">Vị trí Google Maps (Link chia sẻ vị trí Google Maps):</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-danger border-end-0" style="border-radius: 14px 0 0 14px; border-color: #cbd5e1;">📍</span>
                            <input type="url" name="components[${index}][gmap_link]" class="form-control-custom comp-gmap-link border-start-0" placeholder="Ví dụ: https://maps.app.goo.gl/..." oninput="updateGmapBtn(this)" style="border-radius: 0;">
                            <a href="#" target="_blank" class="btn btn-danger px-4 fw-bold comp-gmap-btn d-flex align-items-center gap-1 text-white text-decoration-none" style="background: #ea4335; border-color: #ea4335; border-radius: 0 14px 14px 0;">
                                <span>🗺️ Mở Bản Đồ</span>
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom">Hình ảnh cơ sở cũ:</label>
                        <div class="r2-upload-zone comp-upload-zone">
                            <input type="file" name="components[${index}][photo_file]" accept="image/*" class="comp-file-input">
                            <span class="upload-icon">🏫</span>
                            <div class="upload-text">
                                <strong>Bấm chọn</strong> hoặc kéo thả ảnh vào đây
                            </div>
                        </div>
                        <div class="comp-upload-preview-container"></div>
                    </div>
                </div>
            `;
            container.appendChild(box);
            recalculateTotals();
        });
    }

    // Dynamic Remove Component
    container.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-component-btn')) {
            const box = e.target.closest('.component-box');
            if (box) {
                box.remove();
                recalculateTotals();
            }
        }
    });
});

function updateGmapBtn(input) {
    const btn = input.closest('.input-group')?.querySelector('.comp-gmap-btn');
    if (btn) {
        btn.href = input.value || '#';
    }
}
</script>
@endsection
