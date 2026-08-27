@extends('layouts.app')

@section('title', 'Đang Xem Trực Tiếp: ' . $stream->title)
@section('meta_description', $stream->description ?? ('Xem livestream trực tiếp ' . $stream->title . ' trên DongAnh Discovery'))

@section('content')
<div class="live-viewer-container">
    <div class="viewer-layout">
        <!-- Main Video & Interactions Column (Left) -->
        <div class="viewer-video-pane">
            <div class="viewer-screen-wrapper">
                <!-- Video Element (muted enables instant smooth autoplay across all browsers) -->
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

                <!-- Floating Reactions Layer -->
                <div id="viewer-reaction-layer" class="floating-reaction-layer"></div>

                <!-- Stream Ended Overlay -->
                <div id="viewer-ended-overlay" class="viewer-ended-overlay" style="display: none;">
                    <div class="ended-card">
                        <div class="ended-icon">🎬</div>
                        <h3>Phiên Livestream đã kết thúc</h3>
                        <p>Cảm ơn bạn đã đồng hành và tương tác cùng người phát sóng!</p>
                        <div class="ended-stats">
                            <div>⏱️ Thời lượng: <b id="ended-duration">00:00</b></div>
                            <div>❤️ Lượt thích: <b id="ended-likes">0</b></div>
                        </div>
                        <a href="{{ route('livestream.index') }}" class="btn-back-hub">
                            Xem các phòng Live khác
                        </a>
                    </div>
                </div>

                <!-- Top Status Bar -->
                <div class="viewer-top-bar">
                    <div class="viewer-streamer-info">
                        <img src="{{ $stream->user->avatar_url ?: ('https://ui-avatars.com/api/?name=' . urlencode($stream->user->name) . '&background=0ea5e9&color=fff') }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($stream->user->name) }}&background=0ea5e9&color=fff';" alt="{{ $stream->user->name }}" class="streamer-avatar">
                        <div class="streamer-text">
                            <div class="streamer-name">{{ $stream->user->name }}</div>
                            <div class="streamer-status">
                                <span class="badge-live-mini">LIVE</span>
                                <span>👥 <span id="viewer-online-count">{{ max(1, $stream->viewer_count) }}</span> người xem</span>
                            </div>
                        </div>
                    </div>
                    <div class="viewer-top-actions">
                        <button type="button" class="btn-live-cart-top" onclick="openViewerCart()" id="btn-viewer-cart-top">
                            🛍️ Túi Hàng (<span id="viewer-cart-top-count">{{ $stream->products->count() }}</span>)
                        </button>
                        <button type="button" class="btn-share-stream" onclick="copyStreamUrl()">
                            ↗ Chia sẻ
                        </button>
                    </div>
                </div>

                <!-- Pinned Product Overlay Banner -->
                <div id="viewer-pinned-product-banner" class="viewer-pinned-product" style="{{ $stream->pinnedProduct ? 'display:flex;' : 'display:none;' }}">
                    @if($stream->pinnedProduct)
                        <img src="{{ $stream->pinnedProduct->image_url ? asset($stream->pinnedProduct->image_url) : '/images/ocop-placeholder.png' }}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="pin-thumb" alt="{{ $stream->pinnedProduct->name }}">
                        <div class="pin-info">
                            <span class="pin-badge">🏷️ Đang giới thiệu</span>
                            <div class="pin-title">{{ $stream->pinnedProduct->name }}</div>
                            <div class="pin-price">{{ $stream->pinnedProduct->price ? number_format($stream->pinnedProduct->price) . 'đ' : 'OCOP' }}</div>
                        </div>
                        <button type="button" onclick="openProductQuickView({{ $stream->pinnedProduct->id }}, event)" class="pin-buy-btn">
                            🛒 Xem & Mua
                        </button>
                    @endif
                </div>

                <!-- Floating Reaction Bar -->
                <div class="viewer-reaction-bar">
                    <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('heart')" title="Thả tim">❤️</button>
                    <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('fire')" title="Tuyệt vời">🔥</button>
                    <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('clap')" title="Vỗ tay">👏</button>
                    <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('wow')" title="Yêu thích">😍</button>
                    <button type="button" class="reaction-btn" onclick="DongAnhLiveViewer.sendReaction('star')" title="Tặng sao">⭐</button>
                </div>
            </div>

            <!-- Stream Details Card -->
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
                </div>
            </div>

            <!-- Related Streams -->
            @if($relatedStreams->count() > 0)
                <div class="related-streams-section">
                    <h3 class="related-title">Các phòng Live khác đang phát</h3>
                    <div class="related-grid">
                        @foreach($relatedStreams as $rel)
                            <a href="{{ route('livestream.show', $rel->code_or_id) }}" class="related-card">
                                <div class="related-thumb">
                                    <img src="{{ $rel->cover_image ? asset($rel->cover_image) : 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=400&q=80' }}" alt="{{ $rel->title }}">
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

        <!-- Live Chat Pane (Right) -->
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
    if ($stream->pinnedProduct && !isset($productsMap[$stream->pinnedProduct->id])) {
        $p = $stream->pinnedProduct;
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

<script src="{{ asset('js/livestream-viewer.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    DongAnhLiveViewer.init({
        streamId: '{{ $stream->code_or_id }}',
        channelId: {{ $stream->id }}
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

    document.getElementById('qv-product-img').src = product.image || product.image_url || '/assets/icon/default_food.png';
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
    if (input && input.value.trim()) {
        DongAnhLiveViewer.sendComment(input.value.trim());
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
.live-viewer-container {
    max-width: 1320px;
    margin: 16px auto 48px auto;
    padding: 0 16px;
    font-family: 'Plus Jakarta Sans', 'Be Vietnam Pro', sans-serif;
}

.viewer-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
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

.viewer-unmute-btn {
    position: absolute;
    top: 60px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 50;
    background: rgba(15, 23, 42, 0.88);
    color: #fff;
    border: 1.5px solid rgba(255, 255, 255, 0.3);
    padding: 10px 22px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    backdrop-filter: blur(8px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}
.viewer-unmute-btn:hover {
    background: #0ea5e9;
    border-color: #38bdf8;
    transform: translateX(-50%) scale(1.04);
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

/* Unmute Button */
.viewer-unmute-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(14, 165, 233, 0.9);
    backdrop-filter: blur(8px);
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    border-radius: 99px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    z-index: 25;
    box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
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
    border: 1px solid rgba(255, 255, 255, 0.1);
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

/* Pinned Product Banner */
.viewer-pinned-product {
    position: absolute;
    bottom: 16px;
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
.pin-buy-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    border: none;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 10px;
    cursor: pointer;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    transition: all 0.2s;
}
.pin-buy-btn:hover {
    transform: scale(1.04);
}

/* Floating Reactions */
.viewer-reaction-bar {
    position: absolute;
    bottom: 16px;
    right: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 20;
}
.reaction-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    font-size: 1.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}
.reaction-btn:hover {
    transform: scale(1.18);
    background: rgba(15, 23, 42, 0.95);
}
.reaction-btn:active {
    transform: scale(0.9);
}

/* Floating Reactions Layer */
.floating-reaction-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 25;
}
.floating-particle {
    position: absolute;
    bottom: 20px;
    font-size: 2.4rem;
    animation: floatUp 2.8s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    opacity: 0.95;
    filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.5));
    user-select: none;
    pointer-events: none;
    z-index: 26;
}
@keyframes floatUp {
    0% { transform: translateY(0) scale(0.6) rotate(0deg); opacity: 0; }
    15% { opacity: 1; transform: translateY(-30px) scale(1.25) rotate(-8deg); }
    50% { transform: translateY(-160px) scale(1.05) rotate(12deg); }
    80% { opacity: 0.85; }
    100% { transform: translateY(-380px) scale(0.75) rotate(-18deg); opacity: 0; }
}

