<?php
// Auto/Manual Cloud Backup Runner
// - If called from CLI (php auto_backup.php), runs as 'scheduled'
// - If called via web by a logged-in super_admin, runs as 'manual'

session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/backup_helper.php';
require_once __DIR__ . '/../includes/audit_helper.php';

header('Content-Type: application/json');

$isCli = (php_sapi_name() === 'cli');
$trigger = 'manual';

if ($isCli) {
    // CLI invocation considered scheduled
    $trigger = 'scheduled';
} else {
    // Web invocation: require super_admin
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
}

// Load backup config
$config = require __DIR__ . '/../backup_config.php';

// Ensure backups table exists for tracking
ensure_backups_table($conn);

$result = perform_backup($conn, $config, $trigger);

// Audit log outcome
if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
    $msg = ($trigger === 'scheduled' ? 'Scheduled' : 'Manual') . ' cloud backup ' . ($result['success'] ? 'completed' : 'failed') . ': ' . ($result['message'] ?? '');
    logUserActivity($result['success'] ? 'BACKUP_SUCCESS' : 'BACKUP_FAILED', 'System', $msg, 'backups', null);
}

// Compute last and next backup timestamps
$lastBackup = null; $nextBackup = null;
try {
    $res = $conn->query("SELECT MAX(created_at) AS last_backup FROM backups");
    if ($res && ($row = $res->fetch_assoc())) {
        $lastBackup = $row['last_backup'];
    }
} catch (Exception $e) {}

if ($lastBackup) {
    $nextBackup = date('Y-m-d H:i:s', strtotime($lastBackup . ' +30 days'));
} else {
    $nextBackup = date('Y-m-d H:i:s', strtotime('+30 days'));
}

echo json_encode([
    'success' => $result['success'] ?? false,
    'message' => $result['message'] ?? 'Unknown',
    'filename' => $result['filename'] ?? null,
    'last_backup' => $lastBackup,
    'next_backup' => $nextBackup,
]);
