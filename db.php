<?php

define('FIREBASE_DB_URL', 'https://automind-ai-52b33-default-rtdb.asia-southeast1.firebasedatabase.app');
define('FIREBASE_AUTH', 'K2Np7mgZnDOQJCVjZLcyftVMapBOxZNzyHhJQ87T'); 


function db(string $method, string $path = '', array $data = null)
{
    $cleanPath = ltrim($path, '/');
    $url = rtrim(FIREBASE_DB_URL, '/') . '/' . $cleanPath . '.json';

    if (trim(FIREBASE_AUTH) !== '') {
        $url .= '?auth=' . FIREBASE_AUTH;
    }

    $ch = curl_init($url);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => true 
    ];

    if ($data !== null && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ["success" => false, "error" => "CURL_ERROR", "message" => $error];
    }

    curl_close($ch);
    $decoded = json_decode($response, true);

    if ($httpCode >= 400) {
        return [
            "success" => false, 
            "status"  => $httpCode, 
            "message" => $decoded['error'] ?? "Firebase API Error"
        ];
    }

    return $decoded;
}


function isAdmin($userId) {
    $userData = db('GET', "users/$userId");
    return (isset($userData['role']) && $userData['role'] === 'admin');
}
?>