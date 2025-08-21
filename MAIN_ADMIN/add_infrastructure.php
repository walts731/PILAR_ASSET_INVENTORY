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

    // Handle 4 image inputs
    $images = [null, null, null, null];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_FILES["image_$i"]["name"])) {
            $fileTmp = $_FILES["image_$i"]["tmp_name"];
            $fileName = time() . "_$i_" . basename($_FILES["image_$i"]["name"]);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $images[$i - 1] = $targetPath;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO infrastructure_inventory 
        (classification_type, item_description, nature_occupancy, location, 
        date_constructed_acquired_manufactured, property_no_or_reference, 
        acquisition_cost, market_appraisal_insurable_interest, date_of_appraisal, 
        remarks, image_1, image_2, image_3, image_4) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssdsssssss",
        $classification_type, $item_description, $nature_occupancy, $location,
        $date_constructed, $property_no, $acquisition_cost, $market_appraisal,
        $date_of_appraisal, $remarks,
        $images[0], $images[1], $images[2], $images[3]
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
