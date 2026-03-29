<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

$action = $_GET['action'] ?? '';

// Xác thực admin
function requireAdmin(): void {
    $input = $_SERVER['REQUEST_METHOD'] === 'POST' ? getJsonInput() : [];
    $token = $_GET['admin_token'] ?? $input['admin_token'] ?? '';
    if ($token !== \md5(ADMIN_PASSWORD)) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

switch ($action) {

    // GET: Lấy toàn bộ settings SMTP (admin)
    case 'get_smtp':
        requireAdmin();
        $db = getDB();
        $rows = $db->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key LIKE 'smtp_%'")->fetchAll();
        $settings = array_column($rows, 'setting_value', 'setting_key');
        // Ẩn mật khẩu (chỉ cho biết có hay chưa)
        $settings['smtp_pass_set'] = !empty($settings['smtp_pass']);
        $settings['smtp_pass'] = '';
        jsonResponse(['success' => true, 'data' => $settings]);
        break;

    // POST: Lưu cấu hình SMTP
    case 'save_smtp':
        requireAdmin();
        $input = getJsonInput();
        $db = getDB();

        $allowed = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_from_name', 'smtp_enabled'];
        $stmt = $db->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($allowed as $key) {
            if (isset($input[$key])) {
                $stmt->execute([$key, trim($input[$key])]);
            }
        }

        // Chỉ update password nếu có giá trị mới (không xoá pass cũ nếu để trống)
        if (!empty($input['smtp_pass'])) {
            $stmt->execute(['smtp_pass', $input['smtp_pass']]);
        }

        jsonResponse(['success' => true, 'message' => 'Đã lưu cấu hình SMTP']);
        break;

    // POST: Test gửi email thử
    case 'test_smtp':
        requireAdmin();
        $input = getJsonInput();
        $testEmail = trim($input['test_email'] ?? '');
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Email test không hợp lệ'], 400);
        }

        $html = emailTemplate(
            'Test email thành công! ✓',
            "<p>Đây là email test từ hệ thống <strong>Wonder Pickleball</strong>.</p>
            <p>SMTP đã được cấu hình đúng và hoạt động bình thường.</p>
            <p style='color:#999;font-size:12px'>Gửi lúc: " . date('d/m/Y H:i:s') . "</p>"
        );

        $sent = sendMail($testEmail, 'Admin', '[Wonder Pickleball] Test email', $html);
        if ($sent) {
            jsonResponse(['success' => true, 'message' => 'Email test đã gửi thành công đến ' . $testEmail]);
        } else {
            jsonResponse(['error' => 'Gửi thất bại. Kiểm tra lại SMTP User/Password App và bật "Less secure app" hoặc dùng App Password của Gmail.'], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
