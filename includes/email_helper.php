<?php
/**
 * Email Helper Functions
 * Handles email sending functionality for the PILAR Asset Inventory system
 */

// Include PHPMailer
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Configure PHPMailer with SMTP settings
 * @return PHPMailer
 */
function configurePHPMailer() {
    $mail = new PHPMailer(true);
    
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'waltielappy@gmail.com'; // Replace with your Gmail
    $mail->Password = 'gwox gjah ufkf hyla'; // Use Google App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Default sender
    $mail->setFrom('waltielappy@gmail.com', 'PILAR Asset Inventory System');
    
    return $mail;
}

/**
 * Send welcome email to new user
 * @param string $email User's email address
 * @param string $fullname User's full name
 * @param string $username User's username
 * @param string $password User's temporary password
 * @param string $role User's role
 * @param string $office_name User's office name
 * @return array Result array with success status and message
 */
function sendWelcomeEmail($email, $fullname, $username, $password, $role, $office_name = '') {
    try {
        $mail = configurePHPMailer();
        
        // Email recipient
        $mail->addAddress($email, $fullname);
        
        // Email subject
        $mail->Subject = 'Welcome to PILAR Asset Inventory System';
        
        // Generate login URL
        $baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                   "://" . $_SERVER['HTTP_HOST'] . 
                   str_replace('/MAIN_ADMIN', '', dirname($_SERVER['PHP_SELF']));
        $loginURL = $baseURL . '/index.php';
        
        // Email content (HTML)
        $mail->isHTML(true);
        $mail->Body = generateWelcomeEmailHTML($fullname, $username, $password, $role, $office_name, $loginURL);
        
        // Alternative plain text content
        $mail->AltBody = generateWelcomeEmailText($fullname, $username, $password, $role, $office_name, $loginURL);
        
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Welcome email sent successfully to ' . $email
        ];
        
    } catch (Exception $e) {
        error_log("Welcome email failed: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to send welcome email: ' . $e->getMessage()
        ];
    }
}

/**
 * Generate HTML email content for welcome email
 */
