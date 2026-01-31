<?php
header('Content-Type: application/json');
require_once 'db.php';

// Check if vehicleId is provided
if (!isset($_GET['vehicleId']) || empty($_GET['vehicleId'])) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID is required']);
    exit;
}

$vehicle_id = $_GET['vehicleId'];

try {
    // 1. Fetch maintenance records from the service_logs node
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

                $formatted_records[] = [
                    'id'              => $id,
                    'date'            => $data['date'] ?? '',
                    'type'            => $data['type'] ?? 'General',
                    'serviceProvider' => $data['serviceProvider'] ?? 'Unknown',
                    'description'     => $data['description'] ?? '',
                    'totalCost'       => $cost,
                    'billUrl'         => $data['billUrl'] ?? null,
                    'createdAt'       => $data['createdAt'] ?? null
                ];
            }
        }

        // Sort by date descending (Newest first)
        usort($formatted_records, function($a, $b) {
            return strcmp($b['date'], $a['date']);
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
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}