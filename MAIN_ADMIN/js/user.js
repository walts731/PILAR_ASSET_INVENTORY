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
    // Delete button opens modal
    $('.deleteUserBtn').on('click', function () {
      const userId = $(this).data('id');
      const userName = $(this).data('name');

      $('#deleteUserId').val(userId);
      $('#deleteUserName').text(userName);
      const modal = new bootstrap.Modal(document.getElementById('confirmDeleteUserModal'));
      modal.show();
    });
  });