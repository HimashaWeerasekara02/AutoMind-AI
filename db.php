<?php

// 🔹 Firebase Realtime Database URL (region-specific)
define('FIREBASE_DB_URL', 'https://automind-ai-52b33-default-rtdb.asia-southeast1.firebasedatabase.app');

define('FIREBASE_AUTH', '');

/**
 * Firebase Database REST Function
 *
 * @param string $method  GET | POST | PUT | PATCH | DELETE
 * @param string $path    Node path (example: vehicles, vehicles/vehicleId)
 * @param array|null $data Data to send
 * @return array|null
 * @throws Exception
 */
function db(string $method, string $path = '', array $data = null): ?array
{
    // Build Firebase URL
    $url = rtrim(FIREBASE_DB_URL, '/') . '/' . trim($path, '/') . '.json';

    // Add auth if exists
    if (!empty(FIREBASE_AUTH)) {
        $url .= '?auth=' . FIREBASE_AUTH;
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);

    // Attach data if provided
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    $response = curl_exec($ch);

    // Handle cURL errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("Firebase cURL Error: $error");
    }

    // Get HTTP status
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle Firebase HTTP errors
    if ($httpCode >= 400) {
        throw new Exception("Firebase HTTP Error $httpCode: $response");
    }

    // Handle empty response
    if (trim($response) === '') {
        return null;
    }

    return json_decode($response, true);
}
