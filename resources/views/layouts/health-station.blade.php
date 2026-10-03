<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cổng Quản Lý Trạm Y Tế & Cơ Sở Sức Khỏe') — DongAnh Discovery</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #ccfbf1;
            --accent: #0284c7;
            --emerald: #10b981;
            --dark-bg: #0f172a;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --sidebar-width: 270px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Styles */
        .hs-sidebar {
            width: var(--sidebar-width);
            background-color: var(--dark-bg);
            color: #f8fafc;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }

        .hs-sidebar-brand {
            padding: 24px 20px;
            background: linear-gradient(135deg, #0f766e 0%, #1e293b 100%);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .hs-sidebar-brand h1 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.3px;
        }

        .hs-sidebar-brand p {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 4px;
            font-weight: 500;
        }

        .hs-sidebar-menu {
            padding: 20px 12px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .hs-menu-heading {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 700;
            padding: 12px 12px 6px;
        }

        .hs-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        .hs-nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            color: #94a3b8;
        }

        .hs-nav-link:hover {
            background-color: rgba(255,255,255,0.08);
            color: #ffffff;
        }

        .hs-nav-link:hover i {
            color: #2dd4bf;
        }

        .hs-nav-link.active {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .hs-nav-link.active i {
            color: #ffffff;
        }

        .hs-sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            background: rgba(15, 23, 42, 0.6);
        }

        .hs-station-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(13, 148, 136, 0.15);
            border: 1px solid rgba(45, 212, 191, 0.3);
            padding: 10px 12px;
            border-radius: 8px;
        }

        .hs-station-badge i {
            color: #2dd4bf;
            font-size: 1.2rem;
        }

        .hs-station-badge-info h4 {
            font-size: 0.82rem;
            color: #f8fafc;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 170px;
        }

        .hs-station-badge-info p {
            font-size: 0.72rem;
            color: #94a3b8;
        }

        /* Main Content Wrapper */
        .hs-main-wrapper {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
        }

        /* Top Header */
        .hs-header {
            height: 70px;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .hs-header-title h2 {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hs-header-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .hs-user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            padding: 6px 14px 6px 8px;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
        }

        .hs-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.9rem;
        }

        .hs-user-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .btn-hs-logout {
            background-color: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-hs-logout:hover {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .btn-hs-home {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-hs-home:hover {
            background-color: #dcfce7;
        }

        /* Content Container */
        .hs-content {
            padding: 32px;
            flex-grow: 1;
        }

        /* Alert Messages */
        .hs-alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .hs-alert-success {
            background-color: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .hs-alert-error {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* Footer */
        .hs-footer {
            padding: 20px 32px;
            border-top: 1px solid var(--border-color);
            background-color: #ffffff;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Menu -->
    <aside class="hs-sidebar">
        <div class="hs-sidebar-brand">
            <h1><i class="fa-solid fa-hospital-user" style="color: #2dd4bf;"></i> Trạm Y Tế Số</h1>
            <p>Hệ thống Quản lý Y tế DongAnh Discovery</p>
        </div>

        <div class="hs-sidebar-menu">
            <div class="hs-menu-heading">Bảng Điều Hành Y Tế</div>
            <a href="{{ route('health-station.dashboard') }}" class="hs-nav-link {{ request()->routeIs('health-station.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Tổng quan Trạm Y tế
            </a>
            <a href="{{ route('health-station.profile') }}" class="hs-nav-link {{ request()->routeIs('health-station.profile') ? 'active' : '' }}">
                <i class="fa-solid fa-notes-medical"></i> Hồ sơ & Hotline Cấp cứu
            </a>

            <div class="hs-menu-heading" style="margin-top: 12px;">Chuyên Môn & Nhân Sự</div>
            <a href="{{ route('health-station.services') }}" class="hs-nav-link {{ request()->routeIs('health-station.services') ? 'active' : '' }}">
                <i class="fa-solid fa-briefcase-medical"></i> Dịch vụ Y tế & BHYT
            </a>
            <a href="{{ route('health-station.doctors') }}" class="hs-nav-link {{ request()->routeIs('health-station.doctors') ? 'active' : '' }}">
                <i class="fa-solid fa-user-doctor"></i> Đội ngũ Bác sĩ & Lịch trực
            </a>

            <div class="hs-menu-heading" style="margin-top: 12px;">Tra Cứu Khách Hàng</div>
            <a href="{{ route('eatery.show', $eatery->slug ?? '') }}" target="_blank" class="hs-nav-link">
                <i class="fa-solid fa-globe"></i> Xem Trang Y tế Công khai
            </a>
        </div>

        <div class="hs-sidebar-footer">
            <div class="hs-station-badge">
                <i class="fa-solid fa-square-h"></i>
                <div class="hs-station-badge-info">
                    <h4>{{ $eatery->name ?? 'Cơ sở Y tế' }}</h4>
                    <p>Đông Anh • Hà Nội</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="hs-main-wrapper">
        <header class="hs-header">
            <div class="hs-header-title">
                <h2>@yield('header_title', 'Cổng Quản Lý Trạm Y Tế')</h2>
            </div>
            <div class="hs-header-user">
                <a href="{{ route('home') }}" class="btn-hs-home">
                    <i class="fa-solid fa-house"></i> DongAnh Discovery
                </a>
                <div class="hs-user-chip">
                    <div class="hs-user-avatar">
                        {{ mb_substr($user->name ?? 'Y', 0, 1) }}
                    </div>
                    <span class="hs-user-name">{{ $user->name ?? 'Cán bộ Y tế' }}</span>
                </div>
                <a href="{{ route('logout') }}" class="btn-hs-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </a>
            </div>
        </header>

        <main class="hs-content">
            @if(session('success'))
                <div class="hs-alert hs-alert-success">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="hs-alert hs-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="hs-footer">
            © 2026 Cổng Quản Lý Trạm Y Tế Số — DongAnh Discovery | UBND & Trung Tâm Y Tế Xã Đông Anh
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
