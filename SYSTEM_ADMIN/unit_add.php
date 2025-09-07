<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_name = trim($_POST['unit_name']);

    if (!empty($unit_name)) {
        $stmt = $conn->prepare("INSERT INTO unit (unit_name) VALUES (?)");
        $stmt->bind_param("s", $unit_name);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Unit added successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding unit: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Unit name cannot be empty.";
        $_SESSION['message_type'] = "warning";
    }
}

header("Location: manage_units.php");
exit();
?>
