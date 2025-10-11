<?php
require_once '../connect.php';

$form_id = $_GET['id'] ?? null;

$ics_data = [
    'id' => null,
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'ics_no' => '',
    'received_from_name' => '',
    'received_from_position' => '',
    'received_by_name' => '',
    'received_by_position' => '',
    'created_at' => ''
];

// Fetch the latest ICS data
$sql = "SELECT id, header_image, entity_name, fund_cluster, ics_no, 
               received_from_name, received_from_position, 
               received_by_name, received_by_position, created_at 
        FROM ics_form 
        ORDER BY id DESC 
        LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $ics_data = $result->fetch_assoc();
}
?>

<form method="POST" action="save_ics_header_footer.php" enctype="multipart/form-data">
    <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">
    <?php if (!empty($ics_data['id'])): ?>
        <input type="hidden" name="ics_row_id" value="<?= (int)$ics_data['id'] ?>">
    <?php endif; ?>

    <!-- ICS Header Image Upload -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Header Image</label>
        <?php if (!empty($ics_data['header_image'])): ?>
            <div class="mb-2 text-center">
                <img src="../img/<?= htmlspecialchars($ics_data['header_image']) ?>"
                    alt="Header Image"
                    class="img-fluid rounded border w-100"
                    style="max-height:250px; object-fit:contain;">
            </div>
        <?php endif; ?>
        <input type="file" name="header_image" class="form-control" accept="image/*">
    </div>


    <!-- ENTITY NAME, FUND CLUSTER, ICS NO -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">ENTITY NAME</label>
            <input type="text" class="form-control" name="entity_name"
                value="<?= htmlspecialchars($ics_data['entity_name']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">FUND CLUSTER</label>
            <input type="text" class="form-control" name="fund_cluster"
                value="<?= htmlspecialchars($ics_data['fund_cluster']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">ICS NO.</label>
            <input type="text" class="form-control" name="ics_no"
                value="<?= htmlspecialchars($ics_data['ics_no']) ?>">
        </div>
    </div>

    <!-- FOOTER -->
    <table class="table table-borderless mt-5" style="width:100%; text-align:center;">
        <tr>
            <td style="width:50%; text-align:left; font-weight:bold;">Received from:</td>
            <td style="width:50%; text-align:left; font-weight:bold;">Received by:</td>
        </tr>
        <tr>
            <td>
                <input type="text" name="received_from_name"
                    class="form-control text-center fw-bold"
                    value="<?= htmlspecialchars($ics_data['received_from_name']) ?>"
                    placeholder="Enter name" style="text-decoration:underline;">
            </td>
            <td>
                <input type="text" name="received_by_name"
                    class="form-control text-center fw-bold"
                    value="<?= htmlspecialchars($ics_data['received_by_name']) ?>"
                    placeholder="Enter name" style="text-decoration:underline;">
            </td>
        </tr>
        <tr>
            <td>
                <input type="text" name="received_from_position"
                    class="form-control text-center"
                    value="<?= htmlspecialchars($ics_data['received_from_position']) ?>"
                    placeholder="Enter position">
            </td>
            <td>
                <input type="text" name="received_by_position"
                    class="form-control text-center"
                    value="<?= htmlspecialchars($ics_data['received_by_position']) ?>"
                    placeholder="Enter position">
            </td>
        </tr>
        <tr>
            <td style="height:30px;"></td>
            <td></td>
        </tr>
        <tr>
            <td>
                <input type="date" name="received_from_date"
                    class="form-control text-center"
                    value="<?= !empty($ics_data['created_at']) ? htmlspecialchars(date('Y-m-d', strtotime($ics_data['created_at']))) : date('Y-m-d') ?>">
            </td>
            <td>
                <input type="date" name="received_by_date"
                    class="form-control text-center"
                    value="<?= !empty($ics_data['created_at']) ? htmlspecialchars(date('Y-m-d', strtotime($ics_data['created_at']))) : date('Y-m-d') ?>">
            </td>
        </tr>
    </table>

    <!-- SAVE BUTTON -->
    <div class="text-start mt-4">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send-check-fill"></i>Save</button>
    </div>
</form>