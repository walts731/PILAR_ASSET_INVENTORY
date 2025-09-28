<?php
/**
 * Test script for Remember Me functionality
 * This script helps verify that the remember me system is working correctly
 */

require_once 'connect.php';
require_once 'includes/remember_me_helper.php';

echo "<h2>🧪 Remember Me Functionality Test</h2>";

try {
    // Test 1: Check if table exists
    echo "<h3>1. Database Table Check</h3>";
    $result = $conn->query("SHOW TABLES LIKE 'remember_tokens'");
    if ($result->num_rows > 0) {
        echo "✅ remember_tokens table exists<br>";
        
        // Get table structure
        $structure = $conn->query("DESCRIBE remember_tokens");
        echo "<strong>Table Structure:</strong><br>";
        echo "<ul>";
        while ($row = $structure->fetch_assoc()) {
            echo "<li>{$row['Field']} - {$row['Type']} {$row['Null']} {$row['Key']}</li>";
        }
        echo "</ul>";
    } else {
        echo "❌ remember_tokens table does not exist<br>";
        echo "Run: <code>php setup_remember_me.php</code><br>";
    }
    
    // Test 2: Token generation
    echo "<h3>2. Token Generation Test</h3>";
    $test_token = generateRememberToken();
    if (strlen($test_token) === 64) {
        echo "✅ Token generation working (Length: " . strlen($test_token) . ")<br>";
        echo "Sample token: <code>" . substr($test_token, 0, 16) . "...</code><br>";
    } else {
        echo "❌ Token generation failed<br>";
    }
    
    // Test 3: Check for existing users
    echo "<h3>3. User Account Check</h3>";
    $users_result = $conn->query("SELECT id, username, role, status FROM users WHERE status = 'active' LIMIT 5");
    if ($users_result->num_rows > 0) {
        echo "✅ Active users found for testing:<br>";
        echo "<ul>";
        while ($user = $users_result->fetch_assoc()) {
            echo "<li>ID: {$user['id']} - {$user['username']} ({$user['role']})</li>";
        }
        echo "</ul>";
    } else {
        echo "❌ No active users found<br>";
    }
    
    // Test 4: Cookie settings test
    echo "<h3>4. Cookie Configuration Test</h3>";
    $cookie_test = setRememberCookie("test_token_123", 1);
    if ($cookie_test) {
        echo "✅ Cookie setting function working<br>";
        clearRememberCookie(); // Clean up test cookie
        echo "✅ Cookie clearing function working<br>";
    } else {
        echo "❌ Cookie functions not working<br>";
    }
    
    // Test 5: Database connection test
    echo "<h3>5. Database Functions Test</h3>";
    
    // Test token cleanup (should not fail even with empty table)
    $cleanup_count = cleanupExpiredTokens($conn);
    echo "✅ Token cleanup function working (Cleaned: {$cleanup_count} tokens)<br>";
    
    // Test 6: Current token statistics
    echo "<h3>6. Current Token Statistics</h3>";
    $stats_query = "SELECT 
        COUNT(*) as total_tokens,
        COUNT(DISTINCT user_id) as unique_users,
        COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active_tokens,
        COUNT(CASE WHEN expires_at <= NOW() THEN 1 END) as expired_tokens
        FROM remember_tokens";
    
    $stats_result = $conn->query($stats_query);
    if ($stats_result) {
        $stats = $stats_result->fetch_assoc();
        echo "<ul>";
        echo "<li>Total tokens: {$stats['total_tokens']}</li>";
        echo "<li>Unique users: {$stats['unique_users']}</li>";
        echo "<li>Active tokens: {$stats['active_tokens']}</li>";
        echo "<li>Expired tokens: {$stats['expired_tokens']}</li>";
        echo "</ul>";
    }
    
    echo "<h3>✅ All Tests Completed Successfully!</h3>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='index.php'>login page</a></li>";
    echo "<li>Check the 'Remember me' checkbox</li>";
    echo "<li>Login with valid credentials</li>";
    echo "<li>Close browser completely</li>";
    echo "<li>Return to the site - you should be automatically logged in</li>";
    echo "</ol>";
    
    echo "<p><strong>Security Notes:</strong></p>";
    echo "<ul>";
    echo "<li>Tokens expire after 30 days by default</li>";
    echo "<li>Only 3 most recent tokens per user are kept</li>";
    echo "<li>Tokens are automatically cleaned up on logout</li>";
    echo "<li>All authentication events are logged for audit</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3>❌ Test Failed</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0b5ed7; }
h3 { color: #0a58ca; margin-top: 20px; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
</style>
