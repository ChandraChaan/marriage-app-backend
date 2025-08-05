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

// Only perform UPDATE, never INSERT
$setClause = implode(", ", $setParts);
$sql = "UPDATE PartnerRedProfile SET $setClause WHERE userId = ?";
$values[] = $userId;

// Determine types: assume all are strings except userId at end
$types = str_repeat('s', count($values) - 1) . 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    // Check if any rows were actually updated
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Partner requirements updated successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "No matching profile found to update. Please create a profile first."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to update partner requirements: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>