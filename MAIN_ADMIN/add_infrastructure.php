<?php
require_once '../connect.php';
session_start();

if (isset($_POST['save_inventory'])) {
    $classification_type = $_POST['classification_type'];
    $item_description = $_POST['item_description'];
    $nature_occupancy = $_POST['nature_occupancy'];
    $location = $_POST['location'];
    $date_constructed = $_POST['date_constructed_acquired_manufactured'];
    $property_no = $_POST['property_no_or_reference'];
    $acquisition_cost = $_POST['acquisition_cost'];
    $market_appraisal = $_POST['market_appraisal_insurable_interest'];
    $date_of_appraisal = $_POST['date_of_appraisal'];
    $remarks = $_POST['remarks'];

    // Upload directory
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Handle multiple image uploads (limited to 4)
    $images = [];
    if (!empty($_FILES["additional_images"]["name"][0])) {
        $fileCount = count($_FILES["additional_images"]["name"]);
        $maxImages = min($fileCount, 4); // Limit to 4 images
        
        for ($i = 0; $i < $maxImages; $i++) {
            if (!empty($_FILES["additional_images"]["name"][$i])) {
                $fileTmp = $_FILES["additional_images"]["tmp_name"][$i];
                $fileName = time() . "_" . ($i + 1) . "_" . basename($_FILES["additional_images"]["name"][$i]);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $images[] = $targetPath;
                }
            }
        }
    }

    // Convert images array to JSON
    $imagesJson = !empty($images) ? json_encode($images) : null;

    $stmt = $conn->prepare("INSERT INTO infrastructure_inventory 
        (classification_type, item_description, nature_occupancy, location, 
        date_constructed_acquired_manufactured, property_no_or_reference, 
        acquisition_cost, market_appraisal_insurable_interest, date_of_appraisal, 
        remarks, additional_image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssdssss",
        $classification_type, $item_description, $nature_occupancy, $location,
        $date_constructed, $property_no, $acquisition_cost, $market_appraisal,
        $date_of_appraisal, $remarks, $imagesJson
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Inventory added successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: infrastructure_inventory.php");
    exit();
}
?>
