<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script>
        // Force light theme
        document.documentElement.setAttribute('data-theme', 'light');
    </script>
    
    <!-- SEO Meta Tags -->
    <title>@yield('title', 'Bản đồ số Đông Anh - Dong Anh Map')</title>
    <meta name="description" content="@yield('meta_description', 'Bản đồ số Đông Anh - Số hóa toàn bộ trường học, bệnh viện, khách sạn, nhà hàng, quán ăn và đặc sản tại Đông Anh. Chỉ đường nhanh, thông tin chi tiết, liên kết Google Maps.')">
    <meta name="keywords" content="bản đồ đông anh, trường học đông anh, bệnh viện đông anh, đặc sản đông anh, khách sạn đông anh, địa điểm đông anh">
    <link rel="canonical" href="@yield('canonical_url', request()->url())">
    
    <!-- OpenGraph Social Tags -->
    <meta property="og:site_name" content="Dong Anh Map">
    <meta property="og:title" content="@yield('title', 'Bản đồ số Đông Anh - Dong Anh Map')">
    <meta property="og:description" content="@yield('meta_description', 'Bản đồ số Đông Anh - Số hóa trường học, bệnh viện, khách sạn, nhà hàng và đặc sản.')">
    <meta property="og:image" content="@yield('og_image', 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80')">
    <meta property="og:type" content="website">
    
    <!-- Leaflet.js Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <!-- Google Fonts Preload: Outfit (heading) + Be Vietnam Pro (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Custom Theme Styling -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1.0.0' }}">
    
    <!-- Mobile Native Overrides (Only load for mobile and high zoom desktops) -->
    <link rel="stylesheet" media="screen and (max-width: 1200px)" href="{{ asset('css/mobile-native.css') }}?v={{ file_exists(public_path('css/mobile-native.css')) ? filemtime(public_path('css/mobile-native.css')) : '1.0.0' }}">

    <!-- 📱 Mobile Viewport Fit Fix — Ôm gọn nội dung, không scroll ngang -->
    <link rel="stylesheet" href="{{ asset('css/mobile-fix.css') }}?v={{ file_exists(public_path('css/mobile-fix.css')) ? filemtime(public_path('css/mobile-fix.css')) : '1.0.0' }}">
    
    <!-- Dynamic Schema.org JSON-LD Structured Data for Google Indexing -->
    @yield('seo_schema')
    
    <!-- Alpine.js for Modern Client Interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- 📱 MOBILE LAYOUT — Safety net + box-sizing universal -->
    <style>
        /* Box-sizing universal (nếu base.css chưa load kịp) */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        /* Safety net: body direct children không vượt 100vw */
        body > * {
            max-width: 100%;
        }

        @media (max-width: 992px) {
            html {
                /* html { overflow-x: hidden } đặt trong base.css */
                overflow-y: auto;
                height: auto;
            }
            body {
                /* KHÔNG đặt overflow-x: hidden ở body — gây iOS scroll bug */
                overflow-y: auto;
                height: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Modern Header Action Buttons */
        .header-action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.05);
            border: none;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: background 0.2s, transform 0.1s;
        }
        .header-action-btn:hover {
            background: rgba(0, 0, 0, 0.08);
            color: #0ea5e9;
        }
        .header-action-btn:active {
            transform: scale(0.95);
        }
        .header-action-btn .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 800;
            min-width: 16px;
            height: 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid #ffffff;
        }
        .profile-avatar-container {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(14, 165, 233, 0.2);
            transition: border-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.02);
        }
        .profile-trigger-btn {
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            cursor: pointer;
            display: flex;
            align-items: center;
            position: relative;
        }
        .profile-trigger-btn:hover .profile-avatar-container {
            border-color: #0ea5e9;
        }
        .profile-chevron-badge {
            position: absolute;
            bottom: -3px;
            right: -3px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 2px solid #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
        }
    </style>
