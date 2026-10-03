@extends('layouts.admin')

@section('title', '✏️ Chỉnh Sửa Sản Phẩm Trưng Bày')

@section('content')
<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); padding: 24px; border-radius: 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>✏️</span> Chỉnh Sửa Sản Phẩm: {{ $stall->name ?: $stall->stall_name }}
            </h1>
            <p style="color: rgba(255,255,255,0.85); margin-top: 6px; margin-bottom: 0;">Cập nhật thông tin sản phẩm trưng bày tại Chợ Văn hóa Du lịch Cổ Loa</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/admin/stalls/create?stall_name={{ urlencode($stall->stall_name) }}" class="btn-admin" style="padding: 10px 20px; border-radius: 10px; background-color: #10b981; color: #ffffff; font-weight: 700; text-decoration: none; box-shadow: 0 4px 12px rgba(16,185,129,0.3); display: flex; align-items: center; gap: 6px;">
                ➕ Thêm sản phẩm khác vào gian "{{ $stall->stall_name }}"
            </a>
            <a href="/admin/stalls" class="btn-admin btn-admin-accent" style="padding: 10px 20px; border-radius: 10px; background-color: #ffffff; color: #0284c7; font-weight: 700; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                ⬅ Quay lại danh sách
            </a>
        </div>
    </div>
</div>

