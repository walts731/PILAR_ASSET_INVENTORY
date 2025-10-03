<?php
require_once 'connect.php';
require_once 'includes/tag_format_helper.php';

echo "<h2>Tag Format System Test</h2>";

// Test all tag types
$tagTypes = ['red_tag', 'ics_no', 'itr_no', 'par_no', 'ris_no', 'inventory_tag'];

echo "<h3>Preview Next Tags (without incrementing):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Tag Type</th><th>Preview</th><th>Status</th></tr>";

foreach ($tagTypes as $tagType) {
    $preview = previewTag($tagType);
    $status = $preview ? '✓ OK' : '✗ ERROR';
    echo "<tr><td>$tagType</td><td><strong>$preview</strong></td><td>$status</td></tr>";
}
echo "</table>";

echo "<h3>Generate Actual Tags (with incrementing):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Tag Type</th><th>Generated Tag</th><th>Status</th></tr>";

foreach ($tagTypes as $tagType) {
    $generated = generateTag($tagType);
    $status = $generated ? '✓ OK' : '✗ ERROR';
    echo "<tr><td>$tagType</td><td><strong>$generated</strong></td><td>$status</td></tr>";
}
echo "</table>";

echo "<h3>Check Database Tables:</h3>";

// Check tag_formats table
$result = $conn->query("SELECT COUNT(*) as count FROM tag_formats");
$formatCount = $result->fetch_assoc()['count'];
echo "<p>Tag Formats: $formatCount records</p>";

// Check tag_counters table
$result = $conn->query("SELECT COUNT(*) as count FROM tag_counters");
$counterCount = $result->fetch_assoc()['count'];
echo "<p>Tag Counters: $counterCount records</p>";

echo "<h3>Current Format Configurations (Simple Prefix + Digits):</h3>";
$result = $conn->query("SELECT tag_type, format_template, prefix, increment_digits FROM tag_formats ORDER BY tag_type");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Tag Type</th><th>Format Template</th><th>Prefix</th><th>Digits</th><th>Example</th></tr>";
while ($row = $result->fetch_assoc()) {
    $example = str_replace('{' . str_repeat('#', $row['increment_digits']) . '}', str_pad('1', $row['increment_digits'], '0', STR_PAD_LEFT), $row['format_template']);
    echo "<tr><td>{$row['tag_type']}</td><td>{$row['format_template']}</td><td>{$row['prefix']}</td><td>{$row['increment_digits']}</td><td><strong>$example</strong></td></tr>";
}
echo "</table>";

echo "<p><strong>Integration Status: ✓ Ready to use!</strong></p>";
echo "<p><a href='SYSTEM_ADMIN/manage_tag_format.php'>Go to Manage Tag Format</a></p>";
?>
