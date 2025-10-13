<?php
require_once '../connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "
    SELECT
        inventory_id,
        classification_type,
        item_description,
        nature_occupancy,
        location,
        date_constructed_acquired_manufactured,
        property_no_or_reference,
        acquisition_cost,
        market_appraisal_insurable_interest,
        date_of_appraisal,
        remarks,
        additional_image
    FROM infrastructure_inventory
    WHERE inventory_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Format dates for display
        if ($row['date_constructed_acquired_manufactured']) {
            $row['date_constructed_acquired_manufactured_formatted'] = date("M d, Y", strtotime($row['date_constructed_acquired_manufactured']));
        }
        if ($row['date_of_appraisal']) {
            $row['date_of_appraisal_formatted'] = date("M d, Y", strtotime($row['date_of_appraisal']));
        }

        // Format currency values
        if ($row['acquisition_cost']) {
            $row['acquisition_cost_formatted'] = '₱' . number_format($row['acquisition_cost'], 2);
        }
        if ($row['market_appraisal_insurable_interest']) {
            $row['market_appraisal_insurable_interest_formatted'] = '₱' . number_format($row['market_appraisal_insurable_interest'], 2);
        }

        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Infrastructure record not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No ID provided']);
}

$conn->close();
?>
