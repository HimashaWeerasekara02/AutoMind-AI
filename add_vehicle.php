<?php
header('Content-Type: application/json');


require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed."]);
    exit;
}


$requiredFields = ['nickname', 'make', 'model', 'year', 'odometer'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || (empty($_POST[$field]) && $_POST[$field] !== "0")) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing required field: $field"]);
        exit;
    }
}


$imageUrl = "";
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileType = $_FILES['image']['type'];
    
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


$vehicleData = [
    "userId"          => $_SESSION['user_id'] ?? "guest_user", 
    "nickname"        => trim($_POST['nickname']),
    "make"            => trim($_POST['make']),
    "model"           => trim($_POST['model']),
    "year"            => (int)$_POST['year'],
    "fuelType"        => trim($_POST['fuel'] ?? 'Petrol'), 
    "licensePlate"    => trim($_POST['plate'] ?? ''),
    "currentOdometer" => (int)$_POST['odometer'],
    "imageUrl"        => $imageUrl,
    "createdAt"       => date("c") 
];

try {
    
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