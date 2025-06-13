<?php
require_once '../connect.php';

// Get full name from session or fallback
$fullname = $_SESSION['fullname'] ?? 'User';

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

    <!-- Theme Toggle -->
    <button id="themeToggle" class="btn btn-sm text-dark" title="Toggle Night Mode">
      <i id="themeIcon" class="bi bi-moon-fill text-info" style="font-size: 1.5rem;"></i>
    </button>

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
        <i class="bi bi-person-circle" id="profileIcon" style="font-size: 1.8rem;"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="profileDropdown" style="min-width: 200px;">
        <li class="dropdown-header fw-bold text-dark"><?php echo htmlspecialchars($fullname); ?></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item d-flex align-items-center" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
        <li>
          <button class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#managePasswordModal">
            <i class="bi bi-key me-2"></i> Manage Password
          </button>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item d-flex align-items-center text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
      </ul>
    </div>
  </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="managePasswordModal" tabindex="-1" aria-labelledby="managePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="update_password.php" method="POST" class="modal-content needs-validation" novalidate>
      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">Password must be at least 6 characters and include uppercase, lowercase, number, and symbol.</p>
        <div class="mb-3">
          <label for="currentPassword" class="form-label">Current Password</label>
          <input type="password" class="form-control" id="currentPassword" name="current_password" required>
        </div>
        <div class="mb-3">
          <label for="newPassword" class="form-label">New Password</label>
          <input type="password" class="form-control" id="newPassword" name="new_password"
                 pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{6,}" required>
        </div>
        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="showPasswordToggle">
          <label class="form-check-label" for="showPasswordToggle">Show Password</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update Password</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

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
