<?php
header('Content-Type: application/json');
require_once 'db.php';

// The frontend sends vehicleId as a GET parameter
if (!isset($_GET['vehicleId'])) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID is required']);
    exit;
}

$vehicle_id = $_GET['vehicleId'];

try {
    // 1. Fetch maintenance records from Firebase for this specific vehicle
    // Note: If using Firebase Realtime DB, you may need to filter by vehicleId via query params
    // or structure your data as service_logs/vehicle_id/log_id
    $records = db('GET', "service_logs");

    $total_spent = 0;
    $service_count = 0;
    $formatted_records = [];

    if ($records && is_array($records)) {
        foreach ($records as $id => $data) {
            // Only include records matching the selected vehicleId
            if (isset($data['vehicleId']) && $data['vehicleId'] === $vehicle_id) {
                $cost = isset($data['totalCost']) ? (float)$data['totalCost'] : 0;
                $total_spent += $cost;
                $service_count++;

                // Map Firebase ID and ensure field consistency
                $data['id'] = $id;
                $formatted_records[] = $data;
            }
        }

        // Sort by date descending (Newest first)
        usort($formatted_records, function($a, $b) {
            return strcmp($b['date'] ?? '', $a['date'] ?? '');
        });
    }

    echo json_encode([
        'success' => true,
        'records' => $formatted_records,
        'stats' => [
            'total_spent' => number_format($total_spent, 2, '.', ''),
            'service_count' => $service_count
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}