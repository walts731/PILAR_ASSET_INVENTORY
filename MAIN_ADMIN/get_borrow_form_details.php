<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  exit();
}

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid submission ID']);
  exit();
}

// Fetch system settings for logo
$system = ['logo' => '../img/default-logo.png', 'system_title' => 'Inventory System'];
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
  $system = $result->fetch_assoc();
}

// Fetch the borrow form submission
$sql = "SELECT * FROM borrow_form_submissions WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Database error']);
  exit();
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Submission not found']);
  exit();
}

$submission = $result->fetch_assoc();
$stmt->close();

// Check if status is returned and fetch return slip data
$return_slip_data = null;
if (strtolower($submission['status']) === 'returned') {
  // For guest borrowing system, we don't have separate return slip data
  // The return slip is just the borrow form marked as returned
  $return_slip_data = true; // Flag to indicate we should show return slip
}

// Format status badge
$status = strtolower($submission['status']);
$badge_class = 'bg-secondary';
if ($status === 'pending') $badge_class = 'bg-warning text-dark';
elseif ($status === 'approved') $badge_class = 'bg-success';
elseif ($status === 'rejected') $badge_class = 'bg-danger';
elseif ($status === 'completed') $badge_class = 'bg-info';
elseif ($status === 'returned') $badge_class = 'bg-primary';

// Generate printable form HTML (similar to GUEST/borrow.php)
$content = '<style>
.print-form {
  font-family: Arial, sans-serif;
  max-width: 900px;
  margin: 0 auto;
  background: white;
  padding: 20px;
  border: 1px solid #ddd;
}
.seal {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  object-fit: cover;
}
.document-code {
  font-size: 12px;
}
.table-fixed td, .table-fixed th {
  vertical-align: middle;
}
@media print {
  .print-form {
    box-shadow: none;
    border: 0;
  }
  body {
    background: white;
  }
}
</style>

<div class="print-form">
  <div class="row align-items-center mb-3">
    <div class="col-auto">
      <img src="' . htmlspecialchars('../img/' . $system['logo']) . '" alt="Municipal Logo" class="seal">
    </div>
    <div class="col">
      <div class="text-center">
        <div style="font-weight:700">Republic of the Philippines</div>
        <div style="font-weight:700">Province of Sorsogon</div>
        <div style="font-size:18px;font-weight:800">LOCAL GOVERNMENT UNIT OF PILAR</div>
      </div>
    </div>
    <div class="col-auto text-end document-code">
      <div>Document Code:</div>
      <div><strong>PS-DIT-01-F03-01-01</strong></div>
      <div class="mt-2">Effective Date:</div>
      <div><strong>22 May 2023</strong></div>
    </div>
  </div>

  <hr>

  <div class="row g-2 mb-2">
    <div class="col-md-6">
      <label class="form-label fw-bold">Name:</label>
      <div class="border-bottom border-dark pb-1">' . htmlspecialchars($submission['guest_name']) . '</div>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-bold">Date Borrowed:</label>
      <div class="border-bottom border-dark pb-1">' . date('M j, Y', strtotime($submission['date_borrowed'])) . '</div>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-bold">Schedule of Return:</label>
      <div class="border-bottom border-dark pb-1">' . date('M j, Y', strtotime($submission['schedule_return'])) . '</div>
    </div>
  </div>

  <div class="row g-2 align-items-end mb-3">
   <div class="col-md-4">
      <label class="form-label fw-bold">Contact No.:</label>
      <div class="border-bottom border-dark pb-1">' . htmlspecialchars($submission['contact']) . '</div>
    </div>
    <div class="col-md-5">
      <label class="form-label fw-bold">Barangay:</label>
      <div class="border-bottom border-dark pb-1">' . htmlspecialchars($submission['barangay']) . '</div>
    </div>
    <div class="col-md-3">
  <label class="form-label fw-bold">Borrower Signature:</label>
  <div class="border-bottom border-dark pb-1" style="height: 25px;"></div>
</div>

   
  </div>

  <hr>

  <!-- Borrow Slip Title -->
  <div class="text-center mb-3">
    <h5 style="font-weight: 700; text-decoration: underline;">BORROW SLIP</h5>
  </div>

  <div class="table-responsive mb-3">
    <table class="table table-bordered table-fixed">
      <thead class="table-light text-center align-middle">
        <tr>
          <th style="width:60%">THINGS BORROWED</th>
          <th style="width:10%">QTY</th>
          <th style="width:30%">REMARKS</th>
        </tr>
      </thead>
      <tbody>';

