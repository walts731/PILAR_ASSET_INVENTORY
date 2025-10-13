<?php
require_once '../connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT inventory_id, classification_type, item_description, nature_occupancy, location, date_constructed_acquired_manufactured, property_no_or_reference, acquisition_cost, market_appraisal_insurable_interest, date_of_appraisal, remarks, additional_image FROM infrastructure_inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Summary header
        echo "<div class='d-flex align-items-start justify-content-between mb-2'>";
        echo "  <div>";
        echo "    <div class='h6 mb-1'><i class='bi bi-building me-1 text-primary'></i> Infrastructure Details</div>";
        echo "    <div class='text-muted small'>Comprehensive information and attributes</div>";
        echo "  </div>";
        echo "</div>";

        // Layout container
        echo "<div class='row g-3'>";

        // Left column: Details
        echo "  <div class='col-12 col-lg-7'>";
        echo "    <div class='border rounded-3 p-3 h-100'>";
        echo "      <div class='row g-2'>";
        echo "        <div class='col-12'>";
        echo "          <div class='small text-muted'>Classification/Type</div>";
        echo "          <div class='fw-semibold'>" . htmlspecialchars($row['classification_type']) . "</div>";
        echo "        </div>";
        echo "        <div class='col-12'>";
        echo "          <div class='small text-muted'>Item Description</div>";
        echo "          <div>" . htmlspecialchars($row['item_description']) . "</div>";
        echo "        </div>";
        echo "        <div class='col-md-6'>";
        echo "          <div class='small text-muted'>Nature of Occupancy</div>";
        echo "          <div>" . htmlspecialchars($row['nature_occupancy']) . "</div>";
        echo "        </div>";
        echo "        <div class='col-md-6'>";
        echo "          <div class='small text-muted'>Location</div>";
        echo "          <div>" . htmlspecialchars($row['location']) . "</div>";
        echo "        </div>";
        $d_cam = !empty($row['date_constructed_acquired_manufactured']) ? date('M Y', strtotime($row['date_constructed_acquired_manufactured'])) : '';
        echo "        <div class='col-md-6'>";
        echo "          <div class='small text-muted'>Constructed/Acquired/Manufactured</div>";
        echo "          <div>" . ($d_cam !== '' ? htmlspecialchars($d_cam) : "<span class='text-muted'>N/A</span>") . "</div>";
        echo "        </div>";
        echo "        <div class='col-md-6'>";
        echo "          <div class='small text-muted'>Property No. / Reference</div>";
        echo "          <div>" . (!empty($row['property_no_or_reference']) ? htmlspecialchars($row['property_no_or_reference']) : "<span class='text-muted'>N/A</span>") . "</div>";
        echo "        </div>";
        $acq_cost = is_numeric($row['acquisition_cost']) ? '₱ ' . number_format((float)$row['acquisition_cost'], 2) : htmlspecialchars($row['acquisition_cost']);
        echo "        <div class='col-md-6'>";
        echo "          <div class='small text-muted'>Acquisition Cost</div>";
        echo "          <div>" . (!empty($acq_cost) ? $acq_cost : "<span class='text-muted'>N/A</span>") . "</div>";
        echo "        </div>";
        $app_val = is_numeric($row['market_appraisal_insurable_interest']) ? '₱ ' . number_format((float)$row['market_appraisal_insurable_interest'], 2) : htmlspecialchars($row['market_appraisal_insurable_interest']);
        echo "        <div class='col-md-6'>";
        echo "          <div class='small text-muted'>Market/Appraisal Value</div>";
        echo "          <div>" . (!empty($app_val) ? $app_val : "<span class='text-muted'>N/A</span>") . "</div>";
        echo "        </div>";
        $d_app = !empty($row['date_of_appraisal']) ? date('Y', strtotime($row['date_of_appraisal'])) : '';
        echo "        <div class='col-md-6'>";
        echo "          <div class='small text-muted'>Date of Appraisal</div>";
        echo "          <div>" . ($d_app !== '' ? htmlspecialchars($d_app) : "<span class='text-muted'>N/A</span>") . "</div>";
        echo "        </div>";
        echo "        <div class='col-12'>";
        echo "          <div class='small text-muted'>Remarks</div>";
        echo "          <div>" . (!empty($row['remarks']) ? nl2br(htmlspecialchars($row['remarks'])) : "<span class='text-muted'>N/A</span>") . "</div>";
        echo "        </div>";
        echo "      </div>";
        echo "    </div>";
        echo "  </div>";

        // Right column: Images card
        echo "  <div class='col-12 col-lg-5'>";
        echo "    <div class='border rounded-3 p-3 h-100'>";
        echo "      <div class='d-flex align-items-center justify-content-between mb-2'>";
        echo "        <h6 class='mb-0'><i class='bi bi-images me-1 text-secondary'></i> Images</h6>";
        echo "      </div>";
        echo "      <div class='row g-3'>";

        // Decode JSON array of images
        $images = [];
        if (!empty($row['additional_image'])) {
            $images = json_decode($row['additional_image'], true);
            if (!is_array($images)) {
                $images = [];
            }
        }

        // Display images if available
        if (!empty($images)) {
            foreach ($images as $index => $imagePath) {
                if (!empty($imagePath)) {
                    $src = htmlspecialchars($imagePath);
                    echo "        <div class='col-6'>";
                    echo "          <a href='" . $src . "' target='_blank' class='d-block' title='Open image " . ($index + 1) . " in new tab'>";
                    echo "            <img src='" . $src . "' class='img-fluid rounded border' alt='Image " . ($index + 1) . "' style='object-fit:cover;width:100%;height:150px;'>";
                    echo "          </a>";
                    echo "        </div>";
                }
            }
        } else {
            echo "        <div class='col-12 text-center text-muted'>";
            echo "          <i class='bi bi-image fs-1 mb-2'></i>";
            echo "          <div>No images available</div>";
            echo "        </div>";
        }

        echo "      </div>";
        echo "    </div>";
        echo "  </div>";

        echo "</div>"; // end row
    } else {
        echo "<div class='text-danger'>No record found.</div>";
    }
    $stmt->close();
}
