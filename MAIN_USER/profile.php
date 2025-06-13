<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT fullname, email, profile_picture, office_id, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fullname, $email, $profile_picture, $office_id, $role);
$stmt->fetch();
$stmt->close();

// Fetch office name
$office_name = '';
if ($office_id) {
    $stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $stmt->bind_result($office_name);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>My Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/dashboard.css" />
    <style>
        .cover-photo {
            height: 300px;
            background: url('../img/pilar3.jpg') no-repeat center center;
            background-size: cover;
            /* This ensures the image fills the area proportionally */
            position: relative;
            border-radius: 10px;
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 30px;
            margin-top: -70px;
        }

        .profile-img-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
        }

        .profile-img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            background-color: #f0f0f0;
        }

        .camera-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 6px;
            border-radius: 50%;
            cursor: pointer;
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile-info h3 {
            margin-bottom: 4px;
            margin-top: 30px;
        }

        .edit-btn {
            margin-left: auto;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <!-- Cover -->
        <div class="cover-photo"></div>

        <!-- Profile + Info Section -->
        <div class="profile-section">
            <!-- Profile Picture -->
            <div class="profile-img-wrapper">
                <?php if ($profile_picture && file_exists("../img/" . $profile_picture)): ?>
                    <img src="../img/<?= htmlspecialchars($profile_picture) ?>" alt="Profile" class="profile-img" id="profileImagePreview">
                <?php else: ?>
                    <div class="profile-img d-flex align-items-center justify-content-center text-secondary" id="profileImagePreview">
                        <i class="bi bi-person-circle" style="font-size: 4rem;"></i>
                    </div>
                <?php endif; ?>
                <label for="profileInput" class="camera-icon">
                    <i class="bi bi-camera-fill"></i>
                </label>
                <form action="update_profile_picture.php" method="POST" enctype="multipart/form-data" id="pictureForm">
                    <input type="file" name="profile_picture" id="profileInput" class="d-none" onchange="document.getElementById('pictureForm').submit();">
                </form>
            </div>

            <!-- Name and Role Info -->
            <div class="profile-info">
                <h3><?= htmlspecialchars($fullname) ?></h3>
                <p class="text-muted mb-0"><?= htmlspecialchars(ucfirst($role)) ?> | <?= htmlspecialchars($office_name ?: 'N/A') ?></p>
            </div>

            <!-- Edit Button -->
            <div class="edit-btn">
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square me-1"></i>
                </button>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="update_profile.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($fullname) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>