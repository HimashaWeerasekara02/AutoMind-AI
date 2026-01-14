<?php

header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true);

$docId = $_GET['id'] ?? null;

try {
    // --- DELETE DOCUMENT ---
    if ($method === 'DELETE') {
        if (!$docId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing Document ID']);
            exit;
        }

        // 1. Get file path from Firebase first to delete the actual file
        $doc = db('GET', "documents/$docId");
        
        if ($doc && isset($doc['file_path'])) {
            $filePath = $doc['file_path'];
            
            // Security Check: Ensure we only delete files inside our authorized directory
            if (strpos($filePath, 'uploads/glovebox/') === 0 && file_exists($filePath)) {
                unlink($filePath); // Remove physical file from server
            }
        }

        // 2. Delete the record from Firebase
        db('DELETE', "documents/$docId");

        echo json_encode(['success' => true, 'message' => 'Document and file deleted successfully']);
    }

    // --- EDIT DOCUMENT (Update Name & Expiry) ---
    else if ($method === 'PATCH') {
        if (!$docId || !$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid request data']);
            exit;
        }

        $updateData = [];
        
        // Allow updating the friendly name
        if (isset($input['file_name'])) {
            $updateData['file_name'] = trim($input['file_name']);
        }

        // Allow updating the expiry date
        if (isset($input['expiry_date'])) {
            $updateData['expiry_date'] = $input['expiry_date'];
        }

        if (empty($updateData)) {
            throw new Exception("No valid fields (file_name or expiry_date) provided for update");
        }

        // Update metadata in Firebase
        db('PATCH', "documents/$docId", $updateData);

        echo json_encode(['success' => true, 'message' => 'Document updated successfully']);
    }

    // --- GET DOCUMENT(S) ---
    else if ($method === 'GET') {
        if (!$docId) {
            // Fetch all documents for all vehicles (filtering is done on the frontend)
            $docs = db('GET', 'documents');
            echo json_encode($docs ?: []);
        } else {
            // Fetch single document details
            $doc = db('GET', "documents/$docId");
            if (!$doc) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Document not found']);
            } else {
                echo json_encode($doc);
            }
        }
    }

    // --- METHOD NOT ALLOWED ---
    else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}