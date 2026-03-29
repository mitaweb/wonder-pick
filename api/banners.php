<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

$action = $_GET['action'] ?? '';

// Auth helper
function requireAdmin(): void {
    $token = $_GET['admin_token'] ?? ($_POST['admin_token'] ?? '');
    if (!$token || $token !== md5(ADMIN_PASSWORD)) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

$uploadDir = __DIR__ . '/../uploads/banners/';
$uploadUrl = rtrim(APP_URL, '/') . '/uploads/banners/';

switch ($action) {

    case 'list':
        requireAdmin();
        $rows = getDB()->query("SELECT id, image_path, link_url, created_at FROM banners ORDER BY created_at DESC")->fetchAll();
        foreach ($rows as &$r) {
            $r['image_url'] = $uploadUrl . basename($r['image_path']);
        }
        jsonResponse(['banners' => $rows]);

    case 'create':
        requireAdmin();
        if (empty($_FILES['image'])) jsonResponse(['error' => 'Chưa có file ảnh'], 400);
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) jsonResponse(['error' => 'Upload lỗi: '.$file['error']], 400);
        if ($file['size'] > 5 * 1024 * 1024) jsonResponse(['error' => 'Ảnh quá 5MB'], 400);

        $mime = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowed)) jsonResponse(['error' => 'Chỉ chấp nhận JPG, PNG, WEBP, GIF'], 400);

        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = 'banner_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) jsonResponse(['error' => 'Không thể lưu file'], 500);

        $linkUrl = trim($_POST['link_url'] ?? '');
        if ($linkUrl && !filter_var($linkUrl, FILTER_VALIDATE_URL)) $linkUrl = '';

        $stmt = getDB()->prepare("INSERT INTO banners (image_path, link_url) VALUES (?, ?)");
        $stmt->execute([$filename, $linkUrl ?: null]);
        $id = (int)getDB()->lastInsertId();

        jsonResponse(['success' => true, 'id' => $id, 'image_url' => $uploadUrl . $filename]);

    case 'delete':
        requireAdmin();
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'ID không hợp lệ'], 400);

        $row = getDB()->prepare("SELECT image_path FROM banners WHERE id = ?");
        $row->execute([$id]);
        $banner = $row->fetch();
        if (!$banner) jsonResponse(['error' => 'Không tìm thấy banner'], 404);

        // Remove file
        $filePath = $uploadDir . basename($banner['image_path']);
        if (file_exists($filePath)) @unlink($filePath);

        getDB()->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
