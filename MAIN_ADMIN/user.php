<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Restrict access: only admins (and optionally office_admin) may access User Management
$currentRole = $_SESSION['role'] ?? '';
if (!in_array($currentRole, ['admin', 'user'], true)) {
  header("Location: admin_dashboard.php");
  exit();
}

// If this user has an explicit restriction permission, block access (admins bypass)
if ($currentRole !== 'admin') {
  if ($permStmt = $conn->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = 'restrict_user_management' LIMIT 1")) {
    $uid = (int)($_SESSION['user_id'] ?? 0);
    $permStmt->bind_param('i', $uid);
    $permStmt->execute();
    $permStmt->store_result();
    if ($permStmt->num_rows > 0) {
      $permStmt->close();
      header("Location: admin_dashboard.php");
      exit();
    }
    $permStmt->close();
  }
}

// Ensure system table has default_user_password column (idempotent)
// Create system table if it does not exist (minimal schema used here)
$conn->query("CREATE TABLE IF NOT EXISTS system (
  id INT AUTO_INCREMENT PRIMARY KEY,
  logo VARCHAR(255) DEFAULT '../img/default-logo.png',
  system_title VARCHAR(255) DEFAULT 'Inventory System'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Add column only if missing
$colRes = $conn->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system' AND COLUMN_NAME = 'default_user_password'");
if ($colRes && $colRes->num_rows === 0) {
  $conn->query("ALTER TABLE system ADD COLUMN default_user_password VARCHAR(255) NULL AFTER system_title");
}

// Handle default password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default_password'])) {
  $newDefault = isset($_POST['default_user_password']) ? trim($_POST['default_user_password']) : '';
  // Upsert system row (assumes single-row table)
  $res = $conn->query("SELECT 1 FROM system LIMIT 1");
  if ($res && $res->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE system SET default_user_password = ? LIMIT 1");
    $stmt->bind_param('s', $newDefault);
    $stmt->execute();
    $stmt->close();
  } else {
    $stmt = $conn->prepare("INSERT INTO system (logo, system_title, default_user_password) VALUES ('../img/default-logo.png','Inventory System',?)");
    $stmt->bind_param('s', $newDefault);
    $stmt->execute();
    $stmt->close();
  }
  header('Location: user.php?default_pwd_saved=1' . (isset($_GET['office']) ? ('&office='.(int)$_GET['office']) : ''));
  exit();
}

// Load current default password for display in forms
$default_user_password = '';
$sys = $conn->query("SELECT default_user_password FROM system LIMIT 1");
if ($sys && $sys->num_rows > 0) {
  $rowSys = $sys->fetch_assoc();
  $default_user_password = $rowSys['default_user_password'] ?? '';
}

// Soft-delete handler: mark user as deleted instead of removing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
  $deleteUserId = (int)$_POST['delete_user_id'];
  $officeBack = isset($_POST['office']) ? (int)$_POST['office'] : 0;
  if ($deleteUserId > 0) {
    $upd = $conn->prepare("UPDATE users SET status = 'deleted' WHERE id = ?");
    $upd->bind_param('i', $deleteUserId);
    $upd->execute();
    $upd->close();
  }
  $redirect = 'user.php';
  $qs = [];
  if ($officeBack > 0) { $qs[] = 'office=' . $officeBack; }
  $qs[] = 'soft_deleted=1';
  if (!empty($qs)) { $redirect .= '?' . implode('&', $qs); }
  header('Location: ' . $redirect);
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
  WHERE u.office_id = ? AND u.status <> 'deleted'
");

