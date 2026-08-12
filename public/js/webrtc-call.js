/**
 * WebRTC P2P Call Client for DongAnhSocial
 * Sử dụng simple-peer library để kết nối P2P trực tiếp.
 * Laravel Reverb (WebSocket) chỉ dùng để trao đổi tín hiệu (signaling).
 * v2.0 — simple-peer based (auto SDP handling)
 */

window.DongAnhWebRTC = (function () {
    let peer = null; // SimplePeer instance
    let localStream = null;
    let remoteStream = null;

    let currentCallId = null;
    let targetUserId = null;
    let targetUserName = '';
    let targetUserAvatar = '';
    let isCaller = false;
    let callType = 'audio'; // 'audio' | 'video'
    let callDurationTimer = null;
    let callSeconds = 0;

    let audioContext = null;
    let ringtoneInterval = null;

    const rtcConfig = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
            { urls: 'stun:stun3.l.google.com:19302' },
            { urls: 'stun:stun4.l.google.com:19302' }
        ]
    };

    // --- CSRF Helper ---
    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    let pollCallInterval = null;

    function startPendingCallPolling() {
        if (pollCallInterval) return;
        pollCallInterval = setInterval(async () => {
            if (peer || currentCallId) return; // Nếu đang trong cuộc gọi thì bỏ qua
            try {
                const res = await fetch('/social/call/pending', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrf()
                    }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.has_call && !peer && !currentCallId) {
                    console.log('[WebRTC Polling] Phát hiện cuộc gọi đến:', data);
                    currentCallId = data.call_id;
                    targetUserId = data.caller_id;
                    targetUserName = data.caller_name;
                    targetUserAvatar = data.caller_avatar;
                    callType = data.call_type || 'audio';
                    isCaller = false;

                    if (data.signal_data) {
                        window._pendingSignalData = data.signal_data;
                    }

                    showIncomingModal(targetUserName, targetUserAvatar, callType);
                    playRingtone(false);
                }
            } catch (_) {}
        }, 2000);
    }

    let callerStatusPollInterval = null;

    function startCallerStatusPolling(callId) {
        if (callerStatusPollInterval) clearInterval(callerStatusPollInterval);
        callerStatusPollInterval = setInterval(async () => {
            if (!currentCallId || currentCallId !== callId) {
                clearInterval(callerStatusPollInterval);
                callerStatusPollInterval = null;
                return;
            }
            try {
                const res = await fetch('/social/call/status/' + callId, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status === 'answered' && data.signal_data) {
                    console.log('[WebRTC Caller Polling] Cuộc gọi đã được chấp nhận! Nhận SDP Answer:', data);
                    clearInterval(callerStatusPollInterval);
                    callerStatusPollInterval = null;

                    let answerSignal = data.signal_data;
                    if (typeof answerSignal === 'string') {
                        try { answerSignal = JSON.parse(answerSignal); } catch (_) {}
                    }
                    if (peer && answerSignal) {
                        try { peer.signal(answerSignal); } catch (e) { console.warn('peer.signal answer err:', e); }
                    }

                    stopRingtone();
                    hideOutgoingModal();
                    showActiveCallOverlay(targetUserName, targetUserAvatar, callType);
                    startDurationCounter();
                } else if (data.status === 'rejected' || data.status === 'ended') {
                    console.log('[WebRTC Caller Polling] Cuộc gọi bị từ chối hoặc kết thúc.');
                    clearInterval(callerStatusPollInterval);
                    callerStatusPollInterval = null;
                    stopRingtone();
                    hideOutgoingModal();
                    showToast('Cuộc gọi đã bị từ chối.');
                    cleanupCall();
                }
            } catch (_) {}
        }, 1500);
    }

    // --- INIT (Lắng nghe WebSocket + Polling Fallback) ---
    function init(userId) {
        startPendingCallPolling();

        if (!window.Echo) {
            console.warn('[WebRTC] Laravel Echo chưa sẵn sàng, chờ 1s...');
            setTimeout(() => init(userId), 1000);
            return;
        }

        console.log('[WebRTC] Lắng nghe cuộc gọi trên channel private: call.' + userId);

        try {
            const ch = window.Echo.private('call.' + userId);

            ch.listen('.CallOffer', (e) => {
                console.log('[WebRTC] Nhận CallOffer:', e);
                handleIncomingCall(e);
            });

            // Nhận signal data (SDP answer hoặc ICE candidates) từ đối phương
            ch.listen('.CallSignal', (e) => {
                console.log('[WebRTC] Nhận CallSignal:', e);
                handleRemoteSignal(e);
            });

            ch.listen('.CallHangup', (e) => {
                console.log('[WebRTC] Nhận CallHangup:', e);
                handleRemoteHangup(e);
            });
        } catch (err) {
            console.warn('[WebRTC] Echo listen warning:', err);
        }
    }

    /**
     * Lấy media stream (Micro / Camera) với cơ chế fallback tự động:
     * HD Video -> Basic Video -> Audio-only (nếu camera bị bận hoặc lỗi)
     */
    async function getLocalMediaStream(requestedType) {
        if (requestedType === 'video') {
            try {
                return await navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: { width: { ideal: 1280 }, height: { ideal: 720 } }
                });
            } catch (e1) {
                console.warn('[WebRTC] HD Video failed, fallback to basic video:', e1);
                try {
                    return await navigator.mediaDevices.getUserMedia({
                        audio: true,
                        video: true
                    });
                } catch (e2) {
                    console.warn('[WebRTC] Camera unavailable, fallback to audio call:', e2);
                    showToast('📷 Camera không khả dụng (đang bị dùng bởi app khác hoặc bị bận). Đã chuyển sang cuộc gọi thoại.');
                    callType = 'audio';
                    return await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                }
            }
        } else {
            return await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
        }
    }

    /**
     * Bắt đầu cuộc gọi tới người dùng khác
     */
    async function startCall(receiverId, receiverName, receiverAvatar, type = 'audio') {
        if (peer || currentCallId) {
            alert('Bạn đang trong một cuộc gọi khác.');
            return;
        }

        callType = type;
        targetUserId = receiverId;
        targetUserName = receiverName || 'Người dùng';
        targetUserAvatar = receiverAvatar || '👤';

        showOutgoingModal(receiverName, receiverAvatar, callType);
        playRingtone(true);

        try {
            localStream = await getLocalMediaStream(callType);
            setLocalStreamUI(localStream, callType);

            // Tạo SimplePeer (initiator = true = Caller)
            peer = new SimplePeer({
                initiator: true,
                trickle: true,
                stream: localStream,
                config: rtcConfig
            });

            // simple-peer tự tạo SDP Offer + ICE candidates → gửi qua signal event
            peer.on('signal', async (signalData) => {
                console.log('[WebRTC] Signal gửi đi (caller):', signalData.type || 'candidate');

                if (signalData.type === 'offer') {
                    // Gửi SDP Offer tới server → broadcast tới receiver
                    const res = await fetch('/social/call/initiate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrf()
                        },
                        body: JSON.stringify({
                            receiver_id: receiverId,
                            type: callType,
                            signal_data: JSON.stringify(signalData)
                        })
                    });

                    if (!res.ok) {
                        const errText = await res.text();
                        let errMsg = 'Không thể kết nối (' + res.status + ')';
                        try {
                            const errJson = JSON.parse(errText);
                            if (errJson.message) errMsg = errJson.message;
                        } catch(e) {
                            if (res.status === 401) errMsg = 'Vui lòng đăng nhập lại.';
                        }
                        throw new Error(errMsg);
                    }

                    const data = await res.json();
                    if (data.status === 'success') {
                        currentCallId = data.call_id;
                        startCallerStatusPolling(data.call_id);
                    } else {
                        throw new Error(data.message || 'Lỗi khởi tạo cuộc gọi');
                    }
                } else {
                    // ICE candidate → gửi qua signal endpoint
                    if (currentCallId) {
                        sendSignal(currentCallId, targetUserId, signalData);
                    }
                }
            });

            setupPeerEvents();

        } catch (err) {
            console.error('[WebRTC] Error starting call:', err);
            let msg = err.message || 'Lỗi không xác định';
            if (err.name === 'NotAllowedError' || msg.includes('Permission denied')) {
                msg = 'Trình duyệt chưa được cấp quyền truy cập Camera/Microphone. Vui lòng cho phép quyền trong cài đặt trang web trên trình duyệt và thử lại!';
            }
            alert('Không thể bắt đầu cuộc gọi: ' + msg);
            cleanupCall();
        }
    }

    // --- NHẬN CUỘC GỌI ĐẾN ---
    function handleIncomingCall(e) {
        if (peer || currentCallId) {
            sendHangup(e.call_id, e.caller_id, 'busy');
            return;
        }

        currentCallId = e.call_id;
        targetUserId = e.caller_id;
        targetUserName = e.caller_name;
        targetUserAvatar = e.caller_avatar;
        callType = e.type;
        isCaller = false;

        // Lưu signal data (SDP Offer) để dùng khi acceptCall
        window._pendingSignalData = JSON.parse(e.signal_data);

        showIncomingModal(targetUserName, targetUserAvatar, callType);
        playRingtone(false);
    }

    // --- CHẤP NHẬN CUỘC GỌI ---
    async function acceptCall() {
        stopRingtone();
        hideIncomingModal();

        try {
            localStream = await getLocalMediaStream(callType);
            setLocalStreamUI(localStream, callType);

            // Tạo SimplePeer (initiator = false = Receiver)
            peer = new SimplePeer({
                initiator: false,
                trickle: true,
                stream: localStream,
                config: rtcConfig
            });

            // simple-peer sinh SDP Answer + ICE candidates
            peer.on('signal', (signalData) => {
                console.log('[WebRTC] Signal gửi đi (receiver):', signalData.type || 'candidate');
                if (currentCallId) {
                    sendSignal(currentCallId, targetUserId, signalData);
                }
            });

            setupPeerEvents();

            // Nhập SDP Offer từ Caller → simple-peer tự tạo Answer
            let offerSignal = window._pendingSignalData;
            if (typeof offerSignal === 'string') {
                try {
                    offerSignal = JSON.parse(offerSignal);
                } catch (_) {}
            }
            if (offerSignal) {
                peer.signal(offerSignal);
            }
            window._pendingSignalData = null;

            showActiveCallOverlay(targetUserName, targetUserAvatar, callType);
            startDurationCounter();

        } catch (err) {
            console.error('[WebRTC] Error accepting call:', err);
            let msg = err.message || 'Lỗi không xác định';
            if (err.name === 'NotAllowedError' || msg.includes('Permission denied')) {
                msg = 'Trình duyệt chưa được cấp quyền truy cập Camera/Microphone. Vui lòng cho phép quyền trong cài đặt trang web trên trình duyệt và thử lại!';
            }
            alert('Không thể kết nối cuộc gọi: ' + msg);
            hangup('ended');
        }
    }

    // --- TỪ CHỐI CUỘC GỌI ---
    function rejectCall() {
        stopRingtone();
        hideIncomingModal();
        if (currentCallId && targetUserId) {
            sendHangup(currentCallId, targetUserId, 'rejected');
        }
        cleanupCall();
    }

    // --- NHẬN SIGNAL TỪ ĐỐI PHƯƠNG (SDP Answer hoặc ICE candidates) ---
    function handleRemoteSignal(e) {
        if (!peer || e.call_id !== currentCallId) return;
        try {
            const signalData = typeof e.signal_data === 'string' ? JSON.parse(e.signal_data) : e.signal_data;
            console.log('[WebRTC] Áp dụng remote signal:', signalData.type || 'candidate');
            peer.signal(signalData);

            // Nếu là answer → caller nhận được, chuyển sang overlay active
            if (signalData.type === 'answer' && isCaller) {
                stopRingtone();
                hideOutgoingModal();
                showActiveCallOverlay(targetUserName, targetUserAvatar, callType);
                startDurationCounter();
            }
        } catch (err) {
            console.error('[WebRTC] Error handling remote signal:', err);
        }
    }

    // --- ĐỐI PHƯƠNG CÚP MÁY ---
    function handleRemoteHangup(e) {
        if (e.call_id === currentCallId) {
            let msg = 'Cuộc gọi đã kết thúc.';
            if (e.reason === 'rejected') msg = 'Người nhận đã từ chối cuộc gọi.';
            if (e.reason === 'busy') msg = 'Người nhận đang bận.';
            showToast(msg);
            cleanupCall();
        }
    }

    // --- CÚP MÁY ---
    function hangup(reason) {
        stopRingtone();
        if (currentCallId && targetUserId) {
            sendHangup(currentCallId, targetUserId, reason || 'ended');
        }
        cleanupCall();
    }

    // --- SETUP PEER EVENTS ---
    function setupPeerEvents() {
        peer.on('stream', (stream) => {
            console.log('[WebRTC] Nhận remote stream!');
            remoteStream = stream;
            setRemoteStreamUI(stream);
        });

        peer.on('connect', () => {
            console.log('[WebRTC] ✅ P2P Connection Established!');
        });

        peer.on('error', (err) => {
            console.error('[WebRTC] Peer Error:', err);
            showToast('Lỗi kết nối: ' + err.message);
        });

        peer.on('close', () => {
            console.log('[WebRTC] Peer Connection Closed.');
            cleanupCall();
        });
    }

    // --- GỬI SIGNAL DATA QUA SERVER ---
    async function sendSignal(callId, targetId, signalData) {
        try {
            await fetch('/social/call/signal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({
                    call_id: callId,
                    target_user_id: targetId,
                    signal_data: JSON.stringify(signalData)
                })
            });
        } catch (err) {
            console.error('[WebRTC] Error sending signal:', err);
        }
    }

    // --- GỬI HANGUP ---
    async function sendHangup(callId, targetId, reason) {
        try {
            await fetch('/social/call/hangup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({
                    call_id: callId,
                    target_user_id: targetId,
                    reason: reason
                })
            });
        } catch (err) {
            console.error('[WebRTC] Error sending hangup:', err);
        }
    }

    // --- BẬT/TẮT MICRO ---
    function toggleMute() {
        if (localStream) {
            const audioTrack = localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                const btn = document.getElementById('webrtc-mute-btn');
                if (btn) {
                    btn.classList.toggle('muted', !audioTrack.enabled);
                    btn.innerHTML = audioTrack.enabled ? '🎤 Mute' : '🎙️ Unmute';
                }
            }
        }
    }

    // --- BẬT/TẮT CAMERA ---
    function toggleVideo() {
        if (localStream) {
            const videoTrack = localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = !videoTrack.enabled;
                const btn = document.getElementById('webrtc-video-btn');
                if (btn) {
                    btn.classList.toggle('off', !videoTrack.enabled);
                    btn.innerHTML = videoTrack.enabled ? '📹 Cam Off' : '📷 Cam On';
                }
            }
        }
    }

    // --- DỌN DẸP ---
    function cleanupCall() {
        stopRingtone();
        stopDurationCounter();

        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }

        if (remoteStream) {
            remoteStream.getTracks().forEach(track => track.stop());
            remoteStream = null;
        }

        if (peer) {
            peer.destroy();
            peer = null;
        }

        if (callerStatusPollInterval) {
            clearInterval(callerStatusPollInterval);
            callerStatusPollInterval = null;
        }

        currentCallId = null;
        targetUserId = null;
        targetUserName = '';
        targetUserAvatar = '';
        isCaller = false;
        window._pendingSignalData = null;

        hideIncomingModal();
        hideOutgoingModal();
        hideActiveCallOverlay();
    }

    // --- RINGTONE SYNTHESIZER & NOTIFICATIONS ---
    let originalDocumentTitle = document.title;
    let titleBlinkInterval = null;

    function playRingtone(isOutgoing) {
        stopRingtone();

        if (!isOutgoing) {
            originalDocumentTitle = document.title;
            let blink = false;
            titleBlinkInterval = setInterval(() => {
                document.title = blink ? '📞 CUỘC GỌI ĐẾN...' : '🔔 (1) Cuộc gọi điện thoại mới!';
                blink = !blink;
            }, 800);

            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('📞 Cuộc gọi tới từ ' + targetUserName, {
                    body: callType === 'video' ? 'Cuộc gọi Video P2P' : 'Cuộc gọi Thoại P2P',
                    icon: targetUserAvatar || '/favicon.ico',
                    requireInteraction: true
                });
            } else if ('Notification' in window && Notification.permission !== 'denied') {
                Notification.requestPermission();
            }
        }

        try {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            if (audioContext.state === 'suspended') audioContext.resume().catch(() => {});

            if (isOutgoing) {
                ringtoneInterval = setInterval(() => {
                    if (!audioContext || audioContext.state === 'closed') return;
                    if (audioContext.state === 'suspended') audioContext.resume();
                    const now = audioContext.currentTime;
                    const osc1 = audioContext.createOscillator();
                    const osc2 = audioContext.createOscillator();
                    const gain = audioContext.createGain();
                    osc1.type = 'sine'; osc1.frequency.setValueAtTime(440, now);
                    osc2.type = 'sine'; osc2.frequency.setValueAtTime(480, now);
                    gain.gain.setValueAtTime(0.08, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 1.8);
                    osc1.connect(gain); osc2.connect(gain); gain.connect(audioContext.destination);
                    osc1.start(now); osc2.start(now); osc1.stop(now + 1.8); osc2.stop(now + 1.8);
                }, 3000);
            } else {
                const playDoubleBurst = () => {
                    if (!audioContext || audioContext.state === 'closed') return;
                    if (audioContext.state === 'suspended') audioContext.resume();
                    const now = audioContext.currentTime;
                    [0, 0.2].forEach(offset => {
                        const t = now + offset;
                        const osc1 = audioContext.createOscillator();
                        const osc2 = audioContext.createOscillator();
                        const gain = audioContext.createGain();
                        osc1.type = 'triangle'; osc1.frequency.setValueAtTime(880, t);
                        osc2.type = 'sine'; osc2.frequency.setValueAtTime(1046.5, t);
                        gain.gain.setValueAtTime(0.2, t);
                        gain.gain.exponentialRampToValueAtTime(0.001, t + 0.15);
                        osc1.connect(gain); osc2.connect(gain); gain.connect(audioContext.destination);
                        osc1.start(t); osc2.start(t); osc1.stop(t + 0.15); osc2.stop(t + 0.15);
                    });
                };
                playDoubleBurst();
                ringtoneInterval = setInterval(playDoubleBurst, 1500);
            }
        } catch (e) {
            console.warn('[WebRTC] Ringtone sound disabled:', e);
        }
    }

    function stopRingtone() {
        if (titleBlinkInterval) { clearInterval(titleBlinkInterval); titleBlinkInterval = null; document.title = originalDocumentTitle; }
        if (ringtoneInterval) { clearInterval(ringtoneInterval); ringtoneInterval = null; }
        if (audioContext) { audioContext.close().catch(() => {}); audioContext = null; }
    }

    // --- DURATION COUNTER ---
    function startDurationCounter() {
        callSeconds = 0;
        const timerEl = document.getElementById('webrtc-call-timer');
        if (timerEl) timerEl.innerText = '00:00';
        callDurationTimer = setInterval(() => {
            callSeconds++;
            const mins = String(Math.floor(callSeconds / 60)).padStart(2, '0');
            const secs = String(callSeconds % 60).padStart(2, '0');
            if (timerEl) timerEl.innerText = `${mins}:${secs}`;
        }, 1000);
    }

    function stopDurationCounter() {
        if (callDurationTimer) { clearInterval(callDurationTimer); callDurationTimer = null; }
    }

    // --- UI HELPERS ---
    function setLocalStreamUI(stream, type) {
        const videoEl = document.getElementById('webrtc-local-video');
        if (videoEl) videoEl.srcObject = stream;
    }

    function setRemoteStreamUI(stream) {
        const videoEl = document.getElementById('webrtc-remote-video');
        const audioEl = document.getElementById('webrtc-remote-audio');
        if (videoEl) videoEl.srcObject = stream;
        if (audioEl) audioEl.srcObject = stream;
    }

    function setAvatarSrc(imgElem, avatar) {
        if (!imgElem) return;
        if (!avatar || !avatar.startsWith('http') && !avatar.startsWith('/') && !avatar.startsWith('data:')) {
            imgElem.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100%" height="100%" fill="%23e2e8f0"/><text x="50%" y="55%" font-size="50" text-anchor="middle" dominant-baseline="middle">👤</text></svg>';
        } else {
            imgElem.src = avatar;
        }
    }

    function showIncomingModal(name, avatar, type) {
        const modal = document.getElementById('webrtc-incoming-modal');
        if (!modal) return;
        document.getElementById('webrtc-incoming-name').innerText = name;
        setAvatarSrc(document.getElementById('webrtc-incoming-avatar'), avatar);
        document.getElementById('webrtc-incoming-type').innerText = type === 'video' ? '📹 Cuộc gọi video tới' : '📞 Cuộc gọi thoại tới';
        modal.style.display = 'flex';
    }

    function hideIncomingModal() {
        const modal = document.getElementById('webrtc-incoming-modal');
        if (modal) modal.style.display = 'none';
    }

    function showOutgoingModal(name, avatar, type) {
        const modal = document.getElementById('webrtc-outgoing-modal');
        if (!modal) return;
        document.getElementById('webrtc-outgoing-name').innerText = name;
        setAvatarSrc(document.getElementById('webrtc-outgoing-avatar'), avatar);
        document.getElementById('webrtc-outgoing-type').innerText = type === 'video' ? 'Đang gọi video...' : 'Đang gọi thoại...';
        modal.style.display = 'flex';
    }

    function hideOutgoingModal() {
        const modal = document.getElementById('webrtc-outgoing-modal');
        if (modal) modal.style.display = 'none';
    }

    function showActiveCallOverlay(name, avatar, type) {
        const overlay = document.getElementById('webrtc-active-overlay');
        if (!overlay) return;
        document.getElementById('webrtc-active-name').innerText = name;
        setAvatarSrc(document.getElementById('webrtc-active-avatar'), avatar);
        const videoContainer = document.getElementById('webrtc-video-container');
        if (videoContainer) videoContainer.style.display = type === 'video' ? 'block' : 'none';
        const videoBtn = document.getElementById('webrtc-video-btn');
        if (videoBtn) videoBtn.style.display = type === 'video' ? 'inline-flex' : 'none';
        overlay.style.display = 'flex';
    }

    function hideActiveCallOverlay() {
        const overlay = document.getElementById('webrtc-active-overlay');
        if (overlay) overlay.style.display = 'none';
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'webrtc-toast';
        toast.innerText = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 50);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
    }

    return {
        init,
        startCall,
        acceptCall,
        rejectCall,
        hangup,
        toggleMute,
        toggleVideo
    };
})();
