<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

define('GEMINI_API_KEY', 'AIzaSyBUgfpLxbJuYbn5Bo63wrnzmrSw1HLOEnk');

$vehicleId = $_GET['vehicleId'] ?? '';
if (empty($vehicleId)) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID is required.']);
    exit;
}

// 1. Get service history from Firebase
$history = db('GET', "service_logs");
$vehicleLogs = [];

if ($history && is_array($history)) {
    foreach ($history as $id => $data) {
        if (isset($data['vehicleId']) && $data['vehicleId'] === $vehicleId) {
            $vehicleLogs[] = $data;
        }
    }
}

// 2. Get fuel history
$fuelData = db('GET', "fuel_logs/$vehicleId");
$fuelSummary = [];
if ($fuelData && is_array($fuelData)) {
    foreach ($fuelData as $log) {
        $fuelSummary[] = [
            'date' => $log['date'] ?? '',
            'odometer' => $log['odometer'] ?? 0,
            'quantity' => $log['quantity'] ?? 0
        ];
    }
}

$historyString = json_encode(['service_logs' => $vehicleLogs, 'fuel_logs' => $fuelSummary]);

// 3. Ask Gemini AI to analyze and predict
$prompt = "You are an expert automotive maintenance advisor. Here is a vehicle's service and fuel history in JSON format:

$historyString

Based on these records, analyze the patterns and predict the next 2-3 likely maintenance issues this vehicle will face. Consider mileage intervals, time since last service, and common wear patterns.

Return ONLY a JSON object with this structure:
{
  \"prediction\": \"A professional 2-3 sentence summary of predicted upcoming maintenance needs.\",
  \"urgentItems\": [\"item1\", \"item2\"],
  \"confidence\": \"High/Medium/Low\"
}";

$payload = [
    "contents" => [[
        "parts" => [["text" => $prompt]]
    ]],
    "generationConfig" => [
        "response_mime_type" => "application/json",
        "temperature" => 0.3
    ]
];

$api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=" . GEMINI_API_KEY;

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'prediction' => 'AI service temporarily unavailable.']);
    exit;
}

$result = json_decode($response, true);
$rawJson = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
$aiData = json_decode(trim($rawJson), true);

if ($aiData) {
    echo json_encode(['success' => true, 'data' => $aiData]);
} else {
    echo json_encode(['success' => true, 'data' => [
        'prediction' => 'Insufficient service data to generate a reliable prediction. Log more maintenance records for better analysis.',
        'urgentItems' => [],
        'confidence' => 'Low'
    ]]);
}