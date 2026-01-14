<?php
/**
 * Firebase Realtime Database Configuration
 * AutoMind AI
 */

/**
 * ✅ IMPORTANT:
 * Use the REGION-SPECIFIC URL shown in Firebase Console
 */
define(
    'FIREBASE_DB_URL',
    'https://automind-ai-52b33-default-rtdb.asia-southeast1.firebasedatabase.app'
);

/**
 * 🔐 Optional Authentication
 * Leave empty if Firebase rules allow public access
 * (Recommended to add later for production)
 */
define('FIREBASE_AUTH', '');

/**
 * Firebase Realtime Database REST API Helper
 *
 * @param string      $method  GET | POST | PUT | PATCH | DELETE
 * @param string      $path    Firebase node path (e.g. vehicles, vehicles/id)
 * @param array|null $data    Data payload
 *
 * @return array|null
 * @throws Exception
 */
function db(string $method, string $path = '', array $data = null): ?array
{
    // 🔹 Build Firebase endpoint
    $url = rtrim(FIREBASE_DB_URL, '/') . '/' . ltrim($path, '/') . '.json';

    // 🔹 Append auth token if exists
    if (FIREBASE_AUTH !== '') {
        $url .= '?auth=' . FIREBASE_AUTH;
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json'
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 20,
    ]);

    // 🔹 Attach JSON body for non-GET requests
    if ($data !== null && $method !== 'GET') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ));
    }

    $response = curl_exec($ch);

    // ❌ cURL error
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("Firebase cURL Error: $error");
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ❌ Firebase HTTP error
    if ($httpCode >= 400) {
        throw new Exception("Firebase HTTP Error {$httpCode}: {$response}");
    }

    // 🔹 Empty response (valid for DELETE)
    if ($response === '' || $response === 'null') {
        return null;
    }

    return json_decode($response, true);
}
