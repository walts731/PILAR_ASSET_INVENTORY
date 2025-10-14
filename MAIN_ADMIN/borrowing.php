<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

// Page meta
$page = 'borrow';

// Status filter removed - show all submissions

// Build base SQL
$baseSql = "SELECT 
              id,
              submission_number,
              guest_name,
              date_borrowed,
              schedule_return,
              barangay,
              contact,
              releasing_officer,
              approved_by,
              items,
              status,
              submitted_at,
              updated_at
            FROM borrow_form_submissions";

$sql = $baseSql . " ORDER BY submitted_at DESC";
$stmt = $conn->prepare($sql);

$stmt->execute();
$result = $stmt->get_result();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$count = is_array($rows) ? count($rows) : 0;
$stmt->close();


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrow Form Submissions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Borrow Form Submissions</h4>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary"><?= (int)$count ?> submissions</span>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table id="borrowedAssetsTable" class="table table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  
                  <th>Guest Name</th>
                  <th>Date Borrowed</th>
                  <th>Return Date</th>
                  <th>Barangay</th>
                  <th>Contact</th>
                  <th>Items</th>
                  <th>Status</th>
                  <th>Submitted</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($rows)): ?>
                  <?php foreach ($rows as $r): ?>
                    <tr>
                     
                      <td><?= htmlspecialchars($r['guest_name']) ?></td>
                      <td><?= date('M j, Y', strtotime($r['date_borrowed'])) ?></td>
                      <td><?= date('M j, Y', strtotime($r['schedule_return'])) ?></td>
                      <td><?= htmlspecialchars($r['barangay']) ?></td>
                      <td><?= htmlspecialchars($r['contact']) ?></td>
                      <td>
                        <?php
                        $items = json_decode($r['items'], true);
                        if ($items && is_array($items)) {
                          foreach ($items as $item) {
                            echo '<div class="mb-1"><small>' . htmlspecialchars($item['thing']) . ' (Qty: ' . htmlspecialchars($item['qty']) . ')</small></div>';
                          }
                        } else {
                          echo '<small class="text-muted">No items</small>';
                        }
                        ?>
                      </td>
                      <td>
                        <?php
                        $status = strtolower($r['status']);
                        $badgeClass = 'bg-secondary';
                        if ($status === 'pending') $badgeClass = 'bg-warning text-dark';
                        elseif ($status === 'approved') $badgeClass = 'bg-success';
                        elseif ($status === 'rejected') $badgeClass = 'bg-danger';
                        elseif ($status === 'completed') $badgeClass = 'bg-info';
                        elseif ($status === 'returned') $badgeClass = 'bg-primary';
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                      </td>
                      <td><small class="text-muted"><?= date('M j, Y g:i A', strtotime($r['submitted_at'])) ?></small></td>
                      <td>
                        <div class="btn-group" role="group">
                          <button class="btn btn-sm btn-info view-btn" data-id="<?= $r['id'] ?>" title="View Details">
                            <i class="bi bi-eye"></i>
                          </button>
                          <?php if ($status === 'pending'): ?>
                            <button class="btn btn-sm btn-success accept-btn" data-id="<?= $r['id'] ?>" title="Accept">
                              <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-danger decline-btn" data-id="<?= $r['id'] ?>" title="Decline">
                              <i class="bi bi-x-circle"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- View Form Details Modal -->
  <div class="modal fade" id="viewFormModal" tabindex="-1" aria-labelledby="viewFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewFormModalLabel">Borrow Form Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="formDetailsContent">
          <!-- Form details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="printFormBtn">
            <i class="bi bi-printer me-1"></i>Print Form
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

  <script src="js/dashboard.js"></script>
  <script>
    $(function() {
      $('#borrowedAssetsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[8, 'desc']], // Submitted date
        language: {
          search: 'Search submissions:',
          lengthMenu: 'Show _MENU_ entries',
          info: 'Showing _START_ to _END_ of _TOTAL_ submissions',
          emptyTable: 'No submissions found',
          zeroRecords: 'No matching submissions found'
        },
        columnDefs: []
      });

      // Handle view button
      $(document).on('click', '.view-btn', function() {
        const submissionId = $(this).data('id');
        const btn = $(this);

        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

        $.ajax({
          url: 'get_borrow_form_details.php',
          method: 'GET',
          data: { id: submissionId },
          dataType: 'json',
          success: function(response) {
  if (response.success) {
    $('#formDetailsContent').html(response.content);

    // Check the status inside the content
    let statusText = $('#formDetailsContent').find('.badge').text().trim().toLowerCase();

    // Update modal title based on content
    if (statusText === 'returned') {
      $('#viewFormModalLabel').text('Borrow Form & Return Slip Details');
    } else {
      $('#viewFormModalLabel').text('Borrow Form Details');
    }

    // Show Print button for approved and returned statuses
    if (statusText === 'approved') {
      $('#printFormBtn').show().html('<i class="bi bi-printer me-1"></i>Print Borrow Form');
    } else if (statusText === 'returned') {
      $('#printFormBtn').show().html('<i class="bi bi-printer me-1"></i>Print Return Slip');
    } else {
      $('#printFormBtn').hide();
    }

    const modal = new bootstrap.Modal(document.getElementById('viewFormModal'));
    modal.show();
  } else {
    alert('Error: ' + response.message);
  }
},

          error: function() {
            alert('An error occurred while loading form details. Please try again.');
          },
          complete: function() {
            btn.prop('disabled', false).html('<i class="bi bi-eye"></i>');
          }
        });
      });

      // Handle print button (capture only the modal content area)
$(document).on('click', '#printFormBtn', function() {
  const contentDiv = document.querySelector('#formDetailsContent');
  const statusText = $('#formDetailsContent').find('.badge').text().trim().toLowerCase();
  const isReturnSlip = statusText === 'returned';
  const title = isReturnSlip ? 'Return Slip Print' : 'Borrow Form Print';

  html2canvas(contentDiv, {
    scale: 2, // high resolution
    useCORS: true,
    backgroundColor: '#ffffff'
  }).then(canvas => {
    const imageData = canvas.toDataURL('image/png');

    // Open a new window for printing
    const printWindow = window.open('', '_blank', 'width=900,height=700');
    printWindow.document.open();
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>${title}</title>
        <style>
          body {
            margin: 0;
            padding: 20px;
            text-align: center;
            background: #fff;
            font-family: Arial, sans-serif;
          }
          .print-header {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
          }
          img {
            width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
          }
          @media print {
            body {
              margin: 0;
              padding: 10px;
              background: #fff;
            }
            .print-header {
              font-size: 16px;
            }
            img {
              width: 100%;
              height: auto;
              border: none;
              border-radius: 0;
            }
          }
        </style>
      </head>
      <body>
        <div class="print-header">${isReturnSlip ? 'RETURN SLIP' : 'BORROW FORM'}</div>
        <img src="${imageData}" alt="${title} Snapshot" />
        <script>
          window.onload = function() {
            window.print();
            window.onafterprint = function() { window.close(); };
          };
        <\/script>
      </body>
      </html>
    `);
    printWindow.document.close();
  });
});


      // Handle accept button
      $(document).on('click', '.accept-btn', function() {
        const submissionId = $(this).data('id');
        const btn = $(this);
        
        if (confirm('Are you sure you want to accept this borrow request?')) {
          btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');
          
          $.ajax({
            url: 'process_borrow_action.php',
            method: 'POST',
            data: { 
              action: 'accept', 
              submission_id: submissionId 
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                location.reload();
              } else {
                alert('Error: ' + response.message);
                btn.prop('disabled', false).html('<i class="bi bi-check-circle"></i>');
              }
            },
            error: function() {
              alert('An error occurred. Please try again.');
              btn.prop('disabled', false).html('<i class="bi bi-check-circle"></i>');
            }
          });
        }
      });

      // Handle decline button
      $(document).on('click', '.decline-btn', function() {
        const submissionId = $(this).data('id');
        const btn = $(this);
        
        if (confirm('Are you sure you want to decline this borrow request?')) {
          btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');
          
          $.ajax({
            url: 'process_borrow_action.php',
            method: 'POST',
            data: { 
              action: 'decline', 
              submission_id: submissionId 
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                location.reload();
              } else {
                alert('Error: ' + response.message);
                btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i>');
              }
            },
            error: function() {
              alert('An error occurred. Please try again.');
              btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i>');
            }
          });
        }
      });
    });
  </script>
</body>
</html>
