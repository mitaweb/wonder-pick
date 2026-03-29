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
    return json_decode(file_get_contents('php://input'), true) ?? [];
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

// ---- Gửi thông báo qua Zalo OA ----
function sendZaloNotification(string $accessToken, string $phone, string $userName, string $message): bool {
    // Zalo OA API v3 - gửi tin nhắn tư vấn (consultation message)
    // Cần user đã follow OA và đồng ý nhận tin
    $url = 'https://openapi.zalo.me/v3.0/oa/message/cs';

    $data = [
        'recipient' => ['user_id' => $phone],
        'message' => [
            'text' => $message
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'access_token: ' . $accessToken
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response) {
        $result = json_decode($response, true);
        if (($result['error'] ?? -1) != 0) {
            error_log('[WP Zalo] Send failed: ' . $response);
            return false;
        }
        return true;
    }
    error_log('[WP Zalo] Connection failed, HTTP ' . $httpCode);
    return false;
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
