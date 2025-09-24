<?php
require_once '../connect.php';

$form_id = $_GET['id'] ?? null;

// Defaults
$itr_data = [
    'itr_id' => '',
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'from_accountable_officer' => '',
    'to_accountable_officer' => '',
    'itr_no' => '',
    'date' => date('Y-m-d'),
    'transfer_type' => '',
    'reason_for_transfer' => '',
    'approved_by' => '',
    'approved_designation' => '',
    'approved_date' => '',
    'released_by' => '',
    'released_designation' => '',
    'released_date' => '',
    'received_by' => '',
    'received_designation' => '',
    'received_date' => ''
];

// Fetch the latest ITR data
$sql = "SELECT itr_id, header_image, entity_name, fund_cluster, from_accountable_officer, to_accountable_officer, itr_no, `date`, transfer_type, reason_for_transfer, approved_by, approved_designation, approved_date, released_by, released_designation, released_date, received_by, received_designation, received_date FROM itr_form ORDER BY itr_id DESC LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $itr_data = $result->fetch_assoc();
}
?>

<form method="POST" action="save_itr_header_footer.php" enctype="multipart/form-data">
    <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">
    <input type="hidden" name="itr_id" value="<?= htmlspecialchars($itr_data['itr_id'] ?? '') ?>">

    <!-- Header Image Upload -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Header Image</label>
        <?php if (!empty($itr_data['header_image'])): ?>
            <div class="mb-2 text-center">
                <img src="../img/<?= htmlspecialchars($itr_data['header_image']) ?>"
                    alt="Header Image"
                    class="img-fluid rounded border w-100"
                    style="max-height:250px; object-fit:contain;">
            </div>
        <?php endif; ?>
        <input type="file" name="header_image" class="form-control" accept="image/*">
    </div>

    <!-- Header Fields Row 1: Entity, Fund Cluster, ITR No, Date -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">ENTITY NAME</label>
            <input type="text" class="form-control" name="entity_name" value="<?= htmlspecialchars($itr_data['entity_name']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">FUND CLUSTER</label>
            <input type="text" class="form-control" name="fund_cluster" value="<?= htmlspecialchars($itr_data['fund_cluster']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">ITR NO.</label>
            <input type="text" class="form-control" name="itr_no" value="<?= htmlspecialchars($itr_data['itr_no']) ?>">
        </div>
        <div class="col-md-1">
            <label class="form-label fw-semibold">DATE</label>
            <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($itr_data['date'] ?: date('Y-m-d')) ?>">
        </div>
    </div>

    <!-- Header Fields Row 2: From / To / Transfer Type -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">FROM ACCOUNTABLE OFFICER</label>
            <input type="text" class="form-control" name="from_accountable_officer" value="<?= htmlspecialchars($itr_data['from_accountable_officer']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">TO ACCOUNTABLE OFFICER</label>
            <input type="text" class="form-control" name="to_accountable_officer" value="<?= htmlspecialchars($itr_data['to_accountable_officer']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">TRANSFER TYPE</label>
            <select name="transfer_type" class="form-select">
                <?php
                $types = ['donation' => 'Donation', 'reassignment' => 'Reassignment', 'relocate' => 'Relocate', 'others' => 'Others'];
                $selectedType = strtolower((string)($itr_data['transfer_type'] ?? ''));
                foreach ($types as $val => $label):
                ?>
                    <option value="<?= $val ?>" <?= $selectedType === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Reason for Transfer -->
    <div class="mb-4">
        <label class="form-label fw-semibold">Reason for Transfer</label>
        <textarea name="reason_for_transfer" class="form-control" rows="3" placeholder="Enter reason for transfer..."><?= htmlspecialchars($itr_data['reason_for_transfer']) ?></textarea>
    </div>

    <!-- Approvals/Signatories -->
    <div class="row g-3">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="fw-semibold mb-2">Approved By</div>
                <label class="form-label small">Name</label>
                <input type="text" name="approved_by" class="form-control" value="<?= htmlspecialchars($itr_data['approved_by']) ?>">
                <label class="form-label small mt-2">Designation</label>
                <input type="text" name="approved_designation" class="form-control" value="<?= htmlspecialchars($itr_data['approved_designation']) ?>">
                <label class="form-label small mt-2">Date</label>
                <input type="date" name="approved_date" class="form-control" value="<?= htmlspecialchars($itr_data['approved_date']) ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="fw-semibold mb-2">Released By</div>
                <label class="form-label small">Name</label>
                <input type="text" name="released_by" class="form-control" value="<?= htmlspecialchars($itr_data['released_by']) ?>">
                <label class="form-label small mt-2">Designation</label>
                <input type="text" name="released_designation" class="form-control" value="<?= htmlspecialchars($itr_data['released_designation']) ?>">
                <label class="form-label small mt-2">Date</label>
                <input type="date" name="released_date" class="form-control" value="<?= htmlspecialchars($itr_data['released_date']) ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="fw-semibold mb-2">Received By</div>
                <label class="form-label small">Name</label>
                <input type="text" name="received_by" class="form-control" value="<?= htmlspecialchars($itr_data['received_by']) ?>">
                <label class="form-label small mt-2">Designation</label>
                <input type="text" name="received_designation" class="form-control" value="<?= htmlspecialchars($itr_data['received_designation']) ?>">
                <label class="form-label small mt-2">Date</label>
                <input type="date" name="received_date" class="form-control" value="<?= htmlspecialchars($itr_data['received_date']) ?>">
            </div>
        </div>
    </div>

    <!-- SAVE BUTTON -->
    <div class="text-start mt-4">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send-check-fill"></i> Save</button>
    </div>
</form>
