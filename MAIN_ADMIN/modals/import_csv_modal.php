<!-- Import CSV Modal -->
<div class="modal fade" id="importCSVModal" tabindex="-1" aria-labelledby="importCSVModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="import_csv.php" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importCSVModalLabel">Import Assets via CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

                <div class="mb-3">
                    <label for="csvFile" class="form-label">Choose CSV File</label>
                    <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv,.xlsx" required>
                </div>

                <div class="mb-3">
                    <strong>CSV/Excel Format Instructions:</strong>
                    <p class="mb-1">Your file must include the headers below (case-insensitive). Items are created individually based on <code>quantity</code>.</p>

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
                                    <th>status</th>
                                    <th>acquisition_date</th>
                                    <th>employee_name</th>
                                    <th>end_user</th>
                                    <th>red_tagged</th>
                                    <th>serial_no</th>
                                    <th>code</th>
                                    <th>property_no</th>
                                    <th>model</th>
                                    <th>brand</th>
                                    <th>inventory_tag</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Laptop</td>
                                    <td>Office Equipment</td>
                                    <td>3</td>
                                    <td>unit</td>
                                    <td>45000</td>
                                    <td>IT Department</td>
                                    <td>asset</td>
                                    <td>available</td>
                                    <td>2025-09-01</td>
                                    <td>Juan Dela Cruz</td>
                                    <td>Jane Smith</td>
                                    <td>0</td>
                                    <td>SN-ABC123</td>
                                    <td>CODE-01</td>
                                    <td>PROP-123</td>
                                    <td>XPS 15</td>
                                    <td>Dell</td>
                                    <td>INV-0001</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <em class="text-muted">
                        <strong>Validation Rules:</strong><br>
                        1) <code>employee_name</code> must match an existing name in <code>employees</code> table exactly, or import will fail for that row.<br>
                        2) <code>office_name</code> must match an existing office name exactly, or import will fail for that row.<br>
                        3) <code>serial_no</code>, <code>code</code>, <code>property_no</code>, and <code>inventory_tag</code> must be unique across all assets.<br>
                        4) <code>red_tagged</code> accepts 1/0, yes/no, true/false.<br>
                        5) Each row creates a parent entry in <code>assets_new</code> and then individual items in <code>assets</code> (quantity = 1 each).<br>
                        6) Column headers must be present in the first row of your file.
                    </em>
                </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-upload me-1"></i> Import
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>