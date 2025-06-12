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

  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #eaf4fc;
      margin: 0;
    }

    .sidebar {
      height: 100vh;
      background-color: #2196f3;
      color: white;
      position: fixed;
      width: 240px;
      padding: 20px;
    }

    .sidebar h4 {
      font-weight: bold;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      margin: 12px 0;
      font-weight: 500;
      transition: all 0.2s ease-in-out;
    }

    .sidebar a:hover {
      color: #bbdefb;
      padding-left: 10px;
    }

    .main {
      margin-left: 260px;
      padding: 30px;
      background-color: #fdfefe;
      min-height: 100vh;
    }

    .topbar {
      background-color: #e3f2fd;
      padding: 15px 30px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
      border-radius: 0.5rem;
    }

    .status-badge {
      font-size: 0.75rem;
      padding: 0.3em 0.5em;
    }

    .card {
      border-radius: 0.75rem;
      border: none;
      background-color: #ffffff;
    }

    .card-header {
      background-color: #f0f9ff;
      border-bottom: 1px solid #dee2e6;
      border-top-left-radius: 0.75rem;
      border-top-right-radius: 0.75rem;
    }

    .table th,
    .table td {
      vertical-align: middle;
    }

    .table-hover tbody tr:hover {
      background-color: #f1f9ff;
    }

    .nav-link.active {
      background-color: #f0f0f0;
      font-weight: bold;
      border-left: 4px solid #0d6efd;
    }

    .sidebar .nav {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-top: 30px;
    }

    .sidebar a {
      width: 100%;
      text-align: left;
      padding: 10px 15px;
      border-radius: 10px;
      margin: 5px 0;
      transition: all 0.3s ease;
    }

    .sidebar a.active {
      background-color: white;
      color: #2196f3 !important;
      font-weight: bold;
      border-left: 4px solid #0d6efd;
    }

    .sidebar a:not(.active) {
      color: white;
    }

    .sidebar a:hover:not(.active) {
      background-color: rgba(255, 255, 255, 0.2);
      padding-left: 20px;
    }

    .sidebar.collapsed {
      margin-left: -240px;
      transition: margin 0.3s ease;
    }

    .main-expanded {
      margin-left: 0 !important;
      transition: margin 0.3s ease;
    }

    /* Sidebar hidden animation */
    .sidebar {
      transition: margin-left 0.3s ease;
    }

    .sidebar-hidden {
      margin-left: -240px;
      /* Same as sidebar width */
    }

    /* Main area expands when sidebar is hidden */
    .main {
      transition: margin-left 0.3s ease;
    }

    .main-expanded {
      margin-left: 0 !important;
    }
  </style>
</head>

