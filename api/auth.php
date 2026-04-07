<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

$action = $_GET['action'] ?? '';

switch ($action) {

    // POST: Đăng ký thành viên mới
    // Body: { name, phone, email, password }
    case 'register':
        $input    = getJsonInput();
        $name     = trim($input['name'] ?? '');
        $phone    = sanitizePhone($input['phone'] ?? '');
        $email    = strtolower(trim($input['email'] ?? ''));
        $password = $input['password'] ?? '';

        if (!$name)                 jsonResponse(['error' => 'Vui lòng nhập họ tên'], 400);
        if (strlen($phone) < 9)    jsonResponse(['error' => 'Số điện thoại không hợp lệ'], 400);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(['error' => 'Email không hợp lệ'], 400);
        if (strlen($password) < 6) jsonResponse(['error' => 'Mật khẩu tối thiểu 6 ký tự'], 400);

        $db = getDB();

        // Kiểm tra trùng phone
        $chk = $db->prepare("SELECT id FROM customers WHERE phone = ?");
        $chk->execute([$phone]);
        if ($chk->fetch()) jsonResponse(['error' => 'Số điện thoại đã được đăng ký'], 409);

        // Kiểm tra trùng email
        $chk2 = $db->prepare("SELECT id FROM customers WHERE email = ?");
        $chk2->execute([$email]);
        if ($chk2->fetch()) jsonResponse(['error' => 'Email đã được sử dụng'], 409);

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO customers (phone, name, email, password_hash, sessions, max_sessions, pkg) VALUES (?, ?, ?, ?, 0, 0, 'none')")
           ->execute([$phone, $name, $email, $hash]);

        $stmt = $db->prepare("SELECT id, phone, name, email, sessions, max_sessions, pkg, expires_at, created_at FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();

        // Gửi email chào mừng
        $welcomeHtml = emailTemplate(
            'Chào mừng đến Wonder Pickleball! 🏓',
            "<p>Xin chào <strong>{$name}</strong>,</p>
            <p>Tài khoản của bạn đã được tạo thành công. Bạn có thể đăng nhập và mua gói tập ngay.</p>
            <a class='btn' href='".APP_URL."/member.php'>Xem thẻ tập của tôi →</a>
            <p>Số điện thoại: <strong>{$phone}</strong><br>Email: <strong>{$email}</strong></p>"
        );
        sendMail($email, $name, 'Chào mừng đến Wonder Pickleball!', $welcomeHtml);

        jsonResponse(['success' => true, 'data' => $customer]);
        break;

    // POST: Đăng nhập
    // Body: { phone, password }
    case 'login':
        $input    = getJsonInput();
        $phone    = sanitizePhone($input['phone'] ?? '');
        $password = $input['password'] ?? '';

        if (!$phone || !$password) jsonResponse(['error' => 'Thiếu thông tin đăng nhập'], 400);

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();

        if (!$customer) jsonResponse(['error' => 'Số điện thoại chưa đăng ký'], 404);
        if (!$customer['password_hash']) jsonResponse(['error' => 'Tài khoản chưa có mật khẩu. Vui lòng dùng chức năng đặt mật khẩu.'], 400);
        if (!password_verify($password, $customer['password_hash'])) jsonResponse(['error' => 'Mật khẩu không đúng'], 401);

        // Lấy check-in history
        $ci = $db->prepare("SELECT * FROM checkins WHERE phone = ? ORDER BY checked_in_at DESC LIMIT 10");
        $ci->execute([$phone]);
        $checkins = $ci->fetchAll();

        $expired = $customer['expires_at'] && strtotime($customer['expires_at']) < strtotime('today');

        // Ẩn password_hash trước khi trả về
        unset($customer['password_hash']);
        jsonResponse(['success' => true, 'data' => $customer, 'checkins' => $checkins, 'expired' => $expired]);
        break;

    // POST: Quên mật khẩu — gửi email reset
    // Body: { email }
    case 'forgot':
        $input = getJsonInput();
        $email = strtolower(trim($input['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(['error' => 'Email không hợp lệ'], 400);

        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, phone FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        // Luôn trả success để không lộ email tồn tại hay không
        if (!$customer) {
            jsonResponse(['success' => true, 'message' => 'Nếu email tồn tại, link đặt lại mật khẩu đã được gửi']);
        }

        // Xoá token cũ
        $db->prepare("DELETE FROM password_resets WHERE phone = ?")->execute([$customer['phone']]);

        // Tạo token mới (hết hạn sau 1 giờ)
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $db->prepare("INSERT INTO password_resets (phone, email, token, expires_at) VALUES (?, ?, ?, ?)")
           ->execute([$customer['phone'], $email, $token, $expires]);

        $resetUrl = APP_URL . '/reset-password.php?token=' . $token;
        $html = emailTemplate(
            'Đặt lại mật khẩu',
            "<p>Xin chào <strong>{$customer['name']}</strong>,</p>
            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
            <a class='btn' href='{$resetUrl}'>Đặt lại mật khẩu →</a>
            <p>Link có hiệu lực trong <strong>1 giờ</strong>. Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>"
        );

        $sent = sendMail($email, $customer['name'], '[Wonder Pickleball] Đặt lại mật khẩu', $html);

        if (!$sent) jsonResponse(['error' => 'Không thể gửi email. Vui lòng kiểm tra cấu hình SMTP trong Admin.'], 500);
        jsonResponse(['success' => true, 'message' => 'Link đặt lại mật khẩu đã được gửi về email của bạn']);
        break;

    // POST: Đặt lại mật khẩu bằng token
    // Body: { token, password }
    case 'reset':
        $input    = getJsonInput();
        $token    = trim($input['token'] ?? '');
        $password = $input['password'] ?? '';

        if (!$token)                jsonResponse(['error' => 'Token không hợp lệ'], 400);
        if (strlen($password) < 6)  jsonResponse(['error' => 'Mật khẩu tối thiểu 6 ký tự'], 400);

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) jsonResponse(['error' => 'Link không hợp lệ hoặc đã hết hạn'], 400);

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("UPDATE customers SET password_hash = ? WHERE phone = ?")->execute([$hash, $reset['phone']]);
        $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$token]);

        jsonResponse(['success' => true, 'message' => 'Mật khẩu đã được cập nhật. Bạn có thể đăng nhập ngay.']);
        break;

    // GET: Kiểm tra token reset còn hợp lệ không
    // ?action=check_token&token=xxx
    case 'check_token':
        $token = trim($_GET['token'] ?? '');
        if (!$token) jsonResponse(['valid' => false]);
        $db = getDB();
        $stmt = $db->prepare("SELECT phone FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        jsonResponse(['valid' => (bool)$row]);
        break;

    // POST: Admin đăng nhập
    // Body: { password }
    case 'admin_login':
        $input = getJsonInput();
        $password = $input['password'] ?? '';
        if (!$password) jsonResponse(['error' => 'Thiếu mật khẩu'], 400);
        if ($password !== ADMIN_PASSWORD) jsonResponse(['error' => 'Mật khẩu không đúng'], 401);
        jsonResponse(['success' => true, 'token' => md5(ADMIN_PASSWORD)]);
        break;

    // POST: Thành viên cập nhật email
    // Body: { phone, password, email }
    case 'update_email':
        $input    = getJsonInput();
        $phone    = sanitizePhone($input['phone'] ?? '');
        $password = $input['password'] ?? '';
        $email    = strtolower(trim($input['email'] ?? ''));
        if (!$phone || !$password) jsonResponse(['error' => 'Thiếu thông tin xác thực'], 400);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(['error' => 'Email không hợp lệ'], 400);
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();
        if (!$customer) jsonResponse(['error' => 'Không tìm thấy tài khoản'], 404);
        if (!$customer['password_hash'] || !password_verify($password, $customer['password_hash'])) {
            jsonResponse(['error' => 'Mật khẩu không đúng'], 401);
        }
        $chk = $db->prepare("SELECT id FROM customers WHERE email = ? AND id <> ?");
        $chk->execute([$email, $customer['id']]);
        if ($chk->fetch()) jsonResponse(['error' => 'Email đã được sử dụng'], 409);
        $db->prepare("UPDATE customers SET email = ? WHERE id = ?")->execute([$email, $customer['id']]);
        $stmt2 = $db->prepare("SELECT id, phone, name, email, sessions, max_sessions, pkg, expires_at, created_at FROM customers WHERE id = ?");
        $stmt2->execute([$customer['id']]);
        jsonResponse(['success' => true, 'data' => $stmt2->fetch()]);
        break;

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
