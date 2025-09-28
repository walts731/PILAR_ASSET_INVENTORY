<?php
require_once 'connect.php';
require_once 'includes/audit_helper.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_data = null;

// Check if token is provided and valid
if (!empty($token)) {
    $stmt = $conn->prepare("SELECT id, username, fullname, reset_token_expiry FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() AND status = 'active'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $valid_token = true;
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $new_password)) {
        $error = 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.';
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $updateStmt->bind_param("si", $hashed_password, $user_data['id']);
        
        if ($updateStmt->execute()) {
            $success = 'Your password has been successfully reset. You can now login with your new password.';
            
            // Log successful password reset
            logAuthActivity('PASSWORD_RESET_COMPLETED', "Password successfully reset for user: {$user_data['username']}", $user_data['id'], $user_data['username']);
            
            // Clear user data to prevent form from showing again
            $valid_token = false;
        } else {
            $error = 'An error occurred while resetting your password. Please try again.';
            logErrorActivity('Password Reset', "Failed to update password for user: {$user_data['username']}");
        }
        $updateStmt->close();
    }
}

// Get system information for branding
$system_query = "SELECT system_title, logo FROM system LIMIT 1";
$system_result = $conn->query($system_query);
$system = $system_result ? $system_result->fetch_assoc() : ['system_title' => 'PILAR Asset Inventory', 'logo' => 'img/default-logo.png'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= htmlspecialchars($system['system_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <style>
        .password-strength {
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #198754; }
    </style>
</head>
<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4 rounded" style="max-width: 450px; width: 100%;">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="<?= htmlspecialchars($system['logo']) ?>" alt="Logo" class="mb-3" style="max-height: 80px;">
                <h3 class="fw-bold mb-0">Reset Password</h3>
                <div class="text-muted">Create a new secure password</div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <div class="text-center">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
                    </a>
                </div>
            <?php elseif (empty($token)): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Invalid Request</strong><br>
                    No reset token provided. Please use the link from your email.
                </div>
                <div class="text-center">
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            <?php elseif (!$valid_token): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-x-circle me-2"></i>
                    <strong>Invalid or Expired Token</strong><br>
                    This reset link is invalid or has expired. Please request a new password reset.
                </div>
                <div class="text-center">
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            <?php else: ?>
                <form method="post" id="resetForm">
                    <div class="mb-3">
                        <label class="form-label">Resetting password for:</label>
                        <div class="p-2 bg-light rounded">
                            <strong><?= htmlspecialchars($user_data['fullname']) ?></strong>
                            <small class="text-muted">(<?= htmlspecialchars($user_data['username']) ?>)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="new_password" id="new_password" class="form-control" 
                                   placeholder="Enter new password" required>
                            <span class="input-group-text bg-white border-start-0" id="toggleNewPassword" 
                                  style="cursor: pointer;" title="Show/Hide password">
                                <i class="bi bi-eye" id="eyeIconNew"></i>
                            </span>
                        </div>
                        <div class="password-strength mt-1" id="strengthBar"></div>
                        <small class="text-muted">
                            Must be at least 8 characters with uppercase, lowercase, number, and special character.
                        </small>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                                   placeholder="Confirm new password" required>
                            <span class="input-group-text bg-white border-start-0" id="toggleConfirmPassword" 
                                  style="cursor: pointer;" title="Show/Hide password">
                                <i class="bi bi-eye" id="eyeIconConfirm"></i>
                            </span>
                        </div>
                        <div id="passwordMatch" class="mt-1"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="resetBtn">
                        <i class="bi bi-check-circle me-2"></i>Reset Password
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="index.php" class="small text-muted">
                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Password visibility toggles
    document.getElementById('toggleNewPassword')?.addEventListener('click', function() {
        togglePasswordVisibility('new_password', 'eyeIconNew');
    });
    
    document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
        togglePasswordVisibility('confirm_password', 'eyeIconConfirm');
    });
    
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
    
    // Password strength checker
    document.getElementById('new_password')?.addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('strengthBar');
        const strength = checkPasswordStrength(password);
        
        strengthBar.className = 'password-strength ' + strength.class;
        strengthBar.style.width = strength.width;
    });
    
    // Password match checker
    document.getElementById('confirm_password')?.addEventListener('input', function() {
        const password = document.getElementById('new_password').value;
        const confirm = this.value;
        const matchDiv = document.getElementById('passwordMatch');
        
        if (confirm === '') {
            matchDiv.innerHTML = '';
        } else if (password === confirm) {
            matchDiv.innerHTML = '<small class="text-success"><i class="bi bi-check-circle me-1"></i>Passwords match</small>';
        } else {
            matchDiv.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords do not match</small>';
        }
    });
    
    function checkPasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^A-Za-z\d]/.test(password)) score++;
        
        if (score < 3) {
            return { class: 'strength-weak', width: '33%' };
        } else if (score < 5) {
            return { class: 'strength-medium', width: '66%' };
        } else {
            return { class: 'strength-strong', width: '100%' };
        }
    }
    
    // Form validation
    document.getElementById('resetForm')?.addEventListener('submit', function(e) {
        const password = document.getElementById('new_password').value;
        const confirm = document.getElementById('confirm_password').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return false;
        }
        
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/.test(password)) {
            e.preventDefault();
            alert('Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character!');
            return false;
        }
    });
    </script>
</body>
</html>
