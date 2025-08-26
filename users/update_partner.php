<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../cors.php';
require '../user_auth.php'; // Still need user auth for security
require '../db.php';

// Allow both POST and PUT
$method = $_SERVER['REQUEST_METHOD'];
$data = [];

if ($method === 'POST') {
    $data = $_POST;
} elseif ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $data);
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method Not Allowed. Use POST or PUT."]);
    exit;
}

// Secure and ordered list of allowed fields to update for Partner Requirements
$allowedFields = [
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome',
    'EmploymentType','Education',
];

$fields = [];
$placeholders = [];
$updates = [];
$values = [];

foreach ($data as $key => $value) {
    if (in_array($key, $allowedFields)) {
        $fields[] = $key;
        $placeholders[] = "?";
        $updates[] = "$key = VALUES($key)";
        $values[] = $value;
    }
}

if (empty($fields)) {
    echo json_encode(["success" => false, "error" => "No valid fields provided for update."]);
    exit;
}

// Build SQL - removed userId
$sql = "INSERT INTO PartnerReqProfile (" . implode(", ", $fields) . ")
        VALUES (" . implode(", ", $placeholders) . ")
        ON DUPLICATE KEY UPDATE " . implode(", ", $updates);

// Bind all as strings
$types = str_repeat('s', count($values));

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error, "sql" => $sql]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Partner requirements saved successfully (inserted or updated)"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to save partner requirements: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>