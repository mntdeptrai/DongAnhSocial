@extends('layouts.app')

@section('title', 'Thiết Lập Phòng Livestream - Studio Phát Trực Tiếp Đông Anh')
@section('meta_description', 'Khởi tạo phòng phát sóng trực tiếp chuyên nghiệp, gắn giỏ hàng sản phẩm OCOP Đông Anh và kiểm tra Camera, Microphone trước khi lên sóng.')

@section('content')
<div class="studio-wrapper">
    <!-- Ambient Background Lighting -->
    <div class="studio-ambient-glow glow-1"></div>
    <div class="studio-ambient-glow glow-2"></div>

    <div class="studio-container">
        <!-- Top Studio Bar -->
        <div class="studio-header">
            <div class="studio-header-left">
                <div class="live-studio-badge">
                    <span class="live-studio-pulse"></span>
                    <span>STUDIO CONTROL CENTER</span>
                </div>
                <h1 class="studio-title">Khởi Tạo Phòng Livestream</h1>
                <p class="studio-subtitle">Thiết lập kịch bản phát sóng, gắn giỏ hàng đặc sản OCOP và kiểm tra âm thanh & hình ảnh trước khi phát trực tiếp.</p>
            </div>
            <div class="studio-header-stats">
                <div class="studio-stat-item">
                    <span class="stat-icon">🎙️</span>
                    <div class="stat-text">
                        <span class="stat-label">Âm thanh</span>
                        <span class="stat-val" id="header-mic-status">Đang phát hiện...</span>
                    </div>
                </div>
                <div class="studio-stat-item">
                    <span class="stat-icon">📹</span>
                    <div class="stat-text">
                        <span class="stat-label">Độ phân giải</span>
                        <span class="stat-val">Full HD 1080p</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="studio-grid">
            <!-- Left: Settings & Product Picker -->
            <div class="studio-main-col">
                <form action="{{ route('livestream.store') }}" method="POST" enctype="multipart/form-data" id="live-setup-form">
                    @csrf

                    <!-- Card 1: Broadcast Info -->
                    <div class="studio-card">
                        <div class="card-header-clean">
                            <div class="card-header-icon">📺</div>
                            <div>
                                <h2 class="card-title-clean">1. Thông tin phiên phát sóng</h2>
                                <p class="card-desc-clean">Tiêu đề hấp dẫn và phân loại đúng chủ đề sẽ giúp thu hút nhiều người xem hơn.</p>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="studio-form-group">
                            <label class="studio-label" for="title">
                                Tiêu đề Livestream <span class="text-danger">*</span>
                            </label>
                            <div class="studio-input-wrap">
                                <span class="input-prefix-icon">✨</span>
                                <input type="text" name="title" id="title" class="studio-input" placeholder="Ví dụ: Đại tiệc Nông sản OCOP Đông Anh & Món ngon truyền thống..." required value="{{ old('title') }}" maxlength="150" oninput="updateTitleCounter(this)">
                                <span class="char-counter" id="title-counter">0/150</span>
                            </div>
                            @error('title')
                                <span class="studio-error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Category Grid Selector -->
                        <div class="studio-form-group">
                            <label class="studio-label">
                                Chủ đề phát sóng <span class="text-danger">*</span>
                            </label>
                            <div class="category-radio-grid">
                                <label class="category-radio-card {{ old('category', 'ocop') == 'ocop' ? 'active' : '' }}">
                                    <input type="radio" name="category" value="ocop" {{ old('category', 'ocop') == 'ocop' ? 'checked' : '' }} onchange="selectCategoryCard(this)">
                                    <div class="cat-card-icon">🌾</div>
                                    <div class="cat-card-info">
                                        <span class="cat-card-title">Nông sản OCOP</span>
                                        <span class="cat-card-desc">Bán hàng & giới thiệu nông sản</span>
                                    </div>
                                    <div class="cat-check">✓</div>
                                </label>

                                <label class="category-radio-card {{ old('category') == 'food' ? 'active' : '' }}">
                                    <input type="radio" name="category" value="food" {{ old('category') == 'food' ? 'checked' : '' }} onchange="selectCategoryCard(this)">
                                    <div class="cat-card-icon">🍜</div>
                                    <div class="cat-card-info">
                                        <span class="cat-card-title">Ẩm thực & Quán ngon</span>
                                        <span class="cat-card-desc">Khám phá món ngon Đông Anh</span>
                                    </div>
                                    <div class="cat-check">✓</div>
                                </label>

                                <label class="category-radio-card {{ old('category') == 'travel' ? 'active' : '' }}">
                                    <input type="radio" name="category" value="travel" {{ old('category') == 'travel' ? 'checked' : '' }} onchange="selectCategoryCard(this)">
                                    <div class="cat-card-icon">🗺️</div>
                                    <div class="cat-card-info">
                                        <span class="cat-card-title">Du lịch & Check-in</span>
                                        <span class="cat-card-desc">Review điểm đến & di tích</span>
                                    </div>
                                    <div class="cat-check">✓</div>
                                </label>

                                <label class="category-radio-card {{ old('category') == 'culture' ? 'active' : '' }}">
                                    <input type="radio" name="category" value="culture" {{ old('category') == 'culture' ? 'checked' : '' }} onchange="selectCategoryCard(this)">
                                    <div class="cat-card-icon">🏺</div>
                                    <div class="cat-card-info">
                                        <span class="cat-card-title">Văn hóa & Lễ hội</span>
                                        <span class="cat-card-desc">Cổ Loa, Đền Sái, làng nghề</span>
                                    </div>
                                    <div class="cat-check">✓</div>
                                </label>

                                <label class="category-radio-card {{ old('category') == 'general' ? 'active' : '' }}" style="grid-column: 1 / -1;">
                                    <input type="radio" name="category" value="general" {{ old('category') == 'general' ? 'checked' : '' }} onchange="selectCategoryCard(this)">
                                    <div class="cat-card-icon">💬</div>
                                    <div class="cat-card-info">
                                        <span class="cat-card-title">Giao lưu & Đời sống</span>
                                        <span class="cat-card-desc">Tâm sự, trò chuyện cùng cộng đồng Đông Anh</span>
                                    </div>
                                    <div class="cat-check">✓</div>
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="studio-form-group" style="margin-bottom: 0;">
                            <label class="studio-label" for="description">Mô tả ngắn phiên phát</label>
                            <textarea name="description" id="description" rows="2" class="studio-textarea" placeholder="Chia sẻ đôi nét về nội dung, ưu đãi quà tặng hoặc khách mời trong buổi live này...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- Card 2: Showcase Products & Cart -->
                    <div class="studio-card">
                        <div class="card-header-clean">
                            <div class="card-header-icon">🛍️</div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h2 class="card-title-clean">2. Giỏ hàng Livestream & Sản phẩm Ghim</h2>
                                    <span class="selected-badge" id="selected-prod-count">Đã chọn: 0 sản phẩm</span>
                                </div>
                                <p class="card-desc-clean">Khán giả có thể xem thông tin, giá bán và đặt hàng trực tiếp ngay trong lúc xem Live.</p>
                            </div>
                        </div>

                        <!-- Search & Quick Filters -->
                        <div class="product-filter-bar">
                            <div class="studio-search-wrap">
                                <span class="search-icon">🔍</span>
                                <input type="text" id="create-prod-search" class="studio-search-input" placeholder="Tìm nhanh sản phẩm OCOP theo tên hoặc nhà cung cấp..." oninput="filterCreateProducts(this.value)">
                            </div>
                            <div class="quick-filter-pills">
                                <button type="button" class="filter-pill active" onclick="setProductFilter('all', this)">Tất cả ({{ $ocopProducts->count() }})</button>
                                <button type="button" class="filter-pill" onclick="setProductFilter('4sao', this)">⭐ OCOP 4 sao</button>
                                <button type="button" class="filter-pill" onclick="setProductFilter('3sao', this)">⭐ OCOP 3 sao</button>
                            </div>
                        </div>

                        <!-- Product List Grid -->
                        <div class="products-grid-scroll" id="product-picker-container">
                            @foreach($ocopProducts as $product)
                                @php
                                    $isRating4 = str_contains($product->star_rating ?? '', '4');
                                    $isRating3 = str_contains($product->star_rating ?? '', '3');
                                    $ratingTag = $isRating4 ? '4sao' : ($isRating3 ? '3sao' : 'other');
                                @endphp
                                <label class="prod-item-row" data-name="{{ Str::lower($product->name) }}" data-tag="{{ $ratingTag }}" id="card-prod-{{ $product->id }}">
                                    <div class="prod-checkbox-wrap">
                                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="prod-checkbox" onchange="updateSelectedProductsCount()" {{ (is_array(old('product_ids')) && in_array($product->id, old('product_ids'))) ? 'checked' : '' }}>
                                        <div class="custom-checkbox">
                                            <svg viewBox="0 0 16 16" fill="none"><path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                    </div>

                                    <img src="{{ $product->image_url ? (str_starts_with($product->image_url, 'http') ? $product->image_url : asset($product->image_url)) : '/images/ocop-placeholder.png' }}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="prod-thumb-img" alt="{{ $product->name }}">

                                    <div class="prod-details-col">
                                        <div class="prod-title">{{ $product->name }}</div>
                                        <div class="prod-meta">
                                            <span class="prod-price-text">{{ $product->price ? number_format($product->price) . 'đ' : 'Liên hệ' }}</span>
                                            @if($product->star_rating)
                                                <span class="prod-star-pill">
                                                    {{ $product->star_rating }}
                                                </span>
                                            @endif
                                            @if($product->seller_name)
                                                <span class="prod-seller-pill">🏪 {{ Str::limit($product->seller_name, 26) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="prod-pin-action" title="Ghim sản phẩm này làm tâm điểm đầu tiên khi mở Live">
                                        <input type="radio" name="pinned_product_id" value="{{ $product->id }}" class="pin-radio" id="pin-{{ $product->id }}" onclick="event.stopPropagation(); setPrimaryPin({{ $product->id }})" {{ old('pinned_product_id') == $product->id ? 'checked' : '' }}>
                                        <label for="pin-{{ $product->id }}" class="pin-btn-label" onclick="event.stopPropagation(); setPrimaryPin({{ $product->id }})">
                                            <span class="pin-icon">📌</span>
                                            <span class="pin-text">Ghim đầu</span>
                                        </label>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Card 3: Thumbnail & Extra -->
                    <div class="studio-card">
                        <div class="card-header-clean">
                            <div class="card-header-icon">🖼️</div>
                            <div>
                                <h2 class="card-title-clean">3. Ảnh bìa Thumbnail (Tùy chọn)</h2>
                                <p class="card-desc-clean">Tải lên hình ảnh bắt mắt để hiển thị trên danh sách gợi ý của cộng đồng.</p>
                            </div>
                        </div>

                        <div class="thumbnail-upload-zone" onclick="document.getElementById('cover_image').click()">
                            <input type="file" name="cover_image" id="cover_image" accept="image/*" style="display: none;" onchange="handleThumbnailPreview(this)">
                            <div class="upload-inner" id="thumbnail-drop-content">
                                <div class="upload-icon-circle">📸</div>
                                <div class="upload-instruction">
                                    <strong>Nhấp để chọn ảnh</strong> hoặc kéo thả file vào đây
                                </div>
                                <div class="upload-hint">Định dạng JPG, PNG, WEBP (Khuyến nghị tỉ lệ 16:9 hoặc ảnh ngang)</div>
                            </div>
                            <div class="thumbnail-preview-wrap" id="thumbnail-preview-wrap" style="display: none;">
                                <img id="thumbnail-preview-img" src="" alt="Thumbnail preview">
                                <button type="button" class="btn-remove-thumb" onclick="event.stopPropagation(); removeThumbnailPreview()">✕ Đổi ảnh</button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="studio-submit-bar">
                        <a href="{{ route('livestream.index') }}" class="btn-studio-back">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            <span>Quay lại</span>
                        </a>
                        <button type="submit" class="btn-launch-broadcast" id="btn-submit-stream">
                            <span class="btn-live-dot"></span>
                            <span class="btn-text">BẮT ĐẦU PHÁT SÓNG TRỰC TIẾP</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Studio Monitor & Health Checklist -->
            <div class="studio-sidebar-col">
                <div class="monitor-console">
                    <!-- Monitor Header -->
                    <div class="monitor-header">
                        <div class="monitor-brand">
                            <span class="monitor-led"></span>
                            <span>MONITOR LIVE PREVIEW</span>
                        </div>
                        <div class="monitor-res">1080p • 30fps</div>
                    </div>

                    <!-- Screen Frame -->
                    <div class="monitor-screen-container">
                        <video id="camera-test-video" autoplay playsinline muted></video>
                        
                        <!-- Overlay Elements Mockup -->
                        <div class="monitor-screen-overlay" id="screen-overlay">
                            <div class="mockup-top-bar">
                                <div class="mockup-live-pill">
                                    <span class="pulse-mini"></span>
                                    <span>LIVE</span>
                                </div>
                                <div class="mockup-viewer-pill">
                                    <span>👁️ 0 người xem</span>
                                </div>
                            </div>
                            <div class="mockup-bottom-bar">
                                <div class="mockup-host-info">
                                    <div class="mockup-host-avatar">
                                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="mockup-host-name">
                                        {{ auth()->user()->name ?? 'Đông Anh Host' }}
                                    </div>
                                </div>
                                <div class="mockup-bag-pill" id="mockup-pinned-bubble" style="display: none;">
                                    <span class="bag-icon">🛍️</span>
                                    <span class="bag-name" id="mockup-pinned-name">Đang chọn...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Fallback / Loading state -->
                        <div class="monitor-placeholder" id="camera-placeholder">
                            <div class="placeholder-icon">📷</div>
                            <p class="placeholder-msg">Đang kết nối Camera & Microphone...</p>
                            <span class="placeholder-sub">Vui lòng bấm "Cho phép" (Allow) trên trình duyệt</span>
                        </div>
                    </div>

                    <!-- Audio VU Meter Bar -->
                    <div class="audio-vu-container">
                        <div class="vu-label">
                            <span>🎙️ Âm lượng Mic:</span>
                            <span id="vu-db-text" style="color: #10b981; font-weight: 700;">Sẵn sàng</span>
                        </div>
                        <div class="vu-meter-track">
                            <div class="vu-meter-fill" id="vu-meter-bar"></div>
                        </div>
                    </div>

                    <!-- Quick Device Controls -->
                    <div class="monitor-controls-row">
                        <button type="button" class="monitor-btn" id="btn-toggle-test-cam" onclick="toggleTestCam()">
                            <span class="btn-icon">📹</span>
                            <span class="btn-label">Tắt Camera</span>
                        </button>
                        <button type="button" class="monitor-btn" id="btn-toggle-test-mic" onclick="toggleTestMic()">
                            <span class="btn-icon">🎙️</span>
                            <span class="btn-label">Tắt Micro</span>
                        </button>
                    </div>

                    <!-- Stream Readiness Checklist -->
                    <div class="readiness-card">
                        <h4 class="readiness-title">
                            <span>⚡</span> Kiểm tra điều kiện phát sóng
                        </h4>
                        <div class="readiness-item" id="chk-cam">
                            <div class="chk-status checking"></div>
                            <div class="chk-text">
                                <strong>Thiết bị Video Camera</strong>
                                <span id="chk-cam-desc">Đang kiểm tra tín hiệu...</span>
                            </div>
                        </div>
                        <div class="readiness-item" id="chk-mic">
                            <div class="chk-status checking"></div>
                            <div class="chk-text">
                                <strong>Micro thu âm thanh</strong>
                                <span id="chk-mic-desc">Đang kiểm tra đầu vào...</span>
                            </div>
                        </div>
                        <div class="readiness-item" id="chk-net">
                            <div class="chk-status ready"></div>
                            <div class="chk-text">
                                <strong>Đường truyền mạng</strong>
                                <span>Băng thông ổn định sẵn sàng lên sóng</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pro Tips Card -->
                    <div class="pro-tips-card">
                        <div class="pro-tips-title">💡 Mẹo có phiên Live triệu view:</div>
                        <ul>
                            <li>Đặt camera ngang tầm mắt và giữ góc sáng hướng trực diện.</li>
                            <li>Ghim sẵn 1 sản phẩm OCOP tiêu biểu để tạo sự chú ý đầu phiên.</li>
                            <li>Tương tác nhiệt tình, trả lời bình luận của bà con người xem.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let testStream = null;
let testCamEnabled = true;
let testMicEnabled = true;
let audioContext = null;
let analyser = null;
let microphone = null;

async function initCameraTest() {
    const placeholder = document.getElementById('camera-placeholder');
    const chkCam = document.getElementById('chk-cam');
    const chkCamDesc = document.getElementById('chk-cam-desc');
    const chkMic = document.getElementById('chk-mic');
    const chkMicDesc = document.getElementById('chk-mic-desc');
    const headerMicStatus = document.getElementById('header-mic-status');

    try {
        testStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: true
        });

        const videoEl = document.getElementById('camera-test-video');
        if (videoEl && testStream) {
            videoEl.srcObject = testStream;
            if (placeholder) placeholder.style.display = 'none';
        }

        // Cam Success
        if (chkCam) {
            chkCam.querySelector('.chk-status').className = 'chk-status ready';
            if (chkCamDesc) chkCamDesc.innerText = 'Camera 720p/1080p hoạt động tốt';
        }

        // Mic Success & Init Audio Visualizer
        if (chkMic) {
            chkMic.querySelector('.chk-status').className = 'chk-status ready';
            if (chkMicDesc) chkMicDesc.innerText = 'Microphone đã kết nối và bắt tiếng';
        }
        if (headerMicStatus) headerMicStatus.innerText = 'Chuẩn bị tốt';

        initAudioVisualizer(testStream);

    } catch (err) {
        console.warn('Cannot access test camera/mic:', err);
        if (placeholder) {
            placeholder.innerHTML = `
                <div class="placeholder-icon" style="color: #ef4444;">⚠️</div>
                <p class="placeholder-msg" style="color: #fca5a5;">Không thể mở Camera / Micro</p>
                <span class="placeholder-sub">Vui lòng nhấp vào biểu tượng ổ khóa/camera trên thanh địa chỉ trình duyệt để Cấp Quyền.</span>
            `;
        }

        if (chkCam) {
            chkCam.querySelector('.chk-status').className = 'chk-status error';
            if (chkCamDesc) chkCamDesc.innerText = 'Chưa cấp quyền hoặc camera bận';
        }
        if (chkMic) {
            chkMic.querySelector('.chk-status').className = 'chk-status error';
            if (chkMicDesc) chkMicDesc.innerText = 'Không phát hiện micro khả dụng';
        }
        if (headerMicStatus) headerMicStatus.innerText = 'Chưa kết nối';
    }
}

