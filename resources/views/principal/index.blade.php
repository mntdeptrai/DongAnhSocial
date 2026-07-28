@extends(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.app')

@section('title', 'Kênh Quản Lý Trường Học - Đông Anh Smart Education')

@section('content')
<style>
    .school-admin-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .school-admin-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 30px -10px rgba(79, 70, 229, 0.15);
        border-color: #c7d2fe;
    }
</style>

<!-- Welcome Admin Banner Header -->
<div class="admin-welcome-banner mb-4" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border-radius: 20px; padding: 26px 32px; color: #ffffff; box-shadow: 0 10px 30px rgba(79, 70, 229, 0.25);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="h3 fw-extrabold text-white mb-1" style="font-size: 1.55rem; letter-spacing: -0.02em;">
                🏫 Kênh Điều Hành Quản Lý Trường Học
            </h1>
            <p class="text-white text-opacity-85 small mb-0">
                Danh sách các trường học & địa điểm sáp nhập thuộc thẩm quyền điều hành của bạn
            </p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        ✓ {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        ⚠️ {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    @forelse($schools as $sch)
        @php
            $schoolTag = '🏫 Trường học';
            $nameLower = mb_strtolower($sch->name);
            if (str_contains($nameLower, 'mầm non')) {
                $schoolTag = '🏫 Mầm non';
            } elseif (str_contains($nameLower, 'tiểu học')) {
                $schoolTag = '🏫 Tiểu học';
            } elseif (str_contains($nameLower, 'thcs')) {
                $schoolTag = '🏫 THCS';
            } elseif (str_contains($nameLower, 'thpt')) {
                $schoolTag = '🏫 THPT';
            } elseif ($sch->commune && !str_contains($sch->commune->name, 'Tổ dân phố')) {
                $schoolTag = '📍 ' . $sch->commune->name;
            } else {
                $schoolTag = '📍 Xã Đông Anh';
            }
            $editUrl = request()->is('admin*') ? route('admin.schools.edit', $sch->id) : route('principal.schools.edit', $sch->id);
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="school-admin-card">
                <div style="position: relative; height: 190px; overflow: hidden; background: #cbd5e1;">
                    <img src="{{ $sch->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=600&q=80' }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $sch->standardized_name }}">
                    <span style="position: absolute; top: 12px; left: 12px; background: rgba(79, 70, 229, 0.92); backdrop-filter: blur(6px); color: white; padding: 5px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        {{ $schoolTag }}
                    </span>
                </div>
                <div class="p-4 d-flex flex-column justify-content-between flex-grow-1">
                    <div>
                        <h5 class="fw-bold text-dark mb-2" style="font-size: 1.12rem; line-height: 1.35; color: #0f172a;">{{ $sch->standardized_name }}</h5>
                        <p class="text-secondary small mb-3">📍 {{ $sch->address ?: 'Xã Đông Anh, Hà Nội' }}</p>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-3">
                        @if(count($sch->merged_components) > 0)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill font-mono" style="font-size: 0.8rem;">
                                🔗 {{ count($sch->merged_components) }} trường sáp nhập
                            </span>
                        @else
                            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-mono" style="font-size: 0.8rem;">
                                📍 1 điểm trường độc lập
                            </span>
                        @endif

                        <a href="{{ $editUrl }}" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" style="background: #4f46e5; border: none; font-size: 0.85rem; padding-top: 8px; padding-bottom: 8px;">
                            ✏️ Chỉnh sửa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="p-5 rounded-4 bg-white border shadow-sm" style="max-width: 600px; margin: 0 auto;">
                <h4 class="text-dark fw-bold mb-2">Chưa tìm thấy trường học được phân công</h4>
                <p class="text-secondary small mb-0">Vui lòng liên hệ QTV Hệ thống để gán mã trường quản lý cho tài khoản của bạn.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection
