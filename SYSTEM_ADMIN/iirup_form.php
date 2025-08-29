<?php
require_once '../connect.php';

// Get form_id and success flag
$form_id = $_GET['id'] ?? null;
$success = $_GET['success'] ?? null;

// Fetch latest header data (include header_image)
$header = [
    'accountable_officer' => '',
    'designation' => '',
    'office' => '',
    'header_image' => ''
];
$sql = "SELECT accountable_officer, designation, office, header_image 
        FROM iirup_form 
        ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $header = $result->fetch_assoc();
}

// Fetch office list
$offices = [];
$sql_office = "SELECT id, office_name FROM offices ORDER BY office_name ASC";
$result_office = $conn->query($sql_office);
if ($result_office && $result_office->num_rows > 0) {
    while ($row = $result_office->fetch_assoc()) {
        $offices[] = $row;
    }
}

// Fetch latest footer data
$footer = [
    'footer_accountable_officer' => '',
    'footer_authorized_official' => '',
    'footer_designation_officer' => '',
    'footer_designation_official' => ''
];
$sql_footer = "SELECT footer_accountable_officer, footer_authorized_official, 
                      footer_designation_officer, footer_designation_official 
               FROM iirup_form 
               ORDER BY id DESC LIMIT 1";
$result_footer = $conn->query($sql_footer);
if ($result_footer && $result_footer->num_rows > 0) {
    $footer = $result_footer->fetch_assoc();
}
?>


<?php if ($success === 1): ?>
    <div class="alert alert-success text-center">
        ✅ Header and Footer saved successfully!
    </div>
<?php endif; ?>

<form method="POST" action="save_iirup_header_footer.php" enctype="multipart/form-data">
    <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

    <!-- HEADER SECTION -->
    <h4 class="mb-3">IIRUP Header</h4>
    <div class="row mb-3">
        <!-- Header Image -->
<div class="row mb-3">
    <div class="col-md-12">
        <label class="form-label">Header Image</label>
        <input type="file" name="header_image" class="form-control" accept="image/*">

        <?php if (!empty($header['header_image'])): ?>
            <div class="mt-2 text-center">
                <img src="../img/<?= htmlspecialchars($header['header_image']) ?>" 
                     alt="Header Image" 
                     class="img-fluid rounded shadow-sm" 
                     style="max-height: 120px;">
                <div class="mt-2 fst-italic text-secondary text-center">
                    As of <?= date("F, Y") ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


        <div class="col-md-4">
            <label class="form-label">Accountable Officer</label>
            <input type="text" name="accountable_officer" class="form-control"
                   value="<?= htmlspecialchars($header['accountable_officer']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Designation</label>
            <input type="text" name="designation" class="form-control"
                   value="<?= htmlspecialchars($header['designation']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Department/Office</label>
            <select name="office" class="form-control">
                <option value="">-- Select Office --</option>
                <?php foreach ($offices as $o): ?>
                    <option value="<?= htmlspecialchars($o['office_name']) ?>"
                        <?= ($header['office'] == $o['office_name']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($o['office_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- FOOTER SECTION -->
    <h4 class="mt-4 mb-3">IIRUP Footer</h4>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Accountable Officer (Signature over Printed Name)</label>
            <input type="text" name="footer_accountable_officer" class="form-control"
                   value="<?= htmlspecialchars($footer['footer_accountable_officer']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Authorized Official (Signature over Printed Name)</label>
            <input type="text" name="footer_authorized_official" class="form-control"
                   value="<?= htmlspecialchars($footer['footer_authorized_official']) ?>">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Designation of Accountable Officer</label>
            <input type="text" name="footer_designation_officer" class="form-control"
                   value="<?= htmlspecialchars($footer['footer_designation_officer']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Designation of Authorized Official</label>
            <input type="text" name="footer_designation_official" class="form-control"
                   value="<?= htmlspecialchars($footer['footer_designation_official']) ?>">
        </div>
    </div>

    <!-- SAVE BUTTON -->
    <div class="text-start">
        <button type="submit" class="btn btn-primary btn-lg">Save</button>
    </div>
</form>
