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
    $stmt = $conn->prepare("SELECT header_image, entity_name, fund_cluster, par_no, office_id FROM par_form WHERE form_id = ?");
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
$result = $conn->query("SELECT description, value AS unit_cost, quantity, acquisition_date FROM assets");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $desc = $row['description'];
        $description_details[$desc] = [
            'unit_cost' => $row['unit_cost'],
            'quantity' => $row['quantity'],
            'acquisition_date' => $row['acquisition_date']
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
                                        <option
                                            value="<?= htmlspecialchars($desc) ?>"
                                            data-unit-cost="<?= htmlspecialchars($details['unit_cost']) ?>"
                                            data-quantity="<?= htmlspecialchars($details['quantity']) ?>"
                                            data-date="<?= htmlspecialchars($details['acquisition_date']) ?>">
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
                        <input type="text" name="entity_name" class="form-control" value="<?= htmlspecialchars($par_data['entity_name']) ?>" required>
                    </td>
                    <td>
                        <!-- Blank right cell -->
                    </td>
                </tr>

                <!-- Fund Cluster and PAR No. -->
                <tr>
                    <td>
                        <label class="form-label fw-semibold mb-0">Fund Cluster</label>
                        <input type="text" name="fund_cluster" class="form-control" value="<?= htmlspecialchars($par_data['fund_cluster']) ?>" required>
                    </td>
                    <td>
                        <label class="form-label fw-semibold mb-0">PAR No.</label>
                        <input type="text" name="par_no" class="form-control" value="<?= htmlspecialchars($par_data['par_no']) ?>" required>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ITEM TABLE -->
        <table class="table align-middle text-center mt-4" style="table-layout: fixed; border: none;">
            <thead>
                <tr>
                    <th>QUANTITY</th>
                    <th>UNIT</th>
                    <th>DESCRIPTION</th>
                    <th>PROPERTY NO</th>
                    <th>DATE ACQUIRED</th>
                    <th>UNIT PRICE</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample empty row, duplicate or generate dynamically as needed -->
                <tr>
                    <td><input type="number" name="items[0][quantity]" class="form-control text-end" id="qtyInput0"></td>
                    <td>
                        <select name="items[0][unit]" class="form-select text-center" required>
                            <option value="">Select Unit</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= htmlspecialchars($unit['unit_name']) ?>">
                                    <?= htmlspecialchars($unit['unit_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="items[0][description]" class="form-control" list="descriptionList" id="descInput0">
                        <datalist id="descriptionList">
                            <?php foreach ($description_details as $desc => $details): ?>
                                <option
                                    value="<?= htmlspecialchars($desc) ?>"
                                    data-unit-cost="<?= htmlspecialchars($details['unit_cost']) ?>"
                                    data-quantity="<?= htmlspecialchars($details['quantity']) ?>"
                                    data-date="<?= htmlspecialchars($details['acquisition_date']) ?>">
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                    </td>
                    <td><input type="text" name="items[0][property_no]" class="form-control"></td>
                    <td><input type="date" name="items[0][date_acquired]" class="form-control" id="acqDate0"></td>
                    <td><input type="number" name="items[0][unit_price]" class="form-control text-end" step="0.01" id="unitCost0"></td>
                    <td><input type="number" name="items[0][amount]" class="form-control text-end" step="0.01" id="amount0" readonly></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Save PAR Form</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const descInput = document.getElementById('descInput0');
    const unitCostInput = document.getElementById('unitCost0');
    const qtyInput = document.getElementById('qtyInput0');
    const acqDateInput = document.getElementById('acqDate0');
    const amountInput = document.getElementById('amount0');
    const dataList = document.getElementById('descriptionList');

    function calculateAmount() {
        const qty = parseFloat(qtyInput.value) || 0;
        const unitPrice = parseFloat(unitCostInput.value) || 0;
        const total = qty * unitPrice;
        amountInput.value = total.toFixed(2);
    }

    descInput.addEventListener('input', function () {
        const val = descInput.value;
        const options = dataList.options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === val) {
                const unitCost = options[i].getAttribute('data-unit-cost');
                const maxQty = options[i].getAttribute('data-quantity');
                const acqDate = options[i].getAttribute('data-date');

                unitCostInput.value = unitCost;
                qtyInput.max = maxQty;
                acqDateInput.value = acqDate;

                if (parseInt(qtyInput.value) > parseInt(maxQty)) {
                    qtyInput.value = '';
                    alert("Quantity exceeds available stock: " + maxQty);
                }

                calculateAmount();
                break;
            }
        }
    });

    qtyInput.addEventListener('input', calculateAmount);
    unitCostInput.addEventListener('input', calculateAmount);
});
</script>
