@extends('layouts.app')

@section('title', 'Đang Xem Trực Tiếp: ' . $stream->title)
@section('meta_description', $stream->description ?? ('Xem livestream trực tiếp ' . $stream->title . ' trên DongAnh Discovery'))

@section('content')
<div class="live-viewer-container {{ $stream->status === 'ended' ? 'is-ended' : '' }}">
    <div class="viewer-layout">
        <!-- Main Video & Interactions Column (Left) -->
        <div class="viewer-video-pane">
            <div class="viewer-screen-wrapper">
                
                @if($stream->status === 'ended' && ($stream->youtube_video_id || $stream->recording_url))
                    <!-- Replay Player Mode -->
                    <div class="viewer-replay-box">
                        @if($stream->youtube_video_id)
                            <iframe src="https://www.youtube.com/embed/{{ $stream->youtube_video_id }}?autoplay=1&rel=0&playsinline=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="viewer-replay-frame"></iframe>
                        @else
                            <video src="{{ $stream->recording_url }}" controls autoplay playsinline class="viewer-replay-video"></video>
                        @endif
                    </div>
                @else
                    <!-- Live WebRTC Video Player (muted for instant browser autoplay) -->
                    <video id="viewer-video-player" autoplay playsinline muted class="viewer-main-video"></video>

                    <!-- Connecting Spinner Overlay -->
                    <div id="viewer-connecting-spinner" class="viewer-connecting-overlay">
                        <div class="spinner-ring"></div>
                        <span>Đang kết nối luồng phát trực tiếp...</span>
                    </div>

                    <!-- Unblock Audio / Autoplay Button -->
                    <button type="button" id="viewer-unblock-audio-btn" class="viewer-unmute-btn" onclick="DongAnhLiveViewer.unmuteAndPlay()" style="display: none;">
                        🔊 Nhấn để bật âm thanh Live
                    </button>
                @endif

                <!-- Floating Reactions Layer (TikTok Flying Hearts) -->
                <div id="viewer-reaction-layer" class="floating-reaction-layer"></div>

                <!-- Stream Ended Overlay (Only if no replay available) -->
                <div id="viewer-ended-overlay" class="viewer-ended-overlay" style="{{ ($stream->status === 'ended' && !$stream->youtube_video_id && !$stream->recording_url) ? 'display:flex;' : 'display:none;' }}">
                    <div class="ended-card">
                        <div class="ended-icon">🎬</div>
                        <h3>Phiên Livestream đã kết thúc</h3>
                        <p>Cảm ơn bạn đã đồng hành và tương tác cùng người phát sóng!</p>
                        <div class="ended-stats">
                            <div>⏱️ Thời lượng: <b id="ended-duration">{{ $stream->duration ?: '00:00' }}</b></div>
                            <div>❤️ Lượt thích: <b id="ended-likes">{{ number_format($stream->likes_count) }}</b></div>
                        </div>
                        <a href="{{ route('livestream.index') }}" class="btn-back-hub">
                            Xem các phòng Live khác
                        </a>
                    </div>
                </div>

                <!-- Top Status Bar (TikTok Style on Mobile / Glass on Desktop) -->
                <div class="viewer-top-bar">
                    <div class="viewer-streamer-info">
                        <img src="{{ $stream->user->avatar_url ?: ('https://ui-avatars.com/api/?name=' . urlencode($stream->user->name) . '&background=0ea5e9&color=fff') }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($stream->user->name) }}&background=0ea5e9&color=fff';" alt="{{ $stream->user->name }}" class="streamer-avatar">
                        <div class="streamer-text">
                            <div class="streamer-name">{{ $stream->user->name }}</div>
                            <div class="streamer-status">
                                <span class="badge-live-mini">{{ $stream->status === 'ended' ? 'ĐÃ PHÁT' : 'LIVE' }}</span>
                                <span class="streamer-viewers-tag">👥 <span id="viewer-online-count">{{ max(1, $stream->viewer_count) }}</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="viewer-top-actions">
                        <!-- Desktop Top Cart Button -->
                        <button type="button" class="btn-live-cart-top" onclick="openViewerCart()" id="btn-viewer-cart-top">
                            🛍️ Túi Hàng (<span id="viewer-cart-top-count">{{ $stream->products->count() }}</span>)
                        </button>
                        <!-- Desktop Share Button -->
                        <button type="button" class="btn-share-stream" onclick="copyStreamUrl()">
                            ↗ Chia sẻ
                        </button>
                        <!-- Mobile Close/Exit Live Button -->
                        <a href="{{ route('livestream.index') }}" onclick="window.location.href='{{ route('livestream.index') }}'" ontouchend="window.location.href='{{ route('livestream.index') }}'" class="btn-close-live-mobile" title="Rời phòng Live">✕</a>
                    </div>
                </div>

                @if($stream->status !== 'ended' || $stream->youtube_video_id || $stream->recording_url)
                    <!-- Pinned Product Overlay Banner (TikTok Deal Capsule) -->
                    <div id="viewer-pinned-product-banner" class="viewer-pinned-product" style="{{ $stream->pinnedProduct ? 'display:flex;' : 'display:none;' }}">
                        @if($stream->pinnedProduct)
                            <div class="pin-thumb-wrapper">
                                <img src="{{ $stream->pinnedProduct->image_url ? (str_starts_with($stream->pinnedProduct->image_url, 'http') ? $stream->pinnedProduct->image_url : asset($stream->pinnedProduct->image_url)) : '/images/ocop-placeholder.png' }}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="pin-thumb" alt="{{ $stream->pinnedProduct->name }}">
                                <span class="pin-num-tag">1</span>
                            </div>
                            <div class="pin-info">
                                <div class="pin-badge-row">
                                    <span class="pin-badge">🔥 HOT</span>
                                    <span class="pin-title">{{ $stream->pinnedProduct->name }}</span>
                                </div>
                                <div class="pin-price-row">
                                    <span class="pin-price">{{ $stream->pinnedProduct->price ? number_format($stream->pinnedProduct->price) . 'đ' : 'OCOP' }}</span>
                                    @if($stream->pinnedProduct->star_rating)
                                        <span class="pin-star">⭐ {{ $stream->pinnedProduct->star_rating }}</span>
                                    @endif
                                </div>
                            </div>
                            <button type="button" onclick="openProductQuickView({{ $stream->pinnedProduct->id }}, event)" class="pin-buy-btn">
                                Mua
                            </button>
                        @endif
                    </div>

                    <!-- Desktop Floating Reaction Bar -->
                    <div class="viewer-reaction-bar">
                        <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('heart')" title="Thả tim">❤️</button>
                        <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('fire')" title="Tuyệt vời">🔥</button>
                        <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('clap')" title="Vỗ tay">👏</button>
                        <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('wow')" title="Yêu thích">😍</button>
                        <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('star')" title="Tặng sao">⭐</button>
                    </div>

                    <!-- Mobile TikTok Bottom Action Bar -->
                    <div class="mobile-tiktok-action-bar">
                        <button type="button" class="btn-tiktok-bag" onclick="openViewerCart()" title="Túi hàng livestream">
                            <span class="tiktok-bag-icon">🛍️</span>
                            <span class="tiktok-bag-badge" id="viewer-cart-mobile-count">{{ $stream->products->count() }}</span>
                        </button>

                        <form onsubmit="event.preventDefault(); submitViewerComment();" class="tiktok-chat-form">
                            <input type="text" id="viewer-comment-input-mobile" placeholder="{{ Auth::check() || session('user_id') ? 'Thêm bình luận...' : 'Đăng nhập để chat...' }}" class="tiktok-chat-input" autocomplete="off">
                            <button type="submit" class="btn-tiktok-send-icon" title="Gửi">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </button>
                        </form>

                        <div class="tiktok-reactions-group">
                            <button type="button" class="tiktok-reaction-btn tiktok-heart-btn" onclick="DongAnhLiveViewer.sendReaction('heart')" title="Thả tim">
                                ❤️
                            </button>
                            <button type="button" class="tiktok-reaction-btn" onclick="DongAnhLiveViewer.sendReaction('fire')" title="Cảm xúc lửa">
                                🔥
                            </button>
                            <button type="button" class="tiktok-reaction-btn" onclick="copyStreamUrl()" title="Chia sẻ">
                                ↗️
                            </button>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Stream Details Card (Desktop) -->
            <div class="viewer-details-card">
                <div class="details-main-header">
                    <h1 class="details-title">{{ $stream->title }}</h1>
                    <div class="details-likes-badge">
                        ❤️ <span id="viewer-likes-count">{{ number_format($stream->likes_count) }}</span> lượt thích
                    </div>
                </div>
                @if($stream->description)
                    <p class="details-description">{{ $stream->description }}</p>
                @endif
                <div class="details-meta-row">
                    <span class="category-tag">
                        @if($stream->category === 'ocop') 🌾 Nông sản OCOP
                        @elseif($stream->category === 'food') 🍜 Ẩm thực Đông Anh
                        @elseif($stream->category === 'travel') 🗺️ Du lịch & Check-in
                        @elseif($stream->category === 'culture') 🏺 Văn hóa Di sản
                        @else 💬 Giao lưu Cộng đồng
                        @endif
                    </span>
                    <span class="time-tag">Bắt đầu từ: {{ $stream->created_at->format('H:i, d/m/Y') }}</span>
                    @if($stream->youtube_video_id)
                        <span class="youtube-tag">📺 Lưu trữ Kênh YouTube</span>
                    @endif
                </div>
            </div>

            <!-- Related Streams (Desktop) -->
            @if($relatedStreams->count() > 0)
                <div class="related-streams-section">
                    <h3 class="related-title">Các phòng Live khác đang phát</h3>
                    <div class="related-grid">
                        @foreach($relatedStreams as $rel)
                            <a href="{{ route('livestream.show', $rel->code_or_id) }}" class="related-card">
                                <div class="related-thumb">
                                    <img src="{{ $rel->cover_image ? (str_starts_with($rel->cover_image, 'http') ? $rel->cover_image : asset($rel->cover_image)) : 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=400&q=80' }}" alt="{{ $rel->title }}">
                                    <span class="related-badge-live">LIVE</span>
                                </div>
                                <div class="related-info">
                                    <div class="related-name">{{ $rel->title }}</div>
                                    <div class="related-author">{{ $rel->user->name }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Live Chat Pane (Right Column on Desktop, Floating Overlay on Mobile) -->
        <div class="viewer-chat-pane">
            <div class="chat-pane-header">
                <div class="chat-header-title">
                    <span>💬 Trò chuyện trực tiếp</span>
                    <span class="chat-status-dot"></span>
                </div>
            </div>

            <div class="chat-messages-scroll" id="viewer-chat-messages">
                <div class="chat-welcome-box">
                    <span>Chào mừng bạn đến với phiên phát trực tiếp! Hãy gửi lời chào và bình luận lịch sự cùng mọi người nhé 👋</span>
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
                <form onsubmit="event.preventDefault(); submitViewerComment();" class="chat-form">
                    <input type="text" id="viewer-comment-input" placeholder="{{ Auth::check() || session('user_id') ? 'Bình luận điều gì đó...' : 'Đăng nhập để tham gia trò chuyện...' }}" class="chat-input" autocomplete="off">
                    <button type="submit" class="btn-send-chat">Gửi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Giỏ Hàng Khán Giả (Fixed Fullscreen Overlay) -->
<div id="viewer-cart-modal" class="live-modal-backdrop" onclick="if(event.target === this) closeViewerCart()">
    <div class="live-modal-dialog">
        <div class="live-modal-header">
            <div class="modal-title-with-icon">
                <span class="modal-icon-lg">🛍️</span>
                <div>
                    <h3 class="live-modal-title">Túi Hàng Trực Tiếp</h3>
                    <span class="modal-sub-text"><span id="viewer-cart-total-count">{{ $stream->products->count() }}</span> sản phẩm OCOP & Đặc sản Đông Anh</span>
                </div>
            </div>
            <button type="button" class="live-modal-close" onclick="closeViewerCart()">✕</button>
        </div>

        <div class="viewer-cart-list" id="viewer-cart-items-list">
            @forelse($stream->products as $idx => $prod)
                @php $isPinned = ((int)$stream->pinned_product_id === (int)$prod->id); @endphp
                <div class="viewer-cart-item {{ $isPinned ? 'is-pinned-spotlight' : '' }}" id="viewer-cart-row-{{ $prod->id }}">
                    <div class="cart-item-num">#{{ $idx + 1 }}</div>
                    <img src="{{ $prod->image_url ? (str_starts_with($prod->image_url, 'http') ? $prod->image_url : asset($prod->image_url)) : '/images/ocop-placeholder.png' }}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="cart-item-img" alt="{{ $prod->name }}">
                    <div class="cart-item-info">
                        @if($isPinned)
                            <span class="badge-spotlight">🔥 Đang giới thiệu</span>
                        @endif
                        <div class="cart-item-name">{{ $prod->name }}</div>
                        <div class="cart-item-pricing">
                            <span class="cart-item-price">{{ $prod->price ? number_format($prod->price) . 'đ' : 'Liên hệ' }}</span>
                            @if($prod->star_rating)
                                <span class="cart-item-stars">⭐ {{ $prod->star_rating }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <button type="button" onclick="openProductQuickView({{ $prod->id }}, event)" class="btn-cart-buy">
                            🛒 Xem & Mua
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-viewer-cart" id="empty-viewer-cart-msg">
                    <span>🛍️</span>
                    <p>Streamer chưa gắn sản phẩm nào vào giỏ hàng.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Xem & Mua Nhanh Sản Phẩm Trực Tiếp Trên Live (Fixed Fullscreen Overlay) -->
<div id="viewer-product-quickview-modal" class="live-modal-backdrop" onclick="if(event.target === this) closeProductQuickView()">
    <div class="live-modal-dialog dialog-lg">
        <div class="live-modal-header">
            <div class="modal-title-with-icon">
                <span class="modal-icon-lg">🌾</span>
                <div>
                    <h3 class="live-modal-title">Đặc Sản OCOP Đông Anh</h3>
                    <span class="modal-sub-text">Mua sắm trực tiếp ngay trong phiên Livestream</span>
                </div>
            </div>
            <button type="button" class="live-modal-close" onclick="closeProductQuickView()">✕</button>
        </div>

        <div class="quickview-body">
            <div class="quickview-grid">
                <div class="quickview-media">
                    <img id="qv-product-img" src="/images/ocop-placeholder.png" alt="Sản phẩm" class="quickview-main-img" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';">
                    <span id="qv-product-star" class="quickview-star-tag">⭐ 4 sao OCOP</span>
                </div>
                <div class="quickview-info">
                    <h2 id="qv-product-name" class="quickview-name">Tên sản phẩm</h2>
                    <div class="quickview-pricing-row">
                        <span id="qv-product-price" class="quickview-price">0đ</span>
                        <span id="qv-product-unit" class="quickview-unit"></span>
                    </div>

                    <div id="qv-product-desc" class="quickview-desc">
                        Sản phẩm OCOP chất lượng cao được tuyển chọn và kiểm định chuẩn Đông Anh.
                    </div>

                    <!-- Quantity Control -->
                    <div class="quickview-qty-box">
                        <span class="qty-title">Số lượng:</span>
                        <div class="qty-stepper">
                            <button type="button" class="qty-btn-step" onclick="changeQuickViewQty(-1)">−</button>
                            <input type="number" id="qv-qty-input" value="1" min="1" max="99" readonly class="qty-val">
                            <button type="button" class="qty-btn-step" onclick="changeQuickViewQty(1)">+</button>
                        </div>
                        <div class="qty-total-preview">
                            Thành tiền: <b id="qv-total-price">0đ</b>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="quickview-actions">
                        <button type="button" id="qv-btn-add-cart" class="btn-qv-add-cart" onclick="submitQuickViewAddToCart(false)">
                            🛒 Thêm Vào Giỏ Hàng
                        </button>
                        <button type="button" id="qv-btn-buy-now" class="btn-qv-buy-now" onclick="submitQuickViewAddToCart(true)">
                            ⚡ Mua Ngay
                        </button>
                    </div>

                    <div class="quickview-footer">
                        <a id="qv-product-link" href="#" target="_blank" class="qv-detail-link">
                            Xem bài viết chi tiết & nguồn gốc sản phẩm ↗
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $productsMap = [];
    foreach ($stream->products as $p) {
        $productsMap[$p->id] = [
            'id'          => $p->id,
            'name'        => $p->name,
            'price'       => $p->price ? number_format($p->price) . 'đ' : 'Liên hệ',
            'raw_price'   => (float)$p->price,
            'unit'        => $p->unit ? ('/ ' . $p->unit) : '/ sản phẩm',
            'image'       => $p->image_url ? (str_starts_with($p->image_url, 'http') ? $p->image_url : asset($p->image_url)) : '/images/ocop-placeholder.png',
            'image_url'   => $p->image_url ? (str_starts_with($p->image_url, 'http') ? $p->image_url : asset($p->image_url)) : '/images/ocop-placeholder.png',
            'star_rating' => $p->star_rating ?? '4 sao',
            'description' => $p->description ?? 'Sản phẩm OCOP chất lượng cao được tuyển chọn và kiểm định chuẩn Đông Anh.',
            'story'       => $p->story ?? null,
            'detail_url'  => route('ocop.product.show', $p->slug ?: $p->id),
        ];
    }
@endphp
<script>
window.__liveProducts = @json($productsMap);
</script>

<!-- Hls.js Media Streaming Library -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.8/dist/hls.min.js"></script>

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

                console.log('[LiveViewer] Connecting to Reverb:', wsHost, wsPort, 'TLS:', forceTLS, 'Key:', reverbKey);

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
                    console.log('[LiveViewer] Reverb WebSocket connected successfully.');
                });
                this._pusher.connection.bind('error', (err) => {
                    console.warn('[LiveViewer] Reverb WebSocket connection error:', err);
                });

                this.connector = { pusher: this._pusher };
            }
            channel(name) {
                const ch = this._pusher.subscribe(name);
                ch.bind_global((eventName, data) => {
                    console.log('[LiveViewer WS Event]', name, eventName, data);
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

<!-- Livestream Client Engine -->
<script src="{{ asset('js/livestream-viewer.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    DongAnhLiveViewer.init({
        streamId: '{{ $stream->code_or_id }}',
        channelId: {{ $stream->id }},
        hlsUrl: '{{ $stream->hls_stream_url }}',
        userId: {{ Auth::id() ?: (session('user_id') ?: 'null') }}
    });
});

