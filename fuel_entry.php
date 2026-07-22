<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$vehicleId = $_POST['vehicleId'] ?? null;
$odometer  = $_POST['odometer'] ?? null;
$quantity  = $_POST['quantity'] ?? null; 
$cost      = $_POST['cost'] ?? null;     

if (!$vehicleId || !$odometer || !$quantity || !$cost) {
    echo json_encode(['success' => false, 'message' => 'All telemetry fields are required.']);
    exit;
}

try {
    $fuel_data = [
        'userId'   => $_SESSION['user_id'],
        'date'     => $_POST['date'] ?? date('Y-m-d'),
        'odometer' => (int)$odometer,
        'quantity' => (float)$quantity,
        'cost'     => (float)$cost,
        'station'  => htmlspecialchars($_POST['station'] ?? 'Generic Station'),
        'fullTank' => isset($_POST['fullTank']) ? true : false,
        'createdAt'=> date('c')
    ];

  
    $path = "fuel_logs/$vehicleId";
    $result = db('POST', $path, $fuel_data);


    db('PATCH', "vehicles/$vehicleId", [
        'lastOdometer' => (int)$odometer,
        'lastFuelUpdate' => date('c')
    ]);

    echo json_encode([
        'success' => true, 
        'message' => 'Fuel telemetry synchronized successfully!',
        'logId'   => $result['name'] ?? null
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Telemetry Sync Error: ' . $e->getMessage()
    ]);
}