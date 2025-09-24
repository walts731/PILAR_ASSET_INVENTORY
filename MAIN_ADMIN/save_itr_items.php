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
