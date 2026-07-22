<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';
session_start();

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

if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
    exit;
}

$vehicleId  = $_POST['vehicleId'] ?? null;
$title       = $_POST['title'] ?? basename($_FILES['document']['name']);
$expiryDate = $_POST['expiryDate'] ?? null; 

if (!$vehicleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing vehicleId. Cannot link document.']);
    exit;
}


$uploadDir = 'uploads/glovebox/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}


$originalName = basename($_FILES['document']['name']);
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

if (!in_array($fileExtension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Use PDF, JPG, or PNG.']);
    exit;
}


$safeFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
$targetPath = $uploadDir . $safeFileName;


if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    
    
    $documentData = [
        'userId'      => $userId,         
        'vehicleId'   => $vehicleId,      
        'title'       => trim($title),    
        'expiryDate'  => $expiryDate,     
        'uploadDate'  => date("Y-m-d"),   
        'fileType'    => $_FILES['document']['type']
    ];

    try {
        $firebaseResult = db("POST", "documents", $documentData);

        echo json_encode([
            'success' => true,
            'message' => 'Document saved successfully',
            'fileUrl' => $targetPath,
            'documentId' => $firebaseResult['name'] ?? null
        ]);
    } catch (Exception $e) {
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