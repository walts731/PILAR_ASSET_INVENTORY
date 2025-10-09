<div class="modal fade" id="importEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="import_employees.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header bg-light">
          <h5 class="modal-title"><i class="bi bi-upload"></i> Import Employees (CSV)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-2">
            Upload a CSV file with the following columns:
            <strong>name, office_name, status</strong> and optional <strong>email</strong> (4th column).
          </p>
          <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>name</th>
                  <th>office_name</th>
                  <th>status</th>
                  <th>email (optional)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Juan Dela Cruz</td>
                  <td>Accounting Office</td>
                  <td>permanent</td>
                  <td>juan.delacruz@example.com</td>
                </tr>
                <tr>
                  <td>Maria Clara</td>
                  <td>Mayor's Office</td>
                  <td>contractual</td>
                  <td>maria.clara@example.com</td>
                </tr>
                <tr>
                  <td>Pedro Santos</td>
                  <td>Engineering Office</td>
                  <td>resigned</td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
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


