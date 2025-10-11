<?php
// Simple script to download and extract Ratchet
echo "Downloading Ratchet...\n";

// Create vendor directory if it doesn't exist
if (!is_dir(__DIR__ . '/vendor')) {
    mkdir(__DIR__ . '/vendor', 0777, true);
}

// Download Ratchet
$ratchetZip = __DIR__ . '/ratchet.zip';
$ratchetUrl = 'https://github.com/ratchetphp/Ratchet/archive/refs/tags/v0.4.4.zip';

if (!file_exists($ratchetZip)) {
    echo "Downloading Ratchet from GitHub...\n";
    $zipContent = file_get_contents($ratchetUrl);
    if ($zipContent === false) {
        die("Failed to download Ratchet. Please check your internet connection.\n");
    }
    file_put_contents($ratchetZip, $zipContent);
}

// Extract Ratchet
echo "Extracting Ratchet...\n";
$zip = new ZipArchive;
if ($zip->open($ratchetZip) === TRUE) {
    $zip->extractTo(__DIR__ . '/vendor');
    $zip->close();
    
    // Rename directory to match PSR-4 autoloading
    if (is_dir(__DIR__ . '/vendor/Ratchet-0.4.4')) {
        rename(__DIR__ . '/vendor/Ratchet-0.4.4', __DIR__ . '/vendor/ratchet');
    }
    
    // Create a simple autoloader
    $autoloader = <<<'AUTOLOADER'
<?php
spl_autoload_register(function ($class) {
    $prefix = 'Ratchet\\';
    $base_dir = __DIR__ . '/ratchet/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
AUTOLOADER;
    
    file_put_contents(__DIR__ . '/vendor/autoload.php', $autoloader);
    
    echo "✅ Ratchet installed successfully!\n";
    echo "You can now start the WebSocket server with:\n";
    echo "  cd websocket\n";
    echo "  php simple_server.php\n";
} else {
    echo "Failed to extract Ratchet.\n";
}
