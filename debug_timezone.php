<?php
require_once 'connect.php';

echo "<h2>🕐 Timezone Debug Information</h2>";

// Check PHP timezone
echo "<h3>PHP Timezone Settings:</h3>";
echo "Default timezone: " . date_default_timezone_get() . "<br>";
echo "Current PHP time: " . date('Y-m-d H:i:s') . "<br>";
echo "Current PHP timestamp: " . time() . "<br>";

// Check MySQL timezone
echo "<h3>MySQL Timezone Settings:</h3>";
$mysql_time_result = $conn->query("SELECT NOW() as mysql_now, UTC_TIMESTAMP() as mysql_utc");
$mysql_time = $mysql_time_result->fetch_assoc();
echo "MySQL NOW(): " . $mysql_time['mysql_now'] . "<br>";
echo "MySQL UTC_TIMESTAMP(): " . $mysql_time['mysql_utc'] . "<br>";

// Check timezone difference
echo "<h3>Timezone Comparison:</h3>";
$php_time = date('Y-m-d H:i:s');
$mysql_now = $mysql_time['mysql_now'];

echo "PHP time: {$php_time}<br>";
echo "MySQL time: {$mysql_now}<br>";

$php_timestamp = strtotime($php_time);
$mysql_timestamp = strtotime($mysql_now);
$diff = $php_timestamp - $mysql_timestamp;

echo "Time difference: {$diff} seconds<br>";

if (abs($diff) > 60) {
    echo "<strong style='color: red;'>⚠️ Significant time difference detected!</strong><br>";
} else {
    echo "<strong style='color: green;'>✅ Times are synchronized</strong><br>";
}

// Test token expiry calculation
echo "<h3>Token Expiry Test:</h3>";
$test_expiry_php = date('Y-m-d H:i:s', strtotime('+1 hour'));
$test_expiry_mysql_result = $conn->query("SELECT DATE_ADD(NOW(), INTERVAL 1 HOUR) as expiry");
$test_expiry_mysql = $test_expiry_mysql_result->fetch_assoc()['expiry'];

echo "PHP +1 hour: {$test_expiry_php}<br>";
echo "MySQL +1 hour: {$test_expiry_mysql}<br>";

// Test comparison
$comparison_result = $conn->query("SELECT 
    NOW() as current_time,
    '{$test_expiry_php}' as php_expiry,
    CASE WHEN '{$test_expiry_php}' > NOW() THEN 'Valid' ELSE 'Expired' END as php_status,
    '{$test_expiry_mysql}' as mysql_expiry,
    CASE WHEN '{$test_expiry_mysql}' > NOW() THEN 'Valid' ELSE 'Expired' END as mysql_status
");
$comparison = $comparison_result->fetch_assoc();

echo "<h3>Expiry Comparison:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Method</th><th>Expiry Time</th><th>Status</th></tr>";
echo "<tr><td>PHP Method</td><td>{$comparison['php_expiry']}</td><td><strong style='color: " . ($comparison['php_status'] === 'Valid' ? 'green' : 'red') . ";'>{$comparison['php_status']}</strong></td></tr>";
echo "<tr><td>MySQL Method</td><td>{$comparison['mysql_expiry']}</td><td><strong style='color: " . ($comparison['mysql_status'] === 'Valid' ? 'green' : 'red') . ";'>{$comparison['mysql_status']}</strong></td></tr>";
echo "</table>";

echo "<h3>Recommended Fix:</h3>";
if ($comparison['php_status'] === 'Expired') {
    echo "<p style='color: red;'><strong>Issue Found:</strong> PHP-generated expiry times are being treated as expired by MySQL.</p>";
    echo "<p><strong>Solution:</strong> Use MySQL's DATE_ADD function instead of PHP's strtotime for expiry calculation.</p>";
    echo "<pre>// Instead of:
\$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Use:
\$conn->query(\"UPDATE users SET reset_token = '\$token', reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = \$user_id\");</pre>";
} else {
    echo "<p style='color: green;'><strong>✅ No timezone issues detected.</strong></p>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0b5ed7; }
h3 { color: #0a58ca; margin-top: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f8f9fa; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
</style>
