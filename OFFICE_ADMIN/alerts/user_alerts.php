<?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
      <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        User updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['delete']) && $_GET['delete'] === 'success'): ?>
      <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> User deleted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php elseif (isset($_GET['delete']) && $_GET['delete'] === 'locked'): ?>
      <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-lock-fill me-2"></i> This user cannot be deleted because it's referenced in other records.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php elseif (isset($_GET['delete']) && $_GET['delete'] === 'error'): ?>
      <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-x-circle-fill me-2"></i> An error occurred while deleting the user.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['office_add']) && $_GET['office_add'] === 'success'): ?>
      <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> New office created successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php elseif (isset($_GET['office_add']) && $_GET['office_add'] === 'error'): ?>
      <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-x-circle-fill me-2"></i> Failed to create office. Try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php elseif (isset($_GET['office_add']) && $_GET['office_add'] === 'empty'): ?>
      <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Office name cannot be empty.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['user_add']) && $_GET['user_add'] === 'success'): ?>
      <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> New user added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php elseif (isset($_GET['user_add']) && $_GET['user_add'] === 'error'): ?>
      <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-x-circle-fill me-2"></i> Failed to add user. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php elseif (isset($_GET['user_add']) && $_GET['user_add'] === 'empty'): ?>
      <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Please fill in all required fields.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['user_add']) && $_GET['user_add'] === 'duplicate'): ?>
      <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i> Username already exists. Choose another one.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php elseif (isset($_GET['user_add']) && $_GET['user_add'] === 'weak_password'): ?>
      <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Password must be at least 8 characters long and include at least 1 number, 1 uppercase and 1 lowercase letter.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['office_add']) && $_GET['office_add'] === 'duplicate'): ?>
      <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> This office already exists.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['deactivate']) && $_GET['deactivate'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    User has been successfully deactivated.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php elseif (isset($_GET['deactivate']) && $_GET['deactivate'] === 'error'): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    Failed to deactivate the user. Please try again.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php elseif (isset($_GET['deactivate']) && $_GET['deactivate'] === 'forbidden'): ?>
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    Admin accounts cannot be deactivated.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<?php if (isset($_GET['activate']) && $_GET['activate'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    User has been successfully reactivated.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php elseif (isset($_GET['activate']) && $_GET['activate'] === 'error'): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    Failed to reactivate the user. Please try again.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
