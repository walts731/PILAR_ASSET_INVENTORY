<?php
require_once '../connect.php';
session_start();

// Allow guest access when flagged; otherwise require user_id
if (!isset($_SESSION['user_id']) && empty($_SESSION['is_guest'])) {
  header("Location: ../index.php");
  exit();
}

// Set office_id if not set (skip DB lookup for guest)
if (empty($_SESSION['is_guest'])) {
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
}

// Fetch full name (guest-safe)
$user_name = '';
if (!empty($_SESSION['is_guest'])) {
  $user_name = 'Guest';
} else {
  $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $stmt->bind_result($fullname);
  $stmt->fetch();
  $stmt->close();
  $user_name = $fullname;
}

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

    .status-badge {
      font-weight: 500;
    }
  </style>
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">

    <?php include 'includes/topbar.php' ?>

    <!-- Page Header (mirrors MAIN_ADMIN) -->
    <div class="container-fluid px-0 mb-3">
      <div class="page-header p-3 p-sm-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border" style="width:48px;height:48px;">
            <i class="bi bi-archive text-primary fs-4"></i>
          </div>
          <div>
            <div class="h4 mb-0 title">Inventory</div>
            <div class="text-muted small">View assets and consumables</div>
          </div>
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
        $res = $conn->query("SELECT status, red_tagged FROM assets WHERE type = 'asset'");
        while ($r = $res->fetch_assoc()) {
          $total++;
          if ($r['status'] === 'available') $active++;
          if ($r['status'] === 'borrowed') $borrowed++;
          if ($r['red_tagged']) $red_tagged++;
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
          <form action="generate_selected_report.php" method="POST" target="_blank">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5 class="mb-0">Asset List</h5>
              <button type="submit" class="btn btn-outline-primary rounded-pill btn-sm ">
                <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
              </button>
            </div>
            <div class="alert alert-danger" role="alert" id="checkboxAlert">
              Please select at least one item to generate a report.
            </div>

            <div class="card-body table-responsive">
              <table id="assetTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th><input type="checkbox" id="selectAllAssets" /></th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Value</th>
                    <th>Acquired</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $stmt = $conn->query("SELECT a.*, c.category_name FROM assets a JOIN categories c ON a.category = c.id WHERE a.type = 'asset'");
                  while ($row = $stmt->fetch_assoc()):
                  ?>
                    <tr>
                      <td><input type="checkbox" class="asset-checkbox" name="selected_assets[]" value="<?= $row['id'] ?>"></td>
                      
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
        $ctotal = $cactive = $clow_stock = 0;
        $threshold = 5;
        $cres = $conn->query("SELECT status, quantity FROM assets WHERE type = 'consumable'");
        while ($r = $cres->fetch_assoc()) {
          $ctotal++;
          if ($r['status'] === 'available') $cactive++;
          if ((int)$r['quantity'] <= $threshold) $clow_stock++;
        }
        ?>

        <div class="row mb-4">
          <div class="col-12 col-sm-6 col-md-4 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <h5>Total</h5>
                <h3><?= $ctotal ?></h3>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-4 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <h5>Available</h5>
                <h3><?= $cactive ?></h3>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-4 mb-3">
            <div id="lowStockCard" class="card shadow-sm border-warning h-100" style="cursor: pointer;">
              <div class="card-body">
                <h5>Low Stock</h5>
                <h3><?= $clow_stock ?></h3>
              </div>
            </div>

          </div>
        </div>

        <div class="card shadow-sm">
          <form action="generate_selected_report.php" method="POST" target="_blank">
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
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $threshold = 5; // adjust threshold if needed
                  $stmt = $conn->query("
            SELECT a.*, c.category_name 
            FROM assets a 
            JOIN categories c ON a.category = c.id 
            WHERE a.type = 'consumable'
          ");
                  while ($row = $stmt->fetch_assoc()):
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
</body>

</html>