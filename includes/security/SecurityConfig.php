<?php
/**
 * Security Configuration
 * 
 * Centralized security settings and functions for the application
 */

class SecurityConfig {
    // Session configuration
    const SESSION_TIMEOUT = 1800; // 30 minutes in seconds
    const SESSION_NAME = 'PILAR_ASSET_SESSION';
    const SESSION_REGENERATE_INTERVAL = 300; // 5 minutes in seconds
    
    // Password policy
    const MIN_PASSWORD_LENGTH = 12;
    const PASSWORD_REQUIRE_UPPERCASE = true;
    const PASSWORD_REQUIRE_NUMBER = true;
    const PASSWORD_REQUIRE_SPECIAL = true;
    
    // Login security
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOGIN_LOCKOUT_TIME = 1800; // 30 minutes in seconds
    
    // CSRF Protection
    const CSRF_TOKEN_LENGTH = 32;
    const CSRF_TOKEN_LIFETIME = 3600; // 1 hour in seconds
    
    // Headers
    const SECURITY_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net;"
    ];
    
    // Rate limiting
    const RATE_LIMIT_REQUESTS = 100; // Max requests
    const RATE_LIMIT_WINDOW = 60; // Per 60 seconds
    
    /**
     * Initialize security settings
     */
    public static function init() {
        // Set secure session parameters
        self::configureSession();
        
        // Set security headers
        self::setSecurityHeaders();
        
        // Enable error reporting in development, disable in production
        if (self::isDevelopment()) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
    }
    
    /**
     * Configure secure session parameters
     */
    private static function configureSession() {
        // Set session name
        session_name(self::SESSION_NAME);
        
        // Secure session parameters
        $sessionParams = session_get_cookie_params();
        
        // Set secure session cookie parameters
        session_set_cookie_params([
            'lifetime' => self::SESSION_TIMEOUT,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Prevent session fixation
        ini_set('session.use_strict_mode', 1);
        
        // Only use cookies for session ID
        ini_set('session.use_only_cookies', 1);
        
        // Use strong session ID generation
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', '32');
        
        // Start the session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically
        self::regenerateSessionId();
        
        // Check for session timeout
        self::checkSessionTimeout();
    }
    
    /**
     * Set security headers
     */
    private static function setSecurityHeaders() {
        if (headers_sent()) {
            return;
        }
        
        foreach (self::SECURITY_HEADERS as $header => $value) {
            header("$header: $value");
        }
    }
    
    /**
     * Regenerate session ID periodically
     */
    private static function regenerateSessionId() {
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > self::SESSION_REGENERATE_INTERVAL) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Check for session timeout
     */
    private static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT)) {
            // Last request was more than 30 minutes ago
            session_unset();
            session_destroy();
            header('Location: /PILAR_ASSET_INVENTORY/index.php?timeout=1');
            exit();
        }
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Check if the current request is using HTTPS
     */
    public static function isHttps() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Check if the application is in development mode
     */
    public static function isDevelopment() {
        return ($_SERVER['SERVER_NAME'] === 'localhost' || 
                strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
                strpos($_SERVER['SERVER_NAME'], 'dev.') === 0);
    }
    
    /**
     * Generate a CSRF token
     */
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        // Clean up expired tokens
        $now = time();
        foreach ($_SESSION['csrf_tokens'] as $key => $tokenData) {
            if ($tokenData['expires'] < $now) {
                unset($_SESSION['csrf_tokens'][$key]);
            }
        }
        
        // Generate new token
        $token = bin2hex(random_bytes(self::CSRF_TOKEN_LENGTH));
        $_SESSION['csrf_tokens'][$token] = [
            'created' => $now,
            'expires' => $now + self::CSRF_TOKEN_LIFETIME
        ];
        
        return $token;
    }
    
    /**
     * Validate a CSRF token
     */
    public static function validateCsrfToken($token) {
        if (empty($token) || empty($_SESSION['csrf_tokens'][$token])) {
            return false;
        }
        
        $tokenData = $_SESSION['csrf_tokens'][$token];
        
        // Check if token is expired
        if ($tokenData['expires'] < time()) {
            unset($_SESSION['csrf_tokens'][$token]);
            return false;
        }
        
        // Token is valid, remove it to prevent reuse
        unset($_SESSION['csrf_tokens'][$token]);
        return true;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        // Remove whitespace from the beginning and end of the string
        $data = trim($data);
        
        // Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Validate password against policy
     */
    public static function validatePassword($password) {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return 'Password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters long';
        }
        
        if (self::PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter';
        }
        
        if (self::PASSWORD_REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number';
        }
        
        if (self::PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must contain at least one special character';
        }
        
        return true;
    }
    
    /**
     * Hash a password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify a password against a hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if user has too many failed login attempts
     */
    public static function isRateLimited($identifier, $maxAttempts = null, $window = null) {
        $maxAttempts = $maxAttempts ?? self::MAX_LOGIN_ATTEMPTS;
        $window = $window ?? self::LOGIN_LOCKOUT_TIME;
        
        if (!isset($_SESSION['login_attempts'][$identifier])) {
            $_SESSION['login_attempts'][$identifier] = [
                'attempts' => 0,
                'last_attempt' => 0,
                'locked_until' => 0
            ];
        }
        
        $now = time();
        $attempts = &$_SESSION['login_attempts'][$identifier];
        
        // Reset attempts if window has passed
        if ($now - $attempts['last_attempt'] > $window) {
            $attempts['attempts'] = 0;
            $attempts['locked_until'] = 0;
        }
        
        // Check if account is locked
        if ($attempts['locked_until'] > $now) {
            return true;
        }
        
        // Increment attempts
        $attempts['attempts']++;
        $attempts['last_attempt'] = $now;
        
        // Lock account if too many attempts
        if ($attempts['attempts'] >= $maxAttempts) {
            $attempts['locked_until'] = $now + $window;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get time remaining until account is unlocked
     */
    public static function getLockoutTimeRemaining($identifier) {
        if (!isset($_SESSION['login_attempts'][$identifier]['locked_until'])) {
            return 0;
        }
        
        $remaining = $_SESSION['login_attempts'][$identifier]['locked_until'] - time();
        return max(0, $remaining);
    }
    
    /**
     * Reset login attempts for an identifier
     */
    public static function resetLoginAttempts($identifier) {
        if (isset($_SESSION['login_attempts'][$identifier])) {
            unset($_SESSION['login_attempts'][$identifier]);
        }
    }
}

// Initialize security settings
SecurityConfig::init();
