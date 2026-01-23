<?php
session_start();
require_once "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = strtolower(trim($data['email']));
$password = $data['password'];

$users = db('GET', 'users') ?? [];

foreach ($users as $id => $user) {
    if ($user['email'] === $email && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $id;
        $_SESSION['email'] = $email;
        echo json_encode(["success" => true]);
        exit;
    }
}

echo json_encode(["success" => false, "message" => "Invalid credentials"]);