@if(session()->has('user_id'))
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <!-- Messenger-style Floating Chat Widget (High-End Sleek & Premium Theme) -->
    <style>
        .fchat-head-bubble {
            pointer-events: auto;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
        }
        .fchat-head-bubble:hover {
            transform: scale(1.12);
        }
        .fchat-head-close {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ef4444;
            color: #ffffff;
            border: 2px solid #ffffff;
            font-size: 0.68rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            z-index: 10;
        }
        .fchat-head-bubble:hover .fchat-head-close {
            opacity: 1;
        }
        .fchat-head-close:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        .fchat-window {
            pointer-events: auto;
            width: 345px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 20px 20px 12px 12px;
            box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .fchat-header {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
            flex-shrink: 0;
            transition: background 0.2s ease;
        }
        .fchat-header:hover { background: #f1f5f9; }
        .fchat-header-btn {
            width: 30px; height: 30px;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease;
            font-size: 0.85rem;
        }
        .fchat-header-btn:hover { background: #e2e8f0; color: #0f172a; }
        .fchat-header-btn.primary { color: #0284c7; background: #f0f9ff; }
        .fchat-header-btn.primary:hover { background: #e0f2fe; color: #0369a1; }
        .fchat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 14px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            -webkit-overflow-scrolling: touch;
        }
        .fchat-messages::-webkit-scrollbar { width: 4px; }
        .fchat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .fchat-messages::-webkit-scrollbar-track { background: transparent; }
        .fchat-input-bar {
            background: transparent !important;
            border: none !important;
            color: #0f172a !important;
            font-size: 0.88rem !important;
            padding: 0 !important;
            outline: none !important;
            width: 100% !important;
            font-weight: 500 !important;
        }
        .fchat-input-bar::placeholder { color: #94a3b8; }
        .fchat-input-container {
            flex: 1;
            display: flex;
            align-items: center;
            border-radius: 20px;
            padding: 7px 14px;
            background: #f1f5f9;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .fchat-input-container:focus-within {
            background: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        .fchat-footer {
            padding: 10px 12px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Message Bubble Specific Classes — Luxury & Readable */
        .fchat-msg-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            width: 100%;
            margin-bottom: 2px;
        }
        .fchat-msg-row.sent {
            flex-direction: row-reverse;
        }
        .fchat-msg-row.received {
            flex-direction: row;
        }
        .fchat-bubble-wrap {
            display: block;
            max-width: 76%;
        }
        .fchat-bubble {
            display: inline-block !important;
            width: auto !important;
            max-width: 100% !important;
            padding: 9px 14px !important;
            font-size: 0.88rem !important;
            line-height: 1.45 !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            white-space: normal !important;
            box-sizing: border-box !important;
        }
        .fchat-bubble-sent {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
            border-radius: 18px 18px 4px 18px;
            font-weight: 500;
        }
        .fchat-bubble-received {
            background: #ffffff;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border-radius: 18px 18px 18px 4px;
            font-weight: 500;
        }
        .fchat-avatar-slot {
            width: 26px;
            height: 26px;
            flex-shrink: 0;
        }
    </style>

    <div id="floating-chat-container" 
         x-data 
         x-cloak
         style="position: fixed; bottom: 24px; right: 92px; display: flex; gap: 14px; z-index: 99999; align-items: flex-end; pointer-events: none; font-family: 'Plus Jakarta Sans', sans-serif;">
        
        <template x-for="(chat, index) in $store.chatStore.openChats" :key="chat.id">
            <div>
                <!-- Minimized Floating Avatar Chat Head -->
                <div x-show="chat.is_minimized" 
                     @click="chat.is_minimized = false"
                     class="fchat-head-bubble"
                     :title="chat.name">
                    <div style="position: relative; width: 52px; height: 52px; border-radius: 50%;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #ffffff; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 2.5px solid #ffffff; box-shadow: 0 8px 25px rgba(15, 23, 42, 0.25);">
                            <template x-if="chat.avatar_url">
                                <img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" :src="chat.avatar_url" alt="avatar" style="width: 100%; height: 100%; object-fit: cover;">
                            </template>
                            <template x-if="!chat.avatar_url">
                                <span x-text="chat.avatar || '👤'"></span>
                            </template>
                        </div>
                        <span x-show="chat.is_online" style="position: absolute; bottom: 1px; right: 1px; width: 13px; height: 13px; border-radius: 50%; background: #22c55e; border: 2px solid #ffffff; box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.3);"></span>
                        <button type="button" @click.stop="$store.chatStore.closeChat(chat.id)" class="fchat-head-close" title="Đóng">✕</button>
                    </div>
                </div>

                <!-- Expanded Full Chat Window -->
                <div x-show="!chat.is_minimized" class="fchat-window" style="height: 520px;">
                    
                    <!-- Chat Header -->
                    <div class="fchat-header" @click="chat.is_minimized = !chat.is_minimized">
                        <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                            <!-- Avatar with online dot -->
                            <div style="position: relative; width: 36px; height: 36px; flex-shrink: 0;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 2px solid #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                    <template x-if="chat.avatar_url">
                                        <img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" :src="chat.avatar_url" alt="avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                    </template>
                                    <template x-if="!chat.avatar_url">
                                        <span x-text="chat.avatar || '👤'"></span>
                                    </template>
                                </div>
                                <span x-show="chat.is_online" style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; border-radius: 50%; background: #22c55e; border: 2px solid #ffffff; box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.3);"></span>
                            </div>
                            <div style="display: flex; flex-direction: column; min-width: 0; justify-content: center;">
                                <span style="font-size: 0.92rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25; letter-spacing: -0.01em;" x-text="chat.name"></span>
                                <div style="display: flex; align-items: center; gap: 4px; margin-top: 1px;">
                                    <span style="font-size: 0.72rem; font-weight: 600; line-height: 1.2;" :style="chat.is_online ? 'color: #16a34a;' : 'color: #94a3b8;'" x-text="chat.is_online ? '• Đang hoạt động' : 'Ngoại tuyến'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Header action buttons -->
                        <div style="display: flex; align-items: center; gap: 3px; flex-shrink: 0;" @click.stop.prevent>
                            <a :href="'/profile/' + chat.id" target="_blank" class="fchat-header-btn" title="Xem trang cá nhân" style="text-decoration: none;" @click.stop>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            </a>
                            <button type="button" class="fchat-header-btn" title="Gọi thoại" @click.stop.prevent="window.DongAnhWebRTC ? window.DongAnhWebRTC.startCall(chat.id, chat.name, chat.avatar_url || chat.avatar, 'audio') : alert('Đang tải mô-đun cuộc gọi WebRTC...')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/></svg>
                            </button>
                            <button type="button" class="fchat-header-btn" title="Gọi video" @click.stop.prevent="window.DongAnhWebRTC ? window.DongAnhWebRTC.startCall(chat.id, chat.name, chat.avatar_url || chat.avatar, 'video') : alert('Đang tải mô-đun cuộc gọi WebRTC...')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                            </button>
                            <button type="button" class="fchat-header-btn" @click.stop.prevent="chat.is_minimized = true" title="Thu nhỏ">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13H5v-2h14v2z"/></svg>
                            </button>
                            <button type="button" class="fchat-header-btn" @click.stop.prevent="$store.chatStore.closeChat(chat.id)" title="Đóng" onmouseover="this.style.color='#ef4444'; this.style.background='#fef2f2'" onmouseout="this.style.color='#64748b'; this.style.background='transparent'">✕</button>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div class="fchat-messages" :id="'floating-chat-messages-' + chat.id">
                        <!-- Loading -->
                        <div x-show="chat.loading" style="display: flex; justify-content: center; align-items: center; height: 100%; color: #64748b; font-size: 0.82rem; gap: 8px; font-weight: 600;">
                            <span style="display: inline-block; animation: spin 1s linear infinite;">⏳</span> Đang tải cuộc trò chuyện...
                        </div>

                        <!-- Empty state -->
                        <div x-show="!chat.loading && chat.messages.length === 0" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 10px; color: #64748b; padding: 20px; text-align: center;">
                            <div style="width: 56px; height: 56px; border-radius: 50%; background: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 2px solid #ffffff;">
                                <template x-if="chat.avatar_url"><img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" :src="chat.avatar_url" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"></template>
                                <template x-if="!chat.avatar_url"><span x-text="chat.avatar || '👤'"></span></template>
                            </div>
                            <span style="font-size: 0.88rem; font-weight: 700; color: #0f172a;" x-text="chat.name"></span>
                            <span style="font-size: 0.76rem; color: #64748b;">Chưa có tin nhắn nào. Hãy gửi lời chào đầu tiên! 👋</span>
                        </div>

                        <!-- Message bubbles -->
                        <template x-show="!chat.loading" x-for="(msg, mi) in chat.messages" :key="msg.id">
                            <div>
                                <!-- Timestamp separator if first message -->
                                <template x-if="mi === 0">
                                    <div style="display: flex; justify-content: center; margin: 6px 0 12px;">
                                        <span style="background: rgba(226, 232, 240, 0.7); color: #64748b; font-size: 0.68rem; font-weight: 600; padding: 3px 10px; border-radius: 12px; backdrop-filter: blur(4px);" x-text="msg.created_at_format || 'Hôm nay'"></span>
                                    </div>
                                </template>

                                <!-- Message Row: row-reverse = sent (right), row = received (left) -->
                                <div class="fchat-msg-row" :class="msg.sender_id == {{ session('user_id') }} ? 'sent' : 'received'">
                                    
                                    <!-- Avatar slot for received messages -->
                                    <template x-if="msg.sender_id != {{ session('user_id') }}">
                                        <div class="fchat-avatar-slot">
                                            <!-- Show avatar only for the last consecutive received message -->
                                            <template x-if="!(chat.messages[mi+1] && chat.messages[mi+1].sender_id != {{ session('user_id') }})">
                                                <div style="width: 26px; height: 26px; border-radius: 50%; background: #ffffff; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                                                    <template x-if="chat.avatar_url">
                                                        <img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" :src="chat.avatar_url" style="width: 100%; height: 100%; object-fit: cover;">
                                                    </template>
                                                    <template x-if="!chat.avatar_url">
                                                        <span x-text="chat.avatar || '👤'"></span>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Bubble wrapper: controls alignment direction -->
                                    <div class="fchat-bubble-wrap">
                                        <!-- Text Bubble -->
                                        <template x-if="msg.message">
                                            <div class="fchat-bubble"
                                                 :class="msg.sender_id == {{ session('user_id') }} ? 'fchat-bubble-sent' : 'fchat-bubble-received'">
                                                <span x-text="msg.message"></span>
                                            </div>
                                        </template>
                                        
                                        <!-- Image / Video Bubble -->
                                        <template x-if="msg.media_path">
                                            <div style="border-radius: 16px; overflow: hidden; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 14px rgba(0,0,0,0.08);">
                                                <template x-if="msg.media_type === 'image'">
                                                    <img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" :src="msg.media_path" style="width: 100%; max-height: 190px; object-fit: cover; display: block; cursor: pointer;" @click="window.open(msg.media_path)">
                                                </template>
                                                <template x-if="msg.media_type === 'video'">
                                                    <video :src="msg.media_path" controls style="width: 100%; max-height: 190px; display: block;"></video>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Scroll anchor -->
                        <div :id="'floating-chat-bottom-' + chat.id" style="height: 1px; clear: both;"></div>
                    </div>

                    <!-- Input Footer -->
                    <div class="fchat-footer">
                        <button type="button" class="fchat-header-btn primary shrink-0" style="width: 34px; height: 34px;" title="Ghi âm">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                        </button>
                        <button type="button" @click="document.getElementById('floating-file-input-' + chat.id).click()" class="fchat-header-btn primary shrink-0" style="width: 34px; height: 34px;" title="Gửi tệp đính kèm">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </button>
                        <input type="file" :id="'floating-file-input-' + chat.id" style="display: none;" accept="image/*,video/*" @change="$store.chatStore.uploadFile(chat.id, $event)">

                        <!-- Input Area -->
                        <div class="fchat-input-container">
                            <input type="text" 
                                   :id="'floating-chat-input-' + chat.id" 
                                   placeholder="Nhập tin nhắn..." 
                                   x-model="chat.inputText" 
                                   @keydown.enter.prevent="if (!$event.isComposing && $event.keyCode !== 229) $store.chatStore.sendSubmitMessage(chat.id, $event)"
                                   autocomplete="off" 
                                   class="fchat-input-bar">
                        </div>
                        
                        <!-- Actions -->
                        <div style="flex-shrink: 0; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;">
                            <!-- 👍 Like -->
                            <button type="button" x-show="!chat.inputText || !chat.inputText.trim()" @click="$store.chatStore.sendMessage(chat.id, '👍')" class="fchat-header-btn primary shrink-0" style="width: 34px; height: 34px; font-size: 1.05rem;" title="Gửi 👍">
                                👍
                            </button>
                            <!-- 🚀 Send -->
                            <button type="button" @click.prevent="$store.chatStore.sendSubmitMessage(chat.id)" x-show="chat.inputText && chat.inputText.trim()" style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35); transition: transform 0.15s ease;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='none'" title="Gửi tin nhắn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>


    <!-- Pusher JS Library -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        document.addEventListener('alpine:init', function() {
            Alpine.store('chatStore', {
                openChats: [],

                openChat(friendId, friendName, friendAvatar, friendAvatarUrl, isOnline) {
                    if (window.location.pathname.startsWith('/social')) {
                        window.location.href = '/social?chat_with=' + friendId;
                        return;
                    }

                    // Check if chat is already open
                    const existing = this.openChats.find(c => c.id === friendId);
                    if (existing) {
                        existing.is_minimized = false;
                        this.scrollToBottom(friendId);
                        return;
                    }

                    // Max 3 active chats on screen
                    if (this.openChats.length >= 3) {
                        this.openChats.shift();
                    }

                    const newChat = Alpine.reactive({
                        id: friendId,
                        name: friendName,
                        avatar: friendAvatar,
                        avatar_url: friendAvatarUrl,
                        is_online: isOnline,
                        messages: [],
                        is_minimized: false,
                        loading: true,
                        inputText: ''
                    });

                    this.openChats.push(newChat);

                    // Load initial messages
                    this.loadMessages(friendId);
                },

                closeChat(friendId) {
                    this.openChats = this.openChats.filter(c => c.id !== friendId);
                },

                loadMessages(friendId) {
                    fetch('/social/messages/' + friendId)
                        .then(res => res.json())
                        .then(data => {
                            const chat = this.openChats.find(c => c.id === friendId);
                            if (chat) {
                                chat.messages = Array.isArray(data) ? data : (data.messages || []);
                                chat.loading = false;
                                this.scrollToBottom(friendId);
                            }
                        })
                        .catch(err => {
                            console.error('Failed to load messages', err);
                        });
                    this.startPolling();
                },

                startPolling() {
                    if (this._pollingTimer) return;
                    this._pollingTimer = setInterval(() => {
                        if (document.hidden) return; // Tạm dừng polling khi tab trình duyệt bị ẩn
                        this.openChats.forEach(chat => {
                            if (!chat.is_minimized) {
                                fetch('/social/messages/' + chat.id)
                                    .then(res => res.json())
                                    .then(data => {
                                        const msgs = Array.isArray(data) ? data : (data.messages || []);
                                        if (msgs.length !== chat.messages.length) {
                                            chat.messages = msgs;
                                            this.scrollToBottom(chat.id);
                                        }
                                    })
                                    .catch(() => {});
                            }
                        });

                        fetch('/social/recent-chats', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (Array.isArray(data)) {
                                    this.openChats.forEach(chat => {
                                        const matched = data.find(c => c.id === chat.id);
                                        if (matched) {
                                            chat.is_online = matched.is_online;
                                        }
                                    });
                                }
                            })
                            .catch(() => {});
                    }, 4000);
                },

                sendSubmitMessage(friendId, event = null) {
                    if (event && (event.isComposing || event.keyCode === 229)) {
                        return;
                    }
                    const chat = this.openChats.find(c => c.id === friendId);
                    if (!chat || !chat.inputText || !chat.inputText.trim()) return;
                    
                    const text = chat.inputText.trim();
                    chat.inputText = ''; // clear input directly
                    this.sendMessage(friendId, text);
                },

                sendMessage(friendId, messageText, mediaPath = null, mediaType = null) {
                    fetch('/social/messages', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            receiver_id: friendId,
                            message: messageText,
                            media_path: mediaPath,
                            media_type: mediaType
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const chat = this.openChats.find(c => c.id === friendId);
                            if (chat) {
                                chat.messages.push(data.message);
                                this.scrollToBottom(friendId);
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Failed to send message', err);
                    });
                },

                uploadFile(friendId, event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Size limit 20MB
                    if (file.size > 20 * 1024 * 1024) {
                        alert('Kích thước tệp không được vượt quá 20MB.');
                        return;
                    }

                    const chat = this.openChats.find(c => c.id === friendId);
                    if (chat) {
                        chat.loading = true;
                    }

                    const formData = new FormData();
                    formData.append('files[]', file);

                    fetch('/api/v1/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.files.length > 0) {
                            const uploadedFile = data.files[0];
                            this.sendMessage(friendId, '', uploadedFile.url, uploadedFile.file_type);
                        } else {
                            alert('Tải ảnh thất bại.');
                            if (chat) chat.loading = false;
                        }
                    })
                    .catch(err => {
                        console.error('Error uploading file', err);
                        alert('Lỗi kết nối khi tải ảnh.');
                        if (chat) chat.loading = false;
                    });
                },

                scrollToBottom(friendId) {
                    setTimeout(() => {
                        const el = document.getElementById('floating-chat-bottom-' + friendId);
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }, 120);
                }
            });
        });

        // Initialize Pusher Listener for Live Floating Chat Box Updates
        document.addEventListener('DOMContentLoaded', function() {
            const isProduction = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
            
            const pusher = new Pusher('donganhreverbkey', {
                wsHost: isProduction ? window.location.hostname : '127.0.0.1',
                wsPort: isProduction ? (window.location.protocol === 'https:' ? 443 : 80) : 8090,
                wssPort: isProduction ? (window.location.protocol === 'https:' ? 443 : 80) : 8090,
                forceTLS: isProduction ? window.location.protocol === 'https:' : false,
                enabledTransports: ['ws', 'wss'],
                cluster: 'mt1',
                disableStats: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-Token': '{{ csrf_token() }}',
                    }
                }
            });

            const channel = pusher.subscribe('private-chat.{{ session('user_id') }}');
            
            channel.bind('MessageSent', function(data) {
                const store = Alpine.store('chatStore');
                if (store) {
                    const chat = store.openChats.find(c => c.id === data.sender_id);
                    if (chat) {
                        chat.messages.push(data);
                        
                        // Mark as read on server since the chat window is active
                        fetch('/social/messages/' + data.sender_id).catch(err => {});
                        
                        store.scrollToBottom(data.sender_id);
                    } else {
                        // If window is not open, refresh/invalidate dropdown caches
                        const chatDropdown = document.querySelector('.chat-dropdown');
                        if (chatDropdown) {
                            const xData = chatDropdown.__x?.$data || Alpine.$data(chatDropdown);
                            if (xData && xData.chats.length > 0) {
                                xData.chats = []; // Reset dropdown cache
                                xData.fetchChats();
                            }
                        }
                    }
                }
            });

            window.pusherClient = pusher;
        });
    </script>
    @endif