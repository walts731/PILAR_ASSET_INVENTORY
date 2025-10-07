<?php
// Step-by-step debug version
error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug_steps = [];

try {
    $debug_steps[] = "Step 1: Script started";
    
    // Test database connection
    $debug_steps[] = "Step 2: Testing database connection";
    require_once '../connect.php';
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    $debug_steps[] = "Step 3: Database connected successfully";
    
    // Test session
    session_start();
    $debug_steps[] = "Step 4: Session started";
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not logged in");
    }
    $debug_steps[] = "Step 5: User session validated";
    
    // Check POST data
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Not a POST request");
    }
    $debug_steps[] = "Step 6: POST request confirmed";
    
    // Check required POST fields
    $required_fields = ['accountable_person', 'office', 'category', 'date_received'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    $debug_steps[] = "Step 7: Required fields validated";
    
    // Check assets data
    if (!isset($_POST['assets']) || !is_array($_POST['assets'])) {
        throw new Exception("No assets data provided");
    }
    $debug_steps[] = "Step 8: Assets data found - " . count($_POST['assets']) . " assets";
    
    // Test TagFormatHelper
    $debug_steps[] = "Step 9: Testing TagFormatHelper";
    if (file_exists('../includes/tag_format_helper.php')) {
        require_once '../includes/tag_format_helper.php';
        $tagHelper = new TagFormatHelper($conn);
        $debug_steps[] = "Step 10: TagFormatHelper loaded successfully";
    } else {
        $debug_steps[] = "Step 10: TagFormatHelper file not found - will use fallback";
    }
    
    // Test MR number generation
    $debug_steps[] = "Step 11: Testing MR number generation";
    $year = date('Y');
    $stmt = $conn->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING(mr_no, 9) AS UNSIGNED)), 0) + 1 as next_seq 
                           FROM mr_details 
                           WHERE mr_no LIKE ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare MR number query: " . $conn->error);
    }
    
    $pattern = "MR-{$year}-%";
    $stmt->bind_param('s', $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_seq = $row['next_seq'];
    $stmt->close();
    
    $mr_number = sprintf("MR-%s-%04d", $year, $next_seq);
    $debug_steps[] = "Step 12: MR number generated: $mr_number";
    
    // Test assets table structure
    $debug_steps[] = "Step 13: Checking assets table structure";
    $result = $conn->query("SHOW COLUMNS FROM assets");
    if (!$result) {
        throw new Exception("Cannot check assets table: " . $conn->error);
    }
    
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    $debug_steps[] = "Step 14: Assets table has " . count($columns) . " columns";
    
    // Test mr_details table structure
    $debug_steps[] = "Step 15: Checking mr_details table structure";
    $result = $conn->query("SHOW COLUMNS FROM mr_details");
    if (!$result) {
        throw new Exception("Cannot check mr_details table: " . $conn->error);
    }
    
    $mr_columns = [];
    while ($row = $result->fetch_assoc()) {
        $mr_columns[] = $row['Field'];
    }
    $debug_steps[] = "Step 16: MR details table has " . count($mr_columns) . " columns";
    
    $debug_steps[] = "Step 17: All tests passed successfully!";
    
    echo json_encode([
        'success' => true,
        'message' => 'Debug completed successfully',
        'debug_steps' => $debug_steps,
        'assets_columns' => $columns,
        'mr_columns' => $mr_columns,
        'post_data_keys' => array_keys($_POST),
        'assets_count' => count($_POST['assets'])
    ]);
    
} catch (Exception $e) {
    $debug_steps[] = "ERROR: " . $e->getMessage();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_steps' => $debug_steps,
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
} catch (Error $e) {
    $debug_steps[] = "FATAL ERROR: " . $e->getMessage();
    
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'debug_steps' => $debug_steps,
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
}
?>