let currentQuickViewProduct = null;
let currentQuickViewQty = 1;

function openViewerCart() {
    const modal = document.getElementById('viewer-cart-modal');
    if (modal) modal.classList.add('is-open');
}
function closeViewerCart() {
    const modal = document.getElementById('viewer-cart-modal');
    if (modal) modal.classList.remove('is-open');
}

function openProductQuickView(productId, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    let product = window.__liveProducts ? window.__liveProducts[productId] : null;
    if (!product) {
        console.warn('Product not found in window.__liveProducts for id:', productId);
        return;
    }

    currentQuickViewProduct = product;
    currentQuickViewQty = 1;

    document.getElementById('qv-product-img').src = product.image || product.image_url || '/images/ocop-placeholder.png';
    document.getElementById('qv-product-name').innerText = product.name;
    document.getElementById('qv-product-price').innerText = product.price;
    document.getElementById('qv-product-unit').innerText = product.unit || '/ sản phẩm';
    document.getElementById('qv-product-star').innerText = '⭐ ' + (product.star_rating || '4 sao') + ' OCOP';
    document.getElementById('qv-product-desc').innerText = product.description || (product.story ? product.story.substring(0, 160) + '...' : 'Sản phẩm OCOP chất lượng cao đạt chuẩn chứng nhận Đông Anh.');
    document.getElementById('qv-product-link').href = product.detail_url || '#';
    document.getElementById('qv-qty-input').value = 1;

    updateQuickViewTotal();

    const modal = document.getElementById('viewer-product-quickview-modal');
    if (modal) modal.classList.add('is-open');
}

