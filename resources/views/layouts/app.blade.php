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
    
    <!-- Browser Search Engine SEO Indexing Directives (Google, Bing, Yahoo, Cốc Cốc) -->
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="bingbot" content="index, follow">
    <meta name="author" content="Đông Anh Social">

    <!-- SEO Meta Tags cho Browser Search Engine -->
    <title>@yield('title', 'Khám phá Xã Đông Anh - Tra Cứu Địa Điểm, Trường Học, Ăn Uống, Y Tế, Chợ OCOP')</title>
    <meta name="description" content="@yield('meta_description', 'Khám phá Xã Đông Anh - Tra cứu chính xác các trường học, bệnh viện, nhà hàng quán ăn, đặc sản OCOP, chợ truyền thống tại Xã Đông Anh, Hà Nội trên các công cụ tìm kiếm Google, Cốc Cốc, Bing.')">
    <meta name="keywords" content="@yield('meta_keywords', 'Khám phá Đông Anh, Khám phá Xã Đông Anh, Trường học Xã Đông Anh, Mầm non Đông Anh, Tiểu học Đông Anh, Địa điểm ăn uống Đông Anh, Bệnh viện Đông Anh, Chợ Đông Anh, OCOP Đông Anh, Di sản Cổ Loa')">
    <link rel="canonical" href="@yield('canonical_url', request()->url())">
    
    <!-- OpenGraph Social & Search Engine Indexing Tags -->
    <meta property="og:locale" content="vi_VN">
    <meta property="og:site_name" content="Khám phá Đông Anh - Khám phá Xã Đông Anh">
    <meta property="og:title" content="@yield('title', 'Khám phá Xã Đông Anh - Tra Cứu Địa Điểm, Trường Học, Ăn Uống, Y Tế, Chợ OCOP')">
    <meta property="og:description" content="@yield('meta_description', 'Khám phá Xã Đông Anh - Tra cứu chính xác trường học, bệnh viện, nhà hàng quán ăn, đặc sản OCOP, chợ truyền thống tại Xã Đông Anh, Hà Nội.')">
    <meta property="og:url" content="@yield('canonical_url', request()->url())">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif
    <meta property="og:type" content="@yield('og_type', 'website')">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Khám phá Xã Đông Anh')">
    <meta name="twitter:description" content="@yield('meta_description', 'Khám phá trường học, ăn uống, y tế, chợ OCOP tại Xã Đông Anh.')">
    @hasSection('og_image')
    <meta name="twitter:image" content="@yield('og_image')">
    @endif
    
    <!-- Structured Data JSON-LD WebSite & SearchAction for Google / Bing / Brave Search -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "name": "Khám phá Đông Anh",
      "alternateName": [
        "Khám phá Xã Đông Anh",
        "DongAnh Discovery",
        "Đông Anh Social",
        "Bản đồ số Đông Anh",
        "Xã Đông Anh",
        "Du lịch Đông Anh"
      ],
      "url": "{{ url('/') }}",
      "description": "Nền tảng số hóa dịch vụ, tra cứu trường học, ẩm thực, y tế, chợ truyền thống và đặc sản OCOP Xã Đông Anh, Hà Nội",
      "potentialAction": {
        "@@type": "SearchAction",
        "target": {
          "@@type": "EntryPoint",
          "urlTemplate": "{{ url('/tim-kiem') }}?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    
    @stack('head')
    
    <!-- DNS Prefetch & Preconnect for High-Speed External CDN Assets -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//unpkg.com">
    <link rel="dns-prefetch" href="//pub-0d65b75ce9134b228fdfd47781b22e11.r2.dev">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    @if(request()->is('/', 'tim-kiem*', 'dia-diem*', 'tuyen-duong*', 'checkin*', 'food-tour*', 'ban-tin*'))
    <!-- Leaflet.js Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    @endif
    
    <!-- Google Fonts: Asynchronous Non-blocking Load with Display Swap -->
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Outfit:wght@500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Outfit:wght@500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Outfit:wght@500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap">
    </noscript>
    
    <!-- Custom Theme Styling with Browser-Cacheable Timestamps -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2.1">
    <link rel="stylesheet" href="{{ asset('css/4-screen-responsive.css') }}?v=2.1">
    
    <!-- Mobile Native Overrides (Only load for mobile and tablet screens) -->
    <link rel="stylesheet" media="screen and (max-width: 991px)" href="{{ asset('css/mobile-native.css') }}?v=2.1">

    <!-- 📱 Mobile Viewport Fit Fix — Ôm gọn nội dung, không scroll ngang -->
    <link rel="stylesheet" href="{{ asset('css/mobile-fix.css') }}?v=2.1">
    
    <!-- Facebook-Style Social Feed, Multi-Photo Grid & Lightbox -->
    <link rel="stylesheet" href="{{ asset('css/facebook-feed.css') }}?v={{ file_exists(public_path('css/facebook-feed.css')) ? filemtime(public_path('css/facebook-feed.css')) : '2.2' }}">
    <script defer src="{{ asset('js/facebook-feed.js') }}?v={{ file_exists(public_path('js/facebook-feed.js')) ? filemtime(public_path('js/facebook-feed.js')) : '2.2' }}"></script>
    
    @if(request()->is('/', 'tim-kiem*', 'dia-diem*'))
    <!-- Map-Based Storytelling CSS & GSAP Animation Library -->
    <link rel="stylesheet" href="{{ asset('css/storytelling.css') }}?v=2.1">
    <link rel="stylesheet" href="{{ asset('css/storytelling-mobile.css') }}?v=2.1">
    <link rel="stylesheet" href="{{ asset('css/ocop-storytelling.css') }}?v=2.1">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    @endif
    
    <!-- Dynamic Schema.org JSON-LD Structured Data for Google Indexing -->
    @yield('seo_schema')
    
    <!-- Alpine.js for Modern Client Interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- 📱 MOBILE LAYOUT — Safety net + box-sizing universal -->
    <style>
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            width: 100% !important;
        }

        /* Prevent Alpine.js FOUC (Flash of Unstyled Content) on page load */
        [x-cloak] { display: none !important; }

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

        /* Box-sizing universal (nếu base.css chưa load kịp) */
        *, *::before, *::after {
            box-sizing: border-box;
            corner-shape: squircle;
            -webkit-corner-shape: squircle;
            corner-smoothing: 1;
            -webkit-corner-smoothing: 1;
        }

        /* Safety net: body direct children không vượt 100vw */
        body > * {
            max-width: 100%;
        }

        @media (max-width: 992px) {
            html, body {
                overflow-x: hidden !important;
                max-width: 100vw !important;
                width: 100% !important;
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

    @include('partials.header')

    <!-- Main Content Slot -->
    <main style="min-height: 65vh; padding-bottom: 60px;">
        @yield('content')
    </main>

    @include('partials.footer')

    @if(request()->is('/', 'tim-kiem*', 'dia-diem*', 'tuyen-duong*', 'checkin*', 'food-tour*', 'ban-tin*'))
    <!-- Leaflet.js Map Library -->
    <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script defer src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    @endif
    
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

            // Safety: Reset any lingering fullscreen modal or overlay when returning via Back/Forward cache
            window.addEventListener('pageshow', function() {
                const storyModal = document.getElementById('storytellingModal');
                if (storyModal) {
                    storyModal.classList.remove('active');
                    storyModal.style.display = 'none';
                    storyModal.style.opacity = '0';
                    storyModal.style.pointerEvents = 'none';
                }
                if (window.storyteller) {
                    window.storyteller.closeAndResetModal();
                }
                document.body.style.overflow = 'auto';
            });
        });

        </script>
    @include('partials.guide-modal')
    
    @yield('scripts')

    @if(session()->has('user_id'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function sendHeartbeat() {
                if (document.hidden) return; // Không gửi request heartbeat khi tab đang ở background
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

    @include('partials.chat-widget')

    @include('partials.cart-drawer')


    @if(request()->is('/', 'tim-kiem*'))
    <!-- Map-Based Storytelling Modal & JS Engine -->
    @include('partials.storytelling-modal')
    <script defer src="{{ asset('js/storytelling-data.js') }}?v=2.1"></script>
    <script defer src="{{ asset('js/storytelling-engine.js') }}?v=2.1"></script>

    <!-- OCOP Map-Based Storytelling Modal & JS Engine -->
    @include('partials.ocop-storytelling-modal')
    <script defer src="{{ asset('js/ocop-official-data.js') }}?v=2.1"></script>
    <script>
        window.DB_OCOP_PRODUCTS = (window.OCOP_PRODUCTS && window.OCOP_PRODUCTS.length > 0) ? window.OCOP_PRODUCTS : window.OFFICIAL_OCOP_PRODUCTS;
    </script>
    <script defer src="{{ asset('js/ocop-story-data.js') }}?v=2.1"></script>
    <script defer src="{{ asset('js/ocop-story/camera-controller.js') }}?v=2.1"></script>
    <script defer src="{{ asset('js/ocop-story/marker-controller.js') }}?v=2.1"></script>
    <script defer src="{{ asset('js/ocop-story/card-controller.js') }}?v=2.1"></script>
    <script defer src="{{ asset('js/ocop-story/animation-controller.js') }}?v=2.1"></script>
    <script defer src="{{ asset('js/ocop-story/story-controller.js') }}?v=2.1"></script>
    @endif


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

    {{-- Universal Glassmorphic Squircle Toast Notification Container --}}
    <div id="universal-toast-container" style="position: fixed; top: 24px; right: 24px; z-index: 999999; display: flex; flex-direction: column; gap: 12px; max-width: 420px; width: calc(100% - 48px); pointer-events: none;"></div>

    <script>
    (function() {
        window.showToast = function(message, type = 'info', title = null) {
            let cleanMsg = message || '';
            if (typeof cleanMsg !== 'string') {
                try { cleanMsg = JSON.stringify(cleanMsg); } catch (_) { cleanMsg = String(cleanMsg); }
            }

            // 🛡️ Filter & Sanitize technical SQL or exception logs
            if (cleanMsg.includes('SQLSTATE') || cleanMsg.includes('QueryException') || cleanMsg.includes('PDOException') || cleanMsg.includes('Database:') || cleanMsg.includes('Connection: mysql')) {
                cleanMsg = 'Đã xảy ra lỗi khi thao tác dữ liệu. Vui lòng kiểm tra lại thông tin hoặc thử lại sau.';
                type = 'error';
                title = title || 'Lỗi thao tác';
            }

            const container = document.getElementById('universal-toast-container');
            if (!container) return;

            const config = {
                success: { bg: 'rgba(15, 23, 42, 0.94)', border: 'rgba(16, 185, 129, 0.4)', icon: '✅', iconBg: 'rgba(16, 185, 129, 0.18)', color: '#34d399', title: title || 'Thành công' },
                error: { bg: 'rgba(15, 23, 42, 0.94)', border: 'rgba(239, 68, 68, 0.4)', icon: '⚠️', iconBg: 'rgba(239, 68, 68, 0.18)', color: '#f87171', title: title || 'Có lỗi xảy ra' },
                warning: { bg: 'rgba(15, 23, 42, 0.94)', border: 'rgba(245, 158, 11, 0.4)', icon: '⚡', iconBg: 'rgba(245, 158, 11, 0.18)', color: '#fbbf24', title: title || 'Lưu ý' },
                info: { bg: 'rgba(15, 23, 42, 0.94)', border: 'rgba(14, 165, 233, 0.4)', icon: 'ℹ️', iconBg: 'rgba(14, 165, 233, 0.18)', color: '#38bdf8', title: title || 'Thông báo' }
            };

            const t = config[type] || config.info;
            const toastEl = document.createElement('div');
            toastEl.style.cssText = `
                background: ${t.bg};
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1.5px solid ${t.border};
                color: #ffffff;
                padding: 14px 18px;
                border-radius: 16px;
                corner-shape: squircle;
                -webkit-corner-shape: squircle;
                box-shadow: 0 20px 40px -10px rgba(0,0,0,0.5);
                display: flex;
                align-items: flex-start;
                gap: 12px;
                pointer-events: auto;
                transform: translateX(120%);
                opacity: 0;
                transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                font-family: var(--font-body, 'Plus Jakarta Sans', sans-serif);
            `;

            toastEl.innerHTML = `
                <div style="background: ${t.iconBg}; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                    ${t.icon}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 800; font-size: 0.88rem; color: ${t.color}; margin-bottom: 2px;">${t.title}</div>
                    <div style="font-size: 0.83rem; color: rgba(255,255,255,0.9); line-height: 1.45; word-break: break-word;">${cleanMsg}</div>
                </div>
                <button type="button" style="background: transparent; border: none; color: rgba(255,255,255,0.5); font-size: 1.2rem; cursor: pointer; padding: 0 2px; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">&times;</button>
            `;

            const closeBtn = toastEl.querySelector('button');
            const dismiss = () => {
                toastEl.style.transform = 'translateX(120%)';
                toastEl.style.opacity = '0';
                setTimeout(() => toastEl.remove(), 350);
            };
            closeBtn.onclick = dismiss;

            container.appendChild(toastEl);
            requestAnimationFrame(() => {
                toastEl.style.transform = 'translateX(0)';
                toastEl.style.opacity = '1';
            });

            setTimeout(dismiss, 4500);
        };

        // Override native browser alert(...) so legacy alerts render as modern Toast UI
        window.alert = function(msg) {
            window.showToast(msg, 'info');
        };

        // Flash Session Listener
        @if(session('success'))
            window.showToast(@json(session('success')), 'success');
        @endif
        @if(session('error'))
            window.showToast(@json(session('error')), 'error');
        @endif
        @if(session('warning'))
            window.showToast(@json(session('warning')), 'warning');
        @endif
    })();
    </script>

    @include('partials.confirm-modal')


    @include('partials.social-share-modal')


    @include('partials.auth-modal')

    @include('partials.likers-modal')

    {{-- WebRTC P2P Call Modals, Overlay & Client JS (Tắt trên trang Livestream để tối ưu tài nguyên và đường truyền) --}}
    @if(!request()->is('livestream*'))
    @include('partials.webrtc-call-modal')

    {{-- Real-time Customer Order & Status Notification Engine --}}
    @if(Auth::check() || session('user_id'))
    <script defer src="{{ asset('js/customer-notifications.js') }}?v={{ file_exists(public_path('js/customer-notifications.js')) ? filemtime(public_path('js/customer-notifications.js')) : '1.0.0' }}"></script>
    @endif
    @endif
</body>
</html>


