<?php
require_once '../connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $status = trim($_POST['status']);
    $office_id = intval($_POST['office_id']);

    // --- 1. Generate new employee number ---
    $result = $conn->query("SELECT employee_no FROM employees ORDER BY employee_id DESC LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        $lastNo = intval(substr($row['employee_no'], 3)); // remove "EMP"
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

        // Validate image type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($ext), $allowedTypes)) {
            $_SESSION['error'] = "Invalid image format. Only JPG, PNG, GIF allowed.";
            header("Location: employees.php");
            exit();
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: employees.php");
            exit();
        }
    }

    // --- 3. Insert into database ---
    $stmt = $conn->prepare("INSERT INTO employees (employee_no, name, status, date_added, image, office_id) 
                            VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param("ssssi", $employee_no, $name, $status, $imageName, $office_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Employee added successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    $stmt->close();

    // --- 4. Redirect back ---
    header("Location: employees.php");
    exit();
}
?>
