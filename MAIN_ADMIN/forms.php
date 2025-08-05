<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Set office_id if not already set
if (!isset($_SESSION['office_id'])) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT office_id FROM users WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($office_id);
  if ($stmt->fetch()) {
    $_SESSION['office_id'] = $office_id;
  }
  $stmt->close();
}

// Fetch user's full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();

// Get selected category ID from GET
$category_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch category name for display
$category_name = '';
$stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$stmt->bind_result($category_name);
$stmt->fetch();
$stmt->close();

// Fetch forms by category ID
$forms = [];
if ($category_id > 0) {
  $stmt = $conn->prepare("SELECT id, form_title, category, file_path, created_at FROM forms WHERE category = ?");
  $stmt->bind_param("i", $category_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $forms[] = $row;
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forms - <?= htmlspecialchars($category_name) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid px-4 py-4">
      <h4 class="mb-4">
        <i class="bi bi-file-earmark-text"></i>
        Forms - <?= htmlspecialchars($category_name) ?>
      </h4>

      <?php if (!empty($forms)) : ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped" id="formsTable">
            <thead class="table-light">
              <tr>
                <th>Form Title</th>
                <th>File</th>
                <th>Date Created</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($forms as $form): ?>
                <tr>
                  <td><?= htmlspecialchars($form['form_title']) ?></td>
                  <td>
                    <a href="../uploads/forms/<?= htmlspecialchars($form['file_path']) ?>" target="_blank" class="btn btn-sm btn-primary">
                      <i class="bi bi-download"></i> Download
                    </a>
                  </td>
                  <td><?= date("F j, Y", strtotime($form['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else : ?>
        <div class="alert alert-info">No forms available in this category.</div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#formsTable').DataTable();
    });
  </script>
</body>

</html>
