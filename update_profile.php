<?php
require_once 'db.php';
session_start();

// Ensure the response is always JSON
header('Content-Type: application/json');

// 1. Authentication Check
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

// 2. Data Extraction and Sanitization
$displayName = trim($data['displayName'] ?? '');
$phone       = trim($data['phone'] ?? '');
$photoURL    = $data['photoURL'] ?? '';
$currentPass = $data['currentPassword'] ?? '';
$newPass     = $data['newPassword'] ?? '';

// CAPTURE NOTIFICATION PREFERENCE
// We use null coalescing to ensure we only update it if it was actually sent
$notificationsEnabled = $data['notificationsEnabled'] ?? null;

// --- PHONE CLEANING ---
$phone = preg_replace('/[^\d+]/', '', $phone);

// 3. Validation
if (empty($displayName) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Name and Phone Number are required.']);
    exit;
}

if (!preg_match('/^\+\d{10,15}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone format. Please use + followed by country code.']);
    exit;
}

try {
    // 4. Fetch Current User Record
    $users = db('GET', 'users') ?? [];
    $user = $users[$userId] ?? null;

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User profile not found.']);
        exit;
    }

    // 5. Prepare the Update Payload
    $updates = [
        'displayName' => $displayName,
        'phone'       => $phone,
        'photoURL'    => $photoURL
    ];

    // ONLY ADD TO UPDATES IF PROVIDED (avoids overwriting with null)
    if ($notificationsEnabled !== null) {
        $updates['notificationsEnabled'] = (bool)$notificationsEnabled;
    }

    // 6. Handle Secure Password Update
    if (!empty($newPass)) {
        if (password_verify($currentPass, $user['password'])) {
            $updates['password'] = password_hash($newPass, PASSWORD_BCRYPT);
        } else {
            echo json_encode(['success' => false, 'message' => 'Current password verification failed.']);
            exit;
        }
    }

    // 7. Execute Update via PATCH
    // 
    $result = db('PATCH', "users/$userId", $updates);

    if ($result) {
        // 8. Update Session Variables
        $_SESSION['displayName'] = $displayName;
        $_SESSION['phone'] = $phone;
        
        // Optional: Update session photo if you use it in the sidebar
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