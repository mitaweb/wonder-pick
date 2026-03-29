<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

$action = $_GET['action'] ?? '';

// Tính tiền + buổi theo gói
function calcOrder(string $pkg, int $kids): array {
    $sessions = 0; $amount = 0; $label = '';
    switch ($pkg) {
        case 'pkg_10':
            $sessions = 13; $amount = PRICE_PKG_10;
            $label = 'Gói 10 tặng 3 (13 buổi)'; break;
        case 'pkg_30':
            $sessions = 40; $amount = PRICE_PKG_30;
            $label = 'Gói 30 tặng 10 (40 buổi)'; break;
        case 'single':
            $s = getCurrentSinglePrice();
            $sessions = 1; $amount = $s['price'];
            $label = 'Lẻ 1 buổi (' . $s['slot'] . ')'; break;
        case 'kids':
            // Mua riêng khu vui chơi trẻ em
            $sessions = 0; $amount = 0;
            $label = 'Khu vui chơi trẻ em'; break;
    }
    $kidsAmount = $kids * PRICE_KIDS;
    $amount += $kidsAmount;
    if ($kids > 0 && $pkg !== 'kids') $label .= ' + ' . $kids . ' trẻ em (khu vui chơi)';
    elseif ($pkg === 'kids') $label = $kids . ' trẻ em — Khu vui chơi';
    return ['sessions' => $sessions, 'amount' => $amount, 'label' => $label, 'kids_amount' => $kidsAmount];
}

