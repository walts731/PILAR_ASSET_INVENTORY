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

// Fetch system_info details
$systemInfo = null;
$stmt = $conn->prepare("SELECT id, system_name, description, developer_name, developer_email, version, credits, created_at FROM system_info LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$systemInfo = $result->fetch_assoc();
$stmt->close();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? 'system_branding';

    if ($form_type === 'system_branding') {
        $system_title = trim($_POST['system_title'] ?? '');
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
    } elseif ($form_type === 'system_info') {
        $si_id            = intval($_POST['si_id'] ?? 0);
        $si_system_name   = trim($_POST['system_name'] ?? '');
        $si_description   = trim($_POST['description'] ?? '');
        $si_dev_name      = trim($_POST['developer_name'] ?? '');
        $si_dev_email     = trim($_POST['developer_email'] ?? '');
        $si_version       = trim($_POST['version'] ?? '');
        $si_credits       = trim($_POST['credits'] ?? '');
        $si_created_input = trim($_POST['created_at'] ?? '');

        // Normalize created_at to Y-m-d H:i:s
        $si_created_at = null;
        if ($si_created_input !== '') {
            $si_created_input = str_replace('T', ' ', $si_created_input);
            $ts = strtotime($si_created_input);
            if ($ts !== false) {
                $si_created_at = date('Y-m-d H:i:s', $ts);
            }
        }

        // Note: developer_email is now free-form (textarea). Skip strict email validation to allow multi-line input.

        // Default to existing id if not provided
        if ($si_id === 0 && isset($systemInfo['id'])) {
            $si_id = (int)$systemInfo['id'];
        }

        if ($si_id > 0) {
            $stmt = $conn->prepare("UPDATE system_info SET system_name = ?, description = ?, developer_name = ?, developer_email = ?, version = ?, credits = ?, created_at = ? WHERE id = ?");
            $stmt->bind_param('sssssssi', $si_system_name, $si_description, $si_dev_name, $si_dev_email, $si_version, $si_credits, $si_created_at, $si_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO system_info (system_name, description, developer_name, developer_email, version, credits, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $si_system_name, $si_description, $si_dev_name, $si_dev_email, $si_version, $si_credits, $si_created_at);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: edit_system.php?success_info=1");
        exit();
    }
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
                    <?php if (isset($_GET['success_info'])): ?>
                        <div class="alert alert-success">System info updated successfully!</div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_email'): ?>
                        <div class="alert alert-danger">Developer email is invalid.</div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="system_branding">
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
            
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Edit System Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="form_type" value="system_info">
                        <input type="hidden" name="si_id" value="<?= isset($systemInfo['id']) ? (int)$systemInfo['id'] : 0 ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">System Name</label>
                                <input type="text" name="system_name" class="form-control" value="<?= htmlspecialchars($systemInfo['system_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Version</label>
                                <input type="text" name="version" class="form-control" value="<?= htmlspecialchars($systemInfo['version'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($systemInfo['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Developer Name</label>
                                <textarea name="developer_name" class="form-control" rows="2"><?= htmlspecialchars($systemInfo['developer_name'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Developer Email</label>
                                <textarea name="developer_email" class="form-control" rows="2" placeholder="Enter one per line or free-form text"><?= htmlspecialchars($systemInfo['developer_email'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Credits</label>
                                <textarea name="credits" class="form-control" rows="3"><?= htmlspecialchars($systemInfo['credits'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Created At</label>
                                <input type="datetime-local" name="created_at" class="form-control" value="<?= isset($systemInfo['created_at']) && $systemInfo['created_at'] ? date('Y-m-d\TH:i', strtotime($systemInfo['created_at'])) : '' ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save System Info</button>
                        </div>
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