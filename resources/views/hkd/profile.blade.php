@extends('layouts.hkd')

@section('title', 'Hồ Sơ Cơ Sở Kinh Doanh & Định Vị — ' . $eatery->name)
@section('title_header', 'Hồ Sơ Cơ Sở Kinh Doanh & Vị Trí Bản Đồ')

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .hkd-form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; margin-bottom: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .hkd-form-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; }
    .hkd-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .hkd-form-group { display: flex; flex-direction: column; gap: 6px; }
    .hkd-form-label { font-size: 0.85rem; font-weight: 700; color: #334155; }
    .hkd-form-input, .hkd-form-select, .hkd-form-textarea {
        padding: 12px 16px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 0.92rem; font-weight: 600; outline: none; transition: border-color 0.2s;
    }
    .hkd-form-input:focus, .hkd-form-select:focus, .hkd-form-textarea:focus { border-color: #059669; }
</style>

<form action="{{ route('hkd.profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- THÔNG TIN CHÍNH THỨC VÀ PHÁP LÝ -->
    <div class="hkd-form-card">
        <div class="hkd-form-title">
            <i class="fa-solid fa-id-card" style="color: #059669;"></i>
            <span>Thông Tin Pháp Lý & Hành Chính</span>
        </div>
        <div class="hkd-form-grid">
            <div class="hkd-form-group">
                <label class="hkd-form-label">Tên HKD / Doanh Nghiệp <span style="color: #dc2626;">*</span></label>
                <input type="text" name="name" class="hkd-form-input" value="{{ old('name', $eatery->name) }}" required>
            </div>
            <div class="hkd-form-group">
                <label class="hkd-form-label">Mã Số Thuế (MST)</label>
                <input type="text" name="mst" class="hkd-form-input" value="{{ old('mst', $storyData['mst'] ?? '') }}" placeholder="Ví dụ: 8099245297-001">
            </div>
            <div class="hkd-form-group">
                <label class="hkd-form-label">Chủ Hộ Kinh Doanh / Đại Diện</label>
                <input type="text" name="owner_name" class="hkd-form-input" value="{{ old('owner_name', $storyData['owner_name'] ?? '') }}" placeholder="Tên chủ hộ">
            </div>
            <div class="hkd-form-group">
                <label class="hkd-form-label">Số Điện Thoại Liên Hệ <span style="color: #dc2626;">*</span></label>
                <input type="text" name="phone" class="hkd-form-input" value="{{ old('phone', $eatery->phone) }}" required>
            </div>
            <div class="hkd-form-group" style="grid-column: 1 / -1;">
                <label class="hkd-form-label">Ngành Nghề Đăng Ký Kinh Doanh</label>
                <input type="text" name="industry" class="hkd-form-input" value="{{ old('industry', $storyData['industry'] ?? '') }}" placeholder="Ví dụ: Bán lẻ trong cửa hàng kinh doanh tổng hợp khác">
            </div>
        </div>
    </div>

    <!-- ĐỊA CHỈ & ĐỊA VỊ BẢN ĐỒ GPS -->
    <div class="hkd-form-card">
        <div class="hkd-form-title">
            <i class="fa-solid fa-map-location-dot" style="color: #0284c7;"></i>
            <span>Địa Chỉ Trụ Sở & Tọa Độ Bản Đồ (GPS)</span>
        </div>
        <div class="hkd-form-grid">
            <!-- Address -->
            <div class="hkd-form-group" style="grid-column: 1 / -1;">
                <label class="hkd-form-label">Địa Chỉ Trụ Sở Chính <span style="color: #dc2626;">*</span></label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="address" id="address-input" class="hkd-form-input" style="flex: 1;"
                           value="{{ old('address', $eatery->address) }}" required
                           placeholder="Số nhà, thôn/xóm, xã, huyện Đông Anh, Hà Nội">
                    <button type="button" onclick="geocodeAddress()"
                            style="padding: 0 16px; background: #0284c7; color: #fff; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 700; cursor: pointer; white-space: nowrap; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-magnifying-glass-location"></i> Tìm tọa độ địa chỉ
                    </button>
                </div>
            </div>

            <!-- Google Maps link parser -->
            <div class="hkd-form-group" style="grid-column: 1 / -1;">
                <label class="hkd-form-label">
                    <i class="fa-brands fa-google" style="color: #ea4335;"></i>
                    Dán Link Google Maps <span style="font-weight: 500; color: #94a3b8;">(tự động lấy tọa độ)</span>
                </label>
                <div style="display: flex; gap: 10px; align-items: stretch;">
                    <input type="url" id="gmaps-url" class="hkd-form-input" style="flex: 1;"
                           placeholder="https://maps.google.com/... hoặc https://maps.app.goo.gl/...">
                    <button type="button" onclick="parseGmapsUrl()"
                            style="padding: 0 20px; background: #ea4335; color: #fff; border: none; border-radius: 10px; font-size: 0.88rem; font-weight: 700; cursor: pointer; white-space: nowrap; display: flex; align-items: center; gap: 7px; transition: opacity 0.2s;"
                            onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'">
                        <i class="fa-solid fa-location-crosshairs"></i> Lấy tọa độ
                    </button>
                </div>
                <div id="gmaps-feedback" style="font-size: 0.8rem; margin-top: 5px; display: none;"></div>
            </div>

            <!-- Lat / Lng -->
            <div class="hkd-form-group">
                <label class="hkd-form-label">Vĩ Độ (Latitude GPS)</label>
                <input type="number" step="any" name="latitude" id="lat-input" class="hkd-form-input"
                       value="{{ old('latitude', $eatery->latitude ?? 21.1402) }}" oninput="updateMapFromInputs()">
            </div>
            <div class="hkd-form-group">
                <label class="hkd-form-label">Kinh Độ (Longitude GPS)</label>
                <input type="number" step="any" name="longitude" id="lng-input" class="hkd-form-input"
                       value="{{ old('longitude', $eatery->longitude ?? 105.8495) }}" oninput="updateMapFromInputs()">
            </div>

            <!-- GPS auto-detect button -->
            <div class="hkd-form-group" style="grid-column: 1 / -1;">
                <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                    <button type="button" id="btn-gps" onclick="autoGps()"
                            style="display: inline-flex; align-items: center; gap: 9px; padding: 11px 22px; background: #10b981; color: #fff; border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s;">
                        <i class="fa-solid fa-location-dot"></i>
                        📍 Tự động lấy vị trí GPS hiện tại
                    </button>
                    <span style="font-size: 0.82rem; color: #64748b;">hoặc Click / Kéo thả ghim trên bản đồ bên dưới:</span>
                </div>
                <div id="gps-status" style="font-size: 0.85rem; font-weight: 600; margin-top: 8px;"></div>
            </div>

            <!-- Interactive Leaflet Map Picker -->
            <div class="hkd-form-group" style="grid-column: 1 / -1; margin-top: 6px;">
                <div id="hkd-profile-map" style="width: 100%; height: 300px; border-radius: 14px; border: 2px solid #cbd5e1; z-index: 1;"></div>
            </div>
        </div>

        <div style="margin-top: 16px; padding: 14px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; font-size: 0.85rem; color: #0369a1; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-info" style="font-size: 1.2rem;"></i>
            <span>Tọa độ GPS giúp khách hàng dễ dàng chỉ đường bằng Google Maps & Leaflet trên trang công khai địa điểm.</span>
        </div>
    </div>

    <!-- THÔNG TIN HIỂN THỊ CÔNG KHAI -->
    <div class="hkd-form-card">
        <div class="hkd-form-title">
            <i class="fa-solid fa-sliders" style="color: #d97706;"></i>
            <span>Thông Tin Giới Thiệu & Giờ Mở Cửa</span>
        </div>
        <div class="hkd-form-grid">
            <div class="hkd-form-group">
                <label class="hkd-form-label">Giờ Mở Cửa / Phục Vụ</label>
                <input type="text" name="opening_hours" class="hkd-form-input" value="{{ old('opening_hours', $eatery->opening_hours ?? '07:30 - 21:00') }}">
            </div>
            <div class="hkd-form-group">
                <label class="hkd-form-label">Mức Giá Tham Khảo</label>
                <input type="text" name="price_range" class="hkd-form-input" value="{{ old('price_range', $eatery->price_range ?? 'Liên hệ') }}">
            </div>
            <div class="hkd-form-group" style="grid-column: 1 / -1;">
                <label class="hkd-form-label">Mô Tả / Giới Thiệu Chi Tiết Về Cơ Sở</label>
                <textarea name="description" rows="4" class="hkd-form-textarea" placeholder="Giới thiệu về cơ sở kinh doanh, quy mô, sản phẩm chủ lực...">{{ old('description', $eatery->description) }}</textarea>
            </div>
            <div class="hkd-form-group" style="grid-column: 1 / -1;">
                <label class="hkd-form-label">Hình Ảnh Đại Diện / Ảnh Bìa Cơ Sở</label>
                <input type="file" name="image" accept="image/*" class="hkd-form-input">
                @if($eatery->image_path)
                    <div style="margin-top: 10px;">
                        <img src="{{ $eatery->image_path }}" style="max-height: 140px; border-radius: 10px; border: 1px solid #cbd5e1;">
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- TÀI KHOẢN NGÂN HÀNG VIETQR -->
    <div class="hkd-form-card">
        <div class="hkd-form-title">
            <i class="fa-solid fa-building-columns" style="color: #6366f1;"></i>
            <span>Cấu Hình Nhận Tiền Qua VietQR Ngân Hàng</span>
        </div>
        <div class="hkd-form-grid">
            <div class="hkd-form-group">
                <label class="hkd-form-label">Tên Ngân Hàng (Mã Ngân Hàng)</label>
                <input type="text" name="bank_name" class="hkd-form-input" value="{{ old('bank_name', $storyData['bank_name'] ?? '') }}" placeholder="Ví dụ: MBBank, VCB, Agribank, Techcombank">
            </div>
            <div class="hkd-form-group">
                <label class="hkd-form-label">Số Tài Khoản Ngân Hàng</label>
                <input type="text" name="bank_account" class="hkd-form-input" value="{{ old('bank_account', $storyData['bank_account'] ?? '') }}" placeholder="Số tài khoản">
            </div>
            <div class="hkd-form-group">
                <label class="hkd-form-label">Tên Chủ Tài Khoản (Không dấu)</label>
                <input type="text" name="bank_holder" class="hkd-form-input" value="{{ old('bank_holder', $storyData['bank_holder'] ?? '') }}" placeholder="Tên chủ tài khoản">
            </div>
        </div>
    </div>

    <!-- BUTTON LƯU THAY ĐỔI -->
    <div style="text-align: right; margin-bottom: 40px;">
        <button type="submit" class="hkd-btn-action hkd-btn-emerald" style="padding: 14px 32px; font-size: 1rem;">
            <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Hồ Sơ
        </button>
    </div>
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let profileMap = null;
let profileMarker = null;

document.addEventListener('DOMContentLoaded', function() {
    initProfileMap();
});

function initProfileMap() {
    const latInput = document.getElementById('lat-input');
    const lngInput = document.getElementById('lng-input');
    
    let lat = parseFloat(latInput.value) || 21.1402;
    let lng = parseFloat(lngInput.value) || 105.8495;

    if (profileMap) {
        profileMap.remove();
    }

    profileMap = L.map('hkd-profile-map').setView([lat, lng], 15);

    // Google Maps Tiles Layer (Fast, detailed, exact matches home.blade.php)
    L.tileLayer('https://mt1.google.com/vt/lyrs=m&hl=vi&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: 'abcd',
        attribution: '© Google Maps'
    }).addTo(profileMap);

    profileMarker = L.marker([lat, lng], { draggable: true }).addTo(profileMap);

    profileMarker.on('dragend', function(e) {
        const position = profileMarker.getLatLng();
        latInput.value = position.lat.toFixed(6);
        lngInput.value = position.lng.toFixed(6);
        showGpsStatus(`📍 Đã cập nhật tọa độ từ ghim bản đồ: <strong>${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}</strong>`, '#059669');
    });

    profileMap.on('click', function(e) {
        profileMarker.setLatLng(e.latlng);
        latInput.value = e.latlng.lat.toFixed(6);
        lngInput.value = e.latlng.lng.toFixed(6);
        showGpsStatus(`📍 Đã chọn vị trí mới trên bản đồ: <strong>${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}</strong>`, '#059669');
    });

    [100, 300, 800, 1500].forEach(delay => {
        setTimeout(function() {
            if (profileMap) profileMap.invalidateSize();
        }, delay);
    });
}

function updateMapFromInputs() {
    const lat = parseFloat(document.getElementById('lat-input').value);
    const lng = parseFloat(document.getElementById('lng-input').value);
    if (!isNaN(lat) && !isNaN(lng) && profileMap && profileMarker) {
        const newLatLng = new L.LatLng(lat, lng);
        profileMarker.setLatLng(newLatLng);
        profileMap.panTo(newLatLng);
        setTimeout(function() {
            if (profileMap) profileMap.invalidateSize();
        }, 100);
    }
}

function showGpsStatus(msg, color = '#334155') {
    const st = document.getElementById('gps-status');
    if (st) {
        st.style.color = color;
        st.innerHTML = msg;
    }
}

function autoGps() {
    const btn = document.getElementById('btn-gps');
    if (!navigator.geolocation) {
        showGpsStatus('❌ Trình duyệt của bạn không hỗ trợ lấy vị trí GPS tự động.', '#dc2626');
        return;
    }

    btn.disabled = true;
    btn.style.opacity = '0.6';
    showGpsStatus('⏳ Đang lấy vị trí GPS từ thiết bị của bạn... Vui lòng bấm <b>Cho phép (Allow)</b> khi trình duyệt hỏi quyền vị trí!', '#0284c7');

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            btn.disabled = false;
            btn.style.opacity = '1';
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            document.getElementById('lat-input').value = lat.toFixed(6);
            document.getElementById('lng-input').value = lng.toFixed(6);
            
            updateMapFromInputs();
            showGpsStatus(`✅ Tự động lấy GPS thành công: <strong>${lat.toFixed(6)}, ${lng.toFixed(6)}</strong>`, '#059669');
        },
        function(err) {
            btn.disabled = false;
            btn.style.opacity = '1';
            let msg = 'Không thể lấy được vị trí GPS.';
            if (err.code === 1) {
                msg = '⚠️ Quyền vị trí bị từ chối trên trình duyệt. Bạn có thể click chọn vị trí trực tiếp trên bản đồ hoặc bấm "Tìm tọa độ địa chỉ".';
            } else if (err.code === 2) {
                msg = '⚠️ Vị trí hiện tại không khả dụng. Hãy kiểm tra cài đặt GPS/Wifi trên thiết bị.';
            } else if (err.code === 3) {
                msg = '⚠️ Quá thời gian phản hồi GPS. Vui lòng thử lại.';
            }
            showGpsStatus(msg, '#dc2626');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function parseGmapsUrl() {
    const urlInput = document.getElementById('gmaps-url');
    const url = urlInput.value.trim();
    const fb = document.getElementById('gmaps-feedback');
    fb.style.display = 'block';

    if (!url) {
        fb.style.color = '#dc2626';
        fb.textContent = 'Vui lòng dán đường link Google Maps trước khi bấm nút.';
        return;
    }

    // Standard regex matches
    // 1. @21.1352,105.8458
    let match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
    
    // 2. !3d21.1352!4d105.8458
    if (!match) {
        match = url.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
    }
    
    // 3. q=21.1352,105.8458 or ll=21.1352,105.8458
    if (!match) {
        match = url.match(/[?&](?:q|ll|center)=(-?\d+\.\d+),(-?\d+\.\d+)/);
    }

    if (match) {
        const lat = parseFloat(match[1]);
        const lng = parseFloat(match[2]);
        document.getElementById('lat-input').value = lat.toFixed(6);
        document.getElementById('lng-input').value = lng.toFixed(6);
        updateMapFromInputs();

        fb.style.color = '#059669';
        fb.innerHTML = `✅ Đã trích xuất tọa độ từ Link Google Maps: <strong>${lat.toFixed(6)}, ${lng.toFixed(6)}</strong>`;
    } else {
        fb.style.color = '#d97706';
        fb.innerHTML = '⚠️ Link Google Maps rút gọn (maps.app.goo.gl) hoặc không có thông tin tọa độ thô. Đã kích hoạt tìm kiếm theo địa chỉ...';
        geocodeAddress();
    }
}

function geocodeAddress() {
    const addr = document.getElementById('address-input').value.trim();
    if (!addr) {
        showGpsStatus('Vui lòng nhập địa chỉ trước khi tìm.', '#dc2626');
        return;
    }

    let searchAddr = addr;
    if (!searchAddr.toLowerCase().includes('đông anh')) {
        searchAddr += ', Đông Anh, Hà Nội';
    }

    showGpsStatus(`⏳ Đang tìm kiếm tọa độ cho: "${searchAddr}"...`, '#0284c7');

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchAddr)}&limit=1`)
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                document.getElementById('lat-input').value = lat.toFixed(6);
                document.getElementById('lng-input').value = lng.toFixed(6);
                updateMapFromInputs();
                showGpsStatus(`✅ Đã tự động xác định vị trí theo địa chỉ: <strong>${lat.toFixed(6)}, ${lng.toFixed(6)}</strong>`, '#059669');
            } else {
                showGpsStatus('⚠️ Không tìm thấy tọa độ tự động. Bạn vui lòng click chọn ghim trực tiếp trên bản đồ bên dưới.', '#d97706');
            }
        })
        .catch(err => {
            showGpsStatus('⚠️ Lỗi kết nối dịch vụ định vị. Bạn có thể click chọn vị trí trực tiếp trên bản đồ.', '#dc2626');
        });
}
</script>
@endsection
