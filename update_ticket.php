<?php

require_once 'db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    } else {
        header("Location: login.php");
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? null;
    $response = $input['response'] ?? '';
    $status = $input['status'] ?? 'resolved';

    if (!$id || !$response) {
        echo json_encode(['success' => false, 'message' => 'Missing resolution data.']);
        exit;
    }

    try {
        $updateData = [
            'adminResponse' => htmlspecialchars($response),
            'status' => $status,
            'resolvedAt' => time() * 1000 
        ];

        db('PATCH', "tickets/$id", $updateData);
        echo json_encode(['success' => true, 'message' => 'Resolution transmitted.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Handler - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #f1f5f9; }
        .glass-card { background: #0f172a; border: 1px solid #1e293b; border-radius: 1.5rem; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-8 min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full glass-card p-10 text-center shadow-2xl">
            <div class="bg-blue-600/10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined text-blue-500 text-4xl">settings_input_component</span>
            </div>
            <h1 class="text-2xl font-black italic uppercase tracking-tighter text-white">System <span class="text-blue-500">Endpoint</span></h1>
            <p class="text-slate-400 text-sm mt-4 leading-relaxed">
                This page acts as a technical gateway for ticket resolutions. It is working correctly in the background.
            </p>
            <div class="mt-8 pt-6 border-t border-white/5">
                <a href="admin_support.php" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-400 font-bold text-xs uppercase tracking-widest transition">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Return to Support Hub
                </a>
            </div>
        </div>
    </main>

</body>
</html>