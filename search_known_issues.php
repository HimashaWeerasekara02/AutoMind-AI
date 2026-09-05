<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$query = $_GET['q'] ?? '';
if (empty(trim($query))) {
    echo json_encode(['success' => false, 'message' => 'Search query is required.']);
    exit;
}

define('GEMINI_API_KEY', 'AIzaSyBUgfpLxbJuYbn5Bo63wrnzmrSw1HLOEnk');

$prompt = "You are an expert automotive mechanic advisor. A vehicle owner is searching for common known issues related to: \"$query\".

Return ONLY a JSON array of 3-4 common issues. Each object must have these exact keys:
- \"title\": Short issue name (e.g., \"Transmission Slipping\")
- \"description\": 2-3 sentence explanation of the issue, causes, and symptoms.
- \"severity\": One of \"High\", \"Medium\", or \"Low\"
- \"estimatedCost\": Estimated repair cost range in LKR (e.g., \"LKR 15,000 - 45,000\")

Return ONLY the JSON array, no markdown, no extra text.";

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
    $errorDetail = json_decode($response, true);
    $msg = $errorDetail['error']['message'] ?? "AI API Error (HTTP $httpCode)";
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$result = json_decode($response, true);
$rawJson = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
$issues = json_decode(trim($rawJson), true);

if (!$issues || !is_array($issues)) {
    echo json_encode(['success' => false, 'message' => 'AI could not process this query.']);
    exit;
}

echo json_encode(['success' => true, 'issues' => $issues]);
