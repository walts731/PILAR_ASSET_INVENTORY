<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['office_name'];

    $stmt = $conn->prepare("UPDATE offices SET office_name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Office updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to update office. Please try again.";
        $_SESSION['message_type'] = "danger";
    }

    $stmt->close();
}

header("Location: manage_offices.php");
exit();
