<?php
require_once '../connect.php';
session_start();

// Access control: Only logged-in super_admin can access
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$role = $_SESSION['role'] ?? '';
if ($role !== 'super_admin') {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Forbidden</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />';
    echo '</head><body class="p-4"><div class="alert alert-danger"><strong>403:</strong> You do not have permission to access this page.</div>';
    echo '<a class="btn btn-primary" href="system_admin_dashboard.php">Back to Dashboard</a></body></html>';
    exit();
}

// Optionally ensure table exists (safe no-op if already created)
$conn->query("CREATE TABLE IF NOT EXISTS tag_formats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_type VARCHAR(50) NOT NULL,
    format_code VARCHAR(100) NOT NULL UNIQUE,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle form submissions
$message = null; $message_type = 'info';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $tag_type = trim($_POST['tag_type'] ?? '');
        $format_code = trim($_POST['format_code'] ?? '');
        $created_by = (int)($_SESSION['user_id'] ?? 0);

        // Basic validation
        $valid_types = ['Red Tag', 'Property Tag'];
        if (!in_array($tag_type, $valid_types, true)) {
            $message = 'Invalid tag type.'; $message_type = 'danger';
        } elseif ($format_code === '') {
            $message = 'Format code is required.'; $message_type = 'danger';
        } elseif (!preg_match('/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/', $format_code)) {
            $message = 'Format must use uppercase letters, numbers, and hyphens only (e.g., PS-5S-03-F01-01-06).';
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare('INSERT INTO tag_formats (tag_type, format_code, created_by) VALUES (?, ?, ?)');
            $stmt->bind_param('ssi', $tag_type, $format_code, $created_by);
            if ($stmt->execute()) {
                $message = 'Tag format saved successfully.'; $message_type = 'success';
            } else {
                if ($conn->errno == 1062) {
                    $message = 'A tag format with this code already exists.'; $message_type = 'warning';
                } else {
                    $message = 'Failed to save: ' . htmlspecialchars($conn->error);
                    $message_type = 'danger';
                }
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare('DELETE FROM tag_formats WHERE id = ?');
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $message = 'Tag format deleted.'; $message_type = 'success';
            } else {
                $message = 'Delete failed: ' . htmlspecialchars($conn->error);
                $message_type = 'danger';
            }
            $stmt->close();
        } else {
            $message = 'Invalid record selected.'; $message_type = 'danger';
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $tag_type = trim($_POST['tag_type'] ?? '');
        $format_code = trim($_POST['format_code'] ?? '');
        $valid_types = ['Red Tag', 'Property Tag'];
        if ($id <= 0) {
            $message = 'Invalid record selected.'; $message_type = 'danger';
        } elseif (!in_array($tag_type, $valid_types, true)) {
            $message = 'Invalid tag type.'; $message_type = 'danger';
        } elseif ($format_code === '') {
            $message = 'Format code is required.'; $message_type = 'danger';
        } elseif (!preg_match('/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/', $format_code)) {
            $message = 'Format must use uppercase letters, numbers, and hyphens only (e.g., PS-5S-03-F01-01-06).';
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare('UPDATE tag_formats SET tag_type = ?, format_code = ? WHERE id = ?');
            $stmt->bind_param('ssi', $tag_type, $format_code, $id);
            if ($stmt->execute()) {
                $message = 'Tag format updated successfully.'; $message_type = 'success';
            } else {
                if ($conn->errno == 1062) {
                    $message = 'A tag format with this code already exists.'; $message_type = 'warning';
                } else {
                    $message = 'Update failed: ' . htmlspecialchars($conn->error);
                    $message_type = 'danger';
                }
            }
            $stmt->close();
        }
    }
}

// Fetch existing tag formats
$formats = [];
$sql = "SELECT tf.id, tf.tag_type, tf.format_code, tf.created_at, u.username AS creator
        FROM tag_formats tf
        LEFT JOIN users u ON u.id = tf.created_by
        ORDER BY tf.created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) { $formats[] = $row; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tag Formats</title>
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
                    <h5 class="mb-0">Manage Tag Formats</h5>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-circle"></i> Add Tag Format
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <table id="formatsTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tag Type</th>
                                <th>Format Code</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($formats as $f): ?>
                                <tr>
                                    <td><?= htmlspecialchars($f['tag_type']) ?></td>
                                    <td><code><?= htmlspecialchars($f['format_code']) ?></code></td>
                                    <td><?= htmlspecialchars($f['creator'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($f['created_at']))) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary editBtn" 
                                            data-id="<?= (int)$f['id'] ?>"
                                            data-type="<?= htmlspecialchars($f['tag_type']) ?>"
                                            data-code="<?= htmlspecialchars($f['format_code']) ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= (int)$f['id'] ?>" data-code="<?= htmlspecialchars($f['format_code']) ?>">
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

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="create" />
                    <div class="modal-header">
                        <h5 class="modal-title">Add Tag Format</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tag Type</label>
                            <select name="tag_type" class="form-select" required>
                                <option value="">Select...</option>
                                <option value="Red Tag">Red Tag</option>
                                <option value="Property Tag">Property Tag</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Format Code</label>
                            <input type="text" name="format_code" class="form-control" placeholder="e.g., PS-5S-03-F01-01-06" required pattern="^[A-Z0-9]+(?:-[A-Z0-9]+)*$" />
                            <div class="form-text">Use uppercase letters, numbers, and hyphens only.</div>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" id="deleteId" />
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the tag format <strong id="deleteCode"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="id" id="editId" />
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Tag Format</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tag Type</label>
                            <select name="tag_type" id="editType" class="form-select" required>
                                <option value="Red Tag">Red Tag</option>
                                <option value="Property Tag">Property Tag</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Format Code</label>
                            <input type="text" name="format_code" id="editCode" class="form-control" required pattern="^[A-Z0-9]+(?:-[A-Z0-9]+)*$" />
                            <div class="form-text">Use uppercase letters, numbers, and hyphens only.</div>
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
        $(function() {
            $('#formatsTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 20, 50],
                order: [[3, 'desc']]
            });

            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const code = button.getAttribute('data-code');
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteCode').textContent = code;
            });

            // Edit modal
            $(document).on('click', '.editBtn', function() {
                const id = $(this).data('id');
                const type = $(this).data('type');
                const code = $(this).data('code');
                $('#editId').val(id);
                $('#editType').val(type);
                $('#editCode').val(code);
                $('#editModal').modal('show');
            });
        });
    </script>
</body>
</html>
