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
        <div class="mb-3" style="text-align:center; display: none;">
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
          /* Enhanced IIRUP Table Styling - Matching iirup_form.php */
          .excel-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 11px;
            text-align: center;
            table-layout: auto;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: visible;
            margin: 20px 0;
            position: relative;
            z-index: 1;
          }

          .excel-table th,
          .excel-table td {
            border: 1px solid #ddd;
            padding: 4px 3px;
            vertical-align: middle;
            position: relative;
            overflow: visible;
            font-size: 10px;
            line-height: 1.2;
          }

          /* Header Styling */
          .excel-table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 7px;
            line-height: 1.0;
            border: 1px solid #6c757d;
            padding: 2px 1px;
            height: 20px;
            vertical-align: middle;
            position: sticky;
            top: 0;
            z-index: 10;
          }

          /* Section Headers with Different Colors */
          .excel-table thead tr:first-child th:nth-child(1) {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1565c0;
            border-color: #1976d2;
          }

          .excel-table thead tr:first-child th:nth-child(2) {
            background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
            color: #7b1fa2;
            border-color: #8e24aa;
          }

          .excel-table thead tr:first-child th:nth-child(3) {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            color: #2e7d32;
            border-color: #388e3c;
          }

          .excel-table thead tr:first-child th:nth-child(4) {
            background: linear-gradient(135deg, #fff3e0 0%, #ffcc02 100%);
            color: #f57c00;
            border-color: #ff9800;
          }

          .excel-table thead tr:first-child th:nth-child(5) {
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%);
            color: #c2185b;
            border-color: #e91e63;
          }

          /* Individual Column Headers */
          .excel-table thead tr:nth-child(2) th {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            font-size: 6px;
            padding: 1px 1px;
            min-width: 50px;
            line-height: 1.0;
            height: 18px;
            vertical-align: middle;
          }

          /* Specific Column Widths for Better Readability */
          .excel-table th:nth-child(1), .excel-table td:nth-child(1) { min-width: 80px; } /* Date Acquired */
          .excel-table th:nth-child(2), .excel-table td:nth-child(2) { min-width: 150px; } /* Particulars */
          .excel-table th:nth-child(3), .excel-table td:nth-child(3) { min-width: 80px; } /* Property No */
          .excel-table th:nth-child(4), .excel-table td:nth-child(4) { min-width: 40px; }  /* Qty */
          .excel-table th:nth-child(5), .excel-table td:nth-child(5) { min-width: 70px; }  /* Unit Cost */
          .excel-table th:nth-child(6), .excel-table td:nth-child(6) { min-width: 70px; }  /* Total Cost */
          .excel-table th:nth-child(7), .excel-table td:nth-child(7) { min-width: 90px; }  /* Accumulated Depreciation */
          .excel-table th:nth-child(8), .excel-table td:nth-child(8) { min-width: 90px; }  /* Accumulated Impairment */
          .excel-table th:nth-child(9), .excel-table td:nth-child(9) { min-width: 70px; }  /* Carrying Amount */
          .excel-table th:nth-child(10), .excel-table td:nth-child(10) { min-width: 70px; } /* Remarks */
          .excel-table th:nth-child(11), .excel-table td:nth-child(11) { min-width: 60px; } /* Sale */
          .excel-table th:nth-child(12), .excel-table td:nth-child(12) { min-width: 60px; } /* Transfer */
          .excel-table th:nth-child(13), .excel-table td:nth-child(13) { min-width: 60px; } /* Destruction */
          .excel-table th:nth-child(14), .excel-table td:nth-child(14) { min-width: 60px; } /* Others */
          .excel-table th:nth-child(15), .excel-table td:nth-child(15) { min-width: 60px; } /* Total */
          .excel-table th:nth-child(16), .excel-table td:nth-child(16) { min-width: 70px; } /* Appraised Value */
          .excel-table th:nth-child(17), .excel-table td:nth-child(17) { min-width: 60px; } /* OR No */
          .excel-table th:nth-child(18), .excel-table td:nth-child(18) { min-width: 60px; } /* Amount */
          .excel-table th:nth-child(19), .excel-table td:nth-child(19) { min-width: 80px; } /* Dept/Office */
          .excel-table th:nth-child(20), .excel-table td:nth-child(20) { min-width: 50px; } /* Code */
          .excel-table th:nth-child(21), .excel-table td:nth-child(21) { min-width: 80px; } /* Date Received */
          .excel-table th:nth-child(22), .excel-table td:nth-child(22) { min-width: 70px; } /* Actions */

          /* Row Styling */
          .excel-table tbody tr {
            transition: background-color 0.2s ease;
            position: relative;
            z-index: 1;
          }

          .excel-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
          }

          .excel-table tbody tr:hover {
            background-color: #e3f2fd;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
          }

          /* Input Field Styling */
          .excel-table input,
          .excel-table select {
            width: 100%;
            border: 1px solid transparent;
            text-align: center;
            font-size: 9px;
            padding: 2px 1px;
            background-color: transparent;
            border-radius: 2px;
            transition: all 0.2s ease;
            height: 22px;
            min-height: 22px;
          }

          .excel-table input:focus,
          .excel-table select:focus {
            outline: none;
            border-color: #007bff;
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
          }

          .excel-table input[readonly],
          .excel-table input[disabled] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
          }

          /* Date Input Styling */
          .excel-table input[type="date"] {
            font-size: 10px;
            padding: 3px;
          }

          /* Number Input Styling */
          .excel-table input[type="number"] {
            text-align: right;
            padding-right: 6px;
          }

          /* Select Dropdown Styling */
          .excel-table select {
            cursor: pointer;
            font-size: 10px;
            padding: 3px;
          }

          /* Button Styling in Table */
          .excel-table .btn {
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 3px;
            margin: 0 1px;
          }

          /* Particulars Column Special Styling */
          .excel-table .particulars {
            text-align: left;
            font-weight: 500;
            color: #495057;
          }

          /* Table Container */
          .table-responsive {
            overflow-x: auto;
            margin-bottom: 1rem;
            max-height: 70vh;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
          }

          /* Button styling */
          .view-row-details {
            font-size: 8px;
            padding: 2px 6px;
            white-space: nowrap;
          }

          /* Responsive Design */
          @media (max-width: 1400px) {
            .excel-table {
              font-size: 10px;
            }
            .excel-table thead th {
              font-size: 6px;
            }
            .excel-table input, .excel-table select {
              font-size: 8px;
            }
          }

          @media (max-width: 1200px) {
            .excel-table {
              font-size: 9px;
            }
            .excel-table thead th {
              font-size: 5px;
            }
            .excel-table input, .excel-table select {
              font-size: 7px;
            }
          }

          @media (max-width: 992px) {
            .table-responsive {
              overflow-x: auto;
            }
          }
        </style>

        <div class="table-responsive">
          <table class="excel-table">
          <thead>
            <tr>
              <th colspan="10">INVENTORY</th>
              <th colspan="6">INSPECTION and DISPOSAL</th>
              <th colspan="2">RECORD OF SALES</th>
              <th rowspan="2">DEPT/OFFICE</th>
              <th rowspan="2">CODE</th>
              <th rowspan="2">DATE RECEIVED</th>
              <th rowspan="2">ACTIONS</th>
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
                  <td class="particulars">
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
                  <td><input type="date" value="<?= htmlspecialchars($it['date_received']) ?>" disabled></td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-info view-row-details" 
                            data-item-id="<?= (int)$it['item_id'] ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#rowDetailsModal"
                            title="View full row details">
                      <i class="bi bi-eye"></i> View
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="21" class="text-muted">No items found.</td></tr>
            <?php endif; ?>
          </tbody>
          </table>
        </div>

        <!-- Keep datalist for structure parity -->
        <datalist id="asset_descriptions"></datalist>

        <!-- Row Details Modal -->
        <div class="modal fade" id="rowDetailsModal" tabindex="-1" aria-labelledby="rowDetailsModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="rowDetailsModalLabel">
                  <i class="bi bi-eye"></i> Row Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <!-- Basic Information -->
                  <div class="col-12">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                      <i class="bi bi-info-circle"></i> Basic Information
                    </h6>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Date Acquired</label>
                    <p class="form-control-plaintext" id="view_date_acquired">-</p>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label fw-bold">Particulars/Articles</label>
                    <p class="form-control-plaintext" id="view_particulars">-</p>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Property No.</label>
                    <p class="form-control-plaintext" id="view_property_no">-</p>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label fw-bold">Quantity</label>
                    <p class="form-control-plaintext" id="view_qty">-</p>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-bold">Unit Cost</label>
                    <p class="form-control-plaintext" id="view_unit_cost">-</p>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-bold">Total Cost</label>
                    <p class="form-control-plaintext" id="view_total_cost">-</p>
                  </div>

                  <!-- Financial Information -->
                  <div class="col-12 mt-4">
                    <h6 class="text-success border-bottom pb-2 mb-3">
                      <i class="bi bi-currency-dollar"></i> Financial Information
                    </h6>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Accumulated Depreciation</label>
                    <p class="form-control-plaintext" id="view_accumulated_depreciation">-</p>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Accumulated Impairment</label>
                    <p class="form-control-plaintext" id="view_accumulated_impairment">-</p>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Carrying Amount</label>
                    <p class="form-control-plaintext" id="view_carrying_amount">-</p>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold">Remarks</label>
                    <p class="form-control-plaintext" id="view_remarks">-</p>
                  </div>

                  <!-- Disposal Information -->
                  <div class="col-12 mt-4">
                    <h6 class="text-warning border-bottom pb-2 mb-3">
                      <i class="bi bi-recycle"></i> Disposal Information
                    </h6>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-bold">Sale</label>
                    <p class="form-control-plaintext" id="view_sale">-</p>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-bold">Transfer</label>
                    <p class="form-control-plaintext" id="view_transfer">-</p>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-bold">Destruction</label>
                    <p class="form-control-plaintext" id="view_destruction">-</p>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-bold">Others</label>
                    <p class="form-control-plaintext" id="view_others">-</p>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Total Disposal</label>
                    <p class="form-control-plaintext" id="view_total">-</p>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Appraised Value</label>
                    <p class="form-control-plaintext" id="view_appraised_value">-</p>
                  </div>

                  <!-- Sales Record -->
                  <div class="col-12 mt-4">
                    <h6 class="text-info border-bottom pb-2 mb-3">
                      <i class="bi bi-receipt"></i> Sales Record
                    </h6>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">OR Number</label>
                    <p class="form-control-plaintext" id="view_or_no">-</p>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Amount</label>
                    <p class="form-control-plaintext" id="view_amount">-</p>
                  </div>

                  <!-- Administrative Information -->
                  <div class="col-12 mt-4">
                    <h6 class="text-secondary border-bottom pb-2 mb-3">
                      <i class="bi bi-building"></i> Administrative Information
                    </h6>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Department/Office</label>
                    <p class="form-control-plaintext" id="view_dept_office">-</p>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Code</label>
                    <p class="form-control-plaintext" id="view_code">-</p>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Date Received</label>
                    <p class="form-control-plaintext" id="view_date_received">-</p>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="bi bi-x-circle"></i> Close
                </button>
              </div>
            </div>
          </div>
        </div>

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
          <a href="generate_iirup_pdf.php?id=<?= (int)$iirup_id ?>" target="_blank" class="btn btn-success"><i class="bi bi-printer"></i> Print</a>
          <a href="saved_iirup.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle View button click - populate modal with row details
      document.querySelectorAll('.view-row-details').forEach(button => {
        button.addEventListener('click', function() {
          const row = this.closest('tr');
          
          // Get all the data from the row
          const inputs = row.querySelectorAll('input, select');
          
          // Populate modal with row data
          document.getElementById('view_date_acquired').textContent = inputs[0].value || '-';
          document.getElementById('view_particulars').textContent = inputs[1].value || '-';
          document.getElementById('view_property_no').textContent = inputs[2].value || '-';
          document.getElementById('view_qty').textContent = inputs[3].value || '-';
          document.getElementById('view_unit_cost').textContent = inputs[4].value ? '₱' + parseFloat(inputs[4].value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-';
          document.getElementById('view_total_cost').textContent = inputs[5].value ? '₱' + parseFloat(inputs[5].value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-';
          document.getElementById('view_accumulated_depreciation').textContent = inputs[6].value ? '₱' + parseFloat(inputs[6].value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-';
          document.getElementById('view_accumulated_impairment').textContent = inputs[7].value ? '₱' + parseFloat(inputs[7].value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-';
          document.getElementById('view_carrying_amount').textContent = inputs[8].value ? '₱' + parseFloat(inputs[8].value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-';
          document.getElementById('view_remarks').textContent = inputs[9].value || '-';
          document.getElementById('view_sale').textContent = inputs[10].value || '-';
          document.getElementById('view_transfer').textContent = inputs[11].value || '-';
          document.getElementById('view_destruction').textContent = inputs[12].value || '-';
          document.getElementById('view_others').textContent = inputs[13].value || '-';
          document.getElementById('view_total').textContent = inputs[14].value ? '₱' + parseFloat(inputs[14].value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-';
          document.getElementById('view_appraised_value').textContent = inputs[15].value ? '₱' + parseFloat(inputs[15].value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-';
          document.getElementById('view_or_no').textContent = inputs[16].value || '-';
          document.getElementById('view_amount').textContent = inputs[17].value ? '₱' + parseFloat(inputs[17].value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-';
          document.getElementById('view_dept_office').textContent = inputs[18].value || '-';
          document.getElementById('view_code').textContent = inputs[19].value || '-';
          document.getElementById('view_date_received').textContent = inputs[20].value || '-';
        });
      });
    });
  </script>
</body>
