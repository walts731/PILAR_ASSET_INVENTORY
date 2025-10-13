<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Ensure only super_admin or SYSTEM_ADMIN can access this page
$currentRole = strtolower($_SESSION['role'] ?? '');
if (!in_array($currentRole, ['super_admin', 'system_admin'])) {
  header("Location: system_admin_dashboard.php");
  exit();
}

// Ensure system table has default_user_password column (idempotent)
$conn->query("CREATE TABLE IF NOT EXISTS system (
  id INT AUTO_INCREMENT PRIMARY KEY,
  logo VARCHAR(255) DEFAULT '../img/default-logo.png',
  system_title VARCHAR(255) DEFAULT 'Inventory System',
  default_user_password VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Add column only if missing
$colRes = $conn->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system' AND COLUMN_NAME = 'default_user_password'");
if ($colRes && $colRes->num_rows === 0) {
  $conn->query("ALTER TABLE system ADD COLUMN default_user_password VARCHAR(255) NULL AFTER system_title");
}

// Fetch all roles from the database
$roles = [];
$rolesQuery = $conn->query("SELECT * FROM roles ORDER BY name ASC");
if ($rolesQuery) {
    while ($role = $rolesQuery->fetch_assoc()) {
        $roles[] = $role;
    }
}

// If no roles found, set default roles
if (empty($roles)) {
    $defaultRoles = ['SYSTEM_ADMIN', 'MAIN_ADMIN', 'MAIN_EMPLOYEE', 'MAIN_USER'];
    foreach ($defaultRoles as $roleName) {
        $roles[] = ['name' => $roleName, 'description' => $roleName . ' Role'];
    }
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
  header('Location: user_management.php?default_pwd_saved=1');
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
  if ($deleteUserId > 0) {
    $upd = $conn->prepare("UPDATE users SET status = 'deleted' WHERE id = ?");
    $upd->bind_param('i', $deleteUserId);
    $upd->execute();
    $upd->close();
    $_SESSION['success'] = 'User has been deactivated successfully.';
  }
  header('Location: user_management.php');
  exit();
}

// Fetch list of offices for dropdown
$officeQuery = $conn->query("SELECT id, office_name FROM offices");

// Set selected office from GET or use session default; allow 'all'
$selected_office = $_GET['office'] ?? 'all';

// Fetch users based on selected office (support 'all')
if ($selected_office === 'all') {
  $userStmt = $conn->prepare("
    SELECT u.id, u.username, u.fullname, u.email, u.role, u.status, u.created_at, o.office_name
    FROM users u
    JOIN offices o ON u.office_id = o.id
    WHERE u.status <> 'deleted'
  ");
} else {
  $officeId = (int)$selected_office;
  $userStmt = $conn->prepare("
    SELECT u.id, u.username, u.fullname, u.email, u.role, u.status, u.created_at, o.office_name
    FROM users u
    JOIN offices o ON u.office_id = o.id
    WHERE u.office_id = ? AND u.status <> 'deleted'
  ");
  $userStmt->bind_param("i", $officeId);
}

$userStmt->execute();
$userResult = $userStmt->get_result();
$user_total = $userResult->num_rows;

// Fetch current user's office if not set
if (!isset($_SESSION['office_id'])) {
  $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $stmt->bind_result($office_id);
  if ($stmt->fetch()) {
    $_SESSION['office_id'] = $office_id;
  }
  $stmt->close();
}

// Fetch user's full name for display
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management | Inventory System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css" />
  <link rel="stylesheet" href="css/templates.css" />
  <style>
    /* Sidebar and Main Content Layout */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: 250px;
      z-index: 1000;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      transition: all 0.3s;
      overflow-y: auto;
    }

    .main {
      margin-left: 250px;
      min-height: 100vh;
      background-color: #f8f9fc;
      transition: all 0.3s;
      padding: 1.5rem;
      width: calc(100% - 250px);
      position: relative;
      overflow-x: auto;
    }

    @media (max-width: 991.98px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.show {
        transform: translateX(0);
        width: 250px;
      }
      .main {
        margin-left: 0;
        width: 100%;
        padding: 1rem;
      }
      .main.expand {
        margin-left: 250px;
        width: calc(100% - 250px);
      }
    }

    /* Card Styling */
    .card {
      border: none;
      border-radius: 0.5rem;
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
      margin-bottom: 1.5rem;
      transition: all 0.3s ease;
      overflow: hidden;
    }

    /* Card Styling */
    .card {
      border: none;
      border-radius: 0.5rem;
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
      margin-bottom: 1.5rem;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
    }

    .card-header {
      background-color: #f8f9fc;
      border-bottom: 1px solid #e3e6f0;
      padding: 1rem 1.25rem;
    }

    .card-body {
      padding: 1.25rem;
    }

    /* Table Container */
    .table-container {
      background: white;
      border-radius: 0.5rem;
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
      overflow: hidden;
      margin-bottom: 1.5rem;
    }

    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      width: 100%;
    }

    /* Table Styling */
    .table {
      margin-bottom: 0;
      min-width: 100%;
      table-layout: fixed;
    }

    .table thead th {
      background-color: #f8f9fc;
      border-bottom: 2px solid #e3e6f0;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.7rem;
      letter-spacing: 0.5px;
      padding: 0.85rem 1rem;
      color: #4e73df;
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .table td {
      vertical-align: middle;
      padding: 0.85rem 1rem;
      border-color: #eaecf4;
      color: #5a5c69;
      font-size: 0.9rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .table tbody tr {
      transition: all 0.15s ease;
    }

    .table tbody tr:hover {
      background-color: #f8f9fc;
    }

    .table > :not(:last-child) > :last-child > * {
      border-bottom-color: #eaecf4;
    }

    /* Action buttons */
    .btn-action {
      padding: 0.35rem 0.5rem;
      font-size: 0.8rem;
      line-height: 1;
      border-radius: 0.25rem;
    }

    /* Buttons */
    .btn {
      padding: 0.375rem 0.75rem;
      font-size: 0.85rem;
      font-weight: 500;
      border-radius: 0.35rem;
      transition: all 0.2s;
    }

    .btn i {
      margin-right: 0.25rem;
    }

    /* Page Header */
    .page-header {
      padding: 1.5rem 0;
      margin-bottom: 1.5rem;
      border-bottom: 1px solid #e3e6f0;
    }

    .page-header h1 {
      font-weight: 600;
      color: #4e73df;
      margin-bottom: 0.5rem;
    }

    .page-header .breadcrumb {
      margin-bottom: 0;
      background: transparent;
      padding: 0.5rem 0;
    }

    /* Form Controls */
    .form-control, .form-select {
      border-radius: 0.35rem;
      padding: 0.5rem 0.75rem;
      border: 1px solid #d1d3e2;
    }

    .form-control:focus, .form-select:focus {
      border-color: #bac8f3;
      box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    /* Badges */
    .badge {
      font-weight: 500;
      padding: 0.35em 0.65em;
      border-radius: 0.25rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .card-body {
        padding: 1rem;
      }
      
      .table-responsive {
        border: none;
      }
    }
  </style>
</head>

<body>
  <?php include 'includes/sidebar.php' ?>
  
  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid py-4">
      <!-- Success/Error Messages -->
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
          <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <?php if (isset($_GET['default_pwd_saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
          <i class="bi bi-check-circle me-2"></i> Default password has been updated.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">User Management</h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="system_admin_dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">User Management</li>
          </ol>
        </nav>
      </div>

      <!-- Default Password Settings -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0">
            <i class="bi bi-shield-lock me-2"></i>Default User Password Settings
          </h6>
        </div>
        <div class="card-body">
          <form method="POST" class="row g-3 align-items-center">
            <div class="col-md-6">
              <label for="default_user_password" class="form-label">Default Password for New Users</label>
              <div class="input-group">
                <input type="password" class="form-control" id="default_user_password" name="default_user_password" 
                      value="<?= htmlspecialchars($default_user_password) ?>" required
                      autocomplete="new-password">
                <button class="btn btn-outline-secondary toggle-password" type="button" 
                        onclick="togglePassword('default_user_password', this)">
                  <i class="bi bi-eye-slash"></i>
                </button>
              </div>
              <div class="form-text">This will be the default password for all new users.</div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" name="set_default_password" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- User Management Card -->
      <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-lines-fill text-primary"></i>
            <h6 class="mb-0">User Listing</h6>
            <span class="badge bg-primary rounded-pill"><?= $user_total ?> users</span>
          </div>
          <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
              <select name="office" class="form-select form-select-sm" style="min-width: 180px;" onchange="this.form.submit()">
                <option value="all" <?= $selected_office === 'all' ? 'selected' : '' ?>>All Offices</option>
                <?php 
                $officeQuery->data_seek(0); // Reset pointer
                while ($office = $officeQuery->fetch_assoc()): 
                ?>
                  <option value="<?= $office['id'] ?>" <?= $selected_office == $office['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($office['office_name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </form>
            <a href="user_roles.php" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
              <i class="bi bi-person-gear"></i> Manage Roles
            </a>
            <button class="btn btn-sm btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addUserModal">
              <i class="bi bi-plus-lg"></i> Add User
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="p-3 border-bottom">
            <div class="row g-2">
              <div class="col-md-4">
                <div class="input-group input-group-sm" style="max-width: 350px;">
                  <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                  <input type="text" id="searchUser" class="form-control border-start-0" placeholder="Search by name, email, or role..."
                         autocomplete="off">
                  <button class="btn btn-outline-secondary" type="button" id="searchButton">
                    <i class="bi bi-search"></i> Search
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table id="userTable" class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th>Username</th>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Office</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                // Reset user result pointer
                $userResult->data_seek(0);
                while ($user = $userResult->fetch_assoc()): 
                ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="ms-2">
                          <div class="fw-semibold"><?= htmlspecialchars($user['username']) ?></div>
                          <div class="text-muted small">ID: <?= htmlspecialchars($user['id']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($user['fullname']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                      <span class="badge bg-<?= in_array(strtolower($user['role']), ['admin', 'system_admin']) ? 'primary' : 'success' ?>">
                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($user['office_name']) ?></td>
                    <td>
                      <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                        <?= ucfirst(htmlspecialchars($user['status'])) ?>
                      </span>
                    </td>
                    <td>
                      <button type="button" class="btn btn-outline-primary btn-sm edit-user" data-id="<?= $user['id'] ?>">
                        <i class="bi bi-pencil"></i>
                      </button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="addUserForm" action="process_user.php" method="POST">
          <input type="hidden" name="action" value="add_user">
          <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="fullname" name="fullname" required
                         autocomplete="name">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="username" name="username" required
                         autocomplete="username">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="email" name="email" required
                         autocomplete="email">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                  <select class="form-select" id="role" name="role" required>
                    <option value="">Select Role</option>
                    <?php 
                    $roleQuery = $conn->query("SELECT * FROM roles");
                    while ($role = $roleQuery->fetch_assoc()): ?>
                      <option value="<?= htmlspecialchars($role['name']) ?>"><?= htmlspecialchars($role['name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="office" class="form-label">Office <span class="text-danger">*</span></label>
                  <select class="form-select" id="office" name="office_id" required>
                    <option value="">Select Office</option>
                    <?php 
                    $officeQuery->data_seek(0); // Reset pointer
                    while ($office = $officeQuery->fetch_assoc()): ?>
                      <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['office_name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="mt-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="sendWelcomeEmail" name="send_welcome_email" checked>
                <label class="form-check-label" for="sendWelcomeEmail">
                  Send welcome email with login instructions
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add User</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  </script>
</div>

<script>
  function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('bi-eye-slash');
      icon.classList.add('bi-eye');
    } else {
      input.type = 'password';
      icon.classList.remove('bi-eye');
      icon.classList.add('bi-eye-slash');
    }
  }
</script>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editUserForm" action="process_user.php" method="POST">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editFullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editFullname" name="fullname" required
                                       autocomplete="name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editUsername" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editUsername" name="username" required
                                       autocomplete="username">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editEmail" name="email" required
                                       autocomplete="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRole" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="editRole" name="role" required>
                                    <?php 
                                    $allowed_roles = ['OFFICE_ADMIN', 'MAIN_ADMIN', 'MAIN_USER', 'USER'];
                                    $display_names = [
                                        'OFFICE_ADMIN' => 'OFFICE ADMIN',
                                        'MAIN_ADMIN' => 'MAIN ADMIN',
                                        'MAIN_USER' => 'MAIN USER',
                                        'USER' => 'USER'
                                    ];
                                    
                                    foreach ($roles as $role): 
                                        if (in_array($role['name'], $allowed_roles)): 
                                    ?>
                                        <option value="<?= htmlspecialchars($role['name']) ?>">
                                            <?= htmlspecialchars($display_names[$role['name']]) ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" checked>
                                    <label class="form-check-label" for="statusActive">
                                        Active
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive">
                                    <label class="form-check-label" for="statusInactive">
                                        Inactive
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resetPasswordForm" action="process_user.php" method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="resetPasswordUserId">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reset the password for <strong id="resetUserName"></strong>?</p>
                    <div class="form-group mt-3">
                        <label for="resetPwNewPassword" class="form-label">New Password (leave blank to generate random password)</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="resetPwNewPassword" 
                                   name="new_password" 
                                   autocomplete="new-password"
                                   placeholder="Leave blank to generate random password"
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="resetPwGeneratePassword"
                                    aria-label="Generate random password">
                            <i class="fas fa-sync-alt"></i> Generate
                        </button>
                    </div>
                    <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="forcePasswordChange" name="force_password_change" checked>
                        <label class="form-check-label" for="forcePasswordChange">
                            Require password change at next login
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include footer -->
<?php include '../includes/footer.php'; ?>

<!-- Custom JavaScript -->
<script>
// Safe notification handling
if (typeof NotificationManager === 'undefined') {
    window.NotificationManager = {
        show: function(type, message) {
            const alertType = type === 'error' ? 'danger' : type;
            const alertHtml = `
                <div class="alert alert-${alertType} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('.container-fluid:first').prepend(alertHtml);
            setTimeout(() => $('.alert').alert('close'), 5000);
        }
    };
}

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Edit user modal
    $('.edit-user').on('click', function() {
        var userId = $(this).data('id');
        
        // Fetch user data via AJAX
        $.ajax({
            url: 'get_user.php',
            type: 'GET',
            data: { id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var user = response.data;
                    $('#editUserId').val(user.id);
                    $('#editFullname').val(user.fullname);
                    $('#editUsername').val(user.username);
                    $('#editEmail').val(user.email);
                    $('#editRole').val(user.role);
                    
                    // Set status radio button
                    if (user.status === 'inactive') {
                        $('#statusInactive').prop('checked', true);
                    } else {
                        $('#statusActive').prop('checked', true);
                    }
                    
                    // Show the modal
                    var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                    editModal.show();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error fetching user data. Please try again.');
            }
        });
    });

    // Reset password modal
    $('.reset-password').on('click', function() {
        var userId = $(this).data('id');
        var userName = $(this).data('name');
        
        $('#resetPasswordUserId').val(userId);
        $('#resetUserName').text(userName);
        $('#newPassword').val('');
        
        var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        resetModal.show();
    });

    // Generate random password
    $('#resetPwGeneratePassword').on('click', function() {
        const password = generatePassword(12);
        $('#resetPwNewPassword').val(password);
    });

    function generatePassword(length = 12) {
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const numbers = '0123456789';
        const symbols = '!@#$%^&*()_+~`|}{[]\\:;\'"<>,.?/=';
        const allChars = lowercase + uppercase + numbers + symbols;
        
        // Ensure at least one character from each set
        let password = [
            lowercase[Math.floor(Math.random() * lowercase.length)],
            uppercase[Math.floor(Math.random() * uppercase.length)],
            numbers[Math.floor(Math.random() * numbers.length)],
            symbols[Math.floor(Math.random() * symbols.length)]
        ];
        
        // Fill the rest of the password with random characters
        for (let i = 4; i < length; i++) {
            password.push(allChars[Math.floor(Math.random() * allChars.length)]);
        }
        
        // Shuffle the password array and join into a string
        return password.sort(() => Math.random() - 0.5).join('');
    }

    // Form submission handling
    $('#addUserForm, #editUserForm, #resetPasswordForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        // Submit form via AJAX
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    var alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                    
                    $('.container-fluid.py-4').prepend(alert);
                    
                    // Close modal if open
                    $('.modal').modal('hide');
                    
                    // Reload page after a short delay to show the success message
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    var alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                    
                    $('.container-fluid.py-4').prepend(alert);
                    
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function(xhr, status, error) {
                // Show error message
                var alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            'An error occurred. Please try again.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                
                $('.container-fluid.py-4').prepend(alert);
                
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
    
    // Activate/Deactivate user
    $('.activate-user, .deactivate-user').on('click', function() {
        var userId = $(this).data('id');
        var action = $(this).hasClass('activate-user') ? 'activate' : 'deactivate';
        var confirmMessage = action === 'activate' 
            ? 'Are you sure you want to activate this user?' 
            : 'Are you sure you want to deactivate this user?';
        
        if (confirm(confirmMessage)) {
            $.ajax({
                url: 'process_user.php',
                type: 'POST',
                data: {
                    action: 'update_user_status',
                    user_id: userId,
                    status: action === 'activate' ? 'active' : 'inactive'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message and reload
                        var alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                    response.message +
                                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                    '</div>';
                        
                        $('.container-fluid.py-4').prepend(alert);
                        
                        // Reload page after a short delay to show the success message
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error updating user status. Please try again.');
                }
            });
        }
    });
    // Handle deactivate user
    $(document).on('click', '.deactivate-user', function() {
        const $button = $(this);
        const userId = $button.data('id');
        const userName = $button.data('name');
        
        if (confirm(`Are you sure you want to deactivate ${userName}?`)) {
            $button.addClass('btn-loading');
            // Add your deactivation AJAX call here
            // Example:
            $.post('process_user.php', {
                action: 'update_user_status',
                user_id: userId,
                status: 'inactive'
            }, function(response) {
                if (response.success) {
                    showAlert('success', `Successfully deactivated ${userName}.`);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', response.message || 'Failed to deactivate user.');
                    $button.removeClass('btn-loading');
                }
            }, 'json').fail(function() {
                showAlert('danger', 'An error occurred. Please try again.');
                $button.removeClass('btn-loading');
            });
        }
    });

    // Handle activate user
    $(document).on('click', '.activate-user', function() {
        const $button = $(this);
        const userId = $button.data('id');
        const userName = $button.data('name');
        
        if (confirm(`Are you sure you want to activate ${userName}?`)) {
            $button.addClass('btn-loading');
            
            $.post('process_user.php', {
                action: 'update_user_status',
                user_id: userId,
                status: 'active'
            }, function(response) {
                if (response.success) {
                    showAlert('success', `Successfully activated ${userName}.`);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', response.message || 'Failed to activate user.');
                    $button.removeClass('btn-loading');
                }
            }, 'json').fail(function() {
                showAlert('danger', 'An error occurred. Please try again.');
                $button.removeClass('btn-loading');
            });
        }
    });

    // Handle reset password
    $(document).on('click', '.reset-password', function() {
        const $button = $(this);
        const userId = $button.data('id');
        const userName = $button.data('name');
        
        if (confirm(`Reset password for ${userName}? A temporary password will be sent to their email.`)) {
            $button.addClass('btn-loading');
            
            $.post('process_user.php', {
                action: 'reset_password',
                user_id: userId
            }, function(response) {
                $button.removeClass('btn-loading');
                if (response.success) {
                    showAlert('success', `Password reset email sent to ${userName}.`);
                } else {
                    showAlert('danger', response.message || 'Failed to reset password.');
                }
            }, 'json').fail(function() {
                showAlert('danger', 'An error occurred. Please try again.');
                $button.removeClass('btn-loading');
            });
        }
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Append alert to the top of the content area
        $('.container-fluid:first').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }
});

// Initialize DataTable with search functionality
$(document).ready(function() {
    // Initialize DataTable
    var userTable = $('#userTable').DataTable({
        paging: true,
        searching: true,
        info: true,
        responsive: true,
        order: [[0, 'asc']],
        language: {
            search: "",
            searchPlaceholder: "Search users...",
            lengthMenu: "Show _MENU_ users per page",
            info: "Showing _START_ to _END_ of _TOTAL_ users",
            infoEmpty: "No users found",
            infoFiltered: "(filtered from _MAX_ total users)"
        },
        // Disable the default search box
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Get search elements
    var searchInput = $('#searchUser');
    var searchButton = $('#searchButton');
    
    // Function to perform search
    function performSearch() {
        userTable.search(searchInput.val()).draw();
    }
    
    // Handle search when typing (with a small delay)
    var searchTimeout;
    searchInput.on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 300);
    });
    
    // Handle search button click
    searchButton.on('click', function() {
        performSearch();
    });
    
    // Handle Enter key in search input
    searchInput.on('keypress', function(e) {
        if (e.which === 13) { // 13 is Enter key
            e.preventDefault();
            performSearch();
        }
    });
    
    // Clear the default search box
    $('.dataTables_filter').remove();
    
    // Focus the search input when the page loads
    searchInput.focus();
});
</script>
