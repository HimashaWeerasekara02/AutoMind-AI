<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

// Get the POST data from the fetch call
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "message" => "Please fill in all fields"]);
    exit;
}

$email = strtolower(trim($data['email']));
$password = $data['password'];

try {
    // 1. Fetch all users from your Firebase 'users' node
    $users = db('GET', 'users');

    if (!$users) {
        echo json_encode(["success" => false, "message" => "No users found in database"]);
        exit;
    }

    $foundUser = null;
    $foundId = null;

    // 2. Loop through users to find a match for the email
    foreach ($users as $id => $user) {
        if (isset($user['email']) && $user['email'] === $email) {
            $foundUser = $user;
            $foundId = $id;
            break;
        }
    }

    // 3. Verify the password
    if ($foundUser && password_verify($password, $foundUser['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $foundId;
        $_SESSION['email'] = $foundUser['email'];
        
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}