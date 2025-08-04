<?php
require '../cors.php';
require '../user_auth.php'; // This must set $userId
require '../db.php';

// Ensure userId is set and valid
if (!isset($userId) || empty($userId)) {
    echo json_encode(["error" => "Invalid user authentication."]);
    exit;
}

// Get input data
parse_str(file_get_contents("php://input"), $data);

// List of allowed fields
$allowedFields = [
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome'
];

// Prepare query components
$columns = ['userId'];
$placeholders = ['?'];
$updateParts = [];
$values = [$userId];
$types = 'i'; // userId is integer

// Process each field
foreach ($allowedFields as $field) {
    if (isset($data[$field])) {
        $columns[] = $field;
        $placeholders[] = '?';
        $updateParts[] = "$field = VALUES($field)";
        $values[] = $data[$field];
        
        // Determine parameter type
        if (is_int($data[$field])) {
            $types .= 'i';
        } elseif (is_float($data[$field])) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }
}

// Validate we have fields to update
if (count($columns) === 1) {
    echo json_encode(["error" => "No valid fields provided."]);
    exit;
}

// Build the query
$sql = "INSERT INTO PartnerReqProfile (" . implode(", ", $columns) . ") 
        VALUES (" . implode(", ", $placeholders) . ") 
        ON DUPLICATE KEY UPDATE " . implode(", ", $updateParts);

// Prepare and execute statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

// Bind parameters with proper types
$stmt->bind_param($types, ...$values);

// Execute and respond
if ($stmt->execute()) {
    $affectedRows = $stmt->affected_rows;
    $message = $affectedRows === 1 ? "New record created." : "Existing record updated.";
    echo json_encode(["message" => "Partner requirement profile saved successfully. $message"]);
} else {
    echo json_encode(["error" => "Database operation failed: " . $stmt->error]);
}

// Clean up
$stmt->close();
$conn->close();
?>