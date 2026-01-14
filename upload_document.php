<?php

header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Validate Required Inputs
if (!isset($_FILES['document'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$vehicleId  = $_POST['vehicle_id'] ?? null;
$expiryDate = $_POST['expiry_date'] ?? null; 

if (!$vehicleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing vehicle_id. Cannot link document.']);
    exit;
}

// Setup Directory
$uploadDir = 'uploads/glovebox/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Security: Validate File Extension
$originalName = basename($_FILES['document']['name']);
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

if (!in_array($fileExtension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Use PDF, JPG, or PNG.']);
    exit;
}

// Create a unique name to prevent overwriting
$safeFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
$targetPath = $uploadDir . $safeFileName;

// Move File and Save to Firebase
if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    
    // Data to store in Firebase
    $documentData = [
        'vehicle_id'  => $vehicleId,   // 🔗 Linked Car ID
        'file_name'   => $originalName,
        'file_path'   => $targetPath,
        'expiry_date' => $expiryDate,  // 📅 Important for Insurance/License
        'uploaded_at' => date("Y-m-d H:i:s"),
        'type'        => $_FILES['document']['type']
    ];

    try {
        // Saves to the "documents" node in Firebase
        $firebaseResult = db("POST", "documents", $documentData);

        echo json_encode([
            'success' => true,
            'message' => 'Document saved successfully',
            'path'    => $targetPath,
            'firebase_id' => $firebaseResult['name'] ?? null
        ]);
    } catch (Exception $e) {
        // File exists on server, but database entry failed
        echo json_encode([
            'success' => true, 
            'warning' => 'File saved on server, but database update failed',
            'error'   => $e->getMessage()
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to move file to server storage']);
}