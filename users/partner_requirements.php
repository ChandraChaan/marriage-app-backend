<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

parse_str(file_get_contents("php://input"), $data);

$allowedFields = [
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome'
];

$columns = ['userId'];
$placeholders = ['?'];
$updateParts = [];
$values = [$userId];

foreach ($allowedFields as $field) {
    if (isset($data[$field])) {
        $columns[] = $field;
        $placeholders[] = '?';
        $updateParts[] = "$field = VALUES($field)";
        $values[] = $data[$field];
    }
}

if (count($columns) === 1) {
    echo json_encode(["error" => "No valid fields provided."]);
    exit;
}

$sql = "INSERT INTO PartnerReqProfile (" . implode(", ", $columns) . ") 
        VALUES (" . implode(", ", $placeholders) . ") 
        ON DUPLICATE KEY UPDATE " . implode(", ", $updateParts);

$types = str_repeat('s', count($values));
$types[0] = 'i'; // userId is integer

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Statement preparation failed"]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(["message" => "Partner requirement profile saved successfully."]);
} else {
    echo json_encode(["error" => "Insert or update failed."]);
}

$stmt->close();
$conn->close();
?>
