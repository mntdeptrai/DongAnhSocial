<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <script>
        // Force clean light theme for admin panel matching CabaFood Mockup
        document.documentElement.setAttribute('data-theme', 'light');
    </script>
    
    <title>@yield('title', 'Kênh Quản trị - DongAnh Discovery')</title>
    
    <!-- Bootstrap 5 CSS for UI Grid & Form Components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet.js Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <!-- Dedicated Admin Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ file_exists(public_path('css/admin.css')) ? filemtime(public_path('css/admin.css')) : '1.0.0' }}">
</head>
<body>

    <!-- Beautiful Dead-Center Success/Error Alert Modal System -->
    @if(session('success') || session('error'))
        <div id="admin-toast-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; justify-content: center; align-items: center; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); opacity: 0; transition: opacity 0.3s ease; pointer-events: auto;">
            <div id="admin-toast" style="background: rgba(30, 41, 59, 0.98); backdrop-filter: blur(10px); color: #ffffff; border: 1.5px solid {{ session('success') ? 'rgba(16, 185, 129, 0.35)' : 'rgba(239, 68, 68, 0.35)' }}; padding: 36px 32px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); text-align: center; max-width: 440px; width: 90%; transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease;">
                <div style="background: {{ session('success') ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px auto; color: {{ session('success') ? '#10b981' : '#ef4444' }}; box-shadow: 0 0 20px {{ session('success') ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }};">
                    {{ session('success') ? '✅' : '⚠️' }}
                </div>
                <h3 style="font-size: 1.45rem; font-weight: 800; text-transform: uppercase; color: {{ session('success') ? '#10b981' : '#ef4444' }}; letter-spacing: 0.05em; margin-bottom: 12px;">
                    {{ session('success') ? 'Thành công!' : 'Đã có lỗi!' }}
                </h3>
                <p style="font-size: 1.02rem; color: rgba(255,255,255,0.85); line-height: 1.6; margin-bottom: 28px; padding: 0 10px;">
                    {{ session('success') ?: session('error') }}
                </p>
                <button onclick="closeAdminToast()" style="width: 100%; padding: 12px 24px; border-radius: 10px; border: none; background: {{ session('success') ? '#10b981' : '#ef4444' }}; color: #ffffff; font-weight: 700; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 12px {{ session('success') ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)' }}; transition: all 0.2s;" onmouseover="this.style.filter='brightness(1.15)'" onmouseout="this.style.filter='none'">
                    Đồng ý
                </button>
            </div>
        </div>

        <script>
            function showAdminToast() {
                const container = document.getElementById('admin-toast-container');
                const toast = document.getElementById('admin-toast');
                if (container && toast) {
                    setTimeout(() => {
                        container.style.opacity = '1';
                        toast.style.transform = 'scale(1)';
                    }, 50);

                    // Tự động đóng sau 4.5 giây
                    setTimeout(closeAdminToast, 4500);
                }
            }

            function closeAdminToast() {
                const container = document.getElementById('admin-toast-container');
                const toast = document.getElementById('admin-toast');
                if (container && toast) {
                    container.style.opacity = '0';
                    toast.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        container.remove();
                    }, 300);
                }
            }

            window.addEventListener('pageshow', function (e) {
                const container = document.getElementById('admin-toast-container');
                const isBackNav = e.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward');
                if (isBackNav) {
                    if (container) container.remove();
                } else {
                    showAdminToast();
                }
            });
        </script>
    @endif

    <div class="admin-layout-wrapper">
        
        <!-- ==========================================================================
             LEFT COLUMN: FIXED PURPLE SIDEBAR
             ========================================================================== -->
        <aside class="admin-sidebar-nav">
            <a href="/admin/dashboard" class="admin-sidebar-logo">
                <span>🏰</span>
                <div>
                    <div style="font-size: 0.95rem; font-weight: 800;">DongAnh Discovery</div>
                    <div style="font-size: 0.68rem; font-weight: 500; color: rgba(255,255,255,0.45); margin-top: 1px;">Quản Trị Hệ Thống</div>
                </div>
            </a>

            <div class="admin-sidebar-section-title">Tổng Quan</div>
            <a href="/admin/dashboard" class="admin-menu-item {{ request()->is('admin/dashboard') && !isset($eatery) ? 'active' : '' }}">
                <span>📊</span> Dashboard Thống Kê
            </a>

            @if(isset($eatery) && $eatery)
            <div class="admin-sidebar-section-title">Đang Điều Phối</div>
            <a href="/admin/eateries/{{ $eatery->slug }}/edit" class="admin-menu-item active">
                <span>🏛️</span> {{ Str::limit($eatery->name, 18, '...') }}
            </a>
            @endif

            <div class="admin-sidebar-section-title">Quản Trị Hệ Thống</div>
            <a href="/admin/dashboard#eateries" class="admin-menu-item {{ request()->is('admin/dashboard') && isset($eatery) ? 'active' : '' }}">
                <span>📍</span> Quản Lý Địa Điểm & Cơ Sở
            </a>
            <a href="/admin/schools" class="admin-menu-item {{ request()->is('admin/schools*') ? 'active' : '' }}">
                <span>🏫</span> Quản Lý Trường Học & Sáp Nhập
            </a>
            <a href="/admin/stalls" class="admin-menu-item {{ request()->is('admin/stalls*') ? 'active' : '' }}">
                <span>🛒</span> Quản Lý Gian Hàng
            </a>
            <a href="/admin/users" class="admin-menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <span>👥</span> Quản Lý Tài Khoản
            </a>

            <div class="admin-sidebar-divider"></div>

            <div class="admin-sidebar-section-title">Cổng Thông Tin</div>
            <a href="/" class="admin-menu-item" target="_blank">
                <span>🗺️</span> Khám Phá Đông Anh
            </a>
        </aside>

        <!-- ==========================================================================
             RIGHT COLUMN: CONTENT WRAPPER
             ========================================================================== -->
        <div class="admin-main-wrapper">
            
            <!-- Top Navigation Bar -->
            <header class="admin-topbar">
                <div class="admin-breadcrumbs">
                    <a href="/admin/dashboard">Admin Panel</a>
                    <span class="separator">/</span>
                    <span>@yield('title', 'Bảng điều khiển')</span>
                </div>
                
                <div class="admin-topbar-actions">
                    <!-- Light/Dark Mode Toggle Mockup Icon -->
                    <button class="admin-icon-btn" title="Chuyển giao diện tối" onclick="alert('Tính năng giao diện tối đang được phát triển!')">
                        🌙
                    </button>
                    
                    <!-- Notification Bell Mockup Icon -->
                    <button class="admin-icon-btn" title="Thông báo hệ thống">
                        🔔
                        <span class="admin-icon-btn-badge"></span>
                    </button>
                    
                    <!-- User Dropdown Details -->
                    @if(session()->has('user_id'))
                        <div class="admin-user-profile">
                            <div class="admin-user-avatar">
                                {{ mb_substr(session('user_name'), 0, 1, 'UTF-8') }}
                            </div>
                            <div class="admin-user-info-text" style="text-align: left;">
                                <span class="admin-user-name">{{ session('user_name') }}</span>
                                <span class="admin-user-role">
                                    @if(session('user_role') === 'admin')
                                        Administrator
                                    @elseif(session('user_role') === 'manager')
                                        Ban Quản lý Chợ
                                    @elseif(session('user_role') === 'seller')
                                        Chủ Gian Hàng Số
                                    @else
                                        Thành viên
                                    @endif
                                </span>
                            </div>
                            
                            <a href="/"
                               style="margin-left: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.72rem; border-radius: 8px; background: #f0f9ff; color: #0284c7; border: 1.5px solid rgba(2,132,199,0.2); font-weight: 700; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='#f0f9ff'">
                               🏠 Về Trang Chủ
                            </a>
                            
                            <a href="/auth/logout"
                               onclick="return confirm('Bạn có chắc muốn đăng xuất?')"
                               style="margin-left: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.72rem; border-radius: 8px; background: #fef2f2; color: #ef4444; border: 1.5px solid rgba(239,68,68,0.15); font-weight: 700; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                               🚪 Đăng xuất
                            </a>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Main Yield Content -->
            <main class="admin-content-body">
                @yield('content')
            </main>

            <!-- Corporate Admin Footer -->
            <footer style="background-color: #ffffff; border-top: 1px solid var(--admin-border); padding: 18px 0; text-align: center; font-size: 0.8rem; color: var(--admin-text-muted);">
                &copy; 2026 Kênh Quản trị Bản đồ số Đông Anh (DongAnh Discovery). Tất cả quyền được bảo lưu.
            </footer>
            
        </div>
        
    </div>

    <!-- Leaflet.js Map Library -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
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
    <div id="universal-toast-container" style="position: fixed; top: 24px; right: 24px; z-index: 999999; display: flex; flex-direction: column; gap: 12px; max-width: 420px; width: calc(100% - 48px); pointer-events: none;"></div>

    <script>
    (function() {
        window.showToast = function(message, type = 'info', title = null) {
            let cleanMsg = message || '';
            if (typeof cleanMsg !== 'string') {
                try { cleanMsg = JSON.stringify(cleanMsg); } catch (_) { cleanMsg = String(cleanMsg); }
            }

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
                font-family: var(--admin-font, 'Plus Jakarta Sans', sans-serif);
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

        window.alert = function(msg) {
            window.showToast(msg, 'info');
        };

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
</body>
</html>
