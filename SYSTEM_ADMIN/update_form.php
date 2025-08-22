<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $form_title = trim($_POST['form_title']);

  if ($id > 0 && $form_title !== '') {
    $stmt = $conn->prepare("UPDATE forms SET form_title = ? WHERE id = ?");
    $stmt->bind_param("si", $form_title, $id);
    if ($stmt->execute()) {
      $_SESSION['message'] = "Form updated successfully!";
    } else {
      $_SESSION['message'] = "Error updating form.";
    }
    $stmt->close();
  }
}

header("Location: manage_forms.php");
exit();
