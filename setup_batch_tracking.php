<?php
/**
 * Batch/Lot Tracking Implementation Script
 * PILAR Asset Inventory System
 *
 * This script creates all necessary database tables and structures
 * for implementing batch/lot tracking functionality.
 */

require_once 'connect.php';

// Read and execute the schema file
$schema_file = 'batch_tracking_schema.sql';

if (file_exists($schema_file)) {
    $sql = file_get_contents($schema_file);

    // Split the SQL file into individual statements, handling semicolons inside quotes
    $statements = [];
    $lines = explode("\n", $sql);
    $current_statement = '';
    $in_string = false;
    $string_char = '';

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and empty lines
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }

        // Check for string delimiters
        $i = 0;
        while ($i < strlen($line)) {
            $char = $line[$i];

            if (!$in_string && ($char === '"' || $char === "'")) {
                $in_string = true;
                $string_char = $char;
            } elseif ($in_string && $char === $string_char && ($i === 0 || $line[$i-1] !== '\\')) {
                $in_string = false;
                $string_char = '';
            }
            $i++;
        }

        $current_statement .= $line . "\n";

        // If not in a string and line ends with semicolon, it's a complete statement
        if (!$in_string && substr($line, -1) === ';') {
            $statements[] = trim($current_statement);
            $current_statement = '';
        }
    }

    // Add any remaining statement
    if (!empty($current_statement)) {
        $statements[] = trim($current_statement);
    }

    $success_count = 0;
    $error_count = 0;
    $errors = [];

    echo "<h2>Batch/Lot Tracking Database Setup</h2>";
    echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border: 1px solid #ddd; max-height: 600px; overflow-y: auto;'>";

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip comments and empty statements
        }

        try {
            if (mysqli_query($conn, $statement)) {
                $success_count++;
                echo "<span style='color: green;'>✓</span> " . substr(str_replace("\n", " ", $statement), 0, 100) . "...<br>";
            } else {
                $error_count++;
                $mysql_error = mysqli_error($conn);
                // Check if this is a duplicate constraint error (errno 121 or error message contains "Duplicate key")
                if (strpos($mysql_error, "Duplicate key") !== false || mysqli_errno($conn) == 121) {
                    echo "<span style='color: orange;'>⚠</span> " . substr(str_replace("\n", " ", $statement), 0, 100) . "...<br>";
                    echo "<span style='color: orange; font-size: 12px;'>Warning: Constraint already exists (this is normal if running setup multiple times)</span><br>";
                } else {
                    $errors[] = $mysql_error . " (Statement: " . substr($statement, 0, 100) . "...)";
                    echo "<span style='color: red;'>✗</span> " . substr(str_replace("\n", " ", $statement), 0, 100) . "...<br>";
                    echo "<span style='color: red; font-size: 12px;'>Error: " . $mysql_error . "</span><br>";
                }
            }
        } catch (Exception $e) {
            $error_count++;
            $errors[] = $e->getMessage() . " (Statement: " . substr($statement, 0, 100) . "...)";
            echo "<span style='color: red;'>✗</span> " . substr(str_replace("\n", " ", $statement), 0, 100) . "...<br>";
            echo "<span style='color: red; font-size: 12px;'>Exception: " . $e->getMessage() . "</span><br>";
        }
    }

    echo "</div>";

    echo "<h3>Summary</h3>";
    echo "<p>Total statements executed: " . ($success_count + $error_count) . "</p>";
    echo "<p style='color: green;'>Successful: $success_count</p>";
    echo "<p style='color: red;'>Failed: $error_count</p>";

    if (!empty($errors)) {
        echo "<h4>Errors:</h4>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }

    if ($error_count == 0) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>✓ Database setup completed successfully!</h4>";
        echo "<p>All batch/lot tracking tables have been created successfully.</p>";
        echo "<p>The system is now ready to use batch/lot tracking functionality.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>⚠ Database setup completed with errors</h4>";
        echo "<p>Some tables may not have been created. Please check the errors above and try again.</p>";
        echo "<p>If errors persist, check that all referenced tables (assets, users, categories, offices, employees) exist.</p>";
        echo "</div>";
    }

} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✗ Schema file not found</h4>";
    echo "<p>The file '$schema_file' was not found. Please ensure it exists in the same directory as this script.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h4>Next Steps:</h4>";
echo "<ol>";
echo "<li>Review the batch/lot tracking implementation</li>";
echo "<li>Update asset creation forms to support batch tracking</li>";
echo "<li>Modify borrowing/returning processes for batch items</li>";
echo "<li>Create batch management interface</li>";
echo "<li>Test the complete functionality</li>";
echo "</ol>";

mysqli_close($conn);
?>
