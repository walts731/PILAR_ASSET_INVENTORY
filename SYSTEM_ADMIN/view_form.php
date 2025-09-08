<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$form = null;

if ($form_id > 0) {
  $stmt = $conn->prepare("SELECT id, form_title, category, created_at FROM forms WHERE id = ?");
  $stmt->bind_param("i", $form_id);
  $stmt->execute();
  $stmt->bind_result($id, $form_title, $category, $created_at);
  if ($stmt->fetch()) {
    $form = [
      'id' => $id,
      'form_title' => $form_title,
      'category' => $category,
      'created_at' => $created_at
    ];
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= $form ? htmlspecialchars($form['form_title']) : 'View Form' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container mt-4">
      <?php if ($form): ?>
        <h2><?= htmlspecialchars($form['form_title']) ?> <small class="text-muted">(<?= htmlspecialchars($form['category']) ?>)</small></h2>
        <div class="card shadow mt-3">
          <div class="card-body">
            <?php
            $category = strtolower($form['category']); // e.g., ICS → ics_form.php
            $file = $category . "_form.php";

            if (file_exists($file)) {
              include $file;
            } else {
              echo "<div class='alert alert-danger'>No template found for category: " . htmlspecialchars($form['category']) . "</div>";
            }
            ?>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">Form not found.</div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
</body>

</html>