<?php
// Suppress errors and warnings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add CORS headers
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
            }  else if (isset($_GET['id'])) {
                // Get a single log by ID
                $stmt = $pdo->prepare("SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp FROM logs l JOIN users u ON l.user_id = u.id WHERE l.id = ?");
                $stmt->execute([$_GET['id']]);
                $log = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($log) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'data' => $log]);
                } else {
                    returnError(404, 'Log not found');
                }
            } else {
                // Get all logs for the user, join users for full name, alias created_at as timestamp
                $stmt = $pdo->prepare("
                    SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp
                    FROM logs l
                    JOIN users u ON l.user_id = u.id
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute();
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'data' => $logs]);
            }
            break;

        case 'POST':
            if ($action === 'report') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['logId'])) {
                    returnError(400, 'Missing logId');
                }
                $format = isset($data['format']) ? $data['format'] : 'pdf';
                $range = isset($data['range']) ? $data['range'] : 'monthly';
                $stmt = $pdo->prepare("SELECT * FROM logs WHERE id = ? AND user_id = ?");
                $stmt->execute([$data['logId'], $user['id']]);
                $log = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$log) {
                    returnError(404, 'Log not found');
                }

                if ($format === 'excel') {
                    if (ob_get_length()) ob_end_clean();
                    $headers = [
                        'Communication Type',
                        'Direction',
                        'From / To',
                        'Subject / Summary',
                        'Details / Notes',
                        'Date / Time',
                        'Attachments'
                    ];
                    $values = [
                        $log['type'],
                        $log['direction'],
                        $log['sender'] ?: $log['recipient'] ?: '--',
                        $log['subject'],
                        str_replace(["\r\n", "\n", "\r"], ' ', $log['content']),
                        $log['created_at'],
                        'No attachments'
                    ];
                    $csv = '"' . implode('","', $headers) . "\n";
                    $csv .= '"' . implode('","', $values) . "\n";
                    echo $csv;
                    exit();
                } else {
                    // Generate PDF using TCPDF
                    if (ob_get_length()) ob_end_clean();
                    require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

                    // Create new PDF document
                    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                    // Set document information
                    $pdf->SetCreator('LogSystem');
                    $pdf->SetAuthor($user['firstname'] . ' ' . $user['lastname']);
                    $pdf->SetTitle('Log Entry Report');

                    // Remove default header/footer
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);

                    // Add a page
                    $pdf->AddPage();

                    // Set font
                    $pdf->SetFont('helvetica', '', 12);

                    // Add content
                    $html = '<h1 style="color:#0d47a1;text-align:center;">Log Entry Report</h1>';
                    $html .= '<table cellpadding="6" style="font-size:12px;">';
                    $html .= '<tr><td style="font-weight:bold;width:40%;">Communication Type</td><td>' . htmlspecialchars($log['type']) . '</td></tr>';
                    $html .= '<tr><td style="font-weight:bold;">Direction</td><td>' . htmlspecialchars($log['direction']) . '</td></tr>';
                    $html .= '<tr><td style="font-weight:bold;">From / To</td><td>' . htmlspecialchars($log['sender'] ?: $log['recipient'] ?: '--') . '</td></tr>';
                    $html .= '<tr><td style="font-weight:bold;">Subject / Summary</td><td>' . htmlspecialchars($log['subject']) . '</td></tr>';
                    $html .= '<tr><td style="font-weight:bold;">Details / Notes</td><td>' . nl2br(htmlspecialchars($log['content'])) . '</td></tr>';
                    $html .= '<tr><td style="font-weight:bold;">Date / Time</td><td>' . htmlspecialchars($log['created_at']) . '</td></tr>';
                    $html .= '<tr><td style="font-weight:bold;">Attachments</td><td>No attachments</td></tr>';
                    $html .= '</table>';

                    // Output the HTML content
                    $pdf->writeHTML($html, true, false, true, false, '');

                    // Close and output PDF document
                    $pdf->Output('log-report-' . $log['id'] . '.pdf', 'I');
                    exit();
                }
            } else if ($action === 'report-summary') {
                $data = json_decode(file_get_contents('php://input'), true);
                $format = isset($data['format']) ? $data['format'] : 'pdf';
                $range = isset($data['range']) ? $data['range'] : 'monthly';
                // Fetch all logs for the user (filtering by range can be added here if needed)
                $stmt = $pdo->prepare("
                    SELECT l.id, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.direction, l.type, l.subject, l.content, l.sender, l.recipient, l.confidentiality_level, l.created_at
                    FROM logs l
                    JOIN users u ON l.user_id = u.id
                    WHERE l.user_id = ?
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute([$user['id']]);
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!$logs) {
                    returnError(404, 'No logs found');
                } 
                if ($format === 'excel') {
                    if (ob_get_length()) ob_end_clean();
                    function csvEscape($value) {
                        $value = str_replace(["\r", "\n"], ' ', $value); // Remove newlines
                        $value = str_replace('"', '""', $value);           // Escape double quotes
                        return '"' . $value . '"';                          // Wrap in quotes
                    }
                    $headers = array_keys($logs[0]);
                    $csv = implode(',', array_map('csvEscape', $headers)) . "\r\n";
                    foreach ($logs as $row) {
                        $csv .= implode(',', array_map('csvEscape', $row)) . "\r\n";
                    }
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="log-summary-report.csv"');
                    echo $csv;
                    exit();
                } else {
                    if (ob_get_length()) ob_end_clean();
                    require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
                    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    $pdf->SetCreator('LogSystem');
                    $pdf->SetAuthor($user['firstname'] . ' ' . $user['lastname']);
                    $pdf->SetTitle('Log Summary Report');
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                    $pdf->AddPage();
                    $pdf->SetFont('helvetica', '', 10);
                    $html = '<h2 style="color:#0d47a1;text-align:center;">Log Summary Report</h2>';
                    $html .= '<table border="1" cellpadding="4" cellspacing="0" style="font-size:10px;width:100%;">';
                    // Table header
                    $html .= '<tr style="background:#e3f0ff;">';
                    foreach (array_keys($logs[0]) as $header) {
                        $html .= '<th>' . htmlspecialchars($header) . '</th>';
                    }
                    $html .= '</tr>';
                    // Table rows
                    foreach ($logs as $row) {
                        $html .= '<tr>';
                        foreach ($row as $cell) {
                            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</table>';
                    $pdf->writeHTML($html, true, false, true, false, '');
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="log-summary-report.pdf"');
                    $pdf->Output('log-summary-report.pdf', 'I');
                    exit();
                }
            } else if ($action === 'replies') {
                // Get log_id from the request
                if (!isset($_GET['log_id'])) {
                    returnError(400, 'Missing log_id');
                }
                $logId = $_GET['log_id'];

                // Get replies for the log_id
                $stmt = $pdo->prepare("SELECT * FROM logs_replies WHERE log_id = ?");
                $stmt->execute([$logId]);
                $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($replies) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'data' => $replies]);
                } else {
                    returnError(404, 'No replies found for this log_id');
                }
            } else if ($action === 'add_reply') {
                try {
                    $data = json_decode(file_get_contents('php://input'), true);

                    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                        returnError(400, 'Invalid JSON input: ' . json_last_error_msg());
                    }

                    // Validate required fields for a reply
                    $required_reply_fields = ['log_id', 'log_subject', 'logs_description', 'client_name'];
                    foreach ($required_reply_fields as $field) {
                        if (!isset($data[$field])) {
                            returnError(400, "Missing required field for reply: $field");
                        }
                    }

                    // Insert the reply into logs_replies table
                    $stmt = $pdo->prepare("
                        INSERT INTO logs_replies (
                            log_id, log_subject, logs_description, client_name, logged_by, created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, NOW()
                        )
                    ");

                    $stmt->execute([
                        $data['log_id'],
                        $data['log_subject'],
                        $data['logs_description'],
                        $data['client_name'],
                        $user['firstname'] . ' ' . $user['lastname'] // Logged in user's full name
                    ]);

                    $replyId = $pdo->lastInsertId();

                    // Optionally fetch the newly created reply to return
                    $stmt = $pdo->prepare("SELECT * FROM logs_replies WHERE logs_rep = ?");
                    $stmt->execute([$replyId]);
                    $newReply = $stmt->fetch(PDO::FETCH_ASSOC);

                    echo json_encode(['success' => true, 'data' => $newReply]);
                } catch (PDOException $e) {
                    returnError(500, 'Database error adding reply: ' . $e->getMessage());
                } catch (Exception $e) {
                    returnError(400, 'Error adding reply: ' . $e->getMessage());
                }
            } else if (isset($_GET['id'])) {
                // Get a single log by ID
                $stmt = $pdo->prepare("SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp FROM logs l JOIN users u ON l.user_id = u.id WHERE l.id = ?");
                $stmt->execute([$_GET['id']]);
                $log = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($log) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'data' => $log]);
                } else {
                    returnError(404, 'Log not found');
                }
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                returnError(400, 'Invalid JSON input: ' . json_last_error_msg());
            }
            
            // Validate required fields
            $required_fields = ['direction', 'type', 'subject'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field])) {
                    returnError(400, "Missing required field: $field");
                }
            }
            
            // Insert new log
            $stmt = $pdo->prepare("
                INSERT INTO logs (
                    user_id, direction, type, subject, content, 
                    sender, confidentiality_level, created_at, client_name, log_by
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?
                )
            ");
            
            $stmt->execute([
                $user['id'],
                $data['direction'],
                $data['type'],
                $data['subject'],
                $data['content'] ?? null,
                $data['sender'] ?? null,
                $data['confidential'] ? 'confidential' : 'public',
                $data['client_name'] ?? null,
                $user['firstname'] . ' ' . $user['lastname']
            ]);
            
            $logId = $pdo->lastInsertId();
            
            // Get the created log, join users for full name, alias created_at as timestamp
            $stmt = $pdo->prepare("
                SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullName, l.created_at AS timestamp
                FROM logs l
                JOIN users u ON l.user_id = u.id
                WHERE l.id = ?
            ");
            $stmt->execute([$logId]);
            $log = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $log]);
            break;

        case 'DELETE':
            // Delete a log entry
            if (!isset($_GET['id'])) {
                returnError(400, 'Missing log id');
            }
            $stmt = $pdo->prepare("DELETE FROM logs WHERE id = ? AND user_id = ?");
            $stmt->execute([$_GET['id'], $user['id']]);
            if ($stmt->rowCount() === 0) {
                returnError(404, 'Log not found or not authorized');
            }
            echo json_encode(['success' => true]);
            break;

        default:
            returnError(405, 'Method not allowed');
    }
} catch (PDOException $e) {
    returnError(500, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    returnError(400, $e->getMessage());
}
