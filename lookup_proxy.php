<?php
// lookup_proxy.php
header('Content-Type: application/json');

// This key should stay strictly on the server
$api_key = "AIzaSyBUgfpLxbJuYbn5Bo63wrnzmrSw1HLOEnk"; 

$query = isset($_GET['q']) ? $_GET['q'] : '';

if (empty($query)) {
    echo json_encode(['status' => 'EMPTY', 'results' => []]);
    exit;
}

// We search for repair info related to the car issue
$search_query = urlencode("car repair " . $query);
$url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$search_query}&key={$api_key}";

$response = file_get_contents($url);

// Pass the Google response directly back to the JS
echo $response;