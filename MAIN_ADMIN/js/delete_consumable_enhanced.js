/**
 * Enhanced Delete Consumable JavaScript
 * Handles the enhanced delete consumable modal and AJAX deletion process
 * Requires jQuery and Bootstrap 5
 */

class DeleteConsumableEnhanced {
    constructor() {
        this.currentConsumableId = null;
        this.currentOffice = 'all';
        this.isDeleting = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.getCurrentOffice();
    }

    bindEvents() {
        // Handle delete button clicks
        $(document).on('click', '.deleteConsumableEnhancedBtn', (e) => {
            this.handleDeleteButtonClick(e);
        });

        // Handle modal confirmation
        $('#confirmDeleteConsumableEnhanced').on('click', () => {
            console.log('Confirm delete button clicked');
            this.confirmDeletion();
        });

        // Reset modal when hidden
        $('#deleteConsumableEnhancedModal').on('hidden.bs.modal', () => {
            this.resetModal();
        });

        // Handle office filter changes
        $('#officeFilter').on('change', () => {
            this.getCurrentOffice();
        });
    }

    getCurrentOffice() {
        const urlParams = new URLSearchParams(window.location.search);
        this.currentOffice = urlParams.get('office') || $('#officeFilter').val() || 'all';
    }

    handleDeleteButtonClick(e) {
        const button = $(e.currentTarget);
        
        // Extract data from button attributes
        const consumableData = {
            id: button.data('id'),
            stockNo: button.data('stock-no') || 'N/A',
            description: button.data('description') || 'Unknown Item',
            category: button.data('category') || 'Uncategorized',
            quantity: button.data('quantity') || 0,
            unit: button.data('unit') || 'pcs',
            value: button.data('value') || 0,
            status: button.data('status') || 'available',
            office: button.data('office') || 'No Office',
            lastUpdated: button.data('last-updated') || 'Unknown'
        };

        console.log('Delete button clicked for consumable:', consumableData);

        // Validate required data
        if (!consumableData.id || consumableData.id <= 0) {
            this.showAlert('error', 'Invalid consumable selected for deletion.');
            return;
        }

        // Store current consumable ID
        this.currentConsumableId = consumableData.id;

        // Populate modal with consumable data
        this.populateModal(consumableData);

        // Show the modal
        $('#deleteConsumableEnhancedModal').modal('show');
    }

    populateModal(data) {
        // Calculate total value
        const totalValue = (parseFloat(data.quantity) * parseFloat(data.value)).toFixed(2);

        // Populate modal fields
        $('#deleteConsumableStockNo').text(data.stockNo);
        $('#deleteConsumableDescription').text(data.description);
        $('#deleteConsumableCategory').text(data.category);
        $('#deleteConsumableQuantity').text(data.quantity);
        $('#deleteConsumableUnit').text(data.unit);
        $('#deleteConsumableValue').text(parseFloat(data.value).toFixed(2));
        $('#deleteConsumableTotalValue').text(totalValue);
        $('#deleteConsumableOffice').text(data.office);
        $('#deleteConsumableLastUpdated').text(data.lastUpdated);
        $('#deleteConsumableConfirmName').text(data.description);

        // Set status badge
        const statusElement = $('#deleteConsumableStatus');
        statusElement.removeClass('badge bg-success bg-secondary bg-warning bg-danger');
        
        switch(data.status.toLowerCase()) {
            case 'available':
                statusElement.addClass('badge bg-success').text('Available');
                break;
            case 'unavailable':
                statusElement.addClass('badge bg-secondary').text('Unavailable');
                break;
            case 'low stock':
                statusElement.addClass('badge bg-warning').text('Low Stock');
                break;
            default:
                statusElement.addClass('badge bg-secondary').text(data.status);
        }
    }

