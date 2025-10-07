<?php
require_once '../connect.php';

echo "Testing Row Generation\n";
echo "======================\n\n";

// Include helper functions
include 'load_temp_iirup_items.php';

// Fetch temp items
$temp_items = getTempIIRUPItems($conn);
$preselected_asset = null;

echo "Temp items found: " . count($temp_items) . "\n";

if (!empty($temp_items)) {
    echo "\nTemp items details:\n";
    foreach ($temp_items as $index => $item) {
        echo "Item " . ($index + 1) . ": " . $item['particulars'] . " (ID: " . $item['asset_id'] . ")\n";
    }
}

echo "\nTesting generateIIRUPTableRows function:\n";
echo "---------------------------------------\n";

// Test the function
$table_html = generateIIRUPTableRows($preselected_asset, $temp_items);

// Count rows in generated HTML
$row_count = substr_count($table_html, '<tr class="iirup-row">');
echo "Generated rows: $row_count\n";
echo "Expected rows: " . max(1, count($temp_items)) . "\n";

// Show first few lines of generated HTML
$lines = explode("\n", $table_html);
echo "\nFirst 10 lines of generated HTML:\n";
for ($i = 0; $i < min(10, count($lines)); $i++) {
    echo ($i + 1) . ": " . trim($lines[$i]) . "\n";
}

// Check if particulars are populated
if (strpos($table_html, 'Cord Adaptor') !== false) {
    echo "\n✓ Found 'Cord Adaptor' in generated HTML - temp items are being populated!\n";
} else {
    echo "\n❌ 'Cord Adaptor' not found in generated HTML - temp items may not be populating correctly\n";
}

// Check for asset_id values
if (strpos($table_html, 'value="81"') !== false) {
    echo "✓ Found asset_id 81 in generated HTML\n";
} else {
    echo "❌ Asset_id 81 not found in generated HTML\n";
}
?>
