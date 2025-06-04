<?php
// Suppress errors and warnings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add CORS headers
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to handle errors and return them as JSON
function returnError($code, $message) {
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

// Check if required files exist
if (!file_exists(__DIR__ . '/../../config/connection.php')) {
    returnError(500, 'Database configuration file not found');
}

if (!file_exists(__DIR__ . '/../auth/auth_middleware.php')) {
    returnError(500, 'Auth middleware file not found');
}

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../auth/auth_middleware.php';

// Get user from token
$user = validateToken();
if (!$user) {
    returnError(401, 'Unauthorized');
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Define upload directory
$uploadDir = __DIR__ . '/../../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Function to handle file uploads
function handleFileUploads($files) {
    global $uploadDir;
    $uploadedFiles = [];
    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv'
    ];
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    foreach ($files as $key => $file) {
        if (strpos($key, 'attachment_') === 0) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                // Validate file type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $allowedTypes)) {
                    continue; // Skip invalid file types
                }

                // Validate file size
                if ($file['size'] > $maxFileSize) {
                    continue; // Skip files that are too large
                }

                $tmpName = $file['tmp_name'];
                $fileName = basename($file['name']);
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Generate unique filename
                $newFileName = uniqid() . '_' . $fileName;
                $targetPath = $uploadDir . $newFileName;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $uploadedFiles[] = $newFileName;
                }
            }
        }
    }
    
    return implode(',', $uploadedFiles);
}

