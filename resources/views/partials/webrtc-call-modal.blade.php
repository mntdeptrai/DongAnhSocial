{{-- WebRTC P2P Call Modals & Overlay --}}
@if(Auth::check() || session('user_id'))
<div id="webrtc-incoming-modal" class="webrtc-modal-overlay" style="display: none;">
    <div class="webrtc-modal-card">
        <div class="webrtc-avatar-pulse">
            <img id="webrtc-incoming-avatar" src="" alt="Avatar">
        </div>
        <h3 id="webrtc-incoming-name" class="webrtc-user-name">...</h3>
        <p id="webrtc-incoming-type" class="webrtc-call-type">📞 Cuộc gọi thoại tới...</p>
        <div class="webrtc-modal-actions">
            <button type="button" class="webrtc-btn webrtc-btn-reject" onclick="DongAnhWebRTC.rejectCall()">
                ❌ Từ chối
            </button>
            <button type="button" class="webrtc-btn webrtc-btn-accept" onclick="DongAnhWebRTC.acceptCall()">
                📞 Nghe
            </button>
        </div>
    </div>
</div>

<div id="webrtc-outgoing-modal" class="webrtc-modal-overlay" style="display: none;">
    <div class="webrtc-modal-card">
        <div class="webrtc-avatar-pulse outgoing">
            <img id="webrtc-outgoing-avatar" src="" alt="Avatar">
        </div>
        <h3 id="webrtc-outgoing-name" class="webrtc-user-name">...</h3>
        <p id="webrtc-outgoing-type" class="webrtc-call-type">Đang gọi...</p>
        <div class="webrtc-modal-actions">
            <button type="button" class="webrtc-btn webrtc-btn-reject" onclick="DongAnhWebRTC.hangup('ended')">
                📵 Hủy cuộc gọi
            </button>
        </div>
    </div>
</div>

<div id="webrtc-active-overlay" class="webrtc-active-overlay" style="display: none;">
    <div class="webrtc-active-header">
        <div class="webrtc-active-user-info">
            <img id="webrtc-active-avatar" src="" alt="Avatar" class="webrtc-active-avatar-small">
            <div>
                <div id="webrtc-active-name" style="font-weight:700; color:#ffffff; font-size:1.05rem;">...</div>
                <div id="webrtc-call-timer" style="color:#00f2fe; font-size:0.88rem; font-weight:600;">00:00</div>
            </div>
        </div>
    </div>

    <div id="webrtc-video-container" class="webrtc-video-container" style="display: none;">
        <video id="webrtc-remote-video" autoplay playsinline class="webrtc-remote-video"></video>
        <video id="webrtc-local-video" autoplay playsinline muted class="webrtc-local-video"></video>
    </div>
    <audio id="webrtc-remote-audio" autoplay></audio>

    <div class="webrtc-active-controls">
        <button type="button" id="webrtc-mute-btn" class="webrtc-ctrl-btn" onclick="DongAnhWebRTC.toggleMute()">
            🎤 Mute
        </button>
        <button type="button" id="webrtc-video-btn" class="webrtc-ctrl-btn" onclick="DongAnhWebRTC.toggleVideo()" style="display:none;">
            📹 Cam Off
        </button>
        <button type="button" class="webrtc-ctrl-btn webrtc-ctrl-hangup" onclick="DongAnhWebRTC.hangup('ended')">
            📵 Cúp máy
        </button>
    </div>
</div>

