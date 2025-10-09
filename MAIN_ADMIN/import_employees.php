<?php
require_once '../connect.php';
session_start();

if (isset($_POST['import'])) {
    $duplicates = []; // store duplicate names

    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === 0) {
        $fileName = $_FILES['csv_file']['tmp_name'];
        
        if (($handle = fopen($fileName, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle, 1000, ",");

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Basic columns: name, office_name, status
                $name        = $conn->real_escape_string(trim($data[0] ?? ''));
                $office_name = $conn->real_escape_string(trim($data[1] ?? ''));
                $status      = $conn->real_escape_string(trim($data[2] ?? 'permanent'));
                // Optional 4th column: email
                $emailCsv    = isset($data[3]) ? $conn->real_escape_string(trim($data[3])) : null;

                // Lookup office_id based on office_name
                $office_id = null;
                $stmt = $conn->prepare("SELECT id FROM offices WHERE office_name = ?");
                $stmt->bind_param("s", $office_name);
                $stmt->execute();
                $stmt->bind_result($office_id);
                $stmt->fetch();
                $stmt->close();

                if ($office_id) {
                    // --- Check for duplicate employee name ---
                    $check = $conn->prepare("SELECT COUNT(*) FROM employees WHERE name = ?");
                    $check->bind_param("s", $name);
                    $check->execute();
                    $check->bind_result($exists);
                    $check->fetch();
                    $check->close();

                    if ($exists > 0) {
                        $duplicates[] = $name; // store duplicates
                        continue;
                    }

                    // --- Generate new employee number like EMP0001 ---
                    $result = $conn->query("SELECT employee_no FROM employees ORDER BY employee_id DESC LIMIT 1");
                    if ($row = $result->fetch_assoc()) {
                        $lastNo = intval(substr($row['employee_no'], 3));
                        $newNo = $lastNo + 1;
                    } else {
                        $newNo = 1;
                    }
                    $employee_no = "EMP" . str_pad($newNo, 4, "0", STR_PAD_LEFT);

                    // Insert employee (with optional email if column exists)
                    $hasEmailCol = false;
                    if ($rs = $conn->query("SHOW COLUMNS FROM employees LIKE 'email'")) {
                        $hasEmailCol = $rs->num_rows > 0; $rs->close();
                    }
                    if ($hasEmailCol) {
                        $stmt = $conn->prepare("INSERT INTO employees (employee_no, name, email, office_id, status, date_added) 
                                                 VALUES (?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("sssis", $employee_no, $name, $emailCsv, $office_id, $status);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO employees (employee_no, name, office_id, status, date_added) 
                                                 VALUES (?, ?, ?, ?, NOW())");
                        $stmt->bind_param("ssis", $employee_no, $name, $office_id, $status);
                    }
                    $stmt->execute();
                    $stmt->close();
                }
            }
            fclose($handle);
        }
    }

    // Redirect with duplicates if any
    if (!empty($duplicates)) {
        $dupString = implode(",", $duplicates);
        header("Location: employees.php?import=duplicates&names=" . urlencode($dupString));
    } else {
        header("Location: employees.php?import=success");
    }
    exit();
}
?>