function generateWelcomeEmailHTML($fullname, $username, $password, $role, $office_name, $loginURL) {
    $office_text = $office_name ? " at {$office_name}" : "";
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
            .credentials { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #0b5ed7; margin: 20px 0; }
            .button { display: inline-block; background: #0b5ed7; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to PILAR Asset Inventory System</h1>
            </div>
            <div class='content'>
                <h2>Hello {$fullname}!</h2>
                
                <p>Your account has been successfully created in the PILAR Asset Inventory System. You have been assigned the role of <strong>{$role}</strong>{$office_text}.</p>
                
                <div class='credentials'>
                    <h3>Your Login Credentials:</h3>
                    <p><strong>Username:</strong> {$username}</p>
                    <p><strong>Temporary Password:</strong> {$password}</p>
                    <p><strong>Role:</strong> {$role}</p>
                    " . ($office_name ? "<p><strong>Office:</strong> {$office_name}</p>" : "") . "
                </div>
                
                <div class='warning'>
                    <strong>⚠️ Important Security Notice:</strong>
                    <ul>
                        <li>Please change your password immediately after your first login</li>
                        <li>Do not share your credentials with anyone</li>
                        <li>Keep your login information secure</li>
                    </ul>
                </div>
                
                <p>Click the button below to access the system:</p>
                <a href='{$loginURL}' class='button'>Login to System</a>
                
                <p>If the button doesn't work, copy and paste this URL into your browser:</p>
                <p><a href='{$loginURL}'>{$loginURL}</a></p>
                
                <h3>What's Next?</h3>
                <ol>
                    <li>Click the login link above</li>
                    <li>Enter your username and temporary password</li>
                    <li>Change your password to something secure</li>
                    <li>Explore the system features based on your role</li>
                </ol>
                
                <p>If you have any questions or need assistance, please contact your system administrator.</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from PILAR Asset Inventory System.<br>
                Please do not reply to this email.</p>
                <p>&copy; " . date('Y') . " PILAR Asset Inventory System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Generate plain text email content for welcome email
 */
function generateWelcomeEmailText($fullname, $username, $password, $role, $office_name, $loginURL) {
    $office_text = $office_name ? " at {$office_name}" : "";
    
    return "
WELCOME TO PILAR ASSET INVENTORY SYSTEM

Hello {$fullname}!

Your account has been successfully created in the PILAR Asset Inventory System. You have been assigned the role of {$role}{$office_text}.

YOUR LOGIN CREDENTIALS:
Username: {$username}
Temporary Password: {$password}
Role: {$role}" . ($office_name ? "\nOffice: {$office_name}" : "") . "

IMPORTANT SECURITY NOTICE:
- Please change your password immediately after your first login
- Do not share your credentials with anyone
- Keep your login information secure

LOGIN URL: {$loginURL}

WHAT'S NEXT:
1. Visit the login URL above
2. Enter your username and temporary password
3. Change your password to something secure
4. Explore the system features based on your role

If you have any questions or need assistance, please contact your system administrator.

---
This is an automated message from PILAR Asset Inventory System.
Please do not reply to this email.

© " . date('Y') . " PILAR Asset Inventory System. All rights reserved.
";
}

/**
 * Send password reset email with secure token
 * @param string $email Recipient email
 * @param string $username Recipient username/fullname display
 * @param string $token Secure reset token
 * @return array
 */
function sendPasswordResetEmail($email, $username, $token) {
    try {
        $mail = configurePHPMailer();

        $mail->addAddress($email);
        $mail->Subject = 'Password Reset Request - PILAR Asset Inventory';

        $baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
        $resetURL = rtrim($baseURL, '/\\') . '/reset_password.php?token=' . urlencode($token);

        $mail->isHTML(true);
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>Hello {$username},</p>
            <p>You have requested to reset your password for PILAR Asset Inventory System.</p>
            <p><a href='{$resetURL}' style='background:#0b5ed7;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;'>Reset Password</a></p>
            <p>If the button doesn't work, copy and paste this URL: {$resetURL}</p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this reset, please ignore this email.</p>
        ";

        $mail->AltBody = "Password Reset Request\n\nHello {$username},\n\n" .
            "You have requested to reset your password for PILAR Asset Inventory System.\n\n" .
            "Reset link: {$resetURL}\n\n" .
            "This link will expire in 1 hour. If you didn't request this reset, please ignore this email.";

        $mail->send();

        return [
            'success' => true,
            'message' => 'Password reset email sent successfully'
        ];
    } catch (Exception $e) {
        error_log('Password reset email failed: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to send password reset email: ' . $e->getMessage()
        ];
    }
}

/**
 * Send borrow request approval email
 * @param string $email Guest's email address
 * @param string $guestName Guest's full name
 * @param string $submissionNumber Submission number
 * @param array $items Array of borrowed items
 * @return array Result array with success status and message
 */
function sendBorrowApprovalEmail($email, $guestName, $submissionNumber, $items) {
    try {
        $mail = configurePHPMailer();

        // Email recipient
        $mail->addAddress($email, $guestName);

        // Email subject
        $mail->Subject = 'Borrow Request Approved - PILAR Asset Inventory';

        // Generate login URL
        $baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                   "://" . $_SERVER['HTTP_HOST'] .
                   str_replace('/MAIN_ADMIN', '', dirname($_SERVER['PHP_SELF']));
        $dashboardURL = $baseURL . '/GUEST/guest_dashboard.php';

        // Build items list
        $itemsList = '';
        if ($items && is_array($items)) {
            $itemsList = '<ul>';
            foreach ($items as $item) {
                $thing = htmlspecialchars($item['thing'] ?? '');
                $qty = htmlspecialchars($item['qty'] ?? '');
                $itemsList .= "<li>{$thing} (Quantity: {$qty})</li>";
            }
            $itemsList .= '</ul>';
        }

        // Email content (HTML)
        $mail->isHTML(true);
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .items-list { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745; margin: 20px 0; }
                .button { display: inline-block; background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
                .alert { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Borrow Request Approved!</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$guestName}!</h2>

                    <div class='alert'>
                        <strong>✅ Great news!</strong> Your borrow request has been approved and you can now pick up your items.
                    </div>

                    <p><strong>Submission Number:</strong> {$submissionNumber}</p>

                    <div class='items-list'>
                        <h3>Approved Items:</h3>
                        {$itemsList}
                    </div>

                    <h3>Next Steps:</h3>
                    <ol>
                        <li>Visit the PILAR Asset Inventory office to pick up your items</li>
                        <li>Bring a valid ID for verification</li>
                        <li>Return items by the scheduled return date</li>
                        <li>Ensure items are returned in good condition</li>
                    </ol>

                    <h3>Important Reminders:</h3>
                    <ul>
                        <li>Late returns may result in penalties</li>
                        <li>Damaged items may require repair or replacement costs</li>
                        <li>Contact the office if you need to extend the borrowing period</li>
                    </ul>

                    <p>Click the button below to view your borrowing history:</p>
                    <a href='{$dashboardURL}' class='button'>View My Dashboard</a>

                    <p>If the button doesn't work, copy and paste this URL into your browser:</p>
                    <p><a href='{$dashboardURL}'>{$dashboardURL}</a></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from PILAR Asset Inventory System.<br>
                    Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " PILAR Asset Inventory System. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        // Alternative plain text content
        $mail->AltBody = "
BORROW REQUEST APPROVED - PILAR ASSET INVENTORY

Hello {$guestName}!

Great news! Your borrow request has been approved and you can now pick up your items.

Submission Number: {$submissionNumber}

Approved Items:" .
        ($items && is_array($items) ? "\n" . implode("\n", array_map(function($item) {
            $thing = $item['thing'] ?? '';
            $qty = $item['qty'] ?? '';
            return "- {$thing} (Quantity: {$qty})";
        }, $items)) : "") . "

NEXT STEPS:
1. Visit the PILAR Asset Inventory office to pick up your items
2. Bring a valid ID for verification
3. Return items by the scheduled return date
4. Ensure items are returned in good condition

IMPORTANT REMINDERS:
- Late returns may result in penalties
- Damaged items may require repair or replacement costs
- Contact the office if you need to extend the borrowing period

View your dashboard: {$dashboardURL}

---
This is an automated message from PILAR Asset Inventory System.
Please do not reply to this email.

© " . date('Y') . " PILAR Asset Inventory System. All rights reserved.
";

        $mail->send();

        return [
            'success' => true,
            'message' => 'Approval email sent successfully to ' . $email
        ];

    } catch (Exception $e) {
        error_log("Borrow approval email failed: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to send approval email: ' . $e->getMessage()
        ];
    }
}

/**
 * Send borrow request rejection email
 * @param string $email Guest's email address
 * @param string $guestName Guest's full name
 * @param string $submissionNumber Submission number
 * @param array $items Array of requested items
 * @param string $reason Reason for rejection (optional)
 * @return array Result array with success status and message
 */
function sendBorrowRejectionEmail($email, $guestName, $submissionNumber, $items, $reason = '') {
    try {
        $mail = configurePHPMailer();

        // Email recipient
        $mail->addAddress($email, $guestName);

        // Email subject
        $mail->Subject = 'Borrow Request Not Approved - PILAR Asset Inventory';

        // Generate login URL
        $baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                   "://" . $_SERVER['HTTP_HOST'] .
                   str_replace('/MAIN_ADMIN', '', dirname($_SERVER['PHP_SELF']));
        $dashboardURL = $baseURL . '/GUEST/guest_dashboard.php';

        // Build items list
        $itemsList = '';
        if ($items && is_array($items)) {
            $itemsList = '<ul>';
            foreach ($items as $item) {
                $thing = htmlspecialchars($item['thing'] ?? '');
                $qty = htmlspecialchars($item['qty'] ?? '');
                $itemsList .= "<li>{$thing} (Quantity: {$qty})</li>";
            }
            $itemsList .= '</ul>';
        }

        // Email content (HTML)
        $mail->isHTML(true);
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .items-list { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545; margin: 20px 0; }
                .button { display: inline-block; background: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
                .alert { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .retry-info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⚠️ Borrow Request Not Approved</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$guestName},</h2>

                    <div class='alert'>
                        <strong>❌ Request Status:</strong> Your borrow request has not been approved at this time.
                    </div>

                    <p><strong>Submission Number:</strong> {$submissionNumber}</p>
                    " . ($reason ? "<p><strong>Reason:</strong> {$reason}</p>" : "") . "

                    <div class='items-list'>
                        <h3>Requested Items:</h3>
                        {$itemsList}
                    </div>

                    <div class='retry-info'>
                        <h3>What You Can Do:</h3>
                        <ul>
                            <li>Review the reason for rejection above (if provided)</li>
                            <li>Contact the PILAR Asset Inventory office for more information</li>
                            <li>Submit a new request with different items or dates if appropriate</li>
                            <li>Check asset availability before submitting future requests</li>
                        </ul>
                    </div>

                    <h3>Alternative Actions:</h3>
                    <ul>
                        <li><strong>Check Availability:</strong> Some items may be temporarily unavailable</li>
                        <li><strong>Different Dates:</strong> Try requesting for different borrow/return dates</li>
                        <li><strong>Fewer Items:</strong> Consider borrowing fewer items at once</li>
                        <li><strong>Contact Office:</strong> Reach out to discuss your specific needs</li>
                    </ul>

                    <p>You can submit a new borrow request anytime:</p>
                    <a href='{$dashboardURL}' class='button'>Submit New Request</a>

                    <p>If the button doesn't work, copy and paste this URL into your browser:</p>
                    <p><a href='{$dashboardURL}'>{$dashboardURL}</a></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from PILAR Asset Inventory System.<br>
                    Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " PILAR Asset Inventory System. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        // Alternative plain text content
        $mail->AltBody = "
BORROW REQUEST NOT APPROVED - PILAR ASSET INVENTORY

Hello {$guestName},

Your borrow request has not been approved at this time.

Submission Number: {$submissionNumber}
" . ($reason ? "Reason: {$reason}\n" : "") . "
Requested Items:" .
        ($items && is_array($items) ? "\n" . implode("\n", array_map(function($item) {
            $thing = $item['thing'] ?? '';
            $qty = $item['qty'] ?? '';
            return "- {$thing} (Quantity: {$qty})";
        }, $items)) : "") . "

WHAT YOU CAN DO:
- Review the reason for rejection (if provided)
- Contact the PILAR Asset Inventory office for more information
- Submit a new request with different items or dates if appropriate
- Check asset availability before submitting future requests

ALTERNATIVE ACTIONS:
- Check Availability: Some items may be temporarily unavailable
- Different Dates: Try requesting for different borrow/return dates
- Fewer Items: Consider borrowing fewer items at once
- Contact Office: Reach out to discuss your specific needs

Submit a new request: {$dashboardURL}

---
This is an automated message from PILAR Asset Inventory System.
Please do not reply to this email.

© " . date('Y') . " PILAR Asset Inventory System. All rights reserved.
";

        $mail->send();

        return [
            'success' => true,
            'message' => 'Rejection email sent successfully to ' . $email
        ];

    } catch (Exception $e) {
        error_log("Borrow rejection email failed: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to send rejection email: ' . $e->getMessage()
        ];
    }
}
?>
