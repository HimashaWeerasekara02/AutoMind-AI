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
 * 🔐 Firebase Database Secret / Auth Token
 * If your Firebase Rules are set to: 
 * ".read": "auth != null", ".write": "auth != null"
 * You MUST paste your "Database Secret" here.
 */
define('FIREBASE_AUTH', ''); 

/**
 * Firebase Realtime Database REST API Helper
 *
 * @param string      $method  GET | POST | PUT | PATCH | DELETE
 * @param string      $path    Firebase node path (e.g. vehicles, vehicles/id)
 * @param array|null  $data    Data payload
 *
 * @return array|null
 * @throws Exception
 */
function db(string $method, string $path = '', array $data = null): ?array
{
    // 🔹 Build basic Firebase endpoint
    $url = rtrim(FIREBASE_DB_URL, '/') . '/' . ltrim($path, '/') . '.json';

    // 🔹 Correctly append auth token
    if (trim(FIREBASE_AUTH) !== '') {
        // If the URL already has a '?' (unlikely with .json but safe), use '&', otherwise use '?'
        $separator = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $separator . 'auth=' . FIREBASE_AUTH;
    }

    $ch = curl_init($url);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json'
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => true // Ensure secure connection
    ];

    // 🔹 Attach JSON body for POST, PUT, or PATCH
    if ($data !== null && $method !== 'GET') {
        $options[CURLOPT_POSTFIELDS] = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);

    // ❌ cURL execution error
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL Connection Error: $error");
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ❌ Firebase API error (4xx or 5xx)
    if ($httpCode >= 400) {
        throw new Exception("Firebase API Error (HTTP $httpCode): $response");
    }

    // 🔹 Handle Empty/Null responses
    // Firebase returns the string "null" (as text) if the node doesn't exist
    if ($response === 'null' || $response === '' || $response === null) {
        return null;
    }

    // 🔹 Return decoded array
    return json_decode($response, true);
}