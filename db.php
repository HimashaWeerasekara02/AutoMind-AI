<?php

define(
    'FIREBASE_DB_URL',
    'https://automind-ai-52b33-default-rtdb.asia-southeast1.firebasedatabase.app'
);


define('FIREBASE_AUTH', 'K2Np7mgZnDOQJCVjZLcyftVMapBOxZNzyHhJQ87T'); 

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
    $url = rtrim(FIREBASE_DB_URL, '/') . '/' . ltrim($path, '/') . '.json';

    if (trim(FIREBASE_AUTH) !== '') {
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
        CURLOPT_SSL_VERIFYPEER => true 
    ];

    if ($data !== null && $method !== 'GET') {
        $options[CURLOPT_POSTFIELDS] = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL Connection Error: $error");
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        throw new Exception("Firebase API Error (HTTP $httpCode): $response");
    }

    if ($response === 'null' || $response === '' || $response === null) {
        return null;
    }

    return json_decode($response, true);
}