$userStmt->bind_param("i", $selected_office);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user_total = $userResult->num_rows;

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
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
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

    <!-- User Alerts -->
    <?php include 'alerts/user_alerts.php' ?>
    <?php if (isset($_GET['default_pwd_saved'])): ?>
      <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle"></i> Default password has been updated.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['soft_deleted'])): ?>
      <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle"></i> User has been deleted successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="container-fluid mt-3">
      <div class="page-header p-3 p-sm-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border" style="width:48px;height:48px;">
            <i class="bi bi-people text-primary fs-4"></i>
          </div>
          <div>
            <div class="h4 mb-0 title">User Management</div>
            <div class="text-muted small">Manage users and offices</div>
          </div>
        </div>
        <div class="toolbar d-flex align-items-center gap-2">
          <span class="badge text-bg-secondary" title="Total users listed">
            <?= (int)$user_total ?> user<?= $user_total == 1 ? '' : 's' ?>
          </span>
          <button id="toggleDensity" class="btn btn-outline-secondary btn-sm rounded-pill" title="Toggle compact density">
            <i class="bi bi-arrows-vertical me-1"></i> Density
          </button>
        </div>
      </div>
    </div>

    <!-- Default Password Settings -->
    <div class="container mt-3">
      <div class="card shadow-sm mb-3 card-hover">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong><i class="bi bi-key me-2"></i>Default Password</strong>
        </div>
        <div class="card-body">
          <form method="POST" class="row g-3">
            <input type="hidden" name="set_default_password" value="1" />
            <div class="col-md-6">
              <label for="default_user_password" class="form-label">Default Password for New Users</label>
              <div class="input-group">
                <input type="password" class="form-control" id="default_user_password" name="default_user_password"
                  value="<?= htmlspecialchars($default_user_password) ?>"
                  pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{12,}$" required
                  title="Minimum 12 characters, include uppercase, number and special character">
                <button type="button" class="btn btn-outline-secondary" onclick="(function(){const f=document.getElementById('default_user_password');f.type=f.type==='password'?'text':'password';})();" title="Show/Hide">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              <small class="text-muted">This value will auto-fill in the Add User form. You can change it anytime.</small>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Default Password</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- User Management Card with Office Filter -->
    <div class="card shadow-sm mb-4 mt-3 card-hover">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-person-lines-fill"></i>
          <span>Listing</span>
          <span class="badge text-bg-secondary d-none d-sm-inline"><?= (int)$user_total ?></span>
        </h5>

        <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
          <form method="GET" class="d-flex align-items-center gap-2 mb-0">
            <label for="officeFilter" class="form-label mb-0">Office</label>
            <select name="office" id="officeFilter" class="form-select form-select-sm" onchange="this.form.submit()">
              <?php while ($office = $officeQuery->fetch_assoc()): ?>
                <option value="<?= $office['id'] ?>" <?= $office['id'] == $selected_office ? 'selected' : '' ?>>
                  <?= htmlspecialchars($office['office_name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </form>

          <a href="#" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addOfficeModal">
            <i class="bi bi-plus-circle me-1"></i> New Office
          </a>

          <a href="#" class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill me-1"></i> New User
          </a>
        </div>
      </div>

      <div class="card-body table-responsive">
        <table id="userTable" class="table table-sm table-striped table-hover align-middle">
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
                  <button class="btn btn-sm btn-outline-primary editUserBtn rounded-pill"
                    data-id="<?= $user['id'] ?>"
                    data-fullname="<?= htmlspecialchars($user['fullname']) ?>"
                    data-username="<?= htmlspecialchars($user['username']) ?>"
                    data-email="<?= htmlspecialchars($user['email']) ?>"
                    data-role="<?= $user['role'] ?>"
                    data-status="<?= $user['status'] ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#editUserModal">
                    <i class="bi bi-pencil-square"></i>
                  </button>

                  <?php
                  // Check if user is deletable — this can be based on a separate query
                  $isDeletable = true;

                  try {
                    // Attempt a test delete inside a transaction (no commit)
                    $conn->begin_transaction();
                    $testStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $testStmt->bind_param("i", $user['id']);
                    $testStmt->execute();
                    $affected = $testStmt->affected_rows;
                    $conn->rollback(); // Rollback test delete
                    $isDeletable = $affected > 0;
                  } catch (Exception $e) {
                    $conn->rollback();
                    $isDeletable = false;
                  }
                  ?>

                  <?php if ($user['status'] === 'active'): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                      <span class="text-muted small" title="Admins cannot be deactivated">
                        <i class="bi bi-shield-lock-fill"></i>
                      </span>
                    <?php else: ?>
                      <form method="POST" action="deactivate_user.php" class="d-inline">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="office" value="<?= $selected_office ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Deactivate User">
                          <i class="bi bi-person-dash"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  <?php else: ?>
                    <form method="POST" action="activate_user.php" class="d-inline">
                      <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                      <input type="hidden" name="office" value="<?= $selected_office ?>">
                      <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Activate User">
                        <i class="bi bi-person-check"></i>
                      </button>
                    </form>
                  <?php endif; ?>

                  <!-- Delete Button: only show if role is NOT admin -->
                  <?php if ($user['role'] !== 'admin'): ?>
                    <button
                      class="btn btn-sm btn-outline-dark rounded-pill deleteUserBtn"
                      data-id="<?= $user['id'] ?>"
                      data-fullname="<?= htmlspecialchars($user['fullname']) ?>"
                      data-office="<?= $selected_office ?>"
                      data-bs-toggle="modal"
                      data-bs-target="#confirmDeleteUserModal"
                      title="Delete User">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php endif; ?>

                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Edit User Modal -->
  <?php include 'modals/edit_user_modal.php'; ?>

  <!-- Delete Confirmation Modal -->
  <?php include 'modals/delete_user_modal.php'; ?>

  <!-- Add Office Modal -->
  <?php include 'modals/add_office_modal.php'; ?>

  <!-- Add User Modal -->
  <?php include 'modals/add_user_modal.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
  <script src="js/user.js"></script>
</body>

</html>