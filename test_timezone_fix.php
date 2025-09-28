<?php
/**
 * Test the timezone fix for reset tokens
 */

require_once 'connect.php';

echo "<h2>🔧 Timezone Fix Test</h2>";

// Get a test user
$user_query = "SELECT id, username, email FROM users WHERE status = 'active' LIMIT 1";
$user_result = $conn->query($user_query);

if ($user_result->num_rows === 0) {
    echo "❌ No active users found for testing.<br>";
    exit;
}

$user = $user_result->fetch_assoc();
echo "<h3>Test User: {$user['username']}</h3>";

// Generate a fresh token using the new method (MySQL DATE_ADD)
$token = bin2hex(random_bytes(32));

echo "<p><strong>Generated Token:</strong> <code>" . substr($token, 0, 32) . "...</code></p>";

// Update user with the token using MySQL's DATE_ADD
$updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
$updateStmt->bind_param("si", $token, $user['id']);

if ($updateStmt->execute()) {
    echo "✅ Token saved to database using MySQL DATE_ADD!<br>";
    
    // Verify token in database
    echo "<h3>Database Verification:</h3>";
    $verifyStmt = $conn->prepare("SELECT 
        reset_token, 
        reset_token_expiry, 
        NOW() as mysql_current_time,
        CASE WHEN reset_token_expiry > NOW() THEN 'Valid' ELSE 'Expired' END as status,
        TIMESTAMPDIFF(MINUTE, NOW(), reset_token_expiry) as minutes_remaining
        FROM users WHERE id = ?");
    $verifyStmt->bind_param("i", $user['id']);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    $tokenData = $verifyResult->fetch_assoc();
    
    echo "<ul>";
    echo "<li><strong>Current MySQL Time:</strong> {$tokenData['mysql_current_time']}</li>";
    echo "<li><strong>Token Expiry:</strong> {$tokenData['reset_token_expiry']}</li>";
    echo "<li><strong>Minutes Remaining:</strong> {$tokenData['minutes_remaining']} minutes</li>";
    echo "<li><strong>Status:</strong> <span style='color: " . ($tokenData['status'] === 'Valid' ? 'green' : 'red') . ";'><strong>{$tokenData['status']}</strong></span></li>";
    echo "</ul>";
    
    if ($tokenData['status'] === 'Valid') {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; color: #155724;'>";
        echo "<strong>✅ SUCCESS!</strong> The timezone fix is working correctly. The token is now valid for {$tokenData['minutes_remaining']} minutes.";
        echo "</div>";
        
        // Generate test URL
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $baseURL = $protocol . "://" . $host . $scriptDir;
        $resetURL = $baseURL . '/reset_password.php?token=' . $token;
        
        echo "<h3>Test Reset Link:</h3>";
        echo "<p><a href='{$resetURL}' target='_blank' style='background: #0b5ed7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Reset Password</a></p>";
        echo "<p><small>This link should now work correctly!</small></p>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; color: #721c24;'>";
        echo "<strong>❌ ISSUE STILL EXISTS!</strong> The token is still showing as expired. There may be additional timezone configuration needed.";
        echo "</div>";
    }
    
    $verifyStmt->close();
    
} else {
    echo "❌ Failed to save token to database.<br>";
}

$updateStmt->close();

// Show timezone information
echo "<h3>Current Timezone Information:</h3>";
$timezone_info = $conn->query("SELECT 
    NOW() as mysql_now,
    UTC_TIMESTAMP() as mysql_utc,
    @@session.time_zone as session_timezone,
    @@global.time_zone as global_timezone
");
$tz_data = $timezone_info->fetch_assoc();

echo "<ul>";
echo "<li><strong>MySQL NOW():</strong> {$tz_data['mysql_now']}</li>";
echo "<li><strong>MySQL UTC:</strong> {$tz_data['mysql_utc']}</li>";
echo "<li><strong>Session Timezone:</strong> {$tz_data['session_timezone']}</li>";
echo "<li><strong>Global Timezone:</strong> {$tz_data['global_timezone']}</li>";
echo "<li><strong>PHP Timezone:</strong> " . date_default_timezone_get() . "</li>";
echo "<li><strong>PHP Time:</strong> " . date('Y-m-d H:i:s') . "</li>";
echo "</ul>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0b5ed7; }
h3 { color: #0a58ca; margin-top: 20px; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
</style>
