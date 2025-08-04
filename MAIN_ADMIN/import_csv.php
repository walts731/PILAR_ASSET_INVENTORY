<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');

    if ($handle === false) {
        die("Error opening CSV file.");
    }

    // Skip header
    fgetcsv($handle);

    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        $description   = mysqli_real_escape_string($conn, trim($row[0]));
        $category_name = mysqli_real_escape_string($conn, trim($row[1]));
        $quantity      = (int)trim($row[2]);
        $unit          = mysqli_real_escape_string($conn, trim($row[3]));
        $value         = (float)trim($row[4]);
        $office_name   = mysqli_real_escape_string($conn, trim($row[5]));
        $type          = mysqli_real_escape_string($conn, trim($row[6]));
        $status        = 'Available';
        $red_tagged    = 0;
        $acquired      = date('Y-m-d');

        // Get category_id from category_name
        $cat_query = "SELECT id FROM categories WHERE category_name = '$category_name' LIMIT 1";
        $cat_result = mysqli_query($conn, $cat_query);
        $category_row = mysqli_fetch_assoc($cat_result);
        if (!$category_row) {
            echo "Category not found: $category_name<br>";
            continue;
        }
        $category_id = (int)$category_row['id'];

        // Get office_id from office_name
        $office_query = "SELECT id FROM offices WHERE office_name = '$office_name' LIMIT 1";
        $office_result = mysqli_query($conn, $office_query);
        $office_row = mysqli_fetch_assoc($office_result);
        if (!$office_row) {
            echo "Office not found: $office_name<br>";
            continue;
        }
        $office_id = (int)$office_row['id'];

        // Insert asset
        $insert_sql = "
            INSERT INTO assets 
            (category, description, quantity, unit, value, status, office_id, type, red_tagged, acquisition_date, last_updated)
            VALUES 
            ($category_id, '$description', $quantity, '$unit', $value, '$status', $office_id, '$type', $red_tagged, '$acquired', '$acquired')
        ";

        if (mysqli_query($conn, $insert_sql)) {
            $asset_id = mysqli_insert_id($conn);

            // Generate QR code
            $qr_filename = $asset_id . '.png';
            $qr_path = '../img/' . $qr_filename;
            QRcode::png((string)$asset_id, $qr_path, QR_ECLEVEL_L, 4);

            // Update asset with QR code
            $update_sql = "UPDATE assets SET qr_code = '$qr_filename' WHERE id = $asset_id";
            mysqli_query($conn, $update_sql);
        } else {
            echo "Failed to insert asset: " . mysqli_error($conn) . "<br>";
        }
    }

    fclose($handle);
    header("Location: inventory.php?import=success");
    exit();
} else {
    echo "Invalid request.";
}
?>
