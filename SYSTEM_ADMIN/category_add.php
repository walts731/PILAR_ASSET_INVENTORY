<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name'] ?? '');
    $code = trim($_POST['category_code'] ?? '');

    if (!empty($name) && !empty($code)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name, category_code, status) VALUES (?, ?, 1)");
        $stmt->bind_param("ss", $name, $code);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Category added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding category.";
            $_SESSION['message_type'] = "danger";
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "Category name and code are required.";
        $_SESSION['message_type'] = "warning";
    }
}

header("Location: manage_categories.php");
exit();
