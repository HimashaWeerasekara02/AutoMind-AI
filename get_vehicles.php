<?php

header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode((object)[]);
    exit;
}

$currentUserId = $_SESSION['user_id'];

try {
    $allVehicles = db('GET', 'vehicles');
    $userVehicles = [];

    if ($allVehicles && is_array($allVehicles) && !isset($allVehicles['error'])) {
        foreach ($allVehicles as $vehicleId => $data) {
            
            if (isset($data['userId']) && $data['userId'] === $currentUserId) {
                
                $userVehicles[$vehicleId] = [
                    'id'            => $vehicleId, 
                    'nickname'      => $data['nickname'] ?? 'Unnamed Vehicle',
                    'make'          => $data['make'] ?? 'Unknown',
                    'model'         => $data['model'] ?? 'Unknown',
                    'year'          => $data['year'] ?? 'N/A',
                    
                    'plate'         => $data['plate'] ?? ($data['licensePlate'] ?? 'N/A'),
                    
                    'odometer'      => $data['odometer'] ?? ($data['currentOdometer'] ?? 0),
                    
                    'fuel'          => $data['fuel'] ?? ($data['fuel_type'] ?? 'Petrol'),
                    
                    'fuel_level'    => $data['fuel_level'] ?? ($data['currentFuel'] ?? 0),
                    'tank_capacity' => $data['tank_capacity'] ?? ($data['capacity'] ?? 45),
                    
                    'imageUrl'      => $data['imageUrl'] ?? null,
                    'userId'        => $data['userId']
                ];
            }
        }
    }

    echo json_encode((object)$userVehicles);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}