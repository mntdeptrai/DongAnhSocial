@extends('layouts.app')

@section('title', 'Studio Phát Trực Tiếp - ' . $stream->title)

@section('content')
<div class="live-studio-container">
    <div class="studio-layout">
        <!-- Main Video Studio (Left / Center) -->
        <div class="studio-video-pane">
            <div class="studio-screen-wrapper">
                <!-- Video Element -->
                <video id="host-preview-video" autoplay playsinline muted class="host-main-video"></video>

                <!-- Floating Reactions Layer -->
                <div id="host-reaction-layer" class="floating-reaction-layer"></div>

                <!-- Studio Top Bar -->
                <div class="studio-top-bar">
                    <div class="top-bar-left">
                        <div class="studio-live-badge">
                            <span class="live-blink-dot"></span> LIVE
                        </div>
                        <div class="studio-timer" id="host-stream-timer">00:00</div>
                        <div class="studio-viewers">
                            👥 <span id="host-viewer-count">{{ max(1, $stream->viewer_count) }}</span>
                        </div>
                        <div class="studio-likes">
                            ❤️ <span id="host-likes-count">{{ number_format($stream->likes_count) }}</span>
                        </div>
                    </div>
                    <div class="top-bar-right">
                        <button type="button" class="btn-studio-end" onclick="DongAnhLiveHost.endStream()">
                            🔴 Kết thúc Live
                        </button>
                    </div>
                </div>

                <!-- Pinned Product Overlay Banner -->
                <div id="host-pinned-product-container" class="studio-pinned-product" style="{{ $stream->pinnedProduct ? 'display:flex;' : 'display:none;' }}">
                    @if($stream->pinnedProduct)
                        <img src="{{ $stream->pinnedProduct->image_url ? asset($stream->pinnedProduct->image_url) : '/images/ocop-placeholder.png' }}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="pin-thumb" alt="{{ $stream->pinnedProduct->name }}">
                        <div class="pin-info">
                            <span class="pin-badge">🏷️ Đang giới thiệu</span>
                            <div class="pin-title">{{ $stream->pinnedProduct->name }}</div>
                            <div class="pin-price">{{ $stream->pinnedProduct->price ? number_format($stream->pinnedProduct->price) . 'đ' : 'OCOP' }}</div>
                        </div>
                        <button type="button" class="pin-unpin-btn" onclick="DongAnhLiveHost.pinProduct(null)">✕ Bỏ ghim</button>
                    @endif
                </div>

                <!-- Studio Control Bar -->
                <div class="studio-controls-bar">
                    <button type="button" class="studio-ctrl-btn" id="btn-host-mic" onclick="DongAnhLiveHost.toggleMic()">
                        🎙️ <span class="btn-text">Tắt Mic</span>
                    </button>
                    <button type="button" class="studio-ctrl-btn" id="btn-host-camera" onclick="DongAnhLiveHost.toggleCamera()">
                        📹 <span class="btn-text">Tắt Cam</span>
                    </button>
                    <button type="button" class="studio-ctrl-btn" id="btn-host-switch-cam" onclick="DongAnhLiveHost.switchCamera()">
                        🔄 <span class="btn-text">Đổi Cam</span>
                    </button>
                    <button type="button" class="studio-ctrl-btn" id="btn-host-screen" onclick="DongAnhLiveHost.toggleScreenShare()">
                        🖥️ <span class="btn-text">Share Màn hình</span>
                    </button>
                    <button type="button" class="studio-ctrl-btn btn-cart-trigger" onclick="openCartModal()" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-color: #f59e0b; color: #ffffff;">
                        🛍️ <span class="btn-text">Giỏ hàng (<span id="host-cart-count">{{ $streamProducts->count() }}</span>)</span>
                    </button>
                </div>
            </div>

            <!-- Stream Info Box -->
            <div class="studio-info-card">
                <div class="studio-title-row">
                    <h2 class="studio-stream-title">{{ $stream->title }}</h2>
                    <span class="studio-category-badge">
                        @if($stream->category === 'ocop') 🌾 Nông sản OCOP
                        @elseif($stream->category === 'food') 🍜 Ẩm thực Đông Anh
                        @elseif($stream->category === 'travel') 🗺️ Du lịch & Check-in
                        @elseif($stream->category === 'culture') 🏺 Văn hóa Di sản
                        @else 💬 Giao lưu Cộng đồng
                        @endif
                    </span>
                </div>
                @if($stream->description)
                    <p class="studio-description">{{ $stream->description }}</p>
                @endif
                <div class="studio-share-box">
                    <span class="share-label">🔗 Link phòng live:</span>
                    <input type="text" readonly value="{{ route('livestream.show', $stream->code_or_id) }}" id="live-share-url" class="share-input">
                    <button type="button" class="btn-copy-link" onclick="copyShareUrl()">Sao chép</button>
                </div>
            </div>
        </div>

        <!-- Live Chat Pane (Right) -->
        <div class="studio-chat-pane">
            <div class="chat-pane-header">
                <div class="chat-header-title">
                    <span>💬 Trò chuyện trực tiếp</span>
                    <span class="chat-status-dot"></span>
                </div>
            </div>

            <div class="chat-messages-scroll" id="host-chat-messages">
                <div class="chat-welcome-box">
                    <span>🎉 Chào mừng bạn đến với Studio Livestream Đông Anh! Khán giả đang xem sẽ bình luận tại đây.</span>
                </div>
                @foreach($stream->comments->reverse() as $cmt)
                    <div class="live-chat-item {{ $cmt->user_id === $stream->user_id ? 'host-comment' : '' }}">
                        <img src="{{ $cmt->user->avatar_url ?: ('https://ui-avatars.com/api/?name=' . urlencode($cmt->user->name) . '&background=0ea5e9&color=fff') }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($cmt->user->name) }}&background=0ea5e9&color=fff';" alt="{{ $cmt->user->name }}" class="chat-avatar">
                        <div class="chat-content">
                            <span class="chat-username">{{ $cmt->user->name }}</span>
                            <span class="chat-text">{{ $cmt->message }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="chat-input-area">
                <form onsubmit="event.preventDefault(); submitHostComment();" class="chat-form">
                    <input type="text" id="host-comment-input" placeholder="Nhập bình luận của Streamer..." class="chat-input" autocomplete="off">
                    <button type="submit" class="btn-send-chat">Gửi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Quản Lý Giỏ Hàng Livestream -->
<div id="cart-product-modal" class="pin-modal-overlay" style="display: none;">
    <div class="pin-modal-card cart-modal-card">
        <div class="pin-modal-header">
            <h3>🛍️ Quản Lý Giỏ Hàng Livestream</h3>
            <button type="button" class="modal-close-btn" onclick="closeCartModal()">✕</button>
        </div>

        <!-- Modal Tabs -->
        <div class="cart-modal-tabs">
            <button type="button" class="cart-tab-btn active" id="tab-btn-stream-prods" onclick="switchHostCartTab('stream')">
                🛒 Sản phẩm trong Live (<span id="modal-cart-count">{{ $streamProducts->count() }}</span>)
            </button>
            <button type="button" class="cart-tab-btn" id="tab-btn-all-prods" onclick="switchHostCartTab('all')">
                ➕ Thêm từ Kho OCOP
            </button>
        </div>

        <!-- Tab 1: Products currently attached to this live -->
        <div id="tab-content-stream-prods" class="cart-tab-content active">
            <div class="pin-products-list" id="host-stream-products-list">
                @forelse($streamProducts as $prod)
                    @php $isPinned = ((int)$stream->pinned_product_id === (int)$prod->id); @endphp
                    <div class="pin-product-item {{ $isPinned ? 'selected' : '' }}" id="host-prod-row-{{ $prod->id }}">
                        <img src="{{ $prod->image_url ? (str_starts_with($prod->image_url, 'http') ? $prod->image_url : asset($prod->image_url)) : '/images/ocop-placeholder.png' }}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="pin-item-img" alt="{{ $prod->name }}">
                        <div class="pin-item-info">
                            <div class="pin-item-name">{{ $prod->name }}</div>
                            <div class="pin-item-price">{{ $prod->price ? number_format($prod->price) . 'đ' : 'Đặc sản OCOP' }}</div>
                            @if($isPinned)
                                <span class="badge-currently-pinned">🔥 Đang ghim trên màn hình</span>
                            @endif
                        </div>
                        <div class="pin-item-actions">
                            @if($isPinned)
                                <button type="button" class="btn-pin-action unpin" onclick="DongAnhLiveHost.pinProduct(null)">✕ Bỏ ghim</button>
                            @else
                                <button type="button" class="btn-pin-action pin" onclick="DongAnhLiveHost.pinProduct({{ $prod->id }})">📌 Ghim lên live</button>
                            @endif
                            <button type="button" class="btn-pin-action remove" onclick="DongAnhLiveHost.removeProduct({{ $prod->id }})" title="Xóa khỏi giỏ live">🗑️</button>
                        </div>
                    </div>
                @empty
                    <div class="empty-cart-state" id="host-empty-cart-msg">
                        <span>🛍️</span>
                        <p>Chưa có sản phẩm nào trong giỏ hàng phiên live này.</p>
                        <button type="button" class="btn-switch-add" onclick="switchHostCartTab('all')">+ Thêm sản phẩm từ Kho</button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Tab 2: All OCOP products to add to cart -->
        <div id="tab-content-all-prods" class="cart-tab-content" style="display: none;">
            <div class="pin-modal-search">
                <input type="text" id="host-all-prod-search" placeholder="🔍 Tìm tên sản phẩm OCOP để thêm vào live..." oninput="filterHostAllProducts(this.value)" class="modal-search-field">
            </div>
            <div class="pin-products-list" id="host-all-products-list">
                @foreach($ocopProducts as $prod)
                    @php $alreadyInLive = $streamProducts->contains('id', $prod->id); @endphp
                    <div class="pin-product-item {{ $alreadyInLive ? 'in-live' : '' }}" data-name="{{ Str::lower($prod->name) }}" id="all-prod-row-{{ $prod->id }}">
                        <img src="{{ $prod->image_url ? (str_starts_with($prod->image_url, 'http') ? $prod->image_url : asset($prod->image_url)) : '/images/ocop-placeholder.png' }}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="pin-item-img" alt="{{ $prod->name }}">
                        <div class="pin-item-info">
                            <div class="pin-item-name">{{ $prod->name }}</div>
                            <div class="pin-item-price">{{ $prod->price ? number_format($prod->price) . 'đ' : 'Đặc sản OCOP' }}</div>
                        </div>

                        <div class="pin-item-actions">
                            @if($alreadyInLive)
                                <button type="button" class="btn-pin-action added" disabled>✓ Đã có trong live</button>
                            @else
                                <button type="button" class="btn-pin-action add-to-live" onclick="DongAnhLiveHost.addProduct({{ $prod->id }})">➕ Gắn vào Live</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>


<!-- Pusher & Reverb WebSocket Library -->
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script>
    if (typeof window.Echo === 'undefined') {
        window.Echo = new (class {
            constructor() {
                const isHttps = window.location.protocol === 'https:';
                const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
                const wsHost = isLocal ? '127.0.0.1' : window.location.hostname;
                const wsPort = isLocal ? 8080 : (isHttps ? 443 : 80);
                const forceTLS = isLocal ? false : isHttps;
                const reverbKey = '{{ config('broadcasting.connections.reverb.key') ?: env('REVERB_APP_KEY', 'donganhreverbkey') }}';

                console.log('[LiveHost] Connecting to Reverb:', wsHost, wsPort, 'TLS:', forceTLS, 'Key:', reverbKey);

                this._pusher = new Pusher(reverbKey, {
                    wsHost:           wsHost,
                    wsPort:           wsPort,
                    wssPort:          wsPort,
                    forceTLS:         forceTLS,
                    enabledTransports: ['ws', 'wss'],
                    cluster:          'mt1',
                    disableStats:     true,
                    authEndpoint:     '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    }
                });

                this._pusher.connection.bind('connected', () => {
                    console.log('[LiveHost] Reverb WebSocket connected successfully.');
                });
                this._pusher.connection.bind('error', (err) => {
                    console.warn('[LiveHost] Reverb WebSocket connection error:', err);
                });

                this.connector = { pusher: this._pusher };
            }
            channel(name) {
                const ch = this._pusher.subscribe(name);
                ch.bind_global((eventName, data) => {
                    console.log('[LiveHost WS Event]', name, eventName, data);
                });
                const obj = {
                    listen: (event, cb) => {
                        const evtName = event.startsWith('.') ? event.slice(1) : event;
                        ch.bind(evtName, cb);
                        ch.bind('App\\Events\\' + evtName, cb);
                        return obj;
                    }
                };
                return obj;
            }
            private(name) {
                const ch = this._pusher.subscribe('private-' + name);
                const obj = {
                    listen: (event, cb) => {
                        const evtName = event.startsWith('.') ? event.slice(1) : event;
                        ch.bind(evtName, cb);
                        ch.bind('App\\Events\\' + evtName, cb);
                        return obj;
                    }
                };
                return obj;
            }
        })();
    }
</script>

<script src="{{ asset('js/livestream-host.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    DongAnhLiveHost.init({
        streamId: '{{ $stream->code_or_id }}',
        channelId: {{ $stream->id }}
    });
});

