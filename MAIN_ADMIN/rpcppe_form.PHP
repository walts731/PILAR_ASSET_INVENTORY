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
                    <td><input type="text" name="office[]" class="form-control form-control-sm"></td>
                    <td>
                        <input type="date" name="purchase_date[]" class="form-control form-control-sm small-date">
                    </td>
                    <td><input type="text" name="article[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="description[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="property_number[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="unit_measure[]" class="form-control form-control-sm"></td>
                    <td><input type="number" step="0.01" name="unit_value[]" class="form-control form-control-sm"></td>
                    <td><input type="number" step="0.01" name="balance_value[]" class="form-control form-control-sm"></td>
                    <td><input type="number" step="0.01" name="onhand_value[]" class="form-control form-control-sm"></td>
                    <td><input type="number" name="shortage_qty[]" class="form-control form-control-sm"></td>
                    <td><input type="number" step="0.01" name="shortage_value[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="remarks[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="user[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="serial_number[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="received_from[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="received_by[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="movement[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="document_number[]" class="form-control form-control-sm"></td>
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