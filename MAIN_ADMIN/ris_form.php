<h4 class="text-center mb-4 fw-bold text-uppercase">Requisition and Issue Slip (RIS)</h4>

              <form>
                <!-- Row 1: Division, Responsibility Center, RIS No., Date -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="division" class="form-label fw-semibold">Division</label>
                    <input type="text" class="form-control" id="division" name="division" value="<?= htmlspecialchars($ris_data['division'] ?? '') ?>">
                  </div>
                  <div class="col-md-3">
                    <label for="responsibility_center" class="form-label fw-semibold">Responsibility Center</label>
                    <input type="text" class="form-control" id="responsibility_center" name="responsibility_center" value="<?= htmlspecialchars($ris_data['responsibility_center'] ?? '') ?>">
                  </div>
                  <div class="col-md-3">
                    <label for="ris_no" class="form-label fw-semibold">RIS No.</label>
                    <input type="text" class="form-control" id="ris_no" name="ris_no" value="<?= htmlspecialchars($ris_data['ris_no'] ?? '') ?>">
                  </div>
                  <div class="col-md-3">
                    <label for="date" class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($ris_data['date'] ?? '') ?>">
                  </div>
                </div>

                <!-- Row 2: Office, Responsibility Code, SAI No., Empty -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="office" class="form-label fw-semibold">Office/Unit</label>
                    <select class="form-select" id="office" name="office" required>
                      <option value="" disabled <?= !isset($ris_data['office']) ? 'selected' : '' ?>>Select Office</option>
                      <?php
                      $office_query = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
                      while ($row = $office_query->fetch_assoc()):
                        $selected = (isset($ris_data['office']) && $ris_data['office'] == $row['id']) ? 'selected' : '';
                      ?>
                        <option value="<?= $row['id'] ?>" <?= $selected ?>><?= htmlspecialchars($row['office_name']) ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="responsibility_code" class="form-label fw-semibold">Code</label>
                    <input type="text" class="form-control" id="responsibility_code" name="responsibility_code" value="<?= htmlspecialchars($ris_data['responsibility_code'] ?? '') ?>">
                  </div>
                  <div class="col-md-3">
                    <label for="sai_no" class="form-label fw-semibold">SAI No.</label>
                    <input type="text" class="form-control" id="sai_no" name="sai_no" value="<?= htmlspecialchars($ris_data['sai_no'] ?? '') ?>">
                  </div>
                  <div class="col-md-3">
                    <label for="date" class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($ris_data['date'] ?? '') ?>">
                  </div>
                </div>

                <table class="table table-bordered align-middle text-center">
                  <thead>
                    <tr class="table-secondary">
                      <th colspan="4">REQUISITION</th>
                      <th colspan="2">ISSUANCE</th>
                    </tr>
                    <tr class="table-light">
                      <th>Stock No</th>
                      <th>Unit</th>
                      <th>Description</th>
                      <th>Quantity</th>
                      <th>Quantity</th>
                      <th>Signature</th>
                      <th>Price</th>
                      <th>Total Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                      <tr>
                        <td><input type="text" class="form-control" name="stock_no[]"></td>
                        <td>
                          <select name="unit[]" class="form-select" required>
                            <option value="" disabled selected>Select Unit</option>
                            <?php
                            $unit_query = $conn->query("SELECT id, unit_name FROM unit ORDER BY unit_name ASC");
                            while ($row = $unit_query->fetch_assoc()):
                            ?>
                              <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['unit_name']) ?></option>
                            <?php endwhile; ?>
                          </select>
                        </td>
                        <td style="position: relative;">
                          <input type="text" class="form-control description-input" name="description[]" autocomplete="off">
                          <div class="autocomplete-suggestions"></div>
                        </td>
                        <td><input type="number" class="form-control" name="req_quantity[]"></td>
                        <td><input type="number" class="form-control" name="iss_quantity[]"></td>
                        <td><input type="text" class="form-control" name="signature[]"></td>
                        <td><input type="number" step="0.01" class="form-control" name="price[]"></td>
                        <td><input type="number" step="0.01" class="form-control" name="total_amount[]"></td>
                      </tr>
                    <?php endfor; ?>
                  </tbody>
                </table>

                <!-- Reason for Transfer -->
                <div class="mb-3">
                  <label for="reason_for_transfer" class="form-label">Reason for Transfer:</label>
                  <textarea class="form-control" name="reason_for_transfer" id="reason_for_transfer"><?= htmlspecialchars($ris_data['reason_for_transfer'] ?? '') ?></textarea>
                </div>

                <!-- RIS Footer Layout -->
                <hr>
                <div class="row text-center fw-bold mb-2">
                  <div class="col">Approved by:</div>
                  <div class="col">Released/Issued by:</div>
                  <div class="col">Received by:</div>
                </div>

                <div class="row text-center mb-1">
                  <div class="col">
                    <label class="form-label">Signature:</label>
                    <div class="form-control border-0 bg-light" style="height: 40px;"></div>
                  </div>
                  <div class="col">
                    <label class="form-label">Signature:</label>
                    <div class="form-control border-0 bg-light" style="height: 40px;"></div>
                  </div>
                  <div class="col">
                    <label class="form-label">Signature:</label>
                    <div class="form-control border-0 bg-light" style="height: 40px;"></div>
                  </div>
                </div>

                <div class="row text-center mb-1">
                  <div class="col">
                    <input type="text" class="form-control" name="approved_by_name" placeholder="Printed Name" value="<?= htmlspecialchars($ris_data['approved_by_name'] ?? '') ?>">
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="released_by_name" placeholder="Printed Name" value="<?= htmlspecialchars($ris_data['released_by_name'] ?? '') ?>">
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="received_by_name" placeholder="Printed Name" value="<?= htmlspecialchars($ris_data['received_by_name'] ?? '') ?>">
                  </div>
                </div>

                <div class="row text-center mb-1">
                  <div class="col">
                    <input type="text" class="form-control" name="approved_by_designation" placeholder="Designation" value="<?= htmlspecialchars($ris_data['approved_by_designation'] ?? '') ?>">
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="released_by_designation" placeholder="Designation" value="<?= htmlspecialchars($ris_data['released_by_designation'] ?? '') ?>">
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="received_by_designation" placeholder="Designation" value="<?= htmlspecialchars($ris_data['received_by_designation'] ?? '') ?>">
                  </div>
                </div>

                <div class="row mt-3 mb-4">
                  <div class="col-4">
                    <label for="footer_date" class="form-label">Date:</label>
                    <input type="date" class="form-control" name="footer_date" value="<?= htmlspecialchars($ris_data['footer_date'] ?? '') ?>">
                  </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-send-check-fill"></i> Submit RIS
                </button>
              </form>