function submitHostComment() {
    const input = document.getElementById('host-comment-input');
    if (input && input.value.trim()) {
        DongAnhLiveHost.sendComment(input.value.trim());
    }
}

function openCartModal() {
    document.getElementById('cart-product-modal').style.display = 'flex';
}
function closeCartModal() {
    document.getElementById('cart-product-modal').style.display = 'none';
}
function switchHostCartTab(tab) {
    const btnStream = document.getElementById('tab-btn-stream-prods');
    const btnAll = document.getElementById('tab-btn-all-prods');
    const contentStream = document.getElementById('tab-content-stream-prods');
    const contentAll = document.getElementById('tab-content-all-prods');

    if (tab === 'stream') {
        btnStream.classList.add('active');
        btnAll.classList.remove('active');
        contentStream.style.display = 'block';
        contentAll.style.display = 'none';
    } else {
        btnStream.classList.remove('active');
        btnAll.classList.add('active');
        contentStream.style.display = 'none';
        contentAll.style.display = 'block';
    }
}
function filterHostAllProducts(query) {
    const q = (query || '').toLowerCase().trim();
    const items = document.querySelectorAll('#host-all-products-list .pin-product-item[data-name]');
    items.forEach(item => {
        const name = item.getAttribute('data-name') || '';
        item.style.display = (!q || name.includes(q)) ? 'flex' : 'none';
    });
}

