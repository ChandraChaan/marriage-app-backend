<?php
require '../cors.php';
require '../db.php';

// Set response to JSON
header('Content-Type: application/json');
 
// Read raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$required = ['name', 'email', 'phone', 'subject', 'message'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing field: $field"]);
        exit;
    }
}

// Sanitize and assign inputs
$name    = trim($data['name']);
$email   = trim($data['email']);
$phone   = trim($data['phone']);
$subject = trim($data['subject']);
$message = trim($data['message']);

// Insert contact message into DB
$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(["message" => "Message submitted successfully."]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Failed to save message."]);
}

$stmt->close();
$conn->close();
?>
