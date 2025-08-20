<!-- Duplicate Name Modal -->
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="duplicateModalLabel">Duplicate Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Employee <strong id="duplicateName"></strong> already exists and was not added.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if(isset($_SESSION['duplicate_name'])): ?>
        var duplicateName = "<?php echo $_SESSION['duplicate_name']; ?>";
        document.getElementById("duplicateName").textContent = duplicateName;
        var modal = new bootstrap.Modal(document.getElementById("duplicateModal"));
        modal.show();
        <?php unset($_SESSION['duplicate_name']); ?>
    <?php endif; ?>
});
</script>