@extends('layouts.admin')

@section('title', '➕ Thêm Sản Phẩm Trưng Bày Mới')

@section('content')
<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); padding: 24px; border-radius: 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <h1 style="font-size: 1.45rem; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>🏛️</span> Thêm Sản Phẩm Trưng Bày Mới — Chợ văn hoá Du lịch Cổ Loa
            </h1>
            <p style="color: rgba(255,255,255,0.85); margin-top: 6px; margin-bottom: 0;">Khai báo thông tin sản phẩm, quà lưu niệm & đặc sản trưng bày tại Chợ Cổ Loa</p>
        </div>
        <a href="/admin/stalls" class="btn-admin btn-admin-accent" style="padding: 10px 20px; border-radius: 10px; background-color: #ffffff; color: #0284c7; font-weight: 700; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            ⬅ Quay lại danh sách
        </a>
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
    <!-- Banner Hướng dẫn Gom nhóm sản phẩm vào Gian hàng -->
    <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 14px; padding: 14px 18px; margin-bottom: 24px; font-size: 0.84rem; color: #0369a1;">
        <div style="display: flex; align-items: flex-start; gap: 10px;">
            <span style="font-size: 1.3rem; line-height: 1;">💡</span>
            <div>
                <strong style="font-size: 0.88rem; color: #0284c7;">HƯỚNG DẪN THÊM NHIỀU SẢN PHẨM VÀO GIAN HÀNG:</strong>
                <ul style="margin: 6px 0 0 0; padding-left: 18px; line-height: 1.5;">
                    <li><strong>Để thêm sản phẩm mới vào CÙNG 1 GIAN HÀNG</strong> (VD: Thêm mặt hàng <em>Túi xách da</em>, <em>Giày dép thêu</em> vào gian <em>"Gian hàng Giày dép & Túi xách"</em>): Hãy chọn hoặc gõ lại đúng <strong>Tên Gian Hàng</strong>.</li>
                    <li><strong>Để tạo GIAN HÀNG MỚI HOÀN TOÀN</strong>: Bạn chỉ cần gõ tên Gian Hàng mới của mình.</li>
                </ul>
            </div>
        </div>
    </div>

    <form action="/admin/stalls" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="seller_name" value="Chợ Văn hóa Du lịch Cổ Loa">

        <!-- SECTION 1: DẠNG SẢN PHẨM & ĐỊA ĐIỂM -->
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-bottom: 16px; border-bottom: 1.5px solid #e0f2fe; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <span>🏛️</span> Thông Tin Gian Hàng & Sản Phẩm Cụ Thể
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
                                <option value="{{ $m->id }}" {{ old('eatery_id', $managerEatery ? $managerEatery->id : null) == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <!-- Tên Gian Hàng / Khu Trưng Bày -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">1. Tên Gian Hàng / Khu Trưng Bày <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">🏪</span>
                    <input type="text" name="stall_name" list="existing_stalls_list" required value="{{ old('stall_name', request('stall_name')) }}" class="admin-form-input" placeholder="Chọn hoặc nhập tên Gian hàng..." style="padding-left: 38px; border-radius: 10px; height: 42px; width: 100%;">
                    @if(isset($existingStallNames) && count($existingStallNames) > 0)
                        <datalist id="existing_stalls_list">
                            @foreach($existingStallNames as $sName)
                                <option value="{{ $sName }}">
                            @endforeach
                        </datalist>
                    @endif
                </div>
                <small style="color: #0284c7; font-size: 0.74rem; margin-top: 4px; display: block;">💡 Chọn Gian hàng có sẵn hoặc gõ tên mới</small>
            </div>

            <!-- Tên Mặt Hàng / Sản Phẩm -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">2. Tên Mặt Hàng / Sản Phẩm Cụ Thể <span style="color: var(--admin-danger);">*</span></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">📦</span>
                    <input type="text" name="name" required value="{{ old('name') }}" class="admin-form-input" placeholder="Ví dụ: Túi xách da thêu thủ công" style="padding-left: 38px; border-radius: 10px; height: 42px; width: 100%;">
                </div>
                <small style="color: #64748b; font-size: 0.74rem; margin-top: 4px; display: block;">Tên món hàng nằm trong Gian hàng trên</small>
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
                    <input type="text" name="price" value="{{ old('price') }}" class="admin-form-input" placeholder="Ví dụ: 50.000đ" style="padding-left: 38px; border-radius: 10px; height: 42px; width: 100%;">
                </div>
            </div>

            <!-- Đơn Vị Tính -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Đơn Vị Tính <small style="color: #64748b; font-weight: normal;">(Tùy chọn)</small></label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);">⚖️</span>
                    <input type="text" name="unit" value="{{ old('unit') }}" class="admin-form-input" placeholder="Ví dụ: Cặp, Hộp, Chiếc, Kg" style="padding-left: 38px; border-radius: 10px; height: 42px; width: 100%;">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- Upload Nhiều Ảnh -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Tải Nhiều Ảnh Sản Phẩm (File Máy tính)</label>
                <input type="file" name="images[]" accept="image/*" multiple class="admin-form-input" style="padding: 8px; border-radius: 10px; height: 42px; width: 100%;">
                <small style="color: #64748b; font-size: 0.75rem; margin-top: 4px; display: block;">💡 Nhấn giữ <strong>Ctrl</strong> hoặc <strong>Shift</strong> để chọn cùng lúc nhiều file ảnh</small>
            </div>

            <!-- URL Nhiều Ảnh -->
            <div class="admin-form-group">
                <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Hoặc Dán Link URL Ảnh Online</label>
                <textarea name="image_urls" rows="2" class="admin-form-input" style="border-radius: 10px; padding: 10px; width: 100%; font-size: 0.82rem;" placeholder="Có thể dán nhiều link ảnh (mỗi link 1 dòng):&#10;https://images.unsplash.com/photo-1...&#10;https://images.unsplash.com/photo-2..."></textarea>
            </div>
        </div>

        <!-- SECTION 3: MÔ TẢ NỘI DUNG SẢN PHẨM -->
        <div class="admin-form-group" style="margin-bottom: 28px;">
            <label class="admin-form-label" style="font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; display: block; color: var(--admin-text-main);">Mô Tả Chi Tiết Sản Phẩm & Ý Nghĩa Văn Hóa</label>
            <textarea name="description" rows="4" class="admin-form-input" style="border-radius: 10px; padding: 12px; width: 100%;" placeholder="Nhập giới thiệu chi tiết sản phẩm, nguồn gốc di sản hoặc nét đặc sắc của sản phẩm trưng bày..."></textarea>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1.5px solid var(--admin-border); padding-top: 20px;">
            <a href="/admin/stalls" class="btn-admin btn-admin-accent" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; text-decoration: none;">
                Hủy
            </a>
            <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 32px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border: none; background-color: #0284c7; color: #ffffff; cursor: pointer;">
                ✓ Lưu Sản Phẩm Trưng Bày
            </button>
        </div>
    </form>
</div>
@endsection