function initAudioVisualizer(stream) {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        audioContext = new AudioContext();
        analyser = audioContext.createAnalyser();
        microphone = audioContext.createMediaStreamSource(stream);
        analyser.fftSize = 256;
        microphone.connect(analyser);

        const dataArray = new Uint8Array(analyser.frequencyBinCount);
        const vuBar = document.getElementById('vu-meter-bar');

        function renderFrame() {
            if (!testMicEnabled) {
                if (vuBar) vuBar.style.width = '0%';
                requestAnimationFrame(renderFrame);
                return;
            }
            analyser.getByteFrequencyData(dataArray);
            let sum = 0;
            for (let i = 0; i < dataArray.length; i++) {
                sum += dataArray[i];
            }
            let average = sum / dataArray.length;
            let percent = Math.min(100, Math.round((average / 100) * 100));
            if (vuBar) {
                vuBar.style.width = `${Math.max(6, percent)}%`;
            }
            requestAnimationFrame(renderFrame);
        }
        renderFrame();
    } catch (e) {
        console.warn('VU meter not supported', e);
    }
}

function toggleTestCam() {
    if (!testStream) return;
    const tracks = testStream.getVideoTracks();
    if (tracks.length > 0) {
        testCamEnabled = !testCamEnabled;
        tracks[0].enabled = testCamEnabled;
        const btn = document.getElementById('btn-toggle-test-cam');
        if (btn) {
            btn.innerHTML = testCamEnabled 
                ? '<span class="btn-icon">📹</span><span class="btn-label">Tắt Camera</span>'
                : '<span class="btn-icon">🚫</span><span class="btn-label">Bật Camera</span>';
            btn.classList.toggle('off', !testCamEnabled);
        }
        const video = document.getElementById('camera-test-video');
        if (video) video.style.opacity = testCamEnabled ? '1' : '0.1';
    }
}

