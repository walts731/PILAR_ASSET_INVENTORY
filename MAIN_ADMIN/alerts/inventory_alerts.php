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