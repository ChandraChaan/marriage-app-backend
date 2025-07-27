<?php
require_once 'db.php';

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "No token provided."]);
    exit();
}

$token = $headers['Authorization'];
$decoded = explode("|", base64_decode($token));

if (count($decoded) !== 2 || !is_numeric($decoded[0])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token."]);
    exit();
}

$userId = intval($decoded[0]);

// Optional: Validate user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid or expired token."]);
    exit();
}

// Success: $userId can now be used in your API