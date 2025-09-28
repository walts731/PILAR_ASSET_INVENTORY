// Consumables Export Functionality
function exportConsumables(format) {
  // Get current filter values
  const officeFilter = new URLSearchParams(window.location.search).get('office') || 'all';
  const stockFilter = document.getElementById('stockFilter').value || 'all';
  
  // Build export URL with filters
  let exportUrl = '';
  const params = new URLSearchParams();
  
  // Add office filter
  params.append('office', officeFilter);
  
  // Add stock filter
  if (stockFilter && stockFilter !== '') {
    params.append('stock', stockFilter);
  }
  
  // Add date filters (you can extend this for date range filtering)
  params.append('filter_type', 'all');
  
  // Determine export file based on format
  if (format === 'csv') {
    exportUrl = 'export_consumables_csv.php?' + params.toString();
  } else if (format === 'pdf') {
    exportUrl = 'export_consumables_pdf.php?' + params.toString();
  }
  
  // Open export URL
  if (exportUrl) {
    window.open(exportUrl, '_blank');
  }
}

// Initialize export functionality when document is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('Consumables export functionality loaded');
});
