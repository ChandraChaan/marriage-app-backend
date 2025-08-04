<?php
require '../cors.php';
require '../user_auth.php'; // Ensures $userId is available
require '../db.php';

parse_str(file_get_contents("php://input"), $data);

// Allowed fields for updating
$allowedFields = [
    // Basic Details
    'ProfileCreatedBy', 'Age', 'Height', 'MotherTongue', 'MaritalStatus',
    'PhysicalStatus', 'Country', 'State', 'City',

    // Religious Details
    'Religion', 'Cast', 'SubCast', 'Dosham',
    'EatingHabits', 'SmokingHabits', 'DrinkingHabits',

    // Education & Career
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
    echo json_encode(["error" => "No valid fields provided."]);
    exit;
}

$setClause = implode(", ", $setParts);
$sql = "UPDATE PartnerReqProfile SET $setClause WHERE userId = ?";
$values[] = $userId;

// Bind types
$types = str_repeat('s', count($values) - 1) . 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare statement."]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(["message" => "Partner requirements updated successfully."]);
} else {
    echo json_encode(["error" => "Update failed."]);
}

$stmt->close();
$conn->close();
?>
