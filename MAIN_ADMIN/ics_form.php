<?php
require_once '../connect.php';

// Start session to access flash messages
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Get form_id from URL, default to null if not provided
$form_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch the latest ICS data (remove conditional by $ics_id)
$sql = "SELECT id, header_image, entity_name, fund_cluster, ics_no, 
               received_from_name, received_from_position, 
               received_by_name, received_by_position, created_at 
        FROM ics_form 
        ORDER BY id DESC 
        LIMIT 1"; // ← Gets the most recent record
$result = $conn->query($sql);

$ics_data = [
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'ics_no' => '',
    'received_from_name' => '',
    'received_from_position' => '',
    'received_by_name' => '',
    'received_by_position' => '',
    'created_at' => ''
];

if ($result && $result->num_rows > 0) {
    $ics_data = $result->fetch_assoc();
}

// Fetch unit options
$unit_options = [];
$result = $conn->query("SELECT unit_name FROM unit");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $unit_options[] = $row['unit_name'];
    }
}

// Autofill description data removed to enforce manual entry for all item fields




// Fetch office options
$office_options = [];
$result = $conn->query("SELECT id, office_name FROM offices");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $office_options[] = $row;
    }
}

function generateICSNo($conn)
{
    $year = date("Y");

    // Query the latest ics_no for this year
    $sql = "SELECT ics_no 
            FROM ics_form 
            WHERE ics_no LIKE 'ICS-$year-%' 
            ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        // Extract the last number
        $lastNo = intval(substr($row['ics_no'], -4));
        $nextNo = str_pad($lastNo + 1, 4, "0", STR_PAD_LEFT);
    } else {
        // First number of the year
        $nextNo = "0001";
    }

    return "ICS-$year-$nextNo";
}

// Generate the next ICS number
$new_ics_no = generateICSNo($conn);


?>
<?php if (!empty($_SESSION['flash'])): ?>
    <?php
        $flash = $_SESSION['flash'];
        // Normalize type to Bootstrap alert classes
        $type = isset($flash['type']) ? strtolower($flash['type']) : 'info';
        $allowed = ['primary','secondary','success','danger','warning','info','light','dark'];
        if (!in_array($type, $allowed, true)) { $type = 'info'; }
    ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message'] ?? 'Action completed.') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<!-- Top-right button -->
<div class="d-flex justify-content-end mb-3">
    <a href="saved_ics.php?id=<?= htmlspecialchars($form_id) ?>" class="btn btn-info">
        <i class="bi bi-folder-check"></i> View Saved ICS
    </a>
