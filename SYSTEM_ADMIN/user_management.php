<?php
session_start();
require_once '../connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Check if user has permission to manage users
$has_permission = false;
$user_id = $_SESSION['user_id'];
$permission_check = $conn->prepare("
    SELECT 1 FROM users u
    LEFT JOIN user_permissions up ON u.id = up.user_id
    WHERE u.id = ? AND (u.role = 'super_admin' OR up.permission = 'manage_users')
    LIMIT 1
");

if ($permission_check) {
    $permission_check->bind_param('i', $user_id);
    $permission_check->execute();
    $permission_check->store_result();
    $has_permission = $permission_check->num_rows > 0;
    $permission_check->close();
}

if (!$has_permission) {
    $_SESSION['error'] = 'You do not have permission to manage users.';
    header('Location: system_admin_dashboard.php');
    exit();
}

// Set page title
$pageTitle = 'User Management';

// Include header
include '../includes/header.php';

// Fetch all users with their roles
$users = [];
$query = "
    SELECT u.*, r.name as role_name, r.color as role_color
    FROM users u
    LEFT JOIN roles r ON u.role = r.name
    ORDER BY u.fullname ASC
";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch all available roles
$roles = [];
$roles_result = $conn->query("SELECT * FROM roles ORDER BY position DESC, name ASC");
if ($roles_result) {
    while ($role = $roles_result->fetch_assoc()) {
        $roles[] = $role;
    }
}
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main flex-grow-1">
        <?php include 'includes/topbar.php' ?>

        <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">User Management</h1>
            <div class="d-flex gap-2">
                <a href="user_roles.php" class="btn btn-outline-secondary">
                    <i class="fas fa-user-shield me-2"></i>Manage Roles
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i>Add New User
                </button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Users Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3" style="width: 40px; height: 40px; border-radius: 50%; background-color: #<?= !empty($user['profile_picture']) ? 'fff' : 'e9ecef'; ?>; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                                <?php if (!empty($user['profile_picture'])): ?>
                                                    <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-user" style="font-size: 1.2rem; color: #6c757d;"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($user['fullname']) ?></div>
                                                <small class="text-muted">@<?= htmlspecialchars($user['username']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if (!empty($user['role_name'])): ?>
                                            <span class="badge" style="background-color: <?= $user['role_color'] ?? '#6c757d' ?>; color: #fff;">
                                                <?= htmlspecialchars($user['role_name']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Role</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($user['status'] ?? 'inactive') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= !empty($user['last_login']) ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="d-flex gap-2">
                                            <!-- Edit Button -->
                                            <button class="btn btn-sm btn-icon btn-outline-primary edit-user" 
                                                    data-id="<?= $user['id'] ?>"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top"
                                                    title="Edit User"
                                                    aria-label="Edit user">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <!-- Toggle Status Button -->
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <button class="btn btn-sm btn-icon btn-outline-danger deactivate-user" 
                                                            data-id="<?= $user['id'] ?>"
                                                            data-name="<?= htmlspecialchars($user['fullname']) ?>"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top"
                                                            title="Deactivate User"
                                                            aria-label="Deactivate user">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-icon btn-outline-success activate-user" 
                                                            data-id="<?= $user['id'] ?>"
                                                            data-name="<?= htmlspecialchars($user['fullname']) ?>"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top"
                                                            title="Activate User"
                                                            aria-label="Activate user">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <!-- Reset Password Button -->
                                                <button class="btn btn-sm btn-icon btn-outline-warning reset-password" 
                                                        data-id="<?= $user['id'] ?>"
                                                        data-name="<?= htmlspecialchars($user['fullname']) ?>"
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top"
                                                        title="Reset Password"
                                                        aria-label="Reset password">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addUserForm" action="process_user.php" method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= htmlspecialchars($role['name']) ?>"><?= htmlspecialchars($role['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sendWelcomeEmail" name="send_welcome_email" checked>
                                <label class="form-check-label" for="sendWelcomeEmail">
                                    Send welcome email with login instructions
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editUserForm" action="process_user.php" method="POST">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editFullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editFullname" name="fullname" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editUsername" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editUsername" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editEmail" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRole" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="editRole" name="role" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= htmlspecialchars($role['name']) ?>"><?= htmlspecialchars($role['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" checked>
                                    <label class="form-check-label" for="statusActive">
                                        Active
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive">
                                    <label class="form-check-label" for="statusInactive">
                                        Inactive
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resetPasswordForm" action="process_user.php" method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="resetPasswordUserId">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reset the password for <strong id="resetUserName"></strong>?</p>
                    <div class="form-group mt-3">
                        <label for="resetPwNewPassword" class="form-label">New Password (leave blank to generate random password)</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="resetPwNewPassword" 
                                   name="new_password" 
                                   placeholder="Leave blank to generate random password"
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="resetPwGeneratePassword"
                                    aria-label="Generate random password">
                            <i class="fas fa-sync-alt"></i> Generate
                        </button>
                    </div>
                    <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="forcePasswordChange" name="force_password_change" checked>
                        <label class="form-check-label" for="forcePasswordChange">
                            Require password change at next login
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include footer -->
<?php include '../includes/footer.php'; ?>

<!-- Custom JavaScript -->
<script>
// Safe notification handling
if (typeof NotificationManager === 'undefined') {
    window.NotificationManager = {
        show: function(type, message) {
            const alertType = type === 'error' ? 'danger' : type;
            const alertHtml = `
                <div class="alert alert-${alertType} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('.container-fluid:first').prepend(alertHtml);
            setTimeout(() => $('.alert').alert('close'), 5000);
        }
    };
}

$(document).ready(function() {
    // Initialize DataTable
    var usersTable = $('#usersTable').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 25,
        dom: '<"d-flex justify-content-between align-items-center mb-3"f<"d-flex align-items-center">>rt<"d-flex justify-content-between align-items-center"ip>',
        language: {
            search: "",
            searchPlaceholder: "Search users...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ users",
            infoEmpty: "No users found",
            infoFiltered: "(filtered from _MAX_ total users)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        initComplete: function() {
            // Move the search box to the custom container
            $('div.dt-buttons').appendTo('.dataTables_filter');
            $('.dataTables_filter').addClass('d-flex align-items-center');
        }
    });

    // Custom search box
    $('#userSearch').on('keyup', function() {
        usersTable.search(this.value).draw();
    });

    // Search button click handler
    $('#searchButton').on('click', function() {
        usersTable.search($('#userSearch').val()).draw();
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Edit user modal
    $('.edit-user').on('click', function() {
        var userId = $(this).data('id');
        
        // Fetch user data via AJAX
        $.ajax({
            url: 'get_user.php',
            type: 'GET',
            data: { id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var user = response.data;
                    $('#editUserId').val(user.id);
                    $('#editFullname').val(user.fullname);
                    $('#editUsername').val(user.username);
                    $('#editEmail').val(user.email);
                    $('#editRole').val(user.role);
                    
                    // Set status radio button
                    if (user.status === 'inactive') {
                        $('#statusInactive').prop('checked', true);
                    } else {
                        $('#statusActive').prop('checked', true);
                    }
                    
                    // Show the modal
                    var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                    editModal.show();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error fetching user data. Please try again.');
            }
        });
    });

    // Reset password modal
    $('.reset-password').on('click', function() {
        var userId = $(this).data('id');
        var userName = $(this).data('name');
        
        $('#resetPasswordUserId').val(userId);
        $('#resetUserName').text(userName);
        $('#newPassword').val('');
        
        var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        resetModal.show();
    });

    // Generate random password
    $('#resetPwGeneratePassword').on('click', function() {
        const password = generatePassword(12);
        $('#resetPwNewPassword').val(password);
    });

    function generatePassword(length = 12) {
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const numbers = '0123456789';
        const symbols = '!@#$%^&*()_+~`|}{[]\\:;\'"<>,.?/=';
        const allChars = lowercase + uppercase + numbers + symbols;
        
        // Ensure at least one character from each set
        let password = [
            lowercase[Math.floor(Math.random() * lowercase.length)],
            uppercase[Math.floor(Math.random() * uppercase.length)],
            numbers[Math.floor(Math.random() * numbers.length)],
            symbols[Math.floor(Math.random() * symbols.length)]
        ];
        
        // Fill the rest of the password with random characters
        for (let i = 4; i < length; i++) {
            password.push(allChars[Math.floor(Math.random() * allChars.length)]);
        }
        
        // Shuffle the password array and join into a string
        return password.sort(() => Math.random() - 0.5).join('');
    }

    // Form submission handling
    $('#addUserForm, #editUserForm, #resetPasswordForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        // Submit form via AJAX
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    var alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                    
                    $('.container-fluid.py-4').prepend(alert);
                    
                    // Close modal if open
                    $('.modal').modal('hide');
                    
                    // Reload page after a short delay to show the success message
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    var alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                    
                    $('.container-fluid.py-4').prepend(alert);
                    
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function(xhr, status, error) {
                // Show error message
                var alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            'An error occurred. Please try again.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                
                $('.container-fluid.py-4').prepend(alert);
                
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
    
    // Activate/Deactivate user
    $('.activate-user, .deactivate-user').on('click', function() {
        var userId = $(this).data('id');
        var action = $(this).hasClass('activate-user') ? 'activate' : 'deactivate';
        var confirmMessage = action === 'activate' 
            ? 'Are you sure you want to activate this user?' 
            : 'Are you sure you want to deactivate this user?';
        
        if (confirm(confirmMessage)) {
            $.ajax({
                url: 'process_user.php',
                type: 'POST',
                data: {
                    action: 'update_user_status',
                    user_id: userId,
                    status: action === 'activate' ? 'active' : 'inactive'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message and reload
                        var alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                    response.message +
                                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                    '</div>';
                        
                        $('.container-fluid.py-4').prepend(alert);
                        
                        // Reload page after a short delay to show the success message
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error updating user status. Please try again.');
                }
            });
        }
    });
    // Handle deactivate user
    $(document).on('click', '.deactivate-user', function() {
        const $button = $(this);
        const userId = $button.data('id');
        const userName = $button.data('name');
        
        if (confirm(`Are you sure you want to deactivate ${userName}?`)) {
            $button.addClass('btn-loading');
            // Add your deactivation AJAX call here
            // Example:
            $.post('process_user.php', {
                action: 'update_user_status',
                user_id: userId,
                status: 'inactive'
            }, function(response) {
                if (response.success) {
                    showAlert('success', `Successfully deactivated ${userName}.`);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', response.message || 'Failed to deactivate user.');
                    $button.removeClass('btn-loading');
                }
            }, 'json').fail(function() {
                showAlert('danger', 'An error occurred. Please try again.');
                $button.removeClass('btn-loading');
            });
        }
    });

    // Handle activate user
    $(document).on('click', '.activate-user', function() {
        const $button = $(this);
        const userId = $button.data('id');
        const userName = $button.data('name');
        
        if (confirm(`Are you sure you want to activate ${userName}?`)) {
            $button.addClass('btn-loading');
            
            $.post('process_user.php', {
                action: 'update_user_status',
                user_id: userId,
                status: 'active'
            }, function(response) {
                if (response.success) {
                    showAlert('success', `Successfully activated ${userName}.`);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', response.message || 'Failed to activate user.');
                    $button.removeClass('btn-loading');
                }
            }, 'json').fail(function() {
                showAlert('danger', 'An error occurred. Please try again.');
                $button.removeClass('btn-loading');
            });
        }
    });

    // Handle reset password
    $(document).on('click', '.reset-password', function() {
        const $button = $(this);
        const userId = $button.data('id');
        const userName = $button.data('name');
        
        if (confirm(`Reset password for ${userName}? A temporary password will be sent to their email.`)) {
            $button.addClass('btn-loading');
            
            $.post('process_user.php', {
                action: 'reset_password',
                user_id: userId
            }, function(response) {
                $button.removeClass('btn-loading');
                if (response.success) {
                    showAlert('success', `Password reset email sent to ${userName}.`);
                } else {
                    showAlert('danger', response.message || 'Failed to reset password.');
                }
            }, 'json').fail(function() {
                showAlert('danger', 'An error occurred. Please try again.');
                $button.removeClass('btn-loading');
            });
        }
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Append alert to the top of the content area
        $('.container-fluid:first').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }
});
</script>
