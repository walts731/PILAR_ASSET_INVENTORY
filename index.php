<?php
session_start();
require_once "connect.php"; // Include database connection
// Fetch system settings (logo and title)
$system = [
    'logo' => 'default-logo.png',
    'system_title' => 'Inventory System'
];
if (isset($conn) && $conn instanceof mysqli) {
    $result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $system = $result->fetch_assoc();
    }
}

// Fetch legal documents (Privacy Policy and Terms of Service)
$privacy_policy = null;
$terms_of_service = null;

if (isset($conn) && $conn instanceof mysqli) {
    // Get Privacy Policy
    $stmt = $conn->prepare("SELECT title, content, version, effective_date FROM legal_documents WHERE document_type = 'privacy_policy' AND is_active = 1 ORDER BY created_at DESC LIMIT 1");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $privacy_policy = $result->fetch_assoc();
        }
        $stmt->close();
    }
    
    // Get Terms of Service
    $stmt = $conn->prepare("SELECT title, content, version, effective_date FROM legal_documents WHERE document_type = 'terms_of_service' AND is_active = 1 ORDER BY created_at DESC LIMIT 1");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $terms_of_service = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

// Include login engine AFTER fetching system settings to avoid closing the connection prematurely
require_once "engine/login_engine.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - <?= htmlspecialchars($system['system_title']) ?></title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/login.css">

    <!-- Scoped UI Enhancements aligned with sidebar design -->
    <style>
        /* Match sidebar gradient */
        .login_body { background: linear-gradient(180deg, #0b5ed7 0%, #0a58ca 45%, #0948a6 100%); }

        /* Brand header styles (mirrors sidebar brand) */
        .brand-header { text-align: center; }
        .brand-logo-wrap {
            width: 58px; height: 58px; border-radius: 50%; background: #ffffff;
            display: inline-flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18), inset 0 0 0 6px rgba(255, 255, 255, 0.4);
            margin-bottom: 8px;
        }
        .brand-logo-wrap img { width: 38px; height: 38px; object-fit: contain; filter: none; }
        .brand-title { color: #1f2d3d; line-height: 1.1; }
        .brand-title strong { font-weight: 700; font-size: 1rem; letter-spacing: 0.3px; display: block; }
        .brand-title span { display: block; font-size: .8rem; opacity: 0.85; color: #6b7a90; margin-top: 2px; }

        /* Card and controls */
        .login-card {
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 12px 28px rgba(9, 72, 166, 0.12);
            border-radius: 16px;
            backdrop-filter: saturate(1.2);
        }
        .login-card .form-label { font-weight: 600; color: #22324b; }
        .login-card .input-group-text { background: #fff; }
        .login-card .form-control { padding-top: .6rem; padding-bottom: .6rem; }
        .login-card .helper { color: #6c7a89; font-size: .9rem; }
        .login-card .btn-primary {
            background-color: #0b5ed7; border-color: #0a58ca;
            box-shadow: 0 6px 18px rgba(11,94,215,.28);
        }
        .login-card .btn-primary:hover { background-color: #0a58ca; border-color: #0948a6; transform: translateY(-1px); }
        .login-card .btn-primary:active { transform: translateY(0); }
        .divider {
            display: flex; align-items: center; gap: 10px; color: #8a97ab; font-size: .9rem;
        }
        .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: #e6eaf2; }
        .text-muted a { text-decoration: none; }
        .text-muted a:hover { text-decoration: underline; }
        .carousel .typing-text { min-height: 1.5em; }
    </style>
</head>

<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center login_body">

    <div class="container-fluid">
        <div class="row min-vh-100">

            <!-- Left Container (Carousel with Background and Typing Text) -->
            <div class="col-md-6 p-0">
                <div id="welcomeCarousel" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-inner h-100">

                        <!-- Slide 1 -->
                        <div class="carousel-item active h-100 position-relative bg-cover bg-center" style="background-image: url('img/pilar.jpg');">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
                            <div class="d-flex align-items-center justify-content-center h-100 z-2 position-relative text-white text-center px-4">
                                <div>
                                    <h1 class="fw-bold mb-3">Welcome!</h1>
                                    <p class="fs-5 typing-text" data-text="to <?= htmlspecialchars($system['system_title']) ?>. Please log in to continue."></p>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="carousel-item h-100 position-relative bg-cover bg-center" style="background-image: url('img/chess.jpg');">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
                            <div class="d-flex align-items-center justify-content-center h-100 z-2 position-relative text-white text-center px-4">
                                <div>
                                    <h1 class="fw-bold mb-3">Efficient Tracking</h1>
                                    <p class="fs-5 typing-text" data-text="Keep your inventory organized and accessible."></p>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="carousel-item h-100 position-relative bg-cover bg-center" style="background-image: url('img/pilar3.jpg');">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
                            <div class="d-flex align-items-center justify-content-center h-100 z-2 position-relative text-white text-center px-4">
                                <div>
                                    <h1 class="fw-bold mb-3">Team Collaboration</h1>
                                    <p class="fs-5 typing-text" data-text="Work together with your team seamlessly."></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right Container (Login Form) -->
            <div class="col-md-6 d-flex align-items-center justify-content-center ">
                <div class="card login-card p-4 w-100" style="max-width: 420px;">
                    <div class="brand-header mb-3" aria-label="Application brand">
                        <div class="brand-logo-wrap">
                            <img src="img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo">
                        </div>
                        <div class="brand-title">
                            <strong><?= htmlspecialchars($system['system_title']) ?></strong>
                            <span>Secure Sign In</span>
                        </div>
                    </div>
                    <div class="text-center mb-1">
                        <h3 class="fw-bold mb-0">Welcome Back</h3>
                        <div class="text-muted">Please sign in to continue</div>
                    </div>

                    <?php if (!empty($login_error)): ?>
                        <div class="mb-3" aria-live="polite" aria-atomic="true">
                            <?= $login_error ?>
                        </div>
                    <?php endif; ?>

                    <?php 
                    // Handle logout messages
                    if (isset($_GET['message'])) {
                        if ($_GET['message'] === 'logged_out_all') {
                            echo '<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                                <i class="bi bi-check-circle me-2"></i>You have been logged out from all devices successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
                        }
                    }
                    ?>

                    <form method="post" autocomplete="off" novalidate>
                        <input type="text" name="fakeusernameremembered" style="display:none">
                        <input type="password" name="fakepasswordremembered" style="display:none">

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" autocomplete="username" required aria-required="true" />
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-2">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required aria-required="true" />
                                <span class="input-group-text bg-white border-start-0" id="togglePassword" style="cursor: pointer;" title="Show/Hide password" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember_me" value="1" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="small" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" id="loginBtn" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                        </button>

                        <div class="mt-3 text-center text-muted">
                            By signing in, you agree to our <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms</a>.
                        </div>
                    </form>
                    <div class="mt-3 text-center text-muted small">
                        <span>&copy; <?= date('Y'); ?> <?= htmlspecialchars($system['system_title']) ?> • All rights reserved</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="bi bi-key me-2"></i>Reset Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="forgotPasswordAlert"></div>
                    
                    <form id="forgotPasswordForm">
                        <div class="text-center mb-4">
                            <i class="bi bi-envelope-exclamation text-primary" style="font-size: 3rem;"></i>
                            <h6 class="mt-2 text-muted">Enter your username to receive a password reset link</h6>
                        </div>
                        
                        <div class="mb-3">
                            <label for="resetUsername" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="resetUsername" name="username" placeholder="Enter your username" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="sendResetBtn">
                                <i class="bi bi-send me-2"></i>Send Reset Link
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-arrow-left me-2"></i>Back to Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Note:</strong> The reset link will be sent to your registered email address and will expire in 1 hour.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="privacyModalLabel">
                        <i class="bi bi-shield-lock me-2"></i>Privacy Policy
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="privacy-content">
                        <p class="text-muted mb-4">
                            <strong>Effective Date:</strong> <?= date('F j, Y'); ?><br>
                            <strong>Last Updated:</strong> <?= date('F j, Y'); ?>
                        </p>

                        <h6 class="fw-bold text-primary mb-3">1. Information We Collect</h6>
                        <p>When you use the PILAR Asset Inventory System, we collect the following information:</p>
                        <ul>
                            <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>
                            <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>
                            <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>
                            <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">2. How We Use Your Information</h6>
                        <p>We use your information to:</p>
                        <ul>
                            <li>Provide and maintain the asset inventory management system</li>
                            <li>Authenticate users and maintain account security</li>
                            <li>Track asset movements and maintain audit trails</li>
                            <li>Send important system notifications and updates</li>
                            <li>Improve system functionality and user experience</li>
                            <li>Comply with legal and regulatory requirements</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">3. Information Sharing</h6>
                        <p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>
                        <ul>
                            <li>With authorized personnel within your organization</li>
                            <li>When required by law or legal process</li>
                            <li>To protect the security and integrity of our systems</li>
                            <li>With your explicit consent</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">4. Data Security</h6>
                        <p>We implement appropriate security measures to protect your information:</p>
                        <ul>
                            <li>Encrypted password storage using industry-standard hashing</li>
                            <li>Secure session management with timeout controls</li>
                            <li>Regular security audits and monitoring</li>
                            <li>Access controls based on user roles and permissions</li>
                            <li>Secure data transmission using HTTPS protocols</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">5. Data Retention</h6>
                        <p>We retain your information for as long as necessary to:</p>
                        <ul>
                            <li>Provide the services you've requested</li>
                            <li>Maintain audit trails as required by regulations</li>
                            <li>Comply with legal obligations</li>
                            <li>Resolve disputes and enforce agreements</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">6. Your Rights</h6>
                        <p>You have the right to:</p>
                        <ul>
                            <li>Access and review your personal information</li>
                            <li>Request corrections to inaccurate data</li>
                            <li>Request deletion of your account (subject to legal requirements)</li>
                            <li>Receive information about data breaches that may affect you</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">7. Cookies and Tracking</h6>
                        <p>We use cookies and similar technologies to:</p>
                        <ul>
                            <li>Maintain your login session</li>
                            <li>Remember your preferences</li>
                            <li>Provide "Remember Me" functionality</li>
                            <li>Analyze system usage for improvements</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">8. Changes to This Policy</h6>
                        <p>We may update this Privacy Policy from time to time. We will notify users of any material changes by:</p>
                        <ul>
                            <li>Posting the updated policy on this page</li>
                            <li>Sending email notifications for significant changes</li>
                            <li>Updating the "Last Updated" date at the top of this policy</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">9. Contact Information</h6>
                        <p>If you have questions about this Privacy Policy or our data practices, please contact:</p>
                        <div class="bg-light p-3 rounded">
                            <strong>PILAR Asset Inventory System Administrator</strong><br>
                            Email: <a href="mailto:admin@pilar-system.com">admin@pilar-system.com</a><br>
                            Phone: +1 (555) 123-4567<br>
                            Address: [Your Organization Address]
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms of Service Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="termsModalLabel">
                        <i class="bi bi-file-text me-2"></i>Terms of Service
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="terms-content">
                        <p class="text-muted mb-4">
                            <strong>Effective Date:</strong> <?= date('F j, Y'); ?><br>
                            <strong>Last Updated:</strong> <?= date('F j, Y'); ?>
                        </p>

                        <h6 class="fw-bold text-primary mb-3">1. Acceptance of Terms</h6>
                        <p>By accessing and using the PILAR Asset Inventory System ("the System"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>

                        <h6 class="fw-bold text-primary mb-3 mt-4">2. System Description</h6>
                        <p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>
                        <ul>
                            <li>Track and manage organizational assets</li>
                            <li>Maintain detailed asset records and histories</li>
                            <li>Provide role-based access controls</li>
                            <li>Generate reports and analytics</li>
                            <li>Ensure compliance with asset management policies</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">3. User Accounts and Responsibilities</h6>
                        <p><strong>Account Security:</strong></p>
                        <ul>
                            <li>You are responsible for maintaining the confidentiality of your login credentials</li>
                            <li>You must notify administrators immediately of any unauthorized access</li>
                            <li>You agree to use strong passwords and enable security features when available</li>
                            <li>You are liable for all activities that occur under your account</li>
                        </ul>
                        
                        <p><strong>Authorized Use:</strong></p>
                        <ul>
                            <li>Access is granted only to authorized personnel</li>
                            <li>You may only access data and functions appropriate to your assigned role</li>
                            <li>Sharing of login credentials is strictly prohibited</li>
                            <li>You must comply with your organization's asset management policies</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">4. Prohibited Activities</h6>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Attempt to gain unauthorized access to any part of the System</li>
                            <li>Interfere with or disrupt the System's operation</li>
                            <li>Use the System for any illegal or unauthorized purpose</li>
                            <li>Reverse engineer, decompile, or disassemble any part of the System</li>
                            <li>Introduce viruses, malware, or other harmful code</li>
                            <li>Access or attempt to access accounts belonging to other users</li>
                            <li>Export or share sensitive asset data without proper authorization</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">5. Data Accuracy and Integrity</h6>
                        <p>Users are responsible for:</p>
                        <ul>
                            <li>Ensuring the accuracy of data entered into the System</li>
                            <li>Promptly updating asset information when changes occur</li>
                            <li>Reporting discrepancies or errors to system administrators</li>
                            <li>Following established procedures for asset management</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">6. System Availability</h6>
                        <p>While we strive to maintain continuous service:</p>
                        <ul>
                            <li>The System may be temporarily unavailable for maintenance</li>
                            <li>We do not guarantee 100% uptime or availability</li>
                            <li>Scheduled maintenance will be announced in advance when possible</li>
                            <li>Emergency maintenance may occur without prior notice</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">7. Intellectual Property</h6>
                        <p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p>
                        <ul>
                            <li>All software, designs, and documentation remain our property</li>
                            <li>You receive a limited license to use the System for its intended purpose</li>
                            <li>You may not copy, modify, or distribute any part of the System</li>
                            <li>Your organization retains ownership of data entered into the System</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">8. Privacy and Data Protection</h6>
                        <p>Your privacy is important to us:</p>
                        <ul>
                            <li>Please review our Privacy Policy for details on data handling</li>
                            <li>We implement security measures to protect your information</li>
                            <li>You consent to data processing as described in our Privacy Policy</li>
                            <li>We comply with applicable data protection regulations</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">9. Limitation of Liability</h6>
                        <p>To the maximum extent permitted by law:</p>
                        <ul>
                            <li>We provide the System "as is" without warranties</li>
                            <li>We are not liable for indirect, incidental, or consequential damages</li>
                            <li>Our total liability is limited to the amount paid for System access</li>
                            <li>You agree to indemnify us against claims arising from your use of the System</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">10. Termination</h6>
                        <p>These terms remain in effect until terminated:</p>
                        <ul>
                            <li>Your access may be suspended or terminated for violations of these terms</li>
                            <li>You may request account termination by contacting administrators</li>
                            <li>Upon termination, you must cease all use of the System</li>
                            <li>Certain provisions of these terms survive termination</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">11. Changes to Terms</h6>
                        <p>We reserve the right to modify these terms:</p>
                        <ul>
                            <li>Changes will be posted on this page with an updated effective date</li>
                            <li>Continued use after changes constitutes acceptance</li>
                            <li>Material changes will be communicated to users</li>
                            <li>You should review these terms periodically</li>
                        </ul>

                        <h6 class="fw-bold text-primary mb-3 mt-4">12. Governing Law</h6>
                        <p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p>

                        <h6 class="fw-bold text-primary mb-3 mt-4">13. Contact Information</h6>
                        <p>For questions about these Terms of Service, please contact:</p>
                        <div class="bg-light p-3 rounded">
                            <strong>PILAR Asset Inventory System Administrator</strong><br>
                            Email: <a href="mailto:admin@pilar-system.com">admin@pilar-system.com</a><br>
                            Phone: +1 (555) 123-4567<br>
                            Address: [Your Organization Address]
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Toggle Password -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
    
    <!-- Forgot Password JavaScript -->
    <script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('resetUsername').value.trim();
        const alertDiv = document.getElementById('forgotPasswordAlert');
        const submitBtn = document.getElementById('sendResetBtn');
        
        if (!username) {
            showAlert('Please enter your username.', 'warning');
            return;
        }
        
        // Show loading state
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
        submitBtn.disabled = true;
        
        // Send AJAX request
        fetch('forgot_password_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'username=' + encodeURIComponent(username)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                document.getElementById('forgotPasswordForm').reset();
                
                // Auto-close modal after 3 seconds
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
                }, 3000);
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Send Reset Link';
            submitBtn.disabled = false;
        });
        
        function showAlert(message, type) {
            alertDiv.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'x-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    });
    
    // Clear alerts when modal is closed
    document.getElementById('forgotPasswordModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('forgotPasswordAlert').innerHTML = '';
        document.getElementById('forgotPasswordForm').reset();
    });
    </script>
</body>


</html>