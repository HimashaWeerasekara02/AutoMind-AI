<?php
require_once 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $vId = $_GET['vehicleId'] ?? '';
        $data = db('GET', "fuel_logs/$vId");
        echo json_encode($data ?: new stdClass());
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $vId = $input['vehicleId'];
        $id = $input['id'] ?? null; 
        
        $entry = [
            'date' => $input['date'],
            'odometer' => (int)$input['odometer'],
            'quantity' => (float)$input['quantity'],
            'cost' => (float)$input['cost']
        ];

        if (!empty($id)) {
            db('PATCH', "fuel_logs/$vId/$id", $entry);
        } else {
            db('POST', "fuel_logs/$vId", $entry);
        }
        echo json_encode(['success' => true]);
    } 
    elseif ($method === 'DELETE') {
        $id = $_GET['id'];
        $vId = $_GET['vehicleId'];
        db('DELETE', "fuel_logs/$vId/$id");
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>