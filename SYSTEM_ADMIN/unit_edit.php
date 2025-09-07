<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $unit_name = trim($_POST['unit_name']);

    if ($id > 0 && !empty($unit_name)) {
        $stmt = $conn->prepare("UPDATE unit SET unit_name = ? WHERE id = ?");
        $stmt->bind_param("si", $unit_name, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Unit updated successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating unit: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Invalid data provided.";
        $_SESSION['message_type'] = "warning";
    }
}

header("Location: manage_units.php");
exit();
?>
