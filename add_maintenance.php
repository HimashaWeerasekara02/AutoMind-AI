<?php

header('Content-Type: application/json');
require_once 'db.php';
session_start();


ini_set('display_errors', 0);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$vehicleId = $_POST['vehicleId'] ?? null;
$editingId = $_POST['editingId'] ?? null;
$billPath = $_POST['existingBillPath'] ?? ''; 

if (!$vehicleId) {
    echo json_encode(['success' => false, 'message' => 'Vehicle Identification Failed.']);
    exit;
}

try {
    if (isset($_FILES['bill_doc']) && $_FILES['bill_doc']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/maintenance_bills/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileTmpPath = $_FILES['bill_doc']['tmp_name'];
        $fileName = time() . '_' . str_replace(' ', '_', $_FILES['bill_doc']['name']);
        $dest_path = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $billPath = $dest_path;
        }
    }

    $record_data = [
        'vehicleId'       => $vehicleId,
        'userId'          => $currentUserId,
        'date'            => $_POST['date'] ?? date('Y-m-d'),
        'type'            => htmlspecialchars($_POST['type'] ?? 'Maintenance'),
        'serviceProvider' => htmlspecialchars($_POST['serviceProvider'] ?? 'Manual Log'),
        'description'     => htmlspecialchars($_POST['description'] ?? ''),
        'totalCost'       => (float)($_POST['totalCost'] ?? 0),
        'billUrl'         => $billPath, 
        'updatedAt'       => date('c')
    ];

    if (!empty($editingId)) {
        db('PATCH', "maintenance_logs/" . $editingId, $record_data);
        $message = 'Neural log entry synchronized successfully!';
    } else {
        $record_data['createdAt'] = date('c');
        db('POST', "maintenance_logs", $record_data);
        $message = 'Maintenance record registered successfully!';
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database Sync Failed: ' . $e->getMessage()]);
}