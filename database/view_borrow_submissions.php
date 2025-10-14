<?php
require_once 'C:\xampp\htdocs\PILAR_ASSET_INVENTORY\connect.php';

// Display all borrow form submissions
$sql = "SELECT * FROM borrow_form_submissions ORDER BY submitted_at DESC";
$result = $conn->query($sql);

echo "<h1>Borrow Form Submissions</h1>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Submission #</th><th>Name</th><th>Date Borrowed</th><th>Return Date</th><th>Barangay</th><th>Contact</th><th>Items</th><th>Status</th><th>Submitted</th></tr>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $items = json_decode($row['items'], true);
        $items_display = '';
        foreach ($items as $item) {
            $items_display .= htmlspecialchars($item['thing']) . ' (Qty: ' . htmlspecialchars($item['qty']) . ')<br>';
        }

        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['submission_number']) . "</td>";
        echo "<td>" . htmlspecialchars($row['guest_name']) . "</td>";
        echo "<td>" . $row['date_borrowed'] . "</td>";
        echo "<td>" . $row['schedule_return'] . "</td>";
        echo "<td>" . htmlspecialchars($row['barangay']) . "</td>";
        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
        echo "<td>" . $items_display . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['submitted_at'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10'>No submissions found</td></tr>";
}
echo "</table>";

$conn->close();
?>
