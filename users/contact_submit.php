<?php
require '../cors.php';
require '../db.php';

// Set response header
header('Content-Type: application/json');

// Decode raw JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Required fields
$required = ['name', 'email', 'phone', 'subject', 'message'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing field: $field"]);
        exit;
    }
}

// Sanitize inputs
$name    = trim($data['name']);
$email   = trim($data['email']);
$phone   = trim($data['phone']);
$subject = trim($data['subject']);
$message = trim($data['message']); 

// Get accurate UTC time
$createdAt = (new DateTime("now", new DateTimeZone("Asia/Kolkata")))->format("Y-m-d H:i:s");

// Prepare SQL insert
$stmt = $conn->prepare("INSERT INTO contact_form (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $createdAt);

// Execute and respond
if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["message" => "Message submitted successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to save message."]);
}

$stmt->close();
$conn->close();
?>