function closeProductQuickView() {
    const modal = document.getElementById('viewer-product-quickview-modal');
    if (modal) modal.classList.remove('is-open');
}

function changeQuickViewQty(delta) {
    currentQuickViewQty = Math.max(1, Math.min(99, currentQuickViewQty + delta));
    document.getElementById('qv-qty-input').value = currentQuickViewQty;
    updateQuickViewTotal();
}

function updateQuickViewTotal() {
    if (!currentQuickViewProduct) return;
    const rawPrice = currentQuickViewProduct.raw_price || 0;
    if (rawPrice > 0) {
        const total = rawPrice * currentQuickViewQty;
        document.getElementById('qv-total-price').innerText = total.toLocaleString('vi-VN') + 'đ';
    } else {
        document.getElementById('qv-total-price').innerText = currentQuickViewProduct.price || 'Liên hệ';
    }
}

async function submitQuickViewAddToCart(isBuyNow = false) {
    if (!currentQuickViewProduct) return;

    const btnAdd = document.getElementById('qv-btn-add-cart');
    const btnBuy = document.getElementById('qv-btn-buy-now');
    const activeBtn = isBuyNow ? btnBuy : btnAdd;
    const origText = activeBtn.innerHTML;

    activeBtn.disabled = true;
    activeBtn.innerHTML = '⏳ Đang xử lý...';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ocop_product_id: currentQuickViewProduct.id,
                quantity: currentQuickViewQty
            })
        });

        const data = await res.json();
        activeBtn.disabled = false;
        activeBtn.innerHTML = origText;

        if (data.success || data.status === 'success') {
            if (typeof updateCartBadge === 'function' && data.count !== undefined) {
                updateCartBadge(data.count);
            }

            if (isBuyNow) {
                window.location.href = '/checkout';
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã thêm vào giỏ hàng!',
                        text: `${currentQuickViewProduct.name} (x${currentQuickViewQty})`,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    alert('Đã thêm sản phẩm vào giỏ hàng thành công!');
                }
                closeProductQuickView();
            }
        } else {
            alert(data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
        }
    } catch (err) {
        console.error('Add to cart error:', err);
        activeBtn.disabled = false;
        activeBtn.innerHTML = origText;
        alert('Lỗi kết nối khi thêm vào giỏ hàng.');
    }
}

