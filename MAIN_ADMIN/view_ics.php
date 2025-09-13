<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Get ICS ID from URL
$ics_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($ics_id <= 0) {
  die("Invalid ICS ID.");
}

$ics_form_id = $_GET['form_id'] ?? '';

// Fetch ICS form details
$sql = "SELECT f.id AS ics_id, f.header_image, f.entity_name, f.fund_cluster, f.ics_no,
               f.received_from_name, f.received_from_position,
               f.received_by_name, f.received_by_position, f.created_at,
               o.office_name
        FROM ics_form f
        LEFT JOIN offices o ON f.id = o.id
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ics_id);
$stmt->execute();
$result = $stmt->get_result();
$ics = $result->fetch_assoc();
$stmt->close();

if (!$ics) {
  die("ICS record not found.");
}

// Fetch ICS items
$sql_items = "SELECT item_id, item_no, description, quantity, unit, unit_cost, total_cost, estimated_useful_life
              FROM ics_items
              WHERE ics_id = ?
              ORDER BY item_no ASC";
$stmt = $conn->prepare($sql_items);
$stmt->bind_param("i", $ics_id);
$stmt->execute();
$result_items = $stmt->get_result();
$ics['items'] = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ICS Details - <?= htmlspecialchars($ics['ics_no']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>
  <?php include 'includes/sidebar.php' ?>

  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="saved_ics.php?id=<?php echo $ics_form_id ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Saved ICS</a>
      </div>

      <div class="card mb-5 shadow-sm">
        <div class="card-body">

          <div class="mb-3 text-center">
            <?php if (!empty($ics['header_image'])): ?>
              <img src="../img/<?= htmlspecialchars($ics['header_image']) ?>"
                class="img-fluid mb-2 w-100"
                style="max-height:300px; object-fit:cover;">
            <?php else: ?>
              <p class="text-muted">No header image</p>
            <?php endif; ?>
          </div>

          <div class="row mb-2">
            <div class="col-md-6">
              <p class="mb-1 fw-semibold">Entity Name:</p>
              <p class="border-bottom pb-1"><?= htmlspecialchars($ics['entity_name']) ?></p>
            </div>

          </div>

          <div class="row mb-2">
            <div class="col-md-6">
              <p class="mb-1 fw-semibold">Fund Cluster:</p>
              <p class="border-bottom pb-1"><?= htmlspecialchars($ics['fund_cluster']) ?></p>
            </div>
            <div class="col-md-6">
              <p class="mb-1 fw-semibold">ICS No.:</p>
              <p class="border-bottom pb-1"><?= htmlspecialchars($ics['ics_no']) ?></p>
            </div>
          </div>


          <hr>

          <!-- Items Table -->
          <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
              <thead class="table-secondary">
                <tr>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Unit Cost</th>
                  <th>Total Cost</th>
                  <th>Description</th>
                  <th>Item No</th>
                  <th>Estimated Useful Life</th>
                  <th>Action</th> <!-- New column for actions -->
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($ics['items'])): ?>
                  <?php foreach ($ics['items'] as $item): ?>
                    <tr>
                      <td><?= htmlspecialchars($item['quantity']) ?></td>
                      <td><?= htmlspecialchars($item['unit']) ?></td>
                      <td>₱<?= number_format($item['unit_cost'], 2) ?></td>
                      <td>₱<?= number_format($item['total_cost'], 2) ?></td>
                      <td><?= htmlspecialchars($item['description']) ?></td>
                      <td><?= htmlspecialchars($item['item_no']) ?></td>
                      <td><?= htmlspecialchars($item['estimated_useful_life']) ?></td>
                      <td>
                        <a href="create_mr.php?item_id=<?= htmlspecialchars($item['item_id']) ?>&ics_id=<?= htmlspecialchars($ics['ics_id']) ?>&form_id=<?php echo $ics_form_id ?>" class="btn btn-primary btn-sm">
                          Create Property Tag
                        </a>

                      </td> <!-- Create MR button -->
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-muted">No items found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>


          <!-- Signatories -->
          <div class="row mt-4">
            <div class="col-md-6 text-center">
              <p class="mb-0 fw-bold">Received from:</p>
              <p>
                <?= htmlspecialchars($ics['received_from_name']) ?><br>
                <small><?= htmlspecialchars($ics['received_from_position']) ?></small>
              </p>
            </div>
            <div class="col-md-6 text-center">
              <p class="mb-0 fw-bold">Received by:</p>
              <p>
                <?= htmlspecialchars($ics['received_by_name']) ?><br>
                <small><?= htmlspecialchars($ics['received_by_position']) ?></small>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Export / Print -->
      <div class="mb-5">
        <a href="generate_ics_pdf.php?id=<?= $ics['ics_id'] ?>" class="btn btn-success">
          <i class="bi bi-printer"></i> Print / Export PDF
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>