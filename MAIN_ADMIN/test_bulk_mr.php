<?php
// Simple test script to debug the bulk MR creation
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../connect.php';
session_start();

echo "<h2>Bulk MR Debug Test</h2>";

// Test database connection
if ($conn) {
    echo "<p>✅ Database connection successful</p>";
} else {
    echo "<p>❌ Database connection failed</p>";
    exit;
}

// Check if mr_details table exists and its structure
echo "<h3>MR Details Table Structure:</h3>";
$result = $conn->query("DESCRIBE mr_details");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Could not describe mr_details table: " . $conn->error . "</p>";
}

// Check if assets table has required columns
echo "<h3>Assets Table Structure (relevant columns):</h3>";
$result = $conn->query("DESCRIBE assets");
if ($result) {
    $relevant_columns = ['property_no', 'inventory_tag', 'category', 'model', 'brand', 'serial_no', 'code', 'end_user', 'image', 'employee_id', 'office_id'];
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        if (in_array($row['Field'], $relevant_columns)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p>❌ Could not describe assets table: " . $conn->error . "</p>";
}

// Test TagFormatHelper
echo "<h3>TagFormatHelper Test:</h3>";
try {
    require_once '../includes/tag_format_helper.php';
    $tagHelper = new TagFormatHelper($conn);
    echo "<p>✅ TagFormatHelper loaded successfully</p>";
    
    // Test generating a tag
    $testTag = $tagHelper->generateNextTag('inventory_tag');
    if ($testTag) {
        echo "<p>✅ Test inventory tag generated: " . htmlspecialchars($testTag) . "</p>";
    } else {
        echo "<p>⚠️ Could not generate test inventory tag</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ TagFormatHelper error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check tag_formats table
echo "<h3>Tag Formats Available:</h3>";
$result = $conn->query("SELECT tag_type, format_template, is_active FROM tag_formats WHERE is_active = 1");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'><tr><th>Tag Type</th><th>Format Template</th><th>Active</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['tag_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['format_template']) . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>⚠️ No active tag formats found</p>";
}

echo "<p><strong>Test completed. Check the results above for any issues.</strong></p>";
?>