function submitViewerComment() {
    const input = document.getElementById('viewer-comment-input');
    const inputMobile = document.getElementById('viewer-comment-input-mobile');
    const val = (input && input.value.trim()) || (inputMobile && inputMobile.value.trim());
    if (val) {
        DongAnhLiveViewer.sendComment(val);
        if (input) input.value = '';
        if (inputMobile) inputMobile.value = '';
    }
}

function copyStreamUrl() {
    const url = window.location.href;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(showCopiedToast).catch(() => fallbackCopy(url));
    } else {
        fallbackCopy(url);
    }
}

function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        showCopiedToast();
    } catch (e) {
        prompt('Sao chép liên kết tại đây:', text);
    }
    document.body.removeChild(ta);
}

function showCopiedToast() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Đã sao chép liên kết xem live!',
            timer: 1400,
            showConfirmButton: false
        });
    } else {
        alert('Đã sao chép liên kết xem live thành công!');
    }
}
</script>

<style>
/* ========================================================
   DESKTOP STUDIO VIEWER STYLES
   ======================================================== */
.live-viewer-container {
    max-width: 1320px;
    margin: 16px auto 48px auto;
    padding: 0 16px;
    font-family: 'Plus Jakarta Sans', 'Be Vietnam Pro', sans-serif;
    box-sizing: border-box;
}

