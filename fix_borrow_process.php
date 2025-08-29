<?php
require_once 'connect.php';

// Fix the borrow_requests table data
echo "Fixing borrow_requests data...\n";

// Update status from 'denied' to 'rejected'
$update_status = $conn->query("UPDATE borrow_requests SET status = 'rejected' WHERE status = 'denied'");
if ($update_status) {
    echo "✓ Updated 'denied' status to 'rejected'\n";
} else {
    echo "✗ Error updating status: " . $conn->error . "\n";
}

// Fix requests with quantity 0 by setting them to 1
$fix_quantity = $conn->query("UPDATE borrow_requests SET quantity = 1 WHERE quantity <= 0");
if ($fix_quantity) {
    $affected = $conn->affected_rows;
    echo "✓ Fixed $affected requests with invalid quantity\n";
} else {
    echo "✗ Error fixing quantities: " . $conn->error . "\n";
}

// Update status from 'approved' to 'borrowed' where appropriate
$update_to_borrowed = $conn->query("
    UPDATE borrow_requests br
    JOIN assets a ON br.asset_id = a.id
    SET br.status = 'borrowed'
    WHERE br.status = 'approved' 
    AND a.quantity = 0
");
if ($update_to_borrowed) {
    $affected = $conn->affected_rows;
    echo "✓ Updated $affected requests from 'approved' to 'borrowed'\n";
} else {
    echo "✗ Error updating to borrowed status: " . $conn->error . "\n";
}

echo "Data fix completed.\n";
?>
