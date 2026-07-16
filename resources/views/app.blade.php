<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script>
        // Force light theme — đồng bộ với các trang khác trong hệ thống
        document.documentElement.setAttribute('data-theme', 'light');
    </script>

    <!-- Google Fonts: Outfit + Be Vietnam Pro -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Custom Theme Styling (same set as main app layout) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/checkin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-fix.css') }}">
    <link rel="stylesheet" media="screen and (max-width: 1200px)" href="{{ asset('css/mobile-native.css') }}?v={{ file_exists(public_path('css/mobile-native.css')) ? filemtime(public_path('css/mobile-native.css')) : '1.0.0' }}">
    <!--minhnguyen@123-->
    @viteReactRefresh
    @vite('resources/js/app.jsx')
    @inertiaHead
</head>
<body style="margin: 0; background: var(--bg-base); font-family: 'Be Vietnam Pro', sans-serif; color: var(--text-main); min-height: 100vh;">
    @inertia
</body>
</html>
