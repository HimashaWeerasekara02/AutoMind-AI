<?php

header('Content-Type: application/json');
require_once 'db.php';
session_start();

ini_set('display_errors', 0);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Session expired. Please log in again.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? ''; 
$vehicleId = $_REQUEST['id'] ?? $_POST['id'] ?? null;

try {
    if ($action === 'delete' && $vehicleId) {
        db('DELETE', "vehicles/$vehicleId");
        echo json_encode(['success' => true]);
        exit;
    }

    if ($method === 'POST') {
        $data = [
            'userId'   => $_SESSION['user_id'],
            'nickname' => trim($_POST['nickname'] ?? 'Unnamed Unit'),
            'make'     => trim($_POST['make'] ?? 'Unknown'),
            'model'    => trim($_POST['model'] ?? 'Unknown'),
            'year'     => (int)($_POST['year'] ?? 0),
            'plate'    => trim($_POST['plate'] ?? ''),
            'odometer' => (int)($_POST['odometer'] ?? 0),
            'fuel'     => $_POST['fuel'] ?? 'Petrol',
            'updatedAt'=> date('c')
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/garage/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $fileExt;
            $target = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $data['imageUrl'] = $target;
            }
        }

        if ($action === 'update' && $vehicleId) {
            db('PATCH', "vehicles/$vehicleId", $data);
            echo json_encode(['success' => true, 'message' => 'Profile updated.']);
        } else {
            $data['createdAt'] = date('c');
            $result = db('POST', 'vehicles', $data);
            echo json_encode(['success' => true, 'id' => $result['name'] ?? null]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Undefined operation code.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}