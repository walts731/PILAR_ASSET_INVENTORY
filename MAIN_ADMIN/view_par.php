<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Get PAR ID from URL
$par_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($par_id <= 0) {
  die("Invalid PAR ID.");
}

$par_form_id = $_GET['form_id'] ?? '';

// Fetch PAR form details
$sql = "SELECT f.id AS par_id, f.header_image, f.entity_name, f.fund_cluster, f.par_no,
               f.position_office_left, f.position_office_right,
               f.received_by_name, f.issued_by_name,
               f.date_received_left, f.date_received_right, f.created_at,
               f.office_id, o.office_name
        FROM par_form f
        LEFT JOIN offices o ON f.office_id = o.id
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $par_id);
$stmt->execute();
$result = $stmt->get_result();
$par = $result->fetch_assoc();
$stmt->close();

if (!$par) {
  die("PAR record not found.");
}

// Fetch PAR items (include asset_id for potential tag actions)
$sql_items = "SELECT item_id, asset_id, quantity, unit, description, property_no, date_acquired, unit_price, amount
              FROM par_items
              WHERE form_id = ?
              ORDER BY item_id ASC";
$stmt = $conn->prepare($sql_items);
$stmt->bind_param("i", $par_id);
$stmt->execute();
$result_items = $stmt->get_result();
$par['items'] = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PAR Details - <?= htmlspecialchars($par['par_no']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    /* Print styles */
    @media print {
      @page { size: A4 portrait; margin: 12mm; }
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      /* Hide navigation, buttons, alerts, and elements explicitly marked as no-print */
      .sidebar, .topbar, .navbar, .btn, .alert, .no-print { display: none !important; }
      /* Remove card chrome for clean print */
      .card, .card-body, .container, .shadow, .shadow-sm { box-shadow: none !important; border: none !important; }
      /* Make form fields print like plain text */
      input, select, textarea { border: 0 !important; outline: 0 !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; }
      .input-group, .input-group-text { border: 0 !important; background: transparent !important; box-shadow: none !important; }
      /* Tables */
      table { border-color: #000 !important; }
      thead.table-secondary { background: #e9ecef !important; -webkit-print-color-adjust: exact; 
        print-color-adjust: exact;
      }
      /* Hide action column */
      th.no-print, td.no-print { display: none !important; }
    }
  </style>
</head>

<body>
  <?php include 'includes/sidebar.php' ?>

  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container py-4">
      <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['flash']['message'] ?? '') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
      <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          Changes saved successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="d-flex justify-content-end gap-2 mb-3 no-print">
        <a href="generate_par_pdf.php?id=<?= (int)$par['par_id'] ?>" target="_blank" class="btn btn-outline-dark">
          <i class="bi bi-printer"></i> Print
        </a>
      </div>

      <form action="save_par_items.php" method="POST">
        <input type="hidden" name="existing_par_id" value="<?= (int)$par['par_id'] ?>" />
        <input type="hidden" name="form_id" value="<?= htmlspecialchars($par_form_id) ?>" />
        <div class="card mb-5 shadow-sm">
          <div class="card-body">

          <div class="mb-3 text-center">
            <?php if (!empty($par['header_image'])): ?>
              <img src="../img/<?= htmlspecialchars($par['header_image']) ?>"
                class="img-fluid mb-2 w-100"
                style="max-height:300px; object-fit:cover;">
            <?php else: ?>
              <p class="text-muted">No header image</p>
            <?php endif; ?>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Entity Name</label>
              <input type="text" class="form-control shadow" name="entity_name" value="<?= htmlspecialchars($par['entity_name']) ?>" required />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Fund Cluster</label>
              <input type="text" class="form-control shadow" name="fund_cluster" value="<?= htmlspecialchars($par['fund_cluster']) ?>" />
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">PAR No.</label>
              <input type="text" class="form-control shadow" name="par_no" value="<?= htmlspecialchars($par['par_no']) ?>" readonly />
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Office</label>
              <input type="text" class="form-control shadow" value="<?= htmlspecialchars($par['office_name'] ?? 'N/A') ?>" disabled />
            </div>
          </div>

          <hr>

          <!-- Items Table -->
          <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
              <thead class="table-secondary">
                <tr>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Unit Price</th>
                  <th>Amount</th>
                  <th>Description</th>
                  <th>Property No</th>
                  <th>Date Acquired</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($par['items'])): ?>
                  <?php foreach ($par['items'] as $item): ?>
                    <tr>
                      <td>
                        <input type="number" step="1" min="0" class="form-control form-control-sm text-center shadow" name="items[<?= (int)$item['item_id'] ?>][quantity]" value="<?= htmlspecialchars($item['quantity']) ?>">
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-sm text-center shadow" name="items[<?= (int)$item['item_id'] ?>][unit]" value="<?= htmlspecialchars($item['unit']) ?>">
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">₱</span>
                          <input type="number" step="0.01" min="0" class="form-control text-end item-unit-price shadow" name="items[<?= (int)$item['item_id'] ?>][unit_price]" value="<?= htmlspecialchars($item['unit_price']) ?>">
                        </div>
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">₱</span>
                          <input type="number" step="0.01" min="0" class="form-control text-end item-amount shadow" name="items[<?= (int)$item['item_id'] ?>][amount]" value="<?= htmlspecialchars($item['amount']) ?>" readonly>
                        </div>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-sm shadow" name="items[<?= (int)$item['item_id'] ?>][description]" value="<?= htmlspecialchars($item['description']) ?>">
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-sm shadow" name="items[<?= (int)$item['item_id'] ?>][property_no]" value="<?= htmlspecialchars($item['property_no']) ?>">
                      </td>
                      <td>
                        <input type="date" class="form-control form-control-sm text-center shadow" name="items[<?= (int)$item['item_id'] ?>][date_acquired]" value="<?= htmlspecialchars($item['date_acquired']) ?>">
                        <input type="hidden" name="items[<?= (int)$item['item_id'] ?>][asset_id]" value="<?= htmlspecialchars($item['asset_id']) ?>">
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-muted">No items found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

         <!-- Signatories -->