function toggleTestMic() {
    if (!testStream) return;
    const tracks = testStream.getAudioTracks();
    if (tracks.length > 0) {
        testMicEnabled = !testMicEnabled;
        tracks[0].enabled = testMicEnabled;
        const btn = document.getElementById('btn-toggle-test-mic');
        const vuText = document.getElementById('vu-db-text');
        if (btn) {
            btn.innerHTML = testMicEnabled
                ? '<span class="btn-icon">🎙️</span><span class="btn-label">Tắt Micro</span>'
                : '<span class="btn-icon">🔇</span><span class="btn-label">Bật Micro</span>';
            btn.classList.toggle('off', !testMicEnabled);
        }
        if (vuText) {
            vuText.innerText = testMicEnabled ? 'Sẵn sàng' : 'Đã tắt mic';
            vuText.style.color = testMicEnabled ? '#10b981' : '#ef4444';
        }
    }
}

function switchStreamSource(type) {
    document.querySelectorAll('.source-card').forEach(c => {
        c.style.borderColor = '#e2e8f0';
        c.style.background = '#ffffff';
    });
    const active = document.getElementById(`source-card-${type}`);
    if (active) {
        active.style.borderColor = '#0ea5e9';
        active.style.background = '#f0f9ff';
    }

    const autoBox = document.getElementById('youtube-auto-box');
    if (autoBox) {
        autoBox.style.display = (type === 'youtube_auto') ? 'block' : 'none';
    }

    const ytBox = document.getElementById('youtube-url-box');
    if (ytBox) {
        ytBox.style.display = (type === 'youtube') ? 'block' : 'none';
    }
}

