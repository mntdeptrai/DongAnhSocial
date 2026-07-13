<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Mã xác thực OTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #eef2f5;
        }
        .header {
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #0f172a;
        }
        .instruction {
            font-size: 15px;
            color: #475569;
            margin-bottom: 30px;
        }
        .otp-container {
            text-align: center;
            margin: 30px 0;
            background: #f0f9ff;
            border: 2px dashed #bae6fd;
            border-radius: 12px;
            padding: 20px;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #0284c7;
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        .warning {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 30px;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
            text-align: center;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BẢN ĐỒ KHÁM PHÁ ĐÔNG ANH</h1>
        </div>
        <div class="content">
            <div class="greeting">Xin chào quý khách,</div>
            <div class="instruction">
                Bạn đã gửi yêu cầu thay đổi mật khẩu tài khoản trên hệ thống **Bản đồ khám phá đông anh**. 
                Vui lòng sử dụng mã OTP dưới đây để xác nhận thay đổi mật khẩu của mình:
            </div>
            
            <div class="otp-container">
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <div class="instruction" style="text-align: center; font-weight: 600; color: #0284c7;">
                Mã xác thực này có hiệu lực trong vòng {{ $expiryMinutes }} phút và chỉ sử dụng được 1 lần duy nhất.
            </div>

            <div class="warning">
                Nếu bạn không gửi yêu cầu này, vui lòng bỏ qua email hoặc liên hệ với ban quản trị để bảo mật tài khoản.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Bản đồ khám phá đông anh. All rights reserved.
        </div>
    </div>
</body>
</html>
