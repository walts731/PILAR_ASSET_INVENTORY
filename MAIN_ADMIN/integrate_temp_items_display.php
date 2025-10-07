<?php
/**
 * Simple integration script to add temp items display to IIRUP form
 * Run this script to integrate the temp items functionality
 */

echo "IIRUP Temp Items Display Integration\n";
echo "====================================\n\n";

$iirup_file = 'iirup_form.php';

if (!file_exists($iirup_file)) {
    die("Error: iirup_form.php not found!\n");
}

// Read the current file
$content = file_get_contents($iirup_file);

// Add the include statement after temp items fetching
$search1 = '// Fetch asset data for datalist and JS - only assets where red_tagged = 0 and check for red tag status';
$replace1 = '// Include temp items helper functions
include \'load_temp_iirup_items.php\';

// Fetch asset data for datalist and JS - only assets where red_tagged = 0 and check for red tag status';

if (strpos($content, 'include \'load_temp_iirup_items.php\';') === false) {
    $content = str_replace($search1, $replace1, $content);
    echo "✓ Added temp items helper include\n";
} else {
    echo "✓ Temp items helper already included\n";
}

// Find and replace the table body
$tbody_pattern = '/<tbody>\s*.*?<\/tbody>/s';

if (preg_match($tbody_pattern, $content)) {
    $new_tbody = '<tbody>
        <?php echo generateIIRUPTableRows($preselected_asset, $temp_items); ?>
    </tbody>';
    
    $content = preg_replace($tbody_pattern, $new_tbody, $content);
    echo "✓ Replaced table body with temp items display\n";
} else {
    echo "⚠ Could not find table body to replace\n";
}

// Write the modified content
if (file_put_contents($iirup_file, $content)) {
    echo "✓ Integration completed successfully!\n\n";
    echo "The IIRUP form will now display:\n";
    echo "- Preselected assets from QR codes\n";
    echo "- Temporary items from temp_iirup_items table\n";
    echo "- All data from the temp table fields:\n";
    echo "  * id, asset_id, date_acquired, particulars\n";
    echo "  * property_no, quantity, unit_cost, office, code\n\n";
    echo "Test the form to verify it displays temp items correctly.\n";
} else {
    echo "❌ Error: Could not write to iirup_form.php\n";
}
?>
