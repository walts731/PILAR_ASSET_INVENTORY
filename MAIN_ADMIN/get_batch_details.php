<?php
/**
 * Get Batch Details
 * Returns detailed information about a specific batch
 */

require_once '../../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['batch_id'])) {
    echo 'Unauthorized access';
    exit();
}

$batch_id = (int)$_GET['batch_id'];

$sql = "SELECT b.*, a.asset_name, c.category_name, u.fullname as created_by_name
        FROM batches b
        LEFT JOIN assets a ON b.asset_id = a.id
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN users u ON b.created_by = u.id
        WHERE b.id = $batch_id";

$result = mysqli_query($conn, $sql);

if ($batch = mysqli_fetch_assoc($result)) {
    // Get batch items
    $items_sql = "SELECT * FROM batch_items WHERE batch_id = $batch_id ORDER BY item_number";
    $items_result = mysqli_query($conn, $items_sql);

    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<h6>Batch Information</h6>";
    echo "<table class='table table-sm'>";
    echo "<tr><th>Batch Number:</th><td>{$batch['batch_number']}</td></tr>";
    echo "<tr><th>Batch Name:</th><td>{$batch['batch_name']}</td></tr>";
    echo "<tr><th>Asset:</th><td>{$batch['asset_name']}</td></tr>";
    echo "<tr><th>Category:</th><td>{$batch['category_name']}</td></tr>";
    echo "<tr><th>Batch Size:</th><td>{$batch['batch_size']}</td></tr>";
    echo "<tr><th>Unit Cost:</th><td>₱" . number_format($batch['unit_cost'], 2) . "</td></tr>";
    echo "<tr><th>Total Value:</th><td>₱" . number_format($batch['total_value'], 2) . "</td></tr>";
    echo "<tr><th>Quality Status:</th><td><span class='badge bg-" .
         (match($batch['quality_status']) {
             'approved' => 'success',
             'rejected' => 'danger',
             'quarantined' => 'warning',
             default => 'secondary'
         }) . "'>" . ucfirst($batch['quality_status']) . "</span></td></tr>";
    echo "<tr><th>Created By:</th><td>{$batch['created_by_name']}</td></tr>";
    echo "<tr><th>Created At:</th><td>" . date('M d, Y H:i', strtotime($batch['created_at'])) . "</td></tr>";
    echo "</table>";
    echo "</div>";

    echo "<div class='col-md-6'>";
    echo "<h6>Additional Information</h6>";
    echo "<table class='table table-sm'>";
    echo "<tr><th>Supplier:</th><td>" . ($batch['supplier'] ?: 'N/A') . "</td></tr>";
    echo "<tr><th>Manufacturer:</th><td>" . ($batch['manufacturer'] ?: 'N/A') . "</td></tr>";
    echo "<tr><th>Lot Number:</th><td>" . ($batch['lot_number'] ?: 'N/A') . "</td></tr>";
    echo "<tr><th>Manufacture Date:</th><td>" . ($batch['manufacture_date'] ? date('M d, Y', strtotime($batch['manufacture_date'])) : 'N/A') . "</td></tr>";
    echo "<tr><th>Expiry Date:</th><td>" . ($batch['expiry_date'] ? date('M d, Y', strtotime($batch['expiry_date'])) : 'N/A') . "</td></tr>";
    echo "<tr><th>Storage Location:</th><td>" . ($batch['storage_location'] ?: 'N/A') . "</td></tr>";
    echo "<tr><th>Notes:</th><td>" . ($batch['notes'] ?: 'N/A') . "</td></tr>";
    echo "</table>";
    echo "</div>";
    echo "</div>";

    echo "<div class='row mt-4'>";
    echo "<div class='col-12'>";
    echo "<h6>Batch Items</h6>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-sm table-striped'>";
    echo "<thead><tr><th>Item #</th><th>Serial #</th><th>Status</th><th>Location</th><th>QR Code</th></tr></thead>";
    echo "<tbody>";

    while ($item = mysqli_fetch_assoc($items_result)) {
        echo "<tr>";
        echo "<td>{$item['item_number']}</td>";
        echo "<td>" . ($item['serial_number'] ?: 'N/A') . "</td>";
        echo "<td><span class='badge bg-" .
             (match($item['status']) {
                 'available' => 'success',
                 'borrowed' => 'warning',
                 'in_use' => 'info',
                 'damaged' => 'danger',
                 'expired' => 'secondary',
                 default => 'secondary'
             }) . "'>" . ucfirst(str_replace('_', ' ', $item['status'])) . "</span></td>";
        echo "<td>" . ($item['current_location'] ?: 'N/A') . "</td>";
        echo "<td>";
        if ($item['qr_code']) {
            echo "<img src='../../img/qrcodes/{$item['qr_code']}' alt='QR Code' style='width: 50px; height: 50px;'>";
        } else {
            echo "N/A";
        }
        echo "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div class='alert alert-danger'>Batch not found.</div>";
}

mysqli_close($conn);
?>
