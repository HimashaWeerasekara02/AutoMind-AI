<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in.']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received.']);
    exit;
}

$displayName = trim($data['displayName'] ?? '');
$phone       = trim($data['phone'] ?? '');
$photoURL    = $data['photoURL'] ?? '';
$currentPass = $data['currentPassword'] ?? '';
$newPass     = $data['newPassword'] ?? '';


$notificationsEnabled = $data['notificationsEnabled'] ?? null;


$phone = preg_replace('/[^\d+]/', '', $phone);


if (empty($displayName) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Name and Phone Number are required.']);
    exit;
}

if (!preg_match('/^\+\d{10,15}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone format. Please use + followed by country code.']);
    exit;
}

try {

    $users = db('GET', 'users') ?? [];
    $user = $users[$userId] ?? null;

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User profile not found.']);
        exit;
    }


    $updates = [
        'displayName' => $displayName,
        'phone'       => $phone,
        'photoURL'    => $photoURL
    ];

    
    if ($notificationsEnabled !== null) {
        $updates['notificationsEnabled'] = (bool)$notificationsEnabled;
    }


    if (!empty($newPass)) {
        if (password_verify($currentPass, $user['password'])) {
            $updates['password'] = password_hash($newPass, PASSWORD_BCRYPT);
        } else {
            echo json_encode(['success' => false, 'message' => 'Current password verification failed.']);
            exit;
        }
    }

   
    $result = db('PATCH', "users/$userId", $updates);

    if ($result) {
      
        $_SESSION['displayName'] = $displayName;
        $_SESSION['phone'] = $phone;
        
       
        if (!empty($photoURL)) {
            $_SESSION['photoURL'] = $photoURL;
        }

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save changes to the database.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}