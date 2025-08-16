<?php
require '../cors.php';
require '../db.php';

// Always return JSON
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
$password = $data['password']; // raw for validation
$gender   = trim($data['gender']);
$dob      = trim($data['dob']); // expected: DD/MM/YYYY

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email format."]);
    exit;
}

// Validate phone (10 digits)
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid phone number. Must be 10 digits."]);
    exit;
}

// Validate password length (min 6 chars)
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["error" => "Password must be at least 6 characters."]);
    exit;
}

// Convert DOB to YYYY-MM-DD
$dobDate = DateTime::createFromFormat('d/m/Y', $dob);
if (!$dobDate) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid DOB format. Use DD/MM/YYYY."]);
    exit;
}
$dob = $dobDate->format('Y-m-d');

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Check if email or phone already exists
$checkStmt = $conn->prepare("SELECT id FROM UserProfile WHERE email = ? OR phone = ?");
$checkStmt->bind_param("ss", $email, $phone);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(["error" => "User already exists with this email or phone."]);
    $checkStmt->close();
    $conn->close();
    exit; 
}
$checkStmt->close();

// Insert new user
$insertStmt = $conn->prepare("
    INSERT INTO UserProfile (name, email, phone, password, gender, dob) 
    VALUES (?, ?, ?, ?, ?, ?)
");
$insertStmt->bind_param("ssssss", $name, $email, $phone, $hashedPassword, $gender, $dob);

if ($insertStmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(["message" => "User registered successfully."]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Registration failed."]);
}

$insertStmt->close();
$conn->close();
?>
