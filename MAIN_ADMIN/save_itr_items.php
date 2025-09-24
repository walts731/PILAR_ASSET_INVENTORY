<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: itr_form.php');
  exit();
}

$itr_id = isset($_POST['itr_id']) ? (int)$_POST['itr_id'] : 0;
if ($itr_id <= 0) {
  die('Invalid ITR ID.');
}

// Gather editable header/footer fields
$entity_name = trim($_POST['entity_name'] ?? '');
$fund_cluster = trim($_POST['fund_cluster'] ?? '');
$from_accountable_officer = trim($_POST['from_accountable_officer'] ?? '');
$to_accountable_officer = trim($_POST['to_accountable_officer'] ?? '');
$itr_no = trim($_POST['itr_no'] ?? '');
$date = trim($_POST['date'] ?? '');

// transfer_type from checkboxes + optional Others
$transfer_arr = isset($_POST['transfer_type']) && is_array($_POST['transfer_type']) ? array_map('trim', $_POST['transfer_type']) : [];
$transfer_other = trim($_POST['transfer_type_other'] ?? '');
$known = ['Donation','Reassignment','Relocation'];
$selected = [];
foreach ($transfer_arr as $v) {
  if (in_array($v, $known, true)) { $selected[] = $v; }
}
if (in_array('Others', $transfer_arr, true) && $transfer_other !== '') {
  $selected[] = $transfer_other;
}
$selected = array_values(array_unique($selected));
$transfer_type = implode(',', $selected);

$reason_for_transfer = isset($_POST['reason_for_transfer']) ? trim($_POST['reason_for_transfer']) : '';

// Footer fields
$approved_by = trim($_POST['approved_by'] ?? '');
$approved_designation = trim($_POST['approved_designation'] ?? '');
$approved_date = trim($_POST['approved_date'] ?? '');
$released_by = trim($_POST['released_by'] ?? '');
$released_designation = trim($_POST['released_designation'] ?? '');
$released_date = trim($_POST['released_date'] ?? '');
$received_by = trim($_POST['received_by'] ?? '');
$received_designation = trim($_POST['received_designation'] ?? '');
$received_date = trim($_POST['received_date'] ?? '');

// Optional header image upload
$header_image = '';
if (!empty($_FILES['header_image']['name']) && is_uploaded_file($_FILES['header_image']['tmp_name'])) {
  $target_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR; // ../img/
  if (!is_dir($target_dir)) {
    // Best effort create directory if missing
    @mkdir($target_dir, 0777, true);
  }
  $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['header_image']['name']));
  $target_file = $target_dir . $safeName;
  if (@move_uploaded_file($_FILES['header_image']['tmp_name'], $target_file)) {
    $header_image = $safeName; // store filename only, consistent with SYSTEM_ADMIN saver
  }
}

// Update assets.employee_id to match To Accountable Officer for all selected asset_ids
$toOfficerName = trim($_POST['to_accountable_officer'] ?? '');
if ($toOfficerName !== '') {
  // Resolve employee_id by name
  $empId = 0;
  if ($stmt = $conn->prepare('SELECT employee_id FROM employees WHERE name = ? LIMIT 1')) {
    $stmt->bind_param('s', $toOfficerName);
    $stmt->execute();
    $stmt->bind_result($eid);
    if ($stmt->fetch()) { $empId = (int)$eid; }
    $stmt->close();
  }
  if ($empId > 0) {
    // Gather unique asset_ids from submitted items
    $assetIds = [];
    foreach ($items as $it) {
      $aid = isset($it['asset_id']) ? (int)$it['asset_id'] : 0;
      if ($aid > 0) { $assetIds[$aid] = true; }
    }
    $assetIds = array_keys($assetIds);
    if (!empty($assetIds)) {
      $placeholders = implode(',', array_fill(0, count($assetIds), '?'));
      $types = str_repeat('i', count($assetIds) + 1); // +1 for empId
      $sql = "UPDATE assets SET employee_id = ? WHERE id IN ($placeholders)";
      $stmt = $conn->prepare($sql);
      // Build bind params
      $params = array_merge([$empId], $assetIds);
      $stmt->bind_param($types, ...$params);
      $stmt->execute();
      $stmt->close();
    }
  }
}

