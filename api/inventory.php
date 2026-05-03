<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

$action = $_GET['action'] ?? '';

// Auto-create tables if not exist
function ensureInventoryTables($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        unit VARCHAR(50) DEFAULT 'cái',
        cost_price DECIMAL(12,0) DEFAULT 0,
        sell_price DECIMAL(12,0) DEFAULT 0,
        stock INT DEFAULT 0,
        created_at DATETIME DEFAULT NOW()
    ) CHARACTER SET utf8mb4");

    $db->exec("CREATE TABLE IF NOT EXISTS inventory_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        product_name VARCHAR(200),
        type ENUM('in','out') NOT NULL,
        quantity INT NOT NULL,
        note VARCHAR(255),
        created_at DATETIME DEFAULT NOW()
    ) CHARACTER SET utf8mb4");

    $db->exec("CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        total_amount DECIMAL(12,0) DEFAULT 0,
        total_cost DECIMAL(12,0) DEFAULT 0,
        note VARCHAR(255),
        created_at DATETIME DEFAULT NOW()
    ) CHARACTER SET utf8mb4");

    $db->exec("CREATE TABLE IF NOT EXISTS sale_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT NOT NULL,
        product_id INT,
        product_name VARCHAR(200),
        quantity INT NOT NULL,
        sell_price DECIMAL(12,0),
        cost_price DECIMAL(12,0)
    ) CHARACTER SET utf8mb4");
}

// All endpoints require admin token
$input = getJsonInput();
$token = $_GET['admin_token'] ?? ($input['admin_token'] ?? '');
if ($token !== md5(ADMIN_PASSWORD)) jsonResponse(['error' => 'Unauthorized'], 401);

$db = getDB();
ensureInventoryTables($db);

