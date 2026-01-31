<?php
require_once 'db.php';

// Twilio Credentials (from your console)
$sid    = "YOUR_TWILIO_ACCOUNT_SID";
$token  = "YOUR_TWILIO_AUTH_TOKEN";
$from   = "whatsapp:+14155238886"; // Your Twilio Sandbox Number

/**
 * Function to send WhatsApp via Twilio API
 */
function sendWhatsAppReminder($to, $message, $sid, $token, $from) {
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    
    // Ensure the number is in E.164 format (e.g., +94771234567)
    $data = [
        'From' => $from,
        'To'   => "whatsapp:" . $number,
        'Body' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// LOGIC: Find documents expiring in exactly 7 days
$targetDate = date('Y-m-d', strtotime('+7 days'));

// Query joining documents, vehicles, and users to get the phone number
$sql = "SELECT d.title, d.expiryDate, v.nickname, u.phone 
        FROM documents d
        JOIN vehicles v ON d.vehicleId = v.id
        JOIN users u ON v.user_id = u.id
        WHERE d.expiryDate = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $targetDate);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userPhone = $row['phone']; // Ensure this includes country code (e.g., +94)
        $docTitle  = $row['title'];
        $carName   = $row['nickname'];
        
        $msg = "🚗 *AutoMind AI Reminder*\n\nYour document *{$docTitle}* for vehicle *{$carName}* is set to expire on {$row['expiryDate']}. Please renew it soon!";
        
        $status = sendWhatsAppReminder($userPhone, $msg, $sid, $token, $from);
        $logs[] = "Sent to $userPhone: " . ($status['sid'] ?? 'Failed');
    }
} else {
    $logs[] = "No documents expiring on $targetDate.";
}

// Output for testing purposes
header('Content-Type: application/json');
echo json_encode($logs);
?>