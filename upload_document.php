<?php
header('Content-Type: application/json');

// Enable error reporting for debugging, but keep JSON output clean
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';
session_start();

// 0. Authentication Check
// We need the logged-in user's ID to own this document
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// 1. Validate Required File
if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
    exit;
}

// 2. Mapping variables from POST
$vehicleId  = $_POST['vehicleId'] ?? null;
$title       = $_POST['title'] ?? basename($_FILES['document']['name']);
$expiryDate = $_POST['expiryDate'] ?? null; // Important for Cron Job

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

// 5. Create a unique file path to prevent overwriting
$safeFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
$targetPath = $uploadDir . $safeFileName;

// 6. Move File and Save to Database
if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    
    // NEW: Including 'userId' so the Automatic Reminder script knows who to message
    $documentData = [
        'userId'      => $userId,         // OWNER of the document
        'vehicleId'   => $vehicleId,      // Vehicle linked to
        'title'       => trim($title),    // e.g., "Revenue License"
        'fileUrl'     => $targetPath,     // Path for the 'VIEW' button
        'expiryDate'  => $expiryDate,     // THE CRITICAL FIELD FOR CRON JOB
        'uploadDate'  => date("Y-m-d"),   // Log keeping
        'fileType'    => $_FILES['document']['type']
    ];

    try {
        // Saves to the "documents" node in Firebase
        $firebaseResult = db("POST", "documents", $documentData);

        echo json_encode([
            'success' => true,
            'message' => 'Document saved successfully',
            'fileUrl' => $targetPath,
            'documentId' => $firebaseResult['name'] ?? null
        ]);
    } catch (Exception $e) {
        // Rollback: delete physical file if database entry fails
        if (file_exists($targetPath)) unlink($targetPath);
        
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error'   => 'Database update failed: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to move file to server storage. Check folder permissions.']);
}