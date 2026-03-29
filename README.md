# Wonder Pickleball — Hướng dẫn cài đặt v3

## Cấu trúc file

```
wonder-pickleball/
├── index.php              ← Lễ tân / Check-in
├── buy.php                ← Mua gói tập + QR VietQR (khách vãng lai)
├── member.php             ← Đăng nhập thành viên + mua thêm
├── register.php           ← Đăng ký tài khoản mới
├── reset-password.php     ← Đặt lại mật khẩu từ email
├── admin.php              ← Quản trị: Đơn hàng + Thành viên + SMTP
├── database.sql           ← Tạo DB (chạy 1 lần)
├── includes/
│   ├── config.php         ← *** CẤU HÌNH CHÍNH ***
│   └── nav.php
├── api/
│   ├── auth.php           ← Đăng ký / đăng nhập / quên mật khẩu
│   ├── customers.php      ← Quản lý khách hàng / check-in
│   ├── orders.php         ← Tạo đơn / duyệt đơn / danh sách
│   └── settings.php       ← Cấu hình SMTP
└── assets/
    ├── css/style.css
    └── js/app.js
```

---

## Bước 1 — Tạo Database (DirectAdmin)

1. **DirectAdmin → MySQL Management → Create Database**
   - Tên: `wonder_pickleball`
2. Tạo MySQL user → gán full quyền
3. **phpMyAdmin** → chọn DB → tab **SQL** → chạy toàn bộ `database.sql`

---

## Bước 2 — Upload code

Upload thư mục lên `public_html/` hoặc subdomain (PHP 7.4+, PDO_MySQL)

---

## Bước 3 — Cấu hình `includes/config.php`

```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'wonder_pickleball');
define('DB_USER', 'db_username');
define('DB_PASS', 'db_password');

// URL website (không có / cuối)
define('APP_URL', 'https://yourdomain.com');

// Ngân hàng (đã cấu hình sẵn OCB)
define('BANK_ID',      'OCB');
define('BANK_ACCOUNT', '0789475288');
define('BANK_OWNER',   'Pham Thi Thuong');
define('BANK_NAME',    'OCB - Ngân hàng Phương Đông');

// Admin password
define('ADMIN_PASSWORD', 'wonder2024'); // ĐỔI LẠI
```

---

## Bước 4 — Cấu hình SMTP (trong Admin)

1. Vào `admin.php` → đăng nhập → tab **⚙ Email SMTP**
2. Chọn Gmail → điền email và App Password
3. Bấm **Gửi test** để kiểm tra
4. Bấm **Lưu cấu hình**

**Lấy Gmail App Password:**
- Google Account → Bảo mật → Bật xác minh 2 bước
- `myaccount.google.com/apppasswords` → Tạo mới → Copy 16 ký tự

---

## Luồng hoạt động

### Khách mua gói (buy.php hoặc member.php)
1. Chọn gói → bấm **Tạo đơn & lấy QR**
2. Hiện QR VietQR (OCB) + thông tin chuyển khoản
3. Khách quét QR, chuyển khoản đúng số tiền + nội dung `WP0001`
4. Bấm "Tôi đã chuyển xong" → hiện thông báo chờ duyệt

### Nhân viên duyệt (admin.php → tab Đơn hàng)
1. Đơn mới hiện badge đỏ + màu vàng "⏳ Chờ duyệt"
2. Mở app ngân hàng kiểm tra biến động số dư
3. Bấm **🔍 Xem QR** để đối chiếu nếu cần
4. Bấm **✓ Duyệt** → hệ thống tự cộng buổi cho khách
5. Trang tự refresh mỗi 15 giây

### Thành viên tự phục vụ (member.php)
- Đăng nhập SĐT + mật khẩu
- Xem số buổi, QR check-in, lịch sử
- Mua thêm gói trực tiếp
- Quên mật khẩu → nhận email đặt lại

---

## Trang & chức năng

| URL | Dùng cho |
|-----|----------|
| `/` (index.php) | Lễ tân: tìm khách, check-in |
| `/buy.php` | Mua gói (không cần đăng nhập) |
| `/member.php` | Thành viên: đăng nhập, xem thẻ, mua thêm |
| `/register.php` | Đăng ký tài khoản mới |
| `/reset-password.php` | Đặt lại mật khẩu (từ link email) |
| `/admin.php` | Quản trị (mật khẩu: `wonder2024`) |

---

## Bảng giá mặc định

| Gói | Buổi | Giá | Hiệu lực |
|-----|------|-----|----------|
| Gói 10 tặng 3 | 13 buổi | 600.000đ | 1 tháng |
| Gói 30 tặng 10 | 40 buổi | 1.800.000đ | 3 tháng |
| Lẻ sáng (8-11h) | 1 buổi | 60.000đ | — |
| Lẻ trưa (11-16h) | 1 buổi | 70.000đ | — |
| Lẻ chiều/tối (16-22h) | 1 buổi | 80.000đ | — |
| Khu trẻ em | Không giới hạn giờ | 20.000đ/trẻ | — |
