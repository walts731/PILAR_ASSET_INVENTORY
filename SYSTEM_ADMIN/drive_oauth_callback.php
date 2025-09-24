<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/google_drive_helper.php';
require_once __DIR__ . '/../includes/audit_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  header('Location: ../index.php');
  exit();
}

gdrive_ensure_tables($conn);
$settings = gdrive_get_settings($conn);

$state = $_GET['state'] ?? '';
$code  = $_GET['code'] ?? '';
$error = $_GET['error'] ?? '';

if (!empty($error)) {
  header('Location: drive_settings.php?error=' . urlencode($error));
  exit();
}

if (empty($code) || empty($state) || !isset($_SESSION['gdrive_oauth_state']) || $state !== $_SESSION['gdrive_oauth_state']) {
  header('Location: drive_settings.php?error=' . urlencode('Invalid OAuth state'));
  exit();
}

unset($_SESSION['gdrive_oauth_state']);

$tok = gdrive_exchange_code($settings, $code);
if (!$tok || empty($tok['refresh_token'])) {
  // Some accounts may not return refresh_token on subsequent consent unless prompt=consent; we set it.
  header('Location: drive_settings.php?error=' . urlencode('Failed to obtain refresh token'));
  exit();
}

$accessToken = $tok['access_token'] ?? null;
$expiresIn   = isset($tok['expires_in']) ? (int)$tok['expires_in'] : 3600;
$expiresAt   = time() + $expiresIn - 60;
$refreshToken= $tok['refresh_token'];

gdrive_save_tokens($conn, [
  'refresh_token' => $refreshToken,
  'access_token'  => $accessToken,
  'expires_at'    => $expiresAt,
]);

if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
  logUserActivity('GDRIVE_CONNECTED', 'System', 'Google Drive connected for backups');
}

header('Location: drive_settings.php?connected=1');
exit();
