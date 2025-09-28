<?php
/**
 * Cleanup script for Remember Me tokens
 * This script should be run periodically (e.g., daily via cron job)
 * to remove expired tokens and maintain database performance
 */

require_once 'connect.php';
require_once 'includes/remember_me_helper.php';

try {
    echo "🧹 Starting Remember Me token cleanup...\n";
    
    // Clean up expired tokens
    $cleaned_count = cleanupExpiredTokens($conn);
    
    // Get statistics
    $stats_query = "SELECT 
        COUNT(*) as total_tokens,
        COUNT(DISTINCT user_id) as unique_users,
        MIN(created_at) as oldest_token,
        MAX(last_used) as most_recent_use
        FROM remember_tokens";
    
    $result = $conn->query($stats_query);
    $stats = $result->fetch_assoc();
    
    echo "✅ Cleanup completed successfully!\n";
    echo "📊 Statistics:\n";
    echo "   • Expired tokens removed: {$cleaned_count}\n";
    echo "   • Active tokens remaining: {$stats['total_tokens']}\n";
    echo "   • Users with active tokens: {$stats['unique_users']}\n";
    
    if ($stats['oldest_token']) {
        echo "   • Oldest active token: " . date('Y-m-d H:i:s', strtotime($stats['oldest_token'])) . "\n";
    }
    
    if ($stats['most_recent_use']) {
        echo "   • Most recent token use: " . date('Y-m-d H:i:s', strtotime($stats['most_recent_use'])) . "\n";
    }
    
    echo "\n";
    
    // Recommendations
    if ($stats['total_tokens'] > 1000) {
        echo "⚠️  Warning: High number of active tokens ({$stats['total_tokens']}). Consider:\n";
        echo "   • Running cleanup more frequently\n";
        echo "   • Reducing token expiration time\n";
        echo "   • Limiting tokens per user\n";
    }
    
    if ($cleaned_count > 100) {
        echo "ℹ️  Info: Cleaned up {$cleaned_count} expired tokens. Consider running cleanup more frequently.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during cleanup: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>
