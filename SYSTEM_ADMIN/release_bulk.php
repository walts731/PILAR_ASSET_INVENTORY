<?php
require_once '../connect.php';
session_start();

if (!isset($_GET['ids']) || !isset($_GET['office'])) {
  header("Location: inventory.php?office=" . ($_GET['office'] ?? ''));
  exit();
}

$ids = explode(',', $_GET['ids']);
$office_id = intval($_GET['office']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmt = $conn->prepare("UPDATE assets SET status = 'available', last_updated = NOW() WHERE id IN ($placeholders) AND office_id = ?");
$params = array_merge($ids, [$office_id]);
$stmt->bind_param($types . 'i', ...$params);
$stmt->execute();

header("Location: inventory.php?office=$office_id&bulk_release=success");
exit();
?>