function copyShareUrl() {
    const input = document.getElementById('live-share-url');
    if (input) {
        input.select();
        document.execCommand('copy');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Đã sao chép link!',
                timer: 1200,
                showConfirmButton: false
            });
        }
    }
}
</script>

<style>
.live-studio-container {
    max-width: 1320px;
    margin: 16px auto 48px auto;
    padding: 0 16px;
    font-family: 'Plus Jakarta Sans', 'Be Vietnam Pro', sans-serif;
}

.studio-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 20px;
    align-items: start;
}

/* Video Studio Pane */
.studio-video-pane {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.studio-screen-wrapper {
    position: relative;
    aspect-ratio: 16 / 9;
    background: #020617;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4);
    border: 1px solid #1e293b;
}

.host-main-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scaleX(-1);
}

/* Floating Reactions Layer */
.floating-reaction-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 15;
}
.floating-particle {
    position: absolute;
    bottom: 20px;
    font-size: 2.2rem;
    animation: floatUp 3s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    opacity: 0.95;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));
}
@keyframes floatUp {
    0% { transform: translateY(0) scale(0.6) rotate(0deg); opacity: 0; }
    15% { opacity: 1; transform: translateY(-30px) scale(1.1) rotate(-8deg); }
    50% { transform: translateY(-160px) scale(1) rotate(12deg); }
    100% { transform: translateY(-380px) scale(0.8) rotate(-15deg); opacity: 0; }
}