.viewer-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    align-items: start;
}

/* Video Player Pane */
.viewer-video-pane {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.viewer-screen-wrapper {
    position: relative;
    aspect-ratio: 16 / 9;
    background: #020617;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4);
    border: 1px solid #1e293b;
}

.viewer-main-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Replay Box */
.viewer-replay-box {
    width: 100%;
    height: 100%;
    background: #000;
}
.viewer-replay-frame, .viewer-replay-video {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.viewer-unmute-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 50;
    background: rgba(14, 165, 233, 0.95);
    color: #fff;
    border: 2px solid rgba(255, 255, 255, 0.4);
    padding: 12px 26px;
    border-radius: 30px;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    backdrop-filter: blur(10px);
    box-shadow: 0 10px 30px rgba(14, 165, 233, 0.5);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}
.viewer-unmute-btn:hover {
    background: #0284c7;
    transform: translate(-50%, -50%) scale(1.05);
}

/* Connecting Spinner */
.viewer-connecting-overlay {
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, 0.85);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    color: #94a3b8;
    font-size: 0.92rem;
    z-index: 10;
}
.spinner-ring {
    width: 44px;
    height: 44px;
    border: 3.5px solid rgba(14, 165, 233, 0.2);
    border-top-color: #0ea5e9;
    border-radius: 50%;
    animation: spin 1s infinite linear;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}

/* Ended Overlay */
.viewer-ended-overlay {
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, 0.92);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 30;
    padding: 24px;
}
.ended-card {
    text-align: center;
    color: #ffffff;
    max-width: 380px;
}
.ended-icon {
    font-size: 3rem;
    margin-bottom: 12px;
}
.ended-card h3 {
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 8px;
}
.ended-card p {
    color: #94a3b8;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 18px;
}
.ended-stats {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 12px;
    display: flex;
    justify-content: space-around;
    font-size: 0.88rem;
    margin-bottom: 20px;
}
.btn-back-hub {
    display: inline-block;
    background: #0ea5e9;
    color: #ffffff;
    padding: 12px 24px;
    border-radius: 14px;
    font-weight: 700;
    text-decoration: none;
}

/* Top Bar */
.viewer-top-bar {
    position: absolute;
    top: 16px;
    left: 16px;
    right: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 20;
}
.viewer-streamer-info {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    padding: 6px 14px;
    border-radius: 99px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #ffffff;
}
.streamer-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ef4444;
}
.streamer-name {
    font-size: 0.88rem;
    font-weight: 700;
}
.streamer-status {
    font-size: 0.76rem;
    color: #cbd5e1;
    display: flex;
    align-items: center;
    gap: 6px;
}
.badge-live-mini {
    background: #ef4444;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 1px 5px;
    border-radius: 4px;
}
.viewer-top-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-share-stream {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 99px;
    font-size: 0.84rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-share-stream:hover {
    background: rgba(255, 255, 255, 0.2);
}
.btn-close-live-mobile {
    display: none;
}

/* Pinned Product Banner */
.viewer-pinned-product {
    position: absolute;
    bottom: 16px;
    left: 16px;
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 380px;
    z-index: 20;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    animation: liveProductSlideIn 0.3s ease-out;
}
@keyframes liveProductSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.pin-thumb-wrapper {
    position: relative;
    flex-shrink: 0;
}
.pin-thumb {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    object-fit: cover;
    background: #1e293b;
}
.pin-num-tag {
    position: absolute;
    top: -4px;
    left: -4px;
    background: #ef4444;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 900;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid #fff;
}
.pin-info {
    flex: 1;
    min-width: 0;
}
.pin-badge-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}
.pin-badge {
    background: #ef4444;
    color: #ffffff;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 1px 6px;
    border-radius: 4px;
    flex-shrink: 0;
}
.pin-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pin-price-row {
    display: flex;
    align-items: center;
    gap: 8px;
}
.pin-price {
    font-size: 0.95rem;
    font-weight: 800;
    color: #f59e0b;
}
.pin-star {
    font-size: 0.75rem;
    color: #cbd5e1;
}
.pin-buy-btn {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #ffffff;
    border: none;
    padding: 8px 14px;
    border-radius: 99px;
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    transition: transform 0.15s;
}
.pin-buy-btn:hover {
    transform: scale(1.05);
}

