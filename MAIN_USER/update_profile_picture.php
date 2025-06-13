<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $file = $_FILES['profile_picture'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed)) {
        $filename = "profile_" . $user_id . "_" . time() . "." . $ext;
        $targetPath = "../img/" . $filename;

        if (move_uploaded_file($file["tmp_name"], $targetPath)) {
            // Save filename to DB
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

header("Location: profile.php");
exit();
