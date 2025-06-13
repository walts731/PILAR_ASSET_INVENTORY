<div class="topbar d-flex flex-wrap justify-content-between align-items-center p-2 gap-2">
  <!-- Sidebar Toggle Button -->
  <div class="order-1">
    <button id="toggleSidebar" class="btn btn-outline-primary">
      <i class="bi bi-chevron-left" id="toggleIcon"></i>
    </button>
  </div>

  <!-- Dashboard Title -->
  <h5 class="order-3 order-sm-2 flex-grow-1 text-center text-sm-start m-0">
    Inventory Dashboard
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
        <li><hr class="dropdown-divider"></li>
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
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item d-flex align-items-center" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
        <li><a class="dropdown-item d-flex align-items-center" href="manage_password.php"><i class="bi bi-key me-2"></i> Manage Password</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item d-flex align-items-center text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
      </ul>
    </div>
  </div>
</div>
