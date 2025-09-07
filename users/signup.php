<?php
require '../cors.php';
require '../db.php';

// Set response to JSON
header('Content-Type: application/json');

// Read raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$required = ['name', 'email', 'phone', 'password', 'gender', 'dob'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing field: $field"]);
        exit;
    }
}

// Sanitize and assign inputs
$name     = trim($data['name']);
$email    = trim($data['email']);
$phone    = trim($data['phone']);
$password = password_hash($data['password'], PASSWORD_BCRYPT);
$gender   = trim($data['gender']);
$dob      = trim($data['dob']);

// Check if email or phone already exists
$checkStmt = $conn->prepare("SELECT id FROM UserProfile WHERE email = ? OR phone = ?");
$checkStmt->bind_param("ss", $email, $phone);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(["error" => "User already exists with this email or phone."]);
    exit;
}
$checkStmt->close();
 
// Insert new user
$insertStmt = $conn->prepare("INSERT INTO UserProfile (name, email, phone, password, gender, dob) VALUES (?, ?, ?, ?, ?, ?)");
$insertStmt->bind_param("ssssss", $name, $email, $phone, $password, $gender, $dob);

if ($insertStmt->execute()) {
    $newUserId = $insertStmt->insert_id;

    // Generate token (same as sign in)
    $token = base64_encode($newUserId . "|" . time());

    http_response_code(201); // Created
    echo json_encode([
        "message" => "User registered successfully.",
        "token" => $token,
        "user" => [
            "id" => $newUserId,
            "name" => $name,
            "email" => $email
        ]
    ]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Registration failed. Please try again."]);
}

$insertStmt->close();
$conn->close();
?>
