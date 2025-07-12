<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Set office_id if not set
if (!isset($_SESSION['office_id'])) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT office_id FROM users WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($office_id);
  if ($stmt->fetch()) {
    $_SESSION['office_id'] = $office_id;
  }
  $stmt->close();
}

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">

    <?php include 'includes/topbar.php' ?>

    <?php include 'alerts/inventory_alerts.php'; ?>

    <?php
    // Get list of offices for dropdown
    $offices = $conn->query("SELECT id, office_name FROM offices");

    // Get selected office from GET or default to user's office
    $selected_office = $_GET['office'] ?? $_SESSION['office_id'];
    ?>
    <div class="card card-filter shadow-sm mb-3">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 me-3">Filter Assets and Consumables</h5>

        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
          <form method="GET" class="d-flex align-items-center gap-2 mb-0">
            <label for="officeFilter" class="form-label mb-0">Office</label>
            <select name="office" id="officeFilter" class="form-select form-select-sm" onchange="this.form.submit()">
              <?php while ($office = $offices->fetch_assoc()): ?>
                <option value="<?= $office['id'] ?>" <?= $office['id'] == $selected_office ? 'selected' : '' ?>>
                  <?= htmlspecialchars($office['office_name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </form>

          <button class="btn btn-outline-secondary rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
            <i class="bi bi-tags"></i> Manage Categories
          </button>

          <button class="btn btn-outline-primary rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#addAssetModal">
            <i class="bi bi-plus-circle"></i> Add Asset
          </button>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="inventoryTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="assets-tab" data-bs-toggle="tab" data-bs-target="#assets" type="button" role="tab">Assets</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="consumables-tab" data-bs-toggle="tab" data-bs-target="#consumables" type="button" role="tab">Consumables</button>
      </li>
    </ul>

    <div class="tab-content" id="inventoryTabsContent">
      <!-- Assets Tab -->
      <div class="tab-pane fade show active" id="assets" role="tabpanel">
        <?php
        $total = $active = $borrowed = $red_tagged = 0;
        $res = $conn->prepare("SELECT status, red_tagged FROM assets WHERE type = 'asset' AND office_id = ?");
        $res->bind_param("i", $selected_office);
        $res->execute();
        $resResult = $res->get_result();
        while ($r = $resResult->fetch_assoc()) {
          $total++;
          if ($r['status'] === 'available') $active++;
          if ($r['status'] === 'borrowed') $borrowed++;
          if ($r['status'] === 'red_tagged') $red_tagged++;
        }
        ?>
        <div class="row mb-4">
          <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <h5>Total</h5>
                  <h3><?= $total ?></h3>
                </div>
                <i class="bi bi-box-seam text-primary fs-2"></i>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <h5>Available</h5>
                  <h3><?= $active ?></h3>
                </div>
                <i class="bi bi-check-circle text-info fs-2"></i>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <h5>Borrowed</h5>
                  <h3><?= $borrowed ?></h3>
                </div>
                <i class="bi bi-arrow-left-right text-primary fs-2"></i>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <h5>Red-Tagged</h5>
                  <h3><?= $red_tagged ?></h3>
                </div>
                <i class="bi bi-exclamation-triangle text-info fs-2"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm mb-4">
<form action="generate_selected_report.php" method="POST" target="_blank" class="template-check-form">
            <?php
            // Fetch available report templates
            $template_stmt = $conn->query("SELECT id, template_name FROM report_templates ORDER BY created_at DESC");
            ?>

            <input type="hidden" name="office" value="<?= $selected_office ?>">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5 class="mb-0">Asset List</h5>

              <div class="d-flex flex-wrap gap-2">
                <!-- Bulk Actions -->
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill" id="bulkBorrowBtn">
                  <i class="bi bi-box-arrow-in-right"></i> Borrow
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill" id="bulkReleaseBtn">
                  <i class="bi bi-box-arrow-up"></i> Release
                </button>
                <button type="button" class="btn btn-outline-info btn-sm rounded-pill" id="bulkTransferBtn">
                  <i class="bi bi-arrow-left-right"></i> Transfer
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="bulkReturnBtn">
                  <i class="bi bi-box-arrow-in-left"></i> Return
                </button>

                <!-- Existing Generate Report Button -->
                <button type="submit" class="btn btn-outline-primary rounded-pill btn-sm">
                  <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
                </button>
                <select name="template_id" class="form-select form-select-sm d-inline-block w-auto" required>
                  <option value="" disabled selected>Select Template</option>
                  <?php while ($template = $template_stmt->fetch_assoc()): ?>
                    <option value="<?= $template['id'] ?>"><?= htmlspecialchars($template['template_name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>
            <div class="alert alert-danger" role="alert" id="checkboxAlert">
              Please select at least one item to generate a report.
            </div>

            <div class="card-body table-responsive">
              <table id="assetTable" class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th><input type="checkbox" id="selectAllAssets" /></th>
                    <th>QR</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Value</th>
                    <th>Acquired</th>
                    <th>Updated</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $stmt = $conn->prepare("
                          SELECT a.*, c.category_name 
                          FROM assets a 
                          JOIN categories c ON a.category = c.id 
                          WHERE a.type = 'asset' AND a.office_id = ?
                        ");
                  $stmt->bind_param("i", $selected_office);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()):

                  ?>
                    <tr>
                      <td><input type="checkbox" class="asset-checkbox" name="selected_assets[]" value="<?= $row['id'] ?>"></td>
                      <td>
                        <a href="../img/<?= $row['qr_code'] ?>" download="<?= $row['asset_name'] ?>_QR.png" title="Download QR">
                          <img src="../img/<?= $row['qr_code'] ?>" width="50" alt="QR Code" />
                        </a>
                      </td>
                      <td><?= htmlspecialchars($row['asset_name']) ?></td>
                      <td><?= htmlspecialchars($row['category_name']) ?></td>
                      <td><?= htmlspecialchars($row['description']) ?></td>
                      <td><?= $row['quantity'] ?></td>
                      <td><?= $row['unit'] ?></td>
                      <td>
                        <?php
                        $status_class = match ($row['status']) {
                          'available' => 'success',
                          'borrowed' => 'warning',
                          default => 'secondary',
                        };
                        if ($row['red_tagged']) $status_class = 'danger';
                        ?>
                        <span class="badge bg-<?= $status_class ?> status-badge">
                          <?= $row['red_tagged'] ? 'Red-Tagged' : ucfirst($row['status']) ?>
                        </span>
                      </td>
                      <td>&#8369; <?= number_format($row['value'], 2) ?></td>
                      <td><?= date('F j, Y', strtotime($row['acquisition_date'])) ?></td>
                      <td><?= date('F j, Y', strtotime($row['last_updated'])) ?></td>
                      <td class="text-nowrap">
                        <div class="btn-group" role="group">


                          <!-- Edit Button -->
                          <button type="button"
                            class="btn btn-sm btn-outline-primary rounded-pill updateAssetBtn"
                            data-id="<?= $row['id'] ?>"
                            data-name="<?= htmlspecialchars($row['asset_name']) ?>"
                            data-category="<?= $row['category'] ?>"
                            data-description="<?= htmlspecialchars($row['description']) ?>"
                            data-qty="<?= $row['quantity'] ?>"
                            data-unit="<?= $row['unit'] ?>"
                            data-status="<?= $row['status'] ?>"
                            data-office="<?= $row['office_id'] ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#updateAssetModal">
                            <i class="bi bi-pencil-square"></i>
                          </button>

                          <!-- Delete Button or Lock -->
                          <?php if ($row['status'] !== 'borrowed'): ?>
                            <button type="button"
                              class="btn btn-sm btn-outline-danger rounded-pill deleteAssetBtn"
                              data-id="<?= $row['id'] ?>"
                              data-name="<?= htmlspecialchars($row['asset_name']) ?>"
                              data-bs-toggle="modal"
                              data-bs-target="#deleteAssetModal">
                              <i class="bi bi-trash"></i>
                            </button>
                          <?php else: ?>
                            <span class="text-muted small d-inline-flex align-items-center ms-2"><i class="bi bi-lock"></i></span>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </form>
        </div>
      </div>

      <!-- Consumables Tab -->
      <div class="tab-pane fade" id="consumables" role="tabpanel">
        <?php
        $ctotal = $cactive = $clow_stock = $cunavailable = 0;
        $threshold = 5;
        $cres = $conn->prepare("SELECT status, quantity FROM assets WHERE type = 'consumable' AND office_id = ?");
        $cres->bind_param("i", $selected_office);
        $cres->execute();
        $cresResult = $cres->get_result();
        while ($r = $cresResult->fetch_assoc()) {
          $ctotal++;
          if ($r['status'] === 'available') $cactive++;
          if ($r['status'] === 'unavailable') $cunavailable++;
          if ((int)$r['quantity'] <= $threshold) $clow_stock++;
        }
        ?>

        <div class="row mb-4">
          <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <h5>Total</h5>
                  <h3><?= $ctotal ?></h3>
                </div>
                <i class="bi bi-box-seam text-primary fs-2"></i>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <h5>Available</h5>
                  <h3><?= $cactive ?></h3>
                </div>
                <i class="bi bi-check-circle text-info fs-2"></i>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div id="lowStockCard" class="card shadow-sm border-warning h-100" style="cursor: pointer;">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <h5>Unavailable</h5>
                  <h3><?= $cunavailable ?></h3>
                </div>
                <i class="bi bi-slash-circle text-primary fs-2"></i>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div id="lowStockCard" class="card shadow-sm border-warning h-100" style="cursor: pointer;">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <h5>Low Stock</h5>
                  <h3><?= $clow_stock ?></h3>
                </div>
                <i class="bi bi-exclamation-triangle text-info fs-2"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm">
          <form action="generate_selected_report.php" method="POST">
            <input type="hidden" name="office" value="<?= $selected_office ?>">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5 class="mb-0">Consumable List</h5>
              <div>
                <select id="stockFilter" class="form-select form-select-sm d-inline-block w-auto me-2">
                  <option value="">All Items</option>
                  <option value="low">Low Stock</option>
                </select>

                <button type="submit" class="btn btn-outline-primary rounded-pill btn-sm">
                  <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
                </button>
              </div>
            </div>

            <div class="alert alert-danger" role="alert" id="checkboxAlert">
              Please select at least one item to generate a report.
            </div>

            <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
              <div class="alert alert-success">Consumable updated successfully!</div>
            <?php endif; ?>

            <div class="card-body table-responsive">
              <table id="consumablesTable" class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th><input type="checkbox" id="selectAllConsumables" /></th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Acquired</th>
                    <th>Updated</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $threshold = 5; // adjust threshold if needed
                  $stmt = $conn->prepare("
                            SELECT a.*, c.category_name 
                            FROM assets a 
                            JOIN categories c ON a.category = c.id 
                            WHERE a.type = 'consumable' AND a.office_id = ?
                          ");
                  $stmt->bind_param("i", $selected_office);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()):
                    $is_low = $row['quantity'] <= $threshold;
                  ?>
                    <tr data-stock="<?= $is_low ? 'low' : 'normal' ?>">
                      <td><input type="checkbox" class="consumable-checkbox" name="selected_assets[]" value="<?= $row['id'] ?>"></td>
                      <td><?= htmlspecialchars($row['asset_name']) ?></td>
                      <td><?= htmlspecialchars($row['category_name']) ?></td>
                      <td><?= htmlspecialchars($row['description']) ?></td>
                      <td class="<?= $is_low ? 'text-danger fw-bold' : '' ?>"><?= $row['quantity'] ?></td>
                      <td><?= $row['unit'] ?></td>
                      <td>
                        <span class="badge bg-<?= $row['status'] === 'available' ? 'success' : 'secondary' ?>">
                          <?= ucfirst($row['status']) ?>
                        </span>
                      </td>
                      <td><?= date('F j, Y', strtotime($row['acquisition_date'])) ?></td>
                      <td><?= date('F j, Y', strtotime($row['last_updated'])) ?></td>
                      <td>
                        <button type="button"
                          class="btn btn-sm btn-outline-primary updateConsumableBtn rounded-pill"
                          data-id="<?= $row['id'] ?>"
                          data-name="<?= htmlspecialchars($row['asset_name']) ?>"
                          data-category="<?= $row['category'] ?>"
                          data-description="<?= htmlspecialchars($row['description']) ?>"
                          data-unit="<?= htmlspecialchars($row['unit']) ?>"
                          data-qty="<?= $row['quantity'] ?>"
                          data-status="<?= $row['status'] ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#updateConsumableModal">
                          <i class="bi bi-pencil-square"></i>
                        </button>
                        <?php if ($row['status'] !== 'borrowed'): ?>
                          <button type="button"
                            class="btn btn-sm btn-outline-danger deleteConsumableBtn rounded-pill"
                            data-id="<?= $row['id'] ?>"
                            data-name="<?= htmlspecialchars($row['asset_name']) ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteConsumableModal">
                            <i class="bi bi-trash"></i>
                          </button>
                        <?php else: ?>
                          <span class="text-muted small"><i class="bi bi-lock"></i></span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
  <?php include 'modals/update_consumable_modal.php'; ?>
  <?php include 'modals/delete_consumable_modal.php'; ?>
  <?php include 'modals/update_asset_modal.php'; ?>
  <?php include 'modals/delete_asset_modal.php'; ?>
  <?php include 'modals/add_asset_modal.php'; ?>
  <?php include 'modals/manage_categories_modal.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>


</body>

</html>