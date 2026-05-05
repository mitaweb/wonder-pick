<?php
// ============================================================
//  Wonder Pickleball — Cấu hình
//  *** CHỈNH SỬA CÁC DÒNG DƯỚI ĐÂY ***
// ============================================================

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'wonder_pickleball');
define('DB_USER', 'root');               // username MySQL của anh
define('DB_PASS', '');                   // password MySQL của anh
define('DB_CHARSET', 'utf8mb4');

// --- App ---
define('ADMIN_PASSWORD', 'wonder2024');  // đổi mật khẩu admin
define('APP_NAME', 'Wonder Pickleball');
define('APP_URL', 'https://yourdomain.com'); // URL website của anh (không có dấu / cuối)

// --- Ngân hàng nhận thanh toán (hiển thị trên QR) ---
define('BANK_ID',      'OCB');
define('BANK_ACCOUNT', '0789475288');
define('BANK_OWNER',   'Pham Thi Thuong');
define('BANK_NAME',    'OCB - Ngân hàng Phương Đông');

// --- Sepay Webhook ---
// Điền API Key vào đây để bảo mật webhook endpoint

// --- Bảng giá ---
// Gói thẻ
define('PRICE_PKG_10', 600000);   // Gói 10 tặng 3 = 13 buổi, 600k, hết hạn 1 tháng
define('PRICE_PKG_30', 1800000);  // Gói 30 tặng 10 = 40 buổi, 1.800k, hết hạn 3 tháng

// Giá lẻ theo khung giờ (VNĐ/buổi)
define('PRICE_SOCIAL_MORNING', 60000);  // 8h - 11h
define('PRICE_SOCIAL_NOON',    70000);  // 11h - 16h
define('PRICE_SOCIAL_EVENING', 80000);  // 16h - 22h

// Khu vui chơi trẻ em
define('PRICE_KIDS', 20000);  // 20k/trẻ, không giới hạn giờ

// Prefix mã đơn hàng trong nội dung chuyển khoản
define('ORDER_PREFIX', 'WP');

// Timezone Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ============================================================
//  KHÔNG CẦN CHỈNH PHẦN DƯỚI
// ============================================================

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonInput(): array {
    static $cache = null;
    if ($cache === null) {
        $cache = json_decode(file_get_contents('php://input'), true) ?? [];
    }
    return $cache;
}

function sanitizePhone(string $phone): string {
    return preg_replace('/\D/', '', $phone);
}

function formatPhone(string $phone): string {
    $d = sanitizePhone($phone);
    if (strlen($d) === 10) return substr($d,0,4).' '.substr($d,4,3).' '.substr($d,7,3);
    return $d;
}

// Lấy giá lẻ theo giờ hiện tại
function getCurrentSinglePrice(): array {
    $hour = (int)date('G'); // 0-23
    if ($hour >= 8 && $hour < 11) {
        return ['price' => PRICE_SOCIAL_MORNING, 'slot' => '8h - 11h', 'label' => 'Sáng'];
    } elseif ($hour >= 11 && $hour < 16) {
        return ['price' => PRICE_SOCIAL_NOON, 'slot' => '11h - 16h', 'label' => 'Trưa'];
    } elseif ($hour >= 16 && $hour < 22) {
        return ['price' => PRICE_SOCIAL_EVENING, 'slot' => '16h - 22h', 'label' => 'Chiều/Tối'];
    } else {
        return ['price' => PRICE_SOCIAL_EVENING, 'slot' => 'Ngoài giờ', 'label' => 'Đặc biệt'];
    }
}

// Tính ngày hết hạn theo gói
function calcExpiry(string $pkg, ?string $currentExpiry = null): string {
    $base = ($currentExpiry && strtotime($currentExpiry) > time())
        ? new DateTime($currentExpiry)
        : new DateTime();
    if ($pkg === 'pkg_10') $base->modify('+1 month');
    elseif ($pkg === 'pkg_30') $base->modify('+3 months');
    return $base->format('Y-m-d');
}


