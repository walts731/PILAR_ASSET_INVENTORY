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

<!-- CSV Import Messages -->
<?php if (isset($_GET['import'])): ?>
  <?php if ($_GET['import'] === 'success'): ?>
    <?php 
      $success_count = isset($_GET['ok']) ? (int)$_GET['ok'] : 0;
      $failed_count = isset($_GET['fail']) ? (int)$_GET['fail'] : 0;
    ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      <strong>Import Successful!</strong> 
      <?= $success_count ?> asset<?= $success_count !== 1 ? 's' : '' ?> imported successfully.
      <?php if ($failed_count > 0): ?>
        <br><small class="text-muted"><?= $failed_count ?> row<?= $failed_count !== 1 ? 's' : '' ?> were skipped due to errors.</small>
      <?php endif; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($_GET['import'] === 'partial'): ?>
    <?php 
      $success_count = isset($_GET['ok']) ? (int)$_GET['ok'] : 0;
      $failed_count = isset($_GET['fail']) ? (int)$_GET['fail'] : 0;
      $error_details = isset($_GET['errors']) ? htmlspecialchars(urldecode($_GET['errors'])) : '';
    ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <strong>Partial Import Success!</strong> 
      <?= $success_count ?> asset<?= $success_count !== 1 ? 's' : '' ?> imported successfully, but <?= $failed_count ?> row<?= $failed_count !== 1 ? 's' : '' ?> failed.
      <?php if ($error_details): ?>
        <br><small class="text-muted"><strong>Errors:</strong> <?= $error_details ?></small>
      <?php endif; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($_GET['import'] === 'failed'): ?>
    <?php 
      $failed_count = isset($_GET['fail']) ? (int)$_GET['fail'] : 0;
      $error_details = isset($_GET['errors']) ? htmlspecialchars(urldecode($_GET['errors'])) : '';
    ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-x-circle-fill me-2"></i>
      <strong>Import Failed!</strong> 
      No assets were imported. <?= $failed_count ?> row<?= $failed_count !== 1 ? 's' : '' ?> failed to process.
      <?php if ($error_details): ?>
        <br><small><strong>Errors:</strong> <?= $error_details ?></small>
      <?php endif; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($_GET['import'] === 'error'): ?>
    <?php $error_message = isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Unknown error occurred.'; ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-x-circle-fill me-2"></i>
      <strong>Import Error!</strong> 
      <?= $error_message ?>
      <br><small class="text-muted">Please check your file format and try again.</small>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
<?php endif; ?>



