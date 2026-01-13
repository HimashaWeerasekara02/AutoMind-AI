<?php
// Always return JSON
header('Content-Type: application/json');

// Show errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

try {
    // ✅ Get all vehicles from Firebase
    $vehicles = db('GET', 'vehicles');

    // ✅ Ensure we always return an array (empty if no vehicles)
    if (!$vehicles) {
        $vehicles = [];
    }

    echo json_encode($vehicles);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
