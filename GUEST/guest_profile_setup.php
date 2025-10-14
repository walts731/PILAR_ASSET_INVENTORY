<?php
require_once '../connect.php';
session_start();

// Check if user is a guest and has a guest_id
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true || !isset($_SESSION['guest_id'])) {
    header("Location: ../index.php");
    exit();
}

$guest_id = $_SESSION['guest_id'];

// Fetch system settings for logo
$system = ['logo' => '../img/default-logo.png', 'system_title' => 'Inventory System'];
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');

    $errors = [];

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        // Check if email already exists for another guest
        $stmt = $conn->prepare("SELECT id FROM guests WHERE email = ? AND guest_id != ? AND email != 'guest@pilar.gov.ph'");
        $stmt->bind_param("ss", $email, $guest_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "This email is already registered. Please use a different email.";
        }
        $stmt->close();
    }

    // Validate name
    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    // Validate contact
    if (empty($contact)) {
        $errors[] = "Contact number is required.";
    }

    // Validate barangay
    if (empty($barangay)) {
        $errors[] = "Barangay is required.";
    }

    if (empty($errors)) {
        // Update guest record
        $update_stmt = $conn->prepare("UPDATE guests SET email = ?, name = ?, contact = ?, barangay = ?, first_login = NOW() WHERE guest_id = ?");
        $update_stmt->bind_param("sssss", $email, $name, $contact, $barangay, $guest_id);

        if ($update_stmt->execute()) {
            // Update session
            $_SESSION['guest_email'] = $email;
            $_SESSION['guest_name'] = $name;
            $_SESSION['guest_contact'] = $contact;
            $_SESSION['guest_barangay'] = $barangay;

            // Redirect to dashboard
            header("Location: guest_dashboard.php");
            exit();
        } else {
            $errors[] = "Failed to update your information. Please try again.";
        }

        $update_stmt->close();
    }
}

// Fetch current guest data
$guest_data = null;
$stmt = $conn->prepare("SELECT name, email, contact, barangay FROM guests WHERE guest_id = ?");
$stmt->bind_param("s", $guest_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $guest_data = $result->fetch_assoc();
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - <?= htmlspecialchars($system['system_title']) ?></title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }

        .card-header {
            background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
            color: white;
            text-align: center;
            padding: 2rem 1rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-control:focus {
            border-color: #0b5ed7;
            box-shadow: 0 0 0 0.2rem rgba(11, 94, 215, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
            border: none;
            padding: 0.75rem 2rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0a58ca 0%, #0948a6 100%);
        }

        .required-asterisk {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="profile-card">
                    <div class="card-header">
                        <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" class="rounded-circle mb-3" style="width: 60px; height: 60px; object-fit: cover;">
                        <h4 class="mb-0">Complete Your Profile</h4>
                        <p class="mb-0 opacity-75">Please provide your information to continue</p>
                    </div>

                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email Address <span class="required-asterisk">*</span>
                                </label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value=""
                                       placeholder="Enter your email address" required>
                                <div class="form-text">We'll use this to send you updates about your requests</div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Full Name <span class="required-asterisk">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlspecialchars($guest_data['name'] ?? '') ?>"
                                       placeholder="Enter your full name" required>
                            </div>

                            <div class="mb-3">
                                <label for="contact" class="form-label">
                                    Contact Number <span class="required-asterisk">*</span>
                                </label>
                                <input type="tel" class="form-control" id="contact" name="contact"
                                       value="<?= htmlspecialchars($guest_data['contact'] ?? '') ?>"
                                       placeholder="Enter your contact number" required>
                            </div>

                            <div class="mb-4">
                                <label for="barangay" class="form-label">
                                    Barangay <span class="required-asterisk">*</span>
                                </label>
                                <input type="text" class="form-control" id="barangay" name="barangay"
                                       value="<?= htmlspecialchars($guest_data['barangay'] ?? '') ?>"
                                       placeholder="Enter your barangay" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Complete Profile
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="../logout.php" class="text-muted small">
                                <i class="bi bi-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