<body>

  <div class="sidebar d-flex flex-column justify-content-between">
    <div>
      <h5 class="text-center d-flex align-items-center justify-content-center">
        <img src="../img/logo.jpg" alt="Logo" style="width: 30px; height: 30px; margin-right: 10px;" />
        Pilar Inventory
      </h5>
      <hr />
      <?php
      $page = basename($_SERVER['PHP_SELF'], ".php"); // detects current PHP filename
      ?>
      <nav class="nav flex-column">
        <a href="../MAIN_USER/user_dashboard.php" class="nav-link <?= ($page == 'user_dashboard') ? 'active' : '' ?>">
          <i class="bi bi-box-seam"></i> Inventory
        </a>
        <a href="reports.php" class="nav-link <?= ($page == 'reports') ? 'active' : '' ?>">
          <i class="bi bi-bar-chart-line"></i> Reports
        </a>
        <a href="settings.php" class="nav-link <?= ($page == 'settings') ? 'active' : '' ?>">
          <i class="bi bi-gear"></i> Settings
        </a>
        <a href="../logout.php" class="nav-link">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </nav>
    </div>


  </div>

  <div class="main">

    <div class="topbar d-flex justify-content-between align-items-center p-2">
      <div class="text-end p-2">
        <button id="toggleSidebar" class="btn btn-outline-primary">
          <i class="bi bi-chevron-left" id="toggleIcon"></i>
        </button>
      </div>

      <h3 class=" text-start m-0">Inventory Dashboard</h3>

      <!-- Right-side Icons + DateTime -->
      <div class="d-flex align-items-center gap-3">

        <!-- Date and Time -->
        <div id="datetime" class="text-end text-dark small fw-semibold"></div>

        <!-- Scan QR Icon -->
        <a href="scan_qr.php" class="text-dark text-decoration-none" title="Scan QR">
          <i class="bi bi-qr-code-scan" style="font-size: 1.8rem;"></i>
        </a>

        <!-- Profile Menu -->
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-dark text-decoration-none" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle" style="font-size: 1.8rem;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="profileDropdown" style="min-width: 200px;">
            <li class="dropdown-header fw-bold text-dark"><?php echo htmlspecialchars($fullname); ?></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item d-flex align-items-center" href="view_profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
            <li><a class="dropdown-item d-flex align-items-center" href="manage_password.php"><i class="bi bi-key me-2"></i> Manage Password</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item d-flex align-items-center text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
          </ul>
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
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Total</h5>
                <h3><?= $total ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Available</h5>
                <h3><?= $active ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Borrowed</h5>
                <h3><?= $borrowed ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Red-Tagged</h5>
                <h3><?= $red_tagged ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Asset List</h5>
            <a href="generate_report.php?type=asset" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
            </a>
          </div>
          <div class="card-body table-responsive">
            <table id="assetTable" class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
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
                </tr>
              </thead>
              <tbody>
                <?php
                $stmt = $conn->query("SELECT a.*, c.category_name FROM assets a JOIN categories c ON a.category = c.id WHERE a.type = 'asset'");
                while ($row = $stmt->fetch_assoc()):
                ?>
                  <tr>
                    <td><img src="../img/<?= $row['qr_code'] ?>" width="50"></td>
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
                    <td><?= $row['acquisition_date'] ?></td>
                    <td><?= $row['last_updated'] ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
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

        <!-- Summary Cards -->
        <div class="row mb-4">
          <div class="col-md-4">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Total</h5>
                <h3><?= $ctotal ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Available</h5>
                <h3><?= $cactive ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm border-warning">
              <div class="card-body">
                <h5>Low Stock</h5>
                <h3><?= $clow_stock ?></h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Consumables Table -->
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Consumable List</h5>
            <div>
              <select id="stockFilter" class="form-select form-select-sm d-inline-block w-auto me-2">
                <option value="">All Items</option>
                <option value="low">Low Stock</option>
              </select>
              <a href="generate_report.php?type=consumable" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
              </a>
            </div>
          </div>

          <div class="card-body table-responsive">
            <table id="consumablesTable" class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
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
                </tr>
              </thead>
              <tbody>
                <?php
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
                    <td><img src="<?= $row['qr_code'] ?>" width="50"></td>
                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td class="<?= $row['quantity'] <= 5 ? 'text-danger fw-bold' : '' ?>">
                      <?= $row['quantity'] ?>
                    </td>
                    <td><?= $row['unit'] ?></td>
                    <td>
                      <span class="badge bg-<?= $row['status'] === 'available' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($row['status']) ?>
                      </span>
                    </td>
                    <td>&#8369; <?= number_format($row['value'], 2) ?></td>
                    <td><?= $row['acquisition_date'] ?></td>
                    <td><?= $row['last_updated'] ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
    function updateDateTime() {
      const now = new Date();
      const formatted = now.toLocaleString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
      document.getElementById('datetime').textContent = formatted;
    }
    setInterval(updateDateTime, 1000);
    updateDateTime(); // Initial call

    $(document).ready(function() {
      $('#assetTable').DataTable({
        responsive: true,
        pageLength: 10,
        language: {
          search: "Search assets:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ assets"
        }
      });
    });

    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.sidebar');
    const main = document.querySelector('.main');
    const icon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', () => {
      // Toggle sidebar visibility
      sidebar.classList.toggle('sidebar-hidden');
      main.classList.toggle('main-expanded');

      // Toggle icon direction
      if (sidebar.classList.contains('sidebar-hidden')) {
        icon.classList.remove('bi-chevron-left');
        icon.classList.add('bi-chevron-right');
      } else {
        icon.classList.remove('bi-chevron-right');
        icon.classList.add('bi-chevron-left');
      }
    });

    document.addEventListener("DOMContentLoaded", function() {
      const table = $('#consumablesTable').DataTable({
        responsive: true
      });

      $('#stockFilter').on('change', function() {
        const filter = $(this).val();
        if (filter === "low") {
          table.rows().every(function() {
            const row = this.node();
            const stock = row.getAttribute('data-stock');
            $(row).toggle(stock === 'low');
          });
        } else {
          table.rows().every(function() {
            $(this.node()).show();
          });
        }
      });
    });
  </script>

</body>

</html>