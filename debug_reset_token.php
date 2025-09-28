<?php
require_once 'connect.php';

echo "<h2>🔍 Reset Token Debug Information</h2>";

// Check if reset token columns exist
echo "<h3>1. Database Structure Check</h3>";
$result = $conn->query("DESCRIBE users");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    if (strpos($row['Field'], 'reset') !== false) {
        echo "✅ Column: {$row['Field']} - Type: {$row['Type']} - Null: {$row['Null']} - Default: {$row['Default']}<br>";
    }
}

if (!in_array('reset_token', $columns)) {
    echo "❌ reset_token column is missing!<br>";
    echo "<p><strong>Fix:</strong> Run this SQL to add the missing columns:</p>";
    echo "<pre>ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER password;
ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL AFTER reset_token;</pre>";
}

if (!in_array('reset_token_expiry', $columns)) {
    echo "❌ reset_token_expiry column is missing!<br>";
}

// Check current reset tokens
echo "<h3>2. Current Reset Tokens</h3>";
if (in_array('reset_token', $columns) && in_array('reset_token_expiry', $columns)) {
    $tokens_query = "SELECT id, username, reset_token, reset_token_expiry, 
                     CASE WHEN reset_token_expiry > NOW() THEN 'Valid' ELSE 'Expired' END as token_status
                     FROM users 
                     WHERE reset_token IS NOT NULL 
                     ORDER BY reset_token_expiry DESC 
                     LIMIT 10";
    
    $tokens_result = $conn->query($tokens_query);
    if ($tokens_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Token (first 16 chars)</th><th>Expiry</th><th>Status</th></tr>";
        while ($token = $tokens_result->fetch_assoc()) {
            $token_preview = substr($token['reset_token'], 0, 16) . '...';
            $status_color = $token['token_status'] === 'Valid' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$token['id']}</td>";
            echo "<td>{$token['username']}</td>";
            echo "<td><code>{$token_preview}</code></td>";
            echo "<td>{$token['reset_token_expiry']}</td>";
            echo "<td style='color: {$status_color};'><strong>{$token['token_status']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No reset tokens found in database.<br>";
    }
} else {
    echo "Cannot check tokens - columns missing.<br>";
}

// Test token generation
echo "<h3>3. Token Generation Test</h3>";
$test_token = bin2hex(random_bytes(32));
echo "Generated test token: <code>" . substr($test_token, 0, 32) . "...</code> (Length: " . strlen($test_token) . ")<br>";

// Check if we can get the token from URL
echo "<h3>4. URL Token Check</h3>";
$url_token = $_GET['token'] ?? '';
if (!empty($url_token)) {
    echo "Token from URL: <code>" . substr($url_token, 0, 32) . "...</code> (Length: " . strlen($url_token) . ")<br>";
    
    if (in_array('reset_token', $columns)) {
        // Check if this token exists in database
        $check_stmt = $conn->prepare("SELECT id, username, reset_token_expiry, 
                                      CASE WHEN reset_token_expiry > NOW() THEN 'Valid' ELSE 'Expired' END as status
                                      FROM users WHERE reset_token = ?");
        $check_stmt->bind_param("s", $url_token);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $token_data = $check_result->fetch_assoc();
            echo "✅ Token found in database<br>";
            echo "User ID: {$token_data['id']}<br>";
            echo "Username: {$token_data['username']}<br>";
            echo "Expiry: {$token_data['reset_token_expiry']}<br>";
            echo "Status: <strong style='color: " . ($token_data['status'] === 'Valid' ? 'green' : 'red') . ";'>{$token_data['status']}</strong><br>";
        } else {
            echo "❌ Token not found in database<br>";
        }
        $check_stmt->close();
    }
} else {
    echo "No token provided in URL. Add ?token=YOUR_TOKEN to test.<br>";
}

// Test forgot password handler
echo "<h3>5. Test Forgot Password Request</h3>";
echo "<p>To test the complete flow:</p>";
echo "<ol>";
echo "<li>Go to <a href='index.php'>Login Page</a></li>";
echo "<li>Click 'Forgot Password?' and enter a valid username</li>";
echo "<li>Check the email for the reset link</li>";
echo "<li>Copy the token from the email URL and test it here: <a href='?token=PASTE_TOKEN_HERE'>?token=PASTE_TOKEN_HERE</a></li>";
echo "</ol>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0b5ed7; }
h3 { color: #0a58ca; margin-top: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f8f9fa; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
</style>
