<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
require_once '../includes/lifecycle_helper.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Ensure red_tag_number allows duplicates by removing UNIQUE index if it exists
try {
    $idxSql = "SELECT INDEX_NAME, NON_UNIQUE FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'red_tags' AND COLUMN_NAME = 'red_tag_number'";
    if ($res = $conn->query($idxSql)) {
        while ($row = $res->fetch_assoc()) {
            $indexName = $row['INDEX_NAME'];
            $nonUnique = (int)$row['NON_UNIQUE'];
            // Drop any unique index on red_tag_number (ignore PRIMARY just in case)
            if ($nonUnique === 0 && strcasecmp($indexName, 'PRIMARY') !== 0) {
                $conn->query("ALTER TABLE red_tags DROP INDEX `" . $conn->real_escape_string($indexName) . "`");
            }
        }
        $res->close();
    }
} catch (Throwable $e) {
    // Non-fatal: if drop fails, insertion will still attempt; error will show if constraint remains
}

// Fetch the municipal logo from the system table
$logo_path = '';
$stmt_logo = $conn->prepare("SELECT logo FROM system WHERE id = 1");
$stmt_logo->execute();
$result_logo = $stmt_logo->get_result();

if ($result_logo->num_rows > 0) {
    $logo_data = $result_logo->fetch_assoc();
    $logo_path = '../img/' . $logo_data['logo']; // Path to the logo image
}

$stmt_logo->close();

// Get asset and IIRUP IDs from URL
$asset_id = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : 0;
$iirup_id = isset($_GET['iirup_id']) ? intval($_GET['iirup_id']) : 0;

if ($asset_id <= 0 || $iirup_id <= 0) {
    die('Invalid request parameters.');
}