switch ($action) {

    // POST: Khách tạo đơn hàng → trả về QR VietQR
    case 'create':
        $input = getJsonInput();
        $phone = sanitizePhone($input['phone'] ?? '');
        $name  = trim($input['name'] ?? '');
        $pkg   = $input['pkg_type'] ?? 'pkg_10';
        $kids  = max(0, (int)($input['kids_count'] ?? 0));

        if (strlen($phone) < 9) jsonResponse(['error' => 'Số điện thoại không hợp lệ'], 400);

        $calc = calcOrder($pkg, $kids);
        if ($calc['amount'] <= 0) jsonResponse(['error' => 'Không có gì để thanh toán'], 400);

        $db = getDB();
        if (!$name) {
            $r = $db->prepare("SELECT name FROM customers WHERE phone = ?");
            $r->execute([$phone]); $row = $r->fetch();
            $name = $row ? $row['name'] : 'Khách';
        }

        // Tạo order_code tạm, update sau khi có ID
        $tmp = ORDER_PREFIX . time() . rand(10,99);
        $db->prepare("INSERT INTO orders (phone, customer_name, pkg_type, sessions_to_add, amount, kids_count, order_code, payment_status) VALUES (?,?,?,?,?,?,?,'Unpaid')")
           ->execute([$phone, $name, $pkg, $calc['sessions'], $calc['amount'], $kids, $tmp]);
        $id = $db->lastInsertId();
        $code = ORDER_PREFIX . str_pad($id, 4, '0', STR_PAD_LEFT); // WP0001
        $db->prepare("UPDATE orders SET order_code=? WHERE id=?")->execute([$code, $id]);

        // VietQR link API (đọc từ DB, fallback config)
        $bank = getBankConfig();
        $qrUrl = 'https://img.vietqr.io/image/'
            . rawurlencode($bank['bank_id']) . '-'
            . rawurlencode($bank['bank_account']) . '-compact2.png'
            . '?amount=' . intval($calc['amount'])
            . '&addInfo=' . rawurlencode($code)
            . '&accountName=' . rawurlencode($bank['bank_owner']);

        jsonResponse([
            'success'         => true,
            'order_id'        => $id,
            'order_code'      => $code,
            'amount'          => $calc['amount'],
            'pkg_label'       => $calc['label'],
            'sessions_to_add' => $calc['sessions'],
            'kids_count'      => $kids,
            'qr_url'          => $qrUrl,
            'bank_id'         => $bank['bank_id'],
            'bank_account'    => $bank['bank_account'],
            'bank_owner'      => $bank['bank_owner'],
            'bank_name'       => $bank['bank_name'],
        ]);
        break;

    // GET: Kiểm tra trạng thái đơn (polling frontend)
    case 'status':
        $id = (int)($_GET['order_id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Thiếu order_id'], 400);
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id=?");
        $stmt->execute([$id]);
        $o = $stmt->fetch();
        if (!$o) jsonResponse(['error' => 'Không tìm thấy đơn'], 404);
        jsonResponse(['success' => true, 'data' => $o]);
        break;

    // GET: Lấy giá hiện tại (cho UI)
    case 'pricing':
        $s = getCurrentSinglePrice();
        jsonResponse([
            'single' => $s,
            'pkg_10' => ['sessions'=>13,'price'=>PRICE_PKG_10,'label'=>'Gói 10 tặng 3'],
            'pkg_30' => ['sessions'=>40,'price'=>PRICE_PKG_30,'label'=>'Gói 30 tặng 10'],
            'kids'   => ['price'=>PRICE_KIDS],
            'slots'  => [
                ['time'=>'8h-11h',  'price'=>PRICE_SOCIAL_MORNING,'label'=>'Sáng'],
                ['time'=>'11h-16h', 'price'=>PRICE_SOCIAL_NOON,   'label'=>'Trưa'],
                ['time'=>'16h-22h', 'price'=>PRICE_SOCIAL_EVENING,'label'=>'Chiều/Tối'],
            ],
        ]);
        break;

    // GET (admin): Lấy danh sách đơn hàng
    case 'list':
        if (($_GET['admin_token']??'') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error'=>'Unauthorized'],401);
        $db = getDB();
        $status = $_GET['status'] ?? ''; // '', 'Unpaid', 'Paid', 'Cancelled'
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE payment_status=? ORDER BY created_at DESC LIMIT 100");
            $stmt->execute([$status]);
        } else {
            $stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 100");
        }
        $orders = $stmt->fetchAll();
        // Đếm chờ duyệt
        $pending = $db->query("SELECT COUNT(*) FROM orders WHERE payment_status='Unpaid'")->fetchColumn();
        jsonResponse(['success'=>true,'orders'=>$orders,'pending'=>(int)$pending]);
        break;

    // POST (admin): Duyệt đơn hàng → cộng buổi
    case 'approve':
        $input = getJsonInput();
        if (($input['admin_token']??'') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error'=>'Unauthorized'],401);
        $id   = (int)($input['order_id'] ?? 0);
        $note = trim($input['note'] ?? '');
        if (!$id) jsonResponse(['error'=>'Thiếu order_id'],400);

        $db = getDB();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT * FROM orders WHERE id=? FOR UPDATE");
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            if (!$order)                         { $db->rollBack(); jsonResponse(['error'=>'Không tìm đơn'],404); }
            if ($order['payment_status']==='Paid'){ $db->rollBack(); jsonResponse(['error'=>'Đơn đã được duyệt rồi'],400); }

            // Cập nhật đơn → Paid
            $db->prepare("UPDATE orders SET payment_status='Paid', paid_at=NOW() WHERE id=?")->execute([$id]);

            $phone    = $order['phone'];
            $sessions = (int)$order['sessions_to_add'];
            $pkg      = $order['pkg_type'];

            // Cộng buổi nếu có (không cộng với kids_only)
            if ($sessions > 0) {
                $cust = $db->prepare("SELECT * FROM customers WHERE phone=?");
                $cust->execute([$phone]); $customer = $cust->fetch();

                if ($customer) {
                    $newExp = in_array($pkg,['pkg_10','pkg_30']) ? calcExpiry($pkg, $customer['expires_at']) : $customer['expires_at'];
                    $db->prepare("UPDATE customers SET sessions=sessions+?, max_sessions=max_sessions+?, expires_at=?, updated_at=NOW() WHERE phone=?")
                       ->execute([$sessions, $sessions, $newExp, $phone]);
                } else {
                    // Tự tạo tài khoản nếu chưa có
                    $newExp = in_array($pkg,['pkg_10','pkg_30']) ? calcExpiry($pkg) : null;
                    $db->prepare("INSERT INTO customers (phone,name,sessions,max_sessions,pkg,expires_at) VALUES (?,?,?,?,?,?)")
                       ->execute([$phone, $order['customer_name'], $sessions, $sessions, $pkg, $newExp]);
                }

                $db->prepare("INSERT INTO session_packages (phone,pkg,sessions_added,added_by,note) VALUES (?,?,?,'admin',?)")
                   ->execute([$phone, $pkg, $sessions, 'Duyệt đơn #'.$order['order_code'].($note?' - '.$note:'')]);
            }

            $db->commit();

            // Lấy lại thông tin customer sau khi cập nhật
            $cust2 = $db->prepare("SELECT sessions, expires_at FROM customers WHERE phone=?");
            $cust2->execute([$phone]); $updated = $cust2->fetch();

            jsonResponse(['success'=>true,'message'=>'Đã duyệt đơn #'.$order['order_code'],'sessions_added'=>$sessions,'customer'=>$updated]);
        } catch (\Exception $e) {
            $db->rollBack();
            jsonResponse(['error'=>$e->getMessage()],500);
        }
        break;

    // POST (admin): Huỷ đơn hàng
    case 'cancel':
        $input = getJsonInput();
        if (($input['admin_token']??'') !== \md5(ADMIN_PASSWORD)) jsonResponse(['error'=>'Unauthorized'],401);
        $id = (int)($input['order_id'] ?? 0);
        if (!$id) jsonResponse(['error'=>'Thiếu order_id'],400);
        $db = getDB();
        $db->prepare("UPDATE orders SET payment_status='Cancelled' WHERE id=? AND payment_status='Unpaid'")->execute([$id]);
        jsonResponse(['success'=>true]);
        break;

    default:
        jsonResponse(['error'=>'Action không hợp lệ'],400);
}
