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
        $count = max(1, (int)($input['count'] ?? 1)); // Số người vào chơi
        if (!$phone) jsonResponse(['error' => 'Thiếu số điện thoại'], 400);
        if ($count > 20) jsonResponse(['error' => 'Số người không hợp lệ (tối đa 20)'], 400);
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
            if ($before < $count) {
                $db->rollBack(); jsonResponse(['error' => "Không đủ lượt! Còn {$before} buổi, cần {$count} buổi cho {$count} người."], 400);
            }
            $after  = $before - $count;
            $note   = $count > 1 ? "{$count} người vào chơi" : null;
            $db->prepare("UPDATE customers SET sessions = ? WHERE phone = ?")->execute([$after, $phone]);
            $db->prepare("INSERT INTO checkins (phone, sessions_before, sessions_after, note, people_count) VALUES (?, ?, ?, ?, ?)")->execute([$phone, $before, $after, $note, $count]);
            $db->commit();
            $updated = $db->prepare("SELECT * FROM customers WHERE phone = ?");
            $updated->execute([$phone]);
            $updatedData = $updated->fetch();

            // Gửi email thông báo check-in nếu có email
            $custEmail = $updatedData['email'] ?? '';
            $smtpEnabled = getSetting('smtp_enabled', '0');
            if ($custEmail && $smtpEnabled === '1') {
                $pplText = $count > 1 ? "<tr><td style='padding:6px 0;color:#666'>Số người</td><td style='padding:6px 0;font-weight:600'>{$count} người</td></tr>" : "";
                $dateStr = date('d/m/Y H:i');
                $html = emailTemplate(
                    'Check-in thành công! ✓',
                    "<p>Xin chào <strong>{$updatedData['name']}</strong>,</p>
                    <p>Bạn vừa check-in tại <strong>Wonder Pickleball</strong>:</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0'>
                        {$pplText}
                        <tr><td style='padding:6px 0;color:#666'>Lượt đã trừ</td><td style='padding:6px 0;font-weight:600'>{$count} lượt</td></tr>
                        <tr><td style='padding:6px 0;color:#666'>Lượt còn lại</td><td style='padding:6px 0;font-weight:600;color:#1D9E75;font-size:18px'>{$after} lượt</td></tr>
                        <tr><td style='padding:6px 0;color:#666'>Thời gian</td><td style='padding:6px 0'>{$dateStr}</td></tr>
                    </table>
                    <p>Chúc bạn chơi vui vẻ! 🏓</p>"
                );
                sendMail($custEmail, $updatedData['name'], '[Wonder Pickleball] Check-in thành công', $html);
            }

            jsonResponse(['success' => true, 'data' => $updatedData, 'sessions_before' => $before, 'sessions_after' => $after, 'people_count' => $count]);
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

    // GET (admin): Báo cáo & thống kê
    case 'report':
        if (($_GET['admin_token'] ?? '') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error' => 'Unauthorized'], 401);
        $db = getDB();
        $today = date('Y-m-d');

        // 1. Số người đến chơi hôm nay (tổng people_count hoặc count checkins)
        $ciToday = $db->prepare("SELECT COUNT(*) as total_checkins, COALESCE(SUM(people_count),COUNT(*)) as total_people FROM checkins WHERE DATE(checked_in_at) = ?");
        $ciToday->execute([$today]);
        $checkinStats = $ciToday->fetch();

        // Danh sách check-in hôm nay
        $ciList = $db->prepare("SELECT c.phone, c.sessions_before, c.sessions_after, c.checked_in_at, c.note, c.people_count, cu.name FROM checkins c LEFT JOIN customers cu ON c.phone = cu.phone WHERE DATE(c.checked_in_at) = ? ORDER BY c.checked_in_at DESC");
        $ciList->execute([$today]);
        $todayCheckins = $ciList->fetchAll();

        // 2. Hội viên còn dưới 5 buổi tập (có buổi > 0 và <= 5)
        $lowMembers = $db->query("SELECT phone, name, sessions, max_sessions, expires_at FROM customers WHERE sessions > 0 AND sessions <= 5 ORDER BY sessions ASC")->fetchAll();

        // 3. Doanh thu hôm nay (tổng đơn Paid hôm nay)
        $revToday = $db->prepare("SELECT COALESCE(SUM(amount),0) as revenue, COUNT(*) as paid_count FROM orders WHERE payment_status='Paid' AND DATE(paid_at) = ?");
        $revToday->execute([$today]);
        $revenueStats = $revToday->fetch();

        // Doanh thu 7 ngày gần nhất
        $rev7 = $db->prepare("SELECT DATE(paid_at) as day, SUM(amount) as revenue, COUNT(*) as orders FROM orders WHERE payment_status='Paid' AND paid_at >= DATE_SUB(?, INTERVAL 7 DAY) GROUP BY DATE(paid_at) ORDER BY day DESC");
        $rev7->execute([$today]);
        $revenue7days = $rev7->fetchAll();

        // Đơn hàng hôm nay
        $ordToday = $db->prepare("SELECT * FROM orders WHERE DATE(created_at) = ? ORDER BY created_at DESC");
        $ordToday->execute([$today]);
        $todayOrders = $ordToday->fetchAll();

        jsonResponse([
            'success' => true,
            'checkin_stats' => [
                'total_checkins' => (int)$checkinStats['total_checkins'],
                'total_people' => (int)$checkinStats['total_people'],
            ],
            'today_checkins' => $todayCheckins,
            'low_session_members' => $lowMembers,
            'revenue' => [
                'today' => (float)$revenueStats['revenue'],
                'paid_count' => (int)$revenueStats['paid_count'],
            ],
            'revenue_7days' => $revenue7days,
            'today_orders' => $todayOrders,
        ]);
        break;

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