<!-- Errors Notification Banner -->
@if ($errors->any())
    <div class="admin-alert admin-alert-warning" style="background-color: #fee2e2; border-color: #fecaca; color: #b91c1c; margin-bottom: 24px; padding: 16px; border-radius: 12px;">
        <div>
            <strong style="display: block; margin-bottom: 6px;">⚠️ Vui lòng kiểm tra lại thông tin nhập vào:</strong>
            <ul style="padding-left: 20px; font-size: 0.85rem; margin: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="admin-card" style="max-width: 900px; margin: 0 auto; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); border-radius: 20px; padding: 28px; background: #ffffff;">
    <form action="/admin/stalls/{{ $stall->id }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="seller_name" value="{{ $stall->seller_name ?: 'Chợ Văn hóa Du lịch Cổ Loa' }}">

        <!-- SECTION 1: DẠNG SẢN PHẨM & ĐỊA ĐIỂM -->
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-bottom: 16px; border-bottom: 1.5px solid #e0f2fe; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <span>🏛️</span> Đơn Vị & Tên Sản Phẩm Trưng Bày
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <!-- Địa Điểm Chợ -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Thuộc Chợ / Địa Điểm <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏢</span>
                    @if(session('user_role') === 'manager' && $managerEatery)
                        <input type="hidden" name="eatery_id" value="{{ $managerEatery->id }}">
                        <div class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem; display: flex; align-items: center; background-color: #f1f5f9; color: #0284c7; font-weight: 700; border: 1.5px solid #cbd5e1; cursor: not-allowed;">
                            {{ $managerEatery->name }} 🔒 [Cố định]
                        </div>
                    @else
                        <select name="eatery_id" required class="admin-form-input" style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 0.86rem; width: 100%;">
                            @foreach($markets as $m)
                                <option value="{{ $m->id }}" {{ old('eatery_id', $stall->eatery_id) == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <!-- Tên Gian Hàng / Khu Trưng Bày -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tên Gian Hàng / Khu Trưng Bày <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏪</span>
                    <input type="text" name="stall_name" required value="{{ old('stall_name', $stall->stall_name) }}" class="admin-form-input" placeholder="Ví dụ: Gian hàng Giày dép & Túi xách" style="padding-left: 38px; border-radius: 10px; height: 42px; width: 100%;">
                </div>
            </div>

            <!-- Tên Sản Phẩm -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tên Mặt Hàng / Sản Phẩm Cụ Thể <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">📦</span>
                    <input type="text" name="name" required value="{{ old('name', $stall->name) }}" class="admin-form-input" placeholder="Ví dụ: Túi xách da thêu thủ công" style="padding-left: 38px; border-radius: 10px; height: 42px; width: 100%;">
                </div>
            </div>
        </div>

        <!-- SECTION 2: THÔNG TIN GIÁ BÁN & HÌNH ẢNH -->
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-top: 10px; margin-bottom: 16px; border-bottom: 1.5px solid #e0f2fe; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <span>🏷️</span> Giá Niêm Yết & Hình Ảnh Trưng Bày
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Giá Bán -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Giá Bán / Niêm Yết (VNĐ) <small style="color: #64748b; font-weight: normal;">(Tùy chọn)</small></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">💰</span>
                    <input type="text" name="price" value="{{ old('price', $stall->price) }}" class="admin-form-input" placeholder="Ví dụ: 50.000đ" style="padding-left: 38px; border-radius: 10px; height: 42px; width: 100%;">
                </div>
            </div>

            <!-- Đơn Vị Tính -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Đơn Vị Tính <small style="color: #64748b; font-weight: normal;">(Tùy chọn)</small></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">⚖️</span>
                    <input type="text" name="unit" value="{{ old('unit', $stall->unit) }}" class="admin-form-input" placeholder="Ví dụ: Cặp, Hộp, Chiếc, Kg" style="padding-left: 38px; border-radius: 10px; height: 42px; width: 100%;">
                </div>
            </div>
        </div>

        @php
            $existingImages = [];
            if (!empty($stall->image_path)) {
                $trimmed = trim($stall->image_path);
                if (str_starts_with($trimmed, '[')) {
                    $decoded = json_decode($trimmed, true);
                    if (is_array($decoded)) $existingImages = array_values(array_filter($decoded));
                } elseif (str_contains($trimmed, ',')) {
                    $existingImages = array_values(array_filter(array_map('trim', explode(',', $trimmed))));
                } else {
                    $existingImages = [$trimmed];
                }
            }
        @endphp

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- Upload Nhiều Ảnh -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tải Thêm/Thay Nhiều Ảnh (File Máy tính)</label>
                <input type="file" name="images[]" accept="image/*" multiple class="admin-form-input" style="padding: 8px; border-radius: 10px; height: 42px; width: 100%;">
                <small style="color: #64748b; font-size: 0.75rem; margin-top: 4px; display: block;">💡 Nhấn giữ <strong>Ctrl</strong> hoặc <strong>Shift</strong> để chọn cùng lúc nhiều file ảnh</small>
            </div>

            <!-- URL Nhiều Ảnh -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Hoặc Dán Thêm Link URL Ảnh Online</label>
                <textarea name="image_urls" rows="2" class="admin-form-input" style="border-radius: 10px; padding: 10px; width: 100%; font-size: 0.82rem;" placeholder="Có thể dán nhiều link ảnh (mỗi link 1 dòng)..."></textarea>
            </div>
        </div>

        @if(!empty($existingImages))
            <div style="margin-bottom: 24px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <label style="font-weight: 700; font-size: 0.82rem; margin-bottom: 10px; display: block; color: #0284c7;">🖼️ Danh sách {{ count($existingImages) }} ảnh đang trưng bày của sản phẩm:</label>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    @foreach($existingImages as $idx => $imgUrl)
                        <div style="position: relative; border-radius: 10px; overflow: hidden; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                            <img src="{{ $imgUrl }}" style="width: 80px; height: 80px; object-fit: cover; display: block;" alt="Ảnh {{ $idx + 1 }}">
                            <a href="{{ $imgUrl }}" target="_blank" style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: #ffffff; text-align: center; font-size: 0.68rem; text-decoration: none; padding: 2px 0;">Xem ảnh</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- SECTION 3: MÔ TẢ NỘI DUNG SẢN PHẨM -->
        <div class="admin-form-group" style="margin-bottom: 28px;">
            <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Mô Tả Chi Tiết Sản Phẩm & Ý Nghĩa Văn Hóa</label>
            <textarea name="description" rows="4" class="admin-form-input" style="border-radius: 10px; padding: 12px; width: 100%;" placeholder="Nhập giới thiệu chi tiết sản phẩm, nguồn gốc di sản hoặc nét đặc sắc của sản phẩm trưng bày...">{{ old('description', $stall->description) }}</textarea>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1.5px solid var(--admin-border); padding-top: 20px;">
            <a href="/admin/stalls" class="btn-admin btn-admin-accent" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; text-decoration: none;">
                Hủy
            </a>
            <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 32px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border: none; background-color: #0284c7; color: #ffffff; cursor: pointer;">
                ✓ Cập Nhật Sản Phẩm
            </button>
        </div>
    </form>
</div>
@endsection
