<?php
header('Content-Type: application/json');
require_once 'db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validation based on ServiceLogs entity
if (!$data || !isset($data['vehicleId']) || !isset($data['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data: vehicleId and userId are required']);
    exit;
}

/**
 * Data mapping for ServiceLogs entity
 */
$new_record = [
    'vehicleId'       => htmlspecialchars($data['vehicleId']),       
    'userId'          => htmlspecialchars($data['userId']),          
    'date'            => $data['date'],                              
    'serviceProvider' => htmlspecialchars($data['serviceProvider']), 
    'description'     => htmlspecialchars($data['description']),    
    'odometer'        => (int)$data['odometer'],                     
    'totalCost'       => (float)$data['totalCost'],                  
    'notes'           => htmlspecialchars($data['notes'] ?? ''),     
    'createdAt'       => date('c')                                   
];

try {
    $path = "service_logs"; 
    $result = db('POST', $path, $new_record);

    if ($result && isset($result['name'])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Maintenance record logged successfully',
            'serviceLogId' => $result['name'] 
        ]);
    } else {
        throw new Exception("Failed to save to database");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}