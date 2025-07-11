<?php
require_once '../connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if template ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid template ID.";
    header("Location: templates.php");
    exit();
}

$templateId = (int) $_GET['id'];

// Optional: Add permission check here (e.g., only the creator or admin can delete)

// Prepare and execute deletion
$stmt = $conn->prepare("DELETE FROM report_templates WHERE id = ?");
$stmt->bind_param("i", $templateId);

if ($stmt->execute()) {
    $_SESSION['message'] = "Template deleted successfully.";
} else {
    $_SESSION['message'] = "Failed to delete the template.";
}

$stmt->close();
$conn->close();

header("Location: templates.php"); // Redirect back to template list page
exit();