// Parse items from JSON
$items = json_decode($submission['items'], true);
if ($items && is_array($items)) {
  foreach ($items as $item) {
    $content .= '<tr>';
    $content .= '<td>' . htmlspecialchars($item['thing'] ?? '') . '</td>';
    $content .= '<td>' . htmlspecialchars($item['qty'] ?? '') . '</td>';
    $content .= '<td>' . htmlspecialchars($item['remarks'] ?? '') . '</td>';
    $content .= '</tr>';
  }
} else {
  $content .= '<tr><td colspan="3" class="text-center text-muted">No items found</td></tr>';
}

$content .= '
      </tbody>
    </table>
  </div>

  <div class="row mb-3">
    <div class="col-md-6 text-center">
      <label class="form-label fw-bold">Releasing Officer:</label>
      <div class="border-bottom border-dark pb-1 mt-2">' . htmlspecialchars($submission['releasing_officer']) . '</div>
    </div>
    <div class="col-md-6 text-center">
      <label class="form-label fw-bold">Approved by:</label>
      <div class="border-bottom border-dark pb-1 mt-2">' . htmlspecialchars($submission['approved_by']) . '</div>
    </div>
  </div>

  <!-- Status and submission info -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="text-end">
        <span class="badge ' . $badge_class . ' fs-6">' . ucfirst($status) . '</span>
      </div>
      <div class="text-muted small mt-2">
        Submission #' . htmlspecialchars($submission['submission_number']) . ' • Submitted: ' . date('M j, Y g:i A', strtotime($submission['submitted_at'])) . '
      </div>
    </div>
  </div>
</div>';

if ($return_slip_data) {
  $content .= '<div class="print-form" style="page-break-before: always; margin-top: 40px;">
  <div class="row align-items-center mb-3">
    <div class="col-auto">
      <img src="' . htmlspecialchars('../img/' . $system['logo']) . '" alt="Municipal Logo" class="seal">
    </div>
    <div class="col">
      <div class="text-center">
        <div style="font-weight:700">Republic of the Philippines</div>
        <div style="font-weight:700">Province of Sorsogon</div>
        <div style="font-size:18px;font-weight:800">LOCAL GOVERNMENT UNIT OF PILAR</div>
      </div>
    </div>
    <div class="col-auto text-end document-code">
      <div>Document Code:</div>
      <div><strong>PS-DIT-01-F03-01-01</strong></div>
      <div class="mt-2">Effective Date:</div>
      <div><strong>22 May 2023</strong></div>
    </div>
  </div>

  <hr>

  <!-- Return Slip Title -->
  <div class="text-center mb-3">
    <h5 style="font-weight: 700; text-decoration: underline;">RETURN SLIP</h5>
  </div>

  <div class="table-responsive mb-3">
    <table class="table table-bordered table-fixed">
      <thead class="table-light text-center align-middle">
        <tr>
          <th style="width:50%">ASSET DESCRIPTION</th>
          <th style="width:15%">QTY</th>
          <th style="width:20%">CONDITION</th>
          <th style="width:15%">REMARKS</th>
        </tr>
      </thead>
      <tbody>';

  // Parse items from JSON for return slip
  $items = json_decode($submission['items'], true);
  if ($items && is_array($items)) {
    foreach ($items as $item) {
      $content .= '<tr>';
      $content .= '<td>' . htmlspecialchars($item['thing'] ?? '') . '</td>';
      $content .= '<td>' . htmlspecialchars($item['qty'] ?? '') . '</td>';
      $content .= '<td>Returned</td>';
      $content .= '<td>Items returned to inventory</td>';
      $content .= '</tr>';
    }
  } else {
    $content .= '<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>';
  }

  $content .= '
      </tbody>
    </table>
  </div>

  <div class="row g-2 mb-2">
    <div class="col-md-6">
      <label class="form-label fw-bold">Returned by:</label>
      <div class="border-bottom border-dark pb-1">' . htmlspecialchars($submission['guest_name']) . '</div>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-bold">Return Date:</label>
      <div class="border-bottom border-dark pb-1">' . date('M j, Y', strtotime($submission['updated_at'])) . '</div>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-bold">Status:</label>
      <div class="border-bottom border-dark pb-1">Returned</div>
    </div>
  </div>

  <div class="row g-2 align-items-end mb-3">
   <div class="col-md-4">
      <label class="form-label fw-bold">Received by:</label>
      <div class="border-bottom border-dark pb-1" style="height: 25px;"></div>
    </div>
    <div class="col-md-5">
      <label class="form-label fw-bold">Date Received:</label>
      <div class="border-bottom border-dark pb-1" style="height: 25px;"></div>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-bold">Signature:</label>
      <div class="border-bottom border-dark pb-1" style="height: 25px;"></div>
    </div>
  </div>
</div>';
}

$content .= '</div>';

echo json_encode(['success' => true, 'content' => $content]);

$conn->close();
?>
