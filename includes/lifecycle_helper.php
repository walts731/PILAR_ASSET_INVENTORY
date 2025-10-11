<?php
// Asset Lifecycle Logging Helper
// Creates table if missing and exposes logLifecycleEvent()

require_once __DIR__ . '/../connect.php';

function ensureLifecycleTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS asset_lifecycle_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        asset_id INT NOT NULL,
        event_type ENUM('ACQUIRED','ASSIGNED','TRANSFERRED','DISPOSAL_LISTED','DISPOSED','RED_TAGGED') NOT NULL,
        ref_table VARCHAR(64) NULL,
        ref_id INT NULL,
        from_employee_id INT NULL,
        to_employee_id INT NULL,
        from_office_id INT NULL,
        to_office_id INT NULL,
        notes TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_asset (asset_id),
        INDEX idx_type (event_type),
        INDEX idx_ref (ref_table, ref_id),
        CONSTRAINT fk_lifecycle_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    try { $conn->query($sql); } catch (Throwable $e) { error_log('ensureLifecycleTable: ' . $e->getMessage()); }

/**
 * Resolve office placeholders in a lifecycle notes string using provided office IDs.
 * Supported placeholders are the same as in logLifecycleEvent():
 *  {OFFICE}, {OFFICE_CODE}, {OFFICE_NAME}, {OFFICE_TO}, {OFFICE_FROM},
 *  {OFFICE_TO_NAME}, {OFFICE_FROM_NAME}, {OFFICE_TO_CODE}, {OFFICE_FROM_CODE}
 */
function resolveLifecycleNotes($notes, $from_office_id, $to_office_id) {
    global $conn;
    if (!$notes || strpos($notes, '{OFFICE') === false) { return $notes; }
    try {
        $getOffice = function($officeId) use ($conn) {
            if (empty($officeId)) { return ['name' => '', 'code' => '']; }
            $name = '';
            if ($stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ? LIMIT 1")) {
                $stmt->bind_param('i', $officeId);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && ($row = $res->fetch_assoc())) { $name = (string)$row['office_name']; }
                $stmt->close();
            }
            $code = '';
            if ($name !== '') {
                $upper = strtoupper(trim($name));
                $parts = preg_split('/\s+/', $upper);
                $acronym = '';
                foreach ($parts as $p) {
                    $first = preg_replace('/[^A-Z0-9]/', '', mb_substr($p, 0, 1));
                    $acronym .= $first;
                }
                if ($acronym === '') { $acronym = preg_replace('/[^A-Z0-9]/', '', $upper); }
                $code = $acronym ?: 'OFFICE';
            }
            return [ 'name' => $name, 'code' => $code ];
        };

        $to = $getOffice($to_office_id);
        $from = $getOffice($from_office_id);
        $toName = $to['name'];  $toCode = $to['code'];
        $fromName = $from['name']; $fromCode = $from['code'];

        $out = (string)$notes;
        // Legacy name placeholders
        $out = str_replace('{OFFICE_TO}', $toName, $out);
        $out = str_replace('{OFFICE_FROM}', $fromName, $out);
        // New specific name/code placeholders
        $out = str_replace('{OFFICE_TO_NAME}', $toName, $out);
        $out = str_replace('{OFFICE_FROM_NAME}', $fromName, $out);
        $out = str_replace('{OFFICE_TO_CODE}', $toCode, $out);
        $out = str_replace('{OFFICE_FROM_CODE}', $fromCode, $out);
        // Generic mappings
        $genericName = $toName !== '' ? $toName : $fromName;
        $genericCode = $toCode !== '' ? $toCode : $fromCode;
        $out = str_replace('{OFFICE}', $genericName, $out);
        $out = str_replace('{OFFICE_NAME}', $genericName, $out);
        $out = str_replace('{OFFICE_CODE}', $genericCode, $out);
        return $out;
    } catch (Throwable $e) {
        error_log('resolveLifecycleNotes error: ' . $e->getMessage());
        return $notes;
    }
}
}