/* Floating Reaction Bar (Desktop) */
.viewer-reaction-bar {
    position: absolute;
    bottom: 16px;
    right: 16px;
    display: flex;
    gap: 8px;
    z-index: 20;
}
.reaction-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    font-size: 1.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.15s;
}
.reaction-btn:hover {
    transform: scale(1.2) translateY(-2px);
}

/* Floating Reactions Particle Layer (TikTok Hearts Flow) */
.floating-reaction-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 22;
}
.floating-particle {
    position: absolute;
    bottom: 20px;
    font-size: 2.2rem;
    animation: tiktokFloatUp 2.8s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    filter: drop-shadow(0 2px 8px rgba(0,0,0,0.5));
}
@keyframes tiktokFloatUp {
    0% {
        opacity: 0;
        transform: translateY(0) scale(0.6) rotate(0deg);
    }
    15% {
        opacity: 1;
        transform: translateY(-40px) scale(1.1) rotate(-8deg);
    }
    50% {
        transform: translateY(-160px) scale(1) rotate(12deg);
    }
    80% {
        opacity: 0.8;
        transform: translateY(-280px) scale(0.9) rotate(-10deg);
    }
    100% {
        opacity: 0;
        transform: translateY(-380px) scale(0.7) rotate(15deg);
    }
}

/* Mobile TikTok Action Bar (Hidden on Desktop) */
.mobile-tiktok-action-bar {
    display: none;
}

/* Details Card */
.viewer-details-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 20px 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.details-main-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 10px;
}
.details-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.details-likes-badge {
    background: #fef2f2;
    color: #ef4444;
    border: 1px solid #fee2e2;
    padding: 6px 14px;
    border-radius: 99px;
    font-size: 0.85rem;
    font-weight: 700;
    white-space: nowrap;
}
.details-description {
    color: #64748b;
    font-size: 0.92rem;
    line-height: 1.6;
    margin-bottom: 14px;
}
.details-meta-row {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.82rem;
    color: #64748b;
    flex-wrap: wrap;
}
.category-tag {
    background: #f0fdf4;
    color: #16a34a;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 8px;
}
.youtube-tag {
    background: #fef2f2;
    color: #dc2626;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 8px;
}

/* Related Streams Section */
.related-streams-section {
    background: #ffffff;
    border-radius: 20px;
    padding: 20px 24px;
    border: 1px solid #e2e8f0;
}
.related-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 16px 0;
}
.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px;
}
.related-card {
    text-decoration: none;
    color: inherit;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    transition: transform 0.2s, box-shadow 0.2s;
}
.related-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.06);
}
.related-thumb {
    position: relative;
    aspect-ratio: 16 / 9;
    background: #0f172a;
}
.related-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.related-badge-live {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #ef4444;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
}
.related-info {
    padding: 10px 12px;
}
.related-name {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.related-author {
    font-size: 0.78rem;
    color: #64748b;
}

/* Chat Pane (Desktop) */
.viewer-chat-pane {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    height: 600px;
    overflow: hidden;
}
.chat-pane-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.chat-header-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chat-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 8px #22c55e;
}
.chat-messages-scroll {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.chat-welcome-box {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.4;
    text-align: center;
}
.live-chat-item {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    font-size: 0.85rem;
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
.btn-live-cart-top {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border: none;
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 99px;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

/* ========================================================
   LIVE MODALS (GIỎ HÀNG & XEM NHANH SẢN PHẨM)
   ======================================================== */
.live-modal-backdrop {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 23, 42, 0.75) !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    z-index: 999999 !important;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
}
.live-modal-backdrop.is-open {
    display: flex !important;
    animation: liveModalFadeIn 0.2s ease-out;
}
@keyframes liveModalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.live-modal-dialog {
    background: #ffffff !important;
    border-radius: 24px !important;
    width: 520px;
    max-width: calc(100vw - 32px);
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    border: 1px solid #e2e8f0;
    box-sizing: border-box;
    animation: liveModalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes liveModalSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.live-modal-dialog.dialog-lg {
    width: 640px;
}
.live-modal-header {
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
    background: #ffffff;
    flex-shrink: 0;
}
.modal-title-with-icon {
    display: flex;
    align-items: center;
    gap: 12px;
}
.modal-icon-lg {
    font-size: 1.8rem;
}
.live-modal-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
}
.modal-sub-text {
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 600;
}
.live-modal-close {
    background: #f1f5f9;
    border: none;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    font-size: 1.1rem;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Cart Item List */
.viewer-cart-list {
    padding: 16px 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 520px;
}
.viewer-cart-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    transition: all 0.2s ease;
}
.viewer-cart-item.is-pinned-spotlight {
    background: #fffbeb;
    border-color: #fcd34d;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
}
.cart-item-num {
    font-size: 0.85rem;
    font-weight: 800;
    color: #94a3b8;
    width: 24px;
}
.cart-item-img {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    object-fit: cover;
    background: #e2e8f0;
}
.cart-item-info {
    flex: 1;
    min-width: 0;
}
.badge-spotlight {
    display: inline-block;
    background: #f59e0b;
    color: #ffffff;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    margin-bottom: 4px;
}
.cart-item-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cart-item-pricing {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 2px;
}
.cart-item-price {
    font-size: 0.95rem;
    font-weight: 800;
    color: #ef4444;
}
.cart-item-stars {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
}
.btn-cart-buy {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    border: none;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
}
.empty-viewer-cart {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
}
.empty-viewer-cart span {
    font-size: 3rem;
    display: block;
    margin-bottom: 8px;
}

/* Quick View Modal Body */
.quickview-body {
    padding: 20px 24px;
    overflow-y: auto;
}
.quickview-grid {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 20px;
}
.quickview-media {
    position: relative;
}
.quickview-main-img {
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 18px;
    object-fit: cover;
    background: #f1f5f9;
}
.quickview-star-tag {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(6px);
    color: #fbbf24;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 8px;
}
.quickview-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.quickview-name {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
}
.quickview-pricing-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
}
.quickview-price {
    font-size: 1.4rem;
    font-weight: 900;
    color: #ef4444;
}
.quickview-unit {
    font-size: 0.88rem;
    color: #64748b;
    font-weight: 600;
}
.quickview-desc {
    font-size: 0.88rem;
    color: #475569;
    line-height: 1.55;
    background: #f8fafc;
    padding: 12px 14px;
    border-radius: 12px;
    border-left: 3px solid #0ea5e9;
}
.quickview-qty-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    gap: 12px;
}
.qty-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #334155;
}
.qty-stepper {
    display: flex;
    align-items: center;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    overflow: hidden;
}
.qty-btn-step {
    background: #f1f5f9;
    border: none;
    width: 32px;
    height: 32px;
    font-size: 1.1rem;
    font-weight: 800;
    color: #1e293b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.qty-val {
    width: 44px;
    height: 32px;
    border: none;
    text-align: center;
    font-weight: 800;
    font-size: 0.95rem;
    color: #0f172a;
    background: #ffffff;
    outline: none;
}
.qty-total-preview {
    font-size: 0.85rem;
    color: #64748b;
}
.qty-total-preview b {
    color: #ef4444;
    font-size: 1.05rem;
}
.quickview-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 4px;
}
.btn-qv-add-cart {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    border: none;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 800;
    cursor: pointer;
}
.btn-qv-buy-now {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    border: none;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 800;
    cursor: pointer;
}
.quickview-footer {
    text-align: center;
    margin-top: 4px;
}
.qv-detail-link {
    color: #0284c7;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
}

