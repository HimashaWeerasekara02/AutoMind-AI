<?php

header('Content-Type: application/json');

require_once 'db.php';

try {
    $vehicles = db('GET', 'vehicles');
   
    if ($vehicles === null || $vehicles === false) {
        echo json_encode([]);
        exit;
    }

    echo json_encode($vehicles);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}