<?php
require_once '../connect.php';
$form_id = $_GET['id'] ?? null;

$par_data = [
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'par_no' => '',
    'office_id' => ''
];

// Fetch PAR form data
if ($form_id) {
    $stmt = $conn->prepare("SELECT header_image, entity_name, fund_cluster, par_no, office_id, position_office_left, position_office_right FROM par_form WHERE form_id = ?");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $par_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch offices for dropdown
$offices = [];
$office_query = $conn->query("SELECT id, office_name FROM offices");
while ($row = $office_query->fetch_assoc()) {
    $offices[] = $row;
}

// Fetch description + unit cost + quantity from assets
$description_details = [];
$result = $conn->query("SELECT description, value AS unit_cost, quantity, acquisition_date, unit FROM assets");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $desc = $row['description'];
        $description_details[$desc] = [
            'unit_cost' => $row['unit_cost'],
            'quantity' => $row['quantity'],
            'acquisition_date' => $row['acquisition_date'],
            'unit' => $row['unit']
        ];
    }
}

// Fetch units for dropdown
$units = [];
$unit_query = $conn->query("SELECT id, unit_name FROM unit");
while ($row = $unit_query->fetch_assoc()) {
    $units[] = $row;
}
?>


<div class="container mt-3">

    <form method="post" action="save_par_form.php" enctype="multipart/form-data">
        <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

        <div class="mb-3 text-center">
    <?php if (!empty($par_data['header_image'])): ?>
        <img src="../SYSTEM_ADMIN/img/<?= htmlspecialchars($par_data['header_image']) ?>" 
             class="img-fluid mb-3" 
             style="max-width: 100%; height: auto; object-fit: contain;">
    <?php endif; ?>
</div>


