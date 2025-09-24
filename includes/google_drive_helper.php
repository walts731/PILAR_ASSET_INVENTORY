<?php
// Google Drive OAuth and Upload Helper (no external libraries)
// Stores client settings and tokens in DB and uploads files via Drive v3 REST API

require_once __DIR__ . '/../connect.php';

function gdrive_ensure_tables(mysqli $conn): void {
    $conn->query("CREATE TABLE IF NOT EXISTS google_drive_settings (
        id TINYINT PRIMARY KEY DEFAULT 1,
        client_id TEXT NULL,
        client_secret TEXT NULL,
        redirect_uri TEXT NULL,
        folder_id VARCHAR(128) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS google_drive_tokens (
        id TINYINT PRIMARY KEY DEFAULT 1,
        refresh_token TEXT NULL,
        access_token TEXT NULL,
        expires_at INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ensure single-row settings exist
    $conn->query("INSERT IGNORE INTO google_drive_settings (id) VALUES (1)");
    $conn->query("INSERT IGNORE INTO google_drive_tokens (id) VALUES (1)");
}

function gdrive_get_settings(mysqli $conn): array {
    $res = $conn->query("SELECT * FROM google_drive_settings WHERE id=1");
    return $res && ($row = $res->fetch_assoc()) ? $row : [];
}

function gdrive_save_settings(mysqli $conn, array $data): bool {
    $stmt = $conn->prepare("UPDATE google_drive_settings SET client_id=?, client_secret=?, redirect_uri=?, folder_id=?, updated_at=NOW() WHERE id=1");
    $stmt->bind_param('ssss', $data['client_id'], $data['client_secret'], $data['redirect_uri'], $data['folder_id']);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function gdrive_get_tokens(mysqli $conn): array {
    $res = $conn->query("SELECT * FROM google_drive_tokens WHERE id=1");
    return $res && ($row = $res->fetch_assoc()) ? $row : [];
}

function gdrive_save_tokens(mysqli $conn, array $t): bool {
    $stmt = $conn->prepare("UPDATE google_drive_tokens SET refresh_token=IFNULL(?, refresh_token), access_token=?, expires_at=?, updated_at=NOW() WHERE id=1");
    $refresh = $t['refresh_token'] ?? null;
    $access = $t['access_token'] ?? null;
    $exp = isset($t['expires_at']) ? (int)$t['expires_at'] : null;
    $stmt->bind_param('ssi', $refresh, $access, $exp);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function gdrive_is_configured(mysqli $conn): bool {
    $s = gdrive_get_settings($conn);
    return !empty($s['client_id']) && !empty($s['client_secret']) && !empty($s['redirect_uri']);
}

function gdrive_is_connected(mysqli $conn): bool {
    $t = gdrive_get_tokens($conn);
    return !empty($t['refresh_token']);
}

function gdrive_scopes(): string {
    // For uploading files created by the app
    return 'https://www.googleapis.com/auth/drive.file';
}

function gdrive_auth_url(array $settings, string $state=''): string {
    $params = [
        'client_id' => $settings['client_id'],
        'redirect_uri' => $settings['redirect_uri'],
        'response_type' => 'code',
        'scope' => gdrive_scopes(),
        'access_type' => 'offline',
        'prompt' => 'consent',
    ];
    if ($state !== '') $params['state'] = $state;
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

function gdrive_token_endpoint(): string { return 'https://oauth2.googleapis.com/token'; }

function gdrive_exchange_code(array $settings, string $code): ?array {
    $post = [
        'code' => $code,
        'client_id' => $settings['client_id'],
        'client_secret' => $settings['client_secret'],
        'redirect_uri' => $settings['redirect_uri'],
        'grant_type' => 'authorization_code',
    ];
    $resp = gdrive_http_post(gdrive_token_endpoint(), $post);
    return $resp ? json_decode($resp, true) : null;
}

function gdrive_refresh_access(array $settings, string $refreshToken): ?array {
    $post = [
        'refresh_token' => $refreshToken,
        'client_id' => $settings['client_id'],
        'client_secret' => $settings['client_secret'],
        'grant_type' => 'refresh_token',
    ];
    $resp = gdrive_http_post(gdrive_token_endpoint(), $post);
    return $resp ? json_decode($resp, true) : null;
}

function gdrive_http_post(string $url, array $fields): ?string {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return null;
    return $resp;
}

function gdrive_upload_file(string $accessToken, string $filePath, string $name, ?string $folderId=null): bool {
    if (!file_exists($filePath)) return false;

    $metadata = [ 'name' => $name ];
    if (!empty($folderId)) $metadata['parents'] = [ $folderId ];

    $boundary = 'gdboundary' . uniqid();
    $metaJson = json_encode($metadata);
    $fileData = file_get_contents($filePath);

    $body = "--{$boundary}\r\n" .
            "Content-Type: application/json; charset=UTF-8\r\n\r\n" .
            $metaJson . "\r\n" .
            "--{$boundary}\r\n" .
            "Content-Type: application/sql\r\n\r\n" .
            $fileData . "\r\n" .
            "--{$boundary}--";

    $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: multipart/related; boundary=' . $boundary,
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err) return false;
    return $code >= 200 && $code < 300;
}
