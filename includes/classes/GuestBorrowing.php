<?php
class GuestBorrowing {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Generate a unique request number
     */
    private function generateRequestNumber() {
        $prefix = 'GBR';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        return $prefix . '-' . $date . '-' . $random;
    }
    
    /**
     * Create a new guest borrowing request
     */
    public function createRequest($data) {
        $this->conn->begin_transaction();
        
        try {
            // Create the request
            $requestNumber = $this->generateRequestNumber();
            $query = "INSERT INTO guest_borrowing_requests (
                request_number, guest_name, guest_email, guest_contact, 
                guest_organization, purpose, request_date, needed_by_date, 
                expected_return_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, 'pending')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'ssssssss',
                $requestNumber,
                $data['guest_name'],
                $data['guest_email'],
                $data['guest_contact'],
                $data['guest_organization'],
                $data['purpose'],
                $data['needed_by_date'],
                $data['expected_return_date']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create borrowing request: " . $stmt->error);
            }
            
            $requestId = $this->conn->insert_id;
            
            // Add items to the request
            foreach ($data['items'] as $item) {
                $this->addRequestItem($requestId, $item);
            }
            
            // Log the creation
            $this->logHistory($requestId, 'request_created', 'Borrowing request created', null);
            
            $this->conn->commit();
            return ['success' => true, 'request_id' => $requestId, 'request_number' => $requestNumber];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Add an item to a borrowing request
     */
    private function addRequestItem($requestId, $item) {
        $query = "INSERT INTO guest_borrowing_items (
            request_id, asset_id, quantity, condition_before
        ) VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $condition = 'New'; // Default condition
        $stmt->bind_param('iiis', $requestId, $item['asset_id'], $item['quantity'], $condition);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add item to request: " . $stmt->error);
        }
        
        // Mark asset as reserved
        $this->reserveAsset($item['asset_id'], $requestId);
    }
    
    /**
     * Reserve an asset for a guest borrowing request
     */
    private function reserveAsset($assetId, $requestId) {
        $query = "UPDATE assets 
                 SET is_borrowed = 1, 
                     guest_borrowing_request_id = ? 
                 WHERE id = ? AND is_borrowed = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $requestId, $assetId);
        
        if (!$stmt->execute() || $stmt->affected_rows === 0) {
            throw new Exception("Asset not available for borrowing");
        }
    }
    
    /**
     * Log history for a borrowing request
     */
    private function logHistory($requestId, $action, $details, $performedBy = null) {
        $query = "INSERT INTO guest_borrowing_history (
            request_id, action, details, performed_by
        ) VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('issi', $requestId, $action, $details, $performedBy);
        $stmt->execute();
    }
    
    /**
     * Get a guest's borrowing history
     */
    public function getGuestBorrowingHistory($email) {
        $query = "SELECT r.*, 
                         COUNT(i.id) as item_count,
                         (SELECT status FROM guest_borrowing_history 
                          WHERE request_id = r.id 
                          ORDER BY performed_at DESC LIMIT 1) as last_status
                  FROM guest_borrowing_requests r
                  LEFT JOIN guest_borrowing_items i ON r.id = i.request_id
                  WHERE r.guest_email = ?
                  GROUP BY r.id
                  ORDER BY r.request_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get details of a specific borrowing request
     */
    public function getRequestDetails($requestId, $guestEmail = null) {
        $query = "SELECT r.*, 
                         GROUP_CONCAT(CONCAT(a.asset_name, ' (', i.quantity, ')') SEPARATOR ', ') as items_list,
                         COUNT(i.id) as item_count
                  FROM guest_borrowing_requests r
                  LEFT JOIN guest_borrowing_items i ON r.id = i.request_id
                  LEFT JOIN assets a ON i.asset_id = a.id
                  WHERE r.id = ?";
        
        if ($guestEmail) {
            $query .= " AND r.guest_email = ?";
        }
        
        $query .= " GROUP BY r.id";
        
        $stmt = $this->conn->prepare($query);
        
        if ($guestEmail) {
            $stmt->bind_param('is', $requestId, $guestEmail);
        } else {
            $stmt->bind_param('i', $requestId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $request = $result->fetch_assoc();
        
        // Get items
        $itemsQuery = "SELECT i.*, a.asset_name, a.inventory_tag, a.model, a.brand
                      FROM guest_borrowing_items i
                      JOIN assets a ON i.asset_id = a.id
                      WHERE i.request_id = ?";
        
        $stmt = $this->conn->prepare($itemsQuery);
        $stmt->bind_param('i', $requestId);
        $stmt->execute();
        
        $itemsResult = $stmt->get_result();
        $request['items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);
        
        // Get history
        $historyQuery = "SELECT h.*, u.username as performed_by_name
                        FROM guest_borrowing_history h
                        LEFT JOIN users u ON h.performed_by = u.id
                        WHERE h.request_id = ?
                        ORDER BY h.performed_at ASC";
        
        $stmt = $this->conn->prepare($historyQuery);
        $stmt->bind_param('i', $requestId);
        $stmt->execute();
        
        $historyResult = $stmt->get_result();
        $request['history'] = $historyResult->fetch_all(MYSQLI_ASSOC);
        
        return $request;
    }
    
    /**
     * Update request status
     */
    public function updateRequestStatus($requestId, $status, $userId = null, $notes = '') {
        $this->conn->begin_transaction();
        
        try {
            // Update request status
            $query = "UPDATE guest_borrowing_requests 
                     SET status = ?, 
                         updated_at = NOW()";
            
            $params = [$status];
            $types = 's';
            
            // If approved, set approved_by and approved_at
            if ($status === 'approved') {
                $query .= ", approved_by = ?, approved_at = NOW()";
                $params[] = $userId;
                $types .= 'i';
            }
            
            $query .= " WHERE id = ?";
            $params[] = $requestId;
            $types .= 'i';
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update request status: " . $stmt->error);
            }
            
            // Log the status change
            $action = 'status_' . $status;
            $this->logHistory($requestId, $action, $notes, $userId);
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error updating request status: " . $e->getMessage());
            return false;
        }
    }
}