/* ========================================================
   📱 100% IMMERSIVE TIKTOK LIVE MOBILE EXPERIENCE
   ======================================================== */
@media (max-width: 768px) {
    html, body {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        height: 100dvh !important;
        max-width: 100vw !important;
        max-height: 100dvh !important;
        overflow: hidden !important;
        overscroll-behavior: none !important;
        -webkit-overflow-scrolling: auto !important;
        touch-action: none !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #000000 !important;
    }

    /* Hide standard site header, footer, bottom nav and all floating widgets on mobile live */
    header, footer, .main-navbar, .site-header, nav.navbar, .app-header, .mobile-bottom-nav,
    .floating-cart-btn, .global-cart-button, #global-cart-icon, .floating-cart, .btn-floating-cart,
    .floating-action-btn, #back-to-top, .zalo-chat-widget, #cart-product-modal,
    main > *:not(.live-viewer-container) {
        display: none !important;
    }

    main {
        min-height: 100dvh !important;
        height: 100dvh !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        background: #000000 !important;
    }

    .live-viewer-container {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100% !important;
        height: 100dvh !important;
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100vw !important;
        z-index: 99999 !important;
        background: #000000 !important;
        overflow: hidden !important;
        touch-action: none !important;
    }

    .viewer-layout {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        position: relative !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .viewer-video-pane {
        width: 100% !important;
        height: 100% !important;
        position: absolute !important;
        inset: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        z-index: 1 !important;
    }

    .viewer-screen-wrapper {
        width: 100% !important;
        height: 100% !important;
        border-radius: 0 !important;
        border: none !important;
        aspect-ratio: auto !important;
        position: absolute !important;
        inset: 0 !important;
        background: #000000 !important;
    }

    .viewer-main-video, .viewer-replay-video, .viewer-replay-frame {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    /* Hide desktop details & related section on mobile live screen */
    .viewer-details-card, .related-streams-section, .viewer-reaction-bar, .chat-pane-header, .btn-live-cart-top, .btn-share-stream {
        display: none !important;
    }

    /* TikTok Top Bar */
    .viewer-top-bar {
        position: absolute !important;
        top: max(14px, env(safe-area-inset-top, 14px)) !important;
        left: 12px !important;
        right: 12px !important;
        z-index: 40 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    .viewer-streamer-info {
        background: rgba(0, 0, 0, 0.5) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 30px !important;
        padding: 4px 12px 4px 4px !important;
        gap: 8px !important;
    }
    .streamer-avatar {
        width: 34px !important;
        height: 34px !important;
        border: 2px solid #ef4444 !important;
    }
    .streamer-name {
        font-size: 0.82rem !important;
        max-width: 110px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    .streamer-status {
        font-size: 0.72rem !important;
    }

    .viewer-top-actions {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        pointer-events: auto !important;
        z-index: 101 !important;
    }

    .btn-close-live-mobile {
        display: flex !important;
        width: 38px !important;
        height: 38px !important;
        border-radius: 50% !important;
        background: rgba(0, 0, 0, 0.65) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        color: #ffffff !important;
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        align-items: center !important;
        justify-content: center !important;
        text-decoration: none !important;
        cursor: pointer !important;
        pointer-events: auto !important;
        touch-action: manipulation !important;
        -webkit-tap-highlight-color: transparent !important;
        z-index: 102 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4) !important;
    }

    .floating-reaction-layer {
        pointer-events: none !important;
        position: absolute !important;
        inset: 0 !important;
        z-index: 15 !important;
        overflow: hidden !important;
    }

    /* TikTok Floating Chat Overlay (Bottom Left) */
    .viewer-chat-pane {
        position: absolute !important;
        bottom: calc(max(14px, env(safe-area-inset-bottom, 14px)) + 68px) !important;
        left: 12px !important;
        width: min(290px, 78vw) !important;
        height: 230px !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        z-index: 30 !important;
        pointer-events: none !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-end !important;
    }

    .chat-messages-scroll {
        background: transparent !important;
        padding: 0 !important;
        max-height: 100% !important;
        mask-image: linear-gradient(to top, black 75%, transparent 100%) !important;
        -webkit-mask-image: linear-gradient(to top, black 75%, transparent 100%) !important;
        pointer-events: auto !important;
        gap: 6px !important;
    }

    .chat-welcome-box {
        background: rgba(0, 0, 0, 0.45) !important;
        backdrop-filter: blur(8px) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: #f1f5f9 !important;
        font-size: 0.76rem !important;
        padding: 6px 10px !important;
        border-radius: 12px !important;
    }

    .live-chat-item {
        background: rgba(0, 0, 0, 0.5) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        border-radius: 16px !important;
        padding: 4px 10px !important;
        color: #ffffff !important;
        display: inline-flex !important;
        max-width: 95% !important;
        gap: 6px !important;
        align-items: center !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    }
    .chat-avatar {
        width: 22px !important;
        height: 22px !important;
    }
    .chat-content {
        background: transparent !important;
        padding: 0 !important;
        max-width: 100% !important;
        display: inline !important;
    }
    .chat-username {
        color: #38bdf8 !important;
        font-size: 0.76rem !important;
        font-weight: 800 !important;
        display: inline !important;
        margin-right: 4px !important;
    }
    .chat-text {
        color: #ffffff !important;
        font-size: 0.82rem !important;
        text-shadow: 0 1px 3px rgba(0,0,0,0.8) !important;
        display: inline !important;
    }

    /* Hide desktop chat input on mobile */
    .viewer-chat-pane .chat-input-area {
        display: none !important;
    }

    /* TikTok Pinned Product Deal Card (Floats above bottom bar on right side) */
    .viewer-pinned-product {
        position: absolute !important;
        bottom: calc(max(14px, env(safe-area-inset-bottom, 14px)) + 74px) !important;
        right: 12px !important;
        left: auto !important;
        width: 220px !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(12px) !important;
        border-radius: 16px !important;
        padding: 8px 10px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.4) !important;
        z-index: 35 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }
    .viewer-pinned-product .pin-thumb {
        width: 44px !important;
        height: 44px !important;
        border-radius: 8px !important;
    }
    .viewer-pinned-product .pin-title {
        color: #0f172a !important;
        font-size: 0.8rem !important;
        font-weight: 800 !important;
    }
    .viewer-pinned-product .pin-price {
        color: #ef4444 !important;
        font-size: 0.88rem !important;
        font-weight: 900 !important;
    }
    .viewer-pinned-product .pin-buy-btn {
        background: #ff0050 !important;
        color: #ffffff !important;
        padding: 6px 12px !important;
        font-size: 0.8rem !important;
        font-weight: 800 !important;
        border-radius: 20px !important;
    }

    /* TikTok Mobile Bottom Action Bar */
    .mobile-tiktok-action-bar {
        display: flex !important;
        align-items: center !important;
        position: absolute !important;
        bottom: max(12px, env(safe-area-inset-bottom, 12px)) !important;
        left: 12px !important;
        right: 12px !important;
        z-index: 45 !important;
        gap: 8px !important;
    }

    .btn-tiktok-bag {
        width: 44px !important;
        height: 44px !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #f59e0b, #ea580c) !important;
        border: none !important;
        color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.3rem !important;
        position: relative !important;
        flex-shrink: 0 !important;
        box-shadow: 0 4px 15px rgba(234, 88, 12, 0.4) !important;
        cursor: pointer !important;
    }
    .tiktok-bag-badge {
        position: absolute !important;
        top: -3px !important;
        right: -3px !important;
        background: #ef4444 !important;
        color: #ffffff !important;
        font-size: 0.65rem !important;
        font-weight: 900 !important;
        padding: 1px 5px !important;
        border-radius: 10px !important;
        border: 1.5px solid #ffffff !important;
    }

    .tiktok-chat-form {
        flex: 1 !important;
        display: flex !important;
        align-items: center !important;
        background: rgba(0, 0, 0, 0.5) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        border-radius: 30px !important;
        padding: 0 10px 0 14px !important;
        height: 42px !important;
    }
    .tiktok-chat-input {
        flex: 1 !important;
        background: transparent !important;
        border: none !important;
        outline: none !important;
        color: #ffffff !important;
        font-size: 0.85rem !important;
    }
    .tiktok-chat-input::placeholder {
        color: rgba(255, 255, 255, 0.65) !important;
    }
    .btn-tiktok-send-icon {
        background: transparent !important;
        border: none !important;
        color: #38bdf8 !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 4px !important;
    }

    .tiktok-reactions-group {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        flex-shrink: 0 !important;
    }
    .tiktok-reaction-btn {
        width: 40px !important;
        height: 40px !important;
        border-radius: 50% !important;
        background: rgba(0, 0, 0, 0.5) !important;
        backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.25rem !important;
        cursor: pointer !important;
        transition: transform 0.15s !important;
    }
    .tiktok-reaction-btn:active {
        transform: scale(1.25) !important;
    }

    /* Modal Bottom Sheet on Mobile */
    .live-modal-dialog {
        border-radius: 24px 24px 0 0 !important;
        width: 100vw !important;
        max-width: 100vw !important;
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        max-height: 75vh !important;
        margin: 0 !important;
    }
    .live-modal-backdrop {
        padding: 0 !important;
        align-items: flex-end !important;
    }
    .quickview-grid {
        grid-template-columns: 1fr !important;
    }
    .quickview-media {
        max-width: 200px !important;
        margin: 0 auto !important;
    }
}
</style>
@endsection
