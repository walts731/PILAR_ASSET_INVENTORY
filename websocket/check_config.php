<?php
echo "PHP Version: " . phpversion() . "\n";
echo "OS: " . PHP_OS . "\n\n";

// Check sockets extension
if (extension_loaded('sockets')) {
    echo "✅ Sockets extension is loaded\n";
    
    // Check if socket functions are available
    $functions = ['socket_create', 'socket_bind', 'socket_listen', 'socket_accept', 'socket_read', 'socket_write'];
    $missing = [];
    
    foreach ($functions as $func) {
        if (!function_exists($func)) {
            $missing[] = $func;
        }
    }
    
    if (empty($missing)) {
        echo "✅ All required socket functions are available\n";
    } else {
        echo "❌ Missing socket functions: " . implode(', ', $missing) . "\n";
    }
    
} else {
    echo "❌ Sockets extension is NOT loaded\n";
}

// Check for common issues
echo "\nChecking for common issues:\n";

echo "PHP_SAPI: " . PHP_SAPI . "\n";

// Check if running from command line
if (PHP_SAPI !== 'cli') {
    echo "⚠️  Note: For WebSocket server, it's better to run PHP from command line (CLI)\n";
}

// Check for safe mode (should be off)
if (ini_get('safe_mode')) {
    echo "⚠️  Warning: safe_mode is enabled. This might cause issues.\n";
}

// Check for disabled functions
$disabled = ini_get('disable_functions');
if (!empty($disabled)) {
    $disabled = array_map('trim', explode(',', $disabled));
    $socketFunctions = array_intersect($disabled, $functions);
    
    if (!empty($socketFunctions)) {
        echo "❌ The following socket functions are disabled in php.ini: " . implode(', ', $socketFunctions) . "\n";
    } else {
        echo "✅ No socket functions are disabled\n";
    }
} else {
    echo "✅ No functions are disabled in php.ini\n";
}

// Check for open_basedir restrictions
$openBaseDir = ini_get('open_basedir');
if (!empty($openBaseDir)) {
    echo "⚠️  open_basedir is set: $openBaseDir\n";
    echo "   This might cause permission issues if the script tries to access files outside these directories.\n";
}

// Check for SELinux (on Linux) or other security modules
echo "\nTo run the WebSocket server, use this command from the command line:\n";
echo "cd " . __DIR__ . "\n";
echo "C:\\Users\\Admin\\Desktop\\XAMPP\\php\\php.exe simple_websocket.php\n";
