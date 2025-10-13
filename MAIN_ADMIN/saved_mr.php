<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch MR details (only required columns)
$sql = "SELECT mr_id, inventory_tag, description, office_location, person_accountable, created_at
        FROM mr_details
        ORDER BY created_at DESC";
$result = $conn->query($sql);

$mr_records = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mr_records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Saved MR Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main">
  <?php include 'includes/topbar.php'; ?>

  <div class="container-fluid py-4">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-list-check"></i> Saved Property Tags Records</h5>
        <div class="d-flex gap-2">
          <button type="button" id="selectAllBtn" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-check-square"></i> Select All
          </button>
          <button type="button" id="printSelectedBtn" class="btn btn-sm btn-primary" disabled>
            <i class="bi bi-printer"></i> Print Selected (<span id="selectedCount">0</span>)
          </button>
        </div>
      </div>
      <div class="card-body">
        <?php if (!empty($mr_records)): ?>
          <div class="table-responsive">
            <table id="mrTable" class="table align-middle">
              <thead class="text-center">
                <tr>
                  <th width="40">
                    <input type="checkbox" id="selectAll" class="form-check-input">
                  </th>
                  <th>Inventory Tag</th>
                  <th>Description</th>
                  <th>Office</th>
                  <th>Person Accountable</th>
                  <th>Date Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mr_records as $mr): ?>
                  <tr>
                    <td class="text-center">
                      <input type="checkbox" class="form-check-input mr-checkbox" 
                             value="<?= $mr['mr_id'] ?>" 
                             data-inventory-tag="<?= htmlspecialchars($mr['inventory_tag']) ?>">
                    </td>
                    <td><?= htmlspecialchars($mr['inventory_tag']) ?></td>
                    <td><?= htmlspecialchars($mr['description']) ?></td>
                    <td><?= htmlspecialchars($mr['office_location']) ?></td>
                    <td><?= htmlspecialchars($mr['person_accountable']) ?></td>
                    <td><?= date('F d, Y', strtotime($mr['created_at'])) ?></td>
                    <td class="text-center">
                      <a href="bulk_print_mr.php?ids=<?= urlencode($mr['mr_id']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> View
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info mb-0">No MR records found.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
<script>
  $(document).ready(function () {
    const table = $('#mrTable').DataTable({
      order: [[5, 'desc']], // Sort by Date Created (adjusted for checkbox column)
      pageLength: 10,
      columnDefs: [
        { orderable: false, targets: [0, 6] } // Disable sorting on checkbox and Action columns
      ]
    });

    // Bulk selection functionality
    function updateSelectedCount() {
      const selectedCount = $('.mr-checkbox:checked').length;
      $('#selectedCount').text(selectedCount);
      $('#printSelectedBtn').prop('disabled', selectedCount === 0);
      
      // Update select all button text
      const totalCheckboxes = $('.mr-checkbox').length;
      if (selectedCount === 0) {
        $('#selectAllBtn').html('<i class="bi bi-check-square"></i> Select All');
      } else if (selectedCount === totalCheckboxes) {
        $('#selectAllBtn').html('<i class="bi bi-square"></i> Deselect All');
      } else {
        $('#selectAllBtn').html('<i class="bi bi-check-square"></i> Select All (' + selectedCount + ')');
      }
    }

    // Select/Deselect all functionality
    $('#selectAll, #selectAllBtn').on('click', function() {
      const isChecked = $('#selectAll').prop('checked') || $('.mr-checkbox:checked').length === 0;
      $('.mr-checkbox').prop('checked', isChecked);
      $('#selectAll').prop('checked', isChecked);
      updateSelectedCount();
    });

    // Individual checkbox change
    $(document).on('change', '.mr-checkbox', function() {
      const totalCheckboxes = $('.mr-checkbox').length;
      const checkedCheckboxes = $('.mr-checkbox:checked').length;
      
      $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
      updateSelectedCount();
    });

    // Print selected functionality
    $('#printSelectedBtn').on('click', function() {
      const selectedIds = [];
      $('.mr-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
      });
      
      if (selectedIds.length > 0) {
        // Open bulk print page in new window
        const url = 'bulk_print_mr.php?ids=' + selectedIds.join(',');
        window.open(url, '_blank');
      } else {
        alert('Please select at least one MR record to print.');
      }
    });

    // Initialize count
    updateSelectedCount();
  });
</script>
</body>
</html>
