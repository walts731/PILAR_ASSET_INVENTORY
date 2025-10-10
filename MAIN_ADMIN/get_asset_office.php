<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$assetId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($assetId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid asset id']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT a.office_id, o.office_name FROM assets a LEFT JOIN offices o ON o.id = a.office_id WHERE a.id = ? LIMIT 1");
    $stmt->bind_param('i', $assetId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && ($row = $res->fetch_assoc())) {
        echo json_encode([
            'office_id' => $row['office_id'] ?? null,
            'office_name' => $row['office_name'] ?? ''
        ]);
    } else {
        echo json_encode(['office_id' => null, 'office_name' => '']);
    }
    $stmt->close();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
