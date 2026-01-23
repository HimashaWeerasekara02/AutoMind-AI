<?php
// manage_diagnostics.php
header('Content-Type: application/json');
require_once 'db.php';

// Fetches the stored AI analyses from Firebase
$data = db('GET', 'ai_analyses');
echo json_encode($data ?: []);