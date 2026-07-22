<?php

require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['subject']) || empty($data['message'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

try {
    $ticketData = [
        'userId'    => $_SESSION['user_id'],
        'userEmail' => $_SESSION['email'] ?? 'N/A',
        'subject'   => htmlspecialchars($data['subject']),
        'category'  => htmlspecialchars($data['category'] ?? 'General'),
        'message'   => htmlspecialchars($data['message']),
        'status'    => 'pending',
        'createdAt' => time() * 1000 
    ];

    $result = db('POST', 'tickets', $ticketData);

    if ($result && isset($result['name'])) {
        echo json_encode(['success' => true, 'id' => $result['name']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to write to Firebase']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}