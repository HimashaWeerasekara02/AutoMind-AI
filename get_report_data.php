<?php

header('Content-Type: application/json');
require_once 'db.php';
session_start();


ini_set('display_errors', 0);

$vId = $_GET['vehicleId'] ?? null;
if (!$vId) { 
    echo json_encode(['success' => false, 'message' => 'Vehicle ID is required for telemetry sync.']); 
    exit; 
}

try {
    $maint_data = db('GET', "maintenance_logs") ?: [];
    $total_maint = 0;
    $repairs = 0; 
    $routines = 0;

    foreach ((array)$maint_data as $record) {
        $r = (array)$record;
        
        $recordVehicleId = isset($r['vehicleId']) ? trim((string)$r['vehicleId']) : '';
        $targetVehicleId = trim((string)$vId);

        if ($recordVehicleId === $targetVehicleId) {
            $cost = $r['totalCost'] ?? $r['cost'] ?? $r['amount'] ?? 0;
            $total_maint += (float)$cost;

            $type = strtolower($r['type'] ?? '');
            if (strpos($type, 'repair') !== false || strpos($type, 'audit') !== false || strpos($type, 'defect') !== false) {
                $repairs++; 
            } else {
                $routines++;
            }
        }
    }

    $fuel_total = 0;
    $targetVehicleId = trim((string)$vId);
    
    $vehicle_fuel_path = db('GET', "fuel_logs/$targetVehicleId");
    
    if ($vehicle_fuel_path && is_array($vehicle_fuel_path)) {
        foreach ($vehicle_fuel_path as $f) {
            $entry = (array)$f;
            $fCost = $entry['cost'] ?? $entry['total_price'] ?? $entry['amount'] ?? 0;
            $fuel_total += (float)$fCost;
        }
    } else {
        $global_fuel = db('GET', "fuel_logs") ?: [];
        foreach ((array)$global_fuel as $f) {
            $entry = (array)$f;
            if (isset($entry['vehicleId']) && trim((string)$entry['vehicleId']) === $targetVehicleId) {
                $fCost = $entry['cost'] ?? $entry['total_price'] ?? $entry['amount'] ?? 0;
                $fuel_total += (float)$fCost;
            }
        }
    }

    
    $score = 85 + ($routines * 3) - ($repairs * 8);
    $score = max(min($score, 100), 10);

   
    $alerts = [];
    if ($total_maint == 0 && $fuel_total == 0) {
        $alerts[] = "Awaiting data stream. Please log maintenance or fuel entries for this vehicle.";
    } else {
        if ($score > 80) $alerts[] = "Mechanical reliability is currently OPTIMAL.";
        if ($score <= 50) $alerts[] = "WARNING: Frequent repairs detected. Risk of critical failure is high.";
        
        if ($fuel_total > ($total_maint * 4) && $total_maint > 0) {
            $alerts[] = "Fuel expenses are significantly higher than maintenance. Consider a tune-up.";
        }
    }
    
    if (empty($alerts)) $alerts[] = "Telemetry stable. No immediate alerts.";

    echo json_encode([
        'success' => true,
        'report' => [
            'health_status' => ($score > 80 ? "OPTIMAL" : ($score > 50 ? "STABLE" : "AT RISK")),
            'reliability_score' => $score . "%",
            'financials' => [
                'maint' => (string)round($total_maint, 2),
                'fuel' => (string)round($fuel_total, 2),
                'total' => (string)round($total_maint + $fuel_total, 2)
            ],
            'alerts' => $alerts
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database Sync Error: ' . $e->getMessage()]);
}