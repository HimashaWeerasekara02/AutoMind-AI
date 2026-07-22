<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['vehicleId']) || empty($_GET['vehicleId'])) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID is required']);
    exit;
}

$vehicle_id = $_GET['vehicleId'];

try {
    $all_fuel_data = db('GET', "fuel_logs/$vehicle_id");

    $logs = [];
    $total_cost = 0;
    $total_liters = 0;

    if ($all_fuel_data && is_array($all_fuel_data)) {
        foreach ($all_fuel_data as $id => $data) {
            $cost = isset($data['cost']) ? (float)$data['cost'] : 0;
            $qty = isset($data['quantity']) ? (float)$data['quantity'] : 0;
        
            $fuel_level = isset($data['fuel_level']) ? (float)$data['fuel_level'] : 0;
            
            $total_cost += $cost;
            $total_liters += $qty;

            $logs[] = [
                'id' => $id,
                'date' => $data['date'] ?? '',
                'odometer' => (int)($data['odometer'] ?? 0),
                'quantity' => $qty,
                'cost' => $cost,
                'fuel_level' => $fuel_level, 
                'station' => $data['station'] ?? 'Unknown'
            ];
        }

        usort($logs, function($a, $b) {
            return $b['odometer'] - $a['odometer'];
        });
    }

    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'summary' => [
            'total_spent' => $total_cost,
            'total_liters' => $total_liters,
            'entry_count' => count($logs)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Fuel Database Error: ' . $e->getMessage()
    ]);
}