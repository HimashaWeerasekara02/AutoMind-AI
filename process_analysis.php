<?php
header('Content-Type: application/json');
require_once 'db.php'; 
session_start();

// Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$userId = $_SESSION['user_id'];

/**
 * Replace this with your actual environment variable or secure key storage
 * Note: For production, do not hardcode keys.
 */
define('GEMINI_API_KEY', 'AIzaSyBUgfpLxbJuYbn5Bo63wrnzmrSw1HLOEnk');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bill'])) {
    try {
        // 1. File Validation & Preparation
        if (!isset($_FILES['bill']['tmp_name']) || empty($_FILES['bill']['tmp_name'])) {
            throw new Exception("No file uploaded.");
        }

        $fileTmpPath = $_FILES['bill']['tmp_name'];
        $imageData = base64_encode(file_get_contents($fileTmpPath));
        
        // Determine Mime Type (Important for Gemini)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileTmpPath);

        // 2. AI Prompt Engineering
        // We instruct the AI to return strictly valid JSON for easy parsing.
        $prompt = "You are an expert automotive auditor. Analyze this mechanic bill. 
                   Extract the merchant name, total cost, vehicle details, and individual line items. 
                   Return ONLY a JSON object with this structure: 
                   {
                     \"merchantName\": \"string\", 
                     \"extractedTotal\": number, 
                     \"vehicleInfo\": \"string\", 
                     \"description\": \"Brief summary of work done\", 
                     \"lineItems\": [{\"itemName\": \"string\", \"quantity\": number, \"unitPrice\": number, \"totalPrice\": number}]
                   }";

        $payload = [
            "contents" => [[
                "parts" => [
                    ["text" => $prompt],
                    ["inline_data" => ["mime_type" => $mimeType, "data" => $imageData]]
                ]
            ]],
            "generationConfig" => [
                "response_mime_type" => "application/json",
                "temperature" => 0.2 // Lower temperature for more accurate data extraction
            ]
        ];

        // 3. Send Request to Gemini 2.0 Flash
        $api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . GEMINI_API_KEY;
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $errorDetail = json_decode($response, true);
            $msg = $errorDetail['error']['message'] ?? "Unknown API Error";
            throw new Exception("AI Analysis Failed: $msg");
        }

        // 4. Parse & Clean AI Response
        $result = json_decode($response, true);
        $rawJson = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $aiData = json_decode(trim($rawJson), true);

        if (!$aiData) {
            throw new Exception("AI failed to interpret the document structure.");
        }

        // 5. Database Logic: Save Main Scan Header
        $scanRecord = [
            "userId" => $userId,
            "title" => $aiData['merchantName'] ?? 'Unnamed Merchant',
            "status" => "Analyzed",
            "extractedTotal" => (float)($aiData['extractedTotal'] ?? 0),
            "vehicleInfo" => $aiData['vehicleInfo'] ?? 'Unknown Vehicle',
            "description" => $aiData['description'] ?? 'No summary available.',
            "insights" => "Audit completed via AutoMind AI.",
            "createdAt" => date('c')
        ];
        
        // Using your db() helper function
        $scanResponse = db('POST', 'diagnostics_history', $scanRecord);
        $scanId = $scanResponse['name'] ?? null;

        // 6. Database Logic: Price Auditing Line Items
        if ($scanId && isset($aiData['lineItems']) && is_array($aiData['lineItems'])) {
            // Fetch benchmark prices for comparison
            $marketPrices = db('GET', 'market_benchmark_prices'); 

            foreach ($aiData['lineItems'] as $item) {
                $assessment = "No Benchmark";
                $itemName = strtolower($item['itemName'] ?? '');

                if ($marketPrices && is_array($marketPrices)) {
                    foreach ($marketPrices as $m) {
                        if (isset($m['keyword']) && str_contains($itemName, strtolower($m['keyword']))) {
                            $price = (float)($item['unitPrice'] ?? 0);
                            if ($price > (float)$m['maxPrice']) $assessment = "High Price";
                            else if ($price < (float)$m['minPrice']) $assessment = "Competitive";
                            else $assessment = "Fair Market Value";
                            break;
                        }
                    }
                }

                // Save detailed item breakdown
                db('POST', 'diagnostic_items', [
                    "scanId" => $scanId,
                    "itemName" => $item['itemName'] ?? 'Labor/Unknown',
                    "quantity" => $item['quantity'] ?? 1,
                    "unitPrice" => $item['unitPrice'] ?? 0,
                    "totalPrice" => $item['totalPrice'] ?? 0,
                    "auditResult" => $assessment
                ]);
            }
        }

        // 7. Final Output to Frontend
        echo json_encode([
            'success' => true, 
            'data' => [
                'title' => $scanRecord['title'],
                'status' => $scanRecord['status'],
                'createdAt' => $scanRecord['createdAt'],
                'description' => $scanRecord['description'],
                'insights' => $scanRecord['insights']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}