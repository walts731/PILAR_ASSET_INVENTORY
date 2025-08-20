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
                $employee_no = $conn->real_escape_string($data[0]);
                $name = $conn->real_escape_string($data[1]);
                $office_id = (int)$data[2];
                $status = $conn->real_escape_string($data[3]);
                $image = $conn->real_escape_string($data[4]);

                $sql = "INSERT INTO employees (employee_no, name, office_id, status, image, date_added)
                        VALUES ('$employee_no', '$name', '$office_id', '$status', '$image', NOW())";
                $conn->query($sql);
            }
            fclose($handle);
        }
    }
    header("Location: employees.php?import=success");
    exit();
}
?>
