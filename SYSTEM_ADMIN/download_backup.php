<?php
session_start();
require_once __DIR__ . '/../connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  http_response_code(403);
  exit('Forbidden');
}

// Ensure backups table exists
require_once __DIR__ . '/../includes/backup_helper.php';
ensure_backups_table($conn);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  exit('Invalid ID');
}

$stmt = $conn->prepare('SELECT filename, path FROM backups WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$rec = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$rec) {
  http_response_code(404);
  exit('Not found');
}

$config = require __DIR__ . '/../backup_config.php';
$backupDir = $config['backup_dir'] ?? (__DIR__ . '/../generated_backups');
$filePath = $rec['path'];

// Safety: Ensure the file is inside backup directory
$realFile = realpath($filePath);
$realDir = realpath($backupDir);
if (!$realFile || !$realDir || strpos($realFile, $realDir) !== 0 || !is_file($realFile)) {
  http_response_code(404);
  exit('File not accessible');
}

$filename = basename($rec['filename']);
header('Content-Description: File Transfer');
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($realFile));
readfile($realFile);
exit;
