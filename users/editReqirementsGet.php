<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

parse_str(file_get_contents("php://input"), $data);

// Secure and ordered list of allowed fields to update for Partner Requirements
$allowedFields = [
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',
    'Qualification', 'WorkingAs', 'WorkingWith', 'ProfessionArea', 'AnnualIncome'
];

$setParts = [];
$values = [];

foreach ($data as $key => $value) {
    if (in_array($key, $allowedFields)) {
        $setParts[] = "$key = ?";
        $values[] = $value;
    }
}

if (empty($setParts)) {
    echo json_encode(["success" => false, "error" => "No valid fields provided for update."]);
    exit;
}

$setClause = implode(", ", $setParts);
$sql = "UPDATE PartnerReqProfile SET $setClause WHERE userId = ?";
$values[] = $userId;

// Determine parameter types: all strings except the final userId which is an int
$types = str_repeat('s', count($values) - 1) . 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Partner requirements updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to update partner requirements: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
