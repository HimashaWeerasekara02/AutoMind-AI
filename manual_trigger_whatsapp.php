<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

// 1. Auth Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please log in again.']);
    exit;
}

// 2. Twilio Credentials (Get these from your Twilio Console)
$sid    = "ACxxxxxxxxxxxxxxxxxxxxxxxxxxxx"; 
$token  = "your_auth_token_here";
$from   = "whatsapp:+14155238886"; // The Twilio Sandbox Number

// 3. Get User Phone
$userId = $_SESSION['user_id'];
$users = db('GET', 'users') ?? [];
$userData = $users[$userId] ?? [];
$phone = $userData['phone'] ?? null;

if (!$phone) {
    echo json_encode(['success' => false, 'error' => 'No phone number found. Please update your profile settings first.']);
    exit;
}

// 4. Get Data from JavaScript
$input = json_decode(file_get_contents('php://input'), true);
$title   = $input['title'] ?? 'Document';
$expiry  = $input['expiry'] ?? 'N/A';
$vehicle = $input['vehicle'] ?? 'Vehicle';

// 5. Format the WhatsApp Message
$messageBody = "🔔 *AutoMind AI Reminder*\n\nYour document *{$title}* for vehicle *{$vehicle}* is expiring on *{$expiry}*.\n\nPlease log in to update your Glovebox.";

// 6. Send via Twilio
$url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
$data = [
    'To'   => "whatsapp:" . $phone,
    'From' => $from,
    'Body' => $messageBody
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

if (isset($result['sid'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Twilio API Error']);
}