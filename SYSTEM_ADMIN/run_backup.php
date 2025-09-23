<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/backup_helper.php';
require_once __DIR__ . '/../includes/audit_helper.php';

header('Content-Type: application/json');

// Require authentication and super_admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Load backup config
$config = require __DIR__ . '/../backup_config.php';

$result = perform_backup($conn, $config, 'manual');

// Log to audit logs if available
if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
    $msg = ($result['success'] ? 'Manual backup completed: ' : 'Manual backup failed: ') . ($result['message'] ?? '');
    logUserActivity($result['success'] ? 'BACKUP_SUCCESS' : 'BACKUP_FAILED', 'System', $msg, 'backups', null);
}

echo json_encode($result);
