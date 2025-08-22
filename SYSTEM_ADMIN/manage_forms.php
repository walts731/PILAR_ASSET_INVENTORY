<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch all forms
$forms = [];
$result = $conn->query("SELECT id, form_title FROM forms ORDER BY id ASC");
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $forms[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Form Management</title>
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
  <h2>Manage Forms</h2>

  <!-- Bootstrap Alert -->
  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <table id="formsTable" class="table table-bordered ">
    <thead class="">
      <tr>
        <th class="text-center">Form Title</th>
        <th class="text-center">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($forms as $form): ?>
        <tr>
          <td><?= htmlspecialchars($form['form_title']) ?></td>
          <td>
            <button class="btn btn-sm btn-primary editBtn"
                    data-id="<?= $form['id'] ?>"
                    data-title="<?= htmlspecialchars($form['form_title']) ?>">
              <i class="bi bi-pencil"></i> Edit
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editForm" method="POST" action="update_form.php">
        <div class="modal-header">
          <h5 class="modal-title">Edit Form</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="formId">
          <div class="mb-3">
            <label class="form-label">Form Title</label>
            <input type="text" class="form-control" name="form_title" id="formTitle" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function() {
    $('#formsTable').DataTable();

    // Open modal with form data
    $(document).on('click', '.editBtn', function() {
      $('#formId').val($(this).data('id'));
      $('#formTitle').val($(this).data('title'));
      $('#editModal').modal('show');
    });
  });
</script>
</body>
</html>
