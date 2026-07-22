<?php

require_once 'db.php';

$twilio_sid    = getenv('TWILIO_SID'); 
$twilio_token  = getenv('TWILIO_AUTH_TOKEN');
$twilio_number = getenv('TWILIO_PHONE_NUMBER'); 

if (!$twilio_sid || !$twilio_token || !$twilio_number) {
    die("ERROR: Twilio credentials not found in environment variables." . PHP_EOL);
}

$documents = db('GET', 'documents') ?? [];
$users     = db('GET', 'users') ?? [];

$today = new DateTime();
$remindThreshold = 7; 
$notificationsSent = 0;

echo "--- AutoMind AI Debug Engine (7-Day Window Mode) ---" . PHP_EOL;
echo "Current System Date: " . $today->format('Y-m-d') . PHP_EOL;
echo "Total Documents Found in DB: " . count($documents) . PHP_EOL . PHP_EOL;

foreach ($documents as $docId => $doc) {
    $docTitle = $doc['title'] ?? 'N/A';
    echo "Checking Doc ID: $docId | Title: $docTitle" . PHP_EOL;

    if (!preg_match('/License|Insurance/i', $docTitle)) {
        echo " -> Skipped: Title does not contain 'License' or 'Insurance'" . PHP_EOL;
        continue;
    }

    if (empty($doc['expiryDate'])) {
        echo " -> Skipped: No expiryDate field found" . PHP_EOL;
        continue;
    }


    try {
        $expiry = new DateTime($doc['expiryDate']);
        $interval = $today->diff($expiry);
        $daysLeft = (int)$interval->format('%r%a');

        echo " -> Days Remaining: $daysLeft" . PHP_EOL;

   
        if ($daysLeft <= $remindThreshold && $daysLeft >= 0) {
            $userId = $doc['userId'] ?? '';
            $user = $users[$userId] ?? null;
            
            if (!$user) {
                echo " -> Skipped: User ID ($userId) not found in 'users' node." . PHP_EOL;
                continue;
            }

            if (empty($user['phone'])) {
                echo " -> Skipped: User " . ($user['displayName'] ?? 'Unknown') . " has no phone number." . PHP_EOL;
                continue;
            }

            $phoneNumber = formatPhoneNumber($user['phone']);
            $userName = $user['displayName'] ?? 'Member';

            $dayText = ($daysLeft === 0) ? "TODAY" : "in $daysLeft days";
            $message = "AutoMind AI Alert: Hello {$userName}, your {$docTitle} expires {$dayText} ({$doc['expiryDate']})! Please ensure it is renewed.";

            echo " -> MATCH FOUND! (Condition: $daysLeft <= 7). Attempting SMS to {$phoneNumber}..." . PHP_EOL;
            $status = sendSMS($phoneNumber, $message, $twilio_sid, $twilio_token, $twilio_number);
            echo " -> Twilio API Result: {$status}" . PHP_EOL;
            $notificationsSent++;
        } else {
            echo " -> Skipped: Not within the 0-7 day window." . PHP_EOL;
        }
    } catch (Exception $e) {
        echo " -> Error parsing date for this doc: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "Check Finished. Total Reminders Sent: {$notificationsSent}" . PHP_EOL;

function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (empty($phone)) return null;
    if (substr($phone, 0, 1) === '0') return '+94' . substr($phone, 1);
    return (substr($phone, 0, 1) !== '+') ? '+' . $phone : $phone;
}

function sendSMS($to, $message, $sid, $token, $from) {
    if (strpos($sid, 'ACXXX') !== false) return "Error: Invalid Twilio SID. Edit run_reminders.bat";

    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    $data = ['To' => $to, 'From' => $from, 'Body' => $message];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    return isset($result['sid']) ? "Success (SID: " . $result['sid'] . ")" : "Error: " . ($result['message'] ?? 'Unknown');
}