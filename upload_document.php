<?php
/**
 * Upload Document API
 * AutoMind AI
 */

header('Content-Type: application/json');

// Disable error display to prevent JSON corruption, 
// but log them to the server log.
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';

// ✅ 1. Check Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ✅ 2. Check if file exists in request
if (!isset($_FILES['document'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

// ✅ 3. Setup Directory
$uploadDir = 'uploads/glovebox/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ 4. Prepare File Path
$originalName = basename($_FILES['document']['name']);
$fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
// Create a unique name to prevent overwriting files with the same name
$safeFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
$targetPath = $uploadDir . $safeFileName;

// ✅ 5. Move File and Save to Firebase
if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    
    // Data to store in Firebase
    $documentData = [
        'file_name'   => $originalName,
        'file_path'   => $targetPath,
        'uploaded_at' => date("Y-m-d H:i:s"),
        'type'        => $_FILES['document']['type']
    ];

    try {
        // Use your Firebase db() function instead of $pdo
        // This saves to a node called "documents"
        $firebaseResult = db("POST", "documents", $documentData);

        echo json_encode([
            'success' => true,
            'message' => 'File uploaded and saved to Firebase',
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