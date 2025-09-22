<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
  $asset_id = (int)$_GET['id'];
  if ($asset_id <= 0) {
    header('Location: inventory.php?delete=invalid');
    exit();
  }

  $conn->begin_transaction();
  try {
    // Lock and fetch asset row
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

    // 1) Archive snapshot to assets_archive (best-effort)
    $archive_query = $conn->prepare("INSERT INTO assets_archive 
      (id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand, archived_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $archive_query->bind_param(
      'iississsissdsssssssss',
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
      $asset['type'],
      $asset['image'],
      $asset['serial_no'],
      $asset['code'],
      $asset['property_no'],
      $asset['model'],
      $asset['brand']
    );
    $archive_query->execute();
    $archive_query->close();

    // 2) Remove MR details for this asset
    $stmt = $conn->prepare("DELETE FROM mr_details WHERE asset_id = ?");
    $stmt->bind_param('i', $asset_id);
    $stmt->execute();
    $stmt->close();

    // 3) Adjust ICS items: decrement quantity and total_cost; delete row if qty becomes zero
    $stmt = $conn->prepare("SELECT item_id, quantity, unit_cost FROM ics_items WHERE asset_id = ?");
    $stmt->bind_param('i', $asset_id);
    $stmt->execute();
    $resItems = $stmt->get_result();
    $icsItems = [];
    while ($row = $resItems->fetch_assoc()) { $icsItems[] = $row; }
    $stmt->close();

    foreach ($icsItems as $it) {
      $itemId = (int)$it['item_id'];
      $qty = (int)$it['quantity'];
      $unitCost = (float)$it['unit_cost'];
      if ($qty > 1) {
        $newQty = $qty - 1;
        $newTotal = $unitCost * $newQty;
        $u = $conn->prepare("UPDATE ics_items SET quantity = ?, total_cost = ? WHERE item_id = ?");
        $u->bind_param('idi', $newQty, $newTotal, $itemId);
        $u->execute();
        $u->close();
      } else {
        $d = $conn->prepare("DELETE FROM ics_items WHERE item_id = ?");
        $d->bind_param('i', $itemId);
        $d->execute();
        $d->close();
      }
    }

    // 4) Temporarily disable FK checks (last resort) and delete the asset
    $conn->query('SET FOREIGN_KEY_CHECKS=0');
    $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
    $stmt->bind_param('i', $asset_id);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Failed to delete asset: ' . $conn->error);
    }
    $stmt->close();
    $conn->query('SET FOREIGN_KEY_CHECKS=1');

    // 5) Decrement parent aggregate quantity if present
    if ($asset_new_id > 0) {
      $stmt = $conn->prepare("UPDATE assets_new SET quantity = CASE WHEN quantity > 0 THEN quantity - 1 ELSE 0 END WHERE id = ?");
      $stmt->bind_param('i', $asset_new_id);
      $stmt->execute();
      $stmt->close();
    }

    // Log asset deletion
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
    
    $deletion_context = "Qty: {$asset['quantity']}, Value: ₱" . number_format($asset['value'], 2) . ", Office: {$office_name}, Category: {$category_name}";
    logAssetActivity('DELETE', $asset['description'], $asset_id, $deletion_context);

    $conn->commit();
    header('Location: inventory.php?delete=success');
    exit();
  } catch (Exception $e) {
    $conn->rollback();
    
    // Log deletion failure
    $asset_description = $asset['description'] ?? 'Unknown Asset';
    logErrorActivity('Assets', "Failed to delete asset: {$asset_description} (ID: {$asset_id}) - " . $e->getMessage());
    
    header('Location: inventory.php?delete=failed&msg=' . urlencode($e->getMessage()));
    exit();
  }
} else {
  echo "Invalid request.";
}
?>
