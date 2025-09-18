<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
  $system = $result->fetch_assoc();
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
              <!-- Add "All" option -->
              <option value="all" <?= $selected_office === "all" ? 'selected' : '' ?>>All</option>

              <?php while ($office = $offices->fetch_assoc()): ?>
                <option value="<?= $office['id'] ?>" <?= $office['id'] == $selected_office ? 'selected' : '' ?>>
                  <?= htmlspecialchars($office['office_name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </form>

          <!-- Show buttons only if selected office = user's office -->
          <?php if ($selected_office == $_SESSION['office_id']): ?>
            <!-- Add Asset Button -->
            <button class="btn btn-outline-primary rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#addAssetModal">
              <i class="bi bi-plus-circle"></i> Add Asset
            </button>

            <!-- Import CSV Button -->
            <button class="btn btn-outline-success rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#importCSVModal">
              <i class="bi bi-upload"></i> Import CSV
            </button>
          <?php endif; ?>


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
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="no-property-tab" data-bs-toggle="tab" data-bs-target="#no_property" type="button" role="tab">No Property Tag</button>
      </li>
    </ul>

    <div class="tab-content" id="inventoryTabsContent">
      <!-- Assets Tab -->
      <div class="tab-pane fade show active" id="assets" role="tabpanel">
        <?php
        $total = $active = $borrowed = $red_tagged = 0;
        if ($selected_office === "all") {
          $res = $conn->prepare("SELECT status, red_tagged FROM assets WHERE type = 'asset' AND quantity > 0");
        } else {
          $res = $conn->prepare("SELECT status, red_tagged FROM assets WHERE type = 'asset' AND office_id = ? AND quantity > 0");
          $res->bind_param("i", $selected_office);
        }

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
                <!-- Existing Generate Report Button -->
                <button type="submit" class="btn btn-outline-primary rounded-pill btn-sm">
                  <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
                </button>
              </div>
            </div>
            <div class="alert alert-danger" role="alert" id="checkboxAlert">
              Please select at least one item to generate a report.
            </div>

            <?php
            // Warn about assets without property numbers
            if ($selected_office === "all") {
              $stmtMissing = $conn->prepare("SELECT id, description FROM assets WHERE type='asset' AND quantity > 0 AND (property_no IS NULL OR property_no = '') ORDER BY last_updated DESC LIMIT 10");
            } else {
              $stmtMissing = $conn->prepare("SELECT id, description FROM assets WHERE type='asset' AND quantity > 0 AND office_id = ? AND (property_no IS NULL OR property_no = '') ORDER BY last_updated DESC LIMIT 10");
              $stmtMissing->bind_param("i", $selected_office);
            }
            $missingAssets = [];
            if ($stmtMissing) {
              $stmtMissing->execute();
              $resMissing = $stmtMissing->get_result();
              while ($m = $resMissing->fetch_assoc()) { $missingAssets[] = $m; }
              $stmtMissing->close();
            }
            // Also find affected item records from asset_items where parent asset has no property_no
            if ($selected_office === "all") {
              $stmtMissingItems = $conn->prepare("SELECT ai.item_id, ai.asset_id, ai.inventory_tag FROM asset_items ai JOIN assets a ON ai.asset_id = a.id WHERE a.type='asset' AND (a.property_no IS NULL OR a.property_no = '') ORDER BY ai.item_id DESC LIMIT 20");
            } else {
              $stmtMissingItems = $conn->prepare("SELECT ai.item_id, ai.asset_id, ai.inventory_tag FROM asset_items ai JOIN assets a ON ai.asset_id = a.id WHERE a.type='asset' AND ai.office_id = ? AND (a.property_no IS NULL OR a.property_no = '') ORDER BY ai.item_id DESC LIMIT 20");
              $stmtMissingItems->bind_param("i", $selected_office);
            }
            $missingItems = [];
            if ($stmtMissingItems) {
              $stmtMissingItems->execute();
              $resMI = $stmtMissingItems->get_result();
              while ($mi = $resMI->fetch_assoc()) { $missingItems[] = $mi; }
              $stmtMissingItems->close();
            }
            if (count($missingAssets) > 0): ?>
              <div class="alert alert-warning d-flex align-items-start" role="alert">
                <div>
                  <div class="fw-bold mb-1">Some assets have no Property Number</div>
                  <div class="small mb-1">Recently inserted assets may be missing property numbers. Please review and update them.</div>
                  <ul class="mb-0 small">
                    <?php foreach ($missingAssets as $mi): ?>
                      <li>
                        <a href="#" class="text-decoration-underline viewAssetBtn" data-id="<?= $mi['id'] ?>" data-bs-toggle="modal" data-bs-target="#viewAssetModal">
                          <?= htmlspecialchars($mi['description']) ?> (ID: <?= $mi['id'] ?>)
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                  <?php if (count($missingItems) > 0): ?>
                    <div class="small mt-2">Affected items (asset_items):</div>
                    <ul class="mb-0 small">
                      <?php foreach ($missingItems as $it): ?>
                        <li>
                          <a href="#" class="text-decoration-underline viewAssetBtn" data-id="<?= $it['asset_id'] ?>" data-bs-toggle="modal" data-bs-target="#viewAssetModal">
                            <?= htmlspecialchars($it['inventory_tag'] ?? ('Item #' . $it['item_id'])) ?> (Asset ID: <?= $it['asset_id'] ?>)
                          </a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <div class="card-body table-responsive">
              <table id="assetTable" class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th><input type="checkbox" id="selectAllAssets" /></th>
                    <th>Property no</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Unit Cost</th>
                    <th>Total Value</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                </thead>
                <tbody>
                  <?php
                  if ($selected_office === "all") {
                    $stmt = $conn->prepare("
    SELECT a.*, COALESCE(c.category_name, 'Uncategorized') AS category_name 
    FROM assets a 
    LEFT JOIN categories c ON a.category = c.id 
    WHERE a.type = 'asset' AND a.quantity > 0
  ");
                  } else {
                    $stmt = $conn->prepare("
    SELECT a.*, COALESCE(c.category_name, 'Uncategorized') AS category_name 
    FROM assets a 
    LEFT JOIN categories c ON a.category = c.id 
    WHERE a.type = 'asset' AND a.office_id = ? AND a.quantity > 0
  ");
                    $stmt->bind_param("i", $selected_office);
                  }

                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()):

                  ?>
                    <tr>
                      <td><input type="checkbox" class="asset-checkbox" name="selected_assets[]" value="<?= $row['id'] ?>"></td>
                      <td><?= htmlspecialchars($row['property_no']) ?></td>
                      <td><?= htmlspecialchars($row['description']) ?></td>
                      <td><?= htmlspecialchars($row['category_name']) ?></td>
                      <td><?= $row['quantity'] ?></td>
                      <td><?= $row['unit'] ?></td>
                      <td>&#8369; <?= number_format($row['value'], 2) ?></td> <!-- Unit Cost -->
                      <td>&#8369; <?= number_format($row['value'] * $row['quantity'], 2) ?></td> <!-- Total Value -->
                      <td class="text-nowrap">
                        <div class="btn-group" role="group">
                          <!-- View Button -->
                          <button type="button"
                            class="btn btn-sm btn-outline-info rounded-pill viewAssetBtn"
                            data-id="<?= $row['id'] ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#viewAssetModal">
                            <i class="bi bi-eye"></i>
                          </button>

                          <!-- Edit Button -->
                          <button type="button"
                            class="btn btn-sm btn-outline-primary rounded-pill updateAssetBtn"
                            data-id="<?= $row['id'] ?>"
                            data-category="<?= $row['category'] ?>"
                            data-description="<?= htmlspecialchars($row['description']) ?>"
                            data-qty="<?= $row['quantity'] ?>"
                            data-unit="<?= $row['unit'] ?>"
                            data-status="<?= $row['status'] ?>"
                            data-office="<?= $row['office_id'] ?>"
                            data-image="<?= $row['image'] ?>"
                            data-serial="<?= htmlspecialchars($row['serial_no']) ?>"
                            data-code="<?= htmlspecialchars($row['code']) ?>"
                            data-property="<?= htmlspecialchars($row['property_no']) ?>"
                            data-model="<?= htmlspecialchars($row['model']) ?>"
                            data-brand="<?= htmlspecialchars($row['brand']) ?>"
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
                            <span class="text-muted small d-inline-flex align-items-center ms-2">
                              <i class="bi bi-lock"></i>
                            </span>
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

        if ($selected_office === "all") {
          // Fetch all consumables across offices but only with quantity > 0
          $cres = $conn->prepare("SELECT status, quantity FROM assets WHERE type = 'consumable' AND quantity > 0");
        } else {
          // Fetch consumables for a specific office but only with quantity > 0
          $cres = $conn->prepare("SELECT status, quantity FROM assets WHERE type = 'consumable' AND office_id = ? AND quantity > 0");
          $cres->bind_param("i", $selected_office);
        }

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
                    <th>Stock No</th>
                    <th>Description</th>
                    <th>On Hand</th>
                    <th>Restocked Qty</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $threshold = 5; // adjust threshold if needed
                  if ($selected_office === "all") {
                    $stmt = $conn->prepare("
                        SELECT a.*, COALESCE(c.category_name, 'Uncategorized') AS category_name 
                        FROM assets a 
                        LEFT JOIN categories c ON a.category = c.id 
                        WHERE a.type = 'consumable' AND a.quantity > 0
                      ");
                  } else {
                    $stmt = $conn->prepare("
                        SELECT a.*, COALESCE(c.category_name, 'Uncategorized') AS category_name 
                        FROM assets a 
                        LEFT JOIN categories c ON a.category = c.id 
                        WHERE a.type = 'consumable' AND a.office_id = ? AND a.quantity > 0
                      ");
                    $stmt->bind_param("i", $selected_office);
                  }

                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()):
                    $is_low = $row['quantity'] <= $threshold;
                  ?>
                    <tr data-stock="<?= $is_low ? 'low' : 'normal' ?>">
                      <td><input type="checkbox" class="consumable-checkbox" name="selected_assets[]" value="<?= $row['id'] ?>"></td>
                      <td><?= htmlspecialchars($row['property_no']) ?></td>
                      <td><?= htmlspecialchars($row['description']) ?></td>
                      <td class="<?= $is_low ? 'text-danger fw-bold' : '' ?>"><?= $row['quantity'] ?></td>
                      <td><?= $row['added_stock'] ?></td>
                      <td><?= $row['unit'] ?></td>
                      <td>
                        <span class="badge bg-<?= $row['status'] === 'available' ? 'success' : 'secondary' ?>">
                          <?= ucfirst($row['status']) ?>
                        </span>
                      </td>
                      <td><?= date('M d, Y', strtotime($row['last_updated'])) ?></td>
                      <td>
                        <!-- View Button -->
                        <button type="button"
                          class="btn btn-sm btn-outline-info rounded-pill viewAssetBtn"
                          data-id="<?= $row['id'] ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#viewAssetModal">
                          <i class="bi bi-eye"></i>
                        </button>

                        <button type="button"
                          class="btn btn-sm btn-outline-primary updateConsumableBtn rounded-pill"
                          data-id="<?= $row['id'] ?>"
                          data-category="<?= $row['category'] ?>"
                          data-description="<?= htmlspecialchars($row['description']) ?>"
                          data-unit="<?= htmlspecialchars($row['unit']) ?>"
                          data-qty="<?= $row['quantity'] ?>"
                          data-status="<?= $row['status'] ?>"
                          data-image="<?= $row['image'] ?>"
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
      
      <!-- No Property Tag Tab -->
      <div class="tab-pane fade" id="no_property" role="tabpanel">
        <?php
        // Query for assets missing property_no (not filtered by office)
        $stmtNP = $conn->prepare("
          SELECT a.*, COALESCE(c.category_name, 'Uncategorized') AS category_name
          FROM assets a
          LEFT JOIN categories c ON a.category = c.id
          WHERE a.type = 'asset' AND a.quantity > 0 AND (a.property_no IS NULL OR a.property_no = '')
          ORDER BY a.last_updated DESC
        ");
        $stmtNP->execute();
        $npResult = $stmtNP->get_result();
        ?>

        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Assets Without Property Number</h5>
          </div>
          <div class="card-body table-responsive">
            <table id="noPropertyTable" class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Asset ID</th>
                  <th>Description</th>
                  <th>Category</th>
                  <th>Qty</th>
                  <th>Unit</th>
                  <th>Unit Cost</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $npResult->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= $row['unit'] ?></td>
                    <td>&#8369; <?= number_format($row['value'], 2) ?></td>
                    <td class="text-nowrap">
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill viewAssetBtn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#viewAssetModal">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill updateAssetBtn" data-id="<?= $row['id'] ?>" data-category="<?= $row['category'] ?>" data-description="<?= htmlspecialchars($row['description']) ?>" data-qty="<?= $row['quantity'] ?>" data-unit="<?= $row['unit'] ?>" data-status="<?= $row['status'] ?>" data-office="<?= $row['office_id'] ?>" data-image="<?= $row['image'] ?>" data-serial="<?= htmlspecialchars($row['serial_no']) ?>" data-code="<?= htmlspecialchars($row['code']) ?>" data-property="<?= htmlspecialchars($row['property_no']) ?>" data-model="<?= htmlspecialchars($row['model']) ?>" data-brand="<?= htmlspecialchars($row['brand']) ?>" data-bs-toggle="modal" data-bs-target="#updateAssetModal">
                          <i class="bi bi-pencil-square"></i>
                        </button>
                        <?php if ($row['status'] !== 'borrowed'): ?>
                          <button type="button" class="btn btn-sm btn-outline-danger rounded-pill deleteAssetBtn" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['asset_name']) ?>" data-bs-toggle="modal" data-bs-target="#deleteAssetModal">
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
  <?php include 'modals/view_asset_modal.php'; ?>
  <?php include 'modals/import_csv_modal.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>

  <script>
    function formatDateFormal(dateStr) {
      const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      };
      const date = new Date(dateStr);
      return date.toLocaleDateString('en-US', options);
    }

    document.querySelectorAll('.viewAssetBtn').forEach(button => {
      button.addEventListener('click', function() {
        const assetId = this.getAttribute('data-id');

        fetch(`get_asset_details.php?id=${assetId}`)
          .then(response => response.json())
          .then(data => {
            if (data.error) {
              alert(data.error);
              return;
            }

            // Text content
            document.getElementById('viewOfficeName').textContent = data.office_name;
            document.getElementById('viewCategoryName').textContent = `${data.category_name} (${data.category_type})`;
            document.getElementById('viewType').textContent = data.type;
            document.getElementById('viewStatus').textContent = data.status;
            document.getElementById('viewQuantity').textContent = data.quantity;
            document.getElementById('viewUnit').textContent = data.unit;
            document.getElementById('viewDescription').textContent = data.description;
            document.getElementById('viewAcquisitionDate').textContent = formatDateFormal(data.acquisition_date);
            document.getElementById('viewLastUpdated').textContent = formatDateFormal(data.last_updated);
            document.getElementById('viewValue').textContent = parseFloat(data.value).toFixed(2);

            // Optional fields
            document.getElementById('viewSerialNo').textContent = data.serial_no ?? '';
            document.getElementById('viewCode').textContent = data.code ?? '';
            document.getElementById('viewPropertyNo').textContent = data.property_no ?? '';
            document.getElementById('viewModel').textContent = data.model ?? '';
            document.getElementById('viewBrand').textContent = data.brand ?? '';

            // 🔹 New fields
            document.getElementById('viewInventoryTag').textContent = data.inventory_tag ?? '';
            document.getElementById('viewEmployeeName').textContent = data.employee_name ?? '';

            // Compute total value
            const totalValue = parseFloat(data.value) * parseInt(data.quantity);
            document.getElementById('viewTotalValue').textContent = totalValue.toFixed(2);

            // Images
            document.getElementById('viewQrCode').src = '../img/' + data.qr_code;
            document.getElementById('municipalLogoImg').src = '../img/' + data.system_logo;
            document.getElementById('viewAssetImage').src = '../img/assets/' + data.image;

            // Items table (from asset_items)
            const itemsBody = document.getElementById('viewItemsBody');
            if (itemsBody) {
              itemsBody.innerHTML = '';
              const items = Array.isArray(data.items) ? data.items : [];
              if (items.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 6;
                td.className = 'text-center text-muted';
                td.textContent = 'No item records available';
                tr.appendChild(td);
                itemsBody.appendChild(tr);
              } else {
                items.forEach(it => {
                  const tr = document.createElement('tr');
                  tr.innerHTML = `
                    <td>${it.item_id}</td>
                    <td>${data.property_no ?? ''}</td>
                    <td>${it.inventory_tag ?? ''}</td>
                    <td>${it.serial_no ?? ''}</td>
                    <td>${it.status ?? ''}</td>
                    <td><img src="../img/qrcodes/${it.qr_code}" alt="QR" style="height:32px"></td>
                    <td>${it.date_acquired ? new Date(it.date_acquired).toLocaleDateString('en-US') : ''}</td>
                    <td class="text-nowrap">
                      <a class="btn btn-sm btn-outline-primary" href="create_mr.php?item_id=${it.item_id}" target="_blank">
                        <i class="bi bi-tag"></i> Create Property Tag
                      </a>
                    </td>
                  `;
                  itemsBody.appendChild(tr);
                });
              }
            }
          })
          .catch(error => {
            console.error('Error:', error);
          });
      });
    });
  </script>


</body>

</html>