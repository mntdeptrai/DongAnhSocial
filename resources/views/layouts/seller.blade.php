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
            document.addEventListener('DOMContentLoaded', showSlrToast);
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
                        <div class="slr-stall-badge-market">📍 {{ Str::limit($market->name, 22, '...') }}</div>
                    @endif
                </div>
            @endif

            <div class="slr-sidebar-section-title">Quản Lý Gian Hàng</div>

            <a href="/seller/dashboard" class="slr-menu-item {{ request()->is('seller/dashboard') ? 'active' : '' }}">
                <span>📊</span> Tổng Quan Gian Hàng
            </a>
            <a href="/seller/products" class="slr-menu-item {{ request()->is('seller/products*') ? 'active' : '' }}">
                <span>📦</span> Sản Phẩm & Thực Đơn
            </a>
            <a href="/seller/orders" class="slr-menu-item {{ request()->is('seller/orders*') ? 'active' : '' }}">
                <span>🛍️</span> Đơn Hàng Nhận
            </a>

            <div class="slr-sidebar-divider"></div>

            <div class="slr-sidebar-section-title">Khách Hàng</div>
            @if(isset($market) && $market)
                <a href="/dia-diem/{{ $market->slug }}" target="_blank" class="slr-menu-item">
                    <span>👁️</span> Xem Gian Hàng Trên Bản Đồ
                </a>
            @endif
            <a href="/" class="slr-menu-item" target="_blank">
                <span>🗺️</span> Bản Đồ Số Đông Anh
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
                    <button class="slr-icon-btn" title="Thông báo">
                        🔔
                        <span class="slr-icon-btn-badge"></span>
                    </button>

                    @if(session()->has('user_id'))
                        <div class="slr-user-profile">
                            <div class="slr-user-avatar">
                                {{ mb_substr(session('user_name'), 0, 1, 'UTF-8') }}
                            </div>
                            <div class="slr-user-info-text" style="text-align: left;">
                                <span class="slr-user-name">{{ session('user_name') }}</span>
                                <span class="slr-user-role">Chủ Gian Hàng Số</span>
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
</body>
</html>
