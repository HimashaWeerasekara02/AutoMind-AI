<?php
header('Content-Type: application/json');

// Disable error display for clean JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$vehicleId = $_GET['id'] ?? null;

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

        /**
         * Expanded Data Mapping
         * We map frontend keys to your specific Database/Firebase keys.
         */
        $updateData = [];

        // Text Fields
        if (isset($input['nickname'])) $updateData['nickname'] = trim($input['nickname']);
        if (isset($input['make']))     $updateData['make']     = trim($input['make']);
        if (isset($input['model']))    $updateData['model']    = trim($input['model']);
        if (isset($input['plate']))    $updateData['licensePlate'] = trim($input['plate']);
        if (isset($input['fuel']))     $updateData['fuelType'] = trim($input['fuel']);
        
        // Numeric Fields
        if (isset($input['year']))     $updateData['year'] = (int)$input['year'];
        if (isset($input['odometer'])) $updateData['currentOdometer'] = (int)$input['odometer'];

        // Image Field (Base64 string)
        if (!empty($input['image'])) {
            $updateData['imageUrl'] = $input['image'];
        }

        if (empty($updateData)) {
            throw new Exception("No valid fields provided for update");
        }

        // Send partial update to Firebase Node: vehicles/{vehicleId}
        db('PATCH', "vehicles/$vehicleId", $updateData);

        echo json_encode([
            'success' => true, 
            'message' => 'Vehicle features updated successfully'
        ]);
    } 

    // --- DELETE VEHICLE (DELETE) ---
    else if ($method === 'DELETE') {
        db('DELETE', "vehicles/$vehicleId");

        echo json_encode([
            'success' => true, 
            'message' => 'Vehicle deleted successfully'
        ]);
    }

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