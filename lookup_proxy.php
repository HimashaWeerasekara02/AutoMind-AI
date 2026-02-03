<?php
// lookup_proxy.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allows your frontend to communicate with this script

// Server-side API Key
$api_key = "AIzaSyBUgfpLxbJuYbn5Bo63wrnzmrSw1HLOEnk"; 

// Get and sanitize the query
$query = isset($_GET['q']) ? strip_tags(trim($_GET['q'])) : '';

if (empty($query)) {
    echo json_encode([
        'status' => 'EMPTY',
        'message' => 'No search term provided',
        'results' => []
    ]);
    exit;
}

/**
 * We are using Google Places Text Search. 
 * This is great for finding local specialists for specific car problems.
 */
$search_query = urlencode($query . " car repair service");
$url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$search_query}&key={$api_key}";

// Fetch the data from Google
$response = file_get_contents($url);

if ($response === FALSE) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Failed to connect to search provider'
    ]);
    exit;
}

// Pass the Google response directly back to your frontend
echo $response;
?>