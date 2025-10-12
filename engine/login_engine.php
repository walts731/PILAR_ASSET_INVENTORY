<?php
require_once __DIR__ . '/../includes/audit_helper.php';
require_once __DIR__ . '/../includes/remember_me_helper.php';

$login_error = ""; // Initialize error message

// Check for remember me token if user is not logged in
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $user_data = validateRememberToken($conn, $token);
    
    if ($user_data) {
        // Auto-login user
        $_SESSION['user_id'] = $user_data['user_id'];
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['role'] = $user_data['role'];
        $_SESSION['office_id'] = $user_data['office_id'];
        
        // Log automatic login
        logAuthActivity('AUTO_LOGIN', "User '{$user_data['username']}' auto-logged in via remember token (Role: {$user_data['role']})", $user_data['user_id'], $user_data['username']);
        
        // Redirect based on role
        switch ($user_data['role']) {
            case "super_admin":
                header("Location: SYSTEM_ADMIN/system_admin_dashboard.php?office=" . $user_data['office_id']);
                break;
            case "office_admin":
                header("Location: OFFICE_ADMIN/admin_dashboard.php?office=" . $user_data['office_id']);
                break;
            case "admin":
                // Check if this user has a specific permission to only access Fuel Inventory
                $fuel_only = false;
                if ($permStmt = $conn->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = 'fuel_inventory' LIMIT 1")) {
                    $permStmt->bind_param('i', $user_data['user_id']);
                    $permStmt->execute();
                    $permStmt->store_result();
                    $fuel_only = $permStmt->num_rows > 0;
                    $permStmt->close();
                }
                if ($fuel_only) {
                    header("Location: MAIN_ADMIN/fuel_inventory.php");
                } else {
                    header("Location: MAIN_ADMIN/admin_dashboard.php?office=" . $user_data['office_id']);
                }
                break;
            case "user":
                header("Location: MAIN_USER/user_dashboard.php?office=" . $user_data['office_id']);
                break;
            case "office_user":
                header("Location: USERS/user_dashboard.php?office=" . $user_data['office_id']);
                break;
            default:
                header("Location: MAIN_USER/user_dashboard.php?office=" . $user_data['office_id']);
                break;
        }
        exit;
    } else {
        // Invalid token, clear cookie
        clearRememberCookie();
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';

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

                // Handle remember me functionality
                if ($remember_me) {
                    $token = createRememberToken($conn, $user['id']);
                    if ($token) {
                        setRememberCookie($token);
                    }
                }
                
                // Log successful login
                $login_method = $remember_me ? 'LOGIN_WITH_REMEMBER' : 'LOGIN';
                logAuthActivity($login_method, "User '{$username}' logged in successfully (Role: {$user['role']})", $user["id"], $username);

                // Redirect based on role, with special handling for 'user' + fuel inventory permission
                switch ($user["role"]) {
                    case "super_admin":
                        header("Location: SYSTEM_ADMIN/system_admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "office_admin":
                        header("Location: OFFICE_ADMIN/admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "admin":
                        // Check if this user has a specific permission to only access Fuel Inventory
                        $fuel_only = false;
                        if ($permStmt = $conn->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = 'fuel_inventory' LIMIT 1")) {
                            $permStmt->bind_param('i', $user["id"]);
                            $permStmt->execute();
                            $permStmt->store_result();
                            $fuel_only = $permStmt->num_rows > 0;
                            $permStmt->close();
                        }
                        if ($fuel_only) {
                            header("Location: MAIN_ADMIN/fuel_inventory.php");
                        } else {
                            header("Location: MAIN_ADMIN/admin_dashboard.php?office=" . $user["office_id"]);
                        }
                        break;
                    case "user":
                        header("Location: MAIN_USER/user_dashboard.php?office=" . $user["office_id"]);
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
                // Log failed login attempt (wrong password)
                logAuthActivity('LOGIN_FAILED', "Failed login attempt for username '{$username}' - incorrect password", null, $username);
                
                $login_error = '<div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">'
                    . '<i class="bi bi-x-circle me-2"></i>Invalid credentials. Please try again.'
                    . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                    . '</div>';
            }
        } else {
            // Log failed login attempt (user not found)
            logAuthActivity('LOGIN_FAILED', "Failed login attempt for username '{$username}' - user not found", null, $username);
            
            $login_error = '<div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">'
                . '<i class="bi bi-x-circle me-2"></i>Invalid credentials. Please try again.'
                . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
            . '</div>';
        }

        $stmt->close();
    }
}

$conn->close();
?>