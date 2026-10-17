<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống đang bảo trì - Đông Anh Social</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --border: #e2e8f0;
            --shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08), 0 0 1px 1px rgba(15, 23, 42, 0.04);
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--text-main);
        }
        .error-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px 36px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow);
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f0f9ff;
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            border: 1px solid #bae6fd;
        }
        .icon-wrapper svg {
            width: 40px;
            height: 40px;
            stroke-width: 2;
        }
        h1 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
            color: var(--text-main);
        }
        p {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 32px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid var(--border);
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
        .footer-note {
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 0.85rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <h1>Đã có sự cố xảy ra</h1>
        <p>{{ $message ?? 'Hệ thống đang bảo trì hoặc gặp sự cố kết nối tạm thời. Chúng tôi đã ghi nhận và đang tiến hành khắc phục. Quý khách vui lòng thử lại sau ít phút.' }}</p>
        
        <div class="btn-group">
            <button onclick="window.location.reload()" class="btn btn-secondary">
                🔄 Tải lại trang
            </button>
            <a href="/" class="btn btn-primary">
                🏠 Về trang chủ
            </a>
        </div>

        <div class="footer-note">
            Đông Anh Social &copy; {{ date('Y') }} — Nền tảng số hóa kết nối & khám phá
        </div>
    </div>
</body>
</html>
