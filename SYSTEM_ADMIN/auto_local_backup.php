<?php
// Auto/Manual Local Backup (no external tools, pure PHP)
// - If called with ?check=1, it will only run when last backup is >= 30 days ago
// - Otherwise, runs immediately (manual trigger)

session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/simple_backup_helper.php';
require_once __DIR__ . '/../includes/backup_helper.php'; // for ensure_backups_table
require_once __DIR__ . '/../includes/audit_helper.php';
require_once __DIR__ . '/../includes/google_drive_helper.php';

header('Content-Type: application/json');

$isCheck = isset($_GET['check']) && $_GET['check'] == '1';
$trigger = $isCheck ? 'scheduled' : 'manual';

// For manual trigger via web, require super_admin
if (!$isCheck) {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
}

// Ensure backups table exists
ensure_backups_table($conn);

// Determine last backup
$lastBackup = null;
try {
    $res = $conn->query("SELECT MAX(created_at) AS last_backup FROM backups");
    if ($res && ($row = $res->fetch_assoc())) {
        $lastBackup = $row['last_backup'];
    }
} catch (Exception $e) {}

// If check mode and not due, return without running
if ($isCheck && $lastBackup) {
    $lastTs = strtotime($lastBackup);
    if ($lastTs !== false && (time() - $lastTs) < 30*24*60*60) {
        echo json_encode([
            'success' => true,
            'ran' => false,
            'message' => 'Not due yet',
            'last_backup' => $lastBackup,
            'next_backup' => date('Y-m-d H:i:s', $lastTs + 30*24*60*60),
        ]);
        exit;
    }
}

// Prepare backups directory (project_root/backups)
$backupDir = realpath(__DIR__ . '/..');
$backupDir = $backupDir ? ($backupDir . DIRECTORY_SEPARATOR . 'backups') : (__DIR__ . '/../backups');
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0775, true);
}

// Generate dump
try {
    $sql = generate_sql_dump($conn);
} catch (Throwable $e) {
    $msg = 'Dump generation failed: ' . $e->getMessage();
    if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
        logUserActivity('BACKUP_FAILED', 'System', $msg, 'backups', null);
    }
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// Write file
$resDb = $conn->query('SELECT DATABASE() AS db');
$dbRow = $resDb ? $resDb->fetch_assoc() : null;
$dbName = $dbRow['db'] ?? 'database';
$filename = $dbName . '_auto_backup_' . date('Ymd_His') . '.sql';
$filePath = rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

$ok = @file_put_contents($filePath, $sql);
$status = ($ok !== false) ? 'success' : 'failed';
$size = ($ok !== false) ? filesize($filePath) : null;

// Record in DB
$stmt = $conn->prepare("INSERT INTO backups (filename, path, size_bytes, storage, status, triggered_by) VALUES (?,?,?,?,?,?)");
$storage = 'local';
$stmt->bind_param('ssisss', $filename, $filePath, $size, $storage, $status, $trigger);
$stmt->execute();
$insertId = $conn->insert_id;
$stmt->close();

// Attempt Google Drive upload if configured and connected
$cloudSync = null;
if ($status === 'success') {
    gdrive_ensure_tables($conn);
    if (gdrive_is_configured($conn) && gdrive_is_connected($conn)) {
        $settings = gdrive_get_settings($conn);
        $tokens = gdrive_get_tokens($conn);
        // Refresh access token if absent or expired
        $needRefresh = empty($tokens['access_token']) || empty($tokens['expires_at']) || ((int)$tokens['expires_at'] < time()+60);
        if ($needRefresh && !empty($tokens['refresh_token'])) {
            $newTok = gdrive_refresh_access($settings, $tokens['refresh_token']);
            if ($newTok && !empty($newTok['access_token'])) {
                $tokens['access_token'] = $newTok['access_token'];
                $tokens['expires_at'] = time() + (int)($newTok['expires_in'] ?? 3600) - 60;
                gdrive_save_tokens($conn, [
                    'access_token' => $tokens['access_token'],
                    'expires_at' => $tokens['expires_at'],
                ]);
            }
        }
        if (!empty($tokens['access_token'])) {
            $okUp = gdrive_upload_file($tokens['access_token'], $filePath, $filename, $settings['folder_id'] ?? null);
            $cloudSync = $okUp ? 'success' : 'failed';
            if ($okUp) {
                // Update storage to both
                if ($insertId) {
                    $conn->query('UPDATE backups SET storage=\'both\' WHERE id='.(int)$insertId);
                }
                if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
                    logUserActivity('BACKUP_SYNC_SUCCESS', 'System', 'Uploaded backup to Google Drive: ' . $filename, 'backups', $insertId ?: null);
                }
            } else {
                if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
                    logUserActivity('BACKUP_SYNC_FAILED', 'System', 'Failed to upload backup to Google Drive: ' . $filename, 'backups', $insertId ?: null);
                }
            }
        }
    }
}

$lastBackupNew = null; $nextBackup = null;
try {
    $res2 = $conn->query("SELECT MAX(created_at) AS last_backup FROM backups");
    if ($res2 && ($row2 = $res2->fetch_assoc())) { $lastBackupNew = $row2['last_backup']; }
} catch (Exception $e) {}

if ($lastBackupNew) {
    $nextBackup = date('Y-m-d H:i:s', strtotime($lastBackupNew . ' +30 days'));
}

// Audit
if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
    $msg = ($status === 'success' ? 'Local monthly backup created: ' : 'Local backup failed: ') . $filename;
    logUserActivity($status === 'success' ? 'BACKUP_SUCCESS' : 'BACKUP_FAILED', 'System', $msg, 'backups', null);
}

echo json_encode([
    'success' => ($status === 'success'),
    'ran' => true,
    'message' => $status === 'success' ? 'Backup completed successfully' : 'Backup failed to write file',
    'filename' => $filename,
    'last_backup' => $lastBackupNew,
    'next_backup' => $nextBackup,
    'cloud_sync' => $cloudSync,
]);
