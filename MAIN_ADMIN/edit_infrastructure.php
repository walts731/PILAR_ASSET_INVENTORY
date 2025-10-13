<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_POST['update_inventory'])) {
    $inventory_id = $_POST['inventory_id'];
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

    // Get current images to potentially merge with new ones
    $current_images = [];
    $stmt = $conn->prepare("SELECT additional_image FROM infrastructure_inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $stmt->bind_result($current_images_json);
    if ($stmt->fetch()) {
        if (!empty($current_images_json)) {
            $current_images = json_decode($current_images_json, true) ?? [];
        }
    }
    $stmt->close();

    // Handle new image uploads (limited to 4 total)
    $new_images = [];
    if (!empty($_FILES["additional_images"]["name"][0])) {
        $fileCount = count($_FILES["additional_images"]["name"]);
        $maxNewImages = min($fileCount, 4 - count($current_images)); // Ensure total doesn't exceed 4

        for ($i = 0; $i < $maxNewImages; $i++) {
            if (!empty($_FILES["additional_images"]["name"][$i])) {
                $fileTmp = $_FILES["additional_images"]["tmp_name"][$i];
                $fileName = time() . "_edit_" . ($i + 1) . "_" . basename($_FILES["additional_images"]["name"][$i]);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $new_images[] = $targetPath;
                }
            }
        }
    }

    // Merge current and new images (limited to 4 total)
    $all_images = array_merge($current_images, $new_images);
    $all_images = array_slice($all_images, 0, 4); // Ensure max 4 images
    $imagesJson = !empty($all_images) ? json_encode($all_images) : null;

    // Update the record
    $stmt = $conn->prepare("UPDATE infrastructure_inventory SET
        classification_type = ?, item_description = ?, nature_occupancy = ?, location = ?,
        date_constructed_acquired_manufactured = ?, property_no_or_reference = ?,
        acquisition_cost = ?, market_appraisal_insurable_interest = ?, date_of_appraisal = ?,
        remarks = ?, additional_image = ?
        WHERE inventory_id = ?");

    $stmt->bind_param("ssssssdsssi",
        $classification_type, $item_description, $nature_occupancy, $location,
        $date_constructed, $property_no, $acquisition_cost, $market_appraisal,
        $date_of_appraisal, $remarks, $imagesJson, $inventory_id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Infrastructure record updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
    header("Location: infrastructure_inventory.php");
    exit();
}

// Handle AJAX request to get infrastructure data for editing
if (isset($_GET['id'])) {
    $inventory_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM infrastructure_inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Record not found']);
    }

    $stmt->close();
    exit();
}
?>
