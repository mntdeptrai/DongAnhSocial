@extends(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.app')

@section('title', 'Kênh Quản Lý Trường Học - Đông Anh Smart Education')

@section('content')
<style>
    .school-admin-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 10px 30px -8px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.32, 0.72, 0, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 10px;
    }
    .school-admin-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 35px -10px rgba(79, 70, 229, 0.16);
        border-color: #a5b4fc;
    }
    .school-card-img-box {
        position: relative;
        height: 195px;
        overflow: hidden;
        border-radius: 18px;
        background: #cbd5e1;
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
            $editUrl = request()->is('admin*') ? route('admin.schools.edit', $sch->id) : route('principal.schools.edit', $sch->slug ?: $sch->id);
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="school-admin-card">
                <div class="school-card-img-box">
                    <img src="{{ $sch->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=600&q=80' }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $sch->standardized_name }}">
                    <span style="position: absolute; top: 12px; left: 12px; background: rgba(79, 70, 229, 0.92); backdrop-filter: blur(8px); color: white; padding: 6px 14px; border-radius: 14px; font-size: 0.78rem; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.12); font-family: 'Be Vietnam Pro', sans-serif;">
                        {{ $schoolTag }}
                    </span>
                </div>
                <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                    <div>
                        <h5 class="fw-bold mb-2" style="font-size: 1.1rem; line-height: 1.35; color: #0f172a; font-family: 'Be Vietnam Pro', sans-serif;">{{ $sch->standardized_name }}</h5>
                        <p class="text-secondary small mb-3" style="font-family: 'Be Vietnam Pro', sans-serif;">📍 {{ $sch->address ?: 'Xã Đông Anh, Hà Nội' }}</p>
                    </div>
                    
                    <div class="pt-3 border-top mt-2" style="border-color: #f1f5f9 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                            @if(count($sch->merged_components) > 0)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5" style="font-size: 0.76rem; border-radius: 9999px; font-weight: 600; font-family: 'Be Vietnam Pro', sans-serif;">
                                    🔗 {{ count($sch->merged_components) }} trường sáp nhập
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border px-3 py-1.5" style="font-size: 0.76rem; border-radius: 9999px; font-weight: 600; font-family: 'Be Vietnam Pro', sans-serif;">
                                    📍 1 điểm trường độc lập
                                </span>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('principal.schools.dashboard', $sch->slug ?: $sch->id) }}" class="btn btn-sm btn-success flex-grow-1 shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; font-size: 0.82rem; padding: 8px 12px; border-radius: 9999px; font-weight: 600; font-family: 'Be Vietnam Pro', sans-serif; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #ffffff;">
                                <span>📊</span> Dashboard
                            </a>
                            <a href="{{ $editUrl }}" class="btn btn-sm btn-light border flex-grow-1 shadow-sm" style="font-size: 0.82rem; padding: 8px 12px; border-radius: 9999px; font-weight: 600; color: #475569; background: #ffffff; border-color: #e2e8f0 !important; font-family: 'Be Vietnam Pro', sans-serif; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                <span>⚙️</span> Cấu hình
                            </a>
                        </div>
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
