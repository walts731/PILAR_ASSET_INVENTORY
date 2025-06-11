<?php
require_once "connect.php"; // Database connection

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify if the token exists and is not expired
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = trim($_POST["password"]);
            $confirm_password = trim($_POST["confirm_password"]);

            if (empty($new_password) || empty($confirm_password)) {
                $error = "Please enter your new password.";
            } elseif ($new_password !== $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (!preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $new_password)) {
                $error = "Password must be at least 8 characters long, contain 1 uppercase letter, 1 number, and 1 special character.";
            } else {
                // Hash the new password for security
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Update password and remove reset token
                $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
                $stmt->bind_param("ss", $hashed_password, $token);
                $stmt->execute();

                // Redirect to login page
                header("Location: index.php?reset=success");
                exit;
            }
        }
    } else {
        $error = "Invalid or expired reset token.";
    }
} else {
    $error = "No reset token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/login.css">
    <title>Reset Password</title>
</head>
<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100">

    <div class="card shadow-lg p-4 rounded" style="max-width: 400px; width: 100%;">
        <div class="card-body text-center">
            <h3 class="fw-bold mb-3">Reset Password</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <!-- New Password Field with Eye Icon -->
                <div class="mb-3 text-start">
                    <label for="password" class="fw-bold">New Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required placeholder="Enter new password">
                        <span class="input-group-text bg-white border border-start-0 p-0" id="togglePassword" style="cursor: pointer; width: 40px; display: flex; justify-content: center; align-items: center;">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                    <small class="text-muted">Must be 8+ chars, 1 uppercase, 1 number, 1 special character.</small>
                    <div id="passwordError" class="text-danger small"></div>
                </div>

                <!-- Confirm Password Field -->
                <div class="mb-3 text-start">
                    <label for="confirm_password" class="fw-bold">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Confirm new password">
                    <div id="confirmPasswordError" class="text-danger small"></div>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>Reset Password</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap Icons (Required for Eye Icon) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- JavaScript for Password Validation & Toggle -->
    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("bi-eye");
                eyeIcon.classList.add("bi-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("bi-eye-slash");
                eyeIcon.classList.add("bi-eye");
            }
        });

        // Password Validation
        document.getElementById("password").addEventListener("input", function () {
            const password = this.value;
            const errorDiv = document.getElementById("passwordError");
            const submitBtn = document.getElementById("submitBtn");
            const confirmPasswordField = document.getElementById("confirm_password");

            const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!passwordRegex.test(password)) {
                errorDiv.textContent = "Invalid password format.";
                submitBtn.disabled = true;
            } else {
                errorDiv.textContent = "";
                if (confirmPasswordField.value === password) {
                    submitBtn.disabled = false;
                }
            }
        });

        // Confirm Password Validation
        document.getElementById("confirm_password").addEventListener("input", function () {
            const password = document.getElementById("password").value;
            const confirmPassword = this.value;
            const errorDiv = document.getElementById("confirmPasswordError");
            const submitBtn = document.getElementById("submitBtn");

            if (confirmPassword !== password) {
                errorDiv.textContent = "Passwords do not match.";
                submitBtn.disabled = true;
            } else {
                errorDiv.textContent = "";
                if (document.getElementById("passwordError").textContent === "") {
                    submitBtn.disabled = false;
                }
            }
        });
    </script>

</body>
</html>
