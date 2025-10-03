<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: saved_itr.php");
    exit();
}

try {
    // Get form data
    $itr_id = intval($_POST['itr_id'] ?? 0);
    $form_id = intval($_POST['form_id'] ?? 0);
    
    if ($itr_id <= 0) {
        throw new Exception('Invalid ITR ID');
    }

    // Sanitize input data
    $entity_name = trim($_POST['entity_name'] ?? '');
    $fund_cluster = trim($_POST['fund_cluster'] ?? '');
    $transfer_type = trim($_POST['transfer_type'] ?? '');
    $transfer_type_other = trim($_POST['transfer_type_other'] ?? '');
    $reason_for_transfer = trim($_POST['reason_for_transfer'] ?? '');
    
    // Signature fields
    $approved_by = trim($_POST['approved_by'] ?? '');
    $approved_designation = trim($_POST['approved_designation'] ?? '');
    $approved_date = $_POST['approved_date'] ?? null;
    $released_by = trim($_POST['released_by'] ?? '');
    $released_designation = trim($_POST['released_designation'] ?? '');
    $released_date = $_POST['released_date'] ?? null;

    // Validate required fields
    if (empty($entity_name)) {
        throw new Exception('Entity Name is required');
    }
    if (empty($fund_cluster)) {
        throw new Exception('Fund Cluster is required');
    }
    if (empty($transfer_type)) {
        throw new Exception('Transfer Type is required');
    }
    if (empty($reason_for_transfer)) {
        throw new Exception('Reason for Transfer is required');
    }

    // Handle "Others" transfer type
    $final_transfer_type = $transfer_type;
    if ($transfer_type === 'Others' && !empty($transfer_type_other)) {
        $final_transfer_type = $transfer_type_other;
    }

    // Handle empty date fields
    $approved_date = !empty($approved_date) ? $approved_date : null;
    $released_date = !empty($released_date) ? $released_date : null;

    // Begin transaction
    $conn->begin_transaction();

    // Update ITR form
    $update_stmt = $conn->prepare("UPDATE itr_form SET 
        entity_name = ?, 
        fund_cluster = ?, 
        transfer_type = ?, 
        reason_for_transfer = ?, 
        approved_by = ?, 
        approved_designation = ?, 
        approved_date = ?, 
        released_by = ?, 
        released_designation = ?, 
        released_date = ?
        WHERE itr_id = ?");

    $update_stmt->bind_param("ssssssssssi", 
        $entity_name,
        $fund_cluster,
        $final_transfer_type,
        $reason_for_transfer,
        $approved_by,
        $approved_designation,
        $approved_date,
        $released_by,
        $released_designation,
        $released_date,
        $itr_id
    );

    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update ITR: ' . $update_stmt->error);
    }

    $update_stmt->close();

    // Commit transaction
    $conn->commit();

    // Set success message
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'ITR updated successfully!'
    ];

    // Redirect back to view page
    $redirect_url = "view_itr.php?id=" . $itr_id;
    if ($form_id > 0) {
        $redirect_url .= "&form_id=" . $form_id;
    }
    
    header("Location: " . $redirect_url);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Set error message
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => 'Error updating ITR: ' . $e->getMessage()
    ];

    // Redirect back to view page
    $redirect_url = "view_itr.php?id=" . ($itr_id ?? 0);
    if (isset($form_id) && $form_id > 0) {
        $redirect_url .= "&form_id=" . $form_id;
    }
    
    header("Location: " . $redirect_url);
    exit();
}
?>
