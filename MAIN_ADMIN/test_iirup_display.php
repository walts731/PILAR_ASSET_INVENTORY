<!DOCTYPE html>
<html>
<head>
    <title>Test IIRUP Temp Items Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Test IIRUP Temp Items Display</h2>
        
        <?php
        session_start();
        require_once '../connect.php';
        
        // Set dummy session for testing
        $_SESSION['user_id'] = 1;
        $_SESSION['id'] = 1;
        
        // Include helper functions
        include 'load_temp_iirup_items.php';
        
        // Fetch temp items
        $temp_items = getTempIIRUPItems($conn);
        $preselected_asset = null; // No preselected asset for this test
        
        echo "<div class='alert alert-info'>";
        echo "<strong>Debug Info:</strong><br>";
        echo "Found " . count($temp_items) . " temp items<br>";
        if (!empty($temp_items)) {
            echo "First item: " . htmlspecialchars($temp_items[0]['particulars']) . "<br>";
            echo "Asset ID: " . $temp_items[0]['asset_id'] . "<br>";
        }
        echo "</div>";
        
        if (!empty($temp_items)) {
            echo '<div class="alert alert-success">';
            echo '<strong><i class="bi bi-info-circle"></i> Temporary Items Ready!</strong> ';
            echo 'You have ' . count($temp_items) . ' item(s) ready to be loaded into this IIRUP form.';
            echo '</div>';
        }
        ?>
        
        <h3>Generated Table:</h3>
        <div class="table-responsive">
            <table class="table table-bordered excel-table">
                <thead>
                    <tr>
                        <th>Date Acquired</th>
                        <th>Particulars</th>
                        <th>Property No</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total Cost</th>
                        <th>Office</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo generateIIRUPTableRows($preselected_asset, $temp_items); ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            <h4>Raw Temp Items Data:</h4>
            <pre><?php print_r($temp_items); ?></pre>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
