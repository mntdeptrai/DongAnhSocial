@extends('layouts.health-station')

@section('title', 'Hồ sơ & Hotline Cấp cứu — ' . $eatery->name)
@section('header_title', 'Cập Nhật Hồ Sơ & Hotline Cấp Cứu Trạm Y Tế')

@section('content')
<style>
    .hs-form-card {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        padding: 32px;
        max-width: 900px;
    }

    .hs-form-group {
        margin-bottom: 24px;
    }

    .hs-form-label {
        display: block;
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .hs-form-label span {
        color: #ef4444;
    }

    .hs-form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        font-size: 0.92rem;
        font-family: inherit;
        color: #0f172a;
        transition: all 0.2s ease;
    }

    .hs-form-control:focus {
        outline: none;
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }

    .hs-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .btn-hs-submit {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: #ffffff;
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        transition: all 0.2s ease;
    }

    .btn-hs-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(13, 148, 136, 0.4);
    }

    .hs-img-preview {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        border-radius: 12px;
        margin-top: 10px;
        border: 1px solid #cbd5e1;
    }
</style>

<div class="hs-form-card">
    <form action="{{ route('health-station.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="hs-form-group">
            <label class="hs-form-label">Tên Cơ Sở Y Tế / Trạm Y Tế <span>*</span></label>
            <input type="text" name="name" class="hs-form-control" value="{{ old('name', $eatery->name) }}" required>
        </div>

        <div class="hs-form-row">
            <div class="hs-form-group">
                <label class="hs-form-label"><i class="fa-solid fa-phone-volume" style="color: #ef4444;"></i> Hotline Cấp Cứu & Trực Ban <span>*</span></label>
                <input type="text" name="phone" class="hs-form-control" value="{{ old('phone', $eatery->phone) }}" required placeholder="e.g. 0389 928 304">
            </div>

            <div class="hs-form-group">
                <label class="hs-form-label"><i class="fa-solid fa-clock" style="color: #0284c7;"></i> Lịch Khám & Khung Giờ Trực <span>*</span></label>
                <input type="text" name="opening_hours" class="hs-form-control" value="{{ old('opening_hours', $eatery->opening_hours) }}" required placeholder="e.g. Trực cấp cứu 24/7 | Khám BHYT: 07:30 - 17:00">
            </div>
        </div>

        <div class="hs-form-group">
            <label class="hs-form-label">Địa Chỉ Cơ Sở <span>*</span></label>
            <input type="text" name="address" class="hs-form-control" value="{{ old('address', $eatery->address) }}" required placeholder="Địa chỉ chi tiết (Thôn, Xã Đông Anh, Hà Nội)">
        </div>

        <!-- Google Maps Link & Map Pin Coordinates -->
        <div class="hs-form-group" style="background: #f0fdf4; border: 1px dashed #16a34a; padding: 20px; border-radius: 14px; margin-bottom: 24px;">
            <label class="hs-form-label" style="color: #15803d; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-map-location-dot" style="font-size: 1.1rem; color: #16a34a;"></i> 
                Đường Dẫn Google Maps & Tọa Độ Định Vị Bản Đồ Số
            </label>
            <div style="margin-bottom: 12px;">
                <input type="url" name="map_link" id="map_link_input" class="hs-form-control" value="{{ old('map_link', $storytelling['map_link'] ?? '') }}" placeholder="Dán liên kết Google Maps (e.g. https://maps.app.goo.gl/xxx hoặc https://www.google.com/maps/@21.1393,105.8534...)" oninput="autoDetectCoordsFromLink(this.value)">
                <div style="font-size: 0.78rem; color: #15803d; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                    <span>💡</span> Dán liên kết chia sẻ từ Google Maps hoặc tọa độ (e.g. 21.1393, 105.8534). Hệ thống sẽ tự động ghim vị trí chính xác lên Bản đồ số.
                </div>
            </div>
            
            <div id="coords_detected_badge" style="display: none; background: #dcfce7; color: #15803d; font-size: 0.82rem; font-weight: 700; padding: 6px 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #86efac;">
                ⚡ Đã tự động nhận diện Tọa độ từ Link Google Maps!
            </div>

            <div class="hs-form-row" style="margin-bottom: 0;">
                <div>
                    <label class="hs-form-label" style="font-size: 0.8rem; color: #334155;">📍 Vĩ Độ (Latitude)</label>
                    <input type="text" name="latitude" id="lat_input" class="hs-form-control" value="{{ old('latitude', $eatery->latitude) }}" placeholder="e.g. 21.139304">
                </div>
                <div>
                    <label class="hs-form-label" style="font-size: 0.8rem; color: #334155;">📍 Kinh Độ (Longitude)</label>
                    <input type="text" name="longitude" id="lng_input" class="hs-form-control" value="{{ old('longitude', $eatery->longitude) }}" placeholder="e.g. 105.853405">
                </div>
            </div>
        </div>

        <div class="hs-form-group">
            <label class="hs-form-label">Thông Tin BHYT & Chi Phí Khám</label>
            <input type="text" name="price_range" class="hs-form-control" value="{{ old('price_range', $eatery->price_range) }}" placeholder="e.g. Khám BHYT / Miễn phí tiêm chủng mở rộng">
        </div>

        <div class="hs-form-row">
            <div class="hs-form-group">
                <label class="hs-form-label">Tổng Diện Tích Trạm (m²)</label>
                <input type="text" name="area" class="hs-form-control" value="{{ old('area', $storytelling['area'] ?? '3.674 m²') }}">
            </div>
            <div class="hs-form-group">
                <label class="hs-form-label">Quyết Định Thành Lập / Năm Thành Lập</label>
                <input type="text" name="heritage_year" class="hs-form-control" value="{{ old('heritage_year', $storytelling['heritage_year'] ?? 'Quyết định 01/QĐ-UBND (01/7/2025)') }}">
            </div>
        </div>

        <div class="hs-form-group">
            <label class="hs-form-label">Giới Thiệu Tổng Quan & Nhiệm Vụ Y Tế Cơ Sở</label>
            <textarea name="description" rows="5" class="hs-form-control" placeholder="Mô tả chức năng nhiệm vụ khám chữa bệnh BHYT, tiêm chủng, giám sát dịch bệnh...">{{ old('description', $eatery->description) }}</textarea>
        </div>

        <div class="hs-form-group">
            <label class="hs-form-label">Ảnh Bìa Đại Diện Chính Cơ Sở Y Tế</label>
            <input type="file" name="image" class="hs-form-control" accept="image/*">
            <div style="margin-top: 8px;">
                <input type="url" name="image_url" class="hs-form-control" value="{{ old('image_url', $eatery->image_path) }}" placeholder="Hoặc dán đường dẫn ảnh direct URL...">
            </div>
            @if($eatery->image_path)
                <img src="{{ $eatery->image_path }}" class="hs-img-preview" alt="{{ $eatery->name }}">
            @endif
        </div>

        <!-- Thư viện Ảnh & Upload Nhiều Ảnh -->
        <div class="hs-form-group" style="background: #f8fafc; border: 1px dashed #0d9488; padding: 24px; border-radius: 16px; margin-top: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f766e; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-images"></i> Quản Lý Thư Viện Ảnh Cơ Sở Y Tế (Nhiều Ảnh)
                    </h3>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 4px 0 0 0;">
                        Tải lên cùng lúc nhiều ảnh cơ sở vật chất, phòng khám, đội ngũ y bác sĩ hoặc dán đường dẫn ảnh direct URL. Xem trước hình ảnh trực tiếp bên dưới.
                    </p>
                </div>
                <span id="photo_count_badge" style="background: #ccfbf1; color: #0f766e; font-weight: 700; font-size: 0.82rem; padding: 4px 12px; border-radius: 20px; border: 1px solid #99f6e4;">
                    Đã có {{ count($photos ?? []) }} ảnh
                </span>
            </div>

            <!-- Upload Zone & Controls -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <!-- Box 1: Chọn nhiều file từ máy -->
                <div style="border: 2px dashed #cbd5e1; background: #ffffff; border-radius: 12px; padding: 18px; text-align: center; cursor: pointer; transition: all 0.2s ease;" onclick="document.getElementById('multi_images_input').click()" ondragover="event.preventDefault(); this.style.borderColor='#0d9488'; this.style.background='#f0fdf4';" ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';" ondrop="handleDrop(event)">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.2rem; color: #0d9488; margin-bottom: 8px;"></i>
                    <div style="font-weight: 700; font-size: 0.9rem; color: #1e293b;">Click hoặc Kéo thả nhiều ảnh vào đây</div>
                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">Giữ Ctrl/Shift để chọn nhiều file ảnh JPG, PNG, WEBP</div>
                    <input type="file" id="multi_images_input" name="images[]" multiple accept="image/*" style="display: none;" onchange="handleFileSelect(event)">
                </div>

                <!-- Box 2: Thêm đường dẫn URL -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: center;">
                    <label class="hs-form-label" style="margin-bottom: 6px;"><i class="fa-solid fa-link" style="color: #0284c7;"></i> Thêm Đường Dẫn URL Ảnh Trực Tiếp</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="url" id="custom_url_input" class="hs-form-control" placeholder="https://example.com/anh-tram-y-te.jpg" style="padding: 10px 14px; font-size: 0.85rem;">
                        <button type="button" onclick="addCustomUrl()" style="background: #0284c7; color: white; border: none; padding: 0 16px; border-radius: 8px; font-weight: 700; font-size: 0.85rem; white-space: nowrap; cursor: pointer;">
                            + Thêm URL
                        </button>
                    </div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px;">URL ảnh direct link hiển thị công khai trên trang chi tiết</div>
                </div>
            </div>

            <!-- Preview Container -->
            <div id="image_preview_grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; margin-top: 16px;">
                <!-- Existing photos from DB -->
                @if(isset($photos) && count($photos) > 0)
                    @foreach($photos as $index => $p)
                        <div class="preview-card existing-photo-card" id="photo_card_db_{{ $p->id }}" style="position: relative; border-radius: 12px; overflow: hidden; border: 2px solid {{ $p->image_path === $eatery->image_path ? '#0d9488' : '#e2e8f0' }}; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <img src="{{ $p->image_path }}" style="width: 100%; height: 120px; object-fit: cover; display: block;">
                            <div style="padding: 8px; display: flex; align-items: center; justify-content: space-between; background: #fafafa; border-top: 1px solid #f1f5f9;">
                                <span style="font-size: 0.72rem; font-weight: 700; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $p->image_path === $eatery->image_path ? '⭐ Ảnh chính' : 'Ảnh #' . ($index + 1) }}
                                </span>
                                <button type="button" onclick="removeExistingPhoto({{ $p->id }})" style="background: #fee2e2; color: #ef4444; border: none; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: 800; font-size: 0.8rem;" title="Xóa ảnh này khỏi thư viện">
                                    ✕
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Container ẩn chứa các ID ảnh bị xóa và các URL ảnh mới -->
            <div id="hidden_inputs_container"></div>
        </div>

        <div style="margin-top: 32px;">
            <button type="submit" class="btn-hs-submit">
                <i class="fa-solid fa-floppy-disk"></i> Lưu Cập Nhật Hồ Sơ & Thư Viện Ảnh
            </button>
        </div>
    </form>
