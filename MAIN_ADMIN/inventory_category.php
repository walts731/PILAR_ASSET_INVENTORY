<?php
require_once '../connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
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

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
if ($stmt->fetch()) {
  $user_name = $fullname;
}
$stmt->close();

// Get category ID from URL
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Fetch category details
$category = null;
$stmt = $conn->prepare("SELECT id, category_name FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  $category = $result->fetch_assoc();
}
$stmt->close();

// Fetch aggregates from assets_new that have at least one linked asset in this category
$an_rows = [];
if ($category && isset($category['category_name'])) {
  $stmt = $conn->prepare("
    SELECT 
      an.id AS an_id,
      an.description,
      an.quantity,
      an.unit,
      an.unit_cost,
      an.date_created,
      COALESCE((
        SELECT c.category_name
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        WHERE a.asset_new_id = an.id
        ORDER BY a.id ASC
        LIMIT 1
      ), 'Uncategorized') AS category_name,
      f.ics_no AS ics_no
    FROM assets_new an
    LEFT JOIN ics_form f ON f.id = an.ics_id
    WHERE EXISTS (
      SELECT 1 FROM assets ax WHERE ax.asset_new_id = an.id AND ax.category = ?
    )
    ORDER BY an.date_created DESC
  ");
  $stmt->bind_param("i", $category_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) { $an_rows[] = $row; }
  $stmt->close();
}

// Get system logo
$systemSql = "SELECT logo FROM system LIMIT 1";
$systemResult = $conn->query($systemSql);
$system = $systemResult->fetch_assoc();
$systemLogo = !empty($system['logo']) ? '../img/' . $system['logo'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $category ? htmlspecialchars($category['category_name']) : 'Category Not Found' ?> - Inventory</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    :root { --inv-accent: #0d6efd; }
    .page-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #eef3ff 100%);
      border: 1px solid #e9ecef;
      border-radius: .75rem;
    }
    .page-header .title { font-weight: 600; }
    .toolbar .btn { transition: transform .08s ease-in; }
    .toolbar .btn:hover { transform: translateY(-1px); }
    .card-hover:hover { box-shadow: 0 .25rem .75rem rgba(0,0,0,.06) !important; }
    .table thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
  </style>
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">

    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid mt-4">
      <?php if ($category): ?>
      <!-- Page Header -->
      <div class="page-header p-3 p-sm-4 d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-3">
          <?php if (!empty($systemLogo)): ?>
          <img src="<?= htmlspecialchars($systemLogo) ?>" alt="Logo" class="rounded border bg-white p-1" style="height:42px;object-fit:contain;">
          <?php endif; ?>
          <div>
          <div class="h4 mb-0 title"><?= htmlspecialchars($category['category_name'] ?? 'Category') ?></div>
          <div class="text-muted small">Category Inventory</div>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border" style="width:48px;height:48px;">
            <i class="bi bi-tags text-primary fs-4"></i>
          </div>
          <div>
            
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($category): ?>

        <?php if (count($an_rows) > 0): ?>
          <div class="card shadow-sm card-hover">
            <div class="card-header">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0">
                  <i class="bi bi-list-ul"></i> <?= htmlspecialchars($category['category_name'] ?? 'Unknown Category') ?> Assets
                </h5>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <label for="categoryDateFilter" class="form-label mb-0 small">Filter:</label>
                    <select id="categoryDateFilter" class="form-select form-select-sm" style="min-width: 120px;">
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
                  <div id="customDateRangeCategory" class="d-flex align-items-center gap-2" style="display: none;">
                    <input type="date" id="categoryFromDate" class="form-control form-control-sm" />
                    <span class="small">to</span>
                    <input type="date" id="categoryToDate" class="form-control form-control-sm" />
                    <button class="btn btn-sm btn-outline-primary" id="applyCustomFilterCategory" title="Apply Filter">
                      <i class="bi bi-funnel"></i>
                    </button>
                  </div>
                  <button type="button" id="selectAllBtn" class="btn btn-sm btn-outline-secondary" title="Select/Deselect all items">
                    <i class="bi bi-check-square me-1"></i> Select All
                  </button>
                  <div class="btn-group" role="group">
                    <button type="button" id="exportCsvBtn" class="btn btn-sm btn-success" title="Export filtered items to CSV">
                      <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
                    </button>
                    <button type="button" id="exportPdfBtn" class="btn btn-sm btn-danger" title="Export filtered items to PDF">
                      <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <!-- Alert for selection feedback -->
              <div id="selectionAlert" class="alert alert-info d-none" role="alert">
                <i class="bi bi-info-circle"></i> 
                <span id="selectionMessage">Select assets to export or generate reports. Leave unselected to export all items.</span>
              </div>
              
              <?php if (count($an_rows) > 0): ?>
                <form id="reportForm" method="POST" action="generate_selected_report.php" target="_blank">
                  
                  <div class="table-responsive">
                  <table id="inventoryTable" class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th><input type="checkbox" id="selectAllAssetsCat" /></th>
                        <th>ICS No</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($an_rows as $row): ?>
                        <tr>
                          <td><input type="checkbox" class="asset-checkbox-cat" name="selected_assets_new[]" value="<?= (int)$row['an_id'] ?>" /></td>
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
                              data-bs-target="#viewAssetModal"
                              title="View asset details">
                              <i class="bi bi-eye me-1"></i> View
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  </div>
                </form>
              <?php else: ?>
                <div class="text-center py-5">
                  <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                  <p class="mt-3 mb-0 text-muted">No assets yet in this category.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>


        <?php else: ?>
          <div class="alert alert-warning">No assets found in this category.</div>
        <?php endif; ?>
      <?php else: ?>
        <div class="alert alert-danger">Category not found.</div>
      <?php endif; ?>
    </div>
  </div>

  <?php include 'modals/inventory_category_modal.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>

  <script>
    // Report generation and checkbox functionality
    $(document).ready(function() {
      // Initialize DataTable first
      $('#inventoryTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        language: {
          search: "Filter records:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          paginate: {
            previous: "Prev",
            next: "Next"
          }
        }
      });

      // Checkbox functionality
      function updateSelectionCount() {
        const checkedBoxes = $('.asset-checkbox-cat:checked').length;
        $('#selectedCount').text(checkedBoxes);
        
        if (checkedBoxes > 0) {
          $('#generateReportBtn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
          $('#selectionAlert').removeClass('d-none').removeClass('alert-info').addClass('alert-success');
          $('#selectionMessage').text(`${checkedBoxes} asset(s) selected for export/report generation.`);
        } else {
          $('#generateReportBtn').prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
          $('#selectionAlert').removeClass('d-none').removeClass('alert-success').addClass('alert-info');
          $('#selectionMessage').text('Select assets to export or generate reports. Leave unselected to export all items.');
        }
      }

      // Select All functionality
      $('#selectAllAssetsCat').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.asset-checkbox-cat').prop('checked', isChecked);
        updateSelectionCount();
        
        // Update button text
        $('#selectAllBtn').html(isChecked 
          ? '<i class="bi bi-square"></i> Deselect All' 
          : '<i class="bi bi-check-square"></i> Select All'
        );
      });

      // Individual checkbox change
      $('.asset-checkbox-cat').on('change', function() {
        updateSelectionCount();
        
        // Update select all checkbox state
        const totalBoxes = $('.asset-checkbox-cat').length;
        const checkedBoxes = $('.asset-checkbox-cat:checked').length;
        
        if (checkedBoxes === 0) {
          $('#selectAllAssetsCat').prop('indeterminate', false).prop('checked', false);
          $('#selectAllBtn').html('<i class="bi bi-check-square"></i> Select All');
        } else if (checkedBoxes === totalBoxes) {
          $('#selectAllAssetsCat').prop('indeterminate', false).prop('checked', true);
          $('#selectAllBtn').html('<i class="bi bi-square"></i> Deselect All');
        } else {
          $('#selectAllAssetsCat').prop('indeterminate', true);
          $('#selectAllBtn').html('<i class="bi bi-check-square"></i> Select All');
        }
      });

      // Select All button click
      $('#selectAllBtn').on('click', function() {
        const anyChecked = $('.asset-checkbox-cat:checked').length > 0;
        $('.asset-checkbox-cat').prop('checked', !anyChecked);
        $('#selectAllAssetsCat').prop('checked', !anyChecked).prop('indeterminate', false);
        updateSelectionCount();
        
        $(this).html(!anyChecked 
          ? '<i class="bi bi-square"></i> Deselect All' 
          : '<i class="bi bi-check-square"></i> Select All'
        );
      });

      // Generate Report button click
      $('#generateReportBtn').on('click', function() {
        const checkedBoxes = $('.asset-checkbox-cat:checked').length;
        
        if (checkedBoxes === 0) {
          $('#selectionAlert').removeClass('d-none alert-success').addClass('alert-warning');
          $('#selectionMessage').text('Please select at least one asset to generate a report.');
          return;
        }

        // Show loading state
        $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Generating...');
        
        // Submit the form
        $('#reportForm').submit();
        
        // Reset button after a delay
        setTimeout(() => {
          $(this).prop('disabled', false).html('<i class="bi bi-file-earmark-text"></i> Generate Report (<span id="selectedCount">' + checkedBoxes + '</span>)');
        }, 2000);
      });

      // Export CSV button click
      $('#exportCsvBtn').on('click', function() {
        const checkedBoxes = $('.asset-checkbox-cat:checked');
        const categoryId = <?= $category_id ?>;
        const filterParams = getCurrentFilterParams();
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Exporting...');
        
        // Build URL with selected assets and filters
        let url = `export_category_csv.php?category=${categoryId}`;
        
        // Add filter parameters
        Object.keys(filterParams).forEach(key => {
          if (filterParams[key]) {
            url += `&${key}=${encodeURIComponent(filterParams[key])}`;
          }
        });
        
        if (checkedBoxes.length > 0) {
          const selectedIds = [];
          checkedBoxes.each(function() {
            selectedIds.push($(this).val());
          });
          url += `&selected_assets=${selectedIds.join(',')}`;
        }
        
        // Open export in new tab
        window.open(url, '_blank');
        
        // Reset button after a delay
        setTimeout(() => {
          $(this).prop('disabled', false).html('<i class="bi bi-file-earmark-spreadsheet"></i> CSV');
        }, 2000);
      });

      // Export PDF button click
      $('#exportPdfBtn').on('click', function() {
        const checkedBoxes = $('.asset-checkbox-cat:checked');
        const categoryId = <?= $category_id ?>;
        const filterParams = getCurrentFilterParams();
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Exporting...');
        
        // Build URL with selected assets and filters
        let url = `export_category_pdf.php?category=${categoryId}`;
        
        // Add filter parameters
        Object.keys(filterParams).forEach(key => {
          if (filterParams[key]) {
            url += `&${key}=${encodeURIComponent(filterParams[key])}`;
          }
        });
        
        if (checkedBoxes.length > 0) {
          const selectedIds = [];
          checkedBoxes.each(function() {
            selectedIds.push($(this).val());
          });
          url += `&selected_assets=${selectedIds.join(',')}`;
        }
        
        // Open export in new tab
        window.open(url, '_blank');
        
        // Reset button after a delay
        setTimeout(() => {
          $(this).prop('disabled', false).html('<i class="bi bi-file-earmark-pdf"></i> PDF');
        }, 2000);
      });

      // Date filter functionality
      $('#categoryDateFilter').on('change', function() {
        const filterType = $(this).val();
        if (filterType === 'custom') {
          $('#customDateRangeCategory').show();
        } else {
          $('#customDateRangeCategory').hide();
          if (filterType !== 'all') {
            applyDateFilter(filterType);
          } else {
            // Reset to show all records
            $('#inventoryTable').DataTable().search('').draw();
          }
        }
      });

      // Apply custom date filter
      $('#applyCustomFilterCategory').on('click', function() {
        const fromDate = $('#categoryFromDate').val();
        const toDate = $('#categoryToDate').val();
        
        if (!fromDate || !toDate) {
          alert('Please select both from and to dates.');
          return;
        }
        
        if (new Date(fromDate) > new Date(toDate)) {
          alert('From date cannot be later than to date.');
          return;
        }
        
        applyDateFilter('custom', fromDate, toDate);
      });

      // Function to apply date filters
      function applyDateFilter(filterType, fromDate = null, toDate = null) {
        const table = $('#inventoryTable').DataTable();
        
        // Get date range based on filter type
        let startDate, endDate;
        const now = new Date();
        
        switch(filterType) {
          case 'current_month':
            startDate = new Date(now.getFullYear(), now.getMonth(), 1);
            endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            break;
          case 'current_quarter':
            const currentQuarter = Math.floor(now.getMonth() / 3);
            startDate = new Date(now.getFullYear(), currentQuarter * 3, 1);
            endDate = new Date(now.getFullYear(), (currentQuarter + 1) * 3, 0);
            break;
          case 'current_year':
            startDate = new Date(now.getFullYear(), 0, 1);
            endDate = new Date(now.getFullYear(), 11, 31);
            break;
          case 'last_month':
            startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            endDate = new Date(now.getFullYear(), now.getMonth(), 0);
            break;
          case 'last_quarter':
            const lastQuarter = Math.floor(now.getMonth() / 3) - 1;
            const year = lastQuarter < 0 ? now.getFullYear() - 1 : now.getFullYear();
            const quarter = lastQuarter < 0 ? 3 : lastQuarter;
            startDate = new Date(year, quarter * 3, 1);
            endDate = new Date(year, (quarter + 1) * 3, 0);
            break;
          case 'last_year':
            startDate = new Date(now.getFullYear() - 1, 0, 1);
            endDate = new Date(now.getFullYear() - 1, 11, 31);
            break;
          case 'custom':
            startDate = new Date(fromDate);
            endDate = new Date(toDate);
            break;
        }
        
        // Apply filter to DataTable (this is a simple implementation)
        // For more complex filtering, you might want to reload data from server
        table.draw();
      }

      // Get current filter parameters for export
      function getCurrentFilterParams() {
        const filterType = $('#categoryDateFilter').val();
        let params = { filter_type: filterType };
        
        if (filterType === 'custom') {
          params.from_date = $('#categoryFromDate').val();
          params.to_date = $('#categoryToDate').val();
        }
        
        return params;
      }

      // Initialize count
      updateSelectionCount();
    });

    function formatDateFormal(dateStr) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      const date = new Date(dateStr);
      return date.toLocaleDateString('en-US', options);
    }

    document.querySelectorAll('.viewAssetBtn').forEach(button => {
      button.addEventListener('click', function() {
        const assetId = this.getAttribute('data-id');
        const source = this.getAttribute('data-source') || 'assets';

        const url = source === 'assets_new'
          ? `get_assets_new_details.php?id=${assetId}`
          : `get_asset_details.php?id=${assetId}`;

        fetch(url)
          .then(response => response.json())
          .then(data => {
            if (data.error) {
              alert(data.error);
              return;
            }

            document.getElementById('viewOfficeName').textContent = data.office_name;
            document.getElementById('viewCategoryName').textContent = `${data.category_name} (${data.category_type})`;
            document.getElementById('viewType').textContent = data.type;
            document.getElementById('viewQuantity').textContent = data.quantity;
            document.getElementById('viewUnit').textContent = data.unit;
            document.getElementById('viewDescription').textContent = data.description;
            document.getElementById('viewAcquisitionDate').textContent = formatDateFormal(data.acquisition_date);
            document.getElementById('viewLastUpdated').textContent = formatDateFormal(data.last_updated);
            document.getElementById('viewValue').textContent = parseFloat(data.value).toFixed(2);

            const totalValue = parseFloat(data.value) * parseInt(data.quantity);
            document.getElementById('viewTotalValue').textContent = totalValue.toFixed(2);

            const logoEl = document.getElementById('municipalLogoImg');
            if (logoEl) logoEl.src = '../img/' + (data.system_logo ?? '');

            // Build items table
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
                      <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Asset" onclick="forceDeleteAsset(${it.item_id})"><i class="bi bi-trash"></i></button>
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

    // Force delete shared from inventory.php
    window.forceDeleteAsset = function(assetId) {
      if (!assetId) return;
      if (!confirm('This will permanently delete the asset and update quantities. Continue?')) return;
      fetch('force_delete_asset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(assetId)
      })
      .then(r => r.json())
      .then(resp => {
        if (resp && resp.success) {
          location.reload();
        } else {
          alert(resp.message || 'Failed to delete asset');
        }
      })
      .catch(err => alert('Error deleting: ' + err));
    }
  </script>

</body>

</html>