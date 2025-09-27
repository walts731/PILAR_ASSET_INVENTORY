<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consumable_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $office = isset($_POST['office']) && $_POST['office'] !== '' ? $_POST['office'] : 'all';

    if ($consumable_id <= 0) {
        header('Location: inventory.php?office=' . urlencode($office) . '&delete=invalid');
        exit();
    }

    $conn->begin_transaction();
    try {
        // Lock and fetch the consumable row
        $stmt = $conn->prepare('SELECT * FROM assets WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $consumable_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $item = $res->fetch_assoc();
        $stmt->close();

        if (!$item) {
            throw new Exception('Consumable not found');
        }

        // Archive snapshot to assets_archive (match table schema)
        $archive_query = $conn->prepare("INSERT INTO assets_archive 
          (id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type, archived_at)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $id = (int)$item['id'];
        $asset_name = $item['asset_name'] ?? null;
        $category = isset($item['category']) ? (int)$item['category'] : null;
        $description = $item['description'] ?? null;
        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : null;
        $unit = $item['unit'] ?? null;
        $status = $item['status'] ?? null;
        $acquisition_date = $item['acquisition_date'] ?? null;
        $office_id = isset($item['office_id']) ? (int)$item['office_id'] : null;
        $red_tagged = isset($item['red_tagged']) ? (int)$item['red_tagged'] : 0;
        $last_updated = $item['last_updated'] ?? null;
        $value = isset($item['value']) ? (float)$item['value'] : null;
        $qr_code = $item['qr_code'] ?? null;
        $type = $item['type'] ?? 'consumable';

        $archive_query->bind_param(
          'isisisssiisdss',
          $id,
          $asset_name,
          $category,
          $description,
          $quantity,
          $unit,
          $status,
          $acquisition_date,
          $office_id,
          $red_tagged,
          $last_updated,
          $value,
          $qr_code,
          $type
        );
        $archive_query->execute();
        $archive_query->close();

        // Delete the consumable row
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $del = $conn->prepare('DELETE FROM assets WHERE id = ?');
        $del->bind_param('i', $consumable_id);
        if (!$del->execute()) {
            $del->close();
            throw new Exception('Failed to delete consumable: ' . $conn->error);
        }
        $del->close();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');

        // Log deletion
        $office_name = 'No Office';
        if (!empty($item['office_id'])) {
            $office_stmt = $conn->prepare('SELECT office_name FROM offices WHERE id = ?');
            $office_stmt->bind_param('i', $item['office_id']);
            $office_stmt->execute();
            $office_res = $office_stmt->get_result();
            if ($office_row = $office_res->fetch_assoc()) {
                $office_name = $office_row['office_name'];
            }
            $office_stmt->close();
        }

        $category_name = 'No Category';
        if (!empty($item['category'])) {
            $category_stmt = $conn->prepare('SELECT category_name FROM categories WHERE id = ?');
            $category_stmt->bind_param('i', $item['category']);
            $category_stmt->execute();
            $category_res = $category_stmt->get_result();
            if ($cat_row = $category_res->fetch_assoc()) {
                $category_name = $cat_row['category_name'];
            }
            $category_stmt->close();
        }

        $deletion_context = 'Qty: ' . ($item['quantity'] ?? 0)
            . ', Value: ₱' . number_format((float)($item['value'] ?? 0), 2)
            . ', Office: ' . $office_name
            . ', Category: ' . $category_name;
        logAssetActivity('DELETE_CONSUMABLE', $item['description'] ?? 'Consumable', $consumable_id, $deletion_context);

        $conn->commit();
        header('Location: inventory.php?office=' . urlencode($office) . '&delete=success');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $desc = $item['description'] ?? 'Unknown Consumable';
        logErrorActivity('Consumables', 'Failed to delete consumable: ' . $desc . ' (ID: ' . $consumable_id . ') - ' . $e->getMessage());
        header('Location: inventory.php?office=' . urlencode($office) . '&delete=failed&msg=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    echo 'Invalid request.';
}
