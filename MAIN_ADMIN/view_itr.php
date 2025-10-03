<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// ITR id to view
$itr_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : null;

if ($itr_id <= 0) { 
  die('Invalid ITR ID.'); 
}

// Fetch ITR form data
$stmt = $conn->prepare("SELECT itr_id, header_image, entity_name, fund_cluster, from_accountable_officer, 
  to_accountable_officer, itr_no, date, transfer_type, reason_for_transfer, approved_by, approved_designation, 
  approved_date, released_by, released_designation, released_date, received_by, received_designation, received_date
  FROM itr_form WHERE itr_id = ? LIMIT 1");
$stmt->bind_param('i', $itr_id);
$stmt->execute();
$res = $stmt->get_result();
$itr = $res->fetch_assoc();
$stmt->close();

if (!$itr) { 
  die('ITR not found.'); 
}

// Fetch ITR items
$stmt = $conn->prepare("SELECT item_id, itr_id, date_acquired, property_no, asset_id, description, amount, condition_of_PPE
  FROM itr_items WHERE itr_id = ? ORDER BY item_id ASC");
$stmt->bind_param('i', $itr_id);
$stmt->execute();
$items_rs = $stmt->get_result();
$items = $items_rs->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View ITR - <?= htmlspecialchars($itr['itr_no'] ?? 'N/A') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    .itr-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border: 2px solid #dee2e6;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .itr-section {
      background: #fff;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .field-group {
      background: #f8f9fa;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
    }
    .field-label {
      font-weight: 600;
      color: #495057;
      font-size: 0.9rem;
    }
    .field-value {
      color: #212529;
      font-size: 1rem;
      margin-top: 2px;
    }
    .items-table {
      font-size: 0.9rem;
    }
    .items-table th {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      font-weight: 600;
      text-align: center;
      padding: 12px 8px;
    }
    .items-table td {
      padding: 10px 8px;
      vertical-align: middle;
    }
    .signature-section {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
    }
    .signature-box {
      text-align: center;
      padding: 15px;
      border: 1px solid #dee2e6;
      border-radius: 5px;
      background: white;
      min-height: 80px;
    }
    .print-btn {
      position: fixed;
      top: 100px;
      right: 20px;
      z-index: 1000;
    }
    @media print {
      .no-print { display: none !important; }
      .print-btn { display: none !important; }
      body { margin: 0; }
      .container { max-width: none; margin: 0; padding: 0; }
    }
  </style>
</head>
<body>
  <div class="no-print">
    <?php include 'includes/sidebar.php' ?>
  </div>
  
  <div class="main">
    <div class="no-print">
      <?php include 'includes/topbar.php' ?>
    </div>

    <div class="container-fluid py-4">
      <!-- Navigation -->
      <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4 class="fw-bold"><i class="bi bi-file-earmark-text"></i> View ITR Form</h4>
        <div>
          <a href="saved_itr.php<?= $form_id ? '?id=' . $form_id : '' ?>" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left"></i> Back to Saved ITR
          </a>
          <a href="generate_itr_pdf.php?id=<?= $itr_id ?>" target="_blank" class="btn btn-primary">
            <i class="bi bi-file-earmark-pdf"></i> Generate PDF
          </a>
        </div>
      </div>

      <!-- Flash Messages -->
      <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type'] ?? 'success') ?> alert-dismissible fade show no-print" role="alert">
          <?= htmlspecialchars($_SESSION['flash']['message'] ?? 'Action completed.') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
      <?php endif; ?>

      <!-- ITR Header Image -->
      <?php if (!empty($itr['header_image'])): ?>
        <div class="text-center mb-4">
          <img src="../img/<?= htmlspecialchars($itr['header_image']) ?>" 
               alt="Header Image" 
               class="img-fluid" 
               style="max-height: 150px;">
        </div>
      <?php endif; ?>

      <!-- ITR Form Content -->
      <form id="updateItrForm" method="POST" action="update_itr.php">
        <input type="hidden" name="itr_id" value="<?= $itr_id ?>">
        <input type="hidden" name="form_id" value="<?= $form_id ?>">
        
        <div class="itr-header">
          <div class="text-center mb-3">
            <h3 class="fw-bold text-primary">INVENTORY TRANSFER RECEIPT</h3>
            <h5 class="text-muted">ITR No: <?= htmlspecialchars($itr['itr_no'] ?? 'N/A') ?></h5>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <div class="field-group">
                <label class="field-label">Entity Name <span class="text-danger">*</span></label>
                <input type="text" name="entity_name" class="form-control" 
                       value="<?= htmlspecialchars($itr['entity_name'] ?? '') ?>" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="field-group">
                <label class="field-label">Fund Cluster <span class="text-danger">*</span></label>
                <input type="text" name="fund_cluster" class="form-control" 
                       value="<?= htmlspecialchars($itr['fund_cluster'] ?? '') ?>" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="field-group">
                <div class="field-label">Date</div>
                <div class="field-value"><?= $itr['date'] ? date('F d, Y', strtotime($itr['date'])) : 'N/A' ?></div>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-md-6">
              <div class="field-group">
                <div class="field-label">From Accountable Officer</div>
                <div class="field-value"><?= htmlspecialchars($itr['from_accountable_officer'] ?? 'N/A') ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="field-group">
                <div class="field-label">To Accountable Officer</div>
                <div class="field-value"><?= htmlspecialchars($itr['to_accountable_officer'] ?? 'N/A') ?></div>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-md-6">
              <div class="field-group">
                <label class="field-label">Transfer Type <span class="text-danger">*</span></label>
                <select name="transfer_type" class="form-select" required>
                  <option value="">-- Select Transfer Type --</option>
                  <option value="Donation" <?= ($itr['transfer_type'] === 'Donation') ? 'selected' : '' ?>>Donation</option>
                  <option value="Reassignment" <?= ($itr['transfer_type'] === 'Reassignment') ? 'selected' : '' ?>>Reassignment</option>
                  <option value="Relocate" <?= ($itr['transfer_type'] === 'Relocate') ? 'selected' : '' ?>>Relocate</option>
                  <option value="Others" <?= (!in_array($itr['transfer_type'], ['Donation', 'Reassignment', 'Relocate']) && !empty($itr['transfer_type'])) ? 'selected' : '' ?>>Others</option>
                </select>
                <?php if (!in_array($itr['transfer_type'], ['Donation', 'Reassignment', 'Relocate']) && !empty($itr['transfer_type'])): ?>
                  <input type="text" name="transfer_type_other" class="form-control mt-2" 
                         placeholder="Specify other transfer type" 
                         value="<?= htmlspecialchars($itr['transfer_type']) ?>">
                <?php else: ?>
                  <input type="text" name="transfer_type_other" class="form-control mt-2" 
                         placeholder="Specify other transfer type" style="display: none;">
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6">
              <div class="field-group">
                <label class="field-label">Reason for Transfer <span class="text-danger">*</span></label>
                <textarea name="reason_for_transfer" class="form-control" rows="3" required><?= htmlspecialchars($itr['reason_for_transfer'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

      <!-- ITR Items Table -->
      <div class="itr-section">
        <h5 class="fw-bold mb-3"><i class="bi bi-list-check"></i> Transfer Items</h5>
        
        <?php if (!empty($items)): ?>
          <div class="table-responsive">
            <table class="table table-bordered items-table">
              <thead>
                <tr>
                  <th style="width: 12%">Date Acquired</th>
                  <th style="width: 8%">Item No.</th>
                  <th style="width: 15%">ICS & PAR No./Date</th>
                  <th style="width: 35%">Description / Property No</th>
                  <th style="width: 15%">Unit Price</th>
                  <th style="width: 15%">Condition of Inventory</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $index => $item): ?>
                  <tr>
                    <td><?= $item['date_acquired'] ? date('M d, Y', strtotime($item['date_acquired'])) : 'N/A' ?></td>
                    <td class="text-center"><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($item['property_no'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['description'] ?? '') ?></td>
                    <td class="text-end">₱<?= number_format($item['amount'] ?? 0, 2) ?></td>
                    <td class="text-center">
                      <span class="badge bg-<?= 
                        (strtolower($item['condition_of_PPE'] ?? '') === 'serviceable') ? 'success' : 
                        ((strtolower($item['condition_of_PPE'] ?? '') === 'unserviceable') ? 'danger' : 'warning') 
                      ?>">
                        <?= htmlspecialchars($item['condition_of_PPE'] ?? 'N/A') ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="table-light">
                  <td colspan="4" class="text-end fw-bold">Total Value:</td>
                  <td class="text-end fw-bold">₱<?= number_format(array_sum(array_column($items, 'amount')), 2) ?></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No items found for this ITR.
          </div>
        <?php endif; ?>
      </div>

        <!-- Signature Section -->
        <div class="signature-section">
          <h5 class="fw-bold mb-3"><i class="bi bi-pen"></i> Signatures & Approvals</h5>
          
          <div class="row g-3">
            <!-- Approved By -->
            <div class="col-md-4">
              <div class="signature-box">
                <div class="fw-bold border-bottom pb-2 mb-2">APPROVED BY:</div>
                <div class="mb-2">
                  <label class="form-label small">Name:</label>
                  <input type="text" name="approved_by" class="form-control form-control-sm" 
                         value="<?= htmlspecialchars($itr['approved_by'] ?? '') ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label small">Designation:</label>
                  <input type="text" name="approved_designation" class="form-control form-control-sm" 
                         value="<?= htmlspecialchars($itr['approved_designation'] ?? '') ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label small">Date:</label>
                  <input type="date" name="approved_date" class="form-control form-control-sm" 
                         value="<?= $itr['approved_date'] ?? '' ?>">
                </div>
              </div>
            </div>

            <!-- Released By -->
            <div class="col-md-4">
              <div class="signature-box">
                <div class="fw-bold border-bottom pb-2 mb-2">RELEASED BY:</div>
                <div class="mb-2">
                  <label class="form-label small">Name:</label>
                  <input type="text" name="released_by" class="form-control form-control-sm" 
                         value="<?= htmlspecialchars($itr['released_by'] ?? '') ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label small">Designation:</label>
                  <input type="text" name="released_designation" class="form-control form-control-sm" 
                         value="<?= htmlspecialchars($itr['released_designation'] ?? '') ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label small">Date:</label>
                  <input type="date" name="released_date" class="form-control form-control-sm" 
                         value="<?= $itr['released_date'] ?? '' ?>">
                </div>
              </div>
            </div>

            <!-- Received By -->
            <div class="col-md-4">
              <div class="signature-box">
                <div class="fw-bold border-bottom pb-2 mb-2">RECEIVED BY:</div>
                <div class="field-value fw-bold"><?= htmlspecialchars($itr['received_by'] ?? '') ?></div>
                <div class="field-label"><?= htmlspecialchars($itr['received_designation'] ?? '') ?></div>
                <div class="field-label mt-2">Date: <?= $itr['received_date'] ? date('M d, Y', strtotime($itr['received_date'])) : '___________' ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div class="text-center mt-4 no-print">
          <button type="submit" class="btn btn-success btn-lg">
            <i class="bi bi-save"></i> Save Changes
          </button>
          <button type="button" class="btn btn-secondary btn-lg ms-2" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Reset
          </button>
        </div>
      </form>

      <!-- Footer Info -->
      <div class="text-center mt-4 text-muted">
        <small>Generated on <?= date('F d, Y g:i A') ?> | ITR ID: <?= $itr_id ?></small>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Print functionality
    function printITR() {
      window.print();
    }

    // Keyboard shortcut for printing
    document.addEventListener('keydown', function(e) {
      if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printITR();
      }
    });

    // Transfer type dropdown handling
    document.addEventListener('DOMContentLoaded', function() {
      const transferTypeSelect = document.querySelector('select[name="transfer_type"]');
      const transferTypeOther = document.querySelector('input[name="transfer_type_other"]');

      function toggleOtherInput() {
        if (transferTypeSelect.value === 'Others') {
          transferTypeOther.style.display = 'block';
          transferTypeOther.required = true;
        } else {
          transferTypeOther.style.display = 'none';
          transferTypeOther.required = false;
          transferTypeOther.value = '';
        }
      }

      if (transferTypeSelect && transferTypeOther) {
        transferTypeSelect.addEventListener('change', toggleOtherInput);
        // Initialize on page load
        toggleOtherInput();
      }

      // Form submission handling
      const form = document.getElementById('updateItrForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          // Show loading state
          const submitBtn = form.querySelector('button[type="submit"]');
          if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
          }
        });
      }
    });
  </script>
</body>
</html>
