<?php
// Get current file name without extension
$current_page = basename($_SERVER['PHP_SELF'], ".php");

// Format it to a human-readable title
$page_title = ucwords(str_replace("_", " ", $current_page));

// Optional: Custom override for specific page filenames
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
?>

<div class="topbar d-flex flex-wrap justify-content-between align-items-center p-2 gap-2">
  <!-- Sidebar Toggle Button -->
  <div class="order-1">
    <button id="toggleSidebar" class="btn btn-outline-primary">
      <i class="bi bi-chevron-left" id="toggleIcon"></i>
    </button>
  </div>

  <!-- Dashboard Title -->
  <h5 class="order-3 order-sm-2 flex-grow-1 text-center text-sm-start m-0">
    <?php echo htmlspecialchars($page_title); ?>
  </h5>

  <!-- Right-side Icons + DateTime -->
  <div class="order-2 order-sm-3 d-flex align-items-center gap-3 ms-auto flex-wrap justify-content-end">

    <!-- Date and Time -->
    <div id="datetime" class="text-end text-dark small fw-semibold"></div>

    <!-- Scan QR Icon -->
    <a href="scan_qr.php" class="text-dark text-decoration-none" title="Scan QR">
      <i class="bi bi-qr-code-scan text-primary" style="font-size: 1.8rem;"></i>
    </a>

    <!-- Night/Day Mode Toggle Icon -->
    <button id="themeToggle" class="btn btn-sm text-dark" title="Toggle Night Mode">
      <i id="themeIcon" class="bi bi-moon-fill text-info" style="font-size: 1.5rem;"></i>
    </button>

    <!-- Notification Bell Icon with Dropdown -->
    <div class="dropdown">
      <a href="#" class="text-dark text-decoration-none position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
        <i class="bi bi-bell text-primary" style="font-size: 1.8rem;"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 300px;">
        <li class="dropdown-header fw-bold text-center">Notifications</li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item small text-muted text-center" href="#">No new notifications</a></li>
      </ul>
    </div>

    <!-- Profile Menu -->
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-dark text-decoration-none" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle" id="profileIcon" style="font-size: 1.8rem;"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="profileDropdown" style="min-width: 200px;">
        <li class="dropdown-header fw-bold text-dark"><?php echo htmlspecialchars($fullname); ?></li>
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

<!-- Manage Password Modal -->
<div class="modal fade" id="managePasswordModal" tabindex="-1" aria-labelledby="managePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="update_password.php" method="POST" class="modal-content needs-validation" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="managePasswordModalLabel">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Password requirements note -->
        <p class="small text-muted mb-3">
          Password must be at least 6 characters long and include uppercase, lowercase, number, and symbol.
        </p>

        <!-- Current Password -->
        <div class="mb-3">
          <label for="currentPassword" class="form-label">Current Password</label>
          <input type="password" class="form-control" id="currentPassword" name="current_password" required>
        </div>

        <!-- New Password -->
        <div class="mb-3">
          <label for="newPassword" class="form-label">New Password</label>
          <input type="password" class="form-control" id="newPassword" name="new_password"
            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{6,}" required>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
        </div>

        <!-- Show Password Checkbox -->
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="showPasswordToggle">
          <label class="form-check-label" for="showPasswordToggle">
            Show Password
          </label>
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
  // Show/hide all password fields with one checkbox
  document.getElementById('showPasswordToggle').addEventListener('change', function () {
    const type = this.checked ? 'text' : 'password';
    document.getElementById('currentPassword').type = type;
    document.getElementById('newPassword').type = type;
    document.getElementById('confirmPassword').type = type;
  });

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
</script>
