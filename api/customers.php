<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

$action = $_GET['action'] ?? '';

// Rate limiting cho endpoint public (get, register, checkin)
if (in_array($action, ['get', 'register', 'checkin'])) {
    rateLimit('customers_' . $action, 30, 60);
}

switch ($action) {

    case 'get':
        $phone = sanitizePhone($_GET['phone'] ?? '');
        if (!$phone) jsonResponse(['error' => 'Thiếu số điện thoại'], 400);
        $db = getDB();
        // Nếu nhập ít hơn 9 số → tìm theo số cuối (LIKE %xxxx)
        if (strlen($phone) < 9) {
            if (strlen($phone) < 4) jsonResponse(['error' => 'Nhập tối thiểu 4 số'], 400);
            $stmt = $db->prepare("SELECT phone, name, sessions, max_sessions, expires_at FROM customers WHERE phone LIKE ? ORDER BY name ASC LIMIT 20");
            $stmt->execute(['%' . $phone]);
            $matches = $stmt->fetchAll();
            jsonResponse(['matches' => $matches, 'partial' => true]);
        }
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

            // Gửi email thông báo check-in (qua queue, không chờ đợi)
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
                queueMail($custEmail, $updatedData['name'], '[Wonder Pickleball] Check-in thành công', $html);
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
        // Tìm expiry_days từ packages table
        $expiryDays = 0;
        $packages = getPackages();
        foreach ($packages as $p) {
            if ($p['slug'] === $pkg) { $expiryDays = (int)$p['expiry_days']; break; }
        }
        if (!$expiryDays && $pkg === 'pkg_10') $expiryDays = 30;
        if (!$expiryDays && $pkg === 'pkg_30') $expiryDays = 90;
        $newExpiry = $cust['expires_at'];
        if ($expiryDays > 0) {
            $base = ($newExpiry && strtotime($newExpiry) > time()) ? new DateTime($newExpiry) : new DateTime();
            $base->modify('+' . $expiryDays . ' days');
            $newExpiry = $base->format('Y-m-d');
        }
        $db->prepare("UPDATE customers SET sessions = sessions + ?, max_sessions = max_sessions + ?, expires_at = ? WHERE phone = ?")
           ->execute([$sessions, $sessions, $newExpiry, $phone]);
        $db->prepare("INSERT INTO session_packages (phone, pkg, sessions_added, added_by, note) VALUES (?, ?, ?, 'admin', ?)")
           ->execute([$phone, $pkg, $sessions, $note]);
        $updated = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $updated->execute([$phone]);
        jsonResponse(['success' => true, 'data' => $updated->fetch()]);
        break;

    // POST (admin): Cập nhật thông tin thành viên
    case 'update_customer':
        $input = getJsonInput();
        if (($input['admin_token'] ?? '') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error' => 'Unauthorized'], 401);
        $phone = sanitizePhone($input['phone'] ?? '');
        if (!$phone) jsonResponse(['error' => 'Thiếu số điện thoại'], 400);
        $db = getDB();
        $custStmt = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $custStmt->execute([$phone]);
        $cust = $custStmt->fetch();
        if (!$cust) jsonResponse(['error' => 'Không tìm thấy khách hàng'], 404);

        $name     = trim($input['name'] ?? $cust['name']);
        $email    = trim($input['email'] ?? $cust['email'] ?? '');
        $sessions = isset($input['sessions']) ? max(0, (int)$input['sessions']) : (int)$cust['sessions'];
        $maxSess  = isset($input['max_sessions']) ? max(0, (int)$input['max_sessions']) : (int)$cust['max_sessions'];
        $expiry   = $input['expires_at'] ?? $cust['expires_at'];

        if (!$name) jsonResponse(['error' => 'Tên không được để trống'], 400);

        $db->prepare("UPDATE customers SET name = ?, email = ?, sessions = ?, max_sessions = ?, expires_at = ? WHERE phone = ?")
           ->execute([$name, $email ?: null, $sessions, $maxSess, $expiry ?: null, $phone]);

        // Cập nhật mật khẩu nếu admin gửi new_password
        $newPw = $input['new_password'] ?? '';
        if ($newPw) {
            if (strlen($newPw) < 6) jsonResponse(['error' => 'Mật khẩu tối thiểu 6 ký tự'], 400);
            $hash = password_hash($newPw, PASSWORD_BCRYPT);
            $db->prepare("UPDATE customers SET password_hash = ? WHERE phone = ?")->execute([$hash, $phone]);
        }

        $updated = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $updated->execute([$phone]);
        jsonResponse(['success' => true, 'data' => $updated->fetch()]);
        break;

    // POST (admin): Xóa tài khoản khách hàng
    case 'delete_customer':
        $input = getJsonInput();
        if (($input['admin_token'] ?? '') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error' => 'Unauthorized'], 401);
        $phone = sanitizePhone($input['phone'] ?? '');
        if (!$phone) jsonResponse(['error' => 'Thiếu số điện thoại'], 400);
        $db = getDB();
        $chk = $db->prepare("SELECT id FROM customers WHERE phone = ?");
        $chk->execute([$phone]);
        if (!$chk->fetch()) jsonResponse(['error' => 'Không tìm thấy khách hàng'], 404);
        $db->prepare("DELETE FROM checkins WHERE phone = ?")->execute([$phone]);
        $db->prepare("DELETE FROM session_packages WHERE phone = ?")->execute([$phone]);
        $db->prepare("DELETE FROM orders WHERE phone = ?")->execute([$phone]);
        $db->prepare("DELETE FROM customers WHERE phone = ?")->execute([$phone]);
        jsonResponse(['success' => true]);
        break;

    // POST (admin): Tạo hàng loạt tài khoản khách hàng
    // Body: { admin_token, default_password, customers:[{name,phone,email,sessions}] }
    case 'bulk_create':
        $input = getJsonInput();
        if (($input['admin_token'] ?? '') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error' => 'Unauthorized'], 401);
        $list = $input['customers'] ?? [];
        if (!is_array($list) || count($list) === 0) jsonResponse(['error' => 'Danh sách khách hàng trống'], 400);
        $defaultPw = $input['default_password'] ?? '123456';
        $defaultHash = password_hash($defaultPw, PASSWORD_BCRYPT);
        $db = getDB();
        $created = 0; $skipped = 0; $errors = [];
        foreach ($list as $idx => $row) {
            $name  = trim($row['name'] ?? '');
            $phoneRaw = trim((string)($row['phone'] ?? ''));
            // Tự thêm 0 nếu thiếu
            if ($phoneRaw !== '' && $phoneRaw[0] !== '0' && ctype_digit($phoneRaw)) $phoneRaw = '0' . $phoneRaw;
            $phone = sanitizePhone($phoneRaw);
            $email = strtolower(trim($row['email'] ?? ''));
            $sessions = max(0, (int)($row['sessions'] ?? 0));
            if (!$name || strlen($phone) < 9) { $skipped++; $errors[] = "Dòng " . ($idx+1) . ": thiếu tên hoặc SĐT không hợp lệ"; continue; }
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped++; $errors[] = "Dòng " . ($idx+1) . ": email không hợp lệ"; continue; }
            // Check trùng phone
            $chk = $db->prepare("SELECT id FROM customers WHERE phone = ?");
            $chk->execute([$phone]);
            if ($chk->fetch()) { $skipped++; $errors[] = "Dòng " . ($idx+1) . " ($phone): SĐT đã tồn tại"; continue; }
            // Check trùng email
            if ($email) {
                $chk2 = $db->prepare("SELECT id FROM customers WHERE email = ?");
                $chk2->execute([$email]);
                if ($chk2->fetch()) { $skipped++; $errors[] = "Dòng " . ($idx+1) . " ($email): email đã tồn tại"; continue; }
            }
            try {
                $db->prepare("INSERT INTO customers (phone, name, email, password_hash, sessions, max_sessions, pkg) VALUES (?, ?, ?, ?, ?, ?, 'none')")
                   ->execute([$phone, $name, $email ?: null, $defaultHash, $sessions, $sessions]);
                $created++;
            } catch (Exception $e) {
                $skipped++; $errors[] = "Dòng " . ($idx+1) . ": " . $e->getMessage();
            }
        }
        jsonResponse(['success' => true, 'created' => $created, 'skipped' => $skipped, 'errors' => $errors]);
        break;

    // GET (admin): Xuất CSV danh sách thành viên
    case 'export_csv':
        if (($_GET['admin_token'] ?? '') !== \md5(ADMIN_PASSWORD)) { http_response_code(401); echo 'Unauthorized'; exit; }
        $db = getDB();
        $customers = $db->query("SELECT name, phone, email, sessions, max_sessions, expires_at, created_at FROM customers ORDER BY name ASC")->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="wonder_members_' . date('Ymd_His') . '.csv"');

        $bom = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        $out = fopen('php://output', 'w');
        fwrite($out, $bom);
        fputcsv($out, ['Họ tên', 'Số điện thoại', 'Email', 'Buổi còn lại', 'Tổng buổi', 'Hết hạn', 'Ngày đăng ký']);
        foreach ($customers as $c) {
            fputcsv($out, [
                $c['name'],
                $c['phone'],
                $c['email'] ?? '',
                $c['sessions'],
                $c['max_sessions'],
                $c['expires_at'] ?? '',
                $c['created_at'],
            ]);
        }
        fclose($out);
        exit;

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

        // Date range params (optional)
        $dateFrom = $_GET['date_from'] ?? $today;
        $dateTo   = $_GET['date_to'] ?? $today;
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = $today;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) $dateTo = $today;

        // 1. Check-in trong khoảng ngày
        $ciStats = $db->prepare("SELECT COUNT(*) as total_checkins, COALESCE(SUM(people_count),COUNT(*)) as total_people FROM checkins WHERE DATE(checked_in_at) BETWEEN ? AND ?");
        $ciStats->execute([$dateFrom, $dateTo]);
        $checkinStats = $ciStats->fetch();

        // Check-in tách: combo (sở hữu gói combo) vs vãng lai (chỉ mua lẻ)
        // Combo = khách có ít nhất 1 đơn combo đã thanh toán HOẶC được admin cộng gói combo
        $ciCombo = $db->prepare("SELECT COUNT(*) as checkins, COALESCE(SUM(c.people_count),COUNT(*)) as people FROM checkins c WHERE DATE(c.checked_in_at) BETWEEN ? AND ? AND (c.phone IN (SELECT DISTINCT phone FROM orders WHERE payment_status='Paid' AND pkg_type NOT IN ('single','kids')) OR c.phone IN (SELECT DISTINCT phone FROM session_packages WHERE pkg NOT IN ('single','kids','manual')))");
        $ciCombo->execute([$dateFrom, $dateTo]);
        $ciComboStats = $ciCombo->fetch();
        $ciWalk = [
            'checkins' => (int)$checkinStats['total_checkins'] - (int)$ciComboStats['checkins'],
            'people' => (int)$checkinStats['total_people'] - (int)$ciComboStats['people'],
        ];

        // Lấy danh sách phone sở hữu combo (mua gói qua orders hoặc admin cộng)
        $comboPhones = [];
        $cpStmt = $db->query("SELECT DISTINCT phone FROM orders WHERE payment_status='Paid' AND pkg_type NOT IN ('single','kids') UNION SELECT DISTINCT phone FROM session_packages WHERE pkg NOT IN ('single','kids','manual')");
        foreach ($cpStmt as $r) { $comboPhones[$r['phone']] = true; }

        // Danh sách check-in trong khoảng ngày
        $ciList = $db->prepare("SELECT c.phone, c.sessions_before, c.sessions_after, c.checked_in_at, c.note, c.people_count, cu.name FROM checkins c LEFT JOIN customers cu ON c.phone = cu.phone WHERE DATE(c.checked_in_at) BETWEEN ? AND ? ORDER BY c.checked_in_at DESC");
        $ciList->execute([$dateFrom, $dateTo]);
        $dateCheckins = [];
        foreach ($ciList as $ci) {
            $ci['is_combo'] = isset($comboPhones[$ci['phone']]) ? 1 : 0;
            $dateCheckins[] = $ci;
        }

        // 2. Hội viên còn dưới 5 buổi tập (chỉ khách sở hữu gói combo)
        $lowMembers = $db->query("SELECT phone, name, sessions, max_sessions, expires_at FROM customers WHERE sessions > 0 AND sessions <= 5 AND (phone IN (SELECT DISTINCT phone FROM orders WHERE payment_status='Paid' AND pkg_type NOT IN ('single','kids')) OR phone IN (SELECT DISTINCT phone FROM session_packages WHERE pkg NOT IN ('single','kids','manual'))) ORDER BY sessions ASC")->fetchAll();

        // 3. Doanh thu trong khoảng ngày
        $revRange = $db->prepare("SELECT COALESCE(SUM(amount),0) as revenue, COUNT(*) as paid_count FROM orders WHERE payment_status='Paid' AND DATE(paid_at) BETWEEN ? AND ?");
        $revRange->execute([$dateFrom, $dateTo]);
        $revenueStats = $revRange->fetch();

        // Doanh thu tách: combo (đơn mua gói combo) vs vãng lai (đơn mua lẻ/kids)
        $revCombo = $db->prepare("SELECT COALESCE(SUM(amount),0) as revenue, COUNT(*) as paid_count FROM orders WHERE payment_status='Paid' AND pkg_type NOT IN ('single','kids') AND DATE(paid_at) BETWEEN ? AND ?");
        $revCombo->execute([$dateFrom, $dateTo]);
        $revComboStats = $revCombo->fetch();
        $revWalk = $db->prepare("SELECT COALESCE(SUM(amount),0) as revenue, COUNT(*) as paid_count FROM orders WHERE payment_status='Paid' AND pkg_type IN ('single','kids') AND DATE(paid_at) BETWEEN ? AND ?");
        $revWalk->execute([$dateFrom, $dateTo]);
        $revWalkStats = $revWalk->fetch();

        // Doanh thu theo ngày trong khoảng
        $revDays = $db->prepare("SELECT DATE(paid_at) as day, SUM(amount) as revenue, COUNT(*) as orders FROM orders WHERE payment_status='Paid' AND DATE(paid_at) BETWEEN ? AND ? GROUP BY DATE(paid_at) ORDER BY day DESC");
        $revDays->execute([$dateFrom, $dateTo]);
        $revenueDays = $revDays->fetchAll();

        // Doanh thu tháng này
        $monthStart = date('Y-m-01');
        $revMonth = $db->prepare("SELECT COALESCE(SUM(amount),0) as revenue, COUNT(*) as paid_count FROM orders WHERE payment_status='Paid' AND DATE(paid_at) >= ?");
        $revMonth->execute([$monthStart]);
        $revenueMonth = $revMonth->fetch();

        jsonResponse([
            'success' => true,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'checkin_stats' => [
                'total_checkins' => (int)$checkinStats['total_checkins'],
                'total_people' => (int)$checkinStats['total_people'],
            ],
            'checkin_combo' => [
                'checkins' => (int)$ciComboStats['checkins'],
                'people' => (int)$ciComboStats['people'],
            ],
            'checkin_walkin' => [
                'checkins' => $ciWalk['checkins'],
                'people' => $ciWalk['people'],
            ],
            'date_checkins' => $dateCheckins,
            'low_session_members' => $lowMembers,
            'revenue' => [
                'total' => (float)$revenueStats['revenue'],
                'paid_count' => (int)$revenueStats['paid_count'],
            ],
            'revenue_combo' => [
                'total' => (float)$revComboStats['revenue'],
                'paid_count' => (int)$revComboStats['paid_count'],
            ],
            'revenue_walkin' => [
                'total' => (float)$revWalkStats['revenue'],
                'paid_count' => (int)$revWalkStats['paid_count'],
            ],
            'revenue_days' => $revenueDays,
            'revenue_month' => [
                'total' => (float)$revenueMonth['revenue'],
                'paid_count' => (int)$revenueMonth['paid_count'],
                'month_label' => date('m/Y'),
            ],
        ]);
        break;

    // GET (admin): Xuất báo cáo Excel CSV
    case 'export_report':
        if (($_GET['admin_token'] ?? '') !== \md5(ADMIN_PASSWORD)) { http_response_code(401); echo 'Unauthorized'; exit; }
        $db = getDB();
        $today = date('Y-m-d');
        $dateFrom = $_GET['date_from'] ?? $today;
        $dateTo   = $_GET['date_to'] ?? $today;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = $today;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) $dateTo = $today;

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="wonder_report_' . $dateFrom . '_' . $dateTo . '.csv"');
        $bom = "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fwrite($out, $bom);

        // Sheet 1: Doanh thu
        fputcsv($out, ['=== DOANH THU (' . $dateFrom . ' → ' . $dateTo . ') ===']);
        fputcsv($out, ['Loại', 'Doanh thu', 'Số đơn']);
        $revAll = $db->prepare("SELECT COALESCE(SUM(amount),0) as rev, COUNT(*) as cnt FROM orders WHERE payment_status='Paid' AND DATE(paid_at) BETWEEN ? AND ?");
        $revAll->execute([$dateFrom, $dateTo]);
        $ra = $revAll->fetch();
        $revCombo = $db->prepare("SELECT COALESCE(SUM(amount),0) as rev, COUNT(*) as cnt FROM orders WHERE payment_status='Paid' AND pkg_type NOT IN ('single','kids') AND DATE(paid_at) BETWEEN ? AND ?");
        $revCombo->execute([$dateFrom, $dateTo]);
        $rc = $revCombo->fetch();
        $revWalk = $db->prepare("SELECT COALESCE(SUM(amount),0) as rev, COUNT(*) as cnt FROM orders WHERE payment_status='Paid' AND pkg_type IN ('single','kids') AND DATE(paid_at) BETWEEN ? AND ?");
        $revWalk->execute([$dateFrom, $dateTo]);
        $rw = $revWalk->fetch();
        fputcsv($out, ['Tổng cộng', $ra['rev'], $ra['cnt']]);
        fputcsv($out, ['Khách mua gói (combo)', $rc['rev'], $rc['cnt']]);
        fputcsv($out, ['Khách vãng lai (lẻ)', $rw['rev'], $rw['cnt']]);
        fputcsv($out, []);

        // Sheet 2: Doanh thu theo ngày
        fputcsv($out, ['=== DOANH THU THEO NGÀY ===']);
        fputcsv($out, ['Ngày', 'Doanh thu', 'Số đơn']);
        $revDays = $db->prepare("SELECT DATE(paid_at) as day, SUM(amount) as rev, COUNT(*) as cnt FROM orders WHERE payment_status='Paid' AND DATE(paid_at) BETWEEN ? AND ? GROUP BY DATE(paid_at) ORDER BY day DESC");
        $revDays->execute([$dateFrom, $dateTo]);
        foreach ($revDays as $d) { fputcsv($out, [$d['day'], $d['rev'], $d['cnt']]); }
        fputcsv($out, []);

        // Sheet 3: Lượt chơi
        fputcsv($out, ['=== LƯỢT CHƠI ===']);
        fputcsv($out, ['Loại', 'Lượt check-in', 'Số người']);
        $ciAll = $db->prepare("SELECT COUNT(*) as ci, COALESCE(SUM(people_count),COUNT(*)) as ppl FROM checkins WHERE DATE(checked_in_at) BETWEEN ? AND ?");
        $ciAll->execute([$dateFrom, $dateTo]);
        $ca = $ciAll->fetch();
        $ciCombo = $db->prepare("SELECT COUNT(*) as ci, COALESCE(SUM(c.people_count),COUNT(*)) as ppl FROM checkins c WHERE DATE(c.checked_in_at) BETWEEN ? AND ? AND (c.phone IN (SELECT DISTINCT phone FROM orders WHERE payment_status='Paid' AND pkg_type NOT IN ('single','kids')) OR c.phone IN (SELECT DISTINCT phone FROM session_packages WHERE pkg NOT IN ('single','kids','manual')))");
        $ciCombo->execute([$dateFrom, $dateTo]);
        $cc = $ciCombo->fetch();
        fputcsv($out, ['Tổng cộng', $ca['ci'], $ca['ppl']]);
        fputcsv($out, ['Khách sở hữu gói (combo)', $cc['ci'], $cc['ppl']]);
        fputcsv($out, ['Khách vãng lai (lẻ)', (int)$ca['ci'] - (int)$cc['ci'], (int)$ca['ppl'] - (int)$cc['ppl']]);
        fputcsv($out, []);

        // Sheet 4: Chi tiết check-in
        fputcsv($out, ['=== CHI TIẾT CHECK-IN ===']);
        fputcsv($out, ['Thời gian', 'Họ tên', 'SĐT', 'Loại khách', 'Số người', 'Trước', 'Sau', 'Ghi chú']);
        // Lấy danh sách phone sở hữu combo
        $comboPhones = [];
        $cpStmt = $db->query("SELECT DISTINCT phone FROM orders WHERE payment_status='Paid' AND pkg_type NOT IN ('single','kids') UNION SELECT DISTINCT phone FROM session_packages WHERE pkg NOT IN ('single','kids','manual')");
        foreach ($cpStmt as $r) { $comboPhones[$r['phone']] = true; }
        $ciList = $db->prepare("SELECT c.*, cu.name FROM checkins c LEFT JOIN customers cu ON c.phone = cu.phone WHERE DATE(c.checked_in_at) BETWEEN ? AND ? ORDER BY c.checked_in_at DESC");
        $ciList->execute([$dateFrom, $dateTo]);
        foreach ($ciList as $ci) {
            $type = isset($comboPhones[$ci['phone']]) ? 'Sở hữu gói' : 'Vãng lai';
            fputcsv($out, [$ci['checked_in_at'], $ci['name'] ?? '', $ci['phone'], $type, $ci['people_count'] ?? 1, $ci['sessions_before'], $ci['sessions_after'], $ci['note'] ?? '']);
        }
        fputcsv($out, []);

        // Sheet 5: Hội viên dưới 5 buổi (chỉ combo)
        fputcsv($out, ['=== HỘI VIÊN SỞ HỮU GÓI CÒN DƯỚI 5 BUỔI ===']);
        fputcsv($out, ['Họ tên', 'SĐT', 'Buổi còn lại', 'Tổng buổi', 'Hết hạn']);
        $lowM = $db->query("SELECT phone, name, sessions, max_sessions, expires_at FROM customers WHERE sessions > 0 AND sessions <= 5 AND (phone IN (SELECT DISTINCT phone FROM orders WHERE payment_status='Paid' AND pkg_type NOT IN ('single','kids')) OR phone IN (SELECT DISTINCT phone FROM session_packages WHERE pkg NOT IN ('single','kids','manual'))) ORDER BY sessions ASC");
        foreach ($lowM as $m) { fputcsv($out, [$m['name'], $m['phone'], $m['sessions'], $m['max_sessions'], $m['expires_at'] ?? '']); }

        fclose($out);
        exit;

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
