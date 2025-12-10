<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

if (!isset($_FILES['document'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'No file uploaded']));
}

$uploadDir = 'uploads/glovebox/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$fileName = basename($_FILES['document']['name']);
$targetPath = $uploadDir . time() . '_' . $fileName;

if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    // Optional: Save file metadata to DB
    try {
        $stmt = $pdo->prepare("INSERT INTO documents (file_name, file_path, uploaded_at) VALUES (?, ?, NOW())");
        $stmt->execute([$fileName, $targetPath]);
        
        echo json_encode(['success' => true, 'message' => 'File uploaded', 'path' => $targetPath]);
    } catch (Exception $e) {
        // File saved but DB failed
        echo json_encode(['success' => true, 'warning' => 'File saved but DB error']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to move uploaded file']);
}
?>