<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="add_infrastructure.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Infrastructure Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Classification/Type</label>
                            <input type="text" name="classification_type" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Item Description</label>
                            <input type="text" name="item_description" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nature Occupancy</label>
                            <input type="text" name="nature_occupancy" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date Constructed/Acquired/Manufactured</label>
                            <input type="date" name="date_constructed_acquired_manufactured" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Property No./Other Reference</label>
                            <input type="text" name="property_no_or_reference" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Acquisition Cost</label>
                            <input type="number" step="0.01" name="acquisition_cost" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Market/Appraisal Value</label>
                            <input type="number" step="0.01" name="market_appraisal_insurable_interest" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Appraisal</label>
                            <input type="date" name="date_of_appraisal" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control">
                        </div>

                        <!-- Separate image inputs -->
                        <div class="col-md-4">
                            <label class="form-label">Image 1</label>
                            <input type="file" name="image_1" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Image 2</label>
                            <input type="file" name="image_2" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Image 3</label>
                            <input type="file" name="image_3" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Image 4</label>
                            <input type="file" name="image_4" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="save_inventory" class="btn btn-info">
                        <i class="bi bi-save"></i> Save
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>