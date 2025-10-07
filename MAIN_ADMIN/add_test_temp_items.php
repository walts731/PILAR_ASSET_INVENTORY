<?php
require_once '../connect.php';

echo "Adding Test Temp Items\n";
echo "======================\n\n";

// Sample test data
$test_items = [
    [
        'user_id' => 1, // Adjust this to match your user ID
        'session_id' => 'test_session_123',
        'asset_id' => 1, // Adjust to match existing asset
        'date_acquired' => '2024-01-15',
        'particulars' => 'Test Laptop Computer',
        'property_no' => 'PROP-001',
        'quantity' => 1,
        'unit_cost' => 25000.00,
        'office' => 'IT Department',
        'code' => 'COMP-001'
    ],
    [
        'user_id' => 1,
        'session_id' => 'test_session_123',
        'asset_id' => 2,
        'date_acquired' => '2024-02-20',
        'particulars' => 'Test Office Chair',
        'property_no' => 'PROP-002',
        'quantity' => 1,
        'unit_cost' => 5000.00,
        'office' => 'Admin Office',
        'code' => 'FURN-001'
    ]
];

// Insert test data
$stmt = $conn->prepare("INSERT INTO temp_iirup_items (user_id, session_id, asset_id, date_acquired, particulars, property_no, quantity, unit_cost, office, code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$inserted = 0;
foreach ($test_items as $item) {
    $stmt->bind_param("isissiisss", 
        $item['user_id'],
        $item['session_id'],
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

echo "\nInserted $inserted test items.\n";
echo "\nTo test:\n";
echo "1. Login to the system with user_id = 1\n";
echo "2. Open IIRUP form in browser\n";
echo "3. You should see temp items in the table\n";
echo "\nNote: You may need to update the session_id in the database to match your actual session.\n";

// Show current data
echo "\nCurrent temp_iirup_items data:\n";
$result = $conn->query("SELECT * FROM temp_iirup_items ORDER BY id DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, User: {$row['user_id']}, Session: {$row['session_id']}, Particulars: {$row['particulars']}\n";
    }
}
?>
