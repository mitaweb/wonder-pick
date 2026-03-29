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

    case 'get':
        $phone = sanitizePhone($_GET['phone'] ?? '');
        if (!$phone) jsonResponse(['error' => 'Thiếu số điện thoại'], 400);
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();
        if (!$customer) jsonResponse(['data' => null]);
        $expired = $customer['expires_at'] && strtotime($customer['expires_at']) < strtotime('today');
        $stmt2 = $db->prepare("SELECT * FROM checkins WHERE phone = ? ORDER BY checked_in_at DESC LIMIT 10");
        $stmt2->execute([$phone]);
        $checkins = $stmt2->fetchAll();
        jsonResponse(['data' => $customer, 'checkins' => $checkins, 'expired' => $expired]);
        break;

    case 'register':
        $input = getJsonInput();
        $phone = sanitizePhone($input['phone'] ?? '');
        $name  = trim($input['name'] ?? '');
        if (!$phone || !$name) jsonResponse(['error' => 'Thiếu thông tin'], 400);
        if (strlen($phone) < 9) jsonResponse(['error' => 'Số điện thoại không hợp lệ'], 400);
        $db = getDB();
        $check = $db->prepare("SELECT id FROM customers WHERE phone = ?");
        $check->execute([$phone]);
        if ($check->fetch()) jsonResponse(['error' => 'Số điện thoại đã đăng ký'], 409);
        $db->prepare("INSERT INTO customers (phone, name, sessions, max_sessions, pkg) VALUES (?, ?, 0, 0, 'none')")->execute([$phone, $name]);
        $stmt = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        jsonResponse(['success' => true, 'data' => $stmt->fetch()]);
        break;

    case 'checkin':
        $input = getJsonInput();
        $phone = sanitizePhone($input['phone'] ?? '');
        if (!$phone) jsonResponse(['error' => 'Thiếu số điện thoại'], 400);
        $db = getDB();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT * FROM customers WHERE phone = ? FOR UPDATE");
            $stmt->execute([$phone]);
            $customer = $stmt->fetch();
            if (!$customer) { $db->rollBack(); jsonResponse(['error' => 'Không tìm thấy khách hàng'], 404); }
            if ((int)$customer['sessions'] <= 0) { $db->rollBack(); jsonResponse(['error' => 'Hết buổi tập'], 400); }
            if ($customer['expires_at'] && strtotime($customer['expires_at']) < strtotime('today')) {
                $db->rollBack(); jsonResponse(['error' => 'Thẻ đã hết hạn sử dụng'], 400);
            }
            $before = (int)$customer['sessions'];
            $after  = $before - 1;
            $db->prepare("UPDATE customers SET sessions = ? WHERE phone = ?")->execute([$after, $phone]);
            $db->prepare("INSERT INTO checkins (phone, sessions_before, sessions_after) VALUES (?, ?, ?)")->execute([$phone, $before, $after]);
            $db->commit();
            $updated = $db->prepare("SELECT * FROM customers WHERE phone = ?");
            $updated->execute([$phone]);
            jsonResponse(['success' => true, 'data' => $updated->fetch(), 'sessions_before' => $before, 'sessions_after' => $after]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => $e->getMessage()], 500);
        }
        break;

    case 'add_sessions':
        $input = getJsonInput();
        if (($input['admin_token'] ?? '') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error' => 'Unauthorized'], 401);
        $phone    = sanitizePhone($input['phone'] ?? '');
        $sessions = (int)($input['sessions'] ?? 0);
        $pkg      = $input['pkg'] ?? 'manual';
        $note     = $input['note'] ?? 'Admin cộng thủ công';
        if (!$phone || $sessions <= 0) jsonResponse(['error' => 'Dữ liệu không hợp lệ'], 400);
        $db = getDB();
        $custStmt = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $custStmt->execute([$phone]);
        $cust = $custStmt->fetch();
        if (!$cust) jsonResponse(['error' => 'Không tìm thấy khách hàng'], 404);
        $newExpiry = in_array($pkg, ['pkg_10','pkg_30']) ? calcExpiry($pkg, $cust['expires_at']) : $cust['expires_at'];
        $db->prepare("UPDATE customers SET sessions = sessions + ?, max_sessions = max_sessions + ?, expires_at = ? WHERE phone = ?")
           ->execute([$sessions, $sessions, $newExpiry, $phone]);
        $db->prepare("INSERT INTO session_packages (phone, pkg, sessions_added, added_by, note) VALUES (?, ?, ?, 'admin', ?)")
           ->execute([$phone, $pkg, $sessions, $note]);
        $updated = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $updated->execute([$phone]);
        jsonResponse(['success' => true, 'data' => $updated->fetch()]);
        break;

    case 'all':
        if (($_GET['admin_token'] ?? '') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error' => 'Unauthorized'], 401);
        $db = getDB();
        $search = '%' . trim($_GET['search'] ?? '') . '%';
        $stmt = $db->prepare("SELECT * FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY sessions ASC");
        $stmt->execute([$search, $search]);
        $customers = $stmt->fetchAll();
        $total   = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        $lowSess = $db->query("SELECT COUNT(*) FROM customers WHERE sessions > 0 AND sessions <= 3")->fetchColumn();
        $today   = date('Y-m-d');
        $ciToday = $db->prepare("SELECT COUNT(*) FROM checkins WHERE DATE(checked_in_at) = ?");
        $ciToday->execute([$today]);
        $recentOrders = $db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT 30");
        $recentOrders->execute();
        jsonResponse([
            'customers' => $customers,
            'recent_orders' => $recentOrders->fetchAll(),
            'stats' => ['total' => $total, 'low_sessions' => $lowSess, 'checkins_today' => $ciToday->fetchColumn()]
        ]);
        break;

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
