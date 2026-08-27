/**
 * DongAnhSocial - Livestream Host Studio Engine (WebRTC + Reverb Signaling)
 * Quản lý phát trực tiếp, Camera, Microphone, Screen Share, Live Chat, Pin OCOP & WebRTC Peer Connections
 */

window.DongAnhLiveHost = (function () {
    let streamId = null;
    let channelId = null;
    let mySessionId = 'host_' + Math.random().toString(36).substring(2, 9);
    let localStream = null;
    let screenStream = null;
    let isMicMuted = false;
    let isCameraOff = false;
    let isScreenSharing = false;
    let currentFacingMode = 'user'; // 'user' | 'environment'

    // PeerConnections map: viewerSessionId => RTCPeerConnection
    const peerConnections = new Map();
    const pendingIceCandidatesMap = new Map(); // viewerSessionId => [candidate, ...]

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

    let timerInterval = null;
    let startTimestamp = Date.now();

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Khởi tạo phòng Studio của Host
     */
    async function init(config) {
        streamId = config.streamId;
        channelId = config.channelId || config.streamId;
        console.log('[LiveHost] Initializing studio for stream #', streamId, 'Channel:', channelId, 'Session:', mySessionId);

        // 1. Khởi động Camera & Mic
        try {
            await startLocalMedia();
        } catch (err) {
            console.error('[LiveHost] Cannot access media devices:', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi thiết bị',
                    text: 'Không thể truy cập Camera/Microphone. Vui lòng cấp quyền trong cài đặt trình duyệt!'
                });
            }
        }

        // 2. Lắng nghe WebSocket Channel từ Laravel Echo / Reverb
        setupEchoListeners();

        // 3. Khởi động cơ chế dự phòng HTTP Signaling Fallback
        startHttpPollingFallback();

        // 4. Bắt đầu bộ đếm thời gian
        startTimer();

        // 5. Bắn tín hiệu Host Ready ngay lập tức
        sendSignal('all', 'host_ready', null);

        // Bắn định kỳ host_ready mỗi 3s để người xem vào sau kết nối tức thì
        setInterval(() => {
            sendSignal('all', 'host_ready', null);
        }, 3000);
    }

    let lastSignalTimestamp = 0;
    let pollingInterval = null;

    /**
     * Cơ chế dự phòng: Tự động kéo tín hiệu qua HTTP khi WebSocket bị gián đoạn
     */
    function startHttpPollingFallback() {
        if (pollingInterval) return;
        pollingInterval = setInterval(async () => {
            try {
                const res = await fetch(`/livestream/${streamId}/signals?session_id=${mySessionId}&is_host=1&since=${lastSignalTimestamp}`);
                const data = await res.json();
                if (data.status === 'success' && data.signals && data.signals.length > 0) {
                    for (const sig of data.signals) {
                        if (sig.timestamp > lastSignalTimestamp) {
                            lastSignalTimestamp = sig.timestamp;
                        }
                        console.log('[LiveHost] [HTTP Poll] Received signal:', sig.signal_type, 'from:', sig.sender_session_id);
                        if (sig.signal_type === 'viewer_join') {
                            handleViewerJoin(sig.sender_session_id);
                        } else if (sig.signal_type === 'viewer_answer') {
                            handleViewerAnswer(sig.sender_session_id, sig.signal_data);
                        } else if (sig.signal_type === 'ice_candidate') {
                            handleIceCandidate(sig.sender_session_id, sig.signal_data);
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
     * Lấy stream Camera + Mic
     */
    async function startLocalMedia() {
        const constraints = {
            audio: { echoCancellation: true, noiseSuppression: true },
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1280 },
                height: { ideal: 720 },
                frameRate: { ideal: 30 }
            }
        };

        if (localStream) {
            localStream.getTracks().forEach(t => t.stop());
        }

        localStream = await navigator.mediaDevices.getUserMedia(constraints);
        const videoElement = document.getElementById('host-preview-video');
        if (videoElement) {
            videoElement.srcObject = localStream;
        }

        // Tự động ghi hình phiên Live để lưu trữ và tải lên YouTube
        startRecording(localStream);

        // Cập nhật track tới tất cả viewers hiện có
        peerConnections.forEach((pc) => {
            const senders = pc.getSenders();
            localStream.getTracks().forEach(track => {
                const sender = senders.find(s => s.track && s.track.kind === track.kind);
                if (sender) {
                    sender.replaceTrack(track);
                } else {
                    pc.addTrack(track, localStream);
                }
            });
        });
    }

    /**
     * Lắng nghe các sự kiện Reverb / Echo
     */
    function setupEchoListeners() {
        if (!window.Echo) {
            console.warn('[LiveHost] Laravel Echo not ready yet. Retrying...');
            setTimeout(setupEchoListeners, 1000);
            return;
        }

        const channel = window.Echo.channel('live-stream.' + channelId);

        // 1. Tín hiệu WebRTC
        channel.listen('.LiveStreamSignal', async (e) => {
            if (e.target_session_id !== mySessionId && e.target_session_id !== 'all' && e.target_session_id !== 'host') {
                return;
            }

            console.log('[LiveHost] Received signal:', e.signal_type, 'from:', e.sender_session_id);

            if (e.signal_type === 'viewer_join') {
                handleViewerJoin(e.sender_session_id);
            } else if (e.signal_type === 'viewer_answer') {
                handleViewerAnswer(e.sender_session_id, e.signal_data);
            } else if (e.signal_type === 'ice_candidate') {
                handleIceCandidate(e.sender_session_id, e.signal_data);
            }
        });

        // 2. Bình luận mới
        channel.listen('.LiveStreamCommentSent', (e) => {
            appendComment(e);
        });

        // 3. Reaction thả tim
        channel.listen('.LiveStreamReactionSent', (e) => {
            renderFloatingReaction(e.reaction_type);
            const likesCountEl = document.getElementById('host-likes-count');
            if (likesCountEl && e.total_likes) {
                likesCountEl.innerText = Number(e.total_likes).toLocaleString('vi-VN');
            }
        });

        // 4. Đổi sản phẩm ghim
        channel.listen('.LiveStreamProductPinned', (e) => {
            updatePinnedProductUI(e.product);
        });

        // 5. Cập nhật số người xem định kỳ
        setInterval(syncViewerCount, 15000);
    }

    /**
     * Khi có Viewer mới tham gia -> Tạo RTCPeerConnection và gửi Offer
     */
    async function handleViewerJoin(viewerSessionId) {
        console.log('[LiveHost] Creating WebRTC peer connection for viewer:', viewerSessionId);

        if (peerConnections.has(viewerSessionId)) {
            try { peerConnections.get(viewerSessionId).close(); } catch(e) {}
            peerConnections.delete(viewerSessionId);
        }

        const pc = new RTCPeerConnection(rtcConfig);
        peerConnections.set(viewerSessionId, pc);
        pendingIceCandidatesMap.delete(viewerSessionId);

        // Thêm tracks vào PC
        const activeStream = screenStream || localStream;
        if (activeStream) {
            activeStream.getTracks().forEach(track => {
                try {
                    pc.addTrack(track, activeStream);
                } catch(e) {
                    console.warn('[LiveHost] addTrack error:', e);
                }
            });
        }

        // Bắt ICE candidate
        pc.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal(viewerSessionId, 'ice_candidate', JSON.stringify(event.candidate));
            }
        };

        pc.onconnectionstatechange = () => {
            console.log(`[LiveHost] Viewer ${viewerSessionId} connection state:`, pc.connectionState);
            if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed' || pc.connectionState === 'closed') {
                peerConnections.delete(viewerSessionId);
                pendingIceCandidatesMap.delete(viewerSessionId);
                updateOnlineViewerBadge();
            } else if (pc.connectionState === 'connected') {
                updateOnlineViewerBadge();
            }
        };

        pc.oniceconnectionstatechange = () => {
            console.log(`[LiveHost] Viewer ${viewerSessionId} ICE state:`, pc.iceConnectionState);
        };

        // Tạo SDP Offer
        try {
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);

            console.log('[LiveHost] Sending Host Offer to viewer:', viewerSessionId);
            sendSignal(viewerSessionId, 'host_offer', JSON.stringify(offer));
        } catch (err) {
            console.error('[LiveHost] Error creating offer for viewer:', err);
        }
    }

    /**
     * Nhận Answer từ Viewer
     */
    async function handleViewerAnswer(viewerSessionId, sdpData) {
        const pc = peerConnections.get(viewerSessionId);
        if (!pc) {
            console.warn('[LiveHost] No peer connection found for viewer answer:', viewerSessionId);
            return;
        }

        try {
            const answer = typeof sdpData === 'string' ? JSON.parse(sdpData) : sdpData;
            console.log('[LiveHost] Setting Remote Description for viewer:', viewerSessionId);
            await pc.setRemoteDescription(new RTCSessionDescription(answer));

            // Xả hàng đợi ICE candidate của viewer này nếu có
            const pending = pendingIceCandidatesMap.get(viewerSessionId) || [];
            while (pending.length > 0) {
                const cand = pending.shift();
                try {
                    await pc.addIceCandidate(new RTCIceCandidate(cand));
                } catch (e) {
                    console.warn('[LiveHost] Error draining ICE candidate for viewer:', viewerSessionId, e);
                }
            }
            pendingIceCandidatesMap.delete(viewerSessionId);
        } catch (err) {
            console.error('[LiveHost] Error setting remote description:', err);
        }
    }

    /**
     * Nhận ICE candidate từ Viewer
     */
    async function handleIceCandidate(viewerSessionId, candidateData) {
        const pc = peerConnections.get(viewerSessionId);

        try {
            const candidate = typeof candidateData === 'string' ? JSON.parse(candidateData) : candidateData;
            if (!candidate) return;

            if (!pc || !pc.remoteDescription || !pc.remoteDescription.type) {
                console.log('[LiveHost] Queuing ICE candidate for viewer:', viewerSessionId);
                if (!pendingIceCandidatesMap.has(viewerSessionId)) {
                    pendingIceCandidatesMap.set(viewerSessionId, []);
                }
                pendingIceCandidatesMap.get(viewerSessionId).push(candidate);
                return;
            }

            await pc.addIceCandidate(new RTCIceCandidate(candidate));
        } catch (err) {
            console.error('[LiveHost] Error adding ICE candidate:', err);
        }
    }

    /**
     * Gửi tín hiệu Signaling tới Backend
     */
    async function sendSignal(targetSessionId, signalType, signalData) {
        try {
            console.log('[LiveHost] Sending signal:', signalType, 'to:', targetSessionId);
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
                console.warn('[LiveHost] sendSignal response:', data);
            }
        } catch (err) {
            console.warn('[LiveHost] sendSignal failed:', err);
        }
    }

    /**
     * Bật / Tắt Microphone
     */
    function toggleMic() {
        if (!localStream) return;
        const audioTracks = localStream.getAudioTracks();
        if (audioTracks.length > 0) {
            isMicMuted = !isMicMuted;
            audioTracks[0].enabled = !isMicMuted;

            const btn = document.getElementById('btn-host-mic');
            if (btn) {
                btn.classList.toggle('active-muted', isMicMuted);
                btn.innerHTML = isMicMuted ? '🔇 <span class="btn-text">Bật Mic</span>' : '🎙️ <span class="btn-text">Tắt Mic</span>';
            }
        }
    }

    /**
     * Bật / Tắt Camera
     */
    function toggleCamera() {
        if (!localStream) return;
        const videoTracks = localStream.getVideoTracks();
        if (videoTracks.length > 0) {
            isCameraOff = !isCameraOff;
            videoTracks[0].enabled = !isCameraOff;

            const btn = document.getElementById('btn-host-camera');
            if (btn) {
                btn.classList.toggle('active-muted', isCameraOff);
                btn.innerHTML = isCameraOff ? '🚫 <span class="btn-text">Bật Cam</span>' : '📹 <span class="btn-text">Tắt Cam</span>';
            }
        }
    }

    /**
     * Đổi Camera Trước / Sau (Trên điện thoại / iPad)
     */
    async function switchCamera() {
        currentFacingMode = (currentFacingMode === 'user') ? 'environment' : 'user';
        await startLocalMedia();
    }

    /**
     * Chia sẻ màn hình (Screen Share)
     */
    async function toggleScreenShare() {
        const btn = document.getElementById('btn-host-screen');
        if (!isScreenSharing) {
            try {
                screenStream = await navigator.mediaDevices.getDisplayMedia({
                    video: true,
                    audio: true
                });

                const screenVideoTrack = screenStream.getVideoTracks()[0];
                const previewEl = document.getElementById('host-preview-video');
                if (previewEl) previewEl.srcObject = screenStream;

                // Thay thế track video gửi tới tất cả viewers
                peerConnections.forEach((pc) => {
                    const senders = pc.getSenders();
                    const videoSender = senders.find(s => s.track && s.track.kind === 'video');
                    if (videoSender) {
                        videoSender.replaceTrack(screenVideoTrack);
                    }
                });

                isScreenSharing = true;
                if (btn) {
                    btn.classList.add('active-active');
                    btn.innerHTML = '🛑 <span class="btn-text">Dừng Share</span>';
                }

                screenVideoTrack.onended = () => {
                    stopScreenShare();
                };
            } catch (err) {
                console.warn('[LiveHost] Screen share cancelled or error:', err);
            }
        } else {
            stopScreenShare();
        }
    }

    function stopScreenShare() {
        if (screenStream) {
            screenStream.getTracks().forEach(t => t.stop());
            screenStream = null;
        }

        const previewEl = document.getElementById('host-preview-video');
        if (previewEl && localStream) {
            previewEl.srcObject = localStream;
        }

        if (localStream) {
            const cameraTrack = localStream.getVideoTracks()[0];
            peerConnections.forEach((pc) => {
                const senders = pc.getSenders();
                const videoSender = senders.find(s => s.track && s.track.kind === 'video');
                if (videoSender && cameraTrack) {
                    videoSender.replaceTrack(cameraTrack);
                }
            });
        }

        isScreenSharing = false;
        const btn = document.getElementById('btn-host-screen');
        if (btn) {
            btn.classList.remove('active-active');
            btn.innerHTML = '🖥️ <span class="btn-text">Share Màn hình</span>';
        }
    }

    /**
     * Gửi bình luận từ Studio của Host
     */
    async function sendComment(messageText) {
        const text = (messageText || '').trim();
        if (!text) return;

        const inputEl = document.getElementById('host-comment-input');
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
            const data = await res.json();
            if (data.status === 'success') {
                appendComment(data.comment, true);
            }
        } catch (err) {
            console.error('[LiveHost] Failed to send comment:', err);
        }
    }

    /**
     * Thêm bình luận vào danh sách Live Chat
     */
    function appendComment(comment, isHost = false) {
        const chatBox = document.getElementById('host-chat-messages');
        if (!chatBox) return;

        const div = document.createElement('div');
        div.className = 'live-chat-item' + (isHost ? ' host-comment' : '');
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
     * Thêm sản phẩm vào giỏ hàng Live
     */
    async function addProduct(productId) {
        try {
            const res = await fetch(`/livestream/${streamId}/products`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ product_id: productId })
            });
            const data = await res.json();
            if (data.status === 'success') {
                updateCartUI(data.products, data.pinned_product);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã thêm vào giỏ hàng Live!',
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            }
        } catch (err) {
            console.error('[LiveHost] Add product failed:', err);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng Live
     */
    async function removeProduct(productId) {
        try {
            const res = await fetch(`/livestream/${streamId}/products/${productId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                }
            });
            const data = await res.json();
            if (data.status === 'success') {
                updateCartUI(data.products, data.pinned_product);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã xóa sản phẩm khỏi giỏ hàng Live!',
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            }
        } catch (err) {
            console.error('[LiveHost] Remove product failed:', err);
        }
    }

    /**
     * Cập nhật toàn bộ giao diện Giỏ hàng trong Host Studio
     */
    function updateCartUI(products = [], pinnedProduct = null) {
        const cartCountEl = document.getElementById('host-cart-count');
        const modalCountEl = document.getElementById('modal-cart-count');
        if (cartCountEl) cartCountEl.innerText = products.length;
        if (modalCountEl) modalCountEl.innerText = products.length;

        const listEl = document.getElementById('host-stream-products-list');
        if (listEl) {
            if (products.length === 0) {
                listEl.innerHTML = `
                    <div class="empty-cart-state" id="host-empty-cart-msg">
                        <span>🛍️</span>
                        <p>Chưa có sản phẩm nào trong giỏ hàng phiên live này.</p>
                        <button type="button" class="btn-switch-add" onclick="switchHostCartTab('all')">+ Thêm sản phẩm từ Kho</button>
                    </div>
                `;
            } else {
                listEl.innerHTML = products.map(prod => {
                    const isPinned = prod.is_pinned;
                    return `
                        <div class="pin-product-item ${isPinned ? 'selected' : ''}" id="host-prod-row-${prod.id}">
                            <img src="${prod.image_url || prod.image || '/images/ocop-placeholder.png'}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="pin-item-img" alt="${escapeHtml(prod.name)}">
                            <div class="pin-item-info">
                                <div class="pin-item-name">${escapeHtml(prod.name)}</div>
                                <div class="pin-item-price">${prod.price}</div>
                                ${isPinned ? '<span class="badge-currently-pinned">🔥 Đang ghim trên màn hình</span>' : ''}
                            </div>
                            <div class="pin-item-actions">
                                ${isPinned ? 
                                    `<button type="button" class="btn-pin-action unpin" onclick="DongAnhLiveHost.pinProduct(null)">✕ Bỏ ghim</button>` : 
                                    `<button type="button" class="btn-pin-action pin" onclick="DongAnhLiveHost.pinProduct(${prod.id})">📌 Ghim lên live</button>`
                                }
                                <button type="button" class="btn-pin-action remove" onclick="DongAnhLiveHost.removeProduct(${prod.id})" title="Xóa khỏi giỏ live">🗑️</button>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Cập nhật trạng thái các nút ở Tab 2 (Kho OCOP)
        const allRows = document.querySelectorAll('#host-all-products-list .pin-product-item');
        const activeIds = new Set(products.map(p => Number(p.id)));
        allRows.forEach(row => {
            const rowIdStr = row.id.replace('all-prod-row-', '');
            const rowId = Number(rowIdStr);
            const actionContainer = row.querySelector('.pin-item-actions');
            if (activeIds.has(rowId)) {
                row.classList.add('in-live');
                if (actionContainer) {
                    actionContainer.innerHTML = '<button type="button" class="btn-pin-action added" disabled>✓ Đã có trong live</button>';
                }
            } else {
                row.classList.remove('in-live');
                if (actionContainer) {
                    actionContainer.innerHTML = `<button type="button" class="btn-pin-action add-to-live" onclick="DongAnhLiveHost.addProduct(${rowId})">➕ Gắn vào Live</button>`;
                }
            }
        });

        // Cập nhật banner ghim trên màn hình
        updatePinnedProductUI(pinnedProduct);
    }

    /**
     * Ghim sản phẩm OCOP vào Livestream
     */
    async function pinProduct(productId) {
        try {
            const res = await fetch(`/livestream/${streamId}/pin-product`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ product_id: productId })
            });
            const data = await res.json();
            if (data.status === 'success') {
                updateCartUI(data.products || [], data.pinned_product || data.product);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: productId ? 'Đã ghim sản phẩm!' : 'Đã bỏ ghim sản phẩm',
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            }
        } catch (err) {
            console.error('[LiveHost] Pin product failed:', err);
        }
    }

    /**
     * Cập nhật thẻ sản phẩm ghim trên UI
     */
    function updatePinnedProductUI(product) {
        const pinContainer = document.getElementById('host-pinned-product-container');
        if (!pinContainer) return;

        if (!product) {
            pinContainer.style.display = 'none';
            return;
        }

        pinContainer.style.display = 'flex';
        pinContainer.innerHTML = `
            <img src="${product.image || product.image_url || '/images/ocop-placeholder.png'}" onerror="this.onerror=null; this.src='/images/ocop-placeholder.png';" class="pin-thumb" alt="${escapeHtml(product.name)}">
            <div class="pin-info">
                <span class="pin-badge">🏷️ Đang giới thiệu</span>
                <div class="pin-title">${escapeHtml(product.name)}</div>
                <div class="pin-price">${product.price}</div>
            </div>
            <button class="pin-unpin-btn" onclick="DongAnhLiveHost.pinProduct(null)">✕ Bỏ ghim</button>
        `;
    }


    /**
     * Hiệu ứng Particle Floating Reaction bay lên
     */
    function renderFloatingReaction(type = 'heart') {
        const container = document.getElementById('host-reaction-layer');
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
     * Cập nhật số người xem & gửi lên server
     */
    function updateOnlineViewerBadge() {
        const count = Math.max(1, peerConnections.size + 1);
        const badge = document.getElementById('host-viewer-count');
        if (badge) badge.innerText = count;
    }

    async function syncViewerCount() {
        const count = Math.max(1, peerConnections.size + 1);
        try {
            await fetch(`/livestream/${streamId}/viewer-count`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ count: count })
            });
        } catch (_) {}
    }

    /**
     * Đếm thời gian Livestream
     */
    function startTimer() {
        const timerEl = document.getElementById('host-stream-timer');
        timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - startTimestamp) / 1000);
            const hrs = Math.floor(elapsed / 3600);
            const mins = Math.floor((elapsed % 3600) / 60);
            const secs = elapsed % 60;

            if (timerEl) {
                if (hrs > 0) {
                    timerEl.innerText = `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
                } else {
                    timerEl.innerText = `${pad(mins)}:${pad(secs)}`;
                }
            }
        }, 1000);
    }

    function pad(n) {
        return n < 10 ? '0' + n : n;
    }

    let mediaRecorder = null;
    let recordedChunks = [];

    /**
     * Tự động ghi hình phiên Livestream (MediaRecorder)
     */
    function startRecording(stream) {
        if (!window.MediaRecorder || !stream) return;
        try {
            recordedChunks = [];
            const mimeTypes = [
                'video/webm;codecs=vp9,opus',
                'video/webm;codecs=vp8,opus',
                'video/webm',
                'video/mp4'
            ];
            const mime = mimeTypes.find(m => MediaRecorder.isTypeSupported(m)) || '';
            mediaRecorder = new MediaRecorder(stream, mime ? { mimeType: mime } : {});
            mediaRecorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                    recordedChunks.push(event.data);
                }
            };
            mediaRecorder.start(2500);
            console.log('[LiveHost] MediaRecorder started with mime:', mime);
        } catch (e) {
            console.warn('[LiveHost] MediaRecorder error:', e);
        }
    }

    /**
     * Kết thúc Livestream & Tải video lên YouTube
     */
    async function endStream() {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: 'Kết thúc Livestream?',
                text: 'Bạn có chắc chắn muốn dừng phiên phát sóng trực tiếp này?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '🔴 Dừng phát sóng',
                cancelButtonText: 'Tiếp tục Live'
            });
            if (!result.isConfirmed) return;
        }

        // Hiển thị thông báo đang hoàn tất
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Đang hoàn tất...',
                html: '<div style="font-size:0.95rem; color:#64748b; margin-top:8px;">Vui lòng chờ trong giây lát...</div>',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        // 1. Dừng ghi hình và chuẩn bị tệp video
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            try { mediaRecorder.stop(); } catch(e) {}
        }

        let ytResultData = null;
        if (recordedChunks.length > 0) {
            try {
                const mimeType = mediaRecorder ? (mediaRecorder.mimeType || 'video/webm') : 'video/webm';
                const blob = new Blob(recordedChunks, { type: mimeType });
                const ext = mimeType.includes('mp4') ? 'mp4' : 'webm';
                const file = new File([blob], `live_record_${streamId}_${Date.now()}.${ext}`, { type: mimeType });

                const formData = new FormData();
                formData.append('recording', file);

                console.log('[LiveHost] Uploading recorded stream video (' + (file.size / (1024*1024)).toFixed(2) + ' MB)...');
                const uploadRes = await fetch(`/livestream/${streamId}/upload-recording`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrf()
                    },
                    body: formData
                });
                ytResultData = await uploadRes.json();
                console.log('[LiveHost] Video upload completed:', ytResultData);
            } catch (uploadErr) {
                console.warn('[LiveHost] Error uploading recording:', uploadErr);
            }
        }

        try {
            const res = await fetch(`/livestream/${streamId}/end`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                }
            });
            const data = await res.json();

            // Dừng camera & mic
            if (localStream) localStream.getTracks().forEach(t => t.stop());
            if (screenStream) screenStream.getTracks().forEach(t => t.stop());
            peerConnections.forEach(pc => pc.close());
            if (timerInterval) clearInterval(timerInterval);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '🎉 Phiên Livestream hoàn tất!',
                    html: `
                        <div style="text-align: left; padding: 12px; font-size: 0.95rem; line-height: 1.8;">
                            <div>⏱️ <b>Thời lượng:</b> ${data.duration || '00:00'}</div>
                            <div>👥 <b>Lượt xem cao nhất:</b> ${data.peak_viewers || 1} người</div>
                            <div>❤️ <b>Tổng lượt tương tác:</b> ${data.total_likes || 0} lượt</div>
                        </div>
                    `,
                    confirmButtonText: 'Về trang Quản lý Live',
                    confirmButtonColor: '#0ea5e9'
                }).then(() => {
                    window.location.href = '/livestream';
                });
            } else {
                window.location.href = '/livestream';
            }
        } catch (err) {
            console.error('[LiveHost] Failed to end stream:', err);
            window.location.href = '/livestream';
        }
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    return {
        init,
        toggleMic,
        toggleCamera,
        switchCamera,
        toggleScreenShare,
        sendComment,
        pinProduct,
        addProduct,
        removeProduct,
        updateCartUI,
        renderFloatingReaction,
        endStream
    };
})();