function extractYouTubeId(url) {
    if (!url) return null;
    if (/^[a-zA-Z0-9_-]{11}$/.test(url.trim())) return url.trim();
    const match = url.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i);
    return match ? match[1] : null;
}

function checkYouTubePreview(val) {
    const videoId = extractYouTubeId(val);
    const previewBox = document.getElementById('yt-preview-box');
    const previewThumb = document.getElementById('yt-preview-thumb');
    const previewId = document.getElementById('yt-preview-id');

    if (videoId) {
        if (previewThumb) previewThumb.src = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
        if (previewId) previewId.innerText = `YouTube Video ID: ${videoId}`;
        if (previewBox) previewBox.style.display = 'flex';
    } else {
        if (previewBox) previewBox.style.display = 'none';
    }
}

function updateTitleCounter(input) {
    const counter = document.getElementById('title-counter');
    if (counter) {
        counter.innerText = `${input.value.length}/150`;
        counter.style.color = input.value.length > 130 ? '#ef4444' : '#94a3b8';
    }
}

function selectCategoryCard(radio) {
    document.querySelectorAll('.category-radio-card').forEach(card => {
        card.classList.remove('active');
    });
    if (radio.checked) {
        radio.closest('.category-radio-card').classList.add('active');
    }
}

function filterCreateProducts(query) {
    const q = (query || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.prod-item-row');
    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        if (!q || name.includes(q)) {
            row.style.display = 'flex';
        } else {
            row.style.display = 'none';
        }
    });
}

