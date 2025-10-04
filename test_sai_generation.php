<?php
/**
 * Test SAI number generation
 */

require_once 'connect.php';
require_once 'includes/tag_format_helper.php';

echo "Testing SAI number generation...\n\n";

// Test preview (without incrementing)
$preview = previewTag('sai_no');
echo "Preview SAI No: " . ($preview ?: 'Failed to generate') . "\n";

// Test actual generation (increments counter)
$generated = generateTag('sai_no');
echo "Generated SAI No: " . ($generated ?: 'Failed to generate') . "\n";

// Test another generation
$generated2 = generateTag('sai_no');
echo "Next SAI No: " . ($generated2 ?: 'Failed to generate') . "\n";

// Show current format
$stmt = $conn->prepare("SELECT * FROM tag_formats WHERE tag_type = 'sai_no'");
$stmt->execute();
$format = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($format) {
    echo "\nCurrent SAI format configuration:\n";
    echo "- Template: " . $format['format_template'] . "\n";
    echo "- Prefix: " . $format['prefix'] . "\n";
    echo "- Digits: " . $format['increment_digits'] . "\n";
    echo "- Date Format: " . ($format['date_format'] ?: 'None') . "\n";
} else {
    echo "\nNo SAI format found in database.\n";
}

$conn->close();
?>
