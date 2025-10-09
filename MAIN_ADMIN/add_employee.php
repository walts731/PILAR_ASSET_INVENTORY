<?php
require_once '../connect.php';
require_once '../includes/email_helper.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $status = trim($_POST['status']);
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $office_id = intval($_POST['office_id']);

    // --- 0. Check for duplicate name ---
    $check = $conn->prepare("SELECT COUNT(*) FROM employees WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    $check->bind_result($exists);
    $check->fetch();
    $check->close();

    if ($exists > 0) {
        $_SESSION['duplicate_name'] = $name; // store duplicate name for modal
        header("Location: employees.php");
        exit();
    }

    // --- 1. Generate new employee number ---
    $result = $conn->query("SELECT employee_no FROM employees ORDER BY employee_id DESC LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        $lastNo = intval(substr($row['employee_no'], 3));
        $newNo = $lastNo + 1;
    } else {
        $newNo = 1;
    }
    $employee_no = "EMP" . str_pad($newNo, 4, "0", STR_PAD_LEFT);

    // --- 2. Handle image upload ---
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "../img/";
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid("emp_") . "." . strtolower($ext);
        $uploadPath = $uploadDir . $imageName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($ext), $allowedTypes) || !move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $_SESSION['error'] = "Invalid image or upload failed.";
            header("Location: employees.php");
            exit();
        }
    }

    // --- 3. Insert into database ---
    // Check if 'email' column exists
    $hasEmailCol = false;
    if ($rs = $conn->query("SHOW COLUMNS FROM employees LIKE 'email'")) {
        $hasEmailCol = $rs->num_rows > 0;
        $rs->close();
    }

    if ($hasEmailCol) {
        $stmt = $conn->prepare("INSERT INTO employees (employee_no, name, email, status, date_added, image, office_id) 
                                VALUES (?, ?, ?, ?, NOW(), ?, ?)");
        $stmt->bind_param("sssssi", $employee_no, $name, $email, $status, $imageName, $office_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO employees (employee_no, name, status, date_added, image, office_id) 
                                VALUES (?, ?, ?, NOW(), ?, ?)");
        $stmt->bind_param("ssssi", $employee_no, $name, $status, $imageName, $office_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Employee added successfully!";
        // Attempt to send a welcome email if an email address was provided and column exists
        if ($hasEmailCol && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $mail = configurePHPMailer();
                $mail->addAddress($email, (string)$name);
                $mail->isHTML(true);
                $mail->Subject = 'Welcome to PILAR Asset Inventory';
                $mail->Body =
                    "Hello " . htmlspecialchars((string)$name) . ",<br><br>" .
                    "You have been added to the PILAR Asset Inventory system.<br>" .
                    "<ul>" .
                    "<li><strong>Employee No.:</strong> " . htmlspecialchars((string)$employee_no) . "</li>" .
                    "<li><strong>Status:</strong> " . htmlspecialchars((string)$status) . "</li>" .
                    "</ul>" .
                    "If you believe this was in error, please contact the system administrator.";
                $mail->AltBody = strip_tags(str_replace(['<br>','<br/>','<br />'], "\n", $mail->Body));
                $mail->send();
            } catch (Throwable $e) {
                // Do not fail the request if email sending fails; optionally log
                error_log('Add employee email send failed for ' . $email . ': ' . $e->getMessage());
            }
        }
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    $stmt->close();

    header("Location: employees.php");
    exit();
}
?>
