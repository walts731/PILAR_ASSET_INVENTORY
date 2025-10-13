<!-- Import Infrastructure CSV Modal -->
<div class="modal fade" id="importInfrastructureCSVModal" tabindex="-1" aria-labelledby="importInfrastructureCSVModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="import_infrastructure_csv.php" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importInfrastructureCSVModalLabel">Import Infrastructure via CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="infrastructureCsvFile" class="form-label">Choose CSV File</label>
                    <input type="file" class="form-control" id="infrastructureCsvFile" name="csv_file" accept=".csv,.xlsx" required>
                </div>

                <div class="mb-3">
                    <strong>CSV/Excel Format Instructions:</strong>
                    <p class="mb-1">Your file must include the headers below (case-insensitive).</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-nowrap small align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>classification_type</th>
                                    <th>item_description</th>
                                    <th>location</th>
                                    <th>nature_occupancy</th>
                                    <th>property_no_or_reference</th>
                                    <th>acquisition_cost</th>
                                    <th>market_appraisal_insurable_interest</th>
                                    <th>date_constructed_acquired_manufactured</th>
                                    <th>date_of_appraisal</th>
                                    <th>remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Building</td>
                                    <td>School Building A</td>
                                    <td>Poblacion, Pilar</td>
                                    <td>schools</td>
                                    <td>PROP-INFRA-001</td>
                                    <td>5000000.00</td>
                                    <td>5500000.00</td>
                                    <td>2020-01-15</td>
                                    <td>2024-01-15</td>
                                    <td>Well-maintained school building</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <em class="text-muted">
                        <strong>Validation Rules:</strong><br>
                        1) <code>classification_type</code>, <code>item_description</code>, and <code>location</code> are required fields.<br>
                        2) <code>acquisition_cost</code> and <code>market_appraisal_insurable_interest</code> should be numeric values (optional).<br>
                        3) <code>date_constructed_acquired_manufactured</code> and <code>date_of_appraisal</code> should be in YYYY-MM-DD format (optional).<br>
                        4) Column headers must be present in the first row of your file.<br>
                        5) Each row creates one infrastructure record in the system.
                    </em>
                </div>
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
