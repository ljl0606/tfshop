<?php
header('Access-Control-Allow-Origin: http://example.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Origin');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

echo json_encode(['message' => 'CORS test successful', 'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'unknown']);
