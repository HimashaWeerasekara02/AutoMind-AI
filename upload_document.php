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

// 1. Validate Required File
if (!isset($_FILES['document'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

// 2. Mapping variables to Database Schema
$vehicleId  = $_POST['vehicleId'] ?? null; // Matching DB vehicleId
$title      = $_POST['title'] ?? basename($_FILES['document']['name']); // Display name
$expiryDate = $_POST['expiryDate'] ?? null; // Matching DB expiryDate

if (!$vehicleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing vehicleId. Cannot link document.']);
    exit;
}

// 3. Setup Directory
$uploadDir = 'uploads/glovebox/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 4. Security: Validate File Extension
$originalName = basename($_FILES['document']['name']);
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

if (!in_array($fileExtension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Use PDF, JPG, or PNG.']);
    exit;
}

// 5. Create a unique file path
$safeFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
$targetPath = $uploadDir . $safeFileName;

// 6. Move File and Save to Database
if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    
    // Aligned with the "Documents" Entity in your ER Diagram
    $documentData = [
        'vehicleId'   => $vehicleId,      // Foreign Key
        'title'       => $title,          // Friendly Display Name
        'fileUrl'     => $targetPath,     // Path to file
        'expiryDate'  => $expiryDate,     // Critical for reminders
        'uploadDate'  => date("Y-m-d"),   // Created date
        'type'        => $_FILES['document']['type']
    ];

    try {
        // Saves to the "documents" node
        $firebaseResult = db("POST", "documents", $documentData);

        echo json_encode([
            'success' => true,
            'message' => 'Document saved successfully',
            'fileUrl' => $targetPath,
            'documentId' => $firebaseResult['name'] ?? null
        ]);
    } catch (Exception $e) {
        // Rollback: delete file if database entry fails
        if (file_exists($targetPath)) unlink($targetPath);
        
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error'   => 'Database update failed: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to move file to server storage']);
}