/**
 * WebRTC P2P Call Client for DongAnhSocial
 * Directly connects two devices for zero latency video/audio calling.
 * Uses Laravel Reverb (WebSocket) as the signaling server for initial SDP & ICE candidate exchange.
 */

window.DongAnhWebRTC = (function () {
    let peerConnection = null;
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

    /**
     * Khởi tạo WebRTC Call listener với Laravel Echo
     */
    function init(userId) {
        if (!window.Echo) {
            console.warn('[WebRTC] Laravel Echo chưa được khởi tạo. Đợi 1 giây...');
            setTimeout(() => init(userId), 1000);
            return;
        }

        console.log('[WebRTC] Lắng nghe cuộc gọi trên channel private: call.' + userId);

        const callChannel = window.Echo.private(`call.${userId}`);
        
        callChannel.listen('.CallOffer', (e) => {
            console.log('[WebRTC] Nhận CallOffer:', e);
            handleIncomingCall(e);
        });
        
        callChannel.listen('.CallAnswer', (e) => {
            console.log('[WebRTC] Nhận CallAnswer:', e);
            handleCallAnswer(e);
        });
        
        callChannel.listen('.IceCandidate', (e) => {
            console.log('[WebRTC] Nhận IceCandidate:', e);
            handleRemoteIceCandidate(e);
        });
        
        callChannel.listen('.CallHangup', (e) => {
            console.log('[WebRTC] Nhận CallHangup:', e);
            handleRemoteHangup(e);
        });
    }

    /**
     * Bắt đầu cuộc gọi tới người dùng khác
     */
    async function startCall(receiverId, receiverName, receiverAvatar, type = 'audio') {
        if (peerConnection) {
            alert('Bạn đang trong một cuộc gọi khác.');
            return;
        }

        callType = type;
        targetUserId = receiverId;
        targetUserName = receiverName || 'Người dùng';
        targetUserAvatar = receiverAvatar || '👤';
        isCaller = true;

        showOutgoingModal(targetUserName, targetUserAvatar, callType);
        playRingtone(true); // Nhạc chuông gọi đi

        try {
            // Lấy media từ mic/cam
            const constraints = {
                audio: true,
                video: callType === 'video' ? { width: { ideal: 1280 }, height: { ideal: 720 } } : false
            };
            localStream = await navigator.mediaDevices.getUserMedia(constraints);
            setLocalStreamUI(localStream, callType);

            // Tạo RTCPeerConnection
            createPeerConnection();

            // Add local tracks to PeerConnection
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });

            // Tạo SDP Offer
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);

            // Gửi Offer tới server qua API
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/social/call/initiate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    receiver_id: receiverId,
                    type: callType,
                    sdp_offer: { type: offer.type, sdp: offer.sdp }
                })
            });

            if (!res.ok) {
                const errText = await res.text();
                let errMsg = 'Không thể kết nối máy chủ (' + res.status + ')';
                try {
                    const errJson = JSON.parse(errText);
                    if (errJson.message) errMsg = errJson.message;
                } catch(e) {
                    if (res.status === 401) errMsg = 'Vui lòng đăng nhập lại để gọi điện.';
                }
                throw new Error(errMsg);
            }

            const data = await res.json();
            if (data.status === 'success') {
                currentCallId = data.call_id;
            } else {
                throw new Error(data.message || 'Khởi tạo cuộc gọi thất bại');
            }
        } catch (err) {
            console.error('[WebRTC] Error starting call:', err);
            alert('Không thể bắt đầu cuộc gọi: ' + err.message);
            cleanupCall();
        }
    }

    /**
     * Xử lý khi nhận cuộc gọi đến (CallOffer)
     */
    function handleIncomingCall(e) {
        if (peerConnection || currentCallId) {
            // Đang bận
            sendHangup(e.call_id, e.caller_id, 'busy');
            return;
        }

        currentCallId = e.call_id;
        targetUserId = e.caller_id;
        targetUserName = e.caller_name;
        targetUserAvatar = e.caller_avatar;
        callType = e.type;
        isCaller = false;
        window.pendingSdpOffer = e.sdp_offer;

        showIncomingModal(targetUserName, targetUserAvatar, callType);
        playRingtone(false); // Nhạc chuông cuộc gọi đến
    }

    /**
     * Chấp nhận cuộc gọi đến
     */
    async function acceptCall() {
        stopRingtone();
        hideIncomingModal();

        try {
            const constraints = {
                audio: true,
                video: callType === 'video' ? { width: { ideal: 1280 }, height: { ideal: 720 } } : false
            };
            localStream = await navigator.mediaDevices.getUserMedia(constraints);
            setLocalStreamUI(localStream, callType);

            createPeerConnection();

            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });

            // Set Remote Description từ SDP Offer của Caller
            const offerDesc = window.pendingSdpOffer;
            await peerConnection.setRemoteDescription({ type: offerDesc.type, sdp: offerDesc.sdp });

            // Tạo SDP Answer
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);

            showActiveCallOverlay(targetUserName, targetUserAvatar, callType);
            startDurationCounter();

            // Gửi Answer tới server
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            await fetch('/social/call/answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    call_id: currentCallId,
                    sdp_answer: { type: answer.type, sdp: answer.sdp }
                })
            });
        } catch (err) {
            console.error('[WebRTC] Error accepting call:', err);
            alert('Không thể kết nối cuộc gọi: ' + err.message);
            hangup('ended');
        }
    }

    /**
     * Từ chối cuộc gọi đến
     */
    function rejectCall() {
        stopRingtone();
        hideIncomingModal();
        if (currentCallId && targetUserId) {
            sendHangup(currentCallId, targetUserId, 'rejected');
        }
        cleanupCall();
    }

    /**
     * Caller nhận SDP Answer từ Callee
     */
    async function handleCallAnswer(e) {
        if (!peerConnection || e.call_id !== currentCallId) return;

        stopRingtone();
        hideOutgoingModal();
        showActiveCallOverlay(targetUserName, targetUserAvatar, callType);
        startDurationCounter();

        try {
            const answerDesc = e.sdp_answer;
            await peerConnection.setRemoteDescription({ type: answerDesc.type, sdp: answerDesc.sdp });
            console.log('[WebRTC] Peer Connection Established Successfully!');
        } catch (err) {
            console.error('[WebRTC] Error setting remote description:', err);
        }
    }

    /**
     * Nhận ICE Candidate từ peer đối phương
     */
    async function handleRemoteIceCandidate(e) {
        if (!peerConnection || e.call_id !== currentCallId) return;
        try {
            if (e.candidate) {
                await peerConnection.addIceCandidate(new RTCIceCandidate(e.candidate));
            }
        } catch (err) {
            console.error('[WebRTC] Error adding ICE candidate:', err);
        }
    }

    /**
     * Đối phương cúp máy hoặc từ chối
     */
    function handleRemoteHangup(e) {
        if (e.call_id === currentCallId) {
            let msg = 'Cuộc gọi đã kết thúc.';
            if (e.reason === 'rejected') msg = 'Người nhận đã từ chối cuộc gọi.';
            if (e.reason === 'busy') msg = 'Người nhận đang bận.';
            
            showToast(msg);
            cleanupCall();
        }
    }

    /**
     * Cúp máy chủ động
     */
    function hangup(reason = 'ended') {
        stopRingtone();
        if (currentCallId && targetUserId) {
            sendHangup(currentCallId, targetUserId, reason);
        }
        cleanupCall();
    }

    /**
     * Gửi event Hangup qua server API
     */
    async function sendHangup(callId, targetId, reason) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            await fetch('/social/call/hangup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
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

    /**
     * Khởi tạo RTCPeerConnection
     */
    function createPeerConnection() {
        peerConnection = new RTCPeerConnection(rtcConfig);

        // Gửi ICE candidate local tới peer đối phương
        peerConnection.onicecandidate = (event) => {
            if (event.candidate && currentCallId && targetUserId) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('/social/call/ice-candidate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        call_id: currentCallId,
                        target_user_id: targetUserId,
                        candidate: event.candidate
                    })
                });
            }
        };

        // Nhận track remote từ peer đối phương
        peerConnection.ontrack = (event) => {
            console.log('[WebRTC] Received remote stream track:', event.track.kind);
            if (!remoteStream) {
                remoteStream = new MediaStream();
                setRemoteStreamUI(remoteStream);
            }
            remoteStream.addTrack(event.track);
        };

        peerConnection.oniceconnectionstatechange = () => {
            console.log('[WebRTC] ICE Connection State:', peerConnection.iceConnectionState);
            if (peerConnection.iceConnectionState === 'disconnected' || peerConnection.iceConnectionState === 'failed') {
                showToast('Kết nối bị gián đoạn');
            }
        };
    }

    /**
     * Bật/Tắt Micro
     */
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

    /**
     * Bật/Tắt Camera
     */
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

    /**
     * Dọn dẹp trạng thái cuộc gọi
     */
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

        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }

        currentCallId = null;
        targetUserId = null;
        targetUserName = '';
        targetUserAvatar = '';
        isCaller = false;
        window.pendingSdpOffer = null;

        hideIncomingModal();
        hideOutgoingModal();
        hideActiveCallOverlay();
    }

    // --- RINGTONE SYNTHESIZER & NOTIFICATIONS ---
    let originalDocumentTitle = document.title;
    let titleBlinkInterval = null;

    function playRingtone(isOutgoing) {
        stopRingtone();

        // 1. Nhấp nháy tiêu đề tab trình duyệt để thu hút sự chú ý khi ở tab khác
        if (!isOutgoing) {
            originalDocumentTitle = document.title;
            let blink = false;
            titleBlinkInterval = setInterval(() => {
                document.title = blink ? '📞 CUỘC GỌI ĐẾN...' : '🔔 (1) Cuộc gọi điện thoại mới!';
                blink = !blink;
            }, 800);

            // Gửi Desktop Notification nếu trình duyệt cấp phép
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

        // 2. Web Audio API Generator - Âm thanh chuông điện thoại thực tế
        try {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            if (audioContext.state === 'suspended') {
                audioContext.resume().catch(() => {});
            }

            if (isOutgoing) {
                // Nhạc chuông gọi đi (Dial tone: Tùuuu... Tùuuu...)
                ringtoneInterval = setInterval(() => {
                    if (!audioContext || audioContext.state === 'closed') return;
                    if (audioContext.state === 'suspended') audioContext.resume();

                    const now = audioContext.currentTime;
                    const osc1 = audioContext.createOscillator();
                    const osc2 = audioContext.createOscillator();
                    const gain = audioContext.createGain();

                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(440, now);
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(480, now);

                    gain.gain.setValueAtTime(0.08, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 1.8);

                    osc1.connect(gain);
                    osc2.connect(gain);
                    gain.connect(audioContext.destination);

                    osc1.start(now);
                    osc2.start(now);
                    osc1.stop(now + 1.8);
                    osc2.stop(now + 1.8);
                }, 3000);
            } else {
                // Nhạc chuông cuộc gọi đến (Ring-Ring... Ring-Ring...)
                const playDoubleBurst = () => {
                    if (!audioContext || audioContext.state === 'closed') return;
                    if (audioContext.state === 'suspended') audioContext.resume();

                    const now = audioContext.currentTime;

                    // Burst 1 (Hạng nốt kép 880Hz + 1046Hz)
                    [0, 0.2].forEach(offset => {
                        const t = now + offset;
                        const osc1 = audioContext.createOscillator();
                        const osc2 = audioContext.createOscillator();
                        const gain = audioContext.createGain();

                        osc1.type = 'triangle';
                        osc1.frequency.setValueAtTime(880, t);
                        osc2.type = 'sine';
                        osc2.frequency.setValueAtTime(1046.5, t); // C6 Note

                        gain.gain.setValueAtTime(0.2, t);
                        gain.gain.exponentialRampToValueAtTime(0.001, t + 0.15);

                        osc1.connect(gain);
                        osc2.connect(gain);
                        gain.connect(audioContext.destination);

                        osc1.start(t);
                        osc2.start(t);
                        osc1.stop(t + 0.15);
                        osc2.stop(t + 0.15);
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
        if (titleBlinkInterval) {
            clearInterval(titleBlinkInterval);
            titleBlinkInterval = null;
            document.title = originalDocumentTitle;
        }
        if (ringtoneInterval) {
            clearInterval(ringtoneInterval);
            ringtoneInterval = null;
        }
        if (audioContext) {
            audioContext.close().catch(() => {});
            audioContext = null;
        }
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
        if (callDurationTimer) {
            clearInterval(callDurationTimer);
            callDurationTimer = null;
        }
    }

    // --- UI HELPERS ---
    function setLocalStreamUI(stream, type) {
        const videoEl = document.getElementById('webrtc-local-video');
        if (videoEl) {
            videoEl.srcObject = stream;
        }
    }

    function setRemoteStreamUI(stream) {
        const videoEl = document.getElementById('webrtc-remote-video');
        const audioEl = document.getElementById('webrtc-remote-audio');
        if (videoEl) videoEl.srcObject = stream;
        if (audioEl) audioEl.srcObject = stream;
    }

    function showIncomingModal(name, avatar, type) {
        const modal = document.getElementById('webrtc-incoming-modal');
        if (!modal) return;
        document.getElementById('webrtc-incoming-name').innerText = name;
        document.getElementById('webrtc-incoming-avatar').src = avatar;
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
        document.getElementById('webrtc-outgoing-avatar').src = avatar;
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
        document.getElementById('webrtc-active-avatar').src = avatar;
        
        const videoContainer = document.getElementById('webrtc-video-container');
        if (videoContainer) {
            videoContainer.style.display = type === 'video' ? 'block' : 'none';
        }

        const videoBtn = document.getElementById('webrtc-video-btn');
        if (videoBtn) {
            videoBtn.style.display = type === 'video' ? 'inline-flex' : 'none';
        }

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
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
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
