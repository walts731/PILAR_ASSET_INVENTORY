/**
 * Add Asset Modal JavaScript
 * Professional tabbed interface with validation and dynamic features
 */

class AddAssetModal {
    constructor() {
        this.currentTab = 0;
        this.totalTabs = 3;
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeDefaults();
        this.setupDragAndDrop();
        this.setupFormValidation();
        this.updateSummary();
    }

    bindEvents() {
        // Wait for modal to be fully loaded
        const modal = document.getElementById('addAssetModal');
        if (!modal) {
            console.error('Add Asset Modal not found');
            return;
        }

        // Tab navigation
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        
        if (nextBtn) nextBtn.addEventListener('click', () => this.nextTab());
        if (prevBtn) prevBtn.addEventListener('click', () => this.prevTab());
        
        // Tab pills click - work with Bootstrap's native tabs
        document.querySelectorAll('#addAssetModal .nav-pills .nav-link').forEach((tab, index) => {
            tab.addEventListener('shown.bs.tab', (e) => {
                this.currentTab = index;
                this.updateNavigationButtons();
                this.updateProgressBar();
                this.focusFirstInput();
            });
        });

        // Type change handler
        const typeSelect = document.getElementById('type');
        if (typeSelect) {
            typeSelect.addEventListener('change', () => this.handleTypeChange());
        }
        
        // Image upload
        const imageInput = document.getElementById('asset_image');
        if (imageInput) {
            imageInput.addEventListener('change', (e) => this.previewImage(e));
        }
        
        // Form input changes for summary
        this.setupSummaryUpdates();
        
        // Modal events
        modal.addEventListener('hidden.bs.modal', () => this.resetModal());
        modal.addEventListener('shown.bs.modal', () => this.focusFirstInput());
    }

