<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['category_name']);

    if (!empty($id) && !empty($name)) {
        $stmt = $conn->prepare("UPDATE categories SET category_name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Category updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating category.";
            $_SESSION['message_type'] = "danger";
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "Invalid category data.";
        $_SESSION['message_type'] = "warning";
    }
}

header("Location: manage_categories.php");
exit();
