<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = db('GET', 'ai_analyses');
echo json_encode($data ?: []);