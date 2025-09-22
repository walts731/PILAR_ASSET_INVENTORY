<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Method Not Allowed';
  exit();
}

$iirup_id = isset($_POST['iirup_id']) ? (int)$_POST['iirup_id'] : 0;
if ($iirup_id <= 0) {
  $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid IIRUP reference.'];
  header('Location: saved_iirup.php');
  exit();
}

// Collect header/footer fields
$accountable_officer = trim($_POST['accountable_officer'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$office = trim($_POST['office'] ?? '');
$footer_accountable_officer = trim($_POST['footer_accountable_officer'] ?? '');
$footer_authorized_official = trim($_POST['footer_authorized_official'] ?? '');
$footer_designation_officer = trim($_POST['footer_designation_officer'] ?? '');
$footer_designation_official = trim($_POST['footer_designation_official'] ?? '');
$remarks = $_POST['remarks'] ?? [];
if (!is_array($remarks)) { $remarks = []; }

$conn->begin_transaction();
try {
  // Handle header_image upload
  $header_image = null;
  $prev_image = '';
  $resPrev = $conn->prepare('SELECT header_image FROM iirup_form WHERE id = ? LIMIT 1');
  if ($resPrev) {
    $resPrev->bind_param('i', $iirup_id);
    $resPrev->execute();
    $rs = $resPrev->get_result();
    $rowPrev = $rs ? $rs->fetch_assoc() : null;
    $prev_image = $rowPrev ? trim((string)($rowPrev['header_image'] ?? '')) : '';
    $resPrev->close();
  }

  if (!empty($_FILES['header_image']['name']) && is_uploaded_file($_FILES['header_image']['tmp_name'])) {
    // Delete previous file if exists
    if ($prev_image !== '') {
      $prev_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $prev_image;
      if (is_file($prev_path)) { @unlink($prev_path); }
    }
    $orig = basename($_FILES['header_image']['name']);
    $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
    $header_image = 'iirup_header_' . time() . '_' . substr(sha1(random_bytes(8)), 0, 8) . '_' . $safe;
    $target_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR;
    if (!is_dir($target_dir)) { @mkdir($target_dir, 0775, true); }
    $target_file = $target_dir . $header_image;
    if (!move_uploaded_file($_FILES['header_image']['tmp_name'], $target_file)) {
      throw new Exception('Failed to upload header image.');
    }
  } else {
    // Keep existing
    $header_image = $prev_image;
  }

  // Update header/footer
  $stmt = $conn->prepare('UPDATE iirup_form SET accountable_officer = ?, designation = ?, office = ?, header_image = ?, footer_accountable_officer = ?, footer_authorized_official = ?, footer_designation_officer = ?, footer_designation_official = ? WHERE id = ?');
  if (!$stmt) { throw new Exception('Prepare update iirup_form failed: ' . $conn->error); }
  $stmt->bind_param('ssssssssi', $accountable_officer, $designation, $office, $header_image, $footer_accountable_officer, $footer_authorized_official, $footer_designation_officer, $footer_designation_official, $iirup_id);
  if (!$stmt->execute()) { throw new Exception('Update iirup_form failed: ' . $stmt->error); }
  $stmt->close();

  // Update remarks for items
  if (!empty($remarks)) {
    $stmtItem = $conn->prepare('UPDATE iirup_items SET remarks = ? WHERE item_id = ? AND iirup_id = ?');
    if (!$stmtItem) { throw new Exception('Prepare update iirup_items failed: ' . $conn->error); }
    foreach ($remarks as $item_id => $val) {
      $val = trim((string)$val);
      $item_id = (int)$item_id;
      if ($item_id <= 0) { continue; }
      $stmtItem->bind_param('sii', $val, $item_id, $iirup_id);
      if (!$stmtItem->execute()) { throw new Exception('Update item #' . $item_id . ' failed: ' . $stmtItem->error); }
    }
    $stmtItem->close();
  }

  $conn->commit();
  $_SESSION['flash'] = ['type' => 'success', 'message' => 'IIRUP updated successfully.'];
  header('Location: view_iirup.php?id=' . $iirup_id . '&success=1');
  exit();
} catch (Throwable $e) {
  $conn->rollback();
  $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to update IIRUP: ' . $e->getMessage()];
  header('Location: view_iirup.php?id=' . $iirup_id);
  exit();
}