    setupSummaryUpdates() {
        const inputs = ['description', 'type', 'category', 'quantity', 'unit', 'value', 'employee_name'];
        inputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', () => this.updateSummary());
                element.addEventListener('change', () => this.updateSummary());
            }
        });
    }

    nextTab() {
        if (this.validateCurrentTab()) {
            if (this.currentTab < this.totalTabs - 1) {
                const nextTabIndex = this.currentTab + 1;
                const tabButtons = document.querySelectorAll('#addAssetModal .nav-pills .nav-link');
                if (tabButtons[nextTabIndex]) {
                    // Use Bootstrap's tab API
                    const tab = new bootstrap.Tab(tabButtons[nextTabIndex]);
                    tab.show();
                }
            }
        }
    }

    prevTab() {
        if (this.currentTab > 0) {
            const prevTabIndex = this.currentTab - 1;
            const tabButtons = document.querySelectorAll('#addAssetModal .nav-pills .nav-link');
            if (tabButtons[prevTabIndex]) {
                // Use Bootstrap's tab API
                const tab = new bootstrap.Tab(tabButtons[prevTabIndex]);
                tab.show();
            }
        }
    }

    goToTab(tabIndex) {
        // Only allow going to previous tabs or next tab if current is valid
        if (tabIndex <= this.currentTab || (tabIndex === this.currentTab + 1 && this.validateCurrentTab())) {
            const tabButtons = document.querySelectorAll('#addAssetModal .nav-pills .nav-link');
            if (tabButtons[tabIndex]) {
                const tab = new bootstrap.Tab(tabButtons[tabIndex]);
                tab.show();
            }
        }
    }

    updateProgressBar() {
        const progressPercent = ((this.currentTab + 1) / this.totalTabs) * 100;
        const progressBar = document.querySelector('#addAssetModal .progress-bar');
        if (progressBar) {
            progressBar.style.width = `${progressPercent}%`;
        }
    }

    updateNavigationButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        // Previous button
        prevBtn.style.display = this.currentTab > 0 ? 'block' : 'none';

        // Next/Submit buttons
        if (this.currentTab === this.totalTabs - 1) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }
    }

    validateCurrentTab() {
        const tabPanes = document.querySelectorAll('#addAssetModal .tab-pane');
        if (!tabPanes[this.currentTab]) return true;
        
        const currentPane = tabPanes[this.currentTab];
        const requiredFields = currentPane.querySelectorAll('[required]');
        let isValid = true;

        // Clear previous validation states
        currentPane.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });

        // Remove existing alerts
        currentPane.querySelectorAll('.alert').forEach(alert => alert.remove());

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        if (!isValid) {
            this.showValidationAlert(currentPane, 'Please fill in all required fields before proceeding.');
        }

        return isValid;
    }

    showValidationAlert(container, message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
        alert.innerHTML = `
            <i class="bi bi-exclamation-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        container.insertBefore(alert, container.firstChild);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }

    handleTypeChange() {
        const type = document.getElementById('type').value;
        const propertyGroup = document.getElementById('propertyNoGroup');
        const stockGroup = document.getElementById('stockNoGroup');

        if (type === 'consumable') {
            propertyGroup.classList.add('d-none');
            stockGroup.classList.remove('d-none');
            document.getElementById('property_no').disabled = true;
            document.getElementById('stock_no').disabled = false;
        } else {
            stockGroup.classList.add('d-none');
            propertyGroup.classList.remove('d-none');
            document.getElementById('stock_no').disabled = true;
            document.getElementById('property_no').disabled = false;
        }

        this.updateSummary();
    }

    previewImage(event) {
        const preview = document.getElementById('assetImagePreview');
        const file = event.target.files[0];
        
        if (file) {
            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                this.showValidationAlert(
                    event.target.closest('.field-group'),
                    'File size must be less than 10MB.'
                );
                event.target.value = '';
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                this.showValidationAlert(
                    event.target.closest('.field-group'),
                    'Please select a valid image file.'
                );
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '#';
            preview.classList.add('d-none');
        }
    }

    setupDragAndDrop() {
        const uploadArea = document.querySelector('.image-upload-area');
        const fileInput = document.getElementById('asset_image');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
        });

        uploadArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                this.previewImage({ target: fileInput });
            }
        });
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    updateSummary() {
        const description = document.getElementById('description').value || '-';
        const type = document.getElementById('type').value || '';
        const categorySelect = document.getElementById('category');
        const category = categorySelect.options[categorySelect.selectedIndex]?.text || '';
        const quantity = document.getElementById('quantity').value || '-';
        const unitSelect = document.getElementById('unit');
        const unit = unitSelect.options[unitSelect.selectedIndex]?.text || '';
        const value = document.getElementById('value').value || '0';
        const employee = document.getElementById('employee_name').value || '-';

        document.getElementById('summaryDescription').textContent = description;
        document.getElementById('summaryTypeCategory').textContent = type && category ? `${type} - ${category}` : '-';
        document.getElementById('summaryQuantity').textContent = quantity && unit ? `${quantity} ${unit}` : '-';
        document.getElementById('summaryValue').textContent = value !== '0' ? `₱${parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 2})}` : '-';
        document.getElementById('summaryAssignee').textContent = employee;
    }

    initializeDefaults() {
        // Set default unit to 'unit' if available
        const unitSelect = document.getElementById('unit');
        if (unitSelect) {
            for (const option of unitSelect.options) {
                if (option.value.toLowerCase() === 'unit') {
                    unitSelect.value = option.value;
                    break;
                }
            }
        }

        // Set default quantity
        document.getElementById('quantity').value = '1';
        
        // Initialize summary
        this.updateSummary();
    }

    setupFormValidation() {
        const form = document.querySelector('#addAssetModal form');
        form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    validateForm() {
        const requiredFields = document.querySelectorAll('#addAssetModal [required]');
        let isValid = true;

        requiredFields.forEach(field => {
            field.classList.remove('is-invalid');
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        if (!isValid) {
            // Go to first tab with errors
            for (let i = 0; i < this.totalTabs; i++) {
                const tabPane = document.querySelectorAll('.tab-pane')[i];
                if (tabPane.querySelector('.is-invalid')) {
                    this.currentTab = i;
                    this.showTab(i);
                    this.showValidationAlert(tabPane, 'Please complete all required fields.');
                    break;
                }
            }
        }

        return isValid;
    }

    focusFirstInput() {
        setTimeout(() => {
            const currentPane = document.querySelectorAll('.tab-pane')[this.currentTab];
            const firstInput = currentPane.querySelector('input, select, textarea');
            if (firstInput && !firstInput.disabled) {
                firstInput.focus();
            }
        }, 150);
    }

    resetModal() {
        // Reset form
        const form = document.querySelector('#addAssetModal form');
        if (form) form.reset();
        
        // Reset tabs to first tab
        this.currentTab = 0;
        const firstTab = document.querySelector('#addAssetModal .nav-pills .nav-link');
        if (firstTab) {
            const tab = new bootstrap.Tab(firstTab);
            tab.show();
        }
        
        // Reset image preview
        const preview = document.getElementById('assetImagePreview');
        if (preview) {
            preview.src = '#';
            preview.classList.add('d-none');
        }
        
        // Reset validation states
        document.querySelectorAll('#addAssetModal .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        // Remove alerts
        document.querySelectorAll('#addAssetModal .alert').forEach(alert => alert.remove());
        
        // Reset type-specific fields
        const propertyGroup = document.getElementById('propertyNoGroup');
        const stockGroup = document.getElementById('stockNoGroup');
        const propertyInput = document.getElementById('property_no');
        const stockInput = document.getElementById('stock_no');
        
        if (propertyGroup) propertyGroup.classList.remove('d-none');
        if (stockGroup) stockGroup.classList.add('d-none');
        if (propertyInput) propertyInput.disabled = false;
        if (stockInput) stockInput.disabled = true;
        
        // Reset defaults
        this.initializeDefaults();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Wait a bit for Bootstrap to fully initialize
    setTimeout(() => {
        try {
            new AddAssetModal();
        } catch (error) {
            console.error('Error initializing Add Asset Modal:', error);
        }
    }, 100);
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('addAssetModal');
    if (modal.classList.contains('show')) {
        if (e.key === 'Escape') {
            // Let Bootstrap handle this
        } else if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (submitBtn.style.display !== 'none') {
                submitBtn.click();
            } else if (nextBtn.style.display !== 'none') {
                nextBtn.click();
            }
        }
    }
});