</head>
<body>

    <!-- Sticky Glass Navigation Header -->
    <header class="glass-nav">
        <div class="container nav-wrapper">
            <a href="/" class="logo">
                <span>🗺️</span> DongAnh Map Discovery
            </a>
            
            <div class="nav-collapse main-nav-container" id="navCollapse">
                <nav>
                <ul class="nav-menu">
                    <li><a href="/" class="nav-link {{ request()->is('/') && !request()->has('cat') ? 'active' : '' }}">Trang chủ</a></li>
                    <li><a href="/tim-kiem" class="nav-link {{ request()->is('tim-kiem*') ? 'active' : '' }}">Bản đồ & Tìm kiếm</a></li>
                    <li><a href="/food-tours" class="nav-link {{ request()->is('food-tours*') || (request()->is('food-tour*') && !request()->is('food-tour/tu-tay-lam-dac-san-co-loa*')) ? 'active' : '' }}">Food Tour</a></li>
                    <li><a href="/exp-corner" class="nav-link {{ request()->is('exp-corner*') || request()->is('food-tour/tu-tay-lam-dac-san-co-loa*') ? 'active' : '' }}">Góc trải nghiệm thực tế</a></li>
                    <li><a href="/checkin" class="nav-link {{ request()->is('checkin*') ? 'active' : '' }}">Góc Check-in</a></li>
                    @if(session()->has('user_id'))
                        <li><a href="/social" class="nav-link {{ request()->is('social*') ? 'active' : '' }}">💬 Kết nối bạn bè</a></li>
                    @endif
                    <li><a href="#" onclick="openGuideModal(event)" class="nav-link">Giới thiệu & Hướng dẫn</a></li>
                </ul>
                </nav>
            
                <div class="user-actions" style="display: flex; align-items: center; gap: 10px;">
                    @if(session()->has('user_id'))
                        @php
                            $pendingCount = \App\Models\Friendship::where('friend_id', session('user_id'))
                                ->where('status', 'pending')
                                ->count();
                        @endphp
                        
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
                                            this.chats = data;
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
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="chat-dropdown-menu" 
                                 style="position: absolute; right: -50px; top: 100%; margin-top: 10px; width: 340px; background: var(--bg-card, #ffffff); border: 1px solid var(--border-glow, rgba(0,0,0,0.08)); border-radius: 20px; box-shadow: 0 12px 40px rgba(0,0,0,0.15); z-index: 10000; overflow: hidden; display: flex; flex-direction: column; text-align: left;">
                                
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
                                                    <img :src="chat.avatar_url" alt="avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
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

                        <!-- Nút Yêu cầu kết bạn (Thông báo) -->
                        <a href="/social" class="header-action-btn" title="Yêu cầu kết bạn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            @if($pendingCount > 0)
                                <span class="badge">{{ $pendingCount }}</span>
                            @endif
                        </a>

                        <div class="profile-dropdown" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="profile-trigger-btn">
                                @php $navUser = Auth::user() ?? \App\Models\User::find(session('user_id')); @endphp
                                <div class="profile-avatar-container">
                                    @if($navUser && $navUser->avatar && str_starts_with($navUser->avatar, 'avatars/'))
                                        <img src="{{ rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $navUser->avatar }}" alt="avatar" style="width: 100%; height: 100%; object-fit: cover;">
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
                            <div x-show="open" x-transition class="profile-dropdown-menu">
                                <div class="user-info-header">
                                    <div class="user-name">{{ session('user_name') }}</div>
                                    <div class="user-role">
                                        @if(session('user_role') === 'admin')
                                            🏛️ Quản trị viên
                                        @elseif(session('user_role') === 'seller')
                                            🏪 Chủ cơ sở kinh doanh
                                        @else
                                            👤 Thành viên cộng đồng
                                        @endif
                                    </div>
                                </div>
                                
                                @if(session('user_role') === 'admin' || session('user_role') === 'seller')
                                    <a href="/admin/dashboard" class="dropdown-item">
                                        <span>📊</span> Trang quản lý
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

    <!-- Main Content Slot -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3 class="logo" style="margin-bottom: 16px; font-size: 1.3rem;">🗺️ Dong Anh Map</h3>
                    <p style="font-size: 0.85rem; line-height: 1.6; max-width: 480px;">
                        Bản đồ số Đông Anh là giải pháp công nghệ số hóa toàn bộ trường học, bệnh viện, cơ sở y tế, khách sạn, nhà nghỉ, nhà hàng, quán cafe và quảng bá các sản phẩm OCOP đặc sản truyền thống của huyện Đông Anh, Hà Nội. Hỗ trợ chuyển đổi số và nâng tầm văn hóa du lịch địa phương.
                    </p>
                </div>
                <div>
                    <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem;">Liên kết nhanh</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px; font-size: 0.85rem;">
                        <li><a href="/" style="hover: color: var(--primary);">Trang chủ</a></li>
                        <li><a href="/tim-kiem" style="hover: color: var(--primary);">Bản đồ số</a></li>
                        <li><a href="#" onclick="openGuideModal(event)" style="hover: color: var(--primary);">Giới thiệu & Hướng dẫn</a></li>
                        <li><a href="/auth/login" style="hover: color: var(--primary);">Đăng nhập quản trị viên</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem;">Thông tin hành chính</h4>
                    <p style="font-size: 0.85rem; line-height: 1.6;">
                        <strong>Cơ quan chủ quản:</strong> Uỷ ban nhân dân xã Đông Anh<br>
                        <strong>Chịu trách nhiệm chính:</strong> Phòng Văn hóa - Xã hội xã Đông Anh<br>
                        📍 Địa chỉ: Số 66 đường Cao Lỗ, xã Đông Anh, thành phố Hà Nội<br>
                        📞 Điện thoại: 0243.965.2973<br>
                        🌐 Website: <a href="https://donganh.hanoi.gov.vn" target="_blank" style="color: inherit; text-decoration: none;">donganh.hanoi.gov.vn</a>
                    </p>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.3);">
                &copy; 2026 Bản đồ số Đông Anh (Dong Anh Map). Tất cả quyền được bảo lưu. Phát triển bởi Phòng Văn hóa - Xã hội xã Đông Anh
            </div>
        </div>
    </footer>

    <!-- Leaflet.js Map Library -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Hamburger Menu Logic
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const navCollapse = document.getElementById('navCollapse');
            
            if (mobileMenuBtn && navCollapse) {
                mobileMenuBtn.addEventListener('click', function() {
                    this.classList.toggle('open');
                    navCollapse.classList.toggle('show');
                });
            }

            // Scroll-triggered animations with Intersection Observer
            const observerOptions = {
                root: null,
                rootMargin: "0px",
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            revealElements.forEach(el => observer.observe(el));

            // Button Ripple Micro-interaction
            document.addEventListener('click', function(e) {
                const target = e.target.closest('.btn-primary, .btn-secondary, .btn-accent, .category-card');
                if (target) {
                    const rect = target.getBoundingClientRect();
                    const ripple = document.createElement('span');
                    ripple.className = 'ripple-effect';
                    
                    const size = Math.max(rect.width, rect.height);
                    ripple.style.width = ripple.style.height = `${size}px`;
                    
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    target.style.position = 'relative';
                    target.style.overflow = 'hidden';
                    target.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                }
            });

            // Smooth Scroll state triggers for Nav and Parallax
            const handleScroll = () => {
                const nav = document.querySelector('.glass-nav');
                if (nav) {
                    if (window.scrollY > 20) {
                        nav.classList.add('scrolled');
                    } else {
                        nav.classList.remove('scrolled');
                    }
                }
                // Update parallax css variable
                document.documentElement.style.setProperty('--scroll-offset', window.scrollY);
            };

            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll(); // Run once on startup
        });

        window.openGuideModal = function(e) {
            if (e) e.preventDefault();
            const modal = document.getElementById('guideModal');
            if (modal) {
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.style.opacity = '1';
                    modal.querySelector('.lightbox-content').style.transform = 'scale(1)';
                }, 10);
            }
        };

        window.closeGuideModal = function() {
            const modal = document.getElementById('guideModal');
            if (modal) {
                modal.style.opacity = '0';
                modal.querySelector('.lightbox-content').style.transform = 'scale(0.9)';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        };
    </script>
    
    <!-- Beautiful Guide & Introduction Modal -->
    <div id="guideModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(12px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div class="lightbox-content" style="background: var(--bg-card); border: 1px solid var(--border-glow); width: 90%; max-width: 650px; max-height: 85vh; border-radius: 24px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5); overflow: hidden; transform: scale(0.9); transition: transform 0.3s ease; display: flex; flex-direction: column; position: relative;">
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border-glow); padding: 20px 24px; background: rgba(255,255,255,0.01);">
                <h3 style="margin: 0; font-size: 1.4rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px; font-family: var(--font-heading);">
                    ℹ️ Giới thiệu & Hướng dẫn sử dụng
                </h3>
                <button onclick="closeGuideModal()" style="background: transparent; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">✕</button>
            </div>
            
            <!-- Modal Content (Scrollable) -->
            <div style="overflow-y: auto; padding: 24px 28px; flex: 1; display: flex; flex-direction: column; gap: 24px; line-height: 1.6; color: var(--text-muted);">
                <!-- Section 1: Giới thiệu Đông Anh -->
                <div>
                    <h4 style="color: var(--text-main); font-size: 1.1rem; margin-top: 0; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        🌾 Giới thiệu về Đông Anh
                    </h4>
                    <p style="font-size: 0.9rem; margin: 0;">
                        Đông Anh là vùng đất địa linh nhân kiệt có bề dày lịch sử và truyền thống văn hóa lâu đời, gắn liền với di tích Cổ Loa thành. Hiện nay, Đông Anh đang chuyển mình mạnh mẽ trong tiến trình đô thị hóa và chuyển đổi số. Bản đồ số Đông Anh ra đời nhằm cung cấp giải pháp số hóa toàn diện các hạ tầng dịch vụ: trường học, y tế, lưu trú, ẩm thực địa phương và các sản phẩm OCOP đặc trưng của huyện, hỗ trợ nâng cao đời sống dân cư và thúc đẩy phát triển du lịch bền vững.
                    </p>
                </div>
                
                <!-- Section 2: Hướng dẫn sử dụng -->
                <div>
                    <h4 style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        🗺️ Hướng dẫn sử dụng bản đồ
                    </h4>
                    
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="display: flex; gap: 12px;">
                            <span style="font-size: 1.2rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">1</span>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">Tìm kiếm địa điểm</strong>
                                <span style="font-size: 0.85rem;">Nhập từ khóa như tên trường học, bệnh viện, khách sạn, món ăn tại ô tìm kiếm ở trang chủ để hiển thị vị trí trên bản đồ vệ tinh.</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 12px;">
                            <span style="font-size: 1.2rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">2</span>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">Lọc nâng cao & Bán kính GPS</strong>
                                <span style="font-size: 0.85rem;">Trong phần "Bản đồ & Tìm kiếm", bạn có thể kích hoạt định vị GPS thực tế để quét các tiện ích xung quanh mình trong phạm vi từ 1km đến 10km.</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 12px;">
                            <span style="font-size: 1.2rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">3</span>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">Góc trải nghiệm thực tế</strong>
                                <span style="font-size: 0.85rem;">Tham gia các lớp học nghề, hoạt động vui chơi giải trí và trải nghiệm thực tế các ngành nghề, văn hóa truyền thống Đông Anh.</span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <span style="font-size: 1.2rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">4</span>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">Gửi phản hồi chất lượng dịch vụ</strong>
                                <span style="font-size: 0.85rem;">Nếu phát hiện thông tin địa điểm không chính xác hoặc dịch vụ không đạt chất lượng/an toàn vệ sinh, hãy nhấn "Báo cáo / Góp ý" trực tiếp tại trang chi tiết để gửi thông tin ẩn danh đến Ban quản lý.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="border-top: 1px dashed var(--border-glow); padding: 16px 24px; background: rgba(255,255,255,0.01); display: flex; justify-content: flex-end;">
                <button onclick="closeGuideModal()" class="btn-primary" style="font-size: 0.85rem; padding: 8px 20px; border-radius: 8px; cursor: pointer;">Đã hiểu</button>
            </div>
        </div>
    </div>
    
    @yield('scripts')

    @if(session()->has('user_id'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function sendHeartbeat() {
                fetch('/user/heartbeat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                }).catch(function(err) {
                    console.warn('Heartbeat update skipped', err);
                });
            }
            sendHeartbeat();
            setInterval(sendHeartbeat, 60000); // 1 minute
        });
    </script>
    @endif

    @if(session()->has('user_id'))
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <!-- Messenger-style Floating Chat Widget (Translucent Light Theme) -->
    <style>
        .fchat-window {
            pointer-events: auto;
            width: 310px;
            background: #e8f4fd; /* Matches App.tsx background */
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px 16px 0 0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: height 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Be Vietnam Pro', -apple-system, sans-serif;
        }
        .fchat-header {
            background: #ffffff;
            padding: 10px 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.07);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
            flex-shrink: 0;
        }
        .fchat-header:hover { background: #f8fafc; }
        .fchat-header-btn {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: transparent;
            border: none;
            color: #0084ff;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.15s, color 0.15s;
            font-size: 0.8rem;
        }
        .fchat-header-btn:hover { background: #eff6ff; }
        .fchat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: #e8f4fd;
            -webkit-overflow-scrolling: touch;
        }
        .fchat-messages::-webkit-scrollbar { width: 0px; display: none; }
        .fchat-input-bar {
            background: transparent !important;
            border: none !important;
            color: #111827 !important;
            font-size: 0.875rem !important;
            padding: 0 !important;
            outline: none !important;
            width: 100% !important;
        }
        .fchat-input-bar::placeholder { color: #9ca3af; }
        .fchat-input-container {
            flex: 1;
            display: flex;
            align-items: center;
            border-radius: 9999px;
            padding: 6px 12px;
            background: #f0f2f5;
        }
        .fchat-footer {
            padding: 8px;
            background: #ffffff;
            border-top: 1px solid rgba(0, 0, 0, 0.07);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Message Bubble Specific Classes - mirrored from Beautiful Floating Chat */
        .fchat-msg-row {
            display: flex;
            align-items: flex-end;
            gap: 6px;
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
            /* NO flex here — flex-column stretches children to full width */
            display: block;
            max-width: 72%;
        }
        .fchat-bubble {
            display: inline-block !important;   /* shrinks to text, overrides any global CSS */
            width: auto !important;
            max-width: 100% !important;
            padding: 7px 12px !important;
            font-size: 0.875rem !important;
            line-height: 1.4 !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            white-space: normal !important;     /* collapse spaces, don't preserve them */
            box-sizing: border-box !important;
        }
        .fchat-bubble-sent {
            background: #0084ff;
            color: #ffffff;
            border-radius: 18px 18px 4px 18px;
        }
        .fchat-bubble-received {
            background: #e4e6eb;
            color: #050505;
            border-radius: 18px 18px 18px 4px;
        }
        .fchat-avatar-slot {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
    </style>

    <div id="floating-chat-container" 
         x-data 
         style="position: fixed; bottom: 24px; right: 24px; display: flex; gap: 14px; z-index: 99999; align-items: flex-end; pointer-events: none; font-family: 'Be Vietnam Pro', sans-serif;">
        
        <template x-for="(chat, index) in $store.chatStore.openChats" :key="chat.id">
            <div class="fchat-window"
                 :style="chat.is_minimized ? 'height: 52px;' : 'height: 520px;'">
                
                <!-- Chat Header -->
                <div class="fchat-header" @click="chat.is_minimized = !chat.is_minimized">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                        <!-- Avatar with online dot -->
                        <div style="position: relative; width: 32px; height: 32px; flex-shrink: 0;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #fff3cd; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <template x-if="chat.avatar_url">
                                    <img :src="chat.avatar_url" alt="avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                </template>
                                <template x-if="!chat.avatar_url">
                                    <span x-text="chat.avatar || '👤'"></span>
                                </template>
                            </div>
                            <span x-show="chat.is_online" style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; border-radius: 50%; background: #31a24c; border: 2px solid #ffffff;"></span>
                        </div>
                        <div style="display: flex; flex-direction: column; min-width: 0;">
                            <span style="font-size: 0.875rem; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;" x-text="chat.name"></span>
                            <span style="font-size: 0.75rem; line-height: 1.2;" :style="chat.is_online ? 'color: #31a24c;' : 'color: #6b7280;'" x-text="chat.is_online ? 'Đang hoạt động' : 'Ngoại tuyến'"></span>
                        </div>
                    </div>

                    <!-- Header action buttons -->
                    <div style="display: flex; align-items: center; gap: 2px; flex-shrink: 0;" @click.stop>
                        <button class="fchat-header-btn" title="Gọi thoại">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/></svg>
                        </button>
                        <button class="fchat-header-btn" title="Gọi video">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                        </button>
                        <button class="fchat-header-btn" @click="chat.is_minimized = !chat.is_minimized" title="Thu nhỏ" style="color: #6b7280;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13H5v-2h14v2z"/></svg>
                        </button>
                        <button class="fchat-header-btn" @click="$store.chatStore.closeChat(chat.id)" title="Đóng" style="color: #6b7280;" onmouseover="this.style.color='#f44336'" onmouseout="this.style.color='#6b7280'">✕</button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="fchat-messages" :id="'floating-chat-messages-' + chat.id" x-show="!chat.is_minimized">
                    <!-- Loading -->
                    <div x-show="chat.loading" style="display: flex; justify-content: center; align-items: center; height: 100%; color: #6b7280; font-size: 0.78rem; gap: 6px;">
                        <span style="display: inline-block; animation: spin 1s linear infinite;">⏳</span> Đang tải...
                    </div>

                    <!-- Empty state -->
                    <div x-show="!chat.loading && chat.messages.length === 0" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 8px; color: #6b7280;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                            <template x-if="chat.avatar_url"><img :src="chat.avatar_url" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"></template>
                            <template x-if="!chat.avatar_url"><span x-text="chat.avatar || '👤'"></span></template>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 600; color: #111827;" x-text="chat.name"></span>
                        <span style="font-size: 0.68rem;">Hãy gửi lời chào đầu tiên! 👋</span>
                    </div>

                    <!-- Message bubbles -->
                    <template x-show="!chat.loading" x-for="(msg, mi) in chat.messages" :key="msg.id">
                        <div>
                            <!-- Timestamp separator if first message -->
                            <template x-if="mi === 0">
                                <div style="text-align: center; margin: 4px 0 10px; font-size: 0.65rem; color: #6b7280;" x-text="msg.created_at_format || ''"></div>
                            </template>

                            <!-- Message Row: row-reverse = sent (right), row = received (left) -->
                            <div class="fchat-msg-row" :class="msg.sender_id == {{ session('user_id') }} ? 'sent' : 'received'">
                                
                                <!-- Avatar slot for received messages -->
                                <template x-if="msg.sender_id != {{ session('user_id') }}">
                                    <div class="fchat-avatar-slot">
                                        <!-- Show avatar only for the last consecutive received message -->
                                        <template x-if="!(chat.messages[mi+1] && chat.messages[mi+1].sender_id != {{ session('user_id') }})">
                                            <div style="width: 24px; height: 24px; border-radius: 50%; background: #fff3cd; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                                <template x-if="chat.avatar_url">
                                                    <img :src="chat.avatar_url" style="width: 100%; height: 100%; object-fit: cover;">
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
                                        <div style="border-radius: 12px; overflow: hidden; border: 1px solid rgba(0,0,0,0.08);">
                                            <template x-if="msg.media_type === 'image'">
                                                <img :src="msg.media_path" style="width: 100%; max-height: 180px; object-fit: cover; display: block; cursor: pointer;" @click="window.open(msg.media_path)">
                                            </template>
                                            <template x-if="msg.media_type === 'video'">
                                                <video :src="msg.media_path" controls style="width: 100%; max-height: 180px; display: block;"></video>
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
                <div class="fchat-footer" x-show="!chat.is_minimized">
                    <button type="button" class="fchat-header-btn shrink-0" style="width: 32px; height: 32px;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                    </button>
                    <button type="button" @click="document.getElementById('floating-file-input-' + chat.id).click()" class="fchat-header-btn shrink-0" style="width: 32px; height: 32px;" title="Gửi tệp đính kèm">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </button>
                    <input type="file" :id="'floating-file-input-' + chat.id" style="display: none;" accept="image/*,video/*" @change="$store.chatStore.uploadFile(chat.id, $event)">

                    <!-- Input Area -->
                    <div class="fchat-input-container">
                        <input type="text" 
                               :id="'floating-chat-input-' + chat.id" 
                               placeholder="Aa" 
                               x-model="chat.inputText" 
                               @keydown.enter.prevent="$store.chatStore.sendSubmitMessage(chat.id)"
                               autocomplete="off" 
                               class="fchat-input-bar">
                    </div>
                    
                    <!-- Actions -->
                    <div style="flex-shrink: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <!-- 👍 Like -->
                        <button type="button" x-show="!chat.inputText || !chat.inputText.trim()" @click="$store.chatStore.sendMessage(chat.id, '👍')" class="fchat-header-btn shrink-0" style="width: 32px; height: 32px;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                        </button>
                        <!-- 🚀 Send -->
                        <button type="button" @click.prevent="$store.chatStore.sendSubmitMessage(chat.id)" x-show="chat.inputText && chat.inputText.trim()" class="fchat-header-btn shrink-0" style="width: 32px; height: 32px;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
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

                sendSubmitMessage(friendId) {
                    const chat = this.openChats.find(c => c.id === friendId);
                    if (!chat || !chat.inputText.trim()) return;
                    
                    const text = chat.inputText;
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

    <!-- Shopping Cart Sidebar Drawer & Toast Container -->
    <div id="cartToastContainer" style="position: fixed; top: 90px; right: 24px; z-index: 9999999; display: flex; flex-direction: column; gap: 8px; pointer-events: none;"></div>

    <style>
        #cartDrawer { font-family: 'Be Vietnam Pro', sans-serif; }
        #cartDrawerList { gap: 0; }
        .cart-group-header {
            background: linear-gradient(135deg, rgba(0, 168, 107, 0.08), rgba(0, 168, 107, 0.04));
            border-bottom: 1px solid rgba(0, 168, 107, 0.15);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: 0.2px;
        }
        .cart-item-row {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(0,0,0,0.035);
            transition: background 0.15s;
        }
        .cart-item-row:last-child { border-bottom: none; }
        .cart-item-row:hover { background: rgba(0, 168, 107, 0.025); }
        .cart-group-block {
            border: 1px solid rgba(0, 168, 107, 0.15);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 12px;
            background: var(--bg-card, #fff);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .cart-item-img {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .cart-qty-btn {
            border: none;
            background: rgba(0, 168, 107, 0.1);
            color: var(--primary, #00A86B);
            cursor: pointer;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            flex-shrink: 0;
        }
        .cart-qty-btn:hover { background: var(--primary, #00A86B); color: white; }
        .cart-select-all-bar {
            background: linear-gradient(135deg, rgba(0, 168, 107, 0.06), rgba(0, 168, 107, 0.02));
            border: 1.5px solid rgba(0, 168, 107, 0.18);
            border-radius: 14px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            cursor: pointer;
        }
        .cart-checkbox {
            accent-color: var(--primary, #00A86B);
            cursor: pointer;
            width: 17px;
            height: 17px;
            margin: 0;
            flex-shrink: 0;
        }
        @keyframes cartFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cart-group-block { animation: cartFadeIn 0.2s ease both; }
        .cart-toast-item {
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            gap: 8px;
            pointer-events: auto;
            min-width: 240px;
            border: 1px solid rgba(255,255,255,0.2);
        }
    </style>

    <div id="cartDrawerOverlay" onclick="toggleCartDrawer()" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); backdrop-filter: blur(6px); z-index: 999998; opacity: 0; transition: opacity 0.3s ease;"></div>

    <div id="cartDrawer" style="position: fixed; top: 0; right: -440px; width: 440px; max-width: 100vw; height: 100vh; background: var(--bg-card, #f8f9fa); z-index: 999999; box-shadow: -20px 0 60px rgba(0,0,0,0.15); transition: right 0.35s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column;">

        <!-- Premium Drawer Header -->
        <div style="padding: 0; background: linear-gradient(135deg, #00C897 0%, #00A86B 100%); position: relative; overflow: hidden; flex-shrink: 0;">
            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.08);"></div>
            <div style="position: absolute; bottom: -30px; left: 20px; width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.06);"></div>
            <div style="padding: 20px 24px; position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                        🛒 Giỏ hàng mua sắm
                    </h3>
                    <p id="cartHeaderSubtitle" style="margin: 4px 0 0 0; font-size: 0.78rem; color: rgba(255,255,255,0.8); font-weight: 500;">Đang tải...</p>
                </div>
                <button onclick="toggleCartDrawer()" style="background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 50%; color: #ffffff; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s; backdrop-filter: blur(4px);" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    ✕
                </button>
            </div>
        </div>

        <!-- Drawer Body -->
        <div id="cartDrawerBody" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; scrollbar-width: thin; scrollbar-color: rgba(0, 168, 107, 0.3) transparent;">
            <!-- Loading -->
            <div id="cartDrawerLoading" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; color: var(--text-muted); gap: 14px;">
                <div style="width: 48px; height: 48px; border: 3px solid rgba(0, 168, 107, 0.15); border-top-color: #00A86B; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <span style="font-size: 0.88rem; font-weight: 600;">Đang tải giỏ hàng...</span>
            </div>
            <!-- Empty state -->
            <div id="cartDrawerEmpty" style="display: none; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 16px; text-align: center; color: var(--text-muted); padding: 20px;">
                <div style="width: 96px; height: 96px; border-radius: 50%; background: linear-gradient(135deg, rgba(0, 168, 107, 0.1), rgba(0, 168, 107, 0.05)); display: flex; align-items: center; justify-content: center; font-size: 3rem;">🛒</div>
                <div>
                    <h4 style="margin: 0 0 6px 0; color: var(--text-main); font-weight: 800; font-size: 1.05rem;">Giỏ hàng trống</h4>
                    <p style="margin: 0; font-size: 0.82rem; max-width: 240px; line-height: 1.6;">Hãy thêm các món ngon từ thực đơn nhà hàng hoặc đặc sản OCOP Đông Anh nhé!</p>
                </div>
                <button onclick="toggleCartDrawer()" style="border: 2px solid rgba(0, 168, 107, 0.3); background: rgba(0, 168, 107, 0.05); color: #00A86B; padding: 10px 24px; border-radius: 12px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,168,107,0.1)'" onmouseout="this.style.background='rgba(0,168,107,0.05)'">
                    Khám phá ngay →
                </button>
            </div>
            <!-- Item List wrapper -->
            <div id="cartDrawerList" style="display: none; flex-direction: column;"></div>
        </div>

        <!-- Premium Drawer Footer -->
        <div id="cartDrawerFooter" style="display: none; flex-direction: column; flex-shrink: 0; background: var(--bg-card, #ffffff); border-top: 1px solid rgba(0,0,0,0.06); box-shadow: 0 -8px 24px rgba(0,0,0,0.06);">
            <!-- Total summary -->
            <div style="padding: 14px 20px 10px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.78rem; font-weight: 600; display: block;">Tổng tiền đã chọn</span>
                    <strong id="cartDrawerTotal" style="color: #FF7A00; font-size: 1.4rem; font-weight: 900; font-family: var(--font-heading); letter-spacing: -0.5px;">0đ</strong>
                </div>
                <div id="cartSelectedInfo" style="text-align: right; font-size: 0.78rem; color: var(--text-muted); font-weight: 600;"></div>
            </div>
            <!-- Action buttons -->
            <div style="padding: 0 16px 16px 16px; display: flex; gap: 10px;">
                <button onclick="clearCart()" style="flex: 1; padding: 12px 8px; font-size: 0.82rem; border-radius: 12px; font-weight: 700; border: 1.5px solid rgba(239, 68, 68, 0.25); color: #ef4444; background: rgba(239, 68, 68, 0.04); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.borderColor='rgba(239,68,68,0.5)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.04)'; this.style.borderColor='rgba(239,68,68,0.25)'">
                    🗑️ Xóa tất cả
                </button>
                <a href="/checkout" id="cartDrawerCheckoutBtn" style="flex: 2; padding: 12px 8px; font-size: 0.88rem; border-radius: 12px; text-align: center; text-decoration: none; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: linear-gradient(135deg, #FF9F43, #FF7A00); color: white; box-shadow: 0 6px 20px rgba(255, 122, 0, 0.3); transition: all 0.2s; border: none;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 8px 28px rgba(255,122,0,0.45)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 20px rgba(255,122,0,0.3)'">
                    🚀 Đặt hàng ngay
                </a>
            </div>
        </div>
    </div>

    <script>
        // Global Shopping Cart Client API
        let isCartDrawerOpen = false;

        function toggleCartDrawer() {
            const drawer = document.getElementById('cartDrawer');
            const overlay = document.getElementById('cartDrawerOverlay');
            if (!drawer || !overlay) return;

            isCartDrawerOpen = !isCartDrawerOpen;
            if (isCartDrawerOpen) {
                drawer.style.right = '0';
                overlay.style.display = 'block';
                setTimeout(() => overlay.style.opacity = '1', 10);
                loadCart();
            } else {
                drawer.style.right = '-440px';
                overlay.style.opacity = '0';
                setTimeout(() => overlay.style.display = 'none', 300);
            }
        }

        function showCartToast(message, type = 'success') {
            const container = document.getElementById('cartToastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'cart-toast-item';
            if (type === 'success') {
                toast.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
                toast.style.color = '#ffffff';
            } else {
                toast.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                toast.style.color = '#ffffff';
            }
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
            toast.innerHTML = (type === 'success' ? '<span style="font-size:1.1rem">✅</span>' : '<span style="font-size:1.1rem">⚠️</span>') + message;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            }, 10);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(120%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 350);
            }, 3000);
        }

        function formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(num) + ' đ';
        }

        function updateCartBadge(count) {
            const badge = document.getElementById('floatingCartCount');
            if (badge) {
                if (count > 0) {
                    badge.innerText = count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
            const headerBadge = document.getElementById('headerCartCount');
            if (headerBadge) {
                if (count > 0) {
                    headerBadge.innerText = count;
                    headerBadge.style.display = 'flex';
                } else {
                    headerBadge.style.display = 'none';
                }
            }
        }

        let selectedCartItems = [];
        let globalCartResponse = null;

        function toggleSelectAllCart(cb) {
            if (!globalCartResponse || !globalCartResponse.data) return;
            if (cb.checked) {
                selectedCartItems = globalCartResponse.data.map(item => item.id);
            } else {
                selectedCartItems = [];
            }
            renderGroupedCart();
        }

        function toggleSelectGroup(cb, groupKey) {
            if (!globalCartResponse || !globalCartResponse.data) return;
            const groupItemIds = globalCartResponse.data
                .filter(item => {
                    const eId = item.eatery_id || 0;
                    const itemKey = item.stall_name ? `${eId}_${item.stall_name}` : String(eId);
                    return String(itemKey) === String(groupKey);
                })
                .map(item => item.id);

            if (cb.checked) {
                groupItemIds.forEach(id => {
                    if (!selectedCartItems.includes(id)) {
                        selectedCartItems.push(id);
                    }
                });
            } else {
                selectedCartItems = selectedCartItems.filter(id => !groupItemIds.includes(id));
            }
            renderGroupedCart();
        }

        function toggleSelectItem(cb, itemId) {
            if (cb.checked) {
                if (!selectedCartItems.includes(itemId)) {
                    selectedCartItems.push(itemId);
                }
            } else {
                selectedCartItems = selectedCartItems.filter(id => id !== itemId);
            }
            renderGroupedCart();
        }

        function renderGroupedCart() {
            const list = document.getElementById('cartDrawerList');
            const footer = document.getElementById('cartDrawerFooter');
            const subtitle = document.getElementById('cartHeaderSubtitle');
            const selectedInfo = document.getElementById('cartSelectedInfo');
            if (!list || !globalCartResponse) return;

            list.innerHTML = '';

            const totalItemsCount = globalCartResponse.data.length;
            const selectedItemsCount = selectedCartItems.length;
            const isAllSelected = totalItemsCount > 0 && selectedItemsCount === totalItemsCount;
            const isIndeterminate = selectedItemsCount > 0 && selectedItemsCount < totalItemsCount;

            // Update header subtitle
            if (subtitle) {
                subtitle.textContent = `${totalItemsCount} sản phẩm • ${selectedItemsCount} đã chọn`;
            }

            // Render Select All Bar
            const selectAllHtml = `
                <div class="cart-select-all-bar" onclick="document.getElementById('selectAllCart').click()">
                    <input type="checkbox" id="selectAllCart" onchange="toggleSelectAllCart(this)" ${isAllSelected ? 'checked' : ''} class="cart-checkbox" onclick="event.stopPropagation()">
                    <label for="selectAllCart" style="cursor: pointer; user-select: none; font-size: 0.88rem; font-weight: 700; color: var(--text-main); flex: 1;" onclick="event.stopPropagation()">
                        ✅ Chọn tất cả <span style="color: var(--primary); font-weight: 800;">(${selectedItemsCount}/${totalItemsCount} món)</span>
                    </label>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', selectAllHtml);

            // Group items by eatery_id and stall_name
            const groups = {};
            globalCartResponse.data.forEach(item => {
                const eId = item.eatery_id || 0;
                const key = item.stall_name ? `${eId}_${item.stall_name}` : String(eId);
                if (!groups[key]) {
                    const name = item.stall_name ? `${item.eatery_name || 'Chợ'} - ${item.stall_name}` : (item.eatery_name || 'Khác');
                    groups[key] = { name: name, items: [] };
                }
                groups[key].items.push(item);
            });

            // Render groups with stagger animation
            let groupIndex = 0;
            Object.keys(groups).forEach(key => {
                const group = groups[key];
                const groupItemIds = group.items.map(item => item.id);
                const isGroupAllSelected = groupItemIds.every(id => selectedCartItems.includes(id));
                const groupSelectedCount = groupItemIds.filter(id => selectedCartItems.includes(id)).length;
                const delay = groupIndex * 0.06;

                let groupHtml = `
                    <div class="cart-group-block" style="animation-delay: ${delay}s;">
                        <div class="cart-group-header" onclick="document.getElementById('groupCb_${key}').click(); event.preventDefault();">
                            <input type="checkbox" id="groupCb_${key}" onchange="toggleSelectGroup(this, '${key}')" ${isGroupAllSelected ? 'checked' : ''} class="cart-checkbox" onclick="event.stopPropagation()">
                            <span style="flex: 1;">🏪 ${group.name}</span>
                            <span style="background: rgba(0, 168, 107, 0.12); color: var(--primary, #00A86B); font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 700; flex-shrink: 0;">
                                ${groupSelectedCount}/${group.items.length}
                            </span>
                        </div>
                `;

                group.items.forEach(item => {
                    const isChecked = selectedCartItems.includes(item.id);
                    groupHtml += `
                        <div class="cart-item-row" id="cart-row-${item.id}" style="${isChecked ? '' : 'opacity: 0.6;'}">
                            <input type="checkbox" onchange="toggleSelectItem(this, ${item.id})" ${isChecked ? 'checked' : ''} class="cart-checkbox">
                            <img src="${item.image}" class="cart-item-img" alt="${item.name}"
                                onerror="this.src='https://placehold.co/56x56/00A86B/ffffff?text=%F0%9F%9B%92'">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 0.84rem; font-weight: 700; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 3px;" title="${item.name}">${item.name}</div>
                                <div style="font-size: 0.78rem; color: #FF7A00; font-weight: 800;">${formatMoney(item.price)}</div>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0;">
                                <div style="display: flex; align-items: center; gap: 6px; background: rgba(0, 168, 107, 0.06); border: 1px solid rgba(0, 168, 107, 0.15); border-radius: 8px; padding: 3px 6px;">
                                    <button class="cart-qty-btn" data-item="${item.id}" data-action="dec"
                                        onclick="updateQty(${item.id}, parseInt(document.getElementById('cart-qty-${item.id}').dataset.qty) - 1)">
                                        ${item.quantity > 1 ? '−' : '🗑'}
                                    </button>
                                    <span id="cart-qty-${item.id}" data-qty="${item.quantity}"
                                        style="font-size: 0.82rem; font-weight: 800; min-width: 18px; text-align: center; color: var(--text-main); display: inline-block; transition: transform 0.15s, color 0.15s;">
                                        ${item.quantity}
                                    </span>
                                    <button class="cart-qty-btn" data-item="${item.id}" data-action="inc"
                                        onclick="updateQty(${item.id}, parseInt(document.getElementById('cart-qty-${item.id}').dataset.qty) + 1)">+</button>
                                </div>
                                <strong id="cart-subtotal-${item.id}" style="font-size: 0.82rem; font-weight: 800; color: var(--text-main); display: inline-block; transition: transform 0.15s, color 0.15s;">${formatMoney(item.price * item.quantity)}</strong>
                            </div>
                        </div>
                    `;
                });

                groupHtml += `</div>`;
                list.insertAdjacentHTML('beforeend', groupHtml);
                groupIndex++;
            });

            list.style.display = 'flex';

            // Update selected total & footer
            const selectedTotal = calculateSelectedTotal();
            const totalEl = document.getElementById('cartDrawerTotal');
            if (totalEl) totalEl.innerText = formatMoney(selectedTotal);

            if (selectedInfo) {
                selectedInfo.innerHTML = selectedItemsCount > 0
                    ? `<span style="color: #00A86B; font-weight: 800;">${selectedItemsCount}</span> / ${totalItemsCount} món`
                    : '<span style="color: #ef4444;">Chưa chọn món</span>';
            }

            const checkoutBtn = document.getElementById('cartDrawerCheckoutBtn');
            if (checkoutBtn) {
                if (selectedItemsCount > 0) {
                    checkoutBtn.setAttribute('href', '/checkout?items=' + selectedCartItems.join(','));
                    checkoutBtn.style.pointerEvents = 'auto';
                    checkoutBtn.style.opacity = '1';
                    checkoutBtn.style.filter = 'none';
                } else {
                    checkoutBtn.setAttribute('href', '#');
                    checkoutBtn.style.pointerEvents = 'none';
                    checkoutBtn.style.opacity = '0.5';
                    checkoutBtn.style.filter = 'grayscale(0.8)';
                }
            }

            if (footer) footer.style.display = 'flex';
        }

        function calculateSelectedTotal() {
            let total = 0;
            if (globalCartResponse && globalCartResponse.data) {
                globalCartResponse.data.forEach(item => {
                    if (selectedCartItems.includes(item.id)) {
                        total += item.price * item.quantity;
                    }
                });
            }
            return total;
        }

        function loadCart() {
            const loading = document.getElementById('cartDrawerLoading');
            const empty = document.getElementById('cartDrawerEmpty');
            const list = document.getElementById('cartDrawerList');
            const footer = document.getElementById('cartDrawerFooter');

            if (loading) loading.style.display = 'flex';
            if (empty) empty.style.display = 'none';
            if (list) list.style.display = 'none';
            if (footer) footer.style.display = 'none';

            fetch('/cart')
                .then(res => res.json())
                .then(res => {
                    if (loading) loading.style.display = 'none';
                    updateCartBadge(res.count);
                    globalCartResponse = res;

                    if (res.data.length === 0) {
                        if (empty) empty.style.display = 'flex';
                        selectedCartItems = [];
                        const subtitle = document.getElementById('cartHeaderSubtitle');
                        if (subtitle) subtitle.textContent = 'Giỏ hàng đang trống';
                        return;
                    }

                    // Select all items if selectedCartItems is empty
                    if (selectedCartItems.length === 0) {
                        selectedCartItems = res.data.map(item => item.id);
                    } else {
                        // filter out any item IDs that might have been removed
                        const currentIds = res.data.map(item => item.id);
                        selectedCartItems = selectedCartItems.filter(id => currentIds.includes(id));
                    }

                    renderGroupedCart();
                })
                .catch(err => {
                    console.error('Error loading cart', err);
                    if (loading) loading.style.display = 'none';
                    showCartToast('Không thể kết nối giỏ hàng', 'error');
                });
        }

        function addToCart(e, btn) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const id = btn.getAttribute('data-id');
            const type = btn.getAttribute('data-type'); // 'dish' or 'ocop_product'
            
            const payload = {
                quantity: 1
            };
            if (type === 'dish') {
                payload.dish_id = id;
            } else {
                payload.ocop_product_id = id;
            }

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳...';

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (res.success) {
                    showCartToast('Đã thêm sản phẩm vào giỏ hàng');
                    updateCartBadge(res.count);
                    
                    // Micro-animation: wiggle cart icon
                    const cartIcon = document.querySelector('.header-action-btn[title="Giỏ hàng"]');
                    if (cartIcon) {
                        cartIcon.style.transform = 'scale(1.2) rotate(-10deg)';
                        setTimeout(() => cartIcon.style.transform = 'none', 200);
                    }
                } else {
                    showCartToast(res.message || 'Lỗi thêm vào giỏ hàng', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                console.error(err);
                showCartToast('Lỗi đường truyền kết nối', 'error');
            });
        }

        function animateValue(el, newText, color) {
            if (!el) return;
            el.style.transform = 'scale(1.35)';
            el.style.color = color || 'var(--primary)';
            setTimeout(() => {
                el.textContent = newText;
            }, 80);
            setTimeout(() => {
                el.style.transform = 'scale(1)';
                el.style.color = '';
            }, 220);
        }

        function updateQty(itemId, newQty) {
            if (newQty <= 0) {
                removeItem(itemId);
                return;
            }

            const qtyEl    = document.getElementById(`cart-qty-${itemId}`);
            const totalEl  = document.getElementById(`cart-subtotal-${itemId}`);
            const drawerTotal = document.getElementById('cartDrawerTotal');
            const subtitle = document.getElementById('cartHeaderSubtitle');
            const selInfo  = document.getElementById('cartSelectedInfo');
            const decBtn   = document.querySelector(`[data-item="${itemId}"][data-action="dec"]`);

            // Cập nhật data model
            if (globalCartResponse && globalCartResponse.data) {
                const item = globalCartResponse.data.find(i => i.id === itemId);
                if (item) {
                    item.quantity = newQty;
                    item.total   = item.price * newQty;
                    globalCartResponse.count = globalCartResponse.data.reduce((s, i) => s + i.quantity, 0);

                    // ---- Cập nhật DOM tại chỗ, KHÔNG re-render ----
                    if (qtyEl) {
                        qtyEl.dataset.qty = newQty;
                        animateValue(qtyEl, newQty, '#0ea5e9');
                    }
                    if (totalEl) animateValue(totalEl, formatMoney(item.price * newQty), '#0284c7');

                    // Cập nhật nút trừ: đổi icon nếu cần
                    if (decBtn) decBtn.textContent = newQty > 1 ? '−' : '🗑';

                    // Cập nhật tổng footer
                    const newTotal = calculateSelectedTotal();
                    if (drawerTotal) animateValue(drawerTotal, formatMoney(newTotal), '#0ea5e9');

                    // Cập nhật subtitle & selectedInfo
                    const total = globalCartResponse.data.length;
                    const sel   = selectedCartItems.length;
                    if (subtitle) subtitle.textContent = `${total} sản phẩm • ${sel} đã chọn`;
                    if (selInfo) selInfo.innerHTML = sel > 0
                        ? `<span style="color:var(--primary);font-weight:800">${sel}</span> / ${total} món`
                        : '<span style="color:#ef4444">Chưa chọn món</span>';

                    updateCartBadge(globalCartResponse.count);
                }
            }

            // Gửi lên server nền
            fetch(`/cart/update/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newQty })
            })
            .then(res => res.json())
            .then(res => { if (!res.success) { showCartToast(res.message || 'Lỗi cập nhật', 'error'); loadCart(); } })
            .catch(() => loadCart());
        }

        function removeItem(itemId) {
            // ===== OPTIMISTIC UPDATE: xóa khỏi UI ngay =====
            if (globalCartResponse && globalCartResponse.data) {
                const idx = globalCartResponse.data.findIndex(i => i.id === itemId);
                if (idx !== -1) {
                    globalCartResponse.data.splice(idx, 1);
                    selectedCartItems = selectedCartItems.filter(id => id !== itemId);
                    globalCartResponse.total = globalCartResponse.data.reduce((s, i) => s + i.price * i.quantity, 0);
                    globalCartResponse.count = globalCartResponse.data.reduce((s, i) => s + i.quantity, 0);
                    updateCartBadge(globalCartResponse.count);

                    if (globalCartResponse.data.length === 0) {
                        document.getElementById('cartDrawerList').style.display = 'none';
                        document.getElementById('cartDrawerFooter').style.display = 'none';
                        document.getElementById('cartDrawerEmpty').style.display = 'flex';
                        const subtitle = document.getElementById('cartHeaderSubtitle');
                        if (subtitle) subtitle.textContent = 'Giỏ hàng đang trống';
                    } else {
                        renderGroupedCart();
                    }
                }
            }

            // Gửi request xóa lên server nền
            fetch(`/cart/remove/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showCartToast('Đã xóa sản phẩm khỏi giỏ');
                } else {
                    loadCart(); // fallback reload nếu lỗi
                }
            })
            .catch(err => {
                console.error(err);
                loadCart();
            });
        }

        function clearCart() {
            if (!confirm('Bạn có chắc muốn xóa sạch giỏ hàng?')) return;
            fetch('/cart/clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showCartToast('Đã xóa toàn bộ giỏ hàng');
                    loadCart();
                }
            })
            .catch(err => console.error(err));
        }

        // Initialize cart counts on boot
        document.addEventListener('DOMContentLoaded', () => {
            fetch('/cart')
                .then(res => res.json())
                .then(res => updateCartBadge(res.count))
                .catch(err => console.warn('Cart initialization skipped', err));
        });
    </script>

    <!-- Floating Dynamic Cart Button -->
    <div id="floatingCartButton" style="position: fixed; bottom: 24px; right: 24px; z-index: 99999; display: flex; align-items: center; justify-content: center;">
        <button onclick="toggleCartDrawer()" style="width: 56px; height: 56px; border-radius: 28px; background: linear-gradient(135deg, var(--primary, #ff7e29), #ef4444); border: none; color: #ffffff; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(255, 126, 41, 0.4); transition: transform 0.2s, box-shadow 0.2s; position: relative;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 12px 40px rgba(255, 126, 41, 0.5)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 8px 30px rgba(255, 126, 41, 0.4)'">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span id="floatingCartCount" style="display: none; position: absolute; top: -2px; right: -2px; background: #ef4444; color: #ffffff; font-size: 0.72rem; font-weight: 800; min-width: 18px; height: 18px; border-radius: 9px; align-items: center; justify-content: center; padding: 0 4px; border: 2px solid #ffffff;">0</span>
        </button>
    </div>

    {{-- Laravel Echo + Reverb WebSocket client — chỉ load trên trang cần real-time --}}
    @stack('realtime-scripts')
</body>
</html>
