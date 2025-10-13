<?php
require_once '../connect.php';

$office_id = isset($_GET['office_id']) ? intval($_GET['office_id']) : 0;

if ($office_id > 0) {
    $stmt = $conn->prepare("
        SELECT a.id, a.description, a.quantity, a.value, a.unit
        FROM assets a
        WHERE a.office_id = ? AND a.type = 'consumable'
        ORDER BY a.description ASC
    ");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $consumables = array();
    while ($row = $result->fetch_assoc()) {
        $consumables[] = array(
            'id' => $row['id'],
            'description' => htmlspecialchars($row['description']),
            'quantity' => $row['quantity'],
            'value' => $row['value'],
            'unit' => htmlspecialchars($row['unit'])
        );
    }

    header('Content-Type: application/json');
    echo json_encode($consumables);
} else {
    echo json_encode(array());
}
?>
