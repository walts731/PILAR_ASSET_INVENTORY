<?php
/**
 * Integration script to add temporary IIRUP items functionality
 * Run this script to automatically integrate temp items into iirup_form.php
 */

$iirup_form_file = 'iirup_form.php';
$backup_file = 'iirup_form_backup_' . date('Y-m-d_H-i-s') . '.php';

echo "IIRUP Form Temporary Items Integration Script\n";
echo "===========================================\n\n";

// Check if files exist
if (!file_exists($iirup_form_file)) {
    die("Error: iirup_form.php not found!\n");
}

if (!file_exists('load_temp_iirup_items.php')) {
    die("Error: load_temp_iirup_items.php not found! Please create it first.\n");
}

// Create backup
echo "1. Creating backup: $backup_file\n";
if (!copy($iirup_form_file, $backup_file)) {
    die("Error: Could not create backup file!\n");
}

// Read the current file
echo "2. Reading iirup_form.php\n";
$content = file_get_contents($iirup_form_file);
if ($content === false) {
    die("Error: Could not read iirup_form.php!\n");
}

// Apply modifications
echo "3. Applying modifications...\n";

// 1. Add temp items fetching after preselected asset logic
$search1 = '// Fetch asset data for datalist and JS - only assets where red_tagged = 0 and check for red tag status';
$replace1 = '// Fetch temporary IIRUP items for current user/session
include \'load_temp_iirup_items.php\';
$temp_items = getTempIIRUPItems($conn);

// Fetch asset data for datalist and JS - only assets where red_tagged = 0 and check for red tag status';

if (strpos($content, $search1) !== false) {
    $content = str_replace($search1, $replace1, $content);
    echo "   ✓ Added temp items fetching\n";
} else {
    echo "   ⚠ Could not find location to add temp items fetching\n";
}

// 2. Replace table body generation
$search2 = '        <?php for ($i = 0; $i < 1; $i++): ?>';
$replace2 = '        <?php echo generateIIRUPTableRows($preselected_asset, $temp_items); ?>';

// Find the table body section and replace the loop
$tbody_start = strpos($content, '<tbody>');
$tbody_end = strpos($content, '</tbody>', $tbody_start);

if ($tbody_start !== false && $tbody_end !== false) {
    // Extract the current tbody content
    $current_tbody = substr($content, $tbody_start, $tbody_end - $tbody_start + 8);
    
    // Replace with new tbody
    $new_tbody = "    <tbody>\n        <?php echo generateIIRUPTableRows(\$preselected_asset, \$temp_items); ?>\n    </tbody>";
    
    $content = str_replace($current_tbody, $new_tbody, $content);
    echo "   ✓ Replaced table body generation\n";
} else {
    echo "   ⚠ Could not find table body to replace\n";
}

// 3. Add JavaScript functions before closing script tag
$search3 = '    // Initialize row visibility
    updateRowVisibility();';
$replace3 = '    // Initialize row visibility
    updateRowVisibility();
    
    <?php echo getTempItemsJavaScript($temp_items); ?>';

if (strpos($content, $search3) !== false) {
    $content = str_replace($search3, $replace3, $content);
    echo "   ✓ Added JavaScript functions\n";
} else {
    echo "   ⚠ Could not find location to add JavaScript functions\n";
}

// Write the modified content
echo "4. Writing modified file\n";
if (file_put_contents($iirup_form_file, $content) === false) {
    die("Error: Could not write modified file!\n");
}

echo "\n✅ Integration completed successfully!\n";
echo "📁 Backup saved as: $backup_file\n";
echo "\nNext steps:\n";
echo "1. Test the IIRUP form to ensure it works correctly\n";
echo "2. Add some assets to temp table and verify they display\n";
echo "3. Test the Load Items and Clear Items buttons\n";
echo "4. Verify QR code pre-selection still works\n";
echo "\nIf there are any issues, restore from backup: $backup_file\n";
?>
