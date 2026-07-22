<?php

header('Content-Type: application/json');
require_once 'db.php';
session_start();

ini_set('display_errors', 0);

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$record_id = $_REQUEST['id'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

if (!$record_id) {
    echo json_encode(['success' => false, 'message' => 'Record ID required for this operation.']);
    exit;
}

try {
    $path = "maintenance_logs/$record_id";

    if ($method === 'DELETE') {
        db('DELETE', $path);
        echo json_encode(['success' => true, 'message' => 'Entry deleted from neural history.']);
        exit;
    }

    if ($method === 'PATCH' || $method === 'POST') {
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        if (empty($input)) {
            throw new Exception("No data payload detected for update.");
        }

        $update_data = [];
        $allowed_fields = ['date', 'type', 'description', 'totalCost', 'serviceProvider', 'odometer'];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                if ($field === 'totalCost' || $field === 'odometer') {
                    $update_data[$field] = (float)$input[$field];
                } else {
                    $update_data[$field] = htmlspecialchars($input[$field]);
                }
            }
        }

        if (empty($update_data)) {
            throw new Exception("No valid fields provided for synchronization.");
        }

        $update_data['updatedAt'] = date('c');
        
        db('PATCH', $path, $update_data);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Record synchronized successfully.',
            'synced_fields' => array_keys($update_data)
        ]);
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => "Method $method not supported."]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
}