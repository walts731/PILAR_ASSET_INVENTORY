<?php
require_once '../connect.php';
session_start();

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
$stmt->fetch();
$stmt->close();

// Fetch available assets to borrow
$office_id = $_SESSION['office_id'];
$query = "SELECT id, asset_name, description, quantity, unit, value, acquisition_date FROM assets WHERE office_id = ? AND status = 'Available'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $office_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Borrow Assets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container mt-4">
      <h3 class="mb-4"><i class="bi bi-arrow-left-right"></i> Borrow Assets</h3>

      <table id="borrowTable" class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Asset Name</th>
            <th>Description</th>
            <th>Qty</th>
            <th>Unit</th>
            <th>Value</th>
            <th>Acquired</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $count = 1;
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$count}</td>
                    <td>{$row['asset_name']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['unit']}</td>
                    <td>₱" . number_format($row['value'], 2) . "</td>
                    <td>" . date('F j, Y', strtotime($row['acquisition_date'])) . "</td>
                    <td>
                      <button class='btn btn-primary btn-sm'>
                        <i class='bi bi-box-arrow-in-right'></i> Borrow
                      </button>
                    </td>
                  </tr>";
            $count++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#borrowTable').DataTable();
    });
  </script>
</body>

</html>
