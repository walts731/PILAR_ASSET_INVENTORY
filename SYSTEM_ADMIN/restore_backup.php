<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/audit_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  header('Location: simple_backup.php?error=' . urlencode('Forbidden'));
  exit();
}

if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'])) {
  header('Location: simple_backup.php?error=' . urlencode('Invalid CSRF token'));
  exit();
}

if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
  header('Location: simple_backup.php?error=' . urlencode('No file uploaded'));
  exit();
}

// Basic validation
$fname = $_FILES['sql_file']['name'];
$ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
if ($ext !== 'sql') {
  header('Location: simple_backup.php?error=' . urlencode('Invalid file type'));
  exit();
}

// Increase limits
@set_time_limit(0);
@ini_set('memory_limit', '1024M');

$tmp = $_FILES['sql_file']['tmp_name'];
$sqlContent = file_get_contents($tmp);
if ($sqlContent === false) {
  header('Location: simple_backup.php?error=' . urlencode('Failed to read uploaded file'));
  exit();
}

// Optional: Drop all tables first
if (!empty($_POST['drop_first'])) {
  $res = $conn->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
  while ($res && ($r = $res->fetch_array())) {
    $t = $r[0];
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    $conn->query("DROP TABLE IF EXISTS `" . $conn->real_escape_string($t) . "`");
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
  }
}

// Execute SQL statements
$delimiter = ";\n";
$statements = preg_split('/;\s*\n/', $sqlContent);

$conn->begin_transaction();
try {
  foreach ($statements as $stmt) {
    $s = trim($stmt);
    if ($s === '' || strpos($s, '--') === 0 || strpos($s, '/*') === 0) continue;
    $conn->query($s);
  }
  $conn->commit();
} catch (Throwable $e) {
  $conn->rollback();
  if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
    logUserActivity('RESTORE_FAILED', 'System', 'Restore failed: ' . $e->getMessage());
  }
  header('Location: simple_backup.php?error=' . urlencode('Restore failed: ' . $e->getMessage()));
  exit();
}

if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
  logUserActivity('RESTORE_SUCCESS', 'System', 'Database restored from uploaded SQL');
}

header('Location: simple_backup.php?restored=1');
exit();
