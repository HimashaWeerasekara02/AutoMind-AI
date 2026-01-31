<?php
header('Content-Type: application/json');
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$vehicle_id = $_GET['vehicleId'] ?? null;
if (!$vehicle_id) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID required']);
    exit;
}

try {
    // 1. Fetch Service Logs (Maintenance & Repairs)
    // Structure: service_logs/{logId} -> contains vehicleId
    $service_data = db('GET', "service_logs") ?: [];
    $total_maint_cost = 0;
    $repair_count = 0;
    $routine_count = 0;
    $last_service_date = null;

    foreach ($service_data as $data) {
        if (isset($data['vehicleId']) && $data['vehicleId'] == $vehicle_id) {
            $cost = (float)($data['totalCost'] ?? 0);
            $total_maint_cost += $cost;

            $type = strtolower($data['type'] ?? '');
            $desc = strtolower($data['description'] ?? '');

            if (strpos($type, 'repair') !== false || strpos($desc, 'fix') !== false) {
                $repair_count++;
            } else {
                $routine_count++;
            }

            if (!$last_service_date || $data['date'] > $last_service_date) {
                $last_service_date = $data['date'];
            }
        }
    }

    // 2. Fetch Fuel Logs (Using your specific path structure)
    // Structure: fuel_logs/{vehicleId}/{logId}
    $fuel_data = db('GET', "fuel_logs/$vehicle_id") ?: [];
    $total_fuel_cost = 0;

    if (is_array($fuel_data)) {
        foreach ($fuel_data as $f) {
            // Your fuel log uses 'cost' as the key
            $total_fuel_cost += (float)($f['cost'] ?? 0);
        }
    }

    // 3. Totals and AI Logic
    $grand_total = $total_maint_cost + $total_fuel_cost;
    $reliability_score = max(min(85 + ($routine_count * 3) - ($repair_count * 12), 100), 0);
    
    $status = "OPTIMAL";
    if ($reliability_score < 85) $status = "STABLE";
    if ($reliability_score < 70) $status = "MONITOR";
    if ($reliability_score < 50) $status = "AT RISK";

    $alerts = [];
    if ($last_service_date) {
        $days = (time() - strtotime($last_service_date)) / 86400;
        if ($days > 180) $alerts[] = "Service overdue by " . round($days) . " days.";
    }

    echo json_encode([
        'success' => true,
        'report' => [
            'health_status' => $status,
            'reliability_score' => $reliability_score . "%",
            'financials' => [
                'maint' => number_format($total_maint_cost, 2),
                'fuel' => number_format($total_fuel_cost, 2),
                'total' => number_format($grand_total, 2)
            ],
            'alerts' => $alerts
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}