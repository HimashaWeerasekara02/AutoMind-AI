<?php

header('Content-Type: application/json');
require_once 'db.php';
session_start();

ini_set('display_errors', 0);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$userId = $_SESSION['user_id'];
$vehicleId = $_POST['vehicleId'] ?? '';
$vehicleMake = $_POST['make'] ?? '';
$vehicleModel = $_POST['model'] ?? '';

if (!$vehicleId || !isset($_FILES['billImage'])) {
    echo json_encode(['success' => false, 'error' => 'Vehicle ID and Bill Image are required.']);
    exit;
}


function extractTextSimple($imagePath) {
    try {
        return ocrWithOCRSpace($imagePath);
    } catch (Exception $e) {
    }
    
    throw new Exception("OCR processing failed. Please try a clearer image with better lighting.");
}

function ocrWithOCRSpace($imagePath) {
    $apiKey = "K87899142388957"; 
    $apiUrl = "https://api.ocr.space/parse/image";
    
    $imageData = base64_encode(file_get_contents($imagePath));
    
    $postData = http_build_query([
        'apikey' => $apiKey,
        'base64Image' => 'data:image/jpeg;base64,' . $imageData,
        'language' => 'eng',
        'OCREngine' => '2'
    ]);
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("OCR request failed");
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['ParsedResults'][0]['ParsedText'])) {
        return $result['ParsedResults'][0]['ParsedText'];
    }
    
    throw new Exception("Could not parse image");
}


function parsePartsFromText($text) {
    $lines = explode("\n", $text);
    $parts = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (strlen($line) < 5) continue;
        
        if (preg_match('/(^total|^subtotal|^tax|^discount|^grand|^balance|^payment|^invoice|^bill|^date|^receipt)/i', $line)) {
            continue;
        }
        
      
        if (preg_match('/(.+?)\s+(?:Rs\.?|LKR|රු)?\s*([\d,]+(?:\.\d{1,2})?)\s*$/i', $line, $matches)) {
            $partName = trim($matches[1]);
            $partName = preg_replace('/[^\w\s\-\(\)\/]/u', '', $partName); // Clean special chars
            $price = (float)str_replace(',', '', $matches[2]);
            
            if (strlen($partName) > 3 && $price >= 10 && $price <= 500000) {
                $parts[] = [
                    'part' => $partName,
                    'price' => $price
                ];
            }
        }
    }
    
    return $parts;
}

try {
    $uploadDir = 'uploads/bills/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileExt = pathinfo($_FILES['billImage']['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . uniqid() . '.' . $fileExt;
    $filePath = $uploadDir . $fileName;
    
    if (!move_uploaded_file($_FILES['billImage']['tmp_name'], $filePath)) {
        throw new Exception("Failed to save image.");
    }

    $extractedText = extractTextSimple($filePath);
    
    if (empty($extractedText)) {
        throw new Exception("No text found in image. Ensure the bill is clear and readable.");
    }

    $extractedParts = parsePartsFromText($extractedText);
    
    if (empty($extractedParts)) {
        error_log("OCR Text: " . $extractedText);
        throw new Exception("Could not find parts with prices. Ensure bill has clear itemized parts.");
    }

    $dataset = db('GET', 'car_parts_dataset') ?? [];
    $analysisResults = [];
    $totalPartsCost = 0;

    foreach ($extractedParts as $item) {
        $partName = $item['part'];
        $billedPrice = $item['price'];
        $totalPartsCost += $billedPrice;
        
        $marketPrice = null;
        if ($dataset && is_array($dataset)) {
            foreach ($dataset as $data) {
                if (isset($data['part_name'], $data['make']) && 
                    stripos($partName, $data['part_name']) !== false && 
                    strcasecmp($data['make'], $vehicleMake) == 0) {
                    $marketPrice = $data['price_lkr'] ?? null;
                    break;
                }
            }
        }

        $status = "Fair";
        $diff = 0;
        if ($marketPrice > 0) {
            $diff = (($billedPrice - $marketPrice) / $marketPrice) * 100;
            $status = ($diff > 15) ? "Overcharged" : (($diff < -15) ? "Good Deal" : "Fair");
        } else {
            $status = "No Market Data";
        }

        $analysisResults[] = [
            "part" => $partName,
            "billed" => $billedPrice,
            "market" => $marketPrice,
            "status" => $status,
            "diff_percent" => round($diff, 1)
        ];
    }

    $maintenanceData = [
        "userId" => $userId,
        "vehicleId" => $vehicleId,
        "date" => date('Y-m-d'),
        "description" => "Bill Analysis: " . count($extractedParts) . " parts",
        "totalCost" => $totalPartsCost, 
        "billImage" => $filePath,
        "type" => "Repair",
        "aiAnalysis" => $analysisResults,
        "createdAt" => date('c')
    ];
    
    db('POST', 'maintenance_logs', $maintenanceData);

    echo json_encode([
        'success' => true,
        'analysis' => $analysisResults,
        'total' => $totalPartsCost,
        'parts_count' => count($extractedParts),
        'image' => $filePath
    ]);

} catch (Exception $e) {
    error_log("Bill Analysis Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}