function setProductFilter(tag, btn) {
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const rows = document.querySelectorAll('.prod-item-row');
    rows.forEach(row => {
        const rowTag = row.getAttribute('data-tag');
        if (tag === 'all' || rowTag === tag) {
            row.style.display = 'flex';
        } else {
            row.style.display = 'none';
        }
    });
}

function updateSelectedProductsCount() {
    const checked = document.querySelectorAll('.prod-checkbox:checked');
    const badge = document.getElementById('selected-prod-count');
    if (badge) {
        badge.innerText = `Đã chọn: ${checked.length} sản phẩm`;
        badge.classList.toggle('has-items', checked.length > 0);
    }

    document.querySelectorAll('.prod-item-row').forEach(row => {
        const cb = row.querySelector('.prod-checkbox');
        if (cb && cb.checked) {
            row.classList.add('selected');
        } else {
            row.classList.remove('selected');
        }
    });
}

function setPrimaryPin(prodId) {
    const row = document.getElementById(`card-prod-${prodId}`);
    if (row) {
        const cb = row.querySelector('.prod-checkbox');
        if (cb && !cb.checked) {
            cb.checked = true;
            updateSelectedProductsCount();
        }

        const title = row.querySelector('.prod-title')?.innerText || '';
        const mockupBubble = document.getElementById('mockup-pinned-bubble');
        const mockupName = document.getElementById('mockup-pinned-name');
        if (mockupBubble && mockupName) {
            mockupBubble.style.display = 'inline-flex';
            mockupName.innerText = title;
        }
    }
}

