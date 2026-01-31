<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

/**
 * AUTO-MIND AI: Maintenance Record Handler
 * Handles both the creation of new logs and editing of existing logs.
 */

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$editingId = $_POST['editingId'] ?? null;
$billUrl = $_POST['existingBillUrl'] ?? null;

// 1. Handle File Upload (Processing the scanned bill or receipt)
if (isset($_FILES['bill_doc']) && $_FILES['bill_doc']['error'] === UPLOAD_ERR_OK) {
    // Standardizing the upload directory
    $uploadDir = 'uploads/maintenance_bills/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExtension = pathinfo($_FILES['bill_doc']['name'], PATHINFO_EXTENSION);
    // Generating a unique filename to prevent overwriting
    $fileName = 'bill_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['bill_doc']['tmp_name'], $targetPath)) {
        $billUrl = $targetPath;
    }
}

// 2. Prepare Data Array
// We sanitize the inputs and ensure the totalCost is a float for accurate math in the Dashboard.
$record_data = [
    'vehicleId'       => htmlspecialchars($_POST['vehicleId']),
    'userId'          => $currentUserId,
    'date'            => $_POST['date'],
    'type'            => htmlspecialchars($_POST['type']),
    'serviceProvider' => htmlspecialchars($_POST['serviceProvider']),
    'description'     => htmlspecialchars($_POST['description']),
    'totalCost'       => (float)$_POST['totalCost'],
    'billUrl'         => $billUrl,
    'updatedAt'       => date('c') // ISO 8601 date for Firebase compatibility
];

try {
    if (!empty($editingId)) {
        // --- UPDATE MODE ---
        $path = "service_logs/" . $editingId;
        
        // PATCH updates only the fields provided without overwriting the whole node
        $result = db('PATCH', $path, $record_data);
        $message = 'Service record updated successfully!';
    } else {
        // --- CREATE MODE ---
        $record_data['createdAt'] = date('c');
        
        // POST creates a new entry with a unique Firebase ID
        $result = db('POST', "service_logs", $record_data);
        $message = 'New service record added to history!';
    }

    echo json_encode([
        'success' => true, 
        'message' => $message,
        'data' => $result
    ]);

} catch (Exception $e) {
    // Return structured error message if the Database helper throws an exception
    echo json_encode([
        'success' => false, 
        'message' => 'System Error: ' . $e->getMessage()
    ]);
}
?>