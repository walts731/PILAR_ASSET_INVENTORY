<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    if (!empty($id)) {
        // Double check if category has assets
        $stmt = $conn->prepare("SELECT COUNT(*) FROM assets WHERE category = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($asset_count);
        $stmt->fetch();
        $stmt->close();

        if ($asset_count > 0) {
            $_SESSION['message'] = "Cannot delete category. It still has assets.";
            $_SESSION['message_type'] = "warning";
        } else {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Category deleted successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting category.";
                $_SESSION['message_type'] = "danger";
            }

            $stmt->close();
        }
    } else {
        $_SESSION['message'] = "Invalid category ID.";
        $_SESSION['message_type'] = "warning";
    }
}

header("Location: manage_categories.php");
exit();
