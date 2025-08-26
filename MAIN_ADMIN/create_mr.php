<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
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

$item_id = isset($_GET['item_id']) ? $_GET['item_id'] : null;
$asset_data = [];
$office_name = '';
$asset_details = [];

// Check if MR for this item_id already exists in the mr_details table
$existing_mr_check = false;
if ($item_id) {
    $stmt_check = $conn->prepare("SELECT * FROM mr_details WHERE item_id = ?");
    $stmt_check->bind_param("i", $item_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $existing_mr_check = true;
    }

    $stmt_check->close();
}

// Fetch asset_id from ics_items table based on item_id
$asset_id = null;
if ($item_id) {
    $stmt = $conn->prepare("SELECT asset_id FROM ics_items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $asset_data = $result->fetch_assoc();
        $asset_id = $asset_data['asset_id']; // Fetch asset_id from ics_items
    }
    $stmt->close();
}
// Generate Inventory Tag
$inventory_tag = '';
if ($asset_id) {
    // Example logic for generating an inventory tag
    $prefix = "PS"; // Asset type prefix
    $size = "5S"; // Size or category, you can modify this based on your asset's size
    $department_code = "03"; // Department or office number
    $factory_code = "F02"; // Factory or location code
    $unique_id = str_pad($item_id, 2, "0", STR_PAD_LEFT); // Use item_id or another unique field for the last part of the tag

    // Concatenate to form the full inventory tag
    $inventory_tag = "No. " . $prefix . "-" . $size . "-" . $department_code . "-" . $factory_code . "-" . $unique_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$existing_mr_check) {
    // Collect form data
    $item_id_form = $_POST['item_id'] ?? null;
    $office_location = $_POST['office_location'];
    $description = $_POST['description'];
    $model_no = $_POST['model_no'];
    $serial_no = $_POST['serial_no'];
    $serviceable = isset($_POST['serviceable']) ? 1 : 0;
    $unserviceable = isset($_POST['unserviceable']) ? 1 : 0;
    $unit_quantity = $_POST['unit_quantity'];
    $unit = $_POST['unit'];
    $acquisition_date = $_POST['acquisition_date'];
    $acquisition_cost = $_POST['acquisition_cost'];
    $person_accountable_name = $_POST['person_accountable_name']; // Visible name
    $employee_id = $_POST['employee_id']; // Hidden employee ID
    $acquired_date = $_POST['acquired_date'];
    $counted_date = $_POST['counted_date'];

    // --- Prioritize updating the employee_id in the assets table first ---
    if ($employee_id) {
        $stmt_update_employee = $conn->prepare("UPDATE assets SET employee_id = ? WHERE id = ?");
        $stmt_update_employee->bind_param("ii", $employee_id, $asset_id);
        if (!$stmt_update_employee->execute()) {
            $_SESSION['error_message'] = "Error updating employee_id in assets: " . $stmt_update_employee->error;
            $stmt_update_employee->close();
            header("Location: create_mr.php?item_id=" . $item_id_form);
            exit();
        }
        $stmt_update_employee->close();
    }

    // Insert into mr_details
    $stmt_insert = $conn->prepare("INSERT INTO mr_details 
        (item_id, asset_id, office_location, description, model_no, serial_no, serviceable, unserviceable, unit_quantity, unit, acquisition_date, acquisition_cost, person_accountable, acquired_date, counted_date, inventory_tag) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt_insert->bind_param("iissssiiisssssss",
        $item_id_form, $asset_id, $office_location, $description, $model_no, $serial_no,
        $serviceable, $unserviceable, $unit_quantity, $unit, $acquisition_date, $acquisition_cost,
        $person_accountable_name, $acquired_date, $counted_date, $inventory_tag
    );

    if ($stmt_insert->execute()) {
        $_SESSION['success_message'] = "MR Details successfully recorded!";
        header("Location: create_mr.php?item_id=" . $item_id_form);
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt_insert->error;
    }
    $stmt_insert->close();
}

// --- End of PHP code for form submission and insertion ---

// Fetch data from the `ics_items` table using the item_id (This part remains the same as your original code)
if ($item_id) {
    $stmt = $conn->prepare("SELECT item_id, ics_id, asset_id, ics_no, quantity, unit, unit_cost, total_cost, description, item_no, estimated_useful_life, created_at FROM ics_items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $asset_data = $result->fetch_assoc();
        $asset_id = $asset_data['asset_id'];

        // Fetch the office_name from the `offices` table based on office_id in the assets table
        $stmt_offices = $conn->prepare("SELECT office_name FROM offices WHERE id = (SELECT office_id FROM assets WHERE id = ?)");
        $stmt_offices->bind_param("i", $asset_id);
        $stmt_offices->execute();
        $result_offices = $stmt_offices->get_result();

        if ($result_offices->num_rows > 0) {
            $office_data = $result_offices->fetch_assoc();
            $office_name = $office_data['office_name']; // Store the office_name
        }

        $stmt_offices->close();

        // Fetch data from the `assets` table based on the asset_id
        $stmt_assets = $conn->prepare("SELECT id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand FROM assets WHERE id = ?");
        $stmt_assets->bind_param("i", $asset_id);
        $stmt_assets->execute();
        $result_assets = $stmt_assets->get_result();

        if ($result_assets->num_rows > 0) {
            $asset_details = $result_assets->fetch_assoc();
        }

        $stmt_assets->close();
    }

    $stmt->close();
}

// Fetch the employee's name based on the employee_id
$person_accountable_name = '';
if (isset($asset_details['employee_id'])) {
    $employee_id = $asset_details['employee_id'];
    $stmt_employee = $conn->prepare("SELECT name FROM employees WHERE employee_id = ?");
    $stmt_employee->bind_param("i", $employee_id);
    $stmt_employee->execute();
    $result_employee = $stmt_employee->get_result();

    if ($result_employee->num_rows > 0) {
        $employee_data = $result_employee->fetch_assoc();
        $person_accountable_name = $employee_data['name'];  // Get the name of the person accountable
    }

    $stmt_employee->close();
}


// Fetch employees for datalist
$employees = [];
$sql_employees = "SELECT employee_id, employee_no, name FROM employees";
$result_employees = $conn->query($sql_employees);

if ($result_employees && $result_employees->num_rows > 0) {
    while ($row = $result_employees->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Assuming you are inserting or updating assets table somewhere
$stmt_assets = $conn->prepare("UPDATE assets SET employee_id = ? WHERE id = ?");
$stmt_assets->bind_param("ii", $employee_id, $asset_id);  // Update with employee_id
$stmt_assets->execute();
$stmt_assets->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create MR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <!-- Header: Logo, QR, and GOV LABEL -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Municipal Logo -->
            <img id="municipalLogoImg" src="<?= $logo_path ?>" alt="Municipal Logo" style="height: 70px;">

            <!-- Government Label -->
            <div class="text-center flex-grow-1">
                <h6 class="m-0 text-uppercase fw-bold">Government Property</h6>
            </div>

            <!-- Inventory Tag Display -->
            <div class="text-center">
                <p class="fw-bold">Inventory Tag: <?= $inventory_tag ?></p> <!-- Display the inventory tag here -->
            </div>

            <!-- QR Code -->
            <img id="viewQrCode" src="../img/<?= isset($asset_details['qr_code']) ? $asset_details['qr_code'] : '' ?>" alt="QR Code" style="height: 70px;">
        </div>

        <!-- Form for MR Asset -->
        <div class="container mt-4">
            <?php
            // Display success or error messages
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }

            if ($existing_mr_check) {
                echo '<div class="alert alert-warning">MR Details for this item already exist. You cannot fill the form again.</div>';
            }
            ?>

            <form method="post" action="">
                <!-- Hidden input for item_id -->
                <input type="hidden" name="item_id" value="<?= htmlspecialchars($item_id) ?>">

                <!-- Office Location (Centered) -->
                <div class="row mb-3">
                    <div class="col-md-6 offset-md-3">
                        <label for="office_location" class="form-label">Office Location</label>
                        <input type="text" class="form-control" name="office_location" value="<?= isset($office_name) ? htmlspecialchars($office_name) : '' ?>" required>
                    </div>
                </div>

                <!-- Description (Full Width) -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" value="<?= isset($asset_details['description']) ? htmlspecialchars($asset_details['description']) : '' ?>" required>
                    </div>
                </div>

                <!-- Model No and Serial No -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="model_no" class="form-label">Model No</label>
                        <input type="text" class="form-control" name="model_no" value="<?= isset($asset_details['model']) ? htmlspecialchars($asset_details['model']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="serial_no" class="form-label">Serial No</label>
                        <input type="text" class="form-control" name="serial_no" value="<?= isset($asset_details['serial_no']) ? htmlspecialchars($asset_details['serial_no']) : '' ?>">
                    </div>
                </div>

                <!-- Serviceable and Unserviceable -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Serviceable</label>
                        <input type="checkbox" name="serviceable" value="1" <?= (isset($asset_data['quantity']) && $asset_data['quantity'] > 0) ? 'checked' : '' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unserviceable</label>
                        <input type="checkbox" name="unserviceable" value="1" <?= (isset($asset_data['quantity']) && $asset_data['quantity'] == 0) ? 'checked' : '' ?>>
                    </div>
                </div>

                <!-- Unit Quantity and Acquisition Date -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="unit_quantity" class="form-label">Unit Quantity</label>
                        <div class="d-flex">
                            <input type="number" class="form-control" name="unit_quantity" value="<?= isset($asset_details['quantity']) ? htmlspecialchars($asset_details['quantity']) : '' ?>" required>
                            <select name="unit" class="form-select" required>
                                <option value="kg" <?= (isset($asset_details['unit']) && $asset_details['unit'] == 'kg') ? 'selected' : '' ?>>kg</option>
                                <option value="pcs" <?= (isset($asset_details['unit']) && $asset_details['unit'] == 'pcs') ? 'selected' : '' ?>>pcs</option>
                                <option value="liter" <?= (isset($asset_details['unit']) && $asset_details['unit'] == 'liter') ? 'selected' : '' ?>>liter</option>
                                <!-- Add more unit options here as needed -->
                            </select>
                        </div>
                    </div>

                    <!-- Acquisition Date and Acquisition Cost (Both in col-md-3) -->
                    <div class="col-md-3">
                        <label for="acquisition_date" class="form-label">Acquisition Date</label>
                        <input type="date" class="form-control" name="acquisition_date" value="<?= isset($asset_details['acquisition_date']) ? htmlspecialchars($asset_details['acquisition_date']) : '' ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="acquisition_cost" class="form-label">Acquisition Cost</label>
                        <input type="number" class="form-control" name="acquisition_cost" step="0.01" value="<?= isset($asset_details['value']) ? htmlspecialchars($asset_details['value']) : '' ?>" required>
                    </div>
                </div>

                <!-- Person Accountable -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="person_accountable" class="form-label">Person Accountable</label>

                        <!-- Visible Input for Name -->
                        <input type="text" class="form-control" name="person_accountable_name" id="person_accountable"
                            list="employeeList" placeholder="Type to search employee" autocomplete="off"
                            value="<?= htmlspecialchars($person_accountable_name) ?>">

                        <!-- Hidden Input for Employee ID -->
                        <input type="hidden" name="employee_id" id="employee_id" value="<?= isset($asset_details['employee_id']) ? htmlspecialchars($asset_details['employee_id']) : '' ?>">

                        <!-- Datalist -->
                        <datalist id="employeeList">
                            <?php foreach ($employees as $emp): ?>
                                <option data-id="<?= $emp['employee_id'] ?>" value="<?= htmlspecialchars($emp['name']) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <!-- Acquired Date and Counted Date -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="acquired_date" class="form-label">Acquired Date</label>
                        <input type="date" class="form-control" name="acquired_date" value="<?= isset($asset_details['last_updated']) ? htmlspecialchars($asset_details['last_updated']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="counted_date" class="form-label">Counted Date</label>
                        <input type="date" class="form-control" name="counted_date">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" <?= $existing_mr_check ? 'disabled' : '' ?>>
                    <?= $existing_mr_check ? 'Already Created' : 'Submit' ?>
                </button>

                <!-- Print MR Button (Conditional Display) -->
                <?php if ($existing_mr_check): ?>
                    <a href="print_mr.php?item_id=<?= htmlspecialchars($item_id) ?>" class="btn btn-info ml-2">Print MR</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        document.getElementById('person_accountable').addEventListener('input', function() {
            const inputVal = this.value;
            const options = document.querySelectorAll('#employeeList option');
            let selectedId = '';

            options.forEach(option => {
                if (option.value === inputVal) {
                    selectedId = option.getAttribute('data-id');
                }
            });

            document.getElementById('employee_id').value = selectedId;
        });
    </script>

</body>

</html>