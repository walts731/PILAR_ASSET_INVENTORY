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
  <style>
    :root {
      --inv-accent: #0d6efd;
      --inv-muted: #6c757d;
    }

    .page-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #eef3ff 100%);
      border: 1px solid #e9ecef;
      border-radius: .75rem;
    }

    .page-header .title {
      font-weight: 600;
    }

    .toolbar .btn {
      transition: transform .08s ease-in;
    }

    .toolbar .btn:hover {
      transform: translateY(-1px);
    }

    .card-hover:hover {
      box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .06) !important;
    }

    .table thead th {
      position: sticky;
      top: 0;
      background: #f8f9fa;
      z-index: 1;
    }

    .status-badge {
      font-weight: 500;
    }

    .badge-soft {
      background: rgba(13, 110, 253, .1);
      color: var(--inv-accent);
    }
  </style>
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">

    <?php include 'includes/topbar.php' ?>

    <?php include 'alerts/inventory_alerts.php'; ?>
    <div id="pageAlerts" class="container mt-2"></div>

    <?php
    // Get list of offices for dropdown
    $offices = $conn->query("SELECT id, office_name FROM offices");

    // Get selected office from GET or default to user's office
    $selected_office = $_GET['office'] ?? $_SESSION['office_id'];
    ?>
    <div class="card card-filter shadow-sm mb-3">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <h5 class="mb-0">Inventory Controls</h5>
          <form method="GET" class="d-flex align-items-center gap-2 mb-0">
            <label for="officeFilter" class="form-label mb-0">Office</label>
            <select name="office" id="officeFilter" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="all" <?= $selected_office === "all" ? 'selected' : '' ?>>All</option>
              <?php while ($office = $offices->fetch_assoc()): ?>
                <option value="<?= $office['id'] ?>" <?= $office['id'] == $selected_office ? 'selected' : '' ?>>
                  <?= htmlspecialchars($office['office_name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </form>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-outline-primary rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#addAssetModal" title="Add a new asset">
            <i class="bi bi-plus-circle me-1"></i> Add Asset
          </button>
          <button class="btn btn-outline-success rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#importCSVModal" title="Import assets from CSV">
            <i class="bi bi-upload me-1"></i> Import CSV
          </button>
        </div>
      </div>
    </div>


    <?php
    // Count of assets without inventory tag (not filtered by office)
    $noPropCount = 0;
    if ($stmtCnt = $conn->prepare("SELECT COUNT(*) AS cnt FROM assets WHERE type='asset' AND quantity > 0 AND (inventory_tag IS NULL OR inventory_tag = '')")) {
      $stmtCnt->execute();
      $resCnt = $stmtCnt->get_result();
      if ($resCnt && ($rc = $resCnt->fetch_assoc())) {
        $noPropCount = (int)$rc['cnt'];
      }
      $stmtCnt->close();
    }
    ?>
    <!-- Page Header -->
    <div class="container-fluid px-0 mb-3">
      <div class="page-header p-3 p-sm-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border" style="width:48px;height:48px;">
            <i class="bi bi-archive text-primary fs-4"></i>
          </div>
          <div>
            <div class="h4 mb-0 title">Inventory</div>
            <div class="text-muted small">Manage assets and consumables across offices</div>
          </div>
        </div>
        <!-- Note: Primary actions are placed with the filter card below to avoid duplication -->
      </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="inventoryTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="assets-tab" data-bs-toggle="tab" data-bs-target="#assets" type="button" role="tab">
          <i class="bi bi-hdd-stack me-1"></i> Assets
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="consumables-tab" data-bs-toggle="tab" data-bs-target="#consumables" type="button" role="tab">
          <i class="bi bi-box-seam me-1"></i> Consumables
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="no-property-tab" data-bs-toggle="tab" data-bs-target="#no_property" type="button" role="tab">
          <i class="bi bi-tag me-1"></i> No Inventory Tag
          <span class="badge rounded-pill text-bg-secondary ms-1 align-middle"><?= $noPropCount ?></span>
        </button>
      </li>
      <?php
      // Count unserviceable assets without red tags (system-wide)
      $noRedTagCount = 0;
      $stmtNoRedTag = $conn->prepare("SELECT COUNT(*) FROM assets WHERE status = 'unserviceable' AND red_tagged = 0 AND quantity > 0");
      $stmtNoRedTag->execute();
      $stmtNoRedTag->bind_result($noRedTagCount);
      $stmtNoRedTag->fetch();
      $stmtNoRedTag->close();

      // Count all unserviceable assets (system-wide)
      $allUnserviceableCount = 0;
      $stmtAllUnserv = $conn->prepare("SELECT COUNT(*) FROM assets WHERE status = 'unserviceable' AND quantity > 0");
      $stmtAllUnserv->execute();
      $stmtAllUnserv->bind_result($allUnserviceableCount);
      $stmtAllUnserv->fetch();
      $stmtAllUnserv->close();
      ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="no-red-tag-tab" data-bs-toggle="tab" data-bs-target="#no_red_tag" type="button" role="tab">
          <i class="bi bi-exclamation-octagon me-1"></i> No Red Tag Only
          <span class="badge rounded-pill text-bg-warning ms-1 align-middle"><?= $noRedTagCount ?></span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="unserviceable-tab" data-bs-toggle="tab" data-bs-target="#unserviceable" type="button" role="tab">
          <i class="bi bi-tools me-1"></i> Unserviceable
          <span class="badge rounded-pill text-bg-danger ms-1 align-middle"><?= $allUnserviceableCount ?></span>
        </button>
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
            <div class="card-header">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0">Asset List</h5>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <label for="assetDateFilter" class="form-label mb-0 small">Filter:</label>
                    <select id="assetDateFilter" class="form-select form-select-sm" style="min-width: 120px;">
                      <option value="all">All Records</option>
                      <option value="current_month">Current Month</option>
                      <option value="current_quarter">Current Quarter</option>
                      <option value="current_year">Current Year</option>
                      <option value="last_month">Last Month</option>
                      <option value="last_quarter">Last Quarter</option>
                      <option value="last_year">Last Year</option>
                      <option value="custom">Custom Range</option>
                    </select>
                  </div>
                  <div id="customDateRangeAsset" class="d-flex align-items-center gap-2" style="display: none;">
                    <input type="date" id="assetFromDate" class="form-control form-control-sm" />
                    <span class="small">to</span>
                    <input type="date" id="assetToDate" class="form-control form-control-sm" />
                    <button class="btn btn-sm btn-outline-primary" id="applyCustomFilterAsset" title="Apply Filter">
                      <i class="bi bi-funnel"></i>
                    </button>
                  </div>
                  <button class="btn btn-sm btn-outline-secondary" id="assetRefreshBtn" title="Refresh">
                    <i class="bi bi-arrow-clockwise"></i>
                  </button>
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" id="assetExportCsvBtn" title="Export CSV">
                      <i class="bi bi-filetype-csv"></i> CSV
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="assetExportPdfBtn" title="Export PDF">
                      <i class="bi bi-filetype-pdf"></i> PDF
                    </button>
                  </div>
                  <!-- Existing Generate Report Button -->
                  <button type="submit" class="btn btn-outline-primary rounded-pill btn-sm">
                    <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
                  </button>
                </div>
              </div>
            </div>
            <div class="alert alert-danger" role="alert" id="checkboxAlert">
              Please select at least one item to generate a report.
            </div>

            <?php
            // Warn about assets without property numbers
            if ($selected_office === "all") {
              $stmtMissing = $conn->prepare("SELECT id, description FROM assets WHERE type='asset' AND quantity > 0 AND (inventory_tag IS NULL OR inventory_tag = '') ORDER BY last_updated DESC LIMIT 10");
            } else {
              $stmtMissing = $conn->prepare("SELECT id, description FROM assets WHERE type='asset' AND quantity > 0 AND office_id = ? AND (inventory_tag IS NULL OR inventory_tag = '') ORDER BY last_updated DESC LIMIT 10");
              $stmtMissing->bind_param("i", $selected_office);
            }
            $missingAssets = [];
            if ($stmtMissing) {
              $stmtMissing->execute();
              $resMissing = $stmtMissing->get_result();
              while ($m = $resMissing->fetch_assoc()) {
                $missingAssets[] = $m;
              }
              $stmtMissing->close();
            }
            if (count($missingAssets) > 0): ?>
              <div class="alert alert-warning d-flex align-items-start" role="alert">
                <div>
                  <div class="fw-bold mb-1">Some assets have no Inventory Tag</div>
                  <div class="small mb-1">Recently inserted assets may be missing inventory tags. Please review and update them.</div>
                  <ul class="mb-0 small">
                    <?php foreach ($missingAssets as $mi): ?>
                      <li>
                        <a href="#" class="text-decoration-underline viewAssetBtn" data-id="<?= $mi['id'] ?>" data-bs-toggle="modal" data-bs-target="#viewAssetModal">
                          <?= htmlspecialchars($mi['description']) ?> (ID: <?= $mi['id'] ?>)
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            <?php endif; ?>

            <div class="card-body table-responsive">
              <table id="assetTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th><input type="checkbox" id="selectAllAssets" /></th>
                    <th>ICS No</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Fetch rows from assets_new, joined with assets (which stores asset_new_id) and ics_form to respect office filter
                  if ($selected_office === "all") {
                    $stmt = $conn->prepare("
                      SELECT 
                        an.id AS an_id, 
                        an.description, 
                        an.quantity, 
                        an.unit, 
                        an.unit_cost, 
                        an.date_created,
                        COALESCE(
                          (
                            SELECT c.category_name 
                            FROM assets a 
                            LEFT JOIN categories c ON a.category = c.id 
                            WHERE a.asset_new_id = an.id 
                            ORDER BY a.id ASC 
                            LIMIT 1
                          ), 'Uncategorized'
                        ) AS category_name,
                        f.ics_no AS ics_no
                      FROM assets_new an
                      LEFT JOIN ics_form f ON f.id = an.ics_id
                       WHERE an.quantity > 0
                       ORDER BY an.date_created DESC
                     ");
                  } else {
                    $stmt = $conn->prepare("
                      SELECT 
                        an.id AS an_id, 
                        an.description, 
                        an.quantity, 
                        an.unit, 
                        an.unit_cost, 
                        an.date_created,
                        COALESCE(
                          (
                            SELECT c.category_name 
                            FROM assets a 
                            LEFT JOIN categories c ON a.category = c.id 
                            WHERE a.asset_new_id = an.id 
                            ORDER BY a.id ASC 
                            LIMIT 1
                          ), 'Uncategorized'
                        ) AS category_name,
                        f.ics_no AS ics_no
                      FROM assets_new an
                      LEFT JOIN ics_form f ON f.id = an.ics_id
                       WHERE an.office_id = ? AND an.quantity > 0
                       ORDER BY an.date_created DESC
                     ");
                    $stmt->bind_param("i", $selected_office);
                  }

                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()):

                  ?>
                    <tr>
                      <td><input type="checkbox" class="asset-checkbox" name="selected_assets_new[]" value="<?= $row['an_id'] ?>"></td>
                      <td><?= htmlspecialchars($row['ics_no'] ?? '') ?></td>
                      <td><?= htmlspecialchars($row['description']) ?></td>
                      <td><?= htmlspecialchars($row['category_name']) ?></td>
                      <td><?= (int)$row['quantity'] ?></td>
                      <td><?= htmlspecialchars($row['unit']) ?></td>
                      <td class="text-nowrap">
                        <button type="button"
                          class="btn btn-sm btn-outline-info rounded-pill viewAssetBtn"
                          data-source="assets_new"
                          data-id="<?= (int)$row['an_id'] ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#viewAssetModal">
                          <i class="bi bi-eye"></i>View
                        </button>
                        <button type="button"
  class="btn btn-sm btn-outline-secondary rounded-pill viewLifecycleBtn ms-1"
  data-source="assets_new"
  data-id="<?= (int)$row['an_id'] ?>"
  data-bs-toggle="modal"
  data-bs-target="#viewLifecycleModal"
  title="View Asset Life Cycle">
  <i class="bi bi-graph-up"></i> Life Cycle
</button>
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
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <select id="stockFilter" class="form-select form-select-sm d-inline-block w-auto">
                  <option value="">All Items</option>
                  <option value="low">Low Stock</option>
                </select>

                <!-- Export Buttons -->
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-outline-success btn-sm" onclick="exportConsumables('csv')">
                    <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm" onclick="exportConsumables('pdf')">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                  </button>
                </div>

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
              <table id="consumablesTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th><input type="checkbox" id="selectAllConsumables" /></th>
                    <th>Stock No</th>
                    <th>Description</th>
                    <th>On Hand</th>
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
                          class="btn btn-sm btn-outline-info rounded-pill viewConsumableBtn"
                          data-id="<?= $row['id'] ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#viewConsumableModal">
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


                        <!-- Enhanced Delete Button -->
                        <button type="button"
                          class="btn btn-sm btn-outline-danger rounded-pill deleteConsumableEnhancedBtn"
                          data-id="<?= $row['id'] ?>"
                          data-stock-no="<?= htmlspecialchars($row['property_no']) ?>"
                          data-description="<?= htmlspecialchars($row['description']) ?>"
                          data-category="<?= htmlspecialchars($row['category_name']) ?>"
                          data-quantity="<?= $row['quantity'] ?>"
                          data-unit="<?= htmlspecialchars($row['unit']) ?>"
                          data-value="<?= $row['value'] ?>"
                          data-status="<?= $row['status'] ?>"
                          data-office="<?= htmlspecialchars($row['office_name'] ?? 'No Office') ?>"
                          data-last-updated="<?= date('M d, Y', strtotime($row['last_updated'])) ?>"
                          title="Delete Consumable">
                          <i class="bi bi-trash"></i>
                        </button>
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
        // Query for assets missing inventory_tag (not filtered by office)
        $stmtNP = $conn->prepare("
          SELECT 
            a.*, 
            COALESCE(c.category_name, 'Uncategorized') AS category_name, 
            f.ics_no,
            p.par_no
          FROM assets a
          LEFT JOIN categories c ON a.category = c.id
          LEFT JOIN ics_form f ON a.ics_id = f.id
          LEFT JOIN par_form p ON a.par_id = p.id
          WHERE a.type = 'asset' AND a.quantity > 0 AND (a.inventory_tag IS NULL OR a.inventory_tag = '')
          ORDER BY a.last_updated DESC
        ");

        $stmtNP->execute();
        $npResult = $stmtNP->get_result();
        ?>

        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Assets Without Inventory Tag</h5>
          </div>
          <div class="card-body table-responsive">
            <table id="noPropertyTable" class="table table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>ICS/PAR No.</th>
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
                    <td>
                      <?php
                      $displayNo = 'N/A';
                      if (isset($row['value']) && $row['value'] >= 50000) {
                        $displayNo = htmlspecialchars($row['par_no'] ?? 'N/A (PAR)');
                      } else {
                        $displayNo = htmlspecialchars($row['ics_no'] ?? 'N/A (ICS)');
                      }
                      echo $displayNo;
                      ?>
                    </td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= $row['unit'] ?></td>
                    <td>&#8369; <?= number_format($row['value'], 2) ?></td>
                    <td class="text-nowrap">
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill viewAssetNoTagBtn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#viewAssetModal">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill deleteNoPropertyTagBtn"
                          data-id="<?= (int)$row['id'] ?>"
                          data-name="<?= htmlspecialchars($row['description']) ?>"
                          data-category="<?= htmlspecialchars($row['category_name']) ?>"
                          data-value="<?= number_format($row['value'], 2) ?>"
                          data-qty="<?= $row['quantity'] ?>"
                          data-unit="<?= htmlspecialchars($row['unit']) ?>"
                          data-number="<?= htmlspecialchars($displayNo) ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteNoPropertyTagModal" title="Delete Asset">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- No Red Tag Only Tab -->
      <div class="tab-pane fade" id="no_red_tag" role="tabpanel">
        <?php
        // Query for unserviceable assets without red tags, including IIRUP ID (system-wide)
        $stmtNoRedTag = $conn->prepare("
    SELECT a.*, c.category_name, o.office_name, e.name AS employee_name, ii.iirup_id
    FROM assets a
    LEFT JOIN categories c ON a.category = c.id
    LEFT JOIN offices o ON a.office_id = o.id
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN iirup_items ii ON a.id = ii.asset_id
    WHERE a.status = 'unserviceable' AND a.red_tagged = 0 AND a.quantity > 0
    ORDER BY a.last_updated DESC
  ");
        $stmtNoRedTag->execute();
        $noRedTagResult = $stmtNoRedTag->get_result();
        ?>

        <div class="row mb-4">
          <div class="col-12">
            <div class="alert alert-warning">
              <h6 class="alert-heading mb-2">
                <i class="bi bi-exclamation-triangle"></i> Unserviceable Assets Without Red Tags
              </h6>
              <p class="mb-0">
                These assets are marked as unserviceable but have not been red tagged yet.
                Consider creating IIRUP forms and red tags for proper documentation.
              </p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
              <i class="bi bi-exclamation-circle text-warning"></i>
              Unserviceable Assets Without Red Tags (<?= $noRedTagResult->num_rows ?> items)
            </h6>

          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover" id="noRedTagTable">
                <thead class="table-light">
                  <tr>
                    <th>Property No</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Office</th>
                    <th>Person Accountable</th>
                    <th>Value</th>
                    <th>Qty</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($noRedTagResult->num_rows > 0): ?>
                    <?php while ($row = $noRedTagResult->fetch_assoc()): ?>
                      <tr>
                        <td>
                          <span class="badge bg-secondary"><?= htmlspecialchars($row['property_no'] ?? 'N/A') ?></span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <?php if (!empty($row['image'])): ?>
                              <img src="../img/assets/<?= htmlspecialchars($row['image']) ?>"
                                alt="Asset" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                            <?php else: ?>
                              <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px;">
                                <i class="bi bi-image text-muted"></i>
                              </div>
                            <?php endif; ?>
                            <div>
                              <div class="fw-medium"><?= htmlspecialchars($row['description']) ?></div>
                              <?php if (!empty($row['brand']) || !empty($row['model'])): ?>
                                <small class="text-muted">
                                  <?= htmlspecialchars(trim(($row['brand'] ?? '') . ' ' . ($row['model'] ?? ''))) ?>
                                </small>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-info text-dark">
                            <?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?>
                          </span>
                        </td>
                        <td>
                          <div class="text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($row['office_name'] ?? 'Not Assigned') ?>">
                            <?= htmlspecialchars($row['office_name'] ?? 'Not Assigned') ?>
                          </div>
                        </td>
                        <td>
                          <div class="text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($row['employee_name'] ?? 'Not Assigned') ?>">
                            <?= htmlspecialchars($row['employee_name'] ?? 'Not Assigned') ?>
                          </div>
                        </td>
                        <td>
                          <span class="text-success fw-medium">
                            ₱<?= number_format((float)$row['value'], 2) ?>
                          </span>
                        </td>
                        <td>
                          <span class="badge bg-light text-dark">
                            <?= (int)$row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?>
                          </span>
                        </td>
                        <td>
                          <small class="text-muted">
                            <?= $row['last_updated'] ? date('M j, Y', strtotime($row['last_updated'])) : 'N/A' ?>
                          </small>
                        </td>
                        <td class="text-nowrap">
                          <?php if (!empty($row['iirup_id'])): ?>
                            <a href="create_red_tag.php?asset_id=<?= $row['id'] ?>&iirup_id=<?= $row['iirup_id'] ?>"
                              class="btn btn-sm btn-danger rounded-pill"
                              title="Create Red Tag">
                              <i class="bi bi-tag-fill"></i> Create Red Tag
                            </a>
                          <?php else: ?>
                            <div class="d-flex gap-1">
                              <a href="forms.php?id=7&asset_id=<?= $row['id'] ?>&asset_description=<?= urlencode($row['description']) ?>&inventory_tag=<?= urlencode($row['inventory_tag'] ?? $row['property_no'] ?? '') ?>"
                                class="btn btn-sm btn-outline-warning rounded-pill"
                                title="Create IIRUP Form First">
                                <i class="bi bi-file-earmark-plus"></i>
                              </a>
                              <small class="text-muted align-self-center">IIRUP Required</small>
                            </div>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="9" class="text-center py-4">
                        <div class="text-muted">
                          <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                          <h6>No Unserviceable Assets Without Red Tags</h6>
                          <p class="mb-0">All unserviceable assets have been properly red tagged.</p>
                        </div>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <?php $stmtNoRedTag->close(); ?>
      </div>

      <!-- Unserviceable Tab -->
      <div class="tab-pane fade" id="unserviceable" role="tabpanel">
        <?php
        // Query for all unserviceable assets, including IIRUP ID and additional_images (system-wide)
        $stmtAllUnserv = $conn->prepare("
          SELECT a.*, c.category_name, o.office_name, e.name AS employee_name, ii.iirup_id
          FROM assets a
          LEFT JOIN categories c ON a.category = c.id
          LEFT JOIN offices o ON a.office_id = o.id
          LEFT JOIN employees e ON a.employee_id = e.employee_id
          LEFT JOIN iirup_items ii ON a.id = ii.asset_id
          WHERE a.status = 'unserviceable' AND a.quantity > 0
          ORDER BY a.last_updated DESC
        ");

        $stmtAllUnserv->execute();
        $allUnservResult = $stmtAllUnserv->get_result();
        ?>

        <div class="row mb-4">
          <div class="col-12">
            <div class="alert alert-info">
              <h6 class="alert-heading mb-2">
                <i class="bi bi-info-circle"></i> All Unserviceable Assets
              </h6>
              <p class="mb-0">
                This shows all assets marked as unserviceable, including those with and without red tags.
              </p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
              <i class="bi bi-exclamation-circle text-info"></i>
              All Unserviceable Assets (<?= $allUnservResult->num_rows ?> items)
            </h6>
            <form id="unservReportForm" class="d-flex align-items-center gap-2 flex-wrap" action="generate_unserviceable_report.php" method="POST" target="_blank">
              <input type="hidden" name="office" value="<?= htmlspecialchars($selected_office) ?>">
              <label for="unservReportType" class="mb-0 small">Report:</label>
              <select id="unservReportType" name="report_type" class="form-select form-select-sm w-auto">
                <option value="monthly" selected>Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
              <div id="unservMonthWrap" class="d-flex align-items-center gap-1">
                <label for="unservMonth" class="mb-0 small">Month:</label>
                <select id="unservMonth" name="month" class="form-select form-select-sm w-auto">
                  <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($m == (int)date('n')) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <label for="unservYear" class="mb-0 small">Year:</label>
              <select id="unservYear" name="year" class="form-select form-select-sm w-auto">
                <?php $currentY = (int)date('Y');
                for ($y = $currentY; $y >= $currentY - 10; $y--): ?>
                  <option value="<?= $y ?>" <?= ($y === $currentY) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
              </select>
              <div class="vr d-none d-md-block"></div>

              <select id="redTagFilter" class="form-select form-select-sm w-auto me-2">
                <option value="all">All Items</option>
                <option value="tagged">Red Tagged Only</option>
                <option value="not_tagged">Not Red Tagged Only</option>
              </select>

              <!-- Export Buttons -->
              <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportUnserviceable('csv')">
                  <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="exportUnserviceable('pdf')">
                  <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
              </div>

              <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
              </button>
              <button type="button" id="btnPrintSelectedUnserv" class="btn btn-primary btn-sm rounded-pill">
                <i class="bi bi-printer"></i> Print Selected (<span id="unservSelectedCount">0</span>)
              </button>
            </form>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover" id="allUnserviceableTable">
                <thead class="table-light">
                  <tr>
                    <th style="width:32px;"><input type="checkbox" id="selectAllUnserv"></th>
                    <th>Property No.</th>
                    <th>Description</th>
                    <th>Person Accountable</th>
                    <th>Qty</th>
                    <th>Red Tag Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($allUnservResult->num_rows > 0): ?>
                    <?php while ($row = $allUnservResult->fetch_assoc()): ?>
                      <tr data-updated="<?= htmlspecialchars($row['last_updated'] ?? '') ?>">
                        <td>
                          <input type="checkbox" class="unserv-checkbox" name="selected_assets[]" value="<?= (int)$row['id'] ?>">
                        </td>
                        <td>
                          <div class="text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($row['property_no'] ?? $row['inventory_tag'] ?? 'Not Set') ?>">
                            <?= htmlspecialchars($row['property_no'] ?? $row['inventory_tag'] ?? 'Not Set') ?>
                          </div>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <?php if (!empty($row['image'])): ?>
                              <img src="../img/assets/<?= htmlspecialchars($row['image']) ?>"
                                alt="Asset" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                            <?php else: ?>
                              <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px;">
                                <i class="bi bi-image text-muted"></i>
                              </div>
                            <?php endif; ?>
                            <div>
                              <div class="fw-medium"><?= htmlspecialchars($row['description']) ?></div>
                              <?php if (!empty($row['brand']) || !empty($row['model'])): ?>
                                <small class="text-muted">
                                  <?= htmlspecialchars(trim(($row['brand'] ?? '') . ' ' . ($row['model'] ?? ''))) ?>
                                </small>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                        <td>
                          <div class="text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($row['employee_name'] ?? 'Not Assigned') ?>">
                            <?= htmlspecialchars($row['employee_name'] ?? 'Not Assigned') ?>
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-light text-dark">
                            <?= (int)$row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($row['red_tagged'] == 1): ?>
                            <span class="badge bg-danger">
                              <i class="bi bi-tag-fill"></i> Red Tagged
                            </span>
                          <?php else: ?>
                            <span class="badge bg-warning text-dark">
                              <i class="bi bi-exclamation-triangle"></i> No Red Tag
                            </span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <small class="text-muted">
                            <?= $row['last_updated'] ? date('M j, Y', strtotime($row['last_updated'])) : 'N/A' ?>
                          </small>
                        </td>
                        <td class="text-nowrap">
                          <div class="d-flex gap-1 flex-wrap">
                            <!-- View Button -->
                            <button type="button"
                              class="btn btn-sm btn-outline-info rounded-pill"
                              onclick="viewAssetDetails(<?= $row['id'] ?>)"
                              title="View Asset Details">
                              <i class="bi bi-eye"></i> View
                            </button>

                            <?php if ($row['red_tagged'] == 0): ?>
                              <?php if (!empty($row['iirup_id'])): ?>
                                <a href="create_red_tag.php?asset_id=<?= $row['id'] ?>&iirup_id=<?= $row['iirup_id'] ?>"
                                  class="btn btn-sm btn-danger rounded-pill"
                                  title="Create Red Tag">
                                  <i class="bi bi-tag-fill"></i> Red Tag
                                </a>
                              <?php else: ?>
                                <a href="forms.php?id=7&asset_id=<?= $row['id'] ?>&asset_description=<?= urlencode($row['description']) ?>&inventory_tag=<?= urlencode($row['inventory_tag'] ?? $row['property_no'] ?? '') ?>"
                                  class="btn btn-sm btn-outline-warning rounded-pill"
                                  title="Create IIRUP Form First">
                                  <i class="bi bi-file-earmark-plus"></i> IIRUP
                                </a>
                              <?php endif; ?>
                            <?php else: ?>
                              <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Processed
                              </span>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="8" class="text-center py-4">
                        <div class="text-muted">
                          <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                          <h6>No Unserviceable Assets</h6>
                          <p class="mb-0">All assets are in serviceable condition.</p>
                        </div>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <?php $stmtAllUnserv->close(); ?>
      </div>
    </div>
  </div>
  <?php include 'modals/update_consumable_modal.php'; ?>
  <?php include 'modals/delete_consumable_modal.php'; ?>
  <?php include 'modals/update_asset_modal.php'; ?>
  <?php include 'modals/delete_asset_modal.php'; ?>
  <?php include 'modals/delete_no_property_tag_modal.php'; ?>
  <?php include 'modals/add_asset_modal.php'; ?>
  <?php include 'modals/manage_categories_modal.php'; ?>
  <?php include 'modals/view_asset_modal.php'; ?>
  <?php include 'modals/view_consumable_modal.php'; ?>
  <?php include 'modals/import_csv_modal.php'; ?>
  <?php include 'modals/delete_consumable_enhanced_modal.php'; ?>
  <?php include 'modals/view_lifecycle_modal.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
  <script src="js/delete_consumable_enhanced.js"></script>
  <script src="js/consumables_export.js"></script>
  <script src="js/unserviceable_export.js"></script>

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
        const source = this.getAttribute('data-source') || 'assets';

        const url = source === 'assets_new' ?
          `get_assets_new_details.php?id=${assetId}` :
          `get_asset_details.php?id=${assetId}`;

        fetch(url)
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
            document.getElementById('viewQuantity').textContent = data.quantity;
            document.getElementById('viewUnit').textContent = data.unit;
            document.getElementById('viewDescription').textContent = data.description;
            document.getElementById('viewAcquisitionDate').textContent = formatDateFormal(data.acquisition_date);
            document.getElementById('viewLastUpdated').textContent = formatDateFormal(data.last_updated);
            document.getElementById('viewValue').textContent = parseFloat(data.value).toFixed(2);

            // Sections Identification / Specifications & Assignment / Status removed from modal

            // Compute total value
            const totalValue = parseFloat(data.value) * parseInt(data.quantity);
            document.getElementById('viewTotalValue').textContent = totalValue.toFixed(2);

            // Images (guard elements in case some are removed from modal)
            const logoEl = document.getElementById('municipalLogoImg');
            if (logoEl) logoEl.src = '../img/' + (data.system_logo ?? '');

            // Handle main image and additional images
            const imgEl = document.getElementById('viewAssetImage');
            const imagesCard = document.getElementById('viewImagesCard');
            const mainImageContainer = document.getElementById('mainImageContainer');
            const additionalImagesContainer = document.getElementById('additionalImagesContainer');
            const additionalImagesDiv = document.getElementById('viewAdditionalImages');

            let hasImages = false;

            // Handle main image
            if (data.image && imgEl) {
              imgEl.src = '../img/assets/' + data.image;
              mainImageContainer.style.display = 'block';
              hasImages = true;
            } else if (mainImageContainer) {
              mainImageContainer.style.display = 'none';
            }

            // Handle additional images
            if (additionalImagesDiv) {
              additionalImagesDiv.innerHTML = '';

              if (data.additional_images) {
                let additionalImages = [];
                try {
                  additionalImages = JSON.parse(data.additional_images);
                } catch (e) {
                  console.error('Error parsing additional images:', e);
                }

                if (Array.isArray(additionalImages) && additionalImages.length > 0) {
                  additionalImages.forEach((imageName, index) => {
                    const imgDiv = document.createElement('div');
                    imgDiv.className = 'position-relative';
                    imgDiv.innerHTML = `
                                <img src="../img/assets/${imageName}" 
                                     alt="Additional Image ${index + 1}" 
                                     class="img-thumbnail" 
                                     style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                     onclick="showImageModal('../img/assets/${imageName}', 'Additional Image ${index + 1}')">
                                <div class="position-absolute top-0 end-0 bg-primary text-white rounded-circle" 
                                     style="width: 18px; height: 18px; font-size: 10px; display: flex; align-items: center; justify-content: center; margin: -5px;">
                                    ${index + 1}
                                </div>
                            `;
                    additionalImagesDiv.appendChild(imgDiv);
                  });
                  additionalImagesContainer.style.display = 'block';
                  hasImages = true;
                } else {
                  additionalImagesContainer.style.display = 'none';
                }
              } else {
                additionalImagesContainer.style.display = 'none';
              }
            }

            // Show/hide images card based on whether there are any images
            if (imagesCard) {
              imagesCard.style.display = hasImages ? 'block' : 'none';
            }

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
                    <td>${it.property_no ?? ''}</td>
                    <td>${it.inventory_tag ?? ''}</td>
                    <td>${it.serial_no ?? ''}</td>
                    <td>${it.status ?? ''}</td>
                    <td>${it.date_acquired ? new Date(it.date_acquired).toLocaleDateString('en-US') : ''}</td>
                    <td class="text-nowrap d-flex gap-1">
                      <a class="btn btn-sm btn-outline-primary" href="create_mr.php?asset_id=${it.item_id}" target="_blank" title="${(it.property_no && it.property_no.trim()) ? 'View Property Tag' : 'Create Property Tag'}">
                        <i class="bi bi-tag"></i> ${ (it.property_no && it.property_no.trim()) ? 'View Property Tag' : 'Create Property Tag' }
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

    // ================= Unserviceable Tab: DataTable, Filters, and Bulk Select =================
    (function() {
      const $table = $('#allUnserviceableTable');
      if ($table.length === 0) return;

      // Initialize DataTable (guard against double initialization)
      const dt = $.fn.DataTable.isDataTable($table) ?
        $table.DataTable() :
        $table.DataTable({
          order: [
            [6, 'desc']
          ], // Last Updated column index after adding checkbox
          columnDefs: [{
              targets: 0,
              orderable: false,
              searchable: false
            }, // checkbox column
            {
              targets: -1,
              orderable: false,
              searchable: false
            } // actions column
          ]
        });

      // Search box hookup
      const $search = $('#unservSearch');
      if ($search.length) {
        $search.on('keyup change', function() {
          dt.search(this.value).draw();
        });
      }

      // Custom filter for month/year using each row's data-updated attribute
      const typeSel = document.getElementById('unservReportType');
      const monthSel = document.getElementById('unservMonth');
      const yearSel = document.getElementById('unservYear');

      $.fn.dataTable.ext.search.push(function(settings, rowData, rowIndex, rowNodes) {
        if (settings.nTable !== $table.get(0)) return true; // only apply to this table
        const tr = dt.row(rowIndex).node();
        const raw = tr && tr.getAttribute('data-updated');
        if (!raw) return true; // no date -> include
        const d = new Date(raw);
        if (isNaN(d)) return true;

        const selType = typeSel ? typeSel.value : 'monthly';
        const selYear = yearSel ? parseInt(yearSel.value, 10) : d.getFullYear();
        if (selType === 'yearly') {
          return d.getFullYear() === selYear;
        } else {
          const selMonth = monthSel ? parseInt(monthSel.value, 10) : (d.getMonth() + 1);
          return d.getFullYear() === selYear && (d.getMonth() + 1) === selMonth;
        }
      });

      const triggerFilter = () => dt.draw();
      if (typeSel) typeSel.addEventListener('change', triggerFilter);
      if (monthSel) monthSel.addEventListener('change', triggerFilter);
      if (yearSel) yearSel.addEventListener('change', triggerFilter);

      // Month visibility toggle (reuse if exists)
      const monthWrap = document.getElementById('unservMonthWrap');
      const toggleMonth = () => {
        if (typeSel && monthWrap) monthWrap.style.display = (typeSel.value === 'monthly') ? 'flex' : 'none';
      };
      toggleMonth();
      if (typeSel) typeSel.addEventListener('change', toggleMonth);

      // Persistent selection store
      const selected = new Set();
      const $count = $('#unservSelectedCount');
      const updateCount = () => $count.text(selected.size);

      // Apply selection state when table draws (pagination, filtering, etc.)
      $table.on('draw.dt', function() {
        dt.rows({
          search: 'applied'
        }).every(function() {
          const node = this.node();
          const cb = node.querySelector('input.unserv-checkbox');
          if (cb) cb.checked = selected.has(cb.value);
        });
      });

      // Row checkbox handler
      $table.on('change', 'input.unserv-checkbox', function() {
        if (this.checked) selected.add(this.value);
        else selected.delete(this.value);
        updateCount();
      });

      // Select All for filtered rows
      $('#selectAllUnserv').on('change', function() {
        const checked = this.checked;
        dt.rows({
          search: 'applied'
        }).every(function() {
          const node = this.node();
          const cb = node.querySelector('input.unserv-checkbox');
          if (cb) {
            cb.checked = checked;
            if (checked) selected.add(cb.value);
            else selected.delete(cb.value);
          }
        });
        updateCount();
      });

      // Print Selected button posts selected ids
      $('#btnPrintSelectedUnserv').on('click', function() {
        if (selected.size === 0) {
          alert('Please select at least one asset.');
          return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'generate_unserviceable_report.php';
        form.target = '_blank';

        // Append selected ids
        selected.forEach(id => {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = 'selected_assets[]';
          inp.value = id;
          form.appendChild(inp);
        });

        // Include office
        const office = document.querySelector('input[name="office"][type="hidden"]');
        if (office) {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = 'office';
          inp.value = office.value;
          form.appendChild(inp);
        }

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
      });
    })();
    // ================= End Unserviceable enhancements =================

    // Dedicated handler for No Property Tag tab to avoid conflicts with other tabs
    document.querySelectorAll('.viewAssetNoTagBtn').forEach(button => {
      button.addEventListener('click', function() {
        const assetId = this.getAttribute('data-id');
        if (!assetId) {
          alert('No asset ID found');
          return;
        }

        // Always fetch from main asset details endpoint; backend may return either direct object or {success, asset}
        fetch(`get_asset_details.php?id=${assetId}`)
          .then(r => r.json())
          .then(resp => {
            // Normalize response: direct object or wrapped
            const data = (resp && typeof resp === 'object' && 'asset' in resp) ?
              resp.asset :
              resp;
            if (!data || data.error || resp?.success === false) {
              alert(data?.error || resp?.message || 'Failed to load asset details');
              return;
            }

            // Safe setters
            const setText = (id, val) => {
              const el = document.getElementById(id);
              if (el) el.textContent = val ?? '';
            };

            // Populate core fields
            setText('viewOfficeName', data.office_name ?? '');
            setText('viewCategoryName', `${data.category_name ?? ''}${data.category_type ? ' (' + data.category_type + ')' : ''}`);
            setText('viewType', data.type ?? '');
            setText('viewQuantity', data.quantity ?? '');
            setText('viewUnit', data.unit ?? '');
            setText('viewDescription', data.description ?? '');
            if (data.acquisition_date) setText('viewAcquisitionDate', formatDateFormal(data.acquisition_date));
            if (data.last_updated) setText('viewLastUpdated', formatDateFormal(data.last_updated));
            const valNum = parseFloat(data.value || 0) || 0;
            setText('viewValue', valNum.toFixed(2));
            const totalValue = valNum * (parseInt(data.quantity || 1) || 1);
            setText('viewTotalValue', isFinite(totalValue) ? totalValue.toFixed(2) : '0.00');

            // Images
            const logoEl = document.getElementById('municipalLogoImg');
            if (logoEl && data.system_logo) logoEl.src = '../img/' + data.system_logo;

            const imgEl = document.getElementById('viewAssetImage');
            const imagesCard = document.getElementById('viewImagesCard');
            const mainImageContainer = document.getElementById('mainImageContainer');
            const additionalImagesContainer = document.getElementById('additionalImagesContainer');
            const additionalImagesDiv = document.getElementById('viewAdditionalImages');
            let hasImages = false;

            if (imgEl && data.image) {
              imgEl.src = '../img/assets/' + data.image;
              if (mainImageContainer) mainImageContainer.style.display = 'block';
              hasImages = true;
            } else if (mainImageContainer) {
              mainImageContainer.style.display = 'none';
            }

            if (additionalImagesDiv) {
              additionalImagesDiv.innerHTML = '';
              let additionalImages = [];
              if (data.additional_images) {
                try {
                  additionalImages = Array.isArray(data.additional_images) ? data.additional_images : JSON.parse(data.additional_images);
                } catch (e) {
                  additionalImages = [];
                }
              }
              if (Array.isArray(additionalImages) && additionalImages.length > 0) {
                additionalImages.forEach((imageName, index) => {
                  const imgDiv = document.createElement('div');
                  imgDiv.className = 'position-relative';
                  imgDiv.innerHTML = `
                    <img src="../img/assets/${imageName}" alt="Additional Image ${index + 1}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;" onclick="showImageModal('../img/assets/${imageName}', 'Additional Image ${index + 1}')">
                    <div class="position-absolute top-0 end-0 bg-primary text-white rounded-circle" style="width: 18px; height: 18px; font-size: 10px; display: flex; align-items: center; justify-content: center; margin: -5px;">${index + 1}</div>`;
                  additionalImagesDiv.appendChild(imgDiv);
                });
                if (additionalImagesContainer) additionalImagesContainer.style.display = 'block';
                hasImages = true;
              } else {
                if (additionalImagesContainer) additionalImagesContainer.style.display = 'none';
              }
            }
            if (imagesCard) imagesCard.style.display = hasImages ? 'block' : 'none';

            // Items table: prefer server-provided items; fallback to single main-asset row
            const itemsBody = document.getElementById('viewItemsBody');
            if (itemsBody) {
              itemsBody.innerHTML = '';
              const items = Array.isArray(data.items) ? data.items : [];
              if (items.length > 0) {
                items.forEach(it => {
                  const tr = document.createElement('tr');
                  tr.innerHTML = `
                    <td>${it.property_no ?? ''}</td>
                    <td>${it.inventory_tag ?? ''}</td>
                    <td>${it.serial_no ?? ''}</td>
                    <td>${it.status ?? ''}</td>
                    <td>${it.date_acquired ? new Date(it.date_acquired).toLocaleDateString('en-US') : ''}</td>
                    <td class="text-nowrap d-flex gap-1">
                      <a class="btn btn-sm btn-outline-primary" href="create_mr.php?asset_id=${it.item_id ?? data.id}" target="_blank" title="${(it.property_no && it.property_no.trim()) ? 'View Property Tag' : 'Create Property Tag'}">
                        <i class="bi bi-tag"></i> ${(it.property_no && it.property_no.trim()) ? 'View Property Tag' : 'Create Property Tag'}
                      </a>
                    </td>`;
                  itemsBody.appendChild(tr);
                });
              } else {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                  <td>${data.property_no ?? ''}</td>
                  <td>${data.inventory_tag ?? ''}</td>
                  <td>${data.serial_no ?? ''}</td>
                  <td>${data.status ?? ''}</td>
                  <td>${data.acquisition_date ? new Date(data.acquisition_date).toLocaleDateString('en-US') : ''}</td>
                  <td class="text-nowrap d-flex gap-1">
                    <a class="btn btn-sm btn-outline-primary" href="create_mr.php?asset_id=${data.id}" target="_blank" title="${(data.property_no && String(data.property_no).trim()) ? 'Edit Property Tag' : 'Create Property Tag'}">
                      <i class="bi bi-tag"></i> ${(data.property_no && String(data.property_no).trim()) ? 'Edit Property Tag' : 'Create Property Tag'}
                    </a>
                  </td>`;
                itemsBody.appendChild(tr);
              }
            }
          })
          .catch(err => console.error('NoTag fetch error:', err));
      });
    });

    // View Consumable Modal logic
    function setBadge(el, status) {
      if (!el) return;
      const s = (status || '').toLowerCase();
      el.className = 'badge ' + (s === 'available' ? 'bg-success' : 'bg-secondary');
      el.textContent = status ? (status.charAt(0).toUpperCase() + status.slice(1)) : '—';
    }

    function fmtDate(dateStr) {
      if (!dateStr) return '—';
      const d = new Date(dateStr);
      return d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    }

    document.querySelectorAll('.viewConsumableBtn').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        fetch(`get_consumable_details.php?id=${id}`)
          .then(r => r.json())
          .then(data => {
            if (data.error) {
              alert(data.error);
              return;
            }

            // Populate basic fields
            document.getElementById('consDescription').textContent = data.description ?? '—';
            document.getElementById('consOffice').textContent = data.office_name ?? '—';
            document.getElementById('consPropertyNo').textContent = data.property_no ?? '—';
            document.getElementById('consQuantity').textContent = parseInt(data.quantity ?? 0);
            document.getElementById('consAddedStock').textContent = parseInt(data.added_stock ?? 0);
            document.getElementById('consUnit').textContent = data.unit ?? '—';
            setBadge(document.getElementById('consStatus'), data.status ?? '');

            const value = parseFloat(data.value ?? 0) || 0;
            const qty = parseInt(data.quantity ?? 0) || 0;
            document.getElementById('consValue').textContent = value.toFixed(2);
            document.getElementById('consTotalValue').textContent = data.total_value ? parseFloat(data.total_value).toFixed(2) : (value * qty).toFixed(2);
            document.getElementById('consLastUpdated').textContent = data.last_updated_formatted ?? '—';

            // Image
            const imgEl = document.getElementById('consImage');
            if (imgEl) {
              const img = data.image ? `../img/assets/${data.image}` : '';
              imgEl.style.display = img ? 'block' : 'none';
              imgEl.src = img;
            }
          })
          .catch(err => console.error('Fetch consumable error', err));
      });
    });

    // Global helper to handle per-item actions inside the View Asset modal
    window.openAssetAction = function(action, assetId) {
      if (!assetId) return;
      fetch(`get_asset_details.php?id=${assetId}`)
        .then(r => r.json())
        .then(data => {
          if (data.error) {
            alert(data.error);
            return;
          }
          if (action === 'view') {
            // Re-populate the same modal with the selected asset's details
            document.querySelector(`#viewAssetModal .viewAssetBtn[data-id="${assetId}"]`);
            // Reuse existing population logic by manually setting fields
            document.getElementById('viewOfficeName').textContent = data.office_name ?? '';
            document.getElementById('viewCategoryName').textContent = `${data.category_name ?? ''} (${data.category_type ?? ''})`;
            document.getElementById('viewType').textContent = data.type ?? '';
            document.getElementById('viewQuantity').textContent = data.quantity ?? '';
            document.getElementById('viewUnit').textContent = data.unit ?? '';
            document.getElementById('viewDescription').textContent = data.description ?? '';
            document.getElementById('viewAcquisitionDate').textContent = data.acquisition_date ? formatDateFormal(data.acquisition_date) : '';
            document.getElementById('viewLastUpdated').textContent = data.last_updated ? formatDateFormal(data.last_updated) : '';
            document.getElementById('viewValue').textContent = data.value ? parseFloat(data.value).toFixed(2) : '0.00';
            const totalValue = (parseFloat(data.value || 0) * parseInt(data.quantity || 1));
            document.getElementById('viewTotalValue').textContent = isFinite(totalValue) ? totalValue.toFixed(2) : '0.00';

            // Rebuild items table for this specific asset (single row)
            const itemsBody = document.getElementById('viewItemsBody');
            if (itemsBody) {
              itemsBody.innerHTML = '';
              const it = {
                item_id: data.id,
                property_no: data.property_no,
                inventory_tag: data.inventory_tag,
                serial_no: data.serial_no,
                status: data.status,
                date_acquired: data.acquisition_date
              };
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${it.item_id}</td>
                <td>${it.property_no ?? ''}</td>
                <td>${it.inventory_tag ?? ''}</td>
                <td>${it.serial_no ?? ''}</td>
                <td>${it.status ?? ''}</td>
                <td>${it.date_acquired ? new Date(it.date_acquired).toLocaleDateString('en-US') : ''}</td>
                <td class="text-nowrap d-flex gap-1">
                  <a class="btn btn-sm btn-outline-primary" href="create_mr.php?asset_id=${it.item_id}" target="_blank" title="Create/Edit Property Tag">
                    <i class="bi bi-tag"></i> ${ (it.property_no && it.property_no.trim()) ? 'Edit Property Tag' : 'Create Property Tag' }
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-primary" title="Edit Asset" data-bs-toggle="modal" data-bs-target="#updateAssetModal" onclick="openAssetAction('edit', ${it.item_id})"><i class="bi bi-pencil"></i></button>
                  <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Asset" data-bs-toggle="modal" data-bs-target="#deleteAssetModal" onclick="openAssetAction('delete', ${it.item_id})"><i class="bi bi-trash"></i></button>
                </td>
              `;
              itemsBody.appendChild(tr);
            }
          } else if (action === 'edit') {
            // Populate Update Asset modal fields
            const setVal = (id, val) => {
              const el = document.getElementById(id);
              if (el) el.value = val ?? '';
            };
            setVal('asset_id', data.id);
            setVal('edit_asset_description', data.description);
            setVal('edit_asset_unit', data.unit);
            setVal('edit_asset_quantity', data.quantity);
            setVal('edit_asset_status', data.status);
            setVal('edit_asset_serial', data.serial_no);
            setVal('edit_asset_code', data.code);
            setVal('edit_asset_property', data.property_no);
            setVal('edit_asset_model', data.model);
            setVal('edit_asset_brand', data.brand);
            const imgPrev = document.getElementById('edit_asset_preview');
            if (imgPrev && data.image) imgPrev.src = '../img/assets/' + data.image;
            const updateModalEl = document.getElementById('updateAssetModal');
            if (updateModalEl) new bootstrap.Modal(updateModalEl).show();
          } else if (action === 'delete') {
            // Populate Delete Asset modal
            const setVal = (id, val) => {
              const el = document.getElementById(id);
              if (el) el.value = val ?? '';
            };
            setVal('delete_asset_id', data.id);
            const nameEl = document.getElementById('delete_asset_name');
            if (nameEl) nameEl.textContent = data.description ?? '';
            const delModalEl = document.getElementById('deleteAssetModal');
            if (delModalEl) new bootstrap.Modal(delModalEl).show();
          }
        })
        .catch(err => console.error('Asset action error:', err));
    }

    // Force delete asset (No Property Tab) and update parent quantity
    window.forceDeleteAsset = function(assetId) {
      if (!assetId) return;
      if (!confirm('This will permanently delete the asset and update quantities. Continue?')) return;
      fetch('force_delete_asset.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'id=' + encodeURIComponent(assetId)
        })
        .then(async (r) => {
          const txt = await r.text();
          let data;
          try {
            data = JSON.parse(txt);
          } catch (e) {
            throw new Error('Invalid server response');
          }
          if (!r.ok) {
            const msg = (data && data.message) ? data.message : 'Failed to delete asset';
            throw new Error(msg);
          }
          return data;
        })
        .then(resp => {
          if (resp && resp.success) {
            const alerts = document.getElementById('pageAlerts');
            if (alerts) {
              alerts.innerHTML = `
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> Asset deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>`;
            }
            // Reload to refresh counts/lists
            setTimeout(() => location.reload(), 600);
          } else {
            const msg = (resp && resp.message) ? resp.message : 'Failed to delete asset';
            const alerts = document.getElementById('pageAlerts');
            if (alerts) {
              alerts.innerHTML = `
              <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> ${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>`;
            } else {
              alert(msg);
            }
          }
        })
        .catch(err => {
          const alerts = document.getElementById('pageAlerts');
          if (alerts) {
            alerts.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-x-circle"></i> ${err.message || 'Unexpected error while deleting asset.'}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
          } else {
            alert(err.message || 'Unexpected error while deleting asset.');
          }
        });
    }

    // Function to show image in modal
    window.showImageModal = function(imageSrc, imageTitle) {
      // Create modal if it doesn't exist
      let imageModal = document.getElementById('imageViewModal');
      if (!imageModal) {
        const modalHTML = `
          <div class="modal fade" id="imageViewModal" tabindex="-1" aria-labelledby="imageViewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="imageViewModalLabel">Asset Image</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                  <img id="modalImage" src="" alt="Asset Image" class="img-fluid" style="max-height: 70vh;">
                </div>
              </div>
            </div>
          </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        imageModal = document.getElementById('imageViewModal');
      }

      // Update modal content
      document.getElementById('imageViewModalLabel').textContent = imageTitle;
      document.getElementById('modalImage').src = imageSrc;

      // Show modal
      const modal = new bootstrap.Modal(imageModal);
      modal.show();
    }

    // Function to view asset details with multiple images
    window.viewAssetDetails = function(assetId) {
      if (!assetId) return;

      // Fetch asset details including additional images
      fetch('get_asset_details.php?id=' + encodeURIComponent(assetId))
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAssetDetailsModal(data.asset);
          } else {
            alert('Error loading asset details: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error loading asset details');
        });
    }

    // Function to show asset details modal
    function showAssetDetailsModal(asset) {
      // Create modal if it doesn't exist
      let detailsModal = document.getElementById('assetDetailsModal');
      if (!detailsModal) {
        const modalHTML = `
          <div class="modal fade" id="assetDetailsModal" tabindex="-1" aria-labelledby="assetDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="assetDetailsModalLabel">Asset Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="assetDetailsContent">
                  <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        detailsModal = document.getElementById('assetDetailsModal');
      }

      // Parse additional images
      let additionalImages = [];
      try {
        if (asset.additional_images) {
          additionalImages = JSON.parse(asset.additional_images);
        }
      } catch (e) {
        console.warn('Error parsing additional images:', e);
      }

      // Build images gallery HTML
      let imagesHTML = '';
      if (asset.image || additionalImages.length > 0) {
        imagesHTML = '<div class="row g-2 mb-4">';

        // Main image
        if (asset.image) {
          imagesHTML += `
            <div class="col-6 col-md-3">
              <div class="card">
                <img src="../img/assets/${asset.image}" 
                     class="card-img-top" 
                     style="height: 150px; object-fit: cover; cursor: pointer;"
                     onclick="showImageModal('../img/assets/${asset.image}', 'Main Image')"
                     alt="Main Asset Image">
                <div class="card-body p-2">
                  <small class="text-muted">Main Image</small>
                </div>
              </div>
            </div>
          `;
        }

        // Additional images
        additionalImages.forEach((imageName, index) => {
          imagesHTML += `
            <div class="col-6 col-md-3">
              <div class="card">
                <img src="../img/assets/${imageName}" 
                     class="card-img-top" 
                     style="height: 150px; object-fit: cover; cursor: pointer;"
                     onclick="showImageModal('../img/assets/${imageName}', 'Additional Image ${index + 1}')"
                     alt="Additional Asset Image ${index + 1}">
                <div class="card-body p-2">
                  <small class="text-muted">Image ${index + 1}</small>
                </div>
              </div>
            </div>
          `;
        });

        imagesHTML += '</div>';
      } else {
        imagesHTML = `
          <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i> No images available for this asset.
          </div>
        `;
      }

      // Build the complete modal content
      const modalContent = `
        <div class="row">
          <div class="col-12">
            <h6 class="text-primary mb-3">
              <i class="bi bi-box-seam"></i> ${asset.description || 'N/A'}
            </h6>
          </div>
        </div>

        <!-- Images Section -->
        <div class="mb-4">
          <h6 class="border-bottom pb-2 mb-3">
            <i class="bi bi-images"></i> Asset Images
          </h6>
          ${imagesHTML}
        </div>

        <!-- Asset Information -->
        <div class="row">
          <div class="col-md-6">
            <h6 class="border-bottom pb-2 mb-3">
              <i class="bi bi-info-circle"></i> Basic Information
            </h6>
            <table class="table table-sm">
              <tr><td class="fw-medium">Property No.:</td><td>${asset.property_no || asset.inventory_tag || 'Not Set'}</td></tr>
              <tr><td class="fw-medium">Description:</td><td>${asset.description || 'N/A'}</td></tr>
              <tr><td class="fw-medium">Brand:</td><td>${asset.brand || 'N/A'}</td></tr>
              <tr><td class="fw-medium">Model:</td><td>${asset.model || 'N/A'}</td></tr>
              <tr><td class="fw-medium">Serial No.:</td><td>${asset.serial_no || 'N/A'}</td></tr>
              <tr><td class="fw-medium">Code:</td><td>${asset.code || 'N/A'}</td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="border-bottom pb-2 mb-3">
              <i class="bi bi-gear"></i> Status & Details
            </h6>
            <table class="table table-sm">
              <tr><td class="fw-medium">Status:</td><td><span class="badge bg-${asset.status === 'available' ? 'success' : asset.status === 'borrowed' ? 'warning' : 'danger'}">${asset.status || 'N/A'}</span></td></tr>
              <tr><td class="fw-medium">Quantity:</td><td>${asset.quantity || '0'} ${asset.unit || ''}</td></tr>
              <tr><td class="fw-medium">Value:</td><td>₱${asset.value ? parseFloat(asset.value).toLocaleString('en-US', {minimumFractionDigits: 2}) : '0.00'}</td></tr>
              <tr><td class="fw-medium">Red Tagged:</td><td><span class="badge bg-${asset.red_tagged == 1 ? 'danger' : 'success'}">${asset.red_tagged == 1 ? 'Yes' : 'No'}</span></td></tr>
              <tr><td class="fw-medium">Acquisition Date:</td><td>${asset.acquisition_date || 'N/A'}</td></tr>
              <tr><td class="fw-medium">Last Updated:</td><td>${asset.last_updated ? new Date(asset.last_updated).toLocaleDateString() : 'N/A'}</td></tr>
            </table>
          </div>
        </div>
      `;
      // Update modal content and show
      document.getElementById('assetDetailsContent').innerHTML = modalContent;
      document.getElementById('assetDetailsModalLabel').textContent = `Asset Details - ${asset.description || 'Unknown Asset'}`;

      const modal = new bootstrap.Modal(detailsModal);
      modal.show();
    }

    // No Property Tag Delete Modal Handler
    $(document).ready(function() {
      let currentAssetId = null;

      // Handle delete button click to populate modal
      $(document).on('click', '.deleteNoPropertyTagBtn', function() {
        currentAssetId = $(this).data('id');

        // Populate modal with asset details
        $('#deleteNoPropertyAssetName').text($(this).data('name'));
        $('#deleteNoPropertyAssetCategory').text($(this).data('category'));
        $('#deleteNoPropertyAssetValue').text($(this).data('value'));
        $('#deleteNoPropertyAssetQty').text($(this).data('qty'));
        $('#deleteNoPropertyAssetUnit').text($(this).data('unit'));
        $('#deleteNoPropertyAssetNumber').text($(this).data('number'));
      });

      // Handle confirm delete button
      $('#confirmDeleteNoPropertyTag').on('click', function() {
        if (!currentAssetId) return;

        const button = $(this);
        const originalText = button.html();

        // Show loading state
        button.prop('disabled', true).html('<i class="bi bi-spinner-border spinner-border-sm me-2"></i>Deleting...');

        // Call the existing forceDeleteAsset function
        fetch('force_delete_asset.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(currentAssetId)
          })
          .then(async (r) => {
            const txt = await r.text();
            let data;
            try {
              data = JSON.parse(txt);
            } catch (e) {
              throw new Error('Invalid server response');
            }
            if (!r.ok) {
              const msg = (data && data.message) ? data.message : 'Failed to delete asset';
              throw new Error(msg);
            }
            return data;
          })
          .then(resp => {
            if (resp && resp.success) {
              // Hide modal
              $('#deleteNoPropertyTagModal').modal('hide');

              // Show success message
              const alerts = document.getElementById('pageAlerts');
              if (alerts) {
                alerts.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="bi bi-check-circle"></i> Asset deleted and archived successfully.
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
              }

              // Reload page to refresh the table
              setTimeout(() => location.reload(), 1000);
            } else {
              throw new Error(resp.message || 'Failed to delete asset');
            }
          })
          .catch(err => {
            // Reset button state
            button.prop('disabled', false).html(originalText);

            // Show error message
            const alerts = document.getElementById('pageAlerts');
            if (alerts) {
              alerts.innerHTML = `
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle"></i> ${err.message || 'Unexpected error while deleting asset.'}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>`;
            } else {
              alert(err.message || 'Unexpected error while deleting asset.');
            }
          });
      });

      // Reset modal when hidden
      $('#deleteNoPropertyTagModal').on('hidden.bs.modal', function() {
        currentAssetId = null;
        $('#confirmDeleteNoPropertyTag').prop('disabled', false).html('<i class="bi bi-trash me-2"></i>Yes, Delete Asset');
      });
    });

    // Asset filtering and export functionality
    document.addEventListener('DOMContentLoaded', function() {
      const assetDateFilter = document.getElementById('assetDateFilter');
      const customDateRangeAsset = document.getElementById('customDateRangeAsset');
      const assetFromDate = document.getElementById('assetFromDate');
      const assetToDate = document.getElementById('assetToDate');
      const applyCustomFilterAsset = document.getElementById('applyCustomFilterAsset');

      // Show/hide custom date range inputs for assets
      if (assetDateFilter) {
        assetDateFilter.addEventListener('change', function() {
          if (this.value === 'custom') {
            customDateRangeAsset.style.display = 'flex';
          } else {
            customDateRangeAsset.style.display = 'none';
            // Auto-apply filter for predefined ranges
            applyDateFilterAsset();
          }
        });
      }

      // Apply custom date filter for assets
      if (applyCustomFilterAsset) {
        applyCustomFilterAsset.addEventListener('click', applyDateFilterAsset);
      }

      // Function to get date range based on filter selection
      function getDateRangeAsset(filterType) {
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth();
        const currentQuarter = Math.floor(currentMonth / 3);

        switch (filterType) {
          case 'current_month':
            return {
              from: new Date(currentYear, currentMonth, 1).toISOString().split('T')[0],
                to: new Date(currentYear, currentMonth + 1, 0).toISOString().split('T')[0]
            };
          case 'current_quarter':
            const quarterStart = currentQuarter * 3;
            return {
              from: new Date(currentYear, quarterStart, 1).toISOString().split('T')[0],
                to: new Date(currentYear, quarterStart + 3, 0).toISOString().split('T')[0]
            };
          case 'current_year':
            return {
              from: new Date(currentYear, 0, 1).toISOString().split('T')[0],
                to: new Date(currentYear, 11, 31).toISOString().split('T')[0]
            };
          case 'last_month':
            const lastMonth = currentMonth === 0 ? 11 : currentMonth - 1;
            const lastMonthYear = currentMonth === 0 ? currentYear - 1 : currentYear;
            return {
              from: new Date(lastMonthYear, lastMonth, 1).toISOString().split('T')[0],
                to: new Date(lastMonthYear, lastMonth + 1, 0).toISOString().split('T')[0]
            };
          case 'last_quarter':
            const lastQuarter = currentQuarter === 0 ? 3 : currentQuarter - 1;
            const lastQuarterYear = currentQuarter === 0 ? currentYear - 1 : currentYear;
            const lastQuarterStart = lastQuarter * 3;
            return {
              from: new Date(lastQuarterYear, lastQuarterStart, 1).toISOString().split('T')[0],
                to: new Date(lastQuarterYear, lastQuarterStart + 3, 0).toISOString().split('T')[0]
            };
          case 'last_year':
            return {
              from: new Date(currentYear - 1, 0, 1).toISOString().split('T')[0],
                to: new Date(currentYear - 1, 11, 31).toISOString().split('T')[0]
            };
          case 'custom':
            return {
              from: assetFromDate.value,
                to: assetToDate.value
            };
          default:
            return null;
        }
      }

      // Apply date filter to asset table
      function applyDateFilterAsset() {
        const filterType = assetDateFilter.value;
        const dateRange = getDateRangeAsset(filterType);

        if (!dateRange || filterType === 'all') {
          // Show all rows
          const assetTable = document.querySelector('#assets table tbody');
          if (assetTable) {
            [...assetTable.querySelectorAll('tr')].forEach(tr => {
              tr.style.display = '';
            });
          }
          return;
        }

        const fromDate = new Date(dateRange.from);
        const toDate = new Date(dateRange.to);

        const assetTable = document.querySelector('#assets table tbody');
        if (assetTable) {
          [...assetTable.querySelectorAll('tr')].forEach(tr => {
            // Find the date created column (adjust index as needed)
            const dateCells = tr.querySelectorAll('td');
            if (dateCells.length > 10) { // Assuming date created is around column 11
              const dateText = dateCells[dateCells.length - 2].textContent.trim(); // Second to last column
              const rowDate = new Date(dateText);
              const isInRange = rowDate >= fromDate && rowDate <= toDate;
              tr.style.display = isInRange ? '' : 'none';
            }
          });
        }
      }

      // Export CSV for Assets with date filtering
      const assetExportCsvBtn = document.getElementById('assetExportCsvBtn');
      if (assetExportCsvBtn) {
        assetExportCsvBtn.addEventListener('click', () => {
          const filterType = assetDateFilter.value;
          const dateRange = getDateRangeAsset(filterType);
          const officeFilter = new URLSearchParams(window.location.search).get('office') || 'all';

          let exportUrl = 'export_assets_csv.php';
          const params = new URLSearchParams({
            office: officeFilter
          });

          if (dateRange && filterType !== 'all') {
            params.append('filter_type', filterType);
            params.append('from_date', dateRange.from);
            params.append('to_date', dateRange.to);
          }

          exportUrl += '?' + params.toString();

          // Direct download of CSV
          window.location.href = exportUrl;
        });
      }

      // Export PDF for Assets with date filtering
      const assetExportPdfBtn = document.getElementById('assetExportPdfBtn');
      if (assetExportPdfBtn) {
        assetExportPdfBtn.addEventListener('click', () => {
          const filterType = assetDateFilter.value;
          const dateRange = getDateRangeAsset(filterType);
          const officeFilter = new URLSearchParams(window.location.search).get('office') || 'all';

          let exportUrl = 'export_assets_pdf.php';
          const params = new URLSearchParams({
            office: officeFilter
          });

          if (dateRange && filterType !== 'all') {
            params.append('filter_type', filterType);
            params.append('from_date', dateRange.from);
            params.append('to_date', dateRange.to);
          }

          exportUrl += '?' + params.toString();

          // Open PDF in new tab
          window.open(exportUrl, '_blank');
        });
      }

      // Refresh button for Assets
      const assetRefreshBtn = document.getElementById('assetRefreshBtn');
      if (assetRefreshBtn) {
        assetRefreshBtn.addEventListener('click', () => {
          window.location.reload();
        });
      }
    });

    // Lifecycle viewer for Assets tab (assets_new)
document.querySelectorAll('.viewLifecycleBtn').forEach(btn => {
  btn.addEventListener('click', function() {
    const id = this.getAttribute('data-id');
    const source = this.getAttribute('data-source') || 'assets_new';
    const tableBody = document.getElementById('lifecycleBody');
    const countEl = document.getElementById('lifecycleCount');
    const assetsCountEl = document.getElementById('lifecycleAssetsCount');
    const ctxEl = document.getElementById('lifecycleContext');

    if (tableBody) {
      tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">
        <div class="spinner-border spinner-border-sm me-2"></div>Loading life cycle...
      </td></tr>`;
    }
    if (countEl) countEl.textContent = '0';
    if (assetsCountEl) assetsCountEl.textContent = '0';
    if (ctxEl) ctxEl.textContent = source === 'assets_new'
      ? 'Items created from this Acquisition (assets_new)'
      : 'Single Asset';

    fetch(`get_asset_lifecycle.php?source=${encodeURIComponent(source)}&id=${encodeURIComponent(id)}`)
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          if (tableBody) tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${data.error}</td></tr>`;
          return;
        }
        const events = Array.isArray(data.events) ? data.events : [];
        if (countEl) countEl.textContent = events.length;
        if (assetsCountEl && data.summary && typeof data.summary.assets_count !== 'undefined') {
          assetsCountEl.textContent = data.summary.assets_count;
        }
        if (tableBody) {
          if (events.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No events found.</td></tr>`;
            return;
          }
          const escapeHtml = v => (v == null ? '' : String(v)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'));
          tableBody.innerHTML = events.map(ev => {
            const ref = ev.ref_table ? `${escapeHtml(ev.ref_table)} #${escapeHtml(ev.ref_id ?? '')}` : '';
            const fromStr = [ev.from_office, ev.from_employee].filter(Boolean).map(escapeHtml).join(' • ');
            const toStr = [ev.to_office, ev.to_employee].filter(Boolean).map(escapeHtml).join(' • ');
            const t = (ev.event_type || '').toUpperCase();
            const color = { ACQUIRED: 'success', ASSIGNED: 'primary', TRANSFERRED: 'info', DISPOSAL_LISTED: 'warning', DISPOSED: 'secondary', RED_TAGGED: 'danger' }[t] || 'light';
            const typeBadge = `<span class="badge bg-${color}">${escapeHtml(t)}</span>`;
            const dt = ev.created_at ? new Date(ev.created_at).toLocaleString('en-US', { year:'numeric', month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit' }) : '';
            return `
              <tr>
                <td>${escapeHtml(dt)}</td>
                <td>${typeBadge}</td>
                <td>${fromStr || ''}</td>
                <td>${toStr || ''}</td>
                <td>${ref}</td>
                <td>${escapeHtml(ev.notes || '')}</td>
              </tr>
            `;
          }).join('');
        }

        // ==============================
        // Render roadmap steps
        // ==============================
        const stepsWrap = document.getElementById('lifecycleRoadmapSteps');
        if (stepsWrap) {
          stepsWrap.innerHTML = ''; // clear previous

          const colorFor = (t) => {
            const map = {
              ACQUIRED: 'success',
              ASSIGNED: 'primary',
              TRANSFERRED: 'info',
              DISPOSAL_LISTED: 'warning',
              DISPOSED: 'secondary',
              RED_TAGGED: 'danger'
            };
            return map[(t || '').toUpperCase()] || 'secondary';
          };
          const iconFor = (t) => {
            switch ((t || '').toUpperCase()) {
              case 'ACQUIRED': return 'bi-bag-check';
              case 'ASSIGNED': return 'bi-person-check';
              case 'TRANSFERRED': return 'bi-arrow-left-right';
              case 'DISPOSAL_LISTED': return 'bi-journal-text';
              case 'DISPOSED': return 'bi-trash';
              case 'RED_TAGGED': return 'bi-tag';
              default: return 'bi-circle';
            }
          };
          const fmt = (d) => {
            if (!d) return '';
            try {
              return new Date(d).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            } catch { return ''; }
          };
          const escapeHtml = v => (v == null ? '' : String(v)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'));

          if (events.length === 0) {
            stepsWrap.innerHTML = '<div class="text-muted small">No events</div>';
          } else {
            const steps = [...events].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            steps.forEach(ev => {
              const t = (ev.event_type || '').toUpperCase();
              const color = colorFor(t);
              const icon = iconFor(t);
              const dt = fmt(ev.created_at);
              const ref = ev.ref_table ? `${escapeHtml(ev.ref_table)} #${escapeHtml(ev.ref_id ?? '')}` : '';
              const label = escapeHtml(t.replace('_', ' '));

              const stepHtml = `
                <div class="roadmap-step">
                  <div class="roadmap-dot ${color}" title="${label}"></div>
                  <div class="roadmap-label mt-1"><i class="bi ${icon} me-1"></i>${label}</div>
                  <div class="roadmap-date">${escapeHtml(dt)}</div>
                  ${ref ? `<div class="roadmap-ref">${ref}</div>` : ''}
                </div>
              `;
              stepsWrap.insertAdjacentHTML('beforeend', stepHtml);
            });

            // Auto-scroll to the latest step
            const road = document.getElementById('lifecycleRoadmap');
            if (road) {
              setTimeout(() => { road.scrollLeft = road.scrollWidth; }, 0);
            }
          }
        }
        // ==============================

      })
      .catch(err => {
        if (tableBody) tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load life cycle.</td></tr>`;
        console.error('Lifecycle load error:', err);
      });
  });
});



  </script>

</body>

</html>