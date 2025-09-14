<!-- Duplicate Names Modal -->
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="duplicateModalLabel">Duplicate Employees Found</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>The following employees were <strong>not imported</strong> because they already exist:</p>
        <ul id="duplicateList"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("import") === "duplicates") {
        let names = urlParams.get("names").split(",");
        let list = document.getElementById("duplicateList");
        list.innerHTML = "";
        names.forEach(n => {
            let li = document.createElement("li");
            li.textContent = n;
            list.appendChild(li);
        });
        var modal = new bootstrap.Modal(document.getElementById("duplicateModal"));
        modal.show();
    }
});
</script>
