@extends('layouts.seller')

@section('title', 'Hồ Sơ Cơ Sở Kinh Doanh — ' . ($businessEatery->name ?? 'Cơ sở của tôi'))

@section('content')

@php
    $b = $businessEatery ?? null;
@endphp

@if(session('success'))
    <div style="padding: 16px 24px; background: #ecfdf5; border: 1.5px solid #10b981; color: #065f46; border-radius: 16px; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(16,185,129,0.12);">
        <span style="font-size: 1.4rem;">🎉</span> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding: 16px 24px; background: #fef2f2; border: 1.5px solid #ef4444; color: #991b1b; border-radius: 16px; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(239,68,68,0.12);">
        <span style="font-size: 1.4rem;">⚠️</span> {{ session('error') }}
    </div>
@endif

<style>
.slr-profile-header {
    background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #3730a3 100%);
    border-radius: 22px;
    padding: 28px 32px;
    color: #ffffff;
    box-shadow: 0 16px 32px -8px rgba(67, 56, 202, 0.25);
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
    border-color: #6366f1;
    background: #ffffff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.slr-grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}
</style>

<!-- Profile Header Banner -->
<div class="slr-profile-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 800; color: #c7d2fe; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                🏢 HỒ SƠ PHÁP LÝ & BẢN ĐỒ SỐ
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 900; margin: 0; display: flex; align-items: center; gap: 10px;">
                <span>🏢</span> {{ $b->name ?? 'Cơ Sở Kinh Doanh / Doanh Nghiệp' }}
            </h1>
            <p style="color: rgba(255,255,255,0.85); font-size: 0.92rem; margin-top: 6px; line-height: 1.5;">
                Cập nhật thông tin chi tiết cơ sở để đồng bộ và ghim vị trí chính xác trên Bản đồ số toàn Huyện Đông Anh
            </p>
        </div>
        @if($b && $b->slug)
            <a href="/dia-diem/{{ $b->slug }}" target="_blank" style="padding: 12px 24px; border-radius: 12px; background: #ffffff; color: #4338ca; font-weight: 800; font-size: 0.9rem; text-decoration: none; display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 16px rgba(0,0,0,0.1);">
                <span>🗺️</span> Xem Trực Tiếp Trên Bản Đồ
            </a>
        @endif
    </div>
</div>

@if(!$b)
    <div class="slr-card" style="text-align: center; padding: 40px;">
        <span style="font-size: 3rem;">ℹ️</span>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: #334155; margin-top: 12px;">Tài khoản này chưa được liên kết với Cơ sở kinh doanh ngoài phố</h3>
        <p style="color: #64748b; font-size: 0.95rem; margin-top: 6px;">Bạn hiện đang quản lý gian hàng chợ. Nếu bạn có cơ sở kinh doanh đăng ký, vui lòng liên hệ Ban Quản trị để liên kết.</p>
    </div>
@else
    <form action="{{ route('seller.business-profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Card 1: Thông tin cơ bản & Pháp lý -->
        <div class="slr-card">
            <div class="slr-card-header">
                <span style="font-size: 1.3rem;">📋</span>
                <div>
                    <h2 class="slr-card-title">Thông Tin Biển Hiệu & Địa Chỉ</h2>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 2px 0 0 0;">Tên cơ sở đăng ký kinh doanh và địa chỉ hiển thị cho du khách</p>
                </div>
            </div>

            <div class="slr-grid-2">
                <div class="slr-form-group">
                    <label class="slr-label">Tên Cơ Sở Kinh Doanh / Doanh Nghiệp <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" class="slr-input" value="{{ old('name', $b->name) }}" required placeholder="Ví dụ: HỘ KINH DOANH NGUYỄN THỊ CHĂM">
                </div>

                <div class="slr-form-group">
                    <label class="slr-label">Số Điện Thoại Liên Hệ <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="phone" class="slr-input" value="{{ old('phone', $b->phone) }}" placeholder="Ví dụ: 0971167496">
                </div>
            </div>

            <div class="slr-form-group">
                <label class="slr-label">Địa Chỉ Chi Tiết (Thôn, Xã, Tuyến đường) <span style="color: #ef4444;">*</span></label>
                <input type="text" name="address" class="slr-input" value="{{ old('address', $b->address) }}" placeholder="Ví dụ: Chợ Tó, Thôn Chợ, Xã Đông Anh, Huyện Đông Anh">
            </div>
        </div>

        <!-- Card 2: Thời gian & Giới thiệu dịch vụ -->
        <div class="slr-card">
            <div class="slr-card-header">
                <span style="font-size: 1.3rem;">⏰</span>
                <div>
                    <h2 class="slr-card-title">Thời Gian Hoạt Động & Ngành Nghề</h2>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 2px 0 0 0;">Giúp khách hàng nắm rõ thời gian mở cửa và mức giá niêm yết</p>
                </div>
            </div>

            <div class="slr-grid-2">
                <div class="slr-form-group">
                    <label class="slr-label">Giờ Mở Cửa / Đóng Cửa</label>
                    <input type="text" name="opening_hours" class="slr-input" value="{{ old('opening_hours', $b->opening_hours ?? '06:00 - 21:00') }}" placeholder="Ví dụ: 06:00 - 21:00 (Hàng ngày)">
                </div>

                <div class="slr-form-group">
                    <label class="slr-label">Khoảng Giá Kinh Doanh</label>
                    <input type="text" name="price_range" class="slr-input" value="{{ old('price_range', $b->price_range ?? '20.000đ - 200.000đ') }}" placeholder="Ví dụ: 20.000đ - 200.000đ">
                </div>
            </div>

            <div class="slr-form-group">
                <label class="slr-label">Mô Tả / Giới Thiệu Cơ Sở / Ngành Nghề</label>
                <textarea name="description" rows="4" class="slr-textarea" placeholder="Giới thiệu về cơ sở kinh doanh, các mặt hàng đặc sản, dịch vụ cung cấp...">{{ old('description', $b->description) }}</textarea>
            </div>
        </div>

        <!-- Card 3: Ảnh Đại Diện / Mặt Tiền Cửa Hàng -->
        <div class="slr-card">
            <div class="slr-card-header">
                <span style="font-size: 1.3rem;">📸</span>
                <div>
                    <h2 class="slr-card-title">Hình Ảnh Đại Diện Cơ Sở</h2>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 2px 0 0 0;">Hình ảnh mặt tiền, biển hiệu hoặc không gian kinh doanh để khách dễ nhận diện</p>
                </div>
            </div>

            <div style="display: flex; gap: 24px; flex-wrap: wrap; align-items: center;">
                <div style="width: 140px; height: 140px; border-radius: 16px; overflow: hidden; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; background: #f8fafc; flex-shrink: 0;">
                    @if($b->image_path)
                        <img src="{{ $b->image_path }}" alt="{{ $b->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span style="font-size: 2.5rem; color: #94a3b8;">🏢</span>
                    @endif
                </div>

                <div style="flex: 1; min-width: 260px;">
                    <label class="slr-label">Tải Lên Ảnh Mới</label>
                    <input type="file" name="image" accept="image/*" class="slr-input" style="padding: 9px 14px;">
                    <p style="font-size: 0.78rem; color: #64748b; margin-top: 6px;">Hỗ trợ định dạng: JPG, PNG, WEBP (Dung lượng tối đa 10MB)</p>
                </div>
            </div>
        </div>

        <!-- Submit Button Panel -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <a href="/seller/dashboard" class="btn-admin" style="padding: 14px 28px; border-radius: 14px; background: #f1f5f9; color: #475569; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                Quay lại
            </a>
            <button type="submit" style="padding: 14px 36px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; font-weight: 800; font-size: 1rem; border: none; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4); display: flex; align-items: center; gap: 8px;">
                <span>💾</span> Lưu & Đồng Bộ Thông Tin Cơ Sở
            </button>
        </div>
    </form>
@endif

@endsection
