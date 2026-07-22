<?php
require_once 'db.php';


$users = db('GET', 'users') ?? [];
$vehicles = db('GET', 'vehicles') ?? [];

$today = new DateTime();

foreach ($vehicles as $vId => $v) {
    $ownerId = $v['userId'];
    $user = $users[$ownerId] ?? null;

    if (!$user || !($user['notificationsEnabled'] ?? false)) {
        continue;
    }

    $ownerPhone = $user['phone']; 
    
    checkAndNotify($v['insurance_expiry'], "Insurance", $v['plateNumber'], $ownerPhone);
}

function checkAndNotify($dateString, $type, $plate, $phone) {
    if (!$dateString) return;

    $expiryDate = new DateTime($dateString);
    $today = new DateTime();
    $diff = $today->diff($expiryDate)->days;

      if ($diff == 7) {
        $msg = "AutoMind AI Alert: Your $type for vehicle $plate expires in 7 days ($dateString). Please renew soon!";
        sendWhatsApp($phone, $msg);
    }
}

function sendWhatsApp($to, $message) {
    $sid    = "YOUR_TWILIO_SID";
    $token  = "YOUR_TWILIO_AUTH_TOKEN";
    $from   = "whatsapp:+14155238886"; 

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