// ---- Lấy danh sách gói tập từ DB ----
function getPackages(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $db = getDB();
        // Auto-create table if not exists
        $db->exec("CREATE TABLE IF NOT EXISTS packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            sessions INT NOT NULL DEFAULT 0,
            price INT NOT NULL DEFAULT 0,
            expiry_days INT NOT NULL DEFAULT 30,
            badge VARCHAR(50) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        // Ensure charset for existing tables
        $db->exec("ALTER TABLE packages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        // Migrate: add is_walkin column to customers
        try { $db->exec("ALTER TABLE customers ADD COLUMN is_walkin TINYINT(1) NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
        $rows = $db->query("SELECT * FROM packages WHERE active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
        if (empty($rows)) {
            // Insert defaults
            $db->exec("INSERT IGNORE INTO packages (slug, name, sessions, price, expiry_days, badge, sort_order) VALUES
                ('pkg_10', 'Gói 10 tặng 3', 13, " . PRICE_PKG_10 . ", 30, 'Phổ biến', 1),
                ('pkg_30', 'Gói 30 tặng 10', 40, " . PRICE_PKG_30 . ", 90, NULL, 2)");
            $rows = $db->query("SELECT * FROM packages WHERE active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
        }
        $cache = $rows;
    } catch (\Throwable $e) {
        $cache = [];
    }
    return $cache;
}

// ---- Lấy giá từ DB (fallback config) ----
function getPrice(string $key): int {
    $map = [
        'price_pkg_10'         => PRICE_PKG_10,
        'price_pkg_30'         => PRICE_PKG_30,
        'price_social_morning' => PRICE_SOCIAL_MORNING,
        'price_social_noon'    => PRICE_SOCIAL_NOON,
        'price_social_evening' => PRICE_SOCIAL_EVENING,
        'price_kids'           => PRICE_KIDS,
    ];
    $val = getSetting($key, '');
    return $val !== '' ? (int)$val : ($map[$key] ?? 0);
}

// Lấy giá lẻ theo giờ hiện tại (từ DB)
function getCurrentSinglePriceDynamic(): array {
    $hour = (int)date('G');
    if ($hour >= 8 && $hour < 11) {
        return ['price' => getPrice('price_social_morning'), 'slot' => '8h - 11h', 'label' => 'Sáng'];
    } elseif ($hour >= 11 && $hour < 16) {
        return ['price' => getPrice('price_social_noon'), 'slot' => '11h - 16h', 'label' => 'Trưa'];
    } elseif ($hour >= 16 && $hour < 22) {
        return ['price' => getPrice('price_social_evening'), 'slot' => '16h - 22h', 'label' => 'Chiều/Tối'];
    } else {
        return ['price' => getPrice('price_social_evening'), 'slot' => 'Ngoài giờ', 'label' => 'Đặc biệt'];
    }
}

// ---- Lấy thông tin ngân hàng (DB → fallback config) ----
function getBankConfig(): array {
    return [
        'bank_id'      => getSetting('bank_id', BANK_ID),
        'bank_account' => getSetting('bank_account', BANK_ACCOUNT),
        'bank_owner'   => getSetting('bank_owner', BANK_OWNER),
        'bank_name'    => getSetting('bank_name', BANK_NAME),
    ];
}

// ---- Email queue (gửi email không đồng bộ) ----
function queueMail(string $toEmail, string $toName, string $subject, string $htmlBody): void {
    try {
        $db = getDB();
        $db->exec("CREATE TABLE IF NOT EXISTS email_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            to_email VARCHAR(255) NOT NULL,
            to_name VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body MEDIUMTEXT NOT NULL,
            status ENUM('pending','sent','failed') DEFAULT 'pending',
            attempts INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            sent_at DATETIME DEFAULT NULL,
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $db->prepare("INSERT INTO email_queue (to_email, to_name, subject, body) VALUES (?, ?, ?, ?)")
           ->execute([$toEmail, $toName, $subject, $htmlBody]);
    } catch (\Throwable $e) {
        error_log('[WP Mail Queue] ' . $e->getMessage());
    }
}

// ---- Rate limiting (chống spam) ----
function rateLimit(string $scope = 'global', int $maxRequests = 30, int $windowSeconds = 60): void {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ip = explode(',', $ip)[0];
    $ip = trim($ip);
    try {
        $db = getDB();
        $db->exec("CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            scope VARCHAR(50) NOT NULL DEFAULT 'global',
            requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_scope_time (ip, scope, requested_at)
        ) ENGINE=InnoDB");
        // Xóa record cũ (> 5 phút)
        $db->exec("DELETE FROM rate_limits WHERE requested_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        // Đếm request trong window
        $stmt = $db->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND scope = ? AND requested_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$ip, $scope, $windowSeconds]);
        $count = (int)$stmt->fetchColumn();
        if ($count >= $maxRequests) {
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            header('Retry-After: ' . $windowSeconds);
            echo json_encode(['error' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        // Ghi request mới
        $db->prepare("INSERT INTO rate_limits (ip, scope) VALUES (?, ?)")->execute([$ip, $scope]);
    } catch (\Throwable $e) {
        // Nếu DB lỗi, cho qua (không block user)
    }
}

// ---- Đọc setting từ DB (cached) ----
function getSetting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        try {
            $rows = getDB()->query("SELECT setting_key, setting_value FROM app_settings")->fetchAll();
            $cache = array_column($rows, 'setting_value', 'setting_key');
        } catch (\Throwable $e) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

// ---- Gửi email qua Gmail SMTP (PHPMailer-lite thuần PHP) ----
function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    $host    = getSetting('smtp_host', 'smtp.gmail.com');
    $port    = (int)getSetting('smtp_port', '587');
    $user    = getSetting('smtp_user', '');
    $pass    = getSetting('smtp_pass', '');
    $from    = $user;
    $fromName= getSetting('smtp_from_name', APP_NAME);

    if (!$user || !$pass) {
        error_log('[WP Mail] SMTP chưa cấu hình');
        return false;
    }

    // Kết nối SMTP thủ công (không cần thư viện ngoài)
    $errno = 0; $errstr = '';
    $sock = @fsockopen('tls://'.$host, $port, $errno, $errstr, 15);
    if (!$sock) {
        // Thử STARTTLS trên port 587
        $sock = @fsockopen($host, $port, $errno, $errstr, 15);
        if (!$sock) { error_log('[WP Mail] Cannot connect: '.$errstr); return false; }
        if (!smtpRead($sock)) return false;
        smtpSend($sock, "EHLO localhost");
        smtpRead($sock);
        smtpSend($sock, "STARTTLS");
        smtpRead($sock);
        stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    } else {
        smtpRead($sock);
    }

    smtpSend($sock, "EHLO localhost");          smtpRead($sock);
    smtpSend($sock, "AUTH LOGIN");              smtpRead($sock);
    smtpSend($sock, base64_encode($user));      smtpRead($sock);
    smtpSend($sock, base64_encode($pass));
    $authResp = smtpRead($sock);
    if (strpos($authResp, '235') === false) {
        error_log('[WP Mail] Auth failed: '.$authResp);
        fclose($sock); return false;
    }

    smtpSend($sock, "MAIL FROM:<{$from}>"); smtpRead($sock);
    smtpSend($sock, "RCPT TO:<{$toEmail}>"); smtpRead($sock);
    smtpSend($sock, "DATA"); smtpRead($sock);

    $boundary = md5(uniqid());
    $msg  = "From: =?UTF-8?B?".base64_encode($fromName)."?= <{$from}>\r\n";
    $msg .= "To: =?UTF-8?B?".base64_encode($toName)."?= <{$toEmail}>\r\n";
    $msg .= "Subject: =?UTF-8?B?".base64_encode($subject)."?=\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: base64\r\n";
    $msg .= "\r\n".chunk_split(base64_encode($htmlBody))."\r\n.\r\n";

    smtpSend($sock, $msg);
    $resp = smtpRead($sock);
    smtpSend($sock, "QUIT");
    fclose($sock);

    $ok = strpos($resp, '250') !== false;
    if (!$ok) error_log('[WP Mail] Send failed: '.$resp);
    return $ok;
}

function smtpSend($sock, string $cmd): void { fwrite($sock, $cmd."\r\n"); }
function smtpRead($sock): string {
    $resp = ''; $line = '';
    while (($line = fgets($sock, 512)) !== false) {
        $resp .= $line;
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    return $resp;
}

// ---- Template email ----
function emailTemplate(string $title, string $body): string {
    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>body{font-family:'Segoe UI',sans-serif;background:#f0efe9;margin:0;padding:24px}
.box{background:white;border-radius:16px;max-width:480px;margin:0 auto;padding:32px}
.logo{background:#1D9E75;width:48px;height:48px;border-radius:12px;margin:0 auto 20px;display:flex;align-items:center;justify-content:center}
h1{font-size:20px;font-weight:600;color:#085041;text-align:center;margin:0 0 8px}
p{color:#555;font-size:14px;line-height:1.6;margin:12px 0}
.btn{display:block;background:#1D9E75;color:white;text-decoration:none;border-radius:10px;padding:14px 24px;text-align:center;font-weight:600;font-size:15px;margin:20px 0}
.note{font-size:12px;color:#999;text-align:center;margin-top:16px}
.divider{border:none;border-top:1px solid #eee;margin:20px 0}
</style></head><body><div class="box">
<div style="text-align:center"><div class="logo" style="display:inline-flex">🏓</div></div>
<h1>$title</h1>$body
<hr class="divider">
<p class="note">Wonder Pickleball · Không trả lời email này</p>
</div></body></html>
HTML;
}
