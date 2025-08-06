<?php
// Fetch ICS form data (you can pass ICS ID via GET or session)
$ics_id = $_GET['ics_id'] ?? null;

$ics_data = [
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'ics_no' => '',
];

if ($ics_id) {
    $stmt = $conn->prepare("SELECT * FROM ics_form WHERE id = ?");
    $stmt->bind_param("i", $ics_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $ics_data = $result->fetch_assoc();
    }
}

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

            <table class="table table-bordered text-center align-middle mt-3">
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
                <tbody>
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
                        <td><input type="number" class="form-control" step="0.01" name="unit_cost[]"></td>
                        <td>
                            <input type="number" class="form-control total_cost" name="total_cost[]" step="0.01" readonly>
                        </td>
                        <!-- Searchable Description -->
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
                </tbody>
            </table>

            <button type="submit" class="btn btn-success mt-3">Save ICS</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('table');

    // Mapping from PHP (description => { unit_cost, quantity })
    const descriptionMap = <?= json_encode($description_details) ?>;

    table.addEventListener('input', function (event) {
        const target = event.target;
        const row = target.closest('tr');
        if (!row) return;

        const quantityInput = row.querySelector('input[name="quantity[]"]');
        const unitCostInput = row.querySelector('input[name="unit_cost[]"]');
        const totalCostField = row.querySelector('input[name="total_cost[]"]');

        // If the input was description
        if (target.name === "description[]") {
            const selectedDesc = target.value;
            if (descriptionMap[selectedDesc]) {
                const { unit_cost, quantity } = descriptionMap[selectedDesc];

                // Set unit cost
                unitCostInput.value = unit_cost;

                // Set max quantity
                quantityInput.max = quantity;
                quantityInput.placeholder = `Max: ${quantity}`;
            }
        }

        // Recalculate total cost
        const quantity = parseFloat(quantityInput?.value) || 0;
        const unitCost = parseFloat(unitCostInput?.value) || 0;
        totalCostField.value = (quantity * unitCost).toFixed(2);

        // Optional: Validate quantity against max
        if (quantityInput.max && quantity > parseFloat(quantityInput.max)) {
            quantityInput.setCustomValidity("Quantity exceeds available stock.");
            quantityInput.reportValidity();
        } else {
            quantityInput.setCustomValidity("");
        }
    });
});
</script>
