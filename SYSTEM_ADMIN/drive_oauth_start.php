<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/google_drive_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  header('Location: ../index.php');
  exit();
}

gdrive_ensure_tables($conn);
$settings = gdrive_get_settings($conn);
if (empty($settings['client_id']) || empty($settings['client_secret']) || empty($settings['redirect_uri'])) {
  header('Location: drive_settings.php');
  exit();
}

$state = bin2hex(random_bytes(16));
$_SESSION['gdrive_oauth_state'] = $state;

$authUrl = gdrive_auth_url($settings, $state);
header('Location: ' . $authUrl);
exit();
