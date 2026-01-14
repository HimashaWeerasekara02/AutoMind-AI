<?php
/**
 * Manage Vehicle API (Edit & Delete)
 * AutoMind AI
 */

header('Content-Type: application/json');

// Disable error display for clean JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get Vehicle ID from URL (?id=-Nxxxx...)
$vehicleId = $_GET['id'] ?? null;

// Parse JSON input for PATCH requests
$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true);

if (!$vehicleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Vehicle ID is required']);
    exit;
}

try {
    // --- UPDATE VEHICLE (PATCH) ---
    if ($method === 'PATCH') {
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No data provided for update']);
            exit;
        }

        // Define allowed fields to prevent accidental overwrites of system data
        $allowedFields = ['nickname', 'make', 'model', 'year', 'plate'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = ($field === 'year') ? (int)$input[$field] : trim($input[$field]);
            }
        }

        if (empty($updateData)) {
            throw new Exception("No valid fields provided for update");
        }

        // Send update to Firebase
        db('PATCH', "vehicles/$vehicleId", $updateData);

        echo json_encode([
            'success' => true, 
            'message' => 'Vehicle updated successfully'
        ]);
    } 

    // --- DELETE VEHICLE (DELETE) ---
    else if ($method === 'DELETE') {
        // Optional: You could also fetch all documents linked to this vehicle 
        // and delete them here to keep your storage clean.
        
        db('DELETE', "vehicles/$vehicleId");

        echo json_encode([
            'success' => true, 
            'message' => 'Vehicle deleted successfully'
        ]);
    }

    // --- METHOD NOT ALLOWED ---
    else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}