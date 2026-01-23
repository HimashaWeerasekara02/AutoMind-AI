<?php
header('Content-Type: application/json');
require_once 'db.php';

// GET parameters from the URL
$record_id = $_GET['id'] ?? null;

if (!$record_id) {
    echo json_encode(['success' => false, 'message' => 'Record ID is required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Path: service_logs/{record_id}
    $path = "service_logs/$record_id";

    if ($method === 'DELETE') {
        /**
         * DELETE RECORD
         */
        db('DELETE', $path);
        echo json_encode(['success' => true, 'message' => 'Maintenance record deleted successfully']);
        
    } elseif ($method === 'PATCH') {
        /**
         * UPDATE RECORD
         */
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception("No data provided for update");

        // Aligning with ServiceLogs schema
        $update_data = [];
        if (isset($input['date']))            $update_data['date'] = $input['date'];
        if (isset($input['type']))            $update_data['type'] = htmlspecialchars($input['type']);
        if (isset($input['description']))     $update_data['description'] = htmlspecialchars($input['description']);
        if (isset($input['odometer']))        $update_data['odometer'] = (int)$input['odometer'];
        if (isset($input['totalCost']))       $update_data['totalCost'] = (float)$input['totalCost'];
        if (isset($input['serviceProvider'])) $update_data['serviceProvider'] = htmlspecialchars($input['serviceProvider']);
        
        // Always update the modified timestamp
        $update_data['updatedAt'] = date('c');

        db('PATCH', $path, $update_data);
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}