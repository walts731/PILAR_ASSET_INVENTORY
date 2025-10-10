<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$form_id = $_GET['id'] ?? '';
$ris_form_id = $_GET['form_id'] ?? '';

// Fetch RIS form by ID
$stmt = $conn->prepare("SELECT f.*, o.office_name 
                        FROM ris_form f
                        LEFT JOIN offices o ON f.office_id = o.id
                        WHERE f.id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$ris_data = $result->fetch_assoc() ?? [];
$stmt->close();

// Fetch RIS items with resolved unit name
$item_stmt = $conn->prepare("SELECT ri.*, COALESCE(u.unit_name, ri.unit) AS unit_name
                             FROM ris_items ri
                             LEFT JOIN unit u ON (u.id = ri.unit OR u.unit_name = ri.unit)
                             WHERE ri.ris_form_id = ?");
$item_stmt->bind_param("i", $form_id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();
$ris_items = $item_result->fetch_all(MYSQLI_ASSOC);
$item_stmt->close();

// Fetch offices for dropdown
$offices_result = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
$offices = $offices_result->fetch_all(MYSQLI_ASSOC);

// Fetch units for dropdown
$units_result = $conn->query("SELECT id, unit_name FROM unit ORDER BY unit_name ASC");
$units = $units_result ? $units_result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php 
    // Prepare display values and keep originals for fallback
    $originalRisNo = $ris_data['ris_no'] ?? '';
    $originalSaiNo = $ris_data['sai_no'] ?? '';
    $risOfficeDisplay = ($ris_data['office_name'] ?: ($ris_data['division'] ?? ''));
    $risNoDisplay = preg_replace('/\{OFFICE\}|OFFICE/', $risOfficeDisplay, $originalRisNo);
    $saiNoDisplay = preg_replace('/\{OFFICE\}|OFFICE/', $risOfficeDisplay, $originalSaiNo);

    // Fetch active templates for dynamic previews (same as ris_form.php)
    $ris_template = '';
    $sai_template = '';
    if ($st1 = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'ris_no' AND is_active = 1 LIMIT 1")) {
      $st1->execute();
      $r1 = $st1->get_result();
      if ($r1 && ($row = $r1->fetch_assoc())) { $ris_template = $row['format_template'] ?? ''; }
      $st1->close();
    }
    if ($st2 = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'sai_no' AND is_active = 1 LIMIT 1")) {
      $st2->execute();
      $r2 = $st2->get_result();
      if ($r2 && ($row2 = $r2->fetch_assoc())) { $sai_template = $row2['format_template'] ?? ''; }
      $st2->close();
    }
    ?>
    <title><?= $ris_data ? htmlspecialchars($risNoDisplay ?: 'RIS Form') : 'Form Viewer' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                RIS form has been updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="mb-3 text-center">
            <?php if (!empty($ris_data['header_image'])): ?>
                <img src="../img/<?= htmlspecialchars($ris_data['header_image']) ?>" class="img-fluid mb-3" style="max-width: 100%; height: auto; object-fit: contain;">
            <?php endif; ?>
        </div>

        <form method="post" action="update_ris.php">
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Division</label>
                    <input type="text" class="form-control" name="division" value="<?= htmlspecialchars($ris_data['division'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Responsibility Center</label>
                    <input type="text" class="form-control" name="responsibility_center" value="<?= htmlspecialchars($ris_data['responsibility_center'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">RIS No.</label>
                    <input type="text" class="form-control" id="ris_no" name="ris_no" value="<?= htmlspecialchars($risNoDisplay) ?>" data-original="<?= htmlspecialchars($originalRisNo) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($ris_data['date'] ?? '') ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Office/Unit</label>
                    <select class="form-select" id="office_id" name="office_id">
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= $office['id'] ?>" <?= ($office['id'] == $ris_data['office_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($office['office_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Code</label>
                    <input type="text" class="form-control" name="responsibility_code" value="<?= htmlspecialchars($ris_data['responsibility_code'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">SAI No.</label>
                    <input type="text" class="form-control" id="sai_no" name="sai_no" value="<?= htmlspecialchars($saiNoDisplay) ?>" data-original="<?= htmlspecialchars($originalSaiNo) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" name="sai_date" value="<?= htmlspecialchars($ris_data['date'] ?? '') ?>">
                </div>
            </div>

            <!-- ITEMS TABLE (READONLY) -->
            <table class="table table-bordered align-middle text-center">
                    <tr class="table-secondary">
                        <th colspan="4">REQUISITION</th>
                        <th colspan="3">ISSUANCE</th>
                    </tr>
                    <tr class="table-light">
                        <th>Stock No</th>
                        <th>Unit</th>
                        <th style="width: 30%;">Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ris_items)): ?>
                        <?php foreach ($ris_items as $item): ?>
                            <tr>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($item['stock_no']) ?>" readonly></td>
                                <td>
                                  <input type="hidden" name="ris_item_id[]" value="<?= (int)$item['id'] ?>">
                                  <select name="unit[]" class="form-select">
                                    <?php foreach ($units as $u): ?>
                                      <?php $selected = (strcasecmp($item['unit_name'], $u['unit_name']) === 0) ? 'selected' : ''; ?>
                                      <option value="<?= htmlspecialchars($u['unit_name']) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($u['unit_name']) ?>
                                      </option>
                                    <?php endforeach; ?>
                                  </select>
                                </td>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($item['description']) ?>" readonly></td>
                                <td><input type="number" class="form-control" value="<?= htmlspecialchars($item['quantity']) ?>" readonly></td>
                                <td><input type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($item['price']) ?>" readonly></td>
                                <td><input type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($item['total']) ?>" readonly></td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Nothing follows row -->
                        <?php if (!empty($ris_items)): ?>
                          <tr>
                            <td colspan="6" style="text-align: center; font-style: italic; padding: 8px 0; border-top: 1px solid #000;">— NOTHING FOLLOWS —</td>
                          </tr>
                        <?php endif; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold">Purpose</label>
                <input type="text" class="form-control" name="reason_for_transfer" value="<?= htmlspecialchars($ris_data['reason_for_transfer'] ?? '') ?>">
            </div>

            <!-- FOOTER -->
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
                        <td><input type="date" class="form-control" name="requested_by_date" value="<?= htmlspecialchars($ris_data['requested_by_date'] ?? '') ?>"></td>
                        <td><input type="date" class="form-control" name="approved_by_date" value="<?= htmlspecialchars($ris_data['approved_by_date'] ?? '') ?>"></td>
                        <td><input type="date" class="form-control" name="issued_by_date" value="<?= htmlspecialchars($ris_data['issued_by_date'] ?? '') ?>"></td>
                        <td><input type="date" class="form-control" name="received_by_date" value="<?= htmlspecialchars($ris_data['received_by_date'] ?? '') ?>"></td>
                    </tr>
                </tbody>
            </table>

            <div class="mb-5">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Changes
                </button>
                <a href="generate_ris_pdf.php?id=<?= $ris_data['id'] ?>" class="btn btn-info">
                    <i class="bi bi-printer"></i> Print / Export PDF
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
      // Match ris_form.php behavior: use active templates, full office name, date placeholders, and digit padding
      (function(){
        const RIS_TEMPLATE = <?= json_encode($ris_template) ?>;
        const SAI_TEMPLATE = <?= json_encode($sai_template) ?>;

        function applyDate(tpl, dateStr){
          const d = dateStr ? new Date(dateStr) : new Date();
          const Y = d.getFullYear().toString();
          const M = String(d.getMonth()+1).padStart(2,'0');
          const D = String(d.getDate()).padStart(2,'0');
          return (tpl||'')
            .replace(/\{YYYY\}|YYYY/g, Y)
            .replace(/\{YY\}|YY/g, Y.slice(-2))
            .replace(/\{MM\}|MM/g, M)
            .replace(/\{DD\}|DD/g, D)
            .replace(/\{YYYYMM\}|YYYYMM/g, Y+M)
            .replace(/\{YYYYMMDD\}|YYYYMMDD/g, Y+M+D);
        }
        function padDigits(tpl){
          return (tpl||'').replace(/\{(#+)\}/g,(m,hs)=>{ const w = hs.length; return '0'.repeat(Math.max(0,w-1))+'1'; });
        }
        function getFullOfficeName(){
          const sel = document.getElementById('office_id');
          if (!sel) return 'OFFICE';
          const opt = sel.options[sel.selectedIndex];
          const txt = opt ? (opt.text||'') : '';
          return sel.value ? ((txt || '').trim() || 'OFFICE') : 'OFFICE';
        }
        function updatePreviews(){
          const officeName = getFullOfficeName();
          // Prefer the main RIS date field
          const risDate = document.querySelector("input[name='date']");
          const dateVal = risDate ? risDate.value : '';

          const risField = document.getElementById('ris_no');
          const saiField = document.getElementById('sai_no');
          if (risField && RIS_TEMPLATE){
            let t = applyDate(RIS_TEMPLATE, dateVal).replace(/\{OFFICE\}|OFFICE/g, officeName);
            t = padDigits(t).replace(/--+/g,'-').replace(/^-|-$/g,'');
            risField.value = t;
          }
          if (saiField && SAI_TEMPLATE){
            let t2 = applyDate(SAI_TEMPLATE, dateVal).replace(/\{OFFICE\}|OFFICE/g, officeName);
            t2 = padDigits(t2).replace(/--+/g,'-').replace(/^-|-$/g,'');
            saiField.value = t2;
          }
        }

        const officeSel = document.getElementById('office_id');
        if (officeSel) officeSel.addEventListener('change', updatePreviews);
        const dateInputs = document.querySelectorAll("input[name='date'], input[name='sai_date']");
        dateInputs.forEach(d => d.addEventListener('change', updatePreviews));

        // Initialize on load
        updatePreviews();
      })();
    </script>
</body>

</html>