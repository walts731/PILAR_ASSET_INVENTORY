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
if ($category) {
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
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">

    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid mt-4">
      <?php if ($category): ?>

        <?php if (count($an_rows) > 0): ?>
          <div class="card shadow-sm">
            <div class="card-body">
              <?php if (count($an_rows) > 0): ?>
                <div class="table-responsive">
                  <table id="inventoryTable" class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th><input type="checkbox" id="selectAllAssetsCat" /></th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Cost</th>
                        <th>Total Value</th>
                        <th>ICS No</th>
                        <th>Actions</th>
                        <th>Date Acquired</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($an_rows as $row): ?>
                        <tr>
                          <td><input type="checkbox" class="asset-checkbox-cat" value="<?= (int)$row['an_id'] ?>" /></td>
                          <td><?= htmlspecialchars($row['description']) ?></td>
                          <td><?= htmlspecialchars($row['category_name']) ?></td>
                          <td><?= (int)$row['quantity'] ?></td>
                          <td><?= htmlspecialchars($row['unit']) ?></td>
                          <td>&#8369; <?= number_format((float)$row['unit_cost'], 2) ?></td>
                          <td>&#8369; <?= number_format(((float)$row['unit_cost']) * (int)$row['quantity'], 2) ?></td>
                          <td><?= htmlspecialchars($row['ics_no'] ?? '') ?></td>
                          <td class="text-nowrap">
                            <button type="button"
                              class="btn btn-sm btn-outline-info rounded-pill viewAssetBtn"
                              data-source="assets_new"
                              data-id="<?= (int)$row['an_id'] ?>"
                              data-bs-toggle="modal"
                              data-bs-target="#viewAssetModal">
                              <i class="bi bi-eye"></i>
                            </button>
                          </td>
                          <td><?= !empty($row['date_created']) ? date('M d, Y', strtotime($row['date_created'])) : '' ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
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
                td.colSpan = 7;
                td.className = 'text-center text-muted';
                td.textContent = 'No item records available';
                tr.appendChild(td);
                itemsBody.appendChild(tr);
              } else {
                items.forEach(it => {
                  const tr = document.createElement('tr');
                  tr.innerHTML = `
                    <td>${it.item_id}</td>
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

    $(document).ready(function() {
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
    });
  </script>

</body>

</html>