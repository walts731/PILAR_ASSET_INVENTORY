# Remember Me Functionality

This document describes the "Remember Me" functionality implemented for the PILAR Asset Inventory system.

## 🚀 Quick Setup

1. **Create the database table:**
   ```bash
   php setup_remember_me.php
   ```

2. **Test the functionality:**
   - Go to login page
   - Check "Remember me" checkbox
   - Login with valid credentials
   - Close browser and return - you should be automatically logged in

## 📋 Features

### ✅ Secure Token Management
- **64-character hex tokens** generated using `random_bytes(32)`
- **30-day default expiration** (configurable)
- **Automatic cleanup** of expired tokens
- **User agent and IP tracking** for security

### ✅ Database Integration
- **Foreign key constraints** ensure data integrity
- **Indexes** for optimal performance
- **Audit logging** integration for security monitoring
- **Token limit per user** (keeps only 3 most recent tokens)

### ✅ Security Features
- **Secure cookies** with HttpOnly, Secure, and SameSite flags
- **Token validation** with expiration checks
- **Automatic cleanup** of invalid tokens
- **User status verification** (only active users)

### ✅ User Experience
- **Seamless auto-login** on return visits
- **Role-based redirection** after auto-login
- **Logout options**: single device or all devices
- **Clear success messages** for user feedback

## 🗄️ Database Schema

```sql
CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);
```

## 🔧 Files Modified/Created

### New Files
- `includes/remember_me_helper.php` - Core functionality
- `create_remember_tokens_table.sql` - Database schema
- `setup_remember_me.php` - Setup script
- `cleanup_remember_tokens.php` - Maintenance script

### Modified Files
- `index.php` - Added name attribute to checkbox, logout messages
- `engine/login_engine.php` - Auto-login and remember me logic
- `logout.php` - Token cleanup on logout

## 🔐 Security Considerations

### Token Security
- Tokens are cryptographically secure random values
- Stored as plain text in database (tokens are not passwords)
- Unique constraint prevents token collisions
- Automatic expiration prevents indefinite access

### Cookie Security
- **HttpOnly**: Prevents JavaScript access
- **Secure**: Only sent over HTTPS (when available)
- **SameSite=Strict**: CSRF protection
- **Path=/**: Available site-wide

### Database Security
- Foreign key constraints prevent orphaned tokens
- Indexes on frequently queried columns
- Automatic cleanup of expired tokens
- User status verification on each use

## 🛠️ Usage Examples

### Basic Login with Remember Me
```php
// User checks "Remember me" and logs in
// Token is automatically created and cookie set
// On next visit, user is automatically logged in
```

### Logout Options
```php
// Regular logout (current device only)
header("Location: logout.php");

// Logout from all devices
header("Location: logout.php?all=1");
```

### Manual Token Management
```php
// Create token
$token = createRememberToken($conn, $user_id, 30); // 30 days

// Validate token
$user_data = validateRememberToken($conn, $token);

// Delete specific token
deleteRememberToken($conn, $token);

// Delete all user tokens
deleteAllUserTokens($conn, $user_id);
```

## 🧹 Maintenance

### Automatic Cleanup
The system automatically:
- Limits tokens per user (keeps 3 most recent)
- Validates token expiration on each use
- Clears invalid cookies

### Manual Cleanup
Run periodic cleanup:
```bash
php cleanup_remember_tokens.php
```

### Monitoring
Check audit logs for:
- `LOGIN_WITH_REMEMBER` - Login with remember me checked
- `AUTO_LOGIN` - Automatic login via token
- `LOGOUT` - Regular logout
- `LOGOUT_ALL_DEVICES` - Logout from all devices

## 🔍 Troubleshooting

### Common Issues

1. **Auto-login not working**
   - Check if remember_tokens table exists
   - Verify cookie is being set (check browser dev tools)
   - Check token expiration in database

2. **Tokens not being created**
   - Verify database connection
   - Check for SQL errors in logs
   - Ensure user has valid status

3. **Security warnings**
   - Ensure HTTPS is configured for production
   - Verify secure cookie settings
   - Check user agent/IP tracking

### Debug Information
Enable error logging and check:
- PHP error logs
- Database query logs  
- Audit activity logs
- Browser cookie storage

## 📈 Performance Considerations

- Tokens table has indexes on frequently queried columns
- Automatic cleanup prevents table bloat
- Token validation is optimized with single query
- Cookie operations are minimal overhead

## 🔄 Future Enhancements

Potential improvements:
- Configurable token expiration per user role
- Device fingerprinting for enhanced security
- Token refresh mechanism
- Admin panel for token management
- Email notifications for new device logins

## 📞 Support

For issues or questions about the Remember Me functionality:
1. Check this documentation
2. Review audit logs for authentication events
3. Run cleanup script to resolve token issues
4. Verify database schema matches requirements
