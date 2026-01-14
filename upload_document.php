<?php
/**
 * Upload Document API
 * AutoMind AI - Updated to link documents to specific vehicles
 */

header('Content-Type: application/json');

// Disable error display to prevent JSON corruption
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';

// ✅ 1. Check Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ✅ 2. Check if file and vehicle_id exists
if (!isset($_FILES['document'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$vehicleId = $_POST['vehicle_id'] ?? null;
if (!$vehicleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing vehicle_id. Cannot link document.']);
    exit;
}

// ✅ 3. Setup Directory
$uploadDir = 'uploads/glovebox/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ 4. Security: Validate File Extension
$originalName = basename($_FILES['document']['name']);
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

if (!in_array($fileExtension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only PDF and Images allowed.']);
    exit;
}

// Create a unique name to prevent overwriting
$safeFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
$targetPath = $uploadDir . $safeFileName;

// ✅ 5. Move File and Save to Firebase
if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    
    // Data to store in Firebase
    $documentData = [
        'vehicle_id'  => $vehicleId, // 🔗 This links the doc to the car
        'file_name'   => $originalName,
        'file_path'   => $targetPath,
        'uploaded_at' => date("Y-m-d H:i:s"),
        'type'        => $_FILES['document']['type']
    ];

    try {
        // Saves to the "documents" node in Firebase
        $firebaseResult = db("POST", "documents", $documentData);

        echo json_encode([
            'success' => true,
            'message' => 'File uploaded and linked to vehicle',
            'path'    => $targetPath,
            'firebase_id' => $firebaseResult['name'] ?? null
        ]);
    } catch (Exception $e) {
        // File is on the server, but Firebase failed
        echo json_encode([
            'success' => true, 
            'warning' => 'File saved on server, but failed to update Firebase',
            'error'   => $e->getMessage()
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file to server folder']);
}