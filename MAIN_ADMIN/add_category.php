<?php
require_once '../connect.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_name = trim($_POST['category_name']);

    // Check if category already exists (case-insensitive)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(category_name) = LOWER(?)");
    $stmt->bind_param("s", $category_name);
    $stmt->execute();
    $stmt->bind_result($existing);
    $stmt->fetch();
    $stmt->close();

    if ($existing > 0) {
        header("Location: inventory.php?category_added=duplicate");
        exit();
    }

    // If not duplicate, insert new category
    $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $category_name);
    if ($stmt->execute()) {
        header("Location: inventory.php?category_added=success");
        exit();
    } else {
        header("Location: inventory.php?category_added=fail");
        exit();
    }
}
?>
