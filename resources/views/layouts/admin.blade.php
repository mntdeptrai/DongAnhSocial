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

            // Gọi chạy ngay khi DOM load
            document.addEventListener('DOMContentLoaded', showAdminToast);
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
            <a href="/admin/users" class="admin-menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <span>👥</span> Quản Lý Tài Khoản
            </a>
            <a href="/admin/stalls" class="admin-menu-item {{ request()->is('admin/stalls*') ? 'active' : '' }}">
                <span>🛒</span> Quản Lý Gian Hàng
            </a>

            <div class="admin-sidebar-divider"></div>

            <div class="admin-sidebar-section-title">Cổng Thông Tin</div>
            <a href="/" class="admin-menu-item" target="_blank">
                <span>🗺️</span> Bản Đồ Số Đông Anh
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
</body>
</html>
