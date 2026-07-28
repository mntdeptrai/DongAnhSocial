@extends(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.app')

@section('title', 'Chỉnh sửa Trường học - ' . $school->standardized_name)

@section('content')
<style>
    .school-edit-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        padding: 28px;
        margin-bottom: 24px;
    }
    .school-edit-card-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e1b4b;
        margin-bottom: 18px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0e7ff;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-label-custom {
        font-weight: 700;
        font-size: 0.88rem;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }
    .form-control-custom {
        width: 100%;
        padding: 10px 14px;
        font-size: 0.92rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        background-color: #ffffff;
        color: #0f172a;
        transition: all 0.2s ease;
    }
    .form-control-custom:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3.5px rgba(79, 70, 229, 0.15);
        outline: none;
    }
    .component-box {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 18px;
        position: relative;
        transition: all 0.2s ease;
    }
    .component-box:hover {
        border-color: #818cf8;
        background: #f1f5f9;
    }
    .btn-remove-component {
        position: absolute;
        top: 14px;
        right: 14px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-remove-component:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* ===== Upload Zone Styles ===== */
    .r2-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        background: #fafbfc;
        position: relative;
    }
    .r2-upload-zone:hover,
    .r2-upload-zone.dragover {
        border-color: #4f46e5;
        background: #eef2ff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08);
    }
    .r2-upload-zone .upload-icon {
        font-size: 2rem;
        margin-bottom: 6px;
        display: block;
    }
    .r2-upload-zone .upload-text {
        font-size: 0.85rem;
        color: #64748b;
        line-height: 1.4;
    }
    .r2-upload-zone .upload-text strong {
        color: #4f46e5;
    }
    .r2-upload-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    /* Preview Card */
    .r2-upload-preview {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 12px;
        padding: 10px 14px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        position: relative;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .r2-upload-preview img {
        width: 72px;
        height: 52px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
    }
    .r2-upload-preview .preview-info {
        flex: 1;
        min-width: 0;
    }
    .r2-upload-preview .preview-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-bottom: 2px;
    }
    .r2-upload-preview .preview-meta {
        font-size: 0.76rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .r2-upload-preview .preview-status {
        font-size: 0.78rem;
        font-weight: 700;
    }
    .r2-upload-preview .preview-status.uploading { color: #d97706; }
    .r2-upload-preview .preview-status.success { color: #16a34a; }
    .r2-upload-preview .preview-status.error { color: #dc2626; }
    .r2-upload-preview .btn-remove-preview {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: 8px;
        flex-shrink: 0;
    }
    .r2-upload-preview .btn-remove-preview:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }
</style>

<!-- Welcome Admin Banner Header -->
<div class="admin-welcome-banner mb-4" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border-radius: 20px; padding: 24px 30px; color: #ffffff; box-shadow: 0 10px 30px rgba(79, 70, 229, 0.25);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="mb-1">
                <a href="{{ route('principal.schools.index') }}" class="text-white text-decoration-none small fw-bold opacity-75 hover-opacity-100">
                    ⬅ Quay lại danh sách trường
                </a>
            </div>
            <h1 class="h3 fw-extrabold text-white mb-1" style="font-size: 1.5rem; letter-spacing: -0.02em;">
                🏫 Quản Lý & Chỉnh Sửa Trường Học
            </h1>
            <p class="text-white text-opacity-85 small mb-0">
                Cập nhật thông tin trường sáp nhập mới & danh sách các điểm trường thành phần cũ cho: <strong style="color: #fbbf24;">{{ $school->standardized_name }}</strong>
            </p>
        </div>
        <div>
            <a href="/dia-diem/{{ $school->slug }}" target="_blank" class="btn btn-light rounded-pill px-4 font-weight-bold shadow-sm" style="color: #4f46e5; font-weight: 700; font-size: 0.88rem;">
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

<form id="schoolEditForm" action="{{ route('principal.schools.update', $school->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- SECTION 1: THÔNG TIN TRƯỜNG CHÍNH -->
    <div class="school-edit-card">
        <div class="school-edit-card-title">
            <span style="background: #e0e7ff; color: #4f46e5; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;">📍</span>
            <span>1. Thông tin Trường Sáp Nhập Mới</span>
        </div>

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label-custom">Tên Trường Học (Tự động chuẩn hóa MN/TH): <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control-custom" value="{{ old('name', $school->name) }}" required placeholder="Ví dụ: Trường Mầm non Phúc Lộc">
                <div class="form-text small text-muted mt-1">Các từ viết tắt như MN, TH sẽ tự động được chuẩn hóa thành Mầm non, Tiểu học.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label-custom">Số Điện Thoại Liên Hệ:</label>
                <input type="text" name="phone" class="form-control-custom" value="{{ old('phone', $school->phone) }}" placeholder="024 3883 xxxx">
            </div>

            <div class="col-md-8">
                <label class="form-label-custom">Địa Chỉ Đơn Vị Mới:</label>
                <input type="text" name="address" class="form-control-custom" value="{{ old('address', $school->address) }}" placeholder="Ví dụ: Thôn Phúc Lộc, Xã Đông Anh, Hà Nội">
            </div>

            <div class="col-md-4">
                <label class="form-label-custom">Hiệu Trưởng Đại Diện:</label>
                <input type="text" name="principal_name" class="form-control-custom" value="{{ old('principal_name', $storyData['mergedSchool']['principal'] ?? '') }}" placeholder="Ví dụ: Cô Đỗ Thị Hậu">
            </div>

            <!-- Upload Ảnh Đại Diện Trường (Client-side Nén trước khi lưu) -->
            <div class="col-12">
                <label class="form-label-custom">Hình Ảnh Đại Diện Trường:</label>
                <div class="r2-upload-zone" id="mainUploadZone">
                    <input type="file" name="image" accept="image/*" id="mainFileInput">
                    <span class="upload-icon">📷</span>
                    <div class="upload-text">
                        <strong>Bấm chọn</strong> hoặc <strong>kéo thả ảnh</strong> vào đây<br>
                        <span style="font-size: 0.78rem; color: #94a3b8;">Tự động nén tối ưu trước khi gửi • Chỉ tải lên khi bạn bấm "Lưu thay đổi"</span>
                    </div>
                </div>
                <div id="mainUploadPreview">
                    @if($school->image_path)
                        <div class="r2-upload-preview mt-2" id="mainExistingPreview">
                            <img src="{{ $school->image_path }}" alt="Ảnh hiện tại">
                            <div class="preview-info">
                                <div class="preview-name">Ảnh đại diện hiện tại</div>
                                <div class="preview-meta">
                                    <span class="preview-status success">✓ Đã lưu trên hệ thống</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: DANH SÁCH CÁC TRƯỜNG THÀNH PHẦN SÁP NHẬP VÀO -->
    <div class="school-edit-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom">
            <div>
                <div class="school-edit-card-title mb-1 border-0 pb-0">
                    <span style="background: #ecfdf5; color: #10b981; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;">🏫</span>
                    <span>2. Danh sách các trường học thành phần sáp nhập vào</span>
                </div>
                <p class="text-muted small mb-0 ms-5">Quản lý tên, địa chỉ, đại diện, quy mô lớp/học sinh, CBGVNV, diện tích & tọa độ bản đồ của các điểm trường cũ</p>
            </div>
            <button type="button" id="addNewComponentBtn" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" style="background: #10b981; border: none; font-size: 0.88rem;">
                + Thêm trường sáp nhập
            </button>
        </div>

        <!-- Real-time Aggregated Summary Banner -->
        <div class="alert alert-primary border-0 shadow-sm rounded-4 p-3 mb-4" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); color: #1e1b4b;" id="aggregatedTotalsBanner">
            <div class="fw-bold mb-2 fs-6 d-flex align-items-center gap-2">
                <span>📊</span> <span>Tổng hợp quy mô Trường Sáp Nhập Mới (Tự động cộng từ các điểm trường thành phần):</span>
            </div>
            <div class="row g-2 text-center mb-2">
                <div class="col-md-3 col-6">
                    <div class="bg-white rounded-3 p-2 shadow-sm border">
                        <small class="text-muted d-block fw-semibold">🏫 Tổng số lớp</small>
                        <strong class="fs-5" style="color: #4f46e5;" id="sumClasses">0 lớp</strong>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white rounded-3 p-2 shadow-sm border">
                        <small class="text-muted d-block fw-semibold">👨‍🎓 Tổng học sinh</small>
                        <strong class="fs-5" style="color: #10b981;" id="sumStudents">0 học sinh</strong>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white rounded-3 p-2 shadow-sm border">
                        <small class="text-muted d-block fw-semibold">👩‍🏫 Tổng CBGVNV</small>
                        <strong class="fs-5" style="color: #3b82f6;" id="sumStaff">0 CBGVNV</strong>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white rounded-3 p-2 shadow-sm border">
                        <small class="text-muted d-block fw-semibold">📐 Tổng diện tích</small>
                        <strong class="fs-5" style="color: #ef4444;" id="sumArea">0 m²</strong>
                    </div>
                </div>
            </div>
            <div id="sumLocationsList" class="small text-secondary ps-1 mt-2">
                <!-- Location list rendered dynamically -->
            </div>
        </div>

        <div id="componentsContainer">
            @forelse($components as $index => $comp)
                <div class="component-box">
                    <button type="button" class="btn-remove-component remove-component-btn">✕ Xóa điểm trường này</button>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Tên trường thành phần cũ: <span class="text-danger">*</span></label>
                            <input type="text" name="components[{{ $index }}][name]" class="form-control-custom comp-name" value="{{ $comp['name'] ?? '' }}" required placeholder="Trường Mầm non / Tiểu học cũ..." oninput="recalculateTotals()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">Đại diện / Hiệu trưởng cũ:</label>
                            <input type="text" name="components[{{ $index }}][principal]" class="form-control-custom" value="{{ $comp['principal'] ?? '' }}" placeholder="Ví dụ: Cô Nguyễn Thị Hoa">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">Địa chỉ điểm trường cũ:</label>
                            <input type="text" name="components[{{ $index }}][address]" class="form-control-custom comp-address" value="{{ $comp['address'] ?? '' }}" placeholder="Khu A, Thôn Phúc Lộc..." oninput="recalculateTotals()">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-custom">Số lớp:</label>
                            <input type="number" name="components[{{ $index }}][classes]" class="form-control-custom comp-classes" value="{{ $comp['classes'] ?? 0 }}" oninput="recalculateTotals()">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-custom">Số học sinh:</label>
                            <input type="number" name="components[{{ $index }}][students]" class="form-control-custom comp-students" value="{{ $comp['students'] ?? 0 }}" oninput="recalculateTotals()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">Số CBGVNV (Cán bộ, Giáo viên, Nhân viên):</label>
                            <input type="number" name="components[{{ $index }}][staff]" class="form-control-custom comp-staff" value="{{ $comp['staff'] ?? 0 }}" placeholder="Ví dụ: 35" oninput="recalculateTotals()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">Tổng diện tích đất / cơ sở (m²):</label>
                            <input type="number" step="0.1" name="components[{{ $index }}][area]" class="form-control-custom comp-area" value="{{ $comp['area'] ?? 0 }}" placeholder="Ví dụ: 6500" oninput="recalculateTotals()">
                        </div>

                        <div class="col-12">
                            <label class="form-label-custom">Vị trí Google Maps (Link chia sẻ vị trí Google Maps):</label>
                            @php
                                $latVal = $comp['lat'] ?? '';
                                $lngVal = $comp['lng'] ?? '';
                                $gmapUrl = !empty($comp['gmap_link']) ? $comp['gmap_link'] : (($latVal && $lngVal) ? "https://www.google.com/maps?q={$latVal},{$lngVal}" : "https://www.google.com/maps/search/" . urlencode(($comp['name'] ?? '') . ' ' . ($comp['address'] ?? '')));
                            @endphp
                            <div class="input-group">
                                <span class="input-group-text bg-white text-danger border-end-0" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;">📍</span>
                                <input type="url" name="components[{{ $index }}][gmap_link]" class="form-control-custom comp-gmap-link border-start-0" value="{{ $gmapUrl }}" placeholder="Ví dụ: https://maps.app.goo.gl/1N4EZGPRaSKddxCRA hoặc https://maps.app.goo.gl/..." oninput="updateGmapBtn(this)" style="border-radius: 0;">
                                <a href="{{ $gmapUrl }}" target="_blank" class="btn btn-danger px-4 fw-bold comp-gmap-btn d-flex align-items-center gap-1 text-white text-decoration-none" style="background: #ea4335; border-color: #ea4335; border-radius: 0 12px 12px 0;">
                                    <span>🗺️ Mở Bản Đồ</span>
                                </a>
                            </div>
                        </div>

                        <!-- Upload Hình ảnh cơ sở cũ -->
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
                                    <div class="r2-upload-preview mt-2">
                                        <img src="{{ $comp['photo'] }}" alt="Ảnh cơ sở">
                                        <div class="preview-info">
                                            <div class="preview-name">Ảnh điểm trường hiện tại</div>
                                            <div class="preview-meta">
                                                <span class="preview-status success">✓ Đã lưu trên hệ thống</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted bg-light border rounded-3" id="emptyComponentsAlert">
                    <p class="mb-2 fs-5">📭 Chưa có thông tin điểm trường thành phần</p>
                    <p class="small text-secondary mb-0">Bấm nút <strong>"+ Thêm trường sáp nhập"</strong> ở trên để khởi tạo điểm trường sáp nhập đầu tiên.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="d-flex justify-content-end gap-3 mb-5">
        <a href="{{ route('principal.schools.index') }}" class="btn btn-light border rounded-pill px-4 fw-bold">Hủy bỏ</a>
        <button type="submit" id="submitBtn" class="btn btn-primary rounded-pill px-5 fw-bold shadow-lg" style="background: #4f46e5; border: none; font-size: 1rem; padding-top: 12px; padding-bottom: 12px;">
            💾 Lưu thay đổi
        </button>
    </div>
</form>

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
    if (sumAreaEl) sumAreaEl.textContent = totalArea.toLocaleString('en-US') + 'm2';

    if (sumLocationsListEl) {
        if (locations.length > 0) {
            sumLocationsListEl.innerHTML = locations.join(' • ');
        } else {
            sumLocationsListEl.innerHTML = '<em>Chưa có thông tin điểm trường sáp nhập.</em>';
        }
    }
}

// Client-side Image Compression using Browser HTML5 Canvas
function compressImage(file, maxWidth = 1200, maxHeight = 1200, quality = 0.85) {
    return new Promise((resolve) => {
        if (!file || !file.type.startsWith('image/')) {
            resolve(file);
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;

                if (width > maxWidth || height > maxHeight) {
                    if (width / height > maxWidth / maxHeight) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    } else {
                        width = Math.round((width * maxHeight) / height);
                        height = maxHeight;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    if (blob) {
                        const resizedFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        resolve(resizedFile);
                    } else {
                        resolve(file);
                    }
                }, 'image/jpeg', quality);
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

// Replace File object in Input element with compressed file using DataTransfer
function setFileInputFile(input, file) {
    try {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
    } catch (e) {
        console.warn('DataTransfer not supported in browser', e);
    }
}

// Handle file selection (local preview, no server upload until form submit)
async function handleFileSelect(file, input, previewContainer) {
    if (!file || !file.type.startsWith('image/')) return;

    previewContainer.style.display = 'block';
    previewContainer.innerHTML = `
        <div class="r2-upload-preview mt-2">
            <div class="preview-info">
                <div class="preview-name">${file.name}</div>
                <div class="preview-meta">
                    <span class="preview-status uploading">⚡ Đang tối ưu dung lượng ảnh...</span>
                </div>
            </div>
        </div>
    `;

    const compressedFile = await compressImage(file);
    setFileInputFile(input, compressedFile);

    const objectUrl = URL.createObjectURL(compressedFile);
    previewContainer.innerHTML = `
        <div class="r2-upload-preview mt-2">
            <img src="${objectUrl}" alt="Preview">
            <div class="preview-info">
                <div class="preview-name">${compressedFile.name}</div>
                <div class="preview-meta">
                    <span>${formatBytes(compressedFile.size)} (Đã nén tối ưu)</span>
                    <span class="preview-status success">✓ Sẵn sàng (Chưa lưu)</span>
                </div>
            </div>
            <button type="button" class="btn-remove-preview" onclick="removeSelectedFile(this)" title="Xóa ảnh này">
                <span>✕</span> Xóa
            </button>
        </div>
    `;
}

function removeSelectedFile(btn) {
    const preview = btn.closest('.r2-upload-preview');
    const container = btn.closest('.col-12');
    if (container) {
        const fileInput = container.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = '';
        const hiddenInput = container.querySelector('input[type="hidden"]');
        if (hiddenInput) hiddenInput.value = '';
    }
    preview.remove();
}

// ===== MAIN UPLOAD ZONE =====
function initMainUpload() {
    const zone = document.getElementById('mainUploadZone');
    const fileInput = document.getElementById('mainFileInput');
    const previewContainer = document.getElementById('mainUploadPreview');

    zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            const existingPreview = document.getElementById('mainExistingPreview');
            if (existingPreview) existingPreview.style.display = 'none';
            handleFileSelect(e.dataTransfer.files[0], fileInput, previewContainer);
        }
    });

    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            const existingPreview = document.getElementById('mainExistingPreview');
            if (existingPreview) existingPreview.style.display = 'none';
            handleFileSelect(fileInput.files[0], fileInput, previewContainer);
        }
    });
}

