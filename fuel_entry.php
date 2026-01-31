<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

/**
 * AUTO-MIND AI: Fuel Intelligence Entry
 * Processes new fuel logs and updates vehicle telemetry.
 */

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// 2. Validate Required Inputs
$vehicleId = $_POST['vehicleId'] ?? null;
$odometer  = $_POST['odometer'] ?? null;
$quantity  = $_POST['quantity'] ?? null; // Liters
$cost      = $_POST['cost'] ?? null;     // Total Price

if (!$vehicleId || !$odometer || !$quantity || !$cost) {
    echo json_encode(['success' => false, 'message' => 'All telemetry fields are required.']);
    exit;
}

try {
    // 3. Prepare the Log Entry
    $fuel_data = [
        'userId'   => $_SESSION['user_id'],
        'date'     => $_POST['date'] ?? date('Y-m-d'),
        'odometer' => (int)$odometer,
        'quantity' => (float)$quantity,
        'cost'     => (float)$cost,
        'station'  => htmlspecialchars($_POST['station'] ?? 'Generic Station'),
        'fullTank' => isset($_POST['fullTank']) ? true : false,
        'createdAt'=> date('c')
    ];

    // 4. Save to Firebase (Stored under fuel_logs/{vehicleId})
    // This structure makes fetching for a specific car much faster.
    $path = "fuel_logs/$vehicleId";
    $result = db('POST', $path, $fuel_data);

    /**
     * 5. OPTIONAL: Update Vehicle "Last Odometer" 
     * We update the main vehicle node so the dashboard doesn't have to 
     * calculate the current mileage from scratch every time.
     */
    db('PATCH', "vehicles/$vehicleId", [
        'lastOdometer' => (int)$odometer,
        'lastFuelUpdate' => date('c')
    ]);

    echo json_encode([
        'success' => true, 
        'message' => 'Fuel telemetry synchronized successfully!',
        'logId'   => $result['name'] ?? null
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Telemetry Sync Error: ' . $e->getMessage()
    ]);
}