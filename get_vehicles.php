<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(new stdClass());
    exit;
}

$currentUserId = $_SESSION['user_id'];

try {
    $allVehicles = db('GET', 'vehicles');
    $userVehicles = [];

    if ($allVehicles && is_array($allVehicles) && !isset($allVehicles['error'])) {
        foreach ($allVehicles as $vehicleId => $data) {
            if (isset($data['userId']) && $data['userId'] === $currentUserId) {
                // NORMALIZE DATA: Map database keys to frontend keys
                $userVehicles[$vehicleId] = [
                    'nickname' => $data['nickname'] ?? 'Unnamed',
                    'make'     => $data['make'] ?? '',
                    'model'    => $data['model'] ?? '',
                    'year'     => $data['year'] ?? '',
                    // Map licensePlate to plate
                    'plate'    => $data['licensePlate'] ?? $data['plate'] ?? 'NO PLATE',
                    // Map currentOdometer to odometer
                    'odometer' => $data['currentOdometer'] ?? $data['odometer'] ?? 0,
                    'fuel'     => $data['fuelType'] ?? $data['fuel'] ?? 'Petrol',
                    'imageUrl' => $data['imageUrl'] ?? null
                ];
            }
        }
    }

    echo json_encode((object)$userVehicles);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}