<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $vehicles = db('GET', 'vehicles');
    // If Firebase is empty, return an empty object
    if ($vehicles === null) {
        echo json_encode(new stdClass()); 
    } else {
        // Return raw associative array for the Garage's "for...in" loop
        echo json_encode($vehicles);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}