switch ($action) {

    // ---- PRODUCTS ----

    case 'list_products':
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $stmt = $db->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY name ASC LIMIT 20");
            $stmt->execute(['%' . $q . '%']);
        } else {
            $stmt = $db->query("SELECT * FROM products ORDER BY name ASC");
        }
        jsonResponse(['data' => $stmt->fetchAll()]);
        break;

    case 'create_product':
        $name  = trim($input['name'] ?? '');
        $unit  = trim($input['unit'] ?? 'cái');
        $cost  = (int)($input['cost_price'] ?? 0);
        $sell  = (int)($input['sell_price'] ?? 0);
        $stock = (int)($input['stock'] ?? 0);
        if (!$name) jsonResponse(['error' => 'Thiếu tên sản phẩm'], 400);
        $db->prepare("INSERT INTO products (name, unit, cost_price, sell_price, stock) VALUES (?,?,?,?,?)")
           ->execute([$name, $unit, $cost, $sell, $stock]);
        $id = $db->lastInsertId();
        if ($stock > 0) {
            $db->prepare("INSERT INTO inventory_logs (product_id, product_name, type, quantity, note) VALUES (?,?,?,?,?)")
               ->execute([$id, $name, 'in', $stock, 'Tồn kho ban đầu']);
        }
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['success' => true, 'data' => $stmt->fetch()]);
        break;

    case 'update_product':
        $id   = (int)($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $unit = trim($input['unit'] ?? 'cái');
        $cost = (int)($input['cost_price'] ?? 0);
        $sell = (int)($input['sell_price'] ?? 0);
        if (!$id || !$name) jsonResponse(['error' => 'Thiếu thông tin'], 400);
        $db->prepare("UPDATE products SET name=?, unit=?, cost_price=?, sell_price=? WHERE id=?")
           ->execute([$name, $unit, $cost, $sell, $id]);
        jsonResponse(['success' => true]);
        break;

    case 'delete_product':
        $id = (int)($input['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Thiếu id'], 400);
        $db->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    // ---- INVENTORY (nhập kho) ----

    case 'stock_in':
        $product_id = (int)($input['product_id'] ?? 0);
        $quantity   = (int)($input['quantity'] ?? 0);
        $note       = trim($input['note'] ?? '');
        if (!$product_id || $quantity <= 0) jsonResponse(['error' => 'Thiếu thông tin'], 400);
        $stmt = $db->prepare("SELECT name FROM products WHERE id=?");
        $stmt->execute([$product_id]);
        $p = $stmt->fetch();
        if (!$p) jsonResponse(['error' => 'Sản phẩm không tồn tại'], 404);
        $db->prepare("UPDATE products SET stock = stock + ? WHERE id=?")->execute([$quantity, $product_id]);
        $db->prepare("INSERT INTO inventory_logs (product_id, product_name, type, quantity, note) VALUES (?,?,?,?,?)")
           ->execute([$product_id, $p['name'], 'in', $quantity, $note ?: 'Nhập kho']);
        $stmt2 = $db->prepare("SELECT stock FROM products WHERE id=?");
        $stmt2->execute([$product_id]);
        jsonResponse(['success' => true, 'stock' => $stmt2->fetch()['stock']]);
        break;

    case 'inventory_logs':
        $product_id = (int)($_GET['product_id'] ?? 0);
        if ($product_id) {
            $stmt = $db->prepare("SELECT * FROM inventory_logs WHERE product_id=? ORDER BY id DESC LIMIT 50");
            $stmt->execute([$product_id]);
        } else {
            $stmt = $db->query("SELECT * FROM inventory_logs ORDER BY id DESC LIMIT 100");
        }
        jsonResponse(['data' => $stmt->fetchAll()]);
        break;

    // ---- SALES (bán hàng) ----

    case 'create_sale':
        $items = $input['items'] ?? [];
        $note  = trim($input['note'] ?? '');
        if (empty($items)) jsonResponse(['error' => 'Giỏ hàng trống'], 400);

        $total_amount = 0;
        $total_cost   = 0;
        $rows = [];
        foreach ($items as $it) {
            $pid = (int)($it['product_id'] ?? 0);
            $qty = (int)($it['quantity'] ?? 0);
            if (!$pid || $qty <= 0) continue;
            $stmt = $db->prepare("SELECT * FROM products WHERE id=?");
            $stmt->execute([$pid]);
            $p = $stmt->fetch();
            if (!$p) jsonResponse(['error' => "Sản phẩm #$pid không tồn tại"], 404);
            if ($p['stock'] < $qty) jsonResponse(['error' => "\"" . $p['name'] . "\" chỉ còn " . $p['stock'] . " " . $p['unit']], 400);
            $total_amount += $p['sell_price'] * $qty;
            $total_cost   += $p['cost_price'] * $qty;
            $rows[] = ['product' => $p, 'qty' => $qty];
        }
        if (empty($rows)) jsonResponse(['error' => 'Không có sản phẩm hợp lệ'], 400);

        $db->beginTransaction();
        try {
            $db->prepare("INSERT INTO sales (total_amount, total_cost, note) VALUES (?,?,?)")
               ->execute([$total_amount, $total_cost, $note]);
            $sale_id = $db->lastInsertId();
            foreach ($rows as $r) {
                $p = $r['product'];
                $q = $r['qty'];
                $db->prepare("INSERT INTO sale_items (sale_id, product_id, product_name, quantity, sell_price, cost_price) VALUES (?,?,?,?,?,?)")
                   ->execute([$sale_id, $p['id'], $p['name'], $q, $p['sell_price'], $p['cost_price']]);
                $db->prepare("UPDATE products SET stock = stock - ? WHERE id=?")->execute([$q, $p['id']]);
                $db->prepare("INSERT INTO inventory_logs (product_id, product_name, type, quantity, note) VALUES (?,?,?,?,?)")
                   ->execute([$p['id'], $p['name'], 'out', $q, "Bán hàng #$sale_id"]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => 'Lỗi lưu đơn hàng: ' . $e->getMessage()], 500);
        }
        jsonResponse(['success' => true, 'sale_id' => $sale_id, 'total_amount' => $total_amount]);
        break;

    case 'list_sales':
        $date = $_GET['date'] ?? '';
        if ($date) {
            $stmt = $db->prepare("SELECT s.*, GROUP_CONCAT(CONCAT(si.product_name,'×',si.quantity) SEPARATOR ', ') AS items_summary
                FROM sales s LEFT JOIN sale_items si ON si.sale_id = s.id
                WHERE DATE(s.created_at) = ? GROUP BY s.id ORDER BY s.id DESC");
            $stmt->execute([$date]);
        } else {
            $stmt = $db->query("SELECT s.*, GROUP_CONCAT(CONCAT(si.product_name,'×',si.quantity) SEPARATOR ', ') AS items_summary
                FROM sales s LEFT JOIN sale_items si ON si.sale_id = s.id
                GROUP BY s.id ORDER BY s.id DESC LIMIT 100");
        }
        jsonResponse(['data' => $stmt->fetchAll()]);
        break;

    case 'sale_detail':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Thiếu id'], 400);
        $stmt = $db->prepare("SELECT * FROM sales WHERE id=?");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();
        $stmt2 = $db->prepare("SELECT * FROM sale_items WHERE sale_id=?");
        $stmt2->execute([$id]);
        jsonResponse(['sale' => $sale, 'items' => $stmt2->fetchAll()]);
        break;

    case 'delete_sale':
        $id = (int)($input['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Thiếu id'], 400);
        $stmt = $db->prepare("SELECT * FROM sale_items WHERE sale_id=?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();
        $db->beginTransaction();
        try {
            foreach ($items as $it) {
                $db->prepare("UPDATE products SET stock = stock + ? WHERE id=?")->execute([$it['quantity'], $it['product_id']]);
                $db->prepare("INSERT INTO inventory_logs (product_id, product_name, type, quantity, note) VALUES (?,?,?,?,?)")
                   ->execute([$it['product_id'], $it['product_name'], 'in', $it['quantity'], "Huỷ đơn bán #$id"]);
            }
            $db->prepare("DELETE FROM sale_items WHERE sale_id=?")->execute([$id]);
            $db->prepare("DELETE FROM sales WHERE id=?")->execute([$id]);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
        jsonResponse(['success' => true]);
        break;

    // ---- REPORT ----

    case 'sales_report':
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $stmt = $db->prepare("SELECT
            COUNT(*) AS total_orders,
            SUM(total_amount) AS revenue,
            SUM(total_cost) AS cost,
            SUM(total_amount - total_cost) AS profit
            FROM sales WHERE DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$from, $to]);
        $summary = $stmt->fetch();

        $stmt2 = $db->prepare("SELECT si.product_name, si.product_id,
            SUM(si.quantity) AS total_qty,
            SUM(si.quantity * si.sell_price) AS revenue
            FROM sale_items si
            JOIN sales s ON s.id = si.sale_id
            WHERE DATE(s.created_at) BETWEEN ? AND ?
            GROUP BY si.product_id, si.product_name
            ORDER BY total_qty DESC LIMIT 10");
        $stmt2->execute([$from, $to]);
        $top_products = $stmt2->fetchAll();

        $stmt3 = $db->prepare("SELECT DATE(created_at) AS date,
            SUM(total_amount) AS revenue,
            SUM(total_amount - total_cost) AS profit,
            COUNT(*) AS orders
            FROM sales WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at) ORDER BY date DESC");
        $stmt3->execute([$from, $to]);
        $daily = $stmt3->fetchAll();

        jsonResponse(['summary' => $summary, 'top_products' => $top_products, 'daily' => $daily]);
        break;

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
