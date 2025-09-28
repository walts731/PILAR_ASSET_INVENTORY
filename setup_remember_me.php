<?php
/**
 * Setup script for Remember Me functionality
 * This script creates the remember_tokens table and cleans up expired tokens
 */

require_once 'connect.php';

try {
    // Read and execute the SQL file
    $sql_file = __DIR__ . '/create_remember_tokens_table.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: " . $sql_file);
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Split SQL statements by semicolon and execute each one
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !str_starts_with($statement, '--')) {
            if ($conn->query($statement) === FALSE) {
                throw new Exception("Error executing SQL: " . $conn->error . "\nStatement: " . $statement);
            }
        }
    }
    
    echo "✅ Remember Me functionality setup completed successfully!\n";
    echo "📋 The following has been set up:\n";
    echo "   • remember_tokens table created\n";
    echo "   • Foreign key constraints added\n";
    echo "   • Indexes created for performance\n";
    echo "\n";
    echo "🔧 Next steps:\n";
    echo "   1. Test login with 'Remember Me' checkbox\n";
    echo "   2. Verify auto-login works on return visits\n";
    echo "   3. Test logout functionality\n";
    echo "\n";
    echo "💡 Features included:\n";
    echo "   • Secure token generation (64-character hex)\n";
    echo "   • 30-day default token expiration\n";
    echo "   • Automatic cleanup of old tokens\n";
    echo "   • User agent and IP tracking\n";
    echo "   • Secure cookie settings\n";
    echo "   • Audit logging integration\n";
    
} catch (Exception $e) {
    echo "❌ Error setting up Remember Me functionality: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>
