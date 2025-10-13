<style>
/* Enhanced Pop-out Modal Styling */
.add-infrastructure-modal .modal-dialog {
  max-width: 95vw;
  width: 95vw;
  max-height: 95vh;
  margin: 2.5vh auto;
  transform: scale(0.9);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.add-infrastructure-modal.show .modal-dialog {
  transform: scale(1);
}

.add-infrastructure-modal .modal-content {
  border: none;
  border-radius: 16px;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15),
              0 0 0 1px rgba(255, 255, 255, 0.05),
              inset 0 1px 0 rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.95);
  overflow: hidden;
}

.add-infrastructure-modal .modal-backdrop {
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
}

.add-infrastructure-modal .modal-header {
  background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
  color: white;
  border-radius: 16px 16px 0 0;
  padding: 1.5rem 2rem;
  border: none;
  position: relative;
  overflow: hidden;
}

.add-infrastructure-modal .modal-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
  pointer-events: none;
}

.add-infrastructure-modal .modal-title {
  font-weight: 700;
  font-size: 1.4rem;
  position: relative;
  z-index: 1;
}

.add-infrastructure-modal .btn-close {
  position: relative;
  z-index: 1;
  filter: brightness(0) invert(1);
  opacity: 0.8;
  transition: opacity 0.3s ease;
}

.add-infrastructure-modal .btn-close:hover {
  opacity: 1;
}

.add-infrastructure-modal .modal-body {
  padding: 2rem;
  max-height: calc(95vh - 160px);
  overflow-y: auto;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.add-infrastructure-modal .modal-footer {
  border-radius: 0 0 16px 16px;
  padding: 1.5rem 2rem;
  background: #f8f9fa;
  border-top: 1px solid rgba(0,0,0,0.1);
}

/* Enhanced button styling */
.add-infrastructure-modal .btn-info {
  background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.add-infrastructure-modal .btn-info:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
}

/* Form enhancements */
.add-infrastructure-modal .form-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
}

.add-infrastructure-modal .form-control {
  border: 2px solid #e9ecef;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
}

.add-infrastructure-modal .form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  background: rgba(255, 255, 255, 0.95);
}

/* Image upload area enhancements */
.image-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: pointer;
    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
    position: relative;
    overflow: hidden;
}

.image-upload-area::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(13, 110, 253, 0.05) 0%, transparent 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-upload-area:hover::before {
    opacity: 1;
}

