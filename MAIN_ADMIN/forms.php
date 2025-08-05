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

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();

// Get form ID from URL
$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$form = null;

if ($form_id > 0) {
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

            <?php if ($form['category'] === 'ris'): ?>
              <!-- Static Requisition Inventory Slip layout -->
              <h4 class="text-center mb-3">REQUISITION AND ISSUE SLIP (RIS)</h4>
              <form>
                <div class="mb-3">
                  <label for="office" class="form-label">Office/Unit:</label>
                  <input type="text" class="form-control" id="office" name="office">
                </div>

                <div class="mb-3">
                  <label for="responsibility" class="form-label">Responsibility Center Code:</label>
                  <input type="text" class="form-control" id="responsibility" name="responsibility">
                </div>

                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Stock No.</th>
                      <th>Unit</th>
                      <th>Description</th>
                      <th>Quantity Requested</th>
                      <th>Quantity Issued</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                      <tr>
                        <td><input type="text" class="form-control" name="stock_no[]"></td>
                        <td><input type="text" class="form-control" name="unit[]"></td>
                        <td><input type="text" class="form-control" name="description[]"></td>
                        <td><input type="number" class="form-control" name="qty_requested[]"></td>
                        <td><input type="number" class="form-control" name="qty_issued[]"></td>
                      </tr>
                    <?php endfor; ?>
                  </tbody>
                </table>

                <div class="mb-3">
                  <label class="form-label">Purpose:</label>
                  <textarea class="form-control" name="purpose" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-success">
                  <i class="bi bi-send-check-fill"></i> Submit RIS
                </button>
              </form>

            <?php else: ?>
              <!-- Default form behavior for other categories -->
              <a class="btn btn-primary" href="../<?= htmlspecialchars($form['file_path']) ?>" target="_blank">
                <i class="bi bi-file-earmark-arrow-down"></i> Submit Form
              </a>
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
</body>

</html>
