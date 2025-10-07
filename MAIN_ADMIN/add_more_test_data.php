<?php
require_once '../connect.php';

echo "Adding More Test Data to temp_iirup_items\n";
echo "==========================================\n\n";

// Add more test items
$test_items = [
    [
        'asset_id' => 82,
        'date_acquired' => '2024-03-15',
        'particulars' => 'Test Desktop Computer',
        'property_no' => 'PROP-003',
        'quantity' => 1,
        'unit_cost' => 35000.00,
        'office' => 'IT Department',
        'code' => 'COMP-002'
    ],
    [
        'asset_id' => 83,
        'date_acquired' => '2024-04-20',
        'particulars' => 'Test Printer',
        'property_no' => 'PROP-004',
        'quantity' => 1,
        'unit_cost' => 15000.00,
        'office' => 'Admin Office',
        'code' => 'PRNT-001'
    ],
    [
        'asset_id' => 84,
        'date_acquired' => '2024-05-10',
        'particulars' => 'Test Monitor',
        'property_no' => 'PROP-005',
        'quantity' => 1,
        'unit_cost' => 8000.00,
        'office' => 'IT Department',
        'code' => 'MNTR-001'
    ]
];

$stmt = $conn->prepare("INSERT INTO temp_iirup_items (asset_id, date_acquired, particulars, property_no, quantity, unit_cost, office, code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$inserted = 0;
foreach ($test_items as $item) {
    $stmt->bind_param("isssidss", 
        $item['asset_id'],
        $item['date_acquired'],
        $item['particulars'],
        $item['property_no'],
        $item['quantity'],
        $item['unit_cost'],
        $item['office'],
        $item['code']
    );
    
    if ($stmt->execute()) {
        echo "✓ Inserted: " . $item['particulars'] . "\n";
        $inserted++;
    } else {
        echo "❌ Failed to insert: " . $item['particulars'] . " - " . $stmt->error . "\n";
    }
}

$stmt->close();

echo "\nInserted $inserted additional test items.\n";

// Show current total
$result = $conn->query("SELECT COUNT(*) as total FROM temp_iirup_items");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total temp items now: " . $row['total'] . "\n";
}

echo "\nNow test the IIRUP form - it should show " . ($inserted + 1) . " rows with pre-populated data!\n";
?>
