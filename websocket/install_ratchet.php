<?php
// This script will help install Ratchet using PHP's built-in web server

echo "Installing Ratchet WebSocket library...\n";

// Create composer.json if it doesn't exist
if (!file_exists(__DIR__ . '/../composer.json')) {
    file_put_contents(
        __DIR__ . '/../composer.json',
        json_encode([
            'require' => [
                'cboden/ratchet' => '^0.4.4'
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
    echo "Created composer.json\n";
}

// Check if vendor/autoload.php exists
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "Downloading Composer...\n";
    
    // Download Composer
    $composerSetup = file_get_contents('https://getcomposer.org/installer');
    file_put_contents('composer-setup.php', $composerSetup);
    
    echo "Installing dependencies...\n";
    
    // Run Composer install
    $output = [];
    exec('php composer-setup.php --install-dir=./ --filename=composer.phar 2>&1', $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "Installing Ratchet...\n";
        exec('php composer.phar install -d .. 2>&1', $output, $returnVar);
        
        if ($returnVar === 0) {
            echo "\n✅ Ratchet installed successfully!\n";
            echo "You can now start the WebSocket server with:\n";
            echo "  cd websocket\n";
            echo "  php simple_server.php\n\n";
        } else {
            echo "\n❌ Error installing dependencies. Output:\n";
            echo implode("\n", $output) . "\n";
        }
    } else {
        echo "\n❌ Error installing Composer. Output:\n";
        echo implode("\n", $output) . "\n";
    }
    
    // Clean up
    @unlink('composer-setup.php');
    @unlink('composer.phar');
} else {
    echo "Ratchet is already installed. You can start the WebSocket server with:\n";
    echo "  cd websocket\n";
    echo "  php simple_server.php\n";
}
