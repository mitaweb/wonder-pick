<?php
/**
 * Cron job: Gửi email từ queue
 * Chạy mỗi 1 phút: * * * * * php /path/to/cron/send_emails.php
 */
require_once __DIR__ . '/../includes/config.php';

$db = getDB();

// Lấy tối đa 10 email pending mỗi lần chạy
$stmt = $db->prepare("SELECT * FROM email_queue WHERE status = 'pending' AND attempts < 3 ORDER BY id ASC LIMIT 10");
$stmt->execute();
$emails = $stmt->fetchAll();

if (empty($emails)) exit;

$sent = 0;
foreach ($emails as $e) {
    $db->prepare("UPDATE email_queue SET attempts = attempts + 1 WHERE id = ?")->execute([$e['id']]);
    $ok = sendMail($e['to_email'], $e['to_name'], $e['subject'], $e['body']);
    if ($ok) {
        $db->prepare("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?")->execute([$e['id']]);
        $sent++;
    } else {
        $db->prepare("UPDATE email_queue SET status = IF(attempts >= 3, 'failed', 'pending') WHERE id = ?")->execute([$e['id']]);
    }
}

// Xóa email đã gửi quá 7 ngày
$db->exec("DELETE FROM email_queue WHERE status = 'sent' AND sent_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");

echo date('Y-m-d H:i:s') . " — Sent {$sent}/" . count($emails) . " emails\n";
