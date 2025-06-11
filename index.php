<?php
session_start();
require_once "connect.php"; // Include database connection

$login_error = ""; // Initialize error message

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        $login_error = '<div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
            Please fill in all fields.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("
            SELECT id, username, password, role, office_id 
            FROM users 
            WHERE username = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user["password"])) {
                // Store session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["office_id"] = $user["office_id"];

                // Redirect based on role
                switch ($user["role"]) {
                    case "super_admin":
                        header("Location: OMPDC/system_admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "office_admin":
                        header("Location: ADMIN/admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "admin":
                        header("Location: MAIN_ADMIN/admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "office_user":
                        header("Location: USERS/user_dashboard.php?office=" . $user["office_id"]);
                        break;
                    default:
                        header("Location: MAIN_USER/user_dashboard.php?office=" . $user["office_id"]);
                        break;
                }
                exit;
            } else {
                $login_error = '<div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                    Invalid Credentials.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        } else {
            $login_error = '<div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                Invalid Credentials.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Pilar Inventory Management System</title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/login.css" />
</head>

<body class="bg-light d-flex flex-column justify-content-center align-items-center min-vh-100 p-3 login_body">

    <!-- Login Card -->
    <div class="card shadow-lg p-4 rounded w-100" style="max-width: 400px;">
        <div class="card-body text-center">
            <h3 class="fw-bold mb-3">LOGIN</h3>
            <img src="img/PILAR LOGO TRANSPARENT.png" alt="Website Logo" class="img-fluid mb-3" style="max-width: 100px;" />

            <!-- Bootstrap Alert -->
            <?php if (!empty($login_error)) echo $login_error; ?>

            <form method="post" autocomplete="off">
                <!-- Hidden Fields to Disable Autofill -->
                <input type="text" name="fakeusernameremembered" style="display:none">
                <input type="password" name="fakepasswordremembered" style="display:none">

                <!-- Username -->
                <div class="mb-3 text-start">
                    <label for="username" class="fw-bold">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" autocomplete="off" required />
                </div>

                <!-- Password -->
                <div class="mb-3 text-start">
                    <label for="password" class="fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" placeholder="Enter your password" required />
                        <span class="input-group-text bg-white border border-start-0 p-0" id="togglePassword" style="cursor: pointer; width: 40px; display: flex; justify-content: center; align-items: center;">
                            <i class="bi bi-eye" id="eyeIcon" style="font-size: 1rem;"></i>
                        </span>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary w-100">Login</button>

                <!-- Forgot Password -->
                <div class="mt-3">
                    <a href="forgot_password.php" class="text-decoration-none text-primary">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toggle Password JS -->
    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace("bi-eye-slash", "bi-eye");
            }
        });
    </script>
</body>

</html>
