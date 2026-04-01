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

    // GET: Lấy tất cả settings 1 lần (smtp + bank + pricing)
    case 'get_all':
        requireAdmin();
        $db = getDB();
        $rows = $db->query("SELECT setting_key, setting_value FROM app_settings")->fetchAll();
        $all = array_column($rows, 'setting_value', 'setting_key');

        // SMTP
        $smtp = [];
        foreach ($all as $k => $v) { if (str_starts_with($k, 'smtp_')) $smtp[$k] = $v; }
        $smtp['smtp_pass_set'] = !empty($smtp['smtp_pass']);
        $smtp['smtp_pass'] = '';

        // Bank
        $bank = [
            'bank_id'      => $all['bank_id'] ?? BANK_ID,
            'bank_account' => $all['bank_account'] ?? BANK_ACCOUNT,
            'bank_owner'   => $all['bank_owner'] ?? BANK_OWNER,
            'bank_name'    => $all['bank_name'] ?? BANK_NAME,
        ];

        // Pricing
        $pricing = [
            'price_pkg_10'         => (int)($all['price_pkg_10'] ?? PRICE_PKG_10),
            'price_pkg_30'         => (int)($all['price_pkg_30'] ?? PRICE_PKG_30),
            'price_social_morning' => (int)($all['price_social_morning'] ?? PRICE_SOCIAL_MORNING),
            'price_social_noon'    => (int)($all['price_social_noon'] ?? PRICE_SOCIAL_NOON),
            'price_social_evening' => (int)($all['price_social_evening'] ?? PRICE_SOCIAL_EVENING),
            'price_kids'           => (int)($all['price_kids'] ?? PRICE_KIDS),
        ];

        // Packages
        $packages = getPackages();

        jsonResponse(['success' => true, 'smtp' => $smtp, 'bank' => $bank, 'pricing' => $pricing, 'packages' => $packages]);
        break;

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

    // GET: Lấy bảng giá
    case 'get_pricing':
        requireAdmin();
        jsonResponse(['success' => true, 'data' => [
            'price_pkg_10'        => (int)getSetting('price_pkg_10', (string)PRICE_PKG_10),
            'price_pkg_30'        => (int)getSetting('price_pkg_30', (string)PRICE_PKG_30),
            'price_social_morning'=> (int)getSetting('price_social_morning', (string)PRICE_SOCIAL_MORNING),
            'price_social_noon'   => (int)getSetting('price_social_noon', (string)PRICE_SOCIAL_NOON),
            'price_social_evening'=> (int)getSetting('price_social_evening', (string)PRICE_SOCIAL_EVENING),
            'price_kids'          => (int)getSetting('price_kids', (string)PRICE_KIDS),
        ]]);
        break;

    // POST: Lưu bảng giá
    case 'save_pricing':
        requireAdmin();
        $input = getJsonInput();
        $db = getDB();
        $keys = ['price_pkg_10','price_pkg_30','price_social_morning','price_social_noon','price_social_evening','price_kids'];
        $stmt = $db->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($keys as $k) {
            if (isset($input[$k])) {
                $stmt->execute([$k, (string)(int)$input[$k]]);
            }
        }
        jsonResponse(['success' => true, 'message' => 'Đã lưu bảng giá']);
        break;

    // GET: Lấy cấu hình ngân hàng
    case 'get_bank':
        requireAdmin();
        jsonResponse(['success' => true, 'data' => [
            'bank_id'      => getSetting('bank_id', BANK_ID),
            'bank_account' => getSetting('bank_account', BANK_ACCOUNT),
            'bank_owner'   => getSetting('bank_owner', BANK_OWNER),
            'bank_name'    => getSetting('bank_name', BANK_NAME),
        ]]);
        break;

    // POST: Lưu cấu hình ngân hàng
    case 'save_bank':
        requireAdmin();
        $input = getJsonInput();
        $db = getDB();
        $keys = ['bank_id', 'bank_account', 'bank_owner', 'bank_name'];
        $stmt = $db->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($keys as $k) {
            if (isset($input[$k]) && trim($input[$k]) !== '') {
                $stmt->execute([$k, trim($input[$k])]);
            }
        }
        jsonResponse(['success' => true, 'message' => 'Đã lưu thông tin ngân hàng']);
        break;

    // ============ PACKAGE MANAGEMENT ============

    // GET: Lấy danh sách gói tập (tất cả hoặc chỉ active)
    case 'list_packages':
        requireAdmin();
        $showAll = ($_GET['all'] ?? '') === '1';
        $packages = getPackages();
        if ($showAll) {
            // Trả về cả inactive
            $packages = getDB()->query("SELECT * FROM packages ORDER BY sort_order ASC, id ASC")->fetchAll();
        }
        jsonResponse(['success' => true, 'packages' => $packages]);
        break;

    // POST: Tạo gói tập mới
    case 'create_package':
        requireAdmin();
        $input = getJsonInput();
        $name = trim($input['name'] ?? '');
        $sessions = (int)($input['sessions'] ?? 0);
        $price = (int)($input['price'] ?? 0);
        $expiryDays = (int)($input['expiry_days'] ?? 30);
        $badge = trim($input['badge'] ?? '');
        $sortOrder = (int)($input['sort_order'] ?? 0);

        if (!$name) jsonResponse(['error' => 'Tên gói không được để trống'], 400);
        if ($sessions <= 0) jsonResponse(['error' => 'Số buổi phải lớn hơn 0'], 400);
        if ($price <= 0) jsonResponse(['error' => 'Giá phải lớn hơn 0'], 400);

        // Auto-generate slug
        $slug = 'pkg_' . time() . '_' . rand(100, 999);

        $db = getDB();
        // Ensure table exists
        getPackages();

        $stmt = $db->prepare("INSERT INTO packages (slug, name, sessions, price, expiry_days, badge, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$slug, $name, $sessions, $price, $expiryDays, $badge ?: null, $sortOrder]);
        $id = (int)$db->lastInsertId();

        jsonResponse(['success' => true, 'id' => $id, 'message' => 'Đã tạo gói tập mới']);
        break;

    // POST: Cập nhật gói tập
    case 'update_package':
        requireAdmin();
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'ID không hợp lệ'], 400);

        $name = trim($input['name'] ?? '');
        $sessions = (int)($input['sessions'] ?? 0);
        $price = (int)($input['price'] ?? 0);
        $expiryDays = (int)($input['expiry_days'] ?? 30);
        $badge = trim($input['badge'] ?? '');
        $sortOrder = (int)($input['sort_order'] ?? 0);

        if (!$name) jsonResponse(['error' => 'Tên gói không được để trống'], 400);
        if ($sessions <= 0) jsonResponse(['error' => 'Số buổi phải lớn hơn 0'], 400);
        if ($price <= 0) jsonResponse(['error' => 'Giá phải lớn hơn 0'], 400);

        $db = getDB();
        $stmt = $db->prepare("UPDATE packages SET name=?, sessions=?, price=?, expiry_days=?, badge=?, sort_order=? WHERE id=?");
        $stmt->execute([$name, $sessions, $price, $expiryDays, $badge ?: null, $sortOrder, $id]);

        jsonResponse(['success' => true, 'message' => 'Đã cập nhật gói tập']);
        break;

    // POST: Xóa (ẩn) gói tập
    case 'delete_package':
        requireAdmin();
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'ID không hợp lệ'], 400);

        $db = getDB();
        $db->prepare("UPDATE packages SET active = 0 WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Đã xóa gói tập']);
        break;

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
