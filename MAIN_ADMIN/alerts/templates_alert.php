<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>
<?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Template uploaded successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>