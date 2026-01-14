<?php
/**
 * Get Vehicles API
 * AutoMind AI
 */

// 🔹 Always return JSON
header('Content-Type: application/json');

// 🔹 Show errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

try {
    // 🔹 Fetch vehicles from Firebase
    $vehicles = db('GET', 'vehicles');

    /**
     * Firebase returns:
     * - null → when no data
     * - object → when data exists
     */
    if ($vehicles === null || $vehicles === false) {
        echo json_encode([]);
        exit;
    }

    // 🔹 Success response
    echo json_encode($vehicles);

} catch (Exception $e) {
    // 🔹 Error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
