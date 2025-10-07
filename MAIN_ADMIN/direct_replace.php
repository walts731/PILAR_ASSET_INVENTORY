<?php
// Direct line-by-line replacement

$file_path = 'view_asset_details.php';
$lines = file($file_path);

// Replace lines 396-401 (the IIRUP button section)
$lines[395] = "                                    <!-- Add to IIRUP Button -->\n"; // Line 396 (0-indexed)
$lines[396] = "                                    <button class=\"btn btn-outline-warning add-to-iirup-btn\"\n";
$lines[397] = "                                        data-asset-id=\"<?= \$asset['id'] ?>\">\n";
$lines[398] = "                                        <i class=\"bi bi-exclamation-triangle me-2\"></i>Add to IIRUP\n";
$lines[399] = "                                    </button>\n";
$lines[400] = "";

// Write back to file
file_put_contents($file_path, implode('', $lines));

echo "Direct replacement completed!\n";
echo "Lines 396-401 have been replaced with the new IIRUP button.\n";
?>
