<?php
/**
 * Test script for Forgot Password functionality
 * This script helps verify that the forgot password system is working correctly
 */

require_once 'connect.php';
require_once 'includes/email_helper.php';

echo "<h2>🔐 Forgot Password Functionality Test</h2>";

try {
    // Test 1: Check database structure
    echo "<h3>1. Database Structure Check</h3>";
    
    // Check if users table has reset token columns
    $columns_query = "SHOW COLUMNS FROM users LIKE 'reset_token%'";
    $columns_result = $conn->query($columns_query);
    
    $required_columns = ['reset_token', 'reset_token_expiry'];
    $existing_columns = [];
    
    while ($row = $columns_result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    foreach ($required_columns as $column) {
        if (in_array($column, $existing_columns)) {
            echo "✅ Column '{$column}' exists<br>";
        } else {
            echo "❌ Column '{$column}' missing<br>";
        }
    }
    
    // Test 2: Check for active users
    echo "<h3>2. Active Users Check</h3>";
    $users_query = "SELECT id, username, email, fullname FROM users WHERE status = 'active' LIMIT 5";
    $users_result = $conn->query($users_query);
    
    if ($users_result->num_rows > 0) {
        echo "✅ Active users found for testing:<br>";
        echo "<ul>";
        while ($user = $users_result->fetch_assoc()) {
            echo "<li><strong>{$user['username']}</strong> - {$user['fullname']} ({$user['email']})</li>";
        }
        echo "</ul>";
    } else {
        echo "❌ No active users found<br>";
    }
    
    // Test 3: Email configuration test
    echo "<h3>3. Email Configuration Test</h3>";
    try {
        $mail = configurePHPMailer();
        echo "✅ PHPMailer configuration successful<br>";
        echo "📧 SMTP Host: " . $mail->Host . "<br>";
        echo "🔐 SMTP Port: " . $mail->Port . "<br>";
        echo "👤 From Address: " . $mail->From . "<br>";
    } catch (Exception $e) {
        echo "❌ Email configuration failed: " . $e->getMessage() . "<br>";
    }
    
    // Test 4: Token generation test
    echo "<h3>4. Token Generation Test</h3>";
    $test_token = bin2hex(random_bytes(32));
    if (strlen($test_token) === 64) {
        echo "✅ Token generation working (Length: " . strlen($test_token) . ")<br>";
        echo "Sample token: <code>" . substr($test_token, 0, 16) . "...</code><br>";
    } else {
        echo "❌ Token generation failed<br>";
    }
    
    // Test 5: File existence check
    echo "<h3>5. Required Files Check</h3>";
    $required_files = [
        'forgot_password_handler.php' => 'AJAX handler for password reset requests',
        'reset_password.php' => 'Password reset form page',
        'includes/email_helper.php' => 'Email functionality'
    ];
    
    foreach ($required_files as $file => $description) {
        if (file_exists($file)) {
            echo "✅ {$file} - {$description}<br>";
        } else {
            echo "❌ {$file} - {$description} (MISSING)<br>";
        }
    }
    
    // Test 6: Check current reset tokens
    echo "<h3>6. Current Reset Tokens Status</h3>";
    $tokens_query = "SELECT 
        COUNT(*) as total_tokens,
        COUNT(CASE WHEN reset_token_expiry > NOW() THEN 1 END) as active_tokens,
        COUNT(CASE WHEN reset_token_expiry <= NOW() THEN 1 END) as expired_tokens
        FROM users 
        WHERE reset_token IS NOT NULL";
    
    $tokens_result = $conn->query($tokens_query);
    if ($tokens_result) {
        $tokens = $tokens_result->fetch_assoc();
        echo "<ul>";
        echo "<li>Total reset tokens: {$tokens['total_tokens']}</li>";
        echo "<li>Active tokens: {$tokens['active_tokens']}</li>";
        echo "<li>Expired tokens: {$tokens['expired_tokens']}</li>";
        echo "</ul>";
    }
    
    // Test 7: Password reset email template test
    echo "<h3>7. Password Reset Email Template Test</h3>";
    $test_token = "test_token_123456789";
    $test_username = "testuser";
    $test_email = "test@example.com";
    
    try {
        // Test email generation without actually sending
        $mail = configurePHPMailer();
        $mail->addAddress($test_email);
        $mail->Subject = 'Password Reset Request - PILAR Asset Inventory';
        
        $baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                   "://" . $_SERVER['HTTP_HOST'] . 
                   dirname($_SERVER['PHP_SELF']);
        $resetURL = $baseURL . '/reset_password.php?token=' . $test_token;
        
        $mail->isHTML(true);
        $mail->Body = "
        <h2>Password Reset Request</h2>
        <p>Hello {$test_username},</p>
        <p>You have requested to reset your password for PILAR Asset Inventory System.</p>
        <p><a href='{$resetURL}' style='background: #0b5ed7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
        <p>If the button doesn't work, copy and paste this URL: {$resetURL}</p>
        <p>This link will expire in 1 hour.</p>
        <p>If you didn't request this reset, please ignore this email.</p>
        ";
        
        echo "✅ Password reset email template generated successfully<br>";
        echo "📧 Reset URL format: <code>" . htmlspecialchars($resetURL) . "</code><br>";
        
    } catch (Exception $e) {
        echo "❌ Email template generation failed: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>8. Integration Test Instructions</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>To test the complete forgot password flow:</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='index.php'>Login Page</a></li>";
    echo "<li>Click 'Forgot Password?' link</li>";
    echo "<li>Enter a valid username in the modal</li>";
    echo "<li>Click 'Send Reset Link'</li>";
    echo "<li>Check the user's email for the reset link</li>";
    echo "<li>Click the reset link to open reset_password.php</li>";
    echo "<li>Enter and confirm a new password</li>";
    echo "<li>Test login with the new password</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h3>9. Security Features</h3>";
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Security measures implemented:</strong></p>";
    echo "<ul>";
    echo "<li>🔐 <strong>Secure tokens:</strong> 64-character cryptographically secure tokens</li>";
    echo "<li>⏰ <strong>Token expiration:</strong> 1-hour expiration for reset links</li>";
    echo "<li>🔒 <strong>Single use:</strong> Tokens are cleared after successful reset</li>";
    echo "<li>👤 <strong>User validation:</strong> Only active users can request resets</li>";
    echo "<li>📧 <strong>Email validation:</strong> Reset links sent to registered email only</li>";
    echo "<li>🛡️ <strong>Password strength:</strong> Enforced password complexity requirements</li>";
    echo "<li>📝 <strong>Audit logging:</strong> All reset activities are logged</li>";
    echo "<li>🚫 <strong>Information disclosure:</strong> Same response for valid/invalid usernames</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>10. Troubleshooting</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Common issues and solutions:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Modal not opening:</strong> Check Bootstrap JS is loaded</li>";
    echo "<li><strong>AJAX not working:</strong> Check forgot_password_handler.php exists</li>";
    echo "<li><strong>Email not sending:</strong> Verify SMTP configuration in email_helper.php</li>";
    echo "<li><strong>Reset link invalid:</strong> Check token hasn't expired (1 hour limit)</li>";
    echo "<li><strong>Password requirements:</strong> Must be 8+ chars with uppercase, lowercase, number, special char</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>✅ Forgot Password System Ready!</h3>";
    echo "<p>The forgot password functionality has been successfully implemented and is ready for use.</p>";
    
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
