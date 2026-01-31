<?php
header('Content-Type: application/json');

/**
 * db.php should contain your Firebase connection logic 
 * and the db($method, $path, $data) function.
 */
require 'db.php';
session_start();

// 1. Method Guard
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed."]);
    exit;
}

/**
 * 2. Validation based on ER Diagram Requirements
 */
$requiredFields = ['nickname', 'make', 'model', 'year', 'odometer'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || (empty($_POST[$field]) && $_POST[$field] !== "0")) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing required field: $field"]);
        exit;
    }
}

/**
 * 3. Image Processing (File to Base64)
 */
$imageUrl = "";
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileType = $_FILES['image']['type'];
    
    // Allowed image types
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (in_array($fileType, $allowed)) {
        $imageData = file_get_contents($fileTmpPath);
        $imageUrl = 'data:' . $fileType . ';base64,' . base64_encode($imageData);
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Only JPG, PNG, and WEBP images allowed."]);
        exit;
    }
}

/**
 * 4. Data Mapping (Matches ER Diagram: Vehicles Entity)
 */
$vehicleData = [
    "userId"          => $_SESSION['user_id'] ?? "guest_user", // Link to Users entity
    "nickname"        => trim($_POST['nickname']),
    "make"            => trim($_POST['make']),
    "model"           => trim($_POST['model']),
    "year"            => (int)$_POST['year'],
    "fuelType"        => trim($_POST['fuel'] ?? 'Petrol'), 
    "licensePlate"    => trim($_POST['plate'] ?? ''),
    "currentOdometer" => (int)$_POST['odometer'],
    "imageUrl"        => $imageUrl,
    "createdAt"       => date("c") // ISO 8601
];

try {
    /**
     * 5. Firebase Write
     * Note: We store under 'vehicles' node as per standard structure
     */
    $result = db("POST", "vehicles", $vehicleData);

    if (!isset($result['name'])) {
        throw new Exception("Firebase failed to generate a vehicleId.");
    }

    echo json_encode([
        "success"   => true,
        "message"   => "Vehicle successfully registered!",
        "vehicleId" => $result['name']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}