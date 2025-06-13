<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);

    if (empty($fullname) || empty($email)) {
        $_SESSION['error'] = "Full name and email cannot be empty.";
        header("Location: profile.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: profile.php");
        exit();
    }

    // Update user info
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $fullname, $email, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully.";
    } else {
        $_SESSION['error'] = "Something went wrong while updating your profile.";
    }

    $stmt->close();
}

header("Location: profile.php");
exit();
?>