function handleThumbnailPreview(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const dropContent = document.getElementById('thumbnail-drop-content');
            const previewWrap = document.getElementById('thumbnail-preview-wrap');
            const previewImg = document.getElementById('thumbnail-preview-img');
            if (dropContent) dropContent.style.display = 'none';
            if (previewWrap && previewImg) {
                previewImg.src = e.target.result;
                previewWrap.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeThumbnailPreview() {
    const input = document.getElementById('cover_image');
    if (input) input.value = '';
    const dropContent = document.getElementById('thumbnail-drop-content');
    const previewWrap = document.getElementById('thumbnail-preview-wrap');
    if (dropContent) dropContent.style.display = 'flex';
    if (previewWrap) previewWrap.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    initCameraTest();
    updateSelectedProductsCount();
    
    // Check if a radio pin was already checked
    const checkedPin = document.querySelector('.pin-radio:checked');
    if (checkedPin) {
        setPrimaryPin(checkedPin.value);
    }
});
</script>

<style>
/* -------------------------------------------------------------
   MODERN HIGH-END STUDIO STYLING
------------------------------------------------------------- */
.studio-wrapper {
    position: relative;
    min-height: 100vh;
    background: #f8fafc;
    padding: 36px 16px 80px 16px;
    font-family: 'Plus Jakarta Sans', 'Be Vietnam Pro', -apple-system, sans-serif;
    overflow-x: hidden;
}

.studio-ambient-glow {
    position: absolute;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    filter: blur(120px);
    opacity: 0.18;
    pointer-events: none;
    z-index: 0;
}
.glow-1 {
    top: 40px;
    left: -100px;
    background: #ef4444;
}
.glow-2 {
    top: 300px;
    right: -100px;
    background: #3b82f6;
}

.studio-container {
    max-width: 1240px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* Header */
.studio-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 20px;
}
.live-studio-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}
.live-studio-pulse {
    width: 8px;
    height: 8px;
    background: #ef4444;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: liveBadgePulse 1.6s infinite;
}
@keyframes liveBadgePulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.studio-title {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin: 0 0 6px 0;
}
.studio-subtitle {
    font-size: 0.95rem;
    color: #64748b;
    margin: 0;
    max-width: 680px;
    line-height: 1.5;
}

.studio-header-stats {
    display: flex;
    gap: 12px;
}
.studio-stat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 8px 16px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
}
.stat-icon {
    font-size: 1.25rem;
}
.stat-text {
    display: flex;
    flex-direction: column;
}
.stat-label {
    font-size: 0.72rem;
    color: #94a3b8;
    font-weight: 600;
    text-transform: uppercase;
}
.stat-val {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f172a;
}

/* Grid Layout */
.studio-grid {
    display: grid;
    grid-template-columns: 1fr 430px;
    gap: 28px;
    align-items: start;
}

/* Cards */
.studio-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.03);
    transition: box-shadow 0.2s;
}
.studio-card:hover {
    box-shadow: 0 16px 30px -5px rgba(15, 23, 42, 0.06);
}

.card-header-clean {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 22px;
}
.card-header-icon {
    width: 44px;
    height: 44px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.card-title-clean {
    font-size: 1.12rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px 0;
}
.card-desc-clean {
    font-size: 0.86rem;
    color: #64748b;
    margin: 0;
    line-height: 1.4;
}

/* Form inputs */
.studio-form-group {
    margin-bottom: 20px;
}
.studio-label {
    display: block;
    font-size: 0.88rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}
.studio-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.input-prefix-icon {
    position: absolute;
    left: 14px;
    font-size: 1rem;
    pointer-events: none;
}
.studio-input {
    width: 100%;
    padding: 13px 70px 13px 44px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    font-size: 0.95rem;
    color: #0f172a;
    font-family: inherit;
    transition: all 0.2s;
}
.studio-input:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}
.char-counter {
    position: absolute;
    right: 14px;
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 600;
    pointer-events: none;
}
.studio-textarea {
    width: 100%;
    padding: 12px 16px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    font-size: 0.92rem;
    color: #0f172a;
    font-family: inherit;
    resize: vertical;
    transition: all 0.2s;
    box-sizing: border-box;
}
.studio-textarea:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}
.studio-error-text {
    display: block;
    color: #ef4444;
    font-size: 0.82rem;
    margin-top: 6px;
    font-weight: 600;
}

