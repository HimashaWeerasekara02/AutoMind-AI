<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Method not allowed. Use POST."
    ]);
    exit;
}

$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true);

if ($input === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Invalid JSON input"
    ]);
    exit;
}

// Validate required fields
$requiredFields = ['nickname', 'make', 'model', 'year'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || trim($input[$field]) === '') {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Missing required field: $field"
        ]);
        exit;
    }
}

// Prepare vehicle data
$vehicle = [
    "nickname"    => trim($input['nickname']),
    "make"        => trim($input['make']),
    "model"       => trim($input['model']),
    "year"        => (int)$input['year'],
    "plate"       => trim($input['plate'] ?? ''),
    "created_at"  => date("Y-m-d H:i:s")
];

try {
    // Insert vehicle into Firebase
    $result = db("POST", "vehicles", $vehicle);

    // Check Firebase response
    if (!isset($result['name'])) {
        throw new Exception("Firebase insert failed: no ID returned");
    }

    // Success response
    echo json_encode([
        "success" => true,
        "message" => "Vehicle added successfully",
        "id" => $result['name']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
