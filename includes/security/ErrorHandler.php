<?php
/**
 * Error Handler
 * 
 * Custom error and exception handling for the application
 */

class ErrorHandler {
    /**
     * Initialize error handling
     */
    public static function init() {
        // Set error handler
        set_error_handler([self::class, 'handleError']);
        
        // Set exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Set shutdown function to catch fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    /**
     * Handle PHP errors
     */
    public static function handleError($errno, $errstr, $errfile = '', $errline = 0, $errcontext = []) {
        // Don't execute the built-in PHP error handler
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $errorType = self::getErrorType($errno);
        $message = "$errorType: $errstr in $errfile on line $errline";
        
        // Log the error
        self::logError($message, $errno, $errfile, $errline);
        
        // Display error if in development mode
        if (SecurityConfig::isDevelopment()) {
            self::displayError($message, $errno, $errfile, $errline);
        } else {
            // In production, show a generic error page
            if ($errno === E_USER_ERROR) {
                self::displayFriendlyError();
            }
        }
        
        // Don't execute PHP's internal error handler
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        $message = "Uncaught exception: " . $exception->getMessage();
        $errfile = $exception->getFile();
        $errline = $exception->getLine();
        
        // Log the exception
        self::logError($message, E_ERROR, $errfile, $errline, $exception->getTraceAsString());
        
        // Display error if in development mode
        if (SecurityConfig::isDevelopment()) {
            echo "<h1>Uncaught Exception</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($errfile) . " on line " . $errline . "</p>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            // In production, show a generic error page
            self::displayFriendlyError();
        }
    }
    
    /**
     * Handle fatal errors
     */
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            $message = "Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
            
            // Log the error
            self::logError($message, $error['type'], $error['file'], $error['line']);
            
            // Display error if in development mode
            if (SecurityConfig::isDevelopment()) {
                echo "<h1>Fatal Error</h1>";
                echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
                echo "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . " on line " . $error['line'] . "</p>";
            } else {
                // In production, show a generic error page
                self::displayFriendlyError();
            }
        }
    }
    
    /**
     * Log error to file
     */
    private static function logError($message, $errno, $file, $line, $trace = '') {
        $logDir = dirname(__DIR__, 2) . '/logs';
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/error-' . date('Y-m-d') . '.log';
        $errorType = self::getErrorType($errno);
        $date = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $request = $_SERVER['REQUEST_URI'] ?? '';
        
        $logMessage = "[$date] [$ip] [$errorType] $message\n";
        $logMessage .= "Request: $request\n";
        
        if (!empty($trace)) {
            $logMessage .= "Stack trace:\n$trace\n";
        }
        
        $logMessage .= str_repeat('-', 80) . "\n";
        
        // Write to error log
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Display a friendly error page
     */
    private static function displayFriendlyError() {
        // Don't send output if headers already sent
        if (headers_sent()) {
            return;
        }
        
        // Set 500 Internal Server Error status
        http_response_code(500);
        
        // Display a friendly error page
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Error - Something went wrong</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
                .container { max-width: 800px; margin: 0 auto; padding: 20px; }
                h1 { color: #d32f2f; }
                .error-box { 
                    background-color: #f8d7da; 
                    border: 1px solid #f5c6cb; 
                    color: #721c24; 
                    padding: 15px; 
                    border-radius: 4px; 
                    margin-bottom: 20px;
                }
                .btn { 
                    display: inline-block; 
                    background: #007bff; 
                    color: white; 
                    padding: 10px 20px; 
                    text-decoration: none; 
                    border-radius: 4px; 
                    margin-top: 10px;
                }
                .btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Oops! Something went wrong.</h1>
                <div class="error-box">
                    <p>We apologize, but an unexpected error occurred while processing your request. Our team has been notified and we are working to fix the issue.</p>
                    <p>Please try again later or contact support if the problem persists.</p>
                </div>
                <a href="/" class="btn">Return to Homepage</a>
            </div>
        </body>
        </html>';
        
        echo $html;
        exit;
    }
    
    /**
     * Display error details (for development)
     */
    private static function displayError($message, $errno, $errfile, $errline) {
        if (headers_sent() || !SecurityConfig::isDevelopment()) {
            return;
        }
        
        $errorType = self::getErrorType($errno);
        
        echo "<div style='background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:15px;margin:10px;border-radius:4px;font-family:monospace;'>";
        echo "<strong>$errorType</strong>: $message<br>";
        echo "<small>File: $errfile on line $errline</small>";
        
        // Show backtrace for errors
        if (in_array($errno, [E_ERROR, E_WARNING, E_PARSE, E_NOTICE, E_CORE_ERROR, 
                              E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, 
                              E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE])) {
            echo "<div style='margin-top:10px;padding:10px;background:#f1f1f1;border-radius:4px;'>";
            echo "<strong>Backtrace:</strong><br>";
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            echo "<pre style='white-space:pre-wrap;'>";
            foreach ($backtrace as $i => $trace) {
                $file = $trace['file'] ?? '';
                $line = $trace['line'] ?? '';
                $function = $trace['function'] ?? '';
                $class = $trace['class'] ?? '';
                $type = $trace['type'] ?? '';
                
                echo "#$i $file($line): ";
                if ($class) {
                    echo "$class$type";
                }
                echo "$function()\n";
            }
            echo "</pre>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    /**
     * Convert error number to error type
     */
    private static function getErrorType($errno) {
        $errorTypes = [
            E_ERROR             => 'Fatal Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
        ];
        
        return $errorTypes[$errno] ?? "Unknown Error ($errno)";
    }
}

// Include SecurityConfig if not already included
if (!class_exists('SecurityConfig')) {
    require_once __DIR__ . '/SecurityConfig.php';
}

// Initialize error handling
ErrorHandler::init();
