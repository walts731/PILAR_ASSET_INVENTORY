<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$asset_id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($asset_id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid asset id']);
  exit();
}

$conn->begin_transaction();
try {
  // Get complete asset info for archiving and parent aggregate id
  $stmt = $conn->prepare("SELECT * FROM assets WHERE id = ? FOR UPDATE");
  $stmt->bind_param('i', $asset_id);
  $stmt->execute();
  $res = $stmt->get_result();
  $asset = $res->fetch_assoc();
  $stmt->close();

  if (!$asset) {
    throw new Exception('Asset not found');
  }

  $asset_new_id = (int)($asset['asset_new_id'] ?? 0);

  // 1) Archive asset to assets_archive before deletion
  $archive_query = $conn->prepare("INSERT INTO assets_archive 
    (id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type, archived_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
  $archive_query->bind_param(
    'iississsissdss',
    $asset['id'],
    $asset['asset_name'],
    $asset['category'],
    $asset['description'],
    $asset['quantity'],
    $asset['unit'],
    $asset['status'],
    $asset['acquisition_date'],
    $asset['office_id'],
    $asset['red_tagged'],
    $asset['last_updated'],
    $asset['value'],
    $asset['qr_code'],
    $asset['type']
  );
  $archive_query->execute();
  $archive_query->close();

  // 2) Best-effort cleanup of dependent records
  // MR details
  $stmt = $conn->prepare("DELETE FROM mr_details WHERE asset_id = ?");
  $stmt->bind_param('i', $asset_id);
  $stmt->execute();
  $stmt->close();

  // ICS items mapping (decrement quantity and total_cost, or delete if becomes zero)
  $stmt = $conn->prepare("SELECT item_id, quantity, unit_cost FROM ics_items WHERE asset_id = ?");
  $stmt->bind_param('i', $asset_id);
  $stmt->execute();
  $resItems = $stmt->get_result();
  $items = [];
  while ($row = $resItems->fetch_assoc()) { $items[] = $row; }
  $stmt->close();

  foreach ($items as $it) {
    $itemId = (int)$it['item_id'];
    $qty = (int)$it['quantity'];
    $unitCost = (float)$it['unit_cost'];
    if ($qty > 1) {
      $newQty = $qty - 1;
      $newTotal = $unitCost * $newQty;
      $stmtU = $conn->prepare("UPDATE ics_items SET quantity = ?, total_cost = ? WHERE item_id = ?");
      $stmtU->bind_param('idi', $newQty, $newTotal, $itemId);
      $stmtU->execute();
      $stmtU->close();
    } else {
      $stmtD = $conn->prepare("DELETE FROM ics_items WHERE item_id = ?");
      $stmtD->bind_param('i', $itemId);
      $stmtD->execute();
      $stmtD->close();
    }
  }

  // 3) Disable FK checks as last resort within this transaction scope
  $conn->query('SET FOREIGN_KEY_CHECKS=0');

  // 4) Delete the asset
  $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
  $stmt->bind_param('i', $asset_id);
  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Failed to delete asset: ' . $conn->error);
  }
  $stmt->close();

  // 5) Re-enable FK checks
  $conn->query('SET FOREIGN_KEY_CHECKS=1');

  // 6) Decrement the parent assets_new quantity, if applicable
  if ($asset_new_id > 0) {
    $stmt = $conn->prepare("UPDATE assets_new SET quantity = CASE WHEN quantity > 0 THEN quantity - 1 ELSE 0 END WHERE id = ?");
    $stmt->bind_param('i', $asset_new_id);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Failed to update assets_new quantity');
    }
    $stmt->close();
  }

  // Log asset deletion for audit trail
  $office_name = 'No Office';
  if ($asset['office_id']) {
      $office_stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
      $office_stmt->bind_param("i", $asset['office_id']);
      $office_stmt->execute();
      $office_result = $office_stmt->get_result();
      if ($office_data = $office_result->fetch_assoc()) {
          $office_name = $office_data['office_name'];
      }
      $office_stmt->close();
  }
  
  $category_name = 'No Category';
  if ($asset['category']) {
      $category_stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
      $category_stmt->bind_param("i", $asset['category']);
      $category_stmt->execute();
      $category_result = $category_stmt->get_result();
      if ($category_data = $category_result->fetch_assoc()) {
          $category_name = $category_data['category_name'];
      }
      $category_stmt->close();
  }
  
  $deletion_context = "Qty: {$asset['quantity']}, Value: ₱" . number_format($asset['value'], 2) . ", Office: {$office_name}, Category: {$category_name}, Source: No Property Tag Tab";
  logAssetActivity('DELETE', $asset['description'], $asset_id, $deletion_context);

  $conn->commit();
  echo json_encode(['success' => true]);
} catch (Exception $e) {
  $conn->rollback();
  
  // Log deletion failure
  $asset_description = $asset['description'] ?? 'Unknown Asset';
  logErrorActivity('Assets', "Failed to delete asset from No Property Tag tab: {$asset_description} (ID: {$asset_id}) - " . $e->getMessage());
  
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