// Update itr_form header/footer in one statement
if ($header_image !== '') {
  $sql = 'UPDATE itr_form SET header_image=?, entity_name=?, fund_cluster=?, from_accountable_officer=?, to_accountable_officer=?, itr_no=?, `date`=?, transfer_type=?, reason_for_transfer=?, approved_by=?, approved_designation=?, approved_date=?, released_by=?, released_designation=?, released_date=?, received_by=?, received_designation=?, received_date=? WHERE itr_id=?';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    'ssssssssssssssssssi',
    $header_image,
    $entity_name,
    $fund_cluster,
    $from_accountable_officer,
    $to_accountable_officer,
    $itr_no,
    $date,
    $transfer_type,
    $reason_for_transfer,
    $approved_by,
    $approved_designation,
    $approved_date,
    $released_by,
    $released_designation,
    $released_date,
    $received_by,
    $received_designation,
    $received_date,
    $itr_id
  );
} else {
  $sql = 'UPDATE itr_form SET entity_name=?, fund_cluster=?, from_accountable_officer=?, to_accountable_officer=?, itr_no=?, `date`=?, transfer_type=?, reason_for_transfer=?, approved_by=?, approved_designation=?, approved_date=?, released_by=?, released_designation=?, released_date=?, received_by=?, received_designation=?, received_date=? WHERE itr_id=?';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    'sssssssssssssssssi',
    $entity_name,
    $fund_cluster,
    $from_accountable_officer,
    $to_accountable_officer,
    $itr_no,
    $date,
    $transfer_type,
    $reason_for_transfer,
    $approved_by,
    $approved_designation,
    $approved_date,
    $released_by,
    $released_designation,
    $released_date,
    $received_by,
    $received_designation,
    $received_date,
    $itr_id
  );
}
$stmt->execute();
$stmt->close();

$items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];

// Fetch current item IDs for this ITR
$currentIds = [];
$curRes = $conn->prepare('SELECT item_id FROM itr_items WHERE itr_id = ?');
$curRes->bind_param('i', $itr_id);
$curRes->execute();
$r = $curRes->get_result();
while ($row = $r->fetch_assoc()) { $currentIds[(int)$row['item_id']] = true; }
$curRes->close();

$seenIds = [];

// Prepare statements
$insertSql = 'INSERT INTO itr_items (itr_id, date_acquired, property_no, asset_id, description, amount, condition_of_PPE) VALUES (?,?,?,?,?,?,?)';
$ins = $conn->prepare($insertSql);

$updateSql = 'UPDATE itr_items SET date_acquired=?, property_no=?, asset_id=?, description=?, amount=?, condition_of_PPE=? WHERE item_id=? AND itr_id=?';
$upd = $conn->prepare($updateSql);

foreach ($items as $key => $it) {
  $item_id = isset($it['item_id']) && ctype_digit((string)$it['item_id']) ? (int)$it['item_id'] : 0;
  $date_acquired = trim($it['date_acquired'] ?? '');
  $property_no = trim($it['property_no'] ?? '');
  $asset_id = isset($it['asset_id']) ? (int)$it['asset_id'] : 0;
  $description = trim($it['description'] ?? '');
  $amount = isset($it['amount']) ? (float)$it['amount'] : 0.0;
  $condition = trim($it['condition_of_PPE'] ?? '');

  // Normalize empty dates to NULL
  $date_acq = !empty($date_acquired) ? $date_acquired : null;
  
  if ($item_id > 0 && isset($currentIds[$item_id])) {
    // update
    $upd->bind_param('ssisdiii', $date_acq, $property_no, $asset_id, $description, $amount, $condition, $item_id, $itr_id);
    $upd->execute();
    $seenIds[$item_id] = true;
  } else {
    // insert
    $ins->bind_param('issisds', $itr_id, $date_acq, $property_no, $asset_id, $description, $amount, $condition);
    $ins->execute();
    $newId = $ins->insert_id;
    $seenIds[$newId] = true;
  }
}

// Delete removed items
if (!empty($currentIds)) {
  $toDelete = array_diff(array_keys($currentIds), array_keys($seenIds));
  if (!empty($toDelete)) {
    $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
    $types = str_repeat('i', count($toDelete)) . 'i';
    $sql = "DELETE FROM itr_items WHERE item_id IN ($placeholders) AND itr_id = ?";
    $stmt = $conn->prepare($sql);
    $params = $toDelete;
    $params[] = $itr_id;

    // bind dynamically
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
  }
}

// Redirect back
$_SESSION['flash'] = [ 'type' => 'success', 'message' => 'ITR items saved successfully.' ];
header('Location: itr_form.php');
exit();
