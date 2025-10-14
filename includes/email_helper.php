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
?>
