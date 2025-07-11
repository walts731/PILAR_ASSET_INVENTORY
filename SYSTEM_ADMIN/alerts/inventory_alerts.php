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
<?php if (isset($_GET['bulk']) && $_GET['bulk'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    Selected assets were successfully processed.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<div id="bulkActionAlert" class="alert alert-danger alert-dismissible fade d-none" role="alert">
  <span id="bulkActionAlertMsg">Please select at least one asset to perform this action.</span>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php if (isset($_GET['bulk_release']) && $_GET['bulk_release'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    Selected assets have been successfully released.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<?php if (isset($_GET['report']) && $_GET['report'] === 'none'): ?>
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <strong>No assets selected.</strong> Please select at least one asset to generate a report.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>