    async confirmDeletion() {
        if (this.isDeleting) {
            console.log('Deletion already in progress, ignoring click');
            return;
        }

        if (!this.currentConsumableId) {
            this.showAlert('error', 'No consumable selected for deletion.');
            return;
        }

        // Set loading state
        this.setLoadingState(true);

        try {
            // Prepare deletion data
            const deletionData = {
                id: this.currentConsumableId,
                office: this.currentOffice
            };

            console.log('Sending deletion request:', deletionData);

            // Send AJAX request
            const response = await fetch('delete_consumable_enhanced.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(deletionData)
            });

            // Check HTTP status
            if (!response.ok) {
                const text = await response.text();
                console.error('HTTP error during deletion:', response.status, text);
                throw new Error(`HTTP ${response.status}: ${text || 'Server error'}`);
            }

            // Try to parse JSON safely
            let result;
            const ct = response.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Unexpected non-JSON response:', text);
                throw new Error('Unexpected server response');
            }

            console.log('Deletion response:', result);

            if (result.success) {
                // Success handling
                this.handleDeletionSuccess(result);
            } else {
                // Error handling
                this.handleDeletionError(result.message || 'Unknown error occurred');
            }

        } catch (error) {
            console.error('Deletion request failed:', error);
            this.handleDeletionError('Network error: ' + error.message);
        }
    }

    handleDeletionSuccess(result) {
        console.log('Consumable deleted successfully:', result);

        // Hide modal
        $('#deleteConsumableEnhancedModal').modal('hide');

        // Show success message
        this.showAlert('success', `Consumable "${result.data.description}" has been deleted and archived successfully.`);

        // Redirect to maintain office filter
        setTimeout(() => {
            const redirectUrl = `inventory.php?office=${encodeURIComponent(this.currentOffice)}&delete=success&tab=consumables`;
            console.log('Redirecting to:', redirectUrl);
            window.location.href = redirectUrl;
        }, 1500);
    }

    handleDeletionError(errorMessage) {
        console.error('Deletion failed:', errorMessage);
        
        // Set normal state
        this.setLoadingState(false);

        // Show error message
        this.showAlert('error', 'Failed to delete consumable: ' + errorMessage);
    }

    setLoadingState(isLoading) {
        this.isDeleting = isLoading;
        const confirmButton = $('#confirmDeleteConsumableEnhanced');
        
        if (isLoading) {
            confirmButton.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Deleting...'
            );
        } else {
            confirmButton.prop('disabled', false).html(
                '<i class="bi bi-trash me-1"></i>Yes, Delete Consumable'
            );
        }
    }

    resetModal() {
        // Reset loading state
        this.setLoadingState(false);
        
        // Clear current consumable ID
        this.currentConsumableId = null;
        
        // Clear modal content
        $('#deleteConsumableStockNo, #deleteConsumableDescription, #deleteConsumableCategory, #deleteConsumableQuantity, #deleteConsumableUnit, #deleteConsumableValue, #deleteConsumableTotalValue, #deleteConsumableOffice, #deleteConsumableLastUpdated, #deleteConsumableConfirmName').text('');
        
        // Reset status badge
        $('#deleteConsumableStatus').removeClass('badge bg-success bg-secondary bg-warning bg-danger').text('');
        
        console.log('Modal reset completed');
    }

    showAlert(type, message) {
        // Remove existing alerts
        $('.alert-dismissible').remove();

        // Create alert element
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bi ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Insert alert at the top of the page
        const $container = $('main .container-fluid');
        if ($container.length) {
            $container.prepend(alertHtml);
        } else {
            console.warn('Main container not found; appending alert to body');
            $('body').prepend(alertHtml);
            // As a last resort for visibility, also show a native alert
            try { if (type !== 'success') window.alert(message); } catch (_) {}
        }

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert-dismissible').fadeOut();
        }, 5000);
    }

    // Public method to manually trigger deletion (for testing)
    triggerDeletion(consumableId) {
        this.currentConsumableId = consumableId;
        this.confirmDeletion();
    }
}

// Initialize when document is ready
$(document).ready(function() {
    console.log('Initializing Enhanced Delete Consumable system...');
    window.deleteConsumableEnhanced = new DeleteConsumableEnhanced();
    console.log('Enhanced Delete Consumable system initialized successfully');
});

// Export for module usage (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DeleteConsumableEnhanced;
}
