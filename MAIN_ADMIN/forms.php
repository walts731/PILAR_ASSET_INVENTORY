<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Set office_id if not set
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

// Get form ID from URL
$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$form = null;
$ris_data = null;

if ($form_id > 0) {
  // Fetch form metadata
  $stmt = $conn->prepare("SELECT id, form_title, category, file_path, created_at FROM forms WHERE id = ?");
  $stmt->bind_param("i", $form_id);
  $stmt->execute();
  $stmt->bind_result($id, $form_title, $category, $file_path, $created_at);
  if ($stmt->fetch()) {
    $form = [
      'id' => $id,
      'form_title' => $form_title,
      'category' => $category,
      'file_path' => $file_path,
      'created_at' => $created_at
    ];
  }
  $stmt->close();

  // Fetch RIS data if category is ris
  if ($form && $form['category'] === 'ris') {
    $stmt = $conn->prepare("SELECT `id`, `form_id`, `office_id`, `header_image`, `division`, `responsibility_center`, `ris_no`, `sai_no`, `date`, `approved_by_name`, `approved_by_designation`, `released_by_name`, `released_by_designation`, `received_by_name`, `received_by_designation`, `footer_date`, `reason_for_transfer`, `created_at` FROM `ris_form` WHERE form_id = ?");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ris_data = $result->fetch_assoc();
    $stmt->close();
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $form ? htmlspecialchars($form['form_title']) : 'Form Viewer' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    .autocomplete-suggestions {
      background-color: #fff;
      position: absolute;
      z-index: 999;
      max-height: 300px;
      overflow-y: auto;
      width: 100%;
      font-size: 18px;
      /* Bigger font */
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      /* Add shadow */
      border-radius: 4px;
    }

    .autocomplete-suggestion {
      padding: 12px 15px;
      /* Bigger padding */
      cursor: pointer;
    }

    .autocomplete-suggestion:hover {
      background-color: #f2f2f2;
      font-weight: bold;
      /* Emphasize on hover */
      color: #333;
    }
  </style>

</head>

<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container mt-4">
      <?php if ($form): ?>
        <h2 class="mb-4"><?= htmlspecialchars($form['form_title']) ?></h2>
        <div class="card shadow">
          <div class="card-body">
            <?php if ($form['category'] === 'SIR'): ?>
              <?php include 'ris_form.php'; ?>
            <?php endif; ?>
            <?php if ($form['category'] === 'ICS'): ?>
              <?php include 'ics_form.php'; ?>
            <?php endif; ?>
            <?php if ($form['category'] === 'PAR'): ?>
              <?php include 'par_form.php'; ?>
            <?php endif; ?>
            <?php if ($form['category'] === 'IIRUP'): ?>
              <?php include 'iirup_form.php'; ?>
            <?php endif; ?>
            <?php if ($form['category'] === 'RPCPPE'): ?>
              <?php include 'rpcppe_form.php'; ?>
            <?php endif; ?>
            <?php if ($form['category'] === 'RIS'): ?>
              <?php include 'ris_form.php'; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">
          Form not found. Please select a valid form from the sidebar.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
  <script>
    $(document).on('input', '.description-input', function() {
      const input = $(this);
      const query = input.val().trim();
      const suggestionBox = input.siblings('.autocomplete-suggestions');

      if (query.length >= 1) {
        $.ajax({
          url: 'fetch_descriptions.php',
          method: 'POST',
          data: {
            search: query
          },
          success: function(data) {
            suggestionBox.html(data).show();
          }
        });
      } else {
        suggestionBox.hide();
      }
    });

    $(document).on('click', '.autocomplete-suggestion', function() {
      const selected = $(this).text();
      const input = $(this).closest('td').find('.description-input');
      input.val(selected);
      $(this).parent().hide();
    });

    
  </script>

</body>

</html>