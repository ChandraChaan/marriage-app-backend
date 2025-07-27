<?php
require '../cors.php';
require '../user_auth.php'; // ✅ dynamic token-based validation
require '../db.php';

// Parse PUT data (application/x-www-form-urlencoded)
parse_str(file_get_contents("php://input"), $data);

// Validate inputs
$name   = $data['name']   ?? '';
$phone  = $data['phone']  ?? '';
$gender = $data['gender'] ?? '';
$dob    = $data['dob']    ?? '';

// Validate required fields
if (empty($name) || empty($gender) || empty($dob)) {
    echo json_encode(["error" => "Name, gender, and DOB are required."]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, gender = ?, dob = ? WHERE id = ?");
$stmt->bind_param("ssssi", $name, $phone, $gender, $dob, $userId);

if ($stmt->execute()) {
    echo json_encode(["message" => "Profile updated successfully."]);
} else {
    echo json_encode(["error" => "Failed to update profile."]);
}
?>