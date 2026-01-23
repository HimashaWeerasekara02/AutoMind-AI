<?php

header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$docId = $_GET['id'] ?? null;

try {
    // --- DELETE DOCUMENT ---
    if ($method === 'DELETE') {
        if (!$docId) {
            throw new Exception('Missing Document ID');
        }

        $doc = db('GET', "documents/$docId");
        if ($doc && isset($doc['fileUrl'])) {
            if (file_exists($doc['fileUrl'])) {
                unlink($doc['fileUrl']); 
            }
        }

        db('DELETE', "documents/$docId");
        echo json_encode(['success' => true, 'message' => 'Document deleted']);
    }

    // --- UPDATE DOCUMENT (Title, Expiry, AND/OR New File) ---
    // Note: Use POST for updates if you are sending files via FormData
    else if ($method === 'POST' || $method === 'PATCH') {
        if (!$docId) throw new Exception('Missing Document ID');

        // Fetch existing data to handle file replacement
        $existingDoc = db('GET', "documents/$docId");
        if (!$existingDoc) throw new Exception('Document not found');

        $updateData = [];
        
        // Handle Text Data (From $_POST if using FormData, or file_get_contents if JSON)
        $title = $_POST['title'] ?? null;
        $expiryDate = $_POST['expiryDate'] ?? null;

        if ($title) $updateData['title'] = trim($title);
        if ($expiryDate) $updateData['expiryDate'] = $expiryDate;

        // --- Handle File Replacement ---
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/glovebox/';
            $fileExtension = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
            $newFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
            $newPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['document']['tmp_name'], $newPath)) {
                // Delete old file if a new one is successfully uploaded
                if (isset($existingDoc['fileUrl']) && file_exists($existingDoc['fileUrl'])) {
                    unlink($existingDoc['fileUrl']);
                }
                $updateData['fileUrl'] = $newPath;
                $updateData['type'] = $_FILES['document']['type'];
            }
        }

        if (empty($updateData)) {
            throw new Exception("No changes provided");
        }

        db('PATCH', "documents/$docId", $updateData);
        echo json_encode(['success' => true, 'message' => 'Document updated successfully', 'data' => $updateData]);
    }

    // --- GET DOCUMENT(S) ---
    else if ($method === 'GET') {
        if (!$docId) {
            $docs = db('GET', 'documents');
            echo json_encode($docs ?: []);
        } else {
            $doc = db('GET', "documents/$docId");
            if (!$doc) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Document not found']);
            } else {
                echo json_encode($doc);
            }
        }
    }

    else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}