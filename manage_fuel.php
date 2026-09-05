<?php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$vehicleId = $_GET['vehicleId'] ?? null;
$id = $_GET['id'] ?? null;

try {
    if ($method === 'GET') {
        $data = db('GET', 'fuel_logs');
        $filtered = [];
        if ($data) {
            foreach ($data as $key => $val) {
                if ($val['vehicleId'] === $vehicleId) $filtered[$key] = $val;
            }
        }
        echo json_encode($filtered);
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $result = db('POST', 'fuel_logs', $input);
        echo json_encode(['success' => true, 'id' => $result['name']]);
    } 
    elseif ($method === 'DELETE') {
        db('DELETE', "fuel_logs/$id");
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}