/* Details Card */
.viewer-details-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}
.details-main-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}
.details-title {
    font-size: 1.4rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.details-likes-badge {
    background: #fee2e2;
    color: #dc2626;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 6px 14px;
    border-radius: 99px;
    white-space: nowrap;
}
.details-description {
    color: #64748b;
    font-size: 0.94rem;
    line-height: 1.6;
    margin-bottom: 16px;
}
.details-meta-row {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.82rem;
    color: #64748b;
}
.category-tag {
    background: #f1f5f9;
    color: #334155;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
}

/* Related Streams */
.related-streams-section {
    margin-top: 12px;
}
.related-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 14px;
}
.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
}
.related-card {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: all 0.2s;
}
.related-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px -5px rgba(0,0,0,0.08);
}
.related-thumb {
    position: relative;
    aspect-ratio: 16 / 10;
    background: #0f172a;
}
.related-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.related-badge-live {
    position: absolute;
    top: 6px;
    left: 6px;
    background: #ef4444;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
}
.related-info {
    padding: 10px;
}
.related-name {
    font-size: 0.86rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.related-author {
    font-size: 0.76rem;
    color: #64748b;
    margin-top: 2px;
}

/* Chat Pane */
.viewer-chat-pane {
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
    animation: cart-pulse 2.5s infinite;
}
.btn-live-cart-top:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(245, 158, 11, 0.6);
}
@keyframes cart-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.04); }
}

