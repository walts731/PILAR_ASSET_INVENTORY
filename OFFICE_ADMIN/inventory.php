<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Place this query near the top of your dashboard PHP file
$users_result = $conn->query("SELECT id, fullname FROM users WHERE status = 'active' ORDER BY fullname ASC");
$users_list = [];
if ($users_result) {
  while ($user_row = $users_result->fetch_assoc()) {
    $users_list[] = $user_row;
  }
}

// Fetch system info
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
  $system = $result->fetch_assoc();
}

// Set office_id if not set
if (!isset($_SESSION['office_id'])) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($office_id);
  if ($stmt->fetch()) {
    $_SESSION['office_id'] = $office_id;
  }
  $stmt->close();
}

// Fetch office name
$office_name = '';
if (isset($_SESSION['office_id'])) {
  $stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['office_id']);
  $stmt->execute();
  $stmt->bind_result($office_name);
  $stmt->fetch();
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

$office_id = $_SESSION['office_id'];

// Fetch categories used by this office's assets for category filter
$asset_categories = [];
$cat_stmt = $conn->prepare("SELECT DISTINCT c.id, c.category_name
                           FROM categories c
                           INNER JOIN assets a ON a.category = c.id
                           WHERE a.type = 'asset' AND a.office_id = ? AND a.quantity > 0
                           ORDER BY c.category_name ASC");
$cat_stmt->bind_param('i', $office_id);
$cat_stmt->execute();
$cat_res = $cat_stmt->get_result();
while ($cat = $cat_res->fetch_assoc()) { $asset_categories[] = $cat; }
$cat_stmt->close();

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
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
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
      <?php
      if (!empty($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
        unset($_SESSION['success_message']);
      }
      if (!empty($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
      }
      ?>

      <!-- Assets Tab -->
      <div class="tab-pane fade show active" id="assets" role="tabpanel">
        <?php
        $total = $active = $borrowed = $red_tagged = 0;
        $res = $conn->prepare("SELECT status, red_tagged FROM assets WHERE type = 'asset' AND office_id = ? AND quantity > 0");
        $res->bind_param("i", $office_id);
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
          <?php
          $cards = [
            ['Total', $total, 'box-seam', 'primary'],
            ['Available', $active, 'check-circle', 'info'],
            ['Borrowed', $borrowed, 'arrow-left-right', 'primary'],
            ['Red-Tagged', $red_tagged, 'exclamation-triangle', 'danger']
          ];
          foreach ($cards as [$title, $value, $icon, $color]): ?>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <h5><?= $title ?></h5>
                    <h3><?= $value ?></h3>
                  </div>
                  <i class="bi bi-<?= $icon ?> text-<?= $color ?> fs-2"></i>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="card shadow-sm mb-4">
          <form action="generate_selected_report.php" method="POST" target="_blank">
            <input type="hidden" name="office" value="<?= $office_id ?>">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5 class="mb-0">Asset List</h5>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="dropdown">
                  <button class="btn btn-outline-secondary btn-sm rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-funnel"></i> Filters
                  </button>
                  <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;">
                    <div class="mb-2">
                      <label class="form-label form-label-sm mb-1">Date range</label>
                      <div class="d-flex gap-2">
                        <input type="date" id="assetsFromDate" class="form-control form-control-sm" />
                        <input type="date" id="assetsToDate" class="form-control form-control-sm" />
                      </div>
                    </div>
                    <div class="mb-2">
                      <label class="form-label form-label-sm mb-1">Category</label>
                      <select id="assetsCategory" class="form-select form-select-sm">
                        <option value="all">All</option>
                        <?php foreach ($asset_categories as $cat): ?>
                          <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-2">
                      <label class="form-label form-label-sm mb-1">Status</label>
                      <select id="assetsStatus" class="form-select form-select-sm">
                        <option value="all">All</option>
                        <option value="available">Available</option>
                        <option value="borrowed">Borrowed</option>
                        <option value="unserviceable">Unserviceable</option>
                        <option value="red_tagged">Red Tagged</option>
                      </select>
                    </div>
                    <div class="text-end">
                      <button type="button" class="btn btn-sm btn-primary"><i class="bi bi-check2"></i> Apply</button>
                    </div>
                  </div>
                </div>
                <a href="#" id="btnAssetsCsv" class="btn btn-outline-secondary rounded-pill btn-sm">
                  <i class="bi bi-filetype-csv"></i> Export CSV
                </a>
                <a href="#" id="btnAssetsPdf" class="btn btn-outline-danger rounded-pill btn-sm">
                  <i class="bi bi-filetype-pdf"></i> Export PDF
                </a>
                <button type="submit" class="btn btn-outline-primary rounded-pill btn-sm">
                  <i class="bi bi-file-earmark-arrow-down"></i> Generate Selected
                </button>
              </div>
            </div>

            <div class="card-body table-responsive">
              <table id="assetTable" class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th><input type="checkbox" id="selectAllAssets" /></th>
                    <th>Property no</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Qty / Unit</th>
                    <th>Status</th>
                    <th>Value</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $stmt = $conn->prepare("
                      SELECT a.*, c.category_name 
                      FROM assets a 
                      JOIN categories c ON a.category = c.id 
                      WHERE a.type = 'asset' AND a.office_id = ? AND a.quantity > 0
                  ");
                  $stmt->bind_param("i", $office_id);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()):
                    $status_class = $row['status'] === 'available' ? 'success' : ($row['status'] === 'borrowed' ? 'warning' : 'secondary');
                    if ($row['red_tagged']) $status_class = 'danger';
                  ?>
                    <tr>
                      <td><input type="checkbox" class="asset-checkbox" name="selected_assets[]" value="<?= $row['id'] ?>"></td>
                      <td><?= htmlspecialchars($row['property_no']) ?></td>
                      <td><?= htmlspecialchars($row['description']) ?></td>
                      <td><?= htmlspecialchars($row['category_name']) ?></td>
                      <td><?= (int)$row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                      <td>
                        <span class="badge bg-<?= $status_class ?>"><?= $row['red_tagged'] ? 'Red-Tagged' : ucfirst($row['status']) ?></span>
                      </td>
                      <td>&#8369; <?= number_format($row['value'], 2) ?></td>
                      <td class="text-nowrap">
                        <div class="btn-group" role="group">
                          <a href="view_asset_details.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-info rounded-pill">
                            <i class="bi bi-eye"></i>View
                          </a>
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
        $cres = $conn->prepare("SELECT status, quantity FROM assets WHERE type = 'consumable' AND office_id = ? AND quantity > 0");
        $cres->bind_param("i", $office_id);
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
          <?php
          $cards = [
            ['Total', $ctotal, 'box-seam', 'primary'],
            ['Available', $cactive, 'check-circle', 'info'],
            ['Unavailable', $cunavailable, 'slash-circle', 'primary'],
            ['Low Stock', $clow_stock, 'exclamation-triangle', 'info']
          ];
          foreach ($cards as [$title, $value, $icon, $color]): ?>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <h5><?= $title ?></h5>
                    <h3><?= $value ?></h3>
                  </div>
                  <i class="bi bi-<?= $icon ?> text-<?= $color ?> fs-2"></i>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="card shadow-sm">
          <form action="generate_selected_report.php" method="POST">
            <input type="hidden" name="office" value="<?= $office_id ?>">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5 class="mb-0">Consumable List</h5>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="dropdown">
                  <button class="btn btn-outline-secondary btn-sm rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-funnel"></i> Filters
                  </button>
                  <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;">
                    <div class="mb-2">
                      <label class="form-label form-label-sm mb-1">Date range</label>
                      <div class="d-flex gap-2">
                        <input type="date" id="consFromDate" class="form-control form-control-sm" />
                        <input type="date" id="consToDate" class="form-control form-control-sm" />
                      </div>
                    </div>
                    <div class="text-end">
                      <button type="button" class="btn btn-sm btn-primary"><i class="bi bi-check2"></i> Apply</button>
                    </div>
                  </div>
                </div>
                <a href="#" id="btnConsCsv" class="btn btn-outline-secondary rounded-pill btn-sm">
                  <i class="bi bi-filetype-csv"></i> Export CSV
                </a>
                <a href="#" id="btnConsPdf" class="btn btn-outline-danger rounded-pill btn-sm">
                  <i class="bi bi-filetype-pdf"></i> Export PDF
                </a>
                <button type="submit" class="btn btn-outline-primary rounded-pill btn-sm">
                  <i class="bi bi-file-earmark-arrow-down"></i> Generate Selected
                </button>
              </div>
            </div>

            <div class="card-body table-responsive">
              <table id="consumablesTable" class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th><input type="checkbox" id="selectAllConsumables" /></th>
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
                  $stmt = $conn->prepare("\n                      SELECT a.*, c.category_name \n                      FROM assets a \n                      LEFT JOIN categories c ON a.category = c.id \n                      WHERE a.type = 'consumable' AND a.office_id = ? AND a.quantity > 0\n                  ");
                  $stmt->bind_param("i", $office_id);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()):
                    $is_low = $row['quantity'] <= $threshold;
                  ?>
                    <tr data-stock="<?= $is_low ? 'low' : 'normal' ?>">
                      <td><input type="checkbox" class="consumable-checkbox" name="selected_assets[]" value="<?= $row['id'] ?>"></td>
                      
                      <td><?= htmlspecialchars($row['description']) ?></td>
                      <td class="<?= $is_low ? 'text-danger fw-bold' : '' ?>"><?= $row['quantity'] ?></td>
                      <td><?= $row['unit'] ?></td>
                      <td><span class="badge bg-<?= $row['status'] === 'available' ? 'success' : 'secondary' ?>"><?= ucfirst($row['status']) ?></span></td>
                      <td><?= date('M d, Y', strtotime($row['last_updated'])) ?></td>
                      <td>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill updateConsumableBtn" data-id="<?= $row['id'] ?>" data-status="<?= htmlspecialchars($row['status']) ?>" data-bs-toggle="modal" data-bs-target="#updateConsumableModal"><i class="bi bi-pencil-square"></i></button>
                        
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill dispenseBtn"
                          data-id="<?= $row['id'] ?>"
                          data-name="<?= htmlspecialchars($row['description']) ?>"
                          data-stock="<?= $row['quantity'] ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#dispenseConsumableModal">
                          <i class="bi bi-box-arrow-right"></i> Dispense
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
  <?php include 'modals/dispense_consumable_modal.php'; ?>
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
          })
          .catch(error => {
            console.error('Error:', error);
          });
      });
    });

    // ---- Export URL builders ----
    (function() {
      const officeId = <?= (int)$office_id ?>;

      function buildDateParams(fromEl, toEl) {
        const from = fromEl.value;
        const to = toEl.value;
        if (from && to) {
          return `&filter_type=custom&from_date=${encodeURIComponent(from)}&to_date=${encodeURIComponent(to)}`;
        }
        return '&filter_type=all';
      }

      // Assets: CSV/PDF
      const assetsFrom = document.getElementById('assetsFromDate');
      const assetsTo = document.getElementById('assetsToDate');
      const assetsCat = document.getElementById('assetsCategory');
      const assetsStatus = document.getElementById('assetsStatus');

      function buildAssetsQuery() {
        let q = `?office=${officeId}`;
        q += buildDateParams(assetsFrom, assetsTo);
        const cat = assetsCat ? assetsCat.value : 'all';
        const status = assetsStatus ? assetsStatus.value : 'all';
        if (cat && cat !== 'all') q += `&category=${encodeURIComponent(cat)}`;
        if (status && status !== 'all') q += `&status=${encodeURIComponent(status)}`;
        return q;
      }

      const btnAssetsCsv = document.getElementById('btnAssetsCsv');
      const btnAssetsPdf = document.getElementById('btnAssetsPdf');
      if (btnAssetsCsv) {
        btnAssetsCsv.addEventListener('click', function(e) {
          e.preventDefault();
          const url = `../MAIN_ADMIN/export_assets_csv.php${buildAssetsQuery()}`;
          window.open(url, '_blank');
        });
      }
      if (btnAssetsPdf) {
        btnAssetsPdf.addEventListener('click', function(e) {
          e.preventDefault();
          const url = `../MAIN_ADMIN/export_assets_pdf.php${buildAssetsQuery()}`;
          window.open(url, '_blank');
        });
      }

      // Consumables: CSV/PDF (date filter only)
      const consFrom = document.getElementById('consFromDate');
      const consTo = document.getElementById('consToDate');
      const btnConsCsv = document.getElementById('btnConsCsv');
      const btnConsPdf = document.getElementById('btnConsPdf');
      function buildConsQuery() {
        let q = `?office=${officeId}`;
        q += buildDateParams(consFrom, consTo);
        return q;
      }
      if (btnConsCsv) {
        btnConsCsv.addEventListener('click', function(e) {
          e.preventDefault();
          const url = `../MAIN_ADMIN/export_consumables_csv.php${buildConsQuery()}`;
          window.open(url, '_blank');
        });
      }
      if (btnConsPdf) {
        btnConsPdf.addEventListener('click', function(e) {
          e.preventDefault();
          const url = `../MAIN_ADMIN/export_consumables_pdf.php${buildConsQuery()}`;
          window.open(url, '_blank');
        });
      }
    })();
  </script>

</body>

</html>