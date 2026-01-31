<?php
require_once 'db.php';

// 1. Twilio API Credentials
$sid    = "YOUR_TWILIO_SID"; 
$token  = "YOUR_TWILIO_AUTH_TOKEN";
$from   = "whatsapp:+14155238886"; 

// 2. Fetch Data from Firebase
$documents = db('GET', 'documents') ?? [];
$users     = db('GET', 'users') ?? [];

$today = new DateTime();
$targetDays = 7; // We want to remind them 1 week before

foreach ($documents as $id => $doc) {
    // Skip if no expiry date or no owner
    if (empty($doc['expiryDate']) || empty($doc['userId'])) continue;

    $expiry = new DateTime($doc['expiryDate']);
    $diff = $today->diff($expiry);
    $daysLeft = (int)$diff->format('%r%a');

    // 3. If expiry is exactly 7 days away, send message
    if ($daysLeft === $targetDays) {
        $ownerId = $doc['userId'];
        $user = $users[$ownerId] ?? null;

        if ($user && !empty($user['phone'])) {
            sendWhatsApp(
                $user['phone'], 
                "Hello {$user['displayName']}, your document '{$doc['title']}' expires in 7 days! Please update your Glovebox.",
                $sid, $token, $from
            );
        }
    }
}

function sendWhatsApp($to, $message, $sid, $token, $from) {
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    $data = [
        'To' => "whatsapp:" . $to,
        'From' => $from,
        'Body' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_exec($ch);
    curl_close($ch);
}
?>