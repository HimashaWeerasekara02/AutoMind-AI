<?php
header('Content-Type: application/json');

// Ensure error reporting is off for clean JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$vehicleId = $_GET['vehicleId'] ?? null;
$id = $_GET['id'] ?? null;

try {
    // --- GET NOTES ---
    if ($method === 'GET') {
        if (!$vehicleId) {
            throw new Exception("Vehicle ID is required to fetch notes.");
        }
        
        $data = db('GET', 'vehicle_notes');
        $filtered = [];
        
        if ($data && is_array($data)) {
            foreach ($data as $key => $val) {
                if (isset($val['vehicleId']) && $val['vehicleId'] === $vehicleId) {
                    $filtered[$key] = $val;
                }
            }
        }
        echo json_encode($filtered);
    } 
    
    // --- CREATE NOTE ---
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception("Invalid JSON input.");
        
        // Basic Validation
        if (!isset($input['vehicleId']) || !isset($input['title'])) {
            throw new Exception("Missing required fields (vehicleId or title).");
        }

        $result = db('POST', 'vehicle_notes', $input);
        
        if ($result && isset($result['name'])) {
            echo json_encode(['success' => true, 'id' => $result['name']]);
        } else {
            throw new Exception("Failed to write to Firebase.");
        }
    } 
    
    // --- UPDATE NOTE ---
    elseif ($method === 'PATCH') {
        if (!$id) throw new Exception("Note ID is required for update.");
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception("No data provided for update.");

        db('PATCH', "vehicle_notes/$id", $input);
        echo json_encode(['success' => true]);
    }
    
    // --- DELETE NOTE ---
    elseif ($method === 'DELETE') {
        if (!$id) throw new Exception("Note ID is required for deletion.");
        
        db('DELETE', "vehicle_notes/$id");
        echo json_encode(['success' => true]);
    }
    
    else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}