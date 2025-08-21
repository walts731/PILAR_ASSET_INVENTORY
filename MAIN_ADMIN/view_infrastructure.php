<?php
require_once '../connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT inventory_id, classification_type, item_description, nature_occupancy, location, date_constructed_acquired_manufactured, property_no_or_reference, acquisition_cost, market_appraisal_insurable_interest, date_of_appraisal, remarks, image_1, image_2, image_3, image_4 FROM infrastructure_inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo "<table class='table table-bordered'>";
        echo "<tr><th>Classification/Type</th><td>" . htmlspecialchars($row['classification_type']) . "</td></tr>";
        echo "<tr><th>Item Description</th><td>" . htmlspecialchars($row['item_description']) . "</td></tr>";
        echo "<tr><th>Nature Occupancy</th><td>" . htmlspecialchars($row['nature_occupancy']) . "</td></tr>";
        echo "<tr><th>Location</th><td>" . htmlspecialchars($row['location']) . "</td></tr>";
        echo "<tr><th>Date Constructed/Acquired/Manufactured</th><td>" . htmlspecialchars($row['date_constructed_acquired_manufactured']) . "</td></tr>";
        echo "<tr><th>Property No./Other Reference</th><td>" . htmlspecialchars($row['property_no_or_reference']) . "</td></tr>";
        echo "<tr><th>Acquisition Cost</th><td>" . htmlspecialchars($row['acquisition_cost']) . "</td></tr>";
        echo "<tr><th>Market/Appraisal Value</th><td>" . htmlspecialchars($row['market_appraisal_insurable_interest']) . "</td></tr>";
        echo "<tr><th>Date of Appraisal</th><td>" . htmlspecialchars($row['date_of_appraisal']) . "</td></tr>";
        echo "<tr><th>Remarks</th><td>" . htmlspecialchars($row['remarks']) . "</td></tr>";
        echo "</table>";

        // Display images if available
        echo "<h6 class='mt-3'>Images</h6>";
        echo "<div class='row'>";
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($row["image_$i"])) {
                echo "<div class='col-md-3 mb-3'>";
                echo "<a href='" . htmlspecialchars($row["image_$i"]) . "' target='_blank'>";
                echo "<img src='" . htmlspecialchars($row["image_$i"]) . "' class='img-fluid rounded border' alt='Image $i'>";
                echo "</a>";
                echo "</div>";
            }
        }
        echo "</div>";
    } else {
        echo "<div class='text-danger'>No record found.</div>";
    }
    $stmt->close();
}
?>
