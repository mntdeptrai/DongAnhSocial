/**
 * DongAnhSocial - Livestream Viewer Engine (WebRTC + Reverb Signaling)
 * Nhận video/audio trực tiếp, xem bình luận real-time, thả tim reaction, tương tác sản phẩm OCOP
 */

window.DongAnhLiveViewer = (function () {
    let streamId = null;
    let channelId = null;
    let mySessionId = 'viewer_' + Math.random().toString(36).substring(2, 9);
    let peerConnection = null;
    let remoteStream = null;
    let pendingIceCandidates = [];

    const rtcConfig = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun.cloudflare.com:3478' },
            { urls: 'stun:openrelay.metered.ca:80' },
            { urls: 'turn:openrelay.metered.ca:80', username: 'openrelayproject', credential: 'openrelayproject' },
            { urls: 'turn:openrelay.metered.ca:443', username: 'openrelayproject', credential: 'openrelayproject' }
        ],
        iceCandidatePoolSize: 10
    };

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Khởi tạo phòng xem Livestream cho Viewer
     */
    function init(config) {
        streamId = config.streamId;
        channelId = config.channelId || config.streamId;
        console.log('[LiveViewer] Initializing viewer for stream #', streamId, 'Channel:', channelId, 'Session:', mySessionId);

        // 1. Tạo WebRTC RTCPeerConnection
        createPeerConnection();

        // 2. Lắng nghe WebSocket Channel từ Laravel Echo / Reverb
        setupEchoListeners();

        // 3. Khởi động cơ chế dự phòng HTTP Signaling Fallback
        startHttpPollingFallback();

        // 4. Gửi tín hiệu tham gia phòng tới Host ngay lập tức (100ms)
        setTimeout(() => {
            sendSignal('host', 'viewer_join', null);
        }, 100);

        // Lặp lại gửi tín hiệu nếu chưa kết nối (mỗi 1.5s trong 10s đầu)
        const retryTimer = setInterval(() => {
            if (!peerConnection || (peerConnection.connectionState !== 'connected' && peerConnection.iceConnectionState !== 'connected')) {
                console.log('[LiveViewer] Handshaking... Sending viewer_join signal to Host.');
                sendSignal('host', 'viewer_join', null);
            } else {
                console.log('[LiveViewer] WebRTC Connected successfully!');
                clearInterval(retryTimer);
            }
        }, 1500);
    }

    let lastSignalTimestamp = 0;
    let pollingInterval = null;

    /**
     * Cơ chế dự phòng: Tự động kéo tín hiệu qua HTTP khi WebSocket bị chậm hoặc bị gián đoạn
     */
    function startHttpPollingFallback() {
        if (pollingInterval) return;
        pollingInterval = setInterval(async () => {
            if (peerConnection && (peerConnection.connectionState === 'connected' || peerConnection.iceConnectionState === 'connected')) {
                return;
            }

            try {
                const res = await fetch(`/livestream/${streamId}/signals?session_id=${mySessionId}&since=${lastSignalTimestamp}`);
                const data = await res.json();
                if (data.status === 'success' && data.signals && data.signals.length > 0) {
                    for (const sig of data.signals) {
                        if (sig.timestamp > lastSignalTimestamp) {
                            lastSignalTimestamp = sig.timestamp;
                        }
                        console.log('[LiveViewer] [HTTP Poll] Received signal:', sig.signal_type, 'from:', sig.sender_session_id);
                        if (sig.signal_type === 'host_offer') {
                            handleHostOffer(sig.sender_session_id, sig.signal_data);
                        } else if (sig.signal_type === 'ice_candidate') {
                            handleIceCandidate(sig.signal_data);
                        } else if (sig.signal_type === 'host_ready') {
                            sendSignal(sig.sender_session_id || 'host', 'viewer_join', null);
                        }
                    }
                }
                if (data.now) {
                    lastSignalTimestamp = Math.max(lastSignalTimestamp, data.now - 10);
                }
            } catch (e) {
                // ignore
            }
        }, 500);
    }

    /**
     * Tạo RTCPeerConnection để nhận MediaStream
     */
    function createPeerConnection() {
        if (peerConnection) {
            try { peerConnection.close(); } catch(e) {}
        }

        peerConnection = new RTCPeerConnection(rtcConfig);
        remoteStream = new MediaStream();
        pendingIceCandidates = [];

        // Đăng ký nhận video và audio chủ động
        try {
            peerConnection.addTransceiver('video', { direction: 'recvonly' });
            peerConnection.addTransceiver('audio', { direction: 'recvonly' });
        } catch (e) {
            console.warn('[LiveViewer] addTransceiver warning:', e);
        }

        const videoEl = document.getElementById('viewer-video-player');
        if (videoEl) {
            videoEl.srcObject = remoteStream;
            videoEl.muted = true;
        }

        // Nhận track từ Host
        peerConnection.ontrack = (event) => {
            console.log('[LiveViewer] Received remote track:', event.track.kind);
            if (event.streams && event.streams[0]) {
                const videoElement = document.getElementById('viewer-video-player');
                if (videoElement && videoElement.srcObject !== event.streams[0]) {
                    videoElement.srcObject = event.streams[0];
                }
            } else {
                remoteStream.addTrack(event.track);
            }

            hideLoadingOverlay();
            attemptPlayVideo();
        };

        // Gửi ICE candidate lên Server
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal('host', 'ice_candidate', JSON.stringify(event.candidate));
            }
        };

        peerConnection.onconnectionstatechange = () => {
            console.log('[LiveViewer] Connection state:', peerConnection.connectionState);
            if (peerConnection.connectionState === 'connected') {
                hideLoadingOverlay();
                attemptPlayVideo();
            } else if (peerConnection.connectionState === 'disconnected' || peerConnection.connectionState === 'failed') {
                showReconnectingOverlay();
            }
        };

        peerConnection.oniceconnectionstatechange = () => {
            console.log('[LiveViewer] ICE state:', peerConnection.iceConnectionState);
            if (peerConnection.iceConnectionState === 'connected' || peerConnection.iceConnectionState === 'completed') {
                hideLoadingOverlay();
                attemptPlayVideo();
            }
        };
    }

    /**
     * Cài đặt lắng nghe Reverb / Echo
     */
    function setupEchoListeners() {
        if (!window.Echo) {
            console.warn('[LiveViewer] Laravel Echo not ready yet. Retrying...');
            setTimeout(setupEchoListeners, 1000);
            return;
        }

        const channel = window.Echo.channel('live-stream.' + channelId);

        // 1. Tín hiệu WebRTC
        channel.listen('.LiveStreamSignal', async (e) => {
            if (e.target_session_id !== mySessionId && e.target_session_id !== 'all') {
                return;
            }

            console.log('[LiveViewer] Received signal:', e.signal_type, 'from:', e.sender_session_id);

            if (e.signal_type === 'host_offer') {
                handleHostOffer(e.sender_session_id, e.signal_data);
            } else if (e.signal_type === 'ice_candidate') {
                handleIceCandidate(e.signal_data);
            } else if (e.signal_type === 'host_ready') {
                sendSignal(e.sender_session_id || 'host', 'viewer_join', null);
            }
        });

        // 2. Bình luận mới
        channel.listen('.LiveStreamCommentSent', (e) => {
            appendComment(e);
        });

        // 3. Reaction thả tim
        channel.listen('.LiveStreamReactionSent', (e) => {
            renderFloatingReaction(e.reaction_type);
            const likesCountEl = document.getElementById('viewer-likes-count');
            if (likesCountEl && e.total_likes) {
                likesCountEl.innerText = Number(e.total_likes).toLocaleString('vi-VN');
            }
        });

        // 4. Đổi sản phẩm ghim
        channel.listen('.LiveStreamProductPinned', (e) => {
            updatePinnedProductUI(e.product);
            highlightPinnedCartItem(e.product ? e.product.id : null);
        });

        // 5. Cập nhật danh sách nhiều sản phẩm trong giỏ hàng
        channel.listen('.LiveStreamProductsUpdated', (e) => {
            updateViewerCartUI(e.products || [], e.pinned_product);
        });

        // 6. Host kết thúc phiên Live
        channel.listen('.LiveStreamEnded', (e) => {
            handleStreamEnded(e);
        });
    }


    /**
     * Xử lý Offer từ Host
     */
    async function handleHostOffer(hostSessionId, sdpData) {
        if (!peerConnection) createPeerConnection();

        try {
            const offer = typeof sdpData === 'string' ? JSON.parse(sdpData) : sdpData;
            console.log('[LiveViewer] Setting remote description (Host Offer)...');
            await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));

            // Xả hàng đợi ICE candidate đã nhận trước đó
            while (pendingIceCandidates.length > 0) {
                const candidate = pendingIceCandidates.shift();
                try {
                    await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                } catch (e) {
                    console.warn('[LiveViewer] Error draining queued ICE candidate:', e);
                }
            }

            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);

            console.log('[LiveViewer] Sending Answer to Host:', hostSessionId);
            sendSignal(hostSessionId, 'viewer_answer', JSON.stringify(answer));
        } catch (err) {
            console.error('[LiveViewer] Error handling host offer:', err);
        }
    }

    /**
     * Nhận ICE Candidate
     */
    async function handleIceCandidate(candidateData) {
        try {
            const candidate = typeof candidateData === 'string' ? JSON.parse(candidateData) : candidateData;
            if (!candidate) return;

            if (!peerConnection || !peerConnection.remoteDescription || !peerConnection.remoteDescription.type) {
                console.log('[LiveViewer] Remote description not set yet. Queuing ICE candidate.');
                pendingIceCandidates.push(candidate);
                return;
            }

            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
        } catch (err) {
            console.error('[LiveViewer] Error adding ICE candidate:', err);
        }
    }

    /**
     * Gửi tín hiệu WebRTC lên Server
     */
    async function sendSignal(targetSessionId, signalType, signalData) {
        try {
            console.log('[LiveViewer] Sending signal:', signalType, 'to:', targetSessionId);
            const res = await fetch(`/livestream/${streamId}/signal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({
                    sender_session_id: mySessionId,
                    target_session_id: targetSessionId,
                    signal_type: signalType,
                    signal_data: signalData
                })
            });
            const data = await res.json();
            if (data.status !== 'success') {
                console.warn('[LiveViewer] sendSignal response:', data);
            }
        } catch (err) {
            console.warn('[LiveViewer] sendSignal error:', err);
        }
    }

    /**
     * Phát video an toàn và xử lý chặn autoplay
     */
    function attemptPlayVideo() {
        const videoEl = document.getElementById('viewer-video-player');
        if (!videoEl) return;

        // Thử phát có tiếng trước
        const playPromise = videoEl.play();
        if (playPromise !== undefined) {
            playPromise.then(() => {
                const unblockBtn = document.getElementById('viewer-unblock-audio-btn');
                if (unblockBtn) unblockBtn.style.display = 'none';
            }).catch(() => {
                // Trình duyệt chặn autoplay có tiếng -> Chuyển sang muted để hiển thị video ngay
                console.log('[LiveViewer] Autoplay with audio blocked by browser policy. Playing muted first.');
                videoEl.muted = true;
                videoEl.play().catch(e => console.warn('[LiveViewer] Muted play error:', e));

                // Hiển thị nút bật tiếng nổi bật
                const unblockBtn = document.getElementById('viewer-unblock-audio-btn');
                if (unblockBtn) unblockBtn.style.display = 'flex';
            });
        }
    }

    function unmuteAndPlay() {
        const videoEl = document.getElementById('viewer-video-player');
        if (videoEl) {
            videoEl.muted = false;
            videoEl.play().catch(e => console.warn('[LiveViewer] unmute play error:', e));
        }
        const unblockBtn = document.getElementById('viewer-unblock-audio-btn');
        if (unblockBtn) unblockBtn.style.display = 'none';
    }

    /**
     * Gửi bình luận
     */
    async function sendComment(messageText) {
        const text = (messageText || '').trim();
        if (!text) return;

        const inputEl = document.getElementById('viewer-comment-input');
        if (inputEl) inputEl.value = '';

        try {
            const res = await fetch(`/livestream/${streamId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ message: text })
            });

            if (res.status === 401) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Đăng nhập để tương tác',
                        text: 'Vui lòng đăng nhập để tham gia trò chuyện cùng streamer!',
                        confirmButtonText: 'Đăng nhập ngay',
                        confirmButtonColor: '#0ea5e9'
                    }).then(r => {
                        if (r.isConfirmed) window.location.href = '/login';
                    });
                } else {
                    window.location.href = '/login';
                }
                return;
            }

            const data = await res.json();
            if (data.status === 'success') {
                appendComment(data.comment);
            }
        } catch (err) {
            console.error('[LiveViewer] Send comment failed:', err);
        }
    }

    /**
     * Thêm bình luận vào khung chat
     */
    function appendComment(comment) {
        const chatBox = document.getElementById('viewer-chat-messages');
        if (!chatBox) return;

        const div = document.createElement('div');
        div.className = 'live-chat-item';
        div.innerHTML = `
            <img src="${comment.user_avatar}" alt="${comment.user_name}" class="chat-avatar">
            <div class="chat-content">
                <span class="chat-username">${comment.user_name}</span>
                <span class="chat-text">${escapeHtml(comment.message)}</span>
            </div>
        `;
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    /**
     * Gửi Reaction cảm xúc (Thả tim, lửa, vỗ tay)
     */
    async function sendReaction(type = 'heart') {
        renderFloatingReaction(type);

        try {
            const res = await fetch(`/livestream/${streamId}/reaction`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ type: type })
            });
            const data = await res.json();
            if (data.status === 'success') {
                const likesCountEl = document.getElementById('viewer-likes-count');
                if (likesCountEl && data.total_likes) {
                    likesCountEl.innerText = Number(data.total_likes).toLocaleString('vi-VN');
                }
            }
        } catch (_) {}
    }

    /**
     * Hiệu ứng Particle Floating Reaction bay lên màn hình
     */
    function renderFloatingReaction(type = 'heart') {
        const container = document.getElementById('viewer-reaction-layer');
        if (!container) return;

        const iconMap = {
            heart: '❤️',
            fire: '🔥',
            clap: '👏',
            wow: '😍',
            star: '⭐'
        };

        const icon = iconMap[type] || '❤️';
        const count = 2;

        for (let i = 0; i < count; i++) {
            setTimeout(() => {
                const el = document.createElement('div');
                el.className = 'floating-particle';
                el.innerText = icon;
                const startLeft = 70 + (Math.random() * 24 - 12);
                el.style.left = startLeft + '%';
                el.style.animationDuration = (Math.random() * 0.8 + 2.2) + 's';
                el.style.fontSize = (Math.random() * 0.8 + 2) + 'rem';

                container.appendChild(el);
                setTimeout(() => el.remove(), 3000);
            }, i * 140);
        }
    }

    /**
     * Cập nhật danh sách nhiều sản phẩm trong giỏ hàng Khán giả
     */
    function updateViewerCartUI(products = [], pinnedProduct = null) {
        if (!window.__liveProducts) window.__liveProducts = {};
        products.forEach(p => {
            if (p && p.id) window.__liveProducts[p.id] = p;
        });
        if (pinnedProduct && pinnedProduct.id) {
            window.__liveProducts[pinnedProduct.id] = pinnedProduct;
        }

        const topCountEl = document.getElementById('viewer-cart-top-count');
        const totalCountEl = document.getElementById('viewer-cart-total-count');
        if (topCountEl) topCountEl.innerText = products.length;
        if (totalCountEl) totalCountEl.innerText = products.length;

        const listEl = document.getElementById('viewer-cart-items-list');
        if (listEl) {
            if (products.length === 0) {
                listEl.innerHTML = `
                    <div class="empty-viewer-cart" id="empty-viewer-cart-msg">
                        <span>🛍️</span>
                        <p>Streamer chưa gắn sản phẩm nào vào giỏ hàng.</p>
                    </div>
                `;
            } else {
                listEl.innerHTML = products.map((prod, idx) => {
                    const isPinned = prod.is_pinned;
                    return `
                        <div class="viewer-cart-item ${isPinned ? 'is-pinned-spotlight' : ''}" id="viewer-cart-row-${prod.id}">
                            <div class="cart-item-num">#${idx + 1}</div>
                            <img src="${prod.image_url || prod.image || '/images/ocop-placeholder.png'}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="cart-item-img" alt="${escapeHtml(prod.name)}">
                            <div class="cart-item-info">
                                ${isPinned ? '<span class="badge-spotlight">🔥 Đang giới thiệu</span>' : ''}
                                <div class="cart-item-name">${escapeHtml(prod.name)}</div>
                                <div class="cart-item-pricing">
                                    <span class="cart-item-price">${prod.price}</span>
                                    ${prod.star_rating ? `<span class="cart-item-stars">⭐ ${prod.star_rating}</span>` : ''}
                                </div>
                            </div>
                            <div class="cart-item-actions">
                                <button type="button" onclick="openProductQuickView(${prod.id}, event)" class="btn-cart-buy">
                                    🛒 Xem & Mua
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        updatePinnedProductUI(pinnedProduct);
    }

    /**
     * Làm nổi bật sản phẩm đang ghim trong danh sách giỏ hàng
     */
    function highlightPinnedCartItem(pinnedId) {
        document.querySelectorAll('.viewer-cart-item').forEach(item => {
            const isMatch = pinnedId && item.id === `viewer-cart-row-${pinnedId}`;
            if (isMatch) {
                item.classList.add('is-pinned-spotlight');
                if (!item.querySelector('.badge-spotlight')) {
                    const info = item.querySelector('.cart-item-info');
                    if (info) {
                        const badge = document.createElement('span');
                        badge.className = 'badge-spotlight';
                        badge.innerText = '🔥 Đang giới thiệu';
                        info.insertBefore(badge, info.firstChild);
                    }
                }
            } else {
                item.classList.remove('is-pinned-spotlight');
                const badge = item.querySelector('.badge-spotlight');
                if (badge) badge.remove();
            }
        });
    }

    /**
     * Cập nhật thẻ sản phẩm OCOP đang ghim
     */
    function updatePinnedProductUI(product) {
        const banner = document.getElementById('viewer-pinned-product-banner');
        if (!banner) return;

        if (!product) {
            banner.style.display = 'none';
            return;
        }

        if (!window.__liveProducts) window.__liveProducts = {};
        window.__liveProducts[product.id] = product;

        banner.style.display = 'flex';
        banner.innerHTML = `
            <img src="${product.image || product.image_url || '/images/ocop-placeholder.png'}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="pin-thumb" alt="${escapeHtml(product.name)}">
            <div class="pin-info">
                <span class="pin-badge">🏷️ Đang giới thiệu</span>
                <div class="pin-title">${escapeHtml(product.name)}</div>
                <div class="pin-price">${product.price}</div>
            </div>
            <button type="button" onclick="openProductQuickView(${product.id}, event)" class="pin-buy-btn">
                🛒 Xem & Mua
            </button>
        `;
    }


    /**
     * Xử lý khi Host kết thúc Livestream
     */
    function handleStreamEnded(eventData) {
        const endedOverlay = document.getElementById('viewer-ended-overlay');
        if (endedOverlay) {
            endedOverlay.style.display = 'flex';
            const durationEl = document.getElementById('ended-duration');
            if (durationEl) durationEl.innerText = eventData.duration || '00:00';
            const likesEl = document.getElementById('ended-likes');
            if (likesEl) likesEl.innerText = (eventData.total_likes || 0).toLocaleString('vi-VN');
        }
        if (peerConnection) peerConnection.close();
    }

    function hideLoadingOverlay() {
        const el = document.getElementById('viewer-connecting-spinner');
        if (el) el.style.display = 'none';
    }

    function showReconnectingOverlay() {
        const el = document.getElementById('viewer-connecting-spinner');
        if (el) el.style.display = 'flex';
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    return {
        init,
        unmuteAndPlay,
        sendComment,
        sendReaction,
        renderFloatingReaction
    };
})();