<div class="row mt-4">
  <div class="col-md-6">
    <label class="form-label fw-semibold text-center d-block">Received by - Signature over Printed Name</label>
    <input type="text" class="form-control shadow text-center" name="received_by_name" value="<?= htmlspecialchars($par['received_by_name'] ?? '') ?>" placeholder="Signature over Printed Name">
    
    <label class="form-label mt-2 text-center d-block">Position / Office</label>
    <input type="text" class="form-control shadow text-center" name="position_office_left" value="<?= htmlspecialchars($par['position_office_left'] ?? '') ?>">
    
    <label class="form-label mt-2 text-center d-block">Date</label>
    <input type="date" class="form-control shadow text-center" name="date_received_left" value="<?= htmlspecialchars($par['date_received_left'] ?? '') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold text-center d-block">Issued by - Signature over Printed Name</label>
    <input type="text" class="form-control shadow text-center" name="issued_by_name" value="<?= htmlspecialchars($par['issued_by_name'] ?? '') ?>" placeholder="Signature over Printed Name">
    
    <label class="form-label mt-2 text-center d-block">Position / Office</label>
    <input type="text" class="form-control shadow text-center" name="position_office_right" value="<?= htmlspecialchars($par['position_office_right'] ?? '') ?>">
    
    <label class="form-label mt-2 text-center d-block">Date</label>
    <input type="date" class="form-control shadow text-center" name="date_received_right" value="<?= htmlspecialchars($par['date_received_right'] ?? '') ?>">
  </div>
</div>

          </div>
          </div>
        </div>

        <div class="d-flex gap-2 mb-5">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Auto-recalculate amount when quantity or unit price changes
    document.querySelectorAll('.item-unit-price, input[name^="items"][name$="[quantity]"]').forEach(el => {
      el.addEventListener('input', function() {
        const row = this.closest('tr');
        const qtyEl = row.querySelector('input[name$="[quantity]"]');
        const priceEl = row.querySelector('.item-unit-price');
        const amtEl = row.querySelector('.item-amount');
        const qty = parseFloat(qtyEl?.value || '0');
        const price = parseFloat(priceEl?.value || '0');
        if (amtEl) amtEl.value = (qty * price).toFixed(2);
      });
    });
  </script>
</body>
</html>
