<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <script>
        document.documentElement.setAttribute('data-theme', 'light');
    </script>
    
    <!-- SEO Meta Tags -->
    <title>@yield('title', 'Hành trình Ẩm thực Đông Anh - Dong Anh Map')</title>
    <meta name="description" content="@yield('meta_description', 'Khám phá hành trình ẩm thực, văn hóa và lịch sử Đông Anh với bản đồ số Cinematic thông minh.')">
    <link rel="canonical" href="@yield('canonical_url', request()->url())">
    
    <!-- Leaflet.js Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <!-- Google Fonts Preload: Outfit (heading) + Be Vietnam Pro (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 for modern popup alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Override native window.alert to render modern modal popups automatically across all food tour pages
        window.alert = function(message) {
            if (typeof Swal !== 'undefined') {
                const msg = String(message || '');
                const isError = /lỗi|thất bại|không thể|failed|error|violation|integrity/i.test(msg);
                const isSuccess = /thành công|chúc mừng|đã lưu|success/i.test(msg);
                Swal.fire({
                    title: isError ? '❌ Thông báo Lỗi' : (isSuccess ? '🎉 Thành công!' : '🔔 Thông báo'),
                    text: msg,
                    icon: isError ? 'error' : (isSuccess ? 'success' : 'info'),
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: isError ? '#DC2626' : (isSuccess ? '#10B981' : '#0EA5E9'),
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-gray-100 p-6',
                        confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-white shadow-md'
                    }
                });
            }
        };
    </script>

    <!-- Custom Theme Styling -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1.0.0' }}">
    
    <!-- Mobile Native Overrides (Only load for mobile and tablet screens) -->
    <link rel="stylesheet" media="screen and (max-width: 991px)" href="{{ asset('css/mobile-native.css') }}?v={{ file_exists(public_path('css/mobile-native.css')) ? filemtime(public_path('css/mobile-native.css')) : '1.0.0' }}">
    
    <!-- Alpine.js for Modern Client Interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Universal Integrated Breadcrumbs Component */
        .integrated-breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 16px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            color: #475569;
        }

        .breadcrumb-item-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 10px;
            color: #475569 !important;
            text-decoration: none;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(226, 232, 240, 0.9);
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .breadcrumb-item-link:hover {
            background: #ffffff;
            color: #059669 !important;
            border-color: #a7f3d0;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.1);
            transform: translateY(-1px);
        }

        .breadcrumb-arrow {
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            padding: 0 2px;
        }

        .breadcrumb-item-active {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 10px;
            color: #047857;
            font-weight: 700;
            background: #dcfce7;
            border: 1px solid #a7f3d0;
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

    @yield('styles')
</head>
<body style="min-height: 100vh; display: flex; flex-direction: column; background: var(--bg-base); color: var(--text-main); font-family: var(--font-body); margin: 0; padding: 0;">

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
                    <li><a href="/ban-tin" class="nav-link {{ request()->is('ban-tin*') ? 'active' : '' }}">📰 Bản tin</a></li>
                    <li><a href="/tim-kiem" class="nav-link {{ request()->is('tim-kiem*') ? 'active' : '' }}">Bản đồ & Tìm kiếm</a></li>
                    <li><a href="/food-tours" class="nav-link {{ request()->is('food-tours*') || (request()->is('food-tour*') && !request()->is('food-tour/tu-tay-lam-dac-san-co-loa*')) ? 'active' : '' }}">Food Tour</a></li>
                    <li><a href="/exp-corner" class="nav-link {{ request()->is('exp-corner*') || request()->is('food-tour/tu-tay-lam-dac-san-co-loa*') ? 'active' : '' }}">Góc trải nghiệm thực tế</a></li>
                    <li><a href="/checkin" class="nav-link {{ request()->is('checkin*') ? 'active' : '' }}">📸 Góc Check-in</a></li>
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
                        
                        <!-- Nút Chat -->
                        <a href="/social" class="header-action-btn" title="Tin nhắn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </a>

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
                            }
                        }" @click.outside="open = false" style="position: relative; display: flex; align-items: center;">
                            
                            <button @click="open = !open; if(open) fetchNotifications();" class="header-action-btn" title="Thông báo" style="outline: none; border: none; background: rgba(0, 0, 0, 0.05); cursor: pointer; position: relative;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                                <template x-if="items.length > 0">
                                    <span class="badge" style="position: absolute; top: -2px; right: -2px;" x-text="items.length"></span>
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
                                 class="notif-dropdown-menu" 
                                 style="position: absolute; right: 0; top: 100%; margin-top: 10px; width: 340px; background: var(--bg-card, #ffffff); border: 1px solid var(--border-glow, rgba(0,0,0,0.08)); border-radius: 20px; box-shadow: 0 12px 40px rgba(0,0,0,0.15); z-index: 10000; overflow: hidden; text-align: left; display: flex; flex-direction: column; white-space: normal;">
                                
                                <div style="padding: 16px 18px; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.015);">
                                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--text-main, #1e293b); font-family: var(--font-heading);">Thông báo</h4>
                                    <template x-if="items.length > 0">
                                        <span style="font-size: 0.78rem; color: #ea580c; font-weight: 700; background: rgba(234,88,12,0.1); padding: 3px 8px; border-radius: 12px;" x-text="items.length + ' mới'"></span>
                                    </template>
                                </div>

                                <div style="max-height: 380px; overflow-y: auto; display: flex; flex-direction: column; padding: 8px 0; -webkit-overflow-scrolling: touch;">
                                    <div x-show="loading" style="padding: 30px; text-align: center; color: var(--text-muted, #64748b); font-size: 0.88rem;">
                                        <span style="display: inline-block; animation: spin 1s linear infinite; margin-right: 6px;">⏳</span> Đang tải...
                                    </div>

                                    <template x-if="!loading && items.length > 0">
                                        <div style="display: flex; flex-direction: column;">
                                            <template x-for="item in items" :key="item.id">
                                                <a :href="item.target_url || item.url || (item.type === 'my_order' ? '/orders' : (item.type === 'seller_order' ? '/seller/orders' : (item.type === 'friend' ? '/social' : '/ban-tin')))" style="display: flex; align-items: flex-start; gap: 12px; padding: 12px 18px; text-decoration: none; color: inherit; border-bottom: 1px solid rgba(0,0,0,0.03); transition: background 0.15s; white-space: normal;" onmouseover="this.style.background='rgba(0,0,0,0.02)'" onmouseout="this.style.background='transparent'">
                                                    <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; background: rgba(14,165,233,0.1);">
                                                        <span x-text="item.type === 'reaction' ? '👍' : (item.type === 'comment' ? '💬' : (item.type === 'share' ? '🔄' : (item.type === 'review' ? '⭐' : (item.type === 'friend' ? '👥' : (item.type === 'new_post' ? '📣' : '📦')))))"></span>
                                                    </div>
                                                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; text-align: left;">
                                                        <span style="font-size: 0.84rem; font-weight: 800; color: var(--text-main, #0f172a); white-space: normal; word-break: break-word;" x-text="item.title"></span>
                                                        <span style="font-size: 0.8rem; color: var(--text-muted, #475569); line-height: 1.35; white-space: normal; word-break: break-word;" x-text="item.body"></span>
                                                        <span style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px; white-space: normal;" x-text="item.time"></span>
                                                    </div>
                                                </a>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="!loading && items.length === 0">
                                        <div style="padding: 40px 20px; text-align: center; color: var(--text-muted, #64748b); font-size: 0.88rem; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                            <span style="font-size: 2rem;">🔔</span>
                                            <span>Hiện chưa có thông báo mới nào.</span>
                                        </div>
                                    </template>
                                </div>

                                <div style="border-top: 1px solid rgba(0,0,0,0.06); text-align: center; background: rgba(0,0,0,0.015);">
                                    <a href="/checkin" style="display: block; padding: 12px; font-size: 0.82rem; color: var(--text-main, #1e293b); text-decoration: none; font-weight: 700;">
                                        Xem tất cả thông báo ➔
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="profile-dropdown" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="profile-trigger-btn">
                                @php $navUser = Auth::user() ?? \App\Models\User::find(session('user_id')); @endphp
                                <div class="profile-avatar-container">
                                    @if($navUser && $navUser->avatar && str_starts_with($navUser->avatar, 'avatars/'))
                                        <img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" src="{{ rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $navUser->avatar }}" alt="avatar" style="width: 100%; height: 100%; object-fit: cover;">
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
                                    $effectiveRole = session('user_role') ?: (Auth::check() ? Auth::user()->role : 'user');
                                @endphp
                                <div class="user-info-header">
                                    <div class="user-name">{{ session('user_name') ?: (Auth::check() ? Auth::user()->name : '') }}</div>
                                    <div class="user-role">
                                        @if($effectiveRole === 'admin')
                                            🏛️ Quản trị viên Tổng
                                        @elseif($effectiveRole === 'manager')
                                            🏛️ Ban Quản lý Chợ
                                        @elseif($effectiveRole === 'seller')
                                            🛍️ Chủ Gian Hàng / Cơ Sở
                                        @else
                                            👤 Thành viên cộng đồng
                                        @endif
                                    </div>
                                </div>
                                
                                @if($effectiveRole === 'admin' || $effectiveRole === 'manager')
                                    <a href="/admin/dashboard" class="dropdown-item" style="color: #0ea5e9; font-weight: 700; background: rgba(14, 165, 233, 0.06);">
                                        <span>⚙️</span> Trang Quản Trị Chợ
                                    </a>
                                @elseif($effectiveRole === 'seller')
                                    <a href="/seller/dashboard" class="dropdown-item" style="color: #10b981; font-weight: 700; background: rgba(16, 185, 129, 0.06);">
                                        <span>🛒</span> Kênh Quản Lý Gian Hàng
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

    <!-- Main Content Area -->
    <main style="flex: 1; display: flex; flex-direction: column; width: 100%;">
        @yield('content')
    </main>

    <!-- Optional Footer Slot (Rendered only on list pages, omitted on fullscreen maps) -->
    @yield('footer')

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
                        Đông Anh là vùng đất địa linh nhân kiệt có bề dày lịch sử và truyền thống văn hóa lâu đời, gắn liền với di tích Cổ Loa thành. Hiện nay, Đông Anh đang chuyển mình mạnh mẽ trong tiến trình đô thị hóa và chuyển đổi số. Bản đồ số Đông Anh ra đời nhằm cung cấp giải pháp số hóa toàn diện các hạ tầng dịch vụ: trường học, y tế, lưu trú, ẩm thực địa phương và các sản phẩm OCOP đặc trưng của xã, hỗ trợ nâng cao đời sống dân cư và thúc đẩy phát triển du lịch bền vững.
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
    
    <!-- Back to Top & Quick Controls Floating Widget -->
    <div class="floating-controls-widget">
        <button type="button" id="backToTopBtn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Về đầu trang">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 15l-6-6-6 6"></path>
            </svg>
        </button>
    </div>

    <script>
        // Back to top scroll listener
        window.addEventListener('scroll', () => {
            const btn = document.getElementById('backToTopBtn');
            if (btn) {
                if (window.scrollY > 300) {
                    btn.classList.add('visible');
                } else {
                    btn.classList.remove('visible');
                }
            }
        });
    </script>

    <!-- 🌟 Universal Glassmorphism Light Mode Custom Alert Modal System -->
    <div id="customAlertModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 100050; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
        <div style="width: 90%; max-width: 420px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 24px; padding: 28px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); text-align: center; color: #0f172a; position: relative;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: #f0f9ff; border: 1px solid #bae6fd; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);">
                <span id="customAlertIcon" style="font-size: 1.8rem;">🔔</span>
            </div>
            <h3 id="customAlertTitle" style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">Thông báo</h3>
            <p id="customAlertMessage" style="font-size: 0.88rem; color: #475569; line-height: 1.55; margin: 0 0 24px 0; white-space: pre-line;">
                Nội dung thông báo...
            </p>
            <button id="customAlertBtn" onclick="closeCustomAlert()" style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 32px; border-radius: 50px; font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #ffffff; box-shadow: 0 6px 18px rgba(14, 165, 233, 0.35); transition: all 0.25s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(14, 165, 233, 0.45)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 18px rgba(14, 165, 233, 0.35)';">
                Đồng ý
            </button>
        </div>
    </div>

    <script>
        var customAlertCallback = null;
        function showCustomAlert(title, message, btnText = 'Đồng ý', callback = null, icon = '🔔') {
            const modal = document.getElementById('customAlertModal');
            if (!modal) {
                alert(title + "\n" + message);
                if (typeof callback === 'function') callback();
                return;
            }
            document.getElementById('customAlertTitle').innerText = title;
            document.getElementById('customAlertMessage').innerText = message;
            const btn = document.getElementById('customAlertBtn');
            if (btn) btn.innerText = btnText;
            const iconEl = document.getElementById('customAlertIcon');
            if (iconEl) iconEl.innerText = icon;
            customAlertCallback = callback;
            modal.style.display = 'flex';
        }

        function closeCustomAlert() {
            const modal = document.getElementById('customAlertModal');
            if (modal) modal.style.display = 'none';
            if (typeof customAlertCallback === 'function') {
                const cb = customAlertCallback;
                customAlertCallback = null;
                cb();
            }
        }
    </script>

    @yield('scripts')
</body>
</html>
