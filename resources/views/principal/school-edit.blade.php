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

<form action="{{ route('principal.schools.update', $school->id) }}" method="POST" enctype="multipart/form-data">
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

            <div class="col-12">
                <label class="form-label-custom">Hình Ảnh Đại Diện Trường:</label>
                <input type="file" name="image" class="form-control-custom" accept="image/*">
                @if($school->image_path)
                    <div class="mt-3 p-2 bg-light rounded-3 border d-inline-block">
                        <img src="{{ $school->image_path }}" class="rounded-2" style="height: 100px; width: 160px; object-fit: cover;">
                        <span class="d-block text-muted small mt-1 text-center">Ảnh hiện tại</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- SECTION 2: DANH SÁCH CÁC TRƯỜNG THÀNH PHẦN SÁP NHẬP VÀO -->
    <div class="school-edit-card">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 pb-2 border-bottom">
            <div>
                <div class="school-edit-card-title mb-1 border-0 pb-0">
                    <span style="background: #ecfdf5; color: #10b981; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;">🏫</span>
                    <span>2. Danh sách các trường học thành phần sáp nhập vào</span>
                </div>
                <p class="text-muted small mb-0 ms-5">Quản lý tên, địa chỉ, đại diện, quy mô lớp/học sinh & tọa độ bản đồ của các điểm trường cũ</p>
            </div>
            <button type="button" id="addNewComponentBtn" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" style="background: #10b981; border: none; font-size: 0.88rem;">
                + Thêm trường sáp nhập
            </button>
        </div>

        <div id="componentsContainer">
            @forelse($components as $index => $comp)
                <div class="component-box">
                    <button type="button" class="btn-remove-component remove-component-btn">✕ Xóa điểm trường này</button>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Tên trường thành phần cũ: <span class="text-danger">*</span></label>
                            <input type="text" name="components[{{ $index }}][name]" class="form-control-custom" value="{{ $comp['name'] ?? '' }}" required placeholder="Trường Mầm non / Tiểu học cũ...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">Đại diện / Hiệu trưởng cũ:</label>
                            <input type="text" name="components[{{ $index }}][principal]" class="form-control-custom" value="{{ $comp['principal'] ?? '' }}" placeholder="Ví dụ: Cô Nguyễn Thị Hoa">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label-custom">Địa chỉ điểm trường cũ:</label>
                            <input type="text" name="components[{{ $index }}][address]" class="form-control-custom" value="{{ $comp['address'] ?? '' }}" placeholder="Khu A, Thôn Phúc Lộc...">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label-custom">Số lớp:</label>
                            <input type="number" name="components[{{ $index }}][classes]" class="form-control-custom" value="{{ $comp['classes'] ?? 0 }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label-custom">Số học sinh:</label>
                            <input type="number" name="components[{{ $index }}][students]" class="form-control-custom" value="{{ $comp['students'] ?? 0 }}">
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

                        <div class="col-12">
                            <label class="form-label-custom">Hình ảnh cơ sở cũ:</label>
                            <input type="hidden" name="components[{{ $index }}][existing_photo]" value="{{ $comp['photo'] ?? '' }}">
                            <input type="file" name="components[{{ $index }}][photo_file]" class="form-control-custom" accept="image/*">
                            @if(!empty($comp['photo']))
                                <div class="mt-2 p-1 bg-white rounded border d-inline-block">
                                    <img src="{{ $comp['photo'] }}" class="rounded" style="height: 65px; width: 100px; object-fit: cover;">
                                </div>
                            @endif
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
        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-lg" style="background: #4f46e5; border: none; font-size: 1rem; padding-top: 12px; padding-bottom: 12px;">
            💾 Lưu thay đổi
        </button>
    </div>
</form>

<script>
function updateGmapBtn(input) {
    if (!input) return;
    const box = input.closest('.row');
    if (!box) return;
    const gmapBtn = box.querySelector('.comp-gmap-btn');
    if (gmapBtn && input.value) {
        gmapBtn.href = input.value.trim();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let container = document.getElementById('componentsContainer');
    let addBtn = document.getElementById('addNewComponentBtn');
    let emptyAlert = document.getElementById('emptyComponentsAlert');

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
                        <input type="text" name="components[${index}][name]" class="form-control-custom" required placeholder="Trường Mầm non / Tiểu học cũ...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Đại diện / Hiệu trưởng cũ:</label>
                        <input type="text" name="components[${index}][principal]" class="form-control-custom" placeholder="Ví dụ: Cô Nguyễn Thị Hoa">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label-custom">Địa chỉ điểm trường cũ:</label>
                        <input type="text" name="components[${index}][address]" class="form-control-custom" placeholder="Khu A, Thôn...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-custom">Số lớp:</label>
                        <input type="number" name="components[${index}][classes]" class="form-control-custom" value="10">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-custom">Số học sinh:</label>
                        <input type="number" name="components[${index}][students]" class="form-control-custom" value="300">
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
                        <input type="file" name="components[${index}][photo_file]" class="form-control-custom" accept="image/*">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        reindexItems();
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
