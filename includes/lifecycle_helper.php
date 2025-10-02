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
}

function logLifecycleEvent($asset_id, $event_type, $ref_table = null, $ref_id = null, $from_employee_id = null, $to_employee_id = null, $from_office_id = null, $to_office_id = null, $notes = '') {
    global $conn;
    if (!$conn) { return false; }
    ensureLifecycleTable($conn);
    $stmt = $conn->prepare("INSERT INTO asset_lifecycle_events (asset_id, event_type, ref_table, ref_id, from_employee_id, to_employee_id, from_office_id, to_office_id, notes) VALUES (?,?,?,?,?,?,?,?,?)");
    if (!$stmt) { error_log('logLifecycleEvent prepare failed: ' . $conn->error); return false; }
    $types = 'issiiiiis';
    $stmt->bind_param($types, $asset_id, $event_type, $ref_table, $ref_id, $from_employee_id, $to_employee_id, $from_office_id, $to_office_id, $notes);
    $ok = $stmt->execute();
    if (!$ok) { error_log('logLifecycleEvent exec failed: ' . $stmt->error); }
    $stmt->close();
    return $ok;
}
