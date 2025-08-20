<div class="modal fade" id="importEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="import_employees.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header bg-light">
          <h5 class="modal-title"><i class="bi bi-upload"></i> Import Employees (CSV)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">
            Upload a CSV file with the following columns: 
            <strong>employee_no, name, office_id, status, image</strong>
          </p>
          <div class="mb-3">
            <label for="csvFile" class="form-label">Choose CSV File</label>
            <input type="file" class="form-control" name="csv_file" id="csvFile" accept=".csv" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="import" class="btn btn-primary"><i class="bi bi-check-circle"></i> Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