/* ========================================================
   LIVE MODALS (POPUPS FIXED FULLSCREEN)
   ======================================================== */
.live-modal-backdrop {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 23, 42, 0.8) !important;
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
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 1.1rem;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.live-modal-close:hover {
    background: #e2e8f0;
    color: #0f172a;
}
@keyframes liveModalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes liveModalSlideUp {
    from { transform: translateY(20px) scale(0.97); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

/* Viewer Cart Items */
.viewer-cart-list {
    max-height: 60vh;
    overflow-y: auto;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #ffffff;
}
.viewer-cart-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    transition: all 0.2s;
}
.viewer-cart-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}
.viewer-cart-item.is-pinned-spotlight {
    border-color: #f59e0b;
    background: #fffbeb;
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.15);
}
.cart-item-num {
    font-size: 0.82rem;
    font-weight: 800;
    color: #94a3b8;
    min-width: 24px;
}
.cart-item-img {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}
.cart-item-info {
    flex: 1;
    min-width: 0;
}
.badge-spotlight {
    display: inline-block;
    background: #fef3c7;
    color: #b45309;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    margin-bottom: 3px;
}
.cart-item-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cart-item-pricing {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 3px;
}
.cart-item-price {
    font-size: 0.88rem;
    font-weight: 800;
    color: #ef4444;
}
.cart-item-stars {
    font-size: 0.74rem;
    color: #d97706;
    font-weight: 700;
}
.btn-cart-buy {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    border: none;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
    transition: all 0.2s;
}
.btn-cart-buy:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(16, 185, 129, 0.45);
}
.empty-viewer-cart {
    text-align: center;
    padding: 40px 20px;
    color: #64748b;
}
.empty-viewer-cart span {
    font-size: 3rem;
    display: block;
    margin-bottom: 8px;
}

/* Quick View Modal Body & Layout */
.quickview-body {
    padding: 24px;
    overflow-y: auto;
    max-height: 75vh;
    background: #ffffff;
}
.quickview-grid {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 24px;
    align-items: start;
}
.quickview-media {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    aspect-ratio: 1 / 1;
}
.quickview-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.quickview-star-tag {
    position: absolute;
    bottom: 10px;
    left: 10px;
    right: 10px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(8px);
    color: #fbbf24;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 8px;
    text-align: center;
}
.quickview-info {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.quickview-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    margin: 0;
}
.quickview-pricing-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
}
.quickview-price {
    font-size: 1.5rem;
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
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.qty-btn-step:hover {
    background: #e2e8f0;
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
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.2s;
}
.btn-qv-add-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
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
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
    transition: all 0.2s;
}
.btn-qv-buy-now:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(245, 158, 11, 0.45);
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
.qv-detail-link:hover {
    text-decoration: underline;
}

@media (max-width: 600px) {
    .quickview-grid {
        grid-template-columns: 1fr;
    }
    .quickview-media {
        max-width: 240px;
        margin: 0 auto;
    }
    .quickview-actions {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 980px) {
    .viewer-layout {
        grid-template-columns: 1fr;
    }
    .viewer-chat-pane {
        height: 480px;
    }
}
</style>
@endsection
