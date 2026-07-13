@extends('layouts.admin')

@section('title', ($eatery ? '⚙️ Quản lý: ' . $eatery->name : 'Thêm địa điểm mới'))

@section('content')

<!-- Welcome Workspace Banner -->
<div class="admin-welcome-banner" style="margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 1.45rem;">🏢 {{ $eatery ? $eatery->name : 'Thêm cơ sở mới' }}</h1>
        <p>{{ $eatery ? 'Không gian làm việc & điều phối hồ sơ pháp lý cơ sở' : 'Khai báo hồ sơ ban đầu cho cơ sở kinh doanh mới' }}</p>
    </div>
    <div style="font-size: 2rem;">⚙️</div>
</div>

<!-- Errors Alert Banner -->
@if ($errors->any())
    <div class="admin-alert admin-alert-warning" style="background-color: #fee2e2; border-color: #fecaca; color: #b91c1c;">
        <div>
            <strong style="display: block; margin-bottom: 6px;">⚠️ Vui lòng hoàn thiện các trường thông tin hợp lệ:</strong>
            <ul style="padding-left: 20px; font-size: 0.85rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif



<!-- ==========================================================================
     SUB-TAB WORKSPACE SWITCHER
     ========================================================================== -->
<div class="admin-sub-tabs">
    <button type="button" class="admin-sub-tab-btn active" onclick="switchSubTab(event, 'tab-info')">
        1. Thông tin & Bản đồ
    </button>
    @if($eatery)
        @if($eatery->category->slug === 'dong-anh-food-map')
            <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-dishes')">
                2. Thực đơn món ngon ({{ $eatery->dishes->count() }})
            </button>
            <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-ocop-products')">
                🛍️ Sản phẩm OCOP & Đặc sản ({{ optional($eatery->relationLoaded('ocopProducts') ? $eatery->ocopProducts : collect())->count() }})
            </button>
        @elseif($eatery->category->slug === 'dong-anh-market')
            <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-ocop-products')">
                🛍️ Sản phẩm OCOP & Đặc sản ({{ optional($eatery->relationLoaded('ocopProducts') ? $eatery->ocopProducts : collect())->count() }})
            </button>
        @elseif($eatery->category->slug === 'stay-in-dong-anh')
            <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-rooms')">
                🛌 Quản lý phòng nghỉ ({{ $eatery->rooms->count() }})
            </button>
        @elseif($eatery->category->slug === 'wellness-care')
            <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-wellness-services')">
                🩺 Dịch vụ chăm sóc & Spa ({{ $eatery->wellnessServices->count() }})
            </button>
        @elseif($eatery->category->slug === 'smart-education-map')
            <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-education-programs')">
                🎓 Chương trình giáo dục ({{ $eatery->educationPrograms->count() }})
            </button>
        @elseif(in_array($eatery->category->slug, ['hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']))
            <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-ocop-products')">
                🛍️ Sản phẩm OCOP & Đặc sản ({{ optional($eatery->relationLoaded('ocopProducts') ? $eatery->ocopProducts : collect())->count() }})
            </button>
            <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-cultural-activities')">
                🏛️ Hoạt động văn hóa & Trải nghiệm ({{ optional($eatery->relationLoaded('culturalActivities') ? $eatery->culturalActivities : collect())->count() }})
            </button>
        @endif

        <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-photos')">
            🖼️ Ảnh thực tế ({{ optional($eatery?->relationLoaded('photos') ? $eatery->photos : collect())->count() }})
        </button>
        <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-videos')">
            3. Video Review của quán ({{ optional($eatery->relationLoaded('reviewVideos') ? $eatery->reviewVideos : collect())->count() }})
        </button>
        <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-attp')">
            4. Giấy VSATTP & Nhật ký
        </button>
        <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-contracts')">
            5. Hợp đồng & Hóa đơn
        </button>
        <button type="button" class="admin-sub-tab-btn" onclick="switchSubTab(event, 'tab-reviews')">
            6. Đánh giá từ khách hàng ({{ $eatery->reviews->count() }})
        </button>
    @endif
</div>

<!-- ==========================================================================
     TAB 1: BASIC INFO & MAP COORDINATES PICKER
     ========================================================================== -->
<div id="tab-info" class="admin-tab-section" style="display: block;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>📍</span> Hồ sơ & Định vị cơ sở GPS
            </h2>
        </div>
        
        <form action="{{ $eatery ? '/admin/eateries/' . $eatery->slug : '/admin/eateries' }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($eatery)
                @method('PUT')
            @endif

            <div class="admin-split-layout">
                
                <!-- Left Details Column -->
                <div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Tên cơ sở / quán ăn / khách sạn <span style="color: var(--admin-danger);">*</span></label>
                        <input type="text" name="name" class="admin-form-input" required placeholder="Ví dụ: Bún chả Hùng Thái Cổ Loa" value="{{ old('name', $eatery ? $eatery->name : '') }}">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Phân loại danh mục <span style="color: var(--admin-danger);">*</span></label>
                            <select name="category_id" class="admin-form-input" required>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $cat)
                                    @php
                                        $displayIcon = $cat->icon;
                                        $displayNameEn = $cat->name;
                                        $displayNameVi = $cat->name;
                                        if ($cat->slug === 'dong-anh-food-map') {
                                            $displayIcon = '🍜';
                                            $displayNameEn = 'ĐÔNG ANH FOOD MAP';
                                            $displayNameVi = 'Ẩm thực Đông Anh';
                                        } elseif ($cat->slug === 'stay-in-dong-anh') {
                                            $displayIcon = '🛌';
                                            $displayNameEn = 'Stay in Đông Anh';
                                            $displayNameVi = 'Nhà nghỉ, khách sạn, khu nghỉ dưỡng';
                                        } elseif ($cat->slug === 'wellness-care') {
                                            $displayIcon = '🩺';
                                            $displayNameEn = 'Wellness & Care';
                                            $displayNameVi = 'Y tế – chăm sóc sức khỏe – spa';
                                        } elseif ($cat->slug === 'dong-anh-market') {
                                            $displayIcon = '🛍️';
                                            $displayNameEn = 'Đông Anh Market';
                                            $displayNameVi = 'OCOP – quà tặng – đặc sản';
                                        } elseif ($cat->slug === 'smart-education-map') {
                                            $displayIcon = '🎓';
                                            $displayNameEn = 'Smart Education Map';
                                            $displayNameVi = 'Trường học';
                                        } elseif ($cat->slug === 'hanh-trinh-di-san') {
                                            $displayIcon = '⛩️';
                                            $displayNameEn = 'Heritage Journey';
                                            $displayNameVi = 'Hành trình di sản';
                                        } elseif ($cat->slug === 'discover-dong-anh-community-culture-hub') {
                                            $displayIcon = '🏛️';
                                            $displayNameEn = 'Discover Dong Anh Community & Culture Hub';
                                            $displayNameVi = 'Khám phá thiết chế văn hóa - thể thao Đông Anh';
                                        }
                                    @endphp
                                    <option value="{{ $cat->id }}" data-slug="{{ $cat->slug }}" {{ old('category_id', $eatery ? $eatery->category_id : '') == $cat->id ? 'selected' : '' }}>
                                        {{ $displayIcon }} {{ $displayNameVi }} ({{ $displayNameEn }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Địa bàn xã / Thị trấn / Thôn <span style="color: var(--admin-danger);">*</span></label>
                            <div class="custom-select-wrapper" style="position: relative;">
                                <input type="text" id="communeSearch" name="commune_name" class="admin-form-input" placeholder="🔍 Nhập tên Thôn / Tổ dân phố để tìm..." value="{{ old('commune_name', $eatery && $eatery->commune ? $eatery->commune->name : '') }}" autocomplete="off" required style="padding-right: 32px;">
                                <span class="select-clear-btn" id="communeClearBtn" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; display: none; color: var(--admin-text-muted); font-weight: bold; font-size: 1.1rem; user-select: none;">&times;</span>
                                <div id="communeDropdown" class="custom-dropdown-list glass-panel" style="display: none; position: absolute; top: 100%; left: 0; right: 0; max-height: 250px; overflow-y: auto; z-index: 1000; background: var(--admin-card-bg); border: 1px solid var(--admin-border); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-top: 4px; backdrop-filter: blur(8px);">
                                    @foreach($communes as $com)
                                        <div class="dropdown-item-com" data-id="{{ $com->id }}" data-name="{{ $com->name }}" style="padding: 10px 16px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; color: var(--admin-text-muted); transition: all 0.2s;">
                                            📍 {{ $com->name }}
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="commune_id" id="communeIdHidden" value="{{ old('commune_id', $eatery ? $eatery->commune_id : '') }}">
                            </div>
                            <style>
                                .dropdown-item-com:hover {
                                    background: rgba(13, 148, 136, 0.08) !important;
                                    color: var(--admin-primary) !important;
                                }
                                .custom-dropdown-list::-webkit-scrollbar {
                                    width: 6px;
                                }
                                .custom-dropdown-list::-webkit-scrollbar-track {
                                    background: transparent;
                                }
                                .custom-dropdown-list::-webkit-scrollbar-thumb {
                                    background: var(--admin-border);
                                    border-radius: 4px;
                                }
                                .custom-dropdown-list::-webkit-scrollbar-thumb:hover {
                                    background: var(--admin-text-muted);
                                }
                            </style>
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Địa chỉ chi tiết <span style="color: var(--admin-danger);">*</span></label>
                        <input type="text" name="address" class="admin-form-input" required placeholder="Ví dụ: Thôn Mạch Tràng, Xã Cổ Loa, Đông Anh" value="{{ old('address', $eatery ? $eatery->address : '') }}">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Số điện thoại liên hệ</label>
                            <input type="text" name="phone" class="admin-form-input" placeholder="Ví dụ: 0987654321" value="{{ old('phone', $eatery ? $eatery->phone : '') }}">
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Giờ mở cửa</label>
                            <input type="text" name="opening_hours" class="admin-form-input" placeholder="Ví dụ: 06:00 - 22:00" value="{{ old('opening_hours', $eatery ? $eatery->opening_hours : '') }}">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="admin-form-group" id="priceRangeGroup">
                            <label class="admin-form-label">Mức giá tham khảo</label>
                            <input type="text" name="price_range" id="priceRangeInput" class="admin-form-input" placeholder="Ví dụ: 30.000đ - 80.000đ" value="{{ old('price_range', $eatery ? $eatery->price_range : '') }}">
                        </div>
                        @if(session('user_role') === 'admin')
                        <div class="admin-form-group" style="display: flex; align-items: center; padding-top: 32px;">
                            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: var(--admin-text-main);">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $eatery ? $eatery->is_featured : false) ? 'checked' : '' }} style="width: 17px; height: 17px; accent-color: var(--admin-primary); cursor: pointer;">
                                ⭐ Đánh dấu địa điểm nổi bật
                            </label>
                        </div>
                        @endif
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Ảnh đại diện cơ sở (Upload)</label>
                        <input type="file" name="image" class="admin-form-input" accept="image/*" style="padding: 6px 12px;">
                        @if($eatery && $eatery->image_path)
                            <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">Ảnh hiện tại: <code>{{ $eatery->image_path }}</code></span>
                        @endif
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Hoặc Đường dẫn ảnh (URL)</label>
                        <input type="url" name="image_url" class="admin-form-input" placeholder="https://example.com/eatery.jpg" value="{{ old('image_url', $eatery && !Str::startsWith($eatery->image_path, '/uploads') ? $eatery->image_path : '') }}">
                    </div>
                </div>

                <!-- Right Map Picker Column -->
                <div>
                    <div class="admin-form-group">
                        <label class="admin-form-label" style="display: flex; justify-content: space-between;">
                            <span>🔗 Tự động lấy tọa độ qua link Google Maps</span>
                        </label>
                        <div style="display: flex; gap: 8px;">
                            <input type="url" id="gmapsUrlInput" class="admin-form-input" placeholder="Dán liên kết Google Maps của quán vào đây..." style="flex: 1;">
                            <button type="button" id="btnExtractCoords" class="btn-admin btn-admin-accent">
                                ⚡ Giải mã GPS
                            </button>
                        </div>
                        <span id="gmapsHelperText" style="font-size: 0.76rem; display: block; margin-top: 6px; font-weight: 500;"></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Vĩ độ (Latitude) <span style="color: var(--admin-danger);">*</span></label>
                            <input type="number" step="any" name="latitude" id="latInput" class="admin-form-input" required placeholder="Ví dụ: 21.1182" value="{{ old('latitude', $eatery ? number_format($eatery->latitude, 6, '.', '') : '') }}">
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Kinh độ (Longitude) <span style="color: var(--admin-danger);">*</span></label>
                            <input type="number" step="any" name="longitude" id="lngInput" class="admin-form-input" required placeholder="Ví dụ: 105.8394" value="{{ old('longitude', $eatery ? number_format($eatery->longitude, 6, '.', '') : '') }}">
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label class="admin-form-label" style="margin-bottom: 0;">🎯 Click chọn trực tiếp trên Bản đồ</label>
                            @if($eatery)
                                <button type="button" id="btnMapLockToggle" class="btn-admin" style="padding: 4px 10px; font-size: 0.72rem; border-radius: 6px; background-color: var(--admin-danger-light); color: var(--admin-danger); border: 1.5px solid rgba(239, 68, 68, 0.15); display: inline-flex; align-items: center; gap: 4px;" onclick="toggleMapLock()">
                                    🔒 Đã khóa click (Bấm để sửa vị trí)
                                </button>
                            @endif
                        </div>
                        <div class="admin-map-picker" id="pickerMap"></div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Mô tả giới thiệu ngắn</label>
                        <textarea name="description" class="admin-form-input" rows="3" placeholder="Nhập các nét độc đáo, món đặc sản, cách tìm quán..." style="resize: vertical;">{{ old('description', $eatery ? $eatery->description : '') }}</textarea>
                    </div>
                </div>

            </div>



            <!-- Submit details button -->
            <div style="border-top: 1px solid var(--admin-border); padding-top: 20px; margin-top: 10px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="/admin/dashboard" class="btn-admin btn-admin-secondary">Hủy bỏ</a>
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 28px;">
                    {{ $eatery ? '💾 Lưu thay đổi hồ sơ' : '🚀 Lưu lại & Đăng ký cơ sở' }}
                </button>
            </div>
        </form>
    </div>
</div>

@if($eatery)
<!-- ==========================================================================
     TAB 2: DIGNATURE DISHES MANAGER (THỰC ĐƠN)
     ========================================================================== -->
<div id="tab-dishes" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🍔</span> Biên Tập Thực Đơn Cơ Sở
            </h2>
        </div>

        <div class="admin-split-layout">
            
            <!-- Left: Current Dishes -->
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-text-main);">
                    📋 Danh sách món hiện tại ({{ $eatery->dishes->count() }})
                </h3>

                @if($eatery->dishes->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($eatery->dishes as $dish)
                            <div class="admin-dish-item" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                                <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
                                    <img src="{{ $dish->image_path ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=80&q=80' }}" class="admin-dish-img" style="width: 56px; height: 56px; border-radius: 10px; object-fit: cover;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <h4 class="admin-dish-title" style="margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--admin-text-main);">{{ $dish->name }}</h4>
                                            @if($dish->is_signature)
                                                <span class="admin-badge admin-badge-success" style="font-size: 0.65rem; padding: 2px 6px;">★ Đặc trưng</span>
                                            @endif
                                        </div>
                                        <span style="font-size: 0.76rem; color: var(--admin-text-muted); display: block; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $dish->description ?: 'Chưa có mô tả ngắn' }}</span>
                                        <span class="admin-dish-price" style="font-size: 0.85rem; font-weight: 800; color: var(--admin-success); display: block; margin-top: 1px;">{{ number_format($dish->price, 0, ',', '.') }}đ</span>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <!-- Toggle Signature Star Button -->
                                    <form action="/admin/dishes/{{ $dish->id }}/toggle-signature" method="POST" style="display: inline; margin: 0;">
                                        @csrf
                                        <button type="submit" style="background: transparent; border: none; padding: 4px; font-size: 1.25rem; cursor: pointer; color: {{ $dish->is_signature ? '#eab308' : '#cbd5e1' }}; transition: transform 0.2s;" title="{{ $dish->is_signature ? 'Gỡ sao nổi bật' : 'Đặt làm món nổi bật' }}" onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                                            ★
                                        </button>
                                    </form>

                                    <!-- View Details Button -->
                                    <button type="button" class="btn-admin btn-admin-secondary" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" onclick="openViewDishModal('{{ addslashes($dish->name) }}', '{{ number_format($dish->price, 0, ',', '.') }}đ', '{{ addslashes($dish->description) }}', '{{ $dish->image_path ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80' }}', '{{ $dish->is_signature ? 1 : 0 }}')">
                                        👁️ Xem
                                    </button>

                                    <!-- Edit Button -->
                                    <button type="button" class="btn-admin btn-admin-accent" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" data-dish="{{ json_encode($dish) }}" onclick="openEditDishModal(this)">
                                        ✏️ Sửa
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="/admin/dishes/{{ $dish->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa món này khỏi thực đơn?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            🗑️ Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 0; border: 1.5px dashed var(--admin-border); border-radius: 12px; color: var(--admin-text-muted);">
                        <p style="font-size: 0.88rem; margin-bottom: 4px;">Thực đơn hiện tại đang trống.</p>
                        <p style="font-size: 0.78rem;">Sử dụng form bên phải để khai báo món ngon đầu tiên!</p>
                    </div>
                @endif
            </div>

            <!-- Right: Add Dish Form -->
            <div>
                <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 6px;">
                        <span>✨</span> Thêm món ngon mới
                    </h3>

                    <form action="/admin/dishes" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">

                        <div class="admin-form-group">
                            <label class="admin-form-label">Tên món ăn <span style="color: var(--admin-danger);">*</span></label>
                            <input type="text" name="dish_name" class="admin-form-input" required placeholder="Ví dụ: Bún chả chày Mạch Tràng">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Giá bán thực tế (VNĐ) <span style="color: var(--admin-danger);">*</span></label>
                            <input type="number" name="dish_price" class="admin-form-input" required placeholder="Ví dụ: 35000">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Mô tả tóm tắt</label>
                            <textarea name="dish_description" class="admin-form-input" rows="2" placeholder="Ví dụ: Bún sợi to chuẩn truyền thống kèm chả băm nướng..." style="resize: vertical;"></textarea>
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Ảnh món ăn (Upload)</label>
                            <input type="file" name="dish_image" class="admin-form-input" accept="image/*" style="padding: 5px 12px; font-size: 0.8rem;">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Hoặc URL ảnh món ăn</label>
                            <input type="url" name="dish_image_url" class="admin-form-input" placeholder="https://example.com/dish.jpg">
                        </div>

                        <div class="admin-form-group" style="display: flex; align-items: center; margin-top: 10px; margin-bottom: 15px;">
                            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.84rem; font-weight: bold; color: var(--admin-text-main);">
                                <input type="checkbox" name="is_signature" value="1" style="width: 15px; height: 15px; accent-color: var(--admin-primary); cursor: pointer;">
                                ★ Đặt làm món đặc trưng nổi bật
                            </label>
                        </div>

                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px 0;">
                            🚀 Thêm món vào thực đơn
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@if($eatery && in_array($eatery->category->slug, ['dong-anh-market', 'dong-anh-food-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']))
<!-- ==========================================================================
     TAB: OCOP PRODUCTS MANAGER (SẢN PHẨM OCOP)
     ========================================================================== -->
