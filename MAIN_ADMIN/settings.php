<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Load timeout from DB if not already in session
if (!isset($_SESSION['timeout_duration'])) {
    $stmt = $conn->prepare("SELECT session_timeout FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($session_timeout);
    if ($stmt->fetch()) {
        $_SESSION['timeout_duration'] = $session_timeout;
    } else {
        $_SESSION['timeout_duration'] = 1800; // fallback to 30 mins
    }
    $stmt->close();
}

// Session auto-logout logic
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $_SESSION['timeout_duration']) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

// Fetch office_id if not set
if (!isset($_SESSION['office_id'])) {
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id);
    if ($stmt->fetch()) {
        $_SESSION['office_id'] = $office_id;
    }
    $stmt->close();
}

// Handle session timeout setting
$setting_updated = false;
$error = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['session_timeout'])) {
    $timeout = (int) $_POST['session_timeout'];
    if ($timeout >= 60) {
        $_SESSION['timeout_duration'] = $timeout;
        $setting_updated = true;

        $stmt = $conn->prepare("UPDATE users SET session_timeout = ? WHERE id = ?");
        $stmt->bind_param("ii", $timeout, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $error = "Minimum allowed timeout is 1 minute.";
    }
}

// Handle auto report generation settings
$report_saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_frequency'])) {
    $frequency = $_POST['report_frequency'];
    $day_of_week = $_POST['day_of_week'] ?? null;
    $day_of_month = $_POST['day_of_month'] ?? null;

    $check = $conn->query("SELECT id FROM report_generation_settings LIMIT 1");
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE report_generation_settings SET frequency=?, day_of_week=?, day_of_month=?");
        $stmt->bind_param("ssi", $frequency, $day_of_week, $day_of_month);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO report_generation_settings (frequency, day_of_week, day_of_month) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $frequency, $day_of_week, $day_of_month);
        $stmt->execute();
        $stmt->close();
    }
    $report_saved = true;
}

// Fetch current report settings
$setting_result = $conn->query("SELECT * FROM report_generation_settings LIMIT 1");
$report_setting = $setting_result->fetch_assoc() ?? [
    'frequency' => 'weekly',
    'day_of_week' => 'Monday',
    'day_of_month' => 1
];

// Get user's full name
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css" />
    <style>
        .save-btn {
            background-color: rgb(44, 110, 215);
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .save-btn:hover {
            background-color: rgb(9, 96, 184);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/topbar.php'; ?>
        <div class="container mt-4">
            <!-- Session Timeout Card -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear-fill"></i> Session Timeout Settings</h5>
                </div>
                <div class="card-body">
                    <?php if ($setting_updated): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Timeout updated successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="session_timeout" class="form-label">Auto Logout Time</label>
                            <select name="session_timeout" id="session_timeout" class="form-select">
                                <option value="60" <?= $_SESSION['timeout_duration'] == 60 ? 'selected' : '' ?>>1 Minute</option>
                                <option value="600" <?= $_SESSION['timeout_duration'] == 600 ? 'selected' : '' ?>>10 Minutes</option>
                                <option value="1800" <?= $_SESSION['timeout_duration'] == 1800 ? 'selected' : '' ?>>30 Minutes</option>
                                <option value="3600" <?= $_SESSION['timeout_duration'] == 3600 ? 'selected' : '' ?>>1 Hour</option>
                            </select>
                        </div>
                        <button type="submit" class="btn rounded-pill save-btn"><i class="bi bi-save"></i> Save</button>
                    </form>
                    <div class="mt-4 text-muted">
                        Current timeout: <?= $_SESSION['timeout_duration'] / 60 ?> minutes<br>
                        Last activity: <?= date('M j, Y h:i A', $_SESSION['last_activity']) ?>
                    </div>
                </div>
            </div>

           
        </div>
    </div>

    <!-- JS scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        function toggleScheduleFields() {
            const freq = document.getElementById('report_frequency').value;
            document.getElementById('weeklyField').style.display = freq === 'weekly' ? 'block' : 'none';
            document.getElementById('monthlyField').style.display = freq === 'monthly' ? 'block' : 'none';
        }
        toggleScheduleFields();
    </script>
</body>

</html>