</div>

<script>
    let dt = new DataTransfer();
    let customUrls = [];
    let deletedPhotoIds = [];

    function handleFileSelect(event) {
        const files = event.target.files;
        for (let i = 0; i < files.length; i++) {
            dt.items.add(files[i]);
        }
        document.getElementById('multi_images_input').files = dt.files;
        renderNewFilePreviews();
    }

    function handleDrop(event) {
        event.preventDefault();
        const files = event.dataTransfer.files;
        for (let i = 0; i < files.length; i++) {
            if (files[i].type.startsWith('image/')) {
                dt.items.add(files[i]);
            }
        }
        document.getElementById('multi_images_input').files = dt.files;
        renderNewFilePreviews();
    }

    function removeLocalFile(index) {
        const newDt = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) {
            if (i !== index) {
                newDt.items.add(dt.files[i]);
            }
        }
        dt = newDt;
        document.getElementById('multi_images_input').files = dt.files;
        renderNewFilePreviews();
    }

    function removeExistingPhoto(photoId) {
        if (!deletedPhotoIds.includes(photoId)) {
            deletedPhotoIds.push(photoId);
            const card = document.getElementById('photo_card_db_' + photoId);
            if (card) {
                card.style.opacity = '0.3';
                card.style.filter = 'grayscale(100%)';
                card.style.pointerEvents = 'none';
            }
            updateHiddenInputs();
        }
    }

    function addCustomUrl() {
        const input = document.getElementById('custom_url_input');
        const url = input.value.trim();
        if (!url) return;
        
        customUrls.push(url);
        input.value = '';
        renderCustomUrlPreviews();
        updateHiddenInputs();
    }

    function removeCustomUrl(index) {
        customUrls.splice(index, 1);
        renderCustomUrlPreviews();
        updateHiddenInputs();
    }

    function updateHiddenInputs() {
        const container = document.getElementById('hidden_inputs_container');
        container.innerHTML = '';

        // Added deleted photo IDs
        deletedPhotoIds.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'delete_photo_ids[]';
            inp.value = id;
            container.appendChild(inp);
        });

        // Added custom URLs
        customUrls.forEach(url => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'image_urls[]';
            inp.value = url;
            container.appendChild(inp);
        });
    }

    function renderNewFilePreviews() {
        document.querySelectorAll('.preview-card-new-file').forEach(el => el.remove());
        const grid = document.getElementById('image_preview_grid');

        for (let i = 0; i < dt.files.length; i++) {
            const file = dt.files[i];
            const card = document.createElement('div');
            card.className = 'preview-card preview-card-new-file';
            card.style.cssText = 'position: relative; border-radius: 12px; overflow: hidden; border: 2px solid #0284c7; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.cssText = 'width: 100%; height: 120px; object-fit: cover; display: block;';

            const footer = document.createElement('div');
            footer.style.cssText = 'padding: 8px; display: flex; align-items: center; justify-content: space-between; background: #f0f9ff; border-top: 1px solid #e0f2fe;';
            
            const label = document.createElement('span');
            label.style.cssText = 'font-size: 0.72rem; font-weight: 700; color: #0284c7; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100px;';
            label.innerText = '🆕 ' + file.name;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.style.cssText = 'background: #fee2e2; color: #ef4444; border: none; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: 800; font-size: 0.8rem;';
            btn.innerText = '✕';
            btn.onclick = (function(idx) {
                return function() { removeLocalFile(idx); };
            })(i);

            footer.appendChild(label);
            footer.appendChild(btn);
            card.appendChild(img);
            card.appendChild(footer);
            grid.appendChild(card);
        }
    }

    function renderCustomUrlPreviews() {
        document.querySelectorAll('.preview-card-custom-url').forEach(el => el.remove());
        const grid = document.getElementById('image_preview_grid');

        customUrls.forEach((url, i) => {
            const card = document.createElement('div');
            card.className = 'preview-card preview-card-custom-url';
            card.style.cssText = 'position: relative; border-radius: 12px; overflow: hidden; border: 2px solid #8b5cf6; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);';

            const img = document.createElement('img');
            img.src = url;
            img.style.cssText = 'width: 100%; height: 120px; object-fit: cover; display: block;';
            img.onerror = function() { this.src = 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=400'; };

            const footer = document.createElement('div');
            footer.style.cssText = 'padding: 8px; display: flex; align-items: center; justify-content: space-between; background: #f5f3ff; border-top: 1px solid #ede9fe;';
            
            const label = document.createElement('span');
            label.style.cssText = 'font-size: 0.72rem; font-weight: 700; color: #7c3aed; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100px;';
            label.innerText = '🔗 URL #' + (i + 1);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.style.cssText = 'background: #fee2e2; color: #ef4444; border: none; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: 800; font-size: 0.8rem;';
            btn.innerText = '✕';
            btn.onclick = (function(idx) {
                return function() { removeCustomUrl(idx); };
            })(i);

            footer.appendChild(label);
            footer.appendChild(btn);
            card.appendChild(img);
            card.appendChild(footer);
            grid.appendChild(card);
        });
    }

    let mapResolveTimer = null;
    function autoDetectCoordsFromLink(val) {
        if (!val) return;
        val = val.trim();
        
        let lat = null;
        let lng = null;

        // Match @lat,lng
        let m1 = val.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (m1) {
            lat = m1[1];
            lng = m1[2];
        }

        // Match !3dlat!4dlng
        if (!lat) {
            let m2 = val.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
            if (m2) {
                lat = m2[1];
                lng = m2[2];
            }
        }

        // Match q=lat,lng or ll=lat,lng
        if (!lat) {
            let m3 = val.match(/[?&](?:q|ll)=(-?\d+\.\d+),(-?\d+\.\d+)/);
            if (m3) {
                lat = m3[1];
                lng = m3[2];
            }
        }

        // Match "21.1393, 105.8534"
        if (!lat) {
            let m4 = val.match(/^(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)$/);
            if (m4) {
                lat = m4[1];
                lng = m4[2];
            }
        }

        if (lat && lng) {
            document.getElementById('lat_input').value = lat;
            document.getElementById('lng_input').value = lng;
            const badge = document.getElementById('coords_detected_badge');
            badge.style.display = 'block';
            badge.innerText = `⚡ Đã tự động nhận diện Tọa độ từ Google Maps: ${lat}, ${lng}`;
            return;
        }

        // Nếu là Link rút gọn (maps.app.goo.gl) -> Gọi AJAX giải mã tức thì từ Server
        if (val.startsWith('http')) {
            clearTimeout(mapResolveTimer);
            const badge = document.getElementById('coords_detected_badge');
            badge.style.display = 'block';
            badge.style.background = '#e0f2fe';
            badge.style.color = '#0284c7';
            badge.style.borderColor = '#7dd3fc';
            badge.innerText = '⏳ Đang tự động giải mã Link Google Maps rút gọn...';

            mapResolveTimer = setTimeout(() => {
                fetch('{{ route("health-station.resolve-map-link") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ url: val })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.lat && data.lng) {
                        document.getElementById('lat_input').value = data.lat;
                        document.getElementById('lng_input').value = data.lng;
                        badge.style.background = '#dcfce7';
                        badge.style.color = '#15803d';
                        badge.style.borderColor = '#86efac';
                        badge.innerText = `⚡ Đã tự động giải mã Link Google Maps: Vĩ độ ${data.lat}, Kinh độ ${data.lng}`;
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(() => {
                    badge.style.display = 'none';
                });
            }, 300);
        }
    }
</script>
@endsection
