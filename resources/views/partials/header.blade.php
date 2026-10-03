<!-- Sticky Glass Navigation Header -->
    <header class="glass-nav">
        <div class="container nav-wrapper">
            <a href="/" class="logo">
                <span>🗺️</span> DongAnh Discovery
            </a>
            
            <div class="nav-collapse main-nav-container" id="navCollapse">
                <nav>
                <ul class="nav-menu">
                    <li>
                        <a href="/" class="nav-link {{ request()->is('/') && !request()->has('cat') ? 'active' : '' }}">
                            <span><span class="nav-icon">🏠</span>Trang chủ</span>
                            <span class="nav-arrow">➔</span>
                        </a>
                    </li>
                    <li>
                        <a href="/ban-tin" class="nav-link {{ request()->is('ban-tin*') ? 'active' : '' }}">
                            <span><span class="nav-icon">📰</span>Bản tin</span>
                            <span class="nav-arrow">➔</span>
                        </a>
                    </li>
                    <li>
                        <a href="/tim-kiem" class="nav-link {{ request()->is('tim-kiem*') ? 'active' : '' }}">
                            <span><span class="nav-icon">🗺️</span>Bản đồ & Tìm kiếm</span>
                            <span class="nav-arrow">➔</span>
                        </a>
                    </li>
                    <li>
                        <a href="/food-tours" class="nav-link {{ request()->is('food-tours*') || (request()->is('food-tour*') && !request()->is('food-tour/tu-tay-lam-dac-san-co-loa*')) ? 'active' : '' }}">
                            <span><span class="nav-icon">🍜</span>Food Tour</span>
                            <span class="nav-arrow">➔</span>
                        </a>
                    </li>
                    <li>
                        <a href="/exp-corner" class="nav-link {{ request()->is('exp-corner*') || request()->is('food-tour/tu-tay-lam-dac-san-co-loa*') ? 'active' : '' }}">
                            <span><span class="nav-icon">🏺</span>Góc trải nghiệm thực tế</span>
                            <span class="nav-arrow">➔</span>
                        </a>
                    </li>
                    <li>
                        <a href="/checkin" class="nav-link {{ request()->is('checkin*') ? 'active' : '' }}">
                            <span><span class="nav-icon">📸</span>Góc Check-in</span>
                            <span class="nav-arrow">➔</span>
                        </a>
                    </li>

                    @if(session()->has('user_id'))

                        <li>
                            <a href="/social" class="nav-link {{ request()->is('social*') ? 'active' : '' }}">
                                <span><span class="nav-icon">💬</span>Kết nối bạn bè</span>
                                <span class="nav-arrow">➔</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="#" onclick="openGuideModal(event)" class="nav-link">
                            <span><span class="nav-icon">📖</span>Giới thiệu & Hướng dẫn</span>
                            <span class="nav-arrow">➔</span>
                        </a>
                    </li>
                </ul>
                </nav>
            
                <div class="user-actions" style="display: flex; align-items: center; gap: 10px;">

                    @if(session()->has('user_id'))
                        @php
                            $pendingCount = \App\Models\Friendship::where('friend_id', session('user_id'))
                                ->where('status', 'pending')
                                ->count();
                        @endphp
                        
                        <div class="header-actions-group">
                            <!-- Nút Chat (Messenger Dropdown) -->
                        <div class="chat-dropdown" x-data="{ 
                            open: false, 
                            chats: [], 
                            loading: false, 
                            fetchChats() {
                                if (this.chats.length === 0) {
                                    this.loading = true;
                                    fetch('/social/recent-chats', {
                                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                        .then(res => res.json())
                                        .then(data => {
                                            this.chats = Array.isArray(data) ? data : [];
                                            this.loading = false;
                                        })
                                        .catch(err => {
                                            this.loading = false;
                                        });
                                }
                            } 
                        }" @click.outside="open = false" style="position: relative; display: flex; align-items: center;">
                            
                            <button @click="open = !open; if(open) fetchChats();" class="header-action-btn" title="Tin nhắn" style="outline: none; border: none; background: rgba(0, 0, 0, 0.05); cursor: pointer;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <!-- Unread indicator dot -->
                                <template x-if="chats.some(c => !c.is_read && c.latest_message_sender_id !== {{ session('user_id') }})">
                                    <span class="badge" style="position: absolute; top: -2px; right: -2px; background: #0ea5e9; width: 8px; height: 8px; border-radius: 50%; border: 1.5px solid #fff; padding: 0;"></span>
                                </template>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-cloak
                                 x-show="open" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="chat-dropdown-menu" 
                                 style="position: absolute; right: 0; top: 100%; margin-top: 10px; width: 340px; background: var(--bg-card, #ffffff); border: 1px solid var(--border-glow, rgba(0,0,0,0.08)); border-radius: 20px; box-shadow: 0 12px 40px rgba(0,0,0,0.15); z-index: 10000; overflow: hidden; text-align: left; display: flex; flex-direction: column;">
                                
                                <div style="padding: 16px 18px; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.015);">
                                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--text-main, #1e293b); font-family: var(--font-heading);">Đoạn chat</h4>
                                    <a href="/social" style="font-size: 0.8rem; color: var(--primary, #0ea5e9); text-decoration: none; font-weight: 700; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=1">Xem tất cả</a>
                                </div>

                                <div style="max-height: 380px; overflow-y: auto; display: flex; flex-direction: column; padding: 8px 0; -webkit-overflow-scrolling: touch;">
                                    <!-- Loading Spinner -->
                                    <div x-show="loading" style="padding: 30px; text-align: center; color: var(--text-muted, #64748b); font-size: 0.88rem;">
                                        <span style="display: inline-block; animation: spin 1s linear infinite; margin-right: 6px;">⏳</span> Đang tải...
                                    </div>

                                    <!-- Empty state -->
                                    <div x-show="!loading && chats.length === 0" style="padding: 40px 20px; text-align: center; color: var(--text-muted, #64748b); font-size: 0.88rem; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                        <span style="font-size: 2rem;">💬</span>
                                        <span>Chưa có đoạn chat nào.</span>
                                    </div>

                                    <!-- Chat list items -->
                                    <template x-show="!loading" x-for="chat in chats" :key="chat.id">
                                        <a href="#" @click.prevent="Alpine.store('chatStore').openChat(chat.id, chat.name, chat.avatar, chat.avatar_url, chat.is_online); open = false;" style="display: flex; align-items: center; gap: 12px; padding: 12px 18px; text-decoration: none; color: inherit; transition: background 0.15s; border-bottom: 1px solid rgba(0,0,0,0.02);" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='transparent'">
                                            <!-- Avatar with Presence Dot -->
                                            <div style="position: relative; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.03); flex-shrink: 0;">
                                                <template x-if="chat.avatar_url">
                                                    <img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" :src="chat.avatar_url" alt="avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                                </template>
                                                <template x-if="!chat.avatar_url">
                                                    <span style="font-size: 1.3rem;" x-text="chat.avatar"></span>
                                                </template>
                                                <!-- Online Status Dot -->
                                                <span x-show="chat.is_online" style="position: absolute; bottom: 1px; right: 1px; width: 11px; height: 11px; border-radius: 50%; background: #10b981; border: 2.5px solid var(--bg-card, #fff); box-shadow: 0 0 6px rgba(16,185,129,0.4);"></span>
                                            </div>

                                            <!-- Content Snippet -->
                                            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px;">
                                                <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px;">
                                                    <span style="font-size: 0.88rem; font-weight: 700; color: var(--text-main, #0f172a); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="chat.name"></span>
                                                    <span style="font-size: 0.72rem; color: var(--text-muted, #64748b); flex-shrink: 0;" x-text="chat.latest_message_time"></span>
                                                </div>
                                                <div style="font-size: 0.8rem; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" :style="!chat.is_read && chat.latest_message_sender_id !== {{ session('user_id') }} ? 'font-weight: 800; color: var(--text-main, #0f172a);' : 'color: var(--text-muted, #64748b);'">
                                                        <template x-if="chat.latest_message_sender_id === {{ session('user_id') }}">
                                                            <span style="opacity: 0.8;">Bạn: </span>
                                                        </template>
                                                        <span x-text="chat.latest_message || 'Bắt đầu cuộc trò chuyện...'"></span>
                                                    </span>
                                                    
                                                    <!-- Unread indicator dot in item -->
                                                    <span x-show="!chat.is_read && chat.latest_message_sender_id !== {{ session('user_id') }}" style="width: 8px; height: 8px; border-radius: 50%; background: #0ea5e9; flex-shrink: 0;"></span>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>

                                <div style="border-top: 1px solid rgba(0,0,0,0.06); text-align: center; background: rgba(0,0,0,0.015);">
                                    <a href="/social" style="display: block; padding: 12px; font-size: 0.82rem; color: var(--text-main, #1e293b); text-decoration: none; font-weight: 700; transition: background 0.15s;" onmouseover="this.style.background='rgba(0,0,0,0.02)'" onmouseout="this.style.background='transparent'">
                                        Xem tất cả trong Messenger
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Nút Quản lý Đơn hàng đã đặt -->
                        <a href="/orders" class="header-action-btn" title="Đơn hàng của tôi" style="position: relative; color: var(--text-main, #0f172a);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                        </a>

                        <!-- Nút Thông báo (Pure Notifications Dropdown with Facebook Aggregations) -->
                        <div class="notif-dropdown" x-data="{ 
                            open: false, 
                            items: [],
                            loading: false,
                            filterTab: 'all',
                            get unreadCount() {
                                return this.items.filter(i => !i.is_read).length;
                            },
                            get filteredItems() {
                                if (this.filterTab === 'unread') {
                                    return this.items.filter(i => !i.is_read);
                                }
                                return this.items;
                            },
                            init() {
                                this.fetchNotifications();
                            },
                            fetchNotifications() {
                                this.loading = true;
                                fetch('/api/user-notifications')
                                    .then(res => res.json())
                                    .then(data => {
                                        this.items = Array.isArray(data) ? data : [];
                                        this.loading = false;
                                    })
                                    .catch(() => { this.loading = false; });
                            },
                            async doMarkAndRefresh() {
                                this.items.forEach(i => i.is_read = true);
                                try {
                                    await fetch('/api/user-notifications/read', { 
                                        method: 'POST', 
                                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } 
                                    });
                                } catch(e) {}
                            }
                        }" @click.outside="open = false" style="position: relative; display: flex; align-items: center;">
                            
                            <button @click="open = !open; if(open) { fetchNotifications(); }" class="header-action-btn" title="Thông báo" style="outline: none; border: none; background: rgba(0, 0, 0, 0.05); cursor: pointer; position: relative;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                                <template x-if="unreadCount > 0">
                                    <span class="badge" style="position: absolute; top: -2px; right: -2px;" x-text="unreadCount"></span>
                                </template>
                            </button>

                            <!-- Dropdown Menu (Nền Đục 100% Thuần Khiết) -->
                            <div x-cloak
                                 x-show="open" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="notif-dropdown-menu" 
                                 style="position: absolute; right: 0; top: 100%; margin-top: 10px; width: 390px; max-width: 92vw; background: #ffffff !important; opacity: 1 !important; border: 1px solid rgba(0,0,0,0.08); border-radius: 24px; box-shadow: 0 20px 60px -10px rgba(15, 23, 42, 0.3); z-index: 999999 !important; overflow: hidden; text-align: left; display: flex; flex-direction: column; white-space: normal; backdrop-filter: none !important;">
                                
                                <!-- Header với Segment Controls chuẩn Facebook -->
                                <div style="padding: 18px 20px 14px 20px; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                        <h4 style="margin: 0; font-size: 1.18rem; font-weight: 800; color: #0f172a; font-family: 'Be Vietnam Pro', sans-serif;">
                                            Thông báo
                                        </h4>
                                        <button type="button" @click="doMarkAndRefresh()" style="background: rgba(14,165,233,0.08); border: none; font-size: 0.78rem; font-weight: 800; color: #0ea5e9; cursor: pointer; padding: 6px 12px; border-radius: 20px; transition: all 0.15s;" onmouseover="this.style.background='rgba(14,165,233,0.16)'" onmouseout="this.style.background='rgba(14,165,233,0.08)'">
                                            ✓ Đánh dấu tất cả đã đọc
                                        </button>
                                    </div>
                                    
                                    <!-- Segment Control Pills -->
                                    <div style="background: #f1f5f9; padding: 4px; border-radius: 30px; display: inline-flex; gap: 4px;">
                                        <button type="button" @click="filterTab = 'all'" :style="filterTab === 'all' ? 'background: #0ea5e9; color: #ffffff; box-shadow: 0 3px 10px rgba(14,165,233,0.3);' : 'background: transparent; color: #64748b;'" style="border: none; padding: 6px 18px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; cursor: pointer; transition: all 0.15s;">
                                            Tất cả
                                        </button>
                                        <button type="button" @click="filterTab = 'unread'" :style="filterTab === 'unread' ? 'background: #0ea5e9; color: #ffffff; box-shadow: 0 3px 10px rgba(14,165,233,0.3);' : 'background: transparent; color: #64748b;'" style="border: none; padding: 6px 18px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; cursor: pointer; transition: all 0.15s;">
                                            Chưa đọc <span x-show="unreadCount > 0" x-text="'(' + unreadCount + ')'"></span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Scrollable Notification List -->
                                <div style="max-height: 400px; overflow-y: auto; display: flex; flex-direction: column; padding: 6px 0; background: #ffffff; -webkit-overflow-scrolling: touch;">
                                    <div x-show="loading" style="padding: 40px; text-align: center; color: #64748b; font-size: 0.88rem;">
                                        <span style="display: inline-block; animation: spin 1s linear infinite; margin-right: 6px;">⏳</span> Đang tải...
                                    </div>

                                    <template x-if="!loading && filteredItems.length > 0">
                                        <div style="display: flex; flex-direction: column;">
                                            <template x-for="item in filteredItems" :key="item.id">
                                                <a :href="item.target_url || item.url || '/ban-tin'" 
                                                   @click="handleNotifItemClick(item.target_url || item.url || '/ban-tin', $event); open = false;" 
                                                   :style="item.is_read ? 'background: #ffffff;' : 'background: #f0f9ff; border-left: 3.5px solid #0ea5e9;'" 
                                                   style="display: flex; align-items: center; gap: 14px; padding: 12px 18px; text-decoration: none; color: inherit; border-bottom: 1px solid #f1f5f9; transition: background 0.15s; white-space: normal; position: relative; width: 100%; box-sizing: border-box;" 
                                                   onmouseover="this.style.background='#f8fafc'" 
                                                   onmouseout="this.style.background=this.getAttribute('data-bg') || (this.style.borderLeft ? '#f0f9ff' : '#ffffff')">
                                                    
                                                    <!-- Left Avatar Circle với Mini Badge -->
                                                    <div style="position: relative; width: 44px; height: 44px; flex-shrink: 0;">
                                                        <div :class="'notif-icon-circle notif-type-' + (item.type || 'default')" style="width: 44px !important; height: 44px !important; min-width: 44px !important; min-height: 44px !important; border-radius: 50% !important;">
                                                            <span x-text="item.type === 'reaction' ? '👍' : (item.type === 'comment' ? '💬' : (item.type === 'reply' ? '↩️' : (item.type === 'share' ? '🔄' : (item.type === 'message' ? '✉️' : (item.type === 'review' ? '⭐' : (item.type === 'friend' ? '👥' : (item.type === 'new_post' ? '📣' : '📦')))))))"></span>
                                                        </div>
                                                    </div>

                                                    <!-- Main Content Text -->
                                                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; text-align: left;">
                                                        <span style="font-size: 0.88rem; font-weight: 800; color: #0f172a; white-space: normal; word-break: break-word; line-height: 1.35; margin: 0;" x-text="item.title ? item.title.replace(/^[\p{Emoji}\u200d\ufe0f\s]+/u, '') : ''"></span>
                                                        <span style="font-size: 0.82rem; color: #475569; line-height: 1.4; white-space: normal; word-break: break-word; margin: 0;" x-text="item.body"></span>
                                                        <span style="font-size: 0.72rem; color: #0284c7; font-weight: 700; margin-top: 3px;" x-text="item.time"></span>
                                                    </div>

                                                    <!-- Unread Dot Indicator -->
                                                    <template x-if="!item.is_read">
                                                        <span style="width: 9px; height: 9px; border-radius: 50%; background: #0ea5e9; flex-shrink: 0; margin-left: 6px; box-shadow: 0 0 10px rgba(14,165,233,0.8);"></span>
                                                    </template>
                                                </a>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="!loading && filteredItems.length === 0">
                                        <div style="padding: 45px 20px; text-align: center; color: #64748b; font-size: 0.88rem; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                            <span style="font-size: 2.5rem;">🔔</span>
                                            <span style="font-weight: 700; color: #334155;">Hiện tại chưa có thông báo nào.</span>
                                        </div>
                                    </template>
                                </div>

                                <div style="padding: 12px; border-top: 1px solid #f1f5f9; text-align: center; background: #fafafa;">
                                    <button type="button" @click="fetchNotifications()" style="background: none; border: none; font-size: 0.8rem; font-weight: 800; color: #0ea5e9; cursor: pointer; padding: 4px 12px; border-radius: 12px;" onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='none'">
                                        🔄 Tải lại thông báo
                                    </button>
                                </div>
                            </div>
                        </div>
                        </div> <!-- End header-actions-group -->

                        <div class="profile-dropdown" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="profile-trigger-btn" title="Tài khoản">
                                @php $navUser = Auth::user() ?? \App\Models\User::find(session('user_id')); @endphp
                                <div class="profile-avatar-container">
                                    @if($navUser && $navUser->avatar_url)
                                        <img onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($navUser->name ?? 'User') }}&background=0ea5e9&color=fff';" src="{{ $navUser->avatar_url }}" alt="avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <span style="font-size: 1.2rem;">{{ $navUser->avatar ?? '👤' }}</span>
                                    @endif
                                </div>
                                <div class="profile-chevron-badge">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 9l6 6 6-6"></path>
                                    </svg>
                                </div>
                            </button>
                            <div x-cloak x-show="open" x-transition class="profile-dropdown-menu">
                                @php
                                    $navUser = Auth::user() ?? \App\Models\User::find(session('user_id'));
                                    $effectiveRole = session('user_role') ?: ($navUser ? $navUser->role : 'user');
                                    $hasBusinessEatery = false;
                                    if ($navUser) {
                                        $hasBusinessEatery = \Illuminate\Support\Facades\DB::table('eateries')
                                            ->where('user_id', $navUser->id)
                                            ->orWhere('phone', $navUser->phone)
                                            ->exists();
                                    }
                                @endphp
                                <div class="user-info-header">
                                    <div class="user-name" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                        <span>{{ session('user_name') ?: ($navUser ? $navUser->name : '') }}</span>
                                        @if($effectiveRole === 'admin')
                                            <span title="Tài khoản Quản trị viên (Admin)" style="color: #ef4444; font-size: 0.9rem;">⭐</span>
                                        @endif
                                    </div>
                                    <div class="user-role" style="font-weight: 700; font-size: 0.75rem; color: #059669; margin-top: 3px;">
                                        @if($effectiveRole === 'admin')
                                            🏛️ Quản trị viên Tổng
                                        @elseif($effectiveRole === 'principal')
                                            🏫 Hiệu trưởng Quản lý
                                        @elseif($effectiveRole === 'manager')
                                            🏛️ Ban Quản lý Chợ
                                        @elseif($effectiveRole === 'health_station')
                                            🏥 Cán bộ Trạm Y Tế
                                        @elseif(in_array($effectiveRole, ['hkd', 'dn', 'business', 'seller']) || $hasBusinessEatery)
                                            🏢 Hộ Kinh Doanh & Doanh Nghiệp
                                        @else
                                            👤 Thành viên cộng đồng
                                        @endif
                                    </div>
                                </div>
                                
                                @if($effectiveRole === 'admin' || $effectiveRole === 'manager')
                                    <a href="/admin/dashboard" class="dropdown-item" style="color: #0ea5e9; font-weight: 700; background: rgba(14, 165, 233, 0.08);">
                                        <span>⚙️</span> Trang Quản Trị Hệ Thống
                                    </a>
                                @elseif($effectiveRole === 'health_station')
                                    <a href="/health-station/dashboard" class="dropdown-item" style="color: #0d9488; font-weight: 700; background: rgba(13, 148, 136, 0.08);">
                                        <span>🏥</span> Kênh Quản Lý Trạm Y Tế
                                    </a>
                                @elseif($effectiveRole === 'principal')
                                    <a href="/principal/schools" class="dropdown-item" style="color: #4f46e5; font-weight: 700; background: rgba(79, 70, 229, 0.08);">
                                        <span>🏫</span> Kênh Quản Lý Trường Học
                                    </a>
                                @endif

                                @if(in_array($effectiveRole, ['hkd', 'dn', 'business', 'seller']) || $hasBusinessEatery)
                                    <a href="{{ route('hkd.dashboard') }}" class="dropdown-item" style="color: #059669; font-weight: 800; background: #ecfdf5; border: 1px solid #a7f3d0;">
                                        <span>🏢</span> Kênh Điều Hành HKD & Doanh Nghiệp
                                    </a>
                                @endif
                                
                                <a href="/profile" class="dropdown-item">
                                    <span>👤</span> Trang cá nhân
                                </a>
                                
                                <a href="{{ route('orders.index') }}" class="dropdown-item">
                                    <span>📦</span> Quản lý đơn hàng
                                </a>
                                
                                <form action="/auth/logout" method="POST" style="margin: 0; width: 100%;">
                                    @csrf
                                    <button type="submit" class="dropdown-item dropdown-item-logout">
                                        <span>🚪</span> Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="/auth/login" class="btn-secondary" style="text-decoration: none; padding: 6px 14px; font-size: 0.85rem; border-radius: 8px;">Đăng nhập</a>
                        <a href="/auth/register" class="btn-primary" style="text-decoration: none; padding: 6px 14px; font-size: 0.85rem; border-radius: 8px;">Đăng ký</a>
                    @endif
                </div>
            </div> <!-- End nav-collapse -->

            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>