<?php
/**
 * Update Tag Formats to Simple Prefix + Digits Format
 * This script updates existing tag formats to remove year-based formatting
 */

require_once 'connect.php';

echo "<h2>Updating Tag Formats to Simple Format</h2>";

// Update all existing formats to simple prefix + digits
$updates = [
    'red_tag' => ['template' => 'RT-{####}', 'prefix' => 'RT-'],
    'ics_no' => ['template' => 'ICS-{####}', 'prefix' => 'ICS-'],
    'itr_no' => ['template' => 'ITR-{####}', 'prefix' => 'ITR-'],
    'par_no' => ['template' => 'PAR-{####}', 'prefix' => 'PAR-'],
    'ris_no' => ['template' => 'RIS-{####}', 'prefix' => 'RIS-'],
    'inventory_tag' => ['template' => 'INV-{####}', 'prefix' => 'INV-']
];

foreach ($updates as $tagType => $config) {
    $stmt = $conn->prepare("UPDATE tag_formats SET format_template = ?, prefix = ?, date_format = '' WHERE tag_type = ?");
    $stmt->bind_param("sss", $config['template'], $config['prefix'], $tagType);
    
    if ($stmt->execute()) {
        echo "<p>✓ Updated $tagType to format: {$config['template']}</p>";
    } else {
        echo "<p>✗ Failed to update $tagType</p>";
    }
    $stmt->close();
}

// Show current formats
echo "<h3>Current Tag Formats:</h3>";
$result = $conn->query("SELECT tag_type, format_template, prefix, increment_digits FROM tag_formats ORDER BY tag_type");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Tag Type</th><th>Format Template</th><th>Prefix</th><th>Digits</th><th>Example Output</th></tr>";

while ($row = $result->fetch_assoc()) {
    $example = str_replace('{' . str_repeat('#', $row['increment_digits']) . '}', str_pad('1', $row['increment_digits'], '0', STR_PAD_LEFT), $row['format_template']);
    echo "<tr>";
    echo "<td>" . strtoupper($row['tag_type']) . "</td>";
    echo "<td><code>{$row['format_template']}</code></td>";
    echo "<td><code>{$row['prefix']}</code></td>";
    echo "<td>{$row['increment_digits']}</td>";
    echo "<td><strong>$example</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Benefits of Simple Format:</h3>";
echo "<ul>";
echo "<li><strong>Continuous Numbering:</strong> ITR-0001, ITR-0002, ITR-0003... (no year breaks)</li>";
echo "<li><strong>Simpler Management:</strong> One counter per tag type</li>";
echo "<li><strong>Cleaner Tags:</strong> Shorter, more readable format</li>";
echo "<li><strong>No Year Rollover:</strong> Continuous sequence regardless of year</li>";
echo "</ul>";

echo "<p><strong>✅ Update Complete!</strong></p>";
echo "<p><a href='test_tag_generation.php'>Test Tag Generation</a> | <a href='SYSTEM_ADMIN/manage_tag_format.php'>Manage Formats</a></p>";
?>
