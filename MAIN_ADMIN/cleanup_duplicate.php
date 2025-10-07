<?php
// Remove duplicate comment line

$file_path = 'view_asset_details.php';
$lines = file($file_path);

// Remove the duplicate comment on line 396 (0-indexed 395)
unset($lines[395]);

// Reindex the array and write back
$lines = array_values($lines);
file_put_contents($file_path, implode('', $lines));

echo "Duplicate comment removed successfully!\n";
?>
