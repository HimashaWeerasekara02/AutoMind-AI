<?php
header('Content-Type: application/json');
require_once 'db.php'; 

define('GEMINI_API_KEY', 'AIzaSyBUgfpLxbJuYbn5Bo63wrnzmrSw1HLOEnk');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bill'])) {
    try {
        if (!isset($_FILES['bill']['tmp_name']) || empty($_FILES['bill']['tmp_name'])) {
            throw new Exception("No file uploaded.");
        }

        $imageData = base64_encode(file_get_contents($_FILES['bill']['tmp_name']));
        $mimeType = $_FILES['bill']['type'];

        $prompt = "Analyze this mechanic bill image. Return ONLY a raw JSON object: 
                   {
                     'merchantName': 'string',
                     'extractedTotal': float,
                     'vehicleInfo': 'string',
                     'description': 'short summary',
                     'lineItems': [
                        {'itemName': 'string', 'quantity': 1, 'unitPrice': 0.00, 'totalPrice': 0.00}
                     ]
                   }";

        $payload = [
            "contents" => [[
                "parts" => [
                    ["text" => $prompt],
                    ["inline_data" => ["mime_type" => $mimeType, "data" => $imageData]]
                ]
            ]]
        ];

        // UPDATED: Using the current 2026 stable model and endpoint
        $api_url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY;
        
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
            $msg = $errorDetail['error']['message'] ?? "Unknown Error";
            throw new Exception("Gemini API Error (HTTP $httpCode): $msg");
        }

        $result = json_decode($response, true);
        $rawText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Clean JSON from Markdown wrappers
        $cleanJson = $rawText;
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $rawText, $matches)) {
            $cleanJson = $matches[1];
        }

        $aiData = json_decode(trim($cleanJson), true);
        if (!$aiData) throw new Exception("AI parsing failed. Response was not valid JSON.");

        // --- Database Logic ---
        $scanRecord = [
            "merchantName" => $aiData['merchantName'] ?? 'Unknown Shop',
            "extractedTotal" => $aiData['extractedTotal'] ?? 0,
            "status" => "Processed",
            "vehicleInfo" => $aiData['vehicleInfo'] ?? 'N/A',
            "description" => $aiData['description'] ?? '',
            "createdAt" => date('c')
        ];
        
        $scanResponse = db('POST', 'BillScans', $scanRecord);
        $scanId = $scanResponse['name'] ?? null;

        if ($scanId && isset($aiData['lineItems'])) {
            $marketPrices = db('GET', 'MarketPrices'); 

            foreach ($aiData['lineItems'] as $item) {
                $assessment = "No Market Data";
                $searchKey = strtolower($item['itemName']);

                if ($marketPrices) {
                    foreach ($marketPrices as $m) {
                        if (isset($m['searchKey']) && strpos($searchKey, strtolower($m['searchKey'])) !== false) {
                            $price = (float)$item['unitPrice'];
                            if ($price > (float)$m['oemPriceMax']) $assessment = "Overcharged";
                            else if ($price < (float)$m['oemPriceMin']) $assessment = "Good Deal";
                            else $assessment = "Fair Price";
                            break;
                        }
                    }
                }

                db('POST', 'BillLineItems', [
                    "scanId" => $scanId,
                    "itemName" => $item['itemName'],
                    "quantity" => $item['quantity'],
                    "unitPrice" => $item['unitPrice'],
                    "totalPrice" => $item['totalPrice'],
                    "priceAssessment" => $assessment
                ]);
            }
        }

        echo json_encode(['success' => true, 'data' => $aiData]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}