<?php
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
                        header("Location: SYSTEM_ADMIN/system_admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "office_admin":
                        header("Location: OFFICE_ADMIN/admin_dashboard.php?office=" . $user["office_id"]);
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