<style>
    .webrtc-modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.88);
        backdrop-filter: blur(8px);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .webrtc-modal-card {
        background: rgba(30, 41, 59, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 32px 40px;
        text-align: center;
        width: 340px;
        max-width: 90vw;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        color: #fff;
    }
    .webrtc-avatar-pulse {
        position: relative;
        width: 96px; height: 96px;
        margin: 0 auto 20px;
        border-radius: 50%;
        animation: pulse-ring 1.5s infinite;
    }
    @keyframes pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 20px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .webrtc-avatar-pulse.outgoing {
        animation: pulse-ring-outgoing 1.8s infinite;
    }
    @keyframes pulse-ring-outgoing {
        0% { box-shadow: 0 0 0 0 rgba(0, 242, 254, 0.7); }
        70% { box-shadow: 0 0 0 20px rgba(0, 242, 254, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 242, 254, 0); }
    }
    .webrtc-avatar-pulse img {
        width: 100%; height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #10b981;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
    }
    .webrtc-avatar-pulse.outgoing img {
        border-color: #00f2fe;
        box-shadow: 0 0 20px rgba(0, 242, 254, 0.4);
    }
    .webrtc-user-name {
        font-size: 1.25rem; font-weight: 700; margin: 0 0 6px; color: #f8fafc;
    }
    .webrtc-call-type {
        font-size: 0.9rem; color: #94a3b8; margin: 0 0 24px;
    }
    .webrtc-modal-actions {
        display: flex; gap: 16px; justify-content: center;
    }
    .webrtc-btn {
        border: none; padding: 12px 24px; border-radius: 50px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: transform 0.15s, background 0.15s;
    }
    .webrtc-btn:active { transform: scale(0.95); }
    .webrtc-btn-accept { background: #10b981; color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,0.4); }
    .webrtc-btn-accept:hover { background: #059669; }
    .webrtc-btn-reject { background: #ef4444; color: #fff; box-shadow: 0 4px 14px rgba(239,68,68,0.4); }
    .webrtc-btn-reject:hover { background: #dc2626; }

    .webrtc-active-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: #090d16;
        z-index: 999998;
        display: flex; flex-direction: column; justify-content: space-between;
    }
    .webrtc-active-header {
        position: absolute; top: 20px; left: 20px; z-index: 10;
        background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(10px);
        padding: 8px 16px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.1);
    }
    .webrtc-active-user-info { display: flex; align-items: center; gap: 12px; }
    .webrtc-active-avatar-small { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
    
    .webrtc-video-container {
        position: relative; width: 100%; height: 100%; flex: 1; background: #000; overflow: hidden;
    }
    .webrtc-remote-video {
        width: 100%; height: 100%; object-fit: cover;
    }
    .webrtc-local-video {
        position: absolute; bottom: 100px; right: 20px;
        width: 140px; height: 200px; object-fit: cover;
        border-radius: 16px; border: 2px solid rgba(255,255,255,0.2);
        box-shadow: 0 10px 25px rgba(0,0,0,0.5); z-index: 5;
    }

    .webrtc-active-controls {
        position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 10;
        display: flex; gap: 16px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px);
        padding: 12px 24px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.15);
    }
    .webrtc-ctrl-btn {
        background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.15);
        padding: 10px 20px; border-radius: 30px; font-weight: 600; font-size: 0.9rem; cursor: pointer;
        transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;
    }
    .webrtc-ctrl-btn:hover { background: rgba(255,255,255,0.2); }
    .webrtc-ctrl-btn.muted, .webrtc-ctrl-btn.off { background: #f59e0b; color: #fff; }
    .webrtc-ctrl-hangup { background: #ef4444; border-color: #ef4444; }
    .webrtc-ctrl-hangup:hover { background: #dc2626; }

    .webrtc-toast {
        position: fixed; bottom: 20px; right: 20px; z-index: 9999999;
        background: rgba(15, 23, 42, 0.95); color: #fff; padding: 12px 20px;
        border-radius: 12px; font-weight: 600; font-size: 0.9rem;
        border-left: 4px solid #00f2fe; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        opacity: 0; transform: translateY(10px); transition: all 0.3s;
    }
    .webrtc-toast.show { opacity: 1; transform: translateY(0); }
</style>

<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-peer@9.11.1/simplepeer.min.js"></script>
<script src="{{ asset('js/webrtc-call.js') }}?v={{ time() }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.Echo === 'undefined') {
            window.Echo = new (class {
                constructor() {
                    this._pusher = new Pusher('{{ env('REVERB_APP_KEY') }}', {
                        wsHost:           '{{ env('REVERB_HOST', 'localhost') }}',
                        wsPort:           {{ env('REVERB_PORT', 8080) }},
                        wssPort:          {{ env('REVERB_PORT', 8080) }},
                        forceTLS:         {{ env('REVERB_SCHEME', 'http') === 'https' ? 'true' : 'false' }},
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
                    this.connector = { pusher: this._pusher };
                }
                channel(name) {
                    const ch = this._pusher.subscribe(name);
                    const obj = {
                        listen: (event, cb) => {
                            const evtName = event.startsWith('.') ? event.slice(1) : event;
                            ch.bind(evtName, cb);
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
                            return obj;
                        }
                    };
                    return obj;
                }
            })();
        }

        // Init WebRTC for current logged in user
        var currentUserId = {{ Auth::id() ?? session('user_id') ?? 0 }};
        if (currentUserId && window.DongAnhWebRTC) {
            window.DongAnhWebRTC.init(currentUserId);
        }
    });
</script>
@endif
