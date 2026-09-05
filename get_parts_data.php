<?php

header('Content-Type: application/json');
require_once 'db.php';

try {
    $data = db('GET', 'car_parts_dataset');
    
    if (!$data) {
        echo json_encode([]);
        exit;
    }

    echo json_encode(array_values($data));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>