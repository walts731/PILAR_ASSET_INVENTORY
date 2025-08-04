<!-- Import CSV Modal -->
<div class="modal fade" id="importCSVModal" tabindex="-1" aria-labelledby="importCSVModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="import_csv.php" method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importCSVModalLabel">Import Assets via CSV</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="csvFile" class="form-label">Choose CSV File</label>
          <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv" required>
        </div>

        <div class="mb-3">
          <strong>CSV Format Instructions:</strong>
          <p class="mb-1">Your CSV file must follow the format below:</p>

          <div class="table-responsive">
            <table class="table table-bordered table-sm text-nowrap small align-middle">
              <thead class="table-light">
                <tr>
                  <th>description</th>
                  <th>category_name</th>
                  <th>quantity</th>
                  <th>unit</th>
                  <th>value</th>
                  <th>office_name</th>
                  <th>type</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Electric Drill</td>
                  <td>Tools</td>
                  <td>5</td>
                  <td>pcs</td>
                  <td>1200.50</td>
                  <td>Maintenance Department</td>
                  <td>Asset</td>
                </tr>
                <tr>
                  <td>Laptop</td>
                  <td>Office Equipment</td>
                  <td>10</td>
                  <td>pcs</td>
                  <td>45000.00</td>
                  <td>IT Department</td>
                  <td>Asset</td>
                </tr>
                <tr>
                  <td>Ballpen Black</td>
                  <td>Consumables</td>
                  <td>100</td>
                  <td>pcs</td>
                  <td>10.50</td>
                  <td>Admin Office</td>
                  <td>Consumable</td>
                </tr>
              </tbody>
            </table>
          </div>

          <em class="text-muted">
            <strong>Note:</strong> <code>status</code> is automatically set to <code>Available</code>.<br>
            Column headers must be present in the first row of your CSV.
          </em>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Import</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
