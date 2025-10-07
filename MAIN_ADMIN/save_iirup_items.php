<?php
require_once '../connect.php';
require_once '../includes/lifecycle_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Method Not Allowed';
  exit;
}

// Ensure table exists with proper defaults and FKs to assets(id) and iirup_form(id)
$createSql = "CREATE TABLE IF NOT EXISTS iirup_items (
  item_id INT(11) NOT NULL AUTO_INCREMENT,
  iirup_id INT(11) NOT NULL,
  asset_id INT(11) NULL,
  date_acquired DATE NULL,
  particulars VARCHAR(255) NOT NULL DEFAULT '',
  property_no VARCHAR(255) NULL,
  qty INT(11) NOT NULL DEFAULT 1,
  unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  accumulated_depreciation DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  accumulated_impairment_losses DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  carrying_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  remarks VARCHAR(255) NOT NULL DEFAULT 'Unserviceable',
  sale VARCHAR(255) NULL,
  transfer VARCHAR(255) NULL,
  destruction VARCHAR(255) NULL,
  others VARCHAR(255) NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  appraised_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  or_no VARCHAR(255) NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  dept_office VARCHAR(255) NULL,
  code VARCHAR(255) NULL,
  red_tag VARCHAR(255) NULL,
  date_received DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (item_id),
  KEY idx_iirup_id (iirup_id),
  KEY idx_asset_id (asset_id),
  CONSTRAINT fk_iirup_items_iirup FOREIGN KEY (iirup_id) REFERENCES iirup_form(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_iirup_items_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if (!$conn->query($createSql)) {
  http_response_code(500);
  echo 'Failed to ensure iirup_items table: ' . $conn->error;
  exit;
}

// Schema migration: if table existed before without iirup_id, add it now (NULLABLE for safety)
try {
  $resCols = $conn->query("SHOW COLUMNS FROM iirup_items LIKE 'iirup_id'");
  if ($resCols && $resCols->num_rows === 0) {
    // Add column as NULL to avoid alter failures on existing rows
    $conn->query("ALTER TABLE iirup_items ADD COLUMN iirup_id INT(11) NULL AFTER item_id");
    // Add index if not exists
    $resIdx = $conn->query("SHOW INDEX FROM iirup_items WHERE Key_name='idx_iirup_id'");
    if (!$resIdx || $resIdx->num_rows === 0) {
      $conn->query("CREATE INDEX idx_iirup_id ON iirup_items (iirup_id)");
    }
    // Try to add FK (may fail if existing data violates; ignore)
    try {
      $conn->query("ALTER TABLE iirup_items ADD CONSTRAINT fk_iirup_items_iirup FOREIGN KEY (iirup_id) REFERENCES iirup_form(id) ON UPDATE CASCADE ON DELETE CASCADE");
    } catch (Throwable $e2) { /* ignore */ }
  }
} catch (Throwable $e) {
  // Do not block transaction; proceed as long as column exists or gets added later
}

// Collect arrays from POST safely
$asset_ids = $_POST['asset_id'] ?? [];
$date_acquired = $_POST['date_acquired'] ?? [];
$particulars = $_POST['particulars'] ?? [];
$property_no = $_POST['property_no'] ?? [];
$qty = $_POST['qty'] ?? [];
$unit_cost = $_POST['unit_cost'] ?? [];
$total_cost = $_POST['total_cost'] ?? [];
$accum_dep = $_POST['accumulated_depreciation'] ?? [];
$accum_imp = $_POST['accumulated_impairment_losses'] ?? [];
$carrying = $_POST['carrying_amount'] ?? [];
$remarks = $_POST['remarks'] ?? [];
$sale = $_POST['sale'] ?? [];
$transfer = $_POST['transfer'] ?? [];
$destruction = $_POST['destruction'] ?? [];
$others = $_POST['others'] ?? [];
$total = $_POST['total'] ?? [];
$appraised_value = $_POST['appraised_value'] ?? [];
$or_no = $_POST['or_no'] ?? [];
$amount = $_POST['amount'] ?? [];
$dept_office = $_POST['dept_office'] ?? [];
$code = $_POST['code'] ?? [];
$red_tag = $_POST['red_tag'] ?? [];
$date_received = $_POST['date_received'] ?? [];

$rows = max(
  count($particulars), count($asset_ids), count($qty)
);

if ($rows === 0) {
  echo 'No items submitted.';
  exit;
}

$conn->begin_transaction();

try {
  // Insert header/footer into iirup_form (with header_image upload)
  $accountable_officer = trim($_POST['accountable_officer'] ?? '');
  $designation = trim($_POST['designation'] ?? '');
  $office = trim($_POST['office'] ?? '');
  $footer_accountable_officer  = trim($_POST['footer_accountable_officer'] ?? '');
  $footer_authorized_official  = trim($_POST['footer_authorized_official'] ?? '');
  $footer_designation_officer  = trim($_POST['footer_designation_officer'] ?? '');
  $footer_designation_official = trim($_POST['footer_designation_official'] ?? '');

  // Handle header_image upload: delete previous, then save new if provided
  $header_image = null;
  if (!empty($_FILES['header_image']['name']) && is_uploaded_file($_FILES['header_image']['tmp_name'])) {
    // Delete previous image file if exists
    $resPrev = $conn->query("SELECT header_image FROM iirup_form ORDER BY id DESC LIMIT 1");
    if ($resPrev && ($rowPrev = $resPrev->fetch_assoc())) {
      $prev = trim((string)($rowPrev['header_image'] ?? ''));
      if ($prev !== '') {
        $prevPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $prev;
        if (is_file($prevPath)) { @unlink($prevPath); }
      }
    }

    // Save new file
    $orig = basename($_FILES['header_image']['name']);
    $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
    $header_image = 'iirup_header_' . time() . '_' . substr(sha1(random_bytes(8)), 0, 8) . '_' . $safe;
    $target_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR;
    if (!is_dir($target_dir)) { @mkdir($target_dir, 0775, true); }
    $target_file = $target_dir . $header_image;
    if (!move_uploaded_file($_FILES['header_image']['tmp_name'], $target_file)) {
      throw new Exception('Failed to upload header image.');
    }
  }

  // If no new upload, retain existing header_image from hidden POST
  if ($header_image === null) {
    $existing_header_image = trim($_POST['header_image'] ?? '');
    if ($existing_header_image !== '') {
      $header_image = $existing_header_image;
    }
  }

  // If still empty, fetch the latest header_image from iirup_form as default
  if ($header_image === null || $header_image === '') {
    $resLast = $conn->query("SELECT header_image FROM iirup_form WHERE header_image IS NOT NULL AND header_image <> '' ORDER BY id DESC LIMIT 1");
    if ($resLast && ($rowLast = $resLast->fetch_assoc())) {
      $fallback = trim((string)($rowLast['header_image'] ?? ''));
      if ($fallback !== '') {
        $header_image = $fallback;
      }
    }
  }

  $stmt_hdr = $conn->prepare("INSERT INTO iirup_form (
      accountable_officer, designation, office, header_image,
      footer_accountable_officer, footer_authorized_official,
      footer_designation_officer, footer_designation_official
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  if (!$stmt_hdr) { throw new Exception('Prepare iirup header insert failed: ' . $conn->error); }
  $stmt_hdr->bind_param(
    'ssssssss',
    $accountable_officer,
    $designation,
    $office,
    $header_image,
    $footer_accountable_officer,
    $footer_authorized_official,
    $footer_designation_officer,
    $footer_designation_official
  );
  if (!$stmt_hdr->execute()) { throw new Exception('Insert iirup header failed: ' . $stmt_hdr->error); }
  $iirup_id = $conn->insert_id;
  $stmt_hdr->close();

  // Prepare items insert
  $insertSql = "INSERT INTO iirup_items (
      iirup_id, asset_id, date_acquired, particulars, property_no, qty, unit_cost, total_cost,
      accumulated_depreciation, accumulated_impairment_losses, carrying_amount,
      remarks, sale, transfer, destruction, others, total, appraised_value,
      or_no, amount, dept_office, code, red_tag, date_received
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
  $insertStmt = $conn->prepare($insertSql);
  if (!$insertStmt) { throw new Exception('Prepare insert failed: ' . $conn->error); }

  $updateAssetSql = "UPDATE assets SET status = 'unserviceable' WHERE id = ?";
  $updateAssetStmt = $conn->prepare($updateAssetSql);
  if (!$updateAssetStmt) { throw new Exception('Prepare update-asset failed: ' . $conn->error); }

  // Prepare mr_details update to set unserviceable = 1
  $updateMrSql = "UPDATE mr_details SET unserviceable = 1, serviceable = 0 WHERE asset_id = ?";
  $updateMrStmt = $conn->prepare($updateMrSql);
  if (!$updateMrStmt) { throw new Exception('Prepare update-mr failed: ' . $conn->error); }

  $inserted = 0;
  for ($i = 0; $i < $rows; $i++) {
    // Extract row values with defaults
    $aid = isset($asset_ids[$i]) && $asset_ids[$i] !== '' ? (int)$asset_ids[$i] : null;
    $da = !empty($date_acquired[$i]) ? $date_acquired[$i] : null;
    $part = trim($particulars[$i] ?? '');
    $pno = trim($property_no[$i] ?? '');
    $q = isset($qty[$i]) && (int)$qty[$i] > 0 ? (int)$qty[$i] : 1;
    $uc = isset($unit_cost[$i]) && $unit_cost[$i] !== '' ? (float)$unit_cost[$i] : 0.00;
    $tc = isset($total_cost[$i]) && $total_cost[$i] !== '' ? (float)$total_cost[$i] : ($q * $uc);
    $ad = isset($accum_dep[$i]) && $accum_dep[$i] !== '' ? (float)$accum_dep[$i] : 0.00;
    $ai = isset($accum_imp[$i]) && $accum_imp[$i] !== '' ? (float)$accum_imp[$i] : 0.00;
    $car = isset($carrying[$i]) && $carrying[$i] !== '' ? (float)$carrying[$i] : 0.00;
    $rem = trim($remarks[$i] ?? 'Unserviceable');
    $sl = trim($sale[$i] ?? '');
    $tr = trim($transfer[$i] ?? '');
    $des = trim($destruction[$i] ?? '');
    $oth = trim($others[$i] ?? '');
    $tot = isset($total[$i]) && $total[$i] !== '' ? (float)$total[$i] : 0.00;
    $apv = isset($appraised_value[$i]) && $appraised_value[$i] !== '' ? (float)$appraised_value[$i] : 0.00;
    $orn = trim($or_no[$i] ?? '');
    $amt = isset($amount[$i]) && $amount[$i] !== '' ? (float)$amount[$i] : 0.00;
    $dept = trim($dept_office[$i] ?? '');
    $cd = trim($code[$i] ?? '');
    $rt = trim($red_tag[$i] ?? '');
    $dr = !empty($date_received[$i]) ? $date_received[$i] : null;

    // Skip empty rows (no particulars and no asset id)
    if ($part === '' && $aid === null) {
      continue;
    }

    // Basic validation: quantity must be >=1
    if ($q <= 0) { $q = 1; }

    // Bind and insert (types string has 24 placeholders)
    // Order: i (iirup_id), i (asset_id), s (date_acquired), s (particulars), s (property_no), i (qty),
    // d (unit_cost), d (total_cost), d (accumulated_depreciation), d (accumulated_impairment_losses), d (carrying_amount),
    // s (remarks), s (sale), s (transfer), s (destruction), s (others),
    // d (total), d (appraised_value), s (or_no), d (amount), s (dept_office), s (code), s (red_tag), s (date_received)
    $insertStmt->bind_param(
      'iisssidddddsssssddsdssss',
      $iirup_id,
      $aid,
      $da,
      $part,
      $pno,
      $q,
      $uc,
      $tc,
      $ad,
      $ai,
      $car,
      $rem,
      $sl,
      $tr,
      $des,
      $oth,
      $tot,
      $apv,
      $orn,
      $amt,
      $dept,
      $cd,
      $rt,
      $dr
    );

    if (!$insertStmt->execute()) {
      throw new Exception('Insert failed: ' . $insertStmt->error);
    }

    $inserted++;

    // If an existing asset is selected, update its status to unserviceable
    // Note: red_tagged remains 0 until create_red_tag.php is executed
    if (!is_null($aid) && $aid > 0) {
      // Update asset status to unserviceable
      $updateAssetStmt->bind_param('i', $aid);
      if (!$updateAssetStmt->execute()) {
        throw new Exception('Asset update failed: ' . $updateAssetStmt->error);
      }
      // Lifecycle: DISPOSAL_LISTED for asset added to IIRUP
      if (function_exists('logLifecycleEvent')) {
        $disp_note = sprintf(
          'IIRUP #%d; Remarks: %s; Method: %s%s%s%s',
          (int)$iirup_id,
          (string)$rem,
          $sl !== '' ? ('Sale=' . $sl) : 'N/A',
          $tr !== '' ? (', Transfer=' . $tr) : '',
          $des !== '' ? (', Destruction=' . $des) : '',
          $oth !== '' ? (', Others=' . $oth) : ''
        );
        logLifecycleEvent((int)$aid, 'DISPOSAL_LISTED', 'iirup_form', (int)$iirup_id, null, null, null, null, $disp_note);
      }
      
      // Update corresponding mr_details record to set unserviceable = 1
      // Only update if mr_details record exists for this asset
      $updateMrStmt->bind_param('i', $aid);
      if (!$updateMrStmt->execute()) {
        // Log warning but don't fail the transaction if mr_details doesn't exist
        error_log("Warning: Failed to update mr_details for asset_id $aid: " . $updateMrStmt->error);
      }
    }
  }

  // Close prepared statements
  $insertStmt->close();
  $updateAssetStmt->close();
  $updateMrStmt->close();

  // Clean up temporary IIRUP items after successful submission
  // Since the temp table doesn't have user isolation, clear all temporary items
  $cleanup_stmt = $conn->prepare("DELETE FROM temp_iirup_items");
  if ($cleanup_stmt) {
    $cleanup_stmt->execute();
    $cleanup_stmt->close();
  }

  $conn->commit();

  // Redirect back to the form with success flag if possible
  $back = $_SERVER['HTTP_REFERER'] ?? 'iirup_form.php';
  $sep = (strpos($back, '?') !== false) ? '&' : '?';
  header('Location: ' . $back . $sep . 'success=1&count=' . $inserted);
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  http_response_code(500);
  echo 'Transaction failed: ' . $e->getMessage();
  exit;
}