</div>
<div class="card mt-4">
    <div class="card-body">
        <!-- Inventory Custodian Slip Heading -->

        <form method="post" action="save_ics_items.php" enctype="multipart/form-data">
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">
            <!-- ICS Form ID from database -->
            <input type="hidden" name="ics_id" value="<?= htmlspecialchars($ics_data['id'] ?? '') ?>">
            <div class="mb-3 text-center">
              <?php if (!empty($ics_data['header_image'])): ?>
                  <img src="../img/<?= htmlspecialchars($ics_data['header_image']) ?>"
                      class="img-fluid mb-2"
                      style="max-width: 100%; height: auto; object-fit: contain;">

                  <!-- Hidden input ensures it gets submitted -->
                  <input type="hidden" name="header_image" value="<?= htmlspecialchars($ics_data['header_image']) ?>">
              <?php else: ?>
                  <p class="text-muted">No header image available</p>
              <?php endif; ?>
              <div class="mt-3 text-start">
                <label for="headerImageFile" class="form-label fw-semibold">Replace Header Image</label>
                <input type="file" class="form-control" id="headerImageFile" name="header_image_file" accept="image/*">
                <div class="form-text">Optional. Upload a new header image (JPG, PNG, or WEBP). This will replace the current image.</div>
              </div>
            </div>

            <div class="row mb-3">
                <!-- ENTITY NAME -->
                <div class="col-6">
                    <label class="form-label fw-semibold">ENTITY NAME</label>
                    <input type="text" class="form-control" name="entity_name" value="">
                </div>

                <!-- OFFICE -->
                <div class="col-6">
                    <label class="form-label fw-semibold">
                        DESTINATION <span style="color: red;">*</span>
                    </label>
                    <select class="form-select" name="office_id" required>
                        <option value="" disabled selected>Select office</option>
                        <option value="outside_lgu">Outside LGU</option> <!-- ✅ Static option -->
                        <?php foreach ($office_options as $office): ?>
                            <option value="<?= htmlspecialchars($office['id']) ?>">
                                <?= htmlspecialchars($office['office_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>


            <div class="row">
                <!-- FUND CLUSTER -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">FUND CLUSTER</label>
                    <input type="text" class="form-control" name="fund_cluster" value="">
                </div>

                <!-- ICS NO -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ICS NO.</label>
                    <input type="text" class="form-control" name="ics_no"
                        value="<?= htmlspecialchars($new_ics_no) ?>" readonly>
                </div>

            </div>

            <!-- Items Table -->
            <table class="table table-bordered text-center align-middle mt-3" id="icsTable">
                <thead>
                    <tr>
                        <th rowspan="2">QUANTITY</th>
                        <th rowspan="2">UNIT</th>
                        <th colspan="2">AMOUNT</th>
                        <th rowspan="2">DESCRIPTION</th>
                        <th rowspan="2">ITEM NO</th>
                        <th rowspan="2">ESTIMATED USEFUL LIFE</th>
                    </tr>
                    <tr>
                        <th>UNIT COST</th>
                        <th>TOTAL COST</th>
                    </tr>
                </thead>
                <tbody id="ics-items-body">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <tr>
                            <td><input type="number" class="form-control quantity-field" name="quantity[]" min="1"></td>
                            <td>
                                <select class="form-select" name="unit[]">
                                    <option value="" disabled>Select unit</option>
                                    <?php foreach ($unit_options as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit) ?>" <?= (strtolower($unit) === 'unit') ? 'selected' : '' ?>><?= htmlspecialchars($unit) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="position: relative;">
                                <span style="
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        color: inherit;
        font-size: 1rem;">₱</span>
                                <input type="number" class="form-control text-end" step="0.01" name="unit_cost[]" max="50000" style="padding-left: 1.5rem;">
                            </td>
                            <td style="position: relative;">
                                <span style="
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        color: inherit;
        font-size: 1rem;">₱</span>
                                <input type="number" class="form-control total_cost text-end" name="total_cost[]" step="0.01" readonly style="padding-left: 1.5rem;">
                            </td>

                            <td style="position: relative;">
                                <input type="text" class="form-control description-field"
                                    name="description[]"
                                    placeholder="Type description..."
                                    style="padding-right: 2rem;"> <!-- add right padding so X doesn't overlap -->
                                <button type="button"
                                    class="clear-description"
                                    style="
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            font-weight: bold;
            font-size: 1rem;
            line-height: 1;
            color: #888;
            cursor: pointer;
        ">&times;</button>
                            </td>

                            <td><input type="text" class="form-control" name="item_no[]"></td>
                            <td><input type="text" class="form-control" name="estimated_useful_life[]"></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold"></td>
                        <td style="position: relative;">
                            <span style="
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        color: inherit;
        font-size: 1rem;">₱</span>
                            <input type="number" id="grandTotal" class="form-control fw-bold text-end" readonly style="padding-left: 1.5rem;">
                        </td>

                        <td colspan="3" class="text-start">
                            <button type="button" id="addRowBtn" class="btn btn-primary btn-sm">+ Add Row</button>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Footer Section -->
            <table class="table table-borderless mt-5" style="width:100%; text-align:center; border-collapse: collapse;">
                <tr>
                    <td style="width:50%; text-align:left; font-weight:bold;">Received from:</td>
                    <td style="width:50%; text-align:left; font-weight:bold;">Received by:</td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <input type="text" name="received_from_name"
                            class="form-control text-center fw-bold"
                            value=""
                            placeholder="Enter name"
                            style="text-decoration:underline;">
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="received_by_name"
                            class="form-control text-center fw-bold"
                            value=""
                            placeholder="Enter name"
                            style="text-decoration:underline;">
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <input type="text" name="received_from_position"
                            class="form-control text-center"
                            value=""
                            placeholder="Enter position">
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="received_by_position"
                            class="form-control text-center"
                            value=""
                            placeholder="Enter position">
                    </td>
                </tr>
                <tr>
                    <td style="height:30px;"></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <input type="date" name="received_from_date"
                            class="form-control text-center"
                            value="">
                    </td>
                    <td style="text-align:center;">
                        <input type="date" name="received_by_date"
                            class="form-control text-center"
                            value="">
                    </td>
                </tr>
            </table>

            <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-send-check-fill"></i>Save</button>
        </form>
        <!-- Button to go to Saved ICS -->
        <div class="mt-3">
            <a href="saved_ics.php" class="btn btn-info">
                <i class="bi bi-folder-check"></i> View Saved ICS
            </a>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.getElementById('ics-items-body');
        const addRowBtn = document.getElementById('addRowBtn');
        const grandTotalField = document.getElementById('grandTotal');
        // Duplicate prevention and autofill removed; inputs are manual entry only

        function updateGrandTotal() {
            let sum = 0;
            document.querySelectorAll('input[name="total_cost[]"]').forEach(input => {
                sum += parseFloat(input.value) || 0;
            });
            grandTotalField.value = sum.toFixed(2);
        }

        // No duplicate checking; users may enter any values manually

        // Input handler (quantities, description selection, unit cost -> total)
        tableBody.addEventListener('input', function(event) {
            const target = event.target;
            const row = target.closest('tr');
            if (!row) return;

            const quantityInput = row.querySelector('input[name="quantity[]"]');
            const unitCostInput = row.querySelector('input[name="unit_cost[]"]');
            const totalCostField = row.querySelector('input[name="total_cost[]"]');
            const descriptionInput = row.querySelector('input[name="description[]"]');

            if (target.name === "unit_cost[]") {
                const val = parseFloat(target.value) || 0;
                if (val > 50000) {
                    target.value = 50000;
                    target.setCustomValidity("Unit cost cannot exceed ₱50,000.");
                    target.reportValidity();
                } else {
                    target.setCustomValidity("");
                }
            }

            // No description-driven autofill; users will manually enter all values

            const quantity = parseFloat(quantityInput?.value) || 0;
            const unitCost = parseFloat(unitCostInput?.value) || 0;
            if (totalCostField) totalCostField.value = (quantity * unitCost).toFixed(2);

            if (quantityInput?.max && quantity > parseFloat(quantityInput.max)) {
                quantityInput.setCustomValidity("Quantity exceeds available stock.");
                quantityInput.reportValidity();
            } else {
                quantityInput?.setCustomValidity("");
            }

            updateGrandTotal();
        });

        // Clear (×) button handler - uses event delegation and clears related fields
        tableBody.addEventListener('click', function(event) {
            if (!event.target.classList.contains('clear-description')) return;

            const btn = event.target;
            const row = btn.closest('tr');
            if (!row) return;

            const descriptionInput = row.querySelector('.description-field');
            const unitCostInput = row.querySelector('input[name="unit_cost[]"]');
            const totalCostField = row.querySelector('input[name="total_cost[]"]');
            const quantityInput = row.querySelector('input[name="quantity[]"]');

            if (descriptionInput) descriptionInput.value = '';
            if (unitCostInput) unitCostInput.value = '';
            if (totalCostField) totalCostField.value = '';
            if (quantityInput) {
                quantityInput.value = '';
                quantityInput.removeAttribute('max');
                quantityInput.placeholder = '';
                quantityInput.setCustomValidity("");
            }

            // Trigger input event to recalculate and keep behavior consistent
            if (descriptionInput) descriptionInput.dispatchEvent(new Event('input'));
            updateGrandTotal();

            // Optional: put focus back into the description field
            if (descriptionInput) descriptionInput.focus();
        });

        // Add row (clone)
        addRowBtn.addEventListener('click', function() {
            const firstRow = tableBody.querySelector('tr');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);

            // clear values in cloned inputs/selects
            newRow.querySelectorAll('input, select').forEach(el => {
                // keep attributes but reset values
                if (el.tagName.toLowerCase() === 'select') {
                    // default to 'unit' if available, else first option
                    let set = false;
                    for (let i = 0; i < el.options.length; i++) {
                        if (el.options[i].value.toLowerCase() === 'unit') {
                            el.selectedIndex = i;
                            set = true;
                            break;
                        }
                    }
                    if (!set) el.selectedIndex = 0;
                } else {
                    el.value = '';
                }
            });

            // specifically ensure total_cost cleared
            newRow.querySelectorAll('input.total_cost').forEach(i => i.value = '');

            tableBody.appendChild(newRow);
        });

        // initial total calc
        updateGrandTotal();
    });
</script>