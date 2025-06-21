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

