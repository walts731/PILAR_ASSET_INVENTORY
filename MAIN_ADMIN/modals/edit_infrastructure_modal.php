<div class="modal fade add-infrastructure-modal" id="editInventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="edit_infrastructure.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="inventory_id" id="edit_inventory_id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Infrastructure Inventory
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Classification/Type</label>
                            <input type="text" name="classification_type" id="edit_classification_type" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Item Description</label>
                            <input type="text" name="item_description" id="edit_item_description" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nature Occupancy</label>
                            <input type="text" name="nature_occupancy" id="edit_nature_occupancy" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" id="edit_location" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date Constructed</label>
                            <input type="date" name="date_constructed_acquired_manufactured" id="edit_date_constructed_acquired_manufactured" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Property No./Other Reference</label>
                            <input type="text" name="property_no_or_reference" id="edit_property_no_or_reference" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Acquisition Cost</label>
                            <input type="number" step="0.01" name="acquisition_cost" id="edit_acquisition_cost" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Market/Appraisal Value</label>
                            <input type="number" step="0.01" name="market_appraisal_insurable_interest" id="edit_market_appraisal_insurable_interest" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Appraisal</label>
                            <input type="date" name="date_of_appraisal" id="edit_date_of_appraisal" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" id="edit_remarks" class="form-control">
                        </div>

                        <!-- Existing images display -->
                        <div class="col-12">
                            <label class="form-label">Current Images</label>
                            <div id="currentImagesContainer" class="row g-2">
                                <!-- Current images will be loaded here -->
                            </div>
                        </div>

                        <!-- Additional image upload with preview (full width) -->
                        <div class="col-12">
                            <label class="form-label">Replace/Add Additional Images (max 4 total)</label>
                            <div class="image-upload-area" id="editImageUploadArea">
                                <i class="bi bi-cloud-upload display-4 text-muted mb-2"></i>
                                <p class="mb-2">Click to upload or drag and drop</p>
                                <small class="text-muted">JPG, PNG, GIF up to 10MB each (max 4 total images)</small>
                                <input type="file" name="additional_images[]" id="editAdditionalImages" class="d-none" accept="image/*" multiple>
                            </div>

                            <!-- Image Preview Area -->
                            <div id="editImagePreviewContainer" class="row g-2 mt-3" style="display: none;">
                                <div class="col-12">
                                    <small class="text-muted">New Images to Add:</small>
                                </div>
                                <!-- Previews will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="update_inventory" class="btn btn-info">
                        <i class="bi bi-save"></i> Update
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Enhanced modal animations for edit modal
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editInventoryModal');

    // Add show class animation
    editModal.addEventListener('show.bs.modal', function() {
        setTimeout(() => {
            editModal.classList.add('showing');
        }, 50);
    });

    editModal.addEventListener('shown.bs.modal', function() {
        editModal.classList.remove('showing');
    });

    editModal.addEventListener('hide.bs.modal', function() {
        editModal.classList.add('hiding');
    });

    editModal.addEventListener('hidden.bs.modal', function() {
        editModal.classList.remove('hiding');
    });

    // Enhanced image upload functionality for edit modal
    const editImageUploadArea = document.getElementById('editImageUploadArea');
    const editAdditionalImagesInput = document.getElementById('editAdditionalImages');
    const editImagePreviewContainer = document.getElementById('editImagePreviewContainer');
    const maxImages = 4;
    let editSelectedFiles = [];

    // Click to upload
    editImageUploadArea.addEventListener('click', function() {
        editAdditionalImagesInput.click();
    });

    // File selection
    editAdditionalImagesInput.addEventListener('change', function(e) {
        handleEditFiles(e.target.files);
    });

    // Drag and drop
    editImageUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        editImageUploadArea.classList.add('dragover');
    });

    editImageUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        editImageUploadArea.classList.remove('dragover');
    });

    editImageUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        editImageUploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        handleEditFiles(files);
    });

    function handleEditFiles(files) {
        // Check total files after adding new ones (considering existing images)
        const currentImageCount = document.querySelectorAll('#currentImagesContainer .current-image-item').length;
        if (editSelectedFiles.length + files.length + currentImageCount > maxImages) {
            alert(`You can only have a maximum of ${maxImages} images total. You currently have ${currentImageCount} existing images.`);
            return;
        }

        // Validate file types and sizes
        for (let file of files) {
            if (!file.type.startsWith('image/')) {
                alert(`${file.name} is not an image file.`);
                return;
            }
            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert(`${file.name} is too large. Maximum size is 10MB.`);
                return;
            }
        }

        // Add files to selectedFiles array
        for (let file of files) {
            editSelectedFiles.push(file);
        }

        // Update input and previews
        updateEditFileInput();
        updateEditPreviews();
    }

    function updateEditFileInput() {
        // Create a new DataTransfer object to update the input
        const dt = new DataTransfer();
        editSelectedFiles.forEach(file => dt.items.add(file));
        editAdditionalImagesInput.files = dt.files;
    }

    function updateEditPreviews() {
        // Clear existing previews
        const previewRow = editImagePreviewContainer.querySelector('.row.g-2');
        if (!previewRow) {
            // Create the preview row if it doesn't exist
            const newRow = document.createElement('div');
            newRow.className = 'row g-2 mt-2';
            editImagePreviewContainer.appendChild(newRow);
        }

        // Clear all child elements except the "New Images" label
        const children = Array.from(editImagePreviewContainer.children);
        children.forEach(child => {
            if (!child.querySelector || !child.querySelector('small')) {
                child.remove();
            }
        });

        if (editSelectedFiles.length > 0) {
            editImagePreviewContainer.style.display = 'block';

            const previewRow = document.createElement('div');
            previewRow.className = 'row g-2 mt-2';

            editSelectedFiles.forEach((file, index) => {
                const col = document.createElement('div');
                col.className = 'col-md-3 col-sm-6';

                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';

                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = file.name;

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image-btn';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = function() {
                    removeEditImage(index);
                };

                const fileName = document.createElement('div');
                fileName.className = 'image-file-name';
                fileName.textContent = file.name;

                previewItem.appendChild(img);
                previewItem.appendChild(removeBtn);
                previewItem.appendChild(fileName);
                col.appendChild(previewItem);
                previewRow.appendChild(col);
            });

            editImagePreviewContainer.appendChild(previewRow);
        } else {
            editImagePreviewContainer.style.display = 'none';
        }
    }

    function removeEditImage(index) {
        // Revoke the object URL to free memory
        URL.revokeObjectURL(editSelectedFiles[index].src);
        editSelectedFiles.splice(index, 1);
        updateEditFileInput();
        updateEditPreviews();
    }

    // Reset when modal is closed
    editModal.addEventListener('hidden.bs.modal', function() {
        editSelectedFiles = [];
        updateEditFileInput();
        updateEditPreviews();
        // Clear current images display
        document.getElementById('currentImagesContainer').innerHTML = '';
        // Reset form
        document.getElementById('editInventoryModal').querySelector('form').reset();
    });
});
</script>
