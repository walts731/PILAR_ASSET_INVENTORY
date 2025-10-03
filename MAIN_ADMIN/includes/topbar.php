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
  /* Global Search dropdown: force readable colors inside topbar */
  #globalSearchResults .list-group-item {
    color: #212529 !important;
    /* main text */
    background-color: #ffffff !important;
    border: 0;
    /* optional: cleaner look */
  }

  #globalSearchResults .list-group-item:hover,
  #globalSearchResults .list-group-item:focus {
    background-color: #f8f9fa !important;
    color: #212529 !important;
  }

  #globalSearchResults .small {
    color: #6c757d !important;
    /* subtitle line */
  }

  /* Highest specificity to beat .topbar a color */
  .topbar #globalSearchResults a {
    color: #212529 !important;
  }

  /* Professional blue-themed top bar */
  .topbar {
    background: linear-gradient(180deg, #0b5ed7 0%, #0a58ca 50%, #0948a6 100%);
    color: #fff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.18);
    position: sticky;
    top: 0;
    z-index: 1030;
  }

  .shadow-soft {
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.08);
  }

  /* Typography & links */
  .topbar h5 {
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
  }

  .topbar a {
    color: #eaf2ff;
    text-decoration: none;
  }

  .topbar a:hover {
    opacity: 0.92;
  }

  /* Ensure readable text when components use Bootstrap utilities inside the dark topbar */
  .topbar .text-dark {
    color: rgba(255, 255, 255, 0.92) !important;
  }

  .topbar .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
  }

  .topbar .text-primary {
    color: #e2ecff !important;
  }

  #datetime {
    color: rgba(255, 255, 255, 0.85) !important;
  }

  /* Buttons & icons */
  .topbar .btn {
    transition: background-color 0.2s ease, color 0.2s ease, opacity 0.2s ease;
  }

  .topbar #toggleSidebar.btn {
    border-color: rgba(255, 255, 255, 0.65);
    color: #fff;
  }

  .topbar #toggleSidebar.btn:hover {
    background: rgba(255, 255, 255, 0.12);
  }

  .topbar i {
    color: #ffffff;
  }

  /* Dropdown polish */
  .topbar .dropdown-menu {
    border-radius: 12px;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.06);
  }

  .topbar .dropdown-item {
    padding: 0.5rem 0.75rem;
  }

  .topbar .dropdown-item:hover {
    background: rgba(11, 94, 215, 0.08);
  }
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
    }  elseif ($current_page === 'saved_ris') {
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

    <!-- Global Search -->
    <div class="position-relative" style="min-width: 260px;">
      <input id="globalSearchInput" type="search" class="form-control form-control-sm shadow-soft"
        placeholder="Search assets (description, tag, property, serial)" autocomplete="off">
      <div id="globalSearchResults"
        class="position-absolute w-100 bg-white border rounded shadow-sm list-group"
        style="top: 100%; left: 0; z-index: 2050; display: none; max-height: 50vh; overflow-y: auto; color: #212529;"></div>

    </div>
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
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none;">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationDropdown" style="min-width: 350px; max-height: 70vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
          <h6 class="mb-0 fw-bold">Notifications</h6>
          <div>
            <button id="markAllAsRead" class="btn btn-sm btn-link text-decoration-none" title="Mark all as read">
              <i class="bi bi-check2-all"></i>
            </button>
            <a href="notifications.php" class="btn btn-sm btn-link text-decoration-none" title="View all notifications">
              <i class="bi bi-three-dots"></i>
            </a>
          </div>
        </div>
        <div class="notification-list">
          <!-- Notifications will be loaded here via JavaScript -->
          <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 mb-0 text-muted small">Loading notifications...</p>
          </div>
        </div>
        <div class="dropdown-divider m-0"></div>
        <div class="text-center py-2">
          <a href="notifications.php" class="text-decoration-none small">View all notifications</a>
        </div>
      </div>
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
// ================= Password Toggle =================
document.getElementById('showPasswordToggle')?.addEventListener('change', function() {
  const type = this.checked ? 'text' : 'password';
  ['currentPassword','newPassword','confirmPassword'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.type = type;
  });
});

// ================= Bootstrap Form Validation =================
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

