<?php
// Monthly backup script (to be triggered by OS scheduler)
// Usage: php cron/monthly_backup.php

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/backup_helper.php';
require_once __DIR__ . '/../includes/audit_helper.php';

$config = require __DIR__ . '/../backup_config.php';

// Ensure table exists and get last backup time
ensure_backups_table($conn);
$last = get_last_backup_time($conn); // Y-m-d H:i:s or null

$shouldRun = false;
if (!$last) {
    $shouldRun = true;
} else {
    $lastTs = strtotime($last);
    // Run if 30 days passed since last backup
    $shouldRun = ($lastTs === false) ? true : (time() - $lastTs >= 30 * 24 * 60 * 60);
}

if (!$shouldRun) {
    echo "No backup needed. Last backup: {$last}\n";
    exit(0);
}

$result = perform_backup($conn, $config, 'scheduled');

// Optionally log to audit if available
if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
    $msg = $result['success'] ? 'Scheduled backup completed' : ('Scheduled backup failed: ' . ($result['message'] ?? 'unknown'));
    logUserActivity($result['success'] ? 'BACKUP_SUCCESS' : 'BACKUP_FAILED', 'System', $msg, 'backups', null);
}

echo ($result['success'] ? 'OK: ' : 'ERROR: ') . ($result['message'] ?? '') . "\n";

// Retention policy: delete backups older than N days if configured
$retention = (int)($config['retention_days'] ?? 0);
if ($retention > 0) {
    $cutoff = date('Y-m-d H:i:s', time() - $retention * 24 * 60 * 60);
    $backupDir = $config['backup_dir'] ?? (__DIR__ . '/../generated_backups');
    $stmt = $conn->prepare("SELECT id, path, filename FROM backups WHERE created_at < ?");
    $stmt->bind_param('s', $cutoff);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $path = $row['path'];
            // Safety: only delete inside configured backup directory
            if (strpos(realpath($path) ?: '', realpath($backupDir)) === 0 && file_exists($path)) {
                @unlink($path);
            }
            $conn->query('DELETE FROM backups WHERE id=' . (int)$row['id']);
            if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
                logUserActivity('BACKUP_PURGE', 'System', 'Purged old backup: ' . ($row['filename'] ?? ''), 'backups', (int)$row['id']);
            }
        }
    }
    $stmt->close();
}

// Optional: Alerts placeholder (email/telegram). Wire here if enable_alerts is true.
if (!empty($config['enable_alerts'])) {
    // Implement your notifier here (PHPMailer, etc.)
    // Example: send email to recipients with $result summary
}
