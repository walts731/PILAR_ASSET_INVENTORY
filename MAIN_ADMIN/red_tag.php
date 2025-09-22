<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch Red Tag details with related asset and user information
$sql = "SELECT rt.id, rt.red_tag_number, rt.description, rt.item_location, 
               rt.removal_reason, rt.action, rt.status, rt.date_received,
               u.fullname as tagged_by_name,
               a.property_no, a.office_id
        FROM red_tags rt
        LEFT JOIN users u ON rt.tagged_by = u.id
        LEFT JOIN assets a ON rt.asset_id = a.id
        ORDER BY rt.date_received DESC";
$result = $conn->query($sql);

$red_tag_records = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $red_tag_records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Red Tag Records</title>
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
        <h5 class="mb-0 fw-bold"><i class="bi bi-tag-fill text-danger"></i> Red Tag Records</h5>
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
        <?php if (!empty($red_tag_records)): ?>
          <div class="table-responsive">
            <table id="redTagTable" class="table align-middle">
              <thead class="text-center">
                <tr>
                  <th width="40">
                    <input type="checkbox" id="selectAll" class="form-check-input">
                  </th>
                  <th>Red Tag Number</th>
                  <th>Property Number</th>
                  <th>Description</th>
                  <th>Location</th>
                  <th>Removal Reason</th>
                  <th>Action</th>
                  <th>Tagged By</th>
                  <th>Date Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($red_tag_records as $red_tag): ?>
                  <tr>
                    <td class="text-center">
                      <input type="checkbox" class="form-check-input red-tag-checkbox" 
                             value="<?= $red_tag['id'] ?>" 
                             data-red-tag-number="<?= htmlspecialchars($red_tag['red_tag_number']) ?>">
                    </td>
                    <td class="fw-bold text-danger"><?= htmlspecialchars($red_tag['red_tag_number']) ?></td>
                    <td><?= htmlspecialchars($red_tag['property_no'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($red_tag['description']) ?></td>
                    <td><?= htmlspecialchars($red_tag['item_location']) ?></td>
                    <td>
                      <span class="badge bg-warning text-dark"><?= htmlspecialchars($red_tag['removal_reason']) ?></span>
                    </td>
                    <td>
                      <span class="badge bg-info text-dark"><?= htmlspecialchars($red_tag['action']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($red_tag['tagged_by_name'] ?? 'N/A') ?></td>
                    <td><?= date('F d, Y', strtotime($red_tag['date_received'])) ?></td>
                    <td class="text-center">
                      <a href="print_red_tag.php?id=<?= urlencode($red_tag['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> View
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle"></i> No Red Tag records found.
          </div>
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
    const table = $('#redTagTable').DataTable({
      order: [[7, 'desc']], // Sort by Date Created (adjusted for checkbox column)
      pageLength: 10,
      columnDefs: [
        { orderable: false, targets: [0, 8] } // Disable sorting on checkbox and Action columns
      ]
    });

    // Bulk selection functionality
    function updateSelectedCount() {
      const selectedCount = $('.red-tag-checkbox:checked').length;
      $('#selectedCount').text(selectedCount);
      $('#printSelectedBtn').prop('disabled', selectedCount === 0);
      
      // Update select all button text
      const totalCheckboxes = $('.red-tag-checkbox').length;
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
      const isChecked = $('#selectAll').prop('checked') || $('.red-tag-checkbox:checked').length === 0;
      $('.red-tag-checkbox').prop('checked', isChecked);
      $('#selectAll').prop('checked', isChecked);
      updateSelectedCount();
    });

    // Individual checkbox change
    $(document).on('change', '.red-tag-checkbox', function() {
      const totalCheckboxes = $('.red-tag-checkbox').length;
      const checkedCheckboxes = $('.red-tag-checkbox:checked').length;
      
      $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
      updateSelectedCount();
    });

    // Print selected functionality
    $('#printSelectedBtn').on('click', function() {
      const selectedIds = [];
      $('.red-tag-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
      });
      
      if (selectedIds.length > 0) {
        // Open bulk print page in new window
        const url = 'bulk_print_red_tags.php?ids=' + selectedIds.join(',');
        window.open(url, '_blank');
      } else {
        alert('Please select at least one red tag to print.');
      }
    });

    // Initialize count
    updateSelectedCount();
  });
</script>
</body>
</html>