// ===== COMPONENT UPLOAD ZONE =====
function initComponentUpload(componentBox) {
    const zone = componentBox.querySelector('.comp-upload-zone');
    const fileInput = componentBox.querySelector('.comp-file-input');
    const previewContainer = componentBox.querySelector('.comp-upload-preview-container');
    if (!zone || !fileInput || !previewContainer) return;

    zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            handleFileSelect(e.dataTransfer.files[0], fileInput, previewContainer);
        }
    });

    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            handleFileSelect(fileInput.files[0], fileInput, previewContainer);
        }
    });
}

// ===== Google Maps Link Button =====
function updateGmapBtn(input) {
    if (!input) return;
    const box = input.closest('.row');
    if (!box) return;
    const gmapBtn = box.querySelector('.comp-gmap-btn');
    if (gmapBtn && input.value) {
        gmapBtn.href = input.value.trim();
    }
}

// ===== Dynamic Components Listener =====
document.addEventListener('DOMContentLoaded', function() {
    let container = document.getElementById('componentsContainer');
    let addBtn = document.getElementById('addNewComponentBtn');
    let emptyAlert = document.getElementById('emptyComponentsAlert');

    initMainUpload();
    document.querySelectorAll('.component-box').forEach(box => initComponentUpload(box));
    recalculateTotals();

    function reindexItems() {
        let items = container.querySelectorAll('.component-box');
        items.forEach((item, index) => {
            item.querySelectorAll('input[name]').forEach(input => {
                let name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/components\[\d+\]/, `components[${index}]`));
                }
            });
        });
        recalculateTotals();
    }

    addBtn.addEventListener('click', function() {
        if (emptyAlert) emptyAlert.remove();

        let index = container.querySelectorAll('.component-box').length;
        let template = `
            <div class="component-box">
                <button type="button" class="btn-remove-component remove-component-btn">✕ Xóa điểm trường này</button>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">Tên trường thành phần cũ: <span class="text-danger">*</span></label>
                        <input type="text" name="components[${index}][name]" class="form-control-custom comp-name" required placeholder="Trường Mầm non / Tiểu học cũ..." oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Đại diện / Hiệu trưởng cũ:</label>
                        <input type="text" name="components[${index}][principal]" class="form-control-custom" placeholder="Ví dụ: Cô Nguyễn Thị Hoa">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Địa chỉ điểm trường cũ:</label>
                        <input type="text" name="components[${index}][address]" class="form-control-custom comp-address" placeholder="Khu A, Thôn..." oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Số lớp:</label>
                        <input type="number" name="components[${index}][classes]" class="form-control-custom comp-classes" value="10" oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Số học sinh:</label>
                        <input type="number" name="components[${index}][students]" class="form-control-custom comp-students" value="300" oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Số CBGVNV (Cán bộ, Giáo viên, Nhân viên):</label>
                        <input type="number" name="components[${index}][staff]" class="form-control-custom comp-staff" value="30" placeholder="Ví dụ: 30" oninput="recalculateTotals()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Tổng diện tích đất / cơ sở (m²):</label>
                        <input type="number" step="0.1" name="components[${index}][area]" class="form-control-custom comp-area" value="5000" placeholder="Ví dụ: 5000" oninput="recalculateTotals()">
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom">Vị trí Google Maps (Link chia sẻ vị trí Google Maps):</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-danger border-end-0" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;">📍</span>
                            <input type="url" name="components[${index}][gmap_link]" class="form-control-custom comp-gmap-link border-start-0" value="https://www.google.com/maps" placeholder="https://maps.app.goo.gl/..." oninput="updateGmapBtn(this)" style="border-radius: 0;">
                            <a href="https://www.google.com/maps" target="_blank" class="btn btn-danger px-4 fw-bold comp-gmap-btn d-flex align-items-center gap-1 text-white text-decoration-none" style="background: #ea4335; border-color: #ea4335; border-radius: 0 12px 12px 0;">
                                <span>🗺️ Mở Bản Đồ</span>
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom">Hình ảnh cơ sở cũ:</label>
                        <input type="hidden" name="components[${index}][existing_photo]" class="comp-photo-url" value="">
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
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        reindexItems();

        const newBox = container.querySelectorAll('.component-box');
        initComponentUpload(newBox[newBox.length - 1]);
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-component-btn')) {
            e.target.closest('.component-box').remove();
            if (container.querySelectorAll('.component-box').length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted bg-light border rounded-3" id="emptyComponentsAlert">
                        <p class="mb-2 fs-5">📭 Chưa có thông tin điểm trường thành phần</p>
                        <p class="small text-secondary mb-0">Bấm nút <strong>"+ Thêm trường sáp nhập"</strong> ở trên để khởi tạo điểm trường sáp nhập đầu tiên.</p>
                    </div>
                `;
            }
            reindexItems();
        }
    });
});
</script>
@endsection
