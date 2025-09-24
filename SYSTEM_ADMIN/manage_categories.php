<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header("Location: ../index.php");
    exit();
}

// Ensure new columns exist: category_code (VARCHAR) and status (TINYINT)
// Use INFORMATION_SCHEMA to avoid errors across MySQL/MariaDB versions
try {
    // category_code
    $colCheck = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'category_code'");
    $colCheck->execute();
    $colCheck->bind_result($hasCode);
    $colCheck->fetch();
    $colCheck->close();
    if (intval($hasCode) === 0) {
        $conn->query("ALTER TABLE categories ADD COLUMN category_code VARCHAR(50) NULL AFTER category_name");
    }

    // status
    $colCheck2 = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'status'");
    $colCheck2->execute();
    $colCheck2->bind_result($hasStatus);
    $colCheck2->fetch();
    $colCheck2->close();
    if (intval($hasStatus) === 0) {
        $conn->query("ALTER TABLE categories ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1 AFTER category_code");
    }
} catch (Throwable $e) {
    // Non-fatal; page should still render. Consider logging in audit table if available.
}

// Fetch all categories with asset counts
$categories = [];
$sql = "SELECT c.id, c.category_name, COALESCE(c.category_code,'') AS category_code, COALESCE(c.status,1) AS status,
               COUNT(a.id) AS asset_count
        FROM categories c
        LEFT JOIN assets a ON a.category = c.id
        GROUP BY c.id, c.category_name, c.category_code, c.status
        ORDER BY c.category_name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
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
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage Categories</h5>
                    <!-- Add Category Button -->
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-circle"></i> Add Category
                    </button>
                </div>
                <div class="card-body">
                    <!-- Bootstrap Alert -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                    <?php endif; ?>

                    <!-- Categories Table -->
                    <table id="categoriesTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Category Name</th>
                                <th class="text-center">Category Code</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Assets</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($category['category_name']) ?></td>
                                    <td class="text-center"><code><?= htmlspecialchars($category['category_code'] ?? '') ?></code></td>
                                    <td class="text-center">
                                        <?php if (intval($category['status']) === 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= $category['asset_count'] ?></td>
                                    <td class="text-center">
                                        <!-- Edit Button -->
                                        <button class="btn btn-sm btn-outline-primary editBtn"
                                            data-id="<?= $category['id'] ?>"
                                            data-name="<?= htmlspecialchars($category['category_name']) ?>"
                                            data-code="<?= htmlspecialchars($category['category_code'] ?? '') ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>

                                        <!-- Activate/Deactivate Button -->
                                        <form method="POST" action="category_status.php" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                            <input type="hidden" name="status" value="<?= intval($category['status']) === 1 ? 0 : 1 ?>">
                                            <?php if (intval($category['status']) === 1): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-slash-circle"></i> Deactivate
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-check-circle"></i> Activate
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- Delete Button -->
                                        <button class="btn btn-outline-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-id="<?= $category['id'] ?>"
                                            data-name="<?= htmlspecialchars($category['category_name']) ?>">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="category_delete.php">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the category <strong id="categoryName"></strong>?<br>
                        <small class="text-muted">If this category is in use, it will be marked Inactive instead of deleted.</small></p>
                        <input type="hidden" name="id" id="deleteCategoryId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="category_add.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category Code</label>
                            <input type="text" class="form-control" name="category_code" placeholder="e.g., ICT" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="category_edit.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editCategoryId">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="category_name" id="editCategoryName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category Code</label>
                            <input type="text" class="form-control" name="category_code" id="editCategoryCode" required>
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
            $('#categoriesTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 20, 50],
                order: [[0, 'asc']]
            });

            // Edit modal
            $(document).on('click', '.editBtn', function() {
                $('#editCategoryId').val($(this).data('id'));
                $('#editCategoryName').val($(this).data('name'));
                $('#editCategoryCode').val($(this).data('code'));
                $('#editModal').modal('show');
            });

            // Delete modal
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const categoryId = button.getAttribute('data-id');
                const categoryName = button.getAttribute('data-name');
                document.getElementById('deleteCategoryId').value = categoryId;
                document.getElementById('categoryName').textContent = categoryName;
            });
        });
    </script>
</body>
</html>
