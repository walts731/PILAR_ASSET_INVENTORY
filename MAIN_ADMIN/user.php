<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch list of offices for dropdown
$officeQuery = $conn->query("SELECT id, office_name FROM offices");

// Set selected office from GET or use session default
$selected_office = $_GET['office'] ?? $_SESSION['office_id'];

// Fetch users based on selected office
$userStmt = $conn->prepare("
  SELECT u.id, u.username, u.fullname, u.email, u.role, u.status, u.created_at, o.office_name
  FROM users u
  JOIN offices o ON u.office_id = o.id
  WHERE u.office_id = ?
");
$userStmt->bind_param("i", $selected_office);
$userStmt->execute();
$userResult = $userStmt->get_result();

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

  // TABLES
  // SELECT `id`, `user_id`, `filename`, `generated_at` FROM `generated_reports` 
  // SELECT `id`, `username`, `fullname`, `email`, `password`, `role`, `status`, `created_at`, `reset_token`, `reset_token_expiry`, `office_id`, `profile_picture`, `session_timeout` FROM `users` 
  // SELECT `id`, `office_name`, `icon` FROM `offices` 
  // SELECT `id`, `category_name`, `type` FROM `categories` 
  // SELECT `id`, `asset_name`, `category`, `description`, `quantity`, `unit`, `status`, `acquisition_date`, `office_id`, `red_tagged`, `last_updated`, `value`, `qr_code`, `type` FROM `assets` 
}

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <!-- User Management Card with Office Filter -->
    <div class="card shadow-sm mb-4 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">User Management</h5>
        <form method="GET" class="d-flex align-items-center gap-2">
          <label for="officeFilter" class="form-label mb-0">Office</label>
          <select name="office" id="officeFilter" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php while ($office = $officeQuery->fetch_assoc()): ?>
              <option value="<?= $office['id'] ?>" <?= $office['id'] == $selected_office ? 'selected' : '' ?>>
                <?= htmlspecialchars($office['office_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </form>
      </div>

      <div class="card-body table-responsive">
        <table id="userTable" class="table table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>Full Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Office</th>
              <th>Joined At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($user = $userResult->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($user['fullname']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'dark' : 'info' ?>">
                    <?= ucfirst($user['role']) ?>
                  </span></td>
                <td>
                  <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                    <?= ucfirst($user['status']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($user['office_name']) ?></td>
                <td><?= date('F j, Y', strtotime($user['created_at'])) ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" title="Edit User">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" title="Delete User">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
  <script>
  $(document).ready(function() {
    $('#userTable').DataTable({
      "pageLength": 10
    });
  });
</script>
</body>

</html>