<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "message" => "Please fill in all fields"]);
    exit;
}

$email = strtolower(trim($data['email']));
$password = $data['password'];

try {
    $users = db('GET', 'users');

    if (!$users) {
        echo json_encode(["success" => false, "message" => "No users found in database"]);
        exit;
    }

    $foundUser = null;
    $foundId = null;


    foreach ($users as $id => $user) {
        if (isset($user['email']) && $user['email'] === $email) {
            $foundUser = $user;
            $foundId = $id;
            break;
        }
    }


    if ($foundUser && password_verify($password, $foundUser['password'])) {

        $_SESSION['user_id'] = $foundId;
        $_SESSION['email'] = $foundUser['email'];
        
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}