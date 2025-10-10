<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get logged-in user's office
$office_id = $_SESSION['office_id'] ?? 0;
if (!$office_id) {
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($office_id);
    $stmt->fetch();
    $_SESSION['office_id'] = $office_id;
    $stmt->close();
}

// Fetch users in this office
$userStmt = $conn->prepare("
  SELECT u.id, u.username, u.fullname, u.email, u.role, u.status, u.created_at, o.office_name
  FROM users u
  JOIN offices o ON u.office_id = o.id
  WHERE u.office_id = ?
");
$userStmt->bind_param("i", $office_id);
$userStmt->execute();
$userResult = $userStmt->get_result();

// Fetch full name of logged-in user
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
<title>User Management - Inventory</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>

<?php include 'includes/sidebar.php' ?>

<div class="main">
    <?php include 'includes/topbar.php' ?>
    <?php include 'alerts/user_alerts.php' ?>

    <div class="card shadow-sm mb-4 mt-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">User Management </h5>

            
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
                            <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'dark' : 'info' ?>"><?= ucfirst($user['role']) ?></span></td>
                            <td><span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($user['status']) ?></span></td>
                            <td><?= date('F j, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <!-- Edit Button -->
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

                                <!-- Activate/Deactivate -->
                                <?php if ($user['status'] === 'active'): ?>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" action="deactivate_user.php" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Deactivate User">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <form method="POST" action="activate_user.php" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Activate User">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'modals/edit_user_modal.php'; ?>
<?php include 'modals/delete_user_modal.php'; ?>
<?php include 'modals/add_user_modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="js/dashboard.js"></script>
<script src="js/user.js"></script>

<script>
$(document).ready(function() {
    $('#userTable').DataTable();
});
</script>

</body>
</html>
