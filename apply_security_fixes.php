<?php
// Security fixes for borrowing and returning process
// Run this script to apply additional security improvements

require_once 'connect.php';

echo "Applying security fixes...\n";

// 1. Add proper XSS protection function
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// 2. Add CSRF token generation function
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 3. Add CSRF token validation function
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 4. Clean up any orphaned or invalid data
echo "Cleaning up invalid borrow requests...\n";

// Remove requests with invalid quantities
$cleanup1 = $conn->query("DELETE FROM borrow_requests WHERE quantity <= 0");
echo "Removed " . $conn->affected_rows . " requests with invalid quantities\n";

// Remove requests referencing non-existent assets
$cleanup2 = $conn->query("
    DELETE br FROM borrow_requests br 
    LEFT JOIN assets a ON br.asset_id = a.id 
    WHERE a.id IS NULL
");
echo "Removed " . $conn->affected_rows . " requests with invalid asset references\n";

// Remove requests referencing non-existent users
$cleanup3 = $conn->query("
    DELETE br FROM borrow_requests br 
    LEFT JOIN users u ON br.user_id = u.id 
    WHERE u.id IS NULL
");
echo "Removed " . $conn->affected_rows . " requests with invalid user references\n";

// Remove requests referencing non-existent offices
$cleanup4 = $conn->query("
    DELETE br FROM borrow_requests br 
    LEFT JOIN offices o ON br.office_id = o.id 
    WHERE o.id IS NULL
");
echo "Removed " . $conn->affected_rows . " requests with invalid office references\n";

// 5. Update any inconsistent asset statuses
echo "Fixing asset status inconsistencies...\n";

// Set assets to 'available' if they have quantity > 0 but are marked as 'borrowed'
$status_fix1 = $conn->query("
    UPDATE assets 
    SET status = 'available' 
    WHERE quantity > 0 AND status = 'borrowed'
    AND id NOT IN (
        SELECT DISTINCT asset_id 
        FROM borrow_requests 
        WHERE status = 'borrowed'
    )
");
echo "Fixed " . $conn->affected_rows . " assets marked as borrowed but with available quantity\n";

// Set assets to 'borrowed' if they have quantity = 0 but are marked as 'available'
$status_fix2 = $conn->query("
    UPDATE assets 
    SET status = 'borrowed' 
    WHERE quantity = 0 AND status = 'available'
");
echo "Fixed " . $conn->affected_rows . " assets with zero quantity but marked as available\n";

echo "Security fixes applied successfully!\n";
echo "\nNext steps:\n";
echo "1. Run database_improvements.sql to add constraints and indexes\n";
echo "2. Update your PHP files to use the new security functions\n";
echo "3. Test the borrowing and returning process\n";
?>