/* Top Bar */
.studio-top-bar {
    position: absolute;
    top: 16px;
    left: 16px;
    right: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 20;
}
.top-bar-left {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    padding: 6px 14px;
    border-radius: 99px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #ffffff;
    font-size: 0.86rem;
    font-weight: 600;
}
.studio-live-badge {
    background: #ef4444;
    color: #fff;
    font-size: 0.74rem;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.live-blink-dot {
    width: 6px;
    height: 6px;
    background: #fff;
    border-radius: 50%;
    animation: live-blink 1s infinite;
}
.studio-timer {
    color: #38bdf8;
    font-family: monospace;
    font-weight: 700;
    font-size: 0.95rem;
}
.btn-studio-end {
    background: #ef4444;
    color: #ffffff;
    border: none;
    padding: 8px 18px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    transition: all 0.2s;
}
.btn-studio-end:hover {
    background: #dc2626;
}

/* Pinned Product Overlay */
.studio-pinned-product {
    position: absolute;
    bottom: 84px;
    left: 16px;
    background: rgba(15, 23, 42, 0.9);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(254, 240, 138, 0.3);
    border-radius: 16px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 380px;
    z-index: 20;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
}
.pin-thumb {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    object-fit: cover;
}
.pin-info {
    flex: 1;
    min-width: 0;
}
.pin-badge {
    font-size: 0.7rem;
    font-weight: 700;
    color: #fef08a;
    display: block;
}
.pin-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pin-price {
    font-size: 0.82rem;
    font-weight: 700;
    color: #4ade80;
}
.pin-unpin-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #cbd5e1;
    font-size: 0.75rem;
    padding: 5px 8px;
    border-radius: 8px;
    cursor: pointer;
}
.pin-unpin-btn:hover {
    background: rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

/* Studio Controls Bar */
.studio-controls-bar {
    position: absolute;
    bottom: 16px;
    left: 16px;
    right: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    padding: 10px 16px;
    z-index: 20;
}
.studio-ctrl-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}
.studio-ctrl-btn:hover {
    background: rgba(255, 255, 255, 0.18);
}
.studio-ctrl-btn.active-muted {
    background: rgba(239, 68, 68, 0.25);
    border-color: #ef4444;
    color: #fca5a5;
}
.studio-ctrl-btn.active-active {
    background: rgba(16, 185, 129, 0.25);
    border-color: #10b981;
    color: #86efac;
}
.btn-pin-trigger {
    background: rgba(234, 179, 8, 0.2);
    border-color: rgba(234, 179, 8, 0.4);
    color: #fef08a;
}

