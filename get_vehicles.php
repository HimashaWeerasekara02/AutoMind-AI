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
    // This calls your helper in db.php
    $allVehicles = db('GET', 'vehicles');
    $userVehicles = [];

    // Check if $allVehicles is an array (not null or error)
    if ($allVehicles && is_array($allVehicles) && !isset($allVehicles['error'])) {
        foreach ($allVehicles as $vehicleId => $data) {
            // Check if vehicle belongs to current logged in user
            if (isset($data['userId']) && $data['userId'] === $currentUserId) {
                $userVehicles[$vehicleId] = $data;
            }
        }
    }

    // Force return as JSON Object {} instead of Array [] if empty
    // This is critical for Object.keys() in your dashboard JS
    echo json_encode((object)$userVehicles);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}