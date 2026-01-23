<?php
header('Content-Type: application/json');

/**
 * db.php should contain your Firebase connection logic 
 * and the db($method, $path, $data) function.
 */
require 'db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed."]);
    exit;
}

// Get the JSON payload (containing Base64 image and text fields)
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid JSON input"]);
    exit;
}

/**
 * 1. Validation
 * nickname, make, model, year, and odometer are required based on your UI.
 */
$requiredFields = ['nickname', 'make', 'model', 'year', 'odometer'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || (empty($input[$field]) && $input[$field] !== "0")) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing required field: $field"]);
        exit;
    }
}

/**
 * 2. Data Preparation
 * Mapping frontend keys (plate, fuel, image) to 
 * Database/ER Diagram keys (licensePlate, fuelType, imageUrl).
 */
$vehicleData = [
    "userId"          => $input['userId'] ?? "user_jane_01", // Default for testing/session
    "nickname"        => trim($input['nickname']),
    "make"            => trim($input['make']),
    "model"           => trim($input['model']),
    "year"            => (int)$input['year'],
    "licensePlate"    => trim($input['plate'] ?? ''),
    "currentOdometer" => (int)$input['odometer'],
    "fuelType"        => trim($input['fuel'] ?? 'Petrol'), 
    "imageUrl"        => $input['image'] ?? '', // Stores the Base64 Data URL
    "createdAt"       => date("c")              // ISO 8601 timestamp
];

try {
    /**
     * 3. Firebase Interaction
     * Sending data to the 'vehicles' node/collection.
     */
    $result = db("POST", "vehicles", $vehicleData);

    // Firebase returns a 'name' field which acts as the unique ID (Key)
    if (!isset($result['name'])) {
        throw new Exception("Firebase Error: Record was not created.");
    }

    echo json_encode([
        "success"   => true,
        "message"   => "Vehicle added to your garage!",
        "vehicleId" => $result['name']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error"   => "Server Error: " . $e->getMessage()
    ]);
}