<div id="tab-ocop-products" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
            <h2 class="admin-card-title" style="margin-bottom: 0; display: inline-flex; align-items: center; gap: 8px;">
                <span>🛍️</span> Quản Lý Sản Phẩm OCOP & Đặc Sản Làng Nghề
            </h2>
            <button type="button" class="btn-admin btn-admin-primary" style="padding: 8px 16px; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem;" onclick="openAddOcopProductModal()">
                ➕ Thêm sản phẩm đặc sản / OCOP mới
            </button>
        </div>

        <div style="padding: 20px;">
            <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-text-main);">
                📋 Sản phẩm hiện tại ({{ optional($eatery->relationLoaded('ocopProducts') ? $eatery->ocopProducts : collect())->count() }})
            </h3>

            @php $eateryOcopProducts = $eatery->relationLoaded('ocopProducts') ? $eatery->ocopProducts : collect(); @endphp
            @if($eateryOcopProducts->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
                    @foreach($eateryOcopProducts as $product)
                        <div class="admin-dish-item" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                            <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
                                <img src="{{ $product->image_path ?: asset('images/ocop-placeholder.png') }}" style="width: 56px; height: 56px; border-radius: 10px; object-fit: cover;">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        <h4 style="margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--admin-text-main);">{{ $product->name }}</h4>
                                        @if($product->star_rating)
                                            <span class="admin-badge admin-badge-primary" style="font-size: 0.65rem; padding: 2px 6px;">{{ $product->star_rating }}</span>
                                        @endif
                                    </div>
                                    <span style="font-size: 0.76rem; color: var(--admin-text-muted); display: block; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $product->description ?: 'Chưa có mô tả' }}</span>
                                    <span style="font-size: 0.85rem; font-weight: 800; color: var(--admin-success); display: block; margin-top: 1px;">
                                        {{ $product->price ? number_format($product->price, 0, ',', '.') . 'đ' : 'Liên hệ' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <!-- Edit Button -->
                                <button type="button" class="btn-admin btn-admin-accent" data-product="{{ json_encode($product) }}" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" onclick="openEditOcopProductModal(this)">
                                    ✏️ Sửa
                                </button>

                                <!-- Delete Button -->
                                <form action="/admin/ocop-products/{{ $product->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')" style="display: inline; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                        🗑️ Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 60px 0; border: 1.5px dashed var(--admin-border); border-radius: 12px; color: var(--admin-text-muted);">
                    <p style="font-size: 0.88rem; margin-bottom: 4px;">Chưa có sản phẩm nào được khai báo.</p>
                    <p style="font-size: 0.78rem;">Nhấn nút "Thêm sản phẩm đặc sản / OCOP mới" ở phía trên để bắt đầu!</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal thêm sản phẩm OCOP mới -->
    <div id="addOcopProductModal" class="admin-reels-overlay" style="display: none;">
        <div class="admin-card" style="width: 100%; max-width: 700px; padding: 24px; position: relative; border-radius: 16px; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.15); max-height: 90vh; overflow-y: auto;">
            <button type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--admin-text-muted); font-size: 1.25rem; cursor: pointer; z-index: 10;" onclick="closeAddOcopProductModal()">✕</button>
            
            <h3 class="admin-card-title" style="margin-bottom: 18px; font-size: 1.15rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px; color: var(--admin-primary); display: flex; align-items: center; gap: 6px;">
                <span>✨</span> Đăng Ký Sản Phẩm OCOP / Đặc Sản Mới
            </h3>
            
            <form action="/admin/ocop-products" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Tên sản phẩm/đặc sản <span style="color: var(--admin-danger);">*</span></label>
                        <input type="text" name="name" class="admin-form-input" required placeholder="Ví dụ: Bún Mạch Tràng Cổ Loa">
                    </div>

                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Giá bán (VNĐ)</label>
                        <input type="number" name="price" class="admin-form-input" placeholder="Ví dụ: 35000 (Để trống nếu liên hệ)">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Đạt chuẩn OCOP mấy sao?</label>
                        <select name="star_rating" class="admin-form-input">
                            <option value="">Không có/Chưa xếp hạng</option>
                            <option value="3 sao">⭐ 3 sao</option>
                            <option value="4 sao" selected>⭐⭐ 4 sao</option>
                            <option value="5 sao">⭐⭐⭐ 5 sao</option>
                        </select>
                    </div>

                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Ảnh sản phẩm (Upload)</label>
                        <input type="file" name="image" class="admin-form-input" accept="image/*" style="padding: 5px 12px; font-size: 0.8rem;">
                    </div>
                </div>

                <div class="admin-form-group" style="margin-bottom: 16px;">
                    <label class="admin-form-label">Hoặc URL ảnh sản phẩm</label>
                    <input type="url" name="image_url" class="admin-form-input" placeholder="https://example.com/product.jpg">
                </div>

                <div class="admin-form-group" style="margin-bottom: 16px;">
                    <label class="admin-form-label">Mô tả chi tiết</label>
                    <textarea name="description" class="admin-form-input" rows="2" placeholder="Mô tả về quy trình sản xuất, công dụng, chứng nhận..." style="resize: vertical;"></textarea>
                </div>

                <!-- Collapsible Heritage Fields for OCOP Product -->
                <div style="border: 1.5px solid rgba(212, 175, 55, 0.35); background: rgba(212, 175, 55, 0.02); border-radius: 8px; margin-top: 15px; margin-bottom: 15px; overflow: hidden;">
                    <div onclick="toggleOcopHeritageAddFields()" style="background-color: rgba(212, 175, 55, 0.08); padding: 10px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; font-weight: 800; color: #ffb300; border-bottom: 1px solid rgba(212, 175, 55, 0.15);">
                        <span style="display: flex; align-items: center; gap: 6px;">🌾 Hồ Sơ Di Sản & Chứng Nhận OCOP (Làng nghề / Đặc sản)</span>
                        <span id="ocopHeritageAddIcon" style="transition: transform 0.3s ease; transform: rotate(180deg);">▲</span>
                    </div>
                    <div id="ocopHeritageAddFields" style="padding: 14px; display: flex; background-color: #ffffff; flex-direction: column; gap: 12px;">
                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <label class="admin-form-label" style="font-size: 0.78rem;">Năm công nhận / Lịch sử di sản (Heritage Year)</label>
                            <input type="text" name="heritage_year" class="admin-form-input" style="font-size: 0.82rem; padding: 6px 10px;" placeholder="Ví dụ: Từ thời Hùng Vương / Năm 2018">
                        </div>
                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <label class="admin-form-label" style="font-size: 0.78rem;">Lịch sử hình thành & Câu chuyện (Story)</label>
                            <textarea name="story" class="admin-form-input" rows="3" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Nhập câu chuyện truyền thuyết, lịch sử lâu đời..."></textarea>
                        </div>
                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <label class="admin-form-label" style="font-size: 0.78rem;">Nghệ nhân truyền nghề / Người giữ lửa (Artisans)</label>
                            <textarea name="artisans" class="admin-form-input" rows="2" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Nhập tên nghệ nhân tiêu biểu, những chia sẻ..."></textarea>
                        </div>
                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <label class="admin-form-label" style="font-size: 0.78rem;">Sự thật thú vị / Bạn có biết? (Fun Fact)</label>
                            <textarea name="fun_fact" class="admin-form-input" rows="2" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Ví dụ: Quy trình làm thủ công 100%, không hóa chất..."></textarea>
                        </div>
                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <label class="admin-form-label" style="font-size: 0.78rem;">Nội dung thuyết minh (TTS Audio Narrative)</label>
                            <textarea name="audio_narrative" class="admin-form-input" rows="2" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Nội dung giới thiệu chi tiết bằng giọng nói để AI phát âm..."></textarea>
                        </div>
                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <label class="admin-form-label" style="font-size: 0.78rem;">Thành phần & Bí quyết (Mỗi dòng một nguyên liệu)</label>
                            <textarea name="ingredients_raw" class="admin-form-input" rows="3" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Bột gạo tẻ ngon xay ướt&#10;Thịt heo băm nhuyễn"></textarea>
                        </div>
                        <div class="admin-form-group" style="margin-bottom: 0;">
                            <label class="admin-form-label" style="font-size: 0.78rem;">Hành trình di sản / Dòng lịch sử (Định dạng: Năm | Sự kiện, mỗi dòng một mục)</label>
                            <textarea name="timeline_raw" class="admin-form-input" rows="3" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Thời An Dương Vương | Lương thực cho quân lính&#10;Năm 2021 | Đạt chứng nhận OCOP 3 sao"></textarea>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 15px;">
                    <button type="button" class="btn-admin btn-admin-secondary" style="padding: 10px 20px; font-size: 0.82rem; border-radius: 8px;" onclick="closeAddOcopProductModal()">Hủy bỏ</button>
                    <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 24px; font-size: 0.82rem; border-radius: 8px;">🚀 Đăng ký sản phẩm</button>
                </div>
            </form>
        </div>

</div>
</div>{{-- /tab-ocop-products --}}
@endif

@if($eatery && $eatery->category->slug === 'stay-in-dong-anh')
<!-- ==========================================================================
     TAB: ROOMS MANAGER (PHÒNG NGHỈ)
     ========================================================================== -->
<div id="tab-rooms" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🛌</span> Quản Lý Phòng Nghỉ & Giá Phòng
            </h2>
        </div>

        <div class="admin-split-layout">
            
            <!-- Left: Current Rooms -->
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-text-main);">
                    📋 Danh sách phòng hiện có ({{ $eatery->rooms->count() }})
                </h3>

                @if($eatery->rooms->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($eatery->rooms as $room)
                            <div class="admin-dish-item" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                                <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
                                    <img src="{{ $room->image_path ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=80&q=80' }}" style="width: 56px; height: 56px; border-radius: 10px; object-fit: cover;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <h4 style="margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--admin-text-main);">{{ $room->name }}</h4>
                                            @if($room->bed_type)
                                                <span class="admin-badge admin-badge-success" style="font-size: 0.65rem; padding: 2px 6px;">🛏️ {{ $room->bed_type }}</span>
                                            @endif
                                            <span class="admin-badge admin-badge-primary" style="font-size: 0.65rem; padding: 2px 6px;">👥 Tối đa: {{ $room->capacity }} người</span>
                                        </div>
                                        <span style="font-size: 0.76rem; color: var(--admin-text-muted); display: block; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $room->description ?: 'Chưa có mô tả' }}</span>
                                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--admin-success); display: block; margin-top: 1px;">{{ number_format($room->price, 0, ',', '.') }}đ / đêm</span>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn-admin btn-admin-accent" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" data-room="{{ json_encode($room) }}" onclick="openEditRoomModal(this)">
                                        ✏️ Sửa
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="/admin/rooms/{{ $room->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng này?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            🗑️ Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 0; border: 1.5px dashed var(--admin-border); border-radius: 12px; color: var(--admin-text-muted);">
                        <p style="font-size: 0.88rem; margin-bottom: 4px;">Danh sách phòng đang trống.</p>
                        <p style="font-size: 0.78rem;">Sử dụng form bên phải để tạo phòng mới!</p>
                    </div>
                @endif
            </div>

            <!-- Right: Add Room Form -->
            <div>
                <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 6px;">
                        <span>✨</span> Thêm phòng nghỉ mới
                    </h3>

                    <form action="/admin/rooms" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">

                        <div class="admin-form-group">
                            <label class="admin-form-label">Tên phòng/Loại phòng <span style="color: var(--admin-danger);">*</span></label>
                            <input type="text" name="name" class="admin-form-input" required placeholder="Ví dụ: Phòng Deluxe Hướng Vườn">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Giá phòng/Đêm (VNĐ) <span style="color: var(--admin-danger);">*</span></label>
                            <input type="number" name="price" class="admin-form-input" required placeholder="Ví dụ: 850000">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Loại giường</label>
                            <input type="text" name="bed_type" class="admin-form-input" placeholder="Ví dụ: 1 Giường Đôi King Size, 2 Giường Đơn...">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Sức chứa tối đa (người) <span style="color: var(--admin-danger);">*</span></label>
                            <input type="number" name="capacity" class="admin-form-input" required value="2" min="1">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Mô tả phòng & Tiện ích</label>
                            <textarea name="description" class="admin-form-input" rows="2" placeholder="Ví dụ: Có điều hòa, bồn tắm riêng, bao gồm ăn sáng..." style="resize: vertical;"></textarea>
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Ảnh phòng (Upload)</label>
                            <input type="file" name="image" class="admin-form-input" accept="image/*" style="padding: 5px 12px; font-size: 0.8rem;">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Hoặc URL ảnh phòng</label>
                            <input type="url" name="image_url" class="admin-form-input" placeholder="https://example.com/room.jpg">
                        </div>

                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px 0; margin-top: 10px;">
                            🚀 Đăng ký phòng
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endif

@if($eatery && $eatery->category->slug === 'wellness-care')
<!-- ==========================================================================
     TAB: WELLNESS SERVICES MANAGER (DỊCH VỤ Y TẾ / SPA)
     ========================================================================== -->
<div id="tab-wellness-services" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🩺</span> Quản Lý Dịch Vụ Chăm Sóc Sức Khỏe & Spa
            </h2>
        </div>

        <div class="admin-split-layout">
            
            <!-- Left: Current Services -->
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-text-main);">
                    📋 Danh sách dịch vụ hiện tại ({{ $eatery->wellnessServices->count() }})
                </h3>

                @if($eatery->wellnessServices->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($eatery->wellnessServices as $service)
                            <div class="admin-dish-item" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                                <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
                                    <img src="{{ $service->image_path ?: 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=80&q=80' }}" style="width: 56px; height: 56px; border-radius: 10px; object-fit: cover;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <h4 style="margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--admin-text-main);">{{ $service->name }}</h4>
                                            @if($service->duration)
                                                <span class="admin-badge admin-badge-success" style="font-size: 0.65rem; padding: 2px 6px;">⏱️ {{ $service->duration }}</span>
                                            @endif
                                        </div>
                                        <span style="font-size: 0.76rem; color: var(--admin-text-muted); display: block; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $service->description ?: 'Chưa có mô tả' }}</span>
                                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--admin-success); display: block; margin-top: 1px;">
                                            {{ $service->price ? number_format($service->price, 0, ',', '.') . 'đ' : 'Liên hệ/Tư vấn' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn-admin btn-admin-accent" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" data-service="{{ json_encode($service) }}" onclick="openEditWellnessServiceModal(this)">
                                        ✏️ Sửa
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="/admin/wellness-services/{{ $service->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            🗑️ Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 0; border: 1.5px dashed var(--admin-border); border-radius: 12px; color: var(--admin-text-muted);">
                        <p style="font-size: 0.88rem; margin-bottom: 4px;">Chưa ghim dịch vụ chăm sóc nào.</p>
                        <p style="font-size: 0.78rem;">Sử dụng form bên phải để thêm dịch vụ mới!</p>
                    </div>
                @endif
            </div>

            <!-- Right: Add Wellness Service Form -->
            <div>
                <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 6px;">
                        <span>✨</span> Thêm dịch vụ y tế / Spa mới
                    </h3>

                    <form action="/admin/wellness-services" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">

                        <div class="admin-form-group">
                            <label class="admin-form-label">Tên gói dịch vụ/khám bệnh <span style="color: var(--admin-danger);">*</span></label>
                            <input type="text" name="name" class="admin-form-input" required placeholder="Ví dụ: Trị liệu xông hơi thuốc nam lá dân tộc">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Giá dịch vụ (VNĐ)</label>
                            <input type="number" name="price" class="admin-form-input" placeholder="Ví dụ: 300000 (Để trống nếu liên hệ tư vấn)">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Thời gian thực hiện</label>
                            <input type="text" name="duration" class="admin-form-input" placeholder="Ví dụ: 60 phút, 1 lần điều trị...">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Mô tả dịch vụ chi tiết</label>
                            <textarea name="description" class="admin-form-input" rows="3" placeholder="Mô tả kỹ thuật, tác dụng sức khỏe..." style="resize: vertical;"></textarea>
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Ảnh mô tả dịch vụ (Upload)</label>
                            <input type="file" name="image" class="admin-form-input" accept="image/*" style="padding: 5px 12px; font-size: 0.8rem;">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Hoặc URL ảnh dịch vụ</label>
                            <input type="url" name="image_url" class="admin-form-input" placeholder="https://example.com/service.jpg">
                        </div>

                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px 0; margin-top: 10px;">
                            🚀 Thêm dịch vụ
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endif

@if($eatery && $eatery->category->slug === 'smart-education-map')
<!-- ==========================================================================
     TAB: EDUCATION PROGRAMS MANAGER (CHƯƠNG TRÌNH ĐÀO TẠO)
     ========================================================================== -->
<div id="tab-education-programs" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🎓</span> Quản Lý Chương Trình Đào Tạo & Tuyển Sinh
            </h2>
        </div>

        <div class="admin-split-layout">
            
            <!-- Left: Current Programs -->
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-text-main);">
                    📋 Các chương trình hiện tại ({{ $eatery->educationPrograms->count() }})
                </h3>

                @if($eatery->educationPrograms->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($eatery->educationPrograms as $program)
                            <div class="admin-dish-item" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                                <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
                                    <img src="{{ $program->image_path ?: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=80&q=80' }}" style="width: 56px; height: 56px; border-radius: 10px; object-fit: cover;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <h4 style="margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--admin-text-main);">{{ $program->name }}</h4>
                                            @if($program->duration)
                                                <span class="admin-badge admin-badge-success" style="font-size: 0.65rem; padding: 2px 6px;">⏱️ {{ $program->duration }}</span>
                                            @endif
                                        </div>
                                        <span style="font-size: 0.76rem; color: var(--admin-text-muted); display: block; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $program->description ?: 'Chưa có mô tả' }}</span>
                                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--admin-accent); display: block; margin-top: 1px;">💵 {{ $program->tuition_fee ?: 'Liên hệ tuyển sinh' }}</span>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn-admin btn-admin-accent" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" data-program="{{ json_encode($program) }}" onclick="openEditEducationProgramModal(this)">
                                        ✏️ Sửa
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="/admin/education-programs/{{ $program->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa chương trình đào tạo này?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            🗑️ Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 0; border: 1.5px dashed var(--admin-border); border-radius: 12px; color: var(--admin-text-muted);">
                        <p style="font-size: 0.88rem; margin-bottom: 4px;">Chưa cập nhật chương trình đào tạo nào.</p>
                        <p style="font-size: 0.78rem;">Sử dụng form bên phải để tuyển sinh lớp mới!</p>
                    </div>
                @endif
            </div>

            <!-- Right: Add Education Program Form -->
            <div>
                <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 6px;">
                        <span>✨</span> Thêm chương trình học mới
                    </h3>

                    <form action="/admin/education-programs" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">

                        <div class="admin-form-group">
                            <label class="admin-form-label">Tên chương trình/Khóa học <span style="color: var(--admin-danger);">*</span></label>
                            <input type="text" name="name" class="admin-form-input" required placeholder="Ví dụ: Lớp chất lượng cao tăng cường Tiếng Anh">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Học phí / Kỳ / Tháng</label>
                            <input type="text" name="tuition_fee" class="admin-form-input" placeholder="Ví dụ: Học phí công lập, 1.500.000đ/tháng...">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Thời gian đào tạo</label>
                            <input type="text" name="duration" class="admin-form-input" placeholder="Ví dụ: 3 năm, 6 tháng, 36 buổi...">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Chi tiết chương trình & Mục tiêu đào tạo</label>
                            <textarea name="description" class="admin-form-input" rows="3" placeholder="Ví dụ: Tăng cường tư duy độc lập, rèn luyện kỹ năng mềm..." style="resize: vertical;"></textarea>
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Ảnh tuyển sinh (Upload)</label>
                            <input type="file" name="image" class="admin-form-input" accept="image/*" style="padding: 5px 12px; font-size: 0.8rem;">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Hoặc URL ảnh khóa học</label>
                            <input type="url" name="image_url" class="admin-form-input" placeholder="https://example.com/class.jpg">
                        </div>

                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px 0; margin-top: 10px;">
                            🚀 Thêm chương trình học
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endif

