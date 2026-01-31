<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

/**
 * AUTO-MIND AI: Maintenance Manager
 * Handles targeted DELETE and PATCH operations for individual logs.
 */

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the record ID from the URL query string
$record_id = $_GET['id'] ?? null;

if (!$record_id) {
    echo json_encode(['success' => false, 'message' => 'Record ID is required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Firebase Path: service_logs/{record_id}
    $path = "service_logs/$record_id";

    if ($method === 'DELETE') {
        /**
         * DELETE OPERATION
         * Triggered when clicking "Delete" in the Maintenance History table.
         */
        db('DELETE', $path);
        echo json_encode(['success' => true, 'message' => 'Maintenance record purged successfully']);
        
    } elseif ($method === 'PATCH') {
        /**
         * QUICK UPDATE OPERATION
         * Used for updating text or status fields without re-uploading files.
         */
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception("No data payload received");

        // Prepare sanitized update array
        $update_data = [];
        $fields = ['date', 'type', 'description', 'totalCost', 'serviceProvider'];
        
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                // Sanitize strings, cast numbers
                if ($field === 'totalCost') {
                    $update_data[$field] = (float)$input[$field];
                } else {
                    $update_data[$field] = htmlspecialchars($input[$field]);
                }
            }
        }
        
        // Audit timestamp
        $update_data['updatedAt'] = date('c');

        // Execute PATCH to Firebase
        db('PATCH', $path, $update_data);
        echo json_encode(['success' => true, 'message' => 'Record synchronized successfully']);
        
    } else {
        // Handle unsupported methods (like GET or PUT here)
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method ' . $method . ' not supported by this endpoint']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Management Error: ' . $e->getMessage()]);
}