<?php
/**
 * Test script to generate a fresh reset token for testing
 */

require_once 'connect.php';

echo "<h2>🧪 Reset Token Test</h2>";

// Get a test user
$user_query = "SELECT id, username, email FROM users WHERE status = 'active' LIMIT 1";
$user_result = $conn->query($user_query);

if ($user_result->num_rows === 0) {
    echo "❌ No active users found for testing.<br>";
    exit;
}

$user = $user_result->fetch_assoc();
echo "<h3>Test User: {$user['username']} ({$user['email']})</h3>";

// Generate a fresh token
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

echo "<p><strong>Generated Token:</strong> <code>{$token}</code></p>";
echo "<p><strong>Expiry:</strong> {$expiry}</p>";

// Update user with the token
$updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
$updateStmt->bind_param("ssi", $token, $expiry, $user['id']);

if ($updateStmt->execute()) {
    echo "✅ Token saved to database successfully!<br>";
    
    // Generate the reset URL
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $baseURL = $protocol . "://" . $host . $scriptDir;
    $resetURL = $baseURL . '/reset_password.php?token=' . $token;
    
    echo "<h3>Test Reset Link:</h3>";
    echo "<p><a href='{$resetURL}' target='_blank' style='background: #0b5ed7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Reset Password</a></p>";
    echo "<p><strong>URL:</strong> <code>{$resetURL}</code></p>";
    
    // Verify token in database
    echo "<h3>Database Verification:</h3>";
    $verifyStmt = $conn->prepare("SELECT reset_token, reset_token_expiry, 
                                  CASE WHEN reset_token_expiry > NOW() THEN 'Valid' ELSE 'Expired' END as status
                                  FROM users WHERE id = ?");
    $verifyStmt->bind_param("i", $user['id']);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    $tokenData = $verifyResult->fetch_assoc();
    
    echo "<ul>";
    echo "<li><strong>Token in DB:</strong> " . substr($tokenData['reset_token'], 0, 32) . "...</li>";
    echo "<li><strong>Expiry in DB:</strong> {$tokenData['reset_token_expiry']}</li>";
    echo "<li><strong>Status:</strong> <span style='color: " . ($tokenData['status'] === 'Valid' ? 'green' : 'red') . ";'><strong>{$tokenData['status']}</strong></span></li>";
    echo "</ul>";
    
    $verifyStmt->close();
    
} else {
    echo "❌ Failed to save token to database.<br>";
}

$updateStmt->close();
$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0b5ed7; }
h3 { color: #0a58ca; margin-top: 20px; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
</style>