.image-upload-area:hover {
    border-color: #0d6efd;
    background: linear-gradient(135deg, #f8f9ff 0%, #e7f1ff 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.15);
}

.image-upload-area.dragover {
    border-color: #0d6efd;
    background: linear-gradient(135deg, #e7f1ff 0%, #d1e7ff 100%);
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 35px rgba(13, 110, 253, 0.25);
}

.image-upload-area.dragover::before {
    opacity: 0.5;
}

/* Enhanced preview items */
.image-preview-item {
    position: relative;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 0.75rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.image-preview-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.image-preview-item img {
    width: 100%;
    height: 140px;
    object-fit: cover;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.image-preview-item:hover img {
    transform: scale(1.05);
}

/* Enhanced remove button */
.remove-image-btn {
    position: absolute;
    top: -10px;
    right: -10px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.remove-image-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
}

/* File name styling */
.image-file-name {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.5rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 500;
}

.add-infrastructure-modal.showing .modal-dialog {
  animation: modalPopIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.add-infrastructure-modal.hiding .modal-dialog {
  animation: modalPopOut 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalPopIn {
  0% {
    opacity: 0;
    transform: scale(0.8) translateY(-20px);
  }
  50% {
    opacity: 0.8;
    transform: scale(0.95) translateY(-5px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes modalPopOut {
  0% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Responsive design */
@media (max-width: 768px) {
    .add-infrastructure-modal .modal-dialog {
        width: 98vw;
        max-width: 98vw;
        margin: 1vh auto;
        max-height: 98vh;
    }

    .add-infrastructure-modal .modal-body {
        padding: 1.5rem 1rem;
        max-height: calc(98vh - 140px);
    }

    .add-infrastructure-modal .modal-header,
    .add-infrastructure-modal .modal-footer {
        padding: 1rem 1.5rem;
    }
}
</style>
    <div class="modal fade add-infrastructure-modal" id="addInventoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
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
                            <label class="form-label">Date Constructed</label>
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

                        <!-- Multiple image upload with preview (full width) -->
                        <div class="col-12">
                            <label class="form-label">Additional Images (max 4)</label>
                            <div class="image-upload-area" id="imageUploadArea">
                                <i class="bi bi-cloud-upload display-4 text-muted mb-2"></i>
                                <p class="mb-2">Click to upload or drag and drop</p>
                                <small class="text-muted">JPG, PNG, GIF up to 10MB each (max 4 images)</small>
                                <input type="file" name="additional_images[]" id="additionalImages" class="d-none" accept="image/*" multiple>
                            </div>

                            <!-- Image Preview Area -->
                            <div id="imagePreviewContainer" class="row g-2 mt-3" style="display: none;">
                                <div class="col-12">
                                    <small class="text-muted">Selected Images:</small>
                                </div>
                                <!-- Previews will be inserted here -->
                            </div>
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

<script>
// Enhanced modal animations
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addInventoryModal');

    // Add show class animation
    modal.addEventListener('show.bs.modal', function() {
        setTimeout(() => {
            modal.classList.add('showing');
        }, 50);
    });

    modal.addEventListener('shown.bs.modal', function() {
        modal.classList.remove('showing');
    });

    modal.addEventListener('hide.bs.modal', function() {
        modal.classList.add('hiding');
    });

    modal.addEventListener('hidden.bs.modal', function() {
        modal.classList.remove('hiding');
    });

    // Enhanced image upload functionality
    const imageUploadArea = document.getElementById('imageUploadArea');
    const additionalImagesInput = document.getElementById('additionalImages');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const maxImages = 4;
    let selectedFiles = [];

    // Click to upload
    imageUploadArea.addEventListener('click', function() {
        additionalImagesInput.click();
    });

    // File selection
    additionalImagesInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Drag and drop
    imageUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        imageUploadArea.classList.add('dragover');
    });

    imageUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        imageUploadArea.classList.remove('dragover');
    });

    imageUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        imageUploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    function handleFiles(files) {
        // Check total files after adding new ones
        if (selectedFiles.length + files.length > maxImages) {
            alert(`You can only upload a maximum of ${maxImages} images.`);
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
            selectedFiles.push(file);
        }

        // Update input and previews
        updateFileInput();
        updatePreviews();
    }

    function updateFileInput() {
        // Create a new DataTransfer object to update the input
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        additionalImagesInput.files = dt.files;
    }

    function updatePreviews() {
        // Clear existing previews
        const previewRow = imagePreviewContainer.querySelector('.row.g-2');
        if (!previewRow) {
            // Create the preview row if it doesn't exist
            const newRow = document.createElement('div');
            newRow.className = 'row g-2 mt-2';
            imagePreviewContainer.appendChild(newRow);
        }

        // Clear all child elements except the "Selected Images" label
        const children = Array.from(imagePreviewContainer.children);
        children.forEach(child => {
            if (!child.querySelector || !child.querySelector('small')) {
                child.remove();
            }
        });

        if (selectedFiles.length > 0) {
            imagePreviewContainer.style.display = 'block';

            const previewRow = document.createElement('div');
            previewRow.className = 'row g-2 mt-2';

            selectedFiles.forEach((file, index) => {
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
                    removeImage(index);
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

            imagePreviewContainer.appendChild(previewRow);
        } else {
            imagePreviewContainer.style.display = 'none';
        }
    }

    function removeImage(index) {
        // Revoke the object URL to free memory
        URL.revokeObjectURL(selectedFiles[index].src);
        selectedFiles.splice(index, 1);
        updateFileInput();
        updatePreviews();
    }

    // Reset when modal is closed
    document.getElementById('addInventoryModal').addEventListener('hidden.bs.modal', function() {
        selectedFiles = [];
        updateFileInput();
        updatePreviews();
    });
});
</script>