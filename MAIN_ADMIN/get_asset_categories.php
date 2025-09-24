<?php
/**
 * Get Asset Categories
 * Returns categories for a specific asset
 */

require_once '../../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['asset_id'])) {
    echo '<option value="">Select Category</option>';
    exit();
}

$asset_id = (int)$_GET['asset_id'];

$sql = "SELECT a.category, c.category_name
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        WHERE a.id = $asset_id";

$result = mysqli_query($conn, $sql);

if ($asset = mysqli_fetch_assoc($result)) {
    if ($asset['category']) {
        echo "<option value='{$asset['category']}'>{$asset['category_name']}</option>";
    } else {
        echo '<option value="">No category assigned</option>';
    }
} else {
    echo '<option value="">Asset not found</option>';
}

mysqli_close($conn);
?>
