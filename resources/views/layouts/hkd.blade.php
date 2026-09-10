<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script>document.documentElement.setAttribute('data-theme', 'light');</script>
    <title>@yield('title', 'Kênh Điều Hành Hộ Kinh Doanh & Doanh Nghiệp Số — DongAnh Discovery')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --hkd-primary: #059669;
            --hkd-primary-hover: #047857;
            --hkd-primary-light: #ecfdf5;
            --hkd-accent: #0284c7;
            --hkd-dark: #0f172a;
            --hkd-sidebar: #064e3b;
            --hkd-bg: #f8fafc;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { background-color: var(--hkd-bg); color: #1e293b; display: flex; min-height: 100vh; }

        /* SIDEBAR */
        .hkd-sidebar {
            width: 270px;
            background: linear-gradient(180deg, #044e3b 0%, #065f46 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.12);
        }
        .hkd-sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .hkd-sidebar-logo-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #10b981 0%, #0284c7 100%);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        .hkd-sidebar-title { font-size: 1.05rem; font-weight: 800; color: #ffffff; line-height: 1.2; }
        .hkd-sidebar-subtitle { font-size: 0.72rem; color: #a7f3d0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px; }

        .hkd-business-badge {
            margin: 16px 16px 8px 16px;
            padding: 14px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 14px;
        }
        .hkd-business-badge-type { font-size: 0.68rem; color: #6ee7b7; font-weight: 700; text-transform: uppercase; }
        .hkd-business-badge-name { font-size: 0.92rem; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }

        .hkd-nav { padding: 12px 14px; display: flex; flex-direction: column; gap: 4px; flex: 1; overflow-y: auto; }
        .hkd-nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: #d1fae5;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .hkd-nav-item i { font-size: 1.1rem; width: 22px; text-align: center; }
        .hkd-nav-item:hover, .hkd-nav-item.active {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            font-weight: 700;
            transform: translateX(3px);
        }
        .hkd-nav-item.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
        }

        /* MAIN CONTENT AREA */
        .hkd-main { margin-left: 270px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        
        .hkd-topbar {
            height: 68px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px;
            position: sticky; top: 0; z-index: 90;
        }
        .hkd-topbar-left { font-size: 1.1rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
        .hkd-topbar-right { display: flex; align-items: center; gap: 16px; }

        .hkd-btn-top {
            padding: 8px 16px; border-radius: 10px; text-decoration: none; font-size: 0.85rem; font-weight: 700;
            display: flex; align-items: center; gap: 6px; transition: all 0.2s;
        }
        .hkd-btn-view { background: #f0fdf4; color: #059669; border: 1px solid #a7f3d0; }
        .hkd-btn-view:hover { background: #dcfce7; }
        .hkd-btn-logout { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .hkd-btn-logout:hover { background: #fee2e2; }

        .hkd-container { padding: 32px; flex: 1; }

        /* FORM CONTROLS & UTILITIES */
        .hkd-form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .hkd-form-label { font-size: 0.86rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 6px; }
        .hkd-form-input, .hkd-form-select, .hkd-form-textarea {
            width: 100%;
            padding: 11px 16px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-size: 0.92rem;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .hkd-form-input:focus, .hkd-form-select:focus, .hkd-form-textarea:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
            background: #ffffff;
        }
        .hkd-form-textarea { resize: vertical; min-height: 80px; }

        /* BUTTON SYSTEM */
        .hkd-btn-action {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; border-radius: 12px; font-size: 0.88rem; font-weight: 700;
            border: none; cursor: pointer; transition: all 0.2s ease; text-decoration: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .hkd-btn-action:hover { transform: translateY(-1px); }
        .hkd-btn-action:active { transform: translateY(0); }
        .hkd-btn-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); }
        .hkd-btn-emerald:hover { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
        .hkd-btn-sky { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3); }
        .hkd-btn-sky:hover { background: linear-gradient(135deg, #0369a1 0%, #075985 100%); }
        .hkd-btn-amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3); }
        .hkd-btn-amber:hover { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
        .hkd-btn-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .hkd-btn-slate:hover { background: #e2e8f0; color: #1e293b; }

        /* TOAST NOTIFICATION */
        .hkd-alert-success {
            padding: 16px 20px; background: #ecfdf5; border: 1.5px solid #10b981; color: #065f46;
            border-radius: 14px; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
        }

        @media (max-width: 992px) {
            .hkd-sidebar { width: 80px; }
            .hkd-sidebar-title, .hkd-sidebar-subtitle, .hkd-business-badge, .hkd-nav-item span { display: none; }
            .hkd-main { margin-left: 80px; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="hkd-sidebar">
        <div class="hkd-sidebar-header">
            <div class="hkd-sidebar-logo-icon">
                <i class="fa-solid fa-building-flag"></i>
            </div>
            <div>
                <div class="hkd-sidebar-title">Đông Anh Digital</div>
                <div class="hkd-sidebar-subtitle">Kênh HKD & Doanh Nghiệp</div>
            </div>
        </div>

        @if(isset($eatery))
        <div class="hkd-business-badge">
            <div class="hkd-business-badge-type">🏢 Hộ Kinh Doanh / Doanh Nghiệp</div>
            <div class="hkd-business-badge-name">{{ $eatery->name }}</div>
        </div>
        @endif

        <nav class="hkd-nav">
            <a href="{{ route('hkd.dashboard') }}" class="hkd-nav-item {{ request()->routeIs('hkd.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Tổng Quan & HT10</span>
            </a>
            <a href="{{ route('hkd.profile') }}" class="hkd-nav-item {{ request()->routeIs('hkd.profile') ? 'active' : '' }}">
                <i class="fa-solid fa-store"></i>
                <span>Hồ Sơ & Vị Trí Bản Đồ</span>
            </a>
            <a href="{{ route('hkd.products.index') }}" class="hkd-nav-item {{ request()->routeIs('hkd.products*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Sản Phẩm & Hàng Hóa</span>
            </a>
            <a href="{{ route('hkd.orders.index') }}" class="hkd-nav-item {{ request()->routeIs('hkd.orders*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i>
                <span>Đơn Hàng & Duyệt Đơn</span>
            </a>
            <a href="{{ route('hkd.reports') }}" class="hkd-nav-item {{ request()->routeIs('hkd.reports') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Báo Cáo Doanh Thu HT10</span>
            </a>
            <a href="{{ route('hkd.qr') }}" class="hkd-nav-item {{ request()->routeIs('hkd.qr') ? 'active' : '' }}">
                <i class="fa-solid fa-qrcode"></i>
                <span>Mã QR & VietQR</span>
            </a>
            <a href="{{ route('hkd.chat.index') }}" class="hkd-nav-item {{ request()->routeIs('hkd.chat*') ? 'active' : '' }}">
                <i class="fa-solid fa-comments"></i>
                <span>Trò Chuyện Khách Hàng</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="hkd-main">
        <header class="hkd-topbar">
            <div class="hkd-topbar-left">
                <i class="fa-solid fa-building-circle-check" style="color: #059669;"></i>
                <span>@yield('title_header', 'Kênh Điều Hành HKD & Doanh Nghiệp Số')</span>
            </div>
            <div class="hkd-topbar-right">
                @if(isset($eatery))
                <a href="/dia-diem/{{ $eatery->slug }}" target="_blank" class="hkd-btn-top hkd-btn-view">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Xem Trang Công Khai</span>
                </a>
                @endif
                <a href="{{ route('logout.get') }}" class="hkd-btn-top hkd-btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </header>

        <main class="hkd-container">
            @if(session('success'))
                <div class="hkd-alert-success">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.3rem;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