/* Studio Info Card */
.studio-info-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}
.studio-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
}
.studio-stream-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.studio-category-badge {
    background: #f1f5f9;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 99px;
}
.studio-description {
    color: #64748b;
    font-size: 0.92rem;
    line-height: 1.6;
    margin-bottom: 16px;
}
.studio-share-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px 12px;
}
.share-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #64748b;
    white-space: nowrap;
}
.share-input {
    flex: 1;
    background: transparent;
    border: none;
    font-size: 0.84rem;
    color: #0f172a;
    outline: none;
}
.btn-copy-link {
    background: #0ea5e9;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
}

/* Chat Pane */
.studio-chat-pane {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    height: 680px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.chat-pane-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.chat-header-title {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chat-status-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
}
.chat-messages-scroll {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.chat-welcome-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    font-size: 0.82rem;
    color: #64748b;
    line-height: 1.5;
    text-align: center;
}
.live-chat-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 0.88rem;
}
.chat-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.chat-content {
    background: #f1f5f9;
    border-radius: 14px;
    padding: 8px 12px;
    max-width: 80%;
}
.chat-username {
    display: block;
    font-size: 0.76rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 2px;
}
.chat-text {
    color: #0f172a;
    word-break: break-word;
}
.live-chat-item.host-comment .chat-content {
    background: #e0f2fe;
    border: 1px solid #bae6fd;
}
.live-chat-item.host-comment .chat-username {
    color: #0284c7;
}

