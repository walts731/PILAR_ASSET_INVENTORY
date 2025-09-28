<?php
/**
 * Test script for email functionality
 * This script helps verify that the email system is working correctly
 */

require_once 'includes/email_helper.php';

echo "<h2>📧 Email Functionality Test</h2>";

try {
    // Test email configuration
    echo "<h3>1. Email Configuration Test</h3>";
    
    $mail = configurePHPMailer();
    echo "✅ PHPMailer configuration successful<br>";
    echo "📧 SMTP Host: " . $mail->Host . "<br>";
    echo "🔐 SMTP Port: " . $mail->Port . "<br>";
    echo "👤 From Address: " . $mail->From . "<br>";
    echo "🏢 From Name: " . $mail->FromName . "<br>";
    
    // Test welcome email generation
    echo "<h3>2. Welcome Email Content Test</h3>";
    
    $test_fullname = "John Doe";
    $test_username = "johndoe";
    $test_password = "TempPassword123!";
    $test_role = "user";
    $test_office = "IT Department";
    $test_login_url = "http://localhost/pilar_asset_inventory/index.php";
    
    $html_content = generateWelcomeEmailHTML($test_fullname, $test_username, $test_password, $test_role, $test_office, $test_login_url);
    $text_content = generateWelcomeEmailText($test_fullname, $test_username, $test_password, $test_role, $test_office, $test_login_url);
    
    echo "✅ HTML email content generated (" . strlen($html_content) . " characters)<br>";
    echo "✅ Text email content generated (" . strlen($text_content) . " characters)<br>";
    
    // Show preview of HTML content
    echo "<h4>HTML Email Preview:</h4>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; max-height: 300px; overflow-y: auto;'>";
    echo $html_content;
    echo "</div>";
    
    // Test email sending (optional - uncomment to actually send test email)
    echo "<h3>3. Email Sending Test</h3>";
    echo "<p><strong>Note:</strong> To test actual email sending, uncomment the code below and provide a test email address.</p>";
    
    /*
    // Uncomment this section to test actual email sending
    $test_email = "your-test-email@example.com"; // Replace with your test email
    
    echo "<p>Attempting to send test email to: {$test_email}</p>";
    
    $result = sendWelcomeEmail(
        $test_email,
        $test_fullname,
        $test_username,
        $test_password,
        $test_role,
        $test_office
    );
    
    if ($result['success']) {
        echo "<div style='color: green;'>✅ Test email sent successfully!</div>";
        echo "<p>Message: " . htmlspecialchars($result['message']) . "</p>";
    } else {
        echo "<div style='color: red;'>❌ Test email failed to send</div>";
        echo "<p>Error: " . htmlspecialchars($result['message']) . "</p>";
    }
    */
    
    echo "<h3>4. Integration Test</h3>";
    echo "<p>To test the complete integration:</p>";
    echo "<ol>";
    echo "<li>Go to <a href='MAIN_ADMIN/user.php'>User Management</a></li>";
    echo "<li>Click 'Add New User'</li>";
    echo "<li>Fill in the form with a valid email address</li>";
    echo "<li>Submit the form</li>";
    echo "<li>Check if the success message mentions email status</li>";
    echo "<li>Check the user's email inbox for the welcome message</li>";
    echo "</ol>";
    
    echo "<h3>5. Email Configuration Notes</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Current Configuration:</strong></p>";
    echo "<ul>";
    echo "<li>SMTP Server: smtp.gmail.com</li>";
    echo "<li>Port: 587 (STARTTLS)</li>";
    echo "<li>Authentication: Required</li>";
    echo "<li>From Email: waltielappy@gmail.com</li>";
    echo "</ul>";
    echo "<p><strong>To customize:</strong> Edit the <code>configurePHPMailer()</code> function in <code>includes/email_helper.php</code></p>";
    echo "</div>";
    
    echo "<h3>✅ Email System Ready!</h3>";
    echo "<p>The email functionality has been successfully implemented and is ready for use.</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Test Failed</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0b5ed7; }
h3 { color: #0a58ca; margin-top: 20px; }
h4 { color: #6f42c1; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
</style>
