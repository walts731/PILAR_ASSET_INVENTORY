<?php
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

// Fetch description + unit cost + quantity from assets
$description_details = [];
$result = $conn->query("SELECT description, value AS unit_cost, quantity FROM assets");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $description = $row['description'];
        $description_details[$description] = [
            'unit_cost' => $row['unit_cost'],
            'quantity' => $row['quantity']
        ];
    }
}
?>


<div class="card mt-4">
    <div class="card-body">
        <!-- Inventory Custodian Slip Heading -->
        <h5 class="text-center fw-bold text-uppercase mb-4">Inventory Custodian Slip</h5>

        <form method="post" action="save_ics.php" enctype="multipart/form-data">
            <div class="mb-3">
                <?php if (!empty($ics_data['header_image'])): ?>
                    <img src="<?= $ics_data['header_image'] ?>" height="60" class="mb-2"><br>
                <?php endif; ?>
            </div>

            <div class="row mb-3">
                <!-- ENTITY NAME -->
                <div class="col-6">
                    <label class="form-label fw-semibold">ENTITY NAME</label>
                    <input type="text" class="form-control" name="entity_name" value="<?= htmlspecialchars($ics_data['entity_name']) ?>">
                </div>
            </div>

            <div class="row">
                <!-- FUND CLUSTER -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">FUND CLUSTER</label>
                    <input type="text" class="form-control" name="fund_cluster" value="<?= htmlspecialchars($ics_data['fund_cluster']) ?>">
                </div>

                <!-- ICS NO -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ICS NO.</label>
                    <input type="text" class="form-control" name="ics_no" value="<?= htmlspecialchars($ics_data['ics_no']) ?>">
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
                                    <option value="" disabled selected>Select unit</option>
                                    <?php foreach ($unit_options as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit) ?>"><?= htmlspecialchars($unit) ?></option>
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
                                <input type="number" class="form-control text-end" step="0.01" name="unit_cost[]" style="padding-left: 1.5rem;">
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

                            <td>
                                <input type="text" class="form-control description-field" name="description[]" list="descriptionList" placeholder="Type or search...">
                                <datalist id="descriptionList">
                                    <?php foreach ($description_details as $desc => $detail): ?>
                                        <option value="<?= htmlspecialchars($desc) ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
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
                            value="<?= htmlspecialchars($ics_data['received_from_name']) ?>"
                            placeholder="Enter name"
                            style="text-decoration:underline;">
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="received_by_name"
                            class="form-control text-center fw-bold"
                            value="<?= htmlspecialchars($ics_data['received_by_name']) ?>"
                            placeholder="Enter name"
                            style="text-decoration:underline;">
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <input type="text" name="received_from_position"
                            class="form-control text-center"
                            value="<?= htmlspecialchars($ics_data['received_from_position']) ?>"
                            placeholder="Enter position">
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="received_by_position"
                            class="form-control text-center"
                            value="<?= htmlspecialchars($ics_data['received_by_position']) ?>"
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
                            value="<?= !empty($ics_data['created_at']) ? htmlspecialchars(date('Y-m-d', strtotime($ics_data['created_at']))) : date('Y-m-d') ?>">
                    </td>
                    <td style="text-align:center;">
                        <input type="date" name="received_by_date"
                            class="form-control text-center"
                            value="<?= !empty($ics_data['created_at']) ? htmlspecialchars(date('Y-m-d', strtotime($ics_data['created_at']))) : date('Y-m-d') ?>">
                    </td>
                </tr>
            </table>

            <button type="submit" class="btn btn-success mt-3">Save ICS</button>
        </form>

        <!-- Duplicate Asset Modal -->
        <div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="duplicateModalLabel">Duplicate Asset</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        This asset has already been selected in another row. Please choose a different asset.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.getElementById('ics-items-body');
        const addRowBtn = document.getElementById('addRowBtn');
        const grandTotalField = document.getElementById('grandTotal');
        const duplicateModal = new bootstrap.Modal(document.getElementById('duplicateModal'));

        const descriptionMap = <?= json_encode($description_details) ?>;

        function updateGrandTotal() {
            let sum = 0;
            document.querySelectorAll('input[name="total_cost[]"]').forEach(input => {
                sum += parseFloat(input.value) || 0;
            });
            grandTotalField.value = sum.toFixed(2);
        }

        function isDuplicate(value, currentInput) {
            let isDup = false;
            document.querySelectorAll('input[name="description[]"]').forEach(input => {
                if (input !== currentInput && input.value.trim() === value.trim() && value.trim() !== "") {
                    isDup = true;
                }
            });
            return isDup;
        }

        tableBody.addEventListener('input', function(event) {
            const target = event.target;
            const row = target.closest('tr');
            if (!row) return;

            const quantityInput = row.querySelector('input[name="quantity[]"]');
            const unitCostInput = row.querySelector('input[name="unit_cost[]"]');
            const totalCostField = row.querySelector('input[name="total_cost[]"]');

            if (target.name === "description[]") {
                const selectedDesc = target.value;

                // Check for duplicate before proceeding
                if (isDuplicate(selectedDesc, target)) {
                    target.value = ''; // Clear the field
                    duplicateModal.show(); // Show warning modal
                    return;
                }

                if (descriptionMap[selectedDesc]) {
                    const {
                        unit_cost,
                        quantity
                    } = descriptionMap[selectedDesc];
                    unitCostInput.value = unit_cost;
                    quantityInput.max = quantity;
                    quantityInput.placeholder = `Max: ${quantity}`;
                }
            }

            const quantity = parseFloat(quantityInput?.value) || 0;
            const unitCost = parseFloat(unitCostInput?.value) || 0;
            totalCostField.value = (quantity * unitCost).toFixed(2);

            if (quantityInput.max && quantity > parseFloat(quantityInput.max)) {
                quantityInput.setCustomValidity("Quantity exceeds available stock.");
                quantityInput.reportValidity();
            } else {
                quantityInput.setCustomValidity("");
            }

            updateGrandTotal();
        });

        addRowBtn.addEventListener('click', function() {
            const firstRow = tableBody.querySelector('tr');
            const newRow = firstRow.cloneNode(true);
            newRow.querySelectorAll('input, select').forEach(el => el.value = '');
            tableBody.appendChild(newRow);
        });

        updateGrandTotal();
    });
</script>