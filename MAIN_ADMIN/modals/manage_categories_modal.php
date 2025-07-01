<!-- Manage Categories Modal -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-labelledby="manageCategoriesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="manageCategoriesLabel"><i class="bi bi-tags"></i> Manage Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#categoryList" type="button" role="tab">Category List</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#addCategory" type="button" role="tab">Add Category</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content pt-3">
                    <!-- Category List Tab -->
                    <div class="tab-pane fade show active" id="categoryList" role="tabpanel">
                        <table class="table table-bordered table-hover" style="border-radius: 0.5rem; overflow: hidden;">
                            <thead class="text-center" style="background-color: #e0f0ff; border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem;">

                                <tr>
                                    <th>Category Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $conn->prepare("SELECT id, category_name FROM categories");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $i = 1;
                                while ($cat = $result->fetch_assoc()):
                                    $cat_id = $cat['id'];

                                    // Check if category is used
                                    $check = $conn->prepare("SELECT COUNT(*) FROM assets WHERE category = ?");
                                    $check->bind_param("i", $cat_id);
                                    $check->execute();
                                    $check->bind_result($used_count);
                                    $check->fetch();
                                    $check->close();
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cat['category_name']) ?></td>
                                        <td>
                                            <?php if ($used_count == 0): ?>
                                                <form method="POST" action="delete_category.php" class="d-inline">
                                                    <input type="hidden" name="category_id" value="<?= $cat_id ?>">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmDeleteCategoryModal"
                                                        data-id="<?= $cat_id ?>"
                                                        data-name="<?= htmlspecialchars($cat['category_name']) ?>">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="bi bi-lock"></i> In Use</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Add Category Tab -->
                    <div class="tab-pane fade" id="addCategory" role="tabpanel">
                        <form method="POST" action="add_category.php">
                            <div class="mb-3">
                                <label for="newCategory" class="form-label">Category Name</label>
                                <input type="text" class="form-control" name="category_name" id="newCategory" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn rounded-pill text-white"
                                    style="background-color: #0dcaf0; border: none;"
                                    onmouseover="this.style.backgroundColor='#31d2f2'"
                                    onmouseout="this.style.backgroundColor='#0dcaf0'">
                                    <i class="bi bi-plus-circle"></i> Add Category
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Confirm Delete Category Modal -->
<div class="modal fade" id="confirmDeleteCategoryModal" tabindex="-1" aria-labelledby="confirmDeleteCategoryLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="delete_category.php" class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteCategoryLabel"><i class="bi bi-exclamation-triangle"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category <strong id="categoryNameText"></strong>?</p>
                <input type="hidden" name="category_id" id="confirmCategoryId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger rounded-pill">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    const deleteModal = document.getElementById('confirmDeleteCategoryModal');
    deleteModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const catId = button.getAttribute('data-id');
        const catName = button.getAttribute('data-name');

        document.getElementById('confirmCategoryId').value = catId;
        document.getElementById('categoryNameText').textContent = catName;
    });

    setTimeout(() => {
        const alert = document.querySelector('.alert.alert-success');
        if (alert) {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }
    }, 4000); // Hide after 4 seconds
</script>