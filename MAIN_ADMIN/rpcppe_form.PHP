<?php
// Fetch latest record
$sql = "SELECT accountable_officer, destination, agency_office, member_inventory, chairman_inventory, mayor
        FROM rpcppe_form 
        ORDER BY id DESC 
        LIMIT 1";
$result = $conn->query($sql);

$accountable_officer = "";
$destination = "";
$agency_office = "";
$member_inventory = "";
$chairman_inventory = "";
$mayor = "";

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $accountable_officer = $row['accountable_officer'];
    $destination = $row['destination'];
    $agency_office = $row['agency_office'];
    $member_inventory = $row['member_inventory'];
    $chairman_inventory = $row['chairman_inventory'];
    $mayor = $row['mayor'];
}
?>

<form method="POST" class="container mt-3">
    <!-- First Header Table -->
    <table class="table table-bordered align-middle">
        <tr>
            <td rowspan="2" class="align-middle text-center">For which</td>
            <td class="p-0 text-center">
                <input type="text" name="accountable_officer" value="<?= htmlspecialchars($accountable_officer) ?>" class="form-control border-0 text-center">
                <small>(Name of Accountable Officer)</small>
            </td>
            <td class="p-0 text-center">
                <input type="text" name="destination" value="<?= htmlspecialchars($destination) ?>" class="form-control border-0 text-center">
                <small>(Official Designation)</small>
            </td>
            <td class="p-0 text-center">
                <input type="text" name="agency_office" value="<?= htmlspecialchars($agency_office) ?>" class="form-control border-0 text-center">
                <small>(Agency Office)</small>
            </td>
            <td rowspan="2" class="align-middle text-center">
                is accountable having assumed such accountability on
            </td>
        </tr>
        <tr>
            <td colspan="3"></td>
        </tr>
    </table>

    <style>
        .small-date {
            width: 75px !important;
            font-size: 10px;
            padding: 2px;
        }
    </style>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle table-sm" style="font-size: 11px; width: 100%;">
            <thead class="table-light">
                <tr>
                    <th rowspan="2">CODE</th>
                    <th rowspan="2">Office</th>
                    <th rowspan="2" style="width: 75px;">Date of Purchase</th>
                    <th rowspan="2">ARTICLE</th>
                    <th rowspan="2">DESCRIPTION</th>
                    <th rowspan="2">Property Number</th>
                    <th rowspan="2">Unit of Measure</th>
                    <th rowspan="2">Unit Value</th>
                    <th rowspan="2">Balance Per Card</th>
                    <th rowspan="2">On-Hand Per Count</th>
                    <th colspan="2">Shortage/Overage</th>
                    <th rowspan="2">Remarks</th>
                    <th rowspan="2">USER</th>
                    <th rowspan="2">SERIAL NUMBER</th>
                    <th rowspan="2">RECEIVED FROM</th>
                    <th rowspan="2">RECEIVED BY</th>
                    <th rowspan="2">MOVEMENT</th>
                    <th rowspan="2">DOCUMENT #</th>
                </tr>
                <tr>
                    <th>Qty</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="code[]" class="form-control form-control-sm"></td>
                    <td>
                        <select name="office[]" class="form-control form-control-sm">
                            <option value="">-- Select Office --</option>
                            <?php
                            // Fetch offices from DB
                            $office_query = "SELECT id, office_name FROM offices ORDER BY office_name ASC";
                            $office_result = $conn->query($office_query);

                            if ($office_result && $office_result->num_rows > 0) {
                                while ($office = $office_result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($office['office_id']) . '">' . htmlspecialchars($office['office_name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="date"
                            name="purchase_date[]"
                            class="form-control form-control-sm small-date"
                            value="<?= date('Y-m-d') ?>">
                    </td>
                    <td><input type="text" name="article[]" class="form-control form-control-sm"></td>
                    <td style="position: relative;">
                        <input list="asset_descriptions" style="width:200px;"
                            name="description[]"
                            class="form-control form-control-sm desc-input"
                            oninput="fillAssetDetails(this)">

                        <!-- Clear (X) button -->
                        <button type="button"
                            class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y"
                            style="border: none; padding: 0 6px;"
                            onclick="clearDescription(this)">
                            ✕
                        </button>

                        <datalist id="asset_descriptions">
                            <?php
                            $asset_query = "SELECT description, unit, value FROM assets ORDER BY description ASC";
                            $asset_result = $conn->query($asset_query);

                            if ($asset_result && $asset_result->num_rows > 0) {
                                while ($asset = $asset_result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($asset['description']) . '" 
                          data-unit="' . htmlspecialchars($asset['unit']) . '" 
                          data-value="' . htmlspecialchars($asset['value']) . '">';
                                }
                            }
                            ?>
                        </datalist>
                    </td>
                    <td><input type="text" name="property_number[]" class="form-control form-control-sm"></td>
                    <td>
                        <select name="unit_measure[]" class="form-control form-control-sm">
                            <option value="">-- Select Unit --</option>
                            <?php
                            // Fetch units from database
                            $sql = "SELECT unit_name FROM unit ORDER BY unit_name ASC";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['unit_name']) . '">' . htmlspecialchars($row['unit_name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>

                    <td>
                        <input type="number"
                            step="0.01"
                            name="unit_value[]"
                            class="form-control form-control-sm unit-value-input"
                            min="1">
                    </td>
                    <style>
                        .unit-value-input {
                            width: 90px !important;
                            /* wider than default */
                            font-size: 11px;
                            /* smaller font */
                            text-align: right;
                            /* align numbers neatly */
                        }
                    </style>

                    <td><input type="number" step="0.01" name="balance_value[]" class="form-control form-control-sm" min="1"></td>
                    <td><input type="number" step="0.01" name="onhand_value[]" class="form-control form-control-sm" min="1"></td>
                    <td><input type="number" name="shortage_qty[]" class="form-control form-control-sm" min="1"></td>
                    <td><input type="number" step="0.01" name="shortage_value[]" class="form-control form-control-sm" min="1"></td>
                    <td>
                        <select name="remarks[]" class="form-control form-control-sm">
                            <option value="">-- Select Remark --</option>
                            <option value="Unserviceable">Unserviceable</option>
                            <option value="Unserviceable">Serviceable</option>

                        </select>
                    </td>
                    <td><input type="text" name="user[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="serial_number[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="received_from[]" class="form-control form-control-sm" style="width:200px;"></td>
                    <td><input type="text" name="received_by[]" class="form-control form-control-sm" style="width:200px;"></td>
                    <td>
                        <select name="movement[]" class="form-control form-control-sm">
                            <option value="">-- Select Movement --</option>
                            <option value="ARE">ARE</option>
                            <option value="PAR">PAR</option>
                        </select>
                    </td>
                    <td>
                        <select name="document_number[]" class="form-control form-control-sm">
                            <option value="">-- Select Document # --</option>
                            <?php
                            $doc_query = "SELECT doc_id, document_number FROM doc_no ORDER BY document_number ASC";
                            $doc_result = $conn->query($doc_query);

                            if ($doc_result && $doc_result->num_rows > 0) {
                                while ($doc = $doc_result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($doc['document_number']) . '">' . htmlspecialchars($doc['document_number']) . '</option>';
                                }
                            } else {
                                echo '<option disabled>No document numbers available</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Footer Section -->
    <table class="table table-borderless text-center mt-4" style="font-size: 12px; width: 100%;">
        <tr>
            <td>Prepared by:</td>
            <td>Certified Correct:</td>
            <td>Noted by:</td>
        </tr>
        <tr>
            <td colspan="3" style="height: 40px;"></td>
        </tr> <!-- Space for signatures -->
        <tr>
            <td>
                <input type="text"
                    name="member_inventory"
                    value="<?= htmlspecialchars($member_inventory) ?>"
                    class="form-control form-control-sm text-center">
            </td>
            <td>
                <input type="text"
                    name="chairman_inventory"
                    value="<?= htmlspecialchars($chairman_inventory) ?>"
                    class="form-control form-control-sm text-center">
            </td>
            <td>
                <input type="text"
                    name="mayor"
                    value="<?= htmlspecialchars($mayor) ?>"
                    class="form-control form-control-sm text-center">
            </td>
        </tr>

        <tr>
            <td>Member, Inventory Committee</td>
            <td>Chairman, Inventory Committee</td>
            <td>Municipal Mayor</td>
        </tr>
    </table>


    <button type="submit" class="btn btn-primary">Save</button>
</form>

<script>
    function fillAssetDetails(input) {
        const datalist = document.getElementById("asset_descriptions");
        const options = datalist.options;

        let row = input.closest("tr");
        let unitSelect = row.querySelector('select[name="unit_measure[]"]');
        let unitValue = row.querySelector('input[name="unit_value[]"]');

        // Reset values first
        unitSelect.value = "";
        unitValue.value = "";

        for (let i = 0; i < options.length; i++) {
            if (options[i].value === input.value) {
                let unit = options[i].dataset.unit;
                let value = options[i].dataset.value;

                // Set dropdown to matching unit (if exists)
                if (unit) {
                    for (let j = 0; j < unitSelect.options.length; j++) {
                        if (unitSelect.options[j].value === unit) {
                            unitSelect.value = unit;
                            break;
                        }
                    }
                }

                // Set unit value
                if (value) {
                    unitValue.value = value;
                }
                break;
            }
        }
    }

    function clearDescription(btn) {
        let row = btn.closest("tr");
        row.querySelector('.desc-input').value = "";
        row.querySelector('select[name="unit_measure[]"]').value = "";
        row.querySelector('input[name="unit_value[]"]').value = "";
    }
</script>