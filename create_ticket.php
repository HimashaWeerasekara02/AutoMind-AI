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
    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, subject, category, message, status) VALUES (?, ?, ?, ?, 'pending')");
    $result = $stmt->execute([
        $_SESSION['user_id'],
        htmlspecialchars($data['subject']),
        htmlspecialchars($data['category']),
        htmlspecialchars($data['message'])
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>