.chat-input-area {
    padding: 12px 16px;
    border-top: 1px solid #f1f5f9;
    background: #ffffff;
}
.chat-form {
    display: flex;
    gap: 8px;
}
.chat-input {
    flex: 1;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    font-size: 0.9rem;
    outline: none;
}
.chat-input:focus {
    border-color: #0ea5e9;
}
.btn-send-chat {
    background: #0ea5e9;
    color: #fff;
    border: none;
    padding: 0 16px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
}

/* Pin Modal */
.pin-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(8px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.pin-modal-card {
    background: #ffffff;
    border-radius: 24px;
    width: 480px;
    max-width: 100%;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
.pin-modal-header {
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
}
.pin-modal-header h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
}
.modal-close-btn {
    background: #f1f5f9;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 0.9rem;
    cursor: pointer;
}
.pin-modal-search {
    padding: 12px 24px;
    border-bottom: 1px solid #f1f5f9;
}
.modal-search-field {
    width: 100%;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    font-size: 0.9rem;
    outline: none;
    box-sizing: border-box;
}
.pin-products-list {
    flex: 1;
    overflow-y: auto;
    padding: 12px 24px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.pin-product-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.2s;
}
.pin-product-item:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.pin-product-item.selected {
    background: #eff6ff;
    border-color: #3b82f6;
}
.pin-item-icon {
    font-size: 1.5rem;
}
.pin-item-img {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
}
.pin-item-info {
    flex: 1;
}
.pin-item-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
}
.pin-item-price {
    font-size: 0.82rem;
    font-weight: 600;
    color: #059669;
}
.cart-modal-card {
    width: 540px;
}
.cart-modal-tabs {
    display: flex;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.cart-tab-btn {
    flex: 1;
    padding: 12px 16px;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    font-weight: 700;
    font-size: 0.88rem;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}
.cart-tab-btn.active {
    color: #ef4444;
    border-bottom-color: #ef4444;
    background: #ffffff;
}
.pin-item-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-pin-action {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-pin-action.pin {
    background: #dbeafe;
    color: #1d4ed8;
}
.btn-pin-action.pin:hover {
    background: #bfdbfe;
}
.btn-pin-action.unpin {
    background: #fee2e2;
    color: #b91c1c;
}
.btn-pin-action.add-to-live {
    background: #dcfce7;
    color: #15803d;
}
.btn-pin-action.add-to-live:hover {
    background: #bbf7d0;
}
.btn-pin-action.added {
    background: #f1f5f9;
    color: #94a3b8;
    cursor: default;
}
.btn-pin-action.remove {
    background: #f1f5f9;
    color: #ef4444;
    padding: 6px 8px;
}
.btn-pin-action.remove:hover {
    background: #fee2e2;
}
.badge-currently-pinned {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    color: #ef4444;
    background: #fef2f2;
    padding: 2px 6px;
    border-radius: 4px;
    margin-top: 2px;
}
.empty-cart-state {
    text-align: center;
    padding: 36px 16px;
    color: #64748b;
}
.empty-cart-state span {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 8px;
}
.empty-cart-state p {
    font-size: 0.9rem;
    margin-bottom: 14px;
}
.btn-switch-add {
    background: #ef4444;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
}

@media (max-width: 980px) {
    .studio-layout {
        grid-template-columns: 1fr;
    }
    .studio-chat-pane {
        height: 480px;
    }
    .studio-ctrl-btn .btn-text {
        display: none;
    }
    .studio-ctrl-btn {
        padding: 8px 12px;
    }
}

</style>
@endsection