/* Category Grid */
.category-radio-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.category-radio-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
    position: relative;
}
.category-radio-card input {
    display: none;
}
.category-radio-card:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}
.category-radio-card.active {
    background: #ffffff;
    border-color: #ef4444;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.12);
}
.cat-card-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}
.cat-card-info {
    flex: 1;
    min-width: 0;
}
.cat-card-title {
    display: block;
    font-size: 0.88rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
.cat-card-desc {
    display: block;
    font-size: 0.74rem;
    color: #64748b;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cat-check {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #f1f5f9;
    color: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 800;
    flex-shrink: 0;
    transition: all 0.2s;
}
.category-radio-card.active .cat-check {
    background: #ef4444;
    color: #ffffff;
}

/* Products Showcase */
.selected-badge {
    font-size: 0.78rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #64748b;
    transition: all 0.2s;
}
.selected-badge.has-items {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.product-filter-bar {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
}
.studio-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.search-icon {
    position: absolute;
    left: 14px;
    font-size: 0.9rem;
    color: #94a3b8;
    pointer-events: none;
}
.studio-search-input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.88rem;
    color: #0f172a;
    background: #f8fafc;
    transition: all 0.2s;
    box-sizing: border-box;
}
.studio-search-input:focus {
    outline: none;
    background: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.quick-filter-pills {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 2px;
}
.filter-pill {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}
.filter-pill:hover {
    border-color: #cbd5e1;
    color: #0f172a;
}
.filter-pill.active {
    background: #0f172a;
    border-color: #0f172a;
    color: #ffffff;
}

.products-grid-scroll {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 310px;
    overflow-y: auto;
    padding-right: 4px;
}
.products-grid-scroll::-webkit-scrollbar {
    width: 6px;
}
.products-grid-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 99px;
}

.prod-item-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
}
.prod-item-row:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
}
.prod-item-row.selected {
    background: #f0fdf4;
    border-color: #10b981;
}

.prod-checkbox-wrap input {
    display: none;
}
.custom-checkbox {
    width: 22px;
    height: 22px;
    border: 2px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    transition: all 0.2s;
    flex-shrink: 0;
}
.custom-checkbox svg {
    width: 14px;
    height: 14px;
    display: none;
}
.prod-item-row.selected .custom-checkbox {
    background: #10b981;
    border-color: #10b981;
}
.prod-item-row.selected .custom-checkbox svg {
    display: block;
}

.prod-thumb-img {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
    background: #ffffff;
}

.prod-details-col {
    flex: 1;
    min-width: 0;
}
.prod-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}
.prod-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 3px;
    flex-wrap: wrap;
}
.prod-price-text {
    font-size: 0.85rem;
    font-weight: 800;
    color: #ef4444;
}
.prod-star-pill {
    font-size: 0.72rem;
    font-weight: 700;
    color: #b45309;
    background: #fef3c7;
    padding: 1px 7px;
    border-radius: 6px;
}
.prod-seller-pill {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 500;
}

.prod-pin-action {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}
.prod-pin-action input {
    display: none;
}
.pin-btn-label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 6px 10px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}
.pin-btn-label:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.prod-pin-action input:checked + .pin-btn-label {
    background: #ef4444;
    border-color: #ef4444;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

/* Thumbnail Upload */
.thumbnail-upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 18px;
    padding: 24px;
    background: #f8fafc;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}
.thumbnail-upload-zone:hover {
    border-color: #ef4444;
    background: #fff5f5;
}
.upload-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.upload-icon-circle {
    width: 48px;
    height: 48px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 4px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
}
.upload-instruction {
    font-size: 0.88rem;
    color: #1e293b;
}
.upload-hint {
    font-size: 0.75rem;
    color: #94a3b8;
}
.thumbnail-preview-wrap {
    position: relative;
    max-width: 320px;
    margin: 0 auto;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
}
.thumbnail-preview-wrap img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
}
.btn-remove-thumb {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(4px);
    color: #ffffff;
    border: none;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
}

/* Submit bar */
.studio-submit-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 24px;
}
.btn-studio-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 20px;
    border-radius: 16px;
    color: #64748b;
    font-weight: 700;
    text-decoration: none;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}
.btn-studio-back:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.btn-launch-broadcast {
    position: relative;
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    border: none;
    padding: 16px 28px;
    border-radius: 16px;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.3px;
    cursor: pointer;
    box-shadow: 0 10px 25px -4px rgba(239, 68, 68, 0.5);
    transition: all 0.25s;
    overflow: hidden;
}
.btn-launch-broadcast:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px -4px rgba(239, 68, 68, 0.6);
}
.btn-live-dot {
    width: 10px;
    height: 10px;
    background: #ffffff;
    border-radius: 50%;
    animation: liveBadgePulse 1.2s infinite;
}

