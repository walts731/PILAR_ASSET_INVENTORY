<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['office_name'];

  $stmt = $conn->prepare("INSERT INTO offices (office_name) VALUES (?)");
  $stmt->bind_param("s", $name);
  $stmt->execute();
  $stmt->close();
}

header("Location: manage_offices.php");
exit();
?>
