<?php
/**
 * Check if the WebSocket server is running
 */

$fp = @fsockopen('127.0.0.1', 8080, $errno, $errstr, 1);

if ($fp) {
    echo "WebSocket server is running on port 8080\n";
    fclose($fp);
    exit(0); // Success
} else {
    echo "WebSocket server is NOT running: $errstr ($errno)\n";
    exit(1); // Error
}
