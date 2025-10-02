<?php
require_once '../connect.php';

header('Content-Type: application/json');

// Check if office name is provided
if (!isset($_GET['office']) || empty($_GET['office'])) {
    echo json_encode(['error' => 'Office name is required']);
    exit();
}

$office_name = trim($_GET['office']);

try {
    // First, get the office ID from the office name
    $office_stmt = $conn->prepare("SELECT id FROM offices WHERE office_name = ? LIMIT 1");
    $office_stmt->bind_param('s', $office_name);
    $office_stmt->execute();
    $office_result = $office_stmt->get_result();
    
    if ($office_result->num_rows === 0) {
        echo json_encode(['error' => 'Office not found']);
        exit();
    }
    
    $office = $office_result->fetch_assoc();
    $office_id = $office['id'];
    $office_stmt->close();
    
    // Get employees from that office with permanent status
    $emp_stmt = $conn->prepare("SELECT employee_id, name, employee_no FROM employees WHERE office_id = ? AND status = 'permanent' ORDER BY name ASC");
    $emp_stmt->bind_param('i', $office_id);
    $emp_stmt->execute();
    $emp_result = $emp_stmt->get_result();
    
    $employees = [];
    while ($row = $emp_result->fetch_assoc()) {
        $employees[] = [
            'employee_id' => $row['employee_id'],
            'name' => $row['name'],
            'employee_no' => $row['employee_no']
        ];
    }
    
    $emp_stmt->close();
    
    echo json_encode([
        'success' => true,
        'office_id' => $office_id,
        'office_name' => $office_name,
        'employees' => $employees
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
