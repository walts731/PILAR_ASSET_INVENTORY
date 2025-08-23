<?php
require_once '../connect.php';

// Get form_id from URL for redirect purposes
$form_id = $_GET['id'] ?? null;

// Fetch the **first row only** for display
$rpcppe_data = [
    'id' => '',
    'header_image' => '',
    'accountable_officer' => '',
    'destination' => '',
    'agency_office' => '',
    'member_inventory' => '',
    'chairman_inventory' => '',
    'mayor' => ''
];

$sql = "SELECT id, accountable_officer, destination, agency_office, member_inventory, chairman_inventory, mayor, header_image 
        FROM rpcppe_form 
        ORDER BY id ASC 
        LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $rpcppe_data = $result->fetch_assoc();
}

// Use the database row's id as record_id for updates
$record_id = $rpcppe_data['id'] ?? '';

// Current month/year
$as_of = date('F, Y');
?>

<form method="POST" action="save_rpcppe_header_footer.php" enctype="multipart/form-data">
    <!-- Hidden inputs -->
    <input type="hidden" name="record_id" value="<?= htmlspecialchars($record_id) ?>">
    <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

    <!-- HEADER IMAGE -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Header Image</label>
        <?php if (!empty($rpcppe_data['header_image'])): ?>
            <div class="mb-2 text-center">
                <img src="../img/<?= htmlspecialchars($rpcppe_data['header_image']) ?>"
                     alt="Header Image"
                     class="img-fluid rounded border w-100"
                     style="max-height:250px; object-fit:contain;">
            </div>
        <?php endif; ?>
        <input type="file" name="header_image" class="form-control" accept="image/*">
        <div class="mt-2 fst-italic text-secondary text-center">As of <?= $as_of ?></div>
    </div>

    <!-- HEADER FIELDS -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Accountable Officer</label>
            <input type="text" name="accountable_officer" class="form-control"
                   value="<?= htmlspecialchars($rpcppe_data['accountable_officer']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Designation</label>
            <input type="text" name="destination" class="form-control"
                   value="<?= htmlspecialchars($rpcppe_data['destination']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Agency/Office</label>
            <select name="agency_office" class="form-control">
                <option value="">-- Select Office --</option>
                <?php
                $office_query = "SELECT id, office_name FROM offices ORDER BY office_name ASC";
                $office_result = $conn->query($office_query);
                if ($office_result && $office_result->num_rows > 0) {
                    while ($office = $office_result->fetch_assoc()) {
                        $selected = ($rpcppe_data['agency_office'] == $office['office_name']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($office['office_name']) . '" ' . $selected . '>' 
                            . htmlspecialchars($office['office_name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <!-- FOOTER FIELDS -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Member, Inventory Committee</label>
            <input type="text" name="member_inventory" class="form-control"
                   value="<?= htmlspecialchars($rpcppe_data['member_inventory']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Chairman, Inventory Committee</label>
            <input type="text" name="chairman_inventory" class="form-control"
                   value="<?= htmlspecialchars($rpcppe_data['chairman_inventory']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Municipal Mayor</label>
            <input type="text" name="mayor" class="form-control"
                   value="<?= htmlspecialchars($rpcppe_data['mayor']) ?>">
        </div>
    </div>

    <!-- SAVE BUTTON -->
    <div class="text-start mt-4">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send-check-fill"></i>Save</button>
    </div>
</form>
