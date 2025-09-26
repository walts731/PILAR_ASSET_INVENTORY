<?php
require_once '../connect.php';

// Get current file name without extension
$current_page = basename($_SERVER['PHP_SELF'], ".php");

// Format to human-readable title
$page_title = ucwords(str_replace("_", " ", $current_page));

// Custom page titles
$custom_titles = [
  "index" => "Welcome",
  "user_dashboard" => "Inventory Dashboard",
  "profile" => "Profile",
  "manage_password" => "Change Password",
  "scan_qr" => "Scan QR",
  "settings" => "Settings",
  "reports" => "Reports",
  "inventory" => "Inventory Management",
  "asset_archive" => "Archive",
  "user" => "User Management",
  "forms" => "Forms",
  "borrow" => "Borrowing Management",
  "about" => "About",
  "saved_mr" => "Saved Property Tags",
  "create_mr" => "Create Property Tag",
];

if (array_key_exists($current_page, $custom_titles)) {
  $page_title = $custom_titles[$current_page];
}



// Low stock alert
$low_stock_threshold = 5;
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
  }
  .topbar .dropdown-item { padding: 0.5rem 0.75rem; }
  .topbar .dropdown-item:hover { background: rgba(11, 94, 215, 0.08); }
</style>
<div class="topbar d-flex flex-wrap justify-content-between align-items-center p-2 gap-2">
  <!-- Sidebar Toggle -->
  <div class="order-1">
    <button id="toggleSidebar" class="btn btn-outline-primary">
      <i class="bi bi-chevron-left" id="toggleIcon"></i>
    </button>
  </div>

  <!-- Page Title & Breadcrumb -->
  <div class="order-3 order-sm-2 flex-grow-1">
    <?php
    // Page-specific breadcrumb navigation
    if ($current_page === 'edit_template') {
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="templates.php" class="text-decoration-none text-primary">Templates</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Edit Template</span>
            </h5>';
    } elseif ($current_page === 'borrow_requests') {
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="borrow.php" class="text-decoration-none text-primary">Borrowing Management</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Borrow Request</span>
            </h5>';
    } elseif ($current_page === 'borrowed_assets') {
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="borrow.php" class="text-decoration-none text-primary">Borrowing Management</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Borrowed Assets</span>
            </h5>';
    } elseif ($current_page === 'incoming_borrow_requests') {
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="borrow.php" class="text-decoration-none text-primary">Borrowing Management</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Incoming Borrow Requests</span>
            </h5>';
    } elseif ($current_page === 'returned_assets') {
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="borrow.php" class="text-decoration-none text-primary">Borrowing Management</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Returned Assets</span>
            </h5>';
    } elseif ($current_page === 'saved_ics') {
      $formId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Saved ICS</span>
            </h5>';
    } elseif ($current_page === 'saved_par') {
      $formId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Saved PAR</span>
            </h5>';
    } elseif ($current_page === 'view_ics') {
      $icsId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      $formId = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <a href="saved_ics.php?id=' . $formId . '" class="text-decoration-none text-primary">Saved ICS</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">View ICS</span>
            </h5>';
    } elseif ($current_page === 'view_par') {
      $parId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      $formId = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <a href="saved_par.php?id=' . $formId . '" class="text-decoration-none text-primary">Saved PAR</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">View PAR</span>
            </h5>';
    } elseif ($current_page === 'iirup_form') {
      $formId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <a href="saved_iirup.php?id=' . $formId . '" class="text-decoration-none text-primary">Saved IIRUP</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">IIRUP Form</span>
            </h5>';
    } elseif ($current_page === 'saved_iirup') {
      $formId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Saved IIRUP</span>
            </h5>';
    } elseif ($current_page === 'view_iirup') {
      $iirupId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      $formId = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <a href="saved_iirup.php?id=' . $formId . '" class="text-decoration-none text-primary">Saved IIRUP</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">View IIRUP</span>
            </h5>';
    } elseif ($current_page === 'create_red_tag') {
      $iirup_id = isset($_GET['iirup_id']) ? intval($_GET['iirup_id']) : 0;
      $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 7; // Default to 7 if not provided
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $form_id . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <a href="saved_iirup.php?id=' . $form_id . '" class="text-decoration-none text-primary">Saved IIRUP</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <a href="view_iirup.php?id=' . $iirup_id . '&form_id=' . $form_id . '" class="text-decoration-none text-primary">View IIRUP</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Create Red Tag</span>
            </h5>';
    } elseif ($current_page === 'create_mr') {
      // Show PAR breadcrumb if par_id present; otherwise default to ICS breadcrumb
      $formId = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
      $parId = isset($_GET['par_id']) ? intval($_GET['par_id']) : 0;
      $assetId = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : 0;
      if ($parId > 0) {
        $cmrLink = 'create_mr.php?asset_id=' . $assetId . '&par_id=' . $parId . '&form_id=' . $formId;
        echo '<h5 class="m-0 text-center text-sm-start">
                <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
                <span class="mx-1 text-muted"> &gt; </span>
                <a href="saved_par.php?id=' . $formId . '" class="text-decoration-none text-primary">Saved PAR</a>
                <span class="mx-1 text-muted"> &gt; </span>
                <a href="view_par.php?id=' . $parId . '&form_id=' . $formId . '" class="text-decoration-none text-primary">View PAR</a>
                <span class="mx-1 text-muted"> &gt; </span>
                <span class="text-dark">Create Property Tag</span>
              </h5>';
      } else {
        $icsId = isset($_GET['ics_id']) ? intval($_GET['ics_id']) : 0;
        echo '<h5 class="m-0 text-center text-sm-start">
                <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
                <span class="mx-1 text-muted"> &gt; </span>
                <a href="saved_ics.php?id=' . $formId . '" class="text-decoration-none text-primary">Saved ICS</a>
                <span class="mx-1 text-muted"> &gt; </span>
                <a href="view_ics.php?id=' . $icsId . '&form_id=' . $formId . '" class="text-decoration-none text-primary">View ICS</a>
                <span class="mx-1 text-muted"> &gt; </span>
                <span class="text-dark">Create Property Tag</span>
              </h5>';
      }
    } elseif ($current_page === 'saved_ris') {
      $formId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">Saved RIS</span>
            </h5>';
    } elseif ($current_page === 'view_ris') {
      $risId = isset($_GET['id']) ? intval($_GET['id']) : 0;
      $formId = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
      echo '<h5 class="m-0 text-center text-sm-start">
              <a href="forms.php?id=' . $formId . '" class="text-decoration-none text-primary">Forms</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <a href="saved_ris.php?id=' . $formId . '" class="text-decoration-none text-primary">Saved RIS</a>
              <span class="mx-1 text-muted"> &gt; </span>
              <span class="text-dark">View RIS</span>
            </h5>';
    } else {
      echo '<h5 class="m-0 text-center text-sm-start">' . htmlspecialchars($page_title) . '</h5>';
    }
    ?>
  </div>

  <!-- Right Side Controls -->
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
        <li>
          <hr class="dropdown-divider">
        </li>
        <?php if ($low_stock_count > 0): ?>
          <?php foreach ($low_stock_items as $item): ?>
            <li>
              <a class="dropdown-item small text-danger d-flex justify-content-between align-items-center"
                href="admin_dashboard.php?id=<?php echo $item['id']; ?>">
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
  document.getElementById('showPasswordToggle')?.addEventListener('change', function() {
    const type = this.checked ? 'text' : 'password';
    document.getElementById('currentPassword').type = type;
    document.getElementById('newPassword').type = type;
    document.getElementById('confirmPassword').type = type;
  });

  // Bootstrap validation
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

  // Theme Toggle Functionality
  document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    if (themeToggle && themeIcon) {
      // Handle theme toggle click
      themeToggle.addEventListener('click', function() {
        fetch('../includes/dark_mode_helper.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'toggle_dark_mode=1'
        })
        .then(response => response.json())
        .then(data => {
          // Toggle dark mode class on body and html
          document.body.classList.toggle('dark-mode', data.dark_mode);
          document.documentElement.classList.toggle('dark-mode', data.dark_mode);
          
          // Update the icon and title
          updateThemeIcon(themeToggle, themeIcon, data.dark_mode);
        })
        .catch(error => {
          console.error('Error toggling dark mode:', error);
        });
      });

      // Initialize theme icon based on current mode
      const isDarkMode = document.body.classList.contains('dark-mode');
      updateThemeIcon(themeToggle, themeIcon, isDarkMode);
    }

    // Low stock notification
    const lowStockCard = document.getElementById('lowStockCard');
    const consumablesTab = document.querySelector('#consumables-tab');
    
    if (lowStockCard && consumablesTab) {
      const tab = new bootstrap.Tab(consumablesTab);
      lowStockCard.addEventListener('click', function() {
        tab.show();
        setTimeout(() => {
          const lowStockRow = document.querySelector('#consumablesTable tbody tr[data-stock="low"]');
          if (lowStockRow) {
            lowStockRow.scrollIntoView({
              behavior: 'smooth',
              block: 'center'
            });
            lowStockRow.classList.add('highlight-row');
            setTimeout(() => {
              lowStockRow.classList.remove('highlight-row');
            }, 2000);
          }
        }, 300);
      });
    }

    // Helper function to update theme icon and title
    function updateThemeIcon(toggle, icon, isDark) {
      if (isDark) {
        icon.classList.remove('bi-moon-fill');
        icon.classList.add('bi-sun-fill');
        toggle.title = 'Switch to Light Mode';
      } else {
        icon.classList.remove('bi-sun-fill');
        icon.classList.add('bi-moon-fill');
        toggle.title = 'Switch to Dark Mode';
      }
    }
  });
</script>