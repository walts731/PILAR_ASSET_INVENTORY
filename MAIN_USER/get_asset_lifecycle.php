<?php
require_once '../connect.php';
require_once '../includes/lifecycle_helper.php';
header('Content-Type: application/json');

try {
    // Ensure lifecycle table exists
    ensureLifecycleTable($conn);

    $source = $_GET['source'] ?? 'assets'; // 'assets' | 'assets_new'
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid id']);
        exit;
    }

    // Resolve asset IDs to query lifecycle events for
    $assetIds = [];
    if ($source === 'assets_new') {
        $stmt = $conn->prepare("SELECT id FROM assets WHERE asset_new_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $assetIds[] = (int)$row['id'];
        }
        $stmt->close();

        if (empty($assetIds)) {
            echo json_encode(['events' => [], 'summary' => ['count' => 0, 'assets_count' => 0]]);
            exit;
        }
    } else {
        $assetIds = [$id];
    }

    // Build placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($assetIds), '?'));
    $types = str_repeat('i', count($assetIds));

    $sql = "
        SELECT 
            e.id,
            e.asset_id,
            e.event_type,
            e.ref_table,
            e.ref_id,
            e.from_employee_id,
            fe.name AS from_employee_name,
            e.to_employee_id,
            te.name AS to_employee_name,
            e.from_office_id,
            fo.office_name AS from_office_name,
            e.to_office_id,
            toff.office_name AS to_office_name,
            e.notes,
            e.created_at
        FROM asset_lifecycle_events e
        LEFT JOIN employees fe ON fe.employee_id = e.from_employee_id
        LEFT JOIN employees te ON te.employee_id = e.to_employee_id
        LEFT JOIN offices fo ON fo.id = e.from_office_id
        LEFT JOIN offices toff ON toff.id = e.to_office_id
        WHERE e.asset_id IN ($placeholders)
        ORDER BY e.created_at DESC, e.id DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare query']);
        exit;
    }

    // Bind dynamic IN values
    $stmt->bind_param($types, ...$assetIds);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Resolve dynamic office placeholders in notes for display (handles historical rows)
        $resolvedNotes = resolveLifecycleNotes(
            $row['notes'] ?? '',
            $row['from_office_id'] ?? null,
            $row['to_office_id'] ?? null
        );

        $events[] = [
            'id' => (int)$row['id'],
            'asset_id' => (int)$row['asset_id'],
            'event_type' => $row['event_type'],
            'ref_table' => $row['ref_table'],
            'ref_id' => $row['ref_id'] !== null ? (int)$row['ref_id'] : null,
            'from_employee' => $row['from_employee_name'] ?? null,
            'to_employee' => $row['to_employee_name'] ?? null,
            'from_office' => $row['from_office_name'] ?? null,
            'to_office' => $row['to_office_name'] ?? null,
            'notes' => $resolvedNotes,
            'created_at' => $row['created_at'],
        ];
    }
    $stmt->close();

    echo json_encode([
        'events' => $events,
        'summary' => [
            'count' => count($events),
            'assets_count' => count(array_unique($assetIds)),
        ]
    ]);
} catch (Throwable $e) {
    error_log('OFFICE_ADMIN/get_asset_lifecycle error: ' . $e->getMessage());
    echo json_encode(['error' => 'Unexpected server error']);
}
