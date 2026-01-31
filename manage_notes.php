<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$vehicleId = $_GET['vehicleId'] ?? null;

try {
    switch ($method) {
        case 'GET':
            if (!$vehicleId) throw new Exception("Vehicle ID required");
            $data = db('GET', "vehicle_notes/$vehicleId");
            echo json_encode($data ?: []);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $vId = $input['vehicleId'];
            $result = db('POST', "vehicle_notes/$vId", [
                'title' => htmlspecialchars($input['title']),
                'content' => htmlspecialchars($input['content']),
                'category' => $input['category'],
                'createdAt' => date('c'),
                'userId' => $_SESSION['user_id']
            ]);
            echo json_encode(['success' => true]);
            break;

        case 'PATCH':
            if (!$id) throw new Exception("Note ID required");
            $input = json_decode(file_get_contents('php://input'), true);
            $vId = $input['vehicleId'];
            db('PATCH', "vehicle_notes/$vId/$id", [
                'title' => htmlspecialchars($input['title']),
                'content' => htmlspecialchars($input['content']),
                'category' => $input['category']
            ]);
            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            if (!$id || !$vehicleId) throw new Exception("ID and VehicleID required");
            db('DELETE', "vehicle_notes/$vehicleId/$id");
            echo json_encode(['success' => true]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}