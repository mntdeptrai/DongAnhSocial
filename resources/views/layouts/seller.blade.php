<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script>document.documentElement.setAttribute('data-theme', 'light');</script>
    <title>@yield('title', 'Kênh Gian Hàng Số — DongAnh Discovery')</title>
    <link rel="stylesheet" href="{{ asset('css/seller.css') }}?v={{ file_exists(public_path('css/seller.css')) ? filemtime(public_path('css/seller.css')) : '1.0.0' }}">
</head>
<body>

    <!-- Toast Notification System -->
    @if(session('success') || session('error'))
        <div id="slr-toast-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; justify-content: center; align-items: center; background: rgba(28, 16, 7, 0.55); backdrop-filter: blur(8px); opacity: 0; transition: opacity 0.3s ease; pointer-events: auto;">
            <div id="slr-toast" style="background: rgba(28, 16, 7, 0.97); backdrop-filter: blur(10px); color: #ffffff; border: 1.5px solid {{ session('success') ? 'rgba(217, 119, 6, 0.4)' : 'rgba(239, 68, 68, 0.4)' }}; padding: 36px 32px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); text-align: center; max-width: 440px; width: 90%; transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease;">
                <div style="background: {{ session('success') ? 'rgba(217, 119, 6, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px auto;">
                    {{ session('success') ? '✅' : '⚠️' }}
                </div>
                <h3 style="font-size: 1.35rem; font-weight: 800; color: {{ session('success') ? '#fbbf24' : '#ef4444' }}; margin-bottom: 12px;">
                    {{ session('success') ? 'Thành công' : 'Có lỗi xảy ra' }}
                </h3>
                <p style="font-size: 1rem; color: rgba(255,255,255,0.85); line-height: 1.6; margin-bottom: 28px;">
                    {{ session('success') ?: session('error') }}
                </p>
                <button onclick="closeSlrToast()" style="width: 100%; padding: 12px 24px; border-radius: 10px; border: none; background: {{ session('success') ? '#d97706' : '#ef4444' }}; color: #ffffff; font-weight: 700; font-size: 0.92rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.filter='brightness(1.15)'" onmouseout="this.style.filter='none'">
                    Đồng ý
                </button>
            </div>
        </div>
        <script>
            function showSlrToast() {
                const c = document.getElementById('slr-toast-container');
                const t = document.getElementById('slr-toast');
                if (c && t) {
                    setTimeout(() => { c.style.opacity = '1'; t.style.transform = 'scale(1)'; }, 50);
                    setTimeout(closeSlrToast, 5000);
                }
            }
            function closeSlrToast() {
                const c = document.getElementById('slr-toast-container');
                const t = document.getElementById('slr-toast');
                if (c && t) {
                    c.style.opacity = '0'; t.style.transform = 'scale(0.9)';
                    setTimeout(() => c.remove(), 300);
                }
            }
            window.addEventListener('pageshow', function (e) {
                const c = document.getElementById('slr-toast-container');
                const isBackNav = e.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward');
                if (isBackNav) {
                    if (c) c.remove();
                } else {
                    showSlrToast();
                }
            });
        </script>
    @endif

    <div class="slr-layout-wrapper">

        <!-- ============================================================
             SIDEBAR — Chủ Gian Hàng Số (Amber)
             ============================================================ -->
        <aside class="slr-sidebar-nav">
            <a href="/seller/dashboard" class="slr-sidebar-logo">
                <span>🛒</span>
                <div>
                    <div style="font-size: 0.95rem; font-weight: 800;">Kênh Gian Hàng Số</div>
                    <div style="font-size: 0.68rem; font-weight: 500; color: rgba(255,255,255,0.5); margin-top: 1px;">DongAnh Discovery</div>
                </div>
            </a>

            {{-- Stall Info Badge --}}
            @if(isset($stallName))
                <div class="slr-stall-badge">
                    <div style="font-size: 0.68rem; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; margin-bottom: 3px;">Gian hàng của tôi</div>
                    <div class="slr-stall-badge-name">🏪 {{ Str::limit($stallName, 22, '...') }}</div>
                    @if(isset($market) && $market)
                        <div class="slr-stall-badge-market" style="color: #fbbf24; font-weight: 700;">🏛️ {{ Str::limit($market->name, 22, '...') }}</div>
                    @endif
                    @php
                        $userRouteBiz = isset($routeBusinesses) ? $routeBusinesses : (auth()->check() ? auth()->user()->getRouteBusinesses() : collect());
                    @endphp
                    @if($userRouteBiz && $userRouteBiz->count() > 0)
                        @foreach($userRouteBiz as $rb)
                            <div class="slr-stall-badge-market" style="color: #6ee7b7; font-weight: 700; margin-top: 3px;">
                                🛣️ Tuyến 4.0 ({{ $rb->village_name }})
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif

            <div class="slr-sidebar-section-title">Quản Lý Gian Hàng</div>

            <a href="/seller/dashboard" class="slr-menu-item {{ request()->is('seller/dashboard') ? 'active' : '' }}">
                <span>📊</span> Tổng Quan Gian Hàng
            </a>
            <a href="/seller/profile" class="slr-menu-item {{ request()->is('seller/profile') ? 'active' : '' }}">
                <span>⚙️</span> Cấu Hình Gian Hàng & VietQR
            </a>
            <a href="/seller/products" class="slr-menu-item {{ request()->is('seller/products*') ? 'active' : '' }}">
                <span>📦</span> Sản Phẩm & Thực Đơn
            </a>
            <a href="/seller/orders" class="slr-menu-item {{ request()->is('seller/orders*') ? 'active' : '' }}">
                <span>🛍️</span> Đơn Hàng Nhận
            </a>
            <a href="/seller/chat" class="slr-menu-item {{ request()->is('seller/chat*') ? 'active' : '' }}">
                <span>💬</span> Trò Chuyện & Nhắn Tin
                <span id="slr-chat-sidebar-badge" style="display: none; margin-left: auto; background: #ef4444; color: #fff; font-size: 0.68rem; font-weight: 800; padding: 2px 6px; border-radius: 10px;">0</span>
            </a>

            <div class="slr-sidebar-divider"></div>

            <div class="slr-sidebar-section-title">Khách Hàng</div>
            @if(isset($market) && $market)
                <a href="/dia-diem/{{ $market->slug }}" target="_blank" class="slr-menu-item">
                    <span>👁️</span> Xem Gian Hàng Trên Bản Đồ
                </a>
            @endif
            <a href="/" class="slr-menu-item" target="_blank">
                <span>🗺️</span> Khám Phá Đông Anh
            </a>
        </aside>

        <!-- ============================================================
             MAIN CONTENT
             ============================================================ -->
        <div class="slr-main-wrapper">

            <!-- Topbar -->
            <header class="slr-topbar">
                <div class="slr-breadcrumbs">
                    <a href="/seller/dashboard">Gian Hàng Số</a>
                    <span style="color: #d1d5db;">/</span>
                    <span>@yield('title', 'Bảng điều khiển')</span>
                </div>

                <div class="slr-topbar-actions">
                    <!-- Chat Messages Button -->
                    <a href="/seller/chat" class="slr-icon-btn" title="Tin nhắn từ khách hàng" style="position: relative; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                        💬
                        <span id="slr-chat-topbar-badge" class="slr-icon-btn-badge" style="display: none; position: absolute; top: -4px; right: -4px; background: #ef4444; color: #ffffff; font-size: 0.68rem; font-weight: 900; padding: 2px 6px; border-radius: 10px; border: 2px solid #ffffff; min-width: 18px; height: 18px; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);">0</span>
                    </a>

                    <!-- Notification Bell with Interactive Dropdown -->
                    <div class="slr-notification-dropdown-wrapper" style="position: relative;">
                        <button id="slr-bell-btn" class="slr-icon-btn" title="Thông báo đơn hàng mới" onclick="toggleSellerNotifDropdown(event)" style="position: relative; cursor: pointer;">
                            🔔
                            <span id="slr-notif-badge" class="slr-icon-btn-badge" style="display: none; position: absolute; top: -4px; right: -4px; background: #ef4444; color: #ffffff; font-size: 0.68rem; font-weight: 900; padding: 2px 6px; border-radius: 10px; border: 2px solid #ffffff; min-width: 18px; height: 18px; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);">0</span>
                        </button>
                        
                        <!-- Notification Dropdown Menu -->
                        <div id="slr-notif-dropdown" style="display: none; position: absolute; right: 0; top: calc(100% + 12px); width: 360px; background: #ffffff; border-radius: 20px; box-shadow: 0 16px 45px rgba(0,0,0,0.18); border: 1.5px solid #fde68a; z-index: 10000; overflow: hidden; animation: slrDropdownFade 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                            <div style="padding: 14px 18px; background: linear-gradient(135deg, #fffbeb, #fef3c7); border-bottom: 1px solid #fde68a; display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 1.2rem;">🔔</span>
                                    <strong style="font-size: 0.95rem; color: #92400e;">Thông Báo Đơn Hàng</strong>
                                </div>
                                <span id="slr-notif-count-text" style="font-size: 0.72rem; background: #d97706; color: #ffffff; padding: 2px 8px; border-radius: 20px; font-weight: 800;">0 mới</span>
                            </div>
                            <div id="slr-notif-list" style="max-height: 320px; overflow-y: auto;">
                                <div style="text-align: center; padding: 36px 20px; color: #9ca3af; font-size: 0.85rem;">
                                    <span>✨ Đang kết nối tải thông báo...</span>
                                </div>
                            </div>
                            <div style="padding: 12px 18px; background: #fafafa; border-top: 1px solid #f3f4f6; text-align: center;">
                                <a href="/seller/orders" style="font-size: 0.84rem; font-weight: 800; color: #d97706; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                    Xem tất cả đơn hàng ➔
                                </a>
                            </div>
                        </div>
                    </div>

                    @if(session()->has('user_id'))
                        <div class="slr-user-profile">
                            <div class="slr-user-avatar">
                                {{ mb_substr(session('user_name'), 0, 1, 'UTF-8') }}
                            </div>
                            <div class="slr-user-info-text" style="text-align: left;">
                                <span class="slr-user-name">{{ session('user_name') }}</span>
                                <span class="slr-user-role">Chủ Gian Hàng Số</span>
                            </div>
                            <a href="/"
                               style="margin-left: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.72rem; border-radius: 8px; background: #ecfdf5; color: #059669; border: 1.5px solid rgba(5,150,105,0.2); font-weight: 700; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#ecfdf5'">
                               🏠 Về Trang Chủ
                            </a>
                            <a href="#"
                               onclick="event.preventDefault(); openLogoutConfirmModal();"
                               style="margin-left: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.72rem; border-radius: 8px; background: #fef2f2; color: #ef4444; border: 1.5px solid rgba(239,68,68,0.15); font-weight: 700; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                               🚪 Đăng xuất
                            </a>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Content -->
            <main class="slr-content-body">
                @yield('content')
            </main>

            <footer style="background: #ffffff; border-top: 1px solid #fde68a; padding: 18px 0; text-align: center; font-size: 0.8rem; color: #a8a29e;">
                &copy; 2026 Kênh Gian Hàng Số — DongAnh Discovery &nbsp;|&nbsp; Chợ Số Đông Anh
            </footer>
        </div>
    </div>

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
                font-family: var(--slr-font, 'Plus Jakarta Sans', sans-serif);
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
    <!-- Popup Xác Nhận Đăng Xuất -->
    <div id="logoutConfirmModal" style="display:none; opacity:0; position:fixed; inset:0; z-index:999999; background:rgba(15,23,42,0.6); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); transition:opacity 0.25s ease; justify-content:center; align-items:center;">
        <div id="logoutConfirmBox" style="background:#ffffff; border-radius:20px; padding:28px 32px; max-width:420px; width:calc(100% - 40px); box-shadow:0 25px 60px rgba(0,0,0,0.25); transform:scale(0.92); transition:transform 0.25s ease; position:relative;">
            <button type="button" onclick="closeLogoutConfirmModal()" style="position:absolute; top:12px; right:14px; background:#f1f5f9; border:none; width:32px; height:32px; border-radius:50%; font-size:1rem; cursor:pointer; color:#64748b; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">✕</button>
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:18px;">
                <div style="width:48px; height:48px; border-radius:14px; background:#fef2f2; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0;">🚪</div>
                <div>
                    <h3 style="font-size:1.1rem; font-weight:800; color:#0f172a; margin:0;">Xác nhận đăng xuất</h3>
                    <p style="font-size:0.8rem; color:#64748b; margin:2px 0 0 0;">Phiên làm việc sẽ kết thúc</p>
                </div>
            </div>
            <p style="font-size:0.92rem; color:#334155; line-height:1.6; margin-bottom:24px;">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống? Mọi thay đổi chưa lưu sẽ bị mất.</p>
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeLogoutConfirmModal()" style="padding:10px 20px; border-radius:12px; font-weight:700; font-size:0.9rem; border:none; cursor:pointer; background:#f1f5f9; color:#475569; transition:all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Hủy bỏ</button>
                <button type="button" onclick="window.location.href='/auth/logout'" style="padding:10px 20px; border-radius:12px; font-weight:700; font-size:0.9rem; border:none; cursor:pointer; background:#ef4444; color:#ffffff; box-shadow:0 4px 14px rgba(239,68,68,0.3); transition:all 0.2s;" onmouseover="this.style.background='#dc2626'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#ef4444'; this.style.transform='none'">Đăng xuất</button>
            </div>
        </div>
    </div>
    <script>
    function openLogoutConfirmModal() {
        const modal = document.getElementById('logoutConfirmModal');
        const box = document.getElementById('logoutConfirmBox');
        if (!modal || !box) return;
        modal.style.display = 'flex';
        setTimeout(() => { modal.style.opacity = '1'; box.style.transform = 'scale(1)'; }, 10);
    }
    function closeLogoutConfirmModal() {
        const modal = document.getElementById('logoutConfirmModal');
        const box = document.getElementById('logoutConfirmBox');
        if (!modal || !box) return;
        modal.style.opacity = '0';
        box.style.transform = 'scale(0.92)';
        setTimeout(() => { modal.style.display = 'none'; }, 250);
    }
    document.getElementById('logoutConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeLogoutConfirmModal();
    });
    </script>
    <script src="{{ asset('js/seller-notifications.js') }}?v={{ time() }}"></script>
    <style>
    @keyframes pulse-badge {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
        50% { transform: scale(1.15); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    }
    @keyframes pulse-icon {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1) rotate(5deg); }
    }
    @keyframes slrDropdownFade {
        from { opacity: 0; transform: translateY(-8px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    </style>
</body>
</html>
