<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script>document.documentElement.setAttribute('data-theme', 'light');</script>
    <title>@yield('title', 'Ban Quản Lý Chợ — DongAnh Discovery')</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="{{ asset('css/manager.css') }}?v={{ file_exists(public_path('css/manager.css')) ? filemtime(public_path('css/manager.css')) : '1.0.0' }}">
</head>
<body>

    <!-- Toast Notification System -->
    @if(session('success') || session('error'))
        <div id="mgr-toast-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; justify-content: center; align-items: center; background: rgba(4, 47, 46, 0.55); backdrop-filter: blur(8px); opacity: 0; transition: opacity 0.3s ease; pointer-events: auto;">
            <div id="mgr-toast" style="background: rgba(15, 35, 35, 0.98); backdrop-filter: blur(10px); color: #ffffff; border: 1.5px solid {{ session('success') ? 'rgba(13, 148, 136, 0.4)' : 'rgba(239, 68, 68, 0.4)' }}; padding: 36px 32px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); text-align: center; max-width: 440px; width: 90%; transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease;">
                <div style="background: {{ session('success') ? 'rgba(13, 148, 136, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px auto;">
                    {{ session('success') ? '✅' : '⚠️' }}
                </div>
                <h3 style="font-size: 1.35rem; font-weight: 800; color: {{ session('success') ? '#2dd4bf' : '#ef4444' }}; margin-bottom: 12px;">
                    {{ session('success') ? 'Thành công' : 'Có lỗi xảy ra' }}
                </h3>
                <p style="font-size: 1rem; color: rgba(255,255,255,0.85); line-height: 1.6; margin-bottom: 28px;">
                    {{ session('success') ?: session('error') }}
                </p>
                <button onclick="closeMgrToast()" style="width: 100%; padding: 12px 24px; border-radius: 10px; border: none; background: {{ session('success') ? '#0d9488' : '#ef4444' }}; color: #ffffff; font-weight: 700; font-size: 0.92rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.filter='brightness(1.15)'" onmouseout="this.style.filter='none'">
                    Đồng ý
                </button>
            </div>
        </div>
        <script>
            function showMgrToast() {
                const c = document.getElementById('mgr-toast-container');
                const t = document.getElementById('mgr-toast');
                if (c && t) {
                    setTimeout(() => { c.style.opacity = '1'; t.style.transform = 'scale(1)'; }, 50);
                    setTimeout(closeMgrToast, 5000);
                }
            }
            function closeMgrToast() {
                const c = document.getElementById('mgr-toast-container');
                const t = document.getElementById('mgr-toast');
                if (c && t) {
                    c.style.opacity = '0'; t.style.transform = 'scale(0.9)';
                    setTimeout(() => c.remove(), 300);
                }
            }
            window.addEventListener('pageshow', function (e) {
                const c = document.getElementById('mgr-toast-container');
                const isBackNav = e.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward');
                if (isBackNav) {
                    if (c) c.remove();
                } else {
                    showMgrToast();
                }
            });
        </script>
    @endif

    <div class="mgr-layout-wrapper">

        <!-- ============================================================
             SIDEBAR — Ban Quản Lý Chợ (Teal)
             ============================================================ -->
        <aside class="mgr-sidebar-nav">
            <a href="/admin/dashboard" class="mgr-sidebar-logo">
                <span>🏛️</span>
                <div>
                    <div style="font-size: 0.95rem; font-weight: 800;">Ban Quản Lý Chợ Số</div>
                    <div style="font-size: 0.68rem; font-weight: 500; color: rgba(255,255,255,0.5); margin-top: 1px;">DongAnh Discovery</div>
                </div>
            </a>

            @php
                $mgrEatery = null;
                try {
                    $mgrEatery = \App\Services\EateryApiService::getEateries()
                        ->firstWhere('user_id', session('user_id'));
                } catch (\Throwable $e) {}
            @endphp

            {{-- Chợ đang quản lý --}}
            @if(isset($eatery) && $eatery)
                <div style="background: rgba(13,148,136,0.12); border: 1px solid rgba(13,148,136,0.25); border-radius: 10px; padding: 10px 14px; margin-bottom: 20px;">
                    <div style="font-size: 0.68rem; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; margin-bottom: 4px;">Đang điều hành</div>
                    <div style="font-size: 0.85rem; font-weight: 800; color: #5eead4;">🏪 {{ Str::limit($eatery->name, 22, '...') }}</div>
                </div>
            @elseif($mgrEatery)
                <div style="background: rgba(13,148,136,0.12); border: 1px solid rgba(13,148,136,0.25); border-radius: 10px; padding: 10px 14px; margin-bottom: 20px;">
                    <div style="font-size: 0.68rem; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; margin-bottom: 4px;">Chợ của tôi</div>
                    <div style="font-size: 0.85rem; font-weight: 800; color: #5eead4;">🏪 {{ Str::limit($mgrEatery->name, 22, '...') }}</div>
                </div>
            @endif

            <div class="mgr-sidebar-section-title">Điều Hành</div>

            <a href="/admin/dashboard" class="mgr-menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <span>📊</span> Tổng Quan
            </a>

            @if(isset($eatery) && $eatery)
                <a href="/admin/eateries/{{ $eatery->slug }}/edit" class="mgr-menu-item {{ request()->is('admin/eateries/*/edit') ? 'active' : '' }}">
                    <span>⚙️</span> Quản Lý Chợ
                </a>
            @elseif($mgrEatery)
                <a href="/admin/eateries/{{ $mgrEatery->slug }}/edit" class="mgr-menu-item {{ request()->is('admin/eateries/*/edit') ? 'active' : '' }}">
                    <span>⚙️</span> Quản Lý Chợ
                </a>
            @endif

            <a href="/admin/stalls" class="mgr-menu-item {{ request()->is('admin/stalls*') ? 'active' : '' }}">
                <span>🛒</span> Quản Lý Gian Hàng
            </a>

            <a href="/admin/orders" class="mgr-menu-item {{ request()->is('admin/orders*') ? 'active' : '' }}">
                <span>🛍️</span> Quản Lý Đơn Hàng
            </a>

            <a href="/admin/users" class="mgr-menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <span>👥</span> Quản Lý Tài Khoản
            </a>

            <div class="mgr-sidebar-section-title">Cổng Thông Tin</div>
            <a href="/" class="mgr-menu-item" target="_blank">
                <span>🗺️</span> Khám Phá Đông Anh
            </a>
        </aside>

        <!-- ============================================================
             MAIN CONTENT
             ============================================================ -->
        <div class="mgr-main-wrapper">

            <!-- Topbar -->
            <header class="mgr-topbar">
                <div class="mgr-breadcrumbs">
                    <a href="/admin/dashboard">BQL Chợ</a>
                    <span style="color: #cbd5e1;">/</span>
                    <span>@yield('title', 'Bảng điều khiển')</span>
                </div>

                <div class="mgr-topbar-actions">
                    <button class="mgr-icon-btn" title="Thông báo">
                        🔔
                        <span class="mgr-icon-btn-badge"></span>
                    </button>

                    @if(session()->has('user_id'))
                        <div class="mgr-user-profile">
                            <div class="mgr-user-avatar">
                                {{ mb_substr(session('user_name'), 0, 1, 'UTF-8') }}
                            </div>
                            <div class="mgr-user-info-text" style="text-align: left;">
                                <span class="mgr-user-name">{{ session('user_name') }}</span>
                                <span class="mgr-user-role">Ban Quản Lý Chợ</span>
                            </div>
                            <a href="/auth/logout"
                               onclick="return confirm('Bạn có chắc muốn đăng xuất?')"
                               style="margin-left: 12px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.72rem; border-radius: 8px; background: #fef2f2; color: #ef4444; border: 1.5px solid rgba(239,68,68,0.15); font-weight: 700; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                               🚪 Đăng xuất
                            </a>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Content -->
            <main class="mgr-content-body">
                @yield('content')
            </main>

            <footer style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 18px 0; text-align: center; font-size: 0.8rem; color: #94a3b8;">
                &copy; 2026 Kênh Ban Quản Lý Chợ Số — DongAnh Discovery
            </footer>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @yield('scripts')

    @if(session()->has('user_id'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function sendHeartbeat() {
                fetch('/user/heartbeat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({})
                }).catch(function(err) { console.warn('Heartbeat skipped', err); });
            }
            sendHeartbeat();
            setInterval(sendHeartbeat, 60000);
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
                font-family: var(--mgr-font, 'Plus Jakarta Sans', sans-serif);
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
