<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);

    if (!empty($id)) {
        // Check if category is referenced by assets
        $stmt = $conn->prepare("SELECT COUNT(*) FROM assets WHERE category = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($asset_count);
        $stmt->fetch();
        $stmt->close();

        if ($asset_count > 0) {
            // Mark as inactive instead of deleting
            $upd = $conn->prepare("UPDATE categories SET status = 0 WHERE id = ?");
            $upd->bind_param("i", $id);
            if ($upd->execute()) {
                $_SESSION['message'] = "Category is in use, so it was deactivated instead of deleted.";
                $_SESSION['message_type'] = "warning";
            } else {
                $_SESSION['message'] = "Unable to deactivate category.";
                $_SESSION['message_type'] = "danger";
            }
            $upd->close();
        } else {
            // Try to hard delete; if FK constraint arises, fall back to deactivate
            $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Category deleted successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                // FK or other error; deactivate instead
                $upd = $conn->prepare("UPDATE categories SET status = 0 WHERE id = ?");
                $upd->bind_param("i", $id);
                if ($upd->execute()) {
                    $_SESSION['message'] = "Category could not be deleted due to references, so it was deactivated.";
                    $_SESSION['message_type'] = "warning";
                } else {
                    $_SESSION['message'] = "Error deleting or deactivating category.";
                    $_SESSION['message_type'] = "danger";
                }
                $upd->close();
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
