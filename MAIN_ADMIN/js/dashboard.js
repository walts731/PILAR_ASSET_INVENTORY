function updateDateTime() {
      const now = new Date();
      const formatted = now.toLocaleString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
      document.getElementById('datetime').textContent = formatted;
    }
    setInterval(updateDateTime, 1000);
    updateDateTime(); // Initial call

    $(document).ready(function() {
      $('#assetTable').DataTable({
        responsive: true,
        pageLength: 10,
        language: {
          search: "Search assets:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ assets"
        }
      });
    });

    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.sidebar');
    const main = document.querySelector('.main');
    const icon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', () => {
      // Toggle sidebar visibility
      sidebar.classList.toggle('sidebar-hidden');
      main.classList.toggle('main-expanded');

      // Toggle icon direction
      if (sidebar.classList.contains('sidebar-hidden')) {
        icon.classList.remove('bi-chevron-left');
        icon.classList.add('bi-chevron-right');
      } else {
        icon.classList.remove('bi-chevron-right');
        icon.classList.add('bi-chevron-left');
      }
    });

    document.addEventListener("DOMContentLoaded", function() {
      const table = $('#consumablesTable').DataTable({
        responsive: true
      });

      $('#stockFilter').on('change', function() {
        const filter = $(this).val();
        if (filter === "low") {
          table.rows().every(function() {
            const row = this.node();
            const stock = row.getAttribute('data-stock');
            $(row).toggle(stock === 'low');
          });
        } else {
          table.rows().every(function() {
            $(this.node()).show();
          });
        }
      });
    });

    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    // Load saved mode
    if (localStorage.getItem('theme') === 'dark') {
      document.body.classList.add('dark-mode');
      themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
    }

    themeToggle.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');

      // Swap icons
      themeIcon.classList.toggle('bi-moon-fill', !isDark);
      themeIcon.classList.toggle('bi-sun-fill', isDark);

      // Save preference
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    $(document).ready(function () {
    // For Asset tab
    $('#selectAllAssets').click(function () {
      $('.asset-checkbox').prop('checked', this.checked);
    });

    // For Consumable tab
    $('#selectAllConsumables').click(function () {
      $('.consumable-checkbox').prop('checked', this.checked);
    });
  });

  document.querySelectorAll("form").forEach(form => {
  // Skip validation if form does not contain any 'selected_assets[]' checkboxes
  const checkboxes = form.querySelectorAll("input[type='checkbox'][name='selected_assets[]']");
  if (checkboxes.length === 0) return; // Skip this form

  const alertBox = form.querySelector("#checkboxAlert");

  form.addEventListener("submit", function (e) {
    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);

    if (!anyChecked) {
      e.preventDefault();
      if (alertBox) {
        alertBox.classList.add("show");
        setTimeout(() => alertBox.classList.remove("show"), 3000);
      } else {
        alert("Please select at least one item.");
      }
    }
  });
});

// Update Consumable Modal
$(document).ready(function () {
  $('.updateConsumableBtn').on('click', function () {
    $('#consumable_id').val($(this).data('id'));
    $('#edit_name').val($(this).data('name'));
    $('#edit_category').val($(this).data('category'));
    $('#edit_description').val($(this).data('description'));
    $('#edit_unit').val($(this).data('unit'));
    $('#edit_quantity').val($(this).data('qty'));
    $('#edit_status').val($(this).data('status'));
  });
});

// Delete Consumable Modal
$(document).ready(function () {
  $('.deleteConsumableBtn').on('click', function () {
    $('#deleteConsumableId').val($(this).data('id'));
    $('#deleteConsumableName').text($(this).data('name'));
  });
});

// Initialize DataTable for the archive table
$(document).ready(function() {
            $('#archiveTable').DataTable();
        });

// Update Asset Modal
$(document).on("click", ".updateAssetBtn", function () {
  $("#asset_id").val($(this).data("id"));
  $("#edit_asset_name").val($(this).data("name"));
  $("#edit_asset_category").val($(this).data("category"));
  $("#edit_asset_description").val($(this).data("description"));
  $("#edit_asset_quantity").val($(this).data("qty"));
  $("#edit_asset_unit").val($(this).data("unit"));
  $("#edit_asset_status").val($(this).data("status"));
  $("#edit_asset_office").val($(this).data("office")); 
});

// Delete Asset Modal
$(document).ready(function () {
  $('.deleteAssetBtn').on('click', function () {
    $('#delete_asset_id').val($(this).data('id'));
    $('#delete_asset_name').text($(this).data('name'));
  });
});


// BORROW
// Helper function to collect selected asset IDs
function getSelectedAssetIds() {
  const checkboxes = document.querySelectorAll('.asset-checkbox:checked');
  return Array.from(checkboxes).map(cb => cb.value);
}

function handleBulkAction(action) {
  const selectedIds = getSelectedAssetIds();
  const alertBox = document.getElementById('bulkActionAlert');

  if (selectedIds.length === 0) {
    alertBox.classList.remove('d-none');
    setTimeout(() => alertBox.classList.add('show'), 10);
    alertBox.innerText = "Please select at least one asset to perform this action.";
    return;
  } else {
    alertBox.classList.add('d-none'); // Hide alert if it was previously shown
  }

  // Get selected office from the dropdown
  const office = document.getElementById('officeFilter').value;

  // Redirect to action with selected IDs and office
  const ids = selectedIds.join(',');
  window.location.href = `${action}_bulk.php?ids=${encodeURIComponent(ids)}&office=${encodeURIComponent(office)}`;
}

document.getElementById('bulkBorrowBtn').addEventListener('click', () => handleBulkAction('borrow'));
document.getElementById('bulkReleaseBtn').addEventListener('click', () => handleBulkAction('release'));
document.getElementById('bulkTransferBtn').addEventListener('click', () => handleBulkAction('transfer'));
document.getElementById('bulkReturnBtn').addEventListener('click', () => handleBulkAction('return'));

setTimeout(() => {
  alertBox.classList.add('d-none');
}, 4000);

// RELEASE
function getSelectedAssetIds() {
    const checkboxes = document.querySelectorAll('.asset-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
  }

  function handleBulkAction(action) {
    const selectedIds = getSelectedAssetIds();
    const alertBox = document.getElementById("bulkActionAlert");

    if (selectedIds.length === 0) {
      alertBox.classList.remove("d-none");
      return;
    }

    alertBox.classList.add("d-none"); // hide if visible
    const ids = selectedIds.join(',');
    const office = new URLSearchParams(window.location.search).get("office") || "";
    window.location.href = `${action}_bulk.php?ids=${encodeURIComponent(ids)}&office=${encodeURIComponent(office)}`;
  }

  document.getElementById('bulkReleaseBtn').addEventListener('click', () => handleBulkAction('release'));

  document.querySelectorAll(".template-check-form").forEach(form => {
    form.addEventListener("submit", function (e) {
      const selectedTemplate = this.querySelector("select[name='template_id']");
      if (!selectedTemplate || !selectedTemplate.value) {
        e.preventDefault();
        alert("Please select a report template before generating a report.");
      }
    });
  });
