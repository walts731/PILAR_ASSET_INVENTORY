<?php
/**
 * Simple backup helper (pure PHP, no mysqldump required)
 */
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/backup_helper.php'; // for ensure_backups_table

/**
 * Generate a full SQL dump as a string.
 */
function generate_sql_dump(mysqli $conn): string {
    $nl = "\n";
    $out = '';

    $resDb = $conn->query('SELECT DATABASE() AS db');
    $dbRow = $resDb ? $resDb->fetch_assoc() : null;
    $dbName = $dbRow['db'] ?? 'database';

    $out .= "-- Simple SQL Backup for {$dbName}{$nl}";
    $out .= "-- Generated at: " . date('c') . $nl . $nl;
    $out .= "SET FOREIGN_KEY_CHECKS=0;{$nl}";
    $out .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';{$nl}{$nl}";

    // Tables only
    $tables = [];
    $rt = $conn->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
    while ($rt && ($r = $rt->fetch_array())) {
      $tables[] = $r[0];
    }

    foreach ($tables as $table) {
      $out .= "--\n-- Structure for table `{$table}`\n--{$nl}";
      $out .= "DROP TABLE IF EXISTS `{$table}`;{$nl}";
      $rc = $conn->query('SHOW CREATE TABLE `'.$conn->real_escape_string($table).'`');
      if ($rc) {
        $row = $rc->fetch_assoc();
        $create = array_values($row)[1] ?? '';
        $out .= $create . ";{$nl}{$nl}";
      }

      $countRes = $conn->query('SELECT COUNT(*) AS c FROM `'.$conn->real_escape_string($table).'`');
      $total = ($countRes && ($cr = $countRes->fetch_assoc())) ? (int)$cr['c'] : 0;
      if ($total === 0) { continue; }

      $out .= "--\n-- Data for table `{$table}` ({$total} rows)\n--{$nl}";

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
            $out .= 'INSERT INTO `'.$table.'` ('.implode(',', $cols).') VALUES ('.implode(',', $vals).');' . $nl;
          }
        }
        $offset += $batch;
      }
      $out .= $nl;
    }

    $out .= "SET FOREIGN_KEY_CHECKS=1;{$nl}";

    return $out;
}
