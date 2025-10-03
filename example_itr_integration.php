<?php
/**
 * Example: ITR Form Integration with Automatic Tag Generation
 * This shows how to integrate the tag format system with existing forms
 */

require_once 'connect.php';
require_once 'includes/tag_format_helper.php';

// Example: When creating a new ITR form
function createNewITR($formData) {
    global $conn;
    
    try {
        // Generate automatic ITR number
        $itrNumber = generateTag('itr_no');
        
        if (!$itrNumber) {
            throw new Exception('Failed to generate ITR number');
        }
        
        // Insert ITR form with auto-generated number
        $stmt = $conn->prepare("INSERT INTO itr_form (
            itr_no, 
            entity_name, 
            fund_cluster, 
            from_accountable_officer, 
            to_accountable_officer, 
            transfer_type, 
            reason_for_transfer,
            date,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->bind_param("ssssssss", 
            $itrNumber,
            $formData['entity_name'],
            $formData['fund_cluster'],
            $formData['from_accountable_officer'],
            $formData['to_accountable_officer'],
            $formData['transfer_type'],
            $formData['reason_for_transfer'],
            $formData['date']
        );
        
        if ($stmt->execute()) {
            $itrId = $conn->insert_id;
            $stmt->close();
            
            return [
                'success' => true,
                'itr_id' => $itrId,
                'itr_no' => $itrNumber,
                'message' => "ITR created successfully with number: $itrNumber"
            ];
        } else {
            throw new Exception('Failed to create ITR form');
        }
        
    } catch (Exception $e) {
        error_log("ITR creation error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Example usage:
/*
$formData = [
    'entity_name' => 'LGU-PILAR-CAMUR',
    'fund_cluster' => '01',
    'from_accountable_officer' => 'John Doe',
    'to_accountable_officer' => 'Jane Smith',
    'transfer_type' => 'Reassignment',
    'reason_for_transfer' => 'Office relocation',
    'date' => '2025-01-03'
];

$result = createNewITR($formData);
if ($result['success']) {
    echo "Success: " . $result['message'];
    echo "ITR ID: " . $result['itr_id'];
    echo "ITR Number: " . $result['itr_no'];
} else {
    echo "Error: " . $result['message'];
}
*/

// Example: Preview next ITR number before creating form
function previewNextITRNumber() {
    return previewTag('itr_no');
}

// Example: Integration with existing save_itr_items.php
function integrateWithSaveITRItems() {
    // In your existing save_itr_items.php, replace manual ITR number input with:
    
    // OLD CODE (remove this):
    // $itr_no = $_POST['itr_no']; // Manual input
    
    // NEW CODE (use this instead):
    // $itr_no = generateTag('itr_no'); // Automatic generation
    
    echo "Replace manual ITR number input with automatic generation in save_itr_items.php";
}

// Example: Form field modification for ITR form
function getITRFormFieldExample() {
    $nextITRNumber = previewTag('itr_no');
    
    return '
    <!-- OLD: Manual input field -->
    <!-- <input type="text" name="itr_no" class="form-control" placeholder="Enter ITR number" required> -->
    
    <!-- NEW: Display auto-generated number -->
    <div class="form-group">
        <label class="form-label">ITR No. (Auto-generated)</label>
        <div class="input-group">
            <input type="text" class="form-control" value="' . htmlspecialchars($nextITRNumber) . '" readonly>
            <span class="input-group-text">
                <i class="bi bi-magic" title="Auto-generated"></i>
            </span>
        </div>
        <small class="text-muted">This number will be automatically assigned when you save the form.</small>
    </div>';
}

// Example: Integration for other forms
function integrateOtherForms() {
    return [
        'PAR Form' => [
            'tag_type' => 'par_no',
            'function' => 'generateTag("par_no")',
            'preview' => 'previewTag("par_no")',
            'example' => 'PAR-2025-0001'
        ],
        'ICS Form' => [
            'tag_type' => 'ics_no',
            'function' => 'generateTag("ics_no")',
            'preview' => 'previewTag("ics_no")',
            'example' => 'ICS-2025-0001'
        ],
        'RIS Form' => [
            'tag_type' => 'ris_no',
            'function' => 'generateTag("ris_no")',
            'preview' => 'previewTag("ris_no")',
            'example' => 'RIS-2025-0001'
        ],
        'Red Tag' => [
            'tag_type' => 'red_tag',
            'function' => 'generateTag("red_tag")',
            'preview' => 'previewTag("red_tag")',
            'example' => 'RT-2025-0001'
        ],
        'Inventory Tag' => [
            'tag_type' => 'inventory_tag',
            'function' => 'generateTag("inventory_tag")',
            'preview' => 'previewTag("inventory_tag")',
            'example' => 'INV-2025-0001'
        ]
    ];
}

// Display integration examples
if (isset($_GET['show_examples'])) {
    echo "<h3>Tag Format Integration Examples</h3>";
    
    echo "<h4>ITR Form Field Example:</h4>";
    echo "<pre>" . htmlspecialchars(getITRFormFieldExample()) . "</pre>";
    
    echo "<h4>All Form Integrations:</h4>";
    $integrations = integrateOtherForms();
    foreach ($integrations as $formName => $config) {
        echo "<h5>$formName</h5>";
        echo "<ul>";
        echo "<li>Tag Type: " . $config['tag_type'] . "</li>";
        echo "<li>Generate Function: " . $config['function'] . "</li>";
        echo "<li>Preview Function: " . $config['preview'] . "</li>";
        echo "<li>Example Output: " . $config['example'] . "</li>";
        echo "</ul>";
    }
}
?>
