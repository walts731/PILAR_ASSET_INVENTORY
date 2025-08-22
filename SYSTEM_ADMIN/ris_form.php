<?php
require_once '../connect.php';

// Fetch existing RIS data if editing
$ris_id = $_GET['id'] ?? null;
$ris_data = [];
if ($ris_id) {
    $stmt = $conn->prepare("SELECT * FROM ris_form WHERE id = ?");
    $stmt->bind_param("i", $ris_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ris_data = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $division = $_POST['division'];
    $responsibility_center = $_POST['responsibility_center'];
    $ris_no = $_POST['ris_no'];
    $date = $_POST['date'];
    $office = $_POST['office'];
    $responsibility_code = $_POST['responsibility_code'];
    $sai_no = $_POST['sai_no'];
    $purpose = $_POST['purpose'];

    // Footer
    $requested_by_name = $_POST['requested_by_name'];
    $approved_by_name = $_POST['approved_by_name'];
    $issued_by_name = $_POST['issued_by_name'];
    $received_by_name = $_POST['received_by_name'];
    $requested_by_designation = $_POST['requested_by_designation'];
    $approved_by_designation = $_POST['approved_by_designation'];
    $issued_by_designation = $_POST['issued_by_designation'];
    $received_by_designation = $_POST['received_by_designation'];
    $requested_by_date = $_POST['requested_by_date'];
    $approved_by_date = $_POST['approved_by_date'];
    $issued_by_date = $_POST['issued_by_date'];
    $received_by_date = $_POST['received_by_date'];

    // Header Image upload
    $header_image = $ris_data['header_image'] ?? null;
    if (!empty($_FILES['header_image']['name'])) {
        $targetDir = "../img/";
        $fileName = time() . "_" . basename($_FILES['header_image']['name']);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['header_image']['tmp_name'], $targetFilePath)) {
            $header_image = $fileName;
        }
    }

    // Insert or Update
    if ($ris_id) {
        $stmt = $conn->prepare("
            UPDATE ris_form SET 
                header_image=?, division=?, responsibility_center=?, ris_no=?, date=?, office=?, responsibility_code=?, sai_no=?, purpose=?,
                requested_by_name=?, approved_by_name=?, issued_by_name=?, received_by_name=?,
                requested_by_designation=?, approved_by_designation=?, issued_by_designation=?, received_by_designation=?,
                requested_by_date=?, approved_by_date=?, issued_by_date=?, received_by_date=?
            WHERE id=?
        ");
        $stmt->bind_param("sssssssssssssssssssssi",
            $header_image, $division, $responsibility_center, $ris_no, $date, $office, $responsibility_code, $sai_no, $purpose,
            $requested_by_name, $approved_by_name, $issued_by_name, $received_by_name,
            $requested_by_designation, $approved_by_designation, $issued_by_designation, $received_by_designation,
            $requested_by_date, $approved_by_date, $issued_by_date, $received_by_date,
            $ris_id
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO ris_form (
                header_image, division, responsibility_center, ris_no, date, office, responsibility_code, sai_no, purpose,
                requested_by_name, approved_by_name, issued_by_name, received_by_name,
                requested_by_designation, approved_by_designation, issued_by_designation, received_by_designation,
                requested_by_date, approved_by_date, issued_by_date, received_by_date
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param("sssssssssssssssssssss",
            $header_image, $division, $responsibility_center, $ris_no, $date, $office, $responsibility_code, $sai_no, $purpose,
            $requested_by_name, $approved_by_name, $issued_by_name, $received_by_name,
            $requested_by_designation, $approved_by_designation, $issued_by_designation, $received_by_designation,
            $requested_by_date, $approved_by_date, $issued_by_date, $received_by_date
        );
    }

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>RIS saved successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <!-- Header Image -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Header Image</label>
        <input type="file" name="header_image" class="form-control" accept="image/*">
        <?php if (!empty($ris_data['header_image'])): ?>
            <img src="../img/<?= htmlspecialchars($ris_data['header_image']) ?>" alt="Header Image" class="img-fluid mt-2" style="max-height:150px;">
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
            <label for="office" class="form-label fw-semibold">Office/Unit</label>
            <select class="form-select" id="office" name="office" required>
                <option value="" disabled <?= !isset($ris_data['office']) ? 'selected' : '' ?>>Select Office</option>
                <?php
                $office_query = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
                while ($row = $office_query->fetch_assoc()):
                    $selected = (isset($ris_data['office']) && $ris_data['office'] == $row['id']) ? 'selected' : '';
                ?>
                    <option value="<?= $row['id'] ?>" <?= $selected ?>><?= htmlspecialchars($row['office_name']) ?></option>
                <?php endwhile; ?>
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

    <!-- Purpose -->
    <div class="mb-3">
        <label for="purpose" class="form-label fw-bold">PURPOSE:</label>
        <textarea class="form-control" name="purpose" id="purpose" rows="2"><?= htmlspecialchars($ris_data['purpose'] ?? '') ?></textarea>
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

    <!-- Submit Button -->
    <button type="submit" class="btn btn-success">
        <i class="bi bi-send-check-fill"></i> Save RIS
    </button>
</form>
