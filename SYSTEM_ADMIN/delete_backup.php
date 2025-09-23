<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/backup_helper.php';
require_once __DIR__ . '/../includes/audit_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  http_response_code(403);
  exit('Forbidden');
}

if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'])) {
  http_response_code(400);
  exit('Invalid CSRF token');
}

ensure_backups_table($conn);

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  exit('Invalid ID');
}

$stmt = $conn->prepare('SELECT path, filename FROM backups WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$rec = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$rec) {
  header('Location: backups.php');
  exit();
}

$config = require __DIR__ . '/../backup_config.php';
$backupDir = $config['backup_dir'] ?? (__DIR__ . '/../generated_backups');
$realFile = realpath($rec['path']);
$realDir = realpath($backupDir);

if ($realFile && $realDir && strpos($realFile, $realDir) === 0 && is_file($realFile)) {
  @unlink($realFile);
}

$conn->query('DELETE FROM backups WHERE id=' . (int)$id);

if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
  logUserActivity('BACKUP_DELETE', 'System', 'Deleted backup: ' . ($rec['filename'] ?? ''), 'backups', (int)$id);
}

header('Location: backups.php');
exit();
