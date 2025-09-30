<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Get ICS ID from URL
$ics_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($ics_id <= 0) {
  die("Invalid ICS ID.");
}

$ics_form_id = $_GET['form_id'] ?? '';

// Fetch ICS form details
$sql = "SELECT f.id AS ics_id, f.header_image, f.entity_name, f.fund_cluster, f.ics_no,
               f.received_from_name, f.received_from_position,
               f.received_by_name, f.received_by_position, f.created_at,
               o.office_name
        FROM ics_form f
        LEFT JOIN offices o ON f.id = o.id
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ics_id);
$stmt->execute();
$result = $stmt->get_result();
$ics = $result->fetch_assoc();
$stmt->close();

if (!$ics) {
  die("ICS record not found.");
}

// Fetch ICS items (include asset_id for MR auto-fill)
$sql_items = "SELECT item_id, asset_id, item_no, description, quantity, unit, unit_cost, total_cost, estimated_useful_life
              FROM ics_items
              WHERE ics_id = ?
              ORDER BY item_no ASC";
$stmt = $conn->prepare($sql_items);
$stmt->bind_param("i", $ics_id);
$stmt->execute();
$result_items = $stmt->get_result();
$ics['items'] = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ICS Details - <?= htmlspecialchars($ics['ics_no']) ?></title>
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
      

      <form action="save_ics_items.php" method="POST">
        <input type="hidden" name="existing_ics_id" value="<?= (int)$ics['ics_id'] ?>" />
        <input type="hidden" name="form_id" value="<?= htmlspecialchars($ics_form_id) ?>" />
        <div class="card mb-5 shadow-sm">
          <div class="card-body">

            <div class="mb-3 text-center">
              <?php if (!empty($ics['header_image'])): ?>
                <img src="../img/<?= htmlspecialchars($ics['header_image']) ?>"
                  class="img-fluid mb-2 w-100"
                  style="max-height:300px; object-fit:cover;">
              <?php else: ?>
                <p class="text-muted">No header image</p>
              <?php endif; ?>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Entity Name</label>
                <input type="text" class="form-control" name="entity_name" value="<?= htmlspecialchars($ics['entity_name']) ?>" required />
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Fund Cluster</label>
                <input type="text" class="form-control" name="fund_cluster" value="<?= htmlspecialchars($ics['fund_cluster']) ?>" />
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">ICS No.</label>
                <input type="text" class="form-control" name="ics_no" value="<?= htmlspecialchars($ics['ics_no']) ?>" required />
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
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                    <th>Description</th>
                    <th>Item No</th>
                    <th>Estimated Useful Life</th>
                    
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($ics['items'])): ?>
                    <?php foreach ($ics['items'] as $item): ?>
                      <tr>
                        <td>
                          <input type="number" step="1" min="0" class="form-control form-control-sm text-center item-qty" name="items[<?= (int)$item['item_id'] ?>][quantity]" value="<?= htmlspecialchars($item['quantity']) ?>">
                        </td>
                        <td>
                          <input type="text" class="form-control form-control-sm text-center" name="items[<?= (int)$item['item_id'] ?>][unit]" value="<?= htmlspecialchars($item['unit']) ?>">
                        </td>
                        <td>
                          <div class="input-group input-group-sm">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" min="0" class="form-control text-end item-unit-cost" name="items[<?= (int)$item['item_id'] ?>][unit_cost]" value="<?= htmlspecialchars($item['unit_cost']) ?>">
                          </div>
                        </td>
                        <td>
                          <div class="input-group input-group-sm">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" min="0" class="form-control text-end item-total-cost" name="items[<?= (int)$item['item_id'] ?>][total_cost]" value="<?= htmlspecialchars($item['total_cost']) ?>" readonly>
                          </div>
                        </td>
                        <td>
                          <input type="text" class="form-control form-control-sm" name="items[<?= (int)$item['item_id'] ?>][description]" value="<?= htmlspecialchars($item['description']) ?>">
                        </td>
                        <td>
                          <input type="number" step="1" min="0" class="form-control form-control-sm text-center" name="items[<?= (int)$item['item_id'] ?>][item_no]" value="<?= htmlspecialchars($item['item_no']) ?>">
                        </td>
                        <td>
                          <input type="text" class="form-control form-control-sm text-center" name="items[<?= (int)$item['item_id'] ?>][estimated_useful_life]" value="<?= htmlspecialchars($item['estimated_useful_life']) ?>">
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
                <label class="form-label fw-semibold">Received from - Name</label>
                <input type="text" class="form-control" name="received_from_name" value="<?= htmlspecialchars($ics['received_from_name']) ?>">
                <label class="form-label mt-2">Position</label>
                <input type="text" class="form-control" name="received_from_position" value="<?= htmlspecialchars($ics['received_from_position']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Received by - Name</label>
                <input type="text" class="form-control" name="received_by_name" value="<?= htmlspecialchars($ics['received_by_name']) ?>">
                <label class="form-label mt-2">Position</label>
                <input type="text" class="form-control" name="received_by_position" value="<?= htmlspecialchars($ics['received_by_position']) ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2 mb-5">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Save Changes
          </button>
          <a href="generate_ics_pdf.php?id=<?= $ics['ics_id'] ?>" class="btn btn-success">
            <i class="bi bi-printer"></i> Print / Export PDF
          </a>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Auto-recalculate total cost when quantity or unit cost changes
    document.querySelectorAll('.item-qty, .item-unit-cost').forEach(el => {
      el.addEventListener('input', function() {
        const row = this.closest('tr');
        const qty = parseFloat(row.querySelector('.item-qty')?.value || '0');
        const unit = parseFloat(row.querySelector('.item-unit-cost')?.value || '0');
        const totalEl = row.querySelector('.item-total-cost');
        if (totalEl) totalEl.value = (qty * unit).toFixed(2);
      });
    });
  </script>
</body>

</html>