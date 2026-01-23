<?php
require_once "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = strtolower(trim($data['email']));
$password = password_hash($data['password'], PASSWORD_BCRYPT);

$users = db('GET', 'users') ?? [];

foreach ($users as $u) {
    if ($u['email'] === $email) {
        echo json_encode(["success" => false, "message" => "Email already exists"]);
        exit;
    }
}

db('POST', 'users', [
    "email" => $email,
    "password" => $password,
    "created_at" => date("Y-m-d H:i:s")
]);

echo json_encode(["success" => true]);