@if($eatery && in_array($eatery->category->slug, ['hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']))
<!-- ==========================================================================
     TAB: CULTURAL ACTIVITIES & EXPERIENCES MANAGER (HOẠT ĐỘNG TRẢI NGHIỆM / VĂN HÓA)
     ========================================================================== -->
<div id="tab-cultural-activities" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🏛️</span> Góc Trải Nghiệm Thực Tế & Hoạt Động Văn Hóa
            </h2>
        </div>

        <div class="admin-split-layout">
            
            <!-- Left: Current Activities -->
            <div>
                @php $culturalActivities = $eatery->relationLoaded('culturalActivities') ? $eatery->culturalActivities : collect(); @endphp
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-text-main);">
                    📋 Danh sách hoạt động ({{ $culturalActivities->count() }})
                </h3>

                @if($culturalActivities->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @foreach($culturalActivities as $act)
                            <div class="admin-dish-item" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 14px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                                <div style="display: flex; gap: 14px; flex: 1; min-width: 0;">
                                    <img src="{{ $act->image_path ?: 'https://images.unsplash.com/photo-1596422846543-75c6fc18a523?auto=format&fit=crop&w=80&q=80' }}" class="admin-dish-img" style="width: 72px; height: 72px; border-radius: 10px; object-fit: cover; flex-shrink: 0;">
                                    <div style="min-width: 0; flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <h4 class="admin-dish-title" style="margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--admin-text-main);">{{ $act->name }}</h4>
                                            
                                            @if($act->type === 'experience')
                                                <span style="font-size: 0.68rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 9999px;">Trải nghiệm</span>
                                            @elseif($act->type === 'ticket')
                                                <span style="font-size: 0.68rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 9999px;">Vé vào cổng</span>
                                            @elseif($act->type === 'service')
                                                <span style="font-size: 0.68rem; font-weight: 700; background: #fef9c3; color: #a16207; padding: 2px 8px; border-radius: 9999px;">Dịch vụ</span>
                                            @else
                                                <span style="font-size: 0.68rem; font-weight: 700; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 9999px;">Khác</span>
                                            @endif
                                        </div>

                                        @if($act->price !== null && $act->price > 0)
                                            <span class="admin-dish-price" style="font-size: 0.85rem; font-weight: 800; color: var(--admin-success); display: block; margin-top: 4px;">
                                                {{ number_format($act->price, 0, ',', '.') }}đ / {{ $act->unit }}
                                            </span>
                                        @else
                                            <span class="admin-dish-price" style="font-size: 0.82rem; font-weight: 700; color: var(--admin-text-muted); display: block; margin-top: 4px;">
                                                Theo yêu cầu dâng lễ / {{ $act->unit }}
                                            </span>
                                        @endif

                                        @if($act->discount_note && $act->discount_note !== 'null')
                                            <div style="font-size: 0.74rem; color: #dd6b20; font-weight: 600; margin-top: 3px; display: flex; align-items: center; gap: 4px;">
                                                🏷️ {{ $act->discount_note }}
                                            </div>
                                        @endif

                                        @if($act->description && $act->description !== 'null')
                                            <span style="font-size: 0.78rem; color: var(--admin-text-muted); display: block; margin-top: 6px; line-height: 1.4;">
                                                {{ $act->description }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div style="display: flex; gap: 6px; align-items: center; flex-shrink: 0;">
                                    <button type="button" class="btn-admin btn-admin-accent" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" data-activity="{{ json_encode($act) }}" onclick="openEditCulturalActivityModal(this)">
                                        ✏️ Sửa
                                    </button>
                                    
                                    <form action="/admin/cultural-activities/{{ $act->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hoạt động này?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            ✕ Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 20px; border: 1.5px dashed var(--admin-border); border-radius: 12px; background: #ffffff;">
                        <span style="font-size: 2.2rem; display: block; margin-bottom: 8px;">🏛️</span>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--admin-text-muted); font-weight: 600;">Chưa có hoạt động văn hóa / trải nghiệm thực tế nào được thiết lập.</p>
                        <p style="margin: 4px 0 0 0; font-size: 0.76rem; color: var(--admin-text-muted);">Hãy thêm các hoạt động bắn nỏ, làm bánh chưng, dâng hương lễ vật ở cột bên phải.</p>
                    </div>
                @endif
            </div>

            <!-- Right: Add Cultural Activity Form -->
            <div>
                <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc; position: sticky; top: 20px;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 6px;">
                        <span>➕</span> Thêm Hoạt Động / Trải Nghiệm Mới
                    </h3>
                    
                    <form action="/admin/cultural-activities" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">
                        <input type="hidden" name="eatery_slug" value="{{ $eatery->slug }}">

                        <div class="admin-form-group">
                            <label class="admin-form-label">Tên hoạt động / Trải nghiệm *</label>
                            <input type="text" name="activity_name" class="admin-form-input" required placeholder="Ví dụ: Hoạt động bắn nỏ liên châu">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Phân loại hoạt động *</label>
                            <select name="activity_type" class="admin-form-input" required>
                                <option value="experience">Hoạt động trải nghiệm</option>
                                <option value="ticket">Vé tham quan</option>
                                <option value="service">Dịch vụ di tích</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Giá tiền (VNĐ)</label>
                            <input type="number" name="activity_price" class="admin-form-input" placeholder="Ví dụ: 1000000 (để trống nếu không có giá)">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Đơn vị tính *</label>
                            <input type="text" name="activity_unit" class="admin-form-input" required placeholder="Ví dụ: đoàn (10 người), vé, lượt dâng hương">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Lưu ý / Ưu đãi giảm giá</label>
                            <input type="text" name="activity_discount_note" class="admin-form-input" placeholder="Ví dụ: Học sinh sinh viên được giảm 50%">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label">Mô tả chi tiết hoạt động</label>
                            <textarea name="activity_description" class="admin-form-input" rows="3" placeholder="Ví dụ: Trải nghiệm thực tế bắn nỏ liên châu bằng gỗ..." style="resize: vertical;"></textarea>
                        </div>

                        <div class="admin-form-group" style="margin-bottom: 12px;">
                            <label class="admin-form-label">Tải ảnh lên</label>
                            <input type="file" name="activity_image" class="admin-form-input" accept="image/*" style="padding: 5px 12px; font-size: 0.8rem;">
                        </div>

                        <div class="admin-form-group" style="margin-bottom: 20px;">
                            <label class="admin-form-label">Hoặc URL ảnh</label>
                            <input type="url" name="activity_image_url" class="admin-form-input" placeholder="https://example.com/image.jpg">
                        </div>

                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px 0;">
                            🚀 Thêm hoạt động
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endif

<!-- ==========================================================================
     TAB: EATERY PHOTOS GALLERY
     ========================================================================== -->
<div id="tab-photos" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🖼️</span> Ảnh Thực Tế Của Cơ Sở
            </h2>
            <span style="font-size: 0.8rem; color: var(--admin-text-muted);">{{ $eatery ? $eatery->photos->count() : 0 }} ảnh đã tải lên</span>
        </div>

        {{-- Grid hiển thị ảnh hiện tại --}}
        @if($eatery && $eatery->photos->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; padding: 20px; margin-bottom: 8px;">
            @foreach($eatery->photos as $photo)
            <div style="position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; background: var(--admin-bg); border: 1.5px solid var(--admin-border); box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <img src="{{ $photo->image_path }}" alt="{{ $photo->caption ?: 'Ảnh cơ sở' }}"
                     style="width: 100%; height: 100%; object-fit: cover; display: block;"
                     onerror="this.src='{{ asset('images/ocop-placeholder.png') }}'">
                <div style="position: absolute; inset: 0; background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.7) 100%); opacity: 0; transition: opacity 0.2s;" class="photo-overlay"></div>
                @if($photo->caption)
                <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 8px; color: white; font-size: 0.72rem; opacity: 0; transition: opacity 0.2s;" class="photo-caption-overlay">{{ $photo->caption }}</div>
                @endif
                <form action="{{ route('admin.eatery-photo.destroy', $photo->id) }}" method="POST"
                      style="position: absolute; top: 6px; right: 6px;"
                      onsubmit="return confirm('Xóa ảnh này?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background: rgba(239,68,68,0.9); border: none; color: white; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3); transition: all 0.2s;" title="Xóa ảnh" onmouseover="this.style.background='rgba(220,38,38,1)'" onmouseout="this.style.background='rgba(239,68,68,0.9)'">✕</button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 30px; color: var(--admin-text-muted);">
            <div style="font-size: 3rem; margin-bottom: 8px;">📷</div>
            <p style="font-size: 0.9rem;">Chưa có ảnh thực tế nào. Hãy tải lên ảnh đầu tiên!</p>
        </div>
        @endif
    </div>

    {{-- Form upload ảnh mới --}}
    <div class="admin-card" style="margin-top: 20px;">
        <div class="admin-card-header">
            <h2 class="admin-card-title"><span>📤</span> Tải Lên Ảnh Mới</h2>
        </div>
        <form action="{{ route('admin.eatery-photo.store') }}" method="POST" enctype="multipart/form-data" style="padding: 20px;">
            @csrf
            <input type="hidden" name="eatery_id" value="{{ $eatery?->id }}">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                {{-- Upload file --}}
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--admin-text-muted); margin-bottom: 6px;">📁 Chọn file ảnh (JPEG, PNG, WebP, tối đa 5MB)</label>
                    <input type="file" name="image" id="photoFileInput" accept="image/*"
                           style="width: 100%; padding: 10px; border: 1.5px dashed var(--admin-border); border-radius: 10px; background: var(--admin-bg); color: var(--admin-text-main); font-size: 0.85rem; cursor: pointer;"
                           onchange="previewEateryPhoto(this)">
                </div>
                {{-- Hoặc URL --}}
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--admin-text-muted); margin-bottom: 6px;">🔗 Hoặc dán URL ảnh trực tiếp</label>
                    <input type="url" name="image_url" id="photoUrlInput" placeholder="https://..."
                           class="admin-form-input"
                           oninput="previewEateryPhotoUrl(this.value)">
                </div>
            </div>

            {{-- Caption --}}
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--admin-text-muted); margin-bottom: 6px;">💬 Mô tả ảnh (tùy chọn)</label>
                <input type="text" name="caption" class="admin-form-input" placeholder="VD: Không gian sân vườn thoáng mát..." maxlength="200">
            </div>

            {{-- Preview --}}
            <div id="photoPreviewBox" style="display:none; margin-bottom: 16px; text-align: center;">
                <p style="font-size: 0.8rem; color: var(--admin-text-muted); margin-bottom: 8px;">👁️ Xem trước:</p>
                <img id="photoPreviewImg" src="" alt="Preview"
                     style="max-height: 200px; max-width: 100%; border-radius: 10px; border: 1.5px solid var(--admin-border); object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 28px; border-radius: 8px; font-size: 0.88rem;">
                    📤 Tải lên ảnh
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.photo-overlay:hover, div:hover > .photo-overlay { opacity: 1 !important; }
.photo-caption-overlay { opacity: 0; transition: opacity 0.2s; }
div:hover > .photo-caption-overlay { opacity: 1 !important; }
</style>

