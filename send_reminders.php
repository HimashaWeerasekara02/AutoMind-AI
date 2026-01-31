<?php
require_once 'db.php';

// This script should be set to run every morning at 8:00 AM via CPanel/Crontab
// Example: 0 8 * * * /usr/bin/php /path/to/send_reminders.php

$users = db('GET', 'users') ?? [];
$vehicles = db('GET', 'vehicles') ?? [];

$today = new DateTime();

foreach ($vehicles as $vId => $v) {
    $ownerId = $v['userId'];
    $user = $users[$ownerId] ?? null;

    // SKIP if user doesn't exist or has disabled notifications
    if (!$user || !($user['notificationsEnabled'] ?? false)) {
        continue;
    }

    $ownerPhone = $user['phone']; // Format: +94771234567
    
    // Check various expiry dates (Insurance, License, etc.)
    checkAndNotify($v['insurance_expiry'], "Insurance", $v['plateNumber'], $ownerPhone);
}

function checkAndNotify($dateString, $type, $plate, $phone) {
    if (!$dateString) return;

    $expiryDate = new DateTime($dateString);
    $today = new DateTime();
    $diff = $today->diff($expiryDate)->days;

    // Send reminder if exactly 7 days before expiry
    if ($diff == 7) {
        $msg = "AutoMind AI Alert: Your $type for vehicle $plate expires in 7 days ($dateString). Please renew soon!";
        sendWhatsApp($phone, $msg);
    }
}

function sendWhatsApp($to, $message) {
    // Example using Twilio
    $sid    = "YOUR_TWILIO_SID";
    $token  = "YOUR_TWILIO_AUTH_TOKEN";
    $from   = "whatsapp:+14155238886"; // Twilio Sandbox Number

    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";

    $data = [
        'From' => $from,
        'To'   => "whatsapp:$to",
        'Body' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    curl_close($ch);
}