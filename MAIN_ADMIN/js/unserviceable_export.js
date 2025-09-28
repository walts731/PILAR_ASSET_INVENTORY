// Unserviceable Assets Export Functionality
function exportUnserviceable(format) {
  // Get current filter values
  const officeFilter = new URLSearchParams(window.location.search).get('office') || 'all';
  const redTagFilter = document.getElementById('redTagFilter') ? document.getElementById('redTagFilter').value || 'all' : 'all';
  
  // Build export URL with filters
  let exportUrl = '';
  const params = new URLSearchParams();
  
  // Add office filter
  params.append('office', officeFilter);
  
  // Add red tag filter
  if (redTagFilter && redTagFilter !== 'all') {
    params.append('red_tag', redTagFilter);
  }
  
  // Add date filters (you can extend this for date range filtering)
  params.append('filter_type', 'all');
  
  // Determine export file based on format
  if (format === 'csv') {
    exportUrl = 'export_unserviceable_csv.php?' + params.toString();
  } else if (format === 'pdf') {
    exportUrl = 'export_unserviceable_pdf.php?' + params.toString();
  }
  
  // Open export URL
  if (exportUrl) {
    window.open(exportUrl, '_blank');
  }
}

// Initialize export functionality when document is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('Unserviceable export functionality loaded');
});
