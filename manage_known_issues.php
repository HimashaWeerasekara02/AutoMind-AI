<?php
// manage_known_issues.php
header('Content-Type: application/json');
require_once 'db.php';

// Fetch the 'known_issues' node from Firebase
$data = db('GET', 'known_issues');
echo json_encode($data ?: []);