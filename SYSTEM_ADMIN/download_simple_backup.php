<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/audit_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  http_response_code(403);
  exit('Forbidden');
}

// Increase limits for large dumps
@set_time_limit(0);
@ini_set('memory_limit', '1024M');

// Get DB name
$resDb = $conn->query('SELECT DATABASE() AS db');
$dbRow = $resDb ? $resDb->fetch_assoc() : null;
$dbName = $dbRow['db'] ?? 'database';

$filename = $dbName . '_simple_backup_' . date('Ymd_His') . '.sql';

header('Content-Description: File Transfer');
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename=' . $filename);

$nl = "\n";

echo "-- Simple SQL Backup for {$dbName}{$nl}";
echo "-- Generated at: " . date('c') . $nl . $nl;
echo "SET FOREIGN_KEY_CHECKS=0;{$nl}";
echo "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';{$nl}{$nl}";

// Fetch tables
$tables = [];
$rt = $conn->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
while ($rt && ($r = $rt->fetch_array())) {
  // SHOW FULL TABLES returns columns: Tables_in_db, Table_type
  $tables[] = $r[0];
}

foreach ($tables as $table) {
  // Drop and create
  echo "--\n-- Structure for table `$table`\n--{$nl}";
  echo "DROP TABLE IF EXISTS `$table`;{$nl}";
  $rc = $conn->query('SHOW CREATE TABLE `'.$conn->real_escape_string($table).'`');
  if ($rc) {
    $row = $rc->fetch_assoc();
    $create = array_values($row)[1] ?? '';
    echo $create . ";{$nl}{$nl}";
  }

  // Data dump
  $countRes = $conn->query('SELECT COUNT(*) AS c FROM `'.$conn->real_escape_string($table).'`');
  $total = ($countRes && ($cr = $countRes->fetch_assoc())) ? (int)$cr['c'] : 0;
  if ($total === 0) { continue; }

  echo "--\n-- Data for table `$table` ($total rows)\n--{$nl}";

  $batch = 1000; $offset = 0;
  while ($offset < $total) {
    $q = 'SELECT * FROM `'.$conn->real_escape_string($table).'` LIMIT '.$batch.' OFFSET '.$offset;
    $rs = $conn->query($q);
    if ($rs && $rs->num_rows > 0) {
      while ($row = $rs->fetch_assoc()) {
        $cols = array_map(fn($c) => '`' . str_replace('`','``',$c) . '`', array_keys($row));
        $vals = array_map(function($v) use ($conn) {
          if (is_null($v)) return 'NULL';
          return "'" . $conn->real_escape_string($v) . "'";
        }, array_values($row));
        echo 'INSERT INTO `'.$table.'` ('.implode(',', $cols).') VALUES ('.implode(',', $vals).');' . $nl;
      }
    }
    $offset += $batch;
  }
  echo $nl;
}

echo "SET FOREIGN_KEY_CHECKS=1;{$nl}";

// Audit log
if (function_exists('isAuditLoggingAvailable') && isAuditLoggingAvailable()) {
  logUserActivity('BACKUP_DOWNLOAD', 'System', 'Downloaded simple SQL backup: '.$filename);
}

exit;
