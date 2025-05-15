<?php
require_once '../config.php';

// Skip authentication for OPTIONS requests (handled in config.php)
if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    $auth = authenticate();
    $user_id = $auth->user_id;
    $user_role = $auth->role;
}

// Get all logs with filters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT cl.*, u.username as user_name FROM communication_logs cl JOIN users u ON cl.user_id = u.id WHERE 1=1";
        $params = [];
        
        // Apply filters
        if (isset($_GET['direction']) && in_array($_GET['direction'], ['incoming', 'outgoing'])) {
            $query .= " AND direction = ?";
            $params[] = $_GET['direction'];
        }
        
        if (isset($_GET['type']) && in_array($_GET['type'], ['memo', 'fax', 'email', 'letter', 'phone', 'other'])) {
            $query .= " AND type = ?";
            $params[] = $_GET['type'];
        }
        
        if (isset($_GET['fromDate'])) {
            $query .= " AND timestamp >= ?";
            $params[] = $_GET['fromDate'] . ' 00:00:00';
        }
        
        if (isset($_GET['toDate'])) {
            $query .= " AND timestamp <= ?";
            $params[] = $_GET['toDate'] . ' 23:59:59';
        }
        
        if (isset($_GET['search'])) {
            $query .= " AND (subject LIKE ? OR content LIKE ?)";
            $searchTerm = '%' . $_GET['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Apply confidentiality filter based on user role
        if ($user_role !== 'admin') {
            $query .= " AND confidentiality_level IN ('public', 'confidential')";
        } elseif (isset($_GET['confidentiality']) && in_array($_GET['confidentiality'], ['public', 'confidential', 'secret'])) {
            $query .= " AND confidentiality_level = ?";
            $params[] = $_GET['confidentiality'];
        }
        
        // Order by timestamp descending
        $query .= " ORDER BY timestamp DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
        
        // Record access in a transaction to ensure all records are created
        $db->beginTransaction();
        try {
            foreach ($logs as $log) {
                $stmt = $db->prepare("INSERT INTO log_access (user_id, log_id, action) VALUES (?, ?, 'view')");
                $stmt->execute([$user_id, $log['id']]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error recording log access: " . $e->getMessage());
            // Continue execution - log access recording failure should not prevent viewing logs
        }
        
        echo json_encode($logs);
    } catch (PDOException $e) {
        error_log("Database error in logs.php GET: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        error_log("General error in logs.php GET: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
    }
    exit;
}

// Create new log
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    validateInput($data, [
        'direction' => ['type' => 'string'],
        'type' => ['type' => 'string'],
        'subject' => ['type' => 'string', 'min' => 3, 'max' => 255],
        'timestamp' => ['type' => 'string']
    ]);
    
    try {
        $stmt = $db->prepare("INSERT INTO communication_logs 
            (direction, type, subject, content, sender, recipient, timestamp, user_id, confidentiality_level)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $data['direction'],
            $data['type'],
            $data['subject'],
            $data['content'] ?? '',
            $data['sender'] ?? '',
            $data['recipient'] ?? '',
            $data['timestamp'],
            $user_id,
            $data['confidentiality_level'] ?? 'public'
        ]);
        
        $log_id = $db->lastInsertId();
        
        // Record access
        $stmt = $db->prepare("INSERT INTO log_access (user_id, log_id, action) VALUES (?, ?, 'create')");
        $stmt->execute([$user_id, $log_id]);
        
        http_response_code(201);
        echo json_encode(['id' => $log_id, 'message' => 'Log created successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Update log
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    $log_id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    // First check if log exists and user has permission
    $stmt = $db->prepare("SELECT * FROM communication_logs WHERE id = ?");
    $stmt->execute([$log_id]);
    $log = $stmt->fetch();
    
    if (!$log) {
        http_response_code(404);
        echo json_encode(['error' => 'Log not found']);
        exit;
    }
    
    // Only admin or the creator can edit
    if ($user_role !== 'admin' && $log['user_id'] !== $user_id) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized to edit this log']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("UPDATE communication_logs SET 
            direction = ?,
            type = ?,
            subject = ?,
            content = ?,
            sender = ?,
            recipient = ?,
            timestamp = ?,
            confidentiality_level = ?
            WHERE id = ?");
        
        $stmt->execute([
            $data['direction'] ?? $log['direction'],
            $data['type'] ?? $log['type'],
            $data['subject'] ?? $log['subject'],
            $data['content'] ?? $log['content'],
            $data['sender'] ?? $log['sender'],
            $data['recipient'] ?? $log['recipient'],
            $data['timestamp'] ?? $log['timestamp'],
            $data['confidentiality_level'] ?? $log['confidentiality_level'],
            $log_id
        ]);
        
        // Record access
        $stmt = $db->prepare("INSERT INTO log_access (user_id, log_id, action) VALUES (?, ?, 'edit')");
        $stmt->execute([$user_id, $log_id]);
        
        echo json_encode(['message' => 'Log updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Delete log
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $log_id = $_GET['id'];
    
    // First check if log exists and user has permission
    $stmt = $db->prepare("SELECT * FROM communication_logs WHERE id = ?");
    $stmt->execute([$log_id]);
    $log = $stmt->fetch();
    
    if (!$log) {
        http_response_code(404);
        echo json_encode(['error' => 'Log not found']);
        exit;
    }
    
    // Only admin can delete
    if ($user_role !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized to delete logs']);
        exit;
    }
    
    try {
        // First delete access records
        $stmt = $db->prepare("DELETE FROM log_access WHERE log_id = ?");
        $stmt->execute([$log_id]);
        
        // Then delete the log
        $stmt = $db->prepare("DELETE FROM communication_logs WHERE id = ?");
        $stmt->execute([$log_id]);
        
        echo json_encode(['message' => 'Log deleted successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>