<?php
// ai_predict_maintenance.php
require_once 'db.php';
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');

// 1. Get history
$history = db('GET', 'service_logs');
$historyString = json_encode($history);

// 2. Ask AI to find patterns
$promptText = "Here is the service history of a car in JSON format: $historyString. 
Based on these records, what are the next 2 likely maintenance issues this car will face? 
Provide a short, professional summary.";

// ... (Use the same CURL logic from Step 2 to call Gemini) ...

// Example Mock Output for this demo:
echo json_encode(['prediction' => "Based on your last oil change and brake inspection, your front brake rotors will likely need resurfacing in 3,000 miles. Also, your battery voltage was slightly low in the last log; consider a replacement before winter."]);