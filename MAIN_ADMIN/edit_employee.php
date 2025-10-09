<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $employee_no = $_POST['employee_no'];
    $name = $_POST['name'];
    $office_id = $_POST['office_id'];
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $status = $_POST['status'];
    $image = null;

    // Handle image upload if new file is chosen
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "../img/";
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $fileName;
        }
    }

    // Detect if email column exists
    $hasEmailCol = false;
    if ($rs = $conn->query("SHOW COLUMNS FROM employees LIKE 'email'")) {
        $hasEmailCol = $rs->num_rows > 0;
        $rs->close();
    }

    if ($image) {
        if ($hasEmailCol) {
            $stmt = $conn->prepare("UPDATE employees SET employee_no=?, name=?, email=?, office_id=?, status=?, image=? WHERE employee_id=?");
            $stmt->bind_param("ssssisi", $employee_no, $name, $email, $office_id, $status, $image, $employee_id);
        } else {
            $stmt = $conn->prepare("UPDATE employees SET employee_no=?, name=?, office_id=?, status=?, image=? WHERE employee_id=?");
            $stmt->bind_param("ssissi", $employee_no, $name, $office_id, $status, $image, $employee_id);
        }
    } else {
        if ($hasEmailCol) {
            $stmt = $conn->prepare("UPDATE employees SET employee_no=?, name=?, email=?, office_id=?, status=? WHERE employee_id=?");
            $stmt->bind_param("ssssii", $employee_no, $name, $email, $office_id, $status, $employee_id);
        } else {
            $stmt = $conn->prepare("UPDATE employees SET employee_no=?, name=?, office_id=?, status=? WHERE employee_id=?");
            $stmt->bind_param("ssisi", $employee_no, $name, $office_id, $status, $employee_id);
        }
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Employee updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update employee.";
    }

    $stmt->close();
    header("Location: employees.php");
    exit();
}
?>