<table class="table table-bordered align-middle text-start" style="table-layout: fixed;">
    <tbody>
        <!-- Office/Location Row -->
        <tr>
            <td colspan="2">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6 text-center">
                        <label class="form-label fw-semibold mb-0">Office/Location</label>
                        <select name="office_id" class="form-select text-center" required>
                            <option value="">Select Office</option>
                            <?php foreach ($offices as $office): ?>
                                <option value="<?= htmlspecialchars($office['id']) ?>"
                                    <?= ($office['id'] == $par_data['office_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($office['office_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </td>
        </tr>

        <!-- Entity Name and Blank -->
        <tr>
            <td>
                <label class="form-label fw-semibold mb-0">Entity Name</label>
                <input type="text" name="entity_name" class="form-control" 
                       value="<?= htmlspecialchars($par_data['entity_name']) ?>" required>
            </td>
            <td>
                <!-- Blank right cell -->
            </td>
        </tr>

        <!-- Fund Cluster and PAR No. -->
        <tr>
            <td>
                <label class="form-label fw-semibold mb-0">Fund Cluster</label>
                <input type="text" name="fund_cluster" class="form-control" 
                       value="<?= htmlspecialchars($par_data['fund_cluster']) ?>" required>
            </td>
            <td>
                <label class="form-label fw-semibold mb-0">PAR No.</label>
                <input type="text" name="par_no" class="form-control" 
                       value="<?= htmlspecialchars($par_data['par_no']) ?>" required>
            </td>
        </tr>
    </tbody>
</table>


        <!-- ITEM TABLE -->
        <table class="table align-middle text-center mt-4" style="table-layout: fixed; ">
            <thead>
                <tr>
                    <th>QUANTITY</th>
                    <th>UNIT</th>
                    <th style="width: 30%;">DESCRIPTION</th>
                    <th>PROPERTY NO</th>
                    <th>DATE ACQUIRED</th>
                    <th>UNIT PRICE</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody id="itemTableBody">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <tr>
                        <td><input type="number" name="items[<?= $i ?>][quantity]" class="form-control text-end" id="qtyInput<?= $i ?>" min="1"></td>
                        <td>
                            <select name="items[<?= $i ?>][unit]" class="form-select text-center" required>
                                <option value="">Select Unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= htmlspecialchars($unit['unit_name']) ?>"><?= htmlspecialchars($unit['unit_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="position-relative" style="width: 30%;">
                            <div class="input-group">
                                <input type="text" name="items[<?= $i ?>][description]" class="form-control form-control-lg" list="descriptionList" id="descInput<?= $i ?>">
                                <button type="button"
                                    class="btn p-0 m-0 border-0 bg-transparent"
                                    onclick="clearDescription(<?= $i ?>)"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                    <span class="badge rounded-circle bg-secondary text-white" style="font-size: 0.75rem;">×</span>
                                </button>
                            </div>
                        </td>

                        <td><input type="text" name="items[<?= $i ?>][property_no]" class="form-control"></td>
                        <td><input type="date" name="items[<?= $i ?>][date_acquired]" class="form-control" id="acqDate<?= $i ?>"></td>
                        <td style="position: relative;">
                            <span style="
                                            position: absolute;
                                            top: 50%;
                                            left: 10px;
                                            transform: translateY(-50%);
                                            pointer-events: none;
                                            color: inherit;
                                            font-size: 1rem;
                                        ">₱</span>
                            <input
                                type="number"
                                name="items[<?= $i ?>][unit_price]"
                                class="form-control text-end"
                                step="0.01"
                                id="unitCost<?= $i ?>"
                                style="padding-left: 1.5rem;">
                        </td>

                        <td style="position: relative;">
                            <span style="
                                            position: absolute;
                                            top: 50%;
                                            left: 10px;
                                            transform: translateY(-50%);
                                            pointer-events: none;
                                            color: inherit;
                                            font-size: 1rem;
                                        ">₱</span>
                            <input
                                type="number"
                                name="items[<?= $i ?>][amount]"
                                class="form-control text-end"
                                step="0.01"
                                id="amount<?= $i ?>"
                                readonly
                                style="padding-left: 1.5rem;">
                        </td>

                        <datalist id="descriptionList">
                            <?php foreach ($description_details as $desc => $details): ?>
                                <option value="<?= htmlspecialchars($desc) ?>"
                                    data-unit-cost="<?= htmlspecialchars($details['unit_cost']) ?>"
                                    data-quantity="<?= htmlspecialchars($details['quantity']) ?>"
                                    data-date="<?= htmlspecialchars($details['acquisition_date']) ?>"
                                    data-unit="<?= htmlspecialchars($details['unit']) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>


                    </tr>
                <?php endfor; ?>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold">Total:</td>
                    <td style="position: relative;">
                        <span style="
                                    position: absolute;
                                    top: 50%;
                                    left: 10px;
                                    transform: translateY(-50%);
                                    pointer-events: none;
                                    color: inherit;
                                    font-size: 1rem;
                                    ">₱</span>
                        <input
                            type="text"
                            id="totalAmount"
                            class="form-control text-end fw-bold"
                            readonly
                            style="padding-left: 1.5rem; width: 150px;">
                    </td>
                </tr>

            </tfoot>

            </tbody>

        </table>
        <button type="button" class="btn btn-outline-primary mt-2" onclick="addRow()">+ Add Item</button>

        <hr class="mt-5 mb-4">

        <div class="row mt-4 mb-3">
            <!-- Left: Received by -->
            <div class="col-md-6 text-center">
                <p class="fw-semibold">Received by:</p>
                <br><br>
                <p class="fw-semibold border-bottom border-dark d-inline-block" style="min-width: 250px;"></p>
                <p>Signature over Printed Name</p>

                <label for="position_office_left">Position / Office:</label>
                <input type="text" class="form-control text-center" name="position_office_left"
                    value="<?= htmlspecialchars($par_data['position_office_left'] ?? '') ?>">
                <br>
                <label>Date:</label>
                <input type="date" name="date_received" class="form-control"
                    value="<?= date('Y-d-m') ?>">
            </div>

            <!-- Right: Issued by -->
            <div class="col-md-6 text-center">
                <p class="fw-semibold">Issued by:</p>
                <br><br>
                <p class="fw-semibold border-bottom border-dark d-inline-block" style="min-width: 250px;"></p>
                <p>Signature over Printed Name</p>

                <label for="position_office_right">Position / Office:</label>
                <input type="text" class="form-control text-center" name="position_office_right"
                    value="<?= htmlspecialchars($par_data['position_office_right'] ?? '') ?>">
                <br>
                <label>Date:</label>
                <input type="date" name="date_received" class="form-control"
                    value="<?= date('Y-d-m') ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="bi bi-send-check-fill"></i>Save</button>
    </form>
</div>

<?php include 'modals/par_duplicate_modal.php' ?>
<script>
    let rowIndex = 5; // Start after the initial 5 rows
    let selectedDescriptions = new Set(); // 🔁 Track selected asset descriptions

    function addRow() {
        const tbody = document.getElementById('itemTableBody');
        const newRow = document.createElement('tr');

        newRow.innerHTML = `
        <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control text-end" id="qtyInput${rowIndex}"></td>
        <td>
            <select name="items[${rowIndex}][unit]" class="form-select text-center" required>
                <option value="">Select Unit</option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?= htmlspecialchars($unit['unit_name']) ?>"><?= htmlspecialchars($unit['unit_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="position-relative">
    <div class="input-group">
        <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-lg" list="descriptionList" id="descInput${rowIndex}">
        <button type="button"
                class="btn p-0 m-0 border-0 bg-transparent"
                onclick="clearDescription(${rowIndex})"
                style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
            <span class="badge rounded-circle bg-secondary text-white" style="font-size: 0.75rem;">×</span>
        </button>
    </div>
</td>

        <td><input type="text" name="items[${rowIndex}][property_no]" class="form-control"></td>
        <td><input type="date" name="items[${rowIndex}][date_acquired]" class="form-control" id="acqDate${rowIndex}"></td>
        <td style="position: relative; width: 150px;">
    <span style="
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        color: inherit;
        font-size: 1rem;">₱</span>
    <input
        type="number"
        name="items[${rowIndex}][unit_price]"
        class="form-control text-end"
        step="0.01"
        id="unitCost${rowIndex}"
        style="padding-left: 1.5rem; width: 100%;">
</td>

        <td style="position: relative; width: 150px;">
    <span style="
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        color: inherit;
        font-size: 1rem;">₱</span>
    <input
        type="number"
        name="items[${rowIndex}][amount]"
        class="form-control text-end fw-semibold"
        step="0.01"
        id="amount${rowIndex}"
        readonly
        style="padding-left: 1.5rem; width: 100%;">
</td>

        `;

        tbody.appendChild(newRow);

        attachRowEvents(rowIndex);
        updateTotalAmount();
        rowIndex++;
    }

    function attachRowEvents(i) {
        const descInput = document.getElementById('descInput' + i);
        const unitCostInput = document.getElementById('unitCost' + i);
        const qtyInput = document.getElementById('qtyInput' + i);
        const acqDateInput = document.getElementById('acqDate' + i);
        const amountInput = document.getElementById('amount' + i);
        const dataList = document.getElementById('descriptionList');

        // Auto-calculate amount
        qtyInput.addEventListener('input', calculateAmount);
        unitCostInput.addEventListener('input', calculateAmount);

        function calculateAmount() {
            const qty = parseFloat(qtyInput.value) || 0;
            const unitPrice = parseFloat(unitCostInput.value) || 0;
            const total = qty * unitPrice;
            amountInput.value = total.toFixed(2);
            updateTotalAmount();
        }

        descInput.addEventListener('change', function() {
            const val = descInput.value.trim();

            // Check all other description inputs for duplicates
            const descInputs = document.querySelectorAll('input[list="descriptionList"]');
            let duplicateFound = false;

            descInputs.forEach((input, idx) => {
                if (input !== descInput && input.value.trim() === val) {
                    duplicateFound = true;
                }
            });

            if (duplicateFound) {
                showDuplicateModal();
                descInput.value = '';
                return;
            }

            const options = dataList.options;
            for (let j = 0; j < options.length; j++) {
                if (options[j].value === val) {
                    const unitCost = options[j].getAttribute('data-unit-cost');
                    const maxQty = options[j].getAttribute('data-quantity');
                    const acqDate = options[j].getAttribute('data-date');
                    const unit = options[j].getAttribute('data-unit');

                    unitCostInput.value = unitCost;
                    qtyInput.max = maxQty;
                    acqDateInput.value = acqDate;

                    const unitSelect = document.querySelector(`[name="items[${i}][unit]"]`);
                    if (unitSelect && unit) {
                        for (let opt of unitSelect.options) {
                            if (opt.value === unit) {
                                unitSelect.value = unit;
                                break;
                            }
                        }
                    }

                    if (parseInt(qtyInput.value) > parseInt(maxQty)) {
                        qtyInput.value = '';
                        alert("Quantity exceeds available stock: " + maxQty);
                    }

                    calculateAmount();
                    break;
                }
            }

            updateTotalAmount();
        });
    }

    // Attach to initial rows
    for (let i = 0; i < rowIndex; i++) {
        const desc = document.getElementById('descInput' + i)?.value;
        if (desc) selectedDescriptions.add(desc); // Track existing selections
        attachRowEvents(i);
    }

    function updateTotalAmount() {
        let total = 0;
        for (let i = 0; i < rowIndex; i++) {
            const amountInput = document.getElementById('amount' + i);
            if (amountInput && amountInput.value) {
                total += parseFloat(amountInput.value) || 0;
            }
        }
        document.getElementById('totalAmount').value = total.toFixed(2);
    }

    // Optional: Final duplicate check on form submission
    function checkDuplicates() {
        const descInputs = document.querySelectorAll('input[list="descriptionList"]');
        const seen = new Set();

        for (let input of descInputs) {
            const val = input.value.trim();
            if (val !== '') {
                if (seen.has(val)) {
                    alert("Duplicate asset descriptions found.");
                    return false;
                }
                seen.add(val);
            }
        }

        return true;
    }

    function showDuplicateModal() {
        const duplicateModal = new bootstrap.Modal(document.getElementById('duplicateModal'));
        duplicateModal.show();
    }

    function clearDescription(index) {
        const descInput = document.getElementById('descInput' + index);
        const value = descInput.value.trim();

        if (value && selectedDescriptions.has(value)) {
            selectedDescriptions.delete(value);
        }

        descInput.value = '';

        // Also clear related fields
        const qtyInput = document.getElementById('qtyInput' + index);
        const unitCostInput = document.getElementById('unitCost' + index);
        const acqDateInput = document.getElementById('acqDate' + index);
        const amountInput = document.getElementById('amount' + index);

        if (qtyInput) qtyInput.value = '';
        if (unitCostInput) unitCostInput.value = '';
        if (acqDateInput) acqDateInput.value = '';
        if (amountInput) amountInput.value = '';

        updateTotalAmount();
    }


    // Add this to your submit button: onclick="return checkDuplicates()"
</script>