<script>
function previewEateryPhoto(input) {
    document.getElementById('photoUrlInput').value = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('photoPreviewImg').src = e.target.result;
            document.getElementById('photoPreviewBox').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function previewEateryPhotoUrl(url) {
    document.getElementById('photoFileInput').value = '';
    if (url) {
        document.getElementById('photoPreviewImg').src = url;
        document.getElementById('photoPreviewBox').style.display = 'block';
    } else {
        document.getElementById('photoPreviewBox').style.display = 'none';
    }
}
</script>

<!-- ==========================================================================
     TAB 3: SPECIFIC EATERY VIDEO REVIEW MANAGEMENT
     ========================================================================== -->
<div id="tab-videos" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🎥</span> Quản Lý Video Review Của Cơ Sở
            </h2>
        </div>

        <div class="admin-split-layout">
            
            <!-- Left Column: Add Video Review Form -->
            <div>
                <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #f8fafc;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-accent); display: flex; align-items: center; gap: 6px;">
                        <span>🎬</span> Nhúng Video Review Mới
                    </h3>
                    
                    <form action="{{ route('admin.video.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">

                        <div class="admin-form-group">
                            <label class="admin-form-label">Tiêu đề video ngắn *</label>
                            <input type="text" name="title" required placeholder="Ví dụ: Review ăn sập bún mạch tràng..." class="admin-form-input">
                        </div>

                        <!-- Upload Type tabs inside Form -->
                        <div style="background-color: #e2e8f0; padding: 4px; border-radius: 8px; display: flex; gap: 4px; margin-bottom: 16px;">
                            <button type="button" id="uploadTabBtn-embed" onclick="toggleUploadMode('embed')" class="btn-admin btn-admin-primary" style="flex: 1; font-size: 0.75rem; padding: 6px 0; border-radius: 6px;">
                                🔗 Nhúng Link (0MB)
                            </button>
                            <button type="button" id="uploadTabBtn-file" onclick="toggleUploadMode('file')" class="btn-admin btn-admin-secondary" style="flex: 1; font-size: 0.75rem; padding: 6px 0; border-radius: 6px; background: transparent; border-color: transparent;">
                                📤 Tải Tệp từ máy
                            </button>
                        </div>

                        <!-- Embed URL (Default) -->
                        <div id="uploadContainer-embed" class="admin-form-group" style="display: block;">
                            <label class="admin-form-label">Đường dẫn video (TikTok / YouTube Shorts) *</label>
                            <input type="url" id="videoUrlInput" name="video_url" required placeholder="https://www.tiktok.com/@.../video/..." class="admin-form-input">
                            <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px; line-height: 1.4;">
                                💡 Khuyên dùng: Dán liên kết TikTok hoặc Shorts để tự động hiển thị mượt mà trên bản đồ và không tốn bộ nhớ lưu trữ!
                            </span>
                        </div>

                        <!-- Local File Upload -->
                        <div id="uploadContainer-file" class="admin-form-group" style="display: none;">
                            <label class="admin-form-label">Chọn File Video ngắn từ thiết bị *</label>
                            <input type="file" id="videoFileInput" name="video_file" accept="video/mp4,video/quicktime" class="admin-form-input" style="padding: 6px 12px;">
                            <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px; line-height: 1.4;">
                                ⚠️ Yêu cầu: Video định dạng MP4 dung lượng nhỏ hơn 20MB.
                            </span>
                        </div>

                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px 0; margin-top: 6px;">
                            🚀 Lưu video review
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Current Eatery Videos -->
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-text-main);">
                    📋 Danh sách video hiện tại ({{ optional($eatery->relationLoaded('reviewVideos') ? $eatery->reviewVideos : collect())->count() }})
                </h3>

                @php $eateryReviewVideos = $eatery->relationLoaded('reviewVideos') ? $eatery->reviewVideos : collect(); @endphp
                @if($eateryReviewVideos->count() > 0)
                    <div class="admin-table-container">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Video & Tiêu đề</th>
                                    <th>Nguồn</th>
                                    <th>Trạng thái</th>
                                    <th style="text-align: center;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eateryReviewVideos as $vid)
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="position: relative; width: 38px; height: 50px; border-radius: 6px; overflow: hidden; background: #000; flex-shrink: 0; border: 1px solid var(--admin-border); cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'" onclick="openWatchVideoModal('{{ addslashes($vid->title) }}', '{{ $vid->video_url }}', '{{ $vid->video_type }}')" title="Bấm để xem video">
                                                    <img src="{{ $vid->thumbnail_path ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=100&q=80' }}" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.85;">
                                                    <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 10px;">▶️</span>
                                                </div>
                                                <div style="display: flex; flex-direction: column; min-width: 0;">
                                                    <span style="font-weight: 700; font-size: 0.82rem; color: var(--admin-text-main); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; cursor: pointer;" onclick="openWatchVideoModal('{{ addslashes($vid->title) }}', '{{ $vid->video_url }}', '{{ $vid->video_type }}')" title="Bấm để xem video">
                                                        {{ $vid->title }}
                                                    </span>
                                                    <span style="font-size: 0.7rem; color: var(--admin-text-muted); margin-top: 1px;">
                                                        Bởi: {{ $vid->user->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($vid->video_type === 'tiktok')
                                                <span class="admin-badge" style="background-color: #0f172a; color: #38bdf8;">TikTok</span>
                                            @elseif($vid->video_type === 'youtube_shorts')
                                                <span class="admin-badge admin-badge-danger">Shorts</span>
                                            @else
                                                <span class="admin-badge admin-badge-primary">File</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($vid->status === 'approved')
                                                <span class="admin-badge admin-badge-success">Đã Duyệt</span>
                                            @elseif($vid->status === 'pending')
                                                <span class="admin-badge admin-badge-warning">Chờ Duyệt</span>
                                            @else
                                                <span class="admin-badge admin-badge-danger">Bác bỏ</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="display: inline-flex; gap: 4px; align-items: center;">
                                                @if(session('user_role') === 'admin' && $vid->status === 'pending')
                                                    <form action="{{ route('admin.video.approve', $vid->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-admin btn-admin-primary" style="padding: 4px 8px; font-size: 0.7rem; border-radius: 4px;">
                                                            Duyệt
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <!-- Watch video action -->
                                                <button type="button" class="btn-admin btn-admin-secondary" onclick="openWatchVideoModal('{{ addslashes($vid->title) }}', '{{ $vid->video_url }}', '{{ $vid->video_type }}')" style="padding: 4px 8px; font-size: 0.7rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 2px;">
                                                    👁️ Xem
                                                </button>

                                                <button type="button" class="btn-admin btn-admin-accent" onclick="openEditVideoModal('{{ $vid->id }}', '{{ addslashes($vid->title) }}', '{{ $vid->eatery_id }}', '{{ $vid->video_url }}', '{{ $vid->video_type }}')" style="padding: 4px 8px; font-size: 0.7rem; border-radius: 4px;">
                                                    Sửa
                                                </button>
                                                
                                                <form action="{{ route('admin.video.destroy', $vid->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa video review này không?')" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-admin btn-admin-danger" style="padding: 4px 8px; font-size: 0.7rem; border-radius: 4px;">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 0; border: 1.5px dashed var(--admin-border); border-radius: 12px; color: var(--admin-text-muted);">
                        <p style="font-size: 0.88rem; margin-bottom: 4px;">Quán ăn này chưa liên kết video review nào.</p>
                        <p style="font-size: 0.78rem;">Nhúng link TikTok hoặc đăng tải video mới để hiển thị mượt mà lên bản đồ!</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================================
     TAB 4: TRUST HUB - VSATTP CERTIFICATE & DAILY INSPECTION LOGS
     ========================================================================== -->
<div id="tab-attp" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🛡️</span> Hồ Sơ Vệ Sinh An Toàn Thực Phẩm
            </h2>
        </div>

        <div class="admin-split-layout">
            
            <!-- VSATTP Certificate Form -->
            <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                    <span style="display: flex; align-items: center; gap: 6px;"><span>📜</span> Giấy Chứng Nhận An Toàn VSATTP</span>
                    @if($eatery->foodSafetyCertificate)
                        <button type="button" class="btn-admin btn-admin-accent" style="padding: 4px 8px; font-size: 0.68rem; border-radius: 6px; font-weight: 700;" onclick="openViewCertModal('{{ addslashes($eatery->foodSafetyCertificate->certificate_number) }}', '{{ addslashes($eatery->foodSafetyCertificate->issued_by) }}', '{{ $eatery->foodSafetyCertificate->issued_at->format('d/m/Y') }}', '{{ $eatery->foodSafetyCertificate->expired_at->format('d/m/Y') }}', '{{ $eatery->foodSafetyCertificate->image_path }}')">👁️ Xem Giấy Phép</button>
                    @endif
                </h3>
                
                <form action="/admin/trust/certificate" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">

                    <div class="admin-form-group">
                        <label class="admin-form-label">Số chứng nhận <span style="color: var(--admin-danger);">*</span></label>
                        <input type="text" name="certificate_number" class="admin-form-input" required placeholder="Ví dụ: 124/2024/ATTP-HN" value="{{ $eatery->foodSafetyCertificate ? $eatery->foodSafetyCertificate->certificate_number : '' }}">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Cơ quan cấp chứng nhận <span style="color: var(--admin-danger);">*</span></label>
                        <input type="text" name="issued_by" class="admin-form-input" required placeholder="Chi Cục An Toàn Thực Phẩm Sở Y Tế HN..." value="{{ $eatery->foodSafetyCertificate ? $eatery->foodSafetyCertificate->issued_by : '' }}">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Ngày cấp <span style="color: var(--admin-danger);">*</span></label>
                            <input type="date" name="issued_at" class="admin-form-input" required value="{{ $eatery->foodSafetyCertificate ? $eatery->foodSafetyCertificate->issued_at->format('Y-m-d') : '' }}">
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Ngày hết hạn <span style="color: var(--admin-danger);">*</span></label>
                            <input type="date" name="expired_at" class="admin-form-input" required value="{{ $eatery->foodSafetyCertificate ? $eatery->foodSafetyCertificate->expired_at->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Ảnh chụp chứng nhận (Upload)</label>
                        <input type="file" name="image" class="admin-form-input" accept="image/*" style="padding: 5px 12px; font-size: 0.8rem;">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Hoặc Đường dẫn ảnh (URL)</label>
                        <input type="url" name="image_url" class="admin-form-input" placeholder="https://example.com/cert.jpg" value="{{ $eatery->foodSafetyCertificate ? $eatery->foodSafetyCertificate->image_path : '' }}">
                    </div>

                    <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px 0;">
                        💾 Lưu thông tin chứng nhận VSATTP
                    </button>
                </form>
            </div>

            <!-- Daily Inspection Logs -->
            <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-accent); display: flex; align-items: center; gap: 6px;">
                    <span>📅</span> Đóng Dấu Nhật Ký Hàng Ngày
                </h3>

                <form action="/admin/trust/logs" method="POST">
                    @csrf
                    <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">
                    <input type="hidden" name="log_date" value="{{ date('Y-m-d') }}">

                    <div class="admin-form-group">
                        <label class="admin-form-label">Ngày kiểm tra</label>
                        <input type="text" class="admin-form-input" disabled value="{{ date('d/m/Y') }} (Hôm nay)" style="opacity: 0.85;">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Nguồn gốc nguyên liệu nhập vào sạch *</label>
                        <input type="text" name="ingredients_origin" required class="admin-form-input" placeholder="Ví dụ: Thịt lợn sạch Liêm Hiệp, Rau HTX Vân Nội...">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Nhiệt độ bảo quản & Tình trạng đạt *</label>
                        <input type="text" name="storage_condition" required class="admin-form-input" placeholder="Ví dụ: Tủ đông -18°C và Tủ mát 4°C bảo quản đạt chuẩn...">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Người kiểm tra thực hiện *</label>
                        <input type="text" name="checker_name" required class="admin-form-input" placeholder="Họ và tên..." value="{{ session('user_name') ?: '' }}">
                    </div>

                    <button type="submit" class="btn-admin btn-admin-accent" style="width: 100%; padding: 10px 0;">
                        ✔ Xác Nhận & Đóng Dấu Nhật Ký
                    </button>
                </form>

                <!-- 3 days inspection logs history -->
                <div style="margin-top: 20px;">
                    <h4 style="font-size: 0.84rem; font-weight: 700; margin-bottom: 10px; color: var(--admin-text-main);">
                        Nhật ký đóng dấu 3 ngày gần đây:
                    </h4>
                    @if($eatery->dailyFoodLogs->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            @foreach($eatery->dailyFoodLogs->take(3) as $log)
                                <div style="padding: 10px; border: 1.5px solid var(--admin-border); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc; font-size: 0.8rem;">
                                    <div style="cursor: pointer; flex: 1;" onclick="openViewLogModal('{{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}', '{{ addslashes($log->ingredients_origin) }}', '{{ addslashes($log->storage_condition) }}', '{{ addslashes($log->checker_name) }}')">
                                        <span style="font-weight: 800; color: var(--admin-accent);">📅 {{ $log->log_date->format('d/m/Y') }}</span> - 
                                        <span style="color: var(--admin-text-main);">{{ Str::limit($log->ingredients_origin, 28) }}</span>
                                    </div>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <button type="button" class="btn-admin btn-admin-accent" style="padding: 4px 8px; font-size: 0.68rem; border-radius: 6px; font-weight: 700;" onclick="openViewLogModal('{{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}', '{{ addslashes($log->ingredients_origin) }}', '{{ addslashes($log->storage_condition) }}', '{{ addslashes($log->checker_name) }}')">👁️ Xem</button>
                                        <form action="/admin/trust/logs/{{ $log->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhật ký ngày này?')" style="display: inline; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: none; border: none; color: var(--admin-danger); cursor: pointer; font-size: 0.9rem;" title="Xóa nhật ký">🗑️</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="font-size: 0.78rem; color: var(--admin-text-muted); font-style: italic;">Chưa ghi nhận nhật ký nào gần đây.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================================
     TAB 5: TRUST HUB - SUPPLY CONTRACTS & INVOICES
     ========================================================================== -->
<div id="tab-contracts" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span>🧾</span> Hồ Sơ Hợp Đồng & Hóa Đơn Sạch
            </h2>
        </div>

        <div class="admin-split-layout">
            
            <!-- Supply Clean Contracts -->
            <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-accent); display: flex; align-items: center; gap: 6px;">
                    <span>📜</span> Hợp Đồng Cung Cấp Nguyên Liệu Sạch
                </h3>

                <!-- Upload New Contract -->
                <div style="padding: 14px; border: 1.5px solid var(--admin-border); border-radius: 8px; background-color: #f8fafc; margin-bottom: 16px;">
                    <h4 style="font-size: 0.82rem; font-weight: 700; margin-bottom: 12px; color: var(--admin-text-main);">Khai báo hợp đồng mới:</h4>
                    <form action="/admin/trust/contracts" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">
                        
                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <input type="text" name="supplier_name" required class="admin-form-input" placeholder="Tên đối tác cung cấp (HTX Vân Nội...)" style="padding: 6px 12px; font-size: 0.82rem;">
                        </div>

                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <input type="text" name="items_supplied" required class="admin-form-input" placeholder="Nguyên liệu (Rau quả hữu cơ...)" style="padding: 6px 12px; font-size: 0.82rem;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                            <input type="date" name="signed_at" required class="admin-form-input" placeholder="Ngày ký" style="padding: 6px 12px; font-size: 0.82rem;">
                            <input type="date" name="expired_at" required class="admin-form-input" placeholder="Hết hạn" style="padding: 6px 12px; font-size: 0.82rem;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                            <input type="file" name="image" class="admin-form-input" accept="image/*" style="padding: 3px; font-size: 0.72rem;">
                            <input type="url" name="image_url" class="admin-form-input" placeholder="Hoặc dán URL ảnh" style="padding: 6px 12px; font-size: 0.82rem;">
                        </div>

                        <button type="submit" class="btn-admin btn-admin-accent" style="width: 100%; padding: 6px 0; font-size: 0.8rem;">
                            ➕ Thêm Hợp Đồng Sạch
                        </button>
                    </form>
                </div>

                <!-- Contracts List -->
                @if($eatery->foodSupplyContracts->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($eatery->foodSupplyContracts as $contract)
                            <div style="padding: 10px; border: 1.5px solid var(--admin-border); border-radius: 8px; display: flex; gap: 12px; align-items: center; background-color: #ffffff;">
                                <img src="{{ $contract->image_path }}" style="width: 36px; height: 46px; object-fit: cover; border-radius: 4px; border: 1px solid var(--admin-border); cursor: pointer;" onclick="openViewContractModal('{{ addslashes($contract->supplier_name) }}', '{{ addslashes($contract->items_supplied) }}', '{{ \Carbon\Carbon::parse($contract->signed_at)->format('d/m/Y') }}', '{{ \Carbon\Carbon::parse($contract->expired_at)->format('d/m/Y') }}', '{{ $contract->image_path }}')">
                                <div style="flex: 1; min-width: 0; cursor: pointer;" onclick="openViewContractModal('{{ addslashes($contract->supplier_name) }}', '{{ addslashes($contract->items_supplied) }}', '{{ \Carbon\Carbon::parse($contract->signed_at)->format('d/m/Y') }}', '{{ \Carbon\Carbon::parse($contract->expired_at)->format('d/m/Y') }}', '{{ $contract->image_path }}')">
                                    <h5 style="margin: 0; font-weight: 700; font-size: 0.82rem; color: var(--admin-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $contract->supplier_name }}</h5>
                                    <span style="font-size: 0.7rem; color: var(--admin-accent); display: block; margin-top: 1px;">🌾 {{ $contract->items_supplied }}</span>
                                </div>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <button type="button" class="btn-admin btn-admin-accent" style="padding: 4px 8px; font-size: 0.68rem; border-radius: 6px; font-weight: 700;" onclick="openViewContractModal('{{ addslashes($contract->supplier_name) }}', '{{ addslashes($contract->items_supplied) }}', '{{ \Carbon\Carbon::parse($contract->signed_at)->format('d/m/Y') }}', '{{ \Carbon\Carbon::parse($contract->expired_at)->format('d/m/Y') }}', '{{ $contract->image_path }}')">👁️ Xem</button>
                                    <form action="/admin/trust/contracts/{{ $contract->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hợp đồng này?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: var(--admin-danger); cursor: pointer; font-size: 0.9rem; padding: 2px;" title="Xóa hợp đồng">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size: 0.78rem; color: var(--admin-text-muted); font-style: italic;">Chưa ghim hợp đồng sạch nào.</p>
                @endif
            </div>

            <!-- Purchase Clean Invoices -->
            <div style="padding: 20px; border: 1.5px solid var(--admin-border); border-radius: 12px; background-color: #ffffff;">
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 6px;">
                    <span>🧾</span> Hóa Đơn Mua Hàng Hàng Ngày
                </h3>

                <!-- Upload New Invoice -->
                <div style="padding: 14px; border: 1.5px solid var(--admin-border); border-radius: 8px; background-color: #f8fafc; margin-bottom: 16px;">
                    <h4 style="font-size: 0.82rem; font-weight: 700; margin-bottom: 12px; color: var(--admin-text-main);">Khai báo hóa đơn mới:</h4>
                    <form action="/admin/trust/invoices" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="eatery_id" value="{{ $eatery->id }}">
                        
                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <input type="text" name="supplier_name" required class="admin-form-input" placeholder="Tên đơn vị bán (Chợ rau Đông Anh...)" style="padding: 6px 12px; font-size: 0.82rem;">
                        </div>

                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <input type="text" name="items_summary" required class="admin-form-input" placeholder="Mặt hàng mua (40kg sườn lợn...)" style="padding: 6px 12px; font-size: 0.82rem;">
                        </div>

                        <div class="admin-form-group" style="margin-bottom: 10px;">
                            <input type="date" name="invoice_date" required class="admin-form-input" placeholder="Ngày mua hàng" style="padding: 6px 12px; font-size: 0.82rem;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                            <input type="file" name="image" class="admin-form-input" accept="image/*" style="padding: 3px; font-size: 0.72rem;">
                            <input type="url" name="image_url" class="admin-form-input" placeholder="Hoặc dán URL ảnh" style="padding: 6px 12px; font-size: 0.82rem;">
                        </div>

                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 6px 0; font-size: 0.8rem;">
                            ➕ Thêm Hóa Đơn
                        </button>
                    </form>
                </div>

                <!-- Invoices List -->
                @if($eatery->purchaseInvoices->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($eatery->purchaseInvoices as $invoice)
                            <div style="padding: 10px; border: 1.5px solid var(--admin-border); border-radius: 8px; display: flex; gap: 12px; align-items: center; background-color: #ffffff;">
                                <img src="{{ $invoice->image_path }}" style="width: 36px; height: 46px; object-fit: cover; border-radius: 4px; border: 1px solid var(--admin-border); filter: blur(0.5px); cursor: pointer;" onclick="openViewInvoiceModal('{{ addslashes($invoice->supplier_name) }}', '{{ addslashes($invoice->items_summary) }}', '{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}', '{{ $invoice->image_path }}')">
                                <div style="flex: 1; min-width: 0; cursor: pointer;" onclick="openViewInvoiceModal('{{ addslashes($invoice->supplier_name) }}', '{{ addslashes($invoice->items_summary) }}', '{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}', '{{ $invoice->image_path }}')">
                                    <h5 style="margin: 0; font-weight: 700; font-size: 0.82rem; color: var(--admin-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $invoice->supplier_name }}</h5>
                                    <span style="font-size: 0.7rem; color: var(--admin-text-muted); display: block; margin-top: 1px;">🧾 {{ $invoice->items_summary }}</span>
                                </div>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <button type="button" class="btn-admin btn-admin-primary" style="padding: 4px 8px; font-size: 0.68rem; border-radius: 6px; font-weight: 700;" onclick="openViewInvoiceModal('{{ addslashes($invoice->supplier_name) }}', '{{ addslashes($invoice->items_summary) }}', '{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}', '{{ $invoice->image_path }}')">👁️ Xem</button>
                                    <form action="/admin/trust/invoices/{{ $invoice->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hóa đơn này?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: var(--admin-danger); cursor: pointer; font-size: 0.9rem; padding: 2px;" title="Xóa hóa đơn">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size: 0.78rem; color: var(--admin-text-muted); font-style: italic;">Chưa có hóa đơn nào được cập nhật.</p>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================================
     TAB 6: CUSTOMER REVIEWS & FEEDBACK
     ========================================================================== -->
<div id="tab-reviews" class="admin-tab-section" style="display: none;">
    <div class="admin-card">
        <div class="admin-card-header" style="display: flex; align-items: center; justify-content: space-between;">
            <h2 class="admin-card-title">
                <span>💬</span> Đánh Giá & Phản Hồi Từ Khách Hàng
            </h2>
            <span class="admin-badge admin-badge-primary" style="font-size: 0.8rem; font-weight: 700; padding: 6px 14px;">
                ★ {{ number_format($eatery->reviews->avg('rating') ?: 5.0, 1) }} / 5.0 ({{ $eatery->reviews->count() }} lượt đánh giá)
            </span>
        </div>

        <div style="padding: 8px 0;">
            @if($eatery->reviews->count() > 0)
                <div class="admin-table-container">
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th style="width: 150px;">Khách hàng</th>
                                <th style="width: 130px;">Đánh giá</th>
                                <th>Nội dung nhận xét</th>
                                <th style="width: 130px;">Thời gian</th>
                                @if(session('user_role') === 'admin' || session('user_role') === 'seller')
                                <th style="text-align: center; width: 120px;">Thao tác</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eatery->reviews->sortByDesc('created_at') as $rev)
                                <tr>
                                    <td>
                                        <strong style="color: var(--admin-text-main); font-size: 0.88rem;">{{ $rev->user_name }}</strong>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <span style="color: var(--admin-warning); font-weight: 800; font-size: 0.95rem; letter-spacing: 1px;">
                                            @for($i = 1; $i <= 5; $i++)
                                                {{ $i <= $rev->rating ? '★' : '☆' }}
                                            @endfor
                                        </span>
                                    </td>
                                    <td>
                                        <p style="font-size: 0.86rem; color: var(--admin-text-main); line-height: 1.5; margin: 0; white-space: pre-line;">{{ $rev->comment }}</p>
                                        
                                        <!-- Hiển thị phản hồi đã có của chủ quán -->
                                        @if($rev->seller_reply)
                                        <div style="margin-top: 8px; padding: 10px 14px; background-color: #f8fafc; border-left: 3px solid var(--admin-primary); border-radius: 6px; font-size: 0.82rem;">
                                            <strong style="color: var(--admin-primary); display: flex; align-items: center; gap: 4px; margin-bottom: 4px;">💬 Phản hồi của bạn:</strong>
                                            <p style="margin: 0; color: var(--admin-text-main); font-style: italic;">{{ $rev->seller_reply }}</p>
                                        </div>
                                        @endif

                                        <!-- Form phản hồi (Ẩn mặc định) -->
                                        @if(session('user_role') === 'seller')
                                        <div id="reply-form-{{ $rev->id }}" style="display: none; margin-top: 10px; padding: 12px; border: 1.5px solid var(--admin-border); border-radius: 8px; background-color: #f8fafc;">
                                            <form action="/admin/reviews/{{ $rev->id }}/reply" method="POST">
                                                @csrf
                                                <div class="admin-form-group" style="margin-bottom: 8px;">
                                                    <label class="admin-form-label" style="font-size: 0.75rem; font-weight: 700; margin-bottom: 4px;">Nội dung phản hồi khách hàng:</label>
                                                    <textarea name="seller_reply" rows="2" class="admin-form-input" style="font-size: 0.82rem; padding: 6px 10px;" placeholder="Cảm ơn bạn đã phản hồi tốt về quán. Chúng tôi sẽ cố gắng phát huy tốt hơn nữa...">{{ $rev->seller_reply }}</textarea>
                                                </div>
                                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                                    <button type="button" class="btn-admin btn-admin-secondary" style="padding: 4px 10px; font-size: 0.72rem; border-radius: 6px;" onclick="toggleReplyForm({{ $rev->id }})">Hủy</button>
                                                    <button type="submit" class="btn-admin btn-admin-primary" style="padding: 4px 12px; font-size: 0.72rem; border-radius: 6px;">💾 Lưu phản hồi</button>
                                                </div>
                                            </form>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-size: 0.76rem; color: var(--admin-text-muted);">
                                            {{ \Carbon\Carbon::parse($rev->created_at)->format('H:i d/m/Y') }}
                                        </span>
                                    </td>
                                    @if(session('user_role') === 'admin')
                                    <td style="text-align: center;">
                                        <form action="/admin/reviews/{{ $rev->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này khỏi hệ thống?')" style="display: inline; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-admin btn-admin-danger" style="padding: 4px 8px; font-size: 0.72rem; border-radius: 4px; font-weight: 700; cursor: pointer;">
                                                🗑️ Xóa
                                            </button>
                                        </form>
                                    </td>
                                    @elseif(session('user_role') === 'seller')
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-admin {{ $rev->seller_reply ? 'btn-admin-secondary' : 'btn-admin-accent' }}" style="padding: 4px 8px; font-size: 0.72rem; border-radius: 4px; font-weight: 700; cursor: pointer;" onclick="toggleReplyForm({{ $rev->id }})">
                                            {{ $rev->seller_reply ? '✏️ Sửa PH' : '💬 Phản hồi' }}
                                        </button>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 50px 0; border: 1.5px dashed var(--admin-border); border-radius: 12px; color: var(--admin-text-muted);">
                    <p style="font-size: 1rem; margin-bottom: 6px;">💬 Quán ăn này chưa có đánh giá nào từ khách hàng.</p>
                    <p style="font-size: 0.82rem;">Hãy khuyến khích khách hàng ghé thăm và gửi đánh giá để tăng độ uy tín cho cơ sở!</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- ==========================================================================
     MODAL XEM CHI TIẾT MÓN ĂN (SaaS Luxury Layout)
     ========================================================================== -->
<div id="viewDishModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 500px; padding: 0; position: relative; border-radius: 20px; background-color: #ffffff; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15); overflow: hidden; border: none;">
        <!-- Beautiful floating close icon -->
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: rgba(15, 23, 42, 0.6); border: none; color: #ffffff; font-size: 1rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 20; backdrop-filter: blur(4px); transition: all 0.2s;" onclick="closeViewDishModal()" onmouseover="this.style.background='rgba(15, 23, 42, 0.8)'" onmouseout="this.style.background='rgba(15, 23, 42, 0.6)'">✕</button>
        
        <!-- Premium Image Banner with Gradient Overlay -->
        <div style="position: relative; height: 260px; overflow: hidden; background-color: #f1f5f9;">
            <img id="viewDishImg" src="" style="width: 100%; height: 100%; object-fit: cover;">
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.6) 100%); z-index: 5;"></div>
            
            <!-- Absolute floating signature badge -->
            <span id="viewDishBadge" class="admin-badge" style="position: absolute; top: 16px; left: 16px; font-size: 0.72rem; font-weight: 800; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); display: none; z-index: 10; border: 1px solid rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px;">★ MÓN ĐẶC TRƯNG NỔI BẬT</span>
            
            <!-- Floating dish name in banner -->
            <div style="position: absolute; bottom: 20px; left: 24px; right: 24px; z-index: 10;">
                <h3 id="viewDishName" style="font-size: 1.35rem; font-weight: 800; color: #ffffff; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.4); line-height: 1.3; letter-spacing: -0.01em;"></h3>
            </div>
        </div>

        <!-- Structured Details Panel -->
        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 18px;">
                
                <!-- Price Section (Giá bán) -->
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.7rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">💵 Giá bán niêm yết</span>
                        <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Giá thực tế tại cửa hàng</span>
                    </div>
                    <span id="viewDishPrice" style="font-size: 1.45rem; font-weight: 800; color: #10b981; letter-spacing: -0.02em;"></span>
                </div>
                
                <!-- Description Section (Mô tả hương vị) -->
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 8px; padding-left: 2px;">📝 Mô tả hương vị & Công thức</span>
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; min-height: 80px; max-height: 160px; overflow-y: auto;">
                        <p id="viewDishDesc" style="font-size: 0.88rem; color: #334155; line-height: 1.6; margin: 0; white-space: pre-line;"></p>
                    </div>
                </div>
                
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================================
     MODAL XEM CHI TIẾT HỢP ĐỒNG SẠCH (SaaS Luxury Layout)
     ========================================================================== -->
<div id="viewContractModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 500px; padding: 0; position: relative; border-radius: 20px; background-color: #ffffff; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15); overflow: hidden; border: none;">
        <!-- Beautiful floating close icon -->
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: rgba(15, 23, 42, 0.6); border: none; color: #ffffff; font-size: 1rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 20; backdrop-filter: blur(4px); transition: all 0.2s;" onclick="closeViewContractModal()" onmouseover="this.style.background='rgba(15, 23, 42, 0.8)'" onmouseout="this.style.background='rgba(15, 23, 42, 0.6)'">✕</button>
        
        <!-- Premium Image Banner with Gradient Overlay -->
        <div style="position: relative; height: 260px; overflow: hidden; background-color: #f1f5f9;">
            <img id="viewContractImg" src="" style="width: 100%; height: 100%; object-fit: cover;">
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.6) 100%); z-index: 5;"></div>
            
            <div style="position: absolute; bottom: 20px; left: 24px; right: 24px; z-index: 10;">
                <span class="admin-badge" style="font-size: 0.68rem; font-weight: 800; background: #4f46e5; color: #ffffff; margin-bottom: 6px; padding: 4px 10px; border-radius: 12px; display: inline-block;">📜 HỢP ĐỒNG CUNG CẤP</span>
                <h3 id="viewContractSupplier" style="font-size: 1.3rem; font-weight: 800; color: #ffffff; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.4); line-height: 1.3;"></h3>
            </div>
        </div>

        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                <!-- Items Supplied -->
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">🌾 Danh mục nguyên liệu sạch</span>
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.88rem; font-weight: 600; color: var(--admin-text-main);">
                        <span id="viewContractItems"></span>
                    </div>
                </div>

                <!-- Sign & Expiry Dates -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px;">
                        <span style="font-size: 0.68rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">📅 Ngày ký kết</span>
                        <span id="viewContractSigned" style="font-size: 0.85rem; font-weight: 700; color: var(--admin-text-main);"></span>
                    </div>
                    <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 10px 14px;">
                        <span style="font-size: 0.68rem; font-weight: 800; color: #b45309; text-transform: uppercase; display: block; margin-bottom: 2px;">⏳ Ngày hết hạn</span>
                        <span id="viewContractExpired" style="font-size: 0.85rem; font-weight: 700; color: #b45309;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     MODAL XEM CHI TIẾT HÓA ĐƠN MUA HÀNG (SaaS Luxury Layout)
     ========================================================================== -->
<div id="viewInvoiceModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 500px; padding: 0; position: relative; border-radius: 20px; background-color: #ffffff; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15); overflow: hidden; border: none;">
        <!-- Beautiful floating close icon -->
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: rgba(15, 23, 42, 0.6); border: none; color: #ffffff; font-size: 1rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 20; backdrop-filter: blur(4px); transition: all 0.2s;" onclick="closeViewInvoiceModal()" onmouseover="this.style.background='rgba(15, 23, 42, 0.8)'" onmouseout="this.style.background='rgba(15, 23, 42, 0.6)'">✕</button>
        
        <!-- Premium Image Banner with Gradient Overlay -->
        <div style="position: relative; height: 260px; overflow: hidden; background-color: #f1f5f9;">
            <img id="viewInvoiceImg" src="" style="width: 100%; height: 100%; object-fit: cover;">
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.6) 100%); z-index: 5;"></div>
            
            <div style="position: absolute; bottom: 20px; left: 24px; right: 24px; z-index: 10;">
                <span class="admin-badge" style="font-size: 0.68rem; font-weight: 800; background: #10b981; color: #ffffff; margin-bottom: 6px; padding: 4px 10px; border-radius: 12px; display: inline-block;">🧾 HÓA ĐƠN MUA HÀNG</span>
                <h3 id="viewInvoiceSupplier" style="font-size: 1.3rem; font-weight: 800; color: #ffffff; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.4); line-height: 1.3;"></h3>
            </div>
        </div>

        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                <!-- Summary -->
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">🛒 Chi tiết thực phẩm nhập mua</span>
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.88rem; font-weight: 600; color: var(--admin-text-main);">
                        <span id="viewInvoiceSummary"></span>
                    </div>
                </div>

                <!-- Date -->
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                    <span style="font-size: 0.68rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">📅 Ngày giao dịch / xuất phiếu</span>
                    <span id="viewInvoiceDate" style="font-size: 0.88rem; font-weight: 700; color: var(--admin-text-main);"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     MODAL XEM CHI TIẾT GIẤY PHÉP VSATTP (SaaS Luxury Layout)
     ========================================================================== -->
<div id="viewCertModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 500px; padding: 0; position: relative; border-radius: 20px; background-color: #ffffff; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15); overflow: hidden; border: none;">
        <!-- Beautiful floating close icon -->
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: rgba(15, 23, 42, 0.6); border: none; color: #ffffff; font-size: 1rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 20; backdrop-filter: blur(4px); transition: all 0.2s;" onclick="closeViewCertModal()" onmouseover="this.style.background='rgba(15, 23, 42, 0.8)'" onmouseout="this.style.background='rgba(15, 23, 42, 0.6)'">✕</button>
        
        <!-- Premium Image Banner with Gradient Overlay -->
        <div style="position: relative; height: 260px; overflow: hidden; background-color: #f1f5f9;">
            <img id="viewCertImg" src="" style="width: 100%; height: 100%; object-fit: cover;">
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.6) 100%); z-index: 5;"></div>
            
            <div style="position: absolute; bottom: 20px; left: 24px; right: 24px; z-index: 10;">
                <span class="admin-badge" style="font-size: 0.68rem; font-weight: 800; background: #10b981; color: #ffffff; margin-bottom: 6px; padding: 4px 10px; border-radius: 12px; display: inline-block;">🛡️ CHỨNG NHẬN VSATTP</span>
                <h3 id="viewCertNumber" style="font-size: 1.3rem; font-weight: 800; color: #ffffff; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.4); line-height: 1.3;"></h3>
            </div>
        </div>

        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                <!-- Issued By -->
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">🏛️ Cơ quan cấp chứng nhận</span>
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.88rem; font-weight: 600; color: var(--admin-text-main);">
                        <span id="viewCertIssuedBy"></span>
                    </div>
                </div>

                <!-- Sign & Expiry Dates -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px;">
                        <span style="font-size: 0.68rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">📅 Ngày cấp giấy</span>
                        <span id="viewCertIssuedAt" style="font-size: 0.85rem; font-weight: 700; color: var(--admin-text-main);"></span>
                    </div>
                    <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 10px 14px;">
                        <span style="font-size: 0.68rem; font-weight: 800; color: #b45309; text-transform: uppercase; display: block; margin-bottom: 2px;">⏳ Ngày hết hạn</span>
                        <span id="viewCertExpiredAt" style="font-size: 0.85rem; font-weight: 700; color: #b45309;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     MODAL XEM CHI TIẾT NHẬT KÝ HÀNG NGÀY (SaaS Luxury Layout)
     ========================================================================== -->
<div id="viewLogModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 500px; padding: 0; position: relative; border-radius: 20px; background-color: #ffffff; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15); overflow: hidden; border: none;">
        <!-- Beautiful floating close icon -->
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: rgba(15, 23, 42, 0.6); border: none; color: #ffffff; font-size: 1rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 20; backdrop-filter: blur(4px); transition: all 0.2s;" onclick="closeViewLogModal()" onmouseover="this.style.background='rgba(15, 23, 42, 0.8)'" onmouseout="this.style.background='rgba(15, 23, 42, 0.6)'">✕</button>
        
        <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 32px 24px; color: #ffffff; position: relative;">
            <span class="admin-badge" style="font-size: 0.68rem; font-weight: 800; background: #4f46e5; color: #ffffff; margin-bottom: 8px; padding: 4px 10px; border-radius: 12px; display: inline-block;">📅 NHẬT KÝ AN TOÀN THỰC PHẨM</span>
            <h3 id="viewLogDate" style="font-size: 1.45rem; font-weight: 800; color: #ffffff; margin: 0;"></h3>
        </div>

        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 18px;">
                <!-- Ingredients Origin -->
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">🌾 Nguồn gốc nguyên liệu nhập sạch</span>
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; font-size: 0.88rem; font-weight: 600; color: var(--admin-text-main); line-height: 1.5;">
                        <span id="viewLogOrigin"></span>
                    </div>
                </div>

                <!-- Storage Condition -->
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">❄️ Nhiệt độ bảo quản & Tình trạng đạt</span>
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; font-size: 0.88rem; font-weight: 600; color: var(--admin-text-main); line-height: 1.5;">
                        <span id="viewLogStorage"></span>
                    </div>
                </div>

                <!-- Checker -->
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.68rem; font-weight: 800; color: #166534; text-transform: uppercase; display: block; margin-bottom: 2px;">👤 Người kiểm tra thực hiện</span>
                        <span id="viewLogChecker" style="font-size: 0.9rem; font-weight: 700; color: #166534;"></span>
                    </div>
                    <span style="font-size: 1.25rem;">✅</span>
                </div>

                <!-- Daily Invoices Images -->
                <div id="viewLogInvoicesSection" style="display: none;">
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">🧾 Hóa đơn mua hàng sạch ngày này</span>
                    <div id="viewLogInvoicesList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;">
                        <!-- Điền bằng JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     MODAL CHỈNH SỬA SẢN PHẨM OCOP
     ========================================================================== -->
<div id="editOcopProductModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 600px; padding: 24px; position: relative; border-radius: 16px; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--admin-text-muted); font-size: 1.25rem; cursor: pointer; z-index: 10;" onclick="closeEditOcopProductModal()">✕</button>
        
        <h3 class="admin-card-title" style="margin-bottom: 18px; font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <span>✏️</span> Cập Nhật Sản Phẩm OCOP / Đặc Sản
        </h3>
        
        <form id="editOcopProductForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="admin-form-group">
                <label class="admin-form-label">Tên sản phẩm <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" id="editOcopProductNameInput" name="name" required class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Giá bán (VNĐ)</label>
                <input type="number" id="editOcopProductPriceInput" name="price" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Đạt chuẩn OCOP mấy sao?</label>
                <select id="editOcopProductStarInput" name="star_rating" class="admin-form-input">
                    <option value="">Không có/Chưa xếp hạng</option>
                    <option value="3 sao">⭐ 3 sao</option>
                    <option value="4 sao">⭐⭐ 4 sao</option>
                    <option value="5 sao">⭐⭐⭐ 5 sao</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Mô tả chi tiết</label>
                <textarea id="editOcopProductDescInput" name="description" rows="3" class="admin-form-input"></textarea>
            </div>

            <!-- Collapsible Heritage Fields for Editing OCOP Product -->
            <div style="border: 1.5px solid rgba(212, 175, 55, 0.35); background: rgba(212, 175, 55, 0.02); border-radius: 8px; margin-top: 15px; margin-bottom: 15px; overflow: hidden;">
                <div onclick="toggleOcopHeritageEditFields()" style="background-color: rgba(212, 175, 55, 0.08); padding: 10px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; font-weight: 800; color: #ffb300; border-bottom: 1px solid rgba(212, 175, 55, 0.15);">
                    <span style="display: flex; align-items: center; gap: 6px;">🌾 Hồ Sơ Di Sản & Chứng Nhận OCOP (Làng nghề / Đặc sản)</span>
                    <span id="ocopHeritageEditIcon" style="transition: transform 0.3s ease; transform: rotate(180deg);">▲</span>
                </div>
                <div id="ocopHeritageEditFields" style="padding: 14px; display: flex; background-color: #ffffff; flex-direction: column; gap: 12px;">
                    <div class="admin-form-group" style="margin-bottom: 10px;">
                        <label class="admin-form-label" style="font-size: 0.78rem;">Năm công nhận / Lịch sử di sản (Heritage Year)</label>
                        <input type="text" id="editOcopProductHeritageYearInput" name="heritage_year" class="admin-form-input" style="font-size: 0.82rem; padding: 6px 10px;" placeholder="Ví dụ: Từ thời Hùng Vương / Năm 2018">
                    </div>
                    <div class="admin-form-group" style="margin-bottom: 10px;">
                        <label class="admin-form-label" style="font-size: 0.78rem;">Lịch sử hình thành & Câu chuyện (Story)</label>
                        <textarea id="editOcopProductStoryInput" name="story" class="admin-form-input" rows="3" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Nhập câu chuyện truyền thuyết, lịch sử lâu đời..."></textarea>
                    </div>
                    <div class="admin-form-group" style="margin-bottom: 10px;">
                        <label class="admin-form-label" style="font-size: 0.78rem;">Nghệ nhân truyền nghề / Người giữ lửa (Artisans)</label>
                        <textarea id="editOcopProductArtisansInput" name="artisans" class="admin-form-input" rows="2" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Nhập tên nghệ nhân tiêu biểu, những chia sẻ..."></textarea>
                    </div>
                    <div class="admin-form-group" style="margin-bottom: 10px;">
                        <label class="admin-form-label" style="font-size: 0.78rem;">Sự thật thú vị / Bạn có biết? (Fun Fact)</label>
                        <textarea id="editOcopProductFunFactInput" name="fun_fact" class="admin-form-input" rows="2" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Ví dụ: Quy trình làm thủ công 100%, không hóa chất..."></textarea>
                    </div>
                    <div class="admin-form-group" style="margin-bottom: 10px;">
                        <label class="admin-form-label" style="font-size: 0.78rem;">Nội dung thuyết minh (TTS Audio Narrative)</label>
                        <textarea id="editOcopProductAudioNarrativeInput" name="audio_narrative" class="admin-form-input" rows="2" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Nội dung giới thiệu chi tiết bằng giọng nói để AI phát âm..."></textarea>
                    </div>
                    <div class="admin-form-group" style="margin-bottom: 10px;">
                        <label class="admin-form-label" style="font-size: 0.78rem;">Thành phần & Bí quyết (Mỗi dòng một nguyên liệu)</label>
                        <textarea id="editOcopProductIngredientsInput" name="ingredients_raw" class="admin-form-input" rows="3" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Bột gạo tẻ ngon xay ướt&#10;Thịt heo băm nhuyễn"></textarea>
                    </div>
                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label" style="font-size: 0.78rem;">Hành trình di sản / Dòng lịch sử (Định dạng: Năm | Sự kiện, mỗi dòng một mục)</label>
                        <textarea id="editOcopProductTimelineInput" name="timeline_raw" class="admin-form-input" rows="3" style="font-size: 0.82rem; padding: 6px 10px; resize: vertical;" placeholder="Thời An Dương Vương | Lương thực cho quân lính&#10;Năm 2021 | Đạt chứng nhận OCOP 3 sao"></textarea>
                    </div>
                </div>
            </div>

            <div class="admin-form-group" style="margin-bottom: 12px;">
                <label class="admin-form-label">Chọn File Ảnh mới</label>
                <input type="file" name="image" accept="image/*" class="admin-form-input" style="padding: 6px 12px;">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Hoặc dán URL ảnh mới</label>
                <input type="url" id="editOcopProductImageUrlInput" name="image_url" class="admin-form-input">
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" class="btn-admin btn-admin-secondary" style="padding: 10px 20px; font-size: 0.82rem; border-radius: 8px;" onclick="closeEditOcopProductModal()">Hủy bỏ</button>
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 24px; font-size: 0.82rem; border-radius: 8px;">💾 Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     MODAL CHỈNH SỬA PHÒNG NGHỈ
     ========================================================================== -->
<div id="editRoomModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 480px; padding: 24px; position: relative; border-radius: 16px; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--admin-text-muted); font-size: 1.25rem; cursor: pointer; z-index: 10;" onclick="closeEditRoomModal()">✕</button>
        
        <h3 class="admin-card-title" style="margin-bottom: 18px; font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <span>✏️</span> Cập Nhật Thông Tin Phòng Nghỉ
        </h3>
        
        <form id="editRoomForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="admin-form-group">
                <label class="admin-form-label">Tên phòng/Loại phòng <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" id="editRoomNameInput" name="name" required class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Giá phòng/Đêm (VNĐ) <span style="color: var(--admin-danger);">*</span></label>
                <input type="number" id="editRoomPriceInput" name="price" required class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Loại giường</label>
                <input type="text" id="editRoomBedInput" name="bed_type" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Sức chứa tối đa (người) <span style="color: var(--admin-danger);">*</span></label>
                <input type="number" id="editRoomCapacityInput" name="capacity" required min="1" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Mô tả phòng & Tiện ích</label>
                <textarea id="editRoomDescInput" name="description" rows="2" class="admin-form-input"></textarea>
            </div>

            <div class="admin-form-group" style="margin-bottom: 12px;">
                <label class="admin-form-label">Chọn File Ảnh mới</label>
                <input type="file" name="image" accept="image/*" class="admin-form-input" style="padding: 6px 12px;">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Hoặc dán URL ảnh mới</label>
                <input type="url" id="editRoomImageUrlInput" name="image_url" class="admin-form-input">
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" class="btn-admin btn-admin-secondary" style="padding: 10px 20px; font-size: 0.82rem; border-radius: 8px;" onclick="closeEditRoomModal()">Hủy bỏ</button>
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 24px; font-size: 0.82rem; border-radius: 8px;">💾 Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     MODAL CHỈNH SỬA DỊCH VỤ CHĂM SÓC / SPA
     ========================================================================== -->
<div id="editWellnessServiceModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 480px; padding: 24px; position: relative; border-radius: 16px; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--admin-text-muted); font-size: 1.25rem; cursor: pointer; z-index: 10;" onclick="closeEditWellnessServiceModal()">✕</button>
        
        <h3 class="admin-card-title" style="margin-bottom: 18px; font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <span>✏️</span> Cập Nhật Dịch Vụ Chăm Sóc Sức Khỏe / Spa
        </h3>
        
        <form id="editWellnessServiceForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="admin-form-group">
                <label class="admin-form-label">Tên gói dịch vụ <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" id="editWellnessServiceNameInput" name="name" required class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Giá dịch vụ (VNĐ)</label>
                <input type="number" id="editWellnessServicePriceInput" name="price" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Thời gian thực hiện</label>
                <input type="text" id="editWellnessServiceDurationInput" name="duration" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Mô tả dịch vụ chi tiết</label>
                <textarea id="editWellnessServiceDescInput" name="description" rows="3" class="admin-form-input"></textarea>
            </div>

            <div class="admin-form-group" style="margin-bottom: 12px;">
                <label class="admin-form-label">Chọn File Ảnh mới</label>
                <input type="file" name="image" accept="image/*" class="admin-form-input" style="padding: 6px 12px;">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Hoặc dán URL ảnh mới</label>
                <input type="url" id="editWellnessServiceImageUrlInput" name="image_url" class="admin-form-input">
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" class="btn-admin btn-admin-secondary" style="padding: 10px 20px; font-size: 0.82rem; border-radius: 8px;" onclick="closeEditWellnessServiceModal()">Hủy bỏ</button>
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 24px; font-size: 0.82rem; border-radius: 8px;">💾 Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     MODAL CHỈNH SỬA CHƯƠNG TRÌNH ĐÀO TẠO
     ========================================================================== -->
<div id="editEducationProgramModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 480px; padding: 24px; position: relative; border-radius: 16px; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--admin-text-muted); font-size: 1.25rem; cursor: pointer; z-index: 10;" onclick="closeEditEducationProgramModal()">✕</button>
        
        <h3 class="admin-card-title" style="margin-bottom: 18px; font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <span>✏️</span> Cập Nhật Chương Trình Đào Tạo & Tuyển Sinh
        </h3>
        
        <form id="editEducationProgramForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="admin-form-group">
                <label class="admin-form-label">Tên chương trình/Khóa học <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" id="editEducationProgramNameInput" name="name" required class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Học phí / Kỳ / Tháng</label>
                <input type="text" id="editEducationProgramTuitionInput" name="tuition_fee" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Thời gian đào tạo</label>
                <input type="text" id="editEducationProgramDurationInput" name="duration" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Chi tiết chương trình & Mục tiêu đào tạo</label>
                <textarea id="editEducationProgramDescInput" name="description" rows="3" class="admin-form-input"></textarea>
            </div>

            <div class="admin-form-group" style="margin-bottom: 12px;">
                <label class="admin-form-label">Chọn File Ảnh mới</label>
                <input type="file" name="image" accept="image/*" class="admin-form-input" style="padding: 6px 12px;">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Hoặc dán URL ảnh mới</label>
                <input type="url" id="editEducationProgramImageUrlInput" name="image_url" class="admin-form-input">
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" class="btn-admin btn-admin-secondary" style="padding: 10px 20px; font-size: 0.82rem; border-radius: 8px;" onclick="closeEditEducationProgramModal()">Hủy bỏ</button>
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 24px; font-size: 0.82rem; border-radius: 8px;">💾 Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     MODAL CHỈNH SỬA MÓN ĂN
     ========================================================================== -->
<div id="editDishModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 480px; padding: 24px; position: relative; border-radius: 16px; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--admin-text-muted); font-size: 1.25rem; cursor: pointer; z-index: 10;" onclick="closeEditDishModal()">✕</button>
        
        <h3 class="admin-card-title" style="margin-bottom: 18px; font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <span>✏️</span> Cập Nhật Thông Tin Món Ăn
        </h3>
        
        <form id="editDishForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="admin-form-group">
                <label class="admin-form-label">Tên món ăn <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" id="editDishNameInput" name="dish_name" required placeholder="Ví dụ: Bún chả chày..." class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Giá bán thực tế (VNĐ) <span style="color: var(--admin-danger);">*</span></label>
                <input type="number" id="editDishPriceInput" name="dish_price" required placeholder="Ví dụ: 35000" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Mô tả tóm tắt</label>
                <textarea id="editDishDescInput" name="dish_description" rows="2" class="admin-form-input" placeholder="Ví dụ: Mô tả hương vị món ăn..."></textarea>
            </div>

            <div class="admin-form-group" style="margin-bottom: 12px;">
                <label class="admin-form-label">Chọn File Ảnh mới</label>
                <input type="file" name="dish_image" accept="image/*" class="admin-form-input" style="padding: 6px 12px;">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Hoặc dán URL ảnh mới</label>
                <input type="url" id="editDishImageUrlInput" name="dish_image_url" placeholder="https://example.com/image.jpg" class="admin-form-input">
            </div>

            <div class="admin-form-group" style="display: flex; align-items: center; margin-top: 10px; margin-bottom: 18px;">
                <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.84rem; font-weight: bold; color: var(--admin-text-main);">
                    <input type="checkbox" id="editDishSignatureInput" name="is_signature" value="1" style="width: 15px; height: 15px; accent-color: var(--admin-primary); cursor: pointer;">
                    ★ Đặt làm món đặc trưng nổi bật
                </label>
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" class="btn-admin btn-admin-secondary" style="padding: 10px 20px; font-size: 0.82rem; border-radius: 8px;" onclick="closeEditDishModal()">Hủy bỏ</button>
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 24px; font-size: 0.82rem; border-radius: 8px;">💾 Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     MODAL SỬA VIDEO REVIEW TIỆN ÍCH DÀNH CHO CƠ SỞ
     ========================================================================== -->
<div id="editVideoModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 460px; padding: 24px; position: relative; border-radius: 12px; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--admin-text-muted); font-size: 1.15rem; cursor: pointer;" onclick="closeEditVideoModal()">✕</button>
        
        <h3 class="admin-card-title" style="margin-bottom: 18px; font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <span>✏️</span> Cập Nhật Video Review
        </h3>
        
        <form id="editVideoForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="admin-form-group">
                <label class="admin-form-label">Tiêu đề video *</label>
                <input type="text" id="editVideoTitle" name="title" required placeholder="Ví dụ: Ăn sập chợ Đông Anh..." class="admin-form-input">
            </div>

            <!-- Pre-filled eatery input hidden -->
            @if($eatery)
                <input type="hidden" id="editVideoEateryId" name="eatery_id" value="{{ $eatery->id }}">
            @endif

            <!-- Edit Upload Type Tabs -->
            <div style="background-color: #e2e8f0; padding: 4px; border-radius: 8px; display: flex; gap: 4px; border: 1px solid var(--admin-border); margin-bottom: 16px;">
                <button type="button" id="editTabBtn-embed" onclick="toggleEditUploadMode('embed')" class="btn-admin btn-admin-accent" style="flex: 1; font-size: 0.78rem; padding: 6px 0; border-radius: 6px;">
                    🔗 Nhúng Link
                </button>
                <button type="button" id="editTabBtn-file" onclick="toggleEditUploadMode('file')" class="btn-admin btn-admin-secondary" style="flex: 1; font-size: 0.78rem; padding: 6px 0; border-radius: 6px; background: transparent; border-color: transparent;">
                    📤 Tải Video mới
                </button>
            </div>

            <!-- Edit Embed Section -->
            <div id="editContainer-embed" class="admin-form-group" style="display: block;">
                <label class="admin-form-label">Đường dẫn Video (TikTok / Shorts) *</label>
                <input type="url" id="editVideoUrlInput" name="video_url" placeholder="https://www.tiktok.com/..." class="admin-form-input">
            </div>

            <!-- Edit File Upload Section -->
            <div id="editContainer-file" class="admin-form-group" style="display: none;">
                <label class="admin-form-label">Chọn File Video mới</label>
                <input type="file" id="editVideoFileInput" name="video_file" accept="video/mp4" class="admin-form-input" style="padding: 6px 12px;">
                <span style="font-size: 0.72rem; color: var(--admin-text-muted); display: block; margin-top: 5px; line-height: 1.4;">
                    ⚠️ Để trống nếu bạn muốn giữ nguyên video cũ.
                </span>
            </div>

            <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px 0; margin-top: 8px;">
                💾 Lưu Thay Đổi
            </button>
        </form>
    </div>
</div>

<!-- ==========================================================================
     MODAL XEM TRỰC TIẾP VIDEO REVIEW
     ========================================================================== -->
<div id="watchVideoModal" class="admin-reels-overlay" style="display: none; background: rgba(8, 5, 18, 0.85); backdrop-filter: blur(15px); z-index: 11000;">
    <div class="admin-card" style="width: 100%; max-width: 420px; padding: 24px; position: relative; border-radius: 28px; background: rgba(20, 15, 38, 0.75); backdrop-filter: blur(25px); box-shadow: 0 30px 80px rgba(0,0,0,0.9), 0 0 40px rgba(240, 78, 35, 0.25), inset 0 1px 0 rgba(255,255,255,0.2); overflow: hidden; border: 1.5px solid rgba(255,255,255,0.15); transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
        
        <!-- Glowing Close Button -->
        <button type="button" style="position: absolute; top: 18px; right: 18px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #ffffff; font-size: 1rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 100; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.background='rgba(239, 68, 68, 0.8)'; this.style.borderColor='#ef4444'; this.style.transform='rotate(90deg)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.transform='rotate(0deg)';" onclick="closeWatchVideoModal()">✕</button>
        
        <!-- Premium Pulsing Live Dot Header -->
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
            <span style="display: inline-block; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; box-shadow: 0 0 12px #ef4444; animation: blink 1.2s infinite alternate;"></span>
            <h4 id="watchVideoTitle" style="color: #ffffff; font-size: 1.05rem; font-weight: 800; margin: 0; padding-right: 32px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: var(--font-heading); text-shadow: 0 0 8px rgba(255,255,255,0.3);">🎥 Xem Video Review</h4>
        </div>
        
        <!-- Local HTML5 Video Player Container -->
        <div id="watchLocalContainer" style="display: none; width: 100%; height: 520px; background-color: #000000; border-radius: 20px; overflow: hidden; border: 1.5px solid rgba(255,255,255,0.12); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <video id="watchVideoPlayer" controls style="width: 100%; height: 100%; object-fit: cover;"></video>
        </div>

        <!-- YouTube Shorts Iframe Container -->
        <div id="watchYoutubeContainer" style="display: none; width: 100%; height: 520px; background-color: #000000; border-radius: 20px; overflow: hidden; border: 1.5px solid rgba(255,255,255,0.12); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <iframe id="watchYoutubePlayer" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%; height: 100%;"></iframe>
        </div>

        <!-- TikTok Embed Container -->
        <div id="watchTiktokContainer" style="display: none; width: 100%; height: 520px; background-color: #0c081c; border-radius: 20px; overflow: hidden; align-items: center; justify-content: center; padding: 24px; border: 1.5px solid rgba(255,255,255,0.12); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <div style="text-align: center; color: rgba(255,255,255,0.9); padding: 10px;">
                <div style="font-size: 3rem; margin-bottom: 16px; filter: drop-shadow(0 0 10px rgba(255,255,255,0.3));">📱</div>
                <h5 style="font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 8px; font-family: var(--font-heading);">Video Tiktok ngắn</h5>
                <p style="font-size: 0.8rem; color: rgba(255,255,255,0.6); line-height: 1.5; margin-bottom: 24px; padding: 0 10px;">Để có trải nghiệm mượt mà, tốc độ cao và đầy đủ tính năng tương tác của TikTok, hãy mở xem trực tiếp trên nền tảng nguồn!</p>
                <a id="watchTiktokLink" href="" target="_blank" class="btn-admin" style="background: var(--primary-grad); border: none; padding: 12px 28px; font-size: 0.85rem; font-weight: 700; border-radius: 12px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; color: #fff; box-shadow: 0 4px 15px rgba(240, 78, 35, 0.4); transition: all 0.25s;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(240, 78, 35, 0.6)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(240, 78, 35, 0.4)';">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    Mở Trên TikTok
                </a>
            </div>
        </div>
        
        <!-- Premium Meta Info Pill under player -->
        <div style="margin-top: 15px; display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: rgba(255,255,255,0.5); background: rgba(0,0,0,0.2); padding: 8px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
            <span>🌐 Nguồn phát: Tự động nhận diện</span>
            <span style="color: #10b981; font-weight: 700; display: flex; align-items: center; gap: 4px;">
                <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></span>
                Sẵn sàng phát
            </span>
        </div>
    </div>
</div>

<!-- ==========================================================================
     MODAL SỬA HOẠT ĐỘNG VĂN HÓA / TRẢI NGHIỆM
     ========================================================================== -->
<div id="editCulturalActivityModal" class="admin-reels-overlay" style="display: none;">
    <div class="admin-card" style="width: 100%; max-width: 480px; padding: 24px; position: relative; border-radius: 16px; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <button type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--admin-text-muted); font-size: 1.25rem; cursor: pointer; z-index: 10;" onclick="closeEditCulturalActivityModal()">✕</button>
        
        <h3 class="admin-card-title" style="margin-bottom: 18px; font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <span>✏️</span> Cập Nhật Hoạt Động Văn Hóa
        </h3>
        
        <form id="editCulturalActivityForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="admin-form-group">
                <label class="admin-form-label">Tên hoạt động <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" id="editActivityNameInput" name="activity_name" required placeholder="Ví dụ: Bắn nỏ liên châu..." class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Phân loại hoạt động <span style="color: var(--admin-danger);">*</span></label>
                <select id="editActivityTypeInput" name="activity_type" class="admin-form-input" required>
                    <option value="experience">Hoạt động trải nghiệm</option>
                    <option value="ticket">Vé tham quan</option>
                    <option value="service">Dịch vụ di tích</option>
                    <option value="other">Khác</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Giá tiền (VNĐ)</label>
                <input type="number" id="editActivityPriceInput" name="activity_price" placeholder="Để trống nếu không có giá cố định" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Đơn vị tính <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" id="editActivityUnitInput" name="activity_unit" required placeholder="Ví dụ: đoàn (10 người), vé, lượt..." class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Lưu ý / Ưu đãi giảm giá</label>
                <input type="text" id="editActivityDiscountNoteInput" name="activity_discount_note" placeholder="Ví dụ: Giảm 50% cho học sinh..." class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Mô tả chi tiết</label>
                <textarea id="editActivityDescInput" name="activity_description" rows="3" class="admin-form-input" placeholder="Mô tả nội dung, quy trình hoạt động..."></textarea>
            </div>

            <div class="admin-form-group" style="margin-bottom: 12px;">
                <label class="admin-form-label">Chọn File Ảnh mới</label>
                <input type="file" name="activity_image" accept="image/*" class="admin-form-input" style="padding: 6px 12px;">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Hoặc dán URL ảnh mới</label>
                <input type="url" id="editActivityImageUrlInput" name="activity_image_url" placeholder="https://example.com/image.jpg" class="admin-form-input">
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 15px;">
                <button type="button" class="btn-admin btn-admin-secondary" style="padding: 10px 20px; font-size: 0.82rem; border-radius: 8px;" onclick="closeEditCulturalActivityModal()">Hủy bỏ</button>
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 24px; font-size: 0.82rem; border-radius: 8px;">💾 Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

@endsection

@php
    $mappedInvoices = [];
    if ($eatery && $eatery->purchaseInvoices) {
        foreach ($eatery->purchaseInvoices as $invoice) {
            $mappedInvoices[] = [
                'supplier_name' => $invoice->supplier_name,
                'items_summary' => $invoice->items_summary,
                'invoice_date' => \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y'),
                'image_path' => $invoice->image_path
            ];
        }
    }
@endphp

@section('scripts')
<script>
    // Danh sách hóa đơn mua hàng hằng ngày từ database
    const purchaseInvoices = @json($mappedInvoices);

    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.querySelector('select[name="category_id"]');
        const priceRangeGroup = document.getElementById('priceRangeGroup');
        const priceRangeInput = document.getElementById('priceRangeInput');

        function togglePriceRange() {
            if (!categorySelect || !priceRangeGroup) return;
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            if (selectedOption) {
                const slug = selectedOption.getAttribute('data-slug');
                const hiddenSlugs = ['smart-education-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub'];
                if (hiddenSlugs.includes(slug)) {
                    priceRangeGroup.style.display = 'none';
                    if (priceRangeInput) {
                        priceRangeInput.value = '';
                    }
                } else {
                    priceRangeGroup.style.display = 'block';
                }
            }
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', togglePriceRange);
            togglePriceRange();
        }

        const communeSearch = document.getElementById('communeSearch');
        const communeDropdown = document.getElementById('communeDropdown');
        const communeIdHidden = document.getElementById('communeIdHidden');
        const communeClearBtn = document.getElementById('communeClearBtn');
        const dropdownItems = document.querySelectorAll('.dropdown-item-com');

        if (!communeSearch) return;

        // Toggle clear button on load
        if (communeSearch.value.trim() !== '') {
            communeClearBtn.style.display = 'block';
        }

        // Helper function to remove Vietnamese accents/diacritics
        function removeAccents(str) {
            return str
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'D')
                .toLowerCase();
        }

        // Show dropdown on focus
        communeSearch.addEventListener('focus', function() {
            communeDropdown.style.display = 'block';
        });

        // Filter items on input
        communeSearch.addEventListener('input', function() {
            const query = removeAccents(this.value.trim());
            communeDropdown.style.display = 'block';
            
            if (query !== '') {
                communeClearBtn.style.display = 'block';
            } else {
                communeClearBtn.style.display = 'none';
                communeIdHidden.value = '';
            }

            let hasMatches = false;
            dropdownItems.forEach(item => {
                const name = removeAccents(item.getAttribute('data-name'));
                if (name.includes(query)) {
                    item.style.display = 'block';
                    hasMatches = true;
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Handle item selection
        dropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                communeSearch.value = name;
                communeIdHidden.value = id;
                communeClearBtn.style.display = 'block';
                communeDropdown.style.display = 'none';
                
                // Clear validation style if selected
                communeSearch.style.borderColor = '';
            });
        });

        // Handle clear button click
        communeClearBtn.addEventListener('click', function() {
            communeSearch.value = '';
            communeIdHidden.value = '';
            communeClearBtn.style.display = 'none';
            dropdownItems.forEach(item => item.style.display = 'block');
            communeSearch.focus();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-select-wrapper')) {
                communeDropdown.style.display = 'none';
            }
        });

        // Validate on form submission
        const form = communeSearch.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!communeIdHidden.value) {
                    e.preventDefault();
                    communeSearch.style.borderColor = 'var(--admin-danger)';
                    communeSearch.focus();
                    alert('Vui lòng nhập và chọn một Thôn / Tổ dân phố từ danh sách gợi ý!');
                }
            });
        }
    });

    // Kiểm tra xem là Sửa (Edit) hay Thêm mới
    const hasEatery = {{ $eatery ? 'true' : 'false' }};
    const initLat = {{ $eatery ? number_format($eatery->latitude, 6, '.', '') : 21.1182 }};
    const initLng = {{ $eatery ? number_format($eatery->longitude, 6, '.', '') : 105.8394 }};
    let pickerMap;
    let marker;
    let isMapLocked = hasEatery;

    window.toggleReplyForm = function(reviewId) {
        const formDiv = document.getElementById('reply-form-' + reviewId);
        if (formDiv) {
            if (formDiv.style.display === 'none') {
                formDiv.style.display = 'block';
            } else {
                formDiv.style.display = 'none';
            }
        }
    };

    window.toggleMapLock = function() {
        const btn = document.getElementById('btnMapLockToggle');
        if (!btn) return;
        
        isMapLocked = !isMapLocked;
        
        if (isMapLocked) {
            btn.innerHTML = '🔒 Đã khóa click (Bấm để sửa vị trí)';
            btn.style.backgroundColor = 'var(--admin-danger-light)';
            btn.style.color = 'var(--admin-danger)';
            btn.style.borderColor = 'rgba(239, 68, 68, 0.15)';
        } else {
            btn.innerHTML = '🔓 Đang mở click (Bấm bản đồ để chỉnh)';
            btn.style.backgroundColor = 'var(--admin-success-light)';
            btn.style.color = 'var(--admin-success)';
            btn.style.borderColor = 'rgba(16, 185, 129, 0.15)';
        }
    };

    // 1. Chuyển đổi Tab làm việc chính
    window.switchSubTab = function(event, tabId) {
        // Store in localStorage to persist active tab across page reloads
        localStorage.setItem('activeAdminTab', tabId);

        // Toggle tab button active classes
        document.querySelectorAll('.admin-sub-tab-btn').forEach(btn => btn.classList.remove('active'));
        if (event && event.currentTarget && event.currentTarget.classList.contains('admin-sub-tab-btn')) {
            event.currentTarget.classList.add('active');
        } else {
            const btn = Array.from(document.querySelectorAll('.admin-sub-tab-btn')).find(b => b.getAttribute('onclick') && b.getAttribute('onclick').includes(tabId));
            if (btn) btn.classList.add('active');
        }
        
        // Toggle tab content visibility
        document.querySelectorAll('.admin-tab-section').forEach(section => section.style.display = 'none');
        const activeSection = document.getElementById(tabId);
        if (activeSection) {
            activeSection.style.display = 'block';
            
            // Critical Leaflet refresh:
            if (tabId === 'tab-info' && pickerMap) {
                setTimeout(() => {
                    pickerMap.invalidateSize();
                }, 100);
            }
        }
    };

    // 2. Chuyển đổi chế độ đăng video (Nhúng link vs Tải file)
    window.toggleUploadMode = function(mode) {
        const embedBtn = document.getElementById('uploadTabBtn-embed');
        const fileBtn = document.getElementById('uploadTabBtn-file');
        const embedContainer = document.getElementById('uploadContainer-embed');
        const fileContainer = document.getElementById('uploadContainer-file');
        
        const urlInput = document.getElementById('videoUrlInput');
        const fileInput = document.getElementById('videoFileInput');

        if (mode === 'embed') {
            embedBtn.classList.remove('btn-admin-secondary');
            embedBtn.classList.add('btn-admin-primary');
            embedBtn.style.backgroundColor = '';
            embedBtn.style.borderColor = '';
            
            fileBtn.classList.remove('btn-admin-primary');
            fileBtn.classList.add('btn-admin-secondary');
            fileBtn.style.backgroundColor = 'transparent';
            fileBtn.style.borderColor = 'transparent';
            
            embedContainer.style.display = 'block';
            fileContainer.style.display = 'none';
            
            urlInput.setAttribute('required', 'required');
            fileInput.removeAttribute('required');
        } else {
            fileBtn.classList.remove('btn-admin-secondary');
            fileBtn.classList.add('btn-admin-primary');
            fileBtn.style.backgroundColor = '';
            fileBtn.style.borderColor = '';
            
            embedBtn.classList.remove('btn-admin-primary');
            embedBtn.classList.add('btn-admin-secondary');
            embedBtn.style.backgroundColor = 'transparent';
            embedBtn.style.borderColor = 'transparent';
            
            fileContainer.style.display = 'block';
            embedContainer.style.display = 'none';
            
            fileInput.setAttribute('required', 'required');
            urlInput.removeAttribute('required');
        }
    };

    // 3. Edit Video Modal Logic
    window.openEditVideoModal = function(id, title, eateryId, videoUrl, videoType) {
        const form = document.getElementById('editVideoForm');
        form.setAttribute('action', '/admin/videos/' + id);
        
        document.getElementById('editVideoTitle').value = title;
        
        if (videoType === 'local') {
            toggleEditUploadMode('file');
            document.getElementById('editVideoUrlInput').value = '';
        } else {
            toggleEditUploadMode('embed');
            document.getElementById('editVideoUrlInput').value = videoUrl;
        }
        
        document.getElementById('editVideoModal').style.display = 'flex';
    };

    window.closeEditVideoModal = function() {
        document.getElementById('editVideoModal').style.display = 'none';
    };

    // 3.5. Xem trực tiếp Video Review Modal Logic
    window.openWatchVideoModal = function(title, url, type) {
        document.getElementById('watchVideoTitle').innerText = title;
        
        // Hide all containers by default
        document.getElementById('watchLocalContainer').style.display = 'none';
        document.getElementById('watchYoutubeContainer').style.display = 'none';
        document.getElementById('watchTiktokContainer').style.display = 'none';
        
        // Reset player sources
        document.getElementById('watchVideoPlayer').pause();
        document.getElementById('watchVideoPlayer').src = '';
        document.getElementById('watchYoutubePlayer').src = '';
        
        if (type === 'youtube_shorts') {
            const ytMatch = url.match(/(?:shorts\/|watch\?v=)([a-zA-Z0-9_-]+)/);
            if (ytMatch) {
                document.getElementById('watchYoutubeContainer').style.display = 'block';
                document.getElementById('watchYoutubePlayer').src = 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1';
            } else {
                document.getElementById('watchLocalContainer').style.display = 'block';
                document.getElementById('watchVideoPlayer').src = url;
            }
        } else if (type === 'tiktok') {
            const ttMatch = url.match(/video\/(\d+)/);
            if (ttMatch) {
                document.getElementById('watchYoutubeContainer').style.display = 'block';
                document.getElementById('watchYoutubePlayer').src = 'https://www.tiktok.com/embed/v2/' + ttMatch[1];
            } else {
                document.getElementById('watchTiktokContainer').style.display = 'flex';
                document.getElementById('watchTiktokLink').href = url;
            }
        } else {
            // Local file or standard direct mp4 url
            document.getElementById('watchLocalContainer').style.display = 'block';
            document.getElementById('watchVideoPlayer').src = url;
            document.getElementById('watchVideoPlayer').load();
            document.getElementById('watchVideoPlayer').play().catch(e => console.log('Autoplay blocked'));
        }
        
        document.getElementById('watchVideoModal').style.display = 'flex';
    };

    window.closeWatchVideoModal = function() {
        document.getElementById('watchVideoPlayer').pause();
        document.getElementById('watchVideoPlayer').src = '';
        document.getElementById('watchYoutubePlayer').src = '';
        document.getElementById('watchVideoModal').style.display = 'none';
    };

    // 4. Xem chi tiết Món ăn Modal Logic
    window.openViewDishModal = function(name, price, description, imagePath, isSignature) {
        document.getElementById('viewDishName').innerText = name;
        document.getElementById('viewDishPrice').innerText = price;
        document.getElementById('viewDishDesc').innerText = description || "Không có mô tả chi tiết.";
        document.getElementById('viewDishImg').src = imagePath;
        
        document.getElementById('viewDishBadge').style.display = parseInt(isSignature) === 1 ? 'inline-flex' : 'none';
        document.getElementById('viewDishModal').style.display = 'flex';
    };

    window.closeViewDishModal = function() {
        document.getElementById('viewDishModal').style.display = 'none';
    };

    // 4.5. Xem chi tiết Hợp đồng Modal Logic
    window.openViewContractModal = function(supplier, items, signed, expired, img) {
        document.getElementById('viewContractSupplier').innerText = supplier;
        document.getElementById('viewContractItems').innerText = items;
        document.getElementById('viewContractSigned').innerText = signed;
        document.getElementById('viewContractExpired').innerText = expired;
        document.getElementById('viewContractImg').src = img;
        document.getElementById('viewContractModal').style.display = 'flex';
    };

    window.closeViewContractModal = function() {
        document.getElementById('viewContractModal').style.display = 'none';
    };

    // 4.6. Xem chi tiết Hóa đơn Modal Logic
    window.openViewInvoiceModal = function(supplier, summary, date, img) {
        document.getElementById('viewInvoiceSupplier').innerText = supplier;
        document.getElementById('viewInvoiceSummary').innerText = summary;
        document.getElementById('viewInvoiceDate').innerText = date;
        document.getElementById('viewInvoiceImg').src = img;
        document.getElementById('viewInvoiceModal').style.display = 'flex';
    };

    window.closeViewInvoiceModal = function() {
        document.getElementById('viewInvoiceModal').style.display = 'none';
    };

    // 4.7. Xem chi tiết Chứng nhận VSATTP Modal Logic
    window.openViewCertModal = function(number, issuer, issued, expired, img) {
        document.getElementById('viewCertNumber').innerText = "Số hiệu: " + number;
        document.getElementById('viewCertIssuedBy').innerText = issuer;
        document.getElementById('viewCertIssuedAt').innerText = issued;
        document.getElementById('viewCertExpiredAt').innerText = expired;
        document.getElementById('viewCertImg').src = img || "https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=800&q=80";
        document.getElementById('viewCertModal').style.display = 'flex';
    };

    window.closeViewCertModal = function() {
        document.getElementById('viewCertModal').style.display = 'none';
    };

    // 4.8. Xem chi tiết Nhật ký kiểm tra VSATTP Modal Logic
    window.openViewLogModal = function(date, origin, storage, checker) {
        document.getElementById('viewLogDate').innerText = "Ngày kiểm tra: " + date;
        document.getElementById('viewLogOrigin').innerText = origin;
        document.getElementById('viewLogStorage').innerText = storage;
        document.getElementById('viewLogChecker').innerText = checker;

        // Tìm hóa đơn khớp với ngày kiểm tra
        const listDiv = document.getElementById('viewLogInvoicesList');
        const sectionDiv = document.getElementById('viewLogInvoicesSection');
        if (listDiv && sectionDiv) {
            listDiv.innerHTML = '';
            // purchaseInvoices là mảng đã được khai báo ở đầu script, ngày định dạng d/m/Y
            const matching = purchaseInvoices.filter(inv => inv.invoice_date === date);
            if (matching.length > 0) {
                matching.forEach(inv => {
                    const item = document.createElement('div');
                    item.style.position = 'relative';
                    item.style.borderRadius = '10px';
                    item.style.overflow = 'hidden';
                    item.style.border = '1px solid #e2e8f0';
                    item.style.background = '#f8fafc';
                    item.style.aspectRatio = '3/4';
                    item.style.cursor = 'pointer';
                    item.title = `Đối tác: ${inv.supplier_name}\nSản phẩm: ${inv.items_summary}`;
                    item.onclick = function() {
                        window.open(inv.image_path, '_blank');
                    };

                    item.innerHTML = `<img src="${inv.image_path}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?auto=format&fit=crop&w=300&q=80'">`;
                    listDiv.appendChild(item);
                });
                sectionDiv.style.display = 'block';
            } else {
                sectionDiv.style.display = 'none';
            }
        }

        document.getElementById('viewLogModal').style.display = 'flex';
    };

    window.closeViewLogModal = function() {
        document.getElementById('viewLogModal').style.display = 'none';
    };

    // 5. Sửa Món ăn Modal Logic
    window.openEditDishModal = function(btn) {
        const dish = JSON.parse(btn.getAttribute('data-dish'));
        const form = document.getElementById('editDishForm');
        form.setAttribute('action', '/admin/dishes/' + dish.id);
        
        document.getElementById('editDishNameInput').value = dish.name || '';
        document.getElementById('editDishPriceInput').value = dish.price ? parseInt(dish.price) : '';
        document.getElementById('editDishDescInput').value = dish.description !== 'null' && dish.description !== null ? dish.description : '';
        document.getElementById('editDishImageUrlInput').value = dish.image_path && dish.image_path.startsWith('http') ? dish.image_path : '';
        
        const signatureCheckbox = document.getElementById('editDishSignatureInput');
        if (parseInt(dish.is_signature) === 1) {
            signatureCheckbox.checked = true;
        } else {
            signatureCheckbox.checked = false;
        }
        
        document.getElementById('editDishModal').style.display = 'flex';
    };

    window.closeEditDishModal = function() {
        document.getElementById('editDishModal').style.display = 'none';
    };

    window.openAddOcopProductModal = function() {
        document.getElementById('addOcopProductModal').style.display = 'flex';
    };

    window.closeAddOcopProductModal = function() {
        document.getElementById('addOcopProductModal').style.display = 'none';
    };

    // Sửa Sản phẩm OCOP Modal Logic
    window.openEditOcopProductModal = function(btn) {
        const product = JSON.parse(btn.getAttribute('data-product'));
        const form = document.getElementById('editOcopProductForm');
        form.setAttribute('action', '/admin/ocop-products/' + product.id);
        
        document.getElementById('editOcopProductNameInput').value = product.name || '';
        document.getElementById('editOcopProductPriceInput').value = product.price ? parseInt(product.price) : '';
        document.getElementById('editOcopProductDescInput').value = product.description !== 'null' && product.description !== null ? product.description : '';
        document.getElementById('editOcopProductImageUrlInput').value = product.image_path && product.image_path.startsWith('http') ? product.image_path : '';
        document.getElementById('editOcopProductStarInput').value = product.star_rating || '';
        
        // Populate heritage fields:
        document.getElementById('editOcopProductHeritageYearInput').value = product.heritage_year || '';
        document.getElementById('editOcopProductStoryInput').value = product.story || '';
        document.getElementById('editOcopProductArtisansInput').value = product.artisans || '';
        document.getElementById('editOcopProductFunFactInput').value = product.fun_fact || '';
        document.getElementById('editOcopProductAudioNarrativeInput').value = product.audio_narrative || '';
        
        // Ingredients:
        let ingredientsRaw = '';
        if (Array.isArray(product.ingredients)) {
            ingredientsRaw = product.ingredients.join('\n');
        } else if (typeof product.ingredients === 'string') {
            try {
                const parsed = JSON.parse(product.ingredients);
                if (Array.isArray(parsed)) {
                    ingredientsRaw = parsed.join('\n');
                }
            } catch(e) {
                ingredientsRaw = product.ingredients;
            }
        }
        document.getElementById('editOcopProductIngredientsInput').value = ingredientsRaw;

        // Timeline:
        let timelineRaw = '';
        if (Array.isArray(product.timeline)) {
            timelineRaw = product.timeline.map(t => `${t.year || ''} | ${t.event || ''}`).join('\n');
        } else if (typeof product.timeline === 'string') {
            try {
                const parsed = JSON.parse(product.timeline);
                if (Array.isArray(parsed)) {
                    timelineRaw = parsed.map(t => `${t.year || ''} | ${t.event || ''}`).join('\n');
                }
            } catch(e) {
                timelineRaw = product.timeline;
            }
        }
        document.getElementById('editOcopProductTimelineInput').value = timelineRaw;
        
        // Auto-expand heritage fields if any field contains data
        const fields = document.getElementById('ocopHeritageEditFields');
        const icon = document.getElementById('ocopHeritageEditIcon');
        const hasData = product.heritage_year || product.story || product.artisans || product.fun_fact || product.audio_narrative || ingredientsRaw || timelineRaw;
        if (hasData) {
            fields.style.display = 'flex';
            icon.innerText = '▲';
            icon.style.transform = 'rotate(180deg)';
        } else {
            fields.style.display = 'none';
            icon.innerText = '▼';
            icon.style.transform = 'none';
        }

        document.getElementById('editOcopProductModal').style.display = 'flex';
    };

    window.closeEditOcopProductModal = function() {
        document.getElementById('editOcopProductModal').style.display = 'none';
    };

    window.toggleOcopHeritageAddFields = function() {
        const fields = document.getElementById('ocopHeritageAddFields');
        const icon = document.getElementById('ocopHeritageAddIcon');
        if (fields.style.display === 'none' || fields.style.display === '') {
            fields.style.display = 'flex';
            icon.innerText = '▲';
            icon.style.transform = 'rotate(180deg)';
        } else {
            fields.style.display = 'none';
            icon.innerText = '▼';
            icon.style.transform = 'none';
        }
    };

    window.toggleOcopHeritageEditFields = function() {
        const fields = document.getElementById('ocopHeritageEditFields');
        const icon = document.getElementById('ocopHeritageEditIcon');
        if (fields.style.display === 'none' || fields.style.display === '') {
            fields.style.display = 'flex';
            icon.innerText = '▲';
            icon.style.transform = 'rotate(180deg)';
        } else {
            fields.style.display = 'none';
            icon.innerText = '▼';
            icon.style.transform = 'none';
        }
    };

    // Sửa Phòng nghỉ Modal Logic
    window.openEditRoomModal = function(btn) {
        const room = JSON.parse(btn.getAttribute('data-room'));
        const form = document.getElementById('editRoomForm');
        form.setAttribute('action', '/admin/rooms/' + room.id);
        
        document.getElementById('editRoomNameInput').value = room.name || '';
        document.getElementById('editRoomPriceInput').value = room.price ? parseInt(room.price) : '';
        document.getElementById('editRoomDescInput').value = room.description !== 'null' && room.description !== null ? room.description : '';
        document.getElementById('editRoomImageUrlInput').value = room.image_path && room.image_path.startsWith('http') ? room.image_path : '';
        document.getElementById('editRoomBedInput').value = room.bed_type !== 'null' && room.bed_type !== null ? room.bed_type : '';
        document.getElementById('editRoomCapacityInput').value = room.capacity || '';
        
        document.getElementById('editRoomModal').style.display = 'flex';
    };

    window.closeEditRoomModal = function() {
        document.getElementById('editRoomModal').style.display = 'none';
    };

    // Sửa Dịch vụ Wellness Modal Logic
    window.openEditWellnessServiceModal = function(btn) {
        const service = JSON.parse(btn.getAttribute('data-service'));
        const form = document.getElementById('editWellnessServiceForm');
        form.setAttribute('action', '/admin/wellness-services/' + service.id);
        
        document.getElementById('editWellnessServiceNameInput').value = service.name || '';
        document.getElementById('editWellnessServicePriceInput').value = service.price ? parseInt(service.price) : '';
        document.getElementById('editWellnessServiceDescInput').value = service.description !== 'null' && service.description !== null ? service.description : '';
        document.getElementById('editWellnessServiceImageUrlInput').value = service.image_path && service.image_path.startsWith('http') ? service.image_path : '';
        document.getElementById('editWellnessServiceDurationInput').value = service.duration !== 'null' && service.duration !== null ? service.duration : '';
        
        document.getElementById('editWellnessServiceModal').style.display = 'flex';
    };

    window.closeEditWellnessServiceModal = function() {
        document.getElementById('editWellnessServiceModal').style.display = 'none';
    };

    // Sửa Chương trình Đào tạo Modal Logic
    window.openEditEducationProgramModal = function(btn) {
        const program = JSON.parse(btn.getAttribute('data-program'));
        const form = document.getElementById('editEducationProgramForm');
        form.setAttribute('action', '/admin/education-programs/' + program.id);
        
        document.getElementById('editEducationProgramNameInput').value = program.name || '';
        document.getElementById('editEducationProgramTuitionInput').value = program.tuition_fee !== 'null' && program.tuition_fee !== null ? program.tuition_fee : '';
        document.getElementById('editEducationProgramDescInput').value = program.description !== 'null' && program.description !== null ? program.description : '';
        document.getElementById('editEducationProgramImageUrlInput').value = program.image_path && program.image_path.startsWith('http') ? program.image_path : '';
        document.getElementById('editEducationProgramDurationInput').value = program.duration !== 'null' && program.duration !== null ? program.duration : '';
        
        document.getElementById('editEducationProgramModal').style.display = 'flex';
    };

    window.closeEditEducationProgramModal = function() {
        document.getElementById('editEducationProgramModal').style.display = 'none';
    };

    // Sửa Hoạt động văn hóa Modal Logic
    window.openEditCulturalActivityModal = function(btn) {
        const act = JSON.parse(btn.getAttribute('data-activity'));
        const form = document.getElementById('editCulturalActivityForm');
        form.setAttribute('action', '/admin/cultural-activities/' + act.id);
        
        document.getElementById('editActivityNameInput').value = act.name || '';
        document.getElementById('editActivityTypeInput').value = act.type || '';
        document.getElementById('editActivityPriceInput').value = act.price ? parseInt(act.price) : '';
        document.getElementById('editActivityUnitInput').value = act.unit || '';
        document.getElementById('editActivityDiscountNoteInput').value = (act.discount_note !== 'null' && act.discount_note !== null && act.discount_note !== 'NULL') ? act.discount_note : '';
        document.getElementById('editActivityDescInput').value = (act.description !== 'null' && act.description !== null && act.description !== 'NULL') ? act.description : '';
        document.getElementById('editActivityImageUrlInput').value = (act.image_path && act.image_path.startsWith('http')) ? act.image_path : '';
        
        document.getElementById('editCulturalActivityModal').style.display = 'flex';
    };

    window.closeEditCulturalActivityModal = function() {
        document.getElementById('editCulturalActivityModal').style.display = 'none';
    };

    window.toggleEditUploadMode = function(mode) {
        const embedBtn = document.getElementById('editTabBtn-embed');
        const fileBtn = document.getElementById('editTabBtn-file');
        const embedContainer = document.getElementById('editContainer-embed');
        const fileContainer = document.getElementById('editContainer-file');
        
        const urlInput = document.getElementById('editVideoUrlInput');
        const fileInput = document.getElementById('editVideoFileInput');

        if (mode === 'embed') {
            embedBtn.classList.remove('btn-admin-secondary');
            embedBtn.classList.add('btn-admin-accent');
            embedBtn.style.backgroundColor = '';
            embedBtn.style.borderColor = '';
            
            fileBtn.classList.remove('btn-admin-accent');
            fileBtn.classList.add('btn-admin-secondary');
            fileBtn.style.backgroundColor = 'transparent';
            fileBtn.style.borderColor = 'transparent';
            
            embedContainer.style.display = 'block';
            fileContainer.style.display = 'none';
            
            urlInput.setAttribute('required', 'required');
            fileInput.removeAttribute('required');
        } else {
            fileBtn.classList.remove('btn-admin-secondary');
            fileBtn.classList.add('btn-admin-accent');
            fileBtn.style.backgroundColor = '';
            fileBtn.style.borderColor = '';
            
            embedBtn.classList.remove('btn-admin-accent');
            embedBtn.classList.add('btn-admin-secondary');
            embedBtn.style.backgroundColor = 'transparent';
            embedBtn.style.borderColor = 'transparent';
            
            fileContainer.style.display = 'block';
            embedContainer.style.display = 'none';
            
            fileInput.removeAttribute('required');
            urlInput.removeAttribute('required');
        }
    };

    document.addEventListener("DOMContentLoaded", function() {
        // Restore active sub tab if stored in localStorage
        const storedTabId = localStorage.getItem('activeAdminTab');
        if (storedTabId) {
            const tabBtn = Array.from(document.querySelectorAll('.admin-sub-tab-btn')).find(btn => {
                const clickAttr = btn.getAttribute('onclick');
                return clickAttr && clickAttr.includes(storedTabId);
            });
            if (tabBtn) {
                window.switchSubTab({ currentTarget: tabBtn }, storedTabId);
            }
        }

        // Khởi tạo bản đồ chọn tọa độ
        pickerMap = L.map('pickerMap', {
            zoomControl: true
        }).setView([initLat, initLng], hasEatery ? 15 : 13);

        // Lớp OpenStreetMap light mode sạch sẽ, tối giản cực đẹp
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(pickerMap);

        // Biểu tượng Marker
        const customIcon = L.divIcon({
            html: `<div style="background-color: var(--admin-primary); width: 22px; height: 22px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">📍</div>`,
            className: 'custom-leaflet-marker',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });

        // Vẽ marker ban đầu nếu có dữ liệu
        if (hasEatery) {
            marker = L.marker([initLat, initLng], { icon: customIcon }).addTo(pickerMap);
        }

        // Sự kiện click bản đồ nhặt tọa độ điền tự động vào Form
        pickerMap.on('click', function(e) {
            if (isMapLocked) {
                alert("📍 Bản đồ định vị đang được KHÓA để tránh click nhầm vị trí.\n\nHãy nhấn nút '🔒 Đã khóa click' ở phía trên bản đồ để MỞ KHÓA nếu bạn muốn chỉnh sửa tọa độ của quán!");
                return;
            }

            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            
            // Cập nhật giá trị 2 ô input
            document.getElementById('latInput').value = lat;
            document.getElementById('lngInput').value = lng;
            
            // Di chuyển hoặc vẽ mới marker định vị
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng, { icon: customIcon }).addTo(pickerMap);
            }
            
            pickerMap.panTo(e.latlng);
        });

        // Hỗ trợ cập nhật marker khi nhập tọa độ thủ công bằng tay
        const latInput = document.getElementById('latInput');
        const lngInput = document.getElementById('lngInput');

        function updateMarkerFromInput() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                const newLatLng = new L.LatLng(lat, lng);
                
                if (marker) {
                    marker.setLatLng(newLatLng);
                } else {
                    marker = L.marker(newLatLng, { icon: customIcon }).addTo(pickerMap);
                }
                
                pickerMap.setView(newLatLng, 15);
            }
        }

        latInput.addEventListener('change', updateMarkerFromInput);
        lngInput.addEventListener('change', updateMarkerFromInput);

        // Xử lý trích xuất tọa độ từ Google Maps Link tự động
        const btnExtract = document.getElementById('btnExtractCoords');
        const gmapsInput = document.getElementById('gmapsUrlInput');
        const helperText = document.getElementById('gmapsHelperText');

        btnExtract.addEventListener('click', function() {
            const url = gmapsInput.value.trim();
            if (!url) {
                helperText.innerText = "❌ Vui lòng dán đường dẫn Google Maps trước!";
                helperText.style.color = "var(--admin-danger)";
                return;
            }

            helperText.innerText = "⏳ Đang giải mã đường dẫn và trích xuất tọa độ...";
            helperText.style.color = "var(--admin-primary)";
            btnExtract.disabled = true;

            // Gửi request lên backend endpoint để giải mã link rút gọn và trích xuất tọa độ
            fetch('/admin/parse-google-maps', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url: url })
            })
            .then(response => response.json())
            .then(data => {
                btnExtract.disabled = false;
                if (data.success) {
                    const lat = parseFloat(data.latitude).toFixed(6);
                    const lng = parseFloat(data.longitude).toFixed(6);

                    // Điền vào form
                    latInput.value = lat;
                    lngInput.value = lng;

                    // Di chuyển marker trên bản đồ
                    const newLatLng = new L.LatLng(lat, lng);
                    if (marker) {
                        marker.setLatLng(newLatLng);
                    } else {
                        marker = L.marker(newLatLng, { icon: customIcon }).addTo(pickerMap);
                    }
                    pickerMap.setView(newLatLng, 15);
                    helperText.innerText = "✅ Trích xuất tọa độ thành công!";
                    helperText.style.color = "var(--admin-success)";
                    
                    // Mở khóa map tự động khi trích xuất thành công để admin thấy marker di chuyển
                    isMapLocked = false;
                    const mapBtn = document.getElementById('btnMapLockToggle');
                    if (mapBtn) {
                        mapBtn.innerHTML = '🔓 Đang mở click (Bấm bản đồ để chỉnh)';
                        mapBtn.style.backgroundColor = 'var(--admin-success-light)';
                        mapBtn.style.color = 'var(--admin-success)';
                        mapBtn.style.borderColor = 'rgba(16, 185, 129, 0.15)';
                    }
                } else {
                    helperText.innerText = "❌ " + data.message;
                    helperText.style.color = "var(--admin-danger)";
                }
            })
            .catch(error => {
                btnExtract.disabled = false;
                console.error("Error:", error);
                helperText.innerText = "❌ Lỗi kết nối hệ thống. Vui lòng kiểm tra lại.";
                helperText.style.color = "var(--admin-danger)";
            });
        });

        // Tự động định dạng Mức giá tham khảo khi blur
        const priceRangeInput = document.querySelector('input[name="price_range"]');
        if (priceRangeInput) {
            priceRangeInput.addEventListener('blur', function() {
                let value = this.value.trim();
                if (!value) return;

                function formatNumber(numStr) {
                    let digits = numStr.replace(/\D/g, '');
                    if (!digits) return '';
                    return parseInt(digits, 10).toLocaleString('vi-VN') + 'đ';
                }

                let parts = [];
                if (value.includes('-')) {
                    parts = value.split('-');
                } else if (value.includes(' đến ')) {
                    parts = value.split(' đến ');
                } else if (value.includes(' to ')) {
                    parts = value.split(' to ');
                }

                if (parts.length === 2) { r(parts[0]);
                    let maxStr = formatNumber(parts[1]);
                    if (minStr && maxStr) {
                        this.value = minStr + ' - ' + maxStr;
                    }
                } else {
                    let clean = value.replace(/\D/g, '');
                    if (clean && /^\d+$/.test(clean)) {
                        this.value = formatNumber(clean);
                    }
                }
            });
        }
    });
</script>
@endsection
