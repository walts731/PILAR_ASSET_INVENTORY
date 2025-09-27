// Dedicated JS for Consumables tab interactions (delete flow, modal population)
// Requires jQuery and Bootstrap

$(document).ready(function () {
  // Populate Delete Consumable modal when clicking delete button
  $(document).on('click', '.deleteConsumableBtn', function () {
    const id = $(this).data('id');
    const name = $(this).data('name') || 'this item';

    $('#deleteConsumableId').val(id);
    $('#deleteConsumableName').text(name);

    // Preserve current office filter in hidden field for redirect after deletion
    const urlParams = new URLSearchParams(window.location.search);
    const currentOffice = urlParams.get('office') || $('#officeFilter').val() || 'all';
    $('#deleteConsumableOffice').val(currentOffice);
  });

  // Optional: basic guard on submit to ensure id exists
  $(document).on('submit', '#deleteConsumableForm', function (e) {
    const id = $('#deleteConsumableId').val();
    if (!id) {
      e.preventDefault();
      alert('Invalid consumable selected for deletion.');
    }
  });
});
