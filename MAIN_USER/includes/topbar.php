<?php
require_once '../connect.php';



// Get current file name without extension
$current_page = basename($_SERVER['PHP_SELF'], ".php");

// Format it to a human-readable title
$page_title = ucwords(str_replace("_", " ", $current_page));

// Custom titles for specific pages
$custom_titles = [
  "index" => "Welcome",
  "user_dashboard" => "Inventory Dashboard",
  "profile" => "Profile",
  "manage_password" => "Change Password",
  "scan_qr" => "Scan QR",
  "settings" => "Settings",
  "reports" => "Reports",
];

if (array_key_exists($current_page, $custom_titles)) {
  $page_title = $custom_titles[$current_page];
}

// Low stock threshold
$low_stock_threshold = 5;

// Fetch low stock consumables from assets table (with asset_id)
$low_stock_stmt = $conn->prepare("SELECT id, asset_name AS product_name, quantity AS stock FROM assets WHERE type = 'consumable' AND quantity <= ? ORDER BY quantity ASC");
$low_stock_stmt->bind_param("i", $low_stock_threshold);
$low_stock_stmt->execute();
$low_stock_result = $low_stock_stmt->get_result();

$low_stock_items = [];
while ($row = $low_stock_result->fetch_assoc()) {
  $low_stock_items[] = $row;
}

$low_stock_count = count($low_stock_items);
?>
<style>
  /* Professional blue-themed top bar */
  .topbar {
    background: linear-gradient(180deg, #0b5ed7 0%, #0a58ca 50%, #0948a6 100%);
    color: #fff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.18);
    position: sticky;
    top: 0;
    z-index: 1030;
  }

  .shadow-soft { box-shadow: 0 2px 14px rgba(0,0,0,0.08); }

  /* Typography & links */
  .topbar h5 { color: #ffffff; text-shadow: 0 1px 2px rgba(0,0,0,0.25); }
  .topbar a { color: #eaf2ff; text-decoration: none; }
  .topbar a:hover { opacity: 0.92; }

  /* Ensure readable text when components use Bootstrap utilities inside the dark topbar */
  .topbar .text-dark { color: rgba(255,255,255,0.92) !important; }
  .topbar .text-muted { color: rgba(255,255,255,0.8) !important; }
  .topbar .text-primary { color: #e2ecff !important; }
  #datetime { color: rgba(255,255,255,0.85) !important; }

  /* Buttons & icons */
  .topbar .btn { transition: background-color 0.2s ease, color 0.2s ease, opacity 0.2s ease; }
  .topbar #toggleSidebar.btn { border-color: rgba(255,255,255,0.65); color: #fff; }
  .topbar #toggleSidebar.btn:hover { background: rgba(255,255,255,0.12); }
  .topbar i { color: #ffffff; }

  /* Dropdown polish */
  .topbar .dropdown-menu {
    border-radius: 12px;
    box-shadow: 0 10px 24px rgba(0,0,0,0.15);
    border: 1px solid rgba(0,0,0,0.06);
    background: #ffffff; /* ensure white background */
    color: #212529;      /* default dark text */
  }
  .topbar .dropdown-menu a { color: #212529 !important; }
  .topbar .dropdown-item { padding: 0.5rem 0.75rem; color: #212529; }
  .topbar .dropdown-item .bi { color: inherit; }
  .topbar .dropdown-header { color: #212529; }
  .topbar .dropdown-item:hover { background: rgba(11, 94, 215, 0.08); }
</style>
<div class="topbar d-flex flex-wrap justify-content-between align-items-center p-2 gap-2">
  <!-- Sidebar Toggle -->
  <div class="order-1">
    <button id="toggleSidebar" class="btn btn-outline-primary">
      <i class="bi bi-chevron-left" id="toggleIcon"></i>
    </button>
  </div>

  <!-- Page Title -->
  <h5 class="order-3 order-sm-2 flex-grow-1 text-center text-sm-start m-0">
    <?php echo htmlspecialchars($page_title); ?>
  </h5>

  <!-- Right Side Icons -->
  <div class="order-2 order-sm-3 d-flex align-items-center gap-3 ms-auto flex-wrap justify-content-end">

    <!-- Date & Time -->
    <div id="datetime" class="text-end text-dark small fw-semibold"></div>

    <!-- Scan QR -->
    <a href="scan_qr.php" class="text-dark text-decoration-none" title="Scan QR">
      <i class="bi bi-qr-code-scan text-primary" style="font-size: 1.8rem;"></i>
    </a>

    

    <!-- Notifications -->
    <div class="dropdown">
      <a href="#" class="text-dark text-decoration-none position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
        <i class="bi bi-bell text-primary" style="font-size: 1.8rem;"></i>
        <?php if ($low_stock_count > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?php echo $low_stock_count; ?>
          </span>
        <?php endif; ?>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 300px;">
        <li class="dropdown-header fw-bold text-center">Notifications</li>
        <li><hr class="dropdown-divider"></li>
        <?php if ($low_stock_count > 0): ?>
          <?php foreach ($low_stock_items as $item): ?>
            <li>
              <a class="dropdown-item small text-danger d-flex justify-content-between align-items-center"
                 href="user_dashboard.php?id=<?php echo $item['id']; ?>">
                <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                <span class="badge bg-danger"><?php echo $item['stock']; ?> left</span>
              </a>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li><a class="dropdown-item small text-muted text-center" href="#">No new notifications</a></li>
        <?php endif; ?>
      </ul>
    </div>

    <!-- Profile Dropdown -->
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-dark text-decoration-none" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle" style="font-size: 1.8rem;"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="profileDropdown" style="min-width: 200px;">
        <li class="dropdown-header fw-bold text-dark"><?php echo htmlspecialchars($fullname ?? 'User'); ?></li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item d-flex align-items-center" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
        <li>
          <button class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#managePasswordModal">
            <i class="bi bi-key me-2"></i> Manage Password
          </button>
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item d-flex align-items-center text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
      </ul>
    </div>



    
  </div>
</div>

<!-- Change Password Modal -->
<?php include 'modals/change_password_modal.php'; ?>

<script>
  // Show/hide password toggle
  document.getElementById('showPasswordToggle').addEventListener('change', function () {
    const type = this.checked ? 'text' : 'password';
    document.getElementById('currentPassword').type = type;
    document.getElementById('newPassword').type = type;
    document.getElementById('confirmPassword').type = type;
  });

  // Bootstrap form validation
  (() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();

  document.addEventListener("DOMContentLoaded", function () {
    const lowStockCard = document.getElementById("lowStockCard");
    const consumablesTab = new bootstrap.Tab(document.querySelector('#consumables-tab'));

    lowStockCard.addEventListener("click", function () {
      // Switch to the Consumables tab
      consumablesTab.show();

      // Wait a bit for the tab to show, then scroll
      setTimeout(() => {
        const lowStockRow = document.querySelector('#consumablesTable tbody tr[data-stock="low"]');
        if (lowStockRow) {
          lowStockRow.scrollIntoView({ behavior: 'smooth', block: 'center' });

          // Flash animation
          lowStockRow.classList.add("highlight-row");
          setTimeout(() => {
            lowStockRow.classList.remove("highlight-row");
          }, 2000);
        }
      }, 300);
    });
  });
</script>