// Get current user info
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$user_query->bind_param('i', $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_query->close();

// Fetch list of users to allow selecting Tagged By
$users_list = [];
if ($resUsers = $conn->query("SELECT id, fullname FROM users ORDER BY fullname ASC")) {
    while ($u = $resUsers->fetch_assoc()) { $users_list[] = $u; }
    $resUsers->close();
}

// Check if Red Tag already exists for this asset and IIRUP
$existing_red_tag_check = false;
$red_tag_id = null;
$existing_red_tag_data = null;

$check_red_tag = $conn->prepare("SELECT * FROM red_tags WHERE asset_id = ? AND iirup_id = ?");
$check_red_tag->bind_param('ii', $asset_id, $iirup_id);
$check_red_tag->execute();
$red_tag_result = $check_red_tag->get_result();
if ($red_tag_result->num_rows > 0) {
    $existing_red_tag_check = true;
    $existing_red_tag_data = $red_tag_result->fetch_assoc();
    $red_tag_id = $existing_red_tag_data['id'];
}
$check_red_tag->close();

// Get asset details
$asset_query = $conn->prepare("
    SELECT a.id, a.property_no, a.description, a.acquisition_date, a.office_id, 
           i.particulars, i.dept_office
    FROM assets a
    LEFT JOIN iirup_items i ON a.id = i.asset_id AND i.iirup_id = ?
    WHERE a.id = ?
");
$asset_query->bind_param('ii', $iirup_id, $asset_id);
$asset_query->execute();
$asset_result = $asset_query->get_result();
$asset = $asset_result->fetch_assoc();
$asset_query->close();

if (!$asset) {
    die('Asset not found.');
}

// Determine Red Tag number
// Use existing number when editing, otherwise generate new red tag number
if ($existing_red_tag_check && $existing_red_tag_data) {
    $red_tag_number = $existing_red_tag_data['red_tag_number'];
} else {
    // Generate new red tag number using tag format system
    require_once '../includes/tag_format_helper.php';
    $red_tag_number = generateTag('red_tag');
    if (!$red_tag_number) {
        // If generation fails, use a fallback format
        $red_tag_number = 'RT-' . str_pad(1, 4, '0', STR_PAD_LEFT);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_location = $conn->real_escape_string($_POST['item_location']);
    
    // Handle custom removal reason
    $removal_reason = $conn->real_escape_string($_POST['removal_reason']);
    if ($removal_reason === 'Other' && !empty($_POST['custom_removal_reason'])) {
        $removal_reason = $conn->real_escape_string($_POST['custom_removal_reason']);
    }
    
    // Handle custom action
    $action = $conn->real_escape_string($_POST['action']);
    if ($action === 'Other' && !empty($_POST['custom_action'])) {
        $action = $conn->real_escape_string($_POST['custom_action']);
    }
    
    // Tagged by: prefer posted user id if provided, else fallback to current user
    $tagged_by = isset($_POST['tagged_by']) ? (int)$_POST['tagged_by'] : $user_id;
    
    $description = $asset['description'];
    
    // Handle multiple image uploads
    $uploaded_images = [];
    $upload_errors = [];
    
    if (isset($_FILES['asset_images']) && is_array($_FILES['asset_images']['name'])) {
        $upload_dir = '../img/assets/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        $max_files = 4;
        
        $file_count = count(array_filter($_FILES['asset_images']['name']));
        
        if ($file_count > $max_files) {
            $upload_errors[] = "Maximum $max_files images allowed.";
        } else {
            for ($i = 0; $i < count($_FILES['asset_images']['name']); $i++) {
                if ($_FILES['asset_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['asset_images']['name'][$i];
                    $file_tmp = $_FILES['asset_images']['tmp_name'][$i];
                    $file_size = $_FILES['asset_images']['size'][$i];
                    $file_type = $_FILES['asset_images']['type'][$i];
                    
                    // Validate file type
                    if (!in_array($file_type, $allowed_types)) {
                        $upload_errors[] = "Invalid file type for $file_name. Only JPEG, PNG, GIF, and WebP are allowed.";
                        continue;
                    }
                    
                    // Validate file size
                    if ($file_size > $max_file_size) {
                        $upload_errors[] = "File $file_name is too large. Maximum size is 5MB.";
                        continue;
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $unique_filename = 'asset_' . $asset_id . '_' . time() . '_' . $i . '.' . $file_extension;
                    $upload_path = $upload_dir . $unique_filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $uploaded_images[] = $unique_filename;
                    } else {
                        $upload_errors[] = "Failed to upload $file_name.";
                    }
                } elseif ($_FILES['asset_images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $upload_errors[] = "Upload error for file " . ($_FILES['asset_images']['name'][$i] ?? 'unknown') . ": " . $_FILES['asset_images']['error'][$i];
                }
            }
        }
    }
    
    // Update asset with new images if any were uploaded
    if (!empty($uploaded_images)) {
        // Get existing additional images
        $existing_images_query = $conn->prepare("SELECT additional_images FROM assets WHERE id = ?");
        $existing_images_query->bind_param('i', $asset_id);
        $existing_images_query->execute();
        $existing_result = $existing_images_query->get_result();
        $existing_data = $existing_result->fetch_assoc();
        $existing_images_query->close();
        
        $existing_images = [];
        if (!empty($existing_data['additional_images'])) {
            $existing_images = json_decode($existing_data['additional_images'], true) ?: [];
        }
        
        // Merge new images with existing ones (limit to 4 total)
        $all_images = array_merge($existing_images, $uploaded_images);
        $all_images = array_slice($all_images, 0, 4); // Keep only first 4 images
        
        // Update asset with new images
        $update_images_query = $conn->prepare("UPDATE assets SET additional_images = ? WHERE id = ?");
        $images_json = json_encode($all_images);
        $update_images_query->bind_param('si', $images_json, $asset_id);
        $update_images_query->execute();
        $update_images_query->close();
    }
    
    // Display upload errors if any
    if (!empty($upload_errors)) {
        $_SESSION['upload_errors'] = $upload_errors;
    }
    
    if ($existing_red_tag_check) {
        // UPDATE existing Red Tag
        $update_query = $conn->prepare("
            UPDATE red_tags SET 
                item_location = ?, 
                removal_reason = ?, 
                action = ?, 
                description = ?, 
                tagged_by = ?
            WHERE asset_id = ? AND iirup_id = ?
        ");
        
        $update_query->bind_param(
            'sssssii',
            $item_location,
            $removal_reason,
            $action,
            $description,
            $tagged_by,
            $asset_id,
            $iirup_id
        );
        
        if ($update_query->execute()) {
            // Update asset to set red_tagged = 1
            $update_asset_query = $conn->prepare("UPDATE assets SET red_tagged = 1 WHERE id = ?");
            $update_asset_query->bind_param('i', $asset_id);
            $update_asset_query->execute();
            $update_asset_query->close();
            // Log lifecycle RED_TAGGED event
            $notes = "Removal: {$removal_reason}; Action: {$action}; Location: {$item_location}";
            if (!empty($red_tag_id)) {
                logLifecycleEvent($asset_id, 'RED_TAGGED', 'red_tags', (int)$red_tag_id, null, null, null, null, $notes);
            } else {
                logLifecycleEvent($asset_id, 'RED_TAGGED', 'red_tags', null, null, null, null, null, $notes);
            }
            
            $_SESSION['success_message'] = 'Red Tag successfully updated!';
            $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 7;
            header("Location: create_red_tag.php?asset_id=" . $asset_id . "&iirup_id=" . $iirup_id . "&form_id=" . $form_id);
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating Red Tag: " . $conn->error;
        }
        $update_query->close();
    } else {
        // Create new Red Tag
        $insert_query = $conn->prepare("
            INSERT INTO red_tags (
                red_tag_number, asset_id, iirup_id, date_received, 
                tagged_by, item_location, description, removal_reason, 
                action, status
            ) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, 'Pending')
        ");
        
        $insert_query->bind_param(
            'siisssss',
            $red_tag_number,
            $asset_id,
            $iirup_id,
            $tagged_by,
            $item_location,
            $description,
            $removal_reason,
            $action
        );
        
        if ($insert_query->execute()) {
            $red_tag_id = $conn->insert_id;
            
            // Update asset to set red_tagged = 1
            $update_asset_query = $conn->prepare("UPDATE assets SET red_tagged = 1 WHERE id = ?");
            $update_asset_query->bind_param('i', $asset_id);
            $update_asset_query->execute();
            $update_asset_query->close();
            // Log lifecycle RED_TAGGED event
            $notes = "Removal: {$removal_reason}; Action: {$action}; Location: {$item_location}";
            logLifecycleEvent($asset_id, 'RED_TAGGED', 'red_tags', (int)$red_tag_id, null, null, null, null, $notes);
            
            // Get asset description for logging
            $asset_stmt = $conn->prepare("SELECT description FROM assets WHERE id = ?");
            $asset_stmt->bind_param("i", $asset_id);
            $asset_stmt->execute();
            $asset_result = $asset_stmt->get_result();
            $asset_data = $asset_result->fetch_assoc();
            $asset_stmt->close();
            
            $asset_description = $asset_data['description'] ?? 'Unknown Asset';
            
            // Log red tag creation
            $red_tag_details = "Created Red Tag: {$red_tag_number} for asset: {$asset_description} (Reason: {$removal_reason}, Action: {$action})";
            logUserActivity('CREATE', 'Red Tags', $red_tag_details, 'red_tags', $red_tag_id);
            
            $_SESSION['success_message'] = 'Red Tag has been successfully created!';
            $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 7;
            header("Location: create_red_tag.php?asset_id=" . $asset_id . "&iirup_id=" . $iirup_id . "&form_id=" . $form_id);
            exit();
        } else {
            // Log red tag creation failure
            logErrorActivity('Red Tags', "Failed to create Red Tag: {$red_tag_number} - " . $conn->error);
            
            $_SESSION['error_message'] = "Error creating Red Tag: " . $conn->error;
        }
        $insert_query->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Red Tag</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
    <style>
        .red-tag-form {
            max-width: 800px;
            margin: 20px auto;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .logo {
            max-height: 80px;
        }
        .title {
            text-align: center;
            flex-grow: 1;
        }
        .title h3 {
            color: #dc3545;
            margin: 0;
        }
        .red-tag-no {
            text-align: right;
            font-weight: bold;
        }
        .form-label {
            font-weight: 500;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .img-thumbnail {
            border: 2px solid #dee2e6;
            border-radius: 0.375rem;
        }
        .img-thumbnail:hover {
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php' ?>
    <div class="main">
        <?php include 'includes/topbar.php' ?>
        
        <div class="container py-4">
            <?php
            // Display success or error messages
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
                    . htmlspecialchars($_SESSION['success_message']) .
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
                    '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
                    . htmlspecialchars($_SESSION['error_message']) .
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
                    '</div>';
                unset($_SESSION['error_message']);
            }
            
            // Display upload errors if any
            if (isset($_SESSION['upload_errors'])) {
                echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
                echo '<strong>Image Upload Issues:</strong><ul class="mb-0">';
                foreach ($_SESSION['upload_errors'] as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['upload_errors']);
            }

            if ($existing_red_tag_check) {
                echo '<div class="alert alert-info">A Red Tag record already exists for this item. You can review and edit the details below.</div>';
            }
            ?>
            
            <form method="POST" class="red-tag-form" enctype="multipart/form-data">
                <!-- Header -->
                <div class="header">
                    <div class="logo">
                        <img id="municipalLogoImg" src="<?= $logo_path ?>" alt="Municipal Logo" style="height: 70px;">
                    </div>
                    <div class="title">
                        <div>Republic of the Philippines</div>
                        <div>Province of Sorsogon</div>
                        <div>Municipality of Pilar</div>
                        <h3>5S RED TAG</h3>
                    </div>
                    <div class="red-tag-no">
                        <div>Red Tag No.:</div>
                        <div><?= htmlspecialchars($red_tag_number) ?></div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Control No.:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($red_tag_number) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Received:</label>
                        <input type="date" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tagged By:</label>
                    <input type="text" class="form-control" name="tagged_by_name" id="tagged_by_name" list="userList"
                           value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" placeholder="Type to search user">
                    <input type="hidden" name="tagged_by" id="tagged_by" value="<?= (int)($user_id ?? 0) ?>">
                    <datalist id="userList">
                        <?php foreach ($users_list as $u): ?>
                            <option data-id="<?= (int)$u['id'] ?>" value="<?= htmlspecialchars($u['fullname']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-text">Start typing to select a user. You can adjust this before saving.</div>
                </div>

                <div class="mb-3">
                    <label for="item_location" class="form-label required">Item Location:</label>
                    <input type="text" class="form-control" id="item_location" name="item_location" 
                           value="<?= $existing_red_tag_check && $existing_red_tag_data ? htmlspecialchars($existing_red_tag_data['item_location']) : '' ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description:</label>
                    <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($asset['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="removal_reason" class="form-label required">Removal Reason:</label>
                    <select class="form-select" id="removal_reason" name="removal_reason" required>
                        <option value="">-- Select Reason --</option>
                        <option value="Unnecessary" <?= ($existing_red_tag_check && $existing_red_tag_data && $existing_red_tag_data['removal_reason'] == 'Unnecessary') ? 'selected' : '' ?>>Unnecessary</option>
                        <option value="Broken" <?= ($existing_red_tag_check && $existing_red_tag_data && $existing_red_tag_data['removal_reason'] == 'Broken') ? 'selected' : '' ?>>Broken</option>
                        <option value="Obsolete" <?= ($existing_red_tag_check && $existing_red_tag_data && $existing_red_tag_data['removal_reason'] == 'Obsolete') ? 'selected' : '' ?>>Obsolete</option>
                        <option value="Not in use" <?= ($existing_red_tag_check && $existing_red_tag_data && $existing_red_tag_data['removal_reason'] == 'Not in use') ? 'selected' : '' ?>>Not in use</option>
                        <?php 
                            $is_custom_removal = $existing_red_tag_check && $existing_red_tag_data && 
                                                !in_array($existing_red_tag_data['removal_reason'], ['Unnecessary', 'Broken', 'Obsolete', 'Not in use']);
                        ?>
                        <option value="Other" <?= $is_custom_removal ? 'selected' : '' ?>>Other (specify below)</option>
                    </select>
                    <div id="custom_removal_reason_div" class="mt-2" style="<?= $is_custom_removal ? 'display: block;' : 'display: none;' ?>">
                        <label for="custom_removal_reason" class="form-label">Specify Removal Reason:</label>
                        <input type="text" class="form-control" id="custom_removal_reason" name="custom_removal_reason" 
                               placeholder="Enter custom removal reason..." 
                               value="<?= $is_custom_removal ? htmlspecialchars($existing_red_tag_data['removal_reason']) : '' ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="action" class="form-label required">Action:</label>
                    <select class="form-select" id="action" name="action" required>
                        <option value="">-- Select Action --</option>
                        <option value="For Disposal" <?= ($existing_red_tag_check && $existing_red_tag_data && $existing_red_tag_data['action'] == 'For Disposal') ? 'selected' : '' ?>>For Disposal</option>
                        <option value="For Repair" <?= ($existing_red_tag_check && $existing_red_tag_data && $existing_red_tag_data['action'] == 'For Repair') ? 'selected' : '' ?>>For Repair</option>
                        <option value="For Relocation" <?= ($existing_red_tag_check && $existing_red_tag_data && $existing_red_tag_data['action'] == 'For Relocation') ? 'selected' : '' ?>>For Relocation</option>
                        <option value="For Donation" <?= ($existing_red_tag_check && $existing_red_tag_data && $existing_red_tag_data['action'] == 'For Donation') ? 'selected' : '' ?>>For Donation</option>
                        <?php 
                            $is_custom_action = $existing_red_tag_check && $existing_red_tag_data && 
                                              !in_array($existing_red_tag_data['action'], ['For Disposal', 'For Repair', 'For Relocation', 'For Donation']);
                        ?>
                        <option value="Other" <?= $is_custom_action ? 'selected' : '' ?>>Other (specify below)</option>
                    </select>
                    <div id="custom_action_div" class="mt-2" style="<?= $is_custom_action ? 'display: block;' : 'display: none;' ?>">
                        <label for="custom_action" class="form-label">Specify Action:</label>
                        <input type="text" class="form-control" id="custom_action" name="custom_action" 
                               placeholder="Enter custom action..." 
                               value="<?= $is_custom_action ? htmlspecialchars($existing_red_tag_data['action']) : '' ?>">
                    </div>
                </div>

                <!-- Asset Images Upload Section -->
                <div class="mb-4">
                    <label for="asset_images" class="form-label">Asset Images (Optional):</label>
                    <input type="file" class="form-control" id="asset_images" name="asset_images[]" 
                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple>
                    <div class="form-text">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Upload up to 4 images (JPEG, PNG, GIF, WebP). Maximum file size: 5MB each.
                        </small>
                    </div>
                    
                    <!-- Image Preview Container -->
                    <div id="image_preview_container" class="mt-3" style="display: none;">
                        <label class="form-label">Preview:</label>
                        <div id="image_previews" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    
                    <?php
                    // Display existing images if any
                    $existing_images_query = $conn->prepare("SELECT additional_images FROM assets WHERE id = ?");
                    $existing_images_query->bind_param('i', $asset_id);
                    $existing_images_query->execute();
                    $existing_result = $existing_images_query->get_result();
                    $existing_data = $existing_result->fetch_assoc();
                    $existing_images_query->close();
                    
                    $existing_images = [];
                    if (!empty($existing_data['additional_images'])) {
                        $existing_images = json_decode($existing_data['additional_images'], true) ?: [];
                    }
                    
                    if (!empty($existing_images)): ?>
                        <div class="mt-3">
                            <label class="form-label">Current Images:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($existing_images as $index => $image): ?>
                                    <div class="position-relative">
                                        <img src="../img/assets/<?= htmlspecialchars($image) ?>" 
                                             alt="Asset Image <?= $index + 1 ?>" 
                                             class="img-thumbnail" 
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                        <button type="button" 
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle" 
                                                style="width: 25px; height: 25px; padding: 0; margin: -5px;" 
                                                onclick="removeExistingImage('<?= htmlspecialchars($image) ?>', this)" 
                                                title="Remove image">
                                            <i class="bi bi-x" style="font-size: 12px;"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <?php if ($existing_red_tag_check): ?>
                        <a href="print_red_tag.php?id=<?= $red_tag_id ?>" target="_blank" class="btn btn-primary me-2">
                            <i class="bi bi-printer"></i> Print Red Tag
                        </a>
                        <?php $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 7; ?>
                        <a href="view_iirup.php?id=<?= $iirup_id ?>&form_id=<?= $form_id ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Back to IIRUP
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Update Red Tag
                        </button>
                    <?php else: ?>
                        <?php $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 7; ?>
                        <a href="view_iirup.php?id=<?= $iirup_id ?>&form_id=<?= $form_id ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-tag"></i> Create Red Tag
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const removalReasonSelect = document.getElementById('removal_reason');
            const customRemovalReasonDiv = document.getElementById('custom_removal_reason_div');
            const customRemovalReasonInput = document.getElementById('custom_removal_reason');
            
            const actionSelect = document.getElementById('action');
            const customActionDiv = document.getElementById('custom_action_div');
            const customActionInput = document.getElementById('custom_action');
            
            const imageInput = document.getElementById('asset_images');
            const previewContainer = document.getElementById('image_preview_container');
            const previewsDiv = document.getElementById('image_previews');
            // Tagged By mapping: map selected name to user id
            const taggedByName = document.getElementById('tagged_by_name');
            const taggedById = document.getElementById('tagged_by');
            const userOptions = document.querySelectorAll('#userList option');
            function syncTaggedById() {
                const val = taggedByName.value;
                let found = '';
                userOptions.forEach(opt => { if (opt.value === val) { found = opt.getAttribute('data-id') || ''; } });
                if (found) { taggedById.value = found; }
            }
            if (taggedByName) {
                taggedByName.addEventListener('change', syncTaggedById);
                taggedByName.addEventListener('input', syncTaggedById);
            }
            
            // Function to toggle custom removal reason input
            function toggleCustomRemovalReason() {
                if (removalReasonSelect.value === 'Other') {
                    customRemovalReasonDiv.style.display = 'block';
                    customRemovalReasonInput.required = true;
                } else {
                    customRemovalReasonDiv.style.display = 'none';
                    customRemovalReasonInput.required = false;
                    customRemovalReasonInput.value = '';
                }
            }
            
            // Function to toggle custom action input
            function toggleCustomAction() {
                if (actionSelect.value === 'Other') {
                    customActionDiv.style.display = 'block';
                    customActionInput.required = true;
                } else {
                    customActionDiv.style.display = 'none';
                    customActionInput.required = false;
                    customActionInput.value = '';
                }
            }
            
            // Function to handle image preview
            function handleImagePreview() {
                const files = imageInput.files;
                previewsDiv.innerHTML = '';
                
                if (files.length > 0) {
                    if (files.length > 4) {
                        alert('Maximum 4 images allowed. Only the first 4 will be processed.');
                    }
                    
                    previewContainer.style.display = 'block';
                    
                    for (let i = 0; i < Math.min(files.length, 4); i++) {
                        const file = files[i];
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const previewDiv = document.createElement('div');
                            previewDiv.className = 'position-relative';
                            previewDiv.innerHTML = `
                                <img src="${e.target.result}" 
                                     alt="Preview ${i + 1}" 
                                     class="img-thumbnail" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 bg-dark text-white rounded-circle" 
                                     style="width: 20px; height: 20px; font-size: 10px; display: flex; align-items: center; justify-content: center; margin: -5px;">
                                    ${i + 1}
                                </div>
                            `;
                            previewsDiv.appendChild(previewDiv);
                        };
                        
                        reader.readAsDataURL(file);
                    }
                } else {
                    previewContainer.style.display = 'none';
                }
            }
            
            // Add event listeners
            removalReasonSelect.addEventListener('change', toggleCustomRemovalReason);
            actionSelect.addEventListener('change', toggleCustomAction);
            imageInput.addEventListener('change', handleImagePreview);
            
            // Initialize on page load (for existing red tags)
            toggleCustomRemovalReason();
            toggleCustomAction();
            
            // Auto-select the first option in select elements if they're required and have options
            const selectElements = document.querySelectorAll('select[required]');
            selectElements.forEach(select => {
                if (select.value === '' && select.options.length > 0) {
                    select.selectedIndex = 0;
                }
            });
        });
        
        // Function to remove existing images
        function removeExistingImage(imageName, buttonElement) {
            if (confirm('Are you sure you want to remove this image?')) {
                // Send AJAX request to remove the image
                fetch('remove_asset_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `asset_id=<?= $asset_id ?>&image_name=${encodeURIComponent(imageName)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the image element from DOM
                        buttonElement.parentElement.remove();
                    } else {
                        alert('Failed to remove image: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the image.');
                });
            }
        }
    </script>
</body>
</html>