// ================= Theme Toggle + Notifications =================
document.addEventListener('DOMContentLoaded', function() {
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');

  if (themeToggle && themeIcon) {
    themeToggle.addEventListener('click', function() {
      fetch('../includes/dark_mode_helper.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'toggle_dark_mode=1'
      })
      .then(res => res.json())
      .then(data => {
        document.body.classList.toggle('dark-mode', data.dark_mode);
        document.documentElement.classList.toggle('dark-mode', data.dark_mode);
        updateThemeIcon(themeToggle, themeIcon, data.dark_mode);
      })
      .catch(err => console.error('Error toggling dark mode:', err));
    });

    const isDarkMode = document.body.classList.contains('dark-mode');
    updateThemeIcon(themeToggle, themeIcon, isDarkMode);
  }

  // Low stock card shortcut
  const lowStockCard = document.getElementById('lowStockCard');
  const consumablesTab = document.querySelector('#consumables-tab');
  if (lowStockCard && consumablesTab) {
    const tab = new bootstrap.Tab(consumablesTab);
    lowStockCard.addEventListener('click', function() {
      tab.show();
      setTimeout(() => {
        const lowStockRow = document.querySelector('#consumablesTable tbody tr[data-stock="low"]');
        if (lowStockRow) {
          lowStockRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
          lowStockRow.classList.add('highlight-row');
          setTimeout(() => lowStockRow.classList.remove('highlight-row'), 2000);
        }
      }, 300);
    });
  }

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

// ================= Global Search =================
(function() {
  const input = document.getElementById('globalSearchInput');
  const box = document.getElementById('globalSearchResults');
  if (!input || !box) return;

  let timer = null;
  let lastQ = '';

  const esc = (v) => (v == null ? '' : String(v)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;'));

  function render(list) {
    if (!list || list.length === 0) {
      box.innerHTML = '<div class="p-2 text-muted small">No results</div>';
    } else {
      box.innerHTML = list.map(it => {
        const line2 = [
          it.property_no ? 'Property No: ' + esc(it.property_no) : 'Property No: —',
          it.ics_no ? 'ICS: ' + esc(it.ics_no) : null,
          it.par_no ? 'PAR: ' + esc(it.par_no) : null
        ].filter(Boolean).join(' • ');
        return `<a href="#" class="list-group-item list-group-item-action global-search-item" data-id="${it.id}">
                  <div class="fw-semibold">${esc(it.description)}</div>
                  <div class="small text-muted">${esc(line2)}</div>
                </a>`;
      }).join('');
    }
    box.style.display = 'block';
  }

  function search(q) {
    if (!q || q.length < 2) {
      box.style.display = 'none';
      return;
    }
    fetch('search_assets.php?q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => render(Array.isArray(data.results) ? data.results : []))
      .catch(() => render([]));
  }

  input.addEventListener('input', () => {
    const q = input.value.trim();
    if (q === lastQ) return;
    lastQ = q;
    clearTimeout(timer);
    timer = setTimeout(() => search(q), 250);
  });

  input.addEventListener('focus', () => {
    if (box.innerHTML) box.style.display = 'block';
  });
  input.addEventListener('blur', () => setTimeout(() => {
    box.style.display = 'none';
  }, 250));

  box.addEventListener('mousedown', (e) => e.preventDefault());

  box.addEventListener('click', (e) => {
    const a = e.target.closest('.global-search-item');
    if (!a) return;
    e.preventDefault();

    const id = a.getAttribute('data-id');
    const content = document.getElementById('globalAssetContent');
    if (content) {
      content.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border text-primary"></div><div>Loading...</div></div>';
    }

    fetch('get_asset_details.php?id=' + encodeURIComponent(id))
      .then(r => r.json())
      .then(resp => {
        const d = (resp && resp.asset) ? resp.asset : resp;
        if (!d || d.error) {
          if (content) content.innerHTML = '<div class="text-danger">Failed to load details.</div>';
          return;
        }

        const statusBadge = (() => {
          const s = (d.status || '').toLowerCase();
          const map = { available: 'success', borrowed: 'warning', unserviceable: 'danger', red_tagged: 'danger' };
          return `<span class="badge bg-${map[s] || 'secondary'} text-uppercase">${esc(d.status || 'N/A')}</span>`;
        })();

        const chips = (label, value) => value ? `<span class="badge rounded-pill text-bg-light border me-1">${label}: ${esc(value)}</span>` : '';

        const img = d.image
          ? `<img src="../img/assets/${esc(d.image)}" class="img-fluid rounded border" style="width:100%;max-height:300px;object-fit:cover;" alt="Asset">`
          : `<div class="border rounded d-flex align-items-center justify-content-center bg-light" style="width:100%;height:300px;">
               <i class="bi bi-image text-muted" style="font-size:3rem;"></i>
             </div>`;

        content.innerHTML = `
  <div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
      <div>
        <h5 class="mb-1">${esc(d.description || 'Unnamed Asset')}</h5>
        <div class="text-muted small">
          ${esc(d.category_name || 'Uncategorized')}
          ${d.code ? ' • Code: ' + esc(d.code) : ''}
        </div>
      </div>
      <div>${statusBadge}</div>
    </div>

    <div class="row g-3">
      <div class="col-md-5">
        <div class="card h-100"><div class="card-body p-2">${img}</div></div>
      </div>

      <div class="col-md-7">
        <div class="card mb-3">
          <div class="card-header bg-light fw-semibold">Identification</div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-sm-6"><div class="small text-muted">Property No.</div><div class="fw-semibold">${esc(d.property_no || '—')}</div></div>
              <div class="col-sm-6"><div class="small text-muted">Inventory Tag</div><div class="fw-semibold">${esc(d.inventory_tag || '—')}</div></div>
              <div class="col-sm-6"><div class="small text-muted">Brand</div><div class="fw-semibold">${esc(d.brand || '—')}</div></div>
              <div class="col-sm-6"><div class="small text-muted">Model</div><div class="fw-semibold">${esc(d.model || '—')}</div></div>
              <div class="col-sm-6"><div class="small text-muted">Serial No.</div><div class="fw-semibold">${esc(d.serial_no || '—')}</div></div>
              <div class="col-sm-6"><div class="small text-muted">Unit / Quantity</div><div class="fw-semibold">${esc(d.unit || '—')} • ${esc(d.quantity || '0')}</div></div>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header bg-light fw-semibold">Assignment</div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-sm-6"><div class="small text-muted">Office</div><div class="fw-semibold">${esc(d.office_name || '—')}</div></div>
              <div class="col-sm-6"><div class="small text-muted">Person Accountable</div><div class="fw-semibold">${esc(d.employee_name || d.person_accountable || '—')}</div></div>
              <div class="col-sm-6"><div class="small text-muted">End User</div><div class="fw-semibold">${esc(d.end_user || '—')}</div></div>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header bg-light fw-semibold">Documentation</div>
          <div class="card-body">
            <div class="mb-2">${chips('ICS', d.ics_no)}${chips('PAR', d.par_no)}</div>
            <div class="row g-2">
              <div class="col-sm-6"><div class="small text-muted">Acquisition Date</div><div class="fw-semibold">${d.acquisition_date ? new Date(d.acquisition_date).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}) : '—'}</div></div>
              <div class="col-sm-6"><div class="small text-muted">Last Updated</div><div class="fw-semibold">${d.last_updated ? new Date(d.last_updated).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}) : '—'}</div></div>
              <div class="col-12"><div class="small text-muted">Unit Value</div><div class="fw-semibold">₱${(parseFloat(d.value||0)||0).toLocaleString(undefined,{minimumFractionDigits:2})}</div></div>
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-outline-primary btn-sm" href="create_mr.php?asset_id=${d.id}" target="_blank"><i class="bi bi-tag"></i> Property Tag</a>
          <a class="btn btn-outline-secondary btn-sm" href="forms.php?id=2" target="_blank"><i class="bi bi-journal-text"></i> Forms</a>
        </div>
      </div>
    </div>
  </div>`;

        const modal = new bootstrap.Modal(document.getElementById('globalAssetModal'));
        modal.show();
      })
      .catch(() => {
        if (content) content.innerHTML = '<div class="text-danger">Failed to load details.</div>';
      });
  });
})();
</script>


<!-- Global Asset Details Modal -->
<div class="modal fade" id="globalAssetModal" tabindex="-1" aria-labelledby="globalAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="globalAssetModalLabel">Asset Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="globalAssetContent">
          <div class="text-center text-muted py-4">
            <div class="spinner-border text-primary"></div>
            <div>Loading...</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>