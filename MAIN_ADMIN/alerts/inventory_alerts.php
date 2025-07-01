<?php if (isset($_GET['delete']) && $_GET['delete'] === 'success'): ?>
  <div class="alert alert-success">Consumable deleted and archived successfully!</div>
<?php endif; ?>

<?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
  <div class="alert alert-success">Consumable updated successfully!</div>
<?php endif; ?>

<?php if (isset($_GET['add']) && $_GET['add'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    Asset added successfully!
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    Asset updated successfully!
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<?php if (isset($_GET['category_deleted']) && $_GET['category_deleted'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    Category deleted successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<?php if (isset($_GET['category_added'])): ?>
  <?php if ($_GET['category_added'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      Category added successfully.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($_GET['category_added'] === 'duplicate'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      Category already exists.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($_GET['category_added'] === 'fail'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-x-circle-fill me-2"></i>
      Failed to add category. Please try again.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
<?php endif; ?>