/* -------------------------------------------------------------
   RIGHT SIDEBAR: STUDIO MONITOR & CONSOLE
------------------------------------------------------------- */
.monitor-console {
    background: #0f172a;
    border-radius: 26px;
    padding: 22px;
    color: #ffffff;
    position: sticky;
    top: 24px;
    box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.monitor-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.monitor-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.78rem;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 0.5px;
}
.monitor-led {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 8px #10b981;
}
.monitor-res {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    background: rgba(255, 255, 255, 0.06);
    padding: 3px 8px;
    border-radius: 6px;
}

.monitor-screen-container {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 10;
    background: #1e293b;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.5);
    margin-bottom: 14px;
}
.monitor-screen-container video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scaleX(-1);
    transition: opacity 0.2s;
}

.monitor-screen-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 12px;
    pointer-events: none;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, transparent 35%, transparent 65%, rgba(0, 0, 0, 0.6) 100%);
}
.mockup-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mockup-live-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ef4444;
    color: #ffffff;
    font-size: 0.68rem;
    font-weight: 900;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.5px;
}
.pulse-mini {
    width: 6px;
    height: 6px;
    background: #ffffff;
    border-radius: 50%;
}
.mockup-viewer-pill {
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    color: #f1f5f9;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
}
.mockup-bottom-bar {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.mockup-host-info {
    display: flex;
    align-items: center;
    gap: 8px;
}
.mockup-host-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid #ffffff;
}
.mockup-host-name {
    font-size: 0.78rem;
    font-weight: 700;
    color: #ffffff;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
}
.mockup-bag-pill {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    color: #ffffff;
    max-width: 85%;
}
.bag-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 600;
}

.monitor-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
    background: #1e293b;
    z-index: 10;
}
.placeholder-icon {
    font-size: 2.2rem;
    margin-bottom: 6px;
}
.placeholder-msg {
    font-size: 0.88rem;
    font-weight: 700;
    color: #f1f5f9;
    margin: 0 0 4px 0;
}
.placeholder-sub {
    font-size: 0.75rem;
    color: #94a3b8;
    line-height: 1.4;
}

/* Audio VU Meter */
.audio-vu-container {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 10px 14px;
    margin-bottom: 14px;
}
.vu-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #94a3b8;
    margin-bottom: 6px;
}
.vu-meter-track {
    height: 6px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 999px;
    overflow: hidden;
}
.vu-meter-fill {
    width: 0%;
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #eab308 70%, #ef4444 100%);
    border-radius: 999px;
    transition: width 0.08s ease-out;
}

/* Monitor Buttons */
.monitor-controls-row {
    display: flex;
    gap: 10px;
    margin-bottom: 18px;
}
.monitor-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #ffffff;
    padding: 10px;
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}
.monitor-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
}
.monitor-btn.off {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

/* Readiness Checklist */
.readiness-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 14px;
}
.readiness-title {
    font-size: 0.82rem;
    font-weight: 800;
    color: #f1f5f9;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.readiness-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.readiness-item:last-child {
    margin-bottom: 0;
}
.chk-status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.chk-status.ready {
    background: #10b981;
    box-shadow: 0 0 6px #10b981;
}
.chk-status.checking {
    background: #eab308;
    box-shadow: 0 0 6px #eab308;
    animation: liveBadgePulse 1s infinite;
}
.chk-status.error {
    background: #ef4444;
    box-shadow: 0 0 6px #ef4444;
}
.chk-text {
    display: flex;
    flex-direction: column;
}
.chk-text strong {
    font-size: 0.78rem;
    color: #e2e8f0;
}
.chk-text span {
    font-size: 0.7rem;
    color: #94a3b8;
}

/* Pro Tips */
.pro-tips-card {
    background: rgba(59, 130, 246, 0.06);
    border: 1px solid rgba(59, 130, 246, 0.15);
    border-radius: 14px;
    padding: 12px 14px;
    font-size: 0.78rem;
    color: #93c5fd;
    line-height: 1.5;
}
.pro-tips-title {
    font-weight: 800;
    color: #bfdbfe;
    margin-bottom: 6px;
}
.pro-tips-card ul {
    margin: 0;
    padding-left: 16px;
}
.pro-tips-card li {
    margin-bottom: 4px;
}

/* Responsive */
@media (max-width: 992px) {
    .studio-grid {
        grid-template-columns: 1fr;
    }
    .monitor-console {
        position: static;
    }
    .category-radio-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 640px) {
    .studio-card {
        padding: 20px 16px;
    }
    .studio-title {
        font-size: 1.5rem;
    }
    .studio-submit-bar {
        flex-direction: column-reverse;
    }
    .btn-studio-back, .btn-launch-broadcast {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endsection
