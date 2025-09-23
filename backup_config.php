<?php
// Backup configuration
// Configure where to store backups and optional cloud upload strategy

return [
    // Directory where local backups are stored (relative to project root)
    'backup_dir' => __DIR__ . DIRECTORY_SEPARATOR . 'generated_backups',

    // Optional: Absolute path to mysqldump binary. If null, will try to find in PATH
    // Example (XAMPP Windows): 'C:\\xampp\\mysql\\bin\\mysqldump.exe'
    'mysqldump_path' => 'CC:\xampp\mysql\bin\mariadb-dump.exe',

    // Cloud upload strategy: 'none' | 's3_presigned' | 'custom_script'
    'upload_strategy' => 's3_presigned',

    // If using 's3_presigned', provide a function that returns a presigned upload URL
    // for the given filename, or set a static URL. Presigned URL must accept PUT.
    // Example: function ($filename) { return 'https://...'; }
    's3_presigned_url_provider' => null,

    // If using 'custom_script', specify a shell command template to run after dump.
    // The placeholder {file} will be replaced with the dump file path.
    // Example: 'C:\\path\\to\\uploader.bat {file}'
    'custom_upload_command' => null,

    // Optional retention policy: delete backups older than N days (0 = disabled)
    'retention_days' => 0,

    // Optional alert notifications (you can wire this to email/telegram in monthly_backup.php)
    'enable_alerts' => false,
    'alert_recipients' => [/* 'admin@example.com' */],
];
