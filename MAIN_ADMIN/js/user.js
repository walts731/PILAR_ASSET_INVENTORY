$(document).ready(function() {
      $('#userTable').DataTable({
        "pageLength": 10
      });
    });

    $(document).on('click', '.editUserBtn', function() {
      const btn = $(this);
      $('#editUserId').val(btn.data('id'));
      $('#editFullname').val(btn.data('fullname'));
      $('#editUsername').val(btn.data('username'));
      $('#editEmail').val(btn.data('email'));
      $('#editRole').val(btn.data('role'));
      $('#editStatus').val(btn.data('status'));
    });

    $(document).ready(function () {
    // Delete button opens modal (soft delete)
    $('.deleteUserBtn').on('click', function () {
      const btn = $(this);
      const userId = btn.data('id');
      const userName = btn.data('fullname');
      const office = btn.data('office');

      $('#deleteUserId').val(userId);
      $('#deleteUserName').text(userName);
      $('#deleteUserOffice').val(office);
      const modal = new bootstrap.Modal(document.getElementById('confirmDeleteUserModal'));
      modal.show();
    });
  });