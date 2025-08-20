<?php
require_once '../connect.php';
session_start();

if (isset($_POST['import'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === 0) {
        $fileName = $_FILES['csv_file']['tmp_name'];
        
        if (($handle = fopen($fileName, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle, 1000, ",");

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $name        = $conn->real_escape_string($data[0]);
                $office_name = $conn->real_escape_string($data[1]);
                $status      = $conn->real_escape_string($data[2]);

                // Lookup office_id based on office_name
                $office_id = null;
                $stmt = $conn->prepare("SELECT id FROM offices WHERE office_name = ?");
                $stmt->bind_param("s", $office_name);
                $stmt->execute();
                $stmt->bind_result($office_id);
                $stmt->fetch();
                $stmt->close();

                // Only insert if office was found
                if ($office_id) {
                    // --- Generate new employee number like EMP0001 ---
                    $result = $conn->query("SELECT employee_no FROM employees ORDER BY employee_id DESC LIMIT 1");
                    if ($row = $result->fetch_assoc()) {
                        $lastNo = intval(substr($row['employee_no'], 3)); // remove "EMP"
                        $newNo = $lastNo + 1;
                    } else {
                        $newNo = 1;
                    }
                    $employee_no = "EMP" . str_pad($newNo, 4, "0", STR_PAD_LEFT);

                    // Insert employee with generated employee_no
                    $stmt = $conn->prepare("INSERT INTO employees (employee_no, name, office_id, status, date_added) 
                                             VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssis", $employee_no, $name, $office_id, $status);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            fclose($handle);
        }
    }
    header("Location: employees.php?import=success");
    exit();
}
?>