try {
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname",
        $username,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    switch ($method) {
        case 'GET':
            if ($action === 'dashboard') {
                // Get dashboard statistics
                $stats = array();
                
                // Get total logs count
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM logs WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $stats['total_logs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Get logs by direction
                $stmt = $pdo->prepare("SELECT direction, COUNT(*) as count FROM logs WHERE user_id = ? GROUP BY direction");
                $stmt->execute([$user['id']]);
                $stats['by_direction'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get logs by type
                $stmt = $pdo->prepare("SELECT type, COUNT(*) as count FROM logs WHERE user_id = ? GROUP BY type");
                $stmt->execute([$user['id']]);
                $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get recent logs
                $stmt = $pdo->prepare("
                    SELECT * FROM logs 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ");
                $stmt->execute([$user['id']]);
                $stats['recent_logs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'data' => $stats]);
            } else if ($action === 'getLogs') {
                // Get all logs from the database
                $stmt = $pdo->prepare("
                    SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp,
                           l.confidentiality_level, l.authorize_users
                    FROM logs l 
                    JOIN users u ON l.user_id = u.id 
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute();
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'data' => $logs]);
            } else if ($action === 'getLogMessage') {
                if (!isset($_GET['log_id'])) {
                    returnError(400, 'Missing log_id parameter');
                }
                
                $logId = $_GET['log_id'];

                // First, get the client_name for the given log_id
                $stmt = $pdo->prepare("SELECT client_name FROM logs WHERE log_id = ? LIMIT 1");
                $stmt->execute([$logId]);
                $initialLog = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$initialLog || !isset($initialLog['client_name'])) {
                    returnError(404, 'Initial log not found or missing client name');
                }

                $clientName = $initialLog['client_name'];

                // Now, get all logs for this client_name
                $stmt = $pdo->prepare("
                    SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp 
                    FROM logs l 
                    JOIN users u ON l.user_id = u.id 
                    WHERE l.client_name = ?
                    ORDER BY l.created_at ASC
                ");
                $stmt->execute([$clientName]);
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($logs)) {
                    // This case should ideally not happen if the initial log was found, but good to check
                    returnError(404, 'No logs found for this client name');
                }
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'data' => $logs]);
            } else if ($action === 'getLogReplies') {
                if (!isset($_GET['log_id'])) {
                    returnError(400, 'Missing log_id parameter');
                }
                
                // Fetch replies for the log
                // Alias columns to match the structure of logs table for easier merging
                // Add a distinct direction and type to identify replies
                $reply_stmt = $pdo->prepare("
                    SELECT
                        lr.logs_rep AS id, -- Corrected to use logs_rep as id for replies
                        lr.log_id,
                        lr.logs_description AS content, -- Alias logs_description as content
                        lr.created_at,
                        lr.logged_by AS log_by, -- Alias logged_by as log_by for the replier's name
                        'Outgoing' AS direction, -- Assuming replies are always outgoing from the user's perspective
                        'Reply' AS type,
                        lr.log_subject AS subject, -- Include subject from the reply table
                        lr.client_name AS client_name, -- Include client_name from the reply table
                        NULL AS attachment, -- Assuming replies table doesn't have attachments based on your response
                        lr.client_email AS sender, -- Alias client_email as sender to match logs table structure for merging
                        NULL AS confidential,
                        NULL AS authorize_users,
                        NULL AS status
                    FROM logs_replies lr
                    WHERE lr.log_id = ?
                    ORDER BY lr.created_at ASC
                ");
                $reply_stmt->execute([$_GET['log_id']]);
                $replies = $reply_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'data' => $replies]);
            } else if ($action === 'downloadFile') {
                // Handle file download
                if (!isset($_GET['filename'])) {
                    returnError(400, 'Missing filename parameter');
                }

                $filename = $_GET['filename'];
                $filepath = $uploadDir . $filename;

                // Security check: ensure the file exists and is within the upload directory
                if (!file_exists($filepath) || !is_file($filepath)) {
                    returnError(404, 'File not found');
                }

                // Get the original filename (remove the unique prefix)
                $originalName = implode('_', array_slice(explode('_', $filename), 1));
                
                // Set headers for file download
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $originalName . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                
                // Output file
                readfile($filepath);
                exit;
            } else {
                returnError(400, 'Invalid action specified');
            }
            break;

        case 'POST':
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            $data = [];

            if (strpos($contentType, 'application/json') !== false) {
                // Handle JSON input (e.g., for non-file uploads like replies if not using FormData)
                $json_data = file_get_contents('php://input');
                $data = json_decode($json_data, true);
                
                if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                    returnError(400, 'Invalid JSON input: ' . json_last_error_msg());
                }

            } else if (strpos($contentType, 'multipart/form-data') !== false) {
                // Handle FormData input with file uploads
                // Data comes from $_POST and files from $_FILES
                $data = $_POST;
                
                // Process file uploads
                if (!empty($_FILES)) {
                    $uploadedFiles = handleFileUploads($_FILES);
                    // Store the comma-separated string of filenames in the data array
                    $data['attachment'] = $uploadedFiles;
                } else {
                     // If no files uploaded but it's multipart/form-data, ensure attachment is empty
                    $data['attachment'] = '';
                }
            } else {
                 returnError(415, 'Unsupported Media Type');
            }
            
            // Now process the $data array for both content types

            if ($action === 'addReply') {
                // Handle adding a reply to logs_replies
                // Ensure required fields are present in $data
                $required_reply_fields = ['log_id', 'content', 'subject', 'client_name', 'client_email'];
                 foreach ($required_reply_fields as $field) {
                    if (!isset($data[$field])) {
                        returnError(400, "Missing required field for reply: $field");
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO logs_replies (
                        log_id, logs_description, created_at,
                        logged_by, log_subject, client_name, client_email
                    ) VALUES (
                        ?, ?, NOW(), ?, ?, ?, ?
                    )
                ");

                $stmt->execute([
                    $data['log_id'],
                    $data['content'],
                    $user['firstname'] . ' ' . $user['lastname'], // Use logged-in user's name
                    $data['subject'],
                    $data['client_name'],
                    $data['client_email'] // Include client_email
                ]);

                $replyId = $pdo->lastInsertId();

                 // Fetch the created reply
                 $stmt = $pdo->prepare("
                     SELECT
                         lr.logs_rep AS id,
                         lr.log_id,
                         lr.logs_description AS content,
                         lr.created_at,
                         lr.logged_by AS log_by,
                         'Outgoing' AS direction,
                         'Reply' AS type,
                         lr.log_subject AS subject,
                         lr.client_name AS client_name,
                         NULL AS attachment, // Replies don't have attachments in this schema
                         lr.client_email AS sender,
                         NULL AS confidential,
                         NULL AS authorize_users,
                         NULL AS status
                     FROM logs_replies lr
                     WHERE lr.logs_rep = ?
                 ");
                 $stmt->execute([$replyId]);
                 $reply = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'data' => $reply]);

            } else {
                // Handle inserting a new log entry into logs table

                // Validate required fields for main logs
                $required_fields = ['direction', 'type', 'subject', 'timestamp', 'client_name'];
                foreach ($required_fields as $field) {
                    if (!isset($data[$field])) {
                        returnError(400, "Missing required field: $field");
                    }
                }

                // Check if there's an existing log for this client
                $existingLogId = null;
                if (isset($data['client_name'])) {
                    $stmt = $pdo->prepare("SELECT log_id FROM logs WHERE client_name = ? ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute([$data['client_name']]);
                    $existingLog = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($existingLog) {
                        $existingLogId = $existingLog['log_id'];
                    }
                }

                // Insert new log
                $stmt = $pdo->prepare("
                    INSERT INTO logs (
                        user_id, direction, type, subject, content,
                        sender, recipient, log_by, confidentiality_level, created_at,
                        client_name, attachment, authorize_users, status, log_id
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");

                // Format the timestamp for MySQL (YYYY-MM-DD HH:mm:ss)
                $formattedTimestamp = date('Y-m-d H:i:s', strtotime($data['timestamp']));

                $stmt->execute([
                    $user['id'],
                    $data['direction'] ?? null,
                    $data['type'] ?? null,
                    $data['subject'] ?? null,
                    $data['content'] ?? null,
                    $data['sender'] ?? null,
                    $data['recipient'] ?? null,
                    $user['firstname'] . ' ' . $user['lastname'],
                    isset($data['confidential']) && $data['confidential'] === 'true' ? 'private' : 'public',
                    $formattedTimestamp,
                    $data['client_name'] ?? null,
                    $data['attachment'] ?? '',
                    $data['authorize_users'] ?? null, // This is not sent from frontend for new logs, so should be null
                    $data['status'] ?? 'active',
                    $existingLogId ?? null
                ]);

                $newLogId = $pdo->lastInsertId();

                // If no existing log_id was found, update the log_id to be the same as the auto-generated id
                if (!$existingLogId) {
                    $updateStmt = $pdo->prepare("UPDATE logs SET log_id = ? WHERE id = ?");
                    $updateStmt->execute([$newLogId, $newLogId]);
                }

                // Get the created log, join users for full name, alias created_at as timestamp
                $stmt = $pdo->prepare("
                    SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp
                    FROM logs l
                    JOIN users u ON l.user_id = u.id
                    WHERE l.id = ?
                ");
                $stmt->execute([$newLogId]);
                $log = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'data' => $log]);
            }
            break;

        case 'DELETE':
            // Delete a log entry
            if (!isset($_GET['id'])) {
                returnError(400, 'Missing log_id');
            }

            // Start a transaction
            $pdo->beginTransaction();

            try {
                // First get the attachment filenames
                $stmt = $pdo->prepare("SELECT attachment FROM logs WHERE log_id = ?");
                $stmt->execute([$_GET['id']]);
                $log = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($log && $log['attachment']) {
                    $files = explode(',', $log['attachment']);
                    foreach ($files as $file) {
                        $filePath = $uploadDir . $file;
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }

                // Then delete all associated replies
                $stmt = $pdo->prepare("DELETE FROM logs_replies WHERE log_id = ?");
                $stmt->execute([$_GET['id']]);

                // Then delete the main log entry using log_id
                $stmt = $pdo->prepare("DELETE FROM logs WHERE log_id = ?");
                $stmt->execute([$_GET['id']]);

                if ($stmt->rowCount() === 0) {
                    $pdo->rollBack();
                    returnError(404, 'Log not found or not authorized');
                }

                // Commit the transaction
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                // Rollback the transaction on error
                $pdo->rollBack();
                returnError(500, 'Error deleting log: ' . $e->getMessage());
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            if ($action === 'setConfidential') {
                if (!isset($data['log_id'])) {
                    returnError(400, 'Missing log_id parameter');
                }

                $logIdToUpdate = $data['log_id'];
                $currentUserEmail = $user['email']; // Get email from the authenticated user

                // Get existing authorized users from the database
                $stmt_select_users = $pdo->prepare("SELECT authorize_users FROM logs WHERE log_id = ? LIMIT 1");
                $stmt_select_users->execute([$logIdToUpdate]);
                $existingUsersRow = $stmt_select_users->fetch(PDO::FETCH_ASSOC);
                $existingAuthorizeUsers = $existingUsersRow ? $existingUsersRow['authorize_users'] : '';
                
                // Combine existing users with the new user, ensuring no duplicates
                $userList = array_map('trim', explode(',', $existingAuthorizeUsers));
                if (!in_array($currentUserEmail, $userList)) {
                    $userList[] = $currentUserEmail;
                }
                
                // If authorize_users is also provided in the request body, add those as well (avoiding duplicates)
                if (isset($data['authorize_users'])) {
                    $requestedUsers = array_map('trim', explode(',', $data['authorize_users']));
                    foreach($requestedUsers as $reqUser) {
                        if ($reqUser !== '' && !in_array($reqUser, $userList)) {
                            $userList[] = $reqUser;
                        }
                    }
                }
                
                $updatedAuthorizeUsers = implode(',', $userList);
                
                $stmt = $pdo->prepare("UPDATE logs SET confidentiality_level = 'private', authorize_users = ? WHERE log_id = ?");
                $stmt->execute([$updatedAuthorizeUsers, $logIdToUpdate]);

                if ($stmt->rowCount() === 0) {
                    // If no rows were affected, it means the log_id wasn't found or authorized
                    returnError(404, 'Log not found or not authorized to update');
                }

                echo json_encode(['success' => true, 'message' => 'Log updated successfully']);

            } else {
                 returnError(400, 'Invalid action specified for PUT');
            }
            break;

        default:
            returnError(405, 'Method not allowed');
    }
} catch (PDOException $e) {
    returnError(500, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    returnError(400, $e->getMessage());
}
