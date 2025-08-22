<?php
require_once '../connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Set office_id if not set
if (!isset($_SESSION['office_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id);
    if ($stmt->fetch()) {
        $_SESSION['office_id'] = $office_id;
    }
    $stmt->close();
}

// Fetch full name
$stmt = $conn->prepare("SELECT fullname, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname, $role);
$stmt->fetch();
$stmt->close();

// Fetch current system details
$stmt = $conn->prepare("SELECT id, logo, system_title FROM system LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$system = $result->fetch_assoc();
$stmt->close();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $system_title = trim($_POST['system_title']);
    $logo = $system['logo']; // default old logo

    if (!empty($_FILES['logo']['name'])) {
        $target_dir = "../img/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES['logo']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            $logo = $file_name;
        }
    }

    $stmt = $conn->prepare("UPDATE system SET logo = ?, system_title = ? WHERE id = ?");
    $stmt->bind_param("ssi", $logo, $system_title, $system['id']);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_system.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <div class="container p-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Edit System Logo & Title</h5>
                </div>
                <div class="card-body">

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">System details updated successfully!</div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">System Title</label>
                            <input type="text" name="system_title" class="form-control"
                                value="<?= htmlspecialchars($system['system_title']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">System Logo</label><br>
                            <?php if (!empty($system['logo'])): ?>
                                <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" class="mb-2" style="max-height: 80px;">
                            <?php endif; ?>
                            <input type="file" name="logo" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>