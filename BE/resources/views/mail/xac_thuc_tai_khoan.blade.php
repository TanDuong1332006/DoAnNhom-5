<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực Tài Khoản</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333333;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7fa;
            padding: 40px 0;
        }

        .main-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            padding: 35px 20px;
            text-align: center;
            color: #ffffff;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .content {
            padding: 35px 30px;
            line-height: 1.6;
        }

        .content h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 20px;
        }

        .content p {
            font-size: 15px;
            color: #555555;
            margin-bottom: 20px;
        }

        .info-box {
            background-color: #f8fafc;
            border-left: 4px solid #0d6efd;
            border-radius: 4px;
            padding: 15px 20px;
            margin: 25px 0;
        }

        .info-box table {
            width: 100%;
        }

        .info-box td {
            padding: 6px 0;
            font-size: 14px;
        }

        .info-label {
            color: #718096;
            width: 35%;
            font-weight: 600;
        }

        .info-value {
            color: #2d3748;
            font-weight: 500;
        }

        .btn-container {
            text-align: center;
            margin: 35px 0;
        }

        .btn-verify {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }

        .note {
            font-size: 13px;
            color: #888888;
            border-top: 1px dashed #e2e8f0;
            padding-top: 20px;
            margin-top: 25px;
        }

        .note a {
            color: #0d6efd;
            word-break: break-all;
        }

        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999999;
            border-top: 1px solid #edf2f7;
        }

        .footer p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="main-container">
            <!-- Header -->
            <div class="header">
                <h1>XÁC THỰC TÀI KHOẢN</h1>
            </div>

            <!-- Body Content -->
            <div class="content">
                <h2>Xin chào, {{ $data['ho_va_ten'] ?? 'Quý khách' }}! </h2>
                <p>
                    Cảm ơn bạn đã đăng ký tài khoản tại hệ thống của chúng tôi. Để đảm bảo an toàn bảo mật và bắt đầu sử dụng dịch vụ, vui lòng nhấn vào nút bên dưới để kích hoạt tài khoản của bạn.
                </p>

                <!-- Box thông tin tài khoản -->
                <div class="info-box">
                    <table>
                        <tr>
                            <td class="info-label">Họ và tên:</td>
                            <td class="info-value">{{ $data['ho_va_ten'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Mã kích hoạt:</td>
                            <td class="info-value"><b style="color: #0d6efd; font-size: 16px;">{{ $data['hash_active'] ?? '' }}</b></td>
                        </tr>
                    </table>
                </div>

                <!-- Nút Xác Thực -->
                <div class="btn-container">
                    <a href="http://localhost:5173/kich-hoat" class="btn-verify" target="_blank">
                        Kích Hoạt Tài Khoản Ngay
                    </a>
                </div>

                <!-- Lưu ý -->
                <div class="note">
                    <p>⚠️ <strong>Lưu ý:</strong> Liên kết xác thực này chỉ có hiệu lực trong vòng <strong>24 giờ</strong>. Nếu bạn không thực hiện đăng ký tài khoản này, vui lòng bỏ qua email này.</p>
                    <p>Nếu nút bấm trên không hoạt động, bạn có thể sao chép liên kết sau và dán vào trình duyệt:</p>
                    <p><a href="http://localhost:5173/kich-hoat">http://localhost:5173/kich-hoat</a></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Email này được gửi tự động từ hệ thống, vui lòng không phản hồi trực tiếp email này.</p>
                <p>&copy; 2026 Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </div>
</body>

</html>