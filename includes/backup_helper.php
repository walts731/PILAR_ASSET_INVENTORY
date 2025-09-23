<?php
/**
 * Database Backup Helper
 * - Creates SQL dumps using mysqldump
 * - Optionally uploads to cloud via presigned URL or custom command
 * - Logs entry to `backups` table
 */

require_once __DIR__ . '/../connect.php';

/** Ensure backups table exists */
function ensure_backups_table(mysqli $conn): void {
    $sql = "CREATE TABLE IF NOT EXISTS `backups` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `filename` VARCHAR(255) NOT NULL,
        `path` TEXT NOT NULL,
        `size_bytes` BIGINT DEFAULT NULL,
        `storage` ENUM('local','cloud','both') DEFAULT 'local',
        `status` ENUM('success','failed') DEFAULT 'success',
        `triggered_by` ENUM('manual','scheduled') DEFAULT 'manual',
        `error_message` TEXT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($sql);
}

/** Get most recent backup timestamp */
function get_last_backup_time(mysqli $conn): ?string {
    $res = $conn->query("SELECT created_at FROM backups ORDER BY created_at DESC LIMIT 1");
    if ($res && ($row = $res->fetch_assoc())) {
        return $row['created_at'];
    }
    return null;
}

/** Perform backup and return result array */
function perform_backup(mysqli $conn, array $config, string $trigger = 'manual'): array {
    ensure_backups_table($conn);

    $dbName = $conn->query('SELECT DATABASE() AS db')->fetch_assoc()['db'] ?? null;
    if (!$dbName) {
        return ['success' => false, 'message' => 'Unable to determine database name.'];
    }

    // Load DB connection info from connect.php globals
    global $host, $db_user, $db_pass; // defined in connect.php

    $backupDir = $config['backup_dir'] ?? (__DIR__ . '/../generated_backups');
    if (!is_dir($backupDir)) {
        if (!mkdir($backupDir, 0775, true)) {
            return ['success' => false, 'message' => 'Failed to create backup directory: ' . $backupDir];
        }
    }

    $timestamp = date('Ymd_His');
    $filename = $dbName . '_backup_' . $timestamp . '.sql';
    $filePath = rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    $mysqldump = $config['mysqldump_path'] ?? 'mysqldump';

    // Build command safely (use --result-file to avoid shell redirection issues)
    $output = null; $ret = null; $cmd = '';
    if (stripos(PHP_OS, 'WIN') === 0) {
        // Windows: use double quotes; use --password="..." form
        $cmd = '"' . $mysqldump . '"'
            . ' -h ' . '"' . $host . '"'
            . ' -u ' . '"' . $db_user . '"';
        if ($db_pass !== '') {
            $cmd .= ' --password=' . '"' . $db_pass . '"';
        }
        $cmd .= ' --routines --events --triggers --single-transaction --quick --lock-tables=false'
             . ' --column-statistics=0 --set-gtid-purged=OFF'
             . ' ' . '"' . $dbName . '"'
             . ' --result-file=' . '"' . $filePath . '"'
             . ' 2>&1';
        $fullCmd = 'cmd /c ' . $cmd;
        exec($fullCmd, $output, $ret);
    } else {
        // Linux/macOS: quote with escapeshellarg
        $cmd = escapeshellarg($mysqldump)
            . ' -h ' . escapeshellarg($host)
            . ' -u ' . escapeshellarg($db_user);
        if ($db_pass !== '') {
            $cmd .= ' --password=' . escapeshellarg($db_pass);
        }
        $cmd .= ' --routines --events --triggers --single-transaction --quick --lock-tables=false'
             . ' --column-statistics=0 --set-gtid-purged=OFF'
             . ' ' . escapeshellarg($dbName)
             . ' --result-file=' . escapeshellarg($filePath) . ' 2>&1';
        // Use sh -c to process the pipeline redirection capture
        $fullCmd = '/bin/sh -c ' . escapeshellarg($cmd);
        exec($fullCmd, $output, $ret);
    }

    $status = ($ret === 0 && file_exists($filePath) && filesize($filePath) > 0) ? 'success' : 'failed';

    $storage = 'local';
    $errorMessage = null;

    if ($status === 'success') {
        // Optional cloud upload
        $strategy = $config['upload_strategy'] ?? 'none';
        if ($strategy === 's3_presigned' && is_callable($config['s3_presigned_url_provider'] ?? null)) {
            $url = call_user_func($config['s3_presigned_url_provider'], basename($filePath));
            if ($url) {
                $putOk = backup_put_file($url, $filePath);
                $storage = $putOk ? 'both' : 'local';
                if (!$putOk) { $errorMessage = 'Cloud upload failed (presigned PUT).'; }
            }
        } elseif ($strategy === 'custom_script' && !empty($config['custom_upload_command'])) {
            $cmdTemplate = str_replace('{file}', $filePath, $config['custom_upload_command']);
            $out2 = null; $ret2 = null;
            exec($cmdTemplate, $out2, $ret2);
            $storage = ($ret2 === 0) ? 'both' : 'local';
            if ($ret2 !== 0) { $errorMessage = 'Custom upload script failed.'; }
        }
    } else {
        $firstLines = is_array($output) ? implode("\n", array_slice($output, 0, 5)) : '';
        $errorMessage = 'mysqldump failed. Return code: ' . var_export($ret, true) . ($firstLines ? ('; Output: ' . $firstLines) : '');
    }

    // Record in DB
    $stmt = $conn->prepare("INSERT INTO backups (filename, path, size_bytes, storage, status, triggered_by, error_message) VALUES (?,?,?,?,?,?,?)");
    $size = file_exists($filePath) ? filesize($filePath) : null;
    $stmt->bind_param('ssissss', $filename, $filePath, $size, $storage, $status, $trigger, $errorMessage);
    $stmt->execute();
    $stmt->close();

    return [
        'success' => ($status === 'success'),
        'message' => $status === 'success' ? 'Backup completed successfully' : ($errorMessage ?? 'Backup failed'),
        'filename' => $filename,
        'path' => $filePath,
        'size_bytes' => $size,
        'storage' => $storage,
        'status' => $status,
    ];
}

/** PUT a file to a presigned URL */
function backup_put_file(string $url, string $filePath): bool {
    if (!file_exists($filePath)) return false;
    $ch = curl_init($url);
    $fp = fopen($filePath, 'rb');
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    return ($err === '' && $code >= 200 && $code < 300);
}
