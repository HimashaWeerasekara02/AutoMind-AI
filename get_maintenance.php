<?php

header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$vehicleId = $_GET['vehicleId'] ?? null;

if (!$vehicleId) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID required']);
    exit;
}

try {
    $allLogs = db('GET', "maintenance_logs");
    
    $filteredRecords = [];
    $totalSpent = 0;
    $serviceCount = 0;

    if ($allLogs) {
        foreach ($allLogs as $id => $data) {
            
            if (isset($data['vehicleId']) && $data['vehicleId'] === $vehicleId) {
                $data['id'] = $id; 
                $filteredRecords[] = $data;
                
                $totalSpent += (float)($data['totalCost'] ?? $data['cost'] ?? 0);
                $serviceCount++;
            }
        }
    }

    usort($filteredRecords, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    echo json_encode([
        'success' => true,
        'records' => $filteredRecords,
        'stats' => [
            'total_spent' => $totalSpent,
            'service_count' => $serviceCount
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}