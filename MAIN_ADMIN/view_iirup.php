<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// IIRUP id to view
$iirup_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($iirup_id <= 0) { die('Invalid IIRUP ID.'); }

// Header/footer
$stmt = $conn->prepare("SELECT id, accountable_officer, designation, office, header_image,
  footer_accountable_officer, footer_authorized_official, footer_designation_officer, footer_designation_official
  FROM iirup_form WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $iirup_id);
$stmt->execute();
$res = $stmt->get_result();
$hdr = $res->fetch_assoc();
$stmt->close();
if (!$hdr) { die('IIRUP not found.'); }

// Items
$stmt = $conn->prepare("SELECT item_id, asset_id, date_acquired, particulars, property_no, qty, unit_cost, total_cost,
  accumulated_depreciation, accumulated_impairment_losses, carrying_amount, remarks, sale, transfer, destruction, others,
  total, appraised_value, or_no, amount, dept_office, code, red_tag, date_received
  FROM iirup_items WHERE iirup_id = ? ORDER BY item_id ASC");
$stmt->bind_param('i', $iirup_id);
$stmt->execute();
$items_rs = $stmt->get_result();
$items = $items_rs->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Offices for select (keep structure parity, but disable input)
$offices = [];
$res_off = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
if ($res_off && $res_off->num_rows) {
  while ($r = $res_off->fetch_assoc()) { $offices[] = $r; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View IIRUP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
  <?php include 'includes/sidebar.php' ?>
  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container py-4">
      <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type'] ?? 'success') ?> alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['flash']['message'] ?? 'Changes saved successfully.') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
      <?php elseif (isset($_GET['success']) && $_GET['success'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          Changes saved successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <div class="mb-3 text-center">
        <?php if (!empty($hdr['header_image'])): ?>
          <img src="../img/<?= htmlspecialchars($hdr['header_image']) ?>" alt="Header Image" style="max-height: 120px; display:block; margin:0 auto;">
          <div style="font-size: 12px; color: gray; margin-top: 5px;">As of <?= date('F, Y') ?></div>
        <?php endif; ?>
      </div>

      <!-- Removed top-right 'View Saved IIRUP' button per request -->

      <form method="POST" action="save_iirup_view.php" enctype="multipart/form-data">
        <input type="hidden" name="iirup_id" value="<?= (int)$iirup_id ?>">
        <div class="mb-3" style="text-align:center;">
          <label class="form-label">Header Image</label>
          <input type="file" name="header_image" class="form-control" style="max-width: 400px; margin: 0 auto;">
        </div>

        <div style="display: flex; justify-content: space-between; text-align: center; margin-top: 10px;" class="mb-3">
          <div style="flex: 1; margin: 0 5px;">
            <input type="text" name="accountable_officer" value="<?= htmlspecialchars($hdr['accountable_officer']) ?>" style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
            <br><small><em>(Name of Accountable Officer)</em></small>
          </div>
          <div style="flex: 1; margin: 0 5px;">
            <input type="text" name="designation" value="<?= htmlspecialchars($hdr['designation']) ?>" style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
            <br><small><em>(Designation)</em></small>
          </div>
          <div style="flex: 1; margin: 0 5px;">
            <select name="office" style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
              <option value="">-- Select Office --</option>
              <?php foreach ($offices as $o): ?>
                <option value="<?= htmlspecialchars($o['office_name']) ?>" <?= ($hdr['office'] == $o['office_name']) ? 'selected' : '' ?>><?= htmlspecialchars($o['office_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <br><small><em>(Department/Office)</em></small>
          </div>
        </div>

        <style>
          .excel-table { border-collapse: collapse; width: 100%; font-size: 10px; text-align: center; table-layout: fixed; }
          .excel-table th, .excel-table td { border: 1px solid #000; padding: 2px 3px; vertical-align: middle; }
          .excel-table thead th { background-color: #fff; font-weight: bold; }
          .excel-table input, .excel-table select { width: 100%; border: none; text-align: center; font-size: 10px; padding: 0; }
        </style>

        <table class="excel-table">
          <thead>
            <tr>
              <th colspan="10">INVENTORY</th>
              <th colspan="6">INSPECTION and DISPOSAL</th>
              <th colspan="2">RECORD OF SALES</th>
              <th rowspan="2">DEPT/OFFICE</th>
              <th rowspan="2">CODE</th>
              <th rowspan="2">RED TAG</th>
              <th rowspan="2">DATE RECEIVED</th>
            </tr>
            <tr>
              <th>Date Acquired<br>(1)</th>
              <th>Particulars/ Articles<br>(2)</th>
              <th>Property No.<br>(3)</th>
              <th>Qty<br>(4)</th>
              <th>Unit Cost<br>(5)</th>
              <th>Total Cost<br>(6)</th>
              <th>Accumulated Depreciation<br>(7)</th>
              <th>Accumulated Impairment Losses<br>(8)</th>
              <th>Carrying Amount<br>(9)</th>
              <th>Remarks<br>(10)</th>
              <th>Sale<br>(11)</th>
              <th>Transfer<br>(12)</th>
              <th>Destruction<br>(13)</th>
              <th>Others (Specify)<br>(14)</th>
              <th>Total<br>(15)</th>
              <th>Appraised Value<br>(16)</th>
              <th>OR No.<br>(17)</th>
              <th>Amount<br>(18)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td><input type="date" value="<?= htmlspecialchars($it['date_acquired']) ?>" disabled></td>
                  <td>
                    <input type="text" value="<?= htmlspecialchars($it['particulars']) ?>" disabled>
                    <input type="hidden" value="<?= (int)$it['asset_id'] ?>">
                  </td>
                  <td><input type="text" value="<?= htmlspecialchars($it['property_no']) ?>" disabled></td>
                  <td><input type="number" value="<?= htmlspecialchars($it['qty']) ?>" disabled></td>
                  <td><input type="number" step="0.01" value="<?= htmlspecialchars($it['unit_cost']) ?>" disabled></td>
                  <td><input type="number" step="0.01" value="<?= htmlspecialchars($it['total_cost']) ?>" disabled></td>
                  <td><input type="number" step="0.01" value="<?= htmlspecialchars($it['accumulated_depreciation']) ?>" disabled></td>
                  <td><input type="number" step="0.01" value="<?= htmlspecialchars($it['accumulated_impairment_losses']) ?>" disabled></td>
                  <td><input type="number" step="0.01" value="<?= htmlspecialchars($it['carrying_amount']) ?>" disabled></td>
                  <td>
                    <select name="remarks[<?= (int)$it['item_id'] ?>]">
                      <option value="Unserviceable" <?= ($it['remarks'] === 'Unserviceable') ? 'selected' : '' ?>>Unserviceable</option>
                      <option value="Serviceable" <?= ($it['remarks'] === 'Serviceable') ? 'selected' : '' ?>>Serviceable</option>
                    </select>
                  </td>
                  <td><input type="text" value="<?= htmlspecialchars($it['sale']) ?>" disabled></td>
                  <td><input type="text" value="<?= htmlspecialchars($it['transfer']) ?>" disabled></td>
                  <td><input type="text" value="<?= htmlspecialchars($it['destruction']) ?>" disabled></td>
                  <td><input type="text" value="<?= htmlspecialchars($it['others']) ?>" disabled></td>
                  <td><input type="number" step="0.01" value="<?= htmlspecialchars($it['total']) ?>" disabled></td>
                  <td><input type="number" step="0.01" value="<?= htmlspecialchars($it['appraised_value']) ?>" disabled></td>
                  <td><input type="text" value="<?= htmlspecialchars($it['or_no']) ?>" disabled></td>
                  <td><input type="number" step="0.01" value="<?= htmlspecialchars($it['amount']) ?>" disabled></td>
                  <td><input type="text" value="<?= htmlspecialchars($it['dept_office']) ?>" disabled></td>
                  <td><input type="text" value="<?= htmlspecialchars($it['code']) ?>" disabled></td>
                  <td><input type="text" value="<?= htmlspecialchars($it['red_tag']) ?>" disabled></td>
                  <td><input type="date" value="<?= htmlspecialchars($it['date_received']) ?>" disabled></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="22" class="text-muted">No items found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Keep datalist for structure parity -->
        <datalist id="asset_descriptions"></datalist>

        <!-- FOOTER SECTION mirroring form -->
        <div style="margin-top: 30px; font-size: 12px; line-height: 1.5;">
          <table style="width: 100%; border-collapse: collapse; text-align: center;">
            <tr>
              <td colspan="2" style="padding: 5px; text-align: left;">I HEREBY request inspection and disposition, pursuant to Section 79 of PD 1445, of the property enumerated above.</td>
              <td style="padding: 5px; text-align: left;">I CERTIFY that I have inspected each and every article enumerated in this report, and that the disposition made thereof was, in my judgment, the best for the public interest.</td>
              <td style="padding: 5px; text-align: left;">I CERTIFY that I have witnessed the disposition of the articles enumerated on this report this ____ day of _____________, _____. </td>
            </tr>
            <tr><td colspan="4" style="height:30px;"></td></tr>
            <tr>
              <td>Requested by:</td>
              <td>Approved by:</td>
              <td>(Signature over Printed Name of Inspection Officer)</td>
              <td>(Signature over Printed Name of Witness)</td>
            </tr>
            <tr><td colspan="4" style="height:50px;"></td></tr>
            <tr>
              <td><input type="text" name="footer_accountable_officer" value="<?= htmlspecialchars($hdr['footer_accountable_officer'] ?? '') ?>" style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;"></td>
              <td><input type="text" name="footer_authorized_official" value="<?= htmlspecialchars($hdr['footer_authorized_official'] ?? '') ?>" style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;"></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td>(Signature over Printed Name of Accountable Officer)</td>
              <td>(Signature over Printed Name of Authorized Official)</td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td>
                <input type="text" name="footer_designation_officer" value="<?= htmlspecialchars($hdr['footer_designation_officer'] ?? '') ?>" style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
                <br>(Designation of Accountable Officer)
              </td>
              <td>
                <input type="text" name="footer_designation_official" value="<?= htmlspecialchars($hdr['footer_designation_official'] ?? '') ?>" style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
                <br>(Designation of Authorized Official)
              </td>
              <td></td>
              <td></td>
            </tr>
          </table>
        </div>

        <div class="d-flex gap-2 my-3">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
          <button type="button" class="btn btn-success" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
          <a href="saved_iirup.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
