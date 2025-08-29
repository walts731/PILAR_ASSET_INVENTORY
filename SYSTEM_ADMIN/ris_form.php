<?php
require_once '../connect.php';

// Fetch existing RIS data if editing
$form_id = $_GET['id'] ?? null;
$ris_data = [];
if ($form_id) {
    $stmt = $conn->prepare("SELECT * FROM ris_form WHERE form_id = ?");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ris_data = $result->fetch_assoc();
}
?>

<form method="POST" enctype="multipart/form-data" action="save_ris_header_footer.php">
    <!-- Hidden field for form_id -->
    <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

    <!-- Header Image -->
<div class="mb-3">
    <label class="form-label fw-semibold">Header Image</label>
    <input type="file" name="header_image" class="form-control" accept="image/*">
    <?php if (!empty($ris_data['header_image'])): ?>
        <div class="mt-2">
            <img src="../img/<?= htmlspecialchars($ris_data['header_image']) ?>" 
                 alt="Header Image" 
                 class="img-fluid rounded border"
                 style="width: 100%; height: auto; object-fit: contain; max-height: 300px;">
        </div>
    <?php endif; ?>
</div>


    <!-- Row 1: Division, Responsibility Center, RIS No., Date -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="division" class="form-label fw-semibold">Division</label>
            <input type="text" class="form-control" id="division" name="division" value="<?= htmlspecialchars($ris_data['division'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label for="responsibility_center" class="form-label fw-semibold">Responsibility Center</label>
            <input type="text" class="form-control" id="responsibility_center" name="responsibility_center" value="<?= htmlspecialchars($ris_data['responsibility_center'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label for="ris_no" class="form-label fw-semibold">RIS No.</label>
            <input type="text" class="form-control" id="ris_no" name="ris_no" value="<?= htmlspecialchars($ris_data['ris_no'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label for="date" class="form-label fw-semibold">Date</label>
            <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($ris_data['date'] ?? date('Y-m-d')) ?>">
        </div>
    </div>

    <!-- Row 2: Office, Responsibility Code, SAI No. -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="office_id" class="form-label fw-semibold">Office/Unit</label>
            <select name="office_id" id="office_id" class="form-select" required>
                <option value="">-- Select Office --</option>
                <?php
                $offices = $conn->query("SELECT id, office_name FROM offices");
                while ($row = $offices->fetch_assoc()) {
                    $selected = ($ris_data['office_id'] ?? '') == $row['id'] ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>{$row['office_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="responsibility_code" class="form-label fw-semibold">Code</label>
            <input type="text" class="form-control" id="responsibility_code" name="responsibility_code" value="<?= htmlspecialchars($ris_data['responsibility_code'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label for="sai_no" class="form-label fw-semibold">SAI No.</label>
            <input type="text" class="form-control" id="sai_no" name="sai_no" value="<?= htmlspecialchars($ris_data['sai_no'] ?? '') ?>">
        </div>
    </div>

    <!-- Purpose (must match `reason_for_transfer`) -->
    <div class="mb-3">
        <label for="reason_for_transfer" class="form-label fw-bold">PURPOSE:</label>
        <textarea class="form-control" name="reason_for_transfer" id="reason_for_transfer" rows="2"><?= htmlspecialchars($ris_data['reason_for_transfer'] ?? '') ?></textarea>
    </div>

    <!-- Footer Table -->
    <table class="table table-bordered text-center align-middle">
        <thead class="table-secondary">
            <tr>
                <th></th>
                <th>REQUESTED BY:</th>
                <th>APPROVED BY:</th>
                <th>ISSUED BY:</th>
                <th>RECEIVED BY:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Signature</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Printed Name:</td>
                <td><input type="text" class="form-control" name="requested_by_name" value="<?= htmlspecialchars($ris_data['requested_by_name'] ?? '') ?>"></td>
                <td><input type="text" class="form-control" name="approved_by_name" value="<?= htmlspecialchars($ris_data['approved_by_name'] ?? '') ?>"></td>
                <td><input type="text" class="form-control" name="issued_by_name" value="<?= htmlspecialchars($ris_data['issued_by_name'] ?? '') ?>"></td>
                <td><input type="text" class="form-control" name="received_by_name" value="<?= htmlspecialchars($ris_data['received_by_name'] ?? '') ?>"></td>
            </tr>
            <tr>
                <td>Designation:</td>
                <td><input type="text" class="form-control" name="requested_by_designation" value="<?= htmlspecialchars($ris_data['requested_by_designation'] ?? '') ?>"></td>
                <td><input type="text" class="form-control" name="approved_by_designation" value="<?= htmlspecialchars($ris_data['approved_by_designation'] ?? '') ?>"></td>
                <td><input type="text" class="form-control" name="issued_by_designation" value="<?= htmlspecialchars($ris_data['issued_by_designation'] ?? '') ?>"></td>
                <td><input type="text" class="form-control" name="received_by_designation" value="<?= htmlspecialchars($ris_data['received_by_designation'] ?? '') ?>"></td>
            </tr>
            <tr>
                <td>Date:</td>
                <td><input type="date" class="form-control" name="requested_by_date" value="<?= htmlspecialchars($ris_data['requested_by_date'] ?? date('Y-m-d')) ?>"></td>
                <td><input type="date" class="form-control" name="approved_by_date" value="<?= htmlspecialchars($ris_data['approved_by_date'] ?? date('Y-m-d')) ?>"></td>
                <td><input type="date" class="form-control" name="issued_by_date" value="<?= htmlspecialchars($ris_data['issued_by_date'] ?? date('Y-m-d')) ?>"></td>
                <td><input type="date" class="form-control" name="received_by_date" value="<?= htmlspecialchars($ris_data['received_by_date'] ?? date('Y-m-d')) ?>"></td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Date -->
    <div class="mb-3">
        <label for="footer_date" class="form-label fw-semibold">Footer Date</label>
        <input type="date" class="form-control" id="footer_date" name="footer_date" value="<?= htmlspecialchars($ris_data['footer_date'] ?? date('Y-m-d')) ?>">
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="bi bi-send-check-fill"></i> Save 
    </button>
</form>