function logLifecycleEvent($asset_id, $event_type, $ref_table = null, $ref_id = null, $from_employee_id = null, $to_employee_id = null, $from_office_id = null, $to_office_id = null, $notes = '') {
    global $conn;
    if (!$conn) { return false; }
    ensureLifecycleTable($conn);

    // Resolve office placeholders in notes
    // Supported:
    //  - Generic: {OFFICE} (code), {OFFICE_CODE}, {OFFICE_NAME}
    //  - Specific: {OFFICE_TO}, {OFFICE_FROM} (legacy: names),
    //              {OFFICE_TO_NAME}, {OFFICE_FROM_NAME}, {OFFICE_TO_CODE}, {OFFICE_FROM_CODE}
    try {
        $getOffice = function($officeId) use ($conn) {
            if (empty($officeId)) { return ''; }
            $name = '';
            if ($stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ? LIMIT 1")) {
                $stmt->bind_param('i', $officeId);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && ($row = $res->fetch_assoc())) {
                    $name = (string)$row['office_name'];
                }
                $stmt->close();
            }
            // Derive office code acronym from name (first letter of each word, alnum only)
            $code = '';
            if ($name !== '') {
                $upper = strtoupper(trim($name));
                $parts = preg_split('/\s+/', $upper);
                $acronym = '';
                foreach ($parts as $p) {
                    $first = preg_replace('/[^A-Z0-9]/', '', mb_substr($p, 0, 1));
                    $acronym .= $first;
                }
                if ($acronym === '') {
                    $acronym = preg_replace('/[^A-Z0-9]/', '', $upper);
                }
                $code = $acronym ?: 'OFFICE';
            }
            return [ 'name' => $name, 'code' => $code ];
        };

        if (strpos($notes, '{OFFICE') !== false) {
            $to = $getOffice($to_office_id);
            $from = $getOffice($from_office_id);
            $toName = is_array($to) ? $to['name'] : (string)$to;
            $toCode = is_array($to) ? $to['code'] : '';
            $fromName = is_array($from) ? $from['name'] : (string)$from;
            $fromCode = is_array($from) ? $from['code'] : '';
            // Specific placeholders first
            if ($toName !== '') {
                $notes = str_replace('{OFFICE_TO}', $toName, $notes);
            } else {
                $notes = str_replace('{OFFICE_TO}', '', $notes);
            }
            if ($fromName !== '') {
                $notes = str_replace('{OFFICE_FROM}', $fromName, $notes);
            } else {
                $notes = str_replace('{OFFICE_FROM}', '', $notes);
            }
            // New specific name/code placeholders
            $notes = str_replace('{OFFICE_TO_NAME}', $toName, $notes);
            $notes = str_replace('{OFFICE_FROM_NAME}', $fromName, $notes);
            $notes = str_replace('{OFFICE_TO_CODE}', $toCode, $notes);
            $notes = str_replace('{OFFICE_FROM_CODE}', $fromCode, $notes);
            // Generic {OFFICE}: prefer destination (to), fallback to source (from)
            $genericName = $toName !== '' ? $toName : $fromName;
            $genericCode = $toCode !== '' ? $toCode : $fromCode;
            // Map {OFFICE} to full office name as requested
            $notes = str_replace('{OFFICE}', $genericName, $notes);
            $notes = str_replace('{OFFICE_NAME}', $genericName, $notes);
            $notes = str_replace('{OFFICE_CODE}', $genericCode, $notes);
        }
    } catch (Throwable $e) {
        // Do not block logging on placeholder resolution failures
        error_log('logLifecycleEvent note placeholder error: ' . $e->getMessage());
    }

    $stmt = $conn->prepare("INSERT INTO asset_lifecycle_events (asset_id, event_type, ref_table, ref_id, from_employee_id, to_employee_id, from_office_id, to_office_id, notes) VALUES (?,?,?,?,?,?,?,?,?)");
    if (!$stmt) { error_log('logLifecycleEvent prepare failed: ' . $conn->error); return false; }
    $types = 'issiiiiis';
    $stmt->bind_param($types, $asset_id, $event_type, $ref_table, $ref_id, $from_employee_id, $to_employee_id, $from_office_id, $to_office_id, $notes);
    $ok = $stmt->execute();
    if (!$ok) { error_log('logLifecycleEvent exec failed: ' . $stmt->error); }
    $stmt->close();
    return $ok;
}
