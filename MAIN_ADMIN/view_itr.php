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
  <?php $itrOfficeDisplay = ($itr['entity_name'] ?? ''); $itrNoDisplay = preg_replace('/\{OFFICE\}|OFFICE/', $itrOfficeDisplay, $itr['itr_no'] ?? ''); ?>
  <title>View ITR - <?= htmlspecialchars($itrNoDisplay ?: 'N/A') ?></title>
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
      <!-- Header with Navigation -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Inventory Transfer Receipt (ITR) Form - View/Edit</h4>
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

      <!-- ITR Form Content -->
      <form id="updateItrForm" method="POST" action="update_itr.php">
        <input type="hidden" name="itr_id" value="<?= $itr_id ?>">
        <input type="hidden" name="form_id" value="<?= $form_id ?>">

        <!-- ITR Header Card -->
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <!-- ITR Header Image Display -->
            <div class="mb-3 text-center">
              <?php if (!empty($itr['header_image'])): ?>
                <img src="../img/<?= htmlspecialchars($itr['header_image']) ?>"
                  class="img-fluid mb-2"
                  style="max-width: 100%; height: auto; object-fit: contain;">
              <?php else: ?>
                <p class="text-muted">No header image available</p>
              <?php endif; ?>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-md-4">
                <label for="entity_name" class="form-label">Entity Name <span style="color: red;">*</span></label>
                <input type="text" id="entity_name" name="entity_name" class="form-control shadow" 
                       value="<?= htmlspecialchars($itr['entity_name'] ?? '') ?>" readonly>
              </div>
              <div class="col-md-4">
                <label for="fund_cluster" class="form-label">Fund Cluster <span style="color: red;">*</span></label>
                <input type="text" id="fund_cluster" name="fund_cluster" class="form-control shadow" 
                       value="<?= htmlspecialchars($itr['fund_cluster'] ?? '') ?>" readonly>
              </div>
              <div class="col-md-4">
                <label for="date" class="form-label">Date</label>
                <div class="form-control-plaintext"><?= $itr['date'] ? date('F d, Y', strtotime($itr['date'])) : 'N/A' ?></div>
              </div>
              <div class="col-md-6">
                <label class="form-label">From Accountable Officer</label>
                <div class="form-control-plaintext"><?= htmlspecialchars($itr['from_accountable_officer'] ?? 'N/A') ?></div>
              </div>
              <div class="col-md-6">
                <label class="form-label">To Accountable Officer</label>
                <div class="form-control-plaintext"><?= htmlspecialchars($itr['to_accountable_officer'] ?? 'N/A') ?></div>
              </div>
              <div class="col-md-6">
                <label class="form-label">ITR No.</label>
                <div class="form-control-plaintext"><?= htmlspecialchars($itrNoDisplay ?: 'N/A') ?></div>
              </div>

              <!-- Transfer type radios -->
              <div class="col-md-6">
                <label class="form-label d-block">Transfer Type <span style="color: red;">*</span></label>
                <?php
                // Determine selected transfer type
                $selectedType = $itr['transfer_type'] ?? '';
                $known = ['Donation', 'Reassignment', 'Relocate'];
                $otherValue = '';
                if ($selectedType !== '' && !in_array($selectedType, $known, true)) {
                  $otherValue = $selectedType;
                  $selectedType = 'Others';
                }
                ?>
                <?php foreach ($known as $k): ?>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input transfer-type" type="radio" name="transfer_type" id="tt_<?= strtolower($k) ?>" value="<?= $k ?>" <?= ($selectedType === $k) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="tt_<?= strtolower($k) ?>"><?= $k ?></label>
                  </div>
                <?php endforeach; ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" id="tt_others" name="transfer_type" value="Others" <?= ($selectedType === 'Others') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="tt_others">Others</label>
                </div>
                <div id="transfer_type_other_wrap" class="mt-2" style="display: <?= ($selectedType === 'Others') ? 'block' : 'none' ?>;">
                  <input type="text" id="transfer_type_other" name="transfer_type_other" class="form-control shadow" placeholder="Specify other transfer type" value="<?= htmlspecialchars($otherValue) ?>">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Items Table Card -->
        <div class="card shadow-sm mt-3">
          <div class="card-body">
            <h5 class="card-title">Transfer Items</h5>
            <?php if (!empty($items)): ?>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width:15%">Date Acquired</th>
                      <th style="width:10%">Item No.</th>
                      <th style="width:20%">ICS & PAR No./Date</th>
                      <th style="width:35%">Description</th>
                      <th style="width:15%">Unit Price</th>
                      <th style="width:15%">Condition of Inventory</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($items as $index => $item): ?>
                      <tr>
                        <td><?= $item['date_acquired'] ? date('m/d/Y', strtotime($item['date_acquired'])) : 'N/A' ?></td>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($item['property_no'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['description'] ?? 'N/A') ?></td>
                        <td class="text-end"><?= number_format($item['amount'] ?? 0, 2) ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['condition_of_PPE'] ?? 'N/A') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No items found for this ITR.
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Reason for Transfer Card -->
        <div class="card shadow-sm mt-3">
          <div class="card-body">
            <label for="reason_for_transfer" class="form-label">Reason for Transfer <span style="color: red;">*</span></label>
            <textarea id="reason_for_transfer" name="reason_for_transfer" class="form-control shadow" rows="3" required><?= htmlspecialchars($itr['reason_for_transfer'] ?? '') ?></textarea>
          </div>
        </div>

        <!-- Footer Card -->
        <div class="card shadow-sm mt-3">
          <div class="card-body">
            <div class="row g-3">
              <?php foreach (['approved', 'released', 'received'] as $role): ?>
                <div class="col-md-4">
                  <label class="form-label"><?= ucfirst($role) ?> By <span style="color: red;">*</span></label>
                  <input type="text" name="<?= $role ?>_by" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_by'] ?? '') ?>" required>
                  <label class="form-label mt-2"><?= ucfirst($role) ?> Designation <span style="color: red;">*</span></label>
                  <input type="text" name="<?= $role ?>_designation" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_designation'] ?? '') ?>" required>
                  <label class="form-label mt-2"><?= ucfirst($role) ?> Date</label>
                  <input type="date" name="<?= $role ?>_date" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_date'] ?? '') ?>">
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <p class="small mt-4"> <span style="color: red;">*</span> Required fields</p>

        <!-- Save Button -->
        <div class="text-center mt-4 no-print">
          <button type="submit" class="btn btn-success btn-lg">
            <i class="bi bi-save"></i> Save Changes
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

    // Transfer type radio handling
    document.addEventListener('DOMContentLoaded', function() {
      const ttOthers = document.getElementById('tt_others');
      const ttOtherWrap = document.getElementById('transfer_type_other_wrap');
      const ttOtherInput = document.getElementById('transfer_type_other');

      function updateOtherVisibility() {
        if (!ttOthers || !ttOtherWrap) return;
        const show = ttOthers.checked;
        ttOtherWrap.style.display = show ? 'block' : 'none';
        if (!show && ttOtherInput) ttOtherInput.value = '';
      }
      
      if (ttOthers) {
        document.querySelectorAll('input[name="transfer_type"]').forEach(r => r.addEventListener('change', updateOtherVisibility));
        updateOtherVisibility();
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
