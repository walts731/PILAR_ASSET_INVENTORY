<?php
require_once '../connect.php';

$form_id = $_GET['id'] ?? null;

// Default data
$par_data = [
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'par_no' => '',
    'office_id' => '',
    'position_office_left' => '',
    'position_office_right' => ''
];

// Fetch PAR header/footer data
if ($form_id) {
    $stmt = $conn->prepare("SELECT header_image, entity_name, fund_cluster, par_no, office_id, position_office_left, position_office_right 
                             FROM par_form WHERE form_id = ?");
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
?>

<div class="container mt-3">
    <h3>PAR Header & Footer Settings</h3>
    <form method="post" action="save_par_header_footer.php" enctype="multipart/form-data">
        <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

        <!-- HEADER SETTINGS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                Header Settings
            </div>
            <div class="card-body">
                <!-- Header Image Upload -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Header Image</label>
                    <?php if (!empty($par_data['header_image'])): ?>
                        <div class="mb-2 text-center">
                            <img src="img/<?= htmlspecialchars($par_data['header_image']) ?>"
                                alt="Header Image"
                                class="img-fluid rounded border w-100"
                                style="max-height:250px; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="header_image" class="form-control" accept="image/*">
                </div>

                <!-- Office Dropdown -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Office/Location</label>
                    <select name="office_id" class="form-select" required>
                        <option value="">Select Office</option>
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= $office['id'] ?>" <?= ($office['id'] == $par_data['office_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($office['office_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Entity Name</label>
                        <input type="text" name="entity_name" class="form-control" value="<?= htmlspecialchars($par_data['entity_name']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Fund Cluster</label>
                        <input type="text" name="fund_cluster" class="form-control" value="<?= htmlspecialchars($par_data['fund_cluster']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">PAR No.</label>
                    <input type="text" name="par_no" class="form-control" value="<?= htmlspecialchars($par_data['par_no']) ?>">
                </div>
            </div>
        </div>

        <!-- FOOTER SETTINGS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                Footer / Signatories
            </div>
            <div class="card-body row">
                <div class="col-md-6 text-center mb-3">
                    <p class="fw-semibold">Received by:</p>
                    <input type="text" class="form-control text-center" name="position_office_left"
                        placeholder="Position / Office" value="<?= htmlspecialchars($par_data['position_office_left']) ?>">
                </div>
                <div class="col-md-6 text-center mb-3">
                    <p class="fw-semibold">Issued by:</p>
                    <input type="text" class="form-control text-center" name="position_office_right"
                        placeholder="Position / Office" value="<?= htmlspecialchars($par_data['position_office_right']) ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-info">Save Header & Footer</